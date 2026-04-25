<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Models\UserModel;

class AuthMiddleware {
    public static function handle(): void {
        if (($_SESSION['role'] ?? '') !== 'admin') {
            header('Location: /admin/login');
            exit;
        }
    }

    public static function requireGuest(): void {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if (($_SESSION['role'] ?? '') !== 'guest' || $userId <= 0) {
            header('Location: /gifts');
            exit;
        }

        $user = (new UserModel)->findById($userId);
        if (!$user || ($user['role'] ?? '') !== 'guest') {
            $_SESSION = [];
            session_regenerate_id(true);
            header('Location: /gifts');
            exit;
        }
    }
}
