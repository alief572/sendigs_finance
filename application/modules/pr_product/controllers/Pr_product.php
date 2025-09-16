<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pr_product extends Admin_Controller
{
  //Permission
  protected $viewPermission   = 'PR_Product.View';
  protected $addPermission    = 'PR_Product.Add';
  protected $managePermission = 'PR_Product.Manage';
  protected $deletePermission = 'PR_Product.Delete';

  public function __construct()
  {
    parent::__construct();

    $this->load->model(array('Pr_product/Pr_product_model'));
    date_default_timezone_set('Asia/Bangkok');

    $this->id_user  = $this->auth->user_id();
    $this->datetime = date('Y-m-d H:i:s');
  }

  public function index()
  {
    $this->auth->restrict($this->viewPermission);
    $session  = $this->session->userdata('app_session');

    $sql = "SELECT
              a.*,
              b.nm_customer,
              c.nm_lengkap as request_by,
              DATE_FORMAT(a.created_date, '%d %M %Y') as request_date
            FROM
              tr_pr_product a
              LEFT JOIN customer b ON a.id_customer = b.id_customer
              LEFT JOIN users c ON c.id_user = a.created_by
            WHERE 1=1 AND a.category in ('pr material','base on production') AND a.booking_date IS NOT NULL AND a.close_pr IS NULL";
    $get_pr = $this->db->query($sql)->result_array();

    history("View index request pr material");
    $this->template->set('list_pr', $get_pr);
    $this->template->title('Purchasing Request / PR Product');
    $this->template->render('index');
  }

  public function data_side_approval_pr_material()
  {
    $this->Pr_product_model->data_side_approval_pr_material();
  }

  public function add()
  {
    $this->template->title('PR Product | Add PR');
    $this->template->render('add');
  }

  public function server_side_reorder_point()
  {
    $this->Pr_product_model->get_data_json_reorder_point();
  }

  public function clear_update_reorder()
  {
    $data = $this->input->post();
    $tgl_now = date('Y-m-d');
    $tgl_next_month = date('Y-m-' . '20', strtotime('+1 month', strtotime($tgl_now)));
    $get_materials   = $this->db->get_where('new_inventory_4', array('category' => 'material'))->result_array();

    foreach ($get_materials as $key => $value) {
      $ArrUpdate[$key]['code_lv4'] = $value['code_lv4'];
      $ArrUpdate[$key]['request'] = 0;
      $ArrUpdate[$key]['tgl_dibutuhkan'] = $tgl_next_month;
    }

    $this->db->trans_start();
    $this->db->update_batch('new_inventory_4', $ArrUpdate, 'code_lv4');
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

    $id_material   = $data['id_material'];
    $purchase     = str_replace(',', '', $data['purchase']);
    $tanggal       = $data['tanggal'];
    $keterangan       = $data['keterangan'];


    $ArrHeader = array(
      'request'       => $purchase,
      'tgl_dibutuhkan'   => $tanggal,
      'keterangan'   => $keterangan,
    );

    $this->db->trans_start();
    $this->db->where('code_lv4', $id_material);
    $this->db->update('new_inventory_4', $ArrHeader);
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

    $getraw_materials   = $this->db->get_where('new_inventory_4', array('request >' => 0))->result_array();

    $ArrSaveDetail = [];
    $SUM = 0;
    foreach ($getraw_materials as $key => $value) {
      $SUM += $value['request'];
      $ArrSaveDetail[$key]['so_number'] = $so_number;
      $ArrSaveDetail[$key]['id_material'] = $value['code_lv4'];
      $ArrSaveDetail[$key]['propose_purchase'] = $value['request'];
      $ArrSaveDetail[$key]['note'] = $value['keterangan'];
    }

    $ArrSaveHeader = array(
      'so_number'   => $so_number,
      'no_pr'   => generateNoPR(),
      'category'     => 'pr material',
      'tgl_so'     => date('Y-m-d'),
      'id_customer'   => 'C100-2401002',
      'project' => 'Pengisian Stok Internal',
      'qty_propose' => $SUM,
      'tgl_dibutuhkan' => $value['tgl_dibutuhkan'],
      'created_by'      => $this->id_user,
      'created_date'    => $this->datetime,
      'booking_by'      => $this->id_user,
      'booking_date'    => $this->datetime,
      'tingkat_pr' => $data['tingkat_pr']
    );

    // print_r($ArrSaveHeader);
    // print_r($ArrSaveDetail);
    // exit;

    $this->db->trans_start();
    $this->db->insert('material_planning_base_on_produksi', $ArrSaveHeader);
    if (!empty($ArrSaveDetail)) {
      $this->db->insert_batch('material_planning_base_on_produksi_detail', $ArrSaveDetail);
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

    $tanggal     = $data['tanggal'];
    $get_materials   = $this->db->get_where('new_inventory_4', array('category' => 'material'))->result_array();

    foreach ($get_materials as $key => $value) {
      $ArrUpdate[$key]['code_lv4'] = $value['code_lv4'];
      $ArrUpdate[$key]['tgl_dibutuhkan'] = $tanggal;
    }

    $this->db->trans_start();
    $this->db->update_batch('new_inventory_4', $ArrUpdate, 'code_lv4');
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

  public function set_update_propose_reorder()
  {
    $data = $this->input->post();
    $tgl_now = date('Y-m-d');
    $GET_OUTANDING_PR = get_pr_on_progress();
    $tgl_next_month = date('Y-m-' . '20', strtotime('+1 month', strtotime($tgl_now)));
    $get_materials   = $this->db
      ->select('a.*, b.qty_stock')
      ->join('warehouse_stock b', 'a.code_lv4 = b.id_material AND b.id_gudang = 1', 'left')
      ->get_where('new_inventory_4 a', array('a.category' => 'material'))
      ->result_array();

    foreach ($get_materials as $key => $value) {
      $outanding_pr   = (!empty($GET_OUTANDING_PR[$value['code_lv4']]) and $GET_OUTANDING_PR[$value['code_lv4']] > 0) ? $GET_OUTANDING_PR[$value['code_lv4']] : 0;

      $QTY_PR = NULL;
      if ($value['qty_stock'] < $value['min_stok']) {
        $QTY_PR = ($value['max_stok'] - ($value['qty_stock'] + $outanding_pr));
        $QTY_PR = ($QTY_PR < 0) ? NULL : $QTY_PR;
      }

      $ArrUpdate[$key]['code_lv4'] = $value['code_lv4'];
      $ArrUpdate[$key]['request'] = $QTY_PR;
      $ArrUpdate[$key]['tgl_dibutuhkan'] = $tgl_next_month;
    }

    $this->db->trans_start();
    $this->db->update_batch('new_inventory_4', $ArrUpdate, 'code_lv4');
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
      history('Set propose request material');
    }
    echo json_encode($Arr_Data);
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
      ->select('a.*, b.max_stok, b.min_stok')
      ->join('new_inventory_4 b', 'a.id_material=b.code_lv4', 'left')
      ->get_where(
        'material_planning_base_on_produksi_detail a',
        array(
          'a.so_number' => $so_number
        )
      )
      ->result_array();

    $this->db->select('a.*');
    $this->db->from('new_inventory_4 a');
    $this->db->where('a.category', 'material');
    $this->db->where('(SELECT COUNT(aa.id) FROM material_planning_base_on_produksi_detail aa WHERE aa.so_number = "' . $so_number . '" AND aa.id_material = a.code_lv4) <', 1);
    $list_material_non_pr = $this->db->get()->result_array();

    $data = [
      'so_number' => $so_number,
      'header' => $header,
      'detail' => $detail,
      'list_material_non_pr' => $list_material_non_pr,
      'GET_LEVEL4'   => get_inventory_lv4(),
      'GET_STOK_PUSAT' => getStokMaterial(1)
    ];

    $this->template->title('Edit PR - ' . $so_number);
    $this->template->render('edit_planning', $data);
  }

  public function process_approval_satuan()
  {
    $data       = $this->input->post();
    $id          = $data['id'];
    $action      = $data['action'];
    $so_number  = $data['so_number'];
    $pr_rev      = str_replace(',', '', $data['pr_rev']);

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

  public function process_update_all()
  {
    $data       = $this->input->post();
    $detail      = $data['detail'];
    $so_number  = $data['so_number'];

    $ArrUpdate = [];
    foreach ($detail as $key => $value) {
      $ArrUpdate[$key]['id'] = $value['id'];
      $ArrUpdate[$key]['propose_purchase'] = str_replace(',', '', $value['qty']);
      $ArrUpdate[$key]['note'] = $value['note'];
    }

    $get_pr = $this->db->get_where('material_planning_base_on_produksi', ['so_number' => $so_number])->row();


    $this->db->trans_start();
    $this->db->update('material_planning_base_on_produksi', [
      'no_rev' => ($get_pr->no_rev + 1),
      'reject_status' => '0',
      'tgl_dibutuhkan' => $data['tgl_dibutuhkan'],
      'tingkat_pr' => $data['tingkat_pr'],
      'keterangan_1' => $data['keterangan_1'],
      'keterangan_2' => $data['keterangan_2'],
      'keterangan_3' => $data['keterangan_3']
    ], ['so_number' => $so_number]);
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

  public function print_new()
  {
    $kode  = $this->uri->segment(3);
    $data_session  = $this->session->userdata;
    $session        = $this->session->userdata('app_session');
    $printby    = $session['id_user'];

    $data_url    = base_url();
    $Split_Beda    = explode('/', $data_url);
    $Jum_Beda    = count($Split_Beda);
    $Nama_Beda    = $Split_Beda[$Jum_Beda - 2];

    $getData        = $this->db->get_where('material_planning_base_on_produksi', array('so_number' => $kode))->result_array();
    $getDataDetail  = $this->db->get_where('material_planning_base_on_produksi_detail', array('so_number' => $kode))->result_array();
    $getCustomer    = $this->db->get_where('customer', array('id_customer' => $getData[0]['id_customer']))->result_array();

    $data = array(
      'Nama_Beda' => $Nama_Beda,
      'printby' => $printby,
      'getData' => $getData,
      'getDataDetail' => $getDataDetail,
      'getCustomer' => $getCustomer,
      'GET_DET_Lv4' => get_inventory_lv4(),
      'GET_ACCESSORIES' => get_accessories(),
      'kode' => $kode
    );
    $this->load->view('print_new', $data);
  }

  public function PrintH2()
  {
    ob_clean();
    ob_start();
    $this->auth->restrict($this->managePermission);
    $id = $this->uri->segment(3);
    $data['header'] = $this->db->query("SELECT a.*, b.nm_customer, b.alamat, c.name as country_name, d.nm_pic, d.hp, d.email_pic, b.fax FROM material_planning_base_on_produksi as a LEFT JOIN material_planning_base_on_produksi x ON x.so_number = a.so_number LEFT JOIN customer b ON b.id_customer = a.id_customer LEFT JOIN country_all c ON c.iso3 = b.country_code LEFT JOIN customer_pic d ON d.id_pic = b.id_pic WHERE a.so_number = '" . $id . "' ")->result();
    $data['detail']  = $this->db->query("SELECT a.*, b.nama FROM material_planning_base_on_produksi_detail a 
		INNER JOIN new_inventory_4 b ON b.code_lv4 = a.id_material
		WHERE a.so_number = '" . $id . "' ")->result();
    // $data['detailsum'] = $this->db->query("SELECT AVG(width) as totalwidth, AVG(qty) as totalqty FROM dt_trans_po WHERE no_po = '" . $id . "' ")->result();
    $this->load->view('Print', $data);
    $html = ob_get_contents();

    // print_r($data['header']);
    // exit;

    require_once('./assets/html2pdf/html2pdf/html2pdf.class.php');
    $html2pdf = new HTML2PDF('P', 'A4', 'en', true, 'UTF-8', array(10, 5, 10, 5));
    $html2pdf->pdf->SetDisplayMode('fullpage');
    $html2pdf->WriteHTML($html);
    ob_end_clean();
    $html2pdf->Output('Purchase Request.pdf', 'I');

    // $this->template->title('Testing');
    // $this->template->render('print2');
  }

  public function edit_detail()
  {
    $post = $this->input->post();

    $valid = 1;
    $this->db->trans_begin();

    $this->db->update('material_planning_base_on_produksi_detail', [
      'propose_purchase' => $post['qty_pr'],
      'note' => $post['notes']
    ], [
      'id' => $post['id']
    ]);

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

  public function add_material()
  {
    $post = $this->input->post();

    $this->db->trans_begin();

    $ArrData = [
      'so_number' => $post['so_number'],
      'id_material' => $post['id_material'],
      'propose_purchase' => $post['qty_pr'],
      'status_app' => 'N',
      'note' => $post['notes']
    ];
    $this->db->insert('material_planning_base_on_produksi_detail', $ArrData);

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

  public function get_refresh_material()
  {
    $post = $this->input->post();

    $arr_pr_material = [];
    $get_pr_material = $this->db->select('id_material')->get_where('material_planning_base_on_produksi_detail', ['so_number' => $post['so_number']])->result_array();
    foreach ($get_pr_material as $pr_material) {
      $arr_pr_material[] = $pr_material['id_material'];
    }
    var_dump($arr_pr_material);
    exit;

    $this->db->select('a.code_lv4, a.nama');
    $this->db->from('new_inventory_4 a');
    $this->db->where('a.category', 'material');
    $this->db->where_not_in('a.code_lv4', $arr_pr_material);
    $get_material_non_pr = $this->db->get()->result_array();

    $hasil = '';
    $no = 1;
    foreach ($get_material_non_pr as $material_non_pr) {
      $hasil .= '<tr>';
      $hasil .= '<td class="text-center">' . $no . '</td>';
      $hasil .= '<td>' . $material_non_pr['nama'] . '</td>';
      $hasil .= '<td class="text-right">' . number_format($material_non_pr['min_stok'], 2) . '</td>';
      $hasil .= '<td class="text-right">' . number_format($material_non_pr['max_stok'], 2) . '</td>';
      $hasil .= '<td class="text-right">' . number_format(0, 2) . '</td>';
      $hasil .= '<td><input type="text" class="form-control form-control-sm autoNumeric2 nmat_qty_pr_' . $material_non_pr['code_lv4'] . '" data-id_material="' . $material_non_pr['code_lv4'] . '"></td>';
      $hasil .= '<td><input type="text" class="form-control form-control-sm nmat_notes_' . $material_non_pr['code_lv4'] . '" data-id_material="' . $material_non_pr['code_lv4'] . '"></td>';
      $hasil .= '<td class="text-center"><button type="button" class="btn btn-sm btn-success add_material_pr add_material_pr_' . $material_non_pr['code_lv4'] . '" data-id_material="' . $material_non_pr['code_lv4'] . '"><i class="fa fa-plus"></i></button></td>';
      $hasil .= '</tr>';

      $no++;
    }

    echo $hasil;
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

  public function get_data_product()
  {
    $draw = $this->input->post('draw');
    $start = $this->input->post('start');
    $length = $this->input->post('length');
    $search = $this->input->post('search');

    $product_type = $this->input->post('product_type');

    // print_r($this->input->post());
    // exit;

    $this->db->select('
      a.*, 
      b.category, 
      (SELECT ng_stock FROM stock_product WHERE id = MAX(a.id)) AS stock_ng,
      (SELECT actual_stock FROM stock_product WHERE id = MAX(a.id)) AS stock_akhir,
      (SELECT booking_stock FROM stock_product WHERE id = MAX(a.id)) AS booking_akhir
    ');
    $this->db->from('stock_product a');
    $this->db->join('bom_header b', 'b.no_bom = a.no_bom', 'left');
    $this->db->join('new_inventory_4 c', 'c.code_lv4 = a.code_lv4', 'left');
    $this->db->where('a.deleted_by', null);
    if ($product_type !== '' && $product_type !== null) {
      $this->db->where('c.code_lv1', $product_type);
    }
    if ($search['value'] !== '' && $search['value'] !== null) {
      $this->db->group_start();
      $this->db->like('a.product_name', $search['value'], 'both');
      $this->db->or_like('b.category', $search['value'], 'both');
      $this->db->group_end();
    }
    $this->db->group_by(array('a.no_bom', 'a.code_lv4'));
    $this->db->limit($length, $start);
    $get_data = $this->db->get()->result();
    $totalFiltered = count($get_data);

    $this->db->select('
      a.*, 
      b.category, 
      (SELECT ng_stock FROM stock_product WHERE id = MAX(a.id)) AS stock_ng,
      (SELECT actual_stock FROM stock_product WHERE id = MAX(a.id)) AS stock_akhir,
      (SELECT booking_stock FROM stock_product WHERE id = MAX(a.id)) AS booking_akhir
    ');
    $this->db->from('stock_product a');
    $this->db->join('bom_header b', 'b.no_bom = a.no_bom', 'left');
    $this->db->join('new_inventory_4 c', 'c.code_lv4 = a.code_lv4', 'left');
    $this->db->where('a.deleted_by', null);
    if ($product_type !== '' && $product_type !== null) {
      $this->db->where('c.code_lv1', $product_type);
    }
    if ($search['value'] !== '' && $search['value'] !== null) {
      $this->db->group_start();
      $this->db->like('a.product_name', $search['value'], 'both');
      $this->db->or_like('b.category', $search['value'], 'both');
      $this->db->group_end();
    }
    $this->db->group_by(array('a.no_bom', 'a.code_lv4'));
    $all_data = $this->db->get()->result();
    $total = count($all_data);

    $hasil = [];
    $no = $start + 1;
    foreach ($get_data as $list_data) {

      $nilai_propose = ($list_data->propose_request > 0) ? $list_data->propose_request : '';

      $hasil[] = [
        'no' => $no,
        'type_bom' => strtoupper($list_data->category),
        'product_name' => $list_data->product_name,
        'actual_stock_downgrade' => number_format($list_data->stock_ng),
        'actual_stock_oke' => number_format($list_data->stock_akhir),
        'propose' => '<input type="text" class="form-control form-control-sm propose propose_' . $list_data->id . ' auto_num text-right" data-id="' . $list_data->id . '" value="' . $nilai_propose . '">'
      ];

      $no++;
    }

    echo json_encode([
      'draw' => intval($draw),
      'recordsTotal' => $total,
      'recordsFiltered' => $total,
      'data' => $hasil
    ]);
  }

  public function input_propose()
  {
    $post = $this->input->post();

    $id = $post['id'];
    $nilai = $post['nilai'];

    $this->db->trans_begin();

    $this->db->update('stock_product', ['propose_request' => $nilai], ['id' => $id]);

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

  public function clear_propose_request()
  {
    $this->db->trans_begin();

    $this->db->update('stock_product', ['propose_request' => 0]);

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
}
