<?php
if (PHP_SAPI!=='cli') exit('CLI only');
define('BASEPATH',__DIR__); define('ENVIRONMENT','development');
require dirname(__DIR__,3).'/config/development/database.php';
require dirname(__DIR__).'/libraries/Invoice_audit.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
class AuditResult {
    private $rows;
    function __construct($rows) { $this->rows=$rows; }
    function result_array() { return $this->rows; }
    function row_array() { return $this->rows[0]??array(); }
}
class AuditDb {
    private $connection;
    function __construct($connection) { $this->connection=$connection; }
    function query($sql,$params=array()) {
        if (!preg_match('/^SELECT\s/i',$sql)) throw new RuntimeException('Audit accepts SELECT only');
        $statement=$this->connection->prepare($sql);
        if ($params) $statement->bind_param(str_repeat('s',count($params)),...$params);
        $statement->execute(); $rows=$statement->get_result()->fetch_all(MYSQLI_ASSOC); $statement->close(); return new AuditResult($rows);
    }
}
$config=$db['default'];
$connection=new mysqli($config['hostname'],$config['username'],$config['password'],$config['database'],$config['port']??3306);
$connection->set_charset('utf8mb4');
try {
    $connection->query('START TRANSACTION WITH CONSISTENT SNAPSHOT, READ ONLY');
    $rows=(new Invoice_audit(new AuditDb($connection)))->report();
    $capabilities=(new AuditDb($connection))->query("SELECT TABLE_SCHEMA,TABLE_NAME,ENGINE FROM information_schema.TABLES WHERE (TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('tr_invoicing','tr_jurnal','tr_penerimaan_piutang_detail','tr_invoice_detail_non_kons','kons_tr_actual_plan_tagih','kons_tr_plan_tagih_detail','ms_invoice_sequence')) OR (TABLE_SCHEMA=? AND TABLE_NAME='kons_tr_penawaran_non_konsultasi')",array($db['consultant']['database']))->result_array();
    $itemColumns=(new AuditDb($connection))->query("SELECT COLUMN_NAME,DATA_TYPE,NUMERIC_SCALE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tr_invoice_detail_non_kons' AND COLUMN_NAME IN ('qty','harga','total')")->result_array();
    $connection->rollback();
    echo json_encode(array('mode'=>'read_only','generated_at'=>date(DATE_ATOM),'transaction_tables'=>$capabilities,'item_columns'=>$itemColumns,'invoices'=>$rows),JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
} finally { $connection->close(); }
