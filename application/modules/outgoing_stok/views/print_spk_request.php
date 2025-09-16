<?php

$sroot 		= $_SERVER['DOCUMENT_ROOT'].'origa_live/';
include $sroot."/application/libraries/MPDF57/mpdf.php";
$mpdf=new mPDF('utf-8','A4');

set_time_limit(0);
ini_set('memory_limit','1024M');

//Beginning Buffer to save PHP variables and HTML tags
ob_start();
date_default_timezone_set('Asia/Jakarta');
$today = date('l, d F Y [H:i:s]');
?>

<table class="gridtable2" border='1' width='100%' cellpadding='2'>
    <tr>
        <td align='center'><b>PT ORIGA MULIA</b></td>
    </tr>
    <tr>
        <td align='center'><b><h2>OUTGOING BARANG STOK</h2></b></td>
    </tr>
</table>
<br>
<br>
<table class="gridtable2" width="100%" border='0'>
    <thead>
        <tr>
            <td class="mid">No Transaksi</td>
            <td class="mid">:</td>
            <td class="mid"><?=$getData[0]['kode_trans'];?></td>
            <td class="mid"></td>
            <td class="mid"></td>
            <td class="mid"></td>
        </tr>
        <tr>
            <td class="mid" width='18%'>Gudang</td>
            <td class="mid" width='2%'>:</td>
            <td class="mid" width='30%'><?=strtoupper(get_name('warehouse','nm_gudang','id',$getData[0]['id_gudang_dari']));?></td>
            <td class="mid" width='18%'>Costcenter</td>
            <td class="mid" width='2%'>:</td>
            <td class="mid" width='30%'><?=strtoupper(get_name('warehouse','nm_gudang','id',$getData[0]['id_gudang_ke']));?></td>
        </tr>
        <tr>
            <td class="mid">Department</td>
            <td class="mid">:</td>
            <td class="mid"><?=strtoupper(get_name('ms_department','nama','id',$getData[0]['id_dept']));?></td>
            <td class="mid">Tanggal</td>
            <td class="mid">:</td>
            <td class="mid"><?=tgl_indo($getData[0]['tanggal']);?></td>
        </tr>
        <tr>
            <td class="mid">PIC</td>
            <td class="mid">:</td>
            <td class="mid"><?=$getData[0]['pic'];?></td>
            <td class="mid">Note</td>
            <td class="mid">:</td>
            <td class="mid"><?=$getData[0]['note'];?></td>
        </tr>
    </thead>
</table><br>
<table class="gridtable" width='100%' border='1' cellpadding='2'>
    <thead align='center'>
        <tr>
            <th class="mid" width='5%'>No</th>
            <th class="mid" style='vertical-align:middle;'>Stok Name</th>
            <th class="mid" width='9%'>Qty</th>
            <!-- <th class="mid" width='6%'>Pack</th>
            <th class="mid" width='8%'>Konversi</th>
            <th class="mid" width='10%'>Req. Unit</th> -->
            <th class="mid" width='9%'>Unit</th>
            <!-- <th class="mid" width='11%'>Pengeluaran</th> -->
            <th class="mid" width='20%'>Keterangan</th> 
        </tr>
    </thead>
    <tbody>
        <?php
        $No=0;
        foreach ($getDataDetail as $key => $value) { $No++;
            $id_material 	= $value['id_material'];
            $nm_material    = (!empty($GET_MATERIAL[$id_material]['nama']))?$GET_MATERIAL[$id_material]['nama']:0;
            $id_packing     = (!empty($GET_MATERIAL[$id_material]['id_packing']))?$GET_MATERIAL[$id_material]['id_packing']:0;
            $id_unit     	= (!empty($GET_MATERIAL[$id_material]['id_unit']))?$GET_MATERIAL[$id_material]['id_unit']:0;
            $konversi       = (!empty($GET_MATERIAL[$id_material]['konversi']))?$GET_MATERIAL[$id_material]['konversi']:0;
            $packing        = (!empty($GET_SATUAN[$id_packing]['code']))?$GET_SATUAN[$id_packing]['code']:0;
            $unit        	= (!empty($GET_SATUAN[$id_unit]['code']))?$GET_SATUAN[$id_unit]['code']:0;
            $berat_req = 0;
            $berat_req = $value['qty_order'];
            // if($value['qty_order'] > 0 AND $konversi > 0){
            // $berat_req		= $value['qty_order'] / $konversi;
            // }
            echo "<tr>";
                echo "<td align='center'>".$No."</td>";
                echo "<td>".$nm_material."</td>";
                echo "<td align='center'>".number_format($berat_req,2)."</td>";
                // echo "<td align='center'>".strtoupper($packing)."</td>";
                // echo "<td align='center'>".number_format($konversi,2)."</td>";
                // echo "<td align='center'>".number_format($berat_req * $konversi,2)."</td>";
                echo "<td align='center'>".strtoupper($unit)."</td>";
                // echo "<td align='right'></td>";
                echo "<td>".$value['keterangan']."</td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table><br><br><br>
<table class="gridtable2" width='100%' border='0' cellpadding='2'>
    <tr>
        <td width='65%'></td>
        <td>Disiapkan,</td>
        <td></td>
        <td>Penerima,</td>
        <td></td>
    </tr>
    <tr>
        <td height='45px'></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>_________________</td>
        <td></td>
        <td>_________________</td>
        <td></td>
    </tr>
</table>

<style type="text/css">
    @page {
        margin-top: 1cm;
        margin-left: 0.5cm;
        margin-right: 0.5cm;
        margin-bottom: 1cm;
    }
    .mid{
        vertical-align: middle !important;
    }
    table.gridtable {
        font-family: verdana,arial,sans-serif;
        font-size:11px;
        color:#333333;
        border-width: 1px;
        border-color: #666666;
        border-collapse: collapse;
    }
    table.gridtable th {
        border-width: 1px;
        padding: 3px;
        border-style: solid;
        border-color: #666666;
        background-color: #f2f2f2;
    }
    table.gridtable th.head {
        border-width: 1px;
        padding: 3px;
        border-style: solid;
        border-color: #666666;
        background-color: #7f7f7f;
        color: #ffffff;
    }
    table.gridtable td {
        border-width: 1px;
        padding: 2px;
        border-style: solid;
        border-color: #666666;
        background-color: #ffffff;
    }
    table.gridtable td.cols {
        border-width: 1px;
        padding: 2px;
        border-style: solid;
        border-color: #666666;
        background-color: #ffffff;
    }

    table.gridtable2 {
        font-family: verdana,arial,sans-serif;
        font-size:12;
        color:#333333;
        border-width: 1px;
        border-color: #666666;
        border-collapse: collapse;
    }
    table.gridtable2 th {
        border-width: 1px;
        padding: 3px;
        border-style: none;
        border-color: #666666;
        background-color: #f2f2f2;
    }
    table.gridtable2 th.head {
        border-width: 1px;
        padding: 3px;
        border-style: none;
        border-color: #666666;
        background-color: #7f7f7f;
        color: #ffffff;
    }
    table.gridtable2 td {
        border-width: 1px;
        padding: 3px;
        border-style: none;
        border-color: #666666;
        background-color: #ffffff;
    }
    table.gridtable2 td.cols {
        border-width: 1px;
        padding: 3px;
        border-style: none;
        border-color: #666666;
        background-color: #ffffff;
    }
</style>


<?php
$footer = "<p style='font-family: verdana,arial,sans-serif; font-size:10px;'><i>Printed by : ".ucwords(strtolower($printby)).", ".$today." / Subgudang</i></p>";
$html = ob_get_contents();

ob_end_clean();
// $mpdf->SetWatermarkText('ORI Group');
$mpdf->showWatermarkText = true;
$mpdf->SetTitle($kode_trans); 
$mpdf->AddPageByArray([
    'margin-left' => 4,
    'margin-right' => 4,
    'margin-top' => 10,
    'margin-bottom' => 10,
]);
$mpdf->SetFooter($footer);
$mpdf->WriteHTML($html);
$mpdf->Output('permintaan-material.pdf' ,'I');