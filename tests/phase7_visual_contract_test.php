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
assertTrue(str_contains($css, '--bg: #ffffff'), 'Visual palette includes white background token');
assertTrue(str_contains($css, '--text: #111116'), 'Visual palette includes near-black text token');
assertTrue(str_contains($css, '--purple:'), 'Visual palette includes purple token');
assertTrue(str_contains($css, '.site-header'), 'app.css styles custom modern header');
assertTrue(str_contains($css, '.card:hover'), 'app.css includes card hover animation');
assertTrue(str_contains($css, '@media (prefers-reduced-motion: reduce)'), 'app.css handles reduced motion accessibility');
assertTrue(str_contains($css, '@keyframes fade-slide'), 'app.css includes bespoke entrance animation');

$layout = file_get_contents($root . '/app/Views/layouts/main.php');
assertTrue($layout !== false, 'Main layout is readable');
assertTrue(str_contains($layout, 'site-header'), 'Main layout uses custom header wrapper');
assertTrue(str_contains($layout, 'site-nav'), 'Main layout uses custom nav structure');

$dashboard = file_get_contents($root . '/app/Views/admin/dashboard.php');
assertTrue($dashboard !== false, 'Admin dashboard is readable');
assertTrue(str_contains($dashboard, 'dashboard-metric-card'), 'Dashboard cards expose visual refactor hook class');

fwrite(STDOUT, "\nPhase 7 visual contract checks passed.\n");
