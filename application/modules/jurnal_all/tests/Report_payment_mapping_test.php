<?php
require __DIR__.'/Query_test.php';
$baseChecks=$checks;
$db->query('DELETE FROM tr_jurnal');
$db->query('DELETE FROM payment_approve');
foreach ([
    ['VOUCHER','OTHER-VOUCHER','WRONG-DOC','kasbon'],
    ['APP-A','VOUCHER','DOC-A','periodik'],
    ['APP-B','VOUCHER','DOC-B','transport'],
    ['APP-C','VOUCHER','DOC-A','periodik'],
    ['LEGACY-ID','LEGACY-VOUCHER','LEGACY-DOC','expense'],
    ['APP-D','DOC-VOUCHER','DOCUMENT-KEY','expense'],
    ['PENDING-ID','PENDING-VOUCHER','PENDING-DOC','periodik']
] as $p) $db->insert('payment_approve',array_combine(['id','id_payment','no_doc','tipe'],$p));
$collision=journal('Payment','1','VOUCHER');
$legacy=journal('Payment','1','LEGACY-ID');
$document=journal('Payment','1','DOCUMENT-KEY');
$missing=journal('Payment','1','MISSING');
$typed=journal('Transport','1','VOUCHER');
$pending=journal('Payment','0','PENDING-ID');
$model=new Jurnal_all_model();$model->db=$db;
$header=$model->detail($collision,true)['header'];
check($header['display_no_transaksi']==='VOUCHER','report resolves voucher before colliding approval ID');
check($header['no_referensi']==='DOC-A, DOC-B','report includes all voucher approvals and deduplicates documents');
check($header['category']==='Periodik, Transport','report categories exclude colliding approval');
check($header['no_transaksi']==='VOUCHER'&&$header['debit']==='100.25'&&$header['kredit']==='100.25','source identity and totals remain intact');
check($header['posted_at']===null&&$header['posted_by']===null,'report does not invent posting metadata');
foreach ([$legacy,$document,$missing] as $anchor) {
    $detail=$model->detail($anchor,true);$h=$detail['header'];
    check($h['display_no_transaksi']==='-'&&$h['no_referensi']==='-'&&$h['category']==='-','unmatched report has no approval/document fallback metadata');
    check(!$h['payment_available']&&count($detail['rows'])===2&&$detail['is_balance'],'unmatched journal detail remains available and balanced');
}
$filters=['tab'=>'PAY','year'=>2026];
$list=$model->listing($filters,true);
check(count($list['data'])===5,'all posted journals remain visible including unmatched history');
check($list['totals']===['debit'=>'501.25','kredit'=>'501.25'],'voucher approval count does not multiply journal totals');
foreach (['WRONG-DOC','LEGACY-DOC','LEGACY-VOUCHER'] as $q) check(count($model->groups($filters+['q'=>$q],true))===0,'unselected approval metadata is not searchable: '.$q);
check(count($model->groups($filters+['q'=>'DOC-B'],true))===2,'resolved voucher references are searchable');
check(count($model->groups($filters+['category'=>'Kasbon'],true))===0,'colliding approval cannot contribute category filters');
check($model->options($filters,true)['categories']===['-','Periodik','Transport'],'category options reflect resolved vouchers and unavailable metadata');
$export=$model->export_rows($filters,true);
check(count($export)===10,'export preserves all source COA lines');
foreach ($export as $entry) {
    $h=$entry['header'];
    check($h['no_referensi']===($h['no_transaksi']==='VOUCHER'?'DOC-A, DOC-B':'-'),'Excel metadata matches list and detail');
}
$h=$model->detail($typed,true)['header'];
check($h['display_no_transaksi']==='VOUCHER'&&$h['no_referensi']==='DOC-A, DOC-B','legacy payment type also uses voucher identity');
$h=$model->detail($pending,false)['header'];
check($h['display_no_transaksi']==='PENDING-VOUCHER'&&$h['no_referensi']==='PENDING-DOC','pending Payment retains approval-ID compatibility on the same model');
$multi=journal('Payment','1',' VOUCHER, DOC-VOUCHER, VOUCHER ');
$model=new Jurnal_all_model();$model->db=$db;
$h=$model->detail($multi,true)['header'];
check($h['display_no_transaksi']==='VOUCHER, DOC-VOUCHER'&&$h['no_referensi']==='DOC-A, DOC-B, DOCUMENT-KEY','comma-separated voucher tokens are trimmed and deduplicated');
foreach (['Transportasi','Kasbon','Expense'] as $type) {
    $anchor=journal($type,'1','VOUCHER');
    $model=new Jurnal_all_model();$model->db=$db;
    check($model->detail($anchor,true)['header']['no_referensi']==='DOC-A, DOC-B','report legacy type uses voucher mapping: '.$type);
}
echo 'PASS report payment mapping: '.($checks-$baseChecks)." assertions\n";
