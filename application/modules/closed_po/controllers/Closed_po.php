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

class Closed_po extends Admin_Controller
{
    //Permission
    protected $viewPermission     = 'Closed_PO.View';
    protected $addPermission      = 'Closed_PO.Add';
    protected $managePermission = 'Closed_PO.Manage';
    protected $deletePermission = 'Closed_PO.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('Mpdf', 'upload', 'Image_lib'));
        $this->load->model(array(
            'Closed_po/Closed_po_model',
            'Aktifitas/aktifitas_model',
        ));
        $this->template->title('Closed PO');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');
    }
    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
        $this->template->page_icon('fa fa-users');

        $get_data = $this->db->query("
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
                a.close_po IS NOT NULL
            GROUP BY a.no_po
            ORDER BY a.no_po DESC
        ")->result();

        $this->template->set('results', $get_data);
        $this->template->render('index');
    }

    public function view_po($no_po)
    {
        $session = $this->session->userdata('app_session');
        $getparam = explode(";", $_GET['param']);

        // print_r($no_po);
        // exit;

        $get_po = $this->db->get_where('tr_purchase_order', ['no_po' => $no_po])->row();

        // $getso = $this->Pr_model->get_where_in('so_number', $getparam, 'material_planning_base_on_produksi');
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
				JOIN material_planning_base_on_produksi_detail e ON e.id = a.idpr
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
            'param' => $getparam,
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
        $this->template->render('view_po');
    }
}
