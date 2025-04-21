<?php
$code_lv4 = $getData[0]['code_lv4'];
$nama_product = $getData[0]['nama'];
$qty_order = $getHeader[0]['propose'];
$id_customer = $getHeader[0]['id_customer'];
$project = $getHeader[0]['project'];
$so_customer = $getHeader[0]['so_customer'];
$id_uniq = $getHeader[0]['id'];
$no_bom = $getHeader[0]['no_bom'];
$propose = $getHeader[0]['propose'];

// $no_bom = (!empty($getDataBOM[0]['no_bom']))?$getDataBOM[0]['no_bom']:0;

// $keyNew = $code_lv4.'-'.$no_bom;
// $actual_stock = (!empty($getStockProduct[$keyNew]['stock']))?$getStockProduct[$keyNew]['stock']:0;
// $booking_stock = (!empty($getStockProduct[$keyNew]['booking']))?$getStockProduct[$keyNew]['booking']:0;
// $free_stock = $actual_stock - $booking_stock;
// $free_stock = ($free_stock > 0)?$free_stock:0;

// $sisa_stock = $free_stock;
// $on_process_spk = 0;
// $on_process_fg = $sisa_stock + $on_process_spk;

// $min_stock 	= (!empty($getProductLv4[$code_lv4]['min_stock']))?$getProductLv4[$code_lv4]['min_stock']:0;
// $moq 		= (!empty($getProductLv4[$code_lv4]['moq']))?$getProductLv4[$code_lv4]['moq']:0;

// $propose	= 0;
// if($free_stock < $min_stock){
// 	$propose = $moq;
// }
?>
<div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post" autocomplete="off"><br>
        
      	<div class="form-group row">
        	<div class="col-md-1">
				<label for="customer">Product Name</label>
			</div>
			<div class="col-md-9">
                <input type="hidden" name='code_lv4' id='code_lv4' value='<?=$code_lv4;?>'>
                <input type="text" name='nama_product' id='nama_product' class='form-control' value='<?=$nama_product;?>' readonly>
                <input type="hidden" name='id_customer' id='id_customer' class='form-control' value='<?=$id_customer;?>' readonly>
                <input type="hidden" name='project' id='project' class='form-control' value='<?=$project;?>' readonly>
                <input type="hidden" name='so_customer' id='so_customer' class='form-control' value='<?=$so_customer;?>' readonly>
                <input type="hidden" name='id_uniq' id='id_uniq' class='form-control' value='<?=$id_uniq;?>' readonly>
                <input type="hidden" name='no_bom' id='no_bom' class='form-control' value='<?=$no_bom;?>' readonly>
        	</div>
        </div>
        <div class="form-group row">
            <div class="col-md-1">
                <label for="customer">Propose</label>
            </div>
            <div class="col-md-1">
            <input type="text" name='propose' class='form-control text-center autoNumeric0' value='<?=$propose;?>' readonly>
            </div>
        </div>
        <h4 class='box-title'>A. BOM Single Product</h4>
        <?php
        $val = 0;
        if(!empty($detail_hi_grid)){
            foreach($detail_hi_grid AS $val => $valx){ $val++;

                $getDataBOM = $this->db
                        ->select('a.*,b.nama AS nm_product')
                        ->where_in('a.category',$WhereIN)
                        ->join('new_inventory_4 b','a.id_product=b.code_lv4','left')
                        ->get_where('bom_header a',array('a.no_bom'=>$valx['code_material'],'a.deleted_date'=>NULL))->result_array();
                ?>
                <div class="form-group row">
                    <div class="col-md-1 text-right">
                        
                    </div>
                    <div class="col-md-11">
						<label for="customer">Single Product <?=$val;?> <span class='text-red'>*</span></label>
						<select name='single_product[<?=$val;?>][no_bom]' class='form-control chosen-select'>
							<?php
								if(!empty($getDataBOM)){
									foreach ($getDataBOM as $key => $value) {
										// if($value['no_bom'] == $valx['code_material']){
											$namaBOM = (!empty($getNameBOMProduct[$value['no_bom']]))?$getNameBOMProduct[$value['no_bom']]:'';
											echo "<option value='".$value['no_bom']."'>".strtoupper($namaBOM)."</option>";
										// }
									}
								}
								else{
									echo "<option value='0'>BOM Belum dibuat !!!</option>";
								}
							?>
						</select>
						<span class='text-bold text-primary detail' data-id='<?=$valx['code_material'];?>' data-category='<?=$value['category'];?>' style='cursor:pointer;'>Tampilkan BOM</span>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-1 text-right">
                        
                    </div>
                    <div class="col-md-1">
						<label for="customer">Qty Single Product</label>
						<input type="text" name='single_product[<?=$val;?>][qty]' class='form-control text-center autoNumeric0' value='<?=$valx['qty']*$propose;?>' readonly>
                    </div>
                    <div class="col-md-2">
						<label for="customer">Due Date <span class='text-red'>*</span></label>
                        <input type="text" name='single_product[<?=$val;?>][due_date]' class='form-control text-center datepicker' style='background:transparent;' readonly>
                        <input type="hidden" name='single_product[<?=$val;?>][code_lv4]' value='<?=$getDataBOM[0]['id_product'];?>'>
                        <input type="hidden" name='single_product[<?=$val;?>][nama_product]' value='<?=$getDataBOM[0]['nm_product'];?>'>
                    </div>
                    <div class="col-md-1">
						<label for="customer">Propose <span class='text-red'>*</span></label>
						<input type="text" name='single_product[<?=$val;?>][propose]' class='form-control text-center autoNumeric0' value=''>
                    </div>
                </div>
				<hr>
            <?php
                }
            }
        ?>
		<h4 class='box-title'>B. Cutting Plan</h4>
        <?php
        $val = 0;
        if(!empty($detail_hi_grid)){
            foreach($detail_hi_grid AS $val => $valx){ $val++;

                $getDataBOM = $this->db
                        ->select('a.*,b.nama AS nm_product')
                        ->where_in('a.category',$WhereIN)
                        ->join('new_inventory_4 b','a.id_product=b.code_lv4','left')
                        ->get_where('bom_header a',array('a.no_bom'=>$valx['code_material'],'a.deleted_date'=>NULL))->result_array();
                ?>
                <div class="form-group row">
                    <div class="col-md-1 text-right">
                        
                    </div>
                    <div class="col-md-11">
						<label for="customer">Single Product <?=$val;?></label>
						<input type="text" name='cutting_plan[<?=$val;?>][nama_product]' class='form-control' value='<?=$getDataBOM[0]['nm_product'];?>' readonly>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-1 text-right">
                        
                    </div>
                    <div class="col-md-1">
						<label for="customer">Qty Single Product</label>
						<input type="text" name='cutting_plan[<?=$val;?>][qty]' class='form-control text-center autoNumeric0' value='<?=$valx['qty']*$propose;?>' readonly>
                    </div>
                    <div class="col-md-2">
						<label for="customer">Due Date Cutting <span class='text-red'>*</span></label>
                        <input type="text" name='cutting_plan[<?=$val;?>][due_date]' class='form-control text-center datepicker' style='background:transparent;' readonly>
                        <input type="hidden" name='cutting_plan[<?=$val;?>][code_lv4]' value='<?=$getDataBOM[0]['id_product'];?>'>
                        <input type="hidden" name='cutting_plan[<?=$val;?>][no_bom]' value='<?=$getDataBOM[0]['no_bom'];?>'>
                    </div>
                </div>
				<div class="form-group row">
                    <div class="col-md-1 text-right">
                        
                    </div>
                    <div class="col-md-8">
						<label for="customer">Cutting Plan</label>
						<?php
						$resultUkuranJadi = $this->db->get_where('bom_detail_custom',array('category'=>'ukuran jadi','no_bom_detail'=>$valx['no_bom_detail']))->result_array();
						$no = 0;
						echo "<table class='table table-bordered table-hover table-condensed' width='100%'>";
						foreach($resultUkuranJadi AS $val2D => $val2Dx){ $no++;
							echo "<tr>";
								echo "<td align='left'>";
									echo "<input type='hidden' name='cutting_plan[".$val."][ukuran_jadi][".$no."][id]' value='".$val2Dx['id']."'>";
									echo "<div class='input-group'>";
										echo "<span class='input-group-addon' style='background: bisque;'>Length :</span>";
										echo "<input type='text' name='cutting_plan[".$val."][ukuran_jadi][".$no."][length]' class='form-control input-md autoNumeric' value='".$val2Dx['length']."' style='z-index:auto;' readonly>";
										echo "<span class='input-group-addon' style='background: bisque;'>Width :</span>";
										echo "<input type='text' name='cutting_plan[".$val."][ukuran_jadi][".$no."][width]' class='form-control input-md autoNumeric' value='".$val2Dx['width']."' style='z-index:auto;' readonly>";
										echo "<span class='input-group-addon' style='background: bisque;'>Qty :</span>";
										echo "<input type='text' name='cutting_plan[".$val."][ukuran_jadi][".$no."][qty]' class='form-control input-md autoNumeric' value='".$val2Dx['qty']."' style='z-index:auto;' readonly>";
										echo "<span class='input-group-addon' style='background: bisque;'>Meter Lari :</span>";
										echo "<input type='text' name='cutting_plan[".$val."][ukuran_jadi][".$no."][lari]' class='form-control input-md autoNumeric' value='".$val2Dx['lari']."' style='z-index:auto;' readonly>";
									echo "</div>";
								echo "</td>";
							echo "</tr>";
						}
						echo "</table>";
						?>
                    </div>
                </div>
				<div class="form-group row">
                    <div class="col-md-1 text-right">
                        
                    </div>
                    <div class="col-md-8">
						<label for="customer">Material Cutting</label>
						<?php
						//cutting material
						$resultCuttingMaterial = $this->db->get_where('bom_detail_custom',array('category'=>'material cutting','no_bom_detail'=>$valx['no_bom_detail']))->result_array();
						$materialList    = get_list_inventory_lv4('material');
						$no = 0;
						echo "<table class='table table-bordered table-hover table-condensed' width='100%'>";
						foreach($resultCuttingMaterial AS $val2D => $val2Dx){ $no++;
							echo "<tr class='headerhigrid_".$val."'>";
								echo "<td align='left'>";
									echo "<select name='cutting_plan[".$val."][cutting][".$no."][id_materialx]' data-id='".$val."' class='chosen-select form-control input-sm inline-blockd' style='width:100%' disabled>";
									echo "<option value='0'>Select Material</option>";
									foreach($materialList AS $valx => $value){
										$selected = ($value['code_lv4'] == $val2Dx['code_material'])?'selected':'';
										echo "<option value='".$value['code_lv4']."' ".$selected.">".strtoupper($value['nama'])."</option>";
									}
									echo "</select>";
								echo "</td>";
								echo "<td align='left' width='20%'>";
									echo "<input type='hidden' name='cutting_plan[".$val."][cutting][".$no."][id_material]' value='".$val2Dx['code_material']."'>";
									echo "<input type='hidden' name='cutting_plan[".$val."][cutting][".$no."][id]' value='".$val2Dx['id']."'>";
									echo "<div class='input-group'>";
										echo "<input type='text' name='cutting_plan[".$val."][cutting][".$no."][weight]' class='form-control input-md text-center autoNumeric4 qty' placeholder='Qty' readonly value='".$val2Dx['weight']."'>";
										echo "<span class='input-group-addon'>Kg</span>";
									echo "</div>";
								echo "</td>";
							echo "</tr>";
						}
						echo "</table>";
						?>
                    </div>
                </div>
				<hr>
            <?php
                }
            }
        ?>

		<h4 class='box-title'>C. Accessories</h4>
		<div class="form-group row">
			<div class="col-md-1"></div>
			<div class="col-md-8">
				<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
					<thead>
						<tr class='bg-blue'>
							<th class='text-center' width='5%'>#</th>
							<th class='text-center'>Accessories Name</th>
							<th class='text-center' width='12%'>Qty</th>
							<th class='text-center' width='25%'>Keterangan</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$val = 0;
						if(!empty($detail_accessories)){
							foreach($detail_accessories AS $val => $valx){ $val++;
								echo "<tr>";
									echo "<td align='center'>".$val."</td>";
									echo "<td align='left'>";
										echo "<select name='DetailAcc[".$val."][code_materialx]' class='chosen-select form-control input-sm inline-blockd' disabled>";
										echo "<option value='0'>Select Accessories</option>";
										foreach($accessories AS $valx4){
											$sel2 = ($valx4->id == $valx['code_material'])?'selected':'';
											echo "<option value='".$valx4->id."' ".$sel2.">".strtoupper($valx4->stock_name.' '.$valx4->brand.' '.$valx4->spec)."</option>";
										}
										echo "</select>";
									echo "</td>";
									echo "<td align='left'>";
										echo "<input type='hidden' name='DetailAcc[".$val."][id]' value='".$valx['id']."'>";
										echo "<input type='hidden' name='DetailAcc[".$val."][code_material]' value='".$valx['code_material']."'>";
										echo "<input type='text' name='DetailAcc[".$val."][weight]' class='form-control input-md autoNumeric4 qty text-center' placeholder='Qty' value='".$valx['weight']."' readonly>";
									echo "</td>";
									echo "<td align='left'>";
										echo "<input type='text' name='DetailAcc[".$val."][ket]' class='form-control input-md' placeholder='Keterangan' value='".$valx['ket']."' readonly>";
									echo "</td>";
								echo "</tr>";
							}
						}
						?>
					</tbody>
				</table>
			</div>
		</div>
		<h4 class='box-title'>D. Material Joint & Finishing</h4>
		<div class="form-group row">
			<div class="col-md-1"></div>
			<div class="col-md-8">
				<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
					<thead>
						<tr class='bg-blue'>
							<th class='text-center' width='4%'>#</th>
							<th class='text-center' width='12%'>Layer</th>
							<th class='text-center'>Material Name</th>
							<th class='text-center' width='12%'>Qty</th>
							<th class='text-center' width='25%'>Keterangan</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$val = 0;
						if(!empty($detail_mat_joint)){
							foreach($detail_mat_joint AS $val => $valx){ $val++;
								echo "<tr class='headermatjoint_".$val."'>";
								echo "<td align='center'>".$val."</td>";
								echo "<td align='left'>";
									echo "<input type='text' name='DetailMatJoint[".$val."][layer]' class='form-control input-md' readonly placeholder='Layer' value='".$valx['layer']."'>";
								echo "</td>";
								echo "<td align='left'>";
								echo "<select name='DetailMatJoint[".$val."][code_materialx]' class='chosen-select form-control input-sm inline-blockd' disabled>";
								echo "<option value='0'>Select Material</option>";
								foreach($material AS $valx4){
									$sel2 = ($valx4->code_lv4 == $valx['code_material'])?'selected':'';
									echo "<option value='".$valx4->code_lv4."' ".$sel2.">".strtoupper($valx4->nama)."</option>";
								}
								echo 		"</select>";
								echo "</td>";
								echo "<td align='left'>";
								echo "<input type='hidden' name='DetailMatJoint[".$val."][id]' value='".$valx['id']."'>";
								echo "<input type='text' name='DetailMatJoint[".$val."][weight]' class='form-control input-md text-center autoNumeric4 qty' placeholder='Qty' readonly value='".$valx['weight']."'>";
								echo "</td>";
								echo "<td align='left'>";
									echo "<input type='hidden' name='DetailMatJoint[".$val."][code_material]' value='".$valx['code_material']."'>";
									echo "<input type='text' name='DetailMatJoint[".$val."][ket]' class='form-control input-md' placeholder='Keterangan' readonly value='".$valx['ket']."'>";
								echo "</td>";
								echo "</tr>";
							}
						}
						?>
					</tbody>
				</table>
			</div>
		</div>
		<h4 class='box-title'>E. Flat Sheet</h4>
		<div class="form-group row">
			<div class="col-md-1"></div>
			<div class="col-md-8">
				<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
					<thead>
						<tr class='bg-blue'>
							<th class='text-center' style='width: 4%;'>#</th>
							<th class='text-center' style='width: 23%;'>Length</th>
							<th class='text-center' style='width: 23%;'>Width</th>
							<th class='text-center' style='width: 23%;'>Qty</th>
							<th class='text-center' style='width: 23%;'>M2</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$val = 0;
						if(!empty($detail_flat_sheet)){
							foreach($detail_flat_sheet AS $val => $valx){ $val++;
								echo "<tr class='headerflatsheet_".$val."'>";
								echo "<td align='center'>".$val."</td>";
								echo "<td align='left'>";
								echo "<input type='hidden' name='DetailFlat[".$val."][id]' value='".$valx['id']."'>";
								echo "<input type='text' name='DetailFlat[".$val."][length]' class='form-control input-md text-center autoNumeric4 length changeFlat' placeholder='Length' value='".$valx['length']."' readonly>";
								echo "</td>";
								echo "<td align='left'>";
								echo "<input type='text' name='DetailFlat[".$val."][width]' class='form-control input-md text-center autoNumeric4 width changeFlat' placeholder='Width' value='".$valx['width']."' readonly>";
								echo "</td>";
								echo "<td align='left'>";
								echo "<input type='text' name='DetailFlat[".$val."][qty]' class='form-control input-md text-center autoNumeric4' placeholder='Qty' value='".$valx['qty']."' readonly>";
								echo "</td>";
								echo "<td align='left'>";
								echo "<input type='text' name='DetailFlat[".$val."][m2]' class='form-control input-md text-center autoNumeric4 resultM2' placeholder='M2' readonly value='".$valx['m2']."' readonly>";
								echo "</td>";
								echo "</tr>";

								//material flat sheet
								$resultCuttingMaterial = $this->db->get_where('bom_detail_custom',array('category'=>'material flat sheet','no_bom_detail'=>$valx['no_bom_detail']))->result_array();
								$materialList    = get_list_inventory_lv4('material');
								$no = 0;
								foreach($resultCuttingMaterial AS $val2D => $val2Dx){ $no++;
									echo "<tr class='headerflatsheet_".$val."'>";
										echo "<td align='center'></td>";
										echo "<td align='left' colspan='2'>";
											echo "<select name='DetailFlat[".$val."][material][".$no."][id_materialx]' data-id='".$val."' class='chosen-select form-control input-sm inline-blockd' disabled>";
											echo "<option value='0'>Select Material</option>";
											foreach($materialList AS $valx => $value){
												$selected = ($value['code_lv4'] == $val2Dx['code_material'])?'selected':'';
												echo "<option value='".$value['code_lv4']."' ".$selected.">".strtoupper($value['nama'])."</option>";
											}
											echo "</select>";
										echo "</td>";
										echo "<td align='left'>";
											echo "<input type='hidden' name='DetailFlat[".$val."][material][".$no."][id_material]' value='".$val2Dx['code_material']."'>";
											echo "<input type='hidden' name='DetailFlat[".$val."][material][".$no."][id]' value='".$val2Dx['id']."'>";
											echo "<input type='text' name='DetailFlat[".$val."][material][".$no."][weight]' class='form-control input-md text-center autoNumeric4 qty' placeholder='Qty' value='".$val2Dx['weight']."' readonly>";
										echo "</td>";
										echo "<td align='left'></td>";
									echo "</tr>";
								}
							}
						}
						?>
					</tbody>
				</table>
			</div>
		</div>
		<h4 class='box-title'>F. End Plate / Kick Plate</h4>
		<div class="form-group row">
			<div class="col-md-1"></div>
			<div class="col-md-8">
				<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
					<thead>
						<tr class='bg-blue'>
							<th class='text-center' style='width: 4%;'>#</th>
							<th class='text-center' style='width: 23%;'>Length</th>
							<th class='text-center' style='width: 23%;'>Height</th>
							<th class='text-center' style='width: 23%;'>Qty</th>
							<th class='text-center' style='width: 23%;'>M2</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$val = 0;
						if(!empty($detail_end_plate)){
							foreach($detail_end_plate AS $val => $valx){ $val++;
								echo "<tr class='headerendplate_".$val."'>";
								echo "<td align='center'>".$val."</td>";
								echo "<td align='left'>";
								echo "<input type='hidden' name='DetailEnd[".$val."][id]' value='".$valx['id']."'>";
								echo "<input type='text' name='DetailEnd[".$val."][length]' class='form-control input-md text-center autoNumeric4 length changeEnd' placeholder='Length' value='".$valx['length']."' readonly>";
								echo "</td>";
								echo "<td align='left'>";
								echo "<input type='text' name='DetailEnd[".$val."][width]' class='form-control input-md text-center autoNumeric4 width changeEnd' placeholder='Height' value='".$valx['width']."' readonly>";
								echo "</td>";
								echo "<td align='left'>";
								echo "<input type='text' name='DetailEnd[".$val."][qty]' class='form-control input-md text-center autoNumeric4' placeholder='Qty' value='".$valx['qty']."' readonly>";
								echo "</td>";
								echo "<td align='left'>";
								echo "<input type='text' name='DetailEnd[".$val."][m2]' class='form-control input-md text-center autoNumeric4 resultM2' placeholder='M2' readonly value='".$valx['m2']."' readonly>";
								echo "</td>";
								echo "</tr>";

								//material end plate
								$resultCuttingMaterial = $this->db->get_where('bom_detail_custom',array('category'=>'material end plate','no_bom_detail'=>$valx['no_bom_detail']))->result_array();
								$materialList    = get_list_inventory_lv4('material');
								$no = 0;
								foreach($resultCuttingMaterial AS $val2D => $val2Dx){ $no++;
									echo "<tr class='headerendplate_".$val."'>";
										echo "<td align='center'></td>";
										echo "<td align='left' colspan='2'>";
											echo "<select name='DetailEnd[".$val."][material][".$no."][id_materialx]' data-id='".$val."' class='chosen-select form-control input-sm inline-blockd'  disabled>";
											echo "<option value='0'>Select Material</option>";
											foreach($materialList AS $valx => $value){
												$selected = ($value['code_lv4'] == $val2Dx['code_material'])?'selected':'';
												echo "<option value='".$value['code_lv4']."' ".$selected.">".strtoupper($value['nama'])."</option>";
											}
											echo "</select>";
										echo "</td>";
										echo "<td align='left'>";
											echo "<input type='hidden' name='DetailEnd[".$val."][material][".$no."][id_material]' value='".$val2Dx['code_material']."'>";
											echo "<input type='hidden' name='DetailEnd[".$val."][material][".$no."][id]' value='".$val2Dx['id']."'>";
											echo "<input type='text' name='DetailEnd[".$val."][material][".$no."][weight]' class='form-control input-md text-center autoNumeric4 qty' placeholder='Qty' value='".$val2Dx['weight']."' readonly>";
										echo "</td>";
										echo "<td align='left'></td>";
									echo "</tr>";
								}
							}
						}
						?>
					</tbody>
				</table>
			</div>
		</div>
		<h4 class='box-title'>G. Chequered Plate</h4>
		<div class="form-group row">
			<div class="col-md-1"></div>
			<div class="col-md-8">
				<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
					<thead>
						<tr class='bg-blue'>
							<th class='text-center' style='width: 4%;'>#</th>
							<th class='text-center' style='width: 23%;'>Length</th>
							<th class='text-center' style='width: 23%;'>Width</th>
							<th class='text-center' style='width: 23%;'>Qty</th>
							<th class='text-center' style='width: 23%;'>M2</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$val = 0;
						if(!empty($detail_ukuran_jadi)){
							foreach($detail_ukuran_jadi AS $val => $valx){ $val++;
								echo "<tr class='headerukuranjadi_".$val."'>";
								echo "<td align='center'>".$val."</td>";
								echo "<td align='left'>";
								echo "<input type='hidden' name='DetailJadi[".$val."][id]' value='".$valx['id']."'>";
								echo "<input type='text' name='DetailJadi[".$val."][length]' class='form-control input-md text-center autoNumeric4 length changeEnd' placeholder='Length' value='".$valx['length']."' readonly>";
								echo "</td>";
								echo "<td align='left'>";
								echo "<input type='text' name='DetailJadi[".$val."][width]' class='form-control input-md text-center autoNumeric4 width changeEnd' placeholder='Width' value='".$valx['width']."' readonly>";
								echo "</td>";
								echo "<td align='left'>";
								echo "<input type='text' name='DetailJadi[".$val."][qty]' class='form-control input-md text-center autoNumeric4' placeholder='Qty' value='".$valx['qty']."' readonly>";
								echo "</td>";
								echo "<td align='left'>";
								echo "<input type='text' name='DetailJadi[".$val."][m2]' class='form-control input-md text-center autoNumeric4 resultM2' placeholder='M2' readonly value='".$valx['m2']."'>";
								echo "</td>";
								echo "</tr>";

								//material end plate
								$resultCuttingMaterial = $this->db->get_where('bom_detail_custom',array('category'=>'material ukuran jadi','no_bom_detail'=>$valx['no_bom_detail']))->result_array();
								$materialList    = get_list_inventory_lv4('material');
								$no = 0;
								foreach($resultCuttingMaterial AS $val2D => $val2Dx){ $no++;
									echo "<tr class='headerukuranjadi_".$val."'>";
										echo "<td align='center'></td>";
										echo "<td align='left' colspan='2'>";
											echo "<select name='DetailJadi[".$val."][material][".$no."][id_materialx]' data-id='".$val."' class='chosen-select form-control input-sm inline-blockd' disabled>";
											echo "<option value='0'>Select Material</option>";
											foreach($materialList AS $valx => $value){
												$selected = ($value['code_lv4'] == $val2Dx['code_material'])?'selected':'';
												echo "<option value='".$value['code_lv4']."' ".$selected.">".strtoupper($value['nama'])."</option>";
											}
											echo "</select>";
										echo "</td>";
										echo "<td align='left'>";
											echo "<input type='hidden' name='DetailJadi[".$val."][material][".$no."][id_material]' value='".$val2Dx['code_material']."'>";
											echo "<input type='hidden' name='DetailJadi[".$val."][material][".$no."][id]' value='".$val2Dx['id']."'>";
											echo "<input type='text' name='DetailJadi[".$val."][material][".$no."][weight]' class='form-control input-md text-center autoNumeric4 qty' placeholder='Qty' value='".$val2Dx['weight']."' readonly>";
										echo "</td>";
										echo "<td align='left'></td>";
									echo "</tr>";
								}
							}
						}
						?>
					</tbody>
				</table>
			</div>
		</div>
		<h4 class='box-title'>H. Others</h4>
		<div class="form-group row">
			<div class="col-md-1"></div>
			<div class="col-md-8">
				<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
					<thead>
						<tr class='bg-blue'>
							<th class='text-center' style='width: 4%;'>#</th>
							<th class='text-center' style='width: 23%;'>Length</th>
							<th class='text-center' style='width: 23%;'>Width</th>
							<th class='text-center' style='width: 23%;'>Qty</th>
							<th class='text-center' style='width: 23%;'>M2</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$val = 0;
						if(!empty($detail_others)){
							foreach($detail_others AS $val => $valx){ $val++;
								echo "<tr class='headerothers_".$val."'>";
								echo "<td align='center'>".$val."</td>";
								echo "<td align='left'>";
								echo "<input type='hidden' name='DetailOthers[".$val."][id]' value='".$valx['id']."'>";
								echo "<input type='text' name='DetailOthers[".$val."][length]' class='form-control input-md text-center autoNumeric4 length changeEnd' placeholder='Length' value='".$valx['length']."' readonly>";
								echo "</td>";
								echo "<td align='left'>";
								echo "<input type='text' name='DetailOthers[".$val."][width]' class='form-control input-md text-center autoNumeric4 width changeEnd' placeholder='Width' value='".$valx['width']."' readonly>";
								echo "</td>";
								echo "<td align='left'>";
								echo "<input type='text' name='DetailOthers[".$val."][qty]' class='form-control input-md text-center autoNumeric4' placeholder='Qty' value='".$valx['qty']."' readonly>";
								echo "</td>";
								echo "<td align='left'>";
								echo "<input type='text' name='DetailOthers[".$val."][m2]' class='form-control input-md text-center autoNumeric4 resultM2' placeholder='M2' readonly value='".$valx['m2']."'>";
								echo "</td>";
								echo "</tr>";

								//material end plate
								$resultCuttingMaterial = $this->db->get_where('bom_detail_custom',array('category'=>'material others','no_bom_detail'=>$valx['no_bom_detail']))->result_array();
								$materialList    = get_list_inventory_lv4('material');
								$no = 0;
								foreach($resultCuttingMaterial AS $val2D => $val2Dx){ $no++;
									echo "<tr class='headerothers_".$val."'>";
										echo "<td align='center'></td>";
										echo "<td align='left' colspan='2'>";
											echo "<select name='DetailOthers[".$val."][material][".$no."][id_material]' data-id='".$val."' class='chosen-select form-control input-sm inline-blockd' disabled>";
											echo "<option value='0'>Select Material</option>";
											foreach($materialList AS $valx => $value){
												$selected = ($value['code_lv4'] == $val2Dx['code_material'])?'selected':'';
												echo "<option value='".$value['code_lv4']."' ".$selected.">".strtoupper($value['nama'])."</option>";
											}
											echo "</select>";
										echo "</td>";
										echo "<td align='left'>";
											echo "<input type='hidden' name='DetailOthers[".$val."][material][".$no."][id_material]' value='".$val2Dx['code_material']."'>";
											echo "<input type='hidden' name='DetailOthers[".$val."][material][".$no."][id]' value='".$val2Dx['id']."'>";
											echo "<input type='text' name='DetailOthers[".$val."][material][".$no."][weight]' class='form-control input-md text-center autoNumeric4 qty' placeholder='Qty' value='".$val2Dx['weight']."' readonly>";
										echo "</td>";
										echo "<td align='left'></td>";
									echo "</tr>";
								}
							}
						}
						?>
					</tbody>
				</table>
			</div>
		</div>
		<div class="form-group row">
        	<div class="col-md-1">
				<label for="customer"></label>
			</div>
			<div class="col-md-11">
				<button type="button" class="btn btn-primary" name="save" id="save">Release SO</button>
				<button type="button" class="btn btn-danger" style='margin-left:5px;' name="back" id="back">Back</button>
			</div>
        </div>

      	
		</form>
	</div>
</div>

<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" style='width:70%;'>
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;Detail Data</h4>
      </div>
      <div class="modal-body" id="ModalView">
		...
      </div>
  </div>
</div>


<script src="<?= base_url('assets/js/jquery.maskMoney.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>
<style>
    .datepicker{
        cursor: pointer;
    }
</style>

<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';

	$(document).ready(function(){
		$('.datepicker').datepicker({ dateFormat: 'dd-M-yy' });
    	$('.autoNumeric0').autoNumeric('init', {mDec: '0', aPad: false})
    	$('.autoNumeric4').autoNumeric('init', {mDec: '4', aPad: false})
    	$('.chosen-select').select2()

    	//back
		$(document).on('click', '#back', function(){
		    window.location.href = base_url + active_controller
		});

		$(document).on('click', '.detail', function(){
			var no_bom = $(this).data('id');
			var category = $(this).data('category');
			let controller_category = '';
			if(category == 'standard'){
				controller_category = 'bom';
			}
			if(category == 'topping'){
				controller_category = 'bom_topping';
			}
			if(category == 'grid standard'){
				controller_category = 'bom_hi_grid_standard';
			}
			if(category == 'grid custom'){
				controller_category = 'bom_hi_grid_custom';
			}
			if(category == 'ftackel'){
				controller_category = 'bom_ftackel';
			}
			// alert(id);
			$("#head_title").html("<b>Detail Bill Of Material</b>");
			$.ajax({
				type:'POST',
				url: base_url + controller_category + '/detail/',
				data:{
					'no_bom':no_bom,
				},
				success:function(data){
					$("#dialog-popup").modal();
					$("#ModalView").html(data);

				}
			})
		});

		$('#save').click(function(e){
			e.preventDefault();
			var due_date = $("#due_date").val();
			var propose = $("#propose").val();
			var no_bom = $("#no_bom").val();

			if(no_bom == '0'){
				swal({title	: "Error Message!",text	: 'BOM belum dibuat...',type	: "warning"
				});
				$('#save').prop('disabled',false); return false;
			}

      		if(due_date == '' ){
				swal({title	: "Error Message!",text	: 'Due date empty, select first ...',type	: "warning"
				});
				$('#save').prop('disabled',false); return false;
			}

            if(propose == '' || propose <= 0 ){
				swal({title	: "Error Message!",text	: 'Propose empty, select first ...',type	: "warning"
				});
				$('#save').prop('disabled',false); return false;
			}

			swal({
				  title: "Are you sure?",
				  text: "You will not be able to process again this data!",
				  type: "warning",
				  showCancelButton: true,
				  confirmButtonClass: "btn-danger",
				  confirmButtonText: "Yes, Process it!",
				  cancelButtonText: "No, cancel process!",
				  closeOnConfirm: true,
				  closeOnCancel: false
				},
				function(isConfirm) {
				  if (isConfirm) {
						var formData 	=new FormData($('#data-form')[0]);
						var baseurl=siteurl+active_controller+'/spk_stok_custom';
						$.ajax({
							url			: baseurl,
							type		: "POST",
							data		: formData,
							cache		: false,
							dataType	: 'json',
							processData	: false,
							contentType	: false,
							success		: function(data){
								if(data.status == 1){
									swal({
										  title	: "Save Success!",
										  text	: data.pesan,
										  type	: "success",
										  timer	: 7000
										});
									window.location.href = base_url + active_controller
								}else{

									if(data.status == 2){
										swal({
										  title	: "Save Failed!",
										  text	: data.pesan,
										  type	: "warning",
										  timer	: 7000
										});
									}else{
										swal({
										  title	: "Save Failed!",
										  text	: data.pesan,
										  type	: "warning",
										  timer	: 7000
										});
									}

								}
							},
							error: function() {

								swal({
								  title				: "Error Message !",
								  text				: 'An Error Occured During Process. Please try again..',
								  type				: "warning",
								  timer				: 7000
								});
							}
						});
				  } else {
					swal("Cancelled", "Data can be process again :)", "error");
					return false;
				  }
			});
		});

});



</script>
