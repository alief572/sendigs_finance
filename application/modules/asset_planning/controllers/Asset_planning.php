<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Asset_planning extends Admin_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('master_model');
		$this->load->model('budget_rutin_model');
		$this->load->model('budget_asset_model');
		// $this->db2 = $this->load->database('gl', TRUE);
		// Your own constructor code
	}

	//================================================================================================================
	//===========================================BUDGET RUTIN=========================================================
	//================================================================================================================

	public function index_rutin()
	{
		$this->budget_rutin_model->index_rutin();
	}

	public function server_side_rutin()
	{
		$this->budget_rutin_model->get_data_json_rutin();
	}

	public function detail_rutin()
	{
		$this->budget_rutin_model->detail_rutin();
	}

	public function add_rutin()
	{
		$this->budget_rutin_model->add_rutin();
	}

	public function get_add()
	{
		$this->budget_rutin_model->get_add();
	}

	public function get_spec()
	{
		$this->budget_rutin_model->get_spec();
	}

	public function kompilasi()
	{
		$this->budget_rutin_model->kompilasi();
	}

	public function excel_kompilasi()
	{
		$this->budget_rutin_model->excel_kompilasi();
	}

	public function kompilasi_budget()
	{
		$controller			= ucfirst(strtolower($this->uri->segment(1))) . "/index_rutin";
		$Arr_Akses			= getAcccesmenu($controller);
		if ($Arr_Akses['read'] != '1') {
			$this->session->set_flashdata("alert_data", "<div class=\"alert alert-warning\" id=\"flash-message\">You Don't Have Right To Access This Page, Please Contact Your Administrator....</div>");
			redirect(site_url('dashboard'));
		}

		$group_header = $this->db->query("SELECT department, costcenter FROM budget_rutin_header GROUP BY department, costcenter")->result_array();
		$group_barang = $this->db->query("SELECT id_barang, jenis_barang, satuan FROM budget_rutin_detail GROUP BY id_barang ORDER BY jenis_barang ASC, id_barang")->result_array();

		$data = array(
			'title'			=> 'Indeks Of Compile Asset_planning',
			'action'		=> 'index',
			'akses_menu'	=> $Arr_Akses,
			'group_header' => $group_header,
			'group_barang' => $group_barang
		);
		history('View Data Asset_planning Kompilasi');
		$this->load->view('Asset_planning_rutin/kompilasi_budget', $data);
	}

	public function delete()
	{
		$id 			= $this->uri->segment(3);
		$data_session	= $this->session->userdata;

		$this->db->trans_start();
		$this->db->where('code_budget', $id);
		$this->db->delete('budget_rutin_header');

		$this->db->where('code_budget', $id);
		$this->db->delete('budget_rutin_detail');
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=> 'Delete data failed. Please try again later ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=> 'Delete data success. Thanks ...',
				'status'	=> 1
			);
			history('Delete budget : ' . $id);
		}
		echo json_encode($Arr_Data);
	}

	//================================================================================================================
	//===========================================BUDGET ASSETS=========================================================
	//================================================================================================================

	public function index_asset()
	{
		$this->budget_asset_model->index();
	}

	public function server_side_asset()
	{
		$this->budget_asset_model->get_data_json_asset();
	}

	public function add_asset()
	{
		if ($this->input->post()) {
			$Arr_Kembali	= array();
			$data			= $this->input->post();
			$data_session	= $this->session->userdata;
			$dateTime		= date('Y-m-d H:i:s');
			// print_r($data); exit;
			$code_plan  	= $data['id'];
			$code_planx  	= $data['id'];
			$tanda        	= $data['tanda'];
			$approve        = $data['approve'];
			$id_dept 		= (!empty($data['id_dept'])) ? $data['id_dept'] : NULL;
			$id_costcenter 	= (!empty($data['id_costcenter'])) ? $data['id_costcenter'] : NULL;
			$coa 			= (!empty($data['coa'])) ? $data['coa'] : NULL;
			$coa_akum 		= (!empty($data['coa_akum'])) ? $data['coa_akum'] : NULL;
			$nama_asset 	= strtolower($data['nama_asset']);
			$tahun 			= $data['tahun'];
			$bulan 			= $data['bulan'];
			$budget 		= str_replace(',', '', $data['budget']);
			$budget_pr 		= (!empty($data['budget_pr'])) ? str_replace(',', '', $data['budget_pr']) : '';
			$budget_po 		= (!empty($data['budget_po'])) ? str_replace(',', '', $data['budget_po']) : '';
			$qty 			= str_replace(',', '', $data['qty']);
			$keterangan 	= strtolower($data['keterangan']);
			$reason 		= (!empty($data['reason'])) ? strtolower($data['reason']) : '';
			$status 		= (!empty($data['status'])) ? $data['status'] : '';

			$ym = date('ym');

			$ArrHeader		= array(
				'id_dept' 		=> $id_dept,
				'id_costcenter' => $id_costcenter,
				'coa' 			=> $coa,
				'coa_akum' 		=> $coa_akum,
				'nama_asset' 	=> $nama_asset,
				'tahun' 		=> $tahun,
				'bulan' 		=> $bulan,
				'budget' 		=> $budget,
				'qty' 			=> $qty,
				'keterangan' 	=> $keterangan,
				'updated_by'	=> $this->auth->user_id(),
				'updated_date'	=> $dateTime
			);

			if (empty($code_planx)) {
				$srcMtr			= "SELECT MAX(code_plan) as maxP FROM asset_planning WHERE code_plan LIKE 'PLA" . $ym . "%' ";
				$numrowMtr		= $this->db->query($srcMtr)->num_rows();
				$resultMtr		= $this->db->query($srcMtr)->result_array();
				$angkaUrut2		= $resultMtr[0]['maxP'];
				$urutan2		= (int)substr($angkaUrut2, 7, 3);
				$urutan2++;
				$urut2			= sprintf('%03s', $urutan2);
				$code_plan	= 	"PLA" . $ym . $urut2;

				$ArrHeader		= array(
					'id_dept' 		=> $id_dept,
					'code_plan' 	=> $code_plan,
					'id_costcenter' => $id_costcenter,
					'coa' 			=> $coa,
					'coa_akum' 		=> $coa_akum,
					'nama_asset' 	=> $nama_asset,
					'tahun' 		=> $tahun,
					'bulan' 		=> $bulan,
					'budget' 		=> $budget,
					'qty' 			=> $qty,
					'keterangan' 	=> $keterangan,
					'created_by'	=> $this->auth->user_id(),
					'created_date'	=> $dateTime
				);
			}

			if (!empty($approve)) {
				$ArrHeader		= array(
					'rev_nama_asset' 	=> $nama_asset,
					'rev_tahun' 		=> $tahun,
					'rev_bulan' 		=> $bulan,
					'rev_budget' 		=> $budget,
					'rev_qty' 			=> $qty,
					'rev_keterangan' 	=> $keterangan,
					'budget_pr' 	=> $budget_pr,
					'budget_po' 	=> $budget_po,
					'status' 		=> $status,
					'reason' 		=> $reason,
					'app_by'		=> $this->auth->user_id(),
					'app_date'		=> $dateTime
				);
			}


			// print_r($ArrHeader);
			// exit;

			$this->db->trans_start();
			if (empty($approve)) {
				if (!empty($code_planx)) {
					$this->db->where(array('code_plan' => $code_planx));
					$this->db->update('asset_planning', $ArrHeader);
				}
				if (empty($code_planx)) {
					$this->db->insert('asset_planning', $ArrHeader);
				}
			}
			if (!empty($approve)) {
				$this->db->where(array('code_plan' => $code_planx));
				$this->db->update('asset_planning', $ArrHeader);
			}
			$this->db->trans_complete();


			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$Arr_Kembali	= array(
					'pesan'		=> 'Process data failed. Please try again later ...',
					'status'	=> 0,
					'approve'	=> $approve
				);
			} else {
				$this->db->trans_commit();
				$Arr_Kembali	= array(
					'pesan'		=> 'Process data success. Thanks ...',
					'status'	=> 1,
					'approve'	=> $approve
				);
				history($tanda . ' Pengajuan Busget Asset ' . $code_plan);
			}
			echo json_encode($Arr_Kembali);
		} else {
			$id = $this->uri->segment(3);
			$approve = $this->uri->segment(4);
			$header = $this->db->query("SELECT * FROM asset_planning WHERE code_plan = '" . $id . "' ")->result();
			$datacoa = $this->db->like('no_perkiraan', '13', 'after')->get_where(DBACC . '.coa_master', array('level' => '5', 'no_perkiraan not like ' => '1309%'))->result_array();
			$penyusutan = $this->db->query("SELECT * FROM " . DBACC . ".coa_master WHERE `level`='5' AND (nama LIKE 'DEPRECIATION%') ORDER BY no_perkiraan ASC")->result_array();
			$tanda 			= (!empty($header)) ? 'Edit' : 'Add';
			$list_department = $this->db->get_where('ms_department', ['deleted_by' => null])->result_array();
			$list_costcenter = $this->db->get_where('ms_costcenter', ['deleted_by' => null])->result_array();
			$data = array(
				'title'			=> $tanda . ' Asset_planning Asset',
				'action'		=> strtolower($tanda),
				'header'		=> $header,
				'datacoa'		=> $datacoa,
				'penyusutan'	=> $penyusutan,
				'approve'		=> $approve,
				'id'			=> $id,
				'list_department' => $list_department,
				'list_costcenter' => $list_costcenter
			);
			$this->template->set($data);
			$this->template->render('add');
		}
	}

	public function hapus_asset()
	{
		$this->budget_asset_model->hapus_asset();
	}
}
