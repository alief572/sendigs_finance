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

        // Sum Refill
        $this->db->select('COALESCE(SUM(grand_total), 0) as total_refill');
        $this->db->from('tr_pelaporan_petty_cash');
        $this->db->where('status', 'approved');
        $this->db->where('DATE(approved_on) <', $start_date);
        $this->db->where("EXISTS (SELECT 1 FROM tr_jurnal j WHERE j.no_transaksi = tr_pelaporan_petty_cash.no_pelaporan AND j.sts = '1')");
        $refill = $this->db->get()->row()->total_refill;

        // Sum Expense (menggunakan detail agar akurat dengan data report)
        $this->db->select('COALESCE(SUM(d.total), 0) as total_expense');
        $this->db->from('tr_expense_petty_cash h');
        $this->db->join('tr_expense_petty_cash_detail d', 'd.pencatatan_id = h.id');
        $this->db->where('h.status', 'approved');
        $this->db->where('h.tanggal <', $start_date);
        $this->db->where("
            (
                (h.company = 'STM' AND EXISTS (
                    SELECT 1 FROM tr_pelaporan_petty_cash_detail pd 
                    JOIN tr_pelaporan_petty_cash p ON p.id = pd.pelaporan_id
                    JOIN tr_jurnal j ON j.no_transaksi = p.no_pelaporan
                    WHERE pd.pencatatan_id = h.id AND j.sts = '1'
                ))
                OR
                (h.company != 'STM' AND EXISTS (
                    SELECT 1 FROM tr_pelaporan_petty_cash_detail pd 
                    JOIN tr_pelaporan_petty_cash p ON p.id = pd.pelaporan_id
                    JOIN tr_petty_cash_vuca_sustain vs ON vs.no_pelaporan = p.no_pelaporan
                    JOIN tr_jurnal j ON j.no_transaksi = vs.no_payment_hutang
                    WHERE pd.pencatatan_id = h.id AND j.sts = '1'
                ))
            )
        ");
        $expense = $this->db->get()->row()->total_expense;

        return $refill - $expense;
    }

    public function get_report_data($start_date = null, $end_date = null)
    {
        $where_expense = "h.status = 'approved'";
        $where_refill = "status = 'approved'";

        if (!empty($start_date) && !empty($end_date)) {
            $where_expense .= " AND h.tanggal >= '{$start_date}' AND h.tanggal <= '{$end_date}'";
            $where_refill .= " AND DATE(approved_on) >= '{$start_date}' AND DATE(approved_on) <= '{$end_date}'";
        } elseif (!empty($start_date)) {
            $where_expense .= " AND h.tanggal >= '{$start_date}'";
            $where_refill .= " AND DATE(approved_on) >= '{$start_date}'";
        } elseif (!empty($end_date)) {
            $where_expense .= " AND h.tanggal <= '{$end_date}'";
            $where_refill .= " AND DATE(approved_on) <= '{$end_date}'";
        }

        $sql = "
            SELECT 
                h.no_pencatatan AS no_transaksi,
                h.tanggal AS tanggal,
                d.coa_code AS coa,
                h.company AS company,
                d.pengeluaran AS pengeluaran,
                'Transaksi' AS jenis_jurnal,
                0 AS debit,
                d.total AS kredit,
                h.keterangan AS keterangan,
                h.created_on AS sort_date
            FROM tr_expense_petty_cash h
            JOIN tr_expense_petty_cash_detail d ON d.pencatatan_id = h.id
            WHERE {$where_expense}
            AND (
                (h.company = 'STM' AND EXISTS (
                    SELECT 1 FROM tr_pelaporan_petty_cash_detail pd 
                    JOIN tr_pelaporan_petty_cash p ON p.id = pd.pelaporan_id
                    JOIN tr_jurnal j ON j.no_transaksi = p.no_pelaporan
                    WHERE pd.pencatatan_id = h.id AND j.sts = '1'
                ))
                OR
                (h.company != 'STM' AND EXISTS (
                    SELECT 1 FROM tr_pelaporan_petty_cash_detail pd 
                    JOIN tr_pelaporan_petty_cash p ON p.id = pd.pelaporan_id
                    JOIN tr_petty_cash_vuca_sustain vs ON vs.no_pelaporan = p.no_pelaporan
                    JOIN tr_jurnal j ON j.no_transaksi = vs.no_payment_hutang
                    WHERE pd.pencatatan_id = h.id AND j.sts = '1'
                ))
            )

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
            AND EXISTS (SELECT 1 FROM tr_jurnal j WHERE j.no_transaksi = tr_pelaporan_petty_cash.no_pelaporan AND j.sts = '1')

            ORDER BY tanggal ASC, sort_date ASC
        ";

        return $this->db->query($sql)->result();
    }
}
