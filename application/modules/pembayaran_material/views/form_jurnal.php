<?php
$this->load->view('include/side_menu');
?>

<?=form_open('pembayaran_material/jurnal_save',array('id'=>'frm_data','name'=>'frm_data','role'=>'form','class'=>'form-horizontal',));?>
<?php
$datarequest = $this->db->query("select * from purchase_order_request_payment where no_request='".$data[0]->no_reff."'")->row();
$nopo='';
$datapayterm='';
$total_po=0;
if(!empty($datarequest)){
	$nopo=$datarequest->no_po;
	$datapayterm=$datarequest->tipe;
	if($datapayterm=='TR-02') {
		$total_po=$datarequest->bank_nilai;
	}	
}
?>
<div class="tab-content">
	<div class="tab-pane active">
		<div class="box box-primary">
			<div class="box-header">
				<div class="form-group ">
					<label class="col-sm-2 control-label">No Request</label>
					<div class="col-sm-4">
						<input type="text" id="no_reff" name="no_reff" value="" class="form-control" readonly tabindex="-1">
					</div>
					<label class="col-sm-2 control-label">Date</label>
					<div class="col-sm-4">
						<input type="text" id="tanggal" name="tanggal" value="" class="form-control" readonly tabindex="-1">
					</div>
				</div>
				<div class="form-group payment">
					<label class="col-sm-2 control-label">No PO</label>
					<div class="col-sm-4"><p class="form-control-static"><?=$nopo?></p></div>
					<label class="col-sm-2 control-label">Tipe Payment</label>
					<div class="col-sm-4">
						<?php
						$payterm[0]="Choose an Options";
						echo form_dropdown('payterm',$payterm, $datapayterm,array('id'=>'payterm','class'=>'form-control','disabled'=>'disabled'));
						?>					
					</div>
				</div>
			</div>

			<input type="hidden" name="tipe" id="tipe" value="" />
			<input type="hidden" name="nomor" id="nomor" value="" />
			<input type="hidden" name="no_request" id="no_request" value="" />
			<input type="hidden" name="jenis_jurnal" id="jenis_jurnal" value="" />
			<input type="hidden" name="nocust" id="nocust" value="" />
			<input type="hidden" name="total_po" id="total_po" value="<?=$total_po?>" />
			<div class="box-body">
			<table class="table">
			<thead>
			<tr>
				<th>COA</th>
				<th>Keterangan</th>
				<th>Debet</th>
				<th>Kredit</th>
			</tr>
			</thead>
			<tbody>
			<?php
		$tanggal="";
		$no_reff="";
		$no_request="";
		$tipe="";
		$nomor="";
		$jenis_jurnal="";
		$nocust="";
		$numb=0;
		$total_d=0;
		$total_k=0;
		foreach($data AS $record){ 
			$tanggal=$record->tanggal;
			$no_reff=$record->no_reff;
			$tipe=$record->tipe;
			$nomor=$record->nomor;
			$no_request=$record->no_request;
			$jenis_jurnal=$record->jenis_jurnal;
			$nocust=$record->nocust;
			$numb++;
			$total_d=($total_d+$record->debet);
			$total_k=($total_k+$record->kredit);
			?>
			<tr>
				<td><input type="hidden" name="id[]" id="id<?=$numb?>" value="<?=$record->id;?>" />
				<?php
					echo form_dropdown('no_perkiraan[]',$datacoa, $record->no_perkiraan, array('id'=>'no_perkiraan'.$numb,'class'=>'form-control select2','required'=>'required','style'=>'width:100%'));
				?>
				</td>
				<td><input type="text" class="form-control" id="keterangan<?=$numb?>" name="keterangan[]" value="<?=str_replace('"','`',$record->keterangan);?>"></td>
				<td><input type="text" class="form-control divide autodebet" id="debet<?=$numb?>" name="debet[]" value="<?=$record->debet;?>" required onblur="cektotaldebet(<?=$numb?>)"></td>
				<td><input type="text" class="form-control divide autokredit" id="kredit<?=$numb?>" name="kredit[]" value="<?=$record->kredit;?>" required onblur="cektotalkredit(<?=$numb?>)"></td>
			</tr>
		<?php }
		$link='';
		if($jenis_jurnal=='BUK20') $link='payment_jurnal';
		if($jenis_jurnal=='JV032') $link='ros_jurnal';
		if($jenis_jurnal=='JV033') $link='ros_jurnal';
		if($jenis_jurnal=='JV034') $link='closing_po_jurnal';
		?>
		<tr>
			<td colspan="2">Total</td>
			<td><input type="text" class="form-control divide" id="total_d" name="total_d" value="<?=$total_d;?>" disabled></td>
			<td><input type="text" class="form-control divide" id="total_k" name="total_k" value="<?=$total_k;?>" disabled></td>
		</tr>
			</tbody>
			</table>
			</div>
			<div class="box-footer">
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10">
						<button type="submit" name="simpan-com" class="btn btn-success btn-sm stsview <?php if($total_d!=$total_k) echo "hidden";?>" id="simpan-com"><i class="fa fa-save">&nbsp;</i>Submit</button>
						<a href="<?=base_url("pembayaran_material/".$link)?>" class="btn btn-warning btn-sm"><i class="fa fa-reply">&nbsp;</i>Kembali</a>
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
function cektotaldebet(id){
	SUM_DEBET=0;
	$('.autodebet').each(function(){
		SUM_DEBET += parseFloat($(this).val());
	});
	$("#total_d").val(SUM_DEBET);
	cekbutton();
}
function cektotalkredit(id){
	SUM_KREDIT=0;
	$('.autokredit').each(function(){
		SUM_KREDIT += parseFloat($(this).val());
	});
	$("#total_k").val(SUM_KREDIT);
	cekbutton();
}
function cekbutton(){
	if(parseFloat($("#total_d").val())==parseFloat($("#total_k").val())) {
		$("#simpan-com").removeClass("hidden");
	}else{
		$("#simpan-com").addClass("hidden");
	}

}
	$("#tanggal").val('<?=$tanggal?>');
	$("#no_reff").val('<?=$no_reff?>');
	$("#no_request").val('<?=$no_request?>');
	$("#tipe").val('<?=$tipe?>');
	$("#nomor").val('<?=$nomor?>');
	$("#jenis_jurnal").val('<?=$jenis_jurnal?>');
	$("#nocust").val('<?=$nocust?>');
	<?php if($tipe!='BUK') echo '$(".payment").addClass("hidden");';?>
	$(".divide").divide();
	$('#simpan-com').click(function(e){
		$("#simpan-com").addClass("hidden");
		d_error='';
		e.preventDefault();
   		if($("#date").val()==""){
   			d_error='Date Error';
   			alert(d_error);
   		}
		
		var total_d=$("#total_d").val();
		var total_k=$("#total_k").val();
		if(total_d!=total_k){
   			d_error='Tidak Balance';
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
							url         : base_url + active_controller+"/jurnal_save",
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
								window.location.href = base_url + active_controller +"/<?=$link?>";
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


<?php
if(isset($status)){
	if($status=='view'){
		echo '$("#frm_data :input").prop("disabled", true);
		$(".stsview").addClass("hidden");';
	}
}
?>
</script>
