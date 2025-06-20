<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Actual_plan_tagih extends Admin_Controller
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
            'Actual_plan_tagih/Actual_plan_tagih_model'
        ));
        $this->template->title('Actual_plan_tagih');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $this->template->title('Actual Plan Tagih');
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

    public function aktual_tagihan_get()
    {
        $id = $this->input->post('id');

        $get_plan_tagih_detail  = $this->db->get_where('kons_tr_plan_tagih_detail', array('id' => $id))->row();

        $macet = '';
        $get_actual_plan_tagih = $this->db->get_where('kons_tr_actual_plan_tagih', array('id_detail_plan_tagih' => $id))->result();
        if (!empty($get_actual_plan_tagih) && $get_actual_plan_tagih->tagih_mundur == '3') {
            $macet = '1';
        }

        $this->template->set('data_plan_tagih_detail', $get_plan_tagih_detail);
        $this->template->set('macet', $macet);
        $this->template->render('form_actual_plan_tagih');
    }

    public function aktual_tagihan_macet_get()
    {
        $id = $this->input->post('id');

        $get_plan_tagih_detail  = $this->db->get_where('kons_tr_plan_tagih_detail', array('id' => $id))->row();

        $macet = '';


        $this->template->set('data_plan_tagih_detail', $get_plan_tagih_detail);
        $this->template->set('macet', $macet);
        $this->template->render('form_actual_plan_tagih_macet');
    }

    public function save_actual_plan_tagih()
    {
        $post = $this->input->post();

        $file_surat_mundur = '';
        if (!empty($_FILES['upload_surat_mundur'])) {
            $config['upload_path']   = './uploads/surat_mundur';
            $config['allowed_types'] = '*';
            $config['max_size']      = 999999999999; // In KB
            $config['encrypt_name']  = TRUE; // Optional: encrypt the filename
            $config['remove_spaces']  = TRUE; // Optional: encrypt the filename

            $this->load->library('upload', $config);

            $this->upload->initialize($config);
            if ($this->upload->do_upload('upload_surat_mundur')) {
                $uploadData = $this->upload->data();
                $file_surat_mundur = 'uploads/surat_mundur/' . $uploadData['file_name'];
            } else {
                print_r('surat_mundur - ' . $this->upload->display_errors());
                exit;
            }
        }

        $file_laporan_progress = '';
        if (!empty($_FILES['upload_laporan_progress']['filename'])) {
            $config2['upload_path']   = './uploads/laporan_progress';
            $config2['allowed_types'] = '*';
            $config2['max_size']      = 999999999999; // In KB
            $config2['encrypt_name']  = TRUE; // Optional: encrypt the filename 
            $config2['remove_spaces']  = TRUE; // Optional: encrypt the filename 

            $this->load->library('upload', $config2);

            $this->upload->initialize($config2);
            if ($this->upload->do_upload('upload_laporan_progress')) {
                $uploadData2 = $this->upload->data();
                $file_laporan_progress = 'uploads/laporan_progress/' . $uploadData2['file_name'];
            } else {
                print_r('laporan progress - ' . $this->upload->display_errors());
                exit;
            }
        }

        $this->db->trans_begin();

        if ($post['macet'] == '1') {
            $arr_update = [
                'tgl_actual_plan_tagih' => $post['tgl_plan_tagih'],
                'tagih_mundur' => $post['tagih_mundur'],
                'alasan_mundur' => '',
                'file_surat_mundur' => '',
                'file_laporan_progress' => $file_laporan_progress,
                'macet' => 1
            ];
            $update_actual_plan_tagih = $this->db->update('kons_tr_actual_plan_tagih', $arr_update, array('id_detail_plan_tagih' => $post['id_detail_plan_tagih']));
            if (!$update_actual_plan_tagih) {
                $this->db->trans_rollback();

                print_r($this->db->last_query());
                exit;
            }
        } else {
            $id = $this->Actual_plan_tagih_model->generate_id();
            $arr_insert = [
                'id' => $id,
                'id_detail_plan_tagih' => $post['id_detail_plan_tagih'],
                'id_top' => $post['id_top'],
                'id_spk_penawaran' => $post['id_spk_penawaran'],
                'id_penawaran' => $post['id_penawaran'],
                'term_payment' => $post['term_payment'],
                'persen_payment' => $post['persen_payment'],
                'nominal_payment' => $post['nominal_payment'],
                'desc_payment' => $post['desc_payment'],
                'tgl_plan_tagih' => $post['tgl_plan_tagih'],
                'urutan' => $post['urutan'],
                'tanggal_actual_plan_tagih' => $post['tanggal_actual'],
                'tagih_mundur' => $post['tagih_mundur'],
                'alasan_mundur' => $post['alasan_mundur'],
                'file_surat_mundur' => $file_surat_mundur,
                'file_laporan_progress' => $file_laporan_progress,
                'created_by' => $this->auth->user_id(),
                'created_date' => date('Y-m-d H:i:s')
            ];
            if ($post['tagih_mundur'] == '3') {
                $arr_insert = [
                    'id' => $id,
                    'id_detail_plan_tagih' => $post['id_detail_plan_tagih'],
                    'id_top' => $post['id_top'],
                    'id_spk_penawaran' => $post['id_spk_penawaran'],
                    'id_penawaran' => $post['id_penawaran'],
                    'term_payment' => $post['term_payment'],
                    'persen_payment' => $post['persen_payment'],
                    'nominal_payment' => $post['nominal_payment'],
                    'desc_payment' => $post['desc_payment'],
                    'tgl_plan_tagih' => $post['tgl_plan_tagih'],
                    'urutan' => $post['urutan'],
                    'tanggal_actual_plan_tagih' => $post['tanggal_actual'],
                    'tagih_mundur' => $post['tagih_mundur'],
                    'alasan_mundur' => $post['alasan_mundur'],
                    'file_surat_mundur' => $file_surat_mundur,
                    'file_laporan_progress' => $file_laporan_progress,
                    'macet' => 1,
                    'created_by' => $this->auth->user_id(),
                    'created_date' => date('Y-m-d H:i:s')
                ];
            } else {
                $arr_insert = [
                    'id' => $id,
                    'id_detail_plan_tagih' => $post['id_detail_plan_tagih'],
                    'id_top' => $post['id_top'],
                    'id_spk_penawaran' => $post['id_spk_penawaran'],
                    'id_penawaran' => $post['id_penawaran'],
                    'term_payment' => $post['term_payment'],
                    'persen_payment' => $post['persen_payment'],
                    'nominal_payment' => $post['nominal_payment'],
                    'desc_payment' => $post['desc_payment'],
                    'tgl_plan_tagih' => $post['tgl_plan_tagih'],
                    'urutan' => $post['urutan'],
                    'tanggal_actual_plan_tagih' => $post['tanggal_actual'],
                    'tagih_mundur' => $post['tagih_mundur'],
                    'alasan_mundur' => $post['alasan_mundur'],
                    'file_surat_mundur' => $file_surat_mundur,
                    'file_laporan_progress' => $file_laporan_progress,
                    'created_by' => $this->auth->user_id(),
                    'created_date' => date('Y-m-d H:i:s')
                ];
            }

            $insert_actual_plan = $this->db->insert('kons_tr_actual_plan_tagih', $arr_insert);
            if (!$insert_actual_plan) {
                $this->db->trans_rollback();

                print_r($this->db->last_query());
                exit;
            }
        }


        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $msg = 'Please try again later !';
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $msg = 'Data saved succesfully !';
        }

        echo json_encode([
            'status' => $valid,
            'msg' => $msg
        ]);
    }

    public function save_actual_plan_tagih_macet()
    {
        $post = $this->input->post();

        $file_laporan_progress = '';
        if (!empty($_FILES['upload_laporan_progress'])) {
            $config2['upload_path']   = './uploads/laporan_progress';
            $config2['allowed_types'] = '*';
            $config2['max_size']      = 999999999999; // In KB
            $config2['encrypt_name']  = TRUE; // Optional: encrypt the filename 
            $config2['remove_spaces']  = TRUE; // Optional: encrypt the filename 

            $this->load->library('upload', $config2);

            $this->upload->initialize($config2);
            if ($this->upload->do_upload('upload_laporan_progress')) {
                $uploadData2 = $this->upload->data();
                $file_laporan_progress = 'uploads/laporan_progress/' . $uploadData2['file_name'];
            } else {
                print_r('laporan progress - ' . $this->upload->display_errors());
                exit;
            }
        }

        $this->db->trans_begin();

        $arr_update = [
            'tanggal_actual_plan_tagih' => $post['tanggal_actual'],
            'tagih_mundur' => $post['tagih_mundur'],
            'alasan_mundur' => '',
            'file_surat_mundur' => '',
            'file_laporan_progress' => $file_laporan_progress
        ];
        $update_actual_plan_tagih = $this->db->update('kons_tr_actual_plan_tagih', $arr_update, array('id_detail_plan_tagih' => $post['id_detail_plan_tagih']));
        if (!$update_actual_plan_tagih) {
            $this->db->trans_rollback();

            print_r($this->db->last_query());
            exit;
        }


        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
            $msg = 'Please try again later !';
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $msg = 'Data saved succesfully !';
        }

        echo json_encode([
            'status' => $valid,
            'msg' => $msg
        ]);
    }

    public function get_actual_plan_tagih()
    {
        $this->Actual_plan_tagih_model->get_actual_plan_tagih();
    }
}
