<?php

$sroot 		= $_SERVER['DOCUMENT_ROOT'].'origa_live/';
include $sroot."application/libraries/MPDF57/mpdf.php";
$mpdf=new mPDF('utf-8','A4-L');
$mpdf->defaultheaderline=0;

set_time_limit(0);
ini_set('memory_limit','1024M');
// $HTML_HEADER2 = "<h1>Sample</h1>";

foreach ($getHeader as $keyX => $valueX) {
    $HTML_HEADER = "";
    if($keyX != 0){
        echo "<pagebreak />";
    }

    $HTML_HEADER .= "<table class='gridtable2' border='0' width='100%' cellpadding='2'>";
        $HTML_HEADER .= "<tr>";
            $HTML_HEADER .= "<td class='header_style_company' colspan='6' style='padding-left:90px;'>LAPORAN PRODUKSI SHIFT MESIN ".strtoupper(get_name('ms_costcenter','nama_costcenter','id_costcenter',$getHeader[0]['id_costcenter']))."</td>";
        $HTML_HEADER .= "</tr>";
        $HTML_HEADER .= "<tr>";
            $HTML_HEADER .= "<td class='header_style_alamat' colspan='3' style='padding-left:90px;'>&nbsp;</td>";
            $HTML_HEADER .= "<td class='header_style_alamat' width='15%'></td>";
            $HTML_HEADER .= "<td class='header_style_alamat' width='1%'></td>";
            $HTML_HEADER .= "<td class='header_style_alamat'></td>";
        $HTML_HEADER .= "</tr>";
        $HTML_HEADER .= "<tr>";
            $HTML_HEADER .= "<td class='header_style_alamat' colspan='3' style='padding-left:90px;'>&nbsp;</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'></td>";
            $HTML_HEADER .= "<td class='header_style_alamat'></td>";
            $HTML_HEADER .= "<td class='header_style_alamat'></td>";
        $HTML_HEADER .= "</tr>";
        $HTML_HEADER .= "<tr>";
            $HTML_HEADER .= "<td class='header_style_alamat' style='padding-left:90px;'>Hari</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>".date('l',strtotime($valueX['tanggal']))."</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>Shift</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'></td>";
        $HTML_HEADER .= "</tr>";
        $HTML_HEADER .= "<tr>";
            $HTML_HEADER .= "<td class='header_style_alamat' style='padding-left:90px;'>Tanggal</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>".tgl_indo($valueX['tanggal'])."</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>Leader</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'></td>";
        $HTML_HEADER .= "</tr>";
        $HTML_HEADER .= "<tr>";
            $HTML_HEADER .= "<td class='header_style_alamat' colspan='3'>&nbsp;</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'></td>";
            $HTML_HEADER .= "<td class='header_style_alamat'></td>";
            $HTML_HEADER .= "<td class='header_style_alamat'></td>";
        $HTML_HEADER .= "</tr>";
        $HTML_HEADER .= "<tr>";
            $HTML_HEADER .= "<td class='header_style_alamat' width='20%'>Mesin</td>";
            $HTML_HEADER .= "<td class='header_style_alamat' width='1%'>:</td>";
            $HTML_HEADER .= "<td class='header_style_alamat' width='32%'></td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>Mesin</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'></td>";
        $HTML_HEADER .= "</tr>";
        $HTML_HEADER .= "<tr>";
            $HTML_HEADER .= "<td class='header_style_alamat'>Item Product</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>".strtolower($NamaProduct)."</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>Item Product</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>".strtolower($NamaProduct)."</td>";
        $HTML_HEADER .= "</tr>";
        $HTML_HEADER .= "<tr>";
            $HTML_HEADER .= "<td class='header_style_alamat'>SPK No.</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>".$valueX['no_spk']."</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>SPK No.</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>".$valueX['no_spk']."</td>";
        $HTML_HEADER .= "</tr>";
        $HTML_HEADER .= "<tr>";
            $HTML_HEADER .= "<td class='header_style_alamat'>Operator</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'></td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>Operator</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'></td>";
        $HTML_HEADER .= "</tr>";
    $HTML_HEADER .= "</table>";

    $QTY_PRODUKSI = (!empty($valueX['qty']))?$valueX['qty']:0;
    ?>

    <table class='gridtable' width='100%' border='1' cellpadding='0' cellspacing='0' style='margin-top:5px;'>
        <thead>
            <tr>
                <td align='center' class='colorTitle' colspan='2'>Mixing</td>
                <td align='center' class='colorTitle'>Produksi 1</td>
                <td align='center' class='colorTitle'>Produksi 2</td>
                <td align='center' class='colorTitle'>Deskripsi</td>
                <td align='center' class='colorTitle'>Produksi 1</td>
                <td align='center' class='colorTitle'>Produksi 2</td>
            </tr>
        </thead>
        <tbody>
            <?php
                foreach ($getMaterialMixing as $key => $value) {
                    $nama_material = (!empty($GET_DET_Lv4[$value['code_material']]['nama']))?$GET_DET_Lv4[$value['code_material']]['nama']:'';
                    $code_lv2 = (!empty($GET_DET_Lv4[$value['code_material']]['code_lv2']))?$GET_DET_Lv4[$value['code_material']]['code_lv2']:'';
                    $category = (!empty($GET_DET_Lv2[$code_lv2]['nama']))?$GET_DET_Lv2[$code_lv2]['nama']:'';
                    echo "<tr>";
                        echo "<td width='5%'>".ucwords(strtolower($category))."</td>";
                        echo "<td width='15%'>".strtolower($nama_material)."</td>";
                        echo "<td width='18%' align='center'></td>";
                        echo "<td width='18%' align='center'></td>";
                        echo "<td width='8%' align='center'></td>";
                        echo "<td width='18%' align='center'></td>";
                        echo "<td width='18%' align='center'></td>";
                    echo "</tr>";
                }
            ?>
        </tbody>
    </table>
    <table class='gridtable' width='100%' border='1' cellpadding='0' cellspacing='0' style='margin-top:5px;'>
        <thead>
            <tr>
                <td class='colorTitle' align='center' colspan='2'>Resin Casting</td>
                <td class='colorTitle' align='center' colspan='6'>Produksi 1</td>
                <td class='colorTitle' align='center' colspan='6'>Produksi 2</td>
                <td class='colorTitle' align='center' rowspan='2'>Deskripsi</td>
                <td class='colorTitle' align='center' colspan='6'>Produksi 1</td>
                <td class='colorTitle' align='center' colspan='6'>Produksi 2</td>
            </tr>
            <tr>
                <td class='colorTitle' align='center'>Material</td>
                <td class='colorTitle' align='center'>Deskripsi</td>
                <td class='colorTitle' align='center'>Mix<br>I</td>
                <td class='colorTitle' align='center'>Mix<br>II</td>
                <td class='colorTitle' align='center'>Mix<br>III</td>
                <td class='colorTitle' align='center'>Mix<br>IV</td>
                <td class='colorTitle' align='center'>Mix<br>V</td>
                <td class='colorTitle' align='center'>Mix<br>VI</td>
                <td class='colorTitle' align='center'>Mix<br>I</td>
                <td class='colorTitle' align='center'>Mix<br>II</td>
                <td class='colorTitle' align='center'>Mix<br>III</td>
                <td class='colorTitle' align='center'>Mix<br>IV</td>
                <td class='colorTitle' align='center'>Mix<br>V</td>
                <td class='colorTitle' align='center'>Mix<br>VI</td>
                <td class='colorTitle' align='center'>Mix<br>I</td>
                <td class='colorTitle' align='center'>Mix<br>II</td>
                <td class='colorTitle' align='center'>Mix<br>III</td>
                <td class='colorTitle' align='center'>Mix<br>IV</td>
                <td class='colorTitle' align='center'>Mix<br>V</td>
                <td class='colorTitle' align='center'>Mix<br>VI</td>
                <td class='colorTitle' align='center'>Mix<br>I</td>
                <td class='colorTitle' align='center'>Mix<br>II</td>
                <td class='colorTitle' align='center'>Mix<br>III</td>
                <td class='colorTitle' align='center'>Mix<br>IV</td>
                <td class='colorTitle' align='center'>Mix<br>V</td>
                <td class='colorTitle' align='center'>Mix<br>VI</td>
            </tr>
        </thead>
        <tbody>
            <?php
                foreach ($getMaterial as $key => $value) {
                    $nama_material = (!empty($GET_DET_Lv4[$value['code_material']]['nama']))?$GET_DET_Lv4[$value['code_material']]['nama']:'';
                    $code_lv2       = (!empty($GET_DET_Lv4[$value['code_material']]['code_lv2']))?$GET_DET_Lv4[$value['code_material']]['code_lv2']:'';
                    $code_lv1       = (!empty($GET_DET_Lv4[$value['code_material']]['code_lv1']))?$GET_DET_Lv4[$value['code_material']]['code_lv1']:'';
                    $category       = (!empty($GET_DET_Lv2[$code_lv2]['nama']))?$GET_DET_Lv2[$code_lv2]['nama']:'';
                    if($code_lv1 == 'M123000004'){
                        echo "<tr>";
                            echo "<td width='15%'>".strtolower($nama_material)."</td>";
                            echo "<td width='5%'></td>";
                            echo "<td width='3%' align='center'></td>";
                            echo "<td width='3%' align='center'></td>";
                            echo "<td width='3%' align='center'></td>";
                            echo "<td width='3%' align='center'></td>";
                            echo "<td width='3%' align='center'></td>";
                            echo "<td width='3%' align='center'></td>";
                            echo "<td width='3%' align='center'></td>";
                            echo "<td width='3%' align='center'></td>";
                            echo "<td width='3%' align='center'></td>";
                            echo "<td width='3%' align='center'></td>";
                            echo "<td width='3%' align='center'></td>";
                            echo "<td width='3%' align='center'></td>";
                            echo "<td width='8%' align='center'></td>";
                            echo "<td width='3%' align='center'></td>";
                            echo "<td width='3%' align='center'></td>";
                            echo "<td width='3%' align='center'></td>";
                            echo "<td width='3%' align='center'></td>";
                            echo "<td width='3%' align='center'></td>";
                            echo "<td width='3%' align='center'></td>";
                            echo "<td width='3%' align='center'></td>";
                            echo "<td width='3%' align='center'></td>";
                            echo "<td width='3%' align='center'></td>";
                            echo "<td width='3%' align='center'></td>";
                            echo "<td width='3%' align='center'></td>";
                            echo "<td width='3%' align='center'></td>";
                        echo "</tr>";
                    }
                }
            ?>
        </tbody>
    </table>
    <table class='gridtable' width='100%' border='1' cellpadding='0' cellspacing='0' style='margin-top:5px;'>
        <thead>
            <tr>
                <td class='colorTitle' align='center'>Glass</td>
                <td class='colorTitle' align='center'>Deskripsi</td>
                <td class='colorTitle' align='center'>Berat<br>Awal</td>
                <td class='colorTitle' align='center'>Berat<br>Akhir</td>
                <td class='colorTitle' align='center'>Jml<br>Rov</td>
                <td class='colorTitle' align='center'>Jml<br>Ply</td>
                <td class='colorTitle' align='center'>Berat<br>Awal</td>
                <td class='colorTitle' align='center'>Berat<br>Akhir</td>
                <td class='colorTitle' align='center'>Jml<br>Rov</td>
                <td class='colorTitle' align='center'>Jml<br>Ply</td>
                <td class='colorTitle' align='center'>Deskripsi</td>
                <td class='colorTitle' align='center'>Berat<br>Awal</td>
                <td class='colorTitle' align='center'>Berat<br>Akhir</td>
                <td class='colorTitle' align='center'>Jml<br>Rov</td>
                <td class='colorTitle' align='center'>Jml<br>Ply</td>
                <td class='colorTitle' align='center'>Berat<br>Awal</td>
                <td class='colorTitle' align='center'>Berat<br>Akhir</td>
                <td class='colorTitle' align='center'>Jml<br>Rov</td>
                <td class='colorTitle' align='center'>Jml<br>Ply</td>
            </tr>
        </thead>
        <tbody>
            <?php
                foreach ($getMaterial as $key => $value) {
                    $nama_material = (!empty($GET_DET_Lv4[$value['code_material']]['nama']))?$GET_DET_Lv4[$value['code_material']]['nama']:'';
                    $code_lv2       = (!empty($GET_DET_Lv4[$value['code_material']]['code_lv2']))?$GET_DET_Lv4[$value['code_material']]['code_lv2']:'';
                    $code_lv1       = (!empty($GET_DET_Lv4[$value['code_material']]['code_lv1']))?$GET_DET_Lv4[$value['code_material']]['code_lv1']:'';
                    $category       = (!empty($GET_DET_Lv2[$code_lv2]['nama']))?$GET_DET_Lv2[$code_lv2]['nama']:'';
                    if($code_lv1 == 'M123000009'){
                        echo "<tr>";
                            echo "<td width='15%'>".strtolower($nama_material)."</td>";
                            echo "<td width='5%'></td>";
                            echo "<td width='6%' align='center'></td>";
                            echo "<td width='6%' align='center'></td>";
                            echo "<td width='3%' align='center'></td>";
                            echo "<td width='3%' align='center'></td>";
                            echo "<td width='6%' align='center'></td>";
                            echo "<td width='6%' align='center'></td>";
                            echo "<td width='3%' align='center'></td>";
                            echo "<td width='3%' align='center'></td>";
                            echo "<td width='8%' align='center'></td>";
                            echo "<td width='6%' align='center'></td>";
                            echo "<td width='6%' align='center'></td>";
                            echo "<td width='3%' align='center'></td>";
                            echo "<td width='3%' align='center'></td>";
                            echo "<td width='6%' align='center'></td>";
                            echo "<td width='6%' align='center'></td>";
                            echo "<td width='3%' align='center'></td>";
                            echo "<td width='3%' align='center'></td>";
                        echo "</tr>";
                    }
                }
            ?>
        </tbody>
    </table>
    <table class='gridtable' width='100%' border='1' cellpadding='0' cellspacing='0' style='margin-top:5px;'>
        <tbody>
            <?php
            echo "<tr>";
                echo "<td width='12%' rowspan='5' class='colorTitle' align='center'>TIME</td>";
                echo "<td width='8%' align='left'>Heating Time</td>";
                echo "<td width='12%'></td>";
                echo "<td width='6%'>Menit</td>";
                echo "<td width='12%'></td>";
                echo "<td width='6%'>Menit</td>";
                echo "<td width='8%' align='left'>Heating Time</td>";
                echo "<td width='12%'></td>";
                echo "<td width='6%'>Menit</td>";
                echo "<td width='12%'></td>";
                echo "<td width='6%'>Menit</td>";
            echo "</tr>";
            echo "<tr>";
                echo "<td align='left'>Cooling time</td>";
                echo "<td></td>";
                echo "<td>Menit</td>";
                echo "<td></td>";
                echo "<td>Menit</td>";
                echo "<td align='left'>Cooling time</td>";
                echo "<td></td>";
                echo "<td>Menit</td>";
                echo "<td></td>";
                echo "<td>Menit</td>";
            echo "</tr>";
            echo "<tr>";
                echo "<td height='5px' colspan='10'></td>";
            echo "</tr>";
            echo "<tr>";
                echo "<td align='left'>Start Produksi</td>";
                echo "<td></td>";
                echo "<td>WIB</td>";
                echo "<td></td>";
                echo "<td>WIB</td>";
                echo "<td align='left'>Start Produksi</td>";
                echo "<td></td>";
                echo "<td>WIB</td>";
                echo "<td></td>";
                echo "<td>WIB</td>";
            echo "</tr>";
            echo "<tr>";
                echo "<td align='left'>Finish Produksi</td>";
                echo "<td></td>";
                echo "<td>WIB</td>";
                echo "<td></td>";
                echo "<td>WIB</td>";
                echo "<td align='left'>Finish Produksi</td>";
                echo "<td></td>";
                echo "<td>WIB</td>";
                echo "<td></td>";
                echo "<td>WIB</td>";
            echo "</tr>";
            // echo "<tr>";
            //     echo "<td height='10px' colspan='10'></td>";
            // echo "</tr>";
            ?>
        </tbody>
    </table>
    <table class='gridtable' width='100%' border='1' cellpadding='0' cellspacing='0' style='margin-top:5px;'>
        <tbody>
            <?php
            echo "<tr>";
                echo "<td class='colorTitle' align='center' width='20%'>Material Sisa</td>";
                echo "<td class='colorTitle' align='center' width='18%'>Jumlah Kg</td>";
                echo "<td class='colorTitle' align='center' width='18%'>Deskripsi</td>";
                echo "<td class='colorTitle' align='center' width='8%'>Material Sisa</td>";
                echo "<td class='colorTitle' align='center' width='18%'>Jumlah Kg</td>";
                echo "<td class='colorTitle' align='center' width='18%'>Deskripsi</td>";
            echo "</tr>";
            echo "<tr>";
                echo "<td>&nbsp;</td>";
                echo "<td></td>";
                echo "<td></td>";
                echo "<td></td>";
                echo "<td></td>";
                echo "<td></td>";
            echo "</tr>";
            echo "<tr>";
                echo "<td>&nbsp;</td>";
                echo "<td></td>";
                echo "<td></td>";
                echo "<td></td>";
                echo "<td></td>";
                echo "<td></td>";
            echo "</tr>";
            echo "<tr>";
                echo "<td>&nbsp;</td>";
                echo "<td></td>";
                echo "<td></td>";
                echo "<td></td>";
                echo "<td></td>";
                echo "<td></td>";
            echo "</tr>";
            ?>
        </tbody>
    </table>
    <table class='gridtable' width='100%' border='1' cellpadding='0' cellspacing='0' style='margin-top:5px;'>
        <tbody>
            <?php
            echo "<tr>";
                echo "<td class='colorTitle' align='center' width='20%' rowspan='5'>REMARK</td>";
                echo "<td width='36%'>&nbsp;</td>";
                echo "<td class='colorTitle' align='center' width='8%' rowspan='5'>REMARK</td>";
                echo "<td width='36%'></td>";
            echo "</tr>";
            echo "<tr>";
                echo "<td>&nbsp;</td>";
                echo "<td></td>";
            echo "</tr>";
            echo "<tr>";
                echo "<td>&nbsp;</td>";
                echo "<td></td>";
            echo "</tr>";
            echo "<tr>";
                echo "<td>&nbsp;</td>";
                echo "<td></td>";
            echo "</tr>";
            echo "<tr>";
                echo "<td>&nbsp;</td>";
                echo "<td></td>";
            echo "</tr>";
            ?>
        </tbody>
    </table>
    <?php
    echo "<table class='gridtable' width='30%' border='1' cellpadding='2' style='margin-top:5px;'>";
        echo "<tbody>";
            echo "<tr>";
                echo "<td width='50%' align='center'>Dibuat Oleh,</td>";
                echo "<td width='50%' align='center'>Disetujui Oleh</td>";
            echo "</tr>";
            echo "<tr>";
                echo "<td height='70px;'>&nbsp;</td>";
                echo "<td></td>";
            echo "</tr>";
            echo "<tr>";
                echo "<td align='center'>Leader Shift /Operator</td>";
                echo "<td align='center'>Supervisor Produksi</td>";
            echo "</tr>";
        echo "</tbody>";
    echo "</table>";

}
?>

<style type="text/css">
    .colorTitle{
        background-color: #faf1be;
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
        font-size:10px;
    }
    
    table.gridtable {
        font-family: verdana,arial,sans-serif;
        font-size:11 px;
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

$mpdf->SetWatermarkImage(
    $sroot.'/assets/images/ori_logo2.png',
    1,
    [21,30],
    [5, 0]);
$mpdf->showWatermarkImage = true;

$mpdf->SetHeader($HTML_HEADER);
$mpdf->SetTitle($getHeader[0]['kode_det']);
	$mpdf->AddPageByArray([
		'margin-left' => 5,
		'margin-right' => 2,
		'margin-top' => 58,
		'margin-bottom' => 2,
		'default-header-line' => 5,
	]);
// $mpdf->SetFooter($footer);
$mpdf->WriteHTML($html);
$mpdf->Output("spk-material.pdf" ,'I');
