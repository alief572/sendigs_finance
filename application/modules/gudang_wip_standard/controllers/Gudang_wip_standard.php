<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Gudang_wip_standard extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Gudang_WIP_Standard.View';
    protected $addPermission  	= 'Gudang_WIP_Standard.Add';
    protected $managePermission = 'Gudang_WIP_Standard.Manage';
    protected $deletePermission = 'Gudang_WIP_Standard.Delete';

   public function __construct()
    {
        parent::__construct();

        $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array('Gudang_wip_standard/gudang_wip_standard_model'
                                ));
        date_default_timezone_set('Asia/Bangkok');

        $this->id_user  = $this->auth->user_id();
        $this->datetime = date('Y-m-d H:i:s');
    }

    public function index(){
      $this->auth->restrict($this->viewPermission);
      $session  = $this->session->userdata('app_session');

      $listSO = $this->db->get_where('so_internal',array('deleted_date'=>NULL))->result_array();
      $data = [
        'listSO' => $listSO
      ];

      history("View data gudang wip standard");
      $this->template->title('Gudang WIP / Single Product Origa');
      $this->template->render('index',$data);
    }

    public function data_side_gudang_wip(){
  		$this->gudang_wip_standard_model->data_side_gudang_wip();
  	}
}

?>
