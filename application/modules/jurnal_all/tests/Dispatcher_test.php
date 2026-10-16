<?php
define('BASEPATH',true);
function has_permission($permission){global $permissions;return in_array($permission,$permissions,true);}
function log_message($level,$message){}
class BF_Model{}
require dirname(__DIR__).'/models/Jurnal_all_model.php';
require dirname(__DIR__).'/libraries/Jurnal_all_dispatcher.php';
$checks=0;
function check($ok,$msg){global $checks;$checks++;if(!$ok)throw new RuntimeException($msg);}
class Result {public $acquired=1;public function row(){return $this;}}
class Db {public $locked=true;public $events=[];public function query($sql,$params){$this->events[]=$sql;$r=new Result();$r->acquired=$this->locked?1:0;return $r;}}
class Data extends Jurnal_all_model {
    public $metadataReady=true,$metadataFail=false,$saved=[];
    public function posting_metadata_ready(){return $this->metadataReady;}
    public function save_posting_metadata($header,$actor,$numbers){if($this->metadataFail||!$numbers)throw new RuntimeException('metadata failure');$this->saved[]=[$header,$actor,$numbers];}
    public $type='Payment';public $posted=false;public $balance=true;
    public function detail($id,$posted){if($this->posted)throw new InvalidArgumentException('posted');return ['header'=>['id'=>$id,'source'=>$this->source($this->type),'no_transaksi'=>'1, 2','jenis_transaksi'=>$this->type,'id_company'=>'7'], 'rows'=>[['debit'=>'100.25','kredit'=>'100.25']],'is_balance'=>$this->balance];}
}
class Modules {
    public static $calls=[];public static $response='{"save":1,"msg":"posted","accounting_numbers":[{"company":"7","number":"FINAL-JV-1"}]}';public static $fail=false;
    public static function run($route){self::$calls[]=[$route,$_POST];if(self::$fail)throw new RuntimeException('handler failed');return self::$response;}
}
$permissions=['Jurnal_All.Manage'];$ci=(object)['db'=>new Db(),'Jurnal_all_model'=>new Data(),'auth'=>new class {public function user_id(){return 7;}public function user_name(){return 'finance';}}];
$dispatch=new Jurnal_all_dispatcher(['ci'=>$ci]);
foreach(['Invoicing'=>'INV','Penerimaan Piutang'=>'RCV','Payment'=>'PAY','Expense Report'=>'EXP','Petty Cash'=>'PCS','Refill Pettycash'=>'PCS','Payment Hutang Petty Cash'=>'PCS'] as $type=>$source){
    $ci->Jurnal_all_model->type=$type;$_POST=['original'=>'preserved'];Modules::$calls=[];
    $r=$dispatch->execute('post',1,$source,['no_transaksi'=>'ATTACK','jenis_posting'=>'refill','debit'=>1]);
    check($r['status']===1,'success '.$type);
    check(Modules::$calls[0][0]===Jurnal_all_dispatcher::ROUTES[$source]['post'],'native handler '.$type);
    check(Modules::$calls[0][1]['no_transaksi']==='1, 2'&&!isset(Modules::$calls[0][1]['jenis_posting']),'source key and routing parameters from server '.$type);
    check($_POST===['original'=>'preserved'],'POST restored '.$type);
}
$permissions=[];$before=count(Modules::$calls);try{$dispatch->execute('post',1,'PCS',[]);check(false,'permission');}catch(RuntimeException $e){check(count(Modules::$calls)===$before,'denied before handler');}
$permissions=['Jurnal_All.View'];try{$dispatch->execute('post',1,'PCS',[]);check(false,'view only');}catch(RuntimeException $e){check(true,'View cannot post');}
$permissions=['Jurnal_All.Manage'];$ci->Jurnal_all_model->type='Petty Cash';
foreach(['mismatch','posted','balance','lock','failure','invalid_response'] as $failure){
    $ci->Jurnal_all_model->posted=$failure==='posted';$ci->Jurnal_all_model->balance=$failure!=='balance';$ci->db->locked=$failure!=='lock';Modules::$fail=$failure==='failure';Modules::$response=$failure==='invalid_response'?'invalid':'{"status":1,"msg":"ok"}';
    try{$dispatch->execute('post',1,$failure==='mismatch'?'PAY':'PCS',[]);check(false,$failure);}catch(Throwable $e){check($_POST===['original'=>'preserved'],'failure cleanup '.$failure);}
}
$ci->Jurnal_all_model->posted=false;$ci->Jurnal_all_model->balance=true;$ci->db->locked=true;Modules::$fail=false;
Modules::$response='{"status":0,"msg":"commit failed"}';check($dispatch->execute('post',1,'PCS',[])['status']===0,'commit failure retained');
$ci->Jurnal_all_model->type='Payment';try{$dispatch->execute('revise',1,'PAY',['alasan_revisi'=>'x']);check(false,'unsupported revision');}catch(InvalidArgumentException $e){check(true,'unsupported revision blocked');}
$ci->Jurnal_all_model->type='Invoicing';try{$dispatch->execute('revise',1,'INV',[]);check(false,'reason');}catch(InvalidArgumentException $e){check(true,'revision reason required');}
Modules::$response='{"status":1,"msg":"revised"}';check($dispatch->execute('revise',1,'INV',['alasan_revisi'=>'Periksa kembali'])['status']===1,'supported revision');
check(count(array_filter($ci->db->events,function($s){return strpos($s,'RELEASE_LOCK')!==false;}))>0,'advisory locks released');
$ci->Jurnal_all_model->type='Payment';$ci->Jurnal_all_model->metadataReady=false;$before=count(Modules::$calls);
try{$dispatch->execute('post',1,'PAY',[]);check(false,'schema preflight');}catch(RuntimeException $e){check(count(Modules::$calls)===$before,'missing migration blocks before native posting');}
$ci->Jurnal_all_model->metadataReady=true;Modules::$response='{"save":1,"accounting_numbers":[{"company":"7","number":"FINAL-TRUSTED"}]}';
$result=$dispatch->execute('post',1,'PAY',['posted_by'=>999,'accounting_numbers'=>[['number'=>'ATTACK']]]);
$stored=end($ci->Jurnal_all_model->saved);check($result['metadata_saved']&&$stored[1]['id']===7&&$stored[1]['name']==='finance'&&$stored[2][0]['number']==='FINAL-TRUSTED','metadata actor and final numbers come only from auth/native response');
$before=count($ci->Jurnal_all_model->saved);Modules::$response='{"save":0,"msg":"commit failed"}';$dispatch->execute('post',1,'PAY',[]);
check(count($ci->Jurnal_all_model->saved)===$before,'failed native commit never records metadata');
$ci->Jurnal_all_model->metadataFail=true;Modules::$response='{"save":1,"accounting_numbers":[{"company":"7","number":"FINAL-TRUSTED"}]}';
$result=$dispatch->execute('post',1,'PAY',[]);check($result['status']===1&&!$result['metadata_saved']&&isset($result['warning'])&&$result['outcome']==='posted_metadata_missing','metadata writer failure preserves committed business outcome and warns without retry');
$ci->Jurnal_all_model->metadataFail=false;
echo "PASS Jurnal All dispatcher: {$checks} assertions\n";
