<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Departements extends Admin_Controller
{

	protected $viewPermission   = 'Master_Department.View';
	protected $addPermission    = 'Master_Department.Add';
	protected $managePermission = 'Master_Department.Manage';
	protected $deletePermission = 'Master_Department.Delete';

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Departements/Master_model');
	}

	public function index()
	{

		$this->auth->restrict($this->viewPermission);

		$get_Data			= $this->Master_model->getDatadept();
		$departements		= $this->Master_model->getDepartments();

		$data = array(
			'title'			=> 'Indeks Of Departements',
			'action'		=> 'index',
			'row'			=> $get_Data,
			'data_menu'		=> $departements
		);
		history('View Data Departements');
		$this->template->set($data);
		$this->template->render('index');
		// $this->load->view('Departements/index',$data);
	}
	public function add()
	{
		if ($this->input->post()) {
			$Arr_Kembali			= array();
			$data					= $this->input->post();
			$data['id']				= $this->Master_model->code_otomatis('departments', 'DEP');
			$data_session			= $this->session->userdata;
			$data['created_by']		= $data_session['User']['username'];
			$data['created']		= date('Y-m-d H:i:s');
			if ($this->Master_model->simpan('departments', $data)) {
				$Arr_Kembali		= array(
					'status'		=> 1,
					'pesan'			=> 'Add Departements Success. Thank you & have a nice day.......'
				);
				history('Add Data Departement' . $data['name']);
			} else {
				$Arr_Kembali		= array(
					'status'		=> 2,
					'pesan'			=> 'Add Departements failed. Please try again later......'
				);
			}
			echo json_encode($Arr_Kembali);
		} else {
			$controller			= ucfirst(strtolower($this->uri->segment(1)));
			$Arr_Akses			= getAcccesmenu($controller);
			if ($Arr_Akses['create'] != '1') {
				$this->session->set_flashdata("alert_data", "<div class=\"alert alert-warning\" id=\"flash-message\">You Don't Have Right To Access This Page, Please Contact Your Administrator....</div>");
				redirect(site_url('menu'));
			}
			$arr_Where			= '';
			$arr_Where2			= $this->input->post('company_id');
			$get_Data			= $this->Master_model->getCompanies($arr_Where);
			$get_Data2			= $this->Master_model->optionDivisions($arr_Where2);
			$data = array(
				'title'			=> 'Add Departements',
				'action'		=> 'add',
				'data_companies' => $get_Data,
				'data_divisions' => $get_Data2,

			);

			$this->load->view('Departements/add', $data);
		}
	}
	public function edit($id = '')
	{
		$arr_Where			= '';
		$arr_Where2			= '';
		$get_Data			= $this->Master_model->getCompanies($arr_Where);
		$get_Data2			= $this->Master_model->getDivisions($arr_Where);
		$detail				= $this->Master_model->getData(DBHRIS . '.departments', 'id', $id);
		$data = array(
			'title'			=> 'View Departements',
			'action'		=> 'edit',
			'data_companies' => $get_Data,
			'data_divisions' => $get_Data2,
			'row'			=> $detail
		);
		$this->template->set($data);
		$this->template->render('edit');
	}

	function delete($id)
	{
		$controller			= ucfirst(strtolower($this->uri->segment(1)));
		$Arr_Akses			= getAcccesmenu($controller);
		if ($Arr_Akses['delete'] != '1') {
			$this->session->set_flashdata("alert_data", "<div class=\"alert alert-warning\" id=\"flash-message\">You Don't Have Right To Access This Page, Please Contact Your Administrator....</div>");
			redirect(site_url('departements'));
		}

		$this->db->where('id', $id);
		$this->db->delete("departments");
		if ($this->db->affected_rows() > 0) {
			$this->session->set_flashdata("alert_data", "<div class=\"alert alert-success\" id=\"flash-message\">Data has been successfully deleted...........!!</div>");
			history('Delete Data Departments id' . $id);
			redirect(site_url('departements'));
		}
	}

	function getDetail($kode = '')
	{
		$Data_Array		= $this->Master_model->getArray('divisions', array('company_id' => $kode), 'id', 'name');
		echo json_encode($Data_Array);
	}
}
