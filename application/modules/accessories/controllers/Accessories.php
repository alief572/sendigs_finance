<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Accessories extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Master_Indirect.View';
    protected $addPermission  	= 'Master_Indirect.Add';
    protected $managePermission = 'Master_Indirect.Manage';
    protected $deletePermission = 'Master_Indirect.Delete';

   public function __construct()
    {
        parent::__construct();

        // $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array('Accessories/accessories_model'
                                ));
        $this->template->title('Manage Accessories');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');

        $this->id_user  = $this->auth->user_id();
        $this->datetime = date('Y-m-d H:i:s');
    }

    public function index(){
      $this->auth->restrict($this->viewPermission);
      $session = $this->session->userdata('app_session');
      $this->template->page_icon('fa fa-users');

      $data = [
        'category' =>  $this->db->get_where('accessories_category',array('deleted_date'=>NULL))->result_array(),
      ];
      
      history("View index master barang stok");
      $this->template->title('Master Barang Stok');
      $this->template->render('index',$data);
    }

    public function data_side_accessories(){
  		$this->accessories_model->get_json_accessories();
  	}

    public function add(){
      if($this->input->post()){
        $data = $this->input->post();
        // print_r($data); exit;

        $session 		  = $this->session->userdata('app_session');
        $id           = $data['id'];
        $id_category  = $data['id_category'];
        $id_stock     = $data['id_stock'];
        $stock_name   = $data['stock_name'];
        $trade_name   = trim(strtoupper($data['trade_name']));
        $brand        = trim(strtolower($data['brand']));
        $spec         = trim(strtolower($data['spec']));
        $id_unit_gudang = $data['id_unit_gudang'];
        $konversi         = str_replace(',','',$data['konversi']);
        $id_unit          = $data['id_unit'];
        $status           = (!empty($id))?$data['status']:1;

        $max_stok         = str_replace(',','',$data['max_stok']);
        $min_stok         = str_replace(',','',$data['min_stok']);

        $created_by   = 'updated_by';
        $created_date = 'updated_date';
        $tanda        = 'Insert ';

        $ArrHeader		= array(
          'id_stock'		  => $id_stock,
          'id_category'		=> $id_category,
          'stock_name'		=> $stock_name,
          'trade_name'		=> $trade_name,
          'brand'         => $brand,
          'spec'			    => $spec,
          'id_unit_gudang'=> $id_unit_gudang,
          'konversi'	    => $konversi,
          'id_unit'	      => $id_unit,
          'status'	      => $status,
          'max_stok'	      => $max_stok,
          'min_stok'	      => $min_stok,
          $created_by	    => $this->id_user,
          $created_date	  => $this->datetime
        );

        // print_r($ArrHeader);
        // exit;

        $this->db->trans_start();
          if(empty($id)){
            $this->db->insert('accessories', $ArrHeader);
          }
          if(!empty($id)){
            $this->db->where('id', $id);
            $this->db->update('accessories', $ArrHeader);
          }
    		$this->db->trans_complete();

        if($this->db->trans_status() === FALSE){
    			$this->db->trans_rollback();
    			$Arr_Data	= array(
    				'pesan'		=>'Save gagal disimpan ...',
    				'status'	=> 0
    			);
    		}
    		else{
    			$this->db->trans_commit();
    			$Arr_Data	= array(
    				'pesan'		=>'Save berhasil disimpan. Thanks ...',
    				'status'	=> 1
    			);
          history($tanda." data barang stok ".$id);
    		}

    		echo json_encode($Arr_Data);

      }
      else{
      	$session  = $this->session->userdata('app_session');
        $id 	= $this->uri->segment(3);
        $tanda 	= $this->uri->segment(4);
        $header   		= $this->db->get_where('accessories',array('id' => $id))->result();
        
        $satuan 		    = $this->db->get_where('ms_satuan',array('deleted_date'=>NULL,'category'=>'unit'))->result();
        $satuan_packing = $this->db->get_where('ms_satuan',array('deleted_date'=>NULL,'category'=>'packing'))->result();
        $category 		  = $this->db->get_where('accessories_category',array('deleted_date'=>NULL))->result();

        $data = [
          'header' => $header,
          'tanda' => $tanda,
          'satuan' => $satuan,
          'satuan_packing' => $satuan_packing,
          'category' => $category
        ];
          
        $this->template->set('results', $data);
        $this->template->title('Add Barang Stok');
        $this->template->page_icon('fa fa-edit');
        $this->template->render('add',$data);
      }
    }

  public function hapus(){
      $data = $this->input->post();
      $session 		= $this->session->userdata('app_session');
      $id  = $data['id'];

      $ArrHeader		= array(
        'deleted_by'	  => $session['id_user'],
        'deleted_date'	=> date('Y-m-d H:i:s')
      );

      $this->db->trans_start();
          $this->db->where('id', $id);
          $this->db->update('accessories', $ArrHeader);
      $this->db->trans_complete();

      if($this->db->trans_status() === FALSE){
        $this->db->trans_rollback();
        $Arr_Data	= array(
          'pesan'		=>'Save gagal diproses ...',
          'status'	=> 0
        );
      }
      else{
        $this->db->trans_commit();
        $Arr_Data	= array(
          'pesan'		=>'Save berhasil diproses. Thanks ...',
          'status'	=> 1
        );
        history("Delete data barang stok ".$id);
      }

      echo json_encode($Arr_Data);

  }

  public function download_excel(){
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
    $Col_Akhir  = $Cols = getColsChar(12);
    $sheet->setCellValue('A'.$Row, "BARANG STOK MASTER");
    $sheet->getStyle('A'.$Row.':'.$Col_Akhir.$NewRow)->applyFromArray($mainTitle);
    $sheet->mergeCells('A'.$Row.':'.$Col_Akhir.$NewRow);

    $NewRow = $NewRow +2;
    $NextRow= $NewRow;

    $sheet ->getColumnDimension("A")->setAutoSize(true);
    $sheet->setCellValue('A'.$NewRow, '#');
    $sheet->getStyle('A'.$NewRow.':A'.$NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('A'.$NewRow.':A'.$NextRow);

    $sheet ->getColumnDimension("B")->setAutoSize(true);
    $sheet->setCellValue('B'.$NewRow, 'ID PROGRAM');
    $sheet->getStyle('B'.$NewRow.':B'.$NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('B'.$NewRow.':B'.$NextRow);

    $sheet ->getColumnDimension("C")->setAutoSize(true);
    $sheet->setCellValue('C'.$NewRow, 'CATEGORY');
    $sheet->getStyle('C'.$NewRow.':C'.$NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('C'.$NewRow.':C'.$NextRow);

    $sheet ->getColumnDimension("D")->setAutoSize(true);
    $sheet->setCellValue('D'.$NewRow, 'STOK NAME');
    $sheet->getStyle('D'.$NewRow.':D'.$NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('D'.$NewRow.':D'.$NextRow);

    $sheet ->getColumnDimension("E")->setAutoSize(true);
    $sheet->setCellValue('E'.$NewRow, 'ITEM CODE');
    $sheet->getStyle('E'.$NewRow.':E'.$NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('E'.$NewRow.':E'.$NextRow);

    $sheet ->getColumnDimension("F")->setAutoSize(true);
    $sheet->setCellValue('F'.$NewRow, 'TRADE NAME');
    $sheet->getStyle('F'.$NewRow.':F'.$NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('F'.$NewRow.':F'.$NextRow);

    $sheet ->getColumnDimension("G")->setAutoSize(true);
    $sheet->setCellValue('G'.$NewRow, 'BRAND');
    $sheet->getStyle('G'.$NewRow.':G'.$NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('G'.$NewRow.':G'.$NextRow);

    $sheet ->getColumnDimension("H")->setAutoSize(true);
    $sheet->setCellValue('H'.$NewRow, 'SPECIFICATION');
    $sheet->getStyle('H'.$NewRow.':H'.$NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('H'.$NewRow.':H'.$NextRow);

    $sheet ->getColumnDimension("I")->setAutoSize(true);
    $sheet->setCellValue('i'.$NewRow, 'PACKING UNIT');
    $sheet->getStyle('I'.$NewRow.':I'.$NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('I'.$NewRow.':I'.$NextRow);

    $sheet ->getColumnDimension("J")->setAutoSize(true);
    $sheet->setCellValue('J'.$NewRow, 'KONVERSI');
    $sheet->getStyle('J'.$NewRow.':J'.$NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('J'.$NewRow.':J'.$NextRow);

    $sheet ->getColumnDimension("K")->setAutoSize(true);
    $sheet->setCellValue('K'.$NewRow, 'UNIT MEASUREMENT');
    $sheet->getStyle('K'.$NewRow.':K'.$NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('K'.$NewRow.':K'.$NextRow);

    $sheet ->getColumnDimension("L")->setAutoSize(true);
    $sheet->setCellValue('L'.$NewRow, 'MAXIMUM STOK');
    $sheet->getStyle('L'.$NewRow.':L'.$NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('L'.$NewRow.':L'.$NextRow);

    $sheet ->getColumnDimension("M")->setAutoSize(true);
    $sheet->setCellValue('M'.$NewRow, 'MINIMUM STOK');
    $sheet->getStyle('M'.$NewRow.':M'.$NextRow)->applyFromArray($whiteCenterBold);
    $sheet->mergeCells('M'.$NewRow.':M'.$NextRow);

    // $sheet ->getColumnDimension("N")->setAutoSize(true);
    // $sheet->setCellValue('N'.$NewRow, 'Qty');
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

    $dataResult   = $this->db->select('a.*, b.nm_category')->join('accessories_category b','a.id_category=b.id','left')->get_where('accessories a',array('a.deleted_date'=>NULL))->result_array();
    $GET_UNIT = get_list_satuan();
    $GET_LEVEL3 = get_inventory_lv3();
    $GET_LEVEL2 = get_inventory_lv2();
    $GET_LEVEL1 = get_list_inventory_lv1('product');
    if($dataResult){
      $awal_row   = $NextRow;
      $no = 0;
      foreach($dataResult as $key=>$vals){
        $no++;
        $awal_row++;
        $awal_col   = 0;

        $awal_col++;
        $no   = $no;
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols.$awal_row, $no);
        $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $id   = $vals['id'];
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols.$awal_row, $id);
        $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $nm_category   = $vals['nm_category'];
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols.$awal_row, $nm_category);
        $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $stock_name   = $vals['stock_name'];
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols.$awal_row, $stock_name);
        $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $id_stock   = $vals['id_stock'];
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols.$awal_row, $id_stock);
        $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $trade_name   = $vals['trade_name'];
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols.$awal_row, $trade_name);
        $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $brand   = $vals['brand'];
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols.$awal_row, $brand);
        $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $spec   = $vals['spec'];
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols.$awal_row, $spec);
        $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $id_unit_packing   = (!empty($GET_UNIT[$vals['id_unit_gudang']]['code']))?$GET_UNIT[$vals['id_unit_gudang']]['code']:'';
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols.$awal_row, $id_unit_packing);
        $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $konversi   = $vals['konversi'];
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols.$awal_row, $konversi);
        $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $id_unit   = (!empty($GET_UNIT[$vals['id_unit']]['code']))?$GET_UNIT[$vals['id_unit']]['code']:'';
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols.$awal_row, $id_unit);
        $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $max_stok   = $vals['max_stok'];
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols.$awal_row, $max_stok);
        $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

        $awal_col++;
        $min_stok   = $vals['min_stok'];
        $Cols       = getColsChar($awal_col);
        $sheet->setCellValue($Cols.$awal_row, $min_stok);
        $sheet->getStyle($Cols.$awal_row)->applyFromArray($tableBodyLeft);

      }
    }

    $sheet->setTitle('Barang Stok Master');
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
    header('Content-Disposition: attachment;filename="barang-stok-master.xls"');
    //unduh file
    $objWriter->save("php://output");
  }
}

?>
