<?=form_open($this->uri->uri_string(),array('id'=>'frm_data','name'=>'frm_data','role'=>'form','class'=>'form-horizontal'));?>
<?php if(isset($views)) {?>
<div class="tab-content">
	<div class="tab-pane active">
		<div class="box box-primary">
			<div class="box-body">
				<div class="form-group ">
					<label class="col-sm-2 control-label">No Purchase Order<b class="text-red">*</b></label>
					<div class="col-sm-4">
						<input type="text" class="form-control" id="po_no" name="po_no" value="<?php echo (isset($data->po_no) ? $data->po_no: ""); ?>" placeholder="Automatic" readonly>
					</div>
					<label class="col-sm-2 control-label">Tanggal Purchase Order<b class="text-red">*</b></label>
					<div class="col-sm-4">
						<input type="text" class="form-control tanggal" id="po_date" name="po_date" value="<?php echo (isset($data->trans_date) ? $data->po_date: date("Y-m-d")); ?>" placeholder="Tanggal Purchase Request" required>
					</div>
				</div>
				<div class="form-group ">
					<label for="pr_no" class="col-sm-2 control-label"> No Purchase Request<b class="text-red">*</b></label>
					<div class="col-sm-4">
						<?php
						$pr_list[0]	= 'Select An Option';
						echo form_dropdown('pr_no',$pr_list, (isset($data->pr_no)?$data->pr_no:'0'),array('id'=>'pr_no','required'=>'required','class'=>'form-control select2'));
						?>
					</div>
					<label class="col-sm-2 control-label">Tipe</label>
					<div class="col-sm-4">
						<?php
						$tipe[0]	= 'Select An Option';
						echo form_dropdown('id_payment',$tipe, (isset($data->id_payment)?$data->id_payment:'0'),array('id'=>'id_payment','required'=>'required','class'=>'form-control','onChange'=>'cek_supplier()'));
						?>
					</div>
				</div>
				<div class="form-group <?php echo (($data->id_payment!='PO')?'hidden':'')?>" id="f_sup">
					<label class="col-sm-2 control-label"> Supplier<b class="text-red">*</b></label>
					<div class="col-sm-4">
						<?php
						$supplier[0]	= 'Select An Option';
						echo form_dropdown('id_supplier',$supplier, (isset($data->id_supplier)?$data->id_supplier:'0'),array('id'=>'id_supplier','class'=>'form-control select2'));
						?>
					</div>
				</div>
				<div class="form-group ">
					<label class="col-sm-2 control-label">Buyer</label>
					<div class="col-sm-4">
						<input type="text" class="form-control" id="pic" name="pic" value="<?php echo (isset($data->pic) ? $data->pic: ""); ?>" placeholder="Buyer">
					</div>
					<label class="col-sm-2 control-label">Keterangan</label>
					<div class="col-sm-4">
						<textarea class="form-control" id="po_info" name="po_info"><?php echo (isset($data->po_info) ? $data->po_info: ""); ?></textarea>
					</div>
				</div>
			</div>
			<div class="box-footer table-responsive">
			<table class="table table-bordered table-striped">
				<thead>
					<th width="5">#</th>
					<th>Material</th>
					<th>Qty Request</th>
					<th>Qty Order</th>
					<th>Unit</th>
					<th>Price Reference</th>
					<th>Harga / unit</th>
					<th>Total</th>
				</thead>
				<tbody id="tbl_detail">
				<?php if(!empty($data_material)){
					$idd=0;
					foreach($data_material AS $record){
						$idd++;?>
					<tr>
						<td><input type="checkbox" name="detail_id[]" id="raw_id_<?=$idd?>" value="<?=$idd;?>" checked>
						<input type="hidden" name="material_id_<?=$idd;?>" id="material_id_<?=$idd;?>" value="<?=$record->material_id;?>">
						<input type="hidden" name="id_pr_detail_<?=$idd;?>" id="id_pr_detail_<?=$idd;?>" value="<?=$record->id;?>">
						<td><?= $record->nama ?></td>
						<td><input type="text" class="form-control divide" name="material_request_<?=$idd;?>" id="material_request_<?=$idd;?>" value="<?=$record->material_request;?>" readonly tabindex="-1"></td>
						<td><input type="text" class="form-control divide" name="material_qty_<?=$idd;?>" id="material_qty_<?=$idd;?>" value="<?=$record->material_qty;?>" ></td>
						<td><input type="text" class="form-control" readonly tabindex="-1" name="material_unit_<?=$idd;?>" id="material_unit_<?=$idd;?>" value="<?=$record->material_unit;?>"></td>
						<td><input type="text" class="form-control divide" readonly tabindex="-1" name="material_pr_<?=$idd;?>" id="material_pr_<?=$idd;?>" value="<?=$record->material_pr;?>"></td>
						<td><input type="text" class="form-control divide" name="material_price_<?=$idd;?>" id="material_price_<?=$idd;?>" value="<?=$record->material_price;?>"></td>
						<td><input type="text" class="form-control divide" name="material_total_<?=$idd;?>" id="material_total_<?=$idd;?>" value="<?=$record->material_price*$record->material_qty;?>" readonly tabindex="-1"></td>
					</tr>
					<?php }
				}?><tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
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
<?php } else { ?>
<input type="hidden" id="id" name="id" value="<?php echo set_value('id', isset($data->id) ? $data->id : ''); ?>">
<input type="hidden" id="trans_type" name="trans_type" value="IN">
<input type="hidden" id="status" name="status" value="<?php echo (isset($data->status) ? $data->status : '0'); ?>">
<div class="tab-content">
	<div class="tab-pane active">
		<div class="box box-primary">
			<div class="box-body">
				<div class="form-group ">
					<label class="col-sm-2 control-label">No Purchase Order<b class="text-red">*</b></label>
					<div class="col-sm-4">
						<input type="text" class="form-control" id="po_no" name="po_no" value="<?php echo (isset($data->po_no) ? $data->po_no: ""); ?>" placeholder="Automatic" readonly>
					</div>
					<label class="col-sm-2 control-label">Tanggal Purchase Order<b class="text-red">*</b></label>
					<div class="col-sm-4">
						<input type="text" class="form-control tanggal" id="po_date" name="po_date" value="<?php echo (isset($data->trans_date) ? $data->po_date: date("Y-m-d")); ?>" placeholder="Tanggal Purchase Request" required>
					</div>
				</div>
				<div class="form-group ">
					<label for="pr_no" class="col-sm-2 control-label"> No Purchase Request<b class="text-red">*</b></label>
					<div class="col-sm-4">
						<?php
						$pr_list[0]	= 'Select An Option';
						echo form_dropdown('pr_no',$pr_list, (isset($data->pr_no)?$data->pr_no:'0'),array('id'=>'pr_no','required'=>'required','class'=>'form-control select2'));
						?>
					</div>
					<label class="col-sm-2 control-label">Tipe</label>
					<div class="col-sm-4">
						<?php
						$tipe[0]	= 'Select An Option';
						echo form_dropdown('id_payment',$tipe, (isset($data->id_payment)?$data->id_payment:'0'),array('id'=>'id_payment','required'=>'required','class'=>'form-control','onChange'=>'cek_supplier()'));
						?>
					</div>
				</div>
				<div class="form-group" id="f_sup">
					<label class="col-sm-2 control-label"> Supplier<b class="text-red">*</b></label>
					<div class="col-sm-4">
						<?php
						$supplier[0]	= 'Select An Option';
						echo form_dropdown('id_supplier',$supplier, (isset($data->id_supplier)?$data->id_supplier:'0'),array('id'=>'id_supplier','class'=>'form-control select2'));
						?>
					</div>
				</div>
				<div class="form-group ">
					<label class="col-sm-2 control-label">Buyer</label>
					<div class="col-sm-4">
						<input type="text" class="form-control" id="pic" name="pic" value="<?php echo (isset($data->pic) ? $data->pic: ""); ?>" placeholder="Buyer">
					</div>
					<label class="col-sm-2 control-label">Keterangan</label>
					<div class="col-sm-4">
						<textarea class="form-control" id="po_info" name="po_info"><?php echo (isset($data->po_info) ? $data->po_info: ""); ?></textarea>
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
					<th>Qty Request</th>
					<th>Qty Order</th>
					<th>Unit</th>
					<th>Price Reference</th>
					<th>Harga / unit</th>
					<th>Total
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
						<input type="hidden" name="id_pr_detail_<?=$idd;?>" id="id_pr_detail_<?=$idd;?>" value="<?=$record->id_pr;?>"></td>
						<td><?= $record->nama ?></td>
						<td><input type="text" class="form-control divide" name="material_request_<?=$idd;?>" id="material_request_<?=$idd;?>" value="<?=$record->material_request;?>" readonly tabindex="-1"></td>
						<td><input type="text" class="form-control divide" name="material_qty_<?=$idd;?>" id="material_qty_<?=$idd;?>" value="<?=$record->material_qty;?>" onchange="cektotal('<?=$idd;?>')"></td>
						<td><input type="text" class="form-control" readonly tabindex="-1" name="material_unit_<?=$idd;?>" id="material_unit_<?=$idd;?>" value="<?=$record->material_unit;?>"></td>
						<td><input type="text" class="form-control divide" readonly tabindex="-1" name="material_pr_<?=$idd;?>" id="material_pr_<?=$idd;?>" value="<?=$record->material_pr;?>"></td>
						<td><input type="text" class="form-control divide" name="material_price_<?=$idd;?>" id="material_price_<?=$idd;?>" value="<?=$record->material_price;?>" onchange="cektotal('<?=$idd;?>')"></td>
						<td><input type="text" class="form-control divide" name="material_total_<?=$idd;?>" id="material_total_<?=$idd;?>" value="<?=$record->material_price*$record->material_qty;?>" readonly tabindex="-1"></td>
					</tr>
					<?php }
				}?><tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
				</tbody>
			</table>
			</div>
		</div>
	</div>
</div>
<?= form_close() ?>
<script src="<?= base_url('assets/js/number-divider.min.js')?>"></script>
<script type="text/javascript">
	function cek_supplier(){
		if($("#id_payment").val()=='PO'){
			$("#f_sup").removeClass("hidden");
		}else{
			$("#f_sup").addClass("hidden");
		}
	}
	$(document).ready(function(){
		$('.select2').select2();
		$('.divide').divide();
		cek_supplier();
	});
	<?php echo (isset($data) ? '' : '$("#add-material").addClass("hidden");'); ?>;
	var url_approve = siteurl+'purchase_order/approve/';
	var url_save = siteurl+'purchase_order/save/';
	var url_list_detail = siteurl+'purchase_order/add_material/';
    $('#frm_data').on('submit', function(e){
        e.preventDefault();
		var errors="";
		if($("#id_payment").val()=='PO'){
			if($("#id_supplier").val()=="0") errors="Supplier tidak boleh kosong";
		}
		if($("#id_payment").val()=="0") errors="Tipe tidak boleh kosong";
		if($("#po_date").val()=="") errors="Tanggal Transaksi tidak boleh kosong";
		if($("#pr_no").val()=="0") errors="Nomor Purchase Request tidak boleh kosong";
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

<?php if(isset($data)){
	echo '$("#detail-add-button").removeClass("hidden");
	';
}
?>
	function cektotal(id){
		qty=$("#material_qty_"+id).val();
		harga=$("#material_price_"+id).val();
		total=parseFloat(qty)*parseFloat(harga);
		$("#material_total_"+id).val(total);
	}

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
