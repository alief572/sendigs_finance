<?php
define('BASEPATH', __DIR__);
require dirname(__DIR__).'/libraries/Invoice_vuca.php';
class VucaResult {
    private $rows;
    function __construct($rows) { $this->rows=$rows; }
    function row_array() { return $this->rows[0]??null; }
    function result_array() { return $this->rows; }
}
class VucaMappingDb {
    public $mapping, $locked=false;
    function query($sql,$params) { $this->locked=strpos($sql,'FOR UPDATE')!==false; return new VucaResult($this->mapping?array($this->mapping):array()); }
}
class VucaMasterDb {
    public $rows;
    function query($sql) { return new VucaResult($this->rows); }
}
$checks=0;
function vucaCheck($ok,$message) { global $checks; $checks++; if(!$ok)throw new RuntimeException($message); }
function vucaReject($callback,$message) { try {$callback();} catch(RuntimeException $error) {vucaCheck(true,$message);return;} catch(InvalidArgumentException $error) {vucaCheck(true,$message);return;} throw new RuntimeException($message); }
$db=new VucaMappingDb; $master=new VucaMasterDb;
$codes=array('sales_consulting'=>'4101-01-01','sales_inhouse'=>'4101-01-02','sales_public'=>'4101-01-03','sales_hse'=>'4101-01-04');
$db->mapping=array('owner_company_id'=>'4','accounts'=>json_encode($codes));
$master->rows=array(); foreach($codes as $role=>$code)$master->rows[]=array('no_perkiraan'=>$code,'nama'=>$role);
$accounts=Invoice_vuca::sales_accounts($db,$master,'4',true);
vucaCheck(count($accounts)===4 && $db->locked,'Four revenue roles without VAT mappings, with locking');
foreach($codes as $role=>$code)vucaCheck($accounts[$role]['code']===$code,'Role resolves to configured Vuca master');
foreach(array(null,array('owner_company_id'=>'7','accounts'=>json_encode($codes)),array('owner_company_id'=>'4','accounts'=>'broken'),array('owner_company_id'=>'4','accounts'=>json_encode(array_slice($codes,0,3)))) as $bad){$db->mapping=$bad;vucaReject(function()use($db,$master){Invoice_vuca::sales_accounts($db,$master,'4');},'Reject missing/foreign/incomplete mapping');}
$db->mapping=array('owner_company_id'=>'4','accounts'=>json_encode($codes));
$saved=$master->rows;
$master->rows[]= $master->rows[0];vucaReject(function()use($db,$master){Invoice_vuca::sales_accounts($db,$master,'4');},'Reject ambiguous master');
$master->rows=array_slice($saved,0,3);vucaReject(function()use($db,$master){Invoice_vuca::sales_accounts($db,$master,'4');},'Reject missing account');
$master->rows=$saved;$bad=$codes;$bad['sales_hse']='1102-01-01';$db->mapping['accounts']=json_encode($bad);
vucaReject(function()use($db,$master){Invoice_vuca::sales_accounts($db,$master,'4');},'Reject sales using fixed account');
$lines=array(array('coa'=>'1102-01-01','kredit'=>'0'),array('coa'=>'4101-01-04','kredit'=>'1000000','nm_coa'=>'HSE'));
vucaCheck(Invoice_vuca::saved_revenue($lines)['coa']==='4101-01-04','Retain historical chosen COA');
vucaReject(function(){Invoice_vuca::saved_revenue(array());},'Reject absent historical revenue');
$lines[]=$lines[1];vucaReject(function()use($lines){Invoice_vuca::saved_revenue($lines);},'Reject ambiguous historical revenue');
echo "PASS: $checks Vuca mapping checks\n";
