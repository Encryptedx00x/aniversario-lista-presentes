<?php
declare(strict_types=1);

/**
 * Phase 5 gift purchase-link + Pix equivalence contract test.
 * Run: php tests/phase5_gift_purchase_flow_contract_test.php
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

$migration = file_get_contents($root . '/sql/migrate_phase5_purchase_url.sql');
assertTrue($migration !== false, 'Phase 5 purchase_url migration exists');
assertTrue(str_contains($migration, 'ADD COLUMN purchase_url'), 'Migration adds gifts.purchase_url column');

$giftModel = file_get_contents($root . '/app/Models/GiftModel.php');
assertTrue($giftModel !== false, 'GiftModel source is readable');
assertTrue(str_contains($giftModel, 'purchase_url'), 'GiftModel supports purchase_url persistence');

$adminGiftController = file_get_contents($root . '/app/Controllers/AdminGiftController.php');
assertTrue($adminGiftController !== false, 'AdminGiftController source is readable');
assertTrue(str_contains($adminGiftController, 'purchase_url'), 'AdminGiftController handles purchase_url field');
assertTrue(str_contains($adminGiftController, 'FILTER_VALIDATE_URL'), 'AdminGiftController validates purchase_url format');

$adminForm = file_get_contents($root . '/app/Views/admin/gifts/form.php');
assertTrue($adminForm !== false, 'Admin gift form is readable');
assertTrue(str_contains($adminForm, 'name="purchase_url"'), 'Admin gift form has purchase_url input');

$cards = file_get_contents($root . '/app/Views/public/gifts/_cards.php');
assertTrue($cards !== false, 'Public gift cards view is readable');
assertTrue(str_contains($cards, 'Ver link do produto'), 'Gift cards display product purchase link');
assertTrue(str_contains($cards, '/pix?gift_id='), 'Gift cards provide Pix shortcut for the same gift');

$myGifts = file_get_contents($root . '/app/Views/public/my-gifts/index.php');
assertTrue($myGifts !== false, 'My-gifts view is readable');
assertTrue(str_contains($myGifts, 'Abrir link de compra deste presente'), 'My-gifts shows purchase link for reserved gift');
assertTrue(str_contains($myGifts, 'Dar via Pix'), 'My-gifts includes Pix shortcut action');

$reservationModel = file_get_contents($root . '/app/Models/ReservationModel.php');
assertTrue($reservationModel !== false, 'ReservationModel source is readable');
assertTrue(str_contains($reservationModel, 'g.purchase_url'), 'ReservationModel returns purchase_url in my-gifts query');

fwrite(STDOUT, "\nPhase 5 gift purchase-flow contract checks passed.\n");
