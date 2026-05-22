<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Report Piutang Per Invoice Model
 *
 * Model untuk mengambil data piutang per invoice dari multiple database.
 * Menggunakan cross-database query antara db_sendigs_ss (DBERP),
 * db_consultant_new (DBCNL), dan finance (default).
 */
class Report_piutang_per_invoice_model extends BF_Model
{
    /**
     * Mapping company codes ke tab perusahaan.
     * STM: company id 1, 6, 7
     * VUCA: company id 4
     * SUSTAIN: company id 3
     */
    const COMPANY_TAB_MAP = [
        'STM'     => [1, 6, 7],
        'VUCA'    => [4],
        'SUSTAIN' => [3],
    ];

    /**
     * Mengambil data report piutang per invoice berdasarkan company codes dan filter tanggal.
     *
     * Query melakukan JOIN antar 3 database:
     * - db_sendigs_ss (DBERP): kons_tr_plan_tagih_header, kons_tr_plan_tagih_detail
     * - db_consultant_new (DBCNL): kons_tr_spk_penawaran, kons_tr_penawaran, kons_tr_company
     * - finance (default): tr_invoicing, tr_penerimaan_piutang_detail
     *
     * @param array  $company_codes Array of company IDs to filter (e.g. [1, 6, 7] for STM)
     * @param string $filter_date   Date in Y-m-d format to filter invoices and payments
     * @return array Raw query results
     */
    public function get_report_data($company_codes, $filter_date)
    {
        // Sanitize company_codes to ensure they are integers
        $company_codes = array_map('intval', $company_codes);

        // Build placeholders for IN clause
        $placeholders = implode(',', array_fill(0, count($company_codes), '?'));

        $sql = "SELECT
                    pth.nm_customer,
                    pth.id_spk_penawaran,
                    pth.nilai_bersih_project AS nominal_project,
                    ptd.id AS id_detail_plan_tagih,
                    ptd.urutan AS top_number,
                    ptd.nominal_payment AS rincian_top,
                    inv.id AS id_invoice,
                    inv.tanggal_invoice,
                    inv.no_invoice,
                    inv.total_nominal AS nilai_invoice,
                    ppd.id AS id_payment,
                    DATE(pph.created_date) AS tanggal_bayar,
                    ppd.penerimaan AS nilai_bayar,
                    comp.id AS company_code
                FROM " . DBERP . ".kons_tr_plan_tagih_header pth
                JOIN " . DBERP . ".kons_tr_plan_tagih_detail ptd
                    ON ptd.id_header = pth.id
                JOIN " . DBCNL . ".kons_tr_spk_penawaran spk
                    ON spk.id_spk_penawaran = pth.id_spk_penawaran
                JOIN " . DBCNL . ".kons_tr_penawaran pen
                    ON pen.id_quotation = spk.id_penawaran
                JOIN " . DBCNL . ".kons_tr_company comp
                    ON comp.id = pen.company
                LEFT JOIN tr_invoicing inv
                    ON inv.id_detail_plan_tagih = ptd.id
                    AND inv.tanggal_invoice <= ?
                LEFT JOIN tr_penerimaan_piutang_detail ppd
                    ON ppd.id_inv = inv.id
                LEFT JOIN tr_penerimaan_piutang pph
                    ON pph.no_surat = ppd.id_header
                    AND DATE(pph.created_date) <= ?
                WHERE comp.id IN ({$placeholders})
                ORDER BY pth.nm_customer ASC, pth.id_spk_penawaran ASC,
                         ptd.urutan ASC, inv.tanggal_invoice ASC, pph.created_date ASC";

        // Build bindings array: filter_date (for inv), filter_date (for ppd), then company_codes
        $bindings = array_merge(
            [$filter_date, $filter_date],
            $company_codes
        );

        $query = $this->db->query($sql, $bindings);

        return $query->result_array();
    }
}
