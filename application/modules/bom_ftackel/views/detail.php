<?php
$id_product 	= (!empty($header[0]->id_product))?$header[0]->id_product:'0';
$nm_product		= (!empty($GET_LEVEL4[$id_product]['nama']))?$GET_LEVEL4[$id_product]['nama']:'';
?>
<div class="box box-primary">
	<div class="box-body">
	<br>
		<table width='100%'>
			<tr>
				<th width='20%'>Product Name</th>
				<td><?=$nm_product;?></td>
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
					<th class='text-left' style='width: 10%;'>SPK</th>
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
							echo "<td align='left'>".$valx['spk']."</td>";
							echo "<td align='right'>".number_format($valx['weight'],4)." Kg</td>";
						echo "</tr>";
					}
					echo "<tr>";
						echo "<td></td>";
						echo "<td colspan='5'><b>Total</b></td>";
						echo "<th class='text-right'>".number_format($SUM,4)." Kg</th>";
					echo "</tr>";

					echo "<tr>";
						echo "<td colspan='7' height='30px'></td>";
					echo "</tr>";
					if(!empty($detail_acc)){
						echo "<tr>";
							echo "<th>#</th>";
							echo "<th colspan='5'>Accessories Name</th>";
							echo "<th class='text-right'>Qty</th>";
						echo "</tr>";
						$SUMACC = 0;
						foreach($detail_acc AS $val => $valx){ $val++;
							$nm_material	= (!empty($GET_ACC[$valx['code_material']]['nama_full']))?$GET_ACC[$valx['code_material']]['nama_full']:'-';
							$SUMACC += $valx['weight'];
							echo "<tr>";
								echo "<td align='left'>".$val."</td>";
								echo "<td colspan='5'>".strtoupper($nm_material)."</td>";
								echo "<td align='right'>".number_format($valx['weight'],2)."</td>";
							echo "</tr>";
						}
						echo "<tr>";
							echo "<td></td>";
							echo "<td colspan='5'><b>Total</b></td>";
							echo "<th class='text-right'>".number_format($SUMACC,2)."</th>";
						echo "</tr>";
					}
					?>
			</tbody>
		</table>
	</div>
</div>
