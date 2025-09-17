<?php
set_time_limit(0);
ob_start();

$Successno			=0;
$ErrorInfo			=0;
$sroot 				= $_SERVER['DOCUMENT_ROOT'];


function print_planning_produksi($Nama_APP, $no_plan, $koneksi, $printby){

	$KONN = array(
		'user' => $koneksi['hostuser'],
		'pass' => $koneksi['hostpass'],
		'db'   => $koneksi['hostdb'],
		'host' => $koneksi['hostname']
	);

	$conn = mysqli_connect($KONN['host'],$KONN['user'],$KONN['pass']);
	mysqli_select_db($conn, $KONN['db']);

	$sroot 		= $_SERVER['DOCUMENT_ROOT'];
	// include $sroot. "/application/libraries/PHPMailer/PHPMailerAutoload.php";
	include $sroot."/application/libraries/MPDF57/mpdf.php";
	// $mpdf=new mPDF('utf-8','A4');
	$mpdf=new mPDF('utf-8','A4-L');

	set_time_limit(0);
	ini_set('memory_limit','1024M');

	//Beginning Buffer to save PHP variables and HTML tags
	ob_start();
	date_default_timezone_set('Asia/Jakarta');
	$today = date('l, d F Y [H:i:s]');

  $data_detail  = "SELECT * FROM produksi_planning_data WHERE no_plan='".$no_plan."' GROUP BY `date` ORDER BY `date`";
  $data_product = "SELECT * FROM produksi_planning_data WHERE no_plan='".$no_plan."' GROUP BY product ORDER BY product";
  $data_header  = "SELECT * FROM produksi_planning WHERE no_plan='".$no_plan."'";

  $result	      = mysqli_query($conn, $data_detail);
  $resultx	      = mysqli_query($conn, $data_detail);
  $data_num	    = mysqli_num_rows($result);

  $result_p	    = mysqli_query($conn, $data_product);


  $result_h	    = mysqli_query($conn, $data_header);
  $header	      = mysqli_fetch_array($result_h);
  $date_now = date('Y-m-d', strtotime($header['date_awal']));
	?>

	<table class="gridtable" border='1' width='100%' cellpadding='2'>
		<tr>
      <td width='80px' rowspan='3' style='padding:0px;'><img src='<?=$sroot;?>/assets/images/ori_logo.jpg' alt="" height='100' width='80' ></td>
			<td align='center'><b>PT. ORIGA MULIA FRP</b></td>
			<td width='15%'>Nomor Dok.</td>
			<td width='15%'></td>
		</tr>
		<tr>
			<td align='center' rowspan='2'><b><h2>LAMINATION PLANNING</h2></b></td>
			<td>Rev.</td>
			<td></td>
		</tr>
		<tr>
			<td>Tgl Berlaku</td>
			<td></td>
		</tr>
	</table>
  <br>
	<table class="gridtable2" border='0' width='100%' >
		<tr>
			<td width='24%'>Costcenter</td>
			<td width='1%'>:</td>
			<td width='25%'><?= strtoupper(get_name_field('ms_costcenter', 'nama_costcenter', 'id_costcenter', $header['costcenter']));?></td>
			<td width='24%'></td>
			<td width='1%'></td>
			<td width='25%'></td>
		</tr>
		<tr>
			<td width='24%'>Dari Tanggal</td>
			<td width='1%'>:</td>
			<td width='25%'><?=date('l, d F Y',strtotime($header['date_awal']));?></td>
			<td width='24%'></td>
			<td width='1%'></td>
			<td width='25%'></td>
		</tr>
		<tr>
			<td width='24%'>Sampai Tanggal</td>
			<td width='1%'>:</td>
			<td width='25%'><?=date('l, d F Y',strtotime($header['date_akhir']));?></td>
			<td width='24%'></td>
			<td width='1%'></td>
			<td width='25%'></td>
		</tr>
	</table>
	<br>
  <table class="gridtable" width='100%'>
    <thead>
      <tr>
        <th rowspan='2' style='vertical-align:middle; width:150px !important;'>Project</th>
        <th rowspan='2' style='vertical-align:middle; width:200px !important;'>Product</th>
        <th rowspan='2' style='vertical-align:middle; width:100px !important;'>Qty Order</th>
        <th rowspan='2' style='vertical-align:middle; width:100px !important;'>Stock</th>
        <th rowspan='2' style='vertical-align:middle; width:100px !important;'>Shortages to Fulfill Orders</th>
        <?php
        $key2 = 1;
        while($value = mysqli_fetch_array($result)){
            $loop_date = date("d-m-Y", strtotime("+".$key2." day", strtotime($date_now)));
            $key2++;
            echo "<th colspan='2' style='vertical-align:middle; width:150px !important;'>".$loop_date."</th>";
        }
        ?>
      </tr>
      <tr>
        <?php
        while($value = mysqli_fetch_array($resultx)){
            echo "<th style='vertical-align:middle; width:75px !important;'>Plan</th>";
            echo "<th style='vertical-align:middle; width:75px !important;'>Aktual</th>";
        }
        ?>
      </tr>
    </thead>
    <tbody>
      <?php
        while($value = mysqli_fetch_array($result_p)){
            echo "<tr>";
            echo "<td>".strtoupper(get_project_name($value['product']))."</td>";
            echo "<td>".strtoupper(get_name('ms_inventory_category2','nama','id_category2',$value['product']))."</td>";
            echo "<td align='center'>".$value['qty_order']."</td>";
            echo "<td align='center'>".$value['stock']."</td>";
            echo "<td align='center'>".$value['shortages']."</td>";

            $data_detail2  = "SELECT * FROM produksi_planning_data WHERE no_plan='".$no_plan."' GROUP BY `date` ORDER BY `date`";
            $result2	    = mysqli_query($conn, $data_detail2);
            while($value2 = mysqli_fetch_array($result2)){
                $q_weight = "SELECT qty FROM produksi_planning_data WHERE `date`='".$value2['date']."' AND product='".$value['product']."' AND no_plan='".$value['no_plan']."' LIMIT 1 ";
                $wight_h	= mysqli_query($conn, $q_weight);
              	$weight	  = mysqli_fetch_array($wight_h);
                $nil = (!empty($weight))?$weight['qty']:0;
                echo "<td align='center'>".number_format($nil)."</td>";
                echo "<td align='center'></td>";
            }
            echo "</tr>";
        }
       ?>
    </tbody>
  </table>

	<div id='space'></div>

	<p class='foot1'> <?php echo "<i>Printed by : ".ucwords(strtolower(get_name_field('users', 'username', 'id_user', $printby))).", ".$today." / ".$no_plan."</i>"; ?> </p>
	<br>
	<style type="text/css">
	@page {
		margin-top: 0.7cm;
		margin-left: 0.7cm;
		margin-right: 0.7cm;
		margin-bottom: 0.7cm;
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
		border-color: #666666;
		border-collapse: collapse;
	}
	table.gridtable {
		font-family: verdana,arial,sans-serif;
		font-size:12px;
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
		padding: 8px;
		border-style: solid;
		border-color: #666666;
		background-color: #ffffff;
	}
	table.gridtable td.cols {
		border-width: 1px;
		padding: 8px;
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

	table.cooltabs {
		font-size:12px;
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 5px 5px;
	}
	table.cooltabs th.reg {
		font-family: verdana,arial,sans-serif;
		border-radius: 5px 5px 5px 5px;
		background: #e3e0e4;
		padding: 5px;
	}
	table.cooltabs td.reg {
		font-family: verdana,arial,sans-serif;
		border-radius: 5px 5px 5px 5px;
		padding: 5px;
	}
	#cooltabs {
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 5px 5px;
		background: #e3e0e4;
		padding: 5px;
		width: 800px;
		height: 20px;
	}
	#cooltabs2{
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 5px 5px;
		background: #e3e0e4;
		padding: 5px;
		width: 180px;
		height: 10px;
	}
	#space{
		padding: 3px;
		width: 180px;
		height: 1px;
	}
	#cooltabshead{
		font-size:12px;
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 0 0;
		background: #dfdfdf;
		padding: 5px;
		width: 162px;
		height: 10px;
		float:left;
	}
	#cooltabschild{
		font-size:10px;
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 0 0 5px 5px;
		padding: 5px;
		width: 162px;
		height: 10px;
		float:left;
	}
	p {
		margin: 0 0 0 0;
	}
	p.pos_fixed {
		font-family: verdana,arial,sans-serif;
		position: fixed;
		top: 30px;
		left: 230px;
	}
	p.pos_fixed2 {
		font-family: verdana,arial,sans-serif;
		position: fixed;
		top: 589px;
		left: 230px;
	}
	.barcode {
		padding: 1.5mm;
		margin: 0;
		vertical-align: top;
		color: #000044;
	}
	.barcodecell {
		text-align: center;
		vertical-align: middle;
		position: fixed;
		top: -10px;
		right: 10px;
	}
	.barcodecell2 {
		text-align: center;
		vertical-align: middle;
		position: fixed;
		top: 548px;
		right: 10px;
	}
	p.barcs {
		font-family: verdana,arial,sans-serif;
		font-size:11px;
		position: fixed;
		top: 38px;
		right: 115px;
	}
	p.barcs2 {
		font-family: verdana,arial,sans-serif;
		font-size:11px;
		position: fixed;
		top: 591px;
		right: 115px;
	}
	p.pt {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 62px;
		left: 5px;
	}
	p.alamat {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 71px;
		left: 5px;
	}
	p.tlp {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 80px;
		left: 5px;
	}
	p.pt2 {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 596px;
		left: 5px;
	}
	p.alamat2 {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 605px;
		left: 5px;
	}
	p.tlp2 {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 614px;
		left: 5px;
	}
	#hrnew {
		border: 0;
		border-bottom: 1px dashed #ccc;
		background: #999;
	}
	</style>


	<?php

	$html = ob_get_contents();
	// $footer = "<p style='font-family: verdana,arial,sans-serif; font-size:10px;'><i>Printed by : ".ucfirst(strtolower($printby)).", ".$today."</i></p>";
	// exit;
	ob_end_clean();
	// $mpdf->SetWatermarkText('ORI Group');
	$mpdf->showWatermarkText = true;
	$mpdf->SetTitle('SPK Of Planning Produksi');
	// $mpdf->setHTMLFooter($footer);
	$mpdf->WriteHTML($html);
	$mpdf->Output($no_plan.'.pdf' ,'I');

	//exit;
	//return $attachment;
}

function print_planning_produksi_custom($Nama_APP, $costcenter, $koneksi, $printby, $tgl_awal, $tgl_akhir){

	$KONN = array(
		'user' => $koneksi['hostuser'],
		'pass' => $koneksi['hostpass'],
		'db'   => $koneksi['hostdb'],
		'host' => $koneksi['hostname']
	);

	$conn = mysqli_connect($KONN['host'],$KONN['user'],$KONN['pass']);
	mysqli_select_db($conn, $KONN['db']);

	$sroot 		= $_SERVER['DOCUMENT_ROOT'];
	// include $sroot. "/application/libraries/PHPMailer/PHPMailerAutoload.php";
	include $sroot."/application/libraries/MPDF57/mpdf.php";
	// $mpdf=new mPDF('utf-8','A4');
	$mpdf=new mPDF('utf-8','A4-L');

	set_time_limit(0);
	ini_set('memory_limit','1024M');

	//Beginning Buffer to save PHP variables and HTML tags
	ob_start();
	date_default_timezone_set('Asia/Jakarta');
	$today = date('l, d F Y [H:i:s]');


	echo "<pre>";
  $data_header  = "SELECT * FROM produksi_planning WHERE costcenter='".$costcenter."'";
	$result_h	    = mysqli_query($conn, $data_header);

	// echo $data_header."<br>";
	$dtListArray = array();
	$no = 0;
	while($valx = mysqli_fetch_array($result_h)){
		$dtListArray[$no] = $valx['no_plan'];
		$no++;
	}
	// print_r($dtListArray);
	$dtImplode	= "('".implode("','", $dtListArray)."')";
	// echo $dtImplode; exit;

	$data_detail  = "SELECT * FROM produksi_planning_data WHERE no_plan IN ".$dtImplode." AND `date` BETWEEN '".$tgl_awal."' AND '".$tgl_akhir."' GROUP BY `date` ORDER BY `date`";
  $result	      = mysqli_query($conn, $data_detail);
  $resultx	    = mysqli_query($conn, $data_detail);
  $data_num	    = mysqli_num_rows($result);

	$data_product = "SELECT * FROM produksi_planning_data WHERE no_plan IN ".$dtImplode." AND `date` BETWEEN '".$tgl_awal."' AND '".$tgl_akhir."' GROUP BY product ORDER BY product";
  $result_p	    = mysqli_query($conn, $data_product);


	$data_header2  = "SELECT * FROM produksi_planning WHERE costcenter='".$costcenter."'";
	$result_h2	    = mysqli_query($conn, $data_header2);
	$header	      = mysqli_fetch_array($result_h2);

  $date_now = date('Y-m-d', strtotime($tgl_awal));
	?>

	<table class="gridtable" border='1' width='100%' cellpadding='2'>
		<tr>
      <td width='70px' rowspan='3' style='padding:0px;'><img src='<?=$sroot;?>/assets/images/ori_logo.jpg' alt="" height='90' width='70' ></td>
			<td align='center'><b>PT. ORIGA MULIA FRP</b></td>
			<td width='15%'>Nomor Dok.</td>
			<td width='15%'></td>
		</tr>
		<tr>
			<td align='center' rowspan='2'><b><h3>LAMINATION PLANNING</h3></b></td>
			<td>Rev.</td>
			<td></td>
		</tr>
		<tr>
			<td>Tgl Berlaku</td>
			<td></td>
		</tr>
	</table>
	<table class="gridtable2" border='0' width='100%' >
		<tr>
			<td width='24%'>Costcenter</td>
			<td width='1%'>:</td>
			<td width='25%'><?= strtoupper(get_name_field('ms_costcenter', 'nama_costcenter', 'id_costcenter', $header['costcenter']));?></td>
			<td width='24%'></td>
			<td width='1%'></td>
			<td width='25%'></td>
		</tr>
		<tr>
			<td width='24%'>Dari Tanggal</td>
			<td width='1%'>:</td>
			<td width='25%'><?=date('l, d F Y',strtotime($tgl_awal));?></td>
			<td width='24%'></td>
			<td width='1%'></td>
			<td width='25%'></td>
		</tr>
		<tr>
			<td width='24%'>Sampai Tanggal</td>
			<td width='1%'>:</td>
			<td width='25%'><?=date('l, d F Y',strtotime($tgl_akhir));?></td>
			<td width='24%'></td>
			<td width='1%'></td>
			<td width='25%'></td>
		</tr>
	</table>
  <table class="gridtable" width='100%'>
    <thead>
      <tr>
				<th rowspan='2' style='vertical-align:middle; width:50px !important;'>No</th>
        <th rowspan='2' style='vertical-align:middle; width:150px !important;'>Project</th>
        <th rowspan='2' style='vertical-align:middle; width:200px !important;'>Product</th>
        <th rowspan='2' style='vertical-align:middle; width:100px !important;'>Qty Order</th>
        <th rowspan='2' style='vertical-align:middle; width:100px !important;'>Stock</th>
        <th rowspan='2' style='vertical-align:middle; width:100px !important;'>Shortages to Fulfill Orders</th>
        <?php
        while($value = mysqli_fetch_array($result)){
            echo "<th colspan='2' style='vertical-align:middle; width:150px !important;'>".date('d-M-Y', strtotime($value['date']))."</th>";
        }
        ?>
      </tr>
      <tr>
        <?php
        while($value = mysqli_fetch_array($resultx)){
            echo "<th style='vertical-align:middle; width:75px !important;'>Plan</th>";
            echo "<th style='vertical-align:middle; width:75px !important;'>Aktual</th>";
        }
        ?>
      </tr>
    </thead>
    <tbody>
      <?php
				$nox = 0;
        while($value = mysqli_fetch_array($result_p)){ $nox++;
            echo "<tr>";
						echo "<td align='center'>".$nox."</td>";
            echo "<td>".strtoupper(get_project_name($value['product']))."</td>";
            echo "<td>".strtoupper(get_name('ms_inventory_category2','nama','id_category2',$value['product']))."</td>";
            echo "<td align='center'>".$value['qty_order']."</td>";
            echo "<td align='center'>".$value['stock']."</td>";
            echo "<td align='center'>".$value['shortages']."</td>";

            $data_detail2  = "SELECT * FROM produksi_planning_data WHERE no_plan IN ".$dtImplode." AND `date` BETWEEN '".$tgl_awal."' AND '".$tgl_akhir."' GROUP BY `date` ORDER BY `date`";
            $result2	    = mysqli_query($conn, $data_detail2);
            while($value2 = mysqli_fetch_array($result2)){
                $q_weight = "SELECT qty FROM produksi_planning_data WHERE `date`='".$value2['date']."' AND product='".$value['product']."' AND no_plan='".$value['no_plan']."' LIMIT 1 ";
                $wight_h	= mysqli_query($conn, $q_weight);
              	$weight	  = mysqli_fetch_array($wight_h);
                $nil = (!empty($weight))?$weight['qty']:0;
                echo "<td align='center'>".number_format($nil)."</td>";
                echo "<td align='center'></td>";
            }
            echo "</tr>";
        }
       ?>
    </tbody>
  </table>

	<div id='space'></div>

	<p class='foot1'> <?php echo "<i>Printed by : ".ucwords(strtolower(get_name_field('users', 'username', 'id_user', $printby))).", ".$today." / ".$costcenter."</i>"; ?> </p>
	<br>
	<style type="text/css">
	@page {
		margin-top: 0.7cm;
		margin-left: 0.7cm;
		margin-right: 0.7cm;
		margin-bottom: 0.7cm;
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
		border-color: #666666;
		border-collapse: collapse;
	}
	table.gridtable {
		font-family: verdana,arial,sans-serif;
		font-size:12px;
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
		padding: 8px;
		border-style: solid;
		border-color: #666666;
		background-color: #ffffff;
	}
	table.gridtable td.cols {
		border-width: 1px;
		padding: 8px;
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

	table.cooltabs {
		font-size:12px;
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 5px 5px;
	}
	table.cooltabs th.reg {
		font-family: verdana,arial,sans-serif;
		border-radius: 5px 5px 5px 5px;
		background: #e3e0e4;
		padding: 5px;
	}
	table.cooltabs td.reg {
		font-family: verdana,arial,sans-serif;
		border-radius: 5px 5px 5px 5px;
		padding: 5px;
	}
	#cooltabs {
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 5px 5px;
		background: #e3e0e4;
		padding: 5px;
		width: 800px;
		height: 20px;
	}
	#cooltabs2{
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 5px 5px;
		background: #e3e0e4;
		padding: 5px;
		width: 180px;
		height: 10px;
	}
	#space{
		padding: 3px;
		width: 180px;
		height: 1px;
	}
	#cooltabshead{
		font-size:12px;
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 5px 5px 0 0;
		background: #dfdfdf;
		padding: 5px;
		width: 162px;
		height: 10px;
		float:left;
	}
	#cooltabschild{
		font-size:10px;
		font-family: verdana,arial,sans-serif;
		border-width: 1px;
		border-style: solid;
		border-radius: 0 0 5px 5px;
		padding: 5px;
		width: 162px;
		height: 10px;
		float:left;
	}
	p {
		margin: 0 0 0 0;
	}
	p.pos_fixed {
		font-family: verdana,arial,sans-serif;
		position: fixed;
		top: 30px;
		left: 230px;
	}
	p.pos_fixed2 {
		font-family: verdana,arial,sans-serif;
		position: fixed;
		top: 589px;
		left: 230px;
	}
	.barcode {
		padding: 1.5mm;
		margin: 0;
		vertical-align: top;
		color: #000044;
	}
	.barcodecell {
		text-align: center;
		vertical-align: middle;
		position: fixed;
		top: -10px;
		right: 10px;
	}
	.barcodecell2 {
		text-align: center;
		vertical-align: middle;
		position: fixed;
		top: 548px;
		right: 10px;
	}
	p.barcs {
		font-family: verdana,arial,sans-serif;
		font-size:11px;
		position: fixed;
		top: 38px;
		right: 115px;
	}
	p.barcs2 {
		font-family: verdana,arial,sans-serif;
		font-size:11px;
		position: fixed;
		top: 591px;
		right: 115px;
	}
	p.pt {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 62px;
		left: 5px;
	}
	p.alamat {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 71px;
		left: 5px;
	}
	p.tlp {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 80px;
		left: 5px;
	}
	p.pt2 {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 596px;
		left: 5px;
	}
	p.alamat2 {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 605px;
		left: 5px;
	}
	p.tlp2 {
		font-family: verdana,arial,sans-serif;
		font-size:7px;
		position: fixed;
		top: 614px;
		left: 5px;
	}
	#hrnew {
		border: 0;
		border-bottom: 1px dashed #ccc;
		background: #999;
	}
	</style>


	<?php

	$html = ob_get_contents();
	// $footer = "<p style='font-family: verdana,arial,sans-serif; font-size:10px;'><i>Printed by : ".ucfirst(strtolower($printby)).", ".$today."</i></p>";
	// exit;
	ob_end_clean();
	// $mpdf->SetWatermarkText('ORI Group');
	$mpdf->showWatermarkText = true;
	$mpdf->SetTitle('SPK Of Planning Produksi');
	// $mpdf->setHTMLFooter($footer);
	$mpdf->WriteHTML($html);
	$mpdf->Output($no_plan.'.pdf' ,'I');

	//exit;
	//return $attachment;
}

?>
