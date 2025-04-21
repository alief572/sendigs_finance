<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/*
 * @author Harboens
 * @copyright Copyright (c) 2020
 *
 * This is controller for Budget Rutin
 */

$status = array();
$waktu = array();
class Budget_periodik extends Admin_Controller
{
	//Permission
	protected $viewPermission 	= 'Pembayaran_Periodik.View';
	protected $addPermission  	= 'Pembayaran_Periodik.Add';
	protected $managePermission = 'Pembayaran_Periodik.Manage';
	protected $deletePermission = 'Pembayaran_Periodik.Delete';
	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('All/All_model', 'Budget_periodik/Budget_periodik_model'));
		$this->template->title('Master Pembayaran Rutin');
		$this->template->page_icon('fa fa-cubes');
		date_default_timezone_set('Asia/Bangkok');
		$this->waktu = array("bulan" => "bulan", "tahun" => "tahun");
	}

	public function index()
	{
		$this->auth->restrict($this->viewPermission);
		$data = $this->Budget_periodik_model->GetBudgetRutinGroup();
		$datdept  = $this->All_model->GetDeptCombo();
		$this->template->set('datdept', $datdept);
		$this->template->set('results', $data);
		$this->template->title('Master Pembayaran Periodik');
		$this->template->render('list');
	}

	public function create($key)
	{
		$this->auth->restrict($this->addPermission);
		$datdept  = $this->All_model->GetDeptCombo($key);
		$this->template->set('datdept', $datdept);
		//		$datcoa	= $this->All_model->GetBudgetComboCategory('PEMBAYARAN PERIODIK',date("Y"),$key);
		$datcoa	= $this->All_model->GetCoaCombo();
		$data	= $this->Budget_periodik_model->GetBudgetRutin($key);
		$callcoa = $this->All_model->GetComboBudget();
		$this->template->set('callcoa', $callcoa);
		$this->template->set('data_detail', $data);
		$this->template->set('datcoa', $datcoa);
		$this->template->set('waktu', $this->waktu);
		$this->template->title('Input Master Pembayaran Periodik');
		$this->template->render('input_form');
	}

	public function edit($key)
	{
		$this->auth->restrict($this->addPermission);
		$datdept  = $this->All_model->GetDeptCombo($key);
		$this->template->set('datdept', $datdept);
		//		$datcoa	= $this->All_model->GetBudgetComboCategory('PEMBAYARAN PERIODIK',date("Y"),$key);
		$datcoa	= $this->All_model->GetCoaCombo();
		$data	= $this->Budget_periodik_model->GetBudgetRutin($key);
		$callcoa = $this->All_model->GetComboBudget();
		$this->template->set('callcoa', $callcoa);
		$this->template->set('data_detail', $data);
		$this->template->set('datcoa', $datcoa);
		$this->template->set('waktu', $this->waktu);
		$this->template->title('Input Master Pembayaran Periodik');
		$this->template->render('input_form');
	}

	public function save_data()
	{

		$detail_id		= $this->input->post("detail_id");
		$coa       		= $this->input->post("coa");
		$nama           = $this->input->post("nama");
		$tipe  			= $this->input->post("tipe");
		$bln  			= $this->input->post("bln");
		$thn  			= $this->input->post("thn");
		$nilai			= $this->input->post("nilai");
		$keterangan		= $this->input->post("keterangan");
		$departement	= $this->input->post("departement");
		$kode_id	= $this->input->post("kode_id");

		$this->db->trans_begin();

		$delid = implode("','", $detail_id);
		$budgetcoa = array();
		$this->All_model->dataDelete('ms_budget_rutin', "id not in ('" . $delid . "') and departement='" . $departement . "'");
		for ($x = 0; $x < count($detail_id); $x++) {
			if ($tipe[$x] == 'bulan') {
				$tanggal = $bln[$x];
			} else {
				$tanggal = $thn[$x];
			}
			if ($detail_id[$x] != '') {
				$data = array(
					array(
						'id' => $detail_id[$x],
						'nama' => $nama[$x],
						'coa' => $coa[$x],
						'tipe' => $tipe[$x],
						'tanggal' => $tanggal,
						'nilai' => $nilai[$x],
						'keterangan' => $keterangan[$x],
						'kode_id' => $kode_id[$x],
						'departement' => $departement,
					)
				);
				$this->Budget_periodik_model->update_batch($data, 'id');
			} else {
				$data =  array(
					'nama' => $nama[$x],
					'coa' => $coa[$x],
					'tipe' => $tipe[$x],
					'tanggal' => $tanggal,
					'nilai' => $nilai[$x],
					'keterangan' => $keterangan[$x],
					'kode_id' => $kode_id[$x],
					'departement' => $departement,
				);
				$this->Budget_periodik_model->insert($data);
			}
		}

		//alokasi
		$detail_alokasi	= $this->input->post("detail_alokasi");
		$kode_detail	= $this->input->post("kode_detail");
		$coa_alokasi	= $this->input->post("coa_alokasi");
		$nilai_alokasi	= $this->input->post("nilai_alokasi");
		$this->All_model->dataDelete('ms_budget_rutin_alokasi', array('departement' => $departement));
		for ($x = 0; $x < count($kode_detail); $x++) {
			$dataalokasi =  array(
				'kode' => $kode_detail[$x],
				'coa' => $coa_alokasi[$x],
				'nilai' => $nilai_alokasi[$x],
				'departement' => $departement,
				'created_by' => $this->auth->user_id(),
				'created_on' => date('Y-m-d H:i:s')
			);
			$this->All_model->dataSave('ms_budget_rutin_alokasi', $dataalokasi);
		}

		if ($this->db->trans_status()) {
			$keterangan     = "SUKSES, tambah data ";
			$status         = 1;
			$nm_hak_akses   = $this->addPermission;
			$kode_universal = 'NewData';
			$jumlah         = $x;
			$sql            = $this->db->last_query();
			$result         = TRUE;
		} else {
			$keterangan     = "GAGAL, tambah data ";
			$status         = 0;
			$nm_hak_akses   = $this->addPermission;
			$kode_universal = 'NewData';
			$jumlah         = $x;
			$sql            = $this->db->last_query();
			$result = FALSE;
		}
		simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
		$this->db->trans_complete();
		$param = array(
			'save' => $result
		);
		echo json_encode($param);
	}

	function hapus_data($id)
	{
		$this->auth->restrict($this->deletePermission);
		if ($id != '') {
			$data = $this->All_model->GetOneData('ms_budget_rutin', array('id' => $id));
			$this->All_model->dataDelete('ms_budget_rutin_alokasi', array('kode' => $data->kode_id));
			$result = $this->All_model->dataDelete('ms_budget_rutin', array('id' => $id));
			$keterangan     = "SUKSES, Delete data Budget ";
			$status         = 1;
			$nm_hak_akses   = $this->deletePermission;
			$kode_universal = $id;
			$jumlah = 1;
			$sql            = $this->db->last_query();
		} else {
			$result = 0;
			$keterangan     = "GAGAL, Delete data Budget ";
			$status         = 0;
			$nm_hak_akses   = $this->deletePermission;
			$kode_universal = $id;
			$jumlah = 1;
			$sql            = $this->db->last_query();
		}
		simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
		$param = array(
			'delete' => $result,
			'idx' => $id
		);
		echo json_encode($param);
	}

	function proses_budget_periodik()
	{
		$this->auth->restrict($this->managePermission);
		$data = $this->Budget_periodik_model->GetBudgetRutin();
		$this->db->trans_begin();
		if (!empty($data)) {
			$this->db->query("update ms_budget set bulan_1=0,bulan_2=0, bulan_3=0, bulan_4=0, bulan_5=0, bulan_6=0, bulan_7=0, bulan_8=0, bulan_9=0, bulan_10=0, bulan_11=0, bulan_12=0,total=0 WHERE tahun='" . date("Y") . "' and kategori='PEMBAYARAN PERIODIK'");
			foreach ($data as $record) {

				$databudget['tipe'] = $record->tipe;
				$databudget['coa'] = $record->coa;
				$databudget['tanggal'] = $record->tanggal;
				$databudget['nilai'] = $record->nilai;
				$databudget['departement'] = $record->departement;
				$databudget['kode_id'] = $record->kode_id;
				$databudget['tahun'] = date("Y");
				if ($record->kode_id != '') {
					$dtalok = $this->db->query("select * from ms_budget_rutin_alokasi where kode='" . $record->kode_id . "'")->result();
					if (!empty($dtalok)) {
						foreach ($dtalok as $rdetail) {
							$databudget['coa'] = $rdetail->coa;
							$databudget['nilai'] = ($rdetail->nilai * $record->nilai / 100);
							$this->Budget_periodik_model->updatebudget($databudget);
						}
					} else {
						$this->Budget_periodik_model->updatebudget($databudget);
					}
				} else {
					$this->Budget_periodik_model->updatebudget($databudget);
				}
			}
		}
		if ($this->db->trans_status()) {
			$keterangan     = "SUKSES, proses data ";
			$status         = 1;
			$nm_hak_akses   = $this->addPermission;
			$kode_universal = 'UpdateData';
			$jumlah         = 1;
			$sql            = $this->db->last_query();
			$result         = TRUE;
		} else {
			$keterangan     = "GAGAL, proses data ";
			$status         = 0;
			$nm_hak_akses   = $this->addPermission;
			$kode_universal = 'UpdateData';
			$jumlah         = 1;
			$sql            = $this->db->last_query();
			$result = FALSE;
		}
		$this->db->trans_complete();
		simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
		$this->db->trans_complete();
		$param = array(
			'save' => $result
		);
		echo json_encode($param);
	}
}
