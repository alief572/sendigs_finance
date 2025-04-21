<?php
date_default_timezone_set("Asia/Bangkok");
$pathphoto = base_url().'photocustomer/'.$cust_data->foto;
?>
<!DOCTYPE html>
<html>
<head>
    <title><?=$cust_data->nm_customer."-".$cust_data->id_customer;?></title>
    <style>
                
        {
            margin:0;
            padding:0;
            font-family:Arial;
            font-size:10pt;
            color:#000;
        }
        body
        {
            width:100%;
            font-family:Arial;
            font-size:10pt;
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
            height:297mm;
            width:210mm;
            page-break-after:always;
        }
 
        table
        {
            border-left: 1px solid #ccc;
            border-top: 1px solid #ccc;
             
            border-spacing:0;
            border-collapse: collapse; 
             
        }
         
        table td 
        {
            border-right: 1px solid #ccc;
            border-bottom: 1px solid #ccc;
            padding: 2mm;
        }
         
        table.heading
        {
            height:20mm;
        }
         
        h1.heading
        {
            font-size:14pt;
            color:#000;
            font-weight:normal;
        }
         
        h2.heading
        {
            font-size:9pt;
            color:#000;
            font-weight:normal;
        }
         
        hr
        {
            color:#ccc;
            background:#ccc;
        }

        #cv_datadiri table
        {
            width:100%;
            border-left: 1px solid #ccc;
            border-top: 1px solid #ccc;
     
            border-spacing:0;
            border-collapse: collapse; 
             
            margin-top:5mm;
        }

        #cv_body
        {
            height: 149mm;
        }
         
        #cv_body , #invoice_total
        {   
            width:100%;
        }
        #cv_body table , #invoice_total table
        {
            width:100%;
            border-left: 1px solid #ccc;
            border-top: 1px solid #ccc;
     
            border-spacing:0;
            border-collapse: collapse; 
             
            margin-top:5mm;
        }
         
        #cv_body table td , #invoice_total table td
        {
            text-align:center;
            font-size:9pt;
            border-right: 1px solid #ccc;
            border-bottom: 1px solid #ccc;
            padding:2mm 0;
        }
         
        #cv_body table td.mono  , #invoice_total table td.mono
        {
            font-family:monospace;
            text-align:right;
            padding-right:3mm;
            font-size:10pt;
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
    </style>
</head>
<body>
<div id="wrapper">
    <img class="resize" src="<?=base_url('assets/images/ubi.png'); ?>">
    <p style="text-align:center; font-weight:bold; padding-top:5mm;">BIODATA CUSTOMER</p>
    <table class="heading" style="width:100%;">
        <tr>
            <td style="width:80mm;">
                <table style="tr:hover {border-right: 0px #f5f5f5;}">
                <tr>
                  <td style="width:50mm;">Nama Customer</td>
                  <td style="width:60mm;"><b><?=$cust_data->nm_customer;?></b></td>                  
                </tr>

                <tr>
                  <td style="width:50mm;">Bidang Usaha</td>
                  <td style="width:60mm;"><?=$cust_data->bidang_usaha;?></td>                  
                </tr>

                <tr>
                  <td style="width:50mm;">Produk yang dijual</td>
                  <td style="width:60mm;"><?=$cust_data->produk_jual;?></td>                  
                </tr>

                <tr>
                  <td style="width:50mm;">Kredibilitas</td>
                  <td style="width:60mm;"><?=$cust_data->kredibilitas;?></td>                  
                </tr>

                <tr>
                  <td style="width:50mm;">NPWP</td>
                  <td style="width:60mm;"><?=$cust_data->npwp;?></td>                  
                </tr>

                <tr>
                  <td style="width:50mm;">Alamat NPWP</td>
                  <td style="width:60mm;"><?=$cust_data->alamat_npwp;?></td>                  
                </tr>

                <tr>
                  <td style="width:50mm;">Referensi</td>
                  <td style="width:60mm;"><?=$cust_data->referensi;?></td>                  
                </tr>

                <tr>
                  <td style="width:50mm;">No Telpon / Fax</td>
                  <td style="width:60mm;"><?=$cust_data->telpon." / ".$cust_data->fax;?></td>                  
                </tr>
              </table>              
            </td>
            <td rowspan="2" valign="top" align="center" style="width: :20mm;">
                <img src="<?php echo (isset($cust_data->foto) ? $pathphoto : base_url('photocustomer/cowo.png')); ?>" class="user-image" alt="User Image"><br>                
                <h1 class="heading"><b><?=$cust_data->id_customer;?></b></h1><br>                
            </td>
        </tr>
        <tr>
            <td>                
            Alamat<br/>
            <?php $kodepos = " kode pos ".$cust_data->kode_pos." ";?>
            <i><?=$cust_data->alamat?></i><br>
            <?=(isset($kodepos) ? $kodepos :" ").strtolower($inisial->kota)." Provinsi ".strtolower($inisial->provinsi);?>
            </td>
        </tr>
    </table>
         
         
    <div id="content">
         
        <div id="cv_body">
            <h2 class="heading"><b>Data Toko</b>
            <table>
            <tr style="background:#eee;">
                <td style="width:8%;"><b>No</b></td>
                <td style="width:25%;"><b>Nama Toko</b></td>
                <td style="width:15%;"><b>Area</b></td>
                <td style="width:15%;"><b>Alamat</b></td>
                <td style="width:15%;"><b>Telp</b></td>                
            </tr>
              <?= $no=1; foreach ($cust_toko as $toko){?>  
            <tr>
              <td style="width:8%;"><?=$no?></td>
              <td style="text-align:left; padding-left:10px;"><?=$toko->nm_toko;?>
              </td>
              <td><?=$toko->area?></td>
              <td><?=$toko->alamat_toko?></td>
              <td><?=$toko->telpon_toko?></td>
            </tr>
            <?=$no++;
            }?>             
            </table>

            <h2 class="heading"><b>Data PIC</b>
            <table>
            <tr style="background:#eee;">
                <td style="width:8%;"><b>No</b></td>
                <td style="width:25%;"><b>Nama PIC</b></td>
                <td style="width:15%;"><b>Divisi</b></td>
                <td style="width:15%;"><b>Jabatan</b></td>
                <td style="width:15%;"><b>Kontak</b></td>                
            </tr>
              <?= $no=1; foreach ($cust_pic as $pic){?>  
            <tr>
              <td style="width:8%;"><?=$no?></td>
              <td style="text-align:left; padding-left:10px;"><?=$pic->nm_pic;?>
              </td>
              <td><?=$pic->divisi?></td>
              <td><?=$pic->jabatan?></td>
              <td><?=$pic->hp?></td>
            </tr>
            <?=$no++;
            }?>             
            </table>
        </div>
     
    <?php $tglprint = date("d-m-Y H:i:s");?>     
    <htmlpagefooter name="footer">
        <hr />
        <div id="footer"> 
        <table>
            <tr><td>PT IMPORTA JAYA ABADI - Printed By <?php echo ucwords($userData->nm_lengkap) ." On ". $tglprint; ?></td></tr>
        </table>
        </div>
    </htmlpagefooter>
    <sethtmlpagefooter name="footer" value="on" />
     
</body>
</html>