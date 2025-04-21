<?php
set_time_limit(0);
ob_start();

$Successno			=0;
$ErrorInfo			=0;
$sroot 				= $_SERVER['DOCUMENT_ROOT'];

function print_request_material($Nama_APP, $kode_trans, $koneksi, $printby, $check){

	$KONN = array(
		'user' => $koneksi['hostuser'],
		'pass' => $koneksi['hostpass'],
		'db'   => $koneksi['hostdb'],
		'host' => $koneksi['hostname']
	);

	$conn = mysqli_connect($KONN['host'],$KONN['user'],$KONN['pass']);
	mysqli_select_db($conn, $KONN['db']);

	$sroot 		= $_SERVER['DOCUMENT_ROOT'];
	// include $sroot."/application/libraries/MPDF57/mpdf.php";
	include $sroot."/application/libraries/MPDF57/mpdf.php";
	// $mpdf=new mPDF('utf-8','A4');
	$mpdf=new mPDF('utf-8','A4-L');

	set_time_limit(0);
	ini_set('memory_limit','1024M');

	//Beginning Buffer to save PHP variables and HTML tags
	ob_start();
	date_default_timezone_set('Asia/Jakarta');
	$today = date('l, d F Y [H:i:s]');

	$sql_d 		= "SELECT * FROM warehouse_adjustment_detail WHERE kode_trans='".$kode_trans."' ";
	$rest_d		= mysqli_query($conn, $sql_d);

	$sql_header 		= "SELECT * FROM warehouse_adjustment WHERE kode_trans='".$kode_trans."' ";
	$result_header		= mysqli_query($conn, $sql_header);
	$rest_data 				= mysqli_fetch_array($result_header);
	?>

	<table class="gridtable2" border='1' width='100%' cellpadding='2'>
		<tr>
			<td align='center'><b>PT  ORI POLYTEC COMPOSITE</b></td>
		</tr>
		<tr>
			<td align='center'><b><h2>REQUEST MATERIAL <?= $rest_data['no_ipp']; ?></h2></b></td>
		</tr>
	</table>
	<br>
	<br>
	<table class="gridtable2" width="100%" border='0'>
		<thead>
			<tr>
				<td class="mid" width='15%'>No Transaksi</td>
				<td class="mid" width='2%'>:</td>
				<td class="mid"><?= date('ymdhis', strtotime($rest_data['created_date']));?></td>
			</tr>
			<tr>
				<td class="mid">Tanggal Request</td>
				<td class="mid">:</td>
				<td class="mid"><?= date('d F Y', strtotime($rest_data['created_date']));?></td>
			</tr>
		</thead>
	</table><br>
	<table class="gridtable" width='100%' border='1' cellpadding='2'>
		<thead align='center'>
			<tr>
				<th class="mid" width='4%'>No</th>
				<th class="mid" style='vertical-align:middle;'>Material Name</th>
				<th class="mid" style='vertical-align:middle;'>Category</th>
                <th class="mid" width='13%'>Qty Request</th>>
				<th class="mid" width='17%'>Keterangan</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$No=0;
            while($valx = mysqli_fetch_array($rest_d)){ $No++;

				$qty_oke 		= number_format($valx['qty_oke'],2);
				$keterangan 	= ucfirst($valx['keterangan']);

				if($rest_data['checked'] == 'Y'){
					$qty_oke 		= number_format($valx['check_qty_oke'],2);
					$keterangan 	= ucfirst($valx['check_keterangan']);
				}

				echo "<tr>";
					echo "<td align='center'>".$No."</td>";
					echo "<td>".$valx['nm_material']."</td>";
					echo "<td>".$valx['nm_category']."</td>";
					echo "<td align='right'>".$qty_oke."</td>";
					echo "<td>".$keterangan."</td>";
				echo "</tr>";
			}
			?>
		</tbody>
	</table><br><br><br>
	<table class="gridtable2" width='100%' border='0' cellpadding='2'>
		<tr>
			<td width='75%'></td>
			<td>Penerima,</td>
			<td></td>
		</tr>
		<tr>
			<td height='45px'></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td></td>
			<td>_________________</td>
			<td></td>
		</tr>
	</table>

	<style type="text/css">
		@page {
			margin-top: 1cm;
			margin-left: 0.5cm;
			margin-right: 0.5cm;
			margin-bottom: 1cm;
		}
		.mid{
			vertical-align: middle !important;
		}
		table.gridtable {
			font-family: verdana,arial,sans-serif;
			font-size:9px;
			color:#333333;
			border-width: 1px;
			border-color: #666666;
			border-collapse: collapse;
		}
		table.gridtable th {
			border-width: 1px;
			padding: 8px;
			border-style: solid;
			border-color: #666666;
			background-color: #f2f2f2;
		}
		table.gridtable th.head {
			border-width: 1px;
			padding: 8px;
			border-style: solid;
			border-color: #666666;
			background-color: #7f7f7f;
			color: #ffffff;
		}
		table.gridtable td {
			border-width: 1px;
			padding: 3px;
			border-style: solid;
			border-color: #666666;
			background-color: #ffffff;
		}
		table.gridtable td.cols {
			border-width: 1px;
			padding: 3px;
			border-style: solid;
			border-color: #666666;
			background-color: #ffffff;
		}

		table.gridtable2 {
			font-family: verdana,arial,sans-serif;
			font-size:10px;
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
	</style>


	<?php
	$footer = "<p style='font-family: verdana,arial,sans-serif; font-size:10px;'><i>Printed by : ".ucfirst(strtolower($printby)).", ".$today." / ".$kode_trans."</i></p>";
	$html = ob_get_contents();
	// exit;
	ob_end_clean();
	// $mpdf->SetWatermarkText('ORI Group');
	$mpdf->showWatermarkText = true;
	$mpdf->SetTitle($kode_trans."/".date('ymdhis', strtotime($dated)));
	$mpdf->AddPage();
	$mpdf->SetFooter($footer);
	$mpdf->WriteHTML($html);
	$mpdf->Output('request material '.$kode_trans.'/'.date('ymdhis', strtotime($dated)).'.pdf' ,'I');
}


?>
