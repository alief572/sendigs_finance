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
					<label for="nama" class="col-sm-2 control-label">Nama<b class="text-red">*</b></label>
					<div class="col-sm-4">
						<input type="text" class="form-control" id="nama" name="nama" value="<?php echo (isset($data->nama) ? $data->nama: ""); ?>" placeholder="Nama">
					</div>
					<label for="pengelola" class="col-sm-2 control-label">PIC<b class="text-red">*</b></label>
					<div class="col-sm-4">
						<input type="text" class="form-control" id="pengelola" name="pengelola" value="<?php echo (isset($data->pengelola) ? $data->pengelola: ""); ?>" placeholder="PIC" required>							
					</div>
				</div>
				<div class="form-group ">
					<label for="keterangan" class="col-sm-2 control-label">Jenis Pembelian</label>
					<div class="col-sm-4">
						<textarea class="form-control" id="keterangan" name="keterangan" placeholder="Keterangan"><?php echo (isset($data->keterangan) ? $data->keterangan: ""); ?></textarea>
					</div>
					<label for="budget" class="col-sm-2 control-label">Budget<b class="text-red">*</b></label>
					<div class="col-sm-4">
						<input type="text" class="form-control divide" id="budget" name="budget" value="<?php echo (isset($data->budget) ? $data->budget: "0"); ?>" placeholder="Budget" required>							
					</div>
				</div>
				<div class="form-group ">
					<label for="approval" class="col-sm-2 control-label">Approval By<b class="text-red">*</b></label>
					<div class="col-sm-4">
						<select name="approval" id="approval" class="form-control select2" placeholder="Approval">
						<?php
						echo '<option value="">Select an option</option>';
						foreach ($data_approval as $record){
							$selected='';
							if(isset($data->approval)){
								if ($record->username==$data->approval) $selected=' selected';
							}
							echo '<option value="'.$record->username.'" '.$selected.'>'.$record->nama_karyawan.'</option>';
						}
						?>
						</select>
					</div>
					<label for="coa" class="col-sm-2 control-label">COA<b class="text-red">*</b></label>
					<div class="col-sm-4">
						<select name="coa[]" id="coa" class="form-control select2" multiple placeholder="COA">
						<?php
						$arraycoa=array();
						if(isset($data->coa)) $arraycoa=explode(';',$data->coa);
						foreach ($datacoa as $key=>$val){
							$selected='';
							if(isset($data->coa)){
								if (in_array($key, $arraycoa)) {
								  $selected=' selected';
								}
							}
							echo '<option value="'.$key.'" '.$selected.'>'.$val.'</option>';
						}
						?>
						</select>
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
	var url_save = siteurl+'pettycash/save/';
	$('.select2').select2();
	$('.divide').divide();

    $('#frm_data').on('submit', function(e){
        e.preventDefault();
		var errors="";
		if($("#nama").val()=="") errors="Nama tidak boleh kosong";
		if($("#pengelola").val()=="") errors="Pengelola tidak boleh kosong";
		if(errors==""){
			data_save();
		}else{
			swal(errors);
			return false;
		}
    });
</script>
