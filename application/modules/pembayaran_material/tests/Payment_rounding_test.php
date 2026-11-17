<?php
define('MATERIAL_FIXTURE_ONLY', true);
require __DIR__.'/Batch1_test.php';

$model = new MaterialModel(new MaterialDb(), new MaterialInput());
foreach ([
    ['23', '1000028', '0', 2000100], ['21', '1000028', '0', 2500100],
    ['23', '1000000', '0', 2000000], ['21', '1000000', '0', 2500000],
    ['23', '1000000.50', '0', 2000100], ['23', '1000049.50', '0', 2000100],
    ['21', '1000000.40', '0', 2500100], ['21', '1000039.60', '0', 2500100],
    ['23', '999999.99', '0.01', 2000000], ['21', '999999.99', '0.01', 2500000],
    ['23', '1000028', '110003.08', 2000100], ['21', '1000028', '110003.08', 2500100],
    ['21', '1000000', '0.01', 2500000], ['21', '0', '110003.08', 0],
    ['23', '1000000', '0.01', 2000000], ['23', '0', '110003.08', 0],
    ['23', '0', '0', 0], ['21', '0', '0', 0], ['', '1000028', '0', 0],
    ['PPH 23', '1000028.0000', '0', 2000100], ['1', '1000028', '0', 2000100]
] as $case) {
    check(Pembayaran_material_model::payment_pph($case[0], $case[1], $case[2]) === $case[3], 'PPh ceiling '.json_encode($case));
}
check(Pembayaran_material_model::payment_pph('2', '100', '0', '1.23') === 123, 'Legacy PPh 22 unchanged');
check(Pembayaran_material_model::payment_cents('14,000.5600') === 1400056, 'Grouping and DB trailing zeros');
check(Pembayaran_material_model::payment_decimal(2000100) === '20001.00', 'Exact decimal output');
foreach (['-1', '1,00', 'NaN', '1.001', [], true, '999999999999999999999'] as $invalid) {
    try { $model->payment_pph('23', $invalid, '0'); throw new Exception('Accepted invalid amount'); }
    catch (DomainException $expected) {}
}
foreach (['22', 'bad', [], true] as $invalid) {
    try { $model->payment_pph($invalid, '100', '0'); throw new Exception('Accepted invalid type'); }
    catch (DomainException $expected) {}
}
$payment = (object) ['id' => 'PAY-1', 'tipe' => 'expense', 'no_doc' => 'DOC-1', 'jumlah' => '1000028.0000',
    'tipe_pph' => 'PPH 23', 'total_ppn' => 0, 'total_pph' => '999.44'];
$result = $model->payment_totals([$payment], [], '0', 'company');
check($result['total'] === 98002700 && $result['bank'] === 98002700, 'Recompute approved PPh from type');
check($result['details']['PAY-1']['nilai_pph'] === '20001.00', 'Normalized PPh');
foreach (['company', 'recipient'] as $bearer) {
    $result = $model->payment_totals([$payment], [], '2500.25', $bearer);
    check($result['bank'] === ($bearer === 'company' ? 98252725 : 98002700), 'Admin unchanged '.$bearer);
}
foreach (['-1', '1,00', 'NaN', '1.001', [], true, '999999999999999999999'] as $invalid) {
    try { $model->payment_totals([$payment], [], $invalid, 'company'); throw new Exception('Accepted invalid admin'); }
    catch (DomainException $expected) {}
}
try { $model->payment_pph('23', '92233720368547758.07', '0.01'); throw new Exception('Accepted PPh base overflow'); }
catch (DomainException $expected) {}
$second = clone $payment;
$second->id = 'PAY-2';
$result = $model->payment_totals([$payment, $second], [], '0', 'company');
check($result['bank'] === 196005400, 'PPh rounds per item, not aggregate');
$payment->jumlah = '1000028.56';
$result = $model->payment_totals([$payment], [], '0', 'company');
check($result['bank'] === 98002756, 'Fractional source remains fractional');
foreach ([['OTHER' => []], ['PAY-1' => ['nilai_pph' => '20000.57', 'nilai_ppn' => '0', 'tipe_pph' => '23']],
    ['PAY-1' => ['nilai_pph' => '20001', 'nilai_ppn' => '0']]] as $invalid) {
    try { $model->payment_totals([$payment], $invalid, '0', 'company'); throw new Exception('Accepted invalid detail'); }
    catch (DomainException $expected) {}
}
foreach (['petty_cash_hutang', 'refill_pettycash'] as $type) {
    $special = clone $payment;
    $special->tipe = $type;
    check($model->payment_totals([$special], [], '0', 'company') === null, 'Special cash unchanged');
}
$special->tipe = 'transportasi';
$special->no_doc = 'RPC-1';
check($model->payment_totals([$special], [], '0', 'company') === null, 'Refill document unchanged');
echo "PASS PPh rounding calculation\n";
