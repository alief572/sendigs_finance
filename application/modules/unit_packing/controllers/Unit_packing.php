<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *
 */
class Unit_packing extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Master_Unit_Packing.View';
    protected $addPermission  	= 'Master_Unit_Packing.Add';
    protected $managePermission = 'Master_Unit_Packing.Manage';
    protected $deletePermission = 'Master_Unit_Packing.Delete';

	public function __construct()
    {
        parent::__construct();
        $this->load->model(
			array('Material/material_model')
		);
        $this->template->title('Manage Data Unit');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');
    }

	public function index(){
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		
		$data = $this->db->get_where('ms_satuan',array('deleted'=>'N','category'=>'packing'))->result();

		history("View data satuan packing");
		$this->template->set('results', $data);
		$this->template->title('Unit Packing');
		$this->template->render('index');
	}

	public function add($id = null){
		if($this->input->post()){
			$data = $this->input->post();
			
			$session 	= $this->session->userdata('app_session');
			$username 	= $session['id_user'];
			$datetime 	= date('Y-m-d H:i:s');
			
			$id 		= $data['id'];
			$code     	= trim(strtolower($data['code']));
			$nama     	= trim(strtolower($data['nama']));
			
			$field_by   = (empty($id))?'created_by':'updated_by';
			$field_date = (empty($id))?'created_date':'updated_date';
			$field_hist = (empty($id))?'Add':'Edit';

			$ArrHeader = array(
				'code'		=> $code,
				'nama'		=> $nama,
				'category'	=> 'packing',
				$field_by	=> $username,
				$field_date	=> $datetime
			);

			// print_r($ArrHeader);
			// exit;

			$this->db->trans_start();
				if(empty($id)){
					$this->db->insert('ms_satuan', $ArrHeader);
				}
				if(!empty($id)){
					$this->db->where('id', $id);
					$this->db->update('ms_satuan', $ArrHeader);
				}
			$this->db->trans_complete();

			if($this->db->trans_status() === FALSE){
				$this->db->trans_rollback();
				$Arr_Data	= array(
					'pesan'		=>'Process Failed !',
					'status'	=> 0
				);
			}
			else{
				$this->db->trans_commit();
				$Arr_Data	= array(
					'pesan'		=>'Process Success !',
					'status'	=> 1
				);
				history($field_hist." data unit ".$id);
			}

			echo json_encode($Arr_Data);
		}
		else{
			$session  = $this->session->userdata('app_session');
			$header   = $this->db->get_where('ms_satuan',array('id' => $id))->result();

			$data = [
				'header' => $header,
			];
			$this->template->title('Add Unit');
			$this->template->page_icon('fa fa-edit');
			$this->template->render('add',$data);
		}
	}

	public function hapus(){
		$data = $this->input->post();
		$session 		= $this->session->userdata('app_session');
		$code_material  = $data['id'];

		$ArrHeader		= array(
			'deleted'			  => "Y",
			'deleted_by'	  => $session['id_user'],
			'deleted_date'	=> date('Y-m-d H:i:s')
		);

		$this->db->trans_start();
			$this->db->where('id', $code_material);
			$this->db->update('ms_satuan', $ArrHeader);
		$this->db->trans_complete();

		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=>'Process Failed !',
				'status'	=> 0
			);
		}
		else{
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=>'Process Success !',
				'status'	=> 1
			);
			history("Delete data unit ".$code_material);
		}
		echo json_encode($Arr_Data);
	}
}

?>
