<?php
define('BASEPATH', true);
define('DBACC_STM', 'acc_stm'); define('DBACC_VUCA', 'acc_vuca'); define('DBACC_SUST', 'acc_sustain');
class BF_Model {}
function log_message($level, $message) {}
require dirname(__DIR__) . '/models/Jurnal_expense_posting_model.php';
$checks = 0;
function check($ok, $message) { global $checks; $checks++; if (!$ok) { throw new RuntimeException($message); } }
class PostingResult {
    private $rows;
    public function __construct($rows) { $this->rows=$rows; }
    public function row() { return $this->rows ? (object)$this->rows[0] : null; }
    public function num_rows() { return count($this->rows); }
}
class PostingDb {
    public $rows=[]; public $headers=[]; public $details=[]; public $counter=10; public $fail=''; public $cleanupFail=false; public $events=[]; public $backup; public $conditions=[]; public $ids=[]; public $affected=0;
    public function query($sql,$values) {
        $this->events[]=$sql;
        if(strpos($sql,'GET_LOCK')!==false) return new PostingResult([['acquired'=>$this->fail==='lock' ? 0 : 1]]);
        return new PostingResult([]);
    }
    public function trans_begin() { $this->events[]='begin'; $this->backup=unserialize(serialize($this->rows)); return $this->fail!=='begin'; }
    public function trans_status() { return $this->fail!=='status_transaction'; }
    public function trans_commit() { $this->events[]='commit'; return $this->fail!=='commit'; }
    public function trans_rollback() { $this->events[]='rollback'; $this->rows=$this->backup; return true; }
    public function get_where($table,$where) {
        if($this->fail==='read') return false;
        if(strpos($table,'coa_master')!==false) return new PostingResult($this->fail==='coa_missing' ? [] : [['no_perkiraan'=>$where['no_perkiraan']]]);
        if(strpos($table,'pastibisa')!==false) return new PostingResult($this->fail==='branch_missing' ? [] : [['nomorJC'=>$this->counter,'subcab'=>'A']]);
        if($this->fail==='number_exists') return new PostingResult([['nomor'=>'existing']]);
        return new PostingResult([]);
    }
    public function insert($table,$header) { $this->events[]=$table; if($this->fail==='header')return false; $this->headers[]=$header; return true; }
    public function insert_batch($table,$details) { $this->events[]=$table; $this->details=$this->fail==='detail' ? array_slice($details,0,1) : $details; return $this->fail!=='detail'; }
    public function where_in($key,$ids) { $this->ids=$ids; return $this; }
    public function where($conditions) { $this->conditions=$conditions; return $this; }
    public function update($table,$values) {
        $this->events[]='update '.$table;
        if($table==='tr_jurnal') {
            if($this->fail==='source_status')return false;
            foreach($this->rows as $row)if(in_array($row->id,$this->ids,true))$row->sts='1';
            $this->affected=$this->fail==='source_conflict' ? 0 : count($this->ids); return true;
        }
        if($this->fail==='counter')return false;
        $this->affected=$this->fail==='counter_conflict' ? 0 : 1;
        if($this->affected) $this->counter=$values['nomorJC'];
        return true;
    }
    public function affected_rows() { return $this->affected; }
    public function delete($table,$where) { $this->events[]='delete '.$table; if($this->cleanupFail)return false; if(strpos($table,'javh')!==false)$this->headers=[];else $this->details=[]; return true; }
}
class PendingExpense {
    private $db;
    public function __construct($db) { $this->db=$db; }
    public function validate_company($rows) { return Expense_company::storedCompany($rows); }
    public function get_pending_group($id,$lock=false) {
        foreach($this->db->rows as $row) {
            if(!in_array($row->sts,[null,'','0'],true))throw new InvalidArgumentException('posted');
            if(!in_array($row->jenis_transaksi,['Expense Report','Expense Report Consultant','Pengembalian Kasbon'],true))throw new InvalidArgumentException('type');
        }
        if($id<=0)throw new InvalidArgumentException('id');
        if($lock)$this->db->events[]='source FOR UPDATE';
        return $this->db->rows;
    }
}
class PostingNumber { public function get_Nomor_Jurnal_Sales($branch,$date,$company) { return '101-AJV2600011'; } }
function fixture($company='1',$type='Expense Report Consultant') {
    $db=new PostingDb();
    foreach([[1,0,8800000],[2,2500000,0],[3,1200000,0],[4,2800000,0],[5,1500000,0],[6,800000,0],[7,0,0]] as $r)$db->rows[]=(object)['id'=>$r[0],'debit'=>$r[1],'kredit'=>$r[2],'sts'=>$r[0]===1?null:'0','no_transaksi'=>'ER-2026-00031','jenis_transaksi'=>$type,'id_company'=>$company,'nm_company'=>'Company '.$company,'tgl_jurnal'=>'2026-10-14','coa'=>'5101-01-03','keterangan'=>'Expense {REF:REF-1}'];
    $m=new Jurnal_expense_posting_model();$m->db=$db;$m->Jurnal_expense_model=new PendingExpense($db);$m->Jurnal_payment_nomor_model=new PostingNumber();return [$m,$db];
}
foreach(['1'=>'acc_vuca','6'=>'acc_sustain','7'=>'acc_stm','4'=>'acc_vuca','3'=>'acc_sustain'] as $company=>$schema) {
    foreach(['Expense Report Consultant','Expense Report','Pengembalian Kasbon'] as $type) {
        [$m,$db]=fixture((string)$company,$type);$result=$m->post_journal(1,7);
        if($result['save']===1)check($result['accounting_numbers'][0]['number']===$result['nomor'],'native expense reply preserves actual generated JV');
        check($result['save']===1 && $db->counter===11,'success and counter '.$company.'/'.$type);
        check(count($db->headers)===1 && $db->headers[0]['jml']===8800000 && $db->headers[0]['jenis']==='JV','JV header and source totals');
        check(count($db->details)===6 && $db->details[0]['no_reff']==='REF-1' && $db->details[0]['keterangan']==='Expense' && $db->details[0]['stspos']===1,'zero details skipped and REF parsed');
        check(count(array_filter($db->rows,function($r){return $r->sts==='1';}))===7,'all source rows marked posted including zero');
        check(in_array($schema.'.javh',$db->events,true) && in_array($schema.'.jurnal',$db->events,true),'same accounting routes');
        check($m->post_journal(1,7)['save']===0 && count($db->headers)===1,'repeat posting refused');
    }
}
foreach(['lock','begin','read','coa_missing','branch_missing','number_exists','header','detail','source_status','source_conflict','counter','counter_conflict','status_transaction','commit'] as $failure) {
    [$m,$db]=fixture();$db->fail=$failure;$result=$m->post_journal(1,7);
    check($result['save']===0,'no false success at '.$failure);
    check(!$db->headers && !$db->details && $db->counter===10,'MyISAM compensation at '.$failure);
    check($db->rows[0]->sts===null && $db->rows[1]->sts==='0','source rollback at '.$failure);
}
foreach(['posted','other_type','unbalanced','zero','negative','invalid_id'] as $case) {
    [$m,$db]=fixture();
    if($case==='posted')$db->rows[0]->sts='1';
    if($case==='other_type')$db->rows[0]->jenis_transaksi='Payment';
    if($case==='unbalanced')$db->rows[0]->kredit=1;
    if($case==='zero')foreach($db->rows as $r){$r->debit=0;$r->kredit=0;}
    if($case==='negative')$db->rows[1]->debit=-1;
    check($m->post_journal($case==='invalid_id'?0:1,7)['save']===0 && !$db->headers,'reject '.$case);
}
[$m,$db]=fixture();$db->rows=array_slice($db->rows,0,2);$db->rows[0]->kredit='100.25';$db->rows[1]->debit='100.25';
check($m->post_journal(1,7)['save']===1 && $db->headers[0]['jml']===100.25,'cents preserved');
[$m,$db]=fixture();$db->fail='commit';$db->cleanupFail=true;
$result=$m->post_journal(1,7);
check($result['save']===0&&!$result['cleanup_ok']&&strpos($result['msg'],'administrator')!==false,'residual accounting cleanup failure is explicit');
check($db->rows[0]->sts===null,'cleanup failure still rolls back source');
foreach(['','0','99'] as $id){[$m,$db]=fixture($id);check($m->post_journal(1,7)['save']===0&&!$db->headers&&!$db->details,'unknown company has no accounting writes '.$id);}
[$m,$db]=fixture();$db->rows[1]->id_company='7';
check($m->post_journal(1,7)['save']===0&&!$db->headers,'mixed company blocked before accounting');
[$m,$db]=fixture();$db->rows[1]->nm_company='WRONG';
check($m->post_journal(1,7)['save']===0&&!$db->headers,'mixed company names blocked before accounting');
echo "PASS Jurnal Expense posting: {$checks} assertions\n";
