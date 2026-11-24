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
$posted=true;
$detail=$model->detail($collision,$posted);
$header=$detail['header'];
check($header['display_no_transaksi']==='VOUCHER','report resolves voucher before colliding approval ID');
check($header['no_referensi']==='DOC-A, DOC-B','report includes all voucher approvals and deduplicates documents');
check($header['category']==='Periodik, Transport','report categories exclude colliding approval');
check($header['no_transaksi']==='VOUCHER'&&$header['debit']==='100.25'&&$header['kredit']==='100.25','source identity and totals remain intact');
check($header['posted_at']===null&&$header['posted_by']===null,'report does not invent posting metadata');
ob_start();require dirname(__DIR__).'/views/preview.php';$html=ob_get_clean();
check(strpos($html,'<th>No. Referensi</th>')!==false&&strpos($html,'<dt>No. Referensi</dt><dd>DOC-A, DOC-B</dd>')!==false&&substr_count($html,'<td>DOC-A, DOC-B</td>')===2,'posted payment lines display the same references as the header');
check(strpos($html,'<dt>No. Transaksi</dt><dd>VOUCHER</dd>')!==false&&strpos($html,'<td>VOUCHER</td>')===false&&strpos($html,'<textarea')===false,'posted preview keeps voucher in the header and descriptions read-only');
$h=$model->detail($legacy,true)['header'];
check($h['display_no_transaksi']==='LEGACY-VOUCHER'&&$h['no_referensi']==='LEGACY-DOC'&&$h['category']==='Expense','approval-ID-only journal resolves to its voucher via id');
foreach ([$document,$missing] as $anchor) {
    $detail=$model->detail($anchor,true);$h=$detail['header'];
    check($h['display_no_transaksi']==='-'&&$h['no_referensi']==='-'&&$h['category']==='-','unmatched report has no approval/document fallback metadata');
    check(!$h['payment_available']&&count($detail['rows'])===2&&$detail['is_balance'],'unmatched journal detail remains available and balanced');
}
$filters=['tab'=>'PAY','year'=>2026];
$list=$model->listing($filters,true);
check(count($list['data'])===5,'all posted journals remain visible including unmatched history');
check($list['totals']===['debit'=>'501.25','kredit'=>'501.25'],'voucher approval count does not multiply journal totals');
check(count($model->groups($filters+['q'=>'WRONG-DOC'],true))===0,'unselected approval metadata is not searchable: WRONG-DOC');
foreach (['LEGACY-DOC','LEGACY-VOUCHER'] as $q) check(count($model->groups($filters+['q'=>$q],true))===1,'id-resolved approval metadata is searchable: '.$q);
check(count($model->groups($filters+['q'=>'DOC-B'],true))===2,'resolved voucher references are searchable');
check(count($model->groups($filters+['category'=>'Kasbon'],true))===0,'colliding approval cannot contribute category filters');
check($model->options($filters,true)['categories']===['-','Expense','Periodik','Transport'],'category options reflect resolved vouchers and unavailable metadata');
$export=$model->export_rows($filters,true);
check(count($export)===10,'export preserves all source COA lines');
foreach ($export as $entry) {
    $h=$entry['header'];
    check($h['no_referensi']===(['VOUCHER'=>'DOC-A, DOC-B','LEGACY-ID'=>'LEGACY-DOC'][$h['no_transaksi']] ?? '-'),'Excel metadata matches list and detail');
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
$db->query('DELETE FROM tr_jurnal');$db->query('DELETE FROM payment_approve');
foreach ([
    ['BK--1126-0007','BK--1126-0015','PERIODIK-2026-00218','periodik'],
    ['BK--1126-0011','BK--1126-0007','KS-2026-00342','kasbon'],
    ['APP-LONG','BK--1126-00150','DOC-LONG','periodik'],
    ['APP-BANK','BK-1101-02-01-1126-0001','DOC-BANK','periodik'],
    ['BK--1026-0033','BK--1126-0015','PERIODIK-2026-00218','periodik']
] as $p) $db->insert('payment_approve',array_combine(['id','id_payment','no_doc','tipe'],$p));
$posted=journal('Payment','1','BK--1126-0015','7','7240344.00','7240344.00');
$pending=journal('Payment','0','BK--1126-0007','7','2000000.00','2000000.00');
$db->where('no_transaksi','BK--1126-0007')->update('tr_jurnal',['keterangan'=>'Kasbon {REF:KS-2026-00342} - BK--1126-0011']);
journal('Payment','1','BK--1126-00150');
journal('Payment','1','BK-1101-02-01-1126-0001');
$db->where('no_transaksi','BK-1101-02-01-1126-0001')->update('tr_jurnal',['keterangan'=>'Detail BK--1126-0007']);
journal('Payment','1','BK--1026-0033');
journal('Payment','1',' BK--1126-0015, BK-1101-02-01-1126-0001 ');
$before=$db->order_by('id')->get('tr_jurnal')->result_array();
$approvalsBefore=$db->order_by('id')->get('payment_approve')->result_array();
$model=new Jurnal_all_model();$model->db=$db;
check(strpos($model->detail($posted,true)['header']['extra_search'],'BK--1126-0007')!==false,'collision fixture retains internal approval identity');
$query=$filters+['q'=>'BK--1126-0007'];
check(count($model->groups($query,true))===0,'full voucher excludes hidden approval IDs and detail descriptions of other vouchers');
$page=$model->listing($query,true);
check($page['recordsTotal']===5&&$page['recordsFiltered']===0&&$page['data']===[],'report excludes pending voucher and unrelated posted search hits');
check($page['totals']===['debit'=>'0.00','kredit'=>'0.00'],'excluded vouchers do not contribute totals');
check($model->export_rows($query,true)===[],'export uses the same full voucher filter');
foreach (['BK--1126-0015','  bk--1126-0015  '] as $q) {
    $page=$model->listing($filters+['q'=>$q],true);
    check($page['recordsFiltered']===3,'full voucher matches canonical, legacy approval-key and comma-separated vouchers');
    check($page['totals']===['debit'=>'7240544.50','kredit'=>'7240544.50'],'full voucher totals include only matching groups');
    check(count($model->export_rows($filters+['q'=>$q],true))===6,'full voucher export retains all matching COA lines');
}
check(count($model->groups($filters+['q'=>'BK--1126-00150'],true))===1,'full voucher matching is exact rather than prefix-based');
check(count($model->groups($filters+['q'=>'BK-1101-02-01-1126-0001'],true))===2,'bank-code voucher matches standalone and comma-separated tokens');
check(count($model->groups($filters+['q'=>'BK--1026-0033'],true))===0,'legacy approval key cannot masquerade as the displayed voucher');
foreach (['PERIODIK-2026-00218','1126-0015','Biaya khusus','STM'] as $q) {
    check(count($model->groups($filters+['q'=>$q],true))>0,'ordinary reference, partial, COA and company search stays broad: '.$q);
}
check(count($model->groups($filters+['q'=>'BK--1126-0007'],false))===1,'pending voucher search stays compatible');
check($model->detail($pending,false)['header']['display_no_transaksi']==='BK--1126-0007','pending resolver retains its own voucher identity');
$db->where('no_transaksi','BK--1126-0007')->update('tr_jurnal',['sts'=>'1']);
$page=$model->listing($filters+['search'=>['value'=>'BK--1126-0007']],true);
check($page['recordsFiltered']===1&&$page['data'][0]['display_no_transaksi']==='BK--1126-0007','voucher becomes searchable after posting without including its colliding approval');
check($page['totals']===['debit'=>'2000000.00','kredit'=>'2000000.00'],'newly posted voucher keeps its own totals');
$db->where('no_transaksi','BK--1126-0007')->update('tr_jurnal',['sts'=>'0']);
$expense=journal('Expense Report','1','BK--1126-0007');
check(count($model->groups(['tab'=>'EXP','year'=>2026,'q'=>'BK--1126-0007'],true))===1,'full BK-shaped query does not restrict other report tabs');
$db->where_in('id',[$expense-1,$expense])->delete('tr_jurnal');
check($db->order_by('id')->get('tr_jurnal')->result_array()===$before,'search/list/export never mutate source journals');
check($db->order_by('id')->get('payment_approve')->result_array()===$approvalsBefore,'search/list/export never mutate approvals');
echo 'PASS report payment mapping: '.($checks-$baseChecks)." assertions\n";
