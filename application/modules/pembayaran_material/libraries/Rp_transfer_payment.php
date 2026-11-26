<?php

require_once dirname(__DIR__, 2).'/request_payment/libraries/Rp_transfer.php';
require_once dirname(__DIR__, 2).'/jurnal_all/libraries/Payment_reference.php';
require_once dirname(__DIR__, 2).'/expense/libraries/Expense_company.php';
require_once __DIR__.'/Payment_journal_validator.php';

final class Rp_transfer_payment
{
    public static function nextVoucher($db, $date)
    {
        $prefix = 'BK--' . date('my', strtotime($date)) . '-';
        $pattern = '^' . $prefix . '[0-9]{4}$';
        $last = Rp_transfer::write($db->query(
            'SELECT MAX(candidate) AS last_voucher FROM (
                SELECT CAST(MAX(id) AS CHAR CHARACTER SET ascii) AS candidate FROM tr_payment_paid WHERE id REGEXP ?
                UNION ALL SELECT CAST(MAX(id) AS CHAR CHARACTER SET ascii) FROM payment_approve WHERE id REGEXP ?
                UNION ALL SELECT CAST(MAX(no_transaksi) AS CHAR CHARACTER SET ascii) FROM tr_jurnal WHERE no_transaksi REGEXP ?
            ) AS used_vouchers', [$pattern, $pattern, $pattern]
        ))->row_array();
        $next = (int) substr((string) $last['last_voucher'], -4) + 1;
        if ($next > 9999) {
            throw new DomainException('Nomor BK bulan ini sudah habis.');
        }
        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public static function find($db, $id, $lock = false)
    {
        if (!Rp_transfer::installed($db)) {
            throw new DomainException('Transfer tidak tersedia.');
        }
        $transfer = Rp_transfer::write($db->query(
            "SELECT t.*, h.no_pengajuan, h.company_id, h.company_nama FROM tr_rp_transfer t
             JOIN tr_rp_pengajuan_h h ON h.id=t.id_pengajuan
             WHERE t.id=? AND h.status='done' AND h.deleted=0" . ($lock ? ' FOR UPDATE' : ''), [$id]
        ))->row_array();
        if (!$transfer || !in_array($transfer['status'], ['approved', 'paid'], true)) {
            throw new DomainException('Transfer tidak ditemukan atau belum disetujui.');
        }
        $transfer['docs'] = Rp_transfer::write($db->query(
            "SELECT d.*, pa.status AS payment_status, pa.id_payment AS voucher, pa.tgl_bayar,
                    pa.payment_bank, pa.keterangan_pembayaran, pa.coa_bank
             FROM tr_rp_pengajuan_d d LEFT JOIN payment_approve pa ON BINARY pa.id=BINARY d.payment_approval_id
             WHERE d.id_transfer=? AND d.id_pengajuan=? AND d.decision='approved' ORDER BY d.id" . ($lock ? ' FOR UPDATE' : ''),
            [$id, $transfer['id_pengajuan']]
        ))->result_array();
        if (!$transfer['docs']) {
            throw new DomainException('Transfer tidak memiliki dokumen yang disetujui.');
        }
        return $transfer;
    }

    /** Antrean transfer siap bayar untuk DataTables server-side. $orderColumn = indeks kolom di view. */
    public static function queuePage($db, $search = '', $start = 0, $length = 10, $orderColumn = null, $direction = 'asc')
    {
        $from = " FROM tr_rp_transfer t JOIN tr_rp_pengajuan_h h ON h.id=t.id_pengajuan
             JOIN tr_rp_pengajuan_d d ON d.id_transfer=t.id AND d.decision='approved'
             WHERE t.status='approved' AND h.status='done' AND h.deleted=0";
        $count = static function ($where, $values) use ($db) {
            return (int) (Rp_transfer::write($db->query('SELECT COUNT(DISTINCT t.id) AS total' . $where, $values))->row_array()['total'] ?? 0);
        };
        $total = $count($from, []);
        $filtered = $total;
        $values = [];
        if ($search !== '') {
            // EXISTS agar pencarian nomor dokumen tidak mengurangi dokumen yang dijumlahkan.
            $from .= " AND (h.no_pengajuan LIKE ? OR h.company_nama LIKE ? OR t.nama LIKE ? OR t.bank LIKE ? OR t.no_rek LIKE ?
                 OR EXISTS (SELECT 1 FROM tr_rp_pengajuan_d s WHERE s.id_transfer=t.id AND s.decision='approved'
                     AND (s.no_dokumen LIKE ? OR s.kategori LIKE ?)))";
            $values = array_fill(0, 7, '%' . addcslashes($search, '%_\\') . '%');
            $filtered = $count($from, $values);
        }
        $columns = [1 => 'h.no_pengajuan', 2 => 'h.decided_at', 3 => 'h.company_nama', 4 => 't.nama',
            8 => "SUM(d.dibayarkan) - CASE WHEN t.bearer='recipient' THEN t.admin ELSE 0 END"];
        $order = isset($columns[$orderColumn])
            ? $columns[$orderColumn] . ($direction === 'desc' ? ' DESC' : ' ASC') . ', t.id' : 'h.no_pengajuan, t.urutan';
        $rows = Rp_transfer::write($db->query(
            "SELECT t.id, h.no_pengajuan, h.company_nama, h.decided_at, t.urutan, t.bank, t.no_rek, t.nama, t.admin, t.bearer,
                    GROUP_CONCAT(d.no_dokumen ORDER BY d.id SEPARATOR ', ') AS numbers,
                    GROUP_CONCAT(DISTINCT d.kategori ORDER BY d.kategori SEPARATOR ' + ') AS categories,
                    SUM(d.dibayarkan) AS net" . $from . "
             GROUP BY t.id, h.no_pengajuan, h.company_nama, h.decided_at ORDER BY " . $order .
            ' LIMIT ' . max(0, (int) $start) . ', ' . min(100, max(1, (int) $length)), $values
        ))->result_array();
        return ['total' => $total, 'filtered' => $filtered, 'rows' => $rows];
    }

    /**
     * Dokumen jalur lama yang dicentang: dibayar lewat form transfer ('transfer') atau form lama ('legacy').
     * Campuran, atau layak tetapi tidak bisa jadi satu transfer, ditolak dengan alasannya.
     */
    public static function target($db, array $paymentIds, $since)
    {
        $found = self::candidates($db, $paymentIds, $since);
        if (!$found['eligible']) {
            return 'legacy';
        }
        self::sameTransfer($found);
        return 'transfer';
    }

    /** Transfer yang belum disimpan, bentuknya sama dengan find() sehingga preview dan jurnal memakai jalur yang sama. */
    public static function draft($db, array $paymentIds, array $input, $since)
    {
        $found = self::candidates($db, $paymentIds, $since);
        $header = self::sameTransfer($found);
        // Admin yang dulu sudah dipotong per dokumen dibawa apa adanya, supaya uang yang diterima tidak berubah.
        $carried = array_sum(array_column($found['eligible'], 'legacy_admin'));
        if ($carried) {
            $input = array_merge($input, ['admin' => Rp_transfer::money($carried), 'bearer' => 'recipient']);
        }
        $bearer = $input['bearer'] ?? '';
        if (!in_array($bearer, ['company', 'recipient'], true)) {
            throw new DomainException('Penanggung admin wajib dipilih.');
        }
        $admin = Rp_transfer::cents(($input['admin'] ?? '') === '' ? '0' : $input['admin']);
        $last = Rp_transfer::write($db->select('urutan')->order_by('urutan', 'DESC')
            ->get_where('tr_rp_transfer', ['id_pengajuan' => $header['id']]))->row_array();
        $transfer = Rp_transfer::account($input) + [
            'id' => 0, 'id_pengajuan' => (int) $header['id'], 'urutan' => (int) ($last['urutan'] ?? 0) + 1,
            'admin' => Rp_transfer::money($admin), 'bearer' => $bearer, 'status' => 'approved', 'id_payment' => null,
            'no_pengajuan' => $header['no_pengajuan'], 'company_id' => $header['company_id'], 'company_nama' => $header['company_nama'],
            'docs' => $found['eligible'],
        ];
        Rp_transfer::totals(self::planned($transfer)['net'], $admin, $bearer);
        return $transfer;
    }

    /** Kunci dokumen draft sampai transaksi selesai, supaya tidak dibayar dua kali. */
    public static function lock($db, array $paymentIds)
    {
        $marks = implode(',', array_fill(0, count($paymentIds), '?'));
        Rp_transfer::write($db->query('SELECT id FROM payment_approve WHERE id IN (' . $marks . ') FOR UPDATE', array_values($paymentIds)));
    }

    /** Simpan draft menjadi transfer. Wajib dipanggil di dalam transaksi pembayaran. */
    public static function create($db, array $transfer)
    {
        Rp_transfer::write($db->insert('tr_rp_transfer', [
            'id_pengajuan' => $transfer['id_pengajuan'], 'urutan' => $transfer['urutan'], 'bank' => $transfer['bank'],
            'no_rek' => $transfer['no_rek'], 'nama' => $transfer['nama'], 'admin' => $transfer['admin'],
            'bearer' => $transfer['bearer'], 'status' => 'approved',
        ]));
        $id = (int) $db->insert_id();
        if ($id < 1) {
            throw new RuntimeException('Transfer gagal dibuat.');
        }
        foreach ($transfer['docs'] as $doc) {
            // ID approval lama dipakai apa adanya; hanya dihubungkan ke transfer.
            $change = ['id_transfer' => $id, 'payment_approval_id' => $doc['payment_approval_id']];
            if (!empty($doc['legacy_admin'])) {
                // Admin pindah dari dokumen ke transfer: DIBAYARKAN kembali DPP + PPN - PPh.
                $change += ['admin' => 0, 'dibayarkan' => $doc['dibayarkan']];
            }
            Rp_transfer::write($db->where('id', $doc['id'])->where('id_transfer', null)->update('tr_rp_pengajuan_d', $change));
            if ($db->affected_rows() !== 1) {
                throw new DomainException('Dokumen sudah masuk transfer lain. Muat ulang daftar.');
            }
        }
        return $id;
    }

    /** Total admin lama (sen) yang sudah dipotong per dokumen pada pilihan ini. */
    public static function carriedAdmin($db, array $paymentIds, $since)
    {
        return array_sum(array_column(self::candidates($db, $paymentIds, $since)['eligible'], 'legacy_admin'));
    }

    private static function sameTransfer(array $found)
    {
        if ($found['rejected']) {
            throw new DomainException('Pilihan tidak bisa digabung. Bayar terpisah: ' . implode(', ', $found['rejected']) . '.');
        }
        if (count($found['headers']) !== 1) {
            throw new DomainException('1 payment = 1 transfer. Pilih dokumen dari No. Pengajuan yang sama.');
        }
        if (count($found['eligible']) > 1) {
            foreach ($found['eligible'] as $doc) {
                if (Rp_transfer::groupType($doc['kategori']) === 'refill') {
                    throw new DomainException('Refill Petty Cash harus dibayar sendiri.');
                }
            }
        }
        return reset($found['headers']);
    }

    private static function candidates($db, array $paymentIds, $since)
    {
        $paymentIds = array_values(array_unique(array_filter(array_map('strval', $paymentIds), 'strlen')));
        if (!$paymentIds || count($paymentIds) > 100) {
            throw new DomainException('Pilih minimal satu transaksi pembayaran.');
        }
        $payments = Rp_transfer::write($db->where_in('id', $paymentIds)->get('payment_approve'))->result_array();
        if (count($payments) !== count($paymentIds)) {
            throw new DomainException('Dokumen pembayaran tidak ditemukan. Kosongkan pilihan dan pilih ulang.');
        }
        $details = [];
        $rows = Rp_transfer::write($db->where_in('no_dokumen', array_column($payments, 'no_doc'))->where('decision', 'approved')
            ->get('tr_rp_pengajuan_d'))->result_array();
        foreach ($rows as $row) {
            $details[$row['no_dokumen']][] = $row;
        }
        $headers = [];
        if ($rows) {
            foreach (Rp_transfer::write($db->where_in('id', array_values(array_unique(array_column($rows, 'id_pengajuan'))))
                ->get('tr_rp_pengajuan_h'))->result_array() as $row) {
                $headers[$row['id']] = $row;
            }
        }
        $found = ['eligible' => [], 'rejected' => [], 'headers' => []];
        $cents = static function ($value) {
            return (int) round((float) $value * 100);
        };
        foreach ($payments as $payment) {
            $detail = count($details[$payment['no_doc']] ?? []) === 1 ? $details[$payment['no_doc']][0] : null;
            $header = $detail ? ($headers[$detail['id_pengajuan']] ?? null) : null;
            // Selain syarat ini (periode lama, Hutang Petty Cash) tetap di form lama.
            $gross = $detail ? $cents($detail['dpp']) + $cents($detail['nilai_ppn']) - $cents($detail['nilai_pph']) : 0;
            $admin = $detail ? $cents($detail['admin']) : 0;
            if (!$header || $header['status'] !== 'done' || (int) $header['deleted'] !== 0
                || (string) $payment['status'] !== '1' || !empty($payment['id_payment']) || (string) $payment['created_on'] < $since
                || !empty($detail['id_transfer']) || !empty($detail['payment_approval_id'])
                || Rp_transfer::groupType($detail['kategori']) === 'debt' || Rp_transfer::groupType($payment['tipe']) === 'debt'
                || $cents($payment['jumlah']) !== $cents($detail['dpp'])
                || $admin < 0 || $cents($detail['dibayarkan']) !== $gross - $admin) {
                $found['rejected'][] = $payment['no_doc'];
                continue;
            }
            $detail['dibayarkan'] = Rp_transfer::money($gross);
            $found['eligible'][] = $detail + [
                'legacy_admin' => $admin,
                'payment_status' => $payment['status'], 'voucher' => null, 'tgl_bayar' => null, 'payment_bank' => null,
                'keterangan_pembayaran' => null, 'coa_bank' => null,
            ];
            $found['eligible'][count($found['eligible']) - 1]['payment_approval_id'] = (string) $payment['id'];
            $found['headers'][$header['id']] = $header;
        }
        return $found;
    }

    public static function banks($db, $company)
    {
        $rows = Rp_transfer::write($db->select('a.id, a.nama, a.rekening, a.coa_bank, b.nama_bank')
            ->from('ms_bank a')->join('list_bank b', 'b.id=a.bank', 'left')->where('a.deleted', '0')
            ->order_by('b.nama_bank')->get())->result_array();
        $banks = [];
        foreach ($rows as $row) {
            try {
                if ($row['coa_bank'] !== '' && $row['coa_bank'] !== null && Expense_company::bankCompanyId($row) === (string) $company) {
                    $banks[(string) $row['id']] = $row;
                }
            } catch (DomainException $e) {
                continue;
            }
        }
        return $banks;
    }

    public static function planned(array $transfer)
    {
        $net = 0;
        foreach ($transfer['docs'] as $doc) {
            $net += Rp_transfer::cents($doc['dibayarkan']);
        }
        return Rp_transfer::totals($net, Rp_transfer::cents($transfer['admin']), $transfer['bearer']);
    }

    public static function actual(array $transfer, array $input)
    {
        $planned = self::planned($transfer);
        $mode = $input['actual_bearer'] ?? '';
        if (!in_array($mode, ['', 'company', 'recipient', 'free'], true)) {
            throw new DomainException('Penanggung admin aktual tidak valid.');
        }
        $mode = $mode ?: $transfer['bearer'];
        $defaultCharge = $mode === 'free' ? 0 : Rp_transfer::cents($transfer['admin']);
        $charge = Rp_transfer::cents(($input['bank_charge'] ?? '') === '' ? Rp_transfer::money($defaultCharge) : $input['bank_charge'], true);
        // RP-R-36: Payment Bank = nominal transfer ke penerima (baris TRF); total keluar bank = Payment Bank + Bank Charge.
        $plannedTransfer = Rp_transfer::totals($planned['net'], $defaultCharge, $mode)['transfer'];
        $received = ($input['payment_bank'] ?? '') === '' ? $plannedTransfer : Rp_transfer::cents($input['payment_bank']);
        if ($received <= 0) {
            throw new DomainException('Payment Bank harus lebih dari 0.');
        }
        if ($charge < 0) {
            throw new DomainException('Bank Charge tidak boleh minus.');
        }
        if ($mode === 'free' && $charge !== 0) {
            throw new DomainException('Admin aktual Gratis: Bank Charge harus 0.');
        }
        $bank = $received + $charge;
        $note = $input['keterangan_pembayaran'] ?? '';
        if (!is_string($note) || strlen($note) > 2000) {
            throw new DomainException('Keterangan Pembayaran tidak valid.');
        }
        $difference = $mode !== $transfer['bearer'] || $charge !== Rp_transfer::cents($transfer['admin']) || $received !== $planned['transfer'];
        return [
            'mode' => $mode, 'charge' => $charge, 'bank' => $bank, 'transfer' => $received,
            'net' => $planned['net'], 'adjustment' => $bank - $planned['net'], 'note' => trim($note), 'difference' => $difference,
        ];
    }

    public static function journal($controller, array $transfer, array $input)
    {
        $date = $input['tgl_bayar'] ?? '';
        $parsed = is_string($date) ? DateTimeImmutable::createFromFormat('!Y-m-d', $date) : false;
        if (!$parsed || $parsed->format('Y-m-d') !== $date) {
            throw new DomainException('Tanggal bayar tidak valid.');
        }
        $actual = self::actual($transfer, $input);
        $refill = Rp_transfer::groupType($transfer['docs'][0]['kategori']) === 'refill';
        $company = $refill ? '7' : (string) $transfer['company_id'];
        $target = Payment_reference::target($company);
        if ($target === null) {
            throw new DomainException('Company transfer belum memiliki tujuan jurnal.');
        }
        $bankId = $input['bank'] ?? '';
        if (!is_scalar($bankId)) {
            throw new DomainException('Bank pembayar tidak valid.');
        }
        $bank = $bankId === '' ? null : (self::banks($controller->db, $company)[(string) $bankId] ?? null);
        if ($bankId !== '' && !$bank) {
            throw new DomainException('Bank pembayar harus milik company yang sesuai.');
        }
        $docs = [];
        foreach ($transfer['docs'] as $doc) {
            if ((string) $doc['payment_status'] !== '1' || !$doc['payment_approval_id'] || $doc['voucher']) {
                throw new DomainException('Salah satu dokumen sudah dibayar atau belum siap dibayar.');
            }
            $docs[$doc['payment_approval_id']] = $doc;
        }
        $zero = array_fill_keys(array_keys($docs), '0');
        $summary = [];
        $model = $controller->Pembayaran_material_model;
        $lines = $model->set_jurnal($summary, [
            'id_payment' => implode(',', array_keys($docs)), 'tgl_bayar' => $date,
            'total_payment' => Rp_transfer::money($actual['net']),
            'item_ppn' => $zero, 'item_pph' => $zero, 'pph_data' => array_fill_keys(array_keys($docs), ''),
        ]);
        $coa = [];
        $journal = [];
        $normal = ['id_company' => $company, 'nm_company' => $refill ? 'STM' : $transfer['company_nama'], 'tgl_jurnal' => $date,
            'jenis_transaksi' => $refill ? 'Refill Pettycash' : 'Payment'];
        $add = static function ($code, $label, $debit, $credit, array $division) use (&$journal, &$coa, $normal) {
            if (!$debit && !$credit) {
                return;
            }
            $coa[$code] = true;
            $journal[] = $normal + $division + ['coa' => $code, 'keterangan' => $label, 'debit' => Rp_transfer::money($debit), 'kredit' => Rp_transfer::money($credit)];
        };
        $principal = [];
        $divisions = [];
        $byDoc = array_column($docs, 'payment_approval_id', 'no_dokumen');
        foreach ($lines as $line) {
            $id = $byDoc[$line['reference']] ?? $line['reference'];
            if (!isset($docs[$id])) {
                throw new DomainException('Referensi baris jurnal tidak cocok dengan transfer.');
            }
            $doc = $docs[$id];
            $division = ['id_divisi' => $line['id_divisi'] ?? '', 'nm_divisi' => $line['nm_divisi'] ?? ''];
            $divisions[$id] = $divisions[$id] ?? $division;
            $debit = Rp_transfer::cents(Rp_transfer::money((int) round((float) $line['debit'] * 100)));
            $credit = Rp_transfer::cents(Rp_transfer::money((int) round((float) $line['kredit'] * 100)));
            if ($bank && $credit > 0 && $line['coa'] === $bank['coa_bank']) {
                throw new DomainException('Sumber jurnal memuat kredit bank tambahan.');
            }
            $principal[$id] = ($principal[$id] ?? 0) + $debit - $credit;
            $label = (string) $line['keterangan'];
            $suffix = ' - ' . $doc['no_dokumen'];
            $add((string) $line['coa'], substr($label, -strlen($suffix)) === $suffix ? $label : $label . $suffix, $debit, $credit, $division);
        }
        foreach ($docs as $id => $doc) {
            if (!isset($divisions[$id]) || $principal[$id] !== Rp_transfer::cents($doc['dpp'])) {
                throw new DomainException('COA atau nominal pokok jurnal belum lengkap untuk ' . $doc['no_dokumen'] . '.');
            }
            $is21 = (bool) $doc['flag_pph21'];
            $add('1106-01-06', 'PPN - ' . $doc['no_dokumen'], Rp_transfer::cents($doc['nilai_ppn']), 0, $divisions[$id]);
            $add($is21 ? '2104-01-02' : '2104-01-03', 'PPh ' . ($is21 ? '21' : '23') . ' - ' . $doc['no_dokumen'], 0, Rp_transfer::cents($doc['nilai_pph']), $divisions[$id]);
        }
        $first = reset($divisions);
        $refs = implode(', ', array_column($docs, 'no_dokumen'));
        $add('7201-01-04', $actual['adjustment'] >= 0 ? 'Biaya Transfer - ' . $refs : 'Admin dipotong dari transfer, bank tidak memotong biaya - ' . $refs,
            max(0, $actual['adjustment']), max(0, -$actual['adjustment']), $first);
        // RP-R-36: Cr Bank 2 baris sesuai jurnal aktual, TRF (Payment Bank) dan BIAYA TRANSFER (Bank Charge).
        $bankCoa = $bank['coa_bank'] ?? '(pilih bank)';
        $bankName = $bank['nama_bank'] ?? 'Bank belum dipilih';
        $add($bankCoa, $bankName . ' - TRF ke ' . $transfer['nama'], 0, $actual['transfer'], $first);
        $add($bankCoa, $bankName . ' - BIAYA TRANSFER ke ' . $transfer['nama'], 0, $actual['charge'], $first);
        $accounting = $controller->load->database($target, true);
        $names = array_column(Rp_transfer::write($accounting->select('no_perkiraan, nama')->where_in('no_perkiraan', array_keys($coa))->get('coa_master'))->result_array(), 'nama', 'no_perkiraan');
        foreach ($journal as &$row) {
            $row['nm_coa'] = $names[$row['coa']] ?? '';
            if ($row['nm_coa'] === '' && $row['coa'] !== '(pilih bank)') {
                throw new DomainException('COA ' . ($row['coa'] ?: '(kosong)') . ' belum tersedia di company pembayaran.');
            }
        }
        unset($row);
        return ['rows' => $journal, 'actual' => $actual, 'date' => $date, 'bank' => $bank, 'refill' => $refill];
    }
}
