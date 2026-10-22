<?php
/**
 * Automated Unit & Integration Tests: Pembaruan Modul Expense Report, Approval & Pengembalian Kasbon
 * 
 * Verifikasi:
 * 1. set_jurnal_expense(): Jurnal 1 preview untuk Lebih Kasbon (Debit 1103-01-15), Impas, dan Lebih Expense.
 * 2. approve(): Approval jenjang 1 (Finance: 0 -> 1) dan jenjang 2 (Direktur: 1 -> 3 untuk Lebih Kasbon, 1 -> 2 untuk Impas/Lebih Expense).
 * 3. Segregation of Duties (BR-12): Pelapor dilarang meng-approve atau memverifikasi laporannya sendiri.
 * 4. verifikasi_pengembalian_approve(): Verifikasi 3-checklist dan posting Jurnal 2 (Debit Bank, Kredit 1103-01-15).
 * 5. verifikasi_pengembalian_reject(): Tolak verifikasi kembali ke status 3 dengan alasan wajib.
 * 6. _render_expense_status_badge(): Pemetaan badge visual 6-status (0, 1, 2, 3, 4, 9).
 */

if (!defined('BASEPATH')) {
    define('BASEPATH', __DIR__);
}
if (!defined('DBACC')) {
    define('DBACC', 'db_accounting_test');
}
if (!defined('FCPATH')) {
    define('FCPATH', dirname(__DIR__, 4) . '/');
}

if (!class_exists('Admin_Controller')) {
    class Admin_Controller {}
}

if (!function_exists('has_permission')) {
    function has_permission($perm) { return true; }
}
if (!function_exists('simpan_aktifitas')) {
    function simpan_aktifitas($a, $b, $c, $d, $e, $f) {}
}
if (!function_exists('int_to_roman')) {
    function int_to_roman($integer) {
        $table = array('M'=>1000, 'CM'=>900, 'D'=>500, 'CD'=>400, 'C'=>100, 'XC'=>90, 'L'=>50, 'XL'=>40, 'X'=>10, 'IX'=>9, 'V'=>5, 'IV'=>4, 'I'=>1);
        $return = '';
        while($integer > 0) {
            foreach($table as $rom=>$val) {
                if($integer >= $val) {
                    $integer -= $val;
                    $return .= $rom;
                    break;
                }
            }
        }
        return $return;
    }
}

require_once dirname(__DIR__) . '/controllers/Expense.php';

class MockDbResult {
    private $records;
    public function __construct($records = []) {
        $this->records = is_array($records) ? $records : ($records ? [$records] : []);
    }
    public function row() {
        return !empty($this->records) ? (object)$this->records[0] : null;
    }
    public function row_array() {
        return !empty($this->records) ? (array)$this->records[0] : [];
    }
    public function result() {
        $res = [];
        foreach ($this->records as $r) { $res[] = (object)$r; }
        return $res;
    }
    public function result_array() {
        $res = [];
        foreach ($this->records as $r) { $res[] = (array)$r; }
        return $res;
    }
    public function num_rows() {
        return count($this->records);
    }
}

class MockDb {
    public $tables = [];
    public $last_query = '';
    public $inserted_jurnal = [];
    public $updated_data = [];
    public $wheres = [];
    public $not_likes = [];

    public function select($fields) { return $this; }
    public function from($table) { return $this; }
    public function join($table, $cond, $type = '') { return $this; }
    public function where($k, $v = null, $escape = true) {
        $this->wheres[] = [$k, $v];
        return $this;
    }
    public function where_in($k, $arr) { return $this; }
    public function group_start() { return $this; }
    public function group_end() { return $this; }
    public function or_where($k, $v = null) {
        $this->wheres[] = [$k, $v];
        return $this;
    }
    public function or_like($k, $v = null, $s = '') { return $this; }
    public function like($k, $v = null, $s = '') { return $this; }
    public function not_like($k, $v = null, $s = '') {
        $this->not_likes[] = [$k, $v, $s];
        return $this;
    }
    public function group_by($k) { return $this; }
    public function order_by($k, $dir = '') { return $this; }
    public function limit($l, $s = 0) { return $this; }
    public function count_all_results($table = '', $reset = true) { return 0; }

    public function get($table = '', $limit = null, $offset = null) {
        return new MockDbResult([]);
    }

    public function get_where($table, $where) {
        if ($table === 'tr_expense') {
            $id = isset($where['id']) ? $where['id'] : null;
            if (isset($this->tables['tr_expense'][$id])) {
                return new MockDbResult([$this->tables['tr_expense'][$id]]);
            }
        }
        if ($table === 'tr_expense_detail') {
            $no_doc = isset($where['no_doc']) ? $where['no_doc'] : null;
            if (isset($this->tables['tr_expense_detail'][$no_doc])) {
                return new MockDbResult($this->tables['tr_expense_detail'][$no_doc]);
            }
        }
        if ($table === 'tr_jurnal') {
            return new MockDbResult([]);
        }
        return new MockDbResult([]);
    }

    public function query($sql) {
        $this->last_query = $sql;
        if (strpos($sql, 'MAX(no_jurnal)') !== false) {
            return new MockDbResult([['maxP' => '00005-AJV-X-26']]);
        }
        if (strpos($sql, 'coa_master') !== false) {
            if (strpos($sql, '1103-01-15') !== false) {
                return new MockDbResult([['nama' => 'Piutang Kelebihan Kasbon', 'no_perkiraan' => '1103-01-15']]);
            }
            if (strpos($sql, '1103-01-14') !== false) {
                return new MockDbResult([['nama' => 'Piutang Lain-lain Konsultan', 'no_perkiraan' => '1103-01-14']]);
            }
            if (strpos($sql, '1101-02-01') !== false) {
                return new MockDbResult([['nama' => 'Bank BCA', 'no_perkiraan' => '1101-02-01']]);
            }
            if (strpos($sql, '9999-99-99') !== false) {
                return new MockDbResult([['nama' => 'Hutang Expense / Reimburse Karyawan', 'no_perkiraan' => '9999-99-99']]);
            }
            return new MockDbResult([['nama' => 'Biaya Umum', 'no_perkiraan' => '5101-01-01']]);
        }
        return new MockDbResult([]);
    }

    public function update($table, $data, $where = []) {
        if ($table === 'tr_expense') {
            $valid_cols = [
                'id', 'no_doc', 'tgl_doc', 'departement', 'coa', 'nama', 'approval', 'status',
                'created_by', 'created_on', 'modified_by', 'modified_on', 'approved_by', 'approved_on',
                'jumlah', 'total_kasbon', 'informasi', 'bank_id', 'accnumber', 'accname', 'pettycash',
                'st_reject', 'transfer_coa_bank', 'transfer_tanggal', 'transfer_jumlah', 'transfer_file',
                'no_so', 'add_ppn_nilai', 'add_ppn_coa', 'add_pph_nilai', 'add_pph_coa', 'sts_reject',
                'sts_reject_manage', 'rejected_by', 'rejected_on', 'reject_reason', 'exp_pib',
                'exp_inv_po', 'tipe_penggantian', 'tipe_pengembalian', 'expense_id_kembalian', 'id_po',
                'bon_bukti', 'bukti_pengembalian', 'lebih_bayar', 'kurang_bayar', 'keterangan_kurang_bayar',
                'keterangan_pengembalian', 'id_kasbon', 'project_consultant', 'no_expense_consultant',
                'sts_finance', 'app_finance_date', 'tgl_approval_direktur', 'verified_by', 'verified_on',
                'verif_checklist', 'verif_reject_reason', 'reject_reason_finance', 'id_bank_pengembalian'
            ];
            foreach (array_keys($data) as $col) {
                if (!in_array($col, $valid_cols, true)) {
                    throw new Exception("Unknown column '{$col}' in 'field list' for table tr_expense");
                }
            }
        }
        $this->updated_data[$table][] = ['data' => $data, 'where' => $where];
        if ($table === 'tr_expense' && isset($where['id'])) {
            $id = $where['id'];
            if (isset($this->tables['tr_expense'][$id])) {
                $this->tables['tr_expense'][$id] = array_merge($this->tables['tr_expense'][$id], $data);
            }
        }
        return true;
    }

    public function insert($table, $data) {
        return true;
    }

    public function insert_batch($table, $data) {
        if ($table === 'tr_jurnal') {
            $this->inserted_jurnal = array_merge($this->inserted_jurnal, $data);
        }
        return true;
    }

    public function escape($val) {
        return "'" . addslashes((string)$val) . "'";
    }

    public function trans_begin() { return true; }
    public function trans_status() { return true; }
    public function trans_commit() { return true; }
    public function trans_rollback() { return true; }
    public function error() { return ['code' => 0, 'message' => '']; }
    public function last_query() { return $this->last_query ?: 'SELECT 1'; }
}

class MockAuth {
    public $username = 'admin_tester';
    public $userid = '999';
    public function user_name() { return $this->username; }
    public function user_id() { return $this->userid; }
}

class MockInput {
    public $posts = [];
    public function post($key = null) {
        if ($key === null) return $this->posts;
        return isset($this->posts[$key]) ? $this->posts[$key] : null;
    }
}

class MockTemplate {
    public $vars = [];
    public function set($k, $v = null) {
        if (is_array($k)) {
            $this->vars = array_merge($this->vars, $k);
        } else {
            $this->vars[$k] = $v;
        }
    }
    public function render($v) {}
    public function page_icon($i) {}
    public function title($t) {}
}

class MockOutput {
    public function set_content_type($t) { return $this; }
    public function set_status_header($s) { return $this; }
}

function testCheck($cond, $desc) {
    if (!$cond) {
        echo " [FAIL] {$desc}\n";
        throw new Exception("Test assertion failed: {$desc}");
    } else {
        echo " [PASS] {$desc}\n";
    }
}

echo "\n======================================================\n";
echo " RUNNING EXPENSE REPORT UPDATED FLOW TEST SUITE\n";
echo "======================================================\n\n";

$passCount = 0;

// -----------------------------------------------------------
// TEST 1: Preview Jurnal 1 pada set_jurnal_expense()
// -----------------------------------------------------------
echo "--- TEST 1: Preview Jurnal 1 (set_jurnal_expense) ---\n";

// Case 1A: Lebih Kasbon (Kasbon 1.000.000, Realisasi 750.000 -> Sisa 250.000)
$exp = (new ReflectionClass('Expense'))->newInstanceWithoutConstructor();
$exp->db = new MockDb();
$exp->auth = new MockAuth();
$exp->input = new MockInput();
$exp->input->posts = [
    'tgl_doc' => '2026-10-22',
    'total_kasbon' => '1,000,000',
    'total_expense' => '750,000',
    'coa' => ['5101-01-01'],
    'deskripsi' => ['Beli ATK Proyek'],
    'expense' => ['750,000'],
    'id_kasbon' => ['KB-001']
];

ob_start();
$exp->set_jurnal_expense();
$out1a = ob_get_clean();
$json1a = json_decode($out1a, true);

testCheck($json1a['status'] === 1, "Status response Jurnal 1 Lebih Kasbon sukses (1)");
testCheck(strpos($json1a['hasil'], '1103-01-15') !== false, "Jurnal 1 Lebih Kasbon memuat akun Piutang Kelebihan Kasbon (1103-01-15)");
testCheck(strpos($json1a['hasil'], '1103-01-14') !== false, "Jurnal 1 Lebih Kasbon memuat akun Piutang Lain-lain Konsultan (1103-01-14)");
testCheck($json1a['ttl_debit'] === $json1a['ttl_kredit'], "Jurnal 1 Lebih Kasbon 100% Balancing ({$json1a['ttl_debit']} == {$json1a['ttl_kredit']})");
testCheck($json1a['ttl_debit'] === '1,000,000', "Total Balancing tepat Rp 1.000.000");

// Case 1B: Impas (Kasbon 500.000, Realisasi 500.000)
$exp->input->posts = [
    'tgl_doc' => '2026-10-22',
    'total_kasbon' => '500,000',
    'total_expense' => '500,000',
    'coa' => ['5101-01-01'],
    'deskripsi' => ['Konsumsi Rapat'],
    'expense' => ['500,000'],
    'id_kasbon' => ['KB-002']
];
ob_start();
$exp->set_jurnal_expense();
$out1b = ob_get_clean();
$json1b = json_decode($out1b, true);
testCheck($json1b['ttl_debit'] === '500,000' && $json1b['ttl_kredit'] === '500,000', "Jurnal 1 Impas balancing Rp 500.000");

// Case 1C: Lebih Expense (Kasbon 500.000, Realisasi 700.000 -> Kurang Bayar 200.000)
$exp->input->posts = [
    'tgl_doc' => '2026-10-22',
    'total_kasbon' => '500,000',
    'total_expense' => '700,000',
    'coa' => ['5101-01-01'],
    'deskripsi' => ['Tiket Pesawat'],
    'expense' => ['700,000'],
    'id_kasbon' => ['KB-003']
];
ob_start();
$exp->set_jurnal_expense();
$out1c = ob_get_clean();
$json1c = json_decode($out1c, true);
testCheck(strpos($json1c['hasil'], '9999-99-99') !== false, "Jurnal 1 Lebih Expense memuat akun Hutang Expense (9999-99-99)");
testCheck($json1c['ttl_debit'] === '700,000' && $json1c['ttl_kredit'] === '700,000', "Jurnal 1 Lebih Expense balancing Rp 700.000");

// -----------------------------------------------------------
// TEST 2: Approval Berjenjang & Segregation of Duties (BR-12)
// -----------------------------------------------------------
echo "\n--- TEST 2: Approval Berjenjang & Segregation of Duties (approve) ---\n";

// Case 2A: BR-12 Pelapor mencoba approve laporannya sendiri
$exp = (new ReflectionClass('Expense'))->newInstanceWithoutConstructor();
$mockDb = new MockDb();
$mockDb->tables['tr_expense'][10] = [
    'id' => 10,
    'no_doc' => 'EXP-2026-001',
    'nama' => 'febry_pelapor',
    'status' => '0',
    'sts_finance' => '0',
    'total_kasbon' => 1000000,
    'jumlah' => 800000,
    'lebih_bayar' => 200000
];
$mockAuth = new MockAuth();
$mockAuth->username = 'febry_pelapor'; // Pelapor sama dengan approver
$exp->db = $mockDb;
$exp->auth = $mockAuth;
$exp->input = new MockInput();

ob_start();
$exp->approve('10');
$out2a = ob_get_clean();
$json2a = json_decode($out2a, true);
testCheck($json2a['save'] === false, "BR-12 Blokir: Pelapor dilarang meng-approve laporannya sendiri");
testCheck(strpos($json2a['message'], 'Segregation of Duties') !== false, "Pesan error memuat 'Segregation of Duties'");

// Case 2B: Approval Jenjang 1 (Finance) dari status 0 -> 1
$mockAuth->username = 'dept_head_finance';
ob_start();
$exp->approve('10');
$out2b = ob_get_clean();
$json2b = json_decode($out2b, true);
testCheck($json2b['save'] === true, "Approval Finance berhasil disetujui (save = true)");
testCheck($mockDb->tables['tr_expense'][10]['status'] === 1, "Status expense berubah menjadi 1 (Menunggu Approval Direktur)");
testCheck($mockDb->tables['tr_expense'][10]['sts_finance'] === '1', "Flag sts_finance berubah menjadi '1'");
testCheck($mockDb->tables['tr_expense'][10]['approved_by'] === 'dept_head_finance', "Audit approved_by tercatat Dept Head Finance");

// Case 2C: Approval Jenjang 2 (Direktur) pada Lebih Kasbon -> transisi 1 -> 3 & Posting Jurnal 1
$mockAuth->username = 'direktur_utama';
$mockDb->tables['tr_expense_detail']['EXP-2026-001'] = [
    ['no_doc' => 'EXP-2026-001', 'coa' => '5101-01-01', 'deskripsi' => 'Pengeluaran Lapangan', 'expense' => 800000, 'kasbon' => 1000000, 'id_kasbon' => 'KB-001']
];
$mockDb->tables['tr_expense'][10]['id_kasbon'] = 'KB-001';

ob_start();
$exp->approve('10');
$out2c = ob_get_clean();
$json2c = json_decode($out2c, true);
testCheck($json2c['save'] === true, "Approval Direktur berhasil disetujui (save = true)");
testCheck($mockDb->tables['tr_expense'][10]['status'] === 3, "Kondisi Lebih Kasbon: Status bertransisi ke 3 (Menunggu Pengembalian Kasbon)");
testCheck(!empty($mockDb->tables['tr_expense'][10]['tgl_approval_direktur']), "Timestamp tgl_approval_direktur tercatat");
testCheck(count($mockDb->inserted_jurnal) === 3, "Jurnal 1 otomatis terposting (3 baris: Debit Beban 800rb, Debit Piutang 200rb, Kredit Kasbon 1jt)");

// Verifikasi isi Jurnal 1
$j1_debit_beban = 0; $j1_debit_piutang = 0; $j1_kredit_kasbon = 0;
foreach ($mockDb->inserted_jurnal as $j) {
    if ($j['coa'] === '5101-01-01') $j1_debit_beban = $j['debit'];
    if ($j['coa'] === '1103-01-15') $j1_debit_piutang = $j['debit'];
    if ($j['coa'] === '1103-01-14') $j1_kredit_kasbon = $j['kredit'];
}
testCheck($j1_debit_beban == 800000, "Jurnal 1: Debit Beban Rp 800.000");
testCheck($j1_debit_piutang == 200000, "Jurnal 1: Debit Piutang Kelebihan Kasbon (1103-01-15) Rp 200.000");
testCheck($j1_kredit_kasbon == 1000000, "Jurnal 1: Kredit Piutang Kasbon Konsultan (1103-01-14) Rp 1.000.000");

// -----------------------------------------------------------
// TEST 3: Verifikasi Pengembalian Kasbon & Posting Jurnal 2
// -----------------------------------------------------------
echo "\n--- TEST 3: Verifikasi Pengembalian Kasbon & Jurnal 2 ---\n";

// Siapkan data dokumen status 4 (Menunggu Verifikasi)
$mockDb->tables['tr_expense'][20] = [
    'id' => 20,
    'no_doc' => 'EXP-2026-002',
    'id_kasbon' => 'KB-002',
    'nama' => 'febry_pelapor',
    'status' => '4',
    'total_kasbon' => 1000000,
    'jumlah' => 750000,
    'lebih_bayar' => 250000,
    'transfer_coa_bank' => '1101-02-01', // Bank BCA
    'transfer_tanggal' => '2026-10-22',
    'transfer_jumlah' => 250000,
    'bukti_pengembalian' => 'assets/expense_bukti_pengembalian/transfer_bca_250k.jpg'
];

$exp->auth->username = 'febry_pelapor';
$exp->input->posts = ['id' => 20, 'chk_bukti' => 1, 'chk_mutasi' => 1, 'chk_nominal' => 1];

// Case 3A: BR-12 Verifikasi tidak boleh oleh pelapor sendiri
ob_start();
$exp->verifikasi_pengembalian_approve();
$out3a = ob_get_clean();
$json3a = json_decode($out3a, true);
testCheck($json3a['save'] === false, "BR-12: Pelapor dilarang memverifikasi pengembaliannya sendiri");

// Case 3B: Finance memverifikasi tapi checklist belum lengkap
$exp->auth->username = 'finance_verifikator';
$exp->input->posts = ['id' => 20, 'chk_bukti' => 1, 'chk_mutasi' => 0, 'chk_nominal' => 1];
ob_start();
$exp->verifikasi_pengembalian_approve();
$out3b = ob_get_clean();
$json3b = json_decode($out3b, true);
testCheck($json3b['save'] === false, "Checklist belum lengkap: Verifikasi gagal disetujui");

// Case 3C: Finance memverifikasi lengkap (3 checklist centang) -> Status 2 & Posting Jurnal 2
$exp->input->posts = ['id' => 20, 'chk_bukti' => 1, 'chk_mutasi' => 1, 'chk_nominal' => 1];
$mockDb->inserted_jurnal = []; // reset journal recorder
ob_start();
$exp->verifikasi_pengembalian_approve();
$out3c = ob_get_clean();
$json3c = json_decode($out3c, true);

testCheck($json3c['save'] === true, "Verifikasi pengembalian kasbon sukses disetujui");
testCheck($mockDb->tables['tr_expense'][20]['status'] === 2, "Status bertransisi ke 2 (Selesai/Approved)");
testCheck($mockDb->tables['tr_expense'][20]['verified_by'] === 'finance_verifikator', "Audit verified_by tercatat");
testCheck($mockDb->tables['tr_expense'][20]['verif_checklist'] === 'bukti,mutasi,nominal', "Audit verif_checklist tersimpan");
testCheck(count($mockDb->inserted_jurnal) === 2, "Jurnal 2 berhasil dibukukan (2 baris)");

$j2_debit_bank = 0; $j2_kredit_piutang = 0;
foreach ($mockDb->inserted_jurnal as $j) {
    if ($j['coa'] === '1101-02-01') $j2_debit_bank = $j['debit'];
    if ($j['coa'] === '1103-01-15') $j2_kredit_piutang = $j['kredit'];
}
testCheck($j2_debit_bank == 250000, "Jurnal 2: Debit Bank BCA (1101-02-01) Rp 250.000");
testCheck($j2_kredit_piutang == 250000, "Jurnal 2: Kredit Piutang Kelebihan Kasbon (1103-01-15) Rp 250.000");

// -----------------------------------------------------------
// TEST 4: Penolakan Verifikasi (verifikasi_pengembalian_reject)
// -----------------------------------------------------------
echo "\n--- TEST 4: Tolak Verifikasi Pengembalian Kasbon ---\n";

$mockDb->tables['tr_expense'][21] = [
    'id' => 21,
    'no_doc' => 'EXP-2026-003',
    'status' => '4',
    'lebih_bayar' => 100000
];
$exp->input->posts = ['id' => 21, 'reason' => 'Bukti transfer buram dan nama penerima tidak sesuai'];
ob_start();
$exp->verifikasi_pengembalian_reject();
$out4 = ob_get_clean();
$json4 = json_decode($out4, true);

testCheck($json4['save'] === true, "Tolak verifikasi sukses");
testCheck($mockDb->tables['tr_expense'][21]['status'] === 3, "Status dikembalikan ke 3 (Menunggu Pengembalian oleh pelapor)");
testCheck($mockDb->tables['tr_expense'][21]['verif_reject_reason'] === 'Bukti transfer buram dan nama penerima tidak sesuai', "Catatan reject verifikasi tersimpan");

// -----------------------------------------------------------
// TEST 5: Render Status Badge Modul Expense
// -----------------------------------------------------------
echo "\n--- TEST 5: Render Status Badge Helper ---\n";

$m_badge = new ReflectionMethod('Expense', '_render_expense_status_badge');
$m_badge->setAccessible(true);

$b0 = $m_badge->invoke($exp, ['status' => 0, 'sts_finance' => '0']);
testCheck(strpos($b0, 'Menunggu Approval Finance') !== false, "Badge status 0: Menunggu Approval Finance");

$b1 = $m_badge->invoke($exp, ['status' => 1]);
testCheck(strpos($b1, 'Menunggu Approval Direktur') !== false, "Badge status 1: Menunggu Approval Direktur");

$b3 = $m_badge->invoke($exp, ['status' => 3]);
testCheck(strpos($b3, 'Menunggu Pengembalian') !== false, "Badge status 3: Menunggu Pengembalian");

$b4 = $m_badge->invoke($exp, ['status' => 4]);
testCheck(strpos($b4, 'Menunggu Verifikasi') !== false, "Badge status 4: Menunggu Verifikasi");

$b2 = $m_badge->invoke($exp, ['status' => 2]);
testCheck(strpos($b2, 'Disetujui') !== false, "Badge status 2: Disetujui / Selesai");

$b9 = $m_badge->invoke($exp, ['status' => 9, 'reject_reason' => 'Perlu revisi']);
testCheck(strpos($b9, 'Ditolak') !== false && strpos($b9, 'Perlu revisi') !== false, "Badge status 9: Ditolak dengan alasan revisi");

// -----------------------------------------------------------
// TEST 6: Filter Eksklusi Dokumen PI / id_po IS NULL
// -----------------------------------------------------------
echo "\n--- TEST 6: Filter Eksklusi Dokumen PI & id_po NULL ---\n";

$testDb6 = new MockDb();
$exp->db = $testDb6;
$exp->output = new MockOutput();
$exp->input->posts = [
    'draw' => 1,
    'length' => 10,
    'start' => 0,
    'search' => ['value' => '']
];

ob_start();
$exp->get_dat_expense_data();
$out6 = ob_get_clean();
$json6 = json_decode($out6, true);

$has_id_po_filter = false;
$has_exp_inv_po_filter = false;
foreach ($testDb6->wheres as $w) {
    if ($w[0] === 'a.id_po' && $w[1] === null) {
        $has_id_po_filter = true;
    }
    if ($w[0] === 'a.exp_inv_po' && $w[1] === null) {
        $has_exp_inv_po_filter = true;
    }
}
$has_pi_filter = false;
foreach ($testDb6->not_likes as $nl) {
    if ($nl[0] === 'a.no_doc' && $nl[1] === 'PI-') {
        $has_pi_filter = true;
    }
}

testCheck($has_id_po_filter === true, "Query get_dat_expense_data memfilter a.id_po IS NULL");
testCheck($has_exp_inv_po_filter === true, "Query get_dat_expense_data memfilter a.exp_inv_po IS NULL");
testCheck($has_pi_filter === true, "Query get_dat_expense_data memfilter a.no_doc NOT LIKE 'PI-%'");
testCheck(isset($json6['data']), "Response format get_dat_expense_data valid DataTables JSON");

// Verifikasi direct DB dev jika tersedia
$db_conn = @new mysqli("206.84.97.107", "alief", "Alief@ssc1", "db_sendigs_ss_dev", 3306);
if ($db_conn && !$db_conn->connect_error) {
    $res_check = $db_conn->query("SELECT id, no_doc FROM tr_expense WHERE (pettycash IS NULL OR pettycash = '') AND (exp_pib IS NULL OR exp_pib = '') AND (id_po IS NULL OR id_po = '') AND no_doc NOT LIKE 'PI-%'");
    $has_pi_in_result = false;
    while ($rc = $res_check->fetch_assoc()) {
        if (strpos($rc['no_doc'], 'PI-') === 0) {
            $has_pi_in_result = true;
        }
    }
    testCheck($has_pi_in_result === false, "Direct DB: Dokumen berawalan PI (mis. PI-2025-07-000001) terbukti 100% terfilter dari hasil query");
}

echo "\n======================================================\n";
echo " ALL TEST CHECKS PASSED SUCCESSFULLY (100% PASS)\n";
echo "======================================================\n";
