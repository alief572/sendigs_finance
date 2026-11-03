<?php
require __DIR__.'/Query_test.php';
$db->query('CREATE TABLE ms_budget_rutin (id INTEGER,departement TEXT)');
$db->query('CREATE TABLE tr_pengajuan_rutin_detail (id INTEGER,no_doc TEXT,id_budget INTEGER,coa TEXT,nama TEXT,nilai DECIMAL(20,2))');
$db->data_cache=[];
$model=new Invoice_department_fixture_model();$model->db=$db;
$model->departmentDb=DB(['dbdriver'=>'pdo','subdriver'=>'sqlite','dsn'=>'sqlite::memory:','db_debug'=>false],true);
$model->departmentDb->query('CREATE TABLE departments (id TEXT,name TEXT)');
foreach([['DEP003','Quality'],['DEP025','Marketing']] as $d)$model->departmentDb->insert('departments',['id'=>$d[0],'name'=>$d[1]]);
$db->insert('payment_approve',['id'=>'PERIODIC-APP','id_payment'=>'BK-PERIODIC','no_doc'=>'PERIODIK-BUDGET','tipe'=>'periodik']);
$db->insert('ms_budget_rutin',['id'=>901,'departement'=>'DEP003']);
$db->insert('ms_budget_rutin',['id'=>902,'departement'=>'DEP025']);
foreach([[901,'Telephone A'],[902,'Telephone B']] as $b)$db->insert('tr_pengajuan_rutin_detail',['no_doc'=>'PERIODIK-BUDGET','id_budget'=>$b[0],'coa'=>'5101-01','nama'=>$b[1],'nilai'=>'100.25']);
$a=journal('Payment','0','PERIODIC-APP');
$db->where('no_transaksi','PERIODIC-APP')->update('tr_jurnal',['nm_divisi'=>'Finance & Accounting','keterangan'=>'Telephone A - OLD-REF']);
$b=journal('Payment','0','PERIODIC-APP');
$db->where('id >',$a)->where('no_transaksi','PERIODIC-APP')->update('tr_jurnal',['nm_divisi'=>'Finance & Accounting','keterangan'=>'Telephone B - OLD-REF']);
$before=$checks;$detail=$model->detail($a,false);
check($detail['header']['nm_divisi']==='Marketing, Quality','periodic header uses all budget departments instead of stored processor division');
foreach($detail['rows'] as $line){
 $expected=(float)$line['kredit']>0?'Marketing, Quality':($line['id']<=$a?'Quality':'Marketing');
 check($line['nm_divisi']===$expected,'periodic detail maps matching expense to its budget and credit to combined departments');
}
check($detail['is_balance'],'department enrichment preserves journal balance');
$export=$model->export_rows(['tab'=>'PAY','year'=>'','q'=>'PERIODIK-BUDGET'],false);
check(count($export)===4,'periodic export retains every journal line');
foreach($export as $item)check($item['line']['nm_divisi']===((float)$item['line']['kredit']>0?'Marketing, Quality':($item['line']['id']<=$a?'Quality':'Marketing')),'export department matches detail source mapping');
$db->where('no_transaksi','PERIODIC-APP')->update('tr_jurnal',['sts'=>'1','no_transaksi'=>'BK-PERIODIC']);
check($model->detail($a,true)['header']['nm_divisi']==='Marketing, Quality','posted periodic department uses budget source too');
$db->where('id',902)->update('ms_budget_rutin',['departement'=>'DEP003']);
check($model->detail($a,true)['header']['nm_divisi']==='Quality','duplicate budget departments collapse to one name');
$db->query('DELETE FROM ms_budget_rutin');
check($model->detail($a,true)['header']['nm_divisi']==='Finance & Accounting','missing budget does not invent a department or use creator');
echo 'PASS periodic budget departments: '.($checks-$before).' assertions'.PHP_EOL;
