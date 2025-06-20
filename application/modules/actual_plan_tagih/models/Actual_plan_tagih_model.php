<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Actual_plan_tagih_model extends BF_Model
{
    protected $viewPermission     = 'Actual_Plan_Tagih.View';
    protected $addPermission      = 'Actual_Plan_Tagih.Add';
    protected $managePermission = 'Actual_Plan_Tagih.Manage';
    protected $deletePermission = 'Actual_Plan_Tagih.Delete';

    public function generate_id()
    {
        $Ym             = date('ym');
        $srcMtr            = "SELECT MAX(id) as maxP FROM kons_tr_actual_plan_tagih WHERE id LIKE '%/" . date('y') . "%' ";
        $resultMtr        = $this->db->query($srcMtr)->result_array();
        $angkaUrut2        = $resultMtr[0]['maxP'];
        $urutan2        = (int)substr($angkaUrut2, 0, 5);
        $urutan2++;
        $urut2            = sprintf('%05s', $urutan2);
        $kode_trans        = $urut2 . '/ACT-TGH/' . int_to_roman(date('m')) . '/' . date('y');

        return $kode_trans;
    }

    public function get_actual_plan_tagih()
    {
        $draw = $this->input->post('draw');
        $length = $this->input->post('length');
        $start = $this->input->post('start');
        $search = $this->input->post('search');
        $bulan = $this->input->post('bulan');

        $this->db->select('a.*, b.nm_customer, b.nm_project, b.nm_project_leader, c.nm_sales');
        $this->db->from('kons_tr_plan_tagih_detail a');
        $this->db->join('kons_tr_plan_tagih_header b', 'b.id = a.id_header');
        $this->db->join('kons_tr_spk_penawaran c', 'c.id_spk_penawaran = a.id_spk_penawaran');
        $this->db->join('kons_tr_actual_plan_tagih d', 'd.id_detail_plan_tagih = a.id', 'left');
        if ($bulan == 'macet') {
            $this->db->where('d.id IS NOT NULL');
            $this->db->where('d.tagih_mundur', '3');
        } else {
            $this->db->where('DATE_FORMAT(a.tgl_plan_tagih, "%Y") =', date('Y'));
            $this->db->where('DATE_FORMAT(a.tgl_plan_tagih, "%m") =', sprintf('%02s', $bulan));
            $this->db->where('d.id', null);
        }
        if (!empty($search['value'])) {
            $this->db->group_start();
            $this->db->like('a.id_spk_penawaran', $search['value'], 'both');
            $this->db->or_like('b.nm_customer', $search['value'], 'both');
            $this->db->or_like('b.nm_project', $search['value'], 'both');
            $this->db->or_like('b.nm_project_leader', $search['value'], 'both');
            $this->db->or_like('b.nm_sales', $search['value'], 'both');
            $this->db->end_start();
        }
        $this->db->order_by('a.id', 'desc');
        $this->db->limit($length, $start);

        $get_data = $this->db->get();

        // print_r($this->db->last_query());
        // exit;

        $this->db->select('a.*, b.nm_customer, b.nm_project, b.nm_project_leader, c.nm_sales');
        $this->db->from('kons_tr_plan_tagih_detail a');
        $this->db->join('kons_tr_plan_tagih_header b', 'b.id = a.id_header');
        $this->db->join('kons_tr_spk_penawaran c', 'c.id_spk_penawaran = a.id_spk_penawaran');
        $this->db->join('kons_tr_actual_plan_tagih d', 'd.id_detail_plan_tagih = a.id', 'left');
        if ($bulan == 'macet') {
            $this->db->where('d.id IS NOT NULL');
            $this->db->where('d.tagih_mundur', '3');
        } else {
            $this->db->where('DATE_FORMAT(a.tgl_plan_tagih, "%Y") =', date('Y'));
            $this->db->where('DATE_FORMAT(a.tgl_plan_tagih, "%m") =', sprintf('%02s', $bulan));
            $this->db->where('d.id', null);
        }
        if (!empty($search['value'])) {
            $this->db->group_start();
            $this->db->like('a.id_spk_penawaran', $search['value'], 'both');
            $this->db->or_like('b.nm_customer', $search['value'], 'both');
            $this->db->or_like('b.nm_project', $search['value'], 'both');
            $this->db->or_like('b.nm_project_leader', $search['value'], 'both');
            $this->db->or_like('b.nm_sales', $search['value'], 'both');
            $this->db->end_start();
        }
        $this->db->order_by('a.id', 'desc');

        $get_data_all = $this->db->get();

        $hasil = [];
        $no = (0 + $start);

        foreach ($get_data->result() as $item) {
            $no++;

            $status = '<button type="button" class="btn btn-sm btn-primary">Waiting Actual Plan Tagih</button>';
            if ($bulan == 'macet') {
                $status = '<button type="button" class="btn btn-sm btn-danger">Tagihan Macet</button>';
            }

            if ($bulan == 'macet') {
                $option = '<button type="button" class="btn btn-sm btn-warning aktual_tagihan_macet" title="Penagihan Tagihan Macet" data-id="' . $item->id . '"><i class="fa fa-pencil"></i></button>';
            } else {
                $option = '<button type="button" class="btn btn-sm btn-warning aktual_tagihan" title="Aktual Tagihan" data-id="' . $item->id . '"><i class="fa fa-pencil"></i></button>';
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
