<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Approval_po extends Admin_Controller
{
  //Permission
  protected $viewPermission   = 'Approval_PO.View';
  protected $addPermission    = 'Approval_PO.Add';
  protected $managePermission = 'Approval_PO.Manage';
  protected $deletePermission = 'Approval_PO.Delete';

  public function __construct()
  {
    parent::__construct();

    $this->load->model(array('Approval_po/Approval_po_model', 'Purchase_order/Pr_model'));
    date_default_timezone_set('Asia/Bangkok');

    // $this->id_user  = $this->auth->user_id();
    // $this->datetime = date('Y-m-d H:i:s');
  }

  public function index()
  {
    $this->auth->restrict($this->viewPermission);
    $session  = $this->session->userdata('app_session');

    history("View index approval pr material");
    $this->template->title('Approval PO');
    $this->template->render('index');
  }

  public function data_side_approval_pr_material()
  {
    $this->Approval_po_model->data_side_approval_pr_material();
  }

  public function approval_planning($so_number = null)
  {
    if ($this->input->post()) {
      $data         = $this->input->post();
      $session      = $this->session->userdata('app_session');

      $so_number        = $data['so_number'];
      $tgl_dibutuhkan    = (!empty($data['tgl_dibutuhkan'])) ? date('Y-m-d', strtotime($data['tgl_dibutuhkan'])) : NULL;
      $detail            = $data['detail'];


      $ArrPlanningDetail = [];
      $SUM_USE = 0;
      $SUM_PROPOSE = 0;
      if (!empty($detail)) {
        foreach ($detail as $key => $value) {
          //Planning
          $use_stock = str_replace(',', '', $value['use_stock']);
          $propose = str_replace(',', '', $value['propose']);

          $ArrPlanningDetail[$key]['id'] = $value['id'];
          $ArrPlanningDetail[$key]['stock_free'] = $value['stock_free'];
          $ArrPlanningDetail[$key]['min_stock'] = $value['min_stok'];
          $ArrPlanningDetail[$key]['max_stock'] = $value['max_stok'];
          $ArrPlanningDetail[$key]['use_stock'] = $use_stock;
          $ArrPlanningDetail[$key]['propose_purchase'] = $propose;

          $SUM_USE += $use_stock;
          $SUM_PROPOSE += $propose;
        }
      }

      $ArrHeader = array(
        'tgl_dibutuhkan'  => $tgl_dibutuhkan,
        'qty_use_stok'  => $SUM_USE,
        'qty_propose'  => $SUM_PROPOSE,
        'updated_by'      => $this->id_user,
        'updated_date'    => $this->datetime
      );

      // print_r($ArrBOMDetail);
      // exit;

      $this->db->trans_start();
      $this->db->where('so_number', $so_number);
      $this->db->update('material_planning_base_on_produksi', $ArrHeader);

      if (!empty($ArrPlanningDetail)) {
        $this->db->update_batch('material_planning_base_on_produksi_detail', $ArrPlanningDetail, 'id');
      }
      $this->db->trans_complete();

      if ($this->db->trans_status() === FALSE) {
        $this->db->trans_rollback();
        $Arr_Data  = array(
          'pesan'    => 'Save gagal disimpan ...',
          'status'  => 0
        );
      } else {
        $this->db->trans_commit();
        $Arr_Data  = array(
          'pesan'    => 'Save berhasil disimpan. Thanks ...',
          'status'  => 1
        );
        history("Create material planning  : " . $so_number);
      }
      echo json_encode($Arr_Data);
    } else {
      $header     = $this->db
        ->select('a.*, b.due_date, c.nm_customer')
        ->join('so_internal b', 'a.so_number=b.so_number', 'left')
        ->join('customer c', 'a.id_customer=c.id_customer', 'left')
        ->get_where(
          'material_planning_base_on_produksi a',
          array(
            'a.so_number' => $so_number
          )
        )
        ->result_array();
      $detail     = $this->db
        ->select('a.*, b.max_stok, b.min_stok')
        ->join('new_inventory_4 b', 'a.id_material=b.code_lv4', 'left')
        ->get_where(
          'material_planning_base_on_produksi_detail a',
          array(
            'a.so_number' => $so_number
          )
        )
        ->result_array();

      $data = [
        'so_number' => $so_number,
        'header' => $header,
        'detail' => $detail,
        'GET_LEVEL4'   => get_inventory_lv4(),
        'GET_STOK_PUSAT' => getStokMaterial(1)
      ];

      $this->template->title('Approval PR - ' . $so_number);
      $this->template->render('approval_planning', $data);
    }
  }

  public function detail_planning($so_number = null)
  {
    $header     = $this->db
      ->select('a.*, b.due_date, c.nm_customer')
      ->join('so_internal b', 'a.so_number=b.so_number', 'left')
      ->join('customer c', 'a.id_customer=c.id_customer', 'left')
      ->get_where(
        'material_planning_base_on_produksi a',
        array(
          'a.so_number' => $so_number
        )
      )
      ->result_array();
    $detail     = $this->db
      ->select('a.*, b.max_stok, b.min_stok')
      ->join('new_inventory_4 b', 'a.id_material=b.code_lv4', 'left')
      ->get_where(
        'material_planning_base_on_produksi_detail a',
        array(
          'a.so_number' => $so_number
        )
      )
      ->result_array();

    $data = [
      'so_number' => $so_number,
      'header' => $header,
      'detail' => $detail,
      'GET_LEVEL4'   => get_inventory_lv4(),
      'GET_STOK_PUSAT' => getStokMaterial(1)
    ];

    $this->template->title('Detail PR - ' . $so_number);
    $this->template->render('detail_planning', $data);
  }

  public function process_approval_satuan()
  {
    $data       = $this->input->post();
    $id          = $data['id'];
    $action      = $data['action'];
    $so_number  = $data['so_number'];
    $pr_rev      = $data['pr_rev'];

    $ArrHeader = array(
      'propose_rev'  => ($action == 'approve') ? $pr_rev : NULL,
      'status_app'  => ($action == 'approve') ? 'Y' : 'D',
      'app_by'      => $this->id_user,
      'app_date'    => $this->datetime
    );

    // print_r($ArrBOMDetail);
    // exit;

    $this->db->trans_start();
    $this->db->where('id', $id);
    $this->db->update('material_planning_base_on_produksi_detail', $ArrHeader);
    $this->db->trans_complete();

    if ($this->db->trans_status() === FALSE) {
      $this->db->trans_rollback();
      $Arr_Data  = array(
        'pesan'    => 'Process Failed !',
        'status'  => 0,
        'so_number'  => $so_number
      );
    } else {
      $this->db->trans_commit();
      $Arr_Data  = array(
        'pesan'    => 'Process Success !',
        'status'  => 1,
        'so_number'  => $so_number
      );
      history($action . " satuan pr material  : " . $id);
    }
    echo json_encode($Arr_Data);
  }

  public function process_approval_all()
  {
    $data       = $this->input->post();
    $check      = $data['check'];
    $so_number  = $data['so_number'];

    $ArrUpdate = [];
    foreach ($check as $key => $value) {
      $ArrUpdate[$key]['id'] = $value;
      $ArrUpdate[$key]['propose_rev'] = str_replace(',', '', $data['pr_rev_' . $value]);
      $ArrUpdate[$key]['status_app'] = 'Y';
      $ArrUpdate[$key]['app_by'] = $this->id_user;
      $ArrUpdate[$key]['app_date'] = $this->datetime;
    }

    $this->db->trans_start();
    if (!empty($ArrUpdate)) {
      $this->db->update_batch('material_planning_base_on_produksi_detail', $ArrUpdate, 'id');
    }
    $this->db->trans_complete();

    if ($this->db->trans_status() === FALSE) {
      $this->db->trans_rollback();
      $Arr_Data  = array(
        'pesan'    => 'Process Failed !',
        'status'  => 0,
        'so_number'  => $so_number
      );
    } else {
      $this->db->trans_commit();
      $Arr_Data  = array(
        'pesan'    => 'Process Success !',
        'status'  => 1,
        'so_number'  => $so_number
      );
      history("Approve pr material  : " . $so_number);
    }
    echo json_encode($Arr_Data);
  }

  public function View($id)
  {
    $this->auth->restrict($this->viewPermission);
    $session = $this->session->userdata('app_session');
    $this->template->page_icon('fa fa-pencil');
    $header = $this->db->query("SELECT a.*, c.nm_customer, b.nm_lengkap AS nama_user FROM material_planning_base_on_produksi a LEFT JOIN users b ON b.id_user = a.booking_by LEFT JOIN customer c ON c.id_customer = a.id_customer WHERE a.so_number = '$id' ")->result();
    $detail     = $this->db
      ->select('a.*, b.nama as nm_product, b.max_stok, b.min_stok')
      ->join('new_inventory_4 b', 'a.id_material=b.code_lv4', 'left')
      ->get_where(
        'material_planning_base_on_produksi_detail a',
        array(
          'a.so_number' => $id
        )
      )
      ->result_array();
    $data = [
      'header' => $header,
      'detail' => $detail
    ];
    $this->template->set('results', $data);
    $this->template->title('View P.R');
    $this->template->render('View');
  }

  public function po_approval($no_po)
  {
    $session = $this->session->userdata('app_session');
    // $getparam = explode(";", $_GET['param']);

    // $getso = $this->Pr_model->get_where_in('so_number', $getparam, 'material_planning_base_on_produksi');
    // $getitemso = $this->Pr_model->get_where_in('so_number', $getparam, 'material_planning_base_on_produksi_detail');

    // $getitemso = $this->db->select("x.*, a.propose_purchase, (b.qty_stock - b.qty_booking) AS avl_stock, c.code as code, d.id_stock as code1, c.nama as nm_material, d.stock_name as nm_material1, c.konversi, d.konversi as konversi1, e.code as satuan, f.code as satuan1");
    // $getitemso = $this->db->from('material_planning_base_on_produksi_detail a');
    // $getitemso = $this->db->join('dt_trans_po x', 'x.idpr = a.id');
    // $getitemso = $this->db->join('warehouse_stock b', 'b.id_material = a.id_material', 'left');
    // $getitemso = $this->db->join('new_inventory_4 c', 'c.code_lv4 = a.id_material', 'left');
    // $getitemso = $this->db->join('accessories d', 'd.id = a.id_material', 'left');
    // $getitemso = $this->db->join('ms_satuan e', 'e.id = c.id_unit', 'left');
    // $getitemso = $this->db->join('ms_satuan f', 'f.id = d.id_unit', 'left');
    // $getitemso = $this->db->where_in('x.no_po', $no_po);
    // $getitemso = $this->db->group_by('a.id');
    // $getitemso = $this->db->get()->result();

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
				if(c.code IS NULL, d.id_stock, c.code) as code, 
				'' as code1, 
				if(c.nama IS NULL, d.stock_name, c.nama) as nm_material, 
				'' as nm_material1, 
				e.propose_purchase as propose_purchase,
        if(c.konversi IS NULL, 1, c.konversi) as konversi,
        '0' as konversi1,
        f.code as satuan,
        '' as satuan1,
        a.description as description,
        a.note as note,
        a.persen_disc as persen_disc,
        a.nilai_disc as nilai_disc,
        g.code as packing_unit,
        h.code as packing_unit2
			FROM
				dt_trans_po a
				LEFT JOIN warehouse_stock b ON b.id_material = a.idmaterial
				LEFT JOIN new_inventory_4 c ON c.code_lv4 = a.idmaterial
        LEFT JOIN accessories d ON d.id = a.idmaterial
				JOIN material_planning_base_on_produksi_detail e ON e.id = a.idpr
        LEFT JOIN ms_satuan f ON f.id = c.id_unit
        LEFT JOIN ms_satuan g ON g.id = c.id_unit_packing
        LEFT JOIN ms_satuan h ON h.id = d.id_unit_gudang
			WHERE
				a.no_po IN ('" . str_replace(",", "','", $no_po) . "') AND
				(a.tipe IS NULL OR a.tipe = '')
      GROUP BY a.id

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
				e.qty as propose_purchase,
        '1' as konversi,
        '0' as konversi1,
        f.code as satuan,
        '' as satuan1,
        a.description as description,
        a.note as note,
        a.persen_disc as persen_disc,
        a.nilai_disc as nilai_disc,
        'Pcs' as packing_unit,
        '' as packing_unit2
			FROM
				dt_trans_po a
				JOIN rutin_non_planning_detail e ON e.id = a.idpr
        LEFT JOIN ms_satuan f ON f.id = e.satuan
			WHERE
				a.no_po IN ('" . str_replace(",", "','", $no_po) . "') AND 
				a.tipe = 'pr depart'
      GROUP BY a.id

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
				e.qty as propose_purchase,
        '1' as konversi,
        '0' as konversi1,
        f.code as satuan,
        '' as satuan1,
        a.description as description,
        a.note as note,
        a.persen_disc as persen_disc,
        a.nilai_disc as nilai_disc,
        'Pcs' as packing_unit,
        '' as packing_unit2
			FROM
				dt_trans_po a
				JOIN rutin_non_planning_detail e ON e.id = a.idpr
        LEFT JOIN ms_satuan f ON f.id = e.satuan
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
				1 as propose_purchase,
        '1' as konversi,
        '0' as konversi1,
        'Item' as satuan,
        '' as satuan1,
        a.description as description,
        a.note as note,
        a.persen_disc as persen_disc,
        a.nilai_disc as nilai_disc,
        'Item' as packing_unit,
        '' as packing_unit2
			FROM
				dt_trans_po a
				JOIN " . DBCNL . ".kons_tr_kasbon_project_header e ON e.id = a.idpr
			WHERE
				a.no_po IN ('" . str_replace(",", "','", $no_po) . "') AND 
				a.tipe = 'project consultant'
		")->result();



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
    $list_group_top = $this->db->get_where('list_help', ['group_by' => 'top', 'sts' => 'Y'])->result();
    $list_top = $this->db->get_where('tr_top_po', ['no_po' => $no_po])->result();
    $num_top = count($list_top);

    // $matauang = $this->db->get_where('matauang')->result();

    $header_po = $this->db->get_where('tr_purchase_order', ['no_po' => $no_po])->row();
    $data_department = $this->db->select('if(nama IS NULL, "", nama) as nm_department')->get_where('ms_department', ['id' => $header_po->id_dept])->row();

    $nm_depart = [];
    $get_nm_depart = $this->db->query("SELECT nama FROM ms_department WHERE id IN ('" . str_replace(",", "','", $header_po->id_dept) . "')")->result();
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
      'customers' => $customers,
      'karyawan' => $karyawan,
      'mata_uang' => $mata_uang,
      'getitemso' => $getitemso,
      'list_supplier' => $list_supplier,
      'header_po' => $header_po,
      'data_department' => $data_department,
      'nm_depart' => $nm_depart,
      'list_top' => $list_top,
      'list_group_top' => $list_group_top,
      'num_po' => $num_top
    ];

    $this->template->set('results', $data);
    $this->template->title('Approval Purchase Order');
    $this->template->render('approval_po');
  }

  public function approve_po_process()
  {
    // $this->auth->restrict($this->addPermission);
    $post = $this->input->post();

    $code = $this->Pr_model->generate_code($post['tanggal']);

    $this->db->trans_begin();

    $this->db->update('tr_purchase_order', ['status' => 2], ['no_po' => $post['no_po']]);

    if ($this->db->trans_status() === FALSE) {
      $this->db->trans_rollback();
      $status  = array(
        'pesan'    => 'Approved process has been failed !',
        'code' => '0',
        'status'  => 0
      );
    } else {
      $this->db->trans_commit();
      $status  = array(
        'pesan'    => 'PO has been Approved !',
        'code' => $code,
        'status'  => 1
      );
    }

    echo json_encode($status);
  }

  public function reject_po_process()
  {
    $this->auth->restrict($this->addPermission);
    $post = $this->input->post();

    $code = $this->Pr_model->generate_code($post['tanggal']);

    $this->db->trans_begin();

    $this->db->update('tr_purchase_order', ['status' => 1, 'reject_reason' => $post['reject_reason']], ['no_po' => $post['no_po']]);

    if ($this->db->trans_status() === FALSE) {
      $this->db->trans_rollback();
      $status  = array(
        'pesan'    => 'Rejected process has been failed !',
        'code' => '0',
        'status'  => 0
      );
    } else {
      $this->db->trans_commit();
      $status  = array(
        'pesan'    => 'PO has been Rejected !',
        'code' => $code,
        'status'  => 1
      );
    }

    echo json_encode($status);
  }
}
