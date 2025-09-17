<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Production extends Admin_Controller
{
  //Permission
  protected $viewPermission   = 'Production_List.View';
  protected $addPermission    = 'Production_List.Add';
  protected $managePermission = 'Production_List.Manage';
  protected $deletePermission = 'Production_List.Delete';

  public function __construct()
  {
    parent::__construct();

    $this->load->library(array('upload', 'Image_lib'));
    $this->load->model(array(
      'Production/production_model'
    ));
    // $this->template->title('Manage Data Supplier');

    date_default_timezone_set('Asia/Bangkok');

    $this->id_user  = $this->auth->user_id();
    $this->datetime = date('Y-m-d H:i:s');
  }

  public function index()
  {
    $this->auth->restrict($this->viewPermission);
    $session  = $this->session->userdata('app_session');
    $this->template->page_icon('fa fa-users');

    $listType = $this->db->get_where('new_inventory_1', array('deleted_date' => NULL, 'category' => 'product'))->result_array();

    $data = [
      'listType' => $listType,
    ];
    history("View data production list");
    $this->template->title('Production List');
    $this->template->render('index', $data);
  }

  public function data_side_spk_material()
  {
    $this->production_model->data_side_spk_material();
  }

  //Input Produksi
  public function release_aktual()
  {
    $data         = $this->input->post();
    $session      = $this->session->userdata('app_session');

    $id        = $data['id'];

    $Arr_Data  = array(
      'id'    => $id
    );
    echo json_encode($Arr_Data);
  }

  public function input_produksi($id = null)
  {
    if ($this->input->post()) {
      $data         = $this->input->post();
      $session      = $this->session->userdata('app_session');

      $id        = $data['id_uniq'];
      $qty_ke    = $data['qty_ke'];
      $qty_mixing    = $data['qty_mixing'];
      $qty_mixing_max    = $data['qty_mixing'] + $qty_ke - 1;
      $Detail    = $data['detail2'];
      $qty_produksi        = $data['qty_produksi'];
      $id        = $data['id'];
      $close_date       = (!empty($data['close_date'])) ? date('Y-m-d', strtotime($data['close_date'])) : null;
      $check_close      = (!empty($data['check_close'])) ? $data['check_close'] : 0;
      $check_close_all  = (!empty($data['check_close_all'])) ? $data['check_close_all'] : 0;

      $id_shift        = $data['id_shift'];
      $id_mesin        = $data['id_mesin'];

      // $ArrInsert1 = [];
      // $ArrStock1 = [];
      // if(!empty($data['detail'])){
      //   foreach ($data['detail'] as $key => $value) {
      //       $ArrInsert1[$key]['id'] = $value['id'];
      //       $ArrInsert1[$key]['code_material_aktual'] = $value['code_material_aktual'];
      //       $ArrInsert1[$key]['weight_aktual'] = str_replace(',','',$value['weight_aktual']);

      //       $ArrStock1[$key]['id'] = $value['code_material_aktual'];
      //       $ArrStock1[$key]['qty'] = str_replace(',','',$value['weight_aktual']);
      //   }
      // }

      // $ArrInsert2 = [];
      // $ArrStock2 = [];
      // if(!empty($data['detail2'])){
      //   foreach ($data['detail2'] as $key => $value) {
      //       $ArrInsert2[$key]['id'] = $value['id'];
      //       $ArrInsert2[$key]['code_material_aktual'] = $value['code_material_aktual'];
      //       $ArrInsert2[$key]['weight_aktual'] = str_replace(',','',$value['weight_aktual']);

      //       $ArrStock2[$key]['id'] = $value['code_material_aktual'];
      //       $ArrStock2[$key]['qty'] = str_replace(',','',$value['weight_aktual']);
      //   }
      // }

      // $ArrInsert = array_merge($ArrInsert1,$ArrInsert2);
      // $ArrStock = array_merge($ArrStock1,$ArrStock2);

      $getData        = $this->db->get_where('so_internal_spk', array('id' => $id))->result_array();
      $no_spk         = $getData[0]['no_spk'];
      $kode           = $getData[0]['kode'];
      $qty            = $getData[0]['qty'];
      $kode_trans     = $getData[0]['kode_det'];
      $id_costcenter  = $getData[0]['id_costcenter'];
      $id_gudang_dari = get_name('warehouse', 'id', 'kd_gudang', $id_costcenter);
      $nm_costcenter  = 'AKTUAL - ' . strtoupper(get_name('ms_costcenter', 'nama_costcenter', 'id_costcenter', $id_costcenter));
      $nm_costcenter2 =  strtolower(get_name('ms_costcenter', 'nama_costcenter', 'id_costcenter', $id_costcenter));

      $id_gudang_ke = 14;

      $ArrInsert = [];
      $ArrStock = [];
      $ArrStockBack = [];
      $ArrayID = [];
      $ArrQty = [];
      foreach ($Detail as $key => $value) {
        $ArrayID[] = $value['id'];

        $berat_aktual = str_replace(',', '', $value['berat_aktual']) + str_replace(',', '', $value['berat_aktual_plus']);
        for ($i = $qty_ke; $i <= $qty_mixing_max; $i++) {
          $ArrQty[] = $i;
          $UNIQ = $key . '-' . $i;
          $ArrInsert[$UNIQ]['id_det_spk'] = $value['id'];
          $ArrInsert[$UNIQ]['qty_ke'] = $i;
          $ArrInsert[$UNIQ]['code_material'] = $value['code_material'];
          $ArrInsert[$UNIQ]['weight'] = str_replace(',', '', $value['berat']);
          $ArrInsert[$UNIQ]['code_material_aktual'] = $value['code_material_aktual'];
          $ArrInsert[$UNIQ]['weight_aktual'] =  $berat_aktual / $qty_mixing;
          $ArrInsert[$UNIQ]['created_by'] = $this->id_user;
          $ArrInsert[$UNIQ]['created_date'] = $this->datetime;
          $ArrInsert[$UNIQ]['gudang'] = $nm_costcenter2;
          $ArrInsert[$UNIQ]['close_produksi'] = $close_date;
          $ArrInsert[$UNIQ]['id_shift'] = $id_shift;
          $ArrInsert[$UNIQ]['id_mesin'] = $id_mesin;
        }

        $ArrStock[$key]['id'] = $value['code_material'];
        $ArrStock[$key]['qty'] =  $berat_aktual;
      }

      //STockPlus
      if (!empty($ArrayID)) {
        $getdata = $this->db->where_in('id_det_spk', $ArrayID)->get_where('so_internal_spk_material_pengeluaran', array('qty_ke' => $qty_ke))->result_array();
        foreach ($getdata as $key => $value) {
          $ArrStockBack[$key]['id']   = $value['code_material_aktual'];
          $ArrStockBack[$key]['qty']  = str_replace(',', '', $value['weight_aktual']);
        }
      }

      $ArrUpdate = [
        'tanggal_close' => $close_date,
        'sts_close' => ($check_close == '0') ? 'P' : 'Y',
        'close_by' => $this->id_user,
        'close_date' => ($check_close_all == '0') ? NULL : $this->datetime,
        // 'sts_mixing' => ($check_close_all == '0')?'P':'Y'
      ];

      $InsertQC = [];
      for ($i = $qty_ke; $i <= $qty_mixing_max; $i++) {
        $InsertQC[] = [
          'id_key_spk' => $id,
          'kode' => $kode,
          'kode_det' => $kode_trans,
          'no_spk' => $no_spk,
          'qty' => $qty,
          'product_ke' => $i,
          'close_produksi' => $close_date,
          'close_by' => $this->id_user,
          'close_date' => $this->datetime
        ];
      }

      $getDataClose        = $this->db->get_where('so_internal_spk_material', array('kode_det' => $kode_trans))->result_array();
      $ArrayIDCL = [];
      foreach ($getDataClose as $key => $value) {
        $ArrayIDCL[] = $value['id'];
      }

      $ArrClose = [
        'close_by' => $this->id_user,
        'close_date' => $this->datetime
      ];

      // print_r($ArrInsert);
      // print_r($InsertQC);
      // print_r($ArrInsert);
      // exit;

      $this->db->trans_start();
      if (!empty($ArrInsert)) {
        $this->db->where_in('id_det_spk', $ArrayID);
        $this->db->where_in('qty_ke', $ArrQty);
        $this->db->delete('so_internal_spk_material_pengeluaran');

        $this->db->insert_batch('so_internal_spk_material_pengeluaran', $ArrInsert);
      }

      $this->db->where('id', $id);
      $this->db->update('so_internal_spk', $ArrUpdate);

      if ($check_close == '1') {
        $this->db->insert_batch('so_internal_product', $InsertQC);
      }

      if (!empty($ArrayIDCL) and ($check_close == '1' or $check_close_all == '1')) {
        $this->db->where_in('id_det_spk', $ArrayIDCL);
        $this->db->where_in('qty_ke', $ArrQty);
        $this->db->update('so_internal_spk_material_pengeluaran', $ArrClose);
      }

      $this->db->trans_complete();

      if ($this->db->trans_status() === FALSE) {
        $this->db->trans_rollback();
        $Arr_Data  = array(
          'pesan'    => 'Save gagal disimpan ...',
          'status'  => 0,
          'id' => $id,
          'close' => 0
        );
      } else {
        $this->db->trans_commit();

        $checkCLose = (!empty(checkInputProduksiQty($id)[$id])) ? checkInputProduksiQty($id)[$id] : 0;
        $checkInputMixingQty = ($checkCLose == $qty_produksi) ? 1 : 0;

        if ($checkInputMixingQty == 1) {
          $ArrUpdate = [
            'close_by' => $this->id_user,
            'close_date' => $this->datetime
          ];

          $this->db->where('id', $id);
          $this->db->update('so_internal_spk', $ArrUpdate);
        }

        $Arr_Data  = array(
          'pesan'    => 'Save berhasil disimpan. Thanks ...',
          'status'  => 1,
          'id' => $id,
          'close' => $checkInputMixingQty
        );

        if (!empty($ArrStockBack)) {
          move_warehouse($ArrStockBack, $id_gudang_ke, $id_gudang_dari, $kode_trans, $nm_costcenter);
        }
        move_warehouse($ArrStock, $id_gudang_dari, $id_gudang_ke, $kode_trans, $nm_costcenter);
        history("Input aktual produksi : " . $id);
      }
      echo json_encode($Arr_Data);
    } else {

      $getData = $this->db
        ->select('b.*, a.*, a.id AS id_uniq')
        ->join('so_internal b', 'a.id_so=b.id', 'left')
        ->get_where('so_internal_spk a', array(
          'a.id' => $id
        ))
        ->result_array();


      $id_gudang = get_name('warehouse', 'id', 'kd_gudang', $getData[0]['id_costcenter']);
      $kode  = $getData[0]['kode_det'];
      $qty   = $getData[0]['qty'];
      $getMaterialMixing  = $this->db
        ->select('a.id, a.code_material, a.weight AS berat, a.weight_aktual, b.weight_aktual AS berat_subgudang')
        ->join('so_internal_spk_material_pengeluaran b', 'a.id=b.id_det_spk', 'left')
        ->get_where('so_internal_spk_material a', array('a.type_name' => 'mixing', 'a.kode_det' => $kode))
        ->result_array();
      $getMaterialNonMixing  = $this->db->select('id, code_material, weight AS berat, weight_aktual')->where('kode_det', $kode)->get_where('so_internal_spk_material', array('type_name <>' => 'mixing'))->result_array();

      $getShift = $this->db->select('id_type_shift AS id, name_type_shift AS nama')->get_where('ms_type_shift', array('status' => '1'))->result_array();
      $getMachine = $this->db->select('MIN(id) AS id, nm_asset AS nama')->group_by('nm_asset')->order_by('id', 'asc')->get_where('asset', array('category' => '4', 'deleted_date' => NULL))->result_array();

      $no_bom = $getData[0]['no_bom'];
      $GetNamaBOMProduct  = get_name_product_by_bom($no_bom);
      $NamaProduct         = (!empty($GetNamaBOMProduct[$no_bom])) ? $GetNamaBOMProduct[$no_bom] : 0;

      $data = [
        'getData' => $getData,
        'tipe_mixing_set' => (!empty($getData[0]['tipe_mixing'])) ? $getData[0]['tipe_mixing'] : null,
        'tipe_mixing_name' => ($getData[0]['tipe_mixing'] == '1') ? 'Mixing Per Product' : 'Mixing Per SPK',
        'id' => $id,
        'kode' => $kode,
        'qty' => $qty,
        'NamaProduct' => $NamaProduct,
        'getShift' => $getShift,
        'getMachine' => $getMachine,
        'getMaterialMixing' => $getMaterialMixing,
        'getMaterialNonMixing' => $getMaterialNonMixing,
        'GET_STOK' => getStokMaterial($id_gudang),
        'GET_MATERIAL' => get_inventory_lv4(),
        'checkInputMixing' => checkInputMixing($kode)
      ];
      $this->template->title('Input Aktual Produksi');
      $this->template->render('input_produksi', $data);
    }
  }

  public function history_input_aktual($id = null)
  {
    $getData = $this->db
      ->select('b.*, a.*, a.id AS id_uniq')
      ->join('so_internal b', 'a.id_so=b.id', 'left')
      ->get_where('so_internal_spk a', array(
        'a.id' => $id
      ))
      ->result_array();


    $kode  = $getData[0]['kode_det'];
    $qty   = $getData[0]['qty'];
    $getMaterialMixing  = $this->db->select('id, code_material, weight AS berat, weight_aktual')->where('kode_det', $kode)->get_where('so_internal_spk_material', array('type_name' => 'mixing'))->result_array();
    $getMaterialNonMixing  = $this->db->select('id, code_material, weight AS berat, weight_aktual')->where('kode_det', $kode)->get_where('so_internal_spk_material', array('type_name <>' => 'mixing'))->result_array();

    $no_bom = $getData[0]['no_bom'];
    $GetNamaBOMProduct  = get_name_product_by_bom($no_bom);
    $NamaProduct         = (!empty($GetNamaBOMProduct[$no_bom])) ? $GetNamaBOMProduct[$no_bom] : 0;

    $data = [
      'getData' => $getData,
      'id' => $id,
      'kode' => $kode,
      'qty' => $qty,
      'NamaProduct' => $NamaProduct,
      'getMaterialMixing' => $getMaterialMixing,
      'getMaterialNonMixing' => $getMaterialNonMixing,
      'GET_MATERIAL' => get_inventory_lv4(),
      'GET_USER' => get_list_user()
    ];
    $this->template->title('Hasil Input Aktual Produksi');
    $this->template->render('history_input_aktual', $data);
  }

  public function print_record_production()
  {
    $kode  = $this->uri->segment(3);
    $data_session  = $this->session->userdata;
    $session        = $this->session->userdata('app_session');
    $printby    = $session['id_user'];

    $data_url    = base_url();
    $Split_Beda    = explode('/', $data_url);
    $Jum_Beda    = count($Split_Beda);
    $Nama_Beda    = $Split_Beda[$Jum_Beda - 2];


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
        'a.id' => $kode
      ))
      ->result_array();

    $getHeader = $this->db->get_where('so_internal_spk', array('id' => $kode))->result_array();

    $getMaterialMixing    = $this->db->select('code_material, weight AS berat')->group_by('code_material')->like('kode_det', $getHeader[0]['kode_det'])->get_where('so_internal_spk_material', array('type_name' => 'mixing'))->result_array();
    $getMaterialProduksi  = $this->db->select('code_material, weight AS berat')->group_by('code_material')->like('kode_det', $getHeader[0]['kode_det'])->get_where('so_internal_spk_material', array('type_name <>' => 'mixing'))->result_array();

    $getMaterial = array_merge($getMaterialMixing, $getMaterialProduksi);

    $no_bom = $getData[0]['no_bom'];
    $GetNamaBOMProduct  = get_name_product_by_bom($no_bom);
    $NamaProduct         = (!empty($GetNamaBOMProduct[$no_bom])) ? $GetNamaBOMProduct[$no_bom] : 0;

    $data = array(
      'Nama_Beda' => $Nama_Beda,
      'printby' => $printby,
      'getHeader' => $getHeader,
      'NamaProduct' => $NamaProduct,
      'getData' => $getData,
      'getMaterialMixing' => $getMaterialMixing,
      'getMaterialProduksi' => $getMaterialProduksi,
      'getMaterial' => $getMaterial,
      'GET_DET_Lv4' => get_inventory_lv4(),
      'GET_DET_Lv2' => get_inventory_lv2(),
      'kode' => $kode
    );

    history('Print record production ' . $kode);
    $this->load->view('print_record_production', $data);
  }

  public function history_input_aktual_excel($id = null)
  {
    set_time_limit(0);
    ini_set('memory_limit', '1024M');
    $this->load->library("PHPExcel");

    $objPHPExcel    = new PHPExcel();

    $whiteCenterBold    = whiteCenterBold();
    $whiteRightBold      = whiteRightBold();
    $whiteCenter        = whiteCenter();
    $mainTitle          = mainTitle();
    $tableHeader        = tableHeader();
    $tableBodyCenter    = tableBodyCenter();
    $tableBodyLeft      = tableBodyLeft();
    $tableBodyRight      = tableBodyRight();

    $Arr_Bulan  = array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
    $sheet      = $objPHPExcel->getActiveSheet();

    $GET_MATERIAL = get_inventory_lv4();
    $GET_USER = get_list_user();
    $getData = $this->db
      ->select('b.*, a.*, a.id AS id_uniq')
      ->join('so_internal b', 'a.id_so=b.id', 'left')
      ->get_where('so_internal_spk a', array(
        'a.id' => $id
      ))
      ->result_array();

    $kode  = $getData[0]['kode_det'];
    $qty   = $getData[0]['qty'];

    $no_bom = $getData[0]['no_bom'];
    $GetNamaBOMProduct  = get_name_product_by_bom($no_bom);
    $NamaProduct         = (!empty($GetNamaBOMProduct[$no_bom])) ? $GetNamaBOMProduct[$no_bom] : 0;

    $getMaterialMixingHead  = $this->db->select('*')->get_where('so_internal_spk_material', array('kode_det' => $kode, 'type_name' => 'mixing'))->result_array();
    $ArrayID = [];
    foreach ($getMaterialMixingHead as $key => $value) {
      $ArrayID[] = $value['id'];
    }
    $getMaterialNonMixingHead  = $this->db->select('*')->get_where('so_internal_spk_material', array('kode_det' => $kode, 'type_name <>' => 'mixing'))->result_array();
    $ArrayID2 = [];
    foreach ($getMaterialNonMixingHead as $key => $value) {
      $ArrayID2[] = $value['id'];
    }

    $getMaterialMixing    = $this->db->order_by('qty_ke', 'asc')->order_by('id', 'asc')->where_in('id_det_spk', $ArrayID)->get('so_internal_spk_material_pengeluaran')->result_array();
    $getMaterialNonMixing = $this->db->order_by('qty_ke', 'asc')->order_by('id', 'asc')->where_in('id_det_spk', $ArrayID2)->get('so_internal_spk_material_pengeluaran')->result_array();

    $dateX  = date('Y-m-d H:i:s');
    $Row        = 1;
    $NewRow     = $Row + 1;
    $Col_Akhir  = $Cols = getColsChar(7);
    $sheet->setCellValue('A' . $Row, "HISTORY INPUT AKTUAL PRODUKSI");
    $sheet->getStyle('A' . $Row . ':' . $Col_Akhir . $NewRow)->applyFromArray($mainTitle);
    $sheet->mergeCells('A' . $Row . ':' . $Col_Akhir . $NewRow);

    $NewRow = $NewRow + 2;
    $NextRow = $NewRow;

    $sheet->getColumnDimension("A")->setAutoSize(true);
    $sheet->setCellValue('A' . $NewRow, 'Sales Order');
    $sheet->getStyle('A' . $NewRow . ':B' . $NextRow)->applyFromArray($tableBodyLeft);
    $sheet->mergeCells('A' . $NewRow . ':B' . $NextRow);

    $sheet->getColumnDimension("C")->setAutoSize(true);
    $sheet->setCellValue('C' . $NewRow, $getData[0]['so_number']);
    $sheet->getStyle('C' . $NewRow . ':C' . $NextRow)->applyFromArray($tableBodyLeft);
    $sheet->mergeCells('C' . $NewRow . ':C' . $NextRow);

    $NewRow = $NewRow + 1;
    $NextRow = $NewRow;

    $sheet->getColumnDimension("A")->setAutoSize(true);
    $sheet->setCellValue('A' . $NewRow, 'Product Name');
    $sheet->getStyle('A' . $NewRow . ':B' . $NextRow)->applyFromArray($tableBodyLeft);
    $sheet->mergeCells('A' . $NewRow . ':B' . $NextRow);

    $sheet->getColumnDimension("C")->setAutoSize(true);
    $sheet->setCellValue('C' . $NewRow, strtoupper($NamaProduct));
    $sheet->getStyle('C' . $NewRow . ':C' . $NextRow)->applyFromArray($tableBodyLeft);
    $sheet->mergeCells('C' . $NewRow . ':C' . $NextRow);

    $NewRow = $NewRow + 1;
    $NextRow = $NewRow;

    $sheet->getColumnDimension("A")->setAutoSize(true);
    $sheet->setCellValue('A' . $NewRow, 'No SPK');
    $sheet->getStyle('A' . $NewRow . ':B' . $NextRow)->applyFromArray($tableBodyLeft);
    $sheet->mergeCells('A' . $NewRow . ':B' . $NextRow);

    $sheet->getColumnDimension("C")->setAutoSize(true);
    $sheet->setCellValue('C' . $NewRow, strtoupper($getData[0]['no_spk']));
    $sheet->getStyle('C' . $NewRow . ':C' . $NextRow)->applyFromArray($tableBodyLeft);
    $sheet->mergeCells('C' . $NewRow . ':C' . $NextRow);

    $NewRow = $NewRow + 1;
    $NextRow = $NewRow;

    $sheet->getColumnDimension("A")->setAutoSize(true);
    $sheet->setCellValue('A' . $NewRow, 'Qty Produksi');
    $sheet->getStyle('A' . $NewRow . ':B' . $NextRow)->applyFromArray($tableBodyLeft);
    $sheet->mergeCells('A' . $NewRow . ':B' . $NextRow);

    $sheet->getColumnDimension("C")->setAutoSize(true);
    $sheet->setCellValue('C' . $NewRow, $getData[0]['qty']);
    $sheet->getStyle('C' . $NewRow . ':C' . $NextRow)->applyFromArray($tableBodyLeft);
    $sheet->mergeCells('C' . $NewRow . ':C' . $NextRow);

    $NewRow = $NewRow + 1;
    $NextRow = $NewRow;

    $sheet->getColumnDimension("A")->setAutoSize(true);
    $sheet->setCellValue('A' . $NewRow, 'From Warehouse');
    $sheet->getStyle('A' . $NewRow . ':B' . $NextRow)->applyFromArray($tableBodyLeft);
    $sheet->mergeCells('A' . $NewRow . ':B' . $NextRow);

    $sheet->getColumnDimension("C")->setAutoSize(true);
    $sheet->setCellValue('C' . $NewRow, strtoupper(get_name('warehouse', 'nm_gudang', 'id', $getData[0]['id_gudang'])));
    $sheet->getStyle('C' . $NewRow . ':C' . $NextRow)->applyFromArray($tableBodyLeft);
    $sheet->mergeCells('C' . $NewRow . ':C' . $NextRow);

    $NewRow = $NewRow + 1;
    $NextRow = $NewRow;

    $sheet->getColumnDimension("A")->setAutoSize(true);
    $sheet->setCellValue('A' . $NewRow, 'For Costcenter');
    $sheet->getStyle('A' . $NewRow . ':B' . $NextRow)->applyFromArray($tableBodyLeft);
    $sheet->mergeCells('A' . $NewRow . ':B' . $NextRow);

    $sheet->getColumnDimension("C")->setAutoSize(true);
    $sheet->setCellValue('C' . $NewRow, strtoupper(get_name('ms_costcenter', 'nama_costcenter', 'id_costcenter', $getData[0]['id_costcenter'])));
    $sheet->getStyle('C' . $NewRow . ':C' . $NextRow)->applyFromArray($tableBodyLeft);
    $sheet->mergeCells('C' . $NewRow . ':C' . $NextRow);

    $NewRow = $NewRow + 1;
    $NextRow = $NewRow;

    $sheet->getColumnDimension("A")->setAutoSize(true);
    $sheet->setCellValue('A' . $NewRow, 'Plan Produksi');
    $sheet->getStyle('A' . $NewRow . ':B' . $NextRow)->applyFromArray($tableBodyLeft);
    $sheet->mergeCells('A' . $NewRow . ':B' . $NextRow);

    $sheet->getColumnDimension("C")->setAutoSize(true);
    $sheet->setCellValue('C' . $NewRow, $getData[0]['tanggal']);
    $sheet->getStyle('C' . $NewRow . ':C' . $NextRow)->applyFromArray($tableBodyLeft);
    $sheet->mergeCells('C' . $NewRow . ':C' . $NextRow);

    $NewRow = $NewRow + 1;
    $NextRow = $NewRow;

    $sheet->getColumnDimension("A")->setAutoSize(true);
    $sheet->setCellValue('A' . $NewRow, 'Est. Finish');
    $sheet->getStyle('A' . $NewRow . ':B' . $NextRow)->applyFromArray($tableBodyLeft);
    $sheet->mergeCells('A' . $NewRow . ':B' . $NextRow);

    $sheet->getColumnDimension("C")->setAutoSize(true);
    $sheet->setCellValue('C' . $NewRow, $getData[0]['tanggal_est_finish']);
    $sheet->getStyle('C' . $NewRow . ':C' . $NextRow)->applyFromArray($tableBodyLeft);
    $sheet->mergeCells('C' . $NewRow . ':C' . $NextRow);

    $NewRow = $NewRow + 1;
    $NextRow = $NewRow;

    $sheet->getColumnDimension("A")->setAutoSize(true);
    $sheet->setCellValue('A' . $NewRow, 'Tgl. Selesai Produksi');
    $sheet->getStyle('A' . $NewRow . ':B' . $NextRow)->applyFromArray($tableBodyLeft);
    $sheet->mergeCells('A' . $NewRow . ':B' . $NextRow);

    $sheet->getColumnDimension("C")->setAutoSize(true);
    $sheet->setCellValue('C' . $NewRow, $getData[0]['tanggal_close']);
    $sheet->getStyle('C' . $NewRow . ':C' . $NextRow)->applyFromArray($tableBodyLeft);
    $sheet->mergeCells('C' . $NewRow . ':C' . $NextRow);

    $NewRow = $NewRow + 1;
    $NextRow = $NewRow;

    $USERNAME = (!empty($GET_USER[$getData[0]['close_by']]['nama'])) ? $GET_USER[$getData[0]['close_by']]['nama'] : '';

    $sheet->getColumnDimension("A")->setAutoSize(true);
    $sheet->setCellValue('A' . $NewRow, 'Aktual Produksi By');
    $sheet->getStyle('A' . $NewRow . ':B' . $NextRow)->applyFromArray($tableBodyLeft);
    $sheet->mergeCells('A' . $NewRow . ':B' . $NextRow);

    $sheet->getColumnDimension("C")->setAutoSize(true);
    $sheet->setCellValue('C' . $NewRow, $USERNAME);
    $sheet->getStyle('C' . $NewRow . ':C' . $NextRow)->applyFromArray($tableBodyLeft);
    $sheet->mergeCells('C' . $NewRow . ':C' . $NextRow);

    $NewRow = $NewRow + 1;
    $NextRow = $NewRow;

    $sheet->getColumnDimension("A")->setAutoSize(true);
    $sheet->setCellValue('A' . $NewRow, 'Aktual Produksi Date');
    $sheet->getStyle('A' . $NewRow . ':B' . $NextRow)->applyFromArray($tableBodyLeft);
    $sheet->mergeCells('A' . $NewRow . ':B' . $NextRow);

    $sheet->getColumnDimension("C")->setAutoSize(true);
    $sheet->setCellValue('C' . $NewRow, $getData[0]['tanggal_close']);
    $sheet->getStyle('C' . $NewRow . ':C' . $NextRow)->applyFromArray($tableBodyLeft);
    $sheet->mergeCells('C' . $NewRow . ':C' . $NextRow);

    $NewRow = $NewRow + 2;
    $NextRow = $NewRow;

    $sheet->getColumnDimension("A")->setAutoSize(true);
    $sheet->setCellValue('A' . $NewRow, 'MIXING');
    $sheet->getStyle('A' . $NewRow . ':C' . $NextRow)->applyFromArray($tableBodyLeft);
    $sheet->mergeCells('A' . $NewRow . ':C' . $NextRow);

    $NewRow = $NewRow + 1;
    $NextRow = $NewRow;

    $sheet->getColumnDimension("A")->setAutoSize(true);
    $sheet->setCellValue('A' . $NewRow, '#');
    $sheet->getStyle('A' . $NewRow . ':A' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('A' . $NewRow . ':A' . $NextRow);

    $sheet->getColumnDimension("B")->setAutoSize(true);
    $sheet->setCellValue('B' . $NewRow, 'Code');
    $sheet->getStyle('B' . $NewRow . ':B' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('B' . $NewRow . ':B' . $NextRow);

    $sheet->getColumnDimension("C")->setAutoSize(true);
    $sheet->setCellValue('C' . $NewRow, 'Nama Material');
    $sheet->getStyle('C' . $NewRow . ':C' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('C' . $NewRow . ':C' . $NextRow);

    $sheet->getColumnDimension("D")->setAutoSize(true);
    $sheet->setCellValue('D' . $NewRow, 'Product Ke');
    $sheet->getStyle('D' . $NewRow . ':D' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('D' . $NewRow . ':D' . $NextRow);

    $sheet->getColumnDimension("E")->setAutoSize(true);
    $sheet->setCellValue('E' . $NewRow, 'Kebutuhan');
    $sheet->getStyle('E' . $NewRow . ':E' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('E' . $NewRow . ':E' . $NextRow);

    $sheet->getColumnDimension("F")->setAutoSize(true);
    $sheet->setCellValue('F' . $NewRow, 'Aktual');
    $sheet->getStyle('F' . $NewRow . ':F' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('F' . $NewRow . ':F' . $NextRow);

    $sheet->getColumnDimension("G")->setAutoSize(true);
    $sheet->setCellValue('G' . $NewRow, 'Status');
    $sheet->getStyle('G' . $NewRow . ':G' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('G' . $NewRow . ':G' . $NextRow);


    if ($getMaterialMixing) {
      $awal_row   = $NextRow;
      $no = 0;
      foreach ($getMaterialMixing as $key => $value) {
        $id_material   = $value['code_material'];
        $nm_material    = (!empty($GET_MATERIAL[$id_material]['nama'])) ? $GET_MATERIAL[$id_material]['nama'] : 0;
        $code_material  = (!empty($GET_MATERIAL[$id_material]['code'])) ? $GET_MATERIAL[$id_material]['code'] : 0;
        $berat      = round($value['weight'], 4);
        $aktual      = round($value['weight_aktual'], 4);
        $aktual_sub    = $value['qty_ke'];

        $status = "?";
        if ($berat < $aktual) {
          $status = "Lebih dari Est.";
        }
        if ($berat > $aktual) {
          $status = "Kurang dari Est.";
        }
        if ($berat == $aktual) {
          $status = "Sesuai Est.";
        }

        $no++;
        $awal_row++;
        $awal_col   = 0;

        $awal_col++;
        $no   = $no;
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $no);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $code_material);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $nm_material);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $aktual_sub);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

        $awal_col++;
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $berat);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

        $awal_col++;
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $aktual);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

        $awal_col++;
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $status);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);
      }
    }


    $NewRow = $awal_row + 2;
    $NextRow = $NewRow;

    $sheet->getColumnDimension("A")->setAutoSize(true);
    $sheet->setCellValue('A' . $NewRow, 'NON-MIXING');
    $sheet->getStyle('A' . $NewRow . ':C' . $NextRow)->applyFromArray($tableBodyLeft);
    $sheet->mergeCells('A' . $NewRow . ':C' . $NextRow);

    $NewRow = $NewRow + 1;
    $NextRow = $NewRow;

    $sheet->getColumnDimension("A")->setAutoSize(true);
    $sheet->setCellValue('A' . $NewRow, '#');
    $sheet->getStyle('A' . $NewRow . ':A' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('A' . $NewRow . ':A' . $NextRow);

    $sheet->getColumnDimension("B")->setAutoSize(true);
    $sheet->setCellValue('B' . $NewRow, 'Code');
    $sheet->getStyle('B' . $NewRow . ':B' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('B' . $NewRow . ':B' . $NextRow);

    $sheet->getColumnDimension("C")->setAutoSize(true);
    $sheet->setCellValue('C' . $NewRow, 'Nama Material');
    $sheet->getStyle('C' . $NewRow . ':C' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('C' . $NewRow . ':C' . $NextRow);

    $sheet->getColumnDimension("D")->setAutoSize(true);
    $sheet->setCellValue('D' . $NewRow, '');
    $sheet->getStyle('D' . $NewRow . ':D' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('D' . $NewRow . ':D' . $NextRow);

    $sheet->getColumnDimension("E")->setAutoSize(true);
    $sheet->setCellValue('E' . $NewRow, 'Kebutuhan');
    $sheet->getStyle('E' . $NewRow . ':E' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('E' . $NewRow . ':E' . $NextRow);

    $sheet->getColumnDimension("F")->setAutoSize(true);
    $sheet->setCellValue('F' . $NewRow, 'Aktual');
    $sheet->getStyle('F' . $NewRow . ':F' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('F' . $NewRow . ':F' . $NextRow);

    $sheet->getColumnDimension("G")->setAutoSize(true);
    $sheet->setCellValue('G' . $NewRow, 'Status');
    $sheet->getStyle('G' . $NewRow . ':G' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('G' . $NewRow . ':G' . $NextRow);


    if ($getMaterialNonMixing) {
      $awal_row   = $NextRow;
      $no = 0;
      foreach ($getMaterialNonMixing as $key => $value) {
        $id_material   = $value['code_material'];
        $nm_material    = (!empty($GET_MATERIAL[$id_material]['nama'])) ? $GET_MATERIAL[$id_material]['nama'] : 0;
        $code_material  = (!empty($GET_MATERIAL[$id_material]['code'])) ? $GET_MATERIAL[$id_material]['code'] : 0;
        $berat      = round($value['weight'], 4);
        $aktual      = round($value['weight_aktual'], 4);
        $aktual_sub    = $value['qty_ke'];

        $status = "?";
        if ($berat < $aktual) {
          $status = "Lebih dari Est.";
        }
        if ($berat > $aktual) {
          $status = "Kurang dari Est.";
        }
        if ($berat == $aktual) {
          $status = "Sesuai Est.";
        }

        $no++;
        $awal_row++;
        $awal_col   = 0;

        $awal_col++;
        $no   = $no;
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $no);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $code_material);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $nm_material);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $aktual_sub);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

        $awal_col++;
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $berat);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

        $awal_col++;
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $aktual);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

        $awal_col++;
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $status);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);
      }
    }

    $sheet->setTitle('Input Aktual Produksi');
    //mulai menyimpan excel format xlsx, kalau ingin xls ganti Excel2007 menjadi Excel5
    $objWriter      = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    //sesuaikan headernya
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    //ubah nama file saat diunduh
    header('Content-Disposition: attachment;filename="aktual-produksi-' . $getData[0]['so_number'] . '-' . $getData[0]['no_spk'] . '-' . strtolower(str_replace(' ', '-', get_name('ms_costcenter', 'nama_costcenter', 'id_costcenter', $getData[0]['id_costcenter']))) . '.xls"');
    //unduh file
    $objWriter->save("php://output");
  }

  public function getChangeMaterialMixing()
  {
    $data         = $this->input->post();
    $session      = $this->session->userdata('app_session');

    $id        = $data['id'];
    $kode    = $data['kode'];

    $getMaterialMixing  = $this->db->select('*')->where('kode_det', $kode)->get_where('so_internal_spk_material', array('type_name <>' => 'mixing'))->result_array();
    $ArrayID = [];
    $ArrayIDData = [];
    foreach ($getMaterialMixing as $key => $value) {
      $ArrayID[] = $value['id'];

      $ArrayIDData[$key]['id_det_spk'] = $value['id'];
      $ArrayIDData[$key]['weight'] = $value['weight'];
    }

    $ArrInputMaterial = [];
    if (!empty($ArrayID)) {
      $getdata = $this->db->where_in('id_det_spk', $ArrayID)->get_where('so_internal_spk_material_pengeluaran', array('qty_ke' => $id))->result_array();
      foreach ($getdata as $key => $value) {
        $ArrInputMaterial[$key]['id_det_spk'] = $value['id_det_spk'];
        $ArrInputMaterial[$key]['qty_ke'] = $id;
        $ArrInputMaterial[$key]['weight'] = $value['weight'];
        $ArrInputMaterial[$key]['weight_aktual'] = $value['weight_aktual'];
        $ArrInputMaterial[$key]['balance'] = $value['weight'] - $value['weight_aktual'];
        $ArrInputMaterial[$key]['close_produksi'] = (!empty($value['close_produksi'])) ? date('d-M-Y', strtotime($value['close_produksi'])) : null;
        $ArrInputMaterial[$key]['id_shift'] = $value['id_shift'];
        $ArrInputMaterial[$key]['id_mesin'] = $value['id_mesin'];
        $ArrInputMaterial[$key]['created_date'] = $value['created_date'];
        $ArrInputMaterial[$key]['close'] = (!empty($value['close_date'])) ? 'Y' : 'N';
        $ArrInputMaterial[$key]['sts_close'] = (!empty($value['sts_close'])) ? 'Y' : 'N';
      }
    }


    $Arr_Data  = array(
      'arrayData'    => (!empty($ArrInputMaterial)) ? $ArrInputMaterial : 0,
      'ArrayIDData'    => $ArrayIDData,
    );
    echo json_encode($Arr_Data);
  }

  public function getChangeMaterialHistory()
  {
    $data         = $this->input->post();
    $session      = $this->session->userdata('app_session');

    $id        = $data['id'];
    $kode    = $data['kode'];

    $getMaterialMixing  = $this->db->select('*')->get_where('so_internal_spk_material', array('kode_det' => $kode))->result_array();
    $ArrayID = [];
    $ArrayIDData = [];
    foreach ($getMaterialMixing as $key => $value) {
      $ArrayID[] = $value['id'];

      $ArrayIDData[$key]['id_det_spk'] = $value['id'];
      $ArrayIDData[$key]['weight'] = $value['weight'];
    }

    $ArrInputMaterial = [];

    $getdata = $this->db->where_in('id_det_spk', $ArrayID)->get_where('so_internal_spk_material_pengeluaran', array('qty_ke' => $id))->result_array();
    foreach ($getdata as $key => $value) {
      $berat      = round($value['weight'], 4);
      $aktual      = round($value['weight_aktual'], 4);
      $status = "?";
      if ($berat < $aktual) {
        $status = "<span class='badge bg-red'>Lebih dari Est.</span>";
      }
      if ($berat > $aktual) {
        $status = "<span class='badge bg-blue'>Kurang dari Est.</span>";
      }
      if ($berat == $aktual) {
        $status = "<span class='badge bg-green'>Sesuai Est.</span>";
      }

      $ArrInputMaterial[$key]['id_det_spk'] = $value['id_det_spk'];
      $ArrInputMaterial[$key]['qty_ke'] = $id;
      $ArrInputMaterial[$key]['weight'] = $value['weight'];
      $ArrInputMaterial[$key]['weight_aktual'] = $value['weight_aktual'];
      $ArrInputMaterial[$key]['close_produksi'] = (!empty($value['close_produksi'])) ? date('d-M-Y', strtotime($value['close_produksi'])) : null;
      $ArrInputMaterial[$key]['created_date'] = $value['created_date'];
      $ArrInputMaterial[$key]['status'] = $status;
      $ArrInputMaterial[$key]['close'] = (!empty($value['close_date'])) ? 'Y' : 'N';
    }


    $Arr_Data  = array(
      'arrayData'    => (!empty($ArrInputMaterial)) ? $ArrInputMaterial : 0,
      'ArrayIDData'    => $ArrayIDData,
    );
    echo json_encode($Arr_Data);
  }

  public function saveClose()
  {
    if ($this->input->post()) {
      $data         = $this->input->post();
      $session      = $this->session->userdata('app_session');

      $id            = $data['id'];
      $check_close_all  = (!empty($data['check_close_all'])) ? $data['check_close_all'] : 0;

      $ArrUpdate = [
        'close_by' => $this->id_user,
        'close_date' => ($check_close_all == '0') ? NULL : $this->datetime,
        // 'sts_mixing' => ($check_close_all == '0')?'P':'Y',
        'mixing_by' => $this->id_user,
        'mixing_date' => $this->datetime
      ];

      $this->db->trans_start();
      $this->db->where('id', $id);
      $this->db->update('so_internal_spk', $ArrUpdate);
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
        history("Close input aktual mixing : " . $id);
      }
      echo json_encode($Arr_Data);
    } else {

      $getData = $this->db
        ->select('b.*, a.*, a.id AS id_uniq')
        ->join('so_internal b', 'a.id_so=b.id', 'left')
        ->get_where('so_internal_spk a', array(
          'a.id' => $id
        ))
        ->result_array();


      $id_gudang = 2;
      $kode  = $getData[0]['kode_det'];
      $qty   = $getData[0]['qty'];
      $getMaterialMixing  = $this->db->select('id, code_material, weight AS berat')->where('kode_det', $kode)->get_where('so_internal_spk_material', array('type_name' => 'mixing'))->result_array();

      $data = [
        'getData' => $getData,
        'kode' => $kode,
        'qty' => $qty,
        'id' => $id,
        'getMaterialMixing' => $getMaterialMixing,
        'GET_STOK' => getStokMaterial($id_gudang),
        'GET_MATERIAL' => get_inventory_lv4()
      ];
      $this->template->title('Input Aktual Produksi');
      $this->template->render('add', $data);
    }
  }

  public function input_produksi_ftackle($id = null)
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
    // $getMaterialMixing  = $this->db
    //                         ->select('a.id, a.code_material, a.weight AS berat, a.weight_aktual, b.weight_aktual AS berat_subgudang')
    //                         ->join('so_internal_spk_material_pengeluaran b', 'a.id=b.id_det_spk','left')
    //                         ->get_where('so_internal_spk_material a',array('a.type_name'=>'mixing','a.kode_det'=>$kode))
    //                         ->result_array();
    $getMaterialMixing  = $this->db->select('id, code_material, weight AS berat, add_material')->where('kode_det', $kode)->get_where('so_internal_spk_material', array('type_name' => 'mixing'))->result_array();
    $getMaterialNonMixing  = $this->db->select('id, code_material, weight AS berat, add_material')->where('kode_det', $kode)->get_where('so_internal_spk_material', array('type_name <>' => 'mixing'))->result_array();

    // $cycletime    = $this->db->select('b.nm_process')->join('cycletime_detail_detail b','a.id_time=b.id_time','left')->get_where('cycletime_header a', array('a.deleted_date'=>NULL,'id_product'=>$code_lv4))->result_array();

    $cycletime = $this->db->select('view AS nm_process')->get_where('list', array('category' => 'ftackle'))->result_array();

    $getProcess = $this->db->group_by('nm_process')->get_where('bom_detail', array('no_bom' => $no_bom))->result_array();
    $ArrProcess = [];
    foreach ($getProcess as $key => $value) {
      $ArrProcess[] = $value['nm_process'];
    }

    $no_bom = $getData[0]['no_bom'];
    $GetNamaBOMProduct  = get_name_product_by_bom($no_bom);
    $NamaProduct         = (!empty($GetNamaBOMProduct[$no_bom])) ? $GetNamaBOMProduct[$no_bom] : 0;

    //Stok SPK
    $QUERY = "SELECT
                  a.id_material,
                  sum( check_qty_oke ) AS qty 
                FROM
                  warehouse_adjustment_detail a
                  LEFT JOIN warehouse_adjustment b ON a.kode_trans = b.kode_trans 
                WHERE
                  b.no_ipp = '$id' 
                  AND category = 'request produksi ftackle' 
                  AND b.deleted_date IS NULL
                GROUP BY
                  a.id_material";
    $resultStokSPK = $this->db->query($QUERY)->result_array();
    $ArrStokSPK = [];
    foreach ($resultStokSPK as $key => $value) {
      $ArrStokSPK[$value['id_material']] = $value['qty'];
    }

    $data = [
      'ArrStokSPK' => $ArrStokSPK,
      'getData' => $getData,
      'id' => $id,
      'kode' => $kode,
      'NamaProduct' => $NamaProduct,
      'cycletime' => $cycletime,
      'ArrProcess' => $ArrProcess,
      'qty' => $qty,
      'getMaterialMixing' => $getMaterialMixing,
      'getMaterialNonMixing' => $getMaterialNonMixing,
      'GET_STOK' => getStokMaterial($id_gudang),
      'GET_MATERIAL' => get_inventory_lv4(),
      'checkInputMixing' => checkInputMixing($kode)
    ];
    $this->template->title('Input Aktual Produksi F-Tackle');
    $this->template->render('input_produksi_ftackle', $data);
  }

  public function process_input_produksi_ftackle($processName = null)
  {
    $data         = $this->input->post();
    $session      = $this->session->userdata('app_session');

    $Detail = $data[$processName];
    // echo "<pre>";
    // print_r($Detail);
    // exit;

    $id            = $data['id'];
    $qty_produksi = $data['qty_produksi'];
    $check_close  = (!empty($data['check_close'])) ? 1 : 0;

    $getData        = $this->db->get_where('so_internal_spk', array('id' => $id))->result_array();
    $no_spk         = $getData[0]['no_spk'];
    $kode           = $getData[0]['kode'];
    $qty            = $getData[0]['qty'];
    $kode_trans     = $getData[0]['kode_det'];
    $id_costcenter  = $getData[0]['id_costcenter'];
    $id_gudang_dari = get_name('warehouse', 'id', 'kd_gudang', $id_costcenter);
    $nm_costcenter  = 'AKTUAL - ' . strtoupper(get_name('ms_costcenter', 'nama_costcenter', 'id_costcenter', $id_costcenter));
    $nm_costcenter2 =  strtolower(get_name('ms_costcenter', 'nama_costcenter', 'id_costcenter', $id_costcenter));

    $id_gudang_ke = 14;

    $ArrInsert = [];
    $ArrStock = [];
    $ArrStockBack = [];
    $ArrayID = [];
    foreach ($Detail as $key => $value) {
      // $ArrayID[] = $value['id'];

      if (!empty($value['detail'])) {
        foreach ($value['detail'] as $key2 => $value2) {
          $KEYUNIQ = $key . '-' . $key2;
          $ArrInsert[$KEYUNIQ]['qty_produksi']  = $qty_produksi;
          $ArrInsert[$KEYUNIQ]['id_so_spk']     = $value['id_so_spk'];
          $ArrInsert[$KEYUNIQ]['qty']           = ($key2 == 1) ? str_replace(',', '', $value['qty']) : NULL;
          $ArrInsert[$KEYUNIQ]['process_name']  = $value['nm_process'];
          $ArrInsert[$KEYUNIQ]['tanggal']       = date('Y-m-d', strtotime($value['tanggal']));

          $ArrInsert[$KEYUNIQ]['id_det_spk']            = $value2['id'];
          $ArrInsert[$KEYUNIQ]['code_material']         = $value2['code_material'];
          $ArrInsert[$KEYUNIQ]['weight']                = str_replace(',', '', $value2['berat']);
          $ArrInsert[$KEYUNIQ]['code_material_aktual']  = $value2['code_material_aktual'];
          $ArrInsert[$KEYUNIQ]['weight_aktual']         = str_replace(',', '', $value2['berat_aktual']);
          $ArrInsert[$KEYUNIQ]['created_by']            = $this->id_user;
          $ArrInsert[$KEYUNIQ]['created_date']          = $this->datetime;

          $ArrStock[$KEYUNIQ]['id'] = $value2['code_material'];
          $ArrStock[$KEYUNIQ]['qty'] = str_replace(',', '', $value2['berat_aktual']);
        }
      } else {
        $KEYUNIQ = $key . '-9999';
        $ArrInsert[$KEYUNIQ]['qty_produksi']  = $qty_produksi;
        $ArrInsert[$KEYUNIQ]['id_so_spk']     = $value['id_so_spk'];
        $ArrInsert[$KEYUNIQ]['qty']           = str_replace(',', '', $value['qty']);
        $ArrInsert[$KEYUNIQ]['process_name']  = $value['nm_process'];
        $ArrInsert[$KEYUNIQ]['tanggal']       = date('Y-m-d', strtotime($value['tanggal']));

        $ArrInsert[$KEYUNIQ]['id_det_spk']            = NULL;
        $ArrInsert[$KEYUNIQ]['code_material']         = NULL;
        $ArrInsert[$KEYUNIQ]['weight']                = NULL;
        $ArrInsert[$KEYUNIQ]['code_material_aktual']  = NULL;
        $ArrInsert[$KEYUNIQ]['weight_aktual']         = NULL;
        $ArrInsert[$KEYUNIQ]['created_by']            = $this->id_user;
        $ArrInsert[$KEYUNIQ]['created_date']          = $this->datetime;
      }
    }

    $ArrUpdate = [
      'sts_close' => ($check_close == 0) ? 'P' : 'Y'
    ];

    //Insert Product
    $getMaxProduct = $this->db->select('COUNT(product_ke) AS productMax')->get_where('so_internal_product', array('id_key_spk' => $id))->result_array();
    $qtyMaxOri = (!empty($getMaxProduct[0]['productMax'])) ? $getMaxProduct[0]['productMax'] : 0;
    $qtyMax = (!empty($getMaxProduct[0]['productMax'])) ? $getMaxProduct[0]['productMax'] + 1 : 1;
    $qtyFinishing = str_replace(',', '', $value['qty']) + $qtyMaxOri;
    for ($i = $qtyMax; $i <= $qtyFinishing; $i++) {
      $InsertQC[] = [
        'id_key_spk' => $id,
        'kode' => $kode,
        'kode_det' => $kode_trans,
        'no_spk' => $no_spk,
        'qty' => $qty,
        'product_ke' => $i,
        'close_produksi' => date('Y-m-d', strtotime($value['tanggal'])),
        'close_by' => $this->id_user,
        'close_date' => $this->datetime
      ];
    }

    // echo "<pre>";
    // print_r($ArrInsert);
    // print_r($InsertQC);
    // exit;

    $this->db->trans_start();
    if (!empty($ArrInsert)) {
      $this->db->insert_batch('so_internal_spk_material_pengeluaran_ftackle', $ArrInsert);
    }

    $this->db->where('id', $id);
    $this->db->update('so_internal_spk', $ArrUpdate);

    if ($processName == 'Finishing') {
      if (!empty($InsertQC)) {
        $this->db->insert_batch('so_internal_product', $InsertQC);
      }
    }

    $this->db->trans_complete();

    if ($this->db->trans_status() === FALSE) {
      $this->db->trans_rollback();
      $Arr_Data  = array(
        'pesan'    => 'Save gagal disimpan ...',
        'status'  => 0,
        'id'      => $id,
        'close'   => $check_close
      );
    } else {
      $this->db->trans_commit();
      //check product
      $getTotWIP = $this->db->select('COUNT(product_ke) AS total')->get_where('so_internal_product', array('id_key_spk' => $id))->result_array();
      $qtyTotWIP = (!empty($getTotWIP[0]['total'])) ? $getTotWIP[0]['total'] : 0;

      if ($qtyTotWIP == $qty) {
        $ArrUpdate = [
          'sts_close' => 'Y',
          'tanggal_close' => date('Y-m-d'),
          'close_by' => $this->id_user,
          'close_date' => $this->datetime
        ];

        $this->db->where('id', $id);
        $this->db->update('so_internal_spk', $ArrUpdate);

        $check_close = 1;
      }

      $Arr_Data  = array(
        'pesan'    => 'Save berhasil disimpan. Thanks ...',
        'status'  => 1,
        'id'      => $id,
        'close'   => $check_close
      );

      move_warehouse($ArrStock, $id_gudang_dari, $id_gudang_ke, $kode_trans, $nm_costcenter);
      history("Input aktual produksi ftackle : " . $id);
    }
    echo json_encode($Arr_Data);
  }

  public function process_input_produksi_ftackle_close()
  {
    $data         = $this->input->post();
    $session      = $this->session->userdata('app_session');

    $id            = $data['id'];
    $qty_produksi = $data['qty_produksi'];
    $check_close  = 1;

    $getData        = $this->db->get_where('so_internal_spk', array('id' => $id))->result_array();
    $no_spk         = $getData[0]['no_spk'];
    $kode           = $getData[0]['kode'];
    $qty            = $getData[0]['qty'];
    $kode_trans     = $getData[0]['kode_det'];

    $ArrUpdate = [
      'sts_close' => ($check_close == 0) ? 'P' : 'Y',
      'tanggal_close' => date('Y-m-d'),
      'close_by' => $this->id_user,
      'close_date' => $this->datetime
    ];

    for ($i = 1; $i <= $qty; $i++) {
      $InsertQC[] = [
        'id_key_spk' => $id,
        'kode' => $kode,
        'kode_det' => $kode_trans,
        'no_spk' => $no_spk,
        'qty' => $qty,
        'product_ke' => $i,
        'close_produksi' => date('Y-m-d'),
        'close_by' => $this->id_user,
        'close_date' => $this->datetime
      ];
    }

    $this->db->trans_start();
    $this->db->where('id', $id);
    $this->db->update('so_internal_spk', $ArrUpdate);

    if ($check_close == '1') {
      $this->db->insert_batch('so_internal_product', $InsertQC);
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
      history("Input aktual produksi ftackle close : " . $id);
    }
    echo json_encode($Arr_Data);
  }

  public function history_input_aktual_ftackle($id = null)
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

    $no_bom = $getData[0]['no_bom'];
    $GetNamaBOMProduct  = get_name_product_by_bom($no_bom);
    $NamaProduct         = (!empty($GetNamaBOMProduct[$no_bom])) ? $GetNamaBOMProduct[$no_bom] : 0;

    $data = [
      'getData' => $getData,
      'id' => $id,
      'kode' => $kode,
      'cycletime' => $cycletime,
      'ArrProcess' => $ArrProcess,
      'NamaProduct' => $NamaProduct,
      'qty' => $qty,
      'getMaterialMixing' => $getMaterialMixing,
      'getMaterialNonMixing' => $getMaterialNonMixing,
      'GET_STOK' => getStokMaterial($id_gudang),
      'GET_MATERIAL' => get_inventory_lv4(),
      'checkInputMixing' => checkInputMixing($kode)
    ];
    $this->template->title('History Input Aktual Produksi F-Tackle');
    $this->template->render('history_input_aktual_ftackle', $data);
  }

  public function saveCloseManual()
  {
    $data         = $this->input->post();
    $session      = $this->session->userdata('app_session');

    $id            = $data['id'];
    $reason_close            = $data['reason_close'];

    $ArrUpdate = [
      'sts_close' => 'Y',
      'reason_close' => $reason_close,
      'close_by' => $this->id_user,
      'close_date' => $this->datetime,
    ];

    $this->db->trans_start();
    $this->db->where('id', $id);
    $this->db->update('so_internal_spk', $ArrUpdate);
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
      history("Close input manual produksi : " . $id);
    }
    echo json_encode($Arr_Data);
  }
}
