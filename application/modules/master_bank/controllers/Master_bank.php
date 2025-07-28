<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 *
 */
class Master_bank extends Admin_Controller
{
	//Permission
	protected $viewPermission 	= 'Master_Bank.View';
	protected $addPermission  	= 'Master_Bank.Add';
	protected $managePermission = 'Master_Bank.Manage';
	protected $deletePermission = 'Master_Bank.Delete';

	public function __construct()
	{
		parent::__construct();
		$this->load->model(
			array('Master_bank/Master_bank_model')
		);
		$this->template->title('Master Bank');
		$this->template->page_icon('fa fa-building-o');

		date_default_timezone_set('Asia/Bangkok');
	}

	public function index()
	{

		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');

		$this->db->select('a.*');
		$this->db->from('list_bank a');
		$get_list_bank = $this->db->get()->result();

		history("View data bank");
		$this->template->set('list_bank', $get_list_bank);
		$this->template->title('Master Bank');
		$this->template->render('index');
	}

	public function add()
	{
		$this->template->render('add');
	}

	public function get_data_bank()
	{
		$this->Master_bank_model->get_data_bank();
	}

	public function save_bank()
	{
		$post = $this->input->post();

		$this->db->trans_begin();

		if ($post['id'] !== '') {
			$arr_update = [
				'bank' => $post['bank'],
				'rekening' => $post['no_rek'],
				'nama' => $post['nama_rek'],
				'updated_by' => $this->auth->user_id(),
				'updated_date' => date('Y-m-d H:i:s')
			];

			$update_bank = $this->db->update('ms_bank', $arr_update, ['id' => $post['id']]);
			if (!$update_bank) {
				$this->db->trans_rollback();

				print_r($this->db->last_query());
				exit;
			}
		} else {
			$arr_insert = [
				'bank' => $post['bank'],
				'rekening' => $post['no_rek'],
				'nama' => $post['nama_rek'],
				'created_by' => $this->auth->user_id(),
				'created_date' => date('Y-m-d H:i:s')
			];

			$insert_bank = $this->db->insert('ms_bank', $arr_insert);
			if (!$insert_bank) {
				$this->db->trans_rollback();

				print_r($this->db->last_query());
				exit;
			}
		}


		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();

			$valid = 0;
			$msg = 'Please try again later !';
		} else {
			$this->db->trans_commit();

			$valid = 1;
			$msg = 'Save data bank success !';
		}

		$response = [
			'status' => $valid,
			'msg' => $msg
		];

		echo json_encode($response);
	}

	public function del_bank()
	{
		$id = $this->input->post('id');

		$this->db->trans_begin();

		$this->db->update('ms_bank', ['deleted' => '1', 'deleted_date' => date('Y-m-d H:i:s')], ['id' => $id]);

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();

			$valid = 0;
			$msg = 'Please try again later !';
		} else {
			$this->db->trans_commit();

			$valid = 1;
			$msg = 'Hapus data bank success !';
		}

		$response = [
			'status' => $valid,
			'msg' => $msg
		];

		echo json_encode($response);
	}

	public function EditBank()
	{
		$id = $this->input->post('id');

		$get_data_bank = $this->db->get_where('ms_bank', ['id' => $id])->row();

		$response = [
			'id' => $get_data_bank->id,
			'bank' => $get_data_bank->bank,
			'no_rek' => $get_data_bank->rekening,
			'nama_rek' => $get_data_bank->nama
		];

		echo json_encode($response);
	}
}
