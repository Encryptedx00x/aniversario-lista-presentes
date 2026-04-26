<?php
declare(strict_types=1);

/**
 * Phase 7 visual contract test (framework-free).
 * Run: php tests/phase7_visual_contract_test.php
 */

$root = dirname(__DIR__);

function fail(string $message): never {
    fwrite(STDERR, "[FAIL] {$message}\n");
    exit(1);
}

function assertTrue(bool $condition, string $message): void {
    if (!$condition) {
        fail($message);
    }
    fwrite(STDOUT, "[OK] {$message}\n");
}

$css = file_get_contents($root . '/public/assets/css/app.css');
assertTrue($css !== false, 'app.css is readable');
assertTrue(str_contains($css, '--ui-bg: #ffffff'), 'Visual palette includes white background token');
assertTrue(str_contains($css, '--ui-text: #0f0f14'), 'Visual palette includes near-black text token');
assertTrue(str_contains($css, '--ui-purple:'), 'Visual palette includes purple token');
assertTrue(str_contains($css, '.app-navbar'), 'app.css styles modern navbar');
assertTrue(str_contains($css, '.card:hover'), 'app.css includes card hover animation');
assertTrue(str_contains($css, '@media (prefers-reduced-motion: reduce)'), 'app.css handles reduced motion accessibility');

$layout = file_get_contents($root . '/app/Views/layouts/main.php');
assertTrue($layout !== false, 'Main layout is readable');
assertTrue(str_contains($layout, 'app-navbar'), 'Main layout navbar uses visual refactor class');

$dashboard = file_get_contents($root . '/app/Views/admin/dashboard.php');
assertTrue($dashboard !== false, 'Admin dashboard is readable');
assertTrue(str_contains($dashboard, 'dashboard-metric-card'), 'Dashboard cards expose visual refactor hook class');

fwrite(STDOUT, "\nPhase 7 visual contract checks passed.\n");
