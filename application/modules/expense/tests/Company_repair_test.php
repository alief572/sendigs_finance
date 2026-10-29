<?php
require __DIR__.'/Company_test.php';
require dirname(__DIR__).'/libraries/Expense_company_repair.php';

class Repair_db {
    public $core, $fail='', $dbdriver='pdo';
    public function __construct($core){$this->core=$core;}
    public function __call($method,$args){$r=$this->core->$method(...$args);return $r===$this->core?$this:$r;}
    public function query($sql,$args=[]){
        if(strpos($sql,'GET_LOCK')!==false)return $this->core->query('SELECT '.($this->fail==='lock'?0:1).' AS acquired');
        if(strpos($sql,'RELEASE_LOCK')!==false)return $this->core->query('SELECT 1');
        return $this->core->query(str_replace(' FOR UPDATE','',$sql),$args);
    }
    public function update($table,$values,$where=[]){if($this->fail==='update')return false;return $this->core->update($table,$values,$where);}
    public function trans_commit(){if($this->fail==='commit')return false;return $this->core->trans_commit();}
}
function repair_fixture(&$events){
    $core=DB(['dbdriver'=>'pdo','subdriver'=>'sqlite','dsn'=>'sqlite::memory:','db_debug'=>false],true);
    foreach([
        'CREATE TABLE tr_expense (id INTEGER PRIMARY KEY,no_doc TEXT,id_kasbon TEXT,status TEXT,id_bank_pengembalian TEXT,transfer_coa_bank TEXT,transfer_jumlah NUMERIC,transfer_tanggal TEXT)',
        'CREATE TABLE tr_expense_detail (no_doc TEXT,id_kasbon TEXT,kasbon_pr_non_po_pett TEXT)',
        'CREATE TABLE tr_kasbon (no_doc TEXT,project TEXT,id_expense_pett_pr_non_po TEXT)',
        'CREATE TABLE kons_tr_penawaran (id_quotation TEXT,company TEXT)',
        'CREATE TABLE kons_tr_company (id TEXT,nm_company TEXT)',
        'CREATE TABLE ms_bank (id TEXT,nama TEXT,rekening TEXT,coa_bank TEXT,deleted TEXT)',
        'CREATE TABLE tr_jurnal (id INTEGER PRIMARY KEY,no_transaksi TEXT,jenis_transaksi TEXT,id_company TEXT,nm_company TEXT,sts TEXT,no_jurnal TEXT,tgl_jurnal TEXT,coa TEXT,debit NUMERIC,kredit NUMERIC,keterangan TEXT)',
        'CREATE TABLE tr_jurnal_postings (no_transaksi TEXT,jenis_transaksi TEXT)'
    ] as $sql)$core->query($sql);
    $core->insert('tr_expense',['id'=>1,'no_doc'=>'ER-REPAIR','id_kasbon'=>'KS-REPAIR','status'=>'2','id_bank_pengembalian'=>'20','transfer_coa_bank'=>'1101-02-01','transfer_jumlah'=>100,'transfer_tanggal'=>'2026-10-29']);
    $core->insert('tr_kasbon',['no_doc'=>'KS-REPAIR','project'=>'Q-REPAIR']);
    $core->insert('kons_tr_penawaran',['id_quotation'=>'Q-REPAIR','company'=>'1']);
    $core->insert('kons_tr_company',['id'=>'1','nm_company'=>'STM-Vuca']);
    $core->insert('kons_tr_company',['id'=>'4','nm_company'=>'Vuca']);
    $core->insert('ms_bank',['id'=>'20','nama'=>'PT. VUCA STRATEGI BISNIS','rekening'=>'fixture-vuca','coa_bank'=>'1101-02-01','deleted'=>'0']);
    foreach([1,2,3,4] as $id)$core->insert('tr_jurnal',['id'=>$id,'no_transaksi'=>'ER-REPAIR','jenis_transaksi'=>$id<=2?'Expense Report':'Pengembalian Kasbon',
        'id_company'=>$id<=2?'1':'','nm_company'=>$id<=2?'STM-Vuca':'','sts'=>'0','no_jurnal'=>$id<=2?'AJV-1':'BUM-1','tgl_jurnal'=>'2026-10-29','coa'=>$id===3?'1101-02-01':'1103-01-15','debit'=>$id%2?100:0,'kredit'=>$id%2?0:100,'keterangan'=>'ER-REPAIR']);
    $accounting=[];
    foreach(['accounting_vuca','accounting_sustain','accounting_stm'] as $target){
        $conn=DB(['dbdriver'=>'pdo','subdriver'=>'sqlite','dsn'=>'sqlite::memory:','db_debug'=>false],true);
        $conn->query('CREATE TABLE jurnal (no_reff TEXT,keterangan TEXT)');$conn->query('CREATE TABLE javh (keterangan TEXT)');$accounting[$target]=$conn;
    }
    $db=new Repair_db($core);$events=[];
    $repair=new Expense_company_repair($db,new Expense_company($db,$core),$accounting,function($event)use(&$events){$events[]=$event;return true;});
    return [$db,$repair,$accounting];
}
[$db,$repair,$acc]=repair_fixture($events);$before=$db->query('SELECT * FROM tr_jurnal ORDER BY id')->result_array();
$manifest=$repair->dryRun();$entry=$manifest['entries'][1];
company_check(count($manifest['entries'])===2&&$entry['eligible']&&!$manifest['entries'][0]['eligible'],'dry-run scopes eligible returns and reports realization separately');
company_check($db->query('SELECT * FROM tr_jurnal ORDER BY id')->result_array()===$before&&!$events,'dry-run has no database or audit writes');
$entry=json_decode(json_encode($entry),true);
$result=$repair->apply($entry,'finance.test');
company_check($result['status']==='applied'&&count($events)===2&&$events[1]['outcome']==='committed','apply audit intent and single committed outcome');
$after=$db->query('SELECT * FROM tr_jurnal ORDER BY id')->result_array();
foreach($after as $i=>$row){$expected=$before[$i];if($i>=2){$expected['id_company']='4';$expected['nm_company']='Vuca';}company_check($row===$expected,'only company fields change '.$i);}
company_reject(function()use($repair,$entry){$repair->apply($entry,'finance.test');},'repeat old manifest rejected');
foreach(['amount','source','realization','partial_posted','filled_company','duplicate','metadata','accounting','header','tampered'] as $case){
    [$db,$repair,$acc]=repair_fixture($events);$entry=$repair->dryRun()['entries'][1];
    if($case==='amount')$db->update('tr_jurnal',['debit'=>101],['id'=>3]);
    if($case==='source')$db->update('tr_kasbon',['project'=>'MISSING'],['no_doc'=>'KS-REPAIR']);
    if($case==='realization')$db->update('tr_jurnal',['nm_company'=>'Wrong'],['id'=>1]);
    if($case==='partial_posted')$db->update('tr_jurnal',['sts'=>'1'],['id'=>3]);
    if($case==='filled_company')$db->update('tr_jurnal',['id_company'=>'1'],['id'=>3]);
    if($case==='duplicate'){$row=$db->get_where('tr_jurnal',['id'=>3])->row_array();$row['id']=5;$db->insert('tr_jurnal',$row);}
    if($case==='metadata')$db->insert('tr_jurnal_postings',['no_transaksi'=>'ER-REPAIR','jenis_transaksi'=>'Pengembalian Kasbon']);
    if($case==='accounting')$acc['accounting_stm']->insert('jurnal',['no_reff'=>'ER-REPAIR','keterangan'=>'legacy']);
    if($case==='header')$acc['accounting_sustain']->insert('javh',['keterangan'=>'Posted ER-REPAIR']);
    if($case==='tampered')$entry['company']['id']='7';
    company_reject(function()use($repair,$entry){$repair->apply($entry,'finance.test');},'stale/tampered manifest blocked '.$case);
    company_check(!$events,'rejected repair has no committed audit '.$case);
}
foreach(['lock','update','commit'] as $failure){
    [$db,$repair,$acc]=repair_fixture($events);$entry=$repair->dryRun()['entries'][1];$db->fail=$failure;
    try{$repair->apply($entry,'finance.test');throw new LogicException('failure expected');}catch(RuntimeException $error){}
    company_check($db->get_where('tr_jurnal',['id'=>3])->row_array()['id_company']==='','repair rollback '.$failure);
}
foreach(['intent','committed'] as $failure){
    [$db,$repair,$acc]=repair_fixture($events);$entry=$repair->dryRun()['entries'][1];
    $repair=new Expense_company_repair($db,new Expense_company($db,$db->core),$acc,function($event)use($failure){return $event['outcome']!==$failure;});
    try{$result=$repair->apply($entry,'finance.test');company_check($failure==='committed'&&isset($result['audit_warning']),'commit survives audit final failure');}
    catch(RuntimeException $error){company_check($failure==='intent'&&$db->get_where('tr_jurnal',['id'=>3])->row_array()['id_company']==='','audit intent failure rolls back');}
}
echo 'PASS Expense company repair: '.$checks." cumulative assertions\n";
