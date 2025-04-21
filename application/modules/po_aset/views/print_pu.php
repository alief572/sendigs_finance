<html xmlns="http://www.w3.org/1999/xhtml">
 <head>
  <title> Bukti Penerimaan Uang Tunai </title>
 </head>
 <body>
  <table border="1" cellpadding="2" cellspacing="0">
	<tr>
	<td style="padding:15px;">
	<b>PT.PRISMA HARAPAN</b><br/>
	Jl. Raya Kedoya No. 38, RT.019 RW 004, Kedoya Selatan<br />
	Kebon Jeruk - Jakarta Barat
	<table border="0">
	<tr>
		<td colspan="5" width="700">
			<div style="text-align:center;"><hr/>
			<b>BUKTI PENERIMAAN UANG TUNAI</b>
			<hr/>
			</div>
		</td>
	</tr>
	<tr>
		<td>Tanggal</td>
		<td>:</td>
		<td><?php echo date("d-m-Y",strtotime($kasbon->tgl_kasbon));?></td>
		<td></td>
		<td>No. Bukti : <?php echo $kasbon->no_kasbon?></td>
	</tr>
	<tr>
		<td>Dibayar Kepada</td>
		<td>:</td>
		<td><?php //echo $kasbon->pic?></td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td>Sebesar Rp.</td>
		<td>:</td>
		<td><?php echo number_format($kasbon->nilai_kasbon)?></td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td>Terbilang</td>
		<td>:</td>
		<td colspan="3"><?php echo ynz_terbilang($kasbon->nilai_kasbon,3)?> Rupiah</td>
	</tr>
	<tr>
		<td colspan="5" width="700"><br/></td>
	</tr>
	<tr>
		<td valign="top">Keterangan</td>
		<td valign="top">:</td>
		<td valign="top" height="50" colspan="3"><?php echo nl2br($infopr->description)?></td>
	</tr>
	<tr>
		<td colspan="5"><hr></td>
	</tr>
	<tr>
		<td valign="top"></td>
		<td valign="top"></td>
		<td valign="top" height="50">Dibayar Oleh,</td>
		<td valign="top" height="50">Diterima Oleh,</td>
		<td></td>
	</tr>
	<tr>
		<td valign="top"></td>
		<td valign="top"></td>
		<td valign="top" height="20">(.................)</td>
		<td valign="top" height="20">(.................)</td>
		<td></td>
	</tr>
	</table>
	</td>
	</tr>
  </table>
 </body>
</html>
