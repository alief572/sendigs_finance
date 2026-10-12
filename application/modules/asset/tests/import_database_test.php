<?php
// Must run inside the app container. Only disposable tables receive writes.
define('BASEPATH',dirname(__DIR__,4).'/system/'); define('ENVIRONMENT','development');
require dirname(__DIR__,3).'/config/development/database.php';
require dirname(__DIR__,3).'/libraries/Asset_import_store.php';
require __DIR__.'/../migrations/import_schema.php';
function assert_import($ok,$message) { if (!$ok) { throw new RuntimeException($message); } }
function connect_import($name) { global $db; $c=$db['default']; $m=new mysqli($c['hostname'],$c['username'],$c['password'],$name); $m->set_charset('utf8mb4'); return $m; }
function masters_import() { return (new Asset_import())->masters(['category'=>[['id'=>1,'name'=>'GEDUNG'],['id'=>3,'name'=>'ALAT']], 'company'=>[['id'=>1,'name'=>'Company']], 'department'=>[]]); }
if (($argv[1] ?? '')==='worker') {
    if (!preg_match('/^asset_import_test_[a-f0-9]+$/D',$argv[2])) { exit(2); }
    $store=new Asset_import_store(connect_import($argv[2]));
    try { echo json_encode($store->save($argv[3],'tester','100','masters_import')); }
    catch (Throwable $e) { fwrite(STDERR,$e->getMessage()); exit(1); }
    exit;
}
$live=connect_import($db['default']['database']); $name='asset_import_test_'.bin2hex(random_bytes(6));
$source=$db['default']['database'];
assert_import((bool)$live->query('CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4'),'create disposable DB');
try {
    $test=connect_import($name);
    foreach (['asset','asset_generate'] as $table) { assert_import((bool)$test->query('CREATE TABLE `'.$table.'` LIKE `'.str_replace('`','``',$source).'`.`'.$table.'`'),'clone schema'); }
    // The source may already have the rollout index; remove only from this disposable clone for preflight testing.
    $cloneIndexes=$test->query('SHOW INDEX FROM asset')->fetch_all(MYSQLI_ASSOC); $unique=[];
    foreach ($cloneIndexes as $index) { if (!$index['Non_unique']) { $unique[$index['Key_name']][]=$index['Column_name']; } }
    foreach ($unique as $key=>$columns) { if ($key!=='PRIMARY' && $columns===['kd_asset']) { assert_import((bool)$test->query('ALTER TABLE asset DROP INDEX `'.str_replace('`','``',$key).'`'),'remove clone-only index'); } }
    // Prove duplicate preflight exits before adding any index/table.
    $test->query("INSERT INTO asset (id,kd_asset) VALUES (1,'duplicate'),(2,'duplicate')");
    try { migrate_asset_import($test); throw new LogicException('duplicate accepted'); } catch (RuntimeException $e) { assert_import(!($e instanceof LogicException),'duplicate preflight'); }
    assert_import($test->query("SHOW TABLES LIKE 'asset_import_batches'")->num_rows===0,'no batch DDL on duplicate');
    $test->query('DROP TABLE asset'); $test->query('CREATE TABLE asset LIKE `'.str_replace('`','``',$source).'`.asset');
    migrate_asset_import($test); migrate_asset_import($test);
    $store=new Asset_import_store($test); assert_import($store->ready(),'migration/readiness');
    $rules=new Asset_import(); $masters=masters_import();
    $validated=$rules->validate([2=>['Laptop','ALAT','Company','','31/01/2026',3,3000000,4],3=>['Gedung','GEDUNG','Company','','2026-10-12',1,18000000,20]],$masters);
    $token=$store->preview($validated,'tester','100');
    foreach ([['other','100'],['tester','101']] as $owner) {
        try { $store->save($token,$owner[0],$owner[1],'masters_import'); throw new LogicException('owner accepted'); } catch (Asset_import_failure $e) {}
    }
    $changed=$masters; unset($changed['category']['ALAT']);
    try { $store->save($token,'tester','100',function()use($changed){return $changed;}); throw new LogicException('changed accepted'); } catch (Asset_import_failure $e) { assert_import(count($e->errors)===1,'master error'); }
    assert_import($test->query('SELECT * FROM asset')->num_rows===0,'no writes on changed master');
    $result=$store->save($token,'tester','100','masters_import');
    assert_import($result['ringkasan']['unit']===4,'unit summary');
    assert_import($test->query('SELECT * FROM asset')->num_rows===4,'4 unit records');
    assert_import($test->query('SELECT * FROM asset_generate')->num_rows===384,'48*3 + 240 schedules');
    $unit=$test->query("SELECT * FROM asset WHERE kd_asset='AST-100202601-03-00101'")->fetch_assoc();
    assert_import($unit && (int)$unit['nilai_asset']===3000000 && (int)$unit['value']===62500 && $unit['lokasi_asset']==='' && $unit['cost_center']==='','unit values');
    $schedule=$test->query("SELECT * FROM asset_generate WHERE kd_asset='AST-100202601-03-00101' ORDER BY tahun,bulan")->fetch_all(MYSQLI_ASSOC);
    assert_import($schedule[0]['bulan']==='01' && $schedule[47]['bulan']==='12' && $schedule[47]['tahun']==='2029','monthly starts acquisition month');
    assert_import($store->save($token,'tester','100',function(){throw new RuntimeException('must not revalidate completed');})['replayed'],'idempotent');
    assert_import($test->query('SELECT * FROM asset')->num_rows===4,'no duplicate retry');
    $expired=$store->preview($validated,'tester','100'); $test->query("UPDATE asset_import_batches SET expires_at='2000-01-01' WHERE token='$expired'");
    try { $store->save($expired,'tester','100','masters_import'); throw new LogicException('expiry accepted'); } catch (Asset_import_failure $e) {}
    // Inject an actual schedule write failure and verify the earlier unit inserts roll back.
    $failed=$store->preview($validated,'tester','100');
    assert_import((bool)$test->query("CREATE TRIGGER fail_schedule BEFORE INSERT ON asset_generate FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='test schedule failure'"),'create failure trigger');
    try { $store->save($failed,'tester','100','masters_import'); throw new LogicException('failure accepted'); } catch (RuntimeException $e) { assert_import(!($e instanceof LogicException),'write fails'); }
    assert_import($test->query('SELECT * FROM asset')->num_rows===4 && $test->query('SELECT * FROM asset_generate')->num_rows===384,'whole batch rollback');
    $test->query('DROP TRIGGER fail_schedule');
    // Force a unique-code conflict after allocation, also via a DB trigger.
    $conflict=$store->preview($validated,'tester','100');
    $test->query("CREATE TRIGGER force_conflict BEFORE INSERT ON asset FOR EACH ROW SET NEW.kd_asset='AST-100202601-03-00101'");
    try { $store->save($conflict,'tester','100','masters_import'); throw new LogicException('conflict accepted'); } catch (RuntimeException $e) { assert_import(!($e instanceof LogicException),'unique conflict'); }
    assert_import($test->query('SELECT * FROM asset')->num_rows===4,'conflict rollback'); $test->query('DROP TRIGGER force_conflict');
    // Parallel independent workers prove branch allocation and same-token retry.
    foreach ([false,true] as $sameToken) {
        $a=$store->preview($validated,'tester','100'); $b=$sameToken ? $a : $store->preview($validated,'tester','100');
        $workers=[];
        foreach ([$a,$b] as $workerToken) {
            $pipes=[]; $process=proc_open([PHP_BINARY,__FILE__,'worker',$name,$workerToken],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);
            fclose($pipes[0]); $workers[]=[$process,$pipes];
        }
        foreach ($workers as $worker) {
            $stdout=stream_get_contents($worker[1][1]); $stderr=stream_get_contents($worker[1][2]); fclose($worker[1][1]); fclose($worker[1][2]);
            assert_import(proc_close($worker[0])===0 && json_decode($stdout,true)['status']===1,'concurrency '.$stderr);
        }
    }
    assert_import($test->query('SELECT * FROM asset')->num_rows===16,'parallel allocations + same token writes once');
    $maximum=$rules->validate(array_fill(2,500,['Bulk','ALAT','Company','','2026-11-01',2,120,20]),$masters);
    assert_import(!$maximum['errors'] && $maximum['summary']['unit']===1000,'500 rows/1000 units accepted');
    $maxToken=$store->preview($maximum,'tester','100'); $store->save($maxToken,'tester','100','masters_import');
    assert_import($test->query("SELECT * FROM asset WHERE tgl_perolehan='2026-11-01'")->num_rows===1000,'maximum unit writes');
    assert_import((int)$test->query("SELECT COUNT(*) AS n FROM asset_generate WHERE kd_asset LIKE 'AST-100202611-%'")->fetch_assoc()['n']===240000,'chunked maximum schedules');
    assert_import((int)$test->query("SELECT value FROM asset WHERE kd_asset='AST-100202611-03-00101'")->fetch_assoc()['value']===1,'half rupiah rounds up like manual');
    $test->query("INSERT INTO asset (kd_asset,category,kdcab) VALUES ('AST-101202601-03-99901',3,'101')");
    $exhausted=$store->preview($validated,'tester','101');
    try { $store->save($exhausted,'tester','101','masters_import'); throw new LogicException('sequence exhaustion accepted'); } catch (Asset_import_failure $e) {}
    assert_import($test->query("SELECT * FROM asset WHERE kdcab='101'")->num_rows===1,'sequence exhaustion no write');
    echo "Disposable DB: migration, master/owner/expiry, schedules, rollback, conflicts, idempotency and concurrency OK\n";
} finally {
    if (isset($test)) { $test->close(); }
    assert_import((bool)$live->query('DROP DATABASE `'.$name.'`'),'cleanup disposable DB'); $live->close();
}
