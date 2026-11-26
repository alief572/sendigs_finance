<?php
// Tab Payment Lama: pengelompokan per No. Payment, filter, dan rincian. SQLite + query builder CodeIgniter asli.
define('BASEPATH', dirname(__DIR__, 4) . '/system/');
define('APPPATH', dirname(__DIR__, 3) . '/');
set_error_handler(function ($severity, $message) { throw new ErrorException($message, 0, $severity); });
function log_message($level, $message) {}
function show_error($message) { throw new RuntimeException($message); }
function load_class($name, $directory) { return null; }
function base_url($path = '') { return 'http://fixture/' . $path; }
function site_url($path = '') { return 'http://fixture/' . $path; }
function html_escape($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
$checks = 0;
function check($ok, $message) { global $checks; $checks++; if (!$ok) { throw new RuntimeException($message); } }
require BASEPATH . 'database/DB.php';
require APPPATH . 'modules/record_request_payment/libraries/Record_payment_legacy.php';

$root = sys_get_temp_dir() . '/rp-old-' . getmypid();
mkdir($root . '/assets/expense', 0700, true);
file_put_contents($root . '/assets/expense/bukti.pdf', 'x');

$db = DB(['dbdriver' => 'pdo', 'subdriver' => 'sqlite', 'dsn' => 'sqlite::memory:', 'db_debug' => false], true);
foreach ([
    'payment_approve (id INTEGER PRIMARY KEY, status INTEGER, id_payment TEXT, no_doc TEXT, tgl_bayar TEXT, created_by TEXT, nm_coa_bank TEXT, payment_bank NUMERIC, jumlah NUMERIC, keterangan_pembayaran TEXT, link_doc TEXT)',
    'tr_rp_transfer (id INTEGER PRIMARY KEY, id_payment TEXT)', 'tr_rp_transfer_file (id INTEGER PRIMARY KEY)',
    'tr_payment_paid (id TEXT, bank_charge NUMERIC)', 'tr_jurnal (no_transaksi TEXT, nm_company TEXT)',
    'tr_rp_record (id INTEGER PRIMARY KEY, no_dokumen TEXT, no_pengajuan TEXT)',
] as $table) { $db->query('CREATE TABLE ' . $table); }
$add = function ($payment, $doc, array $more = []) use ($db) {
    $db->insert('payment_approve', $more + ['status' => 2, 'id_payment' => $payment, 'no_doc' => $doc, 'tgl_bayar' => '2026-10-01',
        'created_by' => 'finance', 'nm_coa_bank' => 'Bank BCA', 'payment_bank' => 1000, 'jumlah' => 600, 'keterangan_pembayaran' => 'bayar', 'link_doc' => '']);
};
$add('BK-1', 'KS-1', ['link_doc' => '["bukti.pdf","hilang.pdf","../x"]']);
$add('BK-1', 'KS-2');
$add('BK-2', 'RQ-1', ['tgl_bayar' => '2026-11-02', 'payment_bank' => 0, 'jumlah' => 250, 'keterangan_pembayaran' => 'transport']);
$add('BK-3', 'DPM-1', ['tgl_bayar' => '2026-11-05']);
$add('BK-4', 'KS-9', ['status' => 1]);
$add('', 'KS-8');
$add('BK-5', 'INV-1');
$db->insert('tr_rp_transfer', ['id_payment' => 'BK-3']);
$db->insert('tr_payment_paid', ['id' => 'BK-1', 'bank_charge' => 6500]);
foreach ([['BK-1', 'STM'], ['BK-1', 'STM'], ['BK-2', 'STM-Sustain']] as $j) { $db->insert('tr_jurnal', ['no_transaksi' => $j[0], 'nm_company' => $j[1]]); }
$db->insert('tr_rp_record', ['no_dokumen' => 'KS-2', 'no_pengajuan' => 'PGJ-7']);

$numbers = function ($company = '', $search = '') use ($db) { return implode(',', Record_payment_legacy::numbers($db, $company, $search)); };
check($numbers() === 'BK-2,BK-1', 'Only old paid non-purchase payments, newest first: ' . $numbers());
check($numbers('7') === 'BK-1', 'STM filter');
check($numbers('3') === 'BK-2', 'Sustain includes STM-Sustain');
check($numbers('4') === '', 'Vuca without payments');
check($numbers('', 'KS-2') === 'BK-1' && $numbers('', 'transport') === 'BK-2' && $numbers('', 'PGJ-7') === 'BK-1', 'Search by document, note, and submission');
check($numbers('', 'tidak-ada') === '' && $numbers('3', 'KS-2') === '', 'Filters combine');

$rows = Record_payment_legacy::details($db, ['BK-1', 'BK-2'], $root);
check(count($rows) === 2 && $rows[0]['payment'] === 'BK-1' && $rows[0]['docs'] === ['KS-1', 'KS-2'], 'One row per payment with all documents');
check($rows[0]['paid'] === 1000.0 && $rows[0]['charge'] === 6500.0 && $rows[0]['company'] === 'STM' && $rows[0]['pengajuan'] === 'PGJ-7', 'Amount once per payment, charge, company, submission');
check($rows[1]['paid'] === 250.0 && $rows[1]['pengajuan'] === '' && $rows[1]['charge'] === 0.0, 'Zero bank amount falls back to documents');
check(array_column($rows[0]['proofs'], 'name') === ['bukti.pdf'], 'Only existing proof files inside the folder');
check(Record_payment_legacy::details($db, [], $root) === [], 'Empty page');

// Tampilan: isi di-escape, paging membawa filter.
$old_rows = $rows; $old_rows[0]['note'] = '<script>x</script>'; $old_count = 60; $old_page = 2; $old_pages = 3;
$url = 'http://fixture/record_payment'; $params = ['tab' => 'old', 'q' => 'a b'];
ob_start(); require APPPATH . 'modules/record_request_payment/views/record_payment_old.php'; $html = ob_get_clean();
check(strpos($html, '<script>x') === false && strpos($html, 'view_payment_new/BK-1') !== false && strpos($html, 'tab=old&amp;q=a+b&amp;page=3') !== false, 'Escaped rows and paged links keep filters');

$page = file_get_contents(APPPATH . 'modules/record_request_payment/views/record_payment.php');
check(strpos($html, 'data-payment') === false && strpos($page, "on('click', 'a.view[data-payment]'") !== false, 'Old View links open the page, not the transfer modal');

unlink($root . '/assets/expense/bukti.pdf');
foreach (['/assets/expense', '/assets', ''] as $dir) { rmdir($root . $dir); }
echo "PASS Payment legacy: $checks checks\n";
