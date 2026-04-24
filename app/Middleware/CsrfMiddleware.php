<?php
declare(strict_types=1);

namespace App\Middleware;

class CsrfMiddleware {
    private const COOKIE = 'csrf_token';
    private const TTL    = 3600;

    public static function token(): string {
        // Double-submit cookie pattern: token lives in a cookie (not session),
        // so it works regardless of which server handles the POST on shared hosting.
        if (empty($_COOKIE[self::COOKIE])) {
            $token = bin2hex(random_bytes(32));
            $secure = getenv('APP_ENV') !== 'development';
            setcookie(self::COOKIE, $token, [
                'expires'  => time() + self::TTL,
                'path'     => '/',
                'secure'   => $secure,
                'httponly' => false, // must be readable by JS for AJAX (we read it server-side from POST body)
                'samesite' => 'Lax',
            ]);
            $_COOKIE[self::COOKIE] = $token; // make available for current request
        }
        return $_COOKIE[self::COOKIE];
    }

    public static function validate(): void {
        $post   = $_POST[self::COOKIE]   ?? '';
        $cookie = $_COOKIE[self::COOKIE] ?? '';
        if ($cookie === '' || !hash_equals($cookie, $post)) {
            http_response_code(403);
            (new \App\Controllers\ErrorController)->forbidden();
            exit;
        }
        // Rotate token after each validated POST to limit replay window
        $token = bin2hex(random_bytes(32));
        $secure = getenv('APP_ENV') !== 'development';
        setcookie(self::COOKIE, $token, [
            'expires'  => time() + self::TTL,
            'path'     => '/',
            'secure'   => $secure,
            'httponly' => false,
            'samesite' => 'Lax',
        ]);
        $_COOKIE[self::COOKIE] = $token;
    }
}
