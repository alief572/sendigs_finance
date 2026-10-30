<?php
// Loaded by the disposable MariaDB integration runner, never against application credentials.
$GLOBALS['test_ci']=$ci;
newSource($db,94);
$created=controllerCall($ci,'save_invoice',invoiceRequest($service,94));
verify($created['status']===1,'Concept failure fixture invoice');
$conceptInvoiceId=$created['id'];
$auditSql=file_get_contents(dirname(__DIR__).'/tools/audit_deposit_history.sql');
$auditQueries=array_filter(array_map('trim',explode(';',preg_replace('/^--.*$/m','',$auditSql))));
verify(count($auditQueries)===4, 'Deposit history audit has four read-only checks');
foreach($auditQueries as $query) verify($db->query($query)!==false, 'Deposit history audit executes on disposable database');
$queries=array_values($auditQueries);
verify($db->insert('tr_kwitansi_deposit',array('no_kwitansi'=>'AUDIT-KW','tanggal_kwitansi'=>'2026-10-18','id_customer'=>'AUDIT-C','nm_customer'=>'Fixture','id_company'=>'1','po_customer'=>'PO','nilai_kwitansi'=>'10.00','status'=>'LUNAS','created_by'=>'7','created_date'=>'2026-10-18 00:00:00')), 'Create inconsistent audit kwitansi');
verify($db->insert('tr_customer_deposit',array('no_deposit'=>'AUDIT-DEP','id_customer'=>'AUDIT-C','nm_customer'=>'Fixture','id_company'=>'1','tipe_sumber'=>'KWITANSI_DEPOSIT','no_referensi'=>'MISSING-KW','tanggal'=>'2026-10-18','nominal_awal'=>'10.00','nominal_terpakai'=>'0.00','sisa_saldo'=>'10.00','status'=>'AKTIF','created_by'=>'7','created_date'=>'2026-10-18 00:00:00')), 'Create inconsistent audit deposit');
foreach($queries as $index=>$query) verify($db->query($query)->num_rows()>0, 'History audit detects anomaly '.($index+1));
verify($db->delete('tr_customer_deposit',array('no_deposit'=>'AUDIT-DEP')),'Remove inconsistent audit deposit');
verify($db->delete('tr_kwitansi_deposit',array('no_kwitansi'=>'AUDIT-KW')),'Remove inconsistent audit kwitansi');
$kwModel=(new ReflectionClass('Kwitansi_deposit_model'))->newInstanceWithoutConstructor();
$kwModel->db=$db;
$kwBefore=array();
foreach(array('tr_kwitansi_deposit','tr_customer_deposit','tr_invoicing','tr_jurnal') as $table) $kwBefore[$table]=(int)$service->one('SELECT COUNT(*) n FROM '.$table)['n'];
$db->fault='commit';
$kwFailed=false;
try { $kwModel->save_kwitansi(array('id_customer'=>'KW-CUST','nm_customer'=>'Fixture','tanggal_kwitansi'=>'2026-10-18','id_company'=>'1','po_customer'=>'PO-1','nilai_kwitansi'=>'100.00','user_id'=>'7')); }
catch (RuntimeException $error) { $kwFailed=strpos($error->getMessage(), 'Commit')!==false; if (!$kwFailed) throw new RuntimeException($error->getMessage().' DB: '.json_encode($db->error())); }
verify($kwFailed && $db->fault==='', 'Kwitansi commit failure reported');
foreach($kwBefore as $table=>$count) verify((int)$service->one('SELECT COUNT(*) n FROM '.$table)['n']===$count, 'Kwitansi commit failure rolled back '.$table);
$kwCreated=$kwModel->save_kwitansi(array('id_customer'=>'KW-CUST','nm_customer'=>'Fixture','tanggal_kwitansi'=>'2026-10-18','id_company'=>'1','po_customer'=>'PO-1','nilai_kwitansi'=>'100.00','user_id'=>'7'));
verify($kwCreated['status']===1,'Kwitansi creation commits on actual schema');
$kwInvoice=$service->one('SELECT * FROM tr_invoicing WHERE no_invoice=?',array($kwCreated['no_kwitansi']));
verify($kwInvoice && $kwInvoice['tax_treatment']==='DEP' && $kwInvoice['saldo_piutang']==='100.00','Kwitansi invoice persisted as DEP');
verify((int)$service->one("SELECT COUNT(*) n FROM tr_jurnal WHERE no_transaksi=? AND jenis_transaksi='Kwitansi Deposit'",array($kwCreated['no_kwitansi']))['n']===2,'Kwitansi accrual journals persisted');
verify($db->delete('tr_jurnal',array('no_transaksi'=>$kwCreated['no_kwitansi'],'jenis_transaksi'=>'Kwitansi Deposit')),'Remove test accrual journals');
verify($db->delete('tr_invoicing',array('id'=>$kwInvoice['id'])),'Remove test kwitansi invoice');
verify($db->delete('tr_customer_deposit',array('no_referensi'=>$kwCreated['no_kwitansi'])),'Remove test kwitansi deposit');
verify($db->delete('tr_kwitansi_deposit',array('no_kwitansi'=>$kwCreated['no_kwitansi'])),'Remove test kwitansi');
verify($db->insert('kons_tr_spk_penawaran',array('id_spk_penawaran'=>'SP-KW','id_penawaran'=>'Q2','id_customer'=>'KW-CUST','nm_customer'=>'Trusted Customer','id_company'=>null,'no_spk'=>'SPK-KW-001')), 'Create kwitansi SPK');
$kwService=new Kwitansi_deposit_service($ci);
$trustedPost=array('id_spk_penawaran'=>'SP-KW','id_penawaran'=>'FORGED','id_customer'=>'KW-CUST','nm_customer'=>'Tampered','id_company'=>'4','tanggal_kwitansi'=>'2026-10-18','nilai_kwitansi'=>'100.00');
$beforeKwCount=$service->one('SELECT COUNT(*) n FROM tr_kwitansi_deposit')['n'];
foreach(array('missing_relation','missing_quote','missing_company','unknown_company') as $companyFault){
    $db->update('kons_tr_spk_penawaran',array('id_penawaran'=>$companyFault==='missing_relation'?null:($companyFault==='missing_quote'?'NO-QUOTE':'Q2')),array('id_spk_penawaran'=>'SP-KW'));
    $db->update('kons_tr_penawaran',array('company'=>$companyFault==='missing_company'?null:($companyFault==='unknown_company'?999:1)),array('id_quotation'=>'Q2'));
    verify($kwService->create($trustedPost,$kwModel)['status']===0,'Reject incomplete penawaran company '.$companyFault);
    verify($service->one('SELECT COUNT(*) n FROM tr_kwitansi_deposit')['n']===$beforeKwCount,'No partial kwitansi '.$companyFault);
}
$db->update('kons_tr_spk_penawaran',array('id_penawaran'=>'Q2'),array('id_spk_penawaran'=>'SP-KW'));
$db->update('kons_tr_penawaran',array('company'=>1),array('id_quotation'=>'Q2'));
$trustedKw=$kwService->create($trustedPost,$kwModel);
verify($trustedKw['status']===1,'SPK-based kwitansi creation '.$trustedKw['msg']);
$trustedRow=$service->one('SELECT * FROM tr_kwitansi_deposit WHERE no_kwitansi=?',array($trustedKw['no_kwitansi']));
$kwOwner=$service->mapping('1')['owner']['id'];
$kwSnapshot=json_decode($trustedRow['spk_snapshot'],true);
verify((string)$kwSnapshot['source_company']['id']==='1' && $kwSnapshot['quote']['id_quotation']==='Q2' && $kwSnapshot['spk']['id_company']===null,'Penawaran company overrides empty SPK and forged client quote; snapshot retained');
verify($trustedRow['nm_customer']==='Trusted Customer' && (string)$trustedRow['id_company']===(string)$kwOwner && $trustedRow['id_spk_penawaran']==='SP-KW','SPK and mapping override forged customer name and company');
$badPost=$trustedPost; $badPost['id_customer']='OTHER';
verify($kwService->create($badPost,$kwModel)['status']===0,'Foreign customer SPK rejected');
$badPost=$trustedPost; $badPost['tanggal_kwitansi']='2026-02-30';
verify($kwService->create($badPost,$kwModel)['status']===0,'Invalid kwitansi date rejected');
$db->fault='commit';
verify($kwService->create($trustedPost,$kwModel)['status']===0 && $db->fault==='','SPK kwitansi commit failure rejected');
verify((int)$service->one("SELECT COUNT(*) n FROM tr_kwitansi_deposit WHERE id_spk_penawaran='SP-KW'")['n']===1,'SPK kwitansi failed commit leaves no extra document');
$depInvoice=$service->one('SELECT * FROM tr_invoicing WHERE no_invoice=?',array($trustedKw['no_kwitansi']));
verify($db->insert('tr_alokasi_detail',array('id'=>880,'tipe_bank'=>1,'tanggal_transaksi'=>'2026-10-18','nominal_debit'=>'100.00','nilai_terpakai'=>'0.00')),'Deposit bank allocation fixture');
verify($db->insert('tr_alokasi_split',array('id'=>881,'id_alokasi_detail'=>880,'nominal'=>'100.00','nilai_terpakai'=>'0.00','jenis_alokasi'=>8)),'Deposit split fixture');
$depPreviewPost=array('choose_inv'=>array($depInvoice['id']),'id_alokasi'=>881,'receipt_kind'=>'deposit','pph23_dipotong'=>'N','deposit_choice'=>'N','deposit_amount'=>'0');
$depPreview=$receiptService->preview($depPreviewPost);
$depPost=array('no_inv'=>'1','id_inv_1'=>$depInvoice['id'],'id_alokasi'=>881,'receipt_kind'=>'deposit','pph23_dipotong'=>'N','deposit_choice'=>'N','deposit_amount'=>'0',
    'allocation_fingerprint'=>$depPreview['allocation_fingerprint'],'receipt_fingerprint_1'=>$depPreview['rows'][0]['fingerprint'],
    'penerimaan_1'=>'100.00','biaya_admin_1'=>'0.00','pph_actual_1'=>'0.00','sisa_piutang_1'=>'0.00','dep_1'=>'0.00','lebih_bayar_1'=>'0.00');
$badDep=$depPost;$badDep['sisa_piutang_1']='40.00';$badDep['penerimaan_1']='60.00';
verify(controllerCall($receiptCi,'save_kwitansi_deposit',$badDep)['status']===0,'Dedicated deposit endpoint rejects partial payment');
$depSaved=controllerCall($receiptCi,'save_kwitansi_deposit',$depPost);
verify($depSaved['status']===1,'Dedicated deposit endpoint saves full payment '.$depSaved['msg']);
$depHeader=$service->one('SELECT * FROM tr_penerimaan_piutang WHERE no_surat=?',array($depSaved['no_surat']));
verify($depHeader['jenis_penerimaan']==='deposit','Receipt explicitly classified as deposit');
verify($service->one('SELECT status FROM tr_kwitansi_deposit WHERE no_kwitansi=?',array($trustedKw['no_kwitansi']))['status']==='LUNAS','Deposit receipt settles kwitansi');
verify($receiptService->get_customer_active_deposit('KW-CUST',$kwOwner)===10000,'Deposit receipt activates funds');
verify(controllerCall($receiptCi,'save_kwitansi_deposit',$depPost)['status']===0,'Duplicate deposit receipt rejected');
verify(controllerCall($receiptCi,'rollback_penerimaan',array('id_penerimaan'=>$depHeader['id']))['status']===1,'Deposit receipt rollback succeeds');
verify($receiptService->get_customer_active_deposit('KW-CUST',$kwOwner)===0,'Deposit receipt rollback removes available funds');
verify($db->delete('tr_alokasi_split',array('id'=>881)) && $db->delete('tr_alokasi_detail',array('id'=>880)),'Remove deposit allocation fixtures');
$kwAnchor=$service->one("SELECT id FROM tr_jurnal WHERE no_transaksi=? AND jenis_transaksi='Kwitansi Deposit' ORDER BY id LIMIT 1",array($trustedKw['no_kwitansi']));
$kwPoster=new Consulting_posting($ci);
$glBefore=array();foreach(array('javh','jurnal','tr_kartu_piutang') as $table)$glBefore[$table]=(int)$service->one('SELECT COUNT(*) n FROM '.$table)['n'];
$db->fault='commit';
verify($kwPoster->kwitansi($kwAnchor['id'])['status']===0 && $db->fault==='','Kwitansi GL commit failure rejected');
foreach($glBefore as $table=>$count)verify((int)$service->one('SELECT COUNT(*) n FROM '.$table)['n']===$count,'Kwitansi GL commit failure rolls back '.$table);
$kwPosted=$kwPoster->kwitansi($kwAnchor['id']);
verify($kwPosted['status']===1 && !empty($kwPosted['accounting_numbers'][0]['number']),'Kwitansi accrual posts to JV '.$kwPosted['msg']);
verify($kwPoster->kwitansi($kwAnchor['id'])['status']===0,'Duplicate kwitansi accrual posting rejected');
$kwNumber=$kwPosted['accounting_numbers'][0]['number'];
verify($db->delete('javh',array('nomor'=>$kwNumber)) && $db->delete('jurnal',array('nomor'=>$kwNumber)) && $db->delete('tr_kartu_piutang',array('nomor'=>$kwNumber)),'Remove disposable kwitansi GL fixtures');
foreach(array('tr_jurnal'=>'no_transaksi','tr_invoicing'=>'no_invoice','tr_customer_deposit'=>'no_referensi','tr_kwitansi_deposit'=>'no_kwitansi') as $table=>$column) verify($db->delete($table,array($column=>$trustedKw['no_kwitansi'])),'Remove SPK kwitansi fixture '.$table);
verify($db->delete('kons_tr_spk_penawaran',array('id_spk_penawaran'=>'SP-KW')),'Remove kwitansi SPK');
$post=$receiptPost(array($conceptInvoiceId),array('10.00'),array('0.50'),array('1.00'));
$GLOBALS['test_ci']=$receiptCi;
$beforeInvoice=$service->one('SELECT * FROM tr_invoicing WHERE id=?',array($conceptInvoiceId));
$owner = json_decode($beforeInvoice['consulting_snapshot'], true)['owner']['id'];
foreach (array(array('COMPANY-ONE', (string)$owner, '100.00'), array('COMPANY-OTHER', 'other-company', '300.00')) as $fixture) {
    verify($db->insert('tr_customer_deposit', array('no_deposit'=>$fixture[0], 'id_customer'=>$beforeInvoice['id_customer'],
        'nm_customer'=>$beforeInvoice['nm_customer'], 'id_company'=>$fixture[1], 'tipe_sumber'=>'LEBIH_BAYAR',
        'no_referensi'=>$fixture[0], 'tanggal'=>'2026-10-18', 'tanggal_aktif'=>'2026-10-18', 'nominal_awal'=>$fixture[2], 'nominal_terpakai'=>'0.00',
        'sisa_saldo'=>$fixture[2], 'status'=>'AKTIF', 'created_by'=>'7', 'created_date'=>'2026-10-18 00:00:00')),'Deposit company fixture');
    $fixtureAmount=Penerimaan_calculation::cents($fixture[2]);
    $sourceSnapshot=array('owner'=>array('id'=>$fixture[1]),'lebih'=>$fixtureAmount,'cash'=>2*$fixtureAmount,'balance'=>$fixtureAmount,'reduction'=>$fixtureAmount,'sisa'=>0,'admin'=>0,'pph'=>0,'dep'=>0);
    verify($db->insert('tr_penerimaan_piutang',array('no_surat'=>$fixture[0], 'id_customer'=>$beforeInvoice['id_customer'], 'nominal_penerimaan_bank'=>Invoice_calculation::decimal(2*$fixtureAmount), 'jenis_penerimaan'=>'invoice')), 'Deposit source receipt');
    verify($db->insert('tr_penerimaan_piutang_detail',array('id_header'=>$fixture[0], 'id_inv'=>'SOURCE-'.$fixture[0], 'id_customer'=>$beforeInvoice['id_customer'], 'penerimaan'=>$fixture[2], 'biaya_admin'=>'0.00','pph23'=>'0.00','sisa_piutang'=>'0.00', 'consulting_snapshot'=>json_encode($sourceSnapshot))), 'Deposit source detail');
    $voucher=$service->one('SELECT * FROM tr_customer_deposit WHERE no_deposit=?',array($fixture[0]));
    verify($db->insert('tr_customer_deposit_mutasi',array('id_deposit'=>$voucher['id'],'id_customer'=>$voucher['id_customer'],'no_transaksi'=>$fixture[0],'tanggal'=>'2026-10-18','jenis_mutasi'=>'IN','nominal'=>$voucher['nominal_awal'],'saldo_sebelum'=>'0.00','saldo_sesudah'=>$voucher['nominal_awal'],'created_by'=>'7','created_date'=>'2026-10-18 00:00:00')),'Deposit source IN');
}
verify($receiptService->get_customer_active_deposit($beforeInvoice['id_customer'], $owner)===10000, 'Real SQL isolates customer deposits by company');
$balanceResponse=controllerCall($receiptCi,'get_customer_deposit',array('id_invoice'=>$conceptInvoiceId));
verify((float)$balanceResponse['saldo']===100.0, 'Balance endpoint uses invoice customer and company');
$monitor=(new ReflectionClass('Deposit_customer_model'))->newInstanceWithoutConstructor();$monitor->db=$db;
$summaries=$monitor->get_customer_summary();
$customerSummaries=array_values(array_filter($summaries,function($row)use($beforeInvoice){return $row['id_customer']===$beforeInvoice['id_customer'];}));
verify(count($customerSummaries)===1,'Monitoring shows one combined balance per customer across companies');
verify(count($monitor->get_customer_vouchers($beforeInvoice['id_customer'],$owner))===1,'Monitoring vouchers isolated by company');
verify(count($monitor->get_customer_vouchers($beforeInvoice['id_customer']))===0,'Monitoring never returns combined-company vouchers without company selection');
verify(count($monitor->get_customer_mutasi_ledger($beforeInvoice['id_customer'],$owner))===1,'Monitoring ledger isolated by voucher company');
verify($db->query("DELETE FROM tr_customer_deposit_mutasi WHERE no_transaksi IN ('COMPANY-ONE','COMPANY-OTHER')")!==false,'Remove monitoring source IN');
verify($db->query("DELETE FROM tr_penerimaan_piutang_detail WHERE id_header IN ('COMPANY-ONE','COMPANY-OTHER')")!==false,'Remove source detail');
verify($db->query("DELETE FROM tr_penerimaan_piutang WHERE no_surat IN ('COMPANY-ONE','COMPANY-OTHER')")!==false,'Remove source receipts');
verify($db->query("DELETE FROM tr_customer_deposit WHERE no_deposit IN ('COMPANY-ONE','COMPANY-OTHER')")!==false,'Remove test deposit fixtures');
verify($db->query("DELETE FROM coa_master WHERE no_perkiraan='2102-01-02'")!==false, 'Remove deposit account to test validation');
$missingDepositAccount = false;
try { $receiptService->preview(array('choose_inv'=>array($conceptInvoiceId),'id_alokasi'=>200,'pph23_dipotong'=>'N')); }
catch (RuntimeException $error) { $missingDepositAccount = strpos($error->getMessage(), 'COA') !== false; }
verify($missingDepositAccount, 'Missing deposit COA rejects preview without fabricated fallback');
verify($db->insert('coa_master', array('no_perkiraan'=>'2102-01-02','nama'=>'Deposit Customer')), 'Restore deposit account');
$beforeSplit=$service->one('SELECT * FROM tr_alokasi_split WHERE id=200');
$beforeParent=$service->one('SELECT * FROM tr_alokasi_detail WHERE id=100');
$beforeCounts=array();
foreach(array('tr_penerimaan_piutang','tr_penerimaan_piutang_detail','tr_jurnal')as $table)$beforeCounts[$table]=$service->one('SELECT COUNT(*) n FROM '.$table)['n'];
foreach(array('receipt_header','receipt_detail','journal','invoice_update','receipt_allocation','commit')as $fault){
    $db->fault=$fault;
    $result=controllerCall($receiptCi,'save_penerimaan_piutang',$post);
    verify($result['status']===0 && $db->fault==='','Injected receipt failure actually reached '.$fault);
    verify($service->one('SELECT * FROM tr_invoicing WHERE id=?',array($conceptInvoiceId))===$beforeInvoice,'Atomic invoice '.$fault);
    verify($service->one('SELECT * FROM tr_alokasi_split WHERE id=200')===$beforeSplit,'Atomic split '.$fault);
    verify($service->one('SELECT * FROM tr_alokasi_detail WHERE id=100')===$beforeParent,'Atomic parent '.$fault);
    foreach($beforeCounts as $table=>$count)verify($service->one('SELECT COUNT(*) n FROM '.$table)['n']===$count,'Atomic rows '.$table.' '.$fault);
}
$payload=array('database'=>$database,'operation'=>'receipt','post'=>$post);
$workers=array(workerStart($payload),workerStart($payload));
$responses=array();foreach($workers as $worker)$responses[]=workerFinish($worker);
verify(array_sum(array_column($responses,'status'))===1,'Two concurrent receipt submissions commit exactly once');
$success=$responses[0]['status']?$responses[0]:$responses[1];
$header=$service->one('SELECT * FROM tr_penerimaan_piutang WHERE no_surat=?',array($success['no_surat']));
$detail=$service->one('SELECT * FROM tr_penerimaan_piutang_detail WHERE id_header=?',array($success['no_surat']));
verify($header['nominal_penerimaan_bank']==='10.00' && $detail['penerimaan']==='11.50','Actual controller gross/net contract');
$after=$service->one('SELECT * FROM tr_invoicing WHERE id=?',array($conceptInvoiceId));
$default=Penerimaan_calculation::tax_calc(Invoice_calculation::cents($after['total_nominal_jurnal']),$after['tax_treatment'])['pph_default'];
verify($receiptService->available_pph($after,$default)===$default-100,'PPh balance from committed database history');
$db->update('tr_penerimaan_piutang_detail',array('pph23'=>'1.01'),array('id'=>$detail['id']));
try{$receiptService->available_pph($after,$default);verify(false,'Corrupt PPh history accepted');}
catch(RuntimeException $error){verify(strpos($error->getMessage(),'rekonsiliasi')!==false,'Corrupt history is actionable');}
$db->update('tr_penerimaan_piutang_detail',array('pph23'=>'1.00','sts_pph_23'=>1),array('id'=>$detail['id']));
verify(controllerCall($receiptCi,'rollback_penerimaan',array('id_penerimaan'=>$header['id']))['status']===0,'Proof-of-withholding blocks rollback');
$db->update('tr_penerimaan_piutang_detail',array('sts_pph_23'=>0),array('id'=>$detail['id']));
$db->insert('tr_penerimaan_pph_23',array('id'=>'CONCEPT-PROOF','id_detail_penerimaan'=>$detail['id']));
verify(controllerCall($receiptCi,'rollback_penerimaan',array('id_penerimaan'=>$header['id']))['status']===0,'Linked withholding proof blocks rollback even without flag');
$db->delete('tr_penerimaan_pph_23',array('id'=>'CONCEPT-PROOF'));
$GLOBALS['denied']=array('Penerimaan_Uang.Delete');
verify(controllerCall($receiptCi,'rollback_penerimaan',array('id_penerimaan'=>$header['id']))['status']===0,'Rollback permission required');
$GLOBALS['denied']=array();
$db->fault='delete_journal';
verify(controllerCall($receiptCi,'rollback_penerimaan',array('id_penerimaan'=>$header['id']))['status']===0 && $db->fault==='','Rollback journal failure reached');
verify($service->one('SELECT * FROM tr_invoicing WHERE id=?',array($conceptInvoiceId))===$after,'Failed rollback restores invoice');
verify(controllerCall($receiptCi,'rollback_penerimaan',array('id_penerimaan'=>$header['id']))['status']===1,'Rollback successful after failure');
verify($service->one('SELECT * FROM tr_invoicing WHERE id=?',array($conceptInvoiceId))===$beforeInvoice,'Rollback restores original invoice');
verify($receiptService->available_pph($beforeInvoice,$default)===$default,'Rollback restores PPh allowance');

$GLOBALS['audit_failure']=true;
$post=$receiptPost(array($conceptInvoiceId),array('10.00'),array('0.50'),array('1.00'));
$auditBefore=count($GLOBALS['audit']);
verify(controllerCall($receiptCi,'save_penerimaan_piutang',$post)['status']===1,'Audit writer failure preserves committed receipt');
$GLOBALS['audit_failure']=false;
verify(count($GLOBALS['audit'])===$auditBefore+1,'One audit result per receipt even when writer throws');

// Exercise actual report/export queries and rendered receipt detail with snapshot amounts.
verify($db->query("ALTER TABLE ms_bank ADD nama VARCHAR(100), ADD rekening VARCHAR(100)")!==false,'Report bank columns');
verify($db->query("UPDATE ms_bank SET nama='Bank STM', rekening='1234'")!==false,'Report bank metadata');
verify($db->query('CREATE TABLE list_bank(id INT PRIMARY KEY,nama_bank VARCHAR(100)) ENGINE=InnoDB')!==false,'Report bank fixture');
$lastHeader=$service->one('SELECT * FROM tr_penerimaan_piutang WHERE id_alokasi=200 ORDER BY id DESC LIMIT 1');
$lastDetail=$service->one('SELECT * FROM tr_penerimaan_piutang_detail WHERE id_header=?',array($lastHeader['no_surat']));
$receiptSnapshot=json_decode($lastDetail['consulting_snapshot'],true);
$db->update('tr_invoicing',array('total_akhir_jurnal'=>'9999.00'),array('id'=>$conceptInvoiceId));
$report=$receiptModel->get_report_data('', '', '', $beforeInvoice['no_invoice']);
verify(count($report)===1 && $report[0]['nominal_penerimaan_bank']==='10.00','Export bank receipt counted once');
verify($report[0]['details'][0]['penerimaan']==='11.50' && $report[0]['details'][0]['total_akhir_jurnal']===Invoice_calculation::decimal($receiptSnapshot['balance']),'Export uses historical snapshot despite invoice change');
$html=$renderer->render(dirname(__DIR__).'/views/export_excel.php',array('report_data'=>$report,'filter_info'=>'Concept test'));
verify(strpos($html,'11.5')!==false && strpos($html,$beforeInvoice['no_invoice'])!==false,'Actual export HTML rendered');
$page=$receiptModel->get_report_penerimaan_serverside('', '', '', $beforeInvoice['no_invoice'],0,10,1,'desc');
verify($page['recordsFiltered']===1 && count($page['receipts'][0]['details'])===1,'Actual paginated report query');
verify($page['receipts'][0]['details'][0]['total_akhir_jurnal']===Invoice_calculation::decimal($receiptSnapshot['balance']),'Paged report preserves historical balance');
$filteredPage=$receiptModel->get_report_penerimaan_serverside('', '', '', $beforeInvoice['no_invoice'],0,10,1,'desc','invoice',$beforeInvoice['id_customer']);
verify($filteredPage['recordsFiltered']===1,'Report filters invoice kind and customer');
verify($receiptModel->get_report_penerimaan_serverside('', '', '', $beforeInvoice['no_invoice'],0,10,1,'desc','deposit',$beforeInvoice['id_customer'])['recordsFiltered']===0,'Report deposit filter excludes ordinary invoice');
verify(count($receiptModel->get_report_data('', '', '', $beforeInvoice['no_invoice'],'invoice',$beforeInvoice['id_customer']))===1,'Export filters invoice kind and customer');
verify(count($receiptModel->get_report_data('', '', '', $beforeInvoice['no_invoice'],'deposit',$beforeInvoice['id_customer']))===0,'Export deposit filter excludes ordinary invoice');
$reportCompany=array('7'=>'STM','3'=>'SUSTAIN','4'=>'VUCA')[(string)$receiptSnapshot['owner']['id']];
verify($receiptModel->get_report_penerimaan_serverside('', '', $reportCompany, $beforeInvoice['no_invoice'],0,10,1,'desc')['recordsFiltered']===1,'Report company follows snapshot rather than bank account name');
verify($db->update('tr_penerimaan_piutang',array('jenis_penerimaan'=>null),array('id'=>$lastHeader['id'])),'Clear kind to simulate historical receipt');
verify($receiptModel->get_report_penerimaan_serverside('', '', '', $beforeInvoice['no_invoice'],0,10,1,'desc','invoice')['recordsFiltered']===1,'Historical ordinary receipt inferred without rewriting it');
verify($db->update('tr_penerimaan_piutang',array('jenis_penerimaan'=>'invoice'),array('id'=>$lastHeader['id'])),'Restore fixture receipt kind');
$html=$renderer->render(dirname(__DIR__).'/views/detail.php',array('header'=>$lastHeader,'detail'=>array_merge(array($lastDetail+array('no_invoice'=>$beforeInvoice['no_invoice'],'total_akhir_jurnal'=>'9999.00'))),'alokasi'=>array('tanggal_transaksi'=>'2020-01-01'),'list_jurnal'=>$service->rows('SELECT * FROM tr_jurnal WHERE no_transaksi=?',array($lastHeader['no_surat']))));
verify(strpos($html,'18 October 2026')!==false && strpos($html,'11.50')!==false,'Detail displays snapshot date, allocation and exact journal cents');
$db->update('tr_invoicing',array('total_akhir_jurnal'=>$beforeInvoice['total_akhir_jurnal']),array('id'=>$conceptInvoiceId));
$post=$receiptPost(array($conceptInvoiceId),array('10.00'),array('0.00'),array('0.00'));
$GLOBALS['denied']=array('Penerimaan_Uang.Add');
verify(controllerCall($receiptCi,'save_penerimaan_piutang',$post)['status']===0,'Receipt add permission required');
$GLOBALS['denied']=array();
$GLOBALS['test_ci']=$ci;
