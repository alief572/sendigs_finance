<?php
require dirname(__DIR__).'/libraries/Payment_voucher_reconciliation.php';
$checks=0;
function verify($ok,$message){global $checks;$checks++;if(!$ok)throw new RuntimeException($message);}
$approvals=[];
foreach(Payment_voucher_reconciliation::SOURCES as $id=>$source)$approvals[]=[
    'id'=>$id,'id_payment'=>Payment_voucher_reconciliation::VOUCHER,'no_doc'=>$source[0],'tipe'=>$source[1],
    'jumlah'=>(string)intdiv($source[2],100),'status'=>'2','tgl_bayar'=>'2026-10-05','payment_bank'=>'8920202.00','total_payment'=>'8920202.00','selisih'=>'0','kurs_payment'=>'1'];
$journal=[];
for($i=11079;$i<=11089;$i++)$journal[]=['id'=>(string)$i,'no_transaksi'=>Payment_voucher_reconciliation::NATIVE_KEY,
    'jenis_transaksi'=>$i<11088?'Payment':'Refill Pettycash','id_company'=>'7','nm_company'=>'STM','sts'=>'0',
    'debit'=>$i===11079?'8713202.00':($i===11088?'207000.00':'0'),'kredit'=>$i===11089?'207000.00':'0'];
$evidence=['approvals'=>$approvals,'paid'=>[['bank_charge'=>'0']], 'journal'=>$journal,'collection_errors'=>[],
    'ledger'=>['test.jurnal'=>[]],'postings'=>[],'baseline_matches'=>true,'snapshot_unchanged'=>true,
    'bank'=>['coa_bank'=>'1101-02-01','coa_name'=>'Bank BCA','coa_verified'=>true,'payment_source_consistent'=>true]];
$report=Payment_voucher_reconciliation::assess($evidence);
verify($report['source_contract_matches']&&$report['candidate_credit_row']['kredit']==='8713202.00','baseline gives reviewable missing bank credit');
verify(!$report['candidate_credit_row']['apply_ready']&&$report['candidate_credit_row']['id_company']===null,'owner cannot be guessed from aggregate company');
verify($report['correction_status']==='belum layak diterapkan','no automatic apply eligibility');
foreach(['baseline_matches','snapshot_unchanged'] as $flag){$changed=$evidence;$changed[$flag]=false;verify(count(Payment_voucher_reconciliation::assess($changed)['blockers'])>count($report['blockers']),'drift is a blocker');}
$changed=$evidence;$changed['approvals'][0]['jumlah']='1';$r=Payment_voucher_reconciliation::assess($changed);
verify(!$r['source_contract_matches']&&$r['candidate_credit_row']['tgl_jurnal']===null,'source drift does not invent a verified date');
$changed=$evidence;$changed['approvals'][0]['id']='OTHER';verify(!Payment_voucher_reconciliation::assess($changed)['source_contract_matches'],'unknown approval cannot satisfy contract');
$changed=$evidence;$changed['approvals'][0]['jumlah']='-1';verify(!Payment_voucher_reconciliation::assess($changed)['source_contract_matches'],'malformed source produces blocked report rather than an exception');
$changed=$evidence;$changed['paid'][0]['bank_charge']='bad';verify(count(Payment_voucher_reconciliation::assess($changed)['blockers'])>1,'invalid fee produces blocked report');
$changed=$evidence;$changed['journal'][0]['debit']='bad';verify(count(Payment_voucher_reconciliation::assess($changed)['blockers'])>1,'malformed journal produces blocked report');
$changed=$evidence;$changed['journal'][0]['sts']='1';verify(count(Payment_voucher_reconciliation::assess($changed)['blockers'])>1,'posted journal blocks correction');
$changed=$evidence;$changed['postings']=[['accounting_numbers'=>'[]']];verify(count(Payment_voucher_reconciliation::assess($changed)['blockers'])>1,'posting metadata blocks correction');
$changed=$evidence;$changed['journal'][0]['id_company']='';verify(count(Payment_voucher_reconciliation::assess($changed)['blockers'])>1,'missing company blocks correction');
$changed=$evidence;$changed['ledger']['test.jurnal']=[['no_reff'=>'BK--1026-0002']];verify(count(Payment_voucher_reconciliation::assess($changed)['blockers'])>1,'ledger overlap blocks correction');
$changed=$evidence;$changed['collection_errors']=['unreachable accounting'];verify(in_array('unreachable accounting',Payment_voucher_reconciliation::assess($changed)['blockers'],true),'unavailable target fails closed');
$changed=$evidence;$changed['bank']['coa_verified']=false;verify(Payment_voucher_reconciliation::assess($changed)['candidate_credit_row']['coa']===null,'unverified COA is not proposed as valid');
$keys=['BK--1026-0002','BK--1026-0040'];
foreach(['BK--1026-0040;BK--1026-0006','["BK--1026-0002"]','Payment BK--1026-0002. Test'] as $value)verify(Payment_voucher_reconciliation::matches($value,$keys),'tokens match list/JSON/free text');
verify(!Payment_voucher_reconciliation::matches('BK--1026-00020',$keys),'voucher prefix cannot match another voucher');
verify(!Payment_voucher_reconciliation::matches('X-BK--1026-0002',$keys),'identifier suffix cannot match another identifier');
verify(Payment_voucher_reconciliation::tokens(' B;A,A ')===['A','B'],'list normalisation ignores order, spaces and duplicates');
verify(Payment_voucher_reconciliation::fingerprint([['id'=>1,'x'=>null]])===Payment_voucher_reconciliation::fingerprint([['id'=>'1','x'=>null]]),'prepared and text result types have identical fingerprints');
$output=[];$code=0;
exec(escapeshellarg(PHP_BINARY).' '.escapeshellarg(dirname(__DIR__).'/cli/reconcile_bk_1026_0002.php').' --apply 2>&1',$output,$code);
verify($code===2&&strpos(implode(' ',$output),'no options or apply mode')!==false,'CLI rejects apply before connecting');
echo "PASS read-only reconciliation: {$checks} assertions\n";
