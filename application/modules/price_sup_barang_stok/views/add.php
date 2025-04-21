<?php
	$id 		= (!empty($listData[0]->id))?$listData[0]->id:'';
	$spec 		= (!empty($listData[0]->spec))?$listData[0]->spec:'';
	$nama 		= (!empty($listData[0]->stock_name))?$listData[0]->stock_name:'';
	$status_app = (!empty($listData[0]->status_app))?$listData[0]->status_app:'';
	$kurs 		= (!empty($listData[0]->kurs))?$listData[0]->kurs:'';

	$price_ref 			= (!empty($listData[0]->price_ref))?$listData[0]->price_ref:'';
	$price_ref_high 	= (!empty($listData[0]->price_ref_high))?$listData[0]->price_ref_high:'';
	$price_ref_usd 		= (!empty($listData[0]->price_ref_usd))?$listData[0]->price_ref_usd:'';
	$price_ref_high_usd = (!empty($listData[0]->price_ref_high_usd))?$listData[0]->price_ref_high_usd:'';

	$price_ref_new 	= '';
	$price_ref_high_new 	= '';
	$price_ref_new_usd 	= '';
	$price_ref_high_new_usd 	= '';
	$note 			= '';
	$upload_file 	= '';

	$expired1 	= '';
	$expired3 	= '';
	$expired6 	= '';
	$expired12 	= '';

	if($status_app == 'Y'){
		
		$price_ref_new 		= (!empty($listData[0]->price_ref_new))?$listData[0]->price_ref_new:'';
		$price_ref_high_new = (!empty($listData[0]->price_ref_high_new))?$listData[0]->price_ref_high_new:'';
		$price_ref_new_usd 		= (!empty($listData[0]->price_ref_new_usd))?$listData[0]->price_ref_new_usd:'';
		$price_ref_high_new_usd = (!empty($listData[0]->price_ref_high_new_usd))?$listData[0]->price_ref_high_new_usd:'';
		$note 			= (!empty($listData[0]->note))?$listData[0]->note:'';
		$upload_file 	= (!empty($listData[0]->upload_file))?$listData[0]->upload_file:'';

		$expired1 = (!empty($listData[0]->price_ref_new_expired) AND $listData[0]->price_ref_new_expired == '1')?'selected':'';
		$expired3 = (!empty($listData[0]->price_ref_new_expired) AND $listData[0]->price_ref_new_expired == '3')?'selected':'';
		$expired6 = (!empty($listData[0]->price_ref_new_expired) AND $listData[0]->price_ref_new_expired == '6')?'selected':'';
		$expired12 = (!empty($listData[0]->price_ref_new_expired) AND $listData[0]->price_ref_new_expired == '12')?'selected':'';
	}
?>
<div class="box box-primary">
	<div class="box-body">
		<form id="data_form" method="post"  autocomplete="off" enctype='multiple/form-data'>
			<div class="form-group row">
				<div class="col-md-2">
				<label for="">Material Master</label>
				</div>
				<div class="col-md-10">
				<input type="hidden" class="form-control" id="id" name="id" value='<?=$id;?>'>
				<input type="text" class="form-control" id="nama" required name="nama" placeholder="Material Type" value='<?=$nama;?>' readonly>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
				<label for="">Spesification</label>
				</div>
				<div class="col-md-10">
				<input type="text" class="form-control" id="spec" required name="spec" placeholder="Spesification" value='<?=$spec;?>' readonly>
				</div>
			</div>
			<hr>
			<div class="form-group">
				<div class="col-md-2"></div>
				<div class="col-md-5">
					<label class='text-red'>Lower Price</label>
				</div>
				<div class="col-md-5">
					<label class='text-success'>Higher Price</label>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label>Before</label>
				</div>
				<div class="col-md-5">
					<div class='input-group'>
						<span class="input-group-btn">
							<button type="button" class="btn btn-default btn-flat">IDR</button>
						</span>
						<input type="text" class="form-control text-center autoNumeric" id="price_ref" name="price_ref" value='<?=$price_ref;?>' readonly>
						<span class="input-group-btn">
							<button type="button" class="btn btn-default btn-flat">USD</button>
						</span>
						<input type="text" class="form-control text-center autoNumeric6" id="price_ref_usd" name="price_ref_usd" value='<?=$price_ref_usd;?>' readonly>
					</div>
					
				</div>
				<div class="col-md-5">
					<div class='input-group'>
						<span class="input-group-btn">
							<button type="button" class="btn btn-default btn-flat">IDR</button>
						</span>
						<input type="text" class="form-control text-center autoNumeric" id="price_ref_high" name="price_ref_high" value='<?=$price_ref_high;?>' readonly>
						<span class="input-group-btn">
							<button type="button" class="btn btn-default btn-flat">USD</button>
						</span>
						<input type="text" class="form-control text-center autoNumeric6" id="price_ref_high_usd" name="price_ref_high_usd" value='<?=$price_ref_high_usd;?>' readonly>
					</div>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label>After <span class='text-danger'>*</span></label>
				</div>
				<div class="col-md-5">
					<div class='input-group'>
						<span class="input-group-btn">
							<button type="button" class="btn btn-default btn-flat">IDR</button>
						</span>
						<input type="text" class="form-control text-center autoNumeric" id="price_ref_new" required name="price_ref_new" value='<?=$price_ref_new;?>'>
						<span class="input-group-btn">
							<button type="button" class="btn btn-default btn-flat">USD</button>
						</span>
						<input type="text" class="form-control text-center autoNumeric6" id="price_ref_new_usd" name="price_ref_new_usd" value='<?=$price_ref_new_usd;?>'>
					</div>
				</div>
				<div class="col-md-5">
					<div class='input-group'>
						<span class="input-group-btn">
							<button type="button" class="btn btn-default btn-flat">IDR</button>
						</span>
						<input type="text" class="form-control text-center autoNumeric" id="price_ref_high_new" required name="price_ref_high_new" value='<?=$price_ref_high_new;?>'>
						<span class="input-group-btn">
							<button type="button" class="btn btn-default btn-flat">USD</button>
						</span>
						<input type="text" class="form-control text-center autoNumeric6" id="price_ref_high_new_usd" name="price_ref_high_new_usd" value='<?=$price_ref_high_new_usd;?>'>
					</div>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label>Expired <span class='text-danger'>*</span></label>
				</div>
				<div class="col-md-5">
					<select id="price_ref_expired" name="price_ref_expired" class="form-control input-md chosen-select" required>
						<option value="0">Select An Expired</option>
						<option value="1" <?=$expired1;?>>1 Bulan</option>
						<option value="3" <?=$expired3;?>>3 Bulan</option>
						<option value="6" <?=$expired6;?>>Semester</option>
						<option value="12" <?=$expired12;?>>Tahunan</option>
					</select>
				</div>
				<div class="col-md-1">
					<label>Kurs</label>
				</div>
				<div class="col-md-4">
					<div class='input-group'>
						<input type="text" class="form-control text-center autoNumeric" id="kurs" required name="kurs" value='<?=$kurs;?>'>
						<!-- <span class="input-group-btn">
							<button type="button" class="btn btn-success btn-flat" data-id='<?=$id;?>' id='update-kurs'>Update Kurs</button>
						</span> -->
					</div>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label>File Evidance <span class='text-danger'>*</span></label>
				</div>
				<div class="col-md-5">
					<div class="form-group">
						<input type="file" name='photo' id="photo" required>	
					</div>
					<?php if(!empty($upload_file)){ ?>
						<a href='<?=base_url().$upload_file;?>' target='_blank' class="help-block" title='Download'>Download File</a>
					<?php } ?>
				</div>
			</div>
			<hr>
			<div class="form-group row">
				<div class="col-md-2">
					<label>Note</label>
				</div>
				<div class="col-md-10">
					<textarea class="form-control" id="note" name="note" row='3' placeholder="Note"><?=$note;?></textarea>
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
		$('.autoNumeric').autoNumeric('init', {mDec: '0', aPad: false});
		$(".autoNumeric6").autoNumeric('init', {mDec: '6', aPad: false});
  	});
</script>
