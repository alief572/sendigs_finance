<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Jurnal_payment_model extends BF_Model
{
    protected $viewPermission     = 'Jurnal_Payment.View';
    protected $addPermission      = 'Jurnal_Payment.Add';
    protected $managePermission = 'Jurnal_Payment.Manage';
    protected $deletePermission = 'Jurnal_Payment.Delete';

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

    public function get_data_jurnal()
    {
        $post = $this->input->post();

        $draw   = isset($post['draw']) ? $post['draw'] : 0;
        $length = isset($post['length']) ? $post['length'] : 10;
        $start  = isset($post['start']) ? $post['start'] : 0;
        $search = isset($post['search']) ? $post['search'] : ['value' => ''];
        $order  = isset($post['order']) ? $post['order'] : [];

        $tgl_jurnal = isset($post['tgl_jurnal']) ? $post['tgl_jurnal'] : '';
        $tgl_from = '';
        $tgl_to = '';
        if (!empty($tgl_jurnal)) {
            $exp_tgl_jurnal = explode(' to ', $tgl_jurnal);
            $tgl_from = $exp_tgl_jurnal[0];
            $tgl_to = $exp_tgl_jurnal[1];
        }

        $no_transaksi = isset($post['no_transaksi']) ? $post['no_transaksi'] : '';
        $company = isset($post['company']) ? $post['company'] : '';

        $filter = [
            'tgl_from' => $tgl_from,
            'tgl_to' => $tgl_to,
            'no_transaksi' => $no_transaksi,
            'company' => $company
        ];

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
        $this->db->where('a.jenis_transaksi', 'Payment');
        $this->db->where('a.nm_company <>', '');

        if (!empty($filter['tgl_from']) && !empty($filter['tgl_to'])) {
            $this->db->where('a.tgl_jurnal >=', $filter['tgl_from']);
            $this->db->where('a.tgl_jurnal <=', $filter['tgl_to']);
        }

        if (!empty($filter['no_transaksi'])) {
            $this->db->where('a.no_transaksi', $filter['no_transaksi']);
        }

        if (!empty($filter['company'])) {
            $this->db->where('a.id_company', $filter['company']);
        }

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

    public function get_list_jurnal($filter = null)
    {
        $this->db->select('a.*');
        $this->db->from('tr_jurnal a');
        $this->db->where('a.sts <>', '1');
        $this->db->where('a.jenis_transaksi', 'Payment');
        $this->db->where('a.nm_company <>', '');

        if (!empty($filter['tgl_from']) && !empty($filter['tgl_to'])) {
            $this->db->where('a.tgl_jurnal >=', $filter['tgl_from']);
            $this->db->where('a.tgl_jurnal <=', $filter['tgl_to']);
        }

        if (!empty($filter['no_transaksi'])) {
            $this->db->where('a.no_transaksi', $filter['no_transaksi']);
        }

        if (!empty($filter['company'])) {
            $this->db->where('a.id_company', $filter['company']);
        }

        $this->db->group_by(['a.no_transaksi', 'a.jenis_transaksi']);
        $get_data = $this->db->get()->result_array();

        return $get_data;
    }

    public function get_no_payment_jurnal()
    {
        $get_no_payment_jurnal = $this->db->select('a.no_transaksi')
            ->from('tr_jurnal a')
            ->where('a.sts <>', '1')
            ->where('a.jenis_transaksi', 'Payment')
            ->group_by(['a.no_transaksi', 'a.jenis_transaksi'])
            ->order_by('a.created_date', 'desc')
            ->get()
            ->result_array();

        return $get_no_payment_jurnal;
    }

    public function get_company()
    {
        $get_company = $this->consultant->select('a.id as id_company, a.nm_company')
            ->from('kons_tr_company a')
            ->get()
            ->result_array();

        return $get_company;
    }
}
