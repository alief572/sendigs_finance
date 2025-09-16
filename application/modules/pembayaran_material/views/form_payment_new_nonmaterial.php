<?php $this->load->view('include/side_menu');?>
<?=form_open('pembayaran_material/save_payment_new_nonmaterail',array('id'=>'frm_data','name'=>'frm_data'));?>
		<div class="box box-primary">
		  <div class="box-header">
				<div class="row">
				  <div class="col-md-6">
					<label class="control-label">Nomor Payment</label>
					<input type="text" id="no_payment" name="no_payment" value="<?php echo (isset($data)?$data->no_payment:""); ?>" class="form-control" placeholder ="Auto" readonly>
				  </div>
				  <div class="col-md-6">
					<label class="control-label">Tanggal Pembayaran</label>
					<input type="text" id="payment_date" name="payment_date" value="<?php echo (isset($data)?$data->payment_date:date("Y-m-d")); ?>" class="form-control tanggal" required>
				  </div>
				</div>
		  </div>
		  <div class="box-body">
			<table class="table table-bordered table-striped" id="mytabledata" width='100%'>
				<thead>
					<tr class='bg-blue'>
						<th class="text-center">No Request</th>
						<th class="text-center">No PO</th>
						<th class="text-center">Supplier</th>
						<th class="text-center">Tipe Payment</th>
						<th class="text-center">Currency</th>
						<th class="text-center">PO yang akan dibayar</th>
						<th class="text-center">PPN</th>
						<th class="text-center">PPH</th>
						<th class="text-center">Request Payment</th>
					</tr>
				</thead>			
			<tbody>
			<?php 
			$total=0;
			$id_supplier='';
			$nopo='';
			$curs_header='';$terima_barang_idr=0;
			$total_dpp=0; $total_invoice_ppn=0; $total_invoice_pph=0;
			if(empty($results)){
			}else{
				$numb=0; foreach($results AS $record){ 
				$numb++;
				$id_supplier=$record->id_supplier;
				$curs_header=$record->curs_header;
				?>
			<tr>
				<td><?= $record->no_request ?>
				<input type="hidden" name="id_req[]" id="id_req<?=$numb?>" value="<?=$record->id; ?>" />				
				</td>
				<td><?= $record->no_po ?></td>
				<td><?= $record->nm_supplier?></td>
				<td><?php
				$payterm  = $this->db->query("select data2,name from list_help where group_by='top' and data2='".$record->tipe."'")->row();
				if($nopo!=$record->no_po){
					$datapo  = $this->db->query("select terima_barang_idr from tran_po_header where no_po='".$record->no_po."'")->row();
					$terima_barang_idr=($terima_barang_idr+$datapo->terima_barang_idr);
				}
				$nopo=$record->no_po;
				$total=($total+$record->request_payment);
				echo $payterm->name;?></td>
				<td><?= $record->curs_header?></td>
				<td align=right><?= number_format($record->nilai_po_invoice,2)?></td>
				<td align=right><?= number_format($record->invoice_ppn,2)?></td>
				<td align=right><?= number_format($record->nilai_pph_invoice,2)?></td>
				<td align=right><?= number_format($record->request_payment,2)?></td>
			</tr>
			<?php 
			$total_dpp=($total_dpp+$record->nilai_po_invoice); $total_invoice_ppn=($total_invoice_ppn+$record->invoice_ppn); $total_invoice_pph=($total_invoice_pph+$record->nilai_pph_invoice);
				} 
			}  ?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan=5 align=right>Total</td>
					<td align=right><?php 
					if($total_dpp>0){
						echo number_format($total_dpp,2);
					}
					?>
					</td>
					<td align=right><?php 
					if($total_invoice_ppn>0){
						echo number_format($total_invoice_ppn);
					}
					?>
					</td>					
					<td align=right><?php 
					if($total_invoice_pph>0){
						echo number_format($total_invoice_pph);
					}
					?>
					<td align=right><?php 
					if($total>0){
						echo number_format($total,2);
					}
					?>
					</td>
				</tr>
				<tr>
					<td colspan=9 >A/P PO : <?php 
					if($total>0){
						echo number_format($terima_barang_idr,2);
					}
					?>
					</td>
				</tr>
			</tfoot>
			</table>
		  </div>
		  <div class="box-footer">
			<input type="hidden" name="total" id="total" value="<?=round($total); ?>" />
			<input type="hidden" name="id_supplier" id="id_supplier" value="<?=(isset($data)?$data->id_supplier:$id_supplier); ?>" />
			<input type="hidden" name="curs_header" id="curs_header" value="<?=(isset($data)?$data->curs_header:$curs_header); ?>" />
			<input type="hidden" name="modul" id="modul" value="<?=(isset($data)?$data->modul:'PO'); ?>" />
				<div class="row">
				  <div class="col-md-6">
					<label class="control-label">Bank</label>
					<?php
						echo form_dropdown('bank_coa',$datacoa, (isset($data)?$data->bank_coa:''), array('id'=>'bank_coa','class'=>'form-control select2','required'=>'required'));
					?>
					<label class="control-label">Nilai Bank</label>
					<input type="text" class="form-control divide" id="nilai_bayar_bank" name="nilai_bayar_bank" value="<?php echo (isset($data)?$data->nilai_bayar_bank:$total); ?>" placeholder=0 onblur="cek_kurs()" required>
					<label class="control-label">Kurs Bank</label>
					<input type="text" class="form-control divide" id="curs" name="curs" value="<?php echo (isset($data)?$data->curs:1); ?>" onblur="cek_kurs()" >
					<label class="control-label">Nilai Bank Rupiah</label>
					<input type="text" class="form-control divide" id="bank_nilai" name="bank_nilai" value="<?php echo (isset($data)?$data->bank_nilai:0); ?>" placeholder=0 required tabindex="-1" >
				  </div>
				  <div class="col-md-6">
					<label class="control-label">Bank Admin</label>
					<?php
						echo form_dropdown('bank_coa_admin',$datacoa, (isset($data)?$data->bank_coa_admin:''), array('id'=>'bank_coa_admin','class'=>'form-control select2','required'=>'required'));
					?>
					<label class="control-label">Biaya Admin Bank</label>
					<input type="text" class="form-control divide" id="biaya_admin_forex" name="biaya_admin_forex" value="<?php echo (isset($data)?$data->biaya_admin_forex:0); ?>" placeholder=0 onblur="cek_kurs_admin('')">
					<label class="control-label">Kurs Admin 1</label>
					<input type="text" class="form-control divide" id="curs_admin" name="curs_admin" value="<?php echo (isset($data)?$data->curs_admin:1); ?>" onblur="cek_kurs_admin('')" >
					<label class="control-label">Biaya Admin 1 Rupiah</label>
					<input type="text" class="form-control divide" id="biaya_admin" name="biaya_admin" value="<?php echo (isset($data)?$data->biaya_admin:0); ?>" placeholder=0 required readonly tabindex="-1">

					<label class="control-label">Biaya Admin Bank 2</label>
					<input type="text" class="form-control divide" id="biaya_admin_forex2" name="biaya_admin_forex2" value="<?php echo (isset($data)?$data->biaya_admin_forex2:0); ?>" placeholder=0 onblur="cek_kurs_admin('2')">
					<label class="control-label">Kurs Admin 2</label>
					<input type="text" class="form-control divide" id="curs_admin2" name="curs_admin2" value="<?php echo (isset($data)?$data->curs_admin2:1); ?>" onblur="cek_kurs_admin('2')" >
					<label class="control-label">Biaya Admin 2 Rupiah</label>
					<input type="text" class="form-control divide" id="biaya_admin2" name="biaya_admin2" value="<?php echo (isset($data)?$data->biaya_admin2:0); ?>" placeholder=0 required tabindex="-1">
				  </div>
				</div>
		  </div>
			<div class="box-footer">
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10">
						<button type="submit" name="simpan-com" class="btn btn-success btn-sm stsview" id="simpan-com"><i class="fa fa-save">&nbsp;</i>Submit</button>
						<a href="<?=base_url()?>pembayaran_material/payment_list" class="btn btn-warning btn-sm"><i class="fa fa-reply">&nbsp;</i>Kembali</a>
					</div>
				</div>
			</div>

		</div>
<?=form_close()?>
<?php $this->load->view('include/footer'); ?>
<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
<script>
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
   		if($("#bank_coa_admin").val()=="0"){
   			d_error='Bank Admin Error';
   			alert(d_error);
   		}
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
							url         : base_url + active_controller+"/save_payment_new_nonmaterial",
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
								window.location.href = base_url + active_controller+ "/payment_list";
							} else {
								swal({
									title: "Failed!", text: "Save Error", type: "error", timer: 1500, showConfirmButton: false
								});
							};
							console.log(msg);
						},
						error: function(msg){
						//$("#simpan-com").removeClass("hidden");
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

<?php
if(isset($data)){
	if($data->status=='1'){
		echo '$("#frm_data :input").prop("disabled", true);
		$(".stsview").addClass("hidden");';
	}
}
?>
</script>