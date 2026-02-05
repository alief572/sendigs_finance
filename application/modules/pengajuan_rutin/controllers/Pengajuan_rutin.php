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

	protected $hris;
	protected $waktu;

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('All/All_model', 'Pengajuan_rutin/Pengajuan_rutin_model'));
		$this->template->title('Master Pembayaran Rutin');
		$this->template->page_icon('fa fa-cubes');
		date_default_timezone_set('Asia/Bangkok');
		$this->waktu = array("bulan" => "bulan", "tahun" => "tahun");

		$this->hris = $this->load->database('hris', true);
	}

	public function index()
	{
		//        $this->auth->restrict($this->viewPermission);
		$departemen = '';
		$datauser = $this->All_model->GetInfoUser($this->auth->user_id());
		//		if($datauser) $departemen=$datauser->department_id;
		// $data = $this->Pengajuan_rutin_model->GetPengajuanRutin(array('a.created_by' => $this->auth->user_id()));

		// $this->db->select('a.*, IF(SUM(b.nilai) IS NULL, 0, SUM(b.nilai)) as nilai_total');
		// $this->db->from('tr_pengajuan_rutin a');
		// $this->db->join('tr_pengajuan_rutin_detail b', 'b.no_doc = a.no_doc', 'left');
		// // $this->db->where('a.created_by', $this->auth->user_id());
		// $this->db->group_by('a.no_doc');
		// $data = $this->db->get()->result();

		$this->hris->select('a.id, a.name as nm_dept, b.name as nm_comp');
		$this->hris->from('departments a');
		$this->hris->join('companies b', 'b.id = a.company_id', 'left');
		$get_departments = $this->hris->get()->result();

		$arr_dept = [];
		foreach ($get_departments as $item) {
			$arr_dept[$item->id] = [
				'id' => $item->id,
				'nm_dept' => $item->nm_dept,
				'nm_comp' => $item->nm_comp
			];
		}

		$datdept  = $this->All_model->GetDeptCombo($departemen);

		$data_detail = $this->Pengajuan_rutin_model->GetDataPengajuanRutinAll();
		$this->template->set('datdept', $datdept);
		// $this->template->set('results', $data);
		$this->template->set('dept', $arr_dept);
		$this->template->set('data_detail', $data_detail);
		$this->template->title('Pengajuan Pembayaran Periodik');
		$this->template->render('list');
	}

	public function app_list()
	{
		$departemen = '';
		//		$datauser=$this->All_model->GetInfoUser($this->auth->user_id());
		//		if($datauser) $departemen=$datauser->departemen;
		$data = $this->Pengajuan_rutin_model->GetPengajuanRutin(array('a.status' => 0, 'a.sts_reject' => null));
		$datdept  = $this->All_model->GetDeptCombo();

		$arr_dept = [];

		$this->hris->select('a.id, a.name as nm_dept, b.name as nm_comp');
		$this->hris->from('departments a');
		$this->hris->join('companies b', 'b.id = a.company_id', 'left');
		$get_dept = $this->hris->get()->result();

		foreach ($get_dept as $item_dept) {
			$arr_dept[$item_dept->id] = $item_dept->nm_dept . ' - ' . $item_dept->nm_comp;
		}

		$this->template->set('arr_dept', $arr_dept);
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

		try {
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
						'sts_reject' => null,
						'sts' => 0,
						'reject_ket' => null
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
							'metode_pembelian' => '1',
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
							'metode_pembelian' => '1',
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

			$this->db->trans_commit();

			$this->output->set_status_header(200);
			echo json_encode([
				'save' => TRUE
			]);
		} catch (Exception $e) {
			$this->db->trans_rollback();

			$this->output->set_status_header(500);
			echo json_encode([
				'msg' => $e->getMessage()
			]);
		}

		// if ($this->db->trans_status()) {
		// 	$keterangan     = "SUKSES, tambah data ";
		// 	$status         = 1;
		// 	$nm_hak_akses   = $this->addPermission;
		// 	$kode_universal = 'NewData';
		// 	$jumlah         = $x;
		// 	$sql            = $this->db->last_query();
		// 	$result         = TRUE;
		// } else {
		// 	$keterangan     = "GAGAL, tambah data ";
		// 	$status         = 0;
		// 	$nm_hak_akses   = $this->addPermission;
		// 	$kode_universal = 'NewData';
		// 	$jumlah         = $x;
		// 	$sql            = $this->db->last_query();
		// 	$result = FALSE;
		// }
		// simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
		// $this->db->trans_complete();
		// $param = array(
		// 	'save' => $result
		// );
		// echo json_encode($param);
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
			'save' => $result,
			'id' => $id
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
			'save' => $result,
			'id' => $id
		);
		echo json_encode($param);
	}

	public function get_pengajuan_periodik()
	{
		$post = $this->input->post();

		$draw = intval($post['draw']);
		$length = $post['length'];
		$start = $post['start'];
		$search = $post['search']['value'];

		$this->db->select('a.*, IF(SUM(b.nilai) IS NULL, 0, SUM(b.nilai)) as nilai_total');
		$this->db->from('tr_pengajuan_rutin a');
		$this->db->join('tr_pengajuan_rutin_detail b', 'b.no_doc = a.no_doc', 'left');
		$this->db->group_by('a.no_doc');
		$count_all = $this->db->get()->num_rows();

		$this->db->select('a.*, IF(SUM(b.nilai) IS NULL, 0, SUM(b.nilai)) as nilai_total');
		$this->db->from('tr_pengajuan_rutin a');
		$this->db->join('tr_pengajuan_rutin_detail b', 'b.no_doc = a.no_doc', 'left');

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('a.no_doc', $search, 'both');
			$this->db->or_like('a.nilai_total', $search, 'both');
			$this->db->or_like('a.tanggal_doc', $search, 'both');
			$this->db->group_end();
		}

		$this->db->group_by('a.no_doc');
		$count_filter = $this->db->get()->num_rows();

		$this->db->select('a.*, IF(SUM(b.nilai) IS NULL, 0, SUM(b.nilai)) as nilai_total');
		$this->db->from('tr_pengajuan_rutin a');
		$this->db->join('tr_pengajuan_rutin_detail b', 'b.no_doc = a.no_doc', 'left');

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('a.no_doc', $search, 'both');
			$this->db->or_like('a.nilai_total', $search, 'both');
			$this->db->or_like('a.tanggal_doc', $search, 'both');
			$this->db->group_end();
		}

		$this->db->group_by('a.no_doc');
		$this->db->order_by('a.created_on', 'desc');
		$this->db->limit($length, $start);

		$get_data = $this->db->get()->result();

		$no = (0 + $start);
		$hasil = [];

		foreach ($get_data as $item) {

			$no++;

			$this->hris->select('a.id, a.name as nm_dept, b.name as nm_comp');
			$this->hris->from('departments a');
			$this->hris->join('companies b', 'b.id = a.company_id', 'left');
			$this->hris->where('a.id', $item->departement);
			$get_departments = $this->hris->get()->row();

			$nm_dept = (!empty($get_departments->nm_dept)) ? $get_departments->nm_dept : '';

			$nm_comp = (!empty($get_departments->nm_comp)) ? $get_departments->nm_comp : '';

			$status = '';

			if ($item->sts_reject == '1') {
				$status = '<div class="badge bg-red">Reject</div>';
			} else {
				if ($item->status == '1') {
					$status = '<div class="badge bg-green">Approved</div>';
				} else {
					if ($item->status == '2') {
						$status = '<div class="badge bg-blue">Diproses Finance</div>';

						$this->db->select('a.id');
						$this->db->from('payment_approve a');
						$this->db->where('a.no_doc', $item->no_doc);
						$count_payment_periodik = $this->db->get()->num_rows();

						if ($count_payment_periodik > 0) {
							$status = '<div class="badge bg-green">Paid</div>';
						}
					} else {
						$status = '<div class="badge bg-yellow">Waiting Approval</div>';
					}
				}
			}

			$btn_view = '';
			$btn_edit = '';
			$btn_delete = '';

			if (has_permission($this->viewPermission)) {
				$btn_view = '<a class="btn btn-info btn-sm view" href="javascript:void(0)" title="View" onclick="data_view(' . $item->id . ')"><i class="fa fa-eye"></i></a>';
			}

			if ($item->status == 0) {
				if (has_permission($this->managePermission)) {
					$btn_edit = '<a href="javascript:void(0);" class="btn btn-sm btn-warning btn_edit" title="Edit" onclick="data_edit(' . $item->id . ')"><i class="fa fa-edit"></i></a>';
				}
				if (has_permission($this->deletePermission)) {
					$no_doc_delete = str_replace($item->no_doc, "'" . $item->no_doc . "'", $item->no_doc);
					$btn_delete = '<a class="btn btn-danger btn-sm delete" href="javascript:void(0);" title="hapus" onclick="data_delete(' . $no_doc_delete . ')"><i class="fa fa-trash"></i</a>';
				}
			}

			$keterangan_reject = '';
			if ($item->status < 1 && $item->sts_reject == 1) {
				$keterangan_reject = $item->reject_ket;
			}

			$action = $btn_view . ' ' . $btn_edit . ' ' . $btn_delete;

			$hasil[] = [
				'no' => $no,
				'department' => strtoupper($nm_dept . ' - ' . $nm_comp),
				'nomor' => $item->no_doc,
				'nominal' => number_format($item->nilai_total),
				'tanggal' => date('d F Y', strtotime($item->tanggal_doc)),
				'status' => $status,
				'keterangan_reject' => $keterangan_reject,
				'action' => $action
			];
		}

		$response = [
			'draw' => $draw,
			'recordsTotal' => $count_all,
			'recordsFiltered' => $count_filter,
			'data' => $hasil
		];

		echo json_encode($response);
	}
}
