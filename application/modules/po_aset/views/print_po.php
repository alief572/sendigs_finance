<!DOCTYPE html>
<html>
<head>
    <title>Purchase Order</title>
  <style>
  body { font-family: Calibri,Arial; font-size:10px; }
        table { border-collapse:collapse;      
				table-layout:fixed;}    
	.border-all{border: 1px solid black; }
	.border-rl{border-left: 1px solid;border-right: 1px solid;}
	.border-rlb{border-left: 1px solid;border-right: 1px solid;border-bottom: 1px solid;}
    
  </style>	
</head>
<body>
<table width="900" border="1" cellspacing="0" cellpadding="15">
<tr>
	<td align="center" style="border-style:solid;border-width:5px;">
	<table width="890">
	<tr>
		<td><font style="color:blue;font-size:14px;font-weight:bold;">PT. PRISMA HARAPAN</font><br />
		Website : www.prisma-ads.com</td>
		<td colspan="2">Office : Jl. Raya Kedoya No. 38 Rt 019/Rw.004, Kedoya Selatan,<br />
		Kebon Jeruk - Jakarta Barat &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Fax : 021 - 5835 6570</td>
	</tr>
	<tr>
		<td colspan="3"><hr style="color:black;"></td>
	</tr>
	<tr>
		<td valign="top" width="250">
		
<?php 
	$tgl_po=$results->tgl_po;
	$no_po=$results->no_po;
	$id=$results->id;
	$ppn=$results->ppn;
	$nilai_ppn=$results->nilai_ppn;
	$nilai_po=$results->harga_total;
	$total_nilai_po=$results->total_nilai_po;
	$vendor_id=$results->vendor_id;
	$i=0;

	$nama=$vendor->nama;
	$alamat_office=$vendor->alamat_office;
	$telpon=$vendor->telpon;
?>		
			<table>
			<tr>
				<td valign="top">TO :</td>
				<td>
				<?=$nama;?><br />
				<?=$alamat_office;?><br />
				<?=$telpon;?><br />
				
				</td>
			</tr>
			</table>
		</td>
		<td align="center" width="200"><br /><h3>Purchase Order</h3>
		Tgl. <?=tgl_indo($tgl_po)?></td>
		<td valign="top">
		<table>
		<tr>
			<td>Page</td>
			<td>: 1 of 1</td>
		</tr>
		<tr>
			<td>PO Number</td>
			<td>: <?=$no_po?></td>
		</tr>
		</table>
		</td>
	</tr>
	<tr>
		<td colspan="3">

		<table width="850" cellspacing="0" cellpadding="2">
			<tr><th class="border-all" width="30">No.</th><th width="300" class="border-all">Description</th><th class="border-all" width="50">Quantity</th><th class="border-all">Price</th><th class="border-all">Total</th></tr>
<?php 
	$i++;
	$description=$results->info_desc;
	$notes=$results->notes;
	$nilai_satuan_po=$results->harga_satuan;
	$qty_po=$results->qty;
	echo '<tr><td align="center" class="border-rl" valign="top">'.$i.'</td>';
	echo '<td class="border-rl" valign="top">'.$description.'<br />'.$notes.'</td>';
	echo '<td align="right" class="border-rl" valign="top">'.$qty_po.'</td>';
	echo '<td align="right" class="border-rl" valign="top">'.number_format($nilai_satuan_po).'</td>';
	echo '<td align="right" class="border-rl" valign="top">'.number_format($nilai_po).'</td></tr> '; 

?>
			<tr>
				<td class="border-rlb" height="<?=(100*(6-$i))?>"></td>
				<td class="border-rlb"></td>
				<td class="border-rlb"></td>
				<td class="border-rlb"></td>
				<td class="border-rlb"></td>
			</tr>
			<tr>
				<td rowspan="3" colspan="2" valign="top"></td>
				<td align="right" colspan="2">Sub Total IDR </td>
				<td align="right" class="border-all"><?=number_format($nilai_po)?></td>
			</tr>
			<tr>
				<td align="right" colspan=2>( PPN 10% ) IDR </td>
				<td align="right" class="border-all"><?=number_format($nilai_ppn)?></td>
			</tr>
			<tr>
				<td align="right" colspan=2>TOTAL IDR </td> 
				<td align="right" class="border-all"><?=number_format($total_nilai_po)?></td>
			</tr>
		</table>
		
		</td>
	</tr>
	<tr>
		<td valign="top"><br />Prepared,
		<br /><br />
		<br /><br />
		<?php
		echo $sign_user->nm_lengkap;
		?>
		</td>
		<td valign="top"><br />Approved by,
		<br /><br />
		<?php
		foreach ($sign_approved as $keys){
			echo $keys->nm_lengkap.'<br />';
		}
		?>
		</td>
		<td valign="top"><br />Acknowledged by,<br /><br /><br /><br /></td>
	</tr>	
	</table>
	</td>
</tr>
</table>
</body>
</html>