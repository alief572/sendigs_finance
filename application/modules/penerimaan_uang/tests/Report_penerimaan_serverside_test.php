<?php
// Tes DataTables server-side (report dan dua daftar draft) terhadap database aplikasi (hanya membaca). Hanya jalan di container:
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

function runTest($controller, $name, $post, $endpoint = 'get_report_penerimaan') {
    $controller->input = new MockInput($post);
    ob_start();
    $controller->$endpoint();
    $output = ob_get_clean();
    $json = json_decode($output, true);
    check(is_array($json), "$name - Invalid JSON: $output");
    return $json;
}
function money($text) { return (float)str_replace(array('Rp.', ',', ' '), '', $text); }
function post2026($extra = array()) {
    return array_merge(array('draw' => 1, 'start' => 0, 'length' => 10, 'year' => '2026', 'month' => '', 'company' => '', 'search' => '',
        'order' => array(array('column' => 1, 'dir' => 'asc'))), $extra);
}

// Pembanding independen: penerimaan tahun 2026 (tanggal bank, atau tanggal dibuat bila tanpa alokasi).
$expected = $db->query("SELECT COUNT(*) AS n, COALESCE(SUM(t.nominal_penerimaan_bank),0) AS total FROM (
    SELECT DISTINCT p.id, p.nominal_penerimaan_bank FROM tr_penerimaan_piutang p
    LEFT JOIN tr_alokasi_split s ON s.id = p.id_alokasi
    LEFT JOIN tr_alokasi_detail a ON a.id = COALESCE(s.id_alokasi_detail, p.id_alokasi)
    WHERE YEAR(a.tanggal_transaksi) = 2026 OR (a.tanggal_transaksi IS NULL AND YEAR(p.created_date) = 2026)) t")->row_array();
$expectedCount = (int)$expected['n'];
check($expectedCount > 0, 'Database harus punya penerimaan tahun 2026 untuk tes ini');

echo "=== TEST 1: Report 2026 halaman 1 ===\n";
$res1 = runTest($controller, 'Test 1', post2026());
check($res1['draw'] === 1, 'draw dikembalikan apa adanya');
check($res1['recordsFiltered'] === $expectedCount, "recordsFiltered {$res1['recordsFiltered']} harus sama dengan hitungan SQL independen $expectedCount");
check(count($res1['data']) === min(10, $expectedCount), 'Halaman 1 berisi min(10, total) baris, satu baris per penerimaan');
check($res1['data'][0]['no'] === 1, 'Nomor baris pertama harus 1');
check(abs(money($res1['total_nominal']) - round((float)$expected['total'])) <= 1 + $expectedCount, 'Total nominal harus sama dengan SUM SQL independen (' . $res1['total_nominal'] . ' vs ' . $expected['total'] . ')');
check(strpos($res1['data'][0]['action'], 'title="Lihat"') !== false, 'Tombol Lihat ada');
check(strpos(json_encode($res1['data']), 'rowspan') === false, 'DataTables tidak boleh menerima rowspan');
check(isset($res1['counts']['invoice'], $res1['counts']['deposit'], $res1['counts']['report']), 'Angka tab ikut dikirim');
echo "PASS: report 2026 cocok dengan SQL independen ($expectedCount penerimaan).\n";

echo "=== TEST 2: Search nomor invoice yang ada ===\n";
$term = $db->query("SELECT i.no_invoice FROM tr_penerimaan_piutang p JOIN tr_penerimaan_piutang_detail d ON d.id_header = p.no_surat JOIN tr_invoicing i ON i.id = d.id_inv
    LEFT JOIN tr_alokasi_split s ON s.id = p.id_alokasi LEFT JOIN tr_alokasi_detail a ON a.id = COALESCE(s.id_alokasi_detail, p.id_alokasi)
    WHERE YEAR(a.tanggal_transaksi) = 2026 ORDER BY p.id DESC LIMIT 1")->row_array()['no_invoice'];
check($term !== '', 'Harus ada invoice untuk dicari');
$res2 = runTest($controller, 'Test 2', post2026(array('search' => array('value' => $term))));
$searchExpected = (int)$db->query("SELECT COUNT(DISTINCT p.id) AS n FROM tr_penerimaan_piutang p
    JOIN tr_penerimaan_piutang_detail d ON d.id_header = p.no_surat JOIN tr_invoicing i ON i.id = d.id_inv
    WHERE i.no_invoice LIKE " . $db->escape('%' . $term . '%'))->row_array()['n'];
check($searchExpected >= 1 && $res2['recordsFiltered'] >= 1 && $res2['recordsFiltered'] <= $searchExpected, "Pencarian harus menemukan penerimaan (maks $searchExpected)");
check(strpos(json_encode($res2['data']), trim(json_encode($term), '"')) !== false, 'Nomor invoice hasil pencarian harus tampil');
echo "PASS: Pencarian $term menemukan {$res2['recordsFiltered']} penerimaan.\n";

echo "=== TEST 3: Filter company ===\n";
foreach (array('VUCA', 'SUSTAIN') as $company) {
    $res = runTest($controller, "Company $company", post2026(array('company' => $company, 'length' => 100)));
    check($res['recordsFiltered'] >= 1 && $res['recordsFiltered'] <= $expectedCount, "$company harus punya penerimaan dan tidak lebih dari seluruhnya");
    foreach ($res['data'] as $row) check(strpos($row['company'], '>' . $company . '<') !== false, "Setiap baris $company harus berlabel $company");
    echo "PASS: Company $company: {$res['recordsFiltered']} penerimaan.\n";
}

echo "=== TEST 4: Paging dan urutan tanggal ===\n";
if ($expectedCount >= 20) {
    $page2 = runTest($controller, 'Page 2', post2026(array('draw' => 2, 'start' => 10)));
    check($page2['data'][0]['no'] === 11 && count($page2['data']) === 10, 'Halaman 2 bernomor 11 sampai 20');
    check($page2['data'][0]['detail_bank'] !== $res1['data'][0]['detail_bank'] || $page2['data'][0]['customer'] !== $res1['data'][0]['customer'], 'Halaman 2 tidak identik dengan halaman 1');
}
$dates = array_map(function ($row) { return strtotime($row['tanggal']); }, $res1['data']);
$sorted = $dates; sort($sorted);
check(count($dates) >= 1 && $dates === $sorted, 'Tanggal penerimaan harus terurut naik');
$desc = runTest($controller, 'Desc', post2026(array('order' => array(array('column' => 1, 'dir' => 'desc')))));
check(strtotime($desc['data'][0]['tanggal']) >= strtotime($res1['data'][0]['tanggal']), 'Urutan desc dimulai dari tanggal terbaru');
// Tanpa parameter urut, report harus menampilkan data terbaru lebih dulu (tanggal turun, id turun pada tanggal yang sama).
$noOrder = post2026(array('length' => 100)); unset($noOrder['order']);
$default = runTest($controller, 'Default order', $noOrder);
$defDates = array_map(function ($row) { return strtotime($row['tanggal']); }, $default['data']);
$defSorted = $defDates; rsort($defSorted);
check(count($defDates) > 1 && $defDates === $defSorted, 'Urutan bawaan report harus tanggal terbaru lebih dulu');
$idsByDate = array();
foreach ($default['data'] as $row) { preg_match('/data-id="(\d+)"/', $row['action'], $mm); $idsByDate[strtotime($row['tanggal'])][] = (int)($mm[1] ?? 0); }
foreach ($idsByDate as $ids) { $desc = $ids; rsort($desc); check($ids === $desc, 'Pada tanggal yang sama id terbesar tampil lebih dulu'); }
echo "PASS: Paging dan urutan tanggal benar.\n";

// ---- Daftar draft (tab invoice dan deposit) ----
foreach (array('invoice' => 1, 'deposit' => 8) as $kind => $jenis) {
    echo "=== TEST 5: Draft $kind ===\n";
    $from = "FROM tr_alokasi_split s JOIN tr_alokasi_detail a ON a.id = s.id_alokasi_detail LEFT JOIN ms_bank b ON b.id = a.tipe_bank
        WHERE s.jenis_alokasi = $jenis AND YEAR(a.tanggal_transaksi) >= 2025 AND NOT EXISTS (SELECT 1 FROM tr_penerimaan_piutang p WHERE p.id_alokasi = s.id)";
    $draftExpected = (int)$db->query("SELECT COUNT(DISTINCT s.id) AS n $from")->row_array()['n'];
    $draftPost = array('draw' => 7, 'start' => 0, 'length' => 10, 'receipt_kind' => $kind, 'bank' => '', 'search' => array('value' => ''), 'order' => array(array('column' => 1, 'dir' => 'asc')));
    $d1 = runTest($controller, "Draft $kind", $draftPost, 'draft_rows');
    check($d1['draw'] === 7 && $d1['recordsTotal'] === $draftExpected && $d1['recordsFiltered'] === $draftExpected, "Draft $kind: total {$d1['recordsTotal']} harus sama dengan SQL independen $draftExpected");
    check(count($d1['data']) === min(10, $draftExpected), "Draft $kind: halaman 1 berisi min(10, total) baris");
    check($d1['counts'][$kind] === $draftExpected, "Draft $kind: angka tab sama dengan total");
    if ($draftExpected > 0) {
        $first = $d1['data'][0];
        check($first['no'] === 1 && $first['id'] > 0 && isset($first['tgl'], $first['bank'], $first['ket'], $first['nominal']), "Draft $kind: baris lengkap");
        // Filter bank: hanya baris dari bank itu, dan hitungan sama dengan SQL independen.
        $bankRow = $db->query("SELECT b.id, COUNT(DISTINCT s.id) AS n $from AND b.id IS NOT NULL GROUP BY b.id ORDER BY n DESC LIMIT 1")->row_array();
        if ($bankRow) {
            $d2 = runTest($controller, "Draft $kind bank", array_merge($draftPost, array('bank' => (string)$bankRow['id'], 'length' => 100)), 'draft_rows');
            check($d2['recordsFiltered'] === (int)$bankRow['n'] && $d2['recordsTotal'] === $draftExpected, "Draft $kind: filter bank memberi {$bankRow['n']} baris, total tetap $draftExpected");
        }
        // Search: kata dari keterangan baris pertama harus menemukan baris itu.
        $word = preg_split('/\s+/', trim($first['ket']))[0];
        if (strlen($word) >= 3) {
            $d3 = runTest($controller, "Draft $kind search", array_merge($draftPost, array('search' => array('value' => $word), 'length' => 100)), 'draft_rows');
            check($d3['recordsFiltered'] >= 1 && in_array($first['id'], array_column($d3['data'], 'id')), "Draft $kind: pencarian '$word' menemukan baris pertama");
        }
        // Urutan nominal naik.
        $d4 = runTest($controller, "Draft $kind nominal", array_merge($draftPost, array('order' => array(array('column' => 4, 'dir' => 'asc')))), 'draft_rows');
        $nom = array_column($d4['data'], 'nominal'); $sortedNom = $nom; sort($sortedNom);
        check($nom === $sortedNom, "Draft $kind: nominal terurut naik");
    }
    echo "PASS: Draft $kind ($draftExpected baris) cocok dengan SQL independen, filter bank, search, dan urutan benar.\n";
}

echo "\nALL REPORT AND DRAFT SERVERSIDE TESTS PASSED\n";
