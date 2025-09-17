<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *
 */
class Costcenter extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Costcenter.View';
    protected $addPermission  	= 'Costcenter.Add'; 
    protected $managePermission = 'Costcenter.Manage';
    protected $deletePermission = 'Costcenter.Delete'; 

   public function __construct()
    {
        parent::__construct();

        $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array('Costcenter/Costcenter_model',
		                         'Aktifitas/aktifitas_model',
                                ));
        $this->template->title('Manage Data Supplier');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
       $this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$deleted = '0';
        $data = $this->Costcenter_model->get_data('ms_costcenter','deleted',$deleted);
        $this->template->set('results', $data);
        $this->template->title('Costcenter');
        $this->template->render('index');
    }
    public function addCostcenter()
    {

    		$session = $this->session->userdata('app_session');
			
			$customer    = $this->Costcenter_model->get_data('master_customer');
			$supplier    = $this->Costcenter_model->get_data('master_supplier');
			$material    = $this->Costcenter_model->get_data('ms_material');
			$sales       = $this->Costcenter_model->get_data('ms_karyawan');
			$pic         = $this->Costcenter_model->get_data('child_customer_pic');
			$data = [
			'customer' => $customer,
			'supplier' => $supplier,
			'material' => $material,
			'sales' => $sales,
			'pic' => $pic,
			];
			$this->template->set('results', $data);
		
    		$this->template->title('Add Costcenter');
            $this->template->page_icon('fa fa-edit');
    	    $this->template->title('Add Costcenter');
            $this->template->render('add_costcenter');
    }
	
	public function saveNewCostcenter()
    {
        $this->auth->restrict($this->addPermission);
		$session = $this->session->userdata('app_session');
		
		$post = $_POST['hd1']['1']['produk'];
		$numb2 =0;
		foreach($_POST['data1'] as $d1){
		$numb2++;	
		      $code = $this->Costcenter_model->generate_id();
		     	       	       
		$data1 = [
			'id_shift'		=> $code,
			'name_shift'	=> $nmdiv,
			'id_divisi'		=> $post['id_divisi'],
			'type_shift'	=> $post['type_shift'],
			'id_day'		=> $post['id_day'],
			'start_work'	=> $post['start_work'],
			'done_work'		=> $post['done_work'],
			'start_break1'	=> $post['start_break1'],
			'done_break1'	=> $post['done_break1'],
			'start_break2'	=> $post['start_break2'],
			'done_break2'	=> $post['done_break2'],
			'start_break3'	=> $post['start_break3'],
			'done_break3'	=> $post['done_break3'],
			'deleted'			=> '0'
		];
            //Add Data
              $this->db->insert('ms_costcenter',$data1);
			
		    }		
			
		
		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$status	= array(
			  'pesan'		=>'Gagal Save Item. Thanks ...',
			  'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
			  'pesan'		=>'Success Save Item. invenThanks ...',
			  'status'	=> 1
			);			
		}
		
  		echo json_encode($status);

    }

	
}

?>
