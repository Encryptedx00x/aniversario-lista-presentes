<?php
declare(strict_types=1);
require dirname(__DIR__).'/scripts/recovery/Recovery.php';
if(PHP_SAPI!=='cli')exit(1);
$db=new PDO(getenv('RECOVERY_TEST_DSN') ?: '',getenv('RECOVERY_TEST_USER') ?: '',getenv('RECOVERY_TEST_PASSWORD') ?: '',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
if($db->query('SELECT DATABASE()')->fetchColumn()!=='dyogo_recovery_test')throw new RuntimeException('Test refused: isolated database name required');
$package=HistoricalGiftRecovery::package(getenv('RECOVERY_TEST_PACKAGE') ?: '');
$db->exec("SET time_zone='-03:00'");
$recovery=new HistoricalGiftRecovery($db);$checks=0;
function ok(bool $value,string $label):void{global $checks;++$checks;if(!$value)throw new RuntimeException($label);echo "PASS $label\n";}
function denied(callable $fn,string $label):void{try{$fn();}catch(Throwable $e){ok(true,$label);return;}throw new RuntimeException('Expected rejection: '.$label);}
function countRows(string $table):int{global $db;return (int)$db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();}
function resetTest():void{
    global $db;
    // This connection is guarded above and has no route to production in the test container.
    $db->exec('SET FOREIGN_KEY_CHECKS=0');
    foreach($db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $table){
        if(!preg_match('/^[A-Za-z0-9_]+$/D',$table))throw new RuntimeException('Invalid test table');
        $db->exec("DROP TABLE `$table`");
    }
    $db->exec('SET FOREIGN_KEY_CHECKS=1');
    $db->exec((string)file_get_contents(dirname(__DIR__).'/sql/schema.sql'));
}
function insertGift(array $gift):void{global $db;$fields=HistoricalGiftRecovery::FIELDS;$q=$db->prepare('INSERT INTO gifts (`'.implode('`,`',$fields).'`) VALUES ('.implode(',',array_fill(0,count($fields),'?')).')');$q->execute(array_map(fn($f)=>$gift[$f],$fields));}

ok($db->query('SELECT @@time_zone')->fetchColumn()==='+00:00','import session uses UTC even when target session started in Brazil timezone');
resetTest();
$plan=$recovery->plan($package);
ok(count($plan['insert'])===26 && countRows('gifts')===0,'plan is read-only and finds 26 inserts');
ok($plan['image_references']===0,'plan reports zero initial image references');
ok((int)$db->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME LIKE 'dyogo_recovery_%'")->fetchColumn()===0,'plan creates no ledger');
denied(fn()=>$recovery->apply($package),'apply without explicit ledger preparation fails');
ok(countRows('gifts')===0,'failed apply inserts nothing');
$recovery->initializeLedger();$recovery->initializeLedger();
$result=$recovery->apply($package);
ok($result['inserted']===26 && countRows('gifts')===26,'imports all 26 gifts');
ok(array_map('intval',$db->query('SELECT id FROM gifts ORDER BY id')->fetchAll(PDO::FETCH_COLUMN))===array_column($package['data']['gifts'],'id'),'preserves original IDs including gap at 4');
foreach($package['data']['gifts'] as $gift){
    $q=$db->prepare('SELECT * FROM gifts WHERE id=?');$q->execute([$gift['id']]);$row=$q->fetch();
    foreach(HistoricalGiftRecovery::FIELDS as $field)ok($row[$field]===null ? $gift[$field]===null : (string)$row[$field]===(string)$gift[$field],"gift {$gift['id']} field $field preserved");
}
ok(countRows('users')===0 && countRows('reservations')===0 && countRows('payments')===0,'no accounts, live reservations or payments imported');
$archive=json_decode($db->query('SELECT payload_json FROM dyogo_recovery_batches')->fetchColumn(),true,64,JSON_THROW_ON_ERROR);
ok($archive['historical_reservations']===$package['data']['historical_reservations'],'preserves all six reservations and original gift/user references in archive');
ok($recovery->apply($package)['state']==='already_applied' && countRows('dyogo_recovery_batches')===1 && countRows('dyogo_recovery_rows')===26,'repeated apply does not duplicate data or receipts');
$changed=$package['data']['gifts'][0];$id=$changed['id'];
$db->exec("UPDATE gifts SET name='Later owner edit' WHERE id=$id");
denied(fn()=>$recovery->apply($package),'rerun rejects owner edits');
denied(fn()=>$recovery->rollback($package),'rollback rejects owner edits');
ok(countRows('gifts')===26,'blocked rollback leaves all data intact');
$q=$db->prepare('UPDATE gifts SET name=?,updated_at=? WHERE id=?');$q->execute([$changed['name'],$changed['updated_at'],$id]);
$db->exec("INSERT INTO users(id,name,email,password_hash,role) VALUES (99,'Isolated test','fixture@example.invalid','not-a-login-hash','guest')");
$db->exec("INSERT INTO reservations(id,gift_id,user_id,status) VALUES (999,$id,99,'active')");
denied(fn()=>$recovery->rollback($package),'rollback rejects a new live relationship');
ok(countRows('reservations')===1 && countRows('gifts')===26,'new reservation is preserved');
$db->exec('DELETE FROM reservations WHERE id=999');
$other=$package['data']['gifts'][0];$other['id']=1000;$other['slug']='later-unrelated-gift';insertGift($other);
ok($recovery->rollback($package)['deleted']===26 && countRows('gifts')===1,'rollback removes only owned rows and keeps later unrelated gift');
ok($recovery->plan($package)['image_references']===1,'post-rollback image check detects later gift reusing recovered image');
ok($recovery->rollback($package)['state']==='already_rolled_back','rollback is idempotent');
ok(countRows('dyogo_recovery_batches')===1,'rollback retains provenance and archive');
denied(fn()=>$recovery->apply($package),'rolled-back batch requires a fresh review');

resetTest();$recovery->initializeLedger();insertGift($package['data']['gifts'][0]);
ok($recovery->apply($package)['inserted']===25,'identical preexisting row is not replaced');
ok($recovery->rollback($package)['deleted']===25 && countRows('gifts')===1,'rollback preserves preexisting identical row');

resetTest();$recovery->initializeLedger();$conflict=$package['data']['gifts'][0];$conflict['name']='Existing owner data';insertGift($conflict);
denied(fn()=>$recovery->apply($package),'ID conflict fails before any import');
ok(countRows('gifts')===1 && countRows('dyogo_recovery_batches')===0,'ID conflict leaves no batch or partial import');
resetTest();$recovery->initializeLedger();$conflict['id']=999;insertGift($conflict);
denied(fn()=>$recovery->apply($package),'slug conflict fails closed');
ok(countRows('gifts')===1,'slug conflict preserves existing row');

resetTest();$recovery->initializeLedger();
$db->exec("CREATE TRIGGER injected_failure BEFORE INSERT ON gifts FOR EACH ROW BEGIN IF NEW.id=5 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Injected test failure'; END IF; END");
denied(fn()=>$recovery->apply($package),'mid-import SQL failure is surfaced');
ok(countRows('gifts')===0 && countRows('dyogo_recovery_batches')===0 && countRows('dyogo_recovery_rows')===0,'transaction rolls back gifts, archive and receipts together');

resetTest();$db->exec('ALTER TABLE gifts DROP COLUMN redirect_url');
denied(fn()=>$recovery->plan($package),'schema drift is rejected');
resetTest();$recovery->initializeLedger();
$db->exec('ALTER TABLE dyogo_recovery_rows ADD unknown_column INT');
denied(fn()=>$recovery->initializeLedger(),'ledger schema drift is rejected');

resetTest();$recovery->initializeLedger();
$env=getenv();$env['RECOVERY_DSN']=getenv('RECOVERY_TEST_DSN');$env['RECOVERY_DB_USER']=getenv('RECOVERY_TEST_USER');$env['RECOVERY_DB_PASSWORD']=getenv('RECOVERY_TEST_PASSWORD');$env['RECOVERY_ALLOWED_DATABASE']='dyogo_recovery_test';$env['RECOVERY_PUBLIC_ROOT']=(getenv('RECOVERY_TEST_PACKAGE') ?: '').'/assets';
$command=[PHP_BINARY,dirname(__DIR__).'/scripts/recovery/recover.php','--mode=apply','--package='.getenv('RECOVERY_TEST_PACKAGE'),'--approved-sha256='.$package['id']];
$jobs=[];
for($i=0;$i<2;$i++){
    $pipes=[];$process=proc_open($command,[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes,null,$env);
    fclose($pipes[0]);$jobs[]=[$process,$pipes];
}
$states=[];
foreach($jobs as [$process,$pipes]){
    $output=stream_get_contents($pipes[1]);$errors=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);$exit=proc_close($process);
    ok($exit===0,'concurrent CLI apply succeeds without exposing credentials');
    $states[]=json_decode($output,true,32,JSON_THROW_ON_ERROR)['state'];
}
sort($states);ok($states===['already_applied','applied'] && countRows('gifts')===26 && countRows('dyogo_recovery_batches')===1,'concurrent import serialized to one batch');
$withoutApproval=array_slice($command,0,-1);$pipes=[];$process=proc_open($withoutApproval,[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes,null,$env);
fclose($pipes[0]);stream_get_contents($pipes[1]);$error=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
ok(proc_close($process)!==0 && str_contains($error,'reviewed package hash'),'CLI refuses writes without reviewed hash');
ok(countRows('gifts')===26,'denied CLI does not change imported data');
$db->exec('DELETE FROM dyogo_recovery_rows ORDER BY gift_id LIMIT 1');
denied(fn()=>$recovery->rollback($package),'incomplete rollback receipts fail closed');
ok(countRows('gifts')===26,'damaged receipts cannot cause partial rollback');
echo "TOTAL $checks checks passed on isolated MariaDB; production was not contacted.\n";
