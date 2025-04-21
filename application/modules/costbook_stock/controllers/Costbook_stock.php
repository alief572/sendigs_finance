<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Costbook_stock extends Admin_Controller
{

	//Permission

	protected $viewPermission   = "Costbook_Stock_Per_Item.View";
	protected $addPermission    = "Costbook_Stock_Per_Item.Add";
	protected $managePermission = "Costbook_Stock_Per_Item.Manage";
	protected $deletePermission = "Costbook_Stock_Per_Item.Delete";

	protected $viewPermission2   = "Costbook_Stock_Neraca_Gudang.View";
	protected $addPermission2    = "Costbook_Stock_Neraca_Gudang.Add";
	protected $managePermission2 = "Costbook_Stock_Neraca_Gudang.Manage";
	protected $deletePermission2 = "Costbook_Stock_Neraca_Gudang.Delete";

	public function __construct()
	{
		parent::__construct();
		$this->load->library(array('Mpdf', 'upload', 'Image_lib'));
		$this->load->model('Costbook_stock/Costbook_stock_model');
		$this->template->title('Costbook Stock Per Item');
		$this->template->page_icon('fa fa-building-o');
		date_default_timezone_set('Asia/Bangkok');
	}

	public function per_item()
	{
		$this->auth->restrict($this->viewPermission);

		$this->db->select('a.id, a.kd_gudang, a.nm_gudang');
		$this->db->from('warehouse a');
		$get_warehouse = $this->db->get()->result();

		$this->db->select('a.qty_stock, a.id_material, a.nm_material, b.id_stock as kode_produk, c.id AS id_gudang, c.nm_gudang');
		$this->db->from('warehouse_stock a');
		$this->db->join('accessories b', 'b.id = a.id_material');
		$this->db->join('warehouse c', 'c.id = a.id_gudang', 'left');
		$get_data = $this->db->get()->result();

		$data = [
			'list_warehouse' => $get_warehouse,
			'list_data' => $get_data
		];

		$this->template->set('results', $data);
		$this->template->title('Costbook Stock Per Item');
		$this->template->render('per_item');
	}

	public function neraca_gudang()
	{
		$this->auth->restrict($this->viewPermission);

		$get_warehouse = $this->db->select('a.id, a.nm_gudang')
		->from('warehouse a')
		->get()
		->result();

		$data = array('list_warehouse' => $get_warehouse);

		$this->template->set('results', $data);
		$this->template->title('Costbook Stock Neraca Gudang');
		$this->template->render('neraca_gudang');
	}

	public function change_warehouse()
	{
		$warehouse = $this->input->post('warehouse');
		$tgl_from = $this->input->post('tgl_from');
		$tgl_to = $this->input->post('tgl_to');

		$this->db->select('a.qty_stock, a.id_material, a.nm_material, b.id_stock as kode_produk, c.id AS id_gudang, c.nm_gudang');
		$this->db->from('warehouse_stock a');
		$this->db->join('accessories b', 'b.id = a.id_material');
		$this->db->join('warehouse c', 'c.id = a.id_gudang', 'left');
		if ($warehouse !== '' && $warehouse !== null) {
			$this->db->where('a.id_gudang', $warehouse);
		}
		if ($tgl_from !== null && $tgl_to !== null && $tgl_from !== '' && $tgl_to !== '') {
			$this->db->where('(SELECT COUNT(aa.id) FROM tr_cost_book aa WHERE aa.id_material = a.id_material AND (aa.id_gudang_dari = a.id_gudang OR aa.id_gudang_ke = a.id_gudang) AND DATE_FORMAT(aa.created_on, "%Y-%m-%d") BETWEEN "' . $tgl_from . '" AND "' . $tgl_to . '" ORDER BY aa.created_on DESC LIMIT 1) >', 0);
		}
		$get_data = $this->db->get()->result();

		$data = [
			'list_data' => $get_data,
			'tgl_from' => $tgl_from,
			'tgl_to' => $tgl_to
		];
		$this->template->set('results', $data);
		$this->template->render('list_costbook_per_item');
	}

	public function view_per_item()
	{
		$id_material = $this->input->post('id_material');
		$id_gudang = $this->input->post('id_gudang');

		$get_nm_material = $this->db->select('a.stock_name as nm_material')
			->from('accessories a')
			->where('a.id', $id_material)
			->get()
			->row();

		$nm_material = '';
		if (!empty($get_nm_material)) {
			$nm_material = $get_nm_material->nm_material;
		}

		$get_data = $this->db->select('a.tgl, a.jenis_transaksi, a.qty_transaksi, a.qty, a.nilai_beli, a.costbook, a.value_transaksi, a.value_neraca')
			->from('tr_cost_book a')
			->where('a.id_material', $id_material)
			->group_start()
			->where('a.id_gudang_dari', $id_gudang)
			->or_where('a.id_gudang_ke', $id_gudang)
			->group_end()
			->get()
			->result();

		

		$data = array('list_data' => $get_data, 'nm_material' => $nm_material);

		$this->template->set('results', $data);
		$this->template->render('view_per_item');
	}

	public function get_costbook_data()
	{
		$periode_bulan = $this->input->post('periode_bulan');
		$periode_tahun = $this->input->post('periode_tahun');
		$warehouse = $this->input->post('warehouse');

		// print_r($periode_tahun);
		// exit;

		$this->db->select('a.value_neraca');
		$this->db->from('tr_cost_book a');
		$this->db->where('DATE_FORMAT(a.tgl, "%m")', sprintf('%02s', $periode_bulan));
		$this->db->where('DATE_FORMAT(a.tgl, "%Y")', $periode_tahun);
		$this->db->where('a.jenis_transaksi', 'Saldo Awal');
		$get_saldo_awal = $this->db->query("SELECT a.value_neraca FROM tr_cost_book a WHERE DATE_FORMAT(a.tgl, '%m') = '".sprintf('%02s', $periode_bulan)."' AND DATE_FORMAT(a.tgl, '%Y') = '".$periode_tahun."' AND a.jenis_transaksi = 'Saldo Awal'")->result();
		if($warehouse !== '' && $warehouse !== null) {
			$get_saldo_awal = $this->db->query("SELECT a.value_neraca FROM tr_cost_book a WHERE DATE_FORMAT(a.tgl, '%m') = '".sprintf('%02s', $periode_bulan)."' AND DATE_FORMAT(a.tgl, '%Y') = '".$periode_tahun."' AND (a.id_gudang_dari = '".$warehouse."' OR a.id_gudang_ke = '".$warehouse."') AND a.jenis_transaksi = 'Saldo Awal'")->result();
		}
		// if (!$get_saldo_awal) {
		// 	print_r("SELECT a.value_neraca FROM tr_cost_book a WHERE DATE_FORMAT(a.tgl, '%m') = '".sprintf('%02s', $periode_bulan)."' AND DATE_FORMAT(a.tgl, '%Y') = '".$periode_tahun."' AND a.jenis_transaksi = 'Saldo Awal'");
		// 	exit;
		// }

		$this->db->select('a.jenis_transaksi, a.no_transaksi, a.tgl, a.value_transaksi, IF(a.jenis_transaksi LIKE "%Out%", "Out", "In") as tipe_transaksi, b.nm_gudang as nm_gudang_dari, c.nm_gudang as nm_gudang_ke');
		$this->db->from('tr_cost_book a');
		$this->db->join('warehouse b', 'b.id = a.id_gudang_dari', 'left');
		$this->db->join('warehouse c', 'c.id = a.id_gudang_ke', 'left');
		$this->db->where('a.tipe_material', 'stok');
		$this->db->where('DATE_FORMAT(a.tgl, "%m") =', sprintf('%02s', $periode_bulan));
		$this->db->where('DATE_FORMAT(a.tgl, "%Y") =', $periode_tahun);
		$this->db->not_like('a.jenis_transaksi', 'saldo');
		if ($warehouse !== '' && $warehouse !== null) {
			$this->db->group_start();
			$this->db->where('a.id_gudang_ke', $warehouse);
			$this->db->or_where('a.id_gudang_dari', $warehouse);
			$this->db->group_end();
		}

		$where_warehouse = '';
		if ($warehouse !== '' && $warehouse !== null) {
			$where_warehouse = 'AND (a.id_gudang_dari = "'.$warehouse.'" OR a.id_gudang_ke = "'.$warehouse.'")';
		}
		$get_data = $this->db->query('SELECT a.jenis_transaksi, a.no_transaksi, a.tgl, a.value_transaksi, IF(a.jenis_transaksi LIKE "%Out%", "Out", "In") as tipe_transaksi, b.nm_gudang as nm_gudang_dari, c.nm_gudang as nm_gudang_ke FROM tr_cost_book a LEFT JOIN warehouse b ON b.id = a.id_gudang_dari LEFT JOIN warehouse c ON c.id = a.id_gudang_ke WHERE a.tipe_material = "stok" AND DATE_FORMAT(a.tgl, "%m") = "'.sprintf('%02s', $periode_bulan).'" AND DATE_FORMAT(a.tgl, "%Y") = "'.$periode_tahun.'" AND a.jenis_transaksi NOT LIKE "%saldo%" '.$where_warehouse.' ORDER BY a.created_on ASC')->result();
		// if(!$get_data) {
			// print_r($this->db->last_query());
			// exit;
		// }

		$data = [
			'list_data' => $get_data,
			'list_saldo_awal' => $get_saldo_awal
		];

		$this->template->set('results', $data);
		$this->template->render('list_costbook_neraca_gudang');
	}
}
