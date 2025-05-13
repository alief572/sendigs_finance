<?php
$ENABLE_ADD     = has_permission('Payment_Jurnal.Add');
$ENABLE_MANAGE  = has_permission('Payment_Jurnal.Manage');
$ENABLE_VIEW    = has_permission('Payment_Jurnal.View');
$ENABLE_DELETE  = has_permission('Payment_Jurnal.Delete');
?>
<?= form_open('request_payment/jurnal_save', array('id' => 'frm_data', 'name' => 'frm_data', 'role' => 'form', 'class' => 'form-horizontal',)); ?>
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
			</div>

			<input type="hidden" name="tipe" id="tipe" value="" />
			<input type="hidden" name="nomor" id="nomor" value="" />
			<input type="hidden" name="no_request" id="no_request" value="" />
			<input type="hidden" name="jenis_jurnal" id="jenis_jurnal" value="" />
			<input type="hidden" name="nocust" id="nocust" value="" />
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
						$tanggal = "";
						$no_reff = "";
						$no_request = "";
						$tipe = "";
						$nomor = "";
						$jenis_jurnal = "";
						$nocust = "";
						$numb = 0;
						foreach ($data as $record) {
							$tanggal = $record->tanggal;
							$no_reff = $record->no_reff;
							$tipe = $record->tipe;
							$nomor = $record->nomor;
							$no_request = $record->no_request;
							$jenis_jurnal = $record->jenis_jurnal;
							$nocust = $record->nocust;
							$numb++;
						?>
							<tr>
								<td><input type="hidden" name="id[]" id="id<?= $numb ?>" value="<?= $record->id; ?>" />
									<?php
									echo form_dropdown('no_perkiraan[]', $datacoa, $record->no_perkiraan, array('id' => 'no_perkiraan' . $numb, 'class' => 'form-control select2', 'required' => 'required', 'style' => 'width:100%'));
									?>
								</td>
								<td><input type="text" class="form-control" id="keterangan<?= $numb ?>" name="keterangan[]" value="<?= $record->keterangan; ?>"></td>
								<td><input type="text" class="form-control divide" id="debet<?= $numb ?>" name="debet[]" value="<?= $record->debet; ?>" required></td>
								<td><input type="text" class="form-control divide" id="kredit<?= $numb ?>" name="kredit[]" value="<?= $record->kredit; ?>" required></td>
							</tr>
						<?php }
						?>
					</tbody>
				</table>
			</div>
			<div class="box-footer">
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10">
						<button type="submit" name="simpan-com" class="btn btn-success btn-sm stsview" id="simpan-com"><i class="fa fa-save">&nbsp;</i>Submit</button>
						<a href="<?= base_url("request_payment/payment_jurnal_list") ?>" class="btn btn-warning btn-sm"><i class="fa fa-reply">&nbsp;</i>Kembali</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?= form_close() ?>
<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
<script type="text/javascript">
	$("#tanggal").val('<?= $tanggal ?>');
	$("#no_reff").val('<?= $no_reff ?>');
	$("#no_request").val('<?= $no_request ?>');
	$("#tipe").val('<?= $tipe ?>');
	$("#nomor").val('<?= $nomor ?>');
	$("#jenis_jurnal").val('<?= $jenis_jurnal ?>');
	$("#nocust").val('<?= $nocust ?>');
	<?php if ($tipe != 'BUK') echo '$(".payment").addClass("hidden");'; ?>
	$(".divide").divide();
	$('#simpan-com').click(function(e) {
		$("#simpan-com").addClass("hidden");
		d_error = '';
		e.preventDefault();
		if ($("#date").val() == "") {
			d_error = 'Date Error';
			alert(d_error);
		}
		if (d_error == '') {
			swal({
					title: "Save Data?",
					type: "warning",
					showCancelButton: true,
					confirmButtonClass: "btn-danger",
					confirmButtonText: "Yes",
					cancelButtonText: "No",
					closeOnConfirm: true,
					closeOnCancel: true
				},
				function(isConfirm) {
					if (isConfirm) {
						var formData = new FormData($('#frm_data')[0]);
						$.ajax({
							url: base_url + active_controller + "/jurnal_save",
							type: "POST",
							data: formData,
							cache: false,
							dataType: 'json',
							processData: false,
							contentType: false,
							success: function(msg) {
								if (msg['save'] == '1') {
									swal({
										title: "Success!",
										text: "Data saved",
										type: "success",
										timer: 1500,
										showConfirmButton: false
									});
									window.location.href = base_url + active_controller + "/payment_jurnal_list";
								} else {
									swal({
										title: "Failed!",
										text: "Save Error",
										type: "error",
										timer: 1500,
										showConfirmButton: false
									});
								};
								console.log(msg);
							},
							error: function(msg) {
								$("#simpan-com").removeClass("hidden");
								swal({
									title: "Error!",
									text: "Ajax Error",
									type: "error",
									timer: 1500,
									showConfirmButton: false
								});
								console.log(msg.responseText);
							}
						});
					} else {
						$("#simpan-com").removeClass("hidden");
					}
				});
		} else {
			$("#simpan-com").removeClass("hidden");
		}
	});


	<?php
	if (isset($status)) {
		if ($status == 'view') {
			echo '$("#frm_data :input").prop("disabled", true);
		$(".stsview").addClass("hidden");';
		}
	}
	?>
</script>