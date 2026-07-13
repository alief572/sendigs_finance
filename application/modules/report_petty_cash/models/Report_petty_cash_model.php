<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Report_petty_cash_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_saldo_awal($start_date)
    {
        if (empty($start_date)) {
            return 0;
        }

        $bln = (int) date('m', strtotime($start_date));
        $thn = (int) date('Y', strtotime($start_date));

        $db_accounting = $this->load->database('accounting_stm', TRUE);
        $db_accounting->select('saldoawal');
        $db_accounting->from('coa');
        $db_accounting->where('no_perkiraan', '1101-01-02');
        $db_accounting->where('bln', $bln);
        $db_accounting->where('thn', $thn);
        $query = $db_accounting->get();

        if ($query->num_rows() > 0) {
            return (float) $query->row()->saldoawal;
        }

        return 0;
    }

    public function get_report_data($start_date = null, $end_date = null)
    {
        $where_expense = "a.jenis_transaksi = 'Petty Cash' AND a.sts = '1' AND a.debit > 0 AND a.nm_company = b.company";
        $where_refill = "status = 'approved'";
        $where_transaksi_bank = "target_accounting = 'accounting_stm' AND bank_tujuan = '1101-01-02'";

        if (!empty($start_date) && !empty($end_date)) {
            $where_expense .= " AND a.tgl_jurnal >= '{$start_date}' AND a.tgl_jurnal <= '{$end_date}'";
            $where_refill .= " AND DATE(approved_on) >= '{$start_date}' AND DATE(approved_on) <= '{$end_date}'";
            $where_transaksi_bank .= " AND tgl_request >= '{$start_date}' AND tgl_request <= '{$end_date}'";
        } elseif (!empty($start_date)) {
            $where_expense .= " AND a.tgl_jurnal >= '{$start_date}'";
            $where_refill .= " AND DATE(approved_on) >= '{$start_date}'";
            $where_transaksi_bank .= " AND tgl_request >= '{$start_date}'";
        } elseif (!empty($end_date)) {
            $where_expense .= " AND a.tgl_jurnal <= '{$end_date}'";
            $where_refill .= " AND DATE(approved_on) <= '{$end_date}'";
            $where_transaksi_bank .= " AND tgl_request <= '{$end_date}'";
        }

        $sql = "
            SELECT 
                a.no_transaksi AS no_transaksi,
                a.tgl_jurnal AS tanggal,
                a.coa AS coa,
                a.nm_company AS company,
                a.nm_coa AS pengeluaran,
                'Transaksi' AS jenis_jurnal,
                0 AS debit,
                a.debit AS kredit,
                a.keterangan AS keterangan,
                a.tgl_jurnal AS sort_date
            FROM tr_jurnal a
            JOIN tr_expense_petty_cash b ON a.no_transaksi = b.no_pencatatan
            WHERE {$where_expense}

            UNION ALL

            SELECT 
                no_pelaporan AS no_transaksi,
                DATE(approved_on) AS tanggal,
                '1101-01-02' AS coa,
                'STM' AS company,
                'Refill Kas Kecil STM' AS pengeluaran,
                'Refill' AS jenis_jurnal,
                grand_total AS debit,
                0 AS kredit,
                'Dari Bank STM' AS keterangan,
                approved_on AS sort_date
            FROM tr_pelaporan_petty_cash
            WHERE {$where_refill}
            AND EXISTS (
                SELECT 1 FROM tr_jurnal j 
                JOIN payment_approve pa ON pa.id = j.no_transaksi
                WHERE pa.no_doc = tr_pelaporan_petty_cash.no_pelaporan AND j.sts = '1'
            )

            UNION ALL

            SELECT 
                kd_mutasi AS no_transaksi,
                tgl_request AS tanggal,
                '1101-01-02' AS coa,
                'STM' AS company,
                keterangan AS pengeluaran,
                'Transaksi Bank' AS jenis_jurnal,
                transaksi AS debit,
                0 AS kredit,
                keterangan AS keterangan,
                tgl_request AS sort_date
            FROM tr_request_mutasi_admin
            WHERE {$where_transaksi_bank}

            ORDER BY tanggal ASC, sort_date ASC
        ";

        return $this->db->query($sql)->result();
    }
}
