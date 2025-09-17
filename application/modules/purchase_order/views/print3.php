<?php
// Require composer autoload
$sroot = $_SERVER['DOCUMENT_ROOT'];
include $sroot."/metalsindo/application/libraries/MPDF57/mpdf.php";

$mpdf=new mPDF('utf-8','A4-L');

set_time_limit(0);
ini_set('memory_limit','1024M');
ob_start();

	foreach($header as $header){
	}
	$detailsum = $this->db->query("SELECT SUM(width) as sumwidth, SUM(qty) as sumqty, SUM(totalwidth) as sumtotalwidth, SUM(jumlahharga) as sumjumlahharga, SUM(hargasatuan) as sumhargasatuan FROM dt_trans_po WHERE no_po = '".$header->no_po."' ")->result();
	$jumlahdetail = $this->db->query("SELECT COUNT(no_po) as no_po FROM dt_trans_po WHERE no_po = '".$header->no_po."' ")->result();
	$jumlahdata = $jumlahdetail[0]->no_po;
	$tinggi = 300/$jumlahdata ;
	if(empty($header->negara)){
		$cou ='Indonesia';
		}else{
		$findnegara = $this->db->query("SELECT * FROM negara WHERE id_negara = '".$header->negara."' ")->result();
		$cou = $findnegara[0]->nm_negara;
		}
	$findpic = $this->db->query("SELECT * FROM child_supplier_pic WHERE id_suplier = '".$header->id_suplier."' ")->result();
	$namapic = $findpic[0]->name_pic;
?>

<table border="0" width='100%'>
    <tr>
        <td align="left">
            <h5 style="text-align: left;">PT METALSINDO PACIFIC</h5>
        </td>
    </tr>
</table>
<div style='display:block; border-color:none; background-color:#c2c2c2;' align='center'>
    <h3>PURCHASE ORDER</h3>
</div>
<table class='gridtableX' width='100%' cellpadding='2' border='0'>
    <tbody>
        <tr>
            <td style='width: 50%;'>
                <p>
                    Address<br>
                    Jl. Jababeka XIV, Blok J no. 10 H<br>
                    Cikarang Industrial Estate, Bekasi 17530<br>
                    Phone : (62-21) 89831726734<br>
                    Fax : (62-21) 89831866<br>
                </p>
            </td>
            <td style='width: 50%; text-align:right; vertical-align:top;'>
                <p>
                    PO No : <?= $header->no_surat ?>
                </p>
            </td>
        </tr>
    </tbody>
</table>
<br>
<table class='gridtable2' width='100%' border='1' align='center' cellpadding='0' cellspacing='0'>
    <tr>
        <td width="380" align="center">
            <table width='380' align="center">
                <tr><td width='70' align="left">Supplier</td><td width='10' align="left">:</td><td width='300' align="left"><?= $header->name_suplier ?></td></tr>
                <tr><td width='70' align="left">Address</td><td width='10' align="left">:</td><td width='300' align="left"><?= $header->address_office ?></td></tr>
                <tr><td width='70' align="left">Country</td><td width='10' align="left">:</td><td width='300' align="left"><?= $cou ?></td></tr>
                <tr><td width='70' align="left">PIC</td><td width='10' align="left">:</td><td width='300' align="left"><?= $namapic ?></td></tr>
                <tr><td width='70' align="left">Phone</td><td width='10' align="left">:</td><td width='300' align="left"><?= $header->telephone ?></td></tr>
                <tr><td width='70' align="left">Fax</td><td width='10' align="left">:</td><td width='300' align="left"><?if(empty($header->fax)){echo"-";}else{echo"$header->fax";}  ?></td></tr>
            </table>
        </td>
    </tr>
</table>
<br>

    <table class='gridtable'  cellpadding='0' cellspacing='0' width='100%' style='width:100% !important; vertical-align:top;'>
        <tbody>
            <tr style='vertical-align:middle; background-color:#c2c2c2; font-weight:bold;'>
                <td align="center">Material</td>
                <td align="center">Width</td>
                <td align="center">Total Weight</td>
                <td align="center">Unit Price</td>
                <td align="center">Amount</td>
                <td align="center">Remarks</td>
            </tr>
			<?php	
			$CIF = "<br>".$header->cif."<br><br><br><br>";
			foreach($detail as $detail){
                if($jumlahdata <= '30'){
                    echo"	
                    <tr >
                        <td>".$detail->namamaterial."</td>
                        <td align='right'>".$detail->width."</td>
                        <td align='right'>".number_format($detail->totalwidth)."</td>
                        <td align='right'>".number_format($detail->hargasatuan,3)."</td>
                        <td align='right'>".number_format($detail->jumlahharga).$CIF ."</td>
                        <td>".$detail->description."</td>
                    </tr>";
                    $CIF = "";
                }
                else{
                    echo"	
                    <tr >
                        <td>".$detail->namamaterial."</td>
                        <td align='right'>".number_format($detail->width)."</td>
                        <td align='right'>".number_format($detail->totalwidth)."</td>
                        <td align='right'>".number_format($detail->hargasatuan,3)."</td>
                        <td align='right'>".number_format($detail->jumlahharga).$CIF ."</td>
                        <td>".$detail->description."</td>
                    </tr>";
                    $CIF = "";
                }
			} ?>
			<tr>
                <td align="center">Total </td>
                <td align="right"><?= number_format($detailsum[0]->sumwidth) ?></td>
                <td align="right"><?= number_format($detailsum[0]->sumtotalwidth) ?></td>
                <td align="right"><?= number_format($detailsum[0]->sumhargasatuan,3) ?></td>
                <td align="right"><?= number_format($detailsum[0]->sumjumlahharga) ?></td>
                <td align="center"></td>
			</tr>
			<tr>
                <td colspan='2' align="center">Issued Date</td>
                <td colspan='3' align="center">
                    <?php 
                        if($header->cif == "Destination"){
                            echo"Delivery To :";	
                        }
                        else{
                            echo"Delivery To";	
                        }; 
                    ?>
                </td>
                <td colspan=2' align="center">Eta Date</td>
			</tr>
			<tr>
                <td colspan='2' align="center"><?= date('d-M-Y', strtotime($header->tanggal)) ?></td>
                <td colspan='3' align="center">PT Metalsindo Pacific<br>Cikarang, Indonesia</td>
                <td colspan='2' rowspan='2' align="center"><?= date('d-M-Y', strtotime($header->expect_tanggal)) ?></td>
			</tr>
			<tr>
                <td colspan='2' align="center">Payment Term</td>
                <td colspan='3' align="center">Transfer End Next <?= $header->term ?> Day After BL Date</td>
			</tr>
        </tbody>
	</table>
	<br>

    <table class='gridtableX2' width='100%' cellpadding='0' cellspacing='0' border='0' align='left'>
        <tr>
            <td align='center'>Note : </td><td width='50'><?= $header->note ?></td>
        </tr>
	</table>
    <br>
    <table class='gridtableX2' width='100%' cellpadding='0' cellspacing='0' border='0' align='right'>
        <tr>
            <td align='center'>Approved</td><td width='50'></td>
        </tr>
        <tr>
            <td height='50' align='center'></td><td></td>
        </tr>
        <tr>
            <td align='center'><u>HARRY WIDJAJA</u></td><td></td>
        </tr>
        <tr>
            <td align='center'>President Director</td><td></td>
        </tr>
	</table>
<style>
	.header_style_company{
        padding: 15px;
        color: black;
        font-size: 20px;
        vertical-align:bottom;
    }
    .header_style_company2{
        padding: 15px;
        color: black;
        font-size: 15px;
        vertical-align:top;
    }

    .header_style_alamat{
        padding: 10px;
        color: black;
        font-size: 10px;
    }

    table.default {
        font-family: arial,sans-serif;
        font-size:9px;
        padding: 0px;
    }

    p{
        font-family: arial,sans-serif;
        font-size:14px;
    }
    
    .font{
        font-family: arial,sans-serif;
        font-size:14px;
    }

    table.gridtable {
        font-family: arial,sans-serif;
        font-size:12px;
        color:#333333;
        border: 1px solid #808080;
        border-collapse: collapse;
    }
    table.gridtable th {
        padding: 6px;
        background-color: #f7f7f7;
        color: black;
        border-color: #808080;
        border-style: solid;
        border-width: 1px;
    }
    table.gridtable th.head {
        padding: 6px; 
        background-color: #f7f7f7;
        color: black;
        border-color: #808080;
        border-style: solid;
        border-width: 1px;
    }
    table.gridtable td {
        border-width: 1px;
        padding: 6px;
        border-style: solid;
        border-color: #808080;
    }
    table.gridtable td.cols {
        border-width: 1px;
        padding: 6px;
        border-style: solid;
        border-color: #808080;
    }


    table.gridtable2 {
        font-family: arial,sans-serif;
        font-size:13px;
        color:#333333;
        border-width: 1px;
        border-color: #666666;
        border-collapse: collapse;
    }
    table.gridtable2 td {
        border-width: 1px;
        padding: 1px;
        border-style: none;
        border-color: #666666;
        background-color: #ffffff;
    }
    table.gridtable2 td.cols {
        border-width: 1px;
        padding: 1px;
        border-style: none;
        border-color: #666666;
        background-color: #ffffff;
    }

    table.gridtableX {
        font-family: arial,sans-serif;
        font-size:12px;
        color:#333333;
        border: none;
        border-collapse: collapse;
    }
    table.gridtableX td {
        border-width: 1px;
        padding: 6px;
    }
    table.gridtableX td.cols {
        border-width: 1px;
        padding: 6px;
    }

    table.gridtableX2 {
        font-family: arial,sans-serif;
        font-size:12px;
        color:#333333;
        border: none;
        border-collapse: collapse;
    }
    table.gridtableX2 td {
        border-width: 1px;
        padding: 2px;
    }
    table.gridtableX2 td.cols {
        border-width: 1px;
        padding: 2px;
    }

    #testtable{
        width: 100%;
    }
</style>
<?php
// exit;
$html = ob_get_contents();
ob_end_clean();

$mpdf->WriteHTML($html);
$mpdf->SetTitle('BARCODE ');
$mpdf->Output('BARCODE '.date('dmyhis').'.pdf' ,'I');
