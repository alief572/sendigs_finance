<?php
define('BASEPATH', dirname(__DIR__, 4) . '/system/');
define('APPPATH', dirname(__DIR__, 3) . '/');
define('DBCNL', 'consultant');
set_error_handler(function ($severity, $message) { throw new ErrorException($message, 0, $severity); });
function log_message($level, $message) {}
function show_error($message) { throw new RuntimeException($message); }
function load_class($name, $directory) { return null; }
class BF_Model {}
$checks = 0;
function check($ok, $message) { global $checks; $checks++; if (!$ok) throw new RuntimeException($message); }
require BASEPATH . 'database/DB.php';
require APPPATH . 'modules/jurnal_all/models/Jurnal_all_model.php';
$db = DB(['dbdriver'=>'pdo','subdriver'=>'sqlite','dsn'=>'sqlite::memory:','db_debug'=>false], true);
$db->query('CREATE TABLE tr_jurnal (id INTEGER PRIMARY KEY, no_jurnal TEXT, no_transaksi TEXT, jenis_transaksi TEXT, sts TEXT, id_company TEXT, nm_company TEXT, tgl_jurnal TEXT, coa TEXT, nm_coa TEXT, debit DECIMAL(20,2), kredit DECIMAL(20,2), keterangan TEXT, nm_divisi TEXT)');
$db->query("ATTACH DATABASE ':memory:' AS consultant");
$db->query('CREATE TABLE tr_invoicing (id TEXT PRIMARY KEY, no_invoice TEXT, nm_customer TEXT, nm_project TEXT, id_penawaran TEXT, id_spk_penawaran TEXT)');
$db->query('CREATE TABLE tr_penerimaan_piutang_detail (id_header TEXT,id_inv TEXT)');
$db->query('CREATE TABLE payment_approve (id TEXT,id_payment TEXT,no_doc TEXT,tipe TEXT)');
$db->query('CREATE TABLE consultant.kons_tr_penawaran (id_quotation TEXT,company TEXT)');
$db->query('CREATE TABLE consultant.kons_tr_spk_penawaran (id_spk_penawaran TEXT,id_company TEXT,nm_project TEXT)');
$db->query('CREATE TABLE consultant.kons_tr_penawaran_non_konsultasi (id_penawaran TEXT,id_company TEXT)');
$db->query('CREATE TABLE consultant.kons_tr_company (id TEXT,nm_company TEXT)');
$db->query('CREATE TABLE tr_expense_petty_cash (id INTEGER PRIMARY KEY,no_pencatatan TEXT)');
$db->query('CREATE TABLE tr_pelaporan_petty_cash_detail (pencatatan_id INTEGER,pelaporan_id INTEGER)');
$db->query('CREATE TABLE tr_pelaporan_petty_cash (id INTEGER PRIMARY KEY,no_pelaporan TEXT,status TEXT)');
$db->query('CREATE TABLE tr_expense (no_doc VARCHAR(100),id_kasbon VARCHAR(100),lebih_bayar DECIMAL(20,2),kurang_bayar DECIMAL(20,2),total_kasbon DECIMAL(20,2),jumlah DECIMAL(20,2))');
$db->query('CREATE TABLE tr_expense_detail (no_doc VARCHAR(100),id_kasbon VARCHAR(100),kasbon_pr_non_po_pett VARCHAR(100),kasbon DECIMAL(20,2),expense DECIMAL(20,2))');
$db->query('CREATE TABLE tr_kasbon (no_doc VARCHAR(100),id_pr VARCHAR(100),tipe_pr VARCHAR(50),id_expense_pett_pr_non_po VARCHAR(100))');
$db->query('CREATE TABLE users (id_user INTEGER,nm_lengkap VARCHAR(255))');
$db->query('CREATE TABLE rutin_non_planning_header (no_pr VARCHAR(100),created_by INTEGER)');
$db->query('CREATE TABLE material_planning_base_on_produksi (no_pr VARCHAR(100),created_by INTEGER)');
$db->query('CREATE TABLE tran_pr_header (no_pr VARCHAR(100),created_by INTEGER)');
$db->query('CREATE TABLE consultant.kons_tr_expense_report_project_header (id VARCHAR(100),id_header VARCHAR(100))');
$db->query('CREATE TABLE consultant.kons_tr_kasbon_project_header (id VARCHAR(100))');
$model = new Jurnal_all_model(); $model->db = $db;
$id=0;
function journal($type,$status='0',$number='TRX-1',$company='7',$debit='100.25',$credit='100.25') {
    global $db,$id;
    foreach([[$debit,0], [0,$credit]] as $money) $db->insert('tr_jurnal',['id'=>++$id,'no_jurnal'=>'SOURCE-'.$id,'no_transaksi'=>$number,'jenis_transaksi'=>$type,'sts'=>$status,'id_company'=>$company,'nm_company'=>$company==='4'?'VUCA':'STM','tgl_jurnal'=>'2026-10-14','coa'=>'5101-01','nm_coa'=>'Biaya khusus','debit'=>$money[0],'kredit'=>$money[1],'keterangan'=>'Detail aman {REF:REF-1}','nm_divisi'=>'Finance']);
    return $id;
}
foreach(['Invoicing','Penerimaan Piutang','Payment','Transport','Transportasi','Kasbon','Expense','Expense Report','Expense Report Consultant','Petty Cash','Refill Pettycash','Payment Hutang Petty Cash'] as $type) journal($type,'0',$type);
check($model->source('Expense Report')==='EXP','Expense Report owns one source');
check($model->tab('Refill Pettycash')==='PAY','refill shown in payment');
check($model->source('Refill Pettycash')==='PCS','refill routes to petty cash');
check($model->tab('Petty Cash')==='PCS','recording petty tab');
check($model->source('Unrelated')===null,'unrelated rejected');
// Fixture has no invoices/payment master: source eligibility must exclude unresolved legacy sources.
foreach(['INV','RCV','PAY','EXP','PCS'] as $tab) {
    $data=$model->listing(['tab'=>$tab,'year'=>2026]);
    check(isset($data['data'],$data['recordsTotal'],$data['recordsFiltered'],$data['totals']),'DataTables '.$tab);
}
$expense=journal('Expense Report',null,'NULL-EXP');
journal('Payment',null,'NULL-PAY'); journal('Petty Cash',null,'NULL-PC');
$rows=$model->groups(['tab'=>'EXP','year'=>2026]);
check(count($rows)===3,'expense NULL included and no payment duplication');
check($model->detail($expense,false)['header']['source']==='EXP','source derived from ID');
journal('Petty Cash','0','MULTI','4','90.10','90.10'); journal('Petty Cash','0','MULTI','7','80.20','80.20');
$pcs=$model->groups(['tab'=>'PCS','year'=>2026]);
check(count($pcs)===2,'multi-company petty one group and NULL excluded');
$multi=array_values(array_filter($pcs,function($r){return $r['no_transaksi']==='MULTI';}))[0];
check($multi['debit']==='170.30'&&count($model->detail($multi['id'],false)['rows'])===4,'all company sides preserved');
$page=$model->listing(['tab'=>'EXP','year'=>2026,'length'=>1,'start'=>1]);
check(count($page['data'])===1&&$page['recordsFiltered']===3,'paging');
check(count($model->groups(['tab'=>'EXP','year'=>2026,'search'=>['value'=>'Biaya khusus']]))===3,'search detail COA');
check(count($model->groups(['tab'=>'EXP','year'=>2026,'search'=>['value'=>"' OR 1=1 --"]]))===0,'injection search');
check(count($model->groups(['tab'=>'EXP','year'=>2025]))===0,'year');
check(count($model->groups(['tab'=>'EXP','year'=>2026,'month'=>'9']))===0,'month');
check($model->summary(['EXP'=>['year'=>2026]])['EXP']['count']===3,'summary grouped count');
check(count($model->export_rows(['tab'=>'EXP','year'=>2026,'length'=>1]))===6,'export all COA lines ignores pagination');
$posted=journal('Expense Report','1','HISTORY');
check(count($model->groups(['tab'=>'EXP','year'=>2026],true))===1,'legacy posted included');
$detail=$model->detail($posted,true);
check($detail['header']['posted_at']===null&&$detail['header']['posted_by']===null,'no invented posting metadata');
try{$model->detail($posted,false);check(false,'posted ID rejected');}catch(InvalidArgumentException $e){check(true,'posted ID rejected');}
try{$model->groups(['tab'=>[]]);check(false,'invalid tab');}catch(InvalidArgumentException $e){check(true,'invalid tab rejected');}
$invalid=journal('Other','0','OTHER');
try{$model->detail($invalid,false);check(false,'unrelated ID');}catch(InvalidArgumentException $e){check(true,'unrelated ID rejected');}
$db->insert('consultant.kons_tr_company',['id'=>'4','nm_company'=>'VUCA']);
$db->insert('consultant.kons_tr_penawaran',['id_quotation'=>'Q1','company'=>'4']);
$db->insert('tr_invoicing',['id'=>'INV-OK','no_invoice'=>'INV/2026/1','nm_customer'=>'PT Pelanggan','nm_project'=>'Proyek SPK','id_penawaran'=>'Q1','id_spk_penawaran'=>'SPK-1']);
$inv=journal('Invoicing',null,'INV-OK','7');
$db->insert('tr_penerimaan_piutang_detail',['id_header'=>'RCV-OK','id_inv'=>'INV-OK']);
$rcv=journal('Penerimaan Piutang','0','RCV-OK');
journal('Penerimaan Piutang',null,'RCV-NULL');
$db->insert('tr_penerimaan_piutang_detail',['id_header'=>'RCV-NULL','id_inv'=>'INV-OK']);
$invRows=$model->groups(['tab'=>'INV','year'=>2026,'company'=>'4','q'=>'PT Pelanggan']);
check(count($invRows)===1&&$invRows[0]['debit']==='100.25'&&$invRows[0]['no_referensi']==='SPK-1','invoice enrichment, company, NULL, source totals and SPK reference');
check($invRows[0]['display_no_transaksi']==='INV/2026/1'&&$invRows[0]['no_transaksi']==='INV-OK','invoice display number is separate from native posting key');
check(count($model->groups(['tab'=>'INV','year'=>2026,'q'=>'INV/2026/1']))===1,'invoice number remains searchable after reference becomes SPK');
check($model->detail($inv,false)['header']['no_transaksi']==='INV-OK'&&$model->detail($inv,false)['header']['invoice_number']==='INV/2026/1','detail retains source key and native invoice reference');
check($invRows[0]['keterangan']==='PT Pelanggan - Proyek SPK','invoice description contains only customer and project');
check($model->detail($inv,false)['rows'][0]['keterangan']==='Detail aman {REF:REF-1}','COA detail description remains authoritative source text');
check($model->export_rows(['tab'=>'INV','year'=>2026])[0]['line']['keterangan']==='Detail aman {REF:REF-1}','COA Excel descriptions remain original journal data');
check($model->export_rows(['tab'=>'INV','year'=>2026])[0]['header']['display_no_transaksi']==='INV/2026/1'&&$model->export_rows(['tab'=>'INV','year'=>2026])[0]['header']['no_referensi']==='SPK-1','Excel invoice/SPK display follows table columns');
check($model->groups(['tab'=>'RCV','year'=>2026])[0]['display_no_transaksi']==='RCV-OK'&&$model->groups(['tab'=>'RCV','year'=>2026])[0]['no_referensi']==='INV/2026/1','receipt transaction/reference mapping unchanged');
check($model->groups(['tab'=>'RCV','year'=>2026])[0]['keterangan']==='Pelunasan INV/2026/1 - PT Pelanggan','receipt description uses invoice number and customer');
check($model->detail($rcv,false)['header']['keterangan']==='Pelunasan INV/2026/1 - PT Pelanggan'&&$model->detail($rcv,false)['rows'][0]['keterangan']==='Detail aman {REF:REF-1}','receipt header display changes without modifying COA descriptions');
check(count($model->groups(['tab'=>'RCV','year'=>2026,'q'=>'Pelunasan INV/2026/1']))===1,'receipt display description is searchable');
check($model->export_rows(['tab'=>'RCV','year'=>2026])[0]['line']['keterangan']==='Detail aman {REF:REF-1}','receipt Excel COA description remains source text');
$db->where('no_transaksi','RCV-OK')->update('tr_jurnal',['sts'=>'1']);
check($model->groups(['tab'=>'RCV','year'=>2026],true)[0]['keterangan']==='Pelunasan INV/2026/1 - PT Pelanggan','posted receipt report shares description format');
$db->where('no_transaksi','RCV-OK')->update('tr_jurnal',['sts'=>'0']);
$db->where('id','INV-OK')->update('tr_invoicing',['nm_customer'=>'   ']);
check($model->groups(['tab'=>'RCV','year'=>2026])[0]['keterangan']==='Pelunasan INV/2026/1 - —','missing customer stays explicitly unavailable');
$db->where('id','INV-OK')->update('tr_invoicing',['nm_customer'=>'PT Pelanggan']);
$db->where('no_transaksi','INV-OK')->update('tr_jurnal',['sts'=>'1']);
check($model->groups(['tab'=>'INV','year'=>2026],true)[0]['keterangan']==='PT Pelanggan - Proyek SPK','posted invoice report uses the same customer/project description');
$db->where('no_transaksi','INV-OK')->update('tr_jurnal',['sts'=>null]);
$db->where('id','INV-OK')->update('tr_invoicing',['nm_project'=>null]);
check($model->groups(['tab'=>'INV','year'=>2026])[0]['keterangan']==='PT Pelanggan','missing project does not leave an empty separator or fall back to journal text');
$db->insert('consultant.kons_tr_spk_penawaran',['id_spk_penawaran'=>'SPK-1','id_company'=>'4','nm_project'=>'Project dari SPK']);
check($model->groups(['tab'=>'INV','year'=>2026])[0]['keterangan']==='PT Pelanggan - Project dari SPK','project falls back to the existing SPK relation');
$db->where('id','INV-OK')->update('tr_invoicing',['nm_customer'=>'   ','nm_project'=>' Project Utama ']);
check($model->groups(['tab'=>'INV','year'=>2026])[0]['keterangan']==='Project Utama','missing customer shows only the trimmed project');
$db->where('id','INV-OK')->update('tr_invoicing',['nm_customer'=>'PT Pelanggan','nm_project'=>'Proyek SPK']);
check(count($model->groups(['tab'=>'RCV','year'=>2026]))===1,'receipt NULL not introduced by aggregation');
check(count($model->groups(['tab'=>'INV','year'=>2026,'q'=>'SPK-1']))===1,'SPK search');
$db->insert('payment_approve',['id'=>'201','id_payment'=>'BATCH-1','no_doc'=>'RPC-2026-1','tipe'=>'refill_pettycash']);
$db->insert('payment_approve',['id'=>'202','id_payment'=>'BATCH-1','no_doc'=>'RPC-2026-2','tipe'=>'refill_pettycash']);
$refill=journal('Refill Pettycash','0','BATCH-1','4','50.15','50.15');
// A model instance is request-local, so start a fresh instance after changing fixture master data.
$model=new Jurnal_all_model();$model->db=$db;
$pay=$model->groups(['tab'=>'PAY','year'=>2026,'category'=>'Refill Petty Cash']);
check(count($pay)===1&&$pay[0]['source']==='PCS'&&$pay[0]['no_transaksi']==='BATCH-1'&&$pay[0]['display_no_transaksi']==='BATCH-1','refill display category keeps native key and resolves display voucher');
check($pay[0]['debit']==='50.15'&&strpos($pay[0]['no_referensi'],'RPC-2026-2')!==false,'payment master join cannot multiply money');
$refillApprove=journal('Refill Pettycash','0','201','4','25.00','25.00');
$model=new Jurnal_all_model();$model->db=$db;
$payApprove=$model->groups(['tab'=>'PAY','year'=>2026,'q'=>'201']);
check(count($payApprove)===1&&$payApprove[0]['no_transaksi']==='201'&&$payApprove[0]['display_no_transaksi']==='BATCH-1','payment journal with approval id displays voucher number');
check(count($model->groups(['tab'=>'PAY','year'=>2026,'q'=>'BATCH-1']))===2,'search by payment voucher number matches both native and approval journals');
check(count($model->groups(['tab'=>'PAY','year'=>2026,'q'=>'RPC-2026-1']))===2,'search by document number matches');
$db->where('no_transaksi','201')->delete('tr_jurnal');
check(count($model->detail($refill,false)['rows'])===2,'refill detail includes full group');
$db->where('id',$refill)->update('tr_jurnal',['sts'=>'1']);
try{$model->detail($refill-1,false);check(false,'part posted');}catch(InvalidArgumentException $e){check(true,'part posted group rejected');}
$db->where('id',$refill)->update('tr_jurnal',['sts'=>'0']);
check($model->options(['tab'=>'PAY','year'=>2026])['categories']!==[],'dynamic categories include existing payment types');
$db->where('id',$inv)->update('tr_jurnal',['tgl_jurnal'=>'2025-12-31']);
check(count($model->groups(['tab'=>'INV','year'=>2026]))===1&&count($model->groups(['tab'=>'INV','year'=>2025]))===1,'invoice with actual rows in each year remains discoverable without splitting the group');
check($model->groups(['tab'=>'INV','year'=>2026])[0]['debit']==='100.25'&&count($model->export_rows(['tab'=>'INV','year'=>2026]))===2,'matching period preserves full invoice totals and all exported COA rows');
check(count($model->groups(['tab'=>'INV','year'=>2026,'month'=>'10']))===1&&count($model->groups(['tab'=>'INV','year'=>2026,'month'=>'12']))===0,'invoice month matches actual lines rather than the earliest date');
check(count($model->groups(['tab'=>'PCS','year'=>2026,'company'=>'4']))===1,'petty company filter retains full multi-side group');
check($model->cents('8800000.25')===880000025&&$model->fromCents(17030)==='170.30','cents arithmetic');
check($model->cents('90071992547409.25')===9007199254740925,'cents beyond JavaScript Number precision');
try{$model->cents('999999999999999999.99');check(false,'overflow');}catch(InvalidArgumentException $e){check(true,'unsafe amount fails closed');}
$negative=journal('Expense Report','0','NEGATIVE','7','-10.25','-10.25');
journal('Expense Report','0','NEGATIVE','7','100.50','100.50');
check(!$model->detail($negative,false)['is_balance'],'negative lines disable posting even when net totals balance');
$db->where('id','INV-OK')->update('tr_invoicing',['id_spk_penawaran'=>null]);
check($model->groups(['tab'=>'INV','year'=>2025])[0]['no_referensi']==='','missing SPK reference is blank rather than invoice or native transaction key');
$db->insert('tr_invoicing',['id'=>'INTERNAL-A','no_invoice'=>'ZZZ/1','nm_customer'=>'Customer A','nm_project'=>'Project A','id_penawaran'=>'Q1','id_spk_penawaran'=>'SPK-A']);
$db->insert('tr_invoicing',['id'=>'INTERNAL-Z','no_invoice'=>'AAA/1','nm_customer'=>'Customer Z','nm_project'=>'Project Z','id_penawaran'=>'Q1','id_spk_penawaran'=>'SPK-Z']);
journal('Invoicing','0','INTERNAL-A');journal('Invoicing','0','INTERNAL-Z');
$sorted=$model->listing(['tab'=>'INV','year'=>2026,'order'=>[['column'=>1,'dir'=>'asc']]]);
check($sorted['data'][0]['display_no_transaksi']==='AAA/1'&&$sorted['data'][0]['no_transaksi']==='INTERNAL-Z','transaction sorting follows displayed invoice, not internal source key');
check(count($model->groups(['tab'=>'INV','year'=>'']))===3,'all years returns each invoice once');
check(count($model->groups(['tab'=>'INV','year'=>'','month'=>'12']))===1,'all years with month matches historical lines');
check($model->listing(['tab'=>'INV','year'=>2026])['recordsFiltered']===3&&$model->summary(['INV'=>['year'=>2026]])['INV']['count']===3,'listing and summary share inclusive invoice period selection');
check(isset($model->options(['tab'=>'INV','year'=>''])['companies']['4']),'all years filter options retain source companies');
foreach(['EXP','RCV','PAY','PCS'] as $tab) check($model->filters(['tab'=>$tab,'year'=>''])['year']==='', 'all years accepted '.$tab);
foreach(['2026 OR 1=1','0000',[]] as $year){try{$model->filters(['tab'=>'INV','year'=>$year]);check(false,'invalid year');}catch(InvalidArgumentException $e){check(true,'invalid year rejected');}}
$db->insert('tr_invoicing',['id'=>'GAP','no_invoice'=>'GAP/1','id_penawaran'=>'Q1']);
$gap=journal('Invoicing','0','GAP');$db->where('id',$gap-1)->update('tr_jurnal',['tgl_jurnal'=>'2024-01-01']);
check(count($model->groups(['tab'=>'INV','year'=>2025,'q'=>'GAP/1']))===0,'date range overlap without an actual matching line is excluded');
$db->where('no_transaksi','INV-OK')->update('tr_jurnal',['sts'=>'1']);
check(count($model->groups(['tab'=>'INV','year'=>2026],true))===1&&count($model->groups(['tab'=>'INV','year'=>''],true))===1,'posted history shares full-group invoice period matching');
check(count($model->export_rows(['tab'=>'INV','year'=>2026],true))===2,'posted period export keeps historical and selected-year lines');
foreach(['RCV'=>'Penerimaan Piutang','PAY'=>'Payment','EXP'=>'Expense Report','PCS'=>'Petty Cash'] as $tab=>$type) {
    $before=count($model->groups(['tab'=>$tab,'year'=>'']));
    $number='HIST-'.$tab;$historic=journal($type,'0',$number);
    $db->where('no_transaksi',$number)->update('tr_jurnal',['tgl_jurnal'=>'2024-02-15']);
    if($tab==='RCV')$db->insert('tr_penerimaan_piutang_detail',['id_header'=>$number,'id_inv'=>'INV-OK']);
    check(count($model->groups(['tab'=>$tab,'year'=>'']))===$before+1,'all years includes historical transaction '.$tab);
    check($model->listing(['tab'=>$tab,'year'=>'','q'=>$number])['recordsFiltered']===1&&count($model->export_rows(['tab'=>$tab,'year'=>'','q'=>$number]))===2,'all years list/search/export stay consistent '.$tab);
    check($model->summary([$tab=>['year'=>'']])[$tab]['count']===$before+1,'all years summary '.$tab);
    check(count($model->groups(['tab'=>$tab,'year'=>'','month'=>'2','q'=>$number]))===1&&count($model->groups(['tab'=>$tab,'year'=>2026,'q'=>$number]))===0,'all years month and explicit year '.$tab);
    $db->where('id',$historic)->update('tr_jurnal',['tgl_jurnal'=>'2024-03-15']);
    check(count($model->groups(['tab'=>$tab,'year'=>'','month'=>'3','q'=>$number]))===0,'non invoice all years month uses earliest group date '.$tab);
    $db->where('no_transaksi',$number)->update('tr_jurnal',['sts'=>'1']);
    check(count($model->groups(['tab'=>$tab,'year'=>'','q'=>$number],true))===1&&count($model->export_rows(['tab'=>$tab,'year'=>'','q'=>$number],true))===2,'posted all years keeps full group '.$tab);
}
$db->insert('tr_expense_petty_cash',['id'=>1,'no_pencatatan'=>'MULTI']);
$db->insert('tr_pelaporan_petty_cash',['id'=>1,'no_pelaporan'=>'RPC-2026-0001','status'=>'approved']);
$db->insert('tr_pelaporan_petty_cash',['id'=>2,'no_pelaporan'=>'RPC-2026-REJECTED','status'=>'rejected']);
foreach([1,1,2] as $report)$db->insert('tr_pelaporan_petty_cash_detail',['pencatatan_id'=>1,'pelaporan_id'=>$report]);
$pcs=$model->groups(['tab'=>'PCS','year'=>'','q'=>'RPC-2026-0001']);
check(count($pcs)===1&&$pcs[0]['no_referensi']==='RPC-2026-0001','petty recording reference resolves active report RPC and deduplicates relation');
check($pcs[0]['no_transaksi']==='MULTI'&&$pcs[0]['display_no_transaksi']==='MULTI'&&$pcs[0]['debit']==='170.30'&&count($pcs[0]['companies'])===2,'RPC enrichment preserves native key, full amount and both company sides');
check($model->detail($pcs[0]['id'],false)['header']['no_referensi']==='RPC-2026-0001'&&count($model->detail($pcs[0]['id'],false)['rows'])===4,'preview header uses RPC while preserving native COA rows');
$export=$model->export_rows(['tab'=>'PCS','year'=>'','q'=>'RPC-2026-0001']);
check(count($export)===4&&$export[0]['header']['no_referensi']==='RPC-2026-0001'&&$export[0]['line']['no_transaksi']==='MULTI','RPC export header and search retain full native details');
check(count($model->groups(['tab'=>'PCS','year'=>'','q'=>'RPC-2026-REJECTED']))===0,'rejected report does not supply reference');
check(array_values(array_filter($model->groups(['tab'=>'PCS','year'=>'']),function($row){return $row['no_transaksi']==='Petty Cash';}))[0]['no_referensi']=='','recording with no active report has blank reference rather than fabricated PCP');
$db->insert('tr_pelaporan_petty_cash',['id'=>3,'no_pelaporan'=>'RPC-2026-0002','status'=>'waiting']);
$db->insert('tr_pelaporan_petty_cash_detail',['pencatatan_id'=>1,'pelaporan_id'=>3]);
check($model->groups(['tab'=>'PCS','year'=>'','q'=>'MULTI'])[0]['no_referensi']==='RPC-2026-0001, RPC-2026-0002','multiple active reports are unique and deterministic');
$db->where('no_transaksi','MULTI')->update('tr_jurnal',['sts'=>'1']);
check($model->groups(['tab'=>'PCS','year'=>'','q'=>'RPC-2026-0001'],true)[0]['no_referensi']==='RPC-2026-0001, RPC-2026-0002','posted history resolves the same reporting reference');
$sections=$model->company_sections([
    ['id_company'=>'4','nm_company'=>'Same name','debit'=>'90071992547409.25','kredit'=>'0'],
    ['id_company'=>'7','nm_company'=>'Same name','debit'=>'0','kredit'=>'10.25'],
    ['id_company'=>'4','nm_company'=>'Same name','debit'=>'0','kredit'=>'90071992547409.25']]);
check(count($sections)===2&&count($sections[0]['rows'])===2,'company sections key by ID even when labels match and rows are interleaved');
check($sections[0]['debit']==='90071992547409.25'&&$sections[0]['is_balance']&&!$sections[1]['is_balance'],'company subtotals retain cents precision and independent balance');
check($model->company_sections([['id_company'=>'7','nm_company'=>'STM','debit'=>'0','kredit'=>'0']])===[],'zero lines do not create empty company section');
$single=$model->company_sections([['id_company'=>'7','nm_company'=>'','debit'=>'100.25','kredit'=>'100.25']]);
check(count($single)===1&&$single[0]['nm_company']==='Company 7'&&$single[0]['is_balance'],'single company with unavailable name remains clearly identified');
$offset=$model->company_sections([['id_company'=>'4','debit'=>'10.25','kredit'=>'0'],['id_company'=>'7','debit'=>'0','kredit'=>'10.25']]);
check(!$offset[0]['is_balance']&&!$offset[1]['is_balance'],'opposite company imbalances cannot cancel in individual status');
check($model->groups(['tab'=>'EXP','year'=>'','q'=>'Expense Report Consultant'])[0]['keterangan']==='Expense Report Consultant - Expense Report Consultant','consultant description is explicit source type and number');
check($model->groups(['tab'=>'EXP','year'=>'','q'=>'NULL-EXP'])[0]['no_referensi']==='-','expense without kasbon relation has literal dash reference');
$db->insert('tr_expense',['no_doc'=>'EXP-LINK','id_kasbon'=>'KB-1','lebih_bayar'=>'10.25']);
$db->insert('tr_expense_detail',['no_doc'=>'EXP-LINK','id_kasbon'=>'KB-1','kasbon'=>'100.25','expense'=>'90.00']);
$db->insert('tr_kasbon',['no_doc'=>'KB-1','id_pr'=>'PR-D','tipe_pr'=>'pr departemen']);
$db->insert('users',['id_user'=>101,'nm_lengkap'=>'Pengaju PR Department']);
$db->insert('rutin_non_planning_header',['no_pr'=>'PR-D','created_by'=>101]);
$exp=journal('Expense Report','0','EXP-LINK');
$expense=$model->groups(['tab'=>'EXP','year'=>'','q'=>'EXP-LINK'])[0];
check($expense['no_referensi']==='KB-1'&&$expense['keterangan']==='Realisasi Kasbon Pengembalian - Pengaju PR Department','standard expense resolves kasbon and original PR requester');
check($expense['debit']==='100.25'&&$expense['no_transaksi']==='EXP-LINK','metadata does not multiply totals or replace action identity');
check($model->detail($exp,false)['rows'][0]['keterangan']==='Detail aman {REF:REF-1}'&&$model->export_rows(['tab'=>'EXP','year'=>'','q'=>'EXP-LINK'])[0]['line']['keterangan']==='Detail aman {REF:REF-1}','expense native COA preview/export text remains unchanged');
$db->insert('tr_expense',['no_doc'=>'EXP-MULTI','kurang_bayar'=>'20.00']);
foreach([['KB-2','PR-S','pr stok','Pengaju Stok',102,'material_planning_base_on_produksi'],['KB-3','PR-A','pr asset','Pengaju Asset',103,'tran_pr_header']] as $kb) {
 $db->insert('tr_kasbon',['no_doc'=>$kb[0],'id_pr'=>$kb[1],'tipe_pr'=>$kb[2]]);
 $db->insert('users',['id_user'=>$kb[4],'nm_lengkap'=>$kb[3]]);$db->insert($kb[5],['no_pr'=>$kb[1],'created_by'=>$kb[4]]);
 $db->insert('tr_expense_detail',['no_doc'=>'EXP-MULTI','id_kasbon'=>$kb[0],'kasbon'=>'10.00','expense'=>'20.00']);
}
$db->insert('tr_expense_detail',['no_doc'=>'EXP-MULTI','id_kasbon'=>'KB-2','kasbon'=>'0','expense'=>'0']);
journal('Expense Report','0','EXP-MULTI');
$expense=$model->groups(['tab'=>'EXP','year'=>'','q'=>'EXP-MULTI'])[0];
check($expense['no_referensi']==='KB-2, KB-3'&&$expense['keterangan']==='Realisasi Kasbon Reimburse - Pengaju Asset, Pengaju Stok','detail-only multiple kasbons use unique numbers and PR stock/asset requesters');
$db->insert('consultant.kons_tr_expense_report_project_header',['id'=>'CONS-EXP','id_header'=>'CONS-KB']);
$db->insert('consultant.kons_tr_kasbon_project_header',['id'=>'CONS-KB']);
journal('Expense Report Consultant','0','CONS-EXP');
$expense=$model->groups(['tab'=>'EXP','year'=>'','q'=>'CONS-EXP'])[0];
check($expense['no_referensi']==='CONS-KB'&&$expense['keterangan']==='Expense Report Consultant - CONS-EXP','consultant reference uses real consultant kasbon relation');
$db->insert('tr_expense',['no_doc'=>'EXP-DANGLING','id_kasbon'=>'MISSING']);journal('Expense Report','0','EXP-DANGLING');
check($model->groups(['tab'=>'EXP','year'=>'','q'=>'EXP-DANGLING'])[0]['no_referensi']==='-','dangling kasbon is not shown as a valid reference');
$db->where('no_transaksi','EXP-LINK')->update('tr_jurnal',['sts'=>'1']);
check($model->groups(['tab'=>'EXP','year'=>'','q'=>'KB-1'],true)[0]['keterangan']==='Realisasi Kasbon Pengembalian - Pengaju PR Department','posted expense shares metadata and kasbon search');
$db->where('no_doc','EXP-MULTI')->update('tr_expense',['kurang_bayar'=>null]);
check($model->groups(['tab'=>'EXP','year'=>'','q'=>'EXP-MULTI'])[0]['keterangan']==='Realisasi Kasbon Reimburse - Pengaju Asset, Pengaju Stok','settlement direction falls back to exact detail totals');
$db->insert('tr_expense',['no_doc'=>'EXP-REVERSE']);
$db->insert('tr_kasbon',['no_doc'=>'KB-REV','id_expense_pett_pr_non_po'=>'EXP-REVERSE']);
$db->insert('tr_expense_detail',['no_doc'=>'EXP-REVERSE','kasbon'=>'10.25','expense'=>'10.25']);journal('Expense Report','0','EXP-REVERSE');
$expense=$model->groups(['tab'=>'EXP','year'=>'','q'=>'EXP-REVERSE'])[0];
check($expense['no_referensi']==='KB-REV'&&$expense['keterangan']==='Realisasi Kasbon - -','reverse kasbon relation is valid while missing PR/impas do not invent subtype or user');
$db->insert('tr_expense',['no_doc'=>'EXP-PETTY-REL']);
$db->insert('tr_expense_detail',['no_doc'=>'EXP-PETTY-REL','kasbon_pr_non_po_pett'=>'KB-2','kasbon'=>'10.00','expense'=>'5.00']);journal('Expense Report','0','EXP-PETTY-REL');
check($model->groups(['tab'=>'EXP','year'=>'','q'=>'EXP-PETTY-REL'])[0]['keterangan']==='Realisasi Kasbon Pengembalian - Pengaju Stok','detail PR petty relation supplies kasbon and requester');
$db->insert('tr_expense',['no_doc'=>'CONS-EXP','id_kasbon'=>'KB-2']);journal('Expense Report','0','CONS-EXP');
$typed=$model->groups(['tab'=>'EXP','year'=>'','q'=>'CONS-EXP']);$typedRefs=[];foreach($typed as $row)$typedRefs[$row['jenis_transaksi']]=$row['no_referensi'];
check(count($typed)===2&&$typedRefs['Expense Report']==='KB-2'&&$typedRefs['Expense Report Consultant']==='CONS-KB','standard and consultant reference namespaces cannot leak when transaction numbers coincide');
echo "PASS Jurnal All query: {$checks} assertions\n";
