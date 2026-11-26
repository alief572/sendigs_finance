<?php
// Dokumen jalur lama dibayar lewat form transfer: kelayakan, draft, pembuatan di dalam transaksi.
// SQLite in-memory + query builder CodeIgniter asli. Lock FOR UPDATE (raw MySQL) tidak diuji di sini.
define('BASEPATH', dirname(__DIR__, 4) . '/system/');
define('APPPATH', dirname(__DIR__, 3) . '/');
set_error_handler(function ($severity, $message) { throw new ErrorException($message, 0, $severity); });
function log_message($level, $message) {}
function show_error($message) { throw new RuntimeException($message); }
function load_class($name, $directory) { return null; }
$checks = 0;
function check($ok, $message) { global $checks; $checks++; if (!$ok) { throw new RuntimeException($message); } }
function rejected(callable $call, $needle, $message) {
    try { $call(); } catch (DomainException $e) { check(strpos($e->getMessage(), $needle) !== false, $message . ': ' . $e->getMessage()); return; }
    check(false, $message . ': tidak ditolak');
}
require BASEPATH . 'database/DB.php';
require APPPATH . 'modules/pembayaran_material/libraries/Rp_transfer_payment.php';
require APPPATH . 'modules/record_request_payment/libraries/Rp_transfer_record.php';

const SINCE = '2026-11-01';
const ACCOUNT = ['bank' => 'BCA', 'no_rek' => '123-456', 'nama' => 'Budi', 'admin' => '6500', 'bearer' => 'company'];

function fixture() {
    $db = DB(['dbdriver' => 'pdo', 'subdriver' => 'sqlite', 'dsn' => 'sqlite::memory:', 'db_debug' => false], true);
    $db->query('CREATE TABLE payment_approve (id TEXT PRIMARY KEY, no_doc TEXT, tipe TEXT, status INTEGER, id_payment TEXT, created_on TEXT, jumlah NUMERIC, keperluan TEXT)');
    $db->query('CREATE TABLE tr_rp_pengajuan_h (id INTEGER PRIMARY KEY, no_pengajuan TEXT, company_id TEXT, company_nama TEXT, status TEXT, deleted INTEGER)');
    $db->query('CREATE TABLE tr_rp_pengajuan_d (id INTEGER PRIMARY KEY, id_pengajuan INTEGER, no_dokumen TEXT, kategori TEXT, decision TEXT, dpp NUMERIC, nilai_ppn NUMERIC, nilai_pph NUMERIC, dibayarkan NUMERIC, admin NUMERIC DEFAULT 0, flag_pph21 INTEGER, flag_pph23 INTEGER, id_transfer INTEGER, payment_approval_id TEXT UNIQUE)');
    $db->query('CREATE TABLE tr_rp_transfer (id INTEGER PRIMARY KEY, id_pengajuan INTEGER, urutan INTEGER, bank TEXT, no_rek TEXT, nama TEXT, admin NUMERIC, bearer TEXT, status TEXT, id_payment TEXT, UNIQUE (id_pengajuan, urutan))');
    foreach ([1 => 'done', 2 => 'done', 3 => 'pending'] as $id => $status) {
        $db->insert('tr_rp_pengajuan_h', ['id' => $id, 'no_pengajuan' => 'PGJ-' . $id, 'company_id' => '7', 'company_nama' => 'STM', 'status' => $status, 'deleted' => 0]);
    }
    return $db;
}
function add($db, $id, array $payment = [], array $detail = []) {
    $db->insert('payment_approve', $payment + ['id' => 'PA-' . $id, 'no_doc' => 'DOC-' . $id, 'tipe' => 'transportasi', 'status' => 1,
        'id_payment' => null, 'created_on' => '2026-11-05 10:00:00', 'jumlah' => 100000, 'keperluan' => 'x']);
    $db->insert('tr_rp_pengajuan_d', $detail + ['id_pengajuan' => 1, 'no_dokumen' => 'DOC-' . $id, 'kategori' => 'Transport', 'decision' => 'approved',
        'dpp' => 100000, 'nilai_ppn' => 11000, 'nilai_pph' => 2000, 'dibayarkan' => 109000, 'flag_pph21' => 0, 'flag_pph23' => 1,
        'id_transfer' => null, 'payment_approval_id' => null]);
}

// Kelayakan: semua syarat terpenuhi -> form transfer.
$db = fixture(); add($db, 1); add($db, 2);
check(Rp_transfer_payment::target($db, ['PA-1', 'PA-2'], SINCE) === 'transfer', 'Eligible documents use the transfer form');
// Satu per satu syarat yang gagal -> form lama.
$cases = [
    'periode lama' => [['created_on' => '2026-10-31 23:59:59'], []],
    'sudah dibayar' => [['status' => 2], []],
    'sudah punya voucher' => [['id_payment' => 'BK--1'], []],
    'tanpa pengajuan' => [[], ['decision' => 'rejected']],
    'header belum selesai' => [[], ['id_pengajuan' => 3]],
    'sudah masuk transfer' => [[], ['id_transfer' => 9]],
    'sudah punya approval transfer' => [[], ['payment_approval_id' => 'RPA-1']],
    'hutang petty cash' => [['tipe' => 'petty_cash_hutang'], ['kategori' => 'Petty Cash Hutang']],
    'dpp beda' => [['jumlah' => 99999], []],
    'dibayarkan tidak cocok' => [[], ['dibayarkan' => 102500]],
    'admin tercatat tetapi tidak dipotong' => [[], ['admin' => 6500]],
];
foreach ($cases as $name => $change) {
    $db = fixture(); add($db, 1, $change[0], $change[1]);
    check(Rp_transfer_payment::target($db, ['PA-1'], SINCE) === 'legacy', 'Legacy form: ' . $name);
    add($db, 2);
    rejected(function () use ($db) { Rp_transfer_payment::target($db, ['PA-1', 'PA-2'], SINCE); }, 'DOC-1', 'Mixed selection: ' . $name);
}
$db = fixture(); add($db, 1);
$db->insert('tr_rp_pengajuan_d', ['id_pengajuan' => 2, 'no_dokumen' => 'DOC-1', 'kategori' => 'Transport', 'decision' => 'approved']);
check(Rp_transfer_payment::target($db, ['PA-1'], SINCE) === 'legacy', 'Ambiguous submission detail stays legacy');
$db = fixture(); add($db, 1); add($db, 2, [], ['id_pengajuan' => 2]);
rejected(function () use ($db) { Rp_transfer_payment::target($db, ['PA-1', 'PA-2'], SINCE); }, 'No. Pengajuan yang sama', 'Different submissions');
$db = fixture(); add($db, 1, ['tipe' => 'refill_pettycash'], ['kategori' => 'Refill Pettycash']); add($db, 2);
rejected(function () use ($db) { Rp_transfer_payment::target($db, ['PA-1', 'PA-2'], SINCE); }, 'Refill', 'Refill must be alone');
check(Rp_transfer_payment::target($db, ['PA-1'], SINCE) === 'transfer', 'Refill alone is allowed');
rejected(function () use ($db) { Rp_transfer_payment::target($db, ['PA-1', 'TIDAK-ADA'], SINCE); }, 'tidak ditemukan', 'Unknown payment id');
rejected(function () use ($db) { Rp_transfer_payment::target($db, [], SINCE); }, 'Pilih', 'Empty selection');

// Draft: bentuk sama dengan find(), belum menulis apa pun.
$db = fixture(); add($db, 1); add($db, 2);
$draft = Rp_transfer_payment::draft($db, ['PA-1', 'PA-2'], ACCOUNT, SINCE);
check($draft['id'] === 0 && $draft['urutan'] === 1 && $draft['no_pengajuan'] === 'PGJ-1' && count($draft['docs']) === 2, 'Draft header');
check($draft['docs'][0]['payment_approval_id'] === 'PA-1' && (string) $draft['docs'][0]['payment_status'] === '1' && $draft['docs'][0]['voucher'] === null, 'Draft documents are payable');
check(Rp_transfer_payment::planned($draft) === ['net' => 21800000, 'transfer' => 21800000, 'bank' => 22450000], 'Draft totals');
check($db->count_all('tr_rp_transfer') === 0, 'Draft writes nothing');
foreach (['bank' => '', 'no_rek' => 'abc', 'nama' => '', 'admin' => '-1', 'bearer' => 'lain'] as $field => $value) {
    try { Rp_transfer_payment::draft($db, ['PA-1'], array_merge(ACCOUNT, [$field => $value]), SINCE); check(false, 'Invalid ' . $field . ' accepted'); }
    catch (DomainException $e) { check(true, ''); }
}
rejected(function () use ($db) { Rp_transfer_payment::draft($db, ['PA-1'], array_merge(ACCOUNT, ['admin' => '999999999', 'bearer' => 'recipient']), SINCE); }, 'Admin', 'Admin larger than transfer');

// Create: rollback membuang transfer dan hubungan dokumen; commit lalu submit ulang ditolak.
$db->trans_begin();
$id = Rp_transfer_payment::create($db, $draft);
check($db->get_where('tr_rp_pengajuan_d', ['id_transfer' => $id])->num_rows() === 2, 'Documents linked inside the transaction');
$db->trans_rollback();
check($db->count_all('tr_rp_transfer') === 0 && $db->get_where('tr_rp_pengajuan_d', ['id_transfer' => null])->num_rows() === 2, 'Rollback leaves no transfer');
$db->trans_begin(); $id = Rp_transfer_payment::create($db, $draft); $db->trans_commit();
$row = $db->get_where('tr_rp_pengajuan_d', ['no_dokumen' => 'DOC-1'])->row_array();
check((int) $row['id_transfer'] === $id && $row['payment_approval_id'] === 'PA-1', 'Existing approval id is linked, not replaced');
rejected(function () use ($db) { Rp_transfer_payment::draft($db, ['PA-1'], ACCOUNT, SINCE); }, 'DOC-1', 'Second submit is rejected');
$stale = array_merge($draft, ['urutan' => 2]);
$db->trans_begin();
rejected(function () use ($db, $stale) { Rp_transfer_payment::create($db, $stale); }, 'transfer lain', 'Stale draft cannot steal linked documents');
$db->trans_rollback();
check($db->count_all('tr_rp_transfer') === 1, 'Stale draft leaves no extra transfer');
add($db, 3);
check(Rp_transfer_payment::draft($db, ['PA-3'], ACCOUNT, SINCE)['urutan'] === 2, 'Next transfer number within the submission');

// Admin lama per dokumen pindah ke transfer: uang yang diterima sama, input pemakai tidak bisa mengubahnya.
$db4 = fixture();
add($db4, 1, [], ['admin' => 6500, 'dibayarkan' => 102500]);
add($db4, 2, [], ['admin' => 6500, 'dibayarkan' => 102500]);
add($db4, 3);
check(Rp_transfer_payment::carriedAdmin($db4, ['PA-1', 'PA-2', 'PA-3'], SINCE) === 1300000, 'Carried admin is summed');
$carried = Rp_transfer_payment::draft($db4, ['PA-1', 'PA-2', 'PA-3'], array_merge(ACCOUNT, ['admin' => '0', 'bearer' => 'company']), SINCE);
check($carried['admin'] === '13000.00' && $carried['bearer'] === 'recipient', 'Carried admin is locked to the recipient');
check(Rp_transfer_payment::planned($carried)['transfer'] === 10250000 + 10250000 + 10900000, 'Recipient receives exactly the approved amounts');
check($db4->get_where('tr_rp_pengajuan_d', ['admin' => 6500])->num_rows() === 2, 'Draft does not change approved data');
$db4->trans_begin(); Rp_transfer_payment::create($db4, $carried); $db4->trans_commit();
$moved = $db4->order_by('id')->get('tr_rp_pengajuan_d')->result_array();
check((int) $moved[0]['admin'] === 0 && (float) $moved[0]['dibayarkan'] === 109000.0 && (float) $moved[2]['dibayarkan'] === 109000.0, 'Per document admin moves to the transfer on submit');

// PGJ yang hanya sebagian jadi transfer.
check(Rp_transfer_record::hasDocumentsOutside($db, 1), 'Document outside a transfer is detected');
$db->where('no_dokumen', 'DOC-3')->update('tr_rp_pengajuan_d', ['kategori' => 'Petty Cash Hutang']);
check(!Rp_transfer_record::hasDocumentsOutside($db, 1), 'Petty cash debt never belongs to a transfer');

echo "PASS Transfer draft: $checks checks\n";
