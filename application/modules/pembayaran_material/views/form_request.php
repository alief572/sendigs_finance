<?php
$this->load->view('include/side_menu');
?>
<?=form_open('pembayaran_material/request_payment_save',array('id'=>'frm_data','name'=>'frm_data','role'=>'form','class'=>'form-horizontal'));?>
<input type="hidden" name="id_req" id="id_req" value="<?php echo (isset($data->id) ? $data->id: ''); ?>" />
<input type="hidden" name="id_top" id="id_top" value="<?php echo (isset($data->id) ? $data->id: ''); ?>" />
<input type="hidden" name="tipetrans" id="tipetrans" value="<?=$tipetrans?>" />
<div class="tab-content">
	<div class="tab-pane active">
		<div class="box box-primary">
			<div class="box-header">
				<h3 class="box-title"><?php echo $title;?></h3>
			</div>
			<div class="box-body">
				<div class="row">
				  <div class="col-md-6">
					<label class="control-label">Request Date</label>
					<input type="text" id="request_date" name="request_date" value="<?php echo (isset($data->request_date) ? $data->request_date: date("Y-m-d")); ?>" class="form-control tanggal" required>
					<label class="control-label">No Request</label>
					<input type="text" id="no_request" name="no_request" value="<?php echo (isset($data->no_request) ? $data->no_request: '')?>" class="form-control" placeholder="Auto" readonly>
					<label class="control-label">PO Number</label>
					<input type="text" class="form-control" id="no_po" name="no_po" value="<?php echo $data->no_po; ?>" readonly tabindex="-1">
					<label class="control-label">Supplier</label>
					<p><?=$datapoh->nm_supplier?></p><input type="hidden" id="id_supplier" name="id_supplier" value="<?php echo$datapoh->id_supplier; ?>">
					<label class="control-label">Tipe Payment</label>
					<p><?=strtoupper($payterm->name) ?><input type="hidden" name="tipe" id="tipe" value="<?=$payterm->data2?>" /></p>
					<label class="control-label">Currency</label>
					<p><?=$datapoh->mata_uang?><input type="hidden" name="curs_header" id="curs_header" value="<?=$datapoh->mata_uang?>" /></p>
					<label class="control-label">PO</label>
					<input type="text" class="form-control divide" id="nilai_po" name="nilai_po" value="<?php echo ($data->nilai_po); ?>" readonly tabindex="-1">
					<label class="control-label">PPN</label>
					<input type="text" class="form-control divide" id="nilai_ppn" name="nilai_ppn" value="<?php echo $data->nilai_ppn; ?>" readonly tabindex="-1">
					<label class="control-label">PO+PPN</label>
					<input type="text" class="form-control divide" id="nilai_total" name="nilai_total" value="<?php echo $data->nilai_total; ?>" readonly tabindex="-1">
				</div>
				<div class="col-md-6">
					<input type="hidden" class="form-control divide" id="total_bayar" name="total_bayar" value="<?php echo $data->total_bayar; ?>" readonly tabindex="-1">
					<input type="hidden" class="form-control divide" id="po_belum_dibayar" name="po_belum_dibayar" value="<?php echo ($data->po_belum_dibayar); ?>" readonly tabindex="-1">
					<input type="hidden" class="form-control divide" id="sisa_dp" name="sisa_dp" value="<?php echo $data->sisa_dp; ?>" readonly tabindex="-1">
					<label class="control-label">Bank Account</label>
					<input type="text" id="bank_transfer" name="bank_transfer" value="<?=$data->bank_transfer?>" class="form-control">
					<label class="control-label">Payment Date</label>
					<input type="text" id="req_payment_date" name="req_payment_date" value="<?php echo $data->req_payment_date; ?>" class="form-control tanggal" required>
					<label class="control-label">Nomor Invoice</label>
					<input type="text" class="form-control" id="no_invoice" name="no_invoice" value="<?php echo $data->no_invoice; ?>">
					<label class="control-label">Keterangan Invoice</label>
					<input type="text" class="form-control" id="keterangan" name="keterangan" value="<?php echo $data->keterangan; ?>">
					<label class="control-label">PO yang akan dibayar</label>
					<input type="text" class="form-control divide" id="nilai_po_invoice" name="nilai_po_invoice" value="<?=(isset($data)?$data->nilai_po_invoice:0)?>" placeholder=0 required onblur="calculate_invoice()">
					<label class="control-label">PPN</label>
					<div class="input-group">
						<div class="input-group-addon"><input type="checkbox" value="1" onclick="calculate_invoice()" name="ch_ppn" id="ch_ppn" <?=($data->invoice_ppn<>0?'checked':'')?>></div>
						<input type="text" class="form-control divide" readonly id="invoice_ppn" name="invoice_ppn" value="<?=(isset($data)?$data->invoice_ppn:0)?>" placeholder=0 required tabindex="-1">
					</div>
					<label class="control-label">PPH</label>
					<?php
					$coa_pph=(isset($data)?$data->coa_pph:0);
					echo form_dropdown('coa_pph',$combo_coa_pph,$coa_pph,array('id'=>'coa_pph','class'=>'form-control'));
					?>
					<input type="text" class="form-control divide" onblur="calculate_invoice()" id="nilai_pph_invoice" name="nilai_pph_invoice" value="<?=(isset($data)?$data->nilai_pph_invoice:0)?>" >					

					<label class="control-label">PO+PPN-PPH</label>
					<input type="text" class="form-control divide" id="nilai_invoice" name="nilai_invoice" value="<?=(isset($data)?$data->nilai_invoice:0)?>" placeholder=0 required readonly tabindex="-1">
					<label class="control-label">Nilai Potongan DP</label>
					<input type="text" class="form-control divide" id="potongan_dp" name="potongan_dp" placeholder=0 value="<?=(isset($data)?$data->potongan_dp:0)?>" onblur="calculate_invoice()">
					<label class="control-label">Nilai Potongan Claim</label>
					<input type="text" class="form-control divide" id="potongan_claim" name="potongan_claim" placeholder=0 value="<?=(isset($data)?$data->potongan_claim:0)?>" onblur="calculate_invoice()">
					<label class="control-label">Keterangan Potongan</label>
					<input type="text" class="form-control" id="keterangan_potongan" name="keterangan_potongan" value="<?=(isset($data)?$data->keterangan_potongan:0)?>">
					<label class="control-label">Request Payment</label>
					<input type="text" class="form-control divide" id="request_payment" name="request_payment" value="<?=(isset($data)?$data->request_payment:0)?>" placeholder=0 required readonly tabindex="-1">
				</div>
			</div>

			<div class="table-responsive">
				<h4>Detail PO</h4>
				<table class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>Nama Barang</th>
							<th>Qty</th>
							<th>Price/Unit</th>
							<th>Total Price</th>
							<th>Terkirim</th>
							<th>Payment For</th>
						</tr>
					</thead>
					<tbody>
					<?php
					if(!empty($datapod)){
						foreach($datapod AS $record){ ?>
						<tr>
						<td><?=$record->nm_material?></td>
						<td><?=number_format($record->qty_purchase,2)?></td>
						<td><?=number_format($record->net_price,2)?></td>
						<td><?=number_format($record->total_price,2)?></td>
						<td><?=number_format($record->qty_in,2)?></td>
						<td align=center><?php
						if($record->status_pay==$data->no_request){
							echo '<input type="checkbox" name="payfor[]" value="'.$record->id.'" checked>';
						}else{
							if($record->status_pay==""){
								echo '<input type="checkbox" name="payfor[]" value="'.$record->id.'">';
							}else{
								echo '<input type="hidden" name="payfor[]" value="">';
							}
						}?></td>
						</tr>
						<?php
						}
					}
					?>
					</tbody>
				</table>
				<h4>TOP</h4>
				<table class="table table-bordered table-striped">
					<thead>
					<tr>
						<th class="text-center" width='5%'>Group TOP</th>
						<th class="text-center" width='8%'>Progress (%)</th>
						<th class="text-center" width='11%'>Value</th>
						<th class="text-center" width='25%'>Keterangan</th>
						<th class="text-center" width='10%'>Est Jatuh Tempo</th>
						<th class="text-center" width='25%'>Persyaratan</th>
					</tr>
					</thead>
					<tbody>
					<?php
					if(!empty($data_payterm)){
						foreach($data_payterm AS $valx){
							echo "<tr class='header'>";
								echo "<td align='left'>".$valx->group_top."</td>";
								echo "<td align='left'>".$valx->progress."</td>";
								echo "<td align='left'>".number_format($valx->value_idr,2)."</td>";
								echo "<td align='left'>".$valx->keterangan."</td>";
								echo "<td align='left'>".$valx->jatuh_tempo."</td>";
								echo "<td align='left'>".$valx->syarat."</td>";
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
						<?php if(isset($status)){
							if($status=='approve'){
								echo "<a href='javascript:data_approve(". $data->id . ")' class='btn btn-success btn-sm' title='Approve Request Payment' data-role='qtip' id='app-btn'>Approve <i class='fa fa-check'></i></a>";
								$status='view';
							}
						} else { ?>
						<button type="submit" name="simpan-com" class="btn btn-success btn-sm stsview" id="simpan-com"><i class="fa fa-save">&nbsp;</i>Submit</button>
						<?php } ?>
						<a href="<?=base_url()?>pembayaran_material" class="btn btn-warning btn-sm"><i class="fa fa-reply">&nbsp;</i>Kembali</a>
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
	function data_approve(id){
			swal({
			  title: "Approve Data?",type: "warning",showCancelButton: true,confirmButtonClass: "btn-danger",confirmButtonText: "Yes",cancelButtonText: "No",closeOnConfirm: true,closeOnCancel: true
			},
			function(isConfirm) {
			  if (isConfirm) {
				  $.ajax({
						url         : base_url + active_controller+"/save_approve_request/"+id+"/<?=$tipetrans; ?>",
						type		: "POST",
						cache		: false,
						dataType	: 'json',
						processData	: false,
						contentType	: false,
					success: function(msg){
						if(msg['save']=='1'){
							swal({
								title: "Success!", text: "Data approved", type: "success", timer: 1500, showConfirmButton: false
							});
							window.location.href = base_url + active_controller;
						} else {
							swal({
								title: "Failed!", text: "Approved Error", type: "error", timer: 1500, showConfirmButton: false
							});
						};
						console.log(msg);
					},
					error: function(msg){
					$("#app-btn").removeClass("hidden");
					  swal({
						  title: "Error!",text: "Ajax Error",type: "error",timer: 1500, showConfirmButton: false
					  });
					  console.log(msg.responseText);
					}
				  });
			 }
			 else{
				$("#app-btn").removeClass("hidden");
			 }
		});
	}
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
							url         : base_url + active_controller+"/request_payment_save",
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
								window.location.href = base_url + active_controller;
							} else {
								swal({
									title: "Failed!", text: "Save Error", type: "error", timer: 1500, showConfirmButton: false
								});
							};
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

function calculate_invoice(){
	var inv_po=$("#nilai_po_invoice").val();
	var po_ppn=$('#nilai_ppn').val();
	var ch_ppn=$('#ch_ppn').is(":checked");
//	var ch_pph=$('#ch_pph').is(":checked");
	var inv_dp=$("#potongan_dp").val();
	var inv_claim=$("#potongan_claim").val();
	var potongan_dp=$("#potongan_dp").val();
	var potongan_claim=$("#potongan_claim").val();
	var nilai_invoice=inv_po;
	var inv_ppn=0;
	var inv_pph=0;
	if(ch_ppn){
		inv_ppn=(parseFloat(inv_po)*<?=$def_ppn->info/100?>);
	}
	inv_pph=$("#nilai_pph_invoice").val();
/*
	if(ch_pph){
		inv_pph=(parseFloat(inv_po)*<?=$def_pph->info/100?>);
		$("#nilai_pph_invoice").attr("readonly", false); 
		if(	$("#nilai_pph_invoice").val()>0) {
			inv_pph=$("#nilai_pph_invoice").val();
		}
	}else{
		$("#nilai_pph_invoice").attr("readonly", true);
	}
*/
	nilai_invoice=(parseFloat(inv_po)+parseFloat(inv_ppn)-parseFloat(inv_pph));		
	$("#invoice_ppn").val(inv_ppn);
	$("#nilai_pph_invoice").val(inv_pph);
	$("#nilai_invoice").val(nilai_invoice);
	var req_payment=(parseFloat(nilai_invoice)-parseFloat(potongan_dp)-parseFloat(potongan_claim));
	$("#request_payment").val(req_payment);	
}
	$(".divide").divide();
	$(".tanggal").datepicker({
		todayHighlight: true,
		format : "yyyy-mm-dd",
		showInputs: true,
		autoclose:true
	});

<?php
if(isset($status)){
	if($status=='view'){
		echo '$("#frm_data :input").prop("disabled", true);
		$(".stsview").addClass("hidden");';
	}
}
?>
</script>
