<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Gudang extends Admin_Controller {
	public function __construct(){
    parent::__construct();
		$this->load->model('Gudang/gudang_model');

    $this->template->title('Warehouse');
    $this->template->page_icon('fa fa-building-o');
    date_default_timezone_set('Asia/Bangkok');
  }

  public function index(){
    $this->gudang_model->index();
	}

  public function server_side_mutasi_material(){
		$this->gudang_model->get_data_json_mutasi_material();
	}

  public function modal_mutasi(){
		$this->gudang_model->modal_mutasi();
	}

	public function server_side_modal_mutasi(){
		$this->gudang_model->get_data_json_modal_mutasi();
	}

	public function save_temp_mutasi(){
		$this->gudang_model->save_temp_mutasi();
	}

	public function process_mutasi(){
		$this->gudang_model->process_mutasi();
	}

	public function modal_detail_adjustment(){
		$this->gudang_model->modal_detail_adjustment();
	}

	public function get_gudang_tujuan(){
		$this->gudang_model->get_gudang_tujuan();
	}

	public function print_request(){
		$this->gudang_model->print_request();
	}

}
