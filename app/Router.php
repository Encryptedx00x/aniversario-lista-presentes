<?php
declare(strict_types=1);

use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Controllers\ErrorController;

class Router {
    public static function dispatch(string $uri, string $method, array $routes): void {
        $routeMap = $routes[$method] ?? [];

        if (!isset($routeMap[$uri])) {
            (new ErrorController)->notFound();
            return;
        }

        [$controllerName, $action, $middleware] = array_pad($routeMap[$uri], 3, null);

        // Auth guard — checked before CSRF so unauthenticated users get a clean redirect
        if ($middleware === 'auth') {
            AuthMiddleware::handle();
        }

        // CSRF validation on every state-mutating request (SEC-02)
        if ($method === 'POST') {
            CsrfMiddleware::validate();
        }

        $fqcn       = "App\\Controllers\\{$controllerName}";
        $controller = new $fqcn();
        $controller->$action();
    }
}
