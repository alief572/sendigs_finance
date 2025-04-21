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

class Estimasi extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Estimasi.View';
    protected $addPermission  	= 'Estimasi.Add';
    protected $managePermission = 'Estimasi.Manage';
    protected $deletePermission = 'Estimasi.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array('Estimasi/Estimasi_model',
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
		
		if ($this->input->post()){
        $type  = $this->input->post("type");
		}
		else {
        $type = '';
		}
		
		$this->template->page_icon('fa fa-gears');
		$lvl1 = $this->Estimasi_model->get_data('ms_inventory_type');
		$deleted = '0';
		//$data = $this->Estimasi_model->find_all_by(array('id_type' =>$type));
        $data = $this->Estimasi_model->get_data_ms_estimasi($type);
		$data2 = [
			
			'inventory_1' => $lvl1,
			
		];
		
		$this->template->set('results', $data);
	    $this->template->set('result', $data2);
        $this->template->title('Price Preference');
        $this->template->render('index');
    }
	public function editInventory($id){
		$this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-edit');
		$inven = $this->db->get_where('ms_material',array('id_material' => $id))->result();
		$lvl1 = $this->Estimasi_model->get_data('ms_inventory_type');
		$lvl2 = $this->Estimasi_model->get_data('ms_inventory_category1');
		$lvl3 = $this->Estimasi_model->get_data('ms_inventory_category2');
		$lvl4 = $this->Estimasi_model->get_data('ms_inventory_category3');
		$data = [
			'inven' => $inven,
			'lvl1' => $lvl1,
			'lvl2' => $lvl2,
			'lvl3' => $lvl3,
			'lvl4' => $lvl4
		];
        $this->template->set('results', $data);
		$this->template->title('Edit Material');
        $this->template->render('edit_inventory');
		 
	}
	public function viewInventory(){
		$this->auth->restrict($this->viewPermission);
		$id 	= $this->input->post('id');
		$cust 	= $this->Inventory_4_model->getById($id);
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
	 
		$this->db->where('id_inventory2',$post['id_inventory'])->update("inven_lvl5",$data);
		
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
	public function addEstimasi()
    {
		$this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-edit');
		$inventory_1 = $this->Estimasi_model->get_data('ms_inventory_type');
		$inventory_2 = $this->Estimasi_model->get_data('ms_inventory_category1');
		$inventory_3 = $this->Estimasi_model->get_data('ms_inventory_category2');
		$inventory_4 = $this->Estimasi_model->get_data('ms_inventory_category3');
		$data = [
			'inventory_1' => $inventory_1,
			'inventory_2' => $inventory_2
		];
        $this->template->set('results', $data);
        $this->template->title('Add Price Preference');
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
		function get_inven2()
    {
        $inventory_1=$this->input->post('inventory_1');
        $data=$this->Estimasi_model->level_2($inventory_1);
        echo json_encode($data);
    }
			function get_inven3()
    {
        $inventory_2=$this->input->post('inventory_2');
        $data=$this->Estimasi_model->level_3($inventory_2);
        echo json_encode($data);
    }
	    function get_inven4()
    {
        $inventory_3=$this->input->post('inventory_3');
        $data=$this->Estimasi_model->level_4($inventory_3);
        echo json_encode($data);
    }
	public function saveNewinventory()
    {
        $this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$code = $this->Estimasi_model->generate_id();
		$this->db->trans_begin();
		$data = [
			'id_material'		=> $code,
			'id_type'		    => $post['inventory_1'],
			'id_category1'		=> $post['inventory_2'],
			'id_category2'		=> $post['inventory_3'],
			'id_category3'		=> $post['inventory_4'],
			'nama'				=> $post['nama'],
			'aktif'				=> 'aktif',
			'spec1'				=> $post['spec1'],
			'spec2'				=> $post['spec2'],
			'spec3'				=> $post['spec3'],
			'spec4'				=> $post['spec4'],
			'spec5'				=> $post['spec5'],
			'spec6'				=> $post['spec6'],
			'spec7'				=> $post['spec7'],
			'spec8'				=> $post['spec8'],
			'spec9'				=> $post['spec9'],
			'spec10'			=> $post['spec10'],
			'spec11'		    => $post['spec11'],
			'spec12'			=> $post['spec12'],
            'spec13'			=> $post['spec13'],
            'spec14'			=> $post['spec14'],
            'spec15'			=> $post['spec15'],
			'created_on'		=> date('Y-m-d H:i:s'),
			'created_by'		=> $this->auth->user_id(),
			'deleted'			=> '0'
		];
		
		$insert = $this->db->insert("ms_material",$data);
		
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
