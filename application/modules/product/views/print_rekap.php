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
          border-bottom:1pt ridge black;
        }

        #summary table {
            border-collapse: collapse;
        }

        #summary th, td {
            border: 1px solid black;
        }
    </style>
</head>
<body> 
<div id="wrapper"> 
    <p style="text-align:center; font-weight:bold; padding-top:5mm;">REKAP DATA PRODUK</p>
    <table width="100%" id="tabel-laporan">
        <tr>
            <th width="15%">ID PRODUK</th>
            <th width="20%">JENIS PRODUK</th>
            <th width="15%">GROUP PRODUK</th>
            <th width="25%">NAMA PRODUK</th>
            <th width="5%">SATUAN</th>
            <th width="15%">NAMA COLLY PRODUK</th>
            <th width="5%">QTY</th>
            <th width="5%">SATUAN</th>
            <th width="10%">KOMPONEN</th>
            <th width="5%">QTY</th>
            <th width="5%">SATUAN</th>
        </tr>
        <?php           
        foreach($brg_data as $kd){ 
        $qty_set += $kd['qty'];           
        ?>
        <tr class="border_bottom" id='tabel-laporan'>                    
            <td width="10%" style="text-align: left;"><?=$kd['id_barang'];?></td>
            <td width="10%" style="text-align: left;"><?=strtoupper($kd['nm_jenis']);?></td>
            <td width="15%" style="text-align: left;"><?=strtoupper($kd['nm_group']);?></td>
            <td width="20%" style="text-align: left;"><?=$kd['nm_barang'];?></td>
            <td width="5%" style="text-align: center;"><?=$kd['satuan'];?></td>            
            <td width="20%" style="text-align: left;">
            <?php
            foreach($kol_data as $key => $y) {
                if($kd['id_barang'] == $y['id_barang']){
                    echo strtoupper($y['nm_koli'])."<BR>";
                }
            }
            ?>
            </td>
            <td width="5%" style="text-align: left;">
            <?php
            foreach($kol_data as $key => $y) {
                if($kd['id_barang'] == $y['id_barang']){
                    echo $y['qty']."<BR>";
                    $qty_colly += $y['qty'];
                }
            }
            ?>
            </td>            
            <td width="5%" style="text-align: left;">
            <?php            
            foreach($kol_data as $key => $y) {
                if($kd['id_barang'] == $y['id_barang']){
                    echo $y['satuan']."<BR>";
                }
            }
            ?>
            </td>
            <td width="20%" style="text-align: left;">
            <?php
            foreach($kol_data as $key => $y) {
                foreach($kom_data as $key => $xy) {
                    if($y['id_koli'] == $xy['id_koli']  && $kd['id_barang'] == $y['id_barang']){
                        echo strtoupper($xy['nm_komponen'])."<BR>";
                    }
                }
            }
            ?>
            </td>
            <td width="5%" style="text-align: center;">
            <?php            
            foreach($kol_data as $key => $y) {
                foreach($kom_data as $key => $xy) {
                    if($y['id_koli'] == $xy['id_koli'] && $kd['id_barang'] == $y['id_barang']){
                        echo $xy['qty']."<BR>";
                        $qty_pcs += $xy['qty'];
                    }
                }
            }
            ?>
            </td>
            <td width="5%" style="text-align: center;">
            <?php            
            foreach($kol_data as $key => $y) {
                foreach($kom_data as $key => $xy) {
                    if($y['id_koli'] == $xy['id_koli'] && $kd['id_barang'] == $y['id_barang']){
                        echo $xy['satuan']."<BR>";
                    }
                }
            }
            ?>
            </td>                
              
        </tr>
        <?php } ?>
    </table>    
    <br>
    <table id="summary">
        <tr>
        <th>Total : </th>
        <th>
            <?=$qty_set." <font color='red'><B>SET</B></font> ".$qty_colly." <font color='red'><B>COLLY</B></font> ".$qty_pcs." <font color='red'><B>PCS</B></font> "?>
        </th>
        </tr>
        <tr>
            <td colspan="2"><?="<font color='red'><B>NETTO WEIGHT</B></font> ".round($summary->netto_weight,2)." <font color='red'><B>CBM EACH</B></font> ".round($summary->cbm_each,2)." <font color='red'><B>GROSS WEIGHT</B></font> ".round($summary->gross_weight,2)?></td>
        </tr>
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