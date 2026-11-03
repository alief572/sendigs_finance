<?php
require dirname(__DIR__).'/libraries/Payment_voucher_repair.php';
$checks=0;
function checkRepair($ok,$message){global $checks;$checks++;if(!$ok)throw new RuntimeException($message);}
$e=['source_contract_matches'=>true,'baseline_matches'=>true,'snapshot_unchanged'=>true,'postings'=>[],
 'collection_errors'=>[],'journal_sha256_before'=>'baseline','bank'=>['id'=>'18','coa_bank'=>'1101-02-01','coa_verified'=>true,'payment_source_consistent'=>true],
 'ledger'=>['source.jurnaltras'=>[
  ['id'=>'1183','no_request'=>'BK--1026-0002','no_perkiraan'=>'18','debet'=>'0','kredit'=>'8920202','stspos'=>'1'],
  ['id'=>'1184','no_request'=>'BK--1026-0002','no_perkiraan'=>'2101-01-01','debet'=>'8920202','kredit'=>'0','stspos'=>'1'],
  ['id'=>'1185','no_request'=>'BK--1026-0002','no_perkiraan'=>'6101-01-24','debet'=>'0','kredit'=>'0','stspos'=>'1']], 'target.jurnal'=>[]],
 'journal'=>[]];
foreach(range(11079,11089) as $id)$e['journal'][]=['id'=>(string)$id,'no_transaksi'=>Payment_voucher_reconciliation::NATIVE_KEY,'jenis_transaksi'=>$id<11088?'Payment':'Refill Pettycash',
 'tgl_jurnal'=>'2026-10-05','id_company'=>$id>=11085&&$id<=11087?'':'7','nm_company'=>$id>=11085&&$id<=11087?'':'STM',
 'coa'=>$id<11088?'6207-00-00':'1101-02-01','nm_coa'=>'Account','keterangan'=>'Original','no_jurnal'=>'N'.$id,'sts'=>'0',
 'debit'=>$id===11079?'8713202.00':($id===11088?'207000.00':'0.00'),'kredit'=>$id===11089?'207000.00':'0.00'];
$ownership=['cash'=>[],'periodik'=>[],'target_coas'=>['6207-00-00','1101-02-01']];
foreach(['DPM-2026-00081','DPM-2026-00083','DPM-2026-00082'] as $doc)$ownership['cash'][]=['no_doc'=>$doc,'company_id'=>'COM003'];
foreach(['PERIODIK-2026-00181','PERIODIK-2026-00177'] as $doc)$ownership['periodik'][]=['no_doc'=>$doc,'company_id'=>'COM003'];
$review=['bank_company'=>'STM','bank_id'=>'18','bank_coa'=>'1101-02-01','legacy_jurnaltras'=>'retain_existing_trace_no_replay','reviewed_by'=>'Reviewer'];
$p=Payment_voucher_repair::plan($e,$ownership,$review);
checkRepair($p['ready'],'verified source and reviewed bank produce ready plan');
checkRepair(count($p['updates'])===3&&$p['insert']['kredit']==='8713202.00','three company updates and exact bank credit only');
checkRepair($p['insert']['no_transaksi']===Payment_voucher_reconciliation::NATIVE_KEY&&$p['insert']['sts']==='0','original posting key and pending status');
checkRepair($p['accounting_writes']===0&&$p['legacy_ledger_writes']===0,'plan never writes accounting or legacy trace');
checkRepair(!Payment_voucher_repair::plan($e,$ownership,[])['ready'],'bank/legacy classification requires explicit review');
foreach(['source_contract_matches','baseline_matches','snapshot_unchanged'] as $flag){$x=$e;$x[$flag]=false;checkRepair(!Payment_voucher_repair::plan($x,$ownership,$review)['ready'],'reject '.$flag);}
foreach(['cash','periodik'] as $kind){$o=$ownership;$o[$kind][0]['company_id']='COM012';checkRepair(!Payment_voucher_repair::plan($e,$o,$review)['ready'],'source owner mismatch '.$kind);$o=$ownership;array_pop($o[$kind]);checkRepair(!Payment_voucher_repair::plan($e,$o,$review)['ready'],'source missing '.$kind);}
$o=$ownership;$o['target_coas']=['1101-02-01'];checkRepair(!Payment_voucher_repair::plan($e,$o,$review)['ready'],'missing Periodik target COA blocks apply');
$x=$e;$x['ledger']['target.jurnal']=[['id'=>'1']];checkRepair(!Payment_voucher_repair::plan($x,$ownership,$review)['ready'],'existing accounting blocks repair');
$x=$e;$x['ledger']['source.jurnaltras'][0]['kredit']='1';checkRepair(!Payment_voucher_repair::plan($x,$ownership,$review)['ready'],'changed legacy trace blocks repair');
$x=$e;$x['journal'][0]['sts']='1';checkRepair(!Payment_voucher_repair::plan($x,$ownership,$review)['ready'],'posted source blocks repair');
$x=$e;$x['journal'][0]['kredit']='1';checkRepair(!Payment_voucher_repair::plan($x,$ownership,$review)['ready'],'changed balance blocks repair');
$x=$e;$x['postings']=[['id'=>'1']];checkRepair(!Payment_voucher_repair::plan($x,$ownership,$review)['ready'],'metadata blocks repair');
$x=$e;$x['bank']['coa_bank']='OTHER';checkRepair(!Payment_voucher_repair::plan($x,$ownership,$review)['ready'],'changed bank blocks repair');
echo "PASS voucher repair plan: {$checks} assertions\n";
