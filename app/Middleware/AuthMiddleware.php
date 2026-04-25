<?php
declare(strict_types=1);

namespace App\Middleware;

class AuthMiddleware {
    public static function handle(): void {
        if (($_SESSION['role'] ?? '') !== 'admin') {
            header('Location: /admin/login');
            exit;
        }
    }

    public static function requireGuest(): void {
        if (($_SESSION['role'] ?? '') !== 'guest' || empty($_SESSION['user_id'])) {
            header('Location: /gifts');
            exit;
        }
    }
}
