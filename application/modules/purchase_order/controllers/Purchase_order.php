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

class Purchase_order extends Admin_Controller
{
	//Permission
	protected $viewPermission 	= 'Purchase_Order.View';
	protected $addPermission  	= 'Purchase_Order.Add';
	protected $managePermission = 'Purchase_Order.Manage';
	protected $deletePermission = 'Purchase_Order.Delete';

	public function __construct()
	{
		parent::__construct();
		$this->load->library(array('upload', 'Image_lib'));
		$this->load->model(array(
			'Purchase_order/Pr_model',
			'Aktifitas/aktifitas_model',
		));
		$this->template->title('Manage Data Supplier');
		$this->template->page_icon('fa fa-building-o');

		date_default_timezone_set('Asia/Bangkok');
	}
	public function index()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$data = $this->db->query("
		SELECT 
			a.*, 
			b.nm_lengkap as nm_create, 
			d.so_number,
			f.no_pr as no_pr_material,
			e.no_pr as no_pr_depart,
			h.nama as nm_supplier,
			IF(SUM(j.jumlahharga) IS NULL, 0, SUM(j.jumlahharga)) as harga_po
		FROM 
			tr_purchase_order as a 
			LEFT JOIN users b ON b.id_user = a.created_by 
			LEFT JOIN dt_trans_po c ON c.no_po = a.no_po 
			LEFT JOIN material_planning_base_on_produksi_detail d ON d.id = c.idpr AND (c.tipe IS NULL OR c.tipe = '')
			LEFT JOIN material_planning_base_on_produksi f ON f.so_number = d.so_number AND (c.tipe IS NULL OR c.tipe = '')
			LEFT JOIN rutin_non_planning_detail e ON e.id = c.idpr AND c.tipe = 'pr depart'
			LEFT JOIN rutin_non_planning_header g ON g.no_pengajuan = e.no_pengajuan
			LEFT JOIN new_supplier h ON h.kode_supplier = a.id_suplier
			LEFT JOIN dt_trans_po j ON j.no_po = a.no_po
		WHERE
			a.close_po IS NULL AND
			(SELECT COUNT(aa.id) FROM dt_trans_po aa WHERE aa.no_po = a.no_po) > 0
		GROUP BY a.no_po
		ORDER BY a.no_po DESC
	")->result();

		$link_no_incoming = [];

		foreach ($data as $item) {
			$incoming_no = [];
			$this->db->select('a.kode_trans');
			$this->db->from('tr_incoming_check a');
			$this->db->like('a.no_ipp', $item->no_po, 'both');
			$get_no_incoming = $this->db->get()->result();

			if (!empty($get_no_incoming)) {
				foreach ($get_no_incoming as $item_incoming) {
					$incoming_no[] = $item_incoming->kode_trans;
				}
				$incoming_no = implode(', ', $incoming_no);
			} else {
				$this->db->select('a.kode_trans');
				$this->db->from('warehouse_adjustment a');
				$this->db->like('a.no_ipp', $item->no_po, 'both');
				$get_no_incoming_warehouse = $this->db->get()->result();

				if (!empty($get_no_incoming_warehouse)) {
					foreach ($get_no_incoming_warehouse as $item_incoming) {
						$incoming_no[] = $item_incoming->kode_trans;
					}
					$incoming_no = implode(', ', $incoming_no);
				} else {
					$incoming_no = '';
				}
			}

			$link_no_incoming[$item->no_po] = $incoming_no;
		}

		$this->template->set('results', $data);
		$this->template->set('list_no_incoming', $link_no_incoming);
		$this->template->title('Purchase Order');
		$this->template->render('index');
	}

	public function add()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		$supplier = $data = $this->db->query("SELECT a.* FROM new_supplier as a INNER JOIN tr_purchase_request as c on b.no_pr = c.no_pr WHERE c.status = '2' GROUP BY b.suplier ")->result();
		$comp	= $this->db->query("select a.*, b.nominal as nominal_harga FROM ms_compotition as a inner join child_history_lme as b on b.id_compotition=a.id_compotition where a.deleted='0' and b.status = '0' ")->result();
		$customers = $this->db->get_where('customer', ['deleted_by' => null]);
		$karyawan = $this->db->get_where('employee', ['deleted_by' => null]);
		$mata_uang = $this->db->get_where('mata_uang', ['deleted_by' => null]);
		$matauang = $this->db->get_where('matauang')->result();
		$data = [
			'supplier' => $supplier,
			'comp' => $comp,
			'customers' => $customers,
			'karyawan' => $karyawan,
			'mata_uang' => $mata_uang
		];
		$this->template->set('results', $data);
		$this->template->title('Purchase Order');
		$this->template->render('Add');
	}

	public function edit($no_po)
	{
		$session = $this->session->userdata('app_session');
		// $getparam = explode(";", $_GET['param']);

		// print_r($no_po);
		// exit;

		$get_po = $this->db->get_where('tr_purchase_order', ['no_po' => $no_po])->row();

		$getso = $this->Pr_model->get_where_in('so_number', $no_po, 'material_planning_base_on_produksi');
		// $getitemso = $this->Pr_model->get_where_in('so_number', $getparam, 'material_planning_base_on_produksi_detail');

		// $getitemso = $this->db->select("a.*, (b.qty_stock - b.qty_booking) AS avl_stock, c.code as code, d.id_stock as code1, c.nama as nm_material, d.stock_name as nm_material1, e.propose_purchase");
		// $getitemso = $this->db->from('dt_trans_po a');
		// $getitemso = $this->db->join('warehouse_stock b', 'b.id_material = a.idmaterial', 'left');
		// $getitemso = $this->db->join('new_inventory_4 c', 'c.code_lv4 = a.idmaterial', 'left');
		// $getitemso = $this->db->join('accessories d', 'd.id = a.idmaterial', 'left');
		// $getitemso = $this->db->join('material_planning_base_on_produksi_detail e', 'e.id = a.idpr', 'left');
		// $getitemso = $this->db->where_in('a.no_po', $no_po);
		// $getitemso = $this->db->group_by('a.id');

		$getitemso = $this->db->query("
			SELECT 
				a.id as id,
				a.idpr as idpr,
				a.no_po as no_po,
				a.idmaterial as idmaterial,
				a.qty as qty,
				a.hargasatuan as hargasatuan,
				a.jumlahharga as jumlahharga,
				a.kode_barang as kode_barang,
				a.ppn as ppn,
				a.ppn_persen as ppn_persen,
				a.harga_total as harga_total,
				a.tipe as tipe_pr,
				a.keterangan as keterangan,
				(b.qty_stock - b.qty_booking) AS avl_stock, 
				a.kode_barang as code, 
				'' as code1, 
				a.namamaterial as nm_material, 
				'' as nm_material1,
				a.persen_disc as persen_disc,
				a.nilai_disc as nilai_disc,
				e.propose_purchase as propose_purchase,
				g.code as packing_unit,
				h.code as packing_unit2,
				IF(i.code IS NOT NULL, i.code, j.code) as unit_measure
			FROM
				dt_trans_po a
				LEFT JOIN warehouse_stock b ON b.id_material = a.idmaterial
				LEFT JOIN new_inventory_4 c ON c.code_lv4 = a.idmaterial OR c.id = a.idmaterial
				LEFT JOIN material_planning_base_on_produksi_detail e ON e.id = a.idpr
				LEFT JOIN accessories f ON f.id = a.idmaterial
				LEFT JOIN ms_satuan g ON g.id = c.id_unit_packing
				LEFT JOIN ms_satuan h ON h.id = f.id_unit_gudang
				LEFT JOIN ms_satuan i ON i.id = c.id_unit
				LEFT JOIN ms_satuan j ON j.id = f.id_unit
			WHERE
				a.no_po IN ('" . str_replace(",", "','", $no_po) . "') AND
				(a.tipe IS NULL OR a.tipe = '')
			GROUP BY id

			UNION ALL

			SELECT 
				a.id as id,
				a.idpr as idpr,
				a.no_po as no_po,
				'' as idmaterial,
				a.qty as qty,
				a.hargasatuan as hargasatuan,
				a.jumlahharga as jumlahharga,
				a.kode_barang as kode_barang,
				a.ppn as ppn,
				a.ppn_persen as ppn_persen,
				a.harga_total as harga_total,
				a.tipe as tipe_pr,
				a.keterangan as keterangan,
				'0' AS avl_stock, 
				a.kode_barang as code, 
				'' as code1, 
				a.namamaterial as nm_material, 
				'' as nm_material1,
				a.persen_disc as persen_disc,
				a.nilai_disc as nilai_disc, 
				a.qty as propose_purchase,
				IF(f.code IS NULL, 'Pcs', f.code) as packing_unit,
				'' as packing_unit2,
				IF(f.code IS NULL, 'Pcs', f.code) as unit_measure
			FROM
				dt_trans_po a
				LEFT JOIN rutin_non_planning_detail e ON e.id = a.idpr
				LEFT JOIN ms_satuan f ON f.id = e.satuan
			WHERE
				a.no_po IN ('" . str_replace(",", "','", $no_po) . "') AND 
				a.tipe = 'pr depart'

			UNION ALL

			SELECT 
				a.id as id,
				a.idpr as idpr,
				a.no_po as no_po,
				'' as idmaterial,
				a.qty as qty,
				a.hargasatuan as hargasatuan,
				a.jumlahharga as jumlahharga,
				a.kode_barang as kode_barang,
				a.ppn as ppn,
				a.ppn_persen as ppn_persen,
				a.harga_total as harga_total,
				a.tipe as tipe_pr,
				a.keterangan as keterangan,
				'0' AS avl_stock, 
				a.kode_barang as code, 
				'' as code1, 
				a.namamaterial as nm_material, 
				'' as nm_material1,
				a.persen_disc as persen_disc,
				a.nilai_disc as nilai_disc, 
				a.qty as propose_purchase,
				'Pcs' as packing_unit,
				'' as packing_unit2,
				'Pcs' as unit_measure
			FROM
				dt_trans_po a
				LEFT JOIN rutin_non_planning_detail e ON e.id = a.idpr
			WHERE
				a.no_po IN ('" . str_replace(",", "','", $no_po) . "') AND 
				a.tipe = 'pr asset'
			
			UNION ALL

			SELECT
				a.id as id,
				a.idpr as idpr,
				a.no_po as no_po,
				'' as idmaterial,
				a.qty as qty,
				a.hargasatuan as hargasatuan,
				a.jumlahharga as jumlahharga,
				a.kode_barang as kode_barang,
				a.ppn as ppn,
				a.ppn_persen as ppn_persen,
				a.harga_total as harga_total,
				a.tipe as tipe_pr,
				a.keterangan as keterangan,
				'0' AS avl_stock, 
				a.kode_barang as code, 
				'' as code1, 
				a.namamaterial as nm_material, 
				'' as nm_material1,
				a.persen_disc as persen_disc,
				a.nilai_disc as nilai_disc, 
				a.qty as propose_purchase,
				'Item' as packing_unit,
				'' as packing_unit2,
				'Item' as unit_measure
			FROM 	
				dt_trans_po a
				LEFT JOIN " . DBCNL . ".kons_tr_kasbon_project_header e ON e.id = a.idpr
			WHERE
				a.no_po IN ('" . str_replace(",", "','", $no_po) . "') AND 
				a.tipe = 'project consultant'
			GROUP BY id
		")->result();



		// $getitemso = $this->db->get()->result();

		// print_r($getitemso);
		// exit;

		$aktif = 'active';
		$deleted = '0';
		// $supplier = $data = $this->db->query("SELECT a.* FROM new_supplier as a INNER JOIN dt_trans_pr as b on b.suplier = a.id_suplier INNER JOIN tr_purchase_request as c on b.no_pr = c.no_pr WHERE c.status = '2' GROUP BY b.suplier ")->result();

		// $comp	= $this->db->query("select a.*, b.nominal as nominal_harga FROM ms_compotition as a inner join child_history_lme as b on b.id_compotition=a.id_compotition where a.deleted='0' and b.status='0' ")->result();
		$customers = $this->db->get_where('customer', ['deleted_by' => null])->result();
		$karyawan = $this->db->get_where('ms_karyawan', ['deleted_by' => null])->result();
		$mata_uang = $this->db->get_where('mata_uang', ['deleted' => null])->result();
		$list_supplier = $this->db->get_where('new_supplier', ['deleted_by' => null])->result();
		$list_department = $this->db->select('id, nama')->get_where('ms_department', ['deleted_by' => null])->result();
		// $matauang = $this->db->get_where('matauang')->result();
		$list_group_top = $this->db->get_where('list_help', ['group_by' => 'top', 'sts' => 'Y'])->result();
		$list_top = $this->db->get_where('tr_top_po', ['no_po' => $no_po])->result();
		$num_top = count($list_top);


		$data = [
			// 'supplier' => $supplier,
			// 'comp' => $comp,
			'customers' => $customers,
			'karyawan' => $karyawan,
			'mata_uang' => $mata_uang,
			// 'matauang' => $matauang,

			// 'headerso' => $getso,
			'get_po' => $get_po,
			'getitemso' => $getitemso,
			'list_supplier' => $list_supplier,
			'list_department' => $list_department,
			'list_top' => $list_top,
			'list_group_top' => $list_group_top,
			'num_po' => $num_top
		];

		$this->template->set('results', $data);
		$this->template->title('Input Purchase Order');
		$this->template->render('Edit');
	}

	public function Lihat()
	{
		$id = $this->uri->segment(3);
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		$comp	= $this->db->query("select a.*, b.nominal as nominal_harga FROM ms_compotition as a inner join child_history_lme as b on b.id_compotition=a.id_compotition where a.deleted='0' and b.status='0' ")->result();
		$head = $this->db->query("SELECT a.*, b.name_suplier as suplier FROM tr_purchase_order as a INNER JOIN master_supplier as b on a.id_suplier = b.id_suplier  WHERE a.no_po = '$id' ")->result();
		$detail = $this->db->query("SELECT a.*, b.nama  FROM dt_trans_po a
		INNER JOIN ms_inventory_category3 b ON a.idmaterial = b.id_category3
		WHERE no_po = '$id' ")->result();
		$supplier = $data = $this->db->query("SELECT a.* FROM master_supplier as a INNER JOIN dt_trans_pr as b on b.suplier = a.id_suplier INNER JOIN tr_purchase_request as c on b.no_pr = c.no_pr WHERE c.status = '2' ")->result();
		$customers = $this->Pr_model->get_data('master_customers', 'deleted', $deleted);
		$karyawan = $this->Pr_model->get_data('ms_karyawan', 'deleted', $deleted);
		$mata_uang = $this->Pr_model->get_data('mata_uang', 'deleted' . $deleted);
		$matauang = $this->db->get_where('matauang')->result();
		$data = [
			'head' => $head,
			'comp' => $comp,
			'detail' => $detail,
			'supplier' => $supplier,
			'customers' => $customers,
			'karyawan' => $karyawan,
			'mata_uang' => $mata_uang,
			'matauang' => $matauang,
		];
		$this->template->set('results', $data);
		$this->template->title('Purchase Order');
		$this->template->render('View');
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
		$this->template->title('Add Penawaran');
		$this->template->render('Addpr');
	}
	function CariKurs()
	{

		$loi	= $_GET['loi'];
		$hariini = date('Y-m-d');
		$sepuluh_hari = mktime(0, 0, 0, date('n'), date('j') - 10, date('Y'));
		$tendays = date("Y-m-d", $sepuluh_hari);
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
		$kurs	= $this->db->query("SELECT * FROM mata_uang WHERE kode = 'IDR' ")->result();
		$kurs10hari	= $this->db->query("SELECT AVG(nominal) as nominal FROM perubahan_kurs WHERE tanggal_ubah BETWEEN  '$tendays' AND '$hariini' AND kode_kurs='IDR' ")->result();
		$kurs30hari	= $this->db->query("SELECT AVG(nominal) as nominal FROM perubahan_kurs WHERE MONTH(tanggal_ubah) =  '$blnkmrn' AND YEAR(tanggal_ubah) = '$yearkemaren' AND kode_kurs='IDR' ")->result();
		$nomkurs = $kurs[0]->kurs;
		$nomkurs10 = $kurs10hari[0]->nominal;
		$nomkurs30 = $kurs30hari[0]->nominal;
		$k =  number_format($nomkurs, 2);
		$k10 =  number_format($nomkurs10, 2);
		$k30 =  number_format($nomkurs30, 2);
		if ($loi == 'Import') {
			echo "
				<table class='col-sm-12' border='1' cellspacing='0'>
					<tr>
						<th><center>Kurs On The Spot</center></th>
						<th><center>Kurs 10 Hari</center></th>
						<th><center>Kurs 30 Hari</center></th>
					</tr>
					<tr>
						<td><center>Rp. $k  ,-</center></td>
						<td><center>Rp. $k10  ,-</center></td>
						<td><center>Rp. $k30  ,-</center></td>
					</tr>
				<table>
		";
		} else {
		};
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
		$header = $this->db->query("SELECT * FROM tr_purchase_request WHERE no_pr = '$id' ")->result();
		$detail = $this->db->query("SELECT * FROM dt_trans_pr WHERE no_pr = '$id' ")->result();

		$nm_depart = [];
		$get_nm_depart = $this->db->query("SELECT nama FROM ms_department WHERE id IN ('" . str_replace(",", "','", $header[0]->id_dept) . "')")->result();
		if (!empty($get_nm_depart)) {
			foreach ($get_nm_depart as $item_depart) {
				$nm_depart[] = strtoupper($item_depart->nama);
			}
		}

		if (!empty($nm_depart)) {
			$nm_depart = implode(', ', $nm_depart);
		} else {
			$nm_depart = '';
		}


		$data = [
			'header' => $header,
			'detail' => $detail,
			'nm_depart' => $nm_depart
		];
		$this->template->set('results', $data);
		$this->template->title('View P.R');
		$this->template->render('View');
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
		$loop		= $_GET['jumlah'] + 1;
		$id_suplier	= $_GET['id_suplier'];
		$loi 		= $_GET['loi'];
		$material 	= $this->db->query("SELECT * FROM dt_trans_pr WHERE suplier = '$id_suplier'  ")->result();

		if ($loi == 'Import') {
			echo "
			<tr id='trmaterial_$loop'>
			<th style='font-size:90%'><select style='font-size:90%' class='form-control chosen-select' id='dt_idpr_" . $loop . "' name='dt[" . $loop . "][idpr]'	onchange ='CariProperties(" . $loop . ")'>
			<option value=''>--Pilih--</option>";
			foreach ($material as $material) {
				$nopr = $material->no_pr;
				$surat = $this->db->query("SELECT * FROM tr_purchase_request WHERE no_pr = '$nopr'")->row();
				echo "<option value='$material->id_dt_pr'>$material->nama_material|$surat->no_surat</option>";
			};
			echo "</select></th>
			<th id='idmaterial_" . $loop . "'	hidden><input  type='text' 	style='font-size:90%'	class='form-control' id='dt_idmaterial_" . $loop . "' 	required name='dt[" . $loop . "][idmaterial]' ></th>
			<th id='namaterial_" . $loop . "'	hidden><input  type='text' 	style='font-size:90%'	class='form-control' id='dt_namamaterial_" . $loop . "' required name='dt[" . $loop . "][namamaterial]' ></th>
			
			<th id='description_" . $loop . "'	style='font-size:90%'><input style='font-size:90%' type='text' 			class='form-control' id='dt_description_" . $loop . "' 	required name='dt[" . $loop . "][description]' ></th>
			
			<th id='width_" . $loop . "'><input  type='number' style='font-size:90%'	class='form-control' id='dt_width_" . $loop . "' 		required name='dt[" . $loop . "][width]' onblur='HitungHarga(" . $loop . ")' ></th>
			
			
			<th id='totalwidth_" . $loop . "'	><input  type='number' style='font-size:90%'	class='form-control' id='dt_totalwidth_" . $loop . "' 	required name='dt[" . $loop . "][totalwidth]' onblur='HitungHarga(" . $loop . ")' ></th>
			
			<th id='qty_" . $loop . "'			hidden style='font-size:90%'><input style='font-size:90%' type='number' 			class='form-control' id='dt_qty_" . $loop . "' 			required name='dt[" . $loop . "][qty]' onkeyup='HitungUP(" . $loop . ")' ></th>
			
			<th 							style='font-size:90%' ><select		style='font-size:90%'				class='form-control' id='dt_ratelme_" . $loop . "' 		required name='dt[" . $loop . "][ratelme]' onchange='CariPrice(" . $loop . ")'>
			<option value=''>-Pilih-</option>
			<option value='Hari Ini'>Hari ini</option>
			<option value='H-10'>H-10</option>
			<option value='H-30'>H-30</option>
			</select></th>
			
			<th id='alloyprice_" . $loop . "'	style='font-size:90%'><input  type='text' 	style='font-size:90%'		class='form-control input-md bilangan-desimal' id='dt_alloyprice_" . $loop . "' 	required data-decimal='.' data-thousand='' data-precision='0' data-allow-zero='' name='dt[" . $loop . "][alloyprice]' onkeyup='HitungUP(" . $loop . ")' ></th>
			
			<th 							style='font-size:90%'><input  type='text' 	style='font-size:90%'		class='form-control input-md' id='dt_fabcost_" . $loop . "' 		required  name='dt[" . $loop . "][fabcost]' onkeyup='HitungUP(" . $loop . ")' ></th>
			
			<th	id='panjang_" . $loop . "'		hidden><input  type='number' style='font-size:90%'	class='form-control' id='dt_panjang_" . $loop . "' 		required name='dt[" . $loop . "][panjang]' onkeyup='HitungHarga(" . $loop . ")'></th>
			<th	id='lebar_" . $loop . "'		hidden><input  type='number' style='font-size:90%'	class='form-control' id='dt_lebar_" . $loop . "' 		required name='dt[" . $loop . "][lebar]' ></th>
			
			<th								style='font-size:90%'><input style='font-size:90%' class='form-control input-md autoNumeric3'		class='form-control' id='dt_hargasatuan_" . $loop . "' data-decimal='.' data-thousand='' data-allow-zero='' 	required name='dt[" . $loop . "][hargasatuan]' onblur='HitungUP(" . $loop . ")'></th>
			
			<th								style='font-size:90%'><input style='font-size:90%' type='text' 			class='form-control bilangan-desimal' id='dt_diskon_" . $loop . "' 		required name='dt[" . $loop . "][diskon]' onkeyup='HitungUPIm(" . $loop . ")'></th>
			
			<th								style='font-size:90%'><input style='font-size:90%' type='text' 			class='form-control bilangan-desimal' id='dt_pajak_" . $loop . "' 		required name='dt[" . $loop . "][pajak]' onkeyup='HitungUPIm(" . $loop . ")'></th>
			
			<th id='jumlahharga_" . $loop . "'	style='font-size:90%'><input style='font-size:90%' readonly type='text' 	class='form-control' id='dt_jumlahharga_" . $loop . "' 	required name='dt[" . $loop . "][jumlahharga]' ></th>
			
			<th								style='font-size:90%'><input style='font-size:90%' type='text' 			class='form-control' id='dt_note_" . $loop . "' 		required name='dt[" . $loop . "][note]' ></th>
			
			<th><button type='button' class='btn btn-sm btn-success' title='Lock' data-role='qtip' onClick='return LockMaterial(" . $loop . ");'><i class='fa fa-key'></i></button>
			<button type='button' class='btn btn-sm btn-danger' title='Hapus Data' data-role='qtip' onClick='return HapusItem(" . $loop . ");'><i class='fa fa-close'></i></button></th>
			</tr>
			";
		} else {
			echo "
			<tr id='trmaterial_$loop'>
			<th style='font-size:90%'><select style='font-size:90%' class='form-control chosen-select' id='dt_idpr_" . $loop . "' name='dt[" . $loop . "][idpr]'	onchange ='CariProperties(" . $loop . ")'>
			<option value=''>--Pilih--</option>";
			foreach ($material as $material) {
				echo "<option value='$material->id_dt_pr'>$material->nama_material</option>";
			};
			echo "</select></th>
			<th id='idmaterial_" . $loop . "'	hidden><input  type='text' 	style='font-size:90%'	class='form-control' id='dt_idmaterial_" . $loop . "' 	required name='dt[" . $loop . "][idmaterial]' ></th>
			<th id='namaterial_" . $loop . "'	hidden><input  type='text' 	style='font-size:90%'	class='form-control' id='dt_namamaterial_" . $loop . "' required name='dt[" . $loop . "][namamaterial]' ></th>
			<th id='description_" . $loop . "'	style='font-size:90%'><input style='font-size:90%' type='text' 			class='form-control' id='dt_description_" . $loop . "' 	required name='dt[" . $loop . "][description]' ></th>
			<th id='width_" . $loop . "'		><input  type='number' style='font-size:90%'	class='form-control' id='dt_width_" . $loop . "' 		required name='dt[" . $loop . "][width]' onkeyup='HitungHarga(" . $loop . ")' ></th>
			<th id='totalwidth_" . $loop . "'	><input  type='number' style='font-size:90%'	class='form-control' id='dt_totalwidth_" . $loop . "' 	required name='dt[" . $loop . "][totalwidth]' onkeyup='HitungHarga(" . $loop . ")' ></th>
			<th id='qty_" . $loop . "'			hidden style='font-size:90%'><input style='font-size:90%' type='number' 			class='form-control' id='dt_qty_" . $loop . "' 			required name='dt[" . $loop . "][qty]' onkeyup='HitungUP(" . $loop . ")' ></th>
			<th 							style='font-size:90%' ><select		style='font-size:90%'		class='form-control' id='dt_ratelme_" . $loop . "' 		required name='dt[" . $loop . "][ratelme]' onchange='CariPrice(" . $loop . ")'>
			<option value=''>-Pilih-</option>
			<option value='Hari Ini'>Hari ini</option>
			<option value='H-10'>H-10</option>
			<option value='H-30'>H-30</option>
			</select></th>
			<th id='alloyprice_" . $loop . "'	style='font-size:90%'><input  type='text' 	style='font-size:90%' disabled class='form-control input-md bilangan-desimal' id='dt_alloyprice_" . $loop . "' 	required data-decimal='.' data-thousand='' data-precision='0' data-allow-zero='' name='dt[" . $loop . "][alloyprice]' onkeyup='HitungUPIm(" . $loop . ")' ></th>
			<th 							style='font-size:90%'><input  type='text' 	style='font-size:90%' disabled	class='form-control input-md bilangan-desimal' id='dt_fabcost_" . $loop . "' 		required data-decimal='.' data-thousand='' data-precision='0' data-allow-zero='' name='dt[" . $loop . "][fabcost]' onkeyup='HitungUPIm(" . $loop . ")' ></th>
			<th	id='panjang_" . $loop . "'		hidden><input  type='number' style='font-size:90%'	class='form-control' id='dt_panjang_" . $loop . "' 		required name='dt[" . $loop . "][panjang]' onkeyup='HitungHarga(" . $loop . ")'></th>
			<th	id='lebar_" . $loop . "'		hidden><input  type='number' style='font-size:90%'	class='form-control' id='dt_lebar_" . $loop . "' 		required name='dt[" . $loop . "][lebar]' ></th>
			<th								style='font-size:90%'><input style='font-size:90%' class='form-control input-md autoNumeric3'			class='form-control' id='dt_hargasatuan_" . $loop . "' 	required data-decimal='.' data-thousand='' data-precision='0' data-allow-zero='' name='dt[" . $loop . "][hargasatuan]' onkeyup='HitungUP(" . $loop . ")'></th>
			<th								style='font-size:90%'><input style='font-size:90%' type='text' 			class='form-control bilangan-desimal' id='dt_diskon_" . $loop . "' 		required name='dt[" . $loop . "][diskon]' onkeyup='HitungUP(" . $loop . ")'></th>
			<th								style='font-size:90%'><input style='font-size:90%' type='text' 			class='form-control bilangan-desimal' id='dt_pajak_" . $loop . "' 		required name='dt[" . $loop . "][pajak]' onkeyup='HitungUP(" . $loop . ")'></th>
			<th id='jumlahharga_" . $loop . "'	style='font-size:90%'><input style='font-size:90%' readonly type='text' 	class='form-control' id='dt_jumlahharga_" . $loop . "' 	required name='dt[" . $loop . "][jumlahharga]' ></th>
			<th								style='font-size:90%'><input style='font-size:90%' type='text' 			class='form-control' id='dt_note_" . $loop . "' 		required name='dt[" . $loop . "][note]' ></th>
			<th><button type='button' class='btn btn-sm btn-success' title='Lock' data-role='qtip' onClick='return LockMaterial(" . $loop . ");'><i class='fa fa-key'></i></button>
			<button type='button' class='btn btn-sm btn-danger' title='Hapus Data' data-role='qtip' onClick='return HapusItem(" . $loop . ");'><i class='fa fa-close'></i></button></th>
			</tr>
		";
		}
	}

	function AddMaterial_Direct()
	{
		$no_pr		= $this->input->post('no_pr');
		$loi 		= $this->input->post('loi');
		$material 	= $this->db->query("SELECT * FROM dt_trans_pr WHERE no_pr = '$no_pr'")->result_array();
		$datemin 	= $this->db->query("SELECT MIN(tanggal) AS tanggal FROM dt_trans_pr WHERE no_pr = '$no_pr'")->result();
		// print_r($material);
		$LIST = "";
		foreach ($material as $key => $value) {
			$key++;
			$disabled = ($loi == 'Import') ? '' : 'readonly';
			$disabled2 = ($loi == 'Import') ? 'readonly' : '';
			$idmat = $value['idmaterial'];
			$harga 	= $this->db->query("SELECT * FROM ms_product_pricelist WHERE id_category3 = '$idmat'")->row();

			$total = $harga->harga_beli * $value['qty'];

			$LIST .= "<tr>";
			$LIST .= 	"<td>" . $value['nama_material'] . "
								<input type='hidden' class='form-control input-sm' id='dt_idpr_" . $key . "' name='dt[" . $key . "][idpr]' value='" . $value['id_dt_pr'] . "'>
								<input type='hidden' class='form-control input-sm' id='dt_idmaterial_" . $key . "' name='dt[" . $key . "][idmaterial]' value='" . $value['idmaterial'] . "'>
								<input type='hidden' class='form-control input-sm' id='dt_namamaterial_" . $key . "' name='dt[" . $key . "][namamaterial]' value='" . $value['nama_material'] . "'>
								<input type='hidden' class='form-control input-sm' id='dt_panjang_" . $key . "' name='dt[" . $key . "][panjang]'>
								<input type='hidden' class='form-control input-sm' id='dt_lebar_" . $key . "' name='dt[" . $key . "][lebar]'>

								<input type='hidden' class='form-control input-sm ch_diskon' id='dt_ch_diskon_" . $key . "'>
								<input type='hidden' class='form-control input-sm ch_pajak' id='dt_ch_pajak_" . $key . "'>
								<input type='hidden' class='form-control input-sm ch_jumlah' id='dt_ch_jumlah_" . $key . "'>

							</td>";
			$LIST .= 	"<td><input type='text' class='form-control input-sm' name='dt[" . $key . "][description]' id='dt_description_" . $key . "' value='" . $value['keterangan'] . "'></td>";
			$LIST .= 	"<td hidden><input type='hidden' class='form-control input-sm autoNumeric' name='dt[" . $key . "][width]' id='dt_width_" . $key . "'  value='" . $value['width'] . "'></td>";
			$LIST .= 	"<td hidden><input type='hidden' class='form-control input-sm autoNumeric' name='dt[" . $key . "][length]' id='dt_length_" . $key . "'  value='" . $value['length'] . "'></td>";
			$LIST .= 	"<td><input type='hidden' class='form-control input-sm autoNumeric' name='dt[" . $key . "][totalweight]' id='dt_totalweight_" . $key . "' value='" . $value['totalweight'] . "'  onkeyup='HitAmmount(" . $key . ")'>
										<input type='text' class='form-control input-sm' id='dt_qty_" . $key . "' name='dt[" . $key . "][qty]' value='" . $value['qty'] . "' onkeyup='HitAmmount(" . $key . ")'>
								
							</td>";
			$LIST .= 	"<td hidden>
								<select class='form-control input-sm' id='dt_ratelme_" . $key . "' name='dt[" . $key . "][ratelme]' onchange='CariPrice(" . $key . ")'>
									<option value=''>-Pilih-</option>
									<option value='Hari Ini'>Hari ini</option>
									<option value='H-10'>H-10</option>
									<option value='H-30'>H-30</option>
								</select>
							</td>";
			$LIST .= 	"<td hidden><input type='text' class='form-control input-sm autoNumeric3' id='dt_alloyprice_" . $key . "' " . $disabled . " data-decimal='.' data-thousand='' data-precision='0' data-allow-zero='' name='dt[" . $key . "][alloyprice]' onkeyup='HitAmmount(" . $key . ")'></td>";
			$LIST .= 	"<td hidden><input type='text' class='form-control input-sm autoNumeric3' id='dt_fabcost_" . $key . "' " . $disabled . " name='dt[" . $key . "][fabcost]' onkeyup='HitAmmount(" . $key . ")'></td>";
			$LIST .= 	"<td><input type='text' class='form-control input-sm autoNumeric3' id='dt_hargasatuan_" . $key . "' name='dt[" . $key . "][hargasatuan]' onkeyup='HitAmmount(" . $key . ")' value='" . $harga->harga_beli . "'></td>";
			$LIST .= 	"<td hidden><input type='text' class='form-control input-sm autoNumeric' id='dt_diskon_" . $key . "' name='dt[" . $key . "][diskon]' onkeyup='HitAmmount(" . $key . ")'></td>";
			$LIST .= 	"<td><input type='text' class='form-control input-sm autoNumeric pajak' id='dt_pajak_" . $key . "' name='dt[" . $key . "][pajak]' onkeyup='HitAmmount(" . $key . ")'></td>";
			$LIST .= 	"<td><input type='text' class='form-control input-sm ch_jumlah_ex' id='dt_jumlahharga_" . $key . "' readonly name='dt[" . $key . "][jumlahharga]' value='" . $total . "'></td>";
			$LIST .= 	"<td><input type='text' class='form-control input-sm' id='dt_note_" . $key . "' name='dt[" . $key . "][note]'></td>";
			$LIST .= 	"<td>
								<button type='button' class='btn btn-sm btn-danger hapus_baris' title='Hapus Data' data-role='qtip'><i class='fa fa-close'></i></button>
							</td>";
			$LIST .= "</tr>";
		}

		$data = [
			'list_mat' => $LIST,
			'min_date' => date('d-M-Y', strtotime($datemin[0]->tanggal))
		];

		echo json_encode($data);
	}



	function LockMatrial()
	{
		$loop = $_GET['id'];
		$idpr = $_GET['idpr'];
		$idmaterial = $_GET['idmaterial'];
		$namamaterial = $_GET['namaterial'];
		$description = $_GET['description'];
		$qty = $_GET['qty'];
		$width = $_GET['width'];
		$alloyprice = $_GET['alloyprice'];
		$fabcost = $_GET['fabcost'];
		$ratelme = $_GET['ratelme'];
		$totalwidth = $_GET['totalwidth'];
		$hargasatuan = $_GET['hargasatuan'];
		$panjang = $_GET['panjang'];
		$lebar = $_GET['lebar'];
		$diskon = $_GET['diskon'];
		$pajak = $_GET['pajak'];
		$jumlahharga = $_GET['jumlahharga'];
		$note = $_GET['note'];
		echo "
		<td hidden><input readonly 	type='text' 	value='" . $idpr . "'				class='form-control' id='dt_idpr_" . $loop . "' 	required name='dt[" . $loop . "][idpr]' ></td>		
		<td hidden><input readonly 	type='text' 	value='" . $idmaterial . "'			class='form-control' id='dt_idmaterial_" . $loop . "' 	required name='dt[" . $loop . "][idmaterial]' ></td>		
		<td ><input		readonly  	type='text' 	value='" . $namamaterial . "'		class='form-control' id='dt_namamaterial_" . $loop . "' required name='dt[" . $loop . "][namamaterial]' ></td>
		
		<td ><input		readonly  	type='text' 	value='" . $description . "'		class='form-control' id='dt_description_" . $loop . "' 	required name='dt[" . $loop . "][description]' ></td>
		
		<td><input		readonly  	type='number' 	value='" . $width . "'		class='form-control' id='dt_width_" . $loop . "' 			required name='dt[" . $loop . "][width]'  ></td>
		
		<td ><input		readonly  	type='number' 	value='" . $totalwidth . "'	class='form-control' id='dt_totalwidth_" . $loop . "' 			required name='dt[" . $loop . "][totalwidth]'  ></td>
		
		<td hidden><input		readonly  	type='number' 	value='" . $qty . "'				class='form-control' id='dt_qty_" . $loop . "' 			required name='dt[" . $loop . "][qty]'  ></td>
		
		<td ><input		readonly  	type='text' 	value='" . $ratelme . "'			class='form-control' id='dt_ratelme_" . $loop . "' 			required name='dt[" . $loop . "][ratelme]'  ></td>
		
		<td ><input		readonly  	type='text' 	value='" . $alloyprice . "'			class='form-control' id='dt_alloyprice_" . $loop . "' 			required name='dt[" . $loop . "][alloyprice]'  ></td>
		
		<td ><input		readonly  	type='text' 	value='" . $fabcost . "'			class='form-control' id='dt_fabcost_" . $loop . "' 			required name='dt[" . $loop . "][fabcost]'  ></td>
		
		<td hidden><input		readonly  	type='number' 	value='" . $panjang . "'	class='form-control' id='dt_panjang_" . $loop . "' 			required name='dt[" . $loop . "][panjang]'  ></td>
		<td hidden><input		readonly  	type='number' 	value='" . $lebar . "'		class='form-control' id='dt_lebar_" . $loop . "' 			required name='dt[" . $loop . "][lebar]'  ></td>
		
		<td	><input		readonly  	type='text' 	value='" . $hargasatuan . "'		class='form-control' id='dt_hargasatuan_" . $loop . "' 	required name='dt[" . $loop . "][hargasatuan]' ></td>
		
		<td	hidden><input		readonly 	type='number' 	value='" . $diskon . "'				class='form-control' id='dt_diskon_" . $loop . "' 		required name='dt[" . $loop . "][diskon]' ></td>
		<td	hidden><input		readonly	type='text' 	value='" . $pajak . "'				class='form-control' id='dt_pajak_" . $loop . "' 		required name='dt[" . $loop . "][pajak]' ></td>
		<td ><input		readonly 	type='text' 	value='" . $jumlahharga . "'		class='form-control' id='dt_jumlahharga_" . $loop . "' 	required name='dt[" . $loop . "][jumlahharga]' ></td>
		<td	><input		readonly  	type='text' 	value='" . $note . "'				class='form-control' id='dt_note_" . $loop . "' 		required name='dt[" . $loop . "][note]' ></td>
		<td><button type='button' class='btn btn-sm btn-danger' title='Hapus Data' data-role='qtip' onClick='return CancelItem($loop);'><i class='fa fa-close'></i></button></td>
		";
	}
	function CariPrice()
	{
		$dt_idmaterial = $_GET['dt_idmaterial'];
		$dt_ratelme = $_GET['dt_ratelme'];
		$hariini 		= date('Y-m-d');
		$satu_hari 		= mktime(0, 0, 0, date('n'), date('j') - 1, date('Y'));
		$kemarin 		= date("Y-m-d", $satu_hari);
		$sepuluh_hari 	= mktime(0, 0, 0, date('n'), date('j') - 14, date('Y'));
		$tendays 		= date("Y-m-d", $sepuluh_hari);
		$tglnow 		= date('d');
		$blnnow 		= date('m');
		if ($blnnow 	!= '1') {
			$blnkmrn	 	= $blnnow - 1;
			$yearkemaren 	= date('Y');
		} else {
			$blnkmrn 		= "12";
			$yearnow 		= date('Y');
			$yearkemaren 	= $yearnow - 1;
		}
		$comp13	= $this->db->query("select * FROM child_inven_compotition WHERE id_category3 = '" . $dt_idmaterial . "' AND id_compotition = '13' ")->result();
		$kandungan13 = $comp13[0]->nilai_compotition;
		$lme_spot13		= $this->db->query("SELECT AVG(nominal) as nominal FROM child_history_lme WHERE status='0' AND id_compotition='13' ")->result();
		$lme_10hari13	= $this->db->query("SELECT AVG(nominal) as nominal FROM child_history_lme WHERE tanggal_update BETWEEN  '$tendays' AND '$kemarin' AND id_compotition='13' ")->result();
		$lme_30hari13	= $this->db->query("SELECT AVG(nominal) as nominal FROM child_history_lme WHERE MONTH(tanggal_update) =  '$blnkmrn' AND YEAR(tanggal_update) = '$yearkemaren' AND id_compotition='13' ")->result();
		$nomspot13		= $lme_spot13[0]->nominal * ($kandungan13 / 100);
		$nom1013		= $lme_10hari13[0]->nominal * ($kandungan13 / 100);
		$nom3013		= $lme_30hari13[0]->nominal * ($kandungan13 / 100);
		$comp14	= $this->db->query("select * FROM child_inven_compotition WHERE id_category3 = '" . $dt_idmaterial . "' AND id_compotition = '14' ")->result();
		$kandungan14 = $comp14[0]->nilai_compotition;
		$lme_spot14		= $this->db->query("SELECT AVG(nominal) as nominal FROM child_history_lme WHERE status='0' AND id_compotition='14' ")->result();
		$lme_10hari14	= $this->db->query("SELECT AVG(nominal) as nominal FROM child_history_lme WHERE tanggal_update BETWEEN  '$tendays' AND '$kemarin' AND id_compotition='14' ")->result();
		$lme_30hari14	= $this->db->query("SELECT AVG(nominal) as nominal FROM child_history_lme WHERE MONTH(tanggal_update) =  '$blnkmrn' AND YEAR(tanggal_update) = '$yearkemaren' AND id_compotition='14' ")->result();
		$nomspot14		= $lme_spot14[0]->nominal * ($kandungan14 / 100);
		$nom1014		= $lme_10hari14[0]->nominal * ($kandungan14 / 100);
		$nom3014		= $lme_30hari14[0]->nominal * ($kandungan14 / 100);
		$comp15	= $this->db->query("select * FROM child_inven_compotition WHERE id_category3 = '" . $dt_idmaterial . "' AND id_compotition = '15' ")->result();
		$kandungan15 = $comp15[0]->nilai_compotition;
		$lme_spot15		= $this->db->query("SELECT AVG(nominal) as nominal FROM child_history_lme WHERE status='0' AND id_compotition='15' ")->result();
		$lme_10hari15	= $this->db->query("SELECT AVG(nominal) as nominal FROM child_history_lme WHERE tanggal_update BETWEEN  '$tendays' AND '$kemarin' AND id_compotition='15' ")->result();
		$lme_30hari15	= $this->db->query("SELECT AVG(nominal) as nominal FROM child_history_lme WHERE MONTH(tanggal_update) =  '$blnkmrn' AND YEAR(tanggal_update) = '$yearkemaren' AND id_compotition='15' ")->result();
		$nomspot15		= $lme_spot15[0]->nominal * ($kandungan15 / 100);
		$nom1015		= $lme_10hari15[0]->nominal * ($kandungan15 / 100);
		$nom3015		= $lme_30hari15[0]->nominal * ($kandungan15 / 100);
		$comp16	= $this->db->query("select * FROM child_inven_compotition WHERE id_category3 = '" . $dt_idmaterial . "' AND id_compotition = '16' ")->result();
		$kandungan16 = $comp16[0]->nilai_compotition;
		$lme_spot16		= $this->db->query("SELECT AVG(nominal) as nominal FROM child_history_lme WHERE status='0' AND id_compotition='16' ")->result();
		$lme_10hari16	= $this->db->query("SELECT AVG(nominal) as nominal FROM child_history_lme WHERE tanggal_update BETWEEN  '$tendays' AND '$kemarin' AND id_compotition='16' ")->result();
		$lme_30hari16	= $this->db->query("SELECT AVG(nominal) as nominal FROM child_history_lme WHERE MONTH(tanggal_update) =  '$blnkmrn' AND YEAR(tanggal_update) = '$yearkemaren' AND id_compotition='16' ")->result();
		$nomspot16		= $lme_spot16[0]->nominal * ($kandungan16 / 100);
		$nom1016		= $lme_10hari16[0]->nominal * ($kandungan16 / 100);
		$nom3016		= $lme_30hari16[0]->nominal * ($kandungan16 / 100);
		$comp17	= $this->db->query("select * FROM child_inven_compotition WHERE id_category3 = '" . $dt_idmaterial . "' AND id_compotition = '17' ")->result();
		$kandungan17 = $comp17[0]->nilai_compotition;
		$lme_spot17		= $this->db->query("SELECT AVG(nominal) as nominal FROM child_history_lme WHERE status='0' AND id_compotition='17' ")->result();
		$lme_10hari17	= $this->db->query("SELECT AVG(nominal) as nominal FROM child_history_lme WHERE tanggal_update BETWEEN  '$tendays' AND '$kemarin' AND id_compotition='17' ")->result();
		$lme_30hari17	= $this->db->query("SELECT AVG(nominal) as nominal FROM child_history_lme WHERE MONTH(tanggal_update) =  '$blnkmrn' AND YEAR(tanggal_update) = '$yearkemaren' AND id_compotition='17' ")->result();
		$nomspot17		= $lme_spot17[0]->nominal * ($kandungan17 / 100);
		$nom1017		= $lme_10hari17[0]->nominal * ($kandungan17 / 100);
		$nom3017		= $lme_30hari17[0]->nominal * ($kandungan17 / 100);
		$comp18	= $this->db->query("select * FROM child_inven_compotition WHERE id_category3 = '" . $dt_idmaterial . "' AND id_compotition = '18' ")->result();
		$kandungan18 = $comp18[0]->nilai_compotition;
		$lme_spot18		= $this->db->query("SELECT AVG(nominal) as nominal FROM child_history_lme WHERE status='0' AND id_compotition='18' ")->result();
		$lme_10hari18	= $this->db->query("SELECT AVG(nominal) as nominal FROM child_history_lme WHERE tanggal_update BETWEEN  '$tendays' AND '$kemarin' AND id_compotition='18' ")->result();
		$lme_30hari18	= $this->db->query("SELECT AVG(nominal) as nominal FROM child_history_lme WHERE MONTH(tanggal_update) =  '$blnkmrn' AND YEAR(tanggal_update) = '$yearkemaren' AND id_compotition='18' ")->result();
		$nomspot18		= $lme_spot18[0]->nominal * ($kandungan18 / 100);
		$nom1018		= $lme_10hari18[0]->nominal * ($kandungan18 / 100);
		$nom3018		= $lme_30hari18[0]->nominal * ($kandungan18 / 100);
		$valnow			= number_format($nomspot13 + $nomspot14 + $nomspot15 + $nomspot16 + $nomspot17 + $nomspot18);
		$val10			= number_format($nom1013 + $nom1014 + $nom1015 + $nom1016 + $nom1017 + $nom1018);
		$val30			= number_format($nom3013 + $nom3014 + $nom3015 + $nom3016 + $nom3017 + $nom3018);
		if ($dt_ratelme == "Hari Ini") {
			echo "" . $valnow . "";
		} elseif ($dt_ratelme == "H-10") {
			echo "" . $val10 . "";
		} elseif ($dt_ratelme == "H-30") {
			echo "" . $val30 . "";
		}
	}

	function CariPPN()
	{
		$harga = $_GET['harga'];
		// $cari		= $this->db->query("select persen FROM ppn")->row();
		// $ppn   		= $cari->persen;
		$ppnbarang 	= number_format((11 * $harga) / 100, 2);

		echo "" . $ppnbarang . "";
	}
	function HitungHarga()
	{
		$dt_hargasatuan = $_GET['dt_hargasatuan'];
		$dt_qty = $_GET['dt_qty'];
		$loop = $_GET['id'];
		$dt_weight = $_GET['dt_width'];
		// $isi =  $dt_hargasatuan*$dt_qty;
		$isi =  $dt_hargasatuan * $dt_weight;

		// print_r ($isi);
		// exit;
		echo "<input type='text' value='" . $isi . "' 	class='form-control' id='dt_jumlahharga_" . $loop . "' 	required name='dt[" . $loop . "][jumlahharga]' >";
	}
	function UbahImport()
	{
		$loi = $_GET['loi'];
		echo "<input type='text' readonly value='" . $loi . "' class='form-control' id='loi'  required name='loi' readonly placeholder=''>";
	}
	function TotalWeight()
	{
		$dt_width = $_GET['dt_width'];
		$dt_qty = $_GET['dt_qty'];
		$loop = $_GET['id'];
		$isi =  $dt_width * $dt_qty;
		echo "<input type='text' value='" . $isi . "' 	class='form-control' id='dt_totalwidth_" . $loop . "' 	required name='dt[" . $loop . "][totalwidth]' >";
	}
	function CariIdMaterial()
	{
		$idpr = $_GET['idpr'];
		$loop = $_GET['id'];
		$material = $this->db->query("SELECT * FROM dt_trans_pr WHERE id_dt_pr = '$idpr'  ")->result();
		$isi = $material[0]->idmaterial;
		echo "<input readonly type='text' value='" . $isi . "' 	class='form-control' id='dt_idmaterial_" . $loop . "' 	required name='dt[" . $loop . "][idmaterial]' >";
	}
	function HitungUP()
	{
		$alloyprice = str_replace(",", "", $_GET['alloyprice']);
		$fabcost = $_GET['fabcost'];
		$hargasatuan = str_replace(",", "", $_GET['hargasatuan']);
		// $total2 = round(($alloyprice+$fabcost)/1000,3);
		// print_r( $alloyprice);
		// echo "<PRE>";
		// print_r( $fabcost);
		// echo "<PRE>";
		// print_r( $total2);
		// exit;
		$loi = $_GET['loi'];
		if ($loi == 'Import') {
			$total = number_format(round(($alloyprice + $fabcost) / 1000, 3), 2, ".", ",");
		} else {
			$total = number_format($hargasatuan, 3, ".", ",");
		}
		echo "" . $total . "";
	}
	function Hitjumlah()
	{
		$alloyprice = str_replace(",", "", $_GET['alloyprice']);
		$fabcost = str_replace(",", "", $_GET['fabcost']);
		$hargasatuan = str_replace(",", "", $_GET['hargasatuan']);
		$qty = $_GET['qty'];
		$loi = $_GET['loi'];
		$dt_width = $_GET['dt_width'];
		$diskon = $_GET['diskon'];
		$pajak = $_GET['pajak'];
		// if($loi == 'Import'){
		// $total = $alloyprice+$fabcost;
		//$th1 = $total*$qty;
		// $th1 = $total*$dt_width;
		// $jumlah=number_format($th1);
		// }else{
		//$th1 = $hargasatuan*$qty;
		// $th1 = $hargasatuan*$dt_width;
		// $jumlah=number_format($th1);
		// }

		if ($loi == 'Import') {
			$total = $alloyprice + $fabcost;
			//$th1 = $total*$qty;
			$th1 = $hargasatuan * $dt_width;
			$jumlah = number_format($th1, 2, ".", ",");
		} else {
			//$th1 = $hargasatuan*$qty;
			$th1 = $hargasatuan * $dt_width;
			$jumlah = number_format($th1, 2, ".", ",");
		}

		echo "" . $jumlah . "";
	}
	function CariNamaMaterial()
	{
		$idpr = $_GET['idpr'];
		$loop = $_GET['id'];
		$material = $this->db->query("SELECT * FROM dt_trans_pr WHERE id_dt_pr = '$idpr'  ")->result();
		$isi = $material[0]->nama_material;
		echo "<input readonly type='text' value='" . $isi . "' 	class='form-control' id='dt_namamaterial_" . $loop . "' 	required name='dt[" . $loop . "][namamaterial]' >";
	}
	function CariDescripitionMaterial()
	{
		$idpr = $_GET['idpr'];
		$loop = $_GET['id'];
		$material = $this->db->query("SELECT * FROM dt_trans_pr WHERE id_dt_pr = '$idpr'  ")->result();
		$isi = $material[0]->keterangan;
		echo "<input  type='text' value='" . $isi . "' 	class='form-control' id='dt_description_" . $loop . "' 	required name='dt[" . $loop . "][description]' >";
	}
	function CariPanjangMaterial()
	{
		$idpr = $_GET['idpr'];
		$loop = $_GET['id'];
		$material = $this->db->query("SELECT * FROM dt_trans_pr WHERE id_dt_pr = '$idpr'  ")->result();
		$isi = $material[0]->length;
		echo "<input  type='text' value='" . $isi . "' 	class='form-control' id='dt_panjang_" . $loop . "' 	required name='dt[" . $loop . "][panjang]' >";
	}
	function CariLebarMaterial()
	{
		$idpr = $_GET['idpr'];
		$loop = $_GET['id'];
		$material = $this->db->query("SELECT * FROM dt_trans_pr WHERE id_dt_pr = '$idpr'  ")->result();
		$isi = $material[0]->width;
		echo "<input  type='text' value='" . $isi . "' 	class='form-control' id='dt_lebar_" . $loop . "' 	required name='dt[" . $loop . "][lebar]' >";
	}
	function FormInputKurs()
	{
		$loi = $_GET['loi'];
		$hariini = date('Y-m-d');
		$sepuluh_hari = mktime(0, 0, 0, date('n'), date('j') - 10, date('Y'));
		$tendays = date("Y-m-d", $sepuluh_hari);
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
		$kurs	= $this->db->query("SELECT * FROM ms_kurs WHERE aktif = 'Y' ")->result();
		$kurs10hari	= $this->db->query("SELECT AVG(nominal) as nominal FROM perubahan_kurs WHERE tanggal_ubah BETWEEN  '$tendays' AND '$hariini' AND kode_kurs='IDR' ")->result();
		$kurs30hari	= $this->db->query("SELECT AVG(nominal) as nominal FROM perubahan_kurs WHERE MONTH(tanggal_ubah) =  '$blnkmrn' AND YEAR(tanggal_ubah) = '$yearkemaren' AND kode_kurs='IDR' ")->result();
		$nomkurs_asli = $kurs[0]->nilai_kurs;
		$nomkurs10 = $kurs10hari[0]->nominal;
		$nomkurs30 = $kurs30hari[0]->nominal;
		$k =  $nomkurs;
		$k10 =  $nomkurs10;
		$k30 =  $nomkurs30;
		if ($loi == "Import") {
			echo "
		<div class='form-group row'>
			<div class='col-md-4'>
				<label>Kurs</label>
			</div>
			<div class='col-md-8'>
				<input type='text' class='form-control' value='" . number_format($nomkurs_asli, 2) . "' id='nominal_kurs'  required name='nominal_kurs'  placeholder='Nominal Kurs'> 
			</div>
		</div>
		";
		} else {
			echo "
		<div class='form-group row'>
			<div class='col-md-4'>
				<label>Kurs</label>
			</div>
			<div class='col-md-8'>
				<input type='text' class='form-control' value='" . number_format($nomkurs_asli, 2) . "' id='nominal_kurs'  required name='nominal_kurs'  placeholder='Nominal Kurs'>
			</div>
		</div>
		";
		}
	}
	function CariQtyMaterial()
	{
		$idpr = $_GET['idpr'];
		$loop = $_GET['id'];
		$material = $this->db->query("SELECT * FROM dt_trans_pr WHERE id_dt_pr = '$idpr'  ")->result();
		$isi = $material[0]->qty;
		echo "<input  type='text' value='" . $isi . "' 	class='form-control' id='dt_qty_" . $loop . "' onkeyup='HitungHarga(" . $loop . ")' 	required name='dt[" . $loop . "][qty]' >";
	}
	function CariweightMaterial()
	{
		$idpr = $_GET['idpr'];
		$loop = $_GET['id'];
		$material = $this->db->query("SELECT * FROM dt_trans_pr WHERE id_dt_pr = '$idpr'  ")->result();
		$isi = $material[0]->width;
		echo "<input  type='text' value='" . $isi . "' 	class='form-control' id='dt_width_" . $loop . "' onkeyup='HitungHarga(" . $loop . ")' 	required name='dt[" . $loop . "][width]' >";
	}
	function CariTweightMaterial()
	{
		$idpr = $_GET['idpr'];
		$loop = $_GET['id'];
		$material = $this->db->query("SELECT * FROM dt_trans_pr WHERE id_dt_pr = '$idpr'  ")->result();
		$isi = $material[0]->totalweight;
		echo "<input  type='text' value='" . $isi . "' 	class='form-control' id='dt_totalwidth_" . $loop . "' onkeyup='HitungHarga(" . $loop . ")' 	required name='dt[" . $loop . "][totalwidth]' >";
	}
	function CariWidthMaterial()
	{
		$idpr = $_GET['idpr'];
		$loop = $_GET['id'];
		$material = $this->db->query("SELECT * FROM dt_trans_pr WHERE id_dt_pr = '$idpr'  ")->result();
		$isi = $material[0]->width;
		echo "<input  type='text' value='" . $isi . "' 	class='form-control' id='dt_width_" . $loop . "' onkeyup='HitungHarga(" . $loop . ")' 	required name='dt[" . $loop . "][width]' >";
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
		$supplier	= $this->db->query("SELECT a.*, b.name_suplier as supname FROM child_inven_suplier as a INNER JOIN master_supplier as b on a.id_suplier = b.id_suplier WHERE a.id_category3 = '$id_category3' ")->result();
		echo "<select class='form-control' id='dt_suplier_" . $loop . "' name='dt[" . $loop . "][suplier]'>
		<option value=''>--Pilih--</option>";
		foreach ($supplier as $supplier) {
			echo "<option value='" . $supplier->id_suplier . "'>" . $supplier->supname . "</option>";
		}
		echo "</select>";
	}
	function CariTHarga()
	{
		$hargatotal = str_replace(',', '', $_GET['hargatotal']);
		$jumlahharga = str_replace(',', '', $_GET['jumlahharga']);
		$isi = number_format($hargatotal + $jumlahharga);
		echo "<input readonly type='text' value='" . $isi . "' class='form-control' id='hargatotal'  onkeyup required name='hargatotal' >";
	}
	function CariTDiskon()
	{
		$diskontotal = str_replace(',', '', $_GET['diskontotal']);
		$diskon = str_replace(',', '', $_GET['diskon']) / 100;
		$jumlahharga = str_replace(',', '', $_GET['jumlahharga']);
		$val1 = $jumlahharga * $diskon;
		$isi = number_format($val1 + $diskontotal);
		echo "<input readonly type='text' value='" . $isi . "' class='form-control' id='diskontotal'  onkeyup required name='diskontotal' >";
	}
	function CariTPajak()
	{
		$taxtotal = str_replace(',', '', $_GET['taxtotal']);
		$pajak = str_replace(',', '', $_GET['pajak']) / 100;
		$jumlahharga = str_replace(',', '', $_GET['jumlahharga']);
		$val1 = $jumlahharga * $pajak;
		$isi = number_format($val1 + $taxtotal);
		echo "<input readonly type='text' value='" . $isi . "' class='form-control' id='taxtotal'  onkeyup required name='taxtotal' >";
	}
	function CariTSum()
	{
		$taxtotal = str_replace(',', '', $_GET['taxtotal']);
		$pajak = str_replace(',', '', $_GET['pajak']) / 100;
		$jumlahharga = str_replace(',', '', $_GET['jumlahharga']);
		$val1 = $jumlahharga * $pajak;
		$isi1 = $val1 + $taxtotal;
		$diskontotal = str_replace(',', '', $_GET['diskontotal']);
		$diskon = str_replace(',', '', $_GET['diskon']) / 100;
		$val2 = $jumlahharga * $diskon;
		$isi2 = $val2 + $diskontotal;
		$hargatotal = str_replace(',', '', $_GET['hargatotal']);
		$isi3 = $hargatotal + $jumlahharga;
		$isi = number_format($isi1 - $isi2 + $isi3);
		echo "<input readonly type='text' value='" . $isi . "' class='form-control' id='subtotal'  onkeyup required name='subtotal' >";
	}
	function CariMinHarga()
	{
		$hargatotal = str_replace(',', '', $_GET['hargatotal']);
		$jumlahharga = str_replace(',', '', $_GET['jumlahharga']);
		$isi = number_format($hargatotal - $jumlahharga);
		echo "<input readonly type='text' value='" . $isi . "' class='form-control' id='hargatotal'  onkeyup required name='hargatotal' >";
	}
	function CariMinDiskon()
	{
		$diskontotal = str_replace(',', '', $_GET['diskontotal']);
		$diskon = str_replace(',', '', $_GET['diskon']) / 100;
		$jumlahharga = str_replace(',', '', $_GET['jumlahharga']);
		$val1 = $jumlahharga * $diskon;
		$isi = number_format($val1 - $diskontotal);
		echo "<input readonly type='text' value='" . $isi . "' class='form-control' id='diskontotal'  onkeyup required name='diskontotal' >";
	}
	function CariMinPajak()
	{
		$taxtotal = str_replace(',', '', $_GET['taxtotal']);
		$pajak = str_replace(',', '', $_GET['pajak']) / 100;
		$jumlahharga = str_replace(',', '', $_GET['jumlahharga']);
		$val1 = $jumlahharga * $pajak;
		$isi = number_format($taxtotal - $val1);
		echo "<input readonly type='text' value='" . $isi . "' class='form-control' id='taxtotal'  onkeyup required name='taxtotal' >";
	}
	function CariMinSum()
	{
		$taxtotal = str_replace(',', '', $_GET['taxtotal']);
		$pajak = str_replace(',', '', $_GET['pajak']) / 100;
		$jumlahharga = str_replace(',', '', $_GET['jumlahharga']);
		$val1 = $jumlahharga * $pajak;
		$isi1 = $val1 - $taxtotal;
		$diskontotal = str_replace(',', '', $_GET['diskontotal']);
		$diskon = str_replace(',', '', $_GET['diskon']) / 100;
		$val2 = $jumlahharga * $diskon;
		$isi2 = $val2 - $diskontotal;
		$hargatotal = str_replace(',', '', $_GET['hargatotal']);
		$isi3 = $hargatotal - $jumlahharga;
		$isi = number_format($isi1 - $isi2 + $isi3);
		echo "<input readonly type='text' value='" . $isi . "' class='form-control' id='subtotal'  onkeyup required name='subtotal' >";
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
			$aaa = 'huhu';
		} else {
			$profitaa	= $this->db->query("SELECT * FROM ms_profit_material WHERE  alloy = '$inven1' AND minimum < '$berat' AND maksimum >= '$berat'  ")->result();
			$nilai_profit = $profitaa[0]->profit;
			$aaa = 'hihi';
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
		$this->db->where('no_po', $id)->update("tr_purchase_order", $data);

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

		$this->db->trans_start();

		$po_pr_depart = 0;
		$po_pr_asset = 0;
		$po_project_consultant = 0;
		foreach ($_POST['dt'] as $used) {
			// if ($po_pr_depart == '0') {
			if ($used['tipe_pr'] == 'pr depart') {
				$po_pr_depart = '1';
			}
			// }
			if ($used['tipe_pr'] == 'pr asset') {
				$po_pr_asset = '1';
			}
			if ($used['tipe_pr'] == 'project consultant') {
				$po_project_consultant = '1';
			}
		}
		if ($po_pr_depart == '1') {
			$data = [
				'no_po'				=> $code,
				'no_surat'			=> $no_surat,
				'id_suplier'		=> $post['supplier'],
				'loi'				=> $post['loi'],
				'nominal_kurs'		=> 1,
				'tanggal'			=> $post['tanggal'],
				'expect_tanggal'	=> date('Y-m-d', strtotime($post['expect_tanggal'])),
				'term'				=> $post['term'],
				'cif'				=> $post['cif'],
				'note'				=> $post['note_ket'],
				'no_pr'				=> $post['no_pr'],
				'matauang'			=> $post['matauang'],
				'hargatotal'		=> str_replace(',', '', $post['hargatotal']),
				'diskontotal'		=> str_replace(',', '', $post['diskontotal']),
				'taxtotal'			=> str_replace(',', '', $post['kirim']),
				'subtotal'			=> str_replace(',', '', $post['subtotal']),
				'total_ppn'			=> str_replace(',', '', $post['totalppn']),
				'total_barang'		=> str_replace(',', '', $post['hargatotal']),
				'status'			=> '1',
				'total_ppn_persen'	=> str_replace(',', '', $post['persenppn']),
				'persen_disc' => str_replace(',', '', $post['persendisc']),
				'nilai_disc' => str_replace(',', '', $post['totaldisc']),
				'note' => $post['note'],
				'delivery_date' => $post['delivery_date'],
				'id_dept' => implode(',', $post['dept']),
				'created_on'		=> date('Y-m-d H:i:s'),
				'created_by'		=> $this->auth->user_id(),
				'tipe'		=> 'pr depart'
			];
		} else if ($po_pr_asset == '1') {
			$data = [
				'no_po'				=> $code,
				'no_surat'			=> $no_surat,
				'id_suplier'		=> $post['supplier'],
				'loi'				=> $post['loi'],
				'nominal_kurs'		=> 1,
				'tanggal'			=> $post['tanggal'],
				'expect_tanggal'	=> date('Y-m-d', strtotime($post['expect_tanggal'])),
				'term'				=> $post['term'],
				'cif'				=> $post['cif'],
				'note'				=> $post['note_ket'],
				'no_pr'				=> $post['no_pr'],
				'matauang'			=> $post['matauang'],
				'hargatotal'		=> str_replace(',', '', $post['hargatotal']),
				'diskontotal'		=> str_replace(',', '', $post['diskontotal']),
				'taxtotal'			=> str_replace(',', '', $post['kirim']),
				'subtotal'			=> str_replace(',', '', $post['subtotal']),
				'total_ppn'			=> str_replace(',', '', $post['totalppn']),
				'total_barang'		=> str_replace(',', '', $post['hargatotal']),
				'status'			=> '1',
				'total_ppn_persen'	=> str_replace(',', '', $post['persenppn']),
				'persen_disc' => str_replace(',', '', $post['persendisc']),
				'nilai_disc' => str_replace(',', '', $post['totaldisc']),
				'note' => $post['note'],
				'delivery_date' => $post['delivery_date'],
				'id_dept' => implode(',', $post['dept']),
				'created_on'		=> date('Y-m-d H:i:s'),
				'created_by'		=> $this->auth->user_id(),
				'tipe'		=> 'pr asset'
			];
		} else if ($po_project_consultant == 1) {
			$data = [
				'no_po'				=> $code,
				'no_surat'			=> $no_surat,
				'id_suplier'		=> $post['supplier'],
				'loi'				=> $post['loi'],
				'nominal_kurs'		=> 1,
				'tanggal'			=> $post['tanggal'],
				'expect_tanggal'	=> date('Y-m-d', strtotime($post['expect_tanggal'])),
				'term'				=> $post['term'],
				'cif'				=> $post['cif'],
				'note'				=> $post['note_ket'],
				'no_pr'				=> $post['no_pr'],
				'matauang'			=> $post['matauang'],
				'hargatotal'		=> str_replace(',', '', $post['hargatotal']),
				'diskontotal'		=> str_replace(',', '', $post['diskontotal']),
				'taxtotal'			=> str_replace(',', '', $post['kirim']),
				'subtotal'			=> str_replace(',', '', $post['subtotal']),
				'total_ppn'			=> str_replace(',', '', $post['totalppn']),
				'total_barang'		=> str_replace(',', '', $post['hargatotal']),
				'status'			=> '1',
				'total_ppn_persen'	=> str_replace(',', '', $post['persenppn']),
				'persen_disc' => str_replace(',', '', $post['persendisc']),
				'nilai_disc' => str_replace(',', '', $post['totaldisc']),
				'note' => $post['note'],
				'delivery_date' => $post['delivery_date'],
				'id_dept' => implode(',', $post['dept']),
				'created_on'		=> date('Y-m-d H:i:s'),
				'created_by'		=> $this->auth->user_id(),
				'tipe'		=> 'project consultant'
			];
		} else {
			$data = [
				'no_po'				=> $code,
				'no_surat'			=> $no_surat,
				'id_suplier'		=> $post['supplier'],
				'loi'				=> $post['loi'],
				'nominal_kurs'		=> 1,
				'tanggal'			=> $post['tanggal'],
				'expect_tanggal'	=> date('Y-m-d', strtotime($post['expect_tanggal'])),
				'term'				=> $post['term'],
				'cif'				=> $post['cif'],
				'note'				=> $post['note_ket'],
				'no_pr'				=> $post['no_pr'],
				'matauang'			=> $post['matauang'],
				'hargatotal'		=> str_replace(',', '', $post['hargatotal']),
				'diskontotal'		=> str_replace(',', '', $post['diskontotal']),
				'taxtotal'			=> str_replace(',', '', $post['kirim']),
				'subtotal'			=> str_replace(',', '', $post['subtotal']),
				'total_ppn'			=> str_replace(',', '', $post['totalppn']),
				'total_barang'		=> str_replace(',', '', $post['hargatotal']),
				'status'			=> '1',
				'total_ppn_persen'	=> str_replace(',', '', $post['persenppn']),
				'persen_disc' => str_replace(',', '', $post['persendisc']),
				'nilai_disc' => str_replace(',', '', $post['totaldisc']),
				'note' => $post['note'],
				'delivery_date' => $post['delivery_date'],
				'id_dept' => implode(',', $post['dept']),
				'created_on'		=> date('Y-m-d H:i:s'),
				'created_by'		=> $this->auth->user_id()
			];
		}
		//Add Data
		$insert_tr_purchase_order = $this->db->insert('tr_purchase_order', $data);
		// if(!$insert_tr_purchase_order){
		// 	print_r($this->db->error($insert_tr_purchase_order));
		// 	exit;
		// }

		$valid_qty = 1;
		$numb1 = 0;
		$get_material = $this->db->select('code_lv4, nama')
			->from('new_inventory_4')
			->where('code_lv4', $used['idmaterial'])
			->or_where('id', $used['idmaterial'])
			->get()
			->row_array();
		// $id_material = $get_material['code_lv4'];
		// $nm_material = '';
		foreach ($_POST['dt'] as $used) {
			if (isset($used['checked_point'])) {
				$numb1++;
				$dt =  array(
					'no_po'					=> $code,
					'id_dt_po'				=> $code . '-' . $numb1,
					'idpr'					=> $used['idpr'],
					'idmaterial'			=> $used['idmaterial'],
					'namamaterial'			=> $used['namamaterial'],
					'description'			=> $used['description'],
					'qty'					=> $used['qty'],
					'width'					=> str_replace(",", "", $used['width']),
					'rate_lme'				=> $used['ratelme'],
					'fabcost'				=> str_replace(",", "", $used['fabcost']),
					'alloyprice'			=> str_replace(",", "", $used['alloyprice']),
					'totalwidth'			=> str_replace(",", "", $used['totalweight']),
					'hargasatuan'			=> str_replace(",", "", $used['hargasatuan']),
					'lebar'					=> $used['lebar'],
					'panjang'				=> str_replace(",", "", $used['length']),
					'diskon'				=> str_replace(",", "", $used['diskon']),
					'pajak'					=> str_replace(",", "", $used['pajak']),
					'jumlahharga'			=> str_replace(",", "", $used['jumlahharga']),
					'ppn'					=> str_replace(",", "", $used['nilai_ppn']),
					'ppn_persen'			=> str_replace(",", "", $used['persen_ppn']),
					'persen_disc'			=> str_replace(",", "", $used['disc_persen']),
					'nilai_disc'			=> str_replace(",", "", $used['disc_num']),
					'harga_total'			=> str_replace(",", "", $used['totalharga']),
					'note'					=> $used['note'],
					'kode_barang'			=> $used['kode_barang'],
					'tipe'					=> $used['tipe_pr']
				);

				$insert_dt_trans_po = $this->db->insert('dt_trans_po', $dt);
				// if(!$insert_dt_trans_po){
				// 	print_r($this->db->error($insert_dt_trans_po));
				// 	exit;
				// }
				// $nopr = $used[no_pr];
				$dataupdate = [
					'status_po'				=> 'CLS',
				];

				if ($valid_qty == 1 && $used['tipe_pr'] !== 'project consultant') {
					$get_other_po_brg = $this->db->query("SELECT IF(SUM(a.qty) IS NOT NULL, SUM(a.qty), 0) AS other_qty FROM dt_trans_po a WHERE a.idpr = '" . $used['idpr'] . "' AND a.tipe = '" . $used['tipe_pr'] . "'")->row();

					if ($used['tipe_pr'] == 'pr depart') {
						$get_data_pr = $this->db->query("SELECT IF(qty IS NOT NULL, qty, 0) AS qty_pr FROM rutin_non_planning_detail WHERE id = '" . $used['idpr'] . "'")->row();
					} else if ($used['tipe_pr'] == 'pr asset') {
						$get_data_pr = $this->db->query("SELECT IF(rev_qty IS NOT NULL, rev_qty, 0) AS qty_pr FROM asset_planning WHERE id = '" . $used['idpr'] . "'")->row();
					} else {
						$get_data_pr = $this->db->query("SELECT IF(propose_purchase IS NOT NULL, propose_purchase, 0) AS qty_pr FROM material_planning_base_on_produksi_detail WHERE id = '" . $used['idpr'] . "'")->row();
					}
					// print_r($get_other_po_brg->other_qty.' - '.$get_data_pr->qty_pr);
					// print_r($used[tipe_pr]);
					// exit;


					$qty_all = ($used['qty'] + $get_other_po_brg->other_qty);
					// print($used[idpr].' ('. $used[qty] . ' + '.$get_other_po_brg->other_qty.') - ' . $get_data_pr->qty_pr.'<br>');
					if ($get_other_po_brg->other_qty > $get_data_pr->qty_pr) {
						$valid_qty = 0;
					}
				}

				// $this->db->where('no_pr', $nopr)->update("tr_purchase_request", $dataupdate);
			}
		}

		// Untuk PR Material
		$this->db->select('*');
		$this->db->from('material_planning_base_on_produksi');
		$this->db->where_in('so_number', explode(',', $post['so_number']));
		$countMat = $this->db->get()->row_array();
		if (count($countMat) > 0) {
			$update_material_planning = $this->db->where_in('so_number', explode(',', $post['so_number']))->update('material_planning_base_on_produksi', ['po_number' => $code, 'po_date' => date('Y-m-d')]);

			// if(!$update_material_planning){
			// 	print_r($update_material_planning);
			// 	exit;
			// }
		}

		// Untuk PR Departemen
		$this->db->select('*');
		$this->db->from('rutin_non_planning_header');
		$this->db->where_in('no_pengajuan', explode(',', $post['so_number']));
		$countDept = $this->db->get()->row_array();
		if (count($countDept) > 0) {
			$update_rutin_non_planning = $this->db->where_in('no_pengajuan', explode(',', $post['so_number']))->update('rutin_non_planning_header', ['po_number' => $code, 'po_date' => date('Y-m-d')]);
			if (!$update_rutin_non_planning) {
				print_r($this->db->error($update_rutin_non_planning));
				exit;
			}
		}

		$num_top = $this->input->post('num_top');
		// if($num_top < 1 || $num_top == '') {
		// 	$num_top = 1;
		// }

		if ($num_top > 0 && $num_top !== '') {
			for ($i = 1; $i <= $num_top; $i++) {
				if (isset($post['group_top_' . $i])) {
					$group_top = $this->input->post('group_top_' . $i);
					$progress = $this->input->post('progress_' . $i);
					$nilai_top = $this->input->post('nilai_top_' . $i);
					$keterangan_top = $this->input->post('keterangan_top_' . $i);

					// print_r($num_top.'<br>');

					$insert_top = $this->db->insert('tr_top_po', [
						'no_po' => $code,
						'group_top' => $group_top,
						'progress' => str_replace(',', '', $progress),
						'nilai' => str_replace(',', '', $nilai_top),
						'keterangan' => $keterangan_top,
						'created_by' => $this->auth->user_id(),
						'created_on' => date('Y-m-d H:i:s')
					]);
					if (!$insert_top) {
						print_r($this->db->error($insert_top));
						exit;
					}
				}
			}
		}
		// exit;

		if ($this->db->trans_status() === FALSE || $valid_qty == '0') {
			// print_r($this->db->trans_status());
			// exit;
			$this->db->trans_rollback();
			$msg = 'Gagal Save Item. Thanks ...';
			if ($valid_qty == '0') {
				$msg = 'Maaf, ada PO Qty yang melebihi Qty PR !';
			}
			$status	= array(
				'pesan'		=> $msg,
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

	public function SaveEditPO()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();

		$tgl  = $post['tanggal'];
		$code = $this->Pr_model->generate_code($tgl);
		$no_surat = $this->Pr_model->BuatNomor($tgl);

		$this->db->trans_begin();

		$get_po = $this->db->get_where('tr_purchase_order', ['no_po' => $post['no_po']])->row();

		$data = [
			'id_suplier'		=> $post['supplier'],
			'loi'				=> $post['loi'],
			'nominal_kurs'		=> 1,
			'tanggal'			=> $post['tanggal'],
			'expect_tanggal'	=> date('Y-m-d', strtotime($post['expect_tanggal'])),
			'term'				=> $post['term'],
			'cif'				=> $post['cif'],
			'note'				=> $post['note_ket'],
			'no_pr'				=> $post['no_pr'],
			'matauang'			=> $post['matauang'],
			'hargatotal'		=> str_replace(',', '', $post['hargatotal']),
			'diskontotal'		=> str_replace(',', '', $post['diskontotal']),
			'taxtotal'			=> str_replace(',', '', $post['kirim']),
			'subtotal'			=> str_replace(',', '', $post['subtotal']),
			'total_ppn'			=> str_replace(',', '', $post['totalppn']),
			'total_barang'		=> str_replace(',', '', $post['hargatotal']),
			'status'			=> '1',
			'revisi'			=> ($get_po->revisi + 1),
			'reject_reason'			=> '',
			'total_ppn_persen'	=> str_replace(',', '', $post['persenppn']),
			'persen_disc'	=> str_replace(',', '', $post['persendisc']),
			'nilai_disc'	=> str_replace(',', '', $post['totaldisc']),
			'id_dept' => implode(',', $post['dept']),
			'delivery_date' => $post['delivery_date'],
			'note' => $post['note']
		];
		//Add Data
		$this->db->update('tr_purchase_order', $data, ['no_po' => $post['no_po']]);

		$valid_qty = 1;
		$numb1 = 0;
		foreach ($_POST['dt'] as $used) {
			$numb1++;

			$dt =  array(
				'description'			=> $used['description'],
				'qty'					=> $used['qty'],
				'hargasatuan'			=> str_replace(",", "", $used['hargasatuan']),
				'jumlahharga'			=> str_replace(",", "", $used['jumlahharga']),
				'ppn'					=> str_replace(",", "", $used['nilai_ppn']),
				'ppn_persen'			=> str_replace(",", "", $used['persen_ppn']),
				'persen_disc' 			=> str_replace(",", "", $used['disc_persen']),
				'nilai_disc' 			=> str_replace(",", "", $used['disc_num']),
				'harga_total'			=> str_replace(",", "", $used['totalharga']),
				'note'					=> $used['note']
			);

			if ($valid_qty == 1 && $used['tipe_pr'] !== 'project consultant') {
				$get_other_po_brg = $this->db->query("SELECT IF(SUM(a.qty) IS NOT NULL, SUM(a.qty), 0) AS other_qty FROM dt_trans_po a WHERE a.idpr = '" . $used['idpr'] . "' AND a.tipe = '" . $used['tipe_pr'] . "'")->row();

				if ($used['tipe_pr'] == 'pr depart') {
					$get_data_pr = $this->db->query("SELECT IF(qty IS NOT NULL, qty, 0) AS qty_pr FROM rutin_non_planning_detail WHERE id = '" . $used['idpr'] . "'")->row();
				} else {
					$get_data_pr = $this->db->query("SELECT IF(propose_purchase IS NOT NULL, propose_purchase, 0) AS qty_pr FROM material_planning_base_on_produksi_detail WHERE id = '" . $used['idpr'] . "'")->row();
				}



				$qty_all = ($used['qty']);

				// $qty_all = ($used[qty] + $get_other_po_brg->other_qty);
				// print($used[idpr].' ('. $used[qty] . ' + '.$get_other_po_brg->other_qty.') - ' . $get_data_pr->qty_pr.'<br>');
				if ($qty_all > $get_data_pr->qty_pr) {
					$valid_qty = 0;
				}
			}

			// print_r($used[id]);
			// exit;

			$this->db->update('dt_trans_po', $dt, ['id' => $used['id']]);
			// $nopr = $used[no_pr];
			$dataupdate = [
				'status_po'				=> 'CLS',
			];

			// $this->db->where('no_pr', $nopr)->update("tr_purchase_request", $dataupdate);
		}
		// $this->db->where_in('so_number', explode(',', $post['so_number']))->update('material_planning_base_on_produksi', ['po_number' => $code, 'po_date' => date('Y-m-d')]);

		$del_top_po = $this->db->delete('tr_top_po', ['no_po' => $post['no_po']]);

		$num_top = $this->input->post('num_top');
		// if($num_top < 1 || $num_top == '') {
		// 	$num_top = 1;
		// }

		if ($num_top > 0 && $num_top !== '') {
			for ($i = 1; $i <= $num_top; $i++) {
				if (isset($post['group_top_' . $i])) {
					$group_top = $this->input->post('group_top_' . $i);
					$progress = $this->input->post('progress_' . $i);
					$nilai_top = $this->input->post('nilai_top_' . $i);
					$keterangan_top = $this->input->post('keterangan_top_' . $i);

					// print_r($num_top.'<br>');

					$insert_top = $this->db->insert('tr_top_po', [
						'no_po' => $post['no_po'],
						'group_top' => $group_top,
						'progress' => str_replace(',', '', $progress),
						'nilai' => str_replace(',', '', $nilai_top),
						'keterangan' => $keterangan_top,
						'created_by' => $this->auth->user_id(),
						'created_on' => date('Y-m-d H:i:s')
					]);
					if (!$insert_top) {
						print_r($this->db->error($insert_top));
						exit;
					}
				}
			}
		}

		if ($this->db->trans_status() === FALSE || $valid_qty == '0') {
			$this->db->trans_rollback();
			$msg = 'Gagal Save Item. Thanks ...';
			if ($valid_qty == '0') {
				$msg = 'Maaf, ada PO Qty yang melebihi Qty PR !';
			}
			$status	= array(
				'pesan'		=> $msg,
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
		$code = $post['no_po'];
		$no_surat =  $post['no_surat'];
		$this->db->trans_begin();
		$data = [
			'no_po'				=> $code,
			'no_surat'			=> $no_surat,
			'id_suplier'		=> $post['id_suplier'],
			'loi'				=> $post['loi'],
			'nominal_kurs'		=> 1,
			'tanggal'			=> $post['tanggal'],
			'expect_tanggal'	=> date('Y-m-d', strtotime($post['expect_tanggal'])),
			'term'				=> $post['term'],
			'cif'				=> $post['cif'],
			'note'				=> $post['note_ket'],
			'no_pr'				=> $post['no_pr'],
			'matauang'			=> $post['matauang'],
			'hargatotal'		=> str_replace(',', '', $post['hargatotal']),
			'diskontotal'		=> str_replace(',', '', $post['diskontotal']),
			'taxtotal'			=> str_replace(',', '', $post['taxtotal']),
			'subtotal'			=> str_replace(',', '', $post['subtotal']),
			'status'			=> '1',
			'created_on'		=> date('Y-m-d H:i:s'),
			'created_by'		=> $this->auth->user_id()
		];
		//Add Data 
		$this->db->where('no_po', $code)->update("tr_purchase_order", $data);
		$this->db->delete('dt_trans_po', array('no_po' => $code));
		$numb1 = 0;
		foreach ($_POST['dt'] as $used) {
			$numb1++;
			$dt =  array(
				'no_po'					=> $code,
				'id_dt_po'				=> $code . '-' . $numb1,
				'idpr'					=> $used['idpr'],
				'idmaterial'			=> $used['idmaterial'],
				'namamaterial'			=> $used['namamaterial'],
				'description'			=> $used['description'],
				'qty'					=> $used['qty'],
				'width'					=> str_replace(",", "", $used['width']),
				'rate_lme'				=> $used['ratelme'],
				'fabcost'				=> str_replace(",", "", $used['fabcost']),
				'alloyprice'			=> str_replace(",", "", $used['alloyprice']),
				'totalwidth'			=> str_replace(",", "", $used['totalweight']),
				'hargasatuan'			=> str_replace(",", "", $used['hargasatuan']),
				'lebar'					=> $used['lebar'],
				'panjang'				=> str_replace(",", "", $used['length']),
				'diskon'				=> str_replace(",", "", $used['diskon']),
				'pajak'					=> str_replace(",", "", $used['pajak']),
				'jumlahharga'			=> str_replace(",", "", $used['jumlahharga']),
				'note'					=> $used['note'],
			);
			$this->db->insert('dt_trans_po', $dt);
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
	public function PrintH()
	{
		ob_clean();
		ob_start();
		$this->auth->restrict($this->managePermission);
		$id = $this->uri->segment(3);
		$data['header'] = $this->db->query("SELECT a.*, b.name_suplier as name_suplier, b.address_office as address_office,b.id_negara as negara, b.telephone as telephone,b.fax as fax FROM tr_purchase_order as a INNER JOIN master_supplier as b on a.id_suplier = b.id_suplier WHERE a.no_po = '" . $id . "' ")->result();
		$data['detail']  = $this->db->query("SELECT * FROM dt_trans_po WHERE no_po = '" . $id . "' ")->result();
		$data['detailsum'] = $this->db->query("SELECT AVG(width) as totalwidth, AVG(qty) as totalqty FROM dt_trans_po WHERE no_po = '" . $id . "' ")->result();
		$this->load->view('print', $data);
		$html = ob_get_contents();

		require_once('./assets/html2pdf/html2pdf/html2pdf.class.php');
		$html2pdf = new HTML2PDF('P', 'A4', 'en', true, 'UTF-8', array(0, 0, 0, 0));
		$html2pdf->pdf->SetDisplayMode('fullpage');
		$html2pdf->WriteHTML($html);
		ob_end_clean();
		$html2pdf->Output('Penawran.pdf', 'I');
	}

	public function PrintH2()
	{
		ob_clean();
		ob_start();
		$this->auth->restrict($this->managePermission);
		$id = $this->uri->segment(3);
		$data['header'] = $this->db->query("SELECT a.*, a.id_suplier, b.nama as nm_supp, b.address as alamat, c.name as country_name, b.contact as nm_pic, b.telp as hp, b.email as email_pic, b.fax FROM tr_purchase_order as a LEFT JOIN material_planning_base_on_produksi x ON x.po_number = a.no_po LEFT JOIN new_supplier b ON b.kode_supplier = a.id_suplier LEFT JOIN country_all c ON c.iso3 = b.id_country WHERE a.no_po = '" . $id . "' ")->result();
		// print_r($data['header'][0]->tipe);
		// exit;
		if ($data['header'][0]->tipe !== '' && $data['header'][0]->tipe !== null) {
			if ($data['header'][0]->tipe == 'pr depart') {
				$data['detail']  = $this->db->query("SELECT a.id as id, a.no_po as no_po, a.id_dt_po as id_dt_po, a.idpr as idpr, a.idmaterial as idmaterial, a.namamaterial as namamaterial, a.description as description, a.hargasatuan as hargasatuan, a.jumlahharga as jumlahharga, a.kode_barang as kode_barang, a.ppn as ppn, a.ppn_persen as ppn_persen, a.harga_total as harga_total, a.tipe as tipe, a.keterangan as keterangan, a.namamaterial as nama, '' as code, '1' as konversi, c.code as satuan, c.code as satuan_packing, a.qty as qty FROM dt_trans_po a 
				LEFT JOIN rutin_non_planning_detail b ON b.id = a.idpr
				LEFT JOIN ms_satuan c ON c.id = b.satuan
				WHERE a.no_po = '" . $id . "' ")->result();
			}
			if ($data['header'][0]->tipe == 'pr asset') {
				$data['detail']  = $this->db->query("SELECT a.id as id, a.no_po as no_po, a.id_dt_po as id_dt_po, a.idpr as idpr, a.idmaterial as idmaterial, a.namamaterial as namamaterial, a.description as description, a.hargasatuan as hargasatuan, a.jumlahharga as jumlahharga, a.kode_barang as kode_barang, a.ppn as ppn, a.ppn_persen as ppn_persen, a.harga_total as harga_total, a.tipe as tipe, a.keterangan as keterangan, a.namamaterial as nama, '' as code, '1' as konversi, 'Pcs' as satuan, 'Pcs' as satuan_packing, a.qty as qty FROM dt_trans_po a 
				LEFT JOIN asset_planning b ON b.id = a.idpr
				WHERE a.no_po = '" . $id . "' ")->result();
			}

			$list_idpr = [];
			$list_idpr_non = [];
			foreach ($data['detail'] as $item) {
				if ($item->tipe == 'pr depart' || $item->tipe == 'pr asset') {
					$list_idpr[] = $item->idpr;
				} else {
					$list_idpr_non[] = $item->idpr;
				}
			}

			if ($data['header'][0]->tipe == 'pr depart') {
				$this->db->select('IF(a.nama IS NULL, "", a.nama) as nm_department');
				$this->db->from('ms_department a');
				$this->db->join('rutin_non_planning_header b', 'b.id_dept = a.id', 'left');
				$this->db->join('rutin_non_planning_detail c', 'c.no_pengajuan = b.no_pengajuan', 'left');
				$this->db->where('c.id', $data['detail'][0]->idpr);
				$get_department = $this->db->get()->row();
			}
			if ($data['header'][0]->tipe == 'pr asset') {
				$this->db->select('IF(a.nama IS NULL, "", a.nama) as nm_department');
				$this->db->from('ms_department a');
				$this->db->join('asset_planning b', 'b.id_dept = a.id', 'left');
				$this->db->where('b.id', $data['detail'][0]->idpr);
				$get_department = $this->db->get()->row();
			}



			$data['nm_department'] = $get_department->nm_department;

			if ($data['header'][0]->tipe == 'pr depart') {
				$this->db->select('IF(a.no_pr IS NULL, "", a.no_pr) as no_pr');
				$this->db->from('rutin_non_planning_header a');
				$this->db->join('rutin_non_planning_detail b', 'b.no_pengajuan = a.no_pengajuan', 'left');
				$this->db->where_in('b.id', $list_idpr);
				$this->db->group_by('a.no_pr');
				$get_pr = $this->db->get()->result();
			}
			if ($data['header'][0]->tipe == 'pr asset') {
				$this->db->select('IF(a.no_pr IS NULL, "", a.no_pr) as no_pr');
				$this->db->from('asset_planning a');
				$this->db->where_in('a.id', $list_idpr);
				$this->db->group_by('a.no_pr');
				$get_pr = $this->db->get()->result();

				// print_r($this->db->last_query());
				// exit;
			}

			$list_no_pr = [];
			foreach ($get_pr as $item) {
				if ($item->no_pr !== '') {
					$list_no_pr[] = $item->no_pr;
				}
			}

			if (count($list_idpr_non) > 0) {
				$this->db->select('IF(a.no_pr IS NULL, "", a.no_pr) as no_pr');
				$this->db->from('material_planning_base_on_produksi a');
				$this->db->join('material_planning_base_on_produksi_detail b', 'b.so_number = a.so_number', 'left');
				$this->db->where_in('b.id', $list_idpr_non);
				$this->db->group_by('a.no_pr');
				$get_pr_non = $this->db->get()->result();

				foreach ($get_pr_non as $item) {
					if ($item->no_pr !== '') {
						$list_no_pr[] = $item->no_pr;
					}
				}
			}


			$data['no_pr'] = implode(', ', $list_no_pr);

			// print_r($data['header'][0]->tipe);
			// exit;
		} else {
			$data['detail']  = $this->db->query("SELECT a.*, 
				a.namamaterial as nama, IF(b.code IS NULL OR b.code = '', e.id_stock, b.code), IF(b.konversi IS NULL, 1, b.konversi), c.code as satuan, d.code as satuan_packing FROM dt_trans_po a 
			LEFT JOIN new_inventory_4 b ON b.code_lv4 = a.idmaterial OR b.id = a.idmaterial
			LEFT JOIN ms_satuan c ON c.id = b.id_unit
			LEFT JOIN ms_satuan d ON d.id = b.id_unit_packing
			LEFT JOIN accessories e ON e.id = a.idmaterial
			LEFT JOIN ms_satuan f ON f.id = e.id_unit
			WHERE a.no_po = '" . $id . "' ")->result();

			$list_idpr = [];
			$list_idpr_dep = [];
			foreach ($data['detail'] as $item) {
				if ($item->tipe == 'pr_depart') {
					$list_idpr_dep[] = $item->idpr;
				} else {
					$list_idpr[] = $item->idpr;
				}
			}

			$get_department = $this->db->select('IF(nama IS NULL, "", nama) as nama')->get_where('ms_department', ['id' => $data['header'][0]->id_dept])->row();
			$data['nm_department'] = $get_department->nama;

			$this->db->select('IF(a.no_pr IS NULL, "", a.no_pr) as no_pr');
			$this->db->from('material_planning_base_on_produksi a');
			$this->db->join('material_planning_base_on_produksi_detail b', 'b.so_number = a.so_number', 'left');
			$this->db->where_in('b.id', $list_idpr);
			$this->db->group_by('a.no_pr');
			$get_pr = $this->db->get()->result();

			$list_no_pr = [];
			foreach ($get_pr as $item) {
				if ($item->no_pr !== '') {
					$list_no_pr[] = $item->no_pr;
				}
			}

			if (count($list_idpr_dep) > 0) {
				$this->db->select('IF(a.no_pr IS NULL, "", a.no_pr) as no_pr');
				$this->db->from('rutin_non_planning_header a');
				$this->db->join('rutin_non_planning_detail b', 'b.no_pengajuan = a.no_pengajuan', 'left');
				$this->db->where_in('b.id', $list_idpr_dep);
				$this->db->group_by('a.no_pr');
				$get_pr_dep = $this->db->get()->result();


				foreach ($get_pr_dep as $item) {
					if ($item->no_pr !== '') {
						$list_no_pr[] = $item->no_pr;
					}
				}
			}

			$data['no_pr'] = implode(', ', $list_no_pr);
		}

		// print_r("SELECT a.id as id, a.no_po as no_po, a.id_dt_po as id_dt_po, a.idpr as idpr, a.idmaterial as idmaterial, a.namamaterial as namamaterial, a.description as description, a.hargasatuan as hargasatuan, a.jumlahharga as jumlahharga, a.kode_barang as kode_barang, a.ppn as ppn, a.ppn_persen as ppn_persen, a.harga_total as harga_total, a.tipe as tipe, a.keterangan as keterangan, a.namamaterial as nama, a.kode_barang as code, '1' as konversi, c.code as satuan, '' as satuan_packing FROM dt_trans_po a 
		// INNER JOIN rutin_non_planning_detail b ON b.id = a.idpr 
		// LEFT JOIN ms_satuan c ON c.id = b.satuan
		// WHERE a.no_po = '" . $id . "' ");
		// exit;
		$data['detailsum'] = $this->db->query("SELECT AVG(width) as totalwidth, AVG(qty) as totalqty FROM dt_trans_po WHERE no_po = '" . $id . "' ")->result();

		$data['data_supplier'] = $this->db->get_where('new_supplier', ['kode_supplier' => $data['header'][0]->id_suplier])->row();

		$nm_depart = [];
		$get_nm_depart = $this->db->query("SELECT nama FROM ms_department WHERE id IN ('" . str_replace(",", "','", $data['header'][0]->id_dept) . "')")->result();
		if (!empty($get_nm_depart)) {
			foreach ($get_nm_depart as $item_depart) {
				$nm_depart[] = strtoupper($item_depart->nama);
			}
		}

		if (!empty($nm_depart)) {
			$nm_depart = implode(', ', $nm_depart);
		} else {
			$nm_depart = '';
		}

		$data['nm_depart'] = $nm_depart;

		$this->load->view('print2', $data);
		$html = ob_get_contents();

		require_once('./assets/html2pdf/html2pdf/html2pdf.class.php');
		$html2pdf = new HTML2PDF('P', 'A4', 'en', true, 'UTF-8', array(10, 5, 10, 5));
		$html2pdf->pdf->SetDisplayMode('fullpage');
		$html = ob_get_contents();

		$html2pdf->WriteHTML($html);

		ob_end_clean();
		$html2pdf->Output('Purchase Order.pdf', 'I');

		// $this->template->title('Testing');
		// $this->template->render('print2');
	}

	public function PrintH3($id)
	{

		$data = [
			'header' 	=> $this->db->query("SELECT a.*, b.name_suplier as name_suplier, b.address_office as address_office,b.id_negara as negara, b.telephone as telephone,b.fax as fax FROM tr_purchase_order as a INNER JOIN master_supplier as b on a.id_suplier = b.id_suplier WHERE a.no_po = '" . $id . "' ")->result(),
			'detail'  	=> $this->db->query("SELECT * FROM dt_trans_po WHERE no_po = '" . $id . "' ")->result(),
			'detailsum' => $this->db->query("SELECT AVG(width) as totalwidth, AVG(qty) as totalqty FROM dt_trans_po WHERE no_po = '" . $id . "' ")->result()
		];
		$this->load->view('print3', $data);
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
				'id_type'		        => $h1['inventory_1'],
				'id_category1'		    => $h1['inventory_2'],
				'id_category2'		    => $h1['inventory_3'],
				'nama'		        	=> $h1['nm_inventory'],
				'maker'		        	=> $h1['maker'],
				'density'		        => $h1['density'],
				'hardness'		        => $h1['hardness'],
				'id_bentuk'		        => $h1['id_bentuk'],
				'id_surface'		    => $h1['id_surface'],
				'mountly_forecast'		=> $h1['mountly_forecast'],
				'safety_stock'		    => $h1['safety_stock'],
				'order_point'		    => $h1['order_point'],
				'maksimum'		    	=> $h1['maksimum'],
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
					'id_category3' 	=> $id,
					'id_suplier' 	=> $d1['id_supplier'],
					'lead' 			=> $d1['lead'],
					'minimum' 		=> $d1['minimum'],
					'deleted' 		=> '0',
					'created_on' 	=> date('Y-m-d H:i:s'),
					'created_by' 	=> $this->auth->user_id(),
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
					'id_category3' 		=> $id,
					'id_compotition' 	=> $c1['id_compotition'],
					'nilai_compotition' => $c1['jumlah_kandungan'],
					'deleted' 			=> '0',
					'created_on' 		=> date('Y-m-d H:i:s'),
					'created_by' 		=> $this->auth->user_id(),
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
					'id_category3' 	=> $id,
					'id_dimensi' 	=> $dm['id_dimensi'],
					'nilai_dimensi' => $dm['nilai_dimensi'],
					'deleted' 		=> '0',
					'created_on' 	=> date('Y-m-d H:i:s'),
					'created_by' 	=> $this->auth->user_id(),
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
				'id_type'		        => $h1['inventory_1'],
				'id_category1'		    => $h1['inventory_2'],
				'id_category2'		    => $h1['inventory_3'],
				'nama'		        	=> $h1['nm_inventory'],
				'maker'		        	=> $h1['maker'],
				'density'		        => $h1['density'],
				'hardness'		        => $h1['hardness'],
				'id_bentuk'		        => $h1['id_bentuk'],
				'id_surface'		    => $h1['id_surface'],
				'mountly_forecast'		=> $h1['mountly_forecast'],
				'safety_stock'		    => $h1['safety_stock'],
				'order_point'		    => $h1['order_point'],
				'maksimum'		    	=> $h1['maksimum'],
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
					'id_suplier' => $d1['id_supplier'],
					'lead' => $d1['lead'],
					'minimum' => $d1['minimum'],
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
					'id_compotition' => $c1['id_compotition'],
					'nilai_compotition' => $c1['jumlah_kandungan'],
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
					'id_dimensi' => $dm['id_dimensi'],
					'nilai_dimensi' => $dm['nilai_dimensi'],
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
				'id_type'		        => $h1['inventory_1'],
				'id_category1'		    => $h1['inventory_2'],
				'id_category2'		    => $h1['inventory_3'],
				'nama'		        	=> $h1['nm_inventory'],
				'maker'		        	=> $h1['maker'],
				'density'		        => $h1['density'],
				'hardness'		        => $h1['hardness'],
				'id_bentuk'		        => $h1['id_bentuk'],
				'id_surface'		    => $h1['id_surface'],
				'mountly_forecast'		=> $h1['mountly_forecast'],
				'safety_stock'		    => $h1['safety_stock'],
				'order_point'		    => $h1['order_point'],
				'maksimum'		    	=> $h1['maksimum'],
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
					'id_suplier' => $d1['id_supplier'],
					'lead' => $d1['lead'],
					'minimum' => $d1['minimum'],
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
					'id_compotition' => $c1['id_compotition'],
					'nilai_compotition' => $c1['jumlah_kandungan'],
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
					'id_dimensi' => $dm['id_dimensi'],
					'nilai_dimensi' => $dm['nilai_dimensi'],
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

	function CariLokasi()
	{

		$supplier	= $_GET['supplier'];
		$GetSupp = $this->db->query("SELECT a.* FROM master_supplier as a  WHERE a.id_suplier = '$supplier' ")->result();
		$lokasi = $GetSupp[0]->suplier_location;

		if ($lokasi == 'international') {
			echo "<option value='Import' selected>Import</option>
		      <option value='Lokal'>Lokal</option>";
		} else {
			echo "<option value='Import'>Import</option>
	      <option value='Lokal' selected >Lokal</option>";
		};
	}

	public function getDateExp()
	{
		$id_pr 			= $this->input->post('id_pr');
		// print_r($expect_tanggal);
		$result			= $this->db
			->select('tanggal')
			->from('dt_trans_pr')
			->where_in('id_dt_pr', $id_pr)
			->get()
			->result();
		$expTgl			= date('Y-m-d', strtotime($expect_tanggal));

		$minimal	= $result[0]->tanggal;
		if (!empty($expect_tanggal)) {
			if ($expTgl < $minimal and $expTgl > date('Y-m-d')) {
				$minimal	= $expTgl;
			}
		} else {
			$minimal	= $result[0]->tanggal;
		}
		$ArrJson	= array(
			'minimal' => date('d-M-Y', strtotime($minimal))
		);
		echo json_encode($ArrJson);
	}

	public function getPR()
	{
		$id_suplier = $this->input->post('id_suplier');

		$no_po 		= (!empty($this->input->post('no_po'))) ? $this->input->post('no_po') : 0;

		$get_no_po 	= $this->db->get_where('tr_purchase_order', array('no_po' => $no_po))->result();
		$npo 		= (!empty($get_no_po)) ? $get_no_po[0]->no_pr : 0;

		$filter_pr 	= $this->db->get_where('tr_purchase_order', array('no_pr <>' => $npo))->result_array();


		$ArrPR = [];
		foreach ($filter_pr as $key => $value) {
			if (!empty($value['no_pr'])) {
				$ArrPR[$key] = $value['no_pr'];
			}
		}
		$dtImplode	= "('" . implode("','", $ArrPR) . "')";


		$data 		=  $this->db->query("	SELECT 
												c.* 
											FROM 
												dt_trans_pr b 
												LEFT JOIN tr_purchase_request c ON b.no_pr = c.no_pr 
											WHERE 
												c.status = '2' 
												AND b.suplier='" . $id_suplier . "' 
												AND c.no_pr NOT IN " . $dtImplode . "
											GROUP BY 
												b.no_pr ")->result_array();

		$option 	= "<option value='0'>Pilih PR</option>";
		foreach ($data as $key => $value) {
			$sel = ($npo == $value['no_pr']) ? 'selected' : '';
			$option .= "<option value='" . $value['no_pr'] . "'>" . $value['no_surat'] . "</option>";
		}

		$dataArr = [
			'option' => $option
		];

		echo json_encode($dataArr);
	}



	public function addPurchaseOrder($filter_status = null)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$customer = $this->Pr_model->get_data('customer');
		$this->template->set('customer', $customer);


		$data = $this->Pr_model->cariPurchaserequest($filter_status);
		// echo '<pre>';
		// print_r($data);
		// echo '</pre>';
		// die();

		$this->template->set('results', $data);
		if ($filter_status !== null) {
			$this->template->set('filter_status', $filter_status);
		}
		$this->template->title('Purchase Order');
		$this->template->render('index_pr');
	}

	public function proses()
	{
		$session = $this->session->userdata('app_session');
		$getparam = explode(";", $_GET['param']);

		$getso = $this->Pr_model->get_where_in('so_number', $getparam, 'material_planning_base_on_produksi');
		// $getitemso = $this->Pr_model->get_where_in('so_number', $getparam, 'material_planning_base_on_produksi_detail');

		// $getitemso = $this->db->select("a.*, (b.qty_stock - b.qty_booking) AS avl_stock, c.code as code, d.id_stock as code1, c.nama as nm_material, '' as nm_material1");
		// $getitemso = $this->db->from('material_planning_base_on_produksi_detail a');
		// $getitemso = $this->db->join('warehouse_stock b', 'b.id_material = a.id_material', 'left');
		// $getitemso = $this->db->join('new_inventory_4 c', 'c.code_lv4 = a.id_material', 'left');
		// $getitemso = $this->db->where_in('a.so_number', $getparam);
		// $getitemso = $this->db->group_by('a.id');

		$getitemso = $this->db->query("
			SELECT 
				a.id as id,
				a.so_number as so_number,
				a.id_material as id_material,
				a.propose_purchase as propose_purchase,
				(b.qty_stock - b.qty_booking) AS avl_stock, 
				IF(c.code = '' OR c.code IS NULL, d.id_stock, c.code) as code, 
				'' as code1, 
				IF(c.nama = '' OR c.nama IS NULL, d.stock_name, c.nama) as nm_material,
				'' as tipe_pr,
				e.code as packing_unit,	
				f.code as packing_unit2,
				IF(g.code IS NOT NULL, g.code, h.code) as unit_measure
			FROM
				material_planning_base_on_produksi_detail a
				LEFT JOIN warehouse_stock b ON b.id_material = a.id_material
				LEFT JOIN new_inventory_4 c ON c.code_lv4 = a.id_material 
				LEFT JOIN accessories d ON d.id = a.id_material
				LEFT JOIN ms_satuan e ON e.id = c.id_unit_packing
				LEFT JOIN ms_satuan f ON f.id = d.id_unit_gudang
				LEFT JOIN ms_satuan g ON g.id = c.id_unit
				LEFT JOIN ms_satuan h ON h.id = d.id_unit
			WHERE
				a.so_number IN ('" . str_replace(",", "','", implode(',', $getparam)) . "')
				AND a.status_app = 'Y'
			GROUP BY a.id_material

			UNION ALL

			SELECT
				a.id as id,
				a.no_pengajuan as so_number,
				'' as id_material,
				a.qty as propose_purchase,
				'0' as avl_stock,
				a.nm_barang as code,
				'' as code1,
				a.nm_barang as nm_material,
				'pr depart' as tipe_pr,
				b.code as packing_unit,
				'' as packing_unit2,
				b.code as unit_measure
			FROM
				rutin_non_planning_detail a 
				LEFT JOIN ms_satuan b ON b.id = a.satuan
			WHERE
				a.no_pengajuan IN ('" . str_replace(",", "','", implode(',', $getparam)) . "')
				
			GROUP BY a.id

			UNION ALL

			SELECT
				a.id as id,
				a.code_plan as so_number,
				'' as id_material,
				a.rev_qty as propose_purchase,
				0 as avl_stock,
				a.nama_asset as code,
				'' as code1,
				a.nama_asset as nm_material,
				'pr asset' as tipe_pr,
				'Pcs' as packing_unit,
				'' as packing_unit2,
				'Pcs' as unit_measure
			FROM
				asset_planning a 
			WHERE
				a.code_plan IN ('" . str_replace(",", "','", implode(',', $getparam)) . "')

			UNION ALL

			SELECT
				a.id as id,
				a.id as so_number,
				'' as id_material,
				1 as propose_purchase,
				0 as avl_stock,
				'' as code,
				'' as code1,
				a.deskripsi as nm_material,
				'project consultant' as tipe_pr,
				'Item' as packing_unit,
				'' as packing_unit2,
				'Item' as unit_measure
			FROM
				" . DBCNL . ".kons_tr_kasbon_project_header a
			WHERE
				a.id IN ('" . str_replace(",", "','", implode('m', $getparam)) . "')
		")->result();

		// print_r($this->db->last_query());
		// exit;


		// $getitemso = $this->db->get()->result();

		// print_r($getitemso);
		// exit;

		$aktif = 'active';
		$deleted = '0';
		// $supplier = $data = $this->db->query("SELECT a.* FROM new_supplier as a INNER JOIN dt_trans_pr as b on b.suplier = a.id_suplier INNER JOIN tr_purchase_request as c on b.no_pr = c.no_pr WHERE c.status = '2' GROUP BY b.suplier ")->result();

		// $comp	= $this->db->query("select a.*, b.nominal as nominal_harga FROM ms_compotition as a inner join child_history_lme as b on b.id_compotition=a.id_compotition where a.deleted='0' and b.status='0' ")->result();
		$customers = $this->db->get_where('customer', ['deleted_by' => null])->result();
		$karyawan = $this->db->get_where('ms_karyawan', ['deleted_by' => null])->result();
		$mata_uang = $this->db->get_where('mata_uang', ['deleted' => null])->result();
		$list_supplier = $this->db->get_where('new_supplier', ['deleted_by' => null])->result();
		$list_department = $this->db->select('id, nama')->get_where('ms_department', ['deleted_by' => null])->result();
		// $matauang = $this->db->get_where('matauang')->result();
		$data = [
			// 'supplier' => $supplier,
			// 'comp' => $comp,
			'customers' => $customers,
			'karyawan' => $karyawan,
			'mata_uang' => $mata_uang,
			// 'matauang' => $matauang,
			'param' => $getparam,
			// 'headerso' => $getso,
			'getitemso' => $getitemso,
			'list_supplier' => $list_supplier,
			'list_department' => $list_department
		];

		$this->template->set('results', $data);
		$this->template->title('Input Purchase Order');
		$this->template->render('add_purchaseorder');
	}

	public function po_input_notes()
	{
		$post = $this->input->post();

		$this->db->trans_begin();

		$this->db->update('tr_purchase_order', [
			'note' => $post['notes']
		], [
			'no_po' => $post['no_po']
		]);

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
		} else {
			$this->db->trans_commit();
		}
	}

	public function add_checked_pr()
	{
		$no_pr = $this->input->post('no_pr');
		$tipe_pr = $this->input->post('tipe_pr');



		$get_checked_pr = $this->db->get_where('tr_po_checked_pr', ['no_pr' => $no_pr, 'id_user' => $this->auth->user_id()])->result();

		if (count($get_checked_pr) < 1) {
			$this->db->trans_begin();

			$this->db->insert('tr_po_checked_pr', [
				'no_pr' => $no_pr,
				'id_user' => $this->auth->user_id(),
				'tipe_pr' => $tipe_pr
			]);

			if ($this->db->trans_status() === false) {
				$this->db->trans_rollback();
			} else {
				$this->db->trans_commit();
			}
		}
	}

	public function del_checked_pr()
	{
		$no_pr = $this->input->post('no_pr');

		$this->db->trans_begin();

		$this->db->delete('tr_po_checked_pr', ['no_pr' => $no_pr, 'id_user' => $this->auth->user_id()]);

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
		} else {
			$this->db->trans_commit();
		}
	}

	public function clear_checked_pr()
	{
		$id_user = $this->auth->user_id();

		$this->db->delete('tr_po_checked_pr', ['id_user' => $this->auth->user_id()]);

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
			$valid = 0;
			$msg = "Checked PR has not been removed !";
		} else {
			$this->db->trans_commit();
			$valid = 1;
			$msg = "Checked PR has been removed !";
		}

		echo json_encode([
			'status' => $valid,
			'pesan' => $msg
		]);
	}

	public function process_do()
	{
		$id_user = $this->auth->user_id();

		$get_checked_pr = $this->db->get_where('tr_po_checked_pr', ['id_user' => $id_user])->result();
		$list_id = [];
		foreach ($get_checked_pr as $item) {
			if ($item->tipe_pr == 'pr asset') {
				$get_pr = $this->db->select('b.code_plan')
					->from('tran_pr_header a')
					->join('asset_planning b', 'b.no_pr = a.no_pr', 'left')
					->where('a.id', $item->no_pr)
					->get()
					->row();

				$list_id[] = $get_pr->code_plan;
			} else {
				$list_id[] = $item->no_pr;
			}
		}

		if (count($list_id) < 1) {
			$list_id = 0;
		} else {
			$list_id = implode(';', $list_id);
		}

		echo json_encode([
			'list_id' => $list_id
		]);
	}

	public function view_po($no_po)
	{
		$session = $this->session->userdata('app_session');
		// $getparam = explode(";", $_GET['param']);

		// print_r($no_po);
		// exit;

		$get_po = $this->db->get_where('tr_purchase_order', ['no_po' => $no_po])->row();

		$getso = $this->Pr_model->get_where_in('so_number', $no_po, 'material_planning_base_on_produksi');
		// $getitemso = $this->Pr_model->get_where_in('so_number', $getparam, 'material_planning_base_on_produksi_detail');

		// $getitemso = $this->db->select("a.*, (b.qty_stock - b.qty_booking) AS avl_stock, c.code as code, d.id_stock as code1, c.nama as nm_material, d.stock_name as nm_material1, e.propose_purchase");
		// $getitemso = $this->db->from('dt_trans_po a');
		// $getitemso = $this->db->join('warehouse_stock b', 'b.id_material = a.idmaterial', 'left');
		// $getitemso = $this->db->join('new_inventory_4 c', 'c.code_lv4 = a.idmaterial', 'left');
		// $getitemso = $this->db->join('accessories d', 'd.id = a.idmaterial', 'left');
		// $getitemso = $this->db->join('material_planning_base_on_produksi_detail e', 'e.id = a.idpr', 'left');
		// $getitemso = $this->db->where_in('a.no_po', $no_po);
		// $getitemso = $this->db->group_by('a.id');

		$getitemso = $this->db->query("
			SELECT 
				a.id as id,
				a.idpr as idpr,
				a.no_po as no_po,
				a.idmaterial as idmaterial,
				a.qty as qty,
				a.hargasatuan as hargasatuan,
				a.jumlahharga as jumlahharga,
				a.kode_barang as kode_barang,
				a.ppn as ppn,
				a.ppn_persen as ppn_persen,
				a.harga_total as harga_total,
				a.tipe as tipe_pr,
				a.keterangan as keterangan,
				(b.qty_stock - b.qty_booking) AS avl_stock, 
				a.kode_barang as code, 
				'' as code1, 
				a.namamaterial as nm_material, 
				'' as nm_material1,
				a.persen_disc as persen_disc,
				a.nilai_disc as nilai_disc,
				e.propose_purchase as propose_purchase,
				g.code as packing_unit,
				h.code as packing_unit2,
				IF(i.code IS NOT NULL, i.code, j.code) as unit_measure
			FROM
				dt_trans_po a
				LEFT JOIN warehouse_stock b ON b.id_material = a.idmaterial
				LEFT JOIN new_inventory_4 c ON c.code_lv4 = a.idmaterial OR c.id = a.idmaterial
				LEFT JOIN material_planning_base_on_produksi_detail e ON e.id = a.idpr
				LEFT JOIN accessories f ON f.id = a.idmaterial
				LEFT JOIN ms_satuan g ON g.id = c.id_unit_packing
				LEFT JOIN ms_satuan h ON h.id = f.id_unit_gudang
				LEFT JOIN ms_satuan i ON i.id = c.id_unit
				LEFT JOIN ms_satuan j ON j.id = f.id_unit
			WHERE
				a.no_po IN ('" . str_replace(",", "','", $no_po) . "') AND
				(a.tipe IS NULL OR a.tipe = '')
			GROUP BY id

			UNION ALL

			SELECT 
				a.id as id,
				a.idpr as idpr,
				a.no_po as no_po,
				'' as idmaterial,
				a.qty as qty,
				a.hargasatuan as hargasatuan,
				a.jumlahharga as jumlahharga,
				a.kode_barang as kode_barang,
				a.ppn as ppn,
				a.ppn_persen as ppn_persen,
				a.harga_total as harga_total,
				a.tipe as tipe_pr,
				a.keterangan as keterangan,
				'0' AS avl_stock, 
				a.kode_barang as code, 
				'' as code1, 
				a.namamaterial as nm_material, 
				'' as nm_material1,
				a.persen_disc as persen_disc,
				a.nilai_disc as nilai_disc, 
				a.qty as propose_purchase,
				IF(f.code IS NULL, 'Pcs', f.code) as packing_unit,
				'' as packing_unit2,
				IF(f.code IS NULL, 'Pcs', f.code) as unit_measure
			FROM
				dt_trans_po a
				LEFT JOIN rutin_non_planning_detail e ON e.id = a.idpr
				LEFT JOIN ms_satuan f ON f.id = e.satuan
			WHERE
				a.no_po IN ('" . str_replace(",", "','", $no_po) . "') AND 
				a.tipe = 'pr depart'

			UNION ALL

			SELECT 
				a.id as id,
				a.idpr as idpr,
				a.no_po as no_po,
				'' as idmaterial,
				a.qty as qty,
				a.hargasatuan as hargasatuan,
				a.jumlahharga as jumlahharga,
				a.kode_barang as kode_barang,
				a.ppn as ppn,
				a.ppn_persen as ppn_persen,
				a.harga_total as harga_total,
				a.tipe as tipe_pr,
				a.keterangan as keterangan,
				'0' AS avl_stock, 
				a.kode_barang as code, 
				'' as code1, 
				a.namamaterial as nm_material, 
				'' as nm_material1,
				a.persen_disc as persen_disc,
				a.nilai_disc as nilai_disc, 
				a.qty as propose_purchase,
				'Pcs' as packing_unit,
				'' as packing_unit2,
				'Pcs' as unit_measure
			FROM
				dt_trans_po a
				LEFT JOIN asset_planning e ON e.id = a.idpr
			WHERE
				a.no_po IN ('" . str_replace(",", "','", $no_po) . "') AND 
				a.tipe = 'pr asset'

			UNION ALL

			SELECT
				a.id as id,
				a.idpr as idpr,
				a.no_po as no_po,
				'' as id_material,
				a.qty as qty,
				a.hargasatuan as hargasatuan,
				a.jumlahharga as jumlahharga,
				a.kode_barang as kode_barang,
				a.ppn as ppn,
				a.ppn_persen as ppn_persen,
				a.harga_total as harga_total,
				a.tipe as tipe_pr,
				a.keterangan as keterangan,
				'0' AS avl_stock,
				'' as code,
				'' as code1,
				a.namamaterial as nm_material, 
				'' as nm_material1,
				a.persen_disc as persen_disc,
				a.nilai_disc as nilai_disc, 
				a.qty as propose_purchase,
				'Item' as packing_unit,
				'' as packing_unit2,
				'Item' as unit_measure
			FROM
				dt_trans_po a
				LEFT JOIN " . DBCNL . ".kons_tr_kasbon_project_header e ON e.id = a.idpr
			WHERE
				a.no_po IN ('" . str_replace(",", "','", $no_po) . "') AND 
				a.tipe = 'project consultant'
			GROUP BY id
		")->result();


		// $getitemso = $this->db->get()->result();

		// print_r($this->db->last_query());
		// exit;

		$aktif = 'active';
		$deleted = '0';
		// $supplier = $data = $this->db->query("SELECT a.* FROM new_supplier as a INNER JOIN dt_trans_pr as b on b.suplier = a.id_suplier INNER JOIN tr_purchase_request as c on b.no_pr = c.no_pr WHERE c.status = '2' GROUP BY b.suplier ")->result();

		// $comp	= $this->db->query("select a.*, b.nominal as nominal_harga FROM ms_compotition as a inner join child_history_lme as b on b.id_compotition=a.id_compotition where a.deleted='0' and b.status='0' ")->result();
		$customers = $this->db->get_where('customer', ['deleted_by' => null])->result();
		$karyawan = $this->db->get_where('ms_karyawan', ['deleted_by' => null])->result();
		$mata_uang = $this->db->get_where('mata_uang', ['deleted' => null])->result();
		$list_supplier = $this->db->get_where('new_supplier', ['deleted_by' => null])->result();
		$list_department = $this->db->select('id, nama')->get_where('ms_department', ['deleted_by' => null])->result();
		// $matauang = $this->db->get_where('matauang')->result();
		$list_group_top = $this->db->get_where('list_help', ['group_by' => 'top', 'sts' => 'Y'])->result();
		$list_top = $this->db->get_where('tr_top_po', ['no_po' => $no_po])->result();
		$num_top = count($list_top);

		$nm_depart = [];
		$get_nm_depart = $this->db->query("SELECT nama FROM ms_department WHERE id IN ('" . str_replace(",", "','", $get_po->id_dept) . "')")->result();
		if (!empty($get_nm_depart)) {
			foreach ($get_nm_depart as $item_depart) {
				$nm_depart[] = strtoupper($item_depart->nama);
			}
		}

		if (!empty($nm_depart)) {
			$nm_depart = implode(', ', $nm_depart);
		} else {
			$nm_depart = '';
		}

		$data = [
			// 'supplier' => $supplier,
			// 'comp' => $comp,
			'customers' => $customers,
			'karyawan' => $karyawan,
			'mata_uang' => $mata_uang,
			// 'matauang' => $matauang,
			// 'param' => $getparam,
			// 'headerso' => $getso,
			'get_po' => $get_po,
			'getitemso' => $getitemso,
			'list_supplier' => $list_supplier,
			'list_department' => $list_department,
			'nm_depart' => $nm_depart,
			'list_top' => $list_top,
			'list_group_top' => $list_group_top,
			'num_po' => $num_top
		];

		$this->template->set('results', $data);
		$this->template->title('Detail Purchase Order');
		$this->template->render('view');
	}

	public function add_top_po()
	{
		$get_top_group = $this->db->get_where('list_help', ['group_by' => 'top', 'sts' => 'Y'])->result();

		$list_top_group = '';
		foreach ($get_top_group as $item) {
			$list_top_group .= '<option value="' . $item->id . '">' . strtoupper($item->name) . '</option>';
		}

		echo json_encode([
			'list_top_group' => $list_top_group
		]);
	}

	public function close_po_modal()
	{
		$no_po = $this->input->post('no_po');

		$get_no_surat = $this->db->get_where('tr_purchase_order', ['no_po' => $no_po])->row();

		$this->template->set('no_po', $no_po);
		$this->template->set('no_surat', $get_no_surat->no_surat);
		$this->template->render('close_po_modal');
	}

	public function close_po()
	{
		$post = $this->input->post();

		$this->db->trans_start();

		$data_update = [
			'close_po' => 1,
			'close_po_desc' => $post['close_po_reason']
		];

		$this->db->update('tr_purchase_order', $data_update, ['no_po' => $post['no_po']]);

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
			$valid = 0;
		} else {
			$this->db->trans_commit();
			$valid = 1;
		}

		echo json_encode([
			'status' => $valid
		]);
	}
}
