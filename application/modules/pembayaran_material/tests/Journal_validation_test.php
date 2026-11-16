<?php
define('MATERIAL_FIXTURE_ONLY',true);
require __DIR__.'/Batch1_test.php';
require APPPATH.'helpers/json_helper.php';
$modelSource=file_get_contents(dirname(__DIR__).'/models/Pembayaran_material_model.php');
preg_match_all('/->([a-zA-Z_]\w*)\b(?!\s*\()/',$modelSource,$propertyMatches);
$GLOBALS['materialRowDefaults']=array_merge(array_fill_keys($propertyMatches[1],0),[
    'nama'=>'Bank','nm_bank'=>'Bank','nama_bank'=>'Bank','rekening'=>'Test','coa_bank'=>'1101-01','nm_coa'=>'Kas','no_coa'=>'1101-01','coa'=>'1101-01',
    'requestor'=>'Test','request_by'=>'Test','keperluan'=>'Test','created_on'=>'2026-10-05','max_id'=>'','kode_bank'=>'BNK','subcab'=>'01',
    'no_payment_hutang'=>'PHP-1','department_id'=>5,'company_id'=>'COM006','nm_company'=>'Sustain','name'=>'Department','currency'=>'IDR','tipe'=>'kasbon']);
$journal=['nama_account'=>'Biaya','coa'=>'6207-00-00','tanggal_jurnal'=>'2026-10-05','company'=>'STM',
    'debit'=>'8713202.00','kredit'=>'0','keterangan'=>'Biaya','id_company'=>7,'nm_company'=>'STM','nm_coa'=>'Biaya','id_divisi'=>5,'nm_divisi'=>'Finance'];
list($event,$output,$controller)=material_run('save_payment',[],['jurnal_ls'=>[$journal]]);
check(json_decode($output,true)['status']===0,'unbalanced legacy-shaped Payment must be rejected before staging insert');
check($event['context']['data']['reason']==='invalid_journal','one audit uses invalid_journal reason');
check(in_array('trans_rollback',$controller->db->calls,true)&&!in_array('trans_commit',$controller->db->calls,true),'invalid journal rolls back without commit');
foreach($controller->db->writes as $write)check(!in_array($write[1][0],['tr_jurnal','jurnaltras',DBACC.'.jurnal',DBACC.'.japh'],true),'invalid journal cannot reach staging/accounting insert');
check(strpos(json_decode($output,true)['pesan'],'8713202.00')!==false,'failure explains group difference');
foreach(['writer_failure','writer_exception'] as $failure){
    list($event,$output,$controller)=material_run('save_payment',[$failure=>true],['jurnal_ls'=>[$journal]]);
    check(json_decode($output,true)['status']===0&&$event['context']['data']['reason']==='invalid_journal','audit writer failure cannot change validation outcome');
    check(in_array('trans_rollback',$controller->db->calls,true)&&!in_array('trans_commit',$controller->db->calls,true),'writer failure does not commit invalid data');
}
$emptyPost=['jurnal_ls'=>[],'jurnal_refill_pettycash'=>[]];
list($event,$output)=material_run('save_payment',[],$emptyPost);
check(json_decode($output,true)['status']===0&&$event['context']['data']['reason']==='invalid_journal','empty assembled journal is rejected');
echo "PASS controller invalid journal regression\n";
