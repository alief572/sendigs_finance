<?php
require __DIR__.'/Payment_reference_test.php';
require dirname(__DIR__).'/libraries/Payment_normalization.php';
$normalizationBase=$checks;
function normalizationFixture()
{
    $ci=fixture();
    $ci->db->query('CREATE TABLE tr_jurnal_payment_reference_aliases (run_id TEXT,journal_id INTEGER,original_no_transaksi TEXT,voucher TEXT,approval_ids TEXT,active INTEGER,created_at TEXT,PRIMARY KEY(run_id,journal_id))');
    return $ci;
}
function addNormalizationGroup($ci,$key='APP-2',$company='7',$date='2026-10-15',$status='0')
{
    $ci->db->insert('payment_approve',['id'=>$key,'id_payment'=>'BK--1026-0004','no_doc'=>'DOC-'.$key,'tipe'=>'periodik']);
    foreach([[200,0],[0,200]] as $i=>$money)$ci->db->insert('tr_jurnal',['id'=>3+$i,'no_transaksi'=>$key,'jenis_transaksi'=>'Payment','sts'=>$status,'id_company'=>$company,'nm_company'=>'Company','tgl_jurnal'=>$date,'coa'=>'5101','nm_coa'=>'Biaya','debit'=>$money[0],'kredit'=>$money[1],'keterangan'=>'Payment','no_jurnal'=>'STAGE-2-'.$i,'nm_divisi'=>'Finance']);
}
function normalizationFiles($path)
{
    foreach([$path,$path.'.backup.json',$path.'.journal'] as $file)if(is_file($file))unlink($file);
}
$path=dirname(__DIR__,4).'/scratch/normalization-'.bin2hex(random_bytes(6)).'.json';
try{
    $ci=normalizationFixture();addNormalizationGroup($ci);
    $ci->db->insert('jurnaltras',['no_reff'=>'APP-1','no_request'=>'BK--1026-0004']);
    $ci->load->dbs['accounting']->insert('jurnal',['no_reff'=>'APP-1']);
    $before=$ci->db->get('tr_jurnal')->result_array();$accounting=$ci->load->dbs['accounting']->get('jurnal')->result_array();
    $n=new Payment_normalization($ci);$manifest=$n->prepare($path);
    check(count($manifest['items'])===1&&$manifest['items'][0]['status']==='eligible'&&count($manifest['items'][0]['groups'])===2,'same voucher forms one complete normalization cohort');
    check($manifest['items'][0]['accounting_at_review']['verified'],'existing accounting is captured rather than excluding normalization');
    check($before===$ci->db->get('tr_jurnal')->result_array(),'dry-run changes no journal rows');
    $n->run($path);$verified=$n->verify($path);
    check($verified['changed_rows']===4&&$verified['fields_and_posted_unchanged'],'all pending group rows normalized, other fields identical');
    check($ci->db->get_where('tr_jurnal_payment_reference_aliases',['active'=>1])->num_rows()===4,'one active alias per member persisted atomically');
    check($accounting===$ci->load->dbs['accounting']->get('jurnal')->result_array(),'accounting remains byte-for-byte unchanged');
    $resolved=(new Payment_reference($ci))->resolve('BK--1026-0004');
    check(in_array('APP-1',$resolved['references'],true)&&in_array('APP-2',$resolved['references'],true),'old keys remain searchable for duplicate guards');
    rejects(function()use($ci){(new Payment_reference($ci))->inspect('BK--1026-0004');},'posting ulang diblokir');
    $after=$ci->db->get('tr_jurnal')->result_array();$aliases=$ci->db->get('tr_jurnal_payment_reference_aliases')->result_array();
    $n->run($path);check($after===$ci->db->get('tr_jurnal')->result_array()&&$aliases===$ci->db->get('tr_jurnal_payment_reference_aliases')->result_array(),'apply rerun is exact no-op');
    $n->run($path,true);check($before===$ci->db->get('tr_jurnal')->result_array(),'rollback restores distinct original keys despite existing accounting');
    check($ci->db->get_where('tr_jurnal_payment_reference_aliases',['active'=>0])->num_rows()===4,'rollback retains alias history inactive');
    $n->run($path,true);check($before===$ci->db->get('tr_jurnal')->result_array(),'rollback rerun no-op');
    // Reapply this manifest cannot reuse inactive PK rows; create a fresh batch instead.
    normalizationFiles($path);$n->prepare($path);$n->run($path);check($n->verify($path)['changed_rows']===4,'fresh batch after rollback preserves history and re-normalizes');
    normalizationFiles($path);

    foreach(['company','date','unbalanced','posted_member','mixed_member','metadata','attempt','unknown_status','changed','alias_failure','number_failure','commit','commit_after','new_member','mapping_changed'] as $case){
        $ci=normalizationFixture();addNormalizationGroup($ci,$case==='posted_member'?'APP-2':'APP-2',$case==='company'?'4':'7',$case==='date'?'2026-10-16':'2026-10-15',$case==='posted_member'?'1':'0');
        if($case==='unbalanced')$ci->db->where('id',4)->update('tr_jurnal',['kredit'=>100]);
        if($case==='metadata')$ci->db->insert('tr_jurnal_postings',['no_transaksi'=>'APP-2','jenis_transaksi'=>'Payment']);
        if($case==='attempt')$ci->db->insert('tr_jurnal_payment_attempts',['voucher'=>'BK--1026-0004','status'=>'reconcile']);
        if($case==='unknown_status')$ci->db->where('id',4)->update('tr_jurnal',['sts'=>'2']);
        if($case==='mixed_member')$ci->db->where('id',4)->update('tr_jurnal',['sts'=>'1']);
        $n=new Payment_normalization($ci);$manifest=$n->prepare($path);
        if(in_array($case,['company','date','unbalanced','posted_member','mixed_member','metadata','attempt','unknown_status'],true)){
            check($manifest['items'][0]['status']==='skipped','whole cohort excluded: '.$case);$snapshot=$ci->db->get('tr_jurnal')->result_array();$n->run($path);check($snapshot===$ci->db->get('tr_jurnal')->result_array(),'excluded cohort unchanged: '.$case);
        }else{
            if($case==='changed')$ci->db->where('id',1)->update('tr_jurnal',['keterangan'=>'Changed']);
            if($case==='alias_failure')$ci->db->fail='insert_batch:tr_jurnal_payment_reference_aliases';
            if($case==='number_failure')$ci->db->fail='update:tr_jurnal';
            if($case==='commit')$ci->db->fail='commit:1';
            if($case==='commit_after')$ci->db->fail='commit_after:1';
            if($case==='new_member'){$row=$ci->db->get_where('tr_jurnal',['id'=>1])->row_array();$row['id']=5;$ci->db->insert('tr_jurnal',$row);}
            if($case==='mapping_changed')$ci->db->where('id','APP-2')->update('payment_approve',['id_payment'=>'OTHER']);
            $failed=false;try{$n->run($path);}catch(Throwable $error){$failed=true;}check($failed,'failure safely reported: '.$case);
            if(in_array($case,['alias_failure','number_failure','commit'],true))check($ci->db->get_where('tr_jurnal',['no_transaksi'=>'BK--1026-0004'])->num_rows()===0&&!$ci->db->get('tr_jurnal_payment_reference_aliases')->num_rows(),'source and alias rollback together: '.$case);
            if(in_array($case,['commit','commit_after'],true)){$ci->db->fail='';$n->run($path);check($n->verify($path)['changed_rows']===4,'commit uncertainty resolved idempotently: '.$case);}
        }
        normalizationFiles($path);
    }
    foreach(['posted','new_accounting','changed','metadata'] as $case){
        $ci=normalizationFixture();$n=new Payment_normalization($ci);$n->prepare($path);$n->run($path);
        if($case==='posted')$ci->db->update('tr_jurnal',['sts'=>'1']);
        if($case==='new_accounting')$ci->load->dbs['accounting_stm']->insert('japh',['no_reff'=>'APP-1']);
        if($case==='changed')$ci->db->where('id',1)->update('tr_jurnal',['keterangan'=>'Changed']);
        if($case==='metadata')$ci->db->insert('tr_jurnal_postings',['no_transaksi'=>'APP-1','jenis_transaksi'=>'Payment']);
        $snapshot=$ci->db->get('tr_jurnal')->result_array();$failed=false;try{$n->run($path,true);}catch(Throwable $error){$failed=true;}
        check($failed&&$snapshot===$ci->db->get('tr_jurnal')->result_array(),'rollback cannot alter protected/changed group: '.$case);normalizationFiles($path);
    }
    $ci=normalizationFixture();$ci->db->update('tr_jurnal',['no_transaksi'=>'BK--1026-0004']);$n=new Payment_normalization($ci);$m=$n->prepare($path);check($m['items'][0]['status']==='consistent','canonical-only group needs no write');$n->run($path);check(!$ci->db->get('tr_jurnal_payment_reference_aliases')->num_rows(),'canonical-only group stays untouched');normalizationFiles($path);

    // An old comma-separated reference must survive, even with a paid namespace collision.
    $ci=normalizationFixture();addNormalizationGroup($ci);$ci->db->where_in('id',[1,2])->update('tr_jurnal',['no_transaksi'=>'APP-1,APP-2']);$ci->db->where_in('id',[3,4])->delete('tr_jurnal');
    $ci->db->insert('payment_approve',['id'=>'BK--1026-0004','id_payment'=>'OTHER','no_doc'=>'OTHER-DOC','tipe'=>'periodik']);$ci->db->insert('tr_payment_paid',['id'=>'OTHER']);
    $ci->load->dbs['accounting']->insert('japh',['no_reff'=>'APP-1,APP-2']);
    $n=new Payment_normalization($ci);$n->prepare($path);$n->run($path);
    check((new Payment_reference($ci))->resolve('BK--1026-0004')['voucher']==='BK--1026-0004','alias establishes canonical identity without relying on description');
    rejects(function()use($ci){(new Payment_reference($ci))->inspect('BK--1026-0004');},'Hasil accounting');
    $model=new Jurnal_all_model();$model->db=$ci->db;check($model->detail(1,false)['header']['no_referensi']==='KS-2026-00331, DOC-APP-2','batched model uses the same complete alias mapping');
    $ci->db->where('journal_id',2)->update('tr_jurnal_payment_reference_aliases',['active'=>0]);
    rejects(function()use($ci){(new Payment_reference($ci))->resolve('BK--1026-0004');},'Coverage');normalizationFiles($path);

    $ci=normalizationFixture();addNormalizationGroup($ci,'BK--1026-0004');$n=new Payment_normalization($ci);$n->prepare($path);$n->run($path);
    $verified=$n->verify($path);check($verified['changed_rows']===2&&$verified['normalized_rows']===4,'existing canonical members join the cohort without counting them as changed numbers');
    $n->run($path,true);check($ci->db->get_where('tr_jurnal',['no_transaksi'=>'BK--1026-0004'])->num_rows()===2,'rollback preserves originally canonical members');normalizationFiles($path);

    $ci=normalizationFixture();addNormalizationGroup($ci,'POSTED');$ci->db->where('id','POSTED')->update('payment_approve',['id_payment'=>'POSTED-V']);$ci->db->insert('tr_payment_paid',['id'=>'POSTED-V']);$ci->db->where_in('id',[3,4])->update('tr_jurnal',['sts'=>'1','tgl_posting'=>'2026-10-01','posted_by'=>'42']);
    $ci->db->insert('tr_jurnal_postings',['no_transaksi'=>'POSTED','jenis_transaksi'=>'Payment']);$postedBefore=$ci->db->where_in('id',[3,4])->get('tr_jurnal')->result_array();
    $zero=$ci->db->get_where('tr_jurnal',['id'=>1])->row_array();$zero['id']=5;$zero['debit']=0;$zero['kredit']=0;$ci->db->insert('tr_jurnal',$zero);
    $n=new Payment_normalization($ci);$n->prepare($path);$n->run($path);$verified=$n->verify($path);
    check($verified['posted_rows']===2&&$verified['changed_rows']===3,'zero rows normalize and unrelated posted rows stay protected');
    check($postedBefore===$ci->db->where_in('id',[3,4])->get('tr_jurnal')->result_array(),'posted snapshot including timestamps/actor remains identical');normalizationFiles($path);

    foreach(['approval','voucher','duplicate','old_metadata'] as $case){
        $ci=normalizationFixture();$n=new Payment_normalization($ci);$n->prepare($path);$n->run($path);
        if($case==='approval')$ci->db->where('id','APP-1')->update('payment_approve',['id_payment'=>'OTHER']);
        if($case==='voucher')$ci->db->where('journal_id',1)->update('tr_jurnal_payment_reference_aliases',['voucher'=>'OTHER']);
        if($case==='duplicate'){$alias=$ci->db->get('tr_jurnal_payment_reference_aliases')->row_array();$alias['run_id']=str_repeat('f',32);$ci->db->insert('tr_jurnal_payment_reference_aliases',$alias);}
        if($case==='old_metadata')$ci->db->insert('tr_jurnal_postings',['no_transaksi'=>'APP-1','jenis_transaksi'=>'Payment']);
        $rejected=false;try{(new Payment_reference($ci))->inspect('BK--1026-0004');}catch(Throwable $e){$rejected=true;}check($rejected,'alias cannot bypass changed relationship or metadata: '.$case);normalizationFiles($path);
    }
    $ci=normalizationFixture();addNormalizationGroup($ci);$ci->db->where('id','APP-2')->update('payment_approve',['id_payment'=>'OTHER']);$ci->db->insert('tr_payment_paid',['id'=>'OTHER']);
    $n=new Payment_normalization($ci);$n->prepare($path);$ci->db->fail='commit:2';$failed=false;try{$n->run($path);}catch(Throwable $e){$failed=true;}
    check($failed&&$ci->db->get_where('tr_jurnal',['no_transaksi'=>'BK--1026-0004'])->num_rows()===2,'partial batch records first committed voucher before second failure');
    $ci->db->fail='';$n->run($path);check($n->verify($path)['changed_rows']===4,'resume completes remaining voucher without duplicate alias writes');normalizationFiles($path);

    $ci=normalizationFixture();addNormalizationGroup($ci,'APP-OTHER');$ci->db->where('id','APP-OTHER')->update('payment_approve',['id_payment'=>'OTHER']);$ci->db->insert('tr_payment_paid',['id'=>'OTHER']);
    $ci->db->where_in('id',[1,2])->update('tr_jurnal',['jenis_transaksi'=>'Transport']);$n=new Payment_normalization($ci);$n->prepare($path);$n->run($path);normalizationFiles($path);
    $ci->db->where_in('id',[1,2])->update('tr_jurnal',['jenis_transaksi'=>'Payment']);$ci->db->insert('payment_approve',['id'=>'OTHER','id_payment'=>'BK--1026-0004','no_doc'=>'UNRELATED-NUMBER','tipe'=>'periodik']);
    $n=new Payment_normalization($ci);$n->prepare($path);$n->run($path);
    check($n->verify($path)['changed_rows']===2,'authoritative aliases distinguish another canonical voucher from a colliding approval ID during membership checks');normalizationFiles($path);
    check((new Payment_reference($ci))->inspect('BK--1026-0004')['voucher']==='BK--1026-0004','posting membership uses authoritative aliases for unrelated canonical voucher names too');

    $ci=normalizationFixture();addNormalizationGroup($ci,'BK--1026-0004');$ci->db->where('id','BK--1026-0004')->update('payment_approve',['id_payment'=>'OTHER']);$ci->db->insert('tr_payment_paid',['id'=>'OTHER']);$ci->db->where_in('id',[3,4])->update('tr_jurnal',['keterangan'=>'Payment - BK--1026-0004']);
    $n=new Payment_normalization($ci);$n->prepare($path);$n->run($path);$secondPath=$path.'.second.json';
    try{
        $n->prepare($secondPath);$n->run($secondPath);$snapshot=$ci->db->get('tr_jurnal')->result_array();
        $n->run($path);check($snapshot===$ci->db->get('tr_jurnal')->result_array(),'earlier applied batch remains no-op after its old slot becomes another canonical voucher');
        $failed=false;try{$n->run($path,true);}catch(Throwable $e){$failed=true;}check($failed,'older batch rollback cannot overwrite canonical slots filled by a later batch');
        $n->run($secondPath,true);$n->run($path,true);check($ci->db->get_where('tr_jurnal',['no_transaksi'=>'APP-1'])->num_rows()===2&&$ci->db->get_where('tr_jurnal',['no_transaksi'=>'BK--1026-0004'])->num_rows()===2,'dependent batches roll back safely in reverse order');
    }finally{normalizationFiles($secondPath);normalizationFiles($path);}

    $ci=normalizationFixture();$ci->db->delete('tr_jurnal');$ci->db->delete('payment_approve');$ci->db->delete('tr_payment_paid');$keys=[];
    for($i=0;$i<205;$i++){
        $voucher='BULK-V-'.$i;$approval='BULK-A-'.$i;$keys[]=$voucher;
        $ci->db->insert('payment_approve',['id'=>$approval,'id_payment'=>$voucher,'no_doc'=>'BULK-DOC-'.$i,'tipe'=>'kasbon']);$ci->db->insert('tr_payment_paid',['id'=>$voucher]);
        foreach([[100,0],[0,100]] as $j=>$money){$id=$i*2+$j+1;$ci->db->insert('tr_jurnal',['id'=>$id,'no_transaksi'=>$voucher,'jenis_transaksi'=>'Payment','sts'=>'0','id_company'=>'7','tgl_jurnal'=>'2026-10-15','debit'=>$money[0],'kredit'=>$money[1]]);$ci->db->insert('tr_jurnal_payment_reference_aliases',['run_id'=>str_repeat('a',32),'journal_id'=>$id,'original_no_transaksi'=>$approval,'voucher'=>$voucher,'approval_ids'=>json_encode([$approval]),'active'=>1,'created_at'=>'2026-10-28 10:00:00']);}
    }
    $r=new Payment_reference($ci,true);$r->prime($keys);$queryCount=$ci->db->queryCount();foreach($keys as $key)check($r->resolve($key)['voucher']===$key,'bulk aliased voucher resolves '.$key);
    check($queryCount===$ci->db->queryCount(),'205 alias groups use preloaded batches without per-row queries');
}finally{normalizationFiles($path);}
echo 'PASS Payment normalization: '.($checks-$normalizationBase)." assertions\n";
