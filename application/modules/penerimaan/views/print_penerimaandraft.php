<?php
date_default_timezone_set("Asia/Bangkok");
?>
<!DOCTYPE html>
<html>
<head>
    <title></title>
    <style>
    @font-face { font-family: kitfont; src: url('1979 Dot Matrix Regular.TTF'); }
      html
        {
            margin:0;
            padding:0;
            font-style: kitfont;
            font-family:Arial;
            font-size:9pt;
			font-weignt:bold;
            color:#000;
        }
        body
        {
            width:100%;
            font-family:Arial;
            font-style: kitfont;
            font-size:9pt;
			font-weight:bold;
            margin:0;
            padding:0;
        }

        p
        {
            margin:0;
            padding:0;
        }

        .page
        {
            width: 210mm;
            height: 145mm;
            page-break-after:always;
        }

        #header-tabel tr {
            padding: 0px;
        }
        #tabel-laporan {
            border-spacing: -1px;
            padding: 0px !important;
        }

        #tabel-laporan th{
            /*
            border-top: solid 1px #000;
            border-bottom: solid 1px #000;
            */
           border : solid 1px #000;
            margin: 0px;
            height: auto;
        }

        #tabel-laporan td{
            border : solid 1px #000;
            margin: 0px;
            height: auto;
        }
        #tabel-laporan {
          border-bottom:1px solid #000 !important;
        }

        .isi td{
          border-top:0px !important;
          border-bottom:0px !important;
        }

		 #grey
        {
             background:#eee;
        }

        #footer
        {
            /*width:180mm;*/
            margin:0 15mm;
            padding-bottom:3mm;
        }
        #footer table
        {
            width:100%;
            border-left: 1px solid #ccc;
            border-top: 1px solid #ccc;

            background:#eee;

            border-spacing:0;
            border-collapse: collapse;
        }
        #footer table td
        {
            width:25%;
            text-align:center;
            font-size:9pt;
            border-right: 1px solid #ccc;
            border-bottom: 1px solid #ccc;
        }

        img.resize {
          max-width:12%;
          max-height:12%;
        }
		.pagebreak
		{
		width:100% ;
		page-break-after: always;
		margin-bottom:10px;
		}
    </style>
</head>
<body>
<?php
$header	= $this->db->query("SELECT * FROM tr_invoice_payment_temp WHERE kd_pembayaran ='$kodebayar'")->row();
$coabank	= $header->kd_bank;
$coa	=  $this->db->query("SELECT * FROM ".DBACC.".coa_master WHERE no_perkiraan = '$coabank' ")->row();
?>
<table width=800>
<tr><td rowspan=2 width="100"><img src="<?=base_url("assets/images/ori_logo.jpg")?>" width="80"></td><td colspan=2><h1>PT. ORI POLYTEC COMPOSITES</h1></td></tr>
<tr><td><center><div style="font-size:18px;"><u>BUKTI PENERIMAAN BANK/KAS</u></div><?=$coa->nama?></center></td></tr>
<tr><td rowspan=2 valign=top>Diterima Dari</td><td rowspan=2 valign=top>: <?=$header->nm_customer?></td><td valign=top width="100">Nomor</td><td valign=top nowrap>: <?=$kodebayar?></td></tr>
<tr><td valign=top>Tanggal</td><td valign=top nowrap>: <?=date("d M Y",strtotime($header->tgl_pembayaran))?></td></tr>
<tr><td rowspan=2 valign=top>Say</td><td rowspan=2 valign=top>: <?php if($header->kurs_bayar>0){ 
	echo "# ". ucfirst(trim(ynz_terbilang_format($header->jumlah_bank)))." US dollar #";
}else{
	echo "# ". ucfirst(trim(ynz_terbilang_format($header->jumlah_bank_idr)))." rupiah #";
}?></td>
	<td valign=top>Mata Uang</td><td valign=top nowrap>: <?php echo($header->kurs_bayar>0?'USD':'IDR') ?></td></tr>
<tr><td valign=top>1 IDR</td><td valign=top>: <?php echo($header->kurs_bayar>0?$header->kurs_bayar: '1') ?></td></tr>
</table>
    <br>
	    <table valign="top" width="800" border=1 cellpadding=1 cellspacing=0>

		<?php
			echo "<tr>";
			echo "<th align='center' valign='top'>Tanggal</th>";
			echo "<th align='center' valign='top'>No Faktur</th>";
			echo "<th align='center' valign='top'>Note</th>";
			echo "<th align='center' valign='top'>Nilai</th>";
			echo "</tr>";
			$detail= $this->db->query("SELECT * FROM tr_invoice_payment_detail_temp WHERE kd_pembayaran ='$kodebayar'")->result();
			foreach($detail as $val=>$det){
				$kodeinv = $det->no_invoice;
				$invdtl= $this->db->query("SELECT * FROM tr_invoice_header WHERE no_invoice='$kodeinv'")->row();
		?>
		<tbody>
			<tr>
				<td class="text-center"><?=date("d M Y",strtotime($invdtl->tgl_invoice))?></td>
				<td class="text-center"><?=$det->no_invoice?></td>
				<td class="text-center"></td>
				<td class="text-right" align="right"><?php 
				if($header->kurs_bayar>0){
					echo  number_format($det->total_bayar,2);
				}else{
					echo  number_format($det->total_bayar_idr);
				}?></td>
			</tr>
		<?php
			}			
		?>
			<tr>
				<td class="text-center" colspan='2' rowspan=4></td>
				<td><b>Sub Total</b></td>
				<td class="text-right" align="right"><b>
				<?php 
				if($header->kurs_bayar>0){
					echo 'US'. number_format($header->jumlah_pembayaran,2);
				}else{
					echo 'Rp.'. number_format($header->jumlah_pembayaran_idr);
				}?></b></td>
			</tr>
			<tr>
				<td class="text-center"> <b>Administrasi Bank</b></td>
				<td class="text-right" align="right"><b>
				<?php 
				if($header->kurs_bayar>0){
					echo 'US'. number_format($header->biaya_admin,2);
				}else{
					echo 'Rp.'. number_format($header->biaya_admin_idr);
				}?></b></td>
			</tr>
			<tr>
				<td class="text-center"> <b>PPH</b></td>
				<td class="text-right" align="right"><b>
				<?php 
				if($header->kurs_bayar>0){
					echo 'US'. number_format($header->biaya_pph,2);
				}else{
					echo 'Rp.'. number_format($header->biaya_pph_idr);
				}?></b></td>
			</tr>
			<tr>
				<td class="text-center"> <b>Total </b></td>
				<td class="text-right" align="right"><b>
				<?php 
				if($header->kurs_bayar>0){
					echo 'US'. number_format($header->jumlah_bank,2);
				}else{
					echo 'Rp.'. number_format($header->jumlah_bank_idr);
				}?></b></td>
			</tr>
		</tbody>
		</table>
		<br>
		<table valign="top" width="800" border=1 cellpadding=1 cellspacing=0>
		<tr><th>DIBUAT</th><th>DIPERIKSA</th><th>DIBUKUKAN ACCT</th><th colspan=4>DISETUJUI OLEH</th><th>PENERIMAAN</th></tr>
		<tr><th width=100><br><br><br><br><br></th>
		<th width=100><br><br><br><br><br></th>
		<th width=120><br><br><br><br><br></th>
		<th width=90><br><br><br><br><br></th>
		<th width=90><br><br><br><br><br></th>
		<th width=90><br><br><br><br><br></th>
		<th width=90><br><br><br><br><br></th>
		<th width=100><br><br><br><br><br></th></tr>
		</table>
</body>
</html>


               <button class="btn btn-danger" > 
				<a href="<?= base_url() ?>penerimaan/index_draf">
				   <i class="fa fa-refresh"></i><b> Back</b>
			    </a> 
				</button>
