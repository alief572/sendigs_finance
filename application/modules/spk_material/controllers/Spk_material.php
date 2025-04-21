<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Spk_material extends Admin_Controller
{
  //Permission
  protected $viewPermission   = 'SPK_Material.View';
  protected $addPermission    = 'SPK_Material.Add';
  protected $managePermission = 'SPK_Material.Manage';
  protected $deletePermission = 'SPK_Material.Delete';

  public function __construct()
  {
    parent::__construct();

    $this->load->library(array('upload', 'Image_lib'));
    $this->load->model(array(
      'Spk_material/spk_material_model'
    ));

    date_default_timezone_set('Asia/Bangkok');

    $this->id_user  = $this->auth->user_id();
    $this->datetime = date('Y-m-d H:i:s');
  }

  public function index()
  {
    $this->auth->restrict($this->viewPermission);
    $session  = $this->session->userdata('app_session');
    // $this->template->page_icon('fa fa-users');

    $listSO = $this->db->get_where('so_internal', array('deleted_date' => NULL))->result_array();
    $listType = $this->db->get_where('new_inventory_1', array('deleted_date' => NULL, 'category' => 'product', 'code_lv1 <>' => 'P123000009'))->result_array();
    $data = [
      'listSO' => $listSO,
      'listType' => $listType,
    ];

    history("View data spk material");
    $this->template->title('SPK Produksi');
    $this->template->render('index', $data);
  }

  public function data_side_spk_material()
  {
    $this->spk_material_model->data_side_spk_material();
  }

  public function release_spk()
  {
    $data         = $this->input->post();
    $session      = $this->session->userdata('app_session');

    $id        = $data['id'];
    $qty      = str_replace(',', '', $data['qty']);

    $Arr_Data  = array(
      'id'    => $id,
      'qty'    => $qty
    );
    echo json_encode($Arr_Data);
  }

  public function add($id = null, $qty = null)
  {
    if ($this->input->post()) {
      $data         = $this->input->post();
      $session      = $this->session->userdata('app_session');

      $id        = $data['id'];
      $so_number = $data['so_number'];
      $Detail    = $data['Detail'];

      $Ym = date('ym');
      $SQL        = "SELECT MAX(kode) as maxP FROM so_internal_spk WHERE kode LIKE 'SPK" . $Ym . "%' ";
      $result      = $this->db->query($SQL)->result_array();
      $angkaUrut2    = $result[0]['maxP'];
      $urutan2      = (int)substr($angkaUrut2, 7, 4);
      $urutan2++;
      $urut2        = sprintf('%04s', $urutan2);
      $kode          = "SPK" . $Ym . $urut2;

      $Y          = date('y');
      $SQL        = "SELECT MAX(no_spk) as maxP FROM so_internal_spk WHERE no_spk LIKE 'INT." . $Y . ".%' ";
      // echo $SQL; exit;
      $result      = $this->db->query($SQL)->result_array();
      $angkaUrut2    = $result[0]['maxP'];
      $urutan2      = (int)substr($angkaUrut2, 7, 4);
      $urutan2++;
      $urut2        = sprintf('%04s', $urutan2);
      $no_spk          = "INT." . $Y . '.' . $urut2;

      $ArrInsert = [];
      $ArrInsertMaterial = [];
      foreach ($Detail as $key => $value) {
        $qty = str_replace(',', '', $value['qty']);
        if ($qty > 0) {
          $ArrInsert[$key]['id_so'] = $id;
          $ArrInsert[$key]['kode'] = $kode;
          $ArrInsert[$key]['kode_det'] = $kode . '-' . $key;
          $ArrInsert[$key]['no_spk'] = $no_spk;
          $ArrInsert[$key]['tanggal'] = date('Y-m-d', strtotime($value['tanggal']));
          $ArrInsert[$key]['tanggal_est_finish'] = date('Y-m-d', strtotime($value['tanggal_est_finish']));
          $ArrInsert[$key]['qty'] = $qty;
          $ArrInsert[$key]['id_costcenter'] = $value['costcenter'];
          $ArrInsert[$key]['created_by'] = $this->id_user;
          $ArrInsert[$key]['created_date'] = $this->datetime;

          $dataBOM = $this->db->get_where('so_internal_material', array('so_number' => $so_number))->result_array();
          if (!empty($dataBOM)) {
            foreach ($dataBOM as $key2 => $value2) {
              $UNIQ = $key . '-' . $key2;
              $ArrInsertMaterial[$UNIQ]['kode_det'] = $kode . '-' . $key;
              $ArrInsertMaterial[$UNIQ]['code_material'] = $value2['code_material'];
              $ArrInsertMaterial[$UNIQ]['weight'] = $value2['weight'];
              $ArrInsertMaterial[$UNIQ]['code_lv1'] = $value2['code_lv1'];
              $ArrInsertMaterial[$UNIQ]['type_name'] = $value2['type_name'];
            }
          }
        }
      }

      $this->db->trans_start();
      if (!empty($ArrInsert)) {
        $this->db->insert_batch('so_internal_spk', $ArrInsert);
      }
      if (!empty($ArrInsertMaterial)) {
        $this->db->insert_batch('so_internal_spk_material', $ArrInsertMaterial);
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
          'status'  => 1,
          'kode' => $kode
        );
        history("Create spk planning : " . $so_number);
      }
      echo json_encode($Arr_Data);
    } else {

      $getData = $this->db->get_where('so_internal', array('id' => $id))->result_array();

      $tgl1 = date_create();
      $tgl2 = date_create($getData[0]['due_date']);
      $jarak = date_diff($tgl1, $tgl2);

      $maxDate = $jarak->days + 1;

      $GET_CYCLETIME = get_total_time_cycletime();
      $code_lv4 = $getData[0]['code_lv4'] . '-' . $getData[0]['no_bom'];
      $no_bom = $getData[0]['no_bom'];

      $getDataProduct = $this->db->get_where('bom_header', array('no_bom' => $getData[0]['no_bom']))->result_array();

      $cycletimeMesin   = (!empty($GET_CYCLETIME[$code_lv4]['ct_machine'])) ? $GET_CYCLETIME[$code_lv4]['ct_machine'] : 0;

      $GetNamaBOMProduct  = get_name_product_by_bom($no_bom);
      $NamaProduct         = (!empty($GetNamaBOMProduct[$no_bom])) ? $GetNamaBOMProduct[$no_bom] : 0;

      $data = [
        'getData' => $getData,
        'getDataProduct' => $getDataProduct,
        'maxDate' => $maxDate,
        'NamaProduct' => $NamaProduct,
        'qty' => $qty,
        'cycletime' => ($cycletimeMesin > 0) ? $cycletimeMesin / 60 : 0,
      ];

      $this->template->title('Add Schedule Detil');
      $this->template->render('add', $data);
    }
  }

  public function get_add()
  {
    $id   = $this->uri->segment(3);
    $no   = 0;

    $costcenter  = $this->db->query("SELECT * FROM ms_costcenter WHERE deleted='0' ORDER BY nama_costcenter ASC ")->result_array();
    $d_Header = "";
    // $d_Header .= "<tr>";
    $d_Header .= "<tr class='header_" . $id . "'>";
    $d_Header .= "<td align='left'>";
    $d_Header .= "<input type='text' name='Detail[" . $id . "][tanggal]' class='form-control input-md text-center datepicker' placeholder='Plan Date' readonly>";
    $d_Header .= "</td>";
    $d_Header .= "<td align='left'>";
    $d_Header .= "<input type='text' name='Detail[" . $id . "][tanggal_est_finish]' class='form-control input-md text-center datepicker2' placeholder='Est Finish' readonly>";
    $d_Header .= "</td>";
    $d_Header .= "<td align='left'>";
    $d_Header .= "<input type='text' name='Detail[" . $id . "][qty]' class='form-control input-md text-center autoNumeric0 qty_spk' placeholder='Qty SPK'>";
    $d_Header .= "</td>";
    $d_Header .= "<td align='left'>";
    $d_Header .= "<select name='Detail[" . $id . "][costcenter]' class='chosen-select form-control input-sm inline-blockd costcenter'>";
    $d_Header .= "<option value='0'>Select Costcenter</option>";
    foreach ($costcenter as $val => $valx) {
      $d_Header .= "<option value='" . $valx['id_costcenter'] . "'>" . strtoupper($valx['nama_costcenter']) . "</option>";
    }
    $d_Header .=     "</select>";
    $d_Header .= "</td>";
    $d_Header .= "<td align='center'>";
    $d_Header .= "<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
    $d_Header .= "</td>";
    $d_Header .= "</tr>";

    //add part
    $d_Header .= "<tr id='add_" . $id . "'>";
    $d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-warning addPart' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add</button></td>";
    $d_Header .= "<td align='center'></td>";
    $d_Header .= "<td align='center'></td>";
    $d_Header .= "<td align='center'></td>";
    $d_Header .= "<td align='center'></td>";
    $d_Header .= "</tr>";

    echo json_encode(array(
      'header'      => $d_Header,
    ));
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

    $getMaterialMixing    = $this->db->select('code_material, weight AS berat')->group_by('code_material')->like('kode_det', $kode)->get_where('so_internal_spk_material', array('type_name' => 'mixing'))->result_array();
    $getMaterialProduksi  = $this->db->select('code_material, weight AS berat')->group_by('code_material')->like('kode_det', $kode)->get_where('so_internal_spk_material', array('type_name <>' => 'mixing'))->result_array();

    $getData = $this->db
      ->select('
                          b.nama_product,
                          SUM(a.qty) AS qty_produksi,
                          b.so_number AS nomor_so,
                          a.no_spk,
                          a.tanggal AS tanggal,
                          MAX(a.tanggal_est_finish) AS tanggal_est_finish,
                          b.due_date AS due_date,
                          b.no_bom
                      ')
      ->group_by('a.kode')
      ->join('so_internal b', 'a.id_so=b.id', 'left')
      ->get_where('so_internal_spk a', array(
        'a.kode' => $kode
      ))
      ->result_array();

    $getHeader = $this->db->get_where('so_internal_spk', array('kode' => $kode))->result_array();

    $no_bom = $getData[0]['no_bom'];
    $GetNamaBOMProduct  = get_name_product_by_bom($no_bom);
    $NamaProduct         = (!empty($GetNamaBOMProduct[$no_bom])) ? $GetNamaBOMProduct[$no_bom] : 0;

    $data = array(
      'Nama_Beda' => $Nama_Beda,
      'printby' => $printby,
      'getHeader' => $getHeader,
      'getData' => $getData,
      'NamaProduct' => $NamaProduct,
      'getMaterialMixing' => $getMaterialMixing,
      'getMaterialProduksi' => $getMaterialProduksi,
      'GET_DET_Lv4' => get_inventory_lv4(),
      'kode' => $kode
    );

    history('Print spk material ' . $kode);
    $this->load->view('print_spk3', $data);
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
    $this->spk_material_model->data_side_spk_reprint();
  }
}
