<?php
// Real controller, recorder, and model; in-memory persistence and rendering fixtures.
define('AUDIT_FIXTURE_ONLY', true);
define('DBACC', 'fixture_accounting');
require __DIR__ . '/Audit_test.php';
function has_permission($permission) { return true; }
function base_url($path = '') { return '/fixture/' . $path; }
class BF_Model {
    public function __get($key) { $ci =& get_instance(); return $ci->$key ?? null; }
}
require dirname(__DIR__) . '/models/Request_payment_model.php';

class Batch4Input extends FixtureInput {
    public $getValues = array();
    public function get($key = null) { return $key === null ? $this->getValues : ($this->getValues[$key] ?? null); }
    public function get_post($key) { return $this->post($key) ?? $this->get($key); }
}
class Batch4Uri {
    public $segments = array();
    public function segment($index) { return $this->segments[$index] ?? null; }
}
class Batch4Result {
    private $table;
    public function __construct($table) { $this->table = $table; }
    public function row_array() {
        global $scenario;
        if (isset($scenario['missing_table']) && $scenario['missing_table'] === $this->table) { return array(); }
        return array(
            'id' => 1, 'ids' => 1, 'no_doc' => $scenario['record_doc'] ?? 'DOC-TEST',
            'no_dokumen' => $scenario['record_doc'] ?? 'DOC-TEST', 'no_non_po' => 'DOC-TEST', 'no_pr' => 'PR-TEST',
            'tipe' => $scenario['source_type'] ?? 'kasbon', 'kategori' => $scenario['source_category'] ?? 'Kasbon',
            'nama' => 'PRIVATE', 'nm_lengkap' => 'PRIVATE', 'nm_pic' => 'PRIVATE', 'nm_supplier' => 'PRIVATE',
            'nm_company' => 'STM', 'company' => 'STM', 'company_id' => 'COM003', 'hris_company_id' => 'COM003',
            'id_company' => 'COM003', 'department_id' => 1, 'request_by' => 'PRIVATE', 'created_by' => 'PRIVATE',
            'tgl_doc' => '2026-10-11', 'tanggal_doc' => '2026-10-11', 'tanggal' => '2026-10-11',
            'created_date' => '2026-10-11', 'created_on' => '2026-10-11', 'approved_on' => '2026-10-11',
            'approved_date' => '2026-10-11', 'tgl_bayar' => '2026-10-11', 'pa_tgl_pengajuan' => '2026-10-11',
            'pa_tgl_bayar' => '2026-10-11', 'tgl_pembayaran_paid' => '2026-10-11', 'pa_id' => 'PAY-1',
            'pa_id_payment' => 'PAY-1', 'pa_diajukan_oleh' => 'PRIVATE', 'dibayar_oleh_nama' => 'PRIVATE',
            'keperluan' => 'PRIVATE', 'informasi' => 'PRIVATE', 'deskripsi' => 'PRIVATE',
            'deskripsi_keperluan' => 'PRIVATE', 'keterangan' => 'PRIVATE', 'project_name' => 'PRIVATE',
            'nm_barang' => 'PRIVATE', 'jenis_pr' => $scenario['jenis_pr'] ?? 'pr departemen',
            'tipe_pr' => $scenario['tipe_pr'] ?? 'pr departemen', 'id_pr' => 'PR-TEST',
            'jumlah' => 100, 'jumlah_kasbon' => 100, 'jumlah_expense' => 100, 'nilai_pengajuan' => 100,
            'total_pr' => 100, 'grand_total' => 100, 'selisih' => 100, 'kurang_bayar' => $scenario['kurang_bayar'] ?? 0,
            'id_kasbon' => $scenario['id_kasbon'] ?? null, 'no_kasbon_consultant' => $scenario['no_kasbon_consultant'] ?? null,
            'no_expense_consultant' => null, 'id_penawaran' => 'Q-1', 'id_spk_penawaran' => 'SPK-1', 'id_header' => 'K-1',
            'bank_id' => 1, 'bank' => 1, 'bank_name' => '1101-01 - PRIVATE', 'accnumber' => 'PRIVATE',
            'bank_number' => 'PRIVATE', 'accname' => 'PRIVATE', 'bank_account' => 'PRIVATE',
            'kode_bank' => 'BNK', 'no_perkiraan' => '1101-01', 'kode' => 'IDR', 'curr' => 'IDR', 'currency' => 'IDR',
            'link_doc' => 'doc.pdf', 'sts_reject' => 0, 'sts_reject_manage' => 0, 'reject_reason' => 'PRIVATE',
            'exp_inv_po' => $scenario['exp_inv_po'] ?? 0, 'exp_pib' => $scenario['exp_pib'] ?? 0,
            'no_po' => $scenario['no_po'] ?? 'PO-1', 'invoice_no' => 'INV-TEST',
            'id_top' => array_key_exists('id_top', $scenario) ? $scenario['id_top'] : 1,
            'nilai_ppn' => 11, 'nilai_disc' => 0, 'nilai' => 100, 'group_top' => $scenario['group_top'] ?? '76',
            'total_pengajuan' => !empty($scenario['empty']) ? 0 : 2, 'total_nilai' => !empty($scenario['empty']) ? 0 : 200,
            'total_cash' => 50, 'total_kasbon' => 50, 'total_expense' => 50, 'total_periodik' => 25,
            'total_transport' => 25, 'total_petty_cash' => 0,
        );
    }
    public function row() { $row = $this->row_array(); return $row ? (object) $row : null; }
    public function result() {
        global $scenario;
        if (!empty($scenario['empty'])) { return array(); }
        if ($this->table === 'tr_added_req_payment' && isset($scenario['added_items'])) { return $scenario['added_items']; }
        $row = $this->row();
        return $row ? array($row) : array();
    }
    public function result_array() {
        global $scenario;
        if (!empty($scenario['empty'])) { return array(); }
        $row = $this->row_array();
        return $row ? array($row) : array();
    }
    public function num_rows() {
        global $scenario;
        if (strpos($this->table, 'kons_tr_kasbon_project_header') !== false) {
            if (!empty($scenario['partial_kasbon']) && empty($scenario['_counted_once'])) {
                $scenario['_counted_once'] = true;
                return 2;
            }
            return !empty($scenario['consultant_kasbon']) ? 1 : 0;
        }
        if ($this->table === 'payment_approve') { return !empty($scenario['existing_payment']) ? 1 : 0; }
        return !empty($scenario['empty']) ? 0 : 1;
    }
}
class Batch4Db extends FixtureDb {
    public $queryNumber = 0; public $queryError = 0; public $writeNumber = 0;
    public $writes = array(); public $committed = false; private $fromTable = '';
    public function __call($name, $args) {
        global $scenario;
        if ($name === 'from') { $this->fromTable = $args[0] ?? ''; $this->calls[] = $name; return $this; }
        if ($name === 'count_all_results') {
            $this->calls[] = $name; $this->queryNumber++;
            $this->queryError = !empty($scenario['query_failure']) || ($scenario['fail_query'] ?? 0) === $this->queryNumber ? 999 : 0;
            return !empty($scenario['empty']) ? 0 : 1;
        }
        if (in_array($name, array('get', 'get_where', 'query'), true)) {
            $this->calls[] = $name; $this->queryNumber++;
            $this->queryError = !empty($scenario['query_failure']) || ($scenario['fail_query'] ?? 0) === $this->queryNumber ? 999 : 0;
            $table = $args[0] ?? $this->fromTable;
            if ($name === 'query') {
                if (stripos($table, 'UPDATE ') === 0) { return $this->write($name, $args); }
                preg_match('/from\s+([\w.]+)/i', $table, $match); $table = $match[1] ?? $table;
            }
            $table = trim(preg_replace('/\s+[a-z0-9_]+$/i', '', (string) $table));
            return new Batch4Result($table);
        }
        if ($name === 'error') { return array('code' => $this->queryError, 'message' => $this->queryError ? 'PRIVATE_SQL_ERROR' : ''); }
        if (in_array($name, array('insert', 'insert_batch', 'update', 'update_batch', 'delete'), true)) {
            $this->calls[] = $name; return $this->write($name, $args);
        }
        if ($name === 'trans_commit') {
            if (!empty($scenario['abort_commit'])) { exit; }
            $result = parent::__call($name, $args); $this->committed = $result; return $result;
        }
        if ($name === 'trans_rollback') { $this->writes = array(); $this->committed = false; }
        if ($name === 'escape') { return "'" . (string) ($args[0] ?? '') . "'"; }
        if ($name === 'escape_str' || $name === 'escape_like_str') { return (string) ($args[0] ?? ''); }
        return parent::__call($name, $args);
    }
    private function write($name, $args) {
        global $scenario;
        $this->writeNumber++;
        if (!empty($scenario[$name . '_failure']) || ($scenario['fail_write'] ?? 0) === $this->writeNumber) {
            $this->failed = true; $this->queryError = 999; return false;
        }
        $this->writes[] = array($name, $args); return true;
    }
}
class Batch4Model extends FixtureModel {
    public function __call($name, $args) {
        global $scenario, $ci;
        if (!empty($scenario['model_exception'])) { throw new RuntimeException('PRIVATE'); }
        if ($name === 'generate_id_payment') {
            $ci->db->get('payment_approve');
            return !empty($scenario['generate_failure']) ? false : 'BK-BNK-2610-0001';
        }
        if ($name === 'generate_id_detail') {
            $ci->db->get('payment_approve_details');
            return !empty($scenario['generate_detail_failure']) ? false : 'PAY1-10-2600001';
        }
        if ($name === 'copy_to_payment') {
            if (!empty($scenario['copy_failure'])) { return false; }
            if (!empty($scenario['copy_query_failure'])) { $ci->db->queryError = 999; return true; }
            return true;
        }
        if ($name === 'excel_payment_list') {
            $ci->db->get('request_payment');
            $ci->load->view('excel_payment_list', array());
            return;
        }
        if ($name === 'get_data_req_payment') {
            $ci->db->get('v_request_payment');
            echo json_encode(array('draw' => 1, 'recordsTotal' => !empty($scenario['empty']) ? 0 : 1, 'recordsFiltered' => !empty($scenario['empty']) ? 0 : 1, 'data' => array()));
            return;
        }
        if ($name === 'get_summary_cards') {
            $ci->db->get('v_request_payment');
            return array('total_pengajuan' => !empty($scenario['empty']) ? 0 : 2, 'total_nilai' => !empty($scenario['empty']) ? 0.0 : 200.0);
        }
        $ci->db->get('request_payment');
        if (!empty($scenario['render_exit'])) { exit; }
        if (!empty($scenario['empty'])) { return array(); }
        return array((new Batch4Result('request_payment'))->row());
    }
}
class Batch4Auth extends FixtureAuth { public function user_id() { return 7; } }
class Batch4Session extends FixtureSession { public $userdata = array(); }
class Batch4Template extends FixtureTemplate {
    public function __call($name, $args) {
        global $scenario;
        if ($name === 'render' && !empty($scenario['render_exit'])) { exit; }
        return parent::__call($name, $args);
    }
}
class Batch4Load extends FixtureLoad {
    public function view($name, $data) {
        global $scenario;
        if (!empty($scenario['render_exit'])) { exit; }
        parent::view($name, $data);
    }
}
function run_batch4($method, $options = array(), $post = array(), $get = array(), $segments = array()) {
    global $events, $scenario, $ci, $scenarioCount;
    $events = array(); $scenario = $options; $scenarioCount++;
    $ci = new Request_payment();
    $ci->db = new Batch4Db(); $ci->load = new Batch4Load();
    $consultantProp = new ReflectionProperty('Request_payment', 'consultant');
    $consultantProp->setAccessible(true); $consultantProp->setValue($ci, $ci->db);
    $ci->input = new Batch4Input(); $ci->uri = new Batch4Uri();
    $ci->Request_payment_model = new Batch4Model(); $ci->Approval_request_payment_model = $ci->Request_payment_model;
    $ci->auth = new Batch4Auth(); $ci->session = new Batch4Session(); $ci->template = new Batch4Template();
    if (!empty($options['real_model']) && empty($options['model_exception'])) {
        $ci->Request_payment_model = (new ReflectionClass('Request_payment_model'))->newInstanceWithoutConstructor();
        $ref = new ReflectionClass('Request_payment_model');
        foreach (array('consultant', 'hris') as $propName) {
            $prop = $ref->getProperty($propName); $prop->setAccessible(true); $prop->setValue($ci->Request_payment_model, $ci->db);
        }
        $ci->Approval_request_payment_model = $ci->Request_payment_model;
    }
    $ci->input->values = array_merge(array(
        'tab' => null, 'id_invoice' => 1, 'id' => 'DOC-TEST', 'id_expense' => 'EXP-TEST', 'id_kasbon' => 'KSB-TEST',
        'company_id' => 3, 'date_from' => '2026-10-01', 'date_to' => '2026-10-11', 'kategori' => 'Kasbon',
        'wdo' => 1, 'draw' => 1, 'start' => 0, 'length' => 10, 'search' => array('value' => 'PRIVATE'),
        'tanggal_pembayaran_DOC-TEST' => '11/10/2026', 'kategori_DOC-TEST' => 'Kasbon', 'nilai_pengajuan_DOC-TEST' => '100.000',
    ), $post);
    $ci->input->getValues = array_merge(array('tgl_from' => '2026-10-01', 'tgl_to' => '2026-10-11', 'tipe' => 'kasbon', 'status' => 'paid'), $get);
    $ci->uri->segments = $segments;
    ob_start();
    try { $ci->$method(); }
    catch (Throwable $error) {
        if (isset($ci->module_audit_recorder)) { $ci->module_audit_recorder->finish_interrupted(); }
        if (empty($options['model_exception']) && empty($options['render_exception'])) { ob_end_clean(); throw $error; }
    }
    $output = ob_get_clean();
    if (isset($ci->module_audit_recorder)) { $ci->module_audit_recorder->finish_interrupted(); }
    check(count($events) === 1, $method . ': exactly one audit');
    $event = $events[0];
    check($event['context']['module'] === 'request_payment' && $event['context']['methode'] === $method, 'Endpoint identity');
    check(strpos(json_encode($events), 'PRIVATE') === false, $method . ': private metadata leaked');
    return array($event, $output, $ci);
}
if ($child && strpos($child, '--batch4-exit-') === 0) {
    run_batch4(substr($child, strlen('--batch4-exit-')), array('render_exit' => true, 'abort_commit' => true)); exit;
}
$reads = array('change_tab', 'excel_payment_list', 'view_receive_invoice', 'get_data_req_payment', 'get_summary_cards');
foreach ($reads as $method) {
    foreach (array(array(), array('empty' => true), array('writer_failure' => true), array('writer_exception' => true)) as $options) {
        list($event) = run_batch4($method, $options); check($event['status'] === 1, $method . ': successful read');
    }
    list($event) = run_batch4($method, array('query_failure' => true)); check($event['status'] === 0, $method . ': query failure');
}
foreach (array('excel_payment_list', 'view_receive_invoice') as $method) {
    list($event) = run_batch4($method, array('render_exception' => true));
    check($event['status'] === 0 && $event['context']['data']['outcome'] === 'interrupted', $method . ': render interruption');
}
foreach (array('change_tab', 'excel_payment_list', 'get_data_req_payment', 'get_summary_cards') as $method) {
    list($event) = run_batch4($method, array('model_exception' => true));
    check($event['status'] === 0 && $event['context']['data']['outcome'] === 'interrupted', $method . ': model interruption');
}
foreach ($reads as $method) {
    list($event, $output, $controller) = run_batch4($method);
    for ($query = 1; $query <= $controller->db->queryNumber; $query++) {
        list($failed) = run_batch4($method, array('fail_query' => $query));
        check($failed['status'] === 0, $method . ': earlier query failure ' . $query . ' must survive later successful queries');
    }
}
foreach (array(null, 'transport', 'kasbon', 'expense', 'pembayaran_po', 'periodik', 'direct_payment') as $tab) {
    foreach (array(
        array('real_model' => true, 'record_doc' => 'ER-TEST'),
        array('real_model' => true, 'empty' => true),
        array('real_model' => true, 'source_type' => 'expense', 'exp_inv_po' => 1, 'no_po' => 'TR-01, TR-02'),
        array('real_model' => true, 'source_type' => 'expense', 'exp_pib' => 1, 'record_doc' => 'ROS-01'),
    ) as $options) {
        list($event, $output, $controller) = run_batch4('change_tab', $options, array('tab' => $tab));
        check($event['status'] === 1 && is_string($output), 'change_tab real model');
        for ($query = 1; $query <= $controller->db->queryNumber; $query++) {
            list($failed) = run_batch4('change_tab', array_merge($options, array('fail_query' => $query)), array('tab' => $tab));
            check($failed['status'] === 0, 'change_tab real model query failure');
        }
    }
}
foreach (array('paid', 'open', '') as $status) {
    list($event, $output, $controller) = run_batch4('excel_payment_list', array('real_model' => true), array(), array('tgl_from' => null, 'tgl_to' => null, 'tipe' => 'kasbon', 'status' => $status), array(3 => '2026-10-01', 4 => '2026-10-11'));
    check($event['status'] === 1 && $event['context']['data']['row_count'] === 1, 'excel_payment_list real model');
    list($failed) = run_batch4('excel_payment_list', array('real_model' => true, 'fail_query' => 1), array(), array('status' => $status));
    check($failed['status'] === 0, 'excel_payment_list real model query failure');
}
foreach (array('', array()) as $invalidId) {
    list($event) = run_batch4('view_receive_invoice', array(), array('id_invoice' => $invalidId));
    check($event['status'] === 0 && $event['context']['data']['reason'] === 'invalid_input', 'Invalid invoice ID');
}
list($event) = run_batch4('view_receive_invoice', array('missing_table' => 'tr_invoice_po'));
check($event['status'] === 0 && $event['context']['data']['reason'] === 'invoice_not_found', 'Missing invoice');
list($event) = run_batch4('view_receive_invoice', array('missing_table' => 'tr_top_po'));
check($event['status'] === 0 && $event['context']['data']['reason'] === 'top_not_found', 'Missing invoice TOP');
foreach (array('76', '77', '78') as $groupTop) {
    list($event) = run_batch4('view_receive_invoice', array('group_top' => $groupTop));
    check($event['status'] === 1 && $event['context']['data']['group_top'] === $groupTop && $event['context']['data']['row_count'] === 1, 'Invoice TOP group ' . $groupTop);
}
list($event) = run_batch4('view_receive_invoice', array('id_top' => null));
check($event['status'] === 1 && $event['context']['data']['row_count'] === 1, 'Invoice incoming view');
foreach (array('belum_dibayar', 'menunggu_pembayaran', 'sudah_dibayar') as $tab) {
    foreach (array('Kasbon', 'Transport', 'Cash', 'Non-PO', 'Expense', 'Periodik', 'Direct Payment', 'Refill Pettycash', 'Petty Cash Hutang') as $cat) {
        $options = array('real_model' => true, 'source_category' => $cat, 'no_kasbon_consultant' => $cat === 'Kasbon' ? 'CONS-1' : null, 'record_doc' => $cat === 'Refill Pettycash' ? 'RPC-1' : ($cat === 'Petty Cash Hutang' ? 'PHP-1' : 'DOC-TEST'));
        list($event, $output, $controller) = run_batch4('get_data_req_payment', $options, array('tab' => $tab, 'kategori' => $cat));
        check($event['status'] === 1 && $event['context']['data']['row_count'] === 1 && isset(json_decode($output, true)['data']), 'get_data_req_payment real model ' . $tab . '/' . $cat);
        for ($query = 1; $query <= $controller->db->queryNumber; $query++) {
            list($failed) = run_batch4('get_data_req_payment', array_merge($options, array('fail_query' => $query)), array('tab' => $tab, 'kategori' => $cat));
            check($failed['status'] === 0, 'get_data_req_payment real model query failure ' . $query);
        }
    }
}
foreach (array(array('real_model' => true), array('real_model' => true, 'empty' => true)) as $options) {
    list($event, $output, $controller) = run_batch4('get_summary_cards', $options);
    check($event['status'] === 1 && isset(json_decode($output, true)['total_pengajuan']), 'get_summary_cards real model');
    for ($query = 1; $query <= $controller->db->queryNumber; $query++) {
        list($failed) = run_batch4('get_summary_cards', array_merge($options, array('fail_query' => $query)));
        check($failed['status'] === 0, 'get_summary_cards real model query failure ' . $query);
    }
}
$jsonWrites = array('save_approval_checker_consultant' => 'save', 'save_approval_consultant' => 'save', 'save_request_payment' => 'status');
foreach ($jsonWrites as $method => $key) {
    list($event, $output, $controller) = run_batch4($method);
    check($event['status'] === 1 && json_decode($output, true)[$key] == 1 && $controller->db->committed, $method . ': successful write');
    check(in_array('trans_commit', $event['calls_at_write'], true), $method . ': audit before commit');
    foreach (array('trans_begin_failure', 'transaction_failure', 'trans_commit_failure') as $failure) {
        list($event, $output, $controller) = run_batch4($method, array($failure => true));
        check($event['status'] === 0 && json_decode($output, true)[$key] == 0 && !$controller->db->committed, $method . ': ' . $failure);
    }
    foreach (array('writer_failure', 'writer_exception') as $failure) {
        list($event, $output, $controller) = run_batch4($method, array($failure => true));
        check($event['status'] === 1 && json_decode($output, true)[$key] == 1 && $controller->db->committed, $method . ': writer changed business result');
    }
    list($event, $output, $controller) = run_batch4($method);
    for ($write = 1; $write <= $controller->db->writeNumber; $write++) {
        list($failed, $failedOut, $failedCi) = run_batch4($method, array('fail_write' => $write, 'status_does_not_fail' => true));
        check($failed['status'] === 0 && json_decode($failedOut, true)[$key] == 0 && !$failedCi->db->committed, $method . ': write failure ' . $write);
    }
    for ($query = 1; $query <= $controller->db->queryNumber; $query++) {
        list($failed, $failedOut, $failedCi) = run_batch4($method, array('fail_query' => $query, 'status_does_not_fail' => true));
        check($failed['status'] === 0 && json_decode($failedOut, true)[$key] == 0 && !$failedCi->db->committed, $method . ': query failure ' . $query);
    }
}
foreach (array(array(), array('consultant_kasbon' => true)) as $options) {
    foreach (array('save_approval_checker_consultant', 'save_approval_consultant') as $method) {
        list($event, $output, $controller) = run_batch4($method, $options);
        check($event['status'] === 1 && json_decode($output, true)['save'] === true && $controller->db->committed, $method . ': consultant branch');
        for ($write = 1; $write <= $controller->db->writeNumber; $write++) {
            list($failed, $failedOut, $failedCi) = run_batch4($method, array_merge($options, array('fail_write' => $write, 'status_does_not_fail' => true)));
            check($failed['status'] === 0 && json_decode($failedOut, true)['save'] === false && !$failedCi->db->committed, $method . ': branch write failure');
        }
        for ($query = 1; $query <= $controller->db->queryNumber; $query++) {
            list($failed, $failedOut, $failedCi) = run_batch4($method, array_merge($options, array('fail_query' => $query, 'status_does_not_fail' => true)));
            check($failed['status'] === 0 && json_decode($failedOut, true)['save'] === false && !$failedCi->db->committed, $method . ': branch query failure');
        }
    }
}
list($event, $output, $controller) = run_batch4('save_approval_consultant', array('consultant_kasbon' => true, 'partial_kasbon' => true));
check($event['status'] === 1 && json_decode($output, true)['save'] === true && $controller->db->committed, 'save_approval_consultant partial kasbon');
foreach (array(array('id' => ''), array('id' => array()), array('id_expense' => '')) as $post) {
    list($event, $output, $controller) = run_batch4('save_approval_checker_consultant', array(), $post);
    check($event['status'] === 0 && json_decode($output, true)['save'] === false && !$controller->db->committed, 'Invalid checker consultant input');
}
list($event, $output, $controller) = run_batch4('save_approval_checker_consultant', array('consultant_kasbon' => true), array('id_kasbon' => ''));
check($event['status'] === 0 && json_decode($output, true)['save'] === false, 'Invalid checker consultant kasbon ID');
list($event, $output, $controller) = run_batch4('save_approval_checker_consultant', array('consultant_kasbon' => true, 'missing_table' => 'tr_kasbon'));
check($event['status'] === 0 && json_decode($output, true)['save'] === false && !$controller->db->committed, 'Missing checker consultant kasbon source');
foreach (array(array('id' => ''), array('id' => array())) as $post) {
    list($event, $output, $controller) = run_batch4('save_approval_consultant', array(), $post);
    check($event['status'] === 0 && json_decode($output, true)['save'] === false && !$controller->db->committed, 'Invalid approval consultant ID');
}
foreach (array('request_payment', 'fixture_accounting.coa_master') as $table) {
    if ($table === 'request_payment') {
        list($event, $output, $controller) = run_batch4('save_approval_consultant', array('missing_table' => $table));
        check($event['status'] === 0 && json_decode($output, true)['save'] === false && !$controller->db->committed, 'Missing consultant request_payment');
    }
}
list($event) = run_batch4('save_approval_consultant', array('missing_table' => 'db_consultant_new.kons_tr_expense_report_project_header'));
check($event['status'] === 0, 'Missing consultant expense detail');
list($event) = run_batch4('save_approval_consultant', array('consultant_kasbon' => true, 'missing_table' => 'tr_kasbon'));
check($event['status'] === 0, 'Missing consultant kasbon detail');
foreach (array('generate_failure', 'generate_detail_failure', 'existing_payment') as $failure) {
    list($event, $output, $controller) = run_batch4('save_approval_consultant', array($failure => true));
    check($event['status'] === 0 && json_decode($output, true)['save'] === false && !$controller->db->writes, 'save_approval_consultant ' . $failure);
}
foreach (array(1, 0) as $wdo) {
    list($event, $output, $controller) = run_batch4('added_pilih_data', array(), array('wdo' => $wdo));
    check($event['status'] === 1 && $controller->db->committed && $event['context']['data']['selection_action'] === ($wdo ? 'select' : 'deselect'), 'added_pilih_data wdo=' . $wdo);
    check(in_array('trans_commit', $event['calls_at_write'], true), 'added_pilih_data commit before audit');
    foreach (array('trans_begin_failure', 'transaction_failure', 'trans_commit_failure', 'fail_write' => 1) as $key => $val) {
        $opts = is_int($key) ? array($val => true) : array($key => $val, 'status_does_not_fail' => true);
        list($failed, $out, $failedCi) = run_batch4('added_pilih_data', $opts, array('wdo' => $wdo));
        check($failed['status'] === 0 && !$failedCi->db->committed, 'added_pilih_data failure wdo=' . $wdo);
    }
}
foreach (array(array('id' => ''), array('id' => array()), array('wdo' => 2), array('wdo' => 1, 'kategori' => '')) as $post) {
    list($event, $output, $controller) = run_batch4('added_pilih_data', array(), $post);
    check($event['status'] === 0 && !$controller->db->committed, 'added_pilih_data invalid input');
}
list($event, $output, $controller) = run_batch4('reset_choosed_req_payment');
check($event['status'] === 1 && $controller->db->committed && $event['context']['data']['outcome'] === 'reset', 'reset_choosed_req_payment success');
check(in_array('trans_commit', $event['calls_at_write'], true), 'reset_choosed_req_payment commit before audit');
foreach (array('trans_begin_failure', 'transaction_failure', 'trans_commit_failure', 'delete_failure') as $failure) {
    list($event, $output, $controller) = run_batch4('reset_choosed_req_payment', array($failure => true, 'status_does_not_fail' => $failure === 'delete_failure'));
    check($event['status'] === 0 && !$controller->db->committed, 'reset_choosed_req_payment ' . $failure);
}
foreach (array('writer_failure', 'writer_exception') as $failure) {
    foreach (array('added_pilih_data', 'reset_choosed_req_payment') as $method) {
        list($event, $output, $controller) = run_batch4($method, array($failure => true));
        check($event['status'] === 1 && $controller->db->committed, $method . ': writer nonblocking');
    }
}
list($event, $output, $controller) = run_batch4('save_request_payment', array('empty' => true));
check($event['status'] === 1 && json_decode($output, true)['status'] === 1 && $event['context']['data']['processed_count'] === 0 && $event['context']['data']['outcome'] === 'empty_selection', 'save_request_payment empty selection');
$addedTypes = array(
    'kasbon' => 'tr_kasbon', 'expense' => 'tr_expense', 'transport' => 'tr_transport',
    'periodik' => 'tr_pengajuan_rutin', 'cash' => 'tr_pr_non_po', 'direct payment' => 'tr_direct_payment',
    'direct_payment' => 'tr_direct_payment', 'petty cash hutang' => null, 'petty_cash_hutang' => null,
    'petty cash' => null, 'petty_cash' => null, 'refill pettycash' => null, 'refill_pettycash' => null,
);
foreach ($addedTypes as $type => $sourceTable) {
    $items = array((object) array('no_doc' => 'DOC-TEST', 'tipe' => $type));
    list($event, $output, $controller) = run_batch4('save_request_payment', array('added_items' => $items), array(
        'tanggal_pembayaran_DOC-TEST' => array('11/10/2026'),
        'kategori_DOC-TEST' => array($type),
        'nilai_pengajuan_DOC-TEST' => array('100.000'),
    ));
    check($event['status'] === 1 && json_decode($output, true)['status'] === 1 && $controller->db->committed, 'save_request_payment type ' . $type);
    for ($write = 1; $write <= $controller->db->writeNumber; $write++) {
        list($failed, $failedOut, $failedCi) = run_batch4('save_request_payment', array('added_items' => $items, 'fail_write' => $write, 'status_does_not_fail' => true));
        check($failed['status'] === 0 && json_decode($failedOut, true)['status'] === 0 && !$failedCi->db->committed, 'save_request_payment write failure ' . $type . '#' . $write);
        check(strpos($failedOut, 'PRIVATE_SQL_ERROR') === false, 'Raw DB error leaked');
    }
    if ($sourceTable !== null) {
        list($failed, $failedOut, $failedCi) = run_batch4('save_request_payment', array('added_items' => $items, 'missing_table' => $sourceTable));
        check($failed['status'] === 0 && json_decode($failedOut, true)['status'] === 0 && !$failedCi->db->committed, 'save_request_payment missing source ' . $sourceTable);
    }
}
foreach (array(
    array((object) array('no_doc' => '', 'tipe' => 'kasbon')),
    array((object) array('no_doc' => 'DOC-TEST', 'tipe' => 'unknown_type')),
) as $invalidItems) {
    list($event, $output, $controller) = run_batch4('save_request_payment', array('added_items' => $invalidItems));
    check($event['status'] === 0 && json_decode($output, true)['status'] === 0 && !$controller->db->committed, 'save_request_payment invalid added item');
}
foreach (array('copy_failure', 'copy_query_failure') as $copyErr) {
    list($event, $output) = run_batch4('save_request_payment', array($copyErr => true));
    check($event['status'] === 0 && json_decode($output, true)['status'] === 0, 'save_request_payment ' . $copyErr);
}
$manyItems = array();
for ($i = 1; $i <= 101; $i++) { $manyItems[] = (object) array('no_doc' => 'DOC-' . $i, 'tipe' => 'kasbon'); }
list($event) = run_batch4('save_request_payment', array('added_items' => $manyItems));
check($event['status'] === 1 && $event['context']['data']['document_count'] === 101 && count($event['context']['data']['document_ids']) === 100 && $event['context']['data']['processed_count'] === 101, 'save_request_payment bounded 100 IDs');
foreach (array('change_tab', 'excel_payment_list', 'save_approval_consultant', 'save_request_payment') as $method) {
    $output = shell_exec('"' . PHP_BINARY . '" "' . __FILE__ . '" --batch4-exit-' . $method);
    check(substr_count($output, 'AUDIT:') === 1, $method . ': real shutdown exactly one event');
    $event = json_decode(trim(substr($output, strpos($output, 'AUDIT:') + 6)), true);
    check($event['status'] === 0 && $event['context']['data']['outcome'] === 'interrupted', $method . ': real shutdown interrupted');
}
// Rollout remains opt-in per endpoint: instantiating the controller alone must stay inactive.
$events = array(); $scenario = array(); $ci = new Request_payment();
check(!isset($ci->module_audit_recorder) && !$events, 'Constructor activated audit prematurely'); $scenarioCount++;
echo 'PASS Request Payment batch 4: ' . ($scenarioCount + 4) . " scenarios, real controller/recorder and model\n";
