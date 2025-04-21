<?php
    $ENABLE_ADD     = has_permission('Material_Category.Add');
    $ENABLE_MANAGE  = has_permission('Material_Category.Manage');
    $ENABLE_VIEW    = has_permission('Material_Category.View');
    $ENABLE_DELETE  = has_permission('Material_Category.Delete');

	$id = (!empty($listData[0]->id))?$listData[0]->id:'';
	$kode_currency = (!empty($listData[0]->kode_currency))?$listData[0]->kode_currency:'USD';
	$to_currency = (!empty($listData[0]->to_currency))?$listData[0]->to_currency:'IDR';
	$tanggal = (!empty($listData[0]->tanggal))?$listData[0]->tanggal:date('Y-m-d');
	$kurs = (!empty($listData[0]->kurs))?$listData[0]->kurs:'';

	$status1 = (!empty($listData[0]->status) AND $listData[0]->status == '1')?'checked':'';
	$status2 = (!empty($listData[0]->status) AND $listData[0]->status == '2')?'checked':'';
?>
<div class="box box-primary">
	<div class="box-body">
		<form id="data_form" autocomplete="off">
			<div class="form-group row">
				<div class="col-md-3">
				<label for="">Currency <span class='text-danger'>*</span></label>
				</div>
				<div class="col-md-9">
				<div class="input-group">
					<select name="kode_currency" id="kode_currency" class='chosen-select'>
						<!-- <option value="0">Select Currency</option> -->
						<?php
						foreach ($currency as $key => $value) {
							$selected = ($kode_currency == $value['kode'])?'selected':'';
							echo "<option value='".$value['kode']."' ".$selected.">".strtoupper($value['kode'].' - '.$value['negara'])."</option>";
						}
						?>
					</select>
					<span class="input-group-btn">
					<button type="button" class="btn btn-default btn-flat">To</button>
					</span>
					<select name="to_currency" id="to_currency" class='chosen-select'>
						<!-- <option value="0">Select Currency</option> -->
						<?php
						foreach ($currency as $key => $value) {
							$selected = ($to_currency == $value['kode'])?'selected':'';
							echo "<option value='".$value['kode']."' ".$selected.">".strtoupper($value['kode'].' - '.$value['negara'])."</option>";
						}
						?>
					</select>
					</div>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-3">
				<label for="">Date <span class='text-danger'>*</span></label>
				</div>
				<div class="col-md-4">
				<input type="text" class="form-control text-center datepicker" id="tanggal" name="tanggal" readonly placeholder="Date" value='<?=$tanggal;?>'>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-3">
				<label for="">Rate <span class='text-danger'>*</span></label>
				</div>
				<div class="col-md-4">
				<input type="hidden" class="form-control" id="id" name="id" value='<?=$id;?>'>
					<div class="input-group">
						<span class="input-group-btn">
							<button type="button" class="btn btn-default btn-flat"><span class='text-bold'>1</span> <span class='text-bold' id='cur_dari'><?=$kode_currency;?></span></button>
						</span>
						<input type="text" class="form-control text-center autoNumeric" id="kurs" required name="kurs" placeholder="Kurs" value='<?=$kurs;?>'>
						<span class="input-group-btn">
							<button type="button" class="btn btn-default btn-flat"><span class='text-bold' id='cur_ke'><?=$to_currency;?></span></button>
						</span>
						</div>
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
<style>
	.datepicker{
		cursor: pointer;
	}
</style>
<script>
	$(document).ready(function(){
    	$('.chosen-select').select2({width: '100%'});
		$('.autoNumeric').autoNumeric('init', {mDec: '6', aPad: false});
		$(".datepicker").datepicker({
			format : 'yyyy-mm-dd',
			changeMonth: true,
			changeYear: true,
			maxDate: 0
		});
  	});
</script>
