<?php
// Never loads application credentials; all schema writes use a random disposable database.
$mode = $argv[1] ?? '--isolated-local-db';
if (!in_array($mode, array('--isolated-local-db', '--worker'), true)) {
    fwrite(STDERR, "Usage: php Consulting_integration_test.php [--isolated-local-db]\n");
    exit(1);
}
define('BASEPATH',dirname(__DIR__,4).'/system/'); define('APPPATH',dirname(__DIR__,3).'/'); define('FCPATH',dirname(__DIR__,4).'/');
function base_url($p=''){return '/'.$p;}
function is_php($v){return version_compare(PHP_VERSION,$v,'>=');}
function log_message($l,$m){}
function show_error($m){throw new RuntimeException($m);}
function has_permission($p){return !in_array($p,$GLOBALS['denied']??array(),true);}
function int_to_roman($n){return array(1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII')[(int)$n];}
function &get_instance(){return $GLOBALS['test_ci'];}
function audit_log($message,$status,$event){$GLOBALS['audit'][]=array($message,$status,$event);if(!empty($GLOBALS['audit_failure']))throw new RuntimeException('Writer unavailable');return true;}
if(!function_exists('mb_strlen')){function mb_strlen($s,$enc=null){return strlen($s);}}
class Admin_Controller {} class BF_Model {}
require BASEPATH.'database/DB.php';
require APPPATH.'libraries/Module_audit_recorder.php';
require dirname(__DIR__).'/controllers/Invoicing.php';
require dirname(__DIR__).'/models/Invoicing_model.php';
require dirname(__DIR__).'/libraries/Invoice_consulting_service.php';
require dirname(__DIR__,2).'/penerimaan_uang/controllers/Penerimaan_uang.php';
require dirname(__DIR__,2).'/penerimaan_uang/models/Penerimaan_uang_model.php';
require dirname(__DIR__,2).'/penerimaan_uang/libraries/Consulting_receipt.php';
require dirname(__DIR__).'/libraries/Consulting_posting.php';
require dirname(__DIR__,2).'/jurnal_invoicing/models/Jurnal_invoicing_model.php';
require dirname(__DIR__,2).'/report_piutang_per_invoice/models/Report_piutang_per_invoice_model.php';
require dirname(__DIR__,2).'/penerimaan_pph_23/controllers/Penerimaan_pph_23.php';
$GLOBALS['checks']=0;
function verify($ok,$m){$GLOBALS['checks']++;if(!$ok)throw new RuntimeException($m);}
class ConsultingAuth { function user_id(){return '7';} function user_name(){return 'Admin';} function restrict($p){if(!has_permission($p))throw new RuntimeException('Forbidden');} }
class ConsultingInput {public $values=array();function post($key=null){return $key===null?$this->values:($this->values[$key]??null);} function get($key){return $this->values[$key]??null;} }
class ConsultingLoader {
    public $ci;
    function library($name){$this->ci->module_audit_recorder=new Module_audit_recorder;}
    function database($group,$return){return $this->ci->db;}
    public $viewData; public $viewName;
    function view($view,$data){$this->viewName=$view;$this->viewData=$data;}
}
function migrateSql($db,$file){
    $delimiter=';';$sql='';
    foreach(explode("\n",file_get_contents($file)) as $line){
        if(preg_match('/^DELIMITER\s+(.*)$/',trim($line),$m)){$delimiter=$m[1];continue;}
        if(substr(ltrim($line),0,2)==='--')continue;
        $sql.=$line."\n";
        if(substr(rtrim($sql),-strlen($delimiter))===$delimiter){$statement=substr(rtrim($sql),0,-strlen($delimiter));if(trim($statement)!=='')verify($db->query($statement)!==false,'Migration statement: '.$db->error()['message']);$sql='';}
    }
}
function controllerCall($ci,$method,$post){$ci->input->values=$post;ob_start();$ci->$method();$out=ob_get_clean();$result=json_decode($out,true);verify(is_array($result),'Endpoint JSON: '.$out);return $result;}
function newSource($db,$n,$amount='1000.55'){
    $db->insert('kons_tr_plan_tagih_detail',array('id'=>$n,'sts_invoice'=>0,'status_terakhir'=>'1','id_spk_penawaran'=>'SP1','id_penawaran'=>'Q2','term_payment'=>'TOP '.$n,'nominal_payment'=>$amount));
    $db->insert('kons_tr_actual_plan_tagih',array('id'=>'A'.$n,'id_spk_penawaran'=>'SP1','id_penawaran'=>'Q2','id_detail_plan_tagih'=>$n,'nominal_payment'=>$amount,'sts_invoice'=>0,'tagih_mundur'=>'1'));
}
function invoiceRequest($service,$n,$type='PKP',$role='sales_consulting'){
    $source=$service->source($n);$mapping=$service->mapping($source['source_company_id']);
    return array('id_detail_plan_tagih'=>(string)$n,'tanggal_invoice'=>'2026-10-18','nomor_po'=>'PO-1','nomor_faktur'=>'FP-1','tax_treatment'=>$type,'sales_role'=>$role,
        'source_fingerprint'=>Invoice_calculation::fingerprint(array('source'=>$source,'mapping'=>$mapping)),'total_akhir_jurnal'=>'999999','debit_1'=>'0','id_company_1'=>'tampered');
}

if($mode==='--worker'){
    $payload=json_decode(stream_get_contents(STDIN),true);
    if(!preg_match('/^codex_consulting_[a-f0-9]+$/D',$payload['database']??''))throw new RuntimeException('Unsafe worker database.');
    define('DBCNL',$payload['database']);
    $db=DB(array('hostname'=>'127.0.0.1','port'=>33319,'username'=>'root','password'=>'consulting_test_local','database'=>$payload['database'],'dbdriver'=>'mysqli','pconnect'=>false,'db_debug'=>false,'char_set'=>'utf8mb4','dbcollat'=>'utf8mb4_general_ci'),true);
    $ci=(new ReflectionClass('Invoicing'))->newInstanceWithoutConstructor();$ci->db=$db;$ci->auth=new ConsultingAuth;$ci->input=new ConsultingInput;$ci->load=new ConsultingLoader;$ci->load->ci=$ci;$GLOBALS['test_ci']=$ci;
    $model=(new ReflectionClass('Invoicing_model'))->newInstanceWithoutConstructor();$model->db=$db;$model->auth=$ci->auth;$ci->Invoicing_model=$model;
    if($payload['operation']==='allocate')echo json_encode(array('number'=>$model->generate_no_invoice('1','0')));
    elseif($payload['operation']==='post'){ $posting=new Consulting_posting($ci);echo json_encode($posting->invoice($payload['journal_id'])); }
    else echo json_encode(controllerCall($ci,$payload['operation']==='edit'?'update_invoice':'save_invoice',$payload['post']));
    exit;
}
function workerStart($payload){
    $extDir = realpath(ini_get('extension_dir')) ?: realpath(dirname(PHP_BINARY) . '/ext') ?: ini_get('extension_dir');
    $command=array(PHP_BINARY,'-n','-d','extension_dir='.$extDir,'-d','extension=mysqli',__FILE__,'--worker');
    $process=proc_open($command,array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
    if(!is_resource($process))throw new RuntimeException('Worker launch failed.');fwrite($pipes[0],json_encode($payload));fclose($pipes[0]);return array($process,$pipes);
}
function workerFinish($worker){list($process,$pipes)=$worker;$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);$exit=proc_close($process);verify($exit===0,'Worker exit '.$err);$r=json_decode($out,true);verify(is_array($r),'Worker JSON '.$out);return $r;}
$admin=@new mysqli('127.0.0.1','root','consulting_test_local','',33319);
$started_server_proc=null;
if($admin->connect_errno){
    $mysqld=dirname(__DIR__,4).'/scratch/runtime/mariadb-10.11.10-winx64/bin/mariadbd.exe';
    $defaults=dirname(__DIR__,4).'/scratch/runtime/consulting-db/my.ini';
    if(file_exists($mysqld)&&file_exists($defaults)){
        $serverPipes=array();
        $started_server_proc=proc_open(array($mysqld,'--defaults-file='.$defaults,'--port=33319','--bind-address=127.0.0.1'),array(0=>array('pipe','r'),1=>array('file','NUL','w'),2=>array('file','NUL','w')),$serverPipes);
        for($retry=0;$retry<40;$retry++){
            usleep(250000);
            $admin=@new mysqli('127.0.0.1','root','consulting_test_local','',33319);
            if(!$admin->connect_errno)break;
        }
    }
}
if($admin->connect_errno){
    if($started_server_proc){ @proc_terminate($started_server_proc); @proc_close($started_server_proc); }
    fwrite(STDERR, "DISPOSABLE_DB_UNAVAILABLE: MariaDB on 127.0.0.1:33319 is offline and local daemon could not be started.\nTo guarantee database integrity, production database 'db_sendigs_ss' was NOT touched.\n");
    exit(1);
}
$database='codex_consulting_'.bin2hex(random_bytes(5));$admin->query('CREATE DATABASE '.$database);define('DBCNL',$database);
$native=DB(array('hostname'=>'127.0.0.1','port'=>33319,'username'=>'root','password'=>'consulting_test_local','database'=>$database,'dbdriver'=>'mysqli','pconnect'=>false,'db_debug'=>false,'char_set'=>'utf8mb4','dbcollat'=>'utf8mb4_general_ci'),true);
class ConsultingFaultDb extends CI_DB_mysqli_driver {
    public $fault='';
    public function query($sql,$binds=false,$return_override=null){
        if($this->fault==='sequence' && strpos($sql,'UPDATE ms_invoice_sequence')===0){$this->fault='';return false;}
        return parent::query($sql,$binds,$return_override);
    }
    public function insert($table='', $set=null, $escape=null){if($this->fault==='header' && $table==='tr_invoicing'){$this->fault='';return false;}return parent::insert($table,$set,$escape);}
    public function insert_batch($table,$set=null,$escape=null,$batch_size=100){if($this->fault==='journal' && $table==='tr_jurnal'){$this->fault='';return false;}return parent::insert_batch($table,$set,$escape,$batch_size);}
    public function update($table='', $set=null, $where=null, $limit=null){if($this->fault==='top' && $table==='kons_tr_plan_tagih_detail'){$this->fault='';return false;}return parent::update($table,$set,$where,$limit);}
    public function trans_commit(){if($this->fault==='commit'){$this->fault='';return false;}return parent::trans_commit();}
}
$db=new ConsultingFaultDb(array('hostname'=>'127.0.0.1','port'=>33319,'username'=>'root','password'=>'consulting_test_local','database'=>$database,'dbdriver'=>'mysqli','pconnect'=>false,'db_debug'=>false,'char_set'=>'utf8mb4','dbcollat'=>'utf8mb4_general_ci'));
$db->initialize();$native->close();

$ci=(new ReflectionClass('Invoicing'))->newInstanceWithoutConstructor();$ci->db=$db;$ci->auth=new ConsultingAuth;$ci->input=new ConsultingInput;$ci->load=new ConsultingLoader;$ci->load->ci=$ci;$GLOBALS['test_ci']=$ci;
$model=(new ReflectionClass('Invoicing_model'))->newInstanceWithoutConstructor();$model->db=$db;$model->auth=$ci->auth;$ci->Invoicing_model=$model;
$service=new Invoice_consulting_service($ci);
try {
    foreach(array(dirname(__DIR__,2).'/penerimaan_uang/tests/fixture_schema.sql',__DIR__.'/source_fixture.sql') as $file) foreach(explode(';',preg_replace('/^--.*$/m','',file_get_contents($file)))as $sql)if(trim($sql))verify($db->query($sql)!==false,'Fixture '.$db->error()['message']);
    foreach(array("ALTER TABLE kons_tr_plan_tagih_detail ADD status_terakhir VARCHAR(2), ADD id_spk_penawaran VARCHAR(100), ADD id_penawaran VARCHAR(100), ADD term_payment VARCHAR(100), ADD nominal_payment DECIMAL(20,2), ADD persen_payment DECIMAL(10,2), ADD desc_payment TEXT", "ALTER TABLE kons_tr_actual_plan_tagih ADD tagih_mundur VARCHAR(2)","CREATE TABLE menus(id INT PRIMARY KEY,link VARCHAR(100))", "INSERT INTO menus VALUES (1,'invoicing')", "CREATE TABLE permissions(id_permission INT AUTO_INCREMENT PRIMARY KEY,nm_permission VARCHAR(100),ket VARCHAR(100),id_menu INT,nm_menu VARCHAR(100),created_on DATETIME,created_by VARCHAR(100))", "CREATE TABLE user_permissions(id_user INT,id_permission INT, UNIQUE KEY grant_once(id_user,id_permission))","CREATE TABLE coa_master(no_perkiraan VARCHAR(30) PRIMARY KEY,nama VARCHAR(100))") as $sql)verify($db->query($sql)!==false,'Fixture additions');
    verify($db->query("INSERT INTO kons_tr_company VALUES (3,'Sustain'),(6,'STM-Sustain'),(7,'STM')")!==false,'Canonical owner fixtures');
    $migration=dirname(__DIR__).'/migrations/003_consulting_v3.sql';migrateSql($db,$migration);migrateSql($db,$migration);
    verify($service->one("SELECT COUNT(*) n FROM permissions WHERE nm_permission='Invoicing.Configure'")['n']==1,'Idempotent permission');
    verify($service->one('SELECT id_user FROM user_permissions')['id_user']==7,'Admin only grant');
    $codes=array();$i=1;foreach(Invoice_consulting::roles()as $role=>$label){$code=sprintf('4101-01-%02d',$i++);$codes[$role]=$code;$db->insert('coa_master',array('no_perkiraan'=>$code,'nama'=>$label));}
    $settings=array('source_company_id'=>'1','owner_company_id'=>'7','accounts'=>$codes,'mapping_fingerprint'=>Invoice_calculation::fingerprint(array()));
    $GLOBALS['denied']=array('Invoicing.Configure');verify(controllerCall($ci,'save_coa_mapping',$settings)['status']===0,'Configuration permission');$GLOBALS['denied']=array();
    verify(controllerCall($ci,'save_coa_mapping',$settings)['status']===1,'Mapping endpoint');
    verify(controllerCall($ci,'save_coa_mapping',$settings)['status']===0,'Mapping stale fingerprint');
    $validMapping=$service->one('SELECT * FROM ms_invoice_coa_mapping WHERE source_company_id=1');
    foreach(array('1','2','5','6','99') as $invalidOwner){$invalid=$settings;$invalid['owner_company_id']=$invalidOwner;$invalid['mapping_fingerprint']=Invoice_calculation::fingerprint($validMapping);$r=controllerCall($ci,'save_coa_mapping',$invalid);verify($r['status']===0 && strpos($r['msg'],'STM, Sustain, atau Vuca')!==false,'Reject unsupported owner '.$invalidOwner);verify($service->one('SELECT * FROM ms_invoice_coa_mapping WHERE source_company_id=1')===$validMapping,'Rejected owner preserves mapping');}
    foreach(array('7','3','4') as $validOwner){$old=$service->one('SELECT * FROM ms_invoice_coa_mapping WHERE source_company_id=6');$valid=$settings;$valid['source_company_id']='6';$valid['owner_company_id']=$validOwner;$valid['mapping_fingerprint']=Invoice_calculation::fingerprint($old?:array());verify(controllerCall($ci,'save_coa_mapping',$valid)['status']===1,'Combined SPK accepts canonical owner '.$validOwner);verify($service->mapping('6')['owner']['id']==$validOwner,'Canonical owner read '.$validOwner);}
    $ci->template=new class {public $data;function set($data){$this->data=$data;}function title($title){}function render($view){}};
    $ci->input->values=array('source_company_id'=>'1','owner_company_id'=>'6');$ci->coa_settings();verify(strpos($ci->template->data['error'],'STM, Sustain, atau Vuca')!==false,'GET combined owner rejected');
    $db->query('UPDATE ms_invoice_coa_mapping SET owner_company_id=6 WHERE source_company_id=1');
    try{$service->mapping('1');verify(false,'Invalid legacy mapping must reject issuance');}catch(RuntimeException $error){verify(strpos($error->getMessage(),'Pilih ulang')!==false,'Invalid legacy mapping actionable');}
    $ci->input->values=array('source_company_id'=>'1');$ci->coa_settings();verify($ci->template->data['owner_id']===null && !empty($ci->template->data['owner_warning']),'Legacy mapping can be repaired in UI');verify(count($ci->template->data['journal_owners'])===3,'Controller exposes three owners');verify($service->one('SELECT owner_company_id FROM ms_invoice_coa_mapping WHERE source_company_id=1')['owner_company_id']==6,'GET does not silently convert legacy mapping');
    $db->query('UPDATE ms_invoice_coa_mapping SET owner_company_id=7 WHERE source_company_id=1');
    foreach(array('WAPU','PKP','KB')as $n=>$type){
        $id=10+$n;newSource($db,$id,array('291000000','19581600','60000000')[$n]);$post=invoiceRequest($service,$id,$type);
        $r=controllerCall($ci,'save_invoice',$post);verify($r['status']===1,'Create endpoint '.$type.': '.$r['msg']);
        $invoice=$service->one('SELECT * FROM tr_invoicing WHERE id=?',array($r['id']));
        verify($invoice['consulting_version']==3 && $invoice['pph_jurnal']==0,'Version and no PPh');
        verify($invoice['saldo_piutang']==array('291000000.00','21735576.00','60000000.00')[$n],'Excel balance');
        verify(controllerCall($ci,'save_invoice',$post)['status']===0,'TOP duplicate');
        $journal=$service->one('SELECT SUM(debit) d,SUM(kredit) c FROM tr_jurnal WHERE no_transaksi=?',array($r['id']));verify($journal['d']===$journal['c'],'Stored journal balance');
        $edit=$post+array('id_invoicing'=>$r['id'],'invoice_fingerprint'=>Invoice_calculation::fingerprint($invoice));
        $edit['sales_role']='sales_hse';verify(controllerCall($ci,'update_invoice',$edit)['status']===1,'Edit version3');
        verify(controllerCall($ci,'update_invoice',$edit)['status']===0,'Stale edit');
    }
    newSource($db,30);$post=invoiceRequest($service,30);$GLOBALS['audit_failure']=true;
    verify(controllerCall($ci,'save_invoice',$post)['status']===1,'Audit writer failure preserves commit');$GLOBALS['audit_failure']=false;
    $newInvoice=$service->one('SELECT * FROM tr_invoicing WHERE id_detail_plan_tagih=30');
    $db->query("UPDATE tr_jurnal SET sts='1' WHERE no_transaksi=?",array($newInvoice['id']));
    $edit=$post+array('id_invoicing'=>$newInvoice['id'],'invoice_fingerprint'=>Invoice_calculation::fingerprint($newInvoice));verify(controllerCall($ci,'update_invoice',$edit)['status']===0,'Posted edit guard');
    try { migrateSql($db,dirname(__DIR__).'/migrations/003_consulting_v3_rollback.sql');verify(false,'Rollback must refuse data'); } catch(RuntimeException $e){verify($db->field_exists('consulting_version','tr_invoicing'),'Rollback guard retained data');}
    verify(count($GLOBALS['audit'])>0,'Audit recorded');

    foreach(array(
        "CREATE TABLE tr_alokasi_split(id INT PRIMARY KEY,id_alokasi_detail INT,nominal DECIMAL(20,2),nilai_terpakai DECIMAL(20,2),jenis_alokasi INT) ENGINE=InnoDB",
        "CREATE TABLE ms_bank(id INT PRIMARY KEY,coa_bank VARCHAR(30)) ENGINE=InnoDB",
        "INSERT INTO coa_master VALUES ('1101-01-01','Bank'),('7201-01-04','Admin'),('1102-01-01','Piutang lama'),('1106-01-02','PPh lama'),('1106-01-05','PPh Vuca')",
        "INSERT INTO ms_bank VALUES (1,'1101-01-01')",
        "INSERT INTO tr_alokasi_detail(id,tipe_bank,tanggal_transaksi,nominal_debit,nilai_terpakai) VALUES (100,1,'2026-10-18',100000,0)",
        "INSERT INTO tr_alokasi_split VALUES (200,100,100000,0,1)",
        "ALTER TABLE kons_tr_spk_penawaran ADD id_penawaran VARCHAR(100)",
        "UPDATE kons_tr_spk_penawaran SET id_penawaran='Q2'",
        "CREATE TABLE javh(nomor VARCHAR(50) PRIMARY KEY,tgl DATE,jml DECIMAL(20,2),koreksi_no VARCHAR(100),kdcab VARCHAR(10),jenis VARCHAR(10),keterangan TEXT,bulan VARCHAR(10),tahun VARCHAR(10),user_id VARCHAR(100),memo TEXT,tgl_jvkoreksi DATE,ho_valid VARCHAR(10)) ENGINE=InnoDB",
        "CREATE TABLE jarh(nomor VARCHAR(50) PRIMARY KEY,tgl DATE,jml DECIMAL(20,2),kdcab VARCHAR(10),jenis_reff VARCHAR(10),no_reff VARCHAR(100),customer VARCHAR(100),terima_dari VARCHAR(100),jenis_ar VARCHAR(10),note TEXT,user_id VARCHAR(100),tgl_invoice DATE) ENGINE=InnoDB",
        "CREATE TABLE jurnal(id INT AUTO_INCREMENT PRIMARY KEY,tipe VARCHAR(10),nomor VARCHAR(50),tanggal DATE,no_perkiraan VARCHAR(30),keterangan TEXT,no_reff VARCHAR(100),debet DECIMAL(20,2),kredit DECIMAL(20,2),stspos INT,id_perusahaan VARCHAR(20),nm_perusahaan VARCHAR(100)) ENGINE=InnoDB",
        "CREATE TABLE pastibisa_tb_cabang(nocab VARCHAR(10) PRIMARY KEY,subcab VARCHAR(10),nomorJC INT,nobum INT) ENGINE=InnoDB",
        "INSERT INTO pastibisa_tb_cabang VALUES ('101','A',0,0)",
        "CREATE TABLE tr_kartu_piutang(id INT AUTO_INCREMENT PRIMARY KEY,tipe VARCHAR(10),nomor VARCHAR(50),tanggal DATE,no_perkiraan VARCHAR(30),keterangan TEXT,no_reff VARCHAR(100),debet DECIMAL(20,2),kredit DECIMAL(20,2),id_supplier VARCHAR(100),nama_supplier VARCHAR(100),id_company VARCHAR(20),nm_company VARCHAR(100)) ENGINE=InnoDB"
    ) as $sql)verify($db->query($sql)!==false,'Receipt/GL fixture '.$db->error()['message']);
    $receiptCi=(new ReflectionClass('Penerimaan_uang'))->newInstanceWithoutConstructor();
    $receiptCi->db=$db;$receiptCi->auth=$ci->auth;$receiptCi->input=new ConsultingInput;$receiptCi->load=new ConsultingLoader;$receiptCi->load->ci=$receiptCi;
    $receiptModel=(new ReflectionClass('Penerimaan_uang_model'))->newInstanceWithoutConstructor();$receiptModel->db=$db;$receiptCi->Penerimaan_uang_model=$receiptModel;
    $receiptService=new Consulting_receipt($receiptCi);
    newSource($db,40);$r=controllerCall($ci,'save_invoice',invoiceRequest($service,40));verify($r['status']===1,'Receipt invoice created');$receiptId=$r['id'];
    $original=$service->one('SELECT * FROM tr_invoicing WHERE id=?',array($receiptId));
    $receiptPost=function($ids,$cash,$admins,$taxes)use($receiptService){
        $preview=$receiptService->preview(array('choose_inv'=>$ids,'id_alokasi'=>'200','pph23_dipotong'=>'N','ppn_dipotong'=>'Y'));
        $post=array('no_inv'=>(string)count($ids),'id_alokasi'=>'200','pph23_dipotong'=>'N','ppn_dipotong'=>'Y','allocation_fingerprint'=>$preview['allocation_fingerprint']);
        foreach($preview['rows']as $i=>$row){$n=$i+1;$post['id_inv_'.$n]=$row['invoice']['id'];$post['receipt_fingerprint_'.$n]=$row['fingerprint'];$post['penerimaan_'.$n]=$cash[$i];$post['biaya_admin_'.$n]=$admins[$i];$post['pph_actual_'.$n]=$taxes[$i];}
        return $post;
    };
    $GLOBALS['test_ci']=$receiptCi;
    $post=$receiptPost(array($receiptId),array('100.00'),array('1.00'),array('2.00'));
    $r=controllerCall($receiptCi,'save_penerimaan_piutang',$post);verify($r['status']===1,'Actual PPh receipt endpoint: '.$r['msg']);
    $after=$service->one('SELECT * FROM tr_invoicing WHERE id=?',array($receiptId));verify(Invoice_calculation::cents($after['saldo_piutang'])===Invoice_calculation::cents($original['saldo_piutang'])-10300,'PPh+admin+cash reduction');
    $header=$service->one('SELECT * FROM tr_penerimaan_piutang WHERE no_surat=?',array($r['no_surat']));
    $detail=$service->one('SELECT * FROM tr_penerimaan_piutang_detail WHERE id_header=?',array($r['no_surat']));verify($detail['consulting_reduction']==='103.00'&&$detail['pph23']==='2.00','Receipt reduction snapshot');
    verify(controllerCall($receiptCi,'save_penerimaan_piutang',$post)['status']===0,'Concurrent stale receipt rejected');
    $balance=Invoice_calculation::cents($after['saldo_piutang']);
    $post=$receiptPost(array($receiptId),array(Invoice_calculation::decimal($balance-700)),array('0.00'),array('7.00'));
    $r2=controllerCall($receiptCi,'save_penerimaan_piutang',$post);verify($r2['status']===1,'Final installment');
    verify($service->one('SELECT saldo_piutang FROM tr_invoicing WHERE id=?',array($receiptId))['saldo_piutang']==0,'Paid off');
    verify(controllerCall($receiptCi,'rollback_penerimaan',array('id_penerimaan'=>$header['id']))['status']===0,'Rollback newest installment first');
    $header2=$service->one('SELECT * FROM tr_penerimaan_piutang WHERE no_surat=?',array($r2['no_surat']));
    verify(controllerCall($receiptCi,'rollback_penerimaan',array('id_penerimaan'=>$header2['id']))['status']===1,'Rollback final installment');
    verify(controllerCall($receiptCi,'rollback_penerimaan',array('id_penerimaan'=>$header['id']))['status']===1,'Rollback first installment');
    verify($service->one('SELECT saldo_piutang FROM tr_invoicing WHERE id=?',array($receiptId))['saldo_piutang']===$original['saldo_piutang'],'Exact balance restored');
    verify($service->one('SELECT COUNT(*) n FROM tr_alokasi_split')['n']==1,'Rollback preserves split');
    $post=$receiptPost(array($receiptId),array('99999.00'),array('0'),array('0'));verify(controllerCall($receiptCi,'save_penerimaan_piutang',$post)['status']===0,'Overpayment rejected');
    $legacy=array('id'=>'LEGACY','id_actual_plan_tagih'=>'0','id_detail_plan_tagih'=>0,'no_invoice'=>'L/2026','id_penawaran'=>'N1','id_customer'=>'C1','nm_customer'=>'Customer','tanggal_invoice'=>'2026-10-18','tipe_invoice'=>'0','non_kons'=>'1','saldo_piutang'=>'98.00','total_akhir_jurnal'=>'98.00','total_nominal_jurnal'=>'100.00','dpp_lain_lain_jurnal'=>'91.67','ppn_jurnal'=>'0.00','tagihan_ppn_jurnal'=>'100.00','pph_jurnal'=>'2.00');verify($db->insert('tr_invoicing',$legacy)!==false,'Legacy fixture insert');
    $post=$receiptPost(array($receiptId,'LEGACY'),array('50','10'),array('0','0'),array('1','0'));
    $r=controllerCall($receiptCi,'save_penerimaan_piutang',$post);verify($r['status']===1,'Mixed new/legacy receipt: '.$r['msg']);
    verify($service->one("SELECT saldo_piutang FROM tr_invoicing WHERE id='LEGACY'")['saldo_piutang']==='90.00','Legacy N reduction remains cash minus PPh');
    $j=$service->one('SELECT SUM(debit) d,SUM(kredit) c FROM tr_jurnal WHERE no_transaksi=?',array($r['no_surat']));verify($j['d']===$j['c'],'Mixed journal balanced');
    $anchor=$service->one("SELECT id FROM tr_jurnal WHERE no_transaksi=? AND jenis_transaksi='Penerimaan Piutang' ORDER BY id LIMIT 1",array($r['no_surat']));
    $posting=new Consulting_posting($receiptCi);$posted=$posting->receipt($anchor['id']);verify($posted['status']===1,'Receipt GL atomic posting: '.$posted['msg']);
    verify($posting->receipt($anchor['id'])['status']===0,'Duplicate receipt posting rejected');
    $mixedHeader=$service->one('SELECT id FROM tr_penerimaan_piutang WHERE no_surat=?',array($r['no_surat']));verify(controllerCall($receiptCi,'rollback_penerimaan',array('id_penerimaan'=>$mixedHeader['id']))['status']===0,'Posted receipt rollback rejected');
    $GLOBALS['test_ci']=$ci;
    $jmodel=(new ReflectionClass('Jurnal_invoicing_model'))->newInstanceWithoutConstructor();$jmodel->db=$db;$jmodel->input=$ci->input;$jmodel->load=$ci->load;$jmodel->auth=$ci->auth;
    $anchor=$service->one("SELECT id FROM tr_jurnal WHERE no_transaksi=? AND jenis_transaksi='Invoicing' ORDER BY id LIMIT 1",array($receiptId));
    $posted=controllerCall($jmodel,'save_posting_jurnal',array('id'=>$anchor['id']));verify($posted['save']===1,'Active invoice GL posting: '.$posted['msg']);
    verify(controllerCall($jmodel,'save_posting_jurnal',array('id'=>$anchor['id']))['save']===0,'Duplicate invoice posting rejected');
    verify($service->one('SELECT SUM(debet) d,SUM(kredit) c FROM jurnal')['d']===$service->one('SELECT SUM(debet) d,SUM(kredit) c FROM jurnal')['c'],'GL balanced');

    // Faults must roll back sequence, headers, journals and both TOP status writes together.
    foreach(array('sequence','header','journal','top','commit') as $i=>$fault){
        $n=60+$i;newSource($db,$n);$post=invoiceRequest($service,$n);
        $before=$service->one("SELECT last_sequence FROM ms_invoice_sequence WHERE kode_entitas='STM' AND tahun=?",array(date('Y')));
        $db->fault=$fault;$result=controllerCall($ci,'save_invoice',$post);verify($result['status']===0,'Injected invoice '.$fault);
        verify(!$service->one('SELECT id FROM tr_invoicing WHERE id_detail_plan_tagih=?',array($n)),'No partial header '.$fault);
        verify($service->one('SELECT sts_invoice FROM kons_tr_plan_tagih_detail WHERE id=?',array($n))['sts_invoice']==0,'TOP rolled back '.$fault);
        verify($service->one('SELECT sts_invoice FROM kons_tr_actual_plan_tagih WHERE id_detail_plan_tagih=?',array($n))['sts_invoice']==0,'Actual rolled back '.$fault);
        verify($service->one("SELECT last_sequence FROM ms_invoice_sequence WHERE kode_entitas='STM' AND tahun=?",array(date('Y')))===$before,'Sequence rolled back '.$fault);
    }
    $post=$receiptPost(array($receiptId),array('10'),array('0'),array('0'));
    $balanceBefore=$service->one('SELECT saldo_piutang FROM tr_invoicing WHERE id=?',array($receiptId));$allocationBefore=$service->one('SELECT nilai_terpakai FROM tr_alokasi_split WHERE id=200');
    $GLOBALS['test_ci']=$receiptCi;$db->fault='commit';verify(controllerCall($receiptCi,'save_penerimaan_piutang',$post)['status']===0,'Receipt commit failure');
    verify($service->one('SELECT saldo_piutang FROM tr_invoicing WHERE id=?',array($receiptId))===$balanceBefore,'Receipt balance rollback');
    verify($service->one('SELECT nilai_terpakai FROM tr_alokasi_split WHERE id=200')===$allocationBefore,'Receipt allocation rollback');$GLOBALS['test_ci']=$ci;
    // Mapping edits cannot change account/owner snapshots on existing invoice edits.
    $unposted=$service->one('SELECT * FROM tr_invoicing WHERE id_detail_plan_tagih=11');
    $savedSnapshot=Invoice_consulting::snapshot($unposted);
    $row=$service->one('SELECT * FROM ms_invoice_coa_mapping WHERE source_company_id=1');
    $changed=$settings;$changed['owner_company_id']='4';$changed['mapping_fingerprint']=Invoice_calculation::fingerprint($row);
    verify(controllerCall($ci,'save_coa_mapping',$changed)['status']===1,'Mapping owner changed');
    $edit=array('id_invoicing'=>$unposted['id'],'tanggal_invoice'=>'2026-10-18','nomor_po'=>'PO','nomor_faktur'=>'FP','tax_treatment'=>'PKP','sales_role'=>'sales_hse','invoice_fingerprint'=>Invoice_calculation::fingerprint($unposted));
    verify(controllerCall($ci,'update_invoice',$edit)['status']===1,'Edit retains original mapping');
    verify(Invoice_consulting::snapshot($service->one('SELECT * FROM tr_invoicing WHERE id=?',array($unposted['id'])))['owner']===$savedSnapshot['owner'],'Owner snapshot immutable');
    // Render the actual views without a browser, including hostile stored text.
    class ConsultingView { function render($path,$data){extract($data);ob_start();include $path;return ob_get_clean();} }
    $renderer=new ConsultingView;$viewInvoice=$unposted;$viewInvoice['nm_customer']='<script>alert(1)</script>';
    $html=$renderer->render(dirname(__DIR__).'/views/consulting_invoice.php',array('invoice'=>$viewInvoice,'snapshot'=>$savedSnapshot,'mode'=>'view','journals'=>array(),'source_fingerprint'=>'','invoice_fingerprint'=>'','error'=>null));
    verify(strpos($html,'<script>alert(1)</script>')===false && strpos($html,'&lt;script&gt;')!==false,'Escaped invoice view');
    $html=$renderer->render(dirname(__DIR__).'/views/consulting_invoice.php',array('invoice'=>$viewInvoice,'snapshot'=>$savedSnapshot,'mode'=>'add','journals'=>array(),'source_fingerprint'=>'x','invoice_fingerprint'=>'x','error'=>null));
    verify(strpos($html,'name="sales_role"')!==false && strpos($html,'name="tax_treatment"')!==false,'New invoice required fields rendered');
    $preview=$receiptService->preview(array('choose_inv'=>array($receiptId,'LEGACY'),'id_alokasi'=>'200','pph23_dipotong'=>'N','ppn_dipotong'=>'Y'));
    $html=$renderer->render(dirname(__DIR__,2).'/penerimaan_uang/views/consulting_process.php',$preview);
    verify(preg_match_all('/<input[^>]+name="pph_actual_[0-9]+"/',$html)===1,'Actual PPh input only for new invoice');
    $html=$renderer->render(dirname(__DIR__).'/views/consulting_settings.php',array('companies'=>array(array('id'=>1,'nm_company'=>'STM')),'source_id'=>1,'owner_id'=>7,'journal_owners'=>array(array('id'=>7,'nm_company'=>'STM'),array('id'=>3,'nm_company'=>'Sustain'),array('id'=>4,'nm_company'=>'Vuca')),'mapping'=>null,'master'=>$service->master(1),'roles'=>Invoice_consulting::roles(),'mapping_fingerprint'=>'x','error'=>null));
    verify(substr_count($html,'name="accounts[')===9,'Nine mapping dropdowns');

    // True concurrent connections exercise the production named locks and atomic allocator.
    $payload=array('database'=>$database,'operation'=>'allocate');$workers=array(workerStart($payload),workerStart($payload));$numbers=array();foreach($workers as $worker)$numbers[]=workerFinish($worker)['number'];verify(count(array_unique($numbers))===2,'Concurrent legacy-shared sequence allocation');
    newSource($db,90);$post=invoiceRequest($service,90);$payload=array('database'=>$database,'operation'=>'create','post'=>$post);$workers=array(workerStart($payload),workerStart($payload));$results=array();foreach($workers as $worker)$results[]=workerFinish($worker);verify(array_sum(array_column($results,'status'))===1,'Only one concurrent TOP creation commits');
    $concurrentInvoice=$service->one('SELECT * FROM tr_invoicing WHERE id_detail_plan_tagih=90');$anchor=$service->one("SELECT id FROM tr_jurnal WHERE no_transaksi=? ORDER BY id LIMIT 1",array($concurrentInvoice['id']));
    $edit=array('id_invoicing'=>$concurrentInvoice['id'],'tanggal_invoice'=>'2026-10-18','nomor_po'=>'PO','nomor_faktur'=>'FP','tax_treatment'=>'WAPU','sales_role'=>'sales_consulting','invoice_fingerprint'=>Invoice_calculation::fingerprint($concurrentInvoice));
    $workers=array(workerStart(array('database'=>$database,'operation'=>'post','journal_id'=>$anchor['id'])),workerStart(array('database'=>$database,'operation'=>'edit','post'=>$edit)));$race=array();foreach($workers as $worker)$race[]=workerFinish($worker);
    verify($race[0]['status']+$race[1]['status']===1,'Edit and cached-anchor posting cannot both commit');
    $journalState=$service->one('SELECT COUNT(DISTINCT sts) n FROM tr_jurnal WHERE no_transaksi=?',array($concurrentInvoice['id']));verify($journalState['n']==1,'No mixed replaced/posted journal state');
    newSource($db,91);$created=controllerCall($ci,'save_invoice',invoiceRequest($service,91));$anchor=$service->one('SELECT id FROM tr_jurnal WHERE no_transaksi=? ORDER BY id LIMIT 1',array($created['id']));
    $beforeGl=$service->one('SELECT COUNT(*) n FROM jurnal');$counterBefore=$service->one("SELECT nomorJC FROM pastibisa_tb_cabang WHERE nocab='101'");$db->fault='commit';$poster=new Consulting_posting($ci);verify($poster->invoice($anchor['id'])['status']===0,'GL commit failure');
    verify($service->one('SELECT COUNT(*) n FROM jurnal')===$beforeGl && $service->one("SELECT nomorJC FROM pastibisa_tb_cabang WHERE nocab='101'")===$counterBefore,'GL journal and counter rollback together');
    $beforeAudit=count($GLOBALS['audit']);$GLOBALS['denied']=array('Invoicing.Add');verify(controllerCall($ci,'save_invoice',invoiceRequest($service,60))['status']===0,'Invoice add permission');$GLOBALS['denied']=array();verify(count($GLOBALS['audit'])===$beforeAudit+1,'Exactly one safe audit event per failed write');
    foreach($GLOBALS['audit']as $event){verify(!isset($event[2]['sql']) && empty(array_diff(array_keys($event[2]['data']),array('version','outcome','legacy_event_count'))),'Audit safe allowlist');}

    // Actual report SQL must use the new receivable/reduction, not the display-only gross.
    define('DBERP',$database);
    foreach(array("ALTER TABLE kons_tr_plan_tagih_detail ADD id_header VARCHAR(30) DEFAULT 'H1',ADD urutan INT DEFAULT 1", "ALTER TABLE kons_tr_spk_penawaran ADD nilai_kontrak DECIMAL(20,2) DEFAULT 1000000,ADD input_date DATE DEFAULT '2026-01-01',ADD nm_project_leader VARCHAR(100),ADD nm_sales VARCHAR(100)", "CREATE TABLE kons_tr_plan_tagih_header(id VARCHAR(30) PRIMARY KEY,nm_customer VARCHAR(100),id_spk_penawaran VARCHAR(100))", "INSERT INTO kons_tr_plan_tagih_header VALUES ('H1','Customer','SP1')")as $sql)verify($db->query($sql)!==false,'Report fixture');
    $report=(new ReflectionClass('Report_piutang_per_invoice_model'))->newInstanceWithoutConstructor();$report->db=$db;
    $reportRows=$report->get_report_data(array(1,4),'2026-12-31');$targetRows=array_values(array_filter($reportRows,function($row)use($receiptId){return $row['id_invoice']===$receiptId;}));
    verify(count($targetRows)===1 && $targetRows[0]['nilai_invoice']===$original['total_akhir_jurnal'],'Report new invoice value is initial receivable');
    verify($targetRows[0]['nilai_bayar']==='51.00','Report reduction includes actual PPh');
    $ci->input->values=array('filter_status'=>'invoiced');$ci->download_excel();
    verify($ci->load->viewName==='download_excel' && count($ci->load->viewData['list_data'])>0,'Actual export endpoint query');
    $html=$renderer->render(dirname(__DIR__).'/views/download_excel.php',$ci->load->viewData);
    verify(strpos($html,'Perlakuan PPN')!==false && strpos($html,'32010000.00')!==false,'Export retains treatment and exact PPN');
    // Tampered receipt ownership is rejected even if its totals still balance.
    newSource($db,92);$created=controllerCall($ci,'save_invoice',invoiceRequest($service,92));$post=$receiptPost(array($created['id']),array('10'),array('0'),array('1'));
    $GLOBALS['test_ci']=$receiptCi;$received=controllerCall($receiptCi,'save_penerimaan_piutang',$post);verify($received['status']===1,'Receipt for snapshot tamper test');
    $anchor=$service->one('SELECT id FROM tr_jurnal WHERE no_transaksi=? ORDER BY id LIMIT 1',array($received['no_surat']));$db->query('UPDATE tr_jurnal SET id_company=3 WHERE no_transaksi=?',array($received['no_surat']));$poster=new Consulting_posting($receiptCi);verify($poster->receipt($anchor['id'])['status']===0,'Posting rejects altered company despite balanced totals');
    $GLOBALS['test_ci']=$ci;

    $html=$renderer->render(dirname(__DIR__).'/views/consulting_print.php',array('invoice'=>$viewInvoice,'snapshot'=>$savedSnapshot,'error'=>null));verify(strpos($html,'<script>alert(1)</script>')===false && strpos($html,number_format((float)$viewInvoice['tagihan_ppn_jurnal'],2))!==false,'Print escaped and uses saved gross');
    $legacySave=array('no_inv'=>'1','id_inv_1'=>'LEGACY','id_alokasi'=>'200');$GLOBALS['test_ci']=$receiptCi;
    verify(controllerCall($receiptCi,'save_penerimaan_piutang',$legacySave)['status']===0,'Legacy-only cannot overwrite allocation used by v3');
    $db->query('INSERT INTO tr_alokasi_split VALUES (201,100,1000,0,1)');
    $db->insert('tr_penerimaan_piutang',array('no_surat'=>'OLD-SIBLING','id_alokasi'=>'201','nominal_penerimaan_bank'=>0));$oldHeader=$db->insert_id();
    verify(controllerCall($receiptCi,'rollback_penerimaan',array('id_penerimaan'=>$oldHeader))['status']===0,'Legacy sibling rollback cannot remove v3 parent splits');
    verify($service->one('SELECT COUNT(*) n FROM tr_alokasi_split')['n']==2,'Sibling splits preserved');
    $GLOBALS['test_ci']=$ci;$invoiceBefore=$service->one('SELECT * FROM tr_invoicing WHERE id=?',array($receiptId));
    verify(controllerCall($ci,'update_invoice_vuca',array('id_invoicing'=>$receiptId))['status']===0,'Vuca endpoint cannot rewrite new regular invoice');
    verify(controllerCall($ci,'update_invoice_non_konsultasi',array('id'=>$receiptId))['status']===0,'Nonconsulting endpoint cannot rewrite new regular invoice');
    verify($service->one('SELECT * FROM tr_invoicing WHERE id=?',array($receiptId))===$invoiceBefore,'Alternate endpoint guards preserve snapshot and amounts');

    $pphController=(new ReflectionClass('Penerimaan_pph_23'))->newInstanceWithoutConstructor();$pphController->db=$db;$pphController->input=new ConsultingInput;$pphController->auth=$ci->auth;
    $modernDetail=$service->one('SELECT id FROM tr_penerimaan_piutang_detail WHERE id_inv=? LIMIT 1',array($receiptId));
    $beforePphJournal=$service->one('SELECT COUNT(*) n FROM tr_jurnal');
    verify(controllerCall($pphController,'save_penerimaan_pph_23',array('id_detail_penerimaan'=>$modernDetail['id'],'no_invoice'=>$receiptId))['status']===0,'Legacy PPh recognition cannot duplicate actual PPh');
    verify($service->one('SELECT COUNT(*) n FROM tr_jurnal')===$beforePphJournal,'No duplicate PPh journals');
    newSource($db,93);$valid=invoiceRequest($service,93);
    foreach(array('nomor_po'=>'','nomor_faktur'=>'','tax_treatment'=>'OTHER','tanggal_invoice'=>'2026-02-30','sales_role'=>'made_up')as $key=>$bad){$invalid=$valid;$invalid[$key]=$bad;verify(controllerCall($ci,'save_invoice',$invalid)['status']===0,'Invalid required field '.$key);}
    $db->query('UPDATE kons_tr_actual_plan_tagih SET nominal_payment=1001 WHERE id_detail_plan_tagih=93');verify(controllerCall($ci,'save_invoice',$valid)['status']===0,'Source changed since preview');
    $mappingRow=$service->one('SELECT * FROM ms_invoice_coa_mapping WHERE source_company_id=1');$invalid=$settings;$invalid['owner_company_id']='4';$invalid['mapping_fingerprint']=Invoice_calculation::fingerprint($mappingRow);$invalid['accounts']['vat_wapu_prepaid']='1106-01-XX';verify(controllerCall($ci,'save_coa_mapping',$invalid)['status']===0,'Placeholder COA rejected');
    $invalid['accounts']['vat_wapu_prepaid']='9999-99-99';verify(controllerCall($ci,'save_coa_mapping',$invalid)['status']===0,'Missing COA rejected');
    $db->query('ALTER TABLE ms_invoice_coa_mapping ENGINE=MyISAM');verify(controllerCall($ci,'save_invoice',invoiceRequest($service,60))['status']===0,'Nontransactional prerequisite rejected');$db->query('ALTER TABLE ms_invoice_coa_mapping ENGINE=InnoDB');
    $db->query('ALTER TABLE tr_penerimaan_piutang_detail RENAME COLUMN consulting_snapshot TO missing_snapshot');verify(controllerCall($ci,'save_invoice',array())['status']===0,'Incomplete migration rejected');$db->query('ALTER TABLE tr_penerimaan_piutang_detail RENAME COLUMN missing_snapshot TO consulting_snapshot');
    echo 'PASS: '.$GLOBALS['checks']." consulting integration checks against disposable MariaDB\n";
} finally {
    if(isset($db))$db->close();
    if(isset($admin)&&!$admin->connect_errno){
        if(isset($database))$admin->query('DROP DATABASE IF EXISTS `'.$database.'`');
        if($started_server_proc){$admin->query('SHUTDOWN');}
        $admin->close();
    }
    if($started_server_proc){proc_close($started_server_proc);}
}
