<?php
  set_time_limit(0);
  ob_start();
	$sroot 		= $_SERVER['DOCUMENT_ROOT'];
	include $sroot."origa_live/application/libraries/MPDF57/mpdf.php";
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
			<td><b><h2>BOM Head To Head</h2></b></td>
		</tr>
		</thead>
	</table>
	<br>
  <table class='header_style2' border='0' width='100%' cellpadding='2'>
		<tr>
			<td colspan='3' style='background-color: #ffffff !important;color: #0e5ca9;'><b><h3>PT  ORIGA MULIA FRP</h3></b></td>
		</tr>
		<tr style='background-color: #ffffff;'>
			<td width='120px'>Code</td>
			<td width='15px'>:</td>
			<td><?= $bom_hth;?></td>
		</tr>
		<tr style='background-color: #ffffff;'>
			<td style='vertical-align:top;'>Project / Product</td>
			<td style='vertical-align:top;'>:</td>
			<td><?= strtoupper(get_name('ms_inventory_category1','nama','id_category1',get_name('ms_inventory_category2','id_category1','id_category2', $header[0]->id_product)));?> / <?= strtoupper(get_name('ms_inventory_category2','nama','id_category2', $header[0]->id_product));?></td>
		</tr>
    <tr style='background-color: #ffffff;'>
			<td style='vertical-align:top;'>Price Origa</td>
			<td style='vertical-align:top;'>:</td>
			<td><?= number_format($header[0]->total_price2,5);?></td>
		</tr>
    <tr style='background-color: #ffffff;'>
			<td style='vertical-align:top;'>Price F-Tackle</td>
			<td style='vertical-align:top;'>:</td>
			<td><?= number_format($header[0]->total_price,5);?></td>
		</tr>
	</table>
  <br>
  <table class="gridtable" width='100%' border='0' cellpadding='2'>
    <thead>
      <tr class='bg-bluexyz'>
        <th class="text-left" rowspan='2' width='3%'>NO</th>
        <th class="text-left" rowspan='2' width='17%'>GROUP PROCESS</th>
        <th class="text-left" colspan='4'>ORIGA</th>
        <th class="text-left" colspan='4'>F-TACKLE</th>
      </tr>
      <tr class='bg-bluexyz'>
        <th class="text-left" width='23%'>MATERIAL NAME</th>
        <th class="text-right" width='6%'>QTY</th>
        <th class="text-right" width='5%'>PRICE</th>
        <th class="text-right" width='6%'>TOTAL</th>
        <th class="text-left" width='23%'>MATERIAL NAME</th>
        <th class="text-right" width='6%'>QTY</th>
        <th class="text-right" width='5%'>PRICE</th>
        <th class="text-right" width='6%'>TOTAL</th>
      </tr>
    </thead>
    <tbody>
      <?php
        $nomor = 0;
        $SUM = 0;
        $SUM2 = 0;
        foreach($detail_head AS $val => $valx){ $nomor++;
          $detail_origa = $this->db->query("
                                                SELECT
                                                  a.id AS id,
                                                  a.material AS material,
                                                  a.qty AS qty,
                                                  a.price AS price,
                                                  a.total AS total
                                                FROM
                                                  view_bom_hth_origa a
                                                WHERE
                                                  a.kode_bom_hth_detail = '".$valx['kode_bom_hth_detail']."'")->result_array();
          $detail_ftackle = $this->db->query("
                                                SELECT
                                                  a.id AS id,
                                                  a.material AS material,
                                                  a.qty AS qty,
                                                  a.price AS price,
                                                  a.total AS total
                                                FROM
                                                  view_bom_hth_ftackle a
                                                WHERE
                                                  a.kode_bom_hth_detail = '".$valx['kode_bom_hth_detail']."'")->result_array();

          echo "<tr>";
            echo "<td align='center'>".$nomor."</td>";
            echo "<td>".strtoupper($valx['group_material'])."</td>";
            echo "<td align='left'>";
              foreach($detail_origa AS $val2 => $val2x){
                echo strtoupper(get_name('ms_material','nm_material','code_material', $val2x['material']))."<br>";
              }
            echo "</td>";
            echo "<td align='right'>";
              foreach($detail_origa AS $val2 => $val2x){
                echo number_format($val2x['qty'],5)."<br>";
              }
            echo "</td>";
            echo "<td align='right'>";
              foreach($detail_origa AS $val2 => $val2x){
                echo number_format($val2x['price'],2)."<br>";
              }
            echo "</td>";
            echo "<td align='right'>";
              foreach($detail_origa AS $val2 => $val2x){
                $SUM += $val2x['total'];
                echo number_format($val2x['total'],5)."<br>";
              }
            echo "</td>";
            echo "<td align='left'>";
              foreach($detail_ftackle AS $val2 => $val2x){
                echo strtoupper($val2x['material'])."<br>";
              }
            echo "</td>";
            echo "<td align='right'>";
              foreach($detail_ftackle AS $val2 => $val2x){
                echo number_format($val2x['qty'],5)."<br>";
              }
            echo "</td>";
            echo "<td align='right'>";
              foreach($detail_ftackle AS $val2 => $val2x){
                echo number_format($val2x['price'],2)."<br>";
              }
            echo "</td>";
            echo "<td align='right'>";
              foreach($detail_ftackle AS $val2 => $val2x){
                $SUM2 += $val2x['total'];
                echo number_format($val2x['total'],5)."<br>";
              }
            echo "</td>";
          echo "</tr>";
          // $detail_head_det = $this->db->query("
          //                                       SELECT
          //                                         a.id AS id,
          //                                         a.material AS material,
          //                                         a.qty AS qty,
          //                                         a.price AS price,
          //                                         a.total AS total,
          //                                         b.id AS id2,
          //                                         b.material AS material2,
          //                                         b.qty AS qty2,
          //                                         b.price AS price2,
          //                                         b.total AS total2
          //                                       FROM
          //                                         view_bom_hth_origa a
          //                                       LEFT JOIN view_bom_hth_ftackle b ON a.kode_bom_hth_detail = b.kode_bom_hth_detail
          //                                       WHERE
          //                                         a.kode_bom_hth_detail = '".$valx['kode_bom_hth_detail']."'
          //                                         OR b.kode_bom_hth_detail = '".$valx['kode_bom_hth_detail']."'")->result_array();
          // foreach($detail_head_det AS $val2 => $valx2){
          //   $SUM += $valx2['total'];
          //   $SUM2 += $valx2['total2'];
          //   echo "<tr>";
          //     echo "<td align='center'></td>";
          //     echo "<td></td>";
          //     echo "<td>".strtoupper(get_name('ms_material','nm_material','code_material', $valx2['material']))."</td>";
          //     echo "<td align='right'>".number_format($valx2['qty'],5)."</td>";
          //     echo "<td align='right'>".number_format($valx2['price'],2)."</td>";
          //     echo "<td align='right'>".number_format($valx2['total'],5)."</td>";
          //     echo "<td>".strtoupper($valx2['material2'])."</td>";
          //     echo "<td align='right'>".number_format($valx2['qty2'],5)."</td>";
          //     echo "<td align='right'>".number_format($valx2['price2'],2)."</td>";
          //     echo "<td align='right'>".number_format($valx2['total2'],5)."</td>";
          //   echo "</tr>";
          // }
        }
        echo "<tr>";
          echo "<td></td>";
          echo "<td colspan='4'><b>SUM PRICE</b></td>";
          echo "<td align='right'><b>".number_format($SUM,5)."</b></td>";
          echo "<td colspan='3'></td>";
          echo "<td align='right'><b>".number_format($SUM2,5)."</b></td>";
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
  $html = ob_get_contents();
	$footer = "<p style='font-family: verdana,arial,sans-serif; font-size:10px;'><i>Printed by : ".ucfirst(strtolower(get_name('users', 'username', 'id_user', $printby))).", ".date('d-M-Y H:i:s')."</i></p>";
	// exit;
	ob_end_clean();

	$mpdf->showWatermarkText = true;
	$mpdf->SetTitle('BOM Head To Head');
  // $mpdf->AddPage();
	$mpdf->SetFooter($footer);
	$mpdf->WriteHTML($html);
	$mpdf->Output("BOM Head To Head ".$bom_hth." ".date('dmYHis').".pdf" ,'I');
