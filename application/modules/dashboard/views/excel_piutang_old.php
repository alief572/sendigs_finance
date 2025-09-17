<?php
$sroot		= $_SERVER['DOCUMENT_ROOT'];
include $sroot."/application/libraries/PHPExcel/Classes/PHPExcel.php"; 
include $sroot."/application/libraries/PHPExcel/Classes/PHPExcel/Writer/Excel5.php";

$objPHPExcel	= new PHPExcel();

$style_header = array(
	'borders' => array(
		'allborders' => array(
			  'style' => PHPExcel_Style_Border::BORDER_THIN,
			  'color' => array('rgb'=>'1006A3')
		  )
	),
	'fill' => array(
		'type' => PHPExcel_Style_Fill::FILL_SOLID,
		'color' => array('rgb'=>'E1E0F7'),
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
$sheet 	= $objPHPExcel->getActiveSheet();
$Row	= 1;
$NewRow	= $Row+1;


if($kategori==1){
	$file_name	= 'Laporan_Aging_Piutang_0-15';
	$title_name	= 'Aging 0-15 Hari';
	$judul		= 'Laporan Aging Piutang Customer 0-15 Hari';
}else if($kategori==2){
	$file_name	= 'Laporan_Aging_Piutang_16-30';
	$title_name	= 'Aging 16-30 Hari';
	$judul		= 'Laporan Aging Piutang Customer 16-30 Hari';
}else if($kategori==3){
	$file_name	= 'Laporan_Aging_Piutang_31-60';
	$title_name	= 'Aging 31-60 Hari';
	$judul		= 'Laporan Aging Piutang Customer 31-60 Hari';
}else if($kategori==4){
	$file_name	= 'Laporan_Aging_Piutang_61-90';
	$title_name	= 'Aging 61-90 Hari';
	$judul		= 'Laporan Aging Piutang Customer 61-90 Hari';
}else if($kategori==5){
	$file_name	= 'Laporan_Aging_Piutang_lebih_90';
	$title_name	= 'Aging > 90 Hari';
	$judul		= 'Laporan Aging Piutang Customer > 90 Hari';
}
$sheet->setCellValue('A'.$Row, $judul);
$sheet->getStyle('A'.$Row.':J'.$NewRow)->applyFromArray($style_header);
$sheet->mergeCells('A'.$Row.':J'.$NewRow);

$NewRow++;
$Mulai		= 1;
$Cols		= getColsChar($Mulai);
$sheet->setCellValue($Cols.$NewRow, 'No');
$sheet->getStyle($Cols.$NewRow)->applyFromArray($style_header);
$Arr_Data	= array(
	'no_invoice'			=> 'No Invoice',
	'tanggal_invoice'		=> 'Tanggal Invoice',
	'kdcab'					=> 'Cabang',
	'nm_customer'			=> 'Customer',
	'nm_salesman'			=> 'Salesman',
	'hargajualtotal'		=> 'Total Invoice',
	'jum_bayar'				=> 'Payment',
	'hargajualtotal - jum_bayar'=> 'AR',
	'umur'					=> 'Aging (Days)'
);
foreach($Arr_Data as $keyF=>$valF){
	$Mulai++;
	$Cols		= getColsChar($Mulai);
	$sheet->setCellValue($Cols.$NewRow, $valF);
	$sheet->getStyle($Cols.$NewRow)->applyFromArray($style_header);
}

if($rows_ar){
	$loop	=0;
	$sekarang	= date('Y-m-d');
	$Total_Inv 	= $Total_Pay = $Total_AR	=0;
	foreach($rows_ar as $key=>$val){
		$loop++;
		$NewRow++;
		$Mulai		= 1;
		$Cols		= getColsChar($Mulai);
		$sheet->setCellValue($Cols.$NewRow, $loop);
		$sheet->getStyle($Cols.$NewRow)->applyFromArray($styleArray1);
		$intD		= 0;
		foreach($Arr_Data as $keyF=>$valF){
			$intD++;
			$Mulai++;
			if($intD==2){
				$Nil_Data	= date('d M Y',strtotime($val->$keyF));
			}else if($intD==6){
				$Nil_Data	= number_format(round($val->$keyF));
				$Total_Inv	+=round($val->$keyF);
			}else  if($intD==7){
				$Nil_Data	= number_format(round($val->$keyF));
				$Total_Pay	+=round($val->$keyF);
			}else if($intD==8){
				$Nil_Data	= number_format(round($val->hargajualtotal) - round($val->jum_bayar));
				$Total_AR	+= (round($val->hargajualtotal) - round($val->jum_bayar));
			}else{
				$Nil_Data	= $val->$keyF;
			}
			
			$Cols		= getColsChar($Mulai);
			$sheet->setCellValue($Cols.$NewRow, $Nil_Data);
			$sheet->getStyle($Cols.$NewRow)->applyFromArray($styleArray1);
		}
		
	}
	$NewRow++;
	$sheet->setCellValue('A'.$NewRow, 'Grand Total');
	$sheet->getStyle('A'.$NewRow.':F'.$NewRow)->applyFromArray($style_header);
	$sheet->mergeCells('A'.$NewRow.':F'.$NewRow);
	
	$sheet->setCellValue('G'.$NewRow, number_format($Total_Inv));
	$sheet->getStyle('G'.$NewRow)->applyFromArray($style_header);
	
	$sheet->setCellValue('H'.$NewRow, number_format($Total_Pay));
	$sheet->getStyle('H'.$NewRow)->applyFromArray($style_header);
	
	$sheet->setCellValue('I'.$NewRow, number_format($Total_AR));
	$sheet->getStyle('I'.$NewRow)->applyFromArray($style_header);
	
	$sheet->setCellValue('J'.$NewRow, number_format(''));
	$sheet->getStyle('J'.$NewRow)->applyFromArray($style_header);
}


$sheet->setTitle($title_name);       
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
ob_end_clean();
//sesuaikan headernya 
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
//ubah nama file saat diunduh
header('Content-Disposition: attachment;filename="'.$file_name.date('YmdHis').'.xls"');
//unduh file
$objWriter->save("php://output");
exit;

function getColsChar($colums){
	if($colums>26){
		$modCols = floor($colums/26);
		$ExCols = $modCols*26;
		$totCols = $colums-$ExCols;
		
		if($totCols==0){
			$modCols=$modCols-1;
			$totCols+=26;
		}
		
		$lets1 = getLetColsLetter($modCols);
		$lets2 = getLetColsLetter($totCols);
		return $letsi = $lets1.$lets2;
	}else{
		$lets = getLetColsLetter($colums);
		return $letsi = $lets;
	}
}

function getLetColsLetter($numbs){
// Palleng by jester
	switch($numbs){
		case 1:
		$Chars = 'A';
		break;
		case 2:
		$Chars = 'B';
		break;
		case 3:
		$Chars = 'C';
		break;
		case 4:
		$Chars = 'D';
		break;
		case 5:
		$Chars = 'E';
		break;
		case 6:
		$Chars = 'F';
		break;
		case 7:
		$Chars = 'G';
		break;
		case 8:
		$Chars = 'H';
		break;
		case 9:
		$Chars = 'I';
		break;
		case 10:
		$Chars = 'J';
		break;
		case 11:
		$Chars = 'K';
		break;
		case 12:
		$Chars = 'L';
		break;
		case 13:
		$Chars = 'M';
		break;
		case 14:
		$Chars = 'N';
		break;
		case 15:
		$Chars = 'O';
		break;
		case 16:
		$Chars = 'P';
		break;
		case 17:
		$Chars = 'Q';
		break;
		case 18:
		$Chars = 'R';
		break;
		case 19:
		$Chars = 'S';
		break;
		case 20:
		$Chars = 'T';
		break;
		case 21:
		$Chars = 'U';
		break;
		case 22:
		$Chars = 'V';
		break;
		case 23:
		$Chars = 'W';
		break;
		case 24:
		$Chars = 'X';
		break;
		case 25:
		$Chars = 'Y';
		break;
		case 26: 
		$Chars = 'Z';
		break;
	}

	return $Chars;
}

?>