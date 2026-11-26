<?php
// Test Suite for Deposit Integration, FIFO Consumption, and Rollback Guard
require __DIR__ . '/consulting_fixture.php';

echo "=== DEPOSIT INTEGRATION & FIFO CONSUMPTION TEST SUITE ===\n\n";

// =========================================================================
// TEST 1: Overpayment (Lebih Bayar) -> Creates Customer Deposit & Credit 2102-01-02
// =========================================================================
echo "--- TEST 1: Overpayment (Lebih Bayar) & Journal Balancing ---\n";
$db = createFixtures();
// Invoice tagihan: 100jt (10000000000 cents). PPh 2%: 1.833.333,33 (183333333 cents). Admin: 0. Sisa: 0.
// Customer transfers 105jt (10500000000 cents).
// Overpayment (Lebih bayar): 105.000.000 - (100.000.000 - 1.833.333,33) = 6.833.333,33 (683333333 cents).
// Equation: bankCash = balance (100jt) - pph (1.833.333,33) - admin (0) - sisa (0) - dep (0) + lebih (6.833.333,33) = 105.000.000.
// Kontrol: ((100jt - 1.833.333,33 - 0 - 0) - 105jt - 0) + 6.833.333,33 = 0.
$db->alloc_splits[0]['nominal'] = '105000000.00';
$db->alloc_details[0]['nominal_debit'] = '105000000.00';
$inv1 = makeInvoice(101, 'INV-DEP-001', 'PKP', 9090909091, 9166666667, 1000000000, 183333333, 10000000000, 10000000000);
$db->invoices = array($inv1);

seedDepositEvidence($db);
$ci = new MockCi($db);
$engine = new Consulting_receipt($ci);
$refS = new ReflectionProperty('Consulting_receipt', 's');
$refS->setAccessible(true);
$refS->setValue($engine, new CustomService($ci));

$prevPost = array(
    'choose_inv' => array(101),
    'id_alokasi' => 100,
    'pph23_dipotong' => 'Y',
    'ppn_dipotong' => 'Y',
    'deposit_choice' => 'N',
    'deposit_amount' => '0.00'
);
$prevRes = $engine->preview($prevPost);
checkAssert($prevRes['customer_deposit'] === 0, 'No initial deposit for CUST-001');

$defaultPphCents = $prevRes['rows'][0]['default_pph'];
// Bank nominal is 105jt. Piutang is 100jt.
// bankCash needed without overpayment = 100.000.000 - pph.
// overpayment = 105.000.000 - (100.000.000 - pph) = 5.000.000 + pph.
$lebihCents = 10500000000 - (10000000000 - $defaultPphCents);

$savePost = array(
    'no_inv' => '1',
    'id_alokasi' => 100,
    'allocation_fingerprint' => $prevRes['allocation_fingerprint'],
    'pph23_dipotong' => 'Y',
    'ppn_dipotong' => 'Y',
    'deposit_choice' => 'N',
    'deposit_amount' => '0.00',
    'id_inv_1' => 101,
    'receipt_fingerprint_1' => $prevRes['rows'][0]['fingerprint'],
    'pph_actual_1' => Penerimaan_calculation::decimal($defaultPphCents),
    'biaya_admin_1' => '0.00',
    'sisa_piutang_1' => '0.00',
    'penerimaan_1' => '100000000.00',
    'dep_1' => '0.00',
    'lebih_bayar_1' => Penerimaan_calculation::decimal($lebihCents)
);
$saveRes = $engine->save($savePost);
if (($saveRes['status'] ?? 0) !== 1) {
    echo "SAVE FAILED: " . ($saveRes['msg'] ?? json_encode($saveRes)) . "\n";
}
checkAssert(($saveRes['status'] ?? 0) === 1 && ($saveRes['save'] ?? 0) === 1, 'Save overpayment success: ' . ($saveRes['msg'] ?? ''));

// Verify tr_customer_deposit created
checkAssert(count($db->customer_deposits) === 1, '1 customer deposit record created');
$dep = $db->customer_deposits[0];
checkAssert($dep['id_customer'] === 'CUST-001', 'Deposit belongs to CUST-001');
checkAssert($dep['tipe_sumber'] === 'LEBIH_BAYAR', 'Source type is LEBIH_BAYAR');
checkAssert(Penerimaan_calculation::cents($dep['nominal_awal']) === $lebihCents, 'Deposit initial amount matches');
checkAssert(Penerimaan_calculation::cents($dep['sisa_saldo']) === $lebihCents, 'Deposit remaining balance matches');
checkAssert($dep['status'] === 'AKTIF', 'Deposit status is AKTIF');

// Verify tr_customer_deposit_mutasi created
checkAssert(count($db->deposit_mutasi) === 1, '1 mutasi record created');
$mut = $db->deposit_mutasi[0];
checkAssert($mut['id_deposit'] == $dep['id'], 'Mutasi linked to deposit');
checkAssert($mut['jenis_mutasi'] === 'IN', 'Mutasi is IN');
checkAssert(Penerimaan_calculation::cents($mut['nominal']) === $lebihCents, 'Mutasi nominal matches');

// Verify Journal balancing & Deposit Customer credit
$totDebit = 0; $totKredit = 0;
$foundDepJournal = false;
foreach ($db->journals as $j) {
    $totDebit += Penerimaan_calculation::cents($j['debit']);
    $totKredit += Penerimaan_calculation::cents($j['kredit']);
    if ($j['coa'] === '2102-01-02') {
        $foundDepJournal = true;
        checkAssert(Penerimaan_calculation::cents($j['kredit']) === $lebihCents, 'Deposit COA 2102-01-02 credited');
        checkAssert(Penerimaan_calculation::cents($j['debit']) === 0, 'Deposit COA 2102-01-02 debit is 0');
    }
}
checkAssert($foundDepJournal, 'Journal line for 2102-01-02 exists');
checkAssert($totDebit === $totKredit, 'Journal is 100% BALANCED');
echo "PASS: Overpayment creates deposit voucher & balanced journal\n\n";

// =========================================================================
// TEST 2: Pakai Deposit with FIFO Consumption Across 2 Vouchers
// =========================================================================
echo "--- TEST 2: Pakai Deposit & FIFO Consumption across 2 Vouchers ---\n";
// Let's setup CUST-001 with 2 existing deposit vouchers:
// Voucher 1: nominal_awal = 3jt, sisa_saldo = 3jt, status = 'AKTIF', date = 2026-10-01
// Voucher 2: nominal_awal = 5jt, sisa_saldo = 5jt, status = 'AKTIF', date = 2026-10-05
// Total active deposit = 8jt.
// Invoice: tagihan 10jt. PPh 2% = 0. Admin = 0. Sisa = 0.
// User chooses 'Pakai Deposit' with nominal 5jt (500000000 cents).
// Bank cash needed = 10jt - 5jt = 5jt.
// FIFO consumption should consume Voucher 1 fully (3jt -> sisa 0, status LUNAS_TERPAKAI)
// and Voucher 2 partially (2jt consumed -> sisa 3jt, status TERPAKAI_SEBAGIAN).
$db = createFixtures();
$db->alloc_splits[0]['nominal'] = '95000000.00';
$db->alloc_details[0]['nominal_debit'] = '95000000.00';

$db->customer_deposits = array(
    array(
        'id' => 1,
        'no_deposit' => 'DEP/2026/10/0001',
        'id_customer' => 'CUST-001',
        'nm_customer' => 'PT Mitra Sejahtera',
        'tanggal' => '2026-10-01', 'tanggal_aktif'=>'2026-10-01',
        'tipe_sumber' => 'LEBIH_BAYAR',
        'no_referensi' => 'HIST-REC-001',
        'nominal_awal' => '3000000.00',
        'nominal_terpakai' => '0.00',
        'sisa_saldo' => '3000000.00',
        'status' => 'AKTIF'
    ),
    array(
        'id' => 2,
        'no_deposit' => 'DEP/2026/10/0002',
        'id_customer' => 'CUST-001',
        'nm_customer' => 'PT Mitra Sejahtera',
        'tanggal' => '2026-10-05', 'tanggal_aktif'=>'2026-10-05',
        'tipe_sumber' => 'LEBIH_BAYAR',
        'no_referensi' => 'HIST-REC-002',
        'nominal_awal' => '5000000.00',
        'nominal_terpakai' => '0.00',
        'sisa_saldo' => '5000000.00',
        'status' => 'AKTIF'
    )
);

$inv2 = makeInvoice(201, 'INV-DEP-002', 'PKP', 10000000000, 9166666667, 0, 0, 10000000000, 10000000000);
$db->invoices = array($inv2);

seedDepositEvidence($db);
$ci = new MockCi($db);
$engine = new Consulting_receipt($ci);
$refS->setValue($engine, new CustomService($ci));

$prevPost = array(
    'choose_inv' => array(201),
    'id_alokasi' => 100,
    'pph23_dipotong' => 'N',
    'ppn_dipotong' => 'N',
    'deposit_choice' => 'Y',
    'deposit_amount' => '5000000.00'
);
$prevRes = $engine->preview($prevPost);
checkAssert($prevRes['customer_deposit'] === 800000000, 'Preview shows 8.000.000 active deposit');

$savePost = array(
    'no_inv' => '1',
    'id_alokasi' => 100,
    'allocation_fingerprint' => $prevRes['allocation_fingerprint'],
    'pph23_dipotong' => 'N',
    'ppn_dipotong' => 'N',
    'deposit_choice' => 'Y',
    'deposit_amount' => '5000000.00',
    'id_inv_1' => 201,
    'receipt_fingerprint_1' => $prevRes['rows'][0]['fingerprint'],
    'pph_actual_1' => '0.00',
    'biaya_admin_1' => '0.00',
    'sisa_piutang_1' => '0.00',
    'penerimaan_1' => '100000000.00',
    'dep_1' => '5000000.00',
    'lebih_bayar_1' => '0.00'
);
$saveRes = $engine->save($savePost);
if (($saveRes['status'] ?? 0) !== 1) {
    echo "TEST 2 SAVE FAILED: " . ($saveRes['msg'] ?? json_encode($saveRes)) . "\n";
}
checkAssert($saveRes['status'] === 1 && $saveRes['save'] === 1, 'Save pakai deposit success: ' . ($saveRes['msg'] ?? ''));

// Check FIFO consumption on Voucher 1
$v1 = $db->customer_deposits[0];
checkAssert(Penerimaan_calculation::cents($v1['sisa_saldo']) === 0, 'Voucher 1 sisa_saldo is 0');
checkAssert(Penerimaan_calculation::cents($v1['nominal_terpakai']) === 300000000, 'Voucher 1 nominal_terpakai is 3.000.000');
checkAssert($v1['status'] === 'LUNAS_TERPAKAI', 'Voucher 1 status is LUNAS_TERPAKAI');

// Check FIFO consumption on Voucher 2
$v2 = $db->customer_deposits[1];
checkAssert(Penerimaan_calculation::cents($v2['sisa_saldo']) === 300000000, 'Voucher 2 sisa_saldo is 3.000.000');
checkAssert(Penerimaan_calculation::cents($v2['nominal_terpakai']) === 200000000, 'Voucher 2 nominal_terpakai is 2.000.000');
checkAssert($v2['status'] === 'TERPAKAI_SEBAGIAN', 'Voucher 2 status is TERPAKAI_SEBAGIAN');

// Check mutasi records (2 OUT mutasi rows)
checkAssert(count(depositOuts($db)) === 2, '2 OUT mutasi records generated');
checkAssert(depositOuts($db)[0]['jenis_mutasi'] === 'OUT' && Penerimaan_calculation::cents(depositOuts($db)[0]['nominal']) === 300000000, 'Mutasi 1 is 3jt OUT');
checkAssert(depositOuts($db)[1]['jenis_mutasi'] === 'OUT' && Penerimaan_calculation::cents(depositOuts($db)[1]['nominal']) === 200000000, 'Mutasi 2 is 2jt OUT');

// Verify Journal balancing & Deposit Customer debit
$totDebit = 0; $totKredit = 0;
$foundDepDebit = false;
foreach ($db->journals as $j) {
    $totDebit += Penerimaan_calculation::cents($j['debit']);
    $totKredit += Penerimaan_calculation::cents($j['kredit']);
    if ($j['coa'] === '2102-01-02') {
        $foundDepDebit = true;
        checkAssert(Penerimaan_calculation::cents($j['debit']) === 500000000, 'Deposit COA 2102-01-02 debited 5jt');
        checkAssert(Penerimaan_calculation::cents($j['kredit']) === 0, 'Deposit COA 2102-01-02 credit is 0');
    }
}
checkAssert($foundDepDebit, 'Journal line for 2102-01-02 debit exists');
checkAssert($totDebit === $totKredit, 'Journal is 100% BALANCED');
echo "PASS: FIFO consumption across 2 vouchers & journal debit balanced\n\n";

// =========================================================================
// TEST 3: Rollback Guard when Created Lebih Bayar Deposit is Already Used
// =========================================================================
echo "--- TEST 3: Rollback Guard Blocks Rollback if Deposit Was Used ---\n";
// Setup receipt with generated deposit
$receiptId = $db->receipts[count($db->receipts)-1]['id'];
$receiptNo = $db->receipts[count($db->receipts)-1]['no_surat'];

// Scenario A: Deposit has nominal_terpakai > 0
$db->customer_deposits[] = array(
    'id' => 99,
    'no_deposit' => 'DEP/2026/10/0099',
    'id_customer' => 'CUST-001',
    'nm_customer' => 'PT Mitra Sejahtera',
    'tanggal' => '2026-10-26', 'tanggal_aktif'=>'2026-10-26',
    'tipe_sumber' => 'LEBIH_BAYAR',
    'no_referensi' => $receiptNo,
    'nominal_awal' => '1000000.00',
    'nominal_terpakai' => '500000.00', // already used 500rb!
    'sisa_saldo' => '500000.00',
    'status' => 'TERPAKAI_SEBAGIAN'
);

$rbFail = $engine->rollback($receiptId);
checkAssert(($rbFail['status'] ?? 1) === 0, 'Rollback returned status 0');
checkAssert(strpos($rbFail['msg'] ?? '', 'Rollback ditolak: Saldo deposit lebih bayar dari transaksi ini sudah digunakan') !== false, 'Rollback guard blocked with message: ' . ($rbFail['msg'] ?? ''));
echo "PASS: Rollback guard blocks rollback of consumed overpayment deposit\n\n";

// =========================================================================
// TEST 4: Rollback Restores Consumed Deposits (Restores sisa_saldo & status)
// =========================================================================
echo "--- TEST 4: Rollback Restores Consumed Deposits ---\n";
// From Test 2, we have:
// Voucher 1: was 3jt, fully consumed (sisa 0, status LUNAS_TERPAKAI)
// Voucher 2: was 5jt, 2jt consumed (sisa 3jt, status TERPAKAI_SEBAGIAN)
// Receipt: $receiptId
// Now remove the dummy deposit 99
$db->customer_deposits = array_values(array_filter($db->customer_deposits, function($c) { return $c['id'] != 99; }));

// Perform rollback
$rollbackRes = $engine->rollback($receiptId);
checkAssert($rollbackRes['status'] === 1, 'Rollback executed successfully: ' . ($rollbackRes['msg'] ?? ''));

// Verify Voucher 1 restored to original 3jt and AKTIF
$restoredV1 = $db->customer_deposits[0];
checkAssert(Penerimaan_calculation::cents($restoredV1['sisa_saldo']) === 300000000, 'Voucher 1 sisa_saldo restored to 3jt');
checkAssert(Penerimaan_calculation::cents($restoredV1['nominal_terpakai']) === 0, 'Voucher 1 nominal_terpakai restored to 0');
checkAssert($restoredV1['status'] === 'AKTIF', 'Voucher 1 status restored to AKTIF');

// Verify Voucher 2 restored to original 5jt and AKTIF
$restoredV2 = $db->customer_deposits[1];
checkAssert(Penerimaan_calculation::cents($restoredV2['sisa_saldo']) === 500000000, 'Voucher 2 sisa_saldo restored to 5jt');
checkAssert(Penerimaan_calculation::cents($restoredV2['nominal_terpakai']) === 0, 'Voucher 2 nominal_terpakai restored to 0');
checkAssert($restoredV2['status'] === 'AKTIF', 'Voucher 2 status restored to AKTIF');

// Verify mutasi OUT records deleted
checkAssert(count(depositOuts($db)) === 0, 'OUT mutasi records deleted upon rollback');
echo "PASS: Consumed deposit vouchers restored to AKTIF upon rollback\n\n";

// =========================================================================
// TEST 5: Rejection when Deposit Amount Exceeds Active Customer Deposit
// =========================================================================
echo "--- TEST 5: Rejection when Deposit Amount Exceeds Active Balance ---\n";
$db = createFixtures();
$db->customer_deposits = array(
    array(
        'id' => 1,
        'no_deposit' => 'DEP/2026/10/0001',
        'id_customer' => 'CUST-001',
        'nm_customer' => 'PT Mitra Sejahtera',
        'tanggal' => '2026-10-01', 'tanggal_aktif'=>'2026-10-01',
        'tipe_sumber' => 'LEBIH_BAYAR',
        'no_referensi' => '00001/RKN-KRG/X/26',
        'nominal_awal' => '2000000.00',
        'nominal_terpakai' => '0.00',
        'sisa_saldo' => '2000000.00', // only 2jt available!
        'status' => 'AKTIF'
    )
);
$inv5 = makeInvoice(501, 'INV-DEP-005', 'PKP', 10000000000, 9166666667, 0, 0, 10000000000, 10000000000);
$db->invoices = array($inv5);
seedDepositEvidence($db);
$ci = new MockCi($db);
$engine = new Consulting_receipt($ci);
$refS->setValue($engine, new CustomService($ci));

$prevPost = array(
    'choose_inv' => array(501),
    'id_alokasi' => 100,
    'pph23_dipotong' => 'N',
    'ppn_dipotong' => 'N',
    'deposit_choice' => 'Y',
    'deposit_amount' => '3000000.00' // asking for 3jt
);
$prevRes = $engine->preview($prevPost);

$savePost = array(
    'no_inv' => '1',
    'id_alokasi' => 100,
    'allocation_fingerprint' => $prevRes['allocation_fingerprint'],
    'pph23_dipotong' => 'N',
    'ppn_dipotong' => 'N',
    'deposit_choice' => 'Y',
    'deposit_amount' => '3000000.00',
    'id_inv_1' => 501,
    'receipt_fingerprint_1' => $prevRes['rows'][0]['fingerprint'],
    'pph_actual_1' => '0.00',
    'biaya_admin_1' => '0.00',
    'sisa_piutang_1' => '0.00',
    'penerimaan_1' => '100000000.00',
    'dep_1' => '3000000.00',
    'lebih_bayar_1' => '0.00'
);

$res5 = $engine->save($savePost);
checkAssert(($res5['status'] ?? 1) === 0, 'Save returned status 0 when deposit exceeds balance');
checkAssert(strpos($res5['msg'] ?? '', 'melebihi saldo deposit customer') !== false, 'Error message matches: ' . ($res5['msg'] ?? ''));
echo "PASS: Rejection when deposit exceeds active balance\n\n";

// =========================================================================
// TEST 6: Rejection when dep > 0 and lebih > 0 simultaneously
// =========================================================================
echo "--- TEST 6: Rejection when dep > 0 and lebih > 0 simultaneously ---\n";
$savePost['deposit_amount'] = '2000000.00';
$savePost['dep_1'] = '2000000.00';
$savePost['lebih_bayar_1'] = '500000.00'; // simultaneous!

$res6 = $engine->save($savePost);
checkAssert(($res6['status'] ?? 1) === 0, 'Save returned status 0 when deposit and lebih bayar both filled');
checkAssert(strpos($res6['msg'] ?? '', 'tidak boleh terisi bersamaan') !== false, 'Error message matches: ' . ($res6['msg'] ?? ''));
echo "PASS: Rejection when deposit and lebih bayar are both filled\n\n";

// =========================================================================
// TEST 7: Stress Test - 12 Identical-Date Vouchers FIFO Allocation & Rollback
// =========================================================================
echo "--- TEST 7: 12 Identical-Date Vouchers FIFO Allocation & Rollback ---\n";
$db = createFixtures();
$db->customer_deposits = array();
for ($i = 1; $i <= 12; $i++) {
    $db->customer_deposits[] = array(
        'id' => $i,
        'no_deposit' => sprintf('DEP/2026/10/%04d', $i),
        'id_customer' => 'CUST-001',
        'nm_customer' => 'PT Mitra Sejahtera',
        'tanggal' => '2026-10-15', 'tanggal_aktif'=>'2026-10-15', // identical date for all 12 vouchers
        'tipe_sumber' => ($i % 2 === 0 ? 'KWITANSI_DEPOSIT' : 'LEBIH_BAYAR'),
        'no_referensi' => 'REF-' . $i,
        'nominal_awal' => '100000.00',
        'nominal_terpakai' => '0.00',
        'sisa_saldo' => '100000.00',
        'status' => 'AKTIF'
    );
}

// Invoice 1.250.000,00 (tagihan 1.250.000,00, no tax, no admin)
// Split payment: Bank transfer 500.000,00 + Deposit 750.000,00 = 1.250.000,00
$inv7 = makeInvoice(701, 'INV-DEP-007', 'KB', 125000000, 125000000, 0, 0, 125000000, 125000000);
$db->invoices = array($inv7);
$db->alloc_splits[0]['nominal'] = '500000.00';
$db->alloc_details[0]['nominal_debit'] = '500000.00';

seedDepositEvidence($db);
$ci = new MockCi($db);
$engine = new Consulting_receipt($ci);
$refS->setValue($engine, new CustomService($ci));

$prevPost = array(
    'choose_inv' => array(701),
    'id_alokasi' => 100,
    'pph23_dipotong' => 'N',
    'ppn_dipotong' => 'N',
    'deposit_choice' => 'Y',
    'deposit_amount' => '750000.00'
);
$prevRes = $engine->preview($prevPost);
checkAssert($prevRes['customer_deposit'] === 120000000, 'Total deposit 12 x 100k = 1.2jt');

$savePost = array(
    'no_inv' => '1',
    'id_alokasi' => 100,
    'allocation_fingerprint' => $prevRes['allocation_fingerprint'],
    'pph23_dipotong' => 'N',
    'ppn_dipotong' => 'N',
    'deposit_choice' => 'Y',
    'deposit_amount' => '750000.00',
    'id_inv_1' => 701,
    'receipt_fingerprint_1' => $prevRes['rows'][0]['fingerprint'],
    'pph_actual_1' => '0.00',
    'biaya_admin_1' => '0.00',
    'sisa_piutang_1' => '0.00',
    'penerimaan_1' => '1250000.00',
    'dep_1' => '750000.00',
    'lebih_bayar_1' => '0.00'
);

$res7 = $engine->save($savePost);
checkAssert(($res7['status'] ?? 0) === 1, 'Save 750k from 12 vouchers success: ' . ($res7['msg'] ?? ''));

// Verify vouchers 1 to 7 fully consumed (LUNAS_TERPAKAI)
for ($i = 0; $i < 7; $i++) {
    $v = $db->customer_deposits[$i];
    checkAssert(Penerimaan_calculation::cents($v['sisa_saldo']) === 0, "Voucher {$v['id']} sisa is 0");
    checkAssert(Penerimaan_calculation::cents($v['nominal_terpakai']) === 10000000, "Voucher {$v['id']} terpakai 100k");
    checkAssert($v['status'] === 'LUNAS_TERPAKAI', "Voucher {$v['id']} status LUNAS_TERPAKAI");
}

// Verify voucher 8 partially consumed (50k used, 50k left, TERPAKAI_SEBAGIAN)
$v8 = $db->customer_deposits[7];
checkAssert(Penerimaan_calculation::cents($v8['sisa_saldo']) === 5000000, "Voucher 8 sisa is 50k");
checkAssert(Penerimaan_calculation::cents($v8['nominal_terpakai']) === 5000000, "Voucher 8 terpakai 50k");
checkAssert($v8['status'] === 'TERPAKAI_SEBAGIAN', "Voucher 8 status TERPAKAI_SEBAGIAN");

// Verify vouchers 9 to 12 untouched (AKTIF)
for ($i = 8; $i < 12; $i++) {
    $v = $db->customer_deposits[$i];
    checkAssert(Penerimaan_calculation::cents($v['sisa_saldo']) === 10000000, "Voucher {$v['id']} sisa is 100k");
    checkAssert(Penerimaan_calculation::cents($v['nominal_terpakai']) === 0, "Voucher {$v['id']} terpakai 0");
    checkAssert($v['status'] === 'AKTIF', "Voucher {$v['id']} status AKTIF");
}

// Exactly 8 mutasi records created
checkAssert(count(depositOuts($db)) === 8, '8 mutasi records created for vouchers 1..8');

// Verify Journal balanced
$totD = 0; $totK = 0;
foreach ($db->journals as $j) {
    $totD += Penerimaan_calculation::cents($j['debit']);
    $totK += Penerimaan_calculation::cents($j['kredit']);
}
checkAssert($totD === $totK && $totD === 125000000, 'Journal balanced at 1.25M debit and credit');

// Rollback receipt
$receiptId7 = $db->receipts[count($db->receipts)-1]['id'];
$rb7 = $engine->rollback($receiptId7);
checkAssert($rb7['status'] === 1, 'Rollback of 750k receipt successful: ' . ($rb7['msg'] ?? ''));

// Verify ALL 12 vouchers restored to 100k and AKTIF
for ($i = 0; $i < 12; $i++) {
    $v = $db->customer_deposits[$i];
    checkAssert(Penerimaan_calculation::cents($v['sisa_saldo']) === 10000000, "Restored voucher {$v['id']} sisa 100k");
    checkAssert(Penerimaan_calculation::cents($v['nominal_terpakai']) === 0, "Restored voucher {$v['id']} terpakai 0");
    checkAssert($v['status'] === 'AKTIF', "Restored voucher {$v['id']} status AKTIF");
}
checkAssert(count(depositOuts($db)) === 0, 'All mutasi records cleaned up on rollback');
echo "PASS: 12 Identical-Date Vouchers FIFO Allocation & Full Rollback Restoration\n\n";

echo "ALL DEPOSIT & FIFO INTEGRATION TESTS PASSED 100%!\n";

