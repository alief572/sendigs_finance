<?php
// Controller/recorder are real; persistence, uploads and rendering are fixtures.
define('AUDIT_FIXTURE_ONLY', true);
define('DBACC', 'fixture_accounting');
require __DIR__ . '/Audit_test.php';

class Batch2Result {
    private $table;
    private $where;
    public function __construct($table, $where = array()) { $this->table = $table; $this->where = $where; }
    public function row_array() {
        global $scenario;
        if (($scenario['missing_table'] ?? '') === $this->table ||
            (!empty($scenario['expense_cons']) && $this->table === 'request_payment' && ($this->where['no_doc'] ?? '') === 'SENDIGS-TEST')) { return array(); }
        $row = array_fill_keys(array('nama', 'nm_lengkap', 'keperluan', 'keterangan', 'informasi', 'deskripsi', 'accnumber', 'accname', 'link_doc', 'doc_file'), 'PRIVATE');
        $row += array('id' => 1, 'ids' => 1, 'no_doc' => 'DOC-TEST', 'no_req' => 'DOC-TEST', 'invoice_no' => 'INV-TEST',
            'tipe' => !empty($scenario['expense_cons']) ? 'expense' : 'kasbon', 'no_perkiraan' => '1101-01', 'coa' => '5101-01',
            'bank_name' => '1101-01 - PRIVATE', 'kode_bank' => 'BANK', 'bank_id' => 1, 'currency' => 'IDR',
            'status' => 0, 'tgl_doc' => '2026-10-11', 'tanggal' => '2026-10-11', 'tgl_pr' => '2026-10-11',
            'qty' => 1, 'harga' => 100, 'total_harga' => 100, 'total_request' => 100, 'nilai_satuan_request' => 100,
            'jumlah' => 100, 'jumlah_kasbon' => 100, 'grand_total' => 100, 'nilai' => 100, 'expense' => 100,
            'kurang_bayar' => null, 'kasbon' => 0, 'id_kasbon' => null, 'id_pr' => null);
        if (!empty($scenario['kasbon_pr'])) { $row['id_pr'] = 1; }
        if (!empty($scenario['expense_shortfall'])) { $row['id_kasbon'] = 2; $row['kurang_bayar'] = 30; }
        if (!empty($scenario['expense_credit'])) { $row['kasbon'] = 50; }
        if (!empty($this->where['admin'])) { $row['no_perkiraan'] = '6101-ADMIN'; }
        return $row;
    }
    public function row() { $row = $this->row_array(); return $row ? (object) $row : null; }
    public function result() { global $scenario; $row = $this->row(); return !$row || !empty($scenario['empty']) ? array() : array($row); }
    public function num_rows() { global $scenario; return !empty($scenario['existing_payment']) ? 1 : 0; }
}
class Batch2Db extends FixtureDb {
    public $writes = array(); public $committed = false; public $writeNumber = 0; public $queryNumber = 0; public $queryError = 0;
    public function __call($name, $args) {
        global $scenario;
        if (in_array($name, array('get', 'get_where', 'query'), true)) {
            $this->calls[] = $name;
            $this->queryNumber++; $this->queryError = ($scenario['fail_query'] ?? 0) === $this->queryNumber ? 999 : 0;
            $table = $args[0] ?? '';
            if ($name === 'query') { preg_match('/from\s+([\w.]+)/i', $table, $match); $table = $match[1] ?? ''; }
            $where = $args[1] ?? array();
            if ($name === 'query' && strpos($args[0], "menu='admin'") !== false) { $where['admin'] = true; }
            return new Batch2Result($table, $where);
        }
        if ($name === 'error') { return array('code' => !empty($scenario['query_failure']) ? 999 : $this->queryError); }
        if (in_array($name, array('insert', 'insert_batch', 'update', 'update_batch'), true)) {
            $this->calls[] = $name; $this->writeNumber++;
            $failure = !empty($scenario[$name . '_failure']) || ($scenario['fail_write'] ?? 0) === $this->writeNumber;
            if ($failure) { $this->failed = true; return false; }
            $this->writes[] = array($name, $args); return true;
        }
        if ($name === 'trans_commit') { if (!empty($scenario['abort_commit'])) { exit; } $result = parent::__call($name, $args); $this->committed = $result; return $result; }
        if ($name === 'trans_rollback') { $this->writes = array(); $this->committed = false; }
        return parent::__call($name, $args);
    }
}
class Batch2Model extends FixtureModel {
    public function __call($name, $args) {
        global $scenario;
        if (in_array($name, array('generate_id_payment', 'generate_id_detail'), true)) {
            return empty($scenario['generate_failure']) && ($name !== 'generate_id_detail' || empty($scenario['generate_detail_failure'])) ? ($name === 'generate_id_payment' ? 'PAY-TEST' : 'DET0001') : false;
        }
        global $ci;
        if (!empty($scenario['model_query_failure'])) { $ci->db->queryError = 999; }
        return parent::__call($name, $args);
    }
}
class Batch2Auth extends FixtureAuth { public function user_id() { return 7; } }
class Batch2Template extends FixtureTemplate {
    public function __call($name, $args) { global $scenario; if ($name === 'render' && !empty($scenario['render_exit'])) { exit; } return parent::__call($name, $args); }
}
class Batch2AllModel extends FixtureAllModel {
    public function dataUpdate($table, $data, $where) { global $ci; return $ci->db->update($table, $data, $where) ? 1 : -1; }
}
function run_batch2($method, $options = array(), $post = array(), $get = array()) {
    global $events, $scenario, $ci, $scenarioCount;
    $scenarioCount++; $events = array(); $scenario = $options;
    $ci = new Request_payment(); $ci->db = new Batch2Db(); $ci->Request_payment_model = new Batch2Model();
    $ci->auth = new Batch2Auth(); $ci->All_model = new Batch2AllModel();
    $ci->template = new Batch2Template();
    $ci->input->values = array_merge(array('id' => 1, 'no_doc' => 'DOC-TEST', 'tipe' => 'kasbon',
        'item' => array(array('id' => 1, 'no_doc' => 'DOC-TEST')), 'no_doc_sendigs' => 'SENDIGS-TEST', 'id_expense' => 'EXP-TEST',
        'bank_coa' => '1101-01', 'bank_nilai' => array(100), 'bank_admin' => array(0), 'status' => array('PAY-TEST'),
        'keterangan' => array('PRIVATE'), 'keperluan' => array('PRIVATE'), 'nama' => array('PRIVATE'), 'ids' => array(1)), $post);
    if ($method === 'save_payment') {
        $ci->input->values['no_doc'] = $post['no_doc'] ?? array('DOC-TEST');
        $ci->input->values['tipe'] = $post['tipe'] ?? array('kasbon');
    }
    $_GET = array_merge(array('type' => 'kasbon', 'id' => 1), $get);
    ob_start();
    try { $ci->$method(); } catch (Throwable $error) {
        if (isset($ci->module_audit_recorder)) { $ci->module_audit_recorder->finish_interrupted(); }
        if (empty($options['model_exception']) && empty($options['render_exception'])) { ob_end_clean(); throw $error; }
    }
    $output = ob_get_clean();
    if (isset($ci->module_audit_recorder)) { $ci->module_audit_recorder->finish_interrupted(); }
    check(count($events) === 1, $method . ': exactly one audit');
    check($events[0]['context']['methode'] === $method && $events[0]['context']['module'] === 'request_payment', 'Endpoint identity');
    check(strpos(json_encode($events), 'PRIVATE') === false, 'Private data leaked');
    return array($events[0], $output, $ci);
}
if ($child && strpos($child, '--batch2-exit-') === 0) {
    $method = substr($child, strlen('--batch2-exit-'));
    run_batch2($method, array('render_exit' => true, 'abort_commit' => true)); exit;
}

$reads = array('list_approve_checker', 'list_approve_management', 'list_payment', 'payment_jurnal_list');
foreach ($reads as $method) {
    foreach (array(array(), array('empty' => true), array('writer_failure' => true), array('writer_exception' => true)) as $options) {
        list($event) = run_endpoint($method, $options);
        check($event['status'] === 1, $method . ': successful read');
        check($event['context']['data']['row_count'] === (!empty($options['empty']) ? 0 : 1), $method . ': count');
    }
    list($event) = run_endpoint($method, array('query_failure' => true));
    check($event['status'] === 0, $method . ': query failure');
    foreach (array('render_exception', 'model_exception') as $failure) {
        list($event) = run_endpoint($method, array($failure => true));
        check($event['status'] === 0 && $event['context']['data']['outcome'] === 'interrupted', $method . ': interruption');
    }
}
foreach (array('approval_payment', 'approval_payment_checker') as $method) {
    foreach (array('expense', 'kasbon', 'transportasi', 'nonpo', 'periodik', 'direct_payment') as $type) {
        list($event) = run_batch2($method, array(), array(), array('type' => $type));
        check($event['status'] === 1 && $event['context']['data']['row_count'] === 1, $method . ': type ' . $type);
    }
    foreach (array(array('type' => 'invalid'), array('id' => ''), array('type' => array()), array('id' => array())) as $get) {
        list($event) = run_batch2($method, array(), array(), $get); check($event['status'] === 0, 'Invalid detail');
    }
    foreach (array('request_payment', 'tr_kasbon') as $table) {
        list($event) = run_batch2($method, array('missing_table' => $table)); check($event['status'] === 0, 'Missing detail');
    }
    foreach (array('query_failure', 'render_exception') as $failure) {
        list($event) = run_batch2($method, array($failure => true)); check($event['status'] === 0, 'Failed detail');
    }
    list($event) = run_batch2($method, array('kasbon_pr' => true)); check($event['status'] === 1, 'Kasbon PR detail');
}
$writes = array('save_approval_cons' => 'status', 'save_approval' => 'save', 'save_approval_checker' => 'save', 'save_payment' => 'hasil');
foreach ($writes as $method => $responseKey) {
    list($event, $output, $controller) = run_batch2($method);
    check($event['status'] === 1 && json_decode($output, true)[$responseKey] == 1, $method . ': success');
    check($controller->db->committed && in_array('trans_commit', $event['calls_at_write'], true), $method . ': audit follows commit');
    foreach (array('trans_begin_failure', 'transaction_failure', 'trans_commit_failure', 'update_failure') as $failure) {
        list($event, $output, $controller) = run_batch2($method, array($failure => true));
        check($event['status'] === 0 && json_decode($output, true)[$responseKey] == 0, $method . ': ' . $failure);
        check(!$controller->db->committed, $method . ': failed write committed');
    }
    foreach (array('writer_failure', 'writer_exception') as $failure) {
        list($event, $output, $controller) = run_batch2($method, array($failure => true));
        check($event['status'] === 1 && $controller->db->committed && json_decode($output, true)[$responseKey] == 1, 'Writer changed business result');
    }
}
foreach (array('approval_payment', 'approval_payment_checker', 'save_approval_cons', 'save_approval') as $method) {
    list($event) = run_batch2($method, array('fail_query' => 1));
    check($event['status'] === 0, $method . ': first query error overwritten by later success');
}
foreach (array('expense', 'kasbon', 'transportasi', 'nonpo', 'periodik', 'direct_payment', 'petty_cash_hutang') as $type) {
    list($event, $output, $controller) = run_batch2('save_approval', array(), array('tipe' => $type));
    check($event['status'] === 1 && json_decode($output, true)['save'] === true && $controller->db->committed, 'Approval type ' . $type);
    foreach (array('insert_failure', 'insert_batch_failure', 'update_failure', 'update_batch_failure', 'generate_failure') as $failure) {
        if ($type === 'petty_cash_hutang' && $failure === 'update_batch_failure') { continue; }
        list($event, $output, $controller) = run_batch2('save_approval', array($failure => true, 'status_does_not_fail' => true), array('tipe' => $type));
        check($event['status'] === 0 && json_decode($output, true)['save'] === false && !$controller->db->committed, $type . ': explicit ' . $failure);
    }
}
foreach (array('periodik', 'transportasi', 'expense', 'kasbon', 'direct_payment', 'nonpo') as $type) {
    list($event) = run_batch2('save_approval_checker', array(), array('tipe' => $type)); check($event['status'] === 1, 'Checker type ' . $type);
}
foreach (array('transportasi', 'kasbon', 'expense', 'nonpo', 'periodik', 'direct_payment') as $type) {
    list($event, $output, $controller) = run_batch2('save_payment', array(), array('tipe' => array($type), 'bank_admin' => array(10)));
    check($event['status'] === 1 && json_decode($output, true)['hasil'] === 1, 'Payment type ' . $type);
    check($event['context']['data']['journal_count'] >= 1, 'Journal count');
    list($event, $output) = run_batch2('save_payment', array('insert_batch_failure' => true, 'status_does_not_fail' => true), array('tipe' => array($type)));
    check($event['status'] === 0 && json_decode($output, true)['hasil'] === 0, 'Journal insert failure');
}
foreach (array('save_approval_cons', 'save_approval', 'save_approval_checker') as $method) {
    list($event) = run_batch2($method, array('missing_table' => 'request_payment')); check($event['status'] === 0, 'Missing request');
}
foreach (array('tr_kasbon', 'users') as $table) {
    list($event) = run_batch2('save_approval_cons', array('missing_table' => $table)); check($event['status'] === 0, 'Missing consultant source');
}
foreach (array(array('expense_cons' => true), array('expense_cons' => true, 'expense_shortfall' => true)) as $options) {
    list($event) = run_batch2('save_approval_cons', $options); check($event['status'] === 1, 'Consultant expense fallback');
}
foreach (array(1, 2, 3, 4, 5) as $write) {
    list($event, $output) = run_batch2('save_approval_cons', array('fail_write' => $write, 'status_does_not_fail' => true));
    check($event['status'] === 0 && json_decode($output, true)['status'] === 0, 'Consultant write ' . $write);
}
foreach (array(array('item' => array()), array('tipe' => 'invalid'), array('item' => array(array())), array('no_doc' => array())) as $post) {
    list($event) = run_batch2('save_approval', array(), $post); check($event['status'] === 0, 'Invalid approval input');
}
foreach (array(array('item' => 'invalid'), array('item' => array(array())), array('no_doc' => '')) as $post) {
    list($event) = run_batch2('save_approval_checker', array(), $post); check($event['status'] === 0, 'Invalid checker input');
}
foreach (array(array('status' => 'invalid'), array('bank_nilai' => array('invalid')), array('bank_nilai' => array()), array('bank_coa' => array())) as $post) {
    list($event) = run_batch2('save_payment', array(), $post); check($event['status'] === 0, 'Invalid payment input');
}
list($event) = run_batch2('save_payment', array('missing_table' => 'payment_approve')); check($event['status'] === 0, 'Missing payment');
foreach (array('save_approval_checker' => array('item' => array()), 'save_payment' => array('status' => array())) as $method => $post) {
    list($event, $output) = run_batch2($method, array(), $post);
    check($event['status'] === 1 && $event['context']['data']['processed_count'] === 0 && $event['context']['data']['outcome'] === 'empty_selection', 'Empty selection compatibility');
}
list($event) = run_batch2('save_payment', array(), array('bank_nilai' => array(0))); check($event['status'] === 1 && $event['context']['data']['processed_count'] === 0, 'Skipped zero value');
list($event) = run_batch2('save_payment', array(), array('bank_nilai' => array(-100))); check($event['status'] === 1, 'Negative payment compatibility');
$_FILES = array('doc_file_PAY-TEST' => array('name' => 'PRIVATE', 'type' => 'application/pdf', 'tmp_name' => 'PRIVATE', 'error' => 0, 'size' => 10));
foreach (array(array(), array('upload_failure' => true)) as $options) {
    list($event, $output, $controller) = run_batch2('save_payment', $options);
    check(json_decode($output, true)['hasil'] === 1 && $controller->db->committed, 'Optional upload changed business response');
    check($event['status'] === (!empty($options['upload_failure']) ? 0 : 1), 'Upload audit status');
}
$_FILES = array();
list($event) = run_batch2('save_payment', array('upload_failure' => true)); check($event['status'] === 1, 'No optional upload is not failure');
list($event) = run_batch2('save_approval', array('expense_shortfall' => true), array('tipe' => 'expense')); check($event['context']['data']['amount'] === 30, 'Expense shortfall');
list($event) = run_batch2('save_approval', array('expense_credit' => true), array('tipe' => 'expense')); check($event['context']['data']['amount'] === -50, 'Expense kasbon credit');
list($event) = run_batch2('save_approval', array(), array('tipe' => 'petty_cash_hutang', 'item' => array(array('no_doc' => 'DOC-TEST')))); check($event['status'] === 1, 'Petty cash item uses document reference');
list($event) = run_batch2('save_payment', array(), array('ids' => null)); check($event['status'] === 1, 'Non-periodic payment does not require source detail ID');
list($event, $output, $controller) = run_batch2('save_payment', array(), array('bank_admin' => array(10)));
$journals = array();
foreach ($controller->db->writes as $write) { if ($write[0] === 'insert_batch') { $journals = $write[1][1]; } }
check($journals[1]['no_perkiraan'] === '6101-ADMIN', 'Kasbon admin journal account changed');
foreach (array('save_approval', 'save_approval_cons') as $method) {
    list($event) = run_batch2($method, array('generate_detail_failure' => true)); check($event['status'] === 0, 'Detail ID generation failure');
}
list($event, $output, $controller) = run_batch2('save_approval', array('existing_payment' => true));
check($event['status'] === 0 && json_decode($output, true)['save'] === false && !$controller->db->writes, 'Duplicate must not create orphan detail');
foreach (array(array('no_doc_sendigs' => array()), array('id_expense' => array()), array('no_doc_sendigs' => '', 'id_expense' => '')) as $post) {
    list($event) = run_batch2('save_approval_cons', array(), $post); check($event['status'] === 0, 'Invalid consultant references');
}
foreach (array('tr_kasbon', 'tr_expense_detail', 'tr_transport', 'tr_non_po_detail', 'tr_pengajuan_rutin_detail', 'tr_direct_payment') as $index => $table) {
    $types = array('kasbon', 'expense', 'transportasi', 'nonpo', 'periodik', 'direct_payment');
    list($event) = run_batch2('save_approval', array('missing_table' => $table), array('tipe' => $types[$index]));
    check($event['status'] === 0, 'Missing approval detail ' . $table);
}
// IDs are bounded without discarding the total document count.
$many = array('status' => array(), 'bank_nilai' => array(), 'bank_admin' => array(), 'no_doc' => array(), 'tipe' => array(), 'nama' => array(), 'ids' => array(), 'keterangan' => array());
for ($i = 0; $i < 101; $i++) {
    $many['status'][] = 'PAY-' . $i; $many['bank_nilai'][] = 100; $many['bank_admin'][] = 0;
    $many['no_doc'][] = 'DOC-' . $i; $many['tipe'][] = 'kasbon'; $many['nama'][] = 'PRIVATE';
    $many['ids'][] = $i; $many['keterangan'][] = 'PRIVATE';
}
list($event) = run_batch2('save_payment', array(), $many);
check($event['status'] === 1 && $event['context']['data']['document_count'] === 101 && count($event['context']['data']['document_ids']) === 100 && $event['context']['data']['processed_count'] === 101, 'Large batch metadata');
foreach ($reads as $method) {
    list($event) = run_batch2($method, array('model_query_failure' => true)); check($event['status'] === 0, 'Failed model query followed by successful query');
}
foreach (array('save_approval', 'save_approval_cons', 'save_approval_checker', 'save_payment') as $method) {
    list($event, $output, $controller) = run_batch2($method);
    $writeCount = $controller->db->writeNumber; $queryCount = $controller->db->queryNumber;
    for ($i = 1; $i <= $writeCount; $i++) {
        list($event, $output, $failed) = run_batch2($method, array('fail_write' => $i, 'status_does_not_fail' => true));
        check($event['status'] === 0 && !$failed->db->committed, $method . ': individual write failure ' . $i);
    }
    for ($i = 1; $i <= $queryCount; $i++) {
        list($event, $output, $failed) = run_batch2($method, array('fail_query' => $i));
        check($event['status'] === 0 && !$failed->db->committed, $method . ': individual query failure ' . $i);
    }
}
foreach (array_merge($reads, array('approval_payment', 'approval_payment_checker', 'save_approval')) as $method) {
    $output = shell_exec('"' . PHP_BINARY . '" "' . __FILE__ . '" --batch2-exit-' . $method);
    check(substr_count($output, 'AUDIT:') === 1, $method . ': shutdown must produce one event');
    $event = json_decode(trim(substr($output, strpos($output, 'AUDIT:') + 6)), true);
    check($event['status'] === 0 && $event['context']['data']['outcome'] === 'interrupted', $method . ': real exit fallback');
    $scenarioCount++;
}
// Rollout remains opt-in per endpoint: instantiating the controller alone must stay inactive.
$events = array(); $scenario = array(); $ci = new Request_payment();
check(!isset($ci->module_audit_recorder) && !$events, 'Constructor activated audit prematurely'); $scenarioCount++;
echo 'PASS Request Payment batch 2: ' . $scenarioCount . " scenarios\n";
