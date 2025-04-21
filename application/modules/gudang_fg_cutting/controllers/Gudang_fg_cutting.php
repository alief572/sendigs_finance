<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Gudang_fg_cutting extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Gudang_FG_Cutting.View';
    protected $addPermission  	= 'Gudang_FG_Cutting.Add';
    protected $managePermission = 'Gudang_FG_Cutting.Manage';
    protected $deletePermission = 'Gudang_FG_Cutting.Delete';

   public function __construct()
    {
        parent::__construct();

        $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array('Gudang_fg_cutting/Gudang_fg_cutting_model'
                                ));

        date_default_timezone_set('Asia/Bangkok');

        $this->id_user  = $this->auth->user_id();
        $this->datetime = date('Y-m-d H:i:s');
    }

    public function index(){
      $this->auth->restrict($this->viewPermission);
      $session  = $this->session->userdata('app_session');

      $this->template->title('Gudang Finish Good / Product Cutting');
      $this->template->render('index');
    }

    public function data_side_outstanding_qc(){
  		$this->Gudang_fg_cutting_model->data_side_outstanding_qc();
  	}

}