<?=form_open($this->uri->uri_string(),array('id'=>'frm_data','name'=>'frm_data','role'=>'form','class'=>'form-horizontal'));?>
<input type="hidden" id="id" name="id" value="">
<input type="hidden" id="element_tipe" name="element_tipe" value="<?php echo (isset($data->element_tipe) ? $data->element_tipe : $data_tipe); ?>">
<div class="tab-content">
	<div class="tab-pane active">
		<div class="box box-primary">
			<div class="box-body table-responsive">

				<table class="table table-bordered table-striped">
					<thead>
						<tr>
						<th width="5">#</th>
						<th>Nama</th>
						<th>Spesifikasi</th>
						<th>Brand</th>
						<th>Kurs</th>
						<th>Price reference/unit</th>
						<th>Satuan</th>
						</tr>
					</thead>
					<tbody id="tbl_detail">
					<?php if(!empty($data_material)){
						$data_kurs[0]	= 'Select An Option';
						$idd=0;
						foreach($data_material AS $record){
							$idd++;?>
						<tr>
							<td><input type="checkbox" name="detail_id[]" id="raw_id_<?=$idd?>" value="<?=$idd;?>" checked>
							<input type="hidden" name="element_id_<?=$idd;?>" id="element_id_<?=$idd;?>" value="<?=$record->id_material;?>">
							</td>
							<td><?= $record->nama ?></td>
							<td><?= $record->spec3 ?></td>
							<td><?= $record->spec2 ?></td>
							<td>
								<?php
								echo form_dropdown('element_kurs_'.$idd,$data_kurs, 'IDR', array('id'=>'element_kurs_'.$idd,'class'=>'form-control select2','style'=>'width:100%'));
								?>
							</td>
							<td><input type="text" class="form-control divide" name="element_cost_<?=$idd;?>" id="element_cost_<?=$idd;?>" value="0"></td>
							<td><input type="text" class="form-control" readonly tabindex="-1" name="element_unit_<?=$idd;?>" id="element_unit_<?=$idd;?>" value="<?=$record->satuan;?>"></td>
						</tr>
						<?php }
					}?>
					</tbody>
				</table>
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
		if(errors==""){
			data_save();
		}else{
			swal(errors);
			return false;
		}
    });
</script>
