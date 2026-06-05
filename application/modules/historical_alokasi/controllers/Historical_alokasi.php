<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Historical_alokasi extends Admin_Controller
{
    protected $viewPermission = 'Historical_alokasi.View';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('historical_alokasi/Historical_alokasi_model');
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
        $this->template->title('Historical Alokasi');
        $this->template->render('index', $data);
    }

    public function get_historical_alokasi()
    {
        $this->Historical_alokasi_model->get_historical_alokasi();
    }

    public function get_timeline()
    {
        $id = $this->input->post('id');
        if (empty($id)) {
            echo json_encode([
                'status' => 0,
                'msg' => 'ID not provided',
                'data' => []
            ]);
            return;
        }

        $logs = $this->Historical_alokasi_model->get_timeline($id);

        echo json_encode([
            'status' => 1,
            'data' => $logs
        ]);
    }
}
