
<div class="box-body">
	<br>
	<table class="table table-striped table-bordered table-hover table-condensed" width="100%">
		<thead id='head_table'>
			<tr>
				<th class="text-center" style='vertical-align:middle;' width='5%'>#</th>
				<th class="text-center" style='vertical-align:middle;'>Material Name</th>
        <th class="text-center" style='vertical-align:middle;'>Qty Material</th>
				<th class="text-center" style='vertical-align:middle;'>Qty Packing</th>
        <th class="text-center" style='vertical-align:middle;'>Qty Actual</th>
			</tr>
		</thead>
		<tbody>
			<?php
            $No=0;
			foreach($results['data'] AS $val => $valx){
                $No++;

				echo "<tr>";
					echo "<td align='center'>".$No."</td>";
					echo "<td>".strtoupper($valx['nm_material'])."</td>";
          echo "<td align='right'>".number_format($valx['qty_material'],2)." ".ucfirst($valx['unit'])."</td>";
					echo "<td align='right'>".number_format($valx['qty_packing'],2)." ".ucfirst($valx['unit_packing'])."</td>";
          echo "<td align='right'>".number_format($valx['qty_aktual'],2)." ".ucfirst($valx['unit_aktual'])."</td>";
				echo "</tr>";
			}
			?>
		</tbody>
	</table>
</div>
<script>
swal.close();
</script>
