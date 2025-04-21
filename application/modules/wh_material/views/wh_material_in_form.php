<?=form_open($this->uri->uri_string(),array('id'=>'frm_data','name'=>'frm_data','role'=>'form','class'=>'form-horizontal'));?>
<?php if(isset($views)) {?>
<div class="tab-content">
	<div class="tab-pane active">
		<div class="box box-primary">
			<div class="box-body">
				<div class="form-group ">
					<label class="col-sm-2 control-label">No Dokumen<b class="text-red">*</b></label>
					<div class="col-sm-4">
						<input type="text" class="form-control" id="doc_no" name="doc_no" value="<?php echo (isset($data->doc_no) ? $data->doc_no: ""); ?>" placeholder="Automatic" readonly tabindex="-1">
					</div>
					<label class="col-sm-2 control-label">No PO<b class="text-red">*</b></label>
					<div class="col-sm-4">
						<?php
						$data_po[0]	= 'Select An Option';
						echo form_dropdown('reference_no',$data_po,(isset($data->reference_no)?$data->reference_no:'0'),array('id'=>'reference_no','required'=>'required','class'=>'form-control select2'));
						?>
					</div>
				</div>

				<div class="form-group ">
					<label class="col-sm-2 control-label">Tanggal Transaksi<b class="text-red">*</b></label>
					<div class="col-sm-4">
						<input type="text" class="form-control tanggal" id="trans_date" name="trans_date" value="<?php echo (isset($data->trans_date) ? $data->trans_date: date("Y-m-d")); ?>" placeholder="Tanggal transaksi" required>
					</div>
					<label for="wh_code" class="col-sm-2 control-label">Gudang<b class="text-red">*</b></label>
					<div class="col-sm-4">
						<?php
						$warehouse[0]	= 'Select An Option';
						echo form_dropdown('wh_code',$warehouse,  (isset($data->wh_code)?$data->wh_code:'0'),array('id'=>'wh_code','required'=>'required','class'=>'form-control select2'));
						?>
					</div>
				</div>
				<div class="form-group ">
					<label class="col-sm-2 control-label">PIC</label>
					<div class="col-sm-4">
						<input type="text" class="form-control" id="pic" name="pic" value="<?php echo (isset($data->pic) ? $data->pic: ""); ?>" placeholder="PIC" >
					</div>
					<label class="col-sm-2 control-label">Keterangan</label>
					<div class="col-sm-4">
						<textarea class="form-control" id="info" name="info"><?php echo (isset($data->info) ? $data->info: ""); ?></textarea>
					</div>
				</div>
			</div>
			<div class="box-footer table-responsive">
			<table class="table table-bordered table-striped">
				<thead>
					<th width="5">#</th>
					<th>Material</th>
					<th>Qty Order</th>
					<th>Qty Receive</th>
					<th>Unit</th>
				</thead>
				<tbody id="tbl_detail">
				<?php if(!empty($data_material)){
					$idd=0;
					foreach($data_material AS $record){
						$idd++;?>
					<tr>
						<td><input type="checkbox" name="detail_id[]" id="raw_id_<?=$idd?>" value="<?=$idd;?>" checked>
						<input type="hidden" name="material_id_<?=$idd;?>" id="material_id_<?=$idd;?>" value="<?=$record->material_id;?>">
						<input type="hidden" name="material_price_<?=$idd;?>" id="material_price_<?=$idd;?>" value="<?=$record->material_price;?>">
						<input type="hidden" name="id_po_detail_<?=$idd;?>" id="id_po_detail_<?=$idd;?>" value="<?=$record->id_po;?>">
						</td>
						<td><?= $record->nama ?></td>
						<td><input type="text" class="form-control" readonly tabindex="-1" name="material_order_<?=$idd;?>" id="material_order_<?=$idd;?>" value="<?=$record->material_order;?>"></td>
						<td><input type="text" class="form-control divide" name="material_qty_<?=$idd;?>" id="material_qty_<?=$idd;?>" value="<?=$record->material_qty;?>"></td>
						<td><input type="text" class="form-control" readonly tabindex="-1" name="material_unit_<?=$idd;?>" id="material_unit_<?=$idd;?>" value="<?=$record->material_unit;?>"></td>
					</tr>
					<?php }
				}?>
				</tbody>
			</table>
			</div>
		</div>
	</div>
	<a class="btn btn-warning btn-sm" onclick="cancel()"><i class="fa fa-reply">&nbsp;</i>Kembali</a>
</div>
<script src="<?= base_url('assets/js/number-divider.min.js')?>"></script>
<script type="text/javascript">
	$('.divide').divide();
	$(".tab-content :input").attr("disabled", true);
</script>
<?php }else{?>
<input type="hidden" id="id" name="id" value="<?php echo set_value('id', isset($data->id) ? $data->id : ''); ?>">
<input type="hidden" id="trans_type" name="trans_type" value="IN">
<input type="hidden" id="status" name="status" value="<?php echo (isset($data->status) ? $data->status : '0'); ?>">
<div class="tab-content">
	<div class="tab-pane active">
		<div class="box box-primary">
			<div class="box-body">
				<div class="form-group ">
					<label class="col-sm-2 control-label">No Dokumen<b class="text-red">*</b></label>
					<div class="col-sm-4">
						<input type="text" class="form-control" id="doc_no" name="doc_no" value="<?php echo (isset($data->doc_no) ? $data->doc_no: ""); ?>" placeholder="Automatic" readonly tabindex="-1">
					</div>
					<label class="col-sm-2 control-label">No PO<b class="text-red">*</b></label>
					<div class="col-sm-4">
						<?php
						$data_po[0]	= 'Select An Option';
						echo form_dropdown('reference_no',$data_po,(isset($data->reference_no)?$data->reference_no:'0'),array('id'=>'reference_no','required'=>'required','class'=>'form-control select2'));
						?>
					</div>
				</div>

				<div class="form-group ">
					<label class="col-sm-2 control-label">Tanggal Transaksi<b class="text-red">*</b></label>
					<div class="col-sm-4">
						<input type="text" class="form-control tanggal" id="trans_date" name="trans_date" value="<?php echo (isset($data->trans_date) ? $data->trans_date: date("Y-m-d")); ?>" placeholder="Tanggal transaksi" required>
					</div>
					<label for="wh_code" class="col-sm-2 control-label">Gudang<b class="text-red">*</b></label>
					<div class="col-sm-4">
						<?php
						$warehouse[0]	= 'Select An Option';
						echo form_dropdown('wh_code',$warehouse,  (isset($data->wh_code)?$data->wh_code:'0'),array('id'=>'wh_code','required'=>'required','class'=>'form-control select2'));
						?>
					</div>
				</div>
				<div class="form-group ">
					<label class="col-sm-2 control-label">PIC</label>
					<div class="col-sm-4">
						<input type="text" class="form-control" id="pic" name="pic" value="<?php echo (isset($data->pic) ? $data->pic: ""); ?>" placeholder="PIC" >
					</div>
					<label class="col-sm-2 control-label">Keterangan</label>
					<div class="col-sm-4">
						<textarea class="form-control" id="info" name="info"><?php echo (isset($data->info) ? $data->info: ""); ?></textarea>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10">
						<?php
						if(isset($data)){
							if($data->status==0){
								echo '<button type="button" name="Approve" class="btn btn-primary btn-sm" id="approve" onclick="data_approve()"><i class="fa fa-save">&nbsp;</i>Approve</button>';
							}
						}
						?>
						<button type="submit" name="save" class="btn btn-success btn-sm" id="submit"><i class="fa fa-save">&nbsp;</i>Simpan</button>
						<a class="btn btn-warning btn-sm" onclick="location.reload();return false;"><i class="fa fa-reply">&nbsp;</i>Batal</a>
					</div>
				</div>
			</div>
			<div class="box-footer table-responsive">
			<table class="table table-bordered table-striped">
				<thead>
					<th width="5">#</th>
					<th>Material</th>
					<th>Qty Order</th>
					<th>Qty Receive</th>
					<th>Unit
					<div class="pull-right"><a class="btn btn-success btn-xs" href="javascript:void(0)" title="Tambah" onclick="add_material()" id="add-material"><i class="fa fa-plus">&nbsp;</i>Tambah</a></div></th>
				</thead>
				<tbody id="tbl_detail">
				<?php if(!empty($data_material)){
					$idd=0;
					foreach($data_material AS $record){
						$idd++;?>
					<tr>
						<td><input type="checkbox" name="detail_id[]" id="raw_id_<?=$idd?>" value="<?=$idd;?>" checked>
						<input type="hidden" name="material_id_<?=$idd;?>" id="material_id_<?=$idd;?>" value="<?=$record->material_id;?>">
						<input type="hidden" name="material_price_<?=$idd;?>" id="material_price_<?=$idd;?>" value="<?=$record->material_price;?>">
						<input type="hidden" name="id_po_detail_<?=$idd;?>" id="id_po_detail_<?=$idd;?>" value="<?=$record->id_po;?>">
						</td>
						<td><?= $record->nama ?></td>
						<td><input type="text" class="form-control" readonly tabindex="-1" name="material_order_<?=$idd;?>" id="material_order_<?=$idd;?>" value="<?=$record->material_order;?>"></td>
						<td><input type="text" class="form-control divide" name="material_qty_<?=$idd;?>" id="material_qty_<?=$idd;?>" value="<?=$record->material_qty;?>"></td>
						<td><input type="text" class="form-control" readonly tabindex="-1" name="material_unit_<?=$idd;?>" id="material_unit_<?=$idd;?>" value="<?=$record->material_unit;?>"></td>
					</tr>
					<?php }
				}?>
				<tr><td></td><td></td><td></td><td></td></tr>
				</tbody>
			</table>
			</div>
		</div>
	</div>
</div>
<?= form_close() ?>
<script src="<?= base_url('assets/js/number-divider.min.js')?>"></script>
<script type="text/javascript">
	var url_save = siteurl+'wh_material/save_in/';
	var url_list_detail = siteurl+'wh_material/add_material_in/';
	var url_approve = siteurl+'wh_material/approve_in/';
	$(document).ready(function(){
		$('.select2').select2();
		$('.divide').divide();
		<?php echo (isset($data) ? '' : '$("#add-material").addClass("hidden");'); ?>;
	});
    $('#frm_data').on('submit', function(e){
        e.preventDefault();
		var errors="";
		if($("#reference_no").val()=="") errors="No Reference tidak boleh kosong";
		if($("#wh_code").val()=="0") errors="Gudang tidak boleh kosong";
		if($("#trans_date").val()=="") errors="Tanggal Transaksi tidak boleh kosong";
		if(errors==""){
			data_save_self();
		}else{
			swal(errors);
			return false;
		}
    });

	$(function () {
		$(".tanggal").datepicker({
			todayHighlight: true,
			format : "yyyy-mm-dd",
			showInputs: true,
			autoclose:true
		});
	});

	function add_material(){
		id=$("#id").val();
		$.ajax({
			url : url_list_detail+id, type : "POST", cache : false,
			success : function(data){
				if(data!=''){
					$("#add-material").addClass("hidden");
					$("#tbl_detail").after(data);
					$(".divide").divide();
				}else{
					swal("Data tidak ditemukan");
				}
//				console.log(data);
			}
		});
	}
	function data_approve(){
		swal({
		  title: "Anda Yakin?",
		  text: "Data Akan Disetujui!",
		  type: "info",
		  showCancelButton: true,
		  confirmButtonText: "Ya, setuju!",
		  cancelButtonText: "Tidak!",
		  closeOnConfirm: false,
		  closeOnCancel: true
		},
		function(isConfirm){
		  if (isConfirm) {
			id=$("#id").val();
			$.ajax({
				url: url_approve+id,
				dataType : "json",
				type: 'POST',
				success: function(msg){
					if(msg['save']=='1'){
						swal({
							title: "Sukses!",
							text: "Data Berhasil Di Setujui",
							type: "success",
							timer: 1500,
							showConfirmButton: false
						});
						window.location.reload();
					} else {
						swal({
							title: "Gagal!",
							text: "Data Gagal Di Setujui",
							type: "error",
							timer: 1500,
							showConfirmButton: false
						});
					};
					console.log(msg);
				},
				error: function(msg){
					swal({
						title: "Gagal!",
						text: "Ajax Data Gagal Di Proses",
						type: "error",
						timer: 1500,
						showConfirmButton: false
					});
					console.log(msg);
				}
			});
		  }
		});
	}
</script>
<?php } ?>
