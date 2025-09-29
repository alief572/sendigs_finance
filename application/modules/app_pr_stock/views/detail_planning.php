<?php
$pembeda = substr($header[0]['so_number'], 0, 1);
$due_date = (!empty($header[0]['due_date'])) ? date('d F Y', strtotime($header[0]['due_date'])) : '-';
$tgl_dibutuhkan = (!empty($header[0]['tgl_dibutuhkan'])) ? date('d F Y', strtotime($header[0]['tgl_dibutuhkan'])) : '-';
?>
<div class="box box-primary">
	<div class="box-body">
		<form id="data-form" method="post" autocomplete="off"><br>
			<input type="hidden" name='so_number' id='so_number' value='<?= $header[0]['so_number']; ?>'>
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
								<th class='text-center th'>#</th>
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
							$key = 0;
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
									echo "<td class='text-center'>" . $key . "</td>";
								}
								echo "<td class='text-left'>" . $nm_material . "
										<input type='hidden' name='detail[" . $key . "][id]' value='" . $value['id'] . "'>
										</td>";
								echo "<td class='text-right min_stok'>" . number_format($kebutuhan) . "</td>";
								echo "<td class='text-right max_stok'>" . number_format($kebutuhan * 1.5) . "</td>";
								echo "<td class='text-right min_order'>" . number_format($stock) . "</td>";
								echo "<td class='text-right'>" . number_format($propose * $konversi) . "</td>";
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
				window.location.href = base_url + active_controller + '/approval_management'
			});
		});
	</script>