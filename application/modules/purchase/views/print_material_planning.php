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

  <table class="gridtable2" border='0' width='100%' cellpadding='2'>
		<tr>
			<td style='padding:0px;'><img src='<?=$sroot;?>/origa_live/assets/images/ori_logo.jpg' alt="" height='70' width='60' ></td>
      <td colspan='2'></td>
    </tr>
		<tr>
			<td align='left' colspan='3'><b><h3>PT ORIGA MULIA FRP</h3></b></td>
		</tr>
    <tr>
			<td align='center' colspan='3'><b><h2>PURCHASE REQUISITION</h2></b></td>
		</tr>
    <tr>
			<td align='left' width='70%'><p>Requisition date :</p></td>
      <td align='left' width='10%'><p>PR No.</p></td>
      <td align='left' width='20%'>:</p></td>
		</tr>
    <tr>
			<td align='left'></td>
      <td align='left'><p>PR Date</p></td>
      <td align='left'><p>:</p></td>
		</tr>
    <tr>
			<td align='left'></td>
      <td align='left'><p><img src='<?=$sroot;?>/origa_live/assets/images/kotak.png' style='padding-bottom:-5px;' alt="" height='20' width='30'><span style="vertical-align:middle;"> URGENT</span></p></td>
      <td align='right'><p><img src='<?=$sroot;?>/origa_live/assets/images/kotak.png' style='padding-bottom:-5px;' alt="" height='20' width='30'><span style="vertical-align:middle;"> NORMAL</span></p></td>
    </tr>
	</table>
  <br>
  <table class='gridtable5' width='100%' border='0' cellpadding='2'>
		<thead>
			<tr>
				<th width='3%'>No.</th>
				<th width='21%'>Item Code</th>
				<th width='29%'>Item / Item Description</th>
				<th width='7%'>Qty</th>
				<th width='7%'>Unit</th>
				<th width='7%'>Stock</th>
				<th width='10%'>Project</th>
        <th width='6%'>Due Date</th>
        <th width='10%'>Remark</th>
			</tr>
		</thead>
    <tbody>
      <?php
        foreach($detail AS $val => $valx){
          if($valx['material'] <> '0'){
            $request          = get_edit_plan_mat($no_req,$valx['material']);
            if($request > 0){
              $no++;
              $stock = get_name('warehouse_stock','qty_stock','id_material',$valx['material']);
              $book  = get_name('warehouse_stock','qty_booking','id_material',$valx['material']);

              $stock_pusat      = get_stock_material($valx['material'], '1');
              $stock_subgudang  = get_stock_material($valx['material'], '2');
              $stock            = $stock_pusat + $stock_subgudang;


              echo "<tr>";
                echo "<td class='mid' align='center'>".$no."</td>";
                echo "<td class='mid' align='left'>".strtoupper(get_name('ms_material','nm_material','code_material',$valx['material']))."</td>";
                echo "<td class='mid' align='right'></td>";
                echo "<td class='mid' align='right'>".number_format($request,2)."</td>";
                echo "<td class='mid' align='center'>".strtoupper(get_name('ms_material','unit','code_material',$valx['material']))."</td>";
                echo "<td class='mid' align='right'>".number_format($stock,2)."</td>";
                echo "<td class='mid' align='right'></td>";
                echo "<td class='mid' align='right'></td>";
                echo "<td class='mid' align='right'></td>";
              echo "</tr>";
            }
          }
        }
       ?>
    </tbody>
	</table>
  <table class='gridtable5' width='100%' border='0' cellpadding='2' style='margin-top:1px;'>
		<thead>
      <tr>
				<td align='center'>Request By,</td>
				<td align='center' colspan='4'>Checked By,</td>
				<td align='center' colspan='3'>Approved By,</td>
			</tr>
			<tr>
				<td width='12%' style='height:70px;'></td>
				<td width='12%'></td>
				<td width='12%'></td>
				<td width='12%'></td>
				<td width='12%'></td>
				<td width='14%'></td>
				<td width='13%'></td>
        <td width='13%'></td>
			</tr>
      <tr>
				<td align='center'>Admin</td>
				<td align='center'>Warehouse</td>
				<td align='center'>Head of Dept.</td>
				<td align='center'>Engineering***</td>
				<td align='center'>SCM</td>
				<td align='center'>Factory Manager</td>
				<td align='center'>Cost Control</td>
        <td align='center'>Director**</td>
			</tr>
		</thead>
	</table>
  <br>
  <p>Note :</p>
  <p><?= $temp_print[0]->ket1;?></p>
  <p><?= $temp_print[0]->ket2;?></p>
  <p><?= $temp_print[0]->ket3?></p>
  <p><?= $temp_print[0]->ket4;?></p>
  <style type="text/css">
    @page {
      margin-top: 1cm;
      margin-left: 1cm;
      margin-right: 1cm;
      margin-bottom: 1cm;
    }
    p{
  		font-family: Arial, Helvetica, sans-serif;
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

    table.gridtable2 {
  		font-family: Arial, Helvetica, sans-serif;
  		font-size:12px;
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

    /* TABLE ISI */
    table.gridtable5 {
  		font-family: verdana,arial,sans-serif;
  		font-size:9px;
  		color:#333333;
  		border: 1px solid #404040;
  		border-collapse: collapse;
  	}
  	table.gridtable5 th {
  		padding: 6px;
  		background-color: #f5f5f5;
  		color: black;
  		border-color: #404040;
  		border-style: solid;
  		border-width: 1px;
  	}
  	table.gridtable5 th.head {
  		padding: 6px;
  		background-color: #f5f5f5;
  		color: black;
  		border-color: #404040;
  		border-style: solid;
  		border-width: 1px;
  	}
  	table.gridtable5 td {
  		border-width: 1px;
  		padding: 6px;
  		border-style: solid;
  		border-color: #404040;
  	}
  	table.gridtable5 td.cols {
  		border-width: 1px;
  		padding: 6px;
  		border-style: solid;
  		border-color: #404040;
  	}
  </style>

	<?php
  $html = ob_get_contents();
	// exit;
	ob_end_clean();

	$mpdf->showWatermarkText = true;
	$mpdf->SetTitle('PR Material');
  // $mpdf->AddPage();
	$mpdf->WriteHTML($html);
	$mpdf->Output("PR Material ".$no_req." ".date('dmYHis').".pdf" ,'I');
