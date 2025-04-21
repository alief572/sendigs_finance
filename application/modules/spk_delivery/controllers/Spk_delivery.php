<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Spk_delivery extends Admin_Controller
{
  //Permission
  protected $viewPermission   = 'SPK_Delivery.View';
  protected $addPermission    = 'SPK_Delivery.Add';
  protected $managePermission = 'SPK_Delivery.Manage';
  protected $deletePermission = 'SPK_Delivery.Delete';

  public function __construct()
  {
    parent::__construct();

    $this->load->library(array('upload', 'Image_lib'));
    $this->load->model(array(
      'Spk_delivery/spk_delivery_model'
    ));

    date_default_timezone_set('Asia/Bangkok');

    $this->id_user  = $this->auth->user_id();
    $this->datetime = date('Y-m-d H:i:s');
  }

  public function index()
  {
    $this->auth->restrict($this->viewPermission);
    $session  = $this->session->userdata('app_session');

    $listSO = $this->db->get_where('tr_sales_order', array('approve' => 1))->result_array();
    $data = [
      'listSO' => $listSO
    ];
    history("View data spk delivery");
    $this->template->title('Delivery / SPK Delivery');
    $this->template->render('index', $data);
  }

  public function data_side_spk_material()
  {
    $this->spk_delivery_model->data_side_spk_material();
  }

  public function add($no_so = null)
  {
    if ($this->input->post()) {
      $data         = $this->input->post();
      $session      = $this->session->userdata('app_session');

      $no_so            = $data['no_so'];
      $delivery_date    = (!empty($data['delivery_date'])) ? date('Y-m-d', strtotime($data['delivery_date'])) : NULL;
      $delivery_address  = $data['delivery_address'];
      $detail            = $data['detail'];

      $Ym           = date('ym');
      $SQL          = "SELECT MAX(no_delivery) as maxP FROM spk_delivery WHERE no_delivery LIKE 'DLV" . $Ym . "%' ";
      $result        = $this->db->query($SQL)->result_array();
      $angkaUrut2    = $result[0]['maxP'];
      $urutan2      = (int)substr($angkaUrut2, 7, 4);
      $urutan2++;
      $urut2        = sprintf('%04s', $urutan2);
      $no_delivery  = "DLV" . $Ym . $urut2;

      // $query = $this->db->query("SELECT MAX(no_delivery) as max_id FROM spk_delivery WHERE no_delivery LIKE '%" . int_to_roman(date('m')) . "/" . date('Y') . "%'");
      // $row = $query->row_array();
      // $max_id = $row['max_id'];
      // $max_id1 = (int) substr($max_id, 3, 3);
      // $counter = $max_id1 + 1;
      // $counter = sprintf('%03s', $counter);
      // $no_delivery = "OM/" . $counter . "/" . int_to_roman(date('m')) . "/" . date('Y');

      $ArrHeader = [
        'no_delivery' => $no_delivery,
        'no_so' => $no_so,
        'delivery_date' => $delivery_date,
        'delivery_address' => $delivery_address,
        'created_by' => $this->id_user,
        'created_date' => $this->datetime
      ];

      $ArrDetail = [];
      foreach ($detail as $key => $value) {
        $ArrDetail[$key]['no_delivery']   = $no_delivery;
        $ArrDetail[$key]['no_so']         = $no_so;
        $ArrDetail[$key]['id_so_det']     = $value['id_so_det'];
        $ArrDetail[$key]['code_lv4']      = $value['code_lv4'];
        $ArrDetail[$key]['qty_so']        = $value['qty_so'];
        $ArrDetail[$key]['qty_booking']   = $value['qty_booking'];
        $ArrDetail[$key]['qty_delivery']  = str_replace(',', '', $value['qty_delivery']);
      }

      $this->db->trans_start();
      $this->db->insert('spk_delivery', $ArrHeader);

      if (!empty($ArrDetail)) {
        $this->db->insert_batch('spk_delivery_detail', $ArrDetail);
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
        history("Create spk delivery : " . $no_delivery);
      }
      echo json_encode($Arr_Data);
    } else {
      $QUERY = "SELECT
                    a.no_so,
                    a.no_penawaran,
                    c.nm_customer,
                    a.project,
                    a.delivery_date,
                    a.invoice_address
                  FROM
                    tr_sales_order a
                    LEFT JOIN tr_penawaran b ON a.no_penawaran = b.no_penawaran
                    LEFT JOIN customer c ON b.id_customer = c.id_customer
                  WHERE a.approve = '1' AND a.no_so = '" . $no_so . "' ";
      $getData = $this->db->query($QUERY)->result_array();

      $getDetail = $this->db
        ->select('a.*, SUM(b.qty_delivery) AS qty_delivery')
        ->group_by('a.id_so_detail')
        ->join('spk_delivery_detail b', 'a.id_so_detail = b.id_so_det', 'left')
        ->get_where('tr_sales_order_detail a', array('a.no_so' => $no_so))->result_array();

      $data = [
        'getData' => $getData,
        'getDetail' => $getDetail
      ];

      $this->template->title('Add SPK Delivery');
      $this->template->render('add', $data);
    }
  }

  public function print_spk()
  {
    $kode  = $this->uri->segment(3);
    $data_session  = $this->session->userdata;
    $session        = $this->session->userdata('app_session');
    $printby    = $session['id_user'];

    $data_url    = base_url();
    $Split_Beda    = explode('/', $data_url);
    $Jum_Beda    = count($Split_Beda);
    $Nama_Beda    = $Split_Beda[$Jum_Beda - 2];

    $getData        = $this->db->get_where('spk_delivery', array('no_delivery' => $kode))->result_array();
    $getDataDetail  = $this->db->select('a.*, b.no_bom')->join('tr_sales_order_detail b', 'a.id_so_det=b.id_so_detail')->get_where('spk_delivery_detail a', array('a.no_delivery' => $kode))->result_array();

    $data = array(
      'Nama_Beda' => $Nama_Beda,
      'printby' => $printby,
      'getData' => $getData,
      'getDataDetail' => $getDataDetail,
      'GET_DET_Lv4' => get_inventory_lv4(),
      'kode' => $kode
    );

    history('Print spk delivery ' . $kode);
    $this->load->view('print_spk', $data);
  }














  public function request_to_subgudang()
  {
    $data         = $this->input->post();
    $session      = $this->session->userdata('app_session');

    $id        = $data['id'];
    $detail    = $data['detail'];
    $mix1      = str_replace(',', '', $data['mix1']);
    $mix2      = str_replace(',', '', $data['mix2']);
    $mix3      = str_replace(',', '', $data['mix3']);
    $mix4      = str_replace(',', '', $data['mix4']);
    $mix5      = str_replace(',', '', $data['mix5']);
    $mix6      = str_replace(',', '', $data['mix6']);
    $mix7      = str_replace(',', '', $data['mix7']);
    $getdata = $this->db->get_where('so_internal_spk', array('id' => $id))->result_array();

    $ArrUpdateMat = [];
    foreach ($detail as $key => $value) {
      $ArrUpdateMat[$key]['id'] = $value['id'];
      $ArrUpdateMat[$key]['mix1'] = (!empty($value['mix1'])) ? $value['mix1'] : null;
      $ArrUpdateMat[$key]['mix2'] = (!empty($value['mix2'])) ? $value['mix2'] : null;
      $ArrUpdateMat[$key]['mix3'] = (!empty($value['mix3'])) ? $value['mix3'] : null;
      $ArrUpdateMat[$key]['mix4'] = (!empty($value['mix4'])) ? $value['mix4'] : null;
      $ArrUpdateMat[$key]['mix5'] = (!empty($value['mix5'])) ? $value['mix5'] : null;
      $ArrUpdateMat[$key]['mix6'] = (!empty($value['mix6'])) ? $value['mix6'] : null;
      $ArrUpdateMat[$key]['mix7'] = (!empty($value['mix7'])) ? $value['mix7'] : null;
    }

    $ArrUpdate = array(
      'sts_request' => 'Y',
      'mix1' => $mix1,
      'mix2' => $mix2,
      'mix3' => $mix3,
      'mix4' => $mix4,
      'mix5' => $mix5,
      'mix6' => $mix6,
      'mix7' => $mix7,
      'request_by' => $this->id_user,
      'request_date' => $this->datetime
    );

    $this->db->where('id', $id);
    $this->db->update('so_internal_spk', $ArrUpdate);

    $this->db->update_batch('so_internal_spk_material', $ArrUpdateMat, 'id');

    $Arr_Data  = array(
      'status'    => 1,
      'id'    => $id,
      'kode_det'    => $getdata[0]['kode_det'],
    );
    echo json_encode($Arr_Data);
  }

  public function plan_mixing_add($id)
  {
    $this->auth->restrict($this->viewPermission);
    $session  = $this->session->userdata('app_session');

    $getDataSPK = $this->db->get_where('so_internal_spk', array('id' => $id))->result_array();
    $getData = $this->db->get_where('so_internal', array('id' => $getDataSPK[0]['id_so']))->result_array();
    $getMaterialMixing    = $this->db->select('code_material, weight AS berat, id')->where('kode_det', $getDataSPK[0]['kode_det'])->get_where('so_internal_spk_material', array('type_name' => 'mixing'))->result_array();


    $data = [
      'id' => $id,
      'getDataSPK' => $getDataSPK,
      'getData' => $getData,
      'GET_DET_Lv4' => get_inventory_lv4(),
      'getMaterialMixing' => $getMaterialMixing,
    ];

    $this->template->title('Plan Mixing');
    $this->template->render('plan_mixing', $data);
  }

  //Re-Print SPK
  public function reprint_spk()
  {
    $this->auth->restrict($this->viewPermission);
    $session  = $this->session->userdata('app_session');
    $this->template->page_icon('fa fa-users');

    $this->template->title('SPK Re-Print');
    $this->template->render('reprint_spk');
  }

  public function data_side_spk_reprint()
  {
    $this->spk_delivery_model->data_side_spk_reprint();
  }
}
