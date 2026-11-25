<?php
// Runs only inside Consulting_integration_test.php's random, disposable database.
$GLOBALS['test_ci']=$ci;
$db->update('kons_tr_penawaran',array('company'=>'1'),array('id_quotation'=>'Q2'));
verify($db->query('ALTER TABLE kons_tr_penawaran_non_konsultasi ADD id_customer VARCHAR(100), ADD nm_customer TEXT, ADD address TEXT, ADD biaya_kirim DECIMAL(20,2) DEFAULT 0, ADD sts_close VARCHAR(1) DEFAULT 0')!==false,'Non consulting quote schema');
verify($db->query('CREATE TABLE kons_tr_detail_penawaran_non_konsultasi (id INT AUTO_INCREMENT PRIMARY KEY,id_header VARCHAR(100),nm_item TEXT,qty DECIMAL(20,2),harga DECIMAL(20,2),total DECIMAL(20,2)) ENGINE=InnoDB')!==false,'Non consulting source details');
$db->update('ms_invoice_coa_mapping',array('owner_company_id'=>'7','accounts'=>json_encode($codes)),array('source_company_id'=>'1'));
$nonSource=function($n,$limit='10000.00')use($db){
    $key='NC-'.$n;
    verify($db->insert('kons_tr_penawaran_non_konsultasi',array('id_penawaran'=>$key,'grand_total'=>$limit,'id_company'=>'1','id_customer'=>'C1','nm_customer'=>'Customer <script>','address'=>'Address','biaya_kirim'=>'0.55','keterangan_penawaran'=>'Non consulting','nm_pic_penawaran'=>'Sales')),'Quote fixture');
    verify($db->insert('kons_tr_detail_penawaran_non_konsultasi',array('id_header'=>$key,'nm_item'=>'Item','qty'=>'1.25','harga'=>'800.00','total'=>'1000.00')),'Quote detail fixture');
    return $key;
};
$nonRequest=function($key,$tax='PKP',$role='sales_consulting')use($service){
    $source=$service->non_consulting_source($key);$mapping=$service->mapping($source['source_company_id']);
    return array('id_penawaran'=>$key,'tanggal_invoice'=>'2026-11-23','nomor_po'=>'PO-NC','nomor_faktur'=>'FP-NC','tax_treatment'=>$tax,'sales_role'=>$role,
        'source_fingerprint'=>Invoice_calculation::fingerprint(array('source'=>$source,'mapping'=>$mapping)),
        'item'=>array(array('nama'=>'Item <script>','qty'=>'1.25','harga'=>'800.00')),'biaya_kirim'=>'999999','total_akhir_jurnal'=>'1','id_customer'=>'WRONG','id_company'=>'WRONG','jurnal_invoice_no_coa'=>'9999-99-99');
};
$nonIds=array();
foreach(array('PKP','WAPU','KB','UMKM') as $tax) foreach(array_keys(Invoice_consulting::revenues()) as $role){
    $key=$nonSource($tax.'-'.$role);$post=$nonRequest($key,$tax,$role);$auditBefore=count($GLOBALS['audit']);
    $r=controllerCall($ci,'save_invoice_non_konsultasi',$post);verify($r['status']===1,'Non consulting create '.$tax.'/'.$role.': '.$r['msg']);
    verify(count($GLOBALS['audit'])===$auditBefore+1,'One non consulting create audit');
    $inv=$service->one('SELECT * FROM tr_invoicing WHERE id=?',array($r['id']));$nonIds[$tax][$role]=$inv['id'];
    verify(Invoice_consulting::is_non_consulting($inv) && !Invoice_consulting::is_new($inv) && Invoice_consulting::is_snapshot_invoice($inv),'Kinds stay distinct');
    verify($inv['consulting_top_key']===null && $inv['id_detail_plan_tagih']==0 && $inv['id_spk_penawaran']==='','No fake TOP or SPK');
    verify($inv['id_customer']==='C1' && $inv['consulting_company_id']==='7','Customer and owner from server');
    verify($inv['total_nominal']==='1000.55' && $inv['biaya_kirim']==='0.55' && $inv['pph_jurnal']==0,'Item cents, server shipping, no invoice PPh');
    verify($inv['saldo_piutang']===($tax==='PKP'?'1110.61':'1000.55'),'Modern initial balance '.$tax);
    $snap=Invoice_consulting::snapshot($inv);verify($snap['sales_role']===$role && $snap['invoice_kind']==='non_consulting','Non consulting snapshot');
    $lines=$service->rows("SELECT * FROM tr_jurnal WHERE no_transaksi=? AND jenis_transaksi='Invoicing' ORDER BY id",array($inv['id']));
    verify(array_sum(array_column($lines,'debit'))===array_sum(array_column($lines,'kredit')),'Non consulting journal balanced');
    verify(strpos($lines[0]['keterangan'],$key)!==false,'Journal references quote');
    verify(controllerCall($ci,'save_invoice_non_konsultasi',$post)['status']===0,'Double submit rejected by source fingerprint');
    $ci->edit_invoice_non_kons($inv['id']);verify(empty($ci->template->data['error']) && $ci->template->data['non_consulting'],'Modern edit form dispatch');
    $edit=$post+array('id'=>$inv['id'],'invoice_fingerprint'=>$ci->template->data['invoice_fingerprint']);$edit['nomor_po']='PO-EDIT';
    verify(controllerCall($ci,'update_invoice_non_konsultasi',$edit)['status']===1,'Modern non consulting edit');
    verify(controllerCall($ci,'update_invoice_non_konsultasi',$edit)['status']===0,'Stale non consulting edit rejected');
}
$key=$nonSource('LIMIT','1000.55');$valid=$nonRequest($key);
$bad=$valid;$bad['item'][0]['harga']='800.01';verify(controllerCall($ci,'save_invoice_non_konsultasi',$bad)['status']===0,'Over outstanding rejected');
verify(controllerCall($ci,'save_invoice_non_konsultasi',$valid)['status']===1,'Exact outstanding accepted');
verify(controllerCall($ci,'save_invoice_non_konsultasi',$nonRequest($key))['status']===0,'Exhausted quote rejected');
$key=$nonSource('FAIL');$valid=$nonRequest($key);
foreach(array('header','non_detail','journal','sequence','commit') as $fault){
    $before=$service->rows('SELECT id,saldo_piutang FROM tr_invoicing ORDER BY id');$beforeJournal=$service->one('SELECT COUNT(*) n FROM tr_jurnal');$beforeDetails=$service->one('SELECT COUNT(*) n FROM tr_invoice_detail_non_kons');$beforeSequence=$service->rows('SELECT * FROM ms_invoice_sequence ORDER BY id');
    $db->fault=$fault;$auditBefore=count($GLOBALS['audit']);
    verify(controllerCall($ci,'save_invoice_non_konsultasi',$valid)['status']===0 && $db->fault==='','Injected non consulting failure '.$fault);
    verify($service->rows('SELECT id,saldo_piutang FROM tr_invoicing ORDER BY id')===$before && $service->one('SELECT COUNT(*) n FROM tr_jurnal')===$beforeJournal && $service->one('SELECT COUNT(*) n FROM tr_invoice_detail_non_kons')===$beforeDetails && $service->rows('SELECT * FROM ms_invoice_sequence ORDER BY id')===$beforeSequence,'Full non consulting rollback '.$fault);
    verify(count($GLOBALS['audit'])===$auditBefore+1 && end($GLOBALS['audit'])[1]===0,'One failed audit '.$fault);
}
foreach(array('tax_treatment'=>'BAD','sales_role'=>'BAD','nomor_po'=>'','nomor_faktur'=>'','tanggal_invoice'=>'2026-02-30') as $field=>$value){$bad=$valid;$bad[$field]=$value;verify(controllerCall($ci,'save_invoice_non_konsultasi',$bad)['status']===0,'Invalid non consulting '.$field);}
$GLOBALS['denied']=array('Invoicing.Add');verify(controllerCall($ci,'save_invoice_non_konsultasi',$valid)['status']===0,'Non consulting create permission');$GLOBALS['denied']=array();
$GLOBALS['audit_failure']=true;$r=controllerCall($ci,'save_invoice_non_konsultasi',$valid);$GLOBALS['audit_failure']=false;verify($r['status']===1,'Audit outage preserves committed non consulting invoice');
$partial=controllerCall($ci,'save_invoice_non_konsultasi',$nonRequest($key));verify($partial['status']===1,'Multiple partial invoices have no TOP unique-key collision');
$id=$nonIds['PKP']['sales_consulting'];$inv=$service->one('SELECT * FROM tr_invoicing WHERE id=?',array($id));
$ci->edit_invoice_non_kons($id);$edit=$nonRequest($inv['id_penawaran'])+array('id'=>$id,'invoice_fingerprint'=>$ci->template->data['invoice_fingerprint']);
$db->update('tr_jurnal',array('sts'=>'1'),array('no_transaksi'=>$id));verify(controllerCall($ci,'update_invoice_non_konsultasi',$edit)['status']===0,'Posted non consulting edit blocked');$db->update('tr_jurnal',array('sts'=>'0'),array('no_transaksi'=>$id));
$GLOBALS['test_ci']=$receiptCi;
$post=$receiptPost(array($id),array('100.00'),array('0.50'),array('2.00'));
$r=controllerCall($receiptCi,'save_penerimaan_piutang',$post);verify($r['status']===1,'Modern non consulting receipt: '.$r['msg']);
verify($service->one('SELECT saldo_piutang FROM tr_invoicing WHERE id=?',array($id))['saldo_piutang']==='1008.11','Receipt reduces non consulting balance');
$GLOBALS['test_ci']=$ci;verify(controllerCall($ci,'update_invoice_non_konsultasi',$edit)['status']===0,'Receipt blocks non consulting edit');
$GLOBALS['test_ci']=$receiptCi;$header=$service->one('SELECT * FROM tr_penerimaan_piutang WHERE no_surat=?',array($r['no_penerimaan']??$r['no_surat']??''));
if (!$header) $header=$service->one('SELECT h.* FROM tr_penerimaan_piutang h JOIN tr_penerimaan_piutang_detail d ON d.id_header=h.no_surat WHERE d.id_inv=? ORDER BY h.id DESC LIMIT 1',array($id));
verify(controllerCall($receiptCi,'rollback_penerimaan',array('id_penerimaan'=>$header['id']))['status']===1,'Non consulting receipt rollback');
verify($service->one('SELECT saldo_piutang FROM tr_invoicing WHERE id=?',array($id))['saldo_piutang']==='1110.61','Rollback restores initial balance');
$GLOBALS['test_ci']=$ci;
$journal=$service->one("SELECT id FROM tr_jurnal WHERE no_transaksi=? AND jenis_transaksi='Invoicing' LIMIT 1",array($id));
$posting=new Consulting_posting($ci);$posted=$posting->invoice($journal['id']);verify($posted['status']===1,'Non consulting invoice posting: '.$posted['msg']);
verify($posting->invoice($journal['id'])['status']===0,'Duplicate non consulting posting rejected');
verify($service->one('SELECT COUNT(*) n FROM tr_kartu_piutang WHERE no_reff=?',array($id))['n']==1,'One receivable card entry');

foreach(array('3','4') as $owner){
    $db->update('ms_invoice_coa_mapping',array('owner_company_id'=>$owner),array('source_company_id'=>'1'));
    $key=$nonSource('OWNER-'.$owner);$r=controllerCall($ci,'save_invoice_non_konsultasi',$nonRequest($key));
    verify($r['status']===1,'Non consulting owner '.$owner);
    $inv=$service->one('SELECT * FROM tr_invoicing WHERE id=?',array($r['id']));
    verify($inv['consulting_company_id']===$owner && $inv['saldo_piutang']==='1110.61','Saved mapped owner and PKP balance '.$owner);
    verify(!Invoice_consulting::print_as_vuca($inv,$owner==='4'),'Modern non consulting keeps its item print layout');
    $journal=$service->one('SELECT id FROM tr_jurnal WHERE no_transaksi=? ORDER BY id LIMIT 1',array($inv['id']));
    verify((new Consulting_posting($ci))->invoice($journal['id'])['status']===1,'Mapped non consulting posting '.$owner);
}
$db->update('ms_invoice_coa_mapping',array('owner_company_id'=>'7'),array('source_company_id'=>'1'));
$key=$nonSource('RACE','1000.55');$post=$nonRequest($key);
$workers=array(workerStart(array('database'=>$database,'operation'=>'non_create','post'=>$post)),workerStart(array('database'=>$database,'operation'=>'non_create','post'=>$post)));
$results=array();foreach($workers as $worker)$results[]=workerFinish($worker);
verify(array_sum(array_column($results,'status'))===1,'Concurrent non consulting requests commit once');
verify($service->one('SELECT COUNT(*) n FROM tr_invoicing WHERE id_penawaran=?',array($key))['n']==1,'Concurrent source cannot overinvoice');

$id=$nonIds['WAPU']['sales_inhouse'];$inv=$service->one('SELECT * FROM tr_invoicing WHERE id=?',array($id));
$ci->edit_invoice_non_kons($id);$edit=$nonRequest($inv['id_penawaran'],'WAPU','sales_inhouse')+array('id'=>$id,'invoice_fingerprint'=>$ci->template->data['invoice_fingerprint']);
foreach(array('delete_journal','invoice_update','non_detail','journal','commit') as $fault){
    $before=$service->one('SELECT * FROM tr_invoicing WHERE id=?',array($id));$oldDetails=$service->rows('SELECT * FROM tr_invoice_detail_non_kons WHERE id_header=? ORDER BY id',array($id));$oldLines=$service->rows('SELECT * FROM tr_jurnal WHERE no_transaksi=? ORDER BY id',array($id));
    $db->fault=$fault;verify(controllerCall($ci,'update_invoice_non_konsultasi',$edit)['status']===0 && $db->fault==='','Non consulting edit failure '.$fault);
    verify($service->one('SELECT * FROM tr_invoicing WHERE id=?',array($id))===$before && $service->rows('SELECT * FROM tr_invoice_detail_non_kons WHERE id_header=? ORDER BY id',array($id))===$oldDetails && $service->rows('SELECT * FROM tr_jurnal WHERE no_transaksi=? ORDER BY id',array($id))===$oldLines,'Edit restores header, details and journal '.$fault);
}
$noteBefore=$service->one('SELECT * FROM tr_jurnal WHERE no_transaksi=? ORDER BY id LIMIT 1',array($id));
$note=controllerCall($ci,'save_keterangan_print_non_kons',array('id'=>$id,'company'=>'4','keterangan_print'=>'Print <script>'));
verify($note['status']===1 && $service->one('SELECT * FROM tr_jurnal WHERE id=?',array($noteBefore['id']))===$noteBefore,'Print note cannot reassign journal owner');
$GLOBALS['denied']=array('Invoicing.Manage');verify(controllerCall($ci,'save_keterangan_print_non_kons',array('id'=>$id,'keterangan_print'=>'Forbidden'))['status']===0,'Print note requires manage permission');$GLOBALS['denied']=array();
$bad=$nonRequest($inv['id_penawaran']);$bad['item']='bad';verify(controllerCall($ci,'save_invoice_non_konsultasi',$bad)['status']===0,'Malformed item list returns JSON error');
$bad['item']=array(array('nama'=>'Item','qty'=>array(1),'harga'=>'10'));verify(controllerCall($ci,'save_invoice_non_konsultasi',$bad)['status']===0,'Malformed item qty returns JSON error');
$db->update('tr_invoicing',array('consulting_snapshot'=>'{}'),array('id'=>$id));
verify(controllerCall($ci,'update_invoice_non_konsultasi',$edit)['status']===0,'Broken modern snapshot cannot fall back to legacy');

$ncReceipt=function($id,$allocationId,$cash,$deposit='0.00',$more='0.00')use($db,$receiptService){
    verify($db->insert('tr_alokasi_detail',array('id'=>$allocationId,'tipe_bank'=>1,'tanggal_transaksi'=>'2026-11-23','nominal_debit'=>$cash,'nilai_terpakai'=>'0.00')),'Non consulting bank allocation');
    verify($db->insert('tr_alokasi_split',array('id'=>$allocationId+1,'id_alokasi_detail'=>$allocationId,'nominal'=>$cash,'nilai_terpakai'=>'0.00','jenis_alokasi'=>1)),'Non consulting allocation split');
    $choice=$deposit==='0.00'?'N':'Y';
    $p=$receiptService->preview(array('choose_inv'=>array($id),'id_alokasi'=>$allocationId+1,'pph23_dipotong'=>'N','deposit_choice'=>$choice,'deposit_amount'=>$deposit));
    return array('no_inv'=>'1','id_inv_1'=>$id,'id_alokasi'=>$allocationId+1,'pph23_dipotong'=>'N','deposit_choice'=>$choice,'deposit_amount'=>$deposit,
        'allocation_fingerprint'=>$p['allocation_fingerprint'],'receipt_fingerprint_1'=>$p['rows'][0]['fingerprint'],'penerimaan_1'=>$p['rows'][0]['invoice']['saldo_piutang'],
        'pph_actual_1'=>'0.00','biaya_admin_1'=>'0.00','sisa_piutang_1'=>'0.00','dep_1'=>$deposit,'lebih_bayar_1'=>$more);
};
$GLOBALS['test_ci']=$receiptCi;
$sourceId=$nonIds['KB']['sales_consulting'];$useId=$nonIds['KB']['sales_inhouse'];
$sourcePost=$ncReceipt($sourceId,9100,'1010.55','0.00','10.00');
$created=controllerCall($receiptCi,'save_penerimaan_piutang',$sourcePost);verify($created['status']===1,'Non consulting overpayment creates deposit: '.$created['msg']);
$voucher=$service->one('SELECT * FROM tr_customer_deposit WHERE no_referensi=?',array($created['no_surat']));
verify($voucher && $voucher['sisa_saldo']==='10.00' && $voucher['id_customer']==='C1','Overpayment belongs to real customer');
verify($voucher['po_customer']==='PO-EDIT','Non consulting deposit keeps customer PO instead of quote reference');
$usePost=$ncReceipt($useId,9200,'990.55','10.00');
$used=controllerCall($receiptCi,'save_penerimaan_piutang',$usePost);verify($used['status']===1,'Non consulting consumes customer deposit: '.$used['msg']);
verify($service->one('SELECT sisa_saldo FROM tr_customer_deposit WHERE id=?',array($voucher['id']))['sisa_saldo']==='0.00','Deposit consumed exactly');
$sourceHeader=$service->one('SELECT id FROM tr_penerimaan_piutang WHERE no_surat=?',array($created['no_surat']));$useHeader=$service->one('SELECT id FROM tr_penerimaan_piutang WHERE no_surat=?',array($used['no_surat']));
verify(controllerCall($receiptCi,'rollback_penerimaan',array('id_penerimaan'=>$sourceHeader['id']))['status']===0,'Used overpayment cannot be rolled back');
verify(controllerCall($receiptCi,'rollback_penerimaan',array('id_penerimaan'=>$useHeader['id']))['status']===1,'Deposit usage rollback');
verify($service->one('SELECT sisa_saldo FROM tr_customer_deposit WHERE id=?',array($voucher['id']))['sisa_saldo']==='10.00','Rollback restores deposit');
verify(controllerCall($receiptCi,'rollback_penerimaan',array('id_penerimaan'=>$sourceHeader['id']))['status']===1,'Overpayment rollback after usage restored');

$GLOBALS['test_ci']=$ci;newSource($db,9500,'1000.55');$regular=controllerCall($ci,'save_invoice',invoiceRequest($service,9500));verify($regular['status']===1,'Mixed modern consultation source');
$GLOBALS['test_ci']=$receiptCi;$ids=array($regular['id'],$nonIds['PKP']['sales_public']);
$mixedPost=$receiptPost($ids,array('10.00','10.00'),array('0.00','0.00'),array('0.00','0.00'));
$mixed=controllerCall($receiptCi,'save_penerimaan_piutang',$mixedPost);verify($mixed['status']===1,'Modern consultation and non consultation share valid customer/company batch');
$mixedJournal=$service->one("SELECT id FROM tr_jurnal WHERE no_transaksi=? AND jenis_transaksi='Penerimaan Piutang' LIMIT 1",array($mixed['no_surat']));
verify((new Consulting_posting($receiptCi))->receipt($mixedJournal['id'])['status']===1,'Mixed receipt posting validates both snapshots');
$mixedHeader=$service->one('SELECT id FROM tr_penerimaan_piutang WHERE no_surat=?',array($mixed['no_surat']));
verify(controllerCall($receiptCi,'rollback_penerimaan',array('id_penerimaan'=>$mixedHeader['id']))['status']===0,'Posted mixed receipt rollback refused');
$GLOBALS['test_ci']=$ci;

$db->update('ms_invoice_coa_mapping',array('owner_company_id'=>'4'),array('source_company_id'=>'1'));
$draft=controllerCall($ci,'save_invoice_non_konsultasi',$nonRequest($nonSource('OWNER-QUERY')));
verify($draft['status']===1,'Pending mapped-owner query fixture');
$db->update('ms_invoice_coa_mapping',array('owner_company_id'=>'7'),array('source_company_id'=>'1'));
if(!defined('DBHRIS'))define('DBHRIS',$database);
foreach(array('ALTER TABLE kons_tr_penawaran ADD id_divisi VARCHAR(100)','ALTER TABLE kons_tr_penawaran_non_konsultasi ADD id_divisi VARCHAR(100), ADD pic VARCHAR(100)',
    'CREATE TABLE hris_divisions(id VARCHAR(100),name VARCHAR(100))','CREATE TABLE departments(id VARCHAR(100),name VARCHAR(100))') as $sql)verify($db->query($sql)!==false,'Journal company query schema');
require_once dirname(__DIR__,2).'/report_jurnal_invoicing/models/Report_jurnal_invoicing_model.php';
foreach(array('Jurnal_invoicing_model'=>'base_query_jurnal','Report_jurnal_invoicing_model'=>'_base_query_report') as $class=>$method){
    $queryModel=(new ReflectionClass($class))->newInstanceWithoutConstructor();$queryModel->db=$db;
    $build=new ReflectionMethod($class,$method);if (PHP_VERSION_ID < 80100) { $build->setAccessible(true); }
    foreach(array('STM','VUCA') as $company){
        $build->invoke($queryModel,array('company'=>$company));$query=$db->get();verify($query!==false,'Actual company SQL '.$class.': '.json_encode($db->error()).' '.$db->last_query());
        $rows=array_values(array_filter($query->result_array(),function($row){return strpos($row['id_penawaran_non_kons']??'','NC-OWNER')===0;}));
        verify($company==='STM'?!$rows:count($rows)>0,'Source company cannot override modern owner '.$class.'/'.$company);
        foreach($rows as $row)verify((string)$row['id_company']==='4','Saved owner in journal result');
    }
}

$list=controllerCall($ci,'get_data_quotation_non_konsultasi',array('draw'=>1,'start'=>0,'length'=>250));
$draftInvoice=$service->one('SELECT * FROM tr_invoicing WHERE id=?',array($draft['id']));
$listRows=array_values(array_filter($list['data'],function($row)use($draftInvoice){return $row['no_invoice']===$draftInvoice['no_invoice'];}));
verify(count($listRows)===1 && $listRows[0]['company']==='Vuca','Non consulting list reads saved owner with real SQL');
verify(strpos($listRows[0]['action'],'print_invoice_non_kons/'.$draft['id'])!==false && strpos($listRows[0]['action'],'pilih_print_inv_non_kons')===false,'Modern list print skips mutable company modal');
