<?php
define('BASEPATH', __DIR__);
define('FCPATH', sys_get_temp_dir() . '/kasbon-selection/');
define('DBACC', 'fixture_accounting');
class Admin_Controller {}
require dirname(__DIR__) . '/controllers/Expense.php';
require dirname(__DIR__, 3) . '/libraries/Module_audit_recorder.php';
function has_permission($permission) { return true; }
function &get_instance() { return $GLOBALS['selectionCi']; }
function log_message($level,$message) {}
function audit_log($message,$status,$context) {
    $GLOBALS['selectionCi']->module_audit_recorder->events[]=array($status,$context);
    if (($GLOBALS['selectionWriter'] ?? '')==='exception') { throw new RuntimeException('PRIVATE_WRITER_ERROR'); }
    return ($GLOBALS['selectionWriter'] ?? '')!=='failure';
}
class SelectionAuth {
    public $admin = false;
    public $username = 'alief';
    public function is_admin() { return $this->admin; }
    public function user_name() { return $this->username; }
    public function user_id() { return '206'; }
}
class SelectionResult {
    private $rows;
    public function __construct($rows = array()) { $this->rows = $rows; }
    public function result() { return array_map(function($row) { return (object)$row; }, $this->rows); }
    public function result_array() { return $this->rows; }
    public function row() { return $this->rows ? (object)$this->rows[0] : null; }
}
class SelectionDb {
    public $sql = '';
    public $username = 'alief';
    public $empty = false;
    public $fail = false;
    public $writes = 0;
    public $begins = 0;
    private $from = '';
    private $filters = array();
    public function escape($value) { return "'" . addslashes($value) . "'"; }
    public function __call($method, $args) { return $this; }
    public function from($table) { $this->from = $table; return $this; }
    public function where($key, $value = null) { $this->filters[$key] = $value; return $this; }
    public function where_in($key, $values) { $this->filters[$key] = $values; return $this; }
    public function get($table = '') {
        if (!$table) { $table = $this->from; }
        $filters = $this->filters; $this->filters = array(); $this->from = '';
        if (strpos($table, 'tr_kasbon') !== false) {
            if ($this->fail) { return false; }
            $documents = isset($filters['no_doc']) ? (array)$filters['no_doc'] : array();
            $allowed = array_filter($documents, function($doc) use ($filters) { return !isset($filters['created_by']) || $doc === 'KS-OWN'; });
            return new SelectionResult(array_map(function($doc) { return array('no_doc'=>$doc); }, array_values($allowed)));
        }
        if (strpos($table, 'tr_pengajuan_rutin_detail') !== false) {
            if ($this->fail) { return false; }
            $ids = isset($filters['a.id']) ? (array)$filters['a.id'] : array();
            $allowed = array_filter($ids, function($id) use ($filters) { return !isset($filters['b.created_by']) || $id === '11'; });
            return new SelectionResult(array_map(function($id) { return array('id'=>$id); }, array_values($allowed)));
        }
        return new SelectionResult();
    }
    public function get_where($table, $where) {
        if ($this->fail) { return false; }
        $doc = $where['no_doc'];
        if ($doc === 'MISSING') { return new SelectionResult(); }
        $creator = $doc === 'KS-OWN' ? $this->username : 'someone_else';
        if (isset($where['created_by']) && $where['created_by'] !== $creator) { return new SelectionResult(); }
        return new SelectionResult(array(array('id'=>1,'no_doc'=>$doc,'created_by'=>$creator,'coa'=>'6001','jumlah_kasbon'=>100,'tgl_doc'=>'2026-10-28')));
    }
    public function query($sql) {
        if (strpos($sql, 'UNION ALL') !== false) {
            $this->sql = $sql;
            if ($this->fail) { return false; }
            if ($this->empty) { return new SelectionResult(); }
            $rows = array(array('no_doc'=>'KS-OWN','coa'=>'6001'), array('no_doc'=>'RUTIN-OWN','coa'=>'6001'));
            if (strpos($sql, 'a.created_by = ' . $this->escape($this->username)) === false) { $rows[] = array('no_doc'=>'KS-OTHER','coa'=>'6001'); }
            if (strpos($sql, 'b.created_by = ' . $this->escape('206')) === false) { $rows[] = array('no_doc'=>'RUTIN-OTHER','coa'=>'6001'); }
            return new SelectionResult($rows);
        }
        return new SelectionResult(array(array('nama'=>'Expense fixture')));
    }
    public function error($unused = null) { return array('code'=>$this->fail?999:0,'message'=>$this->fail?'PRIVATE SQL FAILURE':''); }
    public function insert($table, $data) { $this->writes++; return true; }
    public function update($table, $data, $where) { $this->writes++; return true; }
    public function delete($table, $where) { $this->writes++; return true; }
    public function trans_begin() { $this->begins++; }
    public function trans_status() { return true; }
    public function trans_commit() { return true; }
    public function insert_id() { return 10; }
}
class SelectionInput {
    public $values = array();
    public function post($key = null) { return $key === null ? $this->values : (isset($this->values[$key]) ? $this->values[$key] : null); }
}
class SelectionAudit extends Module_audit_recorder {
    public $events = array();
}
class SelectionTemplate {
    public $rendered = '';
    public function __call($name, $args) { if ($name === 'render') { $this->rendered = $args[0]; } return $this; }
}
class SelectionOutput { public $status = 200; public function set_status_header($status) { $this->status=$status; return $this; } }
class SelectionModel {
    public function GetAutoGenerate($type) { return 'EXP-TEST'; }
    public function __call($name, $args) { return $name === 'GetDataWithJenis' ? (object)array('coa'=>'') : array(); }
}
$checks = 0;
function selectionCheck($ok, $message) { global $checks; $checks++; if (!$ok) { throw new RuntimeException($message); } }
function selectionController($username = 'alief', $admin = false) {
    $c = (new ReflectionClass('Expense'))->newInstanceWithoutConstructor();
    $c->auth = new SelectionAuth(); $c->auth->username=$username; $c->auth->admin=$admin;
    $c->db = new SelectionDb(); $c->db->username=$username;
    $c->input = new SelectionInput(); $c->template = new SelectionTemplate(); $c->output = new SelectionOutput();
    $GLOBALS['selectionCi']=$c;
    $c->module_audit_recorder = new SelectionAudit(); $c->All_model = new SelectionModel(); $c->Coa_expense_model = new SelectionModel();
    return $c;
}
function selectionCall($controller, $method, $args = array()) {
    $_FILES=array(); ob_start();
    try { call_user_func_array(array($controller,$method), $args); return ob_get_contents(); } finally { ob_end_clean(); }
}
foreach (array(array('alief',false,false),array('finance-team',false,false),array('finance',false,true),array('admin',true,true)) as $actor) {
    list($name,$admin,$all)=$actor;
    foreach (array(array(),array("someone' OR 1=1 --",'DEPT')) as $filter) {
        $c=selectionController($name,$admin);
        $reply=json_decode(selectionCall($c,'get_kasbon',$filter),true);
        selectionCheck(is_array($reply) && count($reply)===($all?4:2), 'Kasbon selector must scope both sources to creator for '.$name);
        selectionCheck(count($c->module_audit_recorder->events)===1 && $c->module_audit_recorder->events[0][0]===1, 'Exactly one successful read audit');
        selectionCheck(strpos($c->db->sql,"a.status IN ('2', '3')")!==false && strpos($c->db->sql,'ab2.status')!==false, 'Status and duplicate-report exclusions retained');
    }
    foreach (array('KS-OWN','KS-OTHER','MISSING') as $document) {
        $c=selectionController($name,$admin);
        selectionCall($c,'create_report',array($document));
        $allowed=$document!=='MISSING' && ($all||$document==='KS-OWN');
        selectionCheck(($c->template->rendered==='form_report')===$allowed, 'Direct report URL enforces ownership');
        selectionCheck($c->output->status===($allowed?200:404), 'Hidden/missing kasbon response');
        selectionCheck(count($c->module_audit_recorder->events)===1, 'Single report-open audit');
    }
}
$c=selectionController(); $c->db->empty=true;
selectionCheck(json_decode(selectionCall($c,'get_kasbon'),true)===array(), 'No owned kasbon is a valid empty JSON list');
selectionCheck($c->module_audit_recorder->events[0][0]===1, 'Empty list is a successful read');
$c=selectionController(); $c->db->fail=true;
$reply=selectionCall($c,'get_kasbon');
selectionCheck($c->output->status===500 && strpos($reply,'PRIVATE')===false, 'Query failure is controlled and does not expose SQL');
$c=selectionController(); $c->db->fail=true; selectionCall($c,'create_report',array('KS-OWN'));
selectionCheck($c->output->status===500 && $c->template->rendered==='', 'Failed kasbon lookup cannot render report');
foreach (array(array('no_doc_kasbon'=>'KS-OTHER'),array('id_kasbon'=>array('KS-OTHER')),array('id_expense_detail'=>array('22'))) as $references) {
    $c=selectionController();
    $c->input->values=array_merge(array('informasi'=>'Test','total_expense'=>100,'total_kasbon'=>100,'id'=>''),$references);
    $reply=json_decode(selectionCall($c,'save'),true);
    selectionCheck(isset($reply['save']) && $reply['save']===false && $c->db->writes===0 && $c->db->begins===0, 'Forged report references rejected before write/transaction');
    selectionCheck($c->module_audit_recorder->events[0][1]['data']['outcome']==='forbidden', 'Ownership denial audited');
}
$c=selectionController();
$c->input->values=array('id'=>'','informasi'=>'Own kasbon with PR metadata','tgl_doc'=>'2026-10-28','total_expense'=>100,
    'total_kasbon'=>100,'no_doc_kasbon'=>'KS-OWN','id_expense_detail'=>array('PR-DTL-999'));
$reply=json_decode(selectionCall($c,'save'),true);
selectionCheck(!empty($reply['save']), 'PR detail metadata on an owned kasbon is not a routine source ID');
$guard = new ReflectionMethod('Expense','ownsReportKasbonReferences'); $guard->setAccessible(true);
foreach (array(array(array('KS-OWN'),array('11'),true),array(array('KS-OWN','KS-OTHER'),array(),false),array(array(),array('11','22'),false),array(array(array('nested')),array(),false),array(array(),array(),true)) as $case) {
    $c=selectionController();
    selectionCheck($guard->invoke($c,$case[0],$case[1])===$case[2], 'Batch reference validation handles own/foreign/invalid/empty');
}
$c=selectionController('finance');
selectionCheck($guard->invoke($c,array('KS-OTHER'),array('22'))===true, 'Finance can use cross-user references');
$c=selectionController(); $c->db->fail=true;
selectionCheck($guard->invoke($c,array('KS-OWN'),array())===false, 'Reference query errors fail closed');
foreach (array('failure','exception') as $writer) {
    $GLOBALS['selectionWriter']=$writer;
    $c=selectionController(); $c->db->empty=true;
    selectionCheck(json_decode(selectionCall($c,'get_kasbon'),true)===array(), 'Audit writer failure preserves empty selector response');
    selectionCheck(count($c->module_audit_recorder->events)===1, 'Failed audit writer does not duplicate empty-read event');
    $c=selectionController(); $c->input->values=array('no_doc_kasbon'=>'KS-OTHER');
    $reply=json_decode(selectionCall($c,'save'),true);
    selectionCheck($reply['save']===false && $c->db->writes===0, 'Audit writer failure cannot bypass ownership denial');
    selectionCheck(count($c->module_audit_recorder->events)===1, 'Failed writer does not duplicate denied event');
}
$GLOBALS['selectionWriter']='';
echo 'PASS Kasbon selection ownership: '.$checks.' checks'.PHP_EOL;
