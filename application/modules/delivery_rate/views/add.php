<?php
	$id 				= (!empty($listData[0]->id))?$listData[0]->id:'';
	$type 				= (!empty($listData[0]->type))?$listData[0]->type:'';
	$id_country 		= (!empty($listData[0]->id_country))?$listData[0]->id_country:'';
	$category 			= (!empty($listData[0]->category))?$listData[0]->category:'';
	$shipping_method 	= (!empty($listData[0]->shipping_method))?$listData[0]->shipping_method:'';
	$transport_type 	= (!empty($listData[0]->transport_type))?$listData[0]->transport_type:'';
	$area_tujuan 		= (!empty($listData[0]->area_tujuan))?$listData[0]->area_tujuan:'';
	$price				= (!empty($listData[0]->price))?$listData[0]->price:'';

	$type1				= (!empty($listData[0]->type) AND $listData[0]->type == 'local')?'selected':'';
	$type2 				= (!empty($listData[0]->type) AND $listData[0]->type == 'export')?'selected':'';

?>
<div class="box box-primary">
	<div class="box-body">
		<form id="data_form" method="post"  autocomplete="off" enctype='multiple/form-data'>
			<div class="form-group row">
				<div class="col-md-2">
				<label for="">Type <span class='text-danger'>*</span></label>
				</div>
				<div class="col-md-4">
				<select name="type" id="type" class='chosen-select'>
					<option value="local" <?=$type1;?>>Local</option>
					<option value="export" <?=$type2;?>>Export</option>
				</select>
				</div>
			</div>
			<div class="form-group row export">
				<div class="col-md-2">
				<label for="">Country <span class='text-danger'>*</span></label>
				</div>
				<div class="col-md-4">
				<select name="id_country" id="id_country" class='chosen-select'>
					<option value="0">Select Country</option>
					<?php
					foreach ($country as $key => $value) {
						$selected = ($id_country == $value['country_code'])?'selected':'';
						echo "<option value='".$value['country_code']."' ".$selected.">".strtoupper($value['country_name'])."</option>";
					}
					?>
				</select>
				<button type="button" id="addCountry" style="font-weight: bold; font-size: 12px; margin-top: 5px; color: #175477;">Add Country</button>
				</div>
				<div class="col-md-2">
						<label for="">Shipping Method <span class='text-danger'>*</span></label>
					</div>
					<div class="col-md-4">
						<select name="shipping_method" id="shipping_method" class='chosen-select'>
							<option value="0">Select Method</option>
							<?php
							foreach ($shipping_method_ as $key => $value) {
								$selected = ($shipping_method == $value['value'])?'selected':'';
								echo "<option value='".$value['value']."' ".$selected.">".strtoupper($value['view'])."</option>";
							}
							?>
						</select>
					</div>
			</div>
			<div class="form-group row local">
				<div class="col-md-2">
					<label for="">Delivery Category <span class='text-danger'>*</span></label>
				</div>
				<div class="col-md-4">
					<select name="category" id="category" class='chosen-select'>
						<option value="0">Select Category</option>
						<?php
						foreach ($category_lokal as $key => $value) {
							$selected = ($category == $value['value'])?'selected':'';
							echo "<option value='".$value['value']."' ".$selected.">".strtoupper($value['view'])."</option>";
						}
					?>
					</select>
				</div>
				<div class="col-md-2">
					<label>Transport Type</label>
				</div>
				<div class="col-md-4">
					<input type="hidden" class="form-control" id="id" name="id" value='<?=$id;?>'>
					<input type="text" class="form-control" id="transport_type" name="transport_type" value='<?=$transport_type;?>' placeholder="Transport Type">
				</div>
			</div>
			<div class="form-group row local">
				<div class="col-md-2">
				<label for="">Destination Area</label>
				</div>
				<div class="col-md-4">
					<input type="text" class="form-control" id="area_tujuan" name="area_tujuan" value='<?=$area_tujuan;?>' placeholder="Destination Area">
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label>Price IDR <span class='text-danger'>*</span></label>
				</div>
				<div class="col-md-4">
					<input type="text" class="form-control maskM" id="price" name="price" value='<?=$price;?>' placeholder="Price">
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2"></div>
				<div class="col-md-10">
				<button type="submit" class="btn btn-primary" name="save" id="save"><i class="fa fa-save"></i> Save</button>
				</div>
			</div>
		</form>
	</div>
</div>

<script>
	$(document).ready(function(){
    	$('.chosen-select').select2({width: '100%'});
		$('.maskM').autoNumeric();

		let type_delivery = $('#type').val()
		if(type_delivery == 'local'){
			$('.local').show();
			$('.export').hide();
		}
		else{
			$('.local').hide();
			$('.export').show();
		}

		$(document).on('change','#type',function(){
			let type_delivery = $('#type').val()
			if(type_delivery == 'local'){
				$('.local').show();
				$('.export').hide();
			}
			else{
				$('.local').hide();
				$('.export').show();
			}
		});
  	});
</script>
