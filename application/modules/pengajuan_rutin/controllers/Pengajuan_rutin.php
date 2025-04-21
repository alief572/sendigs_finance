<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/*
 * @author Harboens
 * @copyright Copyright (c) 2020
 *
 * This is controller for Pengajuan Rutin
 */

$status = array();
$waktu = array();
class Pengajuan_rutin extends Admin_Controller
{
	//Permission
	protected $viewPermission 	= 'Pengajuan_Pembayaran_Rutin.View';
	protected $addPermission  	= 'Pengajuan_Pembayaran_Rutin.Add';
	protected $managePermission = 'Pengajuan_Pembayaran_Rutin.Manage';
	protected $deletePermission = 'Pengajuan_Pembayaran_Rutin.Delete';
	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('All/All_model', 'Pengajuan_rutin/Pengajuan_rutin_model'));
		$this->template->title('Master Pembayaran Rutin');
		$this->template->page_icon('fa fa-cubes');
		date_default_timezone_set('Asia/Bangkok');
		$this->waktu = array("bulan" => "bulan", "tahun" => "tahun");
	}

	public function index()
	{
		//        $this->auth->restrict($this->viewPermission);
		$departemen = '';
		$datauser = $this->All_model->GetInfoUser($this->auth->user_id());
		//		if($datauser) $departemen=$datauser->department_id;
		// $data = $this->Pengajuan_rutin_model->GetPengajuanRutin(array('a.created_by' => $this->auth->user_id()));
		
		$this->db->select('a.*, IF(SUM(b.nilai) IS NULL, 0, SUM(b.nilai)) as nilai_total, c.nama as nm_dept');
		$this->db->from('tr_pengajuan_rutin a');
		$this->db->join('tr_pengajuan_rutin_detail b', 'b.no_doc = a.no_doc', 'left');
		$this->db->join('ms_department c', 'c.id = a.departement', 'left');
		$this->db->where('a.created_by', $this->auth->user_id());
		$this->db->group_by('a.no_doc');
		$data = $this->db->get()->result();

		$datdept  = $this->All_model->GetDeptCombo($departemen);
		$data_detail = $this->Pengajuan_rutin_model->GetDataPengajuanRutinAll(array('a.created_by' => $this->auth->user_id()));
		$this->template->set('datdept', $datdept);
		$this->template->set('results', $data);
		$this->template->set('data_detail', $data_detail);
		$this->template->title('Pengajuan Pembayaran Periodik');
		$this->template->render('list');
	}

	public function app_list()
	{
		$departemen = '';
		//		$datauser=$this->All_model->GetInfoUser($this->auth->user_id());
		//		if($datauser) $departemen=$datauser->departemen;
		$data = $this->Pengajuan_rutin_model->GetPengajuanRutin(array('a.status' => 0));
		$datdept  = $this->All_model->GetDeptCombo();
		$this->template->set('datdept', $datdept);
		$this->template->set('results', $data);
		$this->template->title('Pengajuan Pembayaran Periodik');
		$this->template->render('app_list');
	}

	public function create($key)
	{
		$this->auth->restrict($this->addPermission);
		$datdept  = $this->All_model->GetDeptCombo($key);
		$this->template->set('datdept', $datdept);
		$this->template->title('Input Pengajuan Pembayaran Periodik');
		$this->template->set('type', 'add');
		$this->template->set('app', '');
		$this->template->render('input_form');
	}

	public function edit($id)
	{
		$data	= $this->Pengajuan_rutin_model->GetDataPengajuanRutin($id);
		if (!$data) {
			$this->template->set_message("Invalid Data", 'error');
			redirect('pengajuan_rutin');
		}
		$datdept  = $this->All_model->GetDeptCombo($data->departement);
		$data_detail = $this->Pengajuan_rutin_model->GetDataPengajuanRutinDetail($data->no_doc);
		$this->template->set('type', 'edit');
		$this->template->set('datdept', $datdept);
		$this->template->set('data', $data);
		$this->template->set('app', '');
		$this->template->set('data_detail', $data_detail);
		$this->template->title('Edit Pengajuan Pembayaran Rutin');
		$this->template->render('input_form');
	}

	public function view($id, $app = '')
	{
		$data	= $this->Pengajuan_rutin_model->GetDataPengajuanRutin($id);
		if (!$data) {
			$this->template->set_message("Invalid Data", 'error');
			redirect('pengajuan_rutin');
		}
		$datdept  = $this->All_model->GetDeptCombo($data->departement);
		$data_detail = $this->Pengajuan_rutin_model->GetDataPengajuanRutinDetail($data->no_doc);
		$this->template->set('type', 'view');
		$this->template->set('datdept', $datdept);
		$this->template->set('data', $data);
		$this->template->set('app', $app);
		$this->template->set('data_detail', $data_detail);
		$this->template->title('View Pengajuan Pembayaran Rutin');
		$this->template->render('input_form');
	}

	public function get_data()
	{
		$allbudget		= $this->input->post("allbudget");
		$dept       	= $this->input->post("dept");
		$tanggal           = $this->input->post("tanggal");
		$data = $this->Pengajuan_rutin_model->GetDataBudgetRutin($dept, $tanggal, $allbudget);
		$param = array(
			'save' => 1,
			'data' => $data,
			'tahun' => date("Y", strtotime($tanggal)),
			'bulan' => date("m", strtotime($tanggal)),
		);
		echo json_encode($param);
	}

	public function save_data()
	{

		$departement	= $this->input->post("departement");
		$id				= $this->input->post("id");
		$no_doc			= $this->input->post("no_doc");
		$tanggal_doc	= $this->input->post("tanggal_doc");
		// $tanggal_doc    = str_replace('/','-',$tanggal_doc);
		$tanggal_doc    = date('Y-m-d', strtotime($tanggal_doc));
		// print_r($tanggal_doc);
		// exit;

		$detail_id		= $this->input->post("detail_id");
		$id_budget		= $this->input->post("id_budget");
		$coa       		= $this->input->post("coa");
		$nama           = $this->input->post("nama");
		$tanggal		= $this->input->post("tanggal");
		$tipe  			= 'rutin';
		$details			= $this->input->post("details");
		$budget			= $this->input->post("budget");
		$nilai			= $this->input->post("nilai");
		$keterangan		= $this->input->post("keterangan");
		$bank_id		= $this->input->post("bank_id");
		$accnumber		= $this->input->post("accnumber");
		$accname		= $this->input->post("accname");
		$metode_pembelian		= $this->input->post("metode_pembelian");

		$this->db->trans_begin();
		if ($no_doc == '') {
			$no_doc = $this->All_model->GetAutoGenerate('format_nonpo');
			$dataheader =  array(
				'tipe' => $tipe,
				'no_doc' => $no_doc,
				'tanggal_doc' => $tanggal_doc,
				'departement' => $departement
				// 'nilai'=>0,
			);
			$this->Pengajuan_rutin_model->insert($dataheader);
		} else {
			$dataheader =  array(
				array(
					'id' => $id,
					'tanggal_doc' => $tanggal_doc,
				)
			);
			$this->Pengajuan_rutin_model->update_batch($dataheader, 'id');
			if (is_array($detail_id)) {
				$delid = implode("','", $detail_id);
				$this->All_model->dataDelete('tr_pengajuan_rutin_detail', " id not in ('" . $delid . "') and no_doc='" . $no_doc . "'");
			} else {
				$this->All_model->dataDelete('tr_pengajuan_rutin_detail', "no_doc='" . $no_doc . "'");
			}
		}
		for ($x = 0; $x < count($detail_id); $x++) {
			$idf = $details[$x];
			if ($detail_id[$x] != '') {
				if ($nilai[$x] > 0) {
					$data = array(
						'id_budget' => $id_budget[$x],
						'coa' => $coa[$x],
						'nama' => $nama[$x],
						'tanggal' => $tanggal[$x],
						'budget' => $budget[$x],
						'nilai' => $nilai[$x],
						'keterangan' => $keterangan[$x],
						'bank_id' => $bank_id[$x],
						'accnumber' => $accnumber[$x],
						'accname' => $accname[$x],
						'metode_pembelian' => $metode_pembelian[$x],
						'created_by' => $this->auth->user_id(),
						'created_on' => date("Y-m-d h:i:s"),
					);
					if (!empty($_FILES['doc_file_' . $idf]['name'])) {
						$_FILES['file']['name'] = $_FILES['doc_file_' . $idf]['name'];
						$_FILES['file']['type'] = $_FILES['doc_file_' . $idf]['type'];
						$_FILES['file']['tmp_name'] = $_FILES['doc_file_' . $idf]['tmp_name'];
						$_FILES['file']['error'] = $_FILES['doc_file_' . $idf]['error'];
						$_FILES['file']['size'] = $_FILES['doc_file_' . $idf]['size'];
						$config['upload_path'] = './assets/bayar_rutin/';
						$config['allowed_types'] = '*';
						$config['remove_spaces'] = TRUE;
						$config['encrypt_name'] = TRUE;

						$this->upload->initialize($config);
						if ($this->upload->do_upload('file')) {
							$uploadData = $this->upload->data();
							$filename = $uploadData['file_name'];
							$data['doc_file'] = $filename;
						} else {
							print_r($this->upload->display_errors());
							exit;
						}
					}
					$this->db->update('tr_pengajuan_rutin_detail', $data, array('id' => $detail_id[$x]));
				}
			} else {
				if ($nilai[$x] > 0) {
					$data =  array(
						'no_doc' => $no_doc,
						'id_budget' => $id_budget[$x],
						'coa' => $coa[$x],
						'nama' => $nama[$x],
						'tanggal' => $tanggal[$x],
						'budget' => $budget[$x],
						'nilai' => $nilai[$x],
						'keterangan' => $keterangan[$x],
						'bank_id' => $bank_id[$x],
						'accnumber' => $accnumber[$x],
						'accname' => $accname[$x],
						'metode_pembelian' => $metode_pembelian[$x],
						'created_by' => $this->auth->user_id(),
						'created_on' => date("Y-m-d h:i:s"),
						'modified_by' => $this->auth->user_id(),
						'modified_on' => date("Y-m-d h:i:s"),
					);
					if (!empty($_FILES['doc_file_' . $idf]['name'])) {
						$_FILES['file']['name'] = $_FILES['doc_file_' . $idf]['name'];
						$_FILES['file']['type'] = $_FILES['doc_file_' . $idf]['type'];
						$_FILES['file']['tmp_name'] = $_FILES['doc_file_' . $idf]['tmp_name'];
						$_FILES['file']['error'] = $_FILES['doc_file_' . $idf]['error'];
						$_FILES['file']['size'] = $_FILES['doc_file_' . $idf]['size'];

						$config['upload_path'] = './assets/bayar_rutin/';
						$config['allowed_types'] = '*';
						$config['remove_spaces'] = TRUE;
						$config['encrypt_name'] = TRUE;

						$this->upload->initialize($config);
						if ($this->upload->do_upload('file')) {
							$uploadData = $this->upload->data();
							$filename = $uploadData['file_name'];
							$data['doc_file'] = $filename;
						} else {
							print_r($this->upload->display_errors());
							exit;
						}
					}
					$this->db->insert('tr_pengajuan_rutin_detail', $data);
				}
			}
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
			$this->db->trans_begin();
			$this->All_model->dataDelete('tr_pengajuan_rutin', array('no_doc' => $id));
			$this->All_model->dataDelete('tr_pengajuan_rutin_detail', array('no_doc' => $id));
			$result = $this->db->trans_status();
			$this->db->trans_complete();
			$keterangan     = "SUKSES, Delete data  ";
			$status         = 1;
			$nm_hak_akses   = $this->deletePermission;
			$kode_universal = $id;
			$jumlah = 1;
			$sql            = $this->db->last_query();
		} else {
			$result = 0;
			$keterangan     = "GAGAL, Delete data  ";
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

	// approve
	public function approve($id = '')
	{
		$result = false;
		if ($id != "") {
			$data = array(
				array(
					'id' => $id,
					'status' => 1,
				)
			);
			$result = $this->Pengajuan_rutin_model->update_batch($data, 'id');
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
	public function reject($id = '')
	{
		$result = false;
		if ($id != "") {
			$data = array(
				array(
					'id' => $id,
					'sts_reject' => 1,
					'reject_ket' => $this->input->post('reject_reason')
				)
			);
			$result = $this->Pengajuan_rutin_model->update_batch($data, 'id');
			$keterangan     = "SUKSES, Reject data " . $id;
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
}
