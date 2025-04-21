<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Production_report_ftackle extends Admin_Controller
{
  //Permission
  protected $viewPermission   = 'Daily_Report_Production_F-Tackle.View';
  protected $addPermission    = 'Daily_Report_Production_F-Tackle.Add';
  protected $managePermission = 'Daily_Report_Production_F-Tackle.Manage';
  protected $deletePermission = 'Daily_Report_Production_F-Tackle.Delete';

  public function __construct()
  {
    parent::__construct();

    $this->load->library(array('upload', 'Image_lib'));
    $this->load->model(array(
      'Production_report_ftackle/production_report_ftackle_model'
    ));
    date_default_timezone_set('Asia/Bangkok');

    $this->id_user  = $this->auth->user_id();
    $this->datetime = date('Y-m-d H:i:s');
  }

  public function index()
  {
    $this->auth->restrict($this->viewPermission);
    $session  = $this->session->userdata('app_session');

    history("View data production daily report");
    $this->template->title('Produksi / Daily Production Report');
    $this->template->render('index');
  }

  public function data_side_spk_material()
  {
    $this->production_report_ftackle_model->data_side_spk_material();
  }

  public function view_process($id = null)
  {
    $getData = $this->db
      ->select('b.*, a.*, a.id AS id_uniq')
      ->join('so_internal b', 'a.id_so=b.id', 'left')
      ->get_where('so_internal_spk a', array(
        'a.id' => $id
      ))
      ->result_array();


    $id_gudang  = get_name('warehouse', 'id', 'kd_gudang', $getData[0]['id_costcenter']);
    $kode       = $getData[0]['kode_det'];
    $code_lv4   = $getData[0]['code_lv4'];
    $no_bom     = $getData[0]['no_bom'];
    $qty        = $getData[0]['qty'];
    $getMaterialMixing  = $this->db
      ->select('a.id, a.code_material, a.weight AS berat, a.weight_aktual, b.weight_aktual AS berat_subgudang')
      ->join('so_internal_spk_material_pengeluaran b', 'a.id=b.id_det_spk', 'left')
      ->get_where('so_internal_spk_material a', array('a.type_name' => 'mixing', 'a.kode_det' => $kode))
      ->result_array();
    $getMaterialNonMixing  = $this->db->select('id, code_material, weight AS berat')->where('kode_det', $kode)->get_where('so_internal_spk_material', array('type_name <>' => 'mixing'))->result_array();

    // $cycletime    = $this->db->select('b.nm_process')->join('cycletime_detail_detail b','a.id_time=b.id_time','left')->get_where('cycletime_header a', array('a.deleted_date'=>NULL,'id_product'=>$code_lv4))->result_array();
    $cycletime = $this->db->select('view AS nm_process')->get_where('list', array('category' => 'ftackle'))->result_array();
    $getProcess = $this->db->group_by('nm_process')->get_where('bom_detail', array('no_bom' => $no_bom))->result_array();
    $ArrProcess = [];
    foreach ($getProcess as $key => $value) {
      $ArrProcess[] = $value['nm_process'];
    }

    $getCountWIP = $this->db->get_where('so_internal_product', array('id_key_spk' => $id, 'qc_date' => NULL))->result_array();
    $getCountFG = $this->db->get_where('so_internal_product', array('id_key_spk' => $id, 'qc_date !=' => NULL))->result_array();

    $no_bom = $getData[0]['no_bom'];
    $GetNamaBOMProduct  = get_name_product_by_bom($no_bom);
    $NamaProduct         = (!empty($GetNamaBOMProduct[$no_bom])) ? $GetNamaBOMProduct[$no_bom] : 0;

    $data = [
      'getCountWIP' => COUNT($getCountWIP),
      'getCountFG' => COUNT($getCountFG),
      'getData' => $getData,
      'NamaProduct' => $NamaProduct,
      'id' => $id,
      'kode' => $kode,
      'cycletime' => $cycletime,
      'ArrProcess' => $ArrProcess,
      'qty' => $qty,
      'getMaterialMixing' => $getMaterialMixing,
      'getMaterialNonMixing' => $getMaterialNonMixing,
      'GET_STOK' => getStokMaterial($id_gudang),
      'GET_MATERIAL' => get_inventory_lv4(),
      'checkInputMixing' => checkInputMixing($kode)
    ];
    $this->template->title('View Process');
    $this->template->render('view_process', $data);
  }
}
