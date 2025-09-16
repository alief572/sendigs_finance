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
$kodebayar	= $headers->no_payment;
$coabank	= $headers->bank_coa;
$coa	=  $this->db->query("SELECT * FROM ".DBACC.".coa_master WHERE no_perkiraan = '$coabank' ")->row();
$supplier	=  $this->db->query("SELECT * FROM supplier WHERE id_supplier= '$headers->id_supplier' ")->row();
?>
<table width=800>
<tr><td rowspan=2 width="100"><img src="<?=base_url("assets/images/ori_logo.jpg")?>" width="80"></td><td colspan=2><h1>PT. ORI POLITEC COMPOSITES</h1></td></tr>
<tr><td><center><div style="font-size:18px;"><u>BUKTI PENGELUARAN BANK/KAS</u></div><?=$coa->nama?></center></td></tr>
<tr><td rowspan=2 valign=top>Supplier</td><td rowspan=2 valign=top>: <?=$supplier->nm_supplier?></td><td valign=top width="100">Nomor</td><td valign=top nowrap>: <?=$kodebayar?></td></tr>
<tr><td valign=top>Tanggal</td><td valign=top nowrap>: <?=date("d M Y",strtotime($headers->payment_date))?></td></tr>
<tr><td rowspan=2 valign=top>Say</td><td rowspan=2 valign=top>: <?php if($headers->curs_header!='IDR'){ 
	echo "# ". ucfirst(trim(ynz_terbilang_format($headers->nilai_bayar_bank)))." US dollar #";
}else{
	echo "# ". ucfirst(trim(ynz_terbilang_format($headers->nilai_bayar_bank)))." rupiah #";
}?></td>
	<td valign=top>Mata Uang</td><td valign=top nowrap>: <?php echo($headers->curs_header) ?></td></tr>
<tr><td valign=top>1 IDR</td><td valign=top>: <?php echo($headers->curs) ?></td></tr>
</table>
    <br>
	    <table valign="top" width="800" border=1 cellpadding=1 cellspacing=0>

		<?php
			echo "<tr>";
			echo "<th align='center' valign='top'>Tanggal Terima</th>";
			echo "<th align='center' valign='top'>No PO</th>";
			echo "<th align='center' valign='top'>No Invoice</th>";
			echo "<th align='center' valign='top'>Nilai</th>";
			echo "</tr>";
			$detail= $this->db->query("SELECT * FROM purchase_order_request_payment WHERE no_payment ='$kodebayar'")->result();
			foreach($detail as $val=>$det){
				$dtpo= $this->db->query("SELECT * FROM billing_top WHERE id='".$det->id_top."'")->row();
		?>
		<tbody>
			<tr>
				<td class="text-center"><?=date("d M Y",strtotime($dtpo->tgl_terima))?></td>
				<td class="text-center"><?=$det->no_po?></td>
				<td class="text-center"><?=$dtpo->invoice_no?></td>
				<td class="text-right" align="right"><?php 
				if($headers->curs_header!='IDR'){
					echo  number_format($det->request_payment,2);
				}else{
					echo  number_format($det->request_payment);
				}?></td>
			</tr>
		<?php
			}			
		?>
			<tr>
				<td class="text-center" colspan='2' rowspan=4></td>
				<td><b>Total</b></td>
				<td class="text-right" align="right"><b>
				<?php 
				if($headers->curs_header!='IDR'){
					echo 'US'. number_format($headers->nilai_bayar_bank,2);
				}else{
					echo 'Rp.'. number_format($headers->nilai_bayar_bank);
				}?></b></td>
			</tr>
			<tr>
				<td class="text-center"> <b>Administrasi Bank 1</b></td>
				<td class="text-right" align="right"><b>
				<?php 
				if($headers->curs_header!='IDR'){
					echo 'US'. number_format($headers->biaya_admin_forex,2);
				}else{
					echo 'Rp.'. number_format($headers->biaya_admin);
				}?></b></td>
			</tr>
			<tr>
				<td class="text-center"> <b>Administrasi Bank 2</b></td>
				<td class="text-right" align="right"><b>
				<?php 
				if($headers->curs_header!='IDR'){
					echo 'US'. number_format($headers->biaya_admin_forex2,2);
				}else{
					echo 'Rp.'. number_format($headers->biaya_admin2);
				}?></b></td>
			</tr>
		</tbody>
		</table>
		<br>
		<table valign="top" width="800" border=1 cellpadding=1 cellspacing=0>
		<tr><th>DIBUAT</th><th>DIPERIKSA</th><th>DIBUKUKAN ACCT</th><th colspan=4>DISETUJUI OLEH</th><th>PENGELUARAN</th></tr>
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
