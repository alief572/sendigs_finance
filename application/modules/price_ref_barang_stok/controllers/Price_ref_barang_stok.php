<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Price_ref_barang_stok extends Admin_Controller
{
    // Permission
    protected $viewPermission   = 'Price_Ref_Barang_Stok.View';
    protected $addPermission    = 'Price_Ref_Barang_Stok.Add';
    protected $managePermission = 'Price_Ref_Barang_Stok.Manage';
    protected $deletePermission = 'Price_Ref_Barang_Stok.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array(
            'Price_ref_barang_stok/Price_ref_barang_stok_model',
            'All/All_model'
        ));
        $this->template->title('Price Reference >> Barang Stok');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');

        $this->id_user  = $this->auth->user_id();
        $this->datetime = date('Y-m-d H:i:s');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        history("View index approval price reference barang stok");
        $this->template->title('Price Reference >> Barang Stok (Approval)');
        $this->template->render('index');
    }

    public function get_data()
    {
        $data = $this->Price_ref_barang_stok_model->get_data_approval();
        echo json_encode($data);
    }

    public function approval($no_doc = null)
    {
        $this->auth->restrict($this->managePermission);

        $header = $this->Price_ref_barang_stok_model->get_header($no_doc);
        if (!$header) {
            $this->template->set_message("Dokumen pengajuan tidak ditemukan.", 'error');
            redirect('price_ref_barang_stok');
        }

        if ($header->status != '0') {
            $this->template->set_message("Dokumen ini sudah pernah diproses approval.", 'warning');
            redirect('price_ref_barang_stok');
        }

        $details = $this->Price_ref_barang_stok_model->get_details($no_doc);
        $files   = $this->Price_ref_barang_stok_model->get_files($no_doc);

        $data = [
            'no_doc'   => $no_doc,
            'header'   => $header,
            'details'  => $details,
            'files'    => $files
        ];

        $this->template->set($data);
        $this->template->title('Approval Pengajuan Price Reference >> ' . (!empty($header->nm_category) ? strtoupper($header->nm_category) : 'Barang Stok'));
        $this->template->render('add');
    }

    public function process_approval()
    {
        $this->auth->restrict($this->managePermission);

        $post = $this->input->post();
        $no_doc       = $post['no_doc'];
        $action       = $post['action']; // 'approve' or 'reject'
        $reason       = $post['reason'] ?? '';
        $items_custom = $post['items'] ?? [];

        if ($action == 'reject' && empty(trim($reason))) {
            echo json_encode([
                'status' => 0,
                'pesan'  => 'Harap masukkan alasan penolakan (reject reason)!'
            ]);
            return;
        }

        $result = $this->Price_ref_barang_stok_model->process_approval($no_doc, $action, $reason, $items_custom);
        echo json_encode($result);
    }

    public function view($no_doc = null)
    {
        $header  = $this->Price_ref_barang_stok_model->get_header($no_doc);
        $details = $this->Price_ref_barang_stok_model->get_details($no_doc);
        $files   = $this->Price_ref_barang_stok_model->get_files($no_doc);

        $data = [
            'header'   => $header,
            'details'  => $details,
            'files'    => $files
        ];

        $this->load->view('price_sup_barang_stok/view', $data);
    }
}


