<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once dirname(__DIR__, 2) . '/invoicing/libraries/Invoice_consulting_service.php';
require_once __DIR__ . '/Penerimaan_calculation.php';

class Consulting_receipt
{
    private $s;
    public function __construct($ci) { $this->s = new Invoice_consulting_service($ci); }

    public function contains_new($ids)
    {
        if (!is_array($ids)) return false;
        foreach ($ids as $id) {
            if (!is_scalar($id)) continue;
            $invoice = $this->s->db->get_where('tr_invoicing', array('id' => $id))->row_array();
            if ($invoice && Invoice_consulting::is_new($invoice)) return true;
        }
        return false;
    }

    public function guard_legacy_allocation($id)
    {
        $s = $this->s;
        if (!$s->db->field_exists('consulting_batch', 'tr_penerimaan_piutang')) return;
        $split = $s->one('SELECT id_alokasi_detail FROM tr_alokasi_split WHERE id=?', array($id));
        $parent = $split ? $split['id_alokasi_detail'] : $id;
        $used = $s->one('SELECT h.id FROM tr_penerimaan_piutang h LEFT JOIN tr_alokasi_split sp ON sp.id=h.id_alokasi WHERE h.consulting_batch=3 AND (sp.id_alokasi_detail=? OR (sp.id IS NULL AND h.id_alokasi=?)) LIMIT 1', array($parent, $parent));
        if ($used) throw new RuntimeException('Alokasi induk memiliki penerimaan konsultasi versi baru. Selesaikan rollback batch tersebut terlebih dahulu atau gunakan alokasi lain.');
    }

    private function allocation($id, $lock = false)
    {
        $s = $this->s;
        $split = $s->one('SELECT * FROM tr_alokasi_split WHERE id=?' . ($lock ? ' FOR UPDATE' : ''), array($id));
        if ($split && (string)$split['jenis_alokasi'] !== '1') throw new RuntimeException('Alokasi bukan penerimaan piutang.');
        $detail = $s->one('SELECT * FROM tr_alokasi_detail WHERE id=?' . ($lock ? ' FOR UPDATE' : ''), array($split ? $split['id_alokasi_detail'] : $id));
        if (!$detail) throw new RuntimeException('Alokasi tidak ditemukan.');
        $capacity = Penerimaan_calculation::cents($split ? $split['nominal'] : $detail['nominal_debit']);
        $used = Penerimaan_calculation::cents(($split ? $split['nilai_terpakai'] : $detail['nilai_terpakai']) ?? '0');
        if (min($capacity, $used) < 0 || $used > $capacity) throw new RuntimeException('Saldo alokasi tidak valid; perlu rekonsiliasi.');
        $recorded = $s->one('SELECT COALESCE(SUM(nominal_penerimaan_bank),0) AS cash FROM tr_penerimaan_piutang WHERE id_alokasi=?', array($id));
        if (Penerimaan_calculation::cents($recorded['cash']) !== $used) throw new RuntimeException('Pemakaian alokasi berbeda dari riwayat penerimaan; perlu rekonsiliasi.');
        $parentCash = $s->one('SELECT COALESCE(SUM(h.nominal_penerimaan_bank),0) AS cash FROM tr_penerimaan_piutang h LEFT JOIN tr_alokasi_split sp ON sp.id=h.id_alokasi WHERE sp.id_alokasi_detail=? OR (sp.id IS NULL AND h.id_alokasi=?)', array($detail['id'], $detail['id']));
        if (Penerimaan_calculation::cents($parentCash['cash']) !== Penerimaan_calculation::cents($detail['nilai_terpakai'] ?? '0')) throw new RuntimeException('Pemakaian induk alokasi berbeda dari riwayat penerimaan; perlu rekonsiliasi.');
        return array('split' => $split, 'detail' => $detail, 'capacity' => $capacity, 'used' => $used);
    }

    private function account($owner, $code)
    {
        $matches = array_values(array_filter($this->s->master($owner), function ($row) use ($code) { return $row['no_perkiraan'] === $code; }));
        if (count($matches) !== 1) throw new RuntimeException('COA bank/admin tidak tersedia atau ambigu pada accounting tujuan.');
        return array('code' => $code, 'name' => $matches[0]['nama']);
    }

    private function invoice_context($invoice, $choice)
    {
        if (Invoice_consulting::is_new($invoice)) {
            $snapshot = Invoice_consulting::snapshot($invoice);
            $taxTreatment = !empty($invoice['tax_treatment']) ? $invoice['tax_treatment'] : (!empty($snapshot['type']) ? $snapshot['type'] : ((float)$invoice['ppn_jurnal'] > 0 ? 'PKP' : 'WAPU'));
            if (!in_array($taxTreatment, array('PKP', 'WAPU', 'KB'), true)) {
                $taxTreatment = 'PKP';
            }
            $salesCents = Penerimaan_calculation::cents($invoice['total_nominal_jurnal']);
            $taxCalc = Penerimaan_calculation::tax_calc($salesCents, $taxTreatment);
            $defaultPph = $choice === 'Y' ? $taxCalc['pph_default'] : 0;

            $spkNo = '';
            if (!empty($invoice['id_spk_penawaran'])) {
                $spk = $this->s->one('SELECT no_spk FROM ' . DBCNL . '.kons_tr_spk_penawaran WHERE id_spk_penawaran=?', array($invoice['id_spk_penawaran']));
                if ($spk && !empty($spk['no_spk'])) {
                    $spkNo = $spk['no_spk'];
                }
            }

            return array(
                'invoice' => $invoice,
                'new' => true,
                'owner' => $snapshot['owner'],
                'accounts' => $snapshot['accounts'],
                'tax_treatment' => $taxTreatment,
                'tax_calc' => $taxCalc,
                'default_pph' => $defaultPph,
                'spk' => $spkNo,
                'remaining_pph' => 0
            );
        }
        $s = $this->s;
        if ((string)$invoice['non_kons'] === '1') $owner = $s->company('1');
        else {
            $spk = $s->one('SELECT * FROM ' . DBCNL . '.kons_tr_spk_penawaran WHERE id_spk_penawaran=?', array($invoice['id_spk_penawaran']));
            $quote = $spk ? $s->one('SELECT * FROM ' . DBCNL . '.kons_tr_penawaran WHERE id_quotation=?', array($spk['id_penawaran'])) : null;
            if (!$spk) throw new RuntimeException('SPK invoice lama tidak ditemukan.');
            $owner = $s->company(!empty($quote['company']) ? $quote['company'] : $spk['id_company']);
        }
        $pph = (string)$invoice['tipe_invoice'] === '1' ? '1106-01-05' : '1106-01-02';
        return array(
            'invoice' => $invoice,
            'new' => false,
            'owner' => $owner,
            'accounts' => array('receivable' => $this->account($owner['id'], '1102-01-01'), 'pph' => $this->account($owner['id'], $pph)),
            'tax_treatment' => (float)$invoice['ppn_jurnal'] > 0 ? 'PKP' : 'WAPU',
            'remaining_pph' => $s->ci->Penerimaan_uang_model->remaining_receipt_pph($invoice, $choice)
        );
    }

    public function preview($post)
    {
        if (!has_permission('Penerimaan_Uang.Add')) throw new RuntimeException('Tidak memiliki izin penerimaan.');
        $s = $this->s; $s->schema(); $ids = $post['choose_inv'] ?? null;
        if (!is_array($ids) || !$ids || count($ids) > 100 || count(array_unique($ids, SORT_REGULAR)) !== count($ids)) throw new InvalidArgumentException('Pilih invoice unik, maksimal 100.');
        $allocation = $this->allocation($s->required($post, 'id_alokasi'));
        $choice = $s->required($post, 'pph23_dipotong', 1);
        if (!in_array($choice, array('Y', 'N'), true)) throw new InvalidArgumentException('Pilihan PPh tidak valid.');
        $rows = array(); $customer = null;
        $bank = $s->one('SELECT * FROM ms_bank WHERE id=?', array($allocation['detail']['tipe_bank']));
        if (!$bank) throw new RuntimeException('Bank alokasi tidak ditemukan.');
        foreach ($ids as $id) {
            if (!is_scalar($id)) throw new InvalidArgumentException('ID invoice tidak valid.');
            $invoice = $s->one('SELECT * FROM tr_invoicing WHERE id=?', array($id));
            if (!$invoice || Penerimaan_calculation::cents($invoice['saldo_piutang']) <= 0) throw new RuntimeException('Invoice tidak ditemukan atau sudah lunas.');
            if ($customer !== null && $customer !== (string)$invoice['id_customer']) throw new RuntimeException('Invoice harus berasal dari customer yang sama.');
            $customer = (string)$invoice['id_customer'];
            $context = $this->invoice_context($invoice, $choice);
            $context['accounts']['bank'] = $this->account($context['owner']['id'], $bank['coa_bank']);
            $context['accounts']['admin'] = $this->account($context['owner']['id'], '7201-01-04');
            $context['fingerprint'] = Invoice_calculation::fingerprint($invoice);
            $rows[] = $context;
        }
        return array(
            'rows' => $rows,
            'allocation' => $allocation,
            'allocation_fingerprint' => Invoice_calculation::fingerprint($allocation),
            'id_alokasi' => $post['id_alokasi'],
            'choice' => $choice,
            'ppn_choice' => in_array($post['ppn_dipotong'] ?? '', array('Y', 'N'), true) ? $post['ppn_dipotong'] : 'N'
        );
    }

    public function save($post)
    {
        $s = $this->s;
        return $s->mutate('save_consulting_receipt', function () use ($s, $post) {
            if (!has_permission('Penerimaan_Uang.Add')) throw new RuntimeException('Tidak memiliki izin penerimaan.');
            $s->transactional(array('tr_invoicing', 'tr_jurnal', 'tr_penerimaan_piutang', 'tr_penerimaan_piutang_detail', 'tr_alokasi_detail', 'tr_alokasi_split'));
            $count = $s->required($post, 'no_inv', 3);
            if (!ctype_digit($count) || (int)$count < 1 || (int)$count > 100) throw new InvalidArgumentException('Jumlah invoice tidak valid.');
            $ids = array(); for ($n = 1; $n <= (int)$count; $n++) $ids[] = $s->required($post, 'id_inv_' . $n);
            if (count(array_unique($ids)) !== count($ids)) throw new InvalidArgumentException('Invoice duplikat pada batch.');

            // The shared receipt lock serializes old/new balances and source allocation reads.
            $allocation = $this->allocation($s->required($post, 'id_alokasi'), true);
            if (!hash_equals(Invoice_calculation::fingerprint($allocation), $s->required($post, 'allocation_fingerprint', 64))) throw new RuntimeException('Alokasi berubah; buat preview ulang.');
            $locked = $ids; sort($locked, SORT_STRING);
            foreach ($locked as $id) $s->one('SELECT id FROM tr_invoicing WHERE id=? FOR UPDATE', array($id));

            $preview = $this->preview(array('choose_inv' => $ids, 'id_alokasi' => $post['id_alokasi'], 'pph23_dipotong' => $post['pph23_dipotong'] ?? '', 'ppn_dipotong' => $post['ppn_dipotong'] ?? 'N'));
            $number = $s->ci->Penerimaan_uang_model->generate_id();
            $details = array(); $journals = array(); $invoiceUpdates = array(); $cashTotal = 0; $submittedCashTotal = 0; $hasNew = false;
            $totPiutang = 0; $totPph = 0; $totAdmin = 0; $totSisa = 0;

            foreach ($preview['rows'] as $index => $context) {
                $n = $index + 1; $invoice = $context['invoice']; $hasNew = $hasNew || $context['new'];
                if (!hash_equals($context['fingerprint'], $s->required($post, 'receipt_fingerprint_' . $n, 64))) throw new RuntimeException('Saldo invoice berubah; buat preview ulang.');
                $cash = Penerimaan_calculation::cents($s->required($post, 'penerimaan_' . $n, 32));
                $admin = Penerimaan_calculation::cents($s->required($post, 'biaya_admin_' . $n, 32));
                $balance = Penerimaan_calculation::cents($invoice['saldo_piutang']);

                if ($context['new']) {
                    $pph = $preview['choice'] === 'Y' ? Penerimaan_calculation::cents($s->required($post, 'pph_actual_' . $n, 32)) : 0;
                    $sisa = isset($post['sisa_piutang_' . $n]) ? Penerimaan_calculation::cents($post['sisa_piutang_' . $n]) : max(0, $balance - ($cash + $admin));

                    if (min($cash, $admin, $pph, $sisa) < 0) throw new InvalidArgumentException('Nilai tidak boleh negatif.');
                    if ($admin > Penerimaan_calculation::MAX_ADMIN_CENTS) throw new InvalidArgumentException('Total biaya admin melebihi batas maksimal Rp 10.000.');
                    if ($pph > $balance) throw new InvalidArgumentException('PPh 23 melebihi piutang dagang invoice ' . $invoice['no_invoice'] . '.');
                    if ($sisa > $balance) throw new InvalidArgumentException('Sisa piutang melebihi piutang dagang invoice ' . $invoice['no_invoice'] . '.');

                    $reduction = $balance - $sisa;
                    $bankCash = $balance - $pph - $admin - $sisa;
                    if ($bankCash < 0) throw new InvalidArgumentException('Penerimaan bank tidak boleh bernilai minus pada ' . $invoice['no_invoice'] . '.');

                    $totPiutang += $balance;
                    $totPph += $pph;
                    $totAdmin += $admin;
                    $totSisa += $sisa;
                    $submittedCashTotal += $cash;
                    $cashTotal += $bankCash;

                    $receiptSnapshot = array(
                        'owner' => $context['owner'],
                        'accounts' => $context['accounts'],
                        'invoice_no' => $invoice['no_invoice'],
                        'date' => $allocation['detail']['tanggal_transaksi'],
                        'cash' => $bankCash,
                        'alok' => $reduction,
                        'admin' => $admin,
                        'pph' => $pph,
                        'sisa' => $sisa,
                        'balance' => $balance,
                        'reduction' => $reduction,
                        'tax_treatment' => $context['tax_treatment'] ?? 'PKP'
                    );

                    $details[] = array(
                        'id_header' => $number,
                        'id_alokasi' => $post['id_alokasi'],
                        'id_inv' => $invoice['id'],
                        'tgl_inv' => $invoice['tanggal_invoice'],
                        'id_customer' => $invoice['id_customer'],
                        'nm_customer' => $invoice['nm_customer'],
                        'dpp' => $invoice['total_nominal_jurnal'],
                        'dpp_lain' => $invoice['dpp_lain_lain_jurnal'],
                        'ppn' => $invoice['ppn_jurnal'],
                        'tagihan_ppn' => $invoice['tagihan_ppn_jurnal'],
                        'pph23' => Invoice_calculation::decimal($pph),
                        'total' => $invoice['total_akhir_jurnal'],
                        'penerimaan' => Invoice_calculation::decimal($reduction),
                        'biaya_admin' => Invoice_calculation::decimal($admin),
                        'sisa_piutang' => Invoice_calculation::decimal($sisa),
                        'consulting_version' => 3,
                        'consulting_snapshot' => json_encode($receiptSnapshot),
                        'consulting_reduction' => Invoice_calculation::decimal($reduction),
                        'created_by' => $s->ci->auth->user_id(),
                        'created_date' => date('Y-m-d H:i:s')
                    );

                    $spkNo = $context['spk'] ?? '';
                    $taxType = $context['tax_treatment'] ?? '';
                    $sfx = ' - ' . $invoice['no_invoice'] . ($spkNo ? ' - ' . $spkNo : '') . ($taxType === 'KB' ? ' - Kawasan Berikat' : '');

                    // Double-entry balancing lines:
                    // 1. Bank Debit
                    if ($bankCash > 0) {
                        $journals[] = array(
                            'no_jurnal' => Invoice_calculation::journal_number($s->db, count($journals) + 1),
                            'tgl_jurnal' => $allocation['detail']['tanggal_transaksi'],
                            'coa' => $context['accounts']['bank']['code'],
                            'nm_coa' => $context['accounts']['bank']['name'],
                            'id_company' => $context['owner']['id'],
                            'nm_company' => $context['owner']['nm_company'],
                            'debit' => Invoice_calculation::decimal($bankCash),
                            'kredit' => '0.00',
                            'keterangan' => $context['accounts']['bank']['name'] . $sfx,
                            'no_transaksi' => $number,
                            'jenis_transaksi' => 'Penerimaan Piutang',
                            'sts' => '0',
                            'created_by' => $s->ci->auth->user_id(),
                            'created_date' => date('Y-m-d H:i:s')
                        );
                    }
                    // 2. PPh 23 Disetor Debit
                    if ($pph > 0) {
                        $journals[] = array(
                            'no_jurnal' => Invoice_calculation::journal_number($s->db, count($journals) + 1),
                            'tgl_jurnal' => $allocation['detail']['tanggal_transaksi'],
                            'coa' => $context['accounts']['pph']['code'],
                            'nm_coa' => $context['accounts']['pph']['name'],
                            'id_company' => $context['owner']['id'],
                            'nm_company' => $context['owner']['nm_company'],
                            'debit' => Invoice_calculation::decimal($pph),
                            'kredit' => '0.00',
                            'keterangan' => $context['accounts']['pph']['name'] . $sfx,
                            'no_transaksi' => $number,
                            'jenis_transaksi' => 'Penerimaan Piutang',
                            'sts' => '0',
                            'created_by' => $s->ci->auth->user_id(),
                            'created_date' => date('Y-m-d H:i:s')
                        );
                    }
                    // 3. Biaya Admin Debit
                    if ($admin > 0) {
                        $journals[] = array(
                            'no_jurnal' => Invoice_calculation::journal_number($s->db, count($journals) + 1),
                            'tgl_jurnal' => $allocation['detail']['tanggal_transaksi'],
                            'coa' => $context['accounts']['admin']['code'],
                            'nm_coa' => $context['accounts']['admin']['name'],
                            'id_company' => $context['owner']['id'],
                            'nm_company' => $context['owner']['nm_company'],
                            'debit' => Invoice_calculation::decimal($admin),
                            'kredit' => '0.00',
                            'keterangan' => $context['accounts']['admin']['name'] . $sfx,
                            'no_transaksi' => $number,
                            'jenis_transaksi' => 'Penerimaan Piutang',
                            'sts' => '0',
                            'created_by' => $s->ci->auth->user_id(),
                            'created_date' => date('Y-m-d H:i:s')
                        );
                    }
                    // 4. Piutang Dagang Usaha Credit (Gross initial receivable)
                    if ($balance > 0) {
                        $journals[] = array(
                            'no_jurnal' => Invoice_calculation::journal_number($s->db, count($journals) + 1),
                            'tgl_jurnal' => $allocation['detail']['tanggal_transaksi'],
                            'coa' => $context['accounts']['receivable']['code'],
                            'nm_coa' => $context['accounts']['receivable']['name'],
                            'id_company' => $context['owner']['id'],
                            'nm_company' => $context['owner']['nm_company'],
                            'debit' => '0.00',
                            'kredit' => Invoice_calculation::decimal($balance),
                            'keterangan' => $context['accounts']['receivable']['name'] . $sfx,
                            'no_transaksi' => $number,
                            'jenis_transaksi' => 'Penerimaan Piutang',
                            'sts' => '0',
                            'created_by' => $s->ci->auth->user_id(),
                            'created_date' => date('Y-m-d H:i:s')
                        );
                    }
                    // 5. Piutang Dagang Usaha Debit (Kurang Bayar / Sisa Piutang)
                    if ($sisa > 0) {
                        $journals[] = array(
                            'no_jurnal' => Invoice_calculation::journal_number($s->db, count($journals) + 1),
                            'tgl_jurnal' => $allocation['detail']['tanggal_transaksi'],
                            'coa' => $context['accounts']['receivable']['code'],
                            'nm_coa' => $context['accounts']['receivable']['name'],
                            'id_company' => $context['owner']['id'],
                            'nm_company' => $context['owner']['nm_company'],
                            'debit' => Invoice_calculation::decimal($sisa),
                            'kredit' => '0.00',
                            'keterangan' => $context['accounts']['receivable']['name'] . $sfx,
                            'no_transaksi' => $number,
                            'jenis_transaksi' => 'Penerimaan Piutang',
                            'sts' => '0',
                            'created_by' => $s->ci->auth->user_id(),
                            'created_date' => date('Y-m-d H:i:s')
                        );
                    }

                    $invoiceUpdates[] = array('id' => $invoice['id'], 'saldo_piutang' => Invoice_calculation::decimal($sisa));
                } else {
                    // Legacy compatibility path
                    $pph = 0;
                    $calculation = Penerimaan_calculation::calculate($balance, $context['remaining_pph'], $preview['choice'], $cash, $admin);
                    if (min($cash, $admin, $pph) < 0 || $calculation['reduction'] <= 0) throw new InvalidArgumentException('Penerimaan tidak valid.');
                    $cashTotal += $cash;
                    $receiptSnapshot = array('owner' => $context['owner'], 'accounts' => $context['accounts'], 'invoice_no' => $invoice['no_invoice'], 'date' => $allocation['detail']['tanggal_transaksi'],
                        'cash' => $cash, 'admin' => $admin, 'pph' => $pph, 'adjustment' => $calculation['adjustment'], 'reduction' => $calculation['reduction']);
                    $details[] = array('id_header' => $number, 'id_alokasi' => $post['id_alokasi'], 'id_inv' => $invoice['id'], 'tgl_inv' => $invoice['tanggal_invoice'],
                        'id_customer' => $invoice['id_customer'], 'nm_customer' => $invoice['nm_customer'], 'dpp' => $invoice['total_nominal_jurnal'], 'dpp_lain' => $invoice['dpp_lain_lain_jurnal'],
                        'ppn' => $invoice['ppn_jurnal'], 'tagihan_ppn' => $invoice['tagihan_ppn_jurnal'], 'pph23' => $preview['choice'] === 'N' ? '0.00' : $invoice['pph_jurnal'],
                        'total' => $invoice['total_akhir_jurnal'], 'penerimaan' => Invoice_calculation::decimal($cash), 'biaya_admin' => Invoice_calculation::decimal($admin),
                        'sisa_piutang' => Invoice_calculation::decimal($calculation['remaining']), 'consulting_version' => 2,
                        'consulting_snapshot' => json_encode($receiptSnapshot),
                        'consulting_reduction' => Invoice_calculation::decimal($calculation['reduction']), 'created_by' => $s->ci->auth->user_id(), 'created_date' => date('Y-m-d H:i:s'));
                    $lines = array(array('bank', $cash, 0), array('admin', $admin, 0), array('pph', 0, $calculation['adjustment']), array('receivable', 0, $calculation['reduction']));
                    foreach ($lines as $line) {
                        if (!$line[1] && !$line[2]) continue;
                        $account = $context['accounts'][$line[0]];
                        $journals[] = array('no_jurnal' => Invoice_calculation::journal_number($s->db, count($journals) + 1), 'tgl_jurnal' => $allocation['detail']['tanggal_transaksi'],
                            'coa' => $account['code'], 'nm_coa' => $account['name'], 'id_company' => $context['owner']['id'], 'nm_company' => $context['owner']['nm_company'],
                            'debit' => Invoice_calculation::decimal($line[1]), 'kredit' => Invoice_calculation::decimal($line[2]), 'keterangan' => $account['name'] . ' - ' . $invoice['no_invoice'],
                            'no_transaksi' => $number, 'jenis_transaksi' => 'Penerimaan Piutang', 'sts' => '0', 'created_by' => $s->ci->auth->user_id(), 'created_date' => date('Y-m-d H:i:s'));
                    }
                    $invoiceUpdates[] = array('id' => $invoice['id'], 'saldo_piutang' => Invoice_calculation::decimal($calculation['remaining']));
                }
            }
            if (!$hasNew) throw new RuntimeException('Batch tidak mengandung invoice konsultasi versi baru.');

            // Validate available capacity
            $capacity = $allocation['capacity'] - $allocation['used'];
            if ($cashTotal > $capacity) throw new RuntimeException('Penerimaan melebihi sisa nominal alokasi bank.');

            // Kontrol zero balance check:
            if ($hasNew) {
                $targetCash = $submittedCashTotal > 0 ? $submittedCashTotal : $cashTotal;
                $k = Penerimaan_calculation::kontrol($totPiutang, $totPph, $totAdmin, $totSisa, $targetCash);
                if ($k !== 0) throw new RuntimeException('Kontrol belum 0 (selisih ' . Penerimaan_calculation::decimal($k) . ').');
            }

            // Balancing verification:
            $debit = 0; $credit = 0;
            foreach ($journals as $j) {
                $debit += Invoice_calculation::cents($j['debit']);
                $credit += Invoice_calculation::cents($j['kredit']);
            }
            if ($debit !== $credit) throw new RuntimeException('Jurnal penerimaan tidak seimbang (Debit: ' . $debit . ', Kredit: ' . $credit . ').');

            $invoice = $preview['rows'][0]['invoice'];
            $s->checked($s->db->insert('tr_penerimaan_piutang', array(
                'no_surat' => $number,
                'id_alokasi' => $post['id_alokasi'],
                'id_customer' => $invoice['id_customer'],
                'nm_customer' => $invoice['nm_customer'],
                'ppn_dipotong' => $preview['ppn_choice'],
                'pph23_dipotong' => $preview['choice'],
                'nominal_penerimaan_bank' => Invoice_calculation::decimal($cashTotal),
                'consulting_batch' => 3,
                'created_by' => $s->ci->auth->user_id(),
                'created_date' => date('Y-m-d H:i:s')
            )));
            $s->checked($s->db->insert_batch('tr_penerimaan_piutang_detail', $details));
            $s->checked($s->db->insert_batch('tr_jurnal', $journals));
            foreach ($invoiceUpdates as $upd) {
                $s->checked($s->db->update('tr_invoicing', array('saldo_piutang' => $upd['saldo_piutang']), array('id' => $upd['id'])));
            }

            $detailUsed = Penerimaan_calculation::cents($allocation['detail']['nilai_terpakai'] ?? '0') + $cashTotal;
            if ($detailUsed > Penerimaan_calculation::cents($allocation['detail']['nominal_debit'] ?? '0')) throw new RuntimeException('Penerimaan melebihi nominal bank pada induk alokasi.');
            $s->checked($s->db->update('tr_alokasi_detail', array('nilai_terpakai' => Invoice_calculation::decimal($detailUsed)), array('id' => $allocation['detail']['id'])));
            if ($allocation['split']) $s->checked($s->db->update('tr_alokasi_split', array('nilai_terpakai' => Invoice_calculation::decimal($allocation['used'] + $cashTotal)), array('id' => $allocation['split']['id'])));

            return array('status' => 1, 'save' => 1, 'no_surat' => $number, 'msg' => 'Penerimaan piutang berhasil disimpan.');
        }, 'penerimaan_uang');
    }

    public function rollback($id)
    {
        $s = $this->s;
        return $s->mutate('rollback_consulting_receipt', function () use ($s, $id) {
            if (!has_permission('Penerimaan_Uang.Delete')) throw new RuntimeException('Tidak memiliki izin rollback penerimaan.');
            $s->transactional(array('tr_invoicing', 'tr_jurnal', 'tr_penerimaan_piutang', 'tr_penerimaan_piutang_detail', 'tr_alokasi_detail', 'tr_alokasi_split'));
            $header = $s->one('SELECT * FROM tr_penerimaan_piutang WHERE id=? AND consulting_batch=3 FOR UPDATE', array($id));
            if (!$header) throw new RuntimeException('Penerimaan sudah dihapus atau tidak ditemukan.');
            $rows = $s->rows("SELECT * FROM tr_jurnal WHERE no_transaksi=? AND jenis_transaksi='Penerimaan Piutang' FOR UPDATE", array($header['no_surat']));
            if (!$rows) throw new RuntimeException('Jurnal penerimaan tidak lengkap; perlu rekonsiliasi.');
            foreach ($rows as $row) if ((string)$row['sts'] === '1') throw new RuntimeException('Jurnal penerimaan sudah diposting.');
            $details = $s->rows('SELECT * FROM tr_penerimaan_piutang_detail WHERE id_header=? ORDER BY id_inv FOR UPDATE', array($header['no_surat']));
            if (!$details) throw new RuntimeException('Rincian penerimaan tidak lengkap.');

            foreach ($details as $detail) {
                $invoice = $s->one('SELECT * FROM tr_invoicing WHERE id=? FOR UPDATE', array($detail['id_inv']));
                if (!$invoice || $detail['consulting_reduction'] === null) throw new RuntimeException('Snapshot pengurangan piutang tidak lengkap.');
                $snapshot = json_decode($detail['consulting_snapshot'] ?? '', true);
                if (Penerimaan_calculation::cents($invoice['saldo_piutang']) !== Penerimaan_calculation::cents($detail['sisa_piutang'])) {
                    throw new RuntimeException('Saldo piutang berbeda dari snapshot; perlu rekonsiliasi.');
                }
                if ($s->one('SELECT id FROM tr_penerimaan_piutang_detail WHERE id_inv=? AND id>?', array($detail['id_inv'], $detail['id']))) {
                    throw new RuntimeException('Rollback penerimaan terbaru lebih dahulu agar riwayat cicilan tetap konsisten.');
                }
                $balance = Penerimaan_calculation::cents($invoice['saldo_piutang']) + Penerimaan_calculation::cents($detail['consulting_reduction']);
                $s->checked($s->db->update('tr_invoicing', array('saldo_piutang' => Invoice_calculation::decimal($balance)), array('id' => $invoice['id'])));
            }
            $allocation = $this->allocation($header['id_alokasi'], true); $cash = Penerimaan_calculation::cents($header['nominal_penerimaan_bank']);
            $detailUsed = Penerimaan_calculation::cents($allocation['detail']['nilai_terpakai'] ?? '0') - $cash;
            if (min($detailUsed, $allocation['used'] - $cash) < 0) throw new RuntimeException('Pemakaian alokasi tidak konsisten.');
            $s->checked($s->db->update('tr_alokasi_detail', array('nilai_terpakai' => Invoice_calculation::decimal($detailUsed)), array('id' => $allocation['detail']['id'])));
            if ($allocation['split']) $s->checked($s->db->update('tr_alokasi_split', array('nilai_terpakai' => Invoice_calculation::decimal($allocation['used'] - $cash)), array('id' => $allocation['split']['id'])));
            $s->checked($s->db->delete('tr_jurnal', array('no_transaksi' => $header['no_surat'], 'jenis_transaksi' => 'Penerimaan Piutang')));
            $s->checked($s->db->delete('tr_penerimaan_piutang_detail', array('id_header' => $header['no_surat'])));
            $s->checked($s->db->delete('tr_penerimaan_piutang', array('id' => $id)));

            return array('status' => 1, 'msg' => 'Rollback penerimaan berhasil.');
        }, 'penerimaan_uang');
    }
}
