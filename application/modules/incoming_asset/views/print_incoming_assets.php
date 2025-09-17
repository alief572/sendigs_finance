<?php

// $sroot 		= $_SERVER['DOCUMENT_ROOT'];
// include $sroot."/application/libraries/MPDF57/mpdf.php";
// // require_once(APPPATH.'libraries/MPDF57/mpdf.php');
// $mpdf=new mPDF('utf-8','A4');
// // $mpdf=new mPDF('utf-8','A4-L');

// set_time_limit(0);
// ini_set('memory_limit','1024M');

// //Beginning Buffer to save PHP variables and HTML tags
// ob_start();
date_default_timezone_set('Asia/Jakarta');
$today = date('l, d F Y [H:i:s]');

$rest_d		= $this->db->get_where('warehouse_adjustment_detail',array('kode_trans'=>$kode_trans))->result_array();
$rest_data 	= $this->db->get_where('warehouse_adjustment',array('kode_trans'=>$kode_trans))->result_array();

$TANGGAL = (!empty($rest_data[0]['tanggal']))?date('d F Y', strtotime($rest_data[0]['tanggal'])):date('d F Y', strtotime($rest_data[0]['created_date']));
?>

<table class="gridtable2" border='1' width='100%' cellpadding='2'>
	<tr>
		<td align='center'><b>PT. ORIGA MULIA FRP</b></td>
	</tr>
	<tr>
		<td align='center'><b><h2>TANDA TERIMA BARANG</h2></b></td>
	</tr>
</table>
<br>
<br>
<table class="gridtable2" width="100%" border='0'>
	<thead>
		<tr>
			<td class="mid">No Transaksi</td>
			<td class="mid">:</td>
			<td class="mid" colspan='4'><?= $kode_trans;?></td>
		</tr>
		<tr>
			<td class="mid" width='15%'>No PO</td>
			<td class="mid" width='2%'>:</td>
			<td class="mid" width='33%'><?= $rest_data[0]['no_ipp'];?></td>
			<td class="mid" width='8%'>PIC</td>
			<td class="mid" width='2%'>:</td>
			<td class="mid" width='40%'><?= $rest_data[0]['pic'];?></td>
		</tr>
		<tr>
			<td class="mid">Tanggal Terima</td>
			<td class="mid">:</td>
			<td class="mid"><?= $TANGGAL;?></td>
			<td class="mid">Note</td>
			<td class="mid">:</td>
			<td class="mid"><?= $rest_data[0]['note'];?></td>
		</tr>
	</thead>
</table><br>
<table class="gridtable" width='100%' border='1' cellpadding='2'>
	<thead align='center'>
		<tr>
            <th class="mid" style='vertical-align:middle;' width='4%'>#</th>
            <th class="mid" style='vertical-align:middle;'>Name Barang</th>
            <th class="mid" style='vertical-align:middle;' width='8%'>Qty</th>
            <th class="mid" style='vertical-align:middle;' width='19%'>Keterangan</th>
            <th class="mid" style='vertical-align:middle;' width='19%'>Pemeriksa</th> 
		</tr>
	</thead>
	<tbody>
		<?php
		$No=0;
		foreach($rest_d AS $val => $valx){$No++;
            $qty_oke 		= number_format($valx['qty_oke']);
            $keterangan 	= (!empty($valx['keterangan']))?ucfirst($valx['keterangan']):'-';
            $pemeriksa 	= (!empty($valx['ket_req_pro']))?ucfirst($valx['ket_req_pro']):'-';
            // if($tanda == 'check' AND $checked == 'Y'){
            //     $qty_oke 		= number_format($valx['check_qty_oke']);
            //     $keterangan 	= (!empty($valx['check_keterangan']))?ucfirst($valx['check_keterangan']):'-';
            //     $pemeriksa 	= (!empty($valx['ket_req_pro']))?ucfirst($valx['ket_req_pro']):'-';
            // }
            
            echo "<tr>";
                echo "<td align='center'>".$No."</td>";
                echo "<td>".strtoupper($valx['nm_material'])."</td>";
                echo "<td align='center'>".$qty_oke."</td>";
                echo "<td>".$keterangan."</td>";
                echo "<td>".$pemeriksa."</td>";
            echo "</tr>";
		}
		?>
	</tbody>
</table><br><br><br>
<table class="gridtable2" width='100%' border='0' cellpadding='2'>
	<tr>
		<td width='65%'></td>
		<td align='center'></td>
		<td></td>
		<td width='5%'></td>
		<td align='center'>Ttd,</td>
		<td></td>
	</tr>
	<tr>
		<td height='45px'></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td></td>
		<td align='center'></td>
		<td></td>
		<td></td>
		<td align='center'>QC Inspector</td>
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
		font-size:10px;
		color:#333333;
		border-width: 1px;
		border-color: #666666;
		border-collapse: collapse;
	}
	table.gridtable th {
		border-width: 1px;
		padding: 6px;
		border-style: solid;
		border-color: #666666;
		background-color: #f2f2f2;
	}
	table.gridtable th.head {
		border-width: 1px;
		padding: 6px;
		border-style: solid;
		border-color: #666666;
		background-color: #7f7f7f;
		color: #ffffff;
	}
	table.gridtable td {
		border-width: 1px;
		padding: 6px;
		border-style: solid;
		border-color: #666666;
		background-color: #ffffff;
	}
	table.gridtable td.cols {
		border-width: 1px;
		padding: 6px;
		border-style: solid;
		border-color: #666666;
		background-color: #ffffff;
	}

	table.gridtable2 {
		font-family: verdana,arial,sans-serif;
		font-size:11px;
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
