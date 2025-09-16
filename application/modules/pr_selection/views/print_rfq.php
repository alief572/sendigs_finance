<?php
set_time_limit(0);
ob_start();

$Successno			=0;
$ErrorInfo			=0;

function print_rfq($Nama_APP, $no_rfq, $koneksi, $printby){

	$KONN = array(
		'user' => $koneksi['hostuser'],
		'pass' => $koneksi['hostpass'],
		'db'   => $koneksi['hostdb'],
		'host' => $koneksi['hostname']
	);

	// print_r($KONN); exit;

	$conn = mysqli_connect($KONN['host'],$KONN['user'],$KONN['pass']);
	mysqli_select_db($conn, $KONN['db']);

	include "application/libraries/MPDF572/mpdf.php"; 
	$mpdf=new mPDF('utf-8','A4');

	set_time_limit(0);
	ini_set('memory_limit','1024M');
	
	$sql_iden	= "SELECT a.* FROM identitas a LIMIT 1";
	$rest_iden	= mysqli_query($conn, $sql_iden);
	$data_iden	= mysqli_fetch_array($rest_iden);

	$sql_header		= "SELECT a.* FROM tran_material_rfq_header a WHERE a.no_rfq='".$no_rfq."' ";
	$rest_header	= mysqli_query($conn, $sql_header);
	$rest_nums		= mysqli_num_rows($rest_header);
	
	$sql_header2		= "SELECT a.* FROM tran_material_rfq_header a WHERE a.no_rfq='".$no_rfq."' LIMIT 1";
	$rest_header2	= mysqli_query($conn, $sql_header2);
	$header			= mysqli_fetch_array($rest_header2);
	
	$noY = 0;
	while($valx = mysqli_fetch_array($rest_header)){ $noY++;
		$sql_detail		= "SELECT a.* FROM tran_material_rfq_detail a WHERE a.hub_rfq='".$valx['hub_rfq']."' ";
		$rest_detail	= mysqli_query($conn, $sql_detail);
	
		$sql_supplier	= "SELECT a.* FROM master_supplier a where id_supplier='".$valx['id_supplier']."' LIMIT 1";
		$rest_supplier	= mysqli_query($conn, $sql_supplier);
		$data_supplier	= mysqli_fetch_array($rest_supplier);

	
		echo "<table class='gridtable2' border='0' width='100%' cellpadding='2'>";
			echo "<tr>";
				echo "<td class='header_style_company' width='58%'>".$data_iden['nm_perusahaan']."</td>";
				echo "<td class='header_style_company bold color_req' colspan='2'>REQUEST FOR QUOTATION</td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td class='header_style_alamat' rowspan=3>".nl2br($data_iden['alamat'])."</td>";
				echo "<td class='header_style_alamat' width='18%'>RFQ</td>";
				echo "<td class='header_style_alamat'>: </td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td class='header_style_alamat'>RFQ Date</td>";
				echo "<td class='header_style_alamat'>:&nbsp;&nbsp;&nbsp;".date('d F Y')."</td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td class='header_style_alamat'>Purchase Request No.</td>";
				echo "<td class='header_style_alamat'>: </td>";
			echo "</tr>";
		echo "</table>";
		echo "<br>";
		echo "<table border='0' width='100%' cellpadding='0'>";
			echo "<tr>";
				echo "<td width='44%' style='vertical-align:top;'>";
					echo "<table class='default' border='0' width='100%' cellpadding='2'>";
						echo "<tr>";
							echo "<td class='header_style2 bold'>VENDOR</td>";
						echo "</tr>";
						echo "<tr>";
							echo "<td>".strtoupper($data_supplier['name_supplier'])."</td>";
						echo "</tr>";
						echo "<tr>";
							echo "<td>".strtoupper($data_supplier['address_office'])."</td>";
						echo "</tr>";
						echo "<tr>";
							echo "<td>PHONE ".strtoupper($data_supplier['telephone'])." </td>";
						echo "</tr>";
						echo "<tr>";
							echo "<td>EMAIL ".strtoupper($data_supplier['email'])."</td>";
						echo "</tr>";
					echo "</table>";
				echo "</td>";
				echo "<td width='6%'></td>";
				echo "<td width='44%'>";
					echo "<table class='default' width='100%' cellpadding='2'>";
						echo "<tr>";
							echo "<td class='header_style2 bold'>QUOTE TO</td>";
						echo "</tr>";
						echo "<tr>";
							echo "<td>".$data_iden['nm_perusahaan']."</td>";
						echo "</tr>";
						echo "<tr>";
							echo "<td>".nl2br($data_iden['alamat'])."</td>";
						echo "</tr>";
						echo "<tr>";
							echo "<td>PHONE ".strtoupper($data_iden['no_telp'])."</td>";
						echo "</tr>";
					echo "</table>";
				echo "</td>";
			echo "</tr>";
		echo "</table>";
		echo "<p>We have requirement as below : </p>";
		echo "<table class='gridtable' width='100%' border='0' cellpadding='2'>";
			echo "<thead>";
				echo "<tr>";
					echo "<th style='text-align: left' width='5%'>NO</th>";
					echo "<th style='text-align: left' width='20%'>ITEM CODE</th>";
					echo "<th style='text-align: left'>DESCRIPTION</th>";
					echo "<th style='text-align: right' width='12%'>QUANTITY</th>";
					echo "<th style='text-align: left' width='10%'>UOM</th>";
					echo "<th style='text-align: right' width='15%'>REQUEST DATE</th>";
				echo "</tr>";
			echo "</thead>";
			echo "<tbody>";
			$no = 0;
			while($valx2 = mysqli_fetch_array($rest_detail)){ $no++;
				echo "<tr>";
					echo "<td>".$no."</td>";
					echo "<td>".strtoupper($valx2['id_material'])."</td>";
					echo "<td>".strtoupper($valx2['nm_material'])."</td>";
					echo "<td align='right'>".number_format($valx2['qty'])."</td>";
					echo "<td>".strtoupper(unit)."</td>";
					echo "<td align='right'>".date('d F Y', strtotime($valx2['tgl_dibutuhkan']))."</td>";
				echo "</tr>";
			}
			$max = 15;
			$sisa = $max - $no;
			for($a=1; $a<=$sisa; $a++){
				echo "<tr>";
					echo "<td>&nbsp;</td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
				echo "</tr>";
			}
			echo "</tbody>";
		echo "</table>";
		echo "<br>";
		echo "<p class='bold'>TERM AND CONDITIONS</p>"; 
		echo "<table class='gridtable3' width='50%' border='1' cellpadding='2'>";
			echo "<tbody>";
				echo "<tr>";
					echo "<td width='40%'>TERM OF PAYMENT</td>";
					echo "<td>".strtoupper($header['top'])."</td>";
				echo "</tr>";
				echo "<tr>";
					echo "<td>INCOTERMS</td>";
					echo "<td>".strtoupper($header['incoterms'])."</td>";
				echo "</tr>";
				echo "<tr>";
					echo "<td>REMARKS</td>";
					echo "<td>".strtoupper($header['remarks'])."</td>";
				echo "</tr>";
			echo "</tbody>";
		echo "</table>";
		echo "<br>";
		echo "<p>Please send the quotation to  : purchasing@sentralsistem.com</p>";
		echo "<br>";
		echo "<p>sentralsistem.com</p>";
		echo "<p>Buyer</p>";
		if($rest_nums <> $noY){
		echo "<pagebreak />";
		}
	}
	?>
	<style type="text/css">
	@page {
		margin-top: 0.4 cm;
		margin-left: 0.4 cm;
		margin-right: 0.4 cm;
		margin-bottom: 0.4 cm;
		margin-footer: 0 cm
	}
	
	.bold{
		font-weight: bold;
	}
	
	.color_req{
		color: #0049a8;
	}
	
	.header_style_company{
		padding: 15px;
		color: black;
		font-size: 20px;
	}
	
	.header_style_alamat{
		padding: 10px;
		color: black;
		font-size: 10px;
	}
	
	.header_style2{
		background-color: #0049a8;
		color: white;
		font-size: 10px;
		padding: 8px;
	}
	
	
	
	table.default {
		font-family: Arial, Helvetica, sans-serif;
		font-size:9px;
		padding: 0px;
	}
	
	p{
		font-family: Arial, Helvetica, sans-serif;
		font-size:10px;
	}

	p.foot1 {
		font-family: verdana,arial,sans-serif;
		font-size:10px;
	}
	.font{
		font-family: verdana,arial,sans-serif;
		font-size:14px;
	}
	
	table.gridtable {
		font-family: verdana,arial,sans-serif;
		font-size:10px;
		color:#333333;
		border: 1px solid #dddddd;
		border-collapse: collapse;
	}
	table.gridtable th {
		padding: 8px;
		background-color: #0049a8;
		color: white;
		border-color: #0049a8;
		border-style: solid;
		border-width: 1px;
	}
	table.gridtable th.head {
		padding: 8px;
		background-color: #0049a8;
		color: white;
		border-color: #0049a8;
		border-style: solid;
		border-width: 1px;
	}
	table.gridtable tr:nth-child(even) {
		background-color: #f2f2f2;
	}
	table.gridtable td {
		padding: 8px;
	}
	table.gridtable td.cols {
		padding: 8px;
	}


	table.gridtable2 {
		font-family: Arial, Helvetica, sans-serif;
		font-size:9px;
		color:#333333;
		border-width: 1px;
		border-color: #666666;
		border-collapse: collapse;
	}
	table.gridtable2 th {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #f2f2f2;
	}
	table.gridtable2 th.head {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #7f7f7f;
		color: #ffffff;
	}
	table.gridtable2 td {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #ffffff;
	}
	table.gridtable2 td.cols {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
		background-color: #ffffff;
	}

	#hrnew {
		border: 0;
		border-bottom: 1px dashed #ccc;
		background: #999;
	}
	
	table.gridtable3 {
		font-family: verdana,arial,sans-serif;
		font-size:9px;
		color:#333333;
		border-width: 1px;
		border-color: #dddddd;
		border-collapse: collapse;
	}
	table.gridtable3 td {
		border-width: 1px;
		padding: 6px;
		border-style: solid;
		border-color: #dddddd;
	}
	table.gridtable3 td.cols {
		border-width: 1px;
		padding: 6px;
		border-style: solid;
		border-color: #dddddd;
	}
	</style>


	<?php

	$html = ob_get_contents();
	// exit;
	ob_end_clean();
	// $mpdf->SetWatermarkText('ORI Group');
	$mpdf->showWatermarkText = true;
	$mpdf->SetTitle('Request From Quotation');
	$mpdf->AddPage();
	$mpdf->WriteHTML($html);
	$mpdf->Output('RFQ '.strtoupper($data_supplier['name_supplier']).' '.date('dmyhis').'.pdf' ,'I');

	//exit;
	//return $attachment;
}

?>
