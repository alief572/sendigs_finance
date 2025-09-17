<?php 

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/*
 * @author Ichsan
 * @copyright Copyright (c) 2019, Ichsan
 *
 * This is controller for Master Supplier
 */

class Inject_data extends Admin_Controller
{
    public function __construct()
	{
		parent::__construct();
		$this->load->library(array('Mpdf', 'upload', 'Image_lib'));
        $this->load->model(array(
            'Purchase_order/Pr_model'
		));
		$this->template->title('Manage Data Supplier');
		$this->template->page_icon('fa fa-building-o');

		date_default_timezone_set('Asia/Bangkok');
	}

	public function index()
	{
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$this->template->title('Inject Data');
		$this->template->render('index');
	}

    public function sub_mit() {
        $tanggal = $this->input->post('tanggal');

        echo json_encode([
			'id' => $this->Pr_model->generate_code($tanggal),
			'id_po' => $this->Pr_model->BuatNomor($tanggal)
		]);
    }
}
