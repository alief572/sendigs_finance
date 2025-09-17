<?php if (!defined('BASEPATH')) { exit('No direct script access allowed');}

class Produksi extends Admin_Controller{

    protected $viewPermission = 'Produksi.View';
    protected $addPermission = 'Produksi.Add';
    protected $managePermission = 'Produksi.Manage';
    protected $deletePermission = 'Produksi.Delete';

    public function __construct(){
        parent::__construct();

        // $this->load->library(array('Mpdf'));
        $this->load->model(array(
			'Produksi/produksi_model','Cycletime/Cycletime_model'
			));

        date_default_timezone_set('Asia/Bangkok');
        $this->template->page_icon('fa fa-table');
    }

    public function index(){
      $this->auth->restrict($this->viewPermission);
      $session = $this->session->userdata('app_session');
      $this->template->page_icon('fa fa-users');

      $sql = "SELECT * FROM cek_daycode_double_fast WHERE jumlah_double > 1";
      $rest = $this->db->query($sql)->result_array();

      $data = array(
        'double_daycode' => $rest
      );

      history("View index report produksi (input produksi)");
      $this->template->title('Report Produksi');
      $this->template->render('index', $data);
    }

    public function data_side_input_produksi(){
    	$this->produksi_model->get_data_json_input_produksi();
    }

    public function report(){
      $this->auth->restrict($this->viewPermission);
      $session = $this->session->userdata('app_session');
      $this->template->page_icon('fa fa-users');
      history("View index report produksi (report wip)");
      $this->template->title('Report Produksi');
      $this->template->render('report');
    }

    public function data_side_daily_report_produksi(){
    	$this->produksi_model->get_data_json_daily_report_produksi();
    }

    public function add(){

    	$session = $this->session->userdata('app_session');

			$customer    = $this->Cycletime_model->get_data('master_customer');
			$supplier    = $this->Cycletime_model->get_data('master_supplier');
			$material    = $this->Cycletime_model->get_data('ms_inventory_category2');
			$mesin      = $this->Cycletime_model->get_data_group('asset','category','4','nm_asset');
      $mould      = $this->Cycletime_model->get_data_group('asset','category','5','nm_asset');
			$costcenter  = $this->Cycletime_model->get_data('ms_costcenter','deleted','0');
			$data = [
			'customer' => $customer,
			'supplier' => $supplier,
			'material' => $material,
			'mesin' => $mesin,
      'mould' => $mould,
			'costcenter' => $costcenter,
			];
			$this->template->set('results', $data);
      $this->template->title('Add Produksi');
      $this->template->page_icon('fa fa-edit');
      $this->template->render('add');
    }

    public function add2(){

    	$session = $this->session->userdata('app_session');

			$customer    = $this->Cycletime_model->get_data('master_customer');
			$supplier    = $this->Cycletime_model->get_data('master_supplier');
			$material    = $this->Cycletime_model->get_data('ms_inventory_category2');
			$mesin       = $this->Cycletime_model->get_data_group('asset','category','4','nm_asset');
      $mould       = $this->Cycletime_model->get_data_group('asset','category','5','nm_asset');

			$data = [
			'customer' => $customer,
			'supplier' => $supplier,
			'material' => $material,
			'mesin' => $mesin,
      'mould' => $mould
			];
			$this->template->set('results', $data);
      $this->template->title('Add Produksi');
      $this->template->page_icon('fa fa-edit');
      $this->template->render('add2');
    }

    public function add2_new(){

    	$session = $this->session->userdata('app_session');

			// $customer    = $this->Cycletime_model->get_data('master_customer');
			// $supplier    = $this->Cycletime_model->get_data('master_supplier');
			// $material    = $this->Cycletime_model->get_data('ms_inventory_category2');
			// $mesin       = $this->Cycletime_model->get_data_group('asset','category','4','nm_asset');
      // $mould       = $this->Cycletime_model->get_data_group('asset','category','5','nm_asset');

      $product_check = $this->db->get_where('ms_inventory_category2', array('ck_produksi'=>'Y'))->result_array();

			$data = [
			// 'customer' => $customer,
			// 'supplier' => $supplier,
			// 'material' => $material,
			// 'mesin' => $mesin,
      // 'mould' => $mould,
      'product_check' => $product_check
			];
			$this->template->set($data);
      $this->template->title('Add Produksi New');
      $this->template->page_icon('fa fa-edit');
      $this->template->render('add2_new');
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
              $where_tgl = "AND DAY(a.tanggal_produksi) = '".$tanggal."' ";
          }

  		$where_bln = "";
          if($bulan > 0){
              $where_bln = "AND MONTH(a.tanggal_produksi) = '".$bulan."' ";
          }

          $where_thn = "";
          if($tahun > 0){
              $where_thn = "AND YEAR(a.tanggal_produksi) = '".$tahun."' ";
          }

  		$where_range = "";
          if($tgl_awal > 0){
              $where_range = "AND DATE(a.tanggal_produksi) BETWEEN '".$tgl_awal."' AND '".$tgl_akhir."' ";
          }

          $sql = "
                  SELECT
                    a.tanggal_produksi,
                    c.nama_costcenter,
                    e.nama AS nm_product,
                    f.nama AS nm_project,
                    a.`code`,
                    a.remarks
                  FROM
                    report_produksi_daily_detail a
                    LEFT JOIN report_produksi_daily_header b ON a.id_produksi_h = b.id_produksi_h
                    LEFT JOIN ms_costcenter c ON b.id_costcenter = c.id_costcenter
                    LEFT JOIN ms_inventory_category2 e ON a.id_product = e.id_category2
                    LEFT JOIN ms_inventory_category1 f ON e.id_category1 = f.id_category1
                  WHERE 1=1 ".$where_tgl." ".$where_bln." ".$where_thn." ".$where_range." AND a.sts_daycode = 'N' AND a.ket = 'good'
              		ORDER BY a.tanggal_produksi DESC";
  		// echo $sql;exit;
  		$product    = $this->db->query($sql)->result_array();

    		$Row		= 1;
    		$NewRow		= $Row+1;
    		$Col_Akhir	= $Cols	= getColsChar(7);
    		$sheet->setCellValue('A'.$Row, 'LAPORAN INPUT PRODUKSI');
    		$sheet->getStyle('A'.$Row.':'.$Col_Akhir.$NewRow)->applyFromArray($style_header2);
    		$sheet->mergeCells('A'.$Row.':'.$Col_Akhir.$NewRow);

    		$NewRow	= $NewRow +2;
    		$NextRow= $NewRow +1;

    		$sheet->setCellValue('A'.$NewRow, 'No');
    		$sheet->getStyle('A'.$NewRow.':A'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('A'.$NewRow.':A'.$NextRow);
    		$sheet->getColumnDimension('A')->setAutoSize(true);

    		$sheet->setCellValue('B'.$NewRow, 'Production Date');
    		$sheet->getStyle('B'.$NewRow.':B'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('B'.$NewRow.':B'.$NextRow);
    		$sheet->getColumnDimension('B')->setAutoSize(true);

  		  $sheet->setCellValue('C'.$NewRow, 'Costcenter');
    		$sheet->getStyle('C'.$NewRow.':C'.$NextRow)->applyFromArray($style_header);
    		$sheet->mergeCells('C'.$NewRow.':C'.$NextRow);
    		$sheet->getColumnDimension('C')->setAutoSize(true);

        $sheet->setCellValue('D'.$NewRow, 'Project');
        $sheet->getStyle('D'.$NewRow.':D'.$NextRow)->applyFromArray($style_header);
        $sheet->mergeCells('D'.$NewRow.':D'.$NextRow);
        $sheet->getColumnDimension('D')->setAutoSize(true);

        $sheet->setCellValue('E'.$NewRow, 'Product');
        $sheet->getStyle('E'.$NewRow.':E'.$NextRow)->applyFromArray($style_header);
        $sheet->mergeCells('E'.$NewRow.':E'.$NextRow);
        $sheet->getColumnDimension('E')->setAutoSize(true);

        $sheet->setCellValue('F'.$NewRow, 'Daycode');
        $sheet->getStyle('F'.$NewRow.':F'.$NextRow)->applyFromArray($style_header);
        $sheet->mergeCells('F'.$NewRow.':F'.$NextRow);
        $sheet->getColumnDimension('F')->setAutoSize(true);

        $sheet->setCellValue('G'.$NewRow, 'Remarks');
        $sheet->getStyle('G'.$NewRow.':G'.$NextRow)->applyFromArray($style_header);
        $sheet->mergeCells('G'.$NewRow.':G'.$NextRow);
        $sheet->getColumnDimension('G')->setAutoSize(true);

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
  				$id_produksi	= $row_Cek['tanggal_produksi'];
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $id_produksi);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

  				$awal_col++;
  				$status_date	= $row_Cek['nama_costcenter'];
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $status_date);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

          $awal_col++;
          $status_date	= $row_Cek['nm_project'];
          $Cols			= getColsChar($awal_col);
          $sheet->setCellValue($Cols.$awal_row, $status_date);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

          $awal_col++;
  				$status_date	= $row_Cek['nm_product'];
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $status_date);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

          $awal_col++;
  				$status_date	= $row_Cek['code'];
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $status_date);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

          $remak = (!empty($row_Cek['remarks']))?$row_Cek['remarks']:'-';
          $awal_col++;
  				$status_date	= $remak;
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $status_date);
  				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

  			}
  		}


  		$sheet->setTitle('Laporan Input Produksi');
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
  		header('Content-Disposition: attachment;filename="Laporan Input Produksi '.date('YmdHis').'.xls"');
  		//unduh file
  		$objWriter->save("php://output");
  	}

    public function edit(){

    	$session = $this->session->userdata('app_session');
      $id_time = $this->uri->segment(3);
      $header	= $this->db->query("SELECT * FROM report_produksi_daily_header WHERE id_produksi_h='".$id_time."' LIMIT 1 ")->result_array();
      $detail	= $this->db->query("SELECT * FROM report_produksi_daily_detail WHERE id_produksi_h='".$id_time."' ")->result_array();
      $costcenter	= $this->db->query("SELECT a.costcenter AS id, b.nama_costcenter AS nama FROM cycletime_detail_header a LEFT JOIN ms_costcenter b ON a.costcenter=b.id_costcenter GROUP BY a.costcenter ORDER BY b.nama_costcenter ASC")->result_array();
      $daycode	= $this->db->query("SELECT * FROM daycode ORDER BY code ASC ")->result_array();

			$data = [
			'header' => $header,
			'detail' => $detail,
      'costcenter' => $costcenter,
      'daycode' => $daycode
			];
			$this->template->set('results', $data);
      $this->template->page_icon('fa fa-edit');
      $this->template->title('Edit Produksi');
      $this->template->render('edit', $data);
    }

    public function change_daycode(){
      if($this->input->post()){
        $data			= $this->input->post();
    		$session 		= $this->session->userdata('app_session');
        $id 	          = $data['id'];
        $daycode_before = $data['daycode_before'];
        $daycode_new 	  = $data['daycode_new'];

        $ArrUbah = array(
          'code' => $daycode_new
        );

        $this->db->trans_start();
          $this->db->where('id',$id);
    			$this->db->update('report_produksi_daily_detail', $ArrUbah);
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
          history("Change daycode produksi ".$id.", ".$daycode_before." to ".$daycode_new);
    		}

    		echo json_encode($Arr_Data);
      }
      else{
    		$id 	= $this->uri->segment(3);
        $header	= $this->db->query("SELECT a.*, d.id_category1 FROM report_produksi_daily_detail a LEFT JOIN ms_inventory_category2 d ON a.id_product = d.id_category2 WHERE a.id_produksi_h='".$id."'")->result_array();
        // print_r($header);exit;
    		$data = [
    			'header' => $header,
          'id' => $id
    			];
        $this->template->set('results', $data);
    		$this->template->render('change_daycode', $data);
      }
  	}

  	public function view(){
  		$this->auth->restrict($this->viewPermission);
  		$id 	= $this->input->post('id');
      $header	= $this->db->query("SELECT a.*, d.id_category1 FROM report_produksi_daily_detail a LEFT JOIN ms_inventory_category2 d ON a.id_product = d.id_category2 WHERE a.id_produksi_h='".$id."'")->result_array();
      // print_r($header);exit;
  		$data = [
  			'header' => $header
  			];
      $this->template->set('results', $data);
  		$this->template->render('view', $data);
  	}

  public function get_add(){
		$id 	= $this->uri->segment(3);
		$no 	= 0;

		$costcenter	= $this->db->query("SELECT a.costcenter AS id, b.nama_costcenter AS nama FROM cycletime_detail_header a LEFT JOIN ms_costcenter b ON a.costcenter=b.id_costcenter WHERE b.deleted='0' GROUP BY a.costcenter ORDER BY b.nama_costcenter ASC")->result_array();
    $machine	= $this->db->query("SELECT * FROM asset WHERE category='4' GROUP BY nm_asset ORDER BY nm_asset ASC ")->result_array();
    $mould	= $this->db->query("SELECT * FROM asset WHERE category='5' GROUP BY nm_asset ORDER BY nm_asset ASC ")->result_array();
		// echo $qListResin; exit;
		$d_Header = "";
		// $d_Header .= "<tr>";
			$d_Header .= "<tr class='header_".$id."'>";
				$d_Header .= "<td align='center'>".$id."</td>";
				$d_Header .= "<td align='left'>";
        $d_Header .= "<select name='Detail[".$id."][id_costcenter]' id='cost_".$id."'data-id='".$id."' class='chosen_select form-control input-sm inline-blockd costcenter'>";
        $d_Header .= "<option value='0'>Select Costcenter</option>";
        foreach($costcenter AS $val => $valx){
          $d_Header .= "<option value='".$valx['id']."'>".strtoupper($valx['nama'])."</option>";
        }
        $d_Header .= 		"</select>";
				$d_Header .= "</td>";
        $d_Header .= "<td align='left'>";

				$d_Header .= "</td>";
				// $d_Header .= "<td align='left'></td>";
        // $d_Header .= "<td align='left'></td>";
        $d_Header .= "<td align='left'></td>";
				$d_Header .= "<td align='center'>";
				$d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
				$d_Header .= "</td>";
			$d_Header .= "</tr>";

  		//add nya
  		$d_Header .= "<tr id='add_".$id."_".$no."' class='header_".$id."'>";
  			$d_Header .= "<td align='center'></td>";
  			$d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' data-id='".$id."' class='btn btn-sm btn-primary addSubPart' title='Add Product'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add</button></td>";
  			// $d_Header .= "<td align='center'></td>";
  			$d_Header .= "<td align='center'></td>";
  			// $d_Header .= "<td align='center'></td>";
        $d_Header .= "<td align='center'></td>";
        $d_Header .= "<td align='center'></td>";
  		$d_Header .= "</tr>";

  		//add part
  		$d_Header .= "<tr id='add_".$id."'>";
  			$d_Header .= "<td align='center'></td>";
  			$d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-warning addPart' title='Add Costcenter'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Costcenter</button></td>";
  			$d_Header .= "<td align='center'></td>";
  			// $d_Header .= "<td align='center'></td>";
  			// $d_Header .= "<td align='center'></td>";
        $d_Header .= "<td align='center'></td>";
        	$d_Header .= "<td align='center'></td>";
  		$d_Header .= "</tr>";

  		 echo json_encode(array(
  				'header'			=> $d_Header,
  		 ));
	}

  public function get_add_sub(){
		$id 	= $this->uri->segment(3);
    $no 	= $this->uri->segment(4);
    $cost 	= $this->uri->segment(5);

    $product	= $this->db->query("SELECT
                                  	a.costcenter AS id,
                                  	b.id_product AS id_product,
                                  	d.nama AS nama,
                                  	c.nama AS nama2
                                  FROM
                                  	cycletime_detail_header a
                                  	LEFT JOIN cycletime_header b ON a.id_time = b.id_time
                                  	LEFT JOIN ms_inventory_category2 d ON b.id_product = d.id_category2
                                  	LEFT JOIN ms_inventory_category1 c ON d.id_category1 = c.id_category1
                                  WHERE
                                    a.costcenter = '".$cost."'
                                  GROUP BY
                                  	a.costcenter,
                                  	b.id_product
                                  ORDER BY
                                  	d.id_category2")->result_array();
    // $daycode	= $this->db->query("SELECT * FROM daycode ORDER BY code ASC ")->result_array();
		// echo $qListResin; exit;
		$d_Header = "";
		// $d_Header .= "<tr>";
			$d_Header .= "<tr class='header_".$id."'>";
				$d_Header .= "<td align='center'></td>";
				$d_Header .= "<td align='left' style='vertical-align:middle; padding-left: 30px;'>";
        $d_Header .= "<select name='Detail[".$id."][detail][".$no."][id_product]' id='product_".$id."_".$no."' class='chosen_select form-control input-sm inline-blockd id_product cost_".$id."'>";
        $d_Header .= "<option value='0'>Select Product Name</option>";
        foreach($product AS $val => $valx){
          $d_Header .= "<option value='".$valx['id_product']."'>".strtoupper($valx['nama2'])." - ".strtoupper($valx['nama'])."</option>";
        }
        $d_Header .= 		"</select>";
				$d_Header .= "</td>";
        // $d_Header .= "<td align='left'>";
        // $d_Header .= "<select name='Detail[".$id."][detail][".$no."][id_process]' id='process_".$id."_".$no."' class='chosen_select form-control input-sm inline-blockd id_process pro_".$id."'>";
        // $d_Header .= "<option value='0'>List Empty</option>";
        // $d_Header .= "</select>";
				// $d_Header .= "</td>";
        $d_Header .= "<td align='left'>";
        $d_Header .= "<select name='Detail[".$id."][detail][".$no."][code][]' id='code_".$id."_".$no."' class='chosen_select form-control input-sm inline-blockd code code_".$id."' multiple>";
        // $d_Header .= "<option value='0'>List Empty</option>";
        // $d_Header .= "<option value='0'>Select Daycode</option>";
        // foreach($daycode AS $val => $valx){
        //   $d_Header .= "<option value='".$valx['code']."'>".strtoupper($valx['code'])."</option>";
        // }
        $d_Header .= 		"</select>";
				$d_Header .= "</td>";
        // $d_Header .= "<td align='left'>";
        // $d_Header .= "<select name='Detail[".$id."][detail][".$no."][ket]' class='chosen_select form-control input-sm inline-blockd ket'>";
        // $d_Header .= "<option value='0'>Select Status</option>";
        // $d_Header .= "<option value='good'>GOOD</option>";
        // $d_Header .= "<option value='bad'>NOT GOOD</option>";
        // $d_Header .= 	"</select>";
				// $d_Header .= "</td>";
        $d_Header .= "<td align='left'>";
        $d_Header .= 	"<input type='text' name='Detail[".$id."][detail][".$no."][remarks]' class='form-control input-md' placeholder='Remarks'>";
				$d_Header .= "</td>";
				$d_Header .= "<td align='center'>";
				$d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delSubPart' title='Delete Part'><i class='fa fa-close'></i></button>";
				$d_Header .= "</td>";
			$d_Header .= "</tr>";

		//add nya
		$d_Header .= "<tr id='add_".$id."_".$no."' class='header_".$id."'>";
			$d_Header .= "<td align='center'></td>";
			$d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' data-id='".$id."' class='btn btn-sm btn-primary addSubPart' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add</button></td>";
			$d_Header .= "<td align='center'></td>";
			// $d_Header .= "<td align='center'></td>";
			// $d_Header .= "<td align='center'></td>";
      $d_Header .= "<td align='center'></td>";
      	$d_Header .= "<td align='center'></td>";
		$d_Header .= "</tr>";

		 echo json_encode(array(
				'header'			=> $d_Header,
		 ));
	}

  public function get_add_sub_new(){
		$id 	= $this->uri->segment(3);
    $no 	= $this->uri->segment(4);
    $cost 	= $this->uri->segment(5);

    $product	= $this->db->query("SELECT
                                  	a.costcenter AS id,
                                  	b.id_product AS id_product,
                                  	d.nama AS nama,
                                  	c.nama AS nama2
                                  FROM
                                  	cycletime_detail_header a
                                  	LEFT JOIN cycletime_header b ON a.id_time = b.id_time
                                  	LEFT JOIN ms_inventory_category2 d ON b.id_product = d.id_category2
                                  	LEFT JOIN ms_inventory_category1 c ON d.id_category1 = c.id_category1
                                  WHERE
                                    a.costcenter = '".$cost."'
                                  GROUP BY
                                  	a.costcenter,
                                  	b.id_product
                                  ORDER BY
                                  	d.id_category2")->result_array();
		$d_Header = "";
			$d_Header .= "<tr class='header_".$id."'>";
				$d_Header .= "<td align='center'></td>";
				$d_Header .= "<td align='left' style='vertical-align:middle; padding-left: 30px;'>";
        $d_Header .= "<select name='Detail[".$id."][detail][".$no."][id_product]' id='product_".$id."_".$no."' class='chosen_select form-control input-sm inline-blockd id_product cost_".$id."'>";
        $d_Header .= "<option value='0'>Select Product Name</option>";
        foreach($product AS $val => $valx){
          $d_Header .= "<option value='".$valx['id_product']."'>".strtoupper($valx['nama2'])." - ".strtoupper($valx['nama'])."</option>";
        }
        $d_Header .= 		"</select>";
				$d_Header .= "</td>";
        $d_Header .= "<td align='center'></td>";
        $d_Header .= "<td align='left'>";
        $d_Header .= "<select name='Detail[".$id."][detail][".$no."][code][]' id='code_".$id."_".$no."' class='chosen_select form-control input-sm inline-blockd code code_".$id."' multiple>";
        $d_Header .= 	"</select>";
				$d_Header .= "</td>";
        $d_Header .= "<td align='left'>";
        $d_Header .= 	"<input type='text' name='Detail[".$id."][detail][".$no."][remarks]' class='form-control input-md' placeholder='Remarks'>";
				$d_Header .= "</td>";
				$d_Header .= "<td align='center'>";
				$d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delSubPart' title='Delete Part'><i class='fa fa-close'></i></button>";
				$d_Header .= "</td>";
			$d_Header .= "</tr>";

		//add nya
		$d_Header .= "<tr id='add_".$id."_".$no."' class='header_".$id."'>";
			$d_Header .= "<td align='center'></td>";
			$d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' data-id='".$id."' class='btn btn-sm btn-primary addSubPart' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add</button></td>";
			$d_Header .= "<td align='center'></td>";
      $d_Header .= "<td align='center'></td>";
			$d_Header .= "<td align='center'></td>";
      $d_Header .= "<td align='center'></td>";
		$d_Header .= "</tr>";

		 echo json_encode(array(
				'header'			=> $d_Header,
		 ));
	}


  public function save_process(){

  	$Arr_Kembali	= array();
		$data			= $this->input->post();
    // print_r($data);
    // exit;
		$session 		= $this->session->userdata('app_session');
    $Detail 	= $data['Detail'];
    $tanggal_pro 	= date('Y-m-d', strtotime($data['tanggal_produksi']));
    $Ym						= date('ym');
    //pengurutan kode
    $srcMtr			= "SELECT MAX(id_produksi) as maxP FROM report_produksi_daily_code WHERE id_produksi LIKE 'PR-".$Ym."%' ";
    $numrowMtr		= $this->db->query($srcMtr)->num_rows();
    $resultMtr		= $this->db->query($srcMtr)->result_array();
    $angkaUrut2		= $resultMtr[0]['maxP'];
    $urutan2		= (int)substr($angkaUrut2, 7, 7);
    $urutan2++;
    $urut2			= sprintf('%07s',$urutan2);
    $id_material	= "PR-".$Ym.$urut2;

    $ArrHeader		= array(
      'id_produksi'			=> $id_material,
      'tanggal_produksi' 	=> $tanggal_pro,
      'created_by'	=> $session['id_user'],
      'created_date'	=> date('Y-m-d H:i:s')
    );



    $ArrDetail	= array();
    $ArrDetail2	= array();
    foreach($Detail AS $val => $valx){
      if(!empty($valx['detail'])){
        $urut				= sprintf('%02s',$val);
        $ArrDetail[$val]['id_produksi'] 			= $id_material;
        $ArrDetail[$val]['id_produksi_h']     = $id_material."-".$urut;
        $ArrDetail[$val]['tanggal_produksi'] 	= $tanggal_pro;
        $ArrDetail[$val]['id_costcenter'] 		= $valx['id_costcenter'];
        $ArrDetail[$val]['created_by'] 			  = $session['username'];
        $ArrDetail[$val]['created_date'] 			= date('Y-m-d H:i:s');
          foreach($valx['detail'] AS $val2 => $valx2){
              if(!empty($valx2['code'])){
                foreach($valx2['code'] AS $cod => $codx){
                  $ArrDetail2[$val2.$val.$cod]['id_produksi'] 			= $id_material;
                  $ArrDetail2[$val2.$val.$cod]['id_produksi_h']     = $id_material."-".$urut;
                  $ArrDetail2[$val2.$val.$cod]['tanggal_produksi']  = $tanggal_pro;
                  $ArrDetail2[$val2.$val.$cod]['id_product'] 	      = $valx2['id_product'];
                  $ArrDetail2[$val2.$val.$cod]['code'] 			        = $codx;
                  $ArrDetail2[$val2.$val.$cod]['remarks'] 			    = $valx2['remarks'];
                }
              }
          }
      }
    }

    // print_r($ArrHeader);
		// print_r($ArrDetail);
		// print_r($ArrDetail2);
		// exit;

		$this->db->trans_start();
			$this->db->insert('report_produksi_daily_code', $ArrHeader);
      if(!empty($ArrDetail)){
  			$this->db->insert_batch('report_produksi_daily_header', $ArrDetail);
      }
      if(!empty($ArrDetail2)){
        $this->db->insert_batch('report_produksi_daily_detail', $ArrDetail2);
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
      history("Insert report produksi ".$id_material);
		}

		echo json_encode($Arr_Data);
	}

  public function save_processx(){

  	$Arr_Kembali	= array();
		$data			= $this->input->post();
    // print_r($data);
    // exit;
		$session 		= $this->session->userdata('app_session');
    $Detail 	= $data['Detail'];
    $tanggal_pro 	= date('Y-m-d', strtotime($data['tanggal_produksi']));
    $Ym						= date('ym');
    //pengurutan kode
    $srcMtr			= "SELECT MAX(id_produksi) as maxP FROM report_produksi_daily_code WHERE id_produksi LIKE 'PR-".$Ym."%' ";
    $numrowMtr		= $this->db->query($srcMtr)->num_rows();
    $resultMtr		= $this->db->query($srcMtr)->result_array();
    $angkaUrut2		= $resultMtr[0]['maxP'];
    $urutan2		= (int)substr($angkaUrut2, 7, 3);
    $urutan2++;
    $urut2			= sprintf('%03s',$urutan2);
    $id_material	= "PR-".$Ym.$urut2;

    $ArrHeader		= array(
      'id_produksi'			=> $id_material,
      'tanggal_produksi' 	=> $tanggal_pro,
      'created_by'	=> $session['id_user'],
      'created_date'	=> date('Y-m-d H:i:s')
    );



    $ArrDetail	= array();
    $ArrDetail2	= array();
    foreach($Detail AS $val => $valx){
      $urut				= sprintf('%02s',$val);
      $ArrDetail[$val]['id_produksi'] 			= $id_material;
      $ArrDetail[$val]['id_produksi_h']     = $id_material."-".$urut;
      $ArrDetail[$val]['tanggal_produksi'] 	= $tanggal_pro;
      $ArrDetail[$val]['id_costcenter'] 		= $valx['id_costcenter'];
      $ArrDetail[$val]['created_by'] 			  = $session['id_user'];
      $ArrDetail[$val]['created_date'] 			= date('Y-m-d H:i:s');
      if(!empty($valx['detail'])){
        foreach($valx['detail'] AS $val2 => $valx2){
          $ArrDetail2[$val2.$val]['id_produksi'] 			= $id_material;
          $ArrDetail2[$val2.$val]['id_produksi_h']    = $id_material."-".$urut;
          $ArrDetail2[$val2.$val]['tanggal_produksi'] 	= $tanggal_pro;
          $ArrDetail2[$val2.$val]['id_product'] 	    = $valx2['id_product'];
          $ArrDetail2[$val2.$val]['code'] 			      = $valx2['code'];
          $ArrDetail2[$val2.$val]['remarks'] 			      = $valx2['remarks'];
        }
      }
    }

    // print_r($ArrHeader);
		// print_r($ArrDetail);
		// print_r($ArrDetail2);
		// exit;

		$this->db->trans_start();
			$this->db->insert('report_produksi_daily_code', $ArrHeader);
      if(!empty($ArrDetail)){
  			$this->db->insert_batch('report_produksi_daily_header', $ArrDetail);
      }
      if(!empty($ArrDetail2)){
        $this->db->insert_batch('report_produksi_daily_detail', $ArrDetail2);
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
      history("Insert report produksi ".$id_material);
		}

		echo json_encode($Arr_Data);
	}

	public function list_center(){
		$id = $this->uri->segment(3);
		$query	 	= "SELECT * FROM ms_costcenter WHERE id_dept='".$id."' ORDER BY nama_costcenter ASC";
		$Q_result	= $this->db->query($query)->result();
		$option 	= "<option value='0'>Select an Option</option>";
		foreach($Q_result as $row)	{
		$option .= "<option value='".$row->nama_costcenter."'>".strtoupper($row->nama_costcenter)."</option>";
		}
		echo json_encode(array(
			'option' => $option
		));
	}

  public function edit_process(){

  	$Arr_Kembali	= array();
		$data			    = $this->input->post();
    // print_r($data);
    // exit;
		$session 		   = $this->session->userdata('app_session');
    $Detail 	     = $data['Detail'];


    $ArrDetail	= array();
    foreach($Detail AS $val => $valx){
      $ArrDetail[$val]['id'] 		= $valx['id'];
      $ArrDetail[$val]['code'] 	= $valx['code'];
      $ArrDetail[$val]['ket'] 	= $valx['ket'];
    }

    // print_r($ArrHeader);
		// print_r($ArrDetail);
		// print_r($ArrDetail2);
		// exit;

		$this->db->trans_start();
			$this->db->update_batch('report_produksi_daily_detail', $ArrDetail,'id');
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
      history("Update report produksi ".$valx['id']);
		}

		echo json_encode($Arr_Data);
	}

  public function delete_cycletime(){

  	$Arr_Kembali	= array();
		$data			    = $this->input->post();
    // print_r($data);
    // exit;
		$session 		   = $this->session->userdata('app_session');
    $id_material	 = $data['id'];

    $ArrHeader		  = array(
      'deleted'			=> "Y",
      'deleted_by'	=> $session['id_user'],
      'deleted_date'	=> date('Y-m-d H:i:s')
    );

		$this->db->trans_start();
      $this->db->where('id_time', $id_material);
			$this->db->update('cycletime_header', $ArrHeader);
		$this->db->trans_complete();

		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=>'Delete gagal disimpan ...',
				'status'	=> 0
			);
		}
		else{
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=>'Delete berhasil disimpan. Thanks ...',
				'status'	=> 1
			);
      history("Delete report produksi ".$id_material);
		}

		echo json_encode($Arr_Data);
	}

  public function get_list_product(){
		$cost = $this->uri->segment(3);
    $product	= $this->db->query("SELECT
                                  	a.costcenter AS id,
                                  	b.id_product AS id_product,
                                  	d.nama AS nama,
                                  	c.nama AS nama2
                                  FROM
                                  	cycletime_detail_header a
                                  	LEFT JOIN cycletime_header b ON a.id_time = b.id_time
                                  	LEFT JOIN ms_inventory_category2 d ON b.id_product = d.id_category2
                                  	LEFT JOIN ms_inventory_category1 c ON d.id_category1 = c.id_category1
                                  WHERE
                                    a.costcenter = '".$cost."'
                                  GROUP BY
                                  	a.costcenter,
                                  	b.id_product
                                  ORDER BY
                                  	b.id_product")->result();
		$option 	= "<option value='0'>Select Product Name</option>";
		foreach($product as $row)	{
		$option .= "<option value='".$row->id_product."'>".strtoupper($row->nama)." (".strtoupper($row->nama2).")</option>";
		}
		echo json_encode(array(
			'option' => $option
		));
	}

  public function get_list_process(){
		$id_product = $this->uri->segment(3);
    $costcenter = $this->uri->segment(4);

    $daycode = "";
    if($costcenter == 'CC2000012'){
      $filter = $this->db->query("SELECT code FROM filter_daycode WHERE id_costcenter='CC2000012' AND id_product='".$id_product."'")->result_array();
      $dtListArray = array();
      foreach($filter AS $val => $valx){
        $dtListArray[$val] = $valx['code'];
      }
      $dtImplode	= "('".implode("','", $dtListArray)."')";

      $filter_daycode = $this->db->query("SELECT code FROM daycode WHERE code NOT IN ".$dtImplode." ORDER BY code ASC ")->result();
      foreach($filter_daycode as $row)	{
  		    $daycode .= "<option value='".$row->code."'>".strtoupper($row->code)."</option>";
  		}

    }

    if($costcenter != 'CC2000012'){
      $filter = $this->db->query("SELECT code FROM filter_daycode WHERE id_costcenter='".$costcenter."' AND id_product='".$id_product."'")->result_array();
      $dtListArray = array();
      foreach($filter AS $val => $valx){
        $dtListArray[$val] = $valx['code'];
      }
      $dtImplode	= "('".implode("','", $dtListArray)."')";

      $filter_daycode = $this->db->query("SELECT code FROM filter_daycode WHERE code NOT IN ".$dtImplode." AND id_costcenter='CC2000012' AND id_product='".$id_product."' AND sts_daycode='N' ORDER BY code ASC ")->result();
      foreach($filter_daycode as $row)	{
  		    $daycode .= "<option value='".$row->code."'>".strtoupper($row->code)."</option>";
  		}
    }



    // $product	= $this->db->query("SELECT
    //                               	a.id_product,
    //                               	d.costcenter,
    //                               	b.id_process AS id_process,
    //                               	c.nm_process AS nama
    //                               FROM
    //                               	cycletime_header a
    //                               	LEFT JOIN cycletime_detail_detail b ON a.id_time = b.id_time
    //                               	LEFT JOIN cycletime_detail_header d ON b.id_costcenter = d.id_costcenter
    //                               	LEFT JOIN ms_process c ON c.id = b.id_process
    //                               WHERE
    //                                 d.costcenter = '".$costcenter."'
    //                                 AND a.id_product = '".$id_product."'
    //                               GROUP BY
    //                               	b.id_process
    //                               ORDER BY
    //                               	a.id_product,
    //                               	c.nm_process")->result();
		// $option 	= "<option value='0'>Select Product Name</option>";
		// foreach($product as $row)	{
		// $option .= "<option value='".$row->id_process."'>".strtoupper($row->nama)."</option>";
		// }
		echo json_encode(array(
			// 'option' => $option,
      'daycode' => $daycode
		));
	}

  public function get_list_process2(){
		$id_product = $this->uri->segment(3);
    $costcenter = $this->uri->segment(4);

    $daycode = "";
    if($costcenter == 'CC2000012'){
      $filter = $this->db->query("SELECT
                                    	a.code AS code
                                    FROM
                                    	( report_produksi_daily_detail a LEFT JOIN report_produksi_daily_header b ON ( ( a.id_produksi_h = b.id_produksi_h ) ) )
                                    WHERE
                                      b.id_costcenter = 'CC2000012'
                                      AND a.id_product = '".$id_product."'
                                    GROUP BY
                                    	b.id_costcenter,
                                    	a.id_product,
                                    	a.code")->result_array();

      $dtListArray = array();
      foreach($filter AS $val => $valx){
        $dtListArray[$val] = $valx['code'];
      }
      $dtImplode	= "('".implode("','", $dtListArray)."')";

      $filter_daycode = $this->db->query("SELECT code FROM daycode WHERE code NOT IN ".$dtImplode." ORDER BY code ASC ")->result();
      foreach($filter_daycode as $row)	{
  		    $daycode .= "<option value='".$row->code."'>".strtoupper($row->code)."</option>";
  		}

    }

    if($costcenter != 'CC2000012'){
      $filter = $this->db->query("SELECT
                                      a.code AS code
                                    FROM
                                      ( report_produksi_daily_detail a LEFT JOIN report_produksi_daily_header b ON ( ( a.id_produksi_h = b.id_produksi_h ) ) )
                                    WHERE
                                      b.id_costcenter = '".$costcenter."'
                                      AND a.id_product = '".$id_product."'
                                    GROUP BY
                                      b.id_costcenter,
                                      a.id_product,
                                      a.code")->result_array();
      $dtListArray = array();
      foreach($filter AS $val => $valx){
        $dtListArray[$val] = $valx['code'];
      }
      $dtImplode	= "('".implode("','", $dtListArray)."')";

      $filter_daycode = $this->db->query("SELECT
                                      a.code AS code
                                    FROM
                                      ( report_produksi_daily_detail a LEFT JOIN report_produksi_daily_header b ON ( ( a.id_produksi_h = b.id_produksi_h ) ) )
                                    WHERE
                                      code NOT IN ".$dtImplode."
                                      AND b.id_costcenter = 'CC2000012'
                                      AND a.id_product = '".$id_product."'
                                      AND sts_daycode='N'
                                    GROUP BY
                                      b.id_costcenter,
                                      a.id_product,
                                      a.code
                                    ORDER BY code ASC")->result();
      foreach($filter_daycode as $row)	{
  		    $daycode .= "<option value='".$row->code."'>".strtoupper($row->code)."</option>";
  		}
    }

		echo json_encode(array(
      'daycode' => $daycode
		));
	}

  public function download_excel(){
      //$brg_data = $this->Barang_model->tampil_produk()->result_array();
      $data	= $this->db->query("SELECT
                                	a.id_produksi,
                                	a.id_produksi_h,
                                	a.tanggal_produksi,
                                	b.id_costcenter,
                                	a.id_product,
                                	COUNT( a.id_product ) AS qty,
                                  d.id_category1
                                FROM
                                	report_produksi_daily_detail a
                                	LEFT JOIN report_produksi_daily_header b ON a.id_produksi_h = b.id_produksi_h
                                  LEFT JOIN ms_inventory_category2 d ON a.id_product = d.id_category2
                                WHERE
                                  a.ket <> 'not yet'
                                GROUP BY
                                  a.tanggal_produksi,
                                	b.id_costcenter,
                                	a.id_product
                                ORDER BY
                                	a.tanggal_produksi DESC,
                                  b.id_costcenter ASC,
                                  d.id_category1 ASC,
                                  a.id_product ASC
                                  ")->result();

      $data = array(
  			'title2'		  => 'Report',
  			'results'	  => $data
  		);

      $this->load->view('excel_produksi',$data);


  }

  public function delete_daycode(){
  	$Arr_Kembali	= array();
		$data			    = $this->input->post();
		$session      = $this->session->userdata('app_session');
    $id	          = $data['id'];

    $ArrHeader		  = array(
      'sts_daycode'	  => "Y",
      'remarks'       => 'delete daycode',
      'delivery_code' => 'delete daycode',
      'delivery_by'	  => $session['id_user'],
      'delivery_date'	=> date('Y-m-d H:i:s')
    );

		$this->db->trans_start();
      $this->db->where('id', $id);
			$this->db->delete('report_produksi_daily_detail');
		$this->db->trans_complete();

		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=>'Delete gagal disimpan ...',
				'status'	=> 0
			);
		}
		else{
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=>'Delete berhasil disimpan. Thanks ...',
				'status'	=> 1
			);
      history("Delete daycode ".$id);
		}

		echo json_encode($Arr_Data);
	}

  public function delete_daycode_double_qc(){
  	$Arr_Kembali	= array();
		$data			    = $this->input->post();
		$session      = $this->session->userdata('app_session');
    $id	          = $data['id'];

    $ArrHeader		  = array(
      'sts_daycode'	  => "Y",
      'remarks'       => 'delete daycode double melewati qc',
      'delivery_code' => 'delete daycode double melewati qc',
      'delivery_by'	  => $session['id_user'],
      'delivery_date'	=> date('Y-m-d H:i:s')
    );

		$this->db->trans_start();
      $this->db->where('id', $id);
			$this->db->delete('report_produksi_daily_detail');
		$this->db->trans_complete();

		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=>'Delete gagal disimpan ...',
				'status'	=> 0
			);
		}
		else{
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=>'Delete berhasil disimpan. Thanks ...',
				'status'	=> 1
			);
      history("Delete daycode double melewati QC ".$id);
		}

		echo json_encode($Arr_Data);
	}

  //Production Planning
  public function production_planning(){
    $this->auth->restrict($this->viewPermission);
    $session = $this->session->userdata('app_session');
    $this->template->page_icon('fa fa-users');
    history("View index production planning");
    $this->template->title('Production Planning');
    $this->template->render('production_planning');
  }

  public function data_side_plan(){
    $this->produksi_model->get_json_plan();
  }

  public function detail_production_planning(){
    // $this->auth->restrict($this->viewPermission);
    $no_plan 	= $this->input->post('no_plan');

    $data_num = $this->db->query("SELECT * FROM produksi_planning_data WHERE no_plan='".$no_plan."' GROUP BY `date` ORDER BY `date`")->num_rows();
    $data = $this->db->query("SELECT * FROM produksi_planning_data WHERE no_plan='".$no_plan."' GROUP BY `date` ORDER BY `date`")->result_array();

    $product = $this->db->query("SELECT * FROM produksi_planning_data WHERE no_plan='".$no_plan."' GROUP BY product ORDER BY product")->result_array();

    $header = $this->db->query("SELECT * FROM produksi_planning WHERE no_plan='".$no_plan."'")->result();

    // print_r($header);
    $data = [
      'data_num' => $data_num,
      'data' => $data,
      'product' => $product,
      'header'=> $header
    ];
    $this->template->set('results', $data);
    $this->template->render('detail_production_planning', $data);
  }

  public function add_production_planning(){
    if($this->input->post()){
      $Arr_Kembali	= array();
      $data			= $this->input->post();
      // print_r($data);
      // exit;
      $session 		  = $this->session->userdata('app_session');
      $detail 	    = $data['detail'];
      $footer 	    = $data['footer'];
      $Ym					  = date('y');
      $no_plan      = $data['no_plan'];
      $no_planx     = $data['no_plan'];
      $costcenter   = $data['costcenter'];
      $date_akhir   = date('Y-m-d', strtotime($data['date_produksi_plan']));
      $date_awal    = $data['date_awal'];
      // print_r($detail);
      // print_r($footer);
      // exit;
      $created_by   = 'updated_by';
      $created_date = 'updated_date';
      $tanda        = 'Update ';
      if(empty($no_planx)){

        $srcMtr			  = "SELECT MAX(no_plan) as maxP FROM produksi_planning WHERE no_plan LIKE 'PR".$Ym."%' ";
        $numrowMtr		= $this->db->query($srcMtr)->num_rows();
        $resultMtr		= $this->db->query($srcMtr)->result_array();
        $angkaUrut2		= $resultMtr[0]['maxP'];
        $urutan2		  = (int)substr($angkaUrut2, 4, 4);
        $urutan2++;
        $urut2			  = sprintf('%04s',$urutan2);
        $no_plan	      = "PR".$Ym.$urut2;

        $created_by   = 'created_by';
        $created_date = 'created_date';
        $tanda        = 'Insert ';
      }

      $ArrHeader		= array(
        'no_plan'		    => $no_plan,
        'costcenter'	  => $costcenter,
        'date_awal'	    => $date_awal,
        'date_akhir'	  => $date_akhir,
        $created_by	    => $session['id_user'],
        $created_date	  => date('Y-m-d H:i:s')
      );



      $ArrDetail	= array();
      $ArrDetail2	= array();
      $nomor1 = 0;
      foreach($detail AS $val => $valx){ $nomor1++;
        $urut				= sprintf('%03s',$val);
        $nomor2 = 0;
        foreach($valx['data'] AS $val2 => $valx2){ $nomor2++;
          // echo $val."-".$val2."=".$valx2['product']."<br>";
          $ArrDetail[$nomor1."-".$nomor2]['no_plan'] 	      = $no_plan;
          $ArrDetail[$nomor1."-".$nomor2]['no_plan_detail']  = $no_plan."-".$urut;
          $ArrDetail[$nomor1."-".$nomor2]['date']          = $valx['date'];
          $ArrDetail[$nomor1."-".$nomor2]['product']       = $valx2['product'];
          $ArrDetail[$nomor1."-".$nomor2]['qty_order']     = $valx2['qty_order'];
          $ArrDetail[$nomor1."-".$nomor2]['stock']         = $valx2['stock'];
          $ArrDetail[$nomor1."-".$nomor2]['shortages']     = $valx2['shortages'];
          $ArrDetail[$nomor1."-".$nomor2]['queue']         = $valx2['queue'];
          $ArrDetail[$nomor1."-".$nomor2]['qty']           = $valx2['qty'];
          $ArrDetail[$nomor1."-".$nomor2]['man_power']     = $valx2['man_power'];
          $ArrDetail[$nomor1."-".$nomor2]['cycletime']     = $valx2['cycletime'];
          $ArrDetail[$nomor1."-".$nomor2]['mp_ct']         = $valx2['mp_ct'];
        }
      }

      // print_r($ArrHeader);
      // print_r($ArrDetail);
      // print_r($data);
      // exit;

      foreach($footer AS $val => $valx){
        $urut				= sprintf('%03s',$val);
        foreach($valx['man_minutes'] AS $val2 => $valx2){
          $ArrDetail2[$val.$val2]['no_plan'] 	      = $no_plan;
          $ArrDetail2[$val.$val2]['no_plan_detail'] = $no_plan."-".$urut;
          $ArrDetail2[$val.$val2]['date']           = $valx['date'];
          $ArrDetail2[$val.$val2]['category']       = $valx2['category'];
          $ArrDetail2[$val.$val2]['value']          = str_replace(',','',$valx2['value']);
        }
        foreach($valx['availability'] AS $val3 => $valx3){ $val2++;
          $ArrDetail2[$val.$val2]['no_plan'] 	      = $no_plan;
          $ArrDetail2[$val.$val2]['no_plan_detail'] = $no_plan."-".$urut;
          $ArrDetail2[$val.$val2]['date']           = $valx['date'];
          $ArrDetail2[$val.$val2]['category']       = $valx3['category'];
          $ArrDetail2[$val.$val2]['value']          = str_replace(',','',$valx3['value']);
        }
      }

      // print_r($ArrHeader);
      // print_r($ArrDetail);
      // print_r($ArrDetail2);
      // exit;

      $this->db->trans_start();
        if(empty($no_planx)){
          $this->db->delete('produksi_planning', array('no_plan' => $no_plan));
          $this->db->insert('produksi_planning', $ArrHeader);
        }
        if(!empty($no_planx)){
          $this->db->where('no_plan', $no_plan);
          $this->db->update('produksi_planning', $ArrHeader);
        }

        if(!empty($ArrDetail)){
          $this->db->delete('produksi_planning_data', array('no_plan' => $no_plan));
          $this->db->insert_batch('produksi_planning_data', $ArrDetail);
        }
        if(!empty($ArrDetail2)){
          $this->db->delete('produksi_planning_footer', array('no_plan' => $no_plan));
          $this->db->insert_batch('produksi_planning_footer', $ArrDetail2);
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
        history($tanda." Production Planning ".$no_plan);
      }

      echo json_encode($Arr_Data);
    }
    else{
      $session  = $this->session->userdata('app_session');
      $no_plan 	  = $this->uri->segment(3);
      $header   = $this->db->get_where('produksi_planning',array('no_plan' => $no_plan))->result();
      $plan    = $this->produksi_model->get_data('ms_costcenter','deleted','0');
      // print_r($plan);
      // exit;
      $data = [
        'header' => $header,
        'plan' => $plan
      ];
      $this->template->set('results', $data);
      $this->template->title('Add Production Planning');
      $this->template->page_icon('fa fa-edit');
      $this->template->render('add_production_planning',$data);
    }
  }

  public function hapus(){
      $data = $this->input->post();
      $session 		= $this->session->userdata('app_session');
      $no_plan  = $data['id'];

      $this->db->trans_start();
          $this->db->where('no_plan', $no_plan);
          $this->db->delete('produksi_planning');

          $this->db->where('no_plan', $no_plan);
          $this->db->delete('produksi_planning_data');

          $this->db->where('no_plan', $no_plan);
          $this->db->delete('produksi_planning_footer');
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
        history("Delete data planning produksi ".$no_plan);
      }

      echo json_encode($Arr_Data);

  }

  public function get_planning(){
    $date_akhir = $this->uri->segment(3);
    $costcenter = $this->uri->segment(4);
    $date_awal 	= $this->uri->segment(5);
    $no_plan 	  = $this->uri->segment(6);

    $date_now   = date('Y-m-d', strtotime(date('Y-m-d')));
    // $date_now   = date('Y-m-d', strtotime('2020-08-27'));

    // $q_max      = "SELECT MAx(date_akhir) AS date_akhir FROM produksi_planning WHERE costcenter='".$costcenter."' LIMIT 1 ";
    // $max_date   = $this->db->query($q_max)->result();

    $max_date   = $this->db->select_max('date_akhir')->limit(1)->get_where('produksi_planning', array('costcenter'=>$costcenter))->result();

    $datex      = (!empty($max_date[0]->date_akhir))?$max_date[0]->date_akhir:$date_now;
    $date       = date('Y-m-d', strtotime('+1 days', strtotime($datex)));
    if(!empty($date_awal)){
      $date       = date('Y-m-d', strtotime($date_awal));
    }

    $akhir      = new DateTime($date_akhir);
    $awal       = new DateTime($date);
    // echo $date; exit;
    $perbedaan  = $akhir->diff($awal);
    $colspan    = $perbedaan->days + 1;

    // $sql_group = "SELECT id_product FROM cycletime_fast WHERE costcenter='".$costcenter."' GROUP BY id_product";
    // $rest_group = $this->db->query($sql_group)->result_array();

    $rest_group = $this->db->select('id_product')->group_by('id_product')->get_where('cycletime_fast',array('costcenter'=>$costcenter))->result_array();

    $dtListArray = array();
				foreach($rest_group AS $val => $valx){
					$dtListArray[$val] = $valx['id_product'];
				}
				$dtImplode	= "('".implode("','", $dtListArray)."')";

    // echo $colspan; exit;
    $product    = $this->db->query("SELECT product, SUM(qty_order) AS qty_order, delivery_date, qty_propose FROM sales_order_detail WHERE delivery_date BETWEEN '".$date."' AND '".$date_akhir."' AND product IN ".$dtImplode." GROUP BY product ORDER BY product ")->result_array();

    $product_date       = $this->db->query("SELECT delivery_date, no_so FROM sales_order_detail WHERE delivery_date BETWEEN '".$date."' AND '".$date_akhir."' GROUP BY no_so ORDER BY delivery_date ")->result_array();
    $product_date_num   = COUNT($product_date);

    $d_Header = "<div class='box box-primary'>";
        $d_Header .= "<div class='box-body'>";
        $d_Header .= "<div class='tableFixHead' style='height:500px;'>";
        $d_Header .= "<table class='table table-bordered table-striped'>";
        $d_Header .= "<thead class='thead'>";
        $d_Header .= "<tr class='bg-blue'>";
          $d_Header .= "<th class='text-center th headcol' style='vertical-align:middle; width:500px !important; z-index: 99999;' rowspan='2'>Product</th>";
          foreach ($product_date as $key2x => $value2x) {
              $d_Header .= "<th class='text-center th long' style='vertical-align:middle;' rowspan='2' width='100px'>Delivery<br>".date('d M Y', strtotime($value2x['delivery_date']))."</th>";
          }
          $d_Header .= "<th class='text-center th long' style='vertical-align:middle;' rowspan='2' width='100px'>Total Propose</th>";
          $d_Header .= "<th class='text-center th long' style='vertical-align:middle;' rowspan='2' width='100px'>Stock</th>";
          $d_Header .= "<th class='text-center th long' style='vertical-align:middle;' rowspan='2' width='100px'>Shortages to Fulfill Propose</th>";
          $d_Header .= "<th class='text-center th long' style='vertical-align:middle;' rowspan='2' width='100px'>Queue</th>";
          $d_Header .= "<th class='text-center th long' style='vertical-align:middle;' rowspan='2' width='100px'>Balance</th>";
          $d_Header .= "<th class='text-center th' style='vertical-align:middle;' colspan='".$colspan."'>Production Planning Date</th>";
        $d_Header .= "</tr>";
        $siz = 65/$colspan;
        $cols_empty = $colspan + 5;
        $d_Header .= "<tr class='bg-blue'>";
          for ($a=0; $a<$colspan; $a++) {
              $loop_date  = date("l", strtotime("+".$a." day", strtotime($date)));
              $loop_date2 = date("Y-m-d", strtotime("+".$a." day", strtotime($date)));
              $loop_date3 = date("d-m-y", strtotime("+".$a." day", strtotime($date)));
              $d_Header .= "<th class='text-center th' style='font-size: 12px; vertical-align:middle;'>".$loop_date."<br>".$loop_date3."
                            <input type='hidden' name='detail[".$a."][date]' class='form-control text-right input-md' value='".$loop_date2."'>
                            <input type='hidden' name='footer[".$a."][date]' class='form-control text-right input-md' value='".$loop_date2."'>
                            </th>";
          }
        $d_Header .= "</tr>";

        $d_Header .= "</thead>";
        $d_Header .= "<tbody>";
          foreach ($product as $key => $value) { $key++;
              $key = $key - 1;
              // $r_data_stock = $this->db->select('qty_stock')->limit(1)->get_where('warehouse_product', array('costcenter'=>$costcenter,'id_product'=>$value['product'],'category'=>'order'))->result();
              //diubah per 21/05/2021 jadi stock FG request client
			  $r_data_stock = $this->db->select('qty_stock')->limit(1)->get_where('warehouse_product', array('id_product'=>$value['product'],'category'=>'product'))->result();
              
			  $stock = (!empty($r_data_stock[0]->qty_stock))?$r_data_stock[0]->qty_stock:0;

              $r_data = $this->db->select('mp, cycletime')->limit(1)->get_where('cycletime_fast', array('costcenter'=>$costcenter,'id_product'=>$value['product']))->result();

              $mp = (!empty($r_data))?$r_data[0]->mp:0;
              $ct = (!empty($r_data))?$r_data[0]->cycletime:0;

              $d_Header .= "<tr class='header_".$key."'>";
              $d_Header .= "<td class='headcol'>".strtoupper(get_name('ms_inventory_category2','nama','id_category2',$value['product']))."</td>";
              $SUMD = 0;
              foreach ($product_date as $key2x => $value2x) {

                  $qty_order = $this->db->select('qty_order')->limit(1)->get_where('sales_order_detail', array('no_so'=>$value2x['no_so'],'delivery_date'=>$value2x['delivery_date'],'product'=>$value['product']))->result();

                  $qty_ = (!empty($qty_order[0]->qty_order))?$qty_order[0]->qty_order:0;

                  $SUMD += $qty_;
                  $d_Header .= "<td class='text-center long'>".$qty_."</td>";
              }
              $d_Header .= "<td class='text-center long'>".$SUMD."</td>";
              $sisa = $SUMD - $stock;
              $d_Header .= "<td class='text-center long'>".$stock."</td>";


              $d_Header .= "<td class='text-center long'>".$sisa."</td>";
              $d_Header .= "<td class='text-center long'>".get_antrian_wip($value['product'], $costcenter)."</td>";
              $d_Header .= "<td class='text-center long'><span id='balance_".$key."'>".$sisa."</span></td>";
              for ($a=0; $a<$colspan; $a++) {
                $loop_date2 = date("Y-m-d", strtotime("+".$a." day", strtotime($date)));
                $rest_d = $this->db->select('qty, mp_ct')->limit(1)->get_where('produksi_planning_data', array('no_plan'=>$no_plan,'date'=>$loop_date2,'product'=>$value['product']))->result();

                $qty    = (!empty($rest_d[0]->qty))?$rest_d[0]->qty:'';
                $mpCT    = (!empty($rest_d[0]->mp_ct))?$rest_d[0]->mp_ct:0;
                $d_Header .= "<td class='text-center'>";
                $d_Header .= "<input type='text' id='qtyp_".$key."_".$a."' name='detail[".$a."][data][".$key."][qty]' style='min-width: 70px !important;' class='form-control text-center input-md maskM get_tot_ct bal_".$key."' value='".$qty."' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
                $d_Header .= "<input type='hidden' id='mp_".$key."_".$a."' name='detail[".$a."][data][".$key."][man_power]' class='form-control text-left input-md maskM' value='".$mp."' placeholder='CT' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
                $d_Header .= "<input type='hidden' id='ct_".$key."_".$a."' name='detail[".$a."][data][".$key."][cycletime]' class='form-control text-left input-md maskM' value='".$ct."' placeholder='MP' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
                $d_Header .= "<input type='hidden' id='tot_ct_".$key."_".$a."' name='detail[".$a."][data][".$key."][mp_ct]' class='form-control text-left input-md maskM tot_ct_".$a."' value='".$mpCT."' placeholder='CT*MP*QTY' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>
                              <input type='hidden' name='detail[".$a."][data][".$key."][product]' class='form-control text-center input-md maskM' readonly value='".$value['product']."'>
                              <input type='hidden' name='detail[".$a."][data][".$key."][qty_order]' class='form-control text-center input-md maskM' readonly value='".$value['qty_order']."'>
                              <input type='hidden' name='detail[".$a."][data][".$key."][stock]' class='form-control text-center input-md maskM' readonly value='".$stock."'>
                              <input type='hidden' name='detail[".$a."][data][".$key."][shortages]' id='sh_".$key."' class='form-control text-center input-md maskM' readonly value='".$sisa."'>
                              <input type='hidden' name='detail[".$a."][data][".$key."][queue]' class='form-control text-center input-md maskM' readonly value='".get_antrian_wip($value['product'], $costcenter)."'>
                              </td>";
              }
              $d_Header .= "</tr>";
          }
          $col = $product_date_num + 5;

          $d_Header .= "<tr>";
            $d_Header .= "<td class='text-left'></td>";
            $d_Header .= "<td class='text-left' colspan='".$col."'><b>TOTAL MAN MINUTES</b></td>";
            for ($a=0; $a<$colspan; $a++) {
              $loop_date2 = date("Y-m-d", strtotime("+".$a." day", strtotime($date)));
              
              $rest_d = $this->db->select('value')->limit(1)->get_where('produksi_planning_footer', array('no_plan'=>$no_plan,'date'=>$loop_date2,'category'=>'man minutes'))->result();

              $value    = (!empty($rest_d[0]->value))?number_format($rest_d[0]->value):'';

              $d_Header .= "<td class='text-center'>";
              $d_Header .= "<input type='text' id='tot_ct_".$a."' name='footer[".$a."][man_minutes][".$a."][value]' value='".$value."' class='form-control text-right input-md maskM' readonly data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>
                            <input type='hidden' name='footer[".$a."][man_minutes][".$a."][category]' value='man minutes' class='form-control text-right input-md' readonly>
                            </td>";
            }
          $d_Header .= "</tr>";
          $d_Header .= "<tr>";
            $d_Header .= "<td class='text-left'><input type='text' readonly style='width: 200px; !important;border-color: transparent; background-color: transparent;'></td>";
            $d_Header .= "<td class='text-left' colspan='".$col."'><b>AVAILABILITY MAN MINUTES</b></td>";
            for ($a=0; $a<$colspan; $a++) {
              $loop_date = date("l", strtotime("+".$a." day", strtotime($date)));
              
              $get_mp = $this->db->select('mp_1, mp_2, mp_3, shift1, shift2, shift3')->limit(1)->get_where('ms_costcenter', array('id_costcenter'=>$costcenter))->result();

              $mpx1 = $get_mp[0]->mp_1;
              $mpx2 = $get_mp[0]->mp_2;
              $mpx3 = $get_mp[0]->mp_3;
              $shx1 = $get_mp[0]->shift1;
              $shx2 = $get_mp[0]->shift2;
              $shx3 = $get_mp[0]->shift3;

              $get_time1 = $this->db->select('b.id_day, b.start_work, b.start_break1, b.done_work, b.done_break1')->limit(1)->join('ms_hari c', 'b.id_day = c.id_hari', 'left')->get_where('ms_shift b', array('b.type_shift'=>'1','c.day_en'=>$loop_date))->result();
              $get_time2 = $this->db->select('b.id_day, b.start_work, b.start_break1, b.done_work, b.done_break1')->limit(1)->join('ms_hari c', 'b.id_day = c.id_hari', 'left')->get_where('ms_shift b', array('b.type_shift'=>'2','c.day_en'=>$loop_date))->result();
              $get_time3 = $this->db->select('b.id_day, b.start_work, b.start_break1, b.done_work, b.done_break1')->limit(1)->join('ms_hari c', 'b.id_day = c.id_hari', 'left')->get_where('ms_shift b', array('b.type_shift'=>'3','c.day_en'=>$loop_date))->result();



              $day = $get_time1[0]->id_day;

              if($shx1 == 'N'){
                $tm1 = 0;
              }else{
                $tm1 = 0;
                if($day <> 'Sat'){
                  if(!empty($get_time1)){
                    $sw   = date_create(get_24($get_time1[0]->start_work));
                    $sb1  = date_create(get_24($get_time1[0]->start_break1));
                    $tm1_1= date_diff($sw, $sb1);

                    $dw   = date_create(get_24($get_time1[0]->done_work));
                    $db1  = date_create(get_24($get_time1[0]->done_break1));
                    $tm1_2= date_diff($dw, $db1);

                    $tm1 = (($tm1_1->h + $tm1_2->h)+(($tm1_1->i + $tm1_2->i)/60)) * $mpx1;
                    $tm1x = (($tm1_1->h + $tm1_2->h)+(($tm1_1->i + $tm1_2->i)/60))."<br>";
                  }else{
                    $tm1 = 0;
                    $tm1x = (($tm1_1->h + $tm1_2->h)+(($tm1_1->i + $tm1_2->i)/60))."<br>";
                  }
                }

                if($day == 'Sat'){
                  if(!empty($get_time1)){
                    $sw   = date_create(get_24($get_time1[0]->start_work));
                    $dw   = date_create(get_24($get_time1[0]->done_work));
                    $tm1_1= date_diff($sw, $dw);

                    $tm1 = (($tm1_1->h)+(($tm1_1->i)/60)) * $mpx1;
                    $tm1x = (($tm1_1->h)+(($tm1_1->i)/60))."<br>";
                  }else{
                    $tm1 = 0;
                    $tm1x = (($tm1_1->h)+(($tm1_1->i)/60))."<br>";
                  }
                }
              }

              if($shx2 == 'N'){
                $tm2 = 0;
              }else{
                $tm2 = 0;
                if($day <> 'Sat'){
                  if(!empty($get_time2)){
                    $sw   = date_create(get_24($get_time2[0]->start_work));
                    $sb1  = date_create(get_24($get_time2[0]->start_break1));
                    $tm2_1= date_diff($sw, $sb1);

                    $dw   = date_create(get_24($get_time2[0]->done_work));
                    $db1  = date_create(get_24($get_time2[0]->done_break1));
                    $tm2_2= date_diff($dw, $db1);

                    $tm2 = (($tm2_1->h + $tm2_2->h)+(($tm2_1->i + $tm2_2->i)/60)) * $mpx2;
                    $tm2x = (($tm2_1->h + $tm2_2->h)+(($tm2_1->i + $tm2_2->i)/60))."<br>";
                  }else{
                    $tm2 = 0;
                    $tm2x = (($tm2_1->h + $tm2_2->h)+(($tm2_1->i + $tm2_2->i)/60))."<br>";
                  }
                }

                if($day == 'Sat'){
                  if(!empty($get_time2)){
                    $sw   = date_create(get_24($get_time2[0]->start_work));
                    $dw   = date_create(get_24($get_time2[0]->done_work));
                    $tm2_2= date_diff($sw, $dw);

                    $tm2 = (($tm2_2->h)+(($tm2_2->i)/60)) * $mpx2;
                    $tm2x = (($tm2_2->h)+(($tm2_2->i)/60))."<br>";
                  }else{
                    $tm2 = 0;
                    $tm2x = (($tm2_2->h)+(($tm2_2->i)/60))."<br>";
                  }
                }
              }

              if($shx3 == 'N'){
                $tm3 = 0;
                $tm3x = "<br>";
              }else{
                $tm3 = 0;
                if($day <> 'Sat'){
                  if(!empty($get_time3)){
                    $sw   = date_create($get_time3[0]->start_work);
                    $sb1  = date_create($get_time3[0]->start_break1);
                    $tm3_1= date_diff($sw, $sb1);

                    $dw   = date_create($get_time3[0]->done_work);
                    $db1  = date_create($get_time3[0]->done_break1);
                    $tm3_2= date_diff($dw, $db1);

                    $tm3 = (($tm3_1->h + $tm3_2->h)+(($tm3_1->i + $tm3_2->i)/60)) * $mpx1;;
                    $tm3x = (($tm3_1->h + $tm3_2->h)+(($tm3_1->i + $tm3_2->i)/60))."<br>";
                  }else{
                    $tm3 = 0;
                    $tm3x = (($tm3_1->h + $tm3_2->h)+(($tm3_1->i + $tm3_2->i)/60))."<br>";
                  }
                }

                if($day == 'Sat'){
                  if(!empty($get_time3)){
                    $sw   = date_create($get_time3[0]->start_work);
                    $dw   = date_create($get_time3[0]->done_work);
                    $tm3_2= date_diff($sw, $dw);

                    $tm3 = (($tm3_2->h)+(($tm3_2->i)/60)) * $mpx1;;
                    $tm3x = (($tm3_2->h)+(($tm3_2->i)/60))."<br>";
                  }else{
                    $tm3 = 0;
                    $tm3x = (($tm3_2->h)+(($tm3_2->i)/60))."<br>";
                  }
                }
              }

              $m_shift = floatval($tm1) + floatval($tm2) + floatval($tm3);
              $pengali = $m_shift * 60 * (93/100);
              $tanda = "";
              // $tanda = $tm1."_".$tm2."_".$tm3."<br>".$m_shift."<br>".$pengali;

              $tanda2 = "";
              // $tanda2 = $tm1x.$tm2x.$tm3x;
              $d_Header .= "<td class='text-center'>".$tanda2.$tanda;
              $d_Header .= "<input type='text' id='man_ct_".$a."' name='footer[".$a."][availability][".$a."][value]' value='".number_format($pengali)."' class='form-control text-right input-md maskM' readonly>
                            <input type='hidden' name='footer[".$a."][availability][".$a."][category]' value='availability' class='form-control text-right input-md' readonly>
                            </td>";
            }
          $d_Header .= "</tr>";
          if(empty($product)){
            $d_Header .= "<tr>";
            $d_Header .= "<td class='text-left' colspan='".$cols_empty."'>Not Found Data ...</td>";
            $d_Header .= "</tr>";
          }
        $d_Header .= "</tbody>";
        $d_Header .= "</table>";
        $d_Header .= "</div>";
        $d_Header .= "</div>";
    $d_Header .= "</div>";


     echo json_encode(array(
        'header'			=> $d_Header,
        'total' => $colspan
     ));
  }

  public function get_planning_edit(){
    $date_akhir = $this->uri->segment(3);
    $costcenter = $this->uri->segment(4);
    $date_awal 	= $this->uri->segment(5);
    $no_plan 	  = $this->uri->segment(6);

    $date_now   = date('Y-m-d', strtotime(date('Y-m-d')));
    // $date_now   = date('Y-m-d', strtotime('2020-08-12'));

    $q_max      = "SELECT MAx(date_akhir) AS date_akhir FROM produksi_planning WHERE costcenter='".$costcenter."' LIMIT 1 ";
    $max_date   = $this->db->query($q_max)->result();

    $datex      = (!empty($max_date[0]->date_akhir))?$max_date[0]->date_akhir:$date_now;
    $date       = date('Y-m-d', strtotime('+1 days', strtotime($datex)));
    if(!empty($date_awal)){
      $date       = date('Y-m-d', strtotime($date_awal));
    }

    $akhir      = new DateTime($date_akhir);
    $awal       = new DateTime($date);
    // echo $date; exit;
    $perbedaan  = $akhir->diff($awal);
    $colspan    = $perbedaan->d + 1;
    // echo $perbedaan->d;
    $product    = $this->db->query("SELECT product, SUM(qty_order) AS qty_order FROM produksi_planning_data WHERE no_plan = '".$no_plan."' GROUP BY product ORDER BY product ")->result_array();

    $product_date    = $this->db->query("SELECT delivery_date FROM sales_order_detail WHERE delivery_date BETWEEN '".$date."' AND '".$date_akhir."' GROUP BY delivery_date ORDER BY delivery_date ")->result_array();
    $product_date_num    = $this->db->query("SELECT delivery_date FROM sales_order_detail WHERE delivery_date BETWEEN '".$date."' AND '".$date_akhir."' GROUP BY delivery_date ORDER BY delivery_date ")->num_rows();

    $d_Header = "<div class='box box-primary'>";
        $d_Header .= "<div class='box-body'>";
        $d_Header .= "<div class='tableFixHead' style='height:500px;'>";
        $d_Header .= "<table class='table table-bordered table-striped'>";
        $d_Header .= "<thead class='thead'>";
        $d_Header .= "<tr class='bg-blue'>";
          $d_Header .= "<th class='text-center th' style='vertical-align:middle; width:300px !important;' rowspan='3'>Product</th>";
          foreach ($product_date as $key2x => $value2x) {
              $d_Header .= "<th class='text-center th' style='vertical-align:middle;' rowspan='3' width='100px'>Delivery<br>".date('d M Y', strtotime($value2x['delivery_date']))."</th>";
          }
          $d_Header .= "<th class='text-center th' style='vertical-align:middle;' rowspan='3' width='100px'>Total Propose</th>";
          $d_Header .= "<th class='text-center th' style='vertical-align:middle;' rowspan='3' width='100px'>Stock</th>";
          $d_Header .= "<th class='text-center th' style='vertical-align:middle;' rowspan='3' width='100px'>Shortages to Fulfill Orders</th>";
          $d_Header .= "<th class='text-center th' style='vertical-align:middle;' rowspan='3' width='100px'>Queue</th>";
          $d_Header .= "<th class='text-center th' style='vertical-align:middle;' colspan='".$colspan."'>Production Planning Date</th>";
        $d_Header .= "</tr>";
        $siz = 65/$colspan;
        $cols_empty = $colspan + 5;
        $d_Header .= "<tr class='bg-blue'>";
          for ($a=0; $a<$colspan; $a++) {
              $loop_date = date("l", strtotime("+".$a." day", strtotime($date)));
              $d_Header .= "<th class='text-center th' style='font-size: 12px; vertical-align:middle;'>".$loop_date."</th>";
          }
        $d_Header .= "</tr>";
        $d_Header .= "<tr class='bg-blue'>";
          for ($a=0; $a<$colspan; $a++) {
              $loop_date = date("d-m-y", strtotime("+".$a." day", strtotime($date)));
              $loop_date2 = date("Y-m-d", strtotime("+".$a." day", strtotime($date)));
              $d_Header .= "<th class='text-center th' style='font-size: 12px; vertical-align:middle; width:300px !important;'>".$loop_date."
                            <input type='hidden' name='detail[".$a."][date]' class='form-control text-right input-md' value='".$loop_date2."'>
                            <input type='hidden' name='footer[".$a."][date]' class='form-control text-right input-md' value='".$loop_date2."'>
                            </th>";
          }
        $d_Header .= "</tr>";

        $d_Header .= "</thead>";
        $d_Header .= "<tbody>";
          foreach ($product as $key => $value) { $key++;
              $key = $key - 1;
              $q_data_stock = "SELECT b.* FROM warehouse_product b WHERE b.costcenter='".$costcenter."' AND b.id_product='".$value['product']."' AND b.category='order' LIMIT 1 ";
              $r_data_stock = $this->db->query($q_data_stock)->result();
              $stock = (!empty($r_data_stock[0]->qty_stock))?$r_data_stock[0]->qty_stock:0;

              $q_data = "SELECT b.* FROM cycletime_fast b WHERE b.costcenter='".$costcenter."' AND b.id_product='".$value['product']."' LIMIT 1 ";
              $r_data = $this->db->query($q_data)->result();

              $mp = (!empty($r_data))?$r_data[0]->mp:0;
              $ct = (!empty($r_data))?$r_data[0]->cycletime:0;

              $d_Header .= "<tr class='header_".$key."'>";
              $d_Header .= "<td>".strtoupper(get_name('ms_inventory_category2','nama','id_category2',$value['product']))."</td>";
              foreach ($product_date as $key2x => $value2x) {
                  $queryx = "SELECT qty_order FROM sales_order_detail WHERE delivery_date = '".$value2x['delivery_date']."' AND product = '".$value['product']."' LIMIT 1 ";
                  $qty_order = $this->db->query($queryx)->result();
                  $qty_ = (!empty($qty_order[0]->qty_order))?$qty_order[0]->qty_order:0;
                  $d_Header .= "<td class='text-center'>".$qty_."</td>";
              }
              $d_Header .= "<td class='text-center'>".$value['qty_order']."</td>";
              $sisa = $value['qty_order'] - $stock;
              $d_Header .= "<td class='text-center'>".$stock."</td>";


              $d_Header .= "<td class='text-center'>".$sisa."</td>";
              $d_Header .= "<td class='text-center'>".get_antrian_wip($value['product'], $costcenter)."</td>";
              for ($a=0; $a<$colspan; $a++) {
                $loop_date2 = date("Y-m-d", strtotime("+".$a." day", strtotime($date)));
                $query  = "SELECT * FROM produksi_planning_data WHERE no_plan='".$no_plan."' AND `date`='".$loop_date2."' AND product='".$value['product']."' LIMIT 1";
                // echo $query;
                $rest_d = $this->db->query($query)->result();
                $qty    = (!empty($rest_d[0]->qty))?$rest_d[0]->qty:'';
                $mpCT    = (!empty($rest_d[0]->mp_ct))?$rest_d[0]->mp_ct:0;
                $d_Header .= "<td class='text-center'>";
                $d_Header .= "<input type='text' id='qtyp_".$key."_".$a."' name='detail[".$a."][data][".$key."][qty]' class='form-control text-center input-md maskM get_tot_ct' value='".$qty."' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
                $d_Header .= "<input type='hidden' id='mp_".$key."_".$a."' name='detail[".$a."][data][".$key."][man_power]' class='form-control text-left input-md maskM' value='".$mp."' placeholder='CT' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
                $d_Header .= "<input type='hidden' id='ct_".$key."_".$a."' name='detail[".$a."][data][".$key."][cycletime]' class='form-control text-left input-md maskM' value='".$ct."' placeholder='MP' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
                $d_Header .= "<input type='hidden' id='tot_ct_".$key."_".$a."' name='detail[".$a."][data][".$key."][mp_ct]' class='form-control text-left input-md maskM tot_ct_".$a."' value='".$mpCT."' placeholder='CT*MP*QTY' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>
                              <input type='hidden' name='detail[".$a."][data][".$key."][product]' class='form-control text-center input-md maskM' readonly value='".$value['product']."'>
                              <input type='hidden' name='detail[".$a."][data][".$key."][qty_order]' class='form-control text-center input-md maskM' readonly value='".$value['qty_order']."'>
                              <input type='hidden' name='detail[".$a."][data][".$key."][stock]' class='form-control text-center input-md maskM' readonly value='".$stock."'>
                              <input type='hidden' name='detail[".$a."][data][".$key."][shortages]' class='form-control text-center input-md maskM' readonly value='".$sisa."'>
                              <input type='hidden' name='detail[".$a."][data][".$key."][queue]' class='form-control text-center input-md maskM' readonly value='".get_antrian_wip($value['product'], $costcenter)."'>
                              </td>";
              }
              $d_Header .= "</tr>";
          }
          $col = $product_date_num + 4;
          // $d_Header .= "<tr id='add_".$key."'>";
          //   $d_Header .= "<td align='left'><button type='button' class='btn btn-sm btn-warning addPart' data-colspan2='".$product_date_num."' data-colspan='".$colspan."' data-tanggal='".$date_akhir."' title='Add Product'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Product</button></td>";
          //   $d_Header .= "<td colspan='".$col."'></td>";
          //   $d_Header .= "<td colspan='".$colspan."'></td>";
          // $d_Header .= "</tr>";

          $d_Header .= "<tr>";
            $d_Header .= "<td class='text-left'></td>";
            $d_Header .= "<td class='text-left' colspan='".$col."'><b>TOTAL MAN MINUTES</b></td>";
            for ($a=0; $a<$colspan; $a++) {
              $loop_date2 = date("Y-m-d", strtotime("+".$a." day", strtotime($date)));
              $query  = "SELECT * FROM produksi_planning_footer WHERE no_plan='".$no_plan."' AND `date`='".$loop_date2."' AND category='man minutes' LIMIT 1";
              // echo $query;
              $rest_d = $this->db->query($query)->result();
              $value    = (!empty($rest_d[0]->value))?number_format($rest_d[0]->value):'';

              $d_Header .= "<td class='text-center'>";
              $d_Header .= "<input type='text' id='tot_ct_".$a."' name='footer[".$a."][man_minutes][".$a."][value]' value='".$value."' class='form-control text-right input-md maskM' readonly data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>
                            <input type='hidden' name='footer[".$a."][man_minutes][".$a."][category]' value='man minutes' class='form-control text-right input-md' readonly>
                            </td>";
            }
          $d_Header .= "</tr>";
          $d_Header .= "<tr>";
            $d_Header .= "<td class='text-left'></td>";
            $d_Header .= "<td class='text-left' colspan='".$col."'><b>AVAILABILITY MAN MINUTES</b></td>";
            for ($a=0; $a<$colspan; $a++) {
              $loop_date = date("l", strtotime("+".$a." day", strtotime($date)));
              $get_mp   = $this->db->query("SELECT b.* FROM ms_costcenter b WHERE b.id_costcenter='".$costcenter."' LIMIT 1 ")->result();;
              $mpx1 = $get_mp[0]->mp_1;
              $mpx2 = $get_mp[0]->mp_2;
              $mpx3 = $get_mp[0]->mp_3;
              $shx1 = $get_mp[0]->shift1;
              $shx2 = $get_mp[0]->shift2;
              $shx3 = $get_mp[0]->shift3;

              $get_time1 = $this->db->query("SELECT b.* FROM ms_shift b LEFT JOIN ms_hari c ON b.id_day=c.id_hari WHERE b.type_shift='1' AND c.day_en='".$loop_date."' LIMIT 1 ")->result();
              $get_time2 = $this->db->query("SELECT b.* FROM ms_shift b LEFT JOIN ms_hari c ON b.id_day=c.id_hari WHERE b.type_shift='2' AND c.day_en='".$loop_date."' LIMIT 1 ")->result();
              $get_time3 = $this->db->query("SELECT b.* FROM ms_shift b LEFT JOIN ms_hari c ON b.id_day=c.id_hari WHERE b.type_shift='3' AND c.day_en='".$loop_date."' LIMIT 1 ")->result();
              $day = $get_time1[0]->id_day;
              // $tm1 = (!empty($get_time1))?($get_time1[0]->start_break1 - $get_time1[0]->start_work) + ($get_time1[0]->done_work - $get_time1[0]->done_break1):0;
              if($shx1 == 'N'){
                $tm1 = 0;
              }else{
                $tm1 = 0;
                if($day <> 'Sat'){
                  if(!empty($get_time1)){
                    $sw   = date_create(get_24($get_time1[0]->start_work));
                    $sb1  = date_create(get_24($get_time1[0]->start_break1));
                    $tm1_1= date_diff($sw, $sb1);

                    $dw   = date_create(get_24($get_time1[0]->done_work));
                    $db1  = date_create(get_24($get_time1[0]->done_break1));
                    $tm1_2= date_diff($dw, $db1);

                    $tm1 = (($tm1_1->h + $tm1_2->h)+(($tm1_1->i + $tm1_2->i)/60)) * $mpx1;
                    $tm1x = (($tm1_1->h + $tm1_2->h)+(($tm1_1->i + $tm1_2->i)/60))."<br>";
                  }else{
                    $tm1 = 0;
                    $tm1x = (($tm1_1->h + $tm1_2->h)+(($tm1_1->i + $tm1_2->i)/60))."<br>";
                  }
                }

                if($day == 'Sat'){
                  if(!empty($get_time1)){
                    $sw   = date_create(get_24($get_time1[0]->start_work));
                    $dw   = date_create(get_24($get_time1[0]->done_work));
                    $tm1_1= date_diff($sw, $dw);

                    $tm1 = (($tm1_1->h)+(($tm1_1->i)/60)) * $mpx1;
                    $tm1x = (($tm1_1->h)+(($tm1_1->i)/60))."<br>";
                  }else{
                    $tm1 = 0;
                    $tm1x = (($tm1_1->h)+(($tm1_1->i)/60))."<br>";
                  }
                }
              }

              if($shx2 == 'N'){
                $tm2 = 0;
              }else{
                $tm2 = 0;
                if($day <> 'Sat'){
                  if(!empty($get_time2)){
                    $sw   = date_create(get_24($get_time2[0]->start_work));
                    $sb1  = date_create(get_24($get_time2[0]->start_break1));
                    $tm2_1= date_diff($sw, $sb1);

                    $dw   = date_create(get_24($get_time2[0]->done_work));
                    $db1  = date_create(get_24($get_time2[0]->done_break1));
                    $tm2_2= date_diff($dw, $db1);

                    $tm2 = (($tm2_1->h + $tm2_2->h)+(($tm2_1->i + $tm2_2->i)/60)) * $mpx2;
                    $tm2x = (($tm2_1->h + $tm2_2->h)+(($tm2_1->i + $tm2_2->i)/60))."<br>";
                  }else{
                    $tm2 = 0;
                    $tm2x = (($tm2_1->h + $tm2_2->h)+(($tm2_1->i + $tm2_2->i)/60))."<br>";
                  }
                }

                if($day == 'Sat'){
                  if(!empty($get_time2)){
                    $sw   = date_create(get_24($get_time2[0]->start_work));
                    $dw   = date_create(get_24($get_time2[0]->done_work));
                    $tm2_2= date_diff($sw, $dw);

                    $tm2 = (($tm2_2->h)+(($tm2_2->i)/60)) * $mpx2;
                    $tm2x = (($tm2_2->h)+(($tm2_2->i)/60))."<br>";
                  }else{
                    $tm2 = 0;
                    $tm2x = (($tm2_2->h)+(($tm2_2->i)/60))."<br>";
                  }
                }
              }

              if($shx3 == 'N'){
                $tm3 = 0;
                $tm3x = "<br>";
              }else{
                $tm3 = 0;
                if($day <> 'Sat'){
                  if(!empty($get_time3)){
                    $sw   = date_create($get_time3[0]->start_work);
                    $sb1  = date_create($get_time3[0]->start_break1);
                    $tm3_1= date_diff($sw, $sb1);

                    $dw   = date_create($get_time3[0]->done_work);
                    $db1  = date_create($get_time3[0]->done_break1);
                    $tm3_2= date_diff($dw, $db1);

                    $tm3 = (($tm3_1->h + $tm3_2->h)+(($tm3_1->i + $tm3_2->i)/60)) * $mpx1;;
                    $tm3x = (($tm3_1->h + $tm3_2->h)+(($tm3_1->i + $tm3_2->i)/60))."<br>";
                  }else{
                    $tm3 = 0;
                    $tm3x = (($tm3_1->h + $tm3_2->h)+(($tm3_1->i + $tm3_2->i)/60))."<br>";
                  }
                }

                if($day == 'Sat'){
                  if(!empty($get_time3)){
                    $sw   = date_create($get_time3[0]->start_work);
                    $dw   = date_create($get_time3[0]->done_work);
                    $tm3_2= date_diff($sw, $dw);

                    $tm3 = (($tm3_2->h)+(($tm3_2->i)/60)) * $mpx1;;
                    $tm3x = (($tm3_2->h)+(($tm3_2->i)/60))."<br>";
                  }else{
                    $tm3 = 0;
                    $tm3x = (($tm3_2->h)+(($tm3_2->i)/60))."<br>";
                  }
                }
              }

              $m_shift = floatval($tm1) + floatval($tm2) + floatval($tm3);
              $pengali = $m_shift * 60 * (93/100);
              $tanda = "";
              // $tanda = $tm1."_".$tm2."_".$tm3."<br>".$m_shift."<br>".$pengali;

              $tanda2 = "";
              // $tanda2 = $tm1x.$tm2x.$tm3x;
              $d_Header .= "<td class='text-center'>".$tanda2.$tanda;
              $d_Header .= "<input type='text' id='man_ct_".$a."' name='footer[".$a."][availability][".$a."][value]' value='".number_format($pengali)."' class='form-control text-right input-md maskM' readonly>
                            <input type='hidden' name='footer[".$a."][availability][".$a."][category]' value='availability' class='form-control text-right input-md' readonly>
                            </td>";
            }
          $d_Header .= "</tr>";
          if(empty($product)){
            $d_Header .= "<tr>";
            $d_Header .= "<td class='text-left' colspan='".$cols_empty."'>Not Found Data ...</td>";
            $d_Header .= "</tr>";
          }
        $d_Header .= "</tbody>";
        $d_Header .= "</table>";
        $d_Header .= "</div>";
        $d_Header .= "</div>";
    $d_Header .= "</div>";


     echo json_encode(array(
        'header'			=> $d_Header,
        'total' => $colspan
     ));
  }

  public function get_product(){
		$id 	     = $this->uri->segment(3);
    $colspan 	 = $this->uri->segment(4);
    $date 	   = $this->uri->segment(5);
    $colspan2 = $this->uri->segment(6);

		$d_Header = "";
    $d_Header .= "<tr class='header_".$id."'>";
    $d_Header .= "<td align='left'>";
    $d_Header .= "<select class='chosen_select form-control input-sm inline-blockd product' data-no='".$id."' data-tgl_akhir='".$date."'>";
      $d_Header .= "<option value='0'>Select Product</option>";
      foreach(get_product() AS $val => $valx){
        $d_Header .= "<option value='".$valx['id_category2']."'>".strtoupper($valx['nama'])."</option>";
      }
      $d_Header .= 		"</select>";
    $d_Header .= "</td>";
    $d_Header .= "<td class='text-center'  colspan='".$colspan2."'></td>";
    $d_Header .= "<td class='text-center'><div id='html_qty_order_".$id."'></div></td>";
    $d_Header .= "<td class='text-center'><div id='html_stock_".$id."'></div></td>";
    $d_Header .= "<td class='text-center'><div id='html_shortages_".$id."'></div></td>";
    $d_Header .= "<td class='text-center'><div id='html_queue_".$id."'></div></td>";
      for ($a=0; $a<$colspan; $a++) {
        $loop_date2 = date("Y-m-d", strtotime("+".$a." day", strtotime($date)));

        $d_Header .= "<td class='text-center'>";
        $d_Header .= "<input type='text' id='qtyp_".$id."_".$a."' name='detail[".$a."][data][".$id."][qty]' class='form-control text-center input-md maskM get_tot_ct' value='' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
        $d_Header .= "<input type='hidden' id='mp_".$id."_".$a."' name='detail[".$a."][data][".$id."][man_power]' class='mp_".$id." form-control text-left input-md maskM' value='man_power' placeholder='CT' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
        $d_Header .= "<input type='hidden' id='ct_".$id."_".$a."' name='detail[".$a."][data][".$id."][cycletime]' class='ct_".$id." form-control text-left input-md maskM' value='cycletime' placeholder='MP' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
        $d_Header .= "<input type='hidden' id='tot_ct_".$id."_".$a."' name='detail[".$a."][data][".$id."][mp_ct]' class='form-control text-left input-md maskM tot_ct_".$a."' value='' placeholder='CT*MP*QTY' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>
                      <input type='hidden' name='detail[".$a."][data][".$id."][product]' class='product_".$id." form-control text-center input-md maskM' readonly value='product'>
                      <input type='hidden' name='detail[".$a."][data][".$id."][qty_order]' class='qty_order_".$id." form-control text-center input-md maskM' readonly value='qty_order'>
                      <input type='hidden' name='detail[".$a."][data][".$id."][stock]' class='stock_".$id." form-control text-center input-md maskM' readonly value='stock'>
                      <input type='hidden' name='detail[".$a."][data][".$id."][shortages]' class='shortages_".$id." form-control text-center input-md maskM' readonly value='sisa'>
                      <input type='hidden' name='detail[".$a."][data][".$id."][queue]' class='queue_".$id." form-control text-center input-md maskM' readonly value='get_antrian_wip'>
                      </td>";
      }
    $d_Header .= "</tr>";



		//add part
		$d_Header .= "<tr id='add_".$id."'>";
      $d_Header .= "<td align='left'><button type='button' class='btn btn-sm btn-warning addPart' data-colspan2='".$colspan2."' data-colspan='".$colspan."' data-tanggal='".$date."' title='Add Product'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Product</button></td>";
      $d_Header .= "<td colspan='4'></td>";
      $d_Header .= "<td colspan='".$colspan."'></td>";
		$d_Header .= "</tr>";

		 echo json_encode(array(
				'header'			=> $d_Header,
		 ));
	}

  public function get_product_data(){
		$no 	        = $this->uri->segment(3);
    $product 	  	= $this->uri->segment(4);
    $costcenter   = $this->uri->segment(5);
    $date_awal    = $this->uri->segment(6);
    $date_akhir   = $this->uri->segment(7);

    $q_data_stock = "SELECT b.* FROM warehouse_product b WHERE b.costcenter='".$costcenter."' AND b.id_product='$product' AND b.category='order' LIMIT 1 ";
    $r_data_stock = $this->db->query($q_data_stock)->result();
    $stock = (!empty($r_data_stock[0]->qty_stock))?$r_data_stock[0]->qty_stock:0;


    $q_data = "SELECT b.* FROM cycletime_fast b WHERE b.costcenter='".$costcenter."' AND b.id_product='$product' LIMIT 1 ";
    $r_data = $this->db->query($q_data)->result();

    $mp = (!empty($r_data))?$r_data[0]->mp:0;
    $ct = (!empty($r_data))?$r_data[0]->cycletime:0;

		 echo json_encode(array(
				'no'			  => $no,
        'product'	  => $product,
        'stock'	    => $stock,
        'mp'	    => $mp,
        'ct'	    => $ct,
        'qty_order' => get_qty_order_so($product, $costcenter, $date_akhir, $date_awal),
        'shortages' => get_qty_order_so($product, $costcenter, $date_akhir, $date_awal) - $stock,
        'queue'	    => get_antrian_wip($product, $costcenter)
		 ));
	}

  public function get_maxdate(){
    $costcenter = $this->uri->segment(3);
    $date_now   = date('Y-m-d', strtotime(date('Y-m-d')));
    $q_max      = "SELECT MAx(date_akhir) AS date_akhir FROM produksi_planning WHERE costcenter='".$costcenter."' LIMIT 1 ";
    $max_date   = $this->db->query($q_max)->result();
    $datex = (!empty($max_date[0]->date_akhir))?$max_date[0]->date_akhir:$date_now;
    $date = date('Y-m-d', strtotime('+1 days', strtotime($datex)));
    // $date = date('Y-m-d', strtotime('2020-08-12'));
    $dateMax = date('Y-m-d', strtotime('+31 days', strtotime($datex)));

     echo json_encode(array(
        'min_date'	=> $date,
        'max_date' => $dateMax
     ));
  }

  public function print_plan(){
    $no_plan		= $this->uri->segment(3);

    $session 		= $this->session->userdata('app_session');
    $printby		= $session['id_user'];
    $koneksi		= akses_server();

    include 'plusPrint.php';
    $data_url		= base_url();
    $Split_Beda	= explode('/',$data_url);
    $Jum_Beda		= count($Split_Beda);
    $Nama_Beda	= $Split_Beda[$Jum_Beda - 2];
    // $okeH  			= $this->session->userdata("ses_username");

    history('Print produksi_planning '.$no_plan);

    print_planning_produksi($Nama_Beda, $no_plan, $koneksi, $printby);
  }

  public function print_plan_custom(){
		$costcenter		= $this->uri->segment(3);
    $tgl_awal	  	= $this->uri->segment(4);
    $tgl_akhir		= $this->uri->segment(5);
    $project		  = $this->uri->segment(6);

		$session 		= $this->session->userdata('app_session');
		$printby		= $session['id_user'];
		$koneksi		= akses_server();

		include 'plusPrint.php';
		$data_url		= base_url();
		$Split_Beda	= explode('/',$data_url);
		$Jum_Beda		= count($Split_Beda);
		$Nama_Beda	= $Split_Beda[$Jum_Beda - 2];
		// $okeH  			= $this->session->userdata("ses_username");

		history('Print produksi_planning '.$costcenter.'/'.$project.' date '.$tgl_awal.' to '.$tgl_akhir);

		print_planning_produksi_custom($Nama_Beda, $costcenter, $koneksi, $printby, $tgl_awal, $tgl_akhir, $project);
	}

  public function edit_production_planning(){
    if($this->input->post()){
      $Arr_Kembali	= array();
      $data			= $this->input->post();

      $session 		  = $this->session->userdata('app_session');
      $detail 	    = $data['detail'];
      $footer 	    = $data['footer'];
      $Ym					  = date('y');
      $no_plan      = $data['no_plan'];
      $no_planx     = $data['no_plan'];
      $costcenter   = $data['costcenter'];
      $date_akhir   = date('Y-m-d', strtotime($data['date_produksi_plan']));
      $date_awal    = $data['date_awal'];
      // print_r($detail);
      // print_r($footer);
      // exit;
      $created_by   = 'updated_by';
      $created_date = 'updated_date';
      $tanda        = 'Update ';
      if(empty($no_planx)){

        $srcMtr			  = "SELECT MAX(no_plan) as maxP FROM produksi_planning WHERE no_plan LIKE 'PR".$Ym."%' ";
        $numrowMtr		= $this->db->query($srcMtr)->num_rows();
        $resultMtr		= $this->db->query($srcMtr)->result_array();
        $angkaUrut2		= $resultMtr[0]['maxP'];
        $urutan2		  = (int)substr($angkaUrut2, 4, 4);
        $urutan2++;
        $urut2			  = sprintf('%04s',$urutan2);
        $no_plan	      = "PR".$Ym.$urut2;

        $created_by   = 'created_by';
        $created_date = 'created_date';
        $tanda        = 'Insert ';
      }

      $ArrHeader		= array(
        'no_plan'		    => $no_plan,
        'costcenter'	  => $costcenter,
        'date_awal'	    => $date_awal,
        'date_akhir'	  => $date_akhir,
        $created_by	    => $session['id_user'],
        $created_date	  => date('Y-m-d H:i:s')
      );



      $ArrDetail	= array();
      $ArrDetail2	= array();
      $nomor1 = 0;
      foreach($detail AS $val => $valx){ $nomor1++;
        $urut				= sprintf('%03s',$val);
        $nomor2 = 0;
        foreach($valx['data'] AS $val2 => $valx2){ $nomor2++;
          // echo $val."-".$val2."=".$valx2['product']."<br>";
          $ArrDetail[$nomor1."-".$nomor2]['no_plan'] 	      = $no_plan;
          $ArrDetail[$nomor1."-".$nomor2]['no_plan_detail']  = $no_plan."-".$urut;
          $ArrDetail[$nomor1."-".$nomor2]['date']          = $valx['date'];
          $ArrDetail[$nomor1."-".$nomor2]['product']       = $valx2['product'];
          $ArrDetail[$nomor1."-".$nomor2]['qty_order']     = $valx2['qty_order'];
          $ArrDetail[$nomor1."-".$nomor2]['stock']         = $valx2['stock'];
          $ArrDetail[$nomor1."-".$nomor2]['shortages']     = $valx2['shortages'];
          $ArrDetail[$nomor1."-".$nomor2]['queue']         = $valx2['queue'];
          $ArrDetail[$nomor1."-".$nomor2]['qty']           = $valx2['qty'];
          $ArrDetail[$nomor1."-".$nomor2]['man_power']     = $valx2['man_power'];
          $ArrDetail[$nomor1."-".$nomor2]['cycletime']     = $valx2['cycletime'];
          $ArrDetail[$nomor1."-".$nomor2]['mp_ct']         = $valx2['mp_ct'];
        }
      }

      // print_r($ArrHeader);
      // print_r($ArrDetail);
      // print_r($data);
      // exit;

      foreach($footer AS $val => $valx){
        $urut				= sprintf('%03s',$val);
        foreach($valx['man_minutes'] AS $val2 => $valx2){
          $ArrDetail2[$val.$val2]['no_plan'] 	      = $no_plan;
          $ArrDetail2[$val.$val2]['no_plan_detail'] = $no_plan."-".$urut;
          $ArrDetail2[$val.$val2]['date']           = $valx['date'];
          $ArrDetail2[$val.$val2]['category']       = $valx2['category'];
          $ArrDetail2[$val.$val2]['value']          = str_replace(',','',$valx2['value']);
        }
        foreach($valx['availability'] AS $val3 => $valx3){ $val2++;
          $ArrDetail2[$val.$val2]['no_plan'] 	      = $no_plan;
          $ArrDetail2[$val.$val2]['no_plan_detail'] = $no_plan."-".$urut;
          $ArrDetail2[$val.$val2]['date']           = $valx['date'];
          $ArrDetail2[$val.$val2]['category']       = $valx3['category'];
          $ArrDetail2[$val.$val2]['value']          = str_replace(',','',$valx3['value']);
        }
      }

      print_r($ArrHeader);
      print_r($ArrDetail);
      print_r($ArrDetail2);
      exit;

      $this->db->trans_start();
        if(empty($no_planx)){
          $this->db->delete('produksi_planning', array('no_plan' => $no_plan));
          $this->db->insert('produksi_planning', $ArrHeader);
        }
        if(!empty($no_planx)){
          $this->db->where('no_plan', $no_plan);
          $this->db->update('produksi_planning', $ArrHeader);
        }

        if(!empty($ArrDetail)){
          $this->db->delete('produksi_planning_data', array('no_plan' => $no_plan));
          $this->db->insert_batch('produksi_planning_data', $ArrDetail);
        }
        if(!empty($ArrDetail2)){
          $this->db->delete('produksi_planning_footer', array('no_plan' => $no_plan));
          $this->db->insert_batch('produksi_planning_footer', $ArrDetail2);
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
        history($tanda." Production Planning ".$no_plan);
      }

      echo json_encode($Arr_Data);
    }
    else{
      $session  = $this->session->userdata('app_session');
      $no_plan 	  = $this->uri->segment(3);
      $header   = $this->db->get_where('produksi_planning',array('no_plan' => $no_plan))->result();
      $plan    = $this->db->get('ms_costcenter')->result();
      // print_r($plan);
      // exit;
      $data = [
        'header' => $header,
        'plan' => $plan
      ];
      $this->template->set('results', $data);
      $this->template->title('Edit Production Planning');
      $this->template->page_icon('fa fa-edit');
      $this->template->render('edit_production_planning',$data);
    }
  }

  public function get_planning2(){
    $date_akhir = $this->uri->segment(3);
    $costcenter = $this->uri->segment(4);
    $date_awal 	= $this->uri->segment(5);
    $no_plan 	  = $this->uri->segment(6);

    $date_now   = date('Y-m-d', strtotime(date('Y-m-d')));

    $max_date   = $this->db->select_max('date_akhir')->limit(1)->get_where('produksi_planning', array('costcenter'=>$costcenter))->result();

    $datex      = (!empty($max_date[0]->date_akhir))?$max_date[0]->date_akhir:$date_now;
    $date       = date('Y-m-d', strtotime('+1 days', strtotime($datex)));
    if(!empty($date_awal)){
      $date       = date('Y-m-d', strtotime($date_awal));
    }

    $akhir      = new DateTime($date_akhir);
    $awal       = new DateTime($date);
    // echo $date; exit;
    $perbedaan  = $akhir->diff($awal);
    $colspan    = $perbedaan->days + 1;

    $rest_group = $this->db->select('id_product')->group_by('id_product')->get_where('cycletime_fast',array('costcenter'=>$costcenter))->result_array();

    $dtListArray = array();
				foreach($rest_group AS $val => $valx){
					$dtListArray[$val] = $valx['id_product'];
				}
				$dtImplode	= "('".implode("','", $dtListArray)."')";

    $product    = $this->db->query("SELECT * FROM produksi_planning_data WHERE no_plan = '".$no_plan."' ORDER BY product, `date`")->result_array();

    $product_date    = $this->db->query("SELECT delivery_date, no_so FROM sales_order_detail WHERE delivery_date BETWEEN '".$date."' AND '".$date_akhir."' GROUP BY no_so ORDER BY delivery_date ")->result_array();
    
    $d_Header = "<div class='box box-primary'>";
        $d_Header .= "<div class='box-body'>";
        $d_Header .= "<div class='tableFixHead' style='height:500px;'>";
        $d_Header .= "<table class='table table-bordered table-striped'>";
        $d_Header .= "<thead class='thead'>";
        $d_Header .= "<tr class='bg-blue'>";
          $d_Header .= "<th class='text-center th headcol' style='vertical-align:middle; width:500px !important; z-index: 99999;' rowspan='2'>Product</th>";
          foreach ($product_date as $key2x => $value2x) {
              $d_Header .= "<th class='text-center th long' style='vertical-align:middle;' rowspan='2' width='100px'>Delivery<br>".date('d M Y', strtotime($value2x['delivery_date']))."</th>";
          }
          $d_Header .= "<th class='text-center th long' style='vertical-align:middle;' rowspan='2' width='100px'>Total Propose</th>";
          $d_Header .= "<th class='text-center th long' style='vertical-align:middle;' rowspan='2' width='100px'>Stock</th>";
          $d_Header .= "<th class='text-center th long' style='vertical-align:middle;' rowspan='2' width='100px'>Shortages to Fulfill Propose</th>";
          $d_Header .= "<th class='text-center th long' style='vertical-align:middle;' rowspan='2' width='100px'>Queue</th>";
          $d_Header .= "<th class='text-center th long' style='vertical-align:middle;' rowspan='2' width='100px'>Balance</th>";
          $d_Header .= "<th class='text-center th' style='vertical-align:middle;' colspan='".$colspan."'>Production Planning Date</th>";
        $d_Header .= "</tr>";
        $siz = 65/$colspan;
        $cols_empty = $colspan + 5;
        $d_Header .= "<tr class='bg-blue'>";
          for ($a=0; $a<$colspan; $a++) {
              $loop_date  = date("l", strtotime("+".$a." day", strtotime($date)));
              $loop_date2 = date("Y-m-d", strtotime("+".$a." day", strtotime($date)));
              $loop_date3 = date("d-m-y", strtotime("+".$a." day", strtotime($date)));
              $d_Header .= "<th class='text-center th' style='font-size: 12px; vertical-align:middle;'>".$loop_date."<br>".$loop_date3."
                            <input type='hidden' name='detail[".$a."][date]' class='form-control text-right input-md' value='".$loop_date2."'>
                            <input type='hidden' name='footer[".$a."][date]' class='form-control text-right input-md' value='".$loop_date2."'>
                            </th>";
          }
        $d_Header .= "</tr>";

        $d_Header .= "</thead>";
        $d_Header .= "<tbody>";
          $product_val = '';
          foreach ($product as $key => $value) {
              $key = $value['product'];

              if($product_val != $value['product']){

                if($product_val != ''){
                  $d_Header .= "</tr>";
                }

                $r_data_stock = $this->db->select('qty_stock')->limit(1)->get_where('warehouse_product', array('costcenter'=>$costcenter,'id_product'=>$value['product'],'category'=>'order'))->result();
                $stock = (!empty($r_data_stock[0]->qty_stock))?$r_data_stock[0]->qty_stock:0;

                $r_data = $this->db->select('mp, cycletime')->limit(1)->get_where('cycletime_fast', array('costcenter'=>$costcenter,'id_product'=>$value['product']))->result();

                $mp = (!empty($r_data))?$r_data[0]->mp:0;
                $ct = (!empty($r_data))?$r_data[0]->cycletime:0;

                $d_Header .= "<tr class='header_".$key."'>";
                $d_Header .= "<td class='headcol'>".strtoupper(get_name('ms_inventory_category2','nama','id_category2',$value['product']))."</td>";
                $SUMD = 0;
                foreach ($product_date as $key2x => $value2x) {

                    $qty_order = $this->db->select('qty_order')->limit(1)->get_where('sales_order_detail', array('no_so'=>$value2x['no_so'],'delivery_date'=>$value2x['delivery_date'],'product'=>$value['product']))->result();

                    $qty_ = (!empty($qty_order[0]->qty_order))?$qty_order[0]->qty_order:0;

                    $SUMD += $qty_;
                    $d_Header .= "<td class='text-center long'>".$qty_."</td>";
                }
                $d_Header .= "<td class='text-center long'>".$SUMD."</td>";
                $sisa = $SUMD - $stock;
                $d_Header .= "<td class='text-center long'>".$stock."</td>";
                $d_Header .= "<td class='text-center long'>".$sisa."</td>";
                $d_Header .= "<td class='text-center long'>".get_antrian_wip($value['product'], $costcenter)."</td>";
                $d_Header .= "<td class='text-center long'><span id='balance_".$key."'>".$sisa."</span></td>";

                $a = -1;
              }
              //mulai looping tanggal qty
              $a++;
              // $a = $a - 1;

              $qty      = (!empty($value['qty']))?$value['qty']:'';
              $mpCT     = (!empty($value['mp_ct']))?$value['mp_ct']:0;
              $d_Header .= "<td class='text-center'>";
              $d_Header .= "<input type='text' id='qtyp_".$key."_".$a."' name='detail[".$a."][data][".$key."][qty]' style='min-width: 70px !important;' class='form-control text-center input-md maskM get_tot_ct bal_".$key."' value='".$qty."' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
              $d_Header .= "<input type='hidden' id='mp_".$key."_".$a."' name='detail[".$a."][data][".$key."][man_power]' class='form-control text-left input-md maskM' value='".$mp."' placeholder='CT' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
              $d_Header .= "<input type='hidden' id='ct_".$key."_".$a."' name='detail[".$a."][data][".$key."][cycletime]' class='form-control text-left input-md maskM' value='".$ct."' placeholder='MP' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
              $d_Header .= "<input type='hidden' id='tot_ct_".$key."_".$a."' name='detail[".$a."][data][".$key."][mp_ct]' class='form-control text-left input-md maskM tot_ct_".$a."' value='".$mpCT."' placeholder='CT*MP*QTY' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>
                            <input type='hidden' name='detail[".$a."][data][".$key."][product]' class='form-control text-center input-md maskM' readonly value='".$value['product']."'>
                            <input type='hidden' name='detail[".$a."][data][".$key."][qty_order]' class='form-control text-center input-md maskM' readonly value='".$SUMD."'>
                            <input type='hidden' name='detail[".$a."][data][".$key."][stock]' class='form-control text-center input-md maskM' readonly value='".$stock."'>
                            <input type='hidden' name='detail[".$a."][data][".$key."][shortages]' id='sh_".$key."' class='form-control text-center input-md maskM' readonly value='".$sisa."'>
                            <input type='hidden' name='detail[".$a."][data][".$key."][queue]' class='form-control text-center input-md maskM' readonly value='".get_antrian_wip($value['product'], $costcenter)."'>
                            </td>";

            $product_val = $value['product'];

          }
          $col = COUNT($product_date) + 5;

          $d_Header .= "<tr>";
            $d_Header .= "<td class='text-left'></td>";
            $d_Header .= "<td class='text-left' colspan='".$col."'><b>TOTAL MAN MINUTES</b></td>";
            for ($a=0; $a<$colspan; $a++) {
              $loop_date2 = date("Y-m-d", strtotime("+".$a." day", strtotime($date)));

              $rest_d = $this->db->select('value')->limit(1)->get_where('produksi_planning_footer', array('no_plan'=>$no_plan,'date'=>$loop_date2,'category'=>'man minutes'))->result();

              $value    = (!empty($rest_d[0]->value))?number_format($rest_d[0]->value):'';

              $d_Header .= "<td class='text-center'>";
              $d_Header .= "<input type='text' id='tot_ct_".$a."' name='footer[".$a."][man_minutes][".$a."][value]' value='".$value."' class='form-control text-right input-md maskM' readonly data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>
                            <input type='hidden' name='footer[".$a."][man_minutes][".$a."][category]' value='man minutes' class='form-control text-right input-md' readonly>
                            </td>";
            }
          $d_Header .= "</tr>";
          $d_Header .= "<tr>";
            $d_Header .= "<td class='text-left'><input type='text' readonly style='width: 200px; !important;border-color: transparent; background-color: transparent;'></td>";
            $d_Header .= "<td class='text-left' colspan='".$col."'><b>AVAILABILITY MAN MINUTES</b></td>";
            for ($a=0; $a<$colspan; $a++) {
              $loop_date = date("l", strtotime("+".$a." day", strtotime($date)));

              $get_mp = $this->db->select('mp_1, mp_2, mp_3, shift1, shift2, shift3')->limit(1)->get_where('ms_costcenter', array('id_costcenter'=>$costcenter))->result();

              $mpx1 = $get_mp[0]->mp_1;
              $mpx2 = $get_mp[0]->mp_2;
              $mpx3 = $get_mp[0]->mp_3;
              $shx1 = $get_mp[0]->shift1;
              $shx2 = $get_mp[0]->shift2;
              $shx3 = $get_mp[0]->shift3;

              $get_time1 = $this->db->select('b.id_day, b.start_work, b.start_break1, b.done_work, b.done_break1')->limit(1)->join('ms_hari c', 'b.id_day = c.id_hari', 'left')->get_where('ms_shift b', array('b.type_shift'=>'1','c.day_en'=>$loop_date))->result();
              $get_time2 = $this->db->select('b.id_day, b.start_work, b.start_break1, b.done_work, b.done_break1')->limit(1)->join('ms_hari c', 'b.id_day = c.id_hari', 'left')->get_where('ms_shift b', array('b.type_shift'=>'2','c.day_en'=>$loop_date))->result();
              $get_time3 = $this->db->select('b.id_day, b.start_work, b.start_break1, b.done_work, b.done_break1')->limit(1)->join('ms_hari c', 'b.id_day = c.id_hari', 'left')->get_where('ms_shift b', array('b.type_shift'=>'3','c.day_en'=>$loop_date))->result();

              $day = $get_time1[0]->id_day;

              if($shx1 == 'N'){
                $tm1 = 0;
              }else{
                $tm1 = 0;
                if($day <> 'Sat'){
                  if(!empty($get_time1)){
                    $sw   = date_create(get_24($get_time1[0]->start_work));
                    $sb1  = date_create(get_24($get_time1[0]->start_break1));
                    $tm1_1= date_diff($sw, $sb1);

                    $dw   = date_create(get_24($get_time1[0]->done_work));
                    $db1  = date_create(get_24($get_time1[0]->done_break1));
                    $tm1_2= date_diff($dw, $db1);

                    $tm1 = (($tm1_1->h + $tm1_2->h)+(($tm1_1->i + $tm1_2->i)/60)) * $mpx1;
                    $tm1x = (($tm1_1->h + $tm1_2->h)+(($tm1_1->i + $tm1_2->i)/60))."<br>";
                  }else{
                    $tm1 = 0;
                    $tm1x = (($tm1_1->h + $tm1_2->h)+(($tm1_1->i + $tm1_2->i)/60))."<br>";
                  }
                }

                if($day == 'Sat'){
                  if(!empty($get_time1)){
                    $sw   = date_create(get_24($get_time1[0]->start_work));
                    $dw   = date_create(get_24($get_time1[0]->done_work));
                    $tm1_1= date_diff($sw, $dw);

                    $tm1 = (($tm1_1->h)+(($tm1_1->i)/60)) * $mpx1;
                    $tm1x = (($tm1_1->h)+(($tm1_1->i)/60))."<br>";
                  }else{
                    $tm1 = 0;
                    $tm1x = (($tm1_1->h)+(($tm1_1->i)/60))."<br>";
                  }
                }
              }

              if($shx2 == 'N'){
                $tm2 = 0;
              }else{
                $tm2 = 0;
                if($day <> 'Sat'){
                  if(!empty($get_time2)){
                    $sw   = date_create(get_24($get_time2[0]->start_work));
                    $sb1  = date_create(get_24($get_time2[0]->start_break1));
                    $tm2_1= date_diff($sw, $sb1);

                    $dw   = date_create(get_24($get_time2[0]->done_work));
                    $db1  = date_create(get_24($get_time2[0]->done_break1));
                    $tm2_2= date_diff($dw, $db1);

                    $tm2 = (($tm2_1->h + $tm2_2->h)+(($tm2_1->i + $tm2_2->i)/60)) * $mpx2;
                    $tm2x = (($tm2_1->h + $tm2_2->h)+(($tm2_1->i + $tm2_2->i)/60))."<br>";
                  }else{
                    $tm2 = 0;
                    $tm2x = (($tm2_1->h + $tm2_2->h)+(($tm2_1->i + $tm2_2->i)/60))."<br>";
                  }
                }

                if($day == 'Sat'){
                  if(!empty($get_time2)){
                    $sw   = date_create(get_24($get_time2[0]->start_work));
                    $dw   = date_create(get_24($get_time2[0]->done_work));
                    $tm2_2= date_diff($sw, $dw);

                    $tm2 = (($tm2_2->h)+(($tm2_2->i)/60)) * $mpx2;
                    $tm2x = (($tm2_2->h)+(($tm2_2->i)/60))."<br>";
                  }else{
                    $tm2 = 0;
                    $tm2x = (($tm2_2->h)+(($tm2_2->i)/60))."<br>";
                  }
                }
              }

              if($shx3 == 'N'){
                $tm3 = 0;
                $tm3x = "<br>";
              }else{
                $tm3 = 0;
                if($day <> 'Sat'){
                  if(!empty($get_time3)){
                    $sw   = date_create($get_time3[0]->start_work);
                    $sb1  = date_create($get_time3[0]->start_break1);
                    $tm3_1= date_diff($sw, $sb1);

                    $dw   = date_create($get_time3[0]->done_work);
                    $db1  = date_create($get_time3[0]->done_break1);
                    $tm3_2= date_diff($dw, $db1);

                    $tm3 = (($tm3_1->h + $tm3_2->h)+(($tm3_1->i + $tm3_2->i)/60)) * $mpx1;;
                    $tm3x = (($tm3_1->h + $tm3_2->h)+(($tm3_1->i + $tm3_2->i)/60))."<br>";
                  }else{
                    $tm3 = 0;
                    $tm3x = (($tm3_1->h + $tm3_2->h)+(($tm3_1->i + $tm3_2->i)/60))."<br>";
                  }
                }

                if($day == 'Sat'){
                  if(!empty($get_time3)){
                    $sw   = date_create($get_time3[0]->start_work);
                    $dw   = date_create($get_time3[0]->done_work);
                    $tm3_2= date_diff($sw, $dw);

                    $tm3 = (($tm3_2->h)+(($tm3_2->i)/60)) * $mpx1;;
                    $tm3x = (($tm3_2->h)+(($tm3_2->i)/60))."<br>";
                  }else{
                    $tm3 = 0;
                    $tm3x = (($tm3_2->h)+(($tm3_2->i)/60))."<br>";
                  }
                }
              }

              $m_shift = floatval($tm1) + floatval($tm2) + floatval($tm3);
              $pengali = $m_shift * 60 * (93/100);
              $tanda = "";
              // $tanda = $tm1."_".$tm2."_".$tm3."<br>".$m_shift."<br>".$pengali;

              $tanda2 = "";
              // $tanda2 = $tm1x.$tm2x.$tm3x;
              $d_Header .= "<td class='text-center'>".$tanda2.$tanda;
              $d_Header .= "<input type='text' id='man_ct_".$a."' name='footer[".$a."][availability][".$a."][value]' value='".number_format($pengali)."' class='form-control text-right input-md maskM' readonly>
                            <input type='hidden' name='footer[".$a."][availability][".$a."][category]' value='availability' class='form-control text-right input-md' readonly>
                            </td>";
            }
          $d_Header .= "</tr>";
          // if(empty($product)){
          //   $d_Header .= "<tr>";
          //   $d_Header .= "<td class='text-left' colspan='".$cols_empty."'>Not Found Data ...</td>";
          //   $d_Header .= "</tr>";
          // }
        $d_Header .= "</tbody>";
        $d_Header .= "</table>";
        $d_Header .= "</div>";
        $d_Header .= "</div>";
    $d_Header .= "</div>";


     echo json_encode(array(
        'header'			=> $d_Header,
        'total' => $colspan
     ));
  }


  //========================================================================================================
  //============================================SPK=========================================================
  //========================================================================================================

  public function spk(){
    $this->auth->restrict($this->viewPermission);
    $session  = $this->session->userdata('app_session');
    $this->template->page_icon('fa fa-users');
    history("View index SPK Produksi");
    $this->template->title('SPK Produksi');
    $this->template->render('spk');
  }

  //========================================================================================================
  //=====================================LOADING VS CAPACITY================================================
  //========================================================================================================

  public function load_vc_cap(){
    // $this->auth->restrict($this->viewPermission);
    $session = $this->session->userdata('app_session');
    $this->template->page_icon('fa fa-users');
    history("View index loading vs capacity");
    $this->template->title('Loading VS Capacity');
    $this->template->render('load_vc_cap');
  }

  public function get_capacity(){
    $no_so        = $this->uri->segment(3);
    $date_awal    = $this->uri->segment(4);
    $date_akhir 	= $this->uri->segment(5);


    $d_Header = "<div class='box box-primary'>";
        $d_Header .= "<div class='box-body'>";
        $d_Header .= "<table class='table table-bordered table-striped'>";
        $d_Header .= "<thead class='thead'>";
        $d_Header .= "<tr class='bg-blue'>";
          $d_Header .= "<th width='20%' class='text-center' rowspan='2' style='vertical-align:middle;'>COSTCENTER</th>";
          $d_Header .= "<th width='20%' class='text-center' colspan='2'>TOTAL WAKTU KAPASITAS</th>";
          $d_Header .= "<th width='20%' class='text-center' colspan='2'>TOTAL WAKTU UNTUK FULLFILL REQUEST</th>";
          $d_Header .= "<th width='20%' class='text-center' colspan='2'>TOTAL WAKTU UNTUK FULLFILL PROPOSE</th>";
          $d_Header .= "<th width='20%' class='text-center' colspan='2'>TOTAL WAKTU UNTUK FULLFILL BALANCE</th>";
        $d_Header .= "</tr>";
        $d_Header .= "<tr class='bg-blue'>";
          $d_Header .= "<th class='text-center'>Day</th>";
          $d_Header .= "<th class='text-center'>Minutes</th>";
          $d_Header .= "<th class='text-center'>Day</th>";
          $d_Header .= "<th class='text-center'>Minutes</th>";
          $d_Header .= "<th class='text-center'>Day</th>";
          $d_Header .= "<th class='text-center'>Minutes</th>";
          $d_Header .= "<th class='text-center'>Day</th>";
          $d_Header .= "<th class='text-center'>Minutes</th>";
        $d_Header .= "</tr>";

        $d_Header .= "</thead>";
        $d_Header .= "<tbody>";
        $date       = date('Y-m-d', strtotime($date_awal));

        $akhir      = new DateTime($date_akhir);
        $awal       = new DateTime($date);
        // echo $date; exit;
        $perbedaan  = $akhir->diff($awal);
        $colspan    = $perbedaan->d + 1;
        $totalSUM_waktu = 0;
        $totalSUM_req = 0;
        $totalSUM_pro = 0;
        $totalSUM_bal = 0;
        // $sql_so     = "SELECT * FROM sales_order_detail WHERE no_so='".$no_so."' ";
        // $rest_so    = $this->db->query($sql_so)->result_array();
        foreach (get_costcenter() as $key => $value) {
          $tot_waktu = 0;
          for ($a=0; $a<$colspan; $a++){
            $loop_date = date("l", strtotime("+".$a." day", strtotime($date)));

            $get_mp  = $this->db->query("SELECT b.* FROM ms_costcenter b WHERE b.id_costcenter='".$value['id_costcenter']."' LIMIT 1 ")->result();
            $mp1     = ($get_mp[0]->shift1 == 'Y')?$get_mp[0]->mp_1:0;
            $mp2     = ($get_mp[0]->shift2 == 'Y')?$get_mp[0]->mp_2:0;
            $mp3     = ($get_mp[0]->shift3 == 'Y')?$get_mp[0]->mp_3:0;


            $shx1 = $get_mp[0]->shift1;
            $shx2 = $get_mp[0]->shift2;
            $shx3 = $get_mp[0]->shift3;

            $get_time1 = $this->db->query("SELECT b.* FROM ms_shift b LEFT JOIN ms_hari c ON b.id_day=c.id_hari WHERE b.type_shift='1' AND c.day_en='".$loop_date."' LIMIT 1 ")->result();
            $get_time2 = $this->db->query("SELECT b.* FROM ms_shift b LEFT JOIN ms_hari c ON b.id_day=c.id_hari WHERE b.type_shift='2' AND c.day_en='".$loop_date."' LIMIT 1 ")->result();
            $get_time3 = $this->db->query("SELECT b.* FROM ms_shift b LEFT JOIN ms_hari c ON b.id_day=c.id_hari WHERE b.type_shift='3' AND c.day_en='".$loop_date."' LIMIT 1 ")->result();
            $day = $get_time1[0]->id_day;

            if($shx1 == 'N'){
              $tm1 = 0;
            }else{
              $tm1 = 0;
              if($day <> 'Sat'){
                if(!empty($get_time1)){
                  $sw   = date_create(get_24($get_time1[0]->start_work));
                  $sb1  = date_create(get_24($get_time1[0]->start_break1));
                  $tm1_1= date_diff($sw, $sb1);

                  $dw   = date_create(get_24($get_time1[0]->done_work));
                  $db1  = date_create(get_24($get_time1[0]->done_break1));
                  $tm1_2= date_diff($dw, $db1);

                  $tm1 = ((($tm1_1->h + $tm1_2->h) * 60) + ($tm1_1->i + $tm1_2->i));
                }else{
                  $tm1 = 0;
                }
              }

              if($day == 'Sat'){
                if(!empty($get_time1)){
                  $sw   = date_create(get_24($get_time1[0]->start_work));
                  $dw   = date_create(get_24($get_time1[0]->done_work));
                  $tm1_1= date_diff($sw, $dw);

                  $tm1 = (($tm1_1->h * 60) + ($tm1_1->i));
                }else{
                  $tm1 = 0;
                }
              }
            }

            if($shx2 == 'N'){
              $tm2 = 0;
            }else{
              $tm2 = 0;
              if($day <> 'Sat'){
                if(!empty($get_time2)){
                  $sw   = date_create(get_24($get_time2[0]->start_work));
                  $sb1  = date_create(get_24($get_time2[0]->start_break1));
                  $tm2_1= date_diff($sw, $sb1);

                  $dw   = date_create(get_24($get_time2[0]->done_work));
                  $db1  = date_create(get_24($get_time2[0]->done_break1));
                  $tm2_2= date_diff($dw, $db1);

                  $tm2 = ((($tm2_1->h + $tm2_2->h) * 60) + ($tm2_1->i + $tm2_2->i));
                }else{
                  $tm2 = 0;
                }
              }

              if($day == 'Sat'){
                if(!empty($get_time2)){
                  $sw   = date_create(get_24($get_time2[0]->start_work));
                  $dw   = date_create(get_24($get_time2[0]->done_work));
                  $tm2_2= date_diff($sw, $dw);

                  $tm2 = (($tm2_2->h * 60) + ($tm2_2->i));
                }else{
                  $tm2 = 0;
                }
              }
            }

            if($shx3 == 'N'){
              $tm3 = 0;
              $tm3x = "<br>";
            }else{
              $tm3 = 0;
              if($day <> 'Sat'){
                if(!empty($get_time3)){
                  $sw   = date_create($get_time3[0]->start_work);
                  $sb1  = date_create($get_time3[0]->start_break1);
                  $tm3_1= date_diff($sw, $sb1);

                  $dw   = date_create($get_time3[0]->done_work);
                  $db1  = date_create($get_time3[0]->done_break1);
                  $tm3_2= date_diff($dw, $db1);

                  $tm3 = ((($tm3_1->h + $tm3_2->h) * 60) + ($tm3_1->i + $tm3_2->i));
                }else{
                  $tm3 = 0;
                }
              }

              if($day == 'Sat'){
                if(!empty($get_time3)){
                  $sw   = date_create($get_time3[0]->start_work);
                  $dw   = date_create($get_time3[0]->done_work);
                  $tm3_2= date_diff($sw, $dw);

                  $tm3 = (($tm3_2->h * 60) + ($tm3_2->i));
                }else{
                  $tm3 = 0;
                }
              }
            }

            $sum_mp  = $mp1 + $mp2 + $mp3;
            $m_shift = floatval($tm1) + floatval($tm2) + floatval($tm3);
            $TOT_ = (floatval($tm1) * $mp1) + (floatval($tm2) * $mp2) + (floatval($tm3) * $mp3);
            $tot_waktu += $TOT_ * (93/100);
          }


          // foreach($rest_so AS $val => $valx){
            $tot_CT_order   = 0;
            $tot_CT_propose = 0;
            $tot_CT_balance = 0;
            $sql_ct = " SELECT
                          b.man_hours,
                          a.qty_order,
                          ((b.man_hours * a.qty_propose)) AS man_h_pro,
                          ((b.man_hours * a.qty_order)) AS man_h_ord,
                          (SELECT c.qty_stock FROM warehouse_product c WHERE c.category='order' AND c.id_product=a.product LIMIT 1) AS stock
                        FROM sales_order_detail a
                          LEFT JOIN cycletime_full b ON a.product=b.id_product
                        WHERE
                          a.no_so='".$no_so."'
                          AND a.product=b.id_product
                          AND b.id_costcenter='".$value['id_costcenter']."'
                          ";
            $get_ct = $this->db->query($sql_ct)->result_array();
            foreach($get_ct AS $val2 => $valx2){
              $tot_CT_order += $valx2['man_h_pro'];
              $tot_CT_propose += $valx2['man_h_ord'];
              $tot_CT_balance += ($valx2['qty_order'] - $valx2['stock']) * $valx2['man_hours'];
            }
          // }
          $totalSUM_waktu += $tot_waktu;
          $totalSUM_req += $tot_CT_order;
          $totalSUM_pro += $tot_CT_propose;
          $totalSUM_bal += $tot_CT_balance;

          $TOT1 = 0;
          $TOT2 = 0;
          $TOT3 = 0;
          if($tot_waktu <> '0' OR $tot_waktu <> ''){
            $TOT1 = $tot_CT_order / $tot_waktu;
            $TOT2 = $tot_CT_propose / $tot_waktu;
            $TOT3 = $tot_CT_balance / $tot_waktu;
          }

          $d_Header .= "<tr>";
            $d_Header .= "<td>".strtoupper($value['nama_costcenter'])."</td>";
            $d_Header .= "<td width='10%' align='center'>".$colspan."</td>";
            $d_Header .= "<td width='10%' align='right' style='padding-right:50px;'>".number_format($tot_waktu)."</td>";
            $d_Header .= "<td width='10%' align='center'>".number_format($TOT1,1)."</td>";
            $d_Header .= "<td width='10%' align='right' style='padding-right:50px;'>".number_format($tot_CT_order)."</td>";
            $d_Header .= "<td width='10%' align='center'>".number_format($TOT2,1)."</td>";
            $d_Header .= "<td width='10%' align='right' style='padding-right:50px;'>".number_format($tot_CT_propose)."</td>";
            $d_Header .= "<td width='10%' align='center'>".number_format($TOT3,1)."</td>";
            $d_Header .= "<td width='10%' align='right' style='padding-right:50px;'>".number_format($tot_CT_balance)."</td>";
          $d_Header .= "</tr>";
        }
        $d_Header .= "<tr>";
          $d_Header .= "<td align='right'><b>TOTAL</b></td>";
          $d_Header .= "<td></td>";
          $d_Header .= "<td align='right' style='padding-right:50px;'><b>".number_format($totalSUM_waktu)."</b></td>";
          $d_Header .= "<td></td>";
          $d_Header .= "<td align='right' style='padding-right:50px;'><b>".number_format($totalSUM_req)."</b></td>";
          $d_Header .= "<td></td>";
          $d_Header .= "<td align='right' style='padding-right:50px;'><b>".number_format($totalSUM_pro)."</b></td>";
          $d_Header .= "<td></td>";
          $d_Header .= "<td align='right' style='padding-right:50px;'><b>".number_format($totalSUM_bal)."</b></td>";
        $d_Header .= "</tr>";
        $d_Header .= "<tr>";
          $d_Header .= "<td align='right'><b>KAPASITAS MAN POWER</b></td>";
          $d_Header .= "<td colspan='7'></td>";
          $d_Header .= "<td align='right' style='padding-right:50px;'><b>".number_format(($totalSUM_bal/$totalSUM_waktu)*100)." %</b></td>";
        $d_Header .= "</tr>";

        $d_Header .= "</tbody>";
        $d_Header .= "</table>";
        $d_Header .= "</div>";
    $d_Header .= "</div>";


     echo json_encode(array(
        'header'			=> $d_Header
     ));
  }

  function insert_select_double_daycode(){
    $session 		= $this->session->userdata('app_session');
		$printby		= $session['id_user'];
		history('Try insert select table daycode double');

    // $sql_day = "SELECT
    //             	a.id_product AS id_product,
    //             	b.id_costcenter AS id_costcenter,
    //             	a.code AS code,
    //             	count( a.id ) AS jumlah_double
    //             FROM
    //             	( report_produksi_daily_detail a LEFT JOIN report_produksi_daily_header b ON ( ( a.id_produksi_h = b.id_produksi_h ) ) )
    //             WHERE
    //             	a.sts_daycode = 'N'
    //             GROUP BY
    //             	a.code,
    //             	a.id_product,
    //             	b.id_costcenter
    //             HAVING COUNT( a.id ) > 1";
    // $restUpdate = $this->db->query($sql_day)->result_array();
    $restUpdate = $this->db->select('id_product, id_costcenter, code, jumlah_double ')->get_where('cek_count_double_daycode', array('jumlah_double > '=>'1'))->result_array();

		$ArrUpdate = array();
		foreach($restUpdate AS $val => $valx){
			$ArrUpdate[$val]['id_product'] 			= $valx['id_product'];
			$ArrUpdate[$val]['id_costcenter'] 	= $valx['id_costcenter'];
			$ArrUpdate[$val]['code'] 	          = $valx['code'];
			$ArrUpdate[$val]['jumlah_double'] 	= $valx['jumlah_double'];
			$ArrUpdate[$val]['created_by'] 		= $printby;
			$ArrUpdate[$val]['created_date'] 	= date('Y-m-d H:i:s');
		}

		// print_r($ArrUpdate);
		// exit;
		$this->db->trans_start();
			$this->db->truncate('table_count_daycode');
			if(!empty($ArrUpdate)){
				$this->db->insert_batch('table_count_daycode', $ArrUpdate);
			}
		$this->db->trans_complete();

		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=>'Update Failed. Please try again later ...',
				'status'	=> 0
			);
		}
		else{
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=>'Update Success. Thanks ...',
				'status'	=> 1
			);
			history('Success insert select table daycode double');
		}
		// echo json_encode($Arr_Data);

	}

  //========================================================================================================
  //======================================DELETE DOUBLE=====================================================
  //========================================================================================================

  public function delete_double(){
    $session = $this->session->userdata('app_session');
    $this->template->page_icon('fa fa-users');

    $sql = "SELECT * FROM cek_daycode_double WHERE jumlah_double > 1";
    $rest = $this->db->query($sql)->result_array();

    $data = array(
      'double_daycode' => $rest
    );

    history("View index report produksi (delete double)");
    $this->template->title('Report Produksi');
    $this->template->render('delete_double', $data);
  }

  public function data_side_delete_double(){
    $this->produksi_model->get_data_json_delete_double();
  }

  public function delete_double_modal(){
    $this->produksi_model->delete_double_modal();
  }

  public function delete_check_daycode(){
    $this->produksi_model->delete_check_daycode();
  }

  public function delete_daycode_double_qc_new(){
  	$Arr_Kembali	= array();
		$data			    = $this->input->post();
		$session      = $this->session->userdata('app_session');
    $id	          = $data['id'];

    $ArrHeader		  = array(
      'sts_daycode'	  => "Y",
      'remarks'       => 'delete daycode double melewati qc',
      'delivery_code' => 'delete daycode double melewati qc',
      'delivery_by'	  => $session['id_user'],
      'delivery_date'	=> date('Y-m-d H:i:s')
    );

		$this->db->trans_start();
      $this->db->where('id', $id);
			$this->db->delete('report_produksi_daily_detail');
		$this->db->trans_complete();

		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=>'Delete gagal disimpan ...',
				'status'	=> 0
			);
		}
		else{
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=>'Delete berhasil disimpan. Thanks ...',
				'status'	=> 1
			);
      history("Delete daycode double melewati QC new ".$id);
		}

		echo json_encode($Arr_Data);
	}
	
	public function delete_daycode_double_lewat(){
		$Arr_Kembali	= array();
		$data			= $this->input->post();
		$session      = $this->session->userdata('app_session');
		$id	          = $data['id'];

		$this->db->trans_start();
			$this->db->where('id', $id);
			$this->db->delete('report_produksi_daily_detail');
		$this->db->trans_complete();

		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=>'Delete gagal disimpan ...',
				'status'	=> 0
			);
		}
		else{
			$this->db->trans_commit();
			$rest = $this->db->get_where('cek_daycode_double_fast',array('jumlah_double > '=>1))->result_array();
			$NEWLABEL = "";
			
			$NEWLABEL .= "<label style='color:green;'>Check Double Daycode :<br></label>";
		
			if(!empty($rest)){
			  foreach($rest AS $val => $valx){ $val++;
				$jml_doub = $valx['jumlah_double'] - 1;
				$ketX = ($valx['ket'] == 'good')?"<span style='color:red;'>SUDAH MELAWATI QC</span>":"<span style='color:green;'>BELUM MELEWATI QC</span>";
				$NEWLABEL .= "<span style='color:red;'><b><br>".$val.") ".strtoupper(get_name('ms_costcenter', 'nama_costcenter', 'id_costcenter', $valx['id_costcenter'])).", ".strtoupper(get_name('ms_inventory_category2', 'nama', 'id_category2', $valx['id_product']))." [".$valx['daycode']."], JUMLAH DOUBLE ".$jml_doub." ".$ketX."</b> <button type='button' id='deldaycode' data-id='".$valx['id']."'>Hapus</button></span>";
			  }
			}
			else{
			  $NEWLABEL .= "<br><label style='color:green;'><u>TIDAK ADA DAYCODE YANG DOUBLE.</u></label>";
			}
	  
			$Arr_Data	= array(
				'pesan'		=>'Delete berhasil disimpan. Thanks ...',
				'status'	=> 1,
				'new_label' => $NEWLABEL
			);
			history("Delete daycode double melewati QC by alert ".$id);
		}

		echo json_encode($Arr_Data);
	}

  //========================================================================================================
  //======================================CHECKED PRODUCT=====================================================
  //========================================================================================================

  public function checked_product(){
    $session = $this->session->userdata('app_session');
    $this->template->page_icon('fa fa-users');
    $rest = $this->db->get_where('ms_inventory_category2', array('deleted'=>'0'))->result_array();

    $data = array(
      'check_p' => $rest
    );

    history("View index checked produksi input");
    $this->template->title('Checked product input');
    $this->template->render('checked_product', $data);
  }

  public function upd_checked_product(){
    $data 			= $this->input->post();
		$id			    = $data['id'];
		$ArrInsertH = array(
			'ck_produksi' 	=> 'Y'
		);

		$this->db->trans_start();
      $this->db->where('id_category2', $id);
      $this->db->update('ms_inventory_category2', $ArrInsertH);
		$this->db->trans_complete();

    history("Checked input produksi ".$id);
	}

  public function upd_unchecked_product(){
    $data 			= $this->input->post();
		$id			    = $data['id'];
		$ArrInsertH = array(
			'ck_produksi' 	=> 'N'
		);

		$this->db->trans_start();
      $this->db->where('id_category2', $id);
      $this->db->update('ms_inventory_category2', $ArrInsertH);
		$this->db->trans_complete();

    history("Un-checked input produksi ".$id);
	}

}
?>
