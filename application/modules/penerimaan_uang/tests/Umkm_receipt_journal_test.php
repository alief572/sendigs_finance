<?php
// Jurnal penerimaan UMKM Non-PKP (PPh Final 0,5%) = sheet "Jurnal Penerimaan" di Prosedur Jurnal Invoicing & Penerimaan.xlsx.
// Murni (tanpa DB): snapshot dibangun seperti Consulting_receipt::save(), lalu Invoice_consulting::receipt_journal().
// Jalankan: php application/modules/penerimaan_uang/tests/Umkm_receipt_journal_test.php
define('BASEPATH', __DIR__);
require dirname(__DIR__, 2) . '/invoicing/libraries/Invoice_consulting.php';
require dirname(__DIR__) . '/libraries/Penerimaan_calculation.php';

$checks = 0;
function umkmCheck($ok, $message) { global $checks; $checks++; if (!$ok) throw new RuntimeException('GAGAL: ' . $message); }

function umkmAccounts() {
    return array(
        'receivable' => array('code' => '1102-01-01', 'name' => 'Piutang Dagang Usaha'),
        'bank' => array('code' => '1101-02-01', 'name' => 'Bank BCA'),
        'admin' => array('code' => '7201-01-04', 'name' => 'Biaya Admin Bank'),
        'deposit' => array('code' => '2102-01-02', 'name' => 'Deposit Customer'),
        'pph' => array('code' => '1108-01-01', 'name' => 'PPh 23 Disetor'),
        'pph_final_expense' => array('code' => '', 'name' => 'Beban PPh Final'),
        'pph_final_payable' => array('code' => '', 'name' => 'Hutang PPh Final'),
    );
}

/** Sama dengan Consulting_receipt::save(): kas = piutang - PPh - admin - sisa - deposit + lebih. */
function umkmSnapshot($tax, $balance, $limit, $choice, $admin, $sisa, $dep, $lebih) {
    $pph = $choice === 'Y' ? $limit : 0;
    $self = ($tax === 'UMKM' && $choice === 'N') ? $limit : 0;
    $cash = $balance - $pph - $admin - $sisa - $dep + $lebih;
    $reduction = $balance - $sisa;
    $s = array('calculation_version' => 4, 'spk' => 'SPK-1', 'owner' => array('id' => 7, 'nm_company' => 'STM'),
        'accounts' => umkmAccounts(), 'invoice_no' => '001/STM/2026', 'date' => '2026-11-10', 'cash' => $cash, 'alok' => $reduction,
        'admin' => $admin, 'pph' => $pph, 'sisa' => $sisa, 'dep' => $dep, 'lebih' => $lebih, 'balance' => $balance,
        'reduction' => $reduction, 'tax_treatment' => $tax);
    if ($tax === 'UMKM') $s['pph_final_self'] = $self;
    return $s;
}

/** Jumlah debit/kredit per nama akun (sen). */
function umkmTotals($lines) {
    $t = array();
    foreach ($lines as $l) {
        $t[$l['name']]['D'] = ($t[$l['name']]['D'] ?? 0) + $l['debit'];
        $t[$l['name']]['K'] = ($t[$l['name']]['K'] ?? 0) + $l['credit'];
    }
    return $t;
}

// [kasus, piutang, batas PPh Final 0,5%, admin, sisa, deposit, lebih, bank dipotong (Y), bank setor sendiri (N)]
$cases = array(
    array('1 Penerimaan DP', 61500000, 307500, 250000, 0, 0, 0, 60942500, 61250000),
    array('2 Progress', 200000000, 1000000, 250000, 0, 0, 0, 198750000, 199750000),
    array('3 Lebih bayar 1.000.000', 200000000, 1000000, 250000, 0, 0, 100000000, 298750000, 299750000),
    array('4 Kurang bayar 1.000.000', 200000000, 1000000, 250000, 100000000, 0, 0, 98750000, 99750000),
    array('5 Bank + Deposit 500.000', 200000000, 1000000, 250000, 0, 50000000, 0, 148750000, 149750000),
);
foreach ($cases as $c) {
    list($name, $balance, $limit, $admin, $sisa, $dep, $lebih, $bankY, $bankN) = $c;
    // Batas PPh Final dari perhitungan sistem sama dengan Excel.
    umkmCheck(Penerimaan_calculation::tax_calc($balance, 'UMKM')['pph_default'] === $limit, "$name batas PPh Final");
    foreach (array('Y' => $bankY, 'N' => $bankN) as $choice => $bank) {
        $label = "$name / " . ($choice === 'Y' ? 'dipotong' : 'setor sendiri');
        $lines = Invoice_consulting::receipt_journal(umkmSnapshot('UMKM', $balance, $limit, $choice, $admin, $sisa, $dep, $lebih));
        $t = umkmTotals($lines);
        umkmCheck(array_sum(array_column($lines, 'debit')) === array_sum(array_column($lines, 'credit')), "$label seimbang");
        umkmCheck(($t['Bank BCA']['D'] ?? 0) === $bank, "$label bank masuk $bank");
        umkmCheck(($t['Biaya Admin Bank']['D'] ?? 0) === $admin, "$label biaya admin");
        umkmCheck(($t['Piutang Dagang Usaha']['K'] ?? 0) === $balance, "$label piutang dikreditkan penuh");
        umkmCheck(($t['Piutang Dagang Usaha']['D'] ?? 0) === $sisa, "$label sisa piutang didebit");
        umkmCheck(($t['Deposit Customer']['D'] ?? 0) === $dep && ($t['Deposit Customer']['K'] ?? 0) === $lebih, "$label deposit dipakai / lebih bayar");
        umkmCheck(!isset($t['PPh 23 Disetor']), "$label tidak memakai PPh 23 Disetor");
        umkmCheck(($t['Beban PPh Final']['D'] ?? 0) === $limit, "$label Beban PPh Final D = PPh Final");
        umkmCheck(($t['Hutang PPh Final']['K'] ?? 0) === ($choice === 'N' ? $limit : 0), "$label Hutang PPh Final K hanya bila setor sendiri");
        foreach ($lines as $l) umkmCheck(substr($l['description'], -15) === ' - UMKM Non-PKP', "$label deskripsi berakhiran UMKM: " . $l['name']);
        foreach ($lines as $l) {
            $isFinal = in_array($l['name'], array('Beban PPh Final', 'Hutang PPh Final'), true);
            umkmCheck($isFinal ? $l['coa'] === '' : $l['coa'] !== '', "$label COA kosong hanya untuk akun PPh Final: " . $l['name']);
        }
    }
}

// Invoice PKP tidak berubah: PPh 23 Disetor, tanpa akun PPh Final, tanpa akhiran UMKM.
$pkp = umkmTotals($lines = Invoice_consulting::receipt_journal(umkmSnapshot('PKP', 222000000, 4000000, 'Y', 250000, 0, 0, 0)));
umkmCheck(($pkp['PPh 23 Disetor']['D'] ?? 0) === 4000000 && !isset($pkp['Beban PPh Final']) && !isset($pkp['Hutang PPh Final']), 'PKP tetap PPh 23 Disetor');
foreach ($lines as $l) umkmCheck(strpos($l['description'], 'UMKM') === false, 'PKP tanpa akhiran UMKM');
$kb = umkmSnapshot('KB', 60000000, 1200000, 'Y', 0, 0, 0, 0);
foreach (Invoice_consulting::receipt_journal($kb) as $l) umkmCheck(substr($l['description'], -18) === ' - Kawasan Berikat', 'KB tetap berakhiran Kawasan Berikat');

// Validasi snapshot.
function umkmRejects($snapshot, $message) {
    try { Invoice_consulting::receipt_journal($snapshot); umkmCheck(false, 'Seharusnya ditolak: ' . $message); }
    catch (RuntimeException $e) { umkmCheck(true, $message); }
}
$bad = umkmSnapshot('PKP', 222000000, 4000000, 'N', 0, 0, 0, 0); $bad['pph_final_self'] = 1000;
umkmRejects($bad, 'pph_final_self pada invoice non-UMKM ditolak');
$bad = umkmSnapshot('UMKM', 200000000, 1000000, 'N', 0, 0, 0, 0); $bad['pph'] = 500000; $bad['cash'] -= 500000;
umkmRejects($bad, 'PPh dipotong dan setor sendiri bersamaan ditolak');
$bad = umkmSnapshot('UMKM', 200000000, 1000000, 'N', 0, 0, 0, 0); $bad['pph_final_self'] = -1;
umkmRejects($bad, 'pph_final_self negatif ditolak');
$bad = umkmSnapshot('UMKM', 200000000, 1000000, 'N', 0, 0, 0, 0); $bad['pph_final_self'] = '1000000';
umkmRejects($bad, 'pph_final_self bukan integer ditolak');
$bad = umkmSnapshot('UMKM', 200000000, 1000000, 'Y', 0, 0, 0, 0); $bad['accounts']['bank']['code'] = '';
umkmRejects($bad, 'COA kosong untuk bank tetap ditolak');
$bad = umkmSnapshot('UMKM', 200000000, 1000000, 'N', 0, 0, 0, 0); unset($bad['accounts']['pph_final_payable']);
umkmRejects($bad, 'akun Hutang PPh Final hilang ditolak');
$bad = umkmSnapshot('UMKM', 200000000, 1000000, 'Y', 0, 0, 0, 0); $bad['cash'] += 1;
umkmRejects($bad, 'snapshot tidak konsisten ditolak');

echo "PASS: $checks cek jurnal penerimaan UMKM Non-PKP\n";
