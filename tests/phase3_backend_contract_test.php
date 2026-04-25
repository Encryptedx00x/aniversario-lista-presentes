<?php
declare(strict_types=1);

/**
 * Phase 3 backend contract test (framework-free).
 * Run: php tests/phase3_backend_contract_test.php
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

assertTrue(isset($routes['POST']['/reserve']), 'Route POST /reserve exists');
assertTrue($routes['POST']['/reserve'][0] === 'ReservationController', 'POST /reserve targets ReservationController');
assertTrue(($routes['POST']['/reserve'][2] ?? null) === 'guest', 'POST /reserve uses guest middleware');

assertTrue(isset($routes['GET']['/csrf-token']), 'Route GET /csrf-token exists');
assertTrue($routes['GET']['/csrf-token'][1] === 'csrfToken', 'GET /csrf-token maps to csrfToken action');

assertTrue(isset($routes['GET']['/my-gifts']), 'Route GET /my-gifts exists');
assertTrue(($routes['GET']['/my-gifts'][2] ?? null) === 'guest', 'GET /my-gifts uses guest middleware');

assertTrue(isset($routes['POST']['/my-gifts/cancel']), 'Route POST /my-gifts/cancel exists');

$authMiddlewareSource = file_get_contents($root . '/app/Middleware/AuthMiddleware.php');
assertTrue($authMiddlewareSource !== false, 'AuthMiddleware source is readable');
assertTrue(str_contains($authMiddlewareSource, 'function requireGuest'), 'AuthMiddleware::requireGuest exists');
assertTrue(str_contains($authMiddlewareSource, 'findById'), 'AuthMiddleware::requireGuest validates guest user still exists');

$reservationModelSource = file_get_contents($root . '/app/Models/ReservationModel.php');
assertTrue($reservationModelSource !== false, 'ReservationModel source is readable');
assertTrue(str_contains($reservationModelSource, 'FOR UPDATE'), 'ReservationModel uses FOR UPDATE lock');
assertTrue(str_contains($reservationModelSource, 'beginTransaction'), 'ReservationModel opens transaction');
assertTrue(str_contains($reservationModelSource, 'rollBack'), 'ReservationModel rolls back on failure');
assertTrue(str_contains($reservationModelSource, 'available_qty = available_qty - 1'), 'ReservationModel decrements available_qty atomically');
assertTrue(str_contains($reservationModelSource, 'INSERT INTO reservations (gift_id, user_id)'), 'ReservationModel insert uses schema-compatible default status');
assertTrue(
    str_contains($reservationModelSource, "CASE WHEN r.status = \\'active\\' THEN \\'reserved\\'")
    || str_contains($reservationModelSource, "CASE WHEN r.status = 'active' THEN 'reserved'"),
    'ReservationModel normalizes legacy active status for UI'
);
assertTrue(str_contains($reservationModelSource, "in_array(\$reservationStatus, ['reserved', 'active']"), 'ReservationModel cancel accepts both reserved and legacy active statuses');
assertTrue(str_contains($reservationModelSource, 'g.name AS gift_name'), 'ReservationModel my-gifts query aliases gift_name for view contract');
assertTrue(str_contains($reservationModelSource, '[ReservationModel::getActiveByUser]'), 'ReservationModel my-gifts query failures are logged and handled');

$reservationControllerSource = file_get_contents($root . '/app/Controllers/ReservationController.php');
assertTrue($reservationControllerSource !== false, 'ReservationController source is readable');
assertTrue(str_contains($reservationControllerSource, 'function store'), 'ReservationController::store exists');
assertTrue(str_contains($reservationControllerSource, 'function myGifts'), 'ReservationController::myGifts exists');
assertTrue(str_contains($reservationControllerSource, 'function cancel'), 'ReservationController::cancel exists');
assertTrue(str_contains($reservationControllerSource, 'function csrfToken'), 'ReservationController::csrfToken exists');
assertTrue(str_contains($reservationControllerSource, "'requires_auth' => true"), 'ReservationController returns requires_auth flag for expired auth');
assertTrue(str_contains($reservationControllerSource, "'Esse presente acabou de ficar indisponível.'") || str_contains($reservationModelSource, "'Esse presente acabou de ficar indisponível.'"), 'Exhausted stock message contract exists');

$migrationSource = file_get_contents($root . '/sql/migrate_phase3.sql');
assertTrue($migrationSource !== false, 'migrate_phase3.sql is readable');
assertTrue(str_contains($migrationSource, 'DROP COLUMN guest_name'), 'Migration removes legacy guest_name column');
assertTrue(str_contains($migrationSource, 'DROP COLUMN guest_email'), 'Migration removes legacy guest_email column');
assertTrue(str_contains($migrationSource, 'MODIFY COLUMN user_id INT UNSIGNED NOT NULL'), 'Migration enforces reservations.user_id NOT NULL');
assertTrue(str_contains($migrationSource, 'ADD CONSTRAINT fk_res_user'), 'Migration adds reservations.user_id FK');

fwrite(STDOUT, "\nPhase 3 backend contract checks passed.\n");
