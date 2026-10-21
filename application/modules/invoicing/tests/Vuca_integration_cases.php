<?php
// Included by Consulting_integration_test.php inside its disposable database lifetime.
require_once dirname(__DIR__).'/libraries/Invoice_vuca.php';
$vucaProperty=new ReflectionProperty('Invoicing','accounting_vuca');$vucaProperty->setAccessible(true);$vucaProperty->setValue($ci,$db);
$GLOBALS['test_ci']=$ci;
$db->query("UPDATE kons_tr_penawaran SET company=4 WHERE id_quotation='Q2'");
foreach(array('1102-01-01'=>'Piutang Vuca','2104-01-07'=>'PPN nol','1106-01-05'=>'PPh Final') as $code=>$name)$db->insert('coa_master',array('no_perkiraan'=>$code,'nama'=>$name));
$vucaCodes=array_intersect_key($codes,Invoice_consulting::revenues());
$db->insert('ms_invoice_coa_mapping',array('source_company_id'=>'4','owner_company_id'=>'4','accounts'=>json_encode($vucaCodes),'updated_by'=>'7','updated_at'=>date('Y-m-d H:i:s')));
$vucaPost=function($n,$role='sales_consulting'){
    return array('id'=>'A'.$n,'tanggal_invoice'=>'2026-10-21','nomor_po'=>'PO-VUCA','nomor_faktur'=>'FAKTUR-VUCA','sales_role'=>$role,
        'nomor_invoice'=>'TAMPERED','total_nominal'=>'1','total_akhir_jurnal'=>'1','coa_jurnal_4'=>'9999-99-99','nm_coa_4'=>'TAMPERED','id_company_4'=>'7');
};
$vucaCount=function()use($service){return $service->one('SELECT COUNT(*) n FROM tr_invoicing')['n'];};
$vucaIds=array();
foreach(array_keys(Invoice_consulting::revenues()) as $i=>$role){
    $n=201+$i;newSource($db,$n,'1000000.00');
    $beforeAudit=count($GLOBALS['audit']);$response=controllerCall($ci,'save_invoice_vuca',$vucaPost($n,$role));
    verify($response['status']===1,'Vuca create '.$role.': '.$response['msg']);
    verify(count($GLOBALS['audit'])===$beforeAudit+1,'Vuca exactly one audit per create');
    $invoice=$service->one('SELECT * FROM tr_invoicing WHERE id_detail_plan_tagih=?',array($n));$vucaIds[]=$invoice['id'];
    verify($invoice['no_invoice']!=='TAMPERED' && $invoice['tipe_invoice']==='1','Server numbering and Vuca type');
    verify($invoice['total_nominal_jurnal']==='1000000.00' && $invoice['pph_jurnal']==='5000.00' && $invoice['saldo_piutang']==='995000.00','Vuca server calculation: '.json_encode(array_intersect_key($invoice,array_flip(array('total_nominal_jurnal','pph_jurnal','saldo_piutang')))));
    verify($invoice['ppn_jurnal']==='0.00' && $invoice['ppn_persen']==='0.00' && $invoice['tagihan_ppn_jurnal']==='1000000.00','Vuca zero VAT and gross');
    verify($invoice['pajak']==='110000.00' && $invoice['total_akhir']==='1110000.00','Legacy document compatibility fields unchanged');
    $lines=$service->rows("SELECT * FROM tr_jurnal WHERE no_transaksi=? AND jenis_transaksi='Invoicing'",array($invoice['id']));
    verify(Invoice_vuca::saved_revenue($lines)['coa']===$vucaCodes[$role],'Configured revenue persisted instead of hidden COA');
    verify(array_sum(array_column($lines,'debit'))===array_sum(array_column($lines,'kredit')) && count($lines)===4,'Four balanced Vuca journal rows');
    verify(controllerCall($ci,'save_invoice_vuca',$vucaPost($n,$role))['status']===0,'Vuca duplicate TOP blocked');
    $ci->edit_invoicing_vuca($invoice['id']);
    verify(Invoice_vuca::saved_revenue($ci->load->viewData['journals'])['coa']===$vucaCodes[$role],'Edit loads stored revenue');
    $edit=array('id_invoicing'=>$invoice['id'],'invoice_fingerprint'=>Invoice_calculation::fingerprint($invoice),'tanggal_invoice'=>'2026-10-22',
        'nomor_invoice'=>$invoice['no_invoice'],'no_po'=>'PO-EDIT','nomor_faktur'=>'FAKTUR-EDIT','coa_jurnal_4'=>'4101-01-01','sales_role'=>'invalid','total_akhir_jurnal'=>'1');
    $response=controllerCall($ci,'update_invoice_vuca',$edit);verify($response['status']===1,'Vuca edit: '.$response['msg']);
    $lines=$service->rows("SELECT * FROM tr_jurnal WHERE no_transaksi=? AND jenis_transaksi='Invoicing'",array($invoice['id']));
    verify(Invoice_vuca::saved_revenue($lines)['coa']===$vucaCodes[$role],'Edit preserves selected COA despite tampered fields');
    verify($service->one('SELECT saldo_piutang FROM tr_invoicing WHERE id=?',array($invoice['id']))['saldo_piutang']==='995000.00','Edit retains calculation');
    $edited=$service->one('SELECT * FROM tr_invoicing WHERE id=?',array($invoice['id']));
    foreach(array('dpp_nilai_lain','pajak','total_akhir') as $key)verify($edited[$key]===$invoice[$key],'Edit preserves legacy document field '.$key);
    verify(controllerCall($ci,'update_invoice_vuca',$edit)['status']===0,'Stale edit rejected');
}
newSource($db,210,'1000000.00');$before=$vucaCount();
foreach(array('','unknown',array('sales_hse')) as $role)verify(controllerCall($ci,'save_invoice_vuca',$vucaPost(210,$role))['status']===0,'Invalid/array/empty sales role rejected');
$invalid=$vucaPost(210);$invalid['nomor_po']='';verify(controllerCall($ci,'save_invoice_vuca',$invalid)['status']===0,'Required PO enforced server-side');
$db->update('ms_invoice_coa_mapping',array('owner_company_id'=>7),array('source_company_id'=>4));
verify(controllerCall($ci,'save_invoice_vuca',$vucaPost(210))['status']===0,'Foreign accounting owner rejected');
$ci->add_invoice_vuca(210);verify(!empty($ci->template->data['error']),'Bad mapping produces actionable form error');
$db->update('ms_invoice_coa_mapping',array('owner_company_id'=>4),array('source_company_id'=>4));
$ci->add_invoice_vuca(210);verify(empty($ci->template->data['error']) && count($ci->template->data['accounts'])===4,'Active form loads with revenue-only mappings');
verify($vucaCount()===$before,'Invalid inputs did not insert partial invoice');
foreach(array('sequence','header','journal','top','commit') as $i=>$fault){
    $n=220+$i;newSource($db,$n,'1000000.00');$before=$vucaCount();$beforeAudit=count($GLOBALS['audit']);
    $beforeSequence=$service->rows("SELECT * FROM ms_invoice_sequence WHERE kode_entitas='VSB'");
    $db->fault=$fault;$response=controllerCall($ci,'save_invoice_vuca',$vucaPost($n));
    verify($response['status']===0,'Vuca injected '.$fault.' cannot report success');
    verify($vucaCount()===$before && !$service->one('SELECT id FROM tr_invoicing WHERE id_detail_plan_tagih=?',array($n)),'No partial Vuca header '.$fault);
    verify($service->one('SELECT sts_invoice FROM kons_tr_plan_tagih_detail WHERE id=?',array($n))['sts_invoice']==0,'Vuca TOP rollback '.$fault);
    verify($service->one('SELECT sts_invoice FROM kons_tr_actual_plan_tagih WHERE id_detail_plan_tagih=?',array($n))['sts_invoice']==0,'Vuca actual rollback '.$fault);
    verify($service->rows("SELECT * FROM ms_invoice_sequence WHERE kode_entitas='VSB'")===$beforeSequence,'Vuca sequence rollback '.$fault);
    verify(count($GLOBALS['audit'])===$beforeAudit+1 && end($GLOBALS['audit'])[1]===0,'Vuca single failed audit '.$fault);
}
newSource($db,230,'1000000.00');$GLOBALS['audit_failure']=true;
verify(controllerCall($ci,'save_invoice_vuca',$vucaPost(230))['status']===1,'Audit writer failure does not invalidate committed Vuca invoice');
$GLOBALS['audit_failure']=false;
foreach($GLOBALS['audit'] as $event)if(($event[2]['module']??'')==='invoicing' && strpos($event[2]['methode']??'','_vuca')!==false){
    verify(!isset($event[2]['sql']) && empty(array_diff(array_keys($event[2]['data']),array('invoice_type','outcome','legacy_event_count'))),'Safe Vuca audit metadata');
}
$posted=$service->one('SELECT * FROM tr_invoicing WHERE id=?',array($vucaIds[0]));
$db->update('tr_jurnal',array('sts'=>'1'),array('no_transaksi'=>$posted['id']));
$edit=array('id_invoicing'=>$posted['id'],'invoice_fingerprint'=>Invoice_calculation::fingerprint($posted),'tanggal_invoice'=>'2026-10-22','nomor_invoice'=>$posted['no_invoice'],'no_po'=>'PO','nomor_faktur'=>'FAKTUR');
verify(controllerCall($ci,'update_invoice_vuca',$edit)['status']===0,'Posted Vuca journal cannot be edited');
newSource($db,231,'1000000.00');
$db->update('kons_tr_plan_tagih_detail',array('nominal_payment'=>'2000000.00'),array('id'=>231));
$ci->add_invoice_vuca(231);verify((float)$ci->template->data['data_actual']->nominal_payment===2000000.0,'Vuca preview keeps original plan-detail nominal source');
verify(controllerCall($ci,'save_invoice_vuca',$vucaPost(231))['status']===1,'Vuca saves differing plan/actual nominal safely');
$different=$service->one('SELECT * FROM tr_invoicing WHERE id_detail_plan_tagih=231');
verify($different['total_nominal_jurnal']==='2000000.00' && $different['saldo_piutang']==='1990000.00','Stored Vuca nominal matches displayed TOP rather than a different actual amount');
$editable=$service->one('SELECT * FROM tr_invoicing WHERE id=?',array($vucaIds[1]));
$edit=array('id_invoicing'=>$editable['id'],'invoice_fingerprint'=>Invoice_calculation::fingerprint($editable),'tanggal_invoice'=>'2026-10-23','nomor_invoice'=>$editable['no_invoice'],'no_po'=>'PO-FAIL','nomor_faktur'=>'FAKTUR-FAIL');
$oldLines=$service->rows("SELECT * FROM tr_jurnal WHERE no_transaksi=? AND jenis_transaksi='Invoicing' ORDER BY id",array($editable['id']));
foreach(array('delete_journal','invoice_update','journal','commit') as $fault){
    $db->fault=$fault;$beforeAudit=count($GLOBALS['audit']);
    verify(controllerCall($ci,'update_invoice_vuca',$edit)['status']===0,'Vuca edit rejects injected '.$fault);
    verify($service->one('SELECT * FROM tr_invoicing WHERE id=?',array($editable['id']))===$editable,'Vuca edit header rollback '.$fault);
    verify($service->rows("SELECT * FROM tr_jurnal WHERE no_transaksi=? AND jenis_transaksi='Invoicing' ORDER BY id",array($editable['id']))===$oldLines,'Vuca edit retains original journals after '.$fault);
    verify(count($GLOBALS['audit'])===$beforeAudit+1 && end($GLOBALS['audit'])[1]===0,'One failed Vuca edit audit '.$fault);
}
$edit['no_po']='';$edit['nomor_faktur']='';
verify(controllerCall($ci,'update_invoice_vuca',$edit)['status']===1,'Historical edit keeps existing optional-document behavior');
verify(Invoice_vuca::saved_revenue($service->rows("SELECT * FROM tr_jurnal WHERE no_transaksi=? AND jenis_transaksi='Invoicing'",array($editable['id'])))['coa']===$vucaCodes['sales_inhouse'],'Optional legacy documents do not reset chosen revenue');
