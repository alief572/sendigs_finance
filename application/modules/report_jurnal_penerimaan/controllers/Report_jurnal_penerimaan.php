<?php
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Report_jurnal_penerimaan extends Admin_Controller
{
    protected $viewPermission     = 'Report_Jurnal_Penerimaan.View';
    protected $addPermission      = 'Report_Jurnal_Penerimaan.Add';
    protected $managePermission = 'Report_Jurnal_Penerimaan.Manage';
    protected $deletePermission = 'Report_Jurnal_Penerimaan.Delete';

    protected $consultant;
    protected $hris;

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('upload', 'Image_lib'));
        $this->load->model(array(
            'Report_jurnal_penerimaan/Report_jurnal_penerimaan_model',
            'Report_jurnal_penerimaan/Report_jurnal_penerimaan_nomor_model'
        ));
        $this->template->title('Jurnal');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');

        $this->consultant = $this->load->database('consultant', true);
        $this->hris = $this->load->database('hris', true);
    }

    public function index()
    {
        $this->consultant->select('a.id_customer, a.nm_customer');
        $this->consultant->from('customer a');
        $this->consultant->where('a.deleted', 'N');
        $get_customer = $this->consultant->get()->result();

        $this->db->select('a.id_company, a.nm_company');
        $this->db->from('tr_jurnal a');
        $this->db->where('a.jenis_transaksi', 'Penerimaan Piutang');
        $this->db->where('a.sts', '1');
        $this->db->group_by('a.id_company');
        $get_company = $this->db->get()->result();

        // $this->db->select('a.id_divisi, a.nm_divisi');
        // $this->db->from('tr_jurnal a');
        // $this->db->where('a.jenis_transaksi', 'Penerimaan Piutang');
        // $this->db->where('a.sts', '1');
        // $this->db->group_by('a.id_divisi');
        // $get_divisi = $this->db->get()->result();

        $this->hris->select('a.id as id_divisi, a.name as nm_divisi');
        $this->hris->from('divisions a');
        $get_divisi = $this->hris->get()->result();

        $data = [
            'list_customer' => $get_customer,
            'list_company' => $get_company,
            'list_divisi' => $get_divisi
        ];

        $this->template->title('Jurnal Invoicing');
        $this->template->set($data);
        $this->template->render('index');
    }

    public function view_jurnal()
    {
        $post = $this->input->post();

        $no_transaksi = $post['no_transaksi'];
        $jenis_transaksi = $post['jenis_transaksi'];

        $get_jurnal = $this->db->get_where('tr_jurnal', ['no_transaksi' => $no_transaksi, 'jenis_transaksi' => $jenis_transaksi])->result();

        $data = [
            'data_jurnal' => $get_jurnal
        ];

        $this->load->view('posting_jurnal', $data);
    }

    public function export_excel()
    {

        $tgl_from = $this->input->get('tgl_from');
        $tgl_to = $this->input->get('tgl_to');
        $client = $this->input->get('client');
        $company = $this->input->get('company');
        $divisi = $this->input->get('divisi');


        $get_data_jurnal = $this->Report_jurnal_penerimaan_model->get_jurnal_invoicing($tgl_from, $tgl_to, $client, $company, $divisi);

        $data = [
            'data_jurnal' => $get_data_jurnal
        ];

        $this->load->view('export_excel', $data);
    }

    public function get_data_jurnal_penerimaan()
    {
        $this->Report_jurnal_penerimaan_model->get_data_jurnal_penerimaan();
    }
}
