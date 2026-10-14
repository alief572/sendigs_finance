<?php
define('BASEPATH', dirname(__DIR__, 4) . '/system/');
define('APPPATH', dirname(__DIR__, 3) . '/');
$checks = 0;
set_error_handler(function ($severity, $message) { throw new ErrorException($message, 0, $severity); });
function log_message($level, $message) {}
function show_error($message) { throw new RuntimeException($message); }
function load_class($name, $directory) { return null; }
function check($ok, $message) { global $checks; $checks++; if (!$ok) { throw new RuntimeException($message); } }
class BF_Model { public function __construct() {} }
class QueryInput { public $values = []; public function post() { return $this->values; } }
// Production uses mysqli's numeric error code; PDO reports success as SQLSTATE 00000.
class QueryDbAdapter {
    private $db;
    public function __construct($db) { $this->db = $db; }
    public function error() { $error = $this->db->error(); if ($error['code'] === '00000') { $error['code'] = 0; } return $error; }
    public function __call($name, $args) { $result = call_user_func_array([$this->db, $name], $args); return $result === $this->db ? $this : $result; }
}
require BASEPATH . 'database/DB.php';
require APPPATH . 'modules/approval_request_payment/models/Approval_request_payment_model.php';
require APPPATH . 'modules/record_request_payment/models/Record_request_payment_model.php';
require APPPATH . 'modules/request_payment/models/Request_payment_model.php';
$db = DB(['dbdriver' => 'pdo', 'subdriver' => 'sqlite', 'dsn' => 'sqlite::memory:', 'db_debug' => false], true);
$db->query('CREATE TABLE tr_rp_pengajuan_h (id INTEGER PRIMARY KEY, no_pengajuan TEXT, company_nama TEXT, status TEXT, deleted INTEGER, submitted_at TEXT, decided_at TEXT, approver TEXT)');
$fields = 'id INTEGER PRIMARY KEY, id_pengajuan INTEGER, no_dokumen TEXT, kategori TEXT, request_by TEXT, keperluan TEXT, dpp NUMERIC, flag_ppn INTEGER, flag_pph23 INTEGER, flag_pph21 INTEGER, nilai_ppn NUMERIC, nilai_pph NUMERIC, admin INTEGER, dibayarkan NUMERIC';
$db->query('CREATE TABLE tr_rp_pengajuan_d (' . $fields . ', id_dokumen TEXT, decision TEXT)');
$db->query('CREATE TABLE tr_rp_record (' . $fields . ', no_pengajuan TEXT, company_nama TEXT)');
foreach ([[1, 'pending', 0], [2, 'done', 0], [3, 'pending', 1]] as $h) {
    $db->insert('tr_rp_pengajuan_h', ['id' => $h[0], 'no_pengajuan' => 'PGJ-' . $h[0], 'company_nama' => 'STM', 'status' => $h[1],
        'deleted' => $h[2], 'submitted_at' => '2026-10-14 08:00:00', 'decided_at' => '2026-10-14 10:00:00', 'approver' => 'approver']);
}
foreach ([[1, 1, 'approved'], [2, 1, 'approved'], [3, 2, 'approved'], [4, 2, 'rejected'], [5, 3, 'approved']] as $d) {
    $item = ['id' => $d[0], 'id_pengajuan' => $d[1], 'no_dokumen' => 'DOC-' . $d[0], 'kategori' => 'Kasbon', 'request_by' => 'pemohon',
        'keperluan' => 'keperluan', 'dpp' => 100000, 'flag_ppn' => 1, 'flag_pph23' => 1, 'flag_pph21' => 0,
        'nilai_ppn' => 11000, 'nilai_pph' => 2000, 'admin' => 2500, 'dibayarkan' => 106500];
    $db->insert('tr_rp_pengajuan_d', $item + ['id_dokumen' => 'SOURCE-' . $d[0], 'decision' => $d[2]]);
    if ($d[0] === 3 || $d[0] === 5) {
        $db->insert('tr_rp_record', $item + ['no_pengajuan' => 'PGJ-' . $d[1], 'company_nama' => 'STM']);
    }
}
$input = new QueryInput();
$modelDb = new QueryDbAdapter($db);
$approval = (new ReflectionClass('Approval_request_payment_model'))->newInstanceWithoutConstructor(); $approval->db = $modelDb; $approval->input = $input;
$record = (new ReflectionClass('Record_request_payment_model'))->newInstanceWithoutConstructor(); $record->db = $modelDb; $record->input = $input;
$request = (new ReflectionClass('Request_payment_model'))->newInstanceWithoutConstructor(); $request->db = $db;
$input->values = ['draw' => 1, 'start' => 0, 'length' => 1, 'search' => ['value' => '']];
$summary = [];
$page = $approval->get_data_pending_items($summary);
check($summary['query_ok'] && $page['recordsTotal'] === 2 && array_column($page['data'], 'no_dokumen') === ['DOC-2'], 'pending/default approved and descending paging');
$input->values['start'] = 1;
check(array_column($approval->get_data_pending_items($summary)['data'], 'no_dokumen') === ['DOC-1'], 'second page');
$input->values['start'] = 0; $input->values['length'] = 25;
$input->values['search']['value'] = 'DOC-1';
$page = $approval->get_data_pending_items($summary);
check($page['recordsTotal'] === 2 && $page['recordsFiltered'] === 1 && count($page['data']) === 1, 'real search/counts');
$input->values['search']['value'] = "' OR 1=1 --";
check($approval->get_data_pending_items($summary)['data'] === [], 'bound search injection');
$input->values['search']['value'] = '';
$page = $record->get_data_record_items($summary);
check($summary['query_ok'] && array_column($page['data'], 'no_dokumen') === ['DOC-3'], 'approved snapshots with done, non-deleted header');
check($page['data'][0]['id_dokumen'] === 'SOURCE-3', 'source print identifier, not snapshot id');
$input->values['search']['value'] = 'PGJ-2';
check($record->get_data_record_items($summary)['recordsFiltered'] === 1, 'Record search joins header');
$input->values['search']['value'] = "' OR 1=1 --";
check($record->get_data_record_items($summary)['data'] === [], 'Record bound search injection');
$input->values['search']['value'] = '';
$db->update('tr_rp_pengajuan_d', ['dpp' => 900000, 'dibayarkan' => 900000], ['id' => 3]);
$page = $record->get_data_record_items($summary);
check($page['data'][0]['dpp'] === 100000.0 && $page['data'][0]['dibayarkan'] === 106500.0, 'snapshot remains immutable after source change');
$locked = $request->get_locked_docs();
sort($locked);
check($locked === ['DOC-1', 'DOC-2', 'DOC-3'], 'pending/approved locked, rejected and deleted unlocked');
$db->insert('tr_rp_pengajuan_h', ['id' => 4, 'no_pengajuan' => 'PGJ-4', 'company_nama' => 'STM', 'status' => 'pending', 'deleted' => 0, 'submitted_at' => '2026-10-14 11:00:00']);
$item = $db->get_where('tr_rp_pengajuan_d', ['id' => 4])->row_array();
$item['id'] = 6; $item['id_pengajuan'] = 4; $item['decision'] = 'approved';
$db->insert('tr_rp_pengajuan_d', $item);
$page = $approval->get_data_pending_items($summary);
check(array_column($page['data'], 'no_dokumen') === ['DOC-4', 'DOC-2', 'DOC-1'], 'rejected document resubmitted once under new PGJ');
check(in_array('DOC-4', $request->get_locked_docs(), true), 'resubmitted document locked');
check(array_column($record->get_data_record_items($summary)['data'], 'no_dokumen') === ['DOC-3'], 'resubmitted reject absent from Record');
$input->values = ['draw' => [], 'start' => [], 'length' => [], 'search' => ['value' => []]];
check($approval->get_data_pending_items($summary)['recordsFiltered'] === 3, 'malformed input safe defaults');
$db->query('DROP TABLE tr_rp_record');
try {
    $record->get_data_record_items($summary);
    throw new RuntimeException('Expected missing-table query failure');
} catch (Error $expected) {
    // This legacy count builder throws on a failed query; the endpoint catches Throwable.
    check(strpos($db->error()['message'], 'no such table') !== false, 'real count-query failure reaches endpoint exception handler');
}
echo "PASS Request Payment tab queries: {$checks} assertions, real SQLite/CodeIgniter query builder\n";
