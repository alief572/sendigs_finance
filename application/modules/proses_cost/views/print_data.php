<?php
date_default_timezone_set("Asia/Bangkok");
?>
<!DOCTYPE html>
<html>
<head>
    <title><?=$sup_data->nm_supplier."-".$sup_data->id_supplier;?></title>
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
    <p style="text-align:center; font-weight:bold; padding-top:5mm;">DATA SUPPLIER</p>
    <br>
    <table style="tr:hover {border-right: 0px #f5f5f5;}" align="center">
        <tr>
            <td style="width:50mm;">ID Supplier</td>
            <td style="width:60mm;" colspan="2"><b><?=$sup_data->id_supplier;?></b></td>                  
        </tr>

        <tr>
            <td style="width:50mm;">Nama Supplier</td>
            <td style="width:60mm;" colspan="2"><?=$sup_data->nm_supplier;?></td>                  
        </tr>

        <tr>
            <td style="width:50mm;">Negara</td>
            <td style="width:60mm;" colspan="2"><?=$sup_data->nm_negara;?></td>                  
        </tr>                

        <tr>
            <td style="width:50mm;">Telpon / Fax</td>
            <td style="width:60mm;" colspan="2"><?=$sup_data->telpon." / ".$sup_data->fax;?></td>                  
        </tr>  

        <tr>
            <td style="width:50mm;">Kontak Person</td>
            <td style="width:60mm;" colspan="2"><?=$sup_data->cp;?></td>                  
        </tr>                    

        <tr>
            <td style="width:50mm;">Handphone / WeChat ID</td>
            <td style="width:60mm;" colspan="2"><?=$sup_data->hp_cp." / ".$sup_data->id_webchat;?></td>                 
        </tr>

        <tr>
            <td style="width:50mm;">Alamat</td>
            <td style="width:60mm;" colspan="2"><?=$sup_data->alamat;?></td>                  
        </tr>                               
    </table>
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