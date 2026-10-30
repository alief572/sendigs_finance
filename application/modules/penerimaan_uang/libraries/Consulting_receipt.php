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

    private function allocation($id, $lock = false, $kind = 'invoice')
    {
        $s = $this->s;
        $split = $s->one('SELECT * FROM tr_alokasi_split WHERE id=?' . ($lock ? ' FOR UPDATE' : ''), array($id));
        if ($split && ($kind === null ? !in_array((string)$split['jenis_alokasi'], array('1', '8'), true) : (string)$split['jenis_alokasi'] !== ($kind === 'deposit' ? '8' : '1'))) throw new RuntimeException('Jenis alokasi tidak sesuai penerimaan.');
        if (!$split && $kind === 'deposit') throw new RuntimeException('Penerimaan kwitansi memerlukan split alokasi Deposit.');
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

    private function receipt_history($invoice, $limit)
    {
        $s = $this->s;
        $rows = $s->rows('SELECT d.*, h.no_surat AS receipt_no FROM tr_penerimaan_piutang_detail d LEFT JOIN tr_penerimaan_piutang h ON h.no_surat=d.id_header WHERE d.id_inv=? ORDER BY d.id', array($invoice['id']));
        $used = 0; $previous = Penerimaan_calculation::cents($invoice['total_akhir_jurnal']);
        foreach ($rows as $row) {
            $snap = json_decode($row['consulting_snapshot'] ?? '', true);
            if (empty($row['receipt_no']) || (int)($row['consulting_version'] ?? 0) !== 3 || !is_array($snap)
                || !isset($snap['pph'], $snap['cash'], $snap['admin'], $snap['reduction']) || $row['consulting_reduction'] === null) {
                throw new RuntimeException('Riwayat penerimaan/PPh tidak lengkap; perlu rekonsiliasi.');
            }
            foreach (array('pph', 'cash', 'admin', 'reduction') as $key) {
                if (!is_int($snap[$key]) || $snap[$key] < 0) throw new RuntimeException('Snapshot penerimaan tidak valid; perlu rekonsiliasi.');
            }
            $remaining = Penerimaan_calculation::cents($row['sisa_piutang']);
            if ($snap['pph'] !== Penerimaan_calculation::cents($row['pph23'])
                || $snap['admin'] !== Penerimaan_calculation::cents($row['biaya_admin'])
                || $snap['reduction'] !== Penerimaan_calculation::cents($row['consulting_reduction'])
                || $snap['cash'] + $snap['admin'] + $snap['pph'] !== $snap['reduction']
                || $remaining < 0 || $previous !== $remaining + $snap['reduction']
                || (isset($snap['balance']) && $snap['balance'] !== $remaining + $snap['reduction'])) {
                throw new RuntimeException('Riwayat saldo/PPh berbeda dari snapshot; perlu rekonsiliasi.');
            }
            if ($snap['pph'] > 0) {
                $account = $snap['accounts']['pph'] ?? null;
                if (!$account || empty($account['code']) || empty($account['name'])) throw new RuntimeException('Snapshot akun PPh tidak lengkap; perlu rekonsiliasi.');
                $journals = $s->rows("SELECT * FROM tr_jurnal WHERE no_transaksi=? AND jenis_transaksi='Penerimaan Piutang'", array($row['receipt_no']));
                $prefix = $account['name'] . ' - ' . $invoice['no_invoice']; $actual = 0;
                foreach ($journals as $journal) {
                    $description = $journal['keterangan'];
                    if ($journal['coa'] === $account['code'] && ($description === $prefix || strpos($description, $prefix . ' - ') === 0)) {
                        $actual += Penerimaan_calculation::cents($journal['debit']) - Penerimaan_calculation::cents($journal['kredit']);
                    }
                }
                if ($actual !== $snap['pph']) throw new RuntimeException('Jurnal PPh berbeda dari riwayat penerimaan; perlu rekonsiliasi.');
            }
            $used += $snap['pph']; $previous = $remaining;
        }
        if ($used > $limit || $previous !== Penerimaan_calculation::cents($invoice['saldo_piutang'])) {
            throw new RuntimeException('Riwayat PPh/saldo invoice tidak konsisten; perlu rekonsiliasi.');
        }
        return array('used' => $used, 'fingerprint' => Invoice_calculation::fingerprint($rows));
    }

    public function available_pph($invoice, $limit)
    {
        return $limit - $this->receipt_history($invoice, $limit)['used'];
    }

    private function invoice_context($invoice, $choice)
    {
        if (Invoice_consulting::is_new($invoice)) {
            $snapshot = Invoice_consulting::snapshot($invoice);
            $taxTreatment = !empty($invoice['tax_treatment']) ? $invoice['tax_treatment'] : (!empty($snapshot['type']) ? $snapshot['type'] : ((float)$invoice['ppn_jurnal'] > 0 ? 'PKP' : 'WAPU'));
            if (!in_array($taxTreatment, array('PKP', 'WAPU', 'KB', 'DEP'), true)) {
                throw new RuntimeException('TAX Code invoice tidak valid; perlu rekonsiliasi.');
            }
            $salesCents = Penerimaan_calculation::cents($invoice['total_nominal_jurnal']);
            $taxCalc = Penerimaan_calculation::tax_calc($salesCents, $taxTreatment);
            $history = $this->receipt_history($invoice, $taxCalc['pph_default']);
            $remainingPph = $taxCalc['pph_default'] - $history['used'];
            $defaultPph = $choice === 'Y' ? $remainingPph : 0;

            // Nomor SPK = id_spk_penawaran (kons_tr_spk_penawaran tidak punya kolom no_spk); sama dengan yang dipakai jurnal Invoicing.
            $spkNo = !empty($invoice['id_spk_penawaran']) ? (string)$invoice['id_spk_penawaran'] : '';

            return array(
                'invoice' => $invoice,
                'new' => true,
                'owner' => $snapshot['owner'],
                'accounts' => $snapshot['accounts'],
                'tax_treatment' => $taxTreatment,
                'tax_calc' => $taxCalc,
                'default_pph' => $defaultPph,
                'spk' => $spkNo,
                'remaining_pph' => $remainingPph,
                'history_fingerprint' => $history['fingerprint']
            );
        }
        throw new InvalidArgumentException('Proses invoice lama dan invoice konsultasi baru secara terpisah.');
    }

    public function preview($post)
    {
        if (!has_permission('Penerimaan_Uang.Add')) throw new RuntimeException('Tidak memiliki izin penerimaan.');
        $s = $this->s; $s->schema(); $ids = $post['choose_inv'] ?? null;
        if (!is_array($ids) || !$ids || count($ids) > 100 || count(array_unique($ids, SORT_REGULAR)) !== count($ids)) throw new InvalidArgumentException('Pilih invoice unik, maksimal 100.');
        $kind = ($post['receipt_kind'] ?? 'invoice') === 'deposit' ? 'deposit' : 'invoice';
        $allocation = $this->allocation($s->required($post, 'id_alokasi'), false, $kind);
        $choice = $s->required($post, 'pph23_dipotong', 1);
        if (!in_array($choice, array('Y', 'N'), true)) throw new InvalidArgumentException('Pilihan PPh tidak valid.');
        $rows = array(); $customer = null; $company = null; $isDepositReceipt = null;
        $bank = $s->one('SELECT * FROM ms_bank WHERE id=?', array($allocation['detail']['tipe_bank']));
        if (!$bank) throw new RuntimeException('Bank alokasi tidak ditemukan.');
        foreach ($ids as $id) {
            if (!is_scalar($id)) throw new InvalidArgumentException('ID invoice tidak valid.');
            $invoice = $s->one('SELECT * FROM tr_invoicing WHERE id=?', array($id));
            if ($invoice && !Invoice_consulting::is_new($invoice)) throw new InvalidArgumentException('Proses invoice lama dan invoice konsultasi baru secara terpisah.');
            if (!$invoice || Penerimaan_calculation::cents($invoice['saldo_piutang']) <= 0) throw new RuntimeException('Invoice tidak ditemukan atau sudah lunas.');
            if ($customer !== null && $customer !== (string)$invoice['id_customer']) throw new RuntimeException('Invoice harus berasal dari customer yang sama.');
            $customer = (string)$invoice['id_customer'];
            $context = $this->invoice_context($invoice, $choice);
            if (isset($post['id_customer']) && (string)$post['id_customer'] !== $customer) throw new InvalidArgumentException('Customer batch tidak sesuai invoice.');
            if (isset($post['id_company']) && (string)$post['id_company'] !== (string)$context['owner']['id']) throw new InvalidArgumentException('Company batch tidak sesuai invoice.');
            $isDeposit = $context['tax_treatment'] === 'DEP';
            if ($isDepositReceipt !== null && $isDepositReceipt !== $isDeposit) throw new RuntimeException('Invoice dan kwitansi deposit harus diproses terpisah.');
            $isDepositReceipt = $isDeposit;
            $invoiceCompany = (string)$context['owner']['id'];
            if ($company !== null && $company !== $invoiceCompany) throw new RuntimeException('Invoice harus berasal dari company yang sama.');
            $company = $invoiceCompany;
            $context['accounts']['bank'] = $this->account($context['owner']['id'], $bank['coa_bank']);
            $context['accounts']['admin'] = $this->account($context['owner']['id'], '7201-01-04');
            $context['accounts']['deposit'] = $this->account($context['owner']['id'], '2102-01-02');
            $context['fingerprint'] = Invoice_calculation::fingerprint(array('invoice' => $invoice, 'history' => $context['history_fingerprint']));
            $rows[] = $context;
        }
        if (($kind === 'deposit') !== $isDepositReceipt) throw new RuntimeException('Gunakan jalur penerimaan invoice atau kwitansi deposit yang sesuai.');
        if ($kind === 'deposit' && ($choice !== 'N' || ($post['deposit_choice'] ?? 'N') !== 'N' || Penerimaan_calculation::cents($post['deposit_amount'] ?? '0') !== 0)) throw new InvalidArgumentException('Kwitansi deposit tidak menerima PPh atau pemakaian deposit.');
        usort($rows, function ($a, $b) {
            $date = strcmp($a['invoice']['tanggal_invoice'] ?? $a['invoice']['created_date'], $b['invoice']['tanggal_invoice'] ?? $b['invoice']['created_date']);
            return $date ?: strcmp((string)$a['invoice']['id'], (string)$b['invoice']['id']);
        });
        $depositChoice = in_array($post['deposit_choice'] ?? '', array('Y', 'N'), true) ? $post['deposit_choice'] : 'N';
        $rawDep = trim((string)($post['deposit_amount'] ?? '0'));
        if (strpos($rawDep, ',') !== false && strpos($rawDep, '.') !== false) {
            $rawDep = strrpos($rawDep, ',') > strrpos($rawDep, '.') ? str_replace(',', '.', str_replace('.', '', $rawDep)) : str_replace(',', '', $rawDep);
        } elseif (strpos($rawDep, ',') !== false) {
            $rawDep = str_replace(',', '.', $rawDep);
        }
        $depositAmount = $depositChoice === 'Y' ? Penerimaan_calculation::cents($rawDep) : 0;
        $custDeposit = 0; $depositError = null;
        try { $custDeposit = $this->get_customer_active_deposit($customer, $company); }
        catch (RuntimeException $error) { $depositError = $error->getMessage(); if ($depositChoice === 'Y') throw $error; }

        return array(
            'receipt_kind' => $kind,
            'rows' => $rows,
            'allocation' => $allocation,
            'allocation_fingerprint' => Invoice_calculation::fingerprint($allocation),
            'id_alokasi' => $post['id_alokasi'],
            'choice' => $choice,
            'ppn_choice' => in_array($post['ppn_dipotong'] ?? '', array('Y', 'N'), true) ? $post['ppn_dipotong'] : 'N',
            'deposit_choice' => $depositChoice,
            'deposit_amount' => $depositAmount,
            'deposit_error' => $depositError,
            'customer_deposit' => $custDeposit
        );
    }

    public function get_customer_active_deposit($customerId, $companyId)
    {
        if (!is_scalar($customerId) || !is_scalar($companyId) || trim((string)$customerId) === '' || trim((string)$companyId) === '') throw new InvalidArgumentException('Pilih customer dan company deposit.');
        $total = 0;
        foreach ($this->active_deposits($customerId, $companyId) as $voucher) $total += Penerimaan_calculation::cents($voucher['sisa_saldo']);
        return $total;
    }

    private function active_deposits($customerId, $companyId, $lock = false)
    {
        if (!$this->s->db->field_exists('tanggal_aktif', 'tr_customer_deposit')) throw new RuntimeException('Terapkan migration 003_deposit_contract.');
        $rows = $this->s->rows("SELECT * FROM tr_customer_deposit WHERE id_customer=? AND id_company=? AND (status IN ('AKTIF', 'TERPAKAI_SEBAGIAN') OR (status='LUNAS_TERPAKAI' AND sisa_saldo<>0)) ORDER BY tanggal_aktif ASC, id ASC" . ($lock ? ' FOR UPDATE' : ''), array($customerId, $companyId));
        foreach ($rows as $row) {
            try { $this->validate_deposit($row); }
            catch (InvalidArgumentException $e) { throw new RuntimeException('Nominal histori deposit tidak valid; perlu rekonsiliasi.'); }

        }
        return $rows;
    }

    private function validate_deposit($voucher)
    {
        $s = $this->s;
        $fail = function () { throw new RuntimeException('Histori saldo deposit belum konsisten; perlu rekonsiliasi.'); };
        $sources = $s->rows('SELECT id FROM tr_customer_deposit WHERE no_referensi=? AND tipe_sumber=?', array($voucher['no_referensi'], $voucher['tipe_sumber']));
        if (count($sources) !== 1) $fail();
        $initial = Penerimaan_calculation::cents($voucher['nominal_awal']);
        $used = Penerimaan_calculation::cents($voucher['nominal_terpakai']);
        $balance = Penerimaan_calculation::cents($voucher['sisa_saldo']);
        $date = DateTime::createFromFormat('!Y-m-d', (string)($voucher['tanggal_aktif'] ?? ''));
        if (!$date || $date->format('Y-m-d') !== $voucher['tanggal_aktif'] || $initial <= 0 || $used < 0 || $balance <= 0
            || $initial - $used !== $balance || ($used === 0 ? $voucher['status'] !== 'AKTIF' : $voucher['status'] !== 'TERPAKAI_SEBAGIAN')) $fail();
        $mutations = $s->rows('SELECT * FROM tr_customer_deposit_mutasi WHERE id_deposit=? ORDER BY id ASC', array($voucher['id']));
        $running = 0; $in = 0; $out = 0; $inCount = 0; $sourceReceipt = null;
        foreach ($mutations as $mutation) {
            $amount = Penerimaan_calculation::cents($mutation['nominal']);
            if ((string)$mutation['id_customer'] !== (string)$voucher['id_customer'] || $amount <= 0
                || Penerimaan_calculation::cents($mutation['saldo_sebelum']) !== $running) $fail();
            if ($mutation['jenis_mutasi'] === 'IN') {
                if ($running !== 0 || ++$inCount !== 1 || $mutation['tanggal'] !== $voucher['tanggal_aktif']) $fail();
                $in += $amount; $running += $amount; $sourceReceipt = $mutation['no_transaksi'];
            } elseif ($mutation['jenis_mutasi'] === 'OUT') {
                $out += $amount; $running -= $amount;
                $this->deposit_receipt($mutation['no_transaksi'], $voucher, 'dep', $amount, false);
            } else $fail();
            if ($running < 0 || Penerimaan_calculation::cents($mutation['saldo_sesudah']) !== $running) $fail();
        }
        if ($inCount !== 1 || $in !== $initial || $out !== $used || $running !== $balance) $fail();
        if ($voucher['tipe_sumber'] === 'KWITANSI_DEPOSIT') {
            $kwRows = $s->rows('SELECT * FROM tr_kwitansi_deposit WHERE no_kwitansi=?', array($voucher['no_referensi']));
            $invoices = $s->rows("SELECT * FROM tr_invoicing WHERE no_invoice=? AND tax_treatment='DEP'", array($voucher['no_referensi']));
            if (count($kwRows) !== 1 || count($invoices) !== 1) $fail();
            $kw = $kwRows[0]; $invoice = $invoices[0];
            $snapshot = Invoice_consulting::snapshot($invoice);
            if ($kw['status'] !== 'LUNAS' || $kw['no_penerimaan'] !== $sourceReceipt
                || (string)$kw['id_customer'] !== (string)$voucher['id_customer'] || (string)$invoice['id_customer'] !== (string)$voucher['id_customer']
                || (string)$kw['id_company'] !== (string)$voucher['id_company'] || (string)$snapshot['owner']['id'] !== (string)$voucher['id_company']
                || !isset($snapshot['nilai']) || Penerimaan_calculation::cents($snapshot['nilai']) !== $initial
                || Penerimaan_calculation::cents($kw['nilai_kwitansi']) !== $initial || Penerimaan_calculation::cents($invoice['saldo_piutang']) !== 0) $fail();
            $this->deposit_receipt($sourceReceipt, $voucher, 'reduction', $initial, true, $invoice['id']);
        } elseif ($voucher['tipe_sumber'] === 'LEBIH_BAYAR') {
            if ($sourceReceipt !== $voucher['no_referensi']) $fail();
            $this->deposit_receipt($sourceReceipt, $voucher, 'lebih', $initial, true);
        } else $fail();
    }

    private function deposit_receipt($number, $voucher, $field, $amount, $exact, $invoiceId = null)
    {
        $headers = $this->s->rows('SELECT * FROM tr_penerimaan_piutang WHERE no_surat=?', array($number));
        $details = $this->s->rows('SELECT * FROM tr_penerimaan_piutang_detail WHERE id_header=?', array($number));
        $valid = count($headers) === 1 && (string)$headers[0]['id_customer'] === (string)$voucher['id_customer'] && $details;
        $total = 0; $cash = 0;
        foreach ($details as $detail) {
            $snapshot = json_decode($detail['consulting_snapshot'] ?? '', true);
            if (!$snapshot || (string)($snapshot['owner']['id'] ?? '') !== (string)$voucher['id_company'] || (string)$detail['id_customer'] !== (string)$voucher['id_customer']) $valid = false;
            foreach (array('cash','admin','pph','dep','lebih','balance','sisa','reduction') as $money) {
                if (!isset($snapshot[$money]) || !is_int($snapshot[$money]) || $snapshot[$money] < 0) $valid = false;
            }
            if ($valid && ($snapshot['balance'] - $snapshot['sisa'] !== $snapshot['reduction']
                || $snapshot['cash'] + $snapshot['admin'] + $snapshot['pph'] + $snapshot['dep'] !== $snapshot['reduction'] + $snapshot['lebih']
                || Penerimaan_calculation::cents($detail['penerimaan']) !== $snapshot['reduction']
                || Penerimaan_calculation::cents($detail['biaya_admin']) !== $snapshot['admin']
                || Penerimaan_calculation::cents($detail['pph23']) !== $snapshot['pph']
                || Penerimaan_calculation::cents($detail['sisa_piutang']) !== $snapshot['sisa'])) $valid = false;
            $cash += (int)($snapshot['cash'] ?? 0);
            if ($exact && $invoiceId !== null && (string)$detail['id_inv'] === (string)$invoiceId
                && (($snapshot['tax_treatment'] ?? '') !== 'DEP' || $snapshot['sisa'] !== 0 || $snapshot['pph'] !== 0 || $snapshot['dep'] !== 0 || $snapshot['lebih'] !== 0)) $valid = false;
            if ($invoiceId === null || (string)$detail['id_inv'] === (string)$invoiceId) $total += (int)($snapshot[$field] ?? 0);
        }
        if ($field === 'dep') {
            $mutations = $this->s->rows("SELECT m.*, d.id_customer AS deposit_customer, d.id_company AS deposit_company FROM tr_customer_deposit_mutasi m LEFT JOIN tr_customer_deposit d ON d.id=m.id_deposit WHERE m.no_transaksi=? AND m.jenis_mutasi='OUT' ORDER BY m.id", array($number));
            $recorded = 0;
            foreach ($mutations as $mutation) {
                if ((string)$mutation['deposit_customer'] !== (string)$voucher['id_customer'] || (string)$mutation['id_customer'] !== (string)$voucher['id_customer']
                    || (string)$mutation['deposit_company'] !== (string)$voucher['id_company']) $valid = false;
                $recorded += Penerimaan_calculation::cents($mutation['nominal']);
            }
            if ($recorded !== $total) $valid = false;
        }
        if (!$valid || Penerimaan_calculation::cents($headers[0]['nominal_penerimaan_bank']) !== $cash || ($exact ? $total !== $amount : $total < $amount)) throw new RuntimeException('Sumber penerimaan deposit belum konsisten; perlu rekonsiliasi.');
    }

    public function generate_deposit_number($date)
    {
        $year = date('Y', strtotime($date));
        $month = date('m', strtotime($date));
        $prefix = "DEP/{$year}/{$month}/";
        $row = $this->s->one("SELECT no_deposit FROM tr_customer_deposit WHERE no_deposit LIKE ? ORDER BY id DESC LIMIT 1", array($prefix . '%'));
        $seq = 1;
        if ($row && !empty($row['no_deposit'])) {
            $lastSeq = (int)substr($row['no_deposit'], strlen($prefix));
            $seq = $lastSeq + 1;
        }
        return $prefix . sprintf('%04d', $seq);
    }

    public function save($post)
    {
        $s = $this->s;
        return $s->mutate('save_consulting_receipt', function () use ($s, $post) {
            if (!has_permission('Penerimaan_Uang.Add')) throw new RuntimeException('Tidak memiliki izin penerimaan.');
            $tables = array('tr_invoicing', 'tr_jurnal', 'tr_penerimaan_piutang', 'tr_penerimaan_piutang_detail', 'tr_alokasi_detail', 'tr_alokasi_split');
            if ($s->db->table_exists('tr_customer_deposit')) $tables[] = 'tr_customer_deposit';
            if ($s->db->table_exists('tr_customer_deposit_mutasi')) $tables[] = 'tr_customer_deposit_mutasi';
            if ($s->db->table_exists('tr_kwitansi_deposit')) $tables[] = 'tr_kwitansi_deposit';
            $s->transactional($tables);
            if (!$s->db->field_exists('jenis_penerimaan', 'tr_penerimaan_piutang')) throw new RuntimeException('Terapkan migration 003_deposit_contract.');
            $count = $s->required($post, 'no_inv', 3);
            if (!ctype_digit($count) || (int)$count < 1 || (int)$count > 100) throw new InvalidArgumentException('Jumlah invoice tidak valid.');
            $ids = array(); for ($n = 1; $n <= (int)$count; $n++) $ids[] = $s->required($post, 'id_inv_' . $n);
            if (count(array_unique($ids)) !== count($ids)) throw new InvalidArgumentException('Invoice duplikat pada batch.');

            // The shared receipt lock serializes old/new balances and source allocation reads.
            $kind = ($post['receipt_kind'] ?? 'invoice') === 'deposit' ? 'deposit' : 'invoice';
            $allocation = $this->allocation($s->required($post, 'id_alokasi'), true, $kind);
            if (!hash_equals(Invoice_calculation::fingerprint($allocation), $s->required($post, 'allocation_fingerprint', 64))) throw new RuntimeException('Alokasi berubah; buat preview ulang.');
            $locked = $ids; sort($locked, SORT_STRING);
            foreach ($locked as $id) $s->one('SELECT id FROM tr_invoicing WHERE id=? FOR UPDATE', array($id));

            $depositChoice = in_array($post['deposit_choice'] ?? '', array('Y', 'N'), true) ? $post['deposit_choice'] : 'N';
            $rawDep = trim((string)($post['deposit_amount'] ?? '0'));
            if (strpos($rawDep, ',') !== false && strpos($rawDep, '.') !== false) {
                $rawDep = strrpos($rawDep, ',') > strrpos($rawDep, '.') ? str_replace(',', '.', str_replace('.', '', $rawDep)) : str_replace(',', '', $rawDep);
            } elseif (strpos($rawDep, ',') !== false) {
                $rawDep = str_replace(',', '.', $rawDep);
            }
            $depositAmount = $depositChoice === 'Y' ? Penerimaan_calculation::cents($rawDep) : 0;

            $preview = $this->preview(array('receipt_kind'=>$kind, 'choose_inv' => $ids, 'id_alokasi' => $post['id_alokasi'], 'pph23_dipotong' => $post['pph23_dipotong'] ?? '', 'ppn_dipotong' => $post['ppn_dipotong'] ?? 'N', 'deposit_choice' => $depositChoice, 'deposit_amount' => $post['deposit_amount'] ?? '0'));
            if ($kind === 'deposit') {
                foreach ($preview['rows'] as $context) {
                    $invoice = $context['invoice'];
                    $kw = $s->one("SELECT * FROM tr_kwitansi_deposit WHERE no_kwitansi=? AND status='MENUNGGU_PEMBAYARAN' FOR UPDATE", array($invoice['no_invoice']));
                    $voucher = $s->one("SELECT * FROM tr_customer_deposit WHERE no_referensi=? AND tipe_sumber='KWITANSI_DEPOSIT' FOR UPDATE", array($invoice['no_invoice']));
                    if (!$kw || !$voucher || $voucher['status'] !== 'MENUNGGU_PEMBAYARAN'
                        || (string)$kw['id_customer'] !== (string)$invoice['id_customer'] || (string)$voucher['id_customer'] !== (string)$invoice['id_customer']
                        || (string)$kw['id_company'] !== (string)$context['owner']['id'] || (string)$voucher['id_company'] !== (string)$context['owner']['id']
                        || Penerimaan_calculation::cents($kw['nilai_kwitansi']) !== Penerimaan_calculation::cents($invoice['saldo_piutang'])
                        || Penerimaan_calculation::cents($voucher['nominal_awal']) !== Penerimaan_calculation::cents($kw['nilai_kwitansi'])
                        || Penerimaan_calculation::cents($voucher['nominal_terpakai']) !== 0) throw new RuntimeException('Histori kwitansi deposit tidak konsisten; perlu rekonsiliasi.');
                }
            }
            $number = $s->ci->Penerimaan_uang_model->generate_id();
            $details = array(); $journals = array(); $invoiceUpdates = array(); $cashTotal = 0;
            $totPiutang = 0; $totPph = 0; $totAdmin = 0; $totSisa = 0;
            $totDep = 0; $totLebih = 0;

            $customerId = (string)$preview['rows'][0]['invoice']['id_customer'];
            $companyId = (string)$preview['rows'][0]['owner']['id'];
            $activeDeposit = $depositChoice === 'Y' ? $this->get_customer_active_deposit($customerId, $companyId) : 0;
            if ($depositChoice === 'Y') {
                if ($depositAmount <= 0) throw new InvalidArgumentException('Nominal deposit dipakai harus lebih besar dari 0.');
                if ($depositAmount > $activeDeposit) throw new InvalidArgumentException('Deposit dipakai (' . Penerimaan_calculation::decimal($depositAmount) . ') melebihi saldo deposit customer (' . Penerimaan_calculation::decimal($activeDeposit) . ').');
            }

            foreach ($preview['rows'] as $index => $context) {
                $invoice = $context['invoice'];
                if (isset($post['id_customer']) && (string)$post['id_customer'] !== $customerId) throw new InvalidArgumentException('Customer batch tidak sesuai.');
                if (isset($post['id_company']) && (string)$post['id_company'] !== $companyId) throw new InvalidArgumentException('Company batch tidak sesuai.');
                $n = array_search((string)$invoice['id'], array_map('strval', $ids), true) + 1;
                if (!hash_equals($context['fingerprint'], $s->required($post, 'receipt_fingerprint_' . $n, 64))) throw new RuntimeException('Saldo invoice berubah; buat preview ulang.');
                $allocated = Penerimaan_calculation::cents($s->required($post, 'penerimaan_' . $n, 32));
                $admin = Penerimaan_calculation::cents($s->required($post, 'biaya_admin_' . $n, 32));
                $balance = Penerimaan_calculation::cents($invoice['saldo_piutang']);

                $pph = $preview['choice'] === 'Y' ? Penerimaan_calculation::cents($s->required($post, 'pph_actual_' . $n, 32)) : 0;
                $sisa = Penerimaan_calculation::cents($s->required($post, 'sisa_piutang_' . $n, 32));
                $dep = $depositChoice === 'Y' ? Penerimaan_calculation::cents($post['dep_' . $n] ?? '0') : 0;
                $lebih = Penerimaan_calculation::cents($post['lebih_bayar_' . $n] ?? '0');

                if ($context['tax_treatment'] === 'DEP' && ($sisa !== 0 || $dep !== 0 || $lebih !== 0
                    || Penerimaan_calculation::cents($post['pph_actual_' . $n] ?? '0') !== 0 || Penerimaan_calculation::cents($post['dep_' . $n] ?? '0') !== 0)) {
                    throw new InvalidArgumentException('Kwitansi deposit harus dilunasi penuh tanpa pemakaian deposit atau lebih bayar.');
                }

                if (min($allocated, $admin, $pph, $sisa, $dep, $lebih) < 0) throw new InvalidArgumentException('Nilai tidak boleh negatif.');
                if ($admin > Penerimaan_calculation::MAX_ADMIN_CENTS) throw new InvalidArgumentException('Total biaya admin melebihi batas maksimal Rp 10.000.');
                if ($pph > $balance) throw new InvalidArgumentException('PPh 23 melebihi piutang dagang invoice ' . $invoice['no_invoice'] . '.');
                if ($sisa > $balance) throw new InvalidArgumentException('Sisa piutang melebihi piutang dagang invoice ' . $invoice['no_invoice'] . '.');

                if ($allocated + $sisa !== $balance || $allocated <= 0) throw new InvalidArgumentException('Alokasi penerimaan + sisa piutang harus sama dengan saldo invoice ' . $invoice['no_invoice'] . '.');
                if ($pph > $context['remaining_pph']) throw new InvalidArgumentException('PPh 23 melebihi sisa batas 2% DPP invoice ' . $invoice['no_invoice'] . '.');
                $reduction = $allocated;
                $bankCash = $balance - $pph - $admin - $sisa - $dep + $lebih;
                if ($bankCash < 0) throw new InvalidArgumentException('Penerimaan bank tidak boleh bernilai minus pada ' . $invoice['no_invoice'] . '.');

                $totPiutang += $balance;
                $totPph += $pph;
                $totAdmin += $admin;
                $totSisa += $sisa;
                $totDep += $dep;
                $totLebih += $lebih;
                $cashTotal += $bankCash;

                $receiptSnapshot = array(
                    'calculation_version' => 4,
                    'spk' => $context['spk'],
                    'sales' => $context['tax_calc']['sales'],
                    'dpp_lain' => $context['tax_calc']['dpp_lain'],
                    'ppn' => $context['tax_calc']['ppn'],
                    'tagihan' => $context['tax_calc']['tagihan'],
                    'pph_limit' => $context['tax_calc']['pph_default'],
                    'owner' => $context['owner'],
                    'accounts' => $context['accounts'],
                    'invoice_no' => $invoice['no_invoice'],
                    'date' => $allocation['detail']['tanggal_transaksi'],
                    'cash' => $bankCash,
                    'alok' => $reduction,
                    'admin' => $admin,
                    'pph' => $pph,
                    'sisa' => $sisa,
                    'dep' => $dep,
                    'lebih' => $lebih,
                    'balance' => $balance,
                    'reduction' => $reduction,
                    'tax_treatment' => $context['tax_treatment'] ?? 'PKP'
                );

                $details[] = array(
                    'id_header' => $number,
                    'id_alokasi' => $post['id_alokasi'],
                    'id_inv' => $invoice['id'],
                    'tgl_inv' => $invoice['tanggal_invoice'] ?? $invoice['tgl_invoice'] ?? date('Y-m-d'),
                    'id_customer' => $invoice['id_customer'],
                    'nm_customer' => $invoice['nm_customer'],
                    'dpp' => $invoice['total_nominal_jurnal'],
                    'dpp_lain' => Invoice_calculation::decimal($context['tax_calc']['dpp_lain']),
                    'ppn' => Invoice_calculation::decimal($context['tax_calc']['ppn']),
                    'tagihan_ppn' => Invoice_calculation::decimal($context['tax_calc']['tagihan']),
                    'pph23' => Invoice_calculation::decimal($pph),
                    'total' => Invoice_calculation::decimal($balance),
                    'penerimaan' => Invoice_calculation::decimal($reduction),
                    'biaya_admin' => Invoice_calculation::decimal($admin),
                    'sisa_piutang' => Invoice_calculation::decimal($sisa),
                    'consulting_version' => 3,
                    'consulting_snapshot' => json_encode($receiptSnapshot),
                    'consulting_reduction' => Invoice_calculation::decimal($reduction),
                    'created_by' => $s->ci->auth->user_id(),
                    'created_date' => date('Y-m-d H:i:s')
                );

                foreach (Invoice_consulting::receipt_journal($receiptSnapshot) as $line) {
                    $journals[] = array(
                        'no_jurnal' => Invoice_calculation::journal_number($s->db, count($journals) + 1),
                        'tgl_jurnal' => $line['date'],
                        'coa' => $line['coa'], 'nm_coa' => $line['name'],
                        'id_company' => $line['owner'], 'nm_company' => $line['company'],
                        'debit' => Invoice_calculation::decimal($line['debit']),
                        'kredit' => Invoice_calculation::decimal($line['credit']),
                        'keterangan' => $line['description'],
                        'no_transaksi' => $number, 'jenis_transaksi' => 'Penerimaan Piutang', 'sts' => '0',
                        'created_by' => $s->ci->auth->user_id(), 'created_date' => date('Y-m-d H:i:s')
                    );
                }

                $invoiceUpdates[] = array('id' => $invoice['id'], 'saldo_piutang' => Invoice_calculation::decimal($sisa));
            }

            if ($depositChoice === 'Y' && $totDep !== $depositAmount) {
                throw new InvalidArgumentException('Total Deposit Dipakai per invoice (' . Penerimaan_calculation::decimal($totDep) . ') harus sama dengan Nominal Deposit Dipakai di form (' . Penerimaan_calculation::decimal($depositAmount) . ').');
            }
            if ($totDep > 0 && $totLebih > 0) {
                throw new InvalidArgumentException('Deposit Dipakai dan Lebih Bayar tidak boleh terisi bersamaan. Kurangi deposit yang dipakai.');
            }

            $capacity = $allocation['capacity'] - $allocation['used'];
            if ($capacity <= 0) throw new RuntimeException('Alokasi bank sudah habis.');
            if ($totAdmin > Penerimaan_calculation::MAX_ADMIN_CENTS) throw new InvalidArgumentException('Total biaya admin melebihi batas maksimal Rp 10.000.');
            $kontrol = Penerimaan_calculation::kontrol($totPiutang, $totPph, $totAdmin, $totSisa, $capacity, $totDep, $totLebih);
            if ($kontrol !== 0) throw new RuntimeException('Kontrol belum 0 (selisih ' . Penerimaan_calculation::decimal($kontrol) . '). Tambahkan invoice atau sesuaikan pembagian alokasi bank.');

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
                'jenis_penerimaan' => $kind,
                'created_by' => $s->ci->auth->user_id(),
                'created_date' => date('Y-m-d H:i:s')
            )));
            $s->checked($s->db->insert_batch('tr_penerimaan_piutang_detail', $details));
            $s->checked($s->db->insert_batch('tr_jurnal', $journals));
            foreach ($invoiceUpdates as $upd) {
                $s->checked($s->db->update('tr_invoicing', array('saldo_piutang' => $upd['saldo_piutang']), array('id' => $upd['id'])));
            }

            $invNosList = array();
            foreach ($preview['rows'] as $ctx) $invNosList[] = $ctx['invoice']['no_invoice'];
            $invNosStr = implode(', ', $invNosList);

            if ($totDep > 0) {
                $activeDeposits = $this->active_deposits($customerId, $companyId, true);
                $remainingToConsume = $totDep;
                foreach ($activeDeposits as $ad) {
                    if ($remainingToConsume <= 0) break;
                    $adSaldo = Penerimaan_calculation::cents($ad['sisa_saldo']);
                    $adTerpakai = Penerimaan_calculation::cents($ad['nominal_terpakai']);
                    $take = min($remainingToConsume, $adSaldo);
                    $newSaldo = $adSaldo - $take;
                    $newTerpakai = $adTerpakai + $take;
                    $newStatus = ($newSaldo === 0) ? 'LUNAS_TERPAKAI' : 'TERPAKAI_SEBAGIAN';

                    $s->checked($s->db->update('tr_customer_deposit', array(
                        'sisa_saldo' => Invoice_calculation::decimal($newSaldo),
                        'nominal_terpakai' => Invoice_calculation::decimal($newTerpakai),
                        'status' => $newStatus,
                        'updated_by' => $s->ci->auth->user_id(),
                        'updated_date' => date('Y-m-d H:i:s')
                    ), array('id' => $ad['id'])));

                    $s->checked($s->db->insert('tr_customer_deposit_mutasi', array(
                        'id_deposit' => $ad['id'],
                        'id_customer' => $customerId,
                        'no_transaksi' => $number,
                        'tanggal' => $allocation['detail']['tanggal_transaksi'],
                        'jenis_mutasi' => 'OUT',
                        'nominal' => Invoice_calculation::decimal($take),
                        'saldo_sebelum' => Invoice_calculation::decimal($adSaldo),
                        'saldo_sesudah' => Invoice_calculation::decimal($newSaldo),
                        'keterangan' => 'Dipakai untuk penerimaan ' . $invNosStr,
                        'created_by' => $s->ci->auth->user_id(),
                        'created_date' => date('Y-m-d H:i:s')
                    )));
                    $remainingToConsume -= $take;
                }
                if ($remainingToConsume > 0) throw new RuntimeException('Saldo deposit customer tidak mencukupi untuk pemotongan FIFO.');
            }

            if ($totLebih > 0) {
                $depNo = $this->generate_deposit_number($allocation['detail']['tanggal_transaksi']);
                $s->checked($s->db->insert('tr_customer_deposit', array(
                    'no_deposit' => $depNo,
                    'id_customer' => $customerId,
                    'nm_customer' => $preview['rows'][0]['invoice']['nm_customer'],
                    'id_company' => $preview['rows'][0]['owner']['id'] ?? '1',
                    'tipe_sumber' => 'LEBIH_BAYAR',
                    'no_referensi' => $number,
                    'po_customer' => $preview['rows'][0]['spk'] ?? '',
                    'tanggal' => $allocation['detail']['tanggal_transaksi'],
                    'tanggal_aktif' => $allocation['detail']['tanggal_transaksi'],
                    'nominal_awal' => Invoice_calculation::decimal($totLebih),
                    'nominal_terpakai' => '0.00',
                    'sisa_saldo' => Invoice_calculation::decimal($totLebih),
                    'status' => 'AKTIF',
                    'keterangan' => 'Lebih bayar penerimaan ' . $invNosStr,
                    'created_by' => $s->ci->auth->user_id(),
                    'created_date' => date('Y-m-d H:i:s')
                )));
                $depId = $s->db->insert_id();
                $s->checked($s->db->insert('tr_customer_deposit_mutasi', array(
                    'id_deposit' => $depId,
                    'id_customer' => $customerId,
                    'no_transaksi' => $number,
                    'tanggal' => $allocation['detail']['tanggal_transaksi'],
                    'jenis_mutasi' => 'IN',
                    'nominal' => Invoice_calculation::decimal($totLebih),
                    'saldo_sebelum' => '0.00',
                    'saldo_sesudah' => Invoice_calculation::decimal($totLebih),
                    'keterangan' => 'Lebih bayar penerimaan ' . $invNosStr,
                    'created_by' => $s->ci->auth->user_id(),
                    'created_date' => date('Y-m-d H:i:s')
                )));
            }

            if ($s->db->table_exists('tr_kwitansi_deposit')) {
                foreach ($preview['rows'] as $ctx) {
                    $inv = $ctx['invoice'];
                    $kw = $s->one("SELECT * FROM tr_kwitansi_deposit WHERE no_kwitansi=? AND status='MENUNGGU_PEMBAYARAN' FOR UPDATE", array($inv['no_invoice']));
                    if ($kw) {
                        $s->checked($s->db->update('tr_kwitansi_deposit', array(
                            'status' => 'LUNAS',
                            'no_penerimaan' => $number,
                            'updated_by' => $s->ci->auth->user_id(),
                            'updated_date' => date('Y-m-d H:i:s')
                        ), array('id' => $kw['id'])));

                        $custDep = $s->one("SELECT * FROM tr_customer_deposit WHERE no_referensi=? AND tipe_sumber='KWITANSI_DEPOSIT' FOR UPDATE", array($kw['no_kwitansi']));
                        if ($custDep) {
                            $s->checked($s->db->update('tr_customer_deposit', array(
                                'status' => 'AKTIF',
                                'tanggal_aktif' => $allocation['detail']['tanggal_transaksi'],
                                'updated_by' => $s->ci->auth->user_id(),
                                'updated_date' => date('Y-m-d H:i:s')
                            ), array('id' => $custDep['id'])));

                            $s->checked($s->db->insert('tr_customer_deposit_mutasi', array(
                                'id_deposit' => $custDep['id'],
                                'id_customer' => $customerId,
                                'no_transaksi' => $number,
                                'tanggal' => $allocation['detail']['tanggal_transaksi'],
                                'jenis_mutasi' => 'IN',
                                'nominal' => $custDep['nominal_awal'],
                                'saldo_sebelum' => '0.00',
                                'saldo_sesudah' => $custDep['nominal_awal'],
                                'keterangan' => 'Pelunasan Kwitansi Deposit ' . $kw['no_kwitansi'],
                                'created_by' => $s->ci->auth->user_id(),
                                'created_date' => date('Y-m-d H:i:s')
                            )));
                        }
                    }
                }
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
            $tables = array('tr_invoicing', 'tr_jurnal', 'tr_penerimaan_piutang', 'tr_penerimaan_piutang_detail', 'tr_alokasi_detail', 'tr_alokasi_split');
            if ($s->db->table_exists('tr_customer_deposit')) $tables[] = 'tr_customer_deposit';
            if ($s->db->table_exists('tr_customer_deposit_mutasi')) $tables[] = 'tr_customer_deposit_mutasi';
            if ($s->db->table_exists('tr_kwitansi_deposit')) $tables[] = 'tr_kwitansi_deposit';
            $s->transactional($tables);
            $header = $s->one('SELECT * FROM tr_penerimaan_piutang WHERE id=? AND consulting_batch=3 FOR UPDATE', array($id));
            if (!$header) throw new RuntimeException('Penerimaan sudah dihapus atau tidak ditemukan.');
            $rows = $s->rows("SELECT * FROM tr_jurnal WHERE no_transaksi=? AND jenis_transaksi='Penerimaan Piutang' FOR UPDATE", array($header['no_surat']));
            if (!$rows) throw new RuntimeException('Jurnal penerimaan tidak lengkap; perlu rekonsiliasi.');
            foreach ($rows as $row) if ((string)$row['sts'] === '1') throw new RuntimeException('Jurnal penerimaan sudah diposting.');
            $details = $s->rows('SELECT * FROM tr_penerimaan_piutang_detail WHERE id_header=? ORDER BY id_inv FOR UPDATE', array($header['no_surat']));
            if (!$details) throw new RuntimeException('Rincian penerimaan tidak lengkap.');
            $expected = array(); $concept = true;
            foreach ($details as $detail) {
                $snapshot = json_decode($detail['consulting_snapshot'] ?? '', true);
                if ((int)($snapshot['calculation_version'] ?? 0) !== 4) { $concept = false; break; }
                if ($snapshot['reduction'] !== Penerimaan_calculation::cents($detail['consulting_reduction'])
                    || $snapshot['sisa'] !== Penerimaan_calculation::cents($detail['sisa_piutang'])) throw new RuntimeException('Snapshot rollback berbeda dari rincian; perlu rekonsiliasi.');
                foreach (Invoice_consulting::receipt_journal($snapshot) as $line) {
                    $expected[] = json_encode(array($line['date'], $line['owner'], $line['company'], $line['coa'], $line['name'], $line['debit'], $line['credit'], $line['description']));
                }
            }
            if ($concept) {
                $actual = array();
                foreach ($rows as $row) $actual[] = json_encode(array($row['tgl_jurnal'], (string)$row['id_company'], $row['nm_company'], $row['coa'], $row['nm_coa'], Penerimaan_calculation::cents($row['debit']), Penerimaan_calculation::cents($row['kredit']), $row['keterangan']));
                sort($expected, SORT_STRING); sort($actual, SORT_STRING);
                if ($expected !== $actual) throw new RuntimeException('Jurnal rollback berbeda dari snapshot; perlu rekonsiliasi.');
            }

            // Rollback guard for created Lebih Bayar deposit
            $createdDep = $s->one("SELECT * FROM tr_customer_deposit WHERE no_referensi=? AND tipe_sumber='LEBIH_BAYAR' FOR UPDATE", array($header['no_surat']));
            if ($createdDep) {
                $mutasiOut = $s->one("SELECT id FROM tr_customer_deposit_mutasi WHERE id_deposit=? AND jenis_mutasi='OUT' LIMIT 1", array($createdDep['id']));
                if ($mutasiOut || Penerimaan_calculation::cents($createdDep['nominal_terpakai']) > 0 || in_array($createdDep['status'], array('TERPAKAI_SEBAGIAN', 'LUNAS_TERPAKAI'), true)) {
                    throw new RuntimeException('Rollback ditolak: Saldo deposit lebih bayar dari transaksi ini sudah digunakan pada transaksi lain.');
                }
                $s->checked($s->db->delete('tr_customer_deposit_mutasi', array('id_deposit' => $createdDep['id'])));
                $s->checked($s->db->delete('tr_customer_deposit', array('id' => $createdDep['id'])));
            }

            // Restore consumed deposits (mutasi OUT)
            $consumedMutasi = $s->rows("SELECT * FROM tr_customer_deposit_mutasi WHERE no_transaksi=? AND jenis_mutasi='OUT' FOR UPDATE", array($header['no_surat']));
            foreach ($consumedMutasi as $cm) {
                $depRow = $s->one("SELECT * FROM tr_customer_deposit WHERE id=? FOR UPDATE", array($cm['id_deposit']));
                if ($depRow) {
                    $nom = Penerimaan_calculation::cents($cm['nominal']);
                    $curSaldo = Penerimaan_calculation::cents($depRow['sisa_saldo']);
                    $curTerpakai = Penerimaan_calculation::cents($depRow['nominal_terpakai']);
                    $awal = Penerimaan_calculation::cents($depRow['nominal_awal']);
                    $restoredSaldo = $curSaldo + $nom;
                    $restoredTerpakai = max(0, $curTerpakai - $nom);
                    $restoredSts = ($restoredSaldo >= $awal) ? 'AKTIF' : 'TERPAKAI_SEBAGIAN';
                    $s->checked($s->db->update('tr_customer_deposit', array(
                        'sisa_saldo' => Invoice_calculation::decimal($restoredSaldo),
                        'nominal_terpakai' => Invoice_calculation::decimal($restoredTerpakai),
                        'status' => $restoredSts,
                        'updated_by' => $s->ci->auth->user_id(),
                        'updated_date' => date('Y-m-d H:i:s')
                    ), array('id' => $depRow['id'])));
                }
                $s->checked($s->db->delete('tr_customer_deposit_mutasi', array('id' => $cm['id'])));
            }

            // Revert settled Kwitansi Deposit
            if ($s->db->table_exists('tr_kwitansi_deposit')) {
                $settledKws = $s->rows("SELECT * FROM tr_kwitansi_deposit WHERE no_penerimaan=? FOR UPDATE", array($header['no_surat']));
                foreach ($settledKws as $skw) {
                    $linkedDep = $s->one("SELECT * FROM tr_customer_deposit WHERE no_referensi=? AND tipe_sumber='KWITANSI_DEPOSIT' FOR UPDATE", array($skw['no_kwitansi']));
                    if ($linkedDep) {
                        $mutOut = $s->one("SELECT id FROM tr_customer_deposit_mutasi WHERE id_deposit=? AND jenis_mutasi='OUT' LIMIT 1", array($linkedDep['id']));
                        if ($mutOut || Penerimaan_calculation::cents($linkedDep['nominal_terpakai']) > 0 || in_array($linkedDep['status'], array('TERPAKAI_SEBAGIAN', 'LUNAS_TERPAKAI'), true)) {
                            throw new RuntimeException('Rollback ditolak: Saldo deposit dari kwitansi ' . $skw['no_kwitansi'] . ' sudah digunakan pada transaksi lain.');
                        }
                        $s->checked($s->db->update('tr_customer_deposit', array(
                            'status' => 'MENUNGGU_PEMBAYARAN',
                            'tanggal_aktif' => null,
                            'updated_by' => $s->ci->auth->user_id(),
                            'updated_date' => date('Y-m-d H:i:s')
                        ), array('id' => $linkedDep['id'])));
                        $s->checked($s->db->delete('tr_customer_deposit_mutasi', array(
                            'id_deposit' => $linkedDep['id'],
                            'no_transaksi' => $header['no_surat'],
                            'jenis_mutasi' => 'IN'
                        )));
                    }
                    $s->checked($s->db->update('tr_kwitansi_deposit', array(
                        'status' => 'MENUNGGU_PEMBAYARAN',
                        'no_penerimaan' => null,
                        'updated_by' => $s->ci->auth->user_id(),
                        'updated_date' => date('Y-m-d H:i:s')
                    ), array('id' => $skw['id'])));
                }
            }

            foreach ($details as $detail) {
                if ((string)($detail['sts_pph_23'] ?? '0') === '1'
                    || $s->one('SELECT id FROM tr_penerimaan_pph_23 WHERE id_detail_penerimaan=? LIMIT 1', array($detail['id']))) {
                    throw new RuntimeException('Penerimaan memiliki bukti potong terkait; selesaikan dependensinya terlebih dahulu.');
                }
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
            $allocation = $this->allocation($header['id_alokasi'], true, null); $cash = Penerimaan_calculation::cents($header['nominal_penerimaan_bank']);
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
