<?php
	$sroot 		= $_SERVER['DOCUMENT_ROOT'];
//	include $sroot."/application/libraries/MPDF57/mpdf.php"; 
require_once(APPPATH.'libraries/MPDF57/mpdf.php');
	$mpdf=new mPDF('utf-8','A4');
	// $mpdf=new mPDF('utf-8','A4-L');

	set_time_limit(0);
	ini_set('memory_limit','1024M');
	
	$data_iden	= $this->db->get('identitas')->result();
	echo "<table class='gridtable2' border='0' width='100%' cellpadding='2'>";
		echo "<tr>";
			echo "<td class='header_style_company' colspan='3' width='60%'>".$data_iden[0]->nama_resmi."</td>";
			echo "<td class='header_style_company bold color_req' colspan='3'>REQUEST PAYMENT</td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td class='header_style_alamat' colspan='3'>".strtoupper($data_iden[0]->alamat_baris1)."</td>";
			echo "<td class='header_style_alamat' width='15%'>Request No.</td>";
			echo "<td class='header_style_alamat' width='1%'>:</td>";
			echo "<td class='header_style_alamat' width='15%'>".$datapo->no_request."</td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td class='header_style_alamat' colspan='3'>".strtoupper($data_iden[0]->alamat_baris2)."</td>";
			echo "<td class='header_style_alamat'>Request Date</td>";
            echo "<td class='header_style_alamat'>:</td>";
			echo "<td class='header_style_alamat'>".date('d F Y',strtotime($datapo->request_date))."</td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td class='header_style_alamat' colspan='3'>".strtoupper($data_iden[0]->alamat_baris3)."</td>";
            echo "<td class='header_style_alamat' colspan=3></td>";
		echo "</tr>";

        echo "<tr>";
            echo "<td class='header_style_alamat' width='15%'>Vendor</td>";
			echo "<td class='header_style_alamat' width='1%'>:</td>";
            echo "<td class='header_style_alamat' colspan='4'>".strtoupper(get_name('supplier','nm_supplier','id_supplier',$datapo->id_supplier))."</td>";
        echo "</tr>";

	echo "</table>";

	echo "<br>";
	echo "<table class='gridtable' width='100%' border='0' cellpadding='2'>";
		echo "<thead>";
			echo "<tr>";
				echo "<th style='text-align: left' width='15%'>No Invoice</th>";
				echo "<th style='text-align: right' width='15%'>Amount</th>";
				echo "<th style='text-align: right' width='10%'>PPN</th>";
				echo "<th style='text-align: center' width='10%'>PPH</th>";
				echo "<th style='text-align: right' width='15%'>Total</th>";
				echo "<th style='text-align: left'>Keterangan</th>";
			echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		echo "<tr>";
			echo "<td>".$datapo->no_invoice."</td>";
			echo "<td align='right'>".$datapo->curs_header." " .number_format($datapo->nilai_po_invoice,2)."</td>";
			echo "<td align='right'>" .number_format($datapo->invoice_ppn,2)."</td>";
			echo "<td align='right'>" .number_format($datapo->nilai_pph_invoice,2)."</td>";
			echo "<td align='right'>" .number_format($datapo->request_payment,2)."</td>";
			echo "<td>".$datapo->keterangan."</td>";
		echo "</tr>";
			$max = 2;
			for($a=1; $a<=$max; $a++){
				echo "<tr>";
					echo "<td>&nbsp;</td>";
					echo "<td>&nbsp;</td>";
					echo "<td>&nbsp;</td>";
					echo "<td>&nbsp;</td>";
					echo "<td>&nbsp;</td>";
					echo "<td>&nbsp;</td>";
				echo "</tr>";
			}
		echo "</tbody>";
	echo "</table>";
	// echo "<p class='bold'>Amount in Words</p>"; 
$satuan = "Rupiah";
if($datapo->curs_header== 'USD'){
	$satuan = "Dollars";
}

	echo "<table class='gridtableX' width='100%' border='0' cellpadding='2'>";
		echo "<tbody>";
			echo "<tr>";
				echo "<td rowspan=3 valign=top><p class='bold'>Amount in Words :<br><u>".ucwords(numberTowords($datapo->request_payment))." ".$satuan."</u></p></td>";
				echo "<td rowspan=3 width='5%'></td>";
				echo "<td align='right' width='15%'>DP :</td>";
				echo "<td width='20%'>".number_format($datapo->potongan_dp,2)."</td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td align='right' width='15%'>Claim :</td>";
				echo "<td width='20%'>".number_format($datapo->potongan_claim,2)."</td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td align='right' width='15%'>Total :</td>";
				echo "<td width='20%'>".number_format($datapo->request_payment,2)."</td>";
			echo "</tr>";
		echo "</tbody>";
	echo "</table><br /><br /><br />";
	echo "<table class='gridtable3' width='100%' border='1' cellpadding='2'>";
		echo "<tbody>";
			echo "<tr>";
				echo "<th width='14%'>CREATED BY</th>";
				echo "<th width='14%'>CHECKED BY</th>";
				echo "<th width='14%'>ACCT BY</th>";
				echo "<th colspan=3>APPROVED BY</th>";
				echo "<th width='14%'>RECEIVED BY</th>";
			echo "</tr>";
            echo "<tr>";
				echo "<th height=100>&nbsp;</th>";
				echo "<th></th>";
				echo "<th></th>";
				echo "<th></th>";
				echo "<th></th>";
				echo "<th></th>";
				echo "<th></th>";
            echo "</tr>";
		echo "</tbody>";
	echo "</table>";
	?>
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
	}
	
	.header_style_alamat{
		padding: 10px;
		color: black;
		font-size: 10px;
        vertical-align: top !important;
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
		font-size:10 px;
		color:#333333;
		border: 1px solid #dddddd;
		border-collapse: collapse;
	}
	table.gridtable th {
		padding: 6px;
		background-color: #0049a8;
		color: white;
		border-color: #0049a8;
		border-style: solid;
		border-width: 1px;
	}
	table.gridtable th.head {
		padding: 6px;
		background-color: #0049a8;
		color: white;
		border-color: #0049a8;
		border-style: solid;
		border-width: 1px;
	}
	table.gridtable tr:nth-child(even) {
		background-color: #f2f2f2;
	}
	table.gridtable td {
		padding: 6px;
	}
	table.gridtable td.cols {
		padding: 6px;
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
		font-size:10px;
	}
	table.gridtable4 td {
		padding: 3px;
		border-color: #dddddd;
	}
	table.gridtable4 td.cols {
		padding: 3px;
	}

    table.gridtableX {
		font-family: verdana,arial,sans-serif;
		font-size:12px;
		border-collapse: collapse;
	}
	table.gridtableX td {
		padding: 4px;
	}
	table.gridtableX td.cols {
		padding: 4px;
	}
	</style>

	<?php
	$html = ob_get_contents();
	// exit;
	ob_end_clean();
	// $mpdf->SetWatermarkText('ORI Group');
	$mpdf->showWatermarkText = true;
	$mpdf->SetTitle($no_po);
	$mpdf->AddPage();
	$mpdf->WriteHTML($html);
	$mpdf->Output($no_po.' - '.strtoupper(get_name('supplier','nm_supplier','id_supplier',$data_header[0]->id_supplier)).' '.date('dmyhis').'.pdf' ,'I');
?>
