<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\View;
use App\Models\GiftModel;
use App\Models\ReservationModel;

class HomeController {

    /**
     * Public landing page — hero + countdown + featured gifts + thank-you note.
     */
    public function index(array $params = []): void {
        $giftModel = new GiftModel;
        $allGifts  = $giftModel->getAllActive();

        // Top 3 cards for the "Em destaque" section: featured first, then by sort_order.
        usort($allGifts, function ($a, $b) {
            $featuredCmp = ((int) ($b['featured'] ?? 0)) <=> ((int) ($a['featured'] ?? 0));
            if ($featuredCmp !== 0) {
                return $featuredCmp;
            }
            return ((int) ($a['sort_order'] ?? 0)) <=> ((int) ($b['sort_order'] ?? 0));
        });
        $featured = array_slice($allGifts, 0, 3);

        $totalGifts     = count($allGifts);
        $totalCapacity  = array_sum(array_column($allGifts, 'quantity'));
        $remaining      = array_sum(array_column($allGifts, 'available_qty'));
        $reservedCount  = max(0, $totalCapacity - $remaining);
        $percent        = $totalCapacity > 0
            ? (int) round(($reservedCount / $totalCapacity) * 100)
            : 0;

        // Target date: 12 June 2026 (matches the design copy).
        $target  = mktime(0, 0, 0, 6, 12, 2026) ?: (time() + 47 * 86400);
        $diff    = max(0, $target - time());
        $days    = (int) floor($diff / 86400);

        View::render('public/home', [
            'title'     => APP_NAME,
            'layout'    => 'main',
            'featured'  => $featured,
            'countdown' => [
                'days'           => $days,
                'percent'        => $percent,
                'totalGifts'     => $totalGifts,
                'reservedCount'  => $reservedCount,
                'daysToConfirm'  => 7,
                'targetIso'      => date('c', $target),
            ],
            'activeNav' => 'home',
        ]);
    }

    /**
     * Pix info page — static data, no backend transaction.
     */
    public function pix(array $params = []): void {
        View::render('public/pix', [
            'title'     => APP_NAME . ' — Pix',
            'layout'    => 'main',
            'activeNav' => 'pix',
        ]);
    }
}
