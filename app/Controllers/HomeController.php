<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\View;
use App\Models\GiftModel;
use App\Models\SettingModel;

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

        $birthdayDateRaw = (new SettingModel)->get('birthday_date', '');
        $countdownDays = null;
        $nextBirthdayLabel = null;
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthdayDateRaw)) {
            $today = new \DateTimeImmutable('today');
            $baseBirthday = \DateTimeImmutable::createFromFormat('Y-m-d', $birthdayDateRaw) ?: null;
            if ($baseBirthday instanceof \DateTimeImmutable) {
                $monthDay = $baseBirthday->format('m-d');
                $next = \DateTimeImmutable::createFromFormat('Y-m-d', $today->format('Y') . '-' . $monthDay) ?: null;
                if ($next instanceof \DateTimeImmutable && $next < $today) {
                    $next = $next->modify('+1 year');
                }

                if ($next instanceof \DateTimeImmutable) {
                    $countdownDays = (int) $today->diff($next)->days;
                    $nextBirthdayLabel = $next->format('d/m/Y');
                }
            }
        }

        View::render('public/home', [
            'title' => APP_NAME . ' — Home',
            'layout' => 'main',
            'featured' => $featured,
            'giftsCount' => count($gifts),
            'countdownDays' => $countdownDays,
            'nextBirthdayLabel' => $nextBirthdayLabel,
        ]);
    }
}
