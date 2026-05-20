<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Report_piutang_model extends BF_Model
{
    /**
     * Get report piutang data grouped by SPK → Invoice → Payment hierarchy.
     *
     * @param string|null $tgl_dari  Start date (yyyy-mm-dd format) or null
     * @param string|null $tgl_sampai End date (yyyy-mm-dd format) or null
     * @return array Grouped data structure
     */
    public function get_report_piutang($tgl_dari = null, $tgl_sampai = null)
    {
        $this->db->select('*');
        $this->db->from('view_report_piutang');

        // Apply date filter only when both dates are provided
        if (!empty($tgl_dari) && !empty($tgl_sampai)) {
            $this->db->where('tanggal_invoice >=', $tgl_dari);
            $this->db->where('tanggal_invoice <=', $tgl_sampai);
        }

        $this->db->order_by('no_spk', 'ASC');
        $this->db->order_by('tanggal_invoice', 'ASC');
        $this->db->order_by('tanggal_penerimaan', 'ASC');

        $results = $this->db->get()->result_array();

        // Group raw results by SPK → Invoice → Payment hierarchy
        return $this->_group_data($results);
    }

    /**
     * Get total piutang (sum of distinct saldo_piutang per invoice).
     *
     * @param string|null $tgl_dari  Start date (yyyy-mm-dd format) or null
     * @param string|null $tgl_sampai End date (yyyy-mm-dd format) or null
     * @return float Total piutang value
     */
    public function get_total_piutang($tgl_dari = null, $tgl_sampai = null)
    {
        // Build subquery to get distinct saldo per invoice, then sum all
        $subquery = "SELECT id_invoice, MAX(saldo_piutang) as saldo FROM view_report_piutang WHERE id_invoice IS NOT NULL";

        if (!empty($tgl_dari) && !empty($tgl_sampai)) {
            $subquery .= " AND tanggal_invoice >= " . $this->db->escape($tgl_dari);
            $subquery .= " AND tanggal_invoice <= " . $this->db->escape($tgl_sampai);
        }

        $subquery .= " GROUP BY id_invoice";

        $query = $this->db->query("SELECT COALESCE(SUM(saldo), 0) as total_piutang FROM ({$subquery}) as invoice_saldo");

        $row = $query->row();

        return (float) ($row ? $row->total_piutang : 0);
    }

    /**
     * Group raw VIEW results into SPK → Invoice → Payment hierarchy.
     *
     * @param array $results Raw query results
     * @return array Grouped hierarchical data
     */
    private function _group_data($results)
    {
        $grouped = [];
        $spk_index = [];

        foreach ($results as $row) {
            $spk_id = $row['id_spk_penawaran'];

            // Initialize SPK group if not exists
            if (!isset($spk_index[$spk_id])) {
                $spk_index[$spk_id] = count($grouped);
                $grouped[] = [
                    'id_spk_penawaran' => $row['id_spk_penawaran'],
                    'no_spk'           => $row['no_spk'],
                    'nm_customer'      => $row['nm_customer'],
                    'nilai_kontrak'    => $row['nilai_kontrak'],
                    'invoices'         => []
                ];
            }

            $spk_idx = $spk_index[$spk_id];

            // Skip if no invoice (SPK without invoices)
            if (empty($row['id_invoice'])) {
                continue;
            }

            // Find or create invoice entry within this SPK
            $invoice_found = false;
            $invoice_idx = null;

            foreach ($grouped[$spk_idx]['invoices'] as $idx => $invoice) {
                if ($invoice['id_invoice'] == $row['id_invoice']) {
                    $invoice_found = true;
                    $invoice_idx = $idx;
                    break;
                }
            }

            if (!$invoice_found) {
                $invoice_idx = count($grouped[$spk_idx]['invoices']);
                $grouped[$spk_idx]['invoices'][] = [
                    'id_invoice'       => $row['id_invoice'],
                    'no_invoice'       => $row['no_invoice'],
                    'tanggal_invoice'  => $row['tanggal_invoice'],
                    'nilai_invoice'    => $row['nilai_invoice'],
                    'saldo_piutang'    => $row['saldo_piutang'],
                    'payments'         => []
                ];
            }

            // Add payment if exists
            if (!empty($row['id_penerimaan_detail'])) {
                $grouped[$spk_idx]['invoices'][$invoice_idx]['payments'][] = [
                    'no_penerimaan'       => $row['no_penerimaan'],
                    'tanggal_penerimaan'  => $row['tanggal_penerimaan'],
                    'nilai_penerimaan'    => $row['nilai_penerimaan']
                ];
            }
        }

        return $grouped;
    }
}
