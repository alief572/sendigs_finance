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

// === NEW CONSULTING TO-BE TAX & CONTROL TESTS ===
// 1. PKP test case
$pkp = $C::tax_calc(200000000, 'PKP');
check($pkp['sales'] === 200000000, 'PKP sales');
check($pkp['dpp_lain'] === 183333333, 'PKP dpp lain 11/12');
check($pkp['ppn'] === 22000000, 'PKP ppn 12%');
check($pkp['pph_default'] === 3666667, 'PKP pph 2%');
check($pkp['tagihan'] === 222000000, 'PKP tagihan sales+ppn');
check($pkp['piutang'] === 222000000, 'PKP piutang');

// 2. WAPU test case
$wapu = $C::tax_calc(300000000, 'WAPU');
check($wapu['sales'] === 300000000, 'WAPU sales');
check($wapu['dpp_lain'] === 275000000, 'WAPU dpp lain');
check($wapu['ppn'] === 0, 'WAPU ppn 0');
check($wapu['pph_default'] === 5500000, 'WAPU pph default');
check($wapu['tagihan'] === 300000000, 'WAPU tagihan sales only');
check($wapu['piutang'] === 300000000, 'WAPU piutang');

// 3. KB (Kawasan Berikat) test case
$kb = $C::tax_calc(6000000000, 'KB');
check($kb['sales'] === 6000000000, 'KB sales');
check($kb['dpp_lain'] === 5500000000, 'KB dpp lain');
check($kb['ppn'] === 0, 'KB ppn 0');
check($kb['pph_default'] === 110000000, 'KB pph default');
check($kb['tagihan'] === 6000000000, 'KB tagihan sales only');
check($kb['piutang'] === 6000000000, 'KB piutang');

// 4. Kontrol zero-balance calculation
// Formula: (Piutang - PPh - Admin - Sisa) - Uang Masuk = 0
$k1 = $C::kontrol(222000000, 3666667, 250000, 0, 218083333);
check($k1 === 0, 'Kontrol zero for PKP full payment with admin');

$kKurang = $C::kontrol(222000000, 3666667, 0, 100000000, 118333333);
check($kKurang === 0, 'Kontrol zero for PKP partial payment with sisa piutang');

// 5. Batch validation
$validBatch = array(
    array('no_invoice'=>'INV-001', 'piutang'=>222000000, 'pph'=>3666667, 'admin'=>250000, 'sisa'=>0)
);
$resValid = $C::validate_batch($validBatch, 218083333, 'Y');
check($resValid['valid'] === true && $resValid['kontrol'] === 0, 'Valid batch passes');

$invalidKontrol = $C::validate_batch($validBatch, 210000000, 'Y');
check($invalidKontrol['valid'] === false && count($invalidKontrol['errors']) > 0, 'Unbalanced batch fails kontrol');

$overAdmin = array(
    array('no_invoice'=>'INV-001', 'piutang'=>222000000, 'pph'=>3666667, 'admin'=>1500000, 'sisa'=>0) // Admin Rp 15.000 > 10.000
);
$resAdmin = $C::validate_batch($overAdmin, 216833333, 'Y');
check($resAdmin['valid'] === false && strpos($resAdmin['errors'][0], '10.000') !== false, 'Admin > 10.000 rejected');

$negVal = array(
    array('no_invoice'=>'INV-001', 'piutang'=>222000000, 'pph'=>-1, 'admin'=>0, 'sisa'=>0)
);
$resNeg = $C::validate_batch($negVal, 222000001, 'Y');
check($resNeg['valid'] === false && strpos($resNeg['errors'][0], 'negatif') !== false, 'Negative value rejected');

echo "PASS: money parsing, MINARTA, partial PPh, admin, decimals, To-Be tax calculation, kontrol zero balance, and batch validation\n";
