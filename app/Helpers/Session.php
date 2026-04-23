<?php
declare(strict_types=1);

namespace App\Helpers;

class Session {
    public static function start(): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.cookie_secure', getenv('APP_ENV') !== 'development' ? '1' : '0');
        session_start();
    }
}
