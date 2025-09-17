<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Approval_invoice_so extends Admin_Controller
{

	//Permission

	protected $viewPermission   = "Approval_Invoice_SO.View";
	protected $addPermission    = "Approval_Invoice_SO.Add";
	protected $managePermission = "Approval_Invoice_SO.Manage";
	protected $deletePermission = "Approval_Invoice_SO.Delete";

	public function __construct()
	{
		parent::__construct();
		$this->load->library(array('Mpdf', 'upload', 'Image_lib'));
		$this->load->model('Approval_invoice_so/Approval_invoice_so_model');
		$this->template->title('Billing Plan Produk');
		$this->template->page_icon('fa fa-building-o');
		date_default_timezone_set('Asia/Bangkok');
	}


	public function index()
	{
		$this->db->select('a.*, b.currency, c.nm_customer');
		$this->db->from('tr_invoice_sales a');
		$this->db->join('tr_penawaran b', 'b.no_penawaran = a.id_penawaran', 'left');
		$this->db->join('customer c', 'c.id_customer = b.id_customer', 'left');
		$this->db->where('a.sts', '');
		$this->db->or_where('a.sts', null);
		$get_result = $this->db->get()->result();

		$this->template->set('results', $get_result);
		$this->template->title('Approval Invoicing');
		$this->template->render('index');
	}

	public function approve_invoice()
	{
		$id_invoice = $this->input->post('id_invoice');

		$this->db->trans_begin();

		$data_update = [
			'sts' => 1,
			'app1_by' => $this->auth->user_id(),
			'app1_on' => date('Y-m-d H:i:s')
		];

		$query_app_invoice = $this->db->update('tr_invoice_sales', $data_update, ['id_invoice' => $id_invoice]);
		if (!$query_app_invoice) {
			print_r($this->db->error($query_app_invoice));
			$this->db->trans_rollback();
			exit;
		}

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
			$valid = 0;
		} else {
			$this->db->trans_commit();
			$valid = 1;
		}

		echo json_encode([
			'status' => $valid
		]);
	}

	public function reject_invoice()
	{
		$id_invoice = $this->input->post('id_invoice');

		$this->db->trans_begin();

		$data_update = [
			'sts' => 0,
			'rej1_by' => $this->auth->user_id(),
			'rej1_on' => date('Y-m-d H:i:s')
		];

		$query_rej_invoice = $this->db->update('tr_invoice_sales', $data_update, ['id_invoice' => $id_invoice]);
		if (!$query_rej_invoice) {
			print_r($this->db->error($query_rej_invoice));
			$this->db->trans_rollback();
			exit;
		}

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
			$valid = 0;
		} else {
			$this->db->trans_commit();
			$valid = 1;
		}

		echo json_encode([
			'status' => $valid
		]);
	}
}
