<?=form_open($this->uri->uri_string(),array('id'=>'frm_data','name'=>'frm_data','role'=>'form','class'=>'form-horizontal'));?>
<input type="hidden" id="id" name="id" value="<?php echo set_value('id', isset($data->id) ? $data->id : ''); ?>">
<input type="hidden" id="element_tipe" name="element_tipe" value="<?php echo (isset($data->element_tipe) ? $data->element_tipe : $data_tipe); ?>">
<div class="tab-content">
	<div class="tab-pane active">
		<div class="box box-primary">
			<div class="box-body">
				<div class="form-group ">
					<label for="element_id" class="col-sm-2 control-label">Nama<b class="text-red">*</b></label>
					<div class="col-sm-4">
						<select name="element_id" id="element_id" class="form-control" onchange="getMaterialDetail()" required readonly>
							<option value="" data-spec1="" data-spec2="">Select An Option</option>
						<?php
						foreach($data_material as $keys){
							$selected="";
							if(isset($data->element_id)){
								if($data->element_id==$keys->id_material){
									$selected=" selected";
								}
							}
							echo '<option value="'.$keys->id_material.'" data-spec1="'.$keys->spec1.'" data-spec2="'.$keys->spec2.'"'.$selected.'>'.$keys->nama.'</option>';
						}
						?>
						</select>
					</div>
					<label for="spec1" class="col-sm-2 control-label">Spesifikasi</label>
					<div class="col-sm-4">
						<input type="text" class="form-control" id="spec3" name="spec3" value="<?php echo (isset($data->spec1) ? $data->spec3: ""); ?>" placeholder="Spesifikasi" readonly tabindex="-1">
					</div>
				</div>
				<div class="form-group ">
					<label for="spec2" class="col-sm-2 control-label">Brand</label>
					<div class="col-sm-4">
						<input type="text" class="form-control" id="spec2" name="spec2" value="<?php echo (isset($data->spec2) ? $data->spec2: ""); ?>" readonly tabindex="-1">
					</div>
					<label for="element_unit" class="col-sm-2 control-label">Satuan<b class="text-red">*</b></label>
					<div class="col-sm-4">
						<select name="element_unit" id="element_unit" class="form-control" required readonly>
							<option value="">Select An Option</option>
						<?php
						if(isset($data_satuan)){
							foreach($data_satuan as $keys){
								$selected="";
								if(isset($data->element_unit)){
									if($data->element_unit==$keys->nama){
										$selected=" selected";
									}
								}
								echo '<option value="'.$keys->nama.'"'.$selected.'>'.$keys->nama.'</option>';
							}
						}
						?>
						</select>
					</div>
				</div>
				<div class="form-group ">
					<label for="element_kurs" class="col-sm-2 control-label">Kurs<b class="text-red">*</b></label>
					<div class="col-sm-4">
							<?php
							$data_kurs[0]	= 'Select An Option';
							echo form_dropdown('element_kurs',$data_kurs, set_value('element_kurs', isset($data->element_kurs) ? $data->element_kurs: 'IDR'), array('id'=>'element_kurs','class'=>'form-control select2'));
							?>
					</div>
					<label for="element_cost" class="col-sm-2 control-label">Price reference/unit<b class="text-red">*</b></label>
					<div class="col-sm-4">
						<input type="text" class="form-control divide" id="element_cost" name="element_cost" value="<?php echo (isset($data->element_cost) ? $data->element_cost: "0"); ?>" placeholder="Nilai" required>
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
	$(document).ready(function(){
		$('.divide').divide();
		$('.select2').select2();
	});
	var url_save = siteurl+'price_ref/others_save/';
    $('#frm_data').on('submit', function(e){
        e.preventDefault();
		var errors="";
		if($("#element_id").val()=="") errors="Nama tidak boleh kosong";
		if($("#element_cost").val()==0) errors="Price reference/unit tidak boleh 0";
		if($("#element_unit").val()=="") errors="Satuan tidak boleh kosong";
		if($("#element_kurs").val()=="0") errors="Kurs tidak boleh kosong";
		if(errors==""){
			data_save();
		}else{
			swal(errors);
			return false;
		}
    });

	function getMaterialDetail(){
		var spec1=$("#element_id").find(':selected').attr('data-spec1');
		var spec2=$("#element_id").find(':selected').attr('data-spec2');
		$("#spec1").val(spec1);
		$("#spec2").val(spec2);
		getSatuan();
	}

	function getSatuan(){
		var element_id = $("#element_id").val();
		$.ajax({
			url : siteurl+"price_ref/material_search/"+element_id, type : "POST", dataType : "json", cache : false,
			success : function(data){
				if(data!=''){
					$('#element_unit').empty();
					datacombo="<option value=''>Select An Option</option>";
					for (i = 0; i < data.length; ++i) {
						datacombo+="<option value='"+data[i].nama+"'>"+data[i].nama+"</option>";
					}
					$('#element_unit').html(datacombo);
				}
				console.log(data);
			}
		});
	}

</script>
