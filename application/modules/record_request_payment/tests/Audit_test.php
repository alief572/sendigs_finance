<?php
// Real Record_request_payment controller + real model + real Module_audit_recorder; in-memory DB + PHPExcel fixture.
define('BASEPATH', __DIR__);
define('APPPATH', dirname(__DIR__, 3) . '/');

$events = array();
$scenario = array();
$ci = null;
$child = $argv[1] ?? null;
$scenarioCount = 0;

set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});
function check($condition, $message) {
    if (!$condition) { throw new RuntimeException($message); }
}
function &get_instance() { global $ci; return $ci; }
function log_message($level, $message) {}
function site_url($uri = '') { return 'http://localhost/' . ltrim((string) $uri, '/'); }
function base_url($uri = '') { return 'http://localhost/' . ltrim((string) $uri, '/'); }
function redirect($uri = '') {
    global $scenario;
    $scenario['redirected_to'] = $uri;
}
function show_error($message, $status_code = 500) {
    global $scenario;
    $scenario['shown_error'] = array('message' => $message, 'status_code' => $status_code);
}
function has_permission($perm) {
    global $scenario;
    return empty($scenario['permission_denied']);
}
function audit_log($message, $status = null, $context = array()) {
    global $events, $scenario, $child, $ci;
    $event = compact('message', 'status', 'context');
    $events[] = $event;
    $events[count($events) - 1]['calls_at_write'] = $ci->db->calls;
    if ($child) { echo 'AUDIT:' . json_encode($event) . "\n"; }
    if (!empty($scenario['writer_exception'])) { throw new RuntimeException('PRIVATE_WRITER_ERROR'); }
    return empty($scenario['writer_failure']);
}

class RecordNormalExitSignal extends RuntimeException {}

if (!class_exists('PHPExcel_Style_Fill')) {
    class PHPExcel_Style_Fill { const FILL_SOLID = 'solid'; }
    class PHPExcel_Style_Alignment { const HORIZONTAL_CENTER = 'center'; const HORIZONTAL_RIGHT = 'right'; }
    class PHPExcel_Style_Border { const BORDER_THIN = 'thin'; }
    class PHPExcel_Cell_DataType { const TYPE_STRING = 's'; }
    class PHPExcelFixtureNode {
        public function __call($name, $args) { return $this; }
    }
    class PHPExcel {
        public function getActiveSheet() { return new PHPExcelFixtureNode(); }
    }
    class PHPExcelFixtureWriter {
        public function save($target) {
            global $scenario, $child;
            $scenario['saved_xlsx'] = $target;
            if (empty($child)) {
                throw new RecordNormalExitSignal('saved');
            }
        }
    }
    class PHPExcel_IOFactory {
        public static function createWriter($excel, $type) {
            global $scenario;
            if (!empty($scenario['export_abort'])) { exit; }
            if (!empty($scenario['export_exception'])) { throw new RuntimeException('PRIVATE_EXPORT_ERROR'); }
            return new PHPExcelFixtureWriter();
        }
    }
}

require APPPATH . 'libraries/Module_audit_recorder.php';
if (!class_exists('BF_Model')) {
    class BF_Model {
        public function __construct() {}
        public function __get($name) { global $ci; return $ci->$name ?? null; }
    }
}
require dirname(__DIR__) . '/models/Record_request_payment_model.php';

class RecordFixtureInput {
    public $values = array();
    public function post($key = null) { return $key === null ? $this->values : ($this->values[$key] ?? null); }
    public function get_post($key) { return $this->post($key); }
}
class RecordFixtureSession {
    public $flash = array();
    public function userdata($key) { return array('username' => 'record_viewer', 'id_user' => 12); }
    public function set_flashdata($key, $val) { $this->flash[$key] = $val; }
}
class RecordFixtureAuth {
    public function restrict($permission) {
        global $child;
        if ($child === '--denied') { exit; }
    }
    public function user_name() { return 'record_viewer'; }
}
class RecordFixtureTemplate {
    public function __call($name, $args) {
        global $scenario;
        if ($name === 'render') {
            if (!empty($scenario['render_exit'])) { exit; }
            if (!empty($scenario['render_exception'])) { throw new RuntimeException('PRIVATE_RENDER_ERROR'); }
        }
    }
}
class RecordFixturePrintModel {
    public function build_print_url($item) {
        global $ci;
        $noDoc = isset($item->no_dokumen) ? $item->no_dokumen : (isset($item->no_doc) ? $item->no_doc : 'DOC-01');
        $ci->db->get_where('tr_kasbon', array('no_doc' => $noDoc))->row();
        return 'http://localhost/print/' . $noDoc;
    }
}
class RecordFixtureLoad {
    public function database($name = '', $return = false) { global $ci; return $ci->db; }
    public function model($models) {
        global $ci;
        foreach ((array) $models as $m) {
            if (stripos($m, 'Request_payment_model') !== false && !isset($ci->Request_payment_model)) {
                $ci->Request_payment_model = new RecordFixturePrintModel();
            }
        }
    }
    public function library($name) {
        global $ci;
        if (strtolower((string) $name) === 'module_audit_recorder' && !isset($ci->module_audit_recorder)) {
            $ci->module_audit_recorder = new Module_audit_recorder();
        }
    }
}

class RecordFixtureResult {
    private $table;
    public function __construct($table) { $this->table = (string) $table; }
    public function row() {
        global $scenario;
        if ($this->table === 'SUM(dibayarkan)') {
            return (object) array('t' => 252750);
        }
        if ($this->table === 'tr_rp_pengajuan_h') {
            if (!empty($scenario['missing_header'])) { return null; }
            return (object) array(
                'id' => 21,
                'no_pengajuan' => 'PGJ-2610-0021',
                'company_nama' => 'Sustain',
                'status' => 'done',
                'submitted_at' => '2026-10-11 08:00:00',
                'decided_at' => '2026-10-11 10:00:00',
                'decided_by' => 'approver_user',
                'jumlah_approved' => 2,
            );
        }
        if ($this->table === 'tr_kasbon') {
            return (object) array(
                'id' => 11,
                'nama' => 'PRIVATE_KASBON_NAMA',
                'bank_id' => 'BCA',
                'accnumber' => 'PRIVATE_REK_123456',
                'accname' => 'PRIVATE_ACC_NAME',
                'no_kasbon_consultant' => $scenario['kasbon_consultant_id'] ?? 'KONS-KS-01',
            );
        }
        if ($this->table === 'kons_tr_kasbon_project_header') {
            return (object) array(
                'created_by' => 'USR-1',
                'bank' => 'MANDIRI',
                'bank_number' => 'PRIVATE_KONS_REK_999',
                'bank_account' => 'PRIVATE_KONS_ACC_NAME',
            );
        }
        if ($this->table === 'users') {
            return (object) array('nm_lengkap' => 'PRIVATE_CREATOR_NAME', 'username' => 'PRIVATE_CREATOR_USER');
        }
        if ($this->table === 'tr_pr_non_po') {
            return (object) array('no_pr' => 'PR-001');
        }
        if ($this->table === 'rutin_non_planning_header') {
            return (object) array(
                'bank_name' => 'BNI',
                'bank_account_no' => 'PRIVATE_CASH_REK_777',
                'bank_account_name' => 'PRIVATE_CASH_ACC_NAME',
            );
        }
        return (object) array(
            'id' => 1,
            'nama' => 'PRIVATE_NAMA',
            'bank_id' => 'BRI',
            'accnumber' => 'PRIVATE_REK_000',
            'accname' => 'PRIVATE_ACC_000',
            'bank_name' => 'BRI',
        );
    }
    public function result() {
        global $scenario;
        if (!empty($scenario['empty'])) { return array(); }
        if ($this->table === 'tr_rp_record') {
            if (!empty($scenario['empty_records'])) { return array(); }
            if (isset($scenario['records_rows'])) { return $scenario['records_rows']; }
            return array(
                (object) array(
                    'id' => 201, 'id_pengajuan' => 21, 'id_dokumen' => 1, 'no_dokumen' => 'DPM-01',
                    'kategori' => $scenario['record_kategori_1'] ?? 'Direct Payment',
                    'request_by' => 'PRIVATE_REQ_BY_1', 'company_nama' => 'Sustain',
                    'keperluan' => 'PRIVATE_KEPERLUAN_1',
                    'dpp' => 100000, 'flag_ppn' => 1, 'flag_pph23' => 1, 'flag_pph21' => 0,
                    'nilai_ppn' => 11000, 'nilai_pph' => 2000, 'admin' => 2500, 'dibayarkan' => 106500,
                ),
                (object) array(
                    'id' => 202, 'id_pengajuan' => 21, 'id_dokumen' => 2, 'no_dokumen' => 'KS-02',
                    'kategori' => $scenario['record_kategori_2'] ?? 'Kasbon',
                    'request_by' => 'PRIVATE_REQ_BY_2', 'company_nama' => 'Sustain',
                    'keperluan' => 'PRIVATE_KEPERLUAN_2',
                    'dpp' => 150000, 'flag_ppn' => 0, 'flag_pph23' => 0, 'flag_pph21' => 1,
                    'nilai_ppn' => 0, 'nilai_pph' => 3750, 'admin' => 0, 'dibayarkan' => 146250,
                ),
            );
        }
        if ($this->table === 'tr_direct_payment') {
            return array(
                (object) array(
                    'bank' => 'PERMATA',
                    'bank_number' => 'PRIVATE_DP_REK_888',
                    'bank_account' => 'PRIVATE_DP_ACC_NAME',
                ),
            );
        }
        if ($this->table === 'payment_approve') {
            return array(
                (object) array(
                    'id' => 301, 'no_doc' => 'EXP-001', 'nama' => 'PRIVATE_LEGACY_NAMA_1',
                    'tgl_doc' => '2026-09-01', 'keperluan' => 'PRIVATE_LEGACY_KEPERLUAN_1',
                    'tipe' => 'expense', 'jumlah' => 120000, 'status' => 2,
                    'tanggal' => '2026-09-02', 'tgl_bayar' => '2026-09-03',
                    'ids' => '10', 'doc_file' => 'file1.pdf', 'doc_file_2' => '', 'link_doc' => '',
                ),
                (object) array(
                    'id' => 302, 'no_doc' => 'DPM-002', 'nama' => 'PRIVATE_LEGACY_NAMA_2',
                    'tgl_doc' => '2026-09-05', 'keperluan' => 'PRIVATE_LEGACY_KEPERLUAN_2',
                    'tipe' => 'direct_payment', 'jumlah' => 250000, 'status' => 1,
                    'tanggal' => '2026-09-06', 'tgl_bayar' => '0000-00-00',
                    'ids' => '11', 'doc_file' => '', 'doc_file_2' => '', 'link_doc' => '',
                ),
            );
        }
        if ($this->table === 'tr_rp_pengajuan_h') {
            return array(
                $this->row(),
                (object) array(
                    'id' => 22,
                    'no_pengajuan' => 'PGJ-2610-0022',
                    'company_nama' => 'Vuca',
                    'status' => 'done',
                    'submitted_at' => '2026-10-11 08:30:00',
                    'decided_at' => '2026-10-11 10:30:00',
                    'decided_by' => 'approver_user',
                    'jumlah_approved' => 1,
                ),
            );
        }
        $r = $this->row();
        return $r ? array($r) : array();
    }
    public function num_rows() {
        global $scenario;
        if ($this->table === 'tr_pr_non_po') { return !empty($scenario['is_cash_doc']) ? 1 : 0; }
        return 0;
    }
}

class RecordFixtureDb {
    public $calls = array(); public $queryNumber = 0; public $queryError = 0;
    private $fromTable = '';
    public function __call($name, $args) {
        global $scenario;
        $this->calls[] = $name;
        if ($name === 'from') { $this->fromTable = $args[0] ?? ''; return $this; }
        if ($name === 'count_all_results') {
            $this->queryNumber++;
            $this->queryError = !empty($scenario['query_failure']) || ($scenario['fail_query'] ?? 0) === $this->queryNumber ? 999 : 0;
            return empty($scenario['empty']) ? 2 : 0;
        }
        if (in_array($name, array('get', 'get_where', 'query'), true)) {
            $this->queryNumber++;
            $this->queryError = !empty($scenario['query_failure']) || ($scenario['fail_query'] ?? 0) === $this->queryNumber ? 999 : 0;
            $table = $args[0] ?? $this->fromTable;
            if ($name === 'query') {
                if (stripos($table, 'SUM(dibayarkan)') !== false) { $table = 'SUM(dibayarkan)'; }
                elseif (preg_match('/from\s+([\w.]+)/i', $table, $m)) { $table = $m[1]; }
            }
            $table = trim(preg_replace('/\s+[a-z0-9_]+$/i', '', (string) $table));
            return new RecordFixtureResult($table);
        }
        if ($name === 'error') {
            return array('code' => $this->queryError, 'message' => $this->queryError ? 'PRIVATE_DB_ERROR' : '');
        }
        return $this;
    }
}

class Admin_Controller {
    public function __construct() {
        global $ci;
        $ci = $this;
        $this->db = new RecordFixtureDb();
        $this->load = new RecordFixtureLoad();
        $this->input = new RecordFixtureInput();
        $this->session = new RecordFixtureSession();
        $this->auth = new RecordFixtureAuth();
        $this->template = new RecordFixtureTemplate();
        $this->Record_request_payment_model = new Record_request_payment_model();
        $ref = new ReflectionProperty('Record_request_payment_model', 'consultant');
        $ref->setAccessible(true);
        $ref->setValue($this->Record_request_payment_model, $this->db);
    }
}

require dirname(__DIR__) . '/controllers/Record_request_payment.php';

function run_record($method, $options = array(), $post = array(), $callArgs = array()) {
    global $events, $scenario, $ci, $scenarioCount;
    $events = array();
    $scenario = $options;
    $scenarioCount++;
    $controller = new Record_request_payment();
    $controller->input->values = array_merge(array(
        'draw' => 1, 'start' => 0, 'length' => 25,
        'search' => array('value' => 'PRIVATE_SEARCH_TERM'),
    ), $post);

    $obLevel = ob_get_level();
    ob_start();
    try {
        call_user_func_array(array($controller, $method), $callArgs);
    } catch (RecordNormalExitSignal $e) {
        // Expected normal exit signal from export_excel after save('php://output') in parent test process
    } catch (Throwable $e) {
        if (isset($controller->module_audit_recorder)) { $controller->module_audit_recorder->finish_interrupted(); }
        if (empty($options['expect_exception'])) {
            while (ob_get_level() > $obLevel) { ob_end_clean(); }
            throw $e;
        }
    }
    $output = '';
    while (ob_get_level() > $obLevel) {
        $output = ob_get_clean() . $output;
    }
    if (isset($controller->module_audit_recorder)) { $controller->module_audit_recorder->finish_interrupted(); }
    return array('events' => $events, 'output' => $output, 'db' => $controller->db, 'scenario' => $scenario);
}

function assert_event($result, $method, $status) {
    check(count($result['events']) === 1, $method . ': expected exactly one audit event, got ' . count($result['events']));
    $event = $result['events'][0];
    $ctx = $event['context'];
    check($event['status'] === $status, $method . ': unexpected status ' . var_export($event['status'], true));
    check(($ctx['module'] ?? null) === 'record_request_payment', $method . ': wrong module ' . var_export($ctx['module'] ?? null, true));
    check(($ctx['methode'] ?? null) === $method, $method . ': wrong methode ' . var_export($ctx['methode'] ?? null, true));
    $serialized = json_encode($event);
    foreach (array('PRIVATE_SEARCH_TERM', 'PRIVATE_DB_ERROR', 'PRIVATE_RENDER_ERROR', 'PRIVATE_WRITER_ERROR', 'PRIVATE_EXPORT_ERROR', 'PRIVATE_REQ_BY', 'PRIVATE_KEPERLUAN', 'PRIVATE_CREATOR_NAME', 'PRIVATE_KASBON_NAMA', 'PRIVATE_REK_', 'PRIVATE_ACC_', 'PRIVATE_KONS_', 'PRIVATE_CASH_', 'PRIVATE_DP_', 'PRIVATE_LEGACY_') as $secret) {
        check(strpos($serialized, $secret) === false, $method . ': leaked ' . $secret);
    }
    return $event;
}

if ($child === '--denied') { run_record('index', array('permission_denied' => true)); exit; }
if ($child === '--abort-read') { run_record('detail', array('render_exit' => true), array(), array(21)); exit; }
if ($child === '--export-abort') { run_record('export_excel', array('export_abort' => true), array(), array(21)); exit; }
if ($child === '--export-ok') { run_record('export_excel', array(), array(), array(21)); exit; }

// Opt-in boundary check: instantiating Record_request_payment does not auto-start audit
$events = array();
$instance = new Record_request_payment();
check(!isset($instance->module_audit_recorder) && count($events) === 0, 'constructor must not emit audit event');

// 1. index
$r = run_record('index');
assert_event($r, 'index', 1);
$r = run_record('index', array('writer_failure' => true));
assert_event($r, 'index', 1);
$r = run_record('index', array('writer_exception' => true));
assert_event($r, 'index', 1);
$r = run_record('index', array('render_exception' => true, 'expect_exception' => true));
$ev = assert_event($r, 'index', 0);
check(($ev['context']['data']['reason'] ?? null) === 'render_exception', 'index render_exception reason');

// 2. get_data_record
$r = run_record('get_data_record');
$ev = assert_event($r, 'get_data_record', 1);
check(($ev['context']['data']['row_count'] ?? null) === 2, 'get_data_record row_count');
check(($ev['context']['data']['records_total'] ?? null) === 2, 'get_data_record records_total');
check(($ev['context']['data']['records_filtered'] ?? null) === 2, 'get_data_record records_filtered');
check(($ev['context']['data']['search_present'] ?? null) === true, 'get_data_record search_present');
$json = json_decode($r['output'], true);
check(is_array($json) && count($json['data'] ?? array()) === 2, 'get_data_record json data');

$r = run_record('get_data_record', array('empty' => true), array('search' => array('value' => '')));
$ev = assert_event($r, 'get_data_record', 1);
check(($ev['context']['data']['row_count'] ?? null) === 0, 'get_data_record empty row_count');
check(($ev['context']['data']['search_present'] ?? null) === false, 'get_data_record empty search_present');

for ($fq = 1; $fq <= 5; $fq++) {
    $r = run_record('get_data_record', array('fail_query' => $fq));
    $ev = assert_event($r, 'get_data_record', 0);
    check(($ev['context']['data']['reason'] ?? null) === 'query_failure', 'get_data_record fail_query ' . $fq);
}

// 3. get_data_record_legacy
$r = run_record('get_data_record_legacy');
$ev = assert_event($r, 'get_data_record_legacy', 1);
check(($ev['context']['data']['row_count'] ?? null) === 2, 'get_data_record_legacy row_count');
check(($ev['context']['data']['records_total'] ?? null) === 2, 'get_data_record_legacy records_total');
check(($ev['context']['data']['records_filtered'] ?? null) === 2, 'get_data_record_legacy records_filtered');
check(($ev['context']['data']['search_present'] ?? null) === true, 'get_data_record_legacy search_present');
$json = json_decode($r['output'], true);
check(is_array($json) && count($json['data'] ?? array()) === 2, 'get_data_record_legacy json data');

$r = run_record('get_data_record_legacy', array('empty' => true), array('search' => array('value' => '')));
$ev = assert_event($r, 'get_data_record_legacy', 1);
check(($ev['context']['data']['row_count'] ?? null) === 0, 'get_data_record_legacy empty row_count');

for ($fq = 1; $fq <= 5; $fq++) {
    $r = run_record('get_data_record_legacy', array('fail_query' => $fq));
    $ev = assert_event($r, 'get_data_record_legacy', 0);
    check(($ev['context']['data']['reason'] ?? null) === 'query_failure', 'get_data_record_legacy fail_query ' . $fq);
}

// 4. detail
$r = run_record('detail', array(), array(), array(21));
$ev = assert_event($r, 'detail', 1);
check(($ev['context']['data']['id_pengajuan'] ?? null) === 21, 'detail id_pengajuan');
check(($ev['context']['data']['no_pengajuan'] ?? null) === 'PGJ-2610-0021', 'detail no_pengajuan');
check(($ev['context']['data']['row_count'] ?? null) === 2, 'detail row_count');

$r = run_record('detail', array('missing_header' => true), array(), array(21));
$ev = assert_event($r, 'detail', 0);
check(($ev['context']['data']['reason'] ?? null) === 'not_found', 'detail not_found reason');
check(strpos($r['scenario']['redirected_to'] ?? '', 'record_request_payment') !== false, 'detail redirects on not_found');

$r = run_record('detail', array('fail_query' => 1), array(), array(21));
$ev = assert_event($r, 'detail', 0);
check(($ev['context']['data']['reason'] ?? null) === 'query_failure', 'detail header query_failure');

for ($fq = 2; $fq <= 10; $fq++) {
    $r = run_record('detail', array('fail_query' => $fq), array(), array(21));
    $ev = assert_event($r, 'detail', 0);
    check(($ev['context']['data']['reason'] ?? null) === 'query_failure', 'detail fail_query ' . $fq);
}

$r = run_record('detail', array('render_exception' => true, 'expect_exception' => true), array(), array(21));
$ev = assert_event($r, 'detail', 0);
check(($ev['context']['data']['reason'] ?? null) === 'render_exception', 'detail render_exception');

// Test detail across bank resolution categories (Transport, Expense, Periodik, Cash, fallback)
foreach (array('Transport', 'Expense', 'Periodik', 'Cash', 'Petty Cash') as $kat) {
    $r = run_record('detail', array('record_kategori_1' => $kat), array(), array(21));
    assert_event($r, 'detail', 1);
    $r = run_record('detail', array('record_kategori_1' => $kat, 'fail_query' => 3), array(), array(21));
    assert_event($r, 'detail', 0);
}

// 5. export_excel
$r = run_record('export_excel', array(), array(), array(21));
$ev = assert_event($r, 'export_excel', 1);
check(($ev['context']['data']['id_pengajuan'] ?? null) === 21, 'export_excel id_pengajuan');
check(($ev['context']['data']['no_pengajuan'] ?? null) === 'PGJ-2610-0021', 'export_excel no_pengajuan');
check(($ev['context']['data']['format'] ?? null) === 'xlsx', 'export_excel format');
check(($ev['context']['data']['row_count'] ?? null) === 2, 'export_excel row_count');
check(($ev['context']['data']['outcome'] ?? null) === 'exported', 'export_excel outcome');
check(($r['scenario']['saved_xlsx'] ?? null) === 'php://output', 'export_excel saved to php://output');

$r = run_record('export_excel', array('missing_header' => true), array(), array(21));
$ev = assert_event($r, 'export_excel', 0);
check(($ev['context']['data']['reason'] ?? null) === 'not_found', 'export_excel missing_header reason');
check(($r['scenario']['shown_error']['status_code'] ?? null) === 404, 'export_excel missing_header 404');

$r = run_record('export_excel', array('empty_records' => true), array(), array(21));
$ev = assert_event($r, 'export_excel', 0);
check(($ev['context']['data']['reason'] ?? null) === 'not_found', 'export_excel empty_records reason');
check(($r['scenario']['shown_error']['status_code'] ?? null) === 404, 'export_excel empty_records 404');

$r = run_record('export_excel', array('fail_query' => 1), array(), array(21));
$ev = assert_event($r, 'export_excel', 0);
check(($ev['context']['data']['reason'] ?? null) === 'query_failure', 'export_excel header query_failure');

$r = run_record('export_excel', array('fail_query' => 2), array(), array(21));
$ev = assert_event($r, 'export_excel', 0);
check(($ev['context']['data']['reason'] ?? null) === 'query_failure', 'export_excel records query_failure');

for ($fq = 3; $fq <= 8; $fq++) {
    $r = run_record('export_excel', array('fail_query' => $fq), array(), array(21));
    $ev = assert_event($r, 'export_excel', 0);
    check(($ev['context']['data']['reason'] ?? null) === 'query_failure', 'export_excel fail_query ' . $fq);
}

$r = run_record('export_excel', array('export_exception' => true, 'expect_exception' => true), array(), array(21));
$ev = assert_event($r, 'export_excel', 0);
check(($ev['context']['data']['reason'] ?? null) === 'export_exception', 'export_excel exception reason');

// 6. Real child subprocesses for permission exit, read shutdown, export abort, and export normal exit
$php = PHP_BINARY;
$self = escapeshellarg(__FILE__);
$deniedOut = (string) shell_exec(escapeshellarg($php) . ' ' . $self . ' --denied');
preg_match_all('/^AUDIT:(.+)$/m', $deniedOut, $dm);
check(count($dm[1]) === 1, '--denied: expected 1 audit event from permission exit');
$dev = json_decode($dm[1][0], true);
check(($dev['status'] ?? null) === 0 && ($dev['context']['data']['outcome'] ?? null) === 'interrupted', '--denied: expected interrupted');
$scenarioCount++;

foreach (array('--abort-read' => 'detail', '--export-abort' => 'export_excel') as $flag => $expectedMethod) {
    $out = (string) shell_exec(escapeshellarg($php) . ' ' . $self . ' ' . $flag);
    preg_match_all('/^AUDIT:(.+)$/m', $out, $m);
    check(count($m[1]) === 1, $flag . ': expected 1 audit event from real shutdown');
    $ev = json_decode($m[1][0], true);
    check(($ev['status'] ?? null) === 0, $flag . ': expected status 0');
    check(($ev['context']['methode'] ?? null) === $expectedMethod, $flag . ': wrong methode');
    check(($ev['context']['data']['outcome'] ?? null) === 'interrupted', $flag . ': expected interrupted');
    $scenarioCount++;
}

$exportOkOut = (string) shell_exec(escapeshellarg($php) . ' ' . $self . ' --export-ok 2>&1');
preg_match_all('/^AUDIT:(.+)$/m', $exportOkOut, $m);
check(count($m[1]) === 1, '--export-ok: expected exactly 1 audit event (no duplicate on exit)');
$ev = json_decode($m[1][0], true);
check(($ev['status'] ?? null) === 1, '--export-ok: expected status 1');
check(($ev['context']['methode'] ?? null) === 'export_excel', '--export-ok: wrong methode');
check(($ev['context']['data']['outcome'] ?? null) === 'exported', '--export-ok: wrong outcome');
check(($ev['context']['data']['row_count'] ?? null) === 2, '--export-ok: wrong row_count');
$scenarioCount++;

echo "PASS Record Request Payment: {$scenarioCount} scenarios, real controller/recorder and model\n";
