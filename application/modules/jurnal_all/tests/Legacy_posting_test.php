<?php
define('BASEPATH',true);define('DBACC_STM','acc_stm');define('DBACC_VUCA','acc_vuca');define('DBACC_SUST','acc_sustain');define('DBCNL','consultant');
class Admin_Controller{} class BF_Model{}
$permissions=['Jurnal_All.Manage'];
function has_permission($permission){global $permissions;return in_array($permission,$permissions,true);}
function log_message($level,$message){}
require dirname(__DIR__,2).'/jurnal_invoicing/models/Jurnal_invoicing_model.php';
require dirname(__DIR__,2).'/jurnal_invoicing/controllers/Jurnal_invoicing.php';
require dirname(__DIR__,2).'/jurnal_penerimaan/controllers/Jurnal_penerimaan.php';
require dirname(__DIR__,2).'/jurnal_payment/controllers/Jurnal_payment.php';
$checks=0;function check($ok,$message){global $checks;$checks++;if(!$ok)throw new RuntimeException($message);}
class NativeResult {
    private $rows;public function __construct($rows){$this->rows=$rows;}
    public function row(){return $this->rows?(object)$this->rows[0]:null;}
    public function result(){return array_map(function($r){return (object)$r;},$this->rows);}
    public function num_rows(){return count($this->rows);}
}
class NativeDb {
    public $rows=[],$writes=[],$events=[],$fail='',$table='',$company='7',$lookup='quote';
    public function __call($name,$args){return $this;}
    public function from($table){$this->table=$table;return $this;}
    public function get($table=null){return $this->get_where($table ?? $this->table,[]);}
    public function get_where($table,$where){
        if($table==='tr_jurnal'||$table==='tr_jurnal a')return new NativeResult(isset($where['id'])?[$this->rows[0]]:$this->rows);
        if(strpos($table,'tr_invoicing')!==false)return new NativeResult([['id'=>'INV1','no_invoice'=>'INV/1','id_penawaran'=>'Q1','id_spk_penawaran'=>'SPK1','id_customer'=>'C1','total_akhir_jurnal'=>'100.25','nm_customer'=>'Customer','tanggal_invoice'=>'2026-10-14','id_company'=>$this->company]]);
        if($table==='tr_penerimaan_piutang_detail')return new NativeResult([['id_inv'=>'INV1']]);
        if($table==='kons_tr_penawaran')return new NativeResult($this->lookup==='quote'?[['company'=>$this->company]]:[]);
        if($table==='kons_tr_spk_penawaran')return new NativeResult($this->lookup==='spk'?[['id_company'=>$this->company]]:[]);
        if($table==='kons_tr_penawaran_non_konsultasi')return new NativeResult([['id_company'=>$this->company]]);
        if($table==='kons_tr_company')return new NativeResult([['nm_company'=>'Company']]);
        return new NativeResult([['id'=>'1','no_doc'=>'DOC1']]);
    }
    private function write($table,$data){$this->events[]=$table;$this->writes[]=[$table,$data];return $this->fail!==$table;}
    public function insert($table,$data){return $this->write($table,$data);}
    public function insert_batch($table,$data){return $this->write($table,$data);}
    public function update($table,$data=[],$where=[]){return $this->write($table,$data);}
    public function query($sql){return $this->write('counter',$sql);}
    public function trans_begin(){$this->events[]='begin';return $this->fail!=='begin';}
    public function trans_status(){return $this->fail!=='transaction';}
    public function trans_commit(){$this->events[]='commit';return $this->fail!=='commit';}
    public function trans_rollback(){$this->events[]='rollback';return true;}
    public function error(){return ['message'=>'fixture failure'];}
}
class NativeInput {public $verb='POST';public function post($key=null){return $key===null?$_POST:($_POST[$key]??null);}public function method($upper){return $this->verb;}}
class NativeAuth {public function user_id(){return 7;}public function user_name(){return 'Admin';}}
class NativeOutput {public $status=200;public function set_status_header($status){$this->status=$status;}}
class NativeNumber {public $calls=[];public function __call($name,$args){$this->calls[]=[$name,$args];return 'NATIVE-001';}}
class NativeLoader {public $dbs=[];public function database($key,$return){return $this->dbs[$key];}}
function assign($object,$field,$value){$p=new ReflectionProperty($object,$field);$p->setAccessible(true);$p->setValue($object,$value);}
function fixture($class,$company='7',$type='Payment'){
    $c=(new ReflectionClass($class))->newInstanceWithoutConstructor();$c->input=new NativeInput();$c->auth=new NativeAuth();$c->output=new NativeOutput();$c->db=new NativeDb();$c->load=new NativeLoader();
    $row=['id'=>1,'no_transaksi'=>'1,2','jenis_transaksi'=>$type,'id_company'=>$company,'nm_company'=>'STM','tgl_jurnal'=>'2026-10-14','coa'=>'5101','nm_coa'=>'Piutang','debit'=>'100.25','kredit'=>'0.00','keterangan'=>'Description {REF:DOC1}'];
    $c->db->rows=[$row,array_merge($row,['id'=>2,'debit'=>'0.00','kredit'=>'100.25'])];
    foreach(['accounting_stm','accounting_vuca','accounting_sustain'] as $key){$c->load->dbs[$key]=new NativeDb();if(property_exists($c,$key))assign($c,$key,$c->load->dbs[$key]);}
    if(property_exists($c,'consultant')){$db=new NativeDb();$db->company=$company;assign($c,'consultant',$db);}
    $c->Jurnal_invoicing_nomor_model=new NativeNumber();$c->Jurnal_penerimaan_nomor_model=new NativeNumber();$c->Jurnal_payment_nomor_model=new NativeNumber();
    $_POST=['id'=>1];return $c;
}
function invoke($c){ob_start();$c->save_posting_jurnal();$value=json_decode(ob_get_clean(),true);$GLOBALS['last_native_response']=$value;check(is_array($value),'native JSON response');return $value['save']??$value['status'];}
foreach(['4'=>'accounting_vuca','1'=>'accounting_stm','6'=>'accounting_stm','7'=>'accounting_stm','3'=>'accounting_sustain'] as $company=>$target){
    foreach(['Jurnal_invoicing_model'=>'Invoicing','Jurnal_penerimaan'=>'Penerimaan Piutang','Jurnal_payment'=>'Payment'] as $class=>$type){
        $c=fixture($class,(string)$company,$type);check(invoke($c)===1,'native success '.$class.'/'.$company);
        $writes=$c->load->dbs[$target]->writes;check(count($writes)>0,'native routing '.$class.'/'.$target);
        foreach($c->load->dbs as $key=>$db)if($key!==$target)check(!$db->writes,'no other company '.$class.'/'.$key);
        $headers=array_values(array_filter($writes,function($w){return in_array($w[0],['javh','jarh','japh'],true);}));
        check(count($headers)===1&&$headers[0][1]['nomor']==='NATIVE-001','native number and one header '.$class);
        check(end($c->db->events)==='commit','success after commit '.$class);
        check($GLOBALS['last_native_response']['accounting_numbers'][0]['number']===$headers[0][1]['nomor']&&$GLOBALS['last_native_response']['accounting_numbers'][0]['company']===(string)$company,'reply final number matches committed native header '.$class);
    }
}
foreach(['Jurnal_invoicing_model'=>'Invoicing','Jurnal_penerimaan'=>'Penerimaan Piutang','Jurnal_payment'=>'Payment'] as $class=>$type){
    foreach(['header','detail','source','counter','commit'] as $failure){
        $c=fixture($class,'7',$type);
        if($failure==='commit')$c->db->fail='commit';
        elseif($failure==='source')$c->db->fail='tr_jurnal';
        else $c->load->dbs['accounting_stm']->fail=$failure==='header'?($class==='Jurnal_invoicing_model'?'javh':($class==='Jurnal_penerimaan'?'jarh':'japh')):($failure==='detail'?'jurnal':'counter');
        // Payment increments via update rather than a raw SQL counter query.
        if($failure==='counter'&&$class==='Jurnal_payment')$c->load->dbs['accounting_stm']->fail='pastibisa_tb_cabang';
        check(invoke($c)===0,'native failure '.$class.'/'.$failure);check(!isset($GLOBALS['last_native_response']['accounting_numbers']),'failure never confirms final number '.$class.'/'.$failure);check(end($c->db->events)==='rollback','rollback result '.$class.'/'.$failure);
    }
}
foreach(['Jurnal_invoicing','Jurnal_penerimaan','Jurnal_payment'] as $class){
    $c=fixture($class);$permissions=[];check(invoke($c)===0&&$c->output->status===403,'source permission '.$class);
    $permissions=['Jurnal_All.Manage'];$c->input->verb='GET';check(invoke($c)===0,'source POST only '.$class);
}
foreach(['Jurnal_invoicing_model'=>'Invoicing','Jurnal_penerimaan'=>'Penerimaan Piutang','Jurnal_payment'=>'Payment'] as $class=>$type){
    $c=fixture($class,'7',$type);$c->db->fail='begin';check(invoke($c)===0&&!$c->load->dbs['accounting_stm']->writes,'transaction begin failure stops native writes '.$class);
}
echo "PASS native Invoice/Receipt/Payment handlers: {$checks} assertions\n";
