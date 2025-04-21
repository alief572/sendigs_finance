<?php if (!defined('BASEPATH')) { exit('No direct script access allowed');}

class Daycode extends Admin_Controller{

    protected $viewPermission = 'Master_Daycode.View';
    protected $addPermission = 'Master_Daycode.Add';
    protected $managePermission = 'Master_Daycode.Manage';
    protected $deletePermission = 'Master_Daycode.Delete';

    public function __construct(){
        parent::__construct();
        $this->load->model(array(
    			   'Daycode/Daycode_model'
    			));

        date_default_timezone_set('Asia/Bangkok');
        $this->template->page_icon('fa fa-table');
    }

    public function index(){
      history("View index daycode");
      $this->template->title('List Daycode');
      $this->template->render('index');
    }

    public function data_side(){
    	$this->Daycode_model->getDataJSON();
    }

    public function crate_manual(){
      $date = date('y-m-d', strtotime('2020-08-01'));

      // echo $date;
      $ArrInsert = array();
      for ($b=0; $b <= 28; $b++) {
          $loop_date = date("y-m-d", strtotime("+".$b." day", strtotime($date)));
          $loop_date2 = date("Y-m-d", strtotime("+".$b." day", strtotime($date)));
          for($a=16;$a<=20;$a++){
            $urut	= sprintf('%02s',$a);
            $code = str_replace('-','/', $loop_date).'/'.$urut;

            $ArrInsert[$b.$a]['code']          = $code;
            $ArrInsert[$b.$a]['tanggal']       = $loop_date2;
            $ArrInsert[$b.$a]['urut']          = $a;
            $ArrInsert[$b.$a]['created_by']    = 'system';
            $ArrInsert[$b.$a]['created_date']  = date('Y-m-d H:i:s');

          }
      }
      echo "<pre>";
      print_r($ArrInsert);
      // exit;
      $this->db->trans_start();
      $this->db->insert_batch('daycode',$ArrInsert);
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
        history("Insert daycode manual ".$date);
  		}

      print_r($Arr_Data);
    }

    public function crate_manual_rusak(){
      $date = date('y-m-d', strtotime('2020-04-01'));

      // echo $date;
      $ArrInsert = array();
      for ($b=0; $b <= 31; $b++) {
          $loop_date = date("y-m-d", strtotime("+".$b." day", strtotime($date)));
          $loop_date2 = date("Y-m-d", strtotime("+".$b." day", strtotime($date)));
          for($a=1;$a<=20;$a++){
            $urut	= sprintf('%02s',$a);
            $code = str_replace('-','/', $loop_date).'/'.$urut;

            $ArrInsert[$b.$a]['code']          = $code;
            $ArrInsert[$b.$a]['tanggal']       = $loop_date2;
            $ArrInsert[$b.$a]['urut']          = $a;
            $ArrInsert[$b.$a]['created_by']    = 'system';
            $ArrInsert[$b.$a]['created_date']  = date('Y-m-d H:i:s');

          }
      }
      // echo "<pre>";
      // print_r($ArrInsert);
      // exit;
      $this->db->trans_start();
      $this->db->insert_batch('daycode',$ArrInsert);
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
        history("Insert daycode manual ".$date);
  		}

      print_r($Arr_Data);
    }

    public function excel_report(){
  		//membuat objek PHPExcel
  		set_time_limit(0);
  		ini_set('memory_limit','1024M');

  		$tanggal	= $this->uri->segment(3);
  		$bulan		= $this->uri->segment(4);
  		$tahun		= $this->uri->segment(5);
  		$tgl_awal	= $this->uri->segment(6);
  		$tgl_akhir	= $this->uri->segment(7);

    		$this->load->library("PHPExcel");
    		// $this->load->library("PHPExcel/Writer/Excel2007");
    		$objPHPExcel	= new PHPExcel();

  		$style_header = array(
  			'borders' => array(
  				'allborders' => array(
  					  'style' => PHPExcel_Style_Border::BORDER_THIN,
  					  'color' => array('rgb'=>'000000')
  				  )
  			),
  			'fill' => array(
  				'type' => PHPExcel_Style_Fill::FILL_SOLID,
  				'color' => array('rgb'=>'e0e0e0'),
  			),
  			'font' => array(
  				'bold' => true,
  			),
  			'alignment' => array(
  				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
  				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
  			)
  		);

  		$style_header2 = array(
  			'fill' => array(
  				'type' => PHPExcel_Style_Fill::FILL_SOLID,
  				'color' => array('rgb'=>'e0e0e0'),
  			),
  			'font' => array(
  				'bold' => true,
  			),
  			'alignment' => array(
  				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
  				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
  			)
  		);

  		$styleArray = array(
  			  'alignment' => array(
  				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
  			  ),
  			  'borders' => array(
  				'allborders' => array(
  					  'style' => PHPExcel_Style_Border::BORDER_THIN,
  					  'color' => array('rgb'=>'000000')
  				  )
  			)
  		  );
  		$styleArray3 = array(
  			  'alignment' => array(
  				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
  			  ),
  			  'borders' => array(
  				'allborders' => array(
  					  'style' => PHPExcel_Style_Border::BORDER_THIN,
  					  'color' => array('rgb'=>'000000')
  				  )
  			)
  		  );
  		 $styleArray4 = array(
  			  'alignment' => array(
  				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
  			  ),
  			  'borders' => array(
  				'allborders' => array(
  					  'style' => PHPExcel_Style_Border::BORDER_THIN,
  					  'color' => array('rgb'=>'000000')
  				  )
  			)
  		  );
  	    $styleArray1 = array(
  			  'borders' => array(
  				  'allborders' => array(
  					  'style' => PHPExcel_Style_Border::BORDER_THIN
  				  )
  			  ),
  			  'alignment' => array(
  				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
  				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
  			  )
  		  );
  		$styleArray2 = array(
  			  'borders' => array(
  				  'allborders' => array(
  					  'style' => PHPExcel_Style_Border::BORDER_THIN
  				  )
  			  ),
  			  'alignment' => array(
  				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
  				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
  			  )
  		  );

    		$Arr_Bulan	= array(1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
    		$sheet 		= $objPHPExcel->getActiveSheet();

  		$where_tgl = "";
          if($tanggal > 0){
              $where_tgl = "AND DAY(a.tanggal) = '".$tanggal."' ";
          }

  		$where_bln = "";
          if($bulan > 0){
              $where_bln = "AND MONTH(a.tanggal) = '".$bulan."' ";
          }

          $where_thn = "";
          if($tahun > 0){
              $where_thn = "AND YEAR(a.tanggal) = '".$tahun."' ";
          }

  		$where_range = "";
          if($tgl_awal > 0){
              $where_range = "AND DATE(a.tanggal) BETWEEN '".$tgl_awal."' AND '".$tgl_akhir."' ";
          }

  		$sql = "
  			SELECT
  				a.*
  			FROM
  				daycode a
  		    WHERE a.tanggal <> '' ".$where_tgl." ".$where_bln." ".$where_thn." ".$where_range."
  			ORDER BY
  				a.id ASC
  		";
  		// echo $sql;exit;
  		$product    = $this->db->query($sql)->result_array();

    		$Row		= 1;
    		$NewRow		= $Row+1;
    		$Col_Akhir	= $Cols	= getColsChar(3);
    		$sheet->setCellValue('A'.$Row, 'DAYCODE');
    		$sheet->getStyle('A'.$Row.':'.$Col_Akhir.$NewRow)->applyFromArray($style_header2);
    		$sheet->mergeCells('A'.$Row.':'.$Col_Akhir.$NewRow);

    		$NewRow	= $NewRow +2;
    		$NextRow= $NewRow +1;

    		$sheet->setCellValue('A'.$NewRow, 'No');
    		$sheet->getStyle('A'.$NewRow.':A'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('A'.$NewRow.':A'.$NextRow);
    		$sheet->getColumnDimension('A')->setAutoSize(true);

    		$sheet->setCellValue('B'.$NewRow, 'Date');
    		$sheet->getStyle('B'.$NewRow.':B'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('B'.$NewRow.':B'.$NextRow);
    		$sheet->getColumnDimension('B')->setAutoSize(true);

  		  $sheet->setCellValue('C'.$NewRow, 'Daycode');
    		$sheet->getStyle('C'.$NewRow.':C'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('C'.$NewRow.':C'.$NextRow);
    		$sheet->getColumnDimension('C')->setAutoSize(true);

  		if($product){
  			$awal_row	= $NextRow;
  			$no=0;
  			foreach($product as $key => $row_Cek){
  				$no++;
  				$awal_row++;
  				$awal_col	= 0;

  				$awal_col++;
  				$nomor	= $no;
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $nomor);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

  				$awal_col++;
  				$id_produksi	= $row_Cek['tanggal'];
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $id_produksi);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

  				$awal_col++;
  				$status_date	= $row_Cek['code'];
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $status_date);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

  			}
  		}


  		$sheet->setTitle('List Daycode');
  		//mulai menyimpan excel format xlsx, kalau ingin xls ganti Excel2007 menjadi Excel5
  		$objWriter		= PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
  		ob_end_clean();
  		//sesuaikan headernya
  		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
  		header("Cache-Control: no-store, no-cache, must-revalidate");
  		header("Cache-Control: post-check=0, pre-check=0", false);
  		header("Pragma: no-cache");
  		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
  		//ubah nama file saat diunduh
  		header('Content-Disposition: attachment;filename="Daycode '.date('YmdHis').'.xls"');
  		//unduh file
  		$objWriter->save("php://output");
  	}

}
?>
