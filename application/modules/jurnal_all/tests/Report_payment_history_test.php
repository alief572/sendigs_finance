<?php
require __DIR__.'/Query_test.php';
$baseChecks=$checks;
$db->query('DELETE FROM tr_jurnal');
$db->query('DELETE FROM payment_approve');
$cases=[
    ['BK--0626-0019','BK--0926-0001','PERIODIK-2026-00039','periodik','Periodik','7','2026-09-05','985350.00'],
    ['BK--0426-0045','BK--0426-0008','RQ-2026-00052','transport','Transport','4','2026-04-10','61000.00'],
    ['BK--0726-0040','BK--0126-0001','DPM-2026-00001','direct_payment','Direct Payment','1','2026-01-24','22865077.00']
];
$anchors=[];
foreach ($cases as $c) {
    [$native,$voucher,$doc,$type,$category,$company,$date,$amount]=$c;
    $db->insert('payment_approve',['id'=>$native,'id_payment'=>$voucher,'no_doc'=>$doc,'tipe'=>$type]);
    $db->insert('payment_approve',['id'=>$voucher,'id_payment'=>null,'no_doc'=>'UNRELATED-'.$native,'tipe'=>'kasbon']);
    $anchors[$native]=journal('Payment','1',$native,$company,$amount,$amount);
    $db->where('no_transaksi',$native)->update('tr_jurnal',['tgl_jurnal'=>$date]);
}
$before=$db->order_by('id')->get('tr_jurnal')->result_array();
$model=new Jurnal_all_model();$model->db=$db;
$filters=['tab'=>'PAY','year'=>2026];
$list=$model->listing($filters,true);
check(count($list['data'])===3,'all three approved histories remain visible');
check($list['totals']===['debit'=>'23911427.00','kredit'=>'23911427.00'],'history mapping preserves journal totals');
foreach ($cases as $c) {
    [$native,$voucher,$doc,$type,$category,$company,$date,$amount]=$c;
    $detail=$model->detail($anchors[$native],true);$h=$detail['header'];
    check($h['display_no_transaksi']===$voucher,'verified history displays voucher: '.$native);
    check($h['no_referensi']===$doc&&$h['category']===$category,'verified voucher supplies reference and category: '.$native);
    check($h['keterangan']==='Pembayaran '.$category.' - '.$voucher,'history description uses resolved voucher');
    check($h['no_transaksi']===$native&&$h['debit']===$amount&&$h['kredit']===$amount,'native identity and amounts remain unchanged');
    check(count($detail['rows'])===2&&$detail['is_balance'],'history COA detail remains balanced');
    check(count($model->groups($filters+['q'=>$voucher],true))===1,'voucher is searchable');
    check(count($model->groups($filters+['q'=>$doc],true))===1,'document is searchable');
    check(count($model->groups($filters+['q'=>$native],true))===1,'native accounting key remains searchable');
    check(count($model->groups($filters+['category'=>$category],true))===1,'resolved category filters history');
}
$export=$model->export_rows($filters,true);
check(count($export)===6,'history export retains every COA line');
foreach ($export as $entry) {
    $expected=array_values(array_filter($cases,function($c)use($entry){return $c[0]===$entry['header']['no_transaksi'];}))[0];
    check($entry['header']['display_no_transaksi']===$expected[1]&&$entry['header']['no_referensi']===$expected[2],'Excel matches resolved history');
}
check(count($model->groups($filters+['q'=>'UNRELATED-'],true))===0,'colliding unpaid approvals never supply report metadata');
check($db->order_by('id')->get('tr_jurnal')->result_array()===$before,'report operations do not mutate source journals');

[$native,$voucher,$doc,$type,$category,$company,$date,$amount]=$cases[0];
$db->where('no_transaksi',$native)->update('tr_jurnal',['sts'=>'0']);
$model=new Jurnal_all_model();$model->db=$db;
check($model->detail($anchors[$native],false)['header']['display_no_transaksi']===$voucher,'pending history still uses existing Payment resolver');
$db->where('no_transaksi',$native)->update('tr_jurnal',['sts'=>'1']);
$db->insert('payment_approve',['id'=>'COLLISION','id_payment'=>$native,'no_doc'=>'WRONG-HISTORY','tipe'=>'kasbon']);
$model=new Jurnal_all_model();$model->db=$db;
check($model->detail($anchors[$native],true)['header']['no_referensi']===$doc,'explicit mapping wins over a colliding native voucher');
foreach (['id_company'=>'4','tgl_jurnal'=>'2026-09-06','debit'=>'1.00','kredit'=>'1.00','jenis_transaksi'=>'Transport'] as $field=>$wrong) {
    $db->where('no_transaksi',$native)->update('tr_jurnal',[$field=>$wrong]);
    $model=new Jurnal_all_model();$model->db=$db;
    $h=$model->detail($anchors[$native],true)['header'];
    check($h['display_no_transaksi']==='-'&&$h['no_referensi']==='-','history guard refuses mismatch: '.$field);
    $db->where('no_transaksi',$native)->delete('tr_jurnal');
    foreach ($before as $line) if ($line['no_transaksi']===$native) $db->insert('tr_jurnal',$line);
}
$db->where('id_payment',$voucher)->delete('payment_approve');
$model=new Jurnal_all_model();$model->db=$db;
check($model->detail($anchors[$native],true)['header']['no_referensi']==='-','missing history voucher cannot fall back to approval ID');
$db->insert('payment_approve',['id'=>'NEW-APP','id_payment'=>'OTHER-VOUCHER','no_doc'=>'NOT-APPROVED','tipe'=>'periodik']);
$unknown=journal('Payment','1','NEW-APP');
$model=new Jurnal_all_model();$model->db=$db;
check($model->detail($unknown,true)['header']['no_referensi']==='-','unlisted history retains strict voucher-only policy');
echo 'PASS report payment history: '.($checks-$baseChecks)." assertions\n";
