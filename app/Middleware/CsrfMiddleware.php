<?php
declare(strict_types=1);

namespace App\Middleware;

class CsrfMiddleware {
    public static function token(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validate(): void {
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            (new \App\Controllers\ErrorController)->forbidden();
            exit;
        }
    }
}
