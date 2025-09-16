<?php
defined('BASEPATH') or exit('No direct script access allowed');
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

		// $this->load->library(array( 'upload', 'Image_lib'));
		$this->load->model(array(
			'Costcenter/Costcenter_model',
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
		$data = $this->Costcenter_model->get_data('ms_costcenter', 'deleted', $deleted);
		$data = $this->db->select('a.*, b.kd_gudang')->join('warehouse b', 'a.id_costcenter=b.kd_gudang', 'left')->get_where('ms_costcenter a', array('a.deleted' => 0))->result();

		history("View index costcenter");
		$this->template->set('results', $data);
		$this->template->title('Costcenter');
		$this->template->render('index');
	}
	public function add()
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
		$this->template->render('add');
	}

	public function saveNewCostcenter()
	{
		$this->auth->restrict($this->addPermission);
		$session = $this->session->userdata('app_session');

		$post = $_POST['detail'];

		$this->db->trans_begin();
		$numb2 = 0;
		foreach ($_POST['detail'] as $d1) {
			$numb2++;
			$code = $this->Costcenter_model->generate_id();

			$data1 =  array(
				'id_costcenter' => $code,
				'id_dept' => '10',
				'nama_costcenter' => strtolower($d1['costcenter']),
				'shift1' => ($d1['mp_1'] != '0' and $d1['mp_1'] != '') ? 'Y' : 'N',
				'shift2' => ($d1['mp_2'] != '0' and $d1['mp_2'] != '') ? 'Y' : 'N',
				'shift3' => ($d1['mp_3'] != '0' and $d1['mp_3'] != '') ? 'Y' : 'N',
				'mp_1' => $d1['mp_1'],
				'mp_2' => $d1['mp_2'],
				'mp_3' => $d1['mp_3'],
				'deleted' => '0',
				'created_on' => date('Y-m-d H:i:s'),
				'created_by' => $session['id_user']
			);
			//Add Data
			$this->db->insert('ms_costcenter', $data1);
		}


		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Item. Thanks ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. invenThanks ...',
				'status'	=> 1
			);
			history("Insert costcenter " . $code);
		}

		echo json_encode($status);
	}

	public function edit()
	{
		$id = $this->uri->segment(3);
		$query = "SELECT * FROM ms_costcenter WHERE id='" . $id . "' LIMIT 1";
		$data = $this->db->query($query)->result();
		// print_r($data);
		$dataArr = array(
			'data' => $data
		);

		$this->template->render('edit', $dataArr);
	}

	public function edit_save()
	{
		$this->auth->restrict($this->addPermission);
		$session = $this->session->userdata('app_session');
		$d1 = $this->input->post();
		$id = $d1['id'];

		$data1 =  array(
			'nama_costcenter' => $d1['nama_costcenter'],
			'shift1' => ($d1['mp_1'] != '0' and $d1['mp_1'] != '') ? 'Y' : 'N',
			'shift2' => ($d1['mp_2'] != '0' and $d1['mp_2'] != '') ? 'Y' : 'N',
			'shift3' => ($d1['mp_3'] != '0' and $d1['mp_3'] != '') ? 'Y' : 'N',
			'mp_1' => $d1['mp_1'],
			'mp_2' => $d1['mp_2'],
			'mp_3' => $d1['mp_3'],
			'modified_on' => date('Y-m-d H:i:s'),
			'modified_by' => $session['id_user']
		);
		//Add Data
		// print_r($data1); exit;


		$this->db->trans_start();
		$this->db->where('id', $id);
		$this->db->update('ms_costcenter', $data1);
		$this->db->trans_complete();


		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'	=> 'Failed Save Item. Thanks ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'	=> 'Success Save Item. invenThanks ...',
				'status'	=> 1
			);
			history("Update costcenter " . $id);
		}

		echo json_encode($status);
	}

	public function hapus_data()
	{
		$this->auth->restrict($this->addPermission);
		$session = $this->session->userdata('app_session');
		$d1 = $this->input->post();
		$id = $this->uri->segment(3);

		$data1 =  array(
			'deleted' => 1,
			'deleted_on' => date('Y-m-d H:i:s'),
			'deleted_by' => $session['id_user']
		);
		//Add Data
		// print_r($data1); exit;


		$this->db->trans_start();
		$this->db->where('id', $id);
		$this->db->update('ms_costcenter', $data1);
		$this->db->trans_complete();


		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'	=> 'Failed Save Item. Thanks ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'	=> 'Success Save Item. invenThanks ...',
				'status'	=> 1
			);
			history("Delete costcenter " . $id);
		}

		echo json_encode($status);
	}
}
