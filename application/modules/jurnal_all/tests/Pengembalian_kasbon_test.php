<?php
require __DIR__.'/Query_test.php';
$baseChecks=$checks;
$number='ER-RETURN-TAB';
$db->insert('tr_expense',['no_doc'=>$number,'id_kasbon'=>'KS-RETURN-TAB','lebih_bayar'=>100]);
$db->insert('tr_kasbon',['no_doc'=>'KS-RETURN-TAB']);
$returnId=journal('Pengembalian Kasbon',null,$number,'7','100.25','100.25');
journal('Expense Report','0',$number,'7','200.00','200.00');
journal('Pengembalian Kasbon','0',$number,'4','50.00','50.00');
$model=new Jurnal_all_model();$model->db=$db;
check($model->source('Pengembalian Kasbon')==='EXP','return belongs to Expense source');
$rows=$model->groups(['tab'=>'EXP','year'=>2026,'q'=>$number]);
check(count($rows)===3,'same document remains separated by transaction type and company');
$returns=array_values(array_filter($rows,function($r){return $r['jenis_transaksi']==='Pengembalian Kasbon';}));
check(count($returns)===2&&$returns[0]['no_referensi']==='KS-RETURN-TAB','return resolves actual kasbon reference');
check($returns[0]['keterangan']==='Pengembalian Kasbon','return without requester uses clean description without double dash');
$namedNumber='ER-RETURN-NAMED';
$db->insert('tr_expense',['no_doc'=>$namedNumber,'id_kasbon'=>'KS-RETURN-NAMED','lebih_bayar'=>50]);
$db->insert('tr_kasbon',['no_doc'=>'KS-RETURN-NAMED','nama'=>'Laras']);
journal('Pengembalian Kasbon','0',$namedNumber,'7','50.00','50.00');
$namedReturn=$model->groups(['tab'=>'EXP','year'=>2026,'q'=>$namedNumber])[0];
check($namedReturn['keterangan']==='Pengembalian Kasbon - Laras','return with requester resolves name cleanly');
$detail=$model->detail($returnId,false);
check($detail['header']['source']==='EXP'&&$detail['is_balance']&&count($detail['rows'])===2,'preview selects return group only');
check($detail['header']['debit']==='100.25','preview does not merge Expense Report or other company');
check(count($model->export_rows(['tab'=>'EXP','year'=>2026,'q'=>$number]))===6,'export includes return and regular source lines');
$list=$model->listing(['tab'=>'EXP','year'=>2026,'q'=>$number]);
check($list['recordsFiltered']===3,'search/list count includes the new source');
$before=count($model->groups(['tab'=>'EXP','year'=>2026]));
check($model->summary(['EXP'=>['year'=>2026]])['EXP']['count']===$before,'summary uses same Expense source scope');
$db->where(['no_transaksi'=>$number,'jenis_transaksi'=>'Pengembalian Kasbon','id_company'=>'7'])->update('tr_jurnal',['sts'=>'1']);
check(count($model->groups(['tab'=>'EXP','year'=>2026,'q'=>$number]))===2,'posted return disappears from pending');
check(count($model->groups(['tab'=>'EXP','year'=>2026,'q'=>$number],true))===1,'posted report includes return');
check(count($model->export_rows(['tab'=>'EXP','year'=>2026,'q'=>$number],true))===2,'posted export preserves return details');
check($model->detail($returnId,true)['header']['no_referensi']==='KS-RETURN-TAB','posted detail retains valid kasbon reference');
check(count($model->groups(['tab'=>'PAY','year'=>2026,'q'=>$number]))===0,'return does not leak into Payment tab');
$emptyCompany=journal('Pengembalian Kasbon','0','ER-RETURN-NOCOMPANY','','10.00','10.00');
check(count($model->groups(['tab'=>'EXP','year'=>2026,'q'=>'ER-RETURN-NOCOMPANY']))===1,'return with empty historical company remains visible');
check($model->detail($emptyCompany,false)['header']['no_referensi']==='-','missing expense linkage shows unavailable reference');
// Division resolution test
$db->query('CREATE TABLE IF NOT EXISTS hris_divisions (id VARCHAR(50) PRIMARY KEY, name VARCHAR(255))');
$db->insert('hris_divisions', ['id'=>'DIV013','name'=>'IT']);
$divMap = $model->divisionMap(['DIV013', 'DEP013', 'Finance & Accounting']);
check(($divMap['DIV013'] ?? '') === 'IT', 'divisionMap resolves division code');

$divNumber = 'ER-DIV-TEST';
$db->insert('tr_expense', ['no_doc'=>$divNumber, 'id_kasbon'=>'KS-DIV-TEST', 'lebih_bayar'=>0]);
$db->insert('tr_kasbon', ['no_doc'=>'KS-DIV-TEST']);
$db->insert('tr_jurnal', [
    'id' => ++$id, 'no_jurnal' => 'SRC-DIV', 'no_transaksi' => $divNumber,
    'jenis_transaksi' => 'Expense Report', 'sts' => '0', 'id_company' => '7',
    'nm_company' => 'STM', 'tgl_jurnal' => '2026-10-28', 'coa' => '5101',
    'nm_coa' => 'Biaya', 'debit' => '100.00', 'kredit' => '0',
    'keterangan' => 'Divisi test', 'nm_divisi' => 'DIV013'
]);
$divFirstId = $id;
$db->insert('tr_jurnal', [
    'id' => ++$id, 'no_jurnal' => 'SRC-DIV-2', 'no_transaksi' => $divNumber,
    'jenis_transaksi' => 'Expense Report', 'sts' => '0', 'id_company' => '7',
    'nm_company' => 'STM', 'tgl_jurnal' => '2026-10-28', 'coa' => '1101',
    'nm_coa' => 'Kas', 'debit' => '0', 'kredit' => '100.00',
    'keterangan' => 'Divisi test credit', 'nm_divisi' => 'DIV013'
]);
$divDetail = $model->detail($divFirstId, false);
check($divDetail['header']['nm_divisi'] === 'IT', 'preview header resolves division ID to name');
check($divDetail['rows'][0]['nm_divisi'] === 'IT', 'preview row resolves division ID to name');
$divExport = $model->export_rows(['tab'=>'EXP','year'=>2026,'q'=>$divNumber]);
check($divExport[0]['line']['nm_divisi'] === 'IT', 'export resolves division ID to name');
echo 'PASS Pengembalian Kasbon Expense tab: '.($checks-$baseChecks)." assertions\n";
