<?php
require __DIR__ . '/concept_fixture.php';
list($db,$engine,$preview,$post)=contractFixture();
$res=$engine->save($post);
checkAssert($res['status']===1, 'Rendered gross allocation must save: '.json_encode($res));
checkAssert($db->receipts[0]['nominal_penerimaan_bank']==='218330833.33', 'Bank net differs from gross allocation');
checkAssert($db->receipt_details[0]['penerimaan']==='222000000.00', 'Gross allocation stored');
$snap=json_decode($db->receipt_details[0]['consulting_snapshot'],true);
checkAssert($snap['balance']===22200000000 && $snap['cash']===21833083333 && $snap['date']==='2026-10-26', 'Authoritative receipt snapshot');
$display=Penerimaan_calculation::receipt_detail(array_merge($db->receipt_details[0], array('no_invoice'=>'MODIFIED', 'total_akhir_jurnal'=>'1.00', 'tax_treatment'=>'OTHER')));
checkAssert($display['no_invoice']==='CONCEPT-1' && $display['total_akhir_jurnal']==='222000000.00' && $display['tax_treatment']==='PKP', 'Historical report values come from snapshot');
$originalJournal=$db->journals[0];$db->journals[0]['debit']='0.00';
rejected($engine->rollback($db->receipts[0]['id']), 'Tampered journals block rollback');
$db->journals[0]=$originalJournal;
checkAssert($engine->rollback($db->receipts[0]['id'])['status']===1, 'Rollback succeeds');
checkAssert($db->invoices[0]['saldo_piutang']==='222000000.00' && $db->alloc_splits[0]['nilai_terpakai']==='0.00', 'Rollback exact balances');

foreach (array('500000000.00','218330833.34','218330833.32') as $bank) {
    list($db,$engine,$preview,$post)=contractFixture($bank);
    rejected($engine->save($post), 'Bank mismatch including one cent must reject');
    checkAssert(!$db->receipts && !$db->journals, 'Rejected reconciliation leaves no writes');
}
list($db,$engine,$preview,$post)=contractFixture();
$post['penerimaan_1']='218330833.33';
rejected($engine->save($post), 'Net cash cannot masquerade as gross allocation');
list($db,$engine,$preview,$post)=contractFixture();
$post['penerimaan_1']='222,000,000.00';
rejected($engine->save($post), 'Server parser remains strict; client must normalize');

foreach (array('PKP','WAPU','KB') as $tax) {
    $gross=$tax==='PKP'?22200000000:20000000000;
    foreach (array('Y','N') as $choice) {
        $cash=$gross-250000-($choice==='Y'?366666667:0);
        list($db,$engine,$preview,$post)=contractFixture(Invoice_calculation::decimal($cash),$tax,$choice);
        checkAssert($engine->save($post)['status']===1, $tax.' '.$choice.' saves');
        $dr=0;$cr=0;foreach($db->journals as $j){$dr+=Invoice_calculation::cents($j['debit']);$cr+=Invoice_calculation::cents($j['kredit']);checkAssert($j['tgl_jurnal']==='2026-10-26','Journal date is bank date');}
        checkAssert($dr===$cr,'Journal balanced');
    }
}

list($db,$engine,$preview,$post)=contractFixture('100000000.00');
$post['biaya_admin_1']='0.00';$post['sisa_piutang_1']='118333333.33';$post['penerimaan_1']='103666666.67';
checkAssert($engine->save($post)['status']===1,'First installment');
$db->alloc_details[]=array_merge($db->alloc_details[0],array('id'=>20,'nominal_debit'=>'118333333.33','nilai_terpakai'=>'0.00'));
$db->alloc_splits[]=array_merge($db->alloc_splits[0],array('id'=>200,'id_alokasi_detail'=>20,'nominal'=>'118333333.33','nilai_terpakai'=>'0.00'));
$next=$engine->preview(array('choose_inv'=>array(1),'id_alokasi'=>200,'pph23_dipotong'=>'Y'));
checkAssert($next['rows'][0]['default_pph']===0,'PPh already consumed is not offered again');
$nextPost=contractPost($next);$nextPost['id_alokasi']=200;$nextPost['biaya_admin_1']='0.00';$nextPost['pph_actual_1']='0.01';
rejected($engine->save($nextPost),'Repeated PPh must reject');
$nextPost['pph_actual_1']='0.00';
checkAssert($engine->save($nextPost)['status']===1,'Final installment without repeated PPh');

list($db,$engine,$preview,$post)=contractFixture();
$post['pph_actual_1']='3666666.68';rejected($engine->save($post),'PPh above 2% rejected');
list($db,$engine,$preview,$post)=contractFixture();
$db->invoices[0]['saldo_piutang']='221999999.99';rejected($engine->save($post),'Stale invoice rejected');

list($db,$engine,$preview,$post)=contractFixture('443988000.00','PKP','N');
$db->invoices[]=makeInvoice(2,'CONCEPT-2','PKP',20000000000,18333333333,2200000000,366666667,22200000000,22200000000);
$preview=$engine->preview(array('choose_inv'=>array(1,2),'id_alokasi'=>100,'pph23_dipotong'=>'N'));
$post=contractPost($preview,'N');$post['biaya_admin_1']='6000.00';$post['biaya_admin_2']='6000.00';
rejected($engine->save($post),'Aggregate admin 12000 rejected');
$db->alloc_details[0]['nominal_debit']='443990000.00';$db->alloc_splits[0]['nominal']='443990000.00';
$preview=$engine->preview(array('choose_inv'=>array(1,2),'id_alokasi'=>100,'pph23_dipotong'=>'N'));
$post=contractPost($preview,'N');$post['biaya_admin_1']='5000.00';$post['biaya_admin_2']='5000.00';
checkAssert($engine->save($post)['status']===1,'Aggregate admin exactly 10000 accepted');

list($db,$engine,$preview,$post)=contractFixture();
$old=$db->invoices[0];$old['id']=2;$old['consulting_version']=2;$db->invoices[]=$old;
try {$engine->preview(array('choose_inv'=>array(1,2),'id_alokasi'=>100,'pph23_dipotong'=>'Y'));throw new RuntimeException('Mixed preview accepted');}
catch(InvalidArgumentException $e){checkAssert(strpos($e->getMessage(),'terpisah')!==false,'Clear mixed message');}
$post['no_inv']='2';$post['id_inv_2']=2;
rejected($engine->save($post),'Mixed direct save rejected');

echo "PASS: concept gross allocation, bank reconciliation, taxes, cumulative PPh, admin, stale preview, mixed guard, snapshot and rollback\n";
