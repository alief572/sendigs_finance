<?php
$this->load->view('include/side_menu');
?>
<?=form_open('pembelian/receive_invoice_save',array('id'=>'frm_data','name'=>'frm_data','role'=>'form','class'=>'form-horizontal', 'enctype'=>'multipart/form-data'));?>
<input type="hidden" name="category" id="category" value="<?php echo (isset($results) ? $results->category: ''); ?>" />
<input type="hidden" name="no_po" id="no_po" value="<?php echo (isset($results) ? $results->no_po: ''); ?>" />
<div class="tab-content">
	<div class="tab-pane active">
		<div class="box box-primary">
			<div class="box-header">
				<h3 class="box-title"><?php echo $title;?></h3>
			</div>
			<div class="box-body">
				<div class="row">
				  <div class="col-md-6">
					<label class="control-label">Receive Date</label>
					<input type="text" id="tgl_terima" name="tgl_terima" value="" class="form-control tanggal" required>
				  </div>
				  <div class="col-md-6">
					<label class="control-label">No Invoice / Kwitansi</label>
					<input type="text" id="invoice_no" name="invoice_no" value="" class="form-control" required>
				  </div>
				  <div class="col-md-6">
					<label class="control-label">Nilai Potongan DP</label>
					<input type="text" class="form-control divide" id="potong_um" name="potong_um" value="<?= (isset($results->potong_um)?$results->potong_um:"0"); ?>" required>
				  </div>
				  <div class="col-md-6">
					<label class="control-label">Nilai PPN</label>
					<input type="text" class="form-control divide" id="nilai_ppn" name="nilai_ppn" value="" required>
				  </div>
				  <div class="col-md-6">
					<label class="control-label">Total Invoice</label>
					<input type="text" class="form-control divide" id="invoice_total" name="invoice_total" value="">
				  </div>
				  <div class="col-md-6">
					<label class="control-label">Nomor Faktur Pajak</label>
					<input type="text" id="faktur_pajak" name="faktur_pajak" value="" class="form-control">
				  </div>
				  <div class="col-md-6">
					<label class="control-label">Surat Jalan / BAST</label>
					<input type="text" id="surat_jalan" name="surat_jalan" value="" class="form-control">
				  </div>
				  <div class="col-md-6">
					<label class="control-label">Lain-lain</label>
					<input type="text" id="lainnya" name="lainnya" value="" class="form-control">
				</div>
			</div>
			<div class="row">
			  <div class="col-md-12">
				<h4>Dokumen Incoming </h4>
				<table class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>No Dokumen</th>
							<th>Incoming Date</th>
							<th>PIC</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
					<?php
					if(!empty($dt_incoming)){
						foreach($dt_incoming AS $record){ 
							echo "<tr>";
								echo "<td align='left'><button type='button' class='btn btn-xs btn-primary detailAjust' title='View Incoming' data-kode_trans='".$record->kode_trans."' ><i class='fa fa-eye'></i></button> ".$record->kode_trans."</td>";
								echo "<td align='left'>".$record->tanggal."</td>";
								echo "<td align='left'>".$record->pic."</td>";
								echo "<td align='left'><input type='checkbox' value='".$record->kode_trans."' name='kode_trans[]' id='kt_".$record->kode_trans."'></td>";
							echo "</tr>";
						}
					}
					?>
					</tbody>
				</table>
			  </div>
			<div class="box-footer">
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10">
						<button type="submit" name="simpan-com" class="btn btn-success btn-sm stsview" id="simpan-com"><i class="fa fa-save">&nbsp;</i>Submit</button>
						<a href="<?=base_url()?>pembelian/purchase_order" class="btn btn-warning btn-sm"><i class="fa fa-reply">&nbsp;</i>Kembali</a>
					</div>
				</div>
			</div>
		</div>

	</div>
</div>
<?= form_close() ?>
<?php $this->load->view('include/footer'); ?>
<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
<script type="text/javascript">
	$(".divide").divide();
	$(".tanggal").datepicker({
		todayHighlight: true,
		dateFormat : "yy-mm-dd",
		showInputs: true,
		autoclose:true
	});
	$('#simpan-com').click(function(e){
		$("#simpan-com").addClass("hidden");
		d_error='';
		e.preventDefault();
   		if($("#tgl_terima").val()==""){
   			d_error='Receive Date Error';
   			alert(d_error);
   		}
		var invoice_no=$("#invoice_no").val();
		var invoice_total=$("#invoice_total").val();
		
   		if(invoice_no==""){
   			d_error='No Invoice / Kwitansi Error';
   			alert(d_error);
   		}
   		if(invoice_total=="" || invoice_total=="0"){
   			d_error='Total Amount Error';
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
							url         : base_url + active_controller+"/receive_invoice_top_save",
							type		: "POST",
							data		: formData,
							cache		: false,
							dataType	: 'json',
							processData	: false,
							contentType	: false,
						success: function(data){
							if(data.status == 1){
								swal({
									title: "Success!", text: "Data saved", type: "success", timer: 1500, showConfirmButton: false
								});
								window.location.href = base_url + active_controller+"/purchase_order";
							} else {
								swal({
									title: "Failed!", text: "Save Error", type: "error", timer: 1500, showConfirmButton: false
								});
							};
//							console.log(msg);
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

</script>
