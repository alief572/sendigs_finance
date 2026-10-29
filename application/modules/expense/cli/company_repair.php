<?php
if (PHP_SAPI !== 'cli') exit(2);
define('BASEPATH', dirname(__DIR__,4).'/system/');
define('APPPATH', dirname(__DIR__,3).'/');
define('ENVIRONMENT','development');
function log_message($level,$message) { file_put_contents('php://stderr',$message.PHP_EOL); }
function show_error($message) { throw new RuntimeException('Koneksi/query database gagal.'); }
function load_class($name,$directory) { return null; }
function is_php($version) { return version_compare(PHP_VERSION,$version,'>='); }
require APPPATH.'config/constants.php';
require APPPATH.'config/development/database.php';
require BASEPATH.'database/DB.php';
require dirname(__DIR__).'/libraries/Expense_company_repair.php';

$options = getopt('', ['output:', 'documents:', 'apply:', 'audit:', 'actor:']);
$connections = [];
$exitCode = 0;
try {
    $apply = isset($options['apply']);
    if ($apply && (!isset($options['audit'],$options['actor']) || isset($options['documents']))) throw new DomainException('Apply memerlukan --audit dan --actor; cakupan berasal dari manifest.');
    if (!$apply && !isset($options['output'])) throw new DomainException('Dry-run memerlukan --output=manifest.json.');
    foreach (['default','consultant','accounting_vuca','accounting_sustain','accounting_stm'] as $group) {
        $settings = array_merge($db[$group], ['db_debug'=>false,'pconnect'=>false]);
        $conn = DB($settings,true);
        if (!$conn->conn_id) throw new RuntimeException('Koneksi '.$group.' tidak tersedia.');
        $connections[$group] = $conn;
        if (!$conn->query('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED')) throw new RuntimeException('Isolasi audit tidak tersedia.');
        if ((!$apply || $group !== 'default') && !$conn->query('START TRANSACTION READ ONLY')) throw new RuntimeException('Transaksi read-only tidak tersedia.');
    }
    $default = $connections['default'];
    if ($apply) {
        $engines = $default->query("SELECT TABLE_NAME,ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('tr_jurnal','tr_expense','tr_expense_detail','tr_kasbon','tr_jurnal_postings')");
        if (!$engines || $engines->num_rows() !== 5) throw new RuntimeException('Schema koreksi belum lengkap.');
        foreach ($engines->result_array() as $table) if (strtoupper($table['ENGINE']) !== 'INNODB') throw new DomainException('Koreksi memerlukan tabel sumber/metadata InnoDB.');
    }
    $audit = null;
    if ($apply) {
        $auditHandle = fopen($options['audit'],'ab');
        if (!$auditHandle) throw new RuntimeException('File audit tidak dapat dibuka.');
        $audit = function($event)use($auditHandle) {
            $line = json_encode(['at'=>date('c')] + $event, JSON_THROW_ON_ERROR).PHP_EOL;
            if (!flock($auditHandle,LOCK_EX)) return false;
            try { return fwrite($auditHandle,$line) === strlen($line) && fflush($auditHandle); }
            finally { flock($auditHandle,LOCK_UN); }
        };
    }
    $service = new Expense_company_repair($default, new Expense_company($default,$connections['consultant']), $connections, $audit);
    if ($apply) {
        $manifest = json_decode(file_get_contents($options['apply']),true,512,JSON_THROW_ON_ERROR);
        if (($manifest['version'] ?? null) !== 1 || !is_array($manifest['entries'] ?? null)) throw new DomainException('Format manifest tidak valid.');
        foreach ($manifest['entries'] as $entry) {
            if (empty($entry['eligible'])) continue;
            echo json_encode($service->apply($entry,$options['actor']),JSON_THROW_ON_ERROR),PHP_EOL;
        }
    } else {
        $documents = isset($options['documents']) ? array_values(array_filter(array_map('trim',explode(',',$options['documents'])))) : [];
        $manifest = $service->dryRun($documents);
        if (file_put_contents($options['output'],json_encode($manifest,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR).PHP_EOL) === false) throw new RuntimeException('Manifest tidak dapat disimpan.');
        echo json_encode(['groups'=>count($manifest['entries']), 'eligible'=>count(array_filter($manifest['entries'],function($entry){return $entry['eligible'];})), 'output'=>$options['output']]),PHP_EOL;
    }
} catch (Throwable $error) {
    echo json_encode(['status'=>'failed','message'=>$error instanceof DomainException ? $error->getMessage() : 'Operasi gagal; periksa koneksi, schema, manifest, dan file audit.']),PHP_EOL;
    $exitCode = 1;
} finally {
    foreach ($connections as $conn) { $conn->query('ROLLBACK'); $conn->close(); }
    if (isset($auditHandle) && is_resource($auditHandle)) fclose($auditHandle);
}
exit($exitCode);
