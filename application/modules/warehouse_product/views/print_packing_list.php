<?php
$sroot 		= $_SERVER['DOCUMENT_ROOT'].'/origa_dev';
// $mpdf=new mPDF('utf-8','A4');
$mpdf=new mPDF('utf-8','A4-L');

set_time_limit(0);
ini_set('memory_limit','1024M');
//Beginning Buffer to save PHP variables and HTML tags
ob_start();
date_default_timezone_set('Asia/Jakarta');
$today = date('Y-m-d H:i:s');

$projectX = "('".str_replace("-","','",$no_so)."')";
$data_detail  = $this->db->query("SELECT
                                    a.*,
                                    SUM(a.qty_order) AS propose,
                                    MAX(a.delivery_date) AS delivery_date,
                                    b.length,
                                    b.high,
                                    b.wide,
                                    b.per_box,
                                    b.group_packing,
                                    b.weight_per_product,
                                    c.shipment
                                  FROM
                                    sales_order_detail a
                                    LEFT JOIN ms_inventory_category2 b ON a.product=b.id_category2
                                    LEFT JOIN ms_inventory_category1 d ON d.id_category1=b.id_category1
                                    LEFT JOIN sales_order_header c ON a.no_so = c.no_so
                                  WHERE
                                    a.no_so IN ".$projectX."
                                    AND a.qty_order > 0
                                    AND a.product <> 'I2000047'
                                  GROUP BY
                                    a.product
                                  ORDER BY
                                    d.urut ASC,
                                    a.product ASC,
                                    b.group_packing ASC")->result_array();
?>

<table class='gridtable2' border='1' width='100%' cellpadding='2'>
  <tr>
    <td width='80px' rowspan='3' style='padding:0px;'><img src='<?=$sroot;?>/assets/images/ori_logo.jpg' alt="" height='100' width='80' ></td>
    <td align='left'  class='header_style_company bold color_req' style='vertical-align:top; padding-left:20px;'><h5>PT ORIGA MULIA FRP</h5></td>
    <td align='right'  class='header_style_company bold color_req' style='vertical-align:top;'></td>
  </tr>
  <tr>
    <td align='left'  class='header_style_company bold color_req' style='vertical-align:top; padding-left:20px;'><h5>PACKING LIST</h5></td>
    <td align='right'  class='header_style_company bold color_req' style='vertical-align:top;'></td>
  </tr>
  <tr>
    <td align='left'  class='header_style_company bold color_req' style='vertical-align:top; padding-left:20px;'><h6>Shipping Schedule - Shipment <?= strtoupper($data_detail[0]['shipment']);?></h6></td>
    <td align='right'  class='header_style_company bold color_req' style='vertical-align:top;'><h6>Container Stuffing on <?= date('d F Y', strtotime($data_detail[0]['delivery_date']));?></h6></td>
  </tr>
</table><br>
<?php
echo "<table class='gridtable' width='100%' border='1' cellpadding='2'>";
  echo "<thead>";
    echo "<tr>";
      echo "<th align='left' rowspan='2' style='vertical-align:middle;'>PROJECT</th>";
      echo "<th align='left' rowspan='2' style='vertical-align:middle;' width='15%'>PRODUCT</th>";
      echo "<th align='right' rowspan='2' style='vertical-align:middle;' width='4%'>QTY</th>";
      echo "<th align='left' rowspan='2' style='vertical-align:middle;' width='10%'>CARTON BOX SIZE</th>";
      echo "<th align='right' rowspan='2' style='vertical-align:middle;' width='4%'>CONTENT PER CARTON BOX</th>";
      echo "<th align='right' rowspan='2' style='vertical-align:middle;' width='4%'>CARTON BOX QTY</th>";
      echo "<th align='right' rowspan='2' style='vertical-align:middle;' width='6%'>PAPER LABEL NO</th>";
      echo "<th align='right' rowspan='2' style='vertical-align:middle;' width='6%'>WEIGHT PER PRODUCT PIECE</th>";
      echo "<th align='center' colspan='2' style='vertical-align:middle;'>WEIGHT (KG)</th>";
      echo "<th align='center' colspan='5' style='vertical-align:middle;'>CONTAINER CAPACITY</th>";
    echo "</tr>";
    echo "<tr>";
      echo "<th align='right' style='vertical-align:middle;' width='4%'>NETTO</th>";
      echo "<th align='right' style='vertical-align:middle;' width='4%'>GROSS</th>";
      echo "<th align='right' style='vertical-align:middle;' width='6%'>LENGTH</th>";
      echo "<th align='right' style='vertical-align:middle;' width='4%'>WIDE</th>";
      echo "<th align='right' style='vertical-align:middle;' width='4%'>HIGH</th>";
      echo "<th align='right' style='vertical-align:middle;' width='6%'>CUB</th>";
      echo "<th align='right' style='vertical-align:middle;' width='4%'>TOTAL CUB</th>";
    echo "</tr>";
  echo "</thead>";
  echo "<tbody>";
    $SUM_BOX = 0;
    foreach($data_detail AS $val => $valx){
      if($valx['propose'] > 0){
        $box_qty = 0;
        if($valx['propose'] != 0 AND $valx['per_box'] != 0){
          $box_qty = number_format($valx['propose'] / $valx['per_box'],2);
        }

        $car_qty = ceil($box_qty);

        $cub = ($valx['length'] * $valx['wide'] * $valx['high']) / 1000000000;

        $netto = $valx['propose'] * $valx['weight_per_product'] / 1000;
        $gross = (($valx['propose'] * $valx['weight_per_product']) + 250) / 1000;

        $SUM_BOX += ceil($box_qty);

        $val_min = $val - 1;
        $val_next = $val + 1;
        $nama_project_bef = get_project_name($data_detail[$val_min]['product']);

        $nama_project = '';
        if($nama_project_bef <> get_project_name($valx['product'])){
          $nama_project = get_project_name($valx['product']);
        }


        $group_pack_bef = get_name('ms_inventory_category2','group_packing','id_category2',$data_detail[$val_min]['product']);
        $group_pack_next = get_name('ms_inventory_category2','group_packing','id_category2',$data_detail[$val_next]['product']);



        $group_cols = '';
        if($group_pack_next == get_name('ms_inventory_category2','group_packing','id_category2',$valx['product']) OR $valx['product'] == 'I2000040' OR $valx['product'] == 'I2000041' OR $valx['product'] == 'I2000042'){
          $group_cols = "rowspan='2'";
        }

        $group_pack = '';
        $group_box = '';
        $kolom = '';
        $kolom_box = '';
        $kolom_dimensi = '';
        if($group_pack_bef <> get_name('ms_inventory_category2','group_packing','id_category2',$valx['product']) OR $valx['product'] == 'I2000040' OR $valx['product'] == 'I2000041' OR $valx['product'] == 'I2000042'){
          $group_pack = number_format($valx['per_box']);
          $group_box  = ceil($box_qty);
          $kolom = "<td align='center' ".$group_cols.">".$group_pack."</td>";
          $kolom_box = "<td align='center' ".$group_cols.">".$group_box."</td>";
          $kolom_dimensi = "<td align='left' ".$group_cols.">".get_dimensi($valx['product'])."</td>";
        }

        echo "<tr>";
          echo "<td>".strtoupper($nama_project)."</td>";
          echo "<td>".strtoupper(get_name('ms_inventory_category2','nama','id_category2',$valx['product']))."</td>";
          echo "<td align='center'>".number_format($valx['propose'])."</td>";
          echo $kolom_dimensi;
          echo $kolom;
          echo $kolom_box;
          echo "<td align='right'>-</td>";
          echo "<td align='right'>".number_format($valx['weight_per_product'],2)."</td>";
          echo "<td align='right'>".number_format($netto,2)."</td>";
          echo "<td align='right'>".number_format($gross,2)."</td>";
          echo "<td align='right'>".number_format($valx['length'])."</td>";
          echo "<td align='right'>".number_format($valx['wide'])."</td>";
          echo "<td align='right'>".number_format($valx['high'])."</td>";
          echo "<td align='right'>".number_format($cub, 6)."</td>";
          echo "<td align='right'>".number_format($cub * $car_qty, 3)."</td>";
        echo "</tr>";

        if($valx['product'] == 'I2000040' OR $valx['product'] == 'I2000041' OR $valx['product'] == 'I2000042'){

          $netto2 = $valx['propose'] * get_name('ms_inventory_category2','weight_per_product','id_category2','I2000047') / 1000;
          $gross2 = (($valx['propose'] * get_name('ms_inventory_category2','weight_per_product','id_category2','I2000047')) + 250) / 1000;

          $cub2 = (get_name('ms_inventory_category2','length','id_category2','I2000047') * get_name('ms_inventory_category2','wide','id_category2','I2000047') * get_name('ms_inventory_category2','high','id_category2','I2000047')) / 1000000000;
          echo "<tr>";
            echo "<td></td>";
            echo "<td>".strtoupper(get_name('ms_inventory_category2','nama','id_category2','I2000047'))."</td>";
            echo "<td align='center'>".number_format($valx['propose'])."</td>";

            echo "<td align='right'>-</td>";
            echo "<td align='right'>".number_format(get_name('ms_inventory_category2','weight_per_product','id_category2','I2000047'),2)."</td>";
            echo "<td align='right'>".number_format($netto2,2)."</td>";
            echo "<td align='right'>".number_format($gross2,2)."</td>";
            echo "<td align='right'>".number_format(get_name('ms_inventory_category2','length','id_category2','I2000047'))."</td>";
            echo "<td align='right'>".number_format(get_name('ms_inventory_category2','wide','id_category2','I2000047'))."</td>";
            echo "<td align='right'>".number_format(get_name('ms_inventory_category2','high','id_category2','I2000047'))."</td>";
            echo "<td align='right'>".number_format($cub2, 6)."</td>";
            echo "<td align='right'>".number_format(0, 3)."</td>";
          echo "</tr>";
        }
      }
    }
  echo "</tbody>";
echo "</table>";

$temp_print = $this->db->query("SELECT * FROM temp_print WHERE category='packing list'")->result();
?>
<br><br><br>
<table class="gridtable2" width='100%' border='0' cellpadding='2'>
	<tr>
		<td width='20%' align='center'>Prepared by,</td>
		<td width='10%' align='left'></td>
		<td width='40%' align='center'><?= ucwords(strtolower($temp_print[0]->city));?>, <?= date('d F Y', strtotime($today));?><br>Checked by,</td>
		<td width='10%' align='left'></td>
		<td width='20%' align='center'>Acknowledged by,</td>
	</tr>
	<tr>
		<td height='60px'></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td align='center'><?= ucwords(strtolower($temp_print[0]->prepared_by));?></td>
		<td align='center'></td>
		<td align='center'><?= ucwords(strtolower($temp_print[0]->checked_by));?></td>
		<td align='center'></td>
		<td align='center'><?= ucwords(strtolower($temp_print[0]->acknowleged_by));?></td>
	</tr>
</table>
<br><br><br>
<p><?= $temp_print[0]->ket1;?></p>
<p><?= $temp_print[0]->ket2;?></p>
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
    vertical-align: top;
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
		font-size:9px;
		color:#333333;
		border: 1px solid #dddddd;
    border-width: 1px solid;
		border-color: #dddddd;
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
	/* table.gridtable tr:nth-child(even) {
		background-color: #f2f2f2;
	} */
	table.gridtable td {
		padding: 8px;
    border-width: 1px;
		border-style: solid;
		border-color: #dddddd;
	}
	table.gridtable td.cols {
		padding: 8px;
    border-width: 1px;
		border-style: solid;
		border-color: #dddddd;
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

	table.gridtable4 {
		font-family: verdana,arial,sans-serif;
		font-size:9px;
		color:#333333;
	}
	table.gridtable4 td {
		padding: 3px;
		border-color: #dddddd;
	}
	table.gridtable4 td.cols {
		padding: 3px;
	}
	</style>

<?php

$html = ob_get_contents();
// exit;
ob_end_clean();
// $mpdf->SetWatermarkText('ORI Group');
$mpdf->showWatermarkText = true;
$mpdf->SetTitle("Packing List ".$no_so);
$mpdf->AddPage();
$mpdf->WriteHTML($html);
$mpdf->Output('packing list '.$no_so.'/'.date('ymdhis', strtotime($today)).'.pdf' ,'I');
?>
