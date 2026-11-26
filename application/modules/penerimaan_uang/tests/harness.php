<?php
/** Runs only against a newly created disposable database on loopback. */
mysqli_report(MYSQLI_REPORT_OFF);
define('BASEPATH', __DIR__);
class BF_Model {}
class Admin_Controller {}
function has_permission($p) { return true; }
function int_to_roman($m) { return array(1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII')[(int)$m]; }
function log_message($level, $message) {}
require dirname(__DIR__) . '/models/Penerimaan_uang_model.php';
require dirname(__DIR__) . '/controllers/Penerimaan_uang.php';
require dirname(__DIR__, 2) . '/invoicing/controllers/Invoicing.php';
function expect($ok, $message) { if (!$ok) throw new RuntimeException($message); }
class TestResult {
    public $rows;
    function __construct($rows = array()) { $this->rows = $rows; }
    function result_array() { return $this->rows; }
    function row_array() { return $this->rows ? $this->rows[0] : array(); }
    function row() { return $this->rows ? (object)$this->rows[0] : null; }
    function num_rows() { return count($this->rows); }
}
class TestDb {
    public $connection, $db_debug = false, $healthy = true, $failInsertTable, $mutations = 0;
    private $filters = array(), $order = array();
    function __construct($connection) { $this->connection = $connection; }
    function query($sql, $params = array()) {
        if (!$params) {
            $result = $this->connection->query($sql);
            if ($result === false) { $this->healthy = false; throw new RuntimeException($this->connection->error); }
            if (preg_match('/^\s*(INSERT|UPDATE|DELETE|ALTER)/i', $sql)) $this->mutations++;
            return new TestResult($result instanceof mysqli_result ? $result->fetch_all(MYSQLI_ASSOC) : array());
        }
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) { $this->healthy = false; throw new RuntimeException($this->connection->error); }
        if ($params) {
            $values = array_map('strval', $params); $refs = array();
            foreach ($values as &$value) $refs[] = &$value;
            $stmt->bind_param(str_repeat('s', count($values)), ...$refs);
        }
        if (!$stmt->execute()) { $this->healthy = false; $error = $stmt->error; $stmt->close(); throw new RuntimeException($error); }
        $result = $stmt->get_result(); $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : array();
        if (preg_match('/^\s*(INSERT|UPDATE|DELETE|ALTER)/i', $sql)) $this->mutations++;
        $stmt->close(); return new TestResult($rows);
    }
    function field_exists($column, $table) { return $this->query('SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?', array($table, $column))->num_rows() > 0; }
    function get_where($table, $conditions) {
        $sql = 'SELECT * FROM ' . $table . ' WHERE ' . implode(' AND ', array_map(function($k){ return $k . ' = ?'; }, array_keys($conditions)));
        if ($this->order) $sql .= ' ORDER BY ' . implode(', ', $this->order);
        $this->order = array(); return $this->query($sql, array_values($conditions));
    }
    function order_by($field, $direction='ASC') { $this->order[] = $field . ' ' . $direction; return $this; }
    function where_in($field, $values) { $this->filters = array($field, $values); return $this; }
    function get($table) { $f = $this->filters; $this->filters = array(); return $this->query('SELECT * FROM ' . $table . ' WHERE ' . $f[0] . ' IN (' . implode(',', array_fill(0,count($f[1]),'?')) . ')', $f[1]); }
    function insert($table, $values) {
        if ($this->failInsertTable === $table) { $this->healthy = false; return false; }
        return $this->query('INSERT INTO ' . $table . ' (' . implode(',',array_keys($values)) . ') VALUES (' . implode(',',array_fill(0,count($values),'?')) . ')', array_values($values));
    }
    function update($table, $values, $conditions) { return $this->query('UPDATE ' . $table . ' SET ' . implode(',',array_map(function($k){return $k . '=?';},array_keys($values))) . ' WHERE ' . implode(' AND ',array_map(function($k){return $k . '=?';},array_keys($conditions))), array_merge(array_values($values),array_values($conditions))); }
    function delete($table, $conditions) { return $this->query('DELETE FROM ' . $table . ' WHERE ' . implode(' AND ',array_map(function($k){return $k . '=?';},array_keys($conditions))),array_values($conditions)); }
    function insert_batch($table, $rows) { foreach ($rows as $row) $this->insert($table,$row); return true; }
    function trans_begin() { $this->healthy = true; return $this->connection->begin_transaction(); }
    function trans_status() { return $this->healthy; }
    function trans_commit() { return $this->connection->commit(); }
    function trans_rollback() { return $this->connection->rollback(); }
}
class TestInput { public $values=array(); function post($key=null) { return $key===null ? $this->values : ($this->values[$key] ?? null); } function get($key) { return $this->values[$key] ?? null; } }
class TestSession { public $values=array(); function userdata($key) { return $this->values[$key] ?? null; } function set_userdata($key,$value) { $this->values[$key]=$value; } }
class TestAuth { function restrict($p) {} function user_id() { return 'tester'; } }
class TestOutput { public $body; function set_content_type($type) { return $this; } function set_output($body) { $this->body=$body; return $this; } }
class TestTemplate { public $data=array(); function set($data) { $this->data=$data; } function render($name) {} }
class VucaTestAccounting {
    function select($v) { return $this; } function from($v) { return $this; } function where_in($f,$v) { return $this; }
    function get() { return new TestResult(array_map(function($c){return array('no_perkiraan'=>$c,'nm_coa'=>$c);},array('1102-01-01','1106-01-05','2104-01-07','4101-01-01'))); }
}
class VucaTestModel { function generate_id_invoice_jurnal($n) { return 'vuca-test-'.$n; } }
function updateVuca($db,$id) {
    $c=(new ReflectionClass('Invoicing'))->newInstanceWithoutConstructor();
    $c->db=$db; $c->input=new TestInput; $c->auth=new TestAuth; $c->Invoicing_model=new VucaTestModel;
    $p=new ReflectionProperty('Invoicing','accounting'); $p->setAccessible(true); $p->setValue($c,new VucaTestAccounting);
    // Gross omitted, incorrect hidden net and journal values: server must compute them.
    $c->input->values=array('id_invoicing'=>$id,'tanggal_invoice'=>'2026-09-01','nomor_invoice'=>$id.'/VSB/2026','no_po'=>'','nomor_faktur'=>'','total_akhir_jurnal'=>'999','invoice_fingerprint'=>Invoice_calculation::fingerprint($db->query('SELECT * FROM tr_invoicing WHERE id = ?',array($id))->row_array()));
    foreach(range(1,4) as $n) foreach(array('tgl_jurnal_'=>'2026-09-01','coa_jurnal_'=>'tampered','id_company_'=>'1','nm_company_'=>'Vuca','nm_coa_'=>'tampered','debit_'=>'999','kredit_'=>'999') as $key=>$v) $c->input->values[$key.$n]=$v;
    ob_start(); $c->update_invoice_vuca(); return json_decode(ob_get_clean(),true);
}
function setupController($db, $session=null) {
    $model=(new ReflectionClass('Penerimaan_uang_model'))->newInstanceWithoutConstructor(); $model->db=$db;
    $controller=(new ReflectionClass('Penerimaan_uang'))->newInstanceWithoutConstructor();
    $controller->db=$db; $controller->Penerimaan_uang_model=$model; $controller->session=$session ?: new TestSession;
    $controller->input=new TestInput; $controller->auth=new TestAuth; $controller->output=new TestOutput; $controller->template=new TestTemplate;
    $property=new ReflectionProperty('Penerimaan_uang','accounting'); $property->setAccessible(true); $property->setValue($controller,$db);
    return $controller;
}
function preview($controller,$ids,$allocation,$choice='N') {
    $controller->input->values=array('choose_inv'=>$ids,'id_alokasi'=>$allocation,'pph23_dipotong'=>$choice,'ppn_dipotong'=>'Y');
    ob_start(); $controller->process_alokasi(); $error=ob_get_clean(); expect(!$error,'Preview error: ' . $error);
    $data=$controller->template->data;
    return array('preview_token'=>$data['preview_token'],'no_inv'=>count($ids),'id_alokasi'=>(string)$allocation,'pph23_dipotong'=>$choice,'ppn_dipotong'=>'Y');
}
function save($controller,$post,$ids,$cash,$admin=array()) {
    foreach($ids as $n=>$id) { $post['id_inv_'.($n+1)]=$id; $post['penerimaan_'.($n+1)]=$cash[$n]; $post['biaya_admin_'.($n+1)]=$admin[$n] ?? '0'; }
    // These deliberately wrong form values must never determine the ledger.
    $post['sisa_piutang_1']='999.999,00'; $post['debit_bank_debit']='999999'; $post['kredit_1106-01-02_1']='999999';
    $controller->input->values=$post; $controller->save_penerimaan_piutang(); return json_decode($controller->output->body,true);
}
function fixture($db,$id,$net,$pph,$type=0,$invoiceNumber=null) {
    $db->insert('tr_invoicing',array('id'=>$id,'no_invoice'=>$invoiceNumber ?: $id.'/STM/2026','id_customer'=>'C1','nm_customer'=>'Customer Test','non_kons'=>1,'tipe_invoice'=>$type,'saldo_piutang'=>$net,'pph_jurnal'=>$pph,'total_akhir_jurnal'=>$net,'tagihan_ppn_jurnal'=>Penerimaan_calculation::decimal(Penerimaan_calculation::cents($net)+Penerimaan_calculation::cents($pph)),'total_nominal_jurnal'=>$net,'dpp_lain_lain_jurnal'=>$net,'ppn_jurnal'=>0,'tanggal_invoice'=>'2026-08-20','created_date'=>'2026-08-20 10:00:00'));
}
function allocation($db,$id,$nominal) {
    $db->insert('tr_alokasi_detail',array('id'=>$id,'nominal_kredit'=>$nominal,'nominal_debit'=>0,'nilai_terpakai'=>0,'tipe_bank'=>1,'tanggal_transaksi'=>'2026-09-10','sts'=>'8'));
    $db->insert('tr_alokasi_split',array('id'=>$id,'id_alokasi_detail'=>$id,'nominal'=>$nominal,'nilai_terpakai'=>0,'jenis_alokasi'=>1));
}
function value($db,$sql) { $row=$db->query($sql)->row_array(); return (string) reset($row); }
