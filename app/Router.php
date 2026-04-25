<?php
declare(strict_types=1);

use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Controllers\ErrorController;

class Router {
    public static function dispatch(string $uri, string $method, array $routes): void {
        $routeMap = $routes[$method] ?? [];

        // 1. Exact match (fast path — no regex overhead)
        if (isset($routeMap[$uri])) {
            [$controllerName, $action, $middleware] = array_pad($routeMap[$uri], 3, null);
            self::run($controllerName, $action, $middleware, $method, []);
            return;
        }

        // 2. Dynamic segment match — {param} placeholders
        foreach ($routeMap as $pattern => $handler) {
            if (strpos($pattern, '{') === false) {
                continue; // skip static routes already checked above
            }
            $regex = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $pattern);
            $regex = '#^' . $regex . '$#';
            if (preg_match($regex, $uri, $matches)) {
                // Keep only named captures (remove numeric keys from preg_match result)
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                [$controllerName, $action, $middleware] = array_pad($handler, 3, null);
                self::run($controllerName, $action, $middleware, $method, $params);
                return;
            }
        }

        (new ErrorController)->notFound();
    }

    private static function run(
        string $controllerName,
        string $action,
        ?string $middleware,
        string $method,
        array $params
    ): void {
        // Auth guards run before CSRF, so unauthorized users get a clean redirect.
        if ($middleware === 'auth') {
            AuthMiddleware::handle();
        } elseif ($middleware === 'guest') {
            AuthMiddleware::requireGuest();
        }

        // CSRF validation on every state-mutating request (SEC-02)
        if ($method === 'POST') {
            CsrfMiddleware::validate();
        }

        $fqcn       = "App\\Controllers\\{$controllerName}";
        $controller = new $fqcn();
        $controller->$action($params);
    }
}
