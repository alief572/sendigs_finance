<?php
// Programmer Arwant 2020 Sentral Sistem
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Master_process extends Admin_Controller{
    //Permission
    protected $viewPermission 	= 'Master_process.View';
    protected $addPermission  	= 'Master_process.Add';
    protected $managePermission = 'Master_process.Manage';
    protected $deletePermission = 'Master_process.Delete';

    public function __construct(){
        parent::__construct();

        // $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array('Master_process/Master_process_model',
                                 'Aktifitas/aktifitas_model',
                                ));
        $this->template->title('Manage Data Supplier');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');
    }

    public function index(){
      $this->auth->restrict($this->viewPermission);
      $session = $this->session->userdata('app_session');
  		$this->template->page_icon('fa fa-users');
  		$deleted = '0';
      $data = $this->Master_process_model->get_data('ms_process','deleted','N');
      history("View index master process");
      $this->template->set('results', $data);
      $this->template->title('Master Process');
      $this->template->render('index');
    }

    public function add(){
      $this->auth->restrict($this->viewPermission);
      $session = $this->session->userdata('app_session');
      $id = $this->uri->segment(3);
      $header = $this->db->get_where('ms_process',array('id' => $id))->result();
      $data = [
      'header' => $header
      ];

      $this->template->page_icon('fa fa-pencil');
      $this->template->title('Add Process');
      $this->template->render('add', $data);
    }

	public function hapus(){
		$this->auth->restrict($this->deletePermission);
		$id = $this->input->post('id');
		// print_r($id);
		// exit();
		$data = [
			'deleted' 		=> 'Y',
			'deleted_by' 	=> $this->auth->user_id(),
			'deleted_date'	=> date('Y-m-d H:i:s')
		];

		$this->db->trans_start();
			$this->db->where('id', $id);
			$this->db->update('ms_process', $data);
		$this->db->trans_complete();

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
      history("Delete master process id : ".$id);
		}

  		echo json_encode($status);
	}

	public function save_data(){

		$Arr_Kembali	= array();
		$data			= $this->input->post();
		$session 		= $this->session->userdata('app_session');

		$id 			= $data['id'];
		$nm_process 	= $data['nm_process'];
		$keterangan 	= $data['keterangan'];

		$ArrHeader		= array(
		  'nm_process'	=> $data['nm_process'],
		  'keterangan'	=> $data['keterangan'],
		  'created_by'	=> $session['id_user'],
		  'created_date'	=> date('Y-m-d H:i:s')
		);
		if(!empty($id)){
			$ArrHeader		= array(
			  'nm_process'	=> $data['nm_process'],
			  'keterangan'	=> $data['keterangan'],
			  'updated_by'	=> $session['id_user'],
			  'updated_date'	=> date('Y-m-d H:i:s')
			);
		}

		$this->db->trans_start();
			if(empty($id)){
				$this->db->insert('ms_process', $ArrHeader);
        history("Insert master process name : ".$data['nm_process']);
			}
			if(!empty($id)){
				$this->db->where('id', $id);
				$this->db->update('ms_process', $ArrHeader);
        history("Update master process name : ".$id." / ".$data['nm_process']);
			}
		$this->db->trans_complete();

		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=>'Save gagal disimpan ...',
				'status'	=> 0
			);
		}
		else{
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=>'Save berhasil disimpan. Thanks ...',
				'status'	=> 1
			);
		}

		echo json_encode($Arr_Data);
	}

}
