<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 *
 */
class Stock_origa extends Admin_Controller
{
  //Permission
  protected $viewPermission   = 'Stock_Milik_Origa.View';
  protected $addPermission    = 'Stock_Milik_Origa.Add';
  protected $managePermission = 'Stock_Milik_Origa.Manage';
  protected $deletePermission = 'Stock_Milik_Origa.Delete';

  public function __construct()
  {
    parent::__construct();

    $this->load->library(array('upload', 'Image_lib'));
    $this->load->model(array(
      'Stock_origa/stock_origa_model'
    ));
    // $this->template->title('Manage Data Supplier');
    // $this->template->page_icon('fa fa-building-o');

    date_default_timezone_set('Asia/Bangkok');

    $this->id_user  = $this->auth->user_id();
    $this->datetime = date('Y-m-d H:i:s');
  }

  public function index()
  {
    $this->auth->restrict($this->viewPermission);
    $session  = $this->session->userdata('app_session');
    // $this->template->page_icon('fa fa-users');

    history("View index stock milik origa");
    $this->template->title('SO Internal (Pengisian Stok)');
    $this->template->render('index');
  }

  public function data_side_stock_milik_origa()
  {
    $this->stock_origa_model->get_json_stock_milik_origa();
  }

  public function spk_stok($id = null, $uniq = null)
  {
    if ($this->input->post()) {
      $data         = $this->input->post();
      $session      = $this->session->userdata('app_session');

      $code_lv4      = $data['code_lv4'];
      $nama_product  = $data['nama_product'];
      $no_bom        = $data['no_bom'];
      $due_date      = date('Y-m-d', strtotime($data['due_date']));
      $propose      = str_replace(',', '', $data['propose']);
      $id_customer  = 'C100-2401002';
      $project      = 'Pengisian Stok Internal';
      $so_customer  = NULL;

      $Y = date('y');
      $SQL      = "SELECT MAX(so_number) as maxP FROM so_internal WHERE so_number LIKE 'SOI" . $Y . "%' ";
      $result    = $this->db->query($SQL)->result_array();
      $angkaUrut2    = $result[0]['maxP'];
      $urutan2      = (int)substr($angkaUrut2, 5, 4);
      $urutan2++;
      $urut2        = sprintf('%04s', $urutan2);
      $so_number    = "SOI" . $Y . $urut2;

      $ArrHeader = array(
        'so_number'       => $so_number,
        'so_customer'     => $so_customer,
        'id_customer'     => $id_customer,
        'project'         => $project,
        'code_lv4'        => $code_lv4,
        'no_bom'          => $no_bom,
        'nama_product'    => strtolower($nama_product),
        'due_date'        => $due_date,
        'propose'         => $propose,
        'created_by'      => $this->id_user,
        'created_date'    => $this->datetime
      );

      //BOM material
      $GET_LEVEL4 = get_inventory_lv4();
      $GET_LEVEL1 = get_list_inventory_lv1('material');
      $dataBOM = $this->db->select('code_material,SUM(weight) AS weight,spk')->group_by('code_material')->get_where('bom_detail', array('no_bom' => $no_bom))->result_array();
      $ArrBOMDetail = [];
      $ArrPlanningDetail = [];
      $SUM_PLANNING = 0;
      if (!empty($dataBOM)) {
        foreach ($dataBOM as $key => $value) {
          $ArrBOMDetail[$key]['so_number'] = $so_number;
          $ArrBOMDetail[$key]['code_material'] = $value['code_material'];
          $ArrBOMDetail[$key]['weight'] = $value['weight'];

          $code_lv4   = (!empty($GET_LEVEL4[$value['code_material']]['code_lv1'])) ? $GET_LEVEL4[$value['code_material']]['code_lv1'] : 0;
          $type_name  = (!empty($GET_LEVEL1[$code_lv4]['nama'])) ? $GET_LEVEL1[$code_lv4]['nama'] : 0;

          $CHECK_FTACKEL = substr($no_bom, 0, 3);
          if ($CHECK_FTACKEL == 'BFT') {
            $type_name  = $value['spk'];
          }

          $ArrBOMDetail[$key]['code_lv1'] = $code_lv4;
          $ArrBOMDetail[$key]['type_name'] = $type_name;

          //Planning
          $qty_plan = $value['weight'] * $propose;
          $ArrPlanningDetail[$key]['so_number'] = $so_number;
          $ArrPlanningDetail[$key]['id_material'] = $value['code_material'];
          $ArrPlanningDetail[$key]['qty_order'] = $qty_plan;
          $SUM_PLANNING += $qty_plan;
        }
      }

      //SO Produksi
      $ArrPlanning = array(
        'so_number' => $so_number,
        'no_pr'   => generateNoPR(),
        'tgl_so' => date('Y-m-d'),
        'id_customer' => $id_customer,
        'project' => $project,
        'qty_order' => $SUM_PLANNING,
        'created_by' => $this->id_user,
        'created_date' => $this->datetime
      );

      // print_r($ArrBOMDetail);
      // exit;

      $this->db->trans_start();
      $this->db->insert('so_internal', $ArrHeader);
      $this->db->insert('material_planning_base_on_produksi', $ArrPlanning);
      if (!empty($ArrBOMDetail)) {
        $this->db->insert_batch('so_internal_material', $ArrBOMDetail);
      }
      if (!empty($ArrPlanningDetail)) {
        $this->db->insert_batch('material_planning_base_on_produksi_detail', $ArrPlanningDetail);
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
        history("Create so internal : " . $so_number);
      }
      echo json_encode($Arr_Data);
    } else {
      $getData = $this->db->get_where('new_inventory_4', array('code_lv4' => $id))->result_array();
      $getStock = $this->db->get_where('stock_product', array('id' => $uniq))->result_array();

      $WhereIN = array('grid standard', 'standard', 'ftackel');
      $getDataBOM = $this->db
        ->select('a.*,b.nama AS nm_product')
        ->where_in('a.category', $WhereIN)
        ->join('new_inventory_4 b', 'a.id_product=b.code_lv4', 'left')
        ->get_where('bom_header a', array('a.id_product' => $id, 'a.deleted_date' => NULL, 'a.no_bom' => $getStock[0]['no_bom']))->result_array();

      $data = [
        'getData' => $getData,
        'getDataBOM' => $getDataBOM,
        'getStockProduct' => get_stock_product(),
        'getProductLv4' => get_inventory_lv4(),
        'getNameBOMProduct' => get_name_product_by_bom_all()
      ];


      $this->template->title('SO Internal (Pengisian Stok)');
      $this->template->render('spk_stok_new', $data);
    }
  }

  public function add()
  {
    if ($this->input->post()) {
      $data         = $this->input->post();
      $session      = $this->session->userdata('app_session');

      $id_product    = $data['id_product'];
      $no_bom        = $data['no_bom'];
      $stock        = str_replace(',', '', $data['stock']);
      $tanggal      = date('Y-m-d');

      $dataMaster = $this->db->get_where('new_inventory_4', array('code_lv4' => $id_product))->result();

      $ArrHeader = array(
        'tanggal'         => $tanggal,
        'code_lv4'        => $id_product,
        'no_bom'          => $no_bom,
        'product_name'    => $dataMaster[0]->nama,
        'actual_stock'    => $stock,
        'min_stock'       => $dataMaster[0]->min_stok,
        'moq'             => $dataMaster[0]->max_stok,
        'updated_by'      => $this->id_user,
        'updated_date'    => $this->datetime
      );

      $this->db->trans_start();
      $this->db->insert('stock_product', $ArrHeader);
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
        history("Stok awal : " . $id_product);
      }
      echo json_encode($Arr_Data);
    } else {
      $ArrlistCT = $this->db->group_by('code_lv4')->get('stock_product')->result_array();
      $ArrProductCT = [];
      foreach ($ArrlistCT as $key => $value) {
        $ArrProductCT[] = $value['code_lv4'];
      }

      $data = [
        'ArrProductCT' => $ArrProductCT,
      ];

      $this->template->title('SO Internal (Pengisian Stok)');
      $this->template->render('add', $data);
    }
  }

  public function spk_stok_detail($id = null)
  {

    $getData = $this->db->get_where('new_inventory_4', array('code_lv4' => $id))->result_array();

    $data = [
      'getData' => $getData,
      'getStockProduct' => get_stock_product(),
      'getProductLv4' => get_inventory_lv4(),
    ];

    $this->template->title('Detail');
    $this->template->render('spk_stok_detail', $data);
  }

  public function download_excel()
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

    $dateX  = date('Y-m-d H:i:s');
    $Row        = 1;
    $NewRow     = $Row + 1;
    $Col_Akhir  = $Cols = getColsChar(14);
    $sheet->setCellValue('A' . $Row, "STOCK ORIGA");
    $sheet->getStyle('A' . $Row . ':' . $Col_Akhir . $NewRow)->applyFromArray($mainTitle);
    $sheet->mergeCells('A' . $Row . ':' . $Col_Akhir . $NewRow);

    $NewRow = $NewRow + 2;
    $NextRow = $NewRow;

    $sheet->getColumnDimension("A")->setAutoSize(true);
    $sheet->setCellValue('A' . $NewRow, '#');
    $sheet->getStyle('A' . $NewRow . ':A' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('A' . $NewRow . ':A' . $NextRow);

    $sheet->getColumnDimension("B")->setAutoSize(true);
    $sheet->setCellValue('B' . $NewRow, 'PRODUCT TYPE');
    $sheet->getStyle('B' . $NewRow . ':B' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('B' . $NewRow . ':B' . $NextRow);

    $sheet->getColumnDimension("C")->setAutoSize(true);
    $sheet->setCellValue('C' . $NewRow, 'PRODUCT CATEGORY');
    $sheet->getStyle('C' . $NewRow . ':C' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('C' . $NewRow . ':C' . $NextRow);

    $sheet->getColumnDimension("D")->setAutoSize(true);
    $sheet->setCellValue('D' . $NewRow, 'PRODUCT TYPE');
    $sheet->getStyle('D' . $NewRow . ':D' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('D' . $NewRow . ':D' . $NextRow);

    $sheet->getColumnDimension("E")->setAutoSize(true);
    $sheet->setCellValue('E' . $NewRow, 'KODE BOM');
    $sheet->getStyle('E' . $NewRow . ':E' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('E' . $NewRow . ':E' . $NextRow);

    $sheet->getColumnDimension("F")->setAutoSize(true);
    $sheet->setCellValue('F' . $NewRow, 'KODE PRODUCT');
    $sheet->getStyle('F' . $NewRow . ':F' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('F' . $NewRow . ':F' . $NextRow);

    $sheet->getColumnDimension("G")->setAutoSize(true);
    $sheet->setCellValue('G' . $NewRow, 'PRODUCT MASTER');
    $sheet->getStyle('G' . $NewRow . ':G' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('G' . $NewRow . ':G' . $NextRow);

    $sheet->getColumnDimension("H")->setAutoSize(true);
    $sheet->setCellValue('H' . $NewRow, 'VARIANT');
    $sheet->getStyle('H' . $NewRow . ':H' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('H' . $NewRow . ':H' . $NextRow);

    $sheet->getColumnDimension("I")->setAutoSize(true);
    $sheet->setCellValue('i' . $NewRow, 'ACTUAL STOK');
    $sheet->getStyle('I' . $NewRow . ':I' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('I' . $NewRow . ':I' . $NextRow);

    $sheet->getColumnDimension("J")->setAutoSize(true);
    $sheet->setCellValue('J' . $NewRow, 'BOOKING STOCK');
    $sheet->getStyle('J' . $NewRow . ':J' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('J' . $NewRow . ':J' . $NextRow);

    $sheet->getColumnDimension("K")->setAutoSize(true);
    $sheet->setCellValue('K' . $NewRow, 'FREE STOCK');
    $sheet->getStyle('K' . $NewRow . ':K' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('K' . $NewRow . ':K' . $NextRow);

    $sheet->getColumnDimension("L")->setAutoSize(true);
    $sheet->setCellValue('L' . $NewRow, 'MIN');
    $sheet->getStyle('L' . $NewRow . ':L' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('L' . $NewRow . ':L' . $NextRow);

    $sheet->getColumnDimension("M")->setAutoSize(true);
    $sheet->setCellValue('M' . $NewRow, 'MOQ');
    $sheet->getStyle('M' . $NewRow . ':M' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('M' . $NewRow . ':M' . $NextRow);

    $sheet->getColumnDimension("N")->setAutoSize(true);
    $sheet->setCellValue('N' . $NewRow, 'PROPOSE');
    $sheet->getStyle('N' . $NewRow . ':N' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('N' . $NewRow . ':N' . $NextRow);

    // $sheet ->getColumnDimension("O")->setAutoSize(true);
    // $sheet->setCellValue('O'.$NewRow, 'Qty');
    // $sheet->getStyle('O'.$NewRow.':O'.$NextRow)->applyFromArray($whiteCenterBold);
    // $sheet->mergeCells('O'.$NewRow.':O'.$NextRow);

    // $sheet ->getColumnDimension("P")->setAutoSize(true);
    // $sheet->setCellValue('P'.$NewRow, 'Qty');
    // $sheet->getStyle('P'.$NewRow.':P'.$NextRow)->applyFromArray($whiteCenterBold);
    // $sheet->mergeCells('P'.$NewRow.':P'.$NextRow);

    // $sheet ->getColumnDimension("Q")->setAutoSize(true);
    // $sheet->setCellValue('Q'.$NewRow, 'Qty');
    // $sheet->getStyle('Q'.$NewRow.':Q'.$NextRow)->applyFromArray($whiteCenterBold);
    // $sheet->mergeCells('Q'.$NewRow.':Q'.$NextRow);

    $SQL = "SELECT
                  a.*,
                  (SELECT actual_stock FROM stock_product WHERE id = MAX(a.id)) AS stock_akhir,
                  (SELECT booking_stock FROM stock_product WHERE id = MAX(a.id)) AS booking_akhir,
                  b.nama AS nm_product,
                  b.min_stok,
                  b.max_stok,
                  c.variant_product,
                  b.code_lv1,
                  b.code_lv2,
                  b.code_lv3
                FROM
                  stock_product a
                  INNER JOIN bom_header c ON a.no_bom=c.no_bom
                  LEFT JOIN new_inventory_4 b ON a.code_lv4=b.code_lv4
                WHERE 1=1 AND c.category IN ('grid standard','standard','ftackel') 
                GROUP BY a.code_lv4, a.no_bom
            ";

    $dataResult   = $this->db->query($SQL)->result_array();
    $GET_UNIT = get_list_satuan();
    $GET_LEVEL3 = get_inventory_lv3();
    $GET_LEVEL2 = get_inventory_lv2();
    $GET_LEVEL1 = get_list_inventory_lv1('product');
    if ($dataResult) {
      $awal_row   = $NextRow;
      $no = 0;
      foreach ($dataResult as $key => $vals) {
        $no++;
        $awal_row++;
        $awal_col   = 0;

        $awal_col++;
        $no   = $no;
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $no);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $code_lv1   = (!empty($GET_LEVEL1[$vals['code_lv1']]['nama'])) ? $GET_LEVEL1[$vals['code_lv1']]['nama'] : '';
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $code_lv1);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $code_lv2   = (!empty($GET_LEVEL2[$vals['code_lv2']]['nama'])) ? $GET_LEVEL2[$vals['code_lv2']]['nama'] : '';
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $code_lv2);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $code_lv3   = (!empty($GET_LEVEL3[$vals['code_lv3']]['nama'])) ? $GET_LEVEL3[$vals['code_lv3']]['nama'] : '';
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $code_lv3);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $no_bom   = $vals['no_bom'];
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $no_bom);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $code_lv4   = $vals['code_lv4'];
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $code_lv4);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $nama   = $vals['nm_product'];
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $nama);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $variant_product   = $vals['variant_product'];
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $variant_product);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $stock_akhir   = $vals['stock_akhir'];
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $stock_akhir);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

        $awal_col++;
        $booking_akhir   = $vals['booking_akhir'];
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $booking_akhir);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

        $awal_col++;
        $balance   = $vals['stock_akhir'] - $vals['booking_akhir'];
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $balance);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

        $awal_col++;
        $min_stok   = $vals['min_stok'];
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $min_stok);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

        $awal_col++;
        $max_stok   = $vals['max_stok'];
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $max_stok);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

        $propose = 0;
        if ($vals['stock_akhir'] - $vals['booking_akhir'] < $vals['min_stok']) {
          $propose = $vals['max_stok'];
        }

        $awal_col++;
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $propose);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);
      }
    }

    $sheet->setTitle('Stock Origa');
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
    header('Content-Disposition: attachment;filename="stock-origa.xls"');
    //unduh file
    $objWriter->save("php://output");
  }

  public function get_list_bom()
  {
    $id_product = $this->input->post('id_product');

    $ArrlistCT = $this->db->group_by('no_bom')->get_where('stock_product', array('deleted_date' => NULL))->result_array();
    $ArrProductCT = [];
    foreach ($ArrlistCT as $key => $value) {
      $ArrProductCT[] = $value['no_bom'];
    }

    $ArrCategory = ['grid standard', 'standard', 'ftackel'];

    $result  = $this->db->select('a.*,b.nama')->where_in('a.category', $ArrCategory)->join('new_inventory_4 b', 'a.id_product=b.code_lv4', 'left')->get_where('bom_header a', array('a.id_product' => $id_product, 'a.deleted_date' => NULL))->result_array();

    if (!empty($result)) {
      $option  = "";
      foreach ($result as $val => $valx) {
        if (!in_array($valx['no_bom'], $ArrProductCT)) {
          $variant_product = (!empty($valx['variant_product'])) ? ' - ' . $valx['variant_product'] : '';
          $option .= "<option value='" . $valx['no_bom'] . "'>" . strtoupper($valx['no_bom'] . ' - ' . $valx['nama'] . $variant_product) . "</option>";
        }
      }
    } else {
      $option  = "<option value='0'>BOM Not Found</option>";
    }

    $ArrJson  = array(
      'option' => $option
    );
    // exit;
    echo json_encode($ArrJson);
  }

  public function hapus()
  {
    $data         = $this->input->post();
    $session      = $this->session->userdata('app_session');

    $id    = $data['id'];

    $ArrHeader = array(
      'deleted_by'      => $this->id_user,
      'deleted_date'    => $this->datetime
    );

    $this->db->trans_start();
    $this->db->where('id', $id);
    $this->db->update('stock_product', $ArrHeader);
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
      history("Delete Stock Product : " . $id);
    }
    echo json_encode($Arr_Data);
  }

  public function qrcode($code_lv4, $no_bom)
  {
    $data_session  = $this->session->userdata;
    $session        = $this->session->userdata('app_session');
    $printby    = $session['id_user'];

    $data_url    = base_url();
    $Split_Beda    = explode('/', $data_url);
    $Jum_Beda    = count($Split_Beda);
    $Nama_Beda    = $Split_Beda[$Jum_Beda - 2];

    $data = array(
      'Nama_Beda' => $Nama_Beda,
      'printby' => $printby,
      'code_lv4' => $code_lv4,
      'no_bom' => $no_bom,
    );

    $this->load->view('print_qrcode', $data);
  }
}
