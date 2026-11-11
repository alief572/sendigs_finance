<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once __DIR__ . '/../libraries/Piutang_aging.php';

/**
 * Akses data Monitoring Piutang / Follow Up Piutang / Dashboard Plan Bayar.
 * Hanya MEMBACA tr_invoicing.saldo_piutang (E2E-NFR-03); tulis hanya ke tr_invoice_ar* dan kolom close di tr_invoicing.
 */
class Monitoring_piutang_model extends BF_Model
{
    const AR_FIELDS = ['tgl_kirim', 'kirim_via', 'ket_kirim', 'no_resi', 'tgl_terima', 'ket_terima', 'bukti_terima', 'bukti_terima_nama', 'fu_pic_nama', 'fu_pic_telp',
        'fu1_plan', 'fu1_aktual', 'fu1_ket', 'fu2_plan', 'fu2_aktual', 'fu2_ket', 'fu3_plan', 'fu3_aktual', 'fu3_ket',
        'fu4_plan', 'fu4_aktual', 'fu4_ket', 'plan_bayar', 'ket_plan_bayar'];

    public function __construct()
    {
        parent::__construct();
    }

    /** Query dasar: invoice + data AR + company (customer/project invoice lama yang kosong diambil dari SPK) (konsultasi lewat penawaran/SPK, non konsultasi lewat penawaran non konsultasi). */
    private function base_sql()
    {
        return "SELECT i.id, i.no_invoice, i.tanggal_invoice AS tgl_invoice, COALESCE(NULLIF(i.nm_customer, ''), s.nm_customer) AS customer, COALESCE(NULLIF(i.nm_project, ''), mk.nm_paket) AS project, i.id_spk_penawaran AS no_spk,
                    i.total_akhir_jurnal AS nilai, i.saldo_piutang AS saldo, i.sts_close, i.close_date, i.close_reason,
                    c.id AS company, c.nm_company AS company_name,
                    (SELECT d.urutan FROM kons_tr_plan_tagih_detail d WHERE d.id = i.id_detail_plan_tagih LIMIT 1) AS top_ke,
                    ar.tgl_kirim, ar.kirim_via, ar.ket_kirim, ar.no_resi, ar.tgl_terima, ar.ket_terima, ar.bukti_terima, ar.bukti_terima_nama, ar.fu_pic_nama, ar.fu_pic_telp,
                    ar.fu1_plan, ar.fu1_aktual, ar.fu1_ket, ar.fu2_plan, ar.fu2_aktual, ar.fu2_ket,
                    ar.fu3_plan, ar.fu3_aktual, ar.fu3_ket, ar.fu4_plan, ar.fu4_aktual, ar.fu4_ket,
                    ar.plan_bayar, ar.ket_plan_bayar, ar.plan_bayar_nominal, ar.status_from, ar.status_changed_date
                FROM tr_invoicing i
                LEFT JOIN tr_invoice_ar ar ON ar.id_invoice = i.id
                LEFT JOIN " . DBCNL . ".kons_tr_penawaran p ON p.id_quotation = i.id_penawaran AND i.non_kons <> '1'
                LEFT JOIN " . DBCNL . ".kons_tr_spk_penawaran s ON s.id_spk_penawaran = i.id_spk_penawaran AND i.non_kons <> '1'
                LEFT JOIN " . DBCNL . ".kons_master_konsultasi_header mk ON mk.id_konsultasi_h = s.id_project
                LEFT JOIN " . DBCNL . ".kons_tr_penawaran_non_konsultasi nk ON nk.id_penawaran = i.id_penawaran AND i.non_kons = '1'
                LEFT JOIN " . DBCNL . ".kons_tr_company c ON c.id = IF(i.non_kons = '1', nk.id_company, COALESCE(p.company, s.id_company))";
    }

    private function attach_extra(array $rows)
    {
        $extra = [];
        if ($rows) {
            foreach ($this->db->query('SELECT id_invoice, ke, plan, aktual, ket FROM tr_invoice_ar_fu ORDER BY ke')->result_array() as $e) {
                $extra[$e['id_invoice']][] = ['ke' => (int) $e['ke'], 'plan' => $e['plan'], 'aktual' => $e['aktual'], 'ket' => $e['ket']];
            }
        }
        foreach ($rows as &$r) {
            $r['extra_fu'] = $extra[$r['id']] ?? [];
        }
        return $rows;
    }

    /**
     * Invoice untuk daftar. $closed: false = hanya belum close; tahun (int) = belum close + yang close di tahun itu;
     * true = semua (Dashboard Plan Bayar menghitung invoice close).
     */
    public function get_rows($closed = false)
    {
        // Kwitansi Deposit (KD-…, tax_treatment DEP) bukan piutang tagih: dikeluarkan dari semua modul piutang.
        $sql = $this->base_sql() . " WHERE COALESCE(i.tax_treatment, '') <> 'DEP' AND i.id NOT LIKE 'KD-%'";
        $bind = [];
        if ($closed === false) {
            $sql .= " AND COALESCE(i.sts_close, '0') <> '1'";
        } elseif ($closed !== true) {
            $sql .= " AND (COALESCE(i.sts_close, '0') <> '1' OR YEAR(i.close_date) = ?)";
            $bind[] = (int) $closed;
        }
        return $this->attach_extra($this->db->query($sql, $bind)->result_array());
    }

    public function get_row($id)
    {
        $rows = $this->attach_extra($this->db->query($this->base_sql() . ' WHERE i.id = ?', [$id])->result_array());
        return $rows ? $rows[0] : null;
    }

    public function get_receipts($id)
    {
        return $this->db->query(
            "SELECT ppd.id_header AS no_penerimaan, COALESCE(ppd.consulting_reduction, COALESCE(ppd.penerimaan, 0) + COALESCE(ppd.biaya_admin, 0)) AS nominal,
                    COALESCE(ad.tanggal_transaksi, DATE(pph.created_date)) AS tanggal
             FROM tr_penerimaan_piutang_detail ppd
             LEFT JOIN tr_penerimaan_piutang pph ON pph.no_surat = ppd.id_header
             LEFT JOIN tr_alokasi_split sp ON sp.id = pph.id_alokasi
             LEFT JOIN tr_alokasi_detail ad ON ad.id = COALESCE(sp.id_alokasi_detail, pph.id_alokasi)
             WHERE ppd.id_inv = ? ORDER BY tanggal",
            [$id]
        )->result_array();
    }

    /**
     * Semua penerimaan piutang di tahun $year (MP-FR-14): tanggal = tanggal transaksi bank dari alokasi
     * (cadangan tanggal input), nominal = pengurang piutang (cadangan penerimaan + biaya admin).
     */
    public function get_realisasi($year)
    {
        return $this->db->query(
            "SELECT t.id_inv, t.no_penerimaan, t.tanggal, t.nominal FROM (
                SELECT ppd.id_inv, ppd.id_header AS no_penerimaan,
                       COALESCE(ad.tanggal_transaksi, DATE(pph.created_date)) AS tanggal,
                       COALESCE(ppd.consulting_reduction, COALESCE(ppd.penerimaan, 0) + COALESCE(ppd.biaya_admin, 0)) AS nominal
                FROM tr_penerimaan_piutang_detail ppd
                LEFT JOIN tr_penerimaan_piutang pph ON pph.no_surat = ppd.id_header
                LEFT JOIN tr_alokasi_split sp ON sp.id = pph.id_alokasi
                LEFT JOIN tr_alokasi_detail ad ON ad.id = COALESCE(sp.id_alokasi_detail, pph.id_alokasi)
             ) t WHERE YEAR(t.tanggal) = ?",
            [(int) $year]
        )->result_array();
    }

    public function get_log($id)
    {
        return $this->db->query('SELECT aksi, data_json, created_by, created_date FROM tr_invoice_ar_log WHERE id_invoice = ? ORDER BY id DESC', [$id])->result_array();
    }

    private function nullify($v)
    {
        $v = is_string($v) ? trim($v) : $v;
        return $v === '' || $v === null ? null : $v;
    }

    /**
     * Simpan data AR satu invoice dalam satu transaksi. Mengembalikan ['ok'=>bool,'errors'=>[..]].
     * Server memakai data tersimpan untuk step terkunci (Piutang_aging::lock_saved), bukan isi form.
     */
    public function save_ar($id, array $input, $user)
    {
        $A = 'Piutang_aging';
        $this->db->trans_begin();
        try {
            $inv = $this->db->query('SELECT id, saldo_piutang, sts_close FROM tr_invoicing WHERE id = ? FOR UPDATE', [$id])->row_array();
            if (!$inv) {
                throw new RuntimeException('Invoice tidak ditemukan.');
            }
            if ($A::is_closed($inv)) {
                throw new RuntimeException('Invoice sudah close dan tidak bisa diubah.');
            }
            $this->db->query('INSERT IGNORE INTO tr_invoice_ar (id_invoice, created_by, created_date) VALUES (?, ?, NOW())', [$id, $user]);
            $this->db->query('SELECT id_invoice FROM tr_invoice_ar WHERE id_invoice = ? FOR UPDATE', [$id]);
            $old = $this->get_row($id);
            if ($A::ar_status($old) === 'plan_bayar') {
                throw new RuntimeException('Plan bayar sudah disimpan; data invoice ini terkunci.');
            }
            $new = [];
            foreach (self::AR_FIELDS as $f) {
                // Kolom yang tidak dikirim (input nonaktif di form) tidak boleh menimpa data tersimpan dengan NULL
                $new[$f] = array_key_exists($f, $input) ? $this->nullify($input[$f]) : ($old[$f] ?? null);
            }
            $new['extra_fu'] = [];
            foreach (($input['extra_fu'] ?? []) as $e) {
                $new['extra_fu'][] = ['ke' => (int) ($e['ke'] ?? 0), 'plan' => $this->nullify($e['plan'] ?? null), 'aktual' => $this->nullify($e['aktual'] ?? null), 'ket' => $this->nullify($e['ket'] ?? null)];
            }
            $merged = $A::lock_saved($new + $old, $old);
            $errors = $A::validate_ar($merged, $old);
            if ($errors) {
                $this->db->trans_rollback();
                return ['ok' => false, 'errors' => $errors];
            }
            $set = [];
            foreach (self::AR_FIELDS as $f) {
                $set[$f] = $this->nullify($merged[$f] ?? null);
            }
            $probe = $merged;
            $oldStatus = $A::ar_status($old);
            $newStatus = $A::ar_status($probe);
            if ($newStatus !== $oldStatus) {
                $set['status_from'] = $oldStatus;
                $set['status_changed_date'] = date('Y-m-d H:i:s');
            }
            if ($newStatus === 'plan_bayar') {
                $set['plan_bayar_nominal'] = $inv['saldo_piutang']; // sisa saat plan bayar pertama disimpan
            }
            $set['updated_by'] = $user;
            $set['updated_date'] = date('Y-m-d H:i:s');
            $this->db->update('tr_invoice_ar', $set, ['id_invoice' => $id]);

            // FU tambahan: hapus yang tidak ada lagi dan belum dilakukan, upsert sisanya
            $keep = array_map(function ($e) { return (int) $e['ke']; }, $merged['extra_fu']);
            $this->db->query('DELETE FROM tr_invoice_ar_fu WHERE id_invoice = ? AND aktual IS NULL' . ($keep ? ' AND ke NOT IN (' . implode(',', $keep) . ')' : ''), [$id]);
            foreach ($merged['extra_fu'] as $e) {
                $this->db->query(
                    'INSERT INTO tr_invoice_ar_fu (id_invoice, ke, plan, aktual, ket, created_by, created_date) VALUES (?, ?, ?, ?, ?, ?, NOW())
                     ON DUPLICATE KEY UPDATE plan = VALUES(plan), aktual = VALUES(aktual), ket = VALUES(ket)',
                    [$id, (int) $e['ke'], $this->nullify($e['plan']), $this->nullify($e['aktual']), $this->nullify($e['ket']), $user]
                );
            }
            $this->db->insert('tr_invoice_ar_log', ['id_invoice' => $id, 'aksi' => 'simpan', 'data_json' => json_encode($set + ['extra_fu' => $merged['extra_fu']]),
                'created_by' => $user, 'created_date' => date('Y-m-d H:i:s')]);
            if ($this->db->trans_status() === false) {
                throw new RuntimeException('Gagal menyimpan data.');
            }
            $this->db->trans_commit();
            return ['ok' => true, 'errors' => [], 'saved' => ['bukti_terima' => $set['bukti_terima']]];
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return ['ok' => false, 'errors' => [$e->getMessage()]];
        }
    }

    /** Close invoice: plan bayar ada dan sisa 0 persis. Dicek ulang di dalam UPDATE (aman dari penerimaan bersamaan). */
    public function close_invoice($id, $reason, $user)
    {
        $A = 'Piutang_aging';
        if (trim((string) $reason) === '') {
            return ['ok' => false, 'errors' => ['Keterangan close wajib diisi.']];
        }
        $this->db->trans_begin();
        try {
            $this->db->query('SELECT id FROM tr_invoicing WHERE id = ? FOR UPDATE', [$id]);
            $row = $this->get_row($id);
            if (!$row) {
                throw new RuntimeException('Invoice tidak ditemukan.');
            }
            $block = $A::close_block_reason($row);
            if ($block !== null) {
                throw new RuntimeException($block);
            }
            $this->db->query(
                "UPDATE tr_invoicing SET sts_close = '1', close_by = ?, close_date = NOW(), close_reason = ?
                 WHERE id = ? AND COALESCE(sts_close, '0') <> '1' AND ROUND(saldo_piutang * 100) = 0",
                [$user, $reason, $id]
            );
            if ($this->db->affected_rows() !== 1) {
                throw new RuntimeException('Close ditolak: invoice sudah close atau belum lunas.');
            }
            $this->db->query('UPDATE tr_invoice_ar SET status_from = ?, status_changed_date = NOW() WHERE id_invoice = ?', [$A::ar_status($row), $id]);
            $this->db->insert('tr_invoice_ar_log', ['id_invoice' => $id, 'aksi' => 'close', 'data_json' => json_encode(['close_reason' => $reason]),
                'created_by' => $user, 'created_date' => date('Y-m-d H:i:s')]);
            $this->db->trans_commit();
            return ['ok' => true, 'errors' => []];
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return ['ok' => false, 'errors' => [$e->getMessage()]];
        }
    }
}
