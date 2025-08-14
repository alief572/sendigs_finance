<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Penerimaan_pph_23 extends Admin_Controller
{
    protected $viewPermission = 'Penerimaan_PPH_23.View';
    protected $managePermission = 'Penerimaan_PPH_23.Manage';
    protected $addPermission = 'Penerimaan_PPH_23.Add';
    protected $deletePermission = 'Penerimaan_PPH_23.Delete';

    protected $consultant;
    protected $accounting;

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array('Penerimaan_pph_23/Penerimaan_pph_23_model'));

        $this->consultant = $this->load->database('consultant', true);
        $this->accounting = $this->load->database('accounting', true);
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $this->template->title('Penerimaan PPH 23');
        $this->template->render('index');
    }

    public function add($id_penerimaan_piutang)
    {
        $this->auth->restrict($this->viewPermission);

        $this->db->select('a.*, b.print_keterangan, b.nm_project, b.id_penawaran');
        $this->db->from('tr_penerimaan_piutang_detail a');
        $this->db->join('tr_invoicing b', 'b.id = a.id_inv');
        $this->db->join('tr_penerimaan_piutang c', 'c.no_surat = a.id_header');
        $this->db->where('c.pph23_dipotong', 'Y');
        $this->db->where('a.id', $id_penerimaan_piutang);
        $get_data_penerimaan = $this->db->get()->row_array();

        $arr_coa_jurnal = ['1050-40-2', '1030-10-1'];

        $this->accounting->select('a.no_perkiraan, a.nama as nm_coa');
        $this->accounting->from('coa_master a');
        $this->accounting->where_in('a.no_perkiraan', $arr_coa_jurnal);
        $get_coa_jurnal = $this->accounting->get()->result_array();

        $this->consultant->select('a.id, a.nm_company');
        $this->consultant->from('kons_tr_company a');
        $this->consultant->join('kons_tr_penawaran b', 'b.company = a.id');
        $this->consultant->where('b.id_quotation', $get_data_penerimaan['id_penawaran']);
        $get_company = $this->consultant->get()->row_array();

        $id_company = (!empty($get_company)) ? $get_company['id'] : '';
        $nm_company = (!empty($get_company)) ? $get_company['nm_company'] : '';

        $data = [
            'id_detail_penerimaan' => $id_penerimaan_piutang,
            'id_company' => $id_company,
            'nm_company' => $nm_company
        ];

        $this->template->set('data_penerimaan', $get_data_penerimaan);
        $this->template->set('data_coa_jurnal', $get_coa_jurnal);
        $this->template->set($data);
        $this->template->title('Penerimaan PPH 23');
        $this->template->render('add');
    }

    public function save_penerimaan_pph_23()
    {
        $post = $this->input->post();

        $id = $this->Penerimaan_pph_23_model->generate_id();

        $filename = '';

        $config['upload_path'] = './uploads/penerimaan_pph_23/';
        $config['allowed_types'] = '*';
        $config['remove_spaces'] = TRUE;
        $config['encrypt_name'] = TRUE;

        $filenames = '';
        $this->upload->initialize($config);
        if ($this->upload->do_upload('upload_bukti_setor')) {
            $uploadData = $this->upload->data();
            $filenames = $uploadData['file_name'];
        } else {
            print_r($this->upload->display_errors());
            exit;
        }

        $arr_insert = [
            'id' => $id,
            'id_detail_penerimaan' => $post['id_detail_penerimaan'],
            'id_inv' => $post['no_invoice'],
            'id_customer' => $post['id_customer'],
            'nm_customer' => $post['customer'],
            'nm_project' => $post['project'],
            'keterangan_invoice' => $post['keterangan_invoice'],
            'nilai_pph' => str_replace(',', '', $post['nilai_pph']),
            'nilai_setor' => str_replace(',', '', $post['nilai_setor']),
            'upload_bukti_setor' => $filenames,
            'created_by' => $this->auth->user_id(),
            'created_date' => date('Y-m-d H:i:s')
        ];

        $arr_insert_jurnal = [];

        $arr_coa_jurnal = ['1050-40-2', '1030-10-1'];

        $this->db->trans_begin();

        $insert_pph_23 = $this->db->insert('tr_penerimaan_pph_23', $arr_insert);
        if (!$insert_pph_23) {
            $this->db->trans_rollback();

            print_r($this->db->last_query());
            exit;
        }

        $update_penerimaan_piutang = $this->db->update('tr_penerimaan_piutang_detail', ['sts_pph_23' => 1], ['id' => $post['id_detail_penerimaan']]);
        if (!$update_penerimaan_piutang) {
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

            $this->Penerimaan_pph_23_model->create_jurnal($id);

            $valid = 1;
            $msg = 'Data has been saved !';
        }

        $response = [
            'status' => $valid,
            'msg' => $msg
        ];

        echo json_encode($response);
    }

    public function get_alokasi_penerimaan_pph23()
    {
        $this->Penerimaan_pph_23_model->get_alokasi_penerimaan_pph23();
    }
}
