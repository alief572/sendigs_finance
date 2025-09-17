<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Master_customer_baru extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Master_customer.View';
    protected $addPermission  	= 'Master_customer.Add';
    protected $managePermission = 'Master_customer.Manage';
    protected $deletePermission = 'Master_customer.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array('Master_customer_baru/Customer_model',
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
        $data = $this->Customer_model->get_data('master_customer');
        $this->template->set('results', $data);
        $this->template->title('Customer');
        $this->template->render('index');
    }
	public function editInventory($id){
		$this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-edit');
		$inven = $this->db->get_where('inven_lvl3',array('id_inventory3' => $id))->result();
		$lvl1 = $this->Inventory_3_model->get_data('inven_lvl1');
		$lvl2 = $this->Inventory_3_model->get_data('inven_lvl2');
		$data = [
			'inven' => $inven,
			'lvl1' => $lvl1,
			'lvl2' => $lvl2
		];
        $this->template->set('results', $data);
		$this->template->title('Inventory');
        $this->template->render('edit_inventory');
		
	}
	public function viewInventory(){
		$this->auth->restrict($this->viewPermission);
		$id 	= $this->input->post('id');
		$cust 	= $this->Inventory_3_model->getById($id);
			// echo "<pre>";
			// print_r($cust);
			// echo "<pre>";
        $this->template->set('result', $cust);
		$this->template->render('view_inventory');
	}
	public function saveEditInventory(){
		$this->auth->restrict($this->editPermission);
		$post = $this->input->post();
		$this->db->trans_begin();
		$data = [
			'id_inventory2'		=> $post['id_inventory1'],
			'nm_inventory3'		=> $post['nm_inventory'],
			'aktif'				=> $post['status'],
			'modified_on'		=> date('Y-m-d H:i:s'),
			'modified_by'		=> $this->auth->user_id()
		];
	 
		$this->db->where('id_inventory2',$post['id_inventory'])->update("inven_lvl3",$data);
		
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
		}
		
  		echo json_encode($status);
	
	}
	public function addcustomer()
    {
		$this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$country = $this->Customer_model->get_data('negara');
		$kategori = $this->Customer_model->get_data('child_customer_category');
		$karyawan = $this->Customer_model->get_data('karyawan');
		$religion = $this->Customer_model->get_data('religion');
		$data = [
			'country' => $country,
			'kategori' => $kategori,
			'religion' => $religion,
			'karyawan' => $karyawan
		];
        $this->template->set('results', $data);
        $this->template->title('Add Customer');
        $this->template->set('action', $action);
        $this->template->set('id', $id);
        $this->template->render('add_customer');
    }
	public function deleteInventory(){
		$this->auth->restrict($this->deletePermission);
		$id = $this->input->post('id');
		$data = [
			'deleted' 		=> '1',
			'deleted_by' 	=> $this->auth->user_id()
		];
		
		$this->db->trans_begin();
		$this->db->where('id_inventory2',$id)->update("inven_lvl2",$data);
		
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
		}
  		echo json_encode($status);
	}
		function get_prov()
    {
        $id_negara=$this->input->post('id_negara');
        $data=$this->Customer_model->provinsi($id_negara);
        echo json_encode($data);
    }
		function get_city()
    {
        $id_prov=$this->input->post('id_prov');
        $data=$this->Customer_model->kota($id_prov);
        echo json_encode($data);
    }
	public function saveNewinventory()
    {
        $this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$code = $this->Inventory_3_model->generate_id();
		$this->db->trans_begin();
		$data = [
			'id_inventory3'		=> $code,
			'id_inventory1'		=> $post['inventory_1'],
			'id_inventory2'		=> $post['inventory_2'],
			'nm_inventory3'		=> $post['nm_inventory'],
			'aktif'				=> 'aktif',
			'created_on'		=> date('Y-m-d H:i:s'),
			'created_by'		=> $this->auth->user_id(),
			'deleted'			=> '0'
		];
		
		$insert = $this->db->insert("inven_lvl3",$data);
		
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
