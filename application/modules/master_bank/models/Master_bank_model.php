<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Harboens
 * @copyright Copyright (c) 2022
 *
 * This is Model for Request Payment
 */

class Master_bank_model extends BF_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function get_data_bank()
    {
        $ENABLE_ADD     = has_permission('Master_Bank.Add');
        $ENABLE_MANAGE  = has_permission('Master_Bank.Manage');
        $ENABLE_VIEW    = has_permission('Master_Bank.View');
        $ENABLE_DELETE  = has_permission('Master_Bank.Delete');

        $draw = $this->input->post('draw');
        $length = $this->input->post('length');
        $start = $this->input->post('start');
        $search = $this->input->post('search');

        $this->db->select('a.id, a.bank, a.rekening, a.nama, b.nama_bank');
        $this->db->from('ms_bank a');
        $this->db->join('list_bank b', 'b.id = a.bank', 'left');
        $this->db->where('a.deleted', '0');
        if (!empty($search['value'])) :
            $this->db->group_start();
            $this->db->like('a.bank', $search['value'], 'both');
            $this->db->or_like('a.rekening', $search['value'], 'both');
            $this->db->or_like('a.nama', $search['value'], 'both');
            $this->db->group_end();
        endif;

        $all_data = $this->db->count_all_results('', false);

        $this->db->limit($length, $start);

        $get_data = $this->db->get()->result_array();

        $hasil = [];

        $no = (0 + $start);
        foreach ($get_data as $item) :
            $no++;

            $delete_btn = '';
            if ($ENABLE_DELETE) {
                $delete_btn = '<button type="button" class="btn btn-sm btn-danger" onclick="delBank(' . $item['id'] . ')" title="Delete Bank"><i class="fa fa-trash"></i></button>';
            }

            $edit_btn = '';
            if ($ENABLE_MANAGE) {
                $edit_btn = '<button type="button" class="btn btn-sm btn-warning" title="Edit Bank" onclick="EditBank(' . $item['id'] . ')"><i class="fa fa-pencil"></i></button>';
            }

            $action = $delete_btn . ' ' . $edit_btn;

            $hasil[] = [
                'no' => $no,
                'bank' => $item['nama_bank'],
                'account_number' => $item['rekening'],
                'account_name' => $item['nama'],
                'action' => $action
            ];
        endforeach;

        $json = [
            'draw' => intval($draw),
            'recordsTotal' => $all_data,
            'recordsFiltered' => $all_data,
            'data' => $hasil
        ];

        echo json_encode($json);
    }
}
