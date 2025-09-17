<?= form_open($this->uri->uri_string(), array('id' => 'frm_data', 'name' => 'frm_data', 'role' => 'form', 'class' => 'form-horizontal')); ?>
<input type="hidden" id="id" name="id" value="<?php echo set_value('id', isset($data->so_number) ? $data->so_number : ''); ?>">
<input type="hidden" id="status" name="status" value="<?php echo (isset($data->status) ? $data->status : '0'); ?>">
<style>
	.balance-error {
		background-color: pink !important;
	}
</style>
<div class="tab-content">
	<div class="tab-pane active">
		<div class="box box-primary">
			<div class="box-body">
				<div class="form-group ">
					<label class="col-sm-2 control-label">No PR<b class="text-red">*</b></label>
					<div class="col-sm-2">
						<input type="text" class="form-control" id="so_number" name="so_number" value="<?php echo (isset($data->so_number) ? $data->so_number : ""); ?>" placeholder="Automatic" readonly>
					</div>
					<label class="col-sm-2 control-label">Tanggal PR<b class="text-red">*</b></label>
					<div class="col-sm-2">
						<input type="text" class="form-control tanggal" id="pr_date" name="pr_date" value="<?php echo (isset($data->trans_date) ? $data->pr_date : date("Y-m-d")); ?>" placeholder="Tanggal Purchase Request" required>
					</div>
					<label class="col-sm-2 control-label">Tanggal Dibutuhkan</label>
					<div class="col-sm-2">
						<input type="text" class="form-control tanggal" id="tgl_dibutuhkan" name="tgl_dibutuhkan" value="<?php echo (isset($data->tgl_dibutuhkan) ? $data->tgl_dibutuhkan : date("Y-m-d")); ?>" placeholder="Tanggal">
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10">
						<?php
						if (isset($data)) {
							if ($data_material[0]->status_app == 'N') {
								if ($stsview == 'approve') {
									echo '<a href="#" type="button" name="Approve" class="btn btn-primary btn-sm" id="approve" onclick="data_approve()"><i class="fa fa-check-square-o">&nbsp;</i>Approve</a>';
								}
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
						<tr>
							<th>No</th>
							<th>Nama Barang</th>
							<th>Qty Permintaan</th>
							<th>Satuan</th>
						</tr>
					</thead>
					<tbody id="tbl_detail">
						<?php $total = 0;
						if (!empty($data_material)) {
							$idd = 0;
							foreach ($data_material as $record) {
								$balanced = "";
								if ($record->propose_purchase > 0) {


									$idd++;
						?>
									<tr>
										<td><input type="checkbox" name="detail_id[]" id="raw_id_<?= $idd ?>" value="<?= $idd; ?>" checked>
											<input type="hidden" name="id_material_<?= $idd; ?>" id="id_material_<?= $idd; ?>" value="<?= $record->id_material; ?>">

										</td>
										<td><?= $record->stock_name ?></td>

										<!-- <td><input type="text" class="form-control input-sm" readonly tabindex="-1" style="width:70px;" name="material_stock_<?= $idd; ?>" id="material_stock_<?= $idd; ?>" value="<?= (($record->material_stock != '') ? $record->material_stock : '0'); ?>"></td> -->

										<td><input type="text" class="form-control input-sm" style="width:70px;" name="material_order_<?= $idd; ?>" id="material_order_<?= $idd; ?>" value="<?= number_format($record->propose_purchase); ?>" onblur="cekbalance(<?= $idd; ?>)"></td>
										<td><input type="text" class="form-control input-sm" readonly style="width:70px;" tabindex="-1" name="material_unit_<?= $idd; ?>" id="material_unit_<?= $idd; ?>" value="<?= ucfirst($record->satuan); ?>"></td>
									</tr>
						<?php
								}
							}
						} ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<?= form_close() ?>
<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
<script type="text/javascript">
	var url_save = siteurl + 'pr_rutin/save/';
	var url_approve = siteurl + 'pr_rutin/approve/';
	var url_list_detail = siteurl + 'pr_rutin/add_material/';
	$(document).ready(function() {
		var status = $("#status").val();
		if (status != '0') {
			$("#submit").addClass("hidden");
			$("#add_material").addClass("hidden");
			$('form input[type="submit"]').prop("disabled", true);
		}
		<?php if (isset($stsview)) {
			if (($stsview != '')) {
				echo "$('form :input').attr('disabled', true);";
				echo '$("#submit").addClass("hidden");';
				//				echo '$("#approve").addClass("hidden");';
			}
		} ?>

	});
	$('.divide').divide();

	function cekbalance(id) {
		var butuh = $("#material_qty_" + id).val();
		var beli = $("#material_order_" + id).val();
		total = parseFloat(butuh - beli);
		$("#balance_" + id).val(total);
		if (parseFloat(total) < 0) {
			$("#balance_" + id).addClass("balance-error");
		} else {
			$("#balance_" + id).removeClass("balance-error");
		}
	}
	$('#frm_data').on('submit', function(e) {
		e.preventDefault();
		var errors = "";
		if (errors == "") {
			data_save();
		} else {
			swal(errors);
			return false;
		}
	});

	$(function() {
		$(".tanggal").datepicker({
			todayHighlight: true,
			format: "yyyy-mm-dd",
			showInputs: true,
			autoclose: true
		});
	});

	function data_approve() {
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
			function(isConfirm) {
				if (isConfirm) {
					id = $("#id").val();
					$.ajax({
						url: url_approve + id,
						dataType: "json",
						type: 'POST',
						success: function(msg) {
							if (msg['save'] == '1') {
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
						error: function(msg) {
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