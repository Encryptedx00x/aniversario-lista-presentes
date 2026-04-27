<?php
declare(strict_types=1);

/**
 * Phase 4 Pix contract test (framework-free).
 * Run: php tests/phase4_pix_contract_test.php
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

$routes = require $root . '/config/routes.php';
assertTrue(isset($routes['GET']['/pix']), 'Route GET /pix exists');
assertTrue(isset($routes['POST']['/pix/contribute']), 'Route POST /pix/contribute exists');

$pixController = file_get_contents($root . '/app/Controllers/PixController.php');
assertTrue($pixController !== false, 'PixController source is readable');
assertTrue(str_contains($pixController, 'function index'), 'PixController::index exists');
assertTrue(str_contains($pixController, 'function contribute'), 'PixController::contribute exists');
assertTrue(str_contains($pixController, '(new ContributionModel)->create'), 'PixController writes contribution records');
assertTrue(str_contains($pixController, 'gift_id'), 'PixController handles optional gift_id for Pix-gift flow');
assertTrue(str_contains($pixController, '(new ReservationModel)->reserve'), 'PixController can reserve gift when contributing via Pix');

$pixView = file_get_contents($root . '/app/Views/public/pix/index.php');
assertTrue($pixView !== false, 'Pix view is readable');
assertTrue(str_contains($pixView, 'action="/pix/contribute"'), 'Pix form posts to /pix/contribute');
assertTrue(str_contains($pixView, 'name="csrf_token"'), 'Pix form includes CSRF token input');
assertTrue(str_contains($pixView, 'name="guest_name"'), 'Pix form includes guest_name');
assertTrue(str_contains($pixView, 'name="guest_email"'), 'Pix form includes guest_email');
assertTrue(str_contains($pixView, 'name="amount"'), 'Pix form includes amount');
assertTrue(str_contains($pixView, 'name="gift_id"'), 'Pix form includes optional gift_id when selected');

$contributionModel = file_get_contents($root . '/app/Models/ContributionModel.php');
assertTrue($contributionModel !== false, 'ContributionModel source is readable');
assertTrue(str_contains($contributionModel, 'INSERT INTO contributions'), 'ContributionModel inserts into contributions table');
assertTrue(str_contains($contributionModel, ':confirmed') && str_contains($contributionModel, '=> 0'), 'ContributionModel stores contribution as pending');

fwrite(STDOUT, "\nPhase 4 Pix contract checks passed.\n");
