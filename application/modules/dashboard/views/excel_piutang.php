<?php
if($kategori==1){
	$file_name	= 'Laporan_Aging_Piutang_0-15';
	$title_name	= 'Aging 0-15 Hari';
	$judul		= 'Laporan Aging Piutang Customer 0-15 Hari';
}else if($kategori==2){
	$file_name	= 'Laporan_Aging_Piutang_16-30';
	$title_name	= 'Aging 16-30 Hari';
	$judul		= 'Laporan Aging Piutang Customer 16-30 Hari';
}else if($kategori==3){
	$file_name	= 'Laporan_Aging_Piutang_31-60';
	$title_name	= 'Aging 31-60 Hari';
	$judul		= 'Laporan Aging Piutang Customer 31-60 Hari';
}else if($kategori==4){
	$file_name	= 'Laporan_Aging_Piutang_61-90';
	$title_name	= 'Aging 61-90 Hari';
	$judul		= 'Laporan Aging Piutang Customer 61-90 Hari';
}else if($kategori==5){
	$file_name	= 'Laporan_Aging_Piutang_lebih_90';
	$title_name	= 'Aging > 90 Hari';
	$judul		= 'Laporan Aging Piutang Customer > 90 Hari';
}

date_default_timezone_set("Asia/Bangkok");
header("Content-type: application/vnd-ms-excel");

header("Content-Disposition: attachment; filename=".$file_name.".xls");

header("Pragma: no-cache");

header("Expires: 0");
$Arr_Data	= array(
	'no_invoice'			=> 'No Invoice',
	'tanggal_invoice'		=> 'Tanggal Invoice',
	'kdcab'					=> 'Cabang',
	'nm_customer'			=> 'Customer',
	'nm_salesman'			=> 'Salesman',
	'hargajualtotal'		=> 'Total Invoice',
	'jum_bayar'				=> 'Payment',
	'hargajualtotal - jum_bayar'=> 'AR',
	'umur'					=> 'Aging (Days)'
);
?>
<!DOCTYPE html>
<html>
<head>
    <title></title>
    <style>



        #header-tabel tr {
            padding: 0px;
        }
        #tabel-laporan {
            border-spacing: -1px;
        }

        #tabel-laporan th{
            /*
            border-top: solid 1px #000;
            border-bottom: solid 1px #000;
            */
           border : dotted 1px #000;
            margin: 0px;
            height: 20px;
        }

        #tabel-laporan td{
            border : dotted 1px #000;
            margin: 0px;
            height: 20px;
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
<body style="border: solid 1px #000;">
    <div id="wrapper">
    <table width="100%" id="tabel-laporan">
          <tr>
              <th colspan="10" style="font-size: 12pt !important;">
                  <center>
                  <?php echo $judul;?>
                  </center>
              </th>
          </tr>
         
          <tr>
			<?php
			echo" <th align='center'>No</th>";
			foreach($Arr_Data as $key=>$vals){
				echo" <th align='center'>".$vals."</th>";
			}
			?>
          </tr>
       <?php
        
        if(@$rows_ar){
			$loop		=0;
			$sekarang	= date('Y-m-d');
			$Total_Inv 	= $Total_Pay = $Total_AR	=0;
			foreach($rows_ar as $key=>$val){
				$loop++;
				
				echo"<tr>";
					echo"<td>$loop</td>";
					$intD		= 0;
					foreach($Arr_Data as $keyF=>$valF){
						$intD++;
						if($intD==2){
							$Nil_Data	= date('d M Y',strtotime($val->$keyF));
						}else if($intD==6){
							$Nil_Data	= number_format(round($val->$keyF));
							$Total_Inv	+=round($val->$keyF);
						}else  if($intD==7){
							$Nil_Data	= number_format(round($val->$keyF));
							$Total_Pay	+=round($val->$keyF);
						}else if($intD==8){
							$Nil_Data	= number_format(round($val->hargajualtotal) - round($val->jum_bayar));
							$Total_AR	+= (round($val->hargajualtotal) - round($val->jum_bayar));
						}else{
							$Nil_Data	= $val->$keyF;
						}
						echo"<td>$Nil_Data</td>";
					}
				echo"</tr>";
				
			}
			echo"<tr>";
				echo"<td colspan='5'><center><strong> GRAND TOTAL</strong></center></td>";
				echo"<td style='text-align:right;font-weight:bold;'>".number_format($Total_Inv)."</td>";
				echo"<td style='text-align:right;font-weight:bold;'>".number_format($Total_Pay)."</td>";
				echo"<td style='text-align:right;font-weight:bold;'>".number_format($Total_AR)."</td>";
				echo"<td style='text-align:right;font-weight:bold;'></td>";
				
			echo"</tr>";
			
		}
      ?>
    </table>
    </div>
   

</body>
</html>
