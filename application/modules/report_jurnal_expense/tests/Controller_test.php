<?php
define('BASEPATH', true);
class Admin_Controller {}
$allowed=true;
function has_permission($permission) { global $allowed; return $allowed && $permission==='Report_Jurnal_Expense.View'; }
function log_message($level,$message) {}
require dirname(__DIR__).'/controllers/Report_jurnal_expense.php';
$checks=0;
function check($ok,$message) { global $checks; $checks++; if(!$ok)throw new RuntimeException($message); }
class ReportRequest {
    public $values=['id'=>1,'draw'=>4];
    public function post($key=null) { return $key===null?$this->values:($this->values[$key]??null); }
    public function get() { return $this->values; }
}
class ReportAudit {
    public $events=[];public $fail=false;
    public function begin($module,$method,$context,$keys){$this->events[]=['begin',$module,$method,$keys];}
    public function complete($message,$status,$context){$this->events[]=['complete',$status,$context];if($this->fail)throw new RuntimeException('writer');}
}
class ReportLoader {
    public $fail=false;
    public function library($name){}
    public function view($name,$data,$return){if($this->fail)throw new RuntimeException('view');return '<table>'.$name.'</table>';}
}
class ReportOutput { public $status=200;public function set_status_header($status){$this->status=$status;} }
class ReportAuth { public function restrict($permission){if(!has_permission($permission))throw new RuntimeException('denied');} }
class ReportTemplate { public function set($data){}public function render($name){} }
class ReportData {
    public $calls=0;public $fail=false;public $detailIds=[];public $invalidFilter=false;public $empty=false;
    private function read(){ $this->calls++;if($this->fail)throw new RuntimeException('query'); }
    public function get_data_jurnal(){ $this->read();return ['draw'=>4,'recordsTotal'=>1,'recordsFiltered'=>1,'data'=>[['id'=>1]]]; }
    public function get_detail_jurnal($id){$this->read();$this->detailIds[]=$id;if(!$id)throw new InvalidArgumentException('id');return [(object)['id'=>1]];}
    public function get_no_payment_jurnal(){ $this->read();return []; }
    public function get_company(){return [];}
    public function filters($values){if($this->invalidFilter)throw new InvalidArgumentException('date');return [];}
    public function get_list_jurnal($filter){$this->read();return $this->empty?[]:[['id'=>1]];}
}
function fixture(){
    $c=(new ReflectionClass('Report_jurnal_expense'))->newInstanceWithoutConstructor();$c->input=new ReportRequest();$c->load=new ReportLoader();$c->auth=new ReportAuth();$c->template=new ReportTemplate();$c->output=new ReportOutput();$c->module_audit_recorder=new ReportAudit();$c->Report_jurnal_expense_model=new ReportData();return $c;
}
foreach(['index','get_data_jurnal','view_jurnal','export_excel','export_jurnal'] as $method){
    $c=fixture();ob_start();$c->$method();ob_end_clean();
    check(count($c->module_audit_recorder->events)===2,'one scope/completion '.$method);
    check($c->module_audit_recorder->events[0][2]===$method,'correct method incl export alias');
    check($c->module_audit_recorder->events[1][1]===1,'success '.$method);
}
$allowed=false;
foreach(['index','get_data_jurnal','view_jurnal','export_excel','export_jurnal'] as $method){
    $c=fixture();ob_start();try{$c->$method();}catch(RuntimeException $e){check($method==='index','restrict only index');}ob_end_clean();
    check($c->Report_jurnal_expense_model->calls===0,'permission before any query '.$method);
    if($method!=='index')check($c->module_audit_recorder->events[1][1]===0,'denied audit '.$method);
}
$allowed=true;
foreach(['get_data_jurnal','view_jurnal','export_excel','export_jurnal'] as $method){$c=fixture();$c->Report_jurnal_expense_model->fail=true;ob_start();$c->$method();$output=ob_get_clean();check($c->module_audit_recorder->events[1][1]===0,'query failure '.$method);check(strpos($output,'query')===false,'safe error '.$method);}
foreach(['view_jurnal','export_excel','export_jurnal'] as $method){$c=fixture();$c->load->fail=true;ob_start();$c->$method();$output=ob_get_clean();check($c->module_audit_recorder->events[1][1]===0&&strpos($output,'<table>')===false,'render failure no partial success '.$method);}
foreach(['view_jurnal','export_excel','export_jurnal'] as $method){$c=fixture();$c->module_audit_recorder->fail=true;ob_start();$c->$method();$output=ob_get_clean();check(strpos($output,'<table>')!==false,'writer failure preserves report '.$method);}
$c=fixture();$c->input->values=['id'=>[],'no_transaksi'=>'ER-other','jenis_transaksi'=>'Payment','company'=>'4'];ob_start();$c->view_jurnal();ob_end_clean();check($c->Report_jurnal_expense_model->detailIds===[0]&&$c->output->status===400,'invalid ID, forged identity ignored');
$c=fixture();$c->Report_jurnal_expense_model->empty=true;ob_start();$c->export_excel();$html=ob_get_clean();check(strpos($html,'<table>')!==false&&$c->module_audit_recorder->events[1][2]['data']['row_count']===0,'empty export valid and audited');
$c=fixture();$c->Report_jurnal_expense_model->invalidFilter=true;ob_start();$c->export_excel();ob_end_clean();check($c->output->status===400&&$c->module_audit_recorder->events[1][1]===0,'invalid export filter');
echo "PASS Report Jurnal Expense controllers: {$checks} assertions\n";
