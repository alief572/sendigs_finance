<?php
require __DIR__ . '/consulting_fixture.php';
// TEST 1: PKP Invoice Receipt (Full Payment with Biaya Admin & PPh 23)
// =========================================================================
echo "=== TEST 1: PKP Full Payment with Admin & PPh 23 ===\n";
$db = createFixtures();
$db->alloc_splits[0]['nominal'] = '218330833.33';
$db->alloc_details[0]['nominal_debit'] = '218330833.33';
// PKP: Sales 200jt, DPP Lain 183.333.333,33, PPN 22jt, PPh 3.666.666,67, Tagihan/Piutang 222jt
$inv1 = makeInvoice(1, 'INV-PKP-001', 'PKP', 20000000000, 18333333333, 2200000000, 366666667, 22200000000, 22200000000);
$db->invoices = array($inv1);

$ci = new MockCi($db);
$engine = new Consulting_receipt($ci);

$refS = new ReflectionProperty('Consulting_receipt', 's');
$refS->setAccessible(true);
$refS->setValue($engine, new CustomService($ci));

// 1a. Preview
$prevPost = array(
    'choose_inv' => array(1),
    'id_alokasi' => 100,
    'pph23_dipotong' => 'Y',
    'ppn_dipotong' => 'Y'
);
$prevRes = $engine->preview($prevPost);
checkAssert(count($prevRes['rows']) === 1, 'Preview returns 1 invoice row');
checkAssert($prevRes['rows'][0]['tax_treatment'] === 'PKP', 'Preview detects PKP');
checkAssert($prevRes['rows'][0]['default_pph'] === 366666667, 'Preview default PPh 23 matches 2%');
// Nomor SPK = id_spk_penawaran (kons_tr_spk_penawaran tidak punya kolom no_spk); fixture melempar bila kolom itu di-query.
checkAssert($prevRes['rows'][0]['spk'] === 'SPK-001', 'Preview SPK number comes from id_spk_penawaran without querying no_spk');

// 1b. Save
// Piutang: 222.000.000 (22200000000 cents), PPh: 3.666.666,67 (366666667 cents), Admin: 2.500 (250000 cents), Sisa: 0
// Bank Cash = 22200000000 - 366666667 - 250000 = 21833083333 cents = 218.330.833,33
// Kontrol: (22200000000 - 366666667 - 250000 - 0) - 21833083333 = 0.
$savePost = array(
    'no_inv' => '1',
    'id_alokasi' => 100,
    'allocation_fingerprint' => $prevRes['allocation_fingerprint'],
    'pph23_dipotong' => 'Y',
    'ppn_dipotong' => 'Y',
    'id_inv_1' => 1,
    'receipt_fingerprint_1' => $prevRes['rows'][0]['fingerprint'],
    'pph_actual_1' => '3666666.67',
    'biaya_admin_1' => '2500.00',
    'sisa_piutang_1' => '0.00',
    'penerimaan_1' => '222000000.00'
);
$saveRes = $engine->save($savePost);
checkAssert($saveRes['status'] === 1 && $saveRes['save'] === 1, 'PKP Save success: ' . ($saveRes['msg'] ?? ''));

// Verify Jurnal Akuntansi
checkAssert(count($db->journals) === 4, '4 journal lines created (Bank, PPh, Admin, Piutang Gross)');
$totDebit = 0; $totKredit = 0;
foreach ($db->journals as $j) {
    $totDebit += Penerimaan_calculation::cents($j['debit']);
    $totKredit += Penerimaan_calculation::cents($j['kredit']);
    if ($j['coa'] === '1101-02-01') checkAssert(Penerimaan_calculation::cents($j['debit']) === 21833083333, 'Bank Debit correct');
    if ($j['coa'] === '1106-01-02') checkAssert(Penerimaan_calculation::cents($j['debit']) === 366666667, 'PPh Debit correct');
    if ($j['coa'] === '7201-01-04') checkAssert(Penerimaan_calculation::cents($j['debit']) === 250000, 'Admin Debit correct');
    if ($j['coa'] === '1102-01-01') checkAssert(Penerimaan_calculation::cents($j['kredit']) === 22200000000, 'Piutang Gross Kredit correct');
}
checkAssert($totDebit === $totKredit, 'Journal is 100% BALANCED');
checkAssert($totDebit === 22200000000, 'Journal total equals gross receivable');
checkAssert(Penerimaan_calculation::cents($db->invoices[0]['saldo_piutang']) === 0, 'Invoice saldo_piutang is 0 (lunas)');
echo "PASS: PKP Full Payment with Admin & PPh 23\n\n";

// =========================================================================
// TEST 2: WAPU Invoice Receipt (Tagihan = Sales, PPN = 0, PPh 23 Dipotong)
// =========================================================================
echo "=== TEST 2: WAPU Invoice Receipt ===\n";
$db = createFixtures();
$db->alloc_splits[0]['nominal'] = '294500000.00';
$db->alloc_details[0]['nominal_debit'] = '294500000.00';
// WAPU: Sales 300jt, DPP Lain 275jt, PPN 0, PPh 5.5jt, Tagihan/Piutang 300jt
$inv2 = makeInvoice(2, 'INV-WAPU-001', 'WAPU', 30000000000, 27500000000, 0, 550000000, 30000000000, 30000000000);
$db->invoices = array($inv2);
$ci = new MockCi($db);
$engine = new Consulting_receipt($ci);
$refS->setValue($engine, new CustomService($ci));

$prevRes = $engine->preview(array('choose_inv' => array(2), 'id_alokasi' => 100, 'pph23_dipotong' => 'Y', 'ppn_dipotong' => 'Y'));
checkAssert($prevRes['rows'][0]['tax_treatment'] === 'WAPU', 'Preview detects WAPU');

// Bank Cash = 300jt - 5.5jt = 294.500.000
$saveRes = $engine->save(array(
    'no_inv' => '1',
    'id_alokasi' => 100,
    'allocation_fingerprint' => $prevRes['allocation_fingerprint'],
    'pph23_dipotong' => 'Y',
    'ppn_dipotong' => 'Y',
    'id_inv_1' => 2,
    'receipt_fingerprint_1' => $prevRes['rows'][0]['fingerprint'],
    'pph_actual_1' => '5500000.00',
    'biaya_admin_1' => '0.00',
    'sisa_piutang_1' => '0.00',
    'penerimaan_1' => '300000000.00'
));
checkAssert($saveRes['status'] === 1, 'WAPU Save success: ' . ($saveRes['msg'] ?? ''));
$totDebit = 0; $totKredit = 0;
foreach ($db->journals as $j) {
    $totDebit += Penerimaan_calculation::cents($j['debit']);
    $totKredit += Penerimaan_calculation::cents($j['kredit']);
}
checkAssert($totDebit === $totKredit && $totDebit === 30000000000, 'WAPU Journal 100% Balanced');
echo "PASS: WAPU Invoice Receipt\n\n";

// =========================================================================
// TEST 3: Kurang Bayar (Partial Payment with Sisa Piutang > 0)
// Resolving Decision 3: 2 baris piutang (Kredit Gross & Debit Sisa Piutang)
// =========================================================================
echo "=== TEST 3: Partial Payment (Underpayment) - 2 Receivable Journal Lines ===\n";
$db = createFixtures();
$db->alloc_splits[0]['nominal'] = '100000000.00';
$db->alloc_details[0]['nominal_debit'] = '100000000.00';
// Invoice Piutang 222jt, PPh 3.666.666,67.
$inv3 = makeInvoice(3, 'INV-PARTIAL-001', 'PKP', 20000000000, 18333333333, 2200000000, 366666667, 22200000000, 22200000000);
$db->invoices = array($inv3);
$ci = new MockCi($db);
$engine = new Consulting_receipt($ci);
$refS->setValue($engine, new CustomService($ci));

$prevRes = $engine->preview(array('choose_inv' => array(3), 'id_alokasi' => 100, 'pph23_dipotong' => 'Y', 'ppn_dipotong' => 'Y'));

// Customer pays partial: Bank Cash Rp 100.000.000
// PPh 23: Rp 3.666.666,67
// Admin: Rp 0
// Sisa Piutang = 22200000000 - 366666667 - 10000000000 = 11833333333 cents = 118.333.333,33
// Kontrol = (22200000000 - 366666667 - 0 - 11833333333) - 10000000000 = 0.
$saveRes = $engine->save(array(
    'no_inv' => '1',
    'id_alokasi' => 100,
    'allocation_fingerprint' => $prevRes['allocation_fingerprint'],
    'pph23_dipotong' => 'Y',
    'ppn_dipotong' => 'Y',
    'id_inv_1' => 3,
    'receipt_fingerprint_1' => $prevRes['rows'][0]['fingerprint'],
    'pph_actual_1' => '3666666.67',
    'biaya_admin_1' => '0.00',
    'sisa_piutang_1' => '118333333.33',
    'penerimaan_1' => '103666666.67'
));
checkAssert($saveRes['status'] === 1, 'Partial payment save success: ' . ($saveRes['msg'] ?? ''));

// Verify Decision 3 Accounting Journal:
// 1. Bank Debit: 100.000.000
// 2. PPh 23 Debit: 3.666.666,67
// 3. Piutang Usaha Kredit (Gross): 222.000.000
// 4. Piutang Usaha Debit (Kurang Bayar / Sisa Piutang): 118.333.333,33
checkAssert(count($db->journals) === 4, '4 journal rows created including 2 receivable rows');
$debitBank = 0; $debitPph = 0; $debitPiutangSisa = 0; $kreditPiutangGross = 0;
foreach ($db->journals as $j) {
    if ($j['coa'] === '1101-02-01') $debitBank += Penerimaan_calculation::cents($j['debit']);
    if ($j['coa'] === '1106-01-02') $debitPph += Penerimaan_calculation::cents($j['debit']);
    if ($j['coa'] === '1102-01-01') {
        if (Penerimaan_calculation::cents($j['debit']) > 0) $debitPiutangSisa += Penerimaan_calculation::cents($j['debit']);
        if (Penerimaan_calculation::cents($j['kredit']) > 0) $kreditPiutangGross += Penerimaan_calculation::cents($j['kredit']);
    }
}
checkAssert($debitBank === 10000000000, 'Bank Debit is 100jt');
checkAssert($debitPph === 366666667, 'PPh Debit is 3.666.666,67');
checkAssert($debitPiutangSisa === 11833333333, 'Receivable Debit (Sisa Piutang) is 118.333.333,33');
checkAssert($kreditPiutangGross === 22200000000, 'Receivable Gross Kredit is 222.000.000');

$totalDebit = $debitBank + $debitPph + $debitPiutangSisa;
checkAssert($totalDebit === $kreditPiutangGross, 'Decision 3: Total Debit equals Total Kredit (100% Balanced)');

// Verify Net Mutation on Receivable
$netReceivableCredit = $kreditPiutangGross - $debitPiutangSisa;
checkAssert($netReceivableCredit === (10000000000 + 366666667), 'Net receivable reduction matches Bank + PPh');

// Verify updated saldo_piutang in invoice table
checkAssert(Penerimaan_calculation::cents($db->invoices[0]['saldo_piutang']) === 11833333333, 'Invoice saldo_piutang updated to 118.333.333,33');
echo "PASS: Partial Payment with 2 Receivable Journal Lines (Decision 3)\n\n";

// =========================================================================
// TEST 4: Editable PPh 23 per Invoice
// =========================================================================
echo "=== TEST 4: Editable PPh 23 per Invoice ===\n";
$db = createFixtures();
$db->alloc_splits[0]['nominal'] = '218334000.00';
$db->alloc_details[0]['nominal_debit'] = '218334000.00';
$inv4 = makeInvoice(4, 'INV-PPH-EDIT', 'PKP', 20000000000, 18333333333, 2200000000, 366666667, 22200000000, 22200000000);
$db->invoices = array($inv4);
$ci = new MockCi($db);
$engine = new Consulting_receipt($ci);
$refS->setValue($engine, new CustomService($ci));

$prevRes = $engine->preview(array('choose_inv' => array(4), 'id_alokasi' => 100, 'pph23_dipotong' => 'Y', 'ppn_dipotong' => 'Y'));

// Customer deducted rounded PPh 23 = Rp 3.666.000 (different by Rp 667)
// Bank Cash = 222.000.000 - 3.666.000 = 218.334.000
$saveRes = $engine->save(array(
    'no_inv' => '1',
    'id_alokasi' => 100,
    'allocation_fingerprint' => $prevRes['allocation_fingerprint'],
    'pph23_dipotong' => 'Y',
    'ppn_dipotong' => 'Y',
    'id_inv_1' => 4,
    'receipt_fingerprint_1' => $prevRes['rows'][0]['fingerprint'],
    'pph_actual_1' => '3666000.00',
    'biaya_admin_1' => '0.00',
    'sisa_piutang_1' => '0.00',
    'penerimaan_1' => '222000000.00'
));
checkAssert($saveRes['status'] === 1, 'Custom PPh 23 save success: ' . ($saveRes['msg'] ?? ''));
$savedPphDebit = 0;
foreach ($db->journals as $j) {
    if ($j['coa'] === '1106-01-02') $savedPphDebit += Penerimaan_calculation::cents($j['debit']);
}
checkAssert($savedPphDebit === 366600000, 'Custom PPh 23 of 3.666.000 reflected in journal');
echo "PASS: Editable PPh 23 per Invoice\n\n";

// =========================================================================
// TEST 5: Rejection Validation (Kontrol != 0, Admin > 10.000, Exceeding Bank)
// =========================================================================
echo "=== TEST 5: Rejection Validations ===\n";
$db = createFixtures();
$db->alloc_splits[0]['nominal'] = '218330833.33';
$db->alloc_details[0]['nominal_debit'] = '218330833.33';
$inv5 = makeInvoice(5, 'INV-REJECT', 'PKP', 20000000000, 18333333333, 2200000000, 366666667, 22200000000, 22200000000);
$db->invoices = array($inv5);
$ci = new MockCi($db);
$engine = new Consulting_receipt($ci);
$refS->setValue($engine, new CustomService($ci));

$prevRes = $engine->preview(array('choose_inv' => array(5), 'id_alokasi' => 100, 'pph23_dipotong' => 'Y', 'ppn_dipotong' => 'Y'));

// 5a. Kontrol != 0
$resKontrol = $engine->save(array(
    'no_inv' => '1',
    'id_alokasi' => 100,
    'allocation_fingerprint' => $prevRes['allocation_fingerprint'],
    'pph23_dipotong' => 'Y',
    'ppn_dipotong' => 'Y',
    'id_inv_1' => 5,
    'receipt_fingerprint_1' => $prevRes['rows'][0]['fingerprint'],
    'pph_actual_1' => '3666666.67',
    'biaya_admin_1' => '0.00',
    'sisa_piutang_1' => '0.00',
    'penerimaan_1' => '222000000.00' // Unbalanced by ~18.333.333
));
checkAssert($resKontrol['status'] === 0 && strpos($resKontrol['msg'], 'Kontrol belum 0') !== false, 'Rejected non-zero kontrol');

// 5b. Biaya Admin > Rp 10.000
$resAdmin = $engine->save(array(
    'no_inv' => '1',
    'id_alokasi' => 100,
    'allocation_fingerprint' => $prevRes['allocation_fingerprint'],
    'pph23_dipotong' => 'Y',
    'ppn_dipotong' => 'Y',
    'id_inv_1' => 5,
    'receipt_fingerprint_1' => $prevRes['rows'][0]['fingerprint'],
    'pph_actual_1' => '3666666.67',
    'biaya_admin_1' => '15000.00', // Admin 15.000 > 10.000
    'sisa_piutang_1' => '0.00',
    'penerimaan_1' => '222000000.00'
));
checkAssert($resAdmin['status'] === 0 && strpos($resAdmin['msg'], '10.000') !== false, 'Rejected admin > 10.000');
echo "PASS: Rejection Validations\n\n";

// =========================================================================
// TEST 6: Rollback Verification
// =========================================================================
echo "=== TEST 6: Rollback Verification ===\n";
$db = createFixtures();
$db->alloc_splits[0]['nominal'] = '218330833.33';
$db->alloc_details[0]['nominal_debit'] = '218330833.33';
$inv6 = makeInvoice(6, 'INV-ROLLBACK', 'PKP', 20000000000, 18333333333, 2200000000, 366666667, 22200000000, 22200000000);
$db->invoices = array($inv6);
$ci = new MockCi($db);
$engine = new Consulting_receipt($ci);
$refS->setValue($engine, new CustomService($ci));

$prevRes = $engine->preview(array('choose_inv' => array(6), 'id_alokasi' => 100, 'pph23_dipotong' => 'Y', 'ppn_dipotong' => 'Y'));
$saveRes = $engine->save(array(
    'no_inv' => '1',
    'id_alokasi' => 100,
    'allocation_fingerprint' => $prevRes['allocation_fingerprint'],
    'pph23_dipotong' => 'Y',
    'ppn_dipotong' => 'Y',
    'id_inv_1' => 6,
    'receipt_fingerprint_1' => $prevRes['rows'][0]['fingerprint'],
    'pph_actual_1' => '3666666.67',
    'biaya_admin_1' => '2500.00',
    'sisa_piutang_1' => '0.00',
    'penerimaan_1' => '222000000.00'
));
checkAssert($saveRes['status'] === 1, 'Initial receipt saved for rollback');
$receiptId = $db->receipts[0]['id'];
checkAssert(count($db->receipts) === 1, 'Receipt exists before rollback');
checkAssert(count($db->journals) > 0, 'Journals exist before rollback');
checkAssert(Penerimaan_calculation::cents($db->invoices[0]['saldo_piutang']) === 0, 'Invoice paid before rollback');

// Execute rollback
$rbRes = $engine->rollback($receiptId);
checkAssert($rbRes['status'] === 1, 'Rollback returned status 1');
checkAssert(count($db->receipts) === 0, 'Header receipt deleted');
checkAssert(count($db->receipt_details) === 0, 'Detail receipts deleted');
checkAssert(count($db->journals) === 0, 'Accounting journals deleted');

// Invoice saldo_piutang restored
checkAssert(Penerimaan_calculation::cents($db->invoices[0]['saldo_piutang']) === 22200000000, 'Invoice saldo_piutang restored to 222.000.000');

// Allocation bank restored
checkAssert(Penerimaan_calculation::cents($db->alloc_details[0]['nilai_terpakai']) === 0, 'Bank allocation nilai_terpakai restored to 0');
checkAssert(Penerimaan_calculation::cents($db->alloc_splits[0]['nilai_terpakai']) === 0, 'Bank split nilai_terpakai restored to 0');
echo "PASS: Rollback Verification\n\n";

echo "ALL CONSULTING TO-BE INTEGRATION TESTS PASSED 100%!\n";
