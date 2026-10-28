<?php
define('BASEPATH', dirname(__DIR__,4).'/system/');
define('APPPATH', dirname(__DIR__,3).'/');
define('DBCNL','consultant');
set_error_handler(function($severity,$message){throw new ErrorException($message,0,$severity);});
function log_message($level,$message) {if(!empty($GLOBALS['paymentLogFails']))throw new RuntimeException('log unavailable');}
function show_error($message) { throw new RuntimeException($message); }
function load_class($name,$directory) { return null; }
function has_permission($permission) { return true; }
class BF_Model {} class Admin_Controller {}
require BASEPATH.'database/DB.php';
require dirname(__DIR__).'/libraries/Payment_patch.php';
require dirname(__DIR__).'/libraries/Payment_posting.php';
require dirname(__DIR__).'/libraries/Jurnal_all_dispatcher.php';
require dirname(__DIR__).'/models/Jurnal_all_model.php';
require dirname(__DIR__,2).'/jurnal_payment/controllers/Jurnal_payment.php';
require dirname(__DIR__).'/controllers/Jurnal_all.php';

$checks=0;
function check($ok,$message) { global $checks; $checks++; if(!$ok)throw new RuntimeException($message); }
function rejects($call,$message) { try{$call();}catch(Throwable $e){check(strpos($e->getMessage(),$message)!==false,'expected '.$message.', got '.$e->getMessage());return;}throw new RuntimeException('Expected rejection: '.$message); }
class PaymentResult {
    private $rows;
    public function __construct($rows){$this->rows=$rows;}
    public function result_array(){return $this->rows;}
    public function row(){return isset($this->rows[0])?(object)$this->rows[0]:null;}
    public function row_array(){return $this->rows[0] ?? [];}
    public function num_rows(){return count($this->rows);}
}
/** Real SQLite query-builder/storage; only MySQL locks and engine discovery are simulated. */
class PaymentFixtureDb {
    public $db_debug=false,$fail='',$commits=0,$engines='InnoDB',$locks=0,$hostname='fixture',$database='payment-source',$port='0';
    private $db;
    public function __construct(){ $this->db=DB(['dbdriver'=>'pdo','subdriver'=>'sqlite','dsn'=>'sqlite::memory:','db_debug'=>false],true); }
    public function __call($method,$args){
        if(in_array($method,['insert','insert_batch','update'],true)&&$this->fail===$method.':'.$args[0]){$this->db->reset_query();return false;}
        $result=call_user_func_array([$this->db,$method],$args);
        if ($result instanceof CI_DB_result) return new PaymentResult($result->result_array());
        return $result===$this->db?$this:$result;
    }
    public function query($sql,$args=[]){
        if(strpos($sql,'SELECT GET_LOCK(')===0){$this->locks++;return new PaymentResult([['acquired'=>$this->fail==='lock'?0:1]]);}
        if(strpos($sql,'SELECT RELEASE_LOCK(')===0){$this->locks--;return $this->fail==='release'?false:new PaymentResult([['released'=>1]]);}
        if(strpos($sql,'LOCK TABLES ')===0){if($this->fail==='tables_lock')return false;$this->locks++;return true;}
        if($sql==='UNLOCK TABLES'){$this->locks--;return true;}
        if(strpos($sql,'information_schema.TABLES')!==false)return new PaymentResult(array_map(function($name){return ['TABLE_NAME'=>$name,'ENGINE'=>$this->engines];},$args));
        $result=$this->db->query(str_replace(' FOR UPDATE','',$sql),$args);
        return $result instanceof CI_DB_result ? new PaymentResult($result->result_array()) : $result;
    }
    public function trans_commit(){
        $this->commits++;
        if($this->fail==='commit:'.$this->commits)return false;
        $ok=$this->db->trans_commit();
        return $this->fail==='commit_after:'.$this->commits?false:$ok;
    }
    public function queryCount(){return count($this->db->queries);}
}
class PaymentFixtureLoader {
    public $dbs;
    public function database($name,$return){if(!isset($this->dbs[$name]))throw new RuntimeException('Missing target');return $this->dbs[$name];}
    public function library($name){}
    public function view($name,$values,$return){extract($values);ob_start();require dirname(__DIR__).'/views/preview.php';return ob_get_clean();}
}
class PaymentFixtureOutput {
    public $body='',$status=200;
    public function set_output($value){$this->body=$value;return $this;}
    public function set_content_type($value){return $this;}
    public function set_status_header($value){$this->status=$value;return $this;}
}
class PaymentFixtureRecorder {public function begin($module,$method,$data,$keys){}public function complete($message,$status,$metadata){}}
class PaymentFixtureNumbers {public function get_no_buk($branch,$company){return 'ACC-'.$company.'-1';}}
class PaymentFixtureAuth {public function user_id(){return '42';} public function user_name(){return 'Finance';}}
class PaymentFixtureInput {public function method($upper){return 'POST';} public function post(){return $_POST;}}
function fixture($company='7') {
    $ci=(object)['db'=>new PaymentFixtureDb(),'load'=>new PaymentFixtureLoader(),'auth'=>new PaymentFixtureAuth(),'Jurnal_payment_nomor_model'=>new PaymentFixtureNumbers()];
    $schemas=[
        'CREATE TABLE tr_jurnal (id INTEGER PRIMARY KEY,no_transaksi TEXT,jenis_transaksi TEXT,sts TEXT,id_company TEXT,nm_company TEXT,tgl_jurnal TEXT,coa TEXT,nm_coa TEXT,debit DECIMAL(20,2),kredit DECIMAL(20,2),keterangan TEXT,no_jurnal TEXT,nm_divisi TEXT,tgl_posting TEXT,posted_by TEXT)',
        'CREATE TABLE payment_approve (id TEXT PRIMARY KEY,id_payment TEXT,no_doc TEXT,tipe TEXT)',
        'CREATE TABLE tr_payment_paid (id TEXT PRIMARY KEY)',
        'CREATE TABLE tr_jurnal_postings (no_transaksi TEXT,jenis_transaksi TEXT)',
        'CREATE TABLE tr_jurnal_payment_attempts (voucher TEXT PRIMARY KEY,source_key TEXT,fingerprint TEXT,status TEXT,accounting_number TEXT,company TEXT,actor_id TEXT,created_at TEXT,updated_at TEXT)',
        'CREATE TABLE jurnaltras (no_reff TEXT,no_request TEXT)'
    ];
    foreach($schemas as $sql)check($ci->db->query($sql)!==false,'source fixture schema');
    foreach(['accounting_stm','accounting_vuca','accounting_sustain','accounting'] as $name){
        $acc=new PaymentFixtureDb();
        foreach([
            'CREATE TABLE jurnal (tipe TEXT,nomor TEXT,tanggal TEXT,no_reff TEXT,no_perkiraan TEXT,keterangan TEXT,debet DECIMAL(20,2),kredit DECIMAL(20,2))',
            'CREATE TABLE japh (nomor TEXT,tgl TEXT,jml DECIMAL(20,2),kdcab TEXT,jenis_reff TEXT,no_reff TEXT,jenis_ap TEXT,note TEXT,user_id TEXT,ho_valid TEXT,batal TEXT)',
            'CREATE TABLE pastibisa_tb_cabang (nocab TEXT,nobuk INTEGER)',
            'CREATE TABLE coa_master (no_perkiraan TEXT)'
        ] as $sql)$acc->query($sql);
        $acc->insert('pastibisa_tb_cabang',['nocab'=>'101','nobuk'=>0]);$acc->insert('coa_master',['no_perkiraan'=>'5101']);
        $ci->load->dbs[$name]=$acc;
    }
    $ci->db->insert('payment_approve',['id'=>'APP-1','id_payment'=>'BK--1026-0004','no_doc'=>'KS-2026-00331','tipe'=>'kasbon']);
    $ci->db->insert('tr_payment_paid',['id'=>'BK--1026-0004']);
    foreach([[100,0],[0,100]] as $i=>$money)$ci->db->insert('tr_jurnal',['id'=>$i+1,'no_transaksi'=>'APP-1','jenis_transaksi'=>'Payment','sts'=>'0','id_company'=>$company,'nm_company'=>'Company','tgl_jurnal'=>'2026-10-15','coa'=>'5101','nm_coa'=>'Biaya','debit'=>$money[0],'kredit'=>$money[1],'keterangan'=>'Payment {REF:OLD-DOC}','no_jurnal'=>'STAGE-'.$i,'nm_divisi'=>'Finance']);
    return $ci;
}

$ci=fixture();$r=new Payment_reference($ci);
check($r->inspect('APP-1')['voucher']==='BK--1026-0004','legacy approval resolves actual voucher');
$model=new Jurnal_all_model();$model->db=$ci->db;
$detail=$model->detail(1,false);$posted=false;
check($detail['header']['display_no_transaksi']==='BK--1026-0004'&&$detail['header']['no_referensi']==='KS-2026-00331','shared resolver populates pending detail');
ob_start();require dirname(__DIR__).'/views/preview.php';$html=ob_get_clean();
check(strpos($html,'OLD-DOC')===false&&substr_count($html,'BK--1026-0004')>=3,'preview line reference equals canonical voucher and strips REF override');
$ci->db->insert('payment_approve',['id'=>'REFILL-1','id_payment'=>'BK--1026-0004','no_doc'=>'RPC-1','tipe'=>'refill_pettycash']);
$ci->db->insert('tr_jurnal',['id'=>3,'no_transaksi'=>'APP-1,REFILL-1','jenis_transaksi'=>'Refill Pettycash','sts'=>'0']);
$ci->db->where('jenis_transaksi','Payment')->update('tr_jurnal',['no_transaksi'=>'BK--1026-0004']);
check(count($r->resolve('BK--1026-0004')['approvals'])===1,'canonical voucher still partitions legacy companion approvals');
$ci=fixture();$r=new Payment_reference($ci);
$ci->db->insert('payment_approve',['id'=>'APP-2','id_payment'=>'BK--1026-0004','no_doc'=>'DOC-2','tipe'=>'periodik']);
$ci->db->where('jenis_transaksi','Payment')->update('tr_jurnal',['no_transaksi'=>'APP-1,APP-2']);
check(count($r->inspect('APP-1,APP-2')['approvals'])===2,'multiple approvals one voucher');
$ci->db->where('id','APP-2')->update('payment_approve',['id_payment'=>'OTHER']);
rejects(function()use($r){$r->inspect('APP-1,APP-2');},'beberapa voucher');
$ci=fixture();$r=new Payment_reference($ci);$ci->db->where('id','APP-1')->update('payment_approve',['id_payment'=>'']);
rejects(function()use($r){$r->inspect('APP-1');},'belum memiliki');
$ci=fixture();$r=new Payment_reference($ci);$ci->db->delete('tr_payment_paid',['id'=>'BK--1026-0004']);
rejects(function()use($r){$r->inspect('APP-1');},'Record pembayaran');
$ci=fixture();$r=new Payment_reference($ci);$ci->db->insert('payment_approve',['id'=>'BK--1026-0004','id_payment'=>'OTHER','no_doc'=>'X','tipe'=>'periodik']);
check($r->inspect('APP-1')['voucher']==='BK--1026-0004','validated source approval is not reinterpreted as an unrelated approval named like its voucher');
$ci->db->where('jenis_transaksi','Payment')->update('tr_jurnal',['no_transaksi'=>'BK--1026-0004']);
check($r->resolve('BK--1026-0004')['voucher']==='BK--1026-0004','only valid paid relationship wins over unrelated unpaid/invalid approval');
$ci->db->insert('tr_payment_paid',['id'=>'OTHER']);
rejects(function()use($r){$r->resolve('BK--1026-0004');},'ambigu');
$ci->db->where('id',1)->update('tr_jurnal',['keterangan'=>'Piutang Lain-lain - APP-1']);
check($r->inspect('BK--1026-0004')['voucher']==='BK--1026-0004','journal approval suffix establishes canonical voucher identity');
$model=new Jurnal_all_model();$model->db=$ci->db;
check($model->detail(1,false)['header']['display_no_transaksi']==='BK--1026-0004'&&$model->detail(1,false)['can_post'],'batched preview resolver agrees with inspector for contextual voucher');
check($model->detail(1,false)['header']['category']==='Kasbon'&&$model->detail(1,false)['header']['no_referensi']==='KS-2026-00331','category and source document use the resolved journal relationship, not colliding Periodik approval');
$otherApproval=$ci->db->get_where('payment_approve',['id'=>'BK--1026-0004'])->row_array();
check((new Payment_posting($ci))->execute(1)['save']===1,'contextual collision can post when no prior accounting exists');
check($ci->load->dbs['accounting_stm']->get('japh')->row_array()['no_reff']==='BK--1026-0004','contextual posting writes actual voucher');
check($otherApproval===$ci->db->get_where('payment_approve',['id'=>'BK--1026-0004'])->row_array(),'unrelated approval is never changed');
$ci=fixture();$ci->db->insert('payment_approve',['id'=>'BK--1026-0004','id_payment'=>'OTHER','no_doc'=>'X','tipe'=>'periodik']);$ci->db->insert('tr_payment_paid',['id'=>'OTHER']);
$ci->db->update('tr_jurnal',['no_transaksi'=>'BK--1026-0004','keterangan'=>'Piutang - BK--1026-0004']);
check((new Payment_reference($ci))->inspect('BK--1026-0004')['voucher']==='OTHER','journal approval identity resolves its actual voucher instead of blindly preferring voucher namespace');
$ci->db->where('id',2)->update('tr_jurnal',['keterangan'=>'Piutang - APP-1']);
rejects(function()use($ci){(new Payment_reference($ci))->inspect('BK--1026-0004');},'ambigu');
$ci->db->update('tr_jurnal',['keterangan'=>'Piutang - APP-10']);
rejects(function()use($ci){(new Payment_reference($ci))->inspect('BK--1026-0004');},'ambigu');
$ci->db->update('tr_jurnal',['keterangan'=>'Piutang {REF:KS-2026-00331}']);
check((new Payment_reference($ci))->inspect('BK--1026-0004')['voucher']==='BK--1026-0004','exact journal document REF relates to the matching approval');
$ci->db->delete('tr_payment_paid',['id'=>'BK--1026-0004']);
rejects(function()use($ci){(new Payment_reference($ci))->inspect('BK--1026-0004');},'Record pembayaran');
foreach(['posted','metadata','accounting','tras','registry'] as $case){
    $ci=fixture();$ci->db->insert('payment_approve',['id'=>'BK--1026-0004','id_payment'=>'OTHER','no_doc'=>'X','tipe'=>'periodik']);$ci->db->insert('tr_payment_paid',['id'=>'OTHER']);
    $ci->db->update('tr_jurnal',['no_transaksi'=>'BK--1026-0004','keterangan'=>'Piutang - APP-1']);
    if($case==='posted')$ci->db->update('tr_jurnal',['sts'=>'1']);
    if($case==='metadata')$ci->db->insert('tr_jurnal_postings',['no_transaksi'=>'BK--1026-0004','jenis_transaksi'=>'Payment']);
    if($case==='accounting')$ci->load->dbs['accounting_stm']->insert('jurnal',['no_reff'=>'BK--1026-0004']);
    if($case==='tras')$ci->db->insert('jurnaltras',['no_request'=>'BK--1026-0004']);
    if($case==='registry')$ci->db->insert('tr_jurnal_payment_attempts',['voucher'=>'BK--1026-0004','status'=>'committed']);
    $before=$ci->db->get('tr_jurnal')->result_array();$beforeAccounting=$ci->load->dbs['accounting_stm']->get('jurnal')->result_array();
    check((new Payment_patch($ci))->inventory()['items'][0]['status']==='skipped','contextual identity cannot bypass existing '.$case);
    $rejected=false;try{(new Payment_posting($ci))->execute(1);}catch(Throwable $e){$rejected=true;}
    if($case==='tras')check(!$rejected&&$ci->db->get_where('tr_jurnal',['sts'=>'1'])->num_rows()===2,'legacy jurnaltras does not block company-target posting');
    else {
        check($rejected&&$before===$ci->db->get('tr_jurnal')->result_array(),'contextual existing '.$case.' staging remains untouched');
        check($beforeAccounting===$ci->load->dbs['accounting_stm']->get('jurnal')->result_array(),'contextual existing '.$case.' accounting remains untouched');
    }
}
$ci=fixture();$ci->db->insert('payment_approve',['id'=>'BK--1026-0004','id_payment'=>'BK--1026-0004','no_doc'=>'DOC-2','tipe'=>'periodik']);
check(count((new Payment_reference($ci))->resolve('BK--1026-0004')['approvals'])===2,'same relationship ID/voucher overlap retains all batch approvals');
foreach(['posted','mixed','null_status','date','actor','metadata','target','auto','tras','ref','company','balance','negative','collision','attempt','unavailable'] as $case){
    $ci=fixture();$r=new Payment_reference($ci);
    if($case==='posted')$ci->db->update('tr_jurnal',['sts'=>'1']);
    if($case==='mixed')$ci->db->where('id',1)->update('tr_jurnal',['sts'=>'1']);
    if($case==='null_status')$ci->db->where('id',1)->update('tr_jurnal',['sts'=>null]);
    if($case==='date')$ci->db->where('id',1)->update('tr_jurnal',['tgl_posting'=>'2026-10-01']);
    if($case==='actor')$ci->db->where('id',1)->update('tr_jurnal',['posted_by'=>'42']);
    if($case==='metadata')$ci->db->insert('tr_jurnal_postings',['no_transaksi'=>'APP-1','jenis_transaksi'=>'Payment']);
    if($case==='target')$ci->load->dbs['accounting_stm']->insert('japh',['no_reff'=>'APP-1']);
    if($case==='auto')$ci->load->dbs['accounting']->insert('jurnal',['no_reff'=>'KS-2026-00331']);
    if($case==='tras')$ci->db->insert('jurnaltras',['no_request'=>'BK--1026-0004']);
    if($case==='ref')$ci->load->dbs['accounting_stm']->insert('jurnal',['no_reff'=>'OLD-DOC']);
    if($case==='company')$ci->db->where('id',2)->update('tr_jurnal',['id_company'=>'4']);
    if($case==='balance')$ci->db->where('id',2)->update('tr_jurnal',['kredit'=>99]);
    if($case==='negative')$ci->db->where('id',2)->update('tr_jurnal',['debit'=>-1]);
    if($case==='collision')$ci->db->insert('tr_jurnal',['id'=>3,'no_transaksi'=>'BK--1026-0004','jenis_transaksi'=>'Payment','sts'=>'1']);
    if($case==='attempt')$ci->db->insert('tr_jurnal_payment_attempts',['voucher'=>'BK--1026-0004','status'=>'started']);
    if($case==='unavailable')unset($ci->load->dbs['accounting']);
    $before=$ci->db->get('tr_jurnal')->result_array();
    $manifest=(new Payment_patch($ci))->inventory();
    check($manifest['items'][0]['status']==='skipped','inventory safely excludes '.$case);
    check($before===$ci->db->get('tr_jurnal')->result_array(),'inventory never changes '.$case);
    $oldTras=$ci->db->get('jurnaltras')->result_array();
    $oldDefault=isset($ci->load->dbs['accounting'])?$ci->load->dbs['accounting']->get('jurnal')->result_array():[];
    $failed=false;try{(new Payment_posting($ci))->execute(1);}catch(Throwable $e){$failed=true;}
    if(in_array($case,['auto','tras','unavailable'],true)) {
        check(!$failed&&$ci->load->dbs['accounting_stm']->get('jurnal')->num_rows()===2,'company posting independent from legacy/default accounting '.$case);
        check($oldTras===$ci->db->get('jurnaltras')->result_array(),'legacy source journals unchanged '.$case);
        if(isset($ci->load->dbs['accounting']))check($oldDefault===$ci->load->dbs['accounting']->get('jurnal')->result_array(),'default accounting unchanged '.$case);
    } else {
        check($failed,'unsafe group rejected '.$case);
        check($ci->load->dbs['accounting_stm']->get('jurnal')->num_rows()===($case==='ref'?1:0),'posting blocked before writes '.$case);
    }
}

$manifestPath=dirname(__DIR__,4).'/scratch/payment-reference-'.bin2hex(random_bytes(6)).'.json';
try {
    $ci=fixture();$patch=new Payment_patch($ci);$before=$ci->db->get('tr_jurnal')->result_array();
    $manifest=$patch->inventory();check($manifest['items'][0]['status']==='eligible','safe group eligible');
    Payment_patch::save($manifestPath,$manifest);$patch->run($manifestPath);
    check($ci->db->get_where('tr_jurnal',['no_transaksi'=>'BK--1026-0004'])->num_rows()===2,'apply updates explicit members');
    $after=$ci->db->get('tr_jurnal')->result_array();$patch->run($manifestPath);
    check($after===$ci->db->get('tr_jurnal')->result_array(),'apply rerun no-op');
    $patch->run($manifestPath,true);check($before===$ci->db->get('tr_jurnal')->result_array(),'rollback restores only identity');
    $patch->run($manifestPath,true);check($before===$ci->db->get('tr_jurnal')->result_array(),'rollback rerun no-op');
    $patch->run($manifestPath);$ci->db->update('tr_jurnal',['sts'=>'1']);
    rejects(function()use($patch,$manifestPath){$patch->run($manifestPath,true);},'Fingerprint');
    foreach(['changed','commit','commit_after','update','environment','accounting','new_collision'] as $case){
        foreach([$manifestPath,$manifestPath.'.journal'] as $file)if(is_file($file))unlink($file);
        $ci=fixture();$patch=new Payment_patch($ci);Payment_patch::save($manifestPath,$patch->inventory());
        if($case==='changed')$ci->db->where('id',1)->update('tr_jurnal',['keterangan'=>'Changed']);
        elseif($case==='update')$ci->db->fail='update:tr_jurnal';
        elseif($case==='environment')$ci->db->database='different';
        elseif($case==='accounting')$ci->load->dbs['accounting']->insert('japh',['no_reff'=>'APP-1']);
        elseif($case==='new_collision'){$ci->db->insert('payment_approve',['id'=>'BK--1026-0004','id_payment'=>'OTHER','no_doc'=>'X','tipe'=>'periodik']);$ci->db->insert('tr_payment_paid',['id'=>'OTHER']);}
        else $ci->db->fail=$case.':1';
        $messages=['changed'=>'Fingerprint','commit'=>'Commit','commit_after'=>'Commit','update'=>'Penulisan','environment'=>'environment','accounting'=>'accounting','new_collision'=>'ambigu'];
        rejects(function()use($patch,$manifestPath){$patch->run($manifestPath);},$messages[$case]);
        if(in_array($case,['commit','commit_after','update'],true)){$ci->db->fail='';$patch->run($manifestPath);check($ci->db->get_where('tr_jurnal',['no_transaksi'=>'BK--1026-0004'])->num_rows()===2,'safe apply rerun resolves '.$case);}
        else check($ci->db->get_where('tr_jurnal',['no_transaksi'=>'APP-1'])->num_rows()===2,'refused candidate remains unchanged '.$case);
    }
    foreach([$manifestPath,$manifestPath.'.journal'] as $file)if(is_file($file))unlink($file);
    $ci=fixture();$patch=new Payment_patch($ci);Payment_patch::save($manifestPath,$patch->inventory());$patch->run($manifestPath);
    $ci->load->dbs['accounting_stm']->insert('japh',['no_reff'=>'BK--1026-0004']);
    rejects(function()use($patch,$manifestPath){$patch->run($manifestPath,true);},'accounting');
    check($ci->db->get_where('tr_jurnal',['no_transaksi'=>'BK--1026-0004'])->num_rows()===2,'rollback cannot touch accounting-linked pending staging');
    file_put_contents($manifestPath.'.journal','{"partial":',FILE_APPEND);
    rejects(function()use($patch,$manifestPath){$patch->run($manifestPath);},'tidak lengkap');
} finally {foreach([$manifestPath,$manifestPath.'.journal'] as $file)if(is_file($file))unlink($file);}

foreach(['4'=>'accounting_vuca','1'=>'accounting_stm','6'=>'accounting_stm','7'=>'accounting_stm','3'=>'accounting_sustain'] as $company=>$name){
    $ci=fixture((string)$company);$result=(new Payment_posting($ci))->execute(1);$acc=$ci->load->dbs[$name];
    check($result['save']===1&&$result['accounting_numbers'][0]['company']===(string)$company,'posting returns committed target '.$company);
    check($acc->get_where('jurnal',['no_reff'=>'BK--1026-0004'])->num_rows()===2,'detail actual voucher '.$company);
    check($acc->get_where('japh',['no_reff'=>'BK--1026-0004'])->num_rows()===1,'header actual voucher '.$company);
    check($ci->db->get_where('tr_jurnal',['sts'=>'1'])->num_rows()===2,'source marked after accounting commit '.$company);
    check($ci->db->get('tr_jurnal_payment_attempts')->row_array()['status']==='committed','durable retry barrier '.$company);
    rejects(function()use($ci){(new Payment_posting($ci))->execute(1);},'sudah diposting');
}
foreach(['detail','header','counter','target_commit','target_commit_after','source_commit','nontransactional'] as $case){
    $ci=fixture();$acc=$ci->load->dbs['accounting_stm'];
    if($case==='detail')$acc->fail='insert_batch:jurnal';
    if($case==='header')$acc->fail='insert:japh';
    if($case==='counter')$acc->fail='update:pastibisa_tb_cabang';
    if($case==='target_commit')$acc->fail='commit:1';
    if($case==='target_commit_after')$acc->fail='commit_after:1';
    if($case==='source_commit')$ci->db->fail='commit:2';
    if($case==='nontransactional')$acc->engines='MEMORY';
    $failed=false;try{(new Payment_posting($ci))->execute(1);}catch(Throwable $e){$failed=true;}check($failed,'writer failure reported '.$case);
    check(!$ci->db->get_where('tr_jurnal',['sts'=>'1'])->num_rows(),'failure does not confirm staging '.$case);
    $attempt=$ci->db->get('tr_jurnal_payment_attempts')->row_array();
    if(strpos($case,'commit')!==false){check($attempt['status']==='reconcile','uncertain commit blocks retries '.$case);rejects(function()use($ci){(new Payment_posting($ci))->execute(1);},'Percobaan');}
    elseif($case!=='nontransactional'){check($attempt['status']==='failed_safe'&&!$acc->get('jurnal')->num_rows(),'pre-commit failure rolls back '.$case);$acc->fail='';check((new Payment_posting($ci))->execute(1)['save']===1,'safe retry '.$case);}
}
foreach(['success','detail','header','counter','source_commit','tables_lock','detail_corruption','header_corruption','registry_update'] as $case){
    $ci=fixture();$acc=$ci->load->dbs['accounting_stm'];$acc->engines='MyISAM';
    $legacy=[['no_reff'=>'BK--1026-0004','nomor'=>'OLD-AUTO']];
    $ci->load->dbs['accounting']->insert_batch('jurnal',$legacy);
    $ci->db->insert('jurnaltras',['no_reff'=>'APP-1','no_request'=>'BK--1026-0004']);
    check((new Payment_reference($ci))->inspectForPosting('APP-1')['target_engines']['jurnal']==='MYISAM','preview permits legacy engine and independent history '.$case);
    if($case==='detail')$acc->fail='insert_batch:jurnal';
    if($case==='header')$acc->fail='insert:japh';
    if($case==='counter')$acc->fail='update:pastibisa_tb_cabang';
    if($case==='source_commit')$ci->db->fail='commit:2';
    if($case==='tables_lock')$acc->fail='tables_lock';
    if($case==='detail_corruption')$acc->query("CREATE TRIGGER changed_detail AFTER INSERT ON jurnal BEGIN UPDATE jurnal SET keterangan='TRUNCATED' WHERE rowid=NEW.rowid; END");
    if($case==='header_corruption')$acc->query("CREATE TRIGGER changed_header AFTER INSERT ON japh BEGIN UPDATE japh SET no_reff='WRONG' WHERE rowid=NEW.rowid; END");
    if($case==='registry_update')$ci->db->fail='update:tr_jurnal_payment_attempts';
    $failed=false;try{$result=(new Payment_posting($ci))->execute(1);}catch(Throwable $e){$failed=true;}
    $attempt=$ci->db->get('tr_jurnal_payment_attempts')->row_array();
    check($acc->locks===0&&$ci->db->locks===0,'all locks released '.$case);
    check($ci->load->dbs['accounting']->get('jurnal')->row_array()['nomor']==='OLD-AUTO','existing default journal unchanged '.$case);
    if($case==='success'){
        check(!$failed&&$result['save']===1&&$attempt['status']==='committed','MyISAM confirmed success');
        check($acc->get('jurnal')->num_rows()===2&&$acc->get('japh')->num_rows()===1&&$acc->get('pastibisa_tb_cabang')->row()->nobuk==1,'legacy detail header and counter complete');
    } else {
        check($failed&&$ci->db->get_where('tr_jurnal',['sts'=>'1'])->num_rows()===0,'legacy failure cannot claim source success '.$case);
        check($attempt['status']===($case==='tables_lock'?'failed_safe':($case==='registry_update'?'started':'reconcile')),'legacy uncertain write has durable barrier '.$case);
        if($case!=='tables_lock')rejects(function()use($ci){(new Payment_posting($ci))->execute(1);},'Percobaan');
        if(in_array($case,['header','counter','source_commit'],true))check($acc->get('jurnal')->num_rows()===2,'partial/complete legacy writes preserved for reconciliation '.$case);
    }
}
$ci=fixture();$ci->db->insert('tr_jurnal',['id'=>3,'no_transaksi'=>'APP-1','jenis_transaksi'=>'Payment','sts'=>'0','id_company'=>'7','tgl_jurnal'=>'2026-10-15','coa'=>'ZERO-NOT-IN-TARGET','debit'=>0,'kredit'=>0,'keterangan'=>'Zero retained']);
check((new Payment_posting($ci))->execute(1)['save']===1,'zero-value staging rows do not require unused target COA');
check($ci->db->get_where('tr_jurnal',['sts'=>'1'])->num_rows()===3&&$ci->load->dbs['accounting_stm']->get('jurnal')->num_rows()===2,'zero staging row preserved and marked with complete group without zero accounting line');
foreach(['eligible','target_duplicate','missing_registry'] as $case){
    $ci=fixture('4');$acc=$ci->load->dbs['accounting_vuca'];$acc->engines='MyISAM';
    $ci->db->insert('jurnaltras',['no_request'=>'BK--1026-0004']);
    $ci->load->dbs['accounting']->insert('jurnal',['no_reff'=>'APP-1']);
    if($case==='target_duplicate')$acc->insert('japh',['no_reff'=>'APP-1']);
    if($case==='missing_registry')$ci->db->query('DROP TABLE tr_jurnal_payment_attempts');
    $preview=(new ReflectionClass('Jurnal_all'))->newInstanceWithoutConstructor();
    foreach(['db','load'] as $property)$preview->$property=$ci->$property;
    $preview->input=new PaymentFixtureInput();$preview->output=new PaymentFixtureOutput();$preview->module_audit_recorder=new PaymentFixtureRecorder();
    $preview->Jurnal_all_model=new Jurnal_all_model();$preview->Jurnal_all_model->db=$ci->db;
    $_POST=['id'=>1,'source'=>'PAY'];$preview->preview();$payload=json_decode($preview->output->body,true);
    check($preview->output->status===200&&$payload['show_post']===true,'real Payment preview exposes manager action '.$case);
    check($payload['can_post']===($case==='eligible'),'real Payment preview enables only target-ready pending '.$case);
    check($case==='eligible'?$payload['post_block_reason']==='':$payload['post_block_reason']!=='','preview block reason matches target readiness '.$case);
    check($ci->db->get_where('tr_jurnal',['sts'=>'0'])->num_rows()===2,'preview never posts source '.$case);
}
$ci=fixture();$ci->db->fail='lock';rejects(function()use($ci){(new Payment_posting($ci))->execute(1);},'sedang diproses');
// Missing rollout prerequisites must not hide known historical posting evidence.
$schemaCi=fixture();$schemaCi->db->query('DROP TABLE tr_jurnal_payment_attempts');
rejects(function()use($schemaCi){(new Payment_reference($schemaCi))->inspect('APP-1');},'migrasi 007');
$schemaCi->db->insert('jurnaltras',['no_reff'=>'APP-1','no_request'=>'BK--1026-0004']);
rejects(function()use($schemaCi){(new Payment_reference($schemaCi))->inspect('APP-1');},'posting ulang diblokir');
check($schemaCi->db->get_where('tr_jurnal',['sts'=>'0'])->num_rows()===2,'missing registry plus historical journal cannot change pending data');
check(!$ci->db->get('tr_jurnal_payment_attempts')->num_rows(),'lock failure cannot claim/write');
$ci=fixture();$ci->db->fail='insert:tr_jurnal_payment_attempts';rejects(function()use($ci){(new Payment_posting($ci))->execute(1);},'registry');
check(!$ci->load->dbs['accounting_stm']->get('japh')->num_rows(),'registry failure cannot write accounting');
$ci=fixture();$ci->db->fail='commit_after:1';rejects(function()use($ci){(new Payment_posting($ci))->execute(1);},'registry');
$ci->db->fail='';rejects(function()use($ci){(new Payment_posting($ci))->execute(1);},'Percobaan');
$ci=fixture();(new Payment_posting($ci))->execute(1);
$ci->db->update('tr_jurnal',['sts'=>'0','tgl_posting'=>null,'posted_by'=>null]);
rejects(function()use($ci){(new Payment_posting($ci))->execute(1);},'Percobaan');
check($ci->load->dbs['accounting_stm']->get('japh')->num_rows()===1,'missing metadata/staging confirmation cannot allow a duplicate');
$ci=fixture();$ci->db->fail='release';$GLOBALS['paymentLogFails']=true;
check((new Payment_posting($ci))->execute(1)['save']===1,'release/logger failure after commit cannot change business success');
$GLOBALS['paymentLogFails']=false;
$ci=fixture();$controller=(new ReflectionClass('Jurnal_payment'))->newInstanceWithoutConstructor();
foreach(['db','load','auth','Jurnal_payment_nomor_model'] as $property)$controller->$property=$ci->$property;
$controller->input=new PaymentFixtureInput();$_POST=['id'=>1,'no_transaksi'=>'FORGED'];
ob_start();$controller->save_posting_jurnal();$response=json_decode(ob_get_clean(),true);
check($response['save']===1,'native endpoint uses protected service');
check($ci->load->dbs['accounting_stm']->get('japh')->row_array()['no_reff']==='BK--1026-0004','client cannot forge voucher');
echo 'PASS Payment reference/posting/patch: '.$checks." assertions (SQLite storage, MySQL lock/engine simulation)\n";
