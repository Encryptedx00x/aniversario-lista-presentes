<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\View;

class ErrorController {
    public function notFound(array $params = []): void {
        http_response_code(404);
        // D-08: full Bootstrap layout, D-10: specific buttons
        View::render('public/404', ['title' => '404 — Página não encontrada', 'layout' => 'main']);
    }

    public function forbidden(array $params = []): void {
        http_response_code(403);
        // D-08: full Bootstrap layout, D-09: specific message and Voltar button
        View::render('public/403', ['title' => '403 — Acesso negado', 'layout' => 'main']);
    }

    public function serverError(array $params = []): void {
        http_response_code(500);
        // D-08: full Bootstrap layout — no stack trace, no exception details
        View::render('public/500', ['title' => '500 — Erro interno', 'layout' => 'main']);
    }
}
