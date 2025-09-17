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
					<th colspan='8'>A. BOM Single Product</th>
				</tr>
				<tr>
					<th class='text-left'>#</th>
					<th class='text-left'>BOM Standard / HI Grid Standard</th>
					<th class='text-right'>Qty</th>
					<th class='text-left'></th>
					<th class='text-left'>Unit</th>
					<th class='text-left' colspan='3'>Keterarangan</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td class='text-left' width='3%'></td>
					<td class='text-left'></td>
					<td class='text-left' width='10%'></td>
					<td class='text-left' width='10%'></td>
					<td class='text-left' width='10%'></td>
					<td class='text-left' width='10%'></td>
					<td class='text-left' width='10%'></td>
					<td class='text-left' width='10%'></td>
				</tr>
				<?php
				$val = 0;
				if(!empty($detail_hi_grid)){
					foreach($detail_hi_grid AS $val => $valx){ $val++;
						$QTY = $valx['qty'];
						echo "<tr>";
							echo "<td align='left' style='vertical-align:top;'>".$val."</td>";
							echo "<td align='left' style='vertical-align:top;'>";
								echo strtoupper(get_name('new_inventory_4','nama','code_lv4',get_name('bom_header','id_product','no_bom',$valx['code_material'])).' | '.get_name('bom_header','variant_product','no_bom',$valx['code_material']));
								$detail_ukuranJadi    = $this->db->get_where('bom_detail_custom',array('no_bom_detail'=>$valx['no_bom_detail'],'category'=>'ukuran jadi'))->result();
								if(!empty($detail_ukuranJadi)){
									echo "<h5><b>Ukuran Jadi</b></h5>";
									echo "<table  width='100%' border='1'>";
										echo "<tr>";
											echo "<th width='10%' class='text-center'>#</th>";
											echo "<th width='30%' class='text-center'>Length</th>";
											echo "<th width='30%' class='text-center'>Width</th>";
											echo "<th width='30%' class='text-center'>Qty</th>";
										echo "</tr>";
										$nomor = 0;
										foreach($detail_ukuranJadi AS $valx2){ $nomor++;
											echo "<tr>";
												echo "<td align='center'>".$nomor."</td>";
												echo "<td align='center'>".number_format($valx2->length,2)."</td>";
												echo "<td align='center'>".number_format($valx2->width,2)."</td>";
												echo "<td align='center'>".number_format($valx2->qty,2)."</td>";
											echo "</tr>";
										}
									echo "</table>";
								}
								//material cutting
								$detailMaterialCutting    = $this->db->get_where('bom_detail_custom',array('no_bom_detail'=>$valx['no_bom_detail'],'category'=>'material cutting'))->result();
								if(!empty($detailMaterialCutting)){
									echo "<h5><b>Material Cutting</b></h5>";
									echo "<table  width='100%' border='1'>";
										echo "<tr>";
											echo "<th width='10%' class='text-center'>#</th>";
											echo "<th width='60%' class='text-left' colspan='2'>Material</th>";
											echo "<th width='30%' class='text-right'>Qty</th>";
										echo "</tr>";
										$nomor = 0;
										foreach($detailMaterialCutting AS $valx2){ $nomor++;
											$nm_material = (!empty($GET_LEVEL4[$valx2->code_material]['nama']))?$GET_LEVEL4[$valx2->code_material]['nama']:'';
											echo "<tr>";
												echo "<td align='center'>".$nomor."</td>";
												echo "<td align='left' colspan='2'>".$nm_material."</td>";
												echo "<td align='right'>".number_format($valx2->weight,4)."</td>";
											echo "</tr>";
										}
									echo "</table>";
								}
							echo "</td>";
							echo "<td align='right' style='vertical-align:top;'>".number_format($QTY,2)."</td>";
							echo "<td align='right'></td>";
							echo "<td align='left' style='vertical-align:top;'>".get_name('ms_satuan','code','id',$valx['unit'])."</td>";
							echo "<td align='left' style='vertical-align:top;' colspan='4'>".$valx['ket']."</td>";
						echo "</tr>";
					}
				}
				?>
			</tbody>
			<thead>
			<thead>
				<tr>
					<th colspan='6'>B. Accessories</th>
				</tr>
				<tr>
					<th class='text-left'>#</th>
					<th class='text-left'>Accessories Name</th>
					<th class='text-right'>Qty</th>
					<th class='text-right'></th>
					<th class='text-right'></th>
					<th class='text-left' colspan='3'>Keterangan</th>
				</tr>
			</thead>
			<tbody>
				<?php
					foreach($detail_accessories AS $val => $valx){ $val++;
						$nm_material	= (!empty($GET_ACC[$valx['code_material']]['nama_full']))?$GET_ACC[$valx['code_material']]['nama_full']:'-';

						echo "<tr>";
							echo "<td align='left'>".$val."</td>";
							echo "<td>".strtoupper($nm_material)."</td>";
							echo "<td align='right'>".number_format($valx['weight'],2)."</td>";
							echo "<td align='left'></td>";
							echo "<td align='left'></td>";
							echo "<td align='left' colspan='3'>".$valx['ket']."</td>";
						echo "</tr>";
					}
					?>
			</tbody>
			<thead>
				<tr>
					<th colspan='6'>C. Material Joint & Finishing</th>
				</tr>
				<tr>
					<th class='text-left'>#</th>
					<th class='text-left'>Material</th>
					<th class='text-right'>Qty</th>
					<th class='text-left'></th>
					<th class='text-left'>Layer</th>
					<th class='text-left' colspan='3'>Keterangan</th>
				</tr>
			</thead>
			<tbody>
				<?php
					foreach($detail_mat_joint AS $val => $valx){ $val++;
						$nm_material	= (!empty($GET_LEVEL4[$valx['code_material']]['nama']))?$GET_LEVEL4[$valx['code_material']]['nama']:'-';

						echo "<tr>";
							echo "<td align='left'>".$val."</td>";
							echo "<td>".strtoupper($nm_material)."</td>";
							echo "<td align='right'>".number_format($valx['weight'],4)."</td>";
							echo "<td></td>";
							echo "<td>".strtoupper($valx['layer'])."</td>";
							echo "<td  colspan='3'>".strtoupper($valx['ket'])."</td>";
						echo "</tr>";
					}
					?>
			</tbody>
		</table>
		<table class='' width='50%'>
			<thead>
				<tr>
					<th colspan='6'>D. Flat Sheet</th>
				</tr>
				<tr>
					<th class='text-left' style='width: 4%;'>#</th>
					<th class='text-left' style='width: 23%;'>Length</th>
					<th class='text-right' style='width: 23%;'>Width</th>
					<th class='text-right' style='width: 23%;'>Qty</th>
					<th class='text-right' style='width: 23%;'>M2</th>
				</tr>
			</thead>
			<tbody>
				<?php
					foreach($detail_flat_sheet AS $val => $valx){ $val++;
						echo "<tr>";
							echo "<td align='left'>".$val."</td>";
							echo "<td align='left'>".number_format($valx['length'],2)."</td>";
							echo "<td align='right'>".number_format($valx['width'],2)."</td>";
							echo "<td align='right'>".number_format($valx['qty'],2)."</td>";
							echo "<td align='right'>".number_format($valx['m2'],2)."</td>";
						echo "</tr>";

						//material
						$detailMaterialCutting    = $this->db->get_where('bom_detail_custom',array('no_bom_detail'=>$valx['no_bom_detail'],'category'=>'material flat sheet'))->result();
						if(!empty($detailMaterialCutting)){
							echo "<tr>";
							echo "<td></td>";
							echo "<td colspan='4'>";
							echo "<h5><b>Material</b></h5>";
							echo "<table  width='100%' border='1'>";
								echo "<tr>";
									echo "<th width='10%' class='text-center'>#</th>";
									echo "<th width='60%' class='text-left' colspan='2'>Material</th>";
									echo "<th width='30%' class='text-right'>Qty</th>";
								echo "</tr>";
								$nomor = 0;
								foreach($detailMaterialCutting AS $valx2){ $nomor++;
									$nm_material = (!empty($GET_LEVEL4[$valx2->code_material]['nama']))?$GET_LEVEL4[$valx2->code_material]['nama']:'';
									echo "<tr>";
										echo "<td align='center'>".$nomor."</td>";
										echo "<td align='left' colspan='2'>".$nm_material."</td>";
										echo "<td align='right'>".number_format($valx2->weight,4)."</td>";
									echo "</tr>";
								}
							echo "</table>";
							echo "</td>";
							echo "</tr>";
						}
					}
					?>
			</tbody>
		</table>
		<table class='' width='50%'>
			<thead>
				<tr>
					<th colspan='6'>E. End Plate / Kick Plate</th>
				</tr>
				<tr>
					<th class='text-left' style='width: 4%;'>#</th>
					<th class='text-left' style='width: 23%;'>Length</th>
					<th class='text-right' style='width: 23%;'>Height</th>
					<th class='text-right' style='width: 23%;'>Qty</th>
					<th class='text-right' style='width: 23%;'>M2</th>
				</tr>
			</thead>
			<tbody>
				<?php
					foreach($detail_end_plate AS $val => $valx){ $val++;
						echo "<tr>";
							echo "<td align='left'>".$val."</td>";
							echo "<td align='left'>".number_format($valx['length'],2)."</td>";
							echo "<td align='right'>".number_format($valx['width'],2)."</td>";
							echo "<td align='right'>".number_format($valx['qty'],2)."</td>";
							echo "<td align='right'>".number_format($valx['m2'],2)."</td>";
						echo "</tr>";

						//material
						$detailMaterialCutting    = $this->db->get_where('bom_detail_custom',array('no_bom_detail'=>$valx['no_bom_detail'],'category'=>'material end plate'))->result();
						if(!empty($detailMaterialCutting)){
							echo "<tr>";
							echo "<td></td>";
							echo "<td colspan='4'>";
							echo "<h5><b>Material</b></h5>";
							echo "<table  width='100%' border='1'>";
								echo "<tr>";
									echo "<th width='10%' class='text-center'>#</th>";
									echo "<th width='60%' class='text-left' colspan='2'>Material</th>";
									echo "<th width='30%' class='text-right'>Qty</th>";
								echo "</tr>";
								$nomor = 0;
								foreach($detailMaterialCutting AS $valx2){ $nomor++;
									$nm_material = (!empty($GET_LEVEL4[$valx2->code_material]['nama']))?$GET_LEVEL4[$valx2->code_material]['nama']:'';
									echo "<tr>";
										echo "<td align='center'>".$nomor."</td>";
										echo "<td align='left' colspan='2'>".$nm_material."</td>";
										echo "<td align='right'>".number_format($valx2->weight,4)."</td>";
									echo "</tr>";
								}
							echo "</table>";
							echo "</td>";
							echo "</tr>";
						}
					}
					?>
			</tbody>
		</table>
		<table class='' width='50%'>
			<thead>
				<tr>
					<th colspan='6'>F. Chequered Plate</th>
				</tr>
				<tr>
					<th class='text-left' style='width: 4%;'>#</th>
					<th class='text-left' style='width: 23%;'>Length</th>
					<th class='text-right' style='width: 23%;'>Width</th>
					<th class='text-right' style='width: 23%;'>Qty</th>
					<th class='text-right' style='width: 23%;'>M2</th>
				</tr>
			</thead>
			<tbody>
				<?php
					foreach($detail_ukuran_jadi AS $val => $valx){ $val++;
						echo "<tr>";
							echo "<td align='left'>".$val."</td>";
							echo "<td align='left'>".number_format($valx['length'],2)."</td>";
							echo "<td align='right'>".number_format($valx['width'],2)."</td>";
							echo "<td align='right'>".number_format($valx['qty'],2)."</td>";
							echo "<td align='right'>".number_format($valx['m2'],2)."</td>";
						echo "</tr>";

						//material
						$detailMaterialCutting    = $this->db->get_where('bom_detail_custom',array('no_bom_detail'=>$valx['no_bom_detail'],'category'=>'material ukuran jadi'))->result();
						if(!empty($detailMaterialCutting)){
							echo "<tr>";
							echo "<td></td>";
							echo "<td colspan='4'>";
							echo "<h5><b>Material</b></h5>";
							echo "<table  width='100%' border='1'>";
								echo "<tr>";
									echo "<th width='10%' class='text-center'>#</th>";
									echo "<th width='60%' class='text-left' colspan='2'>Material</th>";
									echo "<th width='30%' class='text-right'>Qty</th>";
								echo "</tr>";
								$nomor = 0;
								foreach($detailMaterialCutting AS $valx2){ $nomor++;
									$nm_material = (!empty($GET_LEVEL4[$valx2->code_material]['nama']))?$GET_LEVEL4[$valx2->code_material]['nama']:'';
									echo "<tr>";
										echo "<td align='center'>".$nomor."</td>";
										echo "<td align='left' colspan='2'>".$nm_material."</td>";
										echo "<td align='right'>".number_format($valx2->weight,4)."</td>";
									echo "</tr>";
								}
							echo "</table>";
							echo "</td>";
							echo "</tr>";
						}
					}
					?>
			</tbody>
		</table>
		<table class='' width='50%'>
			<thead>
				<tr>
					<th colspan='6'>G. Others</th>
				</tr>
				<tr>
					<th class='text-left' style='width: 4%;'>#</th>
					<th class='text-left' style='width: 23%;'>Length</th>
					<th class='text-right' style='width: 23%;'>Width</th>
					<th class='text-right' style='width: 23%;'>Qty</th>
					<th class='text-right' style='width: 23%;'>M2</th>
				</tr>
			</thead>
			<tbody>
				<?php
					foreach($detail_others AS $val => $valx){ $val++;
						echo "<tr>";
							echo "<td align='left'>".$val."</td>";
							echo "<td align='left'>".number_format($valx['length'],2)."</td>";
							echo "<td align='right'>".number_format($valx['width'],2)."</td>";
							echo "<td align='right'>".number_format($valx['qty'],2)."</td>";
							echo "<td align='right'>".number_format($valx['m2'],2)."</td>";
						echo "</tr>";

						//material
						$detailMaterialCutting    = $this->db->get_where('bom_detail_custom',array('no_bom_detail'=>$valx['no_bom_detail'],'category'=>'material others'))->result();
						if(!empty($detailMaterialCutting)){
							echo "<tr>";
							echo "<td></td>";
							echo "<td colspan='4'>";
							echo "<h5><b>Material</b></h5>";
							echo "<table  width='100%' border='1'>";
								echo "<tr>";
									echo "<th width='10%' class='text-center'>#</th>";
									echo "<th width='60%' class='text-left' colspan='2'>Material</th>";
									echo "<th width='30%' class='text-right'>Qty</th>";
								echo "</tr>";
								$nomor = 0;
								foreach($detailMaterialCutting AS $valx2){ $nomor++;
									$nm_material = (!empty($GET_LEVEL4[$valx2->code_material]['nama']))?$GET_LEVEL4[$valx2->code_material]['nama']:'';
									echo "<tr>";
										echo "<td align='center'>".$nomor."</td>";
										echo "<td align='left' colspan='2'>".$nm_material."</td>";
										echo "<td align='right'>".number_format($valx2->weight,4)."</td>";
									echo "</tr>";
								}
							echo "</table>";
							echo "</td>";
							echo "</tr>";
						}
					}
					?>
			</tbody>
		</table>
		<br>
		<?php
		if(!empty($file_upload)){
			echo "<b>File Gambar: <a href='".base_url().$file_upload."' target='_blank'  title='Download'>(Download File)</a></b>";
		}
		?>
	</div>
</div>
