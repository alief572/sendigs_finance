<?php
define('BASEPATH', __DIR__);
require dirname(__DIR__) . '/libraries/Invoice_consulting.php';
$checks = 0;
function expectConsulting($condition, $message) { global $checks; $checks++; if (!$condition) throw new RuntimeException($message); }
$cases = array(array('WAPU', '291000000', '266750000.00', '32010000.00', '291000000.00'), array('PKP', '19581600', '17949800.00', '2153976.00', '21735576.00'), array('KB', '60000000', '55000000.00', '6600000.00', '60000000.00'));
$accounts = array();
foreach (Invoice_consulting::roles() as $role => $label) $accounts[$role] = array('code' => 'TEST-' . $role, 'name' => $label);
if (($argv[1]??'')==='--json') {
    $parity=array();
    foreach(array('0.01','0.05','0.55','1.01','123.45','19581600','291000000','60000000','9999999999999.99')as $amount)
        foreach(array('PKP','WAPU','KB')as $type)$parity[]=array('amount'=>$amount,'type'=>$type,'totals'=>Invoice_consulting::totals($amount,$type));
    echo json_encode($parity);exit;
}
foreach ($cases as $case) {
    $t = Invoice_consulting::totals($case[1], $case[0]);
    expectConsulting(Invoice_calculation::decimal($t['other']) === $case[2], 'Excel DPP ' . $case[0]);
    expectConsulting(Invoice_calculation::decimal($t['ppn']) === $case[3], 'Excel PPN ' . $case[0]);
    expectConsulting(Invoice_calculation::decimal($t['receivable']) === $case[4], 'Excel piutang ' . $case[0]);
    foreach (Invoice_consulting::revenues() as $role => $label) {
        $rows = Invoice_consulting::journal($t, $accounts, $role, '260/STM/2026', 'SPK-1');
        expectConsulting(array_sum(array_column($rows, 'debit')) === array_sum(array_column($rows, 'credit')), 'Balanced ' . $role);
        expectConsulting(count($rows) === ($case[0] === 'WAPU' ? 4 : ($case[0] === 'PKP' ? 3 : 2)), 'Journal rows');
        expectConsulting(strpos(end($rows)['description'], '260/STM/2026 - SPK-1') !== false, 'Actual numbers in description');
    }
    $f = Invoice_consulting::fields($t);
    expectConsulting($f['pph_jurnal'] === '0.00' && $f['saldo_piutang'] === $case[4], 'No invoice PPh');
    expectConsulting(Invoice_calculation::cents($f['tagihan_ppn_jurnal']) === $t['gross'], 'Display gross retained');
}
foreach (array('0.01','1.01','123.45','9999999999999.99') as $amount) foreach (array('PKP','WAPU','KB') as $type) {
    $t = Invoice_consulting::totals($amount, $type);
    expectConsulting($t['gross'] === $t['sales'] + $t['ppn'], 'Cents safe');
}
foreach (array(array('0','PKP'),array('-1','PKP'),array('1.005','PKP'),array('1','UNKNOWN'),array('1',array())) as $bad) {
    try { Invoice_consulting::totals($bad[0], $bad[1]); expectConsulting(false,'Reject input'); } catch (InvalidArgumentException $e) { expectConsulting(true,'Rejected'); }
}
$r = Invoice_consulting::receipt(111000, 98000, 1000, 2000);
expectConsulting($r['reduction'] === 101000 && $r['remaining'] === 10000, 'Actual PPh reduces balance');
try { Invoice_consulting::receipt(100, 100, 0, 1); expectConsulting(false,'Overpayment'); } catch (InvalidArgumentException $e) { expectConsulting(true,'Overpayment rejected'); }
expectConsulting(!Invoice_consulting::is_new(array('consulting_version'=>3,'tipe_invoice'=>'1')), 'Vuca excluded');
expectConsulting(!Invoice_consulting::is_new(array('consulting_version'=>3,'non_kons'=>'1')), 'Non consultation excluded');
echo "PASS: $checks consulting calculation checks\n";
