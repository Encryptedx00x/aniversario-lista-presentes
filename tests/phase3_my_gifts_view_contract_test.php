<?php
declare(strict_types=1);

/**
 * Phase 3 "Meus Presentes" view contract test (framework-free).
 * Run: php tests/phase3_my_gifts_view_contract_test.php
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

$myGiftsViewPath = $root . '/app/Views/public/my-gifts/index.php';
$layoutPath = $root . '/app/Views/layouts/main.php';
$reservationControllerPath = $root . '/app/Controllers/ReservationController.php';

$myGiftsView = file_get_contents($myGiftsViewPath);
assertTrue($myGiftsView !== false, 'my-gifts/index.php is readable');

$layout = file_get_contents($layoutPath);
assertTrue($layout !== false, 'layouts/main.php is readable');

$reservationController = file_get_contents($reservationControllerPath);
assertTrue($reservationController !== false, 'ReservationController.php is readable');

// Must-have: list renders required gift metadata.
assertTrue(str_contains($myGiftsView, "gift_name"), 'my-gifts view renders gift name field');
assertTrue(str_contains($myGiftsView, "category"), 'my-gifts view renders category field');
assertTrue(str_contains($myGiftsView, "created_at"), 'my-gifts view renders reservation date field');
assertTrue(str_contains($myGiftsView, "image_path"), 'my-gifts view renders gift thumbnail field');
assertTrue(str_contains($myGiftsView, "status"), 'my-gifts view renders reservation status field');

// Must-have: cancel button only for reserved status.
assertTrue(str_contains($myGiftsView, '$isReserved = $status === \'reserved\';'), 'my-gifts computes reserved-only cancellation condition');
assertTrue(str_contains($myGiftsView, '<?php if ($isReserved): ?>'), 'my-gifts cancel form is wrapped by reserved status condition');
assertTrue(str_contains($myGiftsView, "action=\"/my-gifts/cancel\""), 'my-gifts cancel form posts to /my-gifts/cancel');
assertTrue(str_contains($myGiftsView, 'Cancelar reserva'), 'my-gifts view exposes cancel button label');
assertTrue(str_contains($myGiftsView, 'Esta reserva não pode mais ser cancelada.'), 'my-gifts view hides cancel action for non-reserved statuses');

// Must-have: friendly empty state with CTA back to /gifts.
assertTrue(str_contains($myGiftsView, 'Você ainda não reservou presentes'), 'my-gifts view has friendly empty-state title');
assertTrue(str_contains($myGiftsView, 'href="/gifts"'), 'my-gifts empty state links to /gifts');

// Must-have: nav shows "Meus Presentes" only for guests.
assertTrue(str_contains($layout, "($_SESSION['role'] ?? '') === 'guest'"), 'main nav checks guest role before rendering My Gifts link');
assertTrue(str_contains($layout, 'href="/my-gifts"'), 'main nav contains /my-gifts link target');

// Must-have: unauthenticated access redirect contract in controller/middleware flow.
assertTrue(str_contains($reservationController, "header('Location: /gifts');"), 'ReservationController::myGifts redirects unauthenticated users to /gifts');

fwrite(STDOUT, "\nPhase 3 My Gifts view contract checks passed.\n");
