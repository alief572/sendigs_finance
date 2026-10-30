<?php
// Tes report server-side terhadap database aplikasi (hanya membaca). Hanya jalan di container:
//   docker exec sendigs_ss php application/modules/penerimaan_uang/tests/Report_penerimaan_serverside_test.php
// Pembanding diambil dari SQL independen pada saat tes berjalan, bukan angka tetap, supaya tidak rusak saat data bertambah.
// Kegagalan memakai exit(1); assert() tidak dipakai karena di PHP 7.4 hanya mencetak warning dan tes tetap "lulus".
define('BASEPATH', '/var/www/html/system/');
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

echo "=== TEST 1: Default 2026 Page 1 ===\n";
$res1 = runTest($controller, 'Test 1', post2026());
check($res1['recordsFiltered'] === $expectedCount, "recordsFiltered {$res1['recordsFiltered']} harus sama dengan hitungan SQL independen $expectedCount");
check(count($res1['data']) === min(10, $expectedCount), 'Halaman pertama berisi min(10, total) baris');
check($res1['data'][0]['no'] === 1, 'Nomor baris pertama harus 1');
check(abs(money($res1['total_nominal']) - (float)$expected['total']) < 0.01, 'Total nominal report harus sama dengan SUM SQL independen (' . $res1['total_nominal'] . ' vs ' . $expected['total'] . ')');
echo "PASS: Halaman 1 dan total nominal cocok dengan SQL independen ($expectedCount penerimaan).\n";

echo "=== TEST 2: Search '043/VSB' ===\n";
$res2 = runTest($controller, 'Test 2', post2026(array('search' => '', 'custom_search' => '043/VSB')));
$searchExpected = (int)$db->query("SELECT COUNT(DISTINCT p.id) AS n FROM tr_penerimaan_piutang p
    JOIN tr_penerimaan_piutang_detail d ON d.id_header = p.no_surat JOIN tr_invoicing i ON i.id = d.id_inv
    WHERE i.no_invoice LIKE '%043/VSB%'")->row_array()['n'];
check($searchExpected >= 1 && $res2['recordsFiltered'] >= $searchExpected, "Pencarian harus menemukan minimal $searchExpected penerimaan");
check(strpos($res2['data'][0]['no_invoice'], '043/VSB') !== false, 'Nomor invoice hasil pencarian harus cocok');
echo "PASS: Pencarian 043/VSB menemukan {$res2['recordsFiltered']} penerimaan.\n";

echo "=== TEST 3: Filter company ===\n";
foreach (array('VUCA', 'SUSTAIN') as $i => $company) {
    $res = runTest($controller, "Company $company", post2026(array('draw' => 3 + $i, 'company' => $company, 'length' => 100)));
    check($res['recordsFiltered'] <= $expectedCount, "$company tidak boleh lebih banyak dari seluruh penerimaan");
    check($res['recordsFiltered'] >= 1, "$company harus punya penerimaan di data uji");
    foreach ($res['data'] as $row) {
        check(strpos(strip_tags($row['company']), $company) !== false, "Setiap baris $company harus berlabel $company, dapat: " . strip_tags($row['company']));
    }
    echo "PASS: Company $company: {$res['recordsFiltered']} penerimaan, semua berlabel $company.\n";
}

echo "=== TEST 4: Pagination ===\n";
if ($expectedCount >= 20) {
    $res5 = runTest($controller, 'Test 5', post2026(array('draw' => 5, 'start' => 10)));
    check(count($res5['data']) === 10, 'Halaman 2 harus berisi 10 baris');
    check($res5['data'][0]['no'] === 11 && $res5['data'][9]['no'] === 20, 'Halaman 2 bernomor 11 sampai 20');
    $first = array_column($res1['data'], 'detail_bank');
    check(count(array_intersect($first, array_column($res5['data'], 'detail_bank'))) < 10, 'Halaman 2 tidak boleh identik dengan halaman 1');
    echo "PASS: Offset halaman 2 benar (baris 11 sampai 20).\n";
} else {
    echo "SKIP: data kurang dari 20 baris.\n";
}

echo "=== TEST 5: Sorting nominal (kolom 8) ASC ===\n";
$res6 = runTest($controller, 'Test 6', post2026(array('draw' => 6, 'order' => array(array('column' => 8, 'dir' => 'asc')))));
$amounts = array_map(function ($row) { return money($row['penerimaan_bank']); }, $res6['data']);
$sorted = $amounts; sort($sorted);
check(count($amounts) > 1 && $amounts === $sorted, 'Nominal harus terurut naik: ' . implode(', ', $amounts));
echo "PASS: Urutan nominal ASC benar.\n";

echo "\nALL REPORT SERVERSIDE TESTS PASSED\n";
