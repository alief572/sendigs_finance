<?php
require dirname(__DIR__,2).'/penerimaan_uang/tests/harness.php';
require dirname(__DIR__).'/models/Invoicing_model.php';
require dirname(__DIR__).'/libraries/Invoice_audit.php';
class InvoiceTestDb extends TestDb {
    private $table, $fields='*', $codes, $conditions=array();
    function select($fields) { $this->fields=$fields; return $this; }
    function from($table) { $this->table=$table; return $this; }
    function where_in($field,$codes) { $this->codes=array($field,$codes); return $this; }
    function get($table=null) {
        if ($table!==null) return parent::get($table);
        $f=$this->codes; $this->codes=null;
        return $this->query('SELECT '.$this->fields.' FROM '.$this->table.' WHERE '.$f[0].' IN ('.implode(',',array_fill(0,count($f[1]),'?')).')',$f[1]);
    }
    function escape_like_str($s) { return $this->connection->real_escape_string($s); }
    function where($key,$value) { $this->conditions[$key]=$value; return $this; }
    function update($table,$values,$conditions=array()) { $where=$conditions?:$this->conditions; $this->conditions=array(); return parent::update($table,$values,$where); }
}
function invoiceController($db) {
    $model=(new ReflectionClass('Invoicing_model'))->newInstanceWithoutConstructor(); $model->db=$db; $model->auth=new TestAuth;
    $c=(new ReflectionClass('Invoicing'))->newInstanceWithoutConstructor(); $c->db=$db; $c->auth=new TestAuth; $c->input=new TestInput; $c->Invoicing_model=$model;
    $p=new ReflectionProperty('Invoicing','accounting'); $p->setAccessible(true); $p->setValue($c,$db);
    return $c;
}
function callInvoice($c,$endpoint,$post) { $c->input->values=$post; ob_start(); $c->$endpoint(); $r=json_decode(ob_get_clean(),true); expect(is_array($r),'JSON response'); return $r; }
function invoicePost($id,$non=false) {
    $p=array('tanggal_invoice'=>'2026-10-06','ppn_persen'=>'12','nomor_po'=>'','nomor_faktur'=>'','id'=>$id,
        'total_nominal_jurnal'=>'999','total_akhir_jurnal'=>'999','total_tagihan_ppn'=>'999','debit_1'=>'999','kredit_1'=>'999','id_company_1'=>'tampered');
    if ($non) $p+=array('id_penawaran'=>$id,'nm_customer'=>'Customer','biaya_kirim'=>'0.00','item'=>array(array('nama'=>'Item','qty'=>'1.5','harga'=>'100.25')),'jurnal_invoice_no_coa'=>'4101-01-07');
    return $p;
}
function editPost($db,$id,$non=false) {
    $row=$db->query('SELECT * FROM tr_invoicing WHERE id=?',array($id))->row_array();
    $p=invoicePost($non?$row['id_penawaran']:$row['id_actual_plan_tagih'],$non);
    $p[$non?'id':'id_invoicing']=$id; $p['nomor_invoice']=$row['no_invoice']; $p['invoice_fingerprint']=Invoice_calculation::fingerprint($row);
    return $p;
}
if (($argv[1]??'')==='--worker') {
    $payload=json_decode(stream_get_contents(STDIN),true);
    expect(preg_match('/^codex_invoice_test_[a-f0-9]+$/',$payload['database']),'Worker database safety');
    $connection=new mysqli('127.0.0.1','root','',$payload['database'],3306); define('DBCNL',$payload['database']);
    echo json_encode(callInvoice(invoiceController(new InvoiceTestDb($connection)),$payload['endpoint'],$payload['post'])); exit;
}
$connection=new mysqli('127.0.0.1','root','','',3306);
expect(!$connection->connect_errno,'Local test database');
$database='codex_invoice_test_'.bin2hex(random_bytes(6)); $connection->query('CREATE DATABASE '.$database);
$connection->select_db($database); define('DBCNL',$database); $db=new InvoiceTestDb($connection);
try {
    foreach (array(dirname(__DIR__,2).'/penerimaan_uang/tests/fixture_schema.sql',__DIR__.'/source_fixture.sql') as $file) {
        foreach(explode(';',preg_replace('/^--.*$/m','',file_get_contents($file))) as $sql) if(trim($sql)) $db->query($sql);
    }
    $db->query('CREATE TABLE coa_master (no_perkiraan VARCHAR(30) PRIMARY KEY,nama VARCHAR(100)) ENGINE=InnoDB');
    foreach(array('1102-01-01','1106-01-02','1106-01-05','2104-01-07','4101-01-01','4101-01-07') as $code) $db->insert('coa_master',array('no_perkiraan'=>$code,'nama'=>$code));
    $c=invoiceController($db);
    expect(callInvoice($c,'save_invoice_vuca',invoicePost('A1'))['status']===1,'Vuca create');
    $vuca=value($db,"SELECT id FROM tr_invoicing WHERE id_actual_plan_tagih='A1'");
    expect(value($db,"SELECT tagihan_ppn_jurnal FROM tr_invoicing WHERE id='$vuca'")==='15000000.00','Vuca database DPP');
    expect(value($db,"SELECT saldo_piutang FROM tr_invoicing WHERE id='$vuca'")==='14925000.00','Vuca net');
    expect(callInvoice($c,'save_invoice_vuca',invoicePost('A1'))['status']===0,'Double plan blocked');
    expect(callInvoice($c,'update_invoice_vuca',editPost($db,$vuca))['status']===1,'Vuca edit');
    $db->failInsertTable='tr_jurnal';
    expect(callInvoice($c,'save_invoice',invoicePost('A2'))['status']===0,'Consultation journal failure');
    $db->failInsertTable=null;
    expect(value($db,"SELECT sts_invoice FROM kons_tr_actual_plan_tagih WHERE id='A2'")==='0','Plan status rolled back');
    expect(value($db,"SELECT sts_invoice FROM kons_tr_plan_tagih_detail WHERE id=2")==='0','Detail status rolled back');
    expect(callInvoice($c,'save_invoice',invoicePost('A2'))['status']===1,'Regular create');
    $regular=value($db,"SELECT id FROM tr_invoicing WHERE id_actual_plan_tagih='A2'");
    $p=editPost($db,$regular); $p['ppn_persen']='0';
    expect(callInvoice($c,'update_invoice',$p)['status']===1,'Regular edit PPN zero');
    expect(value($db,"SELECT saldo_piutang FROM tr_invoicing WHERE id='$regular'")==='980.54','Regular cents');
    expect(callInvoice($c,'update_invoice',$p)['status']===0,'Stale edit blocked');
    $db->query("UPDATE tr_jurnal SET sts='1' WHERE no_transaksi='$regular'");
    $snapshot=$db->query("SELECT * FROM tr_jurnal WHERE no_transaksi='$regular' ORDER BY id")->result_array();
    expect(callInvoice($c,'update_invoice',editPost($db,$regular))['status']===0,'Posted blocked');
    expect($snapshot===$db->query("SELECT * FROM tr_jurnal WHERE no_transaksi='$regular' ORDER BY id")->result_array(),'Posted journal identical');
    $p=invoicePost('N1',true); $p['biaya_kirim']='2.00';
    expect(callInvoice($c,'save_invoice_non_konsultasi',$p)['status']===1,'Non create decimal items/shipping');
    $non=value($db,"SELECT id FROM tr_invoicing WHERE non_kons='1'");
    expect(value($db,"SELECT total_nominal FROM tr_invoicing WHERE id='$non'")==='152.38','Half up per item');
    expect(value($db,"SELECT COUNT(*) FROM tr_jurnal WHERE no_transaksi='$non' AND coa='4101-01-07'")==='1','Revenue COA preserved');
    expect(callInvoice($c,'update_invoice_non_konsultasi',editPost($db,$non,true))['status']===1,'Non edit');
    $p=editPost($db,$non,true); $p['item'][0]['harga']='20000.00';
    expect(callInvoice($c,'update_invoice_non_konsultasi',$p)['status']===0,'Edit outstanding excludes self but blocks excess');
    $db->insert('tr_penerimaan_piutang_detail',array('id_inv'=>$non,'penerimaan'=>'1.00'));
    $db->query("UPDATE tr_invoicing SET saldo_piutang=saldo_piutang-1 WHERE id='$non'");
    expect(callInvoice($c,'update_invoice_non_konsultasi',editPost($db,$non,true))['status']===0,'Receipt edit blocked');
    $before=value($db,'SELECT COUNT(*) FROM tr_invoicing');
    $sequences=$db->query('SELECT * FROM ms_invoice_sequence ORDER BY id')->result_array();
    foreach(array('qty'=>'-1','harga'=>'-2','nama'=>'') as $field=>$bad) {
        $p=invoicePost('N1',true); $p['item'][0][$field]=$bad;
        expect(callInvoice($c,'save_invoice_non_konsultasi',$p)['status']===0,'Invalid item');
    }
    $p=invoicePost('N1',true); unset($p['ppn_persen']);
    expect(callInvoice($c,'save_invoice_non_konsultasi',$p)['status']===0,'Missing field');
    $db->delete('coa_master',array('no_perkiraan'=>'2104-01-07'));
    expect(callInvoice($c,'save_invoice_non_konsultasi',invoicePost('N1',true))['status']===0,'Missing COA');
    $db->insert('coa_master',array('no_perkiraan'=>'2104-01-07','nama'=>'PPN'));
    $db->failInsertTable='tr_jurnal';
    expect(callInvoice($c,'save_invoice_non_konsultasi',invoicePost('N1',true))['status']===0,'Journal failure'); $db->failInsertTable=null;
    expect(value($db,'SELECT COUNT(*) FROM tr_invoicing')===$before,'No partial headers');
    expect($sequences===$db->query('SELECT * FROM ms_invoice_sequence ORDER BY id')->result_array(),'Failed transactions do not advance sequence');
    // Two connections compete for a quote with only Rp100 remaining.
    $p=invoicePost('N2',true); $p['item']=array(array('nama'=>'Item','qty'=>'1','harga'=>'100.00'));
    $db->query("SELECT GET_LOCK('sendigs_receipt_v2',10)");
    $proc=proc_open(array(PHP_BINARY,__FILE__,'--worker'),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
    fwrite($pipes[0],json_encode(array('database'=>$database,'endpoint'=>'save_invoice_non_konsultasi','post'=>$p))); fclose($pipes[0]);
    $waiting=false;
    for($n=0;$n<100;$n++) { foreach($db->query('SELECT STATE FROM information_schema.PROCESSLIST WHERE DB=? AND ID<>CONNECTION_ID()',array($database))->result_array() as $row) if(stripos($row['STATE']??'','lock')!==false) $waiting=true; if($waiting) break; usleep(20000); }
    expect($waiting,'Worker waiting on lock');
    expect(callInvoice($c,'save_invoice_non_konsultasi',$p)['status']===1,'First quote save');
    $db->query("SELECT RELEASE_LOCK('sendigs_receipt_v2')");
    $output=stream_get_contents($pipes[1]); $error=stream_get_contents($pipes[2]); fclose($pipes[1]); fclose($pipes[2]); proc_close($proc);
    expect(($r=json_decode($output,true)) && $r['status']===0,'Second quote blocked: '.$output.$error);
    expect(value($db,"SELECT COUNT(*) FROM tr_invoicing WHERE id_penawaran='N2'")==='1','No oversubscribed quote');
    expect(value($db,'SELECT COUNT(DISTINCT no_invoice) FROM tr_invoicing')===value($db,'SELECT COUNT(*) FROM tr_invoicing'),'Unique numbering');
    expect(value($db,'SELECT COUNT(DISTINCT no_jurnal) FROM tr_jurnal')===value($db,'SELECT COUNT(*) FROM tr_jurnal'),'Unique journal numbering');
    // A second connection must reject the same stale edit after the first commits.
    $p=editPost($db,$vuca);
    $db->query("SELECT GET_LOCK('sendigs_receipt_v2',10)");
    $proc=proc_open(array(PHP_BINARY,__FILE__,'--worker'),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
    fwrite($pipes[0],json_encode(array('database'=>$database,'endpoint'=>'update_invoice_vuca','post'=>$p))); fclose($pipes[0]);
    $waiting=false;
    for($n=0;$n<100;$n++) { foreach($db->query('SELECT STATE FROM information_schema.PROCESSLIST WHERE DB=? AND ID<>CONNECTION_ID()',array($database))->result_array() as $row) if(stripos($row['STATE']??'','lock')!==false) $waiting=true; if($waiting) break; usleep(20000); }
    expect($waiting,'Edit worker waiting');
    $p['nomor_invoice'].='-REVISED';
    expect(callInvoice($c,'update_invoice_vuca',$p)['status']===1,'First concurrent edit');
    $db->query("SELECT RELEASE_LOCK('sendigs_receipt_v2')");
    $output=stream_get_contents($pipes[1]); $error=stream_get_contents($pipes[2]); fclose($pipes[1]); fclose($pipes[2]); proc_close($proc);
    expect(($r=json_decode($output,true)) && $r['status']===0,'Stale concurrent edit blocked: '.$output.$error);
    // Audit cannot mutate and must report missing/ambiguous evidence without guessing.
    fixture($db,'LEGACYZERO','14925000.00','75000.00',1,'LEGACY/VSB/2026');
    $db->update('tr_invoicing',array('total_nominal_jurnal'=>'15000000.00','tagihan_ppn_jurnal'=>'0.00'),array('id'=>'LEGACYZERO'));
    $mutationCount=$db->mutations;
    $db->query('START TRANSACTION WITH CONSISTENT SNAPSHOT, READ ONLY');
    $audit=(new Invoice_audit($db))->report(); $db->trans_rollback();
    expect($db->mutations===$mutationCount,'Audit does not mutate');
    $legacy=array_values(array_filter($audit,function($r){return $r['id']==='LEGACYZERO';}))[0];
    expect(in_array('gross_mismatch',$legacy['issues'],true),'Legacy zero gross detected');
    expect($legacy['balance_reconstructed']===null,'Missing opening journal not guessed');
    echo "PASS: all six invoice endpoints, server totals, journals, edit guards, fingerprints, atomic failure and concurrent quote writes\n";
} finally {
    $connection->query("SELECT RELEASE_LOCK('sendigs_receipt_v2')");
    $connection->query('DROP DATABASE '.$database); $connection->close();
}
