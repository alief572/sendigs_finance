<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Jurnal_model extends BF_Model
{
    protected $viewPermission     = 'Jurnal.View';
    protected $addPermission      = 'Jurnal.Add';
    protected $managePermission = 'Jurnal.Manage';
    protected $deletePermission = 'Jurnal.Delete';

    protected $accounting;
    protected $accounting_vuca;
    protected $accounting_sustain;

    public function __construct()
    {
        $this->accounting = $this->load->database('accounting', true);
        $this->accounting_vuca = $this->load->database('accounting_vuca', true);
        $this->accounting_sustain = $this->load->database('accounting_sustain', true);
    }

    public function get_data_jurnal()
    {
        $post = $this->input->post();

        $draw = $post['draw'];
        $length = $post['length'];
        $start = $post['start'];
        $search = $post['search'];

        $this->db->select('a.id, a.no_jurnal, a.tgl_jurnal, a.coa, a.id_company, a.nm_company, a.nm_coa, a.debit, a.kredit, a.keterangan, a.sts, a.no_transaksi, a.jenis_transaksi');
        $this->db->from('tr_jurnal a');
        $this->db->where('a.sts <>', '1');
        $this->db->where('a.id_company <>', '');
        $this->db->where('a.jenis_transaksi <>', 'Invoicing');
        $this->db->where('a.jenis_transaksi <>', 'Penerimaan Piutang');
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.no_transaksi', $search['value'], 'both');
            $this->db->or_like('a.jenis_transaksi', $search['value'], 'both');
            $this->db->or_like('a.nm_company', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->group_by('a.no_transaksi');
        $this->db->group_by('a.jenis_transaksi');

        $db_clone = clone $this->db;
        $count_all = $db_clone->count_all_results();

        $this->db->order_by('a.id', 'desc');
        $this->db->limit($length, $start);

        $get_data = $this->db->get()->result();

        $hasil = [];
        $no = (0 + $start);

        foreach ($get_data as $item) {
            $no++;

            $btn_jurnal = '<button type="button" class="btn btn-sm btn-primary" onclick="add_jurnal(' . $item->id . ')" title="Posting Jurnal"><i class="fa fa-plus"></i></button>';

            $action = $btn_jurnal;

            $hasil[] = [
                'no' => $no,
                'no_transaksi' => $item->no_transaksi,
                'jenis_transaksi' => $item->jenis_transaksi,
                'tanggal_jurnal' => date('d F Y', strtotime($item->tgl_jurnal)),
                'company' => $item->nm_company,
                'action' => $btn_jurnal
            ];
        }

        $response = [
            'draw' => intval($draw),
            'recordsTotal' => $count_all,
            'recordsFiltered' => $count_all,
            'data' => $hasil
        ];

        echo json_encode($response);
    }
}
