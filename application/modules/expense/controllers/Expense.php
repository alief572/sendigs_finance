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
		$this->load->model(array('all/All_model', 'Expense/Expense_model', 'All/All_model', 'Jurnal_nomor/Jurnal_model', 'Coa_expense/Coa_expense_model'));
		$this->template->title('Expense Report');
		$this->template->page_icon('fa fa-cubes');
		date_default_timezone_set('Asia/Bangkok');
		$this->status = array("0" => "Baru", "1" => "Disetujui", "2" => "Disetujui Management", "3" => "Selesai", "9" => "Ditolak");
	}

	// list kasbon
	public function kasbon()
	{
		// $where = array('a.nama' => $this->auth->user_name());
		// $data = $this->Expense_model->GetListDataKasbon();
		// $this->template->set('results', $data);
		$this->template->set('status', $this->status);
		$this->template->page_icon('fa fa-list');
		$this->template->title('Kasbon');
		$this->template->render('kasbon_list');
	}
	// list kasbon all
	public function kasbon_list_all()
	{
		// $data = $this->Expense_model->GetListDataKasbon();
		// $this->template->set('results', $data);
		$this->template->set('status', $this->status);
		$this->template->page_icon('fa fa-list');
		$this->template->title('Kasbon List');
		$this->template->render('kasbon_list_all');
	}

	// kasbon create
	public function kasbon_create()
	{

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
			->group_by('b.no_pr');
		$get_detail_pr_stok_material = $this->db->get()->result_array();
		foreach ($get_detail_pr_stok_material as $item) {
			$list_pr_non_po[] = [
				'no_pr' => $item['no_pr'],
				'keterangan' => strtoupper($item['category'])
			];
		}

		$this->db->select('b.no_pr, b.project_name')
			->from('rutin_non_planning_detail a')
			->join('rutin_non_planning_header b', 'b.no_pr = a.no_pr', 'left')
			->join('ms_satuan c', 'c.id = a.satuan', 'left')
			->where('b.metode_pembelian', '2')
			->where('a.kasbon_created', null)
			->group_by('b.no_pr');
		$get_detail_pr_departemen = $this->db->get()->result_array();
		foreach ($get_detail_pr_departemen as $item) {
			$list_pr_non_po[] = [
				'no_pr' => $item['no_pr'],
				'keterangan' => $item['project_name']
			];
		}

		$this->db->select('b.no_pr, b.nama_asset');
		$this->db->from('tran_pr_header a');
		$this->db->join('asset_planning b', 'b.no_pr = a.no_pr', 'left');
		$this->db->where('a.metode_pembelian', 2);
		$this->db->where('a.kasbon_created', null);
		$this->db->group_by('b.no_pr');
		$get_pr_asset = $this->db->get()->result_array();
		foreach ($get_pr_asset as $item) {
			$list_pr_non_po[] = [
				'no_pr' => $item['no_pr'],
				'keterangan' => strtoupper($item['nama_asset'])
			];
		}

		$get_list_coa = $this->Expense_model->GetListCoa('Kasbon');

		$this->template->set('list_pr_non_po', $list_pr_non_po);
		$this->template->set('list_coa', $get_list_coa);
		$this->template->set('mod', '');
		$this->template->render('kasbon_form');
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

		if (!empty($file_name)) {
			$filenames = $file_name;

			if (file_exists($doc_pr)) {
				copy('' . $doc_pr . '', '' . $to_doc_pr . '');
			}
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
			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$result = false;
			} else {
				$this->db->trans_commit();
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
			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$result = FALSE;
			} else {
				$this->db->trans_commit();
				$result = TRUE;
			}
		}
		$param = array(
			'save' => $result,
			'id' => $id
		);
		echo json_encode($param);
	}

	public function get_kasbon($nama = '', $departement = '')
	{
		$data = $this->db->query("SELECT * FROM tr_kasbon WHERE status = 3")->result();
		$query1 = $this->db->query("
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
				(a.nama = '" . $nama . "' AND
				a.departement = '" . $departement . "' AND
				a.status = 3 AND
				(SELECT COUNT(aa.id) FROM tr_expense_detail aa JOIN tr_expense ab ON ab.no_doc = aa.no_doc WHERE aa.id_kasbon = a.no_doc AND ab.pettycash IS NULL) <= 0) OR
				(
					(SELECT COUNT(aa.id) FROM tr_expense_detail aa JOIN tr_expense ab ON aa.no_doc = ab.no_doc WHERE aa.id_kasbon = a.no_doc AND ab.pettycash IS NOT NULL) <= 0
				)
			
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
				a.status = '3' AND
				a.metode_pembelian = '2' AND 
				(SELECT COUNT(aa.id) FROM tr_expense aa JOIN tr_expense_detail ab ON ab.no_doc = aa.no_doc WHERE ab.id_expense_detail = a.id AND aa.status IN ('0','1','2','3')) < 1
		")->num_rows();
		if (!$query1) {
			print_r($this->db->error($query1));
			exit;
		}

		if ($query1 > 0) {

			$data = $this->db->query("
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
				(
					a.nama = '" . $nama . "' AND
					a.departement = '" . $departement . "' AND
					a.status = 3 AND
					(SELECT COUNT(aa.id) FROM tr_expense_detail aa WHERE aa.id_kasbon = a.no_doc) <= 0
				) OR
				(
					a.id_pr IS NOT NULL AND
					a.status = 3 AND
					(SELECT COUNT(aa.id) FROM tr_expense_detail aa JOIN tr_expense ab ON ab.no_doc = aa.no_doc WHERE aa.id_kasbon = a.no_doc AND ab.pettycash IS NULL) <= 0
				)
			
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
				a.status = '3' AND
				a.metode_pembelian = '2' AND 
				(SELECT COUNT(aa.id) FROM tr_expense aa JOIN tr_expense_detail ab ON ab.no_doc = aa.no_doc WHERE ab.id_expense_detail = a.id AND aa.status IN ('0','1','2','3')) < 1
		")->result();
			if (!$data) {
				print_r($this->db->error($data));
				exit;
			}
		} else {
			$data = false;
		}
		echo json_encode($data);
	}

	// kasbon edit
	public function kasbon_edit($id, $mod = '')
	{
		$data = $this->Expense_model->GetDataKasbon($id);

		$this->db->select('a.*, IF(b.code IS NULL, a.unit, b.code) as satuan');
		$this->db->from('tr_pr_detail_kasbon a');
		$this->db->join('ms_satuan b', 'b.id = a.unit', 'left');
		$this->db->where('a.id_kasbon', $data->no_doc);
		$get_pr_detail_kasbon = $this->db->get()->result_array();

		$get_list_coa = $this->Expense_model->GetListCoa('Kasbon');

		$this->template->set('mod', $mod);
		$this->template->set('status', $this->status);
		$this->template->set('data', $data);
		$this->template->set('list_coa', $get_list_coa);
		$this->template->set('stsview', '');
		$this->template->set('list_detail_pr_kasbon', $get_pr_detail_kasbon);
		$this->template->title('Kasbon Form');
		$this->template->page_icon('fa fa-list');
		$this->template->render('kasbon_form');
	}

	public function kasbon_edit_manage($id, $mod = '')
	{
		$data = $this->Expense_model->GetDataKasbon($id);

		$this->db->select('a.*, IF(b.code IS NULL, a.unit, b.code) as satuan');
		$this->db->from('tr_pr_detail_kasbon a');
		$this->db->join('ms_satuan b', 'b.id = a.unit', 'left');
		$this->db->where('a.id_kasbon', $data->no_doc);
		$get_pr_detail_kasbon = $this->db->get()->result_array();

		$get_list_coa = $this->Expense_model->GetListCoa('Kasbon');

		$this->template->set('mod', $mod);
		$this->template->set('status', $this->status);
		$this->template->set('data', $data);
		$this->template->set('list_coa', $get_list_coa);
		$this->template->set('stsview', '');
		$this->template->set('list_detail_pr_kasbon', $get_pr_detail_kasbon);
		$this->template->title('Approval Kasbon by Management');
		$this->template->page_icon('fa fa-list');
		$this->template->render('app_kasbon_manage');
	}
	// kasbon print
	public function kasbon_print($id)
	{
		$results = $this->Expense_model->GetDataKasbon($id);

		$nmuser = $results->created_by;
		if ($results->tipe_pr !== '') {
			if ($results->tipe_pr == 'pr departemen') {
				$this->db->select('b.nm_lengkap');
				$this->db->from('rutin_non_planning_header a');
				$this->db->join('users b', 'b.id_user = a.created_by');
				$this->db->where('a.no_pr', $results->id_pr);
				$get_single_detail = $this->db->get()->row();

				$nmuser = $get_single_detail->nm_lengkap;
			}

			if ($results->tipe_pr == 'pr stok') {
				$this->db->select('b.nm_lengkap');
				$this->db->from('material_planning_base_on_produksi a');
				$this->db->join('users b', 'b.id_user = a.created_by');
				$this->db->where('a.no_pr', $results->id_pr);
				$get_single_detail = $this->db->get()->row();

				$nmuser = $get_single_detail->nm_lengkap;
			}

			if ($results->tipe_pr == 'pr asset') {
				$this->db->select('b.nm_lengkap');
				$this->db->from('tran_pr_header a');
				$this->db->join('users b', 'b.id_user = a.created_by');
				$this->db->where('a.no_pr', $results->id_pr);
				$get_single_detail = $this->db->get()->row();

				$nmuser = $get_single_detail->nm_lengkap;
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

	public function periodik_print($id)
	{
		$this->db->select('
			a.*, 
			a.tanggal_doc as tgl_doc, 
			a.created_by as created_by_user, 
			a.created_on as created_on_header,
			COALESCE(SUM(b.nilai), 0) as jumlah_kasbon, 
			b.keterangan as keperluan, 
		');
		$this->db->from('tr_pengajuan_rutin a');
		$this->db->join('tr_pengajuan_rutin_detail b', 'a.no_doc = b.no_doc', 'left');
		$this->db->where('a.id', $id);

		$query = $this->db->get();

		if (!$query) {
			show_error('Database Error: ' . $this->db->error()['message']);
			return;
		}

		$results = $query->row();

		if (empty($results)) {
			show_404();
		}

		if (empty($results->doc_file)) {
			$results->doc_file = '';
		}

		$nmuser = '-';
		$results->created_by = 'FINANCE';

		$id_user_create = !empty($results->created_by_user) ? $results->created_by_user : $results->created_by;

		if (!empty($id_user_create)) {
			$q_user = $this->db->get_where('users', ['id_user' => $id_user_create]);
			if ($q_user && $q_user->num_rows() > 0) {
				$get_user = $q_user->row();
				$nmuser = $get_user->nm_lengkap;
				$results->created_by = $get_user->username;
			}
		}

		$results->approved_by = 'FINANCE';
		$results->approved_on = '';
		$results->doc_file_2 = '';

		$this->db->select('a.*');
		$this->db->from('tr_pengajuan_rutin_detail a');
		$this->db->where('a.no_doc', $results->no_doc);
		$get_detail = $this->db->get()->result();

		$data = array(
			'title' => 'Print Periodik',
			'stsview' => 'print',
			'data' => $results,
			'nmuser' => $nmuser,
			'detail' => $get_detail
		);

		$this->load->view('periodik_print', $data);
	}

	// kasbon view
	public function kasbon_view($id, $mod = '')
	{
		$data = $this->Expense_model->GetDataKasbon($id);

		$this->db->select('a.*, b.code as satuan');
		$this->db->from('tr_pr_detail_kasbon a');
		$this->db->join('ms_satuan b', 'b.id = a.unit', 'left');
		$this->db->where('a.id_kasbon', $data->no_doc);
		$get_pr_detail_kasbon = $this->db->get()->result_array();

		$this->template->set('mod', $mod);
		$this->template->set('status', $this->status);
		$this->template->set('data', $data);
		$this->template->set('stsview', 'view');
		$this->template->set('list_detail_pr_kasbon', $get_pr_detail_kasbon);
		$this->template->title('Kasbon Form');
		$this->template->page_icon('fa fa-list');
		$this->template->render('kasbon_form_detail');
	}
	// kasbon approval
	public function kasbon_fin()
	{
		// $datawhere = ("a.status = 0");
		// $data = $this->Expense_model->GetListDataKasbon($datawhere);
		$this->template->set('status', $this->status);
		// $this->template->set('results', $data);
		$this->template->set('stsview', 'view');
		$this->template->title('Kasbon Approval by Finance');
		$this->template->page_icon('fa fa-list');
		$this->template->render('kasbon_list_fin');
	}

	// kasbon approve
	public function kasbon_approve($id = '')
	{
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
		echo json_encode($param);
	}

	public function kasbon_approve_manage($id = '')
	{
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
		echo json_encode($param);
	}

	// kasbon delete
	public function kasbon_delete($id)
	{
		$get_kasbon = $this->db->get_where('tr_kasbon', ['id' => $id])->row();

		$no_doc = $get_kasbon->no_doc;
		$get_detail_kasbon = $this->db->get_where('tr_pr_detail_kasbon', ['id_kasbon' => $no_doc])->result_array();
		// print_r($get_detail_kasbon);
		// exit;
		foreach ($get_detail_kasbon as $detail_kasbon) :
			if ($detail_kasbon['tipe_pr'] == 'pr departemen') {
				$this->db->update('rutin_non_planning_detail', ['kasbon_created' => null], ['id' => $detail_kasbon['id_detail']]);
			} else {
				$this->db->update('material_planning_base_on_produksi_detail', ['kasbon_created' => null], ['id' => $detail_kasbon['id_detail']]);
			}
		endforeach;
		$this->db->delete('tr_pr_detail_kasbon', ['id_kasbon' => $no_doc]);
		$result = $this->All_model->dataDelete('tr_kasbon', array('id' => $id));
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
		} else {
			$this->db->trans_commit();
		}
		$param = array('delete' => $result);
		echo json_encode($param);
	}

	// list
	public function index()
	{
		// $data = $this->Expense_model->GetListData(array('nama' => $this->auth->user_name(), 'pettycash' => null, 'exp_pib' => null));
		// $data_detail = $this->Expense_model->GetListDataAll(array('nama' => $this->auth->user_name(), 'pettycash' => null, 'exp_pib' => null));
		// $this->template->set('results', $data);
		// $this->template->set('data_detail', $data_detail);
		$this->template->set('status', $this->status);
		$this->template->page_icon('fa fa-list');
		$this->template->title('Expense Report');
		$this->template->render('index');
	}

	// create
	public function create()
	{
		$data_budget 	= $this->All_model->GetComboBudget('', 'EXPENSE', date('Y'));
		$data_pc 		= $this->All_model->GetPettyCashCombo();
		$data_coa 		= $this->Coa_expense_model->GetDataWithJenis('Expense');
		$coa_field 		= $data_coa->coa;
		$coa_array 		= explode(';', $coa_field);
		$option_coa 	= $this->All_model->GetListCoa($coa_array);

		$this->template->set('data_pc', $data_pc);
		$this->template->set('data_budget', $data_budget);
		$this->template->set('data_coa', $data_coa);
		$this->template->set('option_coa', $option_coa);

		$this->template->render('form');
	}

	// edit
	public function edit($id)
	{
		$data 			= $this->Expense_model->GetDataHeader($id);
		$data_detail	= $this->Expense_model->GetDataDetail($data->no_doc);
		$data_budget 	= $this->All_model->GetComboBudget('', 'EXPENSE', date('Y'));
		$data_pc 		= $this->All_model->GetPettyCashCombo();
		$data_coa 		= $this->Coa_expense_model->GetDataWithJenis('Expense');
		$coa_field 		= $data_coa->coa;
		$coa_array 		= explode(';', $coa_field);
		$option_coa 	= $this->All_model->GetListCoa($coa_array);

		$this->template->set('option_coa', $option_coa);
		$this->template->set('data_pc', $data_pc);
		$this->template->set('data_budget', $data_budget);
		$this->template->set('data_detail', $data_detail);
		$this->template->set('status', $this->status);
		$this->template->set('data', $data);
		$this->template->set('stsview', '');
		$this->template->page_icon('fa fa-list');
		$this->template->render('form');
	}

	// view
	public function view($id)
	{
		$data = $this->Expense_model->GetDataHeader($id);
		$data_detail	= $this->Expense_model->GetDataDetail($data->no_doc);
		$data_budget = $this->All_model->GetComboBudget('', 'EXPENSE', date('Y'));
		$data_pc = $this->All_model->GetPettyCashCombo();

		$get_exp_kasbon = $this->db->select('id_kasbon')->get_where('tr_expense_detail', ['no_doc' => $data->no_doc, 'id_kasbon <>' => ''])->result_array();
		$data_coa 		= $this->Coa_expense_model->GetDataWithJenis('Expense');
		$coa_field 		= $data_coa->coa;
		$coa_array 		= explode(';', $coa_field);
		$option_coa 	= $this->All_model->GetListCoa($coa_array);

		$this->template->set('option_coa', $option_coa);

		$this->template->set('data_pc', $data_pc);
		$this->template->set('data_budget', $data_budget);
		$this->template->set('data_detail', $data_detail);
		$this->template->set('status', $this->status);
		$this->template->set('data', $data);
		$this->template->set('stsview', 'view');
		$this->template->set('data_exp_kasbon', $get_exp_kasbon);
		$this->template->page_icon('fa fa-list');
		$this->template->render('form');
	}
	// print
	public function expense_print($id)
	{
		$response = $this->Expense_model->GetDataHeader($id);
		$data_detail	= $this->Expense_model->GetDataDetail($response->no_doc);
		$data = array(
			'status'		=> $this->status,
			'data_detail'	=> $data_detail,
			'data'			=> $response,
		);
		$this->load->view('expense_print', $data);
	}
	public function expense_pettycash_print($id)
	{
		$response = $this->Expense_model->GetDataHeader($id);
		$data_detail	= $this->Expense_model->GetDataDetail($response->no_doc);
		$data = array(
			'status'		=> $this->status,
			'data_detail'	=> $data_detail,
			'data'			=> $response,
		);
		$this->load->view('expense_pettycash_print', $data);
	}
	public function list_expense_approval()
	{
		$this->template->set('status', $this->status);
		$this->template->page_icon('fa fa-list');
		$this->template->title('Expense Approval By Finance');
		$this->template->render('index_approval');
	}

	public function list_expense_approval_manage()
	{

		// $data = $this->Expense_model->GetListData('sts_finance = "1"');

		$this->db->select('a.*, IF(SUM(b.total_harga) IS NULL, 0, SUM(b.total_harga)) as nominal, c.username as nmuser, d.username as nmapproval');
		$this->db->from('tr_expense a');
		$this->db->join('tr_expense_detail b', 'b.no_doc = a.no_doc', 'left');
		$this->db->join('users c', 'a.nama=c.username', 'left');
		$this->db->join('users d', 'a.approval=d.username', 'left');
		$this->db->where('a.status', 0);
		$this->db->where('a.sts_finance', '1');
		$this->db->where('b.id_kasbon IS NULL');
		$this->db->group_by('a.no_doc');
		$data = $this->db->get()->result();

		$this->template->set('status', $this->status);
		$this->template->set('data', $data);
		$this->template->page_icon('fa fa-list');
		$this->template->title('Expense Approval By Management');
		$this->template->render('index_approval_manage');
	}
	public function expense_list_all()
	{
		// $data = $this->Expense_model->GetListData();
		// $data_detail = $this->Expense_model->GetListDataAll();
		// $this->template->page_icon('fa fa-list');
		$this->template->title('Expense Report List');
		$this->template->set('status', $this->status);
		$this->template->set(['all' => 1]);
		// $this->template->set('results', $data);
		// $this->template->set('data_detail', $data_detail);
		$this->template->render('index');
	}

	public function approval($id)
	{
		$data 			= $this->Expense_model->GetDataHeader($id);
		$data_detail 	= $this->Expense_model->GetDataDetail($data->no_doc);
		$data_budget 	= $this->All_model->GetComboBudget('', 'EXPENSE', date('Y'));
		$get_exp_kasbon = $this->db->select('id_kasbon')->get_where('tr_expense_detail', ['no_doc' => $data->no_doc, 'id_kasbon <>' => ''])->result_array();
		$data_coa 		= $this->Coa_expense_model->GetDataWithJenis('Expense');
		$coa_field 		= $data_coa->coa;
		$coa_array 		= explode(';', $coa_field);
		$option_coa 	= $this->All_model->GetListCoa($coa_array);
		$this->template->set('option_coa', $option_coa);
		$this->template->set('data_budget', $data_budget);
		$this->template->set('data_detail', $data_detail);
		$this->template->set('status', $this->status);
		$this->template->set('data', $data);
		if (!empty($get_exp_kasbon)) {
			$this->template->set('data_exp_kasbon', $get_exp_kasbon);
		}
		$this->template->set('stsview', 'approval');
		$this->template->page_icon('fa fa-list');
		if ($data->pettycash != "") {
			$data_budget = $this->All_model->GetPettyCashComboCoa($data->pettycash);
			$data_pc = $this->All_model->GetOneTable('ms_petty_cash', '', 'nama');
			$this->template->set('data_pc', $data_pc);
			$this->template->set('data_budget', $data_budget);
			$this->template->render('form_pc');
		} else {
			$this->template->render('form');
		}
	}

	// approve
	public function approve($id = '')
	{


		$result = false;
		if ($id != "") {
			$get_expense 		= $this->db->get_where('tr_expense', ['id' => $id])->row();
			$get_expense_detail = $this->db->get_where('tr_expense_detail', ['no_doc' => $get_expense->no_doc])->result();

			if ($get_expense->sts_finance == '0') {
				$arr_update = [
					'sts_finance' => '1',
					'app_finance_date' => date('Y-m-d H:i:s')
				];

				$update_expense = $this->db->update('tr_expense', $arr_update, ['id' => $id]);
				if ($update_expense) {
					$result = true;
				}
			} else {
				if ($get_expense->id_kasbon != null && $get_expense->kurang_bayar > 0) {
					$data = array(
						array(
							'id' => $id,
							'status' => 1,
							'st_reject' => "",
							'approved_by' => $this->auth->user_name(),
							'approved_on' => date("Y-m-d h:i:s")
						)
					);
				} else if ($get_expense->id_kasbon != null && $get_expense->kurang_bayar == null) {
					$data = array(
						array(
							'id' => $id,
							'status' => 3,
							'st_reject' => "",
							'approved_by' => $this->auth->user_name(),
							'approved_on' => date("Y-m-d h:i:s")
						)
					);
				} else if ($get_expense->id_kasbon != null && $get_expense->lebih_bayar != null) {
					$data = array(
						array(
							'id' => $id,
							'status' => 3,
							'st_reject' => "",
							'approved_by' => $this->auth->user_name(),
							'approved_on' => date("Y-m-d h:i:s")
						)
					);
				} else {
					$data = array(
						array(
							'id' => $id,
							'status' => 1,
							'st_reject' => "",
							'approved_by' => $this->auth->user_name(),
							'approved_on' => date("Y-m-d h:i:s")
						)
					);
				}
				$result = $this->Expense_model->update_batch($data, 'id');

				$nilai_expense = 0;
				foreach ($get_expense_detail as $item) {
					$nilai_expense += $item->expense;
					$detail = array(
						'status' => 2,
					);
					$this->db->update('tr_expense_detail', $detail, ['id' => $item->id]);
				}

				// if ($get_expense->pettycash !== '' && $get_expense->pettycash !== null) {
				// 	$get_pettycash = $this->db->get_where('ms_petty_cash', ['nama' => $get_expense->pettycash])->row();

				// 	$nilai_update_pettycash = ($get_pettycash->budget - $nilai_expense);

				// 	$this->db->update('ms_petty_cash', ['budget' => $nilai_update_pettycash], ['id' => $get_pettycash->id]);
				// 	$this->db->update('tr_pengembalian_expense', [
				// 		'status' => 1,
				// 		'app_by' => $this->auth->user_id(),
				// 		'app_date' => date('Y-m-d H:i:s')
				// 	], [
				// 		'id_expense_pettycash' => $get_expense->no_doc
				// 	]);
				// }

				$keterangan     = "SUKSES, Approve data " . $id;
				$status         = 1;
				$nm_hak_akses   = $this->managePermission;
				$kode_universal = $id;
				$jumlah 		= 1;
				$sql            = $this->db->last_query();
				simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
			}
		}
		$param = array(
			'save' => $result,
			'id' => $id
		);
		echo json_encode($param);
	}

	// save
	public function save()
	{
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

		//proses utama update tr_expense
		$this->db->trans_begin();
		if ($id != "") {
			$data = array(
				'tgl_doc' => $tgl_doc,
				'jumlah' => $total_expense,
				'informasi' => $informasi,
				'bank_id' => $bank_id,
				'accnumber' => $accnumber,
				'status' => 0,
				'accname' => $accname,
				'pettycash' => $pettycash,
				'tipe_pengembalian' => $pengembalian,
				'tipe_penggantian' => $penggantian,
				'st_reject' => null,
				'modified_by' => $this->auth->user_name(),
				'modified_on' => date("Y-m-d h:i:s")
			);
			$this->db->update('tr_expense', $data, ['id' => $id]);

			$this->db->delete('tr_expense_detail', ['no_doc' => $this->auth->user_id()]);
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
								}
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
								}
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
						}
					}
				}
			}

			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$result = FALSE;
			} else {
				$this->db->trans_commit();
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

			if (!empty($_FILES['bukti_pengembalian']['name'][0])) {
				foreach ($_FILES['bukti_pengembalian']['name'] as $index => $name) {
					$tmpName = $_FILES['bukti_pengembalian']['tmp_name'][$index];
					$filePath = $uploadDirectory . basename($name);

					if (move_uploaded_file($tmpName, $filePath)) {
						$pathBuktiPengembalian[] = $filePath;
					} else {
						echo "Gagal mengunggah file: $name<br>";
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
			} else {
				$kurang_bayar 	= null;
				$lebih_bayar	= $grand_total;
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
				'pettycash' 			=> $pettycash,
				'approval' 				=> $approval,
				'status' 				=> 0,
				'jumlah' 				=> $total_expense,
				'tipe_penggantian' 		=> $penggantian,
				'tipe_pengembalian' 	=> $pengembalian,
				'bon_bukti' 			=> $bonBukti,
				'bukti_pengembalian' 	=> $buktiPengembalian,
				'lebih_bayar' 			=> $lebih_bayar ?: null,
				'kurang_bayar' 			=> $kurang_bayar ?: null,
				'id_kasbon'				=> $no_doc_kasbon ?: null,
				'created_by' 			=> $this->auth->user_name(),
				'created_on' 			=> date("Y-m-d h:i:s")
			);

			$insert_expense = $this->db->insert('tr_expense', $data);

			if (!$insert_expense) {
				print_r($this->db->error($insert_expense));
				exit;
			}

			$this->db->delete('tr_expense_detail', ['no_doc' => $this->auth->user_id()]);

			// jika detail_id tidak kosong
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
							}
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
								// 'doc_file' => $filenames,
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
					}
				}
			}

			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$result = FALSE;
			} else {
				$this->db->trans_commit();
				$result = TRUE;
			}
		}

		//proses parsing data
		$param = array(
			'save' => $result,
			'id' => $id
		);
		echo json_encode($param);
	}

	// delete
	public function delete($id)
	{
		$this->db->trans_begin();
		$data = $this->Expense_model->GetDataHeader($id);

		$get_expense_detail = $this->db->get_where('tr_expense_detail', ['no_doc' => $data->no_doc])->result();
		foreach ($get_expense_detail as $item) {
			if ($item->kasbon_pr_non_po_pett !== null) {
				$this->db->update('tr_kasbon', ['id_expense_pett_pr_non_po' => null], ['id_expense_pett_pr_non_po' => $data->no_doc]);
			}
			if ($item->id_expense_bayar_sisa !== null) {
				$this->db->update('tr_expense', ['expense_id_kembalian' => null], ['expense_id_kembalian' => $data->no_doc]);
				$this->db->delete('tr_pengembalian_expense', ['id_expense_pettycash' => $data->no_doc]);
			}
		}

		$this->All_model->dataDelete('tr_expense_detail', array('no_doc' => $data->no_doc));
		$this->All_model->dataDelete('tr_expense', array('no_doc' => $data->no_doc));
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$result = FALSE;
		} else {
			$this->db->trans_commit();
			$result = TRUE;
		}
		$param = array('delete' => $result);
		echo json_encode($param);
	}

	function cekbudget()
	{
		$dtl		= $this->input->post("dtl");
		$divisi		= $this->input->post("divisi");

		$tanggal	= $this->input->post("tgl_doc");
		$coa	= $this->input->post("coa");
		$tahun = date("Y", strtotime($tanggal));
		$data = $this->Expense_model->GetBudget($coa, $tahun);
		$param = array();
		if ($data !== false) {
			if ($dtl == '') {
				$bulan = date("n", strtotime($tanggal));
				$budget = 0;
				$terpakai = 0;
				for ($i = 1; $i <= $bulan; $i++) {
					$budget = ($budget + $data->{"bulan_" . $i});
					$terpakai = ($terpakai + $data->{"terpakai_bulan_" . $i});
				}
				$sisa = ($budget - $terpakai);
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
				$param = array(
					'budget' => 0,
					'terpakai' => 0,
					'sisa' => 0,
					'tipe' => '',
				);
			}
		}
		echo json_encode($param);
	}
	// list management transport
	public function transport_req_mgt()
	{
		$data = $this->Expense_model->GetListDataTransportRequest($this->auth->user_name());
		$this->template->set('results', $data);
		$this->template->set('status', $this->status);
		$this->template->page_icon('fa fa-list');
		$this->template->title('Persetujuan Managemen Penggantian Transport');
		$this->template->render('transport_req_mgt_list');
	}

	// list finance transport
	public function transport_req_fin()
	{
		$this->template->page_icon('fa fa-list');
		$this->template->title('Pengecekan Finance Penggantian Transport');
		$this->template->render('transport_req_fin_list');
	}
	// list pengajuan transport
	public function transport_req_all()
	{
		$status = array("0" => "Baru", "1" => "Disetujui", "2" => "Selesai", "3" => "Selesai", "9" => "Ditolak");
		$data = $this->Expense_model->GetListDataTransportRequest();
		$data_detail = $this->Expense_model->GetListDataTransportRequestAll();
		$this->template->set('results', $data);
		$this->template->set('data_detail', $data_detail);
		$this->template->set('status', $status);
		$this->template->page_icon('fa fa-list');
		$this->template->title('Pengajuan  Transport');
		$this->template->render('transport_req_all');
	}

	// list pengajuan transport
	public function transport_req()
	{
		$this->template->page_icon('fa fa-list');
		$this->template->title('Pengajuan Penggantian Transport');
		$this->template->render('transport_req_list');
	}
	// transport pengajuan create
	public function transport_req_create()
	{
		$this->template->set('mod', '');
		$this->template->render('transport_req_form');
	}

	// transport req save
	public function transport_req_save()
	{
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

		$this->db->trans_begin();
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
				'accname' => $accname,
				'jumlah_expense' => ($jumlah_expense),
				'modified_by' => $this->auth->user_name(),
				'modified_on' => date("Y-m-d h:i:s")
			);
			$result = $this->All_model->dataUpdate('tr_transport_req', $data, array('id' => $id));
			$result = $this->All_model->dataUpdate('tr_transport', array('no_req' => '', 'status' => '0'), array('no_req' => $no_doc));
			if (!empty($id_transport)) {
				foreach ($id_transport as $keys => $val) {
					$result = $this->All_model->dataUpdate('tr_transport', array('no_req' => $no_doc, 'status' => '1'), array('id' => $val));
				}
			}
			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
			} else {
				$this->db->trans_commit();
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
					$result = $this->All_model->dataUpdate('tr_transport', array('no_req' => $no_doc, 'status' => '1'), array('id' => $val));
				}
			}
			if (is_numeric($id)) {
				$result         = TRUE;
			} else {
				$result = FALSE;
			}
			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
			} else {
				$this->db->trans_commit();
			}
		}
		$param = array(
			'save' => $result,
			'id' => $id
		);
		echo json_encode($param);
	}

	// transport req edit
	public function transport_req_edit($id, $mod = '')
	{
		$data = $this->Expense_model->GetDataTransportReq($id);
		$data_detail = $this->Expense_model->GetDataTransportInReq($data->no_doc);

		$this->template->set('data_detail', $data_detail);
		$this->template->set('status', $this->status);
		$this->template->set('data', $data);
		$this->template->set('mod', $mod);
		$this->template->set('stsview', '');
		$this->template->page_icon('fa fa-list');
		$this->template->title('Pengajuan Transport');
		$this->template->render('transport_req_form');
	}
	public function transport_req_print($id)
	{
		$results = $this->Expense_model->GetDataTransportReq($id);
		$data_detail = $this->Expense_model->GetDataTransportInReq($results->no_doc);
		$data = array(
			'title'			=> 'Print Transportasi Request',
			'stsview'		=> 'print',
			'data_detail'	=> $data_detail,
			'data'			=> $results
		);
		$this->load->view('transport_req_print', $data);
	}
	// transport req view
	public function transport_req_view($id, $mod = '')
	{
		$data = $this->Expense_model->GetDataTransportReq($id);
		$data_detail = $this->Expense_model->GetDataTransportInReq($data->no_doc);
		$this->template->set('data_detail', $data_detail);
		$this->template->set('status', $this->status);
		$this->template->set('data', $data);
		$this->template->set('mod', $mod);
		$this->template->set('stsview', 'view');
		$this->template->page_icon('fa fa-list');
		$this->template->title('Pengajuan Transport');
		$this->template->render('transport_req_form');
	}

	// list transport
	public function transport()
	{
		$this->template->page_icon('fa fa-list');
		$this->template->title('Transportasi');
		$this->template->render('transport_list');
	}

	// transport create
	public function transport_create()
	{
		// $data_departement = $this->All_model->GetDeptCombo();
		// $this->template->title('Pengajuan Transport');
		// $this->template->set('data_departement', $data_departement);
		$list_coa_transport = $this->Expense_model->getCoaTransport();

		$data = [
			'list_coa' => $list_coa_transport
		];
		$this->template->set($data);
		$this->template->render('transport_form');
	}

	// transport save
	public function transport_save()
	{
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

		// print_r($tgl_doc);

		$valid_photo = 1;
		$msg = '';

		$this->db->trans_begin();
		$config['upload_path'] = 'assets/expense/';
		$config['allowed_types'] = 'jpg|jpeg|png|pdf';
		// $config['max_size'] = 5120000;
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
			// else {
			// 	print_r($this->upload->display_errors());
			// 	exit;
			// }
		}

		if ($valid_photo == 1) {
			if ($id !== "") {
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
			}

			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$result = FALSE;

				$msg = 'Data gagal di simpan !';
			} else {
				$this->db->trans_commit();
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
		echo json_encode($param);
	}

	public function get_list_req_transport($nama, $departement, $date1, $date2)
	{
		$data	= $this->db->query("SELECT * FROM tr_transport WHERE nama='" . $nama . "' and tgl_doc between '" . $date1 . "' and '" . $date2 . "' and (no_req ='' or no_req is null) order by tgl_doc")->result();

		// print_r("SELECT * FROM tr_transport WHERE nama='" . $nama . "' and departement='" . $departement . "' and tgl_doc between '" . $date1 . "' and '" . $date2 . "' and (no_req ='' or no_req is null) order by tgl_doc");
		echo json_encode($data);
		die();
	}
	public function get_transport($nama, $departement)
	{
		$data = $this->All_model->GetOneTable('tr_transport', array('nama' => $nama, 'departement' => $departement, 'status' => '1'), 'tgl_doc');
		echo json_encode($data);
		die();
	}

	// transport edit
	public function transport_edit($id)
	{
		$data = $this->Expense_model->GetDataTransport($id);
		$get_list_coa = $this->Expense_model->getCoaTransport();
		$this->template->set('status', $this->status);
		$this->template->set('data', $data);
		$this->template->set('list_coa', $get_list_coa);
		$this->template->set('stsview', '');
		$this->template->page_icon('fa fa-list');
		$this->template->render('transport_form');
	}

	// transport view
	public function transport_view($id)
	{
		$data = $this->Expense_model->GetDataTransport($id);
		$get_list_coa = $this->Expense_model->getCoaTransport();
		$this->template->set('status', $this->status);
		$this->template->set('data', $data);
		$this->template->set('list_coa', $get_list_coa);
		$this->template->set('stsview', 'view');
		$this->template->page_icon('fa fa-list');
		$this->template->render('transport_form');
	}

	// transport fin approve
	public function transport_req_approve($id = '', $status)
	{
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
			$status         = 1;
			$nm_hak_akses   = $this->managePermission;
			$kode_universal = $id;
			$jumlah = 1;
			$sql            = $this->db->last_query();
			simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
		} else {
			$result = false;
			$id = 0;
		}
		$param = array(
			'save' => $result,
			'id' => $id
		);
		echo json_encode($param);
	}

	// transport approve
	public function transport_approve($id = '')
	{
		$result = false;
		if ($id != "") {
			$data = array(
				'id' => $id,
				'status' => 1,
			);
			$result = $this->All_model->dataUpdate('tr_transport', $data, array('id' => $id));
			$keterangan     = "SUKSES, Update data " . $id;
			$status         = 1;
			$nm_hak_akses   = $this->managePermission;
			$kode_universal = $id;
			$jumlah = 1;
			$sql            = $this->db->last_query();
			simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
		}
		$param = array(
			'save' => $result,
			'id' => $id
		);
		echo json_encode($param);
	}

	// transport delete
	public function transport_delete($id)
	{
		$this->db->trans_begin();
		$result = $this->All_model->dataDelete('tr_transport', array('id' => $id));
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
		} else {
			$this->db->trans_commit();
		}
		$param = array('delete' => $result);
		echo json_encode($param);
	}

	// transport delete
	public function transport_req_delete($id)
	{
		$this->db->trans_begin();
		$data = $this->Expense_model->GetDataTransportReq($id);
		$this->All_model->dataUpdate('tr_transport', array('status' => 0, 'no_req' => ''), array('no_req' => $data->no_doc));
		$result = $this->All_model->dataDelete('tr_transport_req', array('id' => $id));
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
		} else {
			$this->db->trans_commit();
		}
		$param = array('delete' => $result);
		echo json_encode($param);
	}

	// list petty_cash
	public function petty_cash()
	{
		// $data = $this->Expense_model->GetListData(array('nama' => $this->auth->user_name(), 'pettycash != ' => ''));
		// print_r($data);
		// exit;

		// $this->db->select('a.*, IF(SUM(b.total_harga) IS NULL, 0, SUM(b.total_harga)) as nominal, c.username as nmuser, d.username as nmapproval');
		// $this->db->from('tr_expense a');
		// $this->db->join('tr_expense_detail b', 'b.no_doc = a.no_doc', 'left');
		// $this->db->join('users c', 'a.nama=c.username', 'left');
		// $this->db->join('users d', 'a.approval=d.username', 'left');
		// $this->db->where('a.nama', $this->auth->user_name());
		// $this->db->where('a.pettycash !=', '');
		// $this->db->group_by('a.no_doc');
		// $this->db->order_by('a.created_on', 'DESC');
		// $data = $this->db->get()->result();

		// $this->template->set('results', $data);
		$this->template->set('status', $this->status);
		$this->template->page_icon('fa fa-list');
		$this->template->title('Petty Cash');
		$this->template->render('index_pc');
	}

	// create petty_cash
	public function create_pc()
	{
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
	}

	// edit petty_cash
	public function edit_pc($id)
	{
		$data = $this->Expense_model->GetDataHeader($id);
		$data_detail	= $this->Expense_model->GetDataDetail($data->no_doc);
		$data_budget = $this->All_model->GetPettyCashComboCoa($data->pettycash);
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
	}

	// view petty_cash
	public function view_pc($id)
	{
		$data = $this->Expense_model->GetDataHeader($id);
		$data_detail	= $this->Expense_model->GetDataDetail($data->no_doc);
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
	}
	function getcoabudget()
	{
		$coa = $this->input->post("coa");
		$coabudget = str_ireplace(";", "','", $coa);
		$datacombocoa = "";
		$data_budget = $this->db->query("select * from " . DBACC . ".coa_master where no_perkiraan in ('" . $coabudget . "')")->result();
		foreach ($data_budget as $keys) {
			$datacombocoa .= "<option value='" . $keys->no_perkiraan . "'>" . $keys->no_perkiraan . " - " . $keys->nama . "</option>";
		}
		echo $datacombocoa;
		die();
	}
	public function reject()
	{
		// Gunakan output JSON header agar client tahu ini respon JSON
		header('Content-Type: application/json');

		$id     = $this->input->post("id");
		$reason = $this->input->post("reason");
		$table  = $this->input->post("table");

		$result = false;

		$this->db->trans_begin();

		try {
			if (empty($id) || empty($table)) {
				throw new Exception('ID atau Tabel tidak ditemukan!');
			}

			$data = array(
				'status'                => 9,
				'sts_finance'           => '0',
				'app_finance_date'      => null,
				'reject_reason_finance' => $reason
			);

			$this->db->where('id', $id);
			$update = $this->db->update($table, $data);

			if (!$update) {
				throw new Exception($this->db->error()['message']);
			}

			// Jika semua oke, commit transaksinya
			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				throw new Exception('Transaction Failed!');
			} else {
				$this->db->trans_commit();
				$result = true;
				$message = "Berhasil mereject data.";
			}
		} catch (Exception $e) {
			$this->db->trans_rollback();
			$result = false;
			$message = $e->getMessage();
			http_response_code(400); // Bad Request atau 500
		}

		$param = array(
			'save'    => $result,
			'id'      => $id,
			'message' => $message ?? ''
		);

		echo json_encode($param);
	}

	public function reject_manage()
	{
		$result = false;
		$id		= $this->input->post("id");
		$reason	= $this->input->post("reason");
		$table	= $this->input->post("table");
		if ($id != "") {
			$data = array(
				'status' => 9,
				'sts_finance' => '0',
				'app_finance_date' => null,
				'reject_reason_finance' => $reason,
				'sts_reject' => '1',
				'sts_reject_manage' => '1',
				'reject_reason' => $reason
			);
			$result = $this->All_model->dataUpdate($table, $data, array('id' => $id));
			$keterangan     = "SUKSES, Reject data " . $id;
			$status         = 1;
			$nm_hak_akses   = $this->managePermission;
			$kode_universal = $id;
			$jumlah = 1;
			$sql            = $this->db->last_query();
			simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
		}
		$param = array(
			'save' => $result,
			'id' => $id
		);
		echo json_encode($param);
	}

	public function review($id)
	{
		// $controller			= ucfirst(strtolower($this->uri->segment(1)));
		// $Arr_Akses			= getAcccesmenu($controller);
		$data = $this->Expense_model->GetDataHeader($id);
		$data_detail	= $this->Expense_model->GetDataDetail($data->no_doc);
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
			'data'	    	=> $data,
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
		// $this->template('expense_review', $data);
	}

	public function return_confirm($id = '')
	{
		$result = false;
		$data_session	= $this->session->userdata;
		$dateTime = date('Y-m-d H:i:s');
		$UserName = $data_session['app_session']['username'];
		if ($id != "") {
			$transfer_coa_bank	= $this->input->post("transfer_coa_bank");
			$transfer_tanggal	= $this->input->post("transfer_tanggal");
			$transfer_jumlah	= $this->input->post("transfer_jumlah");
			//			$transferfile		= $this->input->post("transferfile");

			$get_expense = $this->db->get_where('tr_expense', ['id' => $id])->row();
			$get_pengembalian_expense = $this->db->get_where('tr_pengembalian_expense', ['no_doc' => $get_expense->no_doc])->result();

			$nilai_on_kembali = 0;
			foreach ($get_pengembalian_expense as $item) {
				$nilai_on_kembali += $item->transfer_jumlah;
			}

			if ($get_expense->jumlah < 0) {
				$sisa_expense = ($get_expense->jumlah * -1);
			} else {
				$sisa_expense = $get_expense->jumlah;
			}

			$total_nilai = ($transfer_jumlah + $nilai_on_kembali);
			$valid = 1;
			if ($total_nilai > $sisa_expense) {
				$valid = 2;
			}

			if ($valid == 1) {
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
				if ($this->db->trans_status() === FALSE) {
					$result = FALSE;
					$this->db->trans_rollback();
				} else {
					$result = TRUE;
					$this->db->trans_commit();
				}
				// history('Approve data expense : ' . $id);
			}
		}
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

		$this->db->trans_begin();

		$this->db->delete('tr_expense_detail', ['id' => $id_detail]);

		$this->db->trans_commit();
	}

	public function del_detail_kasbon_non_po()
	{
		$id_detail = $this->input->post('id_detail');
		$id_kasbon_non_po = $this->input->post('id_kasbon_non_po');

		$this->db->trans_begin();

		$this->db->update('tr_kasbon', ['id_expense_pett_pr_non_po' => null], ['no_doc' => $id_kasbon_non_po]);
		$this->db->delete('tr_expense_detail', ['id' => $id_detail]);

		$this->db->trans_commit();
	}

	public function add_kasbon_pr()
	{
		$post = $this->input->post();

		$get_kasbon = $this->db->get_where('tr_kasbon', ['no_doc' => $post['no_doc']])->row();

		echo json_encode([
			'no_doc' => $get_kasbon->no_doc,
			'keperluan' => $get_kasbon->keperluan,
			'jumlah_kasbon' => $get_kasbon->jumlah_kasbon,
		]);
	}

	public function add_ganti_expense()
	{
		$post = $this->input->post();

		$get_expense = $this->db->get_where('tr_expense', ['no_doc' => $post['no_doc']])->row();

		$this->db->trans_start();

		$this->db->insert('tr_expense_detail', [
			'tanggal' => $post['tgl'],
			'no_doc' => $this->auth->user_id(),
			'qty' => 1,
			'harga' => $get_expense->jumlah,
			'total_harga' => $get_expense->jumlah,
			'status' => 0,
			'expense' => $get_expense->jumlah,
			'created_by' => $this->auth->user_name(),
			'created_on' => date('Y-m-d H:i:s'),
			'id_expense_bayar_sisa' => $post['no_doc']
		]);

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
		} else {
			$this->db->trans_commit();
		}

		$get_expensed_bayar = $this->db->get_where('tr_expense_detail', ['id_expense_bayar_sisa' => $post['no_doc']])->row();


		echo json_encode([
			'no_doc' => $get_expense->no_doc,
			'informasi' => $get_expense->informasi,
			'jumlah' => $get_expense->jumlah,
			'id_detail' => $get_expensed_bayar->id,
			'no_doc2' => $get_expensed_bayar->no_doc
		]);
	}

	public function refresh_list_kasbon_non_pr()
	{
		$post = $this->input->post();

		if ($post['no_doc'] !== '' && $post['no_doc'] !== null) {
			$data_kasbon_pr_pet = $this->db->get_where('tr_kasbon', ['no_doc !=' => $post['no_doc'], 'metode_pembayaran' => 2, 'id_expense_pett_pr_non_po' => null])->result();
		} else {
			$data_kasbon_pr_pet = $this->db->get_where('tr_kasbon', ['metode_pembayaran' => 2, 'id_expense_pett_pr_non_po' => null])->result();
		}

		$hasil = '';
		foreach ($data_kasbon_pr_pet as $item) {
			$hasil .= '<tr>';
			$hasil .= '<td class="text-center">' . $item->created_by . '</td>';
			$hasil .= '<td class="text-left">' . $item->no_doc . '</td>';
			$hasil .= '<td class="text-left">' . $item->keperluan . '</td>';
			$hasil .= '<td class="text-center">' . number_format($item->jumlah_kasbon) . '</td>';
			$hasil .= '<td class="text-center">
									<button type="button" class="btn btn-sm btn-success add_kasbon_pr" data-no_doc="' . $item->no_doc . '">Bayar</button>
								</td>';
			$hasil .= '</tr>';
		}

		echo $hasil;
	}

	public function refresh_list_expense_kembalian()
	{
		$post = $this->input->post();

		// if ($post['no_doc'] !== '' && $post['no_doc'] !== null) {
		// 	$this->db->select('a.*');
		// 	$this->db->from('tr_expense a');
		// 	$this->db->where('(a.tipe_pengembalian = 1 OR a.tipe_penggantian = 1)');
		// 	$this->db->where('a.status', 1);
		// 	$this->db->where('a.jumlah <>', 0);
		// 	$this->db->where('a.exp_pib', null);
		// 	$this->db->where('a.exp_inv_po', null);
		// 	$this->db->where('a.pettycash', null);
		// 	$this->db->where('a.expense_id_kembalian', null);
		// 	$this->db->where('a.no_doc <>', $post['no_doc']);
		// 	$data_expense_kembalian = $this->db->get()->result();
		// } else {
		// 	$this->db->select('a.*');
		// 	$this->db->from('tr_expense a');
		// 	$this->db->where('(a.tipe_pengembalian = 1 OR a.tipe_penggantian = 1)');
		// 	$this->db->where('a.status', 1);
		// 	$this->db->where('a.jumlah <>', 0);
		// 	$this->db->where('a.exp_pib', null);
		// 	$this->db->where('a.exp_inv_po', null);
		// 	$this->db->where('a.pettycash', null);
		// 	$this->db->where('a.expense_id_kembalian', null);
		// 	$data_expense_kembalian = $this->db->get()->result();
		// }

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

		$hasil = '';
		foreach ($data_expense_kembalian as $item) {
			$hasil .= '<tr>';
			$hasil .= '<td class="text-center">' . $item->created_by . '</td>';
			$hasil .= '<td class="text-left">' . $item->no_doc . '</td>';
			$hasil .= '<td class="text-left">' . $item->informasi . '</td>';
			$hasil .= '<td class="text-center">' . number_format($item->jumlah) . '</td>';
			$hasil .= '<td class="text-center">
									<button type="button" class="btn btn-sm btn-success add_ganti_expense" data-no_doc="' . $item->no_doc . '">Bayar</button>
								</td>';
			$hasil .= '</tr>';
		}

		echo $hasil;
	}

	public function del_detail_kembalian_expense()
	{
		$id_detail = $this->input->post('id_detail');
		$id_expense_kembalian = $this->input->post('id_expense_kembalian');

		$this->db->trans_begin();

		$this->db->update('tr_expense', ['expense_id_kembalian' => null], ['no_doc' => $id_expense_kembalian]);
		$this->db->delete('tr_expense_detail', ['id' => $id_detail]);

		$this->db->trans_commit();
	}

	public function copy_pr_doc()
	{
		$post = $this->input->post();

		$no_pr = $post['no_pr'];

		$this->db->select('a.id, a.no_pr, a.document');
		$this->db->from('rutin_non_planning_header a');
		$this->db->where('a.no_pr', $no_pr);
		$get_pr_dept = $this->db->get()->row();

		$this->db->select('a.no_pr, a.dokumen_pendukung');
		$this->db->from('tran_pr_header a');
		$this->db->where('a.no_pr', $no_pr);
		$get_pr_asset = $this->db->get()->row();

		$file_name = '';
		$doc_file = '';
		$to_doc_file = '';
		if (!empty($get_pr_dept)) {
			if (!empty($get_pr_dept->document)) {
				$doc_file = 'assets/pr/' . $get_pr_dept->document;
				$to_doc_file = 'assets/expense/' . $get_pr_dept->document;
				$file_name = $get_pr_dept->document;
			}
		}
		if (!empty($get_pr_asset)) {
			if (!empty($get_pr_asset->dokumen_pendukung)) {
				$doc_file = 'uploads/pr_asset/' . $get_pr_asset->dokumen_pendukung;
				$to_doc_file = 'assets/expense/' . $get_pr_asset->dokumen_pendukung;
				$file_name = $get_pr_asset->dokumen_pendukung;
			}
		}

		echo json_encode([
			'file_name' => $file_name,
			'doc_file' => $doc_file,
			'to_doc_file' => $to_doc_file
		]);
	}

	public function get_data_transport_input()
	{
		$this->Expense_model->get_data_transport_input();
	}
	public function get_data_transport_req_fin_list()
	{
		$this->Expense_model->get_data_transport_req_fin_list();
	}
	public function get_data_transport_req()
	{
		$this->Expense_model->get_data_transport_req();
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

		// 1. Panggil dari VIEW (Logic CASE WHEN sudah ada di dalam View)
		$this->db->from('v_kasbon_list');

		// Filter User (ID 7 dianggap Superadmin/Full Access)
		if ($this->auth->user_id() !== '7' && $this->auth->user_id() !== '202') {
			$this->db->where('nmuser_fix', $this->auth->user_name());
		}

		// Get total records (tanpa filter search)
		$count_all = $this->db->count_all_results('', false);

		// 2. Fitur Search (Sekarang nmuser_fix tinggal dipanggil namanya)
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('no_doc', $search);
			$this->db->or_like('tgl_doc', $search);
			$this->db->or_like('keperluan', $search);
			$this->db->or_like('nmuser_fix', $search); // Gak perlu ngetik subquery lagi
			$this->db->group_end();
		}

		$count_filter = $this->db->count_all_results('', false);

		// 3. Ordering
		// Sesuaikan index array dengan urutan kolom di view/datatable kamu
		$columns = [
			0 => 'no_doc', // Biasanya kolom 0 itu nomor urut, sesuaikan mappingnya
			1 => 'no_doc',
			2 => 'tgl_doc',
			3 => 'nmuser_fix',
			4 => 'keperluan'
		];

		if (isset($post['order'][0]['column'])) {
			$col_idx = $post['order'][0]['column'];
			$this->db->order_by($columns[$col_idx], $post['order'][0]['dir']);
		} else {
			$this->db->order_by('no_doc', 'desc');
		}

		// 4. Limit & Fetch
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

	// --- Helper Tombol Action ---
	private function _render_action_buttons($item)
	{
		$action = '';

		// View & Print
		if (has_permission($this->viewPermissionKasbon)) {
			if (!empty($item['approved_by'])) {
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
		$post = $this->input->post();
		$draw = intval($post['draw']);
		$length = $post['length'];
		$start = $post['start'];
		$search = $post['search']['value'];

		// SEKARANG KITA PAKAI VIEW
		$this->db->from('v_kasbon_list');

		if ($this->auth->user_id() !== '7' && $this->auth->user_id() !== '202') {
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
			$this->db->group_end();
		}

		$count_filter = $this->db->count_all_results('', false);

		$columns = array(
			0 => '',
			1 => 'a.no_doc',
			2 => 'a.tgl_doc',
			3 => 'b.nm_lengkap'
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
			$this->db->group_end();
		}

		$count_filter = $this->db->count_all_results('', false);

		$columns = array(
			0 => '',
			1 => 'a.no_doc',
			2 => 'a.tgl_doc',
			3 => 'b.nm_lengkap'
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

		$this->db->select('a.*, b.nm_lengkap as nmuser, c.username as nmapproval');
		$this->db->from('tr_expense a');
		$this->db->join('users b', 'a.nama=b.username', 'left');
		$this->db->join('users c', 'a.approval=c.username', 'left');
		if (empty($all)) {
			$this->db->where('a.pettycash', null);
			$this->db->where('a.exp_pib', null);
		}

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
			$this->db->or_like('c.username', $search, 'both');
			$this->db->or_like('a.approved_on', $search, 'both');
			$this->db->or_like('a.informasi', $search, 'both');
			$this->db->group_end();
		}

		$db_clone = clone $this->db;
		$count_filter = $db_clone->count_all_results();

		$column_order = [
			0 => '',
			1 => 'a.no_doc',
			2 => 'a.tgl_doc',
			3 => 'b.nm_lengkap',
			4 => 'c.username',
			5 => 'a.approved_on'
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

		$get_data = $this->db->get()->result_array();

		$no = (0 + $start);
		$hasil = [];

		foreach ($get_data as $item) :
			$no++;

			$status = '<span class="badge bg-yellow">Baru</span>';
			if ($item['status'] == '1') {
				$status = '<span class="badge bg-green">Disetujui</span>';
			}
			if ($item['status'] == '2') {
				$status = '<span class="badge bg-green">Disetujui Management</span>';
			}
			if ($item['status'] == '3') {
				$status = '<span class="badge bg-green">Selesai</span>';
			}
			if ($item['status'] == '9') {
				$status = '<span class="badge bg-red">Ditolak</span>';
			}

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

			$hasil[] = [
				'no' => $no,
				'no_doc' => $item['no_doc'],
				'tgl_doc' => $item['tgl_doc'],
				'nama' => $item['nmuser'],
				'approval' => $item['nmapproval'],
				'approval_date' => $item['approved_on'],
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

		$this->db->select('a.*, b.nm_lengkap as nmuser, c.username as nmapproval');
		$this->db->from('tr_expense a');
		$this->db->join('users b', 'a.nama=b.username', 'left');
		$this->db->join('users c', 'a.approval=c.username', 'left');
		$this->db->where('a.pettycash IS NOT NULL');
		$this->db->where('a.exp_pib', null);

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
			$this->db->or_like('c.username', $search, 'both');
			$this->db->or_like('a.informasi', $search, 'both');
			$this->db->group_end();
		}

		$db_clone = clone $this->db;
		$count_filter = $db_clone->count_all_results();

		// $column_order = [
		// 	0 => '',
		// 	1 => 'a.no_doc',
		// 	2 => 'a.tgl_doc',
		// 	3 => 'b.nm_lengkap',
		// 	4 => 'c.username',
		// 	5 => 'a.approved_on'
		// ];

		// if (isset($post['order']) && !empty($post['order'])) {
		// 	$column_index = $post['order'][0]['column']; // Mendapatkan index kolom yang diurutkan
		// 	$column_name = $column_order[$column_index]; // Menentukan nama kolom berdasarkan index
		// 	$column_dir = $post['order'][0]['dir']; // Mendapatkan arah pengurutan (ASC/DESC)
		// 	$this->db->order_by($column_name, $column_dir);
		// } else {
		// 	$this->db->order_by('a.created_on', 'desc');
		// }

		$this->db->order_by('a.created_on', 'desc');

		$this->db->limit($length, $start);

		$get_data = $this->db->get()->result_array();

		$no = (0 + $start);
		$hasil = [];

		foreach ($get_data as $item) :
			$no++;

			$status = '<span class="badge bg-yellow">Baru</span>';
			if ($item['status'] == '1') {
				$status = '<span class="badge bg-green">Disetujui</span>';
			}
			if ($item['status'] == '2') {
				$status = '<span class="badge bg-green">Disetujui Management</span>';
			}
			if ($item['status'] == '3') {
				$status = '<span class="badge bg-green">Selesai</span>';
			}
			if ($item['status'] == '9') {
				$status = '<span class="badge bg-red">Ditolak</span>';
			}

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

			$hasil[] = [
				'no' => $no,
				'no_doc' => $item['no_doc'],
				'tgl_doc' => $item['tgl_doc'],
				'nama' => $item['nmuser'],
				'approval' => $item['nmapproval'],
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
		$this->db->group_by('a.no_doc');

		$count_all = $this->db->count_all_results();

		$this->db->select('a.*, IF(SUM(b.total_harga) IS NULL, 0, SUM(b.total_harga)) as nominal, c.username as nmuser, d.username as nmapproval');
		$this->db->from('tr_expense a');
		$this->db->join('tr_expense_detail b', 'b.no_doc = a.no_doc', 'left');
		$this->db->join('users c', 'a.nama=c.username', 'left');
		$this->db->join('users d', 'a.approval=d.username', 'left');
		$this->db->where('a.status', 0);
		$this->db->where('b.id_kasbon IS NULL');

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
		$this->db->join('tr_expense_detail b', 'b.no_doc = a.no_doc AND b.id_kasbon IS NULL', 'left');
		$this->db->join('users c', 'a.nama = c.username', 'left');
		$this->db->where('a.status', 0);
		$this->db->where('a.sts_finance', '1');
		$count_all = (int) $this->db->get()->row()->cnt;

		// Total setelah filter
		$this->db->select('COUNT(DISTINCT a.no_doc) as cnt');
		$this->db->from('tr_expense a');
		$this->db->join('tr_expense_detail b', 'b.no_doc = a.no_doc AND b.id_kasbon IS NULL', 'left');
		$this->db->join('users c', 'a.nama = c.username', 'left');
		$this->db->where('a.status', 0);
		$this->db->where('a.sts_finance', '1');
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
		$this->db->select('a.id, a.no_doc, a.tgl_doc, a.informasi, a.status,
			IFNULL(SUM(b.total_harga), 0) as nominal,
			c.username as nmuser');
		$this->db->from('tr_expense a');
		$this->db->join('tr_expense_detail b', 'b.no_doc = a.no_doc AND b.id_kasbon IS NULL', 'left');
		$this->db->join('users c', 'a.nama = c.username', 'left');
		$this->db->where('a.status', 0);
		$this->db->where('a.sts_finance', '1');
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
				'status'    => isset($status_label[$row->status]) ? $status_label[$row->status] : $row->status,
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
			"0" => "Baru",
			"1" => "Disetujui",
			"2" => "Disetujui Management",
			"3" => "Selesai",
			"9" => "Ditolak"
		);

		// Total semua record (tanpa filter)
		$this->db->select('COUNT(DISTINCT a.no_doc) as cnt');
		$this->db->from('tr_expense a');
		$this->db->join('tr_expense_detail b', 'b.no_doc = a.no_doc AND b.id_kasbon IS NULL', 'left');
		$this->db->join('users c', 'a.nama = c.username', 'left');
		$this->db->where('a.status', 0);
		$this->db->where('a.sts_finance', '0');
		$count_all = (int) $this->db->get()->row()->cnt;

		// Total setelah filter search
		$this->db->select('COUNT(DISTINCT a.no_doc) as cnt');
		$this->db->from('tr_expense a');
		$this->db->join('tr_expense_detail b', 'b.no_doc = a.no_doc AND b.id_kasbon IS NULL', 'left');
		$this->db->join('users c', 'a.nama = c.username', 'left');
		$this->db->where('a.status', 0);
		$this->db->where('a.sts_finance', '0');
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
		$this->db->select('a.id, a.no_doc, a.tgl_doc, a.informasi, a.status,
			IFNULL(SUM(b.total_harga), 0) as nominal,
			c.username as nmuser');
		$this->db->from('tr_expense a');
		$this->db->join('tr_expense_detail b', 'b.no_doc = a.no_doc AND b.id_kasbon IS NULL', 'left');
		$this->db->join('users c', 'a.nama = c.username', 'left');
		$this->db->where('a.status', 0);
		$this->db->where('a.sts_finance', '0');
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
				'status'    => isset($status_label[$row->status]) ? $status_label[$row->status] : $row->status,
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
}
