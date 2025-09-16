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

class Master_karyawan extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Master_karyawan.View';
    protected $addPermission  	= 'Master_karyawan.Add';
    protected $managePermission = 'Master_karyawan.Manage';
    protected $deletePermission = 'Master_karyawan.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array('Master_karyawan/Karyawan_model',
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
        $data = $this->Karyawan_model->get_data('ms_karyawan','deleted',$deleted);
        $this->template->set('results', $data);
		$deleted2 = '1';
		$data2 = $this->Karyawan_model->get_data('divisi','deleted',$deleted2);
        $this->template->set('results2', $data2);
        $this->template->title('Karyawan');
        $this->template->render('index');
    }
	public function editKaryawan($id){
		$this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-edit');
		$karyawan = $this->db->get_where('ms_karyawan',array('id_karyawan' => $id))->result();
		$divisi = $this->Karyawan_model->get_data('divisi');
		$agama = $this->Karyawan_model->get_data('religion');
		$data = [
			'karyawan' => $karyawan,
			'divisi' => $divisi,
			'agama' => $agama
		];
        $this->template->set('results', $data);
		$this->template->title('Karyawan');
        $this->template->render('edit_karyawan');
		
	}
	public function viewKaryawan(){
		$this->auth->restrict($this->viewPermission);
		$id 	= $this->input->post('id');
		$cust 	= $this->Karyawan_model->getById($id);
			// echo "<pre>";
			// print_r($cust);
			// echo "<pre>";
        $this->template->set('result', $cust);
		$this->template->render('view_inventory');
	}
	public function saveEditKaryawan(){
		$this->auth->restrict($this->editPermission);
		$post = $this->input->post();
		$this->db->trans_begin();
		$data = [
			'nik'					=> $post['nik'],
			'nama_karyawan'			=> $post['nama_karyawan'],
			'tempat_lahir_karyawan'	=> $post['tempat_lahir_karyawan'],
			'tanggal_lahir_karyawan'=> $post['tanggal_lahir_karyawan'],
			'divisi'				=> $post['divisi'],
			'jenis_kelamin'			=> $post['gender'],
			'agama'					=> $post['agama'],
			'levelpendidikan'		=> $post['levelpendidikan'],
			'alamataktif'			=> $post['alamataktif'],
			'nohp'					=> $post['nohp'],
			'email'					=> $post['email'],
			'npwp'					=> $post['npwp'],
			'tgl_join'				=> $post['tgl_join'],
			'tgl_end'				=> $post['tgl_end'],
			'sts_karyawan'			=> $post['sts_karyawan'],
			'norekening'			=> $post['norekening'],
			'modified_on'		=> date('Y-m-d H:i:s'),
			'modified_by'		=> $this->auth->user_id()
		];
	 
		$this->db->where('id_karyawan',$post['id_karyawan'])->update("ms_karyawan",$data);
		
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
	public function addKaryawan()
    {
		$this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-edit');
		$divisi = $this->Karyawan_model->get_data('divisi');
		$religion = $this->Karyawan_model->get_data('religion');
		$data = [
			'divisi' => $divisi,
			'religion' => $religion
		];
        $this->template->set('results', $data);
        $this->template->title('Add Material');
        $this->template->render('add_karyawan');

    }
	public function addDepartement()
    {
		$this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-edit');
		$divisi = $this->Karyawan_model->get_data('divisi');
		$religion = $this->Karyawan_model->get_data('religion');
		$data = [
			'divisi' => $divisi,
			'religion' => $religion
		];
        $this->template->set('results', $data);
        $this->template->title('Add Material');
        $this->template->render('add_karyawan');

    }
	public function deleteInventory(){
		$this->auth->restrict($this->deletePermission);
		$id = $this->input->post('id');
		$data = [
			'deleted' 		=> '1',
			'deleted_by' 	=> $this->auth->user_id()
		];
		
		$this->db->trans_begin();
		$this->db->where('id_karyawan',$id)->update("ms_karyawan",$data);
		
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
	public function saveNewkaryawan()
    {
        $this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$this->db->trans_begin();
		$data = [
			'nik'					=> $post['nik'],
			'nama_karyawan'			=> $post['nama_karyawan'],
			'tempat_lahir_karyawan'	=> $post['tempat_lahir_karyawan'],
			'tanggal_lahir_karyawan'=> $post['tanggal_lahir_karyawan'],
			'divisi'				=> $post['divisi'],
			'jenis_kelamin'			=> $post['gender'],
			'agama'					=> $post['agama'],
			'levelpendidikan'		=> $post['levelpendidikan'],
			'alamataktif'			=> $post['alamataktif'],
			'nohp'					=> $post['nohp'],
			'email'					=> $post['email'],
			'npwp'					=> $post['npwp'],
			'tgl_join'				=> $post['tgl_join'],
			'tgl_end'				=> $post['tgl_end'],
			'sts_karyawan'			=> $post['sts_karyawan'],
			'norekening'			=> $post['norekening'],
			'sts_aktif'				=> 'aktif',
			'created_on'			=> date('Y-m-d H:i:s'),
			'created_by'			=> $this->auth->user_id(),
			'deleted'				=> '0'
		];
		
		$insert = $this->db->insert("ms_karyawan",$data);
		
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
	
}
