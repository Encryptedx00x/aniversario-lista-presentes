<?php
declare(strict_types=1);

namespace App\Helpers;

class Flash {
    public static function set(string $type, string $message): void {
        $_SESSION['flash'] = ['type' => $type, 'msg' => $message];
    }

    public static function get(): ?array {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }

    public static function has(): bool {
        return isset($_SESSION['flash']);
    }
}
