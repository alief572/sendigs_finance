<?php
/** Read-only receipt diagnosis. Usage: php diagnose_receipt.php invoice-number */
if (PHP_SAPI !== 'cli') exit('CLI only');
define('BASEPATH', __DIR__); define('ENVIRONMENT','development');
require dirname(__DIR__, 3).'/config/development/database.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$config=$db['default'];
$conn=new mysqli($config['hostname'],$config['username'],$config['password'],$config['database'],$config['port']??3306);
$conn->set_charset('utf8mb4');
function diagnosticRows($conn,$sql,$params) {
 $stmt=$conn->prepare($sql);$stmt->bind_param(str_repeat('s',count($params)),...$params);$stmt->execute();return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
class BF_Model {}
require dirname(__DIR__).'/models/Penerimaan_uang_model.php';
class DiagnosticResult {
 private $rows;
 function __construct($rows) { $this->rows=$rows; }
 function result_array() { return $this->rows; }
}
class DiagnosticDb {
 private $conn;
 function __construct($conn) { $this->conn=$conn; }
 function get_where($table,$where) {
  if (!in_array($table,array('tr_penerimaan_piutang_detail','tr_penerimaan_piutang','tr_jurnal'),true)) throw new RuntimeException('Unexpected diagnostic table');
  $clauses=array(); foreach(array_keys($where) as $column) {
   if (!in_array($column,array('id_inv','no_surat','no_transaksi','jenis_transaksi'),true)) throw new RuntimeException('Unexpected diagnostic column');
   $clauses[]=$column.' = ?';
  }
  return new DiagnosticResult(diagnosticRows($this->conn,'SELECT * FROM '.$table.' WHERE '.implode(' AND ',$clauses),array_values($where)));
 }
}
$conn->query('START TRANSACTION WITH CONSISTENT SNAPSHOT, READ ONLY');
try {
 $invoices=diagnosticRows($conn,'SELECT id,no_invoice,tipe_invoice,pph_jurnal,total_akhir_jurnal,saldo_piutang FROM tr_invoicing WHERE no_invoice=?',array($argv[1]??'189/STM/2026'));
 $output=array('invoices'=>$invoices);
 foreach($invoices as $i) {
 $details=diagnosticRows($conn,'SELECT d.id,d.id_header,d.id_inv,d.penerimaan,d.biaya_admin,d.sisa_piutang,h.pph23_dipotong FROM tr_penerimaan_piutang_detail d LEFT JOIN tr_penerimaan_piutang h ON h.no_surat=d.id_header WHERE d.id_inv=?',array($i['id']));
 $output['receipts']=$details;
 foreach($details as $d) $output['journals'][$d['id_header']]=diagnosticRows($conn,'SELECT no_transaksi,jenis_transaksi,coa,nm_coa,keterangan,debit,kredit,sts FROM tr_jurnal WHERE no_transaksi=?',array($d['id_header']));
 $model=(new ReflectionClass('Penerimaan_uang_model'))->newInstanceWithoutConstructor();
 $model->db=new DiagnosticDb($conn);
 $remaining=$model->remaining_receipt_pph($i,'N');
 $output['active_model_check']=array('remaining_pph'=>Penerimaan_calculation::decimal($remaining));
 $calculation=Penerimaan_calculation::calculate(Penerimaan_calculation::cents($i['saldo_piutang']),$remaining,'N',Penerimaan_calculation::cents($i['saldo_piutang']),0);
 foreach($calculation as $key=>$value) $output['active_model_check'][$key]=Penerimaan_calculation::decimal($value);
 }
 echo json_encode($output,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;
} finally { $conn->rollback(); }
