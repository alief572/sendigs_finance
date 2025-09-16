<?php
//ipp
$id          	= (!empty($header[0]->id))?$header[0]->id:'';
$no_ipp         = (!empty($header[0]->no_ipp))?$header[0]->no_ipp:'';
$id_customer  	= (!empty($header[0]->id_customer))?$header[0]->id_customer:'';
$project   		= (!empty($header[0]->project))?$header[0]->project:'';
$referensi   	= (!empty($header[0]->referensi))?$header[0]->referensi:'';
$id_top   		= (!empty($header[0]->id_top))?$header[0]->id_top:'';
$keterangan   	= (!empty($header[0]->keterangan))?$header[0]->keterangan:'';
//delivery
$delivery_type   	= (!empty($header[0]->delivery_type))?$header[0]->delivery_type:'';
$id_country   		= (!empty($header[0]->id_country))?$header[0]->id_country:'IDN';
$delivery_category  = (!empty($header[0]->delivery_category))?$header[0]->delivery_category:'';
$area_destinasi   	= (!empty($header[0]->area_destinasi))?$header[0]->area_destinasi:'';
$delivery_address   = (!empty($header[0]->delivery_address))?$header[0]->delivery_address:'';
$shipping_method   	= (!empty($header[0]->shipping_method))?$header[0]->shipping_method:'';
$packing   			= (!empty($header[0]->packing))?$header[0]->packing:'';
$guarantee   		= (!empty($header[0]->guarantee))?$header[0]->guarantee:'';
$delivery_date   			= (!empty($header[0]->delivery_date))?$header[0]->delivery_date:'';
$instalasi_option	= (!empty($header[0]->instalasi_option))?$header[0]->instalasi_option:'';

$delivery_type1	= (!empty($header[0]->delivery_type) AND $header[0]->delivery_type == 'local')?'selected':'';
$delivery_type2 = (!empty($header[0]->delivery_type) AND $header[0]->delivery_type == 'export')?'selected':'';

$instalasi1	= (!empty($header[0]->instalasi_option) AND $header[0]->instalasi_option == 'N')?'selected':'';
$instalasi2 = (!empty($header[0]->instalasi_option) AND $header[0]->instalasi_option == 'Y')?'selected':'';
// print_r($header);
?>

 <div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post"><br>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="customer">Customer Name <span class='text-red'>*</span></label>
				</div>
				<div class="col-md-4">
					<select id="id_customer" name="id_customer" class="form-control input-md chosen-select" disabled>
						<option value="0">Select An Customer</option>
						<?php foreach ($customer as $val => $value){
						$sel = ($value['id_customer'] == $id_customer)?'selected':'';
						?>
						<option value="<?= $value['id_customer'];?>" <?=$sel;?>><?= strtoupper($value['nm_customer'])?></option>
						<?php } ?>
					</select>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="customer">Project Name <span class='text-red'>*</span></label>
				</div>
				<div class="col-md-4">
					<input type="hidden" name="id" id="id" value="<?=$id;?>">
					<input type="hidden" name="no_ipp" id="no_ipp" value="<?=$no_ipp;?>">
					<input type="text" name="project" id="project" class='form-control input-md' required placeholder='Project Name' value="<?=$project;?>">
				</div>
				<div class="col-md-2">
					<label for="customer">Referensi info</label>
				</div>
				<div class="col-md-4">
					<input type="text" name="referensi" id="referensi" class='form-control input-md' placeholder='Referensi Info' value="<?=$referensi;?>">
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="customer">Term Of Payment</label>
				</div>
				<div class="col-md-4">
					<select id="id_top" name="id_top" class="form-control input-md chosen-select" disabled>
						<option value="0">Select An TOP</option>
						<?php foreach ($top as $val => $value){
						$sel = ($value['id'] == $id_top)?'selected':'';
						?>
						<option value="<?= $value['id'];?>" <?=$sel;?>><?= strtoupper($value['name'])?></option>
						<?php } ?>
					</select>
				</div>
				<div class="col-md-2">
					<label for="customer">Keterangan</label>
				</div>
				<div class="col-md-4">
					<textarea name='keterangan' id='keterangan' class='form-control input-md' placeholder='Keterangan' rows='2'><?=$keterangan;?></textarea>
				</div>
			</div>
			<hr>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="customer">Delivery Type</label>
				</div>
				<div class="col-md-4">
					<select name="delivery_type" id="delivery_type" class='form-control input-md chosen-select' width='100%' disabled>
						<option value="0">Select An Delivery Type</option>
						<option value="local" <?=$delivery_type1;?>>Local</option>
						<option value="export" <?=$delivery_type2;?>>Export</option>
					</select>
				</div>
				<div class="col-md-2">
					<label for="customer">Delivery Country</label>
				</div>
				<div class="col-md-4">
					<select name="id_country" id="id_country" class='form-control input-md chosen-select' disabled>
						<option value="0">Select Country</option>
						<?php
						foreach ($country as $key => $value) {
							$selected = ($id_country == $value['iso3'])?'selected':'';
							echo "<option value='".$value['iso3']."' ".$selected.">".strtoupper($value['name'])."</option>";
						}
						?>
					</select>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label>Delivery Category</label>
				</div>
				<div class="col-md-4">
					<select id="delivery_category" name="delivery_category" class="form-control input-md chosen-select" disabled>
						<option value="0">Select An Delivery Category</option>
						<?php foreach ($deliv_category as $val => $value){
						$sel = ($value['value'] == $delivery_category)?'selected':'';
						?>
						<option value="<?= $value['value'];?>" <?=$sel;?>><?= strtoupper($value['view'])?></option>
						<?php } ?>
					</select>
				</div>
				<div class="col-md-2">
					<label>Area Destination</label>
				</div>
				<div class="col-md-4">
				<input type="text" name="area_destinasi" id="area_destinasi" class='form-control input-md' placeholder='Area Destination' value="<?=$area_destinasi;?>">
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label>Shipping Method</label>
				</div>
				<div class="col-md-4">
					<select id="shipping_method" name="shipping_method" class="form-control input-md chosen-select" disabled>
						<option value="0">Select An Shipping Method</option>
						<?php foreach ($shipping as $val => $value){
						$sel = ($value['value'] == $shipping_method)?'selected':'';
						?>
						<option value="<?= $value['value'];?>" <?=$sel;?>><?= strtoupper($value['view'])?></option>
						<?php } ?>
					</select>
				</div>
				<div class="col-md-2">
					<label>Packing</label>
				</div>
				<div class="col-md-4">
					<select id="packing" name="packing" class="form-control input-md chosen-select" disabled>
						<option value="0">Select An Packing</option>
						<?php foreach ($packing_list as $val => $value){
						$sel = ($value['value'] == $packing)?'selected':'';
						?>
						<option value="<?= $value['value'];?>" <?=$sel;?>><?= strtoupper($value['view'])?></option>
						<?php } ?>
					</select>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label>Guarantee</label>
				</div>
				<div class="col-md-4">
					<input type="text" name="guarantee" id="guarantee" class='form-control input-md' placeholder='Guarantee' value="<?=$guarantee;?>">
				</div>
				<div class="col-md-2">
					<label>Installation</label>
				</div>
				<div class="col-md-4">
					<select name="instalasi_option" id="instalasi_option" class='form-control input-md chosen-select' disabled>
						<option value="N" <?=$instalasi1;?>>NO</option>
						<option value="Y" <?=$instalasi2;?>>YES</option>
					</select>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label>Delivery Date</label>
				</div>
				<div class="col-md-4">
					<input type="text" name="delivery_date" id="delivery_date" class='form-control input-md datepicker' readonly placeholder='Delivery Date' value="<?=$delivery_date;?>">
				</div>
				<div class="col-md-2">
					<label>Delivery Address</label>
				</div>
				<div class="col-md-4">
					<textarea name="delivery_address" id="delivery_address" class='form-control input-md' rows="3" placeholder='Delivery Address'><?=$delivery_address;?></textarea>
				</div>
			</div>
			<hr>
			
			
			<div>
				<?php
				$val = 0;
				if(!empty($detail)){
					foreach($detail AS $val => $valx){ $val++;

						$platform   		= (!empty($valx['platform']) AND $valx['platform'] == 'Y')?'checked':'';
						$cover_drainage   	= (!empty($valx['cover_drainage']) AND $valx['cover_drainage'] == 'Y')?'checked':'';
						$facade   			= (!empty($valx['facade']) AND $valx['facade'] == 'Y')?'checked':'';
						$ceilling   		= (!empty($valx['ceilling']) AND $valx['ceilling'] == 'Y')?'checked':'';
						$partition   		= (!empty($valx['partition']) AND $valx['partition'] == 'Y')?'checked':'';
						$fence   			= (!empty($valx['fence']) AND $valx['fence'] == 'Y')?'checked':'';
						$app_others 		= $valx['app_others'];

						$color_dark_green   = (!empty($valx['color_dark_green']) AND $valx['color_dark_green'] == 'Y')?'checked':'';
						$color_dark_grey   	= (!empty($valx['color_dark_grey']) AND $valx['color_dark_grey'] == 'Y')?'checked':'';
						$color_light_grey   = (!empty($valx['color_light_grey']) AND $valx['color_light_grey'] == 'Y')?'checked':'';
						$color_yellow   	= (!empty($valx['color_yellow']) AND $valx['color_yellow'] == 'Y')?'checked':'';
						$color   			= (!empty($valx['color']))?$valx['color']:'';

						$food_grade   		= (!empty($valx['food_grade']) AND $valx['food_grade'] == 'Y')?'checked':'';
						$uv   				= (!empty($valx['uv']) AND $valx['uv'] == 'Y')?'checked':'';
						$fire_reterdant   	= (!empty($valx['fire_reterdant']) AND $valx['fire_reterdant'] == 'Y')?'checked':'';
						$industrial_type   	= (!empty($valx['industrial_type']) AND $valx['industrial_type'] == 'Y')?'checked':'';
						$commercial_type   	= (!empty($valx['commercial_type']) AND $valx['commercial_type'] == 'Y')?'checked':'';
						$superior_type   	= (!empty($valx['superior_type']) AND $valx['superior_type'] == 'Y')?'checked':'';


						$standard_astm   	= (!empty($valx['standard_astm']) AND $valx['standard_astm'] == 'Y')?'checked':'';
						$standard_bs   		= (!empty($valx['standard_bs']) AND $valx['standard_bs'] == 'Y')?'checked':'';
						$standard_dnv   	= (!empty($valx['standard_dnv']) AND $valx['standard_dnv'] == 'Y')?'checked':'';
						$file_pendukung_1   = (!empty($valx['file_pendukung_1']))?$valx['file_pendukung_1']:'';
						$file_pendukung_2   = (!empty($valx['file_pendukung_2']))?$valx['file_pendukung_2']:'';

						$surface_concave   			= (!empty($valx['surface_concave']) AND $valx['surface_concave'] == 'Y')?'checked':'';
						$surface_flat   			= (!empty($valx['surface_flat']) AND $valx['surface_flat'] == 'Y')?'checked':'';
						$surface_chequered_plate   	= (!empty($valx['surface_chequered_plate']) AND $valx['surface_chequered_plate'] == 'Y')?'checked':'';
						$surface_anti_skid   		= (!empty($valx['surface_anti_skid']) AND $valx['surface_anti_skid'] == 'Y')?'checked':'';

						$mesh_open   		= (!empty($valx['mesh_open']) AND $valx['mesh_open'] == 'Y')?'checked':'';
						$mesh_closed   		= (!empty($valx['mesh_closed']) AND $valx['mesh_closed'] == 'Y')?'checked':'';
						
						$file_dokumen   	= (!empty($valx['file_dokumen']))?$valx['file_dokumen']:'';

						$type_product   	= (!empty($valx['type_product']))?$valx['type_product']:'';
						$product_name   	= (!empty($valx['product_name']))?$valx['product_name']:'';
						$accessories   		= (!empty($valx['accessories']))?$valx['accessories']:'';
						$ket   		= (!empty($valx['ket']))?$valx['ket']:'';
						
						echo "<div id='header_".$val."'>";
						echo "<h4 class='text-bold text-primary'>Permintaan ".$val."&nbsp;&nbsp;";
						if(empty($tanda)){
						// echo "<span class='text-red text-bold delPart' data-id='".$val."' style='cursor:pointer;' title='Delete Part'>Delete</span>";
						}
						echo "</h4>";
						echo "<div class='form-group row'>";
						echo "<div class='col-md-2'>";
						echo "<label>Aplikasi Kebutuhan</label>";
						echo "</div>";
						echo "<div class='col-md-2'>";
						echo "<div class='form-group'>";
						echo "<div class='checkbox'><label><input type='checkbox' name='Detail[".$val."][platform]' value='Y' ".$platform.">Platform</label></div>";
						echo "<div class='checkbox'><label><input type='checkbox' name='Detail[".$val."][cover_drainage]' value='Y' ".$cover_drainage.">Cover Drainage</label></div>";
						echo "<div class='checkbox'><label><input type='checkbox' name='Detail[".$val."][facade]' value='Y' ".$facade.">Facade</label></div>";
						echo "</div>";
						echo "</div>";
						echo "<div class='col-md-2'>";
						echo "<div class='form-group'>";
						echo "<div class='checkbox'><label><input type='checkbox' name='Detail[".$val."][ceilling]' value='Y' ".$ceilling.">Ceilling</label></div>";
						echo "<div class='checkbox'><label><input type='checkbox' name='Detail[".$val."][partition]' value='Y' ".$partition.">Partition</label></div>";
						echo "<div class='checkbox'><label><input type='checkbox' name='Detail[".$val."][fence]' value='Y' ".$fence.">Fence</label></div>";
						echo "</div>";
						echo "</div>";
						echo "<div class='col-md-2'>";
						echo "<div class='form-group'><label>Other</label>";
						echo "<input type='text' name='Detail[".$val."][app_others]' class='form-control input-md' placeholder='Other' value='".$app_others."'>";
						echo "</div>";
						echo "</div>";
						
						echo "</div>";

						echo "<hr>";
						echo "<div class='form-group row'>";
						echo "	<div class='col-md-2'>";
						echo "		<label>Type Product</label>";
						echo "	</div>";
						echo "	<div class='col-md-3'>";
						echo "		<select name='Detail[".$val."][type_product]' class='form-control' disabled>";
											foreach ($product_lv1 as $key => $value) {
												$selected = ($type_product == $value['code_lv1'])?'selected':'';
												echo "<option value='".$value['code_lv1']."' ".$selected.">".$value['nama']."</option>";
											}
						echo "		</select>";
						echo "	</div>";
						echo "</div>";
						echo "<div class='form-group row'>";
						echo "	<div class='col-md-2'>";
						echo "		<label>Product Name</label>";
						echo "	</div>";
						echo "	<div class='col-md-6'>";
						echo "		<input type='text' name='Detail[".$val."][product_name]' class='form-control input-md' placeholder='Product Name' value='".$product_name."'>";
						echo "	</div>";
						echo "</div>";
						
						echo "<hr>";
						
						echo "<div class='form-group row'>";
						echo "	<div class='col-md-2'>";
						echo "		<label>Additional Spesification</label>";
						echo "	</div>";
						echo "	<div class='col-md-2'>";
						echo "		<div class='form-group'><label>Additional</label>";
						echo "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$val."][food_grade]' value='Y' ".$food_grade.">Food Grade</label></div>";
						echo "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$val."][uv]' value='Y' ".$uv.">UV</label></div>";
						echo "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$val."][uv]' value='Y' ".$food_grade.">UV</label></div>";
						echo "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$val."][fire_reterdant]' value='Y' ".$fire_reterdant.">Fire Reterdant</label></div>";
						echo "		</div>";
						echo "	</div>";
						echo "	<div class='col-md-2'>";
						echo "		<div class='form-group'><label></label>";
						echo "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$val."][industrial_type]' value='Y' ".$industrial_type.">Industrial Type</label></div>";
						echo "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$val."][commercial_type]' value='Y' ".$commercial_type.">Commercial Type</label></div>";
						echo "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$val."][superior_type]' value='Y' ".$superior_type.">Superior Type</label></div>";
						echo "		</div>";
						echo "	</div>";
						echo "	<div class='col-md-2'>";
						echo "		<div class='form-group'><label>Standard Spec</label>";
						echo "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$val."][standard_astm]' value='Y' ".$standard_astm.">ASTM</label></div>";
						echo "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$val."][standard_bs]' value='Y' ".$standard_bs.">BS</label></div>";
						echo "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$val."][standard_dnv]' value='Y' ".$standard_dnv.">GNV-GL</label></div>";
						echo "		</div>";
						echo "	</div>";
						echo "	<div class='col-md-4'>";
						echo "		<div class='form-group'><label>Dokumen Pendukung</label>";
						echo "		<input type='text' class='form-control' name='Detail[".$val."][file_pendukung_1]' placeholder='Dokumen Pendukung 1' style='margin-bottom:5px;' value=' ".$file_pendukung_1."'>";
						echo "		<input type='text' class='form-control' name='Detail[".$val."][file_pendukung_2]' placeholder='Dokumen Pendukung 2' style='margin-bottom:5px;' value=' ".$file_pendukung_2."'>";
						echo "		</div>";
						echo "	</div>";
						echo "</div>";

						echo "<div class='form-group row'>";
						echo "	<div class='col-md-2'>";
						echo "		<label></label>";
						echo "	</div>";
						echo "	<div class='col-md-2'>";
						echo "		<div class='form-group'><label>Color</label>";
						echo "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$val."][color_dark_green]' value='Y' ".$color_dark_green.">Dark Green</label></div>";
						echo "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$val."][color_dark_grey]' value='Y' ".$color_dark_grey.">Dark Grey</label></div>";
						echo "		</div>";
						echo "	</div>";
						echo "	<div class='col-md-2'>";
						echo "		<div class='form-group'><label></label>";
						echo "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$val."][color_light_grey]' value='Y' ".$color_light_grey.">Light Grey</label></div>";
						echo "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$val."][color_yellow]' value='Y' ".$color_yellow.">Yellow</label></div>";
						echo "		</div>";
						echo "	</div>";
						echo "	<div class='col-md-2'>";
						echo "		<div class='form-group'><label>Color Other</label>";
						echo "		<input type='text' class='form-control' name='Detail[".$val."][color]' placeholder='Color Other' value='".$color."'>";
						echo "		</div>";
						echo "	</div>";
						echo "</div>";
						
						echo "<div class='form-group row' hidden>";
						echo "	<div class='col-md-2'>";
						echo "		<label></label>";
						echo "	</div>";
						echo "	<div class='col-md-2'>";
						echo "		<div class='form-group'><label>Surface</label>";
						echo "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$val."][surface_concave]' value='Y' ".$surface_concave.">Concave</label></div>";
						echo "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$val."][surface_flat]' value='Y' ".$surface_flat.">Flat</label></div>";
						echo "		</div>";
						echo "	</div>";
						echo "	<div class='col-md-2'>";
						echo "		<div class='form-group'><label></label>";
						echo "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$val."][surface_anti_skid]' value='Y' ".$surface_anti_skid.">Anti Skid</label></div>";
						echo "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$val."][surface_chequered_plate]' value='Y' ".$surface_chequered_plate.">Chequered Plate</label></div>";
						echo "		</div>";
						echo "	</div>";
						echo "</div>";

						echo "<div class='form-group row'>";
						echo "	<div class='col-md-2'>";
						echo "		<label>Drawing Customer</label>";
						echo "	</div>";
						echo "	<div class='col-md-5'>";
						// echo "	<input type='file' name='photo_".$val."' id='photo_".$val."' >";
						if(!empty($file_dokumen)){
							echo "<a href='".base_url().$file_dokumen."' target='_blank' class='text-primary' title='Download'>Download File</a>";
						}
						echo "	</div>";
						echo "</div>";

						echo "<div class='form-group row'>";
						echo "	<div class='col-md-2'>";
						echo "		<label>Accessories</label>";
						echo "	</div>";
						echo "	<div class='col-md-6'>";
						echo "		<input type='text' name='Detail[".$val."][accessories]' class='form-control input-md' placeholder='Accessories' value='".$accessories."'>";
						echo "	</div>";
						echo "</div>";

						echo "<div class='form-group row' hidden>";
						echo "	<div class='col-md-2'>";
						echo "		<label>Mesh</label>";
						echo "	</div>";
						echo "	<div class='col-md-2'>";
						echo "		<div class='form-group'>";
						echo "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$val."][mesh_open]' value='Y' ".$mesh_open.">Open Mesh</label></div>";
						echo "		<div class='checkbox'><label><input type='checkbox' name='Detail[".$val."][mesh_closed]' value='Y' ".$mesh_closed.">Closed Mesh</label></div>";
						echo "		</div>";
						echo "	</div>";
						echo "</div>";

						echo "<div class='form-group row'>";
						echo "	<div class='col-md-2'>";
						echo "		<label>Ukuran Jadi</label>";
						echo "	</div>";
						echo "	<div class='col-md-5'>";
						echo "	<table class='table table-striped table-bordered table-hover table-condensed'>";
						echo "		<tr class='bg-blue'>";
						echo "			<th class='text-center' width='30%'>Length</th>";
						echo "			<th class='text-center' width='30%'>Width</th>";
						echo "			<th class='text-center' width='30%'>Qty</th>";
						if(empty($tanda)){
						// echo "			<th class='text-center' width='10%'>#</th>";
						}
						echo "		</tr>";
						
						$getdetailProduct4 = $this->db->get_where('custom_ipp_detail_lainnya',array('category'=>'ukuran jadi','no_ipp'=>$valx['no_ipp'],'no_ipp_code'=>$valx['no_ipp_code']))->result_array();
						$new_number = 0;
						foreach ($getdetailProduct4 as $key => $value) { $new_number++;

							echo "<tr id='headerjadi_".$val."_".$new_number."'>";
								echo "<td align='center'>".number_format($value['length'])."</td>";
								echo "<td align='center'>".number_format($value['width'])."</td>";
								echo "<td align='center'>".number_format($value['order'])."</td>";
								if(empty($tanda)){
								// echo "<td align='center'>";
								// 	echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delPartUkj' title='Delete'><i class='fa fa-close'></i></button>";
								// echo "</td>";
								}
							echo "</tr>";
						}
						if(empty($tanda)){
						// echo "		<tr id='addjadi_".$val."_".$new_number."'>";
						// echo "			<td><button type='button' class='btn btn-sm btn-warning addPartUkj' title='Add Ukuran Jadi'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Ukuran Jadi</button></td>";
						// echo "			<td></td>";
						// echo "			<td></td>";
						// echo "		</tr>";
						}
						echo "	</table>";
						echo "	</div>";
						echo "	<div class='col-md-4'>";
						echo "		<div class='form-group'><label>Keterangan</label>";
						echo "		<textarea class='form-control' name='Detail[".$val."][ket]' placeholder='Keterangan' rows='2'>".$ket."</textarea>";
						echo "		</div>";
						echo "	</div>";
						echo "</div>";


						//penutup div delete
						echo "</div>";
					}
				}
				?>
				<?php if(empty($tanda)){ ?>
				<!-- <div id='add_<?=$val?>'><button type='button' class='btn btn-sm btn-primary addPart' title='Add Permintaan'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Permintaan</button></td></div> -->
				<?php } ?>
			</div>
			<hr>
			<?php if(empty($tanda)){ ?>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="customer">Action</label>
				</div>
				<div class="col-md-4">
					<select id="ajukan_sts" name="ajukan_sts" class="form-control input-md chosen-select">
						<option value="A">Approve</option>
						<option value="N">Reject</option>
					</select>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="customer">Reject Reason</label>
				</div>
				<div class="col-md-4">
					<textarea name='ajukan_sts_reason' id='ajukan_sts_reason' class='form-control input-md' placeholder='Reject reason' rows='2'></textarea>
				</div>
			</div>
			<?php } ?>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="customer"></label>
				</div>
				<div class="col-md-4">
					<?php if(empty($tanda)){ ?>
						<button type="button" class="btn btn-success" name="save" id="approve"><i class="fa fa-check"></i>&nbsp;&nbsp;Approve</button>
					<?php } ?>
					<a href="<?=base_url($this->uri->segment(1));?>" class="btn btn-danger" style='margin-left:5px;' name="back" id="back"><i class="fa fa-reply"></i> Back</a>
				</div>
			</div>

			

		</form>
	</div>
</div>

<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>
<style media="screen">
  .datepicker{
    cursor: pointer;
    padding-left: 12px;
  }
</style>
<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';

	$(document).ready(function(){
		$('.chosen-select').select2();
		$( ".datepicker" ).datepicker();
		$( ".autoNumeric4" ).autoNumeric('init', {mDec: '4', aPad: false});
		$( ".autoNumeric0" ).autoNumeric('init', {mDec: '0', aPad: false});

		$('#approve').click(function(e){
			e.preventDefault();
			var ajukan_sts		 	= $('#ajukan_sts').val();
			var ajukan_sts_reason	= $('#ajukan_sts_reason').val();

			if(ajukan_sts_reason == '' && ajukan_sts == 'N'){
				swal({
					title	: "Error Message!",
					text	: 'Alasan wajib diisi ...',
					type	: "warning"
				});
				return false;
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
						var baseurl = base_url+active_controller+'/confirm'
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
										  timer	: 3000
										});
									window.location.href = base_url + active_controller;
								}else{

									if(data.status == 2){
										swal({
										  title	: "Save Failed!",
										  text	: data.pesan,
										  type	: "warning",
										  timer	: 3000
										});
									}else{
										swal({
										  title	: "Save Failed!",
										  text	: data.pesan,
										  type	: "warning",
										  timer	: 3000
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
