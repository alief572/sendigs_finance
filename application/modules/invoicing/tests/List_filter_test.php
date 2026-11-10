<?php
// Invoicing_model::get_data_spk asli + DB perekam: filter tab Invoiced (INV-FR-02) dan keluaran tanggal. Tanpa DB; SQL tidak dieksekusi.
define('BASEPATH', __DIR__);
define('DBCNL', 'consultant_fixture');
set_error_handler(function ($s, $m, $f, $l) { throw new ErrorException($m, 0, $s, $f, $l); });
class BF_Model {}
function base_url($p = '') { return '/' . $p; }
function int_to_roman($m) { return 'X'; }
require dirname(__DIR__) . '/libraries/Invoice_consulting.php';
require dirname(__DIR__) . '/models/Invoicing_model.php';
function ok($c, $m) { if (!$c) { throw new RuntimeException($m); } $GLOBALS['n'] = ($GLOBALS['n'] ?? 0) + 1; }

class RecDb {
    public $wheres = array(), $orders = array(), $rows = array();
    function __call($n, $a) { return $this; }
    function select($s) { return $this; } function from($t) { return $this; } function join($t, $c, $j = '') { return $this; }
    function where($k, $v = null, $esc = null) { $this->wheres[] = array($k, $v, $esc); return $this; }
    function group_start() { return $this; } function group_end() { return $this; } function like() { return $this; } function or_like() { return $this; }
    function group_by($g) { return $this; } function limit($a, $b = 0) { return $this; }
    function order_by($f, $d = '') { $this->orders[] = trim($f . ' ' . $d); return $this; }
    function count_all_results() { return count($this->rows); }
    function get() { return new RecResult($this->rows); }
    function get_where($t, $w) { return new RecResult(array()); }
}
class RecResult { private $r; function __construct($r) { $this->r = $r; } function result() { return $this->r; } function row() { return null; } }
class RecInput {
    public $v = array();
    function post($k = null) { return $k === null ? $this->v : ($this->v[$k] ?? null); }
}
function run($post, $rows = array()) {
    $m = (new ReflectionClass('Invoicing_model'))->newInstanceWithoutConstructor();
    $m->db = new RecDb; $m->db->rows = $rows; $m->input = new RecInput;
    $m->input->v = $post + array('draw' => 1, 'length' => 10, 'start' => 0);
    ob_start(); $m->get_data_spk(); $out = json_decode(ob_get_clean(), true);
    return array($m->db, $out);
}
function wheresText($db) { return json_encode($db->wheres); }
$row = function ($over = array()) { return (object) ($over + array('id' => 5, 'sts_invoice' => '1', 'id_company' => 4, 'company_name' => 'STM', 'no_invoice' => '001/STM/2026',
    'tgl_invoice' => '2026-10-05', 'id_spk_penawaran' => 'SPK-1', 'nm_customer' => 'C', 'nm_project' => 'P', 'nm_project_leader' => 'PL', 'nm_sales' => 'S')); };

// Bulan + company valid -> ikut menyaring; angka dipaksa int, tidak disisipkan sebagai teks.
list($db) = run(array('filter_status' => 'invoiced', 'filter_tahun' => '2026', 'filter_bulan' => '10', 'filter_company' => '4'));
ok(in_array(array('MONTH(f.tanggal_invoice) =', 10, false), $db->wheres, true), 'bulan 10 disaring sebagai int');
ok(in_array(array('COALESCE(e.id, g.id) =', 4, false), $db->wheres, true), 'company 4 disaring sebagai int');
ok(in_array(array('a.sts_invoice', '1', null), $db->wheres, true), 'status invoiced tetap disaring');
ok($db->orders[0] === 'f.tanggal_invoice desc', 'tab Invoiced: tanggal invoice terbaru dulu');

// Nilai tidak valid diabaikan (bukan error, bukan SQL).
foreach (array('0', '13', '1; DROP TABLE x', array('1'), '', null) as $bad) {
    list($db) = run(array('filter_status' => 'invoiced', 'filter_bulan' => $bad));
    ok(strpos(wheresText($db), 'MONTH(') === false, 'bulan tidak valid diabaikan: ' . json_encode($bad));
}
foreach (array('4 OR 1=1', 'abc', '-1', array('4'), '', null, '12345678901') as $bad) {
    list($db) = run(array('filter_status' => 'invoiced', 'filter_company' => $bad));
    ok(strpos(wheresText($db), 'COALESCE(e.id') === false, 'company tidak valid diabaikan: ' . json_encode($bad));
}
foreach (array('1', '12') as $good) { list($db) = run(array('filter_bulan' => $good)); ok(strpos(wheresText($db), 'MONTH(') !== false, "bulan $good diterima"); }

// Tab Konsultasi (uninvoiced): urutan lama tetap, tanpa filter bulan/company bila tidak dikirim.
list($db) = run(array('filter_status' => 'uninvoiced', 'filter_tahun' => '2026'));
ok($db->orders[0] === 'a.sts_invoice asc' && $db->orders[1] === 'a.id desc', 'urutan tab Konsultasi tidak berubah');
ok(in_array(array('a.sts_invoice !=', '1', null), $db->wheres, true), 'status uninvoiced tetap disaring');
ok(strpos(wheresText($db), 'MONTH(') === false && strpos(wheresText($db), 'COALESCE(e.id') === false, 'tanpa filter tambahan bila tidak dikirim');

// Keluaran tanggal invoice: dd/mm/yyyy, kosong -> '-'.
foreach (array(array('2026-10-05', '05/10/2026'), array(null, '-'), array('', '-'), array('0000-00-00', '-')) as $case) {
    list(, $out) = run(array('filter_status' => 'invoiced'), array($row(array('tgl_invoice' => $case[0]))));
    ok($out['data'][0]['tanggal_invoice'] === $case[1], 'tanggal_invoice ' . json_encode($case[0]) . ' -> ' . $case[1]);
}
// Baris yang belum di-invoice (tab Konsultasi) tidak membawa tanggal.
list(, $out) = run(array('filter_status' => 'uninvoiced'), array($row(array('sts_invoice' => '0', 'no_invoice' => null, 'tgl_invoice' => null))));
ok($out['data'][0]['tanggal_invoice'] === '-' && $out['data'][0]['no_invoice'] === null, 'baris uninvoiced: tanggal "-"');

echo 'PASS Invoicing list filter: ' . $GLOBALS['n'] . " checks (model asli, DB perekam; SQL tidak dieksekusi)\n";
