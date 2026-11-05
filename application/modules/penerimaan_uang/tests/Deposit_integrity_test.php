<?php
require __DIR__ . '/concept_fixture.php';
list($db,$engine,$preview,$post)=contractFixture();
$second=$db->invoices[0];$second['id']=2;$second['tanggal_invoice']='2026-10-28';$db->invoices[]=$second;
$db->invoices[0]['tanggal_invoice']='2026-10-01';
$p=$engine->preview(array('choose_inv'=>array(2,1),'id_alokasi'=>100,'pph23_dipotong'=>'N'));
checkAssert(array_column(array_column($p['rows'],'invoice'),'id')===array(1,2),'Invoice order must follow date then ID');
$db->invoices[1]['tanggal_invoice']='2026-10-01';
$p=$engine->preview(array('choose_inv'=>array(2,1),'id_alokasi'=>100,'pph23_dipotong'=>'N'));
checkAssert(array_column(array_column($p['rows'],'invoice'),'id')===array(1,2),'Equal invoice dates must use ID as tie breaker');
$db->customer_deposits[]=array('id'=>91,'id_customer'=>$db->invoices[0]['id_customer'],'id_company'=>$preview['rows'][0]['owner']['id'],'status'=>'AKTIF','tanggal_aktif'=>'2026-10-01','tanggal'=>'2026-10-01','nominal_awal'=>'100.00','nominal_terpakai'=>'0.00','sisa_saldo'=>'100.00','tipe_sumber'=>'KWITANSI_DEPOSIT','no_referensi'=>'MISSING');
$blocked=false;try{$engine->get_customer_active_deposit($db->invoices[0]['id_customer'],$preview['rows'][0]['owner']['id']);}catch(RuntimeException $e){$blocked=strpos($e->getMessage(),'rekonsiliasi')!==false;}
checkAssert($blocked,'Deposit without source and mutations must be blocked');
$p=$engine->preview(array('choose_inv'=>array(1),'id_alokasi'=>100,'pph23_dipotong'=>'N'));
checkAssert(!empty($p['deposit_error']),'Cash preview must survive unusable deposit history and expose warning');
echo "PASS: invoice ordering and invalid deposit quarantine\n";

foreach (array('LEBIH_BAYAR','KWITANSI_DEPOSIT') as $type) {
    list($db,$engine,$preview,$post)=contractFixture();
    $db->customer_deposits[]=array('id'=>91,'id_customer'=>$db->invoices[0]['id_customer'],'id_company'=>$preview['rows'][0]['owner']['id'],'status'=>'AKTIF','tanggal_aktif'=>'2026-10-01','tanggal'=>'2026-09-01','nominal_awal'=>'100.00','nominal_terpakai'=>'0.00','sisa_saldo'=>'100.00','tipe_sumber'=>$type,'no_referensi'=>'KW-SOURCE');
    seedDepositEvidence($db);
    checkAssert($engine->get_customer_active_deposit('CUST-001','1')===10000,'Valid source is available '.$type);
    $base=serialize(array($db->customer_deposits,$db->deposit_mutasi,$db->receipts,$db->receipt_details,$db->kwitansi_deposits,$db->invoices));
    foreach (array('no_source','duplicate_source','wrong_company','wrong_customer','wrong_amount','missing_in','wrong_chain','wrong_mutation_customer','invalid_date','wrong_status','broken_snapshot','negative_balance','false_exhausted','duplicate_voucher') as $fault) {
        list($db->customer_deposits,$db->deposit_mutasi,$db->receipts,$db->receipt_details,$db->kwitansi_deposits,$db->invoices)=unserialize($base);
        if($fault==='no_source')$db->receipts=array();
        if($fault==='duplicate_source')$db->receipts[]=$db->receipts[0];
        if($fault==='wrong_company'){$snap=json_decode($db->receipt_details[0]['consulting_snapshot'],true);$snap['owner']['id']='OTHER';$db->receipt_details[0]['consulting_snapshot']=json_encode($snap);}
        if($fault==='wrong_customer')$db->receipts[0]['id_customer']='OTHER';
        if($fault==='wrong_amount')$db->deposit_mutasi[0]['nominal']='99.00';
        if($fault==='missing_in')$db->deposit_mutasi=array();
        if($fault==='wrong_chain')$db->deposit_mutasi[0]['saldo_sebelum']='1.00';
        if($fault==='wrong_mutation_customer')$db->deposit_mutasi[0]['id_customer']='OTHER';
        if($fault==='invalid_date')$db->customer_deposits[0]['tanggal_aktif']='2026-02-30';
        if($fault==='wrong_status')$db->customer_deposits[0]['status']='TERPAKAI_SEBAGIAN';
        if($fault==='duplicate_voucher'){$copy=$db->customer_deposits[0];$copy['id']=92;$db->customer_deposits[]=$copy;}
        if($fault==='negative_balance')$db->customer_deposits[0]['sisa_saldo']='-1.00';
        if($fault==='false_exhausted')$db->customer_deposits[0]['status']='LUNAS_TERPAKAI';
        if($fault==='broken_snapshot')$db->receipt_details[0]['consulting_snapshot']='{}';
        $blocked=false;try{$engine->get_customer_active_deposit('CUST-001','1');}catch(RuntimeException $e){$blocked=true;}
        checkAssert($blocked,'Blocked '.$fault.' '.$type);
    }
}
echo "PASS: valid kwitansi/overpayment sources and 28 integrity fault branches\n";
