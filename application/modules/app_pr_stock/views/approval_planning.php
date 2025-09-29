<?php
$pembeda = substr($header[0]['so_number'], 0, 1);
$due_date = (!empty($header[0]['due_date'])) ? date('d F Y', strtotime($header[0]['due_date'])) : '-';
$tgl_dibutuhkan = (!empty($header[0]['tgl_dibutuhkan'])) ? date('d F Y', strtotime($header[0]['tgl_dibutuhkan'])) : '-';

// Detail Approval
$alasan_reject1 = (!empty($header)) ? $header[0]['reject_reason1'] : '';
$alasan_reject2 = (!empty($header)) ? $header[0]['reject_reason2'] : '';
$alasan_reject3 = (!empty($header)) ? $header[0]['reject_reason3'] : '';

$keterangan_1 = (!empty($header)) ? $header[0]['keterangan_1'] : '';
$keterangan_2 = (!empty($header)) ? $header[0]['keterangan_2'] : '';
$keterangan_3 = (!empty($header)) ? $header[0]['keterangan_3'] : '';

$status1 = '';
$tgl_appre_1 = '';
$status2 = '';
$tgl_appre_2 = '';
$status3 = '';
$tgl_appre_3 = '';
if (!empty($header)) {
	if ($header[0]['app_3'] == '1') {
		$status3 = '<div class="badge bg-green">Approved</div>';
		$tgl_appre_3 = date('d F Y', strtotime($header[0]['app_3_date']));
	} else {
		if ($header[0]['sts_reject3'] == '1') {
			$status3 = '<div class="badge bg-red">Rejected</div>';
			$tgl_appre_3 = date('d F Y', strtotime($header[0]['sts_reject3_date']));
		}
	}
}
?>
<div class="box box-primary">
	<div class="box-body">
		<form id="data-form" method="post" autocomplete="off"><br>
			<input type="hidden" name='so_number' id='so_number' value='<?= $header[0]['so_number']; ?>'>
			<input type="hidden" name="tingkat_approval" id="tingkat_approval" value="<?= $tingkat_approval ?>">
			<div class="form-group row">
				<div class="col-md-12">
					<table class='table' width='70%'>
						<tr>
							<td width='20%'>No. Request/SO</td>
							<td width='1%'>:</td>
							<td width='29%'><?= $header[0]['so_number']; ?></td>
							<td width='20%'>Due Date SO</td>
							<td width='1%'>:</td>
							<td width='29%'><?= $due_date; ?></td>
						</tr>
						<tr>
							<td>No. PR</td>
							<td>:</td>
							<td><?= $header[0]['no_pr']; ?></td>
							<td>Tgl Dibutuhkan</td>
							<td>:</td>
							<td><?= $tgl_dibutuhkan; ?></td>
						</tr>
						<tr>
							<td>Customer</td>
							<td>:</td>
							<td><?= $header[0]['nm_customer']; ?></td>
							<td>Tingkat PR</td>
							<td>:</td>
							<td><?= ($header[0]['tingkat_pr'] == 2) ? 'Urgent' : 'Normal' ?></td>
						</tr>
					</table>
				</div>
				<div class="col-md-8">
					<table class="table">
						<thead>
							<tr>
								<th class="text-center">Approval By</th>
								<th class="text-center">Status</th>
								<th class="text-center">Tgl Approve / Reject</th>
								<th class="text-center">Alasan Reject</th>
								<th class="text-center">Keterangan</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td class="text-center">Management</td>
								<td class="text-center">
									<?= $status3 ?>
								</td>
								<td class="text-center">
									<?= $tgl_appre_3 ?>
								</td>
								<td>
									<input type="text" name="reject_reason3" id="" class="form-control" value="<?= $alasan_reject3 ?>" readonly>
								</td>
								<td>
									<input type="text" name="keterangan_3" id="" class="form-control" value="<?= $keterangan_3 ?>">
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="col-md-6">
					<label for="">Nilai Budget</label>
					<input type="text" name="" id="" class="form-control form-control-sm text-right" value="<?= number_format($header[0]['nilai_budget']) ?>" readonly>
				</div>
				<div class="col-md-6">
					<label for="">Nilai Pengajuan</label>
					<input type="text" name="" id="" class="form-control form-control-sm text-right" value="<?= number_format($header[0]['nilai_pengajuan']) ?>" readonly>
				</div>
				<div class="col-md-12">
					<br><br>
					<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
						<thead class='thead'>
							<tr class='bg-blue'>
								<th class='text-center th'><input type="checkbox" name="chk_all" id="chk_all"></th>
								<th class='text-center th'>Barang</th>
								<th class='text-center th'>Kebutuhan 1 Bulan</th>
								<th class='text-center th'>Max</th>
								<th class='text-center th'>Stock</th>
								<th class='text-center th'>Propose</th>
								<th class='text-center th'>Price Reference</th>
								<th class='text-center th'>Total Price</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$grand_total_price = 0;
							foreach ($detail as $key => $value) {
								$key++;
								$nm_material 	= $value['nm_material'];
								$stock_free 	= $value['stock_free'];
								$use_stock 		= $value['use_stock'];
								$sisa_free 		= $stock_free - $use_stock;
								$propose 		= $value['propose_purchase'];

								$get_material = $this->db->get_where('accessories', ['id' => $value['id_material']])->row();
								$get_kebutuhan = $this->db->get_where('budget_rutin_detail', ['id_barang' => $value['id_material']])->row();
								$get_stock = $this->db->get_where('warehouse_stock', ['id_material' => $value['id_material']])->row();

								$kebutuhan = (!empty($get_kebutuhan)) ? $get_kebutuhan->kebutuhan_month : 0;
								$stock = (!empty($get_stock)) ? $get_stock->qty_stock : 0;

								$konversi = (!empty($get_material) && $get_material->konversi > 0) ? $get_material->konversi : 1;

								echo "<tr>";
								if ($value['status_app'] == 'N') {
									echo "<td class='text-center'><input type='checkbox' name='check[" . $value['id'] . "]' class='chk_personal' value='" . $value['id'] . "'></td>";
								}
								echo "<td class='text-left'>" . $nm_material . "
										<input type='hidden' name='detail[" . $key . "][id]' value='" . $value['id'] . "'>
										</td>";
								echo "<td class='text-right min_stok'>" . number_format($kebutuhan) . "</td>";
								echo "<td class='text-right max_stok'>" . number_format($kebutuhan * 1.5) . "</td>";
								echo "<td class='text-right min_order'>" . number_format($stock, 2) . "</td>";
								echo "<td class='text-right'>" . number_format($propose * $konversi, 2) . "</td>";
								echo "<td class='text-right'>Rp. " . number_format($get_material->price_ref_high) . "</td>";
								echo "<td class='text-right'>Rp. " . number_format(($propose * $konversi) * $get_material->price_ref_high) . "</td>";

								echo "</tr>";

								$grand_total_price += (($propose * $konversi) * $get_material->price_ref_high);
							}
							?>
						</tbody>
						<tfoot>
							<tr class="bg-blue">
								<th colspan="7" class="text-center">Total Price Pengajuan</th>
								<th class="text-right">Rp. <?= number_format($grand_total_price) ?></th>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>

			<div class="form-group row">
				<div class="col-md-6">
					<div class="form-group">
						<label for="">Reject Reason</label>
						<textarea name="reject_reason" id="" class="form-control form-control-sm" rows="10"></textarea>
					</div>
				</div>
				<div class="col-md-12" style="margin-top: 2vh;">
					<button type="button" class="btn btn-primary" name="save" id="save">Approve</button>
					<button type="button" class="btn btn-danger" name="reject" id="reject">Reject</button>
					<button type="button" class="btn btn-danger" style='' name="back" id="back">Back</button>
				</div>
			</div>
		</form>
	</div>
</div>

<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" style='width:70%;'>
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;Detail Data</h4>
			</div>
			<div class="modal-body" id="ModalView">
				...
			</div>
		</div>
	</div>


	<script src="<?= base_url('assets/js/jquery.maskMoney.js') ?>"></script>
	<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
	<style>
		.datepicker {
			cursor: pointer;
		}
	</style>

	<script type="text/javascript">
		//$('#input-kendaraan').hide();
		var base_url = '<?php echo base_url(); ?>';
		var active_controller = '<?php echo ($this->uri->segment(1)); ?>';
		var tingkat_approval = $("#tingkat_approval").val();

		$(document).ready(function() {
			$('.datepicker').datepicker({
				dateFormat: 'dd-M-yy'
			});
			$('.autoNumeric5').autoNumeric('init', {
				mDec: '5',
				aPad: false
			})
			$('.chosen-select').select2()

			$("#chk_all").click(function() {
				$('input:checkbox').not(this).prop('checked', this.checked);
			});

			//back
			$(document).on('click', '#back', function() {
				if (tingkat_approval == "1") {
					window.location.href = base_url + active_controller + '/approval_head';
				}
				if (tingkat_approval == "2") {
					window.location.href = base_url + active_controller + '/approval_cost_control';
				}
				if (tingkat_approval == "3") {
					window.location.href = base_url + active_controller + '/approval_cost_control';
				}
			});

			$('#save').click(function(e) {
				e.preventDefault();

				if ($('.chk_personal:checked').length == 0) {
					swal({
						title: "Error Message!",
						text: 'Checklist Minimal Satu !',
						type: "warning"
					});
					return false;
				}

				swal({
						title: "Are you sure?",
						text: "You will not be able to process again this data!",
						type: "warning",
						showCancelButton: true,
						confirmButtonClass: "btn-danger",
						confirmButtonText: "Yes, Process it!",
						cancelButtonText: "No, cancel process!",
						closeOnConfirm: true,
						closeOnCancel: false
					},
					function(isConfirm) {
						if (isConfirm) {
							var formData = new FormData($('#data-form')[0]);
							var baseurl = siteurl + active_controller + '/process_approval_all';
							$.ajax({
								url: baseurl,
								type: "POST",
								data: formData,
								cache: false,
								dataType: 'json',
								processData: false,
								contentType: false,
								success: function(data) {
									if (data.status == 1) {
										swal({
											title: "Save Success!",
											text: data.pesan,
											type: "success",
											timer: 7000
										});
										if (tingkat_approval == "1") {
											window.location.href = base_url + active_controller + '/approval_head';
										}
										if (tingkat_approval == "2") {
											window.location.href = base_url + active_controller + '/approval_cost_control';
										}
										if (tingkat_approval == "3") {
											window.location.href = base_url + active_controller + '/approval_management';
										}
									} else {
										swal({
											title: "Save Failed!",
											text: data.pesan,
											type: "warning",
											timer: 7000
										});
									}
								},
								error: function() {

									swal({
										title: "Error Message !",
										text: 'An Error Occured During Process. Please try again..',
										type: "warning",
										timer: 7000
									});
								}
							});
						} else {
							swal("Cancelled", "Data can be process again :)", "error");
							return false;
						}
					});
			});

			$("#reject").click(function(e) {
				swal({
						title: "Are you sure to Reject this data ?",
						text: "You process this data again from Approval Head !",
						type: "warning",
						showCancelButton: true,
						confirmButtonClass: "btn-danger",
						confirmButtonText: "Yes, Reject it!",
						cancelButtonText: "No, cancel process!",
						closeOnConfirm: true,
						closeOnCancel: false
					},
					function(isConfirm) {
						if (isConfirm) {
							var formData = new FormData($('#data-form')[0]);
							var baseurl = siteurl + active_controller + '/process_reject_all';
							$.ajax({
								url: baseurl,
								type: "POST",
								data: formData,
								cache: false,
								dataType: 'json',
								processData: false,
								contentType: false,
								success: function(data) {
									if (data.status == 1) {
										swal({
											title: "Reject Success!",
											text: data.pesan,
											type: "success",
											timer: 7000
										});
										if (tingkat_approval == "1") {
											window.location.href = base_url + active_controller + '/approval_head';
										}
										if (tingkat_approval == "2") {
											window.location.href = base_url + active_controller + '/approval_cost_control';
										}
										if (tingkat_approval == "3") {
											window.location.href = base_url + active_controller + '/approval_management';
										}
									} else {
										swal({
											title: "Reject Failed!",
											text: data.pesan,
											type: "warning",
											timer: 7000
										});
									}
								},
								error: function() {

									swal({
										title: "Error Message !",
										text: 'An Error Occured During Process. Please try again..',
										type: "warning",
										timer: 7000
									});
								}
							});
						} else {
							swal("Cancelled", "Data can be process again :)", "error");
							return false;
						}
					});
			});

			$(document).on('click', '.processSatuan', function(e) {
				e.preventDefault()
				var id = $(this).data('id');
				var action = $(this).data('action');
				var so_number = $('#so_number').val();
				var pr_rev = $('#pr_rev_' + id).val();
				// alert(id);
				swal({
						title: "Anda Yakin?",
						text: "Process " + action + " PR !",
						type: "warning",
						showCancelButton: true,
						confirmButtonClass: "btn-info",
						confirmButtonText: "Ya!",
						cancelButtonText: "Batal",
						closeOnConfirm: false
					},
					function() {
						$.ajax({
							type: 'POST',
							url: base_url + active_controller + '/process_approval_satuan',
							dataType: "json",
							data: {
								'id': id,
								'action': action,
								'so_number': so_number,
								'pr_rev': pr_rev
							},
							success: function(result) {
								if (result.status == '1') {
									swal({
											title: "Sukses",
											text: result.pesan,
											type: "success"
										},
										function() {
											window.location.href = base_url + active_controller + '/approval_planning/' + result.so_number + '/' + tingkat_approval
										})
								} else {
									swal({
										title: "Error",
										text: result.pesan,
										type: "error"
									})

								}
							},
							error: function() {
								swal({
									title: "Error",
									text: "Data error. Gagal request Ajax",
									type: "error"
								})
							}
						})
					});

			})

		});
	</script>