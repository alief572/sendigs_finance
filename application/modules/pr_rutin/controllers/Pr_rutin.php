<?php
if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}
/*
 * @author Harboens
 * @copyright Copyright (c) 2021
 *
 * This is controller for Trasaction Purchase Request Rutin
 */

$status = array();
class Pr_rutin extends Admin_Controller
{
	//Permission
	protected $viewPermission 	= 'PR_Rutin.View';
	protected $addPermission  	= 'PR_Rutin.Add';
	protected $managePermission = 'PR_Rutin.Manage';
	protected $deletePermission = 'PR_Rutin.Delete';
	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('pr_rutin/Pr_rutin_model', 'All/All_model'));
		$this->template->title('Purchase Request Rutin');
		$this->template->page_icon('fa fa-cubes');
		date_default_timezone_set('Asia/Bangkok');
		// $this->status = array("0" => "Baru", "1" => "Proses", "2" => "Selesai", "5" => "Selesai");
		$this->status = array("N" => "Waiting Approval", "Y" => "Close");
	}

	// list
	public function index()
	{
		$data = $this->Pr_rutin_model->GetListPrRutin();
		$inventory_type = $this->All_model->GetInventoryTypeCombo();
		$this->template->set('inventory_type', $inventory_type);
		$this->template->set('results', $data);
		$this->template->set('status', $this->status);
		$this->template->page_icon('fa fa-list');
		$this->template->title('PR Rutin');
		$this->template->render('pr_rutin_list');
	}

	// create
	public function create()
	{
		$data_material = $this->Pr_rutin_model->GetBudgetRutin();
		$this->template->set('data_material', $data_material);
		$this->template->render('pr_rutin_form');
	}

	// edit
	public function edit($id, $stsview = '')
	{
		$data = $this->Pr_rutin_model->GetDataPrRutin($id);
		$data_material	= $this->Pr_rutin_model->GetDataPrRutinDetail($data->so_number);
		$this->template->set('data_material', $data_material);
		$this->template->set('data', $data);
		$this->template->set('stsview', $stsview);
		$this->template->page_icon('fa fa-list');
		$this->template->render('pr_rutin_form');
	}

	// view
	public function view($id)
	{
		$data = $this->Pr_rutin_model->GetDataPrRutin($id);


		$data_material	= $this->Pr_rutin_model->GetDataPrRutinDetail($data->so_number);
		$this->template->set('data_material', $data_material);
		$this->template->set('stsview', 'view');
		$this->template->set('data', $data);
		$this->template->page_icon('fa fa-list');
		$this->template->render('pr_rutin_form');
	}

	// prints
	public function printout($id)
	{
		$data = $this->Pr_rutin_model->GetDataPrRutin($id);
		$data_material	= $this->Pr_rutin_model->GetDataPrRutinDetail($data->so_number);
		$this->template->set('data_material', $data_material);
		$this->template->set('data', $data);

		$this->load->library(array('Mpdf'));
		$mpdf = new mPDF('', '', '', '', '', '', '', '', '', '');
		$mpdf->SetImportUse();
		$mpdf->RestartDocTemplate();
		$show = $this->template->load_view('pr_rutin_print', $data);
		$this->mpdf->AddPage('L', 'A4', 'en');
		$this->mpdf->WriteHTML($show);
		$this->mpdf->Output();
	}

	// approve
	public function approve($id = '')
	{
		$result = false;
		if ($id !== "") {
			$result = $this->db->update('material_planning_base_on_produksi_detail', [
				'status_app' => 'Y'
			], [
				'so_number' => $id
			]);



			$keterangan     = "SUKSES, Approve data " . $id;
			$status         = 1;
			$nm_hak_akses   = $this->managePermission;
			$kode_universal = $id;
			$jumlah = 1;
			$sql            = $this->db->last_query();
			simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
		}
		$param = array(
			'save' => $result, 'id' => $id
		);
		echo json_encode($param);
	}

	// save
	public function save()
	{
		$id             = $this->input->post("id");
		$pr_date  		= $this->input->post("pr_date");
		$tgl_dibutuhkan = $this->input->post("tgl_dibutuhkan");
		$pr_info		= $this->input->post("pr_info");
		$detail_id		= $this->input->post("detail_id");
		$pr_no			= $this->input->post("pr_no");
		$this->db->trans_begin();
		if ($id != "") {
			$data = array(
				array(
					'id' => $id,
					'pr_date' => $pr_date,
					'tgl_dibutuhkan' => $tgl_dibutuhkan,
					'status' => 0,
				)
			);
			$result = $this->Pr_rutin_model->update_batch($data, 'id');
			$this->All_model->dataDelete('tr_pr_rutin_detail', array('doc_no' => $pr_no));
			if (!empty($detail_id)) {
				foreach ($detail_id as $keys) {
					$material_id		= $this->input->post("id_material_" . $keys);
					$material_qty		= $this->input->post("material_qty_" . $keys);
					$material_unit		= $this->input->post("material_unit_" . $keys);
					$material_stock		= $this->input->post("material_stock_" . $keys);
					$material_order		= $this->input->post("material_order_" . $keys);
					$material_price_ref	= $this->input->post("material_price_ref_" . $keys);
					$kurs				= $this->input->post("kurs_" . $keys);
					if ($material_order > 0) {
						$data_detail =  array(
							'doc_no' => $pr_no,
							'material_id' => $material_id,
							'material_qty' => $material_qty,
							'material_unit' => $material_unit,
							'material_stock' => $material_stock,
							'material_order' => $material_order,
							'material_price_ref' => $material_price_ref,
							'kurs' => $kurs,
							'created_by' => $this->auth->user_id(),
							'created_on' => date("Y-m-d h:i:s")
						);
						$this->All_model->dataSave('tr_pr_rutin_detail', $data_detail);
					}
				}
			}
			$keterangan     = "SUKSES, Edit data " . $id;
			$status         = 1;
			$nm_hak_akses   = $this->managePermission;
			$kode_universal = $id;
			$jumlah = 1;
			$sql            = $this->db->last_query();
			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
			} else {
				$this->db->trans_commit();
			}
			simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
		} else {
			$pr_no = $this->All_model->GetAutoGenerate('format_pr');
			$data =  array(
				'pr_no' => $pr_no,
				'pr_date' => $pr_date,
				'tgl_dibutuhkan' => $tgl_dibutuhkan,
				'status' => 0,
			);
			$id = $this->Pr_rutin_model->insert($data);
			if (!empty($detail_id)) {
				foreach ($detail_id as $keys) {
					$material_id		= $this->input->post("id_material_" . $keys);
					$material_qty		= $this->input->post("material_qty_" . $keys);
					$material_unit		= $this->input->post("material_unit_" . $keys);
					$material_stock		= $this->input->post("material_stock_" . $keys);
					$material_order		= $this->input->post("material_order_" . $keys);
					$material_price_ref	= $this->input->post("material_price_ref_" . $keys);
					$kurs				= $this->input->post("kurs_" . $keys);
					if ($material_order > 0) {
						$data_detail =  array(
							'doc_no' => $pr_no,
							'material_id' => $material_id,
							'material_qty' => $material_qty,
							'material_unit' => $material_unit,
							'material_stock' => $material_stock,
							'material_order' => $material_order,
							'material_price_ref' => $material_price_ref,
							'kurs' => $kurs,
							'created_by' => $this->auth->user_id(),
							'created_on' => date("Y-m-d h:i:s")
						);
						$this->All_model->dataSave('tr_pr_rutin_detail', $data_detail);
					}
				}
			}
			if (is_numeric($id)) {
				$keterangan     = "SUKSES, tambah data " . $id;
				$status         = 1;
				$nm_hak_akses   = $this->addPermission;
				$kode_universal = 'NewData';
				$jumlah         = 1;
				$sql            = $this->db->last_query();
				$result         = TRUE;
			} else {
				$keterangan     = "GAGAL, tambah data" . $id;
				$status         = 0;
				$nm_hak_akses   = $this->addPermission;
				$kode_universal = 'NewData';
				$jumlah         = 1;
				$sql            = $this->db->last_query();
				$result = FALSE;
			}
			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
			} else {
				$this->db->trans_commit();
			}
			simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
		}
		$param = array(
			'save' => $result, 'id' => $id
		);
		echo json_encode($param);
	}

	// delete
	public function delete($id)
	{
		$data = $this->Pr_rutin_model->GetDataPrRutin($id);
		$this->db->trans_begin();
		$data_old  = $this->Pr_rutin_model->find_by(array('id' => $id));
		$this->All_model->dataDelete('tr_pr_rutin_detail', array('doc_no' => $data->so_number));
		$result = $this->Pr_rutin_model->delete($id);
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
		} else {
			$this->db->trans_commit();
		}
		$param = array('delete' => $result);
		echo json_encode($param);
	}
	function list_approval_pr_stock()
	{
		$data = $this->Pr_rutin_model->GetListPrRutin(array('status_app' => 'N'));
		$inventory_type = $this->All_model->GetInventoryTypeCombo();
		$this->template->set('inventory_type', $inventory_type);
		$this->template->set('results', $data);
		$this->template->set('status', $this->status);
		$this->template->page_icon('fa fa-list');
		$this->template->title('Approval PR Stock');
		$this->template->render('pr_list_approval');
	}
}
