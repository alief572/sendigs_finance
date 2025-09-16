<?php
set_time_limit(0);
ob_start();

$Successno			=0;
$ErrorInfo			=0;
$sroot 				= $_SERVER['DOCUMENT_ROOT'];

function PrintSalesOrder($Nama_APP, $koneksi, $printby, $no_so){

	$KONN = array(
		'user' => $koneksi['hostuser'],
		'pass' => $koneksi['hostpass'],
		'db'   => $koneksi['hostdb'],
		'host' => $koneksi['hostname']
	);

	$conn = mysqli_connect($KONN['host'],$KONN['user'],$KONN['pass']);
	mysqli_select_db($conn, $KONN['db']);

	$sroot 		= $_SERVER['DOCUMENT_ROOT']."/origa_live";
	include $sroot."/application/libraries/MPDF57/mpdf.php";
	$mpdf=new mPDF('utf-8','A4');
	// $mpdf=new mPDF('utf-8','A4-L');

	set_time_limit(0);
	ini_set('memory_limit','1024M');

	//Beginning Buffer to save PHP variables and HTML tags
	ob_start();
	date_default_timezone_set('Asia/Jakarta');
	$today = date('l, d F Y [H:i:s]');

	$qBQ 		= "SELECT * FROM sales_order_header WHERE no_so = '".$no_so."' ";
	$dResulBQ	= mysqli_query($conn, $qBQ);
	$dHeaderBQ	= mysqli_fetch_array($dResulBQ);

  $data1BTS 	= "SELECT * FROM sales_order_detail WHERE no_so = '".$no_so."' ";
	$result1BTS	= mysqli_query($conn, $data1BTS);

	echo "<htmlpageheader>";
	?>

	<table class='header_style' border='0' width='100%' cellpadding='2'>
		<thead>
		<tr>
			<td><b><h2>Sales Order</h2></b></td>
		</tr>
		</thead>
	</table>
	<br>

	<table class='header_style2' border='0' width='100%' cellpadding='2'>
		<tr>
			<td width='100px' align='center' style='vertical-align:top;' rowspan='7'><img src='<?=$sroot;?>/assets/images/ori_logo.jpg' alt="" height='120' width='110' ></td>
			<td rowspan='7' width='20px'></td>
			<td colspan='3' style='background-color: #ffffff !important;color: #0e5ca9;'><b><h3>PT  ORIGA MULIA FRP</h3></b></td>
		</tr>
		<tr style='background-color: #ffffff;'>
			<td width='100px'>SO Number</td>
			<td width='15px'>:</td>
			<td><?= $no_so;?></td>
		</tr>
		<tr style='background-color: #ffffff;'>
			<td style='vertical-align:top;'>Customer Name</td>
			<td style='vertical-align:top;'>:</td>
			<td><?= strtoupper(strtolower(get_name('master_customer', 'name_customer', 'id_customer', $dHeaderBQ['code_cust']))); ?></td>

		</tr>
    <tr style='background-color: #ffffff;'>
			<td>Delivery Date</td>
			<td>:</td>
			<td><?= date('l, d F Y',strtotime($dHeaderBQ['delivery_date'])); ?></td>
		</tr>
    <tr style='background-color: #ffffff;'>
			<td>Shipping BY</td>
			<td>:</td>
			<td><?= strtoupper(strtolower($dHeaderBQ['shipping'])); ?></td>
		</tr>
		<tr style='background-color: #ffffff;'>
			<td>&nbsp;</td>
			<td></td>
			<td></td>
		</tr>
	</table>
	<br>
	<?php echo "<htmlpageheader>";?>
	<table class="gridtable" width='100%' border='0' cellpadding='2'>
		<tbody>
			<tr>
				<td style='background-color: #0e5ca9 !important;color: white;' colspan='5'><b>PRODUCT</b></td>
			</tr>
			<tr class='bg-bluexyz'>
				<th class="text-center" width='10%'>No</th>
				<th class="text-center" width='35%'>Project</th>
				<th class="text-center" width='35%'>Product</th>
				<th class="text-center" width='20%'>Qty Propose</th>
				<th class="text-center" width='20%'>Qty Order</th>
			</tr>
		</tbody>
    <tbody>
			<?php
			$SUM = 0;
      $SUM2 = 0;
			$no = 0;
			while($valx = mysqli_fetch_array($result1BTS)){
				$no++;
        $SUM += $valx['qty_order'];
        $SUM2 += $valx['qty_propose'];
				echo "<tr>";
					echo "<td align='center'>".$no."</td>";
					echo "<td align='left'>".strtoupper(get_project_name($valx['product']))."</td>";
					echo "<td align='left'>".strtoupper(get_name('ms_inventory_category2','nama','id_category2',$valx['product']))."</td>";
					echo "<td align='center'>".$valx['qty_order']."</td>";
					echo "<td align='center'>".$valx['qty_propose']."</td>";
				echo "</tr>";
			}
			?>
			<tr class='FootColor'>
        <td><b></b></td>
				<td colspan='2'><b>TOTAL OF PRODUCT</b></td>
				<td align='center'><b><?= number_format($SUM);?></b></td>
        <td align='center'><b><?= number_format($SUM2);?></b></td>
			</tr>
		</tbody>


	</table>
	<style type="text/css">
	@page {
		margin-top: 0cm;
		margin-left: 0cm;
		margin-right: 0cm;
		margin-bottom: 0cm;
	}

	#header{
		position:fixed;
	}
	p.foot1 {
		font-family: verdana,arial,sans-serif;
		font-size:10px;
	}
	.font{
		font-family: verdana,arial,sans-serif;
		font-size:14px;
	}
	.fontheader{
		font-family: verdana,arial,sans-serif;
		font-size:13px;
		color:#333333;
		border-width: 1px;
		border-color: #ffffff;
		border-collapse: collapse;
	}

	.headX{
		background-color: #0e5ca9 !important;
		color: white;
	}

	.header_style{
		border-style: solid;
		border-bottom-width: 5px;
		border-bottom-color: #0e5ca9;
		background-color: #0e5ca9;
		padding: 15px;
		color: white;
	}

	.header_style2{
		font-family: verdana,arial,sans-serif;
		font-size:12px;
		color:#333333;
		border-width: 1px;
		border-style: solid;
		border-color: #ffffff;
		border-collapse: collapse;
		margin-top: 0cm;
		margin-left: 0.5cm;
		margin-right: 0.5cm;
		margin-bottom: 0cm;
	}

	table.gridtable {
		font-family: verdana,arial,sans-serif;
		font-size:10px;
		color:#333333;
		border-width: 1px;
		border-color: black;
		border-collapse: collapse;
		margin-top: 0cm;
		margin-left: 0.5cm;
		margin-right: 0.5cm;
		margin-bottom: 0cm;
	}
	table.gridtable th {
		border-width: 1px;
		padding: 5px;
		border-style: solid;
		border-color: #ffffff;
		background-color: #cccccc;
	}
	table.gridtable th.head {
		border-width: 1px;
		padding: 5px;
		border-style: solid;
		border-color: #ffffff;
		background-color: #7f7f7f;
		color: #ffffff;
	}
	table.gridtable td {
		border-width: 1px;
		padding: 5px;
		border-style: solid;
		border-color: #ffffff;
		background-color: #f2f2f2;

	}
	table.gridtable td.cols {
		border-width: 1px;
		padding: 5px;
		border-style: solid;
		border-color: #ffffff;
		background-color: #f2f2f2;
	}


	table.gridtable2 {
		font-family: verdana,arial,sans-serif;
		font-size:10px;
		color:#333333;
		border-width: 1px;
		border-color: #ffffff;
		border-collapse: collapse;
		margin-top: 0cm;
		margin-left: 0.5cm;
		margin-right: 0.5cm;
		margin-bottom: 0cm;
	}

	table.gridtable2 td {
		border-width: 1px;
		padding: 5px;
		border-style: solid;
		border-color: #ffffff;
		background-color: #ffffff;

	}
	table.gridtable2 td.cols {
		border-width: 1px;
		padding: 5px;
		border-style: solid;
		border-color: #ffffff;
		background-color: #ffffff;
	}

	#space{
		padding: 3px;
		width: 180px;
		height: 1px;
	}
	p {
		margin: 0 0 0 0;
	}


</style>


	<?php
	$footer = "<p style='font-family: verdana,arial,sans-serif; font-size:10px;'><i>Printed by : ".ucfirst(strtolower(get_name('users', 'username', 'id_user', $printby))).", ".$today."</i></p>";

	// exit;
	$html = ob_get_contents();
	ob_end_clean();
	// flush();
	// $mpdf->SetWatermarkText('ORI Group');

	$mpdf->showWatermarkText = true;
	$mpdf->SetTitle('Sales Order');
	// $mpdf->AddPage('L');
	$mpdf->SetFooter($footer);
	$mpdf->WriteHTML($html);
	$mpdf->Output("Sales Order ".$no_so." ".date('dmYHis').".pdf" ,'I');

	//exit;
	//return $attachment;
}


?>
