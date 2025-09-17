<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Incoming_check extends Admin_Controller
{

	protected $viewPermission     = 'Incoming_Check.View';
	protected $addPermission      = 'Incoming_Check.Add';
	protected $managePermission = 'Incoming_Check.Manage';
	protected $deletePermission = 'Incoming_Check.Delete';

	public function __construct()
	{
		parent::__construct();
		$this->load->library(array('upload', 'Image_lib'));
		$this->load->model([
			'Incoming_check/Incoming_check_model',
			'jurnal_nomor/Jurnal_model'
		]);
		$this->load->helper('file');
		$this->template->title('Incoming Check');
		$this->template->page_icon('fa fa-building-o');

		date_default_timezone_set('Asia/Bangkok');
	}

	public function index()
	{
		$this->Incoming_check_model->index_incoming_check();
	}

	//==========================================================================================================================
	//===================================================MATERIAL PLANNING======================================================
	//==========================================================================================================================

	public function material_planing()
	{
		$this->material_planning_model->index_material_planning();
	}

	public function server_side_material_planning()
	{
		$this->material_planning_model->get_data_json_material_planning();
	}

	public function modal_detail_material_planning()
	{
		$this->material_planning_model->detail_material_planning();
	}

	public function modal_add_material_planning()
	{
		$this->material_planning_model->add_get_query_material_planning();
	}

	public function modal_edit_material_planning()
	{
		$this->material_planning_model->edit_get_query_material_planning();
	}

	public function save_material_planning()
	{
		$this->material_planning_model->add_get_query_material_planning();
	}

	public function edit_material_planning()
	{
		$this->material_planning_model->edit_get_query_material_planning();
	}

	public function booking_material()
	{
		$this->material_planning_model->process_booking_material_planning();
	}

	public function spk_material()
	{
		$this->material_planning_model->print_material_planning();
	}

	//Reorder Poin
	public function reorder_point()
	{
		$this->material_planning_model->index_reorder_point();
	}

	public function server_side_reorder_point()
	{
		$this->material_planning_model->get_data_json_reorder_point();
	}

	public function save_reorder_point()
	{
		$this->material_planning_model->save_reorder_point();
	}

	public function save_reorder_change()
	{
		$this->material_planning_model->save_reorder_change();
	}

	public function save_reorder_change_date()
	{
		$this->material_planning_model->save_reorder_change_date();
	}

	public function clear_update_reorder()
	{
		$this->material_planning_model->clear_update_reorder();
	}

	public function save_reorder_all()
	{
		$this->material_planning_model->save_reorder_all();
	}

	//==========================================================================================================================
	//=================================================END MATERIAL PLANNING====================================================
	//==========================================================================================================================

	//==========================================================================================================================
	//====================================================PURCHASE REQUEST======================================================
	//==========================================================================================================================

	//Approval PR
	public function approval_pr()
	{
		$this->purchase_request_model->index_approval_pr();
	}
	public function list_pr_new()
	{
		$controller			= 'warehouse/list_pr_new';
		$Arr_Akses			= getAcccesmenu($controller);
		if ($Arr_Akses['read'] != '1') {
			$this->session->set_flashdata("alert_data", "<div class=\"alert alert-warning\" id=\"flash-message\">You Don't Have Right To Access This Page, Please Contact Your Administrator....</div>");
			redirect(site_url('dashboard'));
		}

		$GET_SO_NUMBER = $this->db->get('so_number')->result_array();
		$ArrGetSO = [];
		foreach ($GET_SO_NUMBER as $val => $value) {
			$ArrGetSO[$value['id_bq']] = $value['so_number'];
		}
		$data_Group	= $this->master_model->getArray('groups', array(), 'id', 'name');
		$data_pr	= $this->db->group_by('a.no_pr')->order_by('a.created_date', 'desc')->join('warehouse_planning_header a', 'a.no_ipp=b.no_ipp')->get('warehouse_planning_detail b')->result_array();
		$data = array(
			'title'			=> 'Indeks Of PR Material',
			'action'			=> 'index',
			'row_group'		=> $data_Group,
			'akses_menu'		=> $Arr_Akses,
			'data_pr'			=> $data_pr,
			'ArrGetSO'		=> $ArrGetSO
		);
		history('View PR Material');
		$this->load->view('Purchase_request/list_pr_new', $data);
	}
	public function approval_pr_new()
	{
		$this->purchase_request_model->index_approval_pr_new();
	}

	public function server_side_app_pr()
	{
		$this->purchase_request_model->get_data_json_app_pr();
	}

	public function server_side_app_pr_new()
	{
		$this->purchase_request_model->get_data_json_app_pr_new();
	}

	public function save_approve_pr()
	{
		$this->purchase_request_model->save_approve_pr();
	}

	public function save_approve_pr_new()
	{
		$this->purchase_request_model->save_approve_pr_new();
	}

	public function modal_detail_pr()
	{
		$this->purchase_request_model->modal_detail_pr();
	}

	public function modal_approve_pr()
	{
		$this->purchase_request_model->modal_approve_pr();
	}

	public function print_detail_pr()
	{
		$this->purchase_request_model->print_detail_pr();
	}

	public function print_detail_pr_new()
	{
		$no_pr = $this->uri->segment(3);
		$tanggal  = date('Y-m-d', strtotime('-1 month', strtotime(date('Y-m-d'))));
		$bulan    = ltrim(date('m', strtotime($tanggal)), '0');
		$tahun    = date('Y', strtotime($tanggal));

		$sql		= "	SELECT
							a.*,
							a.created_date AS tgl_approve,
							e.*,
							a.no_pr AS pr_ord,
							b.qty_stock,
							b.qty_booking,
							c.moq,
							(SELECT d.purchase FROM check_book_per_month d WHERE d.id_material=e.id_material AND d.tahun='" . $tahun . "' AND d.bulan='" . $bulan . "') AS book_per_month
						FROM
							tran_material_pr_header a
							LEFT JOIN tran_material_pr_detail e ON a.no_pr = e.no_pr
							LEFT JOIN warehouse_stock b ON e.id_material = b.id_material
							LEFT JOIN moq_material c ON e.id_material = c.id_material
						WHERE 1=1
							AND (b.id_gudang = '1' OR b.id_gudang = '2') AND a.no_pr = '" . $no_pr . "' ";
		$result = $this->db->query($sql)->result_array();

		$sql_non_frp = "	SELECT
							a.sts_ajuan,
							b.no_po,
							b.id_material,
							b.idmaterial,
							b.qty_request,
							b.qty_revisi,
							b.tanggal,
							b.keterangan,
							b.nm_material,
							c.satuan
						FROM
							tran_material_pr_header a
							LEFT JOIN tran_material_pr_detail b ON a.no_pr = b.no_pr
							LEFT JOIN accessories c ON b.id_material = c.id
						WHERE 1=1
							AND b.category = 'acc'
							AND a.no_pr = '" . $no_pr . "' 
						ORDER BY b.id ASC";
		$non_frp = $this->db->query($sql_non_frp)->result_array();

		$data_session	= $this->session->userdata;
		$printby		= $data_session['ORI_User']['username'];

		$data_url		= base_url();
		$Split_Beda		= explode('/', $data_url);
		$Jum_Beda		= count($Split_Beda);
		$Nama_Beda		= $Split_Beda[$Jum_Beda - 2];

		$data = array(
			'Nama_Beda' => $Nama_Beda,
			'printby' => $printby,
			'no_pr'		=> $no_pr,
			'result'		=> $result,
			'non_frp'		=> $non_frp
		);

		history('Print approve pr material ' . $no_pr);
		$this->load->view('Print/print_pr_approve_new', $data);
	}

	public function save_approve_pr_new_aksesoris()
	{
		$data = $this->input->post();
		$no_ipp 		= $data['no_ipp'];
		$tanda 			= $data['tanda'];
		$id_user 		= $data['id_user'];
		$tgl_butuh 		= (!empty($data['tgl_butuh'] and $data['tgl_butuh'] != '0000-00-00')) ? $data['tgl_butuh'] : NULL;
		$mat_atau_acc	= $this->uri->segment(3);
		$status 		= 'approve';
		$data_session	= $this->session->userdata;
		$dateTime		= date('Y-m-d H:i:s');


		if (!empty($data['detail_acc'])) {
			$detail_acc = $data['detail_acc'];
		}

		$ch_pr_header = $this->db->get_where('warehouse_planning_header', array('no_ipp' => $no_ipp))->result();

		if ($tanda == 'P') {
			$no_ippX = date('Y-m-d', strtotime($no_ipp));
			$ch_pr_header = $this->db->get_where('warehouse_planning_header', array('DATE(created_date)' => $no_ippX))->result();
		}

		//NEW
		$ArrDetail = array();
		$ArrDetailPR = array();

		$Ym = date('ym');
		$qIPP			= "SELECT MAX(no_pr) as maxP FROM tran_pr_header WHERE no_pr LIKE 'PRN" . $Ym . "%' ";
		$numrowIPP		= $this->db->query($qIPP)->num_rows();
		$resultIPP		= $this->db->query($qIPP)->result_array();
		$angkaUrut2		= $resultIPP[0]['maxP'];
		$urutan2		= (int)substr($angkaUrut2, 7, 4);
		$urutan2++;
		$urut2			= sprintf('%04s', $urutan2);
		$no_pr			= "PRN" . $Ym . $urut2;

		$qIPPX			= "SELECT MAX(no_pr_group) as maxP FROM tran_pr_header WHERE no_pr_group LIKE 'PR" . $Ym . "%' ";
		$numrowIPPX		= $this->db->query($qIPPX)->num_rows();
		$resultIPPX		= $this->db->query($qIPPX)->result_array();
		$angkaUrut2X	= $resultIPPX[0]['maxP'];
		$urutan2X		= (int)substr($angkaUrut2X, 6, 4);
		$urutan2X++;
		$urut2X			= sprintf('%04s', $urutan2X);
		$no_pr_group	= "PR" . $Ym . $urut2X;

		$ArrHeaderPR = array(
			'no_pr' => $no_pr,
			'no_pr_group' => $no_pr_group,
			'category' => 'non rutin',
			'tgl_pr'	=> date('Y-m-d'),
			'created_by' => $this->session->userdata['ORI_User']['username'],
			'created_date' => date('Y-m-d H:i:s')
		);

		$SUM_QTY = 0;
		$SUM_HARGA = 0;
		if ($mat_atau_acc == 'acc') {
			if (!empty($data['detail_acc'])) {
				foreach ($detail_acc as $val => $valx) {
					$get_material = $this->db->query("SELECT * FROM accessories WHERE id='" . $valx['id_material'] . "' LIMIT 1")->result();

					$qty_revisi = str_replace(',', '', $valx['qty_revisi']);

					$qty 	= $qty_revisi;
					$harga 	= 0;

					$SUM_QTY 	+= $qty;
					$SUM_HARGA 	+= $harga * $qty;

					$ArrDetailPR[$val]['no_pr'] 		= $no_pr;
					$ArrDetailPR[$val]['no_pr_group'] 	= $no_pr_group;
					$ArrDetailPR[$val]['category'] 		= 'rutin';
					$ArrDetailPR[$val]['tgl_pr'] 		= date('Y-m-d');
					$ArrDetailPR[$val]['id_barang'] 	= (!empty($get_material[0]->id_material)) ? $get_material[0]->id_material : $valx['id_material'];;
					$ArrDetailPR[$val]['nm_barang'] 	= get_name_acc($valx['id_material']);
					$ArrDetailPR[$val]['qty'] 			= (!empty($qty_revisi)) ? $qty_revisi : $valx['qty_request'];
					$ArrDetailPR[$val]['nilai_pr'] 		= $harga;
					$ArrDetailPR[$val]['tgl_dibutuhkan'] = $tgl_butuh;
					$ArrDetailPR[$val]['satuan']		= $valx['satuan'];
					$ArrDetailPR[$val]['app_status'] 	= 'Y';
					$ArrDetailPR[$val]['app_reason']	= NULL;
					$ArrDetailPR[$val]['app_by'] 		= $data_session['ORI_User']['username'];
					$ArrDetailPR[$val]['app_date']		= $dateTime;
					$ArrDetailPR[$val]['created_by'] 	= $data_session['ORI_User']['username'];
					$ArrDetailPR[$val]['created_date'] 	= $dateTime;

					$ArrUpDetail_acc[$val]['id'] 			= $valx['id'];
					$ArrUpDetail_acc[$val]['no_pr'] 		= $no_pr_group;
					$ArrUpDetail_acc[$val]['sts_app'] 		= ($status == 'approve') ? 'Y' : 'D';
					$ArrUpDetail_acc[$val]['sts_app_by'] 	= $this->session->userdata['ORI_User']['username'];
					$ArrUpDetail_acc[$val]['sts_app_date'] 	= date('Y-m-d H:i:s');
				}
			}
		}

		//update planning
		$ArrUpdateHEad = array(
			'no_pr'			=> $no_pr_group
		);

		$this->db->trans_start();
		if ($mat_atau_acc == 'acc') {
			if (!empty($ArrDetailPR)) {
				$this->db->insert('tran_pr_header', $ArrHeaderPR);
				$this->db->insert_batch('tran_pr_detail', $ArrDetailPR);
			}
		}

		if ($tanda == 'I') {
			$this->db->where('no_ipp', $no_ipp);
			$this->db->update('warehouse_planning_header', $ArrUpdateHEad);

			if ($mat_atau_acc == 'acc') {
				if (!empty($ArrUpDetail_acc)) {
					$this->db->where('no_ipp', $no_ipp);
					$this->db->update_batch('warehouse_planning_detail_acc', $ArrUpDetail_acc, 'id');
				}
			}
		}

		if ($tanda == 'P') {
			$this->db->where('DATE(created_date)', date('Y-m-d', strtotime($no_ipp)));
			$this->db->update('warehouse_planning_header', $ArrUpdateHEad);

			$this->db->where('DATE(created_date)', date('Y-m-d', strtotime($no_ipp)));
			$this->db->update_batch('warehouse_planning_detail', $ArrUpDetail, 'id_material');
		}

		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=> 'Save process failed. Please try again later ...',
				'status'	=> 0,
				'tanda' 	=> $tanda,
				'no_ipp' 	=> $no_ipp,
				'id_user'	=> $id_user
			);
		} else {
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=> 'Save process success. Thanks ...',
				'status'	=> 1,
				'tanda' 	=> $tanda,
				'no_ipp' 	=> $no_ipp,
				'id_user'	=> $id_user
			);
			history('Approve PR ' . $no_pr_group);
		}
		echo json_encode($Arr_Data);
	}


	//Progress PR
	public function progress_pr()
	{
		$this->purchase_request_model->index_progress_pr();
	}

	public function modal_detail_progress_pr()
	{
		$this->purchase_request_model->modal_detail_progress_pr();
	}

	public function server_side_progress_pr()
	{
		$this->purchase_request_model->get_data_json_progress_pr();
	}

	public function reject_sebagian_pr_new()
	{
		$this->purchase_request_model->reject_sebagian_pr_new();
	}

	public function reject_sebagian_pr_new_acc()
	{
		$this->purchase_request_model->reject_sebagian_pr_new_acc();
	}

	public function approve_sebagian_pr_new()
	{
		$this->purchase_request_model->approve_sebagian_pr_new();
	}

	public function approve_sebagian_pr_new_acc()
	{
		$this->purchase_request_model->approve_sebagian_pr_new_acc();
	}

	//==========================================================================================================================
	//==================================================END PURCHASE REQUEST====================================================
	//==========================================================================================================================

	//==========================================================================================================================
	//====================================================PURCHASE ORDER========================================================
	//==========================================================================================================================

	public function material_purchase()
	{
		$this->purchase_order_model->index_po();
	}

	public function server_side_po()
	{
		$this->purchase_order_model->get_data_json_po();
	}

	public function modal_detail_po()
	{
		$this->purchase_order_model->modal_detail_po();
	}

	public function modal_edit_po()
	{
		$this->purchase_order_model->modal_edit_po();
	}

	public function modal_add_po()
	{
		$this->purchase_order_model->modal_add_po();
	}

	public function server_side_list_pr()
	{
		$this->purchase_order_model->get_data_json_list_pr();
	}

	public function save_po()
	{
		$this->purchase_order_model->save_po();
	}

	public function update_po()
	{
		$this->purchase_order_model->update_po();
	}

	public function cancel_po()
	{
		$this->purchase_order_model->cancel_po();
	}

	public function cancel_sebagian_po()
	{
		$this->purchase_order_model->cancel_sebagian_po();
	}

	public function spk_po()
	{
		$this->purchase_order_model->spk_po();
	}

	public function print_rfq()
	{
		$this->purchase_order_model->print_rfq();
	}

	public function modal_edit_rfq()
	{
		$this->purchase_order_model->modal_edit_rfq();
	}

	public function delete_rfq()
	{
		$data_session	= $this->session->userdata;
		$UserName		= $data_session['ORI_User']['username'];
		$DateTime		= date('Y-m-d H:i:s');
		$no_rfq			= $this->uri->segment(3);

		$ArrUpdateH = array(
			'sts_ajuan' => 'CNC',
			'cancel_by' => $UserName,
			'cancel_date' => $DateTime
		);

		$ArrUpdateD = array(
			'no_rfq' => NULL
		);

		$this->db->trans_start();
		$this->db->where('no_rfq', $no_rfq);
		$this->db->update('tran_material_rfq_header', $ArrUpdateH);

		// $this->db->where('no_rfq', $no_rfq);
		// $this->db->update('tran_material_pr_detail', $ArrUpdateD);
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=> 'Save process failed. Please try again later ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=> 'Save process success. Thanks ...',
				'status'	=> 1
			);
			history('Delete RFQ ' . $no_rfq);
		}
		echo json_encode($Arr_Data);
	}

	//==========================================================================================================================
	//================================================END PURCHASE ORDER========================================================
	//==========================================================================================================================


	//==========================================================================================================================
	//====================================================WAREHOUSE=============================================================
	//==========================================================================================================================
	//MATERIAL STOCK
	public function material_stock()
	{
		$this->Incoming_check_model->index_material_stock();
	}

	public function server_side_material_stock()
	{
		$this->Incoming_check_model->get_data_json_material_stock();
	}

	public function modal_history()
	{
		$this->Incoming_check_model->modal_history();
	}

	public function modal_history_booking()
	{
		$this->Incoming_check_model->modal_history_booking();
	}

	//MATERIAL ADJUSTMENT
	public function incoming_material()
	{
		$this->Incoming_check_model->index_incoming_material();
	}

	public function modal_incoming_check()
	{
		$this->Incoming_check_model->modal_incoming_check();
	}

	public function server_side_incoming_material()
	{
		$this->Incoming_check_model->get_data_json_incoming_material();
	}

	public function server_side_check_material()
	{
		$has_add = has_permission('Incoming_Check.Add');
		$has_manage = has_permission('Incoming_Check.Manage');
		$has_delete = has_permission('Incoming_Check.Delete');
		$this->Incoming_check_model->get_data_json_check_material($has_add, $has_manage, $has_delete);
	}

	public function modal_detail_adjustment()
	{
		$this->Incoming_check_model->modal_detail_adjustment();
	}

	public function modal_incoming_material()
	{
		$this->Incoming_check_model->modal_incoming_material();
	}

	public function process_in_material()
	{
		$this->Incoming_check_model->process_in_material();
	}

	public function process_check_material()
	{
		$this->Incoming_check_model->process_check_material();
	}

	public function process_adjustment()
	{
		$this->Incoming_check_model->process_adjustment();
	}

	public function modal_move_gudang()
	{
		$this->Incoming_check_model->modal_move_gudang();
	}

	public function move_material()
	{
		$this->Incoming_check_model->move_material();
	}

	public function server_side_move_gudang()
	{
		$this->Incoming_check_model->get_data_json_move_gudang();
	}

	public function print_incoming()
	{
		$this->Incoming_check_model->print_incoming();
	}

	public function print_incoming2()
	{
		$this->Incoming_check_model->print_incoming2();
	}

	//REQUEST SUB GUDANG
	public function request_subgudang()
	{
		$this->Incoming_check_model->index_request_material();
	}

	public function server_side_request_material()
	{
		$this->Incoming_check_model->get_data_json_request_material();
	}

	public function modal_request_material()
	{
		$this->Incoming_check_model->modal_request_material();
	}

	public function server_side_modal_request_material()
	{
		$this->Incoming_check_model->get_data_json_modal_request_material();
	}

	public function process_request_material()
	{
		$this->Incoming_check_model->process_request_material();
	}

	public function print_request()
	{
		$kode_trans     = $this->uri->segment(3);
		$check     		= $this->uri->segment(4);
		$data_session	= $this->session->userdata;
		$printby		= $data_session['ORI_User']['username'];

		$data_url		= base_url();
		$Split_Beda		= explode('/', $data_url);
		$Jum_Beda		= count($Split_Beda);
		$Nama_Beda		= $Split_Beda[$Jum_Beda - 2];

		$GET_SO_NUMBER = $this->db->get('so_number')->result_array();
		$ArrGetSO = [];
		foreach ($GET_SO_NUMBER as $val => $value) {
			$ArrGetSO[$value['id_bq']] = $value['so_number'];
		}

		$GET_SPK_NUMBER = $this->db->get_where('so_detail_header', array('no_spk <>' => NULL))->result_array();
		$ArrGetSPK = [];
		$ArrGetIPP = [];
		foreach ($GET_SPK_NUMBER as $val => $value) {
			$ArrGetSPK[$value['id']] = $value['no_spk'];
			$ArrGetIPP[$value['id']] = $value['id_bq'];
		}

		$data = array(
			'ArrGetSO' => $ArrGetSO,
			'ArrGetSPK' => $ArrGetSPK,
			'ArrGetIPP' => $ArrGetIPP,
			'Nama_Beda' => $Nama_Beda,
			'printby' => $printby,
			'kode_trans' => $kode_trans,
			'check' => $check
		);

		history('Print Request Material ' . $kode_trans);
		$this->load->view('Print/print_list_subgudang', $data);
	}

	public function print_surat_jalan()
	{
		$this->Incoming_check_model->print_surat_jalan();
	}

	public function print_request_sub()
	{
		$this->Incoming_check_model->print_request_sub();
	}

	public function modal_request_check()
	{
		$this->Incoming_check_model->modal_request_check();
	}

	public function modal_request_edit()
	{
		$this->Incoming_check_model->modal_request_edit();
	}

	public function get_list_exp()
	{
		$this->Incoming_check_model->get_list_exp();
	}

	public function save_temp_mutasi()
	{
		$this->Incoming_check_model->save_temp_mutasi();
	}

	public function cancel_request()
	{
		$data 			= $this->input->post();
		$data_session	= $this->session->userdata;

		$kode_trans			= $data['kode_trans'];
		$filter_pusat		= $data['filter_pusat'];
		$filter_subgudang	= $data['filter_subgudang'];
		$filter_uri_tanda	= $data['filter_uri_tanda'];


		$ArrDeleted = array(
			'deleted_by' 	=> $data_session['ORI_User']['username'],
			'deleted_date' 	=> date('Y-m-d H:i:s')
		);

		$this->db->trans_start();
		$this->db->where('kode_trans', $kode_trans);
		$this->db->update('warehouse_adjustment', $ArrDeleted);
		$this->db->trans_complete();


		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=> 'Save process failed. Please try again later ...',
				'status'	=> 0,
				'filter_pusat'	=> $filter_pusat,
				'filter_subgudang'	=> $filter_subgudang,
				'filter_uri_tanda'	=> $filter_uri_tanda,
			);
		} else {
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=> 'Save process success. Thanks ...',
				'status'	=> 1,
				'filter_pusat'	=> $filter_pusat,
				'filter_subgudang'	=> $filter_subgudang,
				'filter_uri_tanda'	=> $filter_uri_tanda,
			);
			history("Cancel request : " . $kode_trans);
		}
		echo json_encode($Arr_Data);
	}

	//REQUEST PRODUKSI
	public function request_produksi()
	{
		$tanda = $this->uri->segment(3);
		$controller			= ucfirst(strtolower($this->uri->segment(1)) . '/' . strtolower($this->uri->segment(2)));
		$judul = "Warehouse Material >> Gudang Produksi >> Request Produksi";
		if (!empty($tanda)) {
			$controller			= ucfirst(strtolower($this->uri->segment(1)) . '/' . strtolower($this->uri->segment(2)) . '/' . $tanda);
			$judul = "Warehouse Material >> Sub Gudang >> Request List";
		}
		// echo $controller.'<br>';
		// exit;
		$Arr_Akses			= getAcccesmenu($controller);
		// print_r($Arr_Akses);
		if ($Arr_Akses['read'] != '1') {
			$this->session->set_flashdata("alert_data", "<div class=\"alert alert-warning\" id=\"flash-message\">You Don't Have Right To Access This Page, Please Contact Your Administrator....</div>");
			redirect(site_url('dashboard'));
		}

		$data_Group			= $this->master_model->getArray('groups', array(), 'id', 'name');
		$pusat				= $this->db->query("SELECT * FROM warehouse WHERE category='subgudang' ORDER BY urut ASC")->result_array();
		$subgudang			= $this->db->query("SELECT * FROM warehouse WHERE category='produksi' ORDER BY urut ASC")->result_array();
		$no_ipp				= $this->db->query("SELECT
													a.no_ipp,
													b.so_number,
													a.id_product
												FROM
													production_spk a
													LEFT JOIN so_number b ON a.no_ipp=REPLACE(b.id_bq, 'BQ-', '')
												WHERE 1=1
													-- a.spk2_cost = 'N'
													AND a.created_date >= '2022-02-01'
												GROUP BY a.no_ipp")->result_array();
		$no_ipp_deadstok 	= $this->db
			->select('product_code_cut AS code_est,no_ipp,product_code AS no_so')
			->get_where('production_spk', array('id_product' => 'deadstok'))
			->result_array();
		$list_ipp_req		= $this->db->query("SELECT no_ipp FROM warehouse_adjustment WHERE no_ipp LIKE 'IPP%' GROUP BY no_ipp")->result_array();
		$uri_tanda			= $this->uri->segment(3);
		$data = array(
			'title'			=> $judul,
			'action'		=> 'index',
			'row_group'		=> $data_Group,
			'akses_menu'	=> $Arr_Akses,
			'pusat'			=> $pusat,
			'subgudang'		=> $subgudang,
			'no_ipp'		=> $no_ipp,
			'no_ipp_deadstok'		=> $no_ipp_deadstok,
			'list_ipp_req'	=> $list_ipp_req,
			'uri_tanda'		=> $uri_tanda,
			'tanki'			=> $this->tanki_model,
		);
		history('View Request Produksi');
		$this->load->view('Warehouse/request_produksi', $data);
	}

	public function server_side_request_produksi()
	{
		$this->Incoming_check_model->get_data_json_request_produksi();
	}

	public function modal_request_produksi()
	{
		$this->Incoming_check_model->modal_request_produksi();
	}

	public function server_side_modal_request_produksi()
	{
		$this->Incoming_check_model->get_data_json_modal_request_produksi();
	}

	public function process_request_produksi()
	{
		$this->Incoming_check_model->process_request_produksi();
	}

	public function request_mat_resin()
	{
		$this->Incoming_check_model->request_mat_resin();
	}

	public function save_update_produksi_2_new()
	{
		$data 			= $this->input->post();
		$data_session	= $this->session->userdata;
		$username = $this->session->userdata['ORI_User']['username'];
		$datetime = date('Y-m-d H:i:s');

		$id				= $data['id'];
		$kode_trans		= $data['kode_trans'];
		$kode_spk 		= $data['kode_spk'];
		$hist_produksi	= $data['hist_produksi'];
		$id_gudang 		= $data['id_gudang_from'];
		$id_gudang_wip 	= $data['id_gudang'];
		$no_request 	= $data['no_request'];
		$date_produksi 	= (!empty($data['date_produksi'])) ? $data['date_produksi'] : NULL;
		$detail_input	= $data['detail_input'];
		$requesta_add 	= (!empty($data['requesta_add'])) ? $data['requesta_add'] : array();
		$edit_add 		= (!empty($data['edit_add'])) ? $data['edit_add'] : array();
		$GET_MATERIAL	= get_detail_material();
		$GET_PERCENT	= get_persent_by_subgudang();

		$dateCreated = $datetime;
		if ($hist_produksi != '0') {
			$dateCreated = $hist_produksi;
		}

		$kode_spk_created = $kode_spk . '/' . $dateCreated;

		$where_in_ID = [];
		$WHERE_KEY_QTY = [];
		foreach ($detail_input as $key => $value) {
			$QTY = str_replace(',', '', $value['qty']);
			if ($QTY > 0) {
				$where_in_ID[] = $value['id'];
				$WHERE_KEY_QTY[$value['id']] 	= $QTY;
			}
		}

		//UPLOAD DOCUMENT
		$file_name = '';
		if (!empty($_FILES["upload_spk"]["name"])) {
			$target_dir     = "assets/file/produksi/";
			$target_dir_u   = $_SERVER['DOCUMENT_ROOT'] . "/assets/file/produksi/";
			$name_file      = 'qc_eng_change_req_' . date('Ymdhis');
			$target_file    = $target_dir . basename($_FILES["upload_spk"]["name"]);
			$name_file_ori  = basename($_FILES["upload_spk"]["name"]);
			$imageFileType  = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
			$nama_upload    = $target_dir_u . $name_file . "." . $imageFileType;
			$file_name    	= $name_file . "." . $imageFileType;

			if (!empty($_FILES["upload_spk"]["tmp_name"])) {
				move_uploaded_file($_FILES["upload_spk"]["tmp_name"], $nama_upload);
			}
		}

		$ArrWhereIN_ = [];
		$ArrWhereIN_IDMILIK = [];
		foreach ($detail_input as $key => $value) {
			$QTY = str_replace(',', '', $value['qty']);
			if ($QTY > 0) {
				$ArrWhereIN_[] = $value['id'];
				$ArrWhereIN_IDMILIK[] = $value['id_milik'];
			}
		}
		$ArrUpdateStock = [];
		//ADD MATERIAL
		$ArrRequestHist = [];
		$ArrRequest = [];
		$nomor = 999;
		if (!empty($requesta_add)) {
			foreach ($requesta_add as $key => $value) {
				$nomor++;
				$TERPAKAI = str_replace(',', '', $value['terpakai']);
				$ArrRequest[$key]['kode_spk'] = $kode_spk;
				$ArrRequest[$key]['kode_trans'] = $kode_trans;
				$ArrRequest[$key]['hist_produksi'] = $hist_produksi;
				$ArrRequest[$key]['id_utama'] = $id;
				$ArrRequest[$key]['id_material'] = $value['id_material'];
				$ArrRequest[$key]['actual_type'] = $value['actual_type'];
				$ArrRequest[$key]['layer'] = $value['layer'];
				$ArrRequest[$key]['persen'] = $value['persen'];
				$ArrRequest[$key]['terpakai'] = $TERPAKAI;
				$ArrRequest[$key]['created_by'] = $username;
				$ArrRequest[$key]['created_date'] = $datetime;

				$ArrUpdateStock[$nomor]['id'] 	= $value['actual_type'];
				$ArrUpdateStock[$nomor]['qty'] 	= $TERPAKAI;

				$ArrRequestHist[$key]['kode_spk'] = $kode_spk;
				$ArrRequestHist[$key]['kode_trans'] = $kode_trans;
				$ArrRequestHist[$key]['hist_produksi'] = $hist_produksi;
				$ArrRequestHist[$key]['id_utama'] = $id;
				$ArrRequestHist[$key]['id_material'] = $value['id_material'];
				$ArrRequestHist[$key]['actual_type'] = $value['actual_type'];
				$ArrRequestHist[$key]['layer'] = $value['layer'];
				$ArrRequestHist[$key]['persen'] = $value['persen'];
				$ArrRequestHist[$key]['terpakai'] = $TERPAKAI;
				$ArrRequestHist[$key]['created_by'] = $username;
				$ArrRequestHist[$key]['created_date'] = $datetime;
			}
		}

		//ADD MATERIAL
		$ArrEditAdd = [];
		$nomor = 999;
		if (!empty($edit_add)) {
			foreach ($edit_add as $key => $value) {
				$nomor++;
				$TERPAKAI = str_replace(',', '', $value['terpakai']);
				if ($TERPAKAI > 0) {
					$getLastQty = $this->db->get_where('production_spk_add', array('id' => $value['id']))->result();
					$QTY_ADD = (!empty($getLastQty[0]->terpakai)) ? $getLastQty[0]->terpakai : 0;
					$ArrEditAdd[$key]['id'] 			= $value['id'];
					$ArrEditAdd[$key]['terpakai'] 		= $TERPAKAI + $QTY_ADD;
					$ArrEditAdd[$key]['created_by'] 	= $username;
					$ArrEditAdd[$key]['created_date'] 	= $datetime;

					$ArrUpdateStock[$nomor]['id'] 	= $value['actual_type2'];
					$ArrUpdateStock[$nomor]['qty'] 	= $TERPAKAI;

					$UNIQ = '9999' . $key;
					$ArrRequestHist[$UNIQ]['kode_spk'] = $kode_spk;
					$ArrRequestHist[$UNIQ]['kode_trans'] = $kode_trans;
					$ArrRequestHist[$UNIQ]['hist_produksi'] = $hist_produksi;
					$ArrRequestHist[$UNIQ]['id_utama'] = $id;
					$ArrRequestHist[$UNIQ]['id_material'] = $getLastQty[0]->id_material;
					$ArrRequestHist[$UNIQ]['actual_type'] = $getLastQty[0]->actual_type;
					$ArrRequestHist[$UNIQ]['layer'] = $getLastQty[0]->layer;
					$ArrRequestHist[$UNIQ]['persen'] = $getLastQty[0]->persen;
					$ArrRequestHist[$UNIQ]['terpakai'] = $TERPAKAI;
					$ArrRequestHist[$UNIQ]['created_by'] = $username;
					$ArrRequestHist[$UNIQ]['created_date'] = $datetime;
				}
			}
		}

		$ArrLooping = ['detail_liner', 'detail_joint', 'detail_strn1', 'detail_strn2', 'detail_str', 'detail_ext', 'detail_topcoat', 'resin_and_add'];

		$get_detail_spk = $this->db->where_in('id', $ArrWhereIN_)->get_where('production_spk', array('kode_spk' => $kode_spk))->result_array();

		// print_r($get_detail_spk);
		// exit;
		$ArrGroup = [];
		$ArrAktualResin = [];
		$ArrAktualPlus = [];
		$ArrAktualAdd = [];
		$ArrUpdate = [];

		$ArrDeatil = [];
		$ArrDeatilAdj = [];
		$ArrUpdateRequest = [];
		$nomor = 0;
		$SUM_MAT = 0;
		foreach ($get_detail_spk as $key => $value) {
			foreach ($ArrLooping as $valueX) {
				if (!empty($data[$valueX])) {
					if ($valueX == 'detail_liner') {
						$DETAIL_NAME = 'LINER THIKNESS / CB';
					}
					if ($valueX == 'detail_joint') {
						$DETAIL_NAME = 'RESIN AND ADD';
					}
					if ($valueX == 'detail_strn1') {
						$DETAIL_NAME = 'STRUKTUR NECK 1';
					}
					if ($valueX == 'detail_strn2') {
						$DETAIL_NAME = 'STRUKTUR NECK 2';
					}
					if ($valueX == 'detail_str') {
						$DETAIL_NAME = 'STRUKTUR THICKNESS';
					}
					if ($valueX == 'detail_ext') {
						$DETAIL_NAME = 'EXTERNAL LAYER THICKNESS';
					}
					if ($valueX == 'detail_topcoat') {
						$DETAIL_NAME = 'TOPCOAT';
					}
					if ($valueX == 'resin_and_add') {
						$DETAIL_NAME = 'RESIN AND ADD';
					}
					$detailX = $data[$valueX];
					// print_r($detailX);

					if ($value['id_product'] != 'deadstok') {
						$get_produksi 	= $this->db->limit(1)->select('id, id_category')->get_where('production_detail', array('id_milik' => $value['id_milik'], 'id_produksi' => 'PRO-' . $value['no_ipp'], 'kode_spk' => $value['kode_spk']))->result();
						//,'print_merge_date'=>$dateCreated
						if (empty($get_produksi)) {
							$Arr_Kembali	= array(
								'pesan'		=> 'Error data proccess, please contact administrator !!! ErrorCode: id_ml:' . $value['id_milik'] . '&spk:' . $value['kode_spk'] . '&tm:' . $dateCreated,
								'status'	=> 2
							);
							// cari di 	production_detail bedasarkan id milik dan kode spk, ganti waktu seperti di alert
							echo json_encode($Arr_Kembali);
							return false;
						}
					}

					foreach ($detailX as $key2 => $value2) {
						$get_liner 		= $this->db->select('id, id_material, qty_order AS berat, key_gudang, check_qty_oke')->get_where('warehouse_adjustment_detail', array('kode_trans' => $kode_trans, 'keterangan' => $DETAIL_NAME))->result_array();
						// print_r($get_liner);
						// exit;
						if (!empty($get_liner)) {
							foreach ($get_liner as $key3 => $value3) {
								if ($value2['id_key'] == $value3['key_gudang']) {
									$nomor 		= $value2['id_key'];
									$ACTUAL_MAT = $value2['actual_type'];
									$QTY_INP	= $WHERE_KEY_QTY[$value['id']];
									$BERAT_UNIT = $value3['berat'] / $QTY_INP;
									$total_est 	= $BERAT_UNIT * $QTY_INP;
									$total_act  = 0;
									if ($value2['kebutuhan'] > 0) {
										$total_act 	= ($total_est / str_replace(',', '', $value2['kebutuhan'])) * str_replace(',', '', $value2['terpakai']);
									}
									$SUM_MAT 	+= $total_act;
									$unit_act 	= $total_act / $QTY_INP;
									$PERSEN 	= str_replace(',', '', $value2['persen']);
									if (!empty($PERSEN) and $PERSEN > 0) {
										$PERSEN 	= str_replace(',', '', $value2['persen']);
									} else {
										$KEY        = $kode_trans . '-' . $value2['id_key'];
										$PERSEN 	= (!empty($GET_PERCENT[$KEY]['persen']) and $GET_PERCENT[$KEY]['persen'] > 0) ? $GET_PERCENT[$KEY]['persen'] : '';;
									}
									//UPDATE FLAG SPK 2
									$ArrUpdate[$key . $key2 . $key3 . $nomor]['id'] 			= $value['id'];
									$ArrUpdate[$key . $key2 . $key3 . $nomor]['spk2'] 		= 'Y';
									$ArrUpdate[$key . $key2 . $key3 . $nomor]['spk2_by'] 		= $username;
									$ArrUpdate[$key . $key2 . $key3 . $nomor]['spk2_date']	= $datetime;
									// $ArrUpdate[$key.$key2.$key3.$nomor]['gudang2']	= $id_gudang;

									//ARRAY STOCK
									$ArrUpdateStock[$nomor]['id'] 	= $ACTUAL_MAT;
									$ArrUpdateStock[$nomor]['qty'] 	= $total_act;
									//UPDATE ADJUSTMENT DETAIL
									$ArrDeatil[$key . $key2 . $key3 . $nomor]['id'] 			    = $value3['id'];
									$ArrDeatil[$key . $key2 . $key3 . $nomor]['id_material'] 		= $ACTUAL_MAT;
									$ArrDeatil[$key . $key2 . $key3 . $nomor]['qty_rusak'] 		= $PERSEN;
									$ArrDeatil[$key . $key2 . $key3 . $nomor]['check_qty_oke'] 	= $total_act + $value3['check_qty_oke'];
									$ArrDeatil[$key . $key2 . $key3 . $nomor]['check_qty_rusak']	= $BERAT_UNIT;
									$ArrDeatil[$key . $key2 . $key3 . $nomor]['check_keterangan']	= $DETAIL_NAME;
									$ArrDeatil[$key . $key2 . $key3 . $nomor]['update_by'] 		= $username;
									$ArrDeatil[$key . $key2 . $key3 . $nomor]['update_date'] 		= $datetime;
									//INSERT ADJUSTMENT CHECK
									$ArrDeatilAdj[$key . $key2 . $key3 . $nomor]['id_detail'] 	= $value3['id'];
									$ArrDeatilAdj[$key . $key2 . $key3 . $nomor]['kode_trans'] 	= $kode_trans;
									$ArrDeatilAdj[$key . $key2 . $key3 . $nomor]['no_ipp'] 		= $no_request;
									$ArrDeatilAdj[$key . $key2 . $key3 . $nomor]['id_material'] 	= $ACTUAL_MAT;
									$ArrDeatilAdj[$key . $key2 . $key3 . $nomor]['nm_material'] 	= $GET_MATERIAL[$ACTUAL_MAT]['nm_material'];
									$ArrDeatilAdj[$key . $key2 . $key3 . $nomor]['id_category'] 	= $GET_MATERIAL[$ACTUAL_MAT]['id_category'];
									$ArrDeatilAdj[$key . $key2 . $key3 . $nomor]['nm_category'] 	= $GET_MATERIAL[$ACTUAL_MAT]['nm_category'];
									$ArrDeatilAdj[$key . $key2 . $key3 . $nomor]['qty_order'] 	= $value3['berat'];
									$ArrDeatilAdj[$key . $key2 . $key3 . $nomor]['qty_oke'] 		= $total_act;
									$ArrDeatilAdj[$key . $key2 . $key3 . $nomor]['qty_rusak'] 	= $PERSEN;
									$ArrDeatilAdj[$key . $key2 . $key3 . $nomor]['expired_date'] 	= NULL;
									$ArrDeatilAdj[$key . $key2 . $key3 . $nomor]['keterangan'] 	= $DETAIL_NAME;
									$ArrDeatilAdj[$key . $key2 . $key3 . $nomor]['update_by'] 	= $username;
									$ArrDeatilAdj[$key . $key2 . $key3 . $nomor]['update_date'] 	= $datetime;

									//UPDATE REQUEST
									$ArrUpdateRequest[$key . $key2 . $key3 . $nomor]['id_key'] 	= $value3['key_gudang'];
									$ArrUpdateRequest[$key . $key2 . $key3 . $nomor]['aktual'] 	= $total_act;
								}
							}
						}
					}
				}
			}
		}

		//grouping sum
		$temp = [];
		$tempx = [];
		$grouping_temp = [];
		foreach ($ArrUpdateStock as $value) {
			if ($value['qty'] > 0) {
				if (!array_key_exists($value['id'], $temp)) {
					$temp[$value['id']] = 0;
				}
				$temp[$value['id']] += $value['qty'];
			}
		}

		//Mengurangi Booking
		$getDetailSPK 	= $this->db->get_where('production_spk', array('kode_spk' => $kode_spk))->result();
		$no_ipp 		= (!empty($getDetailSPK[0]->no_ipp)) ? $getDetailSPK[0]->no_ipp : 0;

		$id_gudang_booking = 2;
		$GETS_STOCK = get_warehouseStockAllMaterial();
		$CHECK_BOOK = get_CheckBooking($no_ipp);
		$ArrUpdate 		= [];
		$ArrUpdateHist 	= [];
		// print_r($temp);
		if ($CHECK_BOOK === TRUE and $no_ipp != 0) {
			// echo 'Masuk';
			foreach ($temp as $material => $qty) {
				$KEY 		= $material . '-' . $id_gudang_booking;
				$booking 	= (!empty($GETS_STOCK[$KEY]['booking'])) ? $GETS_STOCK[$KEY]['booking'] : 0;
				$stock 		= (!empty($GETS_STOCK[$KEY]['stock'])) ? $GETS_STOCK[$KEY]['stock'] : 0;
				$rusak 		= (!empty($GETS_STOCK[$KEY]['rusak'])) ? $GETS_STOCK[$KEY]['rusak'] : 0;
				$id_stock 	= (!empty($GETS_STOCK[$KEY]['id'])) ? $GETS_STOCK[$KEY]['id'] : null;
				$idmaterial 	= (!empty($GETS_STOCK[$KEY]['idmaterial'])) ? $GETS_STOCK[$KEY]['idmaterial'] : null;
				$nm_material 	= (!empty($GETS_STOCK[$KEY]['nm_material'])) ? $GETS_STOCK[$KEY]['nm_material'] : null;
				$id_category 	= (!empty($GETS_STOCK[$KEY]['id_category'])) ? $GETS_STOCK[$KEY]['id_category'] : null;
				$nm_category 	= (!empty($GETS_STOCK[$KEY]['nm_category'])) ? $GETS_STOCK[$KEY]['nm_category'] : null;
				// echo 'ID:'.$id_stock;
				if (!empty($id_stock)) {
					$ArrUpdate[$material]['id'] = $id_stock;
					$ArrUpdate[$material]['qty_booking'] = $booking - $qty;

					$ArrUpdateHist[$material]['id_material'] 	= $material;
					$ArrUpdateHist[$material]['idmaterial'] 	= $idmaterial;
					$ArrUpdateHist[$material]['nm_material'] 	= $nm_material;
					$ArrUpdateHist[$material]['id_category'] 	= $id_category;
					$ArrUpdateHist[$material]['nm_category'] 	= $nm_category;
					$ArrUpdateHist[$material]['id_gudang'] 		= $id_gudang_booking;
					$ArrUpdateHist[$material]['kd_gudang'] 		= get_name('warehouse', 'kd_gudang', 'id', $id_gudang_booking);
					$ArrUpdateHist[$material]['id_gudang_dari'] = NULL;
					$ArrUpdateHist[$material]['kd_gudang_dari'] = 'BOOKING';
					$ArrUpdateHist[$material]['id_gudang_ke'] 	= NULL;
					$ArrUpdateHist[$material]['kd_gudang_ke'] 	= 'PENGURANG';
					$ArrUpdateHist[$material]['qty_stock_awal'] 	= $stock;
					$ArrUpdateHist[$material]['qty_stock_akhir'] 	= $stock;
					$ArrUpdateHist[$material]['qty_booking_awal'] 	= $booking;
					$ArrUpdateHist[$material]['qty_booking_akhir'] 	= $booking - $qty;
					$ArrUpdateHist[$material]['qty_rusak_awal'] 	= $rusak;
					$ArrUpdateHist[$material]['qty_rusak_akhir'] 	= $rusak;
					$ArrUpdateHist[$material]['no_ipp'] 			= $no_ipp;
					$ArrUpdateHist[$material]['jumlah_mat'] 		= $qty;
					$ArrUpdateHist[$material]['ket'] 				= 'pengurangan booking ' . $kode_trans;
					$ArrUpdateHist[$material]['update_by'] 			= $username;
					$ArrUpdateHist[$material]['update_date'] 		= $datetime;
				}
			}
		}

		// print_r($ArrUpdate);
		// print_r($ArrUpdateHist);
		// exit;

		//ENd Mengurangi Booking

		foreach ($ArrUpdateStock as $value) {
			if (!array_key_exists($value['id'], $tempx)) {
				$tempx[$value['id']]['good'] = 0;
			}
			$tempx[$value['id']]['good'] += $value['qty'];

			$grouping_temp[$value['id']]['id'] 			= $value['id'];
			$grouping_temp[$value['id']]['qty_good'] 	= $tempx[$value['id']]['good'];
		}

		move_warehouse($ArrUpdateStock, $id_gudang, $id_gudang_wip, $kode_trans);

		//UPDATE NOMOR SURAT JALAN
		$monthYear 		= date('/m/Y');
		$kode_gudang 	= get_name('warehouse', 'kode', 'id', $id_gudang);

		$getDetAjust 	= $this->db->get_where('warehouse_adjustment', array('kode_trans' => $kode_trans))->result();

		$qIPP			= "SELECT MAX(no_surat_jalan) as maxP FROM warehouse_adjustment WHERE no_surat_jalan LIKE '%/IA" . $kode_gudang . $monthYear . "' ";
		$numrowIPP		= $this->db->query($qIPP)->num_rows();
		$resultIPP		= $this->db->query($qIPP)->result_array();
		$angkaUrut2		= $resultIPP[0]['maxP'];
		$urutan2		= (int)substr($angkaUrut2, 0, 3);
		$urutan2++;
		$urut2			= sprintf('%03s', $urutan2);
		$no_surat_jalan	= $urut2 . "/IA" . $kode_gudang . $monthYear;

		$ArrUpdateHeadAjudtment = array(
			'jumlah_mat_check' => $getDetAjust[0]->jumlah_mat_check + $SUM_MAT,
			'no_surat_jalan' => $no_surat_jalan,
			'file_eng_change' => $file_name,
			'checked_by' => $username,
			'checked_date' => $datetime
		);

		$UpdateRealFlag = array(
			'upload_real2' => "Y",
			'upload_by2' =>  $username,
			'upload_date2' => $datetime
		);

		$UpdatePrintHeader = array(
			'aktual_by' =>  $username,
			'aktual_date' => $datetime
		);

		$this->db->trans_start();

		if (!empty($grouping_temp)) {
			insert_jurnal($grouping_temp, $id_gudang, $id_gudang_wip, $kode_trans, 'transfer subgudang - produksi', 'pengurangan subgudang', 'penambahan gudang produksi');
		}

		$this->db->where('kode_trans', $kode_trans);
		$this->db->update('warehouse_adjustment', $ArrUpdateHeadAjudtment);

		$this->db->update_batch('warehouse_adjustment_detail', $ArrDeatil, 'id');
		$this->db->insert_batch('warehouse_adjustment_check', $ArrDeatilAdj);

		if (!empty($ArrUpdate)) {
			$this->db->update_batch('warehouse_stock', $ArrUpdate, 'id');
			$this->db->insert_batch('warehouse_history', $ArrUpdateHist);
		}

		if (!empty($ArrRequest)) {
			$this->db->insert_batch('production_spk_add', $ArrRequest);
		}

		if (!empty($ArrEditAdd)) {
			$this->db->update_batch('production_spk_add', $ArrEditAdd, 'id');
		}

		if (!empty($ArrRequestHist)) {
			$this->db->insert_batch('production_spk_add_hist', $ArrRequestHist);
		}

		if (!empty($ArrUpdateRequest)) {
			$this->db->where('kode_uniq', $no_request);
			$this->db->update_batch('print_detail', $ArrUpdateRequest, 'id_key');
		}
		$this->db->where('kode_uniq', $no_request);
		$this->db->update('print_header', $UpdatePrintHeader);
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Kembali	= array(
				'pesan'		=> 'Process Failed. Please try again later ...',
				'status'	=> 2,
				'kode_spk'	=> $id
			);
		} else {
			$this->db->trans_commit();
			$Arr_Kembali	= array(
				'pesan'		=> 'Process Success. Thank you & have a nice day ...',
				'status'	=> 1,
				'kode_spk'	=> $id
			);

			history('Approve request producksi ' . $kode_spk . '/' . $kode_trans . '/' . $id);
		}
		echo json_encode($Arr_Kembali);
	}

	public function save_update_produksi_2_new_close()
	{
		$data 			= $this->input->post();
		$data_session	= $this->session->userdata;
		$username = $this->session->userdata['ORI_User']['username'];
		$datetime = date('Y-m-d H:i:s');

		$id				= $data['id'];
		$kode_trans		= $data['kode_trans'];
		$kode_spk 		= $data['kode_spk'];
		$hist_produksi	= $data['hist_produksi'];
		$id_gudang 		= $data['id_gudang_from'];
		$id_gudang_wip 	= $data['id_gudang'];
		$no_request 	= $data['no_request'];
		$date_produksi 	= (!empty($data['date_produksi'])) ? $data['date_produksi'] : NULL;
		$detail_input	= $data['detail_input'];
		$requesta_add 	= (!empty($data['requesta_add'])) ? $data['requesta_add'] : array();
		$edit_add 		= (!empty($data['edit_add'])) ? $data['edit_add'] : array();
		$GET_MATERIAL	= get_detail_material();

		$dateCreated = $datetime;
		if ($hist_produksi != '0') {
			$dateCreated = $hist_produksi;
		}

		$kode_spk_created = $kode_spk . '/' . $dateCreated;

		$where_in_ID = [];
		$WHERE_KEY_QTY = [];
		foreach ($detail_input as $key => $value) {
			$QTY = str_replace(',', '', $value['qty']);
			if ($QTY > 0) {
				$where_in_ID[] = $value['id'];
				$WHERE_KEY_QTY[$value['id']] 	= $QTY;
			}
		}

		//UPLOAD DOCUMENT
		$file_name = '';
		if (!empty($_FILES["upload_spk"]["name"])) {
			$target_dir     = "assets/file/produksi/";
			$target_dir_u   = $_SERVER['DOCUMENT_ROOT'] . "/assets/file/produksi/";
			$name_file      = 'qc_eng_change_req_' . date('Ymdhis');
			$target_file    = $target_dir . basename($_FILES["upload_spk"]["name"]);
			$name_file_ori  = basename($_FILES["upload_spk"]["name"]);
			$imageFileType  = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
			$nama_upload    = $target_dir_u . $name_file . "." . $imageFileType;
			$file_name    	= $name_file . "." . $imageFileType;

			if (!empty($_FILES["upload_spk"]["tmp_name"])) {
				move_uploaded_file($_FILES["upload_spk"]["tmp_name"], $nama_upload);
			}
		}

		$ArrWhereIN_ = [];
		$ArrWhereIN_IDMILIK = [];
		foreach ($detail_input as $key => $value) {
			$QTY = str_replace(',', '', $value['qty']);
			if ($QTY > 0) {
				$ArrWhereIN_[] = $value['id'];
				$ArrWhereIN_IDMILIK[] = $value['id_milik'];
			}
		}
		$ArrUpdateStock = [];
		//ADD MATERIAL
		$ArrRequestHist = [];
		$ArrRequest = [];
		$nomor = 999;
		if (!empty($requesta_add)) {
			foreach ($requesta_add as $key => $value) {
				$nomor++;
				$TERPAKAI = str_replace(',', '', $value['terpakai']);
				$ArrRequest[$key]['kode_spk'] = $kode_spk;
				$ArrRequest[$key]['kode_trans'] = $kode_trans;
				$ArrRequest[$key]['hist_produksi'] = $hist_produksi;
				$ArrRequest[$key]['id_utama'] = $id;
				$ArrRequest[$key]['id_material'] = $value['id_material'];
				$ArrRequest[$key]['actual_type'] = $value['actual_type'];
				$ArrRequest[$key]['layer'] = $value['layer'];
				$ArrRequest[$key]['persen'] = $value['persen'];
				$ArrRequest[$key]['terpakai'] = $TERPAKAI;
				$ArrRequest[$key]['created_by'] = $username;
				$ArrRequest[$key]['created_date'] = $datetime;

				$ArrUpdateStock[$nomor]['id'] 	= $value['actual_type'];
				$ArrUpdateStock[$nomor]['qty'] 	= $TERPAKAI;

				$ArrRequestHist[$key]['kode_spk'] = $kode_spk;
				$ArrRequestHist[$key]['kode_trans'] = $kode_trans;
				$ArrRequestHist[$key]['hist_produksi'] = $hist_produksi;
				$ArrRequestHist[$key]['id_utama'] = $id;
				$ArrRequestHist[$key]['id_material'] = $value['id_material'];
				$ArrRequestHist[$key]['actual_type'] = $value['actual_type'];
				$ArrRequestHist[$key]['layer'] = $value['layer'];
				$ArrRequestHist[$key]['persen'] = $value['persen'];
				$ArrRequestHist[$key]['terpakai'] = $TERPAKAI;
				$ArrRequestHist[$key]['created_by'] = $username;
				$ArrRequestHist[$key]['created_date'] = $datetime;
			}
		}

		//ADD MATERIAL
		$ArrEditAdd = [];
		$nomor = 999;
		if (!empty($edit_add)) {
			foreach ($edit_add as $key => $value) {
				$nomor++;
				$TERPAKAI = str_replace(',', '', $value['terpakai']);
				if ($TERPAKAI > 0) {
					$getLastQty = $this->db->get_where('production_spk_add', array('id' => $value['id']))->result();
					$QTY_ADD = (!empty($getLastQty[0]->terpakai)) ? $getLastQty[0]->terpakai : 0;
					$ArrEditAdd[$key]['id'] 			= $value['id'];
					$ArrEditAdd[$key]['terpakai'] 		= $TERPAKAI + $QTY_ADD;
					$ArrEditAdd[$key]['created_by'] 	= $username;
					$ArrEditAdd[$key]['created_date'] 	= $datetime;

					$ArrUpdateStock[$nomor]['id'] 	= $value['actual_type2'];
					$ArrUpdateStock[$nomor]['qty'] 	= $TERPAKAI;

					$UNIQ = '9999' . $key;
					$ArrRequestHist[$UNIQ]['kode_spk'] = $kode_spk;
					$ArrRequestHist[$UNIQ]['kode_trans'] = $kode_trans;
					$ArrRequestHist[$UNIQ]['hist_produksi'] = $hist_produksi;
					$ArrRequestHist[$UNIQ]['id_utama'] = $id;
					$ArrRequestHist[$UNIQ]['id_material'] = $getLastQty[0]->id_material;
					$ArrRequestHist[$UNIQ]['actual_type'] = $getLastQty[0]->actual_type;
					$ArrRequestHist[$UNIQ]['layer'] = $getLastQty[0]->layer;
					$ArrRequestHist[$UNIQ]['persen'] = $getLastQty[0]->persen;
					$ArrRequestHist[$UNIQ]['terpakai'] = $TERPAKAI;
					$ArrRequestHist[$UNIQ]['created_by'] = $username;
					$ArrRequestHist[$UNIQ]['created_date'] = $datetime;
				}
			}
		}

		$ArrLooping = ['detail_liner', 'detail_joint', 'detail_strn1', 'detail_strn2', 'detail_str', 'detail_ext', 'detail_topcoat', 'resin_and_add'];

		$get_detail_spk = $this->db->where_in('id', $ArrWhereIN_)->get_where('production_spk', array('kode_spk' => $kode_spk))->result_array();

		// print_r($get_detail_spk);
		// exit;
		$ArrGroup = [];
		$ArrAktualResin = [];
		$ArrAktualPlus = [];
		$ArrAktualAdd = [];
		$ArrUpdate = [];

		$ArrDeatil = [];
		$ArrDeatilAdj = [];
		$ArrUpdateRequest = [];
		$nomor = 0;
		$SUM_MAT = 0;
		foreach ($get_detail_spk as $key => $value) {
			foreach ($ArrLooping as $valueX) {
				if (!empty($data[$valueX])) {
					if ($valueX == 'detail_liner') {
						$DETAIL_NAME = 'LINER THIKNESS / CB';
					}
					if ($valueX == 'detail_joint') {
						$DETAIL_NAME = 'RESIN AND ADD';
					}
					if ($valueX == 'detail_strn1') {
						$DETAIL_NAME = 'STRUKTUR NECK 1';
					}
					if ($valueX == 'detail_strn2') {
						$DETAIL_NAME = 'STRUKTUR NECK 2';
					}
					if ($valueX == 'detail_str') {
						$DETAIL_NAME = 'STRUKTUR THICKNESS';
					}
					if ($valueX == 'detail_ext') {
						$DETAIL_NAME = 'EXTERNAL LAYER THICKNESS';
					}
					if ($valueX == 'detail_topcoat') {
						$DETAIL_NAME = 'TOPCOAT';
					}
					if ($valueX == 'resin_and_add') {
						$DETAIL_NAME = 'RESIN AND ADD';
					}
					$detailX = $data[$valueX];
					// print_r($detailX);
					if ($value['id_product'] != 'deadstok') {
						$get_produksi 	= $this->db->limit(1)->select('id, id_category')->get_where('production_detail', array('id_milik' => $value['id_milik'], 'id_produksi' => 'PRO-' . $value['no_ipp'], 'kode_spk' => $value['kode_spk']))->result();
						//,'print_merge_date'=>$dateCreated
						if (empty($get_produksi)) {
							$Arr_Kembali	= array(
								'pesan'		=> 'Error data proccess, please contact administrator !!! ErrorCode: id_ml:' . $value['id_milik'] . '&spk:' . $value['kode_spk'] . '&tm:' . $dateCreated,
								'status'	=> 2
							);
							echo json_encode($Arr_Kembali);
							return false;
						}
					}

					foreach ($detailX as $key2 => $value2) {
						//RESIN
						$get_liner 		= $this->db->select('id, id_material, qty_order AS berat, key_gudang, check_qty_oke')->get_where('warehouse_adjustment_detail', array('kode_trans' => $kode_trans, 'keterangan' => $DETAIL_NAME))->result_array();
						// print_r($get_liner);
						// exit;
						if (!empty($get_liner)) {
							foreach ($get_liner as $key3 => $value3) {
								if ($value2['id_key'] == $value3['key_gudang']) {
									$nomor 		= $value2['id_key'];
									$ACTUAL_MAT = $value2['actual_type'];
									$QTY_INP	= $WHERE_KEY_QTY[$value['id']];
									$BERAT_UNIT = $value3['berat'] / $QTY_INP;
									$total_est 	= $BERAT_UNIT * $QTY_INP;
									$total_act  = 0;
									if ($value2['kebutuhan'] > 0) {
										$total_act 	= ($total_est / str_replace(',', '', $value2['kebutuhan'])) * str_replace(',', '', $value2['terpakai']);
									}
									$SUM_MAT 	+= $total_act;
									$unit_act 	= $total_act / $QTY_INP;
									$PERSEN = str_replace(',', '', $value2['persen']);
									//UPDATE FLAG SPK 2
									$ArrUpdate[$key . $key2 . $key3 . $nomor]['id'] 			= $value['id'];
									$ArrUpdate[$key . $key2 . $key3 . $nomor]['spk2'] 		= 'Y';
									$ArrUpdate[$key . $key2 . $key3 . $nomor]['spk2_by'] 		= $username;
									$ArrUpdate[$key . $key2 . $key3 . $nomor]['spk2_date']	= $datetime;
									// $ArrUpdate[$key.$key2.$key3.$nomor]['gudang2']	= $id_gudang;

									//ARRAY STOCK
									$ArrUpdateStock[$nomor]['id'] 	= $ACTUAL_MAT;
									$ArrUpdateStock[$nomor]['qty'] 	= $total_act;
									//UPDATE ADJUSTMENT DETAIL
									$ArrDeatil[$key . $key2 . $key3 . $nomor]['id'] 			    = $value3['id'];
									$ArrDeatil[$key . $key2 . $key3 . $nomor]['id_material'] 		= $ACTUAL_MAT;
									$ArrDeatil[$key . $key2 . $key3 . $nomor]['check_qty_oke'] 	= $total_act + $value3['check_qty_oke'];
									$ArrDeatil[$key . $key2 . $key3 . $nomor]['check_qty_rusak']	= $BERAT_UNIT;
									$ArrDeatil[$key . $key2 . $key3 . $nomor]['check_keterangan']	= $DETAIL_NAME;
									$ArrDeatil[$key . $key2 . $key3 . $nomor]['update_by'] 		= $username;
									$ArrDeatil[$key . $key2 . $key3 . $nomor]['update_date'] 		= $datetime;
									//INSERT ADJUSTMENT CHECK
									$ArrDeatilAdj[$key . $key2 . $key3 . $nomor]['id_detail'] 	= $value3['id'];
									$ArrDeatilAdj[$key . $key2 . $key3 . $nomor]['kode_trans'] 	= $kode_trans;
									$ArrDeatilAdj[$key . $key2 . $key3 . $nomor]['no_ipp'] 		= $no_request;
									$ArrDeatilAdj[$key . $key2 . $key3 . $nomor]['id_material'] 	= $ACTUAL_MAT;
									$ArrDeatilAdj[$key . $key2 . $key3 . $nomor]['nm_material'] 	= $GET_MATERIAL[$ACTUAL_MAT]['nm_material'];
									$ArrDeatilAdj[$key . $key2 . $key3 . $nomor]['id_category'] 	= $GET_MATERIAL[$ACTUAL_MAT]['id_category'];
									$ArrDeatilAdj[$key . $key2 . $key3 . $nomor]['nm_category'] 	= $GET_MATERIAL[$ACTUAL_MAT]['nm_category'];
									$ArrDeatilAdj[$key . $key2 . $key3 . $nomor]['qty_order'] 	= $value3['berat'];
									$ArrDeatilAdj[$key . $key2 . $key3 . $nomor]['qty_oke'] 		= $total_act;
									$ArrDeatilAdj[$key . $key2 . $key3 . $nomor]['qty_rusak'] 	= 0;
									$ArrDeatilAdj[$key . $key2 . $key3 . $nomor]['expired_date'] 	= NULL;
									$ArrDeatilAdj[$key . $key2 . $key3 . $nomor]['keterangan'] 	= $DETAIL_NAME;
									$ArrDeatilAdj[$key . $key2 . $key3 . $nomor]['update_by'] 	= $username;
									$ArrDeatilAdj[$key . $key2 . $key3 . $nomor]['update_date'] 	= $datetime;

									//UPDATE REQUEST
									$ArrUpdateRequest[$key . $key2 . $key3 . $nomor]['id_key'] 	= $value3['key_gudang'];
									$ArrUpdateRequest[$key . $key2 . $key3 . $nomor]['aktual'] 	= $total_act;
								}
							}
						}
					}
				}
			}
		}

		//grouping sum
		$temp = [];
		$tempx = [];
		$grouping_temp = [];
		foreach ($ArrUpdateStock as $value) {
			if ($value['qty'] > 0) {
				if (!array_key_exists($value['id'], $temp)) {
					$temp[$value['id']] = 0;
				}
				$temp[$value['id']] += $value['qty'];
			}
		}

		//Mengurangi Booking
		$getDetailSPK 	= $this->db->get_where('production_spk', array('kode_spk' => $kode_spk))->result();
		$no_ipp 		= (!empty($getDetailSPK[0]->no_ipp)) ? $getDetailSPK[0]->no_ipp : 0;

		$id_gudang_booking = 2;
		$GETS_STOCK = get_warehouseStockAllMaterial();
		$CHECK_BOOK = get_CheckBooking($no_ipp);
		$ArrUpdate 		= [];
		$ArrUpdateHist 	= [];
		// print_r($temp);
		if ($CHECK_BOOK === TRUE and $no_ipp != 0) {
			// echo 'Masuk';
			foreach ($temp as $material => $qty) {
				$KEY 		= $material . '-' . $id_gudang_booking;
				$booking 	= (!empty($GETS_STOCK[$KEY]['booking'])) ? $GETS_STOCK[$KEY]['booking'] : 0;
				$stock 		= (!empty($GETS_STOCK[$KEY]['stock'])) ? $GETS_STOCK[$KEY]['stock'] : 0;
				$rusak 		= (!empty($GETS_STOCK[$KEY]['rusak'])) ? $GETS_STOCK[$KEY]['rusak'] : 0;
				$id_stock 	= (!empty($GETS_STOCK[$KEY]['id'])) ? $GETS_STOCK[$KEY]['id'] : null;
				$idmaterial 	= (!empty($GETS_STOCK[$KEY]['idmaterial'])) ? $GETS_STOCK[$KEY]['idmaterial'] : null;
				$nm_material 	= (!empty($GETS_STOCK[$KEY]['nm_material'])) ? $GETS_STOCK[$KEY]['nm_material'] : null;
				$id_category 	= (!empty($GETS_STOCK[$KEY]['id_category'])) ? $GETS_STOCK[$KEY]['id_category'] : null;
				$nm_category 	= (!empty($GETS_STOCK[$KEY]['nm_category'])) ? $GETS_STOCK[$KEY]['nm_category'] : null;
				// echo 'ID:'.$id_stock;
				if (!empty($id_stock)) {
					$ArrUpdate[$material]['id'] = $id_stock;
					$ArrUpdate[$material]['qty_booking'] = $booking - $qty;

					$ArrUpdateHist[$material]['id_material'] 	= $material;
					$ArrUpdateHist[$material]['idmaterial'] 	= $idmaterial;
					$ArrUpdateHist[$material]['nm_material'] 	= $nm_material;
					$ArrUpdateHist[$material]['id_category'] 	= $id_category;
					$ArrUpdateHist[$material]['nm_category'] 	= $nm_category;
					$ArrUpdateHist[$material]['id_gudang'] 		= $id_gudang_booking;
					$ArrUpdateHist[$material]['kd_gudang'] 		= get_name('warehouse', 'kd_gudang', 'id', $id_gudang_booking);
					$ArrUpdateHist[$material]['id_gudang_dari'] = NULL;
					$ArrUpdateHist[$material]['kd_gudang_dari'] = 'BOOKING';
					$ArrUpdateHist[$material]['id_gudang_ke'] 	= NULL;
					$ArrUpdateHist[$material]['kd_gudang_ke'] 	= 'PENGURANG';
					$ArrUpdateHist[$material]['qty_stock_awal'] 	= $stock;
					$ArrUpdateHist[$material]['qty_stock_akhir'] 	= $stock;
					$ArrUpdateHist[$material]['qty_booking_awal'] 	= $booking;
					$ArrUpdateHist[$material]['qty_booking_akhir'] 	= $booking - $qty;
					$ArrUpdateHist[$material]['qty_rusak_awal'] 	= $rusak;
					$ArrUpdateHist[$material]['qty_rusak_akhir'] 	= $rusak;
					$ArrUpdateHist[$material]['no_ipp'] 			= $no_ipp;
					$ArrUpdateHist[$material]['jumlah_mat'] 		= $qty;
					$ArrUpdateHist[$material]['ket'] 				= 'pengurangan booking ' . $kode_trans . ' close';
					$ArrUpdateHist[$material]['update_by'] 			= $username;
					$ArrUpdateHist[$material]['update_date'] 		= $datetime;
				}
			}
		}

		// print_r($ArrUpdate);
		// print_r($ArrUpdateHist);
		// exit;

		//ENd Mengurangi Booking

		foreach ($ArrUpdateStock as $value) {
			if (!array_key_exists($value['id'], $tempx)) {
				$tempx[$value['id']]['good'] = 0;
			}
			$tempx[$value['id']]['good'] += $value['qty'];

			$grouping_temp[$value['id']]['id'] 			= $value['id'];
			$grouping_temp[$value['id']]['qty_good'] 	= $tempx[$value['id']]['good'];
		}

		move_warehouse($ArrUpdateStock, $id_gudang, $id_gudang_wip, $kode_trans);

		//UPDATE NOMOR SURAT JALAN
		$monthYear 		= date('/m/Y');
		$kode_gudang 	= get_name('warehouse', 'kode', 'id', $id_gudang);

		$getDetAjust 	= $this->db->get_where('warehouse_adjustment', array('kode_trans' => $kode_trans))->result();

		$qIPP			= "SELECT MAX(no_surat_jalan) as maxP FROM warehouse_adjustment WHERE no_surat_jalan LIKE '%/IA" . $kode_gudang . $monthYear . "' ";
		$numrowIPP		= $this->db->query($qIPP)->num_rows();
		$resultIPP		= $this->db->query($qIPP)->result_array();
		$angkaUrut2		= $resultIPP[0]['maxP'];
		$urutan2		= (int)substr($angkaUrut2, 0, 3);
		$urutan2++;
		$urut2			= sprintf('%03s', $urutan2);
		$no_surat_jalan	= $urut2 . "/IA" . $kode_gudang . $monthYear;

		$ArrUpdateHeadAjudtment = array(
			'checked' => 'Y',
			'jumlah_mat_check' => $getDetAjust[0]->jumlah_mat_check + $SUM_MAT,
			'no_surat_jalan' => $no_surat_jalan,
			'file_eng_change' => $file_name,
			'checked_by' => $username,
			'checked_date' => $datetime
		);

		$UpdateRealFlag = array(
			'upload_real2' => "Y",
			'upload_by2' =>  $username,
			'upload_date2' => $datetime
		);

		$UpdatePrintHeader = array(
			'aktual_by' =>  $username,
			'aktual_date' => $datetime
		);

		$this->db->trans_start();

		if (!empty($grouping_temp)) {
			insert_jurnal($grouping_temp, $id_gudang, $id_gudang_wip, $kode_trans, 'transfer subgudang - produksi', 'pengurangan subgudang', 'penambahan gudang produksi');
		}

		$this->db->where('kode_trans', $kode_trans);
		$this->db->update('warehouse_adjustment', $ArrUpdateHeadAjudtment);

		$this->db->update_batch('warehouse_adjustment_detail', $ArrDeatil, 'id');
		$this->db->insert_batch('warehouse_adjustment_check', $ArrDeatilAdj);

		if (!empty($ArrRequest)) {
			$this->db->insert_batch('production_spk_add', $ArrRequest);
		}

		if (!empty($ArrUpdate)) {
			$this->db->update_batch('warehouse_stock', $ArrUpdate, 'id');
			$this->db->insert_batch('warehouse_history', $ArrUpdateHist);
		}

		if (!empty($ArrEditAdd)) {
			$this->db->update_batch('production_spk_add', $ArrEditAdd, 'id');
		}

		if (!empty($ArrRequestHist)) {
			$this->db->insert_batch('production_spk_add_hist', $ArrRequestHist);
		}

		if (!empty($ArrUpdateRequest)) {
			$this->db->where('kode_uniq', $no_request);
			$this->db->update_batch('print_detail', $ArrUpdateRequest, 'id_key');
		}
		$this->db->where('kode_uniq', $no_request);
		$this->db->update('print_header', $UpdatePrintHeader);
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Kembali	= array(
				'pesan'		=> 'Process Failed. Please try again later ...',
				'status'	=> 2,
				'kode_spk'	=> $id
			);
		} else {
			$this->db->trans_commit();
			$Arr_Kembali	= array(
				'pesan'		=> 'Process Success. Thank you & have a nice day ...',
				'status'	=> 1,
				'kode_spk'	=> $id
			);

			history('Close request produksi ' . $kode_spk . '/' . $kode_trans . '/' . $id);
		}
		echo json_encode($Arr_Kembali);
	}

	//==========================================================================================================================
	//===============================================ADJUSTMENT MATERIAL========================================================
	//==========================================================================================================================

	public function adjustment()
	{
		$this->adjustment_material_model->adjustment();
	}

	public function server_side_adjustment()
	{
		$this->adjustment_material_model->get_data_json_adjustment();
	}

	public function add_adjustment()
	{
		$this->adjustment_material_model->add_adjustment();
	}

	public function excel_adjustment()
	{
		$this->adjustment_material_model->excel_adjustment();
	}


	//==========================================================================================================================
	//==================================================END WAREHOUSE===========================================================
	//==========================================================================================================================


	public function list_ipp()
	{
		$adjust		= $this->input->post('adjust');
		$wherField 	= ($adjust == 'IN') ? 'status' : 'sts_close';
		$wherTable 	= ($adjust == 'IN') ? 'tran_material_po_header' : 'warehouse_planning_header';
		$wherIsi 	= ($adjust == 'IN') ? 'WAITING IN' : 'N';
		$wherF 		= ($adjust == 'IN') ? 'no_po' : 'no_ipp';
		$wherSelct 	= ($adjust == 'IN') ? 'PO Number' : 'IPP';

		$tambahan 	= ($adjust == 'IN') ? "OR " . $wherField . " = 'IN PARSIAL'" : '';

		$query	 	= "SELECT " . $wherF . " FROM " . $wherTable . " WHERE (" . $wherField . " = '" . $wherIsi . "' " . $tambahan . ") ORDER BY " . $wherF . " ASC";

		$Q_result	= $this->db->query($query)->result();
		$Opt 		= (!empty($Q_result)) ? 'Select An ' . $wherSelct : 'List Empty';
		$option 	= "<option value='0'>" . $Opt . "</option>";
		foreach ($Q_result as $row) {
			$option .= "<option value='" . $row->$wherF . "'>" . $row->$wherF . "</option>";
		}
		echo json_encode(array(
			'option' => $option
		));
	}

	public function list_warehouse()
	{
		$adjust	= $this->input->post('adjust');

		$wherField = " sts_2 = 'N' ";
		if ($adjust == 'IN') {
			$wherField = " status = 'Y' AND urut2 = '1' ";
		}
		if ($adjust == 'MOVE') {
			$wherField = " status = 'Y' ";
		}

		$Opt 	= ($adjust == 'IN' or $adjust == 'MOVE') ? 'Select An Warehouse' : 'List Empty';


		$query	 	= "SELECT id, kd_gudang, nm_gudang FROM warehouse WHERE " . $wherField . " ORDER BY urut ASC";
		$Q_result	= $this->db->query($query)->result();
		$option = "<option value='0'>" . $Opt . "</option>";
		foreach ($Q_result as $row) {
			$option .= "<option value='" . $row->id . "'>" . $row->nm_gudang . "</option>";
		}
		echo json_encode(array(
			'option' => $option
		));
	}

	public function list_warehouse_ipp()
	{
		$no_ipp		= $this->input->post('no_ipp');
		$tanda = substr($no_ipp, 0, 2);
		if ($no_ipp <> '0') {
			$queryIpp	= "SELECT a.kd_gudang_ke, b.urut2 FROM warehouse_adjustment a LEFT JOIN warehouse b ON a.kd_gudang_ke=b.kd_gudang WHERE a.no_ipp = '" . $no_ipp . "' AND a.kd_gudang_dari <> 'PURCHASE' AND b.urut2 >= 2 ORDER BY a.created_date DESC LIMIT 1";
			$restIpp	= $this->db->query($queryIpp)->result();

			$urutX		= (!empty($restIpp[0]->urut2)) ? $restIpp[0]->urut2 : 2;

			$Opt 		= (!empty($restIpp[0]->urut2)) ? 'Select An Warehouse' : 'Select An Warehouse';

			if ($tanda == 'PO') {
				$query	 	= "SELECT id, kd_gudang, nm_gudang FROM warehouse WHERE `status` = 'Y' AND urut2 = '1' ORDER BY urut ASC";
			}
			if ($tanda <> 'PO') {
				$query	 	= "SELECT id, kd_gudang, nm_gudang FROM warehouse WHERE urut2 = " . $urutX . " ORDER BY urut ASC";
			}
			// echo $query;
			$Q_result	= $this->db->query($query)->result();
		}
		if ($no_ipp == '0') {
			$Opt = 'List Empty';
		}
		$option = "<option value='0'>" . $Opt . "</option>";
		if ($no_ipp <> '0') {
			foreach ($Q_result as $row) {
				$option .= "<option value='" . $row->id . "'>" . $row->nm_gudang . "</option>";
			}
		}
		echo json_encode(array(
			'option' => $option
		));
	}

	public function list_gudang_ke()
	{
		$gudang		= $this->input->post('gudang');
		$tandax		= $this->input->post('tandax');

		if ($gudang <> '0') {
			$queryIpp	= "SELECT b.urut2 FROM  warehouse b WHERE b.id = '" . $gudang . "' LIMIT 1";
			$restIpp	= $this->db->query($queryIpp)->result();

			if ($tandax == 'MOVE') {
				$whLef = " id != '" . $gudang . "' AND status = 'Y' ";
			} else {
				$whLef = " urut2 > " . $restIpp[0]->urut2;
			}

			$query	 	= "SELECT id, kd_gudang, nm_gudang FROM warehouse WHERE " . $whLef . " ORDER BY urut ASC";
			// echo $query;
			$Q_result	= $this->db->query($query)->result();

			$Opt 		= (!empty($Q_result)) ? 'Select An Warehouse' : 'List Empty - Not Found';
		}
		if ($gudang == '0') {
			$Opt = 'List Empty';
		}

		$option = "<option value='0'>" . $Opt . "</option>";
		if ($gudang <> '0') {
			foreach ($Q_result as $row) {
				$option .= "<option value='" . $row->id . "'>" . $row->nm_gudang . "</option>";
			}
		}
		echo json_encode(array(
			'option' => $option
		));
	}

	public function list_material()
	{

		$query	 	= "SELECT id_material, nm_material FROM raw_materials WHERE `delete` = 'N' ORDER BY nm_material ASC";
		$Q_result	= $this->db->query($query)->result();
		$option = "<option value='0'>Select Material</option>";
		foreach ($Q_result as $row) {
			$option .= "<option value='" . $row->id_material . "'>" . $row->nm_material . "</option>";
		}
		echo json_encode(array(
			'option' => $option
		));
	}

	public function list_material_stock()
	{
		$gudang		= $this->input->post('gudang');
		$query	 	= "SELECT id_material, nm_material FROM warehouse_stock WHERE id_gudang = '" . $gudang . "' ORDER BY nm_material ASC";
		$Q_result	= $this->db->query($query)->result();
		$option = "<option value='0'>Select Material</option>";
		foreach ($Q_result as $row) {
			$option .= "<option value='" . $row->id_material . "'>" . strtoupper($row->nm_material) . "</option>";
		}
		echo json_encode(array(
			'option' => $option
		));
	}

	public function list_expired_date()
	{
		$id_gudang_ke		= $this->input->post('id_gudang_ke');
		$id_material		= $this->input->post('id_material');

		$query	 	= "SELECT expired FROM warehouse_stock_expired WHERE id_gudang = '" . $id_gudang_ke . "' AND id_material = '" . $id_material . "' GROUP BY expired ORDER BY expired ASC";
		$Q_result	= $this->db->query($query)->result();
		// echo $query;
		if (!empty($Q_result)) {
			$option = "<option value='0'>Select Expired</option>";
			foreach ($Q_result as $row) {
				if ($row->expired <> NULL and $row->expired <> '0000-00-00') {
					$option .= "<option value='" . $row->expired . "'>" . date('d-M-Y', strtotime($row->expired)) . "</option>";
				}
				// if($row->expired == NULL OR $row->expired == '0000-00-00'){
				// $option .= "<option value='0'>Expired Empty</option>";
				// }
			}
		}

		if (empty($Q_result)) {
			$option = "<option value='0'>Expired Not Found</option>";
		}
		echo json_encode(array(
			'option' => $option
		));
	}

	public function list_warehouse_dest()
	{
		$gudang		= $this->input->post('gudang');
		$tandax		= $this->input->post('tandax');

		if ($gudang <> '0') {
			$queryIpp	= "SELECT b.urut2 FROM  warehouse b WHERE b.kd_gudang = '" . $gudang . "' LIMIT 1";
			$restIpp	= $this->db->query($queryIpp)->result();

			if ($tandax == 'MOVE') {
				$whLef = " kd_gudang != '" . $gudang . "' AND status = 'Y' ";
			} else {
				$whLef = " urut2 > " . $restIpp[0]->urut2;
			}

			$query	 	= "SELECT id, kd_gudang, nm_gudang FROM warehouse WHERE " . $whLef . " ORDER BY urut ASC";
			// echo $query;
			$Q_result	= $this->db->query($query)->result();

			$Opt 		= (!empty($Q_result)) ? 'Select An Warehouse' : 'List Empty - Not Found';
		}
		if ($gudang == '0') {
			$Opt = 'List Empty';
		}

		$option = "<option value='0'>" . $Opt . "</option>";
		if ($gudang <> '0') {
			foreach ($Q_result as $row) {
				$option .= "<option value='" . $row->id . "'>" . $row->nm_gudang . "</option>";
			}
		}
		echo json_encode(array(
			'option' => $option
		));
	}

	public function testing_booking()
	{
		$data 			= $this->input->post();
		$data_session	= $this->session->userdata;
		$id_bq			= "IPP20761L";
		// echo $id_bq;
		// exit;
		$sqlWhDetail	= "	SELECT
								a.*,
								b.id AS id2,
								b.qty_booking,
								b.kd_gudang,
								b.id_gudang,
								b.idmaterial,
								b.nm_material,
								b.id_category,
								b.nm_category,
								b.qty_stock
							FROM
								warehouse_planning_detail a
								LEFT JOIN warehouse_stock b
									ON a.id_material=b.id_material
							WHERE
								a.no_ipp = '" . $id_bq . "'
								AND a.id_material <> 'MTL-1903000'
								AND (b.id_gudang = '1' OR b.id_gudang = '2')
							";
		$restWhDetail	= $this->db->query($sqlWhDetail)->result_array();

		$ArrDeatil		 = array();
		$ArrHist		 = array();
		foreach ($restWhDetail as $val => $valx) {
			$ArrDeatil[$val]['id'] 			= $valx['id2'];
			$ArrDeatil[$val]['id_material'] = $valx['id_material'];
			$ArrDeatil[$val]['id_gudang'] 	= $valx['id_gudang'];
			$ArrDeatil[$val]['qty_booking'] = $valx['qty_booking'] + $valx['use_stock'];
		}

		foreach ($restWhDetail as $val => $valx) {
			$ArrHist[$val]['id_material'] 		= $valx['id_material'];
			$ArrHist[$val]['idmaterial'] 		= $valx['idmaterial'];
			$ArrHist[$val]['nm_material'] 		= $valx['nm_material'];
			$ArrHist[$val]['id_category'] 		= $valx['id_category'];
			$ArrHist[$val]['nm_category'] 		= $valx['nm_category'];
			$ArrHist[$val]['id_gudang_dari'] 	= $valx['id_gudang'];
			$ArrHist[$val]['kd_gudang_dari'] 	= $valx['kd_gudang'];
			$ArrHist[$val]['kd_gudang_ke'] 		= 'BOOKING';
			$ArrHist[$val]['qty_stock_awal'] 	= $valx['qty_stock'];
			$ArrHist[$val]['qty_stock_akhir'] 	= $valx['qty_stock'];
			$ArrHist[$val]['qty_booking_awal'] 	= $valx['qty_booking'];
			$ArrHist[$val]['qty_booking_akhir'] = $valx['qty_booking'] + $valx['use_stock'];
			$ArrHist[$val]['no_ipp'] 			= $id_bq;
			$ArrHist[$val]['jumlah_mat'] 		= $valx['use_stock'];
			$ArrHist[$val]['update_by'] 		= $data_session['ORI_User']['username'];
			$ArrHist[$val]['update_date'] 		= date('Y-m-d H:i:s');
		}

		$ArrHeader = array(
			'sts_booking' => 'Y',
			'book_by' => $data_session['ORI_User']['username'],
			'book_date' => date('Y-m-d H:i:s')
		);

		echo "<pre>";
		print_r($ArrDeatil);
		print_r($ArrHist);
		print_r($ArrHeader);
		exit;
	}

	public function ExcelGudang()
	{
		//membuat objek PHPExcel
		set_time_limit(0);
		ini_set('memory_limit', '1024M');
		$gudang			= $this->uri->segment(3);
		$date_filter	= $this->uri->segment(4);

		$this->load->library("PHPExcel");
		// $this->load->library("PHPExcel/Writer/Excel2007");
		$objPHPExcel	= new PHPExcel();

		$whiteCenterBold    = whiteCenterBold();
		$whiteRightBold    	= whiteRightBold();
		$whiteCenter    	= whiteCenter();
		$mainTitle    		= mainTitle();
		$tableHeader    	= tableHeader();
		$tableBodyCenter    = tableBodyCenter();
		$tableBodyLeft    	= tableBodyLeft();
		$tableBodyRight    	= tableBodyRight();

		$Arr_Bulan	= array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
		$sheet 		= $objPHPExcel->getActiveSheet();

		$table = "warehouse_stock";
		$where_gudang = '';
		$where_date = '';
		$field_add = '';
		if (!empty($gudang)) {
			$where_gudang = " AND a.id_gudang = '" . $gudang . "' ";
		}

		if (!empty($date_filter)) {
			$where_gudang = " AND a.id_gudang = '" . $gudang . "' ";
			$where_date = " AND DATE(a.hist_date) = '" . $date_filter . "' ";
			$table = "warehouse_stock_per_day";
			$field_add = "a.costbook, a.total_value,";
		}
		$sql = "
			SELECT
				(@row:=@row+1) AS nomor,
				c.idmaterial,
				c.id_material,
				c.nm_material,
				c.nm_category,
				a.qty_stock,
				a.qty_booking,
				a.qty_rusak,
				a.id_gudang,
				" . $field_add . "
				b.nm_gudang
			FROM
				" . $table . " a 
				LEFT JOIN warehouse b ON a.kd_gudang=b.kd_gudang
				LEFT JOIN raw_materials c ON a.id_material = c.id_material,
				(SELECT @row:=0) r
		    WHERE 1=1 AND a.id_material <> 'MTL-1903000' " . $where_gudang . " " . $where_date . "
		";
		$restDetail1	= $this->db->query($sql)->result_array();
		$get_category = $this->db->select('category')->get_where('warehouse', array('id' => $gudang))->result();
		$nm_gudang = strtoupper(get_name('warehouse', 'nm_gudang', 'id', $gudang));

		$tanggal_update = (!empty($date_filter)) ? " (" . date('d F Y', strtotime($date_filter)) . ")" : " (" . date('d F Y') . ")";
		$tanggal_update2 = (!empty($date_filter)) ? date('Y-m-d', strtotime($date_filter)) : date('Y-m-d');

		$Row		= 1;
		$NewRow		= $Row + 1;
		$Col_Akhir	= $Cols	= getColsChar(7);
		$sheet->setCellValue('A' . $Row, 'STOCK - ' . $nm_gudang . $tanggal_update);
		$sheet->getStyle('A' . $Row . ':' . $Col_Akhir . $NewRow)->applyFromArray($mainTitle);
		$sheet->mergeCells('A' . $Row . ':' . $Col_Akhir . $NewRow);

		$NewRow	= $NewRow + 2;
		$NextRow = $NewRow;

		$sheet->setCellValue('A' . $NewRow, '#');
		$sheet->getStyle('A' . $NewRow . ':A' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('A' . $NewRow . ':A' . $NextRow);
		$sheet->getColumnDimension('A')->setWidth(10);

		$sheet->setCellValue('B' . $NewRow, 'ID PROGRAM');
		$sheet->getStyle('B' . $NewRow . ':B' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('B' . $NewRow . ':B' . $NextRow);
		$sheet->getColumnDimension('B')->setWidth(20);

		$sheet->setCellValue('C' . $NewRow, 'ID MATERIAL');
		$sheet->getStyle('C' . $NewRow . ':C' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('C' . $NewRow . ':C' . $NextRow);
		$sheet->getColumnDimension('C')->setWidth(20);

		$sheet->setCellValue('D' . $NewRow, 'MATERIAL');
		$sheet->getStyle('D' . $NewRow . ':D' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('D' . $NewRow . ':D' . $NextRow);
		$sheet->getColumnDimension('D')->setAutoSize(true);

		$sheet->setCellValue('E' . $NewRow, 'CATEGORY');
		$sheet->getStyle('E' . $NewRow . ':E' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('D' . $NewRow . ':E' . $NextRow);
		$sheet->getColumnDimension('E')->setAutoSize(true);

		$sheet->setCellValue('F' . $NewRow, 'WAREHOUSE');
		$sheet->getStyle('F' . $NewRow . ':F' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('F' . $NewRow . ':F' . $NextRow);
		$sheet->getColumnDimension('F')->setWidth(10);

		$sheet->setCellValue('G' . $NewRow, 'STOCK');
		$sheet->getStyle('G' . $NewRow . ':G' . $NextRow)->applyFromArray($tableHeader);
		$sheet->mergeCells('G' . $NewRow . ':G' . $NextRow);
		$sheet->getColumnDimension('G')->setWidth(10);

		if ($get_category[0]->category != 'produksi') {
			$sheet->setCellValue('H' . $NewRow, 'BOOKING');
			$sheet->getStyle('H' . $NewRow . ':H' . $NextRow)->applyFromArray($tableHeader);
			$sheet->mergeCells('H' . $NewRow . ':H' . $NextRow);
			$sheet->getColumnDimension('H')->setWidth(20);

			$sheet->setCellValue('I' . $NewRow, 'AVAILABLE');
			$sheet->getStyle('I' . $NewRow . ':I' . $NextRow)->applyFromArray($tableHeader);
			$sheet->mergeCells('I' . $NewRow . ':I' . $NextRow);
			$sheet->getColumnDimension('I')->setWidth(20);

			if ($get_category[0]->category == 'pusat') {
				$sheet->setCellValue('J' . $NewRow, 'DEMAGED');
				$sheet->getStyle('J' . $NewRow . ':J' . $NextRow)->applyFromArray($tableHeader);
				$sheet->mergeCells('J' . $NewRow . ':J' . $NextRow);
				$sheet->getColumnDimension('J')->setWidth(20);
			}
		}


		// echo $qDetail1; exit;
		$GET_COSTBOOK = get_costbook();

		if ($restDetail1) {
			$awal_row	= $NextRow;
			$no = 0;
			foreach ($restDetail1 as $key => $row_Cek) {
				$no++;
				$awal_row++;
				$awal_col	= 0;

				if (empty($date_filter)) {
					if (!empty($GET_COSTBOOK[$row_Cek['id_material']])) {
						$COSTBOOK = $GET_COSTBOOK[$row_Cek['id_material']];
					} else {
						$COSTBOOK = 0;
					}
				} else {
					$COSTBOOK = $row_Cek['costbook'];
				}
				$COSTBOOK_TOTAL = $COSTBOOK * $row_Cek['qty_stock'];
				$awal_col++;
				$detail_name	= $no;
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $detail_name);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$id_material	= $row_Cek['id_material'];
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $id_material);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$idmaterial	= $row_Cek['idmaterial'];
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $idmaterial);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$nm_material	= strtoupper($row_Cek['nm_material']);
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $nm_material);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$nm_category	= $row_Cek['nm_category'];
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $nm_category);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$nm_gudang	= $row_Cek['nm_gudang'];
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $nm_gudang);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

				$awal_col++;
				$qty_stock	= $row_Cek['qty_stock'];
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $qty_stock);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

				if ($get_category[0]->category != 'produksi') {
					$awal_col++;
					$qty_booking	= $row_Cek['qty_booking'];
					$Cols			= getColsChar($awal_col);
					$sheet->setCellValue($Cols . $awal_row, $qty_booking);
					$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

					$awal_col++;
					$qty_avl	= $row_Cek['qty_stock'] - $row_Cek['qty_booking'];
					$Cols			= getColsChar($awal_col);
					$sheet->setCellValue($Cols . $awal_row, $qty_avl);
					$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

					if ($get_category[0]->category == 'pusat') {
						$awal_col++;
						$qty_rusak	= $row_Cek['qty_rusak'];
						$Cols			= getColsChar($awal_col);
						$sheet->setCellValue($Cols . $awal_row, $qty_rusak);
						$sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);
					}
				}
			}
		}

		$LABEL_TITLE = strtolower('STOCK' . '-' . $tanggal_update2);
		$sheet->setTitle('Stock');
		//mulai menyimpan excel format xlsx, kalau ingin xls ganti Excel2007 menjadi Excel5
		$objWriter		= PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		ob_end_clean();
		//sesuaikan headernya
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		//		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Type: vnd.ms-excel');
		//ubah nama file saat diunduh
		header('Content-Disposition: attachment;filename="' . $LABEL_TITLE . '.xls"');
		//unduh file
		$objWriter->save("php://output");
	}

	public function download_excel($id_bq = null, $type = null)
	{
		//membuat objek PHPExcel
		set_time_limit(0);
		ini_set('memory_limit', '1024M');

		$this->load->library("PHPExcel");
		// $this->load->library("PHPExcel/Writer/Excel2007");
		$objPHPExcel	= new PHPExcel();

		$style_header = array(
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('rgb' => '000000')
				)
			),
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'color' => array('rgb' => 'e0e0e0'),
			),
			'font' => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		);

		$style_header2 = array(
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'color' => array('rgb' => 'e0e0e0'),
			),
			'font' => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		);

		$styleArray = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
			),
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('rgb' => '000000')
				)
			)
		);
		$styleArray3 = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
			),
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('rgb' => '000000')
				)
			)
		);
		$styleArray4 = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT

			),
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('rgb' => '000000')
				)
			)
		);
		$styleArray1 = array(
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN
				)
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		);
		$styleArray2 = array(
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN
				)
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		);

		$Arr_Bulan	= array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
		$sheet 		= $objPHPExcel->getActiveSheet();


		$Row		= 1;
		$NewRow		= $Row + 1;
		$Col_Akhir	= $Cols	= getColsChar(5);
		$sheet->setCellValue('A' . $Row, 'MATERIAL PLANNING ' . str_replace('BQ-', '', $id_bq));
		$sheet->getStyle('A' . $Row . ':E' . $NewRow)->applyFromArray($style_header2);
		$sheet->mergeCells('A' . $Row . ':E' . $NewRow);

		$NewRow	= $NewRow + 2;
		$NextRow = $NewRow;

		$sheet->setCellValue('A' . $NewRow, '#');
		$sheet->getStyle('A' . $NewRow . ':A' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('A' . $NewRow . ':A' . $NextRow);
		$sheet->getColumnDimension('A')->setWidth(10);

		$sheet->setCellValue('B' . $NewRow, 'ID MATERIAL');
		$sheet->getStyle('B' . $NewRow . ':B' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('B' . $NewRow . ':B' . $NextRow);
		$sheet->getColumnDimension('B')->setWidth(20);

		$sheet->setCellValue('C' . $NewRow, 'MATERIAL');
		$sheet->getStyle('C' . $NewRow . ':C' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('C' . $NewRow . ':C' . $NextRow);
		$sheet->getColumnDimension('C')->setAutoSize(true);

		$sheet->setCellValue('D' . $NewRow, 'BERAT');
		$sheet->getStyle('D' . $NewRow . ':D' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('D' . $NewRow . ':D' . $NextRow);
		$sheet->getColumnDimension('D')->setAutoSize(true);

		$sheet->setCellValue('E' . $NewRow, 'UNIT');
		$sheet->getStyle('E' . $NewRow . ':E' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('E' . $NewRow . ':E' . $NextRow);
		$sheet->getColumnDimension('E')->setWidth(10);

		if ($type == 'pipe') {
			$query	= "	SELECT
							a.id_bq AS id_bq,
							a.id_material AS id_material,
							a.nm_material AS nm_material,
							round( sum( ( a.last_cost * b.qty ) ), 3 ) AS last_cost 
						FROM
							( estimasi_total a LEFT JOIN so_bf_detail_header b ON ( ( a.id_milik = b.id_milik ) ) ) 
						WHERE
							( a.id_material <> '0' )  
							AND a.id_bq='" . $id_bq . "'
						GROUP BY
							a.id_material,
							a.id_bq 
						ORDER BY
							a.nm_material";
			$result		= $this->db->query($query)->result_array();

			$non_frp		= $this->db->get_where('so_acc_and_mat', array('category <>' => 'mat', 'id_bq' => $id_bq))->result_array();
			$material		= $this->db->get_where('so_acc_and_mat', array('category' => 'mat', 'id_bq' => $id_bq))->result_array();
		} else {
			$query	= "		SELECT
								a.no_ipp AS id_bq,
								a.id_material AS id_material,
								b.nm_material AS nm_material,
								round( sum( ( a.berat ) ), 3 ) AS last_cost 
							FROM
								( planning_tanki_detail a LEFT JOIN raw_materials b ON ( ( a.id_material = b.id_material ) ) ) 
							WHERE
								( a.id_material <> '0' AND a.id_material <> 'MTL-1903000')  
								AND a.no_ipp='$id_bq'
								AND a.category='mat'
							GROUP BY
								a.id_material,
								a.no_ipp 
							ORDER BY
								b.nm_material";
			$result		= $this->db->query($query)->result_array();

			$sql_non_frp 	= "	SELECT
										a.id,
										a.id AS id_milik,
										a.no_ipp AS id_bq,
										c.id AS id_material,
										SUM(a.berat) AS qty,
										'tanki' AS category,
										'3' AS satuan,
										SUM(a.berat) AS berat,
										b.stock
									FROM
										planning_tanki_detail a
										LEFT JOIN accessories c ON a.id_material=c.id_acc_tanki AND c.category = 5
										LEFT JOIN warehouse_acc_stock b ON c.id = b.id_acc
									WHERE
										a.category = 'acc'
										AND a.no_ipp='$id_bq'
									GROUP BY a.id_material ";
			// echo $sql_non_frp;
			$non_frp		= $this->db->query($sql_non_frp)->result_array();
			$material		= array();
		}

		if ($result) {
			$awal_row	= $NextRow;
			$no = 0;
			foreach ($result as $key => $row_Cek) {
				$no++;
				$awal_row++;
				$awal_col	= 0;

				$awal_col++;
				$detail_name	= $no;
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $detail_name);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray);

				$awal_col++;
				$id_material	= $row_Cek['id_material'];
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $id_material);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray);

				$awal_col++;
				$nm_material	= $row_Cek['nm_material'];
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $nm_material);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray3);

				$awal_col++;
				$last_cost	= $row_Cek['last_cost'];
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $last_cost);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray4);

				$awal_col++;
				$unit	= 'KG';
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $unit);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray);
			}
		}

		if ($non_frp) {
			foreach ($non_frp as $key => $row_Cek) {
				$no++;
				$awal_row++;
				$awal_col	= 0;

				$qty = $row_Cek['qty'];
				$satuan = $row_Cek['satuan'];
				if ($row_Cek['category'] == 'plate') {
					$qty = $row_Cek['berat'];
					$satuan = '1';
				}

				$awal_col++;
				$detail_name	= $no;
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $detail_name);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray);

				$awal_col++;
				$id_material	= $row_Cek['id_material'];
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $id_material);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray);

				$awal_col++;
				$nm_material	= get_name_acc($row_Cek['id_material']);
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $nm_material);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray3);

				$awal_col++;
				$last_cost	= $qty;
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $last_cost);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray4);

				$awal_col++;
				$unit	= strtoupper(get_name('raw_pieces', 'kode_satuan', 'id_satuan', $satuan));
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $unit);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray);
			}
		}

		if ($material) {
			foreach ($material as $key => $row_Cek) {
				$no++;
				$awal_row++;
				$awal_col	= 0;

				$awal_col++;
				$detail_name	= $no;
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $detail_name);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray);

				$awal_col++;
				$id_material	= $row_Cek['id_material'];
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $id_material);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray);

				$awal_col++;
				$nm_material	= strtoupper(get_name('raw_materials', 'nm_material', 'id_material', $row_Cek['id_material']));
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $nm_material);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray3);

				$awal_col++;
				$last_cost	= $row_Cek['qty'];
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $last_cost);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray4);

				$awal_col++;
				$unit	= 'KG';
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $unit);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray);
			}
		}


		$sheet->setTitle(str_replace('BQ-', '', $id_bq));
		//mulai menyimpan excel format xlsx, kalau ingin xls ganti Excel2007 menjadi Excel5
		$objWriter		= PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		ob_end_clean();
		//sesuaikan headernya
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		//ubah nama file saat diunduh
		header('Content-Disposition: attachment;filename="Material Planning -  ' . str_replace('BQ-', '', $id_bq) . ' ' . date('YmdHis') . '.xls"');
		//unduh file
		$objWriter->save("php://output");
	}

	public function get_detail_spk()
	{
		$data 			= $this->input->post();
		$spk 			= $data['no_spk'];
		$category_mat 	= $data['category_mat'];
		$no_ipp 		= $data['no_ipp'];
		$get_detail 	= $this->db->get_where('production_detail', array('no_spk' => $spk))->result();
		if (empty($get_detail)) {
			$Arr_Kembali    = array(
				'pesan'        => 'SPK belum masuk produksi !!!',
				'status'    => 2
			);
			echo json_encode($Arr_Kembali);
			return false;
		}
		$id_milik 		= $get_detail[0]->id_milik;
		$qty_spk 		= $get_detail[0]->qty;
		$product_name 	= get_name('so_detail_header', 'id_category', 'id', $id_milik);
		$qty 			= $get_detail[0]->qty;
		$WHEREIN 		= ['TYP-0003', 'TYP-0004', 'TYP-0005', 'TYP-0006'];
		if ($category_mat != '0') {
			$WHEREIN 		= [$category_mat];
		}

		$get_material 	= $this->db->select('a.*, SUM(a.last_cost) AS total_req')->group_by('a.id_material')->order_by('nm_material', 'asc')->where_in('a.id_category', $WHEREIN)->get_where('so_component_detail a', array('a.id_milik' => $id_milik, 'a.id_material !=' => 'MTL-1903000'))->result_array();

		$HTML = '';
		if (!empty($get_material)) {
			foreach ($get_material as $key => $value) {
				$key++;
				$get_planning = $this->db->select('a.*')->from('planning_detail a')->where('a.no_ipp', $no_ipp)->where('a.id_material', $value['id_material'])->get()->result();
				$QTY_REQ 	= $value['total_req'] * $qty;
				if (!empty($get_planning)) {

					//SISA REQUEST
					$get_sisa_reqx 	= $this->db->get_where('planning_detail_spk', array('no_ipp' => $no_ipp, 'id_milik' => $id_milik, 'id_material' => $value['id_material']))->result();
					$jumlah_req 	= (!empty($get_sisa_reqx)) ? $get_sisa_reqx[0]->total_request : 0;
					$jumlah_aktual 	= (!empty($get_sisa_reqx)) ? $get_sisa_reqx[0]->total_aktual : 0;

					$QTY_REQUEST 	= $jumlah_req;

					$sisa_req 		= $QTY_REQ - $QTY_REQUEST;

					$color = 'text-green text-bold';
					$disabled = '';
					if ($sisa_req <= 0) {
						$color = 'text-red text-bold';
					}
					if ($sisa_req > 0) {
						$HTML .= "<tr>";
						$HTML .= "<td class='text-center'>" . $key . "
										<input type='hidden' name='detail2[999" . $key . "][id]' value='" . $get_planning[0]->id . "'>
										<input type='hidden' name='detail2[999" . $key . "][berat_est]' value='" . $QTY_REQ . "'>
										<input type='hidden' name='detail2[999" . $key . "][qty_sisa]' value='" . $sisa_req . "'>
										<input type='hidden' name='detail2[999" . $key . "][qty_total_req]' value='" . $QTY_REQUEST . "'>
									</td>";
						$HTML .= "<td>" . strtoupper($value['nm_material']) . "</td>";
						$HTML .= "<td class='text-right text-bold'>" . number_format($QTY_REQ, 3) . "</td>";
						$HTML .= "<td class='text-right " . $color . " sisaRequest'>" . number_format($sisa_req, 3) . "</td>";
						$HTML .= "<td class='text-right text-bold'>" . number_format($QTY_REQUEST, 3) . "</td>";
						$HTML .= "<td><input type='text' style='width:100%' name='detail2[999" . $key . "][sudah_request]' data-no='" . $key . "' class='form-control text-bold input-sm text-right autoNumeric requestBlock' placeholder='Request (kg)'></td>";
						$HTML .= "<td><input type='text' style='width:100%' name='detail2[999" . $key . "][ket_request]' data-no='" . $key . "' class='form-control input-sm text-left' placeholder='Keterangan'></td>";
						$HTML .= "</tr>";
					} else {
						$HTML .= "<tr>";
						$HTML .= "<td class='text-center'>" . $key . "</td>";
						$HTML .= "<td>" . strtoupper($value['nm_material']) . "</td>";
						$HTML .= "<td class='text-right text-bold'>" . number_format($QTY_REQ, 3) . "</td>";
						$HTML .= "<td class='text-right " . $color . "'>" . number_format($sisa_req, 3) . "</td>";
						$HTML .= "<td class='text-right text-bold'>" . number_format($QTY_REQUEST, 3) . "</td>";
						$HTML .= "<td colspan='2' class='text-red'><b>Request melebihi limit !!!</b></td>";
						$HTML .= "</tr>";
					}
				} else {
					$HTML .= "<tr>";
					$HTML .= "<td class='text-center'>" . $key . "</td>";
					$HTML .= "<td>" . strtoupper($value['nm_material']) . "</td>";
					$HTML .= "<td class='text-right text-bold'>" . number_format($QTY_REQ, 3) . "</td>";
					$HTML .= "<td class='text-center'>-</td>";
					$HTML .= "<td class='text-center'>-</td>";
					$HTML .= "<td colspan='2' class='text-red'><b>Buat request terlebih dahulu !!!</b></td>";
					$HTML .= "</tr>";
				}
			}
		} else {
			$HTML .= "<tr>";
			$HTML .= "<td colspan='7' class='text-red'><b>Material tidak ditemukan !!!</b></td>";
			$HTML .= "</tr>";
		}

		echo json_encode(array(
			'option' => $HTML,
			'id_milik' => $id_milik,
			'qty_spk' => $qty_spk,
			'product_name' => strtoupper($product_name)
		));
	}

	//new buat request
	public function process_buat_request($no_ipp)
	{
		$data_session	= $this->session->userdata;
		$UserName		= $data_session['ORI_User']['username'];
		$DateTime		= date('Y-m-d H:i:s');

		$DMF_TANDA = substr($no_ipp, 0, 3);

		$query		= "	SELECT
							a.id_bq AS id_bq,
							a.id_material AS id_material,
							a.nm_material AS nm_material,
							round( sum( ( a.last_cost * d.qty ) ), 3 ) AS last_cost
						FROM
							so_estimasi_total a 
							LEFT JOIN so_detail_header d ON a.id_milik = d.id
						WHERE
							a.id_material <> '0'
							AND a.id_bq='BQ-" . $no_ipp . "'
							AND a.id_material <> 'MTL-1903000'
						GROUP BY
							a.id_material,
							a.id_bq 
						ORDER BY
							a.nm_material";
		if ($DMF_TANDA == 'DMF') {
			$query		= "SELECT
								a.kode AS id_bq,
								a.id_material AS id_material,
								a.nm_material AS nm_material,
								round( sum( ( a.last_cost * d.qty ) ), 3 ) AS last_cost
							FROM
								deadstok_estimasi a 
								LEFT JOIN production_spk d ON a.kode = d.product_code_cut
							WHERE
								a.id_material <> '0'
								AND a.kode='$no_ipp'
								AND a.id_material <> 'MTL-1903000'
								AND a.category = 'utama'
								AND a.id_category != 'TYP-0001'
							GROUP BY
								a.id_material
							ORDER BY
								a.nm_material";
		}
		$result		 = $this->db->query($query)->result_array();
		// echo "<pre>";
		// print_r($result);
		// exit;
		$ArrHeader = [
			'no_ipp' => $no_ipp,
			'created_by' => $UserName,
			'created_date' => $DateTime
		];

		$ArrDetail = [];
		foreach ($result as $key => $value) {
			$ArrDetail[$key]['no_ipp'] 			= $no_ipp;
			$ArrDetail[$key]['id_material'] 	= $value['id_material'];
			$ArrDetail[$key]['nm_material'] 	= $value['nm_material'];
			$ArrDetail[$key]['berat'] 			= (!empty($value['last_cost'])) ? $value['last_cost'] : 0;
			$ArrDetail[$key]['total_request'] 	= 0;
		}

		$this->db->trans_start();
		$this->db->insert('planning_header', $ArrHeader);
		if (!empty($ArrDetail)) {
			$this->db->insert_batch('planning_detail', $ArrDetail);
		}
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=> 'Save process failed. Please try again later ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=> 'Save process success. Thanks ...',
				'status'	=> 1
			);
			history('Create request for penampungan request subgudang : ' . $no_ipp);
		}
		echo json_encode($Arr_Data);
	}

	public function get_ros($id_po)
	{
		$resData	= $this->db->query("select id,no_ros from report_of_shipment where id_po='" . $id_po . "' and status='APV'")->result_array();
		$option	= "<option value=''>No ROS</option>";
		foreach ($resData as $val => $valx) {
			$option .= "<option value='" . $valx['id'] . "'>" . ($valx['no_ros']) . "</option>";
		}
		$ArrJson	= array(
			'option' => $option
		);
		echo json_encode($ArrJson);
	}

	public function modal_history_subgudang()
	{
		$kode_trans = $this->uri->segment(3);
		$tanda     	= $this->uri->segment(4);

		$result			= $this->db->group_by('update_date')->select('update_by, update_date, SUM(qty_oke) AS qty_aktual, no_ipp')->get_where('warehouse_adjustment_check', array('kode_trans' => $kode_trans, 'update_by <>' => 'json'))->result_array();
		$result_header	= $this->db->get_where('warehouse_adjustment', array('kode_trans' => $kode_trans))->result();

		$data = array(
			'result' 	=> $result,
			'tanda' 	=> $tanda,
			'checked' 	=> $result_header[0]->checked,
			'kode_trans' => $result_header[0]->kode_trans,
			'no_po' 	=> $result_header[0]->no_ipp,
			'no_ipp' 	=> $result_header[0]->no_ipp,
			'qty_spk' 	=> $result_header[0]->qty_spk,
			'tanggal' 	=> (!empty($result_header[0]->tanggal)) ? date('d-M-Y', strtotime($result_header[0]->tanggal)) : '',
			'id_milik' 	=> get_name('production_detail', 'id_milik', 'no_spk', $result_header[0]->no_spk),
			'dated' 	=> date('ymdhis', strtotime($result_header[0]->created_date)),
			'resv' 		=> date('d F Y', strtotime($result_header[0]->created_date)),
			'GET_USERNAME' => get_detail_user(),
			'DETAIL_MATERIAL' => get_detailAktualAdjustmentCheck()

		);

		$this->load->view('Warehouse/modal_history_subgudang', $data);
	}

	public function print_request_check()
	{
		$post 			= $this->input->post();
		// $kode_trans     = $post['kode_trans'];
		// $update_by     	= $post['update_by'];
		// $update_date    = $post['update_date'];
		$kode_trans     = $this->uri->segment(3);
		$update_by     	= get_name('users', 'username', 'id_user', $this->uri->segment(4));
		$update_date    = date('Y-m-d H:i:s', strtotime($this->uri->segment(5)));
		$no_request     = $this->uri->segment(6);
		$data_session	= $this->session->userdata;
		$printby		= $data_session['ORI_User']['username'];

		$data_url		= base_url();
		$Split_Beda		= explode('/', $data_url);
		$Jum_Beda		= count($Split_Beda);
		$Nama_Beda		= $Split_Beda[$Jum_Beda - 2];

		$GET_SO_NUMBER = $this->db->get('so_number')->result_array();
		$ArrGetSO = [];
		foreach ($GET_SO_NUMBER as $val => $value) {
			$ArrGetSO[$value['id_bq']] = $value['so_number'];
		}

		$GET_SPK_NUMBER = $this->db->get_where('so_detail_header', array('no_spk <>' => NULL))->result_array();
		$ArrGetSPK = [];
		$ArrGetIPP = [];
		foreach ($GET_SPK_NUMBER as $val => $value) {
			$ArrGetSPK[$value['id']] = $value['no_spk'];
			$ArrGetIPP[$value['id']] = $value['id_bq'];
		}

		$rest_data 	= $this->db->get_where('warehouse_adjustment', array('kode_trans' => $kode_trans, 'status_id' => '1'))->result_array();
		$KeGudang = get_name('warehouse', 'nm_gudang', 'id', $rest_data[0]['id_gudang_ke']);
		$tgl_planning = '';
		if (!empty($no_request)) {
			$rest_req 	= $this->db->get_where('print_header', array('kode_uniq' => $no_request))->result_array();
			if (!empty($rest_req[0]['id_gudang'])) {
				$KeGudang = get_name('warehouse', 'nm_gudang', 'id', $rest_req[0]['id_gudang']);
			}
			if (!empty($rest_req[0]['tgl_planning']) and $rest_req[0]['tgl_planning'] != '0000-00-00') {
				$tgl_planning = date('d F Y', strtotime($rest_req[0]['tgl_planning']));
			}
		}

		$data = array(
			'rest_data' => $rest_data,
			'tgl_planning' => $tgl_planning,
			'KeGudang' => $KeGudang,
			'no_request' => ' / ' . $no_request,
			'ArrGetSO' => $ArrGetSO,
			'ArrGetSPK' => $ArrGetSPK,
			'ArrGetIPP' => $ArrGetIPP,
			'Nama_Beda' => $Nama_Beda,
			'printby' => $printby,
			'kode_trans' => $kode_trans,
			'update_by' => $update_by,
			'update_date' => $update_date
		);

		history('Print Request Material ' . $kode_trans . ', ' . $update_date);
		$this->load->view('Print/print_list_subgudang_check', $data);
	}
	function print_qrcode($id)
	{
		$detail = str_replace("~", "','", $id);
		$qDetail1 = " SELECT a.* FROM warehouse_adjustment_detail a WHERE a.id in ('" . $id . "') ORDER BY a.id";
		$restDetail1 = $this->db->query($qDetail1)->result_array();
		$data = array(
			'detail'	=> $restDetail1,
		);
		history('Print Qrcode');
		$this->load->view('Warehouse/print_qrcode', $data);
	}

	public function show_history_booking()
	{
		$data 			= $this->input->post();
		$no_ipp 		= $data['no_ipp'];
		$id_material 		= $data['id_material'];
		$id_gudang 		= $data['id_gudang'];

		$result		= $this->db
			->select('a.*')
			->from('warehouse_history a')
			->where('a.no_ipp', $no_ipp)
			->where('a.id_material', $id_material)
			->where('a.id_gudang', $id_gudang)
			->where('a.update_date > ', '2023-12-15 00:00:00')
			->or_group_start()
			->where('a.kd_gudang_ke', 'BOOKING')
			->where('a.kd_gudang_dari', 'BOOKING')
			->group_end()
			->get()
			->result_array();

		$data_html = "";
		$data_html .= "<tr>";
		$data_html .= "<th>#</th>";
		$data_html .= "<th>Gudang Dari</th>";
		$data_html .= "<th>Gudang Ke</th>";
		$data_html .= "<th class='text-right'>Qty Booking</th>";
		$data_html .= "<th class='text-right'>Booking Awal</th>";
		$data_html .= "<th class='text-right'>Booking Akhir</th>";
		$data_html .= "<th>Keterangan</th>";
		$data_html .= "<th class='text-center'>Tanggal</th>";
		$data_html .= "</tr>";
		$No = 0;
		$QTY_PLUS = 0;
		foreach ($result as $key => $value) {
			$key--;
			$No++;
			$bold = '';
			$bold2 = '';
			$color = 'text-blue';

			$gudang_dari 	= get_name('warehouse', 'nm_gudang', 'id', $value['id_gudang_dari']);
			$dari_gudang 	= (!empty($gudang_dari)) ? $gudang_dari : $value['kd_gudang_dari'];
			$ke_gudang 		= $value['kd_gudang_ke'];

			$QTY 			= $value['jumlah_mat'];
			// $QTY_SEBELUM 	= (!empty($result[$key]['jumlah_mat']))?$result[$key]['jumlah_mat']:0;
			// $QTY_AWAL 		= (!empty($result[$key]['jumlah_mat']))?$result[$key]['jumlah_mat'] + $QTY:0;
			$QTY_AWAL 		= $QTY_PLUS;

			if ($dari_gudang == 'BOOKING') {
				$bold = 'text-bold';
				$color = 'text-red';

				$QTY_AKHIR 	= $QTY_AWAL - $QTY;
			}
			if ($ke_gudang == 'BOOKING') {
				$bold2 = 'text-bold';

				$QTY_AKHIR 	= $QTY_AWAL + $QTY;
			}

			if ($No == 1) {
				$QTY_AKHIR 	= $QTY;
			}
			if ($No == 1) {
				$QTY_AWAL 	= 0;
			}

			$data_html .= "<tr>";
			$data_html .= "<td>" . $No . "</td>";
			$data_html .= "<td class='text-left " . $bold . "'>" . $dari_gudang . "</td>";
			$data_html .= "<td class='text-left " . $bold2 . "'>" . $ke_gudang . "</td>";
			$data_html .= "<td class='text-right " . $color . "'>" . number_format($QTY, 4) . "</td>";
			$data_html .= "<td class='text-right " . $color . "'>" . number_format($QTY_AWAL, 4) . "</td>";
			$data_html .= "<td class='text-right " . $color . "'>" . number_format($QTY_AKHIR, 4) . "</td>";
			$data_html .= "<td>" . strtoupper($value['ket']) . "</td>";
			$data_html .= "<td class='text-center'>" . date('d-M-Y H:i:s', strtotime($value['update_date'])) . "</td>";
			$data_html .= "</tr>";

			$QTY_PLUS = $QTY_AKHIR;
		}
		$data_html .= "<tr>";
		$data_html .= "<td></td>";
		$data_html .= "<td colspan='4' class='text-bold'>SISA BOOKING</td>";
		$data_html .= "<td class='text-right text-bold'>" . number_format($QTY_AKHIR, 4) . "</td>";
		$data_html .= "<td colspan='2'></td>";
		$data_html .= "</tr>";


		$Arr_Kembali	= array(
			'status'	=> 1,
			'data_html'	=> $data_html
		);
		echo json_encode($Arr_Kembali);
	}

	function close_booking_material()
	{
		$no_ipp 	= $this->input->post('no_ipp');
		$id_gudang 	= 2;
		$data_session	= $this->session->userdata;

		$SQL 	= "SELECT * FROM warehouse_history WHERE no_ipp = '$no_ipp' AND kd_gudang_ke='BOOKING' AND update_date > '2023-12-15 00:00:00' GROUP BY id_material";
		$result = $this->db->query($SQL)->result_array();

		$ArrDetail = [];
		foreach ($result as $key => $value) {
			$id_material = $value['id_material'];
			$No = 0;
			$QTY_PLUS = 0;
			$resultHist		= $this->db
				->select('a.*')
				->from('warehouse_history a')
				->where('a.no_ipp', $no_ipp)
				->where('a.id_material', $id_material)
				->where('a.id_gudang', $id_gudang)
				->where('a.update_date > ', '2023-12-15 00:00:00')
				->or_group_start()
				->where('a.kd_gudang_ke', 'BOOKING')
				->where('a.kd_gudang_dari', 'BOOKING')
				->group_end()
				->get()
				->result_array();
			foreach ($resultHist as $key2 => $value2) {
				$No++;

				$gudang_dari 	= get_name('warehouse', 'nm_gudang', 'id', $value2['id_gudang_dari']);
				$dari_gudang 	= (!empty($gudang_dari)) ? $gudang_dari : $value2['kd_gudang_dari'];
				$ke_gudang 		= $value2['kd_gudang_ke'];

				$QTY 			= $value2['jumlah_mat'];
				$QTY_AWAL 		= $QTY_PLUS;

				if ($dari_gudang == 'BOOKING') {
					$QTY_AKHIR 	= $QTY_AWAL - $QTY;
				}
				if ($ke_gudang == 'BOOKING') {
					$QTY_AKHIR 	= $QTY_AWAL + $QTY;
				}

				if ($No == 1) {
					$QTY_AKHIR 	= $QTY;
				}
				if ($No == 1) {
					$QTY_AWAL 	= 0;
				}

				$QTY_PLUS = $QTY_AKHIR;
			}

			$ArrDetail[$key]['id_material'] = $id_material;
			$ArrDetail[$key]['nm_material'] = $value['nm_material'];
			$ArrDetail[$key]['sisa'] = $QTY_AKHIR;
		}

		$ArrDeatil = array();
		$ArrHist = array();
		foreach ($ArrDetail as $val => $valx) {
			$sqlWhDetail	= "	SELECT
									a.*
								FROM
									warehouse_stock a
								WHERE
									a.id_material = '" . $valx['id_material'] . "'
									AND a.id_material <> 'MTL-1903000'
									AND (a.id_gudang = '2')
								";
			$restWhDetail	= $this->db->query($sqlWhDetail)->result_array();

			$ArrHist[$val]['id_material'] 		= $restWhDetail[0]['id_material'];
			$ArrHist[$val]['idmaterial'] 		= $restWhDetail[0]['idmaterial'];
			$ArrHist[$val]['nm_material'] 		= $restWhDetail[0]['nm_material'];
			$ArrHist[$val]['id_category'] 		= $restWhDetail[0]['id_category'];
			$ArrHist[$val]['nm_category'] 		= $restWhDetail[0]['nm_category'];
			$ArrHist[$val]['id_gudang'] 		= $restWhDetail[0]['id_gudang'];
			$ArrHist[$val]['kd_gudang'] 		= $restWhDetail[0]['kd_gudang'];
			$ArrHist[$val]['kd_gudang_dari'] 	= 'BOOKING';
			$ArrHist[$val]['kd_gudang_ke'] 		= 'BOOK CLOSE';
			$ArrHist[$val]['qty_stock_awal'] 	= $restWhDetail[0]['qty_stock'];
			$ArrHist[$val]['qty_stock_akhir'] 	= $restWhDetail[0]['qty_stock'];
			$ArrHist[$val]['qty_booking_awal'] 	= $restWhDetail[0]['qty_booking'];
			if ($valx['sisa'] >= 0) {
				$ArrHist[$val]['qty_booking_akhir'] = $restWhDetail[0]['qty_booking'] - $valx['sisa'];
			} else {
				$ArrHist[$val]['qty_booking_akhir'] = $restWhDetail[0]['qty_booking'] + $valx['sisa'];
			}
			$ArrHist[$val]['no_ipp'] 			= $no_ipp;
			$ArrHist[$val]['jumlah_mat'] 		= $valx['sisa'];
			$ArrHist[$val]['ket'] 				= 'booking material close';
			$ArrHist[$val]['update_by'] 		= $data_session['ORI_User']['username'];
			$ArrHist[$val]['update_date'] 		= date('Y-m-d H:i:s');



			$ArrDeatil[$val]['id'] 				= $restWhDetail[0]['id'];
			$ArrDeatil[$val]['id_material'] 	= $restWhDetail[0]['id_material'];
			$ArrDeatil[$val]['id_gudang'] 		= $restWhDetail[0]['id_gudang'];
			if ($valx['sisa'] >= 0) {
				$ArrDeatil[$val]['qty_booking'] = $restWhDetail[0]['qty_booking'] - $valx['sisa'];
			} else {
				$ArrDeatil[$val]['qty_booking'] = $restWhDetail[0]['qty_booking'] + $valx['sisa'];
			}
			$ArrDeatil[$val]['update_by'] 		= $data_session['ORI_User']['username'];
			$ArrDeatil[$val]['update_date'] 	= date('Y-m-d H:i:s');
		}

		$ArrUpdate = [
			'sts_booking_close' => 'Y'
		];

		// echo "<pre>";
		// print_r($ArrDetail);
		// print_r($ArrHist);
		$this->db->trans_start();
		if (!empty($ArrDeatil)) {
			$this->db->update_batch('warehouse_stock', $ArrDeatil, 'id');
		}
		if (!empty($ArrHist)) {
			$this->db->insert_batch('warehouse_history', $ArrHist);
		}

		$this->db->where('no_ipp', $no_ipp);
		$this->db->update('warehouse_planning_header', $ArrUpdate);
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=> 'Save process failed. Please try again later ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=> 'Save process success. Thanks ...',
				'status'	=> 1
			);
			history('Booking Material Close ' . $no_ipp);
		}
		echo json_encode($Arr_Data);
		//create history close booking
	}

	public function add_lot()
	{
		$data = $this->input->post();
		// $id = $data['id'];

		$this->db->trans_begin();

		$get_detail = $this->db->get_where('tr_incoming_check_detail', ['id' => $data['id']])->row();

		$this->db->select('a.*, b.code as satuan, c.code as packing');
		$this->db->from('new_inventory_4 a');
		$this->db->join('ms_satuan b', 'b.id = a.id_unit', 'left');
		$this->db->join('ms_satuan c', 'c.id = a.id_unit_packing', 'left');
		$this->db->where('a.code_lv4', $get_detail->id_material);
		$get_material = $this->db->get()->row();

		$config['upload_path'] = './uploads/incoming_check/';
		$config['allowed_types'] = '*';
		$config['remove_spaces'] = FALSE;
		$config['encrypt_name'] = TRUE;
		$file_name = '';

		if (!empty($_FILES['upload_file']['name'])) {
			$_FILES['file']['name'] = $_FILES['upload_file']['name'];
			$_FILES['file']['type'] = $_FILES['upload_file']['type'];
			$_FILES['file']['tmp_name'] = $_FILES['upload_file']['tmp_name'];
			$_FILES['file']['error'] = $_FILES['upload_file']['error'];
			$_FILES['file']['size'] = $_FILES['upload_file']['size'];
			$this->load->library('upload', $config);
			$this->upload->initialize($config);
			if ($this->upload->do_upload('file')) {
				$uploadData = $this->upload->data();
				$file_name = 'uploads/incoming_check/' . $uploadData['file_name'];
			} else {
				print_r($this->upload->display_errors());
				exit;
			}
		}

		// print_r($file_name);
		// exit;

		$this->db->insert('tr_checked_incoming_detail', [
			'kode_trans' => $data['kode_trans'],
			'no_ipp' => $get_detail->no_ipp,
			'id_detail' => $get_detail->id,
			'id_material' => $get_detail->id_material,
			'nm_material' => $get_detail->nm_material,
			'qty_order' => $get_detail->qty_order,
			'unit' => $get_material->satuan,
			'packing' => $get_material->packing,
			'qty_ng' => $data['qty_ng'],
			'qty_oke' => $data['qty_oke'],
			'qty_pack' => $data['qty_pack'],
			'expired_date' => $data['expired_date'],
			'uploaded_file' => $file_name,
			'lot_description' => $data['lot_info'],
			'created_by' => $this->auth->user_id(),
			'created_date' => date('Y-m-d H:i:s')
		]);

		if ($this->db->trans_status() === FALSE) {
			$error = $this->db->error();
			print_r($error['message']);
			$this->db->trans_rollback();
			$valid = 0;
		} else {
			$this->db->trans_commit();
			$valid = 1;
		}

		echo json_encode(['hasil' => $valid, 'kode_trans' => $data['kode_trans']]);
	}

	public function refresh_incoming_check()
	{
		$data = $this->input->post();
		$kode_trans = $data['kode_trans'];
		$no_ipp = $data['no_ipp'];

		$get_no_surat = $this->db->select('no_surat')->get_where('tr_purchase_order', ['no_po' => $no_ipp])->row();

		$sql = '
            SELECT 
					a.*, 
					b.konversi,
					c.code as satuan,
					e.code as packing,
					f.no_surat
				FROM 
					tr_incoming_check_detail a 
					LEFT JOIN new_inventory_4 b ON b.code_lv4 = a.id_material 
					LEFT JOIN ms_satuan c ON c.id = b.id_unit 
					LEFT JOIN tr_incoming_check_detail d ON d.kode_trans = a.kode_trans AND d.id_material = a.id_material
					LEFT JOIN ms_satuan e ON e.id = b.id_unit_packing 
					LEFT JOIN tr_purchase_order f ON f.no_po LIKE CONCAT("%",a.no_ipp,"%")
					LEFT JOIN dt_trans_po g ON g.id = a.id_po_detail
				WHERE 	
					a.kode_trans = "' . $kode_trans . '" AND
					g.no_po = "' . $no_ipp . '"
				GROUP BY a.id_material, a.id
			';
		$result = $this->db->query($sql)->result_array();

		$hasil = '';
		$no = 1;
		foreach ($result as $item) :

			$hasil .= '<tr>';
			$hasil .= '
					<input type="hidden" name="id" value="' . $item['id'] . '">
					<input type="hidden" name="kode_trans_' . $item['id'] . '" value="' . $item['kode_trans'] . '">
					<input type="hidden" name="id_material_' . $item['id'] . '" value="' . $item['id_material'] . '">
				';
			$hasil .= '<td class="text-center">' . $no . '</td>';
			$hasil .= '<td class="text-center">' . $get_no_surat->no_surat . '</td>';
			$hasil .= '<td class="">' . $item['nm_material'] . '</td>';
			$hasil .= '<td class="text-center">' . number_format($item['qty_order'], 2) . ' <input type="hidden" name="qty_order_' . $item['id'] . '" value="' . $item['qty_order'] . '"> </td>';
			$hasil .= '<td class="text-center">' . $item['satuan'] . '</td>';
			$hasil .= '<td class="text-center">' . $item['konversi'] . ' <input type="hidden" name="konversi_' . $item['konversi'] . '" class="konversi_' . $item['id'] . '" value="' . $item['konversi'] . '"></td>';
			$hasil .= '<td class="text-center">' . number_format(($item['qty_order'] / $item['konversi'])) . '</td>';
			$hasil .= '<td class="text-center">' . $item['packing'] . '</td>';
			$hasil .= '<td class="">
					<input type="text" name="qty_ng_' . $item['id'] . '" id="" class="form-control form-control-sm input_hid maskM qty_ng qty_ng_' . $item['id'] . '" data-id="' . $item['id'] . '" data-incoming="' . $item['qty_order'] . '" data-konversi="' . $item['konversi'] . '" required>
				</td>';
			$hasil .= '<td class="">
					<input type="text" name="qty_oke_' . $item['id'] . '" id="" class="form-control form-control-sm maskM input_hid qty_oke qty_oke_' . $item['id'] . '" data-id="' . $item['id'] . '" data-id_material="' . $item['id_material'] . '">
				</td>';
			$hasil .= '<td class="">
					<input type="text" name="qty_pack_' . $item['id'] . '" id="" class="form-control form-control-sm maskM qty_pack_' . $item['id'] . '" readonly>
				</td>';
			$hasil .= '<td class="">
					<input type="date" name="expired_date_' . $item['id'] . '" id="" class="form-control form-control-sm input_hid expired_date_' . $item['id'] . '" min="' . date('Y-m-d') . '" data-id="' . $item['id'] . '">
				</td>';
			$hasil .= '<td>
					<input type="file" name="upload_file_' . $item['id'] . '" id="" class="form-control input_hid upload_file_' . $item['id'] . '" data-id="' . $item['id'] . '">
				</td>';
			$hasil .= '<td>
					<input type="text" name="lot_info_' . $item['id'] . '" id="" class="form-control input_hid lot_info_' . $item['id'] . '" data-id="' . $item['id'] . '">
				</td>';
			$hasil .= '<td>
					<button type="button" class="btn btn-sm btn-primary add_lot add_lot_' . $item['id'] . '" data-id="' . $item['id'] . '" data-kode_trans="' . $item['kode_trans'] . '" data-id_material="' . $item['id_material'] . '" data-no_ipp="'.$no_ipp.'"><i class="fa fa-plus"></i></button>
				</td>';
			$hasil .= '</tr>';

			$get_checked = $this->db->get_where('tr_checked_incoming_detail', ['id_detail' => $item['id']])->result_array();
			foreach ($get_checked as $checked_item) :
				$hasil .= '<tr>';
				$hasil .= '<td colspan="8"></td>';
				$hasil .= '<td><input type="text" class="form-control" name="" id="" value="' . number_format($checked_item['qty_ng'], 2) . '" readonly></td>';
				$hasil .= '<td><input type="text" class="form-control" name="" id="" value="' . number_format($checked_item['qty_oke'], 2) . '" readonly></td>';
				$hasil .= '<td><input type="text" class="form-control" name="" id="" value="' . number_format($checked_item['qty_pack'], 2) . '" readonly></td>';
				$hasil .= '<td class="text-center">' . date('d F Y', strtotime($checked_item['expired_date'])) . '</td>';
				$hasil .= '<td class="text-center">
				';

				if(file_exists($checked_item['uploaded_file']) && $checked_item['uploaded_file'] !== ''){
					$hasil .= '<a href="' . base_url($checked_item['uploaded_file']) . '" class="btn btn-sm btn-primary" target="_blank">Download File</a>';
				}
				
				$hasil .= '</td>';
				$hasil .= '<td>' . $checked_item['lot_description'] . '</td>';
				$hasil .= '<td class="text-center">';
				if ($checked_item['sts'] == '0') {
					$hasil .= '<button type="button" class="btn btn-sm btn-danger del_checked" data-id="' . $checked_item['id'] . '" data-kode_trans="' . $checked_item['kode_trans'] . '" data-no_ipp="'.$no_ipp.'"><i class="fa fa-trash"></i></button>';
				}
				$hasil .= '</td>';
				$hasil .= '</tr>';
			endforeach;

			$no++;
		endforeach;

		$get_summary_incoming = $this->db->select('
            a.nm_material,
            a.qty_order,
            IF(SUM(b.qty_ng) IS NULL, 0, SUM(b.qty_ng)) AS ttl_qty_ng,
            IF(SUM(b.qty_oke) IS NULL, 0, SUM(b.qty_oke)) AS ttl_qty_oke,
        ')
        ->from('tr_incoming_check_detail a')
        ->join('tr_checked_incoming_detail b', 'b.id_detail = a.id', 'left')
        ->where('a.kode_trans', $kode_trans)
        ->group_by('a.id_material, a.id')
        ->get()
        ->result_array();

		$hasil2 = '';
		$no = 1;

		$stok_tidak_masuk = 0;
		$stok_masuk = 0;

		foreach($get_summary_incoming as $summ_incom){
			$hasil2 .= '<tr>'; 
			$hasil2 .= '<td class="text-center">'.$no.'</td>'; 
			$hasil2 .= '<td class="text-center">'.$summ_incom['nm_material'].'</td>'; 
			$hasil2 .= '<td class="text-center">'.number_format($summ_incom['qty_order']).'</td>'; 
			$hasil2 .= '<td class="text-center">'.number_format($summ_incom['ttl_qty_ng']).'</td>'; 
			$hasil2 .= '<td class="text-center">'.number_format($summ_incom['ttl_qty_oke']).'</td>'; 
			$hasil2 .= '</tr>';

			$no++;

			$stok_tidak_masuk += $summ_incom['ttl_qty_ng'];
			$stok_masuk += $summ_incom['ttl_qty_oke'];
		}

		$hasil3 = '
			<tr>
				<td colspan="3" class="text-right">Masuk ke Stock</td>
				<td class="text-center">
					<span style="color: red;">'.number_format($stok_tidak_masuk).'</span>
				</td>
				<td class="text-center">
					<span style="color: green;">'.number_format($stok_masuk).'</span>
				</td>
			</tr>
		';

		echo json_encode([
			'hasil' => $hasil,
			'hasil2' => $hasil2,
			'hasil3' => $hasil3
		]);
	}

	public function del_checked_incoming()
	{
		$data = $this->input->post();

		$this->db->trans_begin();

		$this->db->delete('tr_checked_incoming_detail', ['id' => $data['id']]);

		if ($this->db->trans_status() === FALSE) {
			$error = $this->db->error();
			print_r($error['message']);
			$this->db->trans_rollback();
			$valid = 0;
		} else {
			$this->db->trans_commit();
			$valid = 1;
		}

		echo json_encode(['hasil' => $valid]);
	}

	public function download_qr($kode_trans)
	{

		//		$result_header		= $this->db->get_where('warehouse_adjustment',array('kode_trans'=>$kode_trans))->result();
		// $sql_header    = "SELECT a.*,b.id as id_ros, b.no_ros FROM warehouse_adjustment a left join report_of_shipment b on a.no_ros=b.id WHERE a.kode_trans='" . $kode_trans . "' ";
		$sql_header    = "SELECT a.*, b.no_surat FROM tr_incoming_check a LEFT JOIN tr_purchase_order b ON b.no_po = a.no_ipp WHERE a.kode_trans = '" . $kode_trans . "'";
		$result_header        = $this->db->query($sql_header)->row();
		$pembeda = substr($result_header->no_ipp, 0, 1);

		// if ($pembeda == 'P') {
		//     $sql     = "	SELECT
		// 					a.*,
		// 					b.qty_purchase,
		// 					b.qty_in,
		// 					b.satuan,
		// 					b.id AS id2
		// 				FROM
		// 					warehouse_adjustment_detail a
		// 					LEFT JOIN tran_material_po_detail b ON a.no_ipp=b.no_po AND a.id_po_detail = b.id
		// 				WHERE
		// 					a.id_material = b.id_material
		// 					AND a.kode_trans='" . $kode_trans . "' ";
		// }
		// if ($pembeda == 'N') {
		//     $sql     = "	SELECT
		// 					a.*,
		// 					b.qty_purchase,
		// 					b.qty_in,
		// 					b.id AS id2
		// 				FROM
		// 					warehouse_adjustment_detail a
		// 					LEFT JOIN tran_material_non_po_detail b ON a.no_ipp=b.no_non_po AND a.id_po_detail = b.id
		// 				WHERE
		// 					a.id_material = b.id_material
		// 					AND a.kode_trans='" . $kode_trans . "' ";
		// }
		$sql = '
            SELECT 
                a.*, 
                b.konversi,
                c.code as satuan,
                e.code as packing,
                IF(SUM(d.qty_order) IS NULL, 0, SUM(d.qty_order)) as ttl_incoming ,
                f.no_surat
            FROM 
                tr_incoming_check_detail a 
                LEFT JOIN new_inventory_4 b ON b.code_lv4 = a.id_material 
                LEFT JOIN ms_satuan c ON c.id = b.id_unit 
                LEFT JOIN tr_incoming_check_detail d ON d.kode_trans = a.kode_trans AND d.id_material = a.id_material
                LEFT JOIN ms_satuan e ON e.id = b.id_unit_packing 
                LEFT JOIN tr_purchase_order f ON f.no_po = a.no_ipp
            WHERE 
                a.kode_trans = "' . $kode_trans . '"';
		$result            = $this->db->query($sql)->result_array();

		$this->db->select('d.no_pr');
		$this->db->from('tr_incoming_check_detail a');
		$this->db->join('dt_trans_po b', 'b.id = a.id_po_detail');
		// $this->db->from('dt_trans_po a');
		$this->db->join('material_planning_base_on_produksi_detail c', 'c.id = b.idpr', 'left');
		$this->db->join('material_planning_base_on_produksi d', 'd.so_number = c.so_number', 'left');
		$this->db->where('a.kode_trans', $kode_trans);
		$this->db->group_by('d.no_pr');
		$get_no_pr = $this->db->get()->result_array();

		$arr_no_pr = array();
		foreach ($get_no_pr as $pr) :
			$arr_no_pr[] = $pr['no_pr'];
		endforeach;

		$no_pr = implode(', ', $arr_no_pr);

		$no_po = [];
		$get_no_surat = $this
		->db
		->query("
			SELECT
				a.no_surat
			FROM
				tr_purchase_order a
			WHERE
				a.no_po IN ('".str_replace(",","','",$result_header->no_ipp)."')
		")
		->result();
		foreach($get_no_surat as $item) {
			$no_po[] = $item->no_surat;
		}

		$no_po = implode(',', $no_po);





		$data = array(
			'result'     => $result,
			'result_header'     => $result_header,
			'no_po'     => $result_header->no_ipp,
			'no_surat'     => $result_header->no_surat,
			'kode_trans'     => $result_header->kode_trans,
			'id_header'     => $result_header->id,
			'gudang_tujuan'     => $result_header->kd_gudang_ke,
			'id_tujuan'     => $result_header->id_gudang_ke,
			'dated'     => date('ymdhis', strtotime($result_header->created_date)),
			'resv'     => date('d F Y', strtotime($result_header->created_date)),
			'no_pr' => $no_pr,
			'file_incoming_material' => $result_header->file_incoming_material,
			'no_po' => $no_po
			// 'id_ros'    => $result_header[0]->id_ros,
			// 'no_ros'    => $result_header[0]->no_ros,
			// 'total_freight'    => $result_header[0]->total_freight,
		);

		$this->load->view('modal_download_qr', $data);
	}

	public function save_download_qr()
	{

		$group_id = implode('-', $this->input->post('checkboxx'));

		echo json_encode(['id' => $group_id]);
	}

	public function download_incoming_checked_qr($id)
	{
		$data_session	= $this->session->userdata;
		$session 		   = $this->session->userdata('app_session');
		$printby		= $session['id_user'];

		$data_url		= base_url();
		$Split_Beda		= explode('/', $data_url);
		$Jum_Beda		= count($Split_Beda);
		$Nama_Beda		= $Split_Beda[$Jum_Beda - 2];

		//update status qc
		$explode = explode("-", $id);
		// $this->db->where_in('id', $explode)->update('so_internal_product', ['sts_print_qr' => 'Y', 'sts_print_qr_date' => date('Y-m-d H:i:s')]);

		$this->db->select('a.*, b.nm_lengkap');
		$this->db->from('tr_checked_incoming_detail a');
		$this->db->join('users b', 'b.id_user = a.created_by', 'left');
		$this->db->join('tr_incoming_check_detail c', 'c.kode_trans = a.kode_trans AND c.id_material = a.id_material', 'left');
		$this->db->where_in('c.id', explode('-', $id));
		$getData = $this->db->get()->result_array();


		// $getData = $this->db
		// 	->select('a.id, a.daycode, a.qc_pass, a.status, c.code_lv4, c.nama_product, a.inspektor')
		// 	->from('so_internal_product a')
		// 	->join('so_internal_spk b', 'a.id_key_spk=b.id', 'left')
		// 	->join('so_internal c', 'b.id_so=c.id', 'left')
		// 	->where_in('a.id', explode("-", $id))
		// 	->get()
		// 	->result_array();

		$data = array(
			'Nama_Beda' => $Nama_Beda,
			'printby' => $printby,
			'detail' => $getData,
		);

		$this->load->view('download_incoming_checked_qr', $data);
	}
}
