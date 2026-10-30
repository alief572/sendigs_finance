<?php
define('BASEPATH', __DIR__);
define('DBCNL', 'consultant_fixture');
require dirname(__DIR__) . '/libraries/Kwitansi_company.php';
class CompanyResult {
    private $rows;
    function __construct($rows) { $this->rows = $rows; }
    function num_rows() { return count($this->rows); }
    function row_array() { return $this->rows[0] ?? array(); }
}
function companyCheck($ok, $message) { if (!$ok) throw new RuntimeException($message); }
class CompanySourceDb {
    public $quote = array('id_quotation'=>'Q1','company'=>'1'), $company = array('id'=>'1','nm_company'=>'STM-Vuca'), $lock = false, $fail = false;
    function query($sql, $args) {
        companyCheck((substr($sql,-11)===' FOR UPDATE')===$this->lock, 'Lock mode must reach both source queries');
        if (strpos($sql, '.kons_tr_penawaran WHERE')!==false) {
            companyCheck($args===array('Q1'), 'Quote ID comes from SPK');
            return $this->fail ? false : new CompanyResult($this->quote ? array($this->quote) : array());
        }
        companyCheck(strpos($sql,DBCNL.'.kons_tr_company WHERE')!==false,'Use Consultant master');
        companyCheck($args===array('1'),'Company comes from quote');
        return new CompanyResult($this->company ? array($this->company) : array());
    }
}
$db=new CompanySourceDb;
foreach(array(false,true) as $lock) {
    $db->lock=$lock;
    foreach(array(null,'4','1') as $spkCompany) {
        $source=Kwitansi_company::resolve($db,array('id_penawaran'=>'Q1','id_company'=>$spkCompany),$lock);
        companyCheck($source['company']['nm_company']==='STM-Vuca','Master company1, never hardcoded STM');
    }
}
foreach(array('relation','quote','company','master','name','query') as $fault) {
    $db=new CompanySourceDb; $spk=array('id_penawaran'=>'Q1','id_company'=>'4');
    if($fault==='relation')$spk['id_penawaran']=null;
    if($fault==='quote')$db->quote=null;
    if($fault==='company')$db->quote['company']=null;
    if($fault==='master')$db->company=null;
    if($fault==='name')$db->company['nm_company']='';
    if($fault==='query')$db->fail=true;
    $rejected=false;try{Kwitansi_company::resolve($db,$spk);}catch(RuntimeException $e){$rejected=true;}
    companyCheck($rejected,'Reject invalid source '.$fault);
}
echo "PASS: quote company overrides SPK, master ID1, read/lock modes and incomplete-source rejection\n";
