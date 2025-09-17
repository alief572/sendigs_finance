<?php
$id_product 	= (!empty($header[0]->id_product))?$header[0]->id_product:'0';
$variant_product 	= (!empty($header[0]->variant_product))?$header[0]->variant_product:'0';
$nm_product		= (!empty($GET_LEVEL4[$id_product]['nama']))?$GET_LEVEL4[$id_product]['nama']:'';

$fire_retardant = (!empty($header[0]->fire_retardant))?$header[0]->fire_retardant:'0';
$anti_uv 		= (!empty($header[0]->anti_uv))?$header[0]->anti_uv:'0';
$tixotropic 	= (!empty($header[0]->tixotropic))?$header[0]->tixotropic:'0';
$food_grade 	= (!empty($header[0]->food_grade))?$header[0]->food_grade:'0';
$wax 			= (!empty($header[0]->wax))?$header[0]->wax:'0';
$corrosion 		= (!empty($header[0]->corrosion))?$header[0]->corrosion:'0';
?>
<div class="box box-primary">
	<div class="box-body">
		<br>
		<table width='100%'>
			<tr>
				<th width='20%'>Product Name</th>
				<td><?=$nm_product;?></td>
			</tr>
			<tr>
				<th>Variant Product</th>
				<td><?=$variant_product;?></td>
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
		<hr>
		<table width='100%'>
			<tr>
				<th width='20%'>Fire Reterdant</th>
				<td><?=($fire_retardant == '0')?'No':'Yes';?></td>
			</tr>
			<tr>
				<th>Anti UV</th>
				<td><?=($anti_uv == '0')?'No':'Yes';?></td>
			</tr>
			<tr>
				<th>Tixotropic</th>
				<td><?=($tixotropic == '0')?'No':'Yes';?></td>
			</tr>
			<tr>
				<th>Food Grade</th>
				<td><?=($food_grade == '0')?'No':'Yes';?></td>
			</tr>
			<tr>
				<th>Wax</th>
				<td><?=($wax == '0')?'No':'Yes';?></td>
			</tr>
			<tr>
				<th>Corrosion</th>
				<td><?=$corrosion;?></td>
			</tr>
		</table>
	</div>
</div>
