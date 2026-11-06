<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require __DIR__.'/Recovery.php';

try {
    $args=getopt('', ['package:','mode:','approved-sha256:']);
    $mode=$args['mode'] ?? 'plan';
    if(!in_array($mode,['plan','init-ledger','apply','rollback'],true))throw new RuntimeException('Unknown mode');
    $package=HistoricalGiftRecovery::package($args['package'] ?? '');
    if($mode!=='plan' && !hash_equals($package['id'],$args['approved-sha256'] ?? ''))throw new RuntimeException('Exact reviewed package hash is required for writes');
    $dsn=getenv('RECOVERY_DSN') ?: '';
    if(!str_starts_with($dsn,'mysql:') || !preg_match('/(?:^|;)dbname=([A-Za-z0-9_]+)(?:;|$)/',$dsn,$m))throw new RuntimeException('Explicit RECOVERY_DSN with database is required');
    if($mode!=='plan' && (getenv('RECOVERY_ALLOWED_DATABASE') ?: '')!==$m[1])throw new RuntimeException('Explicit target database allowlist is required for writes');
    if($mode==='apply'){
        $root=realpath(getenv('RECOVERY_PUBLIC_ROOT') ?: '');
        if(!$root)throw new RuntimeException('Verified target public root is required before data import');
        foreach($package['data']['assets'] as $asset){
            $file=realpath($root.$asset['path']);
            if(!$file || !str_starts_with($file,$root.DIRECTORY_SEPARATOR) || !hash_equals($asset['sha256'],hash_file('sha256',$file)))throw new RuntimeException('Target images missing/different; install reviewed images first');
        }
    }
    $db=new PDO($dsn,getenv('RECOVERY_DB_USER') ?: '',getenv('RECOVERY_DB_PASSWORD') ?: '');
    if($db->query('SELECT DATABASE()')->fetchColumn()!==$m[1])throw new RuntimeException('Unexpected target database');
    $recovery=new HistoricalGiftRecovery($db);
    $result=match($mode){
        'plan'=>$recovery->plan($package),
        'init-ledger'=>(function()use($recovery){$recovery->initializeLedger();return ['state'=>'ledger_initialized'];})(),
        'apply'=>$recovery->apply($package),
        'rollback'=>$recovery->rollback($package),
    };
    echo json_encode($result,JSON_THROW_ON_ERROR|JSON_PRETTY_PRINT),PHP_EOL;
} catch(Throwable $e) {
    // Database connection errors may contain environment details. Never print them.
    fwrite(STDERR, $e instanceof PDOException ? "Recovery failed: database operation rejected.\n" : 'Recovery failed: '.$e->getMessage()."\n");
    exit(1);
}
