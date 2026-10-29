<?php
define('BASEPATH', dirname(__DIR__, 4).'/system/');
define('APPPATH', dirname(__DIR__, 3).'/');
define('DBACC_VUCA','acc_vuca'); define('DBACC_SUST','acc_sustain'); define('DBACC_STM','acc_stm');
function log_message($level,$message) {}
function show_error($message) { throw new RuntimeException($message); }
function load_class($name,$directory) { return null; }
require BASEPATH.'database/DB.php';
require dirname(__DIR__).'/libraries/Expense_company.php';
$db=DB(['dbdriver'=>'pdo','subdriver'=>'sqlite','dsn'=>'sqlite::memory:','db_debug'=>false],true);
foreach ([
    'CREATE TABLE tr_expense (id INTEGER PRIMARY KEY,no_doc TEXT,id_kasbon TEXT)',
    'CREATE TABLE tr_expense_detail (no_doc TEXT,id_kasbon TEXT,kasbon_pr_non_po_pett TEXT)',
    'CREATE TABLE tr_kasbon (no_doc TEXT,project TEXT,id_expense_pett_pr_non_po TEXT)',
    'CREATE TABLE kons_tr_penawaran (id_quotation TEXT,company TEXT)',
    'CREATE TABLE kons_tr_company (id TEXT,nm_company TEXT)',
    'CREATE TABLE tr_jurnal (no_transaksi TEXT,jenis_transaksi TEXT,id_company TEXT,nm_company TEXT)'
] as $sql) $db->query($sql);
$checks=0;
function company_check($ok,$message) { global $checks; $checks++; if(!$ok)throw new RuntimeException($message); }
function company_reject($callback,$message) { try{$callback();}catch(DomainException $e){company_check(true,$message);return;}throw new RuntimeException($message); }
foreach (['1'=>'STM-Vuca','4'=>'Vuca','3'=>'Sustain','6'=>'STM-Sustain','7'=>'STM'] as $id=>$name) $db->insert('kons_tr_company',['id'=>$id,'nm_company'=>$name]);
$db->insert('tr_expense',['id'=>1,'no_doc'=>'ER-1','id_kasbon'=>'KS-1']);
$db->insert('tr_kasbon',['no_doc'=>'KS-1','project'=>'Q-1']);
$db->insert('kons_tr_penawaran',['id_quotation'=>'Q-1','company'=>'1']);
$service=new Expense_company($db,$db);
$company=$service->resolve('ER-1');
company_check($company['id']==='1'&&$company['name']==='STM-Vuca'&&$company['target']==='accounting_vuca','source resolves canonical company and target');
foreach (['1'=>'acc_vuca','4'=>'acc_vuca','3'=>'acc_sustain','6'=>'acc_sustain','7'=>'acc_stm'] as $id=>$schema) company_check(Expense_company::schema($id)===$schema,'route '.$id);
foreach (['','0','99',' 1',null,[]] as $id) company_reject(function()use($id){Expense_company::target($id);},'unsupported ID rejected');
company_reject(function()use($service){$service->resolveKasbons([['attack']]);},'malformed reference array rejected explicitly');
$db->insert('tr_expense_detail',['no_doc'=>'ER-1','id_kasbon'=>'KS-2']);
$db->insert('tr_kasbon',['no_doc'=>'KS-2','project'=>'Q-1']);
company_check(count($service->resolve('ER-1')['evidence'])===2,'all header/detail kasbons resolved');
$db->update('tr_kasbon',['project'=>'Q-2'],['no_doc'=>'KS-2']);
$db->insert('kons_tr_penawaran',['id_quotation'=>'Q-2','company'=>'4']);
company_reject(function()use($service){$service->resolve('ER-1');},'different company IDs blocked even within same TRAS');
$db->delete('tr_expense_detail',['no_doc'=>'ER-1']);
$db->insert('tr_kasbon',['no_doc'=>'KS-3','project'=>'Q-2','id_expense_pett_pr_non_po'=>'ER-1']);
company_reject(function()use($service){$service->resolve('ER-1');},'reverse kasbon link also checked');
$db->delete('tr_kasbon',['no_doc'=>'KS-3']);
$db->insert('kons_tr_penawaran',['id_quotation'=>'Q-1','company'=>'4']);
company_reject(function()use($service){$service->resolve('ER-1');},'ambiguous quotation blocked');
$db->delete('kons_tr_penawaran',['id_quotation'=>'Q-1','company'=>'4']);
$db->update('tr_kasbon',['project'=>'MISSING'],['no_doc'=>'KS-1']);
company_reject(function()use($service){$service->resolve('ER-1');},'missing project does not select first master company');
$db->update('tr_kasbon',['project'=>'Q-1'],['no_doc'=>'KS-1']);
$rows=[['id_company'=>'1','nm_company'=>'STM-Vuca','no_transaksi'=>'ER-1','jenis_transaksi'=>'Expense Report']];
$db->insert('tr_jurnal',array_merge($rows[0],['jenis_transaksi'=>'Expense Report']));
company_check($service->validateJournal($rows)['id']==='1','stored company matches proven source');
$rows[0]['id_company']='4';$rows[0]['nm_company']='Vuca';
company_reject(function()use($service,$rows){$service->validateJournal($rows);},'stored fallback company cannot override source');
$rows[0]['id_company']='1';$rows[0]['nm_company']='wrong';
company_reject(function()use($service,$rows){$service->validateJournal($rows);},'stored name mismatch blocked');
$rows[0]['nm_company']='STM-Vuca';$rows[]=array_merge($rows[0],['id_company'=>'7','nm_company'=>'STM']);
company_reject(function()use($service,$rows){$service->validateJournal($rows);},'mixed company rows blocked');
$db->insert('tr_expense',['id'=>2,'no_doc'=>'ER-NONE','id_kasbon'=>'']);
company_reject(function()use($service){$service->resolve('ER-NONE');},'no source blocks');
company_reject(function()use($service){$service->resolve('DOES-NOT-EXIST');},'no expense blocks');
$db->insert('tr_expense',['id'=>3,'no_doc'=>'ER-1','id_kasbon'=>'KS-1']);
company_reject(function()use($service){$service->resolve('ER-1');},'duplicate source blocks');
echo 'PASS Expense company: '.$checks." assertions\n";
