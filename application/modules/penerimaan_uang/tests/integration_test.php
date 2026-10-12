<?php
require __DIR__ . "/harness.php";
// A worker opens a separate connection to prove the server rejects a stale concurrent preview.
if (($argv[1] ?? '') === '--worker') {
    $payload=json_decode(stream_get_contents(STDIN),true);
    expect(preg_match('/^codex_receipt_test_[a-f0-9]+$/',$payload['database'])===1,'Unsafe worker database');
    $connection=new mysqli('127.0.0.1','root','',$payload['database'],3306); $db=new TestDb($connection);
    $controller=setupController($db); $controller->session->values=$payload['session'];
    $response=save($controller,$payload['post'],$payload['ids'],$payload['cash']);
    echo json_encode($response); exit;
}
$connection=new mysqli('127.0.0.1','root','','',3306);
expect(!$connection->connect_errno,'Local disposable test database unavailable');
$database='codex_receipt_test_'.bin2hex(random_bytes(6));
$connection->query('CREATE DATABASE '.$database.' CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');
$connection->select_db($database); $db=new TestDb($connection);
define('DBCNL',$database);
try {
    $schema=preg_replace('/^--.*$/m','',file_get_contents(__DIR__.'/fixture_schema.sql'));
    foreach(explode(';',$schema) as $sql) if(trim($sql)) $db->query($sql);
    // Split allocation module extends the older dump's status enum with status 8.
    $db->query("ALTER TABLE tr_alokasi_detail MODIFY sts ENUM('0','1','2','3','4','5','6','7','8') NOT NULL DEFAULT '0'");
    $db->query('CREATE TABLE tr_alokasi_split (id INT PRIMARY KEY,id_alokasi_detail INT,nominal DECIMAL(20,2),nilai_terpakai DECIMAL(20,2),jenis_alokasi INT) ENGINE=InnoDB');
    $db->query('CREATE TABLE ms_bank (id INT PRIMARY KEY, coa_bank VARCHAR(30)) ENGINE=InnoDB');
    $db->query('CREATE TABLE coa_master (no_perkiraan VARCHAR(30), nama VARCHAR(100)) ENGINE=InnoDB');
    $db->insert('ms_bank',array('id'=>1,'coa_bank'=>'1101-02-01'));
    foreach(array('1101-02-01'=>'Bank','1102-01-01'=>'Piutang Dagang','7201-01-04'=>'Admin','1106-01-02'=>'PPh Sustain','1106-01-05'=>'PPh Vuca') as $coa=>$name) $db->insert('coa_master',array('no_perkiraan'=>$coa,'nama'=>$name));
    $beforeMigration=setupController($db);
    try { $beforeMigration->Penerimaan_uang_model->require_v2_schema(); throw new RuntimeException('Missing migration accepted'); } catch (RuntimeException $e) { expect(strpos($e->getMessage(),'belum diterapkan')!==false,'Missing migration blocked'); }
    $migration=file_get_contents(dirname(__DIR__).'/migrations/001_receipt_calculation_v2.sql');
    $migration=preg_replace('/^--.*$/m','',$migration);
    foreach(explode(';',$migration) as $sql) if(trim($sql)) $db->query($sql);
    $controller=setupController($db);
    foreach(explode(';',file_get_contents(dirname(__DIR__,2).'/invoicing/tests/source_fixture.sql')) as $sql) if(trim($sql)) $db->query($sql);
    fixture($db,'VUCAEDIT','14925000.00','75000.00',1);
    $db->update('tr_invoicing',array('tagihan_ppn_jurnal'=>'0.00','total_nominal_jurnal'=>'15000000.00','id_actual_plan_tagih'=>'A1','non_kons'=>'0'),array('id'=>'VUCAEDIT'));
    $editResponse=updateVuca($db,'VUCAEDIT'); expect($editResponse['status']===1,'Vuca edit with absent gross field: '.json_encode($editResponse));
    expect(value($db,"SELECT tagihan_ppn_jurnal FROM tr_invoicing WHERE id='VUCAEDIT'")==='15000000.00','Vuca edit gross');
    expect(value($db,"SELECT saldo_piutang FROM tr_invoicing WHERE id='VUCAEDIT'")==='14925000.00','Vuca edit ignores hidden net');
    expect(value($db,"SELECT SUM(debit) FROM tr_jurnal WHERE no_transaksi='VUCAEDIT'")==='15000000.00','Vuca coherent server journal debit');
    $db->query("UPDATE tr_jurnal SET sts='1' WHERE no_transaksi='VUCAEDIT'");
    $snapshot=$db->query("SELECT * FROM tr_jurnal WHERE no_transaksi='VUCAEDIT' ORDER BY id")->result_array();
    expect(updateVuca($db,'VUCAEDIT')['status']===0,'Posted Vuca edit blocked');
    expect($snapshot===$db->query("SELECT * FROM tr_jurnal WHERE no_transaksi='VUCAEDIT' ORDER BY id")->result_array(),'Posted Vuca journal unchanged');
    $db->query("UPDATE tr_jurnal SET sts='0' WHERE no_transaksi='VUCAEDIT'");
    $db->insert('tr_penerimaan_piutang_detail',array('id_inv'=>'VUCAEDIT','penerimaan'=>'100.00'));
    $db->update('tr_invoicing',array('saldo_piutang'=>'14924900.00'),array('id'=>'VUCAEDIT'));
    expect(updateVuca($db,'VUCAEDIT')['status']===0,'Vuca receipt edit blocked');
    expect(value($db,"SELECT saldo_piutang FROM tr_invoicing WHERE id='VUCAEDIT'")==='14924900.00','Vuca receipt balance not reset');
    foreach (array('tr_jurnal','tr_penerimaan_piutang_detail','tr_invoicing') as $table) $db->query('DELETE FROM '.$table);
    // Legacy Vuca gross zero must not replace the actual receivable balance.
    foreach (array('N','Y') as $choice) {
        $vid='VUCA036'.$choice; $aid=$choice==='N' ? 901 : 902;
        fixture($db,$vid,'14925000.00','75000.00',1,'036/VSB/2026-'.$choice);
        $db->update('tr_invoicing',array('tagihan_ppn_jurnal'=>'0.00','total_nominal_jurnal'=>'15000000.00'),array('id'=>$vid));
        allocation($db,$aid,'15000000.00');
        $vp=preview($controller,array($vid),$aid,$choice);
        if ($choice==='Y') {
            expect(save($controller,$vp,array($vid),array('15.000.000,00'))['status']===0,'Vuca deducted overpayment blocked');
            expect(value($db,"SELECT saldo_piutang FROM tr_invoicing WHERE id='".$vid."'")==='14925000.00','Failed Vuca save leaves balance intact');
            $vp=preview($controller,array($vid),$aid,$choice);
        }
        expect(save($controller,$vp,array($vid),array($choice==='N'?'15.000.000,00':'14.925.000,00'))['status']===1,'Vuca settlement '.$choice);
        expect(value($db,"SELECT saldo_piutang FROM tr_invoicing WHERE id='".$vid."'")==='0.00','Vuca zero '.$choice);
        expect(value($db,"SELECT tagihan_ppn FROM tr_penerimaan_piutang_detail WHERE id_inv='".$vid."'")==='15000000.00','Vuca detail effective gross');
        expect(value($db,"SELECT SUM(kredit) FROM tr_jurnal WHERE coa='1102-01-01' AND keterangan LIKE '% - 036/VSB/2026-".$choice."'")==='14925000.00','Vuca receivable credit');
        expect(value($db,"SELECT SUM(kredit) FROM tr_jurnal WHERE coa='1106-01-05' AND keterangan LIKE '% - 036/VSB/2026-".$choice."'")===($choice==='N'?'75000.00':'0.00'),'Vuca PPh credit');
    }
    // Keep the existing regression fixtures independent of these additional receipts.
    foreach (array('tr_jurnal','tr_penerimaan_piutang_detail','tr_penerimaan_piutang','tr_alokasi_split','tr_alokasi_detail','tr_invoicing') as $table) $db->query('DELETE FROM '.$table);
    fixture($db,'MINARTA','25515810.00','468180.00',0,'189/STM/2026'); allocation($db,1,'25983990.00');
    $post=preview($controller,array('MINARTA'),1);
    expect(save($controller,$post,array('MINARTA'),array('23.409.000,00'))['status']===1,'First receipt');
    expect(value($db,"SELECT saldo_piutang FROM tr_invoicing WHERE id='MINARTA'")==='2574990.00','First remaining');
    // Convert the first receipt to legacy form and mark it posted. It still counts toward PPh.
    $db->query('UPDATE tr_penerimaan_piutang_detail SET versi_perhitungan=NULL,penyesuaian_pph=NULL,saldo_piutang_sebelum=NULL');
    $db->query("UPDATE tr_jurnal SET sts='1'");
    $post=preview($controller,array('MINARTA'),1);
    expect(save($controller,$post,array('MINARTA'),array('2.574.990,00'))['status']===1,'MINARTA settlement');
    expect(value($db,"SELECT saldo_piutang FROM tr_invoicing WHERE id='MINARTA'")==='0.00','MINARTA zero');
    expect(value($db,"SELECT SUM(kredit) FROM tr_jurnal WHERE coa='1106-01-02'")==='468180.00','PPh credited once');
    $latest=value($db,'SELECT MAX(id) FROM tr_penerimaan_piutang');
    $controller->input->values=array('id_penerimaan'=>$latest); $controller->rollback_penerimaan();
    expect(json_decode($controller->output->body,true)['status']===1,'Rollback final receipt');
    expect(value($db,"SELECT saldo_piutang FROM tr_invoicing WHERE id='MINARTA'")==='2574990.00','Rollback balance');
    expect(value($db,'SELECT COUNT(*) FROM tr_alokasi_split')==='1','Split preserved');
    expect(value($db,'SELECT nilai_terpakai FROM tr_alokasi_split WHERE id=1')==='23409000.00','Usage restored');
    // Direct POST changing the locked choice is rejected.
    $post=preview($controller,array('MINARTA'),1); $post['pph23_dipotong']='Y';
    expect(save($controller,$post,array('MINARTA'),array('2.574.990,00'))['status']===0,'Choice tampering');
    $post=preview($controller,array('MINARTA'),1);
    $db->failInsertTable='tr_jurnal';
    expect(save($controller,$post,array('MINARTA'),array('2.574.990,00'))['status']===0,'Failure response');
    $db->failInsertTable=null;
    expect(value($db,"SELECT saldo_piutang FROM tr_invoicing WHERE id='MINARTA'")==='2574990.00','Failed transaction atomic');
    expect(value($db,'SELECT COUNT(*) FROM tr_penerimaan_piutang')==='1','No failed header');
    expect(save($controller,$post,array('MINARTA'),array('2.574.990,00'))['status']===1,'Replacement receipt');
    // Independent invoices in one header and both PPh accounts.
    fixture($db,'A','1000.50','20.00'); fixture($db,'B','2000.00','10.00',1); allocation($db,2,'4000.00');
    $post=preview($controller,array('A','B'),2);
    expect(save($controller,$post,array('A','B'),array('1.010,00','2.010,00'),array('10,50','0'))['status']===1,'Multiple invoice settlement');
    expect(value($db,"SELECT saldo_piutang FROM tr_invoicing WHERE id='A'")==='0.00','Admin decimals');
    expect($controller->Penerimaan_uang_model->invoice_state($db->get_where('tr_invoicing',array('id'=>'A'))->row_array())['adjusted_pph']===2000,'Header mapping A');
    expect($controller->Penerimaan_uang_model->invoice_state($db->get_where('tr_invoicing',array('id'=>'B'))->row_array())['adjusted_pph']===1000,'Header mapping B');
    fixture($db,'TINY','1000.00','200.00'); allocation($db,3,'1500.00');
    $post=preview($controller,array('TINY'),3);
    expect(save($controller,$post,array('TINY'),array('100,00'))['status']===1,'Tiny payment');
    expect(value($db,"SELECT saldo_piutang FROM tr_invoicing WHERE id='TINY'")==='1000.00','Tiny payment no negative credit');
    $post=preview($controller,array('TINY'),3);
    expect(save($controller,$post,array('TINY'),array('1.100,00'))['status']===1,'Remaining PPh carried');
    fixture($db,'Y','1000.50','20.00'); allocation($db,4,'1500.00');
    $post=preview($controller,array('Y'),4,'Y');
    expect(save($controller,$post,array('Y'),array('1.000,00'),array('0,50'))['status']===1,'Deducted PPh');
    $controller->input->values=array('id_inv'=>'MINARTA'); $mutations=$db->mutations; $controller->audit_pph();
    expect($db->mutations===$mutations,'Audit read only');
    $auditResponse=json_decode($controller->output->body,true);
    expect(isset($auditResponse['invoices']),'Audit response: '.$controller->output->body);
    expect($auditResponse['invoices'][0]['stored_balance']==='0.00','Audit invoice');
    // Inspect the rendered PHP preview without opening a browser.
    $data=$controller->template->data;
    ob_start(); extract($data); include dirname(__DIR__).'/views/process.php'; $html=ob_get_clean();
    expect(strpos($html,'Penyesuaian PPh Transaksi')!==false && strpos($html,'receipt-item')!==false,'Rendered preview fields');
    // Same PPh account, different invoice numbers in one header must remain separate.
    fixture($db,'MAP1','1000.00','20.00'); fixture($db,'MAP2','1000.00','30.00'); allocation($db,6,'3000.00');
    $post=preview($controller,array('MAP1','MAP2'),6);
    expect(save($controller,$post,array('MAP1','MAP2'),array('100,00','100,00'))['status']===1,'Same COA multi invoice');
    $m=$controller->Penerimaan_uang_model;
    expect($m->invoice_state($db->get_where('tr_invoicing',array('id'=>'MAP1'))->row_array())['adjusted_pph']===2000,'Exact mapping first');
    expect($m->invoice_state($db->get_where('tr_invoicing',array('id'=>'MAP2'))->row_array())['adjusted_pph']===3000,'Exact mapping second');
    $multiHeader=value($db,'SELECT MAX(id) FROM tr_penerimaan_piutang');
    // Sibling split usage and allocation status must survive rollback.
    $db->insert('tr_alokasi_split',array('id'=>60,'id_alokasi_detail'=>6,'nominal'=>'50.00','nilai_terpakai'=>'50.00','jenis_alokasi'=>2));
    $db->query('UPDATE tr_alokasi_detail SET nilai_terpakai=250 WHERE id=6');
    $controller->input->values=array('id_penerimaan'=>$multiHeader); $controller->rollback_penerimaan();
    expect(json_decode($controller->output->body,true)['status']===1,'Multi invoice rollback');
    expect(value($db,'SELECT nilai_terpakai FROM tr_alokasi_split WHERE id=60')==='50.00','Sibling split retained');
    expect(value($db,'SELECT nilai_terpakai FROM tr_alokasi_detail WHERE id=6')==='50.00','Sibling usage retained');
    expect(value($db,'SELECT sts FROM tr_alokasi_detail WHERE id=6')==='8','Split status retained');
    // Legacy allocation uses the bank amount, not an absent split.nominal.
    $db->insert('tr_alokasi_detail',array('id'=>99,'nominal_kredit'=>'1020.00','nominal_debit'=>0,'nilai_terpakai'=>0,'tipe_bank'=>1,'tanggal_transaksi'=>'2026-09-10','sts'=>'1'));
    fixture($db,'LEGACYALLOCATION','1000.00','20.00');
    $post=preview($controller,array('LEGACYALLOCATION'),99);
    expect(save($controller,$post,array('LEGACYALLOCATION'),array('1.020,00'))['status']===1,'Legacy allocation save');
    fixture($db,'LOCKED','1000.00','20.00'); allocation($db,9,'1020.00');
    $post=preview($controller,array('LOCKED'),9); expect(save($controller,$post,array('LOCKED'),array('100,00'))['status']===1,'Locked choice fixture');
    // Invoice already has a receipt: preview itself rejects a changed choice.
    $controller->input->values=array('choose_inv'=>array('LOCKED'),'id_alokasi'=>9,'pph23_dipotong'=>'Y','ppn_dipotong'=>'Y');
    ob_start(); $controller->process_alokasi(); $error=ob_get_clean(); expect(strpos($error,'terkunci')!==false,'Choice locked on preview');
    // Duplicate IDs are rejected before preview/saving.
    $controller->input->values=array('choose_inv'=>array('A','A'),'id_alokasi'=>2,'pph23_dipotong'=>'N','ppn_dipotong'=>'Y');
    ob_start(); $controller->process_alokasi(); $error=ob_get_clean(); expect(strpos($error,'berbeda')!==false,'Duplicate invoice rejected');
    // An earlier unposted receipt cannot be rolled back after a later installment.
    $tinyFirst=value($db,"SELECT h.id FROM tr_penerimaan_piutang h JOIN tr_penerimaan_piutang_detail d ON d.id_header=h.no_surat WHERE d.id_inv='TINY' ORDER BY d.id LIMIT 1");
    $controller->input->values=array('id_penerimaan'=>$tinyFirst); $controller->rollback_penerimaan();
    expect(strpos(json_decode($controller->output->body,true)['message'],'lanjutan')!==false,'Rollback dependency');
    // A stale invoice snapshot must reject the save without consuming allocation.
    fixture($db,'STALE','1000.00','20.00'); allocation($db,7,'1020.00');
    $post=preview($controller,array('STALE'),7); $db->query("UPDATE tr_invoicing SET saldo_piutang=999 WHERE id='STALE'");
    expect(save($controller,$post,array('STALE'),array('1.000,00'))['status']===0,'Invoice stale rejected');
    expect(value($db,'SELECT nilai_terpakai FROM tr_alokasi_split WHERE id=7')==='0.00','Stale allocation untouched');
    // Missing historical journal evidence cannot silently imply zero PPh usage.
    $db->query("DELETE j FROM tr_jurnal j JOIN tr_penerimaan_piutang_detail d ON d.id_header=j.no_transaksi WHERE d.id_inv='MAP1' AND j.coa='1106-01-02'");
    fixture($db,'MISSING','1000.00','20.00'); allocation($db,8,'1020.00');
    $post=preview($controller,array('MISSING'),8); expect(save($controller,$post,array('MISSING'),array('100,00'))['status']===1,'Missing evidence fixture');
    $db->query("DELETE j FROM tr_jurnal j JOIN tr_penerimaan_piutang_detail d ON d.id_header=j.no_transaksi WHERE d.id_inv='MISSING' AND j.coa='1106-01-02'");
    try { $m->invoice_state($db->get_where('tr_invoicing',array('id'=>'MISSING'))->row_array()); throw new RuntimeException('Missing evidence accepted'); } catch (RuntimeException $e) { expect(strpos($e->getMessage(),'tidak lengkap')!==false,'Missing evidence rejected'); }
    // Posted journals prevent rollback, including for legacy rows.
    $postedHeader=value($db,"SELECT h.id FROM tr_penerimaan_piutang h JOIN tr_penerimaan_piutang_detail d ON d.id_header=h.no_surat WHERE d.id_inv='MINARTA' ORDER BY d.id LIMIT 1");
    $controller->input->values=array('id_penerimaan'=>$postedHeader); $controller->rollback_penerimaan();
    expect(strpos(json_decode($controller->output->body,true)['message'],'diposting')!==false,'Posted rollback blocked');
    // A proof linked to an invoice prevents rollback of even an unposted receipt.
    $proofHeader=value($db,"SELECT h.id FROM tr_penerimaan_piutang h JOIN tr_penerimaan_piutang_detail d ON d.id_header=h.no_surat WHERE d.id_inv='Y' LIMIT 1");
    $proofDetail=value($db,"SELECT id FROM tr_penerimaan_piutang_detail WHERE id_inv='Y'");
    $db->insert('tr_penerimaan_pph_23',array('id'=>'TESTPROOF','id_inv'=>'Y','id_detail_penerimaan'=>$proofDetail));
    $controller->input->values=array('id_penerimaan'=>$proofHeader); $controller->rollback_penerimaan();
    expect(strpos(json_decode($controller->output->body,true)['message'],'bukti potong')!==false,'Proof rollback blocked');
    // Allocation overpayment and malformed money are rejected before any ledger write.
    $post=preview($controller,array('LOCKED'),9);
    expect(save($controller,$post,array('LOCKED'),array('2.000,00'))['status']===0,'Overpayment rejected');
    expect(save($controller,$post,array('LOCKED'),array('not-a-number'))['status']===0,'Invalid amount rejected');
    // Corrupt legacy repeated PPh must be reported rather than quietly clamped to zero.
    $db->query("UPDATE tr_penerimaan_piutang_detail SET versi_perhitungan=NULL, penyesuaian_pph=NULL, saldo_piutang_sebelum=NULL WHERE id_inv='LOCKED'");
    $db->query("UPDATE tr_jurnal j JOIN tr_penerimaan_piutang_detail d ON d.id_header=j.no_transaksi SET j.kredit=40 WHERE d.id_inv='LOCKED' AND j.coa='1106-01-02'");
    try { $m->invoice_state($db->get_where('tr_invoicing',array('id'=>'LOCKED'))->row_array()); throw new RuntimeException('Excess PPh accepted'); } catch (RuntimeException $e) { expect(strpos($e->getMessage(),'tidak konsisten')!==false,'Excess PPh rejected'); }
    // Mixed historical choices also require reconciliation.
    $db->query("UPDATE tr_penerimaan_piutang h JOIN tr_penerimaan_piutang_detail d ON d.id_header=h.no_surat SET h.pph23_dipotong='Y' WHERE d.id_inv='TINY' AND d.penerimaan=1100");
    try { $m->invoice_state($db->get_where('tr_invoicing',array('id'=>'TINY'))->row_array()); throw new RuntimeException('Mixed choices accepted'); } catch (RuntimeException $e) { expect(strpos($e->getMessage(),'campuran')!==false,'Mixed choices rejected'); }
    $controller->input->values=array('id_inv'=>'MISSING'); $controller->audit_pph();
    expect(json_decode($controller->output->body,true)['invoices'][0]['reconstructed_balance']===null,'Incomplete audit does not fabricate balance');
    // Lock a receipt save, start another connection with the same preview, then commit the first.
    fixture($db,'CONCURRENT','1000.00','20.00'); allocation($db,5,'1020.00');
    $post=preview($controller,array('CONCURRENT'),5);
    $db->query("SELECT GET_LOCK('sendigs_receipt_v2',10)");
    $process=proc_open(array(PHP_BINARY,__FILE__,'--worker'),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
    fwrite($pipes[0],json_encode(array('database'=>$database,'session'=>$controller->session->values,'post'=>$post,'ids'=>array('CONCURRENT'),'cash'=>array('1.020,00')))); fclose($pipes[0]);
    // Wait until the other connection is actually waiting for our lock.
    $waiting=false;
    for($n=0;$n<100;$n++) { $rows=$db->query("SELECT STATE FROM information_schema.PROCESSLIST WHERE DB=? AND ID<>CONNECTION_ID()",array($database))->result_array(); foreach($rows as $row) if(stripos($row['STATE'] ?? '', 'lock')!==false) $waiting=true; if($waiting) break; usleep(20000); }
    expect($waiting,'Concurrent worker reached lock');
    expect(save($controller,$post,array('CONCURRENT'),array('1.020,00'))['status']===1,'First concurrent save');
    $db->query("SELECT RELEASE_LOCK('sendigs_receipt_v2')");
    $worker=stream_get_contents($pipes[1]); $workerError=stream_get_contents($pipes[2]); fclose($pipes[1]); fclose($pipes[2]); proc_close($process);
    expect(($response=json_decode($worker,true)) && $response['status']===0,'Concurrent stale rejected: '.$worker.' '.$workerError);
    expect(value($db,"SELECT COUNT(*) FROM tr_penerimaan_piutang_detail WHERE id_inv='CONCURRENT'")==='1','No double receipt');
    echo "PASS: isolated MySQL migration, real controller save/rollback, legacy posted history, hidden-input protection, multiple invoices, both COAs, decimal admin, partial PPh, atomic failure, read-only audit and concurrent stale preview\n";
} finally {
    $connection->query("SELECT RELEASE_LOCK('sendigs_receipt_v2')");
    $connection->query('DROP DATABASE '.$database);
    $connection->close();
}
