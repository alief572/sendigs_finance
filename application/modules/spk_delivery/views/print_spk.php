<?php

$sroot 		= $_SERVER['DOCUMENT_ROOT'].'origa_live/';
include $sroot."application/libraries/MPDF57/mpdf.php";
$mpdf=new mPDF('utf-8','A4');
$mpdf->defaultheaderline=0;

set_time_limit(0);
ini_set('memory_limit','1024M');
// $HTML_HEADER2 = "<h1>Sample</h1>";
$QTY_PRODUKSI = (!empty($getData[0]['qty_produksi']))?$getData[0]['qty_produksi']:0;


    $HTML_HEADER = "";
    $HTML_HEADER .= "<table border='0' width='100%' cellpadding='0'>";
        $HTML_HEADER .= "<tr>";
            $HTML_HEADER .= "<td rowspan='3' width='12%' style='text-align:left; vertical-align:top;'><img src='".$sroot."/assets/images/ori_logo2.png' style='float:left;' height='120' width='90'></td>";
            $HTML_HEADER .= "<td style='text-align:left; color:gray; vertical-align:bottom;'><h2><i>Outstanding Reliant Innovative</i></h2></td>";
            $HTML_HEADER .= "<td width='20%'>";
                $HTML_HEADER .= "<img src='".$sroot."/assets/images/ISO.png' style='float:right; padding-top:25px;' height='60' width='120'>";
            $HTML_HEADER .= "</td>";
        $HTML_HEADER .= "</tr>";
        $HTML_HEADER .= "<tr>";
            $HTML_HEADER .= "<td colspan='2' style='vertical-align:bottom;padding:0px;'><hr style='height:3px;margin:0px;'></td>";
        $HTML_HEADER .= "</tr>";
        $HTML_HEADER .= "<tr>";
            $HTML_HEADER .= "<td style='text-align:left; color:gray;' colspan='2'><i>PT. Origa Mulia FRP</i></td>";
        $HTML_HEADER .= "</tr>";
    $HTML_HEADER .= "</table>";

    $HTML_HEADER .= "<table class='gridtable2' border='0' width='100%' cellpadding='2' style='margin-left:20px;margin-right:20px;'>";
        $HTML_HEADER .= "<tr>";
            $HTML_HEADER .= "<td class='header_style_alamat' width='15%'></td>";
            $HTML_HEADER .= "<td class='header_style_alamat' width='1%'></td>";
            $HTML_HEADER .= "<td class='header_style_alamat' width='40%'></td>";
            $HTML_HEADER .= "<td class='header_style_alamat' colspan='3' style='padding-left:90px;'>Jl. Pembangunan 2 No. 34</td>";
        $HTML_HEADER .= "</tr>";
        $HTML_HEADER .= "<tr>";
            $HTML_HEADER .= "<td class='header_style_company2' rowspan='2' colspan='3'>SPK Delivery</td>";
            $HTML_HEADER .= "<td class='header_style_alamat' colspan='3' style='padding-left:90px;'>Kecamatan Batuceper, Kelurahan Batusari</td>";
        $HTML_HEADER .= "</tr>";
        $HTML_HEADER .= "<tr>";
            $HTML_HEADER .= "<td class='header_style_alamat' colspan='3' style='padding-left:90px;'>Kota Tangerang Banten 15122</td>";
        $HTML_HEADER .= "</tr>";
        $HTML_HEADER .= "<tr>";
            $HTML_HEADER .= "<td class='header_style_alamat'>No. Delivery</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>".$getData[0]['no_delivery']."</td>";
            $HTML_HEADER .= "<td class='header_style_alamat' colspan='3' style='padding-left:90px;'>Indonesia</td>";
        $HTML_HEADER .= "</tr>";

        $delivery_date = (!empty($getData[0]['delivery_date']))?date('d-M-Y',strtotime($getData[0]['delivery_date'])):'-';
        $HTML_HEADER .= "<tr>";
            $HTML_HEADER .= "<td class='header_style_alamat'>Delivery Date</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>".$delivery_date."</td>";
        $HTML_HEADER .= "</tr>";
        $HTML_HEADER .= "<tr>";
            $HTML_HEADER .= "<td class='header_style_alamat'>Delivery Address</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>".$getData[0]['delivery_address']."</td>";
        $HTML_HEADER .= "</tr>";
    $HTML_HEADER .= "</table>";

    ?>
    <table class='gridtable' width='100%' border='1' cellpadding='0' cellspacing='0' style='margin-left:20px;margin-right:20px;'>
        <tr>
            <th width='5%' align='center'>#</th>
            <th align='center'>Product Name</th>
            <th width='15%' align='center'>Qty Order</th>
            <th width='15%' align='center'>Qty Delivery</th>
        </tr>
        <?php
            foreach ($getDataDetail as $key => $value) { $key++;
                $nm_product = (!empty($GET_DET_Lv4[$value['code_lv4']]['nama']))?$GET_DET_Lv4[$value['code_lv4']]['nama']:'';
                $nm_product = get_name_product_by_bom($value['no_bom'])[$value['no_bom']];
                echo "<tr>";
                    echo "<td align='center'>".$key." </td>";
                    echo "<td>".$nm_product."</td>";
                    echo "<td align='center'>".number_format($value['qty_so'],2)."</td>";
                    echo "<td align='center'>".number_format($value['qty_delivery'],2)."</td>";
                echo "</tr>";
            }
        ?>
    </table><br><br><br>
    <?php
    echo "<table class='gridtable4' width='100%' border='0' cellpadding='2'>";
    echo "<tbody>";
        echo "<tr>";
            echo "<td width='33%' align='center'>Dibuat Oleh,</td>";
            echo "<td width='34%' align='center'>Diperiksa Oleh</td>";
            echo "<td width='33%' align='center'>Diketahui Oleh,</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td height='70px;'>&nbsp;</td>";
            echo "<td></td>";
            echo "<td></td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td></td>";
            echo "<td></td>";
            echo "<td></td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td align='center'>Delivery</td>";
            echo "<td align='center'>WH & Logistic</td>";
            echo "<td align='center'>PPIC Head</td>";
        echo "</tr>";
    echo "</tbody>";
    echo "</table>";

?>

<style type="text/css">
    .text-bold{
        font-weight: bold;
    }
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
        font-size:11px;
        padding: 0px;
    }
    
    table.gridtable {
        font-family: verdana,arial,sans-serif;
        font-size:10 px;
        border-collapse: collapse;
    }
    table.gridtable th {
        padding: 3px;
    }
    table.gridtable th.head {
        padding: 3px;
    }
    table.gridtable td {
        padding: 3px;
    }
    table.gridtable td.cols {
        padding: 3px;
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
    
    table.gridtable4 {
        font-family: verdana,arial,sans-serif;
        font-size:12px;
        color:#000000;
    }
    table.gridtable4 td {
        padding: 1px;
        border-color: #dddddd;
    }
    table.gridtable4 td.cols {
        padding: 1px;
    }

    table.gridtable5 {
        font-family: verdana,arial,sans-serif;
        font-size:8px;
        color:#000000;
    }
    table.gridtable5 td {
        padding: 1px;
        border-color: #dddddd;
    }
    table.gridtable5 td.cols {
        padding: 1px;
    }
</style>

<?php
$html = ob_get_contents();
// $footer = "<p style='font-family: verdana,arial,sans-serif; font-size:10px;'><i>Printed by : ".ucfirst(strtolower(get_name('users', 'username', 'id_user', $printby))).", ".date('d-M-Y H:i:s')."</i></p>";
// exit;
ob_end_clean();

// $mpdf->SetWatermarkImage(
//     $sroot.'/assets/images/ori_logo2.png',
//     1,
//     [21,30],
//     [5, 0]);
// $mpdf->showWatermarkImage = true;

$mpdf->SetHeader($HTML_HEADER);
$mpdf->SetTitle($kode);
$mpdf->defaultheaderline = 0;
		
$mpdf->AddPageByArray([
    'orientation' => 'P',
    'margin-top' => 70,
    'margin-bottom' => 15,
    'margin-left' => 0,
    'margin-right' => 0,
    'margin-header' => 0,
    'margin-footer' => 0,
    'line' => 0
]);
// $mpdf->SetFooter($footer);
$mpdf->WriteHTML($html);
$mpdf->Output("spk-material.pdf" ,'I');
