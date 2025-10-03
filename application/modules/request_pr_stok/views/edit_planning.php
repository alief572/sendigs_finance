<?php
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
	if ($header[0]['app_1'] == '1') {
		$status1 = '<div class="badge bg-green">Approved</div>';
		$tgl_appre_1 = date('d F Y', strtotime($header[0]['app_1_date']));
	} else {
		if ($header[0]['sts_reject1'] == '1') {
			$status1 = '<div class="badge bg-red">Rejected</div>';
			$tgl_appre_1 = date('d F Y', strtotime($header[0]['sts_reject1_date']));
		}
	}

	if ($header[0]['app_2'] == '1') {
		$status2 = '<div class="badge bg-green">Approved</div>';
		$tgl_appre_2 = date('d F Y', strtotime($header[0]['app_2_date']));
	} else {
		if ($header[0]['sts_reject2'] == '1') {
			$status2 = '<div class="badge bg-red">Rejected</div>';
			$tgl_appre_2 = date('d F Y', strtotime($header[0]['sts_reject2_date']));
		}
	}

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
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.min.css">
<div class="box box-primary">
	<div class="box-body">
		<form id="data-form" method="post" autocomplete="off"><br>
			<input type="hidden" name='so_number' id='so_number' value='<?= $header[0]['so_number']; ?>'>
			<div class="form-group row">
				<div class="col-md-12">
					<table class='table' width='70%'>
						<tr>
							<td width='20%'>No Request / SO</td>
							<td width='1%'>:</td>
							<td width='29%'><?= $header[0]['so_number']; ?></td>
							<td width='20%'></td>
							<td width='1%'></td>
							<td width='29%'></td>
						</tr>
						<tr>
							<td>No. PR</td>
							<td>:</td>
							<td><?= $header[0]['no_pr']; ?></td>
							<td></td>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<td>Customer</td>
							<td>:</td>
							<td><?= $header[0]['nm_customer']; ?></td>
							<td></td>
							<td></td>
							<td></td>
						</tr>
						<?php
						$tgl_dibutuhkan = (!empty($header[0]['tgl_dibutuhkan'])) ? date('d F Y', strtotime($header[0]['tgl_dibutuhkan'])) : '';
						?>
						<tr>
							<td>Tgl Dibutuhkan</td>
							<td>:</td>
							<td><input type="date" name="tgl_dibutuhkan" id="" class="form-control form-control-sm" value="<?= $header[0]['tgl_dibutuhkan'] ?>"></td>
							<td></td>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<td>Tingkat PR</td>
							<td>:</td>
							<td>
								<select name="tingkat_pr" id="" class="form-control form-control-sm">
									<option value="1" <?= ($header[0]['tingkat_pr'] == 1) ? 'selected' : null ?>>Normal</option>
									<option value="2" <?= ($header[0]['tingkat_pr'] == 2) ? 'selected' : null ?>>Urgent</option>
								</select>
							</td>
							<td></td>
							<td></td>
							<td></td>
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
								<td class="text-center">Departement Head</td>
								<td class="text-center">
									<?= $status1 ?>
								</td>
								<td class="text-center">
									<?= $tgl_appre_1 ?>
								</td>
								<td>
									<input type="text" name="reject_reason1" id="" class="form-control" value="<?= $alasan_reject1 ?>" readonly>
								</td>
								<td>
									<input type="text" name="keterangan_1" id="" class="form-control" value="<?= $keterangan_1 ?>">
								</td>
							</tr>
							<tr>
								<td class="text-center">Cost Control</td>
								<td class="text-center">
									<?= $status2 ?>
								</td>
								<td class="text-center">
									<?= $tgl_appre_2 ?>
								</td>
								<td>
									<input type="text" name="reject_reason2" id="" class="form-control" value="<?= $alasan_reject2 ?>" readonly>
								</td>
								<td>
									<input type="text" name="keterangan_2" id="" class="form-control" value="<?= $keterangan_2 ?>">
								</td>
							</tr>
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
				<div class="col-md-12">
					<table class='table table-striped table-bordered table-hover table-condensed table_data' width='100%'>
						<thead class='thead'>
							<tr class='bg-blue'>
								<th class='text-center th'>#</th>
								<th class='text-center th'>Material Name</th>
								<th class='text-center th'>Min Stock</th>
								<th class='text-center th'>Max Stock</th>
								<th class='text-center th'>Min Order</th>
								<th class='text-center th'>Qty PR</th>
								<th class='text-center th'>Notes</th>
								<th class='text-center th'>#</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$no = 1;
							foreach ($list_stok_non_pr as $item) {
								echo '<tr>';
								echo '<td class="text-center">' . $no . '</td>';
								echo '<td>' . $item['stock_name'] . '</td>';
								echo '<td class="text-right">' . number_format($item['min_stok'], 2) . '</td>';
								echo '<td class="text-right">' . number_format($item['max_stok'], 2) . '</td>';
								echo '<td class="text-right">' . number_format(0, 2) . '</td>';
								echo '<td>';
								echo '<input type="text" class="form-control autoNumeric2 nmat_qty_' . $item['id'] . '">';
								echo '</td>';
								echo '<td>';
								echo '<input type="text" class="form-control nmat_notes_' . $item['id'] . '">';
								echo '</td>';
								echo '<td>';
								echo '<button type="button" class="btn btn-sm btn-success add_stock add_stock_' . $item['id'] . '" data-id="' . $item['id'] . '"><i class="fa fa-plus"></i></button>';
								echo '</td>';
								echo '</tr>';

								$no++;
							}
							?>
						</tbody>
					</table>
				</div>
				<div class="col-md-12">
					<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
						<thead class='thead'>
							<tr class='bg-blue'>
								<th class='text-center th'>#</th>
								<th class='text-center th'>Material Name</th>
								<th class='text-center th'>Min Stock</th>
								<th class='text-center th'>Max Stock</th>
								<th class='text-center th'>Min Order</th>
								<th class='text-center th'>Qty PR</th>
								<th class='text-center th'>#</th>
								<th class='text-center th'>Notes</th>
								<th class='text-center th'>#</th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ($detail as $key => $value) {
								$key++;
								$stock_free 	= $value['stock_free'];
								$use_stock 		= $value['use_stock'];
								$sisa_free 		= $stock_free - $use_stock;
								$propose 		= $value['propose_purchase'];

								if ($propose > 0) {
									echo "<tr>";
									echo "<td class='text-center'>" . $key . "</td>";
									echo "	<td class='text-left'>" . $value['stock_name'] . "</td>";
									echo "<td class='text-right min_stok'>" . number_format($value['min_stok'], 2) . "</td>";
									echo "<td class='text-right max_stok'>" . number_format($value['max_stok'], 2) . "</td>";
									echo "<td class='text-right min_order'>" . number_format(0, 2) . "</td>";
									if ($value['status_app'] == 'N') {
										echo "<td align='center'>";
										echo "<input type='hidden' name='detail[" . $key . "][id]' value='" . $value['id'] . "'>";
										echo "<input type='text' name='detail[" . $key . "][qty]' class='form-control input-sm text-center qty_" . $value['id'] . " autoNumeric2' style='width:100px;' value='" . $propose . "'>";
										echo "</td>";
										echo "<td class='text-center'><span class='badge bg-blue text-bold'>Waiting Process</span></td>";
									}
									if ($value['status_app'] == 'Y') {
										echo "<td class='text-center'>" . number_format($propose, 2) . "</td>";
										echo "<td class='text-center'><span class='badge bg-green text-bold'>Approved</span></td>";
									}
									if ($value['status_app'] == 'D') {
										echo "<td class='text-center'>" . number_format($propose, 2) . "</td>";
										echo "<td class='text-center'><span class='badge bg-red text-bold'>Rejected</span></td>";
									}

									echo "<td>";
									echo "<input type='text' class='form-control input-sm notes_" . $value['id'] . "' name='detail[" . $key . "][notes]' value='" . $value['note'] . "'>";
									echo "</td>";
									echo "<td class='text-center'>";
									echo "<button type='button' class='btn btn-sm btn-warning edit_detail edit_detail_" . $value['id'] . "' data-key='" . $key . "' data-id='" . $value['id'] . "' style='margin-right: 0.5em;'><i class='fa fa-pencil'></i></button>";
									echo "<button type='button' class='btn btn-sm btn-danger del_detail del_detail_" . $value['id'] . "' data-key='" . $key . "' data-id='" . $value['id'] . "'><i class='fa fa-trash'></i></button>";
									echo "</td>";
									echo "</tr>";
								}
							}
							?>
						</tbody>
					</table>
				</div>
			</div>

			<div class="form-group row">
				<div class="col-md-12">
					<button type="button" class="btn btn-primary" name="save" id="save">Update</button>
					<button type="button" class="btn btn-danger" style='margin-left:5px;' name="back" id="back">Back</button>
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


	<script src="https://cdn.datatables.net/2.0.7/js/dataTables.min.js"></script>
	<script src="<?= base_url('assets/js/jquery.maskMoney.js') ?>"></script>
	<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>

	<script type="text/javascript">
		var base_url = '<?php echo base_url(); ?>';
		var active_controller = '<?php echo ($this->uri->segment(1)); ?>';

		var so_number = $('#so_number').val();

		$(document).ready(function() {
			$('.table_data').dataTable();
			$('.datepicker').datepicker({
				dateFormat: 'dd-M-yy'
			});
			$('.autoNumeric5').autoNumeric('init', {
				mDec: '5',
				aPad: false
			})
			$('.autoNumeric2').autoNumeric('init', {
				mDec: '2',
				aPad: false
			})
			$('.chosen-select').select2()

			//back
			$(document).on('click', '#back', function() {
				window.location.href = base_url + active_controller
			});

			$('#save').click(function(e) {
				e.preventDefault();

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
							var baseurl = siteurl + active_controller + '/process_update_all';
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
										window.location.href = base_url + active_controller
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

			function refresh_pr_detail() {
				var so_number = $("#so_number").val();

				$.ajax({
					type: "POST",
					url: siteurl + active_controller + '/refresh_pr_detail',
					data: {
						'so_number': so_number
					},
					cache: false,
					success: function(result) {
						$("#list_pr_detail").html(result);

						$('.datepicker').datepicker({
							dateFormat: 'dd-M-yy'
						});
						$('.autoNumeric5').autoNumeric('init', {
							mDec: '5',
							aPad: false
						})
						$('.autoNumeric2').autoNumeric('init', {
							mDec: '2',
							aPad: false
						})
						$('.chosen-select').select2()
					},
					error: function(result) {

					}
				});
			}

			$(document).on('click', '.edit_detail', function() {
				var key = $(this).data('key');
				var id = $(this).data('id');
				var qty = $('.qty_' + id).val();
				if (qty == '' || qty == null) {
					qty = 0;
				} else {
					qty = qty.split(',').join('');
					qty = parseFloat(qty);
				}
				var notes = $('.notes_' + id).val();

				$.ajax({
					type: "POST",
					url: siteurl + active_controller + '/edit_detail',
					data: {
						'key': key,
						'id': id,
						'qty': qty,
						'notes': notes
					},
					cache: false,
					dataType: 'json',
					beforeSend: function() {
						$('.edit_detail_' + id).html('<i class="fa fa-spin fa-spinner"></i>');
					},
					success: function(result) {
						if (result.status == 1) {
							swal({
								type: 'success',
								title: 'Success !',
								text: 'Success, Material data has been updated !'
							});
						} else {
							swal({
								type: 'error',
								title: 'Failed !',
								text: 'Failed, Material data has not been updated !'
							});
						}

						$('.edit_detail_' + id).html('<i class="fa fa-pencil"></i>');
					},
					error: function(result) {
						swal({
							title: 'Sorry !',
							text: 'Please try again later !',
							type: 'error'
						});

						$('.edit_detail_' + id).html('<i class="fa fa-pencil"></i>');
					}
				});
			});

			$(document).on('click', '.del_detail', function() {
				var key = $(this).data('key');
				var id = $(this).data('id');

				swal({
						title: "Are you sure?",
						text: "This data will permanently deleted !",
						type: "warning",
						showCancelButton: true,
						confirmButtonClass: "btn-danger",
						confirmButtonText: "Yes, Delete it!",
						cancelButtonText: "No, cancel delete!",
						closeOnConfirm: true,
						closeOnCancel: false
					},
					function(isConfirm) {
						if (isConfirm) {
							$.ajax({
								type: "POST",
								url: siteurl + active_controller + '/del_detail',
								data: {
									'id': id
								},
								cache: false,
								dataType: 'json',
								success: function(result) {
									if (result.status == '1') {
										swal({
											title: 'Success !',
											text: 'The data has been deleted !',
											type: 'success'
										}, function(isConfirm) {
											if (isConfirm) {
												location.reload();
											}
										});
									} else {
										swal({
											title: 'Failed !',
											text: 'The data has not been deleted !',
											type: 'error'
										});
									}
								},
								error: function(result) {
									swal({
										type: 'error',
										title: 'Error !',
										text: 'Please try again later !'
									});
								}
							});
						} else {
							swal("Cancelled", "Data can be process again :)", "error");
							return false;
						}
					});
			});

			$(document).on('click', '.add_stock', function() {
				var id = $(this).data('id');
				var qty = $('.nmat_qty_' + id).val();
				if (qty == '' || qty == null) {
					qty = 0;
				} else {
					qty = qty.split(',').join('');
					qty = parseFloat(qty);
				}
				var notes = $('.nmat_notes_' + id).val();

				$.ajax({
					type: "POST",
					url: siteurl + active_controller + '/add_stok',
					data: {
						'so_number': so_number,
						'id': id,
						'qty': qty,
						'notes': notes
					},
					cache: false,
					dataType: 'json',
					beforeSend: function() {
						$('.add_stock_' + id).html('<i class="fa fa-spin fa-spinner"></i>');
						$('.add_stock_' + id).prop('disabled', true);
					},
					success: function(result) {
						if (result.status == '1') {
							swal({
								type: 'success',
								title: 'Success !',
							}, function(next) {
								location.reload();
							});
						} else {
							swal({
								type: 'warning',
								title: 'Failed !'
							});
						}
					},
					error: function(result) {
						swal({
							type: 'error',
							title: 'Error !',
							text: 'Please try again later !'
						});
					}
				})
			});
		});
	</script>