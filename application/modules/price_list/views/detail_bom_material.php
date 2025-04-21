<?php
$id_product 	= (!empty($header[0]->id_product))?$header[0]->id_product:'0';
$variant_product 	= (!empty($header[0]->variant_product))?$header[0]->variant_product:'0';
$nm_product		= (!empty($GET_LEVEL4[$id_product]['nama']))?$GET_LEVEL4[$id_product]['nama']:'';

$file_upload 	= (!empty($header[0]->file_upload))?$header[0]->file_upload:'';

$BERAT_MINUS = 0;
if(!empty($detail_additive)){
	foreach($detail_additive AS $val => $valx){ $val++;
		$detail_custom    = $this->db->get_where('bom_detail_custom',array('no_bom_detail'=>$valx['no_bom_detail'],'category'=>'additive'))->result();
		$PENGURANGAN_BERAT = 0;
		foreach($detail_custom AS $valx2){
			$PENGURANGAN_BERAT += $valx2->weight * $valx2->persen /100;
		}
		$BERAT_MINUS += $PENGURANGAN_BERAT;
	}
}

$TOTAL_PRICE_ALL = 0;
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
					<th colspan='8'>A. Mixing & Proses</th>
				</tr>
				<tr>
					<th class='text-left' style='width: 3%;'>#</th>
					<th class='text-left'>Material Type</th>
					<th class='text-left'>Material Category</th>
					<th class='text-left'>Material Jenis</th>
					<th class='text-left'>Material Name</th>
					<th class='text-right' style='width: 8%;'>Berat</th>
					<th class='text-right' style='width: 1%;'></th>
					<th class='text-right' style='width: 8%;'>Berat Bersih</th>
					<th class='text-right' style='width: 8%;'>Price Ref</th>
					<th class='text-right' style='width: 8%;'>Total Price</th>
				</tr>
			</thead>
			<tbody>
				<?php
					foreach($detail AS $val => $valx){ $val++;
						$nm_material		= (!empty($GET_LEVEL4[$valx['code_material']]['nama']))?$GET_LEVEL4[$valx['code_material']]['nama']:'-';
						$code_lv1		= (!empty($GET_LEVEL4[$valx['code_material']]['code_lv1']))?$GET_LEVEL4[$valx['code_material']]['code_lv1']:'-';
						$code_lv2		= (!empty($GET_LEVEL4[$valx['code_material']]['code_lv2']))?$GET_LEVEL4[$valx['code_material']]['code_lv2']:'-';
						$code_lv3		= (!empty($GET_LEVEL4[$valx['code_material']]['code_lv3']))?$GET_LEVEL4[$valx['code_material']]['code_lv3']:'-';

                        $price_ref      = (!empty($GET_PRICE_REF[$valx['code_material']]['price_ref']))?$GET_PRICE_REF[$valx['code_material']]['price_ref']:0;

						$nm_category = strtolower(get_name('new_inventory_2','nama','code_lv2',$code_lv2));
						echo "<tr>";
							echo "<td align='left'>".$val."</td>";
							echo "<td>".strtoupper(get_name('new_inventory_1','nama','code_lv1',$code_lv1))."</td>";
							echo "<td>".strtoupper($nm_category)."</td>";
							echo "<td>".strtoupper(get_name('new_inventory_3','nama','code_lv3',$code_lv3))."</td>";
							echo "<td>".strtoupper($nm_material)."</td>";
							echo "<td align='right'>".number_format($valx['weight'],4)." Kg</td>";
							$berat_pengurang_additive = ($nm_category == 'resin')?$BERAT_MINUS:0;
							// if($nm_category == 'resin'){
							// 	echo "<td align='right' class='text-red'>".number_format($berat_pengurang_additive,4)." Kg</td>";
							// }
							// else{
								echo "<td align='right' class='text-red'></td>";
							// }
							$berat_bersih = $valx['weight'] - $berat_pengurang_additive;
                            $total_price = $berat_bersih * $price_ref;
                            $TOTAL_PRICE_ALL += $total_price;
							echo "<td align='right'>".number_format($berat_bersih,4)." Kg</td>";
							echo "<td align='right' class='text-green'>".number_format($price_ref,2)."</td>";
							echo "<td align='right' class='text-blue'>".number_format($total_price,2)."</td>";
						echo "</tr>";
					}
					?>
			</tbody>
			<thead hidden>
				<tr>
					<th colspan='8'>B. Additive</th>
				</tr>
				<tr>
					<th class='text-left'>#</th>
					<th class='text-left' colspan='3'>Fungsi Additive</th>
					<th class='text-left' colspan='4'></th>
				</tr>
			</thead>
			<tbody hidden>
				<?php
				$val = 0;
				if(!empty($detail_additive)){
					$BERAT_MINUS = 0;
					foreach($detail_additive AS $val => $valx){ $val++;
						echo "<tr>";
							echo "<td align='left'>".$val."</td>";
							echo "<td align='left' colspan='3'>";
								echo strtoupper(get_name('bom_header','additive_name','no_bom',$valx['code_material']));
							echo "</td>";
							echo "<td align='left' colspan='6'>";
								echo "<table width='100%'>";
									echo "<tr>";
										echo "<th>Material Name</th>";
										echo "<th width='11%' class='text-right'>Berat Bersih</th>";
										echo "<th width='11%' class='text-right'>% Pengurangan</th>";
										echo "<th width='11%' class='text-right'>Berat Pengurangan</th>";
                                        echo "<th width='11%'></th>";
                                        echo "<th width='11%'></th>";
									echo "</tr>";
									$detail_custom    = $this->db->get_where('bom_detail_custom',array('no_bom_detail'=>$valx['no_bom_detail'],'category'=>'additive'))->result();
									$nomor = 0;
									$PENGURANGAN_BERAT = 0;
									foreach($detail_custom AS $valx2){ $nomor++;
										$nm_material = (!empty($GET_LEVEL4[$valx2->code_material]['nama']))?$GET_LEVEL4[$valx2->code_material]['nama']:'';
										$PENGURANGAN_BERAT += $valx2->weight * $valx2->persen /100;

                                        $price_ref      = (!empty($GET_PRICE_REF[$valx2->code_material]['price_ref']))?$GET_PRICE_REF[$valx2->code_material]['price_ref']:0;
                                        $total_price    = $valx2->weight * $price_ref;
                                        $TOTAL_PRICE_ALL += $total_price;
										echo "<tr>";
											echo "<td>".$nm_material."</td>";
											echo "<td align='right'>".number_format($valx2->weight,4)." Kg</td>";
											echo "<td align='right'>".number_format($valx2->persen,2)." %</td>";
											echo "<td align='right'>".number_format($valx2->weight * $valx2->persen /100,4)." Kg</td>";
                                            echo "<td align='right' class='text-green'>".number_format($price_ref,2)."</td>";
							                echo "<td align='right' class='text-blue'>".number_format($total_price,2)."</td>";
										echo "</tr>";
									}
									$BERAT_MINUS += $PENGURANGAN_BERAT;
									echo "<tr>";
										echo "<td></td>";
										echo "<td align='right'></td>";
										echo "<td align='right'></td>";
										echo "<td class='text-red' align='right'>".number_format($PENGURANGAN_BERAT,4)." Kg</td>";
                                        echo "<td align='right'></td>";
                                        echo "<td align='right'></td>";
									echo "</tr>";
								echo "</table>";
							echo "</td>";
						echo "</tr>";
					}
					echo "<tr>";
						echo "<td align='left'></td>";
						echo "<td align='left' colspan='3'></td>";
						echo "<td align='left' colspan='6'>";
							echo "<table width='100%'>";
								echo "<tr>";
									echo "<th></th>";
									echo "<th width='11%' class='text-right'></th>";
									echo "<th width='11%' class='text-right'></th>";
									echo "<th width='11%' class='text-right text-red'>".number_format($BERAT_MINUS,4)." Kg</th>";
                                    echo "<th width='11%' class='text-right'></th>";
                                    echo "<th width='11%' class='text-right'></th>";
								echo "</tr>";
							echo "</table>";
						echo "</td>";
					echo "</tr>";
				}
				?>
			</tbody>
			<?php
			if(!empty($detail_topping)){
				?>
			<thead>
				<tr>
					<th colspan='8'>B. Topping</th>
				</tr>
				<tr>
					<th class='text-left'>#</th>
					<th class='text-left' colspan='3'>Topping</th>
					<th class='text-center' colspan='6'></th>
				</tr>
			</thead>
			<tbody>
				<?php
				$val = 0;
				if(!empty($detail_topping)){
					foreach($detail_topping AS $val => $valx){ $val++;
						echo "<tr>";
							echo "<td align='left'>".$val."</td>";
							echo "<td align='left' colspan='3'>";
								echo strtoupper(get_name('new_inventory_3','nama','code_lv3',get_name('bom_header','id_product','no_bom',$valx['code_material'])).' | '.get_name('bom_header','variant_product','no_bom',$valx['code_material']));
							echo "</td>";
							echo "<td align='left' colspan='6'>";
								echo "<table  width='100%'>";
									echo "<tr>";
										echo "<th>Material Name</th>";
										echo "<th width='11%' class='text-right'>Berat Bersih</th>";
                                        echo "<th width='11%' class='text-right'></th>";
                                        echo "<th width='11%' class='text-right'></th>";
									echo "</tr>";
									$detail_custom    = $this->db->get_where('bom_detail_custom',array('no_bom_detail'=>$valx['no_bom_detail'],'category'=>'topping'))->result();
									$nomor = 0;
									foreach($detail_custom AS $valx2){ $nomor++;
										$nm_material = (!empty($GET_LEVEL4[$valx2->code_material]['nama']))?$GET_LEVEL4[$valx2->code_material]['nama']:'';
                                        $price_ref      = (!empty($GET_PRICE_REF[$valx2->code_material]['price_ref']))?$GET_PRICE_REF[$valx2->code_material]['price_ref']:0;
                                        $total_price    = $valx2->weight * $price_ref;
                                        $TOTAL_PRICE_ALL += $total_price;
										echo "<tr>";
											echo "<td>".$nm_material."</td>";
											echo "<td align='right'>".number_format($valx2->weight,4)." Kg</td>";
											echo "<td align='right' class='text-green'>".number_format($price_ref,2)."</td>";
											echo "<td align='right' class='text-blue'>".number_format($total_price,2)."</td>";
										echo "</tr>";
									}
								echo "</table>";
							echo "</td>";
						echo "</tr>";
					}
				}
				?>
                
			</tbody>
			<?php } ?>
				<tr>
					<th class='text-left' colspan='4'></th>
					<th class='text-left' colspan='5'>TOTAL PRICE</th>
                    <th class='text-right text-red'><?=number_format($TOTAL_PRICE_ALL,2);?></th>
				</tr>
		</table>
	</div>
</div>
