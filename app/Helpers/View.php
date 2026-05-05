<?php
declare(strict_types=1);

namespace App\Helpers;

class View {
    public static function render(string $template, array $data = []): void {
        extract($data, EXTR_SKIP);
        $e      = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        $allowed = ['main', 'admin', 'admin_bare'];
        $layout  = in_array($data['layout'] ?? 'main', $allowed, true)
            ? ($data['layout'] ?? 'main')
            : 'main';
        ob_start();
        require dirname(__DIR__) . "/Views/{$template}.php";
        $content = ob_get_clean();
        require dirname(__DIR__) . "/Views/layouts/{$layout}.php";
    }
}
