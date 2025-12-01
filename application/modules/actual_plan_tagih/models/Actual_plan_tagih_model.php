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

    protected $consultant;

    public function __construct()
    {
        $this->consultant = $this->load->database('consultant', true);
    }

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
        $tahun = $this->input->post('tahun');
        $status = $this->input->post('status');

        // if (empty($tahun)) {
        //     $tahun = date('Y');
        // }

        $this->db->select('a.*, b.nm_customer, b.nm_project, b.nm_project_leader');
        $this->db->from('kons_tr_plan_tagih_detail a');
        $this->db->join('kons_tr_plan_tagih_header b', 'b.id = a.id_header', 'left');
        $this->db->join('kons_tr_actual_plan_tagih d', 'd.id_detail_plan_tagih = a.id', 'left');
        if ($bulan == 'macet') {
            $this->db->where('d.id IS NOT NULL');
            $this->db->where('d.tagih_mundur', '3');
            if(!empty($status)) {
                if($status == '1' || $status == '2') {
                    $this->db->where('d.tagih_mundur', $status);
                } else if($status == '3') {
                    $this->db->where('d.id', null);
                }
            }
        } else {
            $this->db->where('DATE_FORMAT(a.tgl_plan_tagih, "%Y") =', $tahun);
            $this->db->where('DATE_FORMAT(a.tgl_plan_tagih, "%m") =', sprintf('%02s', $bulan));
            if(!empty($status)) {
                if($status == '1' || $status == '2') {
                    $this->db->where('d.tagih_mundur', $status);
                } else if($status == '3') {
                    $this->db->where('d.id', null);
                }
            } else {
                $this->db->group_start();
                $this->db->where_in('d.tagih_mundur', ['2', '3']);
                $this->db->or_where('d.id', null);
                $this->db->group_end();
            }
        }
        if (!empty($search['value'])) {
            $this->db->group_start();
            $this->db->like('a.id_spk_penawaran', $search['value'], 'both');
            $this->db->or_like('b.nm_customer', $search['value'], 'both');
            $this->db->or_like('b.nm_project', $search['value'], 'both');
            $this->db->or_like('b.nm_project_leader', $search['value'], 'both');
            $this->db->or_like('a.desc_payment', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->order_by('a.id', 'desc');
        $this->db->group_by('a.id');
        $this->db->limit($length, $start);

        $get_data = $this->db->get();

        // print_r($this->db->last_query());
        // exit;

        $this->db->select('a.*, b.nm_customer, b.nm_project, b.nm_project_leader');
        $this->db->from('kons_tr_plan_tagih_detail a');
        $this->db->join('kons_tr_plan_tagih_header b', 'b.id = a.id_header');
        $this->db->join('kons_tr_actual_plan_tagih d', 'd.id_detail_plan_tagih = a.id', 'left');
        if ($bulan == 'macet') {
            $this->db->where('d.id IS NOT NULL');
            $this->db->where('d.tagih_mundur', '3');
            if(!empty($status)) {
                if($status == '1' || $status == '2') {
                    $this->db->where('d.tagih_mundur', $status);
                } else if($status == '3') {
                    $this->db->where('d.id', null);
                }
            }
        } else {
            $this->db->where('DATE_FORMAT(a.tgl_plan_tagih, "%Y") =', $tahun);
            $this->db->where('DATE_FORMAT(a.tgl_plan_tagih, "%m") =', sprintf('%02s', $bulan));
            if(!empty($status)) {
                if($status == '1' || $status == '2') {
                    $this->db->where('d.tagih_mundur', $status);
                } else if($status == '3') {
                    $this->db->where('d.id', null);
                }
            } else {
                $this->db->group_start();
                $this->db->where_in('d.tagih_mundur', ['2', '3']);
                $this->db->or_where('d.id', null);
                $this->db->group_end();
            }
        }
        if (!empty($search['value'])) {
            $this->db->group_start();
            $this->db->like('a.id_spk_penawaran', $search['value'], 'both');
            $this->db->or_like('b.nm_customer', $search['value'], 'both');
            $this->db->or_like('b.nm_project', $search['value'], 'both');
            $this->db->or_like('b.nm_project_leader', $search['value'], 'both');
            $this->db->or_like('a.desc_payment', $search['value'], 'both');
            $this->db->group_end();
            
        }
        $this->db->order_by('a.id', 'desc');
        $this->db->group_by('a.id');

        $get_data_all = $this->db->get();

        $hasil = [];
        $no = (0 + $start);

        foreach ($get_data->result() as $item) {
            $no++;

            $get_spk_penawaran = $this->consultant->get_where('kons_tr_spk_penawaran', ['id_spk_penawaran' => $item->id_spk_penawaran])->row();
            $nm_sales = (!empty($get_spk_penawaran)) ? $get_spk_penawaran->nm_sales : '';

            $nm_company = '';
            if (!empty($get_spk_penawaran)) {
                $get_penawaran = $this->consultant->get_where('kons_tr_penawaran', ['id_quotation' => $item->id_penawaran])->row();
                $get_company = $this->consultant->get_where('kons_tr_company', ['id' => $get_penawaran->company])->row();

                $nm_company = (!empty($get_company)) ? $get_penawaran->nm_company : '';
            }

            $status = '<button type="button" class="btn btn-sm btn-primary">Waiting Actual Plan Tagih</button>';
            if ($bulan == 'macet') {
                $status = '<button type="button" class="btn btn-sm btn-danger">Tagihan Macet</button>';
            }

            $check_aktual_telat = $this->db->get_where('kons_tr_actual_plan_tagih', ['id_detail_plan_tagih' => $item->id, 'tagih_mundur' => 2])->result();
            if (count($check_aktual_telat) > 0) {
                $status = '<button type="button" class="btn btn-sm btn-danger">Mundur</button>';
            }

            $check_aktual_tagih = $this->db->get_where('kons_tr_actual_plan_tagih', ['id_detail_plan_tagih' => $item->id, 'tagih_mundur' => 1])->result();
            if (count($check_aktual_tagih) > 0) {
                $status = '<button type="button" class="btn btn-sm btn-success">Tagih</button>';
            }

            if ($bulan == 'macet') {
                $option = '<button type="button" class="btn btn-sm btn-warning aktual_tagihan_macet" title="Penagihan Tagihan Macet" data-id="' . $item->id . '"><i class="fa fa-pencil"></i></button>';
            } else {
                $option = '<button type="button" class="btn btn-sm btn-warning aktual_tagihan" title="Aktual Tagihan" data-id="' . $item->id . '"><i class="fa fa-pencil"></i></button>';

                $get_actual_plan_tagih = $this->db->get_where('kons_tr_actual_plan_tagih', array('id_detail_plan_tagih' => $item->id, 'tagih_mundur' => 1))->result();
                if (count($get_actual_plan_tagih)) {
                    $option = '';
                }
            }

            $this->consultant->select('b.nm_paket');
            $this->consultant->from('kons_tr_spk_penawaran a');
            $this->consultant->join('kons_master_konsultasi_header b', 'b.id_konsultasi_h = a.id_project', 'left');
            $this->consultant->where('a.id_spk_penawaran', $item->id_spk_penawaran);
            $get_spk = $this->consultant->get()->row();

            $nm_paket = (!empty($get_spk)) ? $get_spk->nm_paket : '';

            $nm_project = ($item->nm_project == '' && $item->nm_project == null) ? $nm_paket : $item->nm_project;

            $hasil[] = [
                'no' => $no,
                'company' => $nm_company,
                'no_spk' => $item->id_spk_penawaran,
                'customer' => $item->nm_customer,
                'project' => $nm_project,
                'project_leader' => $item->nm_project_leader,
                'sales' => $nm_sales,
                'keterangan' => $item->desc_payment,
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
