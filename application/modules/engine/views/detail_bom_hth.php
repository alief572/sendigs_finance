<?php
// print_r($header);
?>
<div class="box box-primary">
	<div class="box-body">
		<div class="form-group row">
			<div class="col-md-2">
				<label for="customer">Produk Name</label>
			</div>
			<div class="col-md-4">
				<select id="produk" name="produk" class="form-control input-md chosen-select" disabled>
					<option value="0">Select An Option</option>
					<?php foreach (get_product() as $val => $valx){
						$sel = ($product == $valx['id_category2'])?'selected':'';
					?>
					<option value="<?= $valx['id_category2'];?>" <?=$sel;?>><?= strtoupper(strtolower($valx['nama']))?></option>
					<?php } ?>
				</select>
			</div>
		</div>
		<div class='box box-info'>
			<div class='form-group row'>
				<div class='col-sm-6'>
					<div class='box-header'>
						<h3 class='box-title'>F-Tackle</h3>
						<div class='box-tool pull-right'>
						</div>
					</div>
					<div class='box-body hide_header  table-responsive'>
						<table class='table table-striped table-bordered table-hover table-condensed  table-responsive' width='100%'>
							<thead>
								<tr class='bg-blue'>
									<th class='text-center' style='width: 5%;'>#</th>
									<th class='text-center'>Group Material</th>
									<th class='text-center' style='width: 15%;'>Qty</th>
									<th class='text-center' style='width: 15%;'>Price</th>
									<th class='text-center' style='width: 15%;'>Total</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$id = 0;
								foreach($detail_header AS $val => $valx){ $id++;
									echo "<tr class='header_".$id."'>";
									 echo "<td align='center' style='vertical-align:middle;'>".$id."</td>";
									 echo "<td align='left' style='vertical-align:middle;'>";
										echo "<select name='Detail[".$id."][group_material]' class='chosen_select form-control input-sm inline-blockd group_material' disabled>";
										echo "<option value='0'>Select Group Material</option>";
										foreach($group_material AS $val2 => $valx2){
											$selc = ($valx['id_group_material'] == $valx2['id_group_material'])?'selected':'';
											echo "<option value='".$valx2['id_group_material']."' ".$selc.">".strtoupper($valx2['group_material'])."</option>";
										}
										echo "</select>";
									 echo "</td>";
										echo "<td align='left'>";
									 echo "</td>";
										echo "<td align='left'>";
									 echo "</td>";
									 echo "<td align='left'></td>";
								 echo "</tr>";

								 $detail_detail = $this->db->get_where('bom_hth_detail_detail',array('kode_bom_hth' => $no_bom, 'kode_bom_hth_detail' => $valx['kode_bom_hth_detail'], 'company' => 'f-tackle'))->result_array();
								 $no = 0;
								 foreach($detail_detail AS $val3 => $valx3){ $no++;
									 $material	= $this->db->query("SELECT
																									 b.code_material,
																									 b.weight,
																									 c.nm_material
																								 FROM
																									 bom_header a
																									 LEFT JOIN bom_detail b ON a.no_bom=b.no_bom
																									 LEFT JOIN ms_material c ON b.code_material=c.code_material
																								 WHERE
																									 a.id_product='".$product."'
																								 ")->result_array();
										 echo "<tr class='header_".$id."'>";
											 echo "<td align='center'></td>";
											 echo "<td align='left' style='vertical-align:middle; padding-left: 30px;'>";
											 echo "<input type='text' name='Detail[".$id."][detail][".$no."][material]' class='form-control input-sm' value='".strtoupper($valx3['id_material'])."' readonly>";
											 echo "</td>";
											 echo "<td align='left' style='vertical-align:middle;'>";
											 echo "<input type='text' name='Detail[".$id."][detail][".$no."][qty]' id='qty_".$id."_".$no."' value='".$valx3['qty']."' readonly class='form-control text-right input-md maskM qty_".$id." getTotal' placeholder='Qty'>";
											 echo "</td>";
											 echo "<td align='left' style='vertical-align:middle;'>";
											 echo "<input type='text' name='Detail[".$id."][detail][".$no."][price]' id='price_".$id."_".$no."' value='".$valx3['price']."' readonly class='form-control text-right  input-md maskM getTotal' placeholder='Price'>";
											 echo "</td>";
											 echo "<td align='left' style='vertical-align:middle;'>";
											 echo "<input type='text' name='Detail[".$id."][detail][".$no."][total]' id='total_".$id."_".$no."' value='".$valx3['total']."' readonly class='form-control text-right  input-md total_".$id."' placeholder='Total' readonly>";
											 echo "</td>";
										 echo "</tr>";
								 }
								 echo "<tr id='add_".$id."_".$no."' class='header_".$id."'>";
									 echo "<td align='center'></td>";
									 echo "<td align='left' style='vertical-align:middle;'></td>";
									 echo "<td align='left' style='vertical-align:middle;'>";
									 echo "<input type='text' name='Detail[".$id."][footer][".$no."][total_qty]' class='form-control text-right  input-md total_qty_".$id." total_qty' placeholder='Total Qty' readonly>";
									 echo "</td>";
									 echo "<td align='center'></td>";
									 echo "<td align='left' style='vertical-align:middle;'>";
									 echo "<input type='text' name='Detail[".$id."][footer][".$no."][total_total]' class='form-control text-right  input-md total_total_".$id." total_total' placeholder='Sub Total' readonly>";
									 echo "</td>";
								 echo "</tr>";

								}
								?>
								<tr id='add_<?=$id;?>'>
									<td align='center'></td>
									<td align='left'></td>
									<?php if($id > 0){
										echo "<td align='left' style='vertical-align:middle;'>";
										echo "<input type='text' name='total_qty' id='sub_qty' class='form-control text-right  input-md' placeholder='Total Qty' readonly>";
										echo "</td>";
									 echo "<td align='center'></td>";
										echo "<td align='left' style='vertical-align:middle;'>";
										echo "<input type='text' name='total_total' id='sub_total' class='form-control text-right  input-md' placeholder='Sub Total' readonly>";
										echo "</td>";
									} ?>
									<?php if($id < 1){?>
									<td align='center'></td>
									<td align='center'></td>
									<td align='center'></td>
								<?php } ?>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

				<div class='col-sm-6'>
					<div class='box-header'>
						<h3 class='box-title'>ORIGA</h3>
						<div class='box-tool pull-right'>
						</div>
					</div>
					<div class='box-body hide_header table-responsive'>
						<table class='table table-striped table-bordered table-hover table-condensed table-responsive' width='100%'>
							<thead>
								<tr class='bg-blue'>
									<th class='text-center' style='width: 5%;'>#</th>
									<th class='text-center'>Group Material</th>
									<th class='text-center' style='width: 15%;'>Qty</th>
									<th class='text-center' style='width: 15%;'>Price</th>
									<th class='text-center' style='width: 15%;'>Total</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$id = 0;
								foreach($detail_header2 AS $val => $valx){ $id++;
									echo "<tr class='header2_".$id."'>";
									 echo "<td align='center' style='vertical-align:middle;'>".$id."</td>";
									 echo "<td align='left' style='vertical-align:middle;'>";
										echo "<select name='Detail2[".$id."][group_material]' class='chosen_select form-control input-sm inline-blockd group_material' disabled>";
										echo "<option value='0'>Select Group Material</option>";
										foreach($group_material AS $val2 => $valx2){
											$selc = ($valx['id_group_material'] == $valx2['id_group_material'])?'selected':'';
											echo "<option value='".$valx2['id_group_material']."' ".$selc.">".strtoupper($valx2['group_material'])."</option>";
										}
										echo "</select>";
									 echo "</td>";
										echo "<td align='left'>";
									 echo "</td>";
										echo "<td align='left'>";
									 echo "</td>";
									 echo "<td align='left'></td>";
								 echo "</tr>";

								 $detail_detail = $this->db->get_where('bom_hth_detail_detail',array('kode_bom_hth' => $no_bom, 'kode_bom_hth_detail' => $valx['kode_bom_hth_detail'], 'company' => 'origa'))->result_array();
								 $no = 0;
								 foreach($detail_detail AS $val3 => $valx3){ $no++;
									 $weight	= $this->db->query("SELECT
																									 b.weight
																								 FROM
																									 bom_header a
																									 LEFT JOIN bom_detail b ON a.no_bom=b.no_bom
																								 WHERE
																									 a.id_product='".$product."'
																									 AND b.code_material='".$valx3['id_material']."'
																								 LIMIT 1
																								 ")->result();
									 $get_price	= $this->db->query("SELECT
																							 b.rate
																						 FROM
																							 price_ref b
																						 WHERE
																							 b.code='".$valx3['id_material']."'
																							 AND category = 'material'
																						 LIMIT 1
																						 ")->result();
									 $qty = (!empty($weight[0]->weight))?$weight[0]->weight:0;
									 $price = (!empty($get_price[0]->rate))?$get_price[0]->rate:0;

									 echo "<tr class='header2_".$id."'>";
										 echo "<td align='center'></td>";
										 echo "<td align='left' style='vertical-align:middle; padding-left: 30px;'>";
										 echo "<select name='Detail2[".$id."][detail][".$no."][material]' data-no1='".$id."' data-no2='".$no."' class='chosen_select form-control input-sm inline-blockd material process2' disabled>";
										 echo "<option value='0'>Select Material Name</option>";
										 foreach($material AS $val4 => $valx4){
											 $selc2 = ($valx3['id_material'] == $valx4['code_material'])?'selected':'';
											 echo "<option value='".$valx4['code_material']."' ".$selc2.">".strtoupper($valx4['nm_material'])."</option>";
										 }
										 echo 		"</select>";
										 echo "</td>";
										 echo "<td align='left' style='vertical-align:middle;'>";
										 echo "<input type='text' name='Detail2[".$id."][detail][".$no."][qty]' id='qty2_".$id."_".$no."' value='".$valx3['qty']."' class='form-control text-right input-md maskM qty2_".$id."' placeholder='Qty' readonly>";
										 echo "</td>";
										 echo "<td align='left' style='vertical-align:middle;'>";
										 echo "<input type='text' name='Detail2[".$id."][detail][".$no."][price]' id='price2_".$id."_".$no."' value='".$valx3['price']."' class='form-control text-right  input-md maskM' placeholder='Price' readonly>";
										 echo "</td>";
										 echo "<td align='left' style='vertical-align:middle;'>";
										 echo "<input type='text' name='Detail2[".$id."][detail][".$no."][total]' id='total2_".$id."_".$no."' value='".$valx3['total']."' class='form-control text-right  input-md total2_".$id."' placeholder='Total' readonly>";
										 echo "</td>";
									 echo "</tr>";
								 }
								 echo "<tr id='add2_".$id."_".$no."' class='header2_".$id."'>";
									 echo "<td align='center'></td>";
									 echo "<td align='left' style='vertical-align:middle;'></td>";
									 echo "<td align='left' style='vertical-align:middle;'>";
									 echo "<input type='text' name='Detail2[".$id."][footer][".$no."][total_qty]' class='form-control text-right  input-md total_qty2_".$id." total_qty2' placeholder='Total Qty' readonly>";
									 echo "</td>";
									 echo "<td align='center'></td>";
									 echo "<td align='left' style='vertical-align:middle;'>";
									 echo "<input type='text' name='Detail2[".$id."][footer][".$no."][total_total]' class='form-control text-right  input-md total_total2_".$id." total_total2' placeholder='Sub Total' readonly>";
									 echo "</td>";
								 echo "</tr>";
								}
								?>
								<tr id='add2_<?=$id;?>'>
									<td align='center'></td>
									<td align='left'></td>
									<?php if($id > 0){
										echo "<td align='left' style='vertical-align:middle;'>";
										echo "<input type='text' name='total_qty2' id='sub_qty2' class='form-control text-right  input-md' placeholder='Total Qty' readonly>";
										echo "</td>";
									 echo "<td align='center'></td>";
										echo "<td align='left' style='vertical-align:middle;'>";
										echo "<input type='text' name='total_total2' id='sub_total2' class='form-control text-right  input-md' placeholder='Sub Total' readonly>";
										echo "</td>";
									} ?>
									<?php if($id < 1){?>
									<td align='center'></td>
									<td align='center'></td>
									<td align='center'></td>
								<?php } ?>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
$(document).ready(function(){
	// $('.chosen-select').select2({width: '100%'});
	// $('.chosen_select').select2({width: '100%'});
	var a;
	for(a=0; a <= 50; a++){
		get_summary(a);
		get_summary2(a);
	}
});
</script>
