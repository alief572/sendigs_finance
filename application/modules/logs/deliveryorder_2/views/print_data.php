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
        <table width="100%" border="0" id="header-tabel">
        <tr>
            <th colspan="3" width="20%" style="text-align: left;">PT IMPORTA JAYA ABADI<br>YOGYAKARTA</th>
            <th style="border-right: none;">DELIVERY ORDER (DO)<br><?php echo 'NO. : '.@$do_data->no_do?></th>
            <th colspan="3" style="border-left: none;"></th>
        </tr>
        <tr>
            <td width="10%">NO.REFF</td>
            <td width="1%">:</td>
            <td colspan="2"></td>
            <td width="15%">TGL DO</td>
            <td width="1%">:</td>
            <td><?php echo date('d-M-Y',strtotime(@$do_data->tgl_do))?></td>
        </tr>
        <tr>
            <td width="10%">SALES</td>
            <td width="1%">:</td>
            <td colspan="2"><?php echo strtoupper(@$do_data->nm_salesman)?></td>
            <td width="10%">SUPIR</td>
            <td width="1%">:</td>
            <td><?php echo strtoupper(@$do_data->nm_supir)?></td>
        </tr>
        <tr>
            <td width="10%">CUSTOMER</td>
            <td width="1%">:</td>
            <td colspan="2"><?php echo strtoupper(@$do_data->nm_customer)?></td>
            <td width="10%">KENDARAAN</td>
            <td width="1%">:</td>
            <td><?php echo strtoupper(@$do_data->id_kendaraan)?></td>
        </tr>
        <tr>
            <td width="10%">ALAMAT</td>
            <td width="1%">:</td>
            <td colspan="2"><?php echo @$customer->alamat?></td>
            <td width="10%">KETERANGAN</td>
            <td width="1%">:</td>
            <td></td>
        </tr>
    </table>
    <table width="100%" id="tabel-laporan">
        <tr>
            <th width="2%">NO</th>
            <th width="48%">NAMA BARANG</th>
            <th width="10%">SATUAN</th>
            <th width="10%">QTY ORDER</th>
            <th width="10%">QTY SUPPLY</th>
            <th width="20%">KET</th>
        </tr>
        <?php
        $n=1;
        foreach(@$detail as $kd=>$vd){
            $no=$n++;
        ?>
        <tr>
            <td><center><?php echo $no?></center></td>
            <td><?php echo $vd->id_barang.' / '.$vd->nm_barang?></td>
            <td><center><?php echo $vd->satuan?></center></td>
            <td><center><?php echo $vd->qty_order?></center></td>
            <td><center><?php echo $vd->qty_supply?></center></td>
            <td><center>-</center></td>
        </tr>
        <?php } ?>
    </table>
    </div> 
    <?php $tglprint = date("d-m-Y H:i:s");?>     
<htmlpagefooter name="footer">
    <hr>
    <table width="100%" border="0">
        <tr>
            <td width="30%"><center>Marketing</center></td>
            <td width="40%"><center>Supir</center></td>
            <td width="30%"><center>Customer,</center></td>
        </tr>
        <tr>
            <td width="15%"></td>
            <td width="15%"></td>
            <td width="15%"></td>
        </tr>
        <tr>
            <td width="15%"></td>
            <td width="15%"></td>
            <td width="15%"></td>
        </tr>
        <tr>
            <td width="15%"></td>
            <td width="15%"></td>
            <td width="15%"></td>
        </tr>
        <tr>
            <td width="15%"><center>( <?php echo strtoupper(@$do_data->nm_salesman) ?> )</center></td>
            <td width="15%"><center>( <?php echo strtoupper(@$do_data->nm_supir) ?> )</center></td>
            <td width="15%"><center>( Customer )</center></td>
        </tr>
    </table>
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