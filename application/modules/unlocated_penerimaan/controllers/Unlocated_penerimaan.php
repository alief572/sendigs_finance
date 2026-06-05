<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Unlocated_penerimaan extends Admin_Controller
{
    protected $viewPermission = 'Unlocated Penerimaan.View';
    protected $managePermission = 'Unlocated Penerimaan.Manage';
    protected $addPermission = 'Unlocated Penerimaan.Add';
    protected $deletePermission = 'Unlocated Penerimaan.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array('Unlocated_penerimaan/Unlocated_penerimaan_model'));
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $this->db->select('a.*, b.nama_bank');
        $this->db->from('ms_bank a');
        $this->db->join('list_bank b', 'b.id = a.bank');
        $this->db->where('a.deleted', '0');
        $get_bank = $this->db->get()->result_array();

        $data['data_bank'] = $get_bank;
        $this->template->title('Alokasi');
        $this->template->render('index', $data);
    }

    public function get_unlocated_penerimaan()
    {
        $this->Unlocated_penerimaan_model->get_unlocated_penerimaan();
    }

    public function rollback()
    {
        $ids = $this->input->post('ids');

        if (empty($ids) || !is_array($ids)) {
            $response = [
                'status' => 0,
                'pesan' => 'Tidak ada data terpilih untuk di-rollback!'
            ];
            echo json_encode($response);
            return;
        }

        $rollback = $this->Unlocated_penerimaan_model->rollback_data($ids);

        if ($rollback) {
            $response = [
                'status' => 1,
                'pesan' => 'Seluruh data terpilih berhasil di-rollback ke Alokasi!'
            ];
        } else {
            $response = [
                'status' => 0,
                'pesan' => 'Gagal me-rollback data! Seluruh transaksi dibatalkan.'
            ];
        }

        echo json_encode($response);
    }
}
