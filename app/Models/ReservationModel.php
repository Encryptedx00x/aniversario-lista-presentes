<?php
declare(strict_types=1);

namespace App\Models;

class ReservationModel extends BaseModel {

    /**
     * Atomic reservation with pessimistic lock. Aceita quantidade — usada
     * em presentes type='multi' para o convidado pegar mais de uma vaga
     * num único clique. Estoque é decrementado por $qty atomicamente.
     * Returns a normalized payload consumed by ReservationController::store().
     */
    public function reserve(int $giftId, int $userId, int $qty = 1): array {
        if ($qty < 1)  { $qty = 1; }
        if ($qty > 99) { $qty = 99; }

        try {
            $this->db->beginTransaction();

            $gift = $this->query(
                'SELECT id, type, quantity, available_qty, reserved, redirect_url
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

            $availableNow = (int) $gift['available_qty'];

            // Single-type só aceita qty=1, mesmo que o cliente tente abusar.
            if (($gift['type'] ?? 'single') !== 'multi') {
                $qty = 1;
            }

            if ($availableNow <= 0) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'is_exhausted' => true,
                    'error' => 'Esse presente acabou de ficar indisponível.',
                ];
            }

            if ($availableNow < $qty) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'is_exhausted' => false,
                    'available_qty' => $availableNow,
                    'error' => 'Só restam ' . $availableNow . ' ' . ($availableNow === 1 ? 'unidade' : 'unidades') . ' desse presente.',
                ];
            }

            $this->query(
                'UPDATE gifts
                 SET available_qty = available_qty - :qty,
                     reserved      = reserved + :qty,
                     updated_at    = NOW()
                 WHERE id = :gift_id AND available_qty >= :qty',
                [':gift_id' => $giftId, ':qty' => $qty]
            );

            $this->query(
                'INSERT INTO reservations (gift_id, user_id, status, action_type, quantity)
                 VALUES (:gift_id, :user_id, :status, :action_type, :qty)',
                [
                    ':gift_id'     => $giftId,
                    ':user_id'     => $userId,
                    ':status'      => 'active',
                    ':action_type' => 'reserved',
                    ':qty'         => $qty,
                ]
            );

            $updatedGift = $this->query(
                'SELECT available_qty FROM gifts WHERE id = :gift_id',
                [':gift_id' => $giftId]
            )->fetch();

            $availableQty = (int) ($updatedGift['available_qty'] ?? 0);

            $this->db->commit();

            return [
                'success'       => true,
                'quantity'      => $qty,
                'available_qty' => $availableQty,
                'is_exhausted'  => ($availableQty === 0),
                'redirect_url'  => (string) ($gift['redirect_url'] ?? ''),
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
     * Marks a reservation as purchase-confirmed (= 1) or not-purchased (= 0).
     * Quando $confirmed === 0, libera o estoque de volta (igual cancel).
     * Quando 1, só registra a confirmação — estoque continua reservado e
     * o admin sabe que aquela compra externa rolou.
     */
    public function setPurchaseConfirmed(int $reservationId, int $userId, int $confirmed): array {
        $confirmed = $confirmed === 1 ? 1 : 0;
        try {
            $this->db->beginTransaction();

            $reservation = $this->query(
                'SELECT id, gift_id, quantity, status, action_type
                 FROM reservations
                 WHERE id = :rid AND user_id = :uid
                 FOR UPDATE',
                [':rid' => $reservationId, ':uid' => $userId]
            )->fetch();

            if (!$reservation) {
                $this->db->rollBack();
                return ['success' => false, 'error' => 'Reserva não encontrada.'];
            }
            if ($reservation['status'] !== 'active') {
                $this->db->rollBack();
                return ['success' => false, 'error' => 'Essa reserva não está mais ativa.'];
            }
            if (($reservation['action_type'] ?? '') !== 'reserved') {
                $this->db->rollBack();
                return ['success' => false, 'error' => 'Esse fluxo só vale para presentes reservados.'];
            }

            // "Não comprei" libera estoque e cancela a reserva.
            if ($confirmed === 0) {
                $qty = max(1, (int) ($reservation['quantity'] ?? 1));
                $this->query(
                    'UPDATE reservations
                     SET status = "cancelled", purchase_confirmed = 0
                     WHERE id = :rid',
                    [':rid' => $reservationId]
                );
                $this->query(
                    'UPDATE gifts
                     SET available_qty = LEAST(quantity, available_qty + :qty),
                         reserved      = GREATEST(reserved - :qty, 0),
                         updated_at    = NOW()
                     WHERE id = :gid',
                    [':qty' => $qty, ':gid' => (int) $reservation['gift_id']]
                );
            } else {
                $this->query(
                    'UPDATE reservations
                     SET purchase_confirmed = 1
                     WHERE id = :rid',
                    [':rid' => $reservationId]
                );
            }

            $this->db->commit();
            return ['success' => true, 'confirmed' => $confirmed];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('[ReservationModel::setPurchaseConfirmed] ' . $e->getMessage());
            return ['success' => false, 'error' => 'Não foi possível registrar isso agora. Tente novamente.'];
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
                r.quantity,
                r.purchase_confirmed,
                r.created_at,
                g.name AS gift_name,
                g.category,
                g.description,
                g.price,
                g.image_path,
                g.available_qty,
                g.slug,
                g.redirect_url,
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
     * Most recent activity for the admin dashboard. Mixes reservas and PIX
     * (any active reservation row) and joins user/gift/payment so the view
     * can render rows without extra queries.
     */
    public function recentActivity(int $limit = 6): array {
        $limit = max(1, min(50, $limit));
        return $this->query(
            "SELECT r.id, r.action_type, r.created_at, r.status,
                    u.name  AS user_name,
                    g.name  AS gift_name,
                    g.price AS gift_price,
                    p.amount AS paid_amount
             FROM reservations r
             INNER JOIN users    u ON u.id = r.user_id
             INNER JOIN gifts    g ON g.id = r.gift_id
             LEFT  JOIN payments p ON p.reservation_id = r.id AND p.mp_status = 'approved'
             WHERE r.status = 'active'
             ORDER BY r.created_at DESC
             LIMIT {$limit}"
        )->fetchAll();
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
                'SELECT id, gift_id, quantity, status, action_type
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

            $qty = max(1, (int) ($reservation['quantity'] ?? 1));

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
                 SET available_qty = LEAST(quantity, available_qty + :qty),
                     reserved      = GREATEST(reserved - :qty, 0),
                     updated_at    = NOW()
                 WHERE id = :gift_id',
                [':qty' => $qty, ':gift_id' => (int) $reservation['gift_id']]
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
