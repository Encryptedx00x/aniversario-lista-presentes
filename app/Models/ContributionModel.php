<?php
declare(strict_types=1);

namespace App\Models;

class ContributionModel extends BaseModel {
    public function create(array $data): int {
        $this->query(
            'INSERT INTO contributions (guest_name, guest_email, amount, message, confirmed)
             VALUES (:guest_name, :guest_email, :amount, :message, :confirmed)',
            [
                ':guest_name'  => $data['guest_name'],
                ':guest_email' => $data['guest_email'],
                ':amount'      => $data['amount'],
                ':message'     => $data['message'] ?? null,
                ':confirmed'   => 0, // pending
            ]
        );

        return (int) $this->getDb()->lastInsertId();
    }
}
