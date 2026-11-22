<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Alokasi_pembayaran extends Admin_Controller
{
    protected $viewPermission = 'Alokasi_Pembayaran.View';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('alokasi/Alokasi_penampung_model');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        try {
            $banks = $this->Alokasi_penampung_model->banks();
        } catch (RuntimeException $e) {
            log_message('error', 'alokasi_pembayaran: gagal membaca master bank.');
            show_error('Gagal memuat daftar bank.', 500);
            return;
        }
        $this->template->title('Alokasi Pembayaran');
        $this->template->render('alokasi/penampung', array('data_bank'=>$banks, 'listing_route'=>'alokasi_pembayaran'));
    }

    public function get_alokasi()
    {
        $this->auth->restrict($this->viewPermission);
        $status = 200;
        try {
            $result = $this->Alokasi_penampung_model->listing(6, $this->input->post() ?: array());
        } catch (InvalidArgumentException $e) {
            $status = 400;
            $result = array('draw'=>0, 'recordsTotal'=>0, 'recordsFiltered'=>0, 'data'=>array(), 'error'=>$e->getMessage());
        } catch (RuntimeException $e) {
            log_message('error', 'alokasi_pembayaran: gagal membaca daftar alokasi.');
            $status = 500;
            $result = array('draw'=>0, 'recordsTotal'=>0, 'recordsFiltered'=>0, 'data'=>array(), 'error'=>'Gagal memuat data alokasi.');
        }
        $this->output->set_status_header($status)->set_content_type('application/json')->set_output(json_encode($result));
    }
}
