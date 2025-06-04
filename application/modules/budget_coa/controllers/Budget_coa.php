<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once 'vendor/autoload.php';

use Mpdf\Mpdf;
/*
 * @author harboens
 * @copyright Copyright (c) 2021, Harboens
 *
 * This is controller for Budget
 */

$listtahun = array();
class Budget_coa extends Admin_Controller
{

	protected $viewPermission   = "Budget.View";
	protected $addPermission    = "Budget.Add";
	protected $managePermission = "Budget.Manage";
	protected $deletePermission = "Budget.Delete";

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('Budget_coa/Budget_coa_model', 'jurnal_nomor/Acc_model', 'All/All_model'));
		$this->template->title('Manage Data Budget');
		$this->template->page_icon('fa fa-table');
		date_default_timezone_set("Asia/Bangkok");
		$list_tahun = array();
		for ($i = 2020; $i <= (date("Y") + 1); $i++) {
			$list_tahun[] = $i;
		}

		$this->listtahun = $list_tahun;
		$this->datakategori = $this->All_model->GetCategory();
	}

	public function index()
	{
		$tahun = $this->input->get("tahun");
		if ($tahun == '') $tahun = date("Y");
		$this->auth->restrict($this->viewPermission);
		$data = $this->Budget_coa_model->GetBudget($tahun);
		$this->template->set('results', $data);
		$this->template->set('tahun', $tahun);
		$this->template->set('listtahun', $this->listtahun);
		$this->template->title('Master Budget');
		$this->template->render('list');
	}

	public function create()
	{
		$query = "(a.no_perkiraan like '1106-01-0%' or a.no_perkiraan like '2103-01-0%' or a.no_perkiraan like '4%' or a.no_perkiraan like '5%' or a.no_perkiraan like '6%' or a.no_perkiraan like '7%' or a.no_perkiraan like '8%')";
		$datcoa	= $this->Budget_coa_model->GetCoa('5');
		$this->template->set('datakategori', $this->datakategori);
		$this->template->set('type', 'add');
		$this->template->set('data', $datcoa);
		$datadept	= $this->All_model->GetDeptCombo();
		$this->template->set('datadept', $datadept);
		$this->template->title('Input Budget');
		$this->template->render('budget_form');
	}

	public function detail($tahun)
	{
		$query = "( b.no_perkiraan like '1106-01-0%' or b.no_perkiraan like '2103-01-0%' or b.no_perkiraan like '4%' or b.no_perkiraan like '5%' or b.no_perkiraan like '6%' or b.no_perkiraan like '7%' or b.no_perkiraan like '8%' )";
		$data	= $this->Budget_coa_model->GetBudget($tahun, 'all', $query);
		if (!$data) {
			$this->template->set_message("Invalid Budget", 'error');
			redirect('budget_coa');
		}
		$datadept	= $this->All_model->GetDeptCombo();
		//        $this->template->set('datakategori', $this->datakategori);
		//        $this->template->set('datadept',$datadept);
		$this->template->set('type', 'edit');
		$this->template->set('data', $data);
		$this->template->title('Detail Budget');
		$this->template->render('budget_detail_form');
	}
	public function detail_bulan()
	{
		$bulan = $this->input->post("bulan");
		$tahun = $this->input->post("tahun");
		$query = "( b.no_perkiraan like '1106-01-0%' or b.no_perkiraan like '2103-01-0%' or b.no_perkiraan like '4%' or b.no_perkiraan like '5%' or b.no_perkiraan like '6%' or b.no_perkiraan like '7%' or b.no_perkiraan like '8%' )";
		$data	= $this->Budget_coa_model->GetBudget($tahun, 'all', $query);
		if (!$data) {
			$this->template->set_message("Invalid Budget", 'error');
			redirect('budget_coa');
		}
		$datadept	= $this->All_model->GetDeptCombo();
		//        $this->template->set('datakategori', $this->datakategori);
		//        $this->template->set('datadept',$datadept);
		$this->template->set('type', 'edit');
		$this->template->set('data', $data);
		$this->template->set('bulan', $bulan);
		$this->template->set('tahun', $tahun);
		$this->template->title('Detail Budget');
		$this->template->render('budget_detail_bulan_form');
	}

	public function edit($tahun)
	{
		$query = "( b.no_perkiraan like '1106-01-0%' or b.no_perkiraan like '2103-01-0%' or b.no_perkiraan like '4%' or b.no_perkiraan like '5%' or b.no_perkiraan like '6%' or b.no_perkiraan like '7%' or b.no_perkiraan like '8%' )";
		$data	= $this->Budget_coa_model->GetBudget($tahun, 'all', $query);
		if (!$data) {
			$this->template->set_message("Invalid Budget", 'error');
			redirect('budget_coa');
		}
		$datadept	= $this->All_model->GetDeptCombo();
		$this->template->set('datakategori', $this->datakategori);
		$this->template->set('datadept', $datadept);
		$this->template->set('type', 'edit');
		$this->template->set('data', $data);
		$this->template->title('Edit Budget');
		$this->template->render('budget_form');
	}

	public function save_data()
	{
		$id		        = $this->input->post("id");
		$type           = $this->input->post("type");
		$tahun  		= $this->input->post("tahun");
		$coa       		= $this->input->post("coa");
		$total			= $this->input->post("total");
		$info      		= $this->input->post("info");
		$divisi			= $this->input->post("divisi");

		$definisi		= $this->input->post("definisi");
		$kategori		= $this->input->post("kategori");
		$finance_bulan	= $this->input->post("finance_bulan");
		$finance_tahun	= $this->input->post("finance_tahun");
		/*
		for($i=1;$i<=12;$i++){
			${"bulan_".$i} = $this->input->post('bulan_'.$i);
		}
*/
		$this->db->trans_start();
		if ($type == "edit") {
			for ($x = 0; $x < count($coa); $x++) {
				if ($finance_tahun[$x] == '') $finance_tahun[$x] = 0;
				if ($finance_bulan[$x] == '') $finance_bulan[$x] = 0;
				//			  if($finance_tahun[$x]>0){
				if ($id[$x] != '') {
					$data = array(
						array(
							'id' => $id[$x],
							'tahun' => $tahun,
							'coa' => $coa[$x],
							'info' => $info[$x],
							'divisi' => $divisi[$x],
							'kategori' => $kategori[$x],
							'definisi' => $definisi[$x],
							'finance_bulan' => $finance_bulan[$x],
							'finance_tahun' => $finance_tahun[$x],
							/*
								'total'=>$total[$x],
								'bulan_1'=>$bulan_1[$x], 'bulan_2'=>$bulan_2[$x], 'bulan_3'=>$bulan_3[$x],'bulan_4'=>$bulan_4[$x], 'bulan_5'=>$bulan_5[$x],'bulan_6'=>$bulan_6[$x],
								'bulan_7'=>$bulan_7[$x], 'bulan_8'=>$bulan_8[$x], 'bulan_9'=>$bulan_9[$x], 'bulan_10'=>$bulan_10[$x], 'bulan_11'=>$bulan_11[$x], 'bulan_12'=>$bulan_12[$x],
*/
						)
					);
					$this->Budget_coa_model->update_batch($data, 'id');
				} else {
					$data =  array(
						'tahun' => $tahun,
						'coa' => $coa[$x],
						'info' => $info[$x],
						'divisi' => $divisi[$x],
						'kategori' => $kategori[$x],
						'definisi' => $definisi[$x],
						'finance_bulan' => $finance_bulan[$x],
						'finance_tahun' => $finance_tahun[$x],
						/*
								'total'=>$total[$x],
								'sisa'=>0,
								'bulan_1'=>$bulan_1[$x], 'bulan_2'=>$bulan_2[$x], 'bulan_3'=>$bulan_3[$x],'bulan_4'=>$bulan_4[$x], 'bulan_5'=>$bulan_5[$x],'bulan_6'=>$bulan_6[$x],
								'bulan_7'=>$bulan_7[$x], 'bulan_8'=>$bulan_8[$x], 'bulan_9'=>$bulan_9[$x], 'bulan_10'=>$bulan_10[$x], 'bulan_11'=>$bulan_11[$x], 'bulan_12'=>$bulan_12[$x],
*/
					);
					$this->Budget_coa_model->insert($data);
				}
				//			  }
			}
			$keterangan     = "SUKSES, Edit data ";
			$status         = 1;
			$nm_hak_akses   = $this->managePermission;
			$kode_universal = $tahun;
			$jumlah = $x;
			$sql            = $this->db->last_query();
			simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
			$result			= TRUE;
		} else {
			for ($x = 0; $x < count($coa); $x++) {
				if (isset($finance_tahun[$x]) && $finance_tahun[$x] == '') $finance_tahun[$x] = 0;
				if (isset($finance_bulan[$x]) && $finance_bulan[$x] == '') $finance_bulan[$x] = 0;
				//			  if($finance_tahun[$x]>0){
				$data =  array(
					'tahun' => $tahun,
					'coa' => $coa[$x],
					'info' => $info[$x],
					'divisi' => $divisi[$x],
					'kategori' => $kategori[$x],
					'finance_bulan' => $finance_bulan[$x],
					'finance_tahun' => $finance_tahun[$x],
					'definisi' => $definisi[$x],
					/*
							'total'=>$total[$x],
							'sisa'=>0,
							'bulan_1'=>$bulan_1[$x], 'bulan_2'=>$bulan_2[$x], 'bulan_3'=>$bulan_3[$x],'bulan_4'=>$bulan_4[$x], 'bulan_5'=>$bulan_5[$x],'bulan_6'=>$bulan_6[$x],
							'bulan_7'=>$bulan_7[$x], 'bulan_8'=>$bulan_8[$x], 'bulan_9'=>$bulan_9[$x], 'bulan_10'=>$bulan_10[$x], 'bulan_11'=>$bulan_11[$x], 'bulan_12'=>$bulan_12[$x],
*/
				);
				$this->Budget_coa_model->insert($data);
				//			  }
			}
			if ($this->db->trans_status()) {
				$keterangan     = "SUKSES, tambah data " . $tahun;
				$status         = 1;
				$nm_hak_akses   = $this->addPermission;
				$kode_universal = 'NewData';
				$jumlah         = $x;
				$sql            = $this->db->last_query();
				$result         = TRUE;
			} else {
				$keterangan     = "GAGAL, tambah data Budget " . $tahun;
				$status         = 0;
				$nm_hak_akses   = $this->addPermission;
				$kode_universal = 'NewData';
				$jumlah         = $x;
				$sql            = $this->db->last_query();
				$result = FALSE;
			}
			simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
		}
		$this->db->trans_complete();
		$param = array(
			'save' => $result
		);
		echo json_encode($param);
	}

	function approve_data($tahun)
	{
		if ($tahun != '') {
			$result = $this->All_model->dataUpdate('ms_budget', array('status' => '1'), array('tahun' => $tahun));
			$keterangan     = "SUKSES, Approve data Budget " . $tahun;
			$status         = 1;
			$nm_hak_akses   = $this->deletePermission;
			$kode_universal = $tahun;
			$jumlah = 1;
			$sql            = $this->db->last_query();
			$result = 1;
		} else {
			$result = 0;
			$keterangan     = "GAGAL, approve data Budget " . $tahun;
			$status         = 0;
			$nm_hak_akses   = $this->deletePermission;
			$kode_universal = $tahun;
			$jumlah = 1;
			$sql            = $this->db->last_query();
		}
		simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
		$param = array(
			'delete' => $result,
			'idx' => $tahun
		);
		echo json_encode($param);
	}

	function hapus_data($tahun)
	{
		$this->auth->restrict($this->deletePermission);
		if ($tahun != '') {
			$result = $this->All_model->dataDelete('ms_budget', array('tahun' => $tahun));
			$keterangan     = "SUKSES, Delete data Budget " . $tahun;
			$status         = 1;
			$nm_hak_akses   = $this->deletePermission;
			$kode_universal = $tahun;
			$jumlah = 1;
			$sql            = $this->db->last_query();
			$result = 1;
		} else {
			$result = 0;
			$keterangan     = "GAGAL, Delete data Budget " . $tahun;
			$status         = 0;
			$nm_hak_akses   = $this->deletePermission;
			$kode_universal = $tahun;
			$jumlah = 1;
			$sql            = $this->db->last_query();
		}
		simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
		$param = array(
			'delete' => $result,
			'idx' => $tahun
		);
		echo json_encode($param);
	}

	public function search_old($coa, $tgl, $dtl = '')
	{
		$tahun = date("Y", strtotime($tgl));
		$data = $this->Budget_coa_model->SearchBudget($coa, $tahun);
		$param = array();
		if ($data !== false) {
			if ($dtl == '') {
				$bulan = date("n", strtotime($tgl));
				$budget = $data->{"bulan_" . $bulan};
				$sisa = ($data->{"bulan_" . $bulan} - $data->{"terpakai_bulan_" . $bulan});
				$param = array(
					'budget' => $budget,
					'sisa' => $sisa,
					'tipe' => $data->tipe_pr,
				);
			} else {
				$param = $data;
			}
		} else {
			if ($dtl == '') {
				$param = array(
					'budget' => 0,
					'sisa' => 0,
					'tipe' => '',
				);
			}
		}
		echo json_encode($param);
	}

	public function search($coa, $tgl, $dtl = '')
	{
		$tahun = date("Y", strtotime($tgl));
		$data = $this->Budget_coa_model->SearchBudget($coa, $tahun);
		$param = array();
		if ($data !== false) {
			if ($dtl == '') {
				$bulannow = date("n");
				$bulan = date("n", strtotime($tgl));
				$budget = 0;
				$terpakai = 0;
				for ($i = 1; $i <= $bulannow; $i++) {
					$budget = ($budget + $data->{"bulan_" . $i});
					$terpakai = ($terpakai + $data->{"terpakai_bulan_" . $i});
				}
				$sisa = ($budget - $terpakai);
				$param = array(
					'budget' => $budget,
					'terpakai' => $terpakai,
					'sisa' => $sisa,
					'tipe' => $data->tipe_pr,
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
	function budget_umum()
	{
		$data = $this->Budget_coa_model->GetListBudgetDept('UMUM');
		$this->template->set('results', $data);
		$this->template->set('datakategori', $this->datakategori);
		$this->template->set('listtahun', $this->listtahun);
		$datadept	= $this->All_model->GetDeptCombo();
		$this->template->set('datadept', $datadept);
		$this->template->title('Daftar Budget Umum');
		$this->template->render('list_umum');
	}
	function create_budget_umum()
	{
		$kategori	= $this->input->post("kategori");
		$tahun		= $this->input->post("tahun");
		$divisi		= $this->input->post("divisi");
		$dataset	= array('tahun' => $tahun, 'divisi' => $divisi, 'kategori' => $kategori);
		$datcoa	= $this->Budget_coa_model->GetBudgetCategory($dataset);
		if (empty($datcoa)) {
			echo 'Data tidak ditemukan <a href="' . base_url('budget_coa/budget_nr') . '">Kembali</a>';
			die();
			////$this->template->render('list_nr');
		} else {
			$datajenis = $this->All_model->GetJenis();
			$datacoa = $this->All_model->GetCoaCombo();

			$this->template->set('datacoa', $datacoa);
			$this->template->set('dataset', $dataset);
			$this->template->set('datajenis', $datajenis);

			$this->template->set('data', $datcoa);
			$this->template->set('datakategori', $this->datakategori);
			$datadept	= $this->All_model->GetDeptCombo();
			$this->template->set('datadept', $datadept);
			$this->template->title('Input Budget Detail');
			$this->template->render('budget_form_umum');
		}
	}

	function save_data_umum()
	{
		$id		        = $this->input->post("id");
		$type       	= $this->input->post("type");
		$jenis       	= $this->input->post("jenis");
		$nilai			= $this->input->post("nilai");
		$total			= $this->input->post("total");
		$variabel_coa	= $this->input->post("variabel_coa");
		for ($i = 1; $i <= 12; $i++) {
			${"bulan_" . $i} = $this->input->post('bulan_' . $i);
		}
		$this->db->trans_start();
		if ($type == "edit") {
			for ($x = 0; $x < count($id); $x++) {
				if ($jenis[$x] == 'FIX COST BULANAN') {
					$data =  array(
						'jenis' => $jenis[$x],
						'nilai' => 0,
						'variabel_coa' => '',
						'status' => '2',
						'bulan_1' => $bulan_1[$x],
						'bulan_2' => $bulan_2[$x],
						'bulan_3' => $bulan_3[$x],
						'bulan_4' => $bulan_4[$x],
						'bulan_5' => $bulan_5[$x],
						'bulan_6' => $bulan_6[$x],
						'bulan_7' => $bulan_7[$x],
						'bulan_8' => $bulan_8[$x],
						'bulan_9' => $bulan_9[$x],
						'bulan_10' => $bulan_10[$x],
						'bulan_11' => $bulan_11[$x],
						'bulan_12' => $bulan_12[$x],
						'total' => $total[$x],
						'created_by_dept' => $this->auth->user_id(),
						'created_on_dept' => date('Y-m-d H:i:s')
					);
				} else {
					$data =  array(
						'jenis' => $jenis[$x],
						'nilai' => $nilai[$x],
						'variabel_coa' => $variabel_coa[$x],
						'status' => '2',
						'created_by_dept' => $this->auth->user_id(),
						'created_on_dept' => date('Y-m-d H:i:s')
					);
				}
				$this->All_model->dataUpdate('ms_budget', $data, array('id' => $id[$x]));
			}
			$keterangan     = "SUKSES, Simpan data ";
			$status         = 1;
			$nm_hak_akses   = $this->managePermission;
			$kode_universal = $tahun;
			$jumlah = $x;
			$sql            = $this->db->last_query();
			simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
			$result			= TRUE;
		}
		$this->db->trans_complete();
		$param = array(
			'save' => $result
		);
		echo json_encode($param);
	}

	function approve_data_umum()
	{
		$kategori	= $this->input->post("kategori");
		$tahun		= $this->input->post("tahun");
		$divisi		= $this->input->post("divisi");
		$dataset	= array('tahun' => $tahun, 'divisi' => $divisi, 'kategori' => $kategori);
		$result = $this->All_model->dataUpdate('ms_budget', array('status' => '3', 'created_by_dept' => $this->auth->user_id(), 'created_on_dept' => date('Y-m-d H:i:s')), $dataset);
		$keterangan     = "SUKSES, Approve data budget";
		$status         = 1;
		$nm_hak_akses   = $this->deletePermission;
		$kode_universal = $tahun;
		$jumlah = 1;
		$sql            = $this->db->last_query();
		$result = 1;
		$param = array(
			'save' => $result
		);
		echo json_encode($param);
	}
	function revisi_data_umum()
	{
		$kategori	= $this->input->post("kategori");
		$tahun		= $this->input->post("tahun");
		$divisi		= $this->input->post("divisi");
		$revisi		= $this->input->post("revisi");
		$dataset	= array('tahun' => $tahun, 'divisi' => $divisi, 'kategori' => $kategori);
		$result = $this->All_model->dataUpdate('ms_budget', array('status' => '2', 'revisi' => ($revisi + 1), 'revision_by' => $this->auth->user_id(), 'revision_on' => date('Y-m-d H:i:s')), $dataset);
		$keterangan     = "Revisi data budget";
		$status         = 1;
		$nm_hak_akses   = $this->deletePermission;
		$kode_universal = $tahun;
		$jumlah = 1;
		$sql            = $this->db->last_query();
		$result = 1;
		$param = array(
			'save' => $result
		);
		echo json_encode($param);
	}
	function print_budget_umum()
	{
		$kategori	= $this->input->post("fkategori");
		$tahun		= $this->input->post("ftahun");
		$divisi		= $this->input->post("fdivisi");
		$dataset	= array('tahun' => $tahun, 'divisi' => $divisi, 'kategori' => $kategori);
		$datcoa		= $this->Budget_coa_model->GetBudgetCategory($dataset);
		if (empty($datcoa)) {
			echo 'Data not found';
			die();
			////$this->template->render('list_nr');
		} else {
			$this->template->set('dataset', $dataset);
			$this->template->set('data', $datcoa);
			$dataprint = $this->template->render('budget_print_umum');
			$this->mpdf->AddPage(
				'P',
				'',
				'',
				'',
				'',
				15, // margin_left
				15, // margin right
				30, // margin top
				15, // margin bottom
				7, // margin header
				3 // margin footer
			);
			$this->mpdf->WriteHTML($dataprint);
			$this->mpdf->Output();
		}
	}

	function budget_expense()
	{
		$data = $this->Budget_coa_model->GetListBudgetDept('EXPENSE');
		$this->template->set('results', $data);
		$this->template->set('datakategori', $this->datakategori);
		$this->template->set('listtahun', $this->listtahun);
		$datadept	= $this->All_model->GetDeptCombo();
		$this->template->set('datadept', $datadept);
		$this->template->set('tipek', 'EXPENSE');
		$this->template->title('Daftar Budget Expense');
		$this->template->render('list_nr');
	}

	function budget_nr()
	{
		$data = $this->Budget_coa_model->GetListBudgetDept('NON RUTIN');
		$this->template->set('results', $data);
		$this->template->set('datakategori', $this->datakategori);
		$this->template->set('listtahun', $this->listtahun);
		$datadept	= $this->All_model->GetDeptCombo();
		$this->template->set('datadept', $datadept);
		$this->template->set('tipek', 'NON RUTIN');
		$this->template->title('Daftar Budget Non Rutin');
		$this->template->render('list_nr');
	}
	function approve_data_category()
	{
		$kategori	= $this->input->post("kategori");
		$tahun		= $this->input->post("tahun");
		$divisi		= $this->input->post("divisi");
		$dataset	= array('tahun' => $tahun, 'divisi' => $divisi, 'kategori' => $kategori);
		$result = $this->All_model->dataUpdate('ms_budget', array('status' => '3', 'created_by_dept' => $this->auth->user_id(), 'created_on_dept' => date('Y-m-d H:i:s')), $dataset);
		$keterangan     = "SUKSES, Approve data budget";
		$status         = 1;
		$nm_hak_akses   = $this->deletePermission;
		$kode_universal = $tahun;
		$jumlah = 1;
		$sql            = $this->db->last_query();
		$result = 1;
		$param = array(
			'save' => $result
		);
		echo json_encode($param);
	}
	function revisi_data_category()
	{
		$kategori	= $this->input->post("kategori");
		$tahun		= $this->input->post("tahun");
		$divisi		= $this->input->post("divisi");
		$revisi		= $this->input->post("revisi");
		$dataset	= array('tahun' => $tahun, 'divisi' => $divisi, 'kategori' => $kategori);
		$result = $this->All_model->dataUpdate('ms_budget', array('status' => '2', 'revisi' => ($revisi + 1), 'revision_by' => $this->auth->user_id(), 'revision_on' => date('Y-m-d H:i:s')), $dataset);
		$keterangan     = "Revisi data budget";
		$status         = 1;
		$nm_hak_akses   = $this->deletePermission;
		$kode_universal = $tahun;
		$jumlah = 1;
		$sql            = $this->db->last_query();
		$result = 1;
		$param = array(
			'save' => $result
		);
		echo json_encode($param);
	}
	function print_budget_category()
	{
		$kategori	= $this->input->post("fkategori");
		$tahun		= $this->input->post("ftahun");
		$divisi		= $this->input->post("fdivisi");
		$dataset	= array('tahun' => $tahun, 'divisi' => $divisi, 'kategori' => $kategori);
		$datcoa		= $this->Budget_coa_model->GetBudgetCategory($dataset);
		if (empty($datcoa)) {
			echo 'Data not found';
			die();
			////$this->template->render('list_nr');
		} else {
			$this->template->set('dataset', $dataset);
			$this->template->set('data', $datcoa);
			$dataprint = $this->template->render('budget_print_category');
			$this->mpdf->AddPage(
				'P',
				'',
				'',
				'',
				'',
				15, // margin_left
				15, // margin right
				30, // margin top
				15, // margin bottom
				7, // margin header
				3 // margin footer
			);
			$this->mpdf->WriteHTML($dataprint);
			$this->mpdf->Output();
		}
	}
	function create_budget_category()
	{
		$kategori	= $this->input->post("kategori");
		$tahun		= $this->input->post("tahun");
		$divisi		= $this->input->post("divisi");
		$dataset	= array('tahun' => $tahun, 'divisi' => $divisi, 'kategori' => $kategori);
		$datcoa	= $this->Budget_coa_model->GetBudgetCategory($dataset);
		if (empty($datcoa)) {
			echo 'Data tidak ditemukan <a href="' . base_url('budget_coa/budget_nr') . '">Kembali</a>';
			die();
			////$this->template->render('list_nr');
		} else {
			$this->template->set('dataset', $dataset);
			$this->template->set('data', $datcoa);
			$this->template->set('datakategori', $this->datakategori);
			$datadept	= $this->All_model->GetDeptCombo();
			$this->template->set('datadept', $datadept);
			$this->template->title('Input Budget Detail');
			$this->template->render('budget_form_category');
		}
	}
	function save_data_category()
	{
		$id		        = $this->input->post("id");
		$type           = $this->input->post("type");
		$tahun  		= $this->input->post("tahun");
		$coa       		= $this->input->post("coa");
		$total			= $this->input->post("total");
		$info      		= $this->input->post("info");
		$divisi			= $this->input->post("divisi");
		for ($i = 1; $i <= 12; $i++) {
			${"bulan_" . $i} = $this->input->post('bulan_' . $i);
		}
		$this->db->trans_start();
		if ($type == "edit") {
			for ($x = 0; $x < count($coa); $x++) {
				if ($total[$x] > 0) {
					if ($id[$x] != '') {
						$data =  array(
							'bulan_1' => $bulan_1[$x],
							'bulan_2' => $bulan_2[$x],
							'bulan_3' => $bulan_3[$x],
							'bulan_4' => $bulan_4[$x],
							'bulan_5' => $bulan_5[$x],
							'bulan_6' => $bulan_6[$x],
							'bulan_7' => $bulan_7[$x],
							'bulan_8' => $bulan_8[$x],
							'bulan_9' => $bulan_9[$x],
							'bulan_10' => $bulan_10[$x],
							'bulan_11' => $bulan_11[$x],
							'bulan_12' => $bulan_12[$x],
							'total' => $total[$x],
							'status' => '2',
							'created_by_dept' => $this->auth->user_id(),
							'created_on_dept' => date('Y-m-d H:i:s')
						);
						$this->All_model->dataUpdate('ms_budget', $data, array('id' => $id[$x]));
					}
				}
			}
			$keterangan     = "SUKSES, Simpan data ";
			$status         = 1;
			$nm_hak_akses   = $this->managePermission;
			$kode_universal = $tahun;
			$jumlah = $x;
			$sql            = $this->db->last_query();
			simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
			$result			= TRUE;
		}
		$this->db->trans_complete();
		$param = array(
			'save' => $result
		);
		echo json_encode($param);
	}

	function proses_budget_umum()
	{
		$query = $this->db->query("SELECT * from ms_budget WHERE kategori='UMUM' and tahun='" . date("Y") . "' order by jenis,coa")->result();
		if ($query) {
			foreach ($query as $records) {
				$nilai_tahun = 0;
				$id = $records->id;
				$jenis = $records->jenis;
				$nilai = $records->nilai;
				$variabel_coa = $records->variabel_coa;
				$totalbulan = 0;
				for ($bln = 1; $bln <= 12; $bln++) {
					${"nilai_bulan" . $bln} = 0;
				}
				if ($jenis == 'FIX COST BULANAN') {
					$totalbulan = 0;
					for ($bln = 1; $bln <= 12; $bln++) {
						${"nilai_bulan" . $bln} = $records->{"bulan_" . $bln};
						$totalbulan = ($totalbulan + $records->{"bulan_" . $bln});
					}
					$nilai_tahun = $totalbulan;
				}
				if ($jenis == 'FIX COST TAHUNAN') {
					$nilai_tahun = ($nilai);
					for ($bln = 1; $bln <= 12; $bln++) {
						${"nilai_bulan" . $bln} = round($nilai / 12);
					}
				}
				if ($jenis == 'VARIABLE') {
					$nilai_bulan_coa = 0;
					$nilai_tahun_coa = 0;
					$totalbulan = 0;
					$querycoa = $this->db->query("SELECT * from ms_budget WHERE kategori='UMUM' and tahun='" . date("Y") . "' and coa='" . $variabel_coa . "' limit 1")->row();
					if ($querycoa->jenis == 'FIX COST BULANAN') {
						$totalbulan = 0;
						for ($bln = 1; $bln <= 12; $bln++) {
							${"nilai_bulan" . $bln} = ($querycoa->{"bulan_" . $bln} * $nilai / 100);
							$totalbulan = ($totalbulan + ($querycoa->{"bulan_" . $bln} * $nilai / 100));
						}
						$nilai_tahun = $totalbulan;
					}
					if ($querycoa->jenis == 'FIX COST TAHUNAN') {
						for ($bln = 1; $bln <= 12; $bln++) {
							${"nilai_bulan" . $bln} = round(($querycoa->nilai / 12) * $nilai / 100);
						}
						$nilai_tahun_coa = ($querycoa->nilai);
						$nilai_tahun = round($nilai_tahun_coa * $nilai / 100);
					}
				}
				$data =  array(
					'bulan_1' => $nilai_bulan1,
					'bulan_2' => $nilai_bulan2,
					'bulan_3' => $nilai_bulan3,
					'bulan_4' => $nilai_bulan4,
					'bulan_5' => $nilai_bulan5,
					'bulan_6' => $nilai_bulan6,
					'bulan_7' => $nilai_bulan7,
					'bulan_8' => $nilai_bulan8,
					'bulan_9' => $nilai_bulan9,
					'bulan_10' => $nilai_bulan10,
					'bulan_11' => $nilai_bulan11,
					'bulan_12' => $nilai_bulan12,
					'total' => $nilai_tahun,
				);
				$this->All_model->dataUpdate('ms_budget', $data, array('id' => $id));
			}
		}
		$result = 1;
		$param = array(
			'save' => $result
		);
		echo json_encode($param);
	}
}
