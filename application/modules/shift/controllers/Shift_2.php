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

class Shift extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Shift.View';
    protected $addPermission  	= 'Shift.Add';
    protected $managePermission = 'Shift.Manage';
    protected $deletePermission = 'Shift.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array('Shift/Shift_model',
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
        $data = $this->Shift_model->get_data_idx();
        $this->template->set('results', $data);
        $this->template->title('Master Shifting');
        $this->template->render('index');
    }
	public function editShift($id){
		$this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-edit');
		$shf = $this->db->get_where('ms_shift',array('id_shift' => $id))->result();
		$divisi 	= $this->Shift_model->get_data('divisi');
		$hari 		= $this->Shift_model->get_data('ms_hari');
		$type = $this->Shift_model->get_data('ms_type_shift');
		$data = [
			'shf'	=> $shf,
			'divisi' => $divisi,
			'hari' => $hari,
			'type' => $type
		];
        $this->template->set('results', $data);
		$this->template->title('Edit Shift');
        $this->template->render('edit_shift');
		
	}
	public function viewShift(){
		$this->auth->restrict($this->viewPermission);
		$id 	= $this->input->post('id');
		$cust 	= $this->Shift_model->get_data_byid($id);
        $this->template->set('result', $cust);
		$this->template->render('view_shift');
	}
	public function saveEditShift(){
		$this->auth->restrict($this->editPermission);
		$post = $this->input->post();
		$this->db->trans_begin();
		$data = [
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
	 
		$this->db->where('id_shift',$post['id_shift'])->update("ms_shift",$data);
		
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
	public function addShift()
    {
				$this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$divisi 	= $this->Shift_model->get_data('divisi');
		$hari 		= $this->Shift_model->get_data('ms_hari');
		$type_shift = $this->Shift_model->get_data('ms_type_shift');
		$data = [
			'divisi' => $divisi,
			'hari' => $hari,
			'type_shift' => $type_shift
		];
        $this->template->set('results', $data);
        $this->template->title('Add New Shift');
        $this->template->render('add_shift');

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
        $data=$this->Inventory_3_model->level_2($inventory_1);
        echo json_encode($data);
    }
	public function saveNewshift()
    {
        $this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$code = $this->Shift_model->generate_id();
		$iddiv = $post['id_divisi'];
		$dtdiv = $this->Shift_model->get_data('divisi','id_divisi',$iddiv);
		foreach($dtdiv AS $dtdiv);
		$nmdiv = $dtdiv->nm_div;
		$this->db->trans_begin();
		$data = [
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
		
		$insert = $this->db->insert("ms_shift",$data);
		
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
