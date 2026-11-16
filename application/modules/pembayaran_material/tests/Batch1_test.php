<?php
define('AUDIT_FIXTURE_ONLY', true);
define('DBACC', 'fixture_accounting');
require dirname(__DIR__, 2) . '/request_payment/tests/Audit_test.php';
require APPPATH . 'helpers/audit_trail_helper.php';
require APPPATH . 'helpers/app_helper.php';
class BF_Model {}
class CI_Model {}
require dirname(__DIR__) . '/models/Pembayaran_material_model.php';
require dirname(__DIR__) . '/models/Jurnal_model.php';
require dirname(__DIR__) . '/controllers/Pembayaran_material.php';
class MaterialSession extends FixtureSession {
    public $userdata = array('ORI_User' => array('username' => 'tester', 'id_user' => 7));
}
class MaterialDb extends FixtureDb {
    public $db_debug = false;
    public $table = '', $queryNumber = 0, $writeNumber = 0, $queryError = 0, $writes = array();
    public function __call($name, $args) {
        global $scenario;
        $this->calls[] = $name;
        if ($name === 'escape_like_str') { return $args[0]; }
        if ($name === 'from') { $this->table = $args[0]; return $this; }
        if (in_array($name, array('get', 'get_where', 'query', 'count_all_results'), true)) {
            // Release happens after the business commit and cannot change its outcome.
            if ($name === 'query' && strpos($args[0], 'SELECT RELEASE_LOCK(') === 0) { return true; }
            $this->queryNumber++;
            if (($scenario['abort_query'] ?? 0) === $this->queryNumber) { exit; }
            $sql = $args[0] ?? $this->table;
            if ($name === 'query' && preg_match('/^\s*(UPDATE|DELETE|INSERT)/i', $sql)) {
                if (($scenario['fail_query'] ?? 0) === $this->queryNumber) { return false; }
                return $this->write($name, $args);
            }
            if ($name === 'query') { preg_match('/from\s+([\w.]+)/i', $sql, $match); $sql = $match[1] ?? ''; }
            $this->queryError = ($scenario['fail_query'] ?? 0) === $this->queryNumber ? 999 : 0;
            if ($name === 'query' && strpos($args[0], 'SELECT GET_LOCK(') === 0) {
                return $this->queryError ? false : new MaterialReferenceResult(true);
            }
            if ($name === 'query' && strpos($args[0], 'SELECT RELEASE_LOCK(') === 0) { return !$this->queryError; }
            if ($name === 'get_where' && $sql === 'tr_jurnal' && isset($args[1]['no_transaksi'])) {
                return $this->queryError ? false : new MaterialReferenceResult(false);
            }
            if ($name === 'count_all_results') { return !empty($scenario['all_petty']) || !empty($scenario['empty']) ? 0 : 1; }
            if ($this->queryError && !empty($scenario['query_returns_false'])) { return false; }
            return new MaterialResult($sql);
        }
        if (in_array($name, array('insert', 'insert_batch', 'update', 'delete'), true)) {
            if (($args[0] ?? '') === 'histories') { return true; }
            return $this->write($name, $args);
        }
        if ($name === 'error') { return array('code' => $this->queryError); }
        if ($name === 'trans_begin') { return empty($scenario['trans_begin_failure']); }
        if ($name === 'trans_status') { return empty($scenario['transaction_failure']); }
        if ($name === 'trans_commit') { if (!empty($scenario['abort_commit'])) { exit; } return empty($scenario['trans_commit_failure']); }
        if ($name === 'trans_rollback') { return true; }
        return $this;
    }
    private function write($name, $args) {
        global $scenario;
        $this->writeNumber++; $this->queryError = 0;
        if (($scenario['fail_write'] ?? 0) === $this->writeNumber) { return false; }
        $this->writes[] = array($name, $args); return true;
    }
    public function last_query() { return 'SELECT fixture'; }
}
class MaterialReferenceResult {
    private $locked;
    public function __construct($locked) { $this->locked=$locked; }
    public function row() { return $this->locked ? (object)['acquired'=>1] : null; }
    public function result_array() { return []; }
}
class MaterialResult {
    private $table;
    public function __construct($table) { $this->table = $table; }
    public function row() {
        global $scenario;
        if (($scenario['missing_table'] ?? '') === $this->table || !empty($scenario['empty'])) { return null; }
        $values = array(
            'id' => 'PAY-1', 'id_payment' => 'VOUCHER-1', 'no_doc' => 'DOC-1', 'no_po' => 'PO-1', 'total' => 1,
            'tgl_bayar' => '2026-10-13', 'link_doc' => '', 'payment_bank' => 100, 'jumlah' => 100,
            'total_ppn' => 0, 'total_pph' => 0, 'tipe_pph' => '',
            'keterangan_pembayaran' => 'PRIVATE', 'created_by' => 'PRIVATE', 'nm_supplier' => 'PRIVATE',
            'tipe' => $scenario['payment_type'] ?? 'TR-02', 'modul' => $scenario['payment_module'] ?? 'PO',
            'curs_header' => $scenario['currency'] ?? 'IDR', 'nilai_po_invoice' => 100, 'invoice_ppn' => 10,
            'nilai_pph_invoice' => 5, 'kurs_receive_invoice' => 1, 'potongan_dp' => 10, 'id_supplier' => 'SUP-1',
            'coa_pph' => '2101-PPH', 'keterangan' => 'PRIVATE', 'parameter_no' => '3', 'no_perkiraan' => '1101-01',
            'terima_barang_idr' => !empty($scenario['received_goods']) ? 100 : 0, 'kurs_terima' => 1, 'bank_charge' => 5,
            'nocab' => '101', 'subcab' => '01', 'nobuk' => 1
        );
        $values += $GLOBALS['materialRowDefaults'] ?? array();
        $tableName = preg_split('/\s+/', $this->table)[0];
        if ($tableName === 'tr_choosed_payment') { $values['id_payment'] = 'PAY-1'; }
        return (object) array_merge($values, $scenario['row_overrides'] ?? array(), $scenario['table_overrides'][$tableName] ?? array());
    }
    public function result() {
        global $scenario;
        if (!empty($scenario['empty_selection']) && $this->table === 'tr_choosed_payment') { return array(); }
        $row = $this->row(); if (!$row) { return array(); }
        if (!empty($scenario['journal_fallback']) && $this->table === 'tr_jurnal') { return array(); }
        if (strpos($this->table, 'master_oto_jurnal_detail') !== false) {
            $rows = array(); foreach (array(1, 4, 8, 2, 3, 5, 6, 7, 9) as $number) { $r = clone $row; $r->parameter_no = (string) $number; $rows[] = $r; } return $rows;
        }
        return array($row);
    }
    public function num_rows() { global $scenario; return !empty($scenario['no_admin_rows']) && strpos($this->table, 'payment_approve') !== false ? 0 : count($this->result()); }
    public function result_array() { return array_map(function ($row) { return array('maxP' => '') + (array) $row; }, $this->result()); }
}
class MaterialInput extends FixtureInput { public function ip_address() { return '127.0.0.1'; } }
class MaterialUri { public function segment($number) { return 'pembayaran_material'; } }
class MaterialMaster { public function getArray() { return array(); } }
class MaterialAuth extends FixtureAuth { public function user_id() { return 7; } }
class MaterialJurnal extends Jurnal_model { public function __construct($db, $load) { $this->db = $db; $this->load = $load; } }
class MaterialPcvs { public function update_status_done($document, $actor) { global $scenario; return empty($scenario['pcvs_failure']); } }
function show_error($message, $status = 500) { throw new RuntimeException('HTTP ' . $status); }
function base_url($path = '') { return '/fixture/' . $path; }
class MaterialModel extends Pembayaran_material_model {
    public function __construct($db, $input) { $this->db = $this->consultant = $this->accounting = $this->hris = $db; $this->input = $input; }
}
function material_run($method, $options = array(), $post = array(), $args = array()) {
    global $ci, $scenario, $events, $scenarioCount;
    $scenarioCount++; $scenario = $options; $events = array();
    $ci = new Pembayaran_material(); $ci->db = $options['db_fixture'] ?? new MaterialDb(); $ci->session = new MaterialSession();
    $ci->uri = new MaterialUri(); $ci->input = new MaterialInput();
    $ci->auth = new MaterialAuth(); $ci->Jurnal_model = new MaterialJurnal($ci->db, $ci->load);
    $ci->pcvs_model = new MaterialPcvs();
    $ci->master_model = new MaterialMaster();
    $ci->pembayaran_material_model = $ci->Pembayaran_material_model = new MaterialModel($ci->db, $ci->input);
    $ci->Pembayaran_material_model->auth = $ci->auth;
    $journal = array('nama_account'=>'Kas','coa'=>'1101-01','tanggal_jurnal'=>'2026-10-13','company'=>'STM',
        'debit'=>100,'kredit'=>0,'keterangan'=>'PRIVATE','id_company'=>7,'nm_company'=>'STM','nm_coa'=>'Kas','id_divisi'=>5,'nm_divisi'=>'Finance');
    $ci->input->values = array_merge(array('jurnal_ls'=>array(array_merge($journal,array('coa'=>'6207-00-00')),array_merge($journal,array('debit'=>0,'kredit'=>105)),array_merge($journal,array('coa'=>'7201-01-04','debit'=>5))), 'id_req' => array('REQ-1'), 'payment_date' => '2026-10-13', 'bank_coa' => '1101-01',
        'bank_nilai' => 100, 'curs' => 1, 'id_supplier' => 'SUP-1', 'curs_header' => $options['currency'] ?? 'IDR',
        'biaya_admin_forex' => 0, 'biaya_admin' => 5, 'curs_admin' => 1, 'biaya_admin_forex2' => 0, 'biaya_admin2' => 0,
        'curs_admin2' => 1, 'bank_coa_admin' => '1101-02', 'nilai_bayar_bank' => 100, 'admin_charge_bearer' => 'company',
        'payfor' => array('ITEM-1'), 'no_request' => 'RP-1', 'tipetrans' => 1, 'search' => array('value' => 'PRIVATE'), 'password' => 'PRIVATE',
        'start' => 0, 'length' => 25, 'draw' => 1, 'jenis_payment' => 1, 'id' => 'PAY-1', 'checked' => 1,
        'bank' => '1101-01', 'tgl_bayar' => '2026-10-13', 'keterangan_pembayaran' => 'PRIVATE', 'payment_bank' => '105',
        'total_payment' => '100', 'id_payment' => 'PAY-1', 'bank_charge' => '5', 'supplier_input' => 'SUP-1', 'nm_supplier_input' => 'PRIVATE'), $post);
    $_FILES = $options['files'] ?? array();
    $_GET = array_merge(array('id_payment' => 'PAY-1', 'jenis_payment' => 'pr', 'admin_charge_bearer' => 'company'), $options['get'] ?? array());
    ob_start(); try { call_user_func_array(array($ci, $method), $args); }
    catch (Throwable $error) {
        if (isset($ci->module_audit_recorder)) { $ci->module_audit_recorder->finish_interrupted(); }
        if (empty($options['expected_exception'])) { ob_end_clean(); throw $error; }
    }
    $output = ob_get_clean();
    if (isset($ci->module_audit_recorder)) { $ci->module_audit_recorder->finish_interrupted(); }
    check(count($events) === 1, $method . ': expected one audit');
    check($events[0]['context']['module'] === 'pembayaran_material' && $events[0]['context']['methode'] === $method, 'Wrong attribution');
    check(strpos(json_encode($events), 'PRIVATE') === false, 'Unsafe metadata');
    return array($events[0], $output, $ci);
}
if (defined('MATERIAL_FIXTURE_ONLY')) { return; }
if ($child === '--material-abort') { material_run('save_payment_new', array('abort_commit' => true)); exit; }
$reads = array('index', 'payment_list', 'get_data_payment_list_pr', 'get_data_payment_list_non_pr', 'form_payment_new', 'view_payment_new', 'list_request_payment');
foreach ($reads as $method) {
    $args = $method === 'view_payment_new' ? array('VOUCHER-1') : ($method === 'list_request_payment' ? array('pr') : array());
    list($event, $output, $controller) = material_run($method, array(), array(), $args);
    check($event['status'] === 1, $method . ': success');
    $queryCount = $controller->db->queryNumber;
    for ($i = 1; $i <= $queryCount; $i++) {
        list($event) = material_run($method, array('fail_query' => $i, 'expected_exception' => $method === 'form_payment_new'), array(), $args);
        check($event['status'] === 0, $method . ': missed query ' . $i);
    }
    foreach (array('writer_failure', 'writer_exception') as $writer) {
        list($event, $otherOutput) = material_run($method, array($writer => true), array(), $args);
        check($event['status'] === 1 && $output === $otherOutput, 'Writer altered response');
    }
    list($event) = material_run($method, array('render_exception' => true, 'expected_exception' => true), array(), $args);
    if (in_array($method, array('get_data_payment_list_pr', 'get_data_payment_list_non_pr'), true)) { check($event['status'] === 1, 'Tables do not render'); }
    else { check($event['status'] === 0, 'Render failure'); }
}
foreach (array('save_payment_new', 'save_payment_new_nonmaterial') as $method) {
    foreach (array('TR-01', 'TR-02') as $type) {
        foreach (array('IDR', 'USD') as $currency) {
            foreach (array('PO', 'FORWARDER') as $module) {
                foreach (array('company', 'recipient') as $bearer) {
                    list($event, $output, $controller) = material_run($method, array('payment_type' => $type, 'currency' => $currency, 'payment_module' => $module, 'received_goods' => true), array('admin_charge_bearer' => $bearer));
                    check($event['status'] === 1 && json_decode($output, true)['save'] === true, $method . ': write success');
                    check(in_array('trans_commit', $event['calls_at_write'], true), 'Success before commit');
                }
            }
        }
    }
    list($event, $output, $controller) = material_run($method);
    foreach (array('trans_begin_failure', 'trans_commit_failure', 'transaction_failure', 'generate_failure') as $failure) {
        list($event, $output) = material_run($method, array($failure => true));
        check($event['status'] === 0 && json_decode($output, true)['save'] === false, $failure);
    }
    for ($i = 1; $i <= $controller->db->writeNumber; $i++) {
        list($event, $output) = material_run($method, array('fail_write' => $i));
        check($event['status'] === 0 && json_decode($output, true)['save'] === false, 'Missed write ' . $i);
    }
    for ($i = 1; $i <= $controller->db->queryNumber; $i++) {
        list($event, $output) = material_run($method, array('fail_query' => $i));
        check($event['status'] === 0 && json_decode($output, true)['save'] === false, 'Missed write query ' . $i);
    }
    foreach (array(array('id_req' => array()), array('id_req' => array('')), array('id_req' => 'bad'), array('admin_charge_bearer' => 'bad')) as $invalid) {
        list($event) = material_run($method, array(), $invalid); check($event['status'] === 0, 'Validation failed');
    }
    list($event) = material_run($method, array('missing_table' => $method === 'save_payment_new' ? 'purchase_order_request_payment' : 'purchase_order_request_payment_nm'));
    check($event['status'] === 0, 'Missing document');
    foreach (array('writer_failure', 'writer_exception') as $writer) {
        list($event, $output) = material_run($method, array($writer => true));
        check($event['status'] === 1 && json_decode($output, true)['save'] === true, 'Writer altered payment');
    }
    foreach (array('TR-01', 'TR-02') as $type) {
        list($event, $output, $controller) = material_run($method, array('payment_type' => $type, 'currency' => 'USD'));
        foreach (range(1, $controller->db->writeNumber) as $number) {
            list($event, $output) = material_run($method, array('payment_type' => $type, 'currency' => 'USD', 'fail_write' => $number));
            check($event['status'] === 0 && json_decode($output, true)['save'] === false, 'Branch write failure');
        }
    }
}
foreach (array(1, 2) as $type) {
    list($event, $output, $controller) = material_run('request_payment_save', array(), array('id_req' => 'REQ-1', 'tipetrans' => $type));
    check($event['status'] === 1 && json_decode($output, true)['save'] === true, 'Edit success');
    for ($i = 1; $i <= $controller->db->writeNumber; $i++) {
        list($event, $output) = material_run('request_payment_save', array('fail_write' => $i), array('id_req' => 'REQ-1', 'tipetrans' => $type));
        check($event['status'] === 0 && json_decode($output, true)['save'] === false, 'Edit write failure');
    }
}
foreach (array('trans_begin_failure', 'trans_commit_failure', 'transaction_failure') as $failure) {
    foreach (array('request_payment_save', 'list_request_payment') as $method) {
        list($event) = material_run($method, array($failure => true), array('id_req' => 'REQ-1'), $method === 'list_request_payment' ? array('pr') : array());
        check($event['status'] === 0, 'Reset/edit transaction failure');
    }
}
list($event) = material_run('view_payment_new', array('journal_fallback' => true), array(), array('VOUCHER-1'));
check($event['status'] === 1, 'Journal fallback');
foreach (array('form_payment_new', 'view_payment_new') as $method) {
    list($event) = material_run($method, array('empty' => true, 'expected_exception' => true), array(), $method === 'view_payment_new' ? array('missing') : array());
    check($event['status'] === 0 && $event['context']['data']['reason'] === 'not_found', 'Missing form/detail');
}
list($event) = material_run('form_payment_new', array('get' => array('id_payment' => ''), 'expected_exception' => true));
check($event['status'] === 0, 'Empty form selection');
list($event) = material_run('save_payment_new', array(), array('id_req' => array_fill(0, 101, 'REQ-1')));
check($event['status'] === 1 && count($event['context']['data']['document_ids']) === 100 && $event['context']['data']['document_count'] === 101, 'Bounded batch metadata');
$childOutput = array(); $childExit = 0;
exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' --material-abort', $childOutput, $childExit);
$auditLines = array_values(array_filter($childOutput, function ($line) { return strpos($line, 'AUDIT:') === 0; }));
check($childExit === 0 && count($auditLines) === 1, 'Actual interrupted subprocess');
$shutdownEvent = json_decode(substr($auditLines[0], 6), true);
check($shutdownEvent['status'] === 0 && $shutdownEvent['context']['data']['outcome'] === 'interrupted', 'Interrupted outcome');
echo 'PASS Material batch 1: ' . $scenarioCount . " fixture scenarios\n";
