<div class="box box-primary">
	<div class="box-header with-border">
		<h3 class="box-title">
			<i class="fa fa-cubes"></i> Kelola Budget Stock &mdash; 
			<span class="text-primary font-weight-bold"><?= !empty($warehouse) ? strtoupper($warehouse->nm_gudang) : 'SENTRAL SISTEM' ?></span>
		</h3>
		<div class="pull-right">
			<button type="button" class="btn btn-sm btn-success" id="btn-download-excel" title="Download Excel Budget Stock">
				<i class="fa fa-download"></i> Download Excel
			</button>
			<button type="button" class="btn btn-sm btn-default" onclick="cancel()" title="Kembali ke Daftar">
				<i class="fa fa-arrow-left"></i> Kembali
			</button>
		</div>
	</div>

	<?= form_open('budget_rutin/save_data', ['id' => 'frm_budget_all', 'name' => 'frm_budget_all', 'role' => 'form']) ?>
	<div class="box-body">
		<!-- Header Info Bar -->
		<div class="row" style="margin-bottom: 15px; background: #f9f9f9; padding: 12px 15px; border-radius: 4px; border: 1px solid #e3e3e3;">
			<div class="col-md-4">
				<label class="text-muted" style="margin-bottom:2px; font-size:12px;">Warehouse / Gudang:</label>
				<div style="font-size: 15px; font-weight: bold; color: #333;">
					<i class="fa fa-building-o text-primary"></i> <?= !empty($warehouse) ? strtoupper($warehouse->nm_gudang) : 'SENTRAL SISTEM' ?>
				</div>
			</div>
			<div class="col-md-4">
				<label class="text-muted" style="margin-bottom:2px; font-size:12px;">Terakhir Diperbarui:</label>
				<div style="font-size: 15px; font-weight: bold; color: #333;">
					<i class="fa fa-calendar text-primary"></i> <?= !empty($data->modified_on) ? date('d-M-Y H:i', strtotime($data->modified_on)) : date('d-M-Y') ?>
				</div>
			</div>
			<div class="col-md-4 text-right">
				<label class="text-muted" style="margin-bottom:2px; font-size:12px;">Grand Total Budget Stock:</label>
				<div style="font-size: 18px; font-weight: bold; color: #008d4c;" id="header_grand_total">
					Rp 0
				</div>
			</div>
		</div>

		<input type="hidden" id="id" name="id" value="<?= !empty($code_budget) ? $code_budget : 'BR-00002' ?>">

		<!-- Nav Tabs Kategori -->
		<div class="nav-tabs-custom">
			<ul class="nav nav-tabs" role="tablist" id="categoryTabs">
				<?php 
				$first = true;
				if (!empty($categories)):
					foreach ($categories as $cat):
				?>
					<li class="<?= $first ? 'active' : '' ?>">
						<a href="#tab_cat_<?= $cat->id ?>" data-toggle="tab" aria-expanded="<?= $first ? 'true' : 'false' ?>">
							<i class="fa fa-tags text-primary"></i> <b><?= strtoupper($cat->nm_category) ?></b> 
							<span class="badge bg-blue" id="tab_badge_<?= $cat->id ?>">Rp 0</span>
						</a>
					</li>
				<?php 
						$first = false;
					endforeach;
				endif; 
				?>
			</ul>

			<div class="tab-content" style="padding: 15px 5px;">
				<?php 
				$first_tab = true;
				$overall_grand_total = 0;
				if (!empty($categories)):
					foreach ($categories as $cat):
						$cat_items = $items_by_cat[$cat->id] ?? [];
						$cat_subtotal = 0;
				?>
					<div class="tab-pane <?= $first_tab ? 'active' : '' ?>" id="tab_cat_<?= $cat->id ?>">
						<div style="margin-bottom: 10px; display:flex; justify-content:space-between; align-items:center;">
							<h4 style="margin: 0; font-weight: bold; color: #333;">
								<i class="fa fa-list text-primary"></i> Daftar Barang: <?= strtoupper($cat->nm_category) ?>
								<small class="text-muted">(<?= count($cat_items) ?> item)</small>
							</h4>
						</div>

						<table class="table table-bordered table-striped table-hover" width="100%">
							<thead>
								<tr class="bg-blue">
									<th class="text-center" width="4%">#</th>
									<th class="text-center" width="12%">Kode Stok</th>
									<th width="26%">Nama Barang</th>
									<th width="16%">Spesifikasi</th>
									<th class="text-center" width="8%">Satuan</th>
									<th class="text-right" width="12%">Price Reference (IDR)</th>
									<th class="text-center" width="10%">Kebutuhan 1 Bulan</th>
									<th class="text-right" width="12%">Total Price (IDR)</th>
								</tr>
							</thead>
							<tbody>
								<?php 
								$no = 0;
								if (!empty($cat_items)):
									foreach ($cat_items as $item):
										$no++;
										$price_ref = floatval($item->price_ref_use ?? 0);
										$qty = floatval($item->kebutuhan_month ?? 0);
										$total_item_price = $qty * $price_ref;
										$cat_subtotal += $total_item_price;
										$overall_grand_total += $total_item_price;
								?>
									<tr>
										<td class="text-center" style="vertical-align: middle;"><?= $no ?></td>
										<td class="text-center" style="vertical-align: middle;">
											<span class="label label-default"><?= htmlspecialchars($item->id_stock ?? '-') ?></span>
										</td>
										<td style="vertical-align: middle;">
											<b><?= htmlspecialchars($item->stock_name) ?></b>
										</td>
										<td style="vertical-align: middle; font-size: 12px; color: #555;">
											<?= htmlspecialchars($item->spec ?? '-') ?>
										</td>
										<td class="text-center" style="vertical-align: middle;">
											<input type="hidden" name="items[<?= $item->id_barang ?>][jenis_barang]" value="<?= $cat->id ?>">
											<input type="hidden" name="items[<?= $item->id_barang ?>][satuan]" value="<?= $item->id_unit ?>">
											<span class="badge bg-navy"><?= strtoupper($item->nm_satuan ?? 'PCS') ?></span>
										</td>
										<td class="text-right" style="vertical-align: middle;">
											<input type="hidden" name="items[<?= $item->id_barang ?>][price_reference]" value="<?= $price_ref ?>" id="price_val_<?= $item->id_barang ?>">
											<span class="text-bold" style="color: #2e6da4;">Rp <?= number_format($price_ref, 0, ',', '.') ?></span>
										</td>
										<td class="text-center" style="vertical-align: middle;">
											<input type="text" 
												   name="items[<?= $item->id_barang ?>][kebutuhan_month]" 
												   class="form-control input-sm text-center autoNumeric0 input_kebutuhan" 
												   id="kebutuhan_<?= $item->id_barang ?>" 
												   data-id="<?= $item->id_barang ?>" 
												   data-cat="<?= $cat->id ?>" 
												   data-price="<?= $price_ref ?>" 
												   value="<?= $qty > 0 ? $qty : '' ?>" 
												   placeholder="0">
										</td>
										<td class="text-right" style="vertical-align: middle;">
											<input type="hidden" class="item_total_raw item_cat_<?= $cat->id ?>" id="total_raw_<?= $item->id_barang ?>" value="<?= $total_item_price ?>">
											<span class="text-bold item_total_display" id="total_display_<?= $item->id_barang ?>" style="color: #008d4c;">
												Rp <?= number_format($total_item_price, 0, ',', '.') ?>
											</span>
										</td>
									</tr>
								<?php 
									endforeach;
								else:
								?>
									<tr>
										<td colspan="8" class="text-center text-muted">Tidak ada data master barang pada kategori ini.</td>
									</tr>
								<?php endif; ?>
							</tbody>
							<tfoot>
								<tr class="bg-blue" style="font-size: 13px;">
									<th colspan="7" class="text-right" style="vertical-align: middle;">
										SUBTOTAL KATEGORI <?= strtoupper($cat->nm_category) ?>:
									</th>
									<th class="text-right subtotal_cat_display" id="subtotal_display_<?= $cat->id ?>" style="vertical-align: middle;">
										Rp <?= number_format($cat_subtotal, 0, ',', '.') ?>
									</th>
								</tr>
							</tfoot>
						</table>
					</div>
				<?php 
						$first_tab = false;
					endforeach;
				endif; 
				?>
			</div>
		</div>

		<!-- Grand Total Bar -->
		<div class="row" style="background: #e8f4f8; padding: 15px; border-radius: 4px; border: 1px solid #bce8f1; margin: 10px 0;">
			<div class="col-md-6" style="font-size: 16px; font-weight: bold; color: #31708f; line-height: 35px;">
				<i class="fa fa-calculator"></i> TOTAL KESELURUHAN BUDGET STOCK (SEMUA KATEGORI):
			</div>
			<div class="col-md-6 text-right" style="font-size: 22px; font-weight: bold; color: #008d4c;" id="footer_grand_total">
				Rp <?= number_format($overall_grand_total, 0, ',', '.') ?>
			</div>
		</div>
	</div>

	<div class="box-footer">
		<button type="submit" class="btn btn-success btn-lg" id="btn-save-all">
			<i class="fa fa-save"></i> Simpan Seluruh Budget Stock
		</button>
		<button type="button" class="btn btn-default btn-lg" onclick="cancel()">
			<i class="fa fa-arrow-left"></i> Kembali ke Daftar
		</button>
	</div>
	<?= form_close() ?>
</div>

<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$('.autoNumeric0').autoNumeric('init', {
			mDec: '0',
			aPad: false,
			vMin: '0'
		});

		recalc_all();
	});

	// Real-time calculation on input change
	$(document).on('keyup change', '.input_kebutuhan', function() {
		var id = $(this).data('id');
		var cat_id = $(this).data('cat');
		var price = parseFloat($(this).data('price')) || 0;
		var qty_val = $(this).val().replace(/,/g, '');
		var qty = parseFloat(qty_val) || 0;

		var total = qty * price;

		$('#total_raw_' + id).val(total);
		$('#total_display_' + id).text('Rp ' + formatRupiah(total));

		recalc_category(cat_id);
		recalc_grand_total();
	});

	function recalc_category(cat_id) {
		var subtotal = 0;
		$('.item_cat_' + cat_id).each(function() {
			var val = parseFloat($(this).val()) || 0;
			subtotal += val;
		});
		$('#subtotal_display_' + cat_id).text('Rp ' + formatRupiah(subtotal));
		$('#tab_badge_' + cat_id).text('Rp ' + formatRupiah(subtotal));
	}

	function recalc_grand_total() {
		var grand = 0;
		$('.item_total_raw').each(function() {
			var val = parseFloat($(this).val()) || 0;
			grand += val;
		});
		var formatted = 'Rp ' + formatRupiah(grand);
		$('#header_grand_total').text(formatted);
		$('#footer_grand_total').text(formatted);
	}

	function recalc_all() {
		<?php if (!empty($categories)): foreach ($categories as $cat): ?>
			recalc_category(<?= $cat->id ?>);
		<?php endforeach; endif; ?>
		recalc_grand_total();
	}

	function formatRupiah(number) {
		return Math.round(number).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
	}

	// Submit via AJAX
	$('#frm_budget_all').on('submit', function(e) {
		e.preventDefault();
		var btn = $('#btn-save-all');
		btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');

		$.ajax({
			url: siteurl + "budget_rutin/save_data",
			type: "POST",
			dataType: "json",
			data: $(this).serialize(),
			success: function(res) {
				btn.prop('disabled', false).html('<i class="fa fa-save"></i> Simpan Seluruh Budget Stock');
				if (res.status == 1 || res.save == 1) {
					swal({
						title: "Berhasil!",
						text: res.pesan || "Seluruh data Budget Stock berhasil disimpan!",
						type: "success",
						timer: 1500,
						showConfirmButton: false
					});
					cancel();
				} else {
					swal("Gagal!", res.pesan || "Terjadi kesalahan saat menyimpan data.", "error");
				}
			},
			error: function() {
				btn.prop('disabled', false).html('<i class="fa fa-save"></i> Simpan Seluruh Budget Stock');
				swal("Error!", "Terjadi kesalahan koneksi saat menyimpan data.", "error");
			}
		});
	});

	// Download Excel
	$(document).on('click', '#btn-download-excel', function() {
		var code_budget = $('#id').val();
		window.open(siteurl + 'budget_rutin/download_budget_stock/' + encodeURIComponent(code_budget), '_blank');
	});

	function cancel() {
		$("#form-data").hide();
		$(".box").show();
		$('#example1').DataTable().ajax.reload(null, false);
	}
</script>
