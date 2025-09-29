<div class="nav-tabs-area">
	<!-- /.tab-content -->
	<div class="tab-content">
		<div class="tab-pane active" id="area">
			<!-- Biodata Mitra -->
			<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
			<!-- form start-->
			<div class="box box-primary">
				<?= form_open($this->uri->uri_string(), array('id' => 'frm_data', 'name' => 'frm_data', 'role' => 'form', 'class' => 'form-horizontal')) ?>
				<div class="box-body">
					<div class="form-group row">
						<div class="col-md-2 text-bold">Warehouse</div>
						<div class="col-md-3">
							<?php
							$datdepartemen[0]	= 'Select An Option';
							echo form_dropdown('department', $datdepartemen, set_value('department', isset($data->department) ? $data->department : '0'), array('id' => 'department', 'class' => 'form-control select2', 'style' => 'width:100%;', 'required' => 'required'));
							?>
						</div>
						<div class="col-md-7"></div>
					</div>
					<div class="row" hidden>
						<div class="col-md-6">
							<?php if (isset($data->code_budget)) {
								$type = 'edit';
							} ?>
							<input type="hidden" id="type" name="type" value="<?= isset($type) ? $type : 'add' ?>">
							<input type="hidden" id="id" name="id" value="<?php echo set_value('id', isset($data->code_budget) ? $data->code_budget : ''); ?>">
							<input type="hidden" id="rev" name="rev" value="<?php echo (isset($data->rev) ? $data->rev : '0'); ?>">
						</div>
						<div class="col-md-6">
							<div class="form-group ">
								<label class="col-sm-4 control-label">Cost Center</label>
								<div class="col-sm-8">
									<div class="input-group">
										<?php
										$datcostcenter[0]	= 'Select An Option';
										echo form_dropdown('costcenter', $datcostcenter, set_value('costcenter', isset($data->costcenter) ? $data->costcenter : '0'), array('id' => 'costcenter', 'class' => 'form-control', 'style' => 'width:100%;'));
										?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<hr>
					<?php
					$totals = 0;
					if (isset($jenisrutin)) {
						foreach ($jenisrutin as $key) {
							echo "<h4>" . strtoupper($key->nm_category) . " <button type='button' data-id_barang='" . $key->id . "' class='btn btn-sm btn-success addPart pull-right' title='Add Item'>Add Item</button></h4>
						<table class='table table-striped table-bordered table-hover table-condensed' width='100%' id='tbl_" . $key->id . "'>
							<thead>
								<tr class='bg-blue'>
									<th class='text-center' style='width: 5%;'>#</th>
									<th class='text-center' style='width: 30%;'>Nama Barang</th>
									<th class='text-center'>Spesifikasi</th>
									<th class='text-center' style='width: 15%;'>Kebutuhan 1 Bulan</th>
									<th class='text-center' style='width: 15%;'>Satuan Product</th>
									<th class='text-center' style='width: 15%;'>Price Reference</th>
									<th class='text-center' style='width: 15%;'>Total Price</th>
									<th class='text-center' style='width: 5%;'>#</th>
								</tr>
							</thead>
							<tbody class='tbody_" . $key->id . "'>						
							</tbody>
							<tfoot>
								<tr class='bg-blue'>
									<th colspan='6' class='text-center'>Total Budget Stock</th>
									<th class='text-right tfoot_" . $key->id . "'></th>
									<th></th>
								</tr>
							</tfoot>
						</table>	  
						";
						}
					} else {
						$nojenis = 0;
						foreach ($data_jenis as $item_jenis) {
							echo "<h4>" . strtoupper($item_jenis->nm_jenis);
							echo '<button type="button" data-id_barang="' . $item_jenis->id_jenis . '" class="btn btn-sm btn-success addPart pull-right" title="Add Item">Add Item</button></h4>';
							echo '<table class="table table-striped table-bordered table-hover table-condensed" width="100%" id="tbl_' . $item_jenis->id_jenis . '">';
							echo '<thead>';
							echo '
								<tr class="bg-blue">
									<th class="text-center" style="width: 5%;">#</th>
									<th class="text-center" style="width: 30%;">Nama Barang</th>
									<th class="text-center">Spesifikasi</th>
									<th class="text-center" style="width: 15%;">Kebutuhan 1 Bulan</th>
									<th class="text-center" style="width: 15%;">Satuan Product</th>
									<th class="text-center" style="width: 15%;">Price Reference</th>
									<th class="text-center" style="width: 15%;">Total Price</th>
									<th class="text-center" style="width: 5%;">#</th>
								</tr>
							';
							echo '</thead>';
							echo '<tbody class="tbody_' . $item_jenis->id_jenis . '">';
							$total_budget_stock = 0;
							foreach ($data_detail as $key) {
								if ($key->id_type == $item_jenis->id_jenis) {
									$nojenis++;
									echo '
										<tr>
											<td class="text-center">' . $nojenis . '<input type="hidden" name="jenis_barang[]" value="' . $key->jenis_barang . '"></td>
											<td><input type="hidden" name="id_barang[]" value="' . $key->id_barang . '">' . $key->id_barang . ' - ' . $key->nama_barang . '</td>
											<td>' . $key->spec1 . '</td>
											<td><input type="text" class="form-control input-md text-center autoNumeric0 hitung_total_budget_stock" name="kebutuhan_month[]" id="kebutuhan_month_' . $nojenis . '" onchange="hitungPrice(' . $nojenis . ')" data-id_type="' . $key->id_type . '" value="' . $key->kebutuhan_month . '"></td>
											<td class="text-center"><input type="hidden" name="satuan[]" value="' . $key->id_satuan . '">' . $key->nm_satuan . '</td>
											<td class="text-center"><input type="text" name="price_reference[]" class="form-control form-control-sm text-right hitung_total_budget_stock autoNumeric0" id="price_reference_' . $nojenis . '" onchange="hitungPrice(' . $nojenis . ')" data-id_type="' . $key->id_type . '" value="' . $key->price_reference . '"></td>
											<td class="text-center"><input type="text" name="total_price[]" class="form-control form-control-sm text-right autoNumeric0 total_price_' . $key->id_type . '" id="total_price_' . $nojenis . '" onchange="hitungPrice(' . $nojenis . ')" value="' . $key->total_price . '" readonly></td>
											<td class="text-center"><button type="button" class="btn btn-sm btn-danger delPart" title="Delete Part"><i class="fa fa-close"></i></button></td>
										</tr>
									';

									$total_budget_stock += $key->total_price;
								}
							}
							echo '</tbody>';

							echo '<tfoot>';
							echo '<tr class="bg-blue">';
							echo '<th colspan="6" class="text-center">Total Budget Stock</th>';
							echo '<th class="text-right tfoot_' . $item_jenis->id_jenis . '">' . number_format($total_budget_stock) . '</th>';
							echo '<th></th>';
							echo '</tr>';
							echo '</tfoot>';

							echo '</table>';
						}
						// 			if (isset($data_detail)) {
						// 				$jenisrutin = '';
						// 				$nojenis = 1;
						// 				$total_budget_stock = 0;
						// 				foreach ($data_detail as $key) {
						// 					if ($jenisrutin != $key->id_type) {
						// 						if ($totals > 0) echo '</tbody></table>'; // Menutup tbody dan tabel sebelumnya
						// 						echo "<h4>" . strtoupper($key->nm_jenis) . "
						// <button type='button' data-id_barang='" . $key->id_type . "' class='btn btn-sm btn-success addPart pull-right' title='Add Item'>Add Item</button></h4>
						// <table class='table table-striped table-bordered table-hover table-condensed' width='100%' id='tbl_" . $key->id_type . "'>
						//     <thead>
						//         <tr class='bg-blue'>
						//             <th class='text-center' style='width: 5%;'>#</th>
						//             <th class='text-center' style='width: 30%;'>Nama Barang</th>
						//             <th class='text-center'>Spesifikasi</th>
						//             <th class='text-center' style='width: 15%;'>Kebutuhan 1 Bulan</th>
						//             <th class='text-center' style='width: 15%;'>Satuan Product</th>
						//             <th class='text-center' style='width: 15%;'>Price Reference</th>
						//             <th class='text-center' style='width: 15%;'>Total Price</th>
						//             <th class='text-center' style='width: 5%;'>#</th>
						//         </tr>
						//     </thead>
						//     <tbody>";

						// 						$nojenis = 1;
						// 					}

						// 					$jenisrutin = $key->id_type;

						// 					if ($key->id_barang != '') {
						// 						echo '
						// <tr>
						//     <td class="text-center">' . $nojenis . '<input type="hidden" name="jenis_barang[]" value="' . $key->jenis_barang . '"></td>
						//     <td><input type="hidden" name="id_barang[]" value="' . $key->id_barang . '">' . $key->id_barang . ' - ' . $key->nama_barang . '</td>
						//     <td>' . $key->spec1 . '</td>
						//     <td><input type="text" class="form-control input-md text-center autoNumeric0 hitung_total_budget_stock" name="kebutuhan_month[]" id="kebutuhan_month_' . $totals . '" onchange="hitungPrice(' . $totals . ')" data-id_type="' . $key->id_type . '" value="' . $key->kebutuhan_month . '"></td>
						//     <td class="text-center"><input type="hidden" name="satuan[]" value="' . $key->id_satuan . '">' . $key->nm_satuan . '</td>
						//     <td class="text-center"><input type="text" name="price_reference[]" class="form-control form-control-sm text-right hitung_total_budget_stock autoNumeric0" id="price_reference_' . $totals . '" onchange="hitungPrice(' . $totals . ')" data-id_type="' . $key->id_type . '" value="' . $key->price_reference . '"></td>
						//     <td class="text-center"><input type="text" name="total_price[]" class="form-control form-control-sm text-right autoNumeric0" id="total_price_' . $totals . '" onchange="hitungPrice(' . $totals . ')" value="' . $key->total_price . '" readonly></td>
						//     <td class="text-center"><button type="button" class="btn btn-sm btn-danger delPart" title="Delete Part"><i class="fa fa-close"></i></button></td>
						// </tr>';
						// 						$nojenis++;

						// 						$total_budget_stock += $key->total_price;
						// 					}
						// 					$totals++;
						// 				}

						// 				// Menambahkan footer setelah setiap tabel
						// 				echo '</tbody>';

						// 				echo '<tfoot>';
						// 				echo '<tr>';
						// 				echo '<td colspan="6" class="text-center">Total Budget Stock</td>';
						// 				echo '<td class="text-right">' . number_format($total_budget_stock) . '</td>';
						// 				echo '<td></td>';
						// 				echo '</tr>';
						// 				echo '</tfoot>';

						// 				echo '</table>';
						// 			}
					} ?>
				</div>
				<div class="box-footer">
					<button type="submit" name="save" class="btn btn-success" id="submit">Save</button>
					<a class="btn btn-danger" data-toggle="modal" onclick="cancel()">Back</a>
				</div>
				<?= form_close() ?>
			</div>
		</div>
	</div>
</div>
<script src="<?= base_url('/assets/js/number-divider.min.js') ?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script type="text/javascript">
	var row = <?= $totals ?>;
	$(document).ready(function() {
		$(".divide").divide();
		$('.select2').select2()

		$(".autoNumeric0").autoNumeric('init', {
			mDec: '0',
			aPad: false
		});
	});
	$(document).on('click', '.addPart', function() {
		var jenis_barang = $(this).data('id_barang');
		$.ajax({
			url: siteurl + 'budget_rutin/get_material/' + jenis_barang,
			cache: false,
			type: "POST",
			dataType: "json",
			success: function(data) {
				var options = '<option value="">Select An Option</option>';
				var i;
				for (i = 0; i < data.length; i++) {
					row++;
					options += '<option value=' + data[i].id + ' data-id_spec="' + data[i].spec + '">' + data[i].stock_name + '</option>';
				}
				$('.tbody_' + jenis_barang).append('<tr><td align="center">#<input type="hidden" name="jenis_barang[]" value="' + jenis_barang + '"></td><td><select id="id_barang' + row + '" name="id_barang[]" class="form-control select2 input-md" required onchange="getsatuan(' + row + ')">' + options + '</select></td><td id="spek' + row + '"></td><td><input type="text" class="form-control input-md text-center hitung_total_budget_stock autoNumeric0" data-id_type="' + jenis_barang + '" name="kebutuhan_month[]" id="kebutuhan_month_' + row + '" onchange="hitungPrice(' + row + ')"></td><td><select id="satuan' + row + '" name="satuan[]" class="form-control input-md select2 text-center" required></select></td><td class="text-center"><input type="text" class="form-control form-control-sm text-right autoNumeric0 hitung_total_budget_stock" data-id_type="' + jenis_barang + '" name="price_reference[]" id="price_reference_' + row + '" onchange="hitungPrice(' + row + ')"></td><td class="text-center"><input type="text" class="form-control form-control-sm autoNumeric0 text-right total_price_' + jenis_barang + '" name="total_price[]" id="total_price_' + row + '" readonly></td><td align="center"><button type="button" class="btn btn-sm btn-danger delPart" title="Delete Part"><i class="fa fa-close"></i></button></td></tr>');
				$(".select2").select2();
				$(".autoNumeric0").autoNumeric('init', {
					mDec: '0',
					aPad: false
				});
			},
			error: function() {
				swal({
					title: "Error Message !",
					text: 'Connection Time Out. Please try again..',
					type: "warning",
					timer: 3000,
					showCancelButton: false,
					showConfirmButton: false,
					allowOutsideClick: false
				});
			}
		});
	});

	function getsatuan(id) {
		idbarang = $("#id_barang" + id).val();
		var idspec = $("#id_barang" + id).find(':selected').attr('data-id_spec');
		$("#spek" + id).html(idspec);
		if (idbarang != '') {
			$.ajax({
				url: siteurl + 'budget_rutin/get_satuan/' + idbarang,
				method: "POST",
				dataType: 'json',
				success: function(data) {
					// var html = '<option value="">Select An Option</option>';
					var html = '';
					var i;
					for (i = 0; i < data.length; i++) {
						html += '<option value=' + data[i].id + '>' + data[i].code + '</option>';
					}
					$('#satuan' + id).html(html);
					//					console.log(data);
				}
			});

			$.ajax({
				type: 'post',
				url: siteurl + active_controller + 'getPriceRef',
				data: {
					'id_barang': idbarang
				},
				cache: false,
				dataType: 'json',
				success: function(result) {
					var price_ref = result.nilai_price_ref;
					var kebutuhan_month = $('#kebutuhan_month_' + id).val();
					if (kebutuhan_month !== '') {
						kebutuhan_month = kebutuhan_month.split(',').join('');
						kebutuhan_month = parseFloat(kebutuhan_month);
					} else {
						kebutuhan_month = 0;
					}

					var total_price = (price_ref * kebutuhan_month);

					$('#price_reference_' + id).autoNumeric('set', price_ref);
					$('#total_price_' + id).autoNumeric('set', total_price);
				},
				error: function(result) {
					swal({
						type: 'error',
						title: 'Error !',
						text: 'Please try again later !',
						allowOutsideClick: false,
						showCancelButton: false,
						showConfirmButton: false,
						timer: 3000
					});
				}
			});
		} else {
			$('#satuan').html('');
		}
	}


	$(document).on('click', '.delPart', function() {
		$(this).closest("tr").remove();
	});

	function getcostcentre() {
		dept = $("#department").val();
		if (dept != '0') {
			$.ajax({
				url: siteurl + 'budget_rutin/get_cost_center/' + dept,
				method: "POST",
				dataType: 'json',
				success: function(data) {
					var html = '<option value="">Select An Option</option>';
					var i;
					for (i = 0; i < data.length; i++) {
						html += '<option value=' + data[i].id + '>' + data[i].cost_center + '</option>';
					}
					$('#costcenter').html(html);
					//					console.log(data);
				}
			});
		} else {
			$('#costcenter').html('');
		}
	}

	$('#frm_data').on('submit', function(e) {
		e.preventDefault();
		var formdata = $("#frm_data").serialize();
		$.ajax({
			url: siteurl + "budget_rutin/save_data",
			dataType: "json",
			type: 'POST',
			data: formdata,
			success: function(msg) {
				if (msg['save'] == '1') {
					swal({
						title: "Sukses!",
						text: "Data Berhasil Di Simpan",
						type: "success",
						timer: 1500,
						showConfirmButton: false
					});
					console.log(msg);
					cancel();
				} else {
					swal({
						title: "Gagal!",
						text: "Data Gagal Di Simpan",
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
	});

	function hitungPrice(id) {
		var kebutuhan_month = $('#kebutuhan_month_' + id).val();
		if (kebutuhan_month !== '') {
			var kebutuhan_month = kebutuhan_month.split(',').join('');
			var kebutuhan_month = parseFloat(kebutuhan_month);
		}
		var price_reference = $('#price_reference_' + id).val();
		if (price_reference !== '') {
			var price_reference = price_reference.split(',').join('');
			var price_reference = parseFloat(price_reference);
		}

		var total_price = (kebutuhan_month * price_reference);

		$('#total_price_' + id).autoNumeric('set', total_price);
	}

	$(document).on('change', '.hitung_total_budget_stock', function() {
		var id_type = $(this).data('id_type');

		var totalPrice = 0;

		$('.total_price_' + id_type).each(function() {
			var total = $(this).val();
			if (total !== '') {
				total = total.split(',').join('');
				total = parseFloat(total);
			} else {
				total = 0;
			}

			totalPrice += total;
		})

		$('.tfoot_' + id_type).html(number_format(totalPrice));
	});

	function number_format(number, decimals, dec_point, thousands_sep) {
		// Strip all characters but numerical ones.
		number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
		var n = !isFinite(+number) ? 0 : +number,
			prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
			sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
			dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
			s = '',
			toFixedFix = function(n, prec) {
				var k = Math.pow(10, prec);
				return '' + Math.round(n * k) / k;
			};
		// Fix for IE parseFloat(0.55).toFixed(0) = 0;
		s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
		if (s[0].length > 3) {
			s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
		}
		if ((s[1] || '').length < prec) {
			s[1] = s[1] || '';
			s[1] += new Array(prec - s[1].length + 1).join('0');
		}
		return s.join(dec);
	}

	function cancel() {
		window.location.reload();
	}
</script>