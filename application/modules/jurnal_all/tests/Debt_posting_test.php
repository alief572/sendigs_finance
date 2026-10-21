<?php
define('BASEPATH',dirname(__DIR__,4).'/system/');define('APPPATH',dirname(__DIR__,3).'/');
function log_message($l,$m){}function show_error($m){throw new RuntimeException($m);}function load_class($n,$d){return null;}function is_php($v){return version_compare(PHP_VERSION,$v,'>=');}
class BF_Model{}class CI_Model{}class Admin_Controller{}
$allowed=true;function has_permission($p){global $allowed;return $allowed;}
require BASEPATH.'database/DB.php';
require APPPATH.'modules/jurnal_all/models/Jurnal_all_model.php';
require APPPATH.'modules/jurnal_all/libraries/Jurnal_debt_posting.php';
require APPPATH.'modules/jurnal_all/libraries/Jurnal_all_dispatcher.php';
require APPPATH.'modules/jurnal_payment_petty_cash/models/Jurnal_payment_petty_cash_model.php';
require APPPATH.'modules/jurnal_payment_petty_cash/models/Jurnal_payment_petty_cash_nomor_model.php';
require APPPATH.'modules/jurnal_payment_petty_cash/controllers/Jurnal_payment_petty_cash.php';
require APPPATH.'modules/jurnal_payment/controllers/Jurnal_payment.php';
require APPPATH.'modules/jurnal/controllers/Jurnal.php';
$cfg=['hostname'=>'127.0.0.1','port'=>3307,'username'=>'root','password'=>getenv('JOURNAL_TEST_PASSWORD')?:'',
    'database'=>'','dbdriver'=>'mysqli','db_debug'=>false,'char_set'=>'utf8mb4','dbcollat'=>'utf8mb4_general_ci','save_queries'=>true];
$rootDb=DB($cfg,true);
if(!$rootDb->conn_id){fwrite(STDERR,"Disposable MySQL connection unavailable.\n");exit(2);}
$checks=0;function verify($ok,$m){global $checks;$checks++;if(!$ok)throw new RuntimeException($m);}
class DebtFixtureDb extends CI_DB_mysqli_driver {
    public $fail='',$commitCount=0,$cleanupFail=false;
    public function insert($table='',$set=null,$escape=null){if($this->fail==='insert:'.$table)return false;return parent::insert($table,$set,$escape);}
    public function insert_batch($table,$set=null,$escape=null,$batch_size=100){if($this->fail==='insert:'.$table)return false;return parent::insert_batch($table,$set,$escape,$batch_size);}
    public function update($table='',$set=null,$where=null,$limit=null){if($this->fail==='update:'.$table){$this->_reset_write();return false;}return parent::update($table,$set,$where,$limit);}
    public function delete($table='',$where='',$limit=null,$reset_data=true){if($this->cleanupFail&&$table==='japh'){$this->_reset_write();return false;}return parent::delete($table,$where,$limit,$reset_data);}
    public function query($sql,$binds=false,$return_object=null){
        if($this->fail==='counter_query'&&strpos($sql,'SELECT nocab FROM')===0)return false;
        if(strpos($sql,'SELECT GET_LOCK(')===0)$sql='SELECT GET_LOCK(?, 0) AS acquired';
        return parent::query($sql,$binds,$return_object);
    }
    public function trans_begin($test_mode=false){if($this->fail==='begin')return false;return parent::trans_begin($test_mode);}
    public function trans_commit(){
        $this->commitCount++;
        if($this->fail==='commit:'.$this->commitCount)return false;
        $result=parent::trans_commit();
        return $this->fail==='lost_ack:'.$this->commitCount?false:$result;
    }
}
class DebtFixtureModel extends Jurnal_all_model {
    public $metadataFail=false;
    public function save_posting_metadata($h,$a,$n){if($this->metadataFail)throw new RuntimeException('metadata');return parent::save_posting_metadata($h,$a,$n);}
}
class DebtFixtureLoader {
    public $targets;
    public $owner,$recorder;
    public function model($path){}
    public function database($group,$return){return $this->targets[$group];}
    public function library($name){$this->owner->module_audit_recorder=$this->recorder;}
}
class DebtFixtureAuth {public function user_id(){return 7;}public function user_name(){return 'fixture-finance';}}
class DebtFixtureInput {public function method($upper){return 'POST';}public function post($key=null){return $key===null?$_POST:($_POST[$key] ?? null);}}
class Modules {public static $native,$calls=0;public static function run($route){self::$calls++;if($route!=='jurnal_payment_petty_cash/save_posting_jurnal')throw new RuntimeException('Unexpected route');ob_start();self::$native->save_posting_jurnal();return ob_get_clean();}}
class DebtFixtureRecorder {
    public $begins=0,$completions=0,$fail=false;
    public function begin($module,$method,$data,$keys){$this->begins++;}
    public function complete($message,$status,$context){$this->completions++;if($this->fail)throw new RuntimeException('audit writer');}
}
$prefix='codex_debt_'.bin2hex(random_bytes(5));$schemas=[];$connections=[];
try{
    foreach(['source','accounting_stm','accounting_sustain','accounting_vuca'] as $group){
        $name=$prefix.'_'.$group;$schemas[]=$name;
        if(!$rootDb->query('CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4'))throw new RuntimeException('Fixture schema failed');
        $connection=new DebtFixtureDb(array_merge($cfg,['database'=>$name]));$connection->initialize();$connections[$group]=$connection;
    }
    $source=$connections['source'];
    foreach([
        'CREATE TABLE tr_jurnal(id INT PRIMARY KEY,no_jurnal VARCHAR(255),no_transaksi VARCHAR(255),jenis_transaksi VARCHAR(100),sts VARCHAR(10),id_company VARCHAR(10),nm_company VARCHAR(100),tgl_jurnal DATE,coa VARCHAR(30),nm_coa VARCHAR(100),debit DECIMAL(20,2),kredit DECIMAL(20,2),keterangan TEXT,nm_divisi VARCHAR(100),tgl_posting DATETIME NULL,posted_by VARCHAR(64) NULL) ENGINE=InnoDB',
        "CREATE TABLE payment_approve(id VARCHAR(100) PRIMARY KEY,id_payment VARCHAR(100),no_doc VARCHAR(100),tipe VARCHAR(100),jumlah DECIMAL(20,2),status VARCHAR(10) DEFAULT '2') ENGINE=InnoDB",
        'CREATE TABLE tr_petty_cash_vuca_sustain(no_payment_hutang VARCHAR(100),company VARCHAR(100)) ENGINE=InnoDB'
    ] as $sql)verify($source->query($sql)!==false,'fixture source schema');
    foreach(['003_posting_metadata.sql','006_debt_posting_attempts.sql'] as $migration){
        $sql=file_get_contents(dirname(__DIR__).'/migrations/'.$migration);
        foreach(explode(';',$sql) as $statement)if(trim($statement)!=='')verify($source->query($statement)!==false,'fixture migration '.$migration);
    }
    foreach(['accounting_stm','accounting_sustain','accounting_vuca'] as $group){
        $target=$connections[$group];
        foreach([
            'CREATE TABLE japh(nomor VARCHAR(100) PRIMARY KEY,tgl DATE,jml DECIMAL(20,2),kdcab VARCHAR(20),jenis_reff VARCHAR(20),no_reff VARCHAR(255),bayar_kepada VARCHAR(100),user_id VARCHAR(100),ho_valid VARCHAR(10),batal VARCHAR(10)) ENGINE=InnoDB',
            'CREATE TABLE jurnal(id INT AUTO_INCREMENT PRIMARY KEY,tipe VARCHAR(20),nomor VARCHAR(100),tanggal DATE,no_perkiraan VARCHAR(30),keterangan TEXT,no_reff VARCHAR(255),debet DECIMAL(20,2),kredit DECIMAL(20,2)) ENGINE=InnoDB',
            'CREATE TABLE pastibisa_tb_cabang(nocab VARCHAR(20) PRIMARY KEY,subcab VARCHAR(10),nobuk INT) ENGINE=InnoDB',
            'CREATE TABLE coa_master(no_perkiraan VARCHAR(30) PRIMARY KEY) ENGINE=InnoDB'
        ] as $sql)verify($target->query($sql)!==false,'fixture target schema');
        $target->insert('pastibisa_tb_cabang',['nocab'=>'101','subcab'=>'A','nobuk'=>0]);$target->insert('coa_master',['no_perkiraan'=>'1101-02-01']);
    }
    $reset=function($other='SUSTAIN')use($connections,$source){
        foreach($connections as $connection){$connection->fail='';$connection->cleanupFail=false;$connection->trans_rollback();$connection->commitCount=0;}
        foreach(['tr_jurnal','payment_approve','tr_petty_cash_vuca_sustain','tr_jurnal_postings','tr_jurnal_debt_attempts'] as $table)$source->query('DELETE FROM '.$table);
        foreach(['accounting_stm','accounting_sustain','accounting_vuca'] as $group){$connections[$group]->query('DELETE FROM jurnal');$connections[$group]->query('DELETE FROM japh');$connections[$group]->query('UPDATE pastibisa_tb_cabang SET nobuk=0');}
        $source->insert('payment_approve',['id'=>'APP-1','id_payment'=>'VOUCHER-1','no_doc'=>'PHP-1','tipe'=>'petty_cash_hutang','jumlah'=>'100.25']);
        $source->insert('tr_petty_cash_vuca_sustain',['no_payment_hutang'=>'PHP-1','company'=>$other]);
        for($i=1;$i<=4;$i++)$source->insert('tr_jurnal',['id'=>$i,'no_jurnal'=>'NATIVE-'.$i,'no_transaksi'=>$i<=2?'VOUCHER-1':'APP-1','jenis_transaksi'=>Jurnal_debt_groups::TYPE,
            'sts'=>'0','id_company'=>'1','nm_company'=>$i<=2?'STM':$other,'tgl_jurnal'=>'2026-10-13','coa'=>'1101-02-01','nm_coa'=>'Bank','debit'=>$i%2?'100.25':'0','kredit'=>$i%2?'0':'100.25','keterangan'=>'Original '.$i,'nm_divisi'=>'Finance']);
        $ci=new stdClass();$ci->db=$source;$ci->auth=new DebtFixtureAuth();$ci->load=new DebtFixtureLoader();$ci->load->targets=$connections;
        $ci->Jurnal_all_model=new DebtFixtureModel();$ci->Jurnal_all_model->db=$source;
        $ci->Jurnal_payment_petty_cash_model=(new ReflectionClass('Jurnal_payment_petty_cash_model'))->newInstanceWithoutConstructor();
        $ci->Jurnal_payment_petty_cash_nomor_model=(new ReflectionClass('Jurnal_payment_petty_cash_nomor_model'))->newInstanceWithoutConstructor();
        $source->data_cache=[];return $ci;
    };
    foreach(['SUSTAIN','VUCA'] as $other){
        $ci=$reset($other);$service=new Jurnal_debt_posting(['ci'=>$ci]);$result=$service->execute(1);
        verify($result['status']===1&&count($result['accounting_numbers'])===2,'one action posts both targets '.$other);
        $group=$other==='VUCA'?'accounting_vuca':'accounting_sustain';
        foreach(['accounting_stm',$group] as $target){verify($connections[$target]->count_all('jurnal')===2&&$connections[$target]->count_all('japh')===1,'exactly one header/two original rows '.$target);verify((int)$connections[$target]->get('pastibisa_tb_cabang')->row()->nobuk===1,'counter increments once');}
        verify($source->where('sts','1')->count_all_results('tr_jurnal')===4,'all staging posted after target commits');
        verify($source->count_all('tr_jurnal_postings')===2,'metadata per native key');
        verify($source->get('tr_jurnal_debt_attempts')->row()->status==='posted','durable successful group');
        $postedDetail=$ci->Jurnal_all_model->debt_groups(true)->find(1);
        verify($postedDetail['header']['group_status']==='Sudah posting'&&$postedDetail['header']['posted_by']==='fixture-finance'&&!$postedDetail['can_post'],'posted modal reads actor and cannot repeat posting');
        foreach($postedDetail['company_sections'] as $section)verify(!empty($section['accounting_number'])&&$section['accounting_status']==='Terkonfirmasi','posted modal exposes only confirmed accounting number per destination');
        try{$service->execute(3);verify(false,'retry blocked');}catch(InvalidArgumentException $e){verify(true,'second anchor cannot duplicate posting');}
    }
    $ci=$reset();Modules::$calls=0;$reply=(new Jurnal_all_dispatcher(['ci'=>$ci]))->execute('post',1,'PCS',['no_transaksi'=>'ATTACK','accounting_numbers'=>[['number'=>'ATTACK','company'=>'ATTACK']]]);
    verify($reply['status']===1&&Modules::$calls===0,'Jurnal All dispatcher uses one coordinated service rather than calling native twice');
    verify(strpos(json_encode($source->get('tr_jurnal_postings')->result_array()),'ATTACK')===false,'client routing/numbers never enter confirmed metadata');
    $source->query(file_get_contents(dirname(__DIR__).'/migrations/006_debt_posting_attempts.sql'));
    verify($source->count_all('tr_jurnal_debt_attempts')===1,'idempotent registry migration preserves existing attempt');
    foreach(['insert:tr_jurnal_debt_attempts','lost_ack:1'] as $failure){
        $ci=$reset();$source->fail=$failure;try{(new Jurnal_debt_posting(['ci'=>$ci]))->execute(1);verify(false,'registry durable claim failure');}catch(RuntimeException $e){verify($connections['accounting_stm']->count_all('jurnal')===0&&$connections['accounting_sustain']->count_all('jurnal')===0,'registry claim failure happens before any accounting write');}
        if($failure==='lost_ack:1')verify($source->get('tr_jurnal_debt_attempts')->row()->status==='processing','uncertain registry commit remains blocking rather than automatically retrying');
    }
    $ci=$reset();$source->fail='update:tr_jurnal_debt_attempts';$reply=(new Jurnal_debt_posting(['ci'=>$ci]))->execute(1);
    verify($reply['outcome']==='uncertain'&&$source->get('tr_jurnal_debt_attempts')->row()->status==='processing','checkpoint writer failure leaves durable processing guard');
    foreach(['begin','counter_query','insert:japh','insert:jurnal','update:pastibisa_tb_cabang'] as $failure){
        $ci=$reset();$connections['accounting_sustain']->fail=$failure;$service=new Jurnal_debt_posting(['ci'=>$ci]);$result=$service->execute(1);
        verify($result['status']===0&&$result['outcome']==='failed_no_commit','precommit failure is retryable '.$failure);
        foreach(['accounting_stm','accounting_sustain'] as $target)verify($connections[$target]->count_all('jurnal')===0&&(int)$connections[$target]->get('pastibisa_tb_cabang')->row()->nobuk===0,'rollback rows/counter '.$target);
        verify($source->where('sts','0')->count_all_results('tr_jurnal')===4,'source stays pending');
        $connections['accounting_sustain']->fail='';verify((new Jurnal_debt_posting(['ci'=>$ci]))->execute(1)['status']===1,'retry after confirmed precommit rollback');
    }
    foreach(['accounting_stm','accounting_sustain'] as $target)foreach(['commit:1','lost_ack:1'] as $failure){
        $ci=$reset();$connections[$target]->fail=$failure;$service=new Jurnal_debt_posting(['ci'=>$ci]);$result=$service->execute(1);
        verify($result['status']===0&&$result['outcome']==='uncertain','commit ambiguity fails closed '.$target.'/'.$failure);
        verify($source->get('tr_jurnal_debt_attempts')->row()->status==='uncertain','uncertain result is durable');
        $connections[$target]->fail='';try{$service->execute(1);verify(false,'uncertain retry');}catch(InvalidArgumentException $e){verify(true,'uncertain retry blocked');}
    }
    foreach(['update:tr_jurnal','commit:2'] as $failure){
        $ci=$reset();$source->fail=$failure;$result=(new Jurnal_debt_posting(['ci'=>$ci]))->execute(1);
        verify($result['outcome']==='uncertain'&&$source->where('sts','0')->count_all_results('tr_jurnal')===4,'source failure after target commit requires reconciliation');
        verify($connections['accounting_stm']->count_all('jurnal')===2&&$connections['accounting_sustain']->count_all('jurnal')===2,'confirmed accounting not replayed/removed');
    }
    $ci=$reset();$ci->Jurnal_all_model->metadataFail=true;$result=(new Jurnal_debt_posting(['ci'=>$ci]))->execute(1);
    verify($result['status']===1&&!$result['metadata_saved']&&isset($result['warning']),'metadata failure preserves committed business result');
    verify($source->get('tr_jurnal_debt_attempts')->row()->status==='posted','metadata failure cannot reopen posting');
    $ci=$reset();$allowed=false;try{(new Jurnal_debt_posting(['ci'=>$ci]))->execute(1);verify(false,'permission');}catch(RuntimeException $e){verify($source->count_all('tr_jurnal_debt_attempts')===0,'permission denied before attempt');}$allowed=true;
    foreach([['1 OR 1=1','PCS'],[1,'PAY']] as $invalid){$ci=$reset();try{(new Jurnal_debt_posting(['ci'=>$ci]))->execute($invalid[0],$invalid[1]);verify(false,'tampered identity');}catch(InvalidArgumentException $e){verify($source->count_all('tr_jurnal_debt_attempts')===0,'tampered identity/source cannot claim posting');}}
    $ci=$reset();$source->where('id',1)->update('tr_jurnal',['coa'=>'2103-01-01']);
    try{(new Jurnal_debt_posting(['ci'=>$ci]))->execute(1);verify(false,'unclear coa');}catch(InvalidArgumentException $e){verify($source->count_all('tr_jurnal_debt_attempts')===0,'unclear COA blocked before write');}
    $ci=$reset();$connections['accounting_sustain']->query('DELETE FROM coa_master');
    try{(new Jurnal_debt_posting(['ci'=>$ci]))->execute(1);verify(false,'missing coa');}catch(InvalidArgumentException $e){verify($source->count_all('tr_jurnal_debt_attempts')===0,'target COA must exist');}
    $connections['accounting_sustain']->insert('coa_master',['no_perkiraan'=>'1101-02-01']);
    $ci=$reset();$connections['accounting_sustain']->query('ALTER TABLE jurnal ENGINE=MyISAM');
    try{(new Jurnal_debt_posting(['ci'=>$ci]))->execute(1);verify(false,'nontransactional engine');}catch(InvalidArgumentException $e){verify($source->count_all('tr_jurnal_debt_attempts')===0,'nontransactional engine cannot claim safe retry');}
    $connections['accounting_sustain']->query('ALTER TABLE jurnal ENGINE=InnoDB');
    $ci=$reset();$fresh=$ci->Jurnal_all_model->debt_groups(true)->find(1);$key=$fresh['header']['group_id'];
    $source->insert('tr_jurnal_debt_attempts',['group_key'=>$key,'fingerprint'=>$fresh['fingerprint'],'status'=>'processing','actor_id'=>'7','actor_name'=>'test','target_results'=>'{}','created_at'=>'2026-10-21 10:00:00','updated_at'=>'2026-10-21 10:00:00']);
    $source->where('group_key',$key)->update('tr_jurnal_debt_attempts',['target_results'=>'{"STM":{"number":"UNCONFIRMED","status":"prepared"}}']);
    $unconfirmed=$ci->Jurnal_all_model->debt_groups(true)->find(1);
    verify($unconfirmed['header']['group_status']==='Perlu rekonsiliasi'&&$unconfirmed['header']['source_numbers']===null&&empty($unconfirmed['company_sections'][0]['accounting_number']),'prepared number is not presented as confirmed accounting');
    try{(new Jurnal_debt_posting(['ci'=>$ci]))->execute(1);verify(false,'crash recovery');}catch(InvalidArgumentException $e){verify($connections['accounting_stm']->count_all('jurnal')===0,'crashed/in-progress attempt blocks replay');}
    $ci=$reset();$fresh=$ci->Jurnal_all_model->debt_groups(true)->find(1);$lock='jurnal_debt:'.substr($fresh['header']['group_id'],0,48);
    verify((int)$source->query('SELECT GET_LOCK(?,0) AS acquired',[$lock])->row()->acquired===1,'first connection holds voucher lock');
    $otherSource=new DebtFixtureDb(array_merge($cfg,['database'=>$prefix.'_source']));$otherSource->initialize();
    $otherCi=clone $ci;$otherCi->db=$otherSource;$otherCi->Jurnal_all_model=new DebtFixtureModel();$otherCi->Jurnal_all_model->db=$otherSource;
    try{(new Jurnal_debt_posting(['ci'=>$otherCi]))->execute(3);verify(false,'concurrent posting');}catch(InvalidArgumentException $e){verify($otherSource->count_all('tr_jurnal_debt_attempts')===0,'second connection cannot claim same voucher via another anchor');}
    $source->query('SELECT RELEASE_LOCK(?)',[$lock]);$otherSource->close();
    verify((new Jurnal_debt_posting(['ci'=>$ci]))->execute(1)['status']===1,'failed lock acquisition releases earlier member locks');
    foreach([false,true] as $auditFailure){
        $ci=$reset();$native=(new ReflectionClass('Jurnal_payment_petty_cash'))->newInstanceWithoutConstructor();foreach(get_object_vars($ci) as $property=>$value)$native->$property=$value;
        $native->input=new DebtFixtureInput();$native->load->owner=$native;$native->load->recorder=new DebtFixtureRecorder();$native->load->recorder->fail=$auditFailure;
        $_POST=['no_transaksi'=>'APP-1','jenis_transaksi'=>Jurnal_debt_groups::TYPE];
        ob_start();$native->save_posting_jurnal();$reply=json_decode(ob_get_clean(),true);
        verify($reply['status']===1&&$source->where('sts','1')->count_all_results('tr_jurnal')===4,'native entrypoint redirects the whole voucher, even with audit writer failure');
        verify($native->load->recorder->begins===1&&$native->load->recorder->completions===1,'native debt records one business outcome');
    }
    foreach(['Jurnal','Jurnal_payment'] as $legacy){
        $ci=$reset();$native=(new ReflectionClass('Jurnal_payment_petty_cash'))->newInstanceWithoutConstructor();foreach(get_object_vars($ci) as $property=>$value)$native->$property=$value;
        $native->input=new DebtFixtureInput();$native->load->owner=$native;$native->load->recorder=new DebtFixtureRecorder();Modules::$native=$native;Modules::$calls=0;
        $entry=(new ReflectionClass($legacy))->newInstanceWithoutConstructor();$entry->db=$source;$entry->input=new DebtFixtureInput();
        $_POST=['id'=>1,'source'=>'ATTACK','accounting_numbers'=>'ATTACK'];$original=$_POST;
        ob_start();$entry->save_posting_jurnal();$reply=json_decode(ob_get_clean(),true);
        verify($reply['status']===1&&Modules::$calls===1,'generic legacy entrypoint forwards debt only once: '.$legacy);
        verify($_POST===$original&&$source->where('sts','1')->count_all_results('tr_jurnal')===4,'trusted routing restores input and posts both native members');
    }
    foreach(['accounting_stm','accounting_sustain'] as $group)$connections[$group]->query('ALTER TABLE japh ENGINE=MyISAM');
    $ci=$reset();verify((new Jurnal_debt_posting(['ci'=>$ci]))->execute(1)['status']===1,'legacy MyISAM headers supported without engine migration');
    foreach(['begin','insert:japh','insert:jurnal','update:pastibisa_tb_cabang'] as $failure){
        $ci=$reset();$connections['accounting_sustain']->fail=$failure;$result=(new Jurnal_debt_posting(['ci'=>$ci]))->execute(1);
        verify($result['outcome']==='failed_no_commit','MyISAM precommit failure is retryable only after verified cleanup: '.$failure);
        foreach(['accounting_stm','accounting_sustain'] as $group){verify($connections[$group]->count_all('japh')===0&&$connections[$group]->count_all('jurnal')===0,'only new attempt headers compensated and InnoDB rows rolled back');verify((int)$connections[$group]->get('pastibisa_tb_cabang')->row()->nobuk===0,'counter rolled back with real transaction');}
        $connections['accounting_sustain']->fail='';verify((new Jurnal_debt_posting(['ci'=>$ci]))->execute(1)['status']===1,'confirmed compensation permits retry');
    }
    $ci=$reset();$connections['accounting_sustain']->fail='insert:jurnal';$connections['accounting_sustain']->cleanupFail=true;
    $result=(new Jurnal_debt_posting(['ci'=>$ci]))->execute(1);
    verify($result['outcome']==='uncertain'&&$source->get('tr_jurnal_debt_attempts')->row()->status==='uncertain','failed header compensation is durable reconciliation state');
    verify($connections['accounting_sustain']->count_all('japh')===1,'unconfirmed header is preserved for reconciliation');
    $connections['accounting_sustain']->fail='';$connections['accounting_sustain']->cleanupFail=false;
    try{(new Jurnal_debt_posting(['ci'=>$ci]))->execute(1);verify(false,'cleanup failure retry');}catch(InvalidArgumentException $e){verify(true,'cleanup failure cannot be retried automatically');}
    $ci=$reset();$connections['accounting_stm']->insert('japh',['nomor'=>'UNRELATED','no_reff'=>'UNRELATED','jml'=>'5']);$connections['accounting_sustain']->fail='insert:jurnal';
    verify((new Jurnal_debt_posting(['ci'=>$ci]))->execute(1)['outcome']==='failed_no_commit','compensation is limited to attempt-owned headers');
    verify($connections['accounting_stm']->get_where('japh',['nomor'=>'UNRELATED'])->num_rows()===1,'pre-existing unrelated header is never removed');
    $ci=$reset();$ci->Jurnal_payment_petty_cash_nomor_model->bind_debt_connections($connections);$collision=$ci->Jurnal_payment_petty_cash_nomor_model->get_nomor_buk('101','accounting_stm');
    $connections['accounting_stm']->insert('japh',['nomor'=>$collision,'no_reff'=>'EXISTING-OTHER','jml'=>'5']);
    verify((new Jurnal_debt_posting(['ci'=>$ci]))->execute(1)['outcome']==='failed_no_commit','existing number collision is detected before declaring ownership');
    verify($connections['accounting_stm']->get_where('japh',['nomor'=>$collision,'no_reff'=>'EXISTING-OTHER'])->num_rows()===1,'number collision cannot cause compensation of historical header');
    echo "PASS debt posting disposable MySQL: {$checks} assertions\n";
}finally{
    foreach($connections as $connection){$connection->trans_rollback();$connection->close();}
    foreach($schemas as $name){if(!preg_match('/^codex_debt_[a-f0-9]{10}_(source|accounting_stm|accounting_sustain|accounting_vuca)$/D',$name))throw new RuntimeException('Unsafe fixture cleanup');$rootDb->query('DROP DATABASE `'.$name.'`');}
    $rootDb->close();
}
