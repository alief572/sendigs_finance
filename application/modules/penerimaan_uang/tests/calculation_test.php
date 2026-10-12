<?php
// No application bootstrap and no database writes.
define('BASEPATH', __DIR__);
require dirname(__DIR__) . '/libraries/Penerimaan_calculation.php';
function check($condition, $message) { if (!$condition) throw new RuntimeException($message); }
$C = 'Penerimaan_calculation';
check($C::vuca_totals('15000000.00', '75000.00') === array('gross'=>'15000000.00','net'=>'14925000.00','pph'=>'75000.00'), 'Vuca gross/net');
check($C::vuca_totals('15000000.50', '75000.25')['net'] === '14925000.25', 'Vuca cents');
$vuca = array('tipe_invoice'=>1,'tagihan_ppn_jurnal'=>'0.00','total_nominal_jurnal'=>'15000000.00','ppn_jurnal'=>'0.00','saldo_piutang'=>'14925000.00');
check($C::invoice_gross($vuca) === 1500000000 && $vuca['saldo_piutang'] === '14925000.00', 'Legacy Vuca gross without balance mutation');
$vuca['tipe_invoice']=0;
check($C::invoice_gross($vuca) === 0, 'Fallback limited to Vuca');
$settled=$C::calculate(1492500000,7500000,'N',1500000000,0);
check($settled === array('adjustment'=>7500000,'reduction'=>1492500000,'remaining'=>0), '036 Vuca non deducted');
check($C::calculate(1492500000,7500000,'Y',1492500000,0)['remaining'] === 0, '036 Vuca deducted');
try { $C::calculate(1492500000,7500000,'Y',1500000000,0); throw new RuntimeException('Vuca overpayment accepted'); } catch (InvalidArgumentException $expected) {}
check($C::cents('2.574.990,50', true) === 257499050, 'Indonesian decimals');
check($C::decimal(-101) === '-1.01', 'Decimal output');
$first = $C::calculate(2551581000, 46818000, 'N', 2340900000, 0);
check($first['remaining'] === 257499000 && $first['adjustment'] === 46818000, 'First installment');
$last = $C::calculate($first['remaining'], 0, 'N', 257499000, 0);
check($last['remaining'] === 0 && $last['reduction'] === 257499000 && $last['adjustment'] === 0, 'MINARTA settlement');
$tiny = $C::calculate(100000, 20000, 'N', 10000, 0);
check($tiny['adjustment'] === 10000 && $tiny['reduction'] === 0, 'Adjustment limited to receipt');
check($C::calculate(10050, 500, 'Y', 10000, 50)['remaining'] === 0, 'Deducted with admin and cents');
check($C::calculate(10050, 0, 'N', 10050, 0)['remaining'] === 0, 'No PPh');
foreach (array('1,234.56', 'NaN', '1,234', array(), '99999999999999') as $bad) {
    try { $C::cents($bad, true); throw new RuntimeException('Accepted malformed amount'); } catch (InvalidArgumentException $expected) {}
}
foreach (array(array(100, 0, 'N', 101, 0), array(100, 0, 'Y', -1, 0), array(100, 0, 'X', 1, 0)) as $bad) {
    try { call_user_func_array(array($C, 'calculate'), $bad); throw new RuntimeException('Accepted invalid calculation'); } catch (InvalidArgumentException $expected) {}
}
echo "PASS: money parsing, MINARTA, partial PPh, admin, decimals and invalid input\n";
