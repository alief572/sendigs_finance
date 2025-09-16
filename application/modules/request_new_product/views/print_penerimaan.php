<html>
<head>
<style type="text/css">
        .header_style_company{
            padding: 15px;
            color: black;
            font-size: 20px;
            vertical-align:bottom;
        }
        .header_style_company2{
            padding: 15px;
            color: black;
            font-size: 15px;
            vertical-align:top;
        }

        .header_style_alamat{
            padding: 10px;
            color: black;
            font-size: 10px;
        }

        table.default {
            font-family: arial,sans-serif;
            font-size:9px;
            padding: 0px;
        }

        p{
            font-family: arial,sans-serif;
            font-size:14px;
        }
        
        .font{
            font-family: arial,sans-serif;
            font-size:14px;
        }

        table.gridtable {
            font-family: arial,sans-serif;
            font-size:11px;
            color:#333333;
            border: 1px solid #808080;
            border-collapse: collapse;
        }
        table.gridtable th {
            padding: 6px;
            background-color: #f7f7f7;
            color: black;
            border-color: #808080;
            border-style: solid;
            border-width: 1px;
        }
        table.gridtable th.head {
            padding: 6px; 
            background-color: #f7f7f7;
            color: black;
            border-color: #808080;
            border-style: solid;
            border-width: 1px;
        }
        table.gridtable td {
            border-width: 1px;
            padding: 6px;
            border-style: solid;
            border-color: #808080;
        }
        table.gridtable td.cols {
            border-width: 1px;
            padding: 6px;
            border-style: solid;
            border-color: #808080;
        }


        table.gridtable2 {
            font-family: arial,sans-serif;
            font-size:12px;
            color:#333333;
            border-width: 1px;
            border-color: #666666;
            border-collapse: collapse;
        }
        table.gridtable2 td {
            border-width: 1px;
            padding: 1px;
            border-style: none;
            border-color: #666666;
            background-color: #ffffff;
        }
        table.gridtable2 td.cols {
            border-width: 1px;
            padding: 1px;
            border-style: none;
            border-color: #666666;
            background-color: #ffffff;
        }

        table.gridtableX {
            font-family: arial,sans-serif;
            font-size:12px;
            color:#333333;
            border: 1px;
            border-collapse: collapse;
        }
        table.gridtableX td {
            border-width: 1px;
            padding: 6px;
        }
        table.gridtableX td.cols {
            border-width: 1px;
            padding: 6px;
        }

        table.gridtableX2 {
            font-family: arial,sans-serif;
            font-size:12px;
            color:#333333;
            border: none;
            border-collapse: collapse;
        }
        table.gridtableX2 td {
            border-width: 1px;
            padding: 2px;
        }
        table.gridtableX2 td.cols {
            border-width: 1px;
            padding: 2px;
        }

        #testtable{
            width: 100%;
        }
    </style>
</head>
<body>
<?php
// print_r($id_bq);
// exit;
$data_header        = $this->db->query("SELECT * FROM tr_invoice_payment WHERE kd_pembayaran ='$kodebayar'")->row();
$data_detail        = $this->db->query("SELECT sum(total_bayar_idr) as total_bayar FROM tr_invoice_payment_detail WHERE kd_pembayaran ='$kodebayar'")->row();
//$alamat_cust        =  $this->db->query("SELECT * FROM master_customer WHERE id_customer = '$data_header->id_customer'")->row();
//$pay_term           =  $this->db->query("SELECT * FROM quotation_payment_term WHERE id_quotation = '$data_header->no_ipp'")->row(); 
$jenis				=  $data_header->jenis_invoice;
$ppn				=  $data_header->total_ppn;
// $dt_fabric          = $this->db->query("SELECT * FROM tr_invoice_detail WHERE no_invoice ='$no_invoice' AND kategori_detail ='FABRIC'")->result();
// $rail               = $this->db->query("SELECT * FROM tr_invoice_detail WHERE no_invoice ='$no_invoice' AND kategori_detail ='BQ'")->result();
// $sew                = $this->db->query("SELECT * FROM tr_invoice_detail WHERE no_invoice ='$no_invoice' AND kategori_detail ='SEWING'")->result();
// $airfreight         = $this->db->query("SELECT * FROM tr_invoice_detail WHERE no_invoice ='$no_invoice' AND kategori_detail ='AIRFREIGHT'")->result();
// $akomodasi          = $this->db->query("SELECT * FROM tr_invoice_detail WHERE no_invoice ='$no_invoice' AND kategori_detail ='AKOMODASI'")->result();
// $acc                = $this->db->query("SELECT * FROM tr_invoice_detail WHERE no_invoice ='$no_invoice' AND kategori_detail ='ACCESSORIES'")->result();
?>

<?php
$inv          =  $this->db->query("SELECT * FROM tr_invoice WHERE no_invoice = '$data_header->no_invoice' ")->row();
$alamat_cust =  $this->db->query("SELECT * FROM master_customers WHERE name_customer = '$data_header->nm_customer'")->row();
$coa = $data_header->kd_bank;
				
$nmbank =$this->db->query("SELECT nama FROM gl_waterco.coa_master WHERE no_perkiraan='$coa'")->row();
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
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
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
<div style='display:block; border-color:none; background-color:#c2c2c2;' align='center'>
    <h3>PENERIMAAN</h3>
</div>
<br>
<table class='gridtable2' width='100%' border='1' align='left' cellpadding='0' cellspacing='0'>
    <tr>
        <td width="300" align="center">
            <table width='300' align="center">
                <tr><td width='75' align="left">Kode Bayar </td><td width='10' align="left">:</td><td width='250' align="left"><?= $data_header->kd_pembayaran ?></td></tr>
                <tr><td width='75' align="left">Tgl Bayar</td><td width='10' align="left">:</td><td width='250' align="left"><?= tgl_indo($data_header->tgl_pembayaran)  ?></td></tr>
                <tr><td width='75' align="left">Nama Customer</td><td width='10' align="left">:</td><td width='250' align="left"><?= $data_header->nm_customer ?></td></tr>
                <tr><td width='75' align="left">Alamat Customer</td><td width='10' align="left">:</td><td width='250' align="left"><?= $alamat_cust->address_office ?></td></tr>
                <tr><td width='75' align="left">Bank </td><td width='10' align="left">:</td><td width='250' align="left"><?= $nmbank->nama ?></td></tr>
                <tr><td width='75' align="left">Keterangan pembayaran</td><td width='10' align="left">:</td><td width='250' align="left"><?= $data_header->keterangan ?></td></tr>
            </table>
        </td>
        <td width="300" align="center">
            <table width='300' align="center">
                <tr><td width='75' align="left">Total Invoice Bayar</td><td width='10' align="left">:</td><td width='250' align="left"><?= number_format($data_detail->total_bayar) ?></td></tr>
                <tr><td width='75' align="left">Jumlah Penerimaan bank</td><td width='10' align="left">:</td><td width='250' align="left"><?=  number_format($data_header->jumlah_bank_idr,2)  ?></td></tr>
                <tr><td width='75' align="left">Biaya Administrasi</td><td width='10' align="left">:</td><td width='250' align="left"><?= number_format($data_header->biaya_admin_idr,2)?></td></tr>
                <tr><td width='75' align="left">PPH</td><td width='10' align="left">:</td><td width='250' align="left"><?=number_format($data_header->biaya_pph_idr,2)?></td></tr>
                <tr><td width='75' align="left">Total Penerimaan </td><td width='10' align="left">:</td><td width='250' align="left"><?=number_format($data_header->jumlah_pembayaran_idr,2)?></td></tr>
                <tr><td width='75' align="left">Lebih Bayar </td><td width='10' align="left">:</td><td width='250' align="left"><?=number_format($data_header->tambah_lebih_bayar,2)?></td></tr>
                
           </table>
        </td>
    </tr>
</table>
<br>
    
		<table class='gridtable'  cellpadding='0' cellspacing='0' style='vertical-align:top;'>
        <tbody>
            <tr style='vertical-align:middle; background-color:#c2c2c2; font-weight:bold;'>
                <td align="center" width='50'>No Invoice</td>
                <td align="center" width='330'>Nama Customer</td>
                <td align="center" width='40'>Total Invoice </td>
				<td align="center" width='40'>Sisa Invoice</td>
                <td align="center" width='50'>Total Bayar </td>
               
            </tr>
										
															
								
								<?php
								    
									$detail        = $this->db->query("SELECT * FROM tr_invoice_payment_detail WHERE kd_pembayaran ='$kodebayar'")->result();
									
									

									
									foreach($detail as $val=>$det){
										
									$kodeinv = $det->no_invoice;
									
									$inv_det  = $this->db->query("SELECT * FROM tr_invoice WHERE no_invoice ='$kodeinv'")->row();
									
									
								
								?>
								
								
									<tr>
										<td><?php echo $inv_det->no_surat ?></td>
										<td><?php echo $data_header->nm_customer ?></td>
										<td><?php echo  number_format($inv_det->nilai_invoice,2)?></td>
										<td><?php echo  number_format($inv_det->sisa_invoice_idr,2)?></td>
										<td><?php echo  number_format($det->total_bayar_idr,2)?></td>
										
									</tr>
								
								<?php
								
										}
								
								?>
							</tbody>	
							</table>
	<br>

	<table border="0" width='100%' align="left">

		<tr>
		
			<td width="250" align="left"><br><br>
			<table>
			<tr><td align='center'></td></tr>
			<tr><td align="center"width="70%" valign="top" >
           </td></tr>
			<tr><td align='center'> </td></tr>
			<tr><td align='center'></td></tr>	
			</table>
			</td>
			<td width="250" align="left"><br><br>
			<table>
			<tr><td align='center'></td></tr>
			<tr><td align="center"width="70%" valign="top" >
			 </td></tr>
			<tr><td align='center'></td></tr>
			<tr><td align='center'></td></tr>	
			</table>
			</td>
			<td width="250" align="left"><br><br>
			<table>
			<tr><td align='center'></td></tr>
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
			<tr><td align='center'></td></tr>	
			</table>
			</td>
		</tr>
	</table>
    

    
</body>
</html>