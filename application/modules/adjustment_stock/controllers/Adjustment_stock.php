<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Adjustment_stock extends Admin_Controller
{
  //Permission
  protected $viewPermission   = 'Adjustment_Stock.View';
  protected $addPermission    = 'Adjustment_Stock.Add';
  protected $managePermission = 'Adjustment_Stock.Manage';
  protected $deletePermission = 'Adjustment_Stock.Delete';

  public function __construct()
  {
    parent::__construct();

    $this->load->library(array('upload', 'Image_lib'));
    $this->load->model(array(
      'Adjustment_stock/adjustment_stock_model'
    ));

    date_default_timezone_set('Asia/Bangkok');

    $this->id_user  = $this->auth->user_id();
    $this->datetime = date('Y-m-d H:i:s');
  }

  public function index()
  {
    $this->auth->restrict($this->viewPermission);
    $session  = $this->session->userdata('app_session');

    $material  = $this->db->order_by('stock_name', 'asc')->get_where('accessories', array('deleted_date' => NULL))->result_array();
    $data = array(
      'material'    => $material,
    );

    history("View index adjustment stok");
    $this->template->title('Warehouse Stok >> Adjustment');
    $this->template->render('index', $data);
  }

  public function data_side_adjustment()
  {
    $this->adjustment_stock_model->get_data_json_adjustment();
  }

  public function add()
  {
    if ($this->input->post()) {
      $data       = $this->input->post();
      $data_session  = $this->session->userdata;

      $adjustment_type   = $data['adjustment_type'];
      $id_material       = $data['id_material'];
      $no_ba             = $data['no_ba'];
      $qty_oke           = str_replace(',', '', $data['qty_oke']);
      $keterangan       = $data['keterangan'];

      $id_gudang_dari_m   = $data['id_gudang_dari_m'];
      $id_gudang_ke_m     = $data['id_gudang_ke_m'];
      $kd_gudang_dari_m   = (!empty($id_gudang_dari_m)) ? get_name('warehouse', 'kd_gudang', 'id', $id_gudang_dari_m) : NULL;
      $kd_gudang_ke_m     = (!empty($id_gudang_ke_m)) ? get_name('warehouse', 'kd_gudang', 'id', $id_gudang_ke_m) : NULL;
      $pic_m               = $data['pic_m'];
      // $expired_date_m 	  = $data['expired_date_m'];

      $id_gudang_ke     = $data['id_gudang_ke'];
      $kd_gudang_ke     = get_name('warehouse', 'kd_gudang', 'id', $id_gudang_ke);
      $pic               = $data['pic'];
      // $expired_date 		= $data['expired_date'];

      $Ym       = date('ym');

      $srcMtr        = "SELECT MAX(kode_trans) as maxP FROM warehouse_adjustment WHERE kode_trans LIKE 'TRA" . $Ym . "%' ";
      $resultMtr    = $this->db->query($srcMtr)->result_array();
      $angkaUrut2    = $resultMtr[0]['maxP'];
      $urutan2      = (int)substr($angkaUrut2, 7, 4);
      $urutan2++;
      $urut2        = sprintf('%04s', $urutan2);
      $kode_trans    = "TRA" . $Ym . $urut2;

      $nm_material  = get_name('accessories', 'stock_name', 'id', $id_material);

      $ArrHeader = array(
        'kode_trans'         => $kode_trans,
        'category'           => 'adjustment stock',
        'tanggal'           => date('Y-m-d'),
        'adjustment_type'   => $adjustment_type,
        'jumlah_mat'         => $qty_oke,
        'id_gudang_dari'     => ($adjustment_type == 'mutasi') ? $id_gudang_dari_m : NULL,
        'kd_gudang_dari'     => ($adjustment_type == 'mutasi') ? $kd_gudang_dari_m : 'ADJUSTMENT ' . strtoupper($adjustment_type),
        'id_gudang_ke'       => ($adjustment_type == 'mutasi') ? $id_gudang_ke_m : $id_gudang_ke,
        'kd_gudang_ke'       => ($adjustment_type == 'mutasi') ? $kd_gudang_ke_m : $kd_gudang_ke,
        'pic'               => ($adjustment_type == 'mutasi') ? $pic_m : $pic,
        'note'               => $keterangan,
        'checked'           => 'Y',
        'created_by'         => $this->id_user,
        'created_date'       => $this->datetime,
        'checked_by'         => $this->id_user,
        'checked_date'       => $this->datetime
      );

      $ArrDetail = array(
        'kode_trans'       => $kode_trans,
        'id_material'     => $id_material,
        'nm_material'     => $nm_material,
        'qty_order'       => $qty_oke,
        'qty_oke'         => $qty_oke,
        'no_ba'           => $no_ba,
        'keterangan'       => $keterangan,
        'update_by'       => $this->id_user,
        'update_date'     => $this->datetime,
        'check_qty_oke'   => $qty_oke,
        'check_keterangan'   => $keterangan
      );


      $ArrStock[0]['id']  = $id_material;
      $ArrStock[0]['qty'] = $qty_oke;

      //MUTASI
      if ($adjustment_type == 'mutasi') {
        $id_gudang_dari = $id_gudang_dari_m;
        $id_gudang_ke   = $id_gudang_ke_m;
      }
      //MINUS
      if ($adjustment_type == 'minus') {
        $id_gudang_dari = NULL;
        $id_gudang_ke   = $id_gudang_ke;
      }
      //PLUS
      if ($adjustment_type == 'plus') {
        $id_gudang_dari = NULL;
        $id_gudang_ke   = $id_gudang_ke;
      }

      // print_r($ArrHeader);
      // print_r($ArrDetail);
      // exit;

      $this->db->trans_start();
      $this->db->insert('warehouse_adjustment', $ArrHeader);
      $this->db->insert('warehouse_adjustment_detail', $ArrDetail);
      $this->db->trans_complete();

      if ($this->db->trans_status() === FALSE) {
        $this->db->trans_rollback();
        $Arr_Data  = array(
          'pesan'    => 'Save process failed. Please try again later ...',
          'status'  => 0
        );
      } else {
        $this->db->trans_commit();
        $Arr_Data  = array(
          'pesan'    => 'Save process success. Thanks ...',
          'status'  => 1
        );
        move_warehouse_stok_adjustment($ArrStock, $id_gudang_dari, $id_gudang_ke, $kode_trans, $adjustment_type);
        history("Adjustment barang stok " . $adjustment_type . " : " . $kode_trans);
      }
      echo json_encode($Arr_Data);
    } else {

      $gudang  = $this->db->order_by('urut', 'asc')->get_where('warehouse', array('desc' => 'stok', 'status' => 'Y'))->result_array();
      $data = array(
        'gudang'    => $gudang,
      );

      $this->template->title('Add Adjustment');
      $this->template->render('add', $data);
    }
  }

  public function get_stock()
  {
    $material = $this->uri->segment(3);
    $gudang    = $this->uri->segment(4);
    $unit    = $this->uri->segment(5);

    $IMP = explode('_', $unit);
    // echo $IMP[1];

    if ($gudang == '1') {
      if (empty($unit)) {
        $stock = get_stock_material_packing($material, $gudang);
      }
      if (!empty($unit)) {
        if ($IMP[1] == 'unit') {
          $stock = get_stock_material($material, $gudang);
        }
        if ($IMP[1] == 'packing') {
          $stock = get_stock_material_packing($material, $gudang);
        }
      }

      $sqlSup    = "SELECT satuan_packing, unit FROM ms_material WHERE code_material ='" . $material . "' ";
      $restSup  = $this->db->query($sqlSup)->result();

      $option  = "<option value='" . $restSup[0]->satuan_packing . "_packing'>" . strtoupper($restSup[0]->satuan_packing) . " (Packing)</option>";
      $option  .= "<option value='" . $restSup[0]->unit . "_unit'>" . strtoupper($restSup[0]->unit) . " (Unit)</option>";

      $tanda = "packing";
    }

    if ($gudang != '1') {
      $stock = get_stock_material($material, $gudang);

      $sqlSup    = "SELECT unit FROM ms_material WHERE code_material ='" . $material . "' ";
      $restSup  = $this->db->query($sqlSup)->result();

      $option  = "<option value='" . $restSup[0]->unit . "_unit'>" . strtoupper($restSup[0]->unit) . " (Unit)</option>";

      $tanda = "unit";
    }

    echo json_encode(array(
      'stock'      => floatval($stock),
      'option'    => $option,
      'tanda'      => $tanda
    ));
  }

  public function list_gudang_ke()
  {
    $gudang    = $this->input->post('gudang');
    $tandax    = $this->input->post('tandax');

    if ($gudang <> '0') {
      $queryIpp  = "SELECT b.urut2 FROM  warehouse b WHERE b.id = '" . $gudang . "' LIMIT 1";
      $restIpp  = $this->db->query($queryIpp)->result();

      if ($tandax == 'MOVE') {
        $whLef = " id != '" . $gudang . "' AND status = 'Y' ";
      } else {
        $whLef = " urut2 > " . $restIpp[0]->urut2;
      }

      $query     = "SELECT id, kd_gudang, nm_gudang FROM warehouse WHERE " . $whLef . " ORDER BY urut ASC";
      // echo $query;
      $Q_result  = $this->db->query($query)->result();

      $Opt     = (!empty($Q_result)) ? 'Select An Warehouse' : 'List Empty - Not Found';
    }
    if ($gudang == '0') {
      $Opt = 'List Empty';
    }

    $option = "<option value='0'>" . $Opt . "</option>";
    if ($gudang <> '0') {
      foreach ($Q_result as $row) {
        $option .= "<option value='" . $row->id . "'>" . strtoupper($row->nm_gudang) . "</option>";
      }
    }
    echo json_encode(array(
      'option' => $option
    ));
  }

  public function list_material()
  {

    $Q_result  = $this->db->order_by('stock_name', 'asc')->get_where('accessories', array('deleted_date' => NULL))->result();
    $option = "<option value='0'>Select Barang</option>";
    foreach ($Q_result as $row) {
      $option .= "<option value='" . $row->id . "'>" . $row->stock_name . "</option>";
    }
    echo json_encode(array(
      'option' => $option
    ));
  }

  public function list_material_stock()
  {
    $gudang    = $this->input->post('gudang');
    $Q_result  = $this->db->select('a.id_material, b.nama AS nm_material')->order_by('b.nama', 'asc')->join('new_inventory_4 b', 'a.id_material=b.code_lv4', 'inner')->get_where('warehouse_stock a', array('a.id_gudang' => $gudang))->result();
    $option = "<option value='0'>Select Material</option>";
    foreach ($Q_result as $row) {
      $option .= "<option value='" . $row->id_material . "'>" . strtoupper($row->nm_material) . "</option>";
    }
    echo json_encode(array(
      'option' => $option
    ));
  }
}
