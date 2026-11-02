<?php
// Tes report server-side terhadap database aplikasi (hanya membaca). Hanya jalan di container:
//   docker exec sendigs_ss php application/modules/penerimaan_uang/tests/Report_penerimaan_serverside_test.php
// Pembanding diambil dari SQL independen pada saat tes berjalan, bukan angka tetap, supaya tidak rusak saat data bertambah.
// Kegagalan memakai exit(1); assert() tidak dipakai karena di PHP 7.4 hanya mencetak warning dan tes tetap "lulus".
define('BASEPATH', '/var/www/html/system/');
require '/var/www/html/application/helpers/mockup_format_helper.php';
if (!function_exists('base_url')) { function base_url($p = '') { return '/' . $p; } }
define('APPPATH', '/var/www/html/application/');
define('ENVIRONMENT', 'development');

require_once '/var/www/html/system/core/Common.php';
require_once '/var/www/html/system/database/DB.php';

class Admin_Controller {}
class BF_Model {}
function has_permission($permission) { return true; }
require_once '/var/www/html/application/modules/penerimaan_uang/models/Penerimaan_uang_model.php';
require_once '/var/www/html/application/modules/penerimaan_uang/controllers/Penerimaan_uang.php';

class MockInput {
    private $postData;
    public function __construct($post = array()) { $this->postData = $post; }
    public function post($key = null) {
        if ($key === null) return $this->postData;
        return $this->postData[$key] ?? null;
    }
}

function check($ok, $message) {
    if (!$ok) { echo "FAIL: $message\n"; exit(1); }
}

$db = DB('default', TRUE);
$model = (new ReflectionClass('Penerimaan_uang_model'))->newInstanceWithoutConstructor();
$model->db = $db;
$controller = (new ReflectionClass('Penerimaan_uang'))->newInstanceWithoutConstructor();
$controller->db = $db;
$controller->Penerimaan_uang_model = $model;

function runTest($controller, $name, $post) {
    $controller->input = new MockInput($post);
    ob_start();
    $controller->get_report_penerimaan();
    $output = ob_get_clean();
    $json = json_decode($output, true);
    check(is_array($json), "$name - Invalid JSON: $output");
    return $json;
}
function money($text) { return (float)str_replace(array('Rp.', ',', ' '), '', $text); }
function post2026($extra = array()) {
    return array_merge(array('draw' => 1, 'start' => 0, 'length' => 10, 'year' => '2026', 'month' => '', 'company' => '', 'search' => '',
        'order' => array(array('column' => 1, 'dir' => 'desc'))), $extra);
}

// Pembanding independen: penerimaan tahun 2026 (tanggal bank, atau tanggal dibuat bila tanpa alokasi).
$expected = $db->query("SELECT COUNT(*) AS n, COALESCE(SUM(t.nominal_penerimaan_bank),0) AS total FROM (
    SELECT DISTINCT p.id, p.nominal_penerimaan_bank FROM tr_penerimaan_piutang p
    LEFT JOIN tr_alokasi_split s ON s.id = p.id_alokasi
    LEFT JOIN tr_alokasi_detail a ON a.id = COALESCE(s.id_alokasi_detail, p.id_alokasi)
    WHERE YEAR(a.tanggal_transaksi) = 2026 OR (a.tanggal_transaksi IS NULL AND YEAR(p.created_date) = 2026)) t")->row_array();
$expectedCount = (int)$expected['n'];
check($expectedCount > 0, 'Database harus punya penerimaan tahun 2026 untuk tes ini');

echo "=== TEST 1: Default 2026 (tanpa paging, mockup reportData) ===
";
$res1 = runTest($controller, 'Test 1', post2026());
check($res1['count_receipts'] === $expectedCount, "count_receipts {$res1['count_receipts']} harus sama dengan hitungan SQL independen $expectedCount");
check(abs(money($res1['total_nominal']) - round((float)$expected['total'])) <= 1 + $expectedCount, 'Total nominal harus sama dengan SUM SQL independen (' . $res1['total_nominal'] . ' vs ' . $expected['total'] . ')');
check(substr_count($res1['html'], '<tr>') >= $expectedCount, 'Satu baris per invoice (minimal satu baris per penerimaan)');
check(substr_count($res1['html'], 'rowspan=') >= 1 || $res1['count_invoices'] === $expectedCount, 'Kolom penerimaan di-rowspan bila ada beberapa invoice');
check(strpos($res1['html'], 'Lihat</a>') !== false, 'Tombol Lihat ada');
echo "PASS: report 2026 cocok dengan SQL independen ($expectedCount penerimaan).
";

echo "=== TEST 2: Search nomor invoice yang ada ===
";
$term = $db->query("SELECT i.no_invoice FROM tr_penerimaan_piutang p JOIN tr_penerimaan_piutang_detail d ON d.id_header = p.no_surat JOIN tr_invoicing i ON i.id = d.id_inv
    LEFT JOIN tr_alokasi_split s ON s.id = p.id_alokasi LEFT JOIN tr_alokasi_detail a ON a.id = COALESCE(s.id_alokasi_detail, p.id_alokasi)
    WHERE YEAR(a.tanggal_transaksi) = 2026 ORDER BY p.id DESC LIMIT 1")->row_array()['no_invoice'];
check($term !== '', 'Harus ada invoice untuk dicari');
$res2 = runTest($controller, 'Test 2', post2026(array('search' => '', 'custom_search' => $term)));
$searchExpected = (int)$db->query("SELECT COUNT(DISTINCT p.id) AS n FROM tr_penerimaan_piutang p
    JOIN tr_penerimaan_piutang_detail d ON d.id_header = p.no_surat JOIN tr_invoicing i ON i.id = d.id_inv
    WHERE i.no_invoice LIKE " . $db->escape('%' . $term . '%'))->row_array()['n'];
check($searchExpected >= 1 && $res2['count_receipts'] >= 1 && $res2['count_receipts'] <= $searchExpected, "Pencarian harus menemukan penerimaan (maks $searchExpected)");
check(strpos($res2['html'], htmlspecialchars($term)) !== false, 'Nomor invoice hasil pencarian harus tampil');
echo "PASS: Pencarian $term menemukan {$res2['count_receipts']} penerimaan.
";

echo "=== TEST 3: Filter company ===
";
foreach (array('VUCA', 'SUSTAIN') as $i => $company) {
    $res = runTest($controller, "Company $company", post2026(array('company' => $company)));
    check($res['count_receipts'] <= $expectedCount, "$company tidak boleh lebih banyak dari seluruh penerimaan");
    check($res['count_receipts'] >= 1, "$company harus punya penerimaan di data uji");
    check(strpos($res['html'], '<span class="lbl">' . $company . '</span>') !== false, "Baris $company harus berlabel $company");
    echo "PASS: Company $company: {$res['count_receipts']} penerimaan.
";
}

echo "=== TEST 4: Urutan tanggal naik ===
";
preg_match_all('~<td rowspan="\d+" style="white-space:nowrap">(\d{2}-[A-Za-z]+-\d{4})</td>~', $res1['html'], $m);
$dates = array_map(function ($d) { return strtotime($d); }, $m[1]);
$sorted = $dates; sort($sorted);
check(count($dates) >= 1 && $dates === $sorted, 'Tanggal penerimaan harus terurut naik');
echo "PASS: Urutan tanggal ASC benar.
";

echo "
ALL REPORT TESTS PASSED
";
