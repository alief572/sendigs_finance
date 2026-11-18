<?php
require __DIR__.'/Payment_reference_test.php';
require_once dirname(__DIR__).'/libraries/Payment_identity_repair.php';
$identityBase=$checks;

function identityFixture()
{
    $ci=fixture();$d=$ci->db;
    foreach(['tr_jurnal','payment_approve','tr_payment_paid'] as $t)$d->query('DELETE FROM '.$t);
    $d->query('CREATE TABLE tr_jurnal_payment_reference_aliases (run_id TEXT,journal_id INTEGER,original_no_transaksi TEXT,voucher TEXT,approval_ids TEXT,active INTEGER,created_at TEXT,PRIMARY KEY(run_id,journal_id))');
    foreach($ci->load->dbs as $acc)$acc->query('ALTER TABLE jurnal ADD COLUMN id INTEGER');
    $links=['BK--0826-0016'=>'BK--0926-0011','BK--0826-0017'=>'BK--0926-0014','BK--0826-0020'=>'BK--0926-0018','BK--1126-0007'=>'BK--1126-0011','BK--1126-0008'=>'BK--1126-0015','BK--1126-0010'=>'BK--1126-0012','BK--1126-0018'=>'BK--1126-0027','BK--1126-0019'=>'BK--1126-0029','BK--1126-0020'=>'BK--1126-0030','BK--1126-0021'=>'BK--1126-0031'];
    $id=1;
    foreach(Payment_identity_repair::specs() as $v=>$spec){
        $approval=$links[$v] ?? 'APP-'.$v;$company=$spec['target']==='accounting_sustain'?'3':'7';
        $d->insert('payment_approve',['id'=>$approval,'id_payment'=>$v,'no_doc'=>'DOC-'.$v,'tipe'=>$spec['type']==='Payment'?'kasbon':'refill_pettycash']);
        $d->insert('tr_payment_paid',['id'=>$v]);
        foreach([[100,0,'Cost - '.$approval],[0,100,'Bank - '.$approval],[0,0,'PPN - '.$approval]] as $line){
            $r=['id'=>$id,'no_transaksi'=>$v,'jenis_transaksi'=>$spec['type'],'sts'=>$spec['status'],'id_company'=>$company,'nm_company'=>'Company','tgl_jurnal'=>'2026-11-04','coa'=>'5101','nm_coa'=>'Cost','debit'=>$line[0],'kredit'=>$line[1],'keterangan'=>$line[2],'no_jurnal'=>'S-'.$id,'tgl_posting'=>$spec['status']==='1'?'2026-11-17 10:00:00':null,'posted_by'=>$spec['status']==='1'?'42':null];
            $d->insert('tr_jurnal',$r);
            if($spec['status']==='1'&&($line[0]||$line[1]))$ci->load->dbs[$spec['target']]->insert('jurnal',['id'=>$id,'tipe'=>'BUK','nomor'=>'ACC-'.$v,'tanggal'=>$r['tgl_jurnal'],'no_reff'=>$v,'no_perkiraan'=>'5101','keterangan'=>$r['keterangan'],'debet'=>$line[0],'kredit'=>$line[1]]);
            $id++;
        }
        if($spec['status']==='1')$ci->load->dbs[$spec['target']]->insert('japh',['nomor'=>'ACC-'.$v,'no_reff'=>$v,'jml'=>'100','tgl'=>'2026-11-04','note'=>'Payment '.$v]);
    }
    return $ci;
}
function identitySnapshot($ci)
{
    $out=[];foreach(['tr_jurnal','payment_approve','tr_payment_paid','tr_jurnal_postings','tr_jurnal_payment_attempts'] as $t)$out[$t]=Payment_normalization::digest($ci->db->get($t)->result_array());
    $out['aliases']=Payment_normalization::digest($ci->db->where('active',1)->get('tr_jurnal_payment_reference_aliases')->result_array());
    foreach($ci->load->dbs as $name=>$acc)foreach(['jurnal','japh','pastibisa_tb_cabang'] as $t)$out[$name.':'.$t]=Payment_normalization::digest($acc->get($t)->result_array());return $out;
}
function identityPath(){return sys_get_temp_dir().'/identity-test-'.bin2hex(random_bytes(8)).'.json';}
function identityClean($path){foreach([$path,$path.'.journal',$path.'.backup.json'] as $p)if(is_file($p))unlink($p);}

check(Payment_identity_repair::replaceApproval('Cost - APP-1',['APP-1'],'V')==='Cost - V','replace exact suffix');
check(Payment_identity_repair::replaceApproval('Cost - APP-10',['APP-1'],'V')==='Cost - APP-10','avoid prefix match');
check(Payment_identity_repair::replaceApproval('APP-1 account - RPC-1',['APP-1'],'V')==='APP-1 account - RPC-1','preserve document and prose');
check(Payment_identity_repair::replaceApproval('Cost - APP-2',['APP-1','APP-2'],'V')==='Cost - V','multi approval suffix');
check(Payment_identity_repair::replaceApproval(null,['APP-1'],'V')===null,'preserve null');

$path=identityPath();
try{
    $ci=identityFixture();$before=identitySnapshot($ci);$repair=new Payment_identity_repair($ci);$m=$repair->prepare($path);
    check(identitySnapshot($ci)===$before,'prepare is read-only');check(count($m['items'])===22,'fixed cohort');
    $repair->run($path);$result=$repair->verify($path);check($result['groups']===22&&$result['aliases']===63,'apply and exact verify');
    foreach(Payment_identity_repair::PENDING as $v){$r=(new Payment_reference($ci))->inspectForPosting($v);check($r['voucher']===$v,'canonical pending remains ready '.$v);}
    foreach(Payment_identity_repair::POSTED as $v){$r=$ci->db->get_where('tr_jurnal',['no_transaksi'=>$v])->result_array();check(count(array_filter($r,function($r){return $r['sts']==='1';}))===3,'posted state preserved '.$v);}
    $after=identitySnapshot($ci);$repair->run($path);check(identitySnapshot($ci)===$after,'apply retry writes no duplicates');
    $repair->run($path,true);$repair->verify($path,true);check(identitySnapshot($ci)===$before,'rollback restores exact business records');
    $repair->run($path,true);check(identitySnapshot($ci)===$before,'rollback idempotent');
}finally{identityClean($path);}

foreach(['changed','company','metadata','source','alias','commit','commit_after','accounting_partial','staging_after_accounting','backup','environment'] as $case){
    $path=identityPath();try{
        $ci=identityFixture();$repair=new Payment_identity_repair($ci);$repair->prepare($path);
        if($case==='changed')$ci->db->where('id',1)->update('tr_jurnal',['keterangan'=>'Changed']);
        if($case==='company')$ci->db->where('id',1)->update('tr_jurnal',['id_company'=>'3']);
        if($case==='metadata')$ci->db->insert('tr_jurnal_postings',['no_transaksi'=>Payment_identity_repair::PENDING[0],'jenis_transaksi'=>'Payment']);
        if($case==='source')$ci->db->fail='update:tr_jurnal';
        if($case==='alias')$ci->db->fail='insert_batch:tr_jurnal_payment_reference_aliases';
        if($case==='commit')$ci->db->fail='commit:1';
        if($case==='commit_after')$ci->db->fail='commit_after:1';
        $target=$ci->load->dbs['accounting_stm'];
        if($case==='accounting_partial')$target->query("CREATE TRIGGER fail_detail BEFORE UPDATE ON jurnal WHEN OLD.id=32 BEGIN SELECT RAISE(ABORT,'fixture'); END");
        if($case==='staging_after_accounting')$ci->db->query("CREATE TRIGGER fail_staging BEFORE UPDATE ON tr_jurnal WHEN OLD.id=31 BEGIN SELECT RAISE(ABORT,'fixture'); END");
        if($case==='backup')file_put_contents($path.'.backup.json','{}');
        if($case==='environment'){$m=Payment_patch::load($path);$m['environment']='wrong';file_put_contents($path,json_encode($m));}
        $before=identitySnapshot($ci);$failed=false;try{$repair->run($path);}catch(Throwable $e){$failed=true;}
        check($failed,'reject failure '.$case);check($ci->db->locks===0&&$target->locks===0,'release all locks '.$case);
        if(in_array($case,['changed','company','metadata','source','alias','commit','backup','environment'],true))check(identitySnapshot($ci)===$before,'no business changes on first group failure '.$case);
        if(in_array($case,['accounting_partial','staging_after_accounting'],true)){
            check(Payment_normalization::digest($target->get('jurnal')->result_array())===$before['accounting_stm:jurnal'],'compensate MyISAM detail '.$case);
            check($ci->db->get_where('tr_jurnal',['id'=>31])->row()->keterangan==='Cost - APP-BK--0926-0011','failed posted source remains original '.$case);
        }
        if($case==='commit'){$ci->db->fail='';rejects(function()use($repair,$path){$repair->run($path);},'rekonsiliasi');$repair->run($path,true);check(identitySnapshot($ci)===$before,'explicit rollback reconciles uncommitted source');}
        if($case==='commit_after'){$ci->db->fail='';$repair->run($path);$repair->verify($path);check($ci->db->get_where('tr_jurnal',['id'=>1])->row()->keterangan==='Cost - BK--0826-0016','verified committed retry never duplicates aliases');}
    }finally{identityClean($path);}
}
$ci=fixture();$controller=(new ReflectionClass('Jurnal_payment'))->newInstanceWithoutConstructor();
foreach(['db','load','auth','Jurnal_payment_nomor_model'] as $property)$controller->$property=$ci->$property;
$controller->input=new class extends PaymentFixtureInput {public function method($upper){return 'GET';}};
$controller->output=new PaymentFixtureOutput();$_POST=['id'=>1];
$before=Payment_reference::fingerprint($ci->db->get('tr_jurnal')->result_array());
ob_start();$controller->save_posting_jurnal();$denied=json_decode(ob_get_clean(),true);
check((string)($denied['save'] ?? '')==='0','native Payment rejects GET');
check(Payment_reference::fingerprint($ci->db->get('tr_jurnal')->result_array())===$before&&!$ci->load->dbs['accounting_stm']->get('japh')->num_rows(),'denied native action has no business writes');
echo 'PASS Payment identity repair: '.($checks-$identityBase)." assertions\n";
