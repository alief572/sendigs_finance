<?php
require __DIR__ . '/consulting_fixture.php';

$db = createFixtures();
$db->invoices = array(makeInvoice(801, 'INV-801', 'KB', 100000, 91667, 0, 0, 100000, 100000));
$db->customer_deposits = array(
    array('id'=>1, 'id_customer'=>'CUST-001', 'id_company'=>'1', 'tanggal'=>'2026-10-02', 'tanggal_aktif'=>'2026-10-02', 'status'=>'AKTIF', 'sisa_saldo'=>'100.00', 'nominal_awal'=>'100.00', 'nominal_terpakai'=>'0.00'),
    array('id'=>2, 'id_customer'=>'CUST-001', 'id_company'=>'2', 'tanggal'=>'2026-10-01', 'tanggal_aktif'=>'2026-10-01', 'status'=>'AKTIF', 'sisa_saldo'=>'300.00', 'nominal_awal'=>'300.00', 'nominal_terpakai'=>'0.00')
);
seedDepositEvidence($db);
$ci = new MockCi($db);
$engine = new Consulting_receipt($ci);
$property = new ReflectionProperty('Consulting_receipt', 's');
$property->setAccessible(true);
$property->setValue($engine, new CustomService($ci));
$post = array('choose_inv'=>array(801), 'id_alokasi'=>100, 'pph23_dipotong'=>'N', 'deposit_choice'=>'N');
$preview = $engine->preview($post);
checkAssert($preview['customer_deposit'] === 10000, 'Only deposits belonging to invoice company are available');
$other = makeInvoice(802, 'INV-802', 'KB', 100000, 91667, 0, 0, 100000, 100000);
$snapshot = json_decode($other['consulting_snapshot'], true);
$snapshot['owner']['id'] = '2';
$other['consulting_snapshot'] = json_encode($snapshot);
$db->invoices[] = $other;
$post['choose_inv'][] = 802;
$rejected = false;
try { $engine->preview($post); } catch (RuntimeException $e) { $rejected = strpos($e->getMessage(), 'company') !== false; }
checkAssert($rejected, 'Mixed-company batches must be rejected');

$kw = makeInvoice(803, 'KD-803', 'DEP', 100000, 0, 0, 0, 100000, 100000);
$db->invoices = array($db->invoices[0], $kw);
$post['choose_inv'] = array(801, 803);
$rejected = false;
try { $engine->preview($post); } catch (RuntimeException $e) { $rejected = strpos($e->getMessage(), 'kwitansi') !== false; }
checkAssert($rejected, 'Mixed invoice and kwitansi batches must be rejected');

$db->invoices = array($db->invoices[0]);
$db->alloc_splits[0]['nominal'] = '900.00';
$db->alloc_details[0]['nominal_debit'] = '900.00';
foreach ($db->customer_deposits as &$deposit) {
    $deposit['nominal_awal'] = $deposit['sisa_saldo'];
    $deposit['nominal_terpakai'] = '0.00';
}
unset($deposit);
$post = array('choose_inv'=>array(801), 'id_alokasi'=>100, 'pph23_dipotong'=>'N', 'deposit_choice'=>'Y', 'deposit_amount'=>'100.00');
$preview = $engine->preview($post);
$save = array('no_inv'=>'1', 'id_inv_1'=>801, 'id_alokasi'=>100,
    'allocation_fingerprint'=>$preview['allocation_fingerprint'], 'receipt_fingerprint_1'=>$preview['rows'][0]['fingerprint'],
    'pph23_dipotong'=>'N', 'deposit_choice'=>'Y', 'deposit_amount'=>'100.00', 'pph_actual_1'=>'0.00',
    'penerimaan_1'=>'1000.00', 'biaya_admin_1'=>'0.00', 'sisa_piutang_1'=>'0.00', 'dep_1'=>'100.00', 'lebih_bayar_1'=>'0.00');
$result = $engine->save($save);
checkAssert($result['status'] === 1, 'Valid company deposit can be consumed');
checkAssert($db->customer_deposits[0]['sisa_saldo'] === '0.00', 'Matching company deposit consumed');
checkAssert($db->customer_deposits[1]['sisa_saldo'] === '300.00', 'Earlier deposit of another company remains untouched');
echo "Deposit company isolation tests passed\n";
