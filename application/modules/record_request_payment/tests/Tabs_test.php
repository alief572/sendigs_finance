<?php
define('BASEPATH', __DIR__);
define('APPPATH', dirname(__DIR__, 3) . '/');
$ci = null;
$events = [];
$options = [];
$checks = 0;
set_error_handler(function ($severity, $message, $file, $line) { throw new ErrorException($message, 0, $severity, $file, $line); });
function check($ok, $message) { global $checks; $checks++; if (!$ok) { throw new RuntimeException($message); } }
function &get_instance() { global $ci; return $ci; }
function log_message($level, $message) {}
function site_url($uri = '') { return 'http://localhost/' . ltrim($uri, '/'); }
function base_url($uri = '') { return site_url($uri); }
function has_permission($permission) { global $options; return !in_array($permission, $options['denied'] ?? [], true); }
function audit_log($message, $status = null, $context = []) {
    global $events, $options;
    $events[] = compact('message', 'status', 'context');
    if (!empty($options['writer_exception'])) { throw new RuntimeException('PRIVATE_WRITER'); }
    return empty($options['writer_failure']);
}
class BF_Model {
    public function __construct() {}
    public function __get($key) { global $ci; return $ci->$key ?? null; }
}
class TabsInput {
    public $values = [];
    public function post($key = null) { return $key === null ? $this->values : ($this->values[$key] ?? null); }
    public function get_post($key) { return $this->post($key); }
}
class TabsSession { public function userdata($key) { return ['username' => 'tester', 'id_user' => 1]; } }
class TabsAuth { public function user_name() { return 'tester'; } public function restrict($p) { if (!has_permission($p)) { throw new RuntimeException('denied'); } } }
class TabsTemplate { public function __call($name, $args) {} }
class TabsResult {
    private $rows;
    public function __construct($rows) { $this->rows = $rows; }
    public function result() { return array_map(function ($r) { return (object) $r; }, $this->rows); }
}
class TabsDb {
    public $calls = [];
    public $queryNumber = 0;
    public $lastError = 0;
    private $table = '';
    private $conditions = [];
    private $search = '';
    private $length = null;
    private $offset = 0;
    public function error() { return ['code' => $this->lastError, 'message' => $this->lastError ? 'PRIVATE_DB' : '']; }
    public function __call($name, $args) {
        $this->calls[] = [$name, $args];
        if ($name === 'from') { $this->table = $args[0]; }
        if ($name === 'where') { $this->conditions[$args[0]] = $args[1]; }
        if ($name === 'like' || $name === 'or_like') { $this->search = $args[1]; }
        if ($name === 'limit') { $this->length = $args[0]; $this->offset = $args[1] ?? 0; }
        if ($name === 'count_all_results' || $name === 'get') {
            global $options;
            $this->queryNumber++;
            $this->lastError = ($options['fail_query'] ?? 0) === $this->queryNumber ? 999 : 0;
            if (!empty($options['query_exception'])) { throw new RuntimeException('PRIVATE_QUERY'); }
            if ($this->lastError) { return $name === 'get' ? false : 0; }
            $rows = $this->rows();
            if ($name === 'count_all_results') { return count($rows); }
            return new TabsResult($this->length === null ? $rows : array_slice($rows, $this->offset, $this->length));
        }
        return $this;
    }
    private function rows() {
        global $options;
        if (!empty($options['empty'])) { return []; }
        $base = ['id_pengajuan' => 10, 'no_pengajuan' => 'PGJ-10', 'company_nama' => 'STM', 'submitted_at' => '2026-10-14 08:00:00',
            'decided_at' => '2026-10-14 10:00:00', 'approver' => 'approver', 'id_dokumen' => 'SOURCE-1', 'kategori' => 'Kasbon',
            'request_by' => 'PRIVATE_PERSON', 'keperluan' => 'PRIVATE_DESCRIPTION', 'dpp' => 100000, 'nilai_ppn' => 11000,
            'nilai_pph' => 2000, 'flag_ppn' => 1, 'flag_pph23' => 1, 'flag_pph21' => 0, 'admin' => 2500, 'dibayarkan' => 106500];
        $base = array_replace($base, $options['record_values'] ?? []);
        $states = strpos($this->table, 'tr_rp_record') !== false
            ? [['done', 0, 'approved'], ['done', 0, 'approved'], ['pending', 0, 'approved'], ['done', 1, 'approved']]
            : [['pending', 0, 'approved'], ['pending', 0, 'approved'], ['done', 0, 'approved'], ['done', 0, 'rejected'], ['pending', 1, 'approved']];
        $rows = [];
        foreach ($states as $i => $state) {
            $r = array_merge($base, ['id' => $i + 1, 'no_dokumen' => 'DOC-' . ($i + 1), 'decision' => $state[2]]);
            if (isset($this->conditions['h.status']) && $this->conditions['h.status'] !== $state[0]) { continue; }
            if (isset($this->conditions['h.deleted']) && (int) $this->conditions['h.deleted'] !== $state[1]) { continue; }
            if ($this->search !== '' && stripos(implode(' ', $r), $this->search) === false) { continue; }
            $rows[] = $r;
        }
        return $rows;
    }
}
class TabsPrintModel {
    public function resetAuditQueriesOk() {}
    public function auditQueriesOk() { global $options; return empty($options['print_failure']); }
    public function build_print_url($r) { global $options; if (!empty($options['print_exception'])) { throw new RuntimeException('PRIVATE_PRINT'); } return base_url('print/' . $r->no_dokumen); }
}
class TabsLoad {
    public function database($name, $return = true) { global $ci; return $ci->db; }
    public function model($models) { global $ci; if (in_array('request_payment/Request_payment_model', (array) $models, true)) { $ci->Request_payment_model = new TabsPrintModel(); } }
    public function library($name) { global $ci; if (!isset($ci->module_audit_recorder)) { $ci->module_audit_recorder = new Module_audit_recorder(); } }
}
class Admin_Controller {
    public function __construct() {
        global $ci; $ci = $this;
        $this->db = new TabsDb(); $this->input = new TabsInput(); $this->load = new TabsLoad();
        $this->session = new TabsSession(); $this->auth = new TabsAuth(); $this->template = new TabsTemplate();
        $this->Approval_request_payment_model = new Approval_request_payment_model();
        $this->Record_request_payment_model = new Record_request_payment_model();
    }
}
require APPPATH . 'libraries/Module_audit_recorder.php';
require APPPATH . 'modules/approval_request_payment/models/Approval_request_payment_model.php';
require APPPATH . 'modules/record_request_payment/models/Record_request_payment_model.php';
require APPPATH . 'modules/approval_request_payment/controllers/Approval_request_payment.php';
require APPPATH . 'modules/record_request_payment/controllers/Record_request_payment.php';
function run_items($class, $method, $opts = [], $post = []) {
    global $options, $events;
    $options = $opts; $events = [];
    $controller = new $class();
    $controller->input->values = array_merge(['draw' => 3, 'start' => 0, 'length' => 25, 'search' => ['value' => '']], $post);
    ob_start();
    $controller->$method();
    $output = ob_get_clean();
    $controller->module_audit_recorder->finish_interrupted();
    $json = json_decode($output, true);
    check(is_array($json) && $json['draw'] === 3, $method . ' JSON contract');
    check(count($events) === 1, $method . ' audit dedup');
    check(strpos(json_encode($events), 'PRIVATE') === false, $method . ' safe audit metadata');
    return [$json, $events[0], $controller];
}
foreach ([['Record_request_payment', 'get_data_record_items', 'Record_Request_Payment.View']] as $target) {
    list($class, $method, $permission) = $target;
    list($json, $event, $controller) = run_items($class, $method);
    check(count($json['data']) === 2 && $json['recordsTotal'] === 2, $method . ' per-document status routing');
    check($json['data'][0]['id_pengajuan'] === $json['data'][1]['id_pengajuan'], $method . ' same PGJ separate rows');
    check((float) $json['data'][0]['dibayarkan'] === 106500.0, $method . ' snapshot amount');
    check($event['status'] === 1, $method . ' success audit');
    list($page) = run_items($class, $method, [], ['start' => 1, 'length' => 1]);
    check(count($page['data']) === 1 && $page['data'][0]['no_dokumen'] === 'DOC-2', $method . ' pagination');
    list($found) = run_items($class, $method, [], ['search' => ['value' => 'DOC-2']]);
    check($found['recordsTotal'] === 2 && $found['recordsFiltered'] === 1 && count($found['data']) === 1, $method . ' search counts');
    list($none) = run_items($class, $method, [], ['search' => ['value' => 'missing']]);
    check($none['recordsFiltered'] === 0 && $none['data'] === [], $method . ' unmatched search');
    list($all) = run_items($class, $method, [], ['length' => -1]);
    check(count($all['data']) === 2, $method . ' all rows');
    list($empty, $event) = run_items($class, $method, ['empty' => true]);
    check($empty['data'] === [] && $event['status'] === 1, $method . ' empty success');
    foreach ([['denied' => [$permission]], ['fail_query' => 1], ['fail_query' => 2], ['fail_query' => 3], ['query_exception' => true], ['print_failure' => true], ['print_exception' => true]] as $failure) {
        list($json, $event, $controller) = run_items($class, $method, $failure);
        check($json['data'] === [] && !empty($json['error']) && $event['status'] === 0, $method . ' failure ' . json_encode($failure));
        check(strpos(json_encode($json), 'PRIVATE') === false, $method . ' failure response privacy');
        if (isset($failure['denied'])) { check($controller->db->queryNumber === 0, $method . ' denied before queries'); }
    }
    foreach (['writer_failure', 'writer_exception'] as $failure) {
        list($json, $event) = run_items($class, $method, [$failure => true]);
        check(count($json['data']) === 2 && $event['status'] === 1, $method . ' writer isolation');
    }
}
echo "PASS Record Request Payment items: {$checks} assertions, real models/controllers/recorder\n";
