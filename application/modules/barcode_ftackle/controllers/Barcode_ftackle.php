<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Barcode_ftackle extends Admin_Controller
{
  //Permission
  protected $viewPermission   = 'Barcode_Ftackle.View';
  protected $addPermission    = 'Barcode_Ftackle.Add';
  protected $managePermission = 'Barcode_Ftackle.Manage';
  protected $deletePermission = 'Barcode_Ftackle.Delete';

  public function __construct()
  {
    parent::__construct();

    $this->load->library(array('upload', 'Image_lib'));
    $this->load->model(array(
      'Barcode_ftackle/Barcode_ftackle_model'
    ));

    date_default_timezone_set('Asia/Bangkok');

    $this->id_user  = $this->auth->user_id();
    $this->datetime = date('Y-m-d H:i:s');
  }

  public function index()
  {
    $this->auth->restrict($this->viewPermission);
    $session  = $this->session->userdata('app_session');

    $this->template->title('Quality Control / QR Code F-Tackle');
    $this->template->render('index');
  }

  public function data_side_outstanding_qc()
  {
    $this->Barcode_ftackle_model->data_side_outstanding_qc();
  }

  public function qc($id = null)
  {
    $listData = $this->db
      ->select('a.no_spk, a.qty, b.nama_product, c.variant_product, a.id, a.kode, a.kode_det, b.code_lv4, b.no_bom, z.id_key_spk, z.product_ke')
      ->join('so_internal_spk a', 'a.id=z.id_key_spk', 'left')
      ->join('so_internal b', 'a.id_so=b.id', 'left')
      ->join('bom_header c', 'b.no_bom=c.no_bom', 'left')
      ->get_where('so_internal_product z', array('z.id_key_spk' => $id, 'z.status <>' => 'N'))
      ->result();
    $no_bom = $listData[0]->no_bom;
    $GetNamaBOMProduct  = get_name_product_by_bom($no_bom);
    $NamaProduct         = (!empty($GetNamaBOMProduct[$no_bom])) ? $GetNamaBOMProduct[$no_bom] : 0;

    $data = [
      'listData' => $listData,
      'NamaProduct' => $NamaProduct,
      'GET_QC' => get_quality_control()
    ];
    $this->template->set($data);
    $this->template->render('detail');
  }

  public function print_qrcode($idmilik, $size)
  {
    $data_session  = $this->session->userdata;
    $session        = $this->session->userdata('app_session');
    $printby    = $session['id_user'];

    $data_url    = base_url();
    $Split_Beda    = explode('/', $data_url);
    $Jum_Beda    = count($Split_Beda);
    $Nama_Beda    = $Split_Beda[$Jum_Beda - 2];

    //update status qc
    $explode = explode("-", $idmilik);
    $this->db->where_in('id', $explode)->update('so_internal_product', ['sts_print_qr' => 'Y', 'sts_print_qr_date' => date('Y-m-d H:i:s')]);


    $getData = $this->db
      ->select('a.id, a.daycode, a.qc_pass, a.status, c.code_lv4, c.nama_product, a.inspektor, c.no_bom')
      ->from('so_internal_product a')
      ->join('so_internal_spk b', 'a.id_key_spk=b.id', 'left')
      ->join('so_internal c', 'b.id_so=c.id', 'left')
      ->where_in('a.id', explode("-", $idmilik))
      ->get()
      ->result_array();

    $data = array(
      'Nama_Beda' => $Nama_Beda,
      'printby' => $printby,
      'size' => $size,
      'detail' => $getData,
      'GET_CODE'      => get_list_inventory_lv4('product'),
      'GET_CODE_LV2'  => get_inventory_lv2(),
      'GET_CODE_LV3'  => get_inventory_lv3(),
    );

    $this->load->view('print_qrcode', $data);
  }
}
