<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Gudang_wip_mutasi extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Gudang_WIP_Mutasi.View';
    protected $addPermission  	= 'Gudang_WIP_Mutasi.Add';
    protected $managePermission = 'Gudang_WIP_Mutasi.Manage';
    protected $deletePermission = 'Gudang_WIP_Mutasi.Delete';

   public function __construct()
    {
        parent::__construct();

        $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array('Gudang_wip_mutasi/gudang_wip_mutasi_model'
                                ));
        date_default_timezone_set('Asia/Bangkok');

        $this->id_user  = $this->auth->user_id();
        $this->datetime = date('Y-m-d H:i:s');
    }

    public function index(){
      $this->auth->restrict($this->viewPermission);
      $session  = $this->session->userdata('app_session');

      $listSO = $this->db->get_where('so_internal',array('deleted_date'=>NULL))->result_array();
      $data = [
        'listSO' => $listSO
      ];

      history("View data gudang wip mutasi");
      $this->template->title('Gudang WIP / Mutasi');
      $this->template->render('index',$data);
    }

    public function data_side_gudang_wip_inout(){
  		$this->gudang_wip_mutasi_model->data_side_gudang_wip_inout();
  	}

    public function show_history_in_out_wip_detail(){
      $data       = $this->input->post();
      // print_r($data);
      // echo $data['tanda'];
      // exit;
      $tanda  	    = $data['tanda'];
      $sales_order  = $data['sales_order'];
      $code_lv4  	  = $data['code_lv4'];
      $tgl_awal     = date('Y-m-d',strtotime($data['tgl_awal']));
      $tgl_akhir    = date('Y-m-d',strtotime($data['tgl_akhir']));
  
      if($tanda == 'out'){
        $transaksi	= $this->db
                ->select('COUNT(b.id) AS qty, 
                          a.code_lv4,
                          b.no_spk,
                          a.nama_product,
                          a.so_number,
                          b.qc_by AS created_by,
                          b.qc_date AS created_date')
                ->from('so_internal_product b')
                ->join('so_internal_spk z','b.id_key_spk=z.id','left')
                ->join('so_internal a','a.id=z.id_so AND z.status_id = "1"','left')
                ->join('new_inventory_4 y','a.code_lv4=y.code_lv4','left')
                ->where('a.deleted_date',NULL)
                // ->where('y.code_lv1','P123000009')
                ->where('DATE(b.qc_date) >=',$tgl_awal)
                ->where('DATE(b.qc_date) <=',$tgl_akhir)
                ->where('a.code_lv4',$code_lv4)
                ->group_by('b.id_key_spk')
                ->get()
                ->result_array();
      }
      else{
        $transaksi	= $this->db
                ->select('COUNT(b.id) AS qty, 
                          a.code_lv4,
                          b.no_spk,
                          a.nama_product,
                          a.so_number,
                          b.close_by AS created_by,
                          b.close_date AS created_date')
                ->from('so_internal_product b')
                ->join('so_internal_spk z','b.id_key_spk=z.id','left')
                ->join('so_internal a','a.id=z.id_so AND z.status_id = "1"','left')
                ->join('new_inventory_4 y','a.code_lv4=y.code_lv4','left')
                ->where('a.deleted_date',NULL)
                // ->where('y.code_lv1','P123000009')
                ->where('DATE(b.close_date) >=',$tgl_awal)
                ->where('DATE(b.close_date) <=',$tgl_akhir)
                ->where('a.code_lv4',$code_lv4)
                ->group_by('b.id_key_spk')
                ->get()
                ->result_array();
      }
      // echo $this->db->last_query();
      // print_r($transaksi);
      // exit;
      $ArrTrans_IN = [];
      foreach ($transaksi as $key => $value) {
        $ArrTrans_IN[$value['code_lv4']][] = $value;
      }
      $dataArr = [
        'get_in_trans' 	=> $ArrTrans_IN,
        'code_lv4' 		  => $code_lv4,
        'GET_USER'      => get_list_user()
      ];
  
      $data_html = $this->load->view('history_in_out_wip_detail', $dataArr, TRUE);
      // print_r($ArrTrans_IN);
      // echo $data_html;
      // exit;
      $Arr_Kembali	= array(
        'status'	=> 1,
        'data_html'	=> $data_html
      );
      echo json_encode($Arr_Kembali);
    }

    public function download_excel($tgl_awal,$tgl_akhir){
      set_time_limit(0);
      ini_set('memory_limit','1024M');
      $this->load->library("PHPExcel");
  
      $objPHPExcel    = new PHPExcel();
  
      $whiteCenterBold    = whiteCenterBold();
      $whiteRightBold    	= whiteRightBold();
      $whiteCenter    	  = whiteCenter();
      $mainTitle    		  = mainTitle();
      $tableHeader    	  = tableHeader();
      $tableBodyCenter    = tableBodyCenter();
      $tableBodyLeft    	= tableBodyLeft();
      $tableBodyRight    	= tableBodyRight();
  
      $Arr_Bulan  = array(1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
      $sheet      = $objPHPExcel->getActiveSheet();
  
      $dateX	= date('Y-m-d H:i:s');
      $Row        = 1;
      $NewRow     = $Row+1;
      $Col_Akhir  = $Cols = getColsChar(6);
      $sheet->setCellValue('A'.$Row, "GUDANG WIP MUTASI");
      $sheet->getStyle('A'.$Row.':'.$Col_Akhir.$NewRow)->applyFromArray($mainTitle);
      $sheet->mergeCells('A'.$Row.':'.$Col_Akhir.$NewRow);
  
      $NewRow = $NewRow +2;
      $NextRow= $NewRow;
  
      $sheet ->getColumnDimension("A")->setAutoSize(true);
      $sheet->setCellValue('A'.$NewRow, '#');
      $sheet->getStyle('A'.$NewRow.':A'.$NextRow)->applyFromArray($whiteCenterBold);
      $sheet->mergeCells('A'.$NewRow.':A'.$NextRow);
  
      $sheet ->getColumnDimension("B")->setAutoSize(true);
      $sheet->setCellValue('B'.$NewRow, 'CATEGORY');
      $sheet->getStyle('B'.$NewRow.':B'.$NextRow)->applyFromArray($whiteCenterBold);
      $sheet->mergeCells('B'.$NewRow.':B'.$NextRow);
  
      $sheet ->getColumnDimension("C")->setAutoSize(true);
      $sheet->setCellValue('C'.$NewRow, 'PRODUCT');
      $sheet->getStyle('C'.$NewRow.':C'.$NextRow)->applyFromArray($whiteCenterBold);
      $sheet->mergeCells('C'.$NewRow.':C'.$NextRow);
  
      $sheet ->getColumnDimension("D")->setAutoSize(true);
      $sheet->setCellValue('D'.$NewRow, 'VARIANT');
      $sheet->getStyle('D'.$NewRow.':D'.$NextRow)->applyFromArray($whiteCenterBold);
      $sheet->mergeCells('D'.$NewRow.':D'.$NextRow);
  
      $sheet ->getColumnDimension("E")->setAutoSize(true);
      $sheet->setCellValue('E'.$NewRow, 'IN');
      $sheet->getStyle('E'.$NewRow.':E'.$NextRow)->applyFromArray($whiteCenterBold);
      $sheet->mergeCells('E'.$NewRow.':E'.$NextRow);
  
      $sheet ->getColumnDimension("F")->setAutoSize(true);
      $sheet->setCellValue('F'.$NewRow, 'OUT');
      $sheet->getStyle('F'.$NewRow.':F'.$NextRow)->applyFromArray($whiteCenterBold);
      $sheet->mergeCells('F'.$NewRow.':F'.$NextRow);
  
      // $sheet ->getColumnDimension("G")->setAutoSize(true);
      // $sheet->setCellValue('G'.$NewRow, 'PRODUCT MASTER');
      // $sheet->getStyle('G'.$NewRow.':G'.$NextRow)->applyFromArray($whiteCenterBold);
      // $sheet->mergeCells('G'.$NewRow.':G'.$NextRow);
  
      // $sheet ->getColumnDimension("H")->setAutoSize(true);
      // $sheet->setCellValue('H'.$NewRow, 'VARIANT');
      // $sheet->getStyle('H'.$NewRow.':H'.$NextRow)->applyFromArray($whiteCenterBold);
      // $sheet->mergeCells('H'.$NewRow.':H'.$NextRow);
  
      // $sheet ->getColumnDimension("I")->setAutoSize(true);
      // $sheet->setCellValue('i'.$NewRow, 'ACTUAL STOK');
      // $sheet->getStyle('I'.$NewRow.':I'.$NextRow)->applyFromArray($whiteCenterBold);
      // $sheet->mergeCells('I'.$NewRow.':I'.$NextRow);
  
      // $sheet ->getColumnDimension("J")->setAutoSize(true);
      // $sheet->setCellValue('J'.$NewRow, 'BOOKING STOCK');
      // $sheet->getStyle('J'.$NewRow.':J'.$NextRow)->applyFromArray($whiteCenterBold);
      // $sheet->mergeCells('J'.$NewRow.':J'.$NextRow);
  
      // $sheet ->getColumnDimension("K")->setAutoSize(true);
      // $sheet->setCellValue('K'.$NewRow, 'FREE STOCK');
      // $sheet->getStyle('K'.$NewRow.':K'.$NextRow)->applyFromArray($whiteCenterBold);
      // $sheet->mergeCells('K'.$NewRow.':K'.$NextRow);
  
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

      $sales_date_filter = " AND DATE(b.close_date) BETWEEN '".$tgl_awal."' AND '".$tgl_akhir."'";
      $table = 'so_internal_product';
      $WHERE2 = "";
      // $WHERE2 = "AND b.qc_date IS NULL";

      $key1 = "b.id_key_spk";
      $field1 = "COUNT(b.id) AS qty";
      $group1 = " GROUP BY a.code_lv4";

      $SQL = "SELECT
                (@row:=@row+1) AS nomor,
                a.code_lv4,
                b.no_spk,
                a.nama_product,
                a.so_number,
                ".$field1.",
                a.propose AS qty_propose,
                x.variant_product,
                b.id_key_spk
              FROM
                ".$table." b
                LEFT JOIN so_internal_spk z ON $key1 = z.id
                LEFT JOIN so_internal a ON a.id=z.id_so AND z.status_id = '1'
                LEFT JOIN new_inventory_4 y ON a.code_lv4=y.code_lv4
                LEFT JOIN bom_header x ON a.no_bom=x.no_bom,
                (SELECT @row:=0) r
              WHERE a.deleted_date IS NULL AND y.deleted_date IS NULL ".$WHERE2." ".$sales_date_filter."
              ".$group1;
  
      $dataResult   = $this->db->query($SQL)->result_array();

      $GET_USER = get_list_user();
      $GET_LEVEL4 = get_inventory_lv4();
      $GET_LEVEL2 = get_inventory_lv2();
      $tgl_awal   = (!empty($tgl_awal))?date('Y-m-d',strtotime($tgl_awal)):date('Y-m-d');
      $tgl_akhir  = (!empty($tgl_akhir))?date('Y-m-d',strtotime($tgl_akhir)):date('Y-m-d');
    
      if($dataResult){
        $awal_row   = $NextRow;
        $no = 0;
        foreach($dataResult as $key=>$row){
          $no++;
          $awal_row++;
          $awal_col   = 0;
  
          $awal_col++;
          $no   = $no;
          $Cols       = getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $no);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

          $code_lv2     = (!empty($GET_LEVEL4[$row['code_lv4']]['code_lv2']))?$GET_LEVEL4[$row['code_lv4']]['code_lv2']:'';
          $nm_category  = (!empty($GET_LEVEL2[$code_lv2]['nama']))?$GET_LEVEL2[$code_lv2]['nama']:'';
  
          $awal_col++;
          $Cols       = getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $nm_category);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);
  
          $awal_col++;
          $nm_product   = $row['nm_product'];
          $Cols       = getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $nm_product);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

          $awal_col++;
          $variant   = $row['variant'];
          $Cols       = getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $variant);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

          $countIN  = $this->db->join('so_internal_spk b','a.id_key_spk=b.id','left')->join('so_internal c','b.id_so=c.id AND b.status_id="1"','left')->get_where('so_internal_product a',array('c.code_lv4'=>$row['code_lv4'],'DATE(a.close_date) >='=>$tgl_awal,'DATE(a.close_date) <='=>$tgl_akhir,'c.deleted_date'=>NULL))->result_array();
          $countOUT = $this->db->join('so_internal_spk b','a.id_key_spk=b.id','left')->join('so_internal c','b.id_so=c.id AND b.status_id="1"','left')->get_where('so_internal_product a',array('c.code_lv4'=>$row['code_lv4'],'DATE(a.qc_date) >='=>$tgl_awal,'DATE(a.qc_date) <='=>$tgl_akhir,'c.deleted_date'=>NULL))->result_array();
          
          $qtyIN  = COUNT($countIN);
          $qtyOUT = COUNT($countOUT);
  
          $awal_col++;
          $Cols       = getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $qtyIN);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyRight);
  
          $awal_col++;
          $Cols       = getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $qtyOUT);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyRight);
  
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
      header('Content-Disposition: attachment;filename="gudang-mutasi-wip'.$tgl_awal.'-to-'.$tgl_akhir.'.xls"');
      //unduh file
      $objWriter->save("php://output");
    }
}

?>
