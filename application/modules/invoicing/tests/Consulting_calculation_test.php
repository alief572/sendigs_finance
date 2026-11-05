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
    foreach(array('0.01','0.05','0.10','0.55','1.00','1.01','100.00','123.45','999.99','1000.55','19581600','60000000','291000000','10000000.00','9999999999999.99')as $amount)
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
try { Invoice_consulting::journal(Invoice_consulting::totals('1000','PKP'), array(), 'sales_consulting', 'INV-01', 'SPK-01'); expectConsulting(false,'Incomplete accounts rejected'); } catch (RuntimeException $e) { expectConsulting(true,'Incomplete accounts rejected'); }
try { Invoice_consulting::journal(Invoice_consulting::totals('1000','PKP'), $accounts, 'invalid_role', 'INV-01', 'SPK-01'); expectConsulting(false,'Invalid sales role rejected'); } catch (InvalidArgumentException $e) { expectConsulting(true,'Invalid sales role rejected'); }
$r = Invoice_consulting::receipt(111000, 98000, 1000, 2000);
expectConsulting($r['reduction'] === 101000 && $r['remaining'] === 10000, 'Actual PPh reduces balance');
$rExact = Invoice_consulting::receipt(10000, 10000, 0, 0);
expectConsulting($rExact['reduction'] === 10000 && $rExact['remaining'] === 0 && $rExact['adjustment'] === 0, 'Exact payment');
$rPphOnly = Invoice_consulting::receipt(10000, 0, 0, 10000);
expectConsulting($rPphOnly['reduction'] === 10000 && $rPphOnly['remaining'] === 0 && $rPphOnly['adjustment'] === 0, 'PPh only reduction');
foreach (array(array(100, 100, 0, 1), array(100, -1, 0, 0), array(100, 0, -1, 0), array(100, 0, 0, -1), array(-100, 50, 0, 0), array(100, 0, 0, 0), array(100, 'abc', 0, 0), array(100, null, 0, 0), array('invalid', 50, 0, 0)) as $badReceipt) {
    try { Invoice_consulting::receipt($badReceipt[0], $badReceipt[1], $badReceipt[2], $badReceipt[3]); expectConsulting(false, 'Bad receipt input accepted'); } catch (InvalidArgumentException $e) { expectConsulting(true, 'Bad receipt input rejected'); }
}
expectConsulting(Invoice_consulting::is_new(array('consulting_version'=>3,'tipe_invoice'=>'0','non_kons'=>'0')), 'V3 regular included');
expectConsulting(!Invoice_consulting::is_new(array('consulting_version'=>3,'tipe_invoice'=>'1')), 'Vuca excluded');
expectConsulting(!Invoice_consulting::is_new(array('consulting_version'=>3,'non_kons'=>'1')), 'Non consultation excluded');
expectConsulting(!Invoice_consulting::is_new(array('consulting_version'=>2)), 'V2 excluded');
expectConsulting(!Invoice_consulting::is_new(array('consulting_version'=>0)), 'Legacy excluded');
expectConsulting(!Invoice_consulting::is_new(array()), 'Empty invoice excluded');
$validSnapshotData = array('owner'=>array('id'=>7,'nm_company'=>'STM'),'accounts'=>array('receivable'=>array('code'=>'1102-01-01','name'=>'Piutang'),'bank'=>array('code'=>'1101-01-01','name'=>'Kas/Bank'),'admin'=>array('code'=>'5101-01-01','name'=>'Biaya Admin'),'pph'=>array('code'=>'1108-01-01','name'=>'Uang Muka PPh 23')));
$validInvoiceRecord = array('consulting_version'=>3,'tipe_invoice'=>'0','non_kons'=>'0','consulting_snapshot'=>json_encode($validSnapshotData));
$parsedSnapshot = Invoice_consulting::snapshot($validInvoiceRecord);
expectConsulting($parsedSnapshot['owner']['id'] === 7 && $parsedSnapshot['accounts']['receivable']['code'] === '1102-01-01', 'Valid snapshot parsed');
foreach (array(array(), array('consulting_version'=>2,'consulting_snapshot'=>json_encode($validSnapshotData)), array('consulting_version'=>3,'consulting_snapshot'=>'invalid-json'), array('consulting_version'=>3,'consulting_snapshot'=>json_encode(array('owner'=>array())))) as $badInv) {
    try { Invoice_consulting::snapshot($badInv); expectConsulting(false, 'Bad snapshot accepted'); } catch (RuntimeException $e) { expectConsulting(true, 'Bad snapshot rejected'); }
}
$receiptSnapshot = array('owner'=>array('id'=>7,'nm_company'=>'STM'),'cash'=>9000,'admin'=>100,'pph'=>900,'adjustment'=>0,'reduction'=>10000,'date'=>'2026-10-18','invoice_no'=>'001/STM/2026','accounts'=>$validSnapshotData['accounts']);
$receiptJournalLines = Invoice_consulting::receipt_journal($receiptSnapshot);
expectConsulting(array_sum(array_column($receiptJournalLines, 'debit')) === array_sum(array_column($receiptJournalLines, 'credit')), 'Receipt journal balanced');
expectConsulting(count($receiptJournalLines) === 4, 'Receipt journal row count');
$unbalancedReceiptSnapshot = $receiptSnapshot;
$unbalancedReceiptSnapshot['reduction'] = 5000;
try { Invoice_consulting::receipt_journal($unbalancedReceiptSnapshot); expectConsulting(false, 'Unbalanced receipt snapshot accepted'); } catch (RuntimeException $e) { expectConsulting(true, 'Unbalanced receipt snapshot rejected'); }
$missingOwnerCompanySnapshot = $receiptSnapshot;
unset($missingOwnerCompanySnapshot['owner']['nm_company']);
try { Invoice_consulting::receipt_journal($missingOwnerCompanySnapshot); expectConsulting(false, 'Missing nm_company accepted'); } catch (RuntimeException $e) { expectConsulting(true, 'Missing nm_company rejected'); }
$missingAccountCodeSnapshot = $receiptSnapshot;
$missingAccountCodeSnapshot['accounts']['bank'] = array('name' => 'Kas/Bank');
try { Invoice_consulting::receipt_journal($missingAccountCodeSnapshot); expectConsulting(false, 'Missing account code accepted'); } catch (RuntimeException $e) { expectConsulting(true, 'Missing account code rejected'); }
$missingAccountNameSnapshot = $receiptSnapshot;
$missingAccountNameSnapshot['accounts']['bank'] = array('code' => '1101-01-01');
try { Invoice_consulting::receipt_journal($missingAccountNameSnapshot); expectConsulting(false, 'Missing account name accepted'); } catch (RuntimeException $e) { expectConsulting(true, 'Missing account name rejected'); }
echo "PASS: $checks consulting calculation checks\n";
