<?php
if (PHP_SAPI!=='cli') exit(2);
define('BASEPATH',dirname(__DIR__,4).'/system/');
define('APPPATH',dirname(__DIR__,3).'/');
define('ENVIRONMENT','development');
function log_message($level,$message) {}
function show_error($message) { throw new RuntimeException('Database query failed'); }
function load_class($name,$directory) { return null; }
function is_php($version) { return version_compare(PHP_VERSION,$version,'>='); }
class BF_Model {}
require APPPATH.'config/constants.php';
require APPPATH.'config/development/database.php';
require BASEPATH.'database/DB.php';
require APPPATH.'modules/jurnal_all/models/Jurnal_all_model.php';

class History_readonly_loader
{
    private $settings;
    public $connections=[];
    public function __construct($settings) { $this->settings=$settings; }
    public function database($group,$return=true)
    {
        if (isset($this->connections[$group])) return $this->connections[$group];
        $cfg=array_merge($this->settings[$group],['db_debug'=>false,'pconnect'=>false]);
        $cfg['port']=$cfg['port'] ?? 3306;
        $conn=DB($cfg,true);
        if (!$conn->conn_id || !$conn->query('START TRANSACTION READ ONLY')) throw new RuntimeException('Read-only connection unavailable: '.$group);
        return $this->connections[$group]=$conn;
    }
    public function close()
    {
        foreach ($this->connections as $conn) { $conn->query('ROLLBACK'); $conn->close(); }
    }
}
$runtime=(object)['load'=>new History_readonly_loader($db)];
function &get_instance() { global $runtime; return $runtime; }
$checks=0;
function verify_history($ok,$message)
{
    global $checks;
    $checks++;
    if (!$ok) throw new RuntimeException($message);
}
function history_rows($conn,$sql)
{
    $result=$conn->query($sql);
    if (!$result) throw new RuntimeException('Read-only evidence query failed');
    return $result->result_array();
}
function history_fingerprint($conn,$keys)
{
    $snapshot=[];
    foreach (['tr_jurnal'=>'no_transaksi','payment_approve'=>'id','tr_payment_paid'=>'id','tr_jurnal_postings'=>'no_transaksi'] as $table=>$field) {
        $snapshot[$table]=history_rows($conn,"SELECT * FROM $table WHERE $field IN ($keys) ORDER BY $field");
    }
    return hash('sha256',json_encode($snapshot,JSON_THROW_ON_ERROR));
}

$exitCode=0;
try {
    $conn=$runtime->load->database('default');
    $aliases=require dirname(__DIR__).'/config/report_payment_history.php';
    $keys=implode(',',array_map([$conn,'escape'],array_merge(array_keys($aliases),array_column($aliases,'voucher'))));
    $before=history_fingerprint($conn,$keys);
    $model=new Jurnal_all_model();$model->db=$conn;
    $filters=['tab'=>'PAY','year'=>2026,'length'=>100];
    $listing=$model->listing($filters,true);
    verify_history($listing['recordsTotal']===6,'Expected six posted payment groups');
    verify_history($listing['totals']===['debit'=>'25394927.00','kredit'=>'25394927.00'],'Six-group report totals changed');
    $expected=[
        'BK--0626-0019'=>['doc'=>'PERIODIK-2026-00039','category'=>'Periodik','accounting'=>'101BK2600004','lines'=>6],
        'BK--0426-0045'=>['doc'=>'RQ-2026-00052','category'=>'Transport','accounting'=>'101BK2600001','lines'=>5],
        'BK--0726-0040'=>['doc'=>'DPM-2026-00001','category'=>'Direct Payment','accounting'=>'101BK2600001','lines'=>23]
    ];
    $export=$model->export_rows($filters,true);$report=[];
    foreach ($expected as $native=>$e) {
        $alias=$aliases[$native];
        $found=array_values(array_filter($listing['data'],function($h)use($native){return $h['no_transaksi']===$native;}));
        verify_history(count($found)===1,'History missing/duplicated: '.$native);
        $h=$found[0];$detail=$model->detail($h['id'],true);
        foreach ([$h,$detail['header']] as $header) {
            verify_history($header['display_no_transaksi']===$alias['voucher'],'Wrong history voucher');
            verify_history($header['no_referensi']===$e['doc']&&$header['category']===$e['category'],'Wrong document/category');
            verify_history($header['source_numbers']===$e['accounting'],'Accounting reference changed');
            verify_history($header['tgl_jurnal']===$alias['tgl_jurnal']&&substr($header['posted_at'],0,10)===$alias['tgl_jurnal'],'Journal/posting date changed');
            verify_history($header['debit']===$alias['debit']&&$header['kredit']===$alias['kredit'],'History amount changed');
        }
        verify_history(count($detail['rows'])===$e['lines']&&$detail['is_balance'],'History detail/balance changed');
        foreach ([$native,$alias['voucher'],$e['doc']] as $q) verify_history(count($model->groups($filters+['q'=>$q],true))===1,'History search inconsistent');
        $filtered=$model->groups($filters+['category'=>$e['category'],'company'=>$alias['id_company']],true);
        verify_history(in_array($native,array_column($filtered,'no_transaksi'),true),'History filter inconsistent');
        $lines=array_values(array_filter($export,function($entry)use($native){return $entry['header']['no_transaksi']===$native;}));
        verify_history(count($lines)===$e['lines'],'Excel COA count changed');
        foreach ($lines as $entry) verify_history($entry['header']['display_no_transaksi']===$alias['voucher']&&$entry['header']['no_referensi']===$e['doc']&&$entry['header']['source_numbers']===$e['accounting'],'Excel history inconsistent');
        $report[]=['native'=>$native,'voucher'=>$h['display_no_transaksi'],'reference'=>$h['no_referensi'],'category'=>$h['category'],'accounting'=>$h['source_numbers'],'journal_date'=>$h['tgl_jurnal'],'posted_at'=>$h['posted_at'],'debit'=>$h['debit'],'kredit'=>$h['kredit'],'detail_lines'=>count($detail['rows'])];
    }
    verify_history(history_fingerprint($conn,$keys)===$before,'Source/payment/posting snapshot changed');
    echo json_encode(['status'=>'PASS','checks'=>$checks,'transaction'=>'READ ONLY; ROLLBACK on every connection','database'=>$db['default']['database'],'histories'=>$report,'report_totals'=>$listing['totals'],'source_payment_posting_unchanged'=>true],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR),PHP_EOL;
} catch (Throwable $error) {
    fwrite(STDERR,'History verification failed: '.($error instanceof RuntimeException?$error->getMessage():'database/runtime error code='.(int)$error->getCode()).PHP_EOL);
    $exitCode=2;
} finally {
    $runtime->load->close();
}
exit($exitCode);
