<?php
// Native controller/service integration; loaded only by the disposable database runner.
$GLOBALS['test_ci']=$receiptCi;
$closureAllocation=function($id,$amount,$kind=1,$date='2026-10-18') use($db){
    verify($db->insert('tr_alokasi_detail',array('id'=>$id,'tipe_bank'=>1,'tanggal_transaksi'=>$date,'nominal_debit'=>$amount,'nilai_terpakai'=>'0.00')),'Closure allocation');
    verify($db->insert('tr_alokasi_split',array('id'=>$id+1,'id_alokasi_detail'=>$id,'nominal'=>$amount,'nilai_terpakai'=>'0.00','jenis_alokasi'=>$kind)),'Closure split');
    return $id+1;
};
$closureInvoice=function($id) use($db,$ci,$service){
    newSource($db,$id,'100.00');$r=controllerCall($ci,'save_invoice',invoiceRequest($service,$id,'KB'));
    verify($r['status']===1,'Closure invoice created');return $r['id'];
};
$closurePost=function($ids,$allocation,$kind='invoice',$deposit=0,$more=0) use($receiptService){
    $p=$receiptService->preview(array('choose_inv'=>$ids,'id_alokasi'=>$allocation,'receipt_kind'=>$kind,'pph23_dipotong'=>'N','deposit_choice'=>$deposit?'Y':'N','deposit_amount'=>Invoice_calculation::decimal($deposit)));
    $post=array('no_inv'=>(string)count($p['rows']),'id_alokasi'=>$allocation,'receipt_kind'=>$kind,'pph23_dipotong'=>'N','deposit_choice'=>$deposit?'Y':'N','deposit_amount'=>Invoice_calculation::decimal($deposit),'allocation_fingerprint'=>$p['allocation_fingerprint'],
        'id_customer'=>$p['rows'][0]['invoice']['id_customer'],'id_company'=>$p['rows'][0]['owner']['id']);
    foreach($p['rows'] as $i=>$r){$n=$i+1;$post['id_inv_'.$n]=$r['invoice']['id'];$post['receipt_fingerprint_'.$n]=$r['fingerprint'];$post['penerimaan_'.$n]=$r['invoice']['saldo_piutang'];$post['pph_actual_'.$n]='0.00';$post['biaya_admin_'.$n]='0.00';$post['sisa_piutang_'.$n]='0.00';$post['dep_'.$n]=Invoice_calculation::decimal($i===0?$deposit:0);$post['lebih_bayar_'.$n]=Invoice_calculation::decimal($i===0?$more:0);}
    return $post;
};
$sourceId=$closureInvoice(95);$useId=$closureInvoice(96);
$sourceInvoice=$service->one('SELECT * FROM tr_invoicing WHERE id=?',array($sourceId));
$closureOwner=json_decode($sourceInvoice['consulting_snapshot'],true)['owner']['id'];$closureCustomer=$sourceInvoice['id_customer'];
$sourceAlloc=$closureAllocation(2000,'120.00');
$sourcePost=$closurePost(array($sourceId),$sourceAlloc,'invoice',0,2000);
// Failure at every deposit-creation stage must preserve all related finance rows.
$fingerprintTables=array('tr_invoicing','tr_penerimaan_piutang','tr_penerimaan_piutang_detail','tr_jurnal','tr_customer_deposit','tr_customer_deposit_mutasi','tr_alokasi_split','tr_alokasi_detail');
$fingerprint=function()use($service,$fingerprintTables){$v=array();foreach($fingerprintTables as $table)$v[$table]=$service->rows('SELECT * FROM '.$table.' ORDER BY id');return hash('sha256',json_encode($v));};
foreach(array('deposit_insert','mutation_insert','commit')as $fault){$before=$fingerprint();$db->fault=$fault;$r=controllerCall($receiptCi,'save_penerimaan_piutang',$sourcePost);verify($r['status']===0&&$db->fault==='','Creation fault reached '.$fault);verify($fingerprint()===$before,'No partial writes creating deposit '.$fault);}
$sourceSaved=controllerCall($receiptCi,'save_penerimaan_piutang',$sourcePost);verify($sourceSaved['status']===1,'Real overpayment receipt creates deposit');
$sourceHeader=$service->one('SELECT * FROM tr_penerimaan_piutang WHERE no_surat=?',array($sourceSaved['no_surat']));
$voucher=$service->one('SELECT * FROM tr_customer_deposit WHERE no_referensi=?',array($sourceSaved['no_surat']));
verify($receiptService->get_customer_active_deposit($closureCustomer,$closureOwner)===2000,'Validated native overpayment is available');
verify($monitor->get_customer_active_deposit_total($closureCustomer,$closureOwner)===20.0,'Model compatibility lookup shares integrity validator');
$context=array('id_customer'=>$closureCustomer,'id_company'=>$closureOwner);
verify(controllerCall($receiptCi,'get_customer_deposit',$context)['status']===1,'Explicit customer/company balance works');
verify(controllerCall($receiptCi,'get_customer_deposit',array('id_customer'=>$closureCustomer))['status']===0,'Missing company never combines balances');
verify(controllerCall($receiptCi,'get_customer_deposit',array('id_invoice'=>$useId,'id_company'=>'foreign'))['status']===0,'Forged invoice company rejected');
verify(controllerCall($receiptCi,'get_customer_deposit',array('id_invoice'=>$useId,'id_customer'=>'foreign'))['status']===0,'Forged invoice customer rejected');
verify(controllerCall($receiptCi,'get_customer_deposit_ledger',$context)['id_company']===(string)$closureOwner,'Ledger reports selected company');
$receiptCi->input->values=$context+array('receipt_kind'=>'invoice','pph23_dipotong'=>'N');ob_start();$receiptCi->get_inv_by_cust();$invoiceList=ob_get_clean();
verify(substr_count($invoiceList,'value="'.$useId.'"')===1,'Native invoice lookup renders each selected-owner document once');
$foreign=$sourceInvoice;$foreign['id']='CLOSURE-FOREIGN';$foreign['id_actual_plan_tagih']='CLOSURE-FOREIGN';$foreign['consulting_top_key']=null;$foreign['saldo_piutang']='100.00';
$foreignSnapshot=json_decode($foreign['consulting_snapshot'],true);$foreignSnapshot['owner']=array('id'=>'3','nm_company'=>'Sustain');$foreign['consulting_snapshot']=json_encode($foreignSnapshot);
verify($db->insert('tr_invoicing',$foreign),'Foreign company open invoice');
$receiptCi->input->values=$context+array('receipt_kind'=>'invoice','pph23_dipotong'=>'N');ob_start();$receiptCi->get_inv_by_cust();$invoiceList=ob_get_clean();verify(strpos($invoiceList,'CLOSURE-FOREIGN')===false,'Native document lookup excludes foreign company');
$choices=controllerCall($receiptCi,'get_receipt_companies',array('id_customer'=>$closureCustomer,'receipt_kind'=>'invoice'));verify(in_array('3',array_column($choices['companies'],'id'),true),'Multiple-company selector includes other invoice owner');
verify($db->delete('tr_invoicing',array('id'=>'CLOSURE-FOREIGN')),'Remove foreign-company invoice');
$companyChoices=controllerCall($receiptCi,'get_receipt_companies',array('id_customer'=>$closureCustomer,'receipt_kind'=>'invoice'));
verify($companyChoices['status']===1&&in_array((string)$closureOwner,array_column($companyChoices['companies'],'id'),true),'Company choices derived from open invoices');
// Same name, different customer must not inherit company choices or deposit balance.
$other=$sourceInvoice;$other['id']='CLOSURE-SAME-NAME';$other['id_actual_plan_tagih']='CLOSURE-SAME-NAME';$other['id_customer']='OTHER-CUSTOMER';$other['consulting_top_key']=null;
verify($db->insert('tr_invoicing',$other),'Same-name customer invoice');
verify(controllerCall($receiptCi,'get_customer_deposit',array('id_customer'=>'OTHER-CUSTOMER','id_company'=>$closureOwner))['saldo']==='0.00','Same-name customer does not inherit balance');
$renamed=$other;$renamed['id']='CLOSURE-RENAMED';$renamed['id_actual_plan_tagih']='CLOSURE-RENAMED';$renamed['id_customer']=$closureCustomer;$renamed['nm_customer']='Renamed customer';
verify($db->insert('tr_invoicing',$renamed),'Renamed snapshot invoice');
class ClosureTemplate {public $data=array();function set($name,$value=null){if(is_array($name))$this->data=array_merge($this->data,$name);else $this->data[$name]=$value;}function title($title){}function render($view){}}
$receiptCi->template=new ClosureTemplate;$receiptCi->add_penerimaan_uang($sourceAlloc);
$customerOptions=array_column($receiptCi->template->data['results']['list_customer'],'id_customer');verify(count(array_keys($customerOptions,$closureCustomer,true))===1,'Customer picker deduplicates stable ID across historical names');verify(in_array('OTHER-CUSTOMER',$customerOptions,true),'Same name remains separate customer option');
verify($db->delete('tr_invoicing',array('id'=>'CLOSURE-RENAMED')),'Remove renamed fixture');
verify($db->delete('tr_invoicing',array('id'=>'CLOSURE-SAME-NAME')),'Remove same-name fixture');
$useAlloc=$closureAllocation(2010,'80.00');$usePost=$closurePost(array($useId),$useAlloc,'invoice',2000);
// Tamper ledger/source; cash-only preview still works while deposit usage is blocked.
foreach(array('missing_in','chain','source','date')as $fault){
    $db->trans_begin();
    if($fault==='missing_in')$db->delete('tr_customer_deposit_mutasi',array('id_deposit'=>$voucher['id']));
    if($fault==='chain')$db->update('tr_customer_deposit_mutasi',array('saldo_sebelum'=>'1.00'),array('id_deposit'=>$voucher['id']));
    if($fault==='source')$db->update('tr_customer_deposit',array('no_referensi'=>'MISSING'),array('id'=>$voucher['id']));
    if($fault==='date')$db->update('tr_customer_deposit',array('tanggal_aktif'=>null),array('id'=>$voucher['id']));
    $badBalance=controllerCall($receiptCi,'get_customer_deposit',$context);verify($badBalance['status']===0&&$badBalance['saldo']===null,'Invalid deposit is unavailable '.$fault);
    $cashPreview=$receiptService->preview(array('choose_inv'=>array($useId),'id_alokasi'=>$useAlloc,'pph23_dipotong'=>'N'));verify(!empty($cashPreview['deposit_error']),'Cash preview survives deposit anomaly '.$fault);
    verify(controllerCall($receiptCi,'save_penerimaan_piutang',$usePost)['status']===0,'Invalid deposit cannot be consumed '.$fault);
    $db->trans_rollback();
}
$cashId=$closureInvoice(100);$cashAllocation=$closureAllocation(2070,'100.00');$cashPost=$closurePost(array($cashId),$cashAllocation);
verify($db->update('tr_customer_deposit',array('no_referensi'=>'MISSING'),array('id'=>$voucher['id'])),'Corrupt source for cash-only receipt test');
$cashSaved=controllerCall($receiptCi,'save_penerimaan_piutang',$cashPost);verify($cashSaved['status']===1,'Cash-only receipt commits despite unrelated unusable deposit');
verify($db->update('tr_customer_deposit',array('no_referensi'=>$voucher['no_referensi']),array('id'=>$voucher['id'])),'Restore source after cash test');
$cashHeader=$service->one('SELECT id FROM tr_penerimaan_piutang WHERE no_surat=?',array($cashSaved['no_surat']));verify(controllerCall($receiptCi,'rollback_penerimaan',array('id_penerimaan'=>$cashHeader['id']))['status']===1,'Cash-only rollback');
foreach(array('deposit_update','mutation_insert','commit')as $fault){$before=$fingerprint();$db->fault=$fault;verify(controllerCall($receiptCi,'save_penerimaan_piutang',$usePost)['status']===0&&$db->fault==='','Consumption fault reached '.$fault);verify($fingerprint()===$before,'No partial consumption '.$fault);}
// Competing allocations/invoices both request the same available deposit.
$competeId=$closureInvoice(97);$competeAlloc=$closureAllocation(2020,'80.00');$competePost=$closurePost(array($competeId),$competeAlloc,'invoice',2000);
$workers=array(workerStart(array('database'=>$database,'operation'=>'receipt','post'=>$usePost)),workerStart(array('database'=>$database,'operation'=>'receipt','post'=>$competePost)));
$race=array();foreach($workers as $w)$race[]=workerFinish($w);verify(array_sum(array_column($race,'status'))===1,'Only one competing deposit consumer commits');
verify($receiptService->get_customer_active_deposit($closureCustomer,$closureOwner)===0,'Concurrent consumers cannot make saldo negative');
verify(controllerCall($receiptCi,'rollback_penerimaan',array('id_penerimaan'=>$sourceHeader['id']))['status']===0,'Consumed source cannot be rolled back');
$winner=$race[0]['status']?$race[0]:$race[1];$winnerHeader=$service->one('SELECT * FROM tr_penerimaan_piutang WHERE no_surat=?',array($winner['no_surat']));
verify(controllerCall($receiptCi,'rollback_penerimaan',array('id_penerimaan'=>$winnerHeader['id']))['status']===1,'Consumer rollback succeeds');
verify($receiptService->get_customer_active_deposit($closureCustomer,$closureOwner)===2000,'Consumer rollback restores valid ledger/source balance');
verify(controllerCall($receiptCi,'rollback_penerimaan',array('id_penerimaan'=>$sourceHeader['id']))['status']===1,'Source rollback succeeds after dependencies removed');
verify($receiptService->get_customer_active_deposit($closureCustomer,$closureOwner)===0,'Source rollback removes available deposit');
// Invoice order and ID/value mapping remain stable even for reversed request payloads.
verify($db->update('tr_invoicing',array('tanggal_invoice'=>'2026-10-01'),array('id'=>$useId)),'Set oldest invoice');
$batchAlloc=$closureAllocation(2030,'200.00');$batch=$closurePost(array($competeId,$useId),$batchAlloc);
verify($batch['id_inv_1']===$useId,'Preview orders invoice date before request order');
$reverse=$batch;foreach(array('id_inv_','receipt_fingerprint_','penerimaan_','pph_actual_','biaya_admin_','sisa_piutang_','dep_','lebih_bayar_')as $key){$reverse[$key.'1']=$batch[$key.'2'];$reverse[$key.'2']=$batch[$key.'1'];}
$ordered=controllerCall($receiptCi,'save_penerimaan_piutang',$reverse);verify($ordered['status']===1,'Save binds values to invoice IDs after sorting');
// Report total must count one bank amount for a receipt with two invoice details.
$nativePage=$receiptModel->get_report_penerimaan_serverside('', '', '', $ordered['no_surat'],0,10,1,'desc','invoice',$closureCustomer);
verify($nativePage['recordsFiltered']===1&&count($nativePage['receipts'][0]['details'])===2&&$nativePage['total_nominal']===200.0,'Multi-invoice receipt remains one report header');
$batchHeader=$service->one('SELECT * FROM tr_penerimaan_piutang WHERE no_surat=?',array($ordered['no_surat']));verify(controllerCall($receiptCi,'rollback_penerimaan',array('id_penerimaan'=>$batchHeader['id']))['status']===1,'Ordered batch rollback');
// FIFO follows activation date, independently of voucher creation ID.
$sourceAgain=$closurePost(array($sourceId),$sourceAlloc,'invoice',0,2000);
$sourceAgainSaved=controllerCall($receiptCi,'save_penerimaan_piutang',$sourceAgain);verify($sourceAgainSaved['status']===1,'FIFO later activation source');
$olderSource=$closureInvoice(98);$olderAllocation=$closureAllocation(2050,'130.00',1,'2026-10-17');$olderPost=$closurePost(array($olderSource),$olderAllocation,'invoice',0,3000);
$olderSaved=controllerCall($receiptCi,'save_penerimaan_piutang',$olderPost);verify($olderSaved['status']===1,'FIFO earlier activation created second');
$olderVoucher=$service->one('SELECT * FROM tr_customer_deposit WHERE no_referensi=?',array($olderSaved['no_surat']));
verify($db->update('tr_customer_deposit',array('nm_customer'=>'Historical renamed customer'),array('id'=>$olderVoucher['id'])),'Historical customer name variant');
$pairSummaries=array_values(array_filter($monitor->get_customer_summary(),function($r)use($closureCustomer,$closureOwner){return (string)$r['id_customer']===(string)$closureCustomer;}));verify(count($pairSummaries)===1,'Monitoring groups by customer only despite historical name changes');
$fifoInvoice=$closureInvoice(99);$fifoAllocation=$closureAllocation(2060,'90.00');$fifoPost=$closurePost(array($fifoInvoice),$fifoAllocation,'invoice',1000);
$fifoSaved=controllerCall($receiptCi,'save_penerimaan_piutang',$fifoPost);verify($fifoSaved['status']===1,'FIFO receipt saved');
verify($service->one('SELECT sisa_saldo FROM tr_customer_deposit WHERE id=?',array($olderVoucher['id']))['sisa_saldo']==='20.00','FIFO consumed earliest active date before lower voucher ID');
$otherVoucher=$service->one('SELECT * FROM tr_customer_deposit WHERE no_referensi=?',array($sourceAgainSaved['no_surat']));
verify($db->update('tr_customer_deposit',array('nominal_terpakai'=>'10.00','sisa_saldo'=>'10.00','status'=>'TERPAKAI_SEBAGIAN'),array('id'=>$otherVoucher['id'])),'Forge internally balanced extra consumption');
verify($db->insert('tr_customer_deposit_mutasi',array('id_deposit'=>$otherVoucher['id'],'id_customer'=>$closureCustomer,'no_transaksi'=>$fifoSaved['no_surat'],'tanggal'=>'2026-10-18','jenis_mutasi'=>'OUT','nominal'=>'10.00','saldo_sebelum'=>'20.00','saldo_sesudah'=>'10.00','created_by'=>'7','created_date'=>'2026-10-18 00:00:00')),'Duplicate receipt consumption across another voucher');
$aggregateResponse=controllerCall($receiptCi,'get_customer_deposit',$context);verify($aggregateResponse['status']===0,'Aggregate OUT must equal receipt snapshot across all vouchers');
verify($db->delete('tr_customer_deposit_mutasi',array('id_deposit'=>$otherVoucher['id'],'jenis_mutasi'=>'OUT')),'Remove forged OUT');verify($db->update('tr_customer_deposit',array('nominal_terpakai'=>'0.00','sisa_saldo'=>'20.00','status'=>'AKTIF'),array('id'=>$otherVoucher['id'])),'Restore source voucher');

foreach(array($fifoSaved,$olderSaved,$sourceAgainSaved)as $saved){$h=$service->one('SELECT id FROM tr_penerimaan_piutang WHERE no_surat=?',array($saved['no_surat']));verify(controllerCall($receiptCi,'rollback_penerimaan',array('id_penerimaan'=>$h['id']))['status']===1,'FIFO source/consumer cleanup rollback');}
// Multi-document kwitansi: full settlement after batch admin and dedicated routing.
verify($db->insert('kons_tr_spk_penawaran',array('id_spk_penawaran'=>'SP-CLOSURE-KW','id_penawaran'=>'Q2','id_customer'=>'KW-CUST','nm_customer'=>'Trusted Customer','id_company'=>1,'no_spk'=>'SPK-CLOSURE')), 'Closure kwitansi SPK');
$kwids=array();
foreach(array(1,2)as $n){$req=$trustedPost;$req['id_spk_penawaran']='SP-CLOSURE-KW';$req['nilai_kwitansi']='20000.00';$createdKw=$kwService->create($req,$kwModel);verify($createdKw['status']===1,'Multi kwitansi creation '.$createdKw['msg']);$kwids[]=$service->one('SELECT id FROM tr_invoicing WHERE no_invoice=?',array($createdKw['no_kwitansi']))['id'];}
$kwAlloc=$closureAllocation(2040,'30000.00',8);$kwPost=$closurePost($kwids,$kwAlloc,'deposit');$kwPost['biaya_admin_1']='10000.00';
foreach(array('pph_actual_1','dep_1','lebih_bayar_1')as $field){$bad=$kwPost;$bad[$field]='1.00';verify(controllerCall($receiptCi,'save_kwitansi_deposit',$bad)['status']===0,'Forbidden kwitansi field '.$field);}
$bad=$kwPost;$bad['biaya_admin_1']='10000.01';verify(controllerCall($receiptCi,'save_kwitansi_deposit',$bad)['status']===0,'Kwitansi admin above batch cap rejected');
verify(controllerCall($receiptCi,'save_penerimaan_piutang',$kwPost)['status']===0,'Invoice endpoint cannot accept deposit kind override');
$before=$fingerprint();
foreach(array('receipt_header','receipt_detail','journal','invoice_update','kw_update','deposit_update','mutation_insert','receipt_allocation','commit')as $fault){$db->fault=$fault;$auditCount=count($GLOBALS['audit']);verify(controllerCall($receiptCi,'save_kwitansi_deposit',$kwPost)['status']===0&&$db->fault==='','Kwitansi fault reached '.$fault);verify($fingerprint()===$before,'Kwitansi writes fully rolled back '.$fault);verify(count($GLOBALS['audit'])===$auditCount+1,'One audit result for kwitansi failure '.$fault);}
$workers=array(workerStart(array('database'=>$database,'operation'=>'deposit_receipt','post'=>$kwPost)),workerStart(array('database'=>$database,'operation'=>'deposit_receipt','post'=>$kwPost)));
$kwRace=array();foreach($workers as $w)$kwRace[]=workerFinish($w);verify(array_sum(array_column($kwRace,'status'))===1,'Concurrent kwitansi settlement activates only once');
$kwWinner=$kwRace[0]['status']?$kwRace[0]:$kwRace[1];$kwHeader=$service->one('SELECT * FROM tr_penerimaan_piutang WHERE no_surat=?',array($kwWinner['no_surat']));
verify($receiptService->get_customer_active_deposit('KW-CUST',$kwOwner)===4000000,'Admin does not reduce face-value deposit activation');
$kwPage=$receiptModel->get_report_penerimaan_serverside('', '', '', $kwWinner['no_surat'],0,10,1,'desc','deposit','KW-CUST');verify($kwPage['recordsFiltered']===1&&count($kwPage['receipts'][0]['details'])===2,'Deposit report supports multi-document header');
$kwAnchor=$service->one("SELECT id FROM tr_jurnal WHERE no_transaksi=? AND jenis_transaksi='Penerimaan Piutang' ORDER BY id LIMIT 1",array($kwWinner['no_surat']));
$kwPosting=new Consulting_posting($receiptCi);$beforeGl=$service->one('SELECT COUNT(*) n FROM jurnal');$db->fault='commit';verify($kwPosting->receipt($kwAnchor['id'])['status']===0&&$db->fault==='','Kwitansi payment GL commit failure');verify($service->one('SELECT COUNT(*) n FROM jurnal')===$beforeGl,'Kwitansi GL failure leaves no partial ledger');
$glPosted=$kwPosting->receipt($kwAnchor['id']);verify($glPosted['status']===1,'Kwitansi payment posts native BUM');verify($kwPosting->receipt($kwAnchor['id'])['status']===0,'Kwitansi BUM cannot post twice');verify(controllerCall($receiptCi,'rollback_penerimaan',array('id_penerimaan'=>$kwHeader['id']))['status']===0,'Posted kwitansi payment cannot rollback');
$GLOBALS['test_ci']=$ci;
