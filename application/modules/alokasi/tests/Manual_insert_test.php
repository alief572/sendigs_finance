<?php
// Alokasi manual (admin): builder baris + validasi, dan jaminan guard admin di controller/view; tanpa DB.
define('BASEPATH', __DIR__);
require dirname(__DIR__) . '/libraries/Rekening_koran_parser.php';
function mCheck($ok, $m) { if (!$ok) throw new RuntimeException($m); }

$bank = array('id' => 5, 'bank' => '22');
$ok = array('tanggal_transaksi' => '2026-10-30', 'keterangan' => 'TRF MASUK', 'nominal_debit' => '1,500.50', 'nominal_kredit' => '', 'saldo' => '2,500.00', 'reference_no' => 'R1', 'bank' => '999', 'jenis_bank' => '99');
list($row, $err) = Rekening_koran_parser::manual_row($ok, $bank, 'H1', 7);
mCheck($err === array(), 'Valid manual row: ' . implode(';', $err));
mCheck($row['nominal_debit'] === '1500.50' && $row['nominal_kredit'] === 0 && $row['saldo'] === '2500.00', 'Thousand separators stripped');
mCheck($row['jenis_bank'] === '22' && $row['tipe_bank'] === 5, 'jenis_bank comes from master bank, not POST');
mCheck($row['status_approval'] === 'approved' && $row['created_by'] === 7 && $row['id_header'] === 'H1', 'Open status, user and header');

foreach (array(
    'tanggal_transaksi' => array('2026-02-30', 'Tanggal tidak valid'),
    'keterangan' => array('  ', 'Keterangan wajib'),
    'nominal_debit' => array('abc', 'Debit bukan angka'),
    'saldo' => array('x', 'Saldo bukan angka'),
) as $field => $case) {
    list(, $e) = Rekening_koran_parser::manual_row(array($field => $case[0]) + $ok, $bank, 'H1', 7);
    mCheck(strpos(implode(';', $e), $case[1]) !== false, "Rejects bad $field");
}
list(, $e) = Rekening_koran_parser::manual_row(array('nominal_debit' => '0', 'nominal_kredit' => '0') + $ok, $bank, 'H1', 7);
mCheck(strpos(implode(';', $e), 'sama-sama 0') !== false, 'Debit and kredit both zero rejected');
list(, $e) = Rekening_koran_parser::manual_row(array('nominal_debit' => '-5') + $ok, $bank, 'H1', 7);
mCheck(strpos(implode(';', $e), 'negatif') !== false, 'Negative amount rejected');
list(, $e) = Rekening_koran_parser::manual_row(array('tanggal_transaksi' => 'PEND') + $ok, $bank, 'H1', 7);
mCheck($e !== array(), 'PEND date not allowed for manual rows');

// Guard admin: dicek di server sebelum penulisan apa pun, dan tombol/modal hanya di blok is_admin.
$ctl = file_get_contents(dirname(__DIR__) . '/controllers/Alokasi.php');
$start = strpos($ctl, 'function save_manual_alokasi');
$body = substr($ctl, $start, strpos($ctl, 'function get_bank_sesuai_alokasi') - $start);
mCheck(strpos($body, 'is_admin()') !== false && strpos($body, 'is_admin()') < strpos($body, '->insert('), 'Controller checks is_admin before any insert');
$view = file_get_contents(dirname(__DIR__) . '/views/index.php');
mCheck(preg_match('~<\?php if \(!empty\(\$is_admin\)\) : \?>\s*<button[^>]*dialog-manual~', $view) === 1, 'Manual button only inside is_admin block');
mCheck(preg_match('~<\?php if \(!empty\(\$is_admin\)\) : \?>\s*<div class="modal" id="dialog-manual"~', $view) === 1, 'Manual modal only inside is_admin block');
echo "PASS: Alokasi manual insert (row builder validation, admin-only guard in controller and view)\n";
