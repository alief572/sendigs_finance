<?php
define('BASEPATH', true);
class Admin_Controller {}
$allowed = true; $viewOnly = false;
function has_permission($permission) { global $allowed, $viewOnly; return $allowed && (!$viewOnly || $permission === 'Jurnal_Expense.View'); }
function log_message($level, $message) {}
require dirname(__DIR__) . '/controllers/Jurnal_expense.php';
$checks=0;
function check($ok,$message) { global $checks; $checks++; if(!$ok)throw new RuntimeException($message); }
class ExpenseInput {
    public $values=['id'=>10853,'draw'=>5]; public $verb='POST';
    public function post($key=null) { return $key===null ? $this->values : ($this->values[$key]??null); }
    public function get() { return $this->values; }
    public function method($upper) { return $this->verb; }
}
class ExpenseRecorder {
    public $events=[]; public $throw=false;
    public function begin($module,$method,$data,$keys) { $this->events[]=['begin',$module,$method,$keys]; }
    public function complete($message,$status,$data) { $this->events[]=['complete',$status,$data]; if($this->throw)throw new RuntimeException('writer'); }
}
class ExpenseLoader {
    public $fail=false; public $views=[];
    public function library($name) {}
    public function view($name,$data) { if($this->fail)throw new RuntimeException('render');$this->views[]=$name; }
}
class ExpenseOutput { public $code=200; public function set_status_header($code){$this->code=$code;} }
class ExpenseAuth { public function user_id(){return 7;} public function restrict($permission){if(!has_permission($permission))throw new RuntimeException('permission');} }
class ExpenseTemplate {
    public function set($data){} public function render($view){}
}
class ExpenseData {
    public $fail=false;public $calls=0;
    private function read(){ $this->calls++;if($this->fail)throw new RuntimeException('query'); }
    public function get_data_jurnal(){ $this->read();return ['draw'=>5,'recordsTotal'=>1,'recordsFiltered'=>1,'data'=>[['id'=>10853]]]; }
    public function get_pending_group($id){$this->read();if(!$id)throw new InvalidArgumentException('ID');return [(object)['id'=>$id]];}
    public function get_no_payment_jurnal(){ $this->read();return []; }
    public function get_company(){return [];}
    public function filters($values){return [];}
    public function get_list_jurnal($filter){$this->read();return [];}
}
class ExpensePosting {
    public $result=['save'=>1,'msg'=>'posted','row_count'=>6,'cleanup_ok'=>true];public $calls=0;public $ids=[];
    public function post_journal($id,$user){$this->calls++;$this->ids[]=$id;return $this->result;}
}
function fixture(){
    $c=(new ReflectionClass('Jurnal_expense'))->newInstanceWithoutConstructor();
    $c->input=new ExpenseInput();$c->load=new ExpenseLoader();$c->output=new ExpenseOutput();$c->auth=new ExpenseAuth();$c->template=new ExpenseTemplate();$c->module_audit_recorder=new ExpenseRecorder();$c->Jurnal_expense_model=new ExpenseData();$c->Jurnal_expense_posting_model=new ExpensePosting();return $c;
}
foreach(['index','get_data_jurnal','add_jurnal','save_posting_jurnal','export_jurnal'] as $method){
    $c=fixture();ob_start();$c->$method();$response=ob_get_clean();
    check(count($c->module_audit_recorder->events)===2,'one begin/complete at '.$method);
    check($c->module_audit_recorder->events[1][1]===1,'success audit at '.$method);
    if($method==='save_posting_jurnal')check(json_decode($response,true)['save']===1,'posting contract');
}
$allowed=false;
foreach(['get_data_jurnal','add_jurnal','save_posting_jurnal','export_jurnal'] as $method){
    $c=fixture();ob_start();$c->$method();$response=ob_get_clean();
    check($c->Jurnal_expense_model->calls===0&&$c->Jurnal_expense_posting_model->calls===0,'permission blocks reads/writes at '.$method);
    check($c->module_audit_recorder->events[1][1]===0,'permission failure audit at '.$method);
}
$c=fixture();try{$c->index();throw new LogicException('index permission');}catch(RuntimeException $e){check($c->Jurnal_expense_model->calls===0,'index restrict precedes read');}
$allowed=true;
$viewOnly=true;
foreach(['add_jurnal','save_posting_jurnal'] as $method){$c=fixture();ob_start();$c->$method();ob_end_clean();check($c->Jurnal_expense_model->calls===0&&$c->Jurnal_expense_posting_model->calls===0,'view permission cannot post '.$method);}
$viewOnly=false;
foreach(['add_jurnal','save_posting_jurnal'] as $method){$c=fixture();$c->input->verb='GET';ob_start();$c->$method();ob_end_clean();check($c->Jurnal_expense_model->calls===0&&$c->Jurnal_expense_posting_model->calls===0,'POST only '.$method);}
foreach(['get_data_jurnal','add_jurnal','export_jurnal'] as $method){$c=fixture();$c->Jurnal_expense_model->fail=true;ob_start();$c->$method();ob_end_clean();check($c->module_audit_recorder->events[1][1]===0,'query failure audit '.$method);}
$c=fixture();$c->Jurnal_expense_posting_model->result=['save'=>0,'msg'=>'commit failed','cleanup_ok'=>false];ob_start();$c->save_posting_jurnal();$result=json_decode(ob_get_clean(),true);check($result['save']===0&&$c->module_audit_recorder->events[1][1]===0,'commit and cleanup failure audit');
$c=fixture();$c->module_audit_recorder->throw=true;ob_start();$c->save_posting_jurnal();$result=json_decode(ob_get_clean(),true);check($result['save']===1,'audit writer failure cannot turn committed posting into failure');
$c=fixture();$c->input->values['id']=[];ob_start();$c->save_posting_jurnal();ob_end_clean();check($c->Jurnal_expense_posting_model->ids===[0],'malformed id cannot coerce to existing id');
$c=fixture();$c->input->values['jurnal']=[['id'=>1,'debit'=>1,'kredit'=>1]];ob_start();$c->save_posting_jurnal();ob_end_clean();check($c->Jurnal_expense_posting_model->ids===[10853],'payload nominal/detail IDs not passed to posting');
$base=file_get_contents(dirname(__DIR__,2).'/jurnal_payment/views/index.php');$new=file_get_contents(dirname(__DIR__).'/views/index.php');
preg_match('/<style>(.*?)<\/style>/s',$base,$a);preg_match('/<style>(.*?)<\/style>/s',$new,$b);check($a[1]===$b[1],'all existing CSS preserved');
$rows=[(object)['id'=>10853,'tgl_jurnal'=>'2026-10-14','jenis_transaksi'=>'<script>alert(1)</script>','coa'=>'<img>','nm_coa'=>'<svg>','keterangan'=>'</textarea><script>bad</script> {REF:<iframe>}','no_transaksi'=>'ER-1','debit'=>'100.25','kredit'=>'100.25']];$id=10853;
ob_start();include dirname(__DIR__).'/views/preview.php';$html=ob_get_clean();check(strpos($html,'<script>')===false&&strpos($html,'<iframe>')===false&&strpos($html,'&lt;script&gt;')!==false,'preview output escaping');check(strpos($html,'100.25')!==false,'preview cents retained');
$list_jurnal=[['no_transaksi'=>'<script>bad</script>','no_pengajuan'=>'<svg>','kategori_payment'=>'<img>','tgl_jurnal'=>'2026-10-14','nm_company'=>'<iframe>']];
ob_start();include dirname(__DIR__).'/views/export_excel.php';$html=ob_get_clean();check(strpos($html,'<script>')===false&&strpos($html,'&lt;script&gt;')!==false,'export escaping');
echo "PASS Jurnal Expense controller/views: {$checks} assertions\n";
