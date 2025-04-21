<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Inventory_3 extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Product_Name.View';
    protected $addPermission  	= 'Product_Name.Add';
    protected $managePermission = 'Product_Name.Manage';
    protected $deletePermission = 'Product_Name.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array('upload', 'Image_lib'));
        // $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array('Inventory_3/Inventory_3_model',
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
        $data = $this->Inventory_3_model->get_data_category2();
        history("View index product name");
        $this->template->set('results', $data);
        $this->template->title('Product Name');
        $this->template->render('index');
    }
	public function editInventory($id){
		$this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-edit');
		$inven = $this->db->get_where('ms_inventory_category2',array('id_category2' => $id))->result();
		$lvl1 = $this->Inventory_3_model->get_data('ms_inventory_type');
		$lvl2 = $this->Inventory_3_model->get_data('ms_inventory_category1');
		$data = [
			'inven' => $inven,
			'lvl1' => $lvl1,
			'lvl2' => $lvl2
		];
        $this->template->set('results', $data);
		$this->template->title('Product Name');
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
		// $this->auth->restrict($this->editPermission);
		$post = $this->input->post();
		// print_r($post);
		// exit();
		$this->db->trans_begin();
		$data = [
			'id_type'		    => $post['inventory_1'],
			'id_category1'		=> $post['inventory_2'],
			'nama'      		=> strtolower($post['nm_inventory']),
      'length'      		=> $post['length'],
      'high'      		=> $post['high'],
      'wide'      		=> $post['wide'],
      'cub'      		=> $post['cub'],
			'aktif'				=> $post['status'],
			'modified_on'		=> date('Y-m-d H:i:s'),
			'modified_by'		=> $this->auth->user_id()
		];

		$this->db->where('id_category2',$post['id_inventory'])->update("ms_inventory_category2",$data);

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
      history("Update product name : ".$post['id_inventory']." / ".$post['nm_inventory']);
		}

  		echo json_encode($status);

	}
	public function addInventory()
    {
		$this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$inventory_1 = $this->Inventory_3_model->get_data('ms_inventory_type');
		$inventory_2 = $this->Inventory_3_model->get_data('ms_inventory_category1');
		$data = [
			'inventory_1' => $inventory_1,
			'inventory_2' => $inventory_2
		];
        $this->template->set('results', $data);
        $this->template->title('Add Product Name');
        $this->template->render('add_inventory');

    }
	public function deleteInventory(){
		$this->auth->restrict($this->deletePermission);
		$id = $this->input->post('id');
		$data = [
			'deleted' 		=> '1',
			'deleted_by' 	=> $this->auth->user_id()
		];

		$this->db->trans_begin();
		$this->db->where('id_category2',$id)->update("ms_inventory_category2",$data);

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
      history("Delete product name : ".$id);
		}
  		echo json_encode($status);
	}

	function get_inven2()
    {
        $inventory_1=$_GET['inventory_1'];
        $data=$this->Inventory_3_model->level_2($inventory_1);

        // print_r($data);
        // exit();
        echo "<select id='inventory_2' name='inventory_2' class='form-control input-sm select2'>";
        echo "<option value=''>Select Project Name</option>";
                foreach ($data as $key => $st) :
				      echo "<option value='$st->id_category1' set_select('inventory_2', $st->id_category1, isset($data->id_category1) && $data->id_category1 == $st->id_category1)>".strtoupper(strtolower($st->nama))."</option>";
                endforeach;
        echo "</select>";
    }

	public function saveNewinventory()
    {
        $this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$code = $this->Inventory_3_model->generate_id();
		$this->db->trans_begin();
		$data = [
			'id_category2'		=> $code,
			'id_type'		    => $post['inventory_1'],
			'id_category1'		=> $post['inventory_2'],
			'nama'      		=> strtolower($post['nm_inventory']),
      'length'      		=> $post['length'],
      'high'      		=> $post['high'],
      'wide'      		=> $post['wide'],
      'cub'      		=> $post['cub'],
			'aktif'				=> 'aktif',
			'created_on'		=> date('Y-m-d H:i:s'),
			'created_by'		=> $this->auth->user_id(),
			'deleted'			=> '0'
		];

		$insert = $this->db->insert("ms_inventory_category2",$data);

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
        history("Insert product name : ".$code." / ".$post['nm_inventory']);
		}

  		echo json_encode($status);

    }

}
