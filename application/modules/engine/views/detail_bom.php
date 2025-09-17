<?php
// print_r($header);
?>
<div class="box box-primary">
	<div class="box-body">
		<div class="form-group row">
			<div class="col-md-2">
				<label for="customer">Product Name</label>
			</div>
			<div class="col-md-6">
				<select id="id_product" name="id_product" class="form-control input-md chosen-select" disabled>
					<?php foreach ($results['product'] as $customer){
						$sel = ($customer->id_category2 == $header[0]->id_product)?'selected':'';
					?>
					<option value="<?= $customer->id_category2;?>" <?=$sel;?>><?= strtoupper(strtolower($customer->nama))?></option>
					<?php } ?>
				</select>
			</div>
			</div>
		<div class="form-group row">
			<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
				<thead>
					<tr class='bg-blue'>
						<th class='text-center' style='width: 10%;'>#</th>
						<th class='text-center' style='width: 50%;'>Material Name</th>
						<th class='text-center'>Weight</th>
					</tr>
				</thead>
				<tbody>
					<?php
						$SUM = 0;
						foreach($detail AS $val => $valx){ $val++;
							$SUM += $valx['weight'];
							echo "<tr>";
								echo "<td align='center'>".$val."</td>";
								echo "<td>".strtoupper(get_name('ms_material', 'nm_material', 'code_material', $valx['code_material']))."</td>";
								echo "<td align='right'>".number_format($valx['weight'],2)." Kg</td>";
							echo "</tr>";
						}
						echo "<tr>";
							echo "<td align='center'></td>";
							echo "<td align='left'><b>TOTAL WEIGHT</b></td>";
							echo "<td align='right'><b>".number_format($SUM,2)." Kg</b></td>";
						echo "</tr>";
					 ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
