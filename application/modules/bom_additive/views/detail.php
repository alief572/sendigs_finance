<?php
$additive_name 	= (!empty($header[0]->additive_name))?$header[0]->additive_name:'0';
?>
<div class="box box-primary">
	<div class="box-body">
		<br>
		<table width='100%'>
			<tr>
				<th width='20%'>Kegunaan Additive</th>
				<td><?=$additive_name;?></td>
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
					<th class='text-center' style='width: 15%;'>Additve Dari Total Resin (%)</th>
					<th class='text-center' style='width: 15%;'>Pengurangan Resin (%)</th>
				</tr>
			</thead>
			<tbody>
				<?php
					foreach($detail AS $val => $valx){ $val++;
						$nm_material	= (!empty($GET_LEVEL4[$valx['code_material']]['nama']))?$GET_LEVEL4[$valx['code_material']]['nama']:'-';
						$code_lv1		= (!empty($GET_LEVEL4[$valx['code_material']]['code_lv1']))?$GET_LEVEL4[$valx['code_material']]['code_lv1']:'-';
						$code_lv2		= (!empty($GET_LEVEL4[$valx['code_material']]['code_lv2']))?$GET_LEVEL4[$valx['code_material']]['code_lv2']:'-';
						$code_lv3		= (!empty($GET_LEVEL4[$valx['code_material']]['code_lv3']))?$GET_LEVEL4[$valx['code_material']]['code_lv3']:'-';
						$nm_category = strtolower(get_name('new_inventory_2','nama','code_lv2',$code_lv2));
						echo "<tr>";
							echo "<td align='left'>".$val."</td>";
							echo "<td>".strtoupper(get_name('new_inventory_1','nama','code_lv1',$code_lv1))."</td>";
							echo "<td>".strtoupper($nm_category)."</td>";
							echo "<td>".strtoupper(get_name('new_inventory_3','nama','code_lv3',$code_lv3))."</td>";
							echo "<td>".strtoupper($nm_material)."</td>";
							echo "<td align='center'>".number_format($valx['persen_add'],2)." %</td>";
							echo "<td align='center'>".number_format($valx['persen'],2)." %</td>";
						echo "</tr>";
					}
					?>
			</tbody>
		</table>
	</div>
</div>
