<?php
require __DIR__ . '/legacy_fixture.php';
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
// Form real hanya mengirim id_customer, bukan nama: nama customer harus diambil dari invoice yang dicentang.
$db->invoice['nm_customer'] = 'Customer';
$c->input->values = array('choose_inv'=>array('189'),'id_alokasi'=>5102,'pph23_dipotong'=>'N','ppn_dipotong'=>'Y','nominal_penerimaan_bank'=>'2,574,990');
$c->process_alokasi();
expectLegacy(($c->template->data['nm_customer'] ?? null) === 'Customer', 'Legacy preview resolves customer name from the selected invoice when the form posts only id_customer');
// Save must ignore stale/tampered hidden saldo and journal values.
$db->invoice['created_date'] = '2026-08-20';
$c->auth = new LegacyAuth; $c->session = new LegacySession;
$c->input->values = array('nm_customer'=>'Customer','id_alokasi'=>5102,'ppn_dipotong'=>'Y','pph23_dipotong'=>'N','nominal_penerimaan_bank'=>'999','no_inv'=>1,'id_inv_1'=>'189','penerimaan_1'=>'2.574.990,00','biaya_admin_1'=>'0','sisa_piutang_1'=>'0','kredit_1106-01-02_1'=>'468180','kredit_1102-01-01_1'=>'2106810','debit_bank_debit'=>'999');
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
$c->input->values = array('nm_customer'=>'Customer','id_alokasi'=>5102,'ppn_dipotong'=>'Y','pph23_dipotong'=>'N','nominal_penerimaan_bank'=>'2.574.990,00','no_inv'=>1,'id_inv_1'=>'189','penerimaan_1'=>'2.574.990,00','biaya_admin_1'=>'0','sisa_piutang_1'=>'0');
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
        expectLegacy($data['total_piutang_dagang'] == $balance + 75000, 'Preview shows gross basis (saldo + unused PPh budget) '.$balance.' choice '.$choice);
        expectLegacy(strpos($data['hasil'], '>15,000,000</td>') !== false, 'Legacy gross display');
        expectLegacy(strpos($data['hasil_jurnal'], 'name="kredit_1102-01-01_1" value="'.((int)$balance + 75000).'"') !== false, 'Preview credit matches gross balance');
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
    expectLegacy($c->template->data['total_piutang_dagang'] == 4075000, 'Existing installment stays authoritative (saldo + unused PPh budget)');
    expectLegacy($c->template->data['total_piutang'] == 16000000, 'Stored nonzero gross retained');
}
// Creation must stop producing zero gross for new Vuca invoices.
$writer = legacyController('Invoicing', $db); $writer->Invoicing_model = new LegacyModel; $writer->auth = new LegacyAuth;
// The active Vuca endpoint now validates master/mapping and writes its journal server-side.
class LegacyWriterDb extends LegacyDb {
    public $db_debug=false;
    private $selectedCodes=array();
    function masterRows() {
        $rows=array();foreach(array('1102-01-01','2104-01-07','1106-01-05','4101-01-01','4101-01-02','4101-01-03','4101-01-04') as $code)$rows[]=array('no_perkiraan'=>$code,'nama'=>$code,'nm_coa'=>$code);return $rows;
    }
    function query($sql,$params=array()) {
        if(strpos($sql,'information_schema.TABLES')!==false)return new LegacyResult(array(array('ENGINE'=>'InnoDB')));
        if(strpos($sql,'SELECT no_perkiraan,nama FROM coa_master')===0)return new LegacyResult($this->masterRows());
        if(strpos($sql,'FROM ms_invoice_coa_mapping')!==false)return new LegacyResult(array(array('owner_company_id'=>'4','accounts'=>json_encode(array('sales_consulting'=>'4101-01-01','sales_inhouse'=>'4101-01-02','sales_public'=>'4101-01-03','sales_hse'=>'4101-01-04')))));
        if(strpos($sql,'SELECT id FROM tr_invoicing')===0)return new LegacyResult(array());
        if(strpos($sql,'FROM kons_tr_plan_tagih_detail')!==false)return new LegacyResult(array(array('id'=>1,'nominal_payment'=>'15000000.00')));
        if(strpos($sql,'FROM kons_tr_actual_plan_tagih')!==false)return new LegacyResult(array(array('id'=>'PLAN','id_detail_plan_tagih'=>1,'id_spk_penawaran'=>'SPK','id_penawaran'=>'Q','nominal_payment'=>'15000000.00','sts_invoice'=>0)));
        if(strpos($sql,'kons_tr_spk_penawaran')!==false)return parent::get_where('kons_tr_spk_penawaran',array());
        if(strpos($sql,'kons_tr_penawaran')!==false)return new LegacyResult(array(array('company'=>4)));
        if(strpos($sql,'kons_tr_company')!==false)return new LegacyResult(array(array('id'=>4,'nm_company'=>'Vuca')));
        if(strpos($sql,'kons_master_konsultasi_header')!==false)return new LegacyResult(array(array('nm_paket'=>'Project')));
        return parent::query($sql,$params);
    }
    function where_in($field,$codes) { $this->selectedCodes=$codes; return $this; }
    function get() { return new LegacyResult(array_values(array_filter($this->masterRows(),function($row){return in_array($row['no_perkiraan'],$this->selectedCodes,true);}))); }
    function trans_begin(){return true;}
    function trans_commit(){return true;}
    function trans_rollback(){return true;}
}
function log_message($level,$message) { $GLOBALS['legacy_logs'][]=$message; }
$writer->db = new LegacyWriterDb;
$writer->load=new class {function library($name){throw new RuntimeException('Fixture audit service unavailable');}};
$p = new ReflectionProperty('Invoicing', 'accounting_vuca'); $p->setAccessible(true); $p->setValue($writer, $writer->db);
$writer->input->values = array('id'=>'PLAN','tanggal_invoice'=>'2026-09-15','nomor_po'=>'PO','nomor_faktur'=>'FAKTUR','sales_role'=>'sales_consulting','total_nominal'=>'15000000.00','dpp_nilai_lain'=>'13750000.00','pajak'=>'0','total_akhir'=>'15000000.00','total_nominal_jurnal'=>'15000000.00','dpp_lain_lain'=>'13750000.00','pph_jurnal'=>'75000.00','total_akhir_jurnal'=>'14925000.00');
ob_start(); $writer->save_invoice_vuca(); $response = json_decode(ob_get_clean(), true);
expectLegacy($response['status'] === 1, 'Writer succeeds');
expectLegacy($writer->db->inserted['tagihan_ppn_jurnal'] == 15000000, 'New Vuca gross must equal DPP with zero PPN');
expectLegacy($writer->db->inserted['saldo_piutang'] == 14925000, 'Writer keeps net balance');
// Receipt snapshot uses the same effective gross as the preview.
$db->invoice['tipe_invoice'] = '1'; $db->invoice['tagihan_ppn_jurnal'] = '0.00';
$db->invoice['created_date'] = '2026-09-15';
$c->auth = new LegacyAuth; $c->session = new LegacySession;
$p = new ReflectionProperty('Penerimaan_uang', 'accounting'); $p->setAccessible(true); $p->setValue($c, new LegacyWriterDb);
$c->input->values = array('nm_customer'=>'Customer','id_alokasi'=>5258,'ppn_dipotong'=>'Y','pph23_dipotong'=>'Y','nominal_penerimaan_bank'=>'4.000.000,00','no_inv'=>1,'id_inv_1'=>'038','penerimaan_1'=>'4.075.000,00','biaya_admin_1'=>'0','sisa_piutang_1'=>'0');
ob_start(); $c->save_penerimaan_piutang(); $saved = json_decode(ob_get_clean(), true);
expectLegacy($saved['status'] === 1, 'Receipt fixture saves');
expectLegacy($db->batches['tr_penerimaan_piutang_detail'][0]['tagihan_ppn'] === '15000000.00', 'Receipt snapshot effective gross');
// ---- Lebih Bayar invoice lama: preview, simpan, voucher, penolakan, rollback ----
$db->deposit = true; $db->companyId = '7';
$lebihInvoice = array_replace($originalInvoice, array('id'=>'189','no_invoice'=>'189/STM/2026','tipe_invoice'=>'0','pph_jurnal'=>'468180.00','total_akhir_jurnal'=>'25515810.00','saldo_piutang'=>'2574990.00','tagihan_ppn_jurnal'=>'25983990.00','created_date'=>'2026-08-20','id_customer'=>'C-ID','nm_customer'=>'Customer'));
$db->invoice = $lebihInvoice; $db->history = array(array('id_header'=>'OLD')); $db->historyChoice = 'N';
$db->journals = array(array('coa'=>'1106-01-02','nm_coa'=>'PPh 23 Disetor','keterangan'=>'PPh 23 Disetor - 189/STM/2026','debit'=>'0.00','kredit'=>'468180.00'));
$c->auth = new LegacyAuth; $c->session = new LegacySession;
$p = new ReflectionProperty('Penerimaan_uang', 'accounting'); $p->setAccessible(true); $p->setValue($c, $db);
$previewPost = array('choose_inv'=>array('189'),'id_alokasi'=>5102,'pph23_dipotong'=>'N','ppn_dipotong'=>'Y','nominal_penerimaan_bank'=>'15,000,000');
$c->input->values = $previewPost; $c->process_alokasi();
expectLegacy($c->template->data['lebih_eligible'] === true, 'Lebih Bayar is enabled for a journal-owner company');
expectLegacy(strpos($c->template->data['hasil'], 'name="lebih_bayar_1"') !== false && strpos($c->template->data['hasil'], ' data-lebih readonly ') === false, 'Preview renders an editable Lebih Bayar column');
expectLegacy(strpos($c->template->data['hasil_jurnal'], 'tr_jurnal_lebih') !== false && strpos($c->template->data['hasil_jurnal'], '2102-01-02') !== false, 'Preview journal has the Deposit Customer row');
$db->companyId = '2'; $c->input->values = $previewPost; $c->process_alokasi();
expectLegacy($c->template->data['lebih_eligible'] === false && strpos($c->template->data['hasil'], ' data-lebih readonly ') !== false, 'Lebih Bayar is locked for a company that is not a journal owner');
$db->companyId = '7';

$lebihPost = array('nm_customer'=>'Customer','id_alokasi'=>5102,'ppn_dipotong'=>'Y','pph23_dipotong'=>'N','nominal_penerimaan_bank'=>'999','no_inv'=>1,'id_inv_1'=>'189',
    'penerimaan_1'=>'2,574,990.00','biaya_admin_1'=>'0','sisa_piutang_1'=>'0','lebih_bayar_1'=>'12,425,010.00');
$saveLebih = function ($overrides = array()) use ($c, &$lebihPost) {
    $c->input->values = array_replace($lebihPost, $overrides); ob_start(); $c->save_penerimaan_piutang(); return json_decode(ob_get_clean(), true);
};
$db->batches = array(); $db->inserts = array();
$saved = $saveLebih();
expectLegacy($saved['status'] === 1, 'Receipt with Lebih Bayar saves (browser format 12,425,010.00): '.json_encode($saved));
$jr = $db->batches['tr_jurnal']; $dr = 0; $cr = 0; foreach ($jr as $line) { $dr += (float)$line['debit']; $cr += (float)$line['kredit']; }
expectLegacy($jr[0]['debit'] === '15000000.00', 'Bank debit = receipt + Lebih Bayar');
expectLegacy(end($jr)['coa'] === '2102-01-02' && end($jr)['kredit'] === '12425010.00' && abs($dr - $cr) < 0.005, 'Deposit Customer credit balances the journal');
$detailRow = $db->batches['tr_penerimaan_piutang_detail'][0]; $snap = json_decode($detailRow['consulting_snapshot'], true);
expectLegacy($snap['legacy'] === true && $snap['lebih'] === 1242501000 && $snap['cash'] === 257499000 && (string)$snap['owner']['id'] === '7' && $detailRow['id_customer'] === 'C-ID', 'Detail snapshot is complete and uses the invoice customer');
expectLegacy($detailRow['sisa_piutang'] === '0.00' && $detailRow['penerimaan'] === '2574990.00', 'Receipt columns keep legacy semantics');
$voucher = $db->inserts['tr_customer_deposit'][0]; $mutation = $db->inserts['tr_customer_deposit_mutasi'][0];
expectLegacy($voucher['tipe_sumber'] === 'LEBIH_BAYAR' && $voucher['nominal_awal'] === '12425010.00' && (string)$voucher['id_company'] === '7' && $voucher['no_referensi'] === 'NEW' && $voucher['id_customer'] === 'C-ID', 'Deposit voucher created for the receipt');
expectLegacy($mutation['jenis_mutasi'] === 'IN' && $mutation['nominal'] === '12425010.00' && $mutation['no_transaksi'] === 'NEW', 'Deposit mutation IN matches the voucher');

// Penolakan: tidak ada penulisan baru.
$cases = array(
    'Receipt plus Lebih Bayar must equal bank money' => array('lebih_bayar_1'=>'12,425,011.00'),
    'Negative Lebih Bayar rejected' => array('lebih_bayar_1'=>'-1.00'),
    'Malformed Lebih Bayar rejected' => array('lebih_bayar_1'=>'12abc'),
    'Deposit use is not supported for old invoices' => array('deposit_choice'=>'Y'),
);
foreach ($cases as $label => $override) {
    $before = array($db->batches, $db->inserts); $res = $saveLebih($override);
    expectLegacy($res['status'] === 0 && !empty($res['message']) && array($db->batches, $db->inserts) === $before, $label);
}
$db->companyId = '2'; $before = array($db->batches, $db->inserts); $res = $saveLebih();
expectLegacy($res['status'] === 0 && array($db->batches, $db->inserts) === $before, 'Lebih Bayar rejected when the company is not a journal owner');
// kons_tr_company 6 (STM-Sustain) -> owner 3, 1 (STM-Vuca) -> owner 4; jurnal tetap memakai id company legacy.
foreach (array('6' => '3', '1' => '4', '3' => '3', '4' => '4') as $legacyId => $ownerId) {
    $db->companyId = (string)$legacyId; $db->batches = array(); $db->inserts = array(); $mapped = $saveLebih();
    expectLegacy($mapped['status'] === 1 && (string)$db->inserts['tr_customer_deposit'][0]['id_company'] === $ownerId && (string)json_decode($db->batches['tr_penerimaan_piutang_detail'][0]['consulting_snapshot'], true)['owner']['id'] === $ownerId, "Legacy company $legacyId maps to owner $ownerId");
    expectLegacy((string)end($db->batches['tr_jurnal'])['id_company'] === (string)$legacyId, "Deposit journal keeps the legacy company id $legacyId");
}
$db->companyId = '7';
// Tanpa Lebih Bayar: perilaku lama (tanpa snapshot/voucher).
$db->batches = array(); $db->inserts = array(); $plain = $saveLebih(array('lebih_bayar_1'=>'', 'penerimaan_1'=>'2.574.990,00'));
expectLegacy($plain['status'] === 1 && !isset($db->inserts['tr_customer_deposit']) && empty((json_decode($db->batches['tr_penerimaan_piutang_detail'][0]['consulting_snapshot'], true) ?: array())['legacy']) && $db->batches['tr_jurnal'][0]['debit'] === '2574990.00', 'Without Lebih Bayar nothing deposit-related is written');
// Rollback invoice lama: voucher Lebih Bayar dihapus bila belum terpakai, ditolak bila sudah terpakai.
$rollback = function () use ($c) { $c->input->values = array('id_penerimaan'=>'H1'); $m = new ReflectionMethod('Penerimaan_uang', 'rollback_legacy_penerimaan'); $m->setAccessible(true); ob_start(); $m->invoke($c); return json_decode(ob_get_clean(), true); };
$db->journals = array(); $db->history = array(array('id_inv'=>'189','penerimaan'=>'2574990.00','biaya_admin'=>'0.00','pph23'=>'0.00'));
$db->voucher = array('id'=>77,'status'=>'AKTIF','nominal_terpakai'=>'0.00'); $db->voucherMutations = array(array('jenis_mutasi'=>'IN'));
$db->deletes = array(); $rb = $rollback();
expectLegacy($rb['status'] === 1 && count($db->deletes['tr_customer_deposit'] ?? array()) === 1 && count($db->deletes['tr_customer_deposit_mutasi'] ?? array()) === 1, 'Rollback removes an unused Lebih Bayar voucher and its mutation: '.json_encode($rb));
foreach (array('partly used' => array('status'=>'TERPAKAI_SEBAGIAN','nominal_terpakai'=>'100.00'), 'fully used' => array('status'=>'LUNAS_TERPAKAI','nominal_terpakai'=>'12425010.00')) as $label => $used) {
    $db->voucher = $used + array('id'=>77); $db->deletes = array(); $rb = $rollback();
    expectLegacy($rb['status'] === 0 && empty($db->deletes) && strpos($rb['msg'], 'sudah dipakai') !== false, "Rollback refused when the voucher is $label");
}
$db->voucher = array('id'=>77,'status'=>'AKTIF','nominal_terpakai'=>'0.00'); $db->voucherMutations = array(array('jenis_mutasi'=>'IN'), array('jenis_mutasi'=>'OUT')); $db->deletes = array(); $rb = $rollback();
expectLegacy($rb['status'] === 0 && empty($db->deletes), 'Rollback refused when the voucher has extra mutations');
$db->voucher = null; $db->voucherMutations = array(); $db->deletes = array(); $rb = $rollback();
expectLegacy($rb['status'] === 1 && empty($db->deletes['tr_customer_deposit']), 'Rollback without a Lebih Bayar voucher is unchanged');
// ---- Dipotong (Y): PPH 23 editable memengaruhi hitungan; Sisa Piutang editable ----
$db->deposit = false; $db->companyId = '7';
$db->invoice = $lebihInvoice; $db->history = array(array('id_header'=>'OLD')); $db->historyChoice = 'Y'; $db->journals = array(); $db->batches = array(); $db->inserts = array();
$model = $c->Penerimaan_uang_model;
expectLegacy($model->deducted_pph_budget($db->invoice) === 46818000, 'Y budget is the whole invoice PPh when no adjustment was ever booked');
$pphRow = array('coa'=>'1106-01-02','nm_coa'=>'PPh 23 Disetor','keterangan'=>'PPh 23 Disetor - 189/STM/2026','debit'=>'0.00','kredit'=>'100000.00');
$db->journals = array($pphRow);
expectLegacy($model->deducted_pph_budget($db->invoice) === 36818000, 'Y budget shrinks by the PPh adjustment already booked');
$db->journals = array($pphRow, $pphRow);
expectLegacy($model->deducted_pph_budget($db->invoice) === 46818000, 'Ambiguous Y history is read leniently (never blocks)');
$db->historyChoice = 'N'; $thrown = false;
try { $model->deducted_pph_budget($db->invoice); } catch (InvalidArgumentException $e) { $thrown = true; }
expectLegacy($thrown, 'Y budget still requires the same PPh choice as the history');
$db->historyChoice = 'Y'; $db->journals = array();

// Preview: Y merender PPH 23 + Sisa editable, N tidak merender PPH 23 editable.
$yPreview = array('choose_inv'=>array('189'),'id_alokasi'=>5102,'pph23_dipotong'=>'Y','ppn_dipotong'=>'Y','nominal_penerimaan_bank'=>'15,000,000');
$c->input->values = $yPreview; $c->process_alokasi();
$yHtml = $c->template->data['hasil'];
expectLegacy(strpos($yHtml, 'name="pph_actual_1"') !== false && strpos($yHtml, 'value="468180.00"') !== false, 'Y preview renders an editable PPH 23 defaulting to the budget');
expectLegacy(strpos($yHtml, 'name="remaining_pph_1" value="468180.00"') !== false, 'Y preview passes the PPh budget to the view');
expectLegacy(preg_match('/name="sisa_piutang_1"[^>]*readonly/', $yHtml) === 0 && strpos($yHtml, 'sisaEdited(1)') !== false, 'Sisa Piutang is editable');
$db->historyChoice = 'N'; $db->history = array(); $c->input->values = array_replace($yPreview, array('pph23_dipotong'=>'N')); $c->process_alokasi();
expectLegacy(isset($c->template->data['hasil']) && strpos($c->template->data['hasil'], 'name="penerimaan_1"') !== false && strpos($c->template->data['hasil'], 'pph_actual_1') === false, 'N preview has no editable PPH 23 (automatic like the mockup)');
$db->historyChoice = 'Y'; $db->history = array(array('id_header'=>'OLD'));

// Simpan Y.
$yPost = array('nm_customer'=>'Customer','id_alokasi'=>5102,'ppn_dipotong'=>'Y','pph23_dipotong'=>'Y','nominal_penerimaan_bank'=>'999','no_inv'=>1,'id_inv_1'=>'189',
    'penerimaan_1'=>'3,043,170.00','biaya_admin_1'=>'0','sisa_piutang_1'=>'0');
$saveY = function ($overrides = array()) use ($c, $yPost) {
    $c->input->values = array_replace($yPost, $overrides); ob_start(); $c->save_penerimaan_piutang(); return json_decode(ob_get_clean(), true);
};
$rowOf = function ($coa) use ($db) { foreach ($db->batches['tr_jurnal'] as $line) { if ($line['coa'] === $coa) return $line; } return null; };
$db->batches = array();
$res = $saveY();
expectLegacy($res['status'] === 1 && $rowOf('1102-01-01')['kredit'] === '3043170.00' && $rowOf('1106-01-02')['debit'] === '468180.00' && $db->batches['tr_jurnal'][0]['debit'] === '2574990.00', 'Y without pph_actual books the full budget as PPh and a gross piutang credit (mockup): '.json_encode($res));
$db->batches = array();
$res = $saveY(array('pph_actual_1'=>'468,180.00'));
expectLegacy($res['status'] === 1 && $rowOf('1106-01-02')['debit'] === '468180.00' && $db->batches['tr_penerimaan_piutang_detail'][0]['sisa_piutang'] === '0.00', 'Y with p = budget equals the default');
$db->batches = array();
$res = $saveY(array('pph_actual_1'=>'268,180.00'));
expectLegacy($res['status'] === 1, 'Y with a lower PPh saves: '.json_encode($res));
$dr = 0; $cr = 0; foreach ($db->batches['tr_jurnal'] as $line) { $dr += (float)$line['debit']; $cr += (float)$line['kredit']; }
expectLegacy($db->batches['tr_jurnal'][0]['debit'] === '2774990.00' && $rowOf('1102-01-01')['kredit'] === '3043170.00' && $rowOf('1106-01-02')['debit'] === '268180.00' && abs($dr - $cr) < 0.005, 'Edited PPh is debited, piutang credited gross and the journal still balances');
expectLegacy($db->batches['tr_penerimaan_piutang_detail'][0]['sisa_piutang'] === '0.00' && $db->batches['tr_penerimaan_piutang_detail'][0]['penerimaan'] === '3043170.00' && $db->batches['tr_penerimaan_piutang_detail'][0]['pph23'] === '268180.00', 'Detail Alokasi = gross piutang, PPh = edited value, sisa settles to zero');
foreach (array('p above the budget' => array('pph_actual_1'=>'468,181.00'), 'negative p' => array('pph_actual_1'=>'-1.00'), 'malformed p' => array('pph_actual_1'=>'12abc'),
    'adjustment larger than the receipt' => array('pph_actual_1'=>'0.00', 'penerimaan_1'=>'100,000.00')) as $label => $override) {
    $before = $db->batches; $res = $saveY($override);
    expectLegacy($res['status'] === 0 && !empty($res['message']) && $db->batches === $before, "Y rejects $label without writing");
}
$db->batches = array(); $db->historyChoice = 'N'; $db->history = array();
$res = $saveY(array('pph23_dipotong'=>'N', 'pph_actual_1'=>'1.00'));
expectLegacy($res['status'] === 1 && $rowOf('1106-01-02')['debit'] === '0.00' && $rowOf('1106-01-02')['kredit'] === '0.00' && $rowOf('1102-01-01')['kredit'] === '3043170.00', 'N ignores pph_actual: no PPh line, piutang credited gross (mockup)');
$db->historyChoice = 'Y'; $db->history = array(array('id_header'=>'OLD'));
echo "PASS: MINARTA final receipt and server journals, history validation, partial PPh, overpayment, cents, and legacy Vuca regressions\n";
