<html>
<head>
  <title>Cetak PDF</title>
<style>
    #tables td, th {
		border: 1px solid #000000;
        padding: 2 px;
		font-size: 11px;
		border-collapse: collapse;
    }
	.clearth{
		border: 0px;
		border-collapse: collapse;
	}
</style>
</head>
<body>
<?php
	foreach($header as $header){
	}
	$detailsum = $this->db->query("SELECT SUM(width) as sumwidth, SUM(qty) as sumqty, SUM(totalwidth) as sumtotalwidth, SUM(jumlahharga) as sumjumlahharga, SUM(hargasatuan) as sumhargasatuan FROM dt_trans_po WHERE no_po = '".$header->no_po."' ")->result();
	$jumlahdetail = $this->db->query("SELECT COUNT(no_po) as no_po FROM dt_trans_po WHERE no_po = '".$header->no_po."' ")->result();
	$jumlahdata = $jumlahdetail[0]->no_po;
	$tinggi = 300/$jumlahdata ;
	if(empty($header->negara)){
		$cou ='Indonesia';
		}else{
		$findnegara = $this->db->query("SELECT * FROM negara WHERE id_negara = '".$header->negara."' ")->result();
		$cou = $findnegara[0]->nm_negara;
		}
	$findpic = $this->db->query("SELECT * FROM child_supplier_pic WHERE id_suplier = '".$header->id_suplier."' ")->result();
	$namapic = $findpic[0]->name_pic;
?>
<div id='wrapper'>
<table border="0" width='100%' align="center">
<tr>
	<td width="700" align="center">
		<h5 style="text-align: left;">PT METALSINDO PACIFIC</h5>
	</td>
</tr>
</table>
<table id="tables" bgcolor="grey" border="0" width='100%' align="center">
<tr>
	<td width="700" align="left">
		<h3 style="text-align: center;" >PURCHASE ORDER</h3>
	</td>
</tr>
</table>
<table border="0" width='100%' align="center">
<tr>
	<td width="350" align="left">
	<table>
	<tr><td align='left'>Address</td></tr>
	<tr><td align='left'>Jl. Jababeka XIV, Blok J no. 10 H</td></tr>
	<tr><td align='left'>Cikarang Industrial Estate, Bekasi 17530</td></tr>
	<tr><td align='left'>PHONE:(62-21)89831726734,FAX(62-21)89831866</td></tr>
	</table>
	</td>
	<td width="350" align="right" >
	<table align='right' >
	<tr><td align='left'>PO No</td><td align='left'>:</td><td align='left' border="0" cellspacing="0"><?= $header->no_surat ?></td></tr>
	</table>
	</td>
</tr>
</table>
<table border="1px" cellspacing="0" width='100%' align="center">
<tr>
	<td width="380" align="center">
	<table width='380' align="center">
	<tr><td width='70' align="left">Supplier</td><td width='10' align="left">:</td><td width='300' align="left"><?= $header->name_suplier ?></td></tr>
	<tr><td width='70' align="left">Address</td><td width='10' align="left">:</td><td width='300' align="left"><?= $header->address_office ?></td></tr>
	<tr><td width='70' align="left">Country</td><td width='10' align="left">:</td><td width='300' align="left"><?= $cou ?></td></tr>
	<tr><td width='70' align="left">PIC</td><td width='10' align="left">:</td><td width='300' align="left"><?= $namapic ?></td></tr>
	<tr><td width='70' align="left">Phone</td><td width='10' align="left">:</td><td width='300' align="left"><?= $header->telephone ?></td></tr>
	<tr><td width='70' align="left">FAX</td><td width='10' align="left">:</td><td width='300' align="left"><?if(empty($header->fax)){echo"-";}else{echo"$header->fax";}  ?></td></tr>
	</table>
	</td>
</tr>
</table>
<br>
		<table id="tables" border="0" width='100%'  align="center" cellpadding="2" cellspacing="0">
			<tr bgcolor="grey">
			<td align="center" width="200">Material</td>
			<td align="center" width="70">Width</td>
			<!--<td align="center" width="70">Qty (Unit)</td><td align='center'>".number_format($detail->qty)."</td>-->
			<td align="center" width="70">Total Weight</td>
			<td align="center" width="70">Unit Price</td>
			<td align="center" width="70">Amount</td>
			<td align="center" width="80">Remarks</td>
			</tr>
			<?php	
			$CIF = $header->cif;
			foreach($detail as $detail){
			if($jumlahdata <= '30'){
			echo"	
			<tr >
			<td align='center' height='".$tinggi."'>".$detail->namamaterial."</td>
			<td align='center'>".$detail->width."</td>
			<td align='center'>".number_format($detail->totalwidth)."</td>
			<td align='center'>".number_format($detail->hargasatuan,3)."</td>
			<td align='center'>".number_format($detail->jumlahharga)."<br>".$CIF ."</td>
			<td align='center'>".$detail->description."</td>
			</tr>";
			$CIF = "";
			}else{
			echo"	
			<tr >
			<td align='center'>".$detail->namamaterial."</td>
			<td align='center'>".number_format($detail->width)."</td>
			<td align='center'>".number_format($detail->totalwidth)."</td>
			<td align='center'>".number_format($detail->hargasatuan,3)."</td>
			<td align='center'>".number_format($detail->jumlahharga)."<br>".$CIF ."</td>
			<td align='center'>".$detail->description."</td>
			</tr>";
			$CIF = "";
			}
			} ?>
			 <tr height="10 cm">
			<td align="center">Total </td>
			<td align="center"><?= number_format($detailsum[0]->sumwidth) ?></td>
			<td align="center"><?= number_format($detailsum[0]->sumtotalwidth) ?></td>
			<td align="center"><?= number_format($detailsum[0]->sumhargasatuan,3) ?></td>
			<td align="center"><?= number_format($detailsum[0]->sumjumlahharga) ?></td>
			<td align="center"></td>
			</tr>
			<tr>
			<td colspan='2' align="center">Issued Date</td>
			<td colspan='3' align="center">
			<?php if($header->cif == "Destination"){
			echo"Delivery To :";	
			}else{
			echo"Delivery To";	
			}; ?></td>
			<td  colspan=2' align="center">Eta Date</td>
			</tr>
			<tr>
			<td colspan='2' align="center"><?= $header->tanggal ?></td>
			<td colspan='3' align="center">PT Metalsindo Pacific<br>Cikarang, Indonesia</td>
			<td  colspan='2' rowspan='2' align="center"><?= $header->expect_tanggal ?></td>
			</tr>
									<tr>
			<td colspan='2' align="center">Payment Term</td>
			<td colspan='3' align="center">Transfer End Next <?= $header->term ?> Day After BL Date</td>
			</tr>

	</table>
	<br>
		<table align='right'>
	<tr><td align='center'>Approved</td><td width='100'></td></tr>
	<tr ><td height='70' align='center'></td><td width='100'></td></tr>
	<tr><td align='center'><u>HARRY WIDJAJA</u></td><td width='100'></td></tr>
	<tr><td align='center'>President Director</td><td width='100'> </td></tr>
	</table>
</div>