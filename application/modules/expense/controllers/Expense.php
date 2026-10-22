<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/*
 * @author Harboens
 * @copyright Copyright (c) 2020
 *
 * This is controller for Trasaction Purchase Request
 */

$status = array();
class Expense extends Admin_Controller
{
	//Permission
	protected $viewPermission 	= 'Expense.View';
	protected $addPermission  	= 'Expense.Add';
	protected $managePermission = 'Expense.Manage';
	protected $deletePermission = 'Expense.Delete';

	protected $viewPermissionKasbon 	= 'Kasbon.View';
	protected $addPermissionKasbon  	= 'Kasbon.Add';
	protected $managePermissionKasbon = 'Kasbon.Manage';
	protected $deletePermissionKasbon = 'Kasbon.Delete';

	protected $viewPermissionKasbonList 	= 'Kasbon_List.View';
	protected $addPermissionKasbonList  	= 'Kasbon_List.Add';
	protected $managePermissionKasbonList = 'Kasbon_List.Manage';
	protected $deletePermissionKasbonList = 'Kasbon_List.Delete';

	protected $viewPermissionKasbonApp 	= 'Kasbon_Approval.View';
	protected $addPermissionKasbonApp  	= 'Kasbon_Approval.Add';
	protected $managePermissionKasbonApp = 'Kasbon_Approval.Manage';
	protected $deletePermissionKasbonApp = 'Kasbon_Approval.Delete';

	protected $viewPermissionKasbonAppMan 	= 'Approval Kasbon Management.View';
	protected $addPermissionKasbonAppMan  	= 'Approval Kasbon Management.Add';
	protected $managePermissionKasbonAppMan = 'Approval Kasbon Management.Manage';
	protected $deletePermissionKasbonAppMan = 'Approval Kasbon Management.Delete';

	protected $status;

	public function __construct()
	{
		parent::__construct();
		$this->load->helper('kasbon_attachment');
		$this->load->model(array('all/All_model', 'Expense/Expense_model', 'All/All_model', 'Jurnal_nomor/Jurnal_model', 'Coa_expense/Coa_expense_model'));
		$this->template->title('Expense');
		$this->template->page_icon('fa fa-cubes');
		date_default_timezone_set('Asia/Bangkok');
		$this->status = array("0" => "Waiting Approval Finance", "1" => "Approved", "2" => "Approved", "3" => "Approved", "9" => "Rejected");
	}

	private $auditQueriesOk = true;

	private function beginAudit($method, $data = array(), $keys = array())
	{
		$this->auditQueriesOk = true;
		if (isset($this->load) && method_exists($this->load, 'library')) {
			$this->load->library('Module_audit_recorder');
		}
		if (isset($this->module_audit_recorder) && method_exists($this->module_audit_recorder, 'begin')) {
			return $this->module_audit_recorder->begin('expense', $method,
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

	private function auditSafeData($data)
	{
		$safe = array();
		foreach ($data as $key => $value) {
			if (in_array($key, array('amount', 'total_amount', 'jumlah_kasbon'), true) && (!is_scalar($value) || !is_numeric($value))) { continue; }
			if (in_array($key, array('department_id', 'id'), true) && (!is_scalar($value) || !preg_match('/^[A-Za-z0-9_-]{1,64}$/', (string) $value))) { continue; }
			if (in_array($key, array('no_doc', 'no_pr'), true) && (!is_scalar($value) || !preg_match('/^[A-Za-z0-9\/_.-]{1,100}$/', (string) $value))) { continue; }
			if (in_array($key, array('password', 'secret', 'token', 'raw_sql', 'doc_file', 'doc_file_2'), true)) { continue; }
			if (is_scalar($value) || $value === null) {
				$safe[$key] = is_string($value) ? mb_substr($value, 0, 512, 'UTF-8') : $value;
			}
		}
		return $safe;
	}

	private function auditObserve($result, $connection = null)
	{
		$connection = $connection ?: (isset($this->db) ? $this->db : null);
		if ($connection && method_exists($connection, 'error')) {
			$error = $connection->error();
			if (!empty($error['code'])) {
				$this->auditQueriesOk = false;
			}
		}
		if ($result === false) {
			$this->auditQueriesOk = false;
		}
		return $result;
	}

	public function kasbon()
	{
		$this->beginAudit(__FUNCTION__, array(), array('outcome'));
		// $where = array('a.nama' => $this->auth->user_name());
		// $data = $this->Expense_model->GetListDataKasbon();
		// $this->template->set('results', $data);
		$this->template->set('status', $this->status);
		$this->template->page_icon('fa fa-list');
		$this->template->title('Kasbon');
		$this->template->render('kasbon_list');
		$this->completeAudit('Membuka halaman daftar kasbon', 1, array('outcome' => 'rendered'));
	}
	// list kasbon all
	public function kasbon_list_all()
	{
		$this->beginAudit(__FUNCTION__, array(), array('outcome'));
		// $data = $this->Expense_model->GetListDataKasbon();
		// $this->template->set('results', $data);
		$this->template->set('status', $this->status);
		$this->template->page_icon('fa fa-list');
		$this->template->title('Kasbon List');
		$this->template->render('kasbon_list_all');
		$this->completeAudit('Membuka halaman semua daftar kasbon', 1, array('outcome' => 'rendered'));
	}

	// kasbon create
	public function kasbon_create()
	{
		$this->beginAudit(__FUNCTION__, array(), array('outcome', 'item_count'));
		$list_pr_non_po = [];
		$this->db->select('b.no_pr, b.category')
			->from('material_planning_base_on_produksi_detail a')
			->join('material_planning_base_on_produksi b', 'b.so_number = a.so_number')
			->join('new_inventory_4 c', 'c.code_lv4 = a.id_material', 'left')
			->join('ms_satuan d', 'd.id = c.id_unit', 'left')
			->join('accessories e', 'e.id = a.id_material', 'left')
			->join('ms_satuan f', 'f.id = e.id_unit', 'left')
			->where('b.metode_pembelian', '2')
			->where('a.kasbon_created', null)
			->where('NOT EXISTS (SELECT 1 FROM tr_kasbon k WHERE k.id_pr = b.no_pr)', null, false)
			->group_by('b.no_pr');
		$q_mat = $this->db->get();
		$this->auditObserve($q_mat);
		$get_detail_pr_stok_material = $q_mat ? $q_mat->result_array() : array();
		if (is_array($get_detail_pr_stok_material)) {
			foreach ($get_detail_pr_stok_material as $item) {
				$list_pr_non_po[] = [
					'no_pr' => $item['no_pr'],
					'keterangan' => strtoupper($item['category'])
				];
			}
		}

		$this->db->select('b.no_pr, b.project_name')
			->from('rutin_non_planning_detail a')
			->join('rutin_non_planning_header b', 'b.no_pr = a.no_pr', 'left')
			->join('ms_satuan c', 'c.id = a.satuan', 'left')
			->where('b.metode_pembelian', '2')
			->where('a.kasbon_created', null)
			->where('NOT EXISTS (SELECT 1 FROM tr_kasbon k WHERE k.id_pr = b.no_pr)', null, false)
			->group_by('b.no_pr');
		$q_dept = $this->db->get();
		$this->auditObserve($q_dept);
		$get_detail_pr_departemen = $q_dept ? $q_dept->result_array() : array();
		if (is_array($get_detail_pr_departemen)) {
			foreach ($get_detail_pr_departemen as $item) {
				$list_pr_non_po[] = [
					'no_pr' => $item['no_pr'],
					'keterangan' => $item['project_name']
				];
			}
		}

		$this->db->select('a.no_pr, b.nama_asset');
		$this->db->from('tran_pr_header a');
		$this->db->join('asset_planning b', 'b.no_pr = a.no_pr', 'left');
		$this->db->where('a.metode_pembelian', 2);
		$this->db->where('a.kasbon_created', null);
		$this->db->where('b.no_pr IS NOT NULL');
		$this->db->where('NOT EXISTS (SELECT 1 FROM tr_kasbon k WHERE k.id_pr = a.no_pr)', null, false);
		$this->db->group_by('a.no_pr');
		$q_asset = $this->db->get();
		$this->auditObserve($q_asset);
		$get_pr_asset = $q_asset ? $q_asset->result_array() : array();
		if (is_array($get_pr_asset)) {
			foreach ($get_pr_asset as $item) {
				$list_pr_non_po[] = [
					'no_pr' => $item['no_pr'],
					'keterangan' => strtoupper($item['nama_asset'])
				];
			}
		}

		$get_list_coa = $this->Expense_model->GetListCoa('Kasbon');
		$this->auditObserve($get_list_coa);

		$this->template->set('list_pr_non_po', $list_pr_non_po);
		$this->template->set('list_coa', $get_list_coa);
		$this->template->set('mod', '');
		$this->template->render('kasbon_form');
		$this->completeAudit('Membuka form pembuatan kasbon', $this->auditQueriesOk ? 1 : 0, array(
			'outcome' => $this->auditQueriesOk ? 'rendered' : 'query_error',
			'item_count' => count($list_pr_non_po)
		));
	}

	// kasbon save
	public function kasbon_save()
	{
		$id             	= $this->input->post("id");
		$tgl_doc  			= $this->input->post("tgl_doc");
		$no_doc		   	 	= $this->input->post("no_doc");
		$departement		= $this->input->post("departement");
		$nama				= $this->input->post("nama");
		$keperluan			= $this->input->post("keperluan");
		$keterangan			= $this->input->post("keterangan");
		$jumlah_kasbon		= $this->input->post("jumlah_kasbon");
		$filename			= $this->input->post("filename");
		$bank_id			= $this->input->post("bank_id");
		$accnumber			= $this->input->post("accnumber");
		$accname			= $this->input->post("accname");
		$filename2			= $this->input->post("filename2");
		$project			= $this->input->post("project");
		$no_pr				= $this->input->post("no_pr");
		$tipe_pr			= $this->input->post("tipe_pr");
		$file_name			= $this->input->post("file_name");
		$doc_pr			= $this->input->post("doc_pr");
		$to_doc_pr			= $this->input->post("to_doc_pr");
		$coa = $this->input->post("coa");
		$nm_coa = $this->input->post("nm_coa");
		$metode_pembayaran	= 1;

		$this->beginAudit(__FUNCTION__, array(
			'id' => $id,
			'no_doc' => $no_doc,
			'department_id' => $departement,
			'amount' => $jumlah_kasbon,
			'no_pr' => $no_pr,
			'tipe_pr' => $tipe_pr,
		), array('id', 'no_doc', 'department_id', 'amount', 'no_pr', 'tipe_pr', 'outcome', 'reason'));

		$this->db->trans_begin();
		$config['upload_path'] = 'assets/expense/';
		$config['allowed_types'] = '*';
		$config['remove_spaces'] = TRUE;
		$config['encrypt_name'] = TRUE;
		$filenames = '';
		if ($id !== '') {
			$get_filenames = $this->db->select('doc_file')->get_where('tr_kasbon', ['id' => $id])->row_array();
			if (!empty($get_filenames)) {
				$filenames = $get_filenames['doc_file'];
			}
		}
		if (!empty($_FILES['doc_file']['name'])) {
			$_FILES['file']['name'] = $_FILES['doc_file']['name'];
			$_FILES['file']['type'] = $_FILES['doc_file']['type'];
			$_FILES['file']['tmp_name'] = $_FILES['doc_file']['tmp_name'];
			$_FILES['file']['error'] = $_FILES['doc_file']['error'];
			$_FILES['file']['size'] = $_FILES['doc_file']['size'];
			// $this->load->library('upload', $config);
			$this->upload->initialize($config);
			if ($this->upload->do_upload('file')) {
				$uploadData = $this->upload->data();
				$filenames = $uploadData['file_name'];
			}
		}
		$filenames2 = '';
		if ($id !== '') {
			$get_filenames = $this->db->select('doc_file_2')->get_where('tr_kasbon', ['id' => $id])->row_array();
			if (!empty($get_filenames)) {
				$filenames2 = $get_filenames['doc_file_2'];
			}
		}
		if (!empty($_FILES['doc_file_2']['name'])) {
			$_FILES['file']['name'] = $_FILES['doc_file_2']['name'];
			$_FILES['file']['type'] = $_FILES['doc_file_2']['type'];
			$_FILES['file']['tmp_name'] = $_FILES['doc_file_2']['tmp_name'];
			$_FILES['file']['error'] = $_FILES['doc_file_2']['error'];
			$_FILES['file']['size'] = $_FILES['doc_file_2']['size'];
			// $this->load->library('upload', $config);
			$this->upload->initialize($config);
			if ($this->upload->do_upload('file')) {
				$uploadData2 = $this->upload->data();
				$filenames2 = $uploadData2['file_name'];
			}
		}

		try {
			if ($tipe_pr === 'pr departemen') {
				$existing = $id !== '' ? $this->db->get_where('tr_kasbon', array('id' => $id))->row() : null;
				$unchanged = $existing && $existing->id_pr === $no_pr && $existing->tipe_pr === $tipe_pr;
				if ($unchanged && empty($_FILES['doc_file']['name'])) {
					$filenames = $existing->doc_file;
				} elseif (!$unchanged) {
					$prFiles = kasbon_attachment_names(kasbon_pr_document($this->db, $no_pr));
					if ($prFiles) { $filenames = json_encode($prFiles); }
					kasbon_check_attachment_capacity($this->db, $filenames);
					kasbon_copy_attachments($prFiles, FCPATH . 'assets/pr', FCPATH . 'assets/expense');
				}
			} elseif (!empty($file_name)) {
				$prFiles = kasbon_attachment_names($file_name);
				$source = $tipe_pr === 'pr asset' ? 'uploads/pr_asset' : 'assets/pr';
				kasbon_copy_attachments($prFiles, FCPATH . $source, FCPATH . 'assets/expense');
				$filenames = $file_name;
			}
		} catch (RuntimeException $e) {
			$this->db->trans_rollback();
			$this->completeAudit('Gagal menyimpan kasbon: exception lampiran', 0, array(
				'id' => $id,
				'no_doc' => $no_doc,
				'department_id' => $departement,
				'amount' => $jumlah_kasbon,
				'no_pr' => $no_pr,
				'tipe_pr' => $tipe_pr,
				'outcome' => 'attachment_error',
				'reason' => mb_substr($e->getMessage(), 0, 255)
			));
			echo json_encode(array('save' => false, 'id' => $id, 'message' => $e->getMessage()));
			return;
		}

		if ($id !== "") {
			$data = array(
				'tgl_doc' => date('Y-m-d', strtotime($tgl_doc)),
				'departement' => $departement,
				'keperluan' => $keperluan,
				'project' => $project,
				'nama' => $nama,
				'keterangan' => $keterangan,
				'jumlah_kasbon' => $jumlah_kasbon,
				'doc_file' => $filenames,
				'doc_file_2' => $filenames2,
				'status' => '0',
				'bank_id' => $bank_id,
				'accnumber' => $accnumber,
				'accname' => $accname,
				'id_pr' => $no_pr,
				'tipe_pr' => $tipe_pr,
				'metode_pembayaran' => $metode_pembayaran,
				'no_coa' => $coa,
				'nm_coa' => $nm_coa,
				'modified_by' => $this->auth->user_name(),
				'modified_on' => date("Y-m-d h:i:s"),
			);
			$this->db->delete('tr_pr_detail_kasbon', ['id_kasbon' => $no_doc]);

			if (!empty($no_pr)) {
				if ($tipe_pr == 'pr departemen') {
					$get_detail_pr = $this->db->get_where('rutin_non_planning_detail', ['no_pr' => $no_pr])->result_array();
					$arrInsertDetail = [];
					$arrUpdateDetail = [];
					foreach ($get_detail_pr as $detail_pr) :
						if (isset($_POST['price_input_' . $detail_pr['id']])) {
							$arrInsertDetail[] = [
								'id_detail' => $detail_pr['id'],
								'id_kasbon' => $no_doc,
								'no_pr' => $no_pr,
								'nm_material' => $detail_pr['nm_barang'],
								'qty' => $detail_pr['qty'],
								'unit' => $detail_pr['satuan'],
								'harga' => str_replace(',', '', $this->input->post('price_input_' . $detail_pr['id'])),
								'total_harga' => str_replace(',', '', $this->input->post('grand_total_' . $detail_pr['id'])),
								'created_by' => $this->auth->user_id(),
								'tipe_pr' => $tipe_pr,
								'created_date' => date('Y-m-d H:i:s')
							];

							$arrUpdateDetail[] = [
								'kasbon_created' => 1
							];

							$update_rutin_non_planning_detail = $this->db->update('rutin_non_planning_detail', ['kasbon_created' => 1], ['id' => $detail_pr['id']]);
							if (!$update_rutin_non_planning_detail) {
								$this->db->trans_rollback();

								print_r($this->db->last_query());
								exit;
							}
						}
					endforeach;

					$insert_pr_detail_kasbon = $this->db->insert_batch('tr_pr_detail_kasbon', $arrInsertDetail);
					if (!$insert_pr_detail_kasbon) {
						$this->db->trans_rollback();

						print_r($this->db->last_query());
						exit;
					}
				} else if ($tipe_pr == 'pr asset') {
					$this->db->select('a.*, b.nama_asset as nm_barang, "Pcs" as satuan, b.qty');
					$this->db->from('tran_pr_header a');
					$this->db->join('asset_planning b', 'b.no_pr = a.no_pr', 'left');
					$this->db->where('a.no_pr', $no_pr);
					$get_detail_pr = $this->db->get()->result_array();
					$arrInsertDetail = [];
					$arrUpdateDetail = [];
					foreach ($get_detail_pr as $detail_pr) :
						if (isset($_POST['price_input_' . $detail_pr['id']])) {
							$arrInsertDetail[] = [
								'id_detail' => $detail_pr['id'],
								'id_kasbon' => $no_doc,
								'no_pr' => $no_pr,
								'id_material' => $detail_pr['id'],
								'nm_material' => $detail_pr['nm_barang'],
								'qty' => $detail_pr['qty'],
								'unit' => $detail_pr['satuan'],
								'harga' => str_replace(',', '', $this->input->post('price_input_' . $detail_pr['id'])),
								'total_harga' => str_replace(',', '', $this->input->post('grand_total_' . $detail_pr['id'])),
								'created_by' => $this->auth->user_id(),
								'tipe_pr' => $tipe_pr,
								'created_date' => date('Y-m-d H:i:s')
							];

							$arrUpdateDetail[] = [
								'id' => $detail_pr['id'],
								'kasbon_created' => 1
							];

							$update_tran_pr_header = $this->db->update('tran_pr_header', ['kasbon_created' => '1'], ['id' => $detail_pr['id']]);
							if (!$update_tran_pr_header) {
								$this->db->trans_rollback();

								print_r($this->db->last_query());
								exit;
							}
						}
					endforeach;

					$update_pr_detail_kasbon = $this->db->insert_batch('tr_pr_detail_kasbon', $arrInsertDetail);
					if (!$update_pr_detail_kasbon) {
						$this->db->trans_rollback();

						print_r($this->db->last_query());
						exit;
					}
				} else {
					// $get_detail_pr = $this->db->get_where('material_planning_base_on_produksi_detail', ['no_pr' => $no_pr])->result_array();
					$this->db->select('a.*, if(c.nama IS NULL, e.stock_name, c.nama) as nm_barang, if(d.code IS NULL, f.code, d.code) as satuan');
					$this->db->from('material_planning_base_on_produksi_detail a');
					$this->db->join('material_planning_base_on_produksi b', 'b.so_number = a.so_number');
					$this->db->join('new_inventory_4 c', 'c.code_lv4 = a.id_material', 'left');
					$this->db->join('ms_satuan d', 'd.id = c.id_unit', 'left');
					$this->db->join('accessories e', 'e.id = a.id_material', 'left');
					$this->db->join('ms_satuan f', 'f.id = e.id_unit', 'left');
					$this->db->where('b.no_pr', $no_pr);
					$get_detail_pr = $this->db->get()->result_array();

					$arrInsertDetail = [];
					$arrUpdateDetail = [];
					foreach ($get_detail_pr as $detail_pr) :
						if (isset($_POST['price_input_' . $detail_pr['id']])) {
							$arrInsertDetail[] = [
								'id_detail' => $detail_pr['id'],
								'id_kasbon' => $no_doc,
								'no_pr' => $no_pr,
								'id_material' => $detail_pr['id_material'],
								'nm_material' => $detail_pr['nm_barang'],
								'qty' => $detail_pr['propose_purchase'],
								'unit' => $detail_pr['satuan'],
								'harga' => str_replace(',', '', $this->input->post('price_input_' . $detail_pr['id'])),
								'total_harga' => str_replace(',', '', $this->input->post('grand_total_' . $detail_pr['id'])),
								'created_by' => $this->auth->user_id(),
								'tipe_pr' => $tipe_pr,
								'created_date' => date('Y-m-d H:i:s')
							];

							$arrUpdateDetail[] = [
								'id' => $detail_pr['id'],
								'kasbon_created' => 1
							];

							$update_planning_detail = $this->db->update('material_planning_base_on_produksi_detail', ['kasbon_created' => '1'], ['id' => $detail_pr['id']]);
							if (!$update_planning_detail) {
								$this->db->trans_rollback();

								print_r($this->db->last_query());
								exit;
							}
						}
					endforeach;

					$update_pr_detail_kasbon = $this->db->insert_batch('tr_pr_detail_kasbon', $arrInsertDetail);
					if (!$update_pr_detail_kasbon) {
						$this->db->trans_rollback();

						print_r($this->db->last_query());
						exit;
					}
				}
			}

			$update_kasbon = $this->db->update('tr_kasbon', $data, ['id' => $id]);
			if (!$update_kasbon) {
				$this->db->trans_rollback();

				print_r($this->db->last_query());
				exit;
			}
			// print_r($this->db->last_query());
			// exit;
			if ($this->db->trans_status() === FALSE || $this->db->trans_commit() === FALSE) {
				$this->db->trans_rollback();
				$result = false;
			} else {
				$result = true;
			}
		} else {
			$rec = $this->db->query("select no_perkiraan from " . DBACC . ".master_oto_jurnal_detail where kode_master_jurnal='BUK030' and menu='kasbon'")->row();
			$no_perkiraan = (!empty($rec)) ? $rec->no_perkiraan : '';
			// if (!empty($rec)) {
			// 	$no_perkiraan = $rec->no_perkiraan;
			// }
			$no_doc = $this->All_model->GetAutoGenerate('format_kasbon');
			$data =  array(
				'no_doc' => $no_doc,
				'tgl_doc' => date('Y-m-d', strtotime($tgl_doc)),
				'departement' => $departement,
				'keperluan' => $keperluan,
				'keterangan' => $keterangan,
				'nama' => $nama,
				'jumlah_kasbon' => $jumlah_kasbon,
				'doc_file' => $filenames,
				'project' => $project,
				'coa' => $no_perkiraan,
				'status' => 0,
				'doc_file_2' => $filenames2,
				'bank_id' => $bank_id,
				'accnumber' => $accnumber,
				'accname' => $accname,
				'id_pr' => $no_pr,
				'tipe_pr' => $tipe_pr,
				'metode_pembayaran' => $metode_pembayaran,
				'no_coa' => $coa,
				'nm_coa' => $nm_coa,
				'created_by' => $this->auth->user_name(),
				'created_on' => date("Y-m-d h:i:s"),
			);
			$id = $this->db->insert('tr_kasbon', $data);

			if ($tipe_pr !== '') {
				if ($tipe_pr == 'pr departemen') {
					$get_detail_pr = $this->db->get_where('rutin_non_planning_detail', ['no_pr' => $no_pr])->result_array();
					$arrInsertDetail = [];
					$arrUpdateDetail = [];
					foreach ($get_detail_pr as $detail_pr) :
						if (isset($_POST['price_input_' . $detail_pr['id']])) {
							$arrInsertDetail[] = [
								'id_detail' => $detail_pr['id'],
								'id_kasbon' => $no_doc,
								'no_pr' => $no_pr,
								'nm_material' => $detail_pr['nm_barang'],
								'qty' => $detail_pr['qty'],
								'unit' => $detail_pr['satuan'],
								'harga' => str_replace(',', '', $this->input->post('price_input_' . $detail_pr['id'])),
								'total_harga' => str_replace(',', '', $this->input->post('grand_total_' . $detail_pr['id'])),
								'created_by' => $this->auth->user_id(),
								'tipe_pr' => $tipe_pr,
								'created_date' => date('Y-m-d H:i:s')
							];

							$arrUpdateDetail[] = [
								'id' => $detail_pr['id'],
								'kasbon_created' => 1
							];
						}
					endforeach;

					$this->db->insert_batch('tr_pr_detail_kasbon', $arrInsertDetail);
					$this->db->update_batch('rutin_non_planning_detail', $arrUpdateDetail, 'id');
				} else if ($tipe_pr == 'pr asset') {
					$this->db->select('a.*, b.nama_asset as nm_barang, "Pcs" as satuan, b.qty');
					$this->db->from('tran_pr_header a');
					$this->db->join('asset_planning b', 'b.no_pr = a.no_pr', 'left');
					$this->db->where('a.no_pr', $no_pr);
					$get_detail_pr = $this->db->get()->result_array();
					$arrInsertDetail = [];
					$arrUpdateDetail = [];
					foreach ($get_detail_pr as $detail_pr) :
						if (isset($_POST['price_input_' . $detail_pr['id']])) {
							$arrInsertDetail[] = [
								'id_detail' => $detail_pr['id'],
								'id_kasbon' => $no_doc,
								'no_pr' => $no_pr,
								'id_material' => $detail_pr['id'],
								'nm_material' => $detail_pr['nm_barang'],
								'qty' => $detail_pr['qty'],
								'unit' => $detail_pr['satuan'],
								'harga' => str_replace(',', '', $this->input->post('price_input_' . $detail_pr['id'])),
								'total_harga' => str_replace(',', '', $this->input->post('grand_total_' . $detail_pr['id'])),
								'created_by' => $this->auth->user_id(),
								'tipe_pr' => $tipe_pr,
								'created_date' => date('Y-m-d H:i:s')
							];

							$arrUpdateDetail[] = [
								'id' => $detail_pr['id'],
								'kasbon_created' => 1
							];
						}
					endforeach;

					$this->db->insert_batch('tr_pr_detail_kasbon', $arrInsertDetail);
					$this->db->update_batch('tran_pr_header', $arrUpdateDetail, 'id');
				} else {
					$this->db->select('a.*, if(c.nama IS NULL, e.stock_name, c.nama) as nm_barang, if(d.code IS NULL, f.code, d.code) as satuan');
					$this->db->from('material_planning_base_on_produksi_detail a');
					$this->db->join('material_planning_base_on_produksi b', 'b.so_number = a.so_number');
					$this->db->join('new_inventory_4 c', 'c.code_lv4 = a.id_material', 'left');
					$this->db->join('ms_satuan d', 'd.id = c.id_unit', 'left');
					$this->db->join('accessories e', 'e.id = a.id_material', 'left');
					$this->db->join('ms_satuan f', 'f.id = e.id_unit', 'left');
					$this->db->where('b.no_pr', $no_pr);
					$get_detail_pr = $this->db->get()->result_array();
					$arrInsertDetail = [];
					$arrUpdateDetail = [];
					foreach ($get_detail_pr as $detail_pr) :
						if (isset($_POST['price_input_' . $detail_pr['id']])) {
							$arrInsertDetail[] = [
								'id_detail' => $detail_pr['id'],
								'id_kasbon' => $no_doc,
								'no_pr' => $no_pr,
								'id_material' => $detail_pr['id_material'],
								'nm_material' => $detail_pr['nm_barang'],
								'qty' => $detail_pr['propose_purchase'],
								'unit' => $detail_pr['satuan'],
								'harga' => str_replace(',', '', $this->input->post('price_input_' . $detail_pr['id'])),
								'total_harga' => str_replace(',', '', $this->input->post('grand_total_' . $detail_pr['id'])),
								'created_by' => $this->auth->user_id(),
								'tipe_pr' => $tipe_pr,
								'created_date' => date('Y-m-d H:i:s')
							];

							$arrUpdateDetail[] = [
								'id' => $detail_pr['id'],
								'kasbon_created' => 1
							];
						}
					endforeach;

					$this->db->insert_batch('tr_pr_detail_kasbon', $arrInsertDetail);
					$this->db->update_batch('material_planning_base_on_produksi_detail', $arrUpdateDetail, 'id');
				}
			}
			// if (is_numeric($id)) {
			// 	$result = TRUE;
			// } else {
			// 	$result = FALSE;
			// }
			if ($this->db->trans_status() === FALSE || $this->db->trans_commit() === FALSE) {
				$this->db->trans_rollback();
				$result = FALSE;
			} else {
				$result = TRUE;
			}
		}
		$param = array(
			'save' => $result,
			'id' => $id
		);
		$status = $result ? 1 : 0;
		$this->completeAudit(
			$result ? ($id !== "" ? 'Berhasil memperbarui kasbon' : 'Berhasil membuat kasbon baru') : 'Gagal menyimpan kasbon: transaksi dibatalkan',
			$status,
			array(
				'id' => $id,
				'no_doc' => $no_doc,
				'department_id' => $departement,
				'amount' => $jumlah_kasbon,
				'no_pr' => $no_pr,
				'tipe_pr' => $tipe_pr,
				'outcome' => $result ? 'saved' : 'commit_failed'
			)
		);
		echo json_encode($param);
	}

	public function get_kasbon($nama = '', $departement = '')
	{
		$this->beginAudit(__FUNCTION__, array(
			'filter_nama' => $nama,
			'department_id' => $departement,
		), array('filter_nama', 'department_id', 'record_count', 'outcome'));

		$where_filter = "";
		if (!empty($nama) && !empty($departement)) {
			$where_filter = " AND a.nama = " . $this->db->escape($nama) . " AND a.departement = " . $this->db->escape($departement) . " ";
		}

		$sql_kasbon = "
			SELECT
				a.id as id, 
				a.no_doc as no_doc, 
				a.tgl_doc as tgl_doc, 
				a.departement as departement, 
				a.nama as nama, 
				a.jumlah_kasbon as jumlah_kasbon, 
				a.keperluan as keperluan, 
				a.doc_file as doc_file, 
				a.status as status, 
				a.coa as coa, 
				a.doc_file_2 as doc_file_2, 
				a.bank_id as bank_id, 
				a.accnumber as accnumber, 
				a.accname as accname,
				a.project as project, 
				a.keterangan as keterangan,
				'' as id_expense_detail
			FROM
				tr_kasbon a
			WHERE
				a.status IN ('2', '3') AND
				(a.no_kasbon_consultant IS NULL OR a.no_kasbon_consultant = '') " . $where_filter . " AND
				(SELECT COUNT(aa.id) FROM tr_expense_detail aa JOIN tr_expense ab ON ab.no_doc = aa.no_doc WHERE aa.id_kasbon = a.no_doc AND ab.status IN ('0','1','2','3','4')) <= 0 AND
				(SELECT COUNT(ab2.id) FROM tr_expense ab2 WHERE ab2.id_kasbon = a.no_doc AND ab2.status IN ('0','1','2','3','4')) <= 0
			
			UNION ALL

			SELECT
				a.id as id, 
				a.no_doc as no_doc, 
				a.tanggal as tgl_doc, 
				b.departement as departement, 
				'' as nama, 
				a.nilai as jumlah_kasbon, 
				a.nama as keperluan, 
				a.doc_file as doc_file, 
				a.status as status, 
				'' as coa, 
				'' as doc_file_2, 
				a.bank_id as bank_id, 
				a.accnumber as accnumber, 
				a.accname as accname, 
				'' as project, 
				a.keterangan as keterangan,
				a.id as id_expense_detail
			FROM
				tr_pengajuan_rutin_detail a
				LEFT JOIN tr_pengajuan_rutin b ON b.no_doc = a.no_doc
			WHERE
				a.status IN ('2', '3') AND
				a.metode_pembelian = '2' AND 
				(SELECT COUNT(aa.id) FROM tr_expense aa JOIN tr_expense_detail ab ON ab.no_doc = aa.no_doc WHERE ab.id_expense_detail = a.id AND aa.status IN ('0','1','2','3','4')) < 1
		";

		$query = $this->db->query($sql_kasbon);
		$this->auditObserve($query);
		$data = $query ? $query->result() : false;
		if (!$data) {
			$this->completeAudit('Gagal membaca data kasbon: query error atau data kosong', 0, array(
				'filter_nama' => $nama,
				'department_id' => $departement,
				'outcome' => 'query_error'
			));
			print_r($this->db->error($data));
			exit;
		}

		if (!empty($data) && is_array($data)) {
			foreach ($data as &$item) {
				$coa_code = '';
				$coa_name = '';

				if (!empty($item->no_doc)) {
					// Cek apakah ada di tr_pr_detail_kasbon
					$this->db->select('a.*');
					$this->db->from('tr_pr_detail_kasbon a');
					$this->db->where('a.id_kasbon', $item->no_doc);
					$q_pr = $this->db->get();
					$this->auditObserve($q_pr);
					$q_pr_rows = $q_pr ? $q_pr->result_array() : array();

					if (!empty($q_pr_rows)) {
						foreach ($q_pr_rows as $item_pr) {
							$tipePr = isset($item_pr['tipe_pr']) ? $item_pr['tipe_pr'] : '';
							if ($tipePr == 'pr stok') {
								// 1. Ambil COA dari accessories
								if (!empty($item_pr['id_material'])) {
									$q_acc = $this->db->select('no_coa, nm_coa')->get_where('accessories', ['id' => $item_pr['id_material']]);
									$this->auditObserve($q_acc);
									$get_acc = $q_acc ? $q_acc->row() : null;
									if (!empty($get_acc) && !empty($get_acc->no_coa)) {
										$coa_code = $get_acc->no_coa;
										$coa_name = !empty($get_acc->nm_coa) ? $get_acc->nm_coa : '';
										break;
									}
								}
							} else if ($tipePr == 'pr departemen') {
								// 1. Ambil COA dari detail baris rutin_non_planning_detail
								if (!empty($item_pr['id_detail'])) {
									$q_dtl = $this->db->select('coa')->get_where('rutin_non_planning_detail', ['id' => $item_pr['id_detail']]);
									$this->auditObserve($q_dtl);
									$get_dtl = $q_dtl ? $q_dtl->row() : null;
									if (!empty($get_dtl) && !empty($get_dtl->coa)) {
										$coa_code = $get_dtl->coa;
										break;
									}
								}
								// 2. Jika detail kosong, ambil COA dari header rutin_non_planning_header
								if (empty($coa_code) && !empty($item_pr['no_pr'])) {
									$q_dept = $this->db->select('coa')->get_where('rutin_non_planning_header', ['no_pr' => $item_pr['no_pr']]);
									$this->auditObserve($q_dept);
									$get_dept = $q_dept ? $q_dept->row() : null;
									if (!empty($get_dept) && !empty($get_dept->coa)) {
										$coa_code = $get_dept->coa;
										break;
									}
								}
							}
						}
					}
				}

				// Fallback ke field coa / no_coa pada kasbon
				if (empty($coa_code)) {
					$coa_code = !empty($item->coa) ? $item->coa : '';
				}
				if (empty($coa_code) && !empty($item->no_doc)) {
					$q_kb = $this->db->get_where('tr_kasbon', ['no_doc' => $item->no_doc]);
					$this->auditObserve($q_kb);
					$get_kb = $q_kb ? $q_kb->row() : null;
					if (!empty($get_kb)) {
						$coa_code = !empty($get_kb->no_coa) ? $get_kb->no_coa : (!empty($get_kb->coa) ? $get_kb->coa : '');
					}
				}

				// Default fallback 1304-01-01
				if (empty($coa_code)) {
					$coa_code = '1304-01-01';
				}

				if (empty($coa_name)) {
					$q_coa_acc = $this->db->query("SELECT nama FROM " . DBACC . ".coa_master WHERE no_perkiraan = '" . $coa_code . "'");
					$this->auditObserve($q_coa_acc);
					$row_coa_acc = $q_coa_acc ? $q_coa_acc->row() : null;
					if (!empty($row_coa_acc)) {
						$coa_name = $row_coa_acc->nama;
					} else {
						$coa_name = ($coa_code == '1304-01-01') ? 'Peralatan Kantor' : 'Biaya Pengeluaran';
					}
				}

				$item->coa = $coa_code;
				$item->coa_name = $coa_name;
			}
		}

		$this->completeAudit('Membaca data kasbon', 1, array(
			'filter_nama' => $nama,
			'department_id' => $departement,
			'record_count' => is_array($data) ? count($data) : 0,
			'outcome' => 'fetched'
		));
		echo json_encode($data);
	}

	// kasbon edit
	public function kasbon_edit($id, $mod = '')
	{
		$this->beginAudit(__FUNCTION__, array('id' => $id, 'mod' => $mod), array('id', 'no_doc', 'mod', 'outcome'));
		$data = $this->Expense_model->GetDataKasbon($id);
		$this->auditObserve($data);

		$this->db->select('a.*, IF(b.code IS NULL, a.unit, b.code) as satuan');
		$this->db->from('tr_pr_detail_kasbon a');
		$this->db->join('ms_satuan b', 'b.id = a.unit', 'left');
		$this->db->where('a.id_kasbon', !empty($data->no_doc) ? $data->no_doc : '');
		$q_pr = $this->db->get();
		$this->auditObserve($q_pr);
		$get_pr_detail_kasbon = $q_pr ? $q_pr->result_array() : array();

		$get_list_coa = $this->Expense_model->GetListCoa('Kasbon');
		$this->auditObserve($get_list_coa);

		$this->template->set('mod', $mod);
		$this->template->set('status', $this->status);
		$this->template->set('data', $data);
		$this->template->set('list_coa', $get_list_coa);
		$this->template->set('stsview', '');
		$this->template->set('list_detail_pr_kasbon', $get_pr_detail_kasbon);
		$this->template->title('Kasbon Form');
		$this->template->page_icon('fa fa-list');
		$this->template->render('kasbon_form');
		$this->completeAudit('Membuka form edit kasbon', ($data && $this->auditQueriesOk) ? 1 : 0, array(
			'id' => $id,
			'no_doc' => !empty($data->no_doc) ? $data->no_doc : null,
			'mod' => $mod,
			'outcome' => ($data && $this->auditQueriesOk) ? 'rendered' : ($data ? 'query_error' : 'not_found')
		));
	}

	public function kasbon_edit_manage($id, $mod = '')
	{
		$this->beginAudit(__FUNCTION__, array('id' => $id, 'mod' => $mod), array('id', 'no_doc', 'mod', 'outcome'));
		$data = $this->Expense_model->GetDataKasbon($id);
		$this->auditObserve($data);

		$this->db->select('a.*, IF(b.code IS NULL, a.unit, b.code) as satuan');
		$this->db->from('tr_pr_detail_kasbon a');
		$this->db->join('ms_satuan b', 'b.id = a.unit', 'left');
		$this->db->where('a.id_kasbon', !empty($data->no_doc) ? $data->no_doc : '');
		$q_pr = $this->db->get();
		$this->auditObserve($q_pr);
		$get_pr_detail_kasbon = $q_pr ? $q_pr->result_array() : array();

		$get_list_coa = $this->Expense_model->GetListCoa('Kasbon');
		$this->auditObserve($get_list_coa);

		$this->template->set('mod', $mod);
		$this->template->set('status', $this->status);
		$this->template->set('data', $data);
		$this->template->set('list_coa', $get_list_coa);
		$this->template->set('stsview', '');
		$this->template->set('list_detail_pr_kasbon', $get_pr_detail_kasbon);
		$this->template->title('Approval Kasbon by Management');
		$this->template->page_icon('fa fa-list');
		$this->template->render('app_kasbon_manage');
		$this->completeAudit('Membuka form edit approval kasbon manajemen', ($data && $this->auditQueriesOk) ? 1 : 0, array(
			'id' => $id,
			'no_doc' => !empty($data->no_doc) ? $data->no_doc : null,
			'mod' => $mod,
			'outcome' => ($data && $this->auditQueriesOk) ? 'rendered' : ($data ? 'query_error' : 'not_found')
		));
	}
	// kasbon print
	public function kasbon_print($id)
	{
		$this->beginAudit(__FUNCTION__, array('id' => $id), array('id', 'no_doc', 'tipe_pr', 'outcome'));
		$results = $this->Expense_model->GetDataKasbon($id);
		$this->auditObserve($results);

		if (!empty($results->tipe_pr) && $results->tipe_pr == 'pr departemen') {
			// PR Department print flow
			$pr_header = $this->Expense_model->GetPrDeptHeader($results->id_pr);
			$this->auditObserve($pr_header);
			$pr_details = $this->Expense_model->GetPrDeptDetails($results->id_pr);
			$this->auditObserve($pr_details);
			$dept_name = $this->Expense_model->GetDepartmentName($pr_header->id_dept ?? '');
			$this->auditObserve($dept_name);

			// Get request by name from users table
			$request_by = '';
			if (!empty($pr_header->created_by)) {
				$q_user = $this->db->get_where('users', ['id_user' => $pr_header->created_by]);
				$this->auditObserve($q_user);
				$user = $q_user ? $q_user->row() : null;
				$request_by = !empty($user) ? $user->nm_lengkap : '';
			}

			$data = array(
				'title'      => 'Print Pengajuan Kasbon',
				'kasbon'     => $results,
				'pr_header'  => $pr_header,
				'pr_details' => $pr_details,
				'dept_name'  => $dept_name,
				'request_by' => $request_by,
			);
			$this->load->view('kasbon_pr_dept_print', $data);
		} else if (!empty($results->tipe_pr) && $results->tipe_pr == 'pr stok') {
			// PR Stok print flow
			$pr_header = $this->Expense_model->GetPrStokHeader($results->id_pr);
			$this->auditObserve($pr_header);
			$pr_details = $this->Expense_model->GetPrStokDetails($pr_header->so_number ?? '');
			$this->auditObserve($pr_details);

			// Get request by name
			$request_by = !empty($pr_header->nama_user) ? $pr_header->nama_user : '';

			$data = array(
				'title'      => 'Print Pengajuan Kasbon',
				'kasbon'     => $results,
				'pr_header'  => $pr_header,
				'pr_details' => $pr_details,
				'request_by' => $request_by,
			);
			$this->load->view('kasbon_pr_stok_print', $data);
		} else if (!empty($results->tipe_pr) && $results->tipe_pr == 'pr asset') {
			// PR Asset print flow
			$pr_asset = $this->Expense_model->GetPrAssetData($results->id_pr);
			$this->auditObserve($pr_asset);
			$dept_name = $this->Expense_model->GetDepartmentName($pr_asset->id_dept ?? '');
			$this->auditObserve($dept_name);

			// Get request by name
			$request_by = !empty($pr_asset->nama_user) ? $pr_asset->nama_user : '';

			$data = array(
				'title'      => 'Print Pengajuan Kasbon',
				'kasbon'     => $results,
				'pr_asset'   => $pr_asset,
				'dept_name'  => $dept_name,
				'request_by' => $request_by,
			);
			$this->load->view('kasbon_pr_asset_print', $data);
		} else {
			// Existing flow (unchanged)
			$nmuser = !empty($results->created_by) ? $results->created_by : '';
			if (!empty($results->tipe_pr)) {
				if ($results->tipe_pr == 'pr stok') {
					$this->db->select('b.nm_lengkap');
					$this->db->from('material_planning_base_on_produksi a');
					$this->db->join('users b', 'b.id_user = a.created_by');
					$this->db->where('a.no_pr', $results->id_pr);
					$q_detail = $this->db->get();
					$this->auditObserve($q_detail);
					$get_single_detail = $q_detail ? $q_detail->row() : null;

					if ($get_single_detail) {
						$nmuser = $get_single_detail->nm_lengkap;
					}
				}

				if ($results->tipe_pr == 'pr asset') {
					$this->db->select('b.nm_lengkap');
					$this->db->from('tran_pr_header a');
					$this->db->join('users b', 'b.id_user = a.created_by');
					$this->db->where('a.no_pr', $results->id_pr);
					$q_detail = $this->db->get();
					$this->auditObserve($q_detail);
					$get_single_detail = $q_detail ? $q_detail->row() : null;

					if ($get_single_detail) {
						$nmuser = $get_single_detail->nm_lengkap;
					}
				}
			}

			$data = array(
				'title'			=> 'Print Kasbon',
				'stsview'		=> 'print',
				'data'			=> $results,
				'nmuser'			=> $nmuser
			);
			$this->load->view('kasbon_print', $data);
		}

		$status = ($results && $this->auditQueriesOk) ? 1 : 0;
		$this->completeAudit('Mencetak dokumen kasbon', $status, array(
			'id' => $id,
			'no_doc' => !empty($results->no_doc) ? $results->no_doc : null,
			'tipe_pr' => !empty($results->tipe_pr) ? $results->tipe_pr : null,
			'outcome' => ($results && $this->auditQueriesOk) ? 'printed' : ($results ? 'query_error' : 'not_found')
		));
	}

	public function periodik_print($id)
	{
		$this->beginAudit(__FUNCTION__, array('id' => $id), array('id', 'no_doc', 'outcome'));
		// Fetch header record
		$q_header = $this->db->get_where('tr_pengajuan_rutin', ['id' => $id]);
		$this->auditObserve($q_header);
		$header = $q_header ? $q_header->row() : null;

		if (empty($header)) {
			$this->completeAudit('Gagal mencetak pengajuan periodik: data tidak ditemukan', 0, array(
				'id' => $id,
				'outcome' => 'not_found'
			));
			show_404();
		}

		// Fetch detail records
		$q_detail = $this->db->get_where('tr_pengajuan_rutin_detail', ['no_doc' => $header->no_doc]);
		$this->auditObserve($q_detail);
		$detail = $q_detail ? $q_detail->result() : array();

		// Resolve department name
		$dept_name = $this->Expense_model->GetDepartmentName($header->departement);
		if ($dept_name === null) {
			$dept_name = '';
		}

		// Resolve COA display for each detail record
		if (is_array($detail)) {
			foreach ($detail as $item) {
				$q_coa = $this->db->get_where(DBACC . '.coa_master', ['no_perkiraan' => $item->coa]);
				$this->auditObserve($q_coa);
				$coa_row = $q_coa ? $q_coa->row() : null;
				$item->coa_display = $coa_row
					? $coa_row->no_perkiraan . ' - ' . $coa_row->nama
					: $item->coa;
			}
		}

		// Pass resolved data to view
		$view_data = array(
			'title'     => 'Print Pengajuan Periodik',
			'data'      => $header,
			'detail'    => $detail,
			'dept_name' => $dept_name,
		);

		$this->load->view('periodik_print', $view_data);
		$this->completeAudit('Mencetak pengajuan periodik', $this->auditQueriesOk ? 1 : 0, array(
			'id' => $id,
			'no_doc' => $header->no_doc,
			'outcome' => $this->auditQueriesOk ? 'printed' : 'query_error'
		));
	}

	// kasbon view
	public function kasbon_view($id, $mod = '')
	{
		$this->beginAudit(__FUNCTION__, array('id' => $id, 'mod' => $mod), array('id', 'no_doc', 'mod', 'outcome'));
		$data = $this->Expense_model->GetDataKasbon($id);
		$this->auditObserve($data);

		$this->db->select('a.*, b.code as satuan');
		$this->db->from('tr_pr_detail_kasbon a');
		$this->db->join('ms_satuan b', 'b.id = a.unit', 'left');
		$this->db->where('a.id_kasbon', !empty($data->no_doc) ? $data->no_doc : '');
		$q_pr = $this->db->get();
		$this->auditObserve($q_pr);
		$get_pr_detail_kasbon = $q_pr ? $q_pr->result_array() : array();

		$this->template->set('mod', $mod);
		$this->template->set('status', $this->status);
		$this->template->set('data', $data);
		$this->template->set('stsview', 'view');
		$this->template->set('list_detail_pr_kasbon', $get_pr_detail_kasbon);
		$this->template->title('Kasbon Form');
		$this->template->page_icon('fa fa-list');
		$this->template->render('kasbon_form_detail');
		$this->completeAudit('Membuka detail kasbon', ($data && $this->auditQueriesOk) ? 1 : 0, array(
			'id' => $id,
			'no_doc' => !empty($data->no_doc) ? $data->no_doc : null,
			'mod' => $mod,
			'outcome' => ($data && $this->auditQueriesOk) ? 'rendered' : ($data ? 'query_error' : 'not_found')
		));
	}
	// kasbon approval
	public function kasbon_fin()
	{
		$this->beginAudit(__FUNCTION__, array('page' => 'kasbon_list_fin'), array('page', 'outcome'));
		// $datawhere = ("a.status = 0");
		// $data = $this->Expense_model->GetListDataKasbon($datawhere);
		$this->template->set('status', $this->status);
		// $this->template->set('results', $data);
		$this->template->set('stsview', 'view');
		$this->template->title('Kasbon Approval by Finance');
		$this->template->page_icon('fa fa-list');
		$this->template->render('kasbon_list_fin');
		$this->completeAudit('Membuka halaman approval kasbon finance', 1, array(
			'page' => 'kasbon_list_fin',
			'outcome' => 'rendered'
		));
	}

	// kasbon approve
	public function kasbon_approve($id = '')
	{
		$this->beginAudit(__FUNCTION__, array('id' => $id, 'coa' => $this->input->post('coa')), array('id', 'no_coa', 'sts_finance', 'outcome'));
		$result = false;
		if ($id != "") {
			// $data = array(
			// 	'id' => $id,
			// 	'status' => 1,
			// 	'st_reject' => '',
			// 	'approved_by' => $this->auth->user_name(),
			// 	'approved_on' => date("Y-m-d h:i:s")
			// );

			$data = [
				'id' => $id,
				'sts_finance' => '1',
				'app_finance_date' => date('Y-m-d H:i:s'),
				'reject_reason_finance' => null,
				'no_coa' => $this->input->post('coa'),
				'nm_coa' => $this->input->post('nm_coa')
			];

			$result 		= $this->All_model->dataUpdate('tr_kasbon', $data, array('id' => $id));
			$this->auditObserve($result);
			$keterangan     = "SUKSES, Update data " . $id;
			$status         = 1;
			$nm_hak_akses   = $this->managePermission;
			$kode_universal = $id;
			$jumlah 		= 1;
			$sql            = $this->db->last_query();
			simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
		}
		$param = array(
			'save' => $result,
			'id' => $id
		);
		$auditStatus = $result ? 1 : 0;
		$this->completeAudit(
			$result ? 'Berhasil menyetujui kasbon finance' : 'Gagal menyetujui kasbon finance',
			$auditStatus,
			array(
				'id' => $id,
				'no_coa' => $this->input->post('coa'),
				'sts_finance' => $result ? '1' : null,
				'outcome' => $result ? 'approved' : ($id === '' ? 'empty_id' : 'update_failed')
			)
		);
		echo json_encode($param);
	}

	public function kasbon_approve_manage($id = '')
	{
		$this->beginAudit(__FUNCTION__, array('id' => $id), array('id', 'status', 'outcome'));
		$result = false;
		if ($id != "") {
			$data = array(
				'id' => $id,
				'status' => 1,
				'st_reject' => '',
				'approved_by' => $this->auth->user_name(),
				'approved_on' => date("Y-m-d h:i:s")
			);

			// $data = [
			// 	'id' => $id,
			// 	'sts_finance' => '1',
			// 	'app_finance_date' => date('Y-m-d H:i:s'),
			// 	'reject_reason_finance' => null
			// ];

			$result 		= $this->All_model->dataUpdate('tr_kasbon', $data, array('id' => $id));
			$this->auditObserve($result);
			$keterangan     = "SUKSES, Update data " . $id;
			$status         = 1;
			$nm_hak_akses   = $this->managePermission;
			$kode_universal = $id;
			$jumlah 		= 1;
			$sql            = $this->db->last_query();
			simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
		}
		$param = array(
			'save' => $result,
			'id' => $id
		);
		$auditStatus = $result ? 1 : 0;
		$this->completeAudit(
			$result ? 'Berhasil menyetujui kasbon management' : 'Gagal menyetujui kasbon management',
			$auditStatus,
			array(
				'id' => $id,
				'status' => $result ? 1 : null,
				'outcome' => $result ? 'approved' : ($id === '' ? 'empty_id' : 'update_failed')
			)
		);
		echo json_encode($param);
	}

	// kasbon delete
	public function kasbon_delete($id)
	{
		$this->beginAudit(__FUNCTION__, array('id' => $id), array('id', 'no_doc', 'outcome'));
		$q_kasbon = $this->db->get_where('tr_kasbon', ['id' => $id]);
		$this->auditObserve($q_kasbon);
		$get_kasbon = $q_kasbon ? $q_kasbon->row() : null;

		$result = false;
		$no_doc = null;
		if ($get_kasbon) {
			$no_doc = $get_kasbon->no_doc;
			$q_detail = $this->db->get_where('tr_pr_detail_kasbon', ['id_kasbon' => $no_doc]);
			$this->auditObserve($q_detail);
			$get_detail_kasbon = $q_detail ? $q_detail->result_array() : array();
			// print_r($get_detail_kasbon);
			// exit;
			foreach ($get_detail_kasbon as $detail_kasbon) :
				if (isset($detail_kasbon['tipe_pr']) && $detail_kasbon['tipe_pr'] == 'pr departemen') {
					$up = $this->db->update('rutin_non_planning_detail', ['kasbon_created' => null], ['id' => $detail_kasbon['id_detail']]);
					$this->auditObserve($up);
				} else {
					$up = $this->db->update('material_planning_base_on_produksi_detail', ['kasbon_created' => null], ['id' => $detail_kasbon['id_detail']]);
					$this->auditObserve($up);
				}
			endforeach;
			$del_detail = $this->db->delete('tr_pr_detail_kasbon', ['id_kasbon' => $no_doc]);
			$this->auditObserve($del_detail);
			$result = $this->All_model->dataDelete('tr_kasbon', array('id' => $id));
			$this->auditObserve($result);
			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$result = false;
			} else {
				$this->db->trans_commit();
			}
		}
		$param = array('delete' => $result);
		$auditStatus = $result ? 1 : 0;
		$this->completeAudit(
			$result ? 'Berhasil menghapus kasbon' : 'Gagal menghapus kasbon',
			$auditStatus,
			array(
				'id' => $id,
				'no_doc' => $no_doc,
				'outcome' => $result ? 'deleted' : ($get_kasbon ? 'delete_failed' : 'not_found')
			)
		);
		echo json_encode($param);
	}

	// list
	public function index()
	{
		$this->beginAudit(__FUNCTION__, array('page' => 'index'), array('page', 'outcome'));

		// Query daftar bank perusahaan aktif dari coa_master
		$q_bank = $this->db->query("SELECT no_perkiraan, nama FROM " . DBACC . ".coa_master WHERE no_perkiraan LIKE '1101-02-%' AND level = '5' ORDER BY no_perkiraan");
		$this->auditObserve($q_bank);
		$bank_options = $q_bank ? $q_bank->result() : [];

		// Hitung counter untuk badge Tab 2 (Pengembalian Kasbon) dan Tab 3 (Lebih Expense)
		$cur_user = $this->auth->user_name();
		$is_admin = ($this->auth->user_id() === '7' || $this->auth->user_id() === '1' || has_permission('Expense.Manage'));

		$this->db->where('lebih_bayar >', 0);
		$this->db->where_in('status', ['3', '4']);
		$this->db->group_start();
		$this->db->where('id_po', null);
		$this->db->or_where('id_po', '');
		$this->db->group_end();
		$this->db->not_like('no_doc', 'PI-', 'after');
		if (!$is_admin) {
			$this->db->where('nama', $cur_user);
		}
		$count_pengembalian = $this->db->count_all_results('tr_expense');

		$this->db->where('kurang_bayar >', 0);
		$this->db->where('status', '2');
		$this->db->group_start();
		$this->db->where('id_po', null);
		$this->db->or_where('id_po', '');
		$this->db->group_end();
		$this->db->not_like('no_doc', 'PI-', 'after');
		if (!$is_admin) {
			$this->db->where('nama', $cur_user);
		}
		$count_lebih_expense = $this->db->count_all_results('tr_expense');

		$this->template->set('status', $this->status);
		$this->template->set('bank_options', $bank_options);
		$this->template->set('count_pengembalian', $count_pengembalian);
		$this->template->set('count_lebih_expense', $count_lebih_expense);
		$this->template->page_icon('fa fa-cubes');
		$this->template->title('Expense');
		$this->template->render('index');
		$this->completeAudit('Membuka daftar expense', 1, array(
			'page' => 'index',
			'count_pengembalian' => $count_pengembalian,
			'count_lebih_expense' => $count_lebih_expense,
			'outcome' => 'rendered'
		));
	}

	// create (Direct Expense)
	public function create()
	{
		$this->beginAudit(__FUNCTION__, array('page' => 'form'), array('page', 'pre_id', 'outcome'));
		$detail_files = [];
		$this->template->set('detail_files', $detail_files);
		$data_budget 	= $this->All_model->GetComboBudget('', 'EXPENSE', date('Y'));
		$this->auditObserve($data_budget);
		$data_coa 		= $this->Coa_expense_model->GetDataWithJenis('Expense');
		$this->auditObserve($data_coa);
		$coa_field 		= !empty($data_coa->coa) ? $data_coa->coa : '';
		$coa_array 		= explode(';', $coa_field);
		$option_coa 	= $this->All_model->GetListCoa($coa_array);
		$this->auditObserve($option_coa);

		// Pre-ID No. Dokumen
		$pre_id = '';
		$data_gen = $this->All_model->GetOneData('ms_generate', array('tipe' => 'format_expense'));
		$this->auditObserve($data_gen);
		if ($data_gen !== false && !empty($data_gen)) {
			if (stripos($data_gen->info, 'YEAR', 0) !== false) {
				$years = ($data_gen->info3 != date("Y")) ? date("Y") : $data_gen->info3;
				$number = ($data_gen->info3 != date("Y")) ? 1 : ($data_gen->info2 + 1);
				$newnumber = sprintf('%0' . $data_gen->info4 . 'd', $number);
				$pre_id = str_ireplace('XXXX', $newnumber, $data_gen->info);
				$pre_id = str_ireplace('YEAR', $years, $pre_id);
			} else {
				$number = ($data_gen->info2 + 1);
				$newnumber = sprintf('%0' . $data_gen->info4 . 'd', $number);
				$pre_id = str_ireplace('XXXX', $newnumber, $data_gen->info);
			}
		}

		$this->template->set('pre_id', $pre_id);
		$this->template->set('data_budget', $data_budget);
		$this->template->set('data_coa', $data_coa);
		$this->template->set('option_coa', $option_coa);
		$this->template->set('stsview', '');
		$this->template->page_icon('fa fa-cubes');
		$this->template->title('Expense');

		$this->template->render('form');
		$status = $this->auditQueriesOk ? 1 : 0;
		$this->completeAudit('Membuka form direct expense', $status, array(
			'page' => 'form',
			'pre_id' => $pre_id,
			'outcome' => $this->auditQueriesOk ? 'rendered' : 'query_error'
		));
	}
	// create_report (Pertanggungjawaban Kasbon Sendigs)
	public function create_report($id_kasbon = '')
	{
		$this->beginAudit(__FUNCTION__, array('id_kasbon' => $id_kasbon), array('id_kasbon', 'item_count', 'outcome'));
		$id_kasbon = urldecode($id_kasbon);
		$q_kb = $this->db->get_where('tr_kasbon', ['no_doc' => $id_kasbon]);
		$this->auditObserve($q_kb);
		$data_kasbon = $q_kb ? $q_kb->row() : null;

		$data_budget 	= $this->All_model->GetComboBudget('', 'EXPENSE', date('Y'));
		$this->auditObserve($data_budget);
		$data_coa 		= $this->Coa_expense_model->GetDataWithJenis('Expense');
		$this->auditObserve($data_coa);
		$coa_field 		= !empty($data_coa->coa) ? $data_coa->coa : '';
		$coa_array 		= explode(';', $coa_field);
		$option_coa 	= $this->All_model->GetListCoa($coa_array);
		$this->auditObserve($option_coa);

		// Pre-ID No. Dokumen
		$pre_id = '';
		$data_gen = $this->All_model->GetOneData('ms_generate', array('tipe' => 'format_expense'));
		$this->auditObserve($data_gen);
		if ($data_gen !== false && !empty($data_gen)) {
			if (stripos($data_gen->info, 'YEAR', 0) !== false) {
				$years = ($data_gen->info3 != date("Y")) ? date("Y") : $data_gen->info3;
				$number = ($data_gen->info3 != date("Y")) ? 1 : ($data_gen->info2 + 1);
				$newnumber = sprintf('%0' . $data_gen->info4 . 'd', $number);
				$pre_id = str_ireplace('XXXX', $newnumber, $data_gen->info);
				$pre_id = str_ireplace('YEAR', $years, $pre_id);
			} else {
				$number = ($data_gen->info2 + 1);
				$newnumber = sprintf('%0' . $data_gen->info4 . 'd', $number);
				$pre_id = str_ireplace('XXXX', $newnumber, $data_gen->info);
			}
		}

		// Ambil rincian item PR / Kasbon
		$detail_items = [];
		if (!empty($id_kasbon)) {
			$this->db->select('a.*, IF(b.code IS NULL, "Pcs", b.code) as satuan_name');
			$this->db->from('tr_pr_detail_kasbon a');
			$this->db->join('ms_satuan b', 'b.id = a.unit', 'left');
			$this->db->where('a.id_kasbon', $id_kasbon);
			$q_pr = $this->db->get();
			$this->auditObserve($q_pr);
			$res_pr = $q_pr ? $q_pr->result_array() : array();

			if (!empty($res_pr)) {
				$no = 1;
				foreach ($res_pr as $item_pr) {
					$coa_code = '';
					$coa_name = '';

					if (isset($item_pr['tipe_pr']) && $item_pr['tipe_pr'] == 'pr stok') {
						if (!empty($item_pr['id_material'])) {
							$q_acc = $this->db->select('no_coa, nm_coa')->get_where('accessories', ['id' => $item_pr['id_material']]);
							$this->auditObserve($q_acc);
							$get_acc = $q_acc ? $q_acc->row() : null;
							if (!empty($get_acc) && !empty($get_acc->no_coa)) {
								$coa_code = $get_acc->no_coa;
								$coa_name = !empty($get_acc->nm_coa) ? $get_acc->nm_coa : '';
							}
						}
					} else if (isset($item_pr['tipe_pr']) && $item_pr['tipe_pr'] == 'pr departemen') {
						if (!empty($item_pr['id_detail'])) {
							$q_dtl = $this->db->select('coa')->get_where('rutin_non_planning_detail', ['id' => $item_pr['id_detail']]);
							$this->auditObserve($q_dtl);
							$get_dtl = $q_dtl ? $q_dtl->row() : null;
							if (!empty($get_dtl) && !empty($get_dtl->coa)) {
								$coa_code = $get_dtl->coa;
							}
						}
						if (empty($coa_code) && !empty($item_pr['no_pr'])) {
							$q_dept = $this->db->select('coa')->get_where('rutin_non_planning_header', ['no_pr' => $item_pr['no_pr']]);
							$this->auditObserve($q_dept);
							$get_dept = $q_dept ? $q_dept->row() : null;
							if (!empty($get_dept) && !empty($get_dept->coa)) {
								$coa_code = $get_dept->coa;
							}
						}
					}

					if (empty($coa_code) && !empty($data_kasbon)) {
						$coa_code = !empty($data_kasbon->no_coa) ? $data_kasbon->no_coa : (!empty($data_kasbon->coa) ? $data_kasbon->coa : '');
					}
					if (empty($coa_code)) {
						$coa_code = '1304-01-01';
					}

					if (empty($coa_name)) {
						$q_coa_acc = $this->db->query("SELECT nama FROM " . DBACC . ".coa_master WHERE no_perkiraan = '" . $coa_code . "'");
						$this->auditObserve($q_coa_acc);
						$row_coa_acc = $q_coa_acc ? $q_coa_acc->row() : null;
						if (!empty($row_coa_acc)) {
							$coa_name = $row_coa_acc->nama;
						} else {
							$coa_name = ($coa_code == '1304-01-01') ? 'Peralatan Kantor' : 'Biaya Pengeluaran';
						}
					}

					$qty_k = floatval(isset($item_pr['qty']) ? $item_pr['qty'] : 0);
					$harga_k = floatval(isset($item_pr['harga']) ? $item_pr['harga'] : 0);
					$total_k = floatval(isset($item_pr['total_harga']) ? $item_pr['total_harga'] : 0);

					$detail_items[] = [
						'id' => $no,
						'no_pr' => isset($item_pr['no_pr']) ? $item_pr['no_pr'] : '',
						'tipe_pr' => isset($item_pr['tipe_pr']) ? $item_pr['tipe_pr'] : '',
						'deskripsi' => isset($item_pr['nm_material']) ? $item_pr['nm_material'] : '',
						'keterangan' => !empty($item_pr['no_pr']) ? 'No. PR: ' . $item_pr['no_pr'] : '',
						'tanggal' => !empty($data_kasbon->tgl_doc) ? $data_kasbon->tgl_doc : date('Y-m-d'),
						'coa' => $coa_code,
						'coa_name' => $coa_name,
						'qty_kasbon' => $qty_k,
						'harga_kasbon' => $harga_k,
						'total_kasbon' => $total_k,
						'qty' => $qty_k,
						'harga' => $harga_k,
						'expense' => $total_k,
						'id_expense_detail' => isset($item_pr['id_detail']) ? $item_pr['id_detail'] : ''
					];
					$no++;
				}
			} else {
				// Kasbon umum
				$coa_code = !empty($data_kasbon->no_coa) ? $data_kasbon->no_coa : (!empty($data_kasbon->coa) ? $data_kasbon->coa : '1304-01-01');
				$q_coa_acc = $this->db->query("SELECT nama FROM " . DBACC . ".coa_master WHERE no_perkiraan = '" . $coa_code . "'");
				$this->auditObserve($q_coa_acc);
				$row_coa_acc = $q_coa_acc ? $q_coa_acc->row() : null;
				$coa_name = !empty($row_coa_acc) ? $row_coa_acc->nama : 'Peralatan Kantor';

				$j_kasbon = !empty($data_kasbon->jumlah_kasbon) ? floatval($data_kasbon->jumlah_kasbon) : 0;
				$detail_items[] = [
					'id' => 1,
					'no_pr' => '',
					'tipe_pr' => 'kasbon',
					'deskripsi' => !empty($data_kasbon->keperluan) ? $data_kasbon->keperluan : 'Pengeluaran Kasbon',
					'keterangan' => !empty($data_kasbon->keterangan) ? $data_kasbon->keterangan : '',
					'tanggal' => !empty($data_kasbon->tgl_doc) ? $data_kasbon->tgl_doc : date('Y-m-d'),
					'coa' => $coa_code,
					'coa_name' => $coa_name,
					'qty_kasbon' => 1,
					'harga_kasbon' => $j_kasbon,
					'total_kasbon' => $j_kasbon,
					'qty' => 1,
					'harga' => $j_kasbon,
					'expense' => $j_kasbon,
					'id_expense_detail' => ''
				];
			}
		}

		$this->template->set('detail_files', []);
		$this->template->set('data_budget', $data_budget);
		$this->template->set('data_coa', $data_coa);
		$this->template->set('option_coa', $option_coa);
		$this->template->set('data_kasbon', $data_kasbon);
		$this->template->set('pre_id', $pre_id);
		$this->template->set('detail_items', $detail_items);
		$this->template->set('stsview', '');
		$this->template->page_icon('fa fa-ticket');
		$this->template->title('Expense Report');
		$this->template->render('form_report');

		$status = $this->auditQueriesOk ? 1 : 0;
		$this->completeAudit('Membuka form pelaporan expense', $status, array(
			'id_kasbon' => $id_kasbon,
			'item_count' => count($detail_items),
			'outcome' => $this->auditQueriesOk ? 'rendered' : 'query_error'
		));
	}


	// edit
	public function edit($id)
	{
		$this->beginAudit(__FUNCTION__, array('id' => $id), array('id', 'no_doc', 'form_type', 'outcome'));
		$data 			= $this->Expense_model->GetDataHeader($id);
		$this->auditObserve($data);
		$detail_files = [];
		if (!empty($data->no_doc) && $this->db->table_exists('tr_expense_detail_file')) {
			$q_df = $this->db->get_where('tr_expense_detail_file', ['no_doc' => $data->no_doc]);
			$this->auditObserve($q_df);
			$get_df = $q_df ? $q_df->result() : array();
			foreach ($get_df as $df) {
				$detail_files[$df->id_detail][] = $df;
			}
		}
		$this->template->set('detail_files', $detail_files);
		$data_detail	= !empty($data->no_doc) ? $this->Expense_model->GetDataDetail($data->no_doc) : array();
		$this->auditObserve($data_detail);
		$data_budget 	= $this->All_model->GetComboBudget('', 'EXPENSE', date('Y'));
		$this->auditObserve($data_budget);
		$data_coa 		= $this->Coa_expense_model->GetDataWithJenis('Expense');
		$this->auditObserve($data_coa);
		$coa_field 		= !empty($data_coa->coa) ? $data_coa->coa : '';
		$coa_array 		= explode(';', $coa_field);
		$option_coa 	= $this->All_model->GetListCoa($coa_array);
		$this->auditObserve($option_coa);

		$this->template->set('option_coa', $option_coa);
		$this->template->set('data_budget', $data_budget);
		$this->template->set('data_detail', $data_detail);
		$this->template->set('status', $this->status);
		$this->template->set('data', $data);
		$this->template->set('stsview', '');

		// Deteksi apakah Expense Report (ada Kasbon) atau Direct Expense
		$form_type = 'direct';
		if (!empty($data) && (!empty($data->id_kasbon) || (isset($data->total_kasbon) && floatval($data->total_kasbon) > 0))) {
			$form_type = 'report';
			$q_kb = $this->db->get_where('tr_kasbon', ['no_doc' => $data->id_kasbon]);
			$this->auditObserve($q_kb);
			$data_kasbon = $q_kb ? $q_kb->row() : null;

			$detail_items = [];
			if (is_array($data_detail)) {
				foreach ($data_detail as $rec) {
					$row_coa = !empty($rec->coa) ? $rec->coa : '1304-01-01';
					$q_c = $this->db->query("SELECT nama FROM " . DBACC . ".coa_master WHERE no_perkiraan = '" . $row_coa . "'");
					$this->auditObserve($q_c);
					$row_c = $q_c ? $q_c->row() : null;
					$rec_qty = isset($rec->qty) ? floatval($rec->qty) : 0;
					$rec_kasbon = isset($rec->kasbon) ? floatval($rec->kasbon) : 0;
					$rec_harga = isset($rec->harga) ? floatval($rec->harga) : 0;
					$rec_expense = isset($rec->expense) ? floatval($rec->expense) : 0;

					$detail_items[] = [
						'id' => isset($rec->id) ? $rec->id : null,
						'deskripsi' => isset($rec->deskripsi) ? $rec->deskripsi : '',
						'keterangan' => isset($rec->keterangan) ? $rec->keterangan : '',
						'tanggal' => isset($rec->tanggal) ? $rec->tanggal : '',
						'coa' => $row_coa,
						'coa_name' => !empty($row_c) ? $row_c->nama : 'Peralatan Kantor',
						'qty_kasbon' => $rec_qty,
						'harga_kasbon' => floatval($rec_kasbon > 0 ? ($rec_kasbon / ($rec_qty > 0 ? $rec_qty : 1)) : $rec_harga),
						'total_kasbon' => $rec_kasbon,
						'qty' => $rec_qty,
						'harga' => $rec_harga,
						'expense' => $rec_expense,
						'doc_file' => isset($rec->doc_file) ? $rec->doc_file : null,
						'id_expense_detail' => isset($rec->id_expense_detail) ? $rec->id_expense_detail : null
					];
				}
			}
			$this->template->set('data_kasbon', $data_kasbon);
			$this->template->set('detail_items', $detail_items);
			$this->template->page_icon('fa fa-ticket');
			$this->template->title('Expense Report');
			$this->template->render('form_report');
		} else {
			$this->template->page_icon('fa fa-cubes');
			$this->template->title('Expense');
			$this->template->render('form');
		}

		$status = ($data && $this->auditQueriesOk) ? 1 : 0;
		$this->completeAudit('Membuka form edit expense', $status, array(
			'id' => $id,
			'no_doc' => !empty($data->no_doc) ? $data->no_doc : null,
			'form_type' => $form_type,
			'outcome' => ($data && $this->auditQueriesOk) ? 'rendered' : ($data ? 'query_error' : 'not_found')
		));
	}

	// view
	public function view($id)
	{
		$this->beginAudit(__FUNCTION__, array('id' => $id), array('id', 'no_doc', 'form_type', 'outcome'));
		$data = $this->Expense_model->GetDataHeader($id);
		$this->auditObserve($data);
		$detail_files = [];
		if (!empty($data->no_doc) && $this->db->table_exists('tr_expense_detail_file')) {
			$q_df = $this->db->get_where('tr_expense_detail_file', ['no_doc' => $data->no_doc]);
			$this->auditObserve($q_df);
			$get_df = $q_df ? $q_df->result() : array();
			foreach ($get_df as $df) {
				$detail_files[$df->id_detail][] = $df;
			}
		}
		$this->template->set('detail_files', $detail_files);
		$data_detail	= !empty($data->no_doc) ? $this->Expense_model->GetDataDetail($data->no_doc) : array();
		$this->auditObserve($data_detail);
		$data_budget = $this->All_model->GetComboBudget('', 'EXPENSE', date('Y'));
		$this->auditObserve($data_budget);
		$data_coa 		= $this->Coa_expense_model->GetDataWithJenis('Expense');
		$this->auditObserve($data_coa);
		$coa_field 		= !empty($data_coa->coa) ? $data_coa->coa : '';
		$coa_array 		= explode(';', $coa_field);
		$option_coa 	= $this->All_model->GetListCoa($coa_array);
		$this->auditObserve($option_coa);

		$this->template->set('option_coa', $option_coa);
		$this->template->set('data_budget', $data_budget);
		$this->template->set('data_detail', $data_detail);
		$this->template->set('status', $this->status);
		$this->template->set('data', $data);
		$this->template->set('stsview', 'view');

		// Deteksi apakah Expense Report (ada Kasbon) atau Direct Expense
		$form_type = 'direct';
		if (!empty($data) && (!empty($data->id_kasbon) || (isset($data->total_kasbon) && floatval($data->total_kasbon) > 0))) {
			$form_type = 'report';
			$q_kb = $this->db->get_where('tr_kasbon', ['no_doc' => $data->id_kasbon]);
			$this->auditObserve($q_kb);
			$data_kasbon = $q_kb ? $q_kb->row() : null;

			$detail_items = [];
			if (is_array($data_detail)) {
				foreach ($data_detail as $rec) {
					$row_coa = !empty($rec->coa) ? $rec->coa : '1304-01-01';
					$q_c = $this->db->query("SELECT nama FROM " . DBACC . ".coa_master WHERE no_perkiraan = '" . $row_coa . "'");
					$this->auditObserve($q_c);
					$row_c = $q_c ? $q_c->row() : null;
					$rec_qty = isset($rec->qty) ? floatval($rec->qty) : 0;
					$rec_kasbon = isset($rec->kasbon) ? floatval($rec->kasbon) : 0;
					$rec_harga = isset($rec->harga) ? floatval($rec->harga) : 0;
					$rec_expense = isset($rec->expense) ? floatval($rec->expense) : 0;

					$detail_items[] = [
						'id' => isset($rec->id) ? $rec->id : null,
						'deskripsi' => isset($rec->deskripsi) ? $rec->deskripsi : '',
						'keterangan' => isset($rec->keterangan) ? $rec->keterangan : '',
						'tanggal' => isset($rec->tanggal) ? $rec->tanggal : '',
						'coa' => $row_coa,
						'coa_name' => !empty($row_c) ? $row_c->nama : 'Peralatan Kantor',
						'qty_kasbon' => $rec_qty,
						'harga_kasbon' => floatval($rec_kasbon > 0 ? ($rec_kasbon / ($rec_qty > 0 ? $rec_qty : 1)) : $rec_harga),
						'total_kasbon' => $rec_kasbon,
						'qty' => $rec_qty,
						'harga' => $rec_harga,
						'expense' => $rec_expense,
						'doc_file' => isset($rec->doc_file) ? $rec->doc_file : null,
						'id_expense_detail' => isset($rec->id_expense_detail) ? $rec->id_expense_detail : null
					];
				}
			}
			$this->template->set('data_kasbon', $data_kasbon);
			$this->template->set('detail_items', $detail_items);
			$this->template->page_icon('fa fa-ticket');
			$this->template->title('Expense Report');
			$this->template->render('form_report');
		} else {
			$this->template->page_icon('fa fa-cubes');
			$this->template->title('Expense');
			$this->template->render('form');
		}

		$status = ($data && $this->auditQueriesOk) ? 1 : 0;
		$this->completeAudit('Melihat detail expense', $status, array(
			'id' => $id,
			'no_doc' => !empty($data->no_doc) ? $data->no_doc : null,
			'form_type' => $form_type,
			'outcome' => ($data && $this->auditQueriesOk) ? 'rendered' : ($data ? 'query_error' : 'not_found')
		));
	}
	// print
	public function expense_print($id)
	{
		$this->beginAudit(__FUNCTION__, array('id' => $id), array('id', 'no_doc', 'outcome'));
		$response = $this->Expense_model->GetDataHeader($id);
		$this->auditObserve($response);
		$data_detail = (!empty($response) && !empty($response->no_doc)) ? $this->Expense_model->GetDataDetail($response->no_doc) : array();
		$this->auditObserve($data_detail);

		$detail_files = [];
		if (!empty($response) && !empty($response->no_doc) && $this->db->table_exists('tr_expense_detail_file')) {
			$q_df = $this->db->get_where('tr_expense_detail_file', ['no_doc' => $response->no_doc]);
			$this->auditObserve($q_df);
			$get_df = $q_df ? $q_df->result() : array();
			foreach ($get_df as $df) {
				$detail_files[$df->id_detail][] = $df;
			}
		}

		$data = array(
			'status'		=> $this->status,
			'data_detail'	=> $data_detail,
			'data'			=> $response,
			'detail_files'	=> $detail_files,
		);
		$this->load->view('expense_print', $data);

		$status = ($response && $this->auditQueriesOk) ? 1 : 0;
		$this->completeAudit('Mencetak dokumen expense', $status, array(
			'id' => $id,
			'no_doc' => !empty($response->no_doc) ? $response->no_doc : null,
			'outcome' => ($response && $this->auditQueriesOk) ? 'printed' : ($response ? 'query_error' : 'not_found')
		));
	}
	public function expense_pettycash_print($id)
	{
		$this->beginAudit(__FUNCTION__, array('id' => $id), array('id', 'no_doc', 'outcome'));
		$response = $this->Expense_model->GetDataHeader($id);
		$this->auditObserve($response);
		$data_detail = (!empty($response) && !empty($response->no_doc)) ? $this->Expense_model->GetDataDetail($response->no_doc) : array();
		$this->auditObserve($data_detail);

		$detail_files = [];
		if (!empty($response) && !empty($response->no_doc) && $this->db->table_exists('tr_expense_detail_file')) {
			$q_df = $this->db->get_where('tr_expense_detail_file', ['no_doc' => $response->no_doc]);
			$this->auditObserve($q_df);
			$get_df = $q_df ? $q_df->result() : array();
			foreach ($get_df as $df) {
				$detail_files[$df->id_detail][] = $df;
			}
		}

		$data = array(
			'status'		=> $this->status,
			'data_detail'	=> $data_detail,
			'data'			=> $response,
			'detail_files'	=> $detail_files,
		);
		$this->load->view('expense_pettycash_print', $data);

		$status = ($response && $this->auditQueriesOk) ? 1 : 0;
		$this->completeAudit('Mencetak dokumen expense pettycash', $status, array(
			'id' => $id,
			'no_doc' => !empty($response->no_doc) ? $response->no_doc : null,
			'outcome' => ($response && $this->auditQueriesOk) ? 'printed' : ($response ? 'query_error' : 'not_found')
		));
	}
	public function list_expense_approval()
	{
		$this->beginAudit(__FUNCTION__, array('page' => 'index_approval'), array('page', 'outcome'));
		$this->template->set('status', $this->status);
		$this->template->page_icon('fa fa-list');
		$this->template->title('Expense Approval By Finance');
		$this->template->render('index_approval');
		$this->completeAudit('Membuka daftar approval expense finance', 1, array(
			'page' => 'index_approval',
			'outcome' => 'rendered'
		));
	}

	public function list_expense_approval_manage()
	{
		$this->beginAudit(__FUNCTION__, array('page' => 'index_approval_manage'), array('page', 'item_count', 'outcome'));
		// $data = $this->Expense_model->GetListData('sts_finance = "1"');

		$this->db->select('a.*, COALESCE(NULLIF(a.jumlah, 0), SUM(b.total_harga), 0) as nominal, c.username as nmuser, d.username as nmapproval');
		$this->db->from('tr_expense a');
		$this->db->join('tr_expense_detail b', 'b.no_doc = a.no_doc', 'left');
		$this->db->join('users c', 'a.nama=c.username', 'left');
		$this->db->join('users d', 'a.approval=d.username', 'left');
		$this->db->where('(a.status = 1 OR (a.status = 0 AND a.sts_finance = "1"))', null, false);
		$this->db->group_start();
		$this->db->where('a.id_po', null);
		$this->db->or_where('a.id_po', '');
		$this->db->group_end();
		$this->db->not_like('a.no_doc', 'PI-', 'after');
		$this->db->group_by('a.no_doc');
		$q_data = $this->db->get();
		$this->auditObserve($q_data);
		$data = $q_data ? $q_data->result() : array();

		$this->template->set('status', $this->status);
		$this->template->set('data', $data);
		$this->template->page_icon('fa fa-list');
		$this->template->title('Expense Approval By Management');
		$this->template->render('index_approval_manage');

		$status = $this->auditQueriesOk ? 1 : 0;
		$this->completeAudit('Membuka daftar approval expense management', $status, array(
			'page' => 'index_approval_manage',
			'item_count' => is_array($data) ? count($data) : 0,
			'outcome' => $this->auditQueriesOk ? 'rendered' : 'query_error'
		));
	}
	public function expense_list_all()
	{
		$this->beginAudit(__FUNCTION__, array('page' => 'index', 'all' => 1), array('page', 'all', 'outcome'));
		// $data = $this->Expense_model->GetListData();
		// $data_detail = $this->Expense_model->GetListDataAll();
		// $this->template->page_icon('fa fa-list');
		$this->template->title('Expense Report List');
		$this->template->set('status', $this->status);
		$this->template->set(['all' => 1]);
		// $this->template->set('results', $data);
		// $this->template->set('data_detail', $data_detail);
		$this->template->render('index');
		$this->completeAudit('Membuka seluruh daftar expense report', 1, array(
			'page' => 'index',
			'all' => 1,
			'outcome' => 'rendered'
		));
	}

	public function approval($id)
	{
		$this->beginAudit(__FUNCTION__, array('id' => $id), array('id', 'no_doc', 'form_type', 'outcome'));
		$data = $this->Expense_model->GetDataHeader($id);
		$this->auditObserve($data);

		$detail_files = [];
		if (!empty($data->no_doc) && $this->db->table_exists('tr_expense_detail_file')) {
			$q_df = $this->db->get_where('tr_expense_detail_file', ['no_doc' => $data->no_doc]);
			$this->auditObserve($q_df);
			$get_df = $q_df ? $q_df->result() : array();
			foreach ($get_df as $df) {
				$detail_files[$df->id_detail][] = $df;
			}
		}
		$this->template->set('detail_files', $detail_files);
		$data_detail 	= !empty($data->no_doc) ? $this->Expense_model->GetDataDetail($data->no_doc) : array();
		$this->auditObserve($data_detail);
		$data_budget 	= $this->All_model->GetComboBudget('', 'EXPENSE', date('Y'));
		$this->auditObserve($data_budget);
		$data_coa 		= $this->Coa_expense_model->GetDataWithJenis('Expense');
		$this->auditObserve($data_coa);
		$coa_field 		= !empty($data_coa->coa) ? $data_coa->coa : '';
		$coa_array 		= explode(';', $coa_field);
		$option_coa 	= $this->All_model->GetListCoa($coa_array);
		$this->auditObserve($option_coa);
		$q_bank 		= $this->db->get_where('ms_bank', ['deleted' => 0]);
		$this->auditObserve($q_bank);
		$list_bank 		= $q_bank ? $q_bank->result_array() : array();

		$this->template->set('list_bank', $list_bank);
		$this->template->set('data_coa', $data_coa);
		$this->template->set('option_coa', $option_coa);
		$this->template->set('data_budget', $data_budget);
		$this->template->set('data_detail', $data_detail);
		$this->template->set('status', $this->status);
		$this->template->set('data', $data);
		$this->template->set('stsview', 'approval');
		$this->template->page_icon('fa fa-list');

		$form_type = 'direct';
		if (!empty($data->pettycash)) {
			$form_type = 'pc';
			$this->template->render('form_pc');
		} else if (!empty($data->id_kasbon) || (isset($data->total_kasbon) && floatval($data->total_kasbon) > 0)) {
			$form_type = 'report';
			$q_kb = $this->db->get_where('tr_kasbon', ['no_doc' => $data->id_kasbon]);
			$this->auditObserve($q_kb);
			$data_kasbon = $q_kb ? $q_kb->row() : null;

			$detail_items = [];
			if (is_array($data_detail)) {
				foreach ($data_detail as $rec) {
					$row_coa = !empty($rec->coa) ? $rec->coa : '1304-01-01';
					$q_c = $this->db->query("SELECT nama FROM " . DBACC . ".coa_master WHERE no_perkiraan = '" . $row_coa . "'");
					$this->auditObserve($q_c);
					$row_c = $q_c ? $q_c->row() : null;
					$rec_qty = isset($rec->qty) ? floatval($rec->qty) : 0;
					$rec_kasbon = isset($rec->kasbon) ? floatval($rec->kasbon) : 0;
					$rec_harga = isset($rec->harga) ? floatval($rec->harga) : 0;
					$rec_expense = isset($rec->expense) ? floatval($rec->expense) : 0;

					$detail_items[] = [
						'id' => isset($rec->id) ? $rec->id : null,
						'deskripsi' => isset($rec->deskripsi) ? $rec->deskripsi : '',
						'keterangan' => isset($rec->keterangan) ? $rec->keterangan : '',
						'tanggal' => isset($rec->tanggal) ? $rec->tanggal : '',
						'coa' => $row_coa,
						'coa_name' => !empty($row_c) ? $row_c->nama : 'Peralatan Kantor',
						'qty_kasbon' => $rec_qty,
						'harga_kasbon' => floatval($rec_kasbon > 0 ? ($rec_kasbon / ($rec_qty > 0 ? $rec_qty : 1)) : $rec_harga),
						'total_kasbon' => $rec_kasbon,
						'qty' => $rec_qty,
						'harga' => $rec_harga,
						'expense' => $rec_expense,
						'doc_file' => isset($rec->doc_file) ? $rec->doc_file : null,
						'id_expense_detail' => isset($rec->id_expense_detail) ? $rec->id_expense_detail : null
					];
				}
			}
			$this->template->set('data_kasbon', $data_kasbon);
			$this->template->set('detail_items', $detail_items);
			$this->template->render('form_report');
		} else {
			$this->template->render('form');
		}

		$status = ($data && $this->auditQueriesOk) ? 1 : 0;
		$this->completeAudit('Membuka form approval expense', $status, array(
			'id' => $id,
			'no_doc' => !empty($data->no_doc) ? $data->no_doc : null,
			'form_type' => $form_type,
			'outcome' => ($data && $this->auditQueriesOk) ? 'rendered' : ($data ? 'query_error' : 'not_found')
		));
	}

	// approve
	public function approve($id = '')
	{
		$this->beginAudit(__FUNCTION__, array('id' => $id), array('id', 'stage', 'status', 'outcome'));
		$result = false;
		$stage = null;
		$auditStatusVal = null;
		$err_msg = '';

		if ($id != "") {
			$q_expense 		= $this->db->get_where('tr_expense', ['id' => $id]);
			$this->auditObserve($q_expense);
			$get_expense 	= $q_expense ? $q_expense->row() : null;

			if ($get_expense) {
				// BR-12: Pelapor tidak boleh meng-approve laporannya sendiri
				$current_user = $this->auth->user_name();
				if (!empty($get_expense->nama) && $get_expense->nama === $current_user) {
					$param = array(
						'save' => false,
						'message' => 'Anda tidak dapat menyetujui laporan expense yang Anda buat sendiri (Segregation of Duties)!'
					);
					echo json_encode($param);
					return;
				}

				$q_detail = $this->db->get_where('tr_expense_detail', ['no_doc' => $get_expense->no_doc]);
				$this->auditObserve($q_detail);
				$get_expense_detail = $q_detail ? $q_detail->result() : array();

				// Evaluasi tahap approval: jika sts_finance belum '1' dan status == 0 -> Tahap 1 (Finance)
				if ($get_expense->sts_finance != '1' && (string)$get_expense->status === '0') {
					$stage = 'finance';
					$arr_update = [
						'sts_finance' => '1',
						'app_finance_date' => date('Y-m-d H:i:s'),
						'status' => 1,
						'approved_by' => $this->auth->user_name(),
						'approved_on' => date('Y-m-d H:i:s'),
						'st_reject' => null,
						'reject_reason' => null,
						'reject_reason_finance' => null
					];

					$update_expense = $this->db->update('tr_expense', $arr_update, ['id' => $id]);
					$this->auditObserve($update_expense);
					if ($update_expense) {
						$result = true;
						$auditStatusVal = '1';
					}
				} else {
					// Tahap 2: Direktur / Management Approval
					$stage = 'management';

					$tot_kasbon = floatval($get_expense->total_kasbon);
					$tot_expense = floatval($get_expense->jumlah);
					if ($tot_kasbon <= 0 && !empty($get_expense->id_kasbon)) {
						$qkb = $this->db->get_where('tr_kasbon', ['no_doc' => $get_expense->id_kasbon])->row();
						if ($qkb) {
							$tot_kasbon = floatval($qkb->jumlah_kasbon);
						}
					}
					$selisih = $tot_kasbon - $tot_expense;

					// Jika Lebih Kasbon (kasbon > expense) -> status 3 (Menunggu Pengembalian Kasbon)
					// Jika Sama / Lebih Expense atau Direct Expense -> status 2 (Selesai/Approved)
					if ($selisih > 0 && $tot_kasbon > 0) {
						$statusTarget = 3;
					} else {
						$statusTarget = 2;
					}

					$arr_update = [
						'status' => $statusTarget,
						'status_manage' => 1,
						'approved_by_manage' => $this->auth->user_name(),
						'approved_on_manage' => date('Y-m-d H:i:s'),
						'tgl_approval_direktur' => date('Y-m-d H:i:s'),
						'sts_reject_manage' => null,
						'st_reject' => null,
						'reject_reason' => null
					];

					$update_expense = $this->db->update('tr_expense', $arr_update, ['id' => $id]);
					$this->auditObserve($update_expense);
					if ($update_expense) {
						$result = true;
						$auditStatusVal = (string)$statusTarget;

						// Update detail rows status ke 2
						$this->db->update('tr_expense_detail', ['status' => 2], ['no_doc' => $get_expense->no_doc]);

						// Posting JURNAL 1 final saat disetujui Direktur
						if (method_exists($this, '_post_jurnal_expense_report')) {
							$this->_post_jurnal_expense_report($id);
						}

						$keterangan     = "SUKSES, Approve Direktur/Management expense " . $id;
						$status_act     = 1;
						$nm_hak_akses   = $this->managePermission;
						$kode_universal = $id;
						$jumlah 		= 1;
						$sql            = $this->db->last_query();
						simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status_act);
					}
				}
			}
		}
		$param = array(
			'save' => $result,
			'id' => $id,
			'message' => $result ? 'Berhasil menyetujui expense.' : ($err_msg ?: 'Gagal memproses persetujuan.')
		);
		$auditStatus = $result ? 1 : 0;
		$this->completeAudit(
			$result ? ('Berhasil menyetujui expense (' . ($stage ?: 'approval') . ')') : 'Gagal menyetujui expense',
			$auditStatus,
			array(
				'id' => $id,
				'stage' => $stage,
				'status' => $auditStatusVal,
				'outcome' => $result ? 'approved' : ($id === '' ? 'empty_id' : (!empty($get_expense) ? 'update_failed' : 'not_found'))
			)
		);
		echo json_encode($param);
	}

	// save
	public function save()
	{
		$this->beginAudit(__FUNCTION__, array(
			'id' => $this->input->post('id'),
			'no_doc' => $this->input->post('no_doc'),
			'departement' => $this->input->post('departement')
		), array('id', 'no_doc', 'departement', 'amount', 'outcome', 'reason'));

		$post = $this->input->post();

		$id             		= $this->input->post("id");
		$tgl_doc  				= $this->input->post("tgl_doc");
		$no_doc		    		= $this->input->post("no_doc");
		$no_docc		    	= $this->input->post("no_docc");
		$departement			= $this->input->post("departement");
		$nama					= $this->input->post("nama");
		$approval				= $this->input->post("approval");
		$informasi				= $this->input->post("informasi");
		$bank_id				= $this->input->post("bank_id");
		$accnumber				= $this->input->post("accnumber");
		$accname				= $this->input->post("accname");
		$pettycash				= $this->input->post("pettycash");

		$coa					= $this->input->post("coa");
		$detail_id				= $this->input->post("detail_id");
		$id_detail				= $this->input->post("id_detail");
		$deskripsi				= $this->input->post("deskripsi");
		$spesifikasi			= $this->input->post("spesifikasi");
		$qty					= $this->input->post("qty");
		$harga					= $this->input->post("harga");
		$kasbon					= $this->input->post("kasbon");
		$expense				= $this->input->post("expense");
		$tanggal				= $this->input->post("tanggal");
		$keterangan				= $this->input->post("keterangan");
		$filename				= $this->input->post("filename");
		$id_kasbon				= $this->input->post("id_kasbon");
		$total_kasbon			= $this->input->post("total_kasbon");
		$total_expense			= $this->input->post("total_expense");
		$grand_total			= $this->input->post("grand_total");
		$id_expense_detail		= $this->input->post("id_expense_detail");
		$no_doc_kasbon			= $this->input->post("no_doc_kasbon");
		$idKasbon				= $this->input->post("idKasbon");

		$pengembalian = $this->input->post('pengembalian');
		if (!isset($pengembalian)) {
			$pengembalian = '';
		}

		$penggantian = $this->input->post('penggantian');
		if (!isset($penggantian)) {
			$penggantian = '';
		}

		// Validasi server-side: untuk petty cash, semua detail WAJIB upload file bon bukti
		if (!empty($pettycash) && !empty($detail_id)) {
			$upload_errors = [];
			foreach ($detail_id as $keys => $val) {
				// Skip jika row kasbon (sudah punya file dari kasbon)
				if (!empty($id_kasbon[$keys])) continue;

				// Cek apakah sudah ada file sebelumnya (edit mode)
				$existing_file = (!empty($filename[$keys])) ? $filename[$keys] : '';

				// Jika belum ada file existing, maka file baru WAJIB diupload
				if ($existing_file == '' && empty($_FILES['doc_file_' . $val]['name'])) {
					$upload_errors[] = "Bon Bukti pada baris " . ($keys + 1) . " wajib diupload";
				}
			}
			if (!empty($upload_errors)) {
				$param = array(
					'save' => false,
					'message' => implode(', ', $upload_errors)
				);
				$this->completeAudit('Gagal menyimpan expense (validasi bon bukti)', 0, array(
					'id' => $id,
					'no_doc' => $no_doc,
					'departement' => $departement,
					'amount' => $total_expense,
					'outcome' => 'validation_failed',
					'reason' => 'bon_bukti_required'
				));
				echo json_encode($param);
				return;
			}
		}

		// Validasi server-side: Keterangan laporan wajib diisi (FR-F06)
		if (empty(trim((string)$informasi))) {
			$param = array(
				'save' => false,
				'message' => 'Keterangan laporan expense wajib diisi!'
			);
			echo json_encode($param);
			return;
		}

		// Validasi server-side: Total realisasi pengeluaran harus > 0 (FR-F08)
		if ($total_expense <= 0) {
			$param = array(
				'save' => false,
				'message' => 'Total realisasi pengeluaran harus lebih dari 0!'
			);
			echo json_encode($param);
			return;
		}

		// Validasi server-side (FR-F05): Setiap item Expense Report dengan nilai realisasi > 0 WAJIB memiliki minimal 1 file bon/nota
		$is_expense_report = !empty($no_doc_kasbon) || !empty($id_kasbon);
		if ($is_expense_report && !empty($detail_id)) {
			$missing_proof = [];
			foreach ($detail_id as $keys => $val) {
				$exp_val = isset($expense[$keys]) ? floatval($expense[$keys]) : (isset($qty[$keys], $harga[$keys]) ? floatval($qty[$keys]) * floatval($harga[$keys]) : 0);
				if ($exp_val > 0) {
					$has_new = !empty($_FILES['doc_files_' . $val]['name'][0]) || !empty($_FILES['doc_file_' . $val]['name']);
					$has_existing = !empty($post['existing_files_' . $val]) || !empty($filename[$keys]);
					if (!$has_new && !$has_existing) {
						// Periksa juga ke database tr_expense_detail_file jika sedang mode edit/revisi
						$existing_in_db = 0;
						if (!empty($id_detail[$keys])) {
							$existing_in_db = $this->db->get_where('tr_expense_detail_file', ['id_detail' => $id_detail[$keys]])->num_rows();
						}
						if ($existing_in_db == 0) {
							$item_label = !empty($deskripsi[$keys]) ? $deskripsi[$keys] : 'Baris ' . ($keys + 1);
							$missing_proof[] = $item_label;
						}
					}
				}
			}
			if (!empty($missing_proof)) {
				$param = array(
					'save' => false,
					'message' => 'Bon / Bukti pengeluaran wajib dilampirkan untuk setiap item yang ada realisasinya. Belum ada bukti pada: ' . implode(', ', $missing_proof)
				);
				$this->completeAudit('Gagal menyimpan expense (validasi bukti item wajib)', 0, array(
					'id' => $id,
					'no_doc' => $no_doc,
					'departement' => $departement,
					'amount' => $total_expense,
					'outcome' => 'validation_failed',
					'reason' => 'item_proof_required'
				));
				echo json_encode($param);
				return;
			}
		}

		//proses utama update tr_expense
		$this->db->trans_begin();
		if ($id != "") {
			$grand_total = $total_kasbon - $total_expense;
			if ($grand_total < 0) {
				$kurang_bayar 	= abs($grand_total);
				$lebih_bayar	= null;
			} else if ($grand_total > 0 && $total_kasbon > 0) {
				$kurang_bayar 	= null;
				$lebih_bayar	= $grand_total;
			} else {
				$kurang_bayar 	= null;
				$lebih_bayar	= null;
			}

			$pathBuktiPengembalian = [];
			if (!empty($post['existing_bukti_pengembalian'])) {
				foreach ($post['existing_bukti_pengembalian'] as $eb) {
					if (!empty($eb)) $pathBuktiPengembalian[] = $eb;
				}
			}
			if (!empty($_FILES['bukti_pengembalian']['name'][0])) {
				$uploadDirectory = 'assets/expense_bukti_pengembalian/';
				if (!is_dir($uploadDirectory)) {
					mkdir($uploadDirectory, 0777, true);
				}
				foreach ($_FILES['bukti_pengembalian']['name'] as $index => $name) {
					if (!empty($_FILES['bukti_pengembalian']['tmp_name'][$index])) {
						$tmpName = $_FILES['bukti_pengembalian']['tmp_name'][$index];
						$cleanName = time() . '_' . rand(100, 999) . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($name));
						$filePath = $uploadDirectory . $cleanName;
						if (move_uploaded_file($tmpName, $filePath)) {
							$pathBuktiPengembalian[] = $filePath;
						}
					}
				}
			}
			$buktiPengembalian = ($pengembalian == 2 && !empty($pathBuktiPengembalian)) ? implode(";", $pathBuktiPengembalian) : null;

			$data = array(
				'tgl_doc' => $tgl_doc,
				'jumlah' => $total_expense,
				'total_kasbon' => $total_kasbon,
				'informasi' => $informasi,
				'bank_id' => $bank_id,
				'accnumber' => $accnumber,
				'status' => 0,
				'accname' => $accname,
				'pettycash' => $pettycash,
				'tipe_pengembalian' => $pengembalian,
				'tipe_penggantian' => $penggantian,
				'bukti_pengembalian' => $buktiPengembalian,
				'lebih_bayar' => $lebih_bayar,
				'kurang_bayar' => $kurang_bayar,
				'keterangan_kurang_bayar' => $this->input->post('keterangan_kurang_bayar'),
				'st_reject' => null,
				'reject_reason' => null,
				'reject_reason_finance' => null,
				'rejected_by' => null,
				'rejected_on' => null,
				'sts_reject' => null,
				'sts_reject_manage' => null,
				'modified_by' => $this->auth->user_name(),
				'modified_on' => date("Y-m-d H:i:s")
			);
			$this->db->update('tr_expense', $data, ['id' => $id]);

			$this->db->delete('tr_expense_detail', ['no_doc' => $this->auth->user_id()]);
			$upload_failed = false;
			$upload_failed_msg = '';
			if (!empty($detail_id)) {
				foreach ($detail_id as $keys => $val) {
					$no_doc = $no_doc;

					// proses update jika ada id_detail
					if ($id_detail[$keys] !== '') {
						if ($qty[$keys] > 0) {
							$config['upload_path'] = './assets/expense/';
							$config['allowed_types'] = '*';
							$config['remove_spaces'] = TRUE;
							$config['encrypt_name'] = TRUE;
							$filenames = '';
							$get_filenames = $this->db->select('doc_file')->get_where('tr_expense_detail', ['id' => $id_detail[$keys]])->row_array();
							if (!empty($get_filenames)) {
								$filenames = $get_filenames['doc_file'];
							}
							if (!empty($_FILES['doc_file_' . $val]['name'])) {
								$_FILES['file']['name'] = $_FILES['doc_file_' . $val]['name'];
								$_FILES['file']['type'] = $_FILES['doc_file_' . $val]['type'];
								$_FILES['file']['tmp_name'] = $_FILES['doc_file_' . $val]['tmp_name'];
								$_FILES['file']['error'] = $_FILES['doc_file_' . $val]['error'];
								$_FILES['file']['size'] = $_FILES['doc_file_' . $val]['size'];
								$this->load->library('upload', $config);
								$this->upload->initialize($config);
								if ($this->upload->do_upload('file')) {
									$uploadData = $this->upload->data();
									$filenames = $uploadData['file_name'];
								} else {
									// Upload gagal untuk petty cash - batalkan proses
									if (!empty($pettycash)) {
										$upload_failed = true;
										$upload_failed_msg = 'Gagal upload file Bon Bukti baris ' . ($keys + 1) . ': ' . $this->upload->display_errors('', '');
										break;
									}
								}
							}

							// Validasi: untuk petty cash, file harus ada setelah proses upload
							if (!empty($pettycash) && empty($filenames) && empty($id_kasbon[$keys])) {
								$upload_failed = true;
								$upload_failed_msg = 'Bon Bukti pada baris ' . ($keys + 1) . ' wajib diupload';
								break;
							}

							// untuk update kasbon kasbon pr non po yang dikirim dari form_pc
							if (isset($post['kasbon_pr_non_po_' . $detail_id[$keys]])) {
								$data_detail =  array(
									'no_doc' => $no_doc,
									'deskripsi' => $deskripsi[$keys],
									'qty' => $qty[$keys],
									'harga' => $harga[$keys],
									'total_harga' => ($qty[$keys] * $harga[$keys]),
									'expense' => $expense[$keys],
									'tanggal' => date('Y-m-d', strtotime($tanggal[$keys])),
									'keterangan' => $keterangan[$keys],
									'coa' => $coa[$keys],
									'doc_file' => $filenames,
									'modified_by' => $this->auth->user_name(),
									'modified_on' => date("Y-m-d h:i:s"),
									'id_expense_detail' => (!empty($id_expense_detail[$keys])) ? $id_expense_detail[$keys] : '',
									'kasbon_pr_non_po_pett' => $post['kasbon_pr_non_po_' . $detail_id[$keys]]
								);

								$this->db->update('tr_kasbon', ['id_expense_pett_pr_non_po' => $no_doc], ['no_doc' => $post['kasbon_pr_non_po_' . $detail_id[$keys]]]);
							}

							// untuk update tr_expense bila ada pengembalian expense & insert ke tr_pengembalian_expense yang dikirim dari form_pc
							else if (isset($post['pengembalian_expense_' . $detail_id[$keys]])) {
								$data_detail =  array(
									'no_doc' => $no_doc,
									'deskripsi' => $deskripsi[$keys],
									'qty' => $qty[$keys],
									'harga' => $harga[$keys],
									'total_harga' => ($qty[$keys] * $harga[$keys]),
									'expense' => $expense[$keys],
									'tanggal' => date('Y-m-d', strtotime($tanggal[$keys])),
									'keterangan' => $keterangan[$keys],
									'coa' => $coa[$keys],
									'doc_file' => $filenames,
									'modified_by' => $this->auth->user_name(),
									'modified_on' => date("Y-m-d h:i:s"),
									'id_expense_detail' => (!empty($id_expense_detail[$keys])) ? $id_expense_detail[$keys] : '',
									'id_expense_bayar_sisa' => $post['pengembalian_expense_' . $detail_id[$keys]]
								);

								$this->db->update('tr_expense', ['expense_id_kembalian' => $no_doc], ['no_doc' => $post['pengembalian_expense_' . $detail_id[$keys]]]);

								$this->db->insert('tr_pengembalian_expense', [
									'no_doc' => $post['pengembalian_expense_' . $detail_id[$keys]],
									'transfer_coa_bank' => $coa[$keys],
									'transfer_tanggal' => date('Y-m-d', strtotime($tanggal[$keys])),
									'transfer_jumlah' => $expense[$keys],
									'jalur_pettycash' => 1,
									'id_expense_pettycash' => $no_doc,
									'created_by' => $this->auth->user_id(),
									'created_date' => date('Y-m-d H:i:s')
								]);
							}

							// untuk update tr_expense detail
							else {
								$data_detail =  array(
									'no_doc' => $no_doc,
									'deskripsi' => $deskripsi[$keys],
									'qty' => $qty[$keys],
									'harga' => $harga[$keys],
									'total_harga' => ($qty[$keys] * $harga[$keys]),
									'kasbon' => $kasbon[$keys],
									'expense' => $expense[$keys],
									'status' => 1,
									'tanggal' => date('Y-m-d', strtotime($tanggal[$keys])),
									'keterangan' => $keterangan[$keys],
									'coa' => $coa[$keys],
									'doc_file' => $filenames,
									'id_kasbon' => (($id_kasbon[$keys]) ? $id_kasbon[$keys] : null),
									'modified_by' => $this->auth->user_name(),
									'modified_on' => date("Y-m-d h:i:s"),
									'id_expense_detail' => (!empty($id_expense_detail[$keys]) ? $id_expense_detail[$keys] : null)
								);
							}
							$this->db->update('tr_expense_detail', $data_detail, ['id' => $id_detail[$keys]]);
							$existing_kept = isset($post['existing_files_' . $val]) ? $post['existing_files_' . $val] : [];
							$this->_handle_detail_files($no_doc, $id_detail[$keys], $val, $existing_kept);
						}
					}

					// proses insert karena tidak ada id_detail
					else {
						if ($qty[$keys] > 0) {
							$config['upload_path'] = './assets/expense/';
							$config['allowed_types'] = '*';
							$config['remove_spaces'] = TRUE;
							$config['encrypt_name'] = TRUE;
							$filenames = '';
							$get_filenames = $this->db->select('doc_file')->get_where('tr_expense_detail', ['id' => $id]);
							if (!empty($_FILES['doc_file_' . $val]['name'])) {
								$_FILES['file']['name'] = $_FILES['doc_file_' . $val]['name'];
								$_FILES['file']['type'] = $_FILES['doc_file_' . $val]['type'];
								$_FILES['file']['tmp_name'] = $_FILES['doc_file_' . $val]['tmp_name'];
								$_FILES['file']['error'] = $_FILES['doc_file_' . $val]['error'];
								$_FILES['file']['size'] = $_FILES['doc_file_' . $val]['size'];
								$this->load->library('upload', $config);
								$this->upload->initialize($config);
								if ($this->upload->do_upload('file')) {
									$uploadData = $this->upload->data();
									$filenames = $uploadData['file_name'];
								} else {
									// Upload gagal untuk petty cash - batalkan proses
									if (!empty($pettycash)) {
										$upload_failed = true;
										$upload_failed_msg = 'Gagal upload file Bon Bukti baris ' . ($keys + 1) . ': ' . $this->upload->display_errors('', '');
										break;
									}
								}
							}

							// Validasi: untuk petty cash, file harus ada setelah proses upload
							if (!empty($pettycash) && empty($filenames) && empty($id_kasbon[$keys])) {
								$upload_failed = true;
								$upload_failed_msg = 'Bon Bukti pada baris ' . ($keys + 1) . ' wajib diupload';
								break;
							}

							// update tr_kasbon berdasarkan kasbon_pr_non_po
							if (isset($post['kasbon_pr_non_po_' . $detail_id[$keys]])) {
								$data_detail =  array(
									'no_doc' => $no_doc,
									'deskripsi' => $deskripsi[$keys],
									'qty' => $qty[$keys],
									'harga' => $harga[$keys],
									'total_harga' => ($qty[$keys] * $harga[$keys]),
									'expense' => $expense[$keys],
									'tanggal' => date('Y-m-d', strtotime($tanggal[$keys])),
									'keterangan' => $keterangan[$keys],
									'coa' => $coa[$keys],
									'doc_file' => $filenames,
									'created_by' => $this->auth->user_name(),
									'created_on' => date("Y-m-d h:i:s"),
									'modified_by' => $this->auth->user_name(),
									'modified_on' => date("Y-m-d h:i:s"),
									'id_expense_detail' => (!empty($id_expense_detail[$keys])) ? $id_expense_detail[$keys] : '',
									'kasbon_pr_non_po_pett' => $post['kasbon_pr_non_po_' . $detail_id[$keys]]
								);

								$this->db->update('tr_kasbon', ['id_expense_pett_pr_non_po' => $no_doc], ['no_doc' => $post['kasbon_pr_non_po_' . $detail_id[$keys]]]);
							}

							// update tr_expense berdasarkan pengembalian expense, dan insert tr_pengembalian_expense
							else if (isset($post['pengembalian_expense_' . $detail_id[$keys]])) {
								$data_detail =  array(
									'no_doc' => $no_doc,
									'deskripsi' => $deskripsi[$keys],
									'qty' => $qty[$keys],
									'harga' => $harga[$keys],
									'total_harga' => ($qty[$keys] * $harga[$keys]),
									'expense' => $expense[$keys],
									'tanggal' => date('Y-m-d', strtotime($tanggal[$keys])),
									'keterangan' => $keterangan[$keys],
									'coa' => $coa[$keys],
									'doc_file' => $filenames,
									'created_by' => $this->auth->user_name(),
									'created_on' => date("Y-m-d h:i:s"),
									'modified_by' => $this->auth->user_name(),
									'modified_on' => date("Y-m-d h:i:s"),
									'id_expense_detail' => (!empty($id_expense_detail[$keys])) ? $id_expense_detail[$keys] : '',
									'id_expense_bayar_sisa' => $post['pengembalian_expense_' . $detail_id[$keys]]
								);

								$this->db->update('tr_expense', ['expense_id_kembalian' => $no_doc], ['no_doc' => $post['pengembalian_expense_' . $detail_id[$keys]]]);

								$this->db->insert('tr_pengembalian_expense', [
									'no_doc' => $post['pengembalian_expense_' . $detail_id[$keys]],
									'transfer_coa_bank' => $coa[$keys],
									'transfer_tanggal' => date('Y-m-d', strtotime($tanggal[$keys])),
									'transfer_jumlah' => $expense[$keys],
									'jalur_pettycash' => 1,
									'id_expense_pettycash' => $no_doc,
									'created_by' => $this->auth->user_id(),
									'created_date' => date('Y-m-d H:i:s')
								]);
							}

							// insert tr_expense_detail baru 
							else {
								$data_detail =  array(
									'no_doc' => $no_doc,
									'deskripsi' => $deskripsi[$keys],
									'qty' => $qty[$keys],
									'harga' => $harga[$keys],
									'total_harga' => ($qty[$keys] * $harga[$keys]),
									'kasbon' => $kasbon[$keys],
									'expense' => $expense[$keys],
									'tanggal' => date('Y-m-d', strtotime($tanggal[$keys])),
									'keterangan' => $keterangan[$keys],
									'coa' => $coa[$keys],
									'doc_file' => $filenames,
									'id_kasbon' => $id_kasbon[$keys],
									'created_by' => $this->auth->user_name(),
									'created_on' => date("Y-m-d h:i:s"),
									'modified_by' => $this->auth->user_name(),
									'modified_on' => date("Y-m-d h:i:s"),
									'id_expense_detail' => (!empty($id_expense_detail[$keys])) ? $id_expense_detail[$keys] : ''
								);
							}
							$this->All_model->dataSave('tr_expense_detail', $data_detail);
							$new_dtl_id = $this->db->insert_id();
							$this->_handle_detail_files($no_doc, $new_dtl_id, $val);
						}
					}
				}
			}

			if ($upload_failed) {
				$this->db->trans_rollback();
				$param = array(
					'save' => false,
					'message' => $upload_failed_msg
				);
				$this->completeAudit('Gagal memperbarui expense (upload file gagal)', 0, array(
					'id' => $id,
					'no_doc' => $no_doc,
					'departement' => $departement,
					'amount' => $total_expense,
					'outcome' => 'upload_failed',
					'reason' => $upload_failed_msg
				));
				echo json_encode($param);
				return;
			}

			if ($this->db->trans_status() === FALSE || $this->db->trans_commit() === FALSE) {
				$this->db->trans_rollback();
				$result = FALSE;
			} else {
				$result = TRUE;
			}
		}
		// proses utama insert tr_expense
		else {
			$no_doc = $this->All_model->GetAutoGenerate('format_expense');

			$uploadDirectory = "./assets/expense/";
			$pathBonBukti = [];
			$pathBuktiPengembalian = [];

			if (!empty($_FILES['doc_file']['name'][0])) {
				foreach ($_FILES['doc_file']['name'] as $index => $name) {
					$tmpName = $_FILES['doc_file']['tmp_name'][$index];
					$filePath = $uploadDirectory . basename($name);

					if (move_uploaded_file($tmpName, $filePath)) {
						$pathBonBukti[] = $filePath;
					} else {
						echo "Gagal mengunggah file: $name<br>";
					}
				}
			}
			$bonBukti = implode(";", $pathBonBukti);

			if (!empty($post['existing_bukti_pengembalian'])) {
				foreach ($post['existing_bukti_pengembalian'] as $eb) {
					if (!empty($eb)) $pathBuktiPengembalian[] = $eb;
				}
			}
			if (!empty($_FILES['bukti_pengembalian']['name'][0])) {
				if (!is_dir($uploadDirectory)) {
					mkdir($uploadDirectory, 0777, true);
				}
				foreach ($_FILES['bukti_pengembalian']['name'] as $index => $name) {
					if (!empty($_FILES['bukti_pengembalian']['tmp_name'][$index])) {
						$tmpName = $_FILES['bukti_pengembalian']['tmp_name'][$index];
						$cleanName = time() . '_' . rand(100, 999) . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($name));
						$filePath = $uploadDirectory . $cleanName;

						if (move_uploaded_file($tmpName, $filePath)) {
							$pathBuktiPengembalian[] = $filePath;
						}
					}
				}
			}

			if ($pengembalian == 2) {
				$buktiPengembalian = implode(";", $pathBuktiPengembalian);
			} else {
				$buktiPengembalian = null;
			}

			$grand_total = $total_kasbon - $total_expense;
			if ($grand_total < 0) {
				$kurang_bayar 	= abs($grand_total);
				$lebih_bayar	= null;
			} else if ($grand_total > 0 && $total_kasbon > 0) {
				$kurang_bayar 	= null;
				$lebih_bayar	= $grand_total;
			} else {
				$kurang_bayar 	= null;
				$lebih_bayar	= null;
			}

			$data =  array(
				'no_doc' 				=> $no_doc,
				'tgl_doc' 				=> date('Y-m-d', strtotime($tgl_doc)),
				'departement' 			=> $departement,
				'nama' 					=> $nama,
				'informasi' 			=> $informasi,
				'bank_id' 				=> $bank_id,
				'accnumber' 			=> $accnumber,
				'accname' 				=> $accname,
				'pettycash' 			=> (!empty($pettycash)) ? $pettycash : null,
				'approval' 				=> $approval,
				'status' 				=> 0,
				'jumlah' 				=> $total_expense,
				'total_kasbon' 			=> $total_kasbon,
				'tipe_penggantian' 		=> $penggantian,
				'tipe_pengembalian' 	=> $pengembalian,
				'bon_bukti' 			=> $bonBukti,
				'bukti_pengembalian' 	=> $buktiPengembalian,
				'lebih_bayar' 			=> $lebih_bayar ?: null,
				'kurang_bayar' 			=> $kurang_bayar ?: null,
				'keterangan_kurang_bayar'=> $this->input->post('keterangan_kurang_bayar'),
				'id_kasbon'				=> $no_doc_kasbon ?: null,
				'created_by' 			=> $this->auth->user_name(),
				'created_on' 			=> date("Y-m-d H:i:s")
			);

			$insert_expense = $this->db->insert('tr_expense', $data);

			if (!$insert_expense) {
				print_r($this->db->error($insert_expense));
				exit;
			}

			$this->db->delete('tr_expense_detail', ['no_doc' => $this->auth->user_id()]);

			// jika detail_id tidak kosong
			$upload_failed = false;
			$upload_failed_msg = '';
			if (!empty($detail_id)) {
				foreach ($detail_id as $keys => $val) {
					$no_doc			= $no_doc;
					if ($qty[$keys] > 0) {
						$config['upload_path'] = './assets/expense/';
						$config['allowed_types'] = '*';
						$config['remove_spaces'] = TRUE;
						$config['encrypt_name'] = TRUE;
						$filenames = "";
						if (!empty($_FILES['doc_file_' . $val]['name'])) {
							$_FILES['file']['name'] = $_FILES['doc_file_' . $val]['name'];
							$_FILES['file']['type'] = $_FILES['doc_file_' . $val]['type'];
							$_FILES['file']['tmp_name'] = $_FILES['doc_file_' . $val]['tmp_name'];
							$_FILES['file']['error'] = $_FILES['doc_file_' . $val]['error'];
							$_FILES['file']['size'] = $_FILES['doc_file_' . $val]['size'];
							$this->load->library('upload', $config);
							$this->upload->initialize($config);
							if ($this->upload->do_upload('file')) {
								$uploadData = $this->upload->data();
								$filenames = $uploadData['file_name'];
							} else {
								// Upload gagal untuk petty cash - batalkan proses
								if (!empty($pettycash)) {
									$upload_failed = true;
									$upload_failed_msg = 'Gagal upload file Bon Bukti baris ' . ($keys + 1) . ': ' . $this->upload->display_errors('', '');
									break;
								}
							}
						}

						// Validasi: untuk petty cash, file harus ada setelah proses upload
						if (!empty($pettycash) && empty($filenames) && empty($id_kasbon[$keys])) {
							$upload_failed = true;
							$upload_failed_msg = 'Bon Bukti pada baris ' . ($keys + 1) . ' wajib diupload';
							break;
						}

						//update tr_kasbon berdasarkan kasbon_pr_non_po yang datanya dari form pc
						if (isset($post['kasbon_pr_non_po_' . $detail_id[$keys]])) {
							$data_detail =  array(
								'no_doc' => $no_doc,
								'deskripsi' => $deskripsi[$keys],
								'qty' => $qty[$keys],
								'harga' => $harga[$keys],
								'total_harga' => ($qty[$keys] * $harga[$keys]),
								'expense' => $expense[$keys],
								'tanggal' => date('Y-m-d', strtotime($tanggal[$keys])),
								'keterangan' => $keterangan[$keys],
								'coa' => $coa[$keys],
								'created_by' => $this->auth->user_name(),
								'created_on' => date("Y-m-d h:i:s"),
								'doc_file' => $filenames,
								'id_expense_detail' => (isset($id_expense_detail[$keys])) ? $id_expense_detail[$keys] : null,
								'kasbon_pr_non_po_pett' => $post['kasbon_pr_non_po_' . $detail_id[$keys]]
							);

							$this->db->update('tr_kasbon', ['id_expense_pett_pr_non_po' => $no_doc], ['no_doc' => $post['kasbon_pr_non_po_' . $detail_id[$keys]]]);
						}

						//update tr_expense berdasarkan pengembalian_expense yang datanya dari form_pc
						else if (isset($post['pengembalian_expense_' . $detail_id[$keys]])) {
							$data_detail =  array(
								'no_doc' => $no_doc,
								'deskripsi' => $deskripsi[$keys],
								'qty' => $qty[$keys],
								'harga' => $harga[$keys],
								'total_harga' => ($qty[$keys] * $harga[$keys]),
								'expense' => $expense[$keys],
								'tanggal' => date('Y-m-d', strtotime($tanggal[$keys])),
								'keterangan' => $keterangan[$keys],
								'doc_file' => $filenames,
								'coa' => $coa[$keys],
								'created_by' => $this->auth->user_name(),
								'created_on' => date("Y-m-d h:i:s"),
								'id_expense_detail' => (isset($id_expense_detail[$keys])) ? $id_expense_detail[$keys] : null,
								'id_expense_bayar_sisa' => $post['pengembalian_expense_' . $detail_id[$keys]]
							);

							$update_expense_kembalian = $this->db->update('tr_expense', ['expense_id_kembalian' => $no_doc], ['no_doc' => $post['pengembalian_expense_' . $detail_id[$keys]]]);
							if (!$update_expense_kembalian) {
								print_r($this->db->error($update_expense_kembalian));
								exit;
							}

							$insert_log_kembalian = $this->db->insert('tr_pengembalian_expense', [
								'no_doc' => $post['pengembalian_expense_' . $detail_id[$keys]],
								'transfer_coa_bank' => $coa[$keys],
								'transfer_tanggal' => date('Y-m-d', strtotime($tanggal[$keys])),
								'transfer_jumlah' => $expense[$keys],
								'jalur_pettycash' => 1,
								'id_expense_pettycash' => $no_doc,
								'created_by' => $this->auth->user_id(),
								'created_date' => date('Y-m-d H:i:s')
							]);
							if (!$insert_log_kembalian) {
								print_r($this->db->error($insert_log_kembalian));
								exit;
							}
						}

						// insert pertama kalinya ke tr_expense_detail
						else {
							$data_detail =  array(
								'no_doc' => $no_doc,
								'qty' => (isset($qty[$keys]) ? $qty[$keys] : 0),
								'harga' => (isset($harga[$keys]) ? $harga[$keys] : 0),
								'total_harga' => ($qty[$keys] * $harga[$keys]),
								'kasbon' => (isset($kasbon[$keys]) ? $kasbon[$keys] : 0),
								'expense' => (isset($expense[$keys]) ? $expense[$keys] : 0),
								'tanggal' => date('Y-m-d', strtotime($tanggal[$keys])),
								'deskripsi' => $deskripsi[$keys],
								'keterangan' => $keterangan[$keys],
								'status'	=> 1,
								'doc_file' => $filenames,
								'id_kasbon' => (($id_kasbon[$keys]) ? $id_kasbon[$keys] : null),
								'coa' => (isset($coa[$keys]) ? $coa[$keys] : ""),
								'created_by' => $this->auth->user_name(),
								'created_on' => date("Y-m-d h:i:s"),
								'id_expense_detail' => (!empty($id_expense_detail[$keys]) ? $id_expense_detail[$keys] : null)
							);
						}

						$insert_detail_expense = $this->db->insert('tr_expense_detail', $data_detail);
						if (!$insert_detail_expense) {
							print_r($this->db->error($insert_detail_expense));
							exit;
						}
						$new_dtl_id = $this->db->insert_id();
						$this->_handle_detail_files($no_doc, $new_dtl_id, $val);
					}
				}
			}

			if ($upload_failed) {
				$this->db->trans_rollback();
				$param = array(
					'save' => false,
					'message' => $upload_failed_msg
				);
				$this->completeAudit('Gagal membuat expense baru (upload file gagal)', 0, array(
					'id' => $id,
					'no_doc' => $no_doc,
					'departement' => $departement,
					'amount' => $total_expense,
					'outcome' => 'upload_failed',
					'reason' => $upload_failed_msg
				));
				echo json_encode($param);
				return;
			}

			if ($this->db->trans_status() === FALSE || $this->db->trans_commit() === FALSE) {
				$this->db->trans_rollback();
				$result = FALSE;
			} else {
				$result = TRUE;
			}
		}

		//proses parsing data
		$param = array(
			'save' => $result,
			'id' => $id
		);
		$status = $result ? 1 : 0;
		$isUpdate = ($id != "");
		$this->completeAudit(
			$result ? ($isUpdate ? 'Berhasil memperbarui expense' : 'Berhasil membuat expense baru') : 'Gagal menyimpan expense: transaksi dibatalkan',
			$status,
			array(
				'id' => $id,
				'no_doc' => $no_doc,
				'departement' => $departement,
				'amount' => $total_expense,
				'outcome' => $result ? ($isUpdate ? 'updated' : 'created') : 'transaction_failed'
			)
		);
		echo json_encode($param);
	}

	// delete
	public function delete($id)
	{
		$this->beginAudit(__FUNCTION__, array('id' => $id), array('id', 'no_doc', 'outcome'));
		$this->db->trans_begin();
		$data = $this->Expense_model->GetDataHeader($id);
		$this->auditObserve($data !== false);

		$result = false;
		$no_doc = null;
		if ($data) {
			$no_doc = !empty($data->no_doc) ? $data->no_doc : null;
			$q_detail = $this->db->get_where('tr_expense_detail', ['no_doc' => $data->no_doc]);
			$this->auditObserve($q_detail);
			$get_expense_detail = $q_detail ? $q_detail->result() : array();
			if (is_array($get_expense_detail)) {
				foreach ($get_expense_detail as $item) {
					if (isset($item->kasbon_pr_non_po_pett) && $item->kasbon_pr_non_po_pett !== null) {
						$u_kb = $this->db->update('tr_kasbon', ['id_expense_pett_pr_non_po' => null], ['id_expense_pett_pr_non_po' => $data->no_doc]);
						$this->auditObserve($u_kb);
					}
					if (isset($item->id_expense_bayar_sisa) && $item->id_expense_bayar_sisa !== null) {
						$u_ex = $this->db->update('tr_expense', ['expense_id_kembalian' => null], ['expense_id_kembalian' => $data->no_doc]);
						$this->auditObserve($u_ex);
						$d_pg = $this->db->delete('tr_pengembalian_expense', ['id_expense_pettycash' => $data->no_doc]);
						$this->auditObserve($d_pg);
					}
				}
			}

			$d1 = $this->All_model->dataDelete('tr_expense_detail', array('no_doc' => $data->no_doc));
			$this->auditObserve($d1);
			$d2 = $this->All_model->dataDelete('tr_expense', array('no_doc' => $data->no_doc));
			$this->auditObserve($d2);

			if ($this->db->trans_status() === FALSE || $this->db->trans_commit() === FALSE) {
				$this->db->trans_rollback();
				$result = false;
			} else {
				$result = true;
			}
		} else {
			$this->db->trans_rollback();
		}
		$param = array('delete' => $result);
		$auditStatus = $result ? 1 : 0;
		$this->completeAudit(
			$result ? 'Berhasil menghapus expense' : 'Gagal menghapus expense',
			$auditStatus,
			array(
				'id' => $id,
				'no_doc' => $no_doc,
				'outcome' => $result ? 'deleted' : ($data ? 'delete_failed' : 'not_found')
			)
		);
		echo json_encode($param);
	}

	function cekbudget()
	{
		$this->beginAudit(__FUNCTION__, array(
			'coa' => $this->input->post('coa'),
			'tgl_doc' => $this->input->post('tgl_doc'),
			'dtl' => $this->input->post('dtl')
		), array('coa', 'tahun', 'sisa', 'outcome'));

		$dtl		= $this->input->post("dtl");
		$divisi		= $this->input->post("divisi");

		$tanggal	= $this->input->post("tgl_doc");
		$coa	= $this->input->post("coa");
		$tahun = date("Y", strtotime($tanggal));
		$data = $this->Expense_model->GetBudget($coa, $tahun);
		$this->auditObserve(null);
		$param = array();
		$sisaVal = null;
		if ($data !== false && !empty($data)) {
			if ($dtl == '') {
				$bulan = date("n", strtotime($tanggal));
				$budget = 0;
				$terpakai = 0;
				for ($i = 1; $i <= $bulan; $i++) {
					$budget = ($budget + (isset($data->{"bulan_" . $i}) ? $data->{"bulan_" . $i} : 0));
					$terpakai = ($terpakai + (isset($data->{"terpakai_bulan_" . $i}) ? $data->{"terpakai_bulan_" . $i} : 0));
				}
				$sisa = ($budget - $terpakai);
				$sisaVal = $sisa;
				$param = array(
					'budget' => $budget,
					'terpakai' => $terpakai,
					'sisa' => $sisa,
				);
			} else {
				$param = $data;
			}
		} else {
			if ($dtl == '') {
				$sisaVal = 0;
				$param = array(
					'budget' => 0,
					'terpakai' => 0,
					'sisa' => 0,
					'tipe' => '',
				);
			}
		}

		$status = $this->auditQueriesOk ? 1 : 0;
		$this->completeAudit('Mengecek budget COA', $status, array(
			'coa' => $coa,
			'tahun' => $tahun,
			'sisa' => $sisaVal,
			'outcome' => $this->auditQueriesOk ? (!empty($data) ? 'found' : 'not_found') : 'query_error'
		));
		echo json_encode($param);
	}
	// list management transport
	public function transport_req_mgt()
	{
		$this->beginAudit(__FUNCTION__, array('page' => 'transport_req_mgt_list'), array('page', 'item_count', 'outcome'));
		$data = $this->Expense_model->GetListDataTransportRequest($this->auth->user_name());
		$this->auditObserve(null);
		$this->template->set('results', $data);
		$this->template->set('status', $this->status);
		$this->template->page_icon('fa fa-list');
		$this->template->title('Persetujuan Managemen Penggantian Transport');
		$this->template->render('transport_req_mgt_list');
		$status = $this->auditQueriesOk ? 1 : 0;
		$this->completeAudit('Membuka daftar persetujuan manajemen penggantian transport', $status, array(
			'page' => 'transport_req_mgt_list',
			'item_count' => is_array($data) ? count($data) : 0,
			'outcome' => $this->auditQueriesOk ? 'rendered' : 'query_error'
		));
	}

	// list finance transport
	public function transport_req_fin()
	{
		$this->beginAudit(__FUNCTION__, array('page' => 'transport_req_fin_list'), array('page', 'outcome'));
		$this->template->page_icon('fa fa-list');
		$this->template->title('Pengecekan Finance Penggantian Transport');
		$this->template->render('transport_req_fin_list');
		$this->completeAudit('Membuka daftar pengecekan finance penggantian transport', 1, array(
			'page' => 'transport_req_fin_list',
			'outcome' => 'rendered'
		));
	}
	// list pengajuan transport
	public function transport_req_all()
	{
		$this->beginAudit(__FUNCTION__, array('page' => 'transport_req_all'), array('page', 'item_count', 'outcome'));
		$status = array("0" => "Baru", "1" => "Disetujui", "2" => "Selesai", "3" => "Selesai", "9" => "Ditolak");
		$data = $this->Expense_model->GetListDataTransportRequest();
		$this->auditObserve(null);
		$data_detail = $this->Expense_model->GetListDataTransportRequestAll();
		$this->auditObserve(null);
		$this->template->set('results', $data);
		$this->template->set('data_detail', $data_detail);
		$this->template->set('status', $status);
		$this->template->page_icon('fa fa-list');
		$this->template->title('Pengajuan  Transport');
		$this->template->render('transport_req_all');

		$auditStatus = $this->auditQueriesOk ? 1 : 0;
		$this->completeAudit('Membuka daftar seluruh pengajuan transport', $auditStatus, array(
			'page' => 'transport_req_all',
			'item_count' => is_array($data) ? count($data) : 0,
			'outcome' => $this->auditQueriesOk ? 'rendered' : 'query_error'
		));
	}

	// list pengajuan transport
	public function transport_req()
	{
		$this->beginAudit(__FUNCTION__, array('page' => 'transport_req_list'), array('page', 'outcome'));
		$this->template->page_icon('fa fa-list');
		$this->template->title('Pengajuan Penggantian Transport');
		$this->template->render('transport_req_list');
		$this->completeAudit('Membuka daftar pengajuan penggantian transport', 1, array(
			'page' => 'transport_req_list',
			'outcome' => 'rendered'
		));
	}
	// transport pengajuan create
	public function transport_req_create()
	{
		$this->beginAudit(__FUNCTION__, array('form' => 'transport_req_form'), array('form', 'item_count', 'outcome'));
		$list_coa = $this->Expense_model->getCoaTransport();
		$this->auditObserve(null);
		$this->template->set('list_coa', $list_coa);
		$this->template->set('mod', '');
		$this->template->render('transport_req_form');

		$auditStatus = $this->auditQueriesOk ? 1 : 0;
		$this->completeAudit('Membuka form pengajuan transport baru', $auditStatus, array(
			'form' => 'transport_req_form',
			'item_count' => is_array($list_coa) ? count($list_coa) : 0,
			'outcome' => $this->auditQueriesOk ? 'rendered' : 'query_error'
		));
	}

	// transport req save
	public function transport_req_save()
	{
		$this->beginAudit(__FUNCTION__, array(
			'id' => $this->input->post('id'),
			'no_doc' => $this->input->post('no_doc'),
			'departement' => $this->input->post('departement')
		), array('id', 'no_doc', 'departement', 'amount', 'outcome'));

		$id             = $this->input->post("id");
		$tgl_doc  		= $this->input->post("tgl_doc");
		$no_doc		    = $this->input->post("no_doc");
		$departement	= $this->input->post("departement");
		$nama			= $this->input->post("nama");
		$date1  		= $this->input->post("date1");
		$date2  		= $this->input->post("date2");
		$id_transport	= $this->input->post("id_transport");
		$jumlah_expense	= $this->input->post("jumlah_expense");
		$bank_id		= $this->input->post("bank_id");
		$accnumber		= $this->input->post("accnumber");
		$accname		= $this->input->post("accname");

		$bensin_arr		= $this->input->post("bensin");
		$tol_arr		= $this->input->post("tol");
		$parkir_arr		= $this->input->post("parkir");
		$lainnya_arr	= $this->input->post("lainnya");
		$km_awal_arr	= $this->input->post("km_awal");
		$km_akhir_arr	= $this->input->post("km_akhir");
		$coa_arr		= $this->input->post("coa");
		$nm_coa_arr		= $this->input->post("nm_coa");
		$keperluan_arr	= $this->input->post("keperluan");
		$rute_arr		= $this->input->post("rute");
		$nopol_arr		= $this->input->post("nopol");
		$tgl_doc_detail_arr = $this->input->post("tgl_doc_detail");

		// Clean numeric values (remove thousand separators)
		if (!empty($bensin_arr)) {
			$bensin_arr = array_map(function ($v) {
				return str_replace(['.', ','], '', $v);
			}, $bensin_arr);
		}
		if (!empty($tol_arr)) {
			$tol_arr = array_map(function ($v) {
				return str_replace(['.', ','], '', $v);
			}, $tol_arr);
		}
		if (!empty($parkir_arr)) {
			$parkir_arr = array_map(function ($v) {
				return str_replace(['.', ','], '', $v);
			}, $parkir_arr);
		}
		if (!empty($lainnya_arr)) {
			$lainnya_arr = array_map(function ($v) {
				return str_replace(['.', ','], '', $v);
			}, $lainnya_arr);
		}
		if (!empty($km_awal_arr)) {
			$km_awal_arr = array_map(function ($v) {
				return str_replace(['.', ','], '', $v);
			}, $km_awal_arr);
		}
		if (!empty($km_akhir_arr)) {
			$km_akhir_arr = array_map(function ($v) {
				return str_replace(['.', ','], '', $v);
			}, $km_akhir_arr);
		}
		// Clean jumlah_expense
		$jumlah_expense = str_replace(['.', ','], '', $jumlah_expense);

		$this->db->trans_begin();

		// Configure upload library
		$config['upload_path'] = 'assets/expense/';
		$config['allowed_types'] = 'jpg|jpeg|png|pdf';
		$config['remove_spaces'] = TRUE;
		$config['encrypt_name'] = TRUE;
		$this->load->library('upload', $config);
		$this->upload->initialize($config);

		if ($id != "") {
			$data = array(
				'tgl_doc' => $tgl_doc,
				'departement' => $departement,
				'nama' => $nama,
				'date1' => $date1,
				'date2' => $date2,
				'bank_id' => $bank_id,
				'accnumber' => $accnumber,
				'status' => 0,
				'st_reject' => '',
				'reject_reason_finance' => '',
				'accname' => $accname,
				'jumlah_expense' => ($jumlah_expense),
				'modified_by' => $this->auth->user_name(),
				'modified_on' => date("Y-m-d h:i:s")
			);
			$result = $this->All_model->dataUpdate('tr_transport_req', $data, array('id' => $id));

			// Handle removed details - unlink them from this request
			$removed_transport = $this->input->post("removed_transport");
			if (!empty($removed_transport) && is_array($removed_transport)) {
				foreach ($removed_transport as $removed_id) {
					$this->All_model->dataUpdate('tr_transport', array('no_req' => '', 'status' => '0'), array('id' => intval($removed_id)));
				}
			}

			// Unlink all remaining transport records that are still linked to this doc
			// but NOT in the current id_transport list (handles edge cases)
			$current_linked = $this->db->get_where('tr_transport', array('no_req' => $no_doc))->result();
			if (!empty($current_linked)) {
				foreach ($current_linked as $linked) {
					if (empty($id_transport) || !in_array($linked->id, $id_transport)) {
						$this->All_model->dataUpdate('tr_transport', array('no_req' => '', 'status' => '0'), array('id' => $linked->id));
					}
				}
			}

			// Link the remaining and newly added transport details
			if (!empty($id_transport)) {
				foreach ($id_transport as $keys => $val) {
					$detail_data = array('no_req' => $no_doc, 'status' => '1');
					// Update detail values (bensin, tol, parkir, lainnya) if provided
					if (isset($bensin_arr[$keys])) {
						$detail_data['bensin'] = $bensin_arr[$keys];
					}
					if (isset($tol_arr[$keys])) {
						$detail_data['tol'] = $tol_arr[$keys];
					}
					if (isset($parkir_arr[$keys])) {
						$detail_data['parkir'] = $parkir_arr[$keys];
					}
					if (isset($lainnya_arr[$keys])) {
						$detail_data['lainnya'] = $lainnya_arr[$keys];
					}
					if (isset($km_awal_arr[$keys])) {
						$detail_data['km_awal'] = $km_awal_arr[$keys];
					}
					if (isset($km_akhir_arr[$keys])) {
						$detail_data['km_akhir'] = $km_akhir_arr[$keys];
					}
					if (isset($coa_arr[$keys])) {
						$detail_data['no_coa'] = $coa_arr[$keys];
					}
					if (isset($nm_coa_arr[$keys])) {
						$detail_data['nm_coa'] = $nm_coa_arr[$keys];
					}
					if (isset($keperluan_arr[$keys])) {
						$detail_data['keperluan'] = $keperluan_arr[$keys];
					}
					if (isset($rute_arr[$keys])) {
						$detail_data['rute'] = $rute_arr[$keys];
					}
					if (isset($nopol_arr[$keys])) {
						$detail_data['nopol'] = $nopol_arr[$keys];
					}
					if (isset($tgl_doc_detail_arr[$keys])) {
						$detail_data['tgl_doc'] = date('Y-m-d', strtotime(str_replace('/', '-', $tgl_doc_detail_arr[$keys])));
					}
					// Recalculate jumlah_kasbon
					if (isset($bensin_arr[$keys]) && isset($tol_arr[$keys]) && isset($parkir_arr[$keys]) && isset($lainnya_arr[$keys])) {
						$detail_data['jumlah_kasbon'] = ($bensin_arr[$keys] + $tol_arr[$keys] + $parkir_arr[$keys] + $lainnya_arr[$keys]);
					}

					// Handle file upload
					if (!empty($_FILES['doc_file_' . $val]['name'])) {
						$_FILES['file']['name'] = $_FILES['doc_file_' . $val]['name'];
						$_FILES['file']['type'] = $_FILES['doc_file_' . $val]['type'];
						$_FILES['file']['tmp_name'] = $_FILES['doc_file_' . $val]['tmp_name'];
						$_FILES['file']['error'] = $_FILES['doc_file_' . $val]['error'];
						$_FILES['file']['size'] = $_FILES['doc_file_' . $val]['size'];

						if ($this->upload->do_upload('file')) {
							$uploadData = $this->upload->data();
							$detail_data['doc_file'] = $uploadData['file_name'];
						}
					}

					$result = $this->All_model->dataUpdate('tr_transport', $detail_data, array('id' => $val));
				}
			}
			if ($this->db->trans_status() === FALSE || $this->db->trans_commit() === FALSE) {
				$this->db->trans_rollback();
				$result = 0;
				$error_msg = isset($this->db->error()['message']) ? $this->db->error()['message'] : 'Transaction failed';
			} else {
				$result = 1;
				$error_msg = '';
			}
		} else {
			$no_doc = $this->All_model->GetAutoGenerate('format_transport_req');
			$data =  array(
				'no_doc' => $no_doc,
				'tgl_doc' => $tgl_doc,
				'departement' => $departement,
				'nama' => $nama,
				'date1' => $date1,
				'date2' => $date2,
				'jumlah_expense' => ($jumlah_expense),
				'status' => 0,
				'bank_id' => $bank_id,
				'accnumber' => $accnumber,
				'accname' => $accname,
				'created_by' => $this->auth->user_name(),
				'created_on' => date("Y-m-d h:i:s"),
			);
			$id = $this->All_model->dataSave('tr_transport_req', $data);
			if (!empty($id_transport)) {
				foreach ($id_transport as $keys => $val) {
					$detail_data = array('no_req' => $no_doc, 'status' => '1');
					if (isset($bensin_arr[$keys])) {
						$detail_data['bensin'] = $bensin_arr[$keys];
					}
					if (isset($tol_arr[$keys])) {
						$detail_data['tol'] = $tol_arr[$keys];
					}
					if (isset($parkir_arr[$keys])) {
						$detail_data['parkir'] = $parkir_arr[$keys];
					}
					if (isset($lainnya_arr[$keys])) {
						$detail_data['lainnya'] = $lainnya_arr[$keys];
					}
					if (isset($km_awal_arr[$keys])) {
						$detail_data['km_awal'] = $km_awal_arr[$keys];
					}
					if (isset($km_akhir_arr[$keys])) {
						$detail_data['km_akhir'] = $km_akhir_arr[$keys];
					}
					if (isset($coa_arr[$keys])) {
						$detail_data['no_coa'] = $coa_arr[$keys];
					}
					if (isset($nm_coa_arr[$keys])) {
						$detail_data['nm_coa'] = $nm_coa_arr[$keys];
					}
					if (isset($keperluan_arr[$keys])) {
						$detail_data['keperluan'] = $keperluan_arr[$keys];
					}
					if (isset($rute_arr[$keys])) {
						$detail_data['rute'] = $rute_arr[$keys];
					}
					if (isset($nopol_arr[$keys])) {
						$detail_data['nopol'] = $nopol_arr[$keys];
					}
					if (isset($tgl_doc_detail_arr[$keys])) {
						$detail_data['tgl_doc'] = date('Y-m-d', strtotime(str_replace('/', '-', $tgl_doc_detail_arr[$keys])));
					}
					if (isset($bensin_arr[$keys]) && isset($tol_arr[$keys]) && isset($parkir_arr[$keys]) && isset($lainnya_arr[$keys])) {
						$detail_data['jumlah_kasbon'] = ($bensin_arr[$keys] + $tol_arr[$keys] + $parkir_arr[$keys] + $lainnya_arr[$keys]);
					}

					// Handle file upload
					if (!empty($_FILES['doc_file_' . $val]['name'])) {
						$_FILES['file']['name'] = $_FILES['doc_file_' . $val]['name'];
						$_FILES['file']['type'] = $_FILES['doc_file_' . $val]['type'];
						$_FILES['file']['tmp_name'] = $_FILES['doc_file_' . $val]['tmp_name'];
						$_FILES['file']['error'] = $_FILES['doc_file_' . $val]['error'];
						$_FILES['file']['size'] = $_FILES['doc_file_' . $val]['size'];

						if ($this->upload->do_upload('file')) {
							$uploadData = $this->upload->data();
							$detail_data['doc_file'] = $uploadData['file_name'];
						}
					}

					$result = $this->All_model->dataUpdate('tr_transport', $detail_data, array('id' => $val));
				}
			}
			if (is_numeric($id)) {
				$result         = TRUE;
			} else {
				$result = FALSE;
			}
			if ($this->db->trans_status() === FALSE || $this->db->trans_commit() === FALSE) {
				$this->db->trans_rollback();
				$result = 0;
				$error_msg = isset($this->db->error()['message']) ? $this->db->error()['message'] : 'Transaction failed';
			} else {
				$result = 1;
				$error_msg = '';
			}
		}
		$param = array(
			'save' => $result,
			'id' => $id,
			'msg' => isset($error_msg) ? $error_msg : ''
		);
		$auditStatus = ($result == 1) ? 1 : 0;
		$isUpdate = ($this->input->post("id") != "");
		$this->completeAudit(
			($result == 1) ? ($isUpdate ? 'Berhasil memperbarui pengajuan transport' : 'Berhasil membuat pengajuan transport') : 'Gagal menyimpan pengajuan transport',
			$auditStatus,
			array(
				'id' => $id,
				'no_doc' => $no_doc,
				'departement' => $departement,
				'amount' => $jumlah_expense,
				'outcome' => ($result == 1) ? ($isUpdate ? 'updated' : 'created') : 'transaction_failed'
			)
		);
		echo json_encode($param);
	}

	// transport req edit
	public function transport_req_edit($id, $mod = '')
	{
		$this->beginAudit(__FUNCTION__, array('id' => $id, 'mod' => $mod), array('id', 'no_doc', 'mod', 'outcome'));
		$data = $this->Expense_model->GetDataTransportReq($id);
		$this->auditObserve($data);
		$data_detail = (!empty($data) && !empty($data->no_doc)) ? $this->Expense_model->GetDataTransportInReq($data->no_doc) : array();
		$this->auditObserve(null);

		$list_coa = $this->Expense_model->getCoaTransport();
		$this->auditObserve(null);

		$this->template->set('list_coa', $list_coa);
		$this->template->set('data_detail', $data_detail);
		$this->template->set('status', $this->status);
		$this->template->set('data', $data);
		$this->template->set('mod', $mod);
		$this->template->set('stsview', '');
		$this->template->page_icon('fa fa-list');
		$this->template->title('Pengajuan Transport');
		$this->template->render('transport_req_form');

		$auditStatus = ($data && $this->auditQueriesOk) ? 1 : 0;
		$this->completeAudit('Membuka form edit pengajuan transport', $auditStatus, array(
			'id' => $id,
			'no_doc' => !empty($data->no_doc) ? $data->no_doc : null,
			'mod' => $mod,
			'outcome' => ($data && $this->auditQueriesOk) ? 'rendered' : ($data ? 'query_error' : 'not_found')
		));
	}
	public function transport_req_print($id)
	{
		$this->beginAudit(__FUNCTION__, array('id' => $id), array('id', 'no_doc', 'outcome'));
		$results = $this->Expense_model->GetDataTransportReq($id);
		$this->auditObserve($results);
		$data_detail = (!empty($results) && !empty($results->no_doc)) ? $this->Expense_model->GetDataTransportInReq($results->no_doc) : array();
		$this->auditObserve(null);

		// Resolve department name from HRIS
		$dept_name = '';
		if (!empty($results) && !empty($results->departement)) {
			$dept_name = $this->Expense_model->GetDepartmentName($results->departement);
			$this->auditObserve(null);
		}

		// Resolve request by name from users table
		$request_by = '';
		if (!empty($results) && !empty($results->nama)) {
			$q_u = $this->db->get_where('users', ['username' => $results->nama]);
			$this->auditObserve($q_u);
			$user = $q_u ? $q_u->row() : null;
			$request_by = !empty($user) ? $user->nm_lengkap : $results->nama;
		}

		// Get COA from first detail record
		$coa_display = '';
		if (!empty($data_detail) && isset($data_detail[0])) {
			$coa_display = (isset($data_detail[0]->no_coa) ? $data_detail[0]->no_coa : '') . ' - ' . (isset($data_detail[0]->nm_coa) ? $data_detail[0]->nm_coa : '');
		}

		$data = array(
			'title'			=> 'Print Transportasi Request',
			'stsview'		=> 'print',
			'data_detail'	=> $data_detail,
			'data'			=> $results,
			'dept_name'		=> $dept_name,
			'request_by'	=> $request_by,
			'coa_display'	=> $coa_display,
		);
		$this->load->view('transport_req_print', $data);

		$auditStatus = ($results && $this->auditQueriesOk) ? 1 : 0;
		$this->completeAudit('Mencetak dokumen pengajuan transport', $auditStatus, array(
			'id' => $id,
			'no_doc' => !empty($results->no_doc) ? $results->no_doc : null,
			'outcome' => ($results && $this->auditQueriesOk) ? 'printed' : ($results ? 'query_error' : 'not_found')
		));
	}
	// transport req view
	public function transport_req_view($id, $mod = '')
	{
		$this->beginAudit(__FUNCTION__, array('id' => $id, 'mod' => $mod), array('id', 'no_doc', 'mod', 'outcome'));
		$data = $this->Expense_model->GetDataTransportReq($id);
		$this->auditObserve($data);
		$data_detail = (!empty($data) && !empty($data->no_doc)) ? $this->Expense_model->GetDataTransportInReq($data->no_doc) : array();
		$this->auditObserve(null);

		$list_coa = $this->Expense_model->getCoaTransport();
		$this->auditObserve(null);

		$this->template->set('list_coa', $list_coa);
		$this->template->set('data_detail', $data_detail);
		$this->template->set('status', $this->status);
		$this->template->set('data', $data);
		$this->template->set('mod', $mod);
		$this->template->set('stsview', 'view');
		$this->template->page_icon('fa fa-list');
		$this->template->title('Pengajuan Transport');
		$this->template->render('transport_req_form');

		$auditStatus = ($data && $this->auditQueriesOk) ? 1 : 0;
		$this->completeAudit('Melihat dokumen pengajuan transport', $auditStatus, array(
			'id' => $id,
			'no_doc' => !empty($data->no_doc) ? $data->no_doc : null,
			'mod' => $mod,
			'outcome' => ($data && $this->auditQueriesOk) ? 'rendered' : ($data ? 'query_error' : 'not_found')
		));
	}

	// list transport
	public function transport()
	{
		$this->beginAudit(__FUNCTION__, array('page' => 'transport_list'), array('page', 'outcome'));
		$this->template->page_icon('fa fa-list');
		$this->template->title('Transportasi');
		$this->template->render('transport_list');
		$this->completeAudit('Membuka daftar transportasi', 1, array(
			'page' => 'transport_list',
			'outcome' => 'rendered'
		));
	}

	// transport create
	public function transport_create()
	{
		$this->beginAudit(__FUNCTION__, array('form' => 'transport_form'), array('form', 'item_count', 'outcome'));
		$list_coa_transport = $this->Expense_model->getCoaTransport();
		$this->auditObserve(null);

		$data = [
			'list_coa' => $list_coa_transport
		];
		$this->template->set($data);
		$this->template->render('transport_form');

		$auditStatus = $this->auditQueriesOk ? 1 : 0;
		$this->completeAudit('Membuka form pembuatan transport baru', $auditStatus, array(
			'form' => 'transport_form',
			'item_count' => is_array($list_coa_transport) ? count($list_coa_transport) : 0,
			'outcome' => $this->auditQueriesOk ? 'rendered' : 'query_error'
		));
	}

	// transport save
	public function transport_save()
	{
		$this->beginAudit(__FUNCTION__, array(
			'id' => $this->input->post('id'),
			'no_doc' => $this->input->post('no_doc'),
			'departement' => $this->input->post('departement')
		), array('id', 'no_doc', 'departement', 'amount', 'outcome'));

		$id             = $this->input->post("id");
		$tgl_doc  		= date('Y-m-d', strtotime($this->input->post("tgl_doc")));
		$no_doc		    = $this->input->post("no_doc");
		$departement	= $this->input->post("departement");
		$nama			= $this->input->post("nama");
		$keperluan		= $this->input->post("keperluan");
		$rute			= $this->input->post("rute");
		$nopol			= $this->input->post("nopol");
		$km_awal		= $this->input->post("km_awal");
		if ($km_awal == '') {
			$km_awal = 0;
		}
		$km_akhir		= $this->input->post("km_akhir");
		if ($km_akhir == '') {
			$km_akhir = 0;
		}
		$bensin			= $this->input->post("bensin");
		$tol			= $this->input->post("tol");
		$parkir			= $this->input->post("parkir");
		$filename		= $this->input->post("filename");
		$lainnya		= $this->input->post("lainnya");
		$keterangan		= $this->input->post("keterangan");
		$no_coa = $this->input->post('coa');
		$nm_coa = $this->input->post('nm_coa');

		$valid_photo = 1;
		$msg = '';

		$this->db->trans_begin();
		$config['upload_path'] = 'assets/expense/';
		$config['allowed_types'] = 'jpg|jpeg|png|pdf';
		$config['remove_spaces'] = TRUE;
		$config['encrypt_name'] = TRUE;
		$filenames = $filename;
		if (!empty($_FILES['doc_file']['name'])) {
			$_FILES['file']['name'] = $_FILES['doc_file']['name'];
			$_FILES['file']['type'] = $_FILES['doc_file']['type'];
			$_FILES['file']['tmp_name'] = $_FILES['doc_file']['tmp_name'];
			$_FILES['file']['error'] = $_FILES['doc_file']['error'];
			$_FILES['file']['size'] = $_FILES['doc_file']['size'];
			$this->load->library('upload', $config);
			$this->upload->initialize($config);
			if ($this->upload->do_upload('file')) {
				$uploadData = $this->upload->data();
				$filenames = $uploadData['file_name'];
			} else {
				$valid_photo = 0;
				$msg = $this->upload->display_errors();
			}
		}

		$isUpdate = ($id !== "");
		if ($valid_photo == 1) {
			if ($isUpdate) {
				$result = $this->db->update('tr_transport', [
					'tgl_doc' => $tgl_doc,
					'departement' => $departement,
					'keperluan' => $keperluan,
					'nama' => $nama,
					'rute' => $rute,
					'km_awal' => $km_awal,
					'km_akhir' => $km_akhir,
					'nopol' => $nopol,
					'bensin' => $bensin,
					'tol' => $tol,
					'lainnya' => $lainnya,
					'keterangan' => $keterangan,
					'parkir' => $parkir,
					'jumlah_kasbon' => ($bensin + $tol + $parkir + $lainnya),
					'doc_file' => $filenames,
					'no_coa' => $no_coa,
					'nm_coa' => $nm_coa,
					'modified_by' => $this->auth->user_name(),
					'modified_on' => date("Y-m-d h:i:s")
				], ['id' => $id]);
			} else {
				$no_doc = $this->All_model->GetAutoGenerate('format_transport');
				$data =  array(
					'no_doc' => $no_doc,
					'tgl_doc' => $tgl_doc,
					'departement' => $departement,
					'keperluan' => $keperluan,
					'nama' => $nama,
					'rute' => $rute,
					'km_awal' => $km_awal,
					'km_akhir' => $km_akhir,
					'nopol' => $nopol,
					'bensin' => $bensin,
					'tol' => $tol,
					'parkir' => $parkir,
					'lainnya' => $lainnya,
					'keterangan' => $keterangan,
					'jumlah_kasbon' => ($bensin + $tol + $parkir + $lainnya),
					'doc_file' => $filenames,
					'status' => 0,
					'no_coa' => $no_coa,
					'nm_coa' => $nm_coa,
					'created_by' => $this->auth->user_name(),
					'created_on' => date("Y-m-d h:i:s"),
				);
				$id = $this->All_model->dataSave('tr_transport', $data);
				$result = ($id !== false);
			}

			if ($this->db->trans_status() === FALSE || $this->db->trans_commit() === FALSE) {
				$this->db->trans_rollback();
				$result = FALSE;
				$msg = 'Data gagal di simpan !';
			} else {
				$result = TRUE;
				$msg = 'Data berhasil di simpan !';
			}
		} else {
			$this->db->trans_rollback();
			$result = FALSE;
		}

		$param = array(
			'save' => $result,
			'id' => $id,
			'msg' => $msg
		);

		if ($valid_photo == 0) {
			$auditOutcome = 'upload_failed';
			$auditStatus = 0;
		} elseif ($result) {
			$auditOutcome = ($isUpdate ? 'updated' : 'created');
			$auditStatus = 1;
		} else {
			$auditOutcome = 'transaction_failed';
			$auditStatus = 0;
		}

		$this->completeAudit(
			($result ? ($isUpdate ? 'Berhasil memperbarui data transportasi' : 'Berhasil membuat data transportasi') : ($valid_photo == 0 ? 'Gagal mengunggah berkas transportasi' : 'Gagal menyimpan data transportasi')),
			$auditStatus,
			array(
				'id' => $id,
				'no_doc' => $no_doc,
				'departement' => $departement,
				'amount' => ($bensin + $tol + $parkir + $lainnya),
				'outcome' => $auditOutcome
			)
		);

		echo json_encode($param);
	}

	public function get_list_req_transport($nama, $departement, $date1, $date2)
	{
		$this->beginAudit(__FUNCTION__, array(
			'departement' => $departement,
			'date1' => $date1,
			'date2' => $date2
		), array('departement', 'date1', 'date2', 'item_count', 'outcome'));

		$existing_ids = $this->input->post('existing_ids');
		$no_doc = $this->input->post('no_doc');

		$where = "nama='" . $this->db->escape_str($nama) . "' AND tgl_doc BETWEEN '" . $this->db->escape_str($date1) . "' AND '" . $this->db->escape_str($date2) . "' AND (no_req = '' OR no_req IS NULL";
		// Also include items linked to current document (so they can be re-selected)
		if (!empty($no_doc)) {
			$where .= " OR no_req = '" . $this->db->escape_str($no_doc) . "'";
		}
		$where .= ")";

		// Exclude IDs already displayed in the form
		if (!empty($existing_ids) && is_array($existing_ids)) {
			$escaped_ids = array_map(function ($id) {
				return intval($id);
			}, $existing_ids);
			$where .= " AND id NOT IN (" . implode(',', $escaped_ids) . ")";
		}

		$query = $this->db->query("SELECT * FROM tr_transport WHERE " . $where . " ORDER BY tgl_doc");
		$this->auditObserve($query !== false);
		$data = ($query !== false) ? $query->result() : array();

		$auditStatus = $this->auditQueriesOk ? 1 : 0;
		$this->completeAudit('Mengambil daftar permohonan transport untuk form', $auditStatus, array(
			'departement' => $departement,
			'date1' => $date1,
			'date2' => $date2,
			'item_count' => is_array($data) ? count($data) : 0,
			'outcome' => $this->auditQueriesOk ? 'fetched' : 'query_error'
		));

		echo json_encode($data);
	}

	public function get_transport($nama, $departement)
	{
		$this->beginAudit(__FUNCTION__, array(
			'departement' => $departement
		), array('departement', 'item_count', 'outcome'));

		$data = $this->All_model->GetOneTable('tr_transport', array('nama' => $nama, 'departement' => $departement, 'status' => '1'), 'tgl_doc');
		$this->auditObserve(null);

		$auditStatus = $this->auditQueriesOk ? 1 : 0;
		$this->completeAudit('Mengambil data transportasi aktif', $auditStatus, array(
			'departement' => $departement,
			'item_count' => is_array($data) ? count($data) : 0,
			'outcome' => $this->auditQueriesOk ? 'fetched' : 'query_error'
		));

		echo json_encode($data);
	}

	// transport edit
	public function transport_edit($id)
	{
		$this->beginAudit(__FUNCTION__, array('id' => $id), array('id', 'no_doc', 'outcome'));
		$data = $this->Expense_model->GetDataTransport($id);
		$this->auditObserve($data);
		$get_list_coa = $this->Expense_model->getCoaTransport();
		$this->auditObserve(null);

		$this->template->set('status', $this->status);
		$this->template->set('data', $data);
		$this->template->set('list_coa', $get_list_coa);
		$this->template->set('stsview', '');
		$this->template->page_icon('fa fa-list');
		$this->template->render('transport_form');

		$auditStatus = ($data && $this->auditQueriesOk) ? 1 : 0;
		$this->completeAudit('Membuka form edit transportasi', $auditStatus, array(
			'id' => $id,
			'no_doc' => !empty($data->no_doc) ? $data->no_doc : null,
			'outcome' => ($data && $this->auditQueriesOk) ? 'rendered' : ($data ? 'query_error' : 'not_found')
		));
	}

	// transport view
	public function transport_view($id)
	{
		$this->beginAudit(__FUNCTION__, array('id' => $id), array('id', 'no_doc', 'outcome'));
		$data = $this->Expense_model->GetDataTransport($id);
		$this->auditObserve($data);
		$get_list_coa = $this->Expense_model->getCoaTransport();
		$this->auditObserve(null);

		$this->template->set('status', $this->status);
		$this->template->set('data', $data);
		$this->template->set('list_coa', $get_list_coa);
		$this->template->set('stsview', 'view');
		$this->template->page_icon('fa fa-list');
		$this->template->render('transport_form');

		$auditStatus = ($data && $this->auditQueriesOk) ? 1 : 0;
		$this->completeAudit('Melihat detail transportasi', $auditStatus, array(
			'id' => $id,
			'no_doc' => !empty($data->no_doc) ? $data->no_doc : null,
			'outcome' => ($data && $this->auditQueriesOk) ? 'rendered' : ($data ? 'query_error' : 'not_found')
		));
	}

	// transport fin approve
	public function transport_req_approve($id = '', $status = null)
	{
		$this->beginAudit(__FUNCTION__, array('id' => $id, 'status' => $status), array('id', 'status', 'stage', 'outcome'));
		$stage = ($status == 1) ? 'finance' : (($status == 2) ? 'management' : 'unknown');

		$result = false;
		if ($id != "") {
			$data = array(
				'id' => $id,
				'status' => $status,
				'st_reject' => '',
			);
			if ($status == 1) {
				$data['fin_check_by'] = $this->auth->user_name();
				$data['fin_check_on'] = date("Y-m-d h:i:s");
				$data['approved_by'] = $this->auth->user_name();
				$data['approved_on'] = date("Y-m-d h:i:s");
			}
			if ($status == 2) {
				$data['management_by'] = $this->auth->user_name();
				$data['management_on'] = date("Y-m-d h:i:s");
			}
			$result = $this->All_model->dataUpdate('tr_transport_req', $data, array('id' => $id));
			$keterangan     = "SUKSES, Update data " . $id;
			$logStatus      = 1;
			$nm_hak_akses   = $this->managePermission;
			$kode_universal = $id;
			$jumlah = 1;
			$sql            = $this->db->last_query();
			simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $logStatus);
		} else {
			$result = false;
			$id = 0;
		}

		$param = array(
			'save' => $result,
			'id' => $id
		);

		$auditStatus = ($result) ? 1 : 0;
		$this->completeAudit(
			$result ? 'Berhasil menyetujui pengajuan transport' : ($id == 0 ? 'ID pengajuan transport kosong' : 'Gagal menyetujui pengajuan transport'),
			$auditStatus,
			array(
				'id' => $id,
				'status' => $status,
				'stage' => $stage,
				'outcome' => $result ? 'approved' : ($id == 0 ? 'empty_id' : 'update_failed')
			)
		);

		echo json_encode($param);
	}

	// transport approve
	public function transport_approve($id = '')
	{
		$this->beginAudit(__FUNCTION__, array('id' => $id), array('id', 'status', 'outcome'));

		$result = false;
		if ($id != "") {
			$data = array(
				'id' => $id,
				'status' => 1,
			);
			$result = $this->All_model->dataUpdate('tr_transport', $data, array('id' => $id));
			$keterangan     = "SUKSES, Update data " . $id;
			$logStatus      = 1;
			$nm_hak_akses   = $this->managePermission;
			$kode_universal = $id;
			$jumlah = 1;
			$sql            = $this->db->last_query();
			simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $logStatus);
		} else {
			$id = 0;
		}

		$param = array(
			'save' => $result,
			'id' => $id
		);

		$auditStatus = ($result) ? 1 : 0;
		$this->completeAudit(
			$result ? 'Berhasil menyetujui transportasi' : ($id == 0 ? 'ID transportasi kosong' : 'Gagal menyetujui transportasi'),
			$auditStatus,
			array(
				'id' => $id,
				'status' => 1,
				'outcome' => $result ? 'approved' : ($id == 0 ? 'empty_id' : 'update_failed')
			)
		);

		echo json_encode($param);
	}

	// transport delete
	public function transport_delete($id)
	{
		$this->beginAudit(__FUNCTION__, array('id' => $id), array('id', 'outcome'));
		$this->db->trans_begin();
		$result = $this->All_model->dataDelete('tr_transport', array('id' => $id));
		if ($this->db->trans_status() === FALSE || $this->db->trans_commit() === FALSE) {
			$this->db->trans_rollback();
			$result = false;
			$auditOutcome = 'delete_failed';
			$auditStatus = 0;
		} else {
			$auditOutcome = $result ? 'deleted' : 'delete_failed';
			$auditStatus = $result ? 1 : 0;
		}

		$param = array('delete' => $result);

		$this->completeAudit(
			$result ? 'Berhasil menghapus data transportasi' : 'Gagal menghapus data transportasi',
			$auditStatus,
			array(
				'id' => $id,
				'outcome' => $auditOutcome
			)
		);

		echo json_encode($param);
	}

	// transport delete
	public function transport_req_delete($id)
	{
		$this->beginAudit(__FUNCTION__, array('id' => $id), array('id', 'no_doc', 'outcome'));
		$this->db->trans_begin();
		$data = $this->Expense_model->GetDataTransportReq($id);
		$this->auditObserve($data);

		if ($data) {
			$this->All_model->dataUpdate('tr_transport', array('status' => 0, 'no_req' => ''), array('no_req' => $data->no_doc));
			$result = $this->All_model->dataDelete('tr_transport_req', array('id' => $id));
		} else {
			$result = false;
		}

		if (!$data) {
			$this->db->trans_rollback();
			$auditOutcome = 'not_found';
			$auditStatus = 0;
		} elseif ($this->db->trans_status() === FALSE || $this->db->trans_commit() === FALSE) {
			$this->db->trans_rollback();
			$result = false;
			$auditOutcome = 'delete_failed';
			$auditStatus = 0;
		} else {
			$auditOutcome = $result ? 'deleted' : 'delete_failed';
			$auditStatus = $result ? 1 : 0;
		}

		$param = array('delete' => $result);

		$this->completeAudit(
			($result ? 'Berhasil menghapus pengajuan transport' : ($data ? 'Gagal menghapus pengajuan transport' : 'Pengajuan transport tidak ditemukan')),
			$auditStatus,
			array(
				'id' => $id,
				'no_doc' => !empty($data->no_doc) ? $data->no_doc : null,
				'outcome' => $auditOutcome
			)
		);

		echo json_encode($param);
	}

	// list petty_cash
	public function petty_cash()
	{
		$this->beginAudit(__FUNCTION__, array('page' => 'index_pc'), array('page', 'outcome'));
		$this->template->set('status', $this->status);
		$this->template->page_icon('fa fa-list');
		$this->template->title('Petty Cash');
		$this->template->render('index_pc');
		$this->completeAudit('Membuka daftar petty cash', 1, array(
			'page' => 'index_pc',
			'outcome' => 'rendered'
		));
	}

	// create petty_cash
	public function create_pc()
	{
		$this->beginAudit(__FUNCTION__, array('page' => 'form_pc'), array('page', 'outcome'));

		$data_budget = $this->All_model->GetComboBudget('', 'EXPENSE', date('Y'));
		$data_pc = $this->All_model->GetOneTable('ms_petty_cash', '', 'nama');
		$data_kasbon_pr_pet = $this->db->get_where('tr_kasbon', ['metode_pembayaran' => 2, 'id_expense_pett_pr_non_po' => null])->result();

		$data_penggantian_kasbon = $this->db->query("
			SELECT
				a.*
			FROM
				tr_expense a
			WHERE
				a.status = '1' AND
				a.jumlah <> 0 AND
				(a.tipe_penggantian = 1 OR a.tipe_pengembalian = 1) AND
				a.pettycash IS NULL AND
				a.expense_id_kembalian IS NULL AND
				(SELECT COUNT(aa.id) FROM tr_expense_detail aa WHERE aa.id_expense_bayar_sisa = a.no_doc) < 1
			ORDER BY a.id ASC
		")->result();

		$data_detail	= $this->Expense_model->GetDataDetail($this->auth->user_id());

		$this->template->set('data_pc', $data_pc);
		$this->template->set('data_budget', $data_budget);
		$this->template->set('data_kasbon_pr_pet', $data_kasbon_pr_pet);
		$this->template->set('data_penggantian_kasbon', $data_penggantian_kasbon);
		$this->template->set('data_detail', $data_detail);
		$this->template->render('form_pc');

		$this->completeAudit('Membuka form pembuatan petty cash', 1, array(
			'page' => 'form_pc',
			'outcome' => 'rendered'
		));
	}

	// edit petty_cash
	public function edit_pc($id)
	{
		$this->beginAudit(__FUNCTION__, array('id' => $id), array('id', 'no_doc', 'outcome'));

		$data = $this->Expense_model->GetDataHeader($id);
		$this->auditObserve($data);
		$no_doc = is_object($data) ? ($data->no_doc ?? null) : null;

		$data_detail	= $this->Expense_model->GetDataDetail($no_doc);
		$data_budget = $this->All_model->GetPettyCashComboCoa(is_object($data) ? ($data->pettycash ?? '') : '');
		$data_pc = $this->All_model->GetOneTable('ms_petty_cash', '', 'nama');
		$data_kasbon_pr_pet = $this->db->get_where('tr_kasbon', ['metode_pembayaran' => 2, 'id_expense_pett_pr_non_po' => null])->result();

		$data_penggantian_kasbon = $this->db->query("
			SELECT
				a.*
			FROM
				tr_expense a
			WHERE
				a.status = '1' AND
				a.jumlah <> 0 AND
				(a.tipe_penggantian = 1 OR a.tipe_pengembalian = 1) AND
				a.pettycash IS NULL AND
				a.expense_id_kembalian IS NULL
			ORDER BY a.id ASC
		")->result();

		$this->template->set('data_pc', $data_pc);
		$this->template->set('data_budget', $data_budget);
		$this->template->set('data_detail', $data_detail);
		$this->template->set('status', $this->status);
		$this->template->set('data', $data);
		$this->template->set('stsview', '');
		$this->template->set('data_kasbon_pr_pet', $data_kasbon_pr_pet);
		$this->template->set('data_penggantian_kasbon', $data_penggantian_kasbon);
		$this->template->page_icon('fa fa-list');
		$this->template->render('form_pc');

		$this->completeAudit(
			$data ? 'Membuka form edit petty cash' : 'Data petty cash tidak ditemukan untuk diedit',
			1,
			array(
				'id' => $id,
				'no_doc' => $no_doc,
				'outcome' => $data ? 'rendered' : 'not_found'
			)
		);
	}

	// view petty_cash
	public function view_pc($id)
	{
		$this->beginAudit(__FUNCTION__, array('id' => $id), array('id', 'no_doc', 'outcome'));

		$data = $this->Expense_model->GetDataHeader($id);
		$this->auditObserve($data);
		$no_doc = is_object($data) ? ($data->no_doc ?? null) : null;

		$data_detail	= $this->Expense_model->GetDataDetail($no_doc);
		$data_budget = $this->All_model->GetComboBudget('', 'EXPENSE', date('Y'));
		$data_pc = $this->All_model->GetOneTable('ms_petty_cash', '', 'nama');
		$this->template->set('data_pc', $data_pc);
		$this->template->set('data_budget', $data_budget);
		$this->template->set('data_detail', $data_detail);
		$this->template->set('status', $this->status);
		$this->template->set('data', $data);
		$this->template->set('stsview', 'view');
		$this->template->page_icon('fa fa-list');
		$this->template->render('form_pc');

		$this->completeAudit(
			$data ? 'Membuka view detail petty cash' : 'Data petty cash tidak ditemukan',
			1,
			array(
				'id' => $id,
				'no_doc' => $no_doc,
				'outcome' => $data ? 'rendered' : 'not_found'
			)
		);
	}
	function getcoabudget()
	{
		$coa = $this->input->post("coa");
		$this->beginAudit(__FUNCTION__, array('coa' => $coa), array('coa', 'count', 'outcome'));

		$coabudget = str_ireplace(";", "','", $coa);
		$datacombocoa = "";
		$data_budget = $this->db->query("select * from " . DBACC . ".coa_master where no_perkiraan in ('" . $coabudget . "')")->result();
		foreach ($data_budget as $keys) {
			$datacombocoa .= "<option value='" . $keys->no_perkiraan . "'>" . $keys->no_perkiraan . " - " . $keys->nama . "</option>";
		}

		$this->completeAudit('Mengambil combo COA budget', 1, array(
			'coa' => $coa,
			'count' => count($data_budget),
			'outcome' => 'success'
		));

		echo $datacombocoa;
	}
	public function reject()
	{
		// Gunakan output JSON header agar client tahu ini respon JSON
		header('Content-Type: application/json');

		$id     = $this->input->post("id");
		$reason = $this->input->post("reason");
		$table  = $this->input->post("table");

		$this->beginAudit(__FUNCTION__, array('id' => $id, 'table' => $table, 'reason' => $reason), array('id', 'table', 'reason', 'outcome'));

		$result = false;
		$auditOutcome = 'failed';
		$auditStatus = 0;

		$previous_db_debug = isset($this->db->db_debug) ? $this->db->db_debug : true;
		$this->db->trans_begin();

		try {
			if (empty($id) || empty($table)) {
				$auditOutcome = 'empty_id';
				throw new Exception('ID atau Tabel tidak ditemukan!');
			}

			$allowed_tables = array('tr_transport_req', 'tr_expense', 'tr_kasbon');
			if (!in_array($table, $allowed_tables, true)) {
				$auditOutcome = 'invalid_table';
				throw new Exception('Tabel tidak diizinkan untuk operasi reject!');
			}

			$data = array(
				'status'                => 9,
				'sts_finance'           => '0',
				'app_finance_date'      => null,
				'reject_reason_finance' => $reason,
				'reject_reason'         => $reason,
				'st_reject'             => $reason
			);

			if (method_exists($this->db, 'field_exists')) {
				if ($this->db->field_exists('rejected_by', $table)) {
					$data['rejected_by'] = $this->auth->user_name();
				}
				if ($this->db->field_exists('rejected_on', $table)) {
					$data['rejected_on'] = date('Y-m-d H:i:s');
				}
			} else {
				$data['rejected_by'] = $this->auth->user_name();
				$data['rejected_on'] = date('Y-m-d H:i:s');
			}

			$this->db->db_debug = false;
			$this->db->where('id', $id);
			$update = $this->db->update($table, $data);

			if (!$update) {
				$auditOutcome = 'update_failed';
				throw new Exception(isset($this->db->error()['message']) ? $this->db->error()['message'] : 'Gagal update database');
			}

			// Jika semua oke, commit transaksinya
			if ($this->db->trans_status() === FALSE || $this->db->trans_commit() === FALSE) {
				$this->db->trans_rollback();
				$auditOutcome = 'transaction_failed';
				throw new Exception('Transaction Failed!');
			} else {
				$result = true;
				$message = "Berhasil mereject data.";
				$auditOutcome = 'rejected';
				$auditStatus = 1;
			}
		} catch (Exception $e) {
			$this->db->trans_rollback();
			$result = false;
			$message = $e->getMessage();
			http_response_code(400); // Bad Request atau 500
		} finally {
			if (isset($this->db)) {
				$this->db->db_debug = $previous_db_debug;
			}
		}

		$this->completeAudit(
			$result ? 'Berhasil menolak data' : 'Gagal menolak data: ' . ($message ?? ''),
			$auditStatus,
			array(
				'id' => $id,
				'table' => $table,
				'reason' => $reason,
				'outcome' => $auditOutcome
			)
		);

		$param = array(
			'save'    => $result,
			'id'      => $id,
			'message' => $message ?? ''
		);

		echo json_encode($param);
	}

	public function reject_manage()
	{
		header('Content-Type: application/json');

		$result = false;
		$id		= $this->input->post("id");
		$reason	= $this->input->post("reason");
		$table	= $this->input->post("table");

		$this->beginAudit(__FUNCTION__, array('id' => $id, 'table' => $table, 'reason' => $reason), array('id', 'table', 'reason', 'outcome'));

		$auditOutcome = 'failed';
		$auditStatus = 0;

		if ($id != "" && !empty($table)) {
			$this->db->trans_begin();
			$data = array(
				'status' => 9,
				'sts_finance' => '0',
				'app_finance_date' => null,
				'reject_reason_finance' => $reason,
				'sts_reject' => '1',
				'sts_reject_manage' => '1',
				'reject_reason' => $reason,
				'st_reject' => $reason,
				'rejected_by' => $this->auth->user_name(),
				'rejected_on' => date('Y-m-d H:i:s')
			);
			$updateResult = $this->All_model->dataUpdate($table, $data, array('id' => $id));
			if ($updateResult && $this->db->trans_status() !== FALSE && $this->db->trans_commit() !== FALSE) {
				$result = true;
				$keterangan     = "SUKSES, Reject data " . $id;
				$status         = 1;
				$nm_hak_akses   = $this->managePermission;
				$kode_universal = $id;
				$jumlah = 1;
				$sql            = $this->db->last_query();
				simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
				$auditOutcome = 'rejected';
				$auditStatus = 1;
			} else {
				$this->db->trans_rollback();
				$result = false;
				$auditOutcome = 'update_failed';
			}
		} else {
			$auditOutcome = empty($id) ? 'empty_id' : 'empty_table';
		}

		$this->completeAudit(
			$result ? 'Berhasil menolak data manajemen' : 'Gagal menolak data manajemen',
			$auditStatus,
			array(
				'id' => $id,
				'table' => $table,
				'reason' => $reason,
				'outcome' => $auditOutcome
			)
		);

		$param = array(
			'save' => $result,
			'id' => $id
		);
		echo json_encode($param);
	}

	public function review($id)
	{
		$this->beginAudit(__FUNCTION__, array('id' => $id), array('id', 'no_doc', 'outcome'));

		// $controller			= ucfirst(strtolower($this->uri->segment(1)));
		// $Arr_Akses			= getAcccesmenu($controller);
		$dataHeader = $this->Expense_model->GetDataHeader($id);
		$this->auditObserve($dataHeader);
		$no_doc = is_object($dataHeader) ? ($dataHeader->no_doc ?? null) : null;

		$data_detail	= $this->Expense_model->GetDataDetail($no_doc);
		// $data_departement = $this->All_model->GetDeptCombo();
		$data_budget = $this->All_model->GetCoaCombo('5');
		$data_user = $this->All_model->GetUserCombo();
		$combodept	= $this->Expense_model->getArray('department', array(), 'id', 'nm_dept');
		// $data_coa = $this->All_model->GetCoaCombo('5', "a.no_perkiraan like '1101%'");

		$this->db->select('a.no_perkiraan, a.nama');
		$this->db->from(DBACC . '.coa_master a');
		$this->db->like('a.nama', 'bank');
		$data_coa = $this->db->get()->result();

		$data_departement = $this->db->get('ms_department')->result();

		// $so_number = $this->db->select('a.so_number, b.project')
		// 	->from('so_number a')
		// 	->join('production b', "REPLACE(a.id_bq,'BQ-','') = b.no_ipp", 'left')
		// 	->where('a.id_bq <>', 'x')
		// 	->get()
		// 	->result_array();
		// $combo_coa_pph = $this->All_model->Getcomboparamcoa('pph_pembelian');
		$data = array(
			'title'			=> 'View Expense Report',
			'action'		=> 'index',
			'data_user'	    => $data_user,
			'data_coa'		=> $data_coa,
			'data_budget'	=> $data_budget,
			'data_departement'	=> $data_departement,
			'data_detail'	=> $data_detail,
			'data'	    	=> $dataHeader,
			'stsview'	    => 'review',
			'combodept'		=> $combodept,
			'status'	    => $this->status,
			// 'combo_so'		=> $so_number,
			// 'combo_coa_pph'	=> $combo_coa_pph,
			// 'akses_menu'	=> $Arr_Akses
		);
		$this->template->set($data);
		$this->template->page_icon('fa fa-list');
		$this->template->render('expense_review');

		$this->completeAudit(
			$dataHeader ? 'Membuka form review expense' : 'Data expense tidak ditemukan untuk di-review',
			1,
			array(
				'id' => $id,
				'no_doc' => $no_doc,
				'outcome' => $dataHeader ? 'rendered' : 'not_found'
			)
		);
		// $this->template('expense_review', $data);
	}

	public function return_confirm($id = '')
	{
		$this->beginAudit(__FUNCTION__, array('id' => $id), array('id', 'no_doc', 'valid', 'outcome'));

		$result = false;
		$valid  = 0;
		$auditOutcome = 'failed';
		$auditStatus = 0;
		$no_doc = null;

		$data_session	= $this->session->userdata;
		$dateTime = date('Y-m-d H:i:s');
		$UserName = isset($data_session['app_session']['username']) ? $data_session['app_session']['username'] : '';

		if ($id != "") {
			$transfer_coa_bank	= $this->input->post("transfer_coa_bank");
			$transfer_tanggal	= $this->input->post("transfer_tanggal");
			$transfer_jumlah	= $this->input->post("transfer_jumlah");
			//			$transferfile		= $this->input->post("transferfile");

			$get_expense = $this->db->get_where('tr_expense', ['id' => $id])->row();
			$this->auditObserve($get_expense);
			$no_doc = $get_expense ? ($get_expense->no_doc ?? null) : null;
			$get_pengembalian_expense = $get_expense ? $this->db->get_where('tr_pengembalian_expense', ['no_doc' => $get_expense->no_doc])->result() : [];

			$nilai_on_kembali = 0;
			foreach ($get_pengembalian_expense as $item) {
				$nilai_on_kembali += $item->transfer_jumlah;
			}

			if ($get_expense && $get_expense->jumlah < 0) {
				$sisa_expense = ($get_expense->jumlah * -1);
			} else {
				$sisa_expense = $get_expense ? $get_expense->jumlah : 0;
			}

			$total_nilai = ($transfer_jumlah + $nilai_on_kembali);
			$valid = 1;
			if ($total_nilai > $sisa_expense) {
				$valid = 2;
				$auditOutcome = 'over_limit';
			}

			if ($valid == 1 && $get_expense) {
				// $data = array(
				// 	'status' => 3,
				// 	'transfer_coa_bank' => $transfer_coa_bank,
				// 	'transfer_tanggal' => $transfer_tanggal,
				// 	'transfer_jumlah' => $transfer_jumlah,
				// 	'st_reject' => ''
				// );
				$this->db->trans_begin();
				// $results = $this->db->update('tr_expense', $data, ['id' => $id]);
				$insert_pengembalian = $this->db->insert('tr_pengembalian_expense', [
					'no_doc' => $get_expense->no_doc,
					'transfer_coa_bank' => $transfer_coa_bank,
					'transfer_tanggal' => $transfer_tanggal,
					'transfer_jumlah' => $transfer_jumlah,
					'created_by' => $this->auth->user_id(),
					'created_date' => date('Y-m-d H:i:s')
				]);
				// $recpc = $this->All_model->GetOneData('tr_expense', array('id' => $id, 'status' => '3'));
				// $exjumlah = $recpc->jumlah;
				// if ($exjumlah == 0) {
				// 	$Nomor_JV = $this->Jurnal_model->get_Nomor_Jurnal_Sales('101');
				// 	$jenis_jurnal = "JV";
				// 	$payment_date = date("Y-m-d");
				// } else {
				// 	$Nomor_JV = $this->Jurnal_model->get_Nomor_Jurnal_BUM('101');
				// 	$jenis_jurnal = "BUM";
				// 	$payment_date = $recpc->transfer_tanggal;
				// }
				// $det_Jurnaltes1 = array();
				// $ix = 0;
				// $ketpetty = '';
				// $ketpetty = $recpc->pettycash . ' ';
				// $this->db->update('tr_expense_detail', ['status' => '2'], ['no_doc' => $recpc->no_doc, 'status' => '1']);
				// $Bln 			= substr($payment_date, 5, 2);
				// $Thn 			= substr($payment_date, 0, 4);

				// $session = $this->session->userdata('app_session');
				// $rec = $this->db->query("select * from tr_expense_detail where no_doc='" . $recpc->no_doc . "'")->result();
				// $total = 0;
				// $nomor_jurnal = $jenis_jurnal . date("ymd") . rand(1000, 9999) . $ix;
				// foreach ($rec as $record) {
				// 	if ($record->id_kasbon != '') {
				// 		$det_Jurnaltes1[] = array(
				// 			'nomor' => $nomor_jurnal, 'tanggal' => $payment_date, 'tipe' => 'BUM', 'no_perkiraan' => $record->coa, 'keterangan' => $ketpetty . $record->deskripsi, 'no_request' => $recpc->no_doc, 'debet' => 0, 'kredit' => $record->kasbon, 'no_reff' =>  $recpc->no_doc, 'jenis_jurnal' => $jenis_jurnal, 'nocust' => $recpc->nama, 'stspos' => '1'
				// 		);
				// 		$datadetail = array(
				// 			'tipe'        	=> $jenis_jurnal,
				// 			'nomor'       	=> $Nomor_JV,
				// 			'tanggal'     	=> $payment_date,
				// 			'no_reff'     	=> $recpc->no_doc,
				// 			'no_perkiraan'	=> $record->coa,
				// 			'keterangan' 	=> $ketpetty . $record->deskripsi,
				// 			'debet' 		=> 0,
				// 			'kredit' 		=> $record->kasbon
				// 		);
				// 	} else {
				// 		//expense
				// 		$det_Jurnaltes1[] = array(
				// 			'nomor' => $nomor_jurnal, 'tanggal' => $payment_date, 'tipe' => 'BUM', 'no_perkiraan' => $record->coa, 'keterangan' => $ketpetty . $record->deskripsi, 'no_request' => $recpc->no_doc, 'debet' => $record->expense, 'kredit' => 0, 'no_reff' =>  $recpc->no_doc, 'jenis_jurnal' => $jenis_jurnal, 'nocust' => $recpc->nama, 'stspos' => '1'
				// 		);
				// 		$datadetail = array(
				// 			'tipe'        	=> $jenis_jurnal,
				// 			'nomor'       	=> $Nomor_JV,
				// 			'tanggal'     	=> $payment_date,
				// 			'no_reff'     	=> $recpc->no_doc,
				// 			'no_perkiraan'	=> $record->coa,
				// 			'keterangan' 	=> $ketpetty . $record->deskripsi,
				// 			'debet' 		=> $record->expense,
				// 			'kredit' 		=> 0
				// 		);
				// 		$total = $total + $record->expense;
				// 		if ($recpc->no_so != "") {
				// 			$datadeferred = array(
				// 				'no_so'        	=> $recpc->no_so,
				// 				'tanggal'     	=> $payment_date,
				// 				//							'no_reff'     	=> $recpc->no_doc,
				// 				'tipe'		 	=> 'expense',
				// 				'qty'	 		=> 1,
				// 				'amount' 		=> $record->expense,
				// 				'id_material'	=> "",
				// 				'nm_material'	=> "",
				// 				'keterangan'	=> $ketpetty,
				// 				'kode_trans'	=> $recpc->no_doc
				// 			);
				// 			$this->db->insert('tr_deferred', $datadeferred);
				// 		}
				// 	}
				// 	$this->db->insert(DBACC . '.jurnal', $datadetail);
				// }
				// if ($recpc->transfer_jumlah > 0) {
				// 	//bank coa
				// 	$det_Jurnaltes1[] = array(
				// 		'nomor' => $nomor_jurnal, 'tanggal' => $payment_date, 'tipe' => 'BUM', 'no_perkiraan' => $recpc->transfer_coa_bank, 'keterangan' => $ketpetty, 'no_request' => $recpc->no_doc, 'debet' =>  $recpc->transfer_jumlah, 'kredit' => 0, 'no_reff' =>  $recpc->no_doc, 'jenis_jurnal' => $jenis_jurnal, 'nocust' => $recpc->nama, 'stspos' => '1'
				// 	);
				// 	$datadetail = array(
				// 		'tipe'        	=> $jenis_jurnal,
				// 		'nomor'       	=> $Nomor_JV,
				// 		'tanggal'     	=> $payment_date,
				// 		'no_reff'     	=> $recpc->no_doc,
				// 		'no_perkiraan'	=> $recpc->transfer_coa_bank,
				// 		'keterangan' 	=> $recpc->informasi,
				// 		'debet' 		=> $recpc->transfer_jumlah,
				// 		'kredit' 		=> 0
				// 	);
				// 	$this->db->insert(DBACC . '.jurnal', $datadetail);
				// 	$total = $total + $recpc->transfer_jumlah;
				// }
				// if ($recpc->add_ppn_nilai > 0) {
				// 	//ppn coa
				// 	$det_Jurnaltes1[] = array(
				// 		'nomor' => $nomor_jurnal, 'tanggal' => $payment_date, 'tipe' => 'BUM', 'no_perkiraan' => $recpc->add_ppn_coa, 'keterangan' => $ketpetty, 'no_request' => $recpc->no_doc, 'debet' =>  $recpc->add_ppn_nilai, 'kredit' => 0, 'no_reff' =>  $recpc->no_doc, 'jenis_jurnal' => $jenis_jurnal, 'nocust' => $recpc->nama, 'stspos' => '1'
				// 	);
				// 	$datadetail = array(
				// 		'tipe'        	=> $jenis_jurnal,
				// 		'nomor'       	=> $Nomor_JV,
				// 		'tanggal'     	=> $payment_date,
				// 		'no_reff'     	=> $recpc->no_doc,
				// 		'no_perkiraan'	=> $recpc->add_ppn_coa,
				// 		'keterangan' 	=> $ketpetty,
				// 		'debet' 		=> $recpc->add_ppn_nilai,
				// 		'kredit' 		=> 0
				// 	);
				// 	$this->db->insert(DBACC . '.jurnal', $datadetail);
				// }
				// if ($recpc->add_pph_nilai > 0) {
				// 	//pph coa
				// 	$det_Jurnaltes1[] = array(
				// 		'nomor' => $nomor_jurnal, 'tanggal' => $payment_date, 'tipe' => 'BUM', 'no_perkiraan' => $recpc->add_pph_coa, 'keterangan' => $ketpetty, 'no_request' => $recpc->no_doc, 'debet' =>  0, 'kredit' => $recpc->add_pph_nilai, 'no_reff' =>  $recpc->no_doc, 'jenis_jurnal' => $jenis_jurnal, 'nocust' => $recpc->nama, 'stspos' => '1'
				// 	);
				// 	$datadetail = array(
				// 		'tipe'        	=> $jenis_jurnal,
				// 		'nomor'       	=> $Nomor_JV,
				// 		'tanggal'     	=> $payment_date,
				// 		'no_reff'     	=> $recpc->no_doc,
				// 		'no_perkiraan'	=> $recpc->add_pph_coa,
				// 		'keterangan' 	=> $ketpetty,
				// 		'debet' 		=> 0,
				// 		'kredit' 		=> $recpc->add_pph_nilai
				// 	);
				// 	$this->db->insert(DBACC . '.jurnal', $datadetail);
				// }
				// $this->db->insert_batch('jurnaltras', $det_Jurnaltes1);
				// $keterangan	= 'Penerimaan Expense ' . $recpc->no_doc;
				// $dataJVhead = array(
				// 	'nomor' 	    	=> $Nomor_JV,
				// 	'tgl'	         	=> $payment_date,
				// 	'jml'	            => $total,
				// 	'kdcab'				=> '101',
				// 	'jenis_reff'	    => $jenis_jurnal,
				// 	'no_reff' 		    => $recpc->no_doc,
				// 	'jenis_ar'			=> $jenis_jurnal,
				// 	'note'				=> $keterangan,
				// 	'terima_dari'		=> $recpc->nama,
				// 	'user_id'			=> $UserName,
				// 	'ho_valid'			=> '',
				// 	'batal'			    => '0'
				// );
				// if ($exjumlah == 0) {
				// 	$dataJVhead = array('nomor' => $Nomor_JV, 'tgl' => $payment_date, 'jml' => $total, 'koreksi_no' => '-', 'kdcab' => '101', 'jenis' => $jenis_jurnal, 'keterangan' => $keterangan, 'bulan' => $Bln, 'tahun' => $Thn, 'user_id' => $UserName, 'memo' => $recpc->no_doc, 'tgl_jvkoreksi' => $payment_date, 'ho_valid' => '');
				// 	$this->db->insert(DBACC . '.javh', $dataJVhead);
				// } else {
				// 	$this->db->insert(DBACC . '.jarh', $dataJVhead);
				// 	$Qry_Update_Cabang_acc	 = "UPDATE " . DBACC . ".pastibisa_tb_cabang SET nobum=nobum + 1 WHERE nocab='101'";
				// 	$this->db->query($Qry_Update_Cabang_acc);
				// }
				// if (is_numeric($results)) {
				// 	$result	= TRUE;
				// } else {
				// 	$result = FALSE;
				// }
				if ($this->db->trans_status() === FALSE || $this->db->trans_commit() === FALSE) {
					$result = FALSE;
					$this->db->trans_rollback();
					$auditOutcome = 'insert_failed';
				} else {
					$result = TRUE;
					$auditOutcome = 'confirmed';
					$auditStatus = 1;
				}
				// history('Approve data expense : ' . $id);
			} elseif (!$get_expense) {
				$auditOutcome = 'not_found';
			}
		} else {
			$auditOutcome = 'empty_id';
		}

		$this->completeAudit(
			$result ? 'Berhasil konfirmasi pengembalian expense' : ($valid == 2 ? 'Pengembalian melebihi sisa expense' : 'Gagal konfirmasi pengembalian expense'),
			$auditStatus,
			array(
				'id' => $id,
				'no_doc' => $no_doc,
				'valid' => $valid,
				'outcome' => $auditOutcome
			)
		);

		$param = array(
			'save' => $result,
			'id' => $id,
			'valid' => $valid
		);
		echo json_encode($param);
	}

	public function get_pr_non_po()
	{
		$no_pr = $this->input->post('no_pr');
		$this->beginAudit(__FUNCTION__, array('no_pr' => $no_pr), array('no_pr', 'valid', 'outcome'));

		$this->db->select('if(c.nama IS NULL, e.stock_name, c.nama) as material_name, a.propose_purchase as qty, if(d.code IS NULL, f.code, d.code) as unit, b.category as tipe_pr, a.id, a.price_ref')
			->from('material_planning_base_on_produksi_detail a')
			->join('material_planning_base_on_produksi b', 'b.so_number = a.so_number')
			->join('new_inventory_4 c', 'c.code_lv4 = a.id_material', 'left')
			->join('ms_satuan d', 'd.id = c.id_unit', 'left')
			->join('accessories e', 'e.id = a.id_material', 'left')
			->join('ms_satuan f', 'f.id = e.id_unit', 'left')
			->where('b.no_pr', $no_pr)
			->where('b.metode_pembelian', '2')
			->where('a.kasbon_created', null);
		$get_detail_pr_stok_material = $this->db->get()->result_array();

		$this->db->select('a.id, a.nm_barang as material_name, a.qty, c.code as unit, a.harga as price, (a.qty * a.harga) as total_price,"pr departemen" as tipe_pr, 0 as price_ref')
			->from('rutin_non_planning_detail a')
			->join('rutin_non_planning_header b', 'b.no_pr = a.no_pr', 'left')
			->join('ms_satuan c', 'c.id = a.satuan', 'left')
			->where('a.no_pr', $no_pr)
			->where('b.metode_pembelian', '2')
			->where('a.sts_app', 'Y')
			->where('a.kasbon_created', null);
		$get_detail_pr_departemen = $this->db->get()->result_array();

		$this->db->select('a.id, b.nama_asset as material_name, b.qty, "Pcs" as unit, b.budget as price, (b.budget * b.qty) as total_price, "pr asset" as tipe_pr, 0 as price_ref');
		$this->db->from('tran_pr_header a');
		$this->db->join('asset_planning b', 'b.no_pr = a.no_pr', 'left');
		$this->db->where('a.no_pr', $no_pr);
		$this->db->where('a.metode_pembelian', '2');
		$this->db->where('a.kasbon_created', null);
		$get_detail_pr_asset = $this->db->get()->result_array();

		$tipe_pr = '';

		// print_r(count($get_detail_pr_stok_material));
		// print_r(count($get_detail_pr_departemen));
		// exit;
		$valid = 1;
		$hasil = '';
		$grand_total = 0;
		if (count($get_detail_pr_stok_material) < 1 && count($get_detail_pr_departemen) < 1 && count($get_detail_pr_asset) < 1) {
			$valid = 0;
		} else {
			$no = 1;
			if (count($get_detail_pr_stok_material) > 0) {
				foreach ($get_detail_pr_stok_material as $detail_pr) :
					if ($tipe_pr == '') {
						$tipe_pr = $detail_pr['tipe_pr'];
					}

					$price_ref = $detail_pr['price_ref'];

					$hasil .= '<tr class="detail_pr_' . $detail_pr['id'] . '">';
					$hasil .= '<td class="text-center">' . $no . '</td>';
					$hasil .= '<td class="text-center">' . $detail_pr['material_name'] . '</td>';
					$hasil .= '<td class="text-center">' . number_format($detail_pr['qty']) . ' <input type="hidden" class="qty_' . $detail_pr['id'] . '" value="' . $detail_pr['qty'] . '"></td>';
					$hasil .= '<td class="text-center">' . $detail_pr['unit'] . '</td>';
					$hasil .= '<td class="text-center"><input type="text" name="price_input_' . $detail_pr['id'] . '" class="form-control form-control-sm text-right price_input price_input_' . $detail_pr['id'] . ' autonum" data-no="' . $detail_pr['id'] . '" value="' . $price_ref . '"></td>';
					$hasil .= '<td class="text-center"><input type="text" name="grand_total_' . $detail_pr['id'] . '" class="form-control form-control-sm text-right grand_total_' . $detail_pr['id'] . ' autonum" value="' . ($price_ref * $detail_pr['qty']) . '"></td>';
					$hasil .= '<td class="text-center"><button type="button" class="btn btn-sm btn-danger del_detail" data-no="' . $detail_pr['id'] . '"><i class="fa fa-trash"></i></button></td>';
					$hasil .= '</tr>';
					$no++;

					$grand_total += ($price_ref * $detail_pr['qty']);
				endforeach;
			}

			if (count($get_detail_pr_departemen) > 0) {
				foreach ($get_detail_pr_departemen as $detail_pr) :
					if ($tipe_pr == '') {
						$tipe_pr = $detail_pr['tipe_pr'];
					}

					$hasil .= '<tr class="detail_pr_' . $detail_pr['id'] . '">';
					$hasil .= '<td class="text-center">' . $no . '</td>';
					$hasil .= '<td class="text-center">' . $detail_pr['material_name'] . '</td>';
					$hasil .= '<td class="text-center">' . number_format($detail_pr['qty']) . ' <input type="hidden" class="qty_' . $detail_pr['id'] . '" value="' . $detail_pr['qty'] . '"></td>';
					$hasil .= '<td class="text-center">' . $detail_pr['unit'] . '</td>';
					$hasil .= '<td class="text-center"><input type="text" name="price_input_' . $detail_pr['id'] . '" class="form-control form-control-sm text-right price_input price_input_' . $detail_pr['id'] . ' autonum" data-no="' . $detail_pr['id'] . '" value="' . $detail_pr['price'] . '"></td>';
					$hasil .= '<td class="text-center"><input type="text" name="grand_total_' . $detail_pr['id'] . '" class="form-control form-control-sm text-right grand_total_' . $detail_pr['id'] . ' autonum" value="' . $detail_pr['total_price'] . '"></td>';
					$hasil .= '<td class="text-center"><button type="button" class="btn btn-sm btn-danger del_detail" data-no="' . $detail_pr['id'] . '"><i class="fa fa-trash"></i></button></td>';
					$hasil .= '</tr>';

					$grand_total += ($detail_pr['total_price']);
					$no++;
				endforeach;
			}

			if (count($get_detail_pr_asset)) {
				foreach ($get_detail_pr_asset as $detail_pr) :
					if ($tipe_pr == '') {
						$tipe_pr = $detail_pr['tipe_pr'];
					}

					$hasil .= '<tr class="detail_pr_' . $detail_pr['id'] . '">';
					$hasil .= '<td class="text-center">' . $no . '</td>';
					$hasil .= '<td class="text-center">' . $detail_pr['material_name'] . '</td>';
					$hasil .= '<td class="text-center">' . number_format($detail_pr['qty']) . ' <input type="hidden" class="qty_' . $detail_pr['id'] . '" value="' . $detail_pr['qty'] . '"></td>';
					$hasil .= '<td class="text-center">' . $detail_pr['unit'] . '</td>';
					$hasil .= '<td class="text-center"><input type="text" name="price_input_' . $detail_pr['id'] . '" class="form-control form-control-sm text-right price_input price_input_' . $detail_pr['id'] . ' autonum" data-no="' . $detail_pr['id'] . '" value="' . $detail_pr['price'] . '"></td>';
					$hasil .= '<td class="text-center"><input type="text" name="grand_total_' . $detail_pr['id'] . '" class="form-control form-control-sm text-right grand_total_' . $detail_pr['id'] . ' autonum" value="' . $detail_pr['total_price'] . '"></td>';
					$hasil .= '<td class="text-center"><button type="button" class="btn btn-sm btn-danger del_detail" data-no="' . $detail_pr['id'] . '"><i class="fa fa-trash"></i></button></td>';
					$hasil .= '</tr>';

					$grand_total += ($detail_pr['total_price']);
					$no++;
				endforeach;
			}
		}

		$pesan = '';
		if ($valid == '0') {
			$pesan = 'Sorry, PR not found !';
		}

		$this->completeAudit(
			$valid == 1 ? 'Berhasil mengambil detail PR Non-PO' : 'PR Non-PO tidak ditemukan',
			1,
			array(
				'no_pr' => $no_pr,
				'valid' => $valid,
				'outcome' => $valid == 1 ? 'found' : 'not_found'
			)
		);

		echo json_encode([
			'sts' => $valid,
			'hasil' => $hasil,
			'pesan' => $pesan,
			'tipe_pr' => $tipe_pr,
			'grand_total' => $grand_total
		]);
	}

	public function del_detail()
	{
		$id_detail = $this->input->post('id_detail');
		$this->beginAudit(__FUNCTION__, array('id_detail' => $id_detail), array('id_detail', 'outcome'));

		$this->db->trans_begin();

		$this->db->delete('tr_expense_detail', ['id' => $id_detail]);

		if ($this->db->trans_status() === FALSE || $this->db->trans_commit() === FALSE) {
			$this->db->trans_rollback();
			$auditOutcome = 'delete_failed';
			$auditStatus = 0;
		} else {
			$auditOutcome = 'deleted';
			$auditStatus = 1;
		}

		$this->completeAudit(
			$auditStatus ? 'Berhasil menghapus detail expense' : 'Gagal menghapus detail expense',
			$auditStatus,
			array(
				'id_detail' => $id_detail,
				'outcome' => $auditOutcome
			)
		);
	}

	public function del_detail_kasbon_non_po()
	{
		$id_detail = $this->input->post('id_detail');
		$id_kasbon_non_po = $this->input->post('id_kasbon_non_po');
		$this->beginAudit(__FUNCTION__, array('id_detail' => $id_detail, 'id_kasbon_non_po' => $id_kasbon_non_po), array('id_detail', 'id_kasbon_non_po', 'outcome'));

		$this->db->trans_begin();

		$this->db->update('tr_kasbon', ['id_expense_pett_pr_non_po' => null], ['no_doc' => $id_kasbon_non_po]);
		$this->db->delete('tr_expense_detail', ['id' => $id_detail]);

		if ($this->db->trans_status() === FALSE || $this->db->trans_commit() === FALSE) {
			$this->db->trans_rollback();
			$auditOutcome = 'delete_failed';
			$auditStatus = 0;
		} else {
			$auditOutcome = 'deleted';
			$auditStatus = 1;
		}

		$this->completeAudit(
			$auditStatus ? 'Berhasil menghapus detail kasbon non PO' : 'Gagal menghapus detail kasbon non PO',
			$auditStatus,
			array(
				'id_detail' => $id_detail,
				'id_kasbon_non_po' => $id_kasbon_non_po,
				'outcome' => $auditOutcome
			)
		);
	}

	public function add_kasbon_pr()
	{
		$post = $this->input->post();
		$no_doc = isset($post['no_doc']) ? $post['no_doc'] : null;
		$this->beginAudit(__FUNCTION__, array('no_doc' => $no_doc), array('no_doc', 'outcome'));

		$get_kasbon = !empty($no_doc) ? $this->db->get_where('tr_kasbon', ['no_doc' => $no_doc])->row() : null;
		if ($get_kasbon) {
			$this->auditObserve($get_kasbon);
			$auditOutcome = 'found';
			$auditStatus = 1;
		} else {
			$auditOutcome = 'not_found';
			$auditStatus = 0;
		}

		$this->completeAudit(
			$auditStatus ? 'Data kasbon ditemukan' : 'Data kasbon tidak ditemukan',
			$auditStatus,
			array(
				'no_doc' => $no_doc,
				'outcome' => $auditOutcome
			)
		);

		echo json_encode([
			'no_doc' => !empty($get_kasbon) ? $get_kasbon->no_doc : '',
			'keperluan' => !empty($get_kasbon) ? $get_kasbon->keperluan : '',
			'jumlah_kasbon' => !empty($get_kasbon) ? $get_kasbon->jumlah_kasbon : 0,
		]);
	}

	public function add_ganti_expense()
	{
		$post = $this->input->post();
		$no_doc = isset($post['no_doc']) ? $post['no_doc'] : null;
		$tgl = isset($post['tgl']) ? $post['tgl'] : date('Y-m-d');
		$this->beginAudit(__FUNCTION__, array('no_doc' => $no_doc, 'tgl' => $tgl), array('no_doc', 'id_detail', 'outcome'));

		$get_expense = !empty($no_doc) ? $this->db->get_where('tr_expense', ['no_doc' => $no_doc])->row() : null;
		if ($get_expense) {
			$this->auditObserve($get_expense);
		}

		$id_detail = null;
		$no_doc2 = null;
		$auditStatus = 0;
		$auditOutcome = 'failed';

		if (empty($get_expense)) {
			$auditOutcome = 'not_found';
			$auditStatus = 0;
			$response = [
				'no_doc' => $no_doc,
				'informasi' => '',
				'jumlah' => 0,
				'id_detail' => null,
				'no_doc2' => null
			];
		} else {
			$this->db->trans_begin();

			$jumlah = isset($get_expense->jumlah) ? $get_expense->jumlah : 0;
			$this->db->insert('tr_expense_detail', [
				'tanggal' => $tgl,
				'no_doc' => $this->auth->user_id(),
				'qty' => 1,
				'harga' => $jumlah,
				'total_harga' => $jumlah,
				'status' => 0,
				'expense' => $jumlah,
				'created_by' => $this->auth->user_name(),
				'created_on' => date('Y-m-d H:i:s'),
				'id_expense_bayar_sisa' => $no_doc
			]);

			if ($this->db->trans_status() === FALSE || $this->db->trans_commit() === FALSE) {
				$this->db->trans_rollback();
				$auditOutcome = 'insert_failed';
				$auditStatus = 0;
			} else {
				$auditOutcome = 'created';
				$auditStatus = 1;
			}

			$get_expensed_bayar = $this->db->get_where('tr_expense_detail', ['id_expense_bayar_sisa' => $no_doc])->row();
			if (!empty($get_expensed_bayar)) {
				$id_detail = $get_expensed_bayar->id;
				$no_doc2 = $get_expensed_bayar->no_doc;
			}

			$response = [
				'no_doc' => $get_expense->no_doc,
				'informasi' => $get_expense->informasi,
				'jumlah' => $get_expense->jumlah,
				'id_detail' => $id_detail,
				'no_doc2' => $no_doc2
			];
		}

		$this->completeAudit(
			$auditStatus ? 'Berhasil menambah ganti expense' : 'Gagal menambah ganti expense',
			$auditStatus,
			array(
				'no_doc' => $no_doc,
				'id_detail' => $id_detail,
				'outcome' => $auditOutcome
			)
		);

		echo json_encode($response);
	}

	public function refresh_list_kasbon_non_pr()
	{
		$post = $this->input->post();
		$no_doc = isset($post['no_doc']) ? $post['no_doc'] : null;
		$this->beginAudit(__FUNCTION__, array('no_doc' => $no_doc), array('no_doc', 'row_count', 'outcome'));

		if (!empty($no_doc)) {
			$data_kasbon_pr_pet = $this->db->get_where('tr_kasbon', ['no_doc !=' => $no_doc, 'metode_pembayaran' => 2, 'id_expense_pett_pr_non_po' => null])->result();
		} else {
			$data_kasbon_pr_pet = $this->db->get_where('tr_kasbon', ['metode_pembayaran' => 2, 'id_expense_pett_pr_non_po' => null])->result();
		}

		$rowCount = is_array($data_kasbon_pr_pet) ? count($data_kasbon_pr_pet) : 0;
		$hasil = '';
		if (!empty($data_kasbon_pr_pet)) {
			foreach ($data_kasbon_pr_pet as $item) {
				$hasil .= '<tr>';
				$hasil .= '<td class="text-center">' . (isset($item->created_by) ? $item->created_by : '') . '</td>';
				$hasil .= '<td class="text-left">' . (isset($item->no_doc) ? $item->no_doc : '') . '</td>';
				$hasil .= '<td class="text-left">' . (isset($item->keperluan) ? $item->keperluan : '') . '</td>';
				$hasil .= '<td class="text-center">' . number_format(isset($item->jumlah_kasbon) ? $item->jumlah_kasbon : 0) . '</td>';
				$hasil .= '<td class="text-center">
									<button type="button" class="btn btn-sm btn-success add_kasbon_pr" data-no_doc="' . (isset($item->no_doc) ? $item->no_doc : '') . '">Bayar</button>
								</td>';
				$hasil .= '</tr>';
			}
		}

		$this->completeAudit(
			'Berhasil me-refresh list kasbon non PR',
			1,
			array(
				'no_doc' => $no_doc,
				'row_count' => $rowCount,
				'outcome' => 'rendered'
			)
		);

		echo $hasil;
	}

	public function refresh_list_expense_kembalian()
	{
		$post = $this->input->post();
		$no_doc = isset($post['no_doc']) ? $post['no_doc'] : null;
		$this->beginAudit(__FUNCTION__, array('no_doc' => $no_doc), array('no_doc', 'row_count', 'outcome'));

		$this->db->select('a.*');
		$this->db->from('tr_expense a');
		$this->db->where('(a.tipe_pengembalian = 1 OR a.tipe_penggantian = 1)');
		$this->db->where('a.status', 1);
		$this->db->where('a.jumlah <>', 0);
		$this->db->where('a.exp_pib', null);
		$this->db->where('a.exp_inv_po', null);
		$this->db->where('a.pettycash', null);
		$this->db->where('a.expense_id_kembalian', null);
		$this->db->where('(SELECT COUNT(aa.id) FROM tr_expense_detail aa WHERE aa.id_expense_bayar_sisa = a.no_doc) <', 1);
		$data_expense_kembalian = $this->db->get()->result();

		$rowCount = is_array($data_expense_kembalian) ? count($data_expense_kembalian) : 0;
		$hasil = '';
		if (!empty($data_expense_kembalian)) {
			foreach ($data_expense_kembalian as $item) {
				$hasil .= '<tr>';
				$hasil .= '<td class="text-center">' . (isset($item->created_by) ? $item->created_by : '') . '</td>';
				$hasil .= '<td class="text-left">' . (isset($item->no_doc) ? $item->no_doc : '') . '</td>';
				$hasil .= '<td class="text-left">' . (isset($item->informasi) ? $item->informasi : '') . '</td>';
				$hasil .= '<td class="text-center">' . number_format(isset($item->jumlah) ? $item->jumlah : 0) . '</td>';
				$hasil .= '<td class="text-center">
									<button type="button" class="btn btn-sm btn-success add_ganti_expense" data-no_doc="' . (isset($item->no_doc) ? $item->no_doc : '') . '">Bayar</button>
								</td>';
				$hasil .= '</tr>';
			}
		}

		$this->completeAudit(
			'Berhasil me-refresh list expense kembalian',
			1,
			array(
				'no_doc' => $no_doc,
				'row_count' => $rowCount,
				'outcome' => 'rendered'
			)
		);

		echo $hasil;
	}

	public function del_detail_kembalian_expense()
	{
		$id_detail = $this->input->post('id_detail');
		$id_expense_kembalian = $this->input->post('id_expense_kembalian');
		$this->beginAudit(__FUNCTION__, array('id_detail' => $id_detail, 'id_expense_kembalian' => $id_expense_kembalian), array('id_detail', 'id_expense_kembalian', 'outcome'));

		$this->db->trans_begin();

		$this->db->update('tr_expense', ['expense_id_kembalian' => null], ['no_doc' => $id_expense_kembalian]);
		$this->db->delete('tr_expense_detail', ['id' => $id_detail]);

		if ($this->db->trans_status() === FALSE || $this->db->trans_commit() === FALSE) {
			$this->db->trans_rollback();
			$auditOutcome = 'delete_failed';
			$auditStatus = 0;
		} else {
			$auditOutcome = 'deleted';
			$auditStatus = 1;
		}

		$this->completeAudit(
			$auditStatus ? 'Berhasil menghapus detail kembalian expense' : 'Gagal menghapus detail kembalian expense',
			$auditStatus,
			array(
				'id_detail' => $id_detail,
				'id_expense_kembalian' => $id_expense_kembalian,
				'outcome' => $auditOutcome
			)
		);
	}

	public function copy_pr_doc()
	{
		$post = $this->input->post();
		$no_pr = isset($post['no_pr']) ? $post['no_pr'] : null;
		$this->beginAudit(__FUNCTION__, array('no_pr' => $no_pr), array('no_pr', 'source_type', 'attachment_count', 'outcome'));

		$get_pr_dept = null;
		$get_pr_asset = null;
		if (!empty($no_pr)) {
			$this->db->select('a.id, a.no_pr, a.document');
			$this->db->from('rutin_non_planning_header a');
			$this->db->where('a.no_pr', $no_pr);
			$get_pr_dept = $this->db->get()->row();

			$this->db->select('a.no_pr, a.dokumen_pendukung');
			$this->db->from('tran_pr_header a');
			$this->db->where('a.no_pr', $no_pr);
			$get_pr_asset = $this->db->get()->row();
		}

		$file_name = '';
		$doc_file = '';
		$to_doc_file = '';
		$source_type = 'none';
		$directory = !empty($get_pr_dept) ? 'assets/pr/' : 'uploads/pr_asset/';
		if (!empty($get_pr_dept)) {
			$file_name = $get_pr_dept->document;
			$source_type = 'rutin_non_planning';
			$this->auditObserve($get_pr_dept);
		} elseif (!empty($get_pr_asset)) {
			$file_name = $get_pr_asset->dokumen_pendukung;
			$source_type = 'asset';
			$this->auditObserve($get_pr_asset);
		}

		$attachments = array();
		$attachment_error = null;
		try {
			foreach (kasbon_attachment_names($file_name) as $name) {
				if ($doc_file === '') {
					$doc_file = $directory . $name;
					$to_doc_file = 'assets/expense/' . $name;
				}
				$available = kasbon_attachment_path(FCPATH . $directory, $name) !== false;
				$attachments[] = array('name' => $name, 'url' => $available ? base_url($directory . rawurlencode($name)) : null);
			}
		} catch (RuntimeException $e) {
			$attachment_error = $e->getMessage();
		}

		$auditOutcome = ($source_type !== 'none') ? 'found' : 'not_found';
		$auditStatus = empty($attachment_error) ? 1 : 0;

		$this->completeAudit(
			$auditStatus ? 'Berhasil membaca dokumen PR' : 'Gagal membaca dokumen PR',
			$auditStatus,
			array(
				'no_pr' => $no_pr,
				'source_type' => $source_type,
				'attachment_count' => count($attachments),
				'outcome' => $auditOutcome
			)
		);

		echo json_encode([
			'file_name' => $file_name,
			'doc_file' => $doc_file,
			'to_doc_file' => $to_doc_file,
			'attachments' => $attachments,
			'message' => isset($attachment_error) ? $attachment_error : ''
		]);
	}

	public function get_data_transport_input()
	{
		$this->beginAudit(__FUNCTION__, array('page' => 'transport_input'), array('page', 'outcome'));
		$this->Expense_model->get_data_transport_input();
		$this->completeAudit('Data transport input berhasil diambil', 1, array('page' => 'transport_input', 'outcome' => 'rendered'));
	}

	public function get_data_transport_req_fin_list()
	{
		$this->beginAudit(__FUNCTION__, array('page' => 'transport_req_fin_list'), array('page', 'outcome'));
		$this->Expense_model->get_data_transport_req_fin_list();
		$this->completeAudit('Data transport req finance list berhasil diambil', 1, array('page' => 'transport_req_fin_list', 'outcome' => 'rendered'));
	}

	public function get_data_transport_req()
	{
		$this->beginAudit(__FUNCTION__, array('page' => 'transport_req'), array('page', 'outcome'));
		$this->Expense_model->get_data_transport_req();
		$this->completeAudit('Data transport request berhasil diambil', 1, array('page' => 'transport_req', 'outcome' => 'rendered'));
	}

	public function get_data_transport_req_all()
	{
		$this->Expense_model->get_data_transport_req_all();
	}

	public function get_coa_name()
	{
		$no_coa = $this->input->post('no_coa');

		$get_coa_detail = $this->Expense_model->getCoaDetail($no_coa);

		echo json_encode([
			'nm_coa' => $get_coa_detail->nama
		]);
	}

	public function kasbon_fin_manage()
	{
		// $datawhere = ("a.status = 0");
		// $data = $this->Expense_model->GetListDataKasbon($datawhere);
		$this->template->set('status', $this->status);
		// $this->template->set('results', $data);
		$this->template->set('stsview', 'view');
		$this->template->title('Kasbon Approval by Management');
		$this->template->page_icon('fa fa-list');
		$this->template->render('kasbon_list_fin_manage');
	}

	public function get_dat_list_kasbon()
	{
		$post   = $this->input->post();
		$draw   = intval($post['draw']);
		$length = isset($post['length']) ? $post['length'] : 10;
		$start  = isset($post['start']) ? $post['start'] : 0;
		$search = isset($post['search']['value']) ? $post['search']['value'] : '';

		$is_admin = $this->auth->is_admin();

		// 1. Count all (tanpa search)
		$this->db->from('v_kasbon_list');
		if (!$is_admin) {
			$this->db->where('nmuser_fix', $this->auth->user_name());
		}
		$count_all = $this->db->count_all_results();

		// 2. Count filtered (dengan search)
		$this->db->from('v_kasbon_list');
		if (!$is_admin) {
			$this->db->where('nmuser_fix', $this->auth->user_name());
		}
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('no_doc', $search);
			$this->db->or_like('tgl_doc', $search);
			$this->db->or_like('keperluan', $search);
			$this->db->or_like('keterangan', $search);
			$this->db->or_like('nmuser_fix', $search);
			$this->db->group_end();
		}
		$count_filter = $this->db->count_all_results();

		// 3. Fetch data
		$this->db->from('v_kasbon_list');
		if (!$is_admin) {
			$this->db->where('nmuser_fix', $this->auth->user_name());
		}
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('no_doc', $search);
			$this->db->or_like('tgl_doc', $search);
			$this->db->or_like('keperluan', $search);
			$this->db->or_like('keterangan', $search);
			$this->db->or_like('nmuser_fix', $search);
			$this->db->group_end();
		}

		// 4. Ordering
		$columns = [
			0 => 'no_doc',
			1 => 'no_doc',
			2 => 'tgl_doc',
			3 => 'nmuser_fix',
			4 => 'keperluan',
			5 => 'keterangan'
		];

		if (isset($post['order'][0]['column'])) {
			$col_idx = $post['order'][0]['column'];
			$this->db->order_by($columns[$col_idx], $post['order'][0]['dir']);
		} else {
			$this->db->order_by('no_doc', 'desc');
		}

		// 5. Limit & Fetch
		$this->db->limit($length, $start);
		$get_data = $this->db->get()->result_array();

		$hasil = [];
		$no = $start;
		foreach ($get_data as $item) {
			$no++;

			$hasil[] = [
				'no'        => $no,
				'no_kasbon' => $item['no_doc'],
				'tanggal'   => $item['tgl_doc'],
				'nama'      => $item['nmuser_fix'], // Langsung dari View
				'keperluan' => $item['keperluan'],
				'keterangan' => $item['keterangan'],
				'status'    => $this->_render_status_badge($item),
				'action'    => $this->_render_action_buttons($item),
			];
		}

		$response = [
			'draw'            => $draw,
			'recordsTotal'    => $count_all,
			'recordsFiltered' => $count_filter,
			'data'            => $hasil
		];

		$this->output
			->set_content_type('application/json')
			->set_status_header(200)
			->set_output(json_encode($response));
	}

	// --- Helper Badge Status ---
	private function _render_status_badge($item)
	{
		$sts = '';
		if ($item['status'] == '0') {
			$sts = '<div class="badge bg-blue text-light">Waiting Approval Finance</div>';
			if ($item['sts_finance'] == '1') {
				$sts = '<div class="badge bg-blue">Waiting Approval Management</div>';
			}
		} elseif (in_array($item['status'], ['1', '2'])) {
			$sts = '<div class="badge bg-green text-light">Approved</div>';
		} elseif ($item['status'] == '3') {
			$check = $this->db->get_where('tr_expense_detail', ['id_kasbon' => $item['no_doc'], 'status' => 2])->row();
			$sts = (!empty($check)) ? '<div class="badge bg-dark text-light">Close</div>' : '<div class="badge bg-green text-light">Paid</div>';
		} elseif ($item['status'] == '9') {
			$sts = '<div class="badge bg-red text-light">Reject</div>';
		} elseif ($item['status'] == '4') {
			$sts = '<div class="badge bg-blue text-dark">Kurang</div>';
		}
		return $sts;
	}

	// --- Helper Badge Status Khusus Modul Expense ---
	private function _render_expense_status_badge($item)
	{
		$status_val = is_array($item) ? (isset($item['status']) ? (string)$item['status'] : '') : (isset($item->status) ? (string)$item->status : '');
		$sts_fin    = is_array($item) ? (isset($item['sts_finance']) ? (string)$item['sts_finance'] : '') : (isset($item->sts_finance) ? (string)$item->sts_finance : '');

		// 1. Rejected (9)
		if ($status_val === '9') {
			$sts = '<span class="badge bg-red" style="font-size:11px; padding:4px 8px;"><i class="fa fa-times-circle"></i> Ditolak</span>';
			$reason = '';
			if (is_array($item)) {
				$reason = !empty($item['reject_reason']) ? $item['reject_reason'] : (!empty($item['reject_reason_finance']) ? $item['reject_reason_finance'] : (!empty($item['st_reject']) ? $item['st_reject'] : ''));
			} else {
				$reason = !empty($item->reject_reason) ? $item->reject_reason : (!empty($item->reject_reason_finance) ? $item->reject_reason_finance : (!empty($item->st_reject) ? $item->st_reject : ''));
			}
			if (!empty($reason)) {
				$sts .= '<br><span class="badge" style="background:#fde8e8; color:#c53030; border:1px solid #feb2b2; font-weight:normal; margin-top:4px; font-size:10px; display:inline-block; max-width:200px; white-space:normal; text-align:center; padding:3px 6px;" title="Alasan Reject: ' . htmlspecialchars($reason) . '"><i class="fa fa-info-circle"></i> ' . htmlspecialchars($reason) . '</span>';
			}
			return $sts;
		}

		// 2. Waiting Approval Finance (0)
		if ($status_val === '0' || $status_val === '') {
			if ($sts_fin === '1') {
				return '<span class="badge" style="background-color:#f39c12 !important; color:#fff; font-size:11px; padding:4px 8px;"><i class="fa fa-clock-o"></i> Menunggu Approval Direktur</span>';
			}
			return '<span class="badge bg-blue" style="font-size:11px; padding:4px 8px;"><i class="fa fa-clock-o"></i> Menunggu Approval Finance</span>';
		}

		// 3. Waiting Approval Direktur (1)
		if ($status_val === '1') {
			return '<span class="badge" style="background-color:#f39c12 !important; color:#fff; font-size:11px; padding:4px 8px;"><i class="fa fa-clock-o"></i> Menunggu Approval Direktur</span>';
		}

		// 4. Menunggu Pengembalian Kasbon (3)
		if ($status_val === '3') {
			return '<span class="badge" style="background-color:#6f42c1 !important; color:#fff; font-size:11px; padding:4px 8px;"><i class="fa fa-reply"></i> Menunggu Pengembalian</span>';
		}

		// 5. Menunggu Verifikasi Pengembalian (4)
		if ($status_val === '4') {
			return '<span class="badge" style="background-color:#fd7e14 !important; color:#fff; font-size:11px; padding:4px 8px;"><i class="fa fa-hourglass-half"></i> Menunggu Verifikasi</span>';
		}

		// 6. Selesai / Disetujui (2)
		if ($status_val === '2') {
			return '<span class="badge bg-green" style="font-size:11px; padding:4px 8px;"><i class="fa fa-check-circle"></i> Disetujui</span>';
		}

		return '<span class="badge bg-gray" style="font-size:11px; padding:4px 8px;">-</span>';
	}

	// --- Helper Tombol Action ---
	private function _render_action_buttons($item)
	{
		$action = '';

		// View & Print
		if (has_permission($this->viewPermissionKasbon)) {
			if (!empty($item['approved_by'])) {
			}
			if (!empty($item['no_kasbon_consultant'])) {
				$action .= ' <a class="btn btn-default btn-sm print" href="https://sendigs.com/consultant_new/approval_request_payment/print_kasbon/' . str_replace('/', '|', $item['no_kasbon_consultant']) . '" target="_blank" title="Print"><i class="fa fa-print"></i></a>';
			} else {
				$action .= ' <a class="btn btn-default btn-sm print" href="' . base_url('expense/kasbon_print/' . $item['id']) . '" target="_blank" title="Print"><i class="fa fa-print"></i></a>';
			}
			$action .= ' <a class="btn btn-warning btn-sm view" href="javascript:void(0)" title="View" onclick="data_view(' . $item['id'] . ')"><i class="fa fa-eye"></i></a>';
		}

		// Edit & Delete (Hanya jika belum diproses finance)
		if ($item['sts_finance'] == '0' && ($item['status'] == 0 || $item['status'] == 9)) {
			if (has_permission($this->managePermissionKasbon)) {
				$action .= ' <a class="btn btn-success btn-sm edit" href="javascript:void(0)" title="Edit" onclick="data_edit(' . $item['id'] . ')"><i class="fa fa-edit"></i></a>';
			}
			if (has_permission($this->deletePermissionKasbon)) {
				$action .= ' <a class="btn btn-danger btn-sm delete" href="javascript:void(0)" title="Hapus" onclick="data_delete(' . $item['id'] . ')"><i class="fa fa-trash"></i></a>';
			}
		}

		return $action;
	}

	public function get_dat_kasbon_list()
	{
		$is_admin = $this->auth->is_admin();
		$post = $this->input->post();
		$draw = intval($post['draw']);
		$length = $post['length'];
		$start = $post['start'];
		$search = $post['search']['value'];

		// SEKARANG KITA PAKAI VIEW
		$this->db->from('v_kasbon_list');

		if (!$is_admin) {
			$this->db->where('nmuser_fix', $this->auth->user_name());
		}

		// Get total records
		$count_all = $this->db->count_all_results('', false);

		// Search logic jadi simpel karena nmuser_fix sudah jadi kolom "asli" di View
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('no_doc', $search);
			$this->db->or_like('tgl_doc', $search);
			$this->db->or_like('nmuser_fix', $search); // Gak perlu panggil ulang subquery
			$this->db->or_like('keperluan', $search);
			$this->db->group_end();
		}

		$count_filter = $this->db->count_all_results('', false);

		// Ordering
		$this->db->order_by('no_doc', 'desc');

		$this->db->limit($length, $start);
		$get_data = $this->db->get()->result_array();

		// Data Loop
		$hasil = [];
		$no = $start;
		foreach ($get_data as $item) {
			$no++;
			$hasil[] = [
				'no'            => $no,
				'no_kasbon'     => $item['no_doc'],
				'tanggal'       => $item['tgl_doc'],
				'nama'          => $item['nmuser_fix'],
				'keperluan'		=> $item['keperluan'],
				'approval_date' => (!empty($item['approved_on']) && in_array($item['status'], [1, 2, 3])) ? $item['approved_on'] : '',
				'status'        => $this->_render_status_badge($item),
				'action'        => $this->_render_action_buttons($item), // Opsional: bungkus biar makin rapi
			];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode([
			'draw'            => $draw,
			'recordsTotal'    => $count_all,
			'recordsFiltered' => $count_filter,
			'data'            => $hasil
		]));
	}

	public function get_dat_app_kasbon()
	{
		$post = $this->input->post();

		$draw = intval($post['draw']);
		$length = $post['length'];
		$start = $post['start'];
		$search = $post['search']['value'];

		$this->db->select('a.*, b.nm_lengkap as nmuser');
		$this->db->from('tr_kasbon a');
		$this->db->join('users b', 'a.nama = b.username', 'left');
		$this->db->where('a.status', '0');
		$this->db->where('a.sts_finance', '0');

		// if ($this->auth->user_id() !== '7') {
		// 	$this->db->where('a.created_by', $this->auth->user_name());
		// }

		$count_all = $this->db->count_all_results('', false);

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('a.no_doc', $search, 'both');
			$this->db->or_like('a.tgl_doc', $search, 'both');
			$this->db->or_like('b.nm_lengkap', $search, 'both');
			$this->db->or_like('a.keperluan', $search, 'both');
			$this->db->or_like('a.keterangan', $search, 'both');
			$this->db->group_end();
		}

		$count_filter = $this->db->count_all_results('', false);

		$columns = array(
			0 => '',
			1 => 'a.no_doc',
			2 => 'a.tgl_doc',
			3 => 'b.nm_lengkap',
			4 => 'a.keperluan',
			5 => 'a.keterangan'
		);
		if (isset($post['order'])) {
			// Cek apakah 'order' dan 'column' ada
			if (isset($post['order'][0]['column']) && isset($post['order'][0]['dir'])) {
				$order_column = $columns[$post['order'][0]['column']]; // Ambil kolom berdasarkan indeks
				$order_dir = $post['order'][0]['dir']; // Arah pengurutan (asc/desc)

				// Menambahkan klausa ORDER BY pada query
				$this->db->order_by($order_column, $order_dir);
			}
		}
		$this->db->limit($length, $start);

		$get_data = $this->db->get()->result_array();

		$no = (0 + $start);
		$hasil = [];

		foreach ($get_data as $item) :
			$no++;

			if ($item['status'] == '0') {
				$sts = '<div class="badge bg-yellow text-light">New</div>';
			}
			if ($item['status'] == '1' || $item['status'] == '2') {
				$sts = '<div class="badge bg-dark-blue text-light">Approved</div>';
			}
			if ($item['status'] == '3') {
				$check_expense_report = $this->db->get_where('tr_expense_detail', ['id_kasbon' => $item['no_doc'], 'status' => 2])->row();
				if (!empty($check_expense_report)) {
					$sts = '<div class="badge bg-dark text-light">Close</div>';
				} else {
					$sts = '<div class="badge bg-green text-light">Paid</div>';
				}
			}
			if ($item['status'] == '9') {
				$sts = '<div class="badge bg-red text-light">Reject</div>';
			}
			if ($item['status'] == '4') {
				$sts = '<div class="badge bg-blue text-dark">Kurang</div>';
			}

			$action = '';

			if (has_permission($this->viewPermissionKasbonApp)) {
				$action .= ' <a class="btn btn-default btn-sm print" href="' . base_url("expense/kasbon_print" . $item['id']) . '" target="_blank" title="Print"><i class="fa fa-print"></i></a>';

				$action .= ' <a class="btn btn-warning btn-sm view" href="' . base_url('expense/kasbon_view/' . $item['id'] . '/_fin') . '" title="View"><i class="fa fa-eye"></i> </a>';
			}

			if (has_permission($this->managePermissionKasbonApp)) {
				if ($item['status'] == 0) {
					$action .= ' <a class="btn btn-success btn-sm approve" href="' . base_url('expense/kasbon_edit/' . $item['id'] . '/_fin') . '" title="Approve"><i class="fa fa-check-square-o"></i></a>';
				}
			}

			$nmuser = $item['nmuser'];

			$check_detail = $this->db->get_where('tr_pr_detail_kasbon', ['id_kasbon' => $item['no_doc']])->result();
			if (count($check_detail)) {
				if ($item['tipe_pr'] == 'pr departemen') {
					$this->db->select('b.nm_lengkap');
					$this->db->from('rutin_non_planning_header a');
					$this->db->join('users b', 'b.id_user = a.created_by');
					$this->db->where('a.no_pr', $item['id_pr']);
					$get_single_detail = $this->db->get()->row();

					$nmuser = $get_single_detail->nm_lengkap;
				}

				if ($item['tipe_pr'] == 'pr stok') {
					$this->db->select('b.nm_lengkap');
					$this->db->from('material_planning_base_on_produksi a');
					$this->db->join('users b', 'b.id_user = a.created_by');
					$this->db->where('a.no_pr', $item['id_pr']);
					$get_single_detail = $this->db->get()->row();

					$nmuser = $get_single_detail->nm_lengkap;
				}

				if ($item['tipe_pr'] == 'pr asset') {
					$this->db->select('b.nm_lengkap');
					$this->db->from('tran_pr_header a');
					$this->db->join('users b', 'b.id_user = a.created_by');
					$this->db->where('a.no_pr', $item['id_pr']);
					$get_single_detail = $this->db->get()->row();

					$nmuser = $get_single_detail->nm_lengkap;
				}
			}


			$hasil[] = [
				'no' => $no,
				'no_kasbon' => $item['no_doc'],
				'tanggal' => $item['tgl_doc'],
				'nama' => $nmuser,
				'keperluan' => $item['keperluan'],
				'keterangan' => $item['keterangan'],
				'status' => $sts,
				'action' => $action,
			];
		endforeach;

		$response = [
			'draw' => $draw,
			'recordsTotal' => $count_all,
			'recordsFiltered' => $count_filter,
			'data' => $hasil
		];

		$this->output->set_content_type('application/json')->set_status_header(200);

		echo json_encode($response);
	}

	public function get_dat_app_kasbon_manage()
	{
		$post = $this->input->post();

		$draw = intval($post['draw']);
		$length = $post['length'];
		$start = $post['start'];
		$search = $post['search']['value'];

		$this->db->select('a.*, b.nm_lengkap as nmuser');
		$this->db->from('tr_kasbon a');
		$this->db->join('users b', 'a.nama = b.username', 'left');
		$this->db->where('a.status', '0');
		$this->db->where('a.sts_finance', '1');

		// if ($this->auth->user_id() !== '7') {
		// 	$this->db->where('a.created_by', $this->auth->user_name());
		// }

		$count_all = $this->db->count_all_results('', false);

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('a.no_doc', $search, 'both');
			$this->db->or_like('a.tgl_doc', $search, 'both');
			$this->db->or_like('b.nm_lengkap', $search, 'both');
			$this->db->or_like('a.keperluan', $search, 'both');
			$this->db->or_like('a.keterangan', $search, 'both');
			$this->db->group_end();
		}

		$count_filter = $this->db->count_all_results('', false);

		$columns = array(
			0 => '',
			1 => 'a.no_doc',
			2 => 'a.tgl_doc',
			3 => 'b.nm_lengkap',
			4 => 'a.keperluan',
			5 => 'a.keterangan'
		);
		if (isset($post['order'])) {
			// Cek apakah 'order' dan 'column' ada
			if (isset($post['order'][0]['column']) && isset($post['order'][0]['dir'])) {
				$order_column = $columns[$post['order'][0]['column']]; // Ambil kolom berdasarkan indeks
				$order_dir = $post['order'][0]['dir']; // Arah pengurutan (asc/desc)

				// Menambahkan klausa ORDER BY pada query
				$this->db->order_by($order_column, $order_dir);
			}
		}
		$this->db->limit($length, $start);

		$get_data = $this->db->get()->result_array();

		$no = (0 + $start);
		$hasil = [];

		foreach ($get_data as $item) :
			$no++;

			if ($item['status'] == '0') {
				$sts = '<div class="badge bg-yellow text-light">New</div>';
			}
			if ($item['status'] == '1' || $item['status'] == '2') {
				$sts = '<div class="badge bg-dark-blue text-light">Approved</div>';
			}
			if ($item['status'] == '3') {
				$check_expense_report = $this->db->get_where('tr_expense_detail', ['id_kasbon' => $item['no_doc'], 'status' => 2])->row();
				if (!empty($check_expense_report)) {
					$sts = '<div class="badge bg-dark text-light">Close</div>';
				} else {
					$sts = '<div class="badge bg-green text-light">Paid</div>';
				}
			}
			if ($item['status'] == '9') {
				$sts = '<div class="badge bg-red text-light">Reject</div>';
			}
			if ($item['status'] == '4') {
				$sts = '<div class="badge bg-blue text-dark">Kurang</div>';
			}

			$action = '';

			if (has_permission($this->viewPermissionKasbonAppMan)) {
				$action .= ' <a class="btn btn-default btn-sm print" href="' . base_url("expense/kasbon_print" . $item['id']) . '" target="_blank" title="Print"><i class="fa fa-print"></i></a>';

				$action .= ' <a class="btn btn-warning btn-sm view" href="' . base_url('expense/kasbon_view/' . $item['id'] . '/_fin') . '" title="View"><i class="fa fa-eye"></i> </a>';
			}

			if (has_permission($this->managePermissionKasbonAppMan)) {
				if ($item['status'] == 0) {
					$action .= ' <a class="btn btn-success btn-sm approve" href="' . base_url('expense/kasbon_edit_manage/' . $item['id'] . '/_fin') . '" title="Approve"><i class="fa fa-check-square-o"></i></a>';
				}
			}

			$nmuser = $item['nmuser'];

			$check_detail = $this->db->get_where('tr_pr_detail_kasbon', ['id_kasbon' => $item['no_doc']])->result();
			if (count($check_detail)) {
				if ($item['tipe_pr'] == 'pr departemen') {
					$this->db->select('b.nm_lengkap');
					$this->db->from('rutin_non_planning_header a');
					$this->db->join('users b', 'b.id_user = a.created_by');
					$this->db->where('a.no_pr', $item['id_pr']);
					$get_single_detail = $this->db->get()->row();

					$nmuser = $get_single_detail->nm_lengkap;
				}

				if ($item['tipe_pr'] == 'pr stok') {
					$this->db->select('b.nm_lengkap');
					$this->db->from('material_planning_base_on_produksi a');
					$this->db->join('users b', 'b.id_user = a.created_by');
					$this->db->where('a.no_pr', $item['id_pr']);
					$get_single_detail = $this->db->get()->row();

					$nmuser = $get_single_detail->nm_lengkap;
				}

				if ($item['tipe_pr'] == 'pr asset') {
					$this->db->select('b.nm_lengkap');
					$this->db->from('tran_pr_header a');
					$this->db->join('users b', 'b.id_user = a.created_by');
					$this->db->where('a.no_pr', $item['id_pr']);
					$get_single_detail = $this->db->get()->row();

					$nmuser = $get_single_detail->nm_lengkap;
				}
			}


			$hasil[] = [
				'no' => $no,
				'no_kasbon' => $item['no_doc'],
				'tanggal' => $item['tgl_doc'],
				'nama' => $nmuser,
				'keperluan' => $item['keperluan'],
				'keterangan' => $item['keterangan'],
				'status' => $sts,
				'action' => $action,
			];
		endforeach;

		$response = [
			'draw' => $draw,
			'recordsTotal' => $count_all,
			'recordsFiltered' => $count_filter,
			'data' => $hasil
		];

		$this->output->set_content_type('application/json')->set_status_header(200);

		echo json_encode($response);
	}

	public function get_dat_expense_data()
	{
		$post = $this->input->post();

		$all = (isset($post['all']) && !empty($post['all']) && $post['all'] == '1') ? $post['all'] : '';

		$draw = intval($post['draw']);
		$length = $post['length'];
		$start = $post['start'];
		$search = $post['search']['value'];

		$this->db->select('a.*, b.nm_lengkap as nmuser, c.nm_lengkap as nmapproval');
		$this->db->from('tr_expense a');
		$this->db->join('users b', 'a.nama=b.username', 'left');
		$this->db->join('users c', 'COALESCE(NULLIF(a.approved_by, ""), a.approval) = c.username', 'left');
		if (empty($all)) {
			$this->db->group_start();
			$this->db->where('a.pettycash', null);
			$this->db->or_where('a.pettycash', '');
			$this->db->group_end();

			$this->db->group_start();
			$this->db->where('a.exp_pib', null);
			$this->db->or_where('a.exp_pib', '');
			$this->db->group_end();
		}

		// Filter dokumen invoice PO / pembayaran material (hanya tampilkan yang id_po NULL dan bukan prefix PI-)
		$this->db->group_start();
		$this->db->where('a.id_po', null);
		$this->db->or_where('a.id_po', '');
		$this->db->group_end();
		$this->db->group_start();
		$this->db->where('a.exp_inv_po', null);
		$this->db->or_where('a.exp_inv_po', '');
		$this->db->group_end();
		$this->db->not_like('a.no_doc', 'PI-', 'after');

		if ($this->auth->user_id() !== '7' && $this->auth->user_id() !== '1' && !has_permission('Expense.Manage')) {
			$this->db->group_start();
			$this->db->where('a.nama', $this->auth->user_name());
			$this->db->or_where('a.created_by', $this->auth->user_name());
			$this->db->group_end();
		}

		$this->db->order_by('a.created_on', 'DESC');

		$db_clone = clone $this->db;
		$count_all = $db_clone->count_all_results();

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('a.no_doc', $search, 'both');
			$this->db->or_like('a.tgl_doc', $search, 'both');
			$this->db->or_like('b.nm_lengkap', $search, 'both');
			$this->db->or_like('a.nama', $search, 'both');
			$this->db->or_like('a.jumlah', $search, 'both');
			$this->db->or_like('c.nm_lengkap', $search, 'both');
			$this->db->or_like('c.username', $search, 'both');
			$this->db->or_like('a.approved_by', $search, 'both');
			$this->db->or_like('a.approved_on', $search, 'both');
			$this->db->or_like('a.informasi', $search, 'both');
			$this->db->or_like('a.reject_reason', $search, 'both');
			$this->db->or_like('a.reject_reason_finance', $search, 'both');
			$this->db->or_like('a.st_reject', $search, 'both');
			$this->db->group_end();
		}

		$db_clone = clone $this->db;
		$count_filter = $db_clone->count_all_results();

		$column_order = [
			0 => '',
			1 => 'a.no_doc',
			2 => 'a.tgl_doc',
			3 => 'b.nm_lengkap',
			4 => 'a.jumlah',
			5 => 'c.nm_lengkap',
			6 => 'a.approved_on',
			7 => 'a.informasi',
			8 => 'a.status'
		];

		if (isset($post['order']) && !empty($post['order'])) {
			$column_index = $post['order'][0]['column']; // Mendapatkan index kolom yang diurutkan
			$column_name = (isset($column_order[$column_index]) && !empty($column_order[$column_index])) ? $column_order[$column_index] : 'a.created_on';
			$column_dir = $post['order'][0]['dir']; // Mendapatkan arah pengurutan (ASC/DESC)
			$this->db->order_by($column_name, $column_dir);
		} else {
			$this->db->order_by('a.created_on', 'desc');
		}

		$this->db->limit($length, $start);

		$get_data = $this->db->get()->result_array();

		$no = (0 + $start);
		$hasil = [];

		foreach ($get_data as $item) :
			$no++;

			$status = $this->_render_expense_status_badge($item);

			$action = '';

			if (has_permission($this->viewPermission)) {
				$action .= ' <a class="btn btn-sm btn-default print" href="' . base_url('expense/expense_print/' . $item['id']) . '" 
					target="expense_print" title="Print"><i class="fa fa-print"></i></a>';

				$action .= ' <a class="btn btn-sm btn-warning view" href="javascript:void(0);" title="View" 
					onclick="data_view(' . $item['id'] . ')"><i class="fa fa-eye"></i></a>';
			}

			if (has_permission($this->managePermission)) {
				if ($item['status'] == '0' || $item['status'] == '9') {
					$action .= ' <a href="javascript:void(0);" class="btn btn-sm btn-success edit" title="Edit" 
						onclick="data_edit(' . $item['id'] . ')"><i class="fa fa-edit"></i></a>';
				}
			}

			if (has_permission($this->deletePermission)) {
				if ($item['status'] == '0' || $item['status'] == '9') {
					$action .= ' <a href="javascript:void(0);" class="btn btn-sm btn-danger delete" title="Hapus" 
						onclick="data_delete(' . $item['id'] . ')"><i class="fa fa-trash"></i></a>';
				}
			}

			$approval_name = !empty($item['nmapproval']) ? $item['nmapproval'] : (!empty($item['approved_by']) ? $item['approved_by'] : (!empty($item['approval']) ? $item['approval'] : '-'));

			$hasil[] = [
				'no' => $no,
				'no_doc' => $item['no_doc'],
				'tgl_doc' => $item['tgl_doc'],
				'nama' => !empty($item['nmuser']) ? $item['nmuser'] : $item['nama'],
				'total_realisasi' => number_format($item['jumlah']),
				'approval' => $approval_name,
				'approval_date' => (!empty($item['approved_on']) && $item['approved_on'] != '0000-00-00 00:00:00') ? $item['approved_on'] : '-',
				'keterangan' => $item['informasi'],
				'status' => $status,
				'action' => $action
			];
		endforeach;


		$response = [
			'draw' => $draw,
			'recordsTotal' => $count_all,
			'recordsFiltered' => $count_filter,
			'data' => $hasil
		];

		echo json_encode($response);
	}

	public function get_dat_expense_pc()
	{
		$post = $this->input->post();

		$draw = intval($post['draw']);
		$length = $post['length'];
		$start = $post['start'];
		$search = $post['search']['value'];

		$this->db->select('a.*, b.nm_lengkap as nmuser, c.nm_lengkap as nmapproval');
		$this->db->from('tr_expense a');
		$this->db->join('users b', 'a.nama=b.username', 'left');
		$this->db->join('users c', 'COALESCE(NULLIF(a.approved_by, ""), a.approval) = c.username', 'left');
		$this->db->where('a.pettycash IS NOT NULL');
		$this->db->where('a.exp_pib', null);
		$this->db->group_start();
		$this->db->where('a.id_po', null);
		$this->db->or_where('a.id_po', '');
		$this->db->group_end();
		$this->db->not_like('a.no_doc', 'PI-', 'after');

		if ($this->auth->user_id() !== '7') {
			$this->db->where('a.nama', $this->auth->user_name());
		}

		$db_clone = clone $this->db;
		$count_all = $db_clone->count_all_results();

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('a.no_doc', $search, 'both');
			$this->db->or_like('a.tgl_doc', $search, 'both');
			$this->db->or_like('b.nm_lengkap', $search, 'both');
			$this->db->or_like('a.nama', $search, 'both');
			$this->db->or_like('c.nm_lengkap', $search, 'both');
			$this->db->or_like('c.username', $search, 'both');
			$this->db->or_like('a.approved_by', $search, 'both');
			$this->db->or_like('a.informasi', $search, 'both');
			$this->db->or_like('a.reject_reason', $search, 'both');
			$this->db->or_like('a.reject_reason_finance', $search, 'both');
			$this->db->or_like('a.st_reject', $search, 'both');
			$this->db->group_end();
		}

		$db_clone = clone $this->db;
		$count_filter = $db_clone->count_all_results();

		$this->db->order_by('a.created_on', 'desc');

		$this->db->limit($length, $start);

		$get_data = $this->db->get()->result_array();

		$no = (0 + $start);
		$hasil = [];

		foreach ($get_data as $item) :
			$no++;

			$status = $this->_render_expense_status_badge($item);

			$action = '';

			if (has_permission($this->viewPermission)) {
				$action .= ' <a class="btn btn-sm btn-default print" href="' . base_url('expense/expense_print/' . $item['id']) . '" 
					target="expense_print" title="Print"><i class="fa fa-print"></i></a>';

				$action .= ' <a class="btn btn-sm btn-warning view" href="javascript:void(0);"
				   	title="View" onclick="data_view(' . $item['id'] . ')"><i class="fa fa-eye"></i></a>';
			}

			if (has_permission($this->managePermission)) {
				if ($item['status'] == '0' || $item['status'] == '9') {
					$action .= ' <a class="btn btn-success btn-sm edit" href="javascript:void(0)" title="Edit" onclick="data_edit(' . $item['id'] . ')"><i class="fa fa-edit"></i></a>';
				}
			}

			if (has_permission($this->deletePermission)) {
				if ($item['status'] == '0' || $item['status'] == '9') {
					$action .= ' <a class="btn btn-danger btn-sm delete" href="javascript:void(0)" title="Hapus" onclick="data_delete(' . $item['id'] . ')"><i class="fa fa-trash"></i></a>';
				}
			}

			$approval_name = !empty($item['nmapproval']) ? $item['nmapproval'] : (!empty($item['approved_by']) ? $item['approved_by'] : (!empty($item['approval']) ? $item['approval'] : '-'));

			$hasil[] = [
				'no' => $no,
				'no_doc' => $item['no_doc'],
				'tgl_doc' => $item['tgl_doc'],
				'nama' => !empty($item['nmuser']) ? $item['nmuser'] : $item['nama'],
				'approval' => $approval_name,
				'keterangan' => $item['informasi'],
				'nominal' => number_format($item['jumlah']),
				'status' => $status,
				'action' => $action
			];
		endforeach;


		$response = [
			'draw' => $draw,
			'recordsTotal' => $count_all,
			'recordsFiltered' => $count_filter,
			'data' => $hasil
		];

		echo json_encode($response);
	}

	public function get_dat_approval_expense()
	{
		$post = $this->input->post();

		$draw = intval($post['draw']);
		$length = $post['length'];
		$start = $post['start'];
		$search = $post['search']['value'];

		$this->db->select('a.*, IF(SUM(b.total_harga) IS NULL, 0, SUM(b.total_harga)) as nominal, c.username as nmuser, d.username as nmapproval');
		$this->db->from('tr_expense a');
		$this->db->join('tr_expense_detail b', 'b.no_doc = a.no_doc', 'left');
		$this->db->join('users c', 'a.nama=c.username', 'left');
		$this->db->join('users d', 'a.approval=d.username', 'left');
		$this->db->where('a.status', 0);
		$this->db->where('a.sts_finance IS NULL');
		$this->db->where('b.id_kasbon IS NULL');
		$this->db->group_start();
		$this->db->where('a.id_po', null);
		$this->db->or_where('a.id_po', '');
		$this->db->group_end();
		$this->db->not_like('a.no_doc', 'PI-', 'after');
		$this->db->group_by('a.no_doc');

		$count_all = $this->db->count_all_results();

		$this->db->select('a.*, IF(SUM(b.total_harga) IS NULL, 0, SUM(b.total_harga)) as nominal, c.username as nmuser, d.username as nmapproval');
		$this->db->from('tr_expense a');
		$this->db->join('tr_expense_detail b', 'b.no_doc = a.no_doc', 'left');
		$this->db->join('users c', 'a.nama=c.username', 'left');
		$this->db->join('users d', 'a.approval=d.username', 'left');
		$this->db->where('a.status', 0);
		$this->db->where('b.id_kasbon IS NULL');
		$this->db->group_start();
		$this->db->where('a.id_po', null);
		$this->db->or_where('a.id_po', '');
		$this->db->group_end();
		$this->db->not_like('a.no_doc', 'PI-', 'after');

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('a.no_doc', $search, 'both');
			$this->db->or_like('a.tgl_doc', $search, 'both');
			$this->db->or_like('c.username', $search, 'both');
			$this->db->or_like('d.username', $search, 'both');
			$this->db->or_like('a.informasi', $search, 'both');
			$this->db->or_like('a.jumlah', $search, 'both');
			$this->db->group_end();
		}
		$this->db->group_by('a.no_doc');

		$count_filter = $this->db->count_all_results();


		$this->db->select('a.*, IF(SUM(b.total_harga) IS NULL, 0, SUM(b.total_harga)) as nominal, c.username as nmuser, d.username as nmapproval');
		$this->db->from('tr_expense a');
		$this->db->join('tr_expense_detail b', 'b.no_doc = a.no_doc', 'left');
		$this->db->join('users c', 'a.nama=c.username', 'left');
		$this->db->join('users d', 'a.approval=d.username', 'left');
		$this->db->where('a.status', 0);
		$this->db->where('b.id_kasbon IS NULL');
		$this->db->group_start();
		$this->db->where('a.id_po', null);
		$this->db->or_where('a.id_po', '');
		$this->db->group_end();
		$this->db->not_like('a.no_doc', 'PI-', 'after');

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('a.no_doc', $search, 'both');
			$this->db->or_like('a.tgl_doc', $search, 'both');
			$this->db->or_like('c.username', $search, 'both');
			$this->db->or_like('a.informasi', $search, 'both');
			$this->db->or_like('a.jumlah', $search, 'both');
			$this->db->group_end();
		}
		$this->db->group_by('a.no_doc');

		$column_order = [
			0 => '',
			1 => 'a.no_doc',
			2 => 'a.tgl_doc',
			3 => 'c.username',
			4 => 'a.informasi',
			5 => 'a.jumlah'
		];

		if (isset($post['order']) && !empty($post['order'])) {
			$column_index = $post['order'][0]['column']; // Mendapatkan index kolom yang diurutkan
			$column_name = $column_order[$column_index]; // Menentukan nama kolom berdasarkan index
			$column_dir = $post['order'][0]['dir']; // Mendapatkan arah pengurutan (ASC/DESC)
			$this->db->order_by($column_name, $column_dir);
		} else {
			$this->db->order_by('a.created_on', 'desc');
		}

		$this->db->limit($length, $start);

		$get_data = $this->db->get()->result();
	}

	public function get_dat_approval_expense_manage()
	{
		$post   = $this->input->post();
		$draw   = isset($post['draw'])   ? (int) $post['draw']   : 1;
		$length = isset($post['length']) ? (int) $post['length'] : 10;
		$start  = isset($post['start'])  ? (int) $post['start']  : 0;
		$search = isset($post['search']['value']) ? trim($post['search']['value']) : '';

		$status_label = array(
			"0" => "Baru",
			"1" => "Disetujui",
			"2" => "Disetujui Management",
			"3" => "Selesai",
			"9" => "Ditolak"
		);

		$column_order = array(
			1 => 'a.no_doc',
			2 => 'a.tgl_doc',
			3 => 'c.username',
			4 => 'a.informasi',
			5 => 'nominal',
		);

		// Total semua record
		$this->db->select('COUNT(DISTINCT a.no_doc) as cnt');
		$this->db->from('tr_expense a');
		$this->db->join('tr_expense_detail b', 'b.no_doc = a.no_doc', 'left');
		$this->db->join('users c', 'a.nama = c.username', 'left');
		$this->db->where('(a.status = 1 OR (a.status = 0 AND a.sts_finance = "1"))', null, false);
		$this->db->group_start();
		$this->db->where('a.id_po', null);
		$this->db->or_where('a.id_po', '');
		$this->db->group_end();
		$this->db->not_like('a.no_doc', 'PI-', 'after');
		$count_all = (int) $this->db->get()->row()->cnt;

		// Total setelah filter
		$this->db->select('COUNT(DISTINCT a.no_doc) as cnt');
		$this->db->from('tr_expense a');
		$this->db->join('tr_expense_detail b', 'b.no_doc = a.no_doc', 'left');
		$this->db->join('users c', 'a.nama = c.username', 'left');
		$this->db->where('(a.status = 1 OR (a.status = 0 AND a.sts_finance = "1"))', null, false);
		$this->db->group_start();
		$this->db->where('a.id_po', null);
		$this->db->or_where('a.id_po', '');
		$this->db->group_end();
		$this->db->not_like('a.no_doc', 'PI-', 'after');
		if ($search !== '') {
			$this->db->group_start();
			$this->db->like('a.no_doc', $search, 'both');
			$this->db->or_like('a.tgl_doc', $search, 'both');
			$this->db->or_like('c.username', $search, 'both');
			$this->db->or_like('a.informasi', $search, 'both');
			$this->db->group_end();
		}
		$count_filter = (int) $this->db->get()->row()->cnt;

		// Data aktual
		$this->db->select('a.id, a.no_doc, a.tgl_doc, a.informasi, a.status, a.sts_finance, a.reject_reason, a.reject_reason_finance, a.st_reject,
			COALESCE(NULLIF(a.jumlah, 0), SUM(b.total_harga), 0) as nominal,
			c.username as nmuser');
		$this->db->from('tr_expense a');
		$this->db->join('tr_expense_detail b', 'b.no_doc = a.no_doc', 'left');
		$this->db->join('users c', 'a.nama = c.username', 'left');
		$this->db->where('(a.status = 1 OR (a.status = 0 AND a.sts_finance = "1"))', null, false);
		$this->db->group_start();
		$this->db->where('a.id_po', null);
		$this->db->or_where('a.id_po', '');
		$this->db->group_end();
		$this->db->not_like('a.no_doc', 'PI-', 'after');
		if ($search !== '') {
			$this->db->group_start();
			$this->db->like('a.no_doc', $search, 'both');
			$this->db->or_like('a.tgl_doc', $search, 'both');
			$this->db->or_like('c.username', $search, 'both');
			$this->db->or_like('a.informasi', $search, 'both');
			$this->db->group_end();
		}
		$this->db->group_by('a.no_doc');

		if (isset($post['order'][0])) {
			$col_idx = (int) $post['order'][0]['column'];
			$col_dir = $post['order'][0]['dir'] === 'asc' ? 'ASC' : 'DESC';
			if (isset($column_order[$col_idx])) {
				$this->db->order_by($column_order[$col_idx], $col_dir);
			}
		} else {
			$this->db->order_by('a.id', 'DESC');
		}

		$this->db->limit($length > 0 ? $length : 10, $start);
		$rows = $this->db->get()->result();

		$data = array();
		$no   = $start + 1;
		foreach ($rows as $row) {
			$action = '';
			if (has_permission('Approval Expense Management.View')) {
				$action .= '<a class="btn btn-warning btn-sm" href="javascript:void(0)" title="View" onclick="data_approve(\'' . $row->id . '\')"><i class="fa fa-eye"></i></a> ';
			}
			if (has_permission('Approval Expense Management.Manage') && $row->status == 0) {
				$action .= '<a class="btn btn-success btn-sm" href="javascript:void(0)" title="Approve" onclick="data_approve(\'' . $row->id . '\')"><i class="fa fa-check-square-o"></i></a>';
			}
			$data[] = array(
				'no'        => $no++,
				'no_doc'    => $row->no_doc,
				'tgl_doc'   => $row->tgl_doc,
				'nmuser'    => $row->nmuser,
				'informasi' => $row->informasi,
				'nominal'   => number_format($row->nominal),
				'status'    => $this->_render_expense_status_badge($row),
				'action'    => $action,
			);
		}

		echo json_encode(array(
			'draw'            => $draw,
			'recordsTotal'    => $count_all,
			'recordsFiltered' => $count_filter,
			'data'            => $data,
		));
	}

	public function get_expense_app_finance()
	{
		$draw    = (int) $this->input->get('draw', true);
		$length  = (int) $this->input->get('length', true);
		$start   = (int) $this->input->get('start', true);
		$search  = $this->input->get('search', true);
		$keyword = (!empty($search['value'])) ? trim($search['value']) : '';

		$status_label = array(
			"0" => "Waiting Approval Finance",
			"1" => "Approved",
			"2" => "Approved",
			"3" => "Approved",
			"9" => "Rejected"
		);

		// Total semua record (tanpa filter)
		$this->db->select('COUNT(DISTINCT a.no_doc) as cnt');
		$this->db->from('tr_expense a');
		$this->db->join('tr_expense_detail b', 'b.no_doc = a.no_doc', 'left');
		$this->db->join('users c', 'a.nama = c.username', 'left');
		$this->db->where('a.status', 0);
		$this->db->where('(a.sts_finance = "0" OR a.sts_finance IS NULL)', null, false);
		$this->db->group_start();
		$this->db->where('a.id_po', null);
		$this->db->or_where('a.id_po', '');
		$this->db->group_end();
		$this->db->not_like('a.no_doc', 'PI-', 'after');
		$count_all = (int) $this->db->get()->row()->cnt;

		// Total setelah filter search
		$this->db->select('COUNT(DISTINCT a.no_doc) as cnt');
		$this->db->from('tr_expense a');
		$this->db->join('tr_expense_detail b', 'b.no_doc = a.no_doc', 'left');
		$this->db->join('users c', 'a.nama = c.username', 'left');
		$this->db->where('a.status', 0);
		$this->db->where('(a.sts_finance = "0" OR a.sts_finance IS NULL)', null, false);
		$this->db->group_start();
		$this->db->where('a.id_po', null);
		$this->db->or_where('a.id_po', '');
		$this->db->group_end();
		$this->db->not_like('a.no_doc', 'PI-', 'after');
		if ($keyword !== '') {
			$this->db->group_start();
			$this->db->like('a.no_doc', $keyword);
			$this->db->or_like('a.tgl_doc', $keyword);
			$this->db->or_like('c.username', $keyword);
			$this->db->or_like('a.informasi', $keyword);
			$this->db->group_end();
		}
		$count_filtered = (int) $this->db->get()->row()->cnt;

		// Data aktual dengan limit & offset
		$this->db->select('a.id, a.no_doc, a.tgl_doc, a.informasi, a.status, a.sts_finance, a.reject_reason, a.reject_reason_finance, a.st_reject,
			COALESCE(NULLIF(a.jumlah, 0), SUM(b.total_harga), 0) as nominal,
			c.username as nmuser');
		$this->db->from('tr_expense a');
		$this->db->join('tr_expense_detail b', 'b.no_doc = a.no_doc', 'left');
		$this->db->join('users c', 'a.nama = c.username', 'left');
		$this->db->where('a.status', 0);
		$this->db->where('(a.sts_finance = "0" OR a.sts_finance IS NULL)', null, false);
		$this->db->group_start();
		$this->db->where('a.id_po', null);
		$this->db->or_where('a.id_po', '');
		$this->db->group_end();
		$this->db->not_like('a.no_doc', 'PI-', 'after');
		if ($keyword !== '') {
			$this->db->group_start();
			$this->db->like('a.no_doc', $keyword);
			$this->db->or_like('a.tgl_doc', $keyword);
			$this->db->or_like('c.username', $keyword);
			$this->db->or_like('a.informasi', $keyword);
			$this->db->group_end();
		}
		$this->db->group_by('a.no_doc');
		$this->db->order_by('a.id', 'DESC');
		$this->db->limit($length > 0 ? $length : 10, $start);
		$rows = $this->db->get()->result();

		$data = array();
		$no   = $start + 1;
		foreach ($rows as $row) {
			$action = '';
			if (has_permission('Expense_Approval.View')) {
				$action .= '<a class="btn btn-warning btn-sm" href="javascript:void(0)" title="View" onclick="data_approve(\'' . $row->id . '\')"><i class="fa fa-eye"></i></a> ';
			}
			if (has_permission('Expense_Approval.Manage') && $row->status == 0) {
				$action .= '<a class="btn btn-success btn-sm" href="javascript:void(0)" title="Approve" onclick="data_approve(\'' . $row->id . '\')"><i class="fa fa-check-square-o"></i></a>';
			}
			$data[] = array(
				'no'        => $no++,
				'no_doc'    => $row->no_doc,
				'tgl_doc'   => $row->tgl_doc,
				'nmuser'    => $row->nmuser,
				'informasi' => $row->informasi,
				'nominal'   => number_format($row->nominal),
				'status'    => $this->_render_expense_status_badge($row),
				'action'    => $action,
			);
		}

		echo json_encode(array(
			'draw'            => $draw,
			'recordsTotal'    => $count_all,
			'recordsFiltered' => $count_filtered,
			'data'            => $data,
		));
	}

	public function set_jurnal_expense()
	{
		$post = $this->input->post();
		$tgl_doc = !empty($post['tgl_doc']) ? date('d F Y', strtotime($post['tgl_doc'])) : date('d F Y');
		$tgl_jurnal_val = !empty($post['tgl_doc']) ? date('Y-m-d', strtotime($post['tgl_doc'])) : date('Y-m-d');
		$no_doc = !empty($post['no_doc']) ? $post['no_doc'] : '';
		
		$total_expense = isset($post['total_expense']) ? floatval(str_replace(',', '', $post['total_expense'])) : 0;
		$total_kasbon = isset($post['total_kasbon']) ? floatval(str_replace(',', '', $post['total_kasbon'])) : 0;
		$selisih = $total_kasbon - $total_expense;
		$is_direct_expense = !empty($post['is_direct_expense']) || ($total_kasbon <= 0);

		$hasil_jurnal = '';
		$ttl_debit = 0;
		$ttl_kredit = 0;
		$no_jurnal = 0;

		// Jika tidak ada kasbon dan expense, tidak perlu generate jurnal
		if ($total_kasbon <= 0 && $total_expense <= 0) {
			echo json_encode([
				'status' => 1,
				'hasil' => '',
				'ttl_debit' => '0',
				'ttl_kredit' => '0'
			]);
			return;
		}

		$arr_id_kasbon = isset($post['id_kasbon']) ? (array)$post['id_kasbon'] : [];
		$arr_coa = isset($post['coa']) ? (array)$post['coa'] : [];
		$arr_deskripsi = isset($post['deskripsi']) ? (array)$post['deskripsi'] : [];
		$arr_expense = isset($post['expense']) ? (array)$post['expense'] : [];
		$arr_kasbon = isset($post['kasbon']) ? (array)$post['kasbon'] : [];
		$arr_tanggal = isset($post['tanggal']) ? (array)$post['tanggal'] : [];

		// Tarik Nama Company dari database db_consultant_new (kons_tr_company)
		$id_company = '';
		$nm_company = '';
		$first_kasbon_doc = '';
		foreach ($arr_id_kasbon as $kb_item) {
			if (!empty($kb_item)) {
				$first_kasbon_doc = $kb_item;
				break;
			}
		}

		try {
			if (isset($this->load) && is_object($this->load) && method_exists($this->load, 'database')) {
				$consultant = $this->load->database('consultant', true);
				if (!empty($first_kasbon_doc)) {
					$get_kb = $this->db->get_where('tr_kasbon', ['no_doc' => $first_kasbon_doc])->row();
					if (!empty($get_kb) && !empty($get_kb->project)) {
						$consultant->select('a.id, a.nm_company');
						$consultant->from('kons_tr_company a');
						$consultant->join('kons_tr_penawaran b', 'b.company = a.id', 'left');
						$consultant->where('b.id_quotation', $get_kb->project);
						$get_comp = $consultant->get()->row();
						if (!empty($get_comp)) {
							$id_company = $get_comp->id;
							$nm_company = $get_comp->nm_company;
						}
					}
				}

				if (empty($nm_company)) {
					$get_first_comp = $consultant->get('kons_tr_company')->row();
					if (!empty($get_first_comp)) {
						$id_company = $get_first_comp->id;
						$nm_company = $get_first_comp->nm_company;
					}
				}
			}
		} catch (Throwable $e) {
			$id_company = '';
			$nm_company = '';
		}

		// 1. DEBIT: Setiap baris item Realisasi Expense yang diinput di form
		if (!empty($arr_expense)) {
			foreach ($arr_expense as $idx => $exp_val) {
				$exp_num = floatval(str_replace(',', '', $exp_val));
				if ($exp_num > 0) {
					$no_jurnal++;
					$coa_code = isset($arr_coa[$idx]) && !empty($arr_coa[$idx]) ? $arr_coa[$idx] : '1304-01-01';
					$coa_name = 'Peralatan Kantor';
					$q_coa_acc = $this->db->query("SELECT nama FROM " . DBACC . ".coa_master WHERE no_perkiraan = '" . $coa_code . "'")->row();
					if (!empty($q_coa_acc)) {
						$coa_name = $q_coa_acc->nama;
					} else {
						$coa_name = ($coa_code == '1304-01-01') ? 'Peralatan Kantor' : 'Biaya Pengeluaran';
					}

					$desk = isset($arr_deskripsi[$idx]) && !empty($arr_deskripsi[$idx]) ? $arr_deskripsi[$idx] : 'Pengeluaran Expense';
					$k_doc = isset($arr_id_kasbon[$idx]) ? $arr_id_kasbon[$idx] : '';
					if (!empty($k_doc)) {
						$desk = 'Pengeluaran Kasbon ' . $k_doc . ' - ' . $desk;
					}

					$hasil_jurnal .= '<tr>';
					$hasil_jurnal .= '<td class="text-center">' . $tgl_doc . '<input type="hidden" name="jurnal[' . $no_jurnal . '][tgl_jurnal]" value="' . $tgl_jurnal_val . '"></td>';
					$hasil_jurnal .= '<td class="text-center">' . $coa_code . '<input type="hidden" name="jurnal[' . $no_jurnal . '][coa]" value="' . $coa_code . '"></td>';
					$hasil_jurnal .= '<td class="text-center">' . $nm_company . '<input type="hidden" name="jurnal[' . $no_jurnal . '][id_company]" value="' . $id_company . '"><input type="hidden" name="jurnal[' . $no_jurnal . '][nm_company]" value="' . $nm_company . '"></td>';
					$hasil_jurnal .= '<td>' . $coa_name . '<input type="hidden" name="jurnal[' . $no_jurnal . '][nm_coa]" value="' . $coa_name . '"></td>';
					$hasil_jurnal .= '<td>' . htmlspecialchars($desk) . '<input type="hidden" name="jurnal[' . $no_jurnal . '][deskripsi]" value="' . htmlspecialchars($desk) . '"></td>';
					$hasil_jurnal .= '<td class="text-right">' . number_format($exp_num) . '<input type="hidden" name="jurnal[' . $no_jurnal . '][debit]" value="' . $exp_num . '"></td>';
					if (!$is_direct_expense) {
						$hasil_jurnal .= '<td class="text-right">0<input type="hidden" name="jurnal[' . $no_jurnal . '][kredit]" value="0"></td>';
					}
					$hasil_jurnal .= '</tr>';

					$ttl_debit += $exp_num;
				}
			}
		}

		// Jika ttl_debit masih 0 tapi total_expense > 0, fallback
		if ($ttl_debit == 0 && $total_expense > 0) {
			$no_jurnal++;
			$coa_code = '1304-01-01';
			$coa_name = 'Peralatan Kantor';
			$q_coa_acc = $this->db->query("SELECT nama FROM " . DBACC . ".coa_master WHERE no_perkiraan = '" . $coa_code . "'")->row();
			if (!empty($q_coa_acc)) $coa_name = $q_coa_acc->nama;

			$hasil_jurnal .= '<tr>';
			$hasil_jurnal .= '<td class="text-center">' . $tgl_doc . '<input type="hidden" name="jurnal[' . $no_jurnal . '][tgl_jurnal]" value="' . $tgl_jurnal_val . '"></td>';
			$hasil_jurnal .= '<td class="text-center">' . $coa_code . '<input type="hidden" name="jurnal[' . $no_jurnal . '][coa]" value="' . $coa_code . '"></td>';
			$hasil_jurnal .= '<td class="text-center">' . $nm_company . '<input type="hidden" name="jurnal[' . $no_jurnal . '][id_company]" value="' . $id_company . '"><input type="hidden" name="jurnal[' . $no_jurnal . '][nm_company]" value="' . $nm_company . '"></td>';
			$hasil_jurnal .= '<td>' . $coa_name . '<input type="hidden" name="jurnal[' . $no_jurnal . '][nm_coa]" value="' . $coa_name . '"></td>';
			$hasil_jurnal .= '<td>Realisasi Pengeluaran Expense</td>';
			$hasil_jurnal .= '<td class="text-right">' . number_format($total_expense) . '<input type="hidden" name="jurnal[' . $no_jurnal . '][debit]" value="' . $total_expense . '"></td>';
			if (!$is_direct_expense) {
				$hasil_jurnal .= '<td class="text-right">0<input type="hidden" name="jurnal[' . $no_jurnal . '][kredit]" value="0"></td>';
			}
			$hasil_jurnal .= '</tr>';

			$ttl_debit += $total_expense;
		}

		// Jika LEBIH KASBON (Kasbon > Expense / Selisih > 0): Sisi DEBIT Piutang Kelebihan Kasbon (1103-01-15)
		if ($selisih > 0) {
			$no_jurnal++;
			$coa_piutang = '1103-01-15';
			$nm_piutang = 'Piutang Kelebihan Kasbon';
			$q_coa_acc = $this->db->query("SELECT nama FROM " . DBACC . ".coa_master WHERE no_perkiraan = '" . $coa_piutang . "'")->row();
			if (!empty($q_coa_acc)) $nm_piutang = $q_coa_acc->nama;

			$hasil_jurnal .= '<tr style="background:#e8fadf;">';
			$hasil_jurnal .= '<td class="text-center">' . $tgl_doc . '<input type="hidden" name="jurnal[' . $no_jurnal . '][tgl_jurnal]" value="' . $tgl_jurnal_val . '"></td>';
			$hasil_jurnal .= '<td class="text-center"><b>' . $coa_piutang . '</b><input type="hidden" name="jurnal[' . $no_jurnal . '][coa]" value="' . $coa_piutang . '"></td>';
			$hasil_jurnal .= '<td class="text-center"><b>' . $nm_company . '</b><input type="hidden" name="jurnal[' . $no_jurnal . '][id_company]" value="' . $id_company . '"><input type="hidden" name="jurnal[' . $no_jurnal . '][nm_company]" value="' . $nm_company . '"></td>';
			$hasil_jurnal .= '<td><b>' . $nm_piutang . '</b><input type="hidden" name="jurnal[' . $no_jurnal . '][nm_coa]" value="' . $nm_piutang . '"></td>';
			$hasil_jurnal .= '<td><span class="text-green"><i class="fa fa-reply"></i> Piutang Kelebihan Kasbon (Menunggu Pengembalian ke Rekening Perusahaan)</span><input type="hidden" name="jurnal[' . $no_jurnal . '][deskripsi]" value="Piutang Kelebihan Kasbon"></td>';
			$hasil_jurnal .= '<td class="text-right"><b>' . number_format($selisih) . '</b><input type="hidden" name="jurnal[' . $no_jurnal . '][debit]" value="' . $selisih . '"></td>';
			$hasil_jurnal .= '<td class="text-right">0<input type="hidden" name="jurnal[' . $no_jurnal . '][kredit]" value="0"></td>';
			$hasil_jurnal .= '</tr>';

			$ttl_debit += $selisih;
		}

		// 2. KREDIT: Akun Kasbon / Uang Muka Karyawan (1103-01-14)
		if ($total_kasbon > 0) {
			$no_jurnal++;
			$coa_kasbon = '1103-01-14';
			$nm_kasbon = 'Piutang Lain-lain Konsultan';
			$q_coa_acc = $this->db->query("SELECT nama FROM " . DBACC . ".coa_master WHERE no_perkiraan = '" . $coa_kasbon . "'")->row();
			if (!empty($q_coa_acc)) $nm_kasbon = $q_coa_acc->nama;

			$deskripsi_kasbon = 'Pertanggungjawaban Kasbon' . (!empty($arr_id_kasbon) ? ' (' . implode(', ', array_unique(array_filter($arr_id_kasbon))) . ')' : '');

			$hasil_jurnal .= '<tr>';
			$hasil_jurnal .= '<td class="text-center">' . $tgl_doc . '<input type="hidden" name="jurnal[' . $no_jurnal . '][tgl_jurnal]" value="' . $tgl_jurnal_val . '"></td>';
			$hasil_jurnal .= '<td class="text-center">' . $coa_kasbon . '<input type="hidden" name="jurnal[' . $no_jurnal . '][coa]" value="' . $coa_kasbon . '"></td>';
			$hasil_jurnal .= '<td class="text-center">' . $nm_company . '<input type="hidden" name="jurnal[' . $no_jurnal . '][id_company]" value="' . $id_company . '"><input type="hidden" name="jurnal[' . $no_jurnal . '][nm_company]" value="' . $nm_company . '"></td>';
			$hasil_jurnal .= '<td>' . $nm_kasbon . '<input type="hidden" name="jurnal[' . $no_jurnal . '][nm_coa]" value="' . $nm_kasbon . '"></td>';
			$hasil_jurnal .= '<td>' . $deskripsi_kasbon . '<input type="hidden" name="jurnal[' . $no_jurnal . '][deskripsi]" value="' . $deskripsi_kasbon . '"></td>';
			$hasil_jurnal .= '<td class="text-right">0<input type="hidden" name="jurnal[' . $no_jurnal . '][debit]" value="0"></td>';
			$hasil_jurnal .= '<td class="text-right">' . number_format($total_kasbon) . '<input type="hidden" name="jurnal[' . $no_jurnal . '][kredit]" value="' . $total_kasbon . '"></td>';
			$hasil_jurnal .= '</tr>';

			$ttl_kredit += $total_kasbon;
		}

		// 3. Sisi KREDIT: Hutang Expense / Reimburse Karyawan (9999-99-99)
		// HANYA untuk Expense Report jika LEBIH EXPENSE (ada kasbon & selisih < 0).
		// Untuk Expense Biasa (Direct Expense tanpa kasbon), tidak ada akun perantara Hutang Expense.
		if ($selisih < 0 && $total_kasbon > 0) {
			$kurang_bayar = abs($selisih);
			$no_jurnal++;
			$coa_hutang = '9999-99-99';
			$nm_hutang = 'Hutang Expense / Reimburse Karyawan';
			$q_coa_acc = $this->db->query("SELECT nama FROM " . DBACC . ".coa_master WHERE no_perkiraan = '" . $coa_hutang . "'")->row();
			if (!empty($q_coa_acc)) $nm_hutang = $q_coa_acc->nama;

			$hasil_jurnal .= '<tr style="background:#fde8e8;">';
			$hasil_jurnal .= '<td class="text-center">' . $tgl_doc . '<input type="hidden" name="jurnal[' . $no_jurnal . '][tgl_jurnal]" value="' . $tgl_jurnal_val . '"></td>';
			$hasil_jurnal .= '<td class="text-center"><b>' . $coa_hutang . '</b><input type="hidden" name="jurnal[' . $no_jurnal . '][coa]" value="' . $coa_hutang . '"></td>';
			$hasil_jurnal .= '<td class="text-center"><b>' . $nm_company . '</b><input type="hidden" name="jurnal[' . $no_jurnal . '][id_company]" value="' . $id_company . '"><input type="hidden" name="jurnal[' . $no_jurnal . '][nm_company]" value="' . $nm_company . '"></td>';
			$hasil_jurnal .= '<td><b>' . $nm_hutang . '</b><input type="hidden" name="jurnal[' . $no_jurnal . '][nm_coa]" value="' . $nm_hutang . '"></td>';
			$hasil_jurnal .= '<td><span class="text-red"><i class="fa fa-exclamation-circle"></i> Lebih Expense (Reimburse Kantor ke Karyawan)</span><input type="hidden" name="jurnal[' . $no_jurnal . '][deskripsi]" value="Lebih Expense Reimburse"></td>';
			$hasil_jurnal .= '<td class="text-right">0<input type="hidden" name="jurnal[' . $no_jurnal . '][debit]" value="0"></td>';
			$hasil_jurnal .= '<td class="text-right"><b>' . number_format($kurang_bayar) . '</b><input type="hidden" name="jurnal[' . $no_jurnal . '][kredit]" value="' . $kurang_bayar . '"></td>';
			$hasil_jurnal .= '</tr>';

			$ttl_kredit += $kurang_bayar;
		}

		echo json_encode([
			'status' => 1,
			'hasil' => $hasil_jurnal,
			'ttl_debit' => number_format($ttl_debit),
			'ttl_kredit' => number_format($ttl_kredit)
		]);
	}


	/**
	 * Helper untuk menyimpan multiple file evidence per baris detail expense ke tabel tr_expense_detail_file
	 */
	private function _handle_detail_files($no_doc, $id_detail, $row_key, $existing_kept_ids = [])
	{
		if (!$this->db->table_exists('tr_expense_detail_file')) {
			return;
		}

		if (empty($no_doc) || empty($id_detail)) {
			return;
		}

		// 1. Hapus file existing yang dihapus user di UI
		if (!empty($existing_kept_ids) && is_array($existing_kept_ids)) {
			$this->db->where('no_doc', $no_doc);
			$this->db->where('id_detail', $id_detail);
			$this->db->where_not_in('id', $existing_kept_ids);
			$this->db->delete('tr_expense_detail_file');
		} else if (isset($_POST['has_existing_files_' . $row_key]) && empty($existing_kept_ids)) {
			$this->db->delete('tr_expense_detail_file', ['no_doc' => $no_doc, 'id_detail' => $id_detail]);
		}

		// 2. Upload file-file baru untuk baris ini
		if (!empty($_FILES['doc_files_' . $row_key]['name'])) {
			$files = $_FILES['doc_files_' . $row_key];
			$count = is_array($files['name']) ? count($files['name']) : 0;

			$config['upload_path']   = './assets/expense/';
			$config['allowed_types'] = '*';
			$config['remove_spaces'] = TRUE;
			$config['encrypt_name']  = TRUE;
			$this->load->library('upload', $config);

			for ($i = 0; $i < $count; $i++) {
				if (!empty($files['tmp_name'][$i])) {
					$_FILES['single_dtl_file']['name']     = $files['name'][$i];
					$_FILES['single_dtl_file']['type']     = $files['type'][$i];
					$_FILES['single_dtl_file']['tmp_name'] = $files['tmp_name'][$i];
					$_FILES['single_dtl_file']['error']    = $files['error'][$i];
					$_FILES['single_dtl_file']['size']     = $files['size'][$i];

					$this->upload->initialize($config);
					if ($this->upload->do_upload('single_dtl_file')) {
						$uData = $this->upload->data();
						$this->db->insert('tr_expense_detail_file', [
							'no_doc'     => $no_doc,
							'id_detail'  => $id_detail,
							'doc_file'   => $uData['file_name'],
							'created_by' => $this->auth->user_name(),
							'created_at' => date('Y-m-d H:i:s')
						]);
					}
				}
			}
		}
	}

	/**
	 * Posting otomatis ke tr_jurnal saat Expense Report di-approve final (Lebih Expense atau Impas)
	 */
	private function _post_jurnal_expense_report($id)
	{
		$data = $this->db->get_where('tr_expense', ['id' => $id])->row();
		if (empty($data)) {
			return;
		}

		// Pastikan ini adalah Expense Report (memiliki id_kasbon)
		if (empty($data->id_kasbon)) {
			return;
		}

		// Cek apakah sudah pernah di-insert ke tr_jurnal (mencegah duplikasi)
		$cek_existing = $this->db->get_where('tr_jurnal', [
			'no_transaksi' => $data->no_doc,
			'jenis_transaksi' => 'Expense Report'
		])->num_rows();
		if ($cek_existing > 0) {
			return;
		}

		$get_expense_detail = $this->db->get_where('tr_expense_detail', ['no_doc' => $data->no_doc])->result();
		if (empty($get_expense_detail)) {
			return;
		}

		$total_expense = 0;
		$total_kasbon = 0;
		foreach ($get_expense_detail as $dtl) {
			$total_expense += floatval($dtl->expense);
			$total_kasbon += floatval($dtl->kasbon);
		}

		if ($total_kasbon <= 0 && !empty($data->id_kasbon)) {
			$get_kb = $this->db->get_where('tr_kasbon', ['no_doc' => $data->id_kasbon])->row();
			if (!empty($get_kb)) {
				$total_kasbon = floatval($get_kb->jumlah_kasbon);
			}
		}

		$selisih = $total_kasbon - $total_expense;

		$tgl_doc = !empty($data->tgl_doc) ? $data->tgl_doc : date('Y-m-d');
		$month_num = (int) date('m', strtotime($tgl_doc));
		$month_roman = int_to_roman($month_num);
		$year_short = date('y', strtotime($tgl_doc));

		// Generate nomor jurnal otomatis NNNNN-AJV-{MM}-{YY}
		$srcMtr = "SELECT MAX(no_jurnal) as maxP FROM tr_jurnal WHERE no_jurnal LIKE '%-AJV-" . $month_roman . "-" . $year_short . "%'";
		$resultMtr = $this->db->query($srcMtr)->row_array();
		$urutan = 0;
		if (!empty($resultMtr['maxP'])) {
			$urutan = (int) substr($resultMtr['maxP'], 0, 5);
		}
		$urutan++;
		$no_jurnal = sprintf('%05s', $urutan) . '-AJV-' . $month_roman . '-' . $year_short;

		// Tarik info company
		$id_company = '';
		$nm_company = '';
		try {
			if (isset($this->load) && is_object($this->load) && method_exists($this->load, 'database')) {
				$consultant = $this->load->database('consultant', true);
				if (!empty($data->id_kasbon)) {
					$get_kb = $this->db->get_where('tr_kasbon', ['no_doc' => $data->id_kasbon])->row();
					if (!empty($get_kb) && !empty($get_kb->project)) {
						$consultant->select('a.id, a.nm_company');
						$consultant->from('kons_tr_company a');
						$consultant->join('kons_tr_penawaran b', 'b.company = a.id', 'left');
						$consultant->where('b.id_quotation', $get_kb->project);
						$get_comp = $consultant->get()->row();
						if (!empty($get_comp)) {
							$id_company = $get_comp->id;
							$nm_company = $get_comp->nm_company;
						}
					}
				}

				if (empty($nm_company)) {
					$get_first_comp = $consultant->get('kons_tr_company')->row();
					if (!empty($get_first_comp)) {
						$id_company = $get_first_comp->id;
						$nm_company = $get_first_comp->nm_company;
					}
				}
			}
		} catch (Throwable $e) {
			$id_company = '';
			$nm_company = '';
		}

		$id_divisi = !empty($data->departement) ? $data->departement : '';
		$nm_divisi = !empty($data->departement) ? $data->departement : '';

		$arr_insert = [];

		// 1. Sisi DEBIT: Detail item expense
		foreach ($get_expense_detail as $dtl) {
			$exp_num = floatval($dtl->expense);
			if ($exp_num > 0) {
				$coa_code = !empty($dtl->coa) ? $dtl->coa : '1304-01-01';
				$coa_name = 'Peralatan Kantor';
				$q_coa_acc = $this->db->query("SELECT nama FROM " . DBACC . ".coa_master WHERE no_perkiraan = '" . $coa_code . "'")->row();
				if (!empty($q_coa_acc)) {
					$coa_name = $q_coa_acc->nama;
				} else {
					$coa_name = ($coa_code == '1304-01-01') ? 'Peralatan Kantor' : 'Biaya Pengeluaran';
				}

				$desk = !empty($dtl->deskripsi) ? $dtl->deskripsi : (!empty($dtl->keterangan) ? $dtl->keterangan : 'Pengeluaran Expense');
				if (!empty($dtl->id_kasbon)) {
					$desk = 'Pengeluaran Kasbon ' . $dtl->id_kasbon . ' - ' . $desk;
				}

				$arr_insert[] = [
					'no_jurnal'       => $no_jurnal,
					'tgl_jurnal'      => $tgl_doc,
					'coa'             => $coa_code,
					'id_company'      => $id_company,
					'nm_company'      => $nm_company,
					'nm_coa'          => $coa_name,
					'debit'           => $exp_num,
					'kredit'          => 0,
					'keterangan'      => $desk,
					'sts'             => '0',
					'no_transaksi'    => $data->no_doc,
					'jenis_transaksi' => 'Expense Report',
					'id_divisi'       => $id_divisi,
					'nm_divisi'       => $nm_divisi,
					'created_by'      => $this->auth->user_id(),
					'created_date'    => date('Y-m-d H:i:s')
				];
			}
		}

		// Sisi DEBIT: Piutang Kelebihan Kasbon (1103-01-15) jika LEBIH KASBON ($selisih > 0)
		if ($selisih > 0) {
			$coa_piutang = '1103-01-15';
			$nm_piutang = 'Piutang Kelebihan Kasbon';
			$q_coa_acc = $this->db->query("SELECT nama FROM " . DBACC . ".coa_master WHERE no_perkiraan = '" . $coa_piutang . "'")->row();
			if (!empty($q_coa_acc)) {
				$nm_piutang = $q_coa_acc->nama;
			}

			$desk_piutang = 'Piutang Kelebihan Kasbon (Menunggu Pengembalian) - ' . $data->no_doc;

			$arr_insert[] = [
				'no_jurnal'       => $no_jurnal,
				'tgl_jurnal'      => $tgl_doc,
				'coa'             => $coa_piutang,
				'id_company'      => $id_company,
				'nm_company'      => $nm_company,
				'nm_coa'          => $nm_piutang,
				'debit'           => $selisih,
				'kredit'          => 0,
				'keterangan'      => $desk_piutang,
				'sts'             => '0',
				'no_transaksi'    => $data->no_doc,
				'jenis_transaksi' => 'Expense Report',
				'id_divisi'       => $id_divisi,
				'nm_divisi'       => $nm_divisi,
				'created_by'      => $this->auth->user_id(),
				'created_date'    => date('Y-m-d H:i:s')
			];
		}

		// 2. Sisi KREDIT: Kasbon Awal (Piutang Lain-lain Konsultan)
		if ($total_kasbon > 0) {
			$coa_kasbon = '1103-01-14';
			$nm_kasbon = 'Piutang Lain-lain Konsultan';
			$q_coa_acc = $this->db->query("SELECT nama FROM " . DBACC . ".coa_master WHERE no_perkiraan = '" . $coa_kasbon . "'")->row();
			if (!empty($q_coa_acc)) {
				$nm_kasbon = $q_coa_acc->nama;
			}

			$desk_kasbon = 'Pertanggungjawaban Kasbon' . (!empty($data->id_kasbon) ? ' (' . $data->id_kasbon . ')' : '');

			$arr_insert[] = [
				'no_jurnal'       => $no_jurnal,
				'tgl_jurnal'      => $tgl_doc,
				'coa'             => $coa_kasbon,
				'id_company'      => $id_company,
				'nm_company'      => $nm_company,
				'nm_coa'          => $nm_kasbon,
				'debit'           => 0,
				'kredit'          => $total_kasbon,
				'keterangan'      => $desk_kasbon,
				'sts'             => '0',
				'no_transaksi'    => $data->no_doc,
				'jenis_transaksi' => 'Expense Report',
				'id_divisi'       => $id_divisi,
				'nm_divisi'       => $nm_divisi,
				'created_by'      => $this->auth->user_id(),
				'created_date'    => date('Y-m-d H:i:s')
			];
		}

		// 3. Sisi KREDIT: Hutang Expense / Reimburse (Jika Lebih Expense / Selisih < 0)
		if ($selisih < 0) {
			$kurang_bayar = abs($selisih);
			$coa_hutang = '9999-99-99';
			$nm_hutang = 'Hutang Expense / Reimburse Karyawan';
			$q_coa_acc = $this->db->query("SELECT nama FROM " . DBACC . ".coa_master WHERE no_perkiraan = '" . $coa_hutang . "'")->row();
			if (!empty($q_coa_acc)) {
				$nm_hutang = $q_coa_acc->nama;
			}

			$desk_hutang = 'Lebih Expense (Reimburse Kantor ke Karyawan) - ' . $data->no_doc;

			$arr_insert[] = [
				'no_jurnal'       => $no_jurnal,
				'tgl_jurnal'      => $tgl_doc,
				'coa'             => $coa_hutang,
				'id_company'      => $id_company,
				'nm_company'      => $nm_company,
				'nm_coa'          => $nm_hutang,
				'debit'           => 0,
				'kredit'          => $kurang_bayar,
				'keterangan'      => $desk_hutang,
				'sts'             => '0',
				'no_transaksi'    => $data->no_doc,
				'jenis_transaksi' => 'Expense Report',
				'id_divisi'       => $id_divisi,
				'nm_divisi'       => $nm_divisi,
				'created_by'      => $this->auth->user_id(),
				'created_date'    => date('Y-m-d H:i:s')
			];
		}

		if (!empty($arr_insert)) {
			$this->db->insert_batch('tr_jurnal', $arr_insert);
		}
	}

	/**
	 * Datatables endpoint untuk Tab 2: Pengembalian Kasbon (Sisi User Pelapor)
	 */
	public function get_dat_pengembalian_kasbon()
	{
		$post   = $this->input->post();
		$draw   = isset($post['draw'])   ? (int) $post['draw']   : 1;
		$length = isset($post['length']) ? (int) $post['length'] : 10;
		$start  = isset($post['start'])  ? (int) $post['start']  : 0;
		$search = isset($post['search']['value']) ? trim($post['search']['value']) : '';

		$cur_user = $this->auth->user_name();
		$is_admin = ($this->auth->user_id() === '7' || $this->auth->user_id() === '1' || has_permission('Expense.Manage'));

		$this->db->from('tr_expense a');
		$this->db->where('a.lebih_bayar >', 0);
		$this->db->where_in('a.status', ['3', '4']);
		$this->db->group_start();
		$this->db->where('a.id_po', null);
		$this->db->or_where('a.id_po', '');
		$this->db->group_end();
		$this->db->not_like('a.no_doc', 'PI-', 'after');
		if (!$is_admin) {
			$this->db->where('a.nama', $cur_user);
		}
		$count_all = $this->db->count_all_results();

		$this->db->select('a.*, b.nm_lengkap as nmuser');
		$this->db->from('tr_expense a');
		$this->db->join('users b', 'a.nama=b.username', 'left');
		$this->db->where('a.lebih_bayar >', 0);
		$this->db->where_in('a.status', ['3', '4']);
		$this->db->group_start();
		$this->db->where('a.id_po', null);
		$this->db->or_where('a.id_po', '');
		$this->db->group_end();
		$this->db->not_like('a.no_doc', 'PI-', 'after');
		if (!$is_admin) {
			$this->db->where('a.nama', $cur_user);
		}
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('a.no_doc', $search, 'both');
			$this->db->or_like('a.id_kasbon', $search, 'both');
			$this->db->or_like('a.informasi', $search, 'both');
			$this->db->or_like('a.nama', $search, 'both');
			$this->db->or_like('b.nm_lengkap', $search, 'both');
			$this->db->group_end();
		}
		$count_filter = $this->db->count_all_results('', false);

		$this->db->order_by('a.id', 'DESC');
		$this->db->limit($length, $start);
		$rows = $this->db->get()->result_array();

		$data = [];
		$no = $start;
		foreach ($rows as $item) {
			$no++;
			$nominal_lebih = floatval($item['lebih_bayar']);
			$status_badge = $this->_render_expense_status_badge($item);

			// Tampilkan alasan penolakan verifikasi jika ada
			if (!empty($item['verif_reject_reason']) && (string)$item['status'] === '3') {
				$status_badge .= '<br><span class="badge" style="background:#fff5f5; color:#c53030; border:1px solid #feb2b2; font-weight:normal; font-size:10px; margin-top:3px; display:inline-block; max-width:180px; white-space:normal; text-align:center;">'
					. '<i class="fa fa-info-circle"></i> Catatan Finance: ' . htmlspecialchars($item['verif_reject_reason']) . '</span>';
			}

			$action = '';
			if ((string)$item['status'] === '3') {
				$action = '<button type="button" class="btn btn-primary btn-xs btn-rounded btn-upload-pengembalian" '
					. 'data-id="' . $item['id'] . '" '
					. 'data-doc="' . htmlspecialchars($item['no_doc']) . '" '
					. 'data-kasbon="' . htmlspecialchars($item['id_kasbon'] ?? '') . '" '
					. 'data-nominal="' . $nominal_lebih . '">'
					. '<i class="fa fa-upload"></i> Upload Bukti</button>';
			} else if ((string)$item['status'] === '4') {
				$action = '<a class="btn btn-info btn-xs btn-rounded" href="javascript:void(0);" onclick="data_view(' . $item['id'] . ')">'
					. '<i class="fa fa-eye"></i> Detail Bukti</a>';
			}

			$data[] = [
				'no' => $no,
				'no_doc' => $item['no_doc'],
				'no_kasbon' => !empty($item['id_kasbon']) ? '<span class="badge bg-blue"><i class="fa fa-ticket"></i> ' . $item['id_kasbon'] . '</span>' : '-',
				'tgl_doc' => $item['tgl_doc'],
				'informasi' => htmlspecialchars($item['informasi']),
				'total_kasbon' => number_format(floatval($item['total_kasbon'])),
				'realisasi' => number_format(floatval($item['jumlah'])),
				'lebih_bayar' => '<b>Rp ' . number_format($nominal_lebih) . '</b>',
				'status' => $status_badge,
				'action' => $action
			];
		}

		echo json_encode([
			'draw' => $draw,
			'recordsTotal' => $count_all,
			'recordsFiltered' => $count_filter,
			'data' => $data
		]);
	}

	/**
	 * Endpoint simpan data pengembalian dana & upload bukti transfer (Sisi User Pelapor)
	 */
	public function save_pengembalian_dana()
	{
		$id = $this->input->post('id_expense');
		$transfer_coa_bank = $this->input->post('transfer_coa_bank');
		$transfer_tanggal = $this->input->post('transfer_tanggal');
		$transfer_jumlah = floatval(str_replace(',', '', (string)$this->input->post('transfer_jumlah')));

		if (empty($id)) {
			echo json_encode(['save' => false, 'message' => 'ID Expense tidak ditemukan.']);
			return;
		}

		$expense = $this->db->get_where('tr_expense', ['id' => $id])->row();
		if (empty($expense)) {
			echo json_encode(['save' => false, 'message' => 'Data expense tidak ditemukan.']);
			return;
		}

		if (empty($transfer_coa_bank)) {
			echo json_encode(['save' => false, 'message' => 'Bank Tujuan Transfer perusahaan wajib dipilih!']);
			return;
		}

		if (empty($transfer_tanggal)) {
			echo json_encode(['save' => false, 'message' => 'Tanggal transfer wajib diisi!']);
			return;
		}

		if ($transfer_jumlah <= 0) {
			echo json_encode(['save' => false, 'message' => 'Nominal transfer pengembalian harus lebih dari 0!']);
			return;
		}

		// Validasi upload bukti transfer: minimal 1 file
		$uploaded_files = [];
		$upload_dir = 'assets/expense_bukti_pengembalian/';
		if (!is_dir($upload_dir)) {
			mkdir($upload_dir, 0777, true);
		}

		if (!empty($_FILES['bukti_transfer']['name'][0])) {
			foreach ($_FILES['bukti_transfer']['name'] as $idx => $fname) {
				if (!empty($_FILES['bukti_transfer']['tmp_name'][$idx])) {
					$tmp = $_FILES['bukti_transfer']['tmp_name'][$idx];
					$clean = time() . '_' . rand(100, 999) . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($fname));
					$dest = $upload_dir . $clean;
					if (move_uploaded_file($tmp, $dest)) {
						$uploaded_files[] = $dest;
					}
				}
			}
		}

		// Jika ada file existing sebelumnya dan user tidak unggah baru
		if (empty($uploaded_files) && !empty($expense->bukti_pengembalian)) {
			$uploaded_files = explode(';', $expense->bukti_pengembalian);
		}

		if (empty($uploaded_files)) {
			echo json_encode(['save' => false, 'message' => 'Bukti transfer pengembalian dana wajib diunggah (minimal 1 file gambar / PDF)!']);
			return;
		}

		$update_data = [
			'transfer_coa_bank' => $transfer_coa_bank,
			'transfer_tanggal' => date('Y-m-d', strtotime($transfer_tanggal)),
			'transfer_jumlah' => $transfer_jumlah,
			'bukti_pengembalian' => implode(';', $uploaded_files),
			'status' => 4, // Menunggu Verifikasi Finance
			'verif_reject_reason' => null
		];

		$this->db->trans_begin();
		$this->db->update('tr_expense', $update_data, ['id' => $id]);

		if ($this->db->trans_status() === false || $this->db->trans_commit() === false) {
			$this->db->trans_rollback();
			echo json_encode(['save' => false, 'message' => 'Gagal menyimpan data pengembalian kasbon.']);
			return;
		}

		echo json_encode([
			'save' => true,
			'message' => 'Bukti pengembalian kasbon berhasil diunggah! Laporan kini berstatus "Menunggu Verifikasi Finance".'
		]);
	}

	/**
	 * Datatables endpoint untuk Tab 3: Lebih Expense (Reimburse ke Karyawan)
	 */
	public function get_dat_lebih_expense()
	{
		$post   = $this->input->post();
		$draw   = isset($post['draw'])   ? (int) $post['draw']   : 1;
		$length = isset($post['length']) ? (int) $post['length'] : 10;
		$start  = isset($post['start'])  ? (int) $post['start']  : 0;
		$search = isset($post['search']['value']) ? trim($post['search']['value']) : '';

		$cur_user = $this->auth->user_name();
		$is_admin = ($this->auth->user_id() === '7' || $this->auth->user_id() === '1' || has_permission('Expense.Manage'));

		$this->db->from('tr_expense a');
		$this->db->where('a.kurang_bayar >', 0);
		$this->db->where_in('a.status', ['2', '3', '4']);
		$this->db->group_start();
		$this->db->where('a.id_po', null);
		$this->db->or_where('a.id_po', '');
		$this->db->group_end();
		$this->db->not_like('a.no_doc', 'PI-', 'after');
		if (!$is_admin) {
			$this->db->where('a.nama', $cur_user);
		}
		$count_all = $this->db->count_all_results();

		$this->db->select('a.*, b.nm_lengkap as nmuser, rp.no_doc as no_rp, rp.status as status_rp');
		$this->db->from('tr_expense a');
		$this->db->join('users b', 'a.nama=b.username', 'left');
		$this->db->join('tr_request_payment rp', 'rp.no_doc = a.no_doc AND rp.tipe = "expense"', 'left');
		$this->db->where('a.kurang_bayar >', 0);
		$this->db->where_in('a.status', ['2', '3', '4']);
		$this->db->group_start();
		$this->db->where('a.id_po', null);
		$this->db->or_where('a.id_po', '');
		$this->db->group_end();
		$this->db->not_like('a.no_doc', 'PI-', 'after');
		if (!$is_admin) {
			$this->db->where('a.nama', $cur_user);
		}
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('a.no_doc', $search, 'both');
			$this->db->or_like('a.id_kasbon', $search, 'both');
			$this->db->or_like('a.informasi', $search, 'both');
			$this->db->or_like('a.nama', $search, 'both');
			$this->db->group_end();
		}
		$count_filter = $this->db->count_all_results('', false);

		$this->db->order_by('a.id', 'DESC');
		$this->db->limit($length, $start);
		$rows = $this->db->get()->result_array();

		$data = [];
		$no = $start;
		foreach ($rows as $item) {
			$no++;
			$kurang_bayar = floatval($item['kurang_bayar']);

			// Status alur reimburse via Request Payment
			$st_rp = isset($item['status_rp']) ? (int)$item['status_rp'] : 1;
			if ($st_rp === 1) {
				$badge_rp = '<span class="badge bg-blue" style="font-size:11px; padding:4px 8px;"><i class="fa fa-clock-o"></i> Menunggu Diajukan Finance</span>';
			} else if ($st_rp === 2) {
				$badge_rp = '<span class="badge" style="background:#f39c12; color:#fff; font-size:11px; padding:4px 8px;"><i class="fa fa-hourglass-half"></i> Menunggu Approval Management</span>';
			} else if ($st_rp === 3) {
				$badge_rp = '<span class="badge bg-green" style="font-size:11px; padding:4px 8px;"><i class="fa fa-check-circle"></i> Disetujui / Siap Dibayar</span>';
			} else if ($st_rp >= 4) {
				$badge_rp = '<span class="badge bg-dark" style="font-size:11px; padding:4px 8px;"><i class="fa fa-check"></i> Selesai Dibayar Kantor</span>';
			} else {
				$badge_rp = '<span class="badge bg-gray" style="font-size:11px; padding:4px 8px;">Dalam Antrian</span>';
			}

			$data[] = [
				'no' => $no,
				'no_doc' => $item['no_doc'],
				'no_kasbon' => !empty($item['id_kasbon']) ? '<span class="badge bg-blue"><i class="fa fa-ticket"></i> ' . $item['id_kasbon'] . '</span>' : '-',
				'tgl_doc' => $item['tgl_doc'],
				'informasi' => htmlspecialchars($item['informasi']),
				'kurang_bayar' => '<b style="color:#00a65a; font-size:13px;">Rp ' . number_format($kurang_bayar) . '</b>',
				'status_reimburse' => $badge_rp,
				'action' => '<a class="btn btn-default btn-xs btn-rounded" href="javascript:void(0);" onclick="data_view(' . $item['id'] . ')"><i class="fa fa-eye"></i> Detail</a>'
			];
		}

		echo json_encode([
			'draw' => $draw,
			'recordsTotal' => $count_all,
			'recordsFiltered' => $count_filter,
			'data' => $data
		]);
	}

	/**
	 * Halaman index Menu ID 488: Verifikasi Pengembalian Kasbon (Finance)
	 */
	public function verifikasi_pengembalian()
	{
		$this->beginAudit(__FUNCTION__, array('page' => 'verifikasi_pengembalian_index'), array('page', 'outcome'));
		$this->template->page_icon('fa fa-check-square-o');
		$this->template->title('Verifikasi Pengembalian Kasbon');
		$this->template->render('verifikasi_pengembalian_index');
		$this->completeAudit('Membuka daftar verifikasi pengembalian kasbon', 1, array(
			'page' => 'verifikasi_pengembalian_index',
			'outcome' => 'rendered'
		));
	}

	/**
	 * Datatables endpoint Menu 488: Verifikasi Pengembalian Kasbon
	 */
	public function get_dat_verifikasi_pengembalian()
	{
		$post   = $this->input->post();
		$draw   = isset($post['draw'])   ? (int) $post['draw']   : 1;
		$length = isset($post['length']) ? (int) $post['length'] : 10;
		$start  = isset($post['start'])  ? (int) $post['start']  : 0;
		$search = isset($post['search']['value']) ? trim($post['search']['value']) : '';

		$this->db->from('tr_expense a');
		$this->db->where('a.status', 4);
		$this->db->where('a.lebih_bayar >', 0);
		$this->db->group_start();
		$this->db->where('a.id_po', null);
		$this->db->or_where('a.id_po', '');
		$this->db->group_end();
		$this->db->not_like('a.no_doc', 'PI-', 'after');
		$count_all = $this->db->count_all_results();

		$this->db->select('a.*, b.nm_lengkap as nmuser');
		$this->db->from('tr_expense a');
		$this->db->join('users b', 'a.nama=b.username', 'left');
		$this->db->where('a.status', 4);
		$this->db->where('a.lebih_bayar >', 0);
		$this->db->group_start();
		$this->db->where('a.id_po', null);
		$this->db->or_where('a.id_po', '');
		$this->db->group_end();
		$this->db->not_like('a.no_doc', 'PI-', 'after');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('a.no_doc', $search, 'both');
			$this->db->or_like('a.id_kasbon', $search, 'both');
			$this->db->or_like('a.informasi', $search, 'both');
			$this->db->or_like('a.nama', $search, 'both');
			$this->db->or_like('b.nm_lengkap', $search, 'both');
			$this->db->group_end();
		}
		$count_filter = $this->db->count_all_results('', false);

		$this->db->order_by('a.id', 'DESC');
		$this->db->limit($length, $start);
		$rows = $this->db->get()->result_array();

		$data = [];
		$no = $start;
		foreach ($rows as $item) {
			$no++;
			$lebih_bayar = floatval($item['lebih_bayar']);

			// Cari nama bank
			$bank_label = !empty($item['transfer_coa_bank']) ? $item['transfer_coa_bank'] : '-';
			if (!empty($item['transfer_coa_bank'])) {
				$qb = $this->db->query("SELECT nama FROM " . DBACC . ".coa_master WHERE no_perkiraan = " . $this->db->escape($item['transfer_coa_bank']))->row();
				if ($qb) {
					$bank_label = $qb->nama;
				}
			}

			$status_badge = '<span class="badge" style="background:#fd7e14; color:#fff; font-size:11px; padding:4px 8px;"><i class="fa fa-hourglass-half"></i> Menunggu Verifikasi</span>';

			$action = '<button type="button" class="btn btn-primary btn-xs btn-rounded" onclick="data_verif_detail(' . $item['id'] . ')">'
				. '<i class="fa fa-check-square-o"></i> Proses Verifikasi</button>';

			$data[] = [
				'no' => $no,
				'no_doc' => $item['no_doc'],
				'no_kasbon' => !empty($item['id_kasbon']) ? '<span class="badge bg-blue"><i class="fa fa-ticket"></i> ' . $item['id_kasbon'] . '</span>' : '-',
				'tgl_doc' => $item['tgl_doc'],
				'nmuser' => !empty($item['nmuser']) ? htmlspecialchars($item['nmuser']) : htmlspecialchars($item['nama']),
				'informasi' => htmlspecialchars($item['informasi']),
				'lebih_bayar' => '<b>Rp ' . number_format($lebih_bayar) . '</b>',
				'bank_name' => htmlspecialchars($bank_label),
				'transfer_tanggal' => !empty($item['transfer_tanggal']) ? $item['transfer_tanggal'] : '-',
				'status' => $status_badge,
				'action' => $action
			];
		}

		echo json_encode([
			'draw' => $draw,
			'recordsTotal' => $count_all,
			'recordsFiltered' => $count_filter,
			'data' => $data
		]);
	}

	/**
	 * Form detail verifikasi pengembalian kasbon (Menu 488)
	 */
	public function verifikasi_pengembalian_detail($id)
	{
		$data = $this->db->get_where('tr_expense', ['id' => $id])->row();
		if (empty($data)) {
			echo '<div class="alert alert-danger">Data expense tidak ditemukan.</div>';
			return;
		}

		$bank_name = $data->transfer_coa_bank;
		if (!empty($data->transfer_coa_bank)) {
			$qb = $this->db->query("SELECT nama FROM " . DBACC . ".coa_master WHERE no_perkiraan = " . $this->db->escape($data->transfer_coa_bank))->row();
			if ($qb) {
				$bank_name = $qb->nama;
			}
		}

		$this->template->set('data', $data);
		$this->template->set('bank_name', $bank_name);
		$this->template->render('verifikasi_pengembalian_detail');
	}

	/**
	 * Aksi setujui verifikasi pengembalian kasbon & posting Jurnal 2 (Menu 488)
	 */
	public function verifikasi_pengembalian_approve()
	{
		$id = $this->input->post('id');
		$chk_bukti = (int)$this->input->post('chk_bukti');
		$chk_mutasi = (int)$this->input->post('chk_mutasi');
		$chk_nominal = (int)$this->input->post('chk_nominal');

		if (empty($id)) {
			echo json_encode(['save' => false, 'message' => 'ID Expense tidak ditemukan.']);
			return;
		}

		$expense = $this->db->get_where('tr_expense', ['id' => $id])->row();
		if (empty($expense)) {
			echo json_encode(['save' => false, 'message' => 'Data expense tidak ditemukan.']);
			return;
		}

		// BR-12: Verifikator tidak boleh pelapor sendiri
		if ($expense->nama === $this->auth->user_name()) {
			echo json_encode(['save' => false, 'message' => 'Anda tidak dapat memverifikasi pengembalian untuk laporan Anda sendiri (Segregation of Duties)!']);
			return;
		}

		// Validasi 3 checklist wajib
		if ($chk_bukti !== 1 || $chk_mutasi !== 1 || $chk_nominal !== 1) {
			echo json_encode(['save' => false, 'message' => 'Ketiga butir checklist verifikasi wajib dicentang sebelum menyetujui!']);
			return;
		}

		$bank_coa = !empty($expense->transfer_coa_bank) ? $expense->transfer_coa_bank : '1101-02-01';
		$nominal = floatval($expense->transfer_jumlah > 0 ? $expense->transfer_jumlah : $expense->lebih_bayar);

		$this->db->trans_begin();

		// Update tr_expense status ke 2 (Selesai/Approved)
		$this->db->update('tr_expense', [
			'status' => 2,
			'verified_by' => $this->auth->user_name(),
			'verified_on' => date('Y-m-d H:i:s'),
			'verif_checklist' => 'bukti,mutasi,nominal',
			'verif_reject_reason' => null
		], ['id' => $id]);

		// Posting JURNAL 2 (Debit Bank, Kredit 1103-01-15)
		$this->_post_jurnal_pengembalian_kasbon($id, $bank_coa, $nominal);

		if ($this->db->trans_status() === false || $this->db->trans_commit() === false) {
			$this->db->trans_rollback();
			echo json_encode(['save' => false, 'message' => 'Gagal memverifikasi pengembalian kasbon (kesalahan database).']);
			return;
		}

		echo json_encode([
			'save' => true,
			'message' => 'Verifikasi pengembalian kasbon selesai! Jurnal 2 berhasil dibukukan dan status laporan telah Selesai.'
		]);
	}

	/**
	 * Aksi tolak verifikasi pengembalian kasbon (Menu 488)
	 */
	public function verifikasi_pengembalian_reject()
	{
		$id = $this->input->post('id');
		$reason = trim((string)$this->input->post('reason'));

		if (empty($id)) {
			echo json_encode(['save' => false, 'message' => 'ID Expense tidak ditemukan.']);
			return;
		}

		if (empty($reason)) {
			echo json_encode(['save' => false, 'message' => 'Alasan penolakan verifikasi wajib diisi!']);
			return;
		}

		$expense = $this->db->get_where('tr_expense', ['id' => $id])->row();
		if (empty($expense)) {
			echo json_encode(['save' => false, 'message' => 'Data expense tidak ditemukan.']);
			return;
		}

		// Update tr_expense kembali ke status 3 (Menunggu Pengembalian)
		$this->db->trans_begin();
		$this->db->update('tr_expense', [
			'status' => 3,
			'verif_reject_reason' => $reason
		], ['id' => $id]);

		if ($this->db->trans_status() === false || $this->db->trans_commit() === false) {
			$this->db->trans_rollback();
			echo json_encode(['save' => false, 'message' => 'Gagal menolak verifikasi pengembalian kasbon.']);
			return;
		}

		echo json_encode([
			'save' => true,
			'message' => 'Pengembalian kasbon ditolak. Dokumen telah dikembalikan ke antrian pelapor untuk perbaikan.'
		]);
	}

	/**
	 * Posting otomatis JURNAL 2 saat Finance memverifikasi pengembalian kasbon
	 * Debit : Bank Perusahaan (transfer_coa_bank)
	 * Kredit: 1103-01-15 (Piutang Kelebihan Kasbon)
	 */
	private function _post_jurnal_pengembalian_kasbon($id, $coa_bank, $nominal)
	{
		$data = $this->db->get_where('tr_expense', ['id' => $id])->row();
		if (empty($data)) return false;

		$tgl_doc = !empty($data->transfer_tanggal) ? $data->transfer_tanggal : date('Y-m-d');
		$month_num = (int) date('m', strtotime($tgl_doc));
		$month_roman = int_to_roman($month_num);
		$year_short = date('y', strtotime($tgl_doc));

		// Generate nomor jurnal otomatis NNNNN-BUM-{MM}-{YY}
		$srcMtr = "SELECT MAX(no_jurnal) as maxP FROM tr_jurnal WHERE no_jurnal LIKE '%-BUM-" . $month_roman . "-" . $year_short . "%'";
		$resultMtr = $this->db->query($srcMtr)->row_array();
		$urutan = 0;
		if (!empty($resultMtr['maxP'])) {
			$urutan = (int) substr($resultMtr['maxP'], 0, 5);
		}
		$urutan++;
		$no_jurnal = sprintf('%05s', $urutan) . '-BUM-' . $month_roman . '-' . $year_short;

		// Ambil nama bank dari coa_master
		$q_bank = $this->db->query("SELECT nama FROM " . DBACC . ".coa_master WHERE no_perkiraan = " . $this->db->escape($coa_bank))->row();
		$nm_bank = !empty($q_bank) ? $q_bank->nama : 'Bank Perusahaan';

		$coa_piutang = '1103-01-15';
		$q_piutang = $this->db->query("SELECT nama FROM " . DBACC . ".coa_master WHERE no_perkiraan = " . $this->db->escape($coa_piutang))->row();
		$nm_piutang = !empty($q_piutang) ? $q_piutang->nama : 'Piutang Kelebihan Kasbon';

		$arr_insert = [
			[
				'no_jurnal'       => $no_jurnal,
				'tgl_jurnal'      => $tgl_doc,
				'coa'             => $coa_bank,
				'id_company'      => '',
				'nm_company'      => '',
				'nm_coa'          => $nm_bank,
				'debit'           => $nominal,
				'kredit'          => 0,
				'keterangan'      => 'Penerimaan Pengembalian Kelebihan Kasbon - ' . $data->no_doc . (!empty($data->id_kasbon) ? ' (' . $data->id_kasbon . ')' : ''),
				'sts'             => '0',
				'no_transaksi'    => $data->no_doc,
				'jenis_transaksi' => 'Pengembalian Kasbon',
				'id_divisi'       => !empty($data->departement) ? $data->departement : '',
				'nm_divisi'       => !empty($data->departement) ? $data->departement : '',
				'created_by'      => $this->auth->user_id(),
				'created_date'    => date('Y-m-d H:i:s')
			],
			[
				'no_jurnal'       => $no_jurnal,
				'tgl_jurnal'      => $tgl_doc,
				'coa'             => $coa_piutang,
				'id_company'      => '',
				'nm_company'      => '',
				'nm_coa'          => $nm_piutang,
				'debit'           => 0,
				'kredit'          => $nominal,
				'keterangan'      => 'Penghapusan Piutang Kelebihan Kasbon - ' . $data->no_doc,
				'sts'             => '0',
				'no_transaksi'    => $data->no_doc,
				'jenis_transaksi' => 'Pengembalian Kasbon',
				'id_divisi'       => !empty($data->departement) ? $data->departement : '',
				'nm_divisi'       => !empty($data->departement) ? $data->departement : '',
				'created_by'      => $this->auth->user_id(),
				'created_date'    => date('Y-m-d H:i:s')
			]
		];

		return $this->db->insert_batch('tr_jurnal', $arr_insert);
	}
}

// Sync: 2026-09-22 Full re-push modul expense
