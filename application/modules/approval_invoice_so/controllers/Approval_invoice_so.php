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


	// =========================================================================
	// AUDIT TRAIL
	// =========================================================================

	private function beginAudit($method, $data = array(), $keys = array())
	{
		if (isset($this->load) && method_exists($this->load, 'library')) {
			$this->load->library('Module_audit_recorder');
		}
		if (isset($this->module_audit_recorder) && method_exists($this->module_audit_recorder, 'begin')) {
			return $this->module_audit_recorder->begin('approval_invoice_so', $method,
				array('data' => $this->auditSafeData($data)),
				array_unique(array_merge(array_keys($data), $keys, array('reason', 'outcome'))));
		}
		return false;
	}

	private function completeAudit($message, $status, $data = array())
	{
		if (isset($this->module_audit_recorder) && method_exists($this->module_audit_recorder, 'complete')) {
			return $this->module_audit_recorder->complete($message, $status,
				array('data' => $this->auditSafeData($data), 'sql' => null));
		}
		return false;
	}

	// Hanya scalar; id_invoice dibuang bila bentuknya tidak wajar (bisa berisi '/').
	private function auditSafeData($data)
	{
		$safe = array();
		foreach ($data as $key => $value) {
			if ($key === 'id_invoice' && (!is_scalar($value) || !preg_match('/^[A-Za-z0-9\/_.-]{1,100}$/', (string) $value))) { continue; }
			if (is_scalar($value) || $value === null) {
				$safe[$key] = is_string($value) ? mb_substr($value, 0, 512, 'UTF-8') : $value;
			}
		}
		return $safe;
	}

	public function index()
	{
		$this->beginAudit(__FUNCTION__, array(), array('row_count', 'outcome'));
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
		$this->completeAudit('Membuka daftar approval invoice SO', 1, array('row_count' => is_array($get_result) ? count($get_result) : 0, 'outcome' => 'rendered'));
	}

	public function approve_invoice()
	{
		$this->beginAudit(__FUNCTION__, array(), array('id_invoice', 'affected_rows', 'outcome'));
		$id_invoice = $this->input->post('id_invoice');

		$this->db->trans_begin();

		$data_update = [
			'sts' => 1,
			'app1_by' => $this->auth->user_id(),
			'app1_on' => date('Y-m-d H:i:s')
		];

		$query_app_invoice = $this->db->update('tr_invoice_sales', $data_update, ['id_invoice' => $id_invoice]);
		if (!$query_app_invoice) {
			// Perilaku lama (print_r + exit) dipertahankan; hasil dicatat sebelum keluar.
			$this->db->trans_rollback();
			$this->completeAudit('Menyetujui invoice SO gagal', 0, array('id_invoice' => $id_invoice, 'reason' => 'update_failed', 'outcome' => 'failed'));
			print_r($this->db->error($query_app_invoice));
			exit;
		}

		$affected = $this->db->affected_rows();
		$auditReason = null;
		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
			$auditReason = 'transaction_failed';
			$valid = 0;
		} elseif ($this->db->trans_commit() === false) {
			// Sukses ditentukan dari nilai balik commit (CI3), bukan trans_status().
			$this->db->trans_rollback();
			$auditReason = 'commit_failed';
			$valid = 0;
		} else {
			$valid = 1;
		}

		echo json_encode([
			'status' => $valid
		]);
		$this->completeAudit($valid ? 'Menyetujui invoice SO' : 'Menyetujui invoice SO gagal', $valid,
			$valid ? array('id_invoice' => $id_invoice, 'affected_rows' => $affected, 'outcome' => 'approved')
			       : array('id_invoice' => $id_invoice, 'reason' => $auditReason, 'outcome' => 'failed'));
	}

	public function reject_invoice()
	{
		$this->beginAudit(__FUNCTION__, array(), array('id_invoice', 'affected_rows', 'outcome'));
		$id_invoice = $this->input->post('id_invoice');

		$this->db->trans_begin();

		$data_update = [
			'sts' => 0,
			'rej1_by' => $this->auth->user_id(),
			'rej1_on' => date('Y-m-d H:i:s')
		];

		$query_rej_invoice = $this->db->update('tr_invoice_sales', $data_update, ['id_invoice' => $id_invoice]);
		if (!$query_rej_invoice) {
			// Perilaku lama (print_r + exit) dipertahankan; hasil dicatat sebelum keluar.
			$this->db->trans_rollback();
			$this->completeAudit('Menolak invoice SO gagal', 0, array('id_invoice' => $id_invoice, 'reason' => 'update_failed', 'outcome' => 'failed'));
			print_r($this->db->error($query_rej_invoice));
			exit;
		}

		$affected = $this->db->affected_rows();
		$auditReason = null;
		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
			$auditReason = 'transaction_failed';
			$valid = 0;
		} elseif ($this->db->trans_commit() === false) {
			// Sukses ditentukan dari nilai balik commit (CI3), bukan trans_status().
			$this->db->trans_rollback();
			$auditReason = 'commit_failed';
			$valid = 0;
		} else {
			$valid = 1;
		}

		echo json_encode([
			'status' => $valid
		]);
		$this->completeAudit($valid ? 'Menolak invoice SO' : 'Menolak invoice SO gagal', $valid,
			$valid ? array('id_invoice' => $id_invoice, 'affected_rows' => $affected, 'outcome' => 'rejected')
			       : array('id_invoice' => $id_invoice, 'reason' => $auditReason, 'outcome' => 'failed'));
	}
}
