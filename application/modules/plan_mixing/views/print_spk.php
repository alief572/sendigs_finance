<?php

$sroot 		= $_SERVER['DOCUMENT_ROOT'].'origa_live/';
include $sroot."application/libraries/MPDF57/mpdf.php";
$mpdf=new mPDF('utf-8','A4-L');
$mpdf->defaultheaderline=0;

set_time_limit(0);
ini_set('memory_limit','1024M');
// $HTML_HEADER2 = "<h1>Sample</h1>";
$QTY_PRODUKSI = (!empty($getData[0]['qty_produksi']))?$getData[0]['qty_produksi']:0;

for ($i=1; $i <= $QTY_PRODUKSI; $i++) { 
    # code...
    if($i != 1){
        echo "<pagebreak />";
    }

    $HTML_HEADER = "";
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
            $HTML_HEADER .= "<td class='header_style_alamat'>".tgl_indo($getData[0]['tanggal'])."</td>";
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
            $HTML_HEADER .= "<td class='header_style_alamat'>".strtoupper($NamaProduct)."</td>";
        $HTML_HEADER .= "</tr>";
        $HTML_HEADER .= "<tr>";
            $HTML_HEADER .= "<td class='header_style_alamat' width='15%'>Nomor</td>";
            $HTML_HEADER .= "<td class='header_style_alamat' width='1%'>:</td>";
            $HTML_HEADER .= "<td class='header_style_alamat' width='38%'>".$getData[0]['no_spk']."</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>Kuantitas</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>".$i."/".number_format($getData[0]['qty_produksi'])."</td>";
        $HTML_HEADER .= "</tr>";
        $HTML_HEADER .= "<tr>";
            $HTML_HEADER .= "<td class='header_style_alamat'>Due Date SO</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>".tgl_indo($getData[0]['due_date'])."</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>Satuan</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
            $HTML_HEADER .= "<td class='header_style_alamat'>Pcs</td>";
        $HTML_HEADER .= "</tr>";
        // $HTML_HEADER .= "<tr>";
        //     $HTML_HEADER .= "<td class='header_style_alamat'>Rencana Produksi</td>";
        //     $HTML_HEADER .= "<td class='header_style_alamat'>:</td>";
        //     $HTML_HEADER .= "<td class='header_style_alamat'></td>";
        //     $HTML_HEADER .= "<td class='header_style_alamat'></td>";
        //     $HTML_HEADER .= "<td class='header_style_alamat'></td>";
        //     $HTML_HEADER .= "<td class='header_style_alamat'></td>";
        // $HTML_HEADER .= "</tr>";
    $HTML_HEADER .= "</table>";

    echo $HTML_HEADER;

    ?>
    <table class='gridtable' width='100%' border='1' cellpadding='0' cellspacing='0' style='margin-top:10px;'>
        <tr>
            <th colspan='11' align='left'>Tabel Acuan untuk penggunaan Material Mixing per Produk</th>
        </tr>
        <tr>
            <th width='3%' align='center' rowspan='2'>#</th>
            <th align='center' rowspan='2'>Material Name</th>
            <th width='7%' align='center' rowspan='2'>Weight (kg)</th>
            <th width='7%' align='center' rowspan='2'>% Komposisi</th>
            <th align='center' colspan='7'>Mixing Option (kg)</th>
        </tr>
        <tr>
            <th width='7%' align='right'><?=number_format($getDataSPK[0]['mix1'],4);?></th>
            <th width='7%' align='right'><?=number_format($getDataSPK[0]['mix2'],4);?></th>
            <th width='7%' align='right'><?=number_format($getDataSPK[0]['mix3'],4);?></th>
            <th width='7%' align='right'><?=number_format($getDataSPK[0]['mix4'],4);?></th>
            <th width='7%' align='right'><?=number_format($getDataSPK[0]['mix5'],4);?></th>
            <th width='7%' align='right'><?=number_format($getDataSPK[0]['mix6'],4);?></th>
            <th width='7%' align='right'><?=number_format($getDataSPK[0]['mix7'],4);?></th>
        </tr>
        <?php
            $SUM_FIRST = 0;
            foreach ($getMaterialMixing as $key => $value) {
                $SUM_FIRST += $value['berat'];
            }

            $SUM = 0;
            $SUM1 = 0;
            $SUM2 = 0;
            $SUM3 = 0;
            $SUM4 = 0;
            $SUM5 = 0;
            $SUM6 = 0;
            $SUM7 = 0;
            $SUM_PERSEN = 0;
            foreach ($getMaterialMixing as $key => $value) { $key++;
                $nama_material = (!empty($GET_DET_Lv4[$value['code_material']]['nama']))?$GET_DET_Lv4[$value['code_material']]['nama']:'';
                $code_material = (!empty($GET_DET_Lv4[$value['code_material']]['code']))?$GET_DET_Lv4[$value['code_material']]['code']:'';
                
                $berat = $value['berat'];
                $persen = $berat / $SUM_FIRST * 100;

                $SUM += $berat;
                $SUM_PERSEN += $persen;

                $mix1 = (!empty($value['mix1']))?$value['mix1']:0;
                $mix2 = (!empty($value['mix2']))?$value['mix2']:0;
                $mix3 = (!empty($value['mix3']))?$value['mix3']:0;
                $mix4 = (!empty($value['mix4']))?$value['mix4']:0;
                $mix5 = (!empty($value['mix5']))?$value['mix5']:0;
                $mix6 = (!empty($value['mix6']))?$value['mix6']:0;
                $mix7 = (!empty($value['mix7']))?$value['mix7']:0;

                $mix1_label = (!empty($value['mix1']))?number_format($value['mix1'],4):'';
                $mix2_label = (!empty($value['mix2']))?number_format($value['mix2'],4):'';
                $mix3_label = (!empty($value['mix3']))?number_format($value['mix3'],4):'';
                $mix4_label = (!empty($value['mix4']))?number_format($value['mix4'],4):'';
                $mix5_label = (!empty($value['mix5']))?number_format($value['mix5'],4):'';
                $mix6_label = (!empty($value['mix6']))?number_format($value['mix6'],4):'';
                $mix7_label = (!empty($value['mix7']))?number_format($value['mix7'],4):'';

                $SUM1 += $mix1;
                $SUM2 += $mix2;
                $SUM3 += $mix3;
                $SUM4 += $mix4;
                $SUM5 += $mix5;
                $SUM6 += $mix6;
                $SUM7 += $mix7;

                echo "<tr>";
                    echo "<td align='center'>".$key." </td>";
                    echo "<td>".$nama_material."</td>";
                    echo "<td align='right'>".number_format($berat,4)."</td>";
                    echo "<td align='right'>".number_format($persen,2)." %</td>";
                    echo "<td align='right'>".$mix1_label."</td>";
                    echo "<td align='right'>".$mix2_label."</td>";
                    echo "<td align='right'>".$mix3_label."</td>";
                    echo "<td align='right'>".$mix4_label."</td>";
                    echo "<td align='right'>".$mix5_label."</td>";
                    echo "<td align='right'>".$mix6_label."</td>";
                    echo "<td align='right'>".$mix7_label."</td>";
                echo "</tr>";
            }

            $SUM1_ = ($SUM1 > 0)?number_format($SUM1,4):'';
            $SUM2_ = ($SUM2 > 0)?number_format($SUM2,4):'';
            $SUM3_ = ($SUM3 > 0)?number_format($SUM3,4):'';
            $SUM4_ = ($SUM4 > 0)?number_format($SUM4,4):'';
            $SUM5_ = ($SUM5 > 0)?number_format($SUM5,4):'';
            $SUM6_ = ($SUM6 > 0)?number_format($SUM6,4):'';
            $SUM7_ = ($SUM7 > 0)?number_format($SUM7,4):'';

            echo "<tr>";
                echo "<td></td>";
                echo "<td><b>TOTAL MATERIAL</b></td>";
                echo "<td align='right'><b>".number_format($SUM,4)."</b></td>";
                echo "<td align='right'><b>".number_format($SUM_PERSEN,2)." %</b></td>";
                echo "<td align='right' class='text-bold'>".$SUM1_."</td>";
                echo "<td align='right' class='text-bold'>".$SUM2_."</td>";
                echo "<td align='right' class='text-bold'>".$SUM3_."</td>";
                echo "<td align='right' class='text-bold'>".$SUM4_."</td>";
                echo "<td align='right' class='text-bold'>".$SUM5_."</td>";
                echo "<td align='right' class='text-bold'>".$SUM6_."</td>";
                echo "<td align='right' class='text-bold'>".$SUM7_."</td>";
            echo "</tr>";
        ?>
    </table>
    <table class='gridtable' width='100%' border='1' cellpadding='0' cellspacing='0' style='margin-bottom:10px;'>
        <tr>
            <th colspan='9' align='left'>Aktual pemakaian material mixing</th>
        </tr>
        <tr>
            <th width='3%' rowspan='2'>#</th>
            <th rowspan='2'>Material Name</th>
            <th width='9%'>Mixing 1</th>
            <th width='9%'>Mixing 2</th>
            <th width='9%'>Mixing 3</th>
            <th width='9%'>Mixing 4</th>
            <th width='9%'>Mixing 5</th>
            <th width='9%'>Mixing 6</th>
            <th width='9%'>Mixing 7</th>
        </tr>
        <tr>
            <th>Mixing Option</th>
            <th>Mixing Option</th>
            <th>Mixing Option</th>
            <th>Mixing Option</th>
            <th>Mixing Option</th>
            <th>Mixing Option</th>
            <th>Mixing Option</th>
        </tr>
        <?php
            foreach ($getMaterialMixing as $key => $value) { $key++;
                $nama_material = (!empty($GET_DET_Lv4[$value['code_material']]['nama']))?$GET_DET_Lv4[$value['code_material']]['nama']:'';
                $code_material = (!empty($GET_DET_Lv4[$value['code_material']]['code']))?$GET_DET_Lv4[$value['code_material']]['code']:'';
                
                echo "<tr>";
                    echo "<td align='center'>".$key." </td>";
                    echo "<td>".$nama_material."</td>";
                    echo "<td align='right'></td>";
                    echo "<td align='right'></td>";
                    echo "<td align='right'></td>";
                    echo "<td align='right'></td>";
                    echo "<td align='right'></td>";
                    echo "<td align='right'></td>";
                    echo "<td align='right'></td>";
                echo "</tr>";
            }
        ?>
    </table>
    <?php
    echo "<table class='gridtable4' width='100%' border='0' cellpadding='2'>";
    echo "<tbody>";
        echo "<tr>";
            echo "<td width='33%' align='center'>Dibuat Oleh,</td>";
            echo "<td width='34%' align='center'>Diperiksa Oleh</td>";
            echo "<td width='33%' align='center'>Diketahui Oleh,</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td height='40px;'>&nbsp;</td>";
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
    echo "</table>";
}
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

$mpdf->SetWatermarkImage(
    $sroot.'/assets/images/ori_logo2.png',
    1,
    [21,30],
    [5, 0]);
$mpdf->showWatermarkImage = true;

// $mpdf->SetHeader($HTML_HEADER);
$mpdf->SetTitle($kode);
	$mpdf->AddPageByArray([
		'margin-left' => 5,
		'margin-right' => 5,
		'margin-top' => 5,
		'margin-bottom' => 1,
		'default-header-line' => 5,
	]);
// $mpdf->SetFooter($footer);
$mpdf->WriteHTML($html);
$mpdf->Output("spk-material.pdf" ,'I');
