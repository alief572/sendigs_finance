<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *
 */
class Production_report extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Daily_Report_Production.View';
    protected $addPermission  	= 'Daily_Report_Production.Add';
    protected $managePermission = 'Daily_Report_Production.Manage';
    protected $deletePermission = 'Daily_Report_Production.Delete';

   public function __construct()
    {
        parent::__construct();

        $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array('Production_report/production_report_model'
                                ));
        date_default_timezone_set('Asia/Bangkok');

        $this->id_user  = $this->auth->user_id();
        $this->datetime = date('Y-m-d H:i:s');
    }

    public function index(){
      $this->auth->restrict($this->viewPermission);
      $session  = $this->session->userdata('app_session');

      history("View data production daily report");
      $this->template->title('Produksi / Daily Production Report');
      $this->template->render('index');
    }

    public function data_side_spk_material(){
  		$this->production_report_model->data_side_spk_material();
  	}
}

?>
