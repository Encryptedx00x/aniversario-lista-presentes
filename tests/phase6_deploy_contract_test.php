<?php
declare(strict_types=1);

/**
 * Phase 6 deploy contract test (framework-free).
 * Run: php tests/phase6_deploy_contract_test.php
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

$readme = file_get_contents($root . '/README.md');
assertTrue($readme !== false, 'README.md exists');
assertTrue(str_contains($readme, 'Deploy na Hostinger'), 'README includes Hostinger deploy section');
assertTrue(str_contains($readme, 'sql/schema.sql'), 'README references schema import');
assertTrue(str_contains($readme, 'sql/seed.sql'), 'README references seed import');

$schema = file_get_contents($root . '/sql/schema.sql');
assertTrue($schema !== false, 'schema.sql exists');
assertTrue(str_contains($schema, 'purchase_url'), 'schema.sql includes gifts.purchase_url');
assertTrue(str_contains($schema, 'available_qty'), 'schema.sql includes available_qty');
assertTrue(str_contains($schema, 'fk_res_user'), 'schema.sql includes reservations.user_id FK');

$seed = file_get_contents($root . '/sql/seed.sql');
assertTrue($seed !== false, 'seed.sql exists');
assertTrue(str_contains($seed, 'INSERT INTO users'), 'seed.sql inserts users');
assertTrue(str_contains($seed, 'INSERT INTO gifts'), 'seed.sql inserts gifts');
assertTrue(str_contains($seed, 'INSERT INTO settings'), 'seed.sql inserts settings');

fwrite(STDOUT, "\nPhase 6 deploy contract checks passed.\n");
