<?= form_open($this->uri->uri_string(),array('id'=>'frm_data','name'=>'frm_data','role'=>'form','class'=>'form-horizontal')) ?>
<?php
$readonly=""; 
if (isset($data->id)) $readonly=" readonly"; ?>
<input type="hidden" id="id" name="id" value="<?php echo (isset($data->id) ? $data->id : ''); ?>">
<div class="tab-content">
	<div class="tab-pane active">
		<div class="box box-primary">
			<div class="box-body">
				<div class="form-group ">
					<label for="wh_code" class="col-sm-2 control-label">Kode Gudang<b class="text-red">*</b></label>
					<div class="col-sm-4">
						<input type="text" class="form-control" id="wh_code" name="wh_code" value="<?php echo (isset($data->wh_code) ? $data->wh_code: ""); ?>" placeholder="Kode Gudang" required<?=$readonly?>>
					</div>
					<label for="wh_name" class="col-sm-2 control-label">Nama Gudang<b class="text-red">*</b></label>
					<div class="col-sm-4">
						<input type="text" class="form-control" id="wh_name" name="wh_name" value="<?php echo (isset($data->wh_name) ? $data->wh_name: ""); ?>" placeholder="Nama Gudang" required>							
					</div>
				</div>
				<div class="form-group ">
					<label for="wh_status" class="col-sm-2 control-label">Status<b class="text-red">*</b></label>
					<div class="col-sm-4">
						<select name="wh_status" id="wh_status" class="form-control" required>
							<option value="">Select An Option</option>
							<?php
							foreach($status as $keys=>$values){
								$selected="";
								if(isset($data->wh_status)){
									if($data->wh_status==$keys) $selected=" selected";
								}
								echo '<option value="'.$keys.'"'.$selected.'>'.$values.'</option>';
							}
							?>
						</select>
					</div>
					<label for="wh_info" class="col-sm-2 control-label">Keterangan</label>
					<div class="col-sm-4">
						<textarea class="form-control" id="wh_info" name="wh_info" placeholder="Keterangan"><?php echo (isset($data->wh_info) ? $data->wh_info: ""); ?></textarea>
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
<script type="text/javascript">
	var url_save = siteurl+'warehouse/save/';
    $('#frm_data').on('submit', function(e){
        e.preventDefault();
		var errors="";
		if($("#wh_code").val()=="") errors="Kode gudang tidak boleh kosong";
		if($("#wh_name").val()=="") errors="Nama gudang tidak boleh kosong";
		if($("#wh_status").val()=="") errors="Status gudang tidak boleh kosong";
		if(errors==""){
			data_save();
		}else{
			swal(errors);
			return false;
		}
    });
</script>
