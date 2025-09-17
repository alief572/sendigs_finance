<?=form_open('purchase/save_close_po',array('id'=>'frm_data','name'=>'frm_data','role'=>'form','class'=>'form-horizontal'));?>
<input type="hidden" name="no_po" id="no_po" value="<?php echo (isset($datapo->no_po) ? $datapo->no_po: ''); ?>" />
<div class="tab-content">
	<div class="tab-pane active">
		<div class="box box-primary">
			<div class="box-body">
				<div class="form-group ">
					<label class="col-sm-2 control-label">PO Number</label>
					<div class="col-sm-4">
						<input type="text" class="form-control" id="no_po" name="no_po" value="<?php echo $datapo->no_po; ?>" readonly tabindex="-1">
					</div>
					<label class="col-sm-2 control-label">Supplier</label>
					<div class="col-sm-4">
						<p><?=$datapo->nm_supplier?></p><input type="hidden" id="id_supplier" name="id_supplier" value="<?php echo$datapo->id_supplier; ?>">
					</div>
				</div>
				<div class="form-group ">
					<label class="col-sm-2 control-label">Currency</label>
					<div class="col-sm-4">
						<p class="form-control-static"><?=$datapo->mata_uang?><input type="hidden" name="curs_header" id="curs_header" value="<?=$datapo->mata_uang?>" /></p>
					</div>
					<label class="col-sm-2 control-label">Total PO</label>
					<div class="col-sm-4">
						<input type="text" class="form-control divide" id="nilai_total" name="nilai_total" value="<?php echo $datapo->net_plus_tax; ?>" readonly tabindex="-1">
					</div>
				</div>
<!--
				<div class="form-group ">
					<label class="col-sm-2 control-label">Total Payment</label>
					<div class="col-sm-4">
						<input type="text" class="form-control divide" id="total_bayar" name="total_bayar" value="<?php echo $datapo->total_bayar; ?>" readonly tabindex="-1">
					</div>
					<label class="col-sm-2 control-label">PO belum dibayar</label>
					<div class="col-sm-4">
						<input type="text" class="form-control divide" id="po_belum_dibayar" name="po_belum_dibayar" value="<?php echo ($datapo->nilai_plus_ppn-$datapo->total_bayar); ?>" readonly tabindex="-1">
					</div>
				</div>

<hr />
				<div class="form-group ">
					<label class="col-sm-2 control-label">Total Payment IDR</label>
					<div class="col-sm-4">
						<input type="text" class="form-control divide" id="total_bayar_rupiah" name="total_bayar_rupiah" value="<?php echo $datapo->total_bayar_rupiah; ?>" readonly tabindex="-1">
					</div>
					<label class="col-sm-2 control-label">Total Terima Barang IDR</label>
					<div class="col-sm-4">
						<input type="text" class="form-control divide" id="total_terima_barang_idr" name="total_terima_barang_idr" value="<?php echo $datapo->total_terima_barang_idr; ?>" readonly tabindex="-1">
					</div>
				</div>
				<div class="form-group ">
					<label class="col-sm-2 control-label">Selisih</label>
					<div class="col-sm-4">
						<input type="text" class="form-control divide" id="selisih" name="selisih" value="<?php echo ($datapo->total_bayar_rupiah-$datapo->total_terima_barang_idr); ?>" readonly tabindex="-1">
					</div>
				</div>
-->

			</div>
			<div class="box-footer">
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10">
						<button type="submit" name="simpan-com" class="btn btn-success btn-sm stsview" id="simpan-com"><i class="fa fa-save">&nbsp;</i>Submit</button>
						<a href="<?=base_url()?>purchase" class="btn btn-warning btn-sm"><i class="fa fa-reply">&nbsp;</i>Kembali</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?= form_close() ?>
<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
<script type="text/javascript">

	$(".divide").divide();
	$('#simpan-com').click(function(e){
		$("#simpan-com").addClass("hidden");
		d_error='';
		e.preventDefault();
   		if($("#date").val()==""){
   			d_error='Date Error';
   			alert(d_error);
   		}
		if(d_error==''){
			swal({
				  title: "Save Data?",type: "warning",showCancelButton: true,confirmButtonClass: "btn-danger",confirmButtonText: "Yes",cancelButtonText: "No",closeOnConfirm: true,closeOnCancel: true
				},
				function(isConfirm) {
				  if (isConfirm) {					  
					  var formData 	=new FormData($('#frm_data')[0]);
					  $.ajax({
							url         : base_url + active_controller+"/save_close_po",
							type		: "POST",
							data		: formData,
							cache		: false,
							dataType	: 'json',
							processData	: false,
							contentType	: false,
						success: function(msg){
							if(msg['save']=='1'){
								swal({
									title: "Success!", text: "Data saved", type: "success", timer: 1500, showConfirmButton: false
								});
								location.reload();
//								window.location.href = base_url + active_controller+"/purchase_order";
							} else {
								swal({
									title: "Failed!", text: "Save Error", type: "error", timer: 1500, showConfirmButton: false
								});
							};
							console.log(msg);
						},
						error: function(msg){
						$("#simpan-com").removeClass("hidden");
						  swal({
							  title: "Error!",text: "Ajax Error",type: "error",timer: 1500, showConfirmButton: false
						  });
						  console.log(msg.responseText);
						}
					  });
			     }
				 else{
					$("#simpan-com").removeClass("hidden");
				 }
		  });
		}else{
			$("#simpan-com").removeClass("hidden");
		}
   	});
	$(document).ready(function(){
		swal.close();
	});		
</script>
