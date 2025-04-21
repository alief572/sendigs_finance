<?php

$sroot 		= $_SERVER['DOCUMENT_ROOT'];
include $sroot."/application/libraries/MPDF57/mpdf.php";
$mpdf=new mPDF('utf-8','A4');
// $mpdf=new mPDF([
// 'mode' => 'utf-8',
// 'format' => 'A4',
// 'orientation' => 'P',
// 'margin_left' => 0,
// 'margin_right' => 0,
// 'margin_top' => 0,
// 'margin_bottom' => 0,
// 'margin_header' => 0,
// 'margin_footer' => 0,
// ]);

// $mpdf=new mPDF('utf-8','A4-L');

set_time_limit(0);
ini_set('memory_limit','1024M');

//Beginning Buffer to save PHP variables and HTML tags
ob_start();
date_default_timezone_set('Asia/Jakarta');
$today = date('l, d F Y [H:i:s]');
?>

<table class="gridtable2" width="100%" border='0' style='border-bottom:none;'>
	<tr>
		<td rowspan='7' align='center'  style='border-bottom:none;'>
			<b>PT. Origa Mulia FRP</b><br>
			Jl. Pembangunan 2 No. 34<br>
			Kec. Batuceper, Kel. Batusari<br>
			Kota Tangerang Banten 15122<br>
			Indonesia
		</td>
		<td style='border-right:none; border-bottom: none;' width='12%'></td>
		<td style='border-left:none; border-right:none; border-bottom: none;' width='1%'></td>
		<td style='border-left:none; border-bottom: none;' width='20%'></td>
		<td style='border-bottom:none;' colspan='3' rowspan='2' align='center'><u><b>DISTRIBUSI SURAT JALAN</b></u></td>
	</tr>
	<tr>
		<td style='border-right:none; border-top: none; border-bottom: none;'>Rev. No</td>
		<td style='border:none;'>:</td>
		<td style='border-left:none; border-top: none; border-bottom: none;'></td>
	</tr>
	<tr>
		<td style='border-right:none; border-top: none;'></td>
		<td style='border-right:none; border-top: none; border-left:none;'></td>
		<td style='border-left:none; border-top: none;'></td>
		<td style='border-bottom:none; border-right: none; border-top: none;' width='12%'>Putih/Asli</td>
		<td style='border:none;' width='1%'>:</td>
		<td style='border-bottom:none; border-left: none; border-top: none;' width='20%'>Penagihan / Finance</td>
	</tr>
	<tr>
		<td colspan='3' style='border-bottom:none;' align='center'><u><b>SURAT JALAN</b></u></td>
		<td style='border-bottom:none; border-top: none; border-right: none;'>Merah</td>
		<td style='border:none;'>:</td>
		<td style='border-bottom:none; border-top: none; border-left:none;'>PPIC/Logistik</td>
	</tr>
	<tr>
		<td style='border-bottom:none; border-top: none; border-right: none;'>No SJ</td>
		<td style='border:none;'>:</td>
		<td style='border-bottom:none; border-top: none; border-left:none;'><?=$no_surat_jalan;?></td>
		<td style='border-bottom:none; border-top: none; border-right: none;'>Kuning</td>
		<td style='border:none;'>:</td>
		<td style='border-bottom:none; border-top: none; border-left:none;'>Pembeli / Penerima</td>
	</tr>
	<tr>
		<td style='border-bottom:none; border-top: none; border-right: none;'>No. SO</td>
		<td style='border:none;'>:</td>
		<td style='border-bottom:none; border-top: none; border-left:none;'><?=$getData2[0]['no_so'];?></td>
		<td style='border-bottom:none; border-top: none; border-right: none;'>Hijau</td>
		<td style='border:none;'>:</td>
		<td style='border-bottom:none; border-top: none; border-left:none;'>Cost Control</td>
	</tr>
	<tr>
		<td style='border-bottom:none; border-top: none; border-right: none;'>No. Quo</td>
		<td style='border:none;'>:</td>
		<td style='border-bottom:none; border-top: none; border-left:none;'><?=$getData2[0]['no_penawaran'];?></td>
		<td style='border-bottom:none; border-top: none; border-right: none;'>Biru</td>
		<td style='border: none;'>:</td>
		<td style='border-bottom:none; border-top: none; border-left:none;'>Security</td>
	</tr>
</table>
<table class="gridtable2" width="100%" border='0'>
	<tr>
		<td width='15%' style='border-bottom:none; border-right: none; vertical-align: bottom;' height='30px'>Supir</td>
		<td width='2%' style='border-bottom:none; border-left: none;  border-right: none; vertical-align: bottom;'>:</td>
		<td width='20%' style='border-bottom:none; border-left: none; border-right: none; vertical-align: bottom;'>................................</td>
		<td width='33%' rowspan='5' style='border-bottom:none; border-left: none; vertical-align: top; padding-top:10px;'>
			Cikarang, <?=date('d F Y');?><br>
			Kepada Yth, <br><b><?=$getData2[0]['nm_customer'];?></b><br>
			<?=$getData[0]['delivery_address'];?> <br>Project, <?=$getData2[0]['project'];?>
		</td>
	</tr>
	<tr>
		<td style='border-bottom:none; border-right: none;  border-top: none;'>No. Container</td>
		<td style='border: none;'>:</td>
		<td style='border: none;'>................................</td>
	</tr>
	<tr>
		<td style='border-bottom:none; border-right: none;  border-top: none;'>No. Seal</td>
		<td style='border: none;'>:</td>
		<td style='border: none;'>................................</td>
	</tr>
	<tr>
		<td style='border-bottom:none; border-right: none;  border-top: none;'>Jenis Kendaraan</td>
		<td style='border: none;'>:</td>
		<td style='border: none;'>................................</td>
	</tr>
	<tr>
		<td style='border-bottom:none; border-right: none;  border-top: none;'>No. Polisi</td>
		<td style='border: none;'>:</td>
		<td style='border: none;'>................................</td>
	</tr>
</table>
<table class="gridtable2" width='100%' border='1' cellpadding='2'>
	<tr>
		<td align='center' width='15%'>QTY</td>
		<td align='center' width='10%'>UNIT</td>
		<td align='center' style='vertical-align:middle;'>ITEM CUST/DESC CUST</td>
	</tr>
	<?php
	foreach($getDataDetail AS $val => $value){
		$nm_product = (!empty($GET_DET_Lv4[$value['code_lv4']]['nama']))?$GET_DET_Lv4[$value['code_lv4']]['nama']:'';
		echo "<tr>";
			echo "<td align='center'>".number_format($value['qty_delivery'])."</td>";
			echo "<td align='center'>PCS</td>";
			echo "<td align='left'>".$nm_product."</td>";
		echo "</tr>";
	}
	echo "<tr>";
		echo "<td colspan='2'></td>";
		echo "<td colspan='2'><b>Note: Barang dikirim dalam keadaan baik</b></td>";
	echo "</tr>";
	?>
</table>
<table class="gridtable2" width='100%' border='0' cellpadding='2'>
	<tr>
		<td width='15%' align='center'>Dibuat,</td>
		<td width='15%' align='center'>Diperiksa,</td>
		<td align='center'>Diketahui,</td>
		<td width='15%' align='center'>Diketahui,</td>
		<td width='15%' align='center'>Diterima</td>
	</tr>
	<tr>
		<td height='65px'></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td align='center'>Staff Gudang</td>
		<td align='center'>Head PPIC</td>
		<td align='center'></td>
		<td align='center'>Pembawa</td>
		<td align='center'></td>
	</tr>
	<tr>
		<td align='center' colspan='2'>Log Dept</td>
		<td align='center'>Cost Control Factory Manager</td>
		<td align='center'>(Supir/Ekspedisi)</td>
		<td align='center'>Penerima</td>
	</tr>
</table>
<p>NB : Pembawa bertanggungjawab atas barang yang dikirim.</p>
<style type="text/css">
	@page {
		margin-top: 0cm;
		margin-left: 0cm;
		margin-right: 0cm;
		margin-bottom: 0cm;
	}

	.mid{
		vertical-align: middle !important;
	}

	p{
		font-family: verdana,arial,sans-serif;
		font-size:12px;
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
		font-size:12px;
		color:#333333;
		border-width: 1px;
		border-color: #666666;
		border-collapse: collapse;
		margin-top: 0cm;
		margin-left: 0cm;
	}
	table.gridtable2 th {
		border-width: 1px;
		padding: 8px;
		border-style: solid;
		border-color: #666666;
		background-color: #f2f2f2;
		margin-top: 0cm;
		margin-left: 0cm;
	}
	table.gridtable2 th.head {
		border-width: 1px;
		padding: 8px;
		border-style: solid;
		border-color: #666666;
		background-color: #7f7f7f;
		color: #ffffff;
		margin-top: 0cm;
		margin-left: 0cm;
	}
	table.gridtable2 td {
		border-width: 1px;
		padding: 3px;
		border-style: solid;
		border-color: #666666;
		background-color: #ffffff;
		margin-top: 0cm;
		margin-left: 0cm;
	}
	table.gridtable2 td.cols {
		border-width: 1px;
		padding: 3px;
		border-style: solid;
		border-color: #666666;
		background-color: #ffffff;
		margin-top: 0cm;
		margin-left: 0cm;
	}
</style>


<?php
$footer = "<p style='font-family: verdana,arial,sans-serif; font-size:10px;'><i>Printed by : ".ucfirst(strtolower($printby)).", ".$today." / ".$no_surat_jalan."</i></p>";
$html = ob_get_contents();
// exit;
// $mpdf->SetWatermarkText('ORI Group');
$mpdf->showWatermarkText = true;
$mpdf->SetTitle($no_surat_jalan); 
$mpdf->AddPageByArray([
    'orientation' => 'P',
    'margin-top' => 5,
    'margin-bottom' => 5,
    'margin-left' => 5,
    'margin-right' => 5,
    'margin-header' => 0,
    'margin-footer' => 0,
]);
// $mpdf->SetFooter($footer);
$mpdf->WriteHTML($html);
ob_clean();
$mpdf->Output('surat-jalan.pdf' ,'I');