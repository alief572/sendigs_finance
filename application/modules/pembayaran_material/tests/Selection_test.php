<?php
define('MATERIAL_FIXTURE_ONLY', true);
require __DIR__ . '/Selection_fixture.php';

class SelectionResult {
    private $rows;
    public function __construct($rows) { $this->rows = array_map(function ($r) { return (object) $r; }, $rows); }
    public function result() { return $this->rows; }
    public function row() { return $this->rows[0] ?? null; }
    public function num_rows() { return count($this->rows); }
    public function result_array() { return array_map('get_object_vars', $this->rows); }
    public function row_array() { return $this->result_array()[0] ?? null; }
}
class SelectionDb extends MaterialDb {
    public $data, $filters = array(), $reads = 0, $failRead = 0, $snapshot, $locked = false;
    public function __construct() {
        $this->data = array('v_list_payment' => array(), 'payment_approve' => array(), 'tr_kasbon' => array(), 'tr_expense' => array(),
            'tr_choosed_payment' => array(), 'users' => array(array('id_user' => 7)));
    }
    public function add($id, $type, $consultant = null) {
        $row = array('id' => $id, 'tipe' => $type, 'no_doc' => 'DOC-' . $id, 'admin_bank' => 0,
            'created_by' => '', 'created_on' => '2026-10-20', 'requestor' => 'Fixture', 'jumlah' => 100,
            'keperluan' => 'Selection test', 'currency' => 'IDR', 'status' => 1, 'is_po_payment' => 0);
        $this->data['payment_approve'][] = $row;
        $this->data['v_list_payment'][] = $row;
        if ($type === 'kasbon' || $type === 'expense') {
            $this->data['tr_' . $type][] = array('no_doc' => 'DOC-' . $id, 'no_' . $type . '_consultant' => $consultant, 'nama' => '', 'created_by' => '');
        }
        return $this;
    }
    public function choose($id, $user = 7) { $this->data['tr_choosed_payment'][] = array('id_user' => $user, 'id_payment' => $id); return $this; }
    private function matches($row) {
        foreach ($this->filters as $field => $values) {
            $field = preg_replace('/^\w+\./', '', $field);
            if (substr($field, -3) === ' <>') { if (in_array($row[substr($field, 0, -3)] ?? null, $values, false)) { return false; } continue; }
            if (substr($field, -2) === ' >') { if (($row[substr($field, 0, -2)] ?? 0) <= $values[0]) { return false; } continue; }
            if (substr($field, -3) === ' >=') { if (($row[substr($field, 0, -3)] ?? '') < $values[0]) { return false; } continue; }
            if (preg_match("/^\(created_on < '([\d-]+)' OR created_on IS NULL\)$/", $field, $m)) { if (($row['created_on'] ?? null) !== null && $row['created_on'] >= $m[1]) { return false; } continue; }
            if (!in_array($row[$field] ?? null, $values, false)) { return false; }
        }
        return true;
    }
    public function __call($name, $args) {
        $this->calls[] = $name;
        if (in_array($name, array('select', 'order_by', 'limit', 'group_start', 'group_end'), true)) { return $this; }
        if ($name === 'count_all_results') { return count($this->data[$this->table]); }
        if ($name === 'from') { $this->table = $args[0]; return $this; }
        if ($name === 'where' || $name === 'where_in') {
            $conditions = is_array($args[0]) ? $args[0] : array($args[0] => $args[1]);
            foreach ($conditions as $field => $value) { $this->filters[$field] = is_array($value) ? $value : array($value); }
            return $this;
        }
        if ($name === 'get' || $name === 'get_where' || $name === 'query') {
            $this->reads++;
            if ($this->reads === $this->failRead) { $this->queryError = 999; $this->filters = array(); return false; }
            $this->queryError = 0;
            if ($name === 'query') {
                check(strpos($args[0], 'FOR UPDATE') !== false, 'Expected parameterized user lock');
                check(($args[1][0] ?? null) === 7, 'Lock current user');
                $this->locked = true;
                return new SelectionResult($this->data['users']);
            }
            $table = $args[0] ?? $this->table;
            if ($name === 'get_where') { $this->__call('where', array($args[1])); }
            $rows = array_values(array_filter($this->data[$table] ?? array(), function ($row) { return $this->matches($row); }));
            $this->filters = array();
            return new SelectionResult($rows);
        }
        if ($name === 'insert' || $name === 'delete') {
            check($this->locked, 'Selection writes require per-user lock');
            $result = parent::__call($name, $args);
            if (!$result) { return false; }
            if ($name === 'insert') { $this->data[$args[0]][] = $args[1]; }
            else {
                $this->filters = array_map(function ($v) { return array($v); }, $args[1]);
                $this->data[$args[0]] = array_values(array_filter($this->data[$args[0]], function ($r) { return !$this->matches($r); }));
                $this->filters = array();
            }
            return true;
        }
        if ($name === 'trans_begin') { $this->snapshot = $this->data; }
        if ($name === 'trans_rollback' && $this->snapshot !== null) { $this->data = $this->snapshot; }
        return parent::__call($name, $args);
    }
}
function selection_run($db, $method, $post = array(), $options = array()) {
    return material_run($method, array_merge(array('db_fixture' => $db), $options), $post);
}
function rejected_selection($db, $message) {
    list($event, $output) = selection_run($db, 'check_payment', array('id' => 'B', 'checked' => 1));
    check(json_decode($output, true)['status'] === 0, $message);
    check($event['status'] === 0 && count($db->data['tr_choosed_payment']) === 1, 'Rejected without writes');
}

rejected_selection((new SelectionDb())->add('A', 'kasbon')->add('B', 'expense')->choose('A'), 'Reject mixed types');
foreach (array('kasbon', 'expense') as $type) {
    rejected_selection((new SelectionDb())->add('A', $type)->add('B', $type, 'C-2')->choose('A'), 'Reject NULL vs non-NULL');
    rejected_selection((new SelectionDb())->add('A', $type, 'C-1')->add('B', $type)->choose('A'), 'Reject reverse grouping');
    rejected_selection((new SelectionDb())->add('A', $type)->add('B', $type, '')->choose('A'), 'Empty string is not NULL');
    foreach (array(array(null, null), array('C-1', 'C-2'), array('', 'C-2'), array('0', '')) as $values) {
        $db = (new SelectionDb())->add('A', $type, $values[0])->add('B', $type, $values[1])->choose('A');
        list($event, $output) = selection_run($db, 'check_payment', array('id' => 'B'));
        check(json_decode($output, true)['status'] === 1 && count($db->data['tr_choosed_payment']) === 2, 'Compatible consultant group');
        check(in_array('trans_commit', $event['calls_at_write'], true), 'Success audit follows commit');
    }
}
$db = (new SelectionDb())->add('A', 'periodik')->add('B', 'periodik')->choose('A');
list($event, $output) = selection_run($db, 'check_payment', array('id' => 'B'));
check(json_decode($output, true)['status'] === 1, 'Other types match exactly');
list($event, $output) = selection_run($db, 'check_payment', array('id' => 'B'));
check(count($db->data['tr_choosed_payment']) === 2, 'Idempotent selection');
$db = (new SelectionDb())->add('B', 'expense')->choose('A', 8);
list($event, $output) = selection_run($db, 'check_payment', array('id' => 'B'));
check(json_decode($output, true)['status'] === 1 && count($db->data['tr_choosed_payment']) === 2, 'User isolation');
$db = (new SelectionDb())->add('A', 'kasbon')->choose('A');
list($event, $output) = selection_run($db, 'check_payment', array('id' => 'A', 'checked' => 0));
check(json_decode($output, true)['status'] === 1 && !$db->data['tr_choosed_payment'], 'Uncheck last selection');
foreach (array('clear_choosed_payment', 'used_choosed_payment', 'list_request_payment') as $method) {
    $db = (new SelectionDb())->choose('A')->choose('OTHER', 8);
    list($event) = material_run($method, array('db_fixture' => $db), array(), $method === 'list_request_payment' ? array(2) : array());
    check($event['status'] === 1 && count($db->data['tr_choosed_payment']) === 1 && $db->data['tr_choosed_payment'][0]['id_user'] === 8, 'Reset scoped to locked user');
}
foreach (array('trans_begin_failure', 'transaction_failure', 'trans_commit_failure', 'fail_write') as $failure) {
    $db = (new SelectionDb())->add('B', 'expense');
    list($event, $output) = selection_run($db, 'check_payment', array('id' => 'B'), array($failure => $failure === 'fail_write' ? 1 : true));
    check(json_decode($output, true)['status'] === 0 && !$db->data['tr_choosed_payment'], 'Rollback selection ' . $failure);
}
for ($i = 1; $i <= 4; $i++) {
    $db = (new SelectionDb())->add('A', 'kasbon')->add('B', 'kasbon')->choose('A'); $db->failRead = $i;
    list($event, $output) = selection_run($db, 'check_payment', array('id' => 'B'));
    check(json_decode($output, true)['status'] === 0 && count($db->data['tr_choosed_payment']) === 1, 'Query failure closed');
}
$db = (new SelectionDb())->add('A', 'kasbon')->add('B', 'kasbon')->choose('A');
$db->data['tr_kasbon'] = array(); rejected_selection($db, 'Missing source rejected');
foreach (array('proses_payment', 'form_payment_new', 'save_payment') as $method) {
    $db = (new SelectionDb())->add('A', 'kasbon')->add('B', 'expense')->choose('A')->choose('B');
    list($event, $output) = selection_run($db, $method, array('id_payment' => 'A,B'),
        array('get' => array('id_payment' => 'A;B'), 'expected_exception' => $method === 'form_payment_new'));
    check($event['status'] === 0 && !$db->writes, 'Direct mixed selection blocked: ' . $method);
    if ($method !== 'form_payment_new') { check(json_decode($output, true)['status'] === 0, 'JSON failure'); }
}
foreach (array('writer_failure', 'writer_exception') as $failure) {
    $db = (new SelectionDb())->add('B', 'periodik');
    list($event, $output) = selection_run($db, 'check_payment', array('id' => 'B'), array($failure => true));
    check(json_decode($output, true)['status'] === 1 && count($db->data['tr_choosed_payment']) === 1, 'Audit writer independent');
}
foreach (array(array('id' => ''), array('id' => array('B')), array('checked' => 2), array('checked' => array(1)), array('checked' => null)) as $invalid) {
    $db = (new SelectionDb())->add('B', 'expense');
    list($event, $output) = selection_run($db, 'check_payment', array_merge(array('id' => 'B'), $invalid));
    check(json_decode($output, true)['status'] === 0 && !$db->writes, 'Malformed input rejected');
}
$db = (new SelectionDb())->add('A', 'periodik')->choose('A');
rejected_selection($db, 'Missing payment rejected');
rejected_selection((new SelectionDb())->add('A', 'periodik')->add('B', 'Periodik')->choose('A'), 'Type comparison is exact');
foreach (array('clear_choosed_payment', 'used_choosed_payment', 'list_request_payment') as $method) {
    foreach (array('trans_begin_failure', 'transaction_failure', 'trans_commit_failure', 'fail_write', 'lock_failure') as $failure) {
        $db = (new SelectionDb())->choose('A');
        if ($failure === 'lock_failure') { $db->data['users'] = array(); }
        list($event) = material_run($method, array('db_fixture' => $db, $failure => $failure === 'fail_write' ? 1 : true), array(), $method === 'list_request_payment' ? array(2) : array());
        check($event['status'] === 0 && count($db->data['tr_choosed_payment']) === 1, 'Reset rollback ' . $method . '/' . $failure);
    }
}
for ($i = 1; $i <= 6; $i++) {
    $db = (new SelectionDb())->add('A', 'kasbon')->choose('A'); $db->failRead = $i;
    list($event, $output) = selection_run($db, 'proses_payment');
    check($event['status'] === 0 && json_decode($output, true)['status'] === 0, 'Process query error returns JSON ' . $i);
}
foreach (array('form_payment_new', 'save_payment') as $method) {
    for ($i = 1; $i <= ($method === 'save_payment' ? 3 : 2); $i++) {
        $db = (new SelectionDb())->add('A', 'kasbon'); $db->failRead = $i;
        list($event, $output) = selection_run($db, $method, array('id_payment' => 'A'), array('get' => array('id_payment' => 'A'), 'expected_exception' => $method === 'form_payment_new'));
        check($event['status'] === 0 && !$db->writes, 'Guard failure before payment side effects');
    }
    foreach (array('missing_payment', 'missing_source', 'mixed_consultant') as $failure) {
        $db = (new SelectionDb())->add('A', 'expense')->add('B', 'expense', 'C-2');
        if ($failure === 'missing_payment') { $db->data['payment_approve'] = array(); }
        if ($failure === 'missing_source') { $db->data['tr_expense'] = array(); }
        list($event, $output) = selection_run($db, $method, array('id_payment' => 'A,B'), array('get' => array('id_payment' => 'A;B'), 'expected_exception' => $method === 'form_payment_new'));
        check($event['status'] === 0 && !$db->writes, 'Direct source guard ' . $method . '/' . $failure);
    }
}
$db = (new SelectionDb())->add('A', 'kasbon')->add('B', 'expense')->choose('A')->choose('B');
list($event, $output) = selection_run($db, 'check_payment', array('id' => 'B', 'checked' => 0));
check(json_decode($output, true)['status'] === 1 && count($db->data['tr_choosed_payment']) === 1, 'Can repair old mixed choices by unchecking');
list($event, $output) = selection_run($db, 'proses_payment');
check(json_decode($output, true)['status'] === 1 && json_decode($output, true)['arr_choosed_payment'] === 'A', 'Valid selection processes');
list($event) = selection_run($db, 'clear_choosed_payment');
list($event, $output) = selection_run($db, 'proses_payment');
check(json_decode($output, true)['status'] === 1 && json_decode($output, true)['count_choosed_payment'] === 0, 'Empty process preserves empty-state contract');

foreach (array('kasbon', 'expense') as $type) {
    foreach (array(null, '') as $consultant) {
        $db = (new SelectionDb())->add('A', $type, $consultant)->add('B', $type, $consultant)->add('C', $type, $consultant === null ? '' : null)->add('D', 'periodik')->choose('A');
        // The selected document is outside the visible page, like server-side pagination/search.
        array_shift($db->data['v_list_payment']);
        list($event, $output) = selection_run($db, 'get_list_req_payment', array('jenis_payment' => 2, 'search' => array('value' => '')));
        $data = json_decode($output, true);
        check($event['status'] === 1 && !$data['selection_error'], 'Listing success');
        check($data['data'][0]['selection_compatible'] && strpos($data['data'][0]['option'], ' disabled') === false, 'Same group enabled across pages');
        check(!$data['data'][1]['selection_compatible'] && strpos($data['data'][1]['option'], ' disabled') !== false, 'Other NULL group disabled');
        check(!$data['data'][2]['selection_compatible'] && strpos($data['data'][2]['option'], ' disabled') !== false, 'Other type disabled');
    }
}
$db = (new SelectionDb())->add('A', 'kasbon')->add('B', 'expense')->choose('A')->choose('B');
list($event, $output) = selection_run($db, 'get_list_req_payment', array('jenis_payment' => 2, 'search' => array('value' => '')));
$data = json_decode($output, true);
check($event['status'] === 0 && $data['selection_error'] !== '', 'Old mixed selection exposed');
foreach ($data['data'] as $row) { check(strpos($row['option'], 'checked') !== false && strpos($row['option'], ' disabled') === false, 'Selected rows remain removable'); }
list($event) = selection_run($db, 'clear_choosed_payment');
list($event, $output) = selection_run($db, 'get_list_req_payment', array('jenis_payment' => 2, 'search' => array('value' => '')));
foreach (json_decode($output, true)['data'] as $row) { check($row['selection_compatible'], 'Clear unlocks all groups'); }
$db = (new SelectionDb())->add('A', 'kasbon')->choose('A'); $db->data['tr_kasbon'] = array();
list($event, $output) = selection_run($db, 'get_list_req_payment', array('jenis_payment' => 2, 'search' => array('value' => '')));
check($event['status'] === 0 && json_decode($output, true)['selection_error'] !== '', 'Missing listing source fails closed');
$db = (new SelectionDb())->add('A', 'kasbon')->add('B', 'kasbon')->choose('A');
array_pop($db->data['tr_kasbon']);
list($event, $output) = selection_run($db, 'get_list_req_payment', array('jenis_payment' => 2, 'search' => array('value' => '')));
$data = json_decode($output, true);
check($event['status'] === 1 && $data['data'][0]['selection_compatible'] && !$data['data'][1]['selection_compatible'], 'Unselected orphan only disables its own checkbox');
$db = (new SelectionDb())->add('A', 'expense')->add('B', 'expense')->add('C', 'kasbon')->choose('A')->choose('B');
selection_run($db, 'check_payment', array('id' => 'A', 'checked' => 0));
list($event, $output) = selection_run($db, 'check_payment', array('id' => 'C'));
check(json_decode($output, true)['status'] === 0, 'Removing first selection retains group of remaining choices');
selection_run($db, 'check_payment', array('id' => 'B', 'checked' => 0));
list($event, $output) = selection_run($db, 'check_payment', array('id' => 'C'));
check(json_decode($output, true)['status'] === 1, 'Removing all choices permits a new group');

// Batas periode tabel bawah/atas memakai created_on; tidak ada baris yang hilang atau dobel.
$dates = array('A' => '2026-10-31 23:59:59', 'B' => '2026-11-01 00:00:00', 'C' => '2026-12-05 08:00:00', 'D' => null);
foreach (array('baru' => 'B,C', 'lama' => 'A,D', '' => 'A,B,C,D', 'lain' => 'A,B,C,D') as $periode => $expected) {
    $db = new SelectionDb();
    foreach ($dates as $id => $date) { $db->add($id, 'periodik'); $db->data['v_list_payment'][count($db->data['v_list_payment']) - 1]['created_on'] = $date; }
    list($event, $output) = selection_run($db, 'get_list_req_payment', array('jenis_payment' => 2, 'periode' => $periode, 'search' => array('value' => '')));
    $shown = implode(',', array_map(function ($row) { return substr($row['no_dokumen'], 4); }, json_decode($output, true)['data']));
    check($shown === $expected, 'Period ' . $periode . ' lists ' . $expected . ', got ' . $shown);
}

// Tabel atas: baris transfer dulu, lalu dokumen periode baru; paging menyambung tanpa hilang/dobel.
if (!function_exists('html_escape')) { function html_escape($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); } }
class QueueResult {
    private $rows;
    public function __construct($rows) { $this->rows = $rows; }
    public function row_array() { return $this->rows[0] ?? null; }
    public function result_array() { return $this->rows; }
}
class QueueDb extends SelectionDb {
    public function table_exists($table) { return true; }
    public function __call($name, $args) {
        if ($name === 'query' && strpos($args[0], 'tr_rp_transfer') !== false) {
            $this->calls[] = $name;
            if (strpos($args[0], 'COUNT(DISTINCT t.id)') !== false) { return new QueueResult(array(array('total' => 2))); }
            preg_match('/LIMIT (\d+), (\d+)/', $args[0], $m);
            $rows = array();
            foreach (array(1, 2) as $id) {
                $rows[] = array('id' => $id, 'no_pengajuan' => 'PGJ-T' . $id, 'company_nama' => 'STM', 'decided_at' => '2026-11-20', 'urutan' => 1,
                    'bank' => 'BCA', 'no_rek' => '1', 'nama' => 'X', 'admin' => '0', 'bearer' => 'company', 'numbers' => 'T-' . $id, 'categories' => 'Kasbon', 'net' => '10.00');
            }
            return new QueueResult(array_slice($rows, (int) $m[1], (int) $m[2]));
        }
        if ($name === 'limit') { $this->window = array($args[1] ?? 0, $args[0]); return $this; }
        $result = parent::__call($name, $args);
        if ($name === 'get' && ($args[0] ?? null) === null && $this->table === 'v_list_payment' && $this->window) {
            $result = new SelectionResult(array_map('get_object_vars', array_slice($result->result(), $this->window[0], $this->window[1])));
            $this->window = null;
        }
        if ($name === 'count_all_results') { return count(array_filter($this->data['v_list_payment'], function ($r) { return $r['created_on'] >= '2026-11-01'; })); }
        return $result;
    }
    public $window;
}
$seen = array();
foreach (array(0, 2, 4) as $start) {
    $db = new QueueDb();
    foreach (array('OLD' => '2026-10-01', 'N1' => '2026-11-03', 'N2' => '2026-11-02', 'N3' => '2026-11-01') as $id => $date) {
        $db->add($id, 'periodik'); $db->data['v_list_payment'][count($db->data['v_list_payment']) - 1]['created_on'] = $date;
    }
    list($event, $output) = selection_run($db, 'get_list_rp_transfer', array('draw' => 1, 'start' => $start, 'length' => 2, 'search' => array('value' => '')));
    $json = json_decode($output, true);
    check($event['status'] === 1 && $json['recordsTotal'] === 5 && $json['recordsFiltered'] === 5, 'Queue counts transfers plus new documents: ' . $output);
    foreach ($json['data'] as $row) {
        $seen[] = $row['no'] . ':' . (preg_match('/value="(\d+)"[^>]*data-label/', $row['option']) ? 'T' : (strpos($row['option'], 'check_payment') !== false ? substr(strip_tags(strstr($row['dokumen'], '<br>', true)), 4) : '?'));
    }
}
check(implode(' ', $seen) === '1:T 2:T 3:N1 4:N2 5:N3', 'Queue pages continue without gaps or duplicates: ' . implode(' ', $seen));

// Proses: server menentukan form transfer atau form lama.
foreach (array('2026-11-05' => 'transfer', '2026-10-05' => 'legacy') as $date => $target) {
    $db = (new QueueDb())->add('A', 'periodik')->choose('A');
    $db->data['payment_approve'][0]['created_on'] = $date;
    $db->data['tr_rp_pengajuan_d'] = array(array('id' => 1, 'id_pengajuan' => 9, 'no_dokumen' => 'DOC-A', 'kategori' => 'Periodik', 'decision' => 'approved',
        'dpp' => 100, 'nilai_ppn' => 0, 'nilai_pph' => 0, 'admin' => 0, 'dibayarkan' => 100, 'id_transfer' => null, 'payment_approval_id' => null));
    $db->data['tr_rp_pengajuan_h'] = array(array('id' => 9, 'status' => 'done', 'deleted' => 0));
    list($event, $output) = selection_run($db, 'proses_payment');
    $json = json_decode($output, true);
    check($json['status'] === 1 && $json['target'] === $target, 'Process routes ' . $date . ' to ' . $target . ': ' . $output);
}

$db = new SelectionDb(); $ids = array();
for ($i = 0; $i < 100; $i++) { $ids[] = 'P-' . $i; $db->add('P-' . $i, $i % 2 ? 'kasbon' : 'expense'); }
$model = new MaterialModel($db, new MaterialInput());
check(count($model->payment_groups($ids)) === 100 && $db->reads === 3, 'Batch metadata uses three queries for 100 documents');
$jenis_payment = '2';
ob_start(); require dirname(__DIR__) . '/views/list_request_payment.php'; $html = ob_get_clean();
check(strpos($html, 'data-jenis-payment="2"') !== false && strpos($html, 'assets/js/pembayaran_material/payment_selection.js') !== false, 'View loads tested client with payment category');
check(strpos($html, 'rp-tabs') === false && substr_count($html, 'proses_payment"') === 1, 'Without transfer feature: one list, no tabs');
if (!function_exists('site_url')) { function site_url($path = '') { return '/fixture/' . $path; } }
$rp_transfer_show = true;
ob_start(); require dirname(__DIR__) . '/views/list_request_payment.php'; $tabbed = ob_get_clean();
check(strpos($tabbed, 'Pengajuan Baru<') !== false && strpos($tabbed, 'Dokumen Lama<') !== false && strpos($tabbed, 'data-periode="lama"') !== false
    && substr_count($tabbed, 'btn-success proses_payment') === 1 && substr_count($tabbed, 'id="table_list_req_payment"') === 1 && strpos($tabbed, 'id="table_rp_transfer"') !== false,
    'Two tabs share one set of buttons');
$rp_transfer_show = false;
$modelSource = file_get_contents(dirname(__DIR__) . '/models/Pembayaran_material_model.php');
preg_match_all('/->([a-zA-Z_]\w*)\b(?!\s*\()/', $modelSource, $propertyMatches);
$GLOBALS['materialRowDefaults'] = array_fill_keys($propertyMatches[1], 0);
$GLOBALS['materialRowDefaults'] = array_merge($GLOBALS['materialRowDefaults'], array(
    'nama' => 'PRIVATE', 'nm_bank' => 'PRIVATE', 'nama_bank' => 'PRIVATE', 'rekening' => 'PRIVATE',
    'coa_bank' => '1101-01', 'nm_coa' => 'Kas', 'no_coa' => '1101-01', 'coa' => '1101-01',
    'requestor' => 'PRIVATE', 'request_by' => 'PRIVATE', 'keperluan' => 'PRIVATE', 'created_on' => '2026-10-13',
    'max_id' => '', 'kode_bank' => 'BNK', 'nocab' => '101', 'subcab' => '01', 'nobuk' => 1, 'no_payment_hutang' => 'PHP-1',
    'department_id' => 5, 'company_id' => 'COM006', 'nm_company' => 'Sustain', 'name' => 'Department',
    'currency' => 'IDR', 'tipe' => 'kasbon', 'nilai_pph' => 0, 'nilai_ppn' => 0
));

foreach (array(array(), array('trans_begin_failure' => true), array('transaction_failure' => true), array('trans_commit_failure' => true), array('writer_failure' => true), array('writer_exception' => true)) as $options) {
    list($event, $output) = material_run('save_payment', $options);
    $expected = empty($options['trans_begin_failure']) && empty($options['transaction_failure']) && empty($options['trans_commit_failure']);
    check(json_decode($output, true)['status'] === ($expected ? 1 : 0), 'Published save transaction response');
    check($event['status'] === ($expected ? 1 : 0), 'Published save audit outcome');
    if ($expected) { check(in_array('trans_commit', $event['calls_at_write'], true), 'Published save audit after commit'); }
}
echo 'PASS Selection: ' . $scenarioCount . " scenarios\n";
