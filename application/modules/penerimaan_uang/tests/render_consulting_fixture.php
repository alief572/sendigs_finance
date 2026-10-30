<?php
ob_start(); require __DIR__ . '/concept_fixture.php'; ob_end_clean();
if (($argv[1] ?? '') === '--add') {
    function base_url($path='') { return '/'.$path; }
    $results=array('receipt_kind'=>$argv[2]??'invoice','id_alokasi'=>100,'nominal_penerimaan_bank'=>'100.00', 'list_customer'=>array(array('id_customer'=>'C-ID','nm_customer'=>'Same Name')));
    ob_start();include dirname(__DIR__).'/views/add.php';$html=ob_get_clean();echo json_encode(array('html'=>$html));exit;
}
list($db,$engine,$preview,$post)=contractFixture();
if (($argv[1] ?? '') === '--save') {
    $input=json_decode(stream_get_contents(STDIN),true);
    $result=$engine->save($input);
    echo json_encode(array('result'=>$result,'headers'=>$db->receipts,'details'=>$db->receipt_details,'journals'=>$db->journals));
    exit;
}
if (($argv[1] ?? '') === '--fifo') {
    $db->invoices[]=makeInvoice(2,'NEWER','KB',10000,10000,0,0,10000,10000);
    $db->invoices[0]=makeInvoice(1,'OLDER','KB',10000,10000,0,0,10000,10000);
    $db->invoices[0]['tanggal_invoice']='2026-10-01';$db->invoices[1]['tanggal_invoice']='2026-10-02';
    $db->customer_deposits[]=array('id'=>1,'id_customer'=>'CUST-001','id_company'=>'1','tanggal'=>'2026-10-01','tanggal_aktif'=>'2026-10-01','nominal_awal'=>'150.00','nominal_terpakai'=>'0.00','sisa_saldo'=>'150.00','status'=>'AKTIF');
    seedDepositEvidence($db);
    $db->alloc_splits[0]['nominal']='50.00';$db->alloc_details[0]['nominal_debit']='50.00';
    $preview=$engine->preview(array('choose_inv'=>array(2,1),'id_alokasi'=>100,'pph23_dipotong'=>'N','deposit_choice'=>'Y','deposit_amount'=>'150.00'));
}
if (($argv[1] ?? '') === '--kwitansi') {
    $db->invoices=array(makeInvoice(1,'KW-TEST','DEP',2000000,0,0,0,2000000,2000000));
    $db->alloc_splits[0]['jenis_alokasi']=8;$db->alloc_splits[0]['nominal']='15000.00';$db->alloc_details[0]['nominal_debit']='15000.00';
    $preview=$engine->preview(array('choose_inv'=>array(1),'id_alokasi'=>100,'receipt_kind'=>'deposit','pph23_dipotong'=>'N'));
}
extract($preview);
ob_start(); include dirname(__DIR__) . '/views/consulting_process.php'; $html=ob_get_clean();
echo json_encode(array('html'=>$html));
