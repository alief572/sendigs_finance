<?php
date_default_timezone_set("Asia/Bangkok");
?>
<!DOCTYPE html>
<html>
<head>
    <title></title>
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
            /*height:297mm;
            width:210mm;*/
            width: 297mm;
            height: 210mm;
            page-break-after:always;
        }

        #header-tabel tr {
            padding: 0px;
        }

        #tabel-laporan {
            border-spacing: -1px;
        }

        #tabel-laporan th{
            border-top: solid 1px #000;
            border-bottom: solid 1px #000;
            margin: 0px;
        }

        #tabel-laporan tr{
            border-top: solid 1px #000;
            border-bottom: solid 1px #000;
            margin: 0px;
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

        th, td {
            padding: 15px;
            text-align: left;
        }

        tr.border_bottom td {
          border-bottom:1px dotted black;
        }
    </style>
</head>
<body> 
<div id="wrapper"> 
    <p style="text-align:center; font-weight:bold; padding-top:5mm;">REKAP DATA CUSTOMER</p>
    <table width="100%" id="tabel-laporan">
        <tr>
            <th width="10%">ID CUSTOMER</th>
            <th width="15%">NAMA CUSTOMER</th>
            <th width="15%">BIDANG USAHA</th>
            <th width="15%">MARKETING</th>
            <th width="5%">KREDIBILITAS</th>
            <th width="15%">PRODUK</th>
            <th width="25%">ALAMAT</th>
        </tr>
        <?php           
        foreach($data_cus as $kd){            
        ?>
        <tr class="border_bottom">                    
            <td width="10%" style="text-align: left;"><?=$kd['id_customer'];?></td>   
            <td width="15%" style="text-align: left;"><?=$kd['nm_customer'];?></td>
            <td width="15%" style="text-align: left;"><?=$kd['bidang_usaha'];?></td>
            <td width="15%" style="text-align: left;"><?=$kd['nama_karyawan'];?></td>
            <td width="5%" style="text-align: left;"><?=$kd['kredibilitas'];?></td>
            <td width="5%" style="text-align: left;"><?=$kd['produk_jual'];?></td>
            <td width="5%" style="text-align: left;"><?=$kd['alamat'];?></td>         
        </tr>
        <?php } ?>
    </table>    
</div>
    <?php $tglprint = date("d-m-Y H:i:s");?>     
<htmlpagefooter name="footer">    
    <div id="footer"> 
    <table>
        <tr><td>PT IMPORTA JAYA ABADI - Printed By <?php echo ucwords($userData->nm_lengkap) ." On ". $tglprint; ?></td></tr>
    </table>
    </div>
</htmlpagefooter>
<sethtmlpagefooter name="footer" value="on" />  
</body>
</html> 