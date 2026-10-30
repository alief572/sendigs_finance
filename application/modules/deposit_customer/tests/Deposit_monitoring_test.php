<?php
// Test Suite for Deposit Customer Monitoring Module
define('BASEPATH', __DIR__);
define('DBCNL', 'consultant_fixture');
set_error_handler(function ($severity, $message, $file, $line) { throw new ErrorException($message, 0, $severity, $file, $line); });

class Admin_Controller {}
class BF_Model { public $db; public function __construct() {} }
function has_permission($p) { return true; }

require_once dirname(__DIR__, 2) . '/invoicing/libraries/Invoice_calculation.php';
require_once dirname(__DIR__, 2) . '/penerimaan_uang/libraries/Penerimaan_calculation.php';
require_once dirname(__DIR__) . '/models/Deposit_customer_model.php';

function checkAssert($cond, $msg) {
    if (!$cond) throw new RuntimeException("ASSERTION FAILED: " . $msg);
}

class MockResult {
    private $rows;
    public function __construct($rows = array()) { $this->rows = $rows; }
    public function row_array() { return $this->rows ? $this->rows[0] : array(); }
    public function result_array() { return $this->rows; }
    public function num_rows() { return count($this->rows); }
}

class MockDb {
    public $customer_deposits = array();
    public $deposit_mutasi = array();
    private $where = array();

    public function select($cols) { return $this; }
    public function from($table) { return $this; }
    public function join($table, $cond, $type = '') { return $this; }
    public function where($k, $v = null) { $this->where[$k] = $v; return $this; }
    public function order_by($k, $dir = 'ASC') { return $this; }

    // Emulates: mutasi LEFT JOIN deposit, d.status != MENUNGGU_PEMBAYARAN, optional m.id_customer, ORDER BY tanggal,id.
    public function get() {
        $deps = array();
        foreach ($this->customer_deposits as $d) $deps[$d['id']] = $d;
        $rows = array();
        foreach ($this->deposit_mutasi as $m) {
            $d = $deps[$m['id_deposit']] ?? null;
            if ($d === null || $d['status'] === 'MENUNGGU_PEMBAYARAN') continue;
            if (isset($this->where['m.id_customer']) && $m['id_customer'] != $this->where['m.id_customer']) continue;
            $rows[] = array_merge($m, array('nm_customer' => $d['nm_customer'], 'no_deposit' => $d['no_deposit'], 'no_referensi' => $d['no_referensi'], 'tipe_sumber' => $d['tipe_sumber'], 'id_company' => $d['id_company']));
        }
        usort($rows, function ($a, $b) { return strcmp($a['tanggal'] . sprintf('%06d', $a['id']), $b['tanggal'] . sprintf('%06d', $b['id'])); });
        $this->where = array();
        return new MockResult($rows);
    }
}

echo "=== DEPOSIT CUSTOMER MONITORING TEST SUITE ===\n\n";

$dep = function ($id, $cust, $nm, $company, $tipe, $status, $tgl) {
    return array('id' => $id, 'no_deposit' => 'DEP/' . $id, 'id_customer' => $cust, 'nm_customer' => $nm, 'id_company' => $company, 'tanggal' => $tgl, 'tipe_sumber' => $tipe, 'no_referensi' => 'REF' . $id, 'status' => $status);
};
$mut = function ($id, $idDep, $cust, $no, $tgl, $jenis, $nominal) {
    return array('id' => $id, 'id_deposit' => $idDep, 'id_customer' => $cust, 'no_transaksi' => $no, 'tanggal' => $tgl, 'jenis_mutasi' => $jenis, 'nominal' => $nominal, 'keterangan' => '');
};

$db = new MockDb();
// CUST-001 (dua company): kwitansi 10jt (company 7), lebih bayar 5jt (company 3), dipakai 5jt + 3jt.
// CUST-002: kwitansi 2jt aktif. CUST-003: kwitansi belum dibayar (tidak boleh dihitung).
$db->customer_deposits = array(
    $dep(1, 'CUST-001', 'PT Mitra Sejahtera', '7', 'KWITANSI_DEPOSIT', 'TERPAKAI_SEBAGIAN', '2026-10-01'),
    $dep(2, 'CUST-001', 'PT Mitra Sejahtera', '3', 'LEBIH_BAYAR', 'LUNAS_TERPAKAI', '2026-10-05'),
    $dep(3, 'CUST-002', 'CV Sumber Makmur', '7', 'KWITANSI_DEPOSIT', 'AKTIF', '2026-10-10'),
    $dep(4, 'CUST-003', 'PT Belum Bayar', '7', 'KWITANSI_DEPOSIT', 'MENUNGGU_PEMBAYARAN', '2026-10-12'),
);
$db->deposit_mutasi = array(
    $mut(1, 1, 'CUST-001', 'KD/2026/10/0001', '2026-10-01', 'IN', '10000000.00'),
    $mut(2, 2, 'CUST-001', '00002/REC', '2026-10-05', 'IN', '5000000.00'),
    $mut(3, 2, 'CUST-001', '00003/USE', '2026-10-06', 'OUT', '5000000.00'),
    $mut(4, 1, 'CUST-001', '00004/USE', '2026-10-07', 'OUT', '3000000.00'),
    $mut(5, 3, 'CUST-002', 'KD/2026/10/0002', '2026-10-10', 'IN', '2000000.00'),
    $mut(6, 4, 'CUST-003', 'KD/2026/10/0003', '2026-10-12', 'IN', '9000000.00'),
);

$model = new Deposit_customer_model();
$model->db = $db;

echo "--- TEST 1: Customer summary = IN - OUT lintas company, kwitansi belum dibayar tidak dihitung ---\n";
$summary = $model->get_customer_summary();
checkAssert(count($summary) === 2, 'Customer dengan kwitansi belum dibayar tidak muncul');
$by = array();
foreach ($summary as $c) $by[$c['id_customer']] = $c;
$c1 = $by['CUST-001'];
checkAssert(Penerimaan_calculation::cents($c1['total_nominal_awal']) === 1500000000, 'CUST-001 total nilai 15.000.000 (dua company digabung)');
checkAssert(Penerimaan_calculation::cents($c1['total_dari_kwitansi']) === 1000000000, 'CUST-001 dari kwitansi 10.000.000');
checkAssert(Penerimaan_calculation::cents($c1['total_dari_lebih_bayar']) === 500000000, 'CUST-001 dari lebih bayar 5.000.000');
checkAssert(Penerimaan_calculation::cents($c1['total_nominal_terpakai']) === 800000000, 'CUST-001 terpakai 8.000.000');
checkAssert(Penerimaan_calculation::cents($c1['sisa_saldo']) === 700000000, 'CUST-001 sisa = 7.000.000');
checkAssert($c1['jumlah_deposit'] === 2, 'CUST-001 punya 2 deposit masuk');
checkAssert($c1['last_deposit_date'] === '2026-10-05', 'CUST-001 deposit terakhir 2026-10-05');
checkAssert(!isset($c1['id_company']), 'Tidak ada dimensi company pada ringkasan');
checkAssert(count($model->get_customer_summary('sumber')) === 1, 'Pencarian nama customer');
echo "PASS: customer summary\n\n";

echo "--- TEST 2: KPI dari sumber yang sama dengan tabel ---\n";
$kpi = $model->get_kpi_summary();
checkAssert(Penerimaan_calculation::cents($kpi['total_saldo']) === 900000000, 'Total sisa saldo 9.000.000');
checkAssert($kpi['total_customer_aktif'] === 2, 'Dua customer punya saldo');
checkAssert($kpi['cnt_masuk'] === 3, 'Tiga deposit masuk');
checkAssert(Penerimaan_calculation::cents($kpi['total_masuk']) === 1700000000, 'Total masuk 17.000.000');
checkAssert(Penerimaan_calculation::cents($kpi['total_keluar']) === 800000000, 'Total terpakai 8.000.000');
echo "PASS: KPI\n\n";

echo "--- TEST 3: Ledger per customer dan label sumber ---\n";
$ledger = $model->get_ledger_rows('CUST-001');
checkAssert(count($ledger) === 4, 'CUST-001 punya 4 mutasi');
checkAssert(Deposit_customer_model::source_label($ledger[0]) === 'Penerimaan Kwitansi Deposit', 'IN kwitansi');
checkAssert(Deposit_customer_model::source_label($ledger[1]) === 'Lebih Bayar (Penerimaan Invoice)', 'IN lebih bayar');
checkAssert(Deposit_customer_model::source_label($ledger[2]) === 'Deposit Dipakai (Penerimaan Invoice)', 'OUT dipakai');
checkAssert(count($model->get_ledger_rows('CUST-003')) === 0, 'Kwitansi belum dibayar tidak punya mutasi terlihat');
echo "PASS: ledger\n\n";

echo "ALL DEPOSIT MONITORING TESTS PASSED 100%!\n";
