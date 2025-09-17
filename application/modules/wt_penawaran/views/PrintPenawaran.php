<html>
<head>
  <title>Cetak PDF</title>
<style>
    #tables td, th {
		border: 1px solid grey;
        padding: 0 px;
		font-size: 12px;
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
?>
<table border="0px" cellspacing="0" width='100%' valign="top">
    <tr>
        <td align="left"width="70%" valign="top" >
            <img src='<?=$_SERVER['DOCUMENT_ROOT'];?>watercosystem/assets/images/logo_waterco.png' alt="" height='100' width='250'>
        </td>
        <td align="right" valign="top" width="30%">
			<br>
            PT WATERCO INDONESIA<br>
            Inkopal Plaza Kelapa Gading Blok B, No.31-32 <br> 
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			Jl. Boulevard Barat, Jakarta-14240, Indonesia<br>
			Phone: +62 21 4585 1481, Fax: +62 21 4585 1480<br>
			Website: www.waterco.co.id<br>
            E-Mail:waterco@waterco.co.id
            
        </td>
    </tr>
</table>
<hr>

<table border="0" width='100%' align="left">
<tr>
	<td width="350" align="left">
	<table>
	<tr>
	   <?php if($header->no_revisi != null){ ?>
		<td align='left'>No</td><td align='left'>:</td><td align='left'><?= $header->no_revisi ?></td>
	   <?php } else {?>
		<td align='left'>No</td><td align='left'>:</td><td align='left'><?= $header->no_surat ?></td>
	    <?php } ?>
	  
		<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td align='left'>Jakarta,<?= date('d-F-Y') ?></td>
    </tr>
	<tr><td align='left'>Hal</td><td align='left'>:</td><td align='left'>Penawaran Produk Waterco</td></tr>
	<?php if($header->revisi !='0'){ ?>
	<tr><td align='left'>Revisi</td><td align='left'>:</td><td align='left'><?=$header->revisi?></td></tr>
	<?php }?>
	</table>
</td>	
</tr>
<tr>
<?php
$customer =$this->db->query("SELECT * FROM master_customers WHERE id_customer='$header->id_customer'")->row();
$sales =$this->db->query("SELECT * FROM ms_karyawan WHERE id_karyawan='$header->id_sales'")->row();
$pic =$this->db->query("SELECT * FROM child_customer_pic WHERE id_customer='$header->id_customer'")->row();

?>
	<td width="350" align="left"><br><br>
	<table>
	<tr><td align='left'>Kepada Yth.</td></tr>
	<tr><td align='left'><?=$customer->name_customer?></td></tr>
	<tr><td align='left'>UP.&nbsp; Bpk/Ibu.&nbsp;<?=$header->pic_customer?></td></tr>
	<tr><td align='left'  width="250"><?=$customer->address_office?></td></tr>
	<tr><td align='left'><?=$customer->zip_code?></td></tr>
	</table>
	<br><br>
	<table>
	<tr><td align='left'>Dengan Hormat,</td></tr>
	<tr><td align='left'></td></tr>
	<tr><td align='left'></td></tr>
	<tr><td align='left'>Berikut kami sampaikan penawaran produk waterco sesuai dengan spek yang Bpk/Ibu minta. </td></tr>
	<tr><td align='left'></td></tr>
	</table>
	</td>

</tr>
</table>

<br>
    <table id="tables" border="0" width='100%' align="left">
	<thead>
			<tr height = '60'>
			<th align="center" width="200">Type</th>
			<th align="center" width="60">Qty/Unit</th>
			<th align="center" width="60">Price/Unit</th>
			<th align="center" width="60">Persen</th>
			<th align="center" width="60">Discount</th>
			<th align="center" width="60">Status</th>
			<th align="center" width="60">Total Price</th>
			</tr>
			<tr></tr>

	</thead>    
	<tbody>
			<?	foreach($detail as $detail){
				if($header->order_status=='stk')
				{
					$sts ='Ready';
				}else
				{
					$sts ='Indent';
				}	
			?>
			<tr>
			<td align="left" width="200"><?= $detail->nama_produk ?></td>
			<td align="center" width="60"><?= number_format($detail->qty) ?></td>
			<td align="right" width="80"><?= number_format($detail->harga_satuan) ?></td>
			<td align="right" width="60"><?= number_format( $detail->diskon,1 ) ?>%</td>
			<td align="right" width="80"><?= number_format($detail->nilai_diskon) ?></td>
			<td align="center" width="60"><?= $sts ?></td>
			<td align="right" width="80"><?= number_format($detail->total_harga) ?></td>
			</tr>
			<?}?>
	</tbody>
	<tfoot>
			<tr>
			<th align="center" colspan='5' width="300" align='right'>Total</th>
			<th></th>
			<th align="right" width="80"><?= number_format($header->nilai_penawaran) ?></th>
			</tr>
			<tr>
			<th align="center" colspan='5' width="300" align='right'>PPN</th>
			<th  align="right"><?= $header->ppn ?>%</th>
			<th align="right" width="80"><?= number_format($header->nilai_ppn) ?></th>
			</tr>
			<tr>
			<th align="right" colspan='5' width="300" align='right'>Grand Total</th>
			<th></th>
			<th align="right" width="80"><?= number_format($header->grand_total) ?></th>
			</tr>
	</tfoot>
			
	</table>


	<table border="0" width='100%' align="left">

		<tr>
		<td width="350" align="left"><br><br>
			<table>
		<?php if($header->id_customer =='MC2200277'){ ?>	
		
		<tr><td align='left'>Keterangan</td></tr>
			<tr><td align='right'>&#61;&#62;</td><td>Harga Franco Jakarta</td></tr>
			<tr><td align='right'>&#61;&#62;</td><td>No. Rek PT. Waterco Indonesia BCA A/C 87000.35.990, CIMB Niaga A/C 800095704600</td></tr>
			<tr><td align='right'>&#61;&#62;</td><td>Pembayaran I : DP 80% <br> Pembayaran II: 20%</td></tr>
			<tr><td align='right'>&#61;&#62;</td><td>Penjualan unit waterco tidak termasuk supervisi dan testing & commissioning</td></tr>
			<tr><td align='right'>&#61;&#62;</td><td>Penawaran berlaku 1 bulan</td></tr>
			<tr><td align='right'>&#61;&#62;</td><td>Pembayaran Cash atau Transfer</td></tr>
		
		
		<?php }else{ ?>	
			
		<?php if($header->top == 1){ ?>	
			
			<tr><td align='left'>Keterangan</td></tr>
			<tr><td align='right'>&#61;&#62;</td><td>Harga Franco Jakarta</td></tr>
			<tr><td align='right'>&#61;&#62;</td><td>No. Rek PT. Waterco Indonesia BCA A/C 87000.35.990, CIMB Niaga A/C 800095704600</td></tr>
			<tr><td align='right'>&#61;&#62;</td><td>Pembayaran 100% Lunas</td></tr>
			<tr><td align='right'>&#61;&#62;</td><td>Penjualan unit waterco tidak termasuk supervisi dan testing & commissioning</td></tr>
			<tr><td align='right'>&#61;&#62;</td><td>Penawaran berlaku 1 bulan</td></tr>
			<tr><td align='right'>&#61;&#62;</td><td>Pembayaran Cash atau Transfer</td></tr>
			
		<?php } 
			elseif($header->top == 2){ ?>
			
			<tr><td align='left'>Keterangan</td></tr>
			<tr><td align='right'>&#61;&#62;</td><td>Harga Franco Jakarta</td></tr>
			<tr><td align='right'>&#61;&#62;</td><td>No. Rek PT. Waterco Indonesia BCA A/C 87000.35.990, CIMB Niaga A/C 800095704600</td></tr>
			<tr><td align='right'>&#61;&#62;</td><td>Pembayaran I: DP 30% <br> Pembayaran II: Pelunasan 70%, 30 hari setelah barang dikirim</td></tr>
			<tr><td align='right'>&#61;&#62;</td><td>Penjualan unit waterco tidak termasuk supervisi dan testing & commissioning</td></tr>
			<tr><td align='right'>&#61;&#62;</td><td>Penawaran berlaku 1 bulan</td></tr>
			<tr><td align='right'>&#61;&#62;</td><td>Pembayaran Cash atau Transfer</td></tr>
			
		<?php } else{ ?>
			<tr><td align='left'>Keterangan</td></tr>
			<tr><td align='right'>&#61;&#62;</td><td>Harga Franco Jakarta</td></tr>
			<tr><td align='right'>&#61;&#62;</td><td>No. Rek PT. Waterco Indonesia BCA A/C 87000.35.990, CIMB Niaga A/C 800095704600</td></tr>
			<tr><td align='right'>&#61;&#62;</td><td>Pembayaran I : DP 30% <br> Pembayaran II: 40% (Saat Shipment) <br> Pembayaran III: Pelunasan 30% Sebelum Barang di Kirim</td></tr>
			<tr><td align='right'>&#61;&#62;</td><td>Penjualan unit waterco tidak termasuk supervisi dan testing & commissioning</td></tr>
			<tr><td align='right'>&#61;&#62;</td><td>Penawaran berlaku 1 bulan</td></tr>
			<tr><td align='right'>&#61;&#62;</td><td>Pembayaran Cash atau Transfer</td></tr>
			
		<?php } 
		} ?>
		</table>
			<br><br>
			<table>
			<tr><td align='left'>Demikian penawaran kami, atas perhatian kami ucapkan terima kasih. </td></tr>
			<tr><td align='left'></td></tr>
			</table>
			</td>

		</tr>
	</table>


	<table border="0" width='100%' align="left">

		<tr>
		
			<td width="250" align="left"><br><br>
			<table>
			<tr><td align='center'>Hormat kami,</td></tr>
			<tr><td align="center"width="70%" valign="top" >
            <img src='<?=$_SERVER['DOCUMENT_ROOT'];?>watercosystem/assets/files/tandatangan/<?=$sales->tanda_tangan?>' alt="" height='80' width='100'>
			</td></tr>
			<tr><td align='center'><u><?=$header->nama_sales?></u> </td></tr>
			<tr><td align='center'><?=$sales->nohp?></td></tr>	
			</table>
			</td>
			<td width="230" align="left"><br><br>
			<?php if($header->approved_by != null){ ?>
			<table>
			<tr><td align='center'>Mengetahui</td></tr>
			<tr><td align="center"width="70%" valign="top" >
			 <img src='<?=$_SERVER['DOCUMENT_ROOT'];?>watercosystem/assets/files/tandatangan/Cap_Waterco_dan_ttd_-_Fajar.png' alt="" height='80' width='100'>
			</td></tr>
			<tr><td align='center'><u>Fajar Nugroho Widyanto</u></td></tr>
			<tr><td align='center'>General Manager</td></tr>	
			</table>
			<?php }?>
			</td>
			<td width="250" align="left"><br><br>
			<table>
			<tr><td align='center'>Customer</td></tr>
			<tr><td align='right'></td></tr>
			<tr><td align='right'></td></tr>
			<tr><td align='right'></td></tr>
			<tr><td align='right'></td></tr>
			<tr><td align='right'></td></tr>
			<tr><td align='right'></td></tr>
			<tr><td align='right'></td></tr>
			<tr><td align='right'></td></tr>
			<tr><td align='right'></td></tr>
			<tr><td align='right'></td></tr>
			<tr><td align='right'></td></tr>
			<tr><td align='right'></td></tr>
			<tr><td align='right'></td></tr>
			<tr><td align='right'></td></tr>
			<tr><td align='right'></td></tr>
			<tr><td align='right'></td></tr>
			<tr><td align='center'><u></u></td></tr>
			<tr><td align='center'><?=$customer->name_customer?></td></tr>	
			</table>
			</td>
		</tr>
	</table>