<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *
 */
class Barcode extends Admin_Controller{

	public function __construct(){
        parent::__construct();
        $this->load->model(array('Costcenter/Costcenter_model',
		                         'Aktifitas/aktifitas_model',
                                ));

        date_default_timezone_set('Asia/Bangkok');
    }
	
	public function index(){
		$this->load->view('index');
	}
	
}