<?php
declare(strict_types=1);

namespace App\Models;

class ReservationModel extends BaseModel {

    /**
     * Atomic reservation with pessimistic lock.
     * Returns a normalized payload consumed by ReservationController::store().
     */
    public function reserve(int $giftId, int $userId): array {
        try {
            $this->db->beginTransaction();

            $gift = $this->query(
                'SELECT id, quantity, available_qty, reserved, redirect_url
                 FROM gifts
                 WHERE id = :gift_id AND is_active = 1
                 FOR UPDATE',
                [':gift_id' => $giftId]
            )->fetch();

            if (!$gift) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'is_exhausted' => false,
                    'error' => 'Presente não encontrado.',
                ];
            }

            if ((int) $gift['available_qty'] <= 0) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'is_exhausted' => true,
                    'error' => 'Esse presente acabou de ficar indisponível.',
                ];
            }

            $this->query(
                'UPDATE gifts
                 SET available_qty = available_qty - 1,
                     reserved = reserved + 1,
                     updated_at = NOW()
                 WHERE id = :gift_id AND available_qty > 0',
                [':gift_id' => $giftId]
            );

            $this->query(
                'INSERT INTO reservations (gift_id, user_id, status, action_type)
                 VALUES (:gift_id, :user_id, :status, :action_type)',
                [
                    ':gift_id' => $giftId,
                    ':user_id' => $userId,
                    ':status'  => 'active',
                    ':action_type' => 'reserved',
                ]
            );

            $updatedGift = $this->query(
                'SELECT available_qty FROM gifts WHERE id = :gift_id',
                [':gift_id' => $giftId]
            )->fetch();

            $availableQty = (int) ($updatedGift['available_qty'] ?? 0);

            $this->db->commit();

            return [
                'success' => true,
                'available_qty' => $availableQty,
                'is_exhausted' => ($availableQty === 0),
                'redirect_url' => (string) ($gift['redirect_url'] ?? ''),
            ];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            error_log('[ReservationModel::reserve] ' . $e->getMessage());

            return [
                'success' => false,
                'is_exhausted' => false,
                'error' => 'Não foi possível concluir sua reserva agora. Tente novamente.',
            ];
        }
    }

    /**
     * Returns active reservations for the current guest (newest first).
     */
    public function getActiveByUser(int $userId): array {
        return $this->query(
            'SELECT
                r.id,
                r.gift_id,
                r.status,
                r.action_type,
                r.created_at,
                g.name AS gift_name,
                g.category,
                g.description,
                g.price,
                g.image_path,
                g.available_qty,
                g.slug,
                p.amount AS paid_amount
             FROM reservations r
             INNER JOIN gifts    g ON g.id = r.gift_id
             LEFT  JOIN payments p ON p.reservation_id = r.id AND p.mp_status = "approved"
             WHERE r.user_id = :user_id
               AND r.status = :status
             ORDER BY r.created_at DESC',
            [
                ':user_id' => $userId,
                ':status'  => 'active',
            ]
        )->fetchAll();
    }

    /**
     * Admin listing — joins users + gifts and supports filtering by action_type
     * and status. Returns most recent first.
     */
    public function adminList(string $action = 'all', string $status = 'active'): array {
        $conditions = [];
        $params     = [];

        if ($action === 'reserved' || $action === 'pix') {
            $conditions[]            = 'r.action_type = :action_type';
            $params[':action_type']  = $action;
        }
        if ($status === 'active' || $status === 'cancelled') {
            $conditions[]      = 'r.status = :status';
            $params[':status'] = $status;
        }

        $where = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';
        $sql = "SELECT
                    r.id, r.gift_id, r.user_id, r.status, r.action_type, r.created_at,
                    u.name  AS user_name,
                    u.email AS user_email,
                    u.phone AS user_phone,
                    g.name  AS gift_name,
                    g.price AS gift_price,
                    g.category AS gift_category,
                    p.amount AS paid_amount
                FROM reservations r
                INNER JOIN users    u ON u.id = r.user_id
                INNER JOIN gifts    g ON g.id = r.gift_id
                LEFT  JOIN payments p ON p.reservation_id = r.id AND p.mp_status = 'approved'
                {$where}
                ORDER BY r.created_at DESC";

        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Aggregated summary used by the admin dashboard / reservas page header.
     */
    public function adminSummary(): array {
        $row = $this->query(
            "SELECT
                SUM(CASE WHEN action_type = 'reserved' AND status = 'active' THEN 1 ELSE 0 END) AS active_reserved,
                SUM(CASE WHEN action_type = 'pix'      AND status = 'active' THEN 1 ELSE 0 END) AS active_pix,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END)                          AS cancelled_total,
                COUNT(*)                                                                        AS total
             FROM reservations"
        )->fetch();

        return [
            'active_reserved' => (int) ($row['active_reserved'] ?? 0),
            'active_pix'      => (int) ($row['active_pix']      ?? 0),
            'cancelled_total' => (int) ($row['cancelled_total'] ?? 0),
            'total'           => (int) ($row['total']           ?? 0),
        ];
    }

    /**
     * Cancels one active reservation owned by the given guest and returns stock.
     */
    public function cancel(int $reservationId, int $userId): array {
        try {
            $this->db->beginTransaction();

            $reservation = $this->query(
                'SELECT id, gift_id, status, action_type
                 FROM reservations
                 WHERE id = :reservation_id AND user_id = :user_id
                 FOR UPDATE',
                [
                    ':reservation_id' => $reservationId,
                    ':user_id'        => $userId,
                ]
            )->fetch();

            if (!$reservation) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'error' => 'Reserva não encontrada.',
                ];
            }

            if ($reservation['status'] !== 'active') {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'error' => 'Essa reserva já foi cancelada.',
                ];
            }
            if (($reservation['action_type'] ?? 'reserved') !== 'reserved') {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'error' => 'Somente reservas podem ser canceladas.',
                ];
            }

            $this->query(
                'UPDATE reservations
                 SET status = :status
                 WHERE id = :reservation_id AND status = :active_status',
                [
                    ':status'         => 'cancelled',
                    ':reservation_id' => $reservationId,
                    ':active_status'  => 'active',
                ]
            );

            $this->query(
                'UPDATE gifts
                 SET available_qty = LEAST(quantity, available_qty + 1),
                     reserved = GREATEST(reserved - 1, 0),
                     updated_at = NOW()
                 WHERE id = :gift_id',
                [':gift_id' => (int) $reservation['gift_id']]
            );

            $updatedGift = $this->query(
                'SELECT available_qty FROM gifts WHERE id = :gift_id',
                [':gift_id' => (int) $reservation['gift_id']]
            )->fetch();

            $this->db->commit();

            return [
                'success' => true,
                'available_qty' => (int) ($updatedGift['available_qty'] ?? 0),
            ];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            error_log('[ReservationModel::cancel] ' . $e->getMessage());

            return [
                'success' => false,
                'error' => 'Não foi possível cancelar sua reserva agora. Tente novamente.',
            ];
        }
    }
}
