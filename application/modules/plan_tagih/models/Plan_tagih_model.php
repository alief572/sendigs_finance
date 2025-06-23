<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Plan_tagih_model extends BF_Model
{
    protected $viewPermission     = 'Plan_Tagih.View';
    protected $addPermission      = 'Plan_Tagih.Add';
    protected $managePermission = 'Plan_Tagih.Manage';
    protected $deletePermission = 'Plan_Tagih.Delete';

    public function generate_id()
    {
        $Ym             = date('ym');
        $srcMtr            = "SELECT MAX(id) as maxP FROM kons_tr_plan_tagih_header WHERE id LIKE '%/" . date('y') . "%' ";
        $resultMtr        = $this->db->query($srcMtr)->result_array();
        $angkaUrut2        = $resultMtr[0]['maxP'];
        $urutan2        = (int)substr($angkaUrut2, 0, 3);
        $urutan2++;
        $urut2            = sprintf('%03s', $urutan2);
        $kode_trans        = $urut2 . '/PLN-TGH/' . int_to_roman(date('m')) . '/' . date('y');

        return $kode_trans;
    }

    public function get_data_spk()
    {
        $draw = $this->input->post('draw');
        $length = $this->input->post('length');
        $start = $this->input->post('start');
        $search = $this->input->post('search');

        $this->db->select('a.*');
        $this->db->from('kons_tr_spk_penawaran a');
        $this->db->where('a.sts_spk', 1);
        if (!empty($search['value'])) {
            $this->db->group_start();
            $this->db->like('a.id_spk_penawaran', $search['value'], 'both');
            $this->db->or_like('a.nm_customer', $search['value'], 'both');
            $this->db->or_like('a.nm_project', $search['value'], 'both');
            $this->db->or_like('a.nm_project_leader', $search['value'], 'both');
            $this->db->or_like('a.nm_sales', $search['value'], 'both');
            $this->db->end_start();
        }
        $this->db->order_by('a.id_spk_penawaran', 'desc');
        $this->db->limit($length, $start);

        $get_data = $this->db->get();

        $this->db->select('a.*');
        $this->db->from('kons_tr_spk_penawaran a');
        $this->db->where('a.sts_spk', 1);
        if (!empty($search['value'])) {
            $this->db->group_start();
            $this->db->like('a.id_spk_penawaran', $search['value'], 'both');
            $this->db->or_like('a.nm_customer', $search['value'], 'both');
            $this->db->or_like('a.nm_project', $search['value'], 'both');
            $this->db->or_like('a.nm_project_leader', $search['value'], 'both');
            $this->db->or_like('a.nm_sales', $search['value'], 'both');
            $this->db->end_start();
        }
        $this->db->order_by('a.id_spk_penawaran', 'desc');

        $get_data_all = $this->db->get();

        $no = (0 + $start);
        $hasil = [];
        foreach ($get_data->result() as $item) {
            $no++;

            $status = '<button class="btn btn-sm btn-warning">Draft</button>';

            $check_plan_tagih = $this->db->get_where('kons_tr_plan_tagih_header', array('id_spk_penawaran' => $item->id_spk_penawaran))->result();
            if (count($check_plan_tagih) > 0) {
                $status = '<button type="button" class="btn btn-sm btn-success">Plan Tagih Created</button>';
            }

            $option = '';
            if (has_permission($this->viewPermission)) {


                $check_plan_tagih = $this->db->get_where('kons_tr_plan_tagih_header', array('id_spk_penawaran' => $item->id_spk_penawaran))->result();
                if (count($check_plan_tagih) < 1) {
                    $option .= '<a href="' . base_url('plan_tagih/add_plan_tagih/' . urlencode(str_replace('/', '|', $item->id_spk_penawaran))) . '" class="btn btn-sm btn-warning" title="Add Plan Tagih"><i class="fa fa-pencil"></i></a>';
                } else {
                    $option .= '<a href="' . base_url('plan_tagih/view_plan_tagih/' . urlencode(str_replace('/', '|', $item->id_spk_penawaran))) . '" class="btn btn-sm btn-info" title="View Plan Tagih"><i class="fa fa-eye"></i></a>';

                    $option .= '<a href="' . base_url('plan_tagih/edit_plan_tagih/' . urlencode(str_replace('/', '|', $item->id_spk_penawaran))) . '" class="btn btn-sm btn-success" title="Revisi Plan Tagih"><i class="fa fa-pencil"></i></a>';
                }
            }

            $hasil[] = [
                'no' => $no,
                'company' => '',
                'no_spk' => $item->id_spk_penawaran,
                'customer' => $item->nm_customer,
                'project' => $item->nm_project,
                'project_leader' => $item->nm_project_leader,
                'sales' => $item->nm_sales,
                'status' => $status,
                'option' => $option
            ];
        }

        echo json_encode([
            'draw' => intval($draw),
            'recordsTotal' => $get_data_all->num_rows(),
            'recordsFiltered' => $get_data_all->num_rows(),
            'data' => $hasil
        ]);
    }
}
