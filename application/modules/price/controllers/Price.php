<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *
 */
class Price extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Price_Reference.View';
    protected $addPermission  	= 'Price_Reference.Add';
    protected $managePermission = 'Price_Reference.Manage';
    protected $deletePermission = 'Price_Reference.Delete';

   public function __construct()
    {
        parent::__construct();

        // $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array('Price/price_model'
                                ));
        $this->template->title('Manage Data Supplier');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');
    }

    public function product(){
      $this->auth->restrict($this->viewPermission);
      $session = $this->session->userdata('app_session');
      $this->template->page_icon('fa fa-users');
      $deleted = '0';
      $data = $this->price_model->get_data('ms_satuan','deleted','N');
      history("View index price reference product");
      $this->template->set('results', $data);
      $this->template->title('Price Reference Product');
      $this->template->render('product');
    }

    public function data_side_product(){
      $this->price_model->get_json_product();
    }

    public function add_product(){
      if($this->input->post()){
        $data = $this->input->post();
        // print_r($data); exit;

        $session 	= $this->session->userdata('app_session');
        $id       = $data['id'];
        $code     = $data['code'];
        $rate     = str_replace(',','',$data['rate']);
        $rate_fitting     = str_replace(',','',$data['rate_fitting']);

        $created_by   = 'updated_by';
        $created_date = 'updated_date';
        $tanda        = 'Insert ';
        if(empty($code_materialx)){

          $created_by   = 'created_by';
          $created_date = 'created_date';
          $tanda        = 'Update ';
        }

        $ArrHeader		= array(
          'code'		=> $code,
          'rate'		=> $rate,
          'rate_fitting'		=> $rate_fitting,
          'category'		=> 'product',
          'kurs'		=> 'USD',
          $created_by	    => $session['id_user'],
          $created_date	  => date('Y-m-d H:i:s')
        );

        // print_r($ArrHeader);
        // exit;

        $this->db->trans_start();
          if(empty($id)){
            $this->db->insert('price_ref', $ArrHeader);
          }
          if(!empty($id)){
            $this->db->where('id', $id);
            $this->db->update('price_ref', $ArrHeader);
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
          history($tanda." data price ref product ".$id);
        }

        echo json_encode($Arr_Data);

      }
      else{
        $session  = $this->session->userdata('app_session');
        $id 	  = $this->uri->segment(3);
        $header   = $this->db->get_where('price_ref',array('id' => $id))->result();

        // print_r($header);
        // exit;
        $data = [
          'header' => $header,
        ];
        $this->template->set('results', $data);
        $this->template->title('Add Price Reference');
        $this->template->page_icon('fa fa-edit');
        $this->template->render('add_product',$data);
      }
    }

    public function hapus_product(){
        $data = $this->input->post();
        $session 		= $this->session->userdata('app_session');
        $code_material  = $data['id'];

        $ArrHeader		= array(
          'deleted'			  => "Y",
          'deleted_by'	  => $session['id_user'],
          'deleted_date'	=> date('Y-m-d H:i:s')
        );

        $this->db->trans_start();
            $this->db->where('id', $code_material);
            $this->db->update('price_ref', $ArrHeader);
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
          history("Delete data product ".$code_material);
        }

        echo json_encode($Arr_Data);
    }

    public function material(){
      $this->auth->restrict($this->viewPermission);
      $session = $this->session->userdata('app_session');
      $this->template->page_icon('fa fa-users');
      $deleted = '0';
      $data = $this->price_model->get_data('ms_satuan','deleted','N');
      history("View index price reference material");
      $this->template->set('results', $data);
      $this->template->title('Price Reference Material');
      $this->template->render('material');
    }

    public function data_side_material(){
      $this->price_model->get_json_material();
    }

    public function add_material(){
      if($this->input->post()){
        $data = $this->input->post();
        // print_r($data); exit;

        $session 	= $this->session->userdata('app_session');
        $id       = $data['id'];
        $code     = $data['code'];
		$kurs     = $data['kurs'];
        $rate     = str_replace(',','',$data['rate']);
        $type_material   = $data['type_material'];
        $remarks        = strtolower($data['remarks']);

        $created_by   = 'updated_by';
        $created_date = 'updated_date';
        $tanda        = 'Insert ';
        if(empty($code_materialx)){

          $created_by   = 'created_by';
          $created_date = 'created_date';
          $tanda        = 'Update ';
        }

        $ArrHeader		= array(
          'code'		=> $code,
          'rate'		=> $rate,
          'category'		=> 'material',
          'kurs'		=> $kurs,
          'type_material'		=> $type_material,
          'remarks'		       => $remarks,
          $created_by	    => $session['id_user'],
          $created_date	  => date('Y-m-d H:i:s')
        );

        // print_r($ArrHeader);
        // exit;

        $this->db->trans_start();
          if(empty($id)){
            $this->db->insert('price_ref', $ArrHeader);
          }
          if(!empty($id)){
            $this->db->where('id', $id);
            $this->db->update('price_ref', $ArrHeader);
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
          history($tanda." data price ref material ".$id);
        }

        echo json_encode($Arr_Data);

      }
      else{
        $session  = $this->session->userdata('app_session');
        $id 	  = $this->uri->segment(3);
        $header   = $this->db->get_where('price_ref',array('id' => $id))->result();

        // print_r($header);
        // exit;
        $data = [
          'header' => $header,
        ];
        $this->template->set('results', $data);
        $this->template->title('Add Unit');
        $this->template->page_icon('fa fa-edit');
        $this->template->render('add_material',$data);
      }
    }

    public function hapus_material(){
        $data = $this->input->post();
        $session 		= $this->session->userdata('app_session');
        $code_material  = $data['id'];

        $ArrHeader		= array(
          'deleted'			  => "Y",
          'deleted_by'	  => $session['id_user'],
          'deleted_date'	=> date('Y-m-d H:i:s')
        );

        $this->db->trans_start();
            $this->db->where('id', $code_material);
            $this->db->update('price_ref', $ArrHeader);
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
          history("Delete data material ".$code_material);
        }

        echo json_encode($Arr_Data);
    }

    public function excel_report_material_price(){
  		//membuat objek PHPExcel
  		set_time_limit(0);
  		ini_set('memory_limit','1024M');

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

  		$sql = "
  			SELECT
  				a.*
  			FROM
  				price_ref a
  		    WHERE a.deleted = 'N' AND a.category='material'
  			ORDER BY
  				a.code ASC
  		";
  		// echo $sql;exit;
  		$product    = $this->db->query($sql)->result_array();

    		$Row		= 1;
    		$NewRow		= $Row+1;
    		$Col_Akhir	= $Cols	= getColsChar(6);
    		$sheet->setCellValue('A'.$Row, 'MATERIAL PRICE');
    		$sheet->getStyle('A'.$Row.':'.$Col_Akhir.$NewRow)->applyFromArray($style_header2);
    		$sheet->mergeCells('A'.$Row.':'.$Col_Akhir.$NewRow);

    		$NewRow	= $NewRow +2;
    		$NextRow= $NewRow +1;

    		$sheet->setCellValue('A'.$NewRow, 'No');
    		$sheet->getStyle('A'.$NewRow.':A'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('A'.$NewRow.':A'.$NextRow);
    		$sheet->getColumnDimension('A')->setAutoSize(true);

    		$sheet->setCellValue('B'.$NewRow, 'Material Name');
    		$sheet->getStyle('B'.$NewRow.':B'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('B'.$NewRow.':B'.$NextRow);
    		$sheet->getColumnDimension('B')->setAutoSize(true);

			$sheet->setCellValue('C'.$NewRow, 'Type Material');
    		$sheet->getStyle('C'.$NewRow.':C'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('C'.$NewRow.':C'.$NextRow);
    		$sheet->getColumnDimension('C')->setAutoSize(true);

			$sheet->setCellValue('D'.$NewRow, 'Kurs');
    		$sheet->getStyle('D'.$NewRow.':D'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('D'.$NewRow.':D'.$NextRow);
    		$sheet->getColumnDimension('D')->setAutoSize(true);
			
			$sheet->setCellValue('E'.$NewRow, 'Price');
    		$sheet->getStyle('E'.$NewRow.':E'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('E'.$NewRow.':E'.$NextRow);
    		$sheet->getColumnDimension('E')->setAutoSize(true);
			
			$sheet->setCellValue('F'.$NewRow, 'Remarks');
    		$sheet->getStyle('F'.$NewRow.':F'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('F'.$NewRow.':F'.$NextRow);
    		$sheet->getColumnDimension('F')->setAutoSize(true);

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
  				$status_date	= strtoupper(get_name('ms_material','nm_material','code_material', $row_Cek['code']));
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $status_date);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

          $awal_col++;
          $status_date	= strtoupper($row_Cek['type_material']);
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $status_date);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);
		  
		  $awal_col++;
          $kurs	= strtoupper($row_Cek['kurs']);
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $kurs);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

          $awal_col++;
          $status_date	= number_format($row_Cek['rate'],2);
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $status_date);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray4);
		  
		  $awal_col++;
          $remarks	= strtoupper($row_Cek['remarks']);
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $remarks);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

  			}
  		}


  		$sheet->setTitle('Material Price');
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
  		header('Content-Disposition: attachment;filename="origa-price-material.xls"');
  		//unduh file
  		$objWriter->save("php://output");
  	}

    public function excel_report_product_price(){
  		//membuat objek PHPExcel
  		set_time_limit(0);
  		ini_set('memory_limit','1024M');

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

  		$sql = "
  			SELECT
  				a.*
  			FROM
  				price_ref a
  		    WHERE a.deleted = 'N' AND a.category = 'product'
  			ORDER BY
  				a.code ASC
  		";
  		// echo $sql;exit;
  		$product    = $this->db->query($sql)->result_array();

    		$Row		= 1;
    		$NewRow		= $Row+1;
    		$Col_Akhir	= $Cols	= getColsChar(5);
    		$sheet->setCellValue('A'.$Row, 'PRODUCT PRICE');
    		$sheet->getStyle('A'.$Row.':'.$Col_Akhir.$NewRow)->applyFromArray($style_header2);
    		$sheet->mergeCells('A'.$Row.':'.$Col_Akhir.$NewRow);

    		$NewRow	= $NewRow +2;
    		$NextRow= $NewRow +1;

    		$sheet->setCellValue('A'.$NewRow, 'No');
    		$sheet->getStyle('A'.$NewRow.':A'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('A'.$NewRow.':A'.$NextRow);
    		$sheet->getColumnDimension('A')->setAutoSize(true);

    		$sheet->setCellValue('B'.$NewRow, 'Project');
    		$sheet->getStyle('B'.$NewRow.':B'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('B'.$NewRow.':B'.$NextRow);
    		$sheet->getColumnDimension('B')->setAutoSize(true);

  		  $sheet->setCellValue('C'.$NewRow, 'Product');
    		$sheet->getStyle('C'.$NewRow.':C'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('C'.$NewRow.':C'.$NextRow);
    		$sheet->getColumnDimension('C')->setAutoSize(true);

        $sheet->setCellValue('D'.$NewRow, 'Price');
    		$sheet->getStyle('D'.$NewRow.':D'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('D'.$NewRow.':D'.$NextRow);
    		$sheet->getColumnDimension('D')->setAutoSize(true);

        $sheet->setCellValue('E'.$NewRow, 'Fitting Price');
    		$sheet->getStyle('E'.$NewRow.':E'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('E'.$NewRow.':E'.$NextRow);
    		$sheet->getColumnDimension('E')->setAutoSize(true);

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
  				$id_produksi	= strtoupper(get_name('ms_inventory_category1','nama','id_category1',get_name('ms_inventory_category2','id_category1','id_category2', $row_Cek['code'])));
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $id_produksi);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

  				$awal_col++;
  				$status_date	= strtoupper(get_name('ms_inventory_category2','nama','id_category2', $row_Cek['code']));
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $status_date);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

          $awal_col++;
          $status_date	= number_format($row_Cek['rate'],2);
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $status_date);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray4);

          $awal_col++;
          $status_date	= number_format($row_Cek['rate_fitting'],2);
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $status_date);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray4);

  			}
  		}


  		$sheet->setTitle('Product Price');
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
  		header('Content-Disposition: attachment;filename="origa-price-product.xls"');
  		//unduh file
  		$objWriter->save("php://output");
  	}

}

?>
