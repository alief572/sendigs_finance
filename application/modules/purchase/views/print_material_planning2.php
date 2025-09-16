<?php
  set_time_limit(0);
  ob_start();
	$sroot 		= $_SERVER['DOCUMENT_ROOT'];
	include $sroot."/origa_live/application/libraries/MPDF57/mpdf.php";
	$mpdf=new mPDF('utf-8','A4-L');

	set_time_limit(0);
	ini_set('memory_limit','1024M');

	ob_start();
	date_default_timezone_set('Asia/Jakarta');
	$today = date('l, d F Y [H:i:s]');

	echo "<htmlpageheader>";
	?>

	<table class='header_style' border='0' width='100%' cellpadding='2'>
		<thead>
		<tr>
			<td><b><h2>PURCHASE REQUEST MATERIAL</h2></b></td>
		</tr>
		</thead>
	</table>
	<br>
  <table class='header_style2' border='0' width='100%' cellpadding='2'>
		<tr>
			<td colspan='3' style='background-color: #ffffff !important;color: #0e5ca9;'><b><h3>PT  ORIGA MULIA FRP</h3></b></td>
		</tr>
		<tr style='background-color: #ffffff;'>
			<td width='120px'>No. Request</td>
			<td width='15px'>:</td>
			<td><?= $no_req;?></td>
		</tr>
		<tr style='background-color: #ffffff;'>
			<td style='vertical-align:top;'>Month</td>
			<td style='vertical-align:top;'>:</td>
			<td><?=$today3;?> - <?=$nextO3;?></td>
		</tr>
	</table>
  <br>
  <table class="gridtable" width='100%' border='0' cellpadding='2'>
    <thead>
      <tr class='bg-bluexyz'>
        <th class='text-center' rowspan="2" style='width: 4%;vertical-align:middle;'>#</th>
        <th class='text-center' rowspan="2" style='vertical-align:middle;'>Material Name</th>
        <th class='text-center' rowspan="2" style='width: 7%;vertical-align:middle;'>Stock SubGudang</th>
        <th class='text-center' rowspan="2" style='width: 7%;vertical-align:middle;'>Stock Pusat</th>
        <th class='text-center' colspan="2" style='width: 12%;'><?=$today3;?></th>
        <th class='text-center' colspan="2" style='width: 12%;'><?=$nextM3;?></th>
        <th class='text-center' colspan="2" style='width: 12%;'><?=$nextN3;?></th>
        <th class='text-center' colspan="2" style='width: 12%;'><?=$nextO3;?></th>
        <th class='text-center' rowspan="2" style='width: 7%;vertical-align:middle;'>Kebutuhan<br>4 Bulan</th>
        <th class='text-center' rowspan="2" style='width: 7%;vertical-align:middle;'>Qty Order</th>
      </tr>
      <tr class='bg-bluexyz'>
        <th class='text-center' style='width: 6%;'>Booking</th>
        <th class='text-center' style='width: 6%;'>Free</th>
        <th class='text-center' style='width: 6%;'>Booking</th>
        <th class='text-center' style='width: 6%;'>Free</th>
        <th class='text-center' style='width: 6%;'>Booking</th>
        <th class='text-center' style='width: 6%;'>Free</th>
        <th class='text-center' style='width: 6%;'>Booking</th>
        <th class='text-center' style='width: 6%;'>Free</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $no = 0;
      $readonly = '';
      if($tanda == 'view'){
        $readonly = 'disabled';
      }
      foreach($detail AS $val => $valx){
        if($valx['material'] <> '0'){
          $no++;
          $stock = get_name('warehouse_stock','qty_stock','id_material',$valx['material']);
          $book  = get_name('warehouse_stock','qty_booking','id_material',$valx['material']);

          $stock_pusat  = get_stock_material($valx['material'], '1');


          $req  = get_edit_plan_mat($no_req,$valx['material']);

          $get1  = get_plan_mat($valx['material'],$today1,$today2);
          $get2  = get_plan_mat($valx['material'],$nextM1,$nextM2);
          $get3  = get_plan_mat($valx['material'],$nextN1,$nextN2);
          $get4  = get_plan_mat($valx['material'],$nextO1,$nextO2);

          $stocking =  get_stock_material($valx['material'], '2');

          $stocking2x =  $stocking + $stock_pusat;
          $stocking1 =  $stocking2x - $get1;
          $stocking2 =  $stocking1 - $get2;
          $stocking3 =  $stocking2 - $get3;
          $stocking4 =  $stocking3 - $get4;

          $sum_booking = $get1 + $get2 + $get3 + $get4;
          echo "<tr>";
           echo "<td class='mid' align='center'>".$no."</td>";
           echo "<td class='mid' align='left'>".strtoupper(get_name('ms_material','nm_material','code_material',$valx['material']))."</td>";
           echo "<td class='mid' align='right'>".number_format($stocking, 2)."</td>";
           echo "<td class='mid' align='right'>".number_format($stock_pusat, 2)."</td>";
           echo "<td class='mid' align='right'>".number_format($get1,2)."</td>";
           echo "<td class='mid' align='right'>".number_format($stocking1, 2)."</td>";
           echo "<td class='mid' align='right'>".number_format($get2,2)."</td>";
           echo "<td class='mid' align='right'>".number_format($stocking2, 2)."</td>";
           echo "<td class='mid' align='right'>".number_format($get3,2)."</td>";
           echo "<td class='mid' align='right'>".number_format($stocking3, 2)."</td>";
           echo "<td class='mid' align='right'>".number_format($get4,2)."</td>";
           echo "<td class='mid' align='right'>".number_format($stocking4, 2)."</td>";
           echo "<td class='mid' align='right'>".number_format($sum_booking, 2)."</td>";
           echo "<td class='mid' align='right'>".number_format($req)."</td>";
          echo "</tr>";
        }
      }
      // data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''
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
  $html = ob_get_contents();
	$footer = "<p style='font-family: verdana,arial,sans-serif; font-size:10px;'><i>Printed by : ".ucfirst(strtolower(get_name('users', 'username', 'id_user', $printby))).", ".date('d-M-Y H:i:s')."</i></p>";
	// exit;
	ob_end_clean();

	$mpdf->showWatermarkText = true;
	$mpdf->SetTitle('PR Material');
  // $mpdf->AddPage();
	$mpdf->SetFooter($footer);
	$mpdf->WriteHTML($html);
	$mpdf->Output("PR Material ".$no_req." ".date('dmYHis').".pdf" ,'I');
