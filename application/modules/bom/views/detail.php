<?php
$id_product 		= (!empty($header[0]->id_product))?$header[0]->id_product:'-';
$variant_product 	= (!empty($header[0]->variant_product))?$header[0]->variant_product:'-';
$color_product 		= (!empty($header[0]->color))?$header[0]->color:'-';
$keterangan 		= (!empty($header[0]->keterangan))?$header[0]->keterangan:'-';
$moq 				= (!empty($header[0]->moq))?number_format($header[0]->moq,4):'-';
$nm_product			= (!empty($GET_LEVEL4[$id_product]['nama']))?$GET_LEVEL4[$id_product]['nama']:'';
$waste_product 			= (!empty($header[0]->waste_product))?number_format($header[0]->waste_product,4):'-';
$waste_setting_resin 	= (!empty($header[0]->waste_setting_resin))?number_format($header[0]->waste_setting_resin,4):'-';
$waste_setting_glass 	= (!empty($header[0]->waste_setting_glass))?number_format($header[0]->waste_setting_glass,4):'-';
$width 					= (!empty($header[0]->width))?number_format($header[0]->width,2):'-';
$length 				= (!empty($header[0]->length))?number_format($header[0]->length,2):'-';
?>
<div class="box box-primary">
	<div class="box-body">
	<br>
		<table width='100%'>
			<tr>
				<th width='20%'>Product Name</th>
				<td width='40%'><?=$nm_product;?></td>
				<th width='20%'>Waste Product (Kg)</th>
				<td width='20%'><?=$waste_product;?></td>
			</tr>
			<tr>
				<th>Variant Product</th>
				<td><?=$variant_product;?></td>
				<th>Waste Setting & Cleaning Resin (Kg)</th>
				<td><?=$waste_setting_resin;?></td>
			</tr>
			<tr>
				<th>Color Product</th>
				<td><?=$color_product;?></td>
				<th>Waste Setting & Cleaning Glass (Kg)</th>
				<td><?=$waste_setting_glass;?></td>
			</tr>
			<tr>
				<th>Keterangan</th>
				<td><?=$keterangan;?></td>
				<th>Width (m)</th>
				<td><?=$width;?></td>
			</tr>
			<tr>
				<th>MOQ</th>
				<td><?=$moq;?></td>
				<th>Length (m)</th>
				<td><?=$length;?></td>
			</tr>
		</table>
		<hr>
		<table class='' width='100%'>
			<thead>
				<tr>
					<th class='text-left' style='width: 3%;'>#</th>
					<th class='text-left'>Material Type</th>
					<th class='text-left'>Material Category</th>
					<th class='text-left'>Material Jenis</th>
					<th class='text-left'>Material Name</th>
					<th class='text-right' style='width: 15%;'>Berat</th>
				</tr>
			</thead>
			<tbody>
				<?php
					$SUM = 0;
					foreach($detail AS $val => $valx){ $val++;
						$nm_material	= (!empty($GET_LEVEL4[$valx['code_material']]['nama']))?$GET_LEVEL4[$valx['code_material']]['nama']:'-';
						$code_lv1		= (!empty($GET_LEVEL4[$valx['code_material']]['code_lv1']))?$GET_LEVEL4[$valx['code_material']]['code_lv1']:'-';
						$code_lv2		= (!empty($GET_LEVEL4[$valx['code_material']]['code_lv2']))?$GET_LEVEL4[$valx['code_material']]['code_lv2']:'-';
						$code_lv3		= (!empty($GET_LEVEL4[$valx['code_material']]['code_lv3']))?$GET_LEVEL4[$valx['code_material']]['code_lv3']:'-';
						$SUM += $valx['weight'];
						$nm_category = strtolower(get_name('new_inventory_2','nama','code_lv2',$code_lv2));
						echo "<tr>";
							echo "<td align='left'>".$val."</td>";
							echo "<td>".strtoupper(get_name('new_inventory_1','nama','code_lv1',$code_lv1))."</td>";
							echo "<td>".strtoupper($nm_category)."</td>";
							echo "<td>".strtoupper(get_name('new_inventory_3','nama','code_lv3',$code_lv3))."</td>";
							echo "<td>".strtoupper($nm_material)."</td>";
							echo "<td align='right'>".number_format($valx['weight'],4)." Kg</td>";
						echo "</tr>";
					}
					echo "<tr>";
						echo "<td></td>";
						echo "<td colspan='4'><b>Total Berat</b></td>";
						echo "<th class='text-right'>".number_format($SUM,4)." Kg</th>";
					echo "</tr>";
					?>
			</tbody>
		</table>
	</div>
</div>
