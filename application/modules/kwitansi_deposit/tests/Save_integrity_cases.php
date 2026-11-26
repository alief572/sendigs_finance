<?php
// Runs only inside the native disposable database runner.
$kwState=function()use($service){$state=array();foreach(array('tr_kwitansi_deposit','tr_customer_deposit','tr_invoicing','tr_jurnal')as $table)$state[$table]=$service->rows('SELECT * FROM '.$table.' ORDER BY id');return Invoice_calculation::fingerprint($state);};
$createPost=array('id_spk_penawaran'=>'SP-CLOSURE-KW','id_customer'=>'KW-CUST','tanggal_kwitansi'=>'2098-10-30','nilai_kwitansi'=>'1500000.00');
$depth=new ReflectionProperty('CI_DB_driver','_trans_depth');$depth->setAccessible(true);
foreach(array('kw_number'=>0,'kw_deposit_number'=>0,'kw_header'=>1,'deposit_insert'=>2,'kw_invoice'=>3,'journal'=>4,'commit'=>4)as $fault=>$expectedWrites){
    $before=$kwState();$auditBefore=count($GLOBALS['audit']);$db->kwWrites=array();$db->fault=$fault;
    $failed=$kwService->create($createPost,$kwModel);
    verify($failed['status']===0 && $db->fault==='','Kwitansi create fault reached '.$fault.': '.$failed['msg']);
    verify(count($db->kwWrites)===$expectedWrites,'Stop immediately at failed write '.$fault);
    verify($kwState()===$before,'All kwitansi artifacts rolled back '.$fault);
    verify(count($GLOBALS['audit'])===$auditBefore+1,'One audit result per rejected kwitansi '.$fault);
    verify($depth->getValue($db)===0,'No nested transaction remains '.$fault);
}
verify($db->trans_begin(),'Start numbering evidence fixture');
$ghostInvoice=$service->one('SELECT * FROM tr_invoicing LIMIT 1');$ghostInvoice['id']='NUMBER-GHOST';$ghostInvoice['id_actual_plan_tagih']='NUMBER-GHOST';$ghostInvoice['no_invoice']='KD/2097/10/9999';$ghostInvoice['consulting_top_key']=null;$ghostInvoice['id_detail_plan_tagih']=0;
verify($db->insert('tr_invoicing',$ghostInvoice),'Orphan invoice number evidence: '.json_encode($db->error()));
$ghostDeposit=$service->one('SELECT * FROM tr_customer_deposit LIMIT 1');unset($ghostDeposit['id']);$ghostDeposit['no_deposit']='DEP/2097/10/2000';$ghostDeposit['no_referensi']='KD/2097/10/10000';
verify($db->insert('tr_customer_deposit',$ghostDeposit),'Orphan deposit reference evidence');
$ghostJournal=$service->one('SELECT * FROM tr_jurnal LIMIT 1');unset($ghostJournal['id']);$ghostJournal['no_jurnal']='NUMBER-GHOST';$ghostJournal['no_transaksi']='KD/2097/10/10001';
verify($db->insert('tr_jurnal',$ghostJournal),'Orphan journal number evidence');
$ghostHeader=$service->one('SELECT * FROM tr_kwitansi_deposit LIMIT 1');unset($ghostHeader['id']);$ghostHeader['no_kwitansi']='KD/2097/10/0002';
verify($db->insert('tr_kwitansi_deposit',$ghostHeader),'Low header sequence with later ID');
verify($kwModel->generate_no_kwitansi(null,'2097-10-30')==='KD/2097/10/10002','Numeric max across header and orphan invoice/deposit/journal; no lexical order');
verify($kwModel->generate_deposit_number('2097-10-30')==='DEP/2097/10/2001','Deposit numeric max');
verify($kwModel->generate_no_kwitansi(null,'2097-11-01')==='KD/2097/11/0001','Period isolation');
verify($db->trans_rollback(),'Number evidence fixture cleanup');
$workers=array(workerStart(array('database'=>$database,'operation'=>'create_kwitansi','post'=>$createPost)),workerStart(array('database'=>$database,'operation'=>'create_kwitansi','post'=>$createPost)));
$created=array();foreach($workers as $worker)$created[]=workerFinish($worker);
verify(array_sum(array_column($created,'status'))===2,'Concurrent valid kwitansi creates both commit');
verify($created[0]['no_kwitansi']!==$created[1]['no_kwitansi'] && $created[0]['no_deposit']!==$created[1]['no_deposit'],'Concurrent numbering unique for both document types');
foreach($created as $r){
 verify(count($service->rows('SELECT id FROM tr_kwitansi_deposit WHERE no_kwitansi=?',array($r['no_kwitansi'])))===1,'Concurrent header exists once');
 verify(count($service->rows('SELECT id FROM tr_invoicing WHERE no_invoice=?',array($r['no_kwitansi'])))===1,'Concurrent invoice exists once');
 verify(count($service->rows('SELECT id FROM tr_customer_deposit WHERE no_referensi=?',array($r['no_kwitansi'])))===1,'Concurrent deposit exists once');
 verify(count($service->rows("SELECT id FROM tr_jurnal WHERE no_transaksi=? AND jenis_transaksi='Kwitansi Deposit'",array($r['no_kwitansi'])))===2,'Concurrent two accrual rows');
}
