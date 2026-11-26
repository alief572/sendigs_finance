<?php
// Parser rekening koran (jenis 7 dan 22) + validasi baris, dengan fixture sintetis; tanpa DB.
define('BASEPATH', __DIR__);
require dirname(__DIR__) . '/libraries/Rekening_koran_parser.php';
function pCheck($ok, $m) { if (!$ok) throw new RuntimeException($m); }
function csv($rows) { return array_map('str_getcsv', array_map(function ($r) { return implode(',', array_map(function ($c) { return '"' . $c . '"'; }, $r)); }, $rows)); }

// Jenis 7: 7 baris header, data mulai baris ke-8: tanggal, keterangan, cabang, nominal + CR/DB, saldo.
$rows7 = array(array('No. rekening : 123'), array('Periode : 01/10/2026 - 31/10/2026'), array('Saldo Awal : 1,000.00'), array('Mutasi Debet : 0'), array('Mutasi Kredit : 0'), array('Saldo Akhir : 0'), array(''),
    array('30/10/2026', 'TRF MASUK', '0000', '1,500.00 CR', '2,500.00'),
    array('31/10/2026', 'BIAYA', '0000', '100.00 DB', '2,400.00'),
    array('PEND', 'PENDING', '0000', '50.00 DB', '2,350.00'));
$r = Rekening_koran_parser::parse(csv($rows7), array('bank' => '1', 'jenis_bank' => '7'), 'H1', 9);
pCheck($r['account_no'] === ' 123' || trim($r['account_no']) === '123', 'Account number');
pCheck($r['periode_from'] === '2026-10-01' && $r['periode_to'] === '2026-10-31', 'Periode');
pCheck(count($r['arr_detail']) === 3 && $r['arr_detail'][0]['nominal_debit'] == 1500 && $r['arr_detail'][1]['nominal_kredit'] == 100, 'CR is debit, DB is kredit');
pCheck($r['arr_detail'][0]['tanggal_transaksi'] === '2026-10-30' && $r['arr_detail'][0]['created_by'] === 9, 'Date and user');
pCheck(Rekening_koran_parser::validate($r['arr_detail']) === array(), 'PEND date is accepted');

// Nama bulan Indonesia tidak terbaca -> error per baris (nomor baris 1-based), baris lain tetap OK.
$bad = $rows7; $bad[7][0] = '30 Agustus 2026';
$errs = Rekening_koran_parser::validate(Rekening_koran_parser::parse(csv($bad), array('bank' => '1', 'jenis_bank' => '7'), 'H1', 9)['arr_detail']);
pCheck(array_keys($errs) === array(1) && strpos($errs[1], 'Tanggal tidak valid') !== false, 'Indonesian month name flagged on row 1 only');
pCheck(isset(Rekening_koran_parser::validate(array(array('tanggal_transaksi' => '2026-10-01', 'nominal_debit' => 0, 'nominal_kredit' => 0)))[1]), 'Zero amounts flagged');
pCheck(isset(Rekening_koran_parser::validate(array(array('tanggal_transaksi' => '2026-02-30', 'nominal_debit' => 5, 'nominal_kredit' => 0)))[1]), 'Impossible date flagged');

// Jenis 22: baris 2 berisi saldo awal pada kolom 7, data mulai baris ke-11.
$rows22 = array(array('', '', '', '', '', '', '', ''), array('', '', '', '', '', '', '', '1,000.00'), array('PERIOD :', '01/10/2026 - 31/10/2026'), array('ACCOUNT NO :', '456'), array('TOTAL DEBIT :', '', '0'), array('TOTAL CREDIT :', '', '0'),
    array('', '', '', '', '', '', '', ''), array('', '', '', '', '', '', '', ''), array('', '', '', '', '', '', '', ''), array('', '', '', '', '', '', '', ''),
    array('30/10/2026', '', "'REF1", '', 'SETORAN', '0.00', '2,000.00', '3,000.00'));
$r = Rekening_koran_parser::parse(csv($rows22), array('bank' => '2', 'jenis_bank' => '22'), 'H2', 9);
pCheck(count($r['arr_detail']) === 1 && $r['arr_detail'][0]['reference_no'] === 'REF1' && $r['arr_detail'][0]['nominal_debit'] == 2000 && $r['arr_detail'][0]['saldo'] == 3000, 'Type 22 row');
pCheck($r['account_no'] === '456' && $r['periode_from'] === '2026-10-01' && Rekening_koran_parser::validate($r['arr_detail']) === array(), 'Type 22 header and validation');
echo "PASS: rekening koran parser (types 7 and 22) and row validation\n";
