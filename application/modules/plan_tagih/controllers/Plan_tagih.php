<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Plan_tagih extends Admin_Controller
{
    protected $viewPermission     = 'Plan_Tagih.View';
    protected $addPermission      = 'Plan_Tagih.Add';
    protected $managePermission = 'Plan_Tagih.Manage';
    protected $deletePermission = 'Plan_Tagih.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('upload', 'Image_lib'));
        $this->load->model(array(
            'Plan_tagih/Plan_tagih_model'
        ));
        $this->template->title('Plan_tagih');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $this->template->title('Plan Tagih');
        $this->template->render('index');
    }

    public function add_plan_tagih($id_spk)
    {
        $id_spk = urldecode($id_spk);
        $id_spk = str_replace('|', '/', $id_spk);

        $this->db->select('a.*');
        $this->db->from('kons_tr_spk_penawaran a');
        $this->db->where('a.id_spk_penawaran', $id_spk);
        $get_spk_penawaran = $this->db->get()->row();

        $this->db->select('a.*');
        $this->db->from('kons_tr_spk_penawaran_payment a');
        $this->db->where('a.id_spk_penawaran', $id_spk);
        $this->db->order_by('a.dibuat_tgl', 'asc');
        $get_top_spk_penawaran = $this->db->get()->result();

        $data = [
            'data_spk_penawaran' => $get_spk_penawaran,
            'data_top_spk_penawaran' => $get_top_spk_penawaran
        ];

        $this->template->set($data);
        $this->template->title('Add Plan Tagih');
        $this->template->render('add_plan_tagih');
    }

    public function view_plan_tagih($id_spk)
    {
        $id_spk = urldecode($id_spk);
        $id_spk = str_replace('|', '/', $id_spk);

        $this->db->select('a.*');
        $this->db->from('kons_tr_spk_penawaran a');
        $this->db->where('a.id_spk_penawaran', $id_spk);
        $get_spk_penawaran = $this->db->get()->row();

        $this->db->select('a.*');
        $this->db->from('kons_tr_spk_penawaran_payment a');
        $this->db->where('a.id_spk_penawaran', $id_spk);
        $this->db->order_by('a.dibuat_tgl', 'asc');
        $get_top_spk_penawaran = $this->db->get()->result();

        $this->db->select('a.keterangan_penagihan');
        $this->db->from('kons_tr_plan_tagih_header a ');
        $this->db->where('a.id_spk_penawaran', $id_spk);
        $get_plan_tagih_header = $this->db->get()->row();

        $arr_plan_tagih = [];

        $this->db->select('a.tgl_plan_tagih, a.id_top');
        $this->db->from('kons_tr_plan_tagih_detail a');
        $this->db->where('a.id_spk_penawaran', $id_spk);
        $get_plan_tagih_detail = $this->db->get()->result();

        foreach ($get_plan_tagih_detail as $item) {
            $arr_plan_tagih[$item->id_top] = [
                'tgl_plan_tagih' => $item->tgl_plan_tagih
            ];
        }

        $data = [
            'data_spk_penawaran' => $get_spk_penawaran,
            'data_top_spk_penawaran' => $get_top_spk_penawaran,
            'data_plan_tagih_header' => $get_plan_tagih_header,
            'arr_plan_tagih' => $arr_plan_tagih
        ];

        $this->template->set($data);
        $this->template->title('View Plan Tagih');
        $this->template->render('view_plan_tagih');
    }

    public function edit_plan_tagih($id_spk)
    {
        $id_spk = urldecode($id_spk);
        $id_spk = str_replace('|', '/', $id_spk);

        $this->db->select('a.*');
        $this->db->from('kons_tr_spk_penawaran a');
        $this->db->where('a.id_spk_penawaran', $id_spk);
        $get_spk_penawaran = $this->db->get()->row();

        $this->db->select('a.*');
        $this->db->from('kons_tr_spk_penawaran_payment a');
        $this->db->where('a.id_spk_penawaran', $id_spk);
        $this->db->order_by('a.dibuat_tgl', 'asc');
        $get_top_spk_penawaran = $this->db->get()->result();

        $this->db->select('a.id, a.keterangan_penagihan');
        $this->db->from('kons_tr_plan_tagih_header a ');
        $this->db->where('a.id_spk_penawaran', $id_spk);
        $get_plan_tagih_header = $this->db->get()->row();

        $arr_plan_tagih = [];

        $this->db->select('a.tgl_plan_tagih, a.id, a.id_top');
        $this->db->from('kons_tr_plan_tagih_detail a');
        $this->db->where('a.id_spk_penawaran', $id_spk);
        $get_plan_tagih_detail = $this->db->get()->result();

        foreach ($get_plan_tagih_detail as $item) {
            $arr_plan_tagih[$item->id_top] = [
                'id_plan_tagih' => $item->id,
                'tgl_plan_tagih' => $item->tgl_plan_tagih
            ];
        }

        $data = [
            'data_spk_penawaran' => $get_spk_penawaran,
            'data_top_spk_penawaran' => $get_top_spk_penawaran,
            'data_plan_tagih_header' => $get_plan_tagih_header,
            'arr_plan_tagih' => $arr_plan_tagih
        ];

        $this->template->set($data);
        $this->template->title('Revisi Plan Tagih');
        $this->template->render('revisi_plan_tagih');
    }

    public function save_plan_tagih()
    {
        $post = $this->input->post();

        $id = $this->Plan_tagih_model->generate_id();

        $arr_header = [
            'id' => $id,
            'id_spk_penawaran' => $post['id_spk_penawaran'],
            'id_penawaran' => $post['id_penawaran'],
            'id_customer' => $post['id_customer'],
            'nm_customer' => $post['nm_customer'],
            'id_project' => $post['id_project'],
            'nm_project' => $post['nm_project'],
            'id_project_leader' => $post['id_project_leader'],
            'nm_project_leader' => $post['nm_project_leader'],
            'nilai_bersih_project' => $post['nilai_bersih_project'],
            'keterangan_penagihan' => $post['keterangan_penagihan'],
            'created_by' => $this->auth->user_id(),
            'created_date' => date('Y-m-d H:i:s')
        ];

        $arr_detail = [];
        $no_detail = 0;
        foreach ($post['dt'] as $item) {
            $no_detail++;
            $data_top = $this->db->get_where('kons_tr_spk_penawaran_payment', array('id' => $item['id']))->row();

            $arr_detail[] = [
                'id_spk_penawaran' => $post['id_spk_penawaran'],
                'id_header' => $id,
                'id_penawaran' => $post['id_penawaran'],
                'id_top' => $item['id'],
                'term_payment' => $data_top->term_payment,
                'persen_payment' => $data_top->persen_payment,
                'nominal_payment' => $data_top->nominal_payment,
                'desc_payment' => $data_top->desc_payment,
                'tgl_plan_tagih' => $item['tgl_plan_tagih'],
                'urutan' => $no_detail,
                'created_by' => $this->auth->user_id(),
                'created_date' => date('Y-m-d H:i:s')
            ];
        }

        $this->db->trans_begin();

        $insert_header = $this->db->insert('kons_tr_plan_tagih_header', $arr_header);
        if (!$insert_header) {
            $this->db->trans_rollback();

            print_r($this->db->last_query());
            exit;
        }

        $insert_detail = $this->db->insert_batch('kons_tr_plan_tagih_detail', $arr_detail);
        if (!$insert_detail) {
            $this->db->trans_rollback();

            print_r($this->db->last_query());
            exit;
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $msg = 'Please, try again later !';
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $msg = 'New Plan Tagih has been saved !';
        }

        echo json_encode([
            'status' => $valid,
            'msg' => $msg
        ]);
    }

    public function revisi_plan_tagih()
    {
        $post = $this->input->post();

        $id_header = $post['id_header'];

        $arr_update_header = [
            'keterangan_penagihan' => $post['keterangan_penagihan']
        ];

        $arr_update_detail = [];
        $no_detail = 0;
        foreach ($post['dt'] as $item) {
            $no_detail++;
            $data_top = $this->db->get_where('kons_tr_spk_penawaran_payment', array('id' => $item['id']))->row();

            $arr_update_detail[] = [
                'id' => $item['id_plan_tagih'],
                'tgl_plan_tagih' => $item['tgl_plan_tagih']
            ];
        }

        $this->db->trans_begin();

        $update_header = $this->db->update('kons_tr_plan_tagih_header', $arr_update_header, array('id' => $id_header));
        if (!$update_header) {
            $this->db->trans_rollback();

            print_r($this->db->last_query());
            exit;
        }

        $update_detail = $this->db->update_batch('kons_tr_plan_tagih_detail', $arr_update_detail, 'id');
        if (!$update_detail) {
            $this->db->trans_rollback();

            print_r($this->db->last_query());
            exit;
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $msg = 'Please, try again later !';
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $msg = 'New Plan Tagih has been updated !';
        }

        echo json_encode([
            'status' => $valid,
            'msg' => $msg
        ]);
    }

    public function get_data_spk()
    {
        $this->Plan_tagih_model->get_data_spk();
    }
}
