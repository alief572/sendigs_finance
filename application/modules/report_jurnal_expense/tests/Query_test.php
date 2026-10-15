<?php
define('BASEPATH', dirname(__DIR__, 4) . '/system/');
define('APPPATH', dirname(__DIR__, 3) . '/');
set_error_handler(function ($severity, $message) { throw new ErrorException($message, 0, $severity); });
function log_message($level, $message) {}
function show_error($message) { throw new RuntimeException($message); }
function load_class($name, $directory) { return null; }
class BF_Model {}
class ReportInput { public $values = []; public function post() { return $this->values; } }
$checks = 0;
function check($ok, $message) { global $checks; $checks++; if (!$ok) { throw new RuntimeException($message); } }
require BASEPATH . 'database/DB.php';
require APPPATH . 'modules/report_jurnal_expense/models/Report_jurnal_expense_model.php';
$db = DB(['dbdriver'=>'pdo', 'subdriver'=>'sqlite', 'dsn'=>'sqlite::memory:', 'db_debug'=>false], true);
$db->query('CREATE TABLE tr_jurnal (id INTEGER PRIMARY KEY, no_transaksi TEXT, jenis_transaksi TEXT, sts TEXT, id_company TEXT, nm_company TEXT, tgl_jurnal TEXT, debit NUMERIC, kredit NUMERIC, coa TEXT, nm_coa TEXT, keterangan TEXT)');
foreach ([[1,'ER-1','Expense Report Consultant','1','1','STM-Vuca',0,100.25], [2,'ER-1','Expense Report Consultant','1','1','STM-Vuca',100.25,0], [3,'ER-2','Expense Report','1','7','STM',100,0], [4,'ER-3','Expense Report','0','7','STM',100,0], [5,'ER-4','Expense Report',null,'7','STM',100,0], [6,'ER-5','Expense Report','','7','STM',100,0], [7,'BK-1','Payment','1','7','STM',100,0], [8,'ER-1','Expense Report Consultant','1','4','Vuca',500,0], [9,'ER-6','Expense Report','1','6','STM-Sustain',100,0], [10,'ER-1','Expense Report Consultant','0','1','STM-Vuca',999,0], [11,'ER-1','Expense Report Consultant','1','1','STM-Vuca',0,0]] as $r) {
    $db->insert('tr_jurnal', array_combine(['id','no_transaksi','jenis_transaksi','sts','id_company','nm_company','debit','kredit'], $r)+['tgl_jurnal'=>'2026-10-14','coa'=>'5101','nm_coa'=>'Biaya','keterangan'=>'Expense {REF:REF-1}']);
}
$m=(new ReflectionClass('Report_jurnal_expense_model'))->newInstanceWithoutConstructor();$m->db=$db;$m->input=new ReportInput();
check(count($m->get_list_jurnal())===4,'only two posted Expense types grouped by company');
check(array_column($m->get_no_payment_jurnal(),'no_transaksi')===['ER-1','ER-2','ER-6'],'dropdown matches posted scope');
check(count($m->get_list_jurnal(['company'=>'4']))===2,'Vuca aliases');
check(count($m->get_list_jurnal(['company'=>'3']))===1,'Sustain aliases');
check(count($m->get_list_jurnal(['company'=>'7']))===1,'STM aliases');
check(count($m->get_list_jurnal(['tgl_from'=>'2026-10-15','tgl_to'=>'2026-10-16']))===0,'date filter');
check(!$m->get_list_jurnal(['no_transaksi'=>"' OR 1=1 --"]),'bound transaction filter');
$detail=$m->get_detail_jurnal(1);
check(count($detail)===2&&array_map('intval',array_column($detail,'id'))===[1,2],'detail excludes other company, pending and zero rows');
check((float)$detail[1]->debit===100.25,'detail cents');
check(count($m->get_detail_jurnal(8))===1&&$m->get_detail_jurnal(8)[0]->id_company==='4','different company isolated');
foreach([0,4,5,6,7,999] as $id){try{$m->get_detail_jurnal($id);throw new LogicException('ID should reject');}catch(InvalidArgumentException $e){check(true,'invalid/type/status id rejects');}}
$m->input->values=['draw'=>5,'start'=>0,'length'=>1,'search'=>['value'=>'ER-1']];$page=$m->get_data_jurnal();
check($page['draw']===5&&$page['recordsTotal']===4&&$page['recordsFiltered']===2&&count($page['data'])===1,'search and pagination contract');
check(strpos($page['data'][0]['action'],'data-id=')!==false&&strpos($page['data'][0]['action'],'Post')===false,'View uses representative ID');
$m->input->values['start']=1;check(count($m->get_data_jurnal()['data'])===1,'second page');
$m->input->values['search']['value']="' OR 1=1 --";check($m->get_data_jurnal()['recordsFiltered']===0,'search injection');
$m->input->values=['draw'=>[],'start'=>[],'length'=>[],'search'=>['value'=>[]],'tgl_jurnal'=>[],'order'=>[['column'=>[],'dir'=>[]]]];check($m->get_data_jurnal()['recordsFiltered']===4,'malformed input');
check($m->filters(['tgl_jurnal'=>'2026-10-14'])['tgl_to']==='2026-10-14','single date');
foreach(['2026-02-30','2026-10-15 to 2026-10-14'] as $date){try{$m->filters(['tgl_jurnal'=>$date]);throw new LogicException('date should reject');}catch(InvalidArgumentException $e){check(true,'date validation');}}
$db->query('DROP TABLE tr_jurnal');foreach(['get_list_jurnal','get_no_payment_jurnal','get_data_jurnal'] as $method){try{$m->$method();throw new LogicException('query should fail');}catch(RuntimeException $e){check(true,'query failure '.$method);}}
echo "PASS Report Jurnal Expense queries: {$checks} assertions, real SQLite/CI query builder\n";
