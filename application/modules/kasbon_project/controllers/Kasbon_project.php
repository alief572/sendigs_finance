<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * @author Harboens
 * @copyright Copyright (c) 2020
 *
 * This is controller for Pengajuan Rutin
 */

$status = array();
class Kasbon_project extends Admin_Controller
{
    //Permission
    protected $viewPermission     = 'Kasbon_Project.View';
    protected $addPermission      = 'Kasbon_Project.Add';
    protected $managePermission = 'Kasbon_Project.Manage';
    protected $deletePermission = 'Kasbon_Project.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->template->title('Kasbon Project');
        $this->template->page_icon('fa fa-cubes');
        $this->load->library('upload');
        $this->load->model(array('Kasbon_project/Kasbon_project_model'));
        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $this->template->title('Pengajuan');
        $this->template->render('index');
    }

    public function get_data_spk()
    {
        $draw = $this->input->post('draw');
        $start = $this->input->post('start');
        $length = $this->input->post('length');
        $search = $this->input->post('search');

        $this->db->select('a.*, b.nm_sales, c.nm_paket');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting a');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran b', 'b.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->join(DBCNL . '.kons_master_konsultasi_header c', 'c.id_konsultasi_h = a.id_project', 'left');
        $this->db->where('a.sts', 1);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.id_spk_budgeting', $search['value'], 'both');
            $this->db->or_like('a.id_spk_penawaran', $search['value'], 'both');
            $this->db->or_like('a.nm_customer', $search['value'], 'both');
            $this->db->or_like('b.nm_sales', $search['value'], 'both');
            $this->db->or_like('a.nm_project_leader', $search['value'], 'both');
            $this->db->or_like('a.nm_project', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->order_by('a.create_date', 'desc');
        $this->db->limit($length, $start);

        $get_data = $this->db->get();

        $this->db->select('a.*, b.nm_sales');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting a');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran b', 'b.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->where('a.sts', 1);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.id_spk_budgeting', $search['value'], 'both');
            $this->db->or_like('a.id_spk_penawaran', $search['value'], 'both');
            $this->db->or_like('a.nm_customer', $search['value'], 'both');
            $this->db->or_like('b.nm_sales', $search['value'], 'both');
            $this->db->or_like('a.nm_project_leader', $search['value'], 'both');
            $this->db->or_like('a.nm_project', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->order_by('a.create_date', 'desc');

        $get_data_all = $this->db->get();

        $hasil = [];

        $no = (0 + $start);
        foreach ($get_data->result() as $item) {

            $this->db->select('a.id');
            $this->db->from(DBCNL . '.kons_tr_req_kasbon_project a');
            $this->db->where('a.id_spk_budgeting', $item->id_spk_budgeting);
            $this->db->group_start();
            $this->db->where('a.sts', '');
            $this->db->or_where('a.sts', null);
            $this->db->group_end();
            $get_req = $this->db->get();

            $total_budgeting = 0;

            $sql_total_budget = '
                SELECT
                    a.total_final as total_akomodasi, 
                    0 as total_others, 
                    0 as total_subcont,
                    0 as total_lab
                FROM
                    kons_tr_spk_budgeting_akomodasi a
                WHERE
                    a.id_spk_budgeting = "' . $item->id_spk_budgeting . '"

                UNION ALL

                SELECT
                    0 as total_akomodasi, 
                    a.total_final as total_others, 
                    0 as total_subcont,
                    0 as total_lab
                FROM
                    kons_tr_spk_budgeting_others a
                WHERE
                    a.id_spk_budgeting = "' . $item->id_spk_budgeting . '"

                UNION ALL

                SELECT
                    0 as total_akomodasi, 
                    0 as total_others, 
                    (a.mandays_subcont_final * a.mandays_rate_subcont_final) as total_subcont,
                    0 as total_lab
                FROM
                    kons_tr_spk_budgeting_aktifitas a
                WHERE
                    a.id_spk_budgeting = "' . $item->id_spk_budgeting . '"

                UNION ALL

                SELECT
                    0 as total_akomodasi, 
                    0 as total_others, 
                    0 as total_subcont,
                    a.total_final as total_lab
                FROM
                    kons_tr_spk_budgeting_lab a
                WHERE
                    a.id_spk_budgeting = "' . $item->id_spk_budgeting . '"
            ';

            $get_total_budget = $this->db->query($sql_total_budget)->result();
            foreach ($get_total_budget as $item_budget) {
                $total_budgeting += ($item_budget->total_akomodasi + $item_budget->total_others + $item_budget->total_subcont + $item_budget->total_lab);
            }

            $this->db->select('a.budget_tambahan');
            $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_akomodasi_detail a');
            $this->db->join(DBCNL . '.kons_tr_kasbon_req_ovb_akomodasi_header b', 'b.id_request_ovb = a.id_request_ovb');
            $this->db->where('b.id_spk_budgeting', $item->id_spk_budgeting);
            $this->db->where('b.sts', 1);
            $get_ovb_kasbon = $this->db->get()->result();

            foreach ($get_ovb_kasbon as $item_ovb) {
                $total_budgeting += $item_ovb->budget_tambahan;
            }

            $this->db->select('a.budget_tambahan');
            $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_subcont_detail a');
            $this->db->join(DBCNL . '.kons_tr_kasbon_req_ovb_subcont_header b', 'b.id_request_ovb = a.id_request_ovb');
            $this->db->where('b.id_spk_budgeting', $item->id_spk_budgeting);
            $this->db->where('b.sts', 1);
            $get_ovb_kasbon_subcont = $this->db->get()->result();

            foreach ($get_ovb_kasbon_subcont as $item_ovb) {
                $total_budgeting += $item_ovb->budget_tambahan;
            }

            $this->db->select('a.budget_tambahan');
            $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_others_detail a');
            $this->db->join(DBCNL . '.kons_tr_kasbon_req_ovb_others_header b', 'b.id_request_ovb = a.id_request_ovb');
            $this->db->where('b.id_spk_budgeting', $item->id_spk_budgeting);
            $this->db->where('b.sts', 1);
            $get_ovb_kasbon_others = $this->db->get()->result();

            foreach ($get_ovb_kasbon_others as $item_ovb) {
                $total_budgeting += $item_ovb->budget_tambahan;
            }

            $this->db->select('a.budget_tambahan');
            $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_lab_detail a');
            $this->db->join(DBCNL . '.kons_tr_kasbon_req_ovb_lab_header b', 'b.id_request_ovb = a.id_request_ovb');
            $this->db->where('b.id_spk_budgeting', $item->id_spk_budgeting);
            $this->db->where('b.sts', 1);
            $get_ovb_kasbon_lab = $this->db->get()->result();

            foreach ($get_ovb_kasbon_lab as $item_ovb) {
                $total_budgeting += $item_ovb->budget_tambahan;
            }



            $total_kasbon = 0;

            $sql_total_kasbon = '
                SELECT
                    a.total_pengajuan as total_subcont, 
                    0 as total_akomodasi, 
                    0 as total_others,
                    0 as total_lab
                FROM
                    kons_tr_kasbon_project_subcont a
                WHERE
                    a.sts = "1" AND
                    a.id_spk_budgeting = "' . $item->id_spk_budgeting . '"
                
                UNION ALL

                SELECT
                    0 as total_subcont, 
                    a.total_pengajuan as total_akomodasi, 
                    0 as total_others,
                    0 as total_lab
                FROM
                    kons_tr_kasbon_project_akomodasi a
                WHERE
                    a.sts = "1" AND
                    a.id_spk_budgeting = "' . $item->id_spk_budgeting . '"

                UNION ALL

                SELECT
                    0 as total_subcont, 
                    0 as total_akomodasi, 
                    a.total_pengajuan as total_others,
                    0 as total_lab
                FROM
                    kons_tr_kasbon_project_others a
                WHERE
                    a.sts = "1" AND
                    a.id_spk_budgeting = "' . $item->id_spk_budgeting . '"

                UNION ALL

                SELECT
                    0 as total_subcont, 
                    0 as total_akomodasi, 
                    0 as total_others,
                    a.total_pengajuan as total_lab
                FROM
                    kons_tr_kasbon_project_lab a
                WHERE
                    a.sts = "1" AND
                    a.id_spk_budgeting = "' . $item->id_spk_budgeting . '"
            ';

            $get_total_kasbon  = $this->db->query($sql_total_kasbon)->result();
            foreach ($get_total_kasbon as $item_kasbon) {
                $total_kasbon += ($item_kasbon->total_subcont + $item_kasbon->total_akomodasi + $item_kasbon->total_others + $item_kasbon->total_lab);
            }

            $total_kasbon_nd = 0;

            $sql_total_kasbon_nd = '
                SELECT
                    a.total_pengajuan as total_subcont, 
                    0 as total_akomodasi, 
                    0 as total_others,
                    0 as total_lab
                FROM
                    kons_tr_kasbon_project_subcont a
                WHERE
                    a.sts IS NULL AND
                    a.id_spk_budgeting = "' . $item->id_spk_budgeting . '"
                
                UNION ALL

                SELECT
                    0 as total_subcont, 
                    a.total_pengajuan as total_akomodasi, 
                    0 as total_others,
                    0 as total_lab
                FROM
                    kons_tr_kasbon_project_akomodasi a
                WHERE
                    a.sts IS NULL AND
                    a.id_spk_budgeting = "' . $item->id_spk_budgeting . '"

                UNION ALL

                SELECT
                    0 as total_subcont, 
                    0 as total_akomodasi, 
                    a.total_pengajuan as total_others,
                    0 as total_lab
                FROM
                    kons_tr_kasbon_project_others a
                WHERE
                    a.sts IS NULL AND
                    a.id_spk_budgeting = "' . $item->id_spk_budgeting . '"

                UNION ALL

                SELECT
                    0 as total_subcont, 
                    0 as total_akomodasi, 
                    0 as total_others,
                    a.total_pengajuan as total_lab
                FROM
                    kons_tr_kasbon_project_lab a
                WHERE
                    a.sts IS NULL AND
                    a.id_spk_budgeting = "' . $item->id_spk_budgeting . '"
            ';

            $get_total_kasbon_nd  = $this->db->query($sql_total_kasbon_nd)->result();
            foreach ($get_total_kasbon_nd as $item_kasbon_nd) {
                $total_kasbon_nd += ($item_kasbon_nd->total_subcont + $item_kasbon_nd->total_akomodasi + $item_kasbon_nd->total_others + $item_kasbon_nd->total_lab);
            }

            // $this->db->select('a.budget_tambahan');
            // $this->db->from(DBCNL.'.kons_tr_kasbon_req_ovb_akomodasi_detail a');
            // $this->db->join(DNBCL.'.kons_tr_kasbon_req_ovb_akomodasi_header b', 'b.id_request_ovb = a.id_request_ovb');
            // $this->db->where('b.id_spk_budgeting', $item->id_spk_budgeting);

            // $get_ovb_akomodasi_nd = $this->db->get()->result();

            // foreach ($get_ovb_akomodasi_nd as $item_ovb_akomodasi_nd) {
            //     $total_kasbon_nd += $item_ovb_akomodasi_nd->budget_tambahan;
            // }

            $valid_show = 1;
            // if ($get_req->num_rows() > 0) {
            //     $valid_show = 0;
            // }
            if ($total_kasbon > 0 || $total_kasbon_nd > 0) {
                if ($total_kasbon >= $total_budgeting) {
                    $valid_show = 0;
                }
            }
            // if ($total_kasbon >= $total_budgeting) {
            //     $valid_show = 0;
            // }

            // if ($valid_show == 1) {
            $no++;

            $status = '<button type="button" class="btn btn-sm btn-warning">Draft</button>';

            $this->db->select('a.*');
            $this->db->from(DBCNL . '.kons_tr_req_kasbon_project a');
            $this->db->where('a.id_spk_budgeting', $item->id_spk_budgeting);
            $this->db->where('a.sts', 0);
            $this->db->limit(1, 0);
            $get_req_kasbon = $this->db->get()->row();

            $reject_reason = '';
            if (!empty($get_req_kasbon)) {
                if ($get_req_kasbon->sts == '1' && $total_kasbon >= $total_budgeting) {
                    $status = '<button type="button" class="btn btn-sm btn-success">Approved</button>';
                }
                if ($get_req_kasbon->sts == '2') {
                    $status = '<button type="button" class="btn btn-sm btn-danger">Rejected</button>';
                }
            }

            $option = '<a href="' . base_url('kasbon_project/view_kasbon/' . urlencode(str_replace('/', '|', $item->id_spk_budgeting))) . '" class="btn btn-sm btn-info" title="View Kasbon"><i class="fa fa-eye"></i></a>';

            $btn_edit = '<a href="' . base_url('kasbon_project/add_kasbon/' . urlencode(str_replace('/', '|', $item->id_spk_budgeting))) . '" class="btn btn-sm btn-primary" style="margin-left: 0.5rem;" title="Process Kasbon"><i class="fa fa-pencil"></i></a>';
            if (!empty($get_req_kasbon)) {
                if ($get_req_kasbon->sts == '1' && $total_kasbon >= $total_budgeting) {
                    $btn_edit = '';
                }
                if ($get_req_kasbon->sts == '2') {
                    $btn_edit = '';
                }
            }

            $btn_req_app = '';
            if ($total_kasbon_nd > 0) {
                $btn_req_app = '<button type="button" class="btn btn-sm btn-warning req_approval" data-id_spk_budgeting="' . $item->id_spk_budgeting . '" title="Request Approval" style="margin-left: 0.5rem;"><i class="fa fa-arrow-up"></i></button>';
            }
            if (!empty($get_req_kasbon)) {
                if ($get_req_kasbon->sts == '1' && $total_kasbon >= $total_budgeting) {
                    $btn_req_app = '';
                }
                if ($get_req_kasbon->sts == '2') {
                    $btn_req_app = '';
                }
            }

            if (!empty($get_req_kasbon)) {
                if ($get_req_kasbon->sts == 0) {
                    $status = '<button type="button" class="btn btn-sm btn-primary">Waiting Approval</button>';
                    $btn_req_app = '';
                    $btn_edit = '';
                }
            }

            $option .= $btn_edit . ' ' . $btn_req_app;


            $hasil[] = [
                'no' => $no,
                'id_spk_penawaran' => $item->id_spk_penawaran,
                'nm_customer' => $item->nm_customer,
                'nm_sales' => ucfirst($item->nm_sales),
                'nm_project_leader' => ucfirst($item->nm_project_leader),
                'nm_project' => $item->nm_paket,
                'reject_reason' => $reject_reason,
                'status' => $status,
                'option' => $option
            ];
            // }
        }

        $no_all = 0;
        foreach ($get_data_all->result() as $item) {
            $this->db->select('a.id');
            $this->db->from(DBCNL . '.kons_tr_req_kasbon_project a');
            $this->db->where('a.id_spk_budgeting', $item->id_spk_budgeting);
            $this->db->group_start();
            $this->db->where('a.sts', '');
            $this->db->or_where('a.sts', null);
            $this->db->group_end();
            $get_req = $this->db->get();

            $total_budgeting = 0;

            $sql_total_budget = '
                SELECT
                    a.total_final as total_akomodasi, 
                    0 as total_others, 
                    0 as total_subcont
                FROM
                    kons_tr_spk_budgeting_akomodasi a
                WHERE
                    a.id_spk_budgeting = "' . $item->id_spk_budgeting . '"

                UNION ALL

                SELECT
                    0 as total_akomodasi, 
                    a.total_final as total_others, 
                    0 as total_subcont
                FROM
                    kons_tr_spk_budgeting_others a
                WHERE
                    a.id_spk_budgeting = "' . $item->id_spk_budgeting . '"

                UNION ALL

                SELECT
                    0 as total_akomodasi, 
                    0 as total_others, 
                    (a.mandays_subcont_final * a.mandays_rate_subcont_final) as total_subcont
                FROM
                    kons_tr_spk_budgeting_aktifitas a
                WHERE
                    a.id_spk_budgeting = "' . $item->id_spk_budgeting . '"
            ';

            $get_total_budget = $this->db->query($sql_total_budget)->result();
            foreach ($get_total_budget as $item_budget) {
                $total_budgeting += ($item_budget->total_akomodasi + $item_budget->total_others + $item_budget->total_subcont);
            }

            $this->db->select('a.budget_tambahan');
            $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_akomodasi_detail a');
            $this->db->join(DBCNL . '.kons_tr_kasbon_req_ovb_akomodasi_header b', 'b.id_request_ovb = a.id_request_ovb');
            $this->db->where('b.id_spk_budgeting', $item->id_spk_budgeting);
            $this->db->where('b.sts', 1);
            $get_ovb_kasbon = $this->db->get()->result();

            foreach ($get_ovb_kasbon as $item_ovb) {
                $total_budgeting += $item_ovb->budget_tambahan;
            }

            $total_kasbon = 0;

            $sql_total_kasbon = '
                SELECT
                    a.total_pengajuan as total_subcont, 
                    0 as total_akomodasi, 
                    0 as total_others
                FROM
                    kons_tr_kasbon_project_subcont a
                WHERE
                    a.sts = "1" AND
                    a.id_spk_budgeting = "' . $item->id_spk_budgeting . '"
                
                UNION ALL

                SELECT
                    0 as total_subcont, 
                    a.total_pengajuan as total_akomodasi, 
                    0 as total_others
                FROM
                    kons_tr_kasbon_project_akomodasi a
                WHERE
                    a.sts = "1" AND
                    a.id_spk_budgeting = "' . $item->id_spk_budgeting . '"

                UNION ALL

                SELECT
                    0 as total_subcont, 
                    0 as total_akomodasi, 
                    a.total_pengajuan as total_others
                FROM
                    kons_tr_kasbon_project_others a
                WHERE
                    a.sts = "1" AND
                    a.id_spk_budgeting = "' . $item->id_spk_budgeting . '"
            ';

            $get_total_kasbon  = $this->db->query($sql_total_kasbon)->result();
            foreach ($get_total_kasbon as $item_kasbon) {
                $total_kasbon += ($item_kasbon->total_subcont + $item_kasbon->total_akomodasi + $item_kasbon->total_others);
            }

            $total_kasbon_nd = 0;

            $sql_total_kasbon_nd = '
                SELECT
                    a.total_pengajuan as total_subcont, 
                    0 as total_akomodasi, 
                    0 as total_others
                FROM
                    kons_tr_kasbon_project_subcont a
                WHERE
                    a.sts IS NULL AND
                    a.id_spk_budgeting = "' . $item->id_spk_budgeting . '"
                
                UNION ALL

                SELECT
                    0 as total_subcont, 
                    a.total_pengajuan as total_akomodasi, 
                    0 as total_others
                FROM
                    kons_tr_kasbon_project_akomodasi a
                WHERE
                    a.sts IS NULL AND
                    a.id_spk_budgeting = "' . $item->id_spk_budgeting . '"

                UNION ALL

                SELECT
                    0 as total_subcont, 
                    0 as total_akomodasi, 
                    a.total_pengajuan as total_others
                FROM
                    kons_tr_kasbon_project_others a
                WHERE
                    a.sts IS NULL AND
                    a.id_spk_budgeting = "' . $item->id_spk_budgeting . '"
            ';

            $get_total_kasbon_nd  = $this->db->query($sql_total_kasbon_nd)->result();
            foreach ($get_total_kasbon_nd as $item_kasbon_nd) {
                $total_kasbon_nd += ($item_kasbon_nd->total_subcont + $item_kasbon_nd->total_akomodasi + $item_kasbon_nd->total_others);
            }

            // $this->db->select('a.budget_tambahan');
            // $this->db->from(DBCNL.'.kons_tr_kasbon_req_ovb_akomodasi_detail a');
            // $this->db->join(DNBCL.'.kons_tr_kasbon_req_ovb_akomodasi_header b', 'b.id_request_ovb = a.id_request_ovb');
            // $this->db->where('b.id_spk_budgeting', $item->id_spk_budgeting);

            // $get_ovb_akomodasi_nd = $this->db->get()->result();

            // foreach ($get_ovb_akomodasi_nd as $item_ovb_akomodasi_nd) {
            //     $total_kasbon_nd += $item_ovb_akomodasi_nd->budget_tambahan;
            // }

            $valid_show = 1;
            // if ($get_req->num_rows() > 0) {
            //     $valid_show = 0;
            // }
            if ($total_kasbon > 0 || $total_kasbon_nd > 0) {
                if ($total_kasbon >= $total_budgeting) {
                    $valid_show = 0;
                }
            }
            // if ($total_kasbon >= $total_budgeting) {
            //     $valid_show = 0;
            // }

            if ($valid_show == 1) {
                $no_all++;
            }
        }

        echo json_encode([
            'draw' => intval($draw),
            'recordsTotal' => $no_all,
            'recordsFiltered' => $no_all,
            'data' => $hasil
        ]);
    }

    public function get_data_kasbon_subcont()
    {
        $draw = $this->input->post('draw');
        $start = $this->input->post('start');
        $length = $this->input->post('length');
        $search = $this->input->post('search');
        $id_spk_budgeting = $this->input->post('id_spk_budgeting');
        $view = $this->input->post('view');

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_header a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->where('a.tipe', 1);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.id', $search['value'], 'both');
            $this->db->or_like('a.deskripsi', $search['value'], 'both');
            $this->db->or_like('a.tgl', $search['value'], 'both');
            $this->db->or_like('a.grand_total', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->order_by('a.created_by', 'desc');
        $this->db->limit($length, $start);
        $get_kasbon_subcont = $this->db->get();

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_header a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->where('a.tipe', 1);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.id', $search['value'], 'both');
            $this->db->or_like('a.deskripsi', $search['value'], 'both');
            $this->db->or_like('a.tgl', $search['value'], 'both');
            $this->db->or_like('a.grand_total', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->order_by('a.created_by', 'desc');
        $get_kasbon_subcont_all = $this->db->get();

        $nilai_kasbon_on_proses = 0;
        foreach ($get_kasbon_subcont_all->result() as $item) {
            if ($item->sts !== '1') {
                $nilai_kasbon_on_proses += $item->grand_total;
            }
        }

        $hasil = [];

        $no = 1;
        foreach ($get_kasbon_subcont->result() as $item) {
            $sts = '<button type="button" class="btn btn-sm btn-warning">Draft</button>';
            if ($item->sts_req == '1') {
                $sts = '<button type="button" class="btn btn-sm btn-warning">Waiting Approval</button>';
            }

            if ($item->sts == '1') {
                $sts = '<button type="button" class="btn btn-sm btn-success">Approved</button>';
            }
            if ($item->sts_reject !== null || $item->sts_reject_manage !== null) {
                if ($item->sts_reject !== null) {
                    $sts = '<button type="button" class="btn btn-sm btn-danger">Rejected by Finance</button>';
                }
                if ($item->sts_reject_manage !== null) {
                    $sts = '<button type="button" class="btn btn-sm btn-danger">Rejected by Direktur</button>';
                }
            }

            $check_payment = $this->db->get_where('payment_approve', array('no_doc' => $item->id, 'status' => 2))->row();
            if (!empty($check_payment)) {
                $sts = '<button type="button" class="btn btn-sm btn-success">Paid</button>';
            }

            $option = '
                <div class="btn-group">
                    <button
                        type="button"
                        class="btn btn-sm btn-accent text-primary dropdown-toggle"
                        title="Actions"
                        data-toggle="dropdown"
                        id="dropdownMenu' . $no . '"
                        aria-expanded="false">
                        <i class="fa fa-cogs"></i> <span class="caret"></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
            ';

            $option .= '
                <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                    <a href="' . base_url('kasbon_project/view_kasbon_subcont/' . urlencode(str_replace('/', '|', $item->id))) . '" class="btn btn-sm btn-info" style="color: #000000">
                        <div class="col-12 dropdown-item">
                        <b>
                            <i class="fa fa-eye"></i>
                        </b>
                        </div>
                    </a>
                    <span style="font-weight: 500"> View </span>
                </div>
            ';

            if ($item->sts !== '1' && $item->sts_req !== '1') {
                $option .= '
                    <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                        <a href="javascript:void(0);" class="btn btn-sm btn-danger del_kasbon_subcont" style="color: #000000" data-id="' . $item->id . '">
                            <div class="col-12 dropdown-item">
                            <b>
                                <i class="fa fa-trash"></i>
                            </b>
                            </div>
                        </a>
                        <span style="font-weight: 500"> Delete </span>
                    </div>
                ';

                $option .= '
                    <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                        <a href="' . base_url('kasbon_project/edit_kasbon_subcont/' . urlencode(str_replace('/', '|', $item->id))) . '" class="btn btn-sm btn-warning" style="color: #000000">
                            <div class="col-12 dropdown-item">
                            <b>
                                <i class="fa fa-pencil"></i>
                            </b>
                            </div>
                        </a>
                        <span style="font-weight: 500"> Edit </span>
                    </div>
                ';
            }

            // if ($item->sts_req == '0') {
            //     $option .= '
            //         <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
            //             <a href="javascript:void(0);" class="btn btn-sm btn-primary req_approve_kasbon" style="color: #000000" data-id="' . $item->id . '" title="Request Approval">
            //                 <div class="col-12 dropdown-item">
            //                 <b>
            //                     <i class="fa fa-arrow-up"></i>
            //                 </b>
            //                 </div>
            //             </a>
            //             <span style="font-weight: 500"> Req. Approval </span>
            //         </div>
            //     ';
            // }

            // $option .= '
            //     <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
            //         <a href="javascript:void(0);" class="btn btn-sm btn-danger" style="color: #000000">
            //             <div class="col-12 dropdown-item">
            //             <b>
            //                 <i class="fa fa-close"></i>
            //             </b>
            //             </div>
            //         </a>
            //         <span style="font-weight: 500"> Reject </span>
            //     </div>
            // ';



            $option .= '</div>';

            if ($view == 'view') {
                $option = '';
            }

            $hasil[] = [
                'no' => $no,
                'req_number' => $item->id,
                'nm_aktifitas' => $item->deskripsi,
                'date' => date('d F Y', strtotime($item->tgl)),
                'total' => number_format($item->grand_total, 2),
                'status' => $sts,
                'reject_reason' => $item->reject_reason,
                'option' => $option
            ];

            $no++;
        }

        echo json_encode([
            'draw' => intval($draw),
            'recordsTotal' => $get_kasbon_subcont_all->num_rows(),
            'recordsFiltered' => $get_kasbon_subcont_all->num_rows(),
            'data' => $hasil
        ]);
    }

    public function get_data_kasbon_akomodasi()
    {
        $draw = $this->input->post('draw');
        $start = $this->input->post('start');
        $length = $this->input->post('length');
        $search = $this->input->post('search');
        $id_spk_budgeting = $this->input->post('id_spk_budgeting');
        $view = $this->input->post('view');

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_header a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->where('a.tipe', 2);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.id', $search['value'], 'both');
            $this->db->or_like('a.deskripsi', $search['value'], 'both');
            $this->db->or_like('a.tgl', $search['value'], 'both');
            $this->db->or_like('a.grand_total', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->order_by('a.created_by', 'desc');
        $this->db->limit($length, $start);
        $get_kasbon_akomodasi = $this->db->get();

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_header a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->where('a.tipe', 2);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.id', $search['value'], 'both');
            $this->db->or_like('a.deskripsi', $search['value'], 'both');
            $this->db->or_like('a.tgl', $search['value'], 'both');
            $this->db->or_like('a.grand_total', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->order_by('a.created_by', 'desc');
        $get_kasbon_akomodasi_all = $this->db->get();

        $nilai_kasbon_on_proses = 0;
        foreach ($get_kasbon_akomodasi_all->result() as $item) {
            if ($item->sts !== '1') {
                $nilai_kasbon_on_proses += $item->grand_total;
            }
        }

        $hasil = [];

        $no = 1;
        foreach ($get_kasbon_akomodasi->result() as $item) {
            $sts = '<button type="button" class="btn btn-sm btn-warning">Draft</button>';
            if ($item->sts_req == '1') {
                $sts = '<button type="button" class="btn btn-sm btn-warning">Waiting Approval</button>';
            }
            if ($item->sts == '1') {
                $sts = '<button type="button" class="btn btn-sm btn-success">Approved</button>';
            }

            if ($item->sts_reject !== null || $item->sts_reject_manage !== null) {
                if ($item->sts_reject !== null) {
                    $sts = '<button type="button" class="btn btn-sm btn-danger">Rejected by Finance</button>';
                }
                if ($item->sts_reject_manage !== null) {
                    $sts = '<button type="button" class="btn btn-sm btn-danger">Rejected by Direktur</button>';
                }
            }

            $check_payment = $this->db->get_where('payment_approve', array('no_doc' => $item->id, 'status' => 2))->row();
            if (!empty($check_payment)) {
                $sts = '<button type="button" class="btn btn-sm btn-success">Paid</button>';
            }

            $option = '
                <div class="btn-group">
                    <button
                        type="button"
                        class="btn btn-sm btn-accent text-primary dropdown-toggle"
                        title="Actions"
                        data-toggle="dropdown"
                        id="dropdownMenu' . $no . '"
                        aria-expanded="false">
                        <i class="fa fa-cogs"></i> <span class="caret"></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
            ';

            $option .= '
                <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                    <a href="' . base_url('kasbon_project/view_kasbon_akomodasi/' . urlencode(str_replace('/', '|', $item->id))) . '" class="btn btn-sm btn-info" style="color: #000000">
                        <div class="col-12 dropdown-item">
                        <b>
                            <i class="fa fa-eye"></i>
                        </b>
                        </div>
                    </a>
                    <span style="font-weight: 500"> View </span>
                </div>
            ';

            if ($item->sts !== '1' && $item->sts_req !== '1') {
                $option .= '
                    <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                        <a href="javascript:void(0);" class="btn btn-sm btn-danger del_kasbon_akomodasi" style="color: #000000" data-id="' . $item->id . '">
                            <div class="col-12 dropdown-item">
                            <b>
                                <i class="fa fa-trash"></i>
                            </b>
                            </div>
                        </a>
                        <span style="font-weight: 500"> Delete </span>
                    </div>
                ';

                $option .= '
                    <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                        <a href="' . base_url('kasbon_project/edit_kasbon_akomodasi/' . urlencode(str_replace('/', '|', $item->id))) . '" class="btn btn-sm btn-warning" style="color: #000000">
                            <div class="col-12 dropdown-item">
                            <b>
                                <i class="fa fa-pencil"></i>
                            </b>
                            </div>
                        </a>
                        <span style="font-weight: 500"> Edit </span>
                    </div>
                ';
            }

            // if ($item->sts_req == '0') {
            //     $option .= '
            //         <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
            //             <a href="javascript:void(0);" class="btn btn-sm btn-primary req_approve_kasbon" style="color: #000000" data-id="' . $item->id . '" title="Request Approval">
            //                 <div class="col-12 dropdown-item">
            //                 <b>
            //                     <i class="fa fa-arrow-up"></i>
            //                 </b>
            //                 </div>
            //             </a>
            //             <span style="font-weight: 500"> Req. Approval </span>
            //         </div>
            //     ';
            // }

            $option .= '</div>';

            if ($view == 'view') {
                $option = '';
            }

            $hasil[] = [
                'no' => $no,
                'req_number' => $item->id,
                'nm_biaya' => $item->deskripsi,
                'date' => date('d F Y', strtotime($item->tgl)),
                'total' => number_format($item->grand_total, 2),
                'status' => $sts,
                'reject_reason' => $item->reject_reason,
                'option' => $option
            ];

            $no++;
        }

        echo json_encode([
            'draw' => intval($draw),
            'recordsTotal' => $get_kasbon_akomodasi_all->num_rows(),
            'recordsFiltered' => $get_kasbon_akomodasi_all->num_rows(),
            'data' => $hasil
        ]);
    }

    public function get_data_kasbon_others()
    {
        $draw = $this->input->post('draw');
        $start = $this->input->post('start');
        $length = $this->input->post('length');
        $search = $this->input->post('search');
        $id_spk_budgeting = $this->input->post('id_spk_budgeting');
        $view = $this->input->post('view');

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_header a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->where('a.tipe', 3);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.id', $search['value'], 'both');
            $this->db->or_like('a.deskripsi', $search['value'], 'both');
            $this->db->or_like('a.tgl', $search['value'], 'both');
            $this->db->or_like('a.grand_total', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->order_by('a.created_by', 'desc');
        $this->db->limit($length, $start);
        $get_kasbon_others = $this->db->get();

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_header a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->where('a.tipe', 3);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.id', $search['value'], 'both');
            $this->db->or_like('a.deskripsi', $search['value'], 'both');
            $this->db->or_like('a.tgl', $search['value'], 'both');
            $this->db->or_like('a.grand_total', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->order_by('a.created_by', 'desc');
        $get_kasbon_others_all = $this->db->get();

        $nilai_kasbon_on_proses = 0;
        foreach ($get_kasbon_others_all->result() as $item) {
            if ($item->sts !== '1') {
                $nilai_kasbon_on_proses += $item->grand_total;
            }
        }

        $hasil = [];

        $no = 1;
        foreach ($get_kasbon_others->result() as $item) {
            $sts = '<button type="button" class="btn btn-sm btn-warning">Draft</button>';
            if ($item->sts_req == '1') {
                $sts = '<button type="button" class="btn btn-sm btn-warning">Waiting Approval</button>';
            }
            if ($item->sts == '1') {
                $sts = '<button type="button" class="btn btn-sm btn-success">Approved</button>';
            }
            if ($item->sts_reject !== null || $item->sts_reject_manage !== null) {
                if ($item->sts_reject !== null) {
                    $sts = '<button type="button" class="btn btn-sm btn-danger">Rejected by Finance</button>';
                }
                if ($item->sts_reject_manage !== null) {
                    $sts = '<button type="button" class="btn btn-sm btn-danger">Rejected by Direktur</button>';
                }
            }

            $check_payment = $this->db->get_where('payment_approve', array('no_doc' => $item->id, 'status' => 2))->row();
            if (!empty($check_payment)) {
                $sts = '<button type="button" class="btn btn-sm btn-success">Paid</button>';
            }

            $option = '
                <div class="btn-group">
                    <button
                        type="button"
                        class="btn btn-sm btn-accent text-primary dropdown-toggle"
                        title="Actions"
                        data-toggle="dropdown"
                        id="dropdownMenu' . $no . '"
                        aria-expanded="false">
                        <i class="fa fa-cogs"></i> <span class="caret"></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
            ';

            $option .= '
                <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                    <a href="' . base_url('kasbon_project/view_kasbon_others/' . urlencode(str_replace('/', '|', $item->id))) . '" class="btn btn-sm btn-info" style="color: #000000">
                        <div class="col-12 dropdown-item">
                        <b>
                            <i class="fa fa-eye"></i>
                        </b>
                        </div>
                    </a>
                    <span style="font-weight: 500"> View </span>
                </div>
            ';

            if ($item->sts !== '1' && $item->sts_req !== '1') {
                $option .= '
                    <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                        <a href="javascript:void(0);" class="btn btn-sm btn-danger del_kasbon_others" style="color: #000000" data-id="' . $item->id . '">
                            <div class="col-12 dropdown-item">
                            <b>
                                <i class="fa fa-trash"></i>
                            </b>
                            </div>
                        </a>
                        <span style="font-weight: 500"> Delete </span>
                    </div>
                ';

                $option .= '
                    <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                        <a href="' . base_url('kasbon_project/edit_kasbon_others/' . urlencode(str_replace('/', '|', $item->id))) . '" class="btn btn-sm btn-warning" style="color: #000000">
                            <div class="col-12 dropdown-item">
                            <b>
                                <i class="fa fa-pencil"></i>
                            </b>
                            </div>
                        </a>
                        <span style="font-weight: 500"> Edit </span>
                    </div>
                ';
            }

            // if ($item->sts_req == '0') {
            //     $option .= '
            //         <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
            //             <a href="javascript:void(0);" class="btn btn-sm btn-primary req_approve_kasbon" style="color: #000000" data-id="' . $item->id . '" title="Request Approval">
            //                 <div class="col-12 dropdown-item">
            //                 <b>
            //                     <i class="fa fa-arrow-up"></i>
            //                 </b>
            //                 </div>
            //             </a>
            //             <span style="font-weight: 500"> Req. Approval </span>
            //         </div>
            //     ';
            // }

            $option .= '</div>';

            if ($view == 'view') {
                $option = '';
            }

            $hasil[] = [
                'no' => $no,
                'req_number' => $item->id,
                'nm_biaya' => $item->deskripsi,
                'date' => date('d F Y', strtotime($item->created_date)),
                'total' => number_format($item->grand_total, 2),
                'status' => $sts,
                'reject_reason' => $item->reject_reason,
                'option' => $option
            ];

            $no++;
        }

        echo json_encode([
            'draw' => intval($draw),
            'recordsTotal' => $get_kasbon_others_all->num_rows(),
            'recordsFiltered' => $get_kasbon_others_all->num_rows(),
            'data' => $hasil
        ]);
    }

    public function get_data_kasbon_lab()
    {
        $draw = $this->input->post('draw');
        $start = $this->input->post('start');
        $length = $this->input->post('length');
        $search = $this->input->post('search');
        $id_spk_budgeting = $this->input->post('id_spk_budgeting');
        $view = $this->input->post('view');

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_header a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->where('a.tipe', 4);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.id', $search['value'], 'both');
            $this->db->or_like('a.deskripsi', $search['value'], 'both');
            $this->db->or_like('a.tgl', $search['value'], 'both');
            $this->db->or_like('a.grand_total', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->order_by('a.created_by', 'desc');
        $this->db->limit($length, $start);
        $get_kasbon_lab = $this->db->get();

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_header a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->where('a.tipe', 3);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.id', $search['value'], 'both');
            $this->db->or_like('a.deskripsi', $search['value'], 'both');
            $this->db->or_like('a.tgl', $search['value'], 'both');
            $this->db->or_like('a.grand_total', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->order_by('a.created_by', 'desc');
        $get_kasbon_lab_all = $this->db->get();

        $nilai_kasbon_on_proses = 0;
        foreach ($get_kasbon_lab_all->result() as $item) {
            if ($item->sts !== '1') {
                $nilai_kasbon_on_proses += $item->grand_total;
            }
        }

        $hasil = [];

        $no = 1;
        foreach ($get_kasbon_lab->result() as $item) {
            $sts = '<button type="button" class="btn btn-sm btn-warning">Draft</button>';
            if ($item->sts_req == '1') {
                $sts = '<button type="button" class="btn btn-sm btn-warning">Waiting Approval</button>';
            }
            if ($item->sts == '1') {
                $sts = '<button type="button" class="btn btn-sm btn-success">Approved</button>';
            }
            if ($item->sts_reject !== null || $item->sts_reject_manage !== null) {
                if ($item->sts_reject !== null) {
                    $sts = '<button type="button" class="btn btn-sm btn-danger">Rejected by Finance</button>';
                }
                if ($item->sts_reject_manage !== null) {
                    $sts = '<button type="button" class="btn btn-sm btn-danger">Rejected by Direktur</button>';
                }
            }

            $check_payment = $this->db->get_where('payment_approve', array('no_doc' => $item->id, 'status' => 2))->row();
            if (!empty($check_payment)) {
                $sts = '<button type="button" class="btn btn-sm btn-success">Paid</button>';
            }

            $option = '
                <div class="btn-group">
                    <button
                        type="button"
                        class="btn btn-sm btn-accent text-primary dropdown-toggle"
                        title="Actions"
                        data-toggle="dropdown"
                        id="dropdownMenu' . $no . '"
                        aria-expanded="false">
                        <i class="fa fa-cogs"></i> <span class="caret"></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
            ';

            $option .= '
                <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                    <a href="' . base_url('kasbon_project/view_kasbon_lab/' . urlencode(str_replace('/', '|', $item->id))) . '" class="btn btn-sm btn-info" style="color: #000000">
                        <div class="col-12 dropdown-item">
                        <b>
                            <i class="fa fa-eye"></i>
                        </b>
                        </div>
                    </a>
                    <span style="font-weight: 500"> View </span>
                </div>
            ';

            if ($item->sts !== '1' && $item->sts_req !== '1') {
                $option .= '
                    <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                        <a href="javascript:void(0);" class="btn btn-sm btn-danger del_kasbon_lab" style="color: #000000" data-id="' . $item->id . '">
                            <div class="col-12 dropdown-item">
                            <b>
                                <i class="fa fa-trash"></i>
                            </b>
                            </div>
                        </a>
                        <span style="font-weight: 500"> Delete </span>
                    </div>
                ';

                $option .= '
                    <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                        <a href="' . base_url('kasbon_project/edit_kasbon_lab/' . urlencode(str_replace('/', '|', $item->id))) . '" class="btn btn-sm btn-warning" style="color: #000000">
                            <div class="col-12 dropdown-item">
                            <b>
                                <i class="fa fa-pencil"></i>
                            </b>
                            </div>
                        </a>
                        <span style="font-weight: 500"> Edit </span>
                    </div>
                ';
            }

            // if ($item->sts_req == '0') {
            //     $option .= '
            //         <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
            //             <a href="javascript:void(0);" class="btn btn-sm btn-primary req_approve_kasbon" style="color: #000000" data-id="' . $item->id . '" title="Request Approval">
            //                 <div class="col-12 dropdown-item">
            //                 <b>
            //                     <i class="fa fa-arrow-up"></i>
            //                 </b>
            //                 </div>
            //             </a>
            //             <span style="font-weight: 500"> Req. Approval </span>
            //         </div>
            //     ';
            // }

            $option .= '</div>';

            if ($view == 'view') {
                $option = '';
            }

            $hasil[] = [
                'no' => $no,
                'req_number' => $item->id,
                'nm_biaya' => $item->deskripsi,
                'date' => date('d F Y', strtotime($item->created_date)),
                'total' => number_format($item->grand_total, 2),
                'status' => $sts,
                'reject_reason' => $item->reject_reason,
                'option' => $option
            ];

            $no++;
        }

        echo json_encode([
            'draw' => intval($draw),
            'recordsTotal' => $get_kasbon_lab_all->num_rows(),
            'recordsFiltered' => $get_kasbon_lab_all->num_rows(),
            'data' => $hasil
        ]);
    }

    public function get_data_ovb_akomodasi()
    {
        $draw = $this->input->post('draw');
        $start = $this->input->post('start');
        $length = $this->input->post('length');
        $search = $this->input->post('search');
        $id_spk_budgeting = $this->input->post('id_spk_budgeting');
        $view = $this->input->post('view');

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_akomodasi_header a');
        $this->db->where('a.tipe', '2');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.id_request_ovb', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->group_by('a.id_request_ovb');
        $this->db->order_by('a.created_date', 'desc');
        $this->db->limit($length, $start);

        $get_data = $this->db->get();

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_akomodasi_header a');
        $this->db->where('a.tipe', '2');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.id_request_ovb', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->group_by('a.id_request_ovb');
        $this->db->order_by('a.created_date', 'desc');

        $get_data_all = $this->db->get();

        $hasil = [];

        $no = ($start + 1);

        foreach ($get_data->result_array() as $item) {

            $this->db->select('IF(SUM(a.budget_tambahan) IS NULL, 0, SUM(a.budget_tambahan)) as amount');
            $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_akomodasi_detail a');
            $this->db->where('a.id_request_ovb', $item['id_request_ovb']);
            $get_amount = $this->db->get()->row_array();

            $option = '
                <div class="btn-group">
                    <button
                        type="button"
                        class="btn btn-sm btn-accent text-primary dropdown-toggle"
                        title="Actions"
                        data-toggle="dropdown"
                        id="dropdownMenu' . $no . '"
                        aria-expanded="false">
                        <i class="fa fa-cogs"></i> <span class="caret"></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
            ';

            $option .= '
                    <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                        <a href="' . base_url('kasbon_project/view_request_budget_akomodasi/' . urlencode(str_replace('/', '|', $item['id_request_ovb']))) . '" class="btn btn-sm btn-info" style="color: #000000">
                            <div class="col-12 dropdown-item">
                            <b>
                                <i class="fa fa-eye"></i>
                            </b>
                            </div>
                        </a>
                        <span style="font-weight: 500"> View </span>
                    </div>
                ';

            if ($item['sts'] !== '1') {
                $option .= '
                    <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                        <a href="javascript:void(0);" class="btn btn-sm btn-danger del_ovb_akomodasi" style="color: #000000" data-id_request_ovb="' . $item['id_request_ovb'] . '">
                            <div class="col-12 dropdown-item">
                            <b>
                                <i class="fa fa-trash"></i>
                            </b>
                            </div>
                        </a>
                        <span style="font-weight: 500"> Delete </span>
                    </div>
                ';
            }

            $option .= '</div>';

            if ($view == 'view') {
                $option = '';
            }

            $sts = '<button type="button" class="btn btn-sm btn-primary">Waiting Approval</button>';
            if ($item['sts'] == '1') {
                $sts = '<button type="button" class="btn btn-sm btn-success">Approved</button>';
            }
            if ($item['sts'] == '2') {
                $sts = '<button type="button" class="btn btn-sm btn-danger">Rejected</button>';
            }


            $hasil[] = [
                'no' => $no,
                'id_request_ovb' => $item['id_request_ovb'],
                'amount' => number_format($get_amount['amount'], 2),
                'sts' => $sts,
                'option' => $option
            ];

            $no++;
        }

        echo json_encode([
            'draw' => intval($draw),
            'recordsTotal' => $get_data_all->num_rows(),
            'recordsFiltered' => $get_data_all->num_rows(),
            'data' => $hasil
        ]);
    }

    public function get_data_ovb_others()
    {
        $draw = $this->input->post('draw');
        $start = $this->input->post('start');
        $length = $this->input->post('length');
        $search = $this->input->post('search');
        $id_spk_budgeting = $this->input->post('id_spk_budgeting');
        $view = $this->input->post('view');

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_others_header a');
        $this->db->where('a.tipe', '3');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.id_request_ovb', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->group_by('a.id_request_ovb');
        $this->db->order_by('a.created_date', 'desc');
        $this->db->limit($length, $start);

        $get_data = $this->db->get();

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_others_header a');
        $this->db->where('a.tipe', '3');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.id_request_ovb', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->group_by('a.id_request_ovb');
        $this->db->order_by('a.created_date', 'desc');

        $get_data_all = $this->db->get();

        $hasil = [];

        $no = ($start + 1);

        foreach ($get_data->result_array() as $item) {

            $this->db->select('IF(SUM(a.budget_tambahan) IS NULL, 0, SUM(a.budget_tambahan)) as amount');
            $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_others_detail a');
            $this->db->where('a.id_request_ovb', $item['id_request_ovb']);
            $get_amount = $this->db->get()->row_array();

            $option = '
                <div class="btn-group">
                    <button
                        type="button"
                        class="btn btn-sm btn-accent text-primary dropdown-toggle"
                        title="Actions"
                        data-toggle="dropdown"
                        id="dropdownMenu' . $no . '"
                        aria-expanded="false">
                        <i class="fa fa-cogs"></i> <span class="caret"></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
            ';

            $option .= '
                    <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                        <a href="' . base_url('kasbon_project/view_request_budget_others/' . urlencode(str_replace('/', '|', $item['id_request_ovb']))) . '" class="btn btn-sm btn-info" style="color: #000000">
                            <div class="col-12 dropdown-item">
                            <b>
                                <i class="fa fa-eye"></i>
                            </b>
                            </div>
                        </a>
                        <span style="font-weight: 500"> View </span>
                    </div>
                ';

            if ($item['sts'] !== '1') {
                $option .= '
                    <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                        <a href="javascript:void(0);" class="btn btn-sm btn-danger del_ovb_others" style="color: #000000" data-id_request_ovb="' . $item['id_request_ovb'] . '">
                            <div class="col-12 dropdown-item">
                            <b>
                                <i class="fa fa-trash"></i>
                            </b>
                            </div>
                        </a>
                        <span style="font-weight: 500"> Delete </span>
                    </div>
                ';
            }

            $option .= '</div>';

            if ($view == 'view') {
                $option = '';
            }

            $sts = '<button type="button" class="btn btn-sm btn-primary">Waiting Approval</button>';
            if ($item['sts'] == '1') {
                $sts = '<button type="button" class="btn btn-sm btn-success">Approved</button>';
            }
            if ($item['sts'] == '2') {
                $sts = '<button type="button" class="btn btn-sm btn-danger">Rejected</button>';
            }


            $hasil[] = [
                'no' => $no,
                'id_request_ovb' => $item['id_request_ovb'],
                'amount' => number_format($get_amount['amount'], 2),
                'sts' => $sts,
                'option' => $option
            ];

            $no++;
        }

        echo json_encode([
            'draw' => intval($draw),
            'recordsTotal' => $get_data_all->num_rows(),
            'recordsFiltered' => $get_data_all->num_rows(),
            'data' => $hasil
        ]);
    }

    public function get_data_ovb_lab()
    {
        $draw = $this->input->post('draw');
        $start = $this->input->post('start');
        $length = $this->input->post('length');
        $search = $this->input->post('search');
        $id_spk_budgeting = $this->input->post('id_spk_budgeting');
        $view = $this->input->post('view');

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_lab_header a');
        $this->db->where('a.tipe', '4');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.id_request_ovb', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->group_by('a.id_request_ovb');
        $this->db->order_by('a.created_date', 'desc');
        $this->db->limit($length, $start);

        $get_data = $this->db->get();

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_lab_header a');
        $this->db->where('a.tipe', '4');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.id_request_ovb', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->group_by('a.id_request_ovb');
        $this->db->order_by('a.created_date', 'desc');

        $get_data_all = $this->db->get();

        $hasil = [];

        $no = ($start + 1);

        foreach ($get_data->result_array() as $item) {

            $this->db->select('IF(SUM(a.budget_tambahan) IS NULL, 0, SUM(a.budget_tambahan)) as amount');
            $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_lab_detail a');
            $this->db->where('a.id_request_ovb', $item['id_request_ovb']);
            $get_amount = $this->db->get()->row_array();

            $option = '
                <div class="btn-group">
                    <button
                        type="button"
                        class="btn btn-sm btn-accent text-primary dropdown-toggle"
                        title="Actions"
                        data-toggle="dropdown"
                        id="dropdownMenu' . $no . '"
                        aria-expanded="false">
                        <i class="fa fa-cogs"></i> <span class="caret"></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
            ';

            $option .= '
                    <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                        <a href="' . base_url('kasbon_project/view_request_budget_lab/' . urlencode(str_replace('/', '|', $item['id_request_ovb']))) . '" class="btn btn-sm btn-info" style="color: #000000">
                            <div class="col-12 dropdown-item">
                            <b>
                                <i class="fa fa-eye"></i>
                            </b>
                            </div>
                        </a>
                        <span style="font-weight: 500"> View </span>
                    </div>
                ';

            if ($item['sts'] !== '1') {
                $option .= '
                    <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                        <a href="javascript:void(0);" class="btn btn-sm btn-danger del_ovb_lab" style="color: #000000" data-id_request_ovb="' . $item['id_request_ovb'] . '">
                            <div class="col-12 dropdown-item">
                            <b>
                                <i class="fa fa-trash"></i>
                            </b>
                            </div>
                        </a>
                        <span style="font-weight: 500"> Delete </span>
                    </div>
                ';
            }

            $option .= '</div>';

            if ($view == 'view') {
                $option = '';
            }

            $sts = '<button type="button" class="btn btn-sm btn-primary">Waiting Approval</button>';
            if ($item['sts'] == '1') {
                $sts = '<button type="button" class="btn btn-sm btn-success">Approved</button>';
            }
            if ($item['sts'] == '2') {
                $sts = '<button type="button" class="btn btn-sm btn-danger">Rejected</button>';
            }


            $hasil[] = [
                'no' => $no,
                'id_request_ovb' => $item['id_request_ovb'],
                'amount' => number_format($get_amount['amount'], 2),
                'sts' => $sts,
                'option' => $option
            ];

            $no++;
        }

        echo json_encode([
            'draw' => intval($draw),
            'recordsTotal' => $get_data_all->num_rows(),
            'recordsFiltered' => $get_data_all->num_rows(),
            'data' => $hasil
        ]);
    }

    public function get_data_ovb_subcont()
    {
        $draw = $this->input->post('draw');
        $start = $this->input->post('start');
        $length = $this->input->post('length');
        $search = $this->input->post('search');
        $id_spk_budgeting = $this->input->post('id_spk_budgeting');
        $view = $this->input->post('view');

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_subcont_header a');
        $this->db->where('a.tipe', '1');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.id_request_ovb', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->group_by('a.id_request_ovb');
        $this->db->order_by('a.created_date', 'desc');
        $this->db->limit($length, $start);

        $get_data = $this->db->get();

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_subcont_header a');
        $this->db->where('a.tipe', '1');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('a.id_request_ovb', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->group_by('a.id_request_ovb');
        $this->db->order_by('a.created_date', 'desc');

        $get_data_all = $this->db->get();

        $hasil = [];

        $no = ($start + 1);

        foreach ($get_data->result_array() as $item) {

            $this->db->select('IF(SUM(a.budget_tambahan) IS NULL, 0, SUM(a.budget_tambahan)) as amount');
            $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_subcont_detail a');
            $this->db->where('a.id_request_ovb', $item['id_request_ovb']);
            $get_amount = $this->db->get()->row_array();

            $option = '
                <div class="btn-group">
                    <button
                        type="button"
                        class="btn btn-sm btn-accent text-primary dropdown-toggle"
                        title="Actions"
                        data-toggle="dropdown"
                        id="dropdownMenu' . $no . '"
                        aria-expanded="false">
                        <i class="fa fa-cogs"></i> <span class="caret"></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
            ';

            $option .= '
                    <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                        <a href="' . base_url('kasbon_project/view_request_budget_subcont/' . urlencode(str_replace('/', '|', $item['id_request_ovb']))) . '" class="btn btn-sm btn-info" style="color: #000000">
                            <div class="col-12 dropdown-item">
                            <b>
                                <i class="fa fa-eye"></i>
                            </b>
                            </div>
                        </a>
                        <span style="font-weight: 500"> View </span>
                    </div>
                ';

            if ($item['sts'] !== '1') {
                $option .= '
                    <div class="col-12" style="margin-left: 0.5rem; padding-top: 0.5rem;">
                        <a href="javascript:void(0);" class="btn btn-sm btn-danger del_ovb_subcont" style="color: #000000" data-id_request_ovb="' . $item['id_request_ovb'] . '">
                            <div class="col-12 dropdown-item">
                            <b>
                                <i class="fa fa-trash"></i>
                            </b>
                            </div>
                        </a>
                        <span style="font-weight: 500"> Delete </span>
                    </div>
                ';
            }

            $option .= '</div>';

            if ($view == 'view') {
                $option = '';
            }

            $sts = '<button type="button" class="btn btn-sm btn-primary">Waiting Approval</button>';
            if ($item['sts'] == '1') {
                $sts = '<button type="button" class="btn btn-sm btn-success">Approved</button>';
            }
            if ($item['sts'] == '2') {
                $sts = '<button type="button" class="btn btn-sm btn-danger">Rejected</button>';
            }


            $hasil[] = [
                'no' => $no,
                'id_request_ovb' => $item['id_request_ovb'],
                'amount' => number_format($get_amount['amount'], 2),
                'sts' => $sts,
                'option' => $option
            ];

            $no++;
        }

        echo json_encode([
            'draw' => intval($draw),
            'recordsTotal' => $get_data_all->num_rows(),
            'recordsFiltered' => $get_data_all->num_rows(),
            'data' => $hasil
        ]);
    }

    public function add_kasbon($id_spk_budgeting)
    {
        $id_spk_budgeting = urldecode($id_spk_budgeting);
        $id_spk_budgeting = str_replace('|', '/', $id_spk_budgeting);

        $this->db->select('a.*, b.nm_sales, b.waktu_from, b.waktu_to, c.nm_paket');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting a');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran b', 'b.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->join(DBCNL . '.kons_master_konsultasi_header c', 'c.id_konsultasi_h = a.id_project', 'left');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budgeting = $this->db->get()->row();

        $budget_subcont = 0;
        $this->db->select('a.mandays_subcont_final, a.mandays_rate_subcont_final');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting_aktifitas a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_subcont = $this->db->get()->result();

        foreach ($get_budget_subcont as $item) {
            $budget_subcont += ($item->mandays_rate_subcont_final * $item->mandays_subcont_final);
        }

        $this->db->select('SUM(a.total_budget) as budget_subcont_custom');
        $this->db->from(DBCNL . '.kons_tr_kasbon_custom_ovb_subcont a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_subcont_custom = $this->db->get()->row();

        $budget_subcont += $get_budget_subcont_custom->budget_subcont_custom;

        $this->db->select('SUM(a.qty_budget_tambahan * a.budget_tambahan) as ovb_subcont');
        $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_subcont_detail a');
        $this->db->join(DBCNL . '.kons_tr_kasbon_req_ovb_subcont_header b', 'b.id_request_ovb = a.id_request_ovb', 'left');
        $this->db->where('b.tipe', 1);
        $this->db->where('b.sts', 1);
        $this->db->where('b.id_spk_budgeting', $id_spk_budgeting);
        $get_overbudget_subcont = $this->db->get()->row();

        $budget_subcont += $get_overbudget_subcont->ovb_subcont;

        $this->db->select('SUM(a.total_final) as budget_akomodasi');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting_akomodasi a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_akomodasi = $this->db->get()->row();
        $budget_akomodasi = $get_budget_akomodasi->budget_akomodasi;

        $this->db->select('SUM(b.budget_tambahan) as total_ovb_akomodasi');
        $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_akomodasi_header a');
        $this->db->join(DBCNL . '.kons_tr_kasbon_req_ovb_akomodasi_detail b', 'b.id_request_ovb = a.id_request_ovb', 'left');
        $this->db->where('a.tipe', 2);
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_ovb_akomodasi = $this->db->get()->row();
        $budget_akomodasi += $get_budget_ovb_akomodasi->total_ovb_akomodasi;

        $this->db->select('SUM(a.total_final) as budget_others');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting_others a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_others = $this->db->get()->row();
        $budget_others = $get_budget_others->budget_others;

        $this->db->select('SUM(b.budget_tambahan) as total_ovb_others');
        $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_others_header a');
        $this->db->join(DBCNL . '.kons_tr_kasbon_req_ovb_others_detail b', 'b.id_request_ovb = a.id_request_ovb', 'left');
        $this->db->where('a.tipe', 3);
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_ovb_others = $this->db->get()->row();

        $budget_others += $get_budget_ovb_others->total_ovb_others;

        $this->db->select('SUM(a.total_final) as budget_lab');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting_lab a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_lab = $this->db->get()->row();
        $budget_lab = $get_budget_lab->budget_lab;

        $this->db->select('SUM(b.budget_tambahan) as total_ovb_lab');
        $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_lab_header a');
        $this->db->join(DBCNL . '.kons_tr_kasbon_req_ovb_lab_detail b', 'b.id_request_ovb = a.id_request_ovb');
        $this->db->where('a.tipe', 4);
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_ovb_lab = $this->db->get()->row();

        $budget_lab += $get_budget_ovb_lab->total_ovb_lab;

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_subcont a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_kasbon_subcont = $this->db->get()->result();

        $nilai_kasbon_aktual = 0;
        foreach ($get_kasbon_subcont as $item) {
            $nilai_kasbon_aktual += $item->total_pengajuan;
        }

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_akomodasi a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_kasbon_akomodasi = $this->db->get()->result();

        $nilai_kasbon_aktual_akomodasi = 0;
        foreach ($get_kasbon_akomodasi as $item) {
            $nilai_kasbon_aktual_akomodasi += $item->total_pengajuan;
        }

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_others a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_kasbon_others = $this->db->get()->result();

        $nilai_kasbon_aktual_others = 0;
        foreach ($get_kasbon_others as $item) {
            $nilai_kasbon_aktual_others += $item->total_pengajuan;
        }

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_lab a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_kasbon_lab = $this->db->get()->result();

        $nilai_kasbon_aktual_lab = 0;
        foreach ($get_kasbon_lab as $item) {
            $nilai_kasbon_aktual_lab += $item->total_pengajuan;
        }

        $data = [
            'id_spk_budgeting' => $id_spk_budgeting,
            'list_budgeting' => $get_budgeting,
            'budget_subcont' => $budget_subcont,
            'budget_akomodasi' => $budget_akomodasi,
            'budget_others' => $budget_others,
            'budget_lab' => $budget_lab,
            'list_kasbon_subcont' => $get_kasbon_subcont,
            'nilai_kasbon_aktual' => $nilai_kasbon_aktual,
            'nilai_kasbon_aktual_akomodasi' => $nilai_kasbon_aktual_akomodasi,
            'nilai_kasbon_aktual_others' => $nilai_kasbon_aktual_others,
            'nilai_kasbon_aktual_lab' => $nilai_kasbon_aktual_lab
        ];

        $this->template->set($data);
        $this->template->title('Pengajuan');
        $this->template->render('add');
    }

    public function view_kasbon($id_spk_budgeting)
    {
        $id_spk_budgeting = urldecode($id_spk_budgeting);
        $id_spk_budgeting = str_replace('|', '/', $id_spk_budgeting);

        $this->db->select('a.*, b.nm_sales, b.waktu_from, b.waktu_to, c.nm_paket');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting a');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran b', 'b.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->join(DBCNL . '.kons_master_konsultasi_header c', 'c.id_konsultasi_h = a.id_project', 'left');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budgeting = $this->db->get()->row();

        $budget_subcont = 0;
        $this->db->select('a.mandays_subcont_final, a.mandays_rate_subcont_final');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting_aktifitas a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_subcont = $this->db->get()->result();

        foreach ($get_budget_subcont as $item) {
            $budget_subcont += ($item->mandays_rate_subcont_final * $item->mandays_subcont_final);
        }

        $this->db->select('SUM(a.total_final) as budget_akomodasi');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting_akomodasi a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_akomodasi = $this->db->get()->row();
        $budget_akomodasi = $get_budget_akomodasi->budget_akomodasi;

        $this->db->select('SUM(b.budget_tambahan) as total_ovb_akomodasi');
        $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_akomodasi_header a');
        $this->db->join(DBCNL . '.kons_tr_kasbon_req_ovb_akomodasi_detail b', 'b.id_request_ovb = a.id_request_ovb', 'left');
        $this->db->where('a.sts', 1);
        $this->db->where('a.tipe', 2);
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_ovb_akomodasi = $this->db->get()->row();
        $budget_akomodasi += $get_budget_ovb_akomodasi->total_ovb_akomodasi;

        $this->db->select('SUM(a.total_final) as budget_others');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting_others a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_others = $this->db->get()->row();
        $budget_others = $get_budget_others->budget_others;

        $this->db->select('SUM(a.total_final) as budget_lab');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting_lab a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_lab = $this->db->get()->row();
        $budget_lab = $get_budget_lab->budget_lab;

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_subcont a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_kasbon_subcont = $this->db->get()->result();

        $nilai_kasbon_on_proses = 0;
        foreach ($get_kasbon_subcont as $item) {
            if ($item->sts !== '1') {
                $nilai_kasbon_on_proses += $item->total_pengajuan;
            }
        }

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_akomodasi a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_kasbon_akomodasi = $this->db->get()->result();

        $nilai_kasbon_on_proses_akomodasi = 0;
        foreach ($get_kasbon_akomodasi as $item) {
            if ($item->sts !== '1') {
                $nilai_kasbon_on_proses_akomodasi += $item->total_pengajuan;
            }
        }

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_others a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_kasbon_others = $this->db->get()->result();

        $nilai_kasbon_on_proses_others = 0;
        foreach ($get_kasbon_others as $item) {
            if ($item->sts !== '1') {
                $nilai_kasbon_on_proses_others += $item->total_pengajuan;
            }
        }

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_lab a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_kasbon_lab = $this->db->get()->result();

        $nilai_kasbon_on_proses_lab = 0;
        foreach ($get_kasbon_lab as $item) {
            if ($item->sts !== '1') {
                $nilai_kasbon_on_proses_lab += $item->total_pengajuan;
            }
        }


        $data = [
            'id_spk_budgeting' => $id_spk_budgeting,
            'list_budgeting' => $get_budgeting,
            'budget_subcont' => $budget_subcont,
            'budget_akomodasi' => $budget_akomodasi,
            'budget_others' => $budget_others,
            'budget_lab' => $budget_lab,
            'list_kasbon_subcont' => $get_kasbon_subcont,
            'nilai_kasbon_on_proses' => $nilai_kasbon_on_proses,
            'nilai_kasbon_on_proses_akomodasi' => $nilai_kasbon_on_proses_akomodasi,
            'nilai_kasbon_on_proses_others' => $nilai_kasbon_on_proses_others,
            'nilai_kasbon_on_proses_lab' => $nilai_kasbon_on_proses_lab
        ];

        $this->template->set($data);
        $this->template->render('view');
    }

    public function view_request_budget_akomodasi($id_request_ovb)
    {
        $id_request_ovb = urldecode($id_request_ovb);
        $id_request_ovb = str_replace('|', '/', $id_request_ovb);

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_akomodasi_header a');
        $this->db->where('a.id_request_ovb', $id_request_ovb);
        $get_data_ovb_header = $this->db->get()->row_array();

        $this->db->select('a.*, b.nm_sales, b.waktu_from, b.waktu_to');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting a');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran b', 'b.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->where('a.id_spk_budgeting', $get_data_ovb_header['id_spk_budgeting']);
        $get_budgeting = $this->db->get()->row();

        $this->db->select('a.*, b.nm_biaya');
        $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_akomodasi_detail a');
        $this->db->join(DBCNL . '.kons_master_biaya b', 'b.id = a.id_item', 'left');
        $this->db->where('a.id_request_ovb', $id_request_ovb);
        $get_data_ovb = $this->db->get()->result_array();

        $data = [
            'id_spk_budgeting' => $get_data_ovb_header['id_spk_budgeting'],
            'list_budgeting' => $get_budgeting,
            'list_data_ovb' => $get_data_ovb
        ];

        $this->template->set($data);
        $this->template->render('view_request_budget_akomodasi');
    }

    public function view_request_budget_subcont($id_request_ovb)
    {
        $id_request_ovb = urldecode($id_request_ovb);
        $id_request_ovb = str_replace('|', '/', $id_request_ovb);

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_subcont_header a');
        $this->db->where('a.id_request_ovb', $id_request_ovb);
        $get_data_ovb_header = $this->db->get()->row_array();

        $this->db->select('a.*, b.nm_sales, b.waktu_from, b.waktu_to');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting a');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran b', 'b.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->where('a.id_spk_budgeting', $get_data_ovb_header['id_spk_budgeting']);
        $get_budgeting = $this->db->get()->row();

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_subcont_detail a');
        $this->db->where('a.id_request_ovb', $id_request_ovb);
        $get_data_ovb = $this->db->get()->result_array();

        $data = [
            'id_spk_budgeting' => $get_data_ovb_header['id_spk_budgeting'],
            'list_budgeting' => $get_budgeting,
            'list_data_ovb' => $get_data_ovb
        ];

        $this->template->set($data);
        $this->template->render('view_request_budget_subcont');
    }

    public function view_request_budget_others($id_request_ovb)
    {
        $id_request_ovb = urldecode($id_request_ovb);
        $id_request_ovb = str_replace('|', '/', $id_request_ovb);

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_others_header a');
        $this->db->where('a.id_request_ovb', $id_request_ovb);
        $get_data_ovb_header = $this->db->get()->row_array();

        $this->db->select('a.*, b.nm_sales, b.waktu_from, b.waktu_to');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting a');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran b', 'b.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->where('a.id_spk_budgeting', $get_data_ovb_header['id_spk_budgeting']);
        $get_budgeting = $this->db->get()->row();

        $this->db->select('a.*, b.nm_biaya');
        $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_others_detail a');
        $this->db->join(DBCNL . '.kons_master_biaya b', 'b.id = a.id_item', 'left');
        $this->db->where('a.id_request_ovb', $id_request_ovb);
        $get_data_ovb = $this->db->get()->result_array();

        $data = [
            'id_spk_budgeting' => $get_data_ovb_header['id_spk_budgeting'],
            'list_budgeting' => $get_budgeting,
            'list_data_ovb' => $get_data_ovb
        ];

        $this->template->set($data);
        $this->template->render('view_request_budget_others');
    }

    public function view_request_budget_lab($id_request_ovb)
    {
        $id_request_ovb = urldecode($id_request_ovb);
        $id_request_ovb = str_replace('|', '/', $id_request_ovb);

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_lab_header a');
        $this->db->where('a.id_request_ovb', $id_request_ovb);
        $get_data_ovb_header = $this->db->get()->row_array();

        $this->db->select('a.*, b.nm_sales, b.waktu_from, b.waktu_to');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting a');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran b', 'b.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->where('a.id_spk_budgeting', $get_data_ovb_header['id_spk_budgeting']);
        $get_budgeting = $this->db->get()->row();

        $this->db->select('a.*, b.nm_biaya');
        $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_lab_detail a');
        $this->db->join(DBCNL . '.kons_master_biaya b', 'b.id = a.id_item', 'left');
        $this->db->where('a.id_request_ovb', $id_request_ovb);
        $get_data_ovb = $this->db->get()->result_array();

        $data = [
            'id_spk_budgeting' => $get_data_ovb_header['id_spk_budgeting'],
            'list_budgeting' => $get_budgeting,
            'list_data_ovb' => $get_data_ovb
        ];

        $this->template->set($data);
        $this->template->render('view_request_budget_lab');
    }

    public function add_kasbon_subcont($id_spk_budgeting)
    {
        $id_spk_budgeting = urldecode($id_spk_budgeting);
        $id_spk_budgeting = str_replace('|', '/', $id_spk_budgeting);

        $this->db->select('a.*, b.nm_sales, b.waktu_from, b.waktu_to');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting a');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran b', 'b.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budgeting = $this->db->get()->row();

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting_aktifitas a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->where('a.mandays_rate_subcont_final >', 0);
        $get_data_subcont = $this->db->get()->result();

        $this->db->select('a.id_aktifitas, SUM(a.qty_pengajuan) as ttl_qty_pengajuan, SUM(a.total_pengajuan) as ttl_total_pengajuan');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_subcont a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->group_by('a.id_aktifitas');
        $get_kasbon_subcont = $this->db->get()->result();

        $data_kasbon_subcont = [];
        foreach ($get_kasbon_subcont as $item) :
            $data_kasbon_subcont[$item->id_aktifitas] = [
                'ttl_qty_pengajuan' => $item->ttl_qty_pengajuan,
                'ttl_total_pengajuan' => $item->ttl_total_pengajuan
            ];
        endforeach;

        $data_overbudget_subcont = [];

        $this->db->select('a.id_aktifitas, a.qty_budget_tambahan, a.budget_tambahan, a.pengajuan_budget');
        $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_subcont_detail a');
        $this->db->join(DBCNL . '.kons_tr_kasbon_req_ovb_subcont_header b', 'b.id_request_ovb = a.id_request_ovb', 'left');
        $this->db->where('b.id_spk_budgeting', $id_spk_budgeting);
        $this->db->where('b.sts', '1');
        $get_ovb_subcont = $this->db->get()->result();

        foreach ($get_ovb_subcont as $item_ovb_subcont) :
            $data_overbudget_subcont[$item_ovb_subcont->id_aktifitas] = [
                'qty_budget_tambahan' => $item_ovb_subcont->qty_budget_tambahan,
                'budget_tambahan' => $item_ovb_subcont->budget_tambahan,
                'pengajuan_budget' => $item_ovb_subcont->pengajuan_budget
            ];
        endforeach;

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_custom_ovb_subcont a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_ovb_subcont_custom = $this->db->get()->result();
        // print_r($data_kasbon_subcont);
        // exit;

        $data_kasbon_custom = [];

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_subcont a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->where('a.custom_subcont', '1');
        $get_kasbon_custom = $this->db->get()->result();

        foreach ($get_kasbon_custom as $item) :
            $data_kasbon_custom[$item->id_aktifitas] = [
                'id_spk_budgeting' => $item->id_spk_budgeting,
                'id_spk_penawaran' => $item->id_spk_penawaran,
                'id_penawaran' => $item->id_penawaran,
                'qty_pengajuan' => $item->qty_pengajuan,
                'total_pengajuan' => $item->total_pengajuan
            ];
        endforeach;

        $data = [
            'id_spk_budgeting' => $id_spk_budgeting,
            'list_budgeting' => $get_budgeting,
            'list_subcont' => $get_data_subcont,
            'data_kasbon_subcont' => $data_kasbon_subcont,
            'data_overbudget_subcont' => $data_overbudget_subcont,
            'data_ovb_subcont_custom' => $get_ovb_subcont_custom,
            'data_kasbon_custom' => $data_kasbon_custom
        ];

        $this->template->set($data);
        $this->template->title('Pengajuan Subcont');
        $this->template->render('add_kasbon_subcont');
    }

    public function edit_kasbon_subcont($id_kasbon_subcont)
    {
        $id_kasbon_subcont = urldecode($id_kasbon_subcont);
        $id_kasbon_subcont = str_replace('|', '/', $id_kasbon_subcont);

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_header a');
        $this->db->where('a.id', $id_kasbon_subcont);
        $get_kasbon_subcont = $this->db->get()->row();

        $this->db->select('a.*, b.nm_sales, b.waktu_from, b.waktu_to');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting a');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran b', 'b.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->where('a.id_spk_budgeting', $get_kasbon_subcont->id_spk_budgeting);
        $get_budgeting = $this->db->get()->row();

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting_aktifitas a');
        $this->db->where('a.id_spk_budgeting', $get_kasbon_subcont->id_spk_budgeting);
        $this->db->where('a.mandays_rate_subcont_final >', 0);
        $get_data_subcont = $this->db->get()->result();

        $this->db->select('a.id_aktifitas, a.qty_pengajuan, a.nominal_pengajuan, a.total_pengajuan, a.aktual_terpakai, a.sisa_budget, SUM(a.qty_pengajuan) as ttl_qty_pengajuan, SUM(a.total_pengajuan) as ttl_total_pengajuan, a.qty_estimasi, a.price_unit_estimasi, a.total_budget_estimasi, a.qty_terpakai, a.nominal_terpakai, a.total_terpakai, a.qty_overbudget, a.nominal_overbudget, a.total_overbudget');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_subcont a');
        $this->db->where('a.id_spk_budgeting', $get_kasbon_subcont->id_spk_budgeting);
        $this->db->group_by('a.id_aktifitas');
        $get_kasbon_subcont2 = $this->db->get()->result();

        $data_kasbon_subcont = [];
        foreach ($get_kasbon_subcont2 as $item) {
            $data_kasbon_subcont[$item->id_aktifitas] = [
                'ttl_qty_pengajuan' => $item->ttl_qty_pengajuan,
                'ttl_total_pengajuan' => $item->ttl_total_pengajuan,
                'qty_pengajuan' => $item->qty_pengajuan,
                'nominal_pengajuan' => $item->nominal_pengajuan,
                'total_pengajuan' => $item->total_pengajuan,
                'qty_estimasi' => $item->qty_estimasi,
                'nominal_estimasi' => $item->price_unit_estimasi,
                'total_estimasi' => $item->total_budget_estimasi,
                'aktual_terpakai' => $item->aktual_terpakai,
                'sisa_budget' => $item->sisa_budget,
                'qty_terpakai' => $item->qty_terpakai,
                'nominal_terpakai' => $item->nominal_terpakai,
                'total_terpakai' => $item->total_terpakai,
                'qty_overbudget' => $item->qty_overbudget,
                'nominal_overbudget' => $item->nominal_overbudget,
                'total_overbudget' => $item->total_overbudget
            ];
        }

        $this->db->select('a.id_header, a.id_spk_budgeting, a.id_spk_penawaran, a.id_penawaran, a.id_aktifitas, a.nm_aktifitas, a.qty_pengajuan, a.nominal_pengajuan, a.total_pengajuan, a.qty_estimasi, a.price_unit_estimasi, a.total_budget_estimasi, a.aktual_terpakai, a.sisa_budget');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_subcont a');
        $this->db->where('a.id_header', $id_kasbon_subcont);
        $this->db->where('a.custom_subcont', '1');
        $get_data_subcont_custom = $this->db->get()->result();

        $data = [
            'id_spk_budgeting' => $get_kasbon_subcont->id_spk_budgeting,
            'list_budgeting' => $get_budgeting,
            'list_subcont' => $get_data_subcont,
            'data_kasbon_subcont' => $data_kasbon_subcont,
            'data_kasbon_subcont2' => $get_kasbon_subcont2,
            'data_kasbon_subcont_custom' => $get_data_subcont_custom,
            'header' => $get_kasbon_subcont
        ];

        $this->template->set($data);
        $this->template->title('Pengajuan Subcont');
        $this->template->render('edit_kasbon_subcont');
    }

    public function view_kasbon_subcont($id_header)
    {
        $id_header = urldecode($id_header);
        $id_header = str_replace('|', '/', $id_header);

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_header a');
        $this->db->where('a.id', $id_header);
        $get_header = $this->db->get()->row();

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting_aktifitas a');
        $this->db->where('a.id_spk_budgeting', $get_header->id_spk_budgeting);
        $this->db->where('a.mandays_rate_subcont_final >', 0);
        $get_data_subcont = $this->db->get()->result();

        $this->db->select('a.*, b.nm_sales, b.waktu_from, b.waktu_to');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting a');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran b', 'b.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->where('a.id_spk_budgeting', $get_header->id_spk_budgeting);
        $get_budgeting = $this->db->get()->row();

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_subcont a');
        $this->db->where('a.id_header', $id_header);
        $this->db->where('a.custom_subcont', '0');
        $get_kasbon_subcont = $this->db->get()->result();

        // print_r($this->db->last_query());
        // exit;

        $data_list_kasbon_subcont = [];

        foreach ($get_kasbon_subcont as $item) {
            $data_list_kasbon_subcont[$item->id_aktifitas] = [
                'nm_aktifitas' => $item->nm_aktifitas,
                'nominal_pengajuan' => $item->nominal_pengajuan,
                'qty_pengajuan' => $item->qty_pengajuan,
                'total_pengajuan' => $item->total_pengajuan,
                'qty_estimasi' => $item->qty_estimasi,
                'price_unit_estimasi' => $item->price_unit_estimasi,
                'total_budgeting_estimasi' => $item->total_budget_estimasi,
                'aktual_terpakai' => $item->aktual_terpakai,
                'sisa_budget' => $item->sisa_budget,
                'qty_terpakai' => $item->qty_terpakai,
                'nominal_terpakai' => $item->nominal_terpakai,
                'total_terpakai' => $item->total_terpakai,
                'qty_overbudget' => $item->qty_overbudget,
                'nominal_overbudget' => $item->nominal_overbudget,
                'total_overbudget' => $item->total_overbudget
            ];
        }

        $this->db->select('a.id, a.id_header, a.id_spk_budgeting, a.id_spk_penawaran, a.id_penawaran, a.qty_pengajuan, a.nominal_pengajuan, a.total_pengajuan, a.qty_estimasi, a.price_unit_estimasi, a.total_budget_estimasi, a.aktual_terpakai, a.sisa_budget, a.nm_aktifitas, a.id_aktifitas');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_subcont a');
        $this->db->where('a.id_header', $id_header);
        $this->db->where('a.custom_subcont', '1');
        $get_data_subcont_custom = $this->db->get()->result();

        $data = [
            'header' => $get_header,
            'list_budgeting' => $get_budgeting,
            'list_data_kasbon' => $get_data_subcont,
            'data_list_kasbon_subcont' => $data_list_kasbon_subcont,
            'data_list_kasbon_subcont_custom' => $get_data_subcont_custom
        ];

        $this->template->set($data);
        $this->template->title('Pengajuan Subcont');
        $this->template->render('view_kasbon_subcont');
    }

    public function add_kasbon_akomodasi($id_spk_budgeting)
    {
        $id_spk_budgeting = urldecode($id_spk_budgeting);
        $id_spk_budgeting = str_replace('|', '/', $id_spk_budgeting);

        $this->db->select('a.*, b.nm_sales, b.waktu_from, b.waktu_to');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting a');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran b', 'b.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budgeting = $this->db->get()->row();

        $this->db->select('a.*, b.nm_biaya');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting_akomodasi a');
        $this->db->join(DBCNL . '.kons_master_biaya b', 'b.id = a.id_item', 'left');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_data_akomodasi = $this->db->get()->result();

        $this->db->select('a.id_akomodasi ,SUM(a.qty_pengajuan) as ttl_qty_pengajuan, SUM(a.total_pengajuan) as ttl_total_pengajuan');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_akomodasi a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->group_by('a.id_akomodasi');
        $get_kasbon_akomodasi = $this->db->get()->result();

        $data_kasbon_akomodasi = [];
        foreach ($get_kasbon_akomodasi as $item) {
            $data_kasbon_akomodasi[$item->id_akomodasi] = [
                'ttl_qty_pengajuan' => $item->ttl_qty_pengajuan,
                'ttl_total_pengajuan' => $item->ttl_total_pengajuan
            ];
        }

        $this->db->select('b.id_detail ,b.id_item, SUM(b.budget_tambahan) as total_budget_tambahan, SUM(b.qty_budget_tambahan) as ttl_qty_tambahan');
        $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_akomodasi_header a');
        $this->db->join(DBCNL . '.kons_tr_kasbon_req_ovb_akomodasi_detail b', 'b.id_request_ovb = a.id_request_ovb');
        $this->db->where('a.tipe', 2);
        $this->db->where('a.sts', 1);
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->group_by('b.id');
        $get_ovb_akomodasi = $this->db->get()->result_array();

        $data_ovb_akomodasi = [];
        foreach ($get_ovb_akomodasi as $item) {
            $data_ovb_akomodasi[$item['id_detail']] = [
                'total_budget_tambahan' => $item['total_budget_tambahan'],
                'ttl_qty_tambahan' => $item['ttl_qty_tambahan']
            ];
        }

        $data = [
            'id_spk_budgeting' => $id_spk_budgeting,
            'list_budgeting' => $get_budgeting,
            'list_akomodasi' => $get_data_akomodasi,
            'data_kasbon_akomodasi' => $data_kasbon_akomodasi,
            'data_ovb_akomodasi' => $data_ovb_akomodasi
        ];

        $this->template->set($data);
        $this->template->title('Pengajuan Akomodasi');
        $this->template->render('add_kasbon_akomodasi');
    }

    public function view_kasbon_akomodasi($id_header)
    {
        $id_header = urldecode($id_header);
        $id_header = str_replace('|', '/', $id_header);

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_header a');
        $this->db->where('a.id', $id_header);
        $get_header = $this->db->get()->row();

        $this->db->select('a.*, b.nm_biaya');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting_akomodasi a');
        $this->db->join(DBCNL . '.kons_master_biaya b', 'b.id = a.id_item', 'left');
        $this->db->where('a.id_spk_budgeting', $get_header->id_spk_budgeting);
        $get_data_akomodasi = $this->db->get()->result();

        $this->db->select('a.*, b.nm_sales, b.waktu_from, b.waktu_to');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting a');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran b', 'b.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->where('a.id_spk_budgeting', $get_header->id_spk_budgeting);
        $get_budgeting = $this->db->get()->row();

        $data_budget_tambahan = [];

        $this->db->select('a.id, a.id_item, SUM(a.budget_tambahan) as ttl_budget_tambahan, SUM(a.qty_budget_tambahan) as ttl_qty_budget_tambahan');
        $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_akomodasi_detail a');
        $this->db->join(DBCNL . '.kons_tr_kasbon_req_ovb_akomodasi_header b', 'b.id_request_ovb = a.id_request_ovb');
        $this->db->where('b.id_spk_budgeting', $get_header->id_spk_budgeting);
        $this->db->where('b.sts', '1');
        $this->db->group_by('a.id');
        $get_data_ovb = $this->db->get()->result();

        foreach ($get_data_ovb as $item) {
            $data_budget_tambahan[$item->id] = [
                'budget_tambahan' => $item->ttl_budget_tambahan,
                'qty_budget_tambahan' => $item->ttl_qty_budget_tambahan
            ];
        }

        $this->db->select('a.*, b.nm_biaya');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_akomodasi a');
        $this->db->join(DBCNL . '.kons_master_biaya b', 'b.id = a.id_item', 'left');
        $this->db->where('a.id_header', $id_header);
        $get_kasbon_akomodasi = $this->db->get()->result();

        // print_r($this->db->last_query());
        // exit;

        $data_list_kasbon_akomodasi = [];

        foreach ($get_kasbon_akomodasi as $item) {
            $data_list_kasbon_akomodasi[$item->id] = [
                'nm_biaya' => $item->nm_biaya,
                'nominal_pengajuan' => $item->nominal_pengajuan,
                'qty_pengajuan' => $item->qty_pengajuan,
                'total_pengajuan' => $item->total_pengajuan,
                'qty_estimasi' => $item->qty_estimasi,
                'price_unit_estimasi' => $item->price_unit_estimasi,
                'total_budgeting_estimasi' => $item->total_budget_estimasi,
                'qty_budget_tambahan' => $item->qty_budget_tambahan,
                'budget_tambahan' => $item->budget_tambahan,
                'aktual_terpakai' => $item->aktual_terpakai,
                'sisa_budget' => $item->sisa_budget
            ];
        }

        $data = [
            'header' => $get_header,
            'list_budgeting' => $get_budgeting,
            'list_data_kasbon' => $get_kasbon_akomodasi,
            'list_budget_tambahan' => $data_budget_tambahan,
            'data_list_kasbon_akomodasi' => $data_list_kasbon_akomodasi
        ];

        $this->template->set($data);
        $this->template->title('Pengajuan Akomodasi');
        $this->template->render('view_kasbon_akomodasi');
    }

    public function edit_kasbon_akomodasi($id_header)
    {
        $id_header = urldecode($id_header);
        $id_header = str_replace('|', '/', $id_header);

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_header a');
        $this->db->where('a.id', $id_header);
        $get_header = $this->db->get()->row();

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_akomodasi a');
        $this->db->where('a.id_header', $id_header);
        $get_data_akomodasi = $this->db->get()->result();

        $this->db->select('a.*, b.nm_sales, b.waktu_from, b.waktu_to');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting a');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran b', 'b.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->where('a.id_spk_budgeting', $get_header->id_spk_budgeting);
        $get_budgeting = $this->db->get()->row();

        $data_budget_tambahan = [];

        $this->db->select('a.id, a.id_item, SUM(a.budget_tambahan) as ttl_budget_tambahan, SUM(a.qty_budget_tambahan) as ttl_qty_budget_tambahan');
        $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_akomodasi_detail a');
        $this->db->join(DBCNL . '.kons_tr_kasbon_req_ovb_akomodasi_header b', 'b.id_request_ovb = a.id_request_ovb');
        $this->db->where('b.id_spk_budgeting', $get_header->id_spk_budgeting);
        $this->db->where('b.sts', '1');
        $this->db->group_by('a.id_item');
        $get_data_ovb = $this->db->get()->result();

        foreach ($get_data_ovb as $item) {
            $data_budget_tambahan[$item->id] = [
                'budget_tambahan' => $item->ttl_budget_tambahan,
                'qty_budget_tambahan' => $item->ttl_qty_budget_tambahan
            ];
        }

        $this->db->select('a.*, b.nm_biaya');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_akomodasi a');
        $this->db->join(DBCNL . '.kons_master_biaya b', 'b.id = a.id_item', 'left');
        $this->db->where('a.id_header', $id_header);
        $get_kasbon_akomodasi = $this->db->get()->result();

        // print_r($this->db->last_query());
        // exit;

        $data_list_kasbon_akomodasi = [];

        foreach ($get_kasbon_akomodasi as $item) {
            $data_list_kasbon_akomodasi[$item->id] = [
                'nm_biaya' => $item->nm_biaya,
                'nominal_pengajuan' => $item->nominal_pengajuan,
                'qty_pengajuan' => $item->qty_pengajuan,
                'total_pengajuan' => $item->total_pengajuan,
                'qty_estimasi' => $item->qty_estimasi,
                'price_unit_estimasi' => $item->price_unit_estimasi,
                'total_budgeting_estimasi' => $item->total_budget_estimasi,
                'qty_budget_tambahan' => $item->qty_budget_tambahan,
                'budget_tambahan' => $item->budget_tambahan,
                'aktual_terpakai' => $item->aktual_terpakai,
                'sisa_budget' => $item->sisa_budget
            ];
        }

        $data = [
            'header' => $get_header,
            'list_budgeting' => $get_budgeting,
            'list_data_kasbon' => $get_data_akomodasi,
            'list_budget_tambahan' => $data_budget_tambahan,
            'data_list_kasbon_akomodasi' => $data_list_kasbon_akomodasi
        ];

        $this->template->set($data);
        $this->template->title('Pengajuan Akomodasi');
        $this->template->render('edit_kasbon_akomodasi');
    }

    public function add_kasbon_others($id_spk_budgeting)
    {
        $id_spk_budgeting = urldecode($id_spk_budgeting);
        $id_spk_budgeting = str_replace('|', '/', $id_spk_budgeting);

        $this->db->select('a.*, b.nm_sales, b.waktu_from, b.waktu_to');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting a');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran b', 'b.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budgeting = $this->db->get()->row();

        $this->db->select('a.*, b.nm_biaya');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting_others a');
        $this->db->join(DBCNL . '.kons_master_biaya b', 'b.id = a.id_item', 'left');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_data_others = $this->db->get()->result();

        $this->db->select('a.id_others ,SUM(a.qty_pengajuan) as ttl_qty_pengajuan, SUM(a.total_pengajuan) as ttl_total_pengajuan');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_others a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->group_by('a.id_others');
        $get_kasbon_others = $this->db->get()->result();

        $data_kasbon_others = [];
        foreach ($get_kasbon_others as $item) {
            $data_kasbon_others[$item->id_others] = [
                'ttl_qty_pengajuan' => $item->ttl_qty_pengajuan,
                'ttl_total_pengajuan' => $item->ttl_total_pengajuan
            ];
        }

        $this->db->select('a.id_item, a.qty_budget_tambahan, a.budget_tambahan, a.pengajuan_budget, c.id_others');
        $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_others_detail a');
        $this->db->join(DBCNL . '.kons_tr_kasbon_req_ovb_others_header b', 'b.id_request_ovb = a.id_request_ovb', 'left');
        $this->db->join(DBCNL . '.kons_tr_spk_budgeting_others c', 'c.id = a.id_detail', 'left');
        $this->db->where('b.id_spk_budgeting', $id_spk_budgeting);
        $this->db->where('b.sts', '1');
        $get_ovb_others = $this->db->get()->result();

        $data_overbudget_others = [];
        foreach ($get_ovb_others as $item_ovb_others) :
            $data_overbudget_others[$item_ovb_others->id_others] = [
                'qty_budget_tambahan' => $item_ovb_others->qty_budget_tambahan,
                'budget_tambahan' => $item_ovb_others->budget_tambahan,
                'pengajuan_budget' => $item_ovb_others->pengajuan_budget
            ];
        endforeach;


        $data = [
            'id_spk_budgeting' => $id_spk_budgeting,
            'list_budgeting' => $get_budgeting,
            'list_others' => $get_data_others,
            'data_kasbon_others' => $data_kasbon_others,
            'data_overbudget_others' => $data_overbudget_others
        ];

        $this->template->set($data);
        $this->template->title('Pengajuan Others');
        $this->template->render('add_kasbon_others');
    }

    public function add_kasbon_lab($id_spk_budgeting)
    {
        $id_spk_budgeting = urldecode($id_spk_budgeting);
        $id_spk_budgeting = str_replace('|', '/', $id_spk_budgeting);

        $this->db->select('a.*, b.nm_sales, b.waktu_from, b.waktu_to');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting a');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran b', 'b.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budgeting = $this->db->get()->row();

        $this->db->select('a.*, b.isu_lingkungan as nm_biaya');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting_lab a');
        $this->db->join(DBCNL . '.kons_master_lab b', 'b.id = a.id_item', 'left');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_data_lab = $this->db->get()->result();
        // print_r($this->db->last_query());
        // exit;

        $this->db->select('a.id_lab ,SUM(a.qty_pengajuan) as ttl_qty_pengajuan, SUM(a.total_pengajuan) as ttl_total_pengajuan');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_lab a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->group_by('a.id_lab');
        $get_kasbon_lab = $this->db->get()->result();

        $data_kasbon_lab = [];
        foreach ($get_kasbon_lab as $item) {
            $data_kasbon_lab[$item->id_lab] = [
                'ttl_qty_pengajuan' => $item->ttl_qty_pengajuan,
                'ttl_total_pengajuan' => $item->ttl_total_pengajuan
            ];
        }

        $this->db->select('a.id_item, a.qty_budget_tambahan, a.budget_tambahan, a.pengajuan_budget, c.id_lab');
        $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_lab_detail a');
        $this->db->join(DBCNL . '.kons_tr_kasbon_req_ovb_lab_header b', 'b.id_request_ovb = a.id_request_ovb', 'left');
        $this->db->join(DBCNL . '.kons_tr_spk_budgeting_lab c', 'c.id = a.id_detail', 'left');
        $this->db->where('b.id_spk_budgeting', $id_spk_budgeting);
        $this->db->where('b.sts', '1');
        $get_ovb_lab = $this->db->get()->result();

        $data_overbudget_lab = [];
        foreach ($get_ovb_lab as $item_ovb_lab) :
            $data_overbudget_lab[$item_ovb_lab->id_lab] = [
                'qty_budget_tambahan' => $item_ovb_lab->qty_budget_tambahan,
                'budget_tambahan' => $item_ovb_lab->budget_tambahan,
                'pengajuan_budget' => $item_ovb_lab->pengajuan_budget
            ];
        endforeach;


        $data = [
            'id_spk_budgeting' => $id_spk_budgeting,
            'list_budgeting' => $get_budgeting,
            'list_lab' => $get_data_lab,
            'data_kasbon_lab' => $data_kasbon_lab,
            'data_overbudget_lab' => $data_overbudget_lab
        ];

        $this->template->set($data);
        $this->template->title('Pengajuan Others');
        $this->template->render('add_kasbon_lab');
    }

    public function view_kasbon_others($id_header)
    {
        $id_header = urldecode($id_header);
        $id_header = str_replace('|', '/', $id_header);

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_header a');
        $this->db->where('a.id', $id_header);
        $get_header = $this->db->get()->row();

        $this->db->select('a.*, b.nm_sales, b.waktu_from, b.waktu_to');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting a');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran b', 'b.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->where('a.id_spk_budgeting', $get_header->id_spk_budgeting);
        $get_budgeting = $this->db->get()->row();

        $this->db->select('a.*, b.nm_biaya');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting_others a');
        $this->db->join(DBCNL . '.kons_master_biaya b', 'b.id = a.id_item', 'left');
        $this->db->where('a.id_spk_budgeting', $get_header->id_spk_budgeting);
        $get_data_others = $this->db->get()->result();

        $this->db->select('a.*, b.nm_biaya');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_others a');
        $this->db->join(DBCNL . '.kons_master_biaya b', 'b.id = a.id_item', 'left');
        $this->db->where('a.id_header', $id_header);
        $this->db->group_by('a.id_item');
        $get_data_kasbon = $this->db->get()->result();

        $list_arr_kasbon = [];
        foreach ($get_data_kasbon as $item) {
            $list_arr_kasbon[$item->id_item] = [
                'qty_pengajuan' => $item->qty_pengajuan,
                'nominal_pengajuan' => $item->nominal_pengajuan,
                'total_pengajuan' => $item->total_pengajuan,
                'aktual_terpakai' => $item->aktual_terpakai,
                'sisa_budget' => $item->sisa_budget,
                'qty_terpakai' => $item->qty_terpakai,
                'nominal_terpakai' => $item->nominal_terpakai,
                'total_terpakai' => $item->total_terpakai,
                'qty_overbudget' => $item->qty_overbudget,
                'nominal_overbudget' => $item->nominal_overbudget,
                'total_overbudget' => $item->total_overbudget
            ];
        }

        $data = [
            'header' => $get_header,
            'list_budgeting' => $get_budgeting,
            'list_data_kasbon' => $get_data_kasbon,
            'list_data_others' => $get_data_others,
            'list_arr_kasbon' => $list_arr_kasbon
        ];

        $this->template->set($data);
        $this->template->title('Pengajuan Others');
        $this->template->render('view_kasbon_others');
    }

    public function view_kasbon_lab($id_header)
    {
        $id_header = urldecode($id_header);
        $id_header = str_replace('|', '/', $id_header);

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_header a');
        $this->db->where('a.id', $id_header);
        $get_header = $this->db->get()->row();

        $this->db->select('a.*, b.nm_sales, b.waktu_from, b.waktu_to');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting a');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran b', 'b.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->where('a.id_spk_budgeting', $get_header->id_spk_budgeting);
        $get_budgeting = $this->db->get()->row();

        $this->db->select('a.*, b.isu_lingkungan as nm_biaya');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting_lab a');
        $this->db->join(DBCNL . '.kons_master_lab b', 'b.id = a.id_item', 'left');
        $this->db->where('a.id_spk_budgeting', $get_header->id_spk_budgeting);
        $get_data_lab = $this->db->get()->result();

        $this->db->select('a.*, b.nm_biaya');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_lab a');
        $this->db->join(DBCNL . '.kons_master_biaya b', 'b.id = a.id_item', 'left');
        $this->db->where('a.id_header', $id_header);
        $this->db->group_by('a.id_item');
        $get_data_kasbon = $this->db->get()->result();

        $list_arr_kasbon = [];
        foreach ($get_data_kasbon as $item) {
            $list_arr_kasbon[$item->id_lab] = [
                'qty_pengajuan' => $item->qty_pengajuan,
                'nominal_pengajuan' => $item->nominal_pengajuan,
                'total_pengajuan' => $item->total_pengajuan,
                'aktual_terpakai' => $item->aktual_terpakai,
                'sisa_budget' => $item->sisa_budget,
                'qty_terpakai' => $item->qty_terpakai,
                'nominal_terpakai' => $item->nominal_terpakai,
                'total_terpakai' => $item->total_terpakai,
                'qty_overbudget' => $item->qty_overbudget,
                'nominal_overbudget' => $item->nominal_overbudget,
                'total_overbudget' => $item->total_overbudget
            ];
        }

        $data = [
            'header' => $get_header,
            'list_budgeting' => $get_budgeting,
            'list_data_kasbon' => $get_data_kasbon,
            'list_data_lab' => $get_data_lab,
            'list_arr_kasbon' => $list_arr_kasbon
        ];

        $this->template->set($data);
        $this->template->title('Pengajuan Lab');
        $this->template->render('view_kasbon_lab');
    }

    public function edit_kasbon_others($id_header)
    {
        $id_header = urldecode($id_header);
        $id_header = str_replace('|', '/', $id_header);

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_header a');
        $this->db->where('a.id', $id_header);
        $get_header = $this->db->get()->row();

        $this->db->select('a.*, b.nm_sales, b.waktu_from, b.waktu_to');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting a');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran b', 'b.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->where('a.id_spk_budgeting', $get_header->id_spk_budgeting);
        $get_budgeting = $this->db->get()->row();

        $this->db->select('a.*, b.nm_biaya');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting_others a');
        $this->db->join(DBCNL . '.kons_master_biaya b', 'b.id = a.id_item', 'left');
        $this->db->where('a.id_spk_budgeting', $get_header->id_spk_budgeting);
        $get_data_others = $this->db->get()->result();

        $this->db->select('a.*, b.nm_biaya');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_others a');
        $this->db->join(DBCNL . '.kons_master_biaya b', 'b.id = a.id_item', 'left');
        $this->db->where('a.id_header', $id_header);
        // $this->db->group_by('a.id_item');
        $get_data_kasbon = $this->db->get()->result();

        $list_arr_kasbon = [];
        foreach ($get_data_kasbon as $item) {
            $list_arr_kasbon[$item->id_others] = [
                'qty_pengajuan' => $item->qty_pengajuan,
                'nominal_pengajuan' => $item->nominal_pengajuan,
                'total_pengajuan' => $item->total_pengajuan,
                'aktual_terpakai' => $item->aktual_terpakai,
                'sisa_budget' => $item->sisa_budget,
                'qty_terpakai' => $item->qty_terpakai,
                'nominal_terpakai' => $item->nominal_terpakai,
                'total_terpakai' => $item->total_terpakai,
                'qty_overbudget' => $item->qty_overbudget,
                'nominal_overbudget' => $item->nominal_overbudget,
                'total_overbudget' => $item->total_overbudget
            ];
        }

        $data = [
            'header' => $get_header,
            'list_budgeting' => $get_budgeting,
            'list_data_kasbon' => $get_data_kasbon,
            'list_data_others' => $get_data_others,
            'list_arr_kasbon' => $list_arr_kasbon
        ];

        $this->template->set($data);
        $this->template->title('Pengajuan Lab');
        $this->template->render('edit_kasbon_others');
    }

    public function edit_kasbon_lab($id_header)
    {
        $id_header = urldecode($id_header);
        $id_header = str_replace('|', '/', $id_header);

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_header a');
        $this->db->where('a.id', $id_header);
        $get_header = $this->db->get()->row();

        $this->db->select('a.*, b.nm_sales, b.waktu_from, b.waktu_to');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting a');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran b', 'b.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->where('a.id_spk_budgeting', $get_header->id_spk_budgeting);
        $get_budgeting = $this->db->get()->row();

        $this->db->select('a.*, b.isu_lingkungan as nm_biaya');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting_lab a');
        $this->db->join(DBCNL . '.kons_master_lab b', 'b.id = a.id_item', 'left');
        $this->db->where('a.id_spk_budgeting', $get_header->id_spk_budgeting);
        $get_data_lab = $this->db->get()->result();

        $this->db->select('a.*, b.isu_lingkungan as nm_biaya');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_lab a');
        $this->db->join(DBCNL . '.kons_master_lab b', 'b.id = a.id_item', 'left');
        $this->db->where('a.id_header', $id_header);
        // $this->db->group_by('a.id_item');
        $get_data_kasbon = $this->db->get()->result();

        $list_arr_kasbon = [];
        foreach ($get_data_kasbon as $item) {
            $list_arr_kasbon[$item->id_lab] = [
                'qty_pengajuan' => $item->qty_pengajuan,
                'nominal_pengajuan' => $item->nominal_pengajuan,
                'total_pengajuan' => $item->total_pengajuan,
                'aktual_terpakai' => $item->aktual_terpakai,
                'sisa_budget' => $item->sisa_budget,
                'qty_terpakai' => $item->qty_terpakai,
                'nominal_terpakai' => $item->nominal_terpakai,
                'total_terpakai' => $item->total_terpakai,
                'qty_overbudget' => $item->qty_overbudget,
                'nominal_overbudget' => $item->nominal_overbudget,
                'total_overbudget' => $item->total_overbudget
            ];
        }

        $data = [
            'header' => $get_header,
            'list_budgeting' => $get_budgeting,
            'list_data_kasbon' => $get_data_kasbon,
            'list_data_lab' => $get_data_lab,
            'list_arr_kasbon' => $list_arr_kasbon
        ];

        $this->template->set($data);
        $this->template->title('Pengajuan Lab');
        $this->template->render('edit_kasbon_lab');
    }

    public function add_request_budget_akomodasi($id_spk_budgeting)
    {
        $id_spk_budgeting = urldecode($id_spk_budgeting);
        $id_spk_budgeting = str_replace('|', '/', $id_spk_budgeting);

        $this->db->select('a.*, b.nm_sales, b.waktu_from, b.waktu_to');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting a');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran b', 'b.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_list_spk_budgeting = $this->db->get()->row();

        $this->db->select('a.*, b.nm_biaya');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting_akomodasi a');
        $this->db->join(DBCNL . '.kons_master_biaya b', 'b.id = a.id_item', 'left');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_list_kasbon_akomodasi = $this->db->get()->result();

        $data = [
            'list_budgeting' => $get_list_spk_budgeting,
            'list_kasbon_akomodasi' => $get_list_kasbon_akomodasi
        ];

        $this->template->set($data);
        $this->template->render('add_request_budget_akomodasi');
    }

    public function save_kasbon_subcont()
    {
        $post = $this->input->post();

        $config['upload_path'] = './uploads/kasbon_project/'; //path folder
        $config['allowed_types'] = 'gif|jpg|png|jpeg|bmp|pdf|webp'; //type yang dapat diakses bisa anda sesuaikan
        $config['max_size'] = 100000000; // Maximum file size in kilobytes (2MB).
        $config['encrypt_name'] = FALSE; // Encrypt the uploaded file's name.
        $config['remove_spaces'] = TRUE; // Remove spaces from the file name.

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        $upload_po = '';

        // $files = $_FILES['kasbon_document'];
        // $file_count = count($files['name']);

        // $_FILES['kasbon_document']['name'] = $files['name'];
        // $_FILES['kasbon_document']['type'] = $files['type'];
        // $_FILES['kasbon_document']['tmp_name'] = $files['tmp_name'];
        // $_FILES['kasbon_document']['error'] = $files['error'];
        // $_FILES['kasbon_document']['size'] = $files['size'];

        // if (!$this->upload->do_upload('kasbon_document')) {
        //     // If upload fails, display error
        //     $error = array('error' => $this->upload->display_errors());
        //     // print_r($error);
        // } else {
        //     $data_upload_po = $this->upload->data();
        //     $upload_po = 'uploads/kasbon_project/' . $data_upload_po['file_name'];
        // }


        $this->db->trans_begin();

        $id_header = $this->Kasbon_project_model->generate_id_kasbon_project();

        $grand_total = 0;

        $data_insert_detail = [];

        $no = 1;
        if (isset($post['detail_subcont'])) {
            foreach ($post['detail_subcont'] as $item) {
                if (str_replace(',', '', $item['qty_pengajuan']) > 0 && str_replace(',', '', $item['nominal_pengajuan']) > 0) {
                    $data_insert_detail[] = [
                        'id_header' => $id_header,
                        'id_spk_budgeting' => $post['id_spk_budgeting'],
                        'id_spk_penawaran' => $post['id_spk_penawaran'],
                        'id_penawaran' => $post['id_penawaran'],
                        'id_aktifitas' => $item['id_aktifitas'],
                        'nm_aktifitas' => $item['nm_aktifitas'],
                        'qty_pengajuan' => str_replace(',', '', $item['qty_pengajuan']),
                        'nominal_pengajuan' => str_replace(',', '', $item['nominal_pengajuan']),
                        'total_pengajuan' => str_replace(',', '', $item['total_pengajuan']),
                        'qty_estimasi' => $item['qty_estimasi'],
                        'price_unit_estimasi' => $item['price_unit_estimasi'],
                        'total_budget_estimasi' => $item['total_estimasi'],
                        'aktual_terpakai' => $item['aktual_terpakai'],
                        'sisa_budget' => $item['sisa_budget'],
                        'qty_terpakai' => $item['qty_terpakai'],
                        'nominal_terpakai' => $item['nominal_terpakai'],
                        'total_terpakai' => $item['total_terpakai'],
                        'qty_overbudget' => $item['qty_overbudget'],
                        'nominal_overbudget' => $item['nominal_overbudget'],
                        'total_overbudget' => $item['total_overbudget'],
                        'created_by' => $this->auth->user_id(),
                        'created_date' => date('Y-m-d H:i:s')
                    ];

                    $grand_total += (str_replace(',', '', $item['total_pengajuan']));

                    $no++;
                }
            }
        }

        if (isset($post['subcont_custom'])) {
            foreach ($post['subcont_custom'] as $item) {
                if (str_replace(',', '', $item['qty_budget']) > 0 && str_replace(',', '', $item['nominal_budget'])) {

                    $data_insert_detail[] = [
                        'id_header' => $id_header,
                        'id_spk_budgeting' => $post['id_spk_budgeting'],
                        'id_spk_penawaran' => $post['id_spk_penawaran'],
                        'id_penawaran' => $post['id_penawaran'],
                        'id_aktifitas' => $item['id'],
                        'nm_aktifitas' => $item['nm_item'],
                        'qty_pengajuan' => str_replace(',', '', $item['qty_budget']),
                        'nominal_pengajuan' => str_replace(',', '', $item['nominal_budget']),
                        'total_pengajuan' => str_replace(',', '', $item['total_budget']),
                        'qty_estimasi' => $item['estimasi_qty'],
                        'price_unit_estimasi' => $item['price_unit_estimasi'],
                        'total_budget_estimasi' => $item['total_estimasi'],
                        'aktual_terpakai' => $item['aktual_terpakai'],
                        'sisa_budget' => $item['sisa_budget'],
                        'qty_terpakai' => $item['qty_terpakai'],
                        'nominal_terpakai' => $item['nominal_terpakai'],
                        'total_terpakai' => $item['total_terpakai'],
                        'qty_overbudget' => $item['qty_overbudget'],
                        'nominal_overbudget' => $item['nominal_overbudget'],
                        'total_overbudget' => $item['total_overbudget'],
                        'created_by' => $this->auth->user_id(),
                        'created_date' => date('Y-m-d H:i:s'),
                        'custom_subcont' => 1
                    ];

                    $grand_total += (str_replace(',', '', $item['total_budget']));

                    $no++;
                }
            }
        }

        $data_insert_header = [
            'id' => $id_header,
            'id_spk_budgeting' => $post['id_spk_budgeting'],
            'id_spk_penawaran' => $post['id_spk_penawaran'],
            'id_penawaran' => $post['id_penawaran'],
            'tipe' => 1,
            'deskripsi' => $post['deskripsi'],
            'tgl' => $post['tgl'],
            'grand_total' => $grand_total,
            'dokument_link' => $upload_po,
            'bank' => $post['kasbon_bank'],
            'bank_number' => $post['kasbon_bank_number'],
            'bank_account' => $post['kasbon_bank_account'],
            'metode_pembayaran' => $post['metode_pembayaran'],
            'created_by' => $this->auth->user_id(),
            'created_date' => date('Y-m-d H:i:s')
        ];

        $insert_kasbon_header = $this->db->insert('kons_tr_kasbon_project_header', $data_insert_header);
        if (!$insert_kasbon_header) {
            $this->db->trans_rollback();
            print_r($this->db->error($insert_kasbon_header));
            exit;
        }

        $insert_kasbon_subcont = $this->db->insert_batch('kons_tr_kasbon_project_subcont', $data_insert_detail);
        if (!$insert_kasbon_subcont) {
            $this->db->trans_rollback();
            print_r($data_insert_detail);
            exit;
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $pesan = 'Please try again later !';
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $pesan = 'Data has been saved !';
        }

        echo json_encode([
            'status' => $valid,
            'pesan' => $pesan
        ]);
    }

    public function update_kasbon_subcont()
    {
        $post = $this->input->post();

        $config['upload_path'] = './uploads/kasbon_project/'; //path folder
        $config['allowed_types'] = 'gif|jpg|png|jpeg|bmp|pdf|webp'; //type yang dapat diakses bisa anda sesuaikan
        $config['max_size'] = 100000000; // Maximum file size in kilobytes (2MB).
        $config['encrypt_name'] = FALSE; // Encrypt the uploaded file's name.
        $config['remove_spaces'] = TRUE; // Remove spaces from the file name.

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        $upload_po = '';

        // $files = $_FILES['kasbon_document'];
        // $file_count = count($files['name']);

        // $_FILES['kasbon_document']['name'] = $files['name'];
        // $_FILES['kasbon_document']['type'] = $files['type'];
        // $_FILES['kasbon_document']['tmp_name'] = $files['tmp_name'];
        // $_FILES['kasbon_document']['error'] = $files['error'];
        // $_FILES['kasbon_document']['size'] = $files['size'];

        // if (!$this->upload->do_upload('kasbon_document')) {
        // $upload_po = $post['dokument_link'];
        // } else {
        //     $data_upload_po = $this->upload->data();
        //     $upload_po = 'uploads/kasbon_project/' . $data_upload_po['file_name'];
        // }

        $this->db->trans_begin();

        $reset_kasbon_subcont = $this->db->delete('kons_tr_kasbon_project_subcont', ['id_header' => $post['id']]);

        $update_header = $this->db->update('kons_tr_kasbon_project_header', [
            'deskripsi' => $post['deskripsi'],
            'tgl' => $post['tgl'],
            'bank' => $post['kasbon_bank'],
            'bank_number' => $post['kasbon_bank_number'],
            'bank_account' => $post['kasbon_bank_account'],
            'metode_pembayaran' => $post['metode_pembayaran'],
            'updated_by' => $this->auth->user_id(),
            'updated_date' => date('Y-m-d H:i:s')
        ], [
            'id' => $post['id']
        ]);

        if (!$update_header) {
            $this->db->trans_rollback();

            print_r($this->db->last_query());
            exit;
        }

        $data_insert_detail = [];

        $total_subcont = 0;
        if (isset($post['detail_subcont'])) {
            foreach ($post['detail_subcont'] as $item) {
                $qty_pengajuan = str_replace(',', '', $item['qty_pengajuan']);
                $nominal_pengajuan = str_replace(',', '', $item['nominal_pengajuan']);
                $total_pengajuan = str_replace(',', '', $item['total_pengajuan']);

                $qty_estimasi = str_replace(',', '', $item['qty_estimasi']);
                $price_unit_estimasi = str_replace(',', '', $item['price_unit_estimasi']);
                $total_estimasi = str_replace(',', '', $item['total_budget_estimasi']);

                if ($qty_pengajuan > 0 && $nominal_pengajuan > 0) {
                    $data_insert_detail[] = [
                        'id_header' => $post['id'],
                        'id_spk_budgeting' => $post['id_spk_budgeting'],
                        'id_spk_penawaran' => $post['id_spk_penawaran'],
                        'id_penawaran' => $post['id_penawaran'],
                        'id_aktifitas' => $item['id_aktifitas'],
                        'nm_aktifitas' => $item['nm_aktifitas'],
                        'qty_pengajuan' => $qty_pengajuan,
                        'nominal_pengajuan' => $nominal_pengajuan,
                        'total_pengajuan' => $total_pengajuan,
                        'qty_estimasi' => $qty_estimasi,
                        'price_unit_estimasi' => $price_unit_estimasi,
                        'total_budget_estimasi' => $total_estimasi,
                        'aktual_terpakai' => $item['aktual_terpakai'],
                        'sisa_budget' => $item['sisa_budget'],
                        'qty_terpakai' => $item['qty_terpakai'],
                        'nominal_terpakai' => $item['price_unit_terpakai'],
                        'total_terpakai' => $item['total_budget_terpakai'],
                        'qty_overbudget' => $item['qty_overbudget'],
                        'nominal_overbudget' => $item['price_unit_overbudget'],
                        'total_overbudget' => $item['total_budget_overbudget'],
                        'created_by' => $this->auth->user_id(),
                        'created_date' => date('Y-m-d H:i:s')
                    ];

                    $total_subcont += $total_pengajuan;
                }
            }
        }

        if (isset($post['subcont_custom'])) :
            if (!empty($post['subcont_custom'])) :
                foreach ($post['subcont_custom'] as $item) :
                    $qty_pengajuan = str_replace(',', '', $item['qty_pengajuan']);
                    $nominal_pengajuan = str_replace(',', '', $item['nominal_pengajuan']);
                    $total_pengajuan = str_replace(',', '', $item['total_pengajuan']);

                    $qty_estimasi = str_replace(',', '', $item['qty_estimasi']);
                    $price_unit_estimasi = str_replace(',', '', $item['price_unit_estimasi']);
                    $total_estimasi = str_replace(',', '', $item['total_budget_estimasi']);

                    if ($qty_pengajuan > 0 && $nominal_pengajuan > 0) {
                        $data_insert_detail[] = [
                            'id_header' => $post['id'],
                            'id_spk_budgeting' => $post['id_spk_budgeting'],
                            'id_spk_penawaran' => $post['id_spk_penawaran'],
                            'id_penawaran' => $post['id_penawaran'],
                            'id_aktifitas' => $item['id_aktifitas'],
                            'nm_aktifitas' => $item['nm_aktifitas'],
                            'qty_pengajuan' => $qty_pengajuan,
                            'nominal_pengajuan' => $nominal_pengajuan,
                            'total_pengajuan' => $total_pengajuan,
                            'qty_estimasi' => $qty_estimasi,
                            'price_unit_estimasi' => $price_unit_estimasi,
                            'total_budget_estimasi' => $total_estimasi,
                            'aktual_terpakai' => $item['aktual_terpakai'],
                            'sisa_budget' => $item['sisa_budget'],
                            'qty_terpakai' => $item['qty_terpakai'],
                            'nominal_terpakai' => $item['price_unit_terpakai'],
                            'total_terpakai' => $item['total_budget_terpakai'],
                            'qty_overbudget' => $item['qty_overbudget'],
                            'nominal_overbudget' => $item['price_unit_overbudget'],
                            'total_overbudget' => $item['total_budget_overbudget'],
                            'created_by' => $this->auth->user_id(),
                            'created_date' => date('Y-m-d H:i:s'),
                            'custom_subcont' => 1
                        ];

                        $total_subcont += $total_pengajuan;
                    }
                endforeach;
            endif;
        endif;

        if (!empty($data_insert_detail)) {
            $insert_detail = $this->db->insert_batch('kons_tr_kasbon_project_subcont', $data_insert_detail);
            if (!$insert_detail) {
                $this->db->trans_rollback();

                print_r($this->db->last_query());
                exit;
            }

            $update_header = $this->db->update('kons_tr_kasbon_project_header', array('grand_total' => $total_subcont), array('id' => $post['id']));
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $pesan = 'Please try again later !';
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $pesan = 'Data has been updated !';
        }

        echo json_encode([
            'status' => $valid,
            'pesan' => $pesan
        ]);
    }

    public function save_kasbon_akomodasi()
    {
        $post = $this->input->post();

        $config['upload_path'] = './uploads/kasbon_project/'; //path folder
        $config['allowed_types'] = 'gif|jpg|png|jpeg|bmp|pdf|webp'; //type yang dapat diakses bisa anda sesuaikan
        $config['max_size'] = 100000000; // Maximum file size in kilobytes (2MB).
        $config['encrypt_name'] = FALSE; // Encrypt the uploaded file's name.
        $config['remove_spaces'] = TRUE; // Remove spaces from the file name.

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        $upload_po = '';

        // $files = $_FILES['kasbon_document'];
        // $file_count = count($files['name']);

        // $_FILES['kasbon_document']['name'] = $files['name'];
        // $_FILES['kasbon_document']['type'] = $files['type'];
        // $_FILES['kasbon_document']['tmp_name'] = $files['tmp_name'];
        // $_FILES['kasbon_document']['error'] = $files['error'];
        // $_FILES['kasbon_document']['size'] = $files['size'];

        // if (!$this->upload->do_upload('kasbon_document')) {
        //     // If upload fails, display error
        //     $error = array('error' => $this->upload->display_errors());
        //     // print_r($error);
        // } else {
        //     $data_upload_po = $this->upload->data();
        //     $upload_po = 'uploads/kasbon_project/' . $data_upload_po['file_name'];
        // }


        $this->db->trans_begin();

        $id = $this->Kasbon_project_model->generate_id_kasbon_project();

        $grand_total = 0;
        foreach ($post['detail_akomodasi'] as $item) {
            $grand_total += (str_replace(',', '', $item['total_pengajuan']));
        }

        $data_header = [
            'id' => $id,
            'id_spk_budgeting' => $post['id_spk_budgeting'],
            'id_spk_penawaran' => $post['id_spk_penawaran'],
            'id_penawaran' => $post['id_penawaran'],
            'tipe' => 2,
            'deskripsi' => $post['deskripsi'],
            'tgl' => $post['tgl'],
            'grand_total' => $grand_total,
            'dokument_link' => $upload_po,
            'bank' => $post['kasbon_bank'],
            'bank_number' => $post['kasbon_bank_number'],
            'bank_account' => $post['kasbon_bank_account'],
            'sts_req_payment' => '',
            'metode_pembayaran' => $post['metode_pembayaran'],
            'created_by' => $this->auth->user_id(),
            'created_date' => date('Y-m-d H:i:s')
        ];

        $insert_header = $this->db->insert('kons_tr_kasbon_project_header', $data_header);
        if (!$insert_header) {
            $this->db->trans_rollback();

            print($this->db->error($insert_header));
            exit;
        }

        $data_insert = [];

        $no = 1;
        foreach ($post['detail_akomodasi'] as $item) {
            if (str_replace(',', '', $item['qty_pengajuan']) > 0 && str_replace(',', '', $item['nominal_pengajuan'])) {
                $data_insert[] = [
                    'id_header' => $id,
                    'id_spk_budgeting' => $post['id_spk_budgeting'],
                    'id_spk_penawaran' => $post['id_spk_penawaran'],
                    'id_penawaran' => $post['id_penawaran'],
                    'id_akomodasi' => $item['id_akomodasi'],
                    'id_item' => $item['id_item'],
                    'nm_item' => $item['nm_item'],
                    'qty_pengajuan' => str_replace(',', '', $item['qty_pengajuan']),
                    'nominal_pengajuan' => str_replace(',', '', $item['nominal_pengajuan']),
                    'total_pengajuan' => str_replace(',', '', $item['total_pengajuan']),
                    'qty_estimasi' => $item['qty_estimasi'],
                    'price_unit_estimasi' => $item['price_unit_estimasi'],
                    'total_budget_estimasi' => $item['total_estimasi'],
                    'qty_budget_tambahan' => $item['qty_budget_tambahan'],
                    'budget_tambahan' => $item['budget_tambahan'],
                    'aktual_terpakai' => $item['aktual_terpakai'],
                    'sisa_budget' => $item['sisa_budget'],
                    'qty_terpakai' => $item['qty_terpakai'],
                    'nominal_terpakai' => $item['nominal_terpakai'],
                    'total_terpakai' => $item['total_terpakai'],
                    'qty_overbudget' => $item['qty_overbudget'],
                    'nominal_overbudget' => $item['nominal_overbudget'],
                    'total_overbudget' => $item['total_overbudget'],
                    'created_by' => $this->auth->user_id(),
                    'created_date' => date('Y-m-d H:i:s')
                ];

                $no++;
            }
        }

        $insert_kasbon_subcont = $this->db->insert_batch('kons_tr_kasbon_project_akomodasi', $data_insert);
        if (!$insert_kasbon_subcont) {
            $this->db->trans_rollback();
            print_r($this->db->last_query());
            exit;
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $pesan = 'Please try again later !';
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $pesan = 'Data has been saved !';
        }

        echo json_encode([
            'status' => $valid,
            'pesan' => $pesan
        ]);
    }

    public function update_kasbon_akomodasi()
    {
        $post = $this->input->post();

        $config['upload_path'] = './uploads/kasbon_project/'; //path folder
        $config['allowed_types'] = 'gif|jpg|png|jpeg|bmp|pdf|webp'; //type yang dapat diakses bisa anda sesuaikan
        $config['max_size'] = 100000000; // Maximum file size in kilobytes (2MB).
        $config['encrypt_name'] = FALSE; // Encrypt the uploaded file's name.
        $config['remove_spaces'] = TRUE; // Remove spaces from the file name.

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        $upload_po = '';

        // $files = $_FILES['kasbon_document'];
        // $file_count = count($files['name']);

        // $_FILES['kasbon_document']['name'] = $files['name'];
        // $_FILES['kasbon_document']['type'] = $files['type'];
        // $_FILES['kasbon_document']['tmp_name'] = $files['tmp_name'];
        // $_FILES['kasbon_document']['error'] = $files['error'];
        // $_FILES['kasbon_document']['size'] = $files['size'];

        // if (!$this->upload->do_upload('kasbon_document')) {
        //     // If upload fails, display error
        //     $error = array('error' => $this->upload->display_errors());
        //     // print_r($error);
        // } else {
        //     $data_upload_po = $this->upload->data();
        //     $upload_po = 'uploads/kasbon_project/' . $data_upload_po['file_name'];
        // }


        $this->db->trans_begin();

        $id = $this->Kasbon_project_model->generate_id_kasbon_project();

        $grand_total = 0;
        foreach ($post['dt'] as $item) {
            $grand_total += (str_replace(',', '', $item['total_pengajuan']));
        }

        $reset_kasbon_subcont = $this->db->delete('kons_tr_kasbon_project_akomodasi', ['id_header' => $post['id_header']]);

        $update_header = $this->db->update('kons_tr_kasbon_project_header', [
            'grand_total' => $grand_total,
            'deskripsi' => $post['deskripsi'],
            'tgl' => $post['tgl'],
            'dokument_link' => $upload_po,
            'bank' => $post['kasbon_bank'],
            'bank_number' => $post['kasbon_bank_number'],
            'bank_account' => $post['kasbon_bank_account'],
            'metode_pembayaran' => $post['metode_pembayaran'],
            'updated_by' => $this->auth->user_id(),
            'updated_date' => date('Y-m-d H:i:s')
        ], [
            'id' => $post['id_header']
        ]);

        if (!$update_header) {
            $this->db->trans_rollback();

            print_r($this->db->last_query());
            exit;
        }

        $data_insert_detail = [];

        if (isset($post['dt'])) {
            foreach ($post['dt'] as $item) {
                $qty_pengajuan = str_replace(',', '', $item['qty_pengajuan']);
                $nominal_pengajuan = str_replace(',', '', $item['nominal_pengajuan']);
                $total_pengajuan = str_replace(',', '', $item['total_pengajuan']);

                $qty_estimasi = str_replace(',', '', $item['qty_estimasi']);
                $price_unit_estimasi = str_replace(',', '', $item['price_unit_estimasi']);
                $total_estimasi = str_replace(',', '', $item['total_estimasi']);

                if ($qty_pengajuan > 0 && $nominal_pengajuan > 0) {
                    $data_insert_detail[] = [
                        'id_header' => $post['id_header'],
                        'id_spk_budgeting' => $post['id_spk_budgeting'],
                        'id_spk_penawaran' => $post['id_spk_penawaran'],
                        'id_penawaran' => $post['id_penawaran'],
                        'id_akomodasi' => $item['id_akomodasi'],
                        'id_item' => $item['id_item'],
                        'nm_item' => $item['nm_item'],
                        'qty_pengajuan' => $qty_pengajuan,
                        'nominal_pengajuan' => $nominal_pengajuan,
                        'total_pengajuan' => $total_pengajuan,
                        'qty_estimasi' => $qty_estimasi,
                        'price_unit_estimasi' => $price_unit_estimasi,
                        'total_budget_estimasi' => $total_estimasi,
                        'budget_tambahan' => $item['budget_tambahan'],
                        'aktual_terpakai' => $item['aktual_terpakai'],
                        'sisa_budget' => $item['sisa_budget'],
                        'qty_terpakai' => $item['qty_terpakai'],
                        'nominal_terpakai' => $item['nominal_terpakai'],
                        'total_terpakai' => $item['total_terpakai'],
                        'qty_overbudget' => $item['qty_overbudget'],
                        'nominal_overbudget' => $item['nominal_overbudget'],
                        'total_overbudget' => $item['total_overbudget'],
                        'created_by' => $this->auth->user_id(),
                        'created_date' => date('Y-m-d H:i:s')
                    ];
                }
            }
        }

        if (!empty($data_insert_detail)) {
            $insert_detail = $this->db->insert_batch('kons_tr_kasbon_project_akomodasi', $data_insert_detail);
            if (!$insert_detail) {
                $this->db->trans_rollback();

                print_r($this->db->last_query());
                exit;
            }
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $pesan = 'Please try again later !';
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $pesan = 'Data has been saved !';
        }

        echo json_encode([
            'status' => $valid,
            'pesan' => $pesan
        ]);
    }

    public function save_kasbon_others()
    {
        $post = $this->input->post();

        $config['upload_path'] = './uploads/kasbon_project/'; //path folder
        $config['allowed_types'] = 'gif|jpg|png|jpeg|bmp|pdf|webp'; //type yang dapat diakses bisa anda sesuaikan
        $config['max_size'] = 100000000; // Maximum file size in kilobytes (2MB).
        $config['encrypt_name'] = TRUE; // Encrypt the uploaded file's name.
        $config['remove_spaces'] = TRUE; // Remove spaces from the file name.

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        $upload_po = '';

        // $files = $_FILES['kasbon_document'];
        // $file_count = count($files['name']);

        // $_FILES['kasbon_document']['name'] = $files['name'];
        // $_FILES['kasbon_document']['type'] = $files['type'];
        // $_FILES['kasbon_document']['tmp_name'] = $files['tmp_name'];
        // $_FILES['kasbon_document']['error'] = $files['error'];
        // $_FILES['kasbon_document']['size'] = $files['size'];

        // if (!$this->upload->do_upload('kasbon_document')) {
        //     // If upload fails, display error
        //     $error = array('error' => $this->upload->display_errors());
        //     // print_r($error);
        // } else {
        //     $data_upload_po = $this->upload->data();
        //     $upload_po = 'uploads/kasbon_project/' . $data_upload_po['file_name'];
        // }


        $this->db->trans_begin();

        $id = $this->Kasbon_project_model->generate_id_kasbon_project();

        $grand_total = 0;
        foreach ($post['detail_others'] as $item) {
            $grand_total += (str_replace(',', '', $item['total_pengajuan']));
        }

        $data_insert_header = [
            'id' => $id,
            'id_spk_budgeting' => $post['id_spk_budgeting'],
            'id_spk_penawaran' => $post['id_spk_penawaran'],
            'id_penawaran' => $post['id_penawaran'],
            'tipe' => 3,
            'deskripsi' => $post['deskripsi'],
            'tgl' => $post['tgl'],
            'grand_total' => $grand_total,
            'dokument_link' => $upload_po,
            'bank' => $post['kasbon_bank'],
            'bank_number' => $post['kasbon_bank_number'],
            'bank_account' => $post['kasbon_bank_account'],
            'metode_pembayaran' => $post['metode_pembayaran'],
            'created_by' => $this->auth->user_id(),
            'created_date' => date('Y-m-d H:i:s')
        ];

        $insert_header = $this->db->insert('kons_tr_kasbon_project_header', $data_insert_header);
        if (!$insert_header) {
            $this->db->trans_rollback();

            print_r($this->db->error($insert_header));
            exit;
        }

        $data_insert = [];

        $no = 1;
        foreach ($post['detail_others'] as $item) {
            if (str_replace(',', '', $item['qty_pengajuan']) > 0 && str_replace(',', '', $item['nominal_pengajuan'])) {
                $data_insert[] = [
                    'id_header' => $id,
                    'id_spk_budgeting' => $post['id_spk_budgeting'],
                    'id_spk_penawaran' => $post['id_spk_penawaran'],
                    'id_penawaran' => $post['id_penawaran'],
                    'id_others' => $item['id_others'],
                    'id_item' => $item['id_item'],
                    'nm_item' => $item['nm_item'],
                    'qty_pengajuan' => str_replace(',', '', $item['qty_pengajuan']),
                    'nominal_pengajuan' => str_replace(',', '', $item['nominal_pengajuan']),
                    'total_pengajuan' => str_replace(',', '', $item['total_pengajuan']),
                    'qty_estimasi' => $item['qty_estimasi'],
                    'price_unit_estimasi' => $item['price_unit_estimasi'],
                    'total_budget_estimasi' => $item['total_estimasi'],
                    'aktual_terpakai' => $item['aktual_terpakai'],
                    'sisa_budget' => $item['sisa_budget'],
                    'qty_terpakai' => $item['qty_terpakai'],
                    'nominal_terpakai' => $item['price_unit_terpakai'],
                    'total_terpakai' => $item['total_terpakai'],
                    'qty_overbudget' => $item['qty_overbudget'],
                    'nominal_overbudget' => $item['nominal_overbudget'],
                    'total_overbudget' => $item['total_overbudget'],
                    'created_by' => $this->auth->user_id(),
                    'created_date' => date('Y-m-d H:i:s')
                ];

                $no++;
            }
        }

        $insert_kasbon_subcont = $this->db->insert_batch('kons_tr_kasbon_project_others', $data_insert);
        if (!$insert_kasbon_subcont) {
            $this->db->trans_rollback();
            print_r($this->db->last_query());
            exit;
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $pesan = 'Please try again later !';
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $pesan = 'Data has been saved !';
        }

        echo json_encode([
            'status' => $valid,
            'pesan' => $pesan
        ]);
    }

    public function save_kasbon_lab()
    {
        $post = $this->input->post();

        $config['upload_path'] = './uploads/kasbon_project/'; //path folder
        $config['allowed_types'] = 'gif|jpg|png|jpeg|bmp|pdf|webp'; //type yang dapat diakses bisa anda sesuaikan
        $config['max_size'] = 100000000; // Maximum file size in kilobytes (2MB).
        $config['encrypt_name'] = TRUE; // Encrypt the uploaded file's name.
        $config['remove_spaces'] = TRUE; // Remove spaces from the file name.

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        $upload_po = '';

        // $files = $_FILES['kasbon_document'];
        // $file_count = count($files['name']);

        // $_FILES['kasbon_document']['name'] = $files['name'];
        // $_FILES['kasbon_document']['type'] = $files['type'];
        // $_FILES['kasbon_document']['tmp_name'] = $files['tmp_name'];
        // $_FILES['kasbon_document']['error'] = $files['error'];
        // $_FILES['kasbon_document']['size'] = $files['size'];

        // if (!$this->upload->do_upload('kasbon_document')) {
        //     // If upload fails, display error
        //     $error = array('error' => $this->upload->display_errors());
        //     // print_r($error);
        // } else {
        //     $data_upload_po = $this->upload->data();
        //     $upload_po = 'uploads/kasbon_project/' . $data_upload_po['file_name'];
        // }


        $this->db->trans_begin();

        $id = $this->Kasbon_project_model->generate_id_kasbon_project();

        $grand_total = 0;
        foreach ($post['detail_lab'] as $item) {
            $grand_total += (str_replace(',', '', $item['total_pengajuan']));
        }

        $data_insert_header = [
            'id' => $id,
            'id_spk_budgeting' => $post['id_spk_budgeting'],
            'id_spk_penawaran' => $post['id_spk_penawaran'],
            'id_penawaran' => $post['id_penawaran'],
            'tipe' => 4,
            'deskripsi' => $post['deskripsi'],
            'tgl' => $post['tgl'],
            'grand_total' => $grand_total,
            'dokument_link' => $upload_po,
            'bank' => $post['kasbon_bank'],
            'bank_number' => $post['kasbon_bank_number'],
            'bank_account' => $post['kasbon_bank_account'],
            'metode_pembayaran' => $post['metode_pembayaran'],
            'created_by' => $this->auth->user_id(),
            'created_date' => date('Y-m-d H:i:s')
        ];

        $insert_header = $this->db->insert('kons_tr_kasbon_project_header', $data_insert_header);
        if (!$insert_header) {
            $this->db->trans_rollback();

            print_r($this->db->error($insert_header));
            exit;
        }

        $data_insert = [];

        $no = 1;
        foreach ($post['detail_lab'] as $item) {
            if (str_replace(',', '', $item['qty_pengajuan']) > 0 && str_replace(',', '', $item['nominal_pengajuan'])) {
                $data_insert[] = [
                    'id_header' => $id,
                    'id_spk_budgeting' => $post['id_spk_budgeting'],
                    'id_spk_penawaran' => $post['id_spk_penawaran'],
                    'id_penawaran' => $post['id_penawaran'],
                    'id_lab' => $item['id_lab'],
                    'id_item' => $item['id_item'],
                    'nm_item' => $item['nm_item'],
                    'qty_pengajuan' => str_replace(',', '', $item['qty_pengajuan']),
                    'nominal_pengajuan' => str_replace(',', '', $item['nominal_pengajuan']),
                    'total_pengajuan' => str_replace(',', '', $item['total_pengajuan']),
                    'qty_estimasi' => $item['qty_estimasi'],
                    'price_unit_estimasi' => $item['price_unit_estimasi'],
                    'total_budget_estimasi' => $item['total_estimasi'],
                    'aktual_terpakai' => $item['aktual_terpakai'],
                    'sisa_budget' => $item['sisa_budget'],
                    'qty_terpakai' => $item['qty_terpakai'],
                    'nominal_terpakai' => $item['price_unit_terpakai'],
                    'total_terpakai' => $item['total_terpakai'],
                    'qty_overbudget' => $item['qty_overbudget'],
                    'nominal_overbudget' => $item['nominal_overbudget'],
                    'total_overbudget' => $item['total_overbudget'],
                    'created_by' => $this->auth->user_id(),
                    'created_date' => date('Y-m-d H:i:s')
                ];

                $no++;
            }
        }

        $insert_kasbon_subcont = $this->db->insert_batch('kons_tr_kasbon_project_lab', $data_insert);
        if (!$insert_kasbon_subcont) {
            $this->db->trans_rollback();
            print_r($this->db->last_query());
            exit;
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $pesan = 'Please try again later !';
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $pesan = 'Data has been saved !';
        }

        echo json_encode([
            'status' => $valid,
            'pesan' => $pesan
        ]);
    }

    public function update_kasbon_others()
    {
        $post = $this->input->post();

        $config['upload_path'] = './uploads/kasbon_project/'; //path folder
        $config['allowed_types'] = 'gif|jpg|png|jpeg|bmp|pdf|webp'; //type yang dapat diakses bisa anda sesuaikan
        $config['max_size'] = 100000000; // Maximum file size in kilobytes (2MB).
        $config['encrypt_name'] = TRUE; // Encrypt the uploaded file's name.
        $config['remove_spaces'] = TRUE; // Remove spaces from the file name.

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        $upload_po = '';

        // $files = $_FILES['kasbon_document'];
        // $file_count = count($files['name']);

        // $_FILES['kasbon_document']['name'] = $files['name'];
        // $_FILES['kasbon_document']['type'] = $files['type'];
        // $_FILES['kasbon_document']['tmp_name'] = $files['tmp_name'];
        // $_FILES['kasbon_document']['error'] = $files['error'];
        // $_FILES['kasbon_document']['size'] = $files['size'];

        // if (!$this->upload->do_upload('kasbon_document')) {
        //     $upload_po = $post['dokument_link'];
        // } else {
        //     $data_upload_po = $this->upload->data();
        //     $upload_po = 'uploads/kasbon_project/' . $data_upload_po['file_name'];
        // }

        $grand_total = 0;
        foreach ($post['detail_others'] as $item) {
            $grand_total += (str_replace(',', '', $item['total_pengajuan']));
        }

        $this->db->trans_begin();

        $id = $post['id_header'];

        $this->db->delete('kons_tr_kasbon_project_others', ['id_header' => $id]);

        $data_update_header = [
            'grand_total' => $grand_total,
            'deskripsi' => $post['deskripsi'],
            'tgl' => $post['tgl'],
            'dokument_link' => $upload_po,
            'bank' => $post['kasbon_bank'],
            'bank_number' => $post['kasbon_bank_number'],
            'bank_account' => $post['kasbon_bank_account'],
            'metode_pembayaran' => $post['metode_pembayaran'],
            'updated_by' => $this->auth->user_id(),
            'updated_date' => date('Y-m-d H:i:s')
        ];

        $data_insert_detail = [];

        if (isset($post['detail_others'])) {
            foreach ($post['detail_others'] as $item) {
                $qty_pengajuan = str_replace(',', '', $item['qty_pengajuan']);
                $nominal_pengajuan = str_replace(',', '', $item['nominal_pengajuan']);
                $total_pengajuan = str_replace(',', '', $item['total_pengajuan']);

                $data_insert_detail[] = [
                    'id_header' => $id,
                    'id_spk_budgeting' => $post['id_spk_budgeting'],
                    'id_spk_penawaran' => $post['id_spk_penawaran'],
                    'id_penawaran' => $post['id_penawaran'],
                    'id_others' => $item['id_others'],
                    'id_item' => $item['id_item'],
                    'nm_item' => $item['nm_item'],
                    'qty_pengajuan' => $qty_pengajuan,
                    'nominal_pengajuan' => $nominal_pengajuan,
                    'total_pengajuan' => $total_pengajuan,
                    'qty_estimasi' => $item['qty_estimasi'],
                    'price_unit_estimasi' => $item['price_unit_estimasi'],
                    'total_budget_estimasi' => $item['total_budget_estimasi'],
                    'aktual_terpakai' => $item['aktual_terpakai'],
                    'sisa_budget' => $item['sisa_budget'],
                    'qty_terpakai' => $item['qty_terpakai'],
                    'nominal_terpakai' => $item['nominal_terpakai'],
                    'total_terpakai' => $item['total_terpakai'],
                    'qty_overbudget' => $item['qty_overbudget'],
                    'nominal_overbudget' => $item['nominal_overbudget'],
                    'total_overbudget' => $item['total_overbudget'],
                    'created_by' => $this->auth->user_id(),
                    'created_date' => date('Y-m-d H:i:s')
                ];
            }
        }

        $update_header = $this->db->update('kons_tr_kasbon_project_header', $data_update_header, ['id' => $id]);
        if (!$update_header) {
            $this->db->trans_rollback();

            print_r($this->db->error($update_header));
            exit;
        }

        if (!empty($data_insert_detail)) {
            $insert_detail = $this->db->insert_batch('kons_tr_kasbon_project_others', $data_insert_detail);
            if (!$insert_detail) {
                $this->db->trans_rollback();

                print_r($this->db->error($insert_detail));
                exit;
            }
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $pesan = 'Please try again later !';
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $pesan = 'Data has been updated !';
        }

        echo json_encode([
            'status' => $valid,
            'pesan' => $pesan
        ]);
    }

    public function update_kasbon_lab()
    {
        $post = $this->input->post();

        $config['upload_path'] = './uploads/kasbon_project/'; //path folder
        $config['allowed_types'] = 'gif|jpg|png|jpeg|bmp|pdf|webp'; //type yang dapat diakses bisa anda sesuaikan
        $config['max_size'] = 100000000; // Maximum file size in kilobytes (2MB).
        $config['encrypt_name'] = TRUE; // Encrypt the uploaded file's name.
        $config['remove_spaces'] = TRUE; // Remove spaces from the file name.

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        $upload_po = '';

        // $files = $_FILES['kasbon_document'];
        // $file_count = count($files['name']);

        // $_FILES['kasbon_document']['name'] = $files['name'];
        // $_FILES['kasbon_document']['type'] = $files['type'];
        // $_FILES['kasbon_document']['tmp_name'] = $files['tmp_name'];
        // $_FILES['kasbon_document']['error'] = $files['error'];
        // $_FILES['kasbon_document']['size'] = $files['size'];

        // if (!$this->upload->do_upload('kasbon_document')) {
        //     $upload_po = $post['dokument_link'];
        // } else {
        //     $data_upload_po = $this->upload->data();
        //     $upload_po = 'uploads/kasbon_project/' . $data_upload_po['file_name'];
        // }

        $grand_total = 0;
        foreach ($post['detail_lab'] as $item) {
            $grand_total += (str_replace(',', '', $item['total_pengajuan']));
        }

        $this->db->trans_begin();

        $id = $post['id_header'];

        $this->db->delete('kons_tr_kasbon_project_lab', ['id_header' => $id]);

        $data_update_header = [
            'grand_total' => $grand_total,
            'deskripsi' => $post['deskripsi'],
            'tgl' => $post['tgl'],
            'dokument_link' => $upload_po,
            'bank' => $post['kasbon_bank'],
            'bank_number' => $post['kasbon_bank_number'],
            'bank_account' => $post['kasbon_bank_account'],
            'metode_pembayaran' => $post['metode_pembayaran'],
            'updated_by' => $this->auth->user_id(),
            'updated_date' => date('Y-m-d H:i:s')
        ];

        $data_insert_detail = [];

        if (isset($post['detail_lab'])) {
            foreach ($post['detail_lab'] as $item) {
                $qty_pengajuan = str_replace(',', '', $item['qty_pengajuan']);
                $nominal_pengajuan = str_replace(',', '', $item['nominal_pengajuan']);
                $total_pengajuan = str_replace(',', '', $item['total_pengajuan']);

                $data_insert_detail[] = [
                    'id_header' => $id,
                    'id_spk_budgeting' => $post['id_spk_budgeting'],
                    'id_spk_penawaran' => $post['id_spk_penawaran'],
                    'id_penawaran' => $post['id_penawaran'],
                    'id_lab' => $item['id_lab'],
                    'id_item' => $item['id_item'],
                    'nm_item' => $item['nm_item'],
                    'qty_pengajuan' => $qty_pengajuan,
                    'nominal_pengajuan' => $nominal_pengajuan,
                    'total_pengajuan' => $total_pengajuan,
                    'qty_estimasi' => $item['qty_estimasi'],
                    'price_unit_estimasi' => $item['price_unit_estimasi'],
                    'total_budget_estimasi' => $item['total_budget_estimasi'],
                    'aktual_terpakai' => $item['aktual_terpakai'],
                    'sisa_budget' => $item['sisa_budget'],
                    'qty_terpakai' => $item['qty_terpakai'],
                    'nominal_terpakai' => $item['nominal_terpakai'],
                    'total_terpakai' => $item['total_terpakai'],
                    'qty_overbudget' => $item['qty_overbudget'],
                    'nominal_overbudget' => $item['nominal_overbudget'],
                    'total_overbudget' => $item['total_overbudget'],
                    'created_by' => $this->auth->user_id(),
                    'created_date' => date('Y-m-d H:i:s')
                ];
            }
        }

        $update_header = $this->db->update('kons_tr_kasbon_project_header', $data_update_header, ['id' => $id]);
        if (!$update_header) {
            $this->db->trans_rollback();

            print_r($this->db->error($update_header));
            exit;
        }

        if (!empty($data_insert_detail)) {
            $insert_detail = $this->db->insert_batch('kons_tr_kasbon_project_lab', $data_insert_detail);
            if (!$insert_detail) {
                $this->db->trans_rollback();

                print_r($this->db->error($insert_detail));
                exit;
            }
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $pesan = 'Please try again later !';
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $pesan = 'Data has been updated !';
        }

        echo json_encode([
            'status' => $valid,
            'pesan' => $pesan
        ]);
    }

    public function del_kasbon_subcont()
    {
        $id = $this->input->post('id');

        $this->db->trans_start();

        $del_header = $this->db->delete('kons_tr_kasbon_project_header', ['id' => $id]);
        $del_kasbon_subcont = $this->db->delete('kons_tr_kasbon_project_subcont', ['id_header' => $id]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $pesan = 'Please try again later !';
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $pesan = 'Data has been deleted !';
        }

        echo json_encode([
            'status' => $valid,
            'pesan' => $pesan
        ]);
    }

    public function del_kasbon_akomodasi()
    {
        $id = $this->input->post('id');

        $this->db->trans_start();

        $del_header = $this->db->delete('kons_tr_kasbon_project_header', ['id' => $id]);
        $del_kasbon_akomodasi = $this->db->delete('kons_tr_kasbon_project_akomodasi', ['id_header' => $id]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $pesan = 'Please try again later !';
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $pesan = 'Data has been deleted !';
        }

        echo json_encode([
            'status' => $valid,
            'pesan' => $pesan
        ]);
    }

    public function del_kasbon_others()
    {
        $id = $this->input->post('id_kasbon_others');

        $this->db->trans_start();

        $this->db->delete('kons_tr_kasbon_project_header', ['id' => $id]);
        $this->db->delete('kons_tr_kasbon_project_others', ['id_header' => $id]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $pesan = 'Please try again later !';
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $pesan = 'Data has been deleted !';
        }

        echo json_encode([
            'status' => $valid,
            'pesan' => $pesan
        ]);
    }

    public function del_kasbon_lab()
    {
        $id = $this->input->post('id_kasbon_lab');

        $this->db->trans_start();

        $this->db->delete('kons_tr_kasbon_project_header', ['id' => $id]);
        $this->db->delete('kons_tr_kasbon_project_lab', ['id_header' => $id]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $pesan = 'Please try again later !';
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $pesan = 'Data has been deleted !';
        }

        echo json_encode([
            'status' => $valid,
            'pesan' => $pesan
        ]);
    }

    public function paid_kasbon_subcont()
    {
        $id_kasbon_subcont = $this->input->post('id_kasbon_subcont');

        $this->db->trans_begin();

        $this->db->update('kons_tr_kasbon_project_subcont', ['sts' => 1], ['id_kasbon_subcont' => $id_kasbon_subcont]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $pesan = 'Please try again later !';
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $pesan = 'Data has been paid !';
        }

        echo json_encode([
            'status' => $valid,
            'pesan' => $pesan
        ]);
    }

    public function paid_kasbon_akomodasi()
    {
        $id_kasbon_akomodasi = $this->input->post('id_kasbon_akomodasi');

        $this->db->trans_begin();

        $this->db->update('kons_tr_kasbon_project_akomodasi', ['sts' => 1], ['id_kasbon_akomodasi' => $id_kasbon_akomodasi]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $pesan = 'Please try again later !';
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $pesan = 'Data has been paid !';
        }

        echo json_encode([
            'status' => $valid,
            'pesan' => $pesan
        ]);
    }

    public function paid_kasbon_others()
    {
        $id_kasbon_others = $this->input->post('id_kasbon_others');

        $this->db->trans_begin();

        $this->db->update('kons_tr_kasbon_project_others', ['sts' => 1], ['id_kasbon_others' => $id_kasbon_others]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $pesan = 'Please try again later !';
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $pesan = 'Data has been paid !';
        }

        echo json_encode([
            'status' => $valid,
            'pesan' => $pesan
        ]);
    }

    public function hitung_all_budget_on_process()
    {
        $id_spk_budgeting = $this->input->post('id_spk_budgeting');

        $nilai_budget_subcont = 0;
        $nilai_budget_akomodasi = 0;
        $nilai_budget_others = 0;
        $nilai_budget_lab = 0;

        $this->db->select('a.total_aktifitas_final');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting_aktifitas a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_subcont = $this->db->get()->result();

        $this->db->select('(b.qty_budget_tambahan * b.budget_tambahan) as ttl');
        $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_subcont_header a');
        $this->db->join(DBCNL . '.kons_tr_kasbon_req_ovb_subcont_detail b', 'b.id_request_ovb = a.id_request_ovb');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->where('a.sts', 1);
        $get_ovb_subcont = $this->db->get()->result();

        $this->db->select('a.total_final');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting_akomodasi a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_akomodasi = $this->db->get()->result();

        $this->db->select('(b.qty_budget_tambahan * b.budget_tambahan) as ttl');
        $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_akomodasi_header a');
        $this->db->join(DBCNL . '.kons_tr_kasbon_req_ovb_akomodasi_detail b', 'b.id_request_ovb = a.id_request_ovb');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->where('a.sts', 1);
        $get_ovb_akomodasi = $this->db->get()->result();

        $this->db->select('a.total_final');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting_others a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_others = $this->db->get()->result();

        $this->db->select('(b.qty_budget_tambahan * b.budget_tambahan) as ttl');
        $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_others_header a');
        $this->db->join(DBCNL . '.kons_tr_kasbon_req_ovb_others_detail b', 'b.id_request_ovb = a.id_request_ovb');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->where('a.sts', 1);
        $get_ovb_others = $this->db->get()->result();

        $this->db->select('a.total_final');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting_lab a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_budget_lab = $this->db->get()->result();

        $this->db->select('(b.qty_budget_tambahan * b.budget_tambahan) as ttl');
        $this->db->from(DBCNL . '.kons_tr_kasbon_req_ovb_lab_header a');
        $this->db->join(DBCNL . '.kons_tr_kasbon_req_ovb_lab_detail b', 'b.id_request_ovb = a.id_request_ovb');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->where('a.sts', 1);
        $get_ovb_lab = $this->db->get()->result();

        foreach ($get_budget_subcont as $item_subcont) :
            $nilai_budget_subcont += $item_subcont->total_aktifitas_final;
        endforeach;

        foreach ($get_ovb_subcont as $item_ovb_subcont) {
            $nilai_budget_subcont += $item_ovb_subcont->ttl;
        }

        foreach ($get_budget_akomodasi as $item_akomodasi) :
            $nilai_budget_akomodasi += $item_akomodasi->total_final;
        endforeach;

        foreach ($get_ovb_akomodasi as $item_ovb_akomodasi) {
            $nilai_budget_akomodasi += $item_ovb_akomodasi->ttl;
        }

        foreach ($get_budget_others as $item_others) :
            $nilai_budget_others += $item_others->total_final;
        endforeach;

        foreach ($get_ovb_others as $item_ovb_others) {
            $nilai_budget_others += $item_ovb_others->ttl;
        }

        foreach ($get_budget_lab as $item_lab) :
            $nilai_budget_lab += $item_lab->total_final;
        endforeach;

        foreach ($get_ovb_lab as $item_ovb_lab) {
            $nilai_budget_lab += $item_ovb_lab->ttl;
        }

        $nilai_budget_subcont_on_process = 0;
        $nilai_budget_akomodasi_on_process = 0;
        $nilai_budget_others_on_process = 0;
        $nilai_budget_lab_on_process = 0;

        $this->db->select('a.total_pengajuan');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_subcont a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->where('a.sts', null);
        $get_nilai_budget_subcont_on_process = $this->db->get()->result();

        $this->db->select('a.total_pengajuan');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_akomodasi a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->where('a.sts', null);
        $get_nilai_budget_akomodasi_on_process = $this->db->get()->result();

        $this->db->select('a.total_pengajuan');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_others a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->where('a.sts', null);
        $get_nilai_budget_others_on_process = $this->db->get()->result();

        $this->db->select('a.total_pengajuan');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_lab a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->where('a.sts', null);
        $get_nilai_budget_lab_on_process = $this->db->get()->result();

        foreach ($get_nilai_budget_subcont_on_process as $item) {
            $nilai_budget_subcont_on_process += $item->total_pengajuan;
        }
        foreach ($get_nilai_budget_akomodasi_on_process as $item) {
            $nilai_budget_akomodasi_on_process += $item->total_pengajuan;
        }
        foreach ($get_nilai_budget_others_on_process as $item) {
            $nilai_budget_others_on_process += $item->total_pengajuan;
        }
        foreach ($get_nilai_budget_lab_on_process as $item) {
            $nilai_budget_lab_on_process += $item->total_pengajuan;
        }

        echo json_encode([
            'nilai_budget_subcont' => $nilai_budget_subcont,
            'nilai_budget_akomodasi' => $nilai_budget_akomodasi,
            'nilai_budget_others' => $nilai_budget_others,
            'nilai_budget_lab' => $nilai_budget_lab,
            'nilai_budget_subcont_aktual' => $nilai_budget_subcont_on_process,
            'nilai_budget_akomodasi_aktual' => $nilai_budget_akomodasi_on_process,
            'nilai_budget_others_aktual' => $nilai_budget_others_on_process,
            'nilai_budget_lab_aktual' => $nilai_budget_lab_on_process
        ]);
    }

    public function save_request_budget_akomodasi()
    {
        $post = $this->input->post();

        $this->db->trans_begin();

        $id_request_ovb = $this->Kasbon_project_model->generate_id_req_ovb_akomodasi();

        $data_header = [
            'id_request_ovb' => $id_request_ovb,
            'id_spk_budgeting' => $post['id_spk_budgeting'],
            'id_spk_penawaran' => $post['id_spk_penawaran'],
            'id_penawaran' => $post['id_penawaran'],
            'tipe' => 2,
            'created_by' => $this->auth->user_id(),
            'created_date' => date('Y-m-d H:i:s')
        ];

        $data_detail = [];
        if (isset($post['req_akomodasi'])) {
            foreach ($post['req_akomodasi'] as $item) {
                $qty_budget_tambahan = str_replace(',', '', $item['qty_budget_tambahan']);
                $budget_tambahan = str_replace(',', '', $item['budget_tambahan']);
                if ($qty_budget_tambahan > 0) {
                    $data_detail[] = [
                        'id_request_ovb' => $id_request_ovb,
                        'id_detail' => $item['id_detail'],
                        'id_item' => $item['id_item'],
                        'nm_item' => $item['nm_item'],
                        'qty_estimasi' => str_replace(',', '', $item['qty_estimasi']),
                        'price_unit_estimasi' => str_replace(',', '', $item['price_unit_estimasi']),
                        'total_budget_estimasi' => str_replace(',', '', $item['total_budget']),
                        'qty_budget_tambahan' => str_replace(',', '', $item['qty_budget_tambahan']),
                        'budget_tambahan' => str_replace(',', '', $item['budget_tambahan']),
                        'pengajuan_budget' => str_replace(',', '', $item['pengajuan_new_budget']),
                        'reason' => $item['reason'],
                        'created_by' => $this->auth->user_id(),
                        'created_date' => date('Y-m-d H:i:s')
                    ];
                }
            }
        }

        if (!empty($data_detail)) {
            $insert_header = $this->db->insert('kons_tr_kasbon_req_ovb_akomodasi_header', $data_header);
            if (!$insert_header) {
                $this->db->trans_rollback();

                print_r('Query Header - ' . $this->db->error($insert_header));
                exit;
            }

            $insert_detail = $this->db->insert_batch('kons_tr_kasbon_req_ovb_akomodasi_detail', $data_detail);
            if (!$insert_detail) {
                $this->db->trans_rollback();

                print_r('Query Detail - ' . $this->db->error($insert_detail));
                exit;
            }
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $pesan = 'Please try again later !';
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $pesan = 'Data has been saved !';
        }

        echo json_encode([
            'status' => $valid,
            'pesan' => $pesan
        ]);
    }

    public function del_ovb_akomodasi()
    {
        $id_request_ovb = $this->input->post('id_request_ovb');

        $this->db->trans_begin();

        $del_request_ovb_header = $this->db->delete('kons_tr_kasbon_req_ovb_akomodasi_header', ['id_request_ovb' => $id_request_ovb]);
        if (!$del_request_ovb_header) {
            $this->db->trans_rollback();

            print_r($this->db->error($del_request_ovb_header));
            exit;
        }

        $del_request_ovb_detail = $this->db->delete('kons_tr_kasbon_req_ovb_akomodasi_detail', ['id_request_ovb' => $id_request_ovb]);
        if (!$del_request_ovb_detail) {
            $this->db->trans_rollback();

            print_r($this->db->error($del_request_ovb_detail));
            exit;
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $pesan = 'Please try again later !';
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $pesan = 'Data has been deleted !';
        }

        echo json_encode([
            'status' => $valid,
            'pesan' => $pesan
        ]);
    }

    public function del_ovb_subcont()
    {
        $id_request_ovb = $this->input->post('id_request_ovb');

        $this->db->trans_begin();

        $del_request_ovb_header = $this->db->delete('kons_tr_kasbon_req_ovb_subcont_header', ['id_request_ovb' => $id_request_ovb]);
        if (!$del_request_ovb_header) {
            $this->db->trans_rollback();

            print_r($this->db->error($del_request_ovb_header));
            exit;
        }

        $del_request_ovb_detail = $this->db->delete('kons_tr_kasbon_req_ovb_subcont_detail', ['id_request_ovb' => $id_request_ovb]);
        if (!$del_request_ovb_detail) {
            $this->db->trans_rollback();

            print_r($this->db->error($del_request_ovb_detail));
            exit;
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $pesan = 'Please try again later !';
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $pesan = 'Data has been deleted !';
        }

        echo json_encode([
            'status' => $valid,
            'pesan' => $pesan
        ]);
    }

    public function del_ovb_others()
    {
        $id_request_ovb = $this->input->post('id_request_ovb');

        $this->db->trans_begin();

        $del_request_ovb_header = $this->db->delete('kons_tr_kasbon_req_ovb_others_header', ['id_request_ovb' => $id_request_ovb]);
        if (!$del_request_ovb_header) {
            $this->db->trans_rollback();

            print_r($this->db->error($del_request_ovb_header));
            exit;
        }

        $del_request_ovb_detail = $this->db->delete('kons_tr_kasbon_req_ovb_others_detail', ['id_request_ovb' => $id_request_ovb]);
        if (!$del_request_ovb_detail) {
            $this->db->trans_rollback();

            print_r($this->db->error($del_request_ovb_detail));
            exit;
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $pesan = 'Please try again later !';
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $pesan = 'Data has been deleted !';
        }

        echo json_encode([
            'status' => $valid,
            'pesan' => $pesan
        ]);
    }

    public function del_ovb_lab()
    {
        $id_request_ovb = $this->input->post('id_request_ovb');

        $this->db->trans_begin();

        $del_request_ovb_header = $this->db->delete('kons_tr_kasbon_req_ovb_lab_header', ['id_request_ovb' => $id_request_ovb]);
        if (!$del_request_ovb_header) {
            $this->db->trans_rollback();

            print_r($this->db->error($del_request_ovb_header));
            exit;
        }

        $del_request_ovb_detail = $this->db->delete('kons_tr_kasbon_req_ovb_lab_detail', ['id_request_ovb' => $id_request_ovb]);
        if (!$del_request_ovb_detail) {
            $this->db->trans_rollback();

            print_r($this->db->error($del_request_ovb_detail));
            exit;
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $pesan = 'Please try again later !';
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $pesan = 'Data has been deleted !';
        }

        echo json_encode([
            'status' => $valid,
            'pesan' => $pesan
        ]);
    }

    public function approval_req_ovb()
    {
        $id_request_ovb = $this->input->post('id_request_ovb');

        $this->db->trans_begin();

        $app_request_ovb_header = $this->db->update('kons_tr_kasbon_req_ovb_akomodasi_header', ['sts' => 1], ['id_request_ovb' => $id_request_ovb]);
        if (!$app_request_ovb_header) {
            $this->db->trans_rollback();

            print_r($this->db->error($app_request_ovb_header));
            exit;
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $pesan = 'Please try again later !';
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $pesan = 'Data has been deleted !';
        }

        echo json_encode([
            'status' => $valid,
            'pesan' => $pesan
        ]);
    }

    public function req_approval_kasbon()
    {
        $id_spk_budgeting = $this->input->post('id_spk_budgeting');

        $this->db->trans_begin();

        $data_insert = [];

        $this->db->select('a.id as id_header');
        $this->db->from(DBCNL . '.kons_tr_kasbon_project_header a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $this->db->where('a.sts', null);
        $get_kasbon_header = $this->db->get()->result();

        foreach ($get_kasbon_header as $item) {
            $data_insert[] = [
                'id_spk_budgeting' => $id_spk_budgeting,
                'id_kasbon' => $item->id_header,
                'created_by' => $this->auth->user_id(),
                'created_date' => date('Y-m-d H:i:s')
            ];
        }

        $insert_req = $this->db->insert_batch('kons_tr_req_kasbon_project', $data_insert);
        if (!$insert_req) {
            $this->db->trans_rollback();

            print_r($this->db->error($insert_req));
            exit;
        }

        $update_kasbon_header = $this->db->update('kons_tr_kasbon_project_header', ['sts_reject' => null, 'sts_reject_manage' => null, 'reject_reason' => null], ['id_spk_budgeting' => $id_spk_budgeting, 'sts' => null]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $pesan = 'Please try again later !';
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $pesan = 'Data has been requested approval !';
        }

        echo json_encode([
            'status' => $valid,
            'pesan' => $pesan
        ]);
    }

    public function req_approve_kasbon()
    {
        $post = $this->input->post();

        $this->db->trans_begin();

        $update_req = $this->db->update('kons_tr_kasbon_project_header', ['sts_req' => 1], ['id' => $post['id']]);
        if (!$update_req) {
            $this->db->trans_rollback();

            print_r($this->db->error($update_req));
            exit;
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $pesan = 'Please, try again later !';
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $pesan = 'Data has been moved to Waiting Approval !';
        }

        echo json_encode([
            'status' => $valid,
            'pesan' => $pesan
        ]);
    }

    public function add_request_budget_subcont($id_spk_budgeting)
    {
        $id_spk_budgeting = urldecode($id_spk_budgeting);
        $id_spk_budgeting = str_replace('|', '/', $id_spk_budgeting);

        $this->db->select('a.*, b.nm_sales, b.waktu_from, b.waktu_to');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting a');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran b', 'b.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_list_spk_budgeting = $this->db->get()->row();

        $this->db->select('a.*');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting_aktifitas a');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_list_kasbon_subcont = $this->db->get()->result();

        $data = [
            'list_budgeting' => $get_list_spk_budgeting,
            'list_kasbon_subcont' => $get_list_kasbon_subcont
        ];

        $this->template->set($data);
        $this->template->render('add_request_budget_subcont');
    }

    public function save_request_budget_subcont()
    {
        $post = $this->input->post();

        $this->db->trans_begin();

        $id_request_ovb = $this->Kasbon_project_model->generate_id_req_ovb_subcont();

        $data_header = [
            'id_request_ovb' => $id_request_ovb,
            'id_spk_budgeting' => $post['id_spk_budgeting'],
            'id_spk_penawaran' => $post['id_spk_penawaran'],
            'id_penawaran' => $post['id_penawaran'],
            'tipe' => 1,
            'created_by' => $this->auth->user_id(),
            'created_date' => date('Y-m-d H:i:s')
        ];

        $data_detail = [];
        if (isset($post['req_subcont'])) {
            foreach ($post['req_subcont'] as $item) {
                $qty_budget_tambahan = str_replace(',', '', $item['qty_budget_tambahan']);
                $budget_tambahan = str_replace(',', '', $item['budget_tambahan']);
                if ($qty_budget_tambahan > 0) {
                    $data_detail[] = [
                        'id_request_ovb' => $id_request_ovb,
                        'id_detail' => $item['id_detail'],
                        'id_aktifitas' => $item['id_aktifitas'],
                        'nm_aktifitas' => $item['nm_aktifitas'],
                        'qty_estimasi' => str_replace(',', '', $item['qty_estimasi']),
                        'price_unit_estimasi' => str_replace(',', '', $item['price_unit_estimasi']),
                        'total_budget_estimasi' => str_replace(',', '', $item['total_budget']),
                        'qty_budget_tambahan' => str_replace(',', '', $item['qty_budget_tambahan']),
                        'budget_tambahan' => str_replace(',', '', $item['budget_tambahan']),
                        'pengajuan_budget' => str_replace(',', '', $item['pengajuan_new_budget']),
                        'reason' => $item['reason'],
                        'created_by' => $this->auth->user_id(),
                        'created_date' => date('Y-m-d H:i:s')
                    ];
                }
            }
        }

        $data_custom_subcont = [];
        if (isset($post['custom_subcont'])) {
            if (!empty($post['custom_subcont'])) {
                foreach ($post['custom_subcont'] as $item) :
                    $data_custom_subcont[] = [
                        'id_spk_budgeting' => $item['id_spk_budgeting'],
                        'id_spk_penawaran' => $item['id_spk_penawaran'],
                        'id_penawaran' => $item['id_penawaran'],
                        'nm_item' => $item['nm_item'],
                        'estimasi_qty' => $item['estimasi_qty'],
                        'estimasi_harga' => str_replace(',', '', $item['estimasi_harga']),
                        'estimasi_total' => str_replace(',', '', $item['estimasi_total']),
                        'qty_budget' => $item['qty_budget_tambahan'],
                        'price_budget' => str_replace(',', '', $item['price_budget_tambahan']),
                        'total_budget' => str_replace(',', '', $item['total_budget_tambahan']),
                        'reason' => $item['reason'],
                        'created_by' => $this->auth->user_id(),
                        'created_date' => date('Y-m-d H:i:s')
                    ];
                endforeach;
            }
        }

        if (!empty($data_detail)) {
            $insert_header = $this->db->insert('kons_tr_kasbon_req_ovb_subcont_header', $data_header);
            if (!$insert_header) {
                $this->db->trans_rollback();

                print_r('Query Header - ' . $this->db->last_query());
                exit;
            }

            $insert_detail = $this->db->insert_batch('kons_tr_kasbon_req_ovb_subcont_detail', $data_detail);
            if (!$insert_detail) {
                $this->db->trans_rollback();

                print_r('Query Detail - ' . $this->db->error($insert_detail));
                exit;
            }
        }

        if (!empty($data_custom_subcont)) {
            $insert_custom_subcont = $this->db->insert_batch('kons_tr_kasbon_custom_ovb_subcont', $data_custom_subcont);
            if (!$insert_custom_subcont) {
                $this->db->trans_rollback();

                print_r('Query Detail Custom Subcont - ' . $this->db->last_query());
                exit;
            }
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $pesan = 'Please try again later !';
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $pesan = 'Data has been saved !';
        }

        echo json_encode([
            'status' => $valid,
            'pesan' => $pesan
        ]);
    }

    public function add_request_budget_others($id_spk_budgeting)
    {
        $id_spk_budgeting = urldecode($id_spk_budgeting);
        $id_spk_budgeting = str_replace('|', '/', $id_spk_budgeting);

        $this->db->select('a.*, b.nm_sales, b.waktu_from, b.waktu_to');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting a');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran b', 'b.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_list_spk_budgeting = $this->db->get()->row();

        $this->db->select('a.*, b.nm_biaya');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting_others a');
        $this->db->join(DBCNL . '.kons_master_biaya b', 'b.id = a.id_item', 'left');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_list_kasbon_others = $this->db->get()->result();

        $data = [
            'list_budgeting' => $get_list_spk_budgeting,
            'list_kasbon_akomodasi' => $get_list_kasbon_others
        ];

        $this->template->set($data);
        $this->template->render('add_request_budget_others');
    }

    public function add_request_budget_lab($id_spk_budgeting)
    {
        $id_spk_budgeting = urldecode($id_spk_budgeting);
        $id_spk_budgeting = str_replace('|', '/', $id_spk_budgeting);

        $this->db->select('a.*, b.nm_sales, b.waktu_from, b.waktu_to');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting a');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran b', 'b.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_list_spk_budgeting = $this->db->get()->row();

        $this->db->select('a.*, b.nm_biaya');
        $this->db->from(DBCNL . '.kons_tr_spk_budgeting_lab a');
        $this->db->join(DBCNL . '.kons_master_biaya b', 'b.id = a.id_item', 'left');
        $this->db->where('a.id_spk_budgeting', $id_spk_budgeting);
        $get_list_kasbon_lab = $this->db->get()->result();

        $data = [
            'list_budgeting' => $get_list_spk_budgeting,
            'list_kasbon_akomodasi' => $get_list_kasbon_lab
        ];

        $this->template->set($data);
        $this->template->render('add_request_budget_lab');
    }

    public function save_request_budget_others()
    {
        $post = $this->input->post();

        $this->db->trans_begin();

        $id_request_ovb = $this->Kasbon_project_model->generate_id_req_ovb_others();

        $data_header = [
            'id_request_ovb' => $id_request_ovb,
            'id_spk_budgeting' => $post['id_spk_budgeting'],
            'id_spk_penawaran' => $post['id_spk_penawaran'],
            'id_penawaran' => $post['id_penawaran'],
            'tipe' => 3,
            'created_by' => $this->auth->user_id(),
            'created_date' => date('Y-m-d H:i:s')
        ];

        $data_detail = [];
        if (isset($post['req_others'])) {
            foreach ($post['req_others'] as $item) {
                $qty_budget_tambahan = str_replace(',', '', $item['qty_budget_tambahan']);
                $budget_tambahan = str_replace(',', '', $item['budget_tambahan']);
                if ($qty_budget_tambahan > 0) {
                    $data_detail[] = [
                        'id_request_ovb' => $id_request_ovb,
                        'id_detail' => $item['id_detail'],
                        'id_item' => $item['id_item'],
                        'nm_item' => $item['nm_item'],
                        'qty_estimasi' => str_replace(',', '', $item['qty_estimasi']),
                        'price_unit_estimasi' => str_replace(',', '', $item['price_unit_estimasi']),
                        'total_budget_estimasi' => str_replace(',', '', $item['total_budget']),
                        'qty_budget_tambahan' => str_replace(',', '', $item['qty_budget_tambahan']),
                        'budget_tambahan' => str_replace(',', '', $item['budget_tambahan']),
                        'pengajuan_budget' => str_replace(',', '', $item['pengajuan_new_budget']),
                        'reason' => $item['reason'],
                        'created_by' => $this->auth->user_id(),
                        'created_date' => date('Y-m-d H:i:s')
                    ];
                }
            }
        }

        if (!empty($data_detail)) {
            $insert_header = $this->db->insert('kons_tr_kasbon_req_ovb_others_header', $data_header);
            if (!$insert_header) {
                $this->db->trans_rollback();

                print_r('Query Header - ' . $this->db->error($insert_header));
                exit;
            }

            $insert_detail = $this->db->insert_batch('kons_tr_kasbon_req_ovb_others_detail', $data_detail);
            if (!$insert_detail) {
                $this->db->trans_rollback();

                print_r('Query Detail - ' . $this->db->error($insert_detail));
                exit;
            }
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $pesan = 'Please try again later !';
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $pesan = 'Data has been saved !';
        }

        echo json_encode([
            'status' => $valid,
            'pesan' => $pesan
        ]);
    }

    public function save_request_budget_lab()
    {
        $post = $this->input->post();

        $this->db->trans_begin();

        $id_request_ovb = $this->Kasbon_project_model->generate_id_req_ovb_lab();

        $data_header = [
            'id_request_ovb' => $id_request_ovb,
            'id_spk_budgeting' => $post['id_spk_budgeting'],
            'id_spk_penawaran' => $post['id_spk_penawaran'],
            'id_penawaran' => $post['id_penawaran'],
            'tipe' => 4,
            'created_by' => $this->auth->user_id(),
            'created_date' => date('Y-m-d H:i:s')
        ];

        $data_detail = [];
        if (isset($post['req_lab'])) {
            foreach ($post['req_lab'] as $item) {
                $qty_budget_tambahan = str_replace(',', '', $item['qty_budget_tambahan']);
                $budget_tambahan = str_replace(',', '', $item['budget_tambahan']);
                if ($qty_budget_tambahan > 0) {
                    $data_detail[] = [
                        'id_request_ovb' => $id_request_ovb,
                        'id_detail' => $item['id_detail'],
                        'id_item' => $item['id_item'],
                        'nm_item' => $item['nm_item'],
                        'qty_estimasi' => str_replace(',', '', $item['qty_estimasi']),
                        'price_unit_estimasi' => str_replace(',', '', $item['price_unit_estimasi']),
                        'total_budget_estimasi' => str_replace(',', '', $item['total_budget']),
                        'qty_budget_tambahan' => str_replace(',', '', $item['qty_budget_tambahan']),
                        'budget_tambahan' => str_replace(',', '', $item['budget_tambahan']),
                        'pengajuan_budget' => str_replace(',', '', $item['pengajuan_new_budget']),
                        'reason' => $item['reason'],
                        'created_by' => $this->auth->user_id(),
                        'created_date' => date('Y-m-d H:i:s')
                    ];
                }
            }
        }

        if (!empty($data_detail)) {
            $insert_header = $this->db->insert('kons_tr_kasbon_req_ovb_lab_header', $data_header);
            if (!$insert_header) {
                $this->db->trans_rollback();

                print_r('Query Header - ' . $this->db->error($insert_header));
                exit;
            }

            $insert_detail = $this->db->insert_batch('kons_tr_kasbon_req_ovb_lab_detail', $data_detail);
            if (!$insert_detail) {
                $this->db->trans_rollback();

                print_r('Query Detail - ' . $this->db->error($insert_detail));
                exit;
            }
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $pesan = 'Please try again later !';
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $pesan = 'Data has been saved !';
        }

        echo json_encode([
            'status' => $valid,
            'pesan' => $pesan
        ]);
    }
}
