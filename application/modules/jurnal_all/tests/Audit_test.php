<?php
define('BASEPATH',true);class Admin_Controller{}class BF_Model{}
function log_message($level,$message){}
function has_permission($permission){return true;}
function &get_instance(){return $GLOBALS['ci'];}
$events=[];$rows=[];$writerFails=false;
function audit_log($message,$status,$row){global $events,$rows,$writerFails;$events[]='audit';if($writerFails)throw new RuntimeException('writer');$rows[]=$row+['status'=>$status];return true;}
require dirname(__DIR__,3).'/libraries/Module_audit_recorder.php';
require dirname(__DIR__).'/controllers/Jurnal_all.php';
require dirname(__DIR__,2).'/jurnal_expense/controllers/Jurnal_expense.php';
$checks=0;function check($ok,$message){global $checks;$checks++;if(!$ok)throw new RuntimeException($message);}
class Session {public function userdata($key){return ['id_user'=>7,'username'=>'Admin'];}}
class Input {public function method($upper){return 'POST';}public function post($key=null){$values=['id'=>1,'source'=>'EXP'];return $key===null?$values:($values[$key]??null);}}
class Output {public $body='';public function set_content_type($type){return $this;}public function set_output($body){$this->body=$body;return $this;}public function set_status_header($status){return $this;}}
class Load {public function library($name){}}
class Posting {public $fail=false;public function post_journal($id,$actor){global $events;$events[]=$this->fail?'commit_failed':'commit';$GLOBALS['ci']->module_audit_recorder->defer('legacy',1,['data'=>['password'=>'never keep']]);return ['save'=>$this->fail?0:1,'msg'=>'result','row_count'=>2,'cleanup_ok'=>true];}}
class Dispatch {
    public $source,$metadataWarning=false;
    public function execute($action,$id,$source,$payload){ob_start();$this->source->save_posting_jurnal();$response=json_decode(ob_get_clean(),true);return ['status'=>$response['save'],'msg'=>$response['msg']]+($this->metadataWarning?['warning'=>'Metadata belum tersimpan','outcome'=>'posted_metadata_missing']:[]);}
}
function fixture(){
    $GLOBALS['ci']=(object)['session'=>new Session()];
    $recorder=new Module_audit_recorder();$GLOBALS['ci']->module_audit_recorder=$recorder;
    $legacy=(new ReflectionClass('Jurnal_expense'))->newInstanceWithoutConstructor();$legacy->input=new Input();$legacy->load=new Load();$legacy->module_audit_recorder=$recorder;$legacy->Jurnal_expense_posting_model=new Posting();$legacy->auth=new class{public function user_id(){return 7;}};
    $controller=(new ReflectionClass('Jurnal_all'))->newInstanceWithoutConstructor();$controller->input=new Input();$controller->load=new Load();$controller->output=new Output();$controller->module_audit_recorder=$recorder;$controller->jurnal_all_dispatcher=new Dispatch();$controller->jurnal_all_dispatcher->source=$legacy;
    return [$controller,$legacy,$recorder];
}
[$c,$legacy,$recorder]=fixture();$c->save_posting_jurnal();
check(count($rows)===1&&$rows[0]['module']==='jurnal_all'&&$rows[0]['methode']==='save_posting_jurnal','native recorder one aggregate event despite expense nested audit');
check($events===['commit','audit'],'completion after source commit');
check(!isset($rows[0]['data']['password'])&&$rows[0]['sql']===null,'safe metadata no journal payload or SQL');
check($rows[0]['data']['legacy_event_count']===1,'legacy events deferred not duplicated');
check(json_decode($c->output->body,true)['status']===1,'native business response');
$rows=[];$events=[];[$c,$legacy,$recorder]=fixture();$legacy->Jurnal_expense_posting_model->fail=true;$c->save_posting_jurnal();
check(count($rows)===1&&$rows[0]['status']===0&&$events===['commit_failed','audit'],'failed commit single failure audit');
$rows=[];$events=[];$writerFails=true;[$c,$legacy,$recorder]=fixture();$c->save_posting_jurnal();$recorder->finish_interrupted();
check(json_decode($c->output->body,true)['status']===1&&!$rows,'writer failure preserves success');
check($events===['commit','audit']&&!$recorder->is_active(),'writer failure no retry/shutdown duplicate');
$writerFails=false;$rows=[];$events=[];[$c,$legacy,$recorder]=fixture();$c->jurnal_all_dispatcher->metadataWarning=true;$c->save_posting_jurnal();
check(count($rows)===1&&$rows[0]['status']===1&&$rows[0]['data']['outcome']==='posted_metadata_missing','metadata storage warning records one committed-business audit outcome');
check(json_decode($c->output->body,true)['status']===1&&isset(json_decode($c->output->body,true)['warning']),'metadata warning returned without false posting failure');
echo "PASS aggregate/native recorder integration: {$checks} assertions\n";
