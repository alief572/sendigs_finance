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

        $draw   = isset($post['draw']) ? $post['draw'] : 0;
        $length = isset($post['length']) ? $post['length'] : 10;
        $start  = isset($post['start']) ? $post['start'] : 0;
        $search = isset($post['search']) ? $post['search'] : ['value' => ''];
        $order  = isset($post['order']) ? $post['order'] : [];

        // Define sortable columns mapping
        $sort_columns = [
            1 => 'a.no_transaksi',
            2 => 'a.jenis_transaksi',
            3 => 'a.tgl_jurnal',
            4 => 'a.nm_company'
        ];

        // Base filter criteria
        $this->db->from('tr_jurnal a');
        $this->db->where('a.sts <>', '1');
        $this->db->where_not_in('a.jenis_transaksi', [
            'Invoicing',
            'Penerimaan Piutang',
            'Expense Report Consultant',
            'Refill Pettycash',
            'Payment',
            'Transport'
        ]);
        $this->db->group_by(['a.no_transaksi', 'a.jenis_transaksi']);

        // Calculate recordsTotal (Total before search)
        $temp_db = clone $this->db;
        $query_total = $temp_db->get();
        $recordsTotal = $query_total->num_rows();

        // Apply Search
        if (!empty($search['value'])) {
            $this->db->group_start();
            $this->db->like('a.no_transaksi', $search['value'], 'both');
            $this->db->or_like('a.jenis_transaksi', $search['value'], 'both');
            $this->db->or_like('a.nm_company', $search['value'], 'both');
            $this->db->group_end();
        }

        // Calculate recordsFiltered (Total after search)
        $temp_db_filtered = clone $this->db;
        $query_filtered = $temp_db_filtered->get();
        $recordsFiltered = $query_filtered->num_rows();

        // Apply Ordering
        if (!empty($order) && isset($sort_columns[$order[0]['column']])) {
            $this->db->order_by($sort_columns[$order[0]['column']], $order[0]['dir']);
        } else {
            $this->db->order_by('a.id', 'desc');
        }

        // Apply Select and Limit
        $this->db->select('a.id, a.no_jurnal, a.tgl_jurnal, a.coa, a.id_company, a.nm_company, a.nm_coa, a.debit, a.kredit, a.keterangan, a.sts, a.no_transaksi, a.jenis_transaksi');
        if ($length != -1) {
            $this->db->limit($length, $start);
        }
        $get_data = $this->db->get()->result();

        $hasil = [];
        $no = $start;
        foreach ($get_data as $item) {
            $no++;
            $hasil[] = [
                'no'              => $no,
                'no_transaksi'    => $item->no_transaksi,
                'jenis_transaksi' => $item->jenis_transaksi,
                'tanggal_jurnal'  => date('d F Y', strtotime($item->tgl_jurnal)),
                'company'         => $item->nm_company,
                'action'          => '<button type="button" class="btn btn-sm btn-primary" onclick="add_jurnal(' . $item->id . ')" title="Posting Jurnal"><i class="fa fa-plus"></i></button>'
            ];
        }

        echo json_encode([
            'draw'            => intval($draw),
            'recordsTotal'    => intval($recordsTotal),
            'recordsFiltered' => intval($recordsFiltered),
            'data'            => $hasil
        ]);
    }
}
