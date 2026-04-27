<?php
declare(strict_types=1);

/**
 * Phase 5 dashboard contract test (framework-free).
 * Run: php tests/phase5_dashboard_contract_test.php
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

$controller = file_get_contents($root . '/app/Controllers/AdminAuthController.php');
assertTrue($controller !== false, 'AdminAuthController source is readable');
assertTrue(str_contains($controller, 'AdminStatsModel'), 'AdminAuthController imports AdminStatsModel');
assertTrue(str_contains($controller, 'getDashboardStats'), 'Dashboard action loads dashboard stats');
assertTrue(str_contains($controller, "'stats'  => \$stats"), 'Dashboard action passes stats to view');

$model = file_get_contents($root . '/app/Models/AdminStatsModel.php');
assertTrue($model !== false, 'AdminStatsModel source is readable');
assertTrue(str_contains($model, 'SELECT COUNT(*) AS c FROM gifts'), 'AdminStatsModel counts total gifts');
assertTrue(str_contains($model, 'available_qty > 0'), 'AdminStatsModel counts available gifts');
assertTrue(str_contains($model, 'available_qty <= 0'), 'AdminStatsModel counts sold out gifts');
assertTrue(str_contains($model, 'FROM reservations'), 'AdminStatsModel counts reservations');
assertTrue(str_contains($model, 'FROM contributions'), 'AdminStatsModel counts pix contributions');

$view = file_get_contents($root . '/app/Views/admin/dashboard.php');
assertTrue($view !== false, 'Admin dashboard view is readable');
assertTrue(str_contains($view, 'Esgotados'), 'Dashboard shows sold-out metric card');
assertTrue(str_contains($view, '<?= $e($giftsTotal) ?>'), 'Dashboard renders dynamic total gifts');
assertTrue(str_contains($view, '<?= $e($reservationsTotal) ?>'), 'Dashboard renders dynamic reservations');
assertTrue(str_contains($view, '<?= $e($pixTotal) ?>'), 'Dashboard renders dynamic pix contributions');

fwrite(STDOUT, "\nPhase 5 dashboard contract checks passed.\n");
