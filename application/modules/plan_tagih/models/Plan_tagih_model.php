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

    protected $consultant;

    public function __construct()
    {
        $this->consultant = $this->load->database('consultant', true);
    }

    public function generate_id($no = null)
    {
        $Ym             = date('ym');
        $srcMtr            = "SELECT id as maxP FROM kons_tr_plan_tagih_header WHERE id LIKE '%/" . date('y') . "%' ORDER BY created_date DESC LIMIT 1";
        $resultMtr        = $this->db->query($srcMtr)->result_array();
        $angkaUrut2        = $resultMtr[0]['maxP'];
        if (!empty($no)) {
            $urutan2        = (int)substr($angkaUrut2, 0, 4);
            $urutan2 = ($urutan2 + $no);
        } else {
            $urutan2        = (int)substr($angkaUrut2, 0, 4);
            $urutan2++;
        }
        $urut2            = sprintf('%04s', $urutan2);
        $kode_trans        = $urut2 . '/PLN-TGH/' . int_to_roman(date('m')) . '/' . date('y');

        return $kode_trans;
    }

    public function get_all_plan_tagih_detail()
    {
        $this->db->select('a.id, a.id_header, a.id_spk_penawaran, a.id_penawaran, a.id_top, a.term_payment, a.persen_payment, a.nominal_payment, a.desc_payment, a.tgl_plan_tagih, a.urutan');
        $this->db->from('kons_tr_plan_tagih_detail a');
        $this->db->where('a.tgl_aktual_plan_tagih IS NULL');
        $get_data = $this->db->get()->result();

        return $get_data;
    }

    public function get_data_last_aktual($id_detail_plan_tagih)
    {
        $this->db->select('a.*');
        $this->db->from('kons_tr_actual_plan_tagih a');
        $this->db->where('a.id_detail_plan_tagih', $id_detail_plan_tagih);
        $this->db->where('a.tanggal_actual_plan_tagih IS NOT NULL');
        $this->db->order_by('a.created_date', 'desc');
        $this->db->limit(1);
        $get_data = $this->db->get()->row();

        return $get_data;
    }

    public function get_invoicing($id_detail_plan_tagih = null)
    {
        $this->db->select('a.*');
        $this->db->from('tr_invoicing a');
        if ($id_detail_plan_tagih !== null) {
            $this->db->where('a.id_detail_plan_tagih', $id_detail_plan_tagih);
            $get_data = $this->db->get()->row();
        }

        return $get_data;
    }

    public function get_data_spk()
    {
        $draw = $this->input->post('draw');
        $length = $this->input->post('length');
        $start = $this->input->post('start');
        $search = $this->input->post('search');

        $this->db->select('a.*, b.nm_company');
        $this->db->from(DBCNL . '.kons_tr_spk_penawaran a');
        $this->db->join(DBCNL . '.kons_tr_penawaran b', 'b.id_quotation = a.id_penawaran', 'left');
        $this->db->join('kons_tr_plan_tagih_header c', 'c.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->where('a.sts_spk', 1);
        $this->db->where('a.deleted_by IS NULL');

        $db_clone = clone $this->db;
        $count_all = $db_clone->count_all_results();

        if (!empty($search['value'])) {
            $this->db->group_start();
            $this->db->like('a.id_spk_penawaran', $search['value'], 'both');
            $this->db->or_like('b.nm_company', $search['value'], 'both');
            $this->db->or_like('a.nm_customer', $search['value'], 'both');
            $this->db->or_like('a.nm_project', $search['value'], 'both');
            $this->db->or_like('a.nm_project_leader', $search['value'], 'both');
            $this->db->or_like('a.nm_sales', $search['value'], 'both');
            $this->db->group_end();
        }

        $db_clone = clone $this->db;
        $count_filter = $db_clone->count_all_results();

        $this->db->order_by('c.id_spk_penawaran', 'asc');
        $this->db->order_by('a.input_date', 'desc');
        $this->db->limit($length, $start);

        $get_data = $this->db->get()->result();

        $no = (0 + $start);
        $hasil = [];
        foreach ($get_data as $item) {
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

                    // $option .= '<a href="' . base_url('plan_tagih/edit_plan_tagih/' . urlencode(str_replace('/', '|', $item->id_spk_penawaran))) . '" class="btn btn-sm btn-success" title="Revisi Plan Tagih"><i class="fa fa-pencil"></i></a>';
                }
            }

            $hasil[] = [
                'no' => $no,
                'company' => $item->nm_company,
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
            'recordsTotal' => $count_all,
            'recordsFiltered' => $count_filter,
            'data' => $hasil
        ]);
    }
}
