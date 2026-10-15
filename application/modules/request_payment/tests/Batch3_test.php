<?php
// Real controller and recorder; in-memory persistence and rendering fixtures.
define('AUDIT_FIXTURE_ONLY', true);
define('DBACC', 'fixture_accounting');
require __DIR__ . '/Audit_test.php';
function has_permission($permission) { return true; }
function base_url($path = '') { return '/fixture/' . $path; }
class BF_Model {}
require dirname(__DIR__) . '/models/Request_payment_model.php';

class Batch3Result {
    private $table;
    public function __construct($table) { $this->table = $table; }
    public function row_array() {
        global $scenario;
        if (isset($scenario['missing_table']) && $scenario['missing_table'] === $this->table) { return array(); }
        return array('id' => 1, 'no_doc' => 'DOC-TEST', 'tipe' => $scenario['source_type'] ?? 'kasbon',
            'nama' => 'PRIVATE', 'nm_supplier' => 'PRIVATE', 'no_po' => '', 'invoice_no' => 'INV-TEST',
            'kode' => 'IDR', 'no_perkiraan' => '1101-01', 'coa' => '5101-01', 'curr' => 'IDR',
            'transfer_tanggal' => '2026-10-11', 'expense' => $scenario['expense'] ?? 100,
            'kasbon' => $scenario['kasbon'] ?? 100, 'id_kasbon' => null, 'project' => '',
            'exp_inv_po' => 0, 'exp_pib' => 0, 'deskripsi' => 'PRIVATE', 'coa_bank' => '1101-01',
            'created_by' => 7, 'pay_by' => 7, 'tgl_pengajuan' => '2026-10-11', 'tgl_pembayaran' => '',
            'ids' => 1, 'sts_reject' => 0, 'sts_reject_manage' => 0, 'reject_reason' => 'PRIVATE',
            'tgl_doc' => '2026-10-11', 'keperluan' => 'PRIVATE', 'jumlah' => 100,
            'bank_id' => 1, 'accnumber' => 'PRIVATE', 'accname' => 'PRIVATE');
    }
    public function row() { $row = $this->row_array(); return $row ? (object) $row : null; }
    public function result() { global $scenario; $row = $this->row(); return !$row || !empty($scenario['empty']) ? array() : array($row); }
    public function num_rows() { global $scenario; return $this->table === 'tr_jurnal' && !empty($scenario['existing_journal']) ? 1 : 0; }
}
class Batch3Db extends FixtureDb {
    public $queryNumber = 0; public $queryError = 0; public $writeNumber = 0;
    public $writes = array(); public $committed = false;
    public function __call($name, $args) {
        global $scenario;
        if (in_array($name, array('get', 'get_where', 'query'), true)) {
            $this->calls[] = $name; $this->queryNumber++;
            $this->queryError = !empty($scenario['query_failure']) || ($scenario['fail_query'] ?? 0) === $this->queryNumber ? 999 : 0;
            $table = $args[0] ?? '';
            if ($name === 'query') {
                if (stripos($table, 'UPDATE ') === 0) { return $this->write($name, $args); }
                preg_match('/from\s+([\w.]+)/i', $table, $match); $table = $match[1] ?? '';
            }
            return new Batch3Result($table);
        }
        if ($name === 'error') { return array('code' => $this->queryError); }
        if (in_array($name, array('insert', 'update', 'delete'), true)) { $this->calls[] = $name; return $this->write($name, $args); }
        if ($name === 'trans_commit') {
            if (!empty($scenario['abort_commit'])) { exit; }
            $result = parent::__call($name, $args); $this->committed = $result; return $result;
        }
        if ($name === 'trans_rollback') { $this->writes = array(); $this->committed = false; }
        return parent::__call($name, $args);
    }
    private function write($name, $args) {
        global $scenario;
        $this->writeNumber++;
        if (!empty($scenario[$name . '_failure']) || ($scenario['fail_write'] ?? 0) === $this->writeNumber) { $this->failed = true; return false; }
        $this->writes[] = array($name, $args); return true;
    }
}
class Batch3Model extends FixtureModel {
    public function __call($name, $args) {
        global $scenario, $ci;
        if (!empty($scenario['model_exception'])) { throw new RuntimeException('PRIVATE'); }
        // Simulate persistence inside the model, then its existing response/render.
        $ci->db->get('request_payment');
        if ($name === 'search_payment_list') { $ci->template->render('search_payment_list'); echo 'SEARCH'; return; }
        if (!empty($scenario['empty'])) { return array(); }
        $row = (new Batch3Result('request_payment'))->row_array();
        $row += array('ids' => 1, 'sts_reject' => 0, 'sts_reject_manage' => 0, 'reject_reason' => 'PRIVATE',
            'tgl_doc' => '2026-10-11', 'keperluan' => 'PRIVATE', 'jumlah' => 100,
            'bank_id' => 1, 'accnumber' => 'PRIVATE', 'accname' => 'PRIVATE');
        return array((object) $row);
    }
}
class Batch3AllModel extends FixtureAllModel {
    public function GetCoaCombo() { global $ci; $ci->db->get('coa'); return array(); }
    public function dataUpdate($table, $data, $where) { global $ci, $scenario; return $ci->db->update($table, $data, $where) ? (!empty($scenario['unchanged']) ? 0 : 1) : -1; }
}
class Batch3JournalModel {
    public function get_no_buk($branch) { global $ci, $scenario; $ci->db->get('pastibisa_tb_cabang'); return !empty($scenario['generate_failure']) ? null : 'JV-TEST'; }
}
class Batch3Auth extends FixtureAuth { public function user_id() { return 7; } }
class Batch3Session extends FixtureSession { public $userdata = array(); }
class Batch3Template extends FixtureTemplate {
    public function __call($name, $args) { global $scenario; if ($name === 'render' && !empty($scenario['render_exit'])) { exit; } return parent::__call($name, $args); }
}
function run_batch3($method, $options = array(), $post = array(), $id = 'JV-TEST') {
    global $events, $scenario, $ci, $scenarioCount;
    $events = array(); $scenario = $options; $scenarioCount++;
    $ci = new Request_payment(); $ci->db = new Batch3Db();
    $ci->Request_payment_model = new Batch3Model(); $ci->All_model = new Batch3AllModel();
    $ci->Jurnal_model = new Batch3JournalModel(); $ci->auth = new Batch3Auth();
    $ci->session = new Batch3Session(); $ci->template = new Batch3Template();
    if (($method === 'search_payment_list' || !empty($options['real_model'])) && empty($options['model_exception'])) {
        $ci->Request_payment_model = (new ReflectionClass('Request_payment_model'))->newInstanceWithoutConstructor();
        $ci->Request_payment_model->db = $ci->db; $ci->Request_payment_model->template = $ci->template;
    }
    $ci->input->values = array_merge(array('id' => 1, 'no_doc' => 'DOC-TEST', 'tipe' => 'kasbon',
        'tingkat_approval' => '1', 'reject_reason' => 'PRIVATE', 'item' => array(array('id' => 1)),
        'tgl_from' => '2026-10-01', 'tgl_to' => '2026-10-11', 'bank' => 'PRIVATE',
        'from_date' => '2026-10-01', 'to_date' => '2026-10-11', 'vendor' => '', 'actived_tab' => 'all'), $post);
    if ($method === 'jurnal_save') {
        $ci->input->values = array_merge($ci->input->values, array('id' => array(1), 'no_perkiraan' => array('1101-01'),
            'keterangan' => array('PRIVATE'), 'debet' => array(100), 'kredit' => array(100),
            'tanggal' => '2026-10-11', 'tipe' => 'BUK', 'no_reff' => 'REF-TEST'), $post);
    }
    ob_start();
    try { in_array($method, array('view_jurnal', 'edit_jurnal'), true) ? $ci->$method($id) : $ci->$method(); }
    catch (Throwable $error) {
        if (isset($ci->module_audit_recorder)) { $ci->module_audit_recorder->finish_interrupted(); }
        if (empty($options['model_exception']) && empty($options['render_exception'])) { ob_end_clean(); throw $error; }
    }
    $output = ob_get_clean();
    if (isset($ci->module_audit_recorder)) { $ci->module_audit_recorder->finish_interrupted(); }
    check(count($events) === 1, $method . ': exactly one audit');
    $event = $events[0];
    check($event['context']['module'] === 'request_payment' && $event['context']['methode'] === $method, 'Endpoint identity');
    check(strpos(json_encode($events), 'PRIVATE') === false, 'Private metadata leaked');
    return array($event, $output, $ci);
}
if ($child && strpos($child, '--batch3-exit-') === 0) {
    run_batch3(substr($child, strlen('--batch3-exit-')), array('render_exit' => true, 'abort_commit' => true)); exit;
}
$reads = array('view_jurnal', 'edit_jurnal', 'list_return', 'list_return_approval', 'search_payment_list', 'search_req_payment');
foreach ($reads as $method) {
    foreach (array(array(), array('empty' => true), array('writer_failure' => true), array('writer_exception' => true)) as $options) {
        list($event) = run_batch3($method, $options); check($event['status'] === 1, $method . ': successful read');
    }
    list($event) = run_batch3($method, array('query_failure' => true)); check($event['status'] === 0, $method . ': query failure');
}
foreach (array('view_jurnal', 'edit_jurnal', 'list_return', 'list_return_approval', 'search_payment_list') as $method) {
    list($event) = run_batch3($method, array('render_exception' => true)); check($event['status'] === 0 && $event['context']['data']['outcome'] === 'interrupted', 'Render interruption');
}
foreach (array('view_jurnal', 'edit_jurnal', 'search_payment_list', 'search_req_payment') as $method) {
    list($event) = run_batch3($method, array('model_exception' => true)); check($event['status'] === 0, 'Model interruption');
}
$writes = array('jurnal_save' => 'save', 'reject_approval' => 'save', 'approve_pengembalian_expense' => 'status', 'reject_pengembalian_expense' => 'status');
foreach ($writes as $method => $key) {
    list($event, $output, $controller) = run_batch3($method);
    check($event['status'] === 1 && json_decode($output, true)[$key] == 1 && $controller->db->committed, $method . ': successful write');
    check(in_array('trans_commit', $event['calls_at_write'], true), 'Audit before commit');
    foreach (array('trans_begin_failure', 'transaction_failure', 'trans_commit_failure', 'update_failure') as $failure) {
        list($event, $output, $controller) = run_batch3($method, array($failure => true));
        check($event['status'] === 0 && json_decode($output, true)[$key] == 0 && !$controller->db->committed, $method . ': ' . $failure);
    }
    foreach (array('writer_failure', 'writer_exception') as $failure) {
        list($event, $output, $controller) = run_batch3($method, array($failure => true));
        check($event['status'] === 1 && json_decode($output, true)[$key] == 1 && $controller->db->committed, 'Writer changed business result');
    }
}
foreach ($reads as $method) {
    list($event, $output, $controller) = run_batch3($method);
    for ($query = 1; $query <= $controller->db->queryNumber; $query++) {
        list($event) = run_batch3($method, array('fail_query' => $query));
        check($event['status'] === 0, $method . ': earlier query failure ' . $query . ' must survive later successful queries');
    }
    if ($method !== 'search_payment_list') {
        list($event) = run_batch3($method, array('empty' => true));
        check($event['context']['data']['row_count'] === 0, 'Empty row count');
    }
}
foreach ($writes as $method => $key) {
    list($event, $output, $controller) = run_batch3($method);
    for ($write = 1; $write <= $controller->db->writeNumber; $write++) {
        list($event, $output, $failed) = run_batch3($method, array('fail_write' => $write, 'status_does_not_fail' => true));
        check($event['status'] === 0 && json_decode($output, true)[$key] == 0 && !$failed->db->committed, $method . ': explicit write failure ' . $write);
    }
    for ($query = 1; $query <= $controller->db->queryNumber; $query++) {
        list($event, $output, $failed) = run_batch3($method, array('fail_query' => $query, 'status_does_not_fail' => true));
        check($event['status'] === 0 && json_decode($output, true)[$key] == 0 && !$failed->db->committed, $method . ': query failure ' . $query);
    }
}
foreach (array('transportasi', 'kasbon', 'expense', 'periodik', 'nonpo', 'direct_payment') as $type) {
    foreach (array('1', '2') as $level) {
        $post = array('tipe' => $type, 'tingkat_approval' => $level);
        list($event, $output, $controller) = run_batch3('reject_approval', array('source_type' => $type), $post);
        check($event['status'] === 1 && json_decode($output, true)['save'] === 1, 'Rejection type/level result');
        check($event['context']['data']['outcome'] === 'rejected', 'Successful rejection is audit success');
        foreach ($controller->db->writes as $write) {
            if ($write[0] === 'update') { check(isset($write[1][1]['reject_reason']) || $write[1][0] === 'tr_transport' || $write[1][0] === 'tr_expense_detail', 'Preserve rejection reason in business data'); }
        }
        for ($index = 1; $index <= $controller->db->writeNumber; $index++) {
            list($event, $output, $failed) = run_batch3('reject_approval', array('source_type' => $type, 'fail_write' => $index, 'status_does_not_fail' => true), $post);
            check($event['status'] === 0 && !$failed->db->committed && json_decode($output, true)['save'] == 0, 'Rejection source/delete failure');
        }
    }
}
foreach (array(array('no_doc' => ''), array('no_doc' => array()), array('tingkat_approval' => 3),
    array('reject_reason' => array()), array('tipe' => 'expense')) as $post) {
    list($event, $output, $controller) = run_batch3('reject_approval', array(), $post);
    check($event['status'] === 0 && json_decode($output, true)['save'] === 0 && !$controller->db->committed, 'Invalid rejection preserves numeric save');
}
foreach (array(array(), 'invalid', array(array()), array(array('id' => array()))) as $items) {
    list($event) = run_batch3('reject_approval', array('source_type' => 'periodik'), array('tipe' => 'periodik', 'item' => $items));
    check($event['status'] === 0, 'Invalid periodic selection');
}
foreach (array('request_payment' => 'reject_approval', 'tr_pengembalian_expense' => 'approve_pengembalian_expense') as $table => $method) {
    list($event, $output, $controller) = run_batch3($method, array('missing_table' => $table));
    check($event['status'] === 0 && !$controller->db->committed, 'Missing source');
}
foreach (array('approve_pengembalian_expense', 'reject_pengembalian_expense') as $method) {
    foreach (array('', array()) as $id) { list($event) = run_batch3($method, array(), array('id' => $id)); check($event['status'] === 0, 'Invalid return ID'); }
    list($event) = run_batch3($method, array('missing_table' => 'tr_pengembalian_expense')); check($event['status'] === 0, 'Return source missing');
}
foreach (array('tr_expense', 'tr_expense_detail') as $table) {
    list($event, $output, $controller) = run_batch3('approve_pengembalian_expense', array('missing_table' => $table));
    check($event['status'] === 0 && !$controller->db->committed && json_decode($output, true)['status'] === 0, 'Missing journal source');
}
foreach (array(array('kasbon' => 150), array('kasbon' => 50), array('kasbon' => 100),
    array('kasbon' => 0, 'expense' => 0), array('existing_journal' => true)) as $options) {
    list($event, $output, $controller) = run_batch3('approve_pengembalian_expense', $options);
    check($event['status'] === 1 && $controller->db->committed, 'Return journal variation');
    $journalRows = array_values(array_filter($controller->db->writes, function ($write) { return $write[0] === 'insert' && $write[1][0] === 'tr_jurnal'; }));
    check($event['context']['data']['journal_count'] === count($journalRows), 'Journal count');
    if ($journalRows) {
        $debit = $credit = 0;
        foreach ($journalRows as $write) { $debit += $write[1][1]['debit']; $credit += $write[1][1]['kredit']; }
        check($debit === $credit, 'Existing journal formula remains balanced');
    }
}
foreach (array(array('id' => array()), array('id' => 'invalid'), array('debet' => array()),
    array('debet' => array('invalid')), array('tanggal' => array()), array('tanggal' => ''), array('no_reff' => array())) as $post) {
    list($event, $output, $controller) = run_batch3('jurnal_save', array(), $post);
    check($event['status'] === 0 && json_decode($output, true)['save'] === false && !$controller->db->committed, 'Invalid journal input');
}
list($event) = run_batch3('jurnal_save', array('generate_failure' => true)); check($event['status'] === 0, 'Journal number unavailable');
list($event) = run_batch3('jurnal_save', array('unchanged' => true)); check($event['status'] === 1, 'Zero affected update is not query failure');
foreach (array('view_jurnal', 'edit_jurnal') as $method) {
    foreach (array('', array()) as $id) { list($event) = run_batch3($method, array(), array(), $id); check($event['status'] === 0, 'Invalid journal ID'); }
}
foreach (array(null, 'transport', 'kasbon', 'expense', 'pembayaran_po', 'periodik', 'direct_payment') as $tab) {
    foreach (array(array('real_model' => true), array('real_model' => true, 'empty' => true)) as $options) {
        list($event, $output, $controller) = run_batch3('search_req_payment', $options, array('actived_tab' => $tab));
        check($event['status'] === 1 && is_string(json_decode($output, true)['hasil']), 'Real model tab response');
        check($event['context']['data']['row_count'] === (!empty($options['empty']) || $tab === 'expense' || $tab === 'pembayaran_po' ? 0 : 1), 'Filtered row count: ' . json_encode(array($tab, $options, $event['context']['data'])));
        for ($query = 1; $query <= $controller->db->queryNumber; $query++) {
            list($failed) = run_batch3('search_req_payment', array_merge($options, array('fail_query' => $query)), array('actived_tab' => $tab));
            check($failed['status'] === 0, 'Real model query error');
        }
    }
}
list($event, $output, $controller) = run_batch3('jurnal_save', array('missing_table' => 'jurnal'));
check($event['status'] === 0 && !$controller->db->committed && json_decode($output, true)['save'] === false, 'Missing source journal must not produce accounting entries');
foreach (array('list_return', 'search_payment_list', 'jurnal_save', 'approve_pengembalian_expense') as $method) {
    $output = shell_exec('"' . PHP_BINARY . '" "' . __FILE__ . '" --batch3-exit-' . $method);
    check(substr_count($output, 'AUDIT:') === 1, 'Real shutdown exactly one event');
    $event = json_decode(trim(substr($output, strpos($output, 'AUDIT:') + 6)), true);
    check($event['status'] === 0 && $event['context']['data']['outcome'] === 'interrupted', 'Real shutdown interrupted result');
}
echo 'PASS Request Payment batch 3: ' . ($scenarioCount + 4) . " scenarios, real controller/recorder and search model\n";
