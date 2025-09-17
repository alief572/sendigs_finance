<?php
$this->load->view('include/side_menu');
?>
<?=form_open('pembayaran_material/payment_save',array('id'=>'frm_data','name'=>'frm_data','role'=>'form','class'=>'form-horizontal'));?>
<input type="hidden" name="id_req" id="id_req" value="<?php echo (isset($data->id) ? $data->id: ''); ?>" />
<input type="hidden" name="tipetrans" id="tipetrans" value="<?=(isset($tipetrans)?$tipetrans:''); ?>" />
<div class="tab-content">
	<div class="tab-pane active">
		<div class="box box-primary">
			<div class="box-body">
				<div class="row">
				  <div class="col-md-6">
					<label class="control-label">Request Date</label>
					<input type="text" id="request_date" name="request_date" value="<?php echo (isset($data->request_date) ? $data->request_date: date("Y-m-d")); ?>" class="form-control" readonly>
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
					<label class="control-label">Payment Date</label>
					<input type="text" id="req_payment_date" name="req_payment_date" value="<?php echo $data->req_payment_date; ?>" class="form-control tanggal" required>
					<label class="control-label">Nomor Invoice</label>
					<input type="text" class="form-control" id="no_invoice" name="no_invoice" value="<?php echo $data->no_invoice; ?>" readonly>
					<label class="control-label">Keterangan Invoice</label>
					<input type="text" class="form-control" id="keterangan" name="keterangan" value="<?php echo $data->keterangan; ?>" readonly>
					<label class="control-label">PO yang akan dibayar</label>
					<input type="text" class="form-control divide" id="nilai_po_invoice" name="nilai_po_invoice" value="<?=(isset($data)?$data->nilai_po_invoice:0)?>" placeholder=0 readonly>
					<label class="control-label">PPN</label>
					<div class="input-group">
						<div class="input-group-addon"><input type="checkbox" value="1" name="ch_ppn" id="ch_ppn" <?=($data->invoice_ppn<>0?'checked':'')?>  onclick="return false;"></div>
						<input type="text" class="form-control divide" readonly id="invoice_ppn" name="invoice_ppn" value="<?=(isset($data)?$data->invoice_ppn:0)?>">
					</div>
					<label class="control-label">PPH</label>
					<?php
					$coa_pph=(isset($data)?$data->coa_pph:0);
					echo form_dropdown('coa_pph',$combo_coa_pph,$coa_pph,array('id'=>'coa_pph','class'=>'form-control readonly','style'=>'pointer-events: none;', 'tabindex'=>'-1' ));
					?>
					<input type="text" class="form-control divide" id="nilai_pph_invoice" name="nilai_pph_invoice" value="<?=(isset($data)?$data->nilai_pph_invoice:0)?>" readonly tabindex="-1">
					<label class="control-label">PO+PPN-PPH</label>
					<input type="text" class="form-control divide" id="nilai_invoice" name="nilai_invoice" value="<?=(isset($data)?$data->nilai_invoice:0)?>" placeholder=0 required readonly>
					<label class="control-label">Nilai Potongan DP</label>
					<input type="text" class="form-control divide" id="potongan_dp" name="potongan_dp" placeholder=0 value="<?=(isset($data)?$data->potongan_dp:0)?>" readonly>
					<label class="control-label">Nilai Potongan Claim</label>
					<input type="text" class="form-control divide" id="potongan_claim" name="potongan_claim" placeholder=0 value="<?=(isset($data)?$data->potongan_claim:0)?>" readonly>
					<label class="control-label">Keterangan Potongan</label>
					<input type="text" class="form-control" id="keterangan_potongan" name="keterangan_potongan" value="<?=(isset($data)?$data->keterangan_potongan:0)?>" readonly>
					<label class="control-label">Request Payment</label>
					<input type="text" class="form-control divide" id="request_payment" name="request_payment" value="<?=(isset($data)?$data->request_payment:0)?>" placeholder=0 required readonly>
				  </div>
			    </div>
				<div class="row">
				  <div class="col-md-6">
					<label class="control-label">Tanggal Pembayaran</label>
					<input type="text" id="payment_date" name="payment_date" value="<?php echo $data->payment_date; ?>" class="form-control tanggal" required>
					<label class="control-label">Bank</label>
					<?php
						echo form_dropdown('bank_coa',$datacoa, $data->bank_coa, array('id'=>'bank_coa','class'=>'form-control select2','required'=>'required'));
					?>
					<label class="control-label">Nilai Bank</label>
					<input type="text" class="form-control divide" id="nilai_bayar_bank" name="nilai_bayar_bank" value="<?php echo $data->nilai_bayar_bank; ?>" placeholder=0 onblur="cek_kurs()" required>
					<label class="control-label">Kurs Bank</label>
					<input type="text" class="form-control divide" id="curs" name="curs" value="<?php echo $data->curs; ?>" onblur="cek_kurs()" >
					<label class="control-label">Nilai Bank Rupiah</label>
					<input type="text" class="form-control divide" id="bank_nilai" name="bank_nilai" value="<?php echo $data->bank_nilai; ?>" placeholder=0 required tabindex="-1" >
					<!--
					readonly
					<label class="control-label">Alokasi DP</label>
					<input type="text" class="form-control divide alokasi" id="alokasi_dp" name="alokasi_dp" value="<?php echo $data->alokasi_dp; ?>" placeholder=0>
					<label class="control-label">Alokasi Hutang</label>
					<input type="text" class="form-control divide alokasi" id="alokasi_hutang" name="alokasi_hutang" value="<?php echo $data->alokasi_hutang; ?>" placeholder=0>
					-->
				  </div>
				  <div class="col-md-6"><br /><br /><br />
					<label class="control-label">Biaya Admin Bank</label>
					<input type="text" class="form-control divide" id="biaya_admin_forex" name="biaya_admin_forex" value="<?php echo $data->biaya_admin_forex; ?>" placeholder=0 onblur="cek_kurs_admin('')">
					<label class="control-label">Kurs Admin 1</label>
					<input type="text" class="form-control divide" id="curs_admin" name="curs_admin" value="<?php echo $data->curs_admin; ?>" onblur="cek_kurs_admin('')" >
					<label class="control-label">Biaya Admin 1 Rupiah</label>
					<input type="text" class="form-control divide" id="biaya_admin" name="biaya_admin" value="<?php echo $data->biaya_admin; ?>" placeholder=0 required readonly tabindex="-1">

					<label class="control-label">Biaya Admin Bank 2</label>
					<input type="text" class="form-control divide" id="biaya_admin_forex2" name="biaya_admin_forex2" value="<?php echo $data->biaya_admin_forex2; ?>" placeholder=0 onblur="cek_kurs_admin('2')">
					<label class="control-label">Kurs Admin 2</label>
					<input type="text" class="form-control divide" id="curs_admin2" name="curs_admin2" value="<?php echo $data->curs_admin2; ?>" onblur="cek_kurs_admin('2')" >
					<label class="control-label">Biaya Admin 2 Rupiah</label>
					<input type="text" class="form-control divide" id="biaya_admin2" name="biaya_admin2" value="<?php echo $data->biaya_admin2; ?>" placeholder=0 required tabindex="-1">

				  </div>
				</div>
			</div>
			<div class="box-footer">
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10">
						<button type="submit" name="simpan-com" class="btn btn-success btn-sm stsview" id="simpan-com"><i class="fa fa-save">&nbsp;</i>Submit</button>
						<a href="<?=base_url()?>pembayaran_material/payment" class="btn btn-warning btn-sm"><i class="fa fa-reply">&nbsp;</i>Kembali</a>
					</div>
				</div>
			</div>
		</div>
        <div class="box box-warning">
            <div class="box-header with-border">
              <h3 class="box-title">Info PO</h3>
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
              </div>
            </div>
            <div class="box-body">
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
							echo 'V';
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
								echo "<td align='left'>".number_format($valx->value_idr)."</td>";
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

	function cek_kurs(){
		var nilai_bayar_bank=$("#nilai_bayar_bank").val();
		var curs=$("#curs").val();
		bank_nilai=(parseFloat(nilai_bayar_bank)*parseInt(curs));
		$("#bank_nilai").val(bank_nilai.toFixed(0));
	}
	function cek_kurs_admin(id){
		var biaya_admin_forex=$("#biaya_admin_forex"+id).val();
		var curs_admin=$("#curs_admin"+id).val();
		biaya_admin=(parseFloat(biaya_admin_forex)*parseInt(curs_admin));
		$("#biaya_admin"+id).val(biaya_admin.toFixed(0));
	}

	function cek_alokasi() {
		var nilai_req=$("#request_payment").val();
		var ttl_alokasi=0;
		$('.alokasi').each(function(j, objb) {
			ttl_alokasi=(parseFloat(ttl_alokasi)+parseFloat($(this).val()));
		});
		if(nilai_req!=ttl_alokasi) {
			alert("Nilai Request tidak sama dengan nilai alokasi");
			return "ERROR";
		}else{
			return "OK";
		}
	}
	
	$('#simpan-com').click(function(e){
		$("#simpan-com").addClass("hidden");
		d_error='';
		e.preventDefault();
   		if($("#payment_date").val()==""){
   			d_error='Payment Date Error';
   			alert(d_error);
   		}
   		if($("#bank_coa").val()=="0"){
   			d_error='Bank Error';
   			alert(d_error);
   		}
//		ca=cek_alokasi();
//		if(ca!="OK") d_error='Alokasi Error';
   		if($("#bank_nilai").val()==""){
   			d_error='Payment Error';
   			alert(d_error);
   		}
   		if($("#bank_nilai").val()=="0"){
   			d_error='Payment Error';
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
							url         : base_url + active_controller+"/payment_save",
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
								window.location.href = base_url + active_controller+ "/payment";
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
					//$("#simpan-com").removeClass("hidden");
				 }
		  });
		}else{
			$("#simpan-com").removeClass("hidden");
		}
   	});
$(document ).ready(function() {
	$("#coa_pph").chosen("destroy");
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
