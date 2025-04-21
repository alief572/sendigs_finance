<?php

$sroot 		= $_SERVER['DOCUMENT_ROOT'].'origa_live/';
include $sroot."application/libraries/MPDF57/mpdf.php";
$mpdf=new mPDF('utf-8','A4');
$mpdf->defaultheaderline=0;

set_time_limit(0);
ini_set('memory_limit','1024M');
// $HTML_HEADER2 = "<h1>Sample</h1>";

foreach ($getHeader as $keyX => $valueX) {
    $HTML_HEADER = "";
    if($keyX != 0){
        echo "<pagebreak />";
    }

    // $HTML_HEADER .= "<table class='gridtable2' border='0' width='100%' cellpadding='2'>";
    //     $HTML_HEADER .= "<tr>";
    //         $HTML_HEADER .= "<td class='header_style_company' colspan='3' style='padding-left:90px;'>PT. Origa Mulia FRP</td>";
    //         $HTML_HEADER .= "<td class='header_style_company bold' colspan='3'></td>";
    //     $HTML_HEADER .= "</tr>";
    //     $HTML_HEADER .= "<tr>";
    //         $HTML_HEADER .= "<td class='header_style_alamat' colspan='3' style='padding-left:90px;'>Jl. Pembangunan 2 No. 34</td>";
    //         $HTML_HEADER .= "<td class='header_style_alamat' width='15%'></td>";
    //         $HTML_HEADER .= "<td class='header_style_alamat' width='1%'></td>";
    //         $HTML_HEADER .= "<td class='header_style_alamat'></td>";
    //     $HTML_HEADER .= "</tr>";
    //     $HTML_HEADER .= "<tr>";
    //         $HTML_HEADER .= "<td class='header_style_alamat' colspan='3' style='padding-left:90px;'>Kecamatan Batuceper, Kelurahan Batusari</td>";
    //         $HTML_HEADER .= "<td class='header_style_alamat'>Nomor SO</td>";
    //         $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
    //         $HTML_HEADER .= "<td class='header_style_alamat'>".$getData[0]['nomor_so']."</td>";
    //     $HTML_HEADER .= "</tr>";
    //     $HTML_HEADER .= "<tr>";
    //         $HTML_HEADER .= "<td class='header_style_alamat' colspan='3' style='padding-left:90px;'>Kota Tangerang Banten 15122</td>";
    //         $HTML_HEADER .= "<td class='header_style_alamat'>Nama Customer</td>";
    //         $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
    //         $HTML_HEADER .= "<td class='header_style_alamat'>PT ORIGA MULIA FRP</td>";
    //     $HTML_HEADER .= "</tr>";
    //     $HTML_HEADER .= "<tr>";
    //     $HTML_HEADER .= "<td class='header_style_alamat' colspan='3' style='padding-left:90px;'>Indonesia</td>";
    //         $HTML_HEADER .= "<td class='header_style_alamat'>Plan Date</td>";
    //         $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
    //         $HTML_HEADER .= "<td class='header_style_alamat'>".tgl_indo($valueX['tanggal'])."</td>";
    //     $HTML_HEADER .= "</tr>";
    //     $HTML_HEADER .= "<tr>";
    //         $HTML_HEADER .= "<td class='header_style_company2' colspan='3' rowspan='2'>Perintah Kerja</td>";
    //         $HTML_HEADER .= "<td class='header_style_alamat'></td>";
    //         $HTML_HEADER .= "<td class='header_style_alamat'></td>";
    //         $HTML_HEADER .= "<td class='header_style_alamat'></td>";
    //     $HTML_HEADER .= "</tr>";
    //     $HTML_HEADER .= "<tr>";
    //         $HTML_HEADER .= "<td class='header_style_alamat'>Produk Utama</td>";
    //         $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
    //         $HTML_HEADER .= "<td class='header_style_alamat'>".strtoupper($NamaProduct)."</td>";
    //     $HTML_HEADER .= "</tr>";
    //     $HTML_HEADER .= "<tr>";
    //         $HTML_HEADER .= "<td class='header_style_alamat' width='15%'>Nomor</td>";
    //         $HTML_HEADER .= "<td class='header_style_alamat' width='1%'>:</td>";
    //         $HTML_HEADER .= "<td class='header_style_alamat' width='38%'>".$getData[0]['no_spk']."</td>";
    //         $HTML_HEADER .= "<td class='header_style_alamat'>Kuantitas</td>";
    //         $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
    //         $HTML_HEADER .= "<td class='header_style_alamat'>".number_format($valueX['qty'])."</td>";
    //     $HTML_HEADER .= "</tr>";
    //     $HTML_HEADER .= "<tr>";
    //         $HTML_HEADER .= "<td class='header_style_alamat'>Due Date SO</td>";
    //         $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
    //         $HTML_HEADER .= "<td class='header_style_alamat'>".tgl_indo($getData[0]['due_date'])."</td>";
    //         $HTML_HEADER .= "<td class='header_style_alamat'>Satuan</td>";
    //         $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
    //         $HTML_HEADER .= "<td class='header_style_alamat'>Pcs</td>";
    //     $HTML_HEADER .= "</tr>";
    //     $HTML_HEADER .= "<tr>";
    //         $HTML_HEADER .= "<td class='header_style_alamat'></td>";
    //         $HTML_HEADER .= "<td class='header_style_alamat'></td>";
    //         $HTML_HEADER .= "<td class='header_style_alamat'></td>";
    //         $HTML_HEADER .= "<td class='header_style_alamat'>Est. Finish</td>";
    //         $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
    //         $HTML_HEADER .= "<td class='header_style_alamat'>".tgl_indo($valueX['tanggal_est_finish'])."</td>";
    //     $HTML_HEADER .= "</tr>";
    // $HTML_HEADER .= "</table><br>";

    // echo  $HTML_HEADER;
    $QTY_PRODUKSI = (!empty($valueX['qty']))?$valueX['qty']:0;
    ?>

    <table class='gridtable' width='100%' border='1' cellpadding='0' cellspacing='0' style='margin-top:10px;'>
        <thead>
            <tr>
                <th colspan='6'>SPK MIXING</th>
            </tr>
            <tr>
                <th width='20%'>Kode Barang</th>
                <th>Nama Barang</th>
                <th width='12%'>Berat</th>
                <th width='8%'>Satuan</th>
                <th width='8%'>Aktual</th>
                <th width='12%'>Note</th>
            </tr>
        </thead>
        <tbody>
            <?php
                foreach ($getMaterialMixing as $key => $value) {
                    $nama_material = (!empty($GET_DET_Lv4[$value['code_material']]['nama']))?$GET_DET_Lv4[$value['code_material']]['nama']:'';
                    $code_material = (!empty($GET_DET_Lv4[$value['code_material']]['code']))?$GET_DET_Lv4[$value['code_material']]['code']:'';
                    echo "<tr>";
                        echo "<td>".strtoupper($code_material)."</td>";
                        echo "<td>".strtoupper($nama_material)."</td>";
                        echo "<td align='right'>".number_format($value['berat']*$QTY_PRODUKSI,4)."</td>";
                        echo "<td align='center'>Kg</td>";
                        echo "<td align='center'></td>";
                        echo "<td align='center'></td>";
                    echo "</tr>";
                }
            ?>
        </tbody>
    </table>
    <br>
    <br>
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
            echo "<td align='center'>Planner</td>";
            echo "<td align='center'>WH & Logistic</td>";
            echo "<td align='center'>PPIC Head</td>";
        echo "</tr>";
    echo "</tbody>";
    echo "</table><br>";

}
echo "<pagebreak />";
//SPK PRODUKSI
$NoBreak = 0;
foreach ($getHeader as $keyX => $valueX) {
    // $QTY_LOOP = $valueX['qty'];
    // for ($i=1; $i <= $QTY_LOOP; $i++) {  $NoBreak++;
    //     $HTML_HEADER = "";
    //     if($NoBreak != 1){
    //         echo "<pagebreak />";
    //     }
    
        $HTML_HEADER .= "<table class='gridtable2' border='0' width='100%' cellpadding='2'>";
            $HTML_HEADER .= "<tr>";
                $HTML_HEADER .= "<td class='header_style_company' colspan='3' style='padding-left:90px;'>PT. Origa Mulia FRP</td>";
                $HTML_HEADER .= "<td class='header_style_company bold' colspan='3'></td>";
            $HTML_HEADER .= "</tr>";
            $HTML_HEADER .= "<tr>";
                $HTML_HEADER .= "<td class='header_style_alamat' colspan='3' style='padding-left:90px;'>Jl. Pembangunan 2 No. 34</td>";
                $HTML_HEADER .= "<td class='header_style_alamat' width='15%'></td>";
                $HTML_HEADER .= "<td class='header_style_alamat' width='1%'></td>";
                $HTML_HEADER .= "<td class='header_style_alamat'></td>";
            $HTML_HEADER .= "</tr>";
            $HTML_HEADER .= "<tr>";
                $HTML_HEADER .= "<td class='header_style_alamat' colspan='3' style='padding-left:90px;'>Kecamatan Batuceper, Kelurahan Batusari</td>";
                $HTML_HEADER .= "<td class='header_style_alamat'>Nomor SO</td>";
                $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
                $HTML_HEADER .= "<td class='header_style_alamat'>".$getData[0]['nomor_so']."</td>";
            $HTML_HEADER .= "</tr>";
            $HTML_HEADER .= "<tr>";
                $HTML_HEADER .= "<td class='header_style_alamat' colspan='3' style='padding-left:90px;'>Kota Tangerang Banten 15122</td>";
                $HTML_HEADER .= "<td class='header_style_alamat'>Nama Customer</td>";
                $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
                $HTML_HEADER .= "<td class='header_style_alamat'>PT ORIGA MULIA FRP</td>";
            $HTML_HEADER .= "</tr>";
            $HTML_HEADER .= "<tr>";
            $HTML_HEADER .= "<td class='header_style_alamat' colspan='3' style='padding-left:90px;'>Indonesia</td>";
                $HTML_HEADER .= "<td class='header_style_alamat'>Plan Date</td>";
                $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
                $HTML_HEADER .= "<td class='header_style_alamat'>".tgl_indo($valueX['tanggal'])."</td>";
            $HTML_HEADER .= "</tr>";
            $HTML_HEADER .= "<tr>";
                $HTML_HEADER .= "<td class='header_style_company2' colspan='3' rowspan='2'>Perintah Kerja</td>";
                $HTML_HEADER .= "<td class='header_style_alamat'></td>";
                $HTML_HEADER .= "<td class='header_style_alamat'></td>";
                $HTML_HEADER .= "<td class='header_style_alamat'></td>";
            $HTML_HEADER .= "</tr>";
            $HTML_HEADER .= "<tr>";
                $HTML_HEADER .= "<td class='header_style_alamat'>Produk Utama</td>";
                $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
                $HTML_HEADER .= "<td class='header_style_alamat'>".strtoupper($getData[0]['nama_product'])."</td>";
            $HTML_HEADER .= "</tr>";
            $HTML_HEADER .= "<tr>";
                $HTML_HEADER .= "<td class='header_style_alamat' width='15%'>Nomor</td>";
                $HTML_HEADER .= "<td class='header_style_alamat' width='1%'>:</td>";
                $HTML_HEADER .= "<td class='header_style_alamat' width='38%'>".$getData[0]['no_spk']."</td>";
                $HTML_HEADER .= "<td class='header_style_alamat'>Kuantitas</td>";
                $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
                $HTML_HEADER .= "<td class='header_style_alamat'>".number_format($valueX['qty'])."</td>";
            $HTML_HEADER .= "</tr>";
            $HTML_HEADER .= "<tr>";
                $HTML_HEADER .= "<td class='header_style_alamat'>Due Date SO</td>";
                $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
                $HTML_HEADER .= "<td class='header_style_alamat'>".tgl_indo($getData[0]['due_date'])."</td>";
                $HTML_HEADER .= "<td class='header_style_alamat'>Satuan</td>";
                $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
                $HTML_HEADER .= "<td class='header_style_alamat'>Pcs</td>";
            $HTML_HEADER .= "</tr>";
            $HTML_HEADER .= "<tr>";
                $HTML_HEADER .= "<td class='header_style_alamat'></td>";
                $HTML_HEADER .= "<td class='header_style_alamat'></td>";
                $HTML_HEADER .= "<td class='header_style_alamat'></td>";
                $HTML_HEADER .= "<td class='header_style_alamat'>Est. Finish</td>";
                $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
                $HTML_HEADER .= "<td class='header_style_alamat'>".tgl_indo($valueX['tanggal_est_finish'])."</td>";
            $HTML_HEADER .= "</tr>";
        $HTML_HEADER .= "</table><br>";

        // echo  $HTML_HEADER;
        $QTY_PRODUKSI = (!empty($valueX['qty']))?$valueX['qty']:0;
        ?>

        <table class='gridtable' width='100%' border='1' cellpadding='0' cellspacing='0' style='margin-top:10px;'>
            <thead>
                <tr>
                    <th colspan='6'>SPK PRODUKSI</th>
                </tr>
                <tr>
                    <th width='20%'>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th width='12%'>Berat</th>
                    <th width='8%'>Satuan</th>
                    <th width='8%'>Aktual</th>
                    <th width='12%'>Note</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach ($getMaterialProduksi as $key => $value) {
                        $nama_material = (!empty($GET_DET_Lv4[$value['code_material']]['nama']))?$GET_DET_Lv4[$value['code_material']]['nama']:'';
                        $code_material = (!empty($GET_DET_Lv4[$value['code_material']]['code']))?$GET_DET_Lv4[$value['code_material']]['code']:'';
                        echo "<tr>";
                            echo "<td>".$code_material."</td>";
                            echo "<td>".$nama_material."</td>";
                            echo "<td align='right'>".number_format($value['berat'],4)."</td>";
                            echo "<td align='center'>Kg</td>";
                            echo "<td align='center'></td>";
                            echo "<td align='center'></td>";
                        echo "</tr>";
                    }
                ?>
            </tbody>
        </table>
        <br>
        <br>
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
                echo "<td align='center'>Planner</td>";
                echo "<td align='center'>WH & Logistic</td>";
                echo "<td align='center'>PPIC Head</td>";
            echo "</tr>";
        echo "</tbody>";
        echo "</table><br>";
    // }

}
?>

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
$mpdf->SetTitle($kode);
	$mpdf->AddPageByArray([
		'margin-left' => 5,
		'margin-right' => 5,
		'margin-top' => 60,
		'margin-bottom' => 5,
		'default-header-line' => 5,
	]);
// $mpdf->SetFooter($footer);
$mpdf->WriteHTML($html);
$mpdf->Output("spk-material.pdf" ,'I');
