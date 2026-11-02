<?php
if (!function_exists('has_permission')) { function has_permission($permission) { return true; } }
// Exercise the active controllers with in-memory fixtures; no DB or browser.
define('BASEPATH', __DIR__);
define('DBCNL', 'consultant_fixture');
set_error_handler(function ($severity, $message, $file, $line) { throw new ErrorException($message, 0, $severity, $file, $line); });
class Admin_Controller {}
class BF_Model {}
require dirname(__DIR__) . '/models/Penerimaan_uang_model.php';
require dirname(__DIR__) . '/controllers/Penerimaan_uang.php';
require dirname(__DIR__, 2) . '/invoicing/controllers/Invoicing.php';
function expectLegacy($ok, $message) { if (!$ok) throw new RuntimeException($message); }
class LegacyResult {
    private $rows;
    function __construct($rows) { $this->rows = $rows; }
    function row_array() { return $this->rows[0] ?? array(); }
    function row() { return (object)$this->row_array(); }
    function result_array() { return $this->rows; }
    function num_rows() { return count($this->rows); }
}
class LegacyDb {
    public $invoice, $inserted, $batches = array(), $history = array(), $journals = array(), $historyChoice = 'N';
    // Hook Lebih Bayar invoice lama; nonaktif secara default sehingga kasus lama tidak berubah.
    public $deposit = false, $companyId = '1', $inserts = array(), $deletes = array(), $voucher = null, $voucherMutations = array();
    private $table;
    public $receiptLock = false;
    function query($sql, $params=array()) {
        if (strpos($sql,'SELECT GET_LOCK(')===0 && count($params)===1 && in_array($params[0],array('sendigs_receipt_v2','sendigs_invoice_write'),true)) { $this->receiptLock=true; return new LegacyResult(array(array('acquired'=>1))); }
        if (strpos($sql,'SELECT RELEASE_LOCK(')===0 && count($params)===1 && in_array($params[0],array('sendigs_receipt_v2','sendigs_invoice_write'),true)) { $this->receiptLock=false; return new LegacyResult(array(array('released'=>1))); }
        if ($this->deposit && strpos($sql,'SELECT no_deposit FROM tr_customer_deposit')===0) return new LegacyResult(array());
        throw new RuntimeException('Unexpected fixture SQL: '.$sql);
    }
    function field_exists($field,$table){return $this->deposit && $field === 'consulting_snapshot';}
    function table_exists($table){return $this->deposit;}
    function insert_id(){return 77;}
    function select($s) { return $this; }
    function from($s) { $this->table = $s; return $this; }
    function where($a, $b) { return $this; }
    function set($a, $b = null, $c = null) { return $this; }
    function group_by($a) { return $this; }
    function order_by($a, $b) { return $this; }
    function where_in($a, $b) { return $this; }
    function get() { return $this->get_where($this->table, array()); }
    function get_where($table, $where) {
        if ($table === 'tr_penerimaan_piutang_detail') return new LegacyResult($this->history);
        if ($table === 'tr_penerimaan_piutang') return new LegacyResult(array(array('pph23_dipotong'=>$this->historyChoice,'id'=>'H1','no_surat'=>'NEW','id_alokasi'=>5102,'created_date'=>'2026-10-01 00:00:00','consulting_batch'=>0)));
        if ($table === 'tr_customer_deposit') return new LegacyResult($this->voucher ? array($this->voucher) : array());
        if ($table === 'tr_customer_deposit_mutasi') return new LegacyResult($this->voucherMutations);
        if ($table === 'tr_jurnal') return new LegacyResult(array_map(function ($row) { return $row + array('jenis_transaksi'=>'Penerimaan Piutang'); }, $this->journals));
        if ($table === 'coa_master') return new LegacyResult(array(array('nama'=>'Bank')));
        if (strpos($table, 'tr_invoicing') === 0) return new LegacyResult(array($this->invoice + array('ttl_kredit'=>$this->invoice['pph_jurnal'])));
        if ($table === 'coa_master a') return new LegacyResult(array(array('no_perkiraan'=>'1102-01-01','nm_coa'=>'Piutang'),array('no_perkiraan'=>'1106-01-02','nm_coa'=>'PPh 23 Disetor'),array('no_perkiraan'=>'7201-01-04','nm_coa'=>'Admin')));
        if ($table === 'kons_tr_company') return new LegacyResult(array(array('id'=>$this->companyId,'nm_company'=>'STM-Fixture')));
        return new LegacyResult(array(array('id'=>'1','id_spk_penawaran'=>'SPK','id_detail_plan_tagih'=>'PLAN','id_penawaran'=>'Q','id_customer'=>'C','nm_customer'=>'Customer','address'=>'','id_project'=>'P','id_project_leader'=>'L','nm_project_leader'=>'Leader','id_sales'=>'S','nm_sales'=>'Sales','company'=>'1','id_company'=>'1','nm_company'=>'Vuca','coa_bank'=>'BANK','nm_coa'=>'Bank')));
    }
    function trans_begin() {}
    function trans_commit() {}
    function insert($table, $values) { if ($table === 'tr_invoicing') $this->inserted = $values; $this->inserts[$table][] = $values; return true; }
    function delete($table, $where) { $this->deletes[$table][] = $where; return true; }
    function insert_batch($table, $values) { $this->batches[$table] = $values; return true; }
    function update_batch($table, $values, $key) { return true; }
    function trans_status() { return true; }
    function update($table, $values = null, $where = null) { return true; }
}
class LegacyInput { public $values; function post($key = null) { return $key === null ? $this->values : ($this->values[$key] ?? null); } }
class LegacyTemplate { public $data; function set($data) { $this->data = $data; } function render($name) {} }
require_once dirname(__DIR__, 3) . '/helpers/mockup_format_helper.php';
class LegacyModel {
    public $db;
    function legacy_gross_base($invoice) {
        $model = (new ReflectionClass('Penerimaan_uang_model'))->newInstanceWithoutConstructor();
        $model->db = $this->db;
        return $model->legacy_gross_base($invoice);
    }
    function remaining_receipt_pph($invoice, $choice) {
        $model = (new ReflectionClass('Penerimaan_uang_model'))->newInstanceWithoutConstructor();
        $model->db = $this->db;
        return $model->remaining_receipt_pph($invoice, $choice);
    }
    function deducted_pph_budget($invoice) {
        $model = (new ReflectionClass('Penerimaan_uang_model'))->newInstanceWithoutConstructor();
        $model->db = $this->db;
        return $model->deducted_pph_budget($invoice);
    }
    function resolve_alokasi($id) { return array('is_legacy'=>false,'split'=>array('nominal'=>15000000,'id_alokasi_detail'=>1),'detail'=>array('tipe_bank'=>'1','tanggal_transaksi'=>'2026-09-07')); }
    function generate_no_invoice($company, $type) { return 'NEW/VSB/2026'; }
    function generate_id() { return 'NEW'; }
    function generate_id_invoice_jurnal($number) { return 'JOURNAL-'.$number; }
}
class LegacyAuth { function user_id() { return 'fixture'; } function restrict($permission) {} }
class LegacySession { function userdata($key) { return 'fixture'; } }
function legacyController($class, $db) {
    $c = (new ReflectionClass($class))->newInstanceWithoutConstructor();
    $c->db = $db; $c->input = new LegacyInput; $c->template = new LegacyTemplate;
    foreach (array('consultant','accounting') as $name) {
        $p = new ReflectionProperty($class, $name); $p->setAccessible(true); $p->setValue($c, $db);
    }
    return $c;
}
