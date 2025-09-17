<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Stok_gudang_pusat extends Admin_Controller
{
  //Permission
  protected $viewPermission   = 'Stok_gudang_pusat.View';
  protected $addPermission    = 'Stok_gudang_pusat.Add';
  protected $managePermission = 'Stok_gudang_pusat.Manage';
  protected $deletePermission = 'Stok_gudang_pusat.Delete';

  public function __construct()
  {
    parent::__construct();
    $this->load->model(array(
      'Stok_gudang_pusat/stok_gudang_pusat_model'
    ));
    $this->template->title('Manage Data Supplier');
    $this->template->page_icon('fa fa-building-o');

    date_default_timezone_set('Asia/Bangkok');
  }

  //==========================================================================================================
  //============================================STOCK=========================================================
  //==========================================================================================================

  public function index()
  {
    $this->auth->restrict($this->viewPermission);
    $session  = $this->session->userdata('app_session');

    history("View data gudang pusat");
    $this->template->title('Gudang Material / Gudang Pusat / Stok');
    $this->template->render('index');
  }

  public function data_side_stock()
  {
    $this->stok_gudang_pusat_model->get_json_stock();
  }

  public function modal_history()
  {
    $data     = $this->input->post();
    $gudang   = $data['gudang'];
    $material = $data['material'];

    $sql = "SELECT a.*, b.konversi FROM warehouse_history a LEFT JOIN new_inventory_4 b ON b.code_lv4 = a.id_material WHERE a.id_gudang='" . $gudang . "' AND a.id_material='" . $material . "' ORDER BY a.id ASC ";
    $data = $this->db->query($sql)->result_array();

    $dataArr = array(
      'gudang' => $gudang,
      'material' => $material,
      'data'  => $data
    );
    $this->load->view('modal_history', $dataArr);
  }
  public function modal_lot_detail()
  {
    $data     = $this->input->post();
    $gudang   = $data['gudang'];
    $material = $data['material'];

    // $sql = "SELECT a.* FROM warehouse_history a WHERE a.id_gudang='" . $gudang . "' AND a.id_material='" . $material . "' ORDER BY a.id ASC ";
    // $data = $this->db->query($sql)->result_array();

    $data = $this->db->select('a.*, b.nm_lengkap, c.konversi as nil_kon')
      ->from('tr_checked_incoming_detail a')
      ->join('users b', 'b.id_user = a.created_by', 'left')
      ->join('new_inventory_4 c', 'c.code_lv4 = a.id_material', 'left')
      ->where('a.id_material', $material)
      ->where('a.sts', '1')
      ->where('(a.qty_oke - a.qty_used) >', 0)
      ->get()
      ->result_array();

    // print_r($data);
    // exit;

    $dataArr = array(
      'gudang' => $gudang,
      'material' => $material,
      'data'  => $data
    );
    $this->load->view('modal_lot_detail', $dataArr);
  }

  public function export_excel($material, $gudang)
  {

    $sql = "SELECT a.* FROM warehouse_history a WHERE a.id_gudang='" . $gudang . "' AND a.id_material='" . $material . "' ORDER BY a.id ASC ";
    $data = $this->db->query($sql)->result_array();

    $dataArr = array(
      'gudang' => $gudang,
      'material' => $material,
      'data'  => $data
    );
    $this->load->view('excel_history', $dataArr);
  }

  public function download_excel()
  {
    $tanggal = $this->uri->segment(3);

    $get_material = $this->db->get_where('new_inventory_4', ['deleted_by' => null, 'category' => 'material'])->result_array();
    $get_satuan = $this->db->get_where('ms_satuan', ['deleted' => 'N'])->result_array();

    $list_packing = [];
    $list_unit = [];

    foreach ($get_satuan as $item_satuan) {
      if ($item_satuan['category'] == 'unit') {
        $list_unit[$item_satuan['id']] = $item_satuan['code'];
      } else {
        $list_packing[$item_satuan['id']] = $item_satuan['code'];
      }
    }


    if (date('Y-m-d', strtotime($tanggal)) == date('Y-m-d')) {
      $this->db->select('a.id_material, a.nm_material, a.qty_stock as stok');
      $this->db->from('warehouse_stock a');
      $this->db->join('new_inventory_4 b', 'b.code_lv4 = a.id_material');
      $this->db->where('a.id_gudang', 1);
      $this->db->group_by('a.id_material');
      $get_stok_material = $this->db->get()->result_array();
    } else {
      $this->db->select('a.id_material, a.nm_material, max(a.qty_stock) as stok');
      $this->db->from('warehouse_stock_per_day a');
      $this->db->where('DATE_FORMAT(a.hist_date, "%Y-%m-%d") = ', $tanggal);
      $this->db->where('a.id_gudang', 1);
      $this->db->group_by('a.id_material');
      $get_stok_material = $this->db->get()->result_array();
    }

    $list_stok = [];
    foreach ($get_stok_material as $item_stok) {
      $list_stok[$item_stok['id_material']] = $item_stok['stok'];
    }

    $data = [
      'list_material' => $get_material,
      'list_unit' => $list_unit,
      'list_packing' => $list_packing,
      'list_stok' => $list_stok,
      'tanggal' => $tanggal
    ];

    // $this->load->set('results', $data);
    $this->load->view('excel_stok_gudang_pusat', ['results' => $data]);
  }
}
