<?php
// Exercise the active controllers with in-memory fixtures; no DB or browser.
define('BASEPATH', __DIR__);
define('DBCNL', 'consultant_fixture');
set_error_handler(function ($severity, $message, $file, $line) { throw new ErrorException($message, 0, $severity, $file, $line); });
class Admin_Controller {}
class BF_Model {}
require dirname(__DIR__) . '/models/Penerimaan_uang_model.php';
require dirname(__DIR__) . '/controllers/Penerimaan_uang.php';
require dirname(__DIR__, 2) . '/invoicing/controllers/Invoicing.php';
function expectLegacy($ok, $message) { if (!$ok) throw new RuntimeException($message); }
class LegacyResult {
    private $rows;
    function __construct($rows) { $this->rows = $rows; }
    function row_array() { return $this->rows[0] ?? array(); }
    function row() { return (object)$this->row_array(); }
    function result_array() { return $this->rows; }
}
class LegacyDb {
    public $invoice, $inserted, $batches = array(), $history = array(), $journals = array(), $historyChoice = 'N';
    private $table;
    function select($s) { return $this; }
    function from($s) { $this->table = $s; return $this; }
    function where($a, $b) { return $this; }
    function group_by($a) { return $this; }
    function order_by($a, $b) { return $this; }
    function where_in($a, $b) { return $this; }
    function get() { return $this->get_where($this->table, array()); }
    function get_where($table, $where) {
        if ($table === 'tr_penerimaan_piutang_detail') return new LegacyResult($this->history);
        if ($table === 'tr_penerimaan_piutang') return new LegacyResult(array(array('pph23_dipotong'=>$this->historyChoice)));
        if ($table === 'tr_jurnal') return new LegacyResult(array_map(function ($row) { return $row + array('jenis_transaksi'=>'Penerimaan Piutang'); }, $this->journals));
        if ($table === 'coa_master') return new LegacyResult(array(array('nama'=>'Bank')));
        if (strpos($table, 'tr_invoicing') === 0) return new LegacyResult(array($this->invoice + array('ttl_kredit'=>$this->invoice['pph_jurnal'])));
        if ($table === 'coa_master a') return new LegacyResult(array(array('no_perkiraan'=>'1102-01-01','nm_coa'=>'Piutang'),array('no_perkiraan'=>'1106-01-02','nm_coa'=>'PPh 23 Disetor'),array('no_perkiraan'=>'7201-01-04','nm_coa'=>'Admin')));
        return new LegacyResult(array(array('id'=>'1','id_spk_penawaran'=>'SPK','id_detail_plan_tagih'=>'PLAN','id_penawaran'=>'Q','id_customer'=>'C','nm_customer'=>'Customer','address'=>'','id_project'=>'P','id_project_leader'=>'L','nm_project_leader'=>'Leader','id_sales'=>'S','nm_sales'=>'Sales','company'=>'1','id_company'=>'1','nm_company'=>'Vuca','coa_bank'=>'BANK','nm_coa'=>'Bank')));
    }
    function trans_begin() {}
    function trans_commit() {}
    function insert($table, $values) { if ($table === 'tr_invoicing') $this->inserted = $values; return true; }
    function insert_batch($table, $values) { $this->batches[$table] = $values; return true; }
    function update_batch($table, $values, $key) { return true; }
    function trans_status() { return true; }
    function update($table, $values, $where) { return true; }
}
class LegacyInput { public $values; function post($key = null) { return $key === null ? $this->values : $this->values[$key]; } }
class LegacyTemplate { public $data; function set($data) { $this->data = $data; } function render($name) {} }
class LegacyModel {
    public $db;
    function remaining_receipt_pph($invoice, $choice) {
        $model = (new ReflectionClass('Penerimaan_uang_model'))->newInstanceWithoutConstructor();
        $model->db = $this->db;
        return $model->remaining_receipt_pph($invoice, $choice);
    }
    function resolve_alokasi($id) { return array('is_legacy'=>false,'split'=>array('nominal'=>15000000,'id_alokasi_detail'=>1),'detail'=>array('tipe_bank'=>'1','tanggal_transaksi'=>'2026-09-07')); }
    function generate_no_invoice($company, $type) { return 'NEW/VSB/2026'; }
    function generate_id() { return 'NEW'; }
    function generate_id_invoice_jurnal($number) { return 'JOURNAL-'.$number; }
}
class LegacyAuth { function user_id() { return 'fixture'; } }
class LegacySession { function userdata($key) { return 'fixture'; } }
function legacyController($class, $db) {
    $c = (new ReflectionClass($class))->newInstanceWithoutConstructor();
    $c->db = $db; $c->input = new LegacyInput; $c->template = new LegacyTemplate;
    foreach (array('consultant','accounting') as $name) {
        $p = new ReflectionProperty($class, $name); $p->setAccessible(true); $p->setValue($c, $db);
    }
    return $c;
}
$db = new LegacyDb;
$db->invoice = array('id'=>'038','no_inv'=>'038','no_invoice'=>'038/VSB/2026','tipe_invoice'=>'1','non_kons'=>'0','id_spk_penawaran'=>'SPK','tanggal_invoice'=>'2026-09-15','total_nominal_jurnal'=>'15000000.00','dpp_lain_lain_jurnal'=>'13750000.00','ppn_jurnal'=>'0.00','pph_jurnal'=>'75000.00','tagihan_ppn_jurnal'=>'0.00','total_akhir_jurnal'=>'14925000.00','saldo_piutang'=>'14925000.00');
$c = legacyController('Penerimaan_uang', $db); $c->Penerimaan_uang_model = new LegacyModel;
$c->Penerimaan_uang_model->db = $db;
// MINARTA last installment: the previous receipt already credited all PPh.
$originalInvoice = $db->invoice;
$db->invoice = array_replace($db->invoice, array('id'=>'189','no_invoice'=>'189/STM/2026','tipe_invoice'=>'0','pph_jurnal'=>'468180.00','total_akhir_jurnal'=>'25515810.00','saldo_piutang'=>'2574990.00','tagihan_ppn_jurnal'=>'25983990.00'));
$db->history = array(array('id_header'=>'OLD'));
$db->journals = array(array('coa'=>'1106-01-02','nm_coa'=>'PPh 23 Disetor','keterangan'=>'PPh 23 Disetor - 189/STM/2026','debit'=>'0.00','kredit'=>'468180.00'));
$c->input->values = array('choose_inv'=>array('189'),'id_alokasi'=>5102,'pph23_dipotong'=>'N','ppn_dipotong'=>'Y','customer'=>'Customer','nominal_penerimaan_bank'=>'2,574,990');
$c->process_alokasi();
expectLegacy(strpos($c->template->data['hasil'], 'name="remaining_pph_1" value="0.00"') !== false, 'Final installment must not charge full invoice PPh again');
expectLegacy(strpos($c->template->data['hasil_jurnal'], 'name="kredit_1106-01-02_1" value="0"') !== false, 'Final preview PPh credit is zero');
// Save must ignore stale/tampered hidden saldo and journal values.
$db->invoice['created_date'] = '2026-08-20';
$c->auth = new LegacyAuth; $c->session = new LegacySession;
$c->input->values = array('nm_customer'=>'Customer','id_alokasi'=>5102,'ppn_dipotong'=>'Y','pph23_dipotong'=>'N','nominal_penerimaan_bank'=>'999','no_inv'=>1,'id_inv_1'=>'189','penerimaan_1'=>'2.574.990,00','biaya_admin_1'=>'0','sisa_piutang_1'=>'468.180,00','kredit_1106-01-02_1'=>'468180','kredit_1102-01-01_1'=>'2106810','debit_bank_debit'=>'999');
ob_start(); $c->save_penerimaan_piutang(); $saved = json_decode(ob_get_clean(), true);
expectLegacy($saved['status'] === 1, 'Final installment saves');
expectLegacy($db->batches['tr_penerimaan_piutang_detail'][0]['sisa_piutang'] === '0.00', 'Server computes settlement ignoring stale saldo');
$rows = $db->batches['tr_jurnal'];
expectLegacy($rows[0]['debit'] === '2574990.00' && $rows[1]['kredit'] === '2574990.00' && $rows[2]['kredit'] === '0.00', 'Server recomputes balanced final receipt journal');
$beforeBatches = $db->batches;
$c->input->values['penerimaan_1'] = '2.574.991,00';
ob_start(); $c->save_penerimaan_piutang(); $rejected = json_decode(ob_get_clean(), true);
expectLegacy($rejected['status'] === 0 && !empty($rejected['message']) && $db->batches === $beforeBatches, 'Overpayment rejected before writes');
$db->invoice['saldo_piutang'] = '2574990.50';
$c->input->values['penerimaan_1'] = '2.574.990,50';
ob_start(); $c->save_penerimaan_piutang(); $saved = json_decode(ob_get_clean(), true);
expectLegacy($saved['status'] === 1 && $db->batches['tr_jurnal'][0]['debit'] === '2574990.50', 'Server preserves receipt cents');
// Partial prior adjustment leaves only the unused portion; unrelated invoice rows are ignored.
$db->journals[0]['kredit'] = '100000.00';
$db->journals[] = array_replace($db->journals[0], array('keterangan'=>'PPh 23 Disetor - OTHER','kredit'=>'999999.00'));
expectLegacy($c->Penerimaan_uang_model->remaining_receipt_pph($db->invoice, 'N') === 36818000, 'Only unused matching PPh remains');
foreach (array('missing','duplicate','excess','choice') as $case) {
    $journals = $db->journals;
    if ($case === 'missing') $db->journals = array();
    if ($case === 'duplicate') $db->journals[] = $db->journals[0];
    if ($case === 'excess') $db->journals[0]['kredit'] = '468181.00';
    try {
        $c->Penerimaan_uang_model->remaining_receipt_pph($db->invoice, $case === 'choice' ? 'Y' : 'N');
        throw new RuntimeException('Invalid history accepted: '.$case);
    } catch (InvalidArgumentException $expected) {}
    $db->journals = $journals;
}
$db->historyChoice = 'Y';
expectLegacy($c->Penerimaan_uang_model->remaining_receipt_pph($db->invoice, 'Y') === 0, 'Deducted history has no adjustment');
$db->historyChoice = 'N';
// Actual legacy data: saved receipt snapshots exist, but all journals are absent.
$db->invoice['saldo_piutang'] = '2574990.00';
$db->history = array(array('id'=>745,'id_header'=>'OLD','penerimaan'=>'23409000.00','biaya_admin'=>'0.00','sisa_piutang'=>'2574990.00'));
$db->journals = array();
expectLegacy($c->Penerimaan_uang_model->remaining_receipt_pph($db->invoice, 'N') === 0, 'Actual legacy receipt without journals must open with zero remaining PPh');
$c->input->values = array('choose_inv'=>array('189'),'id_alokasi'=>5102,'pph23_dipotong'=>'N','ppn_dipotong'=>'Y','customer'=>'Customer','nominal_penerimaan_bank'=>'2,574,990');
ob_start(); $c->process_alokasi(); $error = ob_get_clean();
expectLegacy($error === '' && strpos($c->template->data['hasil'], 'name="remaining_pph_1" value="0.00"') !== false, 'Actual legacy preview opens');
$c->input->values = array('nm_customer'=>'Customer','id_alokasi'=>5102,'ppn_dipotong'=>'Y','pph23_dipotong'=>'N','nominal_penerimaan_bank'=>'2.574.990,00','no_inv'=>1,'id_inv_1'=>'189','penerimaan_1'=>'2.574.990,00','biaya_admin_1'=>'0','sisa_piutang_1'=>'468.180,00');
ob_start(); $c->save_penerimaan_piutang(); $saved = json_decode(ob_get_clean(), true);
expectLegacy($saved['status'] === 1 && $db->batches['tr_penerimaan_piutang_detail'][0]['sisa_piutang'] === '0.00', 'Actual legacy settlement saves with zero balance');
$validHistory = $db->history;
foreach (array('balance','negative','excess','incomplete','partial_journals') as $case) {
    if ($case === 'balance') $db->invoice['saldo_piutang'] = '2574991.00';
    if ($case === 'negative') $db->history[0]['biaya_admin'] = '-1.00';
    if ($case === 'excess') $db->history[0]['sisa_piutang'] = '2574991.00';
    if ($case === 'incomplete') unset($db->history[0]['penerimaan']);
    if ($case === 'partial_journals') $db->journals = array(array('coa'=>'1102-01-01','nm_coa'=>'Piutang','keterangan'=>'Piutang - 189/STM/2026','debit'=>'0.00','kredit'=>'22940820.00'));
    try {
        $c->Penerimaan_uang_model->remaining_receipt_pph($db->invoice, 'N');
        throw new RuntimeException('Inconsistent fallback accepted: '.$case);
    } catch (InvalidArgumentException $expected) {}
    $db->history = $validHistory; $db->invoice['saldo_piutang'] = '2574990.00'; $db->journals = array();
}
// Reconstruct multiple journal-free installments in saved ID order.
$db->history = array(
    array('id'=>746,'id_header'=>'SECOND','penerimaan'=>'23309000.00','biaya_admin'=>'0.00','sisa_piutang'=>'2574990.00'),
    array('id'=>745,'id_header'=>'FIRST','penerimaan'=>'100000.00','biaya_admin'=>'0.00','sisa_piutang'=>'25515810.00')
);
expectLegacy($c->Penerimaan_uang_model->remaining_receipt_pph($db->invoice, 'N') === 0, 'Multi installment legacy chain reconstructs PPh once');
$db->history = $validHistory;
$db->journals = array(array('coa'=>'1106-01-02','nm_coa'=>'PPh 23 Disetor','keterangan'=>'PPh 23 Disetor - 189/STM/2026','debit'=>'0.00','kredit'=>'100000.00'));
$db->invoice['tipe_invoice'] = '1';
$db->journals = array(array_replace($db->journals[0], array('coa'=>'1106-01-05','kredit'=>'100000.00')));
expectLegacy($c->Penerimaan_uang_model->remaining_receipt_pph($db->invoice, 'N') === 36818000, 'Vuca history uses its PPh COA');
$db->invoice = $originalInvoice; $db->history = array(); $db->journals = array();
foreach (array('Y','N') as $choice) {
    foreach (array('14925000.00','5000000.00','14924995.00') as $balance) {
        $db->invoice['saldo_piutang'] = $balance;
        $c->input->values = array('choose_inv'=>array('038'),'id_alokasi'=>5258,'pph23_dipotong'=>$choice,'ppn_dipotong'=>'Y','customer'=>'Customer','nominal_penerimaan_bank'=>'15,000,000');
        $before = $db->invoice; $c->process_alokasi(); $data = $c->template->data;
        expectLegacy($data['total_piutang_dagang'] == $balance, 'Preview must retain actual balance '.$balance.' choice '.$choice);
        expectLegacy(strpos($data['hasil'], '>15,000,000</td>') !== false, 'Legacy gross display');
        expectLegacy(strpos($data['hasil_jurnal'], 'name="kredit_1102-01-01_1" value="'.(int)$balance.'"') !== false, 'Preview credit matches actual balance');
        expectLegacy($data['total_piutang'] == ($choice === 'N' ? 15000000 : 14925000), 'Total invoice gross/net');
        expectLegacy($db->invoice === $before, 'Preview must not mutate invoice');
    }
}
$c->input->values = array('nm_customer'=>'Customer'); ob_start(); $c->get_inv_by_cust(); $list = ob_get_clean();
expectLegacy(substr_count($list, '>15,000,000</td>') === 2, 'Invoice list displays effective gross');
// Regular invoices and already-correct Vuca invoices keep their stored gross.
foreach (array('0','1') as $type) {
    $db->invoice['tipe_invoice'] = $type; $db->invoice['tagihan_ppn_jurnal'] = '16000000.00';
    $db->invoice['saldo_piutang'] = '4000000.00';
    $c->input->values = array('choose_inv'=>array('038'),'id_alokasi'=>5258,'pph23_dipotong'=>'N','ppn_dipotong'=>'Y','customer'=>'Customer','nominal_penerimaan_bank'=>'15,000,000');
    $c->process_alokasi();
    expectLegacy($c->template->data['total_piutang_dagang'] == 4000000, 'Existing installment remains authoritative');
    expectLegacy($c->template->data['total_piutang'] == 16000000, 'Stored nonzero gross retained');
}
// Creation must stop producing zero gross for new Vuca invoices.
$writer = legacyController('Invoicing', $db); $writer->Invoicing_model = new LegacyModel; $writer->auth = new LegacyAuth;
// An empty COA fixture keeps this focused on invoice fields, without journal writes.
class LegacyWriterDb extends LegacyDb { function get() { return new LegacyResult(array()); } }
$writer->db = new LegacyWriterDb;
$p = new ReflectionProperty('Invoicing', 'accounting'); $p->setAccessible(true); $p->setValue($writer, $writer->db);
$writer->input->values = array('id'=>'PLAN','tanggal_invoice'=>'2026-09-15','nomor_po'=>'','nomor_faktur'=>'','total_nominal'=>'15000000.00','dpp_nilai_lain'=>'13750000.00','pajak'=>'0','total_akhir'=>'15000000.00','total_nominal_jurnal'=>'15000000.00','dpp_lain_lain'=>'13750000.00','pph_jurnal'=>'75000.00','total_akhir_jurnal'=>'14925000.00');
ob_start(); $writer->save_invoice_vuca(); $response = json_decode(ob_get_clean(), true);
expectLegacy($response['status'] === 1, 'Writer succeeds');
expectLegacy($writer->db->inserted['tagihan_ppn_jurnal'] == 15000000, 'New Vuca gross must equal DPP with zero PPN');
expectLegacy($writer->db->inserted['saldo_piutang'] == 14925000, 'Writer keeps net balance');
// Receipt snapshot uses the same effective gross as the preview.
$db->invoice['tipe_invoice'] = '1'; $db->invoice['tagihan_ppn_jurnal'] = '0.00';
$db->invoice['created_date'] = '2026-09-15';
$c->auth = new LegacyAuth; $c->session = new LegacySession;
$p = new ReflectionProperty('Penerimaan_uang', 'accounting'); $p->setAccessible(true); $p->setValue($c, new LegacyWriterDb);
$c->input->values = array('nm_customer'=>'Customer','id_alokasi'=>5258,'ppn_dipotong'=>'Y','pph23_dipotong'=>'Y','nominal_penerimaan_bank'=>'4.000.000,00','no_inv'=>1,'id_inv_1'=>'038','penerimaan_1'=>'4.000.000,00','biaya_admin_1'=>'0','sisa_piutang_1'=>'0');
ob_start(); $c->save_penerimaan_piutang(); $saved = json_decode(ob_get_clean(), true);
expectLegacy($saved['status'] === 1, 'Receipt fixture saves');
expectLegacy($db->batches['tr_penerimaan_piutang_detail'][0]['tagihan_ppn'] === '15000000.00', 'Receipt snapshot effective gross');
echo "PASS: MINARTA final receipt and server journals, history validation, partial PPh, overpayment, cents, and legacy Vuca regressions\n";
