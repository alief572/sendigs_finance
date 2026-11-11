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
    'tgl_invoice_final' => '2026-10-05', 'id_spk_penawaran' => 'SPK-1', 'nm_customer' => 'C', 'nm_project' => 'P', 'nm_project_leader' => 'PL', 'nm_sales' => 'S')); };

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
    list(, $out) = run(array('filter_status' => 'invoiced'), array($row(array('tgl_invoice_final' => $case[0]))));
    ok($out['data'][0]['tanggal_invoice'] === $case[1], 'tanggal_invoice ' . json_encode($case[0]) . ' -> ' . $case[1]);
}
// Baris yang belum di-invoice (tab Konsultasi) tidak membawa tanggal.
list(, $out) = run(array('filter_status' => 'uninvoiced'), array($row(array('sts_invoice' => '0', 'no_invoice' => null, 'tgl_invoice_final' => null))));
ok($out['data'][0]['tanggal_invoice'] === '-' && $out['data'][0]['no_invoice'] === null, 'baris uninvoiced: tanggal "-"');


// ---- UMKM-FR-07: company 3 grup, Perlakuan PPN, Semua tahun, nilai v3, agregat card ----
foreach (array('STM' => '7,6,1', 'VUCA' => '4', 'SUSTAIN' => '3', 'stm' => '7,6,1') as $group => $ids) {
    list($db) = run(array('filter_status' => 'invoiced', 'filter_company' => $group));
    ok(in_array(array('COALESCE(e.id, g.id) IN (' . $ids . ')', null, false), $db->wheres, true), "company grup $group -> id $ids");
}
list($db) = run(array('filter_status' => 'invoiced', 'filter_company' => '4'));
ok(in_array(array('COALESCE(e.id, g.id) =', 4, false), $db->wheres, true), 'company id angka tetap didukung');
foreach (array('PKP', 'WAPU', 'KB', 'UMKM') as $ppn) {
    list($db) = run(array('filter_status' => 'invoiced', 'filter_ppn' => $ppn));
    ok(in_array(array('f.tax_treatment', $ppn, null), $db->wheres, true), "filter PPN $ppn disaring");
}
foreach (array('umkm', 'X; DROP TABLE a', '', null, array('UMKM')) as $bad) {
    list($db) = run(array('filter_status' => 'invoiced', 'filter_ppn' => $bad));
    ok(strpos(wheresText($db), 'tax_treatment') === false, 'filter PPN tidak valid diabaikan: ' . json_encode($bad));
}
list($db) = run(array('filter_status' => 'invoiced', 'filter_tahun' => 'all'));
ok(strpos(wheresText($db), 'YEAR(') === false, 'Semua tahun: tanpa filter YEAR');
list($db) = run(array('filter_status' => 'uninvoiced', 'filter_tahun' => 'all'));
ok(strpos(wheresText($db), 'YEAR(a.tgl_aktual_plan_tagih)) = ' . date('Y')) !== false, '"all" di luar tab Invoiced jatuh ke tahun berjalan (bukan YEAR = 0)');

$v3 = function ($over = array()) use ($row) { return $row($over + array('consulting_version' => 3, 'tax_treatment' => 'UMKM', 'total_nominal_jurnal' => '615000.00',
    'ppn_jurnal' => '0.00', 'tagihan_ppn_jurnal' => '615000.00', 'total_akhir_jurnal' => '615000.00', 'saldo_piutang' => '615000.00')); };
list(, $out) = run(array('filter_status' => 'invoiced'), array($v3()));
ok($out['data'][0]['tax_label'] === 'UMKM Non-PKP', 'baris Invoiced: label Perlakuan PPN');
ok($out['data'][0]['nilai'] === array('sales' => 61500000, 'ppn' => 0, 'gross' => 61500000, 'saldo' => 61500000), 'baris Invoiced: nilai UMKM (PPN 0, tagihan = penjualan)');
list(, $out) = run(array('filter_status' => 'invoiced'), array($row(array('consulting_version' => 0, 'tax_treatment' => null))));
ok($out['data'][0]['nilai'] === null && $out['data'][0]['tax_label'] === '-', 'invoice lama: nilai null, label "-"');
list(, $out) = run(array('filter_status' => 'uninvoiced'), array($row(array('sts_invoice' => '0'))));
ok(!array_key_exists('nilai', $out['data'][0]) && !array_key_exists('tax_label', $out['data'][0]), 'tab Konsultasi tidak membawa kolom nilai/PPN');

// Agregat card = jumlah baris yang sama dengan ekspor (UMKM-NFR-05).
$rows = array($v3(), $v3(array('tax_treatment' => 'PKP', 'total_nominal_jurnal' => '1000.55', 'ppn_jurnal' => '110.06', 'tagihan_ppn_jurnal' => '1110.61', 'saldo_piutang' => '500.00')),
    $row(array('consulting_version' => 0)));
$t = Invoicing_model::invoiced_totals($rows);
ok($t['count'] === 3, 'card: jumlah invoice termasuk invoice lama');
ok($t['sales'] === 61500000 + 100055 && $t['ppn'] === 11006 && $t['gross'] === 61500000 + 111061 && $t['saldo'] === 61500000 + 50000, 'card: jumlah nominal hanya dari invoice v3, dalam sen tanpa selisih');
ok(Invoicing_model::invoiced_totals(array()) === array('count' => 0, 'gross' => 0, 'sales' => 0, 'ppn' => 0, 'saldo' => 0), 'card: kosong = 0');

// invoiced_rows memakai filter yang sama dengan get_data_spk.
function runRows($f, $rows = array()) {
    $m = (new ReflectionClass('Invoicing_model'))->newInstanceWithoutConstructor();
    $m->db = new RecDb; $m->db->rows = $rows;
    return array($m->db, $m->invoiced_rows($f));
}
list($db, $got) = runRows(array('tahun' => '2026', 'bulan' => '10', 'company' => 'STM', 'ppn' => 'UMKM', 'search' => 'JTEKT'), array(1, 2));
ok($got === array(1, 2), 'invoiced_rows mengembalikan baris DB');
ok(in_array(array('a.sts_invoice', '1', null), $db->wheres, true) && in_array(array('YEAR(f.tanggal_invoice) =', 2026, false), $db->wheres, true), 'invoiced_rows: hanya Invoiced pada tahun terpilih');
ok(in_array(array('MONTH(f.tanggal_invoice) =', 10, false), $db->wheres, true) && in_array(array('f.tax_treatment', 'UMKM', null), $db->wheres, true)
    && in_array(array('COALESCE(e.id, g.id) IN (7,6,1)', null, false), $db->wheres, true), 'invoiced_rows: bulan, PPN, company mengikuti filter');
ok($db->orders === array('f.tanggal_invoice desc', 'a.id desc'), 'invoiced_rows: tanggal invoice terbaru dulu');
list($db) = runRows(array('tahun' => 'all'));
ok(strpos(wheresText($db), 'YEAR(') === false, 'invoiced_rows: Semua tahun tanpa YEAR');
list($db) = runRows(array('tahun' => 'x; DROP', 'bulan' => '13', 'company' => '1 OR 1=1', 'ppn' => 'zzz'));
ok(in_array(array('YEAR(f.tanggal_invoice) =', (int)date('Y'), false), $db->wheres, true) && strpos(wheresText($db), 'MONTH(') === false
    && strpos(wheresText($db), 'COALESCE') === false && strpos(wheresText($db), 'tax_treatment') === false, 'invoiced_rows: filter tidak valid diabaikan, tahun jatuh ke tahun berjalan');
foreach (array('PKP' => 'PKP', 'WAPU' => 'WAPU', 'KB' => 'Kawasan Berikat', 'UMKM' => 'UMKM Non-PKP', '' => '-', null => '-', 'xyz' => 'XYZ') as $code => $label) {
    ok(Invoicing_model::tax_label_for($code) === $label, 'tax_label_for ' . json_encode($code));
}

echo 'PASS Invoicing list filter: ' . $GLOBALS['n'] . " checks (model asli, DB perekam; SQL tidak dieksekusi)\n";
