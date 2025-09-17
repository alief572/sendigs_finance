<?php

$sroot 		= $_SERVER['DOCUMENT_ROOT'].'origa_live/';
include $sroot."application/libraries/MPDF57/mpdf.php";
$mpdf=new mPDF('utf-8','A4-L');
$mpdf->defaultheaderline=0;

set_time_limit(0);
ini_set('memory_limit','1024M');

$HTML_HEADER = "";
$HTML_HEADER .= "<table class='gridtable2' border='0' width='100%' cellpadding='2'>";
    $HTML_HEADER .= "<tr>";
        $HTML_HEADER .= "<td class='header_style_company' colspan='3' style='padding-left:90px;'>PT. Origa Mulia FRP</td>";
        $HTML_HEADER .= "<td class='header_style_company bold' colspan='3'></td>";
    $HTML_HEADER .= "</tr>";
    $HTML_HEADER .= "<tr>";
        $HTML_HEADER .= "<td class='header_style_alamat' colspan='3' style='padding-left:90px;'>Jl. Pembangunan 2 No. 34</td>";
        $HTML_HEADER .= "<td class='header_style_alamat' width='10%'></td>";
        $HTML_HEADER .= "<td class='header_style_alamat' width='1%'></td>";
        $HTML_HEADER .= "<td class='header_style_alamat'></td>";
    $HTML_HEADER .= "</tr>";
    $HTML_HEADER .= "<tr>";
        $HTML_HEADER .= "<td class='header_style_alamat' colspan='3' style='padding-left:90px;'>Kecamatan Batuceper, Kelurahan Batusari</td>";
        $HTML_HEADER .= "<td class='header_style_alamat'></td>";
        $HTML_HEADER .= "<td class='header_style_alamat'></td>";
        $HTML_HEADER .= "<td class='header_style_alamat'></td>";
    $HTML_HEADER .= "</tr>";
    $HTML_HEADER .= "<tr>";
        $HTML_HEADER .= "<td class='header_style_alamat' colspan='3' style='padding-left:90px;'>Kota Tangerang Banten 15122</td>";
        $HTML_HEADER .= "<td class='header_style_alamat'></td>";
        $HTML_HEADER .= "<td class='header_style_alamat'></td>";
        $HTML_HEADER .= "<td class='header_style_alamat'></td>";
    $HTML_HEADER .= "</tr>";
    $HTML_HEADER .= "<tr>";
    $HTML_HEADER .= "<td class='header_style_alamat' colspan='3' style='padding-left:90px;'>Indonesia</td>";
        $HTML_HEADER .= "<td class='header_style_alamat'></td>";
        $HTML_HEADER .= "<td class='header_style_alamat'></td>";
        $HTML_HEADER .= "<td class='header_style_alamat'></td>";
    $HTML_HEADER .= "</tr>";
    $HTML_HEADER .= "<tr>";
        $HTML_HEADER .= "<td colspan='6' height='10px'></td>";
    $HTML_HEADER .= "</tr>";
    $HTML_HEADER .= "<tr>";
        $HTML_HEADER .= "<td class='header_style_company2' colspan='3' rowspan='3'>Standard Checksheet</td>";
        $HTML_HEADER .= "<td class='header_style_alamat'></td>";
        $HTML_HEADER .= "<td class='header_style_alamat'></td>";
        $HTML_HEADER .= "<td class='header_style_alamat'></td>";
    $HTML_HEADER .= "</tr>";
    $HTML_HEADER .= "<tr>";
        $HTML_HEADER .= "<td class='header_style_alamat'>Nomor SO</td>";
        $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
        $HTML_HEADER .= "<td class='header_style_alamat'></td>";
    $HTML_HEADER .= "</tr>";
    $HTML_HEADER .= "<tr>";
        $HTML_HEADER .= "<td class='header_style_alamat'>Plan Date</td>";
        $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
        $HTML_HEADER .= "<td class='header_style_alamat'></td>";
    $HTML_HEADER .= "</tr>";
    $HTML_HEADER .= "<tr>";
        $HTML_HEADER .= "<td class='header_style_alamat' width='10%'>Nomor SPK</td>";
        $HTML_HEADER .= "<td class='header_style_alamat' width='1%'>:</td>";
        $HTML_HEADER .= "<td class='header_style_alamat' width='30%'></td>";
        $HTML_HEADER .= "<td class='header_style_alamat'>Produk Utama</td>";
        $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
        $HTML_HEADER .= "<td class='header_style_alamat'></td>";
    $HTML_HEADER .= "</tr>";
    $HTML_HEADER .= "<tr>";
        $HTML_HEADER .= "<td class='header_style_alamat'>Mesin</td>";
        $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
        $HTML_HEADER .= "<td class='header_style_alamat'></td>";
        $HTML_HEADER .= "<td class='header_style_alamat'>Qty Produksi</td>";
        $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
        $HTML_HEADER .= "<td class='header_style_alamat'></td>";
    $HTML_HEADER .= "</tr>";
    $HTML_HEADER .= "<tr>";
        $HTML_HEADER .= "<td class='header_style_alamat'>Due Date SO</td>";
        $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
        $HTML_HEADER .= "<td class='header_style_alamat'></td>";
        $HTML_HEADER .= "<td class='header_style_alamat'>Satuan</td>";
        $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
        $HTML_HEADER .= "<td class='header_style_alamat'></td>";
    $HTML_HEADER .= "</tr>";
$HTML_HEADER .= "</table>";

echo $HTML_HEADER;
?>
<table border='0' width='100%'>
    <tr>
        <td width='30%' style='vertical-align:top;'>
        <table class='gridtable' width='100%' border='1' cellpadding='0' cellspacing='0' style='margin-top:10px;'>
            <thead>
                <tr>
                    <th align='left' colspan='2'>Surfacing Veil</th>
                </tr>
                <tr>
                    <th width='50%'>#</th>
                    <th>Atas</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $val = 0;
                if(!empty($listSurface)){
                    foreach($listSurface AS $val => $valx){ 
                        $val++;
                        $idValue = (!empty($GET_VALUE[$valx['id']]['id']))?$GET_VALUE[$valx['id']]['id']:'';
                        $atasValue = (!empty($GET_VALUE[$valx['id']]['surface']))?$GET_VALUE[$valx['id']]['surface']:'';
                        echo "<tr>";
                            echo "<td align='left'>".$valx['nama']."</td>";
                            echo "<td align='left'>".$atasValue."</td>";
                        echo "</tr>";
                    }
                }
                ?>
            </tbody>
        </table>
        </td>
        <td width='40%' style='vertical-align:top;'>
        <table class='gridtable' width='100%' border='1' cellpadding='0' cellspacing='0' style='margin-top:10px;'>
            <thead>
                <tr>
                    <th align='left' colspan='5'>Matt</th>
                </tr>
                <tr>
                    <th>#</th>
                    <th width='15%'>Atas</th>
                    <th width='15%'>Bawah</th>
                    <th width='15%'>Kiri</th>
                    <th width='15%'>Kanan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $val = 0;
                if(!empty($listMatt)){
                    foreach($listMatt AS $val => $valx){ 
                        $val++;
                        $idValue = (!empty($GET_VALUE[$valx['id']]['id']))?$GET_VALUE[$valx['id']]['id']:'';
                        $matt_atas = (!empty($GET_VALUE[$valx['id']]['matt_atas']))?$GET_VALUE[$valx['id']]['matt_atas']:'';
                        $matt_bawah = (!empty($GET_VALUE[$valx['id']]['matt_bawah']))?$GET_VALUE[$valx['id']]['matt_bawah']:'';
                        $matt_kiri = (!empty($GET_VALUE[$valx['id']]['matt_kiri']))?$GET_VALUE[$valx['id']]['matt_kiri']:'';
                        $matt_kanan = (!empty($GET_VALUE[$valx['id']]['matt_kanan']))?$GET_VALUE[$valx['id']]['matt_kanan']:'';

                        echo "<tr>";
                            echo "<td align='left'>".$valx['nama']."</td>";
                            echo "<td align='left'>".$matt_atas."</td>";
                            echo "<td align='left'>".$matt_bawah."</td>";
                            echo "<td align='left'>".$matt_kiri."</td>";
                            echo "<td align='left'>".$matt_kanan."</td>";
                        echo "</tr>";
                    }
                }
                ?>
            </tbody>
        </table>
        </td>
        <td width='30%' style='vertical-align:top;'>
        <table class='gridtable' width='100%' border='1' cellpadding='0' cellspacing='0' style='margin-top:10px;'>
            <thead>
                <tr>
                    <th align='left' colspan='2'>Rooving</th>
                </tr>
                <tr>
                    <th width='50%'>#</th>
                    <th>Pemakaian Aktual</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $val = 0;
                if(!empty($listRooving)){
                    foreach($listRooving AS $val => $valx){ 
                        $val++;
                        $idValue = (!empty($GET_VALUE[$valx['id']]['id']))?$GET_VALUE[$valx['id']]['id']:'';
						$pemakaianValue = (!empty($GET_VALUE[$valx['id']]['rooving']))?$GET_VALUE[$valx['id']]['rooving']:'';
                        
                        echo "<tr>";
                            echo "<td align='left'>".$valx['nama']."</td>";
                            echo "<td align='left'>".$pemakaianValue."</td>";
                        echo "</tr>";
                    }
                }
                ?>
            </tbody>
        </table>
        </td>
    </tr>
    <tr>
        <td rowspan='2' colspan='2'>
        <table class='gridtable' width='100%' border='1' cellpadding='0' cellspacing='0' style='margin-top:10px;'>
            <thead>
                <tr>
                    <th align='left' colspan='8'>Checksheet Suhu dan Speed</th>
                </tr>
                <tr>
                    <th></th>
                    <th colspan='3'>Display Temperature (^Celsius)</th>
                    <th colspan='3'>Dies Temperature (^Celsius)</th>
                    <th>Speed Hidrolik (cm/menit)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $val = 0;
                if(!empty($listSuhuSpeed)){
                    foreach($listSuhuSpeed AS $val => $valx){ 
                        $val++;

                        $idValue = (!empty($GET_VALUE[$valx['id']]['id']))?$GET_VALUE[$valx['id']]['id']:'';
                        $display1 = (!empty($GET_VALUE[$valx['id']]['display1']))?$GET_VALUE[$valx['id']]['display1']:'';
                        $display2 = (!empty($GET_VALUE[$valx['id']]['display2']))?$GET_VALUE[$valx['id']]['display2']:'';
                        $display3 = (!empty($GET_VALUE[$valx['id']]['display3']))?$GET_VALUE[$valx['id']]['display3']:'';
                        $dies1 = (!empty($GET_VALUE[$valx['id']]['dies1']))?$GET_VALUE[$valx['id']]['dies1']:'';
                        $dies2 = (!empty($GET_VALUE[$valx['id']]['dies2']))?$GET_VALUE[$valx['id']]['dies2']:'';
                        $dies3 = (!empty($GET_VALUE[$valx['id']]['dies3']))?$GET_VALUE[$valx['id']]['dies3']:'';
                        $speed = (!empty($GET_VALUE[$valx['id']]['speed']))?$GET_VALUE[$valx['id']]['speed']:'';

                        echo "<tr>";
                            echo "<td align='left'>".$valx['nama']."</td>";
                            echo "<td width='12%' align='left'>".$display1."</td>";
                            echo "<td width='12%' align='left'>".$display2."</td>";
                            echo "<td width='12%' align='left'>".$display3."</td>";
                            echo "<td width='12%' align='left'>".$dies1."</td>";
                            echo "<td width='12%' align='left'>".$dies2."</td>";
                            echo "<td width='12%' align='left'>".$dies3."</td>";
                            echo "<td width='12%' align='left'>".$speed."</td>";
                        echo "</tr>";
                    }
                }
                ?>
            </tbody>
        </table>
        </td>
        <td style='vertical-align:top;'>
        <table class='gridtable' width='100%' border='1' cellpadding='0' cellspacing='0' style='margin-top:10px;'>
            <tbody>
                <tr>
                    <th align='left'>Note :</th>
                </tr>
                <tr>
                    <td height='200px'></td>
                </tr>
            </tbody>
        </table>
        </td>
    </tr>
    <tr>
        <td style='vertical-align:bottom;'>
        <table class='gridtable' width='100%' border='1' cellpadding='0' cellspacing='0' style='margin-top:10px;'>
            <tbody>
                <tr>
                    <td align='center' width='50%'>Dibuat Oleh :</td>
                    <td align='center'>Disetujui Oleh :</td>
                </tr>
                <tr>
                    <td height='100px'></td>
                    <td></td>
                </tr>
                <tr>
                    <td align='center'>Leader Shift / Operator</td>
                    <td align='center'>Supervisor Produksi</td>
                </tr>
            </tbody>
        </table>

        </td>
    </tr>
</table>
<style type="text/css">
    .bold{
        font-weight: bold;
    }
    .header_style_company{
        padding: 15px;
        color: black;
        font-size: 20px;
    }
    .header_style_company2{
        padding-bottom: 20px;
        color: black;
        font-size: 16px;
        /* vertical-align: bottom; */
    }
    .header_style_alamat{
        padding: 10px;
        color: black;
        font-size: 11px;
        vertical-align: top !important;
    }
    p{
        font-family: verdana,arial,sans-serif;
        font-size:10px;
    }
    
    table.gridtable {
        font-family: verdana,arial,sans-serif;
        font-size:8 px;
        border-collapse: collapse;
    }
    table.gridtable th {
        padding: 2px;
    }
    table.gridtable th.head {
        padding: 2px;
    }
    table.gridtable td {
        padding: 2px;
    }
    table.gridtable td.cols {
        padding: 2px;
    }

    table.gridtable2 {
        font-family: verdana,arial,sans-serif;
        font-size:11px;
        color:#000000;
        border-collapse: collapse;
    }
    table.gridtable2 th {
        padding: 1px;
    }
    table.gridtable2 th.head {
        padding: 1px;
    }
    table.gridtable2 td {
        border-width: 1px;
        padding: 1px;
    }
    table.gridtable2 td.cols {
        padding: 1px;
    }
</style>

<?php
$html = ob_get_contents();
ob_end_clean();

$mpdf->SetWatermarkImage(
    $sroot.'/assets/images/ori_logo2.png',
    1,
    [21,30],
    [5, 0]);
$mpdf->showWatermarkImage = true;

// $mpdf->SetHeader($HTML_HEADER);
$mpdf->SetTitle('Checksheet');
	$mpdf->AddPageByArray([
		'margin-left' => 5,
		'margin-right' => 5,
		'margin-top' => 5,
		'margin-bottom' => 5,
		'default-header-line' => 5,
	]);
// $mpdf->SetFooter($footer);
$mpdf->WriteHTML($html);
$mpdf->Output("print-checksheet.pdf" ,'I');
