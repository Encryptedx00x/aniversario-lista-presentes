<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\View;
use App\Models\GiftModel;

class HomeController {
    public function index(array $params = []): void {
        $giftModel = new GiftModel;
        $gifts = $giftModel->getAllActive();

        $featured = array_values(array_filter($gifts, static fn(array $gift): bool => (bool) ($gift['featured'] ?? false)));
        if (count($featured) < 3) {
            $featured = array_slice($gifts, 0, 3);
        } else {
            $featured = array_slice($featured, 0, 3);
        }

        View::render('public/home/index', [
            'title' => APP_NAME . ' — Home',
            'layout' => 'main',
            'featured' => $featured,
            'giftsCount' => count($gifts),
        ]);
    }
}
