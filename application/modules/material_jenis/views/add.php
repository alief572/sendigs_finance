<?php
    $ENABLE_ADD     = has_permission('Material_Jenis.Add');
    $ENABLE_MANAGE  = has_permission('Material_Jenis.Manage');
    $ENABLE_VIEW    = has_permission('Material_Jenis.View');
    $ENABLE_DELETE  = has_permission('Material_Jenis.Delete');

	$id = (!empty($listData[0]->id))?$listData[0]->id:'';
	$code_lv1 = (!empty($listData[0]->code_lv1))?$listData[0]->code_lv1:'';
	$code_lv2 = (!empty($listData[0]->code_lv2))?$listData[0]->code_lv2:'';
	$code = (!empty($listData[0]->code_lv2))?$listData[0]->code_lv3:'';
	$nama = (!empty($listData[0]->nama))?$listData[0]->nama:'';

	$status1 = (!empty($listData[0]->status) AND $listData[0]->status == '1')?'checked':'';
	$status2 = (!empty($listData[0]->status) AND $listData[0]->status == '2')?'checked':'';
?>
<div class="box box-primary">
	<div class="box-body">
		<form id="data_form" autocomplete="off">
			<div class="form-group row">
				<div class="col-md-3">
				<label for="">Material Type <span class='text-danger'>*</span></label>
				</div>
				<div class="col-md-9">
				<select name="code_lv1" id="code_lv1" class='chosen-select'>
					<option value="0">Select Material Type</option>
					<?php
					foreach ($listLevel1 as $key => $value) {
						$selected = ($code_lv1 == $value['code_lv1'])?'selected':'';
						echo "<option value='".$value['code_lv1']."' ".$selected.">".strtoupper($value['nama'])."</option>";
					}
					?>
				</select>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-3">
				<label for="">Material Category <span class='text-danger'>*</span></label>
				</div>
				<div class="col-md-9">
				<select name="code_lv2" id="code_lv2" class='chosen-select'>
					<?php
					if(!empty($id) AND !empty($listLevel2)){
						echo "<option value='0'>Select Material Category</option>";
						foreach ($listLevel2 as $key => $value) {
							$selected = ($code_lv2 == $value['code_lv2'])?'selected':'';
							echo "<option value='".$value['code_lv2']."' ".$selected.">".strtoupper($value['nama'])."</option>";
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
				<div class="col-md-3">
				<label for="">Material Jenis <span class='text-danger'>*</span></label>
				</div>
				<div class="col-md-9">
				<input type="hidden" class="form-control" id="id" name="id" value='<?=$id;?>'>
				<input type="hidden" class="form-control" id="code" name="code" value='<?=$code;?>'>
				<input type="text" class="form-control" id="nama" required name="nama" placeholder="Material Type" value='<?=$nama;?>'>
				</div>
			</div>
			<?php if(!empty($id)){ ?>
			<div class="form-group row">
				<div class="col-md-3">
					<label for="">Status</label>
				</div>
				<div class="col-md-4">
					<label>
					<input type="radio" class="radio-control" name="status" value="1" <?=$status1;?>> Aktif
					</label>
					&nbsp &nbsp &nbsp
					<label>
					<input type="radio" class="radio-control" name="status" value="0" <?=$status2;?>> Non-Aktif
					</label>
				</div>
			</div>
			<?php } ?>
			<div class="form-group row">
				<div class="col-md-3"></div>
				<div class="col-md-9">
				<button type="submit" class="btn btn-primary" name="save" id="save"><i class="fa fa-save"></i> Save</button>
				</div>
			</div>
		</form>
	</div>
</div>

<script>
	$(document).ready(function(){
    	$('.chosen-select').select2({width: '100%'});
  	});
</script>
