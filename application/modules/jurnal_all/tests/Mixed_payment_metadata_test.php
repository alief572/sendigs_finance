<?php
require __DIR__.'/Query_test.php';
$baseChecks=$checks;
$db->query('DELETE FROM tr_jurnal');$db->query('DELETE FROM payment_approve');
$key='C1,P1,P2,R1,C2,C3';
foreach([['C1','DPM-81','cash'],['P1','PERIODIK-181','periodik'],['P2','PERIODIK-177','periodik'],['R1','RPC-29','refill_pettycash'],['C2','DPM-83','cash'],['C3','DPM-82','cash']] as $p) {
    $db->insert('payment_approve',['id'=>$p[0],'id_payment'=>'BK--1026-0002','no_doc'=>$p[1],'tipe'=>$p[2]]);
}
$payment=journal('Payment','0',$key,'7','8713202.00','0.00');
$refill=journal('Refill Pettycash','0',$key,'7','207000.00','207000.00');
$model=new Jurnal_all_model();$model->db=$db;
$header=$model->detail($payment,false)['header'];
check($header['category']==='Cash, Periodik','Payment must exclude category owned by refill sibling');
check($header['no_referensi']==='DPM-81, PERIODIK-181, PERIODIK-177, DPM-83, DPM-82','Payment metadata contains only its five source documents');
check($header['keterangan']==='Pembayaran Cash, Periodik - BK--1026-0002','Payment header is deterministic instead of MAX line description');
check($header['debit']==='8713202.00'&&$header['kredit']==='0.00'&&!$model->detail($payment,false)['is_balance'],'metadata does not conceal or repair the native imbalance');
$refillHeader=$model->detail($refill,false)['header'];
check($refillHeader['no_referensi']==='RPC-29'&&$refillHeader['category']==='Refill Petty Cash','refill retains its strict source');
check(count($model->groups(['tab'=>'PAY','year'=>2026,'q'=>'RPC-29']))===1,'refill reference does not find sibling Payment');
check(count($model->groups(['tab'=>'PAY','year'=>2026,'q'=>'R1']))===2,'native key remains searchable even if it contains the refill approval');
check(count($model->groups(['tab'=>'PAY','year'=>2026,'category'=>'Refill Petty Cash']))===1,'category filter uses partitioned metadata');
foreach($model->export_rows(['tab'=>'PAY','year'=>2026]) as $line) {
    check($line['line']['keterangan']==='Detail aman {REF:REF-1}','COA export text is unchanged');
    if($line['header']['jenis_transaksi']==='Payment')check($line['header']['keterangan']==='Pembayaran Cash, Periodik - BK--1026-0002','Excel header matches detail');
}
$db->where('jenis_transaksi','Refill Pettycash')->update('tr_jurnal',['sts'=>'1']);
$model=new Jurnal_all_model();$model->db=$db;
check($model->detail($payment,false)['header']['category']==='Cash, Periodik','posted sibling still owns its metadata');
check($model->detail($refill,true)['header']['no_referensi']==='RPC-29','posted report keeps refill metadata');
$db->where('jenis_transaksi','Payment')->update('tr_jurnal',['sts'=>'1']);
check($model->detail($payment,true)['header']['keterangan']==='Pembayaran Cash, Periodik - BK--1026-0002','posted Payment uses deterministic header');

$db->where('jenis_transaksi','Refill Pettycash')->delete('tr_jurnal');
$model=new Jurnal_all_model();$model->db=$db;
check(strpos($model->detail($payment,true)['header']['no_referensi'],'RPC-29')!==false,'without sibling legacy references must remain available');
$debt=journal('Payment Hutang Petty Cash','0',$key);
$db->insert('payment_approve',['id'=>'R1','id_payment'=>'BK--1026-0002','no_doc'=>'PHP-29','tipe'=>'petty_cash_hutang']);
$db->where(['id'=>'R1','tipe'=>'refill_pettycash'])->delete('payment_approve');
$model=new Jurnal_all_model();$model->db=$db;
check($model->detail($payment,true)['header']['category']==='Cash, Periodik'&&$model->detail($debt,false)['header']['no_referensi']==='PHP-29','debt sibling also owns only its special payment type');
$fallback=journal('Payment','0','UNRESOLVED');
check($model->detail($fallback,false)['header']['keterangan']==='Pembayaran Payment - UNRESOLVED','missing voucher uses native key in deterministic header');
echo 'PASS mixed payment metadata: '.($checks-$baseChecks)." assertions\n";
