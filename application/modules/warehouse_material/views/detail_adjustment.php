
<div class="box-body">
	<br>
	<table class="table table-striped table-bordered table-hover table-condensed" width="100%">
		<thead id='head_table'>
			<tr>
				<th class="text-center" style='vertical-align:middle;' width='5%'>#</th>
				<th class="text-center" style='vertical-align:middle;' width='10%'>Material Id</th>
				<th class="text-center" style='vertical-align:middle;' >Material Name</th>
        <th class="text-center" style='vertical-align:middle;' width='8%'>Qty Packing</th>
				<th class="text-center" style='vertical-align:middle;' width='8%'>Qty Material</th>
				<!-- <th class="text-center" style='vertical-align:middle;' width='8%'>From</th>
				<th class="text-center" style='vertical-align:middle;' width='8%'>To</th> -->
				<!-- <th class="text-center" style='vertical-align:middle;' width='8%'>Early Stock</th>
				<th class="text-center" style='vertical-align:middle;' width='8%'>Last Stock</th>
				<th class="text-center" style='vertical-align:middle;' width='8%'>Early Booking</th>
				<th class="text-center" style='vertical-align:middle;' width='8%'>Last Booking</th>
				<th class="text-center" style='vertical-align:middle;' width='7%'>Early Damaged</th>
				<th class="text-center" style='vertical-align:middle;' width='7%'>Last Damaged</th> -->
			</tr>
		</thead>
		<tbody>
			<?php
            $No=0;
			foreach($results['qBQdetailRest'] AS $val => $valx){
                $No++;

				echo "<tr>";
					echo "<td align='center'>".$No."</td>";
					echo "<td>".$valx['idmaterial']."</td>";
					echo "<td>".strtoupper($valx['nm_material'])."</td>";
          echo "<td align='right'>".number_format($valx['jumlah_mat'] / get_konversi($valx['id_material']),3)."</td>";
					echo "<td align='right'>".number_format($valx['jumlah_mat'],3)."</td>";
					// echo "<td align='center'>".$valx['kd_gudang_dari']."</td>";
					// echo "<td align='center'>".$valx['kd_gudang_ke']."</td>";
					// echo "<td align='right'>".number_format($valx['qty_stock_awal'],3)." Kg</td>";
					// echo "<td align='right'>".number_format($valx['qty_stock_akhir'],3)." Kg</td>";
					// echo "<td align='right'>".number_format($valx['qty_booking_awal'],3)." Kg</td>";
					// echo "<td align='right'>".number_format($valx['qty_booking_akhir'],3)." Kg</td>";
					// echo "<td align='right'>".number_format($valx['qty_rusak_awal'],3)." Kg</td>";
					// echo "<td align='right'>".number_format($valx['qty_rusak_akhir'],3)." Kg</td>";
				echo "</tr>";
			}
			?>
		</tbody>
	</table>
</div>
<script>
swal.close();
</script>
