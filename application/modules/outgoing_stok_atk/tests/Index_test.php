<?php
define('BASEPATH', __DIR__);
set_error_handler(function ($severity, $message, $file, $line) { throw new ErrorException($message, 0, $severity, $file, $line); });
class BF_Model {}
function get_list_user() { return array(7 => array('nama' => 'Finance')); }
function base_url($path = '') { return '/fixture/' . $path; }
function log_message($level, $message) {}
require dirname(__DIR__) . '/models/Outgoing_stok_atk_model.php';
$checks = 0;
function verify($condition, $message) { global $checks; $checks++; if (!$condition) { throw new RuntimeException($message); } }
class IndexResult {
    private $rows;
    public function __construct($rows) { $this->rows = $rows; }
    public function result_array() { return $this->rows; }
    public function row_array() { return $this->rows[0] ?? array(); }
    public function num_rows() { return count($this->rows); }
}
class IndexDb {
    public $pdo, $queries = array(), $failAt = 0, $errorCode = 0;
    public function __construct() {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec('CREATE TABLE warehouse (id INTEGER PRIMARY KEY, nm_gudang TEXT);
            CREATE TABLE warehouse_adjustment (id INTEGER PRIMARY KEY, kode_trans TEXT, pic TEXT, id_dept INTEGER,
                tanggal TEXT, jumlah_mat REAL, jumlah_mat_packing REAL, created_by INTEGER, created_date TEXT,
                checked TEXT, deleted_date TEXT, category TEXT, id_gudang_dari INTEGER, id_gudang_ke INTEGER);
            CREATE TABLE warehouse_adjustment_detail (id INTEGER PRIMARY KEY, kode_trans TEXT, id_material INTEGER, qty_order REAL);
            CREATE TABLE accessories (id INTEGER PRIMARY KEY, id_stock TEXT, stock_name TEXT, id_unit INTEGER);
            CREATE TABLE ms_satuan (id INTEGER PRIMARY KEY, code TEXT, deleted_date TEXT);
            INSERT INTO warehouse VALUES (1, "Gudang pusat"), (2, "Subgudang");
            INSERT INTO accessories VALUES (11, "PAPER-A4", "Kertas A4", 1), (12, "PEN-01", "Pulpen", 2),
                (13, "XSS", "<img src=x onerror=alert(1)>", 3), (14, "LITERAL", "Barang 100%_!", 999),
                (15, "OLD", "Satuan lama", 4);
            INSERT INTO ms_satuan VALUES (1, "RIM", NULL), (2, "PCS", NULL), (3, "<b>BOX</b>", NULL), (4, "OLD", "2026-01-01");');
        for ($i = 1; $i <= 6; $i++) {
            $this->pdo->prepare('INSERT INTO warehouse_adjustment VALUES (?, ?, ?, 1, ?, ?, 0, 7, ?, "Y", NULL, "outgoing stok", 1, 2)')
                ->execute(array($i, 'TRM' . $i, 'PIC ' . $i, '2026-10-' . (10 + $i), $i * 2, '2026-10-' . (10 + $i) . ' 10:00:00'));
        }
        $this->pdo->exec('INSERT INTO warehouse_adjustment VALUES (7, "DELETED", "", 1, "2026-10-01", 1, 0, 7, "2026-10-01", "Y", "2026-10-02", "outgoing stok", 1, 2);
            INSERT INTO warehouse_adjustment VALUES (8, "INCOMING", "", 1, "2026-10-01", 1, 0, 7, "2026-10-01", "Y", NULL, "incoming stok", 1, 2);
            INSERT INTO warehouse_adjustment_detail VALUES (1, "TRM1", 11, 2.5), (2, "TRM1", 12, 3),
                (3, "TRM1", 11, 1), (4, "TRM2", 11, 4), (5, "TRM3", 999, 1.25),
                (6, "TRM4", 13, 0.5), (7, "TRM4", 14, 1000.75), (8, "TRM6", 15, 1),
                (9, "DELETED", 11, 1), (10, "INCOMING", 11, 1);');
    }
    public function escape_like_str($value) { return str_replace(array('!', '%', '_'), array('!!', '!%', '!_'), $value); }
    public function error() { return array('code' => $this->errorCode); }
    public function query($sql, $bindings = array()) {
        $this->queries[] = array($sql, $bindings);
        $this->errorCode = count($this->queries) === $this->failAt ? 999 : 0;
        if ($this->errorCode) { return false; }
        $statement = $this->pdo->prepare($sql); $statement->execute($bindings);
        return new IndexResult($statement->fetchAll(PDO::FETCH_ASSOC));
    }
}
class IndexHris {
    public function __call($name, $args) {
        if ($name === 'get') { return new IndexResult(array(array('nm_dept' => 'Sales', 'nm_comp' => 'Company'))); }
        return $this;
    }
}
class IndexAuth { public function user_name() { return 'Finance'; } }
class IndexModel extends Outgoing_stok_atk_model {
    public function __construct($db) { $this->db = $db; $this->dbhris = new IndexHris(); $this->auth = new IndexAuth(); }
}
function response($search = '', $start = 0, $length = 10, $column = 1, $direction = 'desc', $failAt = 0) {
    $db = new IndexDb(); $db->failAt = $failAt;
    $_REQUEST = array('draw' => 3, 'start' => $start, 'length' => $length,
        'search' => array('value' => $search), 'order' => array(array('column' => $column, 'dir' => $direction)));
    ob_start(); (new IndexModel($db))->data_side_request_material(); $output = ob_get_clean();
    $data = json_decode($output, true);
    verify(is_array($data), 'Valid JSON response');
    return array($data, $db);
}

$db = new IndexDb(); $model = new IndexModel($db);
$details = $model->get_outgoing_item_details(array('TRM1', 'TRM2'));
verify(count($db->queries) === 1 && count($details['TRM1']) === 3 && count($details['TRM2']) === 1, 'One batch query preserves detail rows');
verify(!isset($details['TRM4']), 'Only current page details loaded');
verify($model->get_outgoing_item_details(array()) === array() && count($db->queries) === 1, 'Empty page skips detail query');

list($data, $db) = response();
verify($data['recordsTotal'] === 6 && $data['recordsFiltered'] === 6 && count($data['data']) === 6, 'Counts exclude deleted and other categories');
verify(count($data['data'][0]) === 10, 'Ten columns including item detail');
$byCode = array(); foreach ($data['data'] as $row) { $byCode[strip_tags($row[1])] = $row; }
verify(strpos($byCode['TRM1'][4], 'Kertas A4') !== false && strpos($byCode['TRM1'][4], 'Pulpen') !== false, 'All items visible');
verify(strpos($byCode['TRM1'][4], '2,50 RIM') !== false && strpos($byCode['TRM1'][4], '3,00 PCS') !== false, 'Qty order with two decimals and correct unit');
verify(substr_count($byCode['TRM1'][4], 'Kertas A4') === 2, 'Repeated lines are not merged');
verify(strpos($byCode['TRM3'][4], '999') !== false && strpos($byCode['TRM3'][4], '1,25 -') !== false, 'Missing material fallback preserves quantity');
verify(strpos($byCode['TRM5'][4], 'Tidak ada detail barang') !== false, 'Empty detail fallback');
verify(strpos($byCode['TRM4'][4], '<img') === false && strpos($byCode['TRM4'][4], '&lt;img') !== false && strpos($byCode['TRM4'][4], '&lt;b&gt;BOX') !== false, 'Item and unit HTML escaped');
verify(strpos($byCode['TRM4'][4], '1.000,75 -') !== false && strpos($byCode['TRM6'][4], '1,00 -') !== false, 'Missing/deleted units use fallback');
verify(strip_tags($byCode['TRM1'][5]) === '2' && strpos($byCode['TRM1'][9], "data-kode_trans='TRM1'") !== false, 'Header total and detail action preserved');
$batch = array_filter($db->queries, function ($query) { return strpos($query[0], 'qty_order') !== false; });
verify(count($batch) === 1, 'One detail query for whole listing');

foreach (array('Kertas', 'PAPER-A4', 'pApEr-a4') as $search) {
    list($data) = response($search);
    verify($data['recordsTotal'] === 6 && $data['recordsFiltered'] === 2 && count($data['data']) === 2, 'Item search counts transactions once');
    $all = json_encode($data['data']);
    verify(strpos($all, 'Pulpen') !== false, 'Searching one item still displays every item in transaction');
}
foreach (array('TRM3' => 1, 'Subgudang' => 6, 'NoSuchItem' => 0, "%' OR 1=1 --" => 0, '100%_!' => 1) as $term => $count) {
    list($data) = response($term);
    verify($data['recordsFiltered'] === $count, 'Search term is literal: ' . $term);
}
list($page, $db) = response('Kertas', 1, 1, 1, 'asc');
verify(count($page['data']) === 1 && strip_tags($page['data'][0][1]) === 'TRM2' && $page['recordsFiltered'] === 2, 'Pagination applies to headers after filtering');
list($data) = response('', 0, 2, 2, 'asc');
verify(strip_tags($data['data'][0][1]) === 'TRM1' && strip_tags($data['data'][1][1]) === 'TRM2', 'Date sorting uses transaction date');
list($data) = response('', 0, 2, 5, 'desc');
verify(strip_tags($data['data'][0][1]) === 'TRM6', 'Qty column index shifted correctly');
foreach (array(0, 1, 2, 3, 5, 6, 7, 8, 4, 9, 999, '1; DROP TABLE accessories') as $column) {
    list($data) = response('', 0, 10, $column, 'asc');
    verify(count($data['data']) === 6, 'Whitelisted sort or safe fallback');
}
list($data, $db) = response('', '-1 OR 1=1', '2;DROP', array(1), 'DESC; DROP TABLE accessories');
verify(count($data['data']) === 6 && $db->pdo->query('SELECT COUNT(*) FROM accessories')->fetchColumn() == 5, 'Invalid sort/pagination cannot inject SQL');
list($data, $db) = response(array('bad'));
verify(count($data['data']) === 6, 'Non-scalar search safely defaults to empty');
for ($i = 1; $i <= 4; $i++) {
    list($data) = response('Kertas', 0, 10, 1, 'desc', $i);
    verify(isset($data['error']) && $data['data'] === array() && strpos($data['error'], 'SELECT') === false, 'Query failure returns safe error without fake empty details');
}
list($data, $db) = response('', 100, 10);
verify($data['data'] === array() && $data['recordsTotal'] === 6, 'Page beyond last transaction is empty without losing counts');
verify(count(array_filter($db->queries, function ($query) { return strpos($query[0], 'qty_order') !== false; })) === 0, 'Empty page does not fetch item details');

$db = new IndexDb();
$db->pdo->exec('INSERT INTO warehouse_adjustment VALUES (9, "TRM1", "duplicate", 1, "2026-10-11", 2, 0, 7, "2026-10-11", "Y", NULL, "outgoing stok", 1, 2)');
$fetch = (new IndexModel($db))->get_query_json_request_material('Kertas', 1, 'asc', 0, 10);
verify($fetch['totalData'] === 6 && $fetch['totalFiltered'] === 2 && count($fetch['query']->result_array()) === 2, 'Duplicate headers/details cannot multiply transaction counts');

$db = new IndexDb();
$db->pdo->exec("INSERT INTO accessories VALUES (16, 'EMPTY', '', 2);
    INSERT INTO warehouse_adjustment_detail VALUES (11, 'TRM2', 16, 0), (12, 'TRM2', 12, 0.3333)");
$_REQUEST = array('search' => array('value' => 'TRM2'));
ob_start(); (new IndexModel($db))->data_side_request_material(); $data = json_decode(ob_get_clean(), true);
verify($data['draw'] === 0 && $data['recordsFiltered'] === 1, 'Missing optional DataTables input uses safe defaults');
verify(strpos($data['data'][0][4], 'ID: 16') !== false && strpos($data['data'][0][4], '0,00 PCS') !== false, 'Empty material name uses fallback and zero quantity is retained');
verify(strpos($data['data'][0][4], '0,33 PCS') !== false, 'Fractional qty is rounded to two decimals');

$db = new IndexDb(); $model = new IndexModel($db);
for ($i = 20; $i < 175; $i++) {
    $code = 'BATCH-' . $i;
    $db->pdo->prepare('INSERT INTO warehouse_adjustment VALUES (?, ?, "Bulk", 1, "2026-10-20", 1, 0, 7, "2026-10-20", "Y", NULL, "outgoing stok", 1, 2)')->execute(array($i, $code));
    $db->pdo->prepare('INSERT INTO warehouse_adjustment_detail VALUES (?, ?, 11, 1)')->execute(array($i, $code));
}
$_REQUEST = array('length' => 10000, 'order' => array(array('column' => 1, 'dir' => 'asc')));
ob_start(); $model->data_side_request_material(); $data = json_decode(ob_get_clean(), true);
verify(count($data['data']) === 150 && $data['recordsTotal'] === 161, 'Page length capped to largest menu option');
$batches = array_values(array_filter($db->queries, function ($query) { return strpos($query[0], 'qty_order') !== false; }));
verify(count($batches) === 1 && count($batches[0][1]) === 150, '150 transaction page uses one bounded detail batch');
echo 'PASS Outgoing index: ' . $checks . " checks\n";
