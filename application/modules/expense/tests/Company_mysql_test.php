<?php
if (PHP_SAPI !== 'cli') exit(2);
define('BASEPATH',dirname(__DIR__,4).'/system/');define('APPPATH',dirname(__DIR__,3).'/');define('FCPATH',dirname(__DIR__,4).'/');
function log_message($level,$message) {}
function show_error($message) { throw new RuntimeException($message); }
function load_class($name,$directory) { return null; }
function is_php($version) { return version_compare(PHP_VERSION,$version,'>='); }
function has_permission($permission) { return true; }
function int_to_roman($n) { return ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'][$n-1]; }
class Admin_Controller {} class BF_Model {} class CI_Model {}
require BASEPATH.'database/DB.php';
$worker=($argv[1] ?? '')==='--worker';
$prefix=$worker?$argv[2]:'codex_exp_company_'.getmypid();
if(!preg_match('/^codex_exp_company_[0-9]+$/D',$prefix))exit(3);
define('DBCNL',$prefix.'_consultant');define('DBACC_VUCA',$prefix.'_vuca');define('DBACC_STM',$prefix.'_stm');define('DBACC_SUST',$prefix.'_sustain');define('DBACC',DBACC_STM);
require dirname(__DIR__).'/controllers/Expense.php';
require dirname(__DIR__).'/libraries/Expense_company_repair.php';
require dirname(__DIR__,2).'/jurnal_expense/models/Jurnal_expense_model.php';
require dirname(__DIR__,2).'/jurnal_expense/models/Jurnal_expense_posting_model.php';
require dirname(__DIR__,2).'/jurnal_payment/models/Jurnal_payment_nomor_model.php';

function native_db($schema='') {
    return DB(['dbdriver'=>'mysqli','hostname'=>'127.0.0.1','port'=>(int)(getenv('EXPENSE_COMPANY_TEST_PORT')?:3391),'username'=>'root',
        'password'=>getenv('EXPENSE_COMPANY_TEST_PASSWORD')?:'expense_fixture_local','database'=>$schema,'char_set'=>'utf8mb4','dbcollat'=>'utf8mb4_general_ci','db_debug'=>false,'pconnect'=>false],true);
}
class Native_company_db {
    public $core,$fail='',$events=[],$dbdriver='mysqli';
    public function __construct($db){$this->core=$db;}
    public function __get($key){return $this->core->$key;}
    public function __call($method,$args){$this->events[]=$method.' '.(is_scalar($args[0]??null)?$args[0]:'');$r=$this->core->$method(...$args);return $r===$this->core?$this:$r;}
    public function insert_batch($table,$rows){if($this->fail==='insert'){ $this->core->insert_batch($table,array_slice($rows,0,1));return false;}return $this->core->insert_batch($table,$rows);}
    public function update($table,$values,$where=[]){if($this->fail==='update'){$this->core->reset_query();return false;}return $this->core->update($table,$values,$where);}
    public function trans_commit(){return $this->fail==='commit'?false:$this->core->trans_commit();}
}
class Native_company_loader {
    private $consultant;
    public function __construct(){ $this->consultant=native_db(DBCNL); }
    public function database($name,$return=true){return $this->consultant;}
    public function library($name){}
}
class Native_company_auth {public function user_name(){return 'finance';}public function user_id(){return 7;}public function is_admin(){return false;}}
class Native_company_input {public $values=[];public function post($key=null){return $key===null?$this->values:($this->values[$key]??null);}}
class Native_company_audit {
    public $events=[],$fail=false;
    public function begin($module,$method,$data,$keys){}
    public function complete($message,$status,$data){$this->events[]=[$status,$data];if($this->fail)throw new RuntimeException('writer');return true;}
}
function native_controller($db){
    $c=(new ReflectionClass('Expense'))->newInstanceWithoutConstructor();$c->db=$db;$c->load=new Native_company_loader();$c->auth=new Native_company_auth();$c->input=new Native_company_input();$c->module_audit_recorder=new Native_company_audit();return $c;
}
function native_call($c,$method,$id){$c->input->values=['id'=>$id,'chk_bukti'=>'1','chk_mutasi'=>'1','chk_nominal'=>'1','id_company'=>'7'];ob_start();if($method==='approve')$c->approve($id);else $c->$method();return json_decode(ob_get_clean(),true);}
if($worker){
    $c=native_controller(new Native_company_db(native_db($prefix.'_erp')));
    while(microtime(true)<(float)($argv[5]??0))usleep(1000);
    if($argv[3]==='repair'){
        $accounting=['accounting_vuca'=>native_db(DBACC_VUCA),'accounting_sustain'=>native_db(DBACC_SUST),'accounting_stm'=>native_db(DBACC_STM)];
        $repair=new Expense_company_repair($c->db,new Expense_company($c->db,$c->load->database('consultant')),$accounting,function($event){return true;});
        try{echo json_encode($repair->apply(json_decode(file_get_contents($argv[4]),true),'fixture.worker'));}
        catch(DomainException $error){echo json_encode(['status'=>'blocked']);}
    }else echo json_encode(native_call($c,$argv[3],$argv[4]));
    exit;
}

$root=native_db();$schemas=[$prefix.'_erp',DBCNL,DBACC_VUCA,DBACC_STM,DBACC_SUST];$checks=0;
function native_check($ok,$message){global $checks;$checks++;if(!$ok)throw new RuntimeException($message);}
function native_sql($db,$sql){$r=$db->query($sql);if($r===false)throw new RuntimeException('Fixture SQL failed: '.$db->error()['message']);return $r;}
function native_race($prefix,$action,$argument){
    $workers=[];$start=(string)(microtime(true)+0.4);
    foreach([1,2] as $i){$pipes=[];$process=proc_open([PHP_BINARY,__FILE__,'--worker',$prefix,$action,(string)$argument,$start],[1=>['pipe','w'],2=>['pipe','w']],$pipes);$workers[]=[$process,$pipes];}
    $results=[];foreach($workers as [$process,$pipes]){$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);foreach($pipes as $pipe)fclose($pipe);native_check(proc_close($process)===0&&$err==='','worker exits cleanly');$results[]=json_decode($out,true);}
    return $results;
}
try {
    foreach($schemas as $schema)native_sql($root,'CREATE DATABASE '.$schema);
    $core=native_db($prefix.'_erp');$db=new Native_company_db($core);
    native_sql($core,'CREATE TABLE tr_expense (id INT PRIMARY KEY,no_doc VARCHAR(100) UNIQUE,id_kasbon VARCHAR(100),id_bank_pengembalian VARCHAR(20),status VARCHAR(5),sts_finance VARCHAR(5),nama VARCHAR(100),total_kasbon DECIMAL(20,2),jumlah DECIMAL(20,2),lebih_bayar DECIMAL(20,2),transfer_coa_bank VARCHAR(50),transfer_jumlah DECIMAL(20,2),transfer_tanggal DATE,verified_by VARCHAR(100),verified_on DATETIME,verif_checklist VARCHAR(100),verif_reject_reason TEXT,approval VARCHAR(100),approved_by VARCHAR(100),approved_on DATETIME,tgl_approval_direktur DATETIME,sts_reject_manage VARCHAR(10),st_reject VARCHAR(10),reject_reason TEXT) ENGINE=InnoDB');
    native_sql($core,'CREATE TABLE tr_expense_detail (id INT AUTO_INCREMENT PRIMARY KEY,no_doc VARCHAR(100),id_kasbon VARCHAR(100),kasbon_pr_non_po_pett VARCHAR(100),expense DECIMAL(20,2),kasbon DECIMAL(20,2),coa VARCHAR(50),status INT) ENGINE=InnoDB');
    native_sql($core,'CREATE TABLE tr_kasbon (no_doc VARCHAR(100) PRIMARY KEY,project VARCHAR(100),id_expense_pett_pr_non_po VARCHAR(100),jumlah_kasbon DECIMAL(20,2)) ENGINE=InnoDB');
    native_sql($core,'CREATE TABLE tr_jurnal (id INT AUTO_INCREMENT PRIMARY KEY,no_transaksi VARCHAR(100),jenis_transaksi VARCHAR(100),id_company VARCHAR(10),nm_company VARCHAR(100),sts VARCHAR(5),no_jurnal VARCHAR(100),tgl_jurnal DATE,coa VARCHAR(50),nm_coa VARCHAR(100),debit DECIMAL(20,2),kredit DECIMAL(20,2),keterangan TEXT,id_divisi VARCHAR(100),nm_divisi VARCHAR(100),created_by VARCHAR(100),created_date DATETIME) ENGINE=InnoDB');
    native_sql($core,'CREATE TABLE ms_bank (id INT PRIMARY KEY,nama VARCHAR(100),rekening VARCHAR(100),coa_bank VARCHAR(50),deleted VARCHAR(1)) ENGINE=InnoDB');
    foreach([18=>'PT. Sentral Tehnologi Managemen',20=>'PT. VUCA STRATEGI BISNIS',21=>'PT. SENTRAL SUSTAINABILITY CONSULTING'] as $id=>$owner)$core->insert('ms_bank',['id'=>$id,'nama'=>$owner,'rekening'=>'fixture-'.$id,'coa_bank'=>'1101-02-01','deleted'=>'0']);
    native_sql($core,'CREATE TABLE tr_jurnal_postings (no_transaksi VARCHAR(100),jenis_transaksi VARCHAR(100)) ENGINE=InnoDB');
    $consultant=native_db(DBCNL);
    native_sql($consultant,'CREATE TABLE kons_tr_penawaran (id_quotation VARCHAR(100) PRIMARY KEY,company VARCHAR(10)) ENGINE=InnoDB');
    native_sql($consultant,'CREATE TABLE kons_tr_company (id VARCHAR(10) PRIMARY KEY,nm_company VARCHAR(100)) ENGINE=InnoDB');
    $names=['1'=>'STM-Vuca','4'=>'Vuca','3'=>'Sustain','6'=>'STM-Sustain','7'=>'STM'];
    foreach($names as $id=>$name){$consultant->insert('kons_tr_company',['id'=>$id,'nm_company'=>$name]);$consultant->insert('kons_tr_penawaran',['id_quotation'=>'Q-'.$id,'company'=>$id]);}
    $accounting=[];
    foreach(['accounting_vuca'=>DBACC_VUCA,'accounting_sustain'=>DBACC_SUST,'accounting_stm'=>DBACC_STM] as $target=>$schema){
        $acc=native_db($schema);$accounting[$target]=$acc;
        native_sql($acc,'CREATE TABLE coa_master (no_perkiraan VARCHAR(50) PRIMARY KEY,nama VARCHAR(100)) ENGINE=InnoDB');
        foreach(['1101-02-01','1103-01-15','1103-01-14','5101-01-01','9999-99-99'] as $coa)$acc->insert('coa_master',['no_perkiraan'=>$coa,'nama'=>$coa]);
        native_sql($acc,'CREATE TABLE pastibisa_tb_cabang (nocab VARCHAR(10) PRIMARY KEY,subcab VARCHAR(10),nomorJC INT) ENGINE=MyISAM');$acc->insert('pastibisa_tb_cabang',['nocab'=>'101','subcab'=>'A','nomorJC'=>10]);
        native_sql($acc,'CREATE TABLE javh (nomor VARCHAR(100) PRIMARY KEY,tgl DATE,jml DECIMAL(20,2),koreksi_no VARCHAR(100),kdcab VARCHAR(10),jenis VARCHAR(10),keterangan TEXT,bulan VARCHAR(2),tahun VARCHAR(4),user_id VARCHAR(100),memo TEXT,tgl_jvkoreksi DATE,ho_valid VARCHAR(10)) ENGINE=MyISAM');
        native_sql($acc,'CREATE TABLE jurnal (id INT AUTO_INCREMENT PRIMARY KEY,nomor VARCHAR(100),tipe VARCHAR(10),tanggal DATE,no_perkiraan VARCHAR(50),keterangan TEXT,no_reff VARCHAR(100),debet DECIMAL(20,2),kredit DECIMAL(20,2),stspos INT) ENGINE=MyISAM');
    }
    $seed=function($id,$company='1',$status='4')use($core,$names){
        $doc='ER-FIXTURE-'.$id;$kb='KS-FIXTURE-'.$id;
        $core->insert('tr_expense',['id'=>$id,'no_doc'=>$doc,'id_kasbon'=>$kb,'id_bank_pengembalian'=>in_array((string)$company,['1','4'],true)?20:(in_array((string)$company,['3','6'],true)?21:18),'status'=>$status,'sts_finance'=>'1','nama'=>'reporter','total_kasbon'=>1000,'jumlah'=>750,'lebih_bayar'=>250,'transfer_coa_bank'=>'1101-02-01','transfer_jumlah'=>250,'transfer_tanggal'=>'2026-10-29']);
        $core->insert('tr_expense_detail',['no_doc'=>$doc,'id_kasbon'=>$kb,'expense'=>750,'kasbon'=>1000,'coa'=>'5101-01-01']);
        $core->insert('tr_kasbon',['no_doc'=>$kb,'project'=>'Q-'.$company,'jumlah_kasbon'=>1000]);
        if($status==='4')foreach([[1000,0],[0,1000]] as $amount)$core->insert('tr_jurnal',['no_transaksi'=>$doc,'jenis_transaksi'=>'Expense Report','id_company'=>$company,'nm_company'=>$names[$company],'sts'=>'0','no_jurnal'=>'AJV-'.$id,'tgl_jurnal'=>'2026-10-29','coa'=>'1103-01-14','debit'=>$amount[0],'kredit'=>$amount[1],'keterangan'=>$doc]);
        return $doc;
    };
    foreach(['insert','update','commit','source'] as $i=>$failure){
        $id=10+$i;$seed($id);if($failure==='source')$core->update('tr_expense',['id_bank_pengembalian'=>'999'],['id'=>$id]);else $db->fail=$failure;
        $c=native_controller($db);$r=native_call($c,'verifikasi_pengembalian_approve',$id);$db->fail='';
        native_check(!$r['save']&&$core->get_where('tr_expense',['id'=>$id])->row()->status==='4','return rollback '.$failure);
        native_check($core->get_where('tr_jurnal',['no_transaksi'=>'ER-FIXTURE-'.$id,'jenis_transaksi'=>'Pengembalian Kasbon'])->num_rows()===0,'no partial return '.$failure);
    }
    foreach(['insert','update','commit','source'] as $i=>$failure){
        $id=20+$i;$seed($id,'1','1');if($failure==='source')$core->update('tr_kasbon',['project'=>null],['no_doc'=>'KS-FIXTURE-'.$id]);else $db->fail=$failure;
        $c=native_controller($db);$r=native_call($c,'approve',$id);$db->fail='';
        native_check(!$r['save']&&$core->get_where('tr_expense',['id'=>$id])->row()->status==='1','management rollback '.$failure);
        native_check($core->get_where('tr_jurnal',['no_transaksi'=>'ER-FIXTURE-'.$id])->num_rows()===0,'no partial realization '.$failure);
    }
    $seed(29);native_sql($core,'ALTER TABLE tr_expense_detail ENGINE=MyISAM');$c=native_controller($db);$r=native_call($c,'verifikasi_pengembalian_approve',29);
    native_check(!$r['save']&&strpos($r['message'],'InnoDB')!==false&&$core->get_where('tr_expense',['id'=>29])->row()->status==='4','nontransactional source blocks before status or journal writes');
    native_sql($core,'ALTER TABLE tr_expense_detail ENGINE=InnoDB');
    $seed(30);$results=native_race($prefix,'verifikasi_pengembalian_approve','30');$success=count(array_filter($results,function($r){return !empty($r['save']);}));
    native_check($success===1&&$core->get_where('tr_jurnal',['no_transaksi'=>'ER-FIXTURE-30','jenis_transaksi'=>'Pengembalian Kasbon'])->num_rows()===2,'two actual workers produce one return group');
    $seed(32,'1','1');$results=native_race($prefix,'approve','32');
    native_check(count(array_filter($results,function($r){return !empty($r['save']);}))===1&&$core->get_where('tr_jurnal',['no_transaksi'=>'ER-FIXTURE-32'])->num_rows()===3,'two actual workers produce one realization group');
    $c=native_controller($db);$seed(31);$c->module_audit_recorder->fail=true;$r=native_call($c,'verifikasi_pengembalian_approve',31);
    native_check($r['save']&&count($c->module_audit_recorder->events)===1,'audit exception preserves committed return and emits one completion');
    $seed(33,'1','1');native_check(native_call($c,'approve',33)['save']&&count($c->module_audit_recorder->events)===2,'audit exception preserves committed management approval');
    foreach($names as $company=>$name){
        $id=100+(int)$company;$doc=$seed($id,(string)$company);$c=native_controller($db);native_check(native_call($c,'verifikasi_pengembalian_approve',$id)['save'],'create return '.$company);
        $native=new Jurnal_expense_model();$native->db=$db;$native->load=$c->load;
        $numbers=(new ReflectionClass('Jurnal_payment_nomor_model'))->newInstanceWithoutConstructor();$numbers->db=$db;
        $posting=new Jurnal_expense_posting_model();$posting->db=$db;$posting->Jurnal_expense_model=$native;$posting->Jurnal_payment_nomor_model=$numbers;
        $anchor=$core->get_where('tr_jurnal',['no_transaksi'=>$doc,'jenis_transaksi'=>'Pengembalian Kasbon'])->row();
        $target=$accounting[Expense_company::target((string)$company)];
        $counterBefore=(int)$target->get_where('pastibisa_tb_cabang',['nocab'=>'101'])->row()->nomorJC;
        $r=$posting->post_journal($anchor->id,7);native_check($r['save']===1,'native post '.$company.': '.($r['msg']??''));
        native_check((int)$target->get_where('pastibisa_tb_cabang',['nocab'=>'101'])->row()->nomorJC===$counterBefore+1&&(int)substr($r['nomor'],-5)===$counterBefore+1,'actual number and counter use same target '.$company);
        native_check($target->get_where('javh',['nomor'=>$r['nomor']])->num_rows()===1&&$target->get_where('jurnal',['nomor'=>$r['nomor']])->num_rows()===2,'header and detail target '.$company);
        native_check($posting->post_journal($anchor->id,7)['save']===0,'repeat native post blocked '.$company);
    }
    foreach(['insert','update','commit'] as $i=>$failure){
        $id=300+$i;$doc=$seed($id);$c=native_controller($db);native_call($c,'verifikasi_pengembalian_approve',$id);
        $anchor=$core->get_where('tr_jurnal',['no_transaksi'=>$doc,'jenis_transaksi'=>'Pengembalian Kasbon'])->row();
        $acc=$accounting['accounting_vuca'];$beforeHeader=$acc->count_all('javh');$beforeDetail=$acc->count_all('jurnal');$counter=$acc->get_where('pastibisa_tb_cabang',['nocab'=>'101'])->row()->nomorJC;
        $db->fail=$failure;$r=$posting->post_journal($anchor->id,7);$db->fail='';
        native_check($r['save']===0&&$r['cleanup_ok'],'native posting failure '.$failure);
        native_check($acc->count_all('javh')===$beforeHeader&&$acc->count_all('jurnal')===$beforeDetail&&$acc->get_where('pastibisa_tb_cabang',['nocab'=>'101'])->row()->nomorJC===$counter,'real MyISAM compensation '.$failure);
        native_check($core->get_where('tr_jurnal',['id'=>$anchor->id])->row()->sts==='0','source posting rollback '.$failure);
    }
    $doc=$seed(400);$c=native_controller($db);native_call($c,'verifikasi_pengembalian_approve',400);
    $anchor=$core->get_where('tr_jurnal',['no_transaksi'=>$doc,'jenis_transaksi'=>'Pengembalian Kasbon'])->row();
    $acc=$accounting['accounting_vuca'];$count=$acc->count_all('javh');$acc->delete('coa_master',['no_perkiraan'=>'1103-01-15']);
    $r=$posting->post_journal($anchor->id,7);
    native_check($r['save']===0&&strpos($r['msg'],'COA')!==false&&$acc->count_all('javh')===$count,'missing COA in actual target blocks native writes');
    $acc->insert('coa_master',['no_perkiraan'=>'1103-01-15','nama'=>'1103-01-15']);
    $doc=$seed(200);$c=native_controller($db);native_call($c,'verifikasi_pengembalian_approve',200);
    $core->update('tr_jurnal',['id_company'=>'','nm_company'=>''],['no_transaksi'=>$doc,'jenis_transaksi'=>'Pengembalian Kasbon']);
    $events=[];$repair=new Expense_company_repair($db,new Expense_company($db,$consultant),$accounting,function($event)use(&$events){$events[]=$event;return true;});
    $entry=$repair->dryRun([$doc])['entries'][1];native_check($entry['eligible'],'native dry-run proves eligible legacy return');
    $db->fail='commit';try{$repair->apply($entry,'fixture');throw new LogicException('commit must fail');}catch(RuntimeException $error){}$db->fail='';
    native_check($core->get_where('tr_jurnal',['no_transaksi'=>$doc,'jenis_transaksi'=>'Pengembalian Kasbon'])->row()->id_company==='','native repair failed commit rolls back');
    native_check($repair->apply($entry,'fixture')['status']==='applied','native repair commits proven source');
    $doc=$seed(201);$c=native_controller($db);native_call($c,'verifikasi_pengembalian_approve',201);
    $core->update('tr_jurnal',['id_company'=>'','nm_company'=>''],['no_transaksi'=>$doc,'jenis_transaksi'=>'Pengembalian Kasbon']);
    $entry=$repair->dryRun([$doc])['entries'][1];$manifestPath=tempnam(sys_get_temp_dir(),'expense-company-race-');file_put_contents($manifestPath,json_encode($entry));
    try{$results=native_race($prefix,'repair',$manifestPath);}finally{unlink($manifestPath);}
    native_check(count(array_filter($results,function($r){return ($r['status']??'')==='applied';}))===1&&count(array_filter($results,function($r){return ($r['status']??'')==='blocked';}))===1,'concurrent repair applies manifest once');
    $doc=$seed(500);$c=native_controller($db);native_check(native_call($c,'verifikasi_pengembalian_approve',500)['save'],'seed historical return');
    $core->update('tr_kasbon',['project'=>null],['no_doc'=>'KS-FIXTURE-500']);
    $core->update('tr_expense',['id_bank_pengembalian'=>'18'],['id'=>500]);
    $core->update('tr_jurnal',['id_company'=>'','nm_company'=>''],['no_transaksi'=>$doc,'jenis_transaksi'=>'Pengembalian Kasbon']);
    $entry=$repair->dryRun([$doc])['entries'][1];native_check($entry['eligible']&&$entry['company']['id']==='7','bank proves STM despite absent project and legacy fallback realization label');
    native_check($repair->apply($entry,'fixture')['status']==='applied','nonproject bank-backed history repair commits');
    native_check($core->get_where('tr_jurnal',['no_transaksi'=>$doc,'jenis_transaksi'=>'Expense Report'])->row()->id_company==='1','bank repair preserves realization history');
    $anchor=$core->get_where('tr_jurnal',['no_transaksi'=>$doc,'jenis_transaksi'=>'Pengembalian Kasbon'])->row();native_check($posting->post_journal($anchor->id,7)['save']===1,'repaired bank-backed nonproject return can post to STM');
    $doc=$seed(501,'3');$c=native_controller($db);native_call($c,'verifikasi_pengembalian_approve',501);
    $realizationId=$core->get_where('tr_jurnal',['no_transaksi'=>$doc,'jenis_transaksi'=>'Expense Report'])->row()->id;
    native_check($posting->post_journal($realizationId,7)['save']===1,'realization posted before return');
    $core->update('tr_jurnal',['id_company'=>'','nm_company'=>''],['no_transaksi'=>$doc,'jenis_transaksi'=>'Pengembalian Kasbon']);
    $entry=$repair->dryRun([$doc])['entries'][1];native_check($entry['eligible'],'exact posted realization is not mistaken for posted return');
    $repair->apply($entry,'fixture');$anchor=$core->get_where('tr_jurnal',['no_transaksi'=>$doc,'jenis_transaksi'=>'Pengembalian Kasbon'])->row();native_check($posting->post_journal($anchor->id,7)['save']===1,'return posts after separately posted realization');
    $core->update('tr_jurnal',['id_company'=>'','nm_company'=>'','sts'=>'0'],['no_transaksi'=>$doc,'jenis_transaksi'=>'Pengembalian Kasbon']);
    native_check(!$repair->dryRun([$doc])['entries'][1]['eligible'],'actual posted return remains blocked even if source status and company are corrupted');
    echo 'PASS Expense native MySQL: '.$checks." assertions; real concurrent workers, actual numbering and InnoDB/MyISAM tables\n";
} finally {
    foreach(array_reverse($schemas) as $schema)$root->query('DROP DATABASE IF EXISTS '.$schema);
    $root->close();
}
