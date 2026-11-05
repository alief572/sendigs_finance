<?php
// Test Suite for Modul Kwitansi Deposit Customer & Integration with Penerimaan Uang
require dirname(__DIR__, 2) . '/penerimaan_uang/tests/consulting_fixture.php';
require dirname(__DIR__) . '/models/Kwitansi_deposit_model.php';

echo "=== MODUL KWITANSI DEPOSIT & INTEGRATION TEST SUITE ===\n\n";

$db = createFixtures();
$ci = new MockCi($db);
$engine = new Consulting_receipt($ci);
$refS = new ReflectionProperty('Consulting_receipt', 's');
$refS->setAccessible(true);
$refS->setValue($engine, new CustomService($ci));

$kwModel = new Kwitansi_deposit_model();
$kwModel->db = $db;

// =========================================================================
// TEST 1: Kwitansi Creation & Accrual Journal Balancing
// =========================================================================
echo "--- TEST 1: Kwitansi Creation, Numbering & Accrual Journal ---\n";

$kwPost = array(
    'id_customer' => 'CUST-001',
    'nm_customer' => 'PT Pelanggan Utama',
    'tanggal_kwitansi' => '2026-10-28',
    'id_company' => '1',
    'nm_company' => 'PT Konsultan Prima',
    'po_customer' => 'PO/2026/001',
    'nilai_kwitansi' => '50000000.00',
    'keterangan' => 'Deposit awal proyek konsultasi IT',
    'user_id' => 'USR-01'
);

$res = $kwModel->save_kwitansi($kwPost);
checkAssert($res['status'] === 1, 'Kwitansi created successfully');
checkAssert(!empty($res['no_kwitansi']), 'Kwitansi number generated');
checkAssert(strpos($res['no_kwitansi'], 'KD/2026/10/') === 0, 'Format KD/YYYY/MM/XXXX matches requirement');

$noKw = $res['no_kwitansi'];
echo "Generated Kwitansi No: {$noKw}\n";

// Check tr_kwitansi_deposit
$kwRow = null;
foreach ($db->kwitansi_deposits as $kd) {
    if ($kd['no_kwitansi'] === $noKw) { $kwRow = $kd; break; }
}
checkAssert($kwRow !== null, 'Header exists in tr_kwitansi_deposit');
checkAssert($kwRow['status'] === 'MENUNGGU_PEMBAYARAN', 'Header status is MENUNGGU_PEMBAYARAN');
checkAssert(Penerimaan_calculation::cents($kwRow['nilai_kwitansi']) === 5000000000, 'Nilai kwitansi is 50.000.000');

// Check tr_customer_deposit
$depRow = null;
foreach ($db->customer_deposits as $cd) {
    if ($cd['no_referensi'] === $noKw) { $depRow = $cd; break; }
}
checkAssert($depRow !== null, 'Voucher exists in tr_customer_deposit');
checkAssert($depRow['status'] === 'MENUNGGU_PEMBAYARAN', 'Deposit status is MENUNGGU_PEMBAYARAN (not yet active)');
checkAssert($depRow['tipe_sumber'] === 'KWITANSI_DEPOSIT', 'tipe_sumber is KWITANSI_DEPOSIT');

// Check tr_invoicing sync
$invRow = null;
foreach ($db->invoices as $inv) {
    if ($inv['no_invoice'] === $noKw) { $invRow = $inv; break; }
}
checkAssert($invRow !== null, 'Synced invoice exists in tr_invoicing');
checkAssert($invRow['tax_treatment'] === 'DEP', 'tax_treatment is DEP');
checkAssert(Penerimaan_calculation::cents($invRow['saldo_piutang']) === 5000000000, 'saldo_piutang is 50.000.000');
checkAssert(Penerimaan_calculation::cents($invRow['total_nominal_jurnal']) === 5000000000, 'DPP is 50.000.000');

// Check Accrual Journal in tr_jurnal
$journals = array_values(array_filter($db->journals, function($j) use ($noKw) {
    return ($j['no_transaksi'] ?? '') === $noKw;
}));
checkAssert(count($journals) === 2, 'Exactly 2 journal lines posted for accrual');
$debitTotal = 0; $kreditTotal = 0;
$hasPiutangDebit = false; $hasDepositKredit = false;
foreach ($journals as $j) {
    $d = Penerimaan_calculation::cents($j['debit']);
    $k = Penerimaan_calculation::cents($j['kredit']);
    $debitTotal += $d;
    $kreditTotal += $k;
    if ($j['coa'] === '1102-01-01' && $d === 5000000000) $hasPiutangDebit = true;
    if ($j['coa'] === '2102-01-02' && $k === 5000000000) $hasDepositKredit = true;
}
checkAssert($debitTotal === 5000000000 && $kreditTotal === 5000000000, 'Accrual journal is balanced (Debit = Kredit = 50.000.000)');
checkAssert($hasPiutangDebit, 'Debit Piutang Dagang Usaha (1102-01-01)');
checkAssert($hasDepositKredit, 'Kredit Deposit Customer (2102-01-02)');
echo "PASS: Kwitansi creation, numbering, and accrual journal balancing verified\n\n";

// =========================================================================
// TEST 2: Bank Settlement in Penerimaan Uang Activates Deposit
// =========================================================================
echo "--- TEST 2: Bank Settlement in Penerimaan Uang & Deposit Activation ---\n";

$invId = $invRow['id'];
$db->alloc_splits[0]['jenis_alokasi'] = '8';
$db->alloc_splits[0]['nominal'] = '50000000.00';
$db->alloc_details[0]['nominal_debit'] = '50000000.00';

$prevPost = array(
    'receipt_kind' => 'deposit',
    'choose_inv' => array($invId),
    'id_alokasi' => 100,
    'pph23_dipotong' => 'N',
    'ppn_dipotong' => 'Y',
    'deposit_choice' => 'N',
    'deposit_amount' => '0.00'
);
$prevRes = $engine->preview($prevPost);
checkAssert($prevRes['customer_deposit'] === 0, 'No active deposit yet for customer');
checkAssert($prevRes['rows'][0]['default_pph'] === 0, 'PPh default is 0 for DEP invoice');
checkAssert($prevRes['rows'][0]['tax_calc']['tagihan'] === 5000000000, 'Tagihan is 50.000.000');

$savePost = array(
    'receipt_kind' => 'deposit',
    'no_inv' => '1',
    'id_alokasi' => 100,
    'allocation_fingerprint' => $prevRes['allocation_fingerprint'],
    'pph23_dipotong' => 'N',
    'ppn_dipotong' => 'Y',
    'deposit_choice' => 'N',
    'deposit_amount' => '0.00',
    'id_inv_1' => $invId,
    'receipt_fingerprint_1' => $prevRes['rows'][0]['fingerprint'],
    'pph_actual_1' => '0.00',
    'biaya_admin_1' => '0.00',
    'sisa_piutang_1' => '0.00',
    'penerimaan_1' => '50000000.00',
    'dep_1' => '0.00',
    'lebih_bayar_1' => '0.00'
);
$db->alloc_splits[0]['nominal'] = '30000000.00';
$db->alloc_details[0]['nominal_debit'] = '30000000.00';
$partialPreview = $engine->preview($prevPost);
$partialPost = $savePost;
$partialPost['allocation_fingerprint'] = $partialPreview['allocation_fingerprint'];
$partialPost['receipt_fingerprint_1'] = $partialPreview['rows'][0]['fingerprint'];
$partialPost['penerimaan_1'] = '30000000.00';
$partialPost['sisa_piutang_1'] = '20000000.00';
$partialResult = $engine->save($partialPost);
checkAssert($partialResult['status'] === 0, 'Partial kwitansi settlement must be rejected');
checkAssert($db->kwitansi_deposits[0]['status'] === 'MENUNGGU_PEMBAYARAN', 'Partial settlement must not mark kwitansi paid');
checkAssert($db->customer_deposits[0]['status'] === 'MENUNGGU_PEMBAYARAN', 'Partial settlement must not activate deposit');
$db->alloc_splits[0]['nominal'] = '50000000.00';
$db->alloc_details[0]['nominal_debit'] = '50000000.00';
$saveRes = $engine->save($savePost);
checkAssert($saveRes['status'] === 1, 'Bank receipt settlement saved successfully');
$receiptNo = $saveRes['no_surat'];
echo "Settlement Receipt No: {$receiptNo}\n";

// Verify kwitansi deposit transitioned to LUNAS
$kwUpdated = null;
foreach ($db->kwitansi_deposits as $kd) {
    if ($kd['no_kwitansi'] === $noKw) { $kwUpdated = $kd; break; }
}
checkAssert($kwUpdated['status'] === 'LUNAS', 'tr_kwitansi_deposit status changed to LUNAS');
checkAssert($kwUpdated['no_penerimaan'] === $receiptNo, 'no_penerimaan updated with receipt no');

// Verify customer deposit transitioned to AKTIF
$depUpdated = null;
foreach ($db->customer_deposits as $cd) {
    if ($cd['no_referensi'] === $noKw) { $depUpdated = $cd; break; }
}
checkAssert($depUpdated['status'] === 'AKTIF', 'tr_customer_deposit status activated to AKTIF');

// Verify mutasi IN recorded
$mutasiIn = null;
foreach ($db->deposit_mutasi as $m) {
    if ($m['id_deposit'] == $depUpdated['id'] && $m['jenis_mutasi'] === 'IN') {
        $mutasiIn = $m; break;
    }
}
checkAssert($mutasiIn !== null, 'Mutasi IN recorded in tr_customer_deposit_mutasi');
checkAssert(Penerimaan_calculation::cents($mutasiIn['nominal']) === 5000000000, 'Mutasi IN nominal is 50.000.000');
checkAssert(Penerimaan_calculation::cents($mutasiIn['saldo_sesudah']) === 5000000000, 'Saldo sesudah mutasi is 50.000.000');

// Verify invoice saldo_piutang is now 0
foreach ($db->invoices as $inv) {
    if ($inv['id'] == $invId) {
        checkAssert(Penerimaan_calculation::cents($inv['saldo_piutang']) === 0, 'Kwitansi invoice saldo_piutang is 0');
    }
}
echo "PASS: Bank settlement and deposit activation verified\n\n";

// =========================================================================
// TEST 3: Subsequent Consumption of Activated Deposit via FIFO
// =========================================================================
echo "--- TEST 3: Subsequent Consumption of Activated Deposit via FIFO ---\n";

// Customer now has 50jt active deposit.
$activeBal = $engine->get_customer_active_deposit('CUST-001', '1');
checkAssert($activeBal === 5000000000, 'Active deposit is 50.000.000');

// Create new invoice INV-REG-001 (Tagihan 30jt, WAPU)
$inv2 = makeInvoice(202, 'INV-REG-001', 'WAPU', 3000000000, 2750000000, 0, 55000000, 3000000000, 3000000000);
$db->invoices[] = $inv2;

// Prepare bank receipt with 0 bank cash (paid fully with 29.450.000 deposit + 550.000 PPh = 30.000.000)
// Or with bankCash = 10jt, deposit = 19.450.000
// Let's do: Tagihan = 30jt, PPh = 550.000. Net tagihan = 29.450.000.
// We use 20jt deposit, and 9.450.000 bank cash.
// Kontrol: ((30jt - 550.000 - 0 - 0) - 9.450.000 - 20.000.000) + 0 = 0!
$db->alloc_details[] = array(
    'id' => 20,
    'bank' => 1,
    'tipe_bank' => 1,
    'tanggal_transaksi' => '2026-10-28',
    'nominal_debit' => '9450000.00',
    'nilai_terpakai' => '0.00',
    'saldo' => '9450000.00',
    'keterangan' => 'Terima Transfer Baru'
);
$db->alloc_splits[] = array(
    'id' => 200,
    'id_alokasi_detail' => 20,
    'nominal' => '9450000.00',
    'nilai_terpakai' => '0.00',
    'jenis_alokasi' => '1'
);

$prevPost2 = array(
    'choose_inv' => array(202),
    'id_alokasi' => 200,
    'pph23_dipotong' => 'Y',
    'ppn_dipotong' => 'N',
    'deposit_choice' => 'Y',
    'deposit_amount' => '20000000.00'
);
$prevRes2 = $engine->preview($prevPost2);
checkAssert($prevRes2['customer_deposit'] === 5000000000, 'Customer has 50jt active deposit');

$savePost2 = array(
    'no_inv' => '1',
    'id_alokasi' => 200,
    'allocation_fingerprint' => $prevRes2['allocation_fingerprint'],
    'pph23_dipotong' => 'Y',
    'ppn_dipotong' => 'N',
    'deposit_choice' => 'Y',
    'deposit_amount' => '20000000.00',
    'id_inv_1' => 202,
    'receipt_fingerprint_1' => $prevRes2['rows'][0]['fingerprint'],
    'pph_actual_1' => '550000.00',
    'biaya_admin_1' => '0.00',
    'sisa_piutang_1' => '0.00',
    'penerimaan_1' => '30000000.00',
    'dep_1' => '20000000.00',
    'lebih_bayar_1' => '0.00'
);
$saveRes2 = $engine->save($savePost2);
checkAssert($saveRes2['status'] === 1, 'Receipt with deposit consumption saved successfully');

// Deposit sisa_saldo should now be 50jt - 20jt = 30jt. Status TERPAKAI_SEBAGIAN.
foreach ($db->customer_deposits as $cd) {
    if ($cd['no_referensi'] === $noKw) {
        checkAssert(Penerimaan_calculation::cents($cd['sisa_saldo']) === 3000000000, 'Remaining deposit is 30.000.000');
        checkAssert(Penerimaan_calculation::cents($cd['nominal_terpakai']) === 2000000000, 'Nominal terpakai is 20.000.000');
        checkAssert($cd['status'] === 'TERPAKAI_SEBAGIAN', 'Status is TERPAKAI_SEBAGIAN');
    }
}
// Mutasi OUT recorded
$mutasiOut = null;
foreach ($db->deposit_mutasi as $m) {
    if ($m['id_deposit'] == $depUpdated['id'] && $m['jenis_mutasi'] === 'OUT') {
        $mutasiOut = $m; break;
    }
}
checkAssert($mutasiOut !== null, 'Mutasi OUT recorded for FIFO consumption');
checkAssert(Penerimaan_calculation::cents($mutasiOut['nominal']) === 2000000000, 'Mutasi OUT nominal is 20.000.000');
echo "PASS: Subsequent FIFO consumption of activated deposit verified\n\n";

// =========================================================================
// TEST 4: Rollback Guard Blocks Rollback if Deposit was Consumed
// =========================================================================
echo "--- TEST 4: Rollback Guard Blocks Rollback of Settled Kwitansi ---\n";

// Find receipt header ID for the first receipt (receiptNo)
$rcptHdr = null;
foreach ($db->receipts as $r) {
    if ($r['no_surat'] === $receiptNo) { $rcptHdr = $r; break; }
}
checkAssert($rcptHdr !== null, 'Receipt header found');

$rbResult = $engine->rollback($rcptHdr['id']);
checkAssert($rbResult['status'] === 0, 'Rollback correctly rejected by Rollback Guard');
checkAssert(strpos($rbResult['msg'], 'sudah digunakan pada transaksi lain') !== false, 'Correct error message on rollback attempt');
echo "Blocked successfully: {$rbResult['msg']}\n";
echo "PASS: Rollback guard blocks rollback of kwitansi settlement when deposit was consumed\n\n";

// =========================================================================
// TEST 5: Rollback Reversion when Unconsumed
// =========================================================================
echo "--- TEST 5: Rollback Reversion when Deposit is Unconsumed ---\n";

// 1. First rollback the second receipt that consumed 20jt
$rcptHdr2 = null;
foreach ($db->receipts as $r) {
    if ($r['no_surat'] === $saveRes2['no_surat']) { $rcptHdr2 = $r; break; }
}
checkAssert($rcptHdr2 !== null, 'Second receipt header found');
$rbResult2 = $engine->rollback($rcptHdr2['id']);
checkAssert($rbResult2['status'] === 1, 'Second receipt rolled back successfully');

// Deposit is now fully restored to 50jt and status AKTIF
foreach ($db->customer_deposits as $cd) {
    if ($cd['no_referensi'] === $noKw) {
        checkAssert(Penerimaan_calculation::cents($cd['sisa_saldo']) === 5000000000, 'Deposit restored to 50.000.000');
        checkAssert(Penerimaan_calculation::cents($cd['nominal_terpakai']) === 0, 'Nominal terpakai restored to 0');
        checkAssert($cd['status'] === 'AKTIF', 'Status restored to AKTIF');
    }
}

// 2. Now rollback the kwitansi settlement receipt (first receipt)
$rbResult1 = $engine->rollback($rcptHdr['id']);
checkAssert($rbResult1['status'] === 1, 'Kwitansi settlement rolled back successfully');

// Verify kwitansi deposit reverted to MENUNGGU_PEMBAYARAN
foreach ($db->kwitansi_deposits as $kd) {
    if ($kd['no_kwitansi'] === $noKw) {
        checkAssert($kd['status'] === 'MENUNGGU_PEMBAYARAN', 'Kwitansi status reverted to MENUNGGU_PEMBAYARAN');
        checkAssert($kd['no_penerimaan'] === null, 'no_penerimaan cleared');
    }
}
// Verify customer deposit reverted to MENUNGGU_PEMBAYARAN
foreach ($db->customer_deposits as $cd) {
    if ($cd['no_referensi'] === $noKw) {
        checkAssert($cd['status'] === 'MENUNGGU_PEMBAYARAN', 'Customer deposit status reverted to MENUNGGU_PEMBAYARAN');
    }
}
// Verify mutasi IN was removed
$mutasiInAfterRb = array_filter($db->deposit_mutasi, function($m) use ($depUpdated) {
    return $m['id_deposit'] == $depUpdated['id'] && $m['jenis_mutasi'] === 'IN';
});
checkAssert(empty($mutasiInAfterRb), 'Mutasi IN removed upon rollback');
echo "PASS: Kwitansi deposit and customer deposit reverted cleanly on unconsumed rollback\n\n";

$faultDb = createFixtures();
$faultDb->fail_commit = true;
$kwModel->db = $faultDb;
$commitRejected = false;
try { $kwModel->save_kwitansi($kwPost); } catch (RuntimeException $e) { $commitRejected = true; }
checkAssert($commitRejected, 'Failed commit must not report successful kwitansi');
checkAssert(count($faultDb->kwitansi_deposits) === 0 && count($faultDb->customer_deposits) === 0 && count($faultDb->invoices) === 0 && count($faultDb->journals) === 0, 'Failed commit rolls back every kwitansi write');

echo "ALL KWITANSI DEPOSIT & INTEGRATION TESTS PASSED 100%!\n";
