<?php
$header_supp="";$jumlah_supp=0;
foreach($dt_supplier AS $val => $valx){
	$jumlah_supp++;
	$header_supp.='<th>'.$valx->nm_supplier.'</th>';
}
$no  = 0;
$datarow=array();//print_r($result);
foreach($result AS $val => $valx){ 
	$no++;
	$datarow['material'.$valx->id_barang]['id_barang']=$valx->id_barang;
	$datarow['material'.$valx->id_barang]['no_rfq']=$valx->no_rfq;
	$datarow['material'.$valx->id_barang]['nm_barang']=$valx->nm_barang;
	$datarow['material'.$valx->id_barang]['price_ref']=$valx->price_ref;
	$datarow['material'.$valx->id_barang]['qty']=$valx->qty;
	$datarow['material'.$valx->id_barang]['tgl_dibutuhkan']=$valx->tgl_dibutuhkan;
	$datarow['material'.$valx->id_barang][$valx->id_supplier]=array('price_ref_sup'=>$valx->price_ref_sup,'moq'=>$valx->moq,'lead_time'=>$valx->lead_time,'currency'=>$valx->currency);	
}
?>
<div class="box-body"> 
	<br>
	<table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%">
		<thead id='head_table'>
			<tr class='bg-blue'>
				<th class="text-center" width='7%' rowspan=2>No RFQ</th> 
				<th class="text-center" width='15%' rowspan=2>Material Name</th>
				<th class="text-center" width='6%' rowspan=2>Price Ref ($)</th>
				<th class="text-center" width='7%' rowspan=2>Qty PR</th>
				<th class="text-center" width='6%' colspan=<?=$jumlah_supp?>>Harga</th>
				<th class="text-center" width='6%' colspan=<?=$jumlah_supp?>>MOQ</th>
				<th class="text-center" width='6%' colspan=<?=$jumlah_supp?>>Lead Time</th>
				<th class="text-center" width='7%' rowspan=2>Tgl Dibutuhkan</th>
			</th>
			</tr>
			<tr class='bg-blue'>
				<?=$header_supp?>
				<?=$header_supp?>
				<?=$header_supp?>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach($datarow as $keys => $value) {
				echo '<tr><td>'.$value['no_rfq'].'</td>
				<td>'.$value['nm_barang'].'</td>
				<td>'.$value['price_ref'].'</td>
				<td>'.$value['qty'].'</td>';
				foreach($dt_supplier AS $val => $valx){
					echo '<td align=right nowrap>'.strtoupper($value[$valx->id_supplier]['currency']).' '.number_format($value[$valx->id_supplier]['price_ref_sup']).'</td>';
				}
				foreach($dt_supplier AS $val => $valx){
					echo '<td align=center>'.$value[$valx->id_supplier]['moq'].'</td>';
				}
				foreach($dt_supplier AS $val => $valx){
					echo '<td align=center>'.$value[$valx->id_supplier]['lead_time'].'</td>';
				}
				echo'<td>'.date('d-m-Y', strtotime($value['tgl_dibutuhkan'])).'</td>
				</tr>';
			}
/*			
            foreach($result AS $val => $valx){ $no++;
                echo "<tr>";
					if($no == '1'){
						echo "<td align='center' rowspan='".$jumlah."'>".$valx['no_rfq']."</td>";
					}
					if(in_array($no, $dataArr) || $no == '1'){
						echo "<td align='left' rowspan='".$rows2."'>".$valx['nm_supplier']."</td>";
					}
					if(in_array($no, $dataArr) || $no == '1'){
						echo "<td align='left' rowspan='".$rows2."'><b>".strtoupper($valx['lokasi'])."</b><br>".strtoupper($valx['alamat_supplier'])."<br><b>CURRENCY : ".strtoupper($valx['currency'])."</b></td>";
					}
					echo "<td align='left'>".strtoupper($valx['nm_barang'])."</td>";
					echo "<td align='right'>".number_format($valx['price_ref'],2)."</td>";
					echo "<td align='right'>".number_format($valx['price_ref_sup'],2)."</td>";
					echo "<td align='right'>".number_format($valx['harga_idr'])."</td>";
					echo "<td align='right'>".number_format($valx['qty'])."</td>";
					echo "<td align='center'>".number_format($valx['moq'])."</td>";
					echo "<td align='center'>".number_format($valx['lead_time'])."</td>";
					echo "<td align='center'>".date('d-m-Y', strtotime($valx['tgl_dibutuhkan']))."</td>";
					echo "<td align='right'>".number_format($valx['total_harga'])."</td>";
				echo "</tr>";
			}
*/
			?>
		</tbody>
	</table>
</div>

<script>
	$(document).ready(function(){
		swal.close();
	});
</script>