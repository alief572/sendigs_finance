
<div class="box-body"> 
	<br>
	<table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%">
		<thead id='head_table'>
			<tr class='bg-blue'>
				<th class="text-center" width='10%'>No PO</th> 
				<th class="text-center" width='30%'>Supplier Name</th>
				<th class="text-center">Material Name</th>
				<th class="text-center" width='10%'>Qty</th>
				<th class="text-center" width='10%'>Price/Unit</th>
				<th class="text-center" width='10%'>Total Price</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$jumlah = count($result);
			$no  = 0;
			$SUM = 0;
            foreach($result AS $val => $valx){ $no++;
				$SUM += $valx['qty_purchase'] * $valx['net_price'];
                echo "<tr>";
					if($no == '1'){
						echo "<td align='center' rowspan='".$jumlah."'>".$valx['no_po']."</td>";
					}
					if($no == '1'){
						echo "<td align='left' rowspan='".$jumlah."'>".$valx['nm_supplier']."</td>";
					}
					echo "<td align='left'>".strtoupper($valx['nm_barang'])."</td>";
					echo "<td align='right'>".number_format($valx['qty_purchase'],2)."</td>";
					echo "<td align='right'>".number_format($valx['net_price'],2)." <span class='text-primary text-bold'>".strtoupper($valx['currency'])."</span></td>";
					echo "<td align='right'>".number_format($valx['total_price'],2)."</td>";
				echo "</tr>";
			}
			echo "<tr>";
				echo "<td align='left' colspan='4'></td>";
				echo "<td align='right'><b>TOTAL PRICE</b></td>";
				echo "<td align='right'><b>".number_format($SUM,2)."</b></td>";
			echo "</tr>";
			?>
		</tbody>
	</table>
</div>

<script>
	$(document).ready(function(){
		swal.close();
	});
</script>