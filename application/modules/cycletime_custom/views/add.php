<?php
$id_time = (!empty($header[0]['id_time']))?$header[0]['id_time']:'';
$no_bom = (!empty($header[0]['no_bom']))?$header[0]['no_bom']:'';
$id_product = (!empty($header[0]['id_product']))?$header[0]['id_product']:'';
$total_ct_setting = (!empty($header[0]['total_ct_setting']))?$header[0]['total_ct_setting']:'';
$total_ct_produksi = (!empty($header[0]['total_ct_produksi']))?$header[0]['total_ct_produksi']:'';
$moq = (!empty($header[0]['moq']))?$header[0]['moq']:'';
// echo $id_time;
?>
<form id="data-form" method="post">
 <div class="box box-primary">
    <div class="box-body"><br>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="customer">Produk Name <span class='text-danger'>*</span></label>
				</div>
				<div class="col-md-10">
					<select id="produk" name="produk" class="form-control input-md chosen-select" required>
						<option value="0">Select An Option</option>
						<?php 
						foreach ($product as $material){
							$selected = ($material->code_lv4 == $id_product)?'selected':'';
							// if (!in_array($material->code_lv4, $results['ArrProductCT'])) {
								?>
								<option value="<?= $material->code_lv4;?>" <?=$selected;?>><?= strtoupper(strtolower($material->nama))?></option>
								<?php 
							// }
							} ?>
					</select>
					<input type="hidden" name='id_time' value='<?=$id_time;?>'>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="customer">BOM <span class="text-red">*</span></label>
				</div>
				<div class="col-md-10">
					<select id="no_bom" name="no_bom" class="form-control input-md chosen-select">
						<?php
						if(!empty($id_time)){
							foreach ($ArrBOM as $key => $value) {
								$selected = ($value['no_bom'] == $no_bom)?'selected':'';
								echo "<option value='".$value['no_bom']."' ".$selected.">".$value['nama']."</option>";
							}
						}
						else{
							echo "<option value='0'>List Empty</option>";
						}
						?>
					</select>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="customer">Total Cycletime Setting</label>
				</div>
				<div class="col-md-2">
					<input type="text" name="total_ct_setting" id="total_ct_setting" class='form-control input-md text-right autoNumeric4' readonly value='<?=$total_ct_setting;?>'>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="customer">Total Cycletime Production</label>
				</div>
				<div class="col-md-2">
					<input type="text" name="total_ct_produksi" id="total_ct_produksi" class='form-control input-md text-right autoNumeric4' readonly value='<?=$total_ct_produksi;?>'>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="customer">MOQ <span class="text-red">*</span></label>
				</div>
				<div class="col-md-2">
					<input type="text" name="moq" id='moq' class='form-control input-md text-right autoNumeric4' readonly value='<?=$moq;?>'>
				</div>
			</div>

			<br>
			<div class='box box-info'>
				<div class='box-header'>
					<h3 class='box-title'>A. Cycletime Cutting</h3>
					<div class='box-tool pull-right'>
					</div>
				</div>
				<div class='box-body'>
					<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class='text-center' style='width: 4%;'>#</th>
								<th class='text-center' style='width: 30%;'>BOM Standard & HI GRID Standard</th>
								<th class='text-center' style='width: 15%;'></th>
               					<th class='text-center' style='width: 15%;'></th>
								<th class='text-center'></th>
								<th class='text-center'></th>
								<th class='text-center' style='width: 4%;'>#</th>
							</tr>
						</thead>
						<tbody id='tableProductBOM'>
							<?php
								$nomor = 0;
								$id = 0;
								foreach ($detailBOH as $key => $value) { $nomor++;
									$no_bom = str_replace('add','',$value['category']);
									$GetNameBOM = get_name_product_by_bom($no_bom);
									$NameBOM = (!empty($GetNameBOM[$no_bom]))?$GetNameBOM[$no_bom]:'-';
								
									echo "<tr>";
										echo "<td align='center'>#</td>";
										echo "<td align='left'>".$NameBOM."<input type='hidden' name='listBOM[]' value='add".$no_bom."'></td>";
										echo "<td></td>";
										echo "<td></td>";
										echo "<td></td>";
										echo "<td></td>";
										echo "<td></td>";
									echo "</tr>";

									echo "<tr>";
										echo "<td align='center'></td>";
										echo "<td align='left'>";
											// $resultUkuranJadi = $this->db->get_where('bom_detail_custom',array('category'=>'ukuran jadi','no_bom_detail'=>$value['no_bom_detail']))->result_array();
											// $no = 0;
											// echo "<table>";
											// foreach($resultUkuranJadi AS $val2D => $val2Dx){ $no++;
											// 	echo "<tr>";
											// 		echo "<td align='center'></td>";
											// 		echo "<td align='left'>";
											// 			echo "<div class='input-group'>";
											// 				echo "<span class='input-group-addon' style='background: bisque;'>Length :</span>";
											// 				echo "<input type='text' name='DetailHiGrid[".$val."][ukuran_jadi][".$no."][length]' class='form-control input-md autoNumeric' value='".$val2Dx['length']."'>";
											// 				echo "<span class='input-group-addon' style='background: bisque;'>Width :</span>";
											// 				echo "<input type='text' name='DetailHiGrid[".$val."][ukuran_jadi][".$no."][width]' class='form-control input-md autoNumeric' value='".$val2Dx['width']."'>";
											// 				echo "<span class='input-group-addon' style='background: bisque;'>Qty :</span>";
											// 				echo "<input type='text' name='DetailHiGrid[".$val."][ukuran_jadi][".$no."][qty]' class='form-control input-md autoNumeric' value='".$val2Dx['qty']."'>";
											// 				echo "<span class='input-group-addon' style='background: bisque;'>Meter Lari :</span>";
											// 				echo "<input type='text' name='DetailHiGrid[".$val."][ukuran_jadi][".$no."][lari]' class='form-control input-md autoNumeric' value='".$val2Dx['lari']."'>";
											// 			echo "</div>";
											// 		echo "</td>";
											// 		echo "<td align='left' hidden></td>";
											// 		echo "<td align='left'></td>";
											// 		echo "<td align='left'></td>";
											// 		echo "<td align='left'></td>";
											// 		echo "<td align='left'>";
											// 			echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delSubPart' title='Delete Part'><i class='fa fa-close'></i></button>";
											// 		echo "</td>";
											// 	echo "</tr>";
											// }
											// echo "<table>";
										echo "</td>";
										echo "<td></td>";
										echo "<td></td>";
										echo "<td></td>";
										echo "<td></td>";
										echo "<td></td>";
									echo "</tr>";

									
									$className 	= $value['category'];
    								$detailBomSingle = $this->db->get_where('cycletime_custom_detail_header',array('category'=>$className,'id_costcenter'=>$value['id_costcenter']))->result_array();
									$nomorX = 0;
									foreach($detailBomSingle AS $val2 => $val2x){
										if($val2x['category'] == $className){
											$id++;
											$nomorX++;
											echo "<tr class='header".$className."_".$id."'>";
												echo "<td align='center'>".$nomorX."</td>";
												echo "<td align='left'>";
													echo "<select name='".$className."[".$id."][costcenter]' class='chosen-select form-control input-sm inline-blockd costcenter'>";
														foreach($costcenter AS $val => $valx){
															$sel = ($valx['id_costcenter'] == $val2x['costcenter'])?'selected':'';
															echo "<option value='".$valx['id_costcenter']."' ".$sel.">".strtoupper($valx['nama_costcenter'])."</option>";
														}
													echo "</select>";
												echo "</td>";
												echo "<td></td>";
												echo "<td></td>";
												echo "<td></td>";
												echo "<td></td>";
												echo "<td align='center'>";
												echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
												echo "</td>";
											echo "</tr>";

											$q_dheader_test = $this->db->get_where('cycletime_custom_detail_detail',array('id_costcenter'=>$val2x['id_costcenter']))->result_array();
											// echo $this->db->last_query().'<br>';
											$no = 0;
											foreach($q_dheader_test AS $val2D => $val2Dx){ $no++;
												$checked1 = ($val2Dx['tipe'] == 'production')?'checked':'';
												$checked2 = ($val2Dx['tipe'] == 'setting')?'checked':'';
												echo "<tr class='header".$className."_".$id."'>";
													echo "<td align='center'></td>";
													echo "<td align='left' style='vertical-align:middle; padding-left: 30px;'>";
														echo "<b>Tipe Cycletime</b>";
														echo "<div class='radio'>";
														echo "<label>";
														echo "<input type='radio' class='tipe' name='".$className."[".$id."][detail][".$no."][tipe]' value='production' ". $checked1.">";
														echo "Cycletime Production";
														echo "</label>";
														echo "<label>";
														echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' class='tipe' name='".$className."[".$id."][detail][".$no."][tipe]' value='setting' ".$checked2.">";
														echo "Cycletime Setting";
														echo "</label>";
														echo "</div>";
														echo "<b>Process Name</b>";
														echo "<input type='text' name='".$className."[".$id."][detail][".$no."][process]' value='".$val2Dx['nm_process']."' class='form-control input-md' placeholder='Process Name' style='margin-bottom:15px;'>";
														echo "<b>Machine</b>";
														echo "<select name='".$className."[".$id."][detail][".$no."][machine]' class='chosen-select form-control input-sm inline-blockd'>";
														echo "<option value='0'>NONE MACHINE</option>";
														foreach($mesin AS $val => $valx){
														$sel = ($valx['kd_asset'] == $val2Dx['machine'])?'selected':'';
														echo "<option value='".$valx['kd_asset']."' ".$sel.">".strtoupper($valx['nm_asset'])."</option>";
														}
														echo 		"</select>";
														echo "<b>Mould / Tools</b>";
														echo "<select name='".$className."[".$id."][detail][".$no."][mould]' class='chosen-select form-control input-sm'>";
														echo "<option value='0'>NONE MOULD/TOOLS</option>";
														foreach($mould AS $val => $valx){
														$sel = ($valx['kd_asset'] == $val2Dx['mould'])?'selected':'';
														echo "<option value='".$valx['kd_asset']."' ".$sel.">".strtoupper($valx['nm_asset'])."</option>";
														}
														echo 		"</select>";
														echo "<br><br><br>";
													echo "</td>";
													echo "<td align='left'>";
														echo "<b>Cycletime (minutes)</b>";
														echo "<input type='text' name='".$className."[".$id."][detail][".$no."][cycletime]' value='".$val2Dx['cycletime']."' class='form-control input-md maskM cycletime' placeholder='Cycletime (Minutes)'>";
													echo "</td>";
													echo "<td align='left'>";
														echo "<b>Man Power</b>";
														echo "<input type='text' name='".$className."[".$id."][detail][".$no."][qty_mp]' value='".$val2Dx['qty_mp']."' class='form-control input-md maskM' placeholder='Qty Man Power'>";
													echo "</td>";
													echo "<td align='left'>";
														echo "<b>Information</b>";
														echo "<input type='text' name='".$className."[".$id."][detail][".$no."][note]' value='".$val2Dx['note']."' class='form-control input-md' placeholder='Information'>";
													echo "</td>";
													echo "<td align='left'>";
														$sel11 = ($val2Dx['va']=='Y')?'selected':'';
														$sel12 = ($val2Dx['va']=='N')?'selected':'';
														echo "<b>VA</b><br>";
														echo "<select name='".$className."[".$id."][detail][".$no."][va]' class='chosen-select form-control input-sm'>";
															echo "<option value='0'>Select VA</option>";
															echo "<option value='Y' ".$sel11.">Value Added</option>";
															echo "<option value='N' ".$sel12.">Non Value Added</option>";
														echo "</select>";
													echo "</td>";
													echo "<td align='center'>";
														echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delSubPart' title='Delete Part'><i class='fa fa-close'></i></button>";
													echo "</td>";
												echo "</tr>";
											}
											echo "<tr id='".$className."_".$id."_".$no."' class='header".$className."_".$id."'>";
												echo "<td align='center'></td>";
												echo "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button'data-classname='".$className."' class='btn btn-sm btn-primary addSubPart' title='Add Process'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Process</button></td>";
												echo "<td align='center'></td>";
												echo "<td align='center'></td>";
												echo "<td align='center'></td>";
												echo "<td align='center'></td>";
												echo "<td align='center'></td>";
											echo "</tr>";
										}
									}
								
									echo "<tr id='add".$no_bom."_".$id."'>";
									echo "<td align='center'></td>";
										echo "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' data-classname='add".$no_bom."' class='btn btn-sm btn-warning addPart' title='Add Costcenter'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Costcenter</button></td>";
										echo "<td align='center'></td>";
										echo "<td align='center'></td>";
										echo "<td align='center'></td>";
										echo "<td align='center'></td>";
										echo "<td align='center'></td>";
									echo "</tr>";
								}
							?>
						</tbody>
					</table>
				</div>
			</div>
			
			<div class='box box-info'>
				<div class='box-header'>
					<h3 class='box-title'>B. Material Joint & Finishing</h3>
					<div class='box-tool pull-right'>
					</div>
				</div>
				<div class='box-body'>
					<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class='text-center' style='width: 4%;'>#</th>
								<th class='text-center' style='width: 30%;'>Cost Center</th>
								<th class='text-center' style='width: 15%;'></th>
               					<th class='text-center' style='width: 15%;'></th>
								<th class='text-center'></th>
								<th class='text-center'></th>
								<th class='text-center' style='width: 4%;'>#</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$id = 0;
							$className 	= 'addJoint';
							foreach($detail AS $val2 => $val2x){
								if($val2x['category'] == $className){
									$id++;
									echo "<tr class='header".$className."_".$id."'>";
										echo "<td align='center'>".$id."</td>";
										echo "<td align='left'>";
											echo "<select name='".$className."[".$id."][costcenter]' class='chosen-select form-control input-sm inline-blockd costcenter'>";
												foreach($costcenter AS $val => $valx){
													$sel = ($valx['id_costcenter'] == $val2x['costcenter'])?'selected':'';
													echo "<option value='".$valx['id_costcenter']."' ".$sel.">".strtoupper($valx['nama_costcenter'])."</option>";
												}
											echo "</select>";
										echo "</td>";
										echo "<td></td>";
										echo "<td></td>";
										echo "<td></td>";
										echo "<td></td>";
										echo "<td align='center'>";
										echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
										echo "</td>";
									echo "</tr>";

									$q_dheader_test = $this->db->get_where('cycletime_custom_detail_detail',array('id_costcenter'=>$val2x['id_costcenter']))->result_array();
									$no = 0;
									foreach($q_dheader_test AS $val2D => $val2Dx){ $no++;
										$checked1 = ($val2Dx['tipe'] == 'production')?'checked':'';
										$checked2 = ($val2Dx['tipe'] == 'setting')?'checked':'';
										echo "<tr class='header".$className."_".$id."'>";
											echo "<td align='center'></td>";
											echo "<td align='left' style='vertical-align:middle; padding-left: 30px;'>";
												echo "<b>Tipe Cycletime</b>";
												echo "<div class='radio'>";
												echo "<label>";
												echo "<input type='radio' class='tipe' name='".$className."[".$id."][detail][".$no."][tipe]' value='production' ". $checked1.">";
												echo "Cycletime Production";
												echo "</label>";
												echo "<label>";
												echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' class='tipe' name='".$className."[".$id."][detail][".$no."][tipe]' value='setting' ".$checked2.">";
												echo "Cycletime Setting";
												echo "</label>";
												echo "</div>";
												echo "<b>Process Name</b>";
												echo "<input type='text' name='".$className."[".$id."][detail][".$no."][process]' value='".$val2Dx['nm_process']."' class='form-control input-md' placeholder='Process Name' style='margin-bottom:15px;'>";
												echo "<b>Machine</b>";
												echo "<select name='".$className."[".$id."][detail][".$no."][machine]' class='chosen-select form-control input-sm inline-blockd'>";
												echo "<option value='0'>NONE MACHINE</option>";
												foreach($mesin AS $val => $valx){
												$sel = ($valx['kd_asset'] == $val2Dx['machine'])?'selected':'';
												echo "<option value='".$valx['kd_asset']."' ".$sel.">".strtoupper($valx['nm_asset'])."</option>";
												}
												echo 		"</select>";
												echo "<b>Mould / Tools</b>";
												echo "<select name='".$className."[".$id."][detail][".$no."][mould]' class='chosen-select form-control input-sm'>";
												echo "<option value='0'>NONE MOULD/TOOLS</option>";
												foreach($mould AS $val => $valx){
												$sel = ($valx['kd_asset'] == $val2Dx['mould'])?'selected':'';
												echo "<option value='".$valx['kd_asset']."' ".$sel.">".strtoupper($valx['nm_asset'])."</option>";
												}
												echo 		"</select>";
												echo "<br><br><br>";
											echo "</td>";
											echo "<td align='left'>";
												echo "<b>Cycletime (minutes)</b>";
												echo "<input type='text' name='".$className."[".$id."][detail][".$no."][cycletime]' value='".$val2Dx['cycletime']."' class='form-control input-md maskM cycletime' placeholder='Cycletime (Minutes)'>";
											echo "</td>";
											echo "<td align='left'>";
												echo "<b>Man Power</b>";
												echo "<input type='text' name='".$className."[".$id."][detail][".$no."][qty_mp]' value='".$val2Dx['qty_mp']."' class='form-control input-md maskM' placeholder='Qty Man Power'>";
											echo "</td>";
											echo "<td align='left'>";
												echo "<b>Information</b>";
												echo "<input type='text' name='".$className."[".$id."][detail][".$no."][note]' value='".$val2Dx['note']."' class='form-control input-md' placeholder='Information'>";
											echo "</td>";
											echo "<td align='left'>";
												$sel11 = ($val2Dx['va']=='Y')?'selected':'';
												$sel12 = ($val2Dx['va']=='N')?'selected':'';
												echo "<b>VA</b><br>";
												echo "<select name='".$className."[".$id."][detail][".$no."][va]' class='chosen-select form-control input-sm'>";
													echo "<option value='0'>Select VA</option>";
													echo "<option value='Y' ".$sel11.">Value Added</option>";
													echo "<option value='N' ".$sel12.">Non Value Added</option>";
												echo "</select>";
											echo "</td>";
											echo "<td align='center'>";
												echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delSubPart' title='Delete Part'><i class='fa fa-close'></i></button>";
											echo "</td>";
										echo "</tr>";
									}
									echo "<tr id='".$className."_".$id."_".$no."' class='header".$className."_".$id."'>";
										echo "<td align='center'></td>";
										echo "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button'data-classname='".$className."' class='btn btn-sm btn-primary addSubPart' title='Add Process'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Process</button></td>";
										echo "<td align='center'></td>";
										echo "<td align='center'></td>";
										echo "<td align='center'></td>";
										echo "<td align='center'></td>";
										echo "<td align='center'></td>";
									echo "</tr>";
								}
							}
							?>
							<tr id='<?=$className;?>_<?=$id;?>'>
								<td align='center'></td>
								<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' data-classname='<?=$className;?>' data-classname='addJoint' class='btn btn-sm btn-warning addPart' title='Add Costcenter'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Costcenter</button></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>

			<div class='box box-success'>
				<div class='box-header'>
					<h3 class='box-title'>C. Flat Sheet</h3>
					<div class='box-tool pull-right'>
					</div>
				</div>
				<div class='box-body'>
					<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class='text-center' style='width: 4%;'>#</th>
								<th class='text-center' style='width: 30%;'>Cost Center</th>
								<th class='text-center' style='width: 15%;'></th>
               					<th class='text-center' style='width: 15%;'></th>
								<th class='text-center'></th>
								<th class='text-center'></th>
								<th class='text-center' style='width: 4%;'>#</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$id = 0;
							$className 	= 'addFlatSheet';
							foreach($detail AS $val2 => $val2x){
								if($val2x['category'] == $className){
									$id++;
									echo "<tr class='header".$className."_".$id."'>";
										echo "<td align='center'>".$id."</td>";
										echo "<td align='left'>";
											echo "<select name='".$className."[".$id."][costcenter]' class='chosen-select form-control input-sm inline-blockd costcenter'>";
												foreach($costcenter AS $val => $valx){
													$sel = ($valx['id_costcenter'] == $val2x['costcenter'])?'selected':'';
													echo "<option value='".$valx['id_costcenter']."' ".$sel.">".strtoupper($valx['nama_costcenter'])."</option>";
												}
											echo "</select>";
										echo "</td>";
										echo "<td></td>";
										echo "<td></td>";
										echo "<td></td>";
										echo "<td></td>";
										echo "<td align='center'>";
										echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
										echo "</td>";
									echo "</tr>";

									$q_dheader_test = $this->db->get_where('cycletime_custom_detail_detail',array('id_costcenter'=>$val2x['id_costcenter']))->result_array();
									$no = 0;
									foreach($q_dheader_test AS $val2D => $val2Dx){ $no++;
										$checked1 = ($val2Dx['tipe'] == 'production')?'checked':'';
										$checked2 = ($val2Dx['tipe'] == 'setting')?'checked':'';
										echo "<tr class='header".$className."_".$id."'>";
											echo "<td align='center'></td>";
											echo "<td align='left' style='vertical-align:middle; padding-left: 30px;'>";
												echo "<b>Tipe Cycletime</b>";
												echo "<div class='radio'>";
												echo "<label>";
												echo "<input type='radio' class='tipe' name='".$className."[".$id."][detail][".$no."][tipe]' value='production' ". $checked1.">";
												echo "Cycletime Production";
												echo "</label>";
												echo "<label>";
												echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' class='tipe' name='".$className."[".$id."][detail][".$no."][tipe]' value='setting' ".$checked2.">";
												echo "Cycletime Setting";
												echo "</label>";
												echo "</div>";
												echo "<b>Process Name</b>";
												echo "<input type='text' name='".$className."[".$id."][detail][".$no."][process]' value='".$val2Dx['nm_process']."' class='form-control input-md' placeholder='Process Name' style='margin-bottom:15px;'>";
												echo "<b>Machine</b>";
												echo "<select name='".$className."[".$id."][detail][".$no."][machine]' class='chosen-select form-control input-sm inline-blockd'>";
												echo "<option value='0'>NONE MACHINE</option>";
												foreach($mesin AS $val => $valx){
												$sel = ($valx['kd_asset'] == $val2Dx['machine'])?'selected':'';
												echo "<option value='".$valx['kd_asset']."' ".$sel.">".strtoupper($valx['nm_asset'])."</option>";
												}
												echo 		"</select>";
												echo "<b>Mould / Tools</b>";
												echo "<select name='".$className."[".$id."][detail][".$no."][mould]' class='chosen-select form-control input-sm'>";
												echo "<option value='0'>NONE MOULD/TOOLS</option>";
												foreach($mould AS $val => $valx){
												$sel = ($valx['kd_asset'] == $val2Dx['mould'])?'selected':'';
												echo "<option value='".$valx['kd_asset']."' ".$sel.">".strtoupper($valx['nm_asset'])."</option>";
												}
												echo 		"</select>";
												echo "<br><br><br>";
											echo "</td>";
											echo "<td align='left'>";
												echo "<b>Cycletime (minutes)</b>";
												echo "<input type='text' name='".$className."[".$id."][detail][".$no."][cycletime]' value='".$val2Dx['cycletime']."' class='form-control input-md maskM cycletime' placeholder='Cycletime (Minutes)'>";
											echo "</td>";
											echo "<td align='left'>";
												echo "<b>Man Power</b>";
												echo "<input type='text' name='".$className."[".$id."][detail][".$no."][qty_mp]' value='".$val2Dx['qty_mp']."' class='form-control input-md maskM' placeholder='Qty Man Power'>";
											echo "</td>";
											echo "<td align='left'>";
												echo "<b>Information</b>";
												echo "<input type='text' name='".$className."[".$id."][detail][".$no."][note]' value='".$val2Dx['note']."' class='form-control input-md' placeholder='Information'>";
											echo "</td>";
											echo "<td align='left'>";
												$sel11 = ($val2Dx['va']=='Y')?'selected':'';
												$sel12 = ($val2Dx['va']=='N')?'selected':'';
												echo "<b>VA</b><br>";
												echo "<select name='".$className."[".$id."][detail][".$no."][va]' class='chosen-select form-control input-sm'>";
													echo "<option value='0'>Select VA</option>";
													echo "<option value='Y' ".$sel11.">Value Added</option>";
													echo "<option value='N' ".$sel12.">Non Value Added</option>";
												echo "</select>";
											echo "</td>";
											echo "<td align='center'>";
												echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delSubPart' title='Delete Part'><i class='fa fa-close'></i></button>";
											echo "</td>";
										echo "</tr>";
									}
									echo "<tr id='".$className."_".$id."_".$no."' class='header".$className."_".$id."'>";
										echo "<td align='center'></td>";
										echo "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button'data-classname='".$className."' class='btn btn-sm btn-primary addSubPart' title='Add Process'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Process</button></td>";
										echo "<td align='center'></td>";
										echo "<td align='center'></td>";
										echo "<td align='center'></td>";
										echo "<td align='center'></td>";
										echo "<td align='center'></td>";
									echo "</tr>";
								}
							}
							?>
							<tr id='<?=$className;?>_<?=$id;?>'>
								<td align='center'></td>
								<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' data-classname='<?=$className;?>' data-classname='addJoint' class='btn btn-sm btn-warning addPart' title='Add Costcenter'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Costcenter</button></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>

			<div class='box box-warning'>
				<div class='box-header'>
					<h3 class='box-title'>D. End Plate / Kick Plate</h3>
					<div class='box-tool pull-right'>
					</div>
				</div>
				<div class='box-body'>
					<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class='text-center' style='width: 4%;'>#</th>
								<th class='text-center' style='width: 30%;'>Cost Center</th>
								<th class='text-center' style='width: 15%;'></th>
               					<th class='text-center' style='width: 15%;'></th>
								<th class='text-center'></th>
								<th class='text-center'></th>
								<th class='text-center' style='width: 4%;'>#</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$id = 0;
							$className 	= 'addEndPlate';
							foreach($detail AS $val2 => $val2x){
								if($val2x['category'] == $className){
									$id++;
									echo "<tr class='header".$className."_".$id."'>";
										echo "<td align='center'>".$id."</td>";
										echo "<td align='left'>";
											echo "<select name='".$className."[".$id."][costcenter]' class='chosen-select form-control input-sm inline-blockd costcenter'>";
												foreach($costcenter AS $val => $valx){
													$sel = ($valx['id_costcenter'] == $val2x['costcenter'])?'selected':'';
													echo "<option value='".$valx['id_costcenter']."' ".$sel.">".strtoupper($valx['nama_costcenter'])."</option>";
												}
											echo "</select>";
										echo "</td>";
										echo "<td></td>";
										echo "<td></td>";
										echo "<td></td>";
										echo "<td></td>";
										echo "<td align='center'>";
										echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
										echo "</td>";
									echo "</tr>";

									$q_dheader_test = $this->db->get_where('cycletime_custom_detail_detail',array('id_costcenter'=>$val2x['id_costcenter']))->result_array();
									$no = 0;
									foreach($q_dheader_test AS $val2D => $val2Dx){ $no++;
										$checked1 = ($val2Dx['tipe'] == 'production')?'checked':'';
										$checked2 = ($val2Dx['tipe'] == 'setting')?'checked':'';
										echo "<tr class='header".$className."_".$id."'>";
											echo "<td align='center'></td>";
											echo "<td align='left' style='vertical-align:middle; padding-left: 30px;'>";
												echo "<b>Tipe Cycletime</b>";
												echo "<div class='radio'>";
												echo "<label>";
												echo "<input type='radio' class='tipe' name='".$className."[".$id."][detail][".$no."][tipe]' value='production' ". $checked1.">";
												echo "Cycletime Production";
												echo "</label>";
												echo "<label>";
												echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' class='tipe' name='".$className."[".$id."][detail][".$no."][tipe]' value='setting' ".$checked2.">";
												echo "Cycletime Setting";
												echo "</label>";
												echo "</div>";
												echo "<b>Process Name</b>";
												echo "<input type='text' name='".$className."[".$id."][detail][".$no."][process]' value='".$val2Dx['nm_process']."' class='form-control input-md' placeholder='Process Name' style='margin-bottom:15px;'>";
												echo "<b>Machine</b>";
												echo "<select name='".$className."[".$id."][detail][".$no."][machine]' class='chosen-select form-control input-sm inline-blockd'>";
												echo "<option value='0'>NONE MACHINE</option>";
												foreach($mesin AS $val => $valx){
												$sel = ($valx['kd_asset'] == $val2Dx['machine'])?'selected':'';
												echo "<option value='".$valx['kd_asset']."' ".$sel.">".strtoupper($valx['nm_asset'])."</option>";
												}
												echo 		"</select>";
												echo "<b>Mould / Tools</b>";
												echo "<select name='".$className."[".$id."][detail][".$no."][mould]' class='chosen-select form-control input-sm'>";
												echo "<option value='0'>NONE MOULD/TOOLS</option>";
												foreach($mould AS $val => $valx){
												$sel = ($valx['kd_asset'] == $val2Dx['mould'])?'selected':'';
												echo "<option value='".$valx['kd_asset']."' ".$sel.">".strtoupper($valx['nm_asset'])."</option>";
												}
												echo 		"</select>";
												echo "<br><br><br>";
											echo "</td>";
											echo "<td align='left'>";
												echo "<b>Cycletime (minutes)</b>";
												echo "<input type='text' name='".$className."[".$id."][detail][".$no."][cycletime]' value='".$val2Dx['cycletime']."' class='form-control input-md maskM cycletime' placeholder='Cycletime (Minutes)'>";
											echo "</td>";
											echo "<td align='left'>";
												echo "<b>Man Power</b>";
												echo "<input type='text' name='".$className."[".$id."][detail][".$no."][qty_mp]' value='".$val2Dx['qty_mp']."' class='form-control input-md maskM' placeholder='Qty Man Power'>";
											echo "</td>";
											echo "<td align='left'>";
												echo "<b>Information</b>";
												echo "<input type='text' name='".$className."[".$id."][detail][".$no."][note]' value='".$val2Dx['note']."' class='form-control input-md' placeholder='Information'>";
											echo "</td>";
											echo "<td align='left'>";
												$sel11 = ($val2Dx['va']=='Y')?'selected':'';
												$sel12 = ($val2Dx['va']=='N')?'selected':'';
												echo "<b>VA</b><br>";
												echo "<select name='".$className."[".$id."][detail][".$no."][va]' class='chosen-select form-control input-sm'>";
													echo "<option value='0'>Select VA</option>";
													echo "<option value='Y' ".$sel11.">Value Added</option>";
													echo "<option value='N' ".$sel12.">Non Value Added</option>";
												echo "</select>";
											echo "</td>";
											echo "<td align='center'>";
												echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delSubPart' title='Delete Part'><i class='fa fa-close'></i></button>";
											echo "</td>";
										echo "</tr>";
									}
									echo "<tr id='".$className."_".$id."_".$no."' class='header".$className."_".$id."'>";
										echo "<td align='center'></td>";
										echo "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button'data-classname='".$className."' class='btn btn-sm btn-primary addSubPart' title='Add Process'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Process</button></td>";
										echo "<td align='center'></td>";
										echo "<td align='center'></td>";
										echo "<td align='center'></td>";
										echo "<td align='center'></td>";
										echo "<td align='center'></td>";
									echo "</tr>";
								}
							}
							?>
							<tr id='<?=$className;?>_<?=$id;?>'>
								<td align='center'></td>
								<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' data-classname='<?=$className;?>' data-classname='addJoint' class='btn btn-sm btn-warning addPart' title='Add Costcenter'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Costcenter</button></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>

			<div class='box box-danger'>
				<div class='box-header'>
					<h3 class='box-title'>E. Chequered Plate</h3>
					<div class='box-tool pull-right'>
					</div>
				</div>
				<div class='box-body'>
					<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class='text-center' style='width: 4%;'>#</th>
								<th class='text-center' style='width: 30%;'>Cost Center</th>
								<th class='text-center' style='width: 15%;'></th>
               					<th class='text-center' style='width: 15%;'></th>
								<th class='text-center'></th>
								<th class='text-center'></th>
								<th class='text-center' style='width: 4%;'>#</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$id = 0;
							$className 	= 'addChequeredPlate';
							foreach($detail AS $val2 => $val2x){
								if($val2x['category'] == $className){
									$id++;
									echo "<tr class='header".$className."_".$id."'>";
										echo "<td align='center'>".$id."</td>";
										echo "<td align='left'>";
											echo "<select name='".$className."[".$id."][costcenter]' class='chosen-select form-control input-sm inline-blockd costcenter'>";
												foreach($costcenter AS $val => $valx){
													$sel = ($valx['id_costcenter'] == $val2x['costcenter'])?'selected':'';
													echo "<option value='".$valx['id_costcenter']."' ".$sel.">".strtoupper($valx['nama_costcenter'])."</option>";
												}
											echo "</select>";
										echo "</td>";
										echo "<td></td>";
										echo "<td></td>";
										echo "<td></td>";
										echo "<td></td>";
										echo "<td align='center'>";
										echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
										echo "</td>";
									echo "</tr>";

									$q_dheader_test = $this->db->get_where('cycletime_custom_detail_detail',array('id_costcenter'=>$val2x['id_costcenter']))->result_array();
									$no = 0;
									foreach($q_dheader_test AS $val2D => $val2Dx){ $no++;
										$checked1 = ($val2Dx['tipe'] == 'production')?'checked':'';
										$checked2 = ($val2Dx['tipe'] == 'setting')?'checked':'';
										echo "<tr class='header".$className."_".$id."'>";
											echo "<td align='center'></td>";
											echo "<td align='left' style='vertical-align:middle; padding-left: 30px;'>";
												echo "<b>Tipe Cycletime</b>";
												echo "<div class='radio'>";
												echo "<label>";
												echo "<input type='radio' class='tipe' name='".$className."[".$id."][detail][".$no."][tipe]' value='production' ". $checked1.">";
												echo "Cycletime Production";
												echo "</label>";
												echo "<label>";
												echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' class='tipe' name='".$className."[".$id."][detail][".$no."][tipe]' value='setting' ".$checked2.">";
												echo "Cycletime Setting";
												echo "</label>";
												echo "</div>";
												echo "<b>Process Name</b>";
												echo "<input type='text' name='".$className."[".$id."][detail][".$no."][process]' value='".$val2Dx['nm_process']."' class='form-control input-md' placeholder='Process Name' style='margin-bottom:15px;'>";
												echo "<b>Machine</b>";
												echo "<select name='".$className."[".$id."][detail][".$no."][machine]' class='chosen-select form-control input-sm inline-blockd'>";
												echo "<option value='0'>NONE MACHINE</option>";
												foreach($mesin AS $val => $valx){
												$sel = ($valx['kd_asset'] == $val2Dx['machine'])?'selected':'';
												echo "<option value='".$valx['kd_asset']."' ".$sel.">".strtoupper($valx['nm_asset'])."</option>";
												}
												echo 		"</select>";
												echo "<b>Mould / Tools</b>";
												echo "<select name='".$className."[".$id."][detail][".$no."][mould]' class='chosen-select form-control input-sm'>";
												echo "<option value='0'>NONE MOULD/TOOLS</option>";
												foreach($mould AS $val => $valx){
												$sel = ($valx['kd_asset'] == $val2Dx['mould'])?'selected':'';
												echo "<option value='".$valx['kd_asset']."' ".$sel.">".strtoupper($valx['nm_asset'])."</option>";
												}
												echo 		"</select>";
												echo "<br><br><br>";
											echo "</td>";
											echo "<td align='left'>";
												echo "<b>Cycletime (minutes)</b>";
												echo "<input type='text' name='".$className."[".$id."][detail][".$no."][cycletime]' value='".$val2Dx['cycletime']."' class='form-control input-md maskM cycletime' placeholder='Cycletime (Minutes)'>";
											echo "</td>";
											echo "<td align='left'>";
												echo "<b>Man Power</b>";
												echo "<input type='text' name='".$className."[".$id."][detail][".$no."][qty_mp]' value='".$val2Dx['qty_mp']."' class='form-control input-md maskM' placeholder='Qty Man Power'>";
											echo "</td>";
											echo "<td align='left'>";
												echo "<b>Information</b>";
												echo "<input type='text' name='".$className."[".$id."][detail][".$no."][note]' value='".$val2Dx['note']."' class='form-control input-md' placeholder='Information'>";
											echo "</td>";
											echo "<td align='left'>";
												$sel11 = ($val2Dx['va']=='Y')?'selected':'';
												$sel12 = ($val2Dx['va']=='N')?'selected':'';
												echo "<b>VA</b><br>";
												echo "<select name='".$className."[".$id."][detail][".$no."][va]' class='chosen-select form-control input-sm'>";
													echo "<option value='0'>Select VA</option>";
													echo "<option value='Y' ".$sel11.">Value Added</option>";
													echo "<option value='N' ".$sel12.">Non Value Added</option>";
												echo "</select>";
											echo "</td>";
											echo "<td align='center'>";
												echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delSubPart' title='Delete Part'><i class='fa fa-close'></i></button>";
											echo "</td>";
										echo "</tr>";
									}
									echo "<tr id='".$className."_".$id."_".$no."' class='header".$className."_".$id."'>";
										echo "<td align='center'></td>";
										echo "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button'data-classname='".$className."' class='btn btn-sm btn-primary addSubPart' title='Add Process'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Process</button></td>";
										echo "<td align='center'></td>";
										echo "<td align='center'></td>";
										echo "<td align='center'></td>";
										echo "<td align='center'></td>";
										echo "<td align='center'></td>";
									echo "</tr>";
								}
							}
							?>
							<tr id='<?=$className;?>_<?=$id;?>'>
								<td align='center'></td>
								<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' data-classname='<?=$className;?>' data-classname='addJoint' class='btn btn-sm btn-warning addPart' title='Add Costcenter'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Costcenter</button></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>

			<div class='box box-danger'>
				<div class='box-header'>
					<h3 class='box-title'>F. Others</h3>
					<div class='box-tool pull-right'>
					</div>
				</div>
				<div class='box-body'>
					<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class='text-center' style='width: 4%;'>#</th>
								<th class='text-center' style='width: 30%;'>Cost Center</th>
								<th class='text-center' style='width: 15%;'></th>
               					<th class='text-center' style='width: 15%;'></th>
								<th class='text-center'></th>
								<th class='text-center'></th>
								<th class='text-center' style='width: 4%;'>#</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$id = 0;
							$className 	= 'addOthers';
							foreach($detail AS $val2 => $val2x){
								if($val2x['category'] == $className){
									$id++;
									echo "<tr class='header".$className."_".$id."'>";
										echo "<td align='center'>".$id."</td>";
										echo "<td align='left'>";
											echo "<select name='".$className."[".$id."][costcenter]' class='chosen-select form-control input-sm inline-blockd costcenter'>";
												foreach($costcenter AS $val => $valx){
													$sel = ($valx['id_costcenter'] == $val2x['costcenter'])?'selected':'';
													echo "<option value='".$valx['id_costcenter']."' ".$sel.">".strtoupper($valx['nama_costcenter'])."</option>";
												}
											echo "</select>";
										echo "</td>";
										echo "<td></td>";
										echo "<td></td>";
										echo "<td></td>";
										echo "<td></td>";
										echo "<td align='center'>";
										echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
										echo "</td>";
									echo "</tr>";

									$q_dheader_test = $this->db->get_where('cycletime_custom_detail_detail',array('id_costcenter'=>$val2x['id_costcenter']))->result_array();
									$no = 0;
									foreach($q_dheader_test AS $val2D => $val2Dx){ $no++;
										$checked1 = ($val2Dx['tipe'] == 'production')?'checked':'';
										$checked2 = ($val2Dx['tipe'] == 'setting')?'checked':'';
										echo "<tr class='header".$className."_".$id."'>";
											echo "<td align='center'></td>";
											echo "<td align='left' style='vertical-align:middle; padding-left: 30px;'>";
												echo "<b>Tipe Cycletime</b>";
												echo "<div class='radio'>";
												echo "<label>";
												echo "<input type='radio' class='tipe' name='".$className."[".$id."][detail][".$no."][tipe]' value='production' ". $checked1.">";
												echo "Cycletime Production";
												echo "</label>";
												echo "<label>";
												echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' class='tipe' name='".$className."[".$id."][detail][".$no."][tipe]' value='setting' ".$checked2.">";
												echo "Cycletime Setting";
												echo "</label>";
												echo "</div>";
												echo "<b>Process Name</b>";
												echo "<input type='text' name='".$className."[".$id."][detail][".$no."][process]' value='".$val2Dx['nm_process']."' class='form-control input-md' placeholder='Process Name' style='margin-bottom:15px;'>";
												echo "<b>Machine</b>";
												echo "<select name='".$className."[".$id."][detail][".$no."][machine]' class='chosen-select form-control input-sm inline-blockd'>";
												echo "<option value='0'>NONE MACHINE</option>";
												foreach($mesin AS $val => $valx){
												$sel = ($valx['kd_asset'] == $val2Dx['machine'])?'selected':'';
												echo "<option value='".$valx['kd_asset']."' ".$sel.">".strtoupper($valx['nm_asset'])."</option>";
												}
												echo 		"</select>";
												echo "<b>Mould / Tools</b>";
												echo "<select name='".$className."[".$id."][detail][".$no."][mould]' class='chosen-select form-control input-sm'>";
												echo "<option value='0'>NONE MOULD/TOOLS</option>";
												foreach($mould AS $val => $valx){
												$sel = ($valx['kd_asset'] == $val2Dx['mould'])?'selected':'';
												echo "<option value='".$valx['kd_asset']."' ".$sel.">".strtoupper($valx['nm_asset'])."</option>";
												}
												echo 		"</select>";
												echo "<br><br><br>";
											echo "</td>";
											echo "<td align='left'>";
												echo "<b>Cycletime (minutes)</b>";
												echo "<input type='text' name='".$className."[".$id."][detail][".$no."][cycletime]' value='".$val2Dx['cycletime']."' class='form-control input-md maskM cycletime' placeholder='Cycletime (Minutes)'>";
											echo "</td>";
											echo "<td align='left'>";
												echo "<b>Man Power</b>";
												echo "<input type='text' name='".$className."[".$id."][detail][".$no."][qty_mp]' value='".$val2Dx['qty_mp']."' class='form-control input-md maskM' placeholder='Qty Man Power'>";
											echo "</td>";
											echo "<td align='left'>";
												echo "<b>Information</b>";
												echo "<input type='text' name='".$className."[".$id."][detail][".$no."][note]' value='".$val2Dx['note']."' class='form-control input-md' placeholder='Information'>";
											echo "</td>";
											echo "<td align='left'>";
												$sel11 = ($val2Dx['va']=='Y')?'selected':'';
												$sel12 = ($val2Dx['va']=='N')?'selected':'';
												echo "<b>VA</b><br>";
												echo "<select name='".$className."[".$id."][detail][".$no."][va]' class='chosen-select form-control input-sm'>";
													echo "<option value='0'>Select VA</option>";
													echo "<option value='Y' ".$sel11.">Value Added</option>";
													echo "<option value='N' ".$sel12.">Non Value Added</option>";
												echo "</select>";
											echo "</td>";
											echo "<td align='center'>";
												echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delSubPart' title='Delete Part'><i class='fa fa-close'></i></button>";
											echo "</td>";
										echo "</tr>";
									}
									echo "<tr id='".$className."_".$id."_".$no."' class='header".$className."_".$id."'>";
										echo "<td align='center'></td>";
										echo "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button'data-classname='".$className."' class='btn btn-sm btn-primary addSubPart' title='Add Process'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Process</button></td>";
										echo "<td align='center'></td>";
										echo "<td align='center'></td>";
										echo "<td align='center'></td>";
										echo "<td align='center'></td>";
										echo "<td align='center'></td>";
									echo "</tr>";
								}
							}
							?>
							<tr id='<?=$className;?>_<?=$id;?>'>
								<td align='center'></td>
								<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' data-classname='<?=$className;?>' data-classname='addJoint' class='btn btn-sm btn-warning addPart' title='Add Costcenter'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Costcenter</button></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>

			<button type="button" class="btn btn-danger" style='float:right; margin-left:5px;' name="back" id="back"><i class="fa fa-reply"></i> Back</button>
			<button type="submit" class="btn btn-primary" style='float:right;' name="save" id="save"><i class="fa fa-save"></i> Save</button>
		</div>
	</div>
</form>

<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>

<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';

	$(document).ready(function(){
		$('.chosen-select').select2({width: '100%'});

		$(document).on('change','#produk',function(){
			var id_product = $("#produk").val();

			$.ajax({
				url:siteurl+active_controller+'/get_list_bom',
				method : "POST",
				data : {id_product:id_product},
				dataType : 'json',
				success: function(data){
					$('#no_bom').html(data.option);
				}
			});
		});
		//add part
		$(document).on('click', '.addPart', function(){
			// loading_spinner();
			var get_id 		= $(this).parent().parent().attr('id');
			var split_id	= get_id.split('_');
			var id 			= parseInt(split_id[1])+1;
			var id_bef 		= split_id[1];
			var className	= $(this).data('classname')

			$.ajax({
				url: base_url + active_controller +'/get_add/'+id+'/'+className,
				cache: false,
				type: "POST",
				dataType: "json",
				success: function(data){
					$("#"+className+"_"+id_bef).before(data.header);
					$("#"+className+"_"+id_bef).remove();
					$('.chosen-select').select2({width: '100%'});
					$('.maskM').autoNumeric();
					swal.close();
				},
				error: function() {
					swal({
						title	: "Error Message !",
						text	: 'Connection Time Out. Please try again..',
						type	: "warning",
						timer	: 3000
					});
				}
			});
		});

		//add part
		$(document).on('click', '.addSubPart', function(){
			// loading_spinner();
			var get_id 		= $(this).parent().parent().attr('id');
			var split_id	= get_id.split('_');
			var id 			= split_id[1];
			var id2 		= parseInt(split_id[2])+1;
			var id_bef 		= split_id[2];

			var className	= $(this).data('classname')

			$.ajax({
				url: base_url+active_controller+'/get_add_sub/'+id+'/'+id2+'/'+className,
				cache: false,
				type: "POST",
				dataType: "json",
				success: function(data){
					$("#"+className+"_"+id+"_"+id_bef).before(data.header);
					$("#"+className+"_"+id+"_"+id_bef).remove();
					$('.chosen-select').select2({width:'100%'});
					$('.maskM').autoNumeric();
					swal.close();
				},
				error: function() {
					swal({
						title				: "Error Message !",
						text				: 'Connection Time Out. Please try again..',
						type				: "warning",
						timer				: 3000
					});
				}
			});
		});

		//delete part
		$(document).on('click', '.delPart', function(){
			var get_id 		= $(this).parent().parent().attr('class');
			$("."+get_id).remove();
		});

		$(document).on('click', '.delSubPart', function(){
			var get_id 		= $(this).parent().parent('tr').html();
			$(this).parent().parent('tr').remove();
		});

    //add part
		$(document).on('click', '#back', function(){
		    window.location.href = base_url + active_controller;
		});

		$(document).on('keyup', '.cycletime', function(){
			let SumTotalSet = 0
			let SumTotalPro = 0
			let cycletime
			let tipe
			$('.cycletime').each(function(){
				tipe = $(this).parent().parent().find('.tipe:checked').val()
				cycletime = getNum($(this).val().split(',').join(''))

				if(tipe == 'setting'){
					SumTotalSet += cycletime
				}
				else{
					SumTotalPro += cycletime
				}
			})

			// console.log(SumTotal)
			$('#total_ct_setting').val(number_format(SumTotalSet,2))
			$('#total_ct_produksi').val(number_format(SumTotalPro,2))

			let moq = SumTotalSet * 9 / SumTotalPro
			$('#moq').val(Math.round(moq))
		});

		$(document).on('click', '.tipe', function(){
			let SumTotalSet = 0
			let SumTotalPro = 0
			let cycletime
			let tipe
			$('.cycletime').each(function(){
				tipe = $(this).parent().parent().find('.tipe:checked').val()
				cycletime = getNum($(this).val().split(',').join(''))
				// console.log(tipe)
				if(tipe == 'setting'){
					SumTotalSet += cycletime
				}
				else{
					SumTotalPro += cycletime
				}
			})

			// console.log(SumTotal)
			$('#total_ct_setting').val(number_format(SumTotalSet,2))
			$('#total_ct_produksi').val(number_format(SumTotalPro,2))

			let moq = SumTotalSet * 9 / SumTotalPro
			$('#moq').val(Math.round(moq))
		});

		$(document).on('change', '#no_bom', function(){
			// loading_spinner();
			var no_bom	= $(this).val()

			if(no_bom != '0'){
				$.ajax({
					url: base_url+active_controller+'/get_bom_product_custom/'+no_bom,
					cache: false,
					type: "POST",
					dataType: "json",
					success: function(data){
						$('#tableProductBOM').html(data.header);
						$('.chosen-select').select2({width:'100%'});
						$('.maskM').autoNumeric();
						swal.close();
					},
					error: function() {
						swal({
							title				: "Error Message !",
							text				: 'Connection Time Out. Please try again..',
							type				: "warning",
							timer				: 3000
						});
					}
				});
			}
			else{
				$('#tableProductBOM').html('');
			}

		});



		$('#save').click(function(e){
			e.preventDefault();
			var produk		= $('#produk').val();
			var no_bom		= $('#no_bom').val();
			var moq			= $('#moq').val();
			var costcenter	= $('.costcenter').val();
			var process		= $('.process').val();
			// console.log(moq)
			if(produk == '0' ){
				swal({
					title	: "Error Message!",
					text	: 'Product name empty, select first ...',
					type	: "warning"
				});

				$('#save').prop('disabled',false);
				return false;
			}
			if(no_bom == '0' ){
				swal({
					title	: "Error Message!",
					text	: 'No BOM name empty, select first ...',
					type	: "warning"
				});

				$('#save').prop('disabled',false);
				return false;
			}
			if(costcenter == '0' ){
				swal({
					title	: "Error Message!",
					text	: 'Costcenter empty, select first ...',
					type	: "warning"
				});

				$('#save').prop('disabled',false);
				return false;
			}
			if(process == '' ){
				swal({
					title	: "Error Message!",
					text	: 'Process name empty, select first ...',
					type	: "warning"
				});

				$('#save').prop('disabled',false);
				return false;
			}
			// if(moq == '0' || moq == 'Infinity' ){
			// 	swal({
			// 		title	: "Error Message!",
			// 		text	: 'MOQ Tidak Boleh NOL !',
			// 		type	: "warning"
			// 	});

			// 	$('#save').prop('disabled',false);
			// 	return false;
			// }

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
						var baseurl=siteurl+active_controller+'/save_cycletime'
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
									window.location.href = base_url + active_controller;
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

		function number_format (number, decimals, dec_point, thousands_sep) {
			// Strip all characters but numerical ones.
			number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
			var n = !isFinite(+number) ? 0 : +number,
				prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
				sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
				dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
				s = '',
				toFixedFix = function (n, prec) {
					var k = Math.pow(10, prec);
					return '' + Math.round(n * k) / k;
				};
			// Fix for IE parseFloat(0.55).toFixed(0) = 0;
			s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
			if (s[0].length > 3) {
				s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
			}
			if ((s[1] || '').length < prec) {
				s[1] = s[1] || '';
				s[1] += new Array(prec - s[1].length + 1).join('0');
			}
			return s.join(dec);
		}

});

</script>
