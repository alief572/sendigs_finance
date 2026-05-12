<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Jurnal_penerimaan_model extends BF_Model
{
    protected $viewPermission     = 'Jurnal_Penerimaan.View';
    protected $addPermission      = 'Jurnal_Penerimaan.Add';
    protected $managePermission = 'Jurnal_Penerimaan.Manage';
    protected $deletePermission = 'Jurnal_Penerimaan.Delete';

    protected $accounting;
    protected $accounting_vuca;
    protected $accounting_sustain;
    protected $consultant;

    public function __construct()
    {
        $this->accounting = $this->load->database('accounting', true);
        $this->accounting_vuca = $this->load->database('accounting_vuca', true);
        $this->accounting_sustain = $this->load->database('accounting_sustain', true);
        $this->consultant = $this->load->database('consultant', true);
    }

    /**
     * Get data for DataTables
     * Optimized with correct counting for GROUP BY
     */
    public function get_data_jurnal_invoicing()
    {
        $post    = $this->input->post();
        $draw    = $post['draw'];
        $length  = $post['length'];
        $start   = $post['start'];
        $search  = $post['search']['value'];

        $klien = $post['klien'];
        $no_invoice = $post['no_invoice'];
        $company = $post['company'];

        $filter = [
            'b.id_customer' => $klien,
            'b.no_invoice' => $no_invoice,
            'd.id' => $company
        ];

        // 1. Build base query
        $this->_query_jurnal($filter);

        // 2. Count Total Records (before search)
        $totalData = $this->db->count_all_results('', false);
        // Since we use GROUP_BY, CI's count_all_results can be unreliable. 
        // Using subquery for accurate count of groups
        $sql_total = $this->db->get_compiled_select('', false);
        $totalData = $this->db->query("SELECT COUNT(*) AS num FROM ($sql_total) AS temp")->row()->num;

        // 3. Apply Search
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.tgl_jurnal', $search);
            $this->db->or_like('b.nm_customer', $search);
            $this->db->or_like('b.nm_project', $search);
            $this->db->or_like('b.no_invoice', $search);
            $this->db->or_like('d.nm_company', $search);
            $this->db->or_like('e.name', $search);
            $this->db->or_like('a.coa', $search);
            $this->db->or_like('a.nm_coa', $search);
            $this->db->or_like('b.id_spk_penawaran', $search);
            $this->db->or_like('a.debit', $search);
            $this->db->or_like('a.kredit', $search);
            $this->db->group_end();
        }

        // 4. Count Filtered Records
        $sql_filtered = $this->db->get_compiled_select('', FALSE);
        $totalFiltered = $this->db->query("SELECT COUNT(*) AS num FROM ($sql_filtered) AS temp")->row()->num;

        // 5. Order and Limit
        $this->db->order_by('a.created_date', 'desc');
        if ($length != -1) {
            $this->db->limit($length, $start);
        }

        $get_data = $this->db->get()->result();

        // 6. Format Data
        $data = [];
        $no   = $start;
        foreach ($get_data as $row) {
            $no++;

            $data[] = [
                'no'                 => $no,
                'tgl'                => date('d F Y', strtotime($row->tgl_jurnal)),
                'klien'              => $row->nm_customer,
                'no_invoice'         => $row->no_invoice,
                'keterangan_tagihan' => $row->nm_project . ' - <span style="font-weight: bold;">' . $row->id_spk_penawaran . '</span>',
                'company'            => $row->nm_company,
                'nm_divisi'          => $row->nm_divisi,
                'coa'                => $row->coa,
                'perkiraan'          => $row->nm_coa,
                'uraian'             => $row->no_invoice,
                'original'           => number_format($row->total_debit),
                'action'             => '<button type="button" class="btn btn-sm btn-primary posting_jurnal" title="Posting Jurnal" data-id="' . $row->id . '"><i class="fa fa-arrow-up"></i></button>'
            ];
        }

        echo json_encode([
            'draw'            => intval($draw),
            'recordsTotal'    => intval($totalData),
            'recordsFiltered' => intval($totalFiltered),
            'data'            => $data
        ]);
    }

    private function _query_jurnal($filter = null)
    {
        $this->db->select('a.id, a.tgl_jurnal, a.no_transaksi, a.coa, a.nm_coa, a.debit, a.kredit, SUM(a.debit) as total_debit, b.nm_customer, COALESCE(b.nm_project, f.nm_project) as nm_project, b.no_invoice, b.id_spk_penawaran, d.id as id_company, d.nm_company, e.name as nm_divisi', FALSE)
            ->from('tr_jurnal a')
            ->join('tr_invoicing b', 'b.id = a.no_transaksi', 'left')
            ->join(DBCNL . '.kons_tr_penawaran c', 'c.id_quotation = b.id_penawaran', 'left')
            ->join(DBCNL . '.kons_tr_spk_penawaran f', 'f.id_spk_penawaran = b.id_spk_penawaran', 'left')
            ->join(DBCNL . '.kons_tr_company d', 'd.id = COALESCE(c.company, f.id_company)', 'left', FALSE)
            ->join('hris_divisions e', 'e.id = COALESCE(c.id_divisi, f.id_divisi)', 'left', FALSE)
            ->where('a.jenis_transaksi', 'Penerimaan Piutang')
            ->where('a.sts <>', '1')
            ->where('b.no_invoice IS NOT NULL');

        if (!empty($filter)) {
            foreach ($filter as $key => $value) {
                if ($value !== '') {
                    $this->db->where($key, $value);
                }
            }
        }

        $this->db->group_start()
            ->where('a.debit >', 0)
            ->or_where('a.kredit >', 0)
            ->group_end();
        $this->db->group_by('a.no_transaksi');
    }

    public function update_sts_revisi_jurnal()
    {
        $post = $this->input->post();

        $this->db->trans_begin();

        $valid = 1;
        $msg = '';

        $get_jurnal = $this->db->get_where('tr_jurnal', ['id' => $post['id']])->row();

        $update_sts = $this->db->update('tr_jurnal', ['sts' => '9', 'alasan_revisi' => $post['alasan_revisi']], ['no_transaksi' => $get_jurnal->no_transaksi, 'jenis_transaksi' => $get_jurnal->jenis_transaksi]);
        if (!$update_sts) {
            $this->db->trans_rollback();

            $valid = 0;
            $msg = $this->db->error()['message'];
        }

        if ($this->db->trans_status() === false || $valid == 0) {
            if ($valid !== 0) {
                $this->db->trans_rollback();

                $valid = 0;
                $msg = 'Please try again later !';
            }
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $msg = 'Status data berhasil di update !';
        }

        $response = [
            'status' => $valid,
            'msg' => $msg
        ];

        echo json_encode($response);
    }

    public function get_cust_jurnal()
    {
        $get_cust_jurnal = $this->consultant->select('a.id_customer, a.nm_customer')
            ->from('customer a')
            ->where('a.deleted', 'N')
            ->where('a.sts_aktif', 'Y')
            ->get()
            ->result_array();

        return $get_cust_jurnal;
    }

    public function get_no_invoice_jurnal()
    {
        $get_no_invoice_jurnal = $this->db->select('a.no_invoice, a.created_date')
            ->from('tr_invoicing a')
            ->join('tr_jurnal b', 'b.no_transaksi = a.id')
            ->where('b.sts <>', '1')
            ->where('b.jenis_transaksi', 'Penerimaan Piutang')
            ->group_by('a.no_invoice')
            ->order_by('a.created_date', 'desc')
            ->get()
            ->result_array();

        return $get_no_invoice_jurnal;
    }

    public function get_company_jurnal()
    {
        $get_company_jurnal = $this->consultant->select('a.id as id_company, a.nm_company')
            ->from('kons_tr_company a')
            ->get()
            ->result_array();

        return $get_company_jurnal;
    }
}
