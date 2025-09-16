<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Stok_gudang_barang extends Admin_Controller
{
  //Permission
  protected $viewPermission   = 'StockGudangBarang.View';
  protected $addPermission    = 'StockGudangBarang.Add';
  protected $managePermission = 'StockGudangBarang.Manage';
  protected $deletePermission = 'StockGudangBarang.Delete';

  public function __construct()
  {
    parent::__construct();
    $this->load->model(array(
      'Stok_gudang_barang/stok_gudang_barang_model'
    ));
    $this->template->title('Warehouse Stok');

    date_default_timezone_set('Asia/Bangkok');
  }

  //==========================================================================================================
  //============================================STOCK=========================================================
  //==========================================================================================================

  public function index()
  {
    $this->auth->restrict($this->viewPermission);
    $session  = $this->session->userdata('app_session');

    $data = [
      'category' =>  $this->db->get_where('accessories_category', array('deleted_date' => NULL))->result_array(),
      'warehouse' =>  $this->db->get_where('warehouse', array('desc' => 'stok'))->result_array(),
    ];

    history("View data gudang stok");
    $this->template->title('Warehouse Stok / Stok');
    $this->template->render('index', $data);
  }

  public function data_side_stock()
  {
    $this->stok_gudang_barang_model->get_json_stock();
  }

  public function modal_history()
  {
    $data     = $this->input->post();
    $gudang   = $data['gudang'];
    $material = $data['material'];

    $sql = "SELECT a.* FROM warehouse_history a WHERE a.id_gudang='" . $gudang . "' AND a.id_material='" . $material . "' ORDER BY a.id ASC ";
    $data = $this->db->query($sql)->result_array();

    $dataArr = array(
      'gudang' => $gudang,
      'material' => $material,
      'data'  => $data
    );
    $this->load->view('modal_history', $dataArr);
  }

  public function download_excel($id_warehouse, $id_category, $date_filter)
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

    $nm_gudang = get_name('warehouse', 'nm_gudang', 'id', $id_warehouse);

    $dateX  = date('Y-m-d H:i:s');
    $Row        = 1;
    $NewRow     = $Row + 1;
    $Col_Akhir  = $Cols = getColsChar(10);
    $sheet->setCellValue('A' . $Row, "GUDANG STOK (" . $nm_gudang . ") " . $date_filter);
    $sheet->getStyle('A' . $Row . ':' . $Col_Akhir . $NewRow)->applyFromArray($mainTitle);
    $sheet->mergeCells('A' . $Row . ':' . $Col_Akhir . $NewRow);

    $NewRow = $NewRow + 2;
    $NextRow = $NewRow;

    $sheet->getColumnDimension("A")->setAutoSize(true);
    $sheet->setCellValue('A' . $NewRow, '#');
    $sheet->getStyle('A' . $NewRow . ':A' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('A' . $NewRow . ':A' . $NextRow);

    $sheet->getColumnDimension("B")->setAutoSize(true);
    $sheet->setCellValue('B' . $NewRow, 'ID PROGRAM');
    $sheet->getStyle('B' . $NewRow . ':B' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('B' . $NewRow . ':B' . $NextRow);

    $sheet->getColumnDimension("C")->setAutoSize(true);
    $sheet->setCellValue('C' . $NewRow, 'CATEGORY');
    $sheet->getStyle('C' . $NewRow . ':C' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('C' . $NewRow . ':C' . $NextRow);

    $sheet->getColumnDimension("D")->setAutoSize(true);
    $sheet->setCellValue('D' . $NewRow, 'CODE');
    $sheet->getStyle('D' . $NewRow . ':D' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('D' . $NewRow . ':D' . $NextRow);

    $sheet->getColumnDimension("E")->setAutoSize(true);
    $sheet->setCellValue('E' . $NewRow, 'NM BARANG');
    $sheet->getStyle('E' . $NewRow . ':E' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('E' . $NewRow . ':E' . $NextRow);

    $sheet->getColumnDimension("F")->setAutoSize(true);
    $sheet->setCellValue('F' . $NewRow, 'WAREHOUSE');
    $sheet->getStyle('F' . $NewRow . ':F' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('F' . $NewRow . ':F' . $NextRow);

    $sheet->getColumnDimension("G")->setAutoSize(true);
    $sheet->setCellValue('G' . $NewRow, 'STOK PACKING');
    $sheet->getStyle('G' . $NewRow . ':G' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('G' . $NewRow . ':G' . $NextRow);

    $sheet->getColumnDimension("H")->setAutoSize(true);
    $sheet->setCellValue('H' . $NewRow, 'UNIT PACKING');
    $sheet->getStyle('H' . $NewRow . ':H' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('H' . $NewRow . ':H' . $NextRow);

    $sheet->getColumnDimension("I")->setAutoSize(true);
    $sheet->setCellValue('i' . $NewRow, 'CONVERTION');
    $sheet->getStyle('I' . $NewRow . ':I' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('I' . $NewRow . ':I' . $NextRow);

    $sheet->getColumnDimension("J")->setAutoSize(true);
    $sheet->setCellValue('J' . $NewRow, 'STOK');
    $sheet->getStyle('J' . $NewRow . ':J' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('J' . $NewRow . ':J' . $NextRow);

    $sheet->getColumnDimension("K")->setAutoSize(true);
    $sheet->setCellValue('K' . $NewRow, 'UNIT');
    $sheet->getStyle('K' . $NewRow . ':K' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('K' . $NewRow . ':K' . $NextRow);

    // $sheet ->getColumnDimension("L")->setAutoSize(true);
    // $sheet->setCellValue('L'.$NewRow, 'MIN');
    // $sheet->getStyle('L'.$NewRow.':L'.$NextRow)->applyFromArray($whiteCenterBold);
    // $sheet->mergeCells('L'.$NewRow.':L'.$NextRow);

    // $sheet ->getColumnDimension("M")->setAutoSize(true);
    // $sheet->setCellValue('M'.$NewRow, 'MOQ');
    // $sheet->getStyle('M'.$NewRow.':M'.$NextRow)->applyFromArray($whiteCenterBold);
    // $sheet->mergeCells('M'.$NewRow.':M'.$NextRow);

    // $sheet ->getColumnDimension("N")->setAutoSize(true);
    // $sheet->setCellValue('N'.$NewRow, 'PROPOSE');
    // $sheet->getStyle('N'.$NewRow.':N'.$NextRow)->applyFromArray($whiteCenterBold);
    // $sheet->mergeCells('N'.$NewRow.':N'.$NextRow);

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

    // $tgl_awal = date('Y-m-d',strtotime($tgl_awal));
    // $tgl_akhir = date('Y-m-d',strtotime($tgl_akhir));

    $where_category = "";
    if ($id_category != '0') {
      $where_category = " AND a.id_category='" . $id_category . "'";
    }

    $where_barang = "";
    // if ($id_warehouse == '17') {
    //   $where_barang = " AND a.id_category IN (5,8,10) ";
    // }
    // if ($id_warehouse == '19') {
    //   $where_barang = " AND a.id_category IN (7) ";
    // }
    // if ($id_warehouse == '20') {
    //   $where_barang = " AND a.id_category IN (2,3,6) ";
    // }

    $SQL = "SELECT
              a.id AS id_accessories,
              a.id_stock AS code,
              a.stock_name AS nama,
              a.id_unit_gudang AS id_unit_packing,
              a.id_unit,
              a.konversi,
			        b.nm_category
            FROM
              accessories a
              LEFT JOIN accessories_category b ON a.id_category=b.id
            WHERE 
              a.deleted_date IS NULL 
              " . $where_category . " 
              " . $where_barang . "
          ";

    $dataResult   = $this->db->query($SQL)->result_array();

    if (empty($date_filter)) {
      $GET_STOK = getStokBarang($id_warehouse);
    } else {
      $GET_STOK = getStokBarangHistory($id_warehouse, $date_filter);
    }
    $GET_UNIT = get_list_satuan();
    $nm_gudang = get_name('warehouse', 'nm_gudang', 'id', $id_warehouse);
    $nm_gudang_title = strtolower(str_replace(' ', '-', get_name('warehouse', 'nm_gudang', 'id', $id_warehouse)));

    if ($dataResult) {
      $awal_row   = $NextRow;
      $no = 0;
      foreach ($dataResult as $key => $row) {
        $no++;
        $awal_row++;
        $awal_col   = 0;

        $awal_col++;
        $no   = $no;
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $no);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $id_accessories   = $row['id_accessories'];
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $id_accessories);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $nm_category   = $row['nm_category'];
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $nm_category);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $code   = $row['code'];
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $code);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $nama   = $row['nama'];
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $nama);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $nm_gudang);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

        $unit_packing = (!empty($GET_UNIT[$row['id_unit_packing']]['code'])) ? $GET_UNIT[$row['id_unit_packing']]['code'] : '';
        $unit_satuan  = (!empty($GET_UNIT[$row['id_unit']]['code'])) ? $GET_UNIT[$row['id_unit']]['code'] : '';
        $id_material  = $row['id_accessories'];
        $stock_pack   = (!empty($GET_STOK[$id_material]['stok_packing'])) ? $GET_STOK[$id_material]['stok_packing'] : '';
        $stock        = (!empty($GET_STOK[$id_material]['stok'])) ? $GET_STOK[$id_material]['stok'] : '';

        $awal_col++;
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $stock_pack);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

        $awal_col++;
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $unit_packing);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $konversi   = $row['konversi'];
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $konversi);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

        $awal_col++;
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $stock);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

        $awal_col++;
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $unit_satuan);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);
      }
    }

    $sheet->setTitle('Stok');
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
    header('Content-Disposition: attachment;filename="gudang-stok-' . $nm_gudang_title . '.xls"');
    //unduh file
    $objWriter->save("php://output");
  }

  public function download_excel_history($id_warehouse, $id_barang)
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

    $nm_gudang = get_name('warehouse', 'nm_gudang', 'id', $id_warehouse);

    $dateX  = date('Y-m-d H:i:s');
    $Row        = 1;
    $NewRow     = $Row + 1;
    $Col_Akhir  = $Cols = getColsChar(11);
    $sheet->setCellValue('A' . $Row, "HISTORY GUDANG STOK (" . $nm_gudang . ")");
    $sheet->getStyle('A' . $Row . ':' . $Col_Akhir . $NewRow)->applyFromArray($mainTitle);
    $sheet->mergeCells('A' . $Row . ':' . $Col_Akhir . $NewRow);

    $NewRow = $NewRow + 2;
    $NextRow = $NewRow;

    $sheet->getColumnDimension("A")->setAutoSize(true);
    $sheet->setCellValue('A' . $NewRow, '#');
    $sheet->getStyle('A' . $NewRow . ':A' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('A' . $NewRow . ':A' . $NextRow);

    $sheet->getColumnDimension("B")->setAutoSize(true);
    $sheet->setCellValue('B' . $NewRow, 'NM BARANG');
    $sheet->getStyle('B' . $NewRow . ':B' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('B' . $NewRow . ':B' . $NextRow);

    $sheet->getColumnDimension("C")->setAutoSize(true);
    $sheet->setCellValue('C' . $NewRow, 'GUDANG DARI');
    $sheet->getStyle('C' . $NewRow . ':C' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('C' . $NewRow . ':C' . $NextRow);

    $sheet->getColumnDimension("D")->setAutoSize(true);
    $sheet->setCellValue('D' . $NewRow, 'GUDANG KE');
    $sheet->getStyle('D' . $NewRow . ':D' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('D' . $NewRow . ':D' . $NextRow);

    $sheet->getColumnDimension("E")->setAutoSize(true);
    $sheet->setCellValue('E' . $NewRow, 'QTY');
    $sheet->getStyle('E' . $NewRow . ':E' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('E' . $NewRow . ':E' . $NextRow);

    $sheet->getColumnDimension("F")->setAutoSize(true);
    $sheet->setCellValue('F' . $NewRow, 'QTY AWAL');
    $sheet->getStyle('F' . $NewRow . ':F' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('F' . $NewRow . ':F' . $NextRow);

    $sheet->getColumnDimension("G")->setAutoSize(true);
    $sheet->setCellValue('G' . $NewRow, 'QTY AKHIR');
    $sheet->getStyle('G' . $NewRow . ':G' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('G' . $NewRow . ':G' . $NextRow);

    $sheet->getColumnDimension("H")->setAutoSize(true);
    $sheet->setCellValue('H' . $NewRow, 'NO TRANS');
    $sheet->getStyle('H' . $NewRow . ':H' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('H' . $NewRow . ':H' . $NextRow);

    $sheet->getColumnDimension("I")->setAutoSize(true);
    $sheet->setCellValue('i' . $NewRow, 'KET');
    $sheet->getStyle('I' . $NewRow . ':I' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('I' . $NewRow . ':I' . $NextRow);

    $sheet->getColumnDimension("J")->setAutoSize(true);
    $sheet->setCellValue('J' . $NewRow, 'BY');
    $sheet->getStyle('J' . $NewRow . ':J' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('J' . $NewRow . ':J' . $NextRow);

    $sheet->getColumnDimension("K")->setAutoSize(true);
    $sheet->setCellValue('K' . $NewRow, 'DATED');
    $sheet->getStyle('K' . $NewRow . ':K' . $NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('K' . $NewRow . ':K' . $NextRow);

    // $sheet ->getColumnDimension("L")->setAutoSize(true);
    // $sheet->setCellValue('L'.$NewRow, 'MIN');
    // $sheet->getStyle('L'.$NewRow.':L'.$NextRow)->applyFromArray($whiteCenterBold);
    // $sheet->mergeCells('L'.$NewRow.':L'.$NextRow);

    // $sheet ->getColumnDimension("M")->setAutoSize(true);
    // $sheet->setCellValue('M'.$NewRow, 'MOQ');
    // $sheet->getStyle('M'.$NewRow.':M'.$NextRow)->applyFromArray($whiteCenterBold);
    // $sheet->mergeCells('M'.$NewRow.':M'.$NextRow);

    // $sheet ->getColumnDimension("N")->setAutoSize(true);
    // $sheet->setCellValue('N'.$NewRow, 'PROPOSE');
    // $sheet->getStyle('N'.$NewRow.':N'.$NextRow)->applyFromArray($whiteCenterBold);
    // $sheet->mergeCells('N'.$NewRow.':N'.$NextRow);

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

    // $tgl_awal = date('Y-m-d',strtotime($tgl_awal));
    // $tgl_akhir = date('Y-m-d',strtotime($tgl_akhir));

    $SQL = "SELECT a.* FROM warehouse_history a WHERE a.id_gudang='" . $id_warehouse . "' AND a.id_material='" . $id_barang . "' ORDER BY a.id ASC ";
    $dataResult   = $this->db->query($SQL)->result_array();
    $nm_barang   = get_name('accessories', 'stock_name', 'id', $id_barang);
    if ($dataResult) {
      $awal_row   = $NextRow;
      $no = 0;
      foreach ($dataResult as $key => $row) {
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
        $sheet->setCellValue($Cols . $awal_row, $nm_barang);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $kd_gudang_dari   = $row['kd_gudang_dari'];
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $kd_gudang_dari);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $kd_gudang_ke   = $row['kd_gudang_ke'];
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $kd_gudang_ke);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $qty = $row['jumlah_mat'];
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $qty);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

        $awal_col++;
        $qty_stock_awal   = $row['qty_stock_awal'];
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $qty_stock_awal);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

        $awal_col++;
        $qty_stock_akhir = $row['qty_stock_akhir'];
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $qty_stock_akhir);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyRight);

        $awal_col++;
        $no_ipp   = $row['no_ipp'];
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $no_ipp);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $ket   = $row['ket'];
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $ket);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $username = get_name('users', 'nm_lengkap', 'id_user', $row['update_by']);
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $username);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $update_date   = $row['update_date'];
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols . $awal_row, $update_date);
        $sheet->getStyle($Cols . $awal_row)->applyFromArray($tableBodyLeft);
      }
    }

    $sheet->setTitle('Stok');
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
    header('Content-Disposition: attachment;filename="history-stok-' . $nm_barang . '.xls"');
    //unduh file
    $objWriter->save("php://output");
  }
}
