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
    <p style="text-align:center; font-weight:bold; padding-top:5mm;">REKAP DATA KARYAWAN</p>
    <table width="100%" id="tabel-laporan">
        <tr>
            <th width="10%">NIK</th>
            <th width="15%">NAMA KARYAWAN</th>
            <th width="15%">TTL</th>
            <th width="5%">STATUS</th>
            <th width="5%">TGL BERGABUNG</th>
            <th width="5%">TGL AKHIR</th>
            <th width="5%">JENIS KELAMIN</th>
            <th width="5%">AGAMA</th>
            <th width="5%">DIVISI</th>
            <th width="5%">NOREKENING</th>
            <th width="5%">NO HP</th>
            <th width="25%">ALAMAT</th>
        </tr>
        <?php           
        foreach($data_kar as $kd){            
        ?>
        <tr class="border_bottom">                    
            <td width="10%" style="text-align: left;"><?=$kd['nik'];?></td>   
            <td width="15%" style="text-align: left;"><?=$kd['nama_karyawan'];?></td>
            <td width="15%" style="text-align: left;"><?=$kd['tempatlahir'].', '.date('d-m-Y', strtotime($kd['tanggallahir']));?></td>
            <td width="5%" style="text-align: left;"><?=$kd['sts_karyawan'];?></td>
            <td width="5%" style="text-align: left;"><?=date('d-m-Y', strtotime($kd['tgl_join']));?></td>
            <td width="5%" style="text-align: center;"><?=date('d-m-Y', strtotime($kd['tgl_end']));?></td>            
            <td width="5%" style="text-align: left;"><?=$kd['jeniskelamin'];?></td>                            
            <td width="5%" style="text-align: left;"><?=$kd['agama'];?></td>
            <td width="5%" style="text-align: left;"><?=$kd['nm_divisi'];?></td>
            <td width="5%" style="text-align: left;"><?=$kd['norekening'];?></td>
            <td width="5%" style="text-align: left;"><?=$kd['nohp'];?></td>
            <td width="5%" style="text-align: left;"><?=$kd['alamataktif'];?></td>         
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