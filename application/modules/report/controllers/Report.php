<?php if (!defined('BASEPATH')) { exit('No direct script access allowed');}

class Report extends Admin_Controller{

    protected $viewPermission = 'Report_Produksi.View';
    protected $addPermission = 'Report_Produksi.Add';
    protected $managePermission = 'Report_Produksi.Manage';
    protected $deletePermission = 'Report_Produksi.Delete';

    public function __construct(){
        parent::__construct();

        // $this->load->library(array('Mpdf'));

        date_default_timezone_set('Asia/Bangkok');
        $this->template->page_icon('fa fa-table');
    }

    public function index(){
      $session = $this->session->userdata('app_session');
      $this->template->page_icon('fa fa-users');
      $this->template->title('Report Produksi');
      $this->template->render('index');
    }

    public function productivity(){
      $session = $this->session->userdata('app_session');
      $this->template->page_icon('fa fa-users');
      $this->template->title('Productivity');
      $this->template->render('productivity');
    }

    public function get_planning(){
      $sales_order = $this->uri->segment(3);
      // echo $perbedaan->d;
      // $product    = $this->db->query("SELECT a.* FROM sales_order_detail a WHERE a.no_so = '".$sales_order."' ORDER BY a.product")->result_array();
      $product    = $this->db->query("SELECT a.id_category2 AS product FROM ms_inventory_category2 a ORDER BY a.id_category2")->result_array();

        $d_Header = "<div class='box box-primary'>";
          $d_Header .= "<div class='box-body'>";
          $d_Header .= "<div class='tableFixHead' style='height:500px;'>";
          $d_Header .= "<table class='table table-bordered table-striped'>";
          $d_Header .= "<thead class='thead'>";
          $d_Header .= "<tr class='bg-blue'>";
          $d_Header .= "<th class='text-center th headcol' style='vertical-align:middle; z-index: 99999;' width='10%'>Project</th>";
          $d_Header .= "<th class='text-center th headcol' style='vertical-align:middle; z-index: 99999'>Product</th>";
          $d_Header .= "<th class='text-center th' style='vertical-align:middle;' width='4%'>Qty Requirement</th>";
          $d_Header .= "<th class='text-center th' style='vertical-align:middle;' width='4%'>Qty Propose</th>";

          foreach (get_costcenter_report() as $key2 => $value2){
            $d_Header .= "<th class='text-center th' style='vertical-align:middle;' width='4%'>".strtoupper($value2['nama_costcenter'])."</th>";
          }
          $d_Header .= "<th class='text-center th' style='vertical-align:middle;' width='4%'>Finish Good</th>";
          $d_Header .= "<th class='text-center th' style='vertical-align:middle;' width='4%'>Finish Good Over</th>";
          $d_Header .= "<th class='text-center th' style='vertical-align:middle;' width='4%'>Finish Good Balance</th>";


          $d_Header .= "<th class='text-center th' style='vertical-align:middle;' width='4%'>FG + WIP</th>";
          $d_Header .= "<th class='text-center th' style='vertical-align:middle;' width='4%'>Total All Stock<br>(Lam + WIP + FG)</th>";
          $d_Header .= "<th class='text-center th' style='vertical-align:middle;' width='4%'>Balance All Stock</th>";

          $d_Header .= "</thead>";
          $d_Header .= "<tbody>";
            $SUM_4 = 0;
            $SUM_5 = 0;
            $SUM_6 = 0;
            $SUM_7 = 0;
            $SUM_8 = 0;
            $SUM_9 = 0;
            $SUM_10 = 0;
            $SUM_11 = 0;
            $SUM_12 = 0;
            $SUM_13 = 0;
            $SUM_FG_OVER = 0;
            $SUM_14 = 0;
            $SUM_15 = 0;
            $SUM_16 = 0;
            $SUM_17 = 0;
            $SUM_18 = 0;
            $SUM_19 = 0;
            foreach ($product as $key => $value) { $key++;
              $productX    = $this->db->query("SELECT a.* FROM sales_order_detail a WHERE a.no_so = '".$sales_order."' AND a.product='".$value['product']."' LIMIT 1")->result_array();
              $qty_order = (!empty($productX[0]['qty_order']))?$productX[0]['qty_order']:0;
              $qty_propose = (!empty($productX[0]['qty_propose']))?$productX[0]['qty_propose']:0;

              $query2	 = $this->db->query("SELECT qty_stock FROM warehouse_product WHERE id_product='".$value['product']."' AND category = 'product' LIMIT 1 ")->result();
              $qty2_ = (!empty($query2[0]->qty_stock))?$query2[0]->qty_stock:0;
              $lastcost = get_last_costcenter_warehouse($value['product']);

              $query2_wip	 = $this->db->query("SELECT SUM(a.qty_stock) AS qty_stock FROM warehouse_product a LEFT JOIN ms_costcenter b ON a.costcenter=b.id_costcenter WHERE a.id_product='".$value['product']."' AND a.costcenter <> '".$lastcost."' AND a.category = 'order' AND a.costcenter <> 'CC2000012' AND b.urut <> '0'")->result();
              $qty2_wip = (!empty($query2_wip[0]->qty_stock))?$query2_wip[0]->qty_stock:0;

              $query2_lam	 = $this->db->query("SELECT a.qty_stock AS qty_stock FROM warehouse_product a LEFT JOIN ms_costcenter b ON a.costcenter=b.id_costcenter WHERE a.id_product='".$value['product']."' AND a.category = 'order' AND a.costcenter = 'CC2000012' AND b.urut <> '0'")->result();
              $qty2_lam = (!empty($query2_lam[0]->qty_stock))?$query2_lam[0]->qty_stock:0;

              $d_Header .= "<tr>";
                $d_Header .= "<td class='headcol'>".strtoupper(get_project_name($value['product']))."</td>";
                $d_Header .= "<td class='headcol'>".strtoupper(get_name('ms_inventory_category2','nama','id_category2',$value['product']))."</td>";

                $d_Header .= "<td class='text-center'>".$qty_propose."</td>";
                $d_Header .= "<td class='text-center'>".$qty_order."</td>";
                foreach (get_costcenter_report() as $key2 => $value2){
                  $query	 = $this->db->query("SELECT qty_stock FROM warehouse_product WHERE id_product='".$value['product']."' AND costcenter='".$value2['id_costcenter']."' AND category = 'order' LIMIT 1 ")->result();
                  $qty_ = (!empty($query[0]->qty_stock))?$query[0]->qty_stock:0;
                  if(get_last_costcenter_warehouse($value['product']) == $value2['id_costcenter']){
                    $qty_ = 0;
                  }

                  if($value2['id_costcenter'] == 'CC2000003'){
                    $query2	 = $this->db->query("SELECT COUNT(*) AS qty_stock FROM report_produksi_daily_detail WHERE id_product='".$value['product']."' AND ket='not yet'")->result();
                    $qty_ = (!empty($query2[0]->qty_stock))?$query2[0]->qty_stock:0;
                  }

                  $d_Header .= "<td class='text-center'>".$qty_."</td>";
                }

                $q5	 = $this->db->query("SELECT qty_stock FROM warehouse_product WHERE id_product='".$value['product']."' AND costcenter='CC2000012' AND category = 'order' LIMIT 1 ")->result();
                $qty_5 = (!empty($q5[0]->qty_stock))?$q5[0]->qty_stock:0;
                $q6	 = $this->db->query("SELECT qty_stock FROM warehouse_product WHERE id_product='".$value['product']."' AND costcenter='CC2000011' AND category = 'order' LIMIT 1 ")->result();
                $qty_6 = (!empty($q6[0]->qty_stock))?$q6[0]->qty_stock:0;
                $q7	 = $this->db->query("SELECT qty_stock FROM warehouse_product WHERE id_product='".$value['product']."' AND costcenter='CC2000007' AND category = 'order' LIMIT 1 ")->result();
                $qty_7 = (!empty($q7[0]->qty_stock))?$q7[0]->qty_stock:0;
                $q8	 = $this->db->query("SELECT qty_stock FROM warehouse_product WHERE id_product='".$value['product']."' AND costcenter='CC2000006' AND category = 'order' LIMIT 1 ")->result();
                $qty_8 = (!empty($q8[0]->qty_stock))?$q8[0]->qty_stock:0;
                $q9	 = $this->db->query("SELECT qty_stock FROM warehouse_product WHERE id_product='".$value['product']."' AND costcenter='CC2000009' AND category = 'order' LIMIT 1 ")->result();
                $qty_9 = (!empty($q9[0]->qty_stock))?$q9[0]->qty_stock:0;
                $q10	 = $this->db->query("SELECT qty_stock FROM warehouse_product WHERE id_product='".$value['product']."' AND costcenter='CC2000010' AND category = 'order' LIMIT 1 ")->result();
                $qty_10 = (!empty($q10[0]->qty_stock))?$q10[0]->qty_stock:0;
                $q11	 = $this->db->query("SELECT qty_stock FROM warehouse_product WHERE id_product='".$value['product']."' AND costcenter='CC2000005' AND category = 'order' LIMIT 1 ")->result();
                $qty_11 = (!empty($q11[0]->qty_stock))?$q11[0]->qty_stock:0;
                $q12	 = $this->db->query("SELECT qty_stock FROM warehouse_product WHERE id_product='".$value['product']."' AND costcenter='CC2000004' AND category = 'order' LIMIT 1 ")->result();
                $qty_12 = (!empty($q12[0]->qty_stock))?$q12[0]->qty_stock:0;
                $q18	 = $this->db->query("SELECT COUNT(*) AS qty_stock FROM report_produksi_daily_detail WHERE id_product='".$value['product']."' AND ket='not yet'")->result();
                $qty_18 = (!empty($q18[0]->qty_stock))?$q18[0]->qty_stock:0;

                if(get_last_costcenter_warehouse($value['product']) == 'CC2000010'){
                  $qty_10 = 0;
                }
                if(get_last_costcenter_warehouse($value['product']) == 'CC2000005'){
                  $qty_11 = 0;
                }
                if(get_last_costcenter_warehouse($value['product']) == 'CC2000004'){
                  $qty_12 = 0;
                }

                $SUM_5 += $qty_5;
                $SUM_6 += $qty_6;
                $SUM_7 += $qty_7;
                $SUM_8 += $qty_8;
                $SUM_9 += $qty_9;
                $SUM_10 += $qty_10;
                $SUM_11 += $qty_11;
                $SUM_12 += $qty_12;

                $SUM_18 += $qty_18;


                $b_WIP = $qty2_ + $qty2_wip;
                $b_All = $qty2_ + $qty2_wip + $qty2_lam;
                $b_AlSt = $b_All - $qty_order;

                $b_FG_OVER = $qty2_ - $qty_order;
                if($qty2_ - $qty_order < 0){
                  $b_FG_OVER = 0;
                }

                //jika finishgood
                $b_FINISH_GOOD = $qty2_;
                if($qty2_ > $qty_order){
                  $b_FINISH_GOOD = $qty_order;
                }


                $b_BALANCE = 0;
                if($b_FINISH_GOOD < $qty_order){
                  $b_BALANCE = $b_FINISH_GOOD - $qty_order;
                }

                $d_Header .= "<td class='text-center'>".$b_FINISH_GOOD."</td>";
                $d_Header .= "<td class='text-center'>".$b_FG_OVER."</td>";
                $d_Header .= "<td class='text-center'>".$b_BALANCE."</td>";
                $d_Header .= "<td class='text-center'>".$b_WIP."</td>";
                $d_Header .= "<td class='text-center'>".$b_All."</td>";
                $d_Header .= "<td class='text-center'>".$b_AlSt."</td>";

                $SUM_4 += $qty_order;
                $SUM_19 += $qty_propose;

                $SUM_13 += $b_FINISH_GOOD;
                $SUM_14 += $b_BALANCE;
                $SUM_15 += $b_WIP;
                $SUM_16 += $b_All;
                $SUM_17 += $b_AlSt;
                $SUM_FG_OVER += $b_FG_OVER;

              $d_Header .= "</tr>";
            }
            $d_Header .= "<tfoot class='tfoot'>";
              $d_Header .= "<tr>";
                $d_Header .= "<td class='text-left'><b>TOTAL</b></td>";
                $d_Header .= "<td class='text-left'><b></b></td>";
                $d_Header .= "<td class='text-center'><b>".$SUM_19."</b></td>";
                $d_Header .= "<td class='text-center'><b>".$SUM_4."</b></td>";
                $d_Header .= "<td class='text-center'><b>".$SUM_5."</b></td>";
                $d_Header .= "<td class='text-center'><b>".$SUM_6."</b></td>";
                $d_Header .= "<td class='text-center'><b>".$SUM_7."</b></td>";
                $d_Header .= "<td class='text-center'><b>".$SUM_8."</b></td>";
                $d_Header .= "<td class='text-center'><b>".$SUM_9."</b></td>";
                $d_Header .= "<td class='text-center'><b>".$SUM_10."</b></td>";
                $d_Header .= "<td class='text-center'><b>".$SUM_11."</b></td>";
                $d_Header .= "<td class='text-center'><b>".$SUM_12."</b></td>";
                $d_Header .= "<td class='text-center'><b>".$SUM_18."</b></td>";
                $d_Header .= "<td class='text-center'><b>".$SUM_13."</b></td>";
                $d_Header .= "<td class='text-center'><b>".$SUM_FG_OVER."</b></td>";
                $d_Header .= "<td class='text-center'><b>".$SUM_14."</b></td>";
                $d_Header .= "<td class='text-center'><b>".$SUM_15."</b></td>";
                $d_Header .= "<td class='text-center'><b>".$SUM_16."</b></td>";
                $d_Header .= "<td class='text-center'><b>".$SUM_17."</b></td>";
              $d_Header .= "</tr>";
            $d_Header .= "</tfoot>";
            if(empty($product)){
              $d_Header .= "<tr>";
              $d_Header .= "<td class='text-left' colspan='18'>Not Found Data ...</td>";
              $d_Header .= "</tr>";
            }
          $d_Header .= "</tbody>";
          $d_Header .= "</table>";
          $d_Header .= "</div>";
          $d_Header .= "</div>";
      $d_Header .= "</div>";


       echo json_encode(array(
          'header'			=> $d_Header
       ));
    }

    public function excel_report(){
  		//membuat objek PHPExcel
  		set_time_limit(0);
  		ini_set('memory_limit','1024M');
  		$no_so		= $this->uri->segment(3);

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

      // $product    = $this->db->query("SELECT a.* FROM sales_order_detail a WHERE a.no_so = '".$no_so."' ORDER BY a.product")->result_array();
      $product    = $this->db->query("SELECT a.id_category2 AS product FROM ms_inventory_category2 a ORDER BY a.id_category2")->result_array();

  		$Row		= 1;
  		$NewRow		= $Row+1;
  		$Col_Akhir	= $Cols	= getColsChar(20);
  		$sheet->setCellValue('A'.$Row, 'REPORT PRODUKSI '.$no_so);
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

  		$sheet->setCellValue('D'.$NewRow, 'Qty Requirement');
  		$sheet->getStyle('D'.$NewRow.':D'.$NextRow)->applyFromArray($style_header);
  		$sheet->mergeCells('D'.$NewRow.':D'.$NextRow);
  		$sheet->getColumnDimension('D')->setAutoSize(true);

  		$sheet->setCellValue('E'.$NewRow, 'Qty Propose');
  		$sheet->getStyle('E'.$NewRow.':E'.$NextRow)->applyFromArray($style_header);
  		$sheet->mergeCells('E'.$NewRow.':E'.$NextRow);
  		$sheet->getColumnDimension('E')->setAutoSize(true);

      $no = 6;
  		foreach (get_costcenter_report() as $key2 => $value2){
  			$row_name = getColsChar($no++);
  			$sheet->setCellValue($row_name.$NewRow, strtoupper($value2['nama_costcenter']));
  			$sheet->getStyle($row_name.$NewRow.':'.$row_name.''.$NextRow)->applyFromArray($style_header);
  			$sheet->mergeCells($row_name.$NewRow.':'.$row_name.''.$NextRow);
  			$sheet->getColumnDimension($row_name)->setAutoSize(false);
  		}

      $sheet->setCellValue('O'.$NewRow, 'Finish Good');
  		$sheet->getStyle('O'.$NewRow.':O'.$NextRow)->applyFromArray($style_header);
  		$sheet->mergeCells('O'.$NewRow.':O'.$NextRow);
  		$sheet->getColumnDimension('O')->setAutoSize(true);

  		$sheet->setCellValue('P'.$NewRow, 'Finish Good Over');
  		$sheet->getStyle('P'.$NewRow.':P'.$NextRow)->applyFromArray($style_header);
  		$sheet->mergeCells('P'.$NewRow.':P'.$NextRow);
  		$sheet->getColumnDimension('O')->setAutoSize(true);

  		$sheet->setCellValue('Q'.$NewRow, 'Finish Good Balance');
  		$sheet->getStyle('Q'.$NewRow.':Q'.$NextRow)->applyFromArray($style_header);
  		$sheet->mergeCells('Q'.$NewRow.':Q'.$NextRow);
  		$sheet->getColumnDimension('Q')->setAutoSize(true);

  		$sheet->setCellValue('R'.$NewRow, 'FG + WIP');
  		$sheet->getStyle('R'.$NewRow.':R'.$NextRow)->applyFromArray($style_header);
  		$sheet->mergeCells('R'.$NewRow.':R'.$NextRow);
  		$sheet->getColumnDimension('R')->setAutoSize(true);

  		$sheet->setCellValue('S'.$NewRow, 'Total All Stock (Lam + WIP + FG)');
  		$sheet->getStyle('S'.$NewRow.':S'.$NextRow)->applyFromArray($style_header);
  		$sheet->mergeCells('S'.$NewRow.':S'.$NextRow);
  		$sheet->getColumnDimension('S')->setAutoSize(true);

      $sheet->setCellValue('T'.$NewRow, 'Balance All Stock');
      $sheet->getStyle('T'.$NewRow.':T'.$NextRow)->applyFromArray($style_header);
      $sheet->mergeCells('T'.$NewRow.':T'.$NextRow);
      $sheet->getColumnDimension('T')->setAutoSize(true);

      $sheet->setCellValue('U'.$NewRow, 'No Dokumen');
      $sheet->getStyle('U'.$NewRow.':U'.$NextRow)->applyFromArray($style_header);
      $sheet->mergeCells('U'.$NewRow.':U'.$NextRow);
      $sheet->getColumnDimension('U')->setAutoSize(true);

      $SUM_4 = 0;
        $SUM_5 = 0;
        $SUM_6 = 0;
        $SUM_7 = 0;
        $SUM_8 = 0;
        $SUM_9 = 0;
        $SUM_10 = 0;
        $SUM_11 = 0;
        $SUM_12 = 0;
        $SUM_13 = 0;
        $SUM_14 = 0;
        $SUM_15 = 0;
        $SUM_16 = 0;
        $SUM_17 = 0;

        $SUM_18 = 0;

        $SUM_19 = 0;

          $b_FG_OVER = 0;
    if($product){
			$awal_row	= $NextRow;
			$no=0;
			foreach($product as $key => $row_Cek){
        $productX    = $this->db->query("SELECT a.* FROM sales_order_detail a WHERE a.no_so = '".$no_so."' AND a.product='".$row_Cek['product']."' LIMIT 1")->result_array();
        $qty_order = (!empty($productX[0]['qty_order']))?$productX[0]['qty_order']:0;
        $qty_propose = (!empty($productX[0]['qty_propose']))?$productX[0]['qty_propose']:0;

        $query2	 = $this->db->query("SELECT qty_stock FROM warehouse_product WHERE id_product='".$row_Cek['product']."' AND category = 'product' LIMIT 1 ")->result();
        $qty2_ = (!empty($query2[0]->qty_stock))?$query2[0]->qty_stock:0;
        $lastcost = get_last_costcenter_warehouse($row_Cek['product']);
        $query2_wip	 = $this->db->query("SELECT SUM(a.qty_stock) AS qty_stock FROM warehouse_product a LEFT JOIN ms_costcenter b ON a.costcenter=b.id_costcenter WHERE a.id_product='".$row_Cek['product']."' AND a.category = 'order' AND a.costcenter <> 'CC2000012' AND a.costcenter <> '".$lastcost."' AND b.urut <> '0'")->result();
        $qty2_wip = (!empty($query2_wip[0]->qty_stock))?$query2_wip[0]->qty_stock:0;

        $query2_lam	 = $this->db->query("SELECT a.qty_stock AS qty_stock FROM warehouse_product a LEFT JOIN ms_costcenter b ON a.costcenter=b.id_costcenter WHERE a.id_product='".$row_Cek['product']."' AND a.category = 'order' AND a.costcenter = 'CC2000012' AND b.urut <> '0'")->result();
        $qty2_lam = (!empty($query2_lam[0]->qty_stock))?$query2_lam[0]->qty_stock:0;

				$no++;
				$awal_row++;
				$awal_col	= 0;

				$awal_col++;
				$nomor	= $no;
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols.$awal_row, $nomor);
				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

				$awal_col++;
				$est_material	= strtoupper(get_project_name($row_Cek['product']));
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols.$awal_row, $est_material);
        $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

        $awal_col++;
				$est_harga	= strtoupper(get_name('ms_inventory_category2','nama','id_category2',$row_Cek['product']));
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols.$awal_row, $est_harga);
				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray3);

				$awal_col++;
				$est_harga	= $qty_propose;
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols.$awal_row, $est_harga);
        $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray4);

        $awal_col++;
				$est_harga	= $qty_order;
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols.$awal_row, $est_harga);
				$sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray4);

        foreach (get_costcenter_report() as $key2 => $value2){
          $query	 = $this->db->query("SELECT qty_stock FROM warehouse_product WHERE id_product='".$row_Cek['product']."' AND costcenter='".$value2['id_costcenter']."' AND category = 'order' LIMIT 1 ")->result();

          $qty_ = (!empty($query[0]->qty_stock))?$query[0]->qty_stock:0;
          if(get_last_costcenter_warehouse($row_Cek['product']) == $value2['id_costcenter']){
            $qty_ = 0;
          }

          if($value2['id_costcenter'] == 'CC2000003'){
            $query2	 = $this->db->query("SELECT COUNT(*) AS qty_stock FROM report_produksi_daily_detail WHERE id_product='".$row_Cek['product']."' AND ket='not yet'")->result();
            $qty_ = (!empty($query2[0]->qty_stock))?$query2[0]->qty_stock:0;
          }

          $awal_col++;
  				$est_harga	= $qty_;
  				$Cols			= getColsChar($awal_col);
  				$sheet->setCellValue($Cols.$awal_row, $est_harga);
          $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray4);
        }

        $q5	 = $this->db->query("SELECT qty_stock FROM warehouse_product WHERE id_product='".$row_Cek['product']."' AND costcenter='CC2000012' AND category = 'order' LIMIT 1 ")->result();
        $qty_5 = (!empty($q5[0]->qty_stock))?$q5[0]->qty_stock:0;
        $q6	 = $this->db->query("SELECT qty_stock FROM warehouse_product WHERE id_product='".$row_Cek['product']."' AND costcenter='CC2000011' AND category = 'order' LIMIT 1 ")->result();
        $qty_6 = (!empty($q6[0]->qty_stock))?$q6[0]->qty_stock:0;
        $q7	 = $this->db->query("SELECT qty_stock FROM warehouse_product WHERE id_product='".$row_Cek['product']."' AND costcenter='CC2000007' AND category = 'order' LIMIT 1 ")->result();
        $qty_7 = (!empty($q7[0]->qty_stock))?$q7[0]->qty_stock:0;
        $q8	 = $this->db->query("SELECT qty_stock FROM warehouse_product WHERE id_product='".$row_Cek['product']."' AND costcenter='CC2000006' AND category = 'order' LIMIT 1 ")->result();
        $qty_8 = (!empty($q8[0]->qty_stock))?$q8[0]->qty_stock:0;
        $q9	 = $this->db->query("SELECT qty_stock FROM warehouse_product WHERE id_product='".$row_Cek['product']."' AND costcenter='CC2000009' AND category = 'order' LIMIT 1 ")->result();
        $qty_9 = (!empty($q9[0]->qty_stock))?$q9[0]->qty_stock:0;
        $q10	 = $this->db->query("SELECT qty_stock FROM warehouse_product WHERE id_product='".$row_Cek['product']."' AND costcenter='CC2000010' AND category = 'order' LIMIT 1 ")->result();
        $qty_10 = (!empty($q10[0]->qty_stock))?$q10[0]->qty_stock:0;
        $q11	 = $this->db->query("SELECT qty_stock FROM warehouse_product WHERE id_product='".$row_Cek['product']."' AND costcenter='CC2000005' AND category = 'order' LIMIT 1 ")->result();
        $qty_11 = (!empty($q11[0]->qty_stock))?$q11[0]->qty_stock:0;
        $q12	 = $this->db->query("SELECT qty_stock FROM warehouse_product WHERE id_product='".$row_Cek['product']."' AND costcenter='CC2000004' AND category = 'order' LIMIT 1 ")->result();
        $qty_12 = (!empty($q12[0]->qty_stock))?$q12[0]->qty_stock:0;
        $q18	 = $this->db->query("SELECT COUNT(*) AS qty_stock FROM report_produksi_daily_detail WHERE id_product='".$row_Cek['product']."' AND ket='not yet'")->result();
        $qty_18 = (!empty($q18[0]->qty_stock))?$q18[0]->qty_stock:0;

        if(get_last_costcenter_warehouse($row_Cek['product']) == 'CC2000010'){
          $qty_10 = 0;
        }
        if(get_last_costcenter_warehouse($row_Cek['product']) == 'CC2000005'){
          $qty_11 = 0;
        }
        if(get_last_costcenter_warehouse($row_Cek['product']) == 'CC2000004'){
          $qty_12 = 0;
        }

        $SUM_5 += $qty_5;
        $SUM_6 += $qty_6;
        $SUM_7 += $qty_7;
        $SUM_8 += $qty_8;
        $SUM_9 += $qty_9;
        $SUM_10 += $qty_10;
        $SUM_11 += $qty_11;
        $SUM_12 += $qty_12;

        $SUM_18 += $qty_18;


        $b_WIP = $qty2_ + $qty2_wip;
        $b_All = $qty2_ + $qty2_wip + $qty2_lam;
        $b_AlSt = $b_All - $qty_order;

        $b_FG_OVER = $qty2_ - $qty_order;
        if($qty2_ - $qty_order < 0){
          $b_FG_OVER = 0;
        }

        //jika finishgood
        $b_FINISH_GOOD = $qty2_;
        if($qty2_ > $qty_order){
          $b_FINISH_GOOD = $qty_order;
        }


        $b_BALANCE = 0;
        if($b_FINISH_GOOD < $qty_order){
          $b_BALANCE = $b_FINISH_GOOD - $qty_order;
        }

        $SUM_4 += $qty_order;
        $SUM_19 += $qty_propose;

        $SUM_13 += $b_FINISH_GOOD;
        $SUM_14 += $b_BALANCE;
        $SUM_15 += $b_WIP;
        $SUM_16 += $b_All;
        $SUM_17 += $b_AlSt;
        $SUM_FG_OVER += $b_FG_OVER;

        $awal_col++;
        $est_harga	= $b_FINISH_GOOD;
        $Cols			= getColsChar($awal_col);
        $sheet->setCellValue($Cols.$awal_row, $est_harga);
        $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray4);

        $awal_col++;
        $est_harga	= $b_FG_OVER;
        $Cols			= getColsChar($awal_col);
        $sheet->setCellValue($Cols.$awal_row, $est_harga);
        $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray4);

        $awal_col++;
        $est_harga	= $b_BALANCE;
        $Cols			= getColsChar($awal_col);
        $sheet->setCellValue($Cols.$awal_row, $est_harga);
        $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray4);

        $awal_col++;
        $est_harga	= $b_WIP;
        $Cols			= getColsChar($awal_col);
        $sheet->setCellValue($Cols.$awal_row, $est_harga);
        $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray4);

        $awal_col++;
        $est_harga	= $b_All;
        $Cols			= getColsChar($awal_col);
        $sheet->setCellValue($Cols.$awal_row, $est_harga);
        $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray4);

        $awal_col++;
        $est_harga	= $b_AlSt;
        $Cols			= getColsChar($awal_col);
        $sheet->setCellValue($Cols.$awal_row, $est_harga);
        $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray4);

        $awal_col++;
        $est_harga	= '';
        $Cols			= getColsChar($awal_col);
        $sheet->setCellValue($Cols.$awal_row, $est_harga);
        $sheet->getStyle($Cols.$awal_row)->applyFromArray($styleArray4);
			}
      $Colsw = floatval($no) +6;

			// echo $Colsw."-".$Colse; exit;
			$sheet->setCellValue("A".$Colsw."", 'SUM');
			$sheet->getStyle("A".$Colsw.":C".$Colsw."")->applyFromArray($style_header);
			$sheet->mergeCells("A".$Colsw.":C".$Colsw."");
			$sheet->getColumnDimension('A')->setAutoSize(true);

      $sheet->setCellValue("D".$Colsw."", $SUM_19);
			$sheet->getStyle("D".$Colsw.":D".$Colsw."")->applyFromArray($styleArray4);
			$sheet->mergeCells("D".$Colsw.":D".$Colsw."");
			$sheet->getColumnDimension('D')->setAutoSize(true);

      $sheet->setCellValue("E".$Colsw."", $SUM_4);
			$sheet->getStyle("E".$Colsw.":E".$Colsw."")->applyFromArray($styleArray4);
			$sheet->mergeCells("E".$Colsw.":E".$Colsw."");
			$sheet->getColumnDimension('E')->setAutoSize(true);

      $sheet->setCellValue("F".$Colsw."", $SUM_5);
			$sheet->getStyle("F".$Colsw.":F".$Colsw."")->applyFromArray($styleArray4);
			$sheet->mergeCells("F".$Colsw.":F".$Colsw."");
			$sheet->getColumnDimension('F')->setAutoSize(true);

      $sheet->setCellValue("G".$Colsw."", $SUM_6);
			$sheet->getStyle("G".$Colsw.":G".$Colsw."")->applyFromArray($styleArray4);
			$sheet->mergeCells("G".$Colsw.":G".$Colsw."");
			$sheet->getColumnDimension('G')->setAutoSize(true);

      $sheet->setCellValue("H".$Colsw."", $SUM_7);
			$sheet->getStyle("H".$Colsw.":H".$Colsw."")->applyFromArray($styleArray4);
			$sheet->mergeCells("H".$Colsw.":H".$Colsw."");
			$sheet->getColumnDimension('H')->setAutoSize(true);

      $sheet->setCellValue("I".$Colsw."", $SUM_8);
			$sheet->getStyle("I".$Colsw.":I".$Colsw."")->applyFromArray($styleArray4);
			$sheet->mergeCells("I".$Colsw.":I".$Colsw."");
			$sheet->getColumnDimension('I')->setAutoSize(true);

			$sheet->setCellValue("J".$Colsw."", $SUM_9);
			$sheet->getStyle("J".$Colsw.":J".$Colsw."")->applyFromArray($styleArray4);
			$sheet->mergeCells("J".$Colsw.":J".$Colsw."");
			$sheet->getColumnDimension('J')->setAutoSize(true);

			$sheet->setCellValue("K".$Colsw."", $SUM_10 );
			$sheet->getStyle("K".$Colsw.":K".$Colsw."")->applyFromArray($styleArray4);
			$sheet->mergeCells("K".$Colsw.":K".$Colsw."");
			$sheet->getColumnDimension('K')->setAutoSize(true);

			$sheet->setCellValue("L".$Colsw."", $SUM_11);
			$sheet->getStyle("L".$Colsw.":L".$Colsw."")->applyFromArray($styleArray4);
			$sheet->mergeCells("L".$Colsw.":L".$Colsw."");
			$sheet->getColumnDimension('L')->setAutoSize(true);

			$sheet->setCellValue("M".$Colsw."", $SUM_12);
			$sheet->getStyle("M".$Colsw.":M".$Colsw."")->applyFromArray($styleArray4);
			$sheet->mergeCells("M".$Colsw.":M".$Colsw."");
			$sheet->getColumnDimension('M')->setAutoSize(true);

			$sheet->setCellValue("N".$Colsw."", $SUM_18);
			$sheet->getStyle("N".$Colsw.":N".$Colsw."")->applyFromArray($styleArray4);
			$sheet->mergeCells("N".$Colsw.":N".$Colsw."");
			$sheet->getColumnDimension('N')->setAutoSize(true);

			$sheet->setCellValue("O".$Colsw."", $SUM_13);
			$sheet->getStyle("O".$Colsw.":O".$Colsw."")->applyFromArray($styleArray4);
			$sheet->mergeCells("O".$Colsw.":O".$Colsw."");
			$sheet->getColumnDimension('O')->setAutoSize(true);

			$sheet->setCellValue("P".$Colsw."", $SUM_FG_OVER);
			$sheet->getStyle("P".$Colsw.":P".$Colsw."")->applyFromArray($styleArray4);
			$sheet->mergeCells("P".$Colsw.":P".$Colsw."");
			$sheet->getColumnDimension('P')->setAutoSize(true);

			$sheet->setCellValue("Q".$Colsw."", $SUM_14);
			$sheet->getStyle("Q".$Colsw.":Q".$Colsw."")->applyFromArray($styleArray4);
			$sheet->mergeCells("Q".$Colsw.":Q".$Colsw."");
			$sheet->getColumnDimension('Q')->setAutoSize(true);

			$sheet->setCellValue("R".$Colsw."", $SUM_15);
			$sheet->getStyle("R".$Colsw.":R".$Colsw."")->applyFromArray($styleArray4);
			$sheet->mergeCells("R".$Colsw.":R".$Colsw."");
			$sheet->getColumnDimension('R')->setAutoSize(true);

			$sheet->setCellValue("S".$Colsw."", $SUM_16);
			$sheet->getStyle("S".$Colsw.":S".$Colsw."")->applyFromArray($styleArray4);
			$sheet->mergeCells("S".$Colsw.":S".$Colsw."");
			$sheet->getColumnDimension('S')->setAutoSize(true);

			$sheet->setCellValue("T".$Colsw."", $SUM_17);
			$sheet->getStyle("T".$Colsw.":T".$Colsw."")->applyFromArray($styleArray4);
			$sheet->mergeCells("T".$Colsw.":T".$Colsw."");
			$sheet->getColumnDimension('T')->setAutoSize(true);

      $sheet->setCellValue("U".$Colsw."", '');
			$sheet->getStyle("U".$Colsw.":U".$Colsw."")->applyFromArray($styleArray4);
			$sheet->mergeCells("U".$Colsw.":U".$Colsw."");
			$sheet->getColumnDimension('U')->setAutoSize(true);
		}





  		$sheet->setTitle('Excel Report '.$no_so);
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
  		header('Content-Disposition: attachment;filename="Excel Report '.$no_so.' '.date('YmdHis').'.xls"');
  		//unduh file
  		$objWriter->save("php://output");
  	}

    public function get_productivity(){
      $tgl_awal = $this->uri->segment(3);
      $tgl_akhir = $this->uri->segment(4);

      $header_date    = $this->db->query("SELECT a.date AS datex FROM produksi_planning_data a WHERE a.date BETWEEN '".$tgl_awal."' AND '".$tgl_akhir."' GROUP BY a.date ORDER BY a.date")->result_array();

      $cols_width = (80 / count($header_date))/3;
        $d_Header = "<div class='box box-primary'>";
          $d_Header .= "<div class='box-body'>";
            $d_Header .= "<div class='tableFixHead' style='height:500px;'>";
              $d_Header .= "<table class='table table-bordered table-striped'>";
                $d_Header .= "<thead class='thead'>";
                  $d_Header .= "<tr class='bg-blue'>";
                    $d_Header .= "<th rowspan='2' class='text-center th headcol' style='vertical-align:middle; z-index: 99999;' width='10%'>Costcenter</th>";
                    foreach($header_date AS $val => $valx){
                      $d_Header .= "<th class='text-center' colspan='2'>".date('d-M-Y', strtotime($valx['datex']))."</th>";
                      $d_Header .= "<th class='text-center' style='vertical-align:middle;' width='".$cols_width."%' rowspan='2'>Productivity (%)</th>";
                    }
                  $d_Header .= "</tr>";
                  $d_Header .= "<tr class='bg-blue'>";
                    foreach($header_date AS $val => $valx){
                      $d_Header .= "<th class='text-center' width='".$cols_width."%'>Plan</th>";
                      $d_Header .= "<th class='text-center' width='".$cols_width."%'>Actual</th>";
                    }
                  $d_Header .= "</tr>";
              $d_Header .= "</thead>";
              $d_Header .= "<tbody>";
                foreach(get_costcenter_input_produksi() AS $val => $valx){
                  $d_Header   .= "<tr>";
                    $d_Header .= "<td class='headcol'>".strtoupper($valx['nama_costcenter'])."</td>";
                    foreach($header_date AS $val2 => $valx2){
                      $sql_qty = "SELECT SUM(a.qty) AS sum_qty FROM produksi_planning_data a LEFT JOIN produksi_planning b ON a.no_plan=b.no_plan WHERE b.costcenter='".$valx['id_costcenter']."' AND a.`date`='".$valx2['datex']."' GROUP BY a.`date` ";
                      $data_qty = $this->db->query($sql_qty)->result();
                      $qty_date = (!empty($data_qty))?$data_qty[0]->sum_qty:0;

                      $sql_qty2 = "SELECT COUNT(a.id) AS sum_qty FROM report_produksi_daily_detail a LEFT JOIN report_produksi_daily_header b ON a.id_produksi=b.id_produksi WHERE a.ket='good' AND b.id_costcenter='".$valx['id_costcenter']."' AND DATE(a.tanggal_produksi) = '".$valx2['datex']."' GROUP BY DATE(a.tanggal_produksi) ";
                      $data_qty2 = $this->db->query($sql_qty2)->result();
                      $qty_date2 = (!empty($data_qty2))?$data_qty2[0]->sum_qty:0;

                      $productivity = 0;
                      if($qty_date <> '0' AND $qty_date2 <> '0'){
                        $productivity = $qty_date2/$qty_date * 100;
                      }

                      $d_Header .= "<td align='center'>".$qty_date."</td>";
                      $d_Header .= "<td align='center'>".$qty_date2."</td>";
                      $d_Header .= "<td align='right' style='padding-right:20px;'>".number_format($productivity,2)." %</td>";
                    }
                  $d_Header   .= "</tr>";
                }
              $d_Header .= "</tbody>";

              $d_Header .= "</table>";
            $d_Header .= "</div>";
          $d_Header .= "</div>";
      $d_Header .= "</div>";


       echo json_encode(array(
          'header'			=> $d_Header
       ));
    }


}
?>
