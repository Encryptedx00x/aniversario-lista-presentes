<?php
declare(strict_types=1);

namespace App\Models;

class AdminStatsModel extends BaseModel {
    public function getDashboardStats(): array {
        $giftsTotal = (int) ($this->query('SELECT COUNT(*) AS c FROM gifts')->fetch()['c'] ?? 0);
        $giftsAvailable = (int) ($this->query('SELECT COUNT(*) AS c FROM gifts WHERE is_active = 1 AND available_qty > 0')->fetch()['c'] ?? 0);
        $giftsSoldOut = (int) ($this->query('SELECT COUNT(*) AS c FROM gifts WHERE is_active = 1 AND available_qty <= 0')->fetch()['c'] ?? 0);

        $reservations = (int) ($this->query(
            "SELECT COUNT(*) AS c
             FROM reservations
             WHERE status IN ('active', 'reserved', 'confirmed')"
        )->fetch()['c'] ?? 0);

        $pixContributions = (int) ($this->query('SELECT COUNT(*) AS c FROM contributions')->fetch()['c'] ?? 0);

        return [
            'gifts_total'        => $giftsTotal,
            'gifts_available'    => $giftsAvailable,
            'gifts_sold_out'     => $giftsSoldOut,
            'reservations_total' => $reservations,
            'pix_total'          => $pixContributions,
        ];
    }
}
