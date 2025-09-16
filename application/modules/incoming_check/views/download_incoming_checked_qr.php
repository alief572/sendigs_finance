<?php

$sroot         = $_SERVER['DOCUMENT_ROOT'] . 'origa_live/';
include $sroot . "application/libraries/MPDF57/mpdf.php";
include $sroot . "application/libraries/phpqrcode/qrlib.php";
$mpdf = new mPDF('utf-8', array(42, 23));
$mpdf->defaultheaderline = 0;

set_time_limit(0);
ini_set('memory_limit', '1024M');

// for ($i=0; $i < 20; $i++) { 
# code...
foreach ($detail as $val => $valx) {
    $status_label = 'QC PASSED';
    $inspektor_label = $valx['nm_lengkap'];
    $link     = $valx['kode_trans'] . '/' . $valx['id'] . ', ' . $valx['id_material'] . ', ' . $valx['nm_material'];

    // print_r($link);
    // exit;
    // $images	= "	<figure class='fig-header'>
    //                 <span>
    //                     <img src='https://chart.googleapis.com/chart?chs=150x150&amp;cht=qr&amp;chl=$link&amp;choe=UTF-8' alt='QR code'>
    //                 </span>
    //                 <figcaption  class='fig-caption'>".$valx['daycode']."</figcaption>
    //                 <figcaption    class='fig-caption'>".$valx['qc_pass']."</figcaption>
    //                 <figcaption  class='fig-caption'>".$status_label."</figcaption>
    //             </figure>";
    $images  = "
		<table class='gridtable' style='width:100%;' border='0' cellpadding='0' cellspacing='0'>
			<tr>
				<td width='45%' style='padding-left: -5px;'><img src='https://quickchart.io/qr?text=$link' alt='QR code'></td>
				<td style='vertical-align:top; padding-right: -5px; padding-top: 7px;'>
                    <b>PT ORIGA MULIA FRP</b> <br><br>
                    <b style='font-size: 6px; padding-left: 1.5px;'>No. Lot : " . $valx['lot_description'] . "</b> <br>
                    <b style='font-size: 6px; padding-left: 1.5px;'>Tgl Datang : " . date('d F Y', strtotime($valx['created_date'])) . "</b> <br>
                    <b style='font-size: 6px; padding-left: 1.5px;'>Tgl Exp : " . date('d F Y', strtotime($valx['expired_date'])) . "</b> <br>    
                    ";

    if ($valx['sts'] == '1') {
        $images .= "<b style='font-size: 6px; padding-left: 1.5px;'>QC Passed</b> <br>";
    }

    $images .= "
                    <b style='font-size: 6px; padding-left: 1.5px;'>Tgl Check : " . date('d F Y', strtotime($valx['created_date'])) . "</b> <br>
                    <b style='font-size: 6px; padding-left: 1.5px;'>Checked : " . $valx['nm_lengkap'] . "</b>
                </td>
			</tr>
		</table>
		";
    echo $images;

    // QRcode::png($link);
}
// }

$width = '80px';

?>
<style>
    table.gridtable {
        font-family: verdana, arial, sans-serif;
        font-size: 8 px;
        border-collapse: collapse;
    }

    .qr {
        border: 1px solid black;
    }

    .fig-header {
        border: 2px solid black;
        padding: 5px;
        margin-bottom: 10px;
        /* margin-right: 5px;
		margin-left: 5px; */
        text-align: left;
        width: 60px;
        display: flex !important;
        float: left;
    }

    .fig-caption {
        font-family: verdana, arial, sans-serif;
        font-size: 9px;
    }
</style>
<?php

$html = ob_get_contents();
// exit;
ob_end_clean();
// $mpdf->SetWatermarkText("<span style='font-size:7px; padding-top:2px;'><b>PT ORINDO ERATEC - PT ORIGAMULIA FRP</b></span>");
// $mpdf->showWatermarkText = true;
$mpdf->SetTitle('QR Code');
$mpdf->AddPageByArray([
    'margin-left' => 0,
    'margin-right' => 0,
    'margin-top' => 0,
    'margin-bottom' => 0,
    'default-header-line' => 5,
]);
$mpdf->WriteHTML($html);
$mpdf->Output("qrcode.pdf", 'I');
