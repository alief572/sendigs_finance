<?php
	$id = (!empty($listData[0]->id))?$listData[0]->id:'';
	$nm_category = (!empty($listData[0]->nm_category))?$listData[0]->nm_category:'';
	$description = (!empty($listData[0]->description))?$listData[0]->description:'';
?>
<div class="box box-primary">
	<div class="box-body">
		<form id="data_form" autocomplete="off">
			<div class="form-group row">
				<div class="col-md-3">
				<label for="">Category <span class='text-danger'>*</span></label>
				</div>
				<div class="col-md-9">
				<input type="hidden" class="form-control" id="id" name="id" value='<?=$id;?>'>
				<input type="text" class="form-control" id="nm_category" required name="nm_category" placeholder="Category" value='<?=$nm_category;?>'>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-3">
				<label for="">Description</label>
				</div>
				<div class="col-md-9">
				<textarea name="description" id="description" class="form-control" rows="3"><?=$description;?></textarea>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-3"></div>
				<div class="col-md-9">
				<button type="submit" class="btn btn-primary" name="save" id="save"><i class="fa fa-save"></i> Save</button>
				</div>
			</div>
		</form>
	</div>
</div>
