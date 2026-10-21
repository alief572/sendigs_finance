<?php
define('BASEPATH',true);class Admin_Controller{}class BF_Model{}
$allowed=true;function has_permission($p){global $allowed;return $allowed;}
require dirname(__DIR__,2).'/jurnal_payment_petty_cash/controllers/Jurnal_payment_petty_cash.php';
require dirname(__DIR__,2).'/jurnal_payment_petty_cash/models/Jurnal_payment_petty_cash_model.php';
$checks=0;function check($ok,$msg){global $checks;$checks++;if(!$ok)throw new RuntimeException($msg);}
class PettyInput {public $verb='POST';public function method($upper){return $this->verb;}public function post($key){return $_POST[$key]??null;}}
class PettyDb {public $fail='';public $events=[];public function trans_begin(){return $this->fail!=='begin';}public function trans_status(){return $this->fail!=='transaction';}public function trans_commit(){$this->events[]='commit';return $this->fail!=='commit';}public function trans_rollback(){$this->events[]='rollback';}}
class PettyAuth{public function user_id(){return 7;}}
class PettyNumbers{
    public $fail='';public $calls=[];
    public function get_nomor_buk($branch,$db){$this->calls[]=['number',$db];return $this->fail==='number'?false:'BUK-'.$db;}
    public function increment_nobuk($branch,$db){$this->calls[]=['counter',$db];return $this->fail!=='counter';}
}
class PettySource {
    public $rows=[],$events=[],$fail='',$unclear=false;
    public function get_detail_by_transaksi($number,$type){return $this->fail==='empty'?[]:$this->rows;}
    public function validate_balance($rows){return $this->fail!=='balance';}
    public function find_unclear_coa($rows){return $this->unclear?['2103-01-01']:[];}
    public function begin_transaction($db){$this->events[]=['begin',$db];return $this->fail!=='begin-'.$db;}
    public function rollback_transaction($db){$this->events[]=['rollback',$db];}
    public function commit_transaction($db){$this->events[]=['commit',$db];return $this->fail!=='commit-'.$db;}
    public function check_transaction_status($db){return $this->fail!=='transaction';}
    public function update_status_posted($number,$type){$this->events[]=['status',$number,$type];return $this->fail!=='source';}
    public function post_jurnal_refill($header,$rows,$number,$db){$this->events[]=['single',$db,$number];return $this->fail!=='write';}
    public function post_jurnal_stm($header,$rows,$number){$this->events[]=['stm','accounting_stm',$number];return $this->fail!=='write';}
    public function post_jurnal_intercompany($company,$header,$rows,$companyNumber,$stmNumber){$this->events[]=['inter',$company,$companyNumber,$stmNumber];return $this->fail!=='write';}
}
function fixture($company='STM',$type='Petty Cash',$multi=false){
    $c=(new ReflectionClass('Jurnal_payment_petty_cash'))->newInstanceWithoutConstructor();$c->input=new PettyInput();$c->auth=new PettyAuth();$c->db=new PettyDb();$c->Jurnal_payment_petty_cash_model=new PettySource();$c->Jurnal_payment_petty_cash_nomor_model=new PettyNumbers();
    foreach([$company,...($multi?['STM']:[])] as $side)foreach([[100.25,0],[0,100.25]] as $money)$c->Jurnal_payment_petty_cash_model->rows[]=(object)['no_transaksi'=>'NATIVE-KEY','id_company'=>$side==='STM'?'5':($side==='VUCA'?'4':'6'),'nm_company'=>$side,'debit'=>$money[0],'kredit'=>$money[1],'tgl_jurnal'=>'2026-10-14','keterangan'=>$type==='Refill Pettycash'?'Refill petty cash':'Description'];
    $_POST=['no_transaksi'=>'NATIVE-KEY','jenis_transaksi'=>$type];return $c;
}
function invoke($c){ob_start();$c->save_posting_jurnal();$r=json_decode(ob_get_clean(),true);$GLOBALS['last_petty_response']=$r;check(is_array($r),'native petty response');return $r['status'];}
function invokeRetainedDebtHelper($c){
    $rows=$c->Jurnal_payment_petty_cash_model->rows;$company=$rows[0]->nm_company;
    $method=new ReflectionMethod('Jurnal_payment_petty_cash',$company==='STM'?'_post_stm':'_post_intercompany');$method->setAccessible(true);
    $header=(object)['tgl'=>'2026-10-14','jml'=>100.25,'no_reff'=>'NATIVE-KEY','user_id'=>7];
    $args=[$header,$rows,'NATIVE-KEY','Payment Hutang Petty Cash'];if($company!=='STM')array_unshift($args,$company);
    ob_start();$method->invokeArgs($c,$args);$r=json_decode(ob_get_clean(),true);$GLOBALS['last_petty_response']=$r;
    check(is_array($r),'retained native helper response');return $r['status'];
}
foreach(['STM'=>'accounting_stm','VUCA'=>'accounting_vuca','SUSTAIN'=>'accounting_sustain'] as $company=>$db){
    foreach(['Petty Cash','Refill Pettycash'] as $type){
        $c=fixture($company,$type);check(invoke($c)===1,'single/refill '.$company.'/'.$type);
        check(in_array(['single',$db,'BUK-'.$db],$c->Jurnal_payment_petty_cash_model->events,true),'single target and native number');
        check($GLOBALS['last_petty_response']['accounting_numbers']===[['company'=>$company,'number'=>'BUK-'.$db]],'single reply contains actual target number');
        check(in_array(['status','NATIVE-KEY',$type],$c->Jurnal_payment_petty_cash_model->events,true),'status uses native key');
    }
}
foreach(['VUCA','SUSTAIN'] as $company){
    foreach(['Petty Cash','Payment Hutang Petty Cash'] as $type){
        $c=fixture($company,$type,true);check(($type==='Payment Hutang Petty Cash'?invokeRetainedDebtHelper($c):invoke($c))===1,'intercompany '.$company.'/'.$type);
        $db=$company==='VUCA'?'accounting_vuca':'accounting_sustain';
        check(in_array(['inter',$company,'BUK-'.$db,'BUK-accounting_stm'],$c->Jurnal_payment_petty_cash_model->events,true),'intercompany two native numbers');
        check($GLOBALS['last_petty_response']['accounting_numbers']===[['company'=>$company,'number'=>'BUK-'.$db],['company'=>'STM','number'=>'BUK-accounting_stm']],'intercompany reply contains both actual committed numbers');
    }
}
$c=fixture('STM','Payment Hutang Petty Cash');check(invokeRetainedDebtHelper($c)===1&&in_array(['stm','accounting_stm','BUK-accounting_stm'],$c->Jurnal_payment_petty_cash_model->events,true),'retained STM hutang helper');
foreach(['empty','balance','write','source','transaction','commit-accounting_stm','commit-accounting_vuca','source_commit','source_begin','begin-accounting_stm','begin-accounting_vuca','number','counter','unclear'] as $failure){
    $c=fixture('VUCA','Petty Cash',true);
    if(in_array($failure,['number','counter'],true))$c->Jurnal_payment_petty_cash_nomor_model->fail=$failure;
    elseif($failure==='source_commit')$c->db->fail='commit';
    elseif($failure==='source_begin')$c->db->fail='begin';
    elseif($failure==='unclear')$c->Jurnal_payment_petty_cash_model->unclear=true;
    else $c->Jurnal_payment_petty_cash_model->fail=$failure;
    check(invoke($c)===0,'petty failure '.$failure);check(!isset($GLOBALS['last_petty_response']['accounting_numbers']),'petty failed commit/write has no confirmed numbers');
}
$allowed=false;$c=fixture();check(invoke($c)===0&&!$c->Jurnal_payment_petty_cash_nomor_model->calls,'permission before write');$allowed=true;
$c=fixture();$c->input->verb='GET';check(invoke($c)===0,'petty POST only');
foreach([['STM','Petty Cash'],['SUSTAIN','Refill Pettycash'],['STM','Payment Hutang Petty Cash']] as $scenario){
    foreach(['source_commit','target_commit'] as $failure){
        $c=fixture($scenario[0],$scenario[1]);
        if($failure==='source_commit')$c->db->fail='commit';
        else $c->Jurnal_payment_petty_cash_model->fail='commit-'.($scenario[0]==='STM'?'accounting_stm':'accounting_sustain');
        check(($scenario[1]==='Payment Hutang Petty Cash'?invokeRetainedDebtHelper($c):invoke($c))===0,'distinct branch commit failure '.$scenario[1].'/'.$failure);
    }
}
// Run the real ledger model to verify company-side batch splitting, not a copy of its logic.
class Ledger {
    public $writes=[];public $fail='';
    public function insert($table,$row){$this->writes[]=[$table,$row];return $this->fail!==$table;}
    public function insert_batch($table,$rows){$this->writes[]=[$table,$rows];return $this->fail!==$table;}
    public function trans_commit(){return false;}
}
$model=(new ReflectionClass('Jurnal_payment_petty_cash_model'))->newInstanceWithoutConstructor();
$ledgers=[];foreach(['accounting_stm','accounting_vuca','accounting_sustain'] as $property){$p=new ReflectionProperty($model,$property);$p->setAccessible(true);$ledgers[$property]=new Ledger();$p->setValue($model,$ledgers[$property]);}
foreach(['VUCA'=>'accounting_vuca','SUSTAIN'=>'accounting_sustain'] as $company=>$target){
    foreach($ledgers as $ledger)$ledger->writes=[];
    $rows=[];foreach([[$company,$company==='VUCA'?'4':'6',90.10],['STM','5',80.20]] as $side)foreach([[$side[2],0],[0,$side[2]]] as $money)$rows[]=(object)['id_company'=>$side[1],'nm_company'=>$side[0],'debit'=>$money[0],'kredit'=>$money[1],'tgl_jurnal'=>'2026-10-14','coa'=>'5101','keterangan'=>'Safe','no_transaksi'=>'KEY'];
    $header=(object)['tgl'=>'2026-10-14','no_reff'=>'KEY','user_id'=>7];
    check($model->post_jurnal_intercompany($company,$header,$rows,'COMPANY-NO','STM-NO')===true,'native ledger split '.$company);
    check($ledgers[$target]->writes[0][1]['jml']===90.10&&$ledgers['accounting_stm']->writes[0][1]['jml']===80.20,'per-side header totals');
    check(count($ledgers[$target]->writes[1][1])===2&&$ledgers['accounting_stm']->writes[1][1][0]['nomor']==='STM-NO','side rows and numbers');
}
check($model->commit_transaction('accounting_stm')===false,'real model propagates commit failure');
echo "PASS native Petty Cash handlers/model: {$checks} assertions\n";
