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

class Closed_pr extends Admin_Controller
{
	//Permission
	protected $viewPermission 	= 'Closed_PR.View';
	protected $addPermission  	= 'Closed_PR.Add';
	protected $managePermission = 'Closed_PR.Manage';
	protected $deletePermission = 'Closed_PR.Delete';

	public function __construct()
	{
		parent::__construct();
		$this->load->library(array('Mpdf', 'upload', 'Image_lib'));
		$this->load->model(array(
			'Closed_pr/Closed_pr_model',
			'Aktifitas/aktifitas_model',
		));
		$this->template->title('Closed PR');
		$this->template->page_icon('fa fa-building-o');

		date_default_timezone_set('Asia/Bangkok');
	}
	public function index()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		
		$get_data = $this->db->query('
			SELECT
				a.so_number as id_pr,
				a.no_pr,
				IF(a.category = "pr material", "PR Material", "PR Stok") as kategori_pr,
				b.nm_lengkap as request_by,
				DATE_FORMAT(a.created_date, "%d %M %Y") as request_date
			FROM
				material_planning_base_on_produksi a
				LEFT JOIN users b ON b.id_user = a.created_by
			WHERE
				a.close_pr IS NOT NULL AND
				a.category IN ("pr material", "pr stok")

			UNION ALL

			SELECT
				a.no_pengajuan as id_pr,
				a.no_pr,
				"PR Department" as kategori_pr,
				b.nm_lengkap as request_by,
				DATE_FORMAT(a.created_date, "%d %M %Y") as request_date
			FROM
				rutin_non_planning_header a 
				LEFT JOIN users b ON b.id_user = a.created_by
			WHERE
				a.close_pr IS NOT NULL
		')->result();

		$this->template->set('result', $get_data);
		$this->template->render('index');
	}

	public function view_barang_pr() {
		$post = $this->input->post();

		$hasil = '';
		if($post['kategori_pr'] == 'PR Department') {
			$get_list_barang = $this->db->select('a.nm_barang as nm_barang, a.qty as qty, b.code as unit, b.code as unit_packing, 1 as nilai_konversi')
			->from('rutin_non_planning_detail a')
			->join('ms_satuan b', 'b.id = a.satuan', 'left')
			->where('a.no_pengajuan', $post['id_pr'])
			->get()
			->result();
		} else {
			$get_list_barang = $this->db->select('IF(b.nama IS NULL, c.stock_name, b.nama) as nm_barang, a.propose_purchase as qty, IF(d.code IS NULL, f.code, d.code) as unit, IF(e.code IS NULL, g.code, e.code) as unit_packing, IF(b.konversi IS NULL, c.konversi, b.konversi) as nilai_konversi')
			->from('material_planning_base_on_produksi_detail a')
			->join('new_inventory_4 b', 'b.code_lv4 = a.id_material', 'left')
			->join('accessories c', 'c.id = a.id_material', 'left')
			->join('ms_satuan d', 'd.id = b.id_unit', 'left')
			->join('ms_satuan e', 'e.id = b.id_unit_packing', 'left')
			->join('ms_satuan f', 'f.id = c.id_unit', 'left')
			->join('ms_satuan g', 'g.id = c.id_unit_gudang', 'left')
			->where('a.so_number', $post['id_pr'])
			->get()
			->result();
			// print_r($this->db->last_query());
			// exit;
		}

		$this->template->set('no_pr', $post['no_pr']);
		$this->template->set('list_barang', $get_list_barang);
		$this->template->render('view_barang_pr');
	}
}
