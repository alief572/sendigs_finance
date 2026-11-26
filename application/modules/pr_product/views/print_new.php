<?php

$sroot         = $_SERVER['DOCUMENT_ROOT'] . 'origa_live/';
include $sroot . "application/libraries/MPDF57/mpdf.php";
$mpdf = new mPDF('utf-8', 'A4');
$mpdf->defaultheaderline = 0;

if ($getData[0]['tingkat_pr'] == '2') {
    $tingkat_pr = 'Urgent';
} else {
    $tingkat_pr = 'Normal';
}

set_time_limit(0);
ini_set('memory_limit', '1024M');
// $HTML_HEADER2 = "<h1>Sample</h1>";
$QTY_PRODUKSI = (!empty($getData[0]['qty_produksi'])) ? $getData[0]['qty_produksi'] : 0;


$HTML_HEADER = "";
$HTML_HEADER .= "<table class='gridtable2' border='0' width='100%' cellpadding='0' style='margin-left:20px;'>";
$HTML_HEADER .= "<tr>";
$HTML_HEADER .= "<td rowspan='2' style='text-align:left; vertical-align:top;'><img src='" . $sroot . "/assets/images/ori_logo2.png' style='float:left;' height='120' width='90'></td>";
$HTML_HEADER .= "<td width='35%'></td>";
$HTML_HEADER .= "</tr>";
$HTML_HEADER .= "<tr>";
$HTML_HEADER .= "<td class='header_style_alamat' style='vertical-align:bottom; font-size:12px;'>Jl. Pembangunan 2 No. 34<br>Kecamatan Batuceper, Kelurahan Batusari<br>Kota Tangerang Banten 15122<br>Indonesia</td>";
$HTML_HEADER .= "</tr>";
$HTML_HEADER .= "</table><hr>";


$HTML_HEADER .= "<table class='gridtable2' border='0' width='100%' cellpadding='2' style='margin-left:20px;margin-right:20px;'>";
$HTML_HEADER .= "<tr>";
$HTML_HEADER .= "<td class='header_style_alamat' width='10%'></td>";
$HTML_HEADER .= "<td class='header_style_alamat' width='1%'></td>";
$HTML_HEADER .= "<td class='header_style_alamat' ></td>";
$HTML_HEADER .= "<td class='header_style_alamat' width='10%'></td>";
$HTML_HEADER .= "<td class='header_style_alamat' width='1%'></td>";
$HTML_HEADER .= "<td class='header_style_alamat' width='25%'></td>";
$HTML_HEADER .= "</tr>";
$HTML_HEADER .= "<tr>";
$HTML_HEADER .= "<td class='header_style_company2' colspan='6' align='center' style='height:50px;'>Purchase Request</td>";
$HTML_HEADER .= "</tr>";
$HTML_HEADER .= "<tr>";
$HTML_HEADER .= "<td class='header_style_alamat'>Customer</td>";
$HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
$HTML_HEADER .= "<td class='header_style_alamat'>" . $getCustomer[0]['nm_customer'] . "</td>";
$HTML_HEADER .= "<td class='header_style_alamat'></td>";
$HTML_HEADER .= "<td class='header_style_alamat'></td>";
$HTML_HEADER .= "<td class='header_style_alamat'></td>";
$HTML_HEADER .= "</tr>";
$HTML_HEADER .= "<tr>";
$HTML_HEADER .= "<td class='header_style_alamat'>Address</td>";
$HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
$HTML_HEADER .= "<td class='header_style_alamat'>" . $getCustomer[0]['alamat'] . "</td>";
$HTML_HEADER .= "<td class='header_style_alamat'>No PR</td>";
$HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
$HTML_HEADER .= "<td class='header_style_alamat'>" . $getData[0]['no_pr'] . "</td>";
$HTML_HEADER .= "</tr>";
$country = get_name('country_all', 'nicename', 'iso3', $getCustomer[0]['country_code']);
$HTML_HEADER .= "<tr>";
$HTML_HEADER .= "<td class='header_style_alamat'>Country</td>";
$HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
$HTML_HEADER .= "<td class='header_style_alamat'>" . $country . "</td>";
$HTML_HEADER .= "<td class='header_style_alamat'>Date</td>";
$HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
$HTML_HEADER .= "<td class='header_style_alamat'>" . date('d F Y', strtotime($getData[0]['tgl_so'])) . "</td>";
$HTML_HEADER .= "</tr>";
$pic = get_name('customer_pic', 'nm_pic', 'id_pic', $getCustomer[0]['id_pic']);
$HTML_HEADER .= "<tr>";
$HTML_HEADER .= "<td class='header_style_alamat'>PIC</td>";
$HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
$HTML_HEADER .= "<td class='header_style_alamat'>" . $pic . "</td>";
$HTML_HEADER .= "<td class='header_style_alamat'>Rev.</td>";
$HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
$HTML_HEADER .= "<td class='header_style_alamat'>" . $getData[0]['no_rev'] . "</td>";
$HTML_HEADER .= "</tr>";
$HTML_HEADER .= "<tr>";
$HTML_HEADER .= "<td class='header_style_alamat'>Phone</td>";
$HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
$HTML_HEADER .= "<td class='header_style_alamat'>" . $getCustomer[0]['telpon'] . "</td>";
$HTML_HEADER .= "<td class='header_style_alamat'>Page</td>";
$HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
$HTML_HEADER .= "<td class='header_style_alamat'></td>";
$HTML_HEADER .= "</tr>";
$HTML_HEADER .= "<tr>";
$HTML_HEADER .= "<td class='header_style_alamat'>Fax</td>";
$HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
$HTML_HEADER .= "<td class='header_style_alamat'>" . $getCustomer[0]['fax'] . "</td>";
$HTML_HEADER .= "<td class='header_style_alamat'>Ref PI</td>";
$HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
$HTML_HEADER .= "<td class='header_style_alamat'></td>";
$HTML_HEADER .= "</tr>";
$HTML_HEADER .= "<tr>";
$HTML_HEADER .= "<td class='header_style_alamat'></td>";
$HTML_HEADER .= "<td class='header_style_alamat'></td>";
$HTML_HEADER .= "<td class='header_style_alamat'></td>";
$HTML_HEADER .= "<td class='header_style_alamat'>Needed Date</td>";
$HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
$HTML_HEADER .= "<td class='header_style_alamat'>" . date('d F Y', strtotime($getData[0]['tgl_dibutuhkan'])) . "</td>";
$HTML_HEADER .= "</tr>";
$HTML_HEADER .= "<tr>";
$HTML_HEADER .= "<td class='header_style_alamat'></td>";
$HTML_HEADER .= "<td class='header_style_alamat'></td>";
$HTML_HEADER .= "<td class='header_style_alamat'></td>";
$HTML_HEADER .= "<td class='header_style_alamat'>Tingkat PR</td>";
$HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
$HTML_HEADER .= "<td class='header_style_alamat'>" . $tingkat_pr . "</td>";
$HTML_HEADER .= "</tr>";
$HTML_HEADER .= "</table>";

?>
<br><br>
<table class='gridtable' width='100%' border='1' cellpadding='0' cellspacing='0' style='padding-top: 25px;margin-left:20px;margin-right:20px;'>
    <tr>
        <th width='5%' align='center'>#</th>
        <th align='center'>Code</th>
        <th align='center'>Material Name</th>
        <th width='15%' align='center'>Qty</th>
        <th width='15%' align='center'>Note</th>
    </tr>
    <?php
    foreach ($getDataDetail as $key => $value) {
        $key++;
        $tandaMat = substr($value['id_material'], 0, 1);
        if ($tandaMat == 'M') {
            $nm_product_code    = (!empty($GET_DET_Lv4[$value['id_material']]['code'])) ? $GET_DET_Lv4[$value['id_material']]['code'] : '';
            $nm_product         = (!empty($GET_DET_Lv4[$value['id_material']]['nama'])) ? $GET_DET_Lv4[$value['id_material']]['nama'] : '';
        } else {
            $nm_product_code    = (!empty($GET_ACCESSORIES[$value['id_material']]['code'])) ? $GET_ACCESSORIES[$value['id_material']]['code'] : '';
            $nm_product         = (!empty($GET_ACCESSORIES[$value['id_material']]['nama'])) ? $GET_ACCESSORIES[$value['id_material']]['nama'] : '';
        }
        echo "<tr>";
        echo "<td align='center'>" . $key . " </td>";
        echo "<td>" . $nm_product_code . "</td>";
        echo "<td>" . $nm_product . "</td>";
        echo "<td align='right'>" . number_format($value['propose_purchase'], 2) . "</td>";
        echo "<td align='left'>" . $value['note'] . "</td>";
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
echo "<td align='center'>____________________</td>";
echo "<td align='center'>____________________</td>";
echo "<td align='center'>____________________</td>";
echo "</tr>";
echo "</tbody>";
echo "</table>";

?>

<style type="text/css">
    .text-bold {
        font-weight: bold;
    }

    .bold {
        font-weight: bold;
    }

    .header_style_company {
        padding: 15px;
        color: black;
        font-size: 20px;
    }

    .header_style_company2 {
        padding-bottom: 20px;
        color: black;
        font-size: 20px;
        /* vertical-align: bottom; */
    }

    .header_style_alamat {
        padding: 10px;
        color: black;
        font-size: 11px;
        vertical-align: top !important;
    }

    p {
        font-family: verdana, arial, sans-serif;
        font-size: 11px;
        padding: 0px;
    }

    table.gridtable {
        font-family: verdana, arial, sans-serif;
        font-size: 10 px;
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
        font-family: verdana, arial, sans-serif;
        font-size: 11px;
        color: #000000;
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
        font-family: verdana, arial, sans-serif;
        font-size: 12px;
        color: #000000;
    }

    table.gridtable4 td {
        padding: 1px;
        border-color: #dddddd;
    }

    table.gridtable4 td.cols {
        padding: 1px;
    }

    table.gridtable5 {
        font-family: verdana, arial, sans-serif;
        font-size: 8px;
        color: #000000;
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
    'margin-top' => 85,
    'margin-bottom' => 15,
    'margin-left' => 0,
    'margin-right' => 0,
    'margin-header' => 0,
    'margin-footer' => 0,
    'line' => 0
]);
// $mpdf->SetFooter($footer);
$mpdf->WriteHTML($html);
$mpdf->Output("spk-material.pdf", 'I');
