<?php
require dirname(__DIR__).'/libraries/Payment_journal_validator.php';
$checks=0;
function verify($ok,$message){global $checks;$checks++;if(!$ok)throw new RuntimeException($message);}
function pair($amount='100.25',$type='Payment',$key='A,B') {
    $row=['no_transaksi'=>$key,'jenis_transaksi'=>$type,'coa'=>'1101-02-01','debit'=>$amount,'kredit'=>'0'];
    return [$row,array_merge($row,['debit'=>'0','kredit'=>$amount])];
}
function rejects($rows,$message) {
    try{Payment_journal_validator::validate($rows);verify(false,$message);}
    catch(Invalid_payment_journal $e){verify($e->getMessage()!=='',$message);}
}
foreach(['Payment','Transport','Transportasi','Kasbon','Expense','Refill Pettycash','Payment Hutang Petty Cash'] as $type) {
    Payment_journal_validator::validate(pair('100.25',$type));verify(true,'valid '.$type);
}
Payment_journal_validator::validate(array_merge(pair('100.25','Payment Hutang Petty Cash','APP'),pair('100.25','Payment Hutang Petty Cash','VOUCHER')));verify(true,'debt sides retain distinct native groups');
foreach(['company','recipient'] as $bearer) {
    $rows=pair('100.25');
    $rows[1]['kredit']=$bearer==='company'?'105.25':'100.25';
    $rows[]=array_merge($rows[0],['coa'=>'7201-01-04','debit'=>$bearer==='company'?'5.00':'0','kredit'=>'0']);
    $rows[]=array_merge($rows[0],['coa'=>'','debit'=>'0','kredit'=>'0']);
    Payment_journal_validator::validate($rows);verify(true,'admin and zero tax '.$bearer);
}
$rows=pair('105.00');$rows[0]['debit']='100';$rows[1]['kredit']='100';
$rows[]=array_merge($rows[0],['coa'=>'1106-01-06','debit'=>'10','kredit'=>'0']);
$rows[]=array_merge($rows[0],['coa'=>'2104-01-03','debit'=>'0','kredit'=>'10']);
Payment_journal_validator::validate($rows);verify(true,'positive tax lines');
$rows=pair('0.30');$rows[0]['debit']='0.10';$rows[]=array_merge($rows[0],['debit'=>'0.20']);
Payment_journal_validator::validate($rows);verify(true,'decimal arithmetic has no float residual');
$rows=pair('92233720368547758.07');Payment_journal_validator::validate($rows);verify(true,'maximum cents supported');
rejects([], 'empty journal');rejects(null,'non-array journal');rejects([null],'non-array row');rejects(pair('0'),'all zero group');
$rows=pair('8713202.00');array_pop($rows);rejects($rows,'legacy missing bank credit');
$rows=pair();$rows[1]['kredit']='100.24';rejects($rows,'one-cent imbalance');
$rows=pair();$rows[1]['no_transaksi']='OTHER';rejects($rows,'opposite groups cannot cancel');
$rows=pair();$rows[1]['jenis_transaksi']='Refill Pettycash';rejects($rows,'opposite types cannot cancel');
$rows=pair();$rows[0]['no_transaksi']="A\xff";$rows[1]['no_transaksi']="B\xff";rejects($rows,'opaque native keys must not collapse through JSON encoding failures');
foreach(['-1','1.234','1,000.00','1e5','NaN','',null,[],true] as $bad) {
    $rows=pair();$rows[0]['debit']=$bad;rejects($rows,'invalid nominal');
}
foreach(['no_transaksi','jenis_transaksi','coa'] as $field)foreach(['',[],null] as $bad){$rows=pair();$rows[0][$field]=$bad;rejects($rows,'invalid identity/active COA');}
rejects(pair('92233720368547758.08'),'single amount overflow');
rejects(pair(true),'boolean is not a nominal');
rejects(array_merge(pair('92233720368547758.07'),pair('0.01')),'sum overflow');
echo "PASS payment journal validator: {$checks} assertions\n";
