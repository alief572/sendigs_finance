<?php
date_default_timezone_set("Asia/Bangkok");
?>
<!DOCTYPE html>
<html>
<head>
    <title><?=$brg_data->nm_barang."-".$brg_data->id_barang;?></title>
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
    <p style="text-align:center; font-weight:bold; padding-top:5mm;">DATA PRODUK</p>
    <br>
    <table style="tr:hover {border-right: 0px #f5f5f5;}" align="center">
        <tr>
            <td style="width:50mm;">Kode Produk</td>
            <td style="width:60mm;" colspan="2"><b><?=$brg_data->id_barang;?></b></td>                  
        </tr>

        <tr>
            <td style="width:50mm;">Nama Produk</td>
            <td style="width:60mm;" colspan="2"><?=$brg_data->nm_barang;?></td>                  
        </tr>

        <tr>
            <td style="width:50mm;">Brand</td>
            <td style="width:60mm;" colspan="2"><?=$brg_data->brand;?></td>                  
        </tr>                

        <tr>
            <td style="width:50mm;">Satuan</td>
            <td style="width:60mm;" colspan="2"><?=$brg_data->satuan;?></td>                  
        </tr>                    

        <tr>
            <td><b>Colly</b></td>
            <td><b>Component</b></td>
            <td><b>Qty</b></td>                    
        </tr>
            <?php
            echo "<tr>";
            foreach ($kol_data as $d){
                echo "<tr>";
                echo "<td style='width:60mm;' colspan='3'>".$d['nm_koli']."</td>"; 
                $qty_pcs += $d['qty'];                      
                echo "</tr>";            
            foreach ($kom_data as $dx){
                if($d['id_koli']==$dx['id_koli']){
                    echo "<tr style='tr:hover {border-right: 0px #f5f5f5;}'>";
                    echo "<td style='width:30mm;'></td>"; 
                    echo "<td style='width:70mm;'>".$dx['nm_komponen']."</td>"; 
                    echo "<td align='right'>".$dx['qty']."</td>";    
                    echo "</tr>";
                }               
            }            
            } 
            echo "</tr>";
            if(empty($qty_pcs) || $qty_pcs==''){
                    $qty_pcs = '0';
                }else{
                    $qty_pcs = $qty_pcs;
                }       
            ?>     
    </table>
    <br>
    <table>
        <tr>
        <td><b>Total : </b></td>
        <td>
            <?=$qty_set." <font color='red'><B>SET</B></font> ".$brg_data->qty." <font color='red'><B>COLLY</B></font> ".$qty_pcs." <font color='red'><B>PCS</B></font> "?>
        </td>
        </tr>
        <tr>
            <td colspan="2"><?="<font color='red'><B>NETTO WEIGHT</B></font> ".round($brg_data->netto_weight,2)." <font color='red'><B>CBM EACH</B></font> ".round($brg_data->cbm_each,2)." <font color='red'><B>GROSS WEIGHT</B></font> ".round($brg_data->gross_weight,2)?></td>
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