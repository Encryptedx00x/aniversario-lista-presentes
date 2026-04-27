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
                g.slug
             FROM reservations r
             INNER JOIN gifts g ON g.id = r.gift_id
             WHERE r.user_id = :user_id
               AND r.status = :status
             ORDER BY r.created_at DESC',
            [
                ':user_id' => $userId,
                ':status'  => 'active',
            ]
        )->fetchAll();
    }

    public function contributePix(int $giftId, int $userId): array {
        try {
            $gift = $this->query(
                'SELECT id FROM gifts WHERE id = :gift_id AND is_active = 1 LIMIT 1',
                [':gift_id' => $giftId]
            )->fetch();

            if (!$gift) {
                return [
                    'success' => false,
                    'error' => 'Presente não encontrado.',
                ];
            }

            $this->query(
                'INSERT INTO reservations (gift_id, user_id, status, action_type)
                 VALUES (:gift_id, :user_id, :status, :action_type)',
                [
                    ':gift_id' => $giftId,
                    ':user_id' => $userId,
                    ':status' => 'active',
                    ':action_type' => 'pix',
                ]
            );

            return [
                'success' => true,
            ];
        } catch (\Throwable $e) {
            error_log('[ReservationModel::contributePix] ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Não foi possível registrar o PIX agora. Tente novamente.',
            ];
        }
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
