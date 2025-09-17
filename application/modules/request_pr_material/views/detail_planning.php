<?php
// Detail Approval
$alasan_reject1 = (!empty($header)) ? $header[0]['reject_reason1'] : '';
$alasan_reject2 = (!empty($header)) ? $header[0]['reject_reason2'] : '';
$alasan_reject3 = (!empty($header)) ? $header[0]['reject_reason3'] : '';

$keterangan_1 = (!empty($header)) ? $header[0]['keterangan_1'] : '';
$keterangan_2 = (!empty($header)) ? $header[0]['keterangan_2'] : '';
$keterangan_3 = (!empty($header)) ? $header[0]['keterangan_3'] : '';

$status1 = '';
$status2 = '';
$status3 = '';
if (!empty($header)) {
	if ($header[0]['app_1'] == '1') {
		$status1 = '<div class="badge bg-green">Approved</div>';
	} else {
		if ($header[0]['sts_reject1'] == '1') {
			$status1 = '<div class="badge bg-red">Rejected</div>';
		}
	}

	if ($header[0]['app_2'] == '1') {
		$status2 = '<div class="badge bg-green">Approved</div>';
	} else {
		if ($header[0]['sts_reject2'] == '1') {
			$status2 = '<div class="badge bg-red">Rejected</div>';
		}
	}

	if ($header[0]['app_3'] == '1') {
		$status3 = '<div class="badge bg-green">Approved</div>';
	} else {
		if ($header[0]['sts_reject3'] == '1') {
			$status3 = '<div class="badge bg-red">Rejected</div>';
		}
	}
}
?>
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
							<td><?= $tgl_dibutuhkan; ?></td>
							<td></td>
							<td></td>
							<td></td>
						</tr>
					</table>
				</div>
				<div class="form-group row">
					<div class="col-md-8">
						<table class="table">
							<thead>
								<tr>
									<th class="text-center">Approval By</th>
									<th class="text-center">Status</th>
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
									<td>
										<input type="text" name="reject_reason1" id="" class="form-control" value="<?= $alasan_reject1 ?>" readonly>
									</td>
									<td>
										<input type="text" name="keterangan_1" id="" class="form-control" value="<?= $keterangan_1 ?>" readonly>
									</td>
								</tr>
								<tr>
									<td class="text-center">Cost Control</td>
									<td class="text-center">
										<?= $status2 ?>
									</td>
									<td>
										<input type="text" name="reject_reason2" id="" class="form-control" value="<?= $alasan_reject2 ?>" readonly>
									</td>
									<td>
										<input type="text" name="keterangan_2" id="" class="form-control" value="<?= $keterangan_2 ?>" readonly>
									</td>
								</tr>
								<tr>
									<td class="text-center">Management</td>
									<td class="text-center">
										<?= $status3 ?>
									</td>
									<td>
										<input type="text" name="reject_reason3" id="" class="form-control" value="<?= $alasan_reject3 ?>" readonly>
									</td>
									<td>
										<input type="text" name="keterangan_3" id="" class="form-control" value="<?= $keterangan_3 ?>" readonly>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<div class="col-md-12">
					<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
						<thead class='thead'>
							<tr class='bg-blue'>
								<th class='text-center th'>#</th>
								<th class='text-center th'>Material Name</th>
								<!-- <th class='text-center th'>Estimasi (Kg)</th>
							<th class='text-center th'>Stock Free (Kg)</th>
							<th class='text-center th'>Use Stock (Kg)</th>
							<th class='text-center th'>Sisa Stock Free (Kg)</th> -->
								<th class='text-center th'>Min Stock</th>
								<th class='text-center th'>Max Stock</th>
								<th class='text-center th'>Min Order</th>
								<th class='text-center th'>Qty PR</th>
								<th class='text-center th'>Qty Rev</th>
								<th class='text-center th'>Note</th>
								<th class='text-center th'>#</th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ($detail as $key => $value) {
								$key++;
								$nm_material 	= (!empty($GET_LEVEL4[$value['id_material']]['nama'])) ? $GET_LEVEL4[$value['id_material']]['nama'] : '';
								$stock_free 	= $value['stock_free'];
								$use_stock 		= $value['use_stock'];
								$sisa_free 		= $stock_free - $use_stock;
								$propose 		= $value['propose_purchase'];

								if ($propose > 0) {
									echo "<tr>";
									echo "<td class='text-center'>" . $key . "</td>";
									echo "	<td class='text-left'>" . $nm_material . "
										<input type='hidden' name='detail[" . $key . "][id]' value='" . $value['id'] . "'>
										</td>";
									// echo "<td class='text-right qty_order'>".number_format($value['qty_order'],5)."</td>";
									// echo "<td class='text-right stock_free'>".number_format($stock_free,5)."</td>";
									// echo "<td class='text-right stock_free'>".number_format($use_stock,5)."</td>";
									// echo "<td class='text-right sisa_free'>".number_format($sisa_free,5)."</td>";
									echo "<td class='text-right min_stok'>" . number_format($value['min_stok'], 2) . "</td>";
									echo "<td class='text-right max_stok'>" . number_format($value['max_stok'], 2) . "</td>";
									echo "<td class='text-right min_order'>" . number_format(0, 2) . "</td>";
									echo "<td class='text-right'>" . number_format($propose, 2) . "</td>";
									echo "<td class='text-center'>" . number_format($value['propose_rev'], 2) . "</td>";
									echo "<td class='text-left'>".$value['note']."</td>";
									if ($value['status_app'] == 'N') {
										echo "<td class='text-center'><span class='badge bg-blue text-bold'>Waiting Process</span></td>";
									}
									if ($value['status_app'] == 'Y') {
										echo "<td class='text-center'><span class='badge bg-green text-bold'>Approved</span></td>";
									}
									if ($value['status_app'] == 'D') {
										echo "<td class='text-center'><span class='badge bg-red text-bold'>Rejected</span></td>";
									}
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
					<!-- <button type="button" class="btn btn-primary" name="save" id="save">Process</button> -->
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


	<script src="<?= base_url('assets/js/jquery.maskMoney.js') ?>"></script>
	<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>

	<script type="text/javascript">
		var base_url = '<?php echo base_url(); ?>';
		var active_controller = '<?php echo ($this->uri->segment(1)); ?>';

		$(document).ready(function() {
			$('.datepicker').datepicker({
				dateFormat: 'dd-M-yy'
			});
			$('.autoNumeric5').autoNumeric('init', {
				mDec: '5',
				aPad: false
			})
			$('.chosen-select').select2()

			//back
			$(document).on('click', '#back', function() {
				window.location.href = base_url + active_controller
			});
		});
	</script>