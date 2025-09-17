<?= form_open($this->uri->uri_string(),array('id'=>'frm_data','name'=>'frm_data','role'=>'form','class'=>'form-horizontal')) ?>
<input type="hidden" id="id" name="id" value="<?php echo set_value('id', isset($data->id) ? $data->id : ''); ?>">
<div class="tab-content">
	<div class="tab-pane active">
		<div class="box box-primary">
			<div class="box-body">
				<div class="form-group ">
					<label for="element_name" class="col-sm-2 control-label">Elemen cost MP<b class="text-red">*</b></label>
					<div class="col-sm-4">
						<input type="text" class="form-control" id="element_name" name="element_name" value="<?php echo (isset($data->element_name) ? $data->element_name: ""); ?>" placeholder="Elemen cost MP" required>
					</div>
					<label for="element_info" class="col-sm-2 control-label">Keterangan</label>
					<div class="col-sm-4">
						<textarea class="form-control" id="element_info" name="element_info" placeholder="Keterangan"><?php echo (isset($data->element_info) ? $data->element_info: ""); ?></textarea>
					</div>
				</div>
				<div class="form-group ">
					<label for="element_cost" class="col-sm-2 control-label">Nilai<b class="text-red">*</b></label>
					<div class="col-sm-4">
						<input type="text" class="form-control divide" id="element_cost" name="element_cost" value="<?php echo (isset($data->element_cost) ? $data->element_cost: "0"); ?>" placeholder="Nilai" required onblur="getInHour()">							
					</div>
					<label for="element_unit" class="col-sm-2 control-label">Satuan<b class="text-red">*</b></label>
					<div class="col-sm-4">
						<select name="element_unit" id="element_unit" class="form-control" onchange="getInHour()" required>
							<option value="" data-id="0">Select An Option</option>
						<?php
						foreach($satuan as $keys=>$values){
							$selected="";
							if(isset($data->element_unit)){
								if($data->element_unit==$keys){
									$selected=" selected";
								}
							}
							echo '<option value="'.$keys.'" data-id="'.$values.'"'.$selected.'>'.$keys.'</option>';
						}
						?>
						</select>
					</div>
				</div>
				<div class="form-group ">
					<label for="element_in_hour" class="col-sm-2 control-label">Biaya per jam</label>
					<div class="col-sm-4">
						<input type="text" class="form-control divide" id="element_in_hour" name="element_in_hour" value="<?php echo (isset($data->element_in_hour) ? $data->element_in_hour: "0"); ?>" placeholder="Biaya per jam" readonly tabindex="-1">
					</div>
					<label for="element_use" class="col-sm-2 control-label">Biaya dipakai<b class="text-red">*</b></label>
					<div class="col-sm-4">
						<input type="text" class="form-control divide" id="element_use" name="element_use" value="<?php echo (isset($data->element_use) ? $data->element_use: "0"); ?>">
					</div>
				</div>
				<div class="box-footer">
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10">
							<button type="submit" name="save" class="btn btn-success btn-sm" id="submit"><i class="fa fa-save">&nbsp;</i>Simpan</button>
							<a class="btn btn-warning btn-sm" onclick="cancel()"><i class="fa fa-reply">&nbsp;</i>Batal</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?= form_close() ?>
<script src="<?= base_url('assets/js/number-divider.min.js')?>"></script>
<script type="text/javascript">
	$('.divide').divide();
	var url_save = siteurl+'price_ref/mp_save/';
    $('#frm_data').on('submit', function(e){
        e.preventDefault();
		var errors="";
		if($("#element_name").val()=="") errors="Elemen cost MP tidak boleh kosong";
		if($("#element_in_hour").val()==0) errors="Biaya per jam tidak boleh 0";
		if(parseFloat($("#element_use").val())<parseFloat($("#element_in_hour").val())) errors="Biaya dipakai harus lebih besar atau sama dengan biaya per jam";
		if(errors==""){
			data_save();
		}else{
			swal(errors);
			return false;
		}
    });

	function getInHour(){
		var unit=$("#element_unit").find(':selected').attr('data-id');
		var cost=$("#element_cost").val();
		if(unit==0){
			costPerHour=0;
		}else{
			costPerHour=parseInt(parseInt(cost)/parseInt(unit));
		}
		$("#element_in_hour").val(costPerHour);
		$("#element_use").val(costPerHour);
	}
</script>
