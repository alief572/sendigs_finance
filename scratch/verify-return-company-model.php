<?php
if(PHP_SAPI!=='cli')exit(2);
define('BASEPATH',dirname(__DIR__).'/system/');define('APPPATH',dirname(__DIR__).'/application/');define('ENVIRONMENT','development');
function log_message($level,$message){}
function show_error($message){throw new RuntimeException('Read-only database query failed');}
function load_class($name,$directory){return null;}
function is_php($version){return version_compare(PHP_VERSION,$version,'>=');}
class BF_Model{}
require APPPATH.'config/constants.php';require APPPATH.'config/development/database.php';require BASEPATH.'database/DB.php';
require APPPATH.'modules/jurnal_all/models/Jurnal_all_model.php';
class Return_readonly_loader {
    public $connections=[];private $settings;
    public function __construct($settings){$this->settings=$settings;}
    public function database($group,$return=true){
        if(isset($this->connections[$group]))return $this->connections[$group];
        $conn=DB(array_merge($this->settings[$group],['db_debug'=>false,'pconnect'=>false]),true);
        if(!$conn->query('START TRANSACTION READ ONLY'))throw new RuntimeException('Read-only unavailable');
        return $this->connections[$group]=$conn;
    }
}
$runtime=(object)['load'=>new Return_readonly_loader($db)];function &get_instance(){global $runtime;return $runtime;}
try{
    $model=new Jurnal_all_model();$model->db=$runtime->load->database('default');$result=[];
    foreach(['ER-2026-00033','ER-2026-00040','ER-2026-00041','ER-2026-00042','ER-2026-00044'] as $doc){
        $page=$model->listing(['tab'=>'EXP','year'=>'','q'=>$doc]);
        $returns=array_values(array_filter($page['data'],function($row){return $row['jenis_transaksi']==='Pengembalian Kasbon';}));
        if(count($returns)!==1||$returns[0]['id_company']!=='7'||$returns[0]['nm_company']!=='STM')throw new RuntimeException('Listing company mismatch');
        $detail=$model->detail($returns[0]['id'],false);
        if(!$detail['can_post']||!$detail['is_balance']||$detail['header']['accounting_target']!=='accounting_stm')throw new RuntimeException('Preview target or guard mismatch');
        $result[]=['document'=>$doc,'company'=>'STM','target'=>'accounting_stm','can_post'=>true,'lines'=>count($detail['rows']),'debit'=>$detail['header']['debit'],'credit'=>$detail['header']['kredit']];
    }
    file_put_contents(dirname(__DIR__).'/docs/expense-company-bank-model-2026-10-29.json',json_encode($result,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR).PHP_EOL);
    echo 'PASS 5 actual Jurnal All listings/previews:STM, balanced, company guard passed, target accounting_stm'.PHP_EOL;
}finally{foreach($runtime->load->connections as $conn){$conn->query('ROLLBACK');$conn->close();}}
