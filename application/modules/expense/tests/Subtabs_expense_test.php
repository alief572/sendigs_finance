<?php
/**
 * Automated Unit Test: Sub-tabs Pengembalian Kasbon & Lebih Expense
 * 
 * Memverifikasi:
 * 1. get_dat_pengembalian_kasbon() memproses parameter 'subtab' (upload, verif, done)
 *    dan mengembalikan payload 'counts' yang akurat.
 * 2. get_dat_lebih_expense() memproses parameter 'subtab' (belum, menunggu, selesai)
 *    dan mengembalikan payload 'counts' yang akurat serta kolom company & no_pengajuan.
 * 3. form_pengembalian() mendukung mode read-only saat status = 4 atau 2,
 *    menyembunyikan tombol kirim, dan merender existing file chips.
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

if (!class_exists('BF_Model')) {
    class BF_Model {}
}

if (!function_exists('has_permission')) {
    function has_permission($perm) { return true; }
}
if (!function_exists('simpan_aktifitas')) {
    function simpan_aktifitas($a, $b, $c, $d, $e, $f) {}
}
if (!function_exists('base_url')) {
    function base_url($uri = '') { return 'http://localhost/sendigs/' . ltrim($uri, '/'); }
}

require_once FCPATH . 'application/modules/expense/models/Expense_model.php';
require_once FCPATH . 'application/modules/expense/controllers/Expense.php';

function testCheck($condition, $message) {
    if ($condition) {
        echo " [PASS] " . $message . "\n";
    } else {
        echo " [FAIL] " . $message . "\n";
        exit(1);
    }
}

class MockDbSubtabs {
    public $where_clauses = [];
    public $select_clause = '';
    public $data_expense = [];

    public function select($s, $escape = true) {
        $this->select_clause = $s;
        return $this;
    }
    public function from($f) { return $this; }
    public function join($t, $c, $type = 'left') { return $this; }
    public function where($w, $v = null, $escape = true) {
        $this->where_clauses[] = [$w, $v];
        return $this;
    }
    public function where_in($k, $v) {
        $this->where_clauses[] = [$k, $v];
        return $this;
    }
    public function group_start() { return $this; }
    public function group_end() { return $this; }
    public function or_where($k, $v) { return $this; }
    public function not_like($k, $v, $pos = 'after') { return $this; }
    public function like($k, $v, $pos = 'both') { return $this; }
    public function or_like($k, $v, $pos = 'both') { return $this; }
    public function order_by($k, $dir = 'ASC') { return $this; }
    public function limit($l, $s = 0) { return $this; }
    public function escape($val) { return "'" . addslashes($val) . "'"; }

    public function count_all_results($table = '', $reset = true) {
        return count($this->data_expense);
    }

    public function get() {
        // Return simulated object or result array
        $rows = $this->data_expense;
        return new class($rows) {
            private $rows;
            public function __construct($r) { $this->rows = $r; }
            public function result_array() { return $this->rows; }
            public function result() {
                return array_map(function($x) { return (object)$x; }, $this->rows);
            }
            public function row() {
                // If it's a count query with SUM(CASE ...)
                return (object)[
                    'cnt_upload' => 2,
                    'cnt_verif'  => 1,
                    'cnt_done'   => 3,
                    'cnt_belum'  => 4,
                    'cnt_menunggu' => 2,
                    'cnt_selesai'  => 5
                ];
            }
        };
    }

    public function query($sql) {
        return new class {
            public function row() {
                return (object)[
                    'cnt_belum'    => 4,
                    'cnt_menunggu' => 2,
                    'cnt_selesai'  => 5
                ];
            }
            public function result_array() { return []; }
            public function result() { return []; }
        };
    }

    public function get_where($table, $where) {
        $data = (object)[
            'id' => 10,
            'no_doc' => 'EXP-TEST-001',
            'id_kasbon' => 'KS-TEST-001',
            'jumlah' => 750000,
            'lebih_bayar' => 250000,
            'status' => 4, // Menunggu Verifikasi
            'id_bank_pengembalian' => 1,
            'transfer_tanggal' => '2026-10-28',
            'transfer_jumlah' => 250000,
            'bukti_pengembalian' => 'bukti_test_1.jpg;bukti_test_2.pdf'
        ];
        return new class($data) {
            private $d;
            public function __construct($d) { $this->d = $d; }
            public function row() { return $this->d; }
            public function result() { return [$this->d]; }
        };
    }
}

class MockInputSubtabs {
    public $posts = [];
    public $gets = [];
    public function post($key = null) {
        if ($key === null) return $this->posts;
        return isset($this->posts[$key]) ? $this->posts[$key] : null;
    }
    public function get($key = null) {
        if ($key === null) return $this->gets;
        return isset($this->gets[$key]) ? $this->gets[$key] : null;
    }
}

class MockAuthSubtabs {
    public function user_name() { return 'user_test'; }
    public function is_login() { return true; }
    public function is_admin() { return true; }
}

class MockTemplateSubtabs {
    public $vars = [];
    public $rendered_view = '';

    public function set($k, $v = null) {
        if (is_array($k)) {
            foreach ($k as $x => $y) $this->vars[$x] = $y;
        } else {
            $this->vars[$k] = $v;
        }
    }
    public function page_icon($i) {}
    public function title($t) {}
    public function render($view) {
        $this->rendered_view = $view;
        extract($this->vars);
        include FCPATH . 'application/modules/expense/views/' . $view . '.php';
    }
}

echo "\n======================================================\n";
echo " RUNNING EXPENSE SUB-TABS UNIT & INTEGRATION TESTS\n";
echo "======================================================\n\n";

$exp = (new ReflectionClass('Expense'))->newInstanceWithoutConstructor();
$mockDb = new MockDbSubtabs();
$mockInput = new MockInputSubtabs();
$exp->db = $mockDb;
$exp->input = $mockInput;
$exp->auth = new MockAuthSubtabs();
$exp->template = new MockTemplateSubtabs();

// -------------------------------------------------------------
// TEST 1: get_dat_pengembalian_kasbon per subtab & counts
// -------------------------------------------------------------
echo "--- TEST 1: get_dat_pengembalian_kasbon (Subtabs & Payload Counts) ---\n";
$mockInput->posts = [
    'draw' => 1,
    'length' => 10,
    'start' => 0,
    'search' => ['value' => ''],
    'subtab' => 'upload'
];

$mockDb->data_expense = [
    [
        'id' => 1,
        'no_doc' => 'EXP-2026-0001',
        'id_kasbon' => 'KS-2026-0001',
        'informasi' => 'Beli ATK',
        'tgl_doc' => '2026-10-25',
        'tgl_approval_direktur' => '2026-10-26 14:00:00',
        'total_kasbon' => 1000000,
        'jumlah' => 800000,
        'lebih_bayar' => 200000,
        'status' => '3',
        'verif_reject_reason' => 'Struk belum jelas'
    ]
];

ob_start();
$exp->get_dat_pengembalian_kasbon();
$out1 = ob_get_clean();
$json1 = json_decode($out1, true);

testCheck(isset($json1['counts']), "Response get_dat_pengembalian_kasbon memiliki key 'counts'");
testCheck($json1['counts']['upload'] === 2, "Counts upload tepat = 2");
testCheck($json1['counts']['verif'] === 1, "Counts verif tepat = 1");
testCheck($json1['counts']['done'] === 3, "Counts done tepat = 3");
testCheck(strpos($json1['data'][0]['action'], 'btn-upload-pengembalian') !== false, "Aksi pada subtab upload memuat tombol 'Upload Bukti'");
testCheck(strpos($json1['data'][0]['informasi'], 'Ditolak Finance: Struk belum jelas') !== false, "Keterangan menampilkan catatan reject jika ada");

// -------------------------------------------------------------
// TEST 2: get_dat_lebih_expense per subtab & counts
// -------------------------------------------------------------
echo "\n--- TEST 2: get_dat_lebih_expense (Subtabs, Company, & Payload Counts) ---\n";
$mockInput->posts = [
    'draw' => 1,
    'length' => 10,
    'start' => 0,
    'search' => ['value' => ''],
    'subtab' => 'belum'
];

$mockDb->data_expense = [
    [
        'id' => 2,
        'no_doc' => 'EXP-2026-0002',
        'id_kasbon' => 'KS-2026-0002',
        'informasi' => 'Transport Klien',
        'tgl_doc' => '2026-10-25',
        'tgl_approval_direktur' => '2026-10-26 15:00:00',
        'total_kasbon' => 500000,
        'jumlah' => 700000,
        'kurang_bayar' => 200000,
        'status' => '2',
        'hris_company_name' => 'PT. Sinergi Tekno Medika',
        'no_pengajuan_done' => null,
        'no_pengajuan_pending' => null,
        'status_rp_legacy' => null,
        'no_rp_legacy' => null,
        'reject_reason_rp' => 'Rute belum sesuai SPPD'
    ]
];

ob_start();
$exp->get_dat_lebih_expense();
$out2 = ob_get_clean();
$json2 = json_decode($out2, true);

testCheck(isset($json2['counts']), "Response get_dat_lebih_expense memiliki key 'counts'");
testCheck($json2['counts']['belum'] === 4, "Counts belum tepat = 4");
testCheck($json2['counts']['menunggu'] === 2, "Counts menunggu tepat = 2");
testCheck($json2['counts']['selesai'] === 5, "Counts selesai tepat = 5");
testCheck($json2['data'][0]['company'] === 'STM', "Company berhasil di-resolve menjadi 'STM'");
testCheck(strpos($json2['data'][0]['status_reimburse'], 'Menunggu Diajukan Finance') !== false, "Badge status menampilkan 'Menunggu Diajukan Finance'");
testCheck(strpos($json2['data'][0]['status_reimburse'], 'Ditolak di Request Payment') !== false, "Badge penolakan tampil jika pernah ditolak di Request Payment");
testCheck(strpos($json2['data'][0]['keterangan'], 'Rute belum sesuai SPPD') !== false, "Keterangan menampilkan alasan penolakan Request Payment");

// -------------------------------------------------------------
// TEST 3: form_pengembalian Read-only Mode
// -------------------------------------------------------------
echo "\n--- TEST 3: form_pengembalian Read-Only Mode (Status 4 / Menunggu Verifikasi) ---\n";
ob_start();
$exp->form_pengembalian(10);
$form_html = ob_get_clean();

testCheck(strpos($form_html, 'disabled') !== false, "Select Bank berstatus disabled pada mode read-only");
testCheck(strpos($form_html, 'readonly') !== false, "Input tanggal & nominal berstatus readonly");
testCheck(strpos($form_html, 'Pilih Bukti Transfer') === false, "Tombol 'Pilih Bukti Transfer' disembunyikan pada mode read-only");
testCheck(strpos($form_html, 'bukti_test_1.jpg') !== false, "File bukti_test_1.jpg tampil pada daftar chip bukti");
testCheck(strpos($form_html, 'bukti_test_2.pdf') !== false, "File bukti_test_2.pdf tampil pada daftar chip bukti");
testCheck(strpos($form_html, 'id="btn_submit_kirim_pengembalian"') === false, "Tombol submit 'Kirim Bukti Transfer' disembunyikan pada mode read-only");
testCheck(strpos($form_html, 'Draft: Menunggu Verifikasi Finance') !== false, "Badge header jurnal menampilkan 'Draft: Menunggu Verifikasi Finance'");

echo "\n======================================================\n";
echo " ALL SUB-TABS EXPENSE TESTS PASSED (100% PASS)\n";
echo "======================================================\n";
