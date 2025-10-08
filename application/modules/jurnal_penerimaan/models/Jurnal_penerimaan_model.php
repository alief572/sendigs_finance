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

    public function __construct()
    {
        $this->accounting = $this->load->database('accounting', true);
        $this->accounting_vuca = $this->load->database('accounting_vuca', true);
        $this->accounting_sustain = $this->load->database('accounting_sustain', true);
    }

    public function get_data_jurnal_invoicing()
    {
        $post = $this->input->post();

        $draw = $post['draw'];
        $length = $post['length'];
        $start = $post['start'];
        $search = $post['search'];

        $this->db->select('a.id, a.tgl_jurnal, a.no_transaksi, a.coa, a.nm_coa, a.debit, a.kredit, b.nm_customer, b.nm_project, b.no_invoice, b.id_spk_penawaran, d.id as id_company, d.nm_company, e.name as nm_divisi');
        $this->db->from('tr_jurnal a');
        $this->db->join('tr_invoicing b', 'b.id = a.no_transaksi');
        $this->db->join(DBCNL . '.kons_tr_penawaran c', 'c.id_quotation = b.id_penawaran');
        $this->db->join(DBCNL . '.kons_tr_company d', 'd.id = c.company');
        $this->db->join(DBHRIS . '.divisions e', 'e.id = c.id_divisi');
        $this->db->where('a.jenis_transaksi', 'Penerimaan Piutang');
        $this->db->where('a.sts <>', '1');
        $this->db->group_start();
        $this->db->where('a.debit >', 0);
        $this->db->or_where('a.kredit >', 0);
        $this->db->group_end();

        $db_clone = clone $this->db;
        $count_all = $db_clone->count_all_results();

        if (!empty($search['value'])) {
            $this->db->group_start();
            $this->db->like('a.tgl_jurnal', $search['value'], 'both');
            $this->db->or_like('b.nm_customer', $search['value'], 'both');
            $this->db->or_like('b.nm_project', $search['value'], 'both');
            $this->db->or_like('b.no_invoice', $search['value'], 'both');
            $this->db->or_like('d.nm_company', $search['value'], 'both');
            $this->db->or_like('e.name', $search['value'], 'both');
            $this->db->or_like('a.coa', $search['value'], 'both');
            $this->db->or_like('a.nm_coa', $search['value'], 'both');
            $this->db->or_like('b.id_spk_penawaran', $search['value'], 'both');
            $this->db->or_like('a.debit', $search['value'], 'both');
            $this->db->or_like('a.kredit', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->group_by('a.id');

        $db_clone = clone $this->db;
        $count_filtered = $db_clone->count_all_results();

        $this->db->limit($length, $start);

        $get_data = $this->db->get()->result();

        $no = (0 + $start);
        $hasil = [];

        foreach ($get_data as $row) {
            $no++;

            $nilai = 0;
            if ($row->debit > 0) {
                $nilai = $row->debit;
            }
            if ($row->kredit > 0) {
                $nilai = $row->kredit;
            }

            $btn_post_jurnal = '<button type="button" class="btn btn-sm btn-primary posting_jurnal" title="Posting Jurnal" data-id="' . $row->id . '"><i class="fa fa-arrow-up"></i></button>';
            $action = $btn_post_jurnal;

            $hasil[] = [
                'no' => $no,
                'tgl' => date('d F Y', strtotime($row->tgl_jurnal)),
                'klien' => $row->nm_customer,
                'no_invoice' => $row->no_invoice,
                'keterangan_tagihan' => $row->nm_project . ' - <span style="font-weight: bold;">' . $row->id_spk_penawaran . '</span>',
                'company' => $row->nm_company,
                'nm_divisi' => $row->nm_divisi,
                'coa' => $row->coa,
                'perkiraan' => $row->nm_coa,
                'uraian' => $row->no_invoice,
                'original' => number_format($nilai),
                'action' => $action
            ];
        }

        $response = [
            'draw' => intval($draw),
            'recordsTotal' => $count_all,
            'recordsFiltered' => $count_filtered,
            'data' => $hasil
        ];

        echo json_encode($response);
    }
}
