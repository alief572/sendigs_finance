<div class="box box-primary">
	<div class="box-body">
		<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
			<thead class='thead'>
				<tr class='bg-blue'>
					<th class='text-center th'>#</th>
					<th class='text-left th'>Material Name</th>
					<th class='text-right th'>Qty Order SO</th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ($detail as $key => $value) { $key++;
					$nm_material = (!empty($GET_LEVEL4[$value['id_material']]['nama']))?$GET_LEVEL4[$value['id_material']]['nama']:'';
					echo "<tr>";
						echo "<td class='text-center'>".$key."</td>";
						echo "<td class='text-left'>".$nm_material."</td>";
						echo "<td class='text-right'>".number_format($value['qty_order'],5)." Kg</td>";
					echo "</tr>";
				}
				?>
			</tbody>
		</table>
	</div>
</div>