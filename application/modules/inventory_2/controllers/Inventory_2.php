<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Inventory_2 extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Project_Name.View';
    protected $addPermission  	= 'Project_Name.Add';
    protected $managePermission = 'Project_Name.Manage';
    protected $deletePermission = 'Project_Name.Delete';

    public function __construct()
    {
        parent::__construct();

        // $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array('Inventory_2/Inventory_2_model',
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
        $data = $this->Inventory_2_model->get_data_category1();
        history("View index project name");
        $this->template->set('results', $data);
        $this->template->title('Project Name');
        $this->template->render('index');
    }
	public function editInventory($id){
		$this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-edit');
		$inven = $this->db->get_where('ms_inventory_category1',array('id_category1' => $id))->result();
		$lvl1 = $this->Inventory_2_model->get_data('ms_inventory_type');
		$data = [
			'inven' => $inven,
			'lvl1' => $lvl1
		];
        $this->template->set('results', $data);
		$this->template->title('Edit Project Name');
        $this->template->render('edit_inventory');

	}
	public function viewInventory(){
		$this->auth->restrict($this->viewPermission);
		$id 	= $this->input->post('id');
		$cust 	= $this->Inventory_2_model->getById($id);
			// echo "<pre>";
			// print_r($cust);
			// echo "<pre>";
        $this->template->set('result', $cust);
		$this->template->render('view_inventory');
	}
	public function saveEditInventory(){
		$this->auth->restrict($this->editPermission);
		$post = $this->input->post();
		// print_r($post);
		// exit;
		$this->db->trans_begin();
		$data = [
			'id_type'		    => $post['inventory_1'],
			'nama'		        => strtolower($post['nm_inventory']),
			'aktif'				=> $post['status'],
			'modified_on'		=> date('Y-m-d H:i:s'),
			'modified_by'		=> $this->auth->user_id()
		];

		$this->db->where('id_category1',$post['id_inventory'])->update("ms_inventory_category1",$data);

		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$status	= array(
			  'pesan'		=>'Gagal Save Item. Thanks ...',
			  'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
			  'pesan'		=>'Success Save Item. Thanks ...',
			  'status'	=> 1
			);
      history("Update project name : ".$post['id_inventory']." / ".$post['nm_inventory']);
		}

  		echo json_encode($status);

	}
	public function addInventory()
    {
				$this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$inventory_1 = $this->Inventory_2_model->get_data('ms_inventory_type');
		$data = [
			'inventory_1' => $inventory_1
		];
        $this->template->set('results', $data);
        $this->template->title('Add Project Name');
        $this->template->render('add_inventory_2');

    }
	public function deleteInventory(){
		$this->auth->restrict($this->deletePermission);
		$id = $this->input->post('id');
		// print_r($id);
		// exit();
		$data = [
			'deleted' 		=> '1',
			'deleted_by' 	=> $this->auth->user_id()
		];

		$this->db->trans_begin();
		$this->db->where('id_category1',$id)->update("ms_inventory_category1",$data);

		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$status	= array(
			  'pesan'		=>'Gagal Save Item. Thanks ...',
			  'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
			  'pesan'		=>'Success Save Item. Thanks ...',
			  'status'	=> 1
			);
      history("Delete project name : ".$id);
		}

  		echo json_encode($status);
	}
	public function saveNewinventory()
    {
        $this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$code = $this->Inventory_2_model->generate_id();
    $urut = $this->db->query("SELECT MAX(urut) AS urut FROM ms_inventory_category1")->result();
		$this->db->trans_begin();
		$data = [
			'id_category1'	 	=> $code,
			'id_type'		    => $post['inventory_1'],
			'nama'		        => strtolower($post['nm_inventory']),
			'aktif'				=> 'aktif',
      'urut'				=> $urut[0]->urut + 1,
			'created_on'		=> date('Y-m-d H:i:s'),
			'created_by'		=> $this->auth->user_id(),
			'deleted'			=> '0'
		];

		$insert = $this->db->insert("ms_inventory_category1",$data);

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
        history("Insert project name : ".$code." / ".$post['nm_inventory']);
		}

  		echo json_encode($status);

    }

}
