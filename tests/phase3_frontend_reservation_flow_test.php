<?php
declare(strict_types=1);

/**
 * Phase 3 frontend reservation flow contract test (framework-free).
 * Run: php tests/phase3_frontend_reservation_flow_test.php
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

$appJsPath = $root . '/public/assets/js/app.js';
$giftsViewPath = $root . '/app/Views/public/gifts/index.php';

$appJs = file_get_contents($appJsPath);
assertTrue($appJs !== false, 'app.js is readable');

$giftsView = file_get_contents($giftsViewPath);
assertTrue($giftsView !== false, 'gifts/index.php is readable');

assertTrue(str_contains($appJs, "if (loggedIn !== '1')"), 'initChooseGift has guest-auth branch');
assertTrue(str_contains($appJs, 'this._reserveGift(btn, giftId);'), 'logged guest flow calls _reserveGift');
assertTrue(!str_contains($appJs, 'console.log'), 'reservation click flow no longer uses console.log stub');

assertTrue(str_contains($appJs, "fetch('/reserve'"), 'reserve flow posts to /reserve endpoint');
assertTrue(str_contains($appJs, "method: 'POST'"), 'reserve flow uses POST');
assertTrue(str_contains($appJs, 'gift_id: String(giftId)'), 'reserve payload includes gift_id');
assertTrue(str_contains($appJs, "csrf_token: csrfToken"), 'reserve payload includes csrf_token');

assertTrue(str_contains($appJs, 'this._fetchFreshCsrfToken()'), '_onAuthSuccess refreshes CSRF token');
assertTrue(str_contains($appJs, "fetch('/csrf-token'"), '_fetchFreshCsrfToken requests /csrf-token');
assertTrue(str_contains($appJs, 'btn.click();'), '_onAuthSuccess re-clicks pending gift button');

assertTrue(str_contains($appJs, 'qtyText.textContent = `Disponível: ${availableQty}`;'), 'reserve success updates available quantity in DOM');
assertTrue(str_contains($appJs, "btn.textContent = 'Indisponível';"), 'reserve success/error can switch button to Indisponível');
assertTrue(str_contains($appJs, 'if (data?.is_exhausted)'), 'reserve error handles race-condition exhausted response');
assertTrue(str_contains($appJs, 'this._showReserveToast(message, true);'), 'reserve error shows error toast');

assertTrue(str_contains($giftsView, 'id="reserve-toast"'), 'gifts view contains reservation toast container');
assertTrue(str_contains($giftsView, 'id="reserve-toast-message"'), 'gifts view contains reservation toast message placeholder');
assertTrue(str_contains($giftsView, 'href="/my-gifts"'), 'reservation toast contains "Ver meus presentes" link target');

fwrite(STDOUT, "\nPhase 3 frontend reservation flow contract checks passed.\n");
