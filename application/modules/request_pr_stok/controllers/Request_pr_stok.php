<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once 'vendor/autoload.php';

use Mpdf\Mpdf;

class Request_pr_stok extends Admin_Controller
{
  //Permission
  protected $viewPermission   = 'PR_Stok.View';
  protected $addPermission    = 'PR_Stok.Add';
  protected $managePermission = 'PR_Stok.Manage';
  protected $deletePermission = 'PR_Stok.Delete';

  protected $id_user;
  protected $datetime;

  public function __construct()
  {
    parent::__construct();
    $this->load->library(array('upload', 'Image_lib'));

    $this->load->model(array('Request_pr_stok/request_pr_stok_model'));
    date_default_timezone_set('Asia/Bangkok');

    $this->id_user  = $this->auth->user_id();
    $this->datetime = date('Y-m-d H:i:s');
  }

  public function index()
  {
    $this->auth->restrict($this->viewPermission);
    $session  = $this->session->userdata('app_session');

    $get_data = $this->db->select('a.*, b.nm_customer, e.nm_lengkap as request_by, DATE_FORMAT(a.created_date, "%d %M %Y") as request_date')
      ->from('material_planning_base_on_produksi a')
      ->join('customer b', 'b.id_customer = a.id_customer', 'left')
      ->join('material_planning_base_on_produksi_detail c', 'c.so_number = a.so_number', 'left')
      ->join('accessories d', 'd.id = c.id_material', 'left')
      ->join('users e', 'e.id_user = a.created_by', 'left')
      ->where('a.category', 'pr stok')
      ->where('a.booking_date <>', null)
      ->where('a.close_pr', null)
      ->group_by('a.so_number')
      ->order_by('a.created_date', 'desc')
      ->get()
      ->result();

    history("View index request pr stok");
    $this->template->set('result', $get_data);
    $this->template->title('Purchasing Request / PR Stok');
    $this->template->render('index');
  }

  public function data_side_approval_pr_material()
  {
    $this->request_pr_stok_model->data_side_approval_pr_material();
  }

  public function add()
  {
    $data = [
      'category' => $this->db->get_where('accessories_category', array('deleted_date' => NULL))->result_array()
    ];
    $this->template->title('Re-Order Point Stok');
    $this->template->render('add', $data);
  }

  public function add_new()
  {
    $data = [
      'category' => $this->db->get_where('accessories_category', array('deleted_date' => NULL))->result_array()
    ];
    $this->template->title('Re-Order Point Stok');
    $this->template->render('add_new', $data);
  }

  public function server_side_reorder_point()
  {
    $this->request_pr_stok_model->get_data_json_reorder_point();
  }

  public function server_side_reorder_point_new()
  {
    $this->request_pr_stok_model->server_side_reorder_point_new();
  }

  public function clear_update_reorder($id_category)
  {
    $data = $this->input->post();
    $tgl_now = date('Y-m-d');
    $tgl_next_month = date('Y-m-' . '20', strtotime('+1 month', strtotime($tgl_now)));


    $get_materials   = $this->db->get_where('accessories', array('id_category' => $id_category))->result_array();

    foreach ($get_materials as $key => $value) {
      $ArrUpdate[$key]['id'] = $value['id'];
      $ArrUpdate[$key]['request'] = 0;
      $ArrUpdate[$key]['request_pack'] = 0;
      $ArrUpdate[$key]['tgl_dibutuhkan'] = $tgl_next_month;
    }

    $this->db->trans_start();
    $this->db->update_batch('accessories', $ArrUpdate, 'id');
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
      history('Clear all propose request material');
    }
    echo json_encode($Arr_Data);
  }

  public function save_reorder_change()
  {
    $data = $this->input->post();

    $id_material  = $data['id_material'];
    $purchase     = str_replace(',', '', $data['purchase']);
    // $purchase_pack     = str_replace(',', '', $data['purchase_pack']);
    $tanggal      = $data['tanggal'];
    $info         = $data['info'];

    $get_accessories = $this->db->get_where('accessories', ['id' => $id_material])->row();
    $purchase_pack = ($purchase / $get_accessories->konversi);

    $get_price_ref = $this->db->select('price_reference')->get_where('budget_rutin_detail', ['id_barang' => $id_material])->row();

    $price_ref = (!empty($get_accessories)) ? $get_accessories->price_ref_use : 0;


    $ArrHeader = array(
      'info_pr'          => $info,
      'request'       => $purchase,
      'request_pack' => $purchase_pack,
      'tgl_dibutuhkan' => $tanggal,
      'price_ref_use' => $price_ref
    );
    // print_r($ArrHeader);
    // exit;
    $this->db->trans_start();
    $this->db->where('id', $id_material);
    $this->db->update('accessories', $ArrHeader);
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
      history('Change propose request material ' . $id_material . ' / ' . $purchase . ' / ' . $tanggal);
    }
    echo json_encode($Arr_Data);
  }

  public function save_reorder_all()
  {
    $data = $this->input->post();

    $Ym         = date('ym');
    $qIPP        = "SELECT MAX(so_number) as maxP FROM material_planning_base_on_produksi WHERE so_number LIKE 'P" . $Ym . "%' ";
    $resultIPP  = $this->db->query($qIPP)->result_array();
    $angkaUrut2  = $resultIPP[0]['maxP'];
    $urutan2    = (int)substr($angkaUrut2, 5, 5);
    $urutan2++;
    $urut2      = sprintf('%05s', $urutan2);
    $so_number      = "P" . $Ym . $urut2;

    $id_category     = $data['category'];


    // $getraw_materials   = $this->db->get_where('accessories', array('id_category' => $id_category, 'deleted_date' => NULL, 'status' => '1', 'request >' => 0))->result_array();

    $getraw_materials = $this->db->query('SELECT a.* FROM accessories a WHERE a.id_category = "' . $id_category . '" AND a.deleted_date IS NULL AND a.status = "1" AND (a.request_pack > 0)')->result_array();

    $ArrSaveDetail = [];
    $SUM = 0;
    foreach ($getraw_materials as $key => $value) {
      $SUM += $value['request'];

      if ($value['price_ref_use'] < 1) {
        $this->db->select('a.price_reference');
        $this->db->from('budget_rutin_detail a');
        $this->db->where('a.id_barang', $value['id']);
        $get_price_ref = $this->db->get()->row();

        $price_ref = $get_price_ref->price_reference ?? 0;
      } else {
        $price_ref = $value['price_ref_use'];
      }

      $ArrSaveDetail[$key]['so_number'] = $so_number;
      $ArrSaveDetail[$key]['id_material'] = $value['id'];
      $ArrSaveDetail[$key]['propose_purchase'] = $value['request'];
      $ArrSaveDetail[$key]['price_ref'] = $price_ref;
    }

    $ArrSaveHeader = array(
      'so_number'   => $so_number,
      'no_pr'   => generateNoPR(),
      'category'     => 'pr stok',
      'tgl_so'     => date('Y-m-d'),
      'id_customer'   => 'C100-2401002',
      'project' => 'Pengisian Stok Internal',
      'qty_propose' => $SUM,
      'tgl_dibutuhkan' => $value['tgl_dibutuhkan'],
      'created_by'      => $this->id_user,
      'created_date'    => $this->datetime,
      'booking_by'      => $this->id_user,
      'booking_date'    => $this->datetime,
      'tingkat_pr' => $data['tingkat_pr'],
      'nilai_budget' => $data['nilai_budget'],
      'nilai_pengajuan' => $data['nilai_pengajuan']
    );

    // print_r($ArrSaveHeader);
    // print_r($ArrSaveDetail);
    // exit;

    $this->db->trans_start();
    $insert_stok = $this->db->insert('material_planning_base_on_produksi', $ArrSaveHeader);
    if (!$insert_stok) {
      $this->db->trans_rollback();

      print_r($this->db->last_query());
      exit;
    }
    if (!empty($ArrSaveDetail)) {
      $insert_pr_stok_detail = $this->db->insert_batch('material_planning_base_on_produksi_detail', $ArrSaveDetail);
      if (!$insert_pr_stok_detail) {
        $this->db->trans_rollback();

        print_r($this->db->last_query());
        exit;
      }
    }
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
      history('Save pengajuan propose material all');
    }
    echo json_encode($Arr_Data);
  }

  public function save_reorder_change_date()
  {
    $data = $this->input->post();

    $tanggal         = $data['tanggal'];
    $id_category     = $data['id_category'];
    $get_materials   = $this->db->get_where('accessories', array('id_category' => $id_category))->result_array();

    foreach ($get_materials as $key => $value) {
      $ArrUpdate[$key]['id'] = $value['id'];
      $ArrUpdate[$key]['tgl_dibutuhkan'] = $tanggal;
    }

    $this->db->trans_start();
    $this->db->update_batch('accessories', $ArrUpdate, 'id');
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
      history('Change propose request material tgl dibutuhkan all ' . $tanggal);
    }
    echo json_encode($Arr_Data);
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
      ->select('a.*, b.max_stok, b.min_stok, b.stock_name, b.no_coa, b.nm_coa')
      ->join('accessories b', 'a.id_material=b.id', 'left')
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

  public function edit_planning($so_number = null)
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
      ->select('a.*, b.max_stok, b.min_stok, b.stock_name')
      ->join('accessories b', 'a.id_material=b.id', 'left')
      ->get_where(
        'material_planning_base_on_produksi_detail a',
        array(
          'a.so_number' => $so_number
        )
      )
      ->result_array();

    $this->db->select('a.*');
    $this->db->from('accessories a');
    $this->db->where('(SELECT COUNT(aa.id) FROM material_planning_base_on_produksi_detail aa WHERE aa.so_number = "' . $so_number . '" AND aa.id_material = a.id) <', 1);
    $list_stok_non_pr = $this->db->get()->result_array();

    $data = [
      'so_number' => $so_number,
      'header' => $header,
      'detail' => $detail,
      'list_stok_non_pr' => $list_stok_non_pr,
      'GET_LEVEL4'   => get_inventory_lv4(),
      'GET_STOK_PUSAT' => getStokMaterial(1)
    ];

    $this->template->title('Edit PR - ' . $so_number);
    $this->template->render('edit_planning', $data);
  }

  public function process_update_all()
  {
    $data       = $this->input->post();
    $detail      = $data['detail'];
    $so_number  = $data['so_number'];

    $ArrUpdate = [];
    foreach ($detail as $key => $value) {
      $ArrUpdate[$key]['id'] = $value['id'];
      $ArrUpdate[$key]['propose_purchase'] = str_replace(',', '', $value['qty']);
    }

    $get_pr = $this->db->get_where('material_planning_base_on_produksi', ['so_number' => $so_number])->row();


    $this->db->trans_start();
    $this->db->update(
      'material_planning_base_on_produksi',
      [
        'no_rev' => ($get_pr->no_rev + 1),
        'tgl_dibutuhkan' => $data['tgl_dibutuhkan'],
        'tingkat_pr' => $data['tingkat_pr'],
        'keterangan_1' => $data['keterangan_1'],
        'keterangan_2' => $data['keterangan_2'],
        'keterangan_3' => $data['keterangan_3'],
        'app_1' => null,
        'app_2' => null,
        'app_3' => null,
        'sts_reject1' => null,
        'sts_reject2' => null,
        'sts_reject3' => null,
        'app_1_by' => null,
        'app_1_date' => null,
        'app_2_by' => null,
        'app_2_date' => null,
        'app_3_by' => null,
        'app_3_date' => null,
        'sts_reject1_by' => null,
        'sts_reject1_date' => null,
        'sts_reject2_by' => null,
        'sts_reject2_date' => null,
        'sts_reject3_by' => null,
        'sts_reject3_date' => null,
        'rejected' => null,
        'app_post' => null
      ],
      [
        'so_number' => $so_number
      ]
    );
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
      history("Update qty pr material  : " . $so_number);
    }
    echo json_encode($Arr_Data);
  }

  public function auto_update_rutin()
  {
    $data = $this->input->post();
    $category_awal = $this->uri->segment(3);
    $tgl_now = date('Y-m-d');
    $tgl_next_month = date('Y-m-' . '20', strtotime('+1 month', strtotime($tgl_now)));
    $get_rutin   = $this->db->get_where('accessories', array('id_category' => $category_awal))->result_array();
    $ArrUpdate = [];

    foreach ($get_rutin as $key => $value) {
      $get_kebutuhan   = $this->db->select('SUM(kebutuhan_month) AS sum_keb')->get_where('budget_rutin_detail', array('id_barang' => $value['id']))->row();
      $get_stock     = $this->db->select('SUM(qty_stock) AS stock')->where('id_gudang', 1)->get_where('warehouse_stock', array('id_material' => $value['id'], 'id_gudang' => 1))->result();
      $get_konversi = $this->db->select('a.konversi, a.max_stok')->get_where('accessories a', ['a.id' => $value['id']])->row_array();

      $konversi = (!empty($get_konversi)) ? $get_konversi['konversi'] : 1;
      $max_stok = ($get_kebutuhan->sum_keb * 1.5);

      $get_price_ref = $this->db->select('price_reference')->get_where('budget_rutin_detail', ['id_barang' => $value['id']])->row();
      $price_ref = (!empty($get_price_ref)) ? $get_price_ref->price_reference : 0;

      $stock_oke   = (!empty($get_stock[0]->stock)) ? $get_stock[0]->stock : 0;
      $purchase   = ($max_stok - $stock_oke);
      $purchase2   = ($purchase < 0) ? 0 : ceil($purchase);
      // if (($max_stok * $konversi) > $stock_oke) {
      //   $purchase2 = ceil($purchase);
      // } else if (($max_stok * $konversi) == $stock_oke) {
      //   $purchase2 = (($max_stok * $konversi) * 0.5);
      // } else {
      //   $purchase2 = 0;
      // }

      $ArrUpdate[$key]['id'] = $value['id'];
      $ArrUpdate[$key]['request'] = $purchase2;
      $ArrUpdate[$key]['request_pack'] = ($purchase2 / $konversi);
      $ArrUpdate[$key]['tgl_dibutuhkan'] = $tgl_next_month;
      $ArrUpdate[$key]['spec_pr'] = null;
      $ArrUpdate[$key]['info_pr'] = null;
      $ArrUpdate[$key]['price_ref_use'] = $price_ref;
    }

    $this->db->trans_start();
    if (!empty($ArrUpdate)) {
      $this->db->update_batch('accessories', $ArrUpdate, 'id');
    }
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
      history('Update auto rutin pr');
    }
    echo json_encode($Arr_Data);
  }

  public function PrintH2($id_pr)
  {
    $header = $this->request_pr_stok_model->getPRStockHeader($id_pr);
    $detail = $this->request_pr_stok_model->getPRStockDetail($id_pr);

    $data = [
      'header' => $header,
      'detail' => $detail
    ];

    $this->load->view('Print', $data);
  }

  public function edit_detail()
  {
    $post = $this->input->post();

    $this->db->trans_begin();

    $ArrUpdate = [
      'propose_purchase' => $post['qty'],
      'note' => $post['notes']
    ];

    // print_r($ArrUpdate);

    $this->db->update('material_planning_base_on_produksi_detail', $ArrUpdate, ['id' => $post['id']]);

    if ($this->db->trans_status === FALSE) {
      $this->db->trans_rollback();
      $valid = 0;
    } else {
      $this->db->trans_commit();
      $valid = 1;
    }

    echo json_encode(['status' => $valid]);
  }

  public function del_detail()
  {
    $id = $this->input->post('id');

    $this->db->trans_begin();

    $this->db->delete('material_planning_base_on_produksi_detail', ['id' => $id]);

    if ($this->db->trans_status() === false) {
      $this->db->trans_rollback();
      $valid = 0;
    } else {
      $this->db->trans_commit();
      $valid = 1;
    }

    echo json_encode([
      'status' => $valid
    ]);
  }

  public function refresh_pr_detail()
  {
    $post = $this->input->post();
    $so_number = $post['so_number'];

    $detail     = $this->db
      ->select('a.*, b.max_stok, b.min_stok, b.nama as nm_material')
      ->join('new_inventory_4 b', 'a.id_material=b.code_lv4', 'left')
      ->get_where(
        'material_planning_base_on_produksi_detail a',
        array(
          'a.so_number' => $so_number
        )
      )
      ->result_array();

    $hasil = '';
    $GET_LEVEL4 = get_inventory_lv4();
    $GET_STOK_PUSAT = getStokMaterial(1);
    foreach ($detail as $key => $value) {
      $key++;
      $nm_material   = (!empty($GET_LEVEL4[$value['id_material']]['nama'])) ? $GET_LEVEL4[$value['id_material']]['nama'] : '';
      $stock_free   = $value['stock_free'];
      $use_stock     = $value['use_stock'];
      $sisa_free     = $stock_free - $use_stock;
      $propose     = $value['propose_purchase'];



      if ($propose > 0) {
        $hasil .= "<tr>";
        $hasil .= "<td class='text-center'>" . $key . "</td>";
        $hasil .= "	<td class='text-left'>" . $value['nm_material'] . "
            
            </td>";
        $hasil .= "<td class='text-right min_stok'>" . number_format($value['min_stok'], 2) . "</td>";
        $hasil .= "<td class='text-right max_stok'>" . number_format($value['max_stok'], 2) . "</td>";
        $hasil .= "<td class='text-right min_order'>" . number_format(0, 2) . "</td>";
        if ($value['status_app'] == 'N') {
          $hasil .= "<td align='center'>";
          $hasil .= "<input type='hidden' name='detail[" . $key . "][id]' value='" . $value['id'] . "'>";
          $hasil .= "<input type='text' name='detail[" . $key . "][qty]' class='form-control input-sm text-center qty_pr_" . $value['id'] . " autoNumeric2' style='width:100px;' value='" . $propose . "'>";
          $hasil .= "</td>";
          $hasil .= "<td class='text-center'><span class='badge bg-blue text-bold'>Waiting Process</span></td>";
        }
        if ($value['status_app'] == 'Y') {
          $hasil .= "<td class='text-center'>" . number_format($propose, 2) . "</td>";
          $hasil .= "<td class='text-center'><span class='badge bg-green text-bold'>Approved</span></td>";
        }
        if ($value['status_app'] == 'D') {
          $hasil .= "<td class='text-center'>" . number_format($propose, 2) . "</td>";
          $hasil .= "<td class='text-center'><span class='badge bg-red text-bold'>Rejected</span></td>";
        }
        $hasil .= "<td class='text-center'><input type='text' class='form-control notes_" . $value['id'] . "' name='detail[" . $key . "][note]' value='" . $value['note'] . "'></td>";
        $hasil .= '<td class="text-center">
            <button type="button" class="btn btn-sm btn-warning edit_detail" data-id="' . $value['id'] . '"><i class="fa fa-edit"></i></button>
            <button type="button" class="btn btn-sm btn-danger del_detail" data-id="' . $value['id'] . '"><i class="fa fa-trash"></i></button>
          </td>';
        $hasil .= "</tr>";
      }
    }

    echo $hasil;
  }

  public function add_stok()
  {
    $post = $this->input->post();
    $get_material = $this->db->get_where('accessories', ['id' => $post['id']])->row();

    $price_ref = (!empty($get_material) && $get_material->price_ref_use !== null) ? $get_material->price_ref_use : 0;

    $this->db->trans_begin();


    $ArrData = [
      'so_number' => $post['so_number'],
      'id_material' => $post['id'],
      'propose_purchase' => $post['qty'],
      'status_app' => 'N',
      'note' => $post['notes'],
      'price_ref' => $price_ref
    ];
    $insert = $this->db->insert('material_planning_base_on_produksi_detail', $ArrData);
    if (!$insert) {
      print_r($this->db->error($insert));
      exit;
    }

    if ($this->db->trans_status() === FALSE) {
      $this->db->trans_rollback();

      $valid = 0;
      $msg = "Sorry, please try again !";
    } else {
      $this->db->trans_commit();

      $valid = 1;
      $msg = "Success, new material has been added";
    }

    echo json_encode([
      'status' => $valid,
      'msg' => $msg
    ]);
  }

  public function check_inputed_qty_stock()
  {
    $get_data = $this->db->query('
      SELECT
        id
      FROM
        accessories
      WHERE
        request_pack > 0
    ')->num_rows();

    echo json_encode([
      'jumlah_data' => $get_data
    ]);
  }

  public function close_pr_modal()
  {
    $so_number = $this->input->post('so_number');

    $get_no_pr = $this->db->get_where('material_planning_base_on_produksi', ['so_number' => $so_number])->row();

    $this->template->set('no_pr', $get_no_pr->no_pr);
    $this->template->set('so_number', $so_number);
    $this->template->render('close_pr_modal');
  }

  public function close_pr()
  {
    $so_number = $this->input->post('so_number');
    $close_pr_reason = $this->input->post('close_pr_reason');

    $this->db->trans_start();

    $update_close_pr = $this->db->update('material_planning_base_on_produksi', ['close_pr' => 1, 'close_pr_desc' => $close_pr_reason], ['so_number' => $so_number]);

    if ($this->db->trans_status() === false) {
      $this->db->trans_rollback();

      $valid = 0;
    } else {
      $this->db->trans_commit();

      $valid = 1;
    }

    echo json_encode([
      'status' => $valid
    ]);
  }

  public function hitung_budget()
  {
    $category = $this->input->post('category');

    $this->db->select('a.kebutuhan_month, a.price_reference');
    $this->db->from('budget_rutin_detail a');
    $this->db->join('accessories b', 'b.id = a.id_barang');
    $this->db->where('b.id_category', $category);
    $get_hitung_budget = $this->db->get()->result();

    $nilai_budget = 0;
    foreach ($get_hitung_budget as $item_budget) {
      $nilai_budget += ($item_budget->kebutuhan_month * $item_budget->price_reference);
    }

    $response = [
      'nilai_budget' => $nilai_budget
    ];

    echo json_encode($response);
  }

  public function hitung_pengajuan()
  {
    $category = $this->input->post('category');

    $this->db->select('a.request, a.price_ref_use');
    $this->db->from('accessories a');
    $this->db->where('a.id_category', $category);
    $this->db->where('a.request >', 0);
    $get_hitung_pengajuan = $this->db->get()->result();

    $nilai_pengajuan = 0;
    foreach ($get_hitung_pengajuan as $item) {
      $nilai_pengajuan += ($item->request * $item->price_ref_use);
    }

    $response = [
      'nilai_pengajuan' => $nilai_pengajuan
    ];

    echo json_encode($response);
  }
}
