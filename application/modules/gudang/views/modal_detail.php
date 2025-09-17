
<div class="box-body">
	<table id="my-grid" class="table" width="100%">
		<thead>
			<tr>
				<td class="text-left" style='vertical-align:middle;' width='15%'>No Transaksi</td>
				<td class="text-left" style='vertical-align:middle;' width='2%'>:</td>
				<td class="text-left" style='vertical-align:middle;'><?=$kode_trans;?></td>
			</tr>
			<tr>
				<td class="text-left" style='vertical-align:middle;'>Tanggal Request</td>
				<td class="text-left" style='vertical-align:middle;'>:</td>
				<td class="text-left" style='vertical-align:middle;'><?=$resv;?></td>
			</tr>
		</thead>
	</table><br>
	<table class="table table-striped table-bordered table-hover table-condensed" width="100%">
		<thead>
			<tr>
				<th class="text-center" style='vertical-align:middle;' width='4%'>No</th>
				<th class="text-left" style='vertical-align:middle;'>Material Name</th>
        <th class="text-right" style='vertical-align:middle;' width='9%'>Qty Mutasi (Pack)</th>
        <th class="text-right" style='vertical-align:middle;' width='9%'>Qty Mutasi (Kg)</th>
				<th class="text-left" style='vertical-align:middle;' width='12%'>Keterangan</th>
			</tr>
		</thead>
		<tbody>
			<?php
            $No=0;
			foreach($result AS $val => $valx){
                $No++;

        $konversi = get_konversi($valx['id_material']);
        $qty_pack 	= number_format($valx['qty_oke']/$konversi,2);
				$qty_oke 		= number_format($valx['qty_oke'],2);
				$keterangan = ucfirst($valx['keterangan']);
				if($checked == 'Y'){
          $qty_pack 	= number_format($valx['check_qty_oke']/$konversi,2);
					$qty_oke 		= number_format($valx['check_qty_oke'],2);
					$keterangan = ucfirst($valx['check_keterangan']);
				}
				echo "<tr>";
					echo "<td align='center'>".$No."</td>";
					echo "<td>".strtoupper($valx['nm_material'])."</td>";
          echo "<td align='right'>".$qty_pack."</td>";
					echo "<td align='right'>".$qty_oke."</td>";
					echo "<td>".$keterangan."</td>";
				echo "</tr>";
			}
			?>
		</tbody>
	</table>
</div>
<script>
	swal.close();
</script>
