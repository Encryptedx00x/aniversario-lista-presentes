<?php
declare(strict_types=1);

namespace App\Models;

class PaymentModel extends BaseModel {

    /**
     * Inserts a new payment record. Returns the new row id.
     *
     * Expected keys in $data:
     *   user_id, gift_id, amount, mp_payment_id, mp_status, mp_qr_code,
     *   mp_qr_code_b64, mp_mode, expires_at (Y-m-d H:i:s | null), raw_response (string|null)
     */
    public function create(array $data): int {
        $this->query(
            'INSERT INTO payments
                (user_id, gift_id, amount, mp_payment_id, mp_status,
                 mp_qr_code, mp_qr_code_b64, mp_mode, expires_at, raw_response)
             VALUES
                (:user_id, :gift_id, :amount, :mp_payment_id, :mp_status,
                 :mp_qr_code, :mp_qr_code_b64, :mp_mode, :expires_at, :raw_response)',
            [
                ':user_id'        => (int) $data['user_id'],
                ':gift_id'        => (int) $data['gift_id'],
                ':amount'         => (string) $data['amount'],
                ':mp_payment_id'  => (string) $data['mp_payment_id'],
                ':mp_status'      => (string) ($data['mp_status'] ?? 'pending'),
                ':mp_qr_code'     => (string) ($data['mp_qr_code'] ?? ''),
                ':mp_qr_code_b64' => (string) ($data['mp_qr_code_b64'] ?? ''),
                ':mp_mode'        => (string) $data['mp_mode'],
                ':expires_at'     => $data['expires_at'] ?? null,
                ':raw_response'   => $data['raw_response'] ?? null,
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function findByMpId(string $mpId): array|false {
        return $this->query(
            'SELECT * FROM payments WHERE mp_payment_id = :id LIMIT 1',
            [':id' => $mpId]
        )->fetch();
    }

    public function findByIdForUser(int $id, int $userId): array|false {
        return $this->query(
            'SELECT * FROM payments
             WHERE id = :id AND user_id = :user_id
             LIMIT 1',
            [':id' => $id, ':user_id' => $userId]
        )->fetch();
    }

    /**
     * Returns the most recent still-pending payment for the user/gift pair,
     * if any. Used to reuse the same MP cobrança when the guest reloads
     * `/pix?gift_id=X` instead of creating duplicates.
     */
    public function findPendingForUserAndGift(int $userId, int $giftId): array|false {
        return $this->query(
            "SELECT * FROM payments
             WHERE user_id = :user_id
               AND gift_id = :gift_id
               AND mp_status = 'pending'
               AND (expires_at IS NULL OR expires_at > NOW())
             ORDER BY created_at DESC
             LIMIT 1",
            [':user_id' => $userId, ':gift_id' => $giftId]
        )->fetch();
    }

    public function updateStatus(int $id, string $status, ?string $rawResponse = null): void {
        $this->query(
            'UPDATE payments
             SET mp_status = :status,
                 raw_response = COALESCE(:raw, raw_response)
             WHERE id = :id',
            [
                ':status' => $status,
                ':raw'    => $rawResponse,
                ':id'     => $id,
            ]
        );
    }

    public function markApprovedWithReservation(int $paymentId, int $reservationId, ?string $rawResponse = null): void {
        $this->query(
            "UPDATE payments
             SET mp_status      = 'approved',
                 reservation_id = :reservation_id,
                 raw_response   = COALESCE(:raw, raw_response)
             WHERE id = :id",
            [
                ':reservation_id' => $reservationId,
                ':raw'            => $rawResponse,
                ':id'             => $paymentId,
            ]
        );
    }
}
