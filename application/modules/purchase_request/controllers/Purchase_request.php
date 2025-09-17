<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/*
 * @author Ichsan
 * @copyright Copyright (c) 2019, Ichsan
 *
 * This is controller for Master Supplier
 */

class Purchase_request extends Admin_Controller
{
	//Permission
	protected $viewPermission 	= 'List_Outstanding_PR.View';
	protected $addPermission  	= 'List_Outstanding_PR.Add';
	protected $managePermission = 'List_Outstanding_PR.Manage';
	protected $deletePermission = 'List_Outstanding_PR.Delete';

	public function __construct()
	{
		parent::__construct();
		$this->load->library(array('upload', 'Image_lib'));
		$this->load->model(array(
			'Purchase_request/Pr_model',
			'Aktifitas/aktifitas_model',
		));
		$this->template->title('Manage Data Supplier');
		$this->template->page_icon('fa fa-building-o');

		date_default_timezone_set('Asia/Bangkok');
	}
	public function index($status_filter = null)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		// if ($status_filter !== null) {
		// 	if ($status_filter == '3') {
		// 		$data = $this->db->query('
		// 			SELECT 
		// 				a.so_number AS no_pr,
		// 				"" AS no_pengajuan,
		// 				a.project AS project,
		// 				a.booking_by AS booking_by,
		// 				"" AS booking_by_name,
		// 				a.booking_date AS booking_date,
		// 				"1" AS pr_non_depart, 
		// 				"0" AS pr_depart
		// 			FROM material_planning_base_on_produksi a
		// 			WHERE 
		// 				a.booking_date IS NOT NULL AND a.reject_status = 0 AND
		// 				(SELECT COUNT(aa.id) FROM material_planning_base_on_produksi_detail aa WHERE aa.so_number = a.so_number) > 0 AND
		// 				(SELECT COUNT(aa.id) FROM material_planning_base_on_produksi_detail aa WHERE aa.so_number = a.so_number AND aa.status_app = "Y") > 0 AND
		// 				(SELECT COUNT(ac.id) FROM dt_trans_po ac WHERE ac.idpr IN (SELECT ab.id FROM material_planning_base_on_produksi_detail ab WHERE ab.so_number = a.so_number)  AND ac.tipe = "") < 1

		// 			UNION ALL

		// 			SELECT 
		// 				b.no_pr AS no_pr,
		// 				b.no_pengajuan AS no_pengajuan,
		// 				b.project_name AS project,
		// 				b.created_by AS booking_by,
		// 				"" AS booking_by_name,
		// 				DATE_FORMAT(b.created_date, "%d %M %Y") AS booking_date,
		// 				"0" AS pr_non_depart, 
		// 				"1" AS pr_depart
		// 			FROM
		// 				rutin_non_planning_header b
		// 			WHERE
		// 				b.status_id = "1" AND 
		// 				(SELECT COUNT(aa.id) FROM rutin_non_planning_detail aa WHERE aa.no_pr = b.no_pr) > 0 AND
		// 				(SELECT COUNT(aa.id) FROM rutin_non_planning_detail aa WHERE aa.no_pr = b.no_pr AND aa.sts_app = "Y") > 0 AND
		// 				(SELECT COUNT(ac.id) FROM dt_trans_po ac WHERE ac.idpr IN (SELECT ab.id FROM rutin_non_planning_detail ab WHERE ab.no_pr = b.no_pr) AND ac.tipe = "depart") < 1
		// 			ORDER BY no_pr
		// 		')->result();
		// 	} else {
		// 		// $data = $this->db->query('
		// 		// 	SELECT a.*
		// 		// 	FROM material_planning_base_on_produksi a
		// 		// 	WHERE 
		// 		// 		a.booking_date IS NOT NULL AND a.reject_status = 0 AND
		// 		// 		(SELECT COUNT(aa.id) FROM material_planning_base_on_produksi_detail aa WHERE aa.so_number = a.so_number) > 0 AND
		// 		// 		(SELECT COUNT(aa.id) FROM material_planning_base_on_produksi_detail aa WHERE aa.so_number = a.so_number AND aa.status_app = "Y") > 0 AND
		// 		// 		(SELECT COUNT(ac.id) FROM dt_trans_po ac WHERE ac.idpr IN (SELECT ab.id FROM material_planning_base_on_produksi ab WHERE ab.so_number = a.so_number)) > 0
		// 		// 	ORDER BY a.so_number
		// 		// ')->result();

		// 		$data = $this->db->query('
		// 			SELECT 
		// 				a.so_number AS no_pr,
		// 				"" AS no_pengajuan,
		// 				a.project AS project,
		// 				a.booking_by AS booking_by,
		// 				"" AS booking_by_name,
		// 				a.booking_date AS booking_date,
		// 				"1" AS pr_non_depart, 
		// 				"0" AS pr_depart
		// 			FROM material_planning_base_on_produksi a
		// 			WHERE 
		// 				a.booking_date IS NOT NULL AND a.reject_status = 0 AND
		// 				(SELECT COUNT(aa.id) FROM material_planning_base_on_produksi_detail aa WHERE aa.so_number = a.so_number) > 0 AND
		// 				(SELECT COUNT(aa.id) FROM material_planning_base_on_produksi_detail aa WHERE aa.so_number = a.so_number AND aa.status_app = "Y") > 0 AND
		// 				(SELECT COUNT(ac.id) FROM dt_trans_po ac WHERE ac.idpr IN (SELECT ab.id FROM material_planning_base_on_produksi_detail ab WHERE ab.so_number = a.so_number) AND ac.tipe = "") > 0

		// 			UNION ALL

		// 			SELECT 
		// 				b.no_pr AS no_pr,
		// 				b.no_pengajuan AS no_pengajuan,
		// 				b.project_name AS project,
		// 				b.created_by AS booking_by,
		// 				"" AS booking_by_name,
		// 				DATE_FORMAT(b.created_date, "%d %M %Y") AS booking_date,
		// 				"0" AS pr_non_depart, 
		// 				"1" AS pr_depart
		// 			FROM
		// 				rutin_non_planning_header b
		// 			WHERE
		// 				b.status_id = "1" AND 
		// 				(SELECT COUNT(aa.id) FROM rutin_non_planning_detail aa WHERE aa.no_pr = b.no_pr) > 0 AND
		// 				(SELECT COUNT(aa.id) FROM rutin_non_planning_detail aa WHERE aa.no_pr = b.no_pr AND aa.sts_app = "Y") > 0 AND
		// 				(SELECT COUNT(ac.id) FROM dt_trans_po ac WHERE ac.idpr IN (SELECT ab.id FROM rutin_non_planning_detail ab WHERE ab.no_pr = b.no_pr) AND ac.tipe = "depart") > 0
		// 			ORDER BY no_pr
		// 		')->result();
		// 	}
		// } else {
		// 	// $data = $this->db->query('
		// 	// 	SELECT a.*
		// 	// 	FROM material_planning_base_on_produksi a
		// 	// 	WHERE 
		// 	// 		a.booking_date IS NOT NULL AND a.reject_status = 0 AND
		// 	// 		(SELECT COUNT(aa.id) FROM material_planning_base_on_produksi_detail aa WHERE aa.so_number = a.so_number) > 0 AND
		// 	// 		(SELECT COUNT(aa.id) FROM material_planning_base_on_produksi_detail aa WHERE aa.so_number = a.so_number AND aa.status_app = "Y") > 0
		// 	// 	ORDER BY a.so_number
		// 	// ')->result();

		// 	$data = $this->db->query('
		// 		SELECT 
		// 			a.so_number AS no_pr,
		// 			"" AS no_pengajuan,
		// 			a.project AS project,
		// 			a.booking_by AS booking_by,
		// 			"" AS booking_by_name,
		// 			a.booking_date AS booking_date,
		// 			"1" AS pr_non_depart, 
		// 			"0" AS pr_depart
		// 		FROM material_planning_base_on_produksi a
		// 		WHERE 
		// 			a.booking_date IS NOT NULL AND a.reject_status = 0 AND
		// 			(SELECT COUNT(aa.id) FROM material_planning_base_on_produksi_detail aa WHERE aa.so_number = a.so_number) > 0 AND
		// 			(SELECT COUNT(aa.id) FROM material_planning_base_on_produksi_detail aa WHERE aa.so_number = a.so_number AND aa.status_app = "Y") > 0

		// 		UNION ALL

		// 		SELECT 
		// 			b.no_pr AS no_pr,
		// 			b.no_pengajuan AS no_pengajuan,
		// 			b.project_name AS project,
		// 			b.created_by AS booking_by,
		// 			"" AS booking_by_name,
		// 			DATE_FORMAT(b.created_date, "%d %M %Y") AS booking_date,
		// 				"0" AS pr_non_depart, 
		// 				"1" AS pr_depart
		// 			FROM
		// 				rutin_non_planning_header b
		// 			WHERE
		// 				b.status_id = "1" AND 
		// 				(SELECT COUNT(aa.id) FROM rutin_non_planning_detail aa WHERE aa.no_pr = b.no_pr) > 0 AND
		// 				(SELECT COUNT(aa.id) FROM rutin_non_planning_detail aa WHERE aa.no_pr = b.no_pr AND aa.sts_app = "Y") > 0
		// 			ORDER BY no_pr
		// 		')->result();
		// }

		$data = $this->db->query('
			SELECT 
				a.no_pr AS no_pr,
				a.so_number AS no_pengajuan,
				a.project AS project,
				a.booking_by AS booking_by,
				"" AS booking_by_name,
				a.booking_date AS booking_date,
				"1" AS pr_non_depart, 
				"0" AS pr_depart,
				"0" AS pr_asset
			FROM material_planning_base_on_produksi a
			WHERE 
				a.booking_date IS NOT NULL AND a.reject_status = 0 AND
				(SELECT COUNT(aa.id) FROM material_planning_base_on_produksi_detail aa WHERE aa.so_number = a.so_number) > 0 AND
				(SELECT COUNT(aa.id) FROM material_planning_base_on_produksi_detail aa WHERE aa.so_number = a.so_number AND aa.status_app = "Y") > 0 AND
				a.category IN ("pr material", "pr stok") AND
				a.metode_pembelian = "1" AND
				a.close_pr IS NULL

			UNION ALL

			SELECT 
				b.no_pr AS no_pr,
				b.no_pengajuan AS no_pengajuan,
				b.project_name AS project,
				b.created_by AS booking_by,
				"" AS booking_by_name,
				DATE_FORMAT(b.created_date, "%d %M %Y") AS booking_date,
					"0" AS pr_non_depart, 
					"1" AS pr_depart,
					"0" AS pr_asset
				FROM
					rutin_non_planning_header b
				WHERE
					b.status_id = "1" AND 
					(SELECT COUNT(aa.id) FROM rutin_non_planning_detail aa WHERE aa.no_pr = b.no_pr) > 0 AND
					(SELECT COUNT(aa.id) FROM rutin_non_planning_detail aa WHERE aa.no_pr = b.no_pr AND aa.sts_app = "Y") > 0 AND
					b.metode_pembelian = "1" AND
					b.close_pr IS NULL

			UNION ALL
			
			SELECT
				c.no_pr as no_pr,
				c.id as no_pengajuan,
				d.keterangan as project,
				c.created_by as booking_by,
				"" as booking_by_name,
				DATE_FORMAT(c.created_date, "%d %M %Y") AS booking_date,
				"0" AS pr_non_depart, 
				"0" AS pr_depart,
				"1" AS pr_asset
			FROM
				tran_pr_header c
				LEFT JOIN asset_planning d ON d.no_pr = c.no_pr
			WHERE
				c.app_status_3 = "Y" AND
				c.metode_pembelian = "1" AND
				c.close_pr IS NULL

			ORDER BY booking_date DESC
			')->result();
		$this->template->set('list_data', $data);
		$this->template->set('status_filter', $status_filter);

		$this->template->title('List Outstanding PO');
		$this->template->render('index');
	}

	public function add()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		$customers = $this->db->get_where('customer', ['deleted' => 'N'])->result();
		$karyawan = $this->db->get_where('employee', ['deleted_by' => null])->result();
		$mata_uang = $this->db->get_where('master_kurs', ['deleted_by' => null])->result();
		$data = [
			'customers' => $customers,
			'karyawan' => $karyawan,
			'mata_uang' => $mata_uang,
		];
		$this->template->set('results', $data);
		$this->template->title('Purchase Request');
		$this->template->render('Add');
	}
	public function edit()
	{
		$id = $this->uri->segment(3);
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		$head = $this->db->query("SELECT * FROM tr_purchase_request  WHERE no_pr = '$id' ")->result();
		$detail = $this->db->query("SELECT * FROM dt_trans_pr  WHERE no_pr = '$id' ")->result();
		$customers = $this->db->get_where('customer', ['deleted' => 'N'])->result();
		$karyawan = $this->db->get_where('employee', ['deleted_by' => null])->result();
		$mata_uang = $this->db->get_where('master_kurs', ['deleted_by' => null])->result();
		$data = [
			'head' => $head,
			'detail' => $detail,
			'customers' => $customers,
			'karyawan' => $karyawan,
			'mata_uang' => $mata_uang,
		];
		$this->template->set('results', $data);
		$this->template->title('Purchase Request');
		$this->template->render('Edit');
	}
	public function add_pr()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		$customers = $this->Pr_model->get_data('master_customers', 'deleted', $deleted);
		$karyawan = $this->Pr_model->get_data('ms_karyawan', 'deleted', $deleted);
		$mata_uang = $this->Pr_model->get_data('mata_uang', 'deleted' . $deleted);
		$data = [
			'customers' => $customers,
			'karyawan' => $karyawan,
			'mata_uang' => $mata_uang,
		];
		$this->template->set('results', $data);
		$this->template->title('Purchase Request');
		$this->template->render('Addpr');
	}
	public function PrintHeader1($id)
	{
		$this->auth->restrict($this->managePermission);
		$id = $this->uri->segment(3);
		$data['header'] = $this->Pr_model->getHeaderPenawaran($id);
		$data['detail']  = $this->Pr_model->PrintDetail($id);
		$this->load->view('PrintHeader', $data);
	}
	public function PrintHeader($id)
	{
		ob_clean();
		ob_start();
		$this->auth->restrict($this->managePermission);
		$id = $this->uri->segment(3);
		$data['header'] = $this->Pr_model->getHeaderPenawaran($id);
		$data['detail']  = $this->Pr_model->PrintDetail($id);
		$this->load->view('PrintHeader', $data);
		$html = ob_get_contents();

		require_once('./assets/html2pdf/html2pdf/html2pdf.class.php');
		$html2pdf = new HTML2PDF('P', 'A4', 'en', true, 'UTF-8', array(0, 0, 0, 0));
		$html2pdf->pdf->SetDisplayMode('fullpage');
		$html2pdf->WriteHTML($html);
		ob_end_clean();
		$html2pdf->Output('Penawaran.pdf', 'I');
	}
	public function EditHeader($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		$head = $this->Pr_model->get_data('tr_penawaran', 'no_penawaran', $id);
		$customers = $this->Pr_model->get_data('master_customers', 'deleted', $deleted);
		$karyawan = $this->Pr_model->get_data('ms_karyawan', 'deleted', $deleted);
		$mata_uang = $this->Pr_model->get_data('mata_uang', 'deleted', $deleted);
		$data = [
			'customers' => $customers,
			'karyawan' => $karyawan,
			'head' => $head,
			'mata_uang' => $mata_uang,
		];
		$this->template->set('results', $data);
		$this->template->title('Add Penawaran');
		$this->template->render('EditHeader');
	}
	public function detail()
	{
		$id = $this->uri->segment(3);
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$deleted = '0';
		$detail = $this->Pr_model->getpenawaran($id);
		$header = $this->Pr_model->getHeaderPenawaran($id);
		$data = [
			'detail' => $detail,
			'header' => $header
		];
		$this->template->set('results', $data);
		$this->template->title('Penawaran');
		$this->template->render('detail');
	}

	public function editPenawaran($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$penawaran = $this->Pr_model->get_data('child_penawaran', 'id_child_penawaran', $id);
		$inventory_3 = $this->Pr_model->get_data_category();
		$data = [
			'penawaran' => $penawaran,
			'inventory_3' => $inventory_3,
		];
		$this->template->set('results', $data);
		$this->template->title('Edit Penawaran');
		$this->template->render('editPenawaran');
	}



	public function View($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$header = $this->db->query("SELECT a.*, c.nm_customer, b.nm_lengkap AS nama_user FROM material_planning_base_on_produksi a LEFT JOIN users b ON b.id_user = a.booking_by LEFT JOIN customer c ON c.id_customer = a.id_customer WHERE a.no_pr = '" . $id . "' ")->result();
		$detail     = $this->db
			->select('a.*, IF(b.nama IS NULL, e.stock_name, b.nama) as nm_product, if(b.code IS NULL, e.id_stock, b.code) as code_material, b.konversi as qty_pack, b.max_stok, b.min_stok, if(c.code IS NULL, f.code, c.code) as satuan_packing, if(d.code IS NULL, g.code, d.code) as unit_measure, h.category')
			->from('material_planning_base_on_produksi_detail a')
			->join('new_inventory_4 b', 'a.id_material=b.code_lv4', 'left')
			->join('ms_satuan c', 'c.id = b.id_unit_packing', 'left')
			->join('ms_satuan d', 'd.id = b.id_unit', 'left')
			->join('accessories e', 'e.id = a.id_material', 'left')
			->join('ms_satuan f', 'f.id = e.id_unit_gudang', 'left')
			->join('ms_satuan g', 'g.id = e.id_unit', 'left')
			->join('material_planning_base_on_produksi h', 'h.so_number = a.so_number', 'left')
			->where('a.so_number', $header[0]->so_number)
			->where('a.status_app', 'Y')
			->get()
			->result_array();
		$data = [
			'header' => $header,
			'detail' => $detail
		];
		$this->template->set('results', $data);
		$this->template->title('View P.R');
		$this->template->render('View');
	}

	public function view_asset($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$header = $this->db->query("SELECT a.*, c.nm_customer, b.nm_lengkap AS nama_user FROM material_planning_base_on_produksi a LEFT JOIN users b ON b.id_user = a.booking_by LEFT JOIN customer c ON c.id_customer = a.id_customer WHERE a.no_pr = '" . $id . "' ")->result();
		$detail     = $this->db
			->select('a.*, IF(b.nama IS NULL, e.stock_name, b.nama) as nm_product, if(b.code IS NULL, e.id_stock, b.code) as code_material, b.konversi as qty_pack, b.max_stok, b.min_stok, if(c.code IS NULL, f.code, c.code) as satuan_packing, if(d.code IS NULL, g.code, d.code) as unit_measure, h.category')
			->from('material_planning_base_on_produksi_detail a')
			->join('new_inventory_4 b', 'a.id_material=b.code_lv4', 'left')
			->join('ms_satuan c', 'c.id = b.id_unit_packing', 'left')
			->join('ms_satuan d', 'd.id = b.id_unit', 'left')
			->join('accessories e', 'e.id = a.id_material', 'left')
			->join('ms_satuan f', 'f.id = e.id_unit_gudang', 'left')
			->join('ms_satuan g', 'g.id = e.id_unit', 'left')
			->join('material_planning_base_on_produksi h', 'h.so_number = a.so_number', 'left')
			->where('a.so_number', $header[0]->so_number)
			->where('a.status_app', 'Y')
			->get()
			->result_array();
		$data = [
			'header' => $header,
			'detail' => $detail
		];
		$this->template->set('results', $data);
		$this->template->title('View P.R');
		$this->template->render('view_asset');
	}

	public function View_depart($id)
	{
		$data_Group	= $this->db->get('groups')->result();
		$header 	= $this->db->query("SELECT a.*, b.nm_lengkap as nm_user FROM rutin_non_planning_header a LEFT JOIN users b ON b.id_user = a.created_by WHERE a.no_pr = '" . $id . "' ")->result();
		$detail 	= $this->db->query("SELECT * FROM rutin_non_planning_detail WHERE no_pr = '" . $id . "' ")->result_array();
		$datacoa 	= $this->db->query("SELECT a.coa,b.nama FROM coa_category a join " . DBACC . ".coa_master b on a.coa=b.no_perkiraan WHERE a.tipe='NONRUTIN' order by a.coa")->result_array();
		$satuan		= $this->db->get_where('ms_satuan', array('deleted' => 'N'))->result_array();
		$tanda 		= (!empty($header)) ? 'Edit' : 'Add';
		if (!empty($approve)) {
			$tanda 		= ($approve == 'view') ? 'View' : 'Approve';
		}

		// print_r($header);
		// exit;

		$get_departement = $this->db->get('ms_department')->result();
		$data = array(
			'title'				=> $tanda . ' PR Departemen',
			'action'		=> strtolower($tanda),
			'header'		=> $header,
			'detail'		=> $detail,
			'datacoa'		=> $datacoa,
			'satuan'		=> $satuan,
			'id'			=> $id,
			'list_departement' => $get_departement
		);

		$this->template->set($data);
		$this->template->render('view_depart');
	}

	public function viewPenawaran($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$penawaran = $this->Pr_model->get_data('child_penawaran', 'id_child_penawaran', $id);
		$inventory_3 = $this->Pr_model->get_data_category();
		$data = [
			'penawaran' => $penawaran,
			'inventory_3' => $inventory_3,
		];
		$this->template->set('results', $data);
		$this->template->title('Edit Penawaran');
		$this->template->render('viewPenawaran');
	}

	public function viewBentuk($id)
	{
		$this->auth->restrict($this->viewPermission);
		$id 	= $this->input->post('id');
		$bentuk = $this->db->get_where('ms_bentuk', array('id_bentuk' => $id))->result();
		$dimensi = $this->Bentuk_model->getDimensi($id);
		$data = [
			'bentuk' => $bentuk,
			'dimensi' => $dimensi,
		];
		$this->template->set('results', $data);
		$this->template->render('view_bentuk');
	}


	public function addPenawaran($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$headpenawaran = $this->Pr_model->get_data('tr_penawaran', 'no_penawaran', $id);
		$inventory_3 = $this->Pr_model->get_data_category();
		$data = [
			'inventory_3' => $inventory_3,
			'headpenawaran' => $headpenawaran
		];
		$this->template->set('results', $data);
		$this->template->title('Add Penawaran');
		$this->template->render('AddPenawaran');
	}

	function AddMaterial()
	{
		$loop = $_GET['jumlah'] + 1;
		$material = $this->db->query("SELECT a.* FROM new_inventory_4 as a WHERE a.category = 'material' AND a.deleted_by IS NULL")->result();
		$list_supplier = $this->db->get_where('new_supplier', ['deleted_by' => null])->result();
		echo "
		<tr id='tr_$loop'>
		<td><select class='form-control select2' id='dt_idmaterial_$loop' name='dt[$loop][idmaterial]'	onchange ='CariProperties($loop)'>
		<option value=''>Pilih</option>";
		foreach ($material as $material) {

			echo "<option value='$material->code_lv4'>$material->nama</option>";
		};
		echo "</select></td>
		<td hidden id='bentuk_" . $loop . "'><input readonly type='text' class='form-control autoNumeric text-right' id='dt_bentuk_$loop' required name='dt[$loop][bentuk]' ></td>
		<td id='idbentuk_" . $loop . "' hidden><input readonly type='number' class='form-control' id='dt_idbentuk_$loop' required name='dt[$loop][idbentuk]' ></td>
		<td id='kodeproduk_" . $loop . "'><input readonly type='text' class='form-control' id='dt_kodeproduk_$loop' required name='dt[$loop][kodeproduk]' ></td>
		
		<td hidden><input type='text' class='form-control autoNumeric text-right' id='dt_odameter_$loop' 			required name='dt[$loop][odameter]' 	></td>
		<td><input type='number' class='form-control' id='dt_qty_$loop' 			required name='dt[$loop][qty]' ></td>
		<td hidden><input type='number' class='form-control maskMoney' id='dt_weight_$loop' 			required name='dt[$loop][weight]' 	onkeyup='HitungTweight(" . $loop . ")'></td>
		<td id='HasilTwight_" . $loop . "' hidden><input type='text' class='form-control autoNumeric text-right' id='dt_totalweight_$loop' 	required name='dt[$loop][totalweight]' ></td>
		<td hidden><input type='text' class='form-control text-right autoNumeric' id='dt_width_$loop' 	required name='dt[$loop][width]' ></td>
		<td hidden><input type='text' class='form-control text-right autoNumeric' id='dt_length_$loop' 	required name='dt[$loop][length]' ></td>
		<td id='supplier_" . $loop . "'><select class='form-control select3' id='dt_suplier_$loop' name='dt[$loop][suplier]'>
			<option value=''>- Select Supplier -</option>
		";

		foreach ($list_supplier as $supplier) {
			echo "<option value='" . $supplier->id . "'>" . $supplier->nama . "</option>";
		}

		echo "
		</select></td>
		<td><input type='text' class='form-control datepicker' readonly id='dt_tanggal_$loop' 	required name='dt[$loop][tanggal]' 	></td>
		<td><input type='text' class='form-control' id='dt_keterangan_$loop' 	required name='dt[$loop][keterangan]' 	></td>
		<td><button type='button' class='btn btn-sm btn-danger' title='Hapus Data' data-role='qtip' onClick='return HapusItem($loop);'><i class='fa fa-close'></i></button></td>
		</tr>
		";
	}
	function CariBentuk()
	{
		$id_category3 = $_GET['idmaterial'];
		$loop = $_GET['id'];
		$kategory3	= $this->db->query("SELECT * FROM ms_inventory_category3 WHERE id_category3 = '$id_category3' ")->result();
		$id_bentuk = $kategory3[0]->id_bentuk;
		$bentukquery	= $this->db->query("SELECT * FROM ms_bentuk WHERE id_bentuk = '$id_bentuk' ")->result();
		$bentuk_material = $bentukquery[0]->nm_bentuk;
		echo "<input readonly type='text' class='form-control' value='" . $bentuk_material . "' id='dt_bentuk_" . $loop . "' required name='dt[" . $loop . "][bentuk]' >";
	}
	function CariIdBentuk()
	{
		$id_category3 = $_GET['idmaterial'];
		$loop = $_GET['id'];
		$kategory3	= $this->db->query("SELECT * FROM ms_inventory_category3 WHERE id_category3 = '$id_category3' ")->result();
		$id_bentuk = $kategory3[0]->id_bentuk;
		echo "<input readonly type='text' class='form-control' value='" . $id_bentuk . "' id='dt_idbentuk_" . $loop . "' required name='dt[" . $loop . "][idbentuk]' >";
	}
	function CariSupplier()
	{
		$id_category3 = $_GET['idmaterial'];
		$loop = $_GET['id'];
		$supplier	= $this->db->query("SELECT a.* FROM  master_supplier a")->result();
		echo "<select class='form-control select2' id='dt_suplier_" . $loop . "' name='dt[" . $loop . "][suplier]'>
		<option value=''>Pilih</option>";
		foreach ($supplier as $supplier) {
			echo "<option value='" . $supplier->id_suplier . "'>" . $supplier->name_suplier . "</option>";
		}
		echo "</select>";
	}
	function getemail()
	{
		$id_customer = $_GET['id_customer'];
		$kategory3	= $this->db->query("SELECT * FROM master_customers WHERE id_customer = '$id_customer' ")->result();
		$thickness = $kategory3[0]->email;
		echo "<input type='email' class='form-control' id='email_customer' value='$thickness' required name='email_customer' >";
	}
	function HitungTwight()
	{
		$loop = $_GET['id'];
		$dt_qty = $_GET['dt_qty'];
		$dt_weight = $_GET['dt_weight'];
		$totalweight = $dt_qty * $dt_weight;
		echo "<input type='number' value='" . $totalweight . "' class='form-control' id='dt_totalweight_$loop' 	required name='dt[$loop][totalweight]' >";
	}
	function getsales()
	{
		$id_customer = $_GET['id_customer'];
		$kategory3	= $this->db->query("SELECT * FROM master_customers WHERE id_customer = '$id_customer' ")->result();
		$id_karyawan = $kategory3[0]->id_karyawan;
		$karyawan	= $this->db->query("SELECT * FROM ms_karyawan WHERE id_karyawan = '$id_karyawan' ")->result();
		$nama_karyawan = $karyawan[0]->nama_karyawan;
		echo "	<div class='col-md-8' hidden>
					<input type='text' class='form-control' id='nama_sales' value='$id_karyawan' required name='nama_sales' readonly placeholder='Sales Marketing'>
				</div>
				<div class='col-md-8'>
					<input type='text' class='form-control' id='id_sales' value='$nama_karyawan'  required name='id_sales' readonly placeholder='Sales Marketing'>
				</div>";
	}
	function getpic()
	{
		$id_customer = $_GET['id_customer'];
		$kategory3	= $this->db->query("SELECT * FROM child_customer_pic WHERE id_customer = '$id_customer' ")->result();
		echo "<select id='pic_customer' name='pic_customer' class='form-control select' required>
				<option value=''>--Pilih--</option>";
		foreach ($kategory3 as $pic) {
			echo "<option value='$pic->name_pic'>$pic->name_pic</option>";
		}
		echo "</select>";
	}

	function cari_thickness()
	{
		$id_category3 = $_GET['id_category3'];
		$kategory3	= $this->db->query("SELECT * FROM child_inven_dimensi WHERE id_category3 = '$id_category3' ")->result();
		$thickness = $kategory3[0]->nilai_dimensi;
		echo "<input type='text' class='form-control' readonly id='thickness' value='$thickness' required name='thickness' placeholder='Bentuk Material'>";
	}
	function cari_density()
	{
		$id_category3 = $_GET['id_category3'];
		$kategory3	= $this->db->query("SELECT * FROM ms_inventory_category3 WHERE id_category3 = '$id_category3' ")->result();
		$density = $kategory3[0]->density;
		echo "<input type='text' class='form-control' readonly id='density' value='$density' required name='density' placeholder='Bentuk Material'>";
	}
	function hitung_komisi()
	{
		$bottom = $_GET['bottom'];
		$komisi = $_GET['bottom'] * $_GET['komisi'] / 100;
		$profit = $_GET['bottom'] * $_GET['profit'] / 100;
		$hasil = $bottom + $komisi + $profit;
		echo "<input type='text' class='form-control' value='$hasil' id='harga_penawaran'  required name='harga_penawaran' placeholder='Bentuk Material'>";
	}

	function CariKodeproduk()
	{
		$id_category3 = $_GET['idmaterial'];
		$loop = $_GET['id'];
		$kategory3	= $this->db->query("SELECT * FROM new_inventory_4 WHERE code_lv4 = '$id_category3' ")->result();
		$id_bentuk = $kategory3[0]->code;
		echo "<input readonly type='text' class='form-control' value='" . $id_bentuk . "' id='dt_kodeproduk_" . $loop . "' required name='dt[" . $loop . "][kodeproduk]' >";
	}
	function carimsprofit()
	{
		$density = $_GET['density'];
		$inven1 = $_GET['inven1'];
		$thickness = $_GET['thickness'];
		$width = $_GET['width'];
		$berat = $_GET['forecast'];
		$maxprofit	= $this->db->query("SELECT max(maksimum) as maximum FROM ms_profit_material WHERE alloy = '$inven1' ")->result();
		$nilaimax = $maxprofit[0]->maximum;

		if ($berat > $nilaimax) {
			$profitaa	= $this->db->query("SELECT * FROM ms_profit_material WHERE alloy = '$inven1' AND minimum < '$berat' AND maksimum  IS NULL   ")->result();
			$nilai_profit = $profitaa[0]->profit;
			$aaa = huhu;
		} else {
			$profitaa	= $this->db->query("SELECT * FROM ms_profit_material WHERE  alloy = '$inven1' AND minimum < '$berat' AND maksimum >= '$berat'  ")->result();
			$nilai_profit = $profitaa[0]->profit;
			$aaa = hihi;
		}
		echo "$nilai_profit %";
	}
	function cari_inven1()
	{
		$id_category3 = $_GET['id_category3'];
		$kategory3	= $this->db->query("SELECT * FROM ms_inventory_category3 WHERE id_category3 = '$id_category3' ")->result();
		$inven1 = $kategory3[0]->id_category1;
		echo "<input type='text' class='form-control' id='inven1' value='$inven1'  required name='inven1' placeholder='Bentuk Material'>";
	}
	public function delDetail()
	{
		$this->auth->restrict($this->deletePermission);
		$id = $this->input->post('id');
		// print_r($id);
		// exit();
		$data = [
			'deleted' 		=> '1',
			'deleted_by' 	=> $this->auth->user_id()
		];

		$this->db->trans_begin();
		$this->db->where('id_dimensi', $id)->update("ms_dimensi", $data);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Item. Thanks ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. Thanks ...',
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}

	public function Approved()
	{
		$this->auth->restrict($this->deletePermission);
		$id = $this->input->post('id');
		$data = [
			'status' 		=> '2',
		];

		$this->db->trans_begin();
		$this->db->where('no_pr', $id)->update("tr_purchase_request", $data);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Approve P.R. Thanks ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Approve P.R. Thanks ...',
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}
	function get_inven2()
	{
		$inventory_1 = $_GET['inventory_1'];
		$data = $this->Pr_model->level_2($inventory_1);
		echo "<select id='inventory_2' name='hd1[1][inventory_2]' class='form-control onchange='get_inv3()'  input-sm select2'>";
		echo "<option value=''>--Pilih--</option>";
		foreach ($data as $key => $st) :
			echo "<option value='$st->id_category1' set_select('inventory_2', $st->id_category1, isset($data->id_category1) && $data->id_category1 == $st->id_category1)>$st->nama
                    </option>";
		endforeach;
		echo "</select>";
	}
	function get_inven3()
	{
		$inventory_2 = $_GET['inventory_2'];
		$data = $this->Pr_model->level_3($inventory_2);

		// print_r($data);
		// exit();
		echo "<select id='inventory_3' name='hd1[1][inventory_3]' class='form-control input-sm select2'>";
		echo "<option value=''>--Pilih--</option>";
		foreach ($data as $key => $st) :
			echo "<option value='$st->id_category2' set_select('inventory_3', $st->id_category2, isset($data->id_category2) && $data->id_category2 == $st->id_category2)>$st->nama
                    </option>";
		endforeach;
		echo "</select>";
	}
	public function saveNewPenawaran()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$this->db->trans_begin();
		$hariini = date('Y-m-d');
		$sepuluh_hari = mktime(0, 0, 0, date('n'), date('j') - 10, date('Y'));
		$tendays = date("Y-m-d", $sepuluh_hari);
		$sebulan = mktime(0, 0, 0, date('n'), date('j') - 30, date('Y'));
		$tirtydays = date("Y-m-d", $sebulan);
		$tglnow = date('d');
		$blnnow = date('m');
		if ($blnnow != '1') {
			$blnkmrn = $blnnow - 1;
			$yearkemaren = date('Y');
		} else {
			$blnkmrn = "12";
			$yearnow = date('Y');
			$yearkemaren = $yearnow - 1;
		}
		$kurs_terpakai = $post['kurs_terpakai'];
		if ($kurs_terpakai == 'spot') {
			$kurs	= $this->db->query("SELECT * FROM mata_uang WHERE kode = 'IDR' ")->result();
			$nominal = $kurs[0]->kurs;
		} elseif ($kurs_terpakai == '10') {
			$kurs	= $this->db->query("SELECT AVG(nominal) as nominal FROM perubahan_kurs WHERE tanggal_ubah BETWEEN  '$tendays' AND '$hariini' AND kode_kurs='IDR' ")->result();
			$nominal = $kurs[0]->nominal;
		} elseif ($kurs_terpakai == '30') {
			$kurs	= $this->db->query("SELECT AVG(nominal) as nominal FROM perubahan_kurs WHERE MONTH(tanggal_ubah) =  '$blnkmrn' AND YEAR(tanggal_ubah) = '$yearkemaren' AND kode_kurs='IDR' ")->result();
			$nominal = $kurs[0]->nominal;
		} else {
			$noinal = '1';
		}
		$code = $post['no_penawaran'];
		$dolar = $post['harga_penawaran'] / $nominal;
		$data = [
			'id_child_penawaran'	=> $code,
			'id_category3'			=> $post['id_category3'],
			'no_penawaran'			=> $post['no_penawaran'],
			'bentuk_material'		=> $post['bentuk_material'],
			'id_bentuk'				=> $post['id_bentuk'],
			'thickness'				=> $post['thickness'],
			'density'				=> $post['density'],
			'lotno'					=> $post['lotno'],
			'width'					=> $post['width'],
			'forecast'				=> $post['forecast'],
			'kurs_terpakai'				=> $post['kurs_terpakai'],
			'inven1'				=> $post['inven1'],
			'bottom'				=> $post['bottom'],
			'dasar_harga'			=> $post['dasar_harga'],
			'komisi'				=> $post['komisi'],
			'profit'				=> $post['profit'],
			'keterangan'			=> $post['keterangan'],
			'harga_penawaran'		=> $post['harga_penawaran'],
			'harga_dolar'			=> $dolar,
			'created_on'			=> date('Y-m-d H:i:s'),
			'created_by'			=> $this->auth->user_id()
		];
		//Add Data
		$this->db->insert('child_penawaran', $data);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Item. Thanks ...',
				'code' => $code,
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. invenThanks ...',
				'code' => $code,
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}


	public function SaveNew()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();

		$tgl  = $post['tanggal'];

		$code = $this->Pr_model->generate_code($tgl);
		$no_surat = $this->Pr_model->BuatNomor($tgl);
		$this->db->trans_begin();
		$data = [
			'no_pr'				=> $code,
			'no_surat'			=> $no_surat,
			'tanggal'			=> $post['tanggal'],
			'requestor'			=> $post['requestor'],
			'status'			=> '1',
			'created_on'		=> date('Y-m-d H:i:s'),
			'created_by'		=> $this->auth->user_id()
		];
		//Add Data
		$this->db->insert('tr_purchase_request', $data);

		$numb1 = 0;
		foreach ($_POST['dt'] as $used) {
			$numb1++;

			$idmat  = $used[idmaterial];
			$materials = $this->db->query("SELECT a.* FROM ms_inventory_category3 a WHERE a.id_category3 ='$idmat' ")->row();


			$dt =  array(
				'no_pr'					=> $code,
				'id_dt_pr'				=> $code . '-' . $numb1,
				'idameter'			=> $used[idameter],
				'odameter'			=> str_replace(',', '', $used[odameter]),
				'idmaterial'			=> $used[idmaterial],
				'nama_material'			=> $materials->nama,
				'bentuk'				=> $used[bentuk],
				'id_bentuk'				=> $used[idbentuk],
				'qty'			=> $used[qty],
				'weight'			=> $used[weight],
				'totalweight'			=> str_replace(',', '', $used[totalweight]),
				'width'			=> str_replace(',', '', $used[width]),
				'length'			=> str_replace(',', '', $used[length]),
				'suplier'			=> $used[suplier],
				'tanggal'			=> $used[tanggal],
				'keterangan'			=> $used[keterangan],
				'kode_barang'			=> $used[kodeproduk],
			);
			$this->db->insert('dt_trans_pr', $dt);
		}
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Item. Thanks ...',
				'code' => $code,
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. invenThanks ...',
				'code' => $code,
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}
	public function SaveEdit()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();

		// print_r($post);
		// exit;

		$code = $post['no_pr'];
		$no_surat = $post['no_surat'];
		$this->db->trans_begin();
		$data = [
			'no_pr'				=> $code,
			'no_surat'			=> $no_surat,
			'tanggal'			=> $post['tanggal'],
			'requestor'			=> $post['requestor'],
			'status'			=> '1',
			'created_on'		=> date('Y-m-d H:i:s'),
			'created_by'		=> $this->auth->user_id()
		];
		//Add Data
		$this->db->where('no_pr', $code)->update("tr_purchase_request", $data);
		$this->db->delete('dt_trans_pr', array('no_pr' => $code));

		$numb1 = 0;
		foreach ($_POST['dt'] as $used) {
			$numb1++;

			$idmat  = $used[idmaterial];
			$matrial = $this->db->query("SELECT a.* FROM new_inventory_4 a WHERE a.code_lv4 ='$idmat' ")->row();

			$dt =  array(
				'no_pr'					=> $code,
				'id_dt_pr'				=> $code . '-' . $numb1,
				'idmaterial'			=> $used[idmaterial],
				'nama_material'			=> $matrial->nama,
				'qty'				=> $used[qty],
				'weight'			=> $used[weight],
				'totalweight'		=> str_replace(',', '', $used[totalweight]),
				'width'			=> str_replace(',', '', $used[width]),
				'length'			=> str_replace(',', '', $used[length]),
				'suplier'			=> $used[suplier],
				'tanggal'			=> $used[tanggal],
				'keterangan'		=> $used[keterangan],
				'kode_barang'		=> $matrial->code,
			);
			$this->db->insert('dt_trans_pr', $dt);
		}
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Item. Thanks ...',
				'code' => $code,
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. invenThanks ...',
				'code' => $code,
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}
	public function SaveEditHeader()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$code		= $post['no_penawaran'];
		$no_surat	= $post['no_surat'];
		$this->db->trans_begin();
		$data = [
			'no_surat'				=> $no_surat,
			'tgl_penawaran'			=> date('Y-m-d'),
			'id_customer'			=> $post['id_customer'],
			'pic_customer'			=> $post['pic_customer'],
			'mata_uang'			=> $post['mata_uang'],
			'email_customer'		=> $post['email_customer'],
			'valid_until'			=> $post['valid_until'],
			'pengiriman'			=> $post['pengiriman'],
			'terms_payment'			=> $post['terms_payment'],
			'exclude_vat'			=> $post['exclude_vat'],
			'note'					=> $post['note'],
			'id_sales'				=> $post['id_sales'],
			'nama_sales'			=> $post['nama_sales'],
			'created_on'			=> date('Y-m-d H:i:s'),
			'created_by'			=> $this->auth->user_id()
		];
		//Add Data
		$this->db->where('no_penawaran', $code)->update("tr_penawaran", $data);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Item. Thanks ...',
				'code' => $code,
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. invenThanks ...',
				'code' => $code,
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}
	public function saveEditPenawaran()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();

		$this->db->trans_begin();
		$hariini = date('Y-m-d');
		$sepuluh_hari = mktime(0, 0, 0, date('n'), date('j') - 10, date('Y'));
		$tendays = date("Y-m-d", $sepuluh_hari);
		$sebulan = mktime(0, 0, 0, date('n'), date('j') - 30, date('Y'));
		$tirtydays = date("Y-m-d", $sebulan);
		$tglnow = date('d');
		$blnnow = date('m');
		if ($blnnow != '1') {
			$blnkmrn = $blnnow - 1;
			$yearkemaren = date('Y');
		} else {
			$blnkmrn = "12";
			$yearnow = date('Y');
			$yearkemaren = $yearnow - 1;
		}
		$kurs_terpakai = $post['kurs_terpakai'];
		if ($kurs_terpakai == 'spot') {
			$kurs	= $this->db->query("SELECT * FROM mata_uang WHERE kode = 'IDR' ")->result();
			$nominal = $kurs[0]->kurs;
		} elseif ($kurs_terpakai == '10') {
			$kurs	= $this->db->query("SELECT AVG(nominal) as nominal FROM perubahan_kurs WHERE tanggal_ubah BETWEEN  '$tendays' AND '$hariini' AND kode_kurs='IDR' ")->result();
			$nominal = $kurs[0]->nominal;
		} elseif ($kurs_terpakai == '30') {
			$kurs	= $this->db->query("SELECT AVG(nominal) as nominal FROM perubahan_kurs WHERE MONTH(tanggal_ubah) =  '$blnkmrn' AND YEAR(tanggal_ubah) = '$yearkemaren' AND kode_kurs='IDR' ")->result();
			$nominal = $kurs[0]->nominal;
		} else {
			$noinal = '1';
		}
		$id = $post['id_child_penawaran'];
		$dolar = $post['harga_penawaran'] / $nominal;
		$data = [
			'id_category3'			=> $post['id_category3'],
			'bentuk_material'		=> $post['bentuk_material'],
			'id_bentuk'				=> $post['id_bentuk'],
			'thickness'				=> $post['thickness'],
			'density'				=> $post['density'],
			'forecast'				=> $post['forecast'],
			'inven1'				=> $post['inven1'],
			'bottom'				=> $post['bottom'],
			'dasar_harga'			=> $post['dasar_harga'],
			'komisi'				=> $post['komisi'],
			'profit'				=> $post['profit'],
			'kurs_terpakai'			=> $post['kurs_terpakai'],
			'keterangan'			=> $post['keterangan'],
			'harga_penawaran'		=> $post['harga_penawaran'],
			'harga_dolar'			=> $dolar,
			'created_on'			=> date('Y-m-d H:i:s'),
			'created_by'			=> $this->auth->user_id()
		];
		//Add Data
		$this->db->where('id_child_penawaran', $id)->update("child_penawaran", $data);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Item. Thanks ...',
				'code' => $id_bentuk,
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. invenThanks ...',
				'code' => $id_bentuk,
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}
	public function deletePenawaran()
	{
		$this->auth->restrict($this->deletePermission);
		$id = $this->input->post('id');
		$this->db->trans_begin();
		$this->db->delete('child_penawaran', array('id_child_penawaran' => $id));

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Item. Thanks ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. Thanks ...',
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}

	public function saveEditInventory()
	{
		$this->auth->restrict($this->addPermission);
		$session = $this->session->userdata('app_session');
		$code = $this->Pr_model->generate_id();
		$this->db->trans_begin();
		$id = $_POST['hd1']['1']['id_category3'];
		$id_bentuk = $_POST['hd1']['1']['id_bentuk'];
		$numb1 = 0;
		foreach ($_POST['hd1'] as $h1) {
			$numb1++;
			$header1 =  array(
				'id_type'		        => $h1[inventory_1],
				'id_category1'		    => $h1[inventory_2],
				'id_category2'		    => $h1[inventory_3],
				'nama'		        	=> $h1[nm_inventory],
				'maker'		        	=> $h1[maker],
				'density'		        => $h1[density],
				'hardness'		        => $h1[hardness],
				'id_bentuk'		        => $h1[id_bentuk],
				'id_surface'		    => $h1[id_surface],
				'mountly_forecast'		=> $h1[mountly_forecast],
				'safety_stock'		    => $h1[safety_stock],
				'order_point'		    => $h1[order_point],
				'maksimum'		    	=> $h1[maksimum],
				'aktif'					=> 'aktif',
				'created_on'		=> date('Y-m-d H:i:s'),
				'created_by'		=> $this->auth->user_id(),
				'deleted'			=> '0'
			);
			$this->db->where('id_category3', $id)->update("ms_inventory_category3", $header1);
		}

		if (empty($_POST['data1'])) {
		} else {
			$this->db->delete('child_inven_suplier', array('id_category3' => $id));
			$numb2 = 0;

			foreach ($_POST['data1'] as $d1) {
				$numb2++;
				$data1 =  array(
					'id_category3' => $id,
					'id_suplier' => $d1[id_supplier],
					'lead' => $d1[lead],
					'minimum' => $d1[minimum],
					'deleted' => '0',
					'created_on' => date('Y-m-d H:i:s'),
					'created_by' => $this->auth->user_id(),
				);
				//Add Data
				$this->db->insert('child_inven_suplier', $data1);
			}
		}

		if (empty($_POST['compo'])) {
		} else {
			$this->db->delete('child_inven_compotition', array('id_category3' => $id));
			$numb3 = 0;
			foreach ($_POST['compo'] as $c1) {
				$numb3++;
				$comp =  array(
					'id_category3' => $id,
					'id_compotition' => $c1[id_compotition],
					'nilai_compotition' => $c1[jumlah_kandungan],
					'deleted' => '0',
					'created_on' => date('Y-m-d H:i:s'),
					'created_by' => $this->auth->user_id(),
				);
				//Add Data
				$this->db->insert('child_inven_compotition', $comp);
			}
		}

		if (empty($_POST['dimens'])) {
		} else {
			$this->db->delete('child_inven_dimensi', array('id_category3' => $id));
			$numb4 = 0;
			foreach ($_POST['dimens'] as $dm) {
				$numb4++;
				$dms =  array(
					'id_category3' => $id,
					'id_dimensi' => $dm[id_dimensi],
					'nilai_dimensi' => $dm[nilai_dimensi],
					'deleted' => '0',
					'created_on' => date('Y-m-d H:i:s'),
					'created_by' => $this->auth->user_id(),
				);
				//Add Data
				$this->db->insert('child_inven_dimensi', $dms);
			}
		}
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Item. Thanks ...',
				'code' => $id_bentuk,
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. invenThanks ...',
				'code' => $id_bentuk,
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}
	function get_compotition_new()
	{
		$inventory_2 = $_GET['inventory_2'];
		$comp = $this->Pr_model->compotition($inventory_2);
		$numb = 0;
		// print_r($data);
		// exit();
		foreach ($comp as $key => $cmp) : $numb++;
			echo "<tr>
					  <td hidden align='left'>
					  <input type='text' name='compo[$numb][id_compotition]' readonly class='form-control'  value='$cmp->id_compotition'>
					  </td>
					  <td align='left'>
					  $cmp->name_compotition
					  </td>
					  <td align='left'>
					  <input type='text' name='compo[$numb][jumlah_kandungan]' class='form-control'>
					  </td>
					  <td align='left'>%</td>
                    </tr>";
		endforeach;
		echo "</select>";
	}
	function get_dimensi()
	{
		$id_bentuk = $_GET['id_bentuk'];
		$dim = $this->Pr_model->bentuk($id_bentuk);
		$numb = 0;
		// print_r($data);
		// exit();
		foreach ($dim as $key => $ensi) : $numb++;
			echo "<tr>
					  <td align='left' hidden>
					  <input type='text' name='dimens[$numb][id_dimensi]' readonly class='form-control'  value='$ensi->id_dimensi'>
					  </td>
					  <td align='left'>
					  $ensi->nm_dimensi
					  </td>
					  <td align='left'>
					  <input type='text' name='dimens[$numb][nilai_dimensi]' class='form-control'>
					  </td>
                    </tr>";
		endforeach;
		echo "</select>";
	}
	function get_compotition_old()
	{
		$inventory_2 = $_GET['inventory_2'];
		$comp = $this->Pr_model->compotition_edit($inventory_2);
		$numb = 0;
		// print_r($data);
		// exit();
		foreach ($comp as $key => $cmp) : $numb++;
			echo "<tr>
					  <td hidden align='left'>
					  <input type='text' name='compo[$numb][id_compotition]' readonly class='form-control'  value='$cmp->id_compotition'>
					  </td>
					  <td align='left'>
					  $cmp->name_compotition
					  </td>
					  <td align='left'>
					  <input type='text' name='compo[$numb][jumlah_kandungan]' class='form-control'>
					  </td>
					  <td align='left'>%</td>
                    </tr>";
		endforeach;
		echo "</select>";
	}
	function get_dimensi_old()
	{
		$id_bentuk = $_GET['id_bentuk'];
		$dim = $this->Pr_model->bentuk_edit($id_bentuk);
		$numb = 0;
		// print_r($data);
		// exit();
		foreach ($dim as $key => $ensi) : $numb++;
			echo "<tr>
					  <td hidden align='left'>
					  <input type='text' name='dimens[$numb][id_dimensi]' readonly class='form-control'  value='$cmp->id_dimensi'>
					  </td>
					  <td align='left'>
					  $ensi->nm_dimensi
					  </td>
					  <td align='left'>
					  <input type='text' name='dimens[$numb][nilai_dimensi]' class='form-control'>
					  </td>
                    </tr>";
		endforeach;
		echo "</select>";
	}
	public function saveEditInventorylama()
	{
		$this->auth->restrict($this->addPermission);
		$session = $this->session->userdata('app_session');
		$code = $this->Pr_model->generate_id();
		$this->db->trans_begin();
		$id = $_POST['hd1']['1']['id_category3'];
		$numb1 = 0;
		//$head = $_POST['hd1'];
		foreach ($_POST['hd1'] as $h1) {
			$numb1++;

			$header1 =  array(
				'id_type'		        => $h1[inventory_1],
				'id_category1'		    => $h1[inventory_2],
				'id_category2'		    => $h1[inventory_3],
				'nama'		        	=> $h1[nm_inventory],
				'maker'		        	=> $h1[maker],
				'density'		        => $h1[density],
				'hardness'		        => $h1[hardness],
				'id_bentuk'		        => $h1[id_bentuk],
				'id_surface'		    => $h1[id_surface],
				'mountly_forecast'		=> $h1[mountly_forecast],
				'safety_stock'		    => $h1[safety_stock],
				'order_point'		    => $h1[order_point],
				'maksimum'		    	=> $h1[maksimum],
				'aktif'					=> 'aktif',
				'created_on'		=> date('Y-m-d H:i:s'),
				'created_by'		=> $this->auth->user_id(),
				'deleted'			=> '0'
			);
			//Add Data
			$this->db->where('id_category3', $id)->update("ms_inventory_category3", $data);
		}
		$this->db->delete('child_inven_suplier', array('id_category3' => $id));
		if (empty($_POST['data1'])) {
		} else {
			$numb2 = 0;
			foreach ($_POST['data1'] as $d1) {
				$numb2++;
				$data1 =  array(
					'id_category3' => $code,
					'id_suplier' => $d1[id_supplier],
					'lead' => $d1[lead],
					'minimum' => $d1[minimum],
					'deleted' => '0',
					'created_on' => date('Y-m-d H:i:s'),
					'created_by' => $this->auth->user_id(),
				);
				//Add Data
				$this->db->insert('child_inven_suplier', $data1);
			}
		}
		if (empty($_POST['compo'])) {
		} else {
			$numb3 = 0;
			foreach ($_POST['compo'] as $c1) {
				$numb3++;
				$comp =  array(
					'id_category3' => $code,
					'id_compotition' => $c1[id_compotition],
					'nilai_compotition' => $c1[jumlah_kandungan],
					'deleted' => '0',
					'created_on' => date('Y-m-d H:i:s'),
					'created_by' => $this->auth->user_id(),
				);
				//Add Data
				$this->db->insert('child_inven_compotition', $comp);
			}
		}
		if (empty($_POST['dimens'])) {
		} else {
			$numb4 = 0;
			foreach ($_POST['dimens'] as $dm) {
				$numb4++;
				$dms =  array(
					'id_category3' => $code,
					'id_dimensi' => $dm[id_dimensi],
					'nilai_dimensi' => $dm[nilai_dimensi],
					'deleted' => '0',
					'created_on' => date('Y-m-d H:i:s'),
					'created_by' => $this->auth->user_id(),
				);
				//Add Data
				$this->db->insert('child_inven_dimensi', $dms);
			}
		}
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Item. Thanks ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. invenThanks ...',
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}
	public function saveEditInventoryOld()
	{
		$this->auth->restrict($this->addPermission);
		$session = $this->session->userdata('app_session');
		$code = $this->Pr_model->generate_id();
		$this->db->trans_begin();
		$id = $_POST['hd1']['1']['id_category3'];
		$numb1 = 0;
		//$head = $_POST['hd1'];
		foreach ($_POST['hd1'] as $h1) {
			$numb1++;

			$header1 =  array(
				'id_type'		        => $h1[inventory_1],
				'id_category1'		    => $h1[inventory_2],
				'id_category2'		    => $h1[inventory_3],
				'nama'		        	=> $h1[nm_inventory],
				'maker'		        	=> $h1[maker],
				'density'		        => $h1[density],
				'hardness'		        => $h1[hardness],
				'id_bentuk'		        => $h1[id_bentuk],
				'id_surface'		    => $h1[id_surface],
				'mountly_forecast'		=> $h1[mountly_forecast],
				'safety_stock'		    => $h1[safety_stock],
				'order_point'		    => $h1[order_point],
				'maksimum'		    	=> $h1[maksimum],
				'aktif'					=> 'aktif',
				'created_on'		=> date('Y-m-d H:i:s'),
				'created_by'		=> $this->auth->user_id(),
				'deleted'			=> '0'
			);
			//Add Data
			$this->db->where('id_category3', $id)->update("ms_inventory_category3", $data);
		}
		if (empty($_POST['data1'])) {
		} else {
			$numb2 = 0;
			foreach ($_POST['data1'] as $d1) {
				$numb2++;
				$data1 =  array(
					'id_category3' => $id,
					'id_suplier' => $d1[id_supplier],
					'lead' => $d1[lead],
					'minimum' => $d1[minimum],
					'deleted' => '0',
					'created_on' => date('Y-m-d H:i:s'),
					'created_by' => $this->auth->user_id(),
				);
				//Add Data
				$this->db->insert('child_inven_suplier', $data1);
			}
		}
		if (empty($_POST['compo'])) {
		} else {
			$numb3 = 0;
			foreach ($_POST['compo'] as $c1) {
				$numb3++;
				$comp =  array(
					'id_category3' => $id,
					'id_compotition' => $c1[id_compotition],
					'nilai_compotition' => $c1[jumlah_kandungan],
					'deleted' => '0',
					'created_on' => date('Y-m-d H:i:s'),
					'created_by' => $this->auth->user_id(),
				);
				//Add Data
				$this->db->insert('child_inven_compotition', $comp);
			}
		}
		if (empty($_POST['dimens'])) {
		} else {
			$numb4 = 0;
			foreach ($_POST['dimens'] as $dm) {
				$numb4++;
				$dms =  array(
					'id_category3' => $id,
					'id_dimensi' => $dm[id_dimensi],
					'nilai_dimensi' => $dm[nilai_dimensi],
					'deleted' => '0',
					'created_on' => date('Y-m-d H:i:s'),
					'created_by' => $this->auth->user_id(),
				);
				//Add Data
				$this->db->insert('child_inven_dimensi', $dms);
			}
		}
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Item. Thanks ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. invenThanks ...',
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}

	function get_compotition()
	{
		$inventory_2 = $_GET['inventory_2'];
		$comp = $this->Pr_model->compotition($inventory_2);
		$numb = 0;
		// print_r($data);
		// exit();
		foreach ($comp as $key => $cmp) : $numb++;
			echo "<tr>
					  <td hidden align='left'>
					  <input type='text' name='compo[$numb][id_compotition]' readonly class='form-control'  value='$cmp->id_compotition'>
					  </td>
					  <td align='left'>
					  $cmp->name_compotition
					  </td>
					  <td align='left'>
					  <input type='text' name='compo[$numb][jumlah_kandungan]' class='form-control'>
					  </td>
					  <td align='left'>%</td>
                    </tr>";
		endforeach;
		echo "</select>";
	}


	public function index_approval()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$data = $this->db->query('
			SELECT a.*
			FROM material_planning_base_on_produksi a
			WHERE 
				a.category="pr material" AND 
				a.booking_date IS NOT NULL AND
				(SELECT COUNT(aa.id) FROM material_planning_base_on_produksi_detail aa WHERE aa.so_number = a.so_number) AND
				a.sts_req_app = "1" AND
				a.app_req = "0"
			ORDER BY a.so_number
		')->result();
		$this->template->set('results', $data);
		$this->template->title('Approval Purchase Request');
		$this->template->render('index_approval');
	}

	public function update_dtpr()
	{

		$dtpr = $this->db->query("SELECT a.* FROM dt_trans_pr as a")->result();
		foreach ($dtpr as $dt) {
			$idmaterial = $dt->idmaterial;
			$material = $this->db->query("SELECT a.* FROM ms_inventory_category3 as a WHERE id_category3='$idmaterial'")->row();
			$nama = $material->kode_barang;
			$this->db->query("UPDATE dt_trans_pr SET kode_barang='$nama' WHERE idmaterial='$idmaterial'");
		};
	}

	public function request_approval()
	{
		$post = $this->input->post();
		$so_number = $post['so_number'];

		$this->db->trans_begin();

		$this->db->update('material_planning_base_on_produksi', [
			'sts_req_app' => 1
		], [
			'so_number' => $so_number
		]);

		if ($this->db->trans_status() === false) {
			$valid = 0;

			$this->db->trans_rollback();
		} else {
			$valid = 1;

			$this->db->trans_commit();
		}

		echo json_encode([
			'status' => $valid
		]);
	}

	public function approval()
	{
		$post = $this->input->post();
		$so_number = $post['so_number'];

		$this->db->trans_begin();

		$this->db->update('material_planning_base_on_produksi', ['sts_req_app' => 1, 'app_req' => 1], ['so_number' => $so_number]);

		if ($this->db->trans_status() === false) {
			$valid = 0;

			$this->db->trans_rollback();
		} else {
			$valid = 1;

			$this->db->trans_commit();
		}

		echo json_encode([
			'status' => $valid
		]);
	}

	public function PrintH2()
	{
		ob_clean();
		ob_start();
		// $this->auth->restrict($this->managePermission);
		$id = $this->uri->segment(3);
		$data['header'] = $this->db->query("SELECT a.*, b.nm_customer, b.alamat, c.name as country_name, d.nm_pic, d.hp, d.email_pic, b.fax, x.category as kategori_pr FROM material_planning_base_on_produksi as a LEFT JOIN material_planning_base_on_produksi x ON x.so_number = a.so_number LEFT JOIN customer b ON b.id_customer = a.id_customer LEFT JOIN country_all c ON c.iso3 = b.country_code LEFT JOIN customer_pic d ON d.id_pic = b.id_pic WHERE a.so_number = '" . $id . "' ")->result();
		$data['detail']  = $this->db->query("SELECT a.*, if(b.code IS NULL, e.id_stock, b.code) as code, if(b.nama IS NULL, e.stock_name, b.nama) as nama, if(b.konversi IS NULL, if(e.konversi <= 0, 1, e.konversi), b.konversi) as konversi, if(c.code IS NULL, f.code, c.code) as satuan, if(d.code IS NULL, g.code, d.code) as satuan_packing FROM material_planning_base_on_produksi_detail a 
		LEFT JOIN new_inventory_4 b ON b.code_lv4 = a.id_material 
		LEFT JOIN ms_satuan c ON c.id = b.id_unit
		LEFT JOIN ms_satuan d ON d.id = b.id_unit_packing
		LEFT JOIN accessories e ON e.id = a.id_material
		LEFT JOIN ms_satuan f ON f.id = e.id_unit
		LEFT JOIN ms_satuan g ON g.id = e.id_unit_gudang
		WHERE a.so_number = '" . $id . "' ")->result();
		// $data['detailsum'] = $this->db->query("SELECT AVG(width) as totalwidth, AVG(qty) as totalqty FROM dt_trans_po WHERE no_po = '" . $id . "' ")->result();
		$this->load->view('Print', $data);
		$html = ob_get_contents();

		// print_r($data['header']);
		// exit;

		require_once('./assets/html2pdf/html2pdf/html2pdf.class.php');
		$html2pdf = new HTML2PDF('P', 'A4', 'en', true, 'UTF-8', array(10, 5, 10, 5));
		$html2pdf->pdf->SetDisplayMode('fullpage');
		$html2pdf->WriteHTML($html);
		ob_end_clean();
		$html2pdf->Output('Purchase Request.pdf', 'I');

		// $this->template->title('Testing');
		// $this->template->render('print2');
	}
}
