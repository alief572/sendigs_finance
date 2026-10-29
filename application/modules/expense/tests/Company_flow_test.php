<?php
require __DIR__.'/Expense_report_updated_test.php';

function company_flow_fixture($status='4') {
    $controller=(new ReflectionClass('Expense'))->newInstanceWithoutConstructor();
    $controller->db=new MockDb();$controller->load=new CompanyFlowLoader($controller);
    $controller->auth=new MockAuth();$controller->auth->username='finance';$controller->input=new MockInput();
    $controller->db->tables['tr_expense'][1]=['id'=>1,'no_doc'=>'ER-1','id_kasbon'=>'KB-001','nama'=>'user','status'=>$status,'sts_finance'=>'1',
        'total_kasbon'=>1000000,'jumlah'=>750000,'lebih_bayar'=>250000,'transfer_coa_bank'=>'1101-02-01','transfer_tanggal'=>'2026-10-29','transfer_jumlah'=>250000];
    $controller->db->tables['tr_expense_detail']['ER-1']=[['no_doc'=>'ER-1','coa'=>'5101-01-01','expense'=>750000,'kasbon'=>1000000]];
    if($status==='4')$controller->db->tables['tr_jurnal'][]=['no_transaksi'=>'ER-1','jenis_transaksi'=>'Expense Report','id_company'=>'1','nm_company'=>'STM-Vuca'];
    $controller->input->posts=['id'=>1,'chk_bukti'=>1,'chk_mutasi'=>1,'chk_nominal'=>1,'id_company'=>'7','nm_company'=>'ATTACK'];
    return $controller;
}
function company_flow_call($controller,$method) { ob_start(); if($method==='approve')$controller->approve(1);else $controller->$method();return json_decode(ob_get_clean(),true); }
foreach(['approve'=>'1','verifikasi_pengembalian_approve'=>'4'] as $method=>$status) {
    foreach(['lock','begin','insert','update','transaction','commit','missing_source','wrong_realization'] as $failure) {
        if($failure==='wrong_realization' && $method==='approve')continue;
        $c=company_flow_fixture($status);
        if($failure==='missing_source'){if($method==='approve')$c->db->tables['tr_kasbon']['KB-001']['project']=null;else $c->db->tables['ms_bank']=[];}
        elseif($failure==='wrong_realization')$c->db->tables['tr_jurnal'][0]['id_company']='7';
        else $c->db->fail=$failure;
        $before=$c->db->tables;
        $r=company_flow_call($c,$method);
        testCheck($r['save']===false&&$c->db->tables===$before&&!$c->db->inserted_jurnal,$method.' rolls back '.$failure);
    }
    $c=company_flow_fixture($status);$r=company_flow_call($c,$method);
    testCheck($r['save']===true,$method.' succeeds with verified source');
    foreach($c->db->inserted_jurnal as $line)testCheck($line['id_company']===($method==='approve'?'1':'4')&&$line['nm_company']===($method==='approve'?'STM-Vuca':'Vuca'),$method.' ignores client company');
    $count=count($c->db->inserted_jurnal);$r=company_flow_call($c,$method);
    testCheck(!$r['save']&&count($c->db->inserted_jurnal)===$count,$method.' repeat cannot duplicate journal');
}
$c=company_flow_fixture('1');$c->input->posts=['no_doc'=>'ER-1','total_expense'=>750000,'total_kasbon'=>1000000,'id_kasbon'=>['KB-002'],'id_company'=>'7'];
$c->db->tables['tr_kasbon']['KB-001']['project']=null;
$r=company_flow_call($c,'set_jurnal_expense');
testCheck($r['status']===0&&$r['hasil']==='','preview cannot override missing stored source with request kasbon');
echo "PASS Expense company flow and fault regression\n";
