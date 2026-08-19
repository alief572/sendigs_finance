<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Report_jurnal_payment extends Admin_Controller
{
    protected $viewPermission   = 'Report_Jurnal_Payment.View';
    protected $addPermission    = 'Report_Jurnal_Payment.Add';
    protected $managePermission = 'Report_Jurnal_Payment.Manage';
    protected $deletePermission = 'Report_Jurnal_Payment.Delete';

    protected $consultant;

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('upload', 'Image_lib'));
        $this->load->model(array(
            'Report_jurnal_payment/Report_jurnal_payment_model',
            'Report_jurnal_payment/Report_jurnal_payment_nomor_model'
        ));
        $this->template->title('Report Jurnal Payment');
        $this->template->page_icon('fa fa-book');

        date_default_timezone_set('Asia/Bangkok');

        $this->consultant = $this->load->database('consultant', true);
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $get_no_payment_jurnal = $this->Report_jurnal_payment_model->get_no_payment_jurnal();
        $get_company           = $this->Report_jurnal_payment_model->get_company();

        $data = [
            'list_no_transaksi' => $get_no_payment_jurnal,
            'list_company'      => $get_company
        ];

        $this->template->set($data);
        $this->template->title('Report Jurnal Payment');
        $this->template->render('index');
    }

    public function get_data_jurnal()
    {
        $this->Report_jurnal_payment_model->get_data_jurnal();
    }

    public function view_jurnal()
    {
        $post = $this->input->post();

        $no_transaksi    = $post['no_transaksi'] ?? '';
        $jenis_transaksi = $post['jenis_transaksi'] ?? '';

        $get_jurnal = $this->Report_jurnal_payment_model->get_detail_jurnal($no_transaksi, $jenis_transaksi);

        $data = [
            'data_jurnal'     => $get_jurnal,
            'no_transaksi'    => $no_transaksi,
            'jenis_transaksi' => $jenis_transaksi
        ];

        $this->load->view('view_jurnal', $data);
    }

    public function export_excel()
    {
        $tgl_jurnal = $this->input->get('tgl_jurnal');
        $tgl_from   = '';
        $tgl_to     = '';
        if (!empty($tgl_jurnal)) {
            $exp_tgl_jurnal = explode(' to ', $tgl_jurnal);
            $tgl_from = $exp_tgl_jurnal[0] ?? '';
            $tgl_to   = $exp_tgl_jurnal[1] ?? '';
        }

        $no_transaksi = $this->input->get('no_transaksi');
        $company      = $this->input->get('company');

        $filter = [
            'tgl_from'     => $tgl_from,
            'tgl_to'       => $tgl_to,
            'no_transaksi' => $no_transaksi,
            'company'      => $company
        ];

        $get_data = $this->Report_jurnal_payment_model->get_list_jurnal($filter);

        if (empty($get_data)) {
            echo json_encode([
                'status' => 0,
                'msg'    => 'Data tidak ditemukan !'
            ]);
            exit;
        }

        $data = [
            'list_jurnal' => $get_data
        ];

        $this->load->view('export_excel', $data);
    }

    public function export_jurnal()
    {
        $this->export_excel();
    }
}
