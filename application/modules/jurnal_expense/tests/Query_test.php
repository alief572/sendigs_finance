<?php
define('BASEPATH', dirname(__DIR__, 4) . '/system/');
define('APPPATH', dirname(__DIR__, 3) . '/');
set_error_handler(function ($severity, $message) { throw new ErrorException($message, 0, $severity); });
function log_message($level, $message) {}
function show_error($message) { throw new RuntimeException($message); }
function load_class($name, $directory) { return null; }
class BF_Model {}
class ExpenseQueryInput { public $values = []; public function post() { return $this->values; } }
function has_permission($key) { return true; }
$checks = 0;
function check($ok, $message) { global $checks; $checks++; if (!$ok) { throw new RuntimeException($message); } }
require BASEPATH . 'database/DB.php';
require APPPATH . 'modules/jurnal_expense/models/Jurnal_expense_model.php';
$db = DB(['dbdriver' => 'pdo', 'subdriver' => 'sqlite', 'dsn' => 'sqlite::memory:', 'db_debug' => false], true);
$db->query('CREATE TABLE tr_jurnal (id INTEGER PRIMARY KEY, no_transaksi TEXT, jenis_transaksi TEXT, sts TEXT, id_company TEXT, nm_company TEXT, tgl_jurnal TEXT)');
foreach ([[1,'ER-1','Expense Report Consultant',null,'1','STM-Vuca'],[2,'ER-1','Expense Report Consultant','0','1','STM-Vuca'],[3,'ER-2','Expense Report','','7','STM'],[4,'ER-3','Expense Report','1','7','STM'],[5,'BK-1','Payment','0','7','STM'],[6,'ER-1','Expense Report Consultant','0','4','Vuca'],[7,'ER-4','Expense Report','0','6','STM-Sustain']] as $r) {
    $db->insert('tr_jurnal', array_combine(['id','no_transaksi','jenis_transaksi','sts','id_company','nm_company'], $r) + ['tgl_jurnal'=>'2026-10-14']);
}
$model = (new ReflectionClass('Jurnal_expense_model'))->newInstanceWithoutConstructor();
$model->db = $db; $model->input = new ExpenseQueryInput();
$rows = $model->get_list_jurnal();
check(count($rows) === 4, 'only pending Expense sources; grouped per transaction/type/company');
check(array_column($model->get_no_payment_jurnal(), 'no_transaksi') === ['ER-1','ER-2','ER-4'], 'dropdown uses identical pending scope');
check(count($model->get_list_jurnal(['company'=>'4'])) === 2, 'Vuca aliases match list and export');
check(count($model->get_list_jurnal(['company'=>'3'])) === 1, 'Sustain aliases');
check(count($model->get_list_jurnal(['company'=>'7'])) === 1, 'STM aliases');
check(count($model->get_list_jurnal(['tgl_from'=>'2026-10-15','tgl_to'=>'2026-10-16'])) === 0, 'date filter');
check(count($model->get_list_jurnal(['no_transaksi'=>"' OR 1=1 --"])) === 0, 'bound transaction filter');
$model->input->values=['draw'=>4,'start'=>0,'length'=>1,'search'=>['value'=>'ER-1']];
$page = $model->get_data_jurnal();
check($page['draw']===4 && $page['recordsTotal']===4 && $page['recordsFiltered']===2 && count($page['data'])===1, 'search/count/pagination contract');
$model->input->values['start']=1;
check(count($model->get_data_jurnal()['data'])===1, 'second page');
$model->input->values['search']['value']="' OR 1=1 --";
check($model->get_data_jurnal()['recordsFiltered']===0, 'search injection');
$model->input->values=['draw'=>[], 'start'=>[], 'length'=>[], 'search'=>['value'=>[]], 'tgl_jurnal'=>[], 'order'=>[['column'=>[], 'dir'=>[]]]];
check($model->get_data_jurnal()['recordsFiltered']===4, 'malformed input safe');
check(count($model->get_pending_group(1))===2, 'preview includes NULL and 0, same company only');
foreach ([0,3,4,5,999] as $id) {
    if ($id===3) { check(count($model->get_pending_group($id))===1, 'empty status preview'); continue; }
    try { $model->get_pending_group($id); throw new LogicException('must reject id '.$id); }
    catch (InvalidArgumentException $e) { check(true, 'invalid/posted/other type ID rejected'); }
}
$db->insert('tr_jurnal', ['id'=>8,'no_transaksi'=>'ER-1','jenis_transaksi'=>'Expense Report Consultant','sts'=>'1','id_company'=>'1','nm_company'=>'STM-Vuca','tgl_jurnal'=>'2026-10-14']);
try { $model->get_pending_group(1); throw new LogicException('mixed group must reject'); }
catch (InvalidArgumentException $e) { check(true, 'partly posted group cannot repost'); }
$db->delete('tr_jurnal', ['id'=>8]);
$db->update('tr_jurnal', ['tgl_jurnal'=>'2026-10-15'], ['id'=>2]);
try { $model->get_pending_group(1); throw new LogicException('date mismatch must reject'); }
catch (InvalidArgumentException $e) { check(true, 'inconsistent dates rejected'); }
check($model->filters(['tgl_jurnal'=>'2026-10-14'])['tgl_to']==='2026-10-14', 'single day date filter');
foreach (['2026-02-30','2026-10-15 to 2026-10-14',"' OR 1=1"] as $date) {
    try { $model->filters(['tgl_jurnal'=>$date]); throw new LogicException('date must reject'); }
    catch (InvalidArgumentException $e) { check(true, 'invalid date input'); }
}
foreach ([101,102] as $id) $db->insert('tr_jurnal',['id'=>$id,'no_transaksi'=>'ER-RETURN','jenis_transaksi'=>'Pengembalian Kasbon','sts'=>$id===101?null:'0','id_company'=>'7','nm_company'=>'STM','tgl_jurnal'=>'2026-10-14']);
check(count($model->get_list_jurnal(['no_transaksi'=>'ER-RETURN']))===1,'return appears in native expense list');
check(count($model->get_pending_group(101))===2,'return accepted by posting group validation');
$db->where('id',102)->update('tr_jurnal',['sts'=>'1']);
try { $model->get_pending_group(101); throw new LogicException('partly posted return must reject'); }
catch (InvalidArgumentException $e) { check(true,'return preserves mixed status guard'); }
$db->query('DROP TABLE tr_jurnal');
try { $model->get_list_jurnal(); throw new LogicException('failure must propagate'); }
catch (RuntimeException $e) { check(true, 'failed query throws instead of false empty success'); }
echo "PASS Jurnal Expense real SQLite queries: {$checks} assertions\n";
