<?php
require __DIR__.'/Query_test.php';
$baseChecks=$checks;
$db->query('DELETE FROM tr_jurnal');
$db->query('DELETE FROM payment_approve');
foreach ([
    ['BK--0926-0015','BK--0826-0018','DPM-2026-00020','Cash'],
    ['BK--1026-0055','BK--0926-0015','PHP-2026-0012','petty_cash_hutang']
] as $p) $db->insert('payment_approve',array_combine(['id','id_payment','no_doc','tipe'],$p));
$stm=journal('Payment Hutang Petty Cash','0','BK--0926-0015','7','2647250.00','2647250.00');
$sustain=journal('Payment Hutang Petty Cash','0','BK--1026-0055','1','2647250.00','2647250.00');
$model=new Jurnal_all_model();$model->db=$db;
$rows=$model->groups(['tab'=>'PAY','year'=>2026]);
check(count($rows)===2,'both native petty debt groups remain visible');
foreach ($rows as $row) {
    check($row['display_no_transaksi']==='BK--0926-0015','collision must not add the unrelated Cash voucher');
    check($row['no_referensi']==='PHP-2026-0012'&&$row['category']==='Payment Hutang Petty Cash','reference and category use only the selected debt payment');
    check($row['debit']==='2647250.00'&&$row['kredit']==='2647250.00','collision cannot change journal totals');
}
foreach ([$stm,$sustain] as $anchor) {
    $detail=$model->detail($anchor,false);
    check(count($detail['rows'])===2&&$detail['is_balance'],'native detail rows and balance remain intact');
    check($detail['header']['display_no_transaksi']==='BK--0926-0015'&&$detail['header']['no_referensi']==='PHP-2026-0012','detail uses the resolved payment');
}
foreach (['BK--0826-0018','DPM-2026-00020'] as $q) check(count($model->groups(['tab'=>'PAY','year'=>2026,'q'=>$q]))===0,'unrelated payment metadata is not searchable: '.$q);
check(count($model->groups(['tab'=>'PAY','year'=>2026,'q'=>'PHP-2026-0012']))===2,'resolved reference finds both sides');
check(count($model->groups(['tab'=>'PAY','year'=>2026,'category'=>'Cash']))===0,'unrelated category cannot select a debt group');
check($model->options(['tab'=>'PAY','year'=>2026])['categories']===['Payment Hutang Petty Cash'],'category options use selected payments');
$export=$model->export_rows(['tab'=>'PAY','year'=>2026]);
check(count($export)===4,'export preserves four native accounting lines');
foreach($export as $line) check($line['header']['display_no_transaksi']==='BK--0926-0015'&&$line['header']['no_referensi']==='PHP-2026-0012','Excel metadata matches listing');
$db->where_in('no_transaksi',['BK--0926-0015','BK--1026-0055'])->update('tr_jurnal',['sts'=>'1']);
$model=new Jurnal_all_model();$model->db=$db;
check(count($model->groups(['tab'=>'PAY','year'=>2026],true))===2,'posted report retains both groups');
check($model->detail($stm,true)['header']['no_referensi']==='PHP-2026-0012','posted detail shares resolver');
check(count($model->export_rows(['tab'=>'PAY','year'=>2026],true))===4,'posted export retains native lines');

foreach ([
    ['SHARED','V-ID','DOC-ID','Cash'],
    ['APP-V','SHARED','DOC-V','Cash'],
    ['APP-D','V-DOC','SHARED','Cash'],
    ['APP-R','SHARED','RPC-SHARED','refill_pettycash'],
    ['A1','V-A','DOC-A','Cash'],
    ['A2','V-A','DOC-B','periodik'],
    ['A3','V-B','DOC-C','kasbon'],
    ['EMPTY',null,'DOC-EMPTY','expense'],
    ['DOC-APP','DOC-VOUCHER','DOCUMENT-KEY','Cash'],
    ['DEBT-ID','DEBT-VOUCHER','PHP-ID','petty_cash_hutang'],
    ['DEBT-OTHER','DEBT-ID','PHP-OTHER','petty_cash_hutang'],
    ['DOC-R','REFILL-VOUCHER','REFILL-DOCUMENT','refill_pettycash'],
    ['WRONG','WRONG-VOUCHER','RPC-UNRELATED','Cash']
] as $p) $db->insert('payment_approve',array_combine(['id','id_payment','no_doc','tipe'],$p));
$normal=journal('Payment','0','SHARED');
$refill=journal('Refill Pettycash','0','SHARED');
$voucher=journal('Payment','0','V-A');
$multi=journal('Payment','0',' A1, A2, A1, A3 ');
$empty=journal('Expense','0','EMPTY');
$missing=journal('Payment','0','MISSING');
$document=journal('Payment','0','DOCUMENT-KEY');
$debtPriority=journal('Payment Hutang Petty Cash','0','DEBT-ID');
$refillDocument=journal('Refill Pettycash','0','REFILL-DOCUMENT');
$wrongType=journal('Refill Pettycash','0','WRONG');
$model=new Jurnal_all_model();$model->db=$db;
check($model->detail($normal,false)['header']['display_no_transaksi']==='-'&&!$model->detail($normal,false)['can_post'],'conflicting approval/voucher identity blocks Payment');
check($model->detail($normal,false)['header']['no_referensi']==='DOC-ID','lower tiers cannot leak references');
check($model->detail($refill,false)['header']['no_referensi']==='RPC-SHARED','same cached key resolves separately for refill type');
check($model->detail($normal,false)['header']['no_referensi']==='DOC-ID','typed resolution cannot contaminate normal cache');
$fresh=new Jurnal_all_model();$fresh->db=$db;
check($fresh->detail($refill,false)['header']['category']==='Refill Petty Cash'&&$fresh->detail($normal,false)['header']['no_referensi']==='DOC-ID','cache remains correct in reverse call order');
check($model->detail($voucher,false)['header']['no_referensi']==='DOC-A, DOC-B','voucher fallback preserves multiple approvals in a batch');
$header=$model->detail($multi,false)['header'];
check($header['display_no_transaksi']==='-'&&!$model->detail($multi,false)['can_post']&&$header['no_referensi']==='DOC-A, DOC-B, DOC-C','multiple vouchers remain visible but cannot be posted as one Payment');
check($header['category']==='Cash, Periodik, Kasbon','generic payment keeps mixed supported categories');
check($model->detail($empty,false)['header']['display_no_transaksi']==='EMPTY','Expense legacy retains native identity when voucher is empty');
check($model->detail($missing,false)['header']['display_no_transaksi']==='-'&&!$model->detail($missing,false)['header']['payment_available'],'unresolved Payment has no actual voucher');
check($model->detail($document,false)['header']['display_no_transaksi']==='-'&&!$model->detail($document,false)['can_post'],'document key alone cannot establish an actual payment voucher');
check($model->detail($debtPriority,false)['header']['no_referensi']==='PHP-ID','compatible approval wins over a compatible voucher tier');
check($model->detail($refillDocument,false)['header']['display_no_transaksi']==='REFILL-VOUCHER','typed document fallback resolves refill');
try {$model->detail($wrongType,false);check(false,'unrelated RPC-like reference cannot make a Cash payment eligible as refill');}
catch(InvalidArgumentException $e){check(true,'unmatched refill keeps source eligibility guard');}
check(count($model->groups(['tab'=>'PAY','year'=>2026,'q'=>'RPC-UNRELATED']))===0,'rejected type cannot contribute reference search');
check(count($model->groups(['tab'=>'PAY','year'=>2026,'q'=>'APP-V']))===0,'discarded voucher tier cannot contribute approval search');
check(count($model->groups(['tab'=>'PAY','year'=>2026,'q'=>'DOC-APP']))===1,'selected approval is searchable even when native key is a document');
$paymentQueries=function()use($db){return count(array_filter($db->queries,function($sql){return strpos($sql,'FROM "payment_approve"')!==false;}));};
$queryCount=$paymentQueries();
$model->detail($normal,false);$model->detail($refill,false);
check($queryCount>0&&$paymentQueries()===$queryCount,'cached keys do not repeat payment queries across journal types');
$list=$model->listing(['tab'=>'PAY','year'=>2026,'length'=>100,'order'=>[['column'=>1,'dir'=>'asc']]]);
$display=array_column($list['data'],'display_no_transaksi');$sorted=$display;usort($sorted,'strnatcasecmp');
check($display===$sorted,'sorting follows resolved display vouchers');
$db->query('DELETE FROM tr_jurnal');$db->query('DELETE FROM payment_approve');
for($i=0;$i<205;$i++) {
    $db->insert('payment_approve',['id'=>'BULK-'.$i,'id_payment'=>'VOUCHER-'.$i,'no_doc'=>'DOC-'.$i,'tipe'=>'Cash']);
    journal('Payment','0','BULK-'.$i);
}
$model=new Jurnal_all_model();$model->db=$db;
$queryCount=$paymentQueries();
$siblingQueries=function()use($db){return count(array_filter($db->queries,function($sql){return strpos($sql,'SELECT "no_transaksi", "jenis_transaksi"')===0;}));};
$siblingCount=$siblingQueries();
$bulk=$model->groups(['tab'=>'PAY','year'=>2026]);
check(count($bulk)===205&&$paymentQueries()-$queryCount===2,'205 journal keys use two payment lookup chunks, not per-row queries');
check($siblingQueries()-$siblingCount===2,'205 Payment groups use two sibling lookup batches');
$siblingCount=$siblingQueries();$model->detail($bulk[0]['id'],false);
check($siblingQueries()===$siblingCount,'known missing sibling is cached for detail as well');
check(count(array_unique(array_column($bulk,'display_no_transaksi')))===205,'chunk boundaries preserve distinct resolved vouchers');
echo 'PASS payment mapping: '.($checks-$baseChecks)." assertions\n";
