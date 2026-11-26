<?php
define('BASEPATH', __DIR__);
define('DBCNL', 'consultant_fixture');
class BF_Model {}
require dirname(__DIR__).'/models/Kwitansi_deposit_model.php';
function verifyLookup($ok, $message) { if (!$ok) throw new RuntimeException($message); }
class LookupResult {
    private $rows;
    function __construct($rows) { $this->rows=$rows; }
    function num_rows() { return count($this->rows); }
    function result_array() { return $this->rows; }
    function row_array() { return $this->rows[0] ?? array(); }
}
class LookupDb {
    public $present=true, $metadataFailure=false, $emptyCustomers=false, $whereValue, $metadataCalls=0, $fallbackCalls=0;
    function query($sql, $args=array()) {
        if (strpos($sql, 'information_schema.TABLES')!==false) {
            verifyLookup($args[0]===DBCNL, 'Schema must be bound independently');
            verifyLookup(in_array($args[1],array('kons_tr_company','kons_tr_spk_penawaran'),true),'Exact table metadata filter');
            $this->metadataCalls++;
            return $this->metadataFailure ? false : new LookupResult($this->present ? array(array('TABLE_NAME'=>$args[1])) : array());
        }
        if (strpos($sql,'SELECT * FROM '.DBCNL.'.kons_tr_penawaran WHERE')===0) return new LookupResult(array(array('id_quotation'=>'Q1','company'=>'4')));
        if (strpos($sql,'SELECT id,nm_company')===0) return new LookupResult(array(array('id'=>'4','nm_company'=>'Vuca')));
        if (strpos($sql,'SELECT nm_company')===0) return new LookupResult(array(array('nm_company'=>'Vuca')));
        if (strpos($sql,'FROM tr_invoicing')!==false) { $this->fallbackCalls++; return new LookupResult(array(array('id_customer'=>'LEGACY','nm_customer'=>'Legacy'))); }
        verifyLookup(strpos($sql,DBCNL.'.kons_tr_spk_penawaran')!==false,'Customer source is Consultant schema');
        return new LookupResult($this->emptyCustomers ? array() : array(array('id_customer'=>'C1','nm_customer'=>'Customer')));
    }
    function table_exists($table) { verifyLookup($table==='tr_invoicing','Qualified table_exists must never be used'); return true; }
    function field_exists($field,$table) { verifyLookup($table===DBCNL.'.kons_tr_spk_penawaran','Optional fields use qualified source'); return false; }
    function select($value) { return $this; }
    function from($value) { verifyLookup($value===DBCNL.'.kons_tr_spk_penawaran a','SPK source'); return $this; }
    function where($key,$value) { verifyLookup($key==='a.id_customer','ID filter'); $this->whereValue=$value; return $this; }
    function order_by($key,$direction) { return $this; }
    function get() { return new LookupResult($this->whereValue==='C1' ? array(array('id_spk_penawaran'=>'SPK1','id_penawaran'=>'Q1','id_customer'=>'C1','id_company'=>'4')) : array()); }
}
$model=(new ReflectionClass('Kwitansi_deposit_model'))->newInstanceWithoutConstructor();
$model->db=$db=new LookupDb;
verifyLookup($model->get_active_spk_by_customer('C1')[0]['nm_company']==='Vuca','SPK includes actual company');
verifyLookup($model->get_active_spk_by_customer('OTHER')===array(),'Customer isolation');
verifyLookup($model->get_active_spk_by_customer('')===array(),'Blank customer rejected');
verifyLookup($model->get_customer_list()[0]['id_customer']==='C1' && $db->fallbackCalls===0,'Customer uses SPK source');
verifyLookup($model->get_company_name('4')==='Vuca','Company name lookup');
verifyLookup($model->get_company_code('4')==='V','Existing company code formatting preserved');
foreach (array('missing','failure','empty') as $mode) {
    $model->db=$db=new LookupDb;
    $db->present=$mode!=='missing'; $db->metadataFailure=$mode==='failure'; $db->emptyCustomers=$mode==='empty';
    verifyLookup($model->get_customer_list()[0]['id_customer']==='LEGACY','Fallback for '.$mode);
    if ($mode!=='empty') {
        verifyLookup($model->get_active_spk_by_customer('C1')===array(),'No SPK on '.$mode);
        verifyLookup($model->get_company_name('4')==='','Existing company fallback on '.$mode);
    }
}
echo "PASS: cross-schema SPK/customer/company metadata, isolation, optional fields and fallback branches\n";
