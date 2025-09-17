<?php
set_time_limit(0);
ob_start();

$Successno			=0;
$ErrorInfo			=0;
$sroot 				= $_SERVER['DOCUMENT_ROOT'];

function PrintMaterialPlanning($Nama_APP, $koneksi, $printby, $no_so){

	$KONN = array(
		'user' => $koneksi['hostuser'],
		'pass' => $koneksi['hostpass'],
		'db'   => $koneksi['hostdb'],
		'host' => $koneksi['hostname']
	);

	$conn = mysqli_connect($KONN['host'],$KONN['user'],$KONN['pass']);
	mysqli_select_db($conn, $KONN['db']);

	$sroot 		= $_SERVER['DOCUMENT_ROOT'];
	include $sroot."/origa_live/application/libraries/MPDF57/mpdf.php";
	// $mpdf=new mPDF('utf-8','A4');
	$mpdf=new mPDF('utf-8','A4-L');

	set_time_limit(0);
	ini_set('memory_limit','1024M');

	//Beginning Buffer to save PHP variables and HTML tags
	ob_start();
	date_default_timezone_set('Asia/Jakarta');
	$today = date('l, d F Y [H:i:s]');

	$qBQ 		= "SELECT * FROM material_planning WHERE no_plan = '".$no_so."' ";
	$dResulBQ	= mysqli_query($conn, $qBQ);
	$dHeaderBQ	= mysqli_fetch_array($dResulBQ);

  $data2 	  = "SELECT * FROM material_planning_data WHERE no_plan='".$no_so."' GROUP BY material ORDER BY material";

  $data	    = mysqli_query($conn, $data2);
  $data_num	= mysqli_num_rows($data);
  // echo $data_num;
  $data1BTS 	= "SELECT * FROM material_planning_data WHERE no_plan='".$no_so."' GROUP BY product ORDER BY product";
	$result1BTS	= mysqli_query($conn, $data1BTS);

	echo "<htmlpageheader>";
	?>

	<table class='header_style' border='0' width='100%' cellpadding='2'>
		<thead>
		<tr>
			<td><b><h2>Request Material Planning</h2></b></td>
		</tr>
		</thead>
	</table>
	<br>

	<table class='header_style2' border='0' width='100%' cellpadding='2'>
		<tr>
			<td colspan='3' style='background-color: #ffffff !important;color: #0e5ca9;'><b><h3>PT  ORIGA MULIA FRP</h3></b></td>
		</tr>
		<tr style='background-color: #ffffff;'>
			<td width='100px'>Code</td>
			<td width='15px'>:</td>
			<td><?= $no_so;?></td>
		</tr>
		<tr style='background-color: #ffffff;'>
			<td style='vertical-align:top;'>Month Planning</td>
			<td style='vertical-align:top;'>:</td>
			<td><?= date('F Y',strtotime($dHeaderBQ['tahun'].'-'.$dHeaderBQ['bulan'].'-01')); ?></td>
		</tr>
	</table>
	<br>
	<?php echo "<htmlpageheader>";?>
	<table class="gridtable" width='100%' border='0' cellpadding='2'>
		<tbody>
			<tr>
				<td style='background-color: #0e5ca9 !important;color: white;' colspan='<?=$data_num + 5;?>'><b>MATERIAL PLANNING</b></td>
			</tr>
			<tr class='bg-bluexyz'>
				<th class="text-center" rowspan='2' width='11%'>Project</th>
				<th class="text-center" rowspan='2' width='13%'>Product</th>
				<th class="text-center" rowspan='2' width='4%'>Qty Propose</th>
				<th class="text-center" colspan='<?=$data_num;?>'>Material Name</th>
			</tr>
      <tr class='bg-bluexyz'>
        <?php
        $siz = 72/$data_num;
        while($value = mysqli_fetch_array($data)){
            echo "<th align='left' style='vertical-align:top; text-align: left;' width='".$siz."%'>".strtoupper(get_name('ms_material','nm_material','code_material',$value['material']))."</th>";
        }
        ?>
      </tr>
		</tbody>
    <tbody>
			<?php
			$SUM = 0;
			$no = 0;
			while($valx = mysqli_fetch_array($result1BTS)){
				$no++;
        $SUM += $valx['qty_order'];
				echo "<tr>";
					echo "<td align='left'>".strtoupper(get_project_name($valx['product']))."</td>";
					echo "<td align='left'>".strtoupper(get_name('ms_inventory_category2','nama','id_category2',$valx['product']))."</td>";
					echo "<td align='center'>".$valx['qty_order']."</td>";
          $data3 	  = "SELECT * FROM material_planning_data WHERE no_plan='".$no_so."' GROUP BY material ORDER BY material";
          $data3x	    = mysqli_query($conn, $data3);
          while($value2 = mysqli_fetch_array($data3x)){
              $q_weight = "SELECT weight FROM material_planning_data WHERE material='".$value2['material']."' AND product='".$valx['product']."' AND no_plan='".$valx['no_plan']."' LIMIT 1 ";
              $dResulBQ	= mysqli_query($conn, $q_weight);
            	$weight	= mysqli_fetch_array($dResulBQ);
              $nil = (!empty($weight['weight']))?$weight['weight']:0;
              echo "<td align='right'>".number_format($nil,2)."</td>";
          }
				echo "</tr>";
			}

      $data2 	  = "SELECT * FROM material_planning_data WHERE no_plan='".$no_so."' GROUP BY material ORDER BY material";
      $data	    = mysqli_query($conn, $data2);
      echo "<tr>";
        echo "<td></td>";
        echo "<td colspan='3'>TOTAL KEBUTUHAN</td>";
        while($value2 = mysqli_fetch_array($data)){
          $q_weight = "SELECT weight FROM material_planning_footer WHERE material='".$value2['material']."' AND category='sum' AND no_plan='".$no_so."' LIMIT 1 ";
          $dResulBQ	= mysqli_query($conn, $q_weight);
          $weight	= mysqli_fetch_array($dResulBQ);
          echo "<td align='right'>".number_format($weight['weight'],2)."</td>";
        }
      echo "</tr>";
			?>
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
		font-size:9px;
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
	// $footer = "<p style='font-family: verdana,arial,sans-serif; font-size:10px;'><i>Printed by : ".ucfirst(strtolower(get_name('users', 'username', 'id_user', $printby))).", ".$today."</i></p>";

	// exit;
	$html = ob_get_contents();
	ob_end_clean();
	// flush();
	// $mpdf->SetWatermarkText('ORI Group');

	$mpdf->showWatermarkText = true;
	$mpdf->SetTitle('Material Planning');
	// $mpdf->AddPage('L');
	$mpdf->SetFooter($footer);
	$mpdf->WriteHTML($html);
	$mpdf->Output("Material Planning ".$no_so." ".date('dmYHis').".pdf" ,'I');

	//exit;
	//return $attachment;
}


?>
