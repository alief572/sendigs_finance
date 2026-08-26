<?php
	$is_edit = ($type == 'edit');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/select2/select2.min.css')?>">
<style>
	.table-custom th, .table-custom td {
		vertical-align: middle !important;
		padding: 6px 8px !important;
	}
	.nav-tabs-custom > .nav-tabs > li.active {
		border-top-color: #3c8dbc;
	}
	.bg-header-cat {
		background-color: #f4f6f9;
		font-weight: bold;
	}
	.item-row-highlight {
		background-color: #e8f4fd !important;
	}
</style>

<div class="box box-primary">
	<div class="box-header with-border">
		<h3 class="box-title"><i class="fa <?= $is_edit ? 'fa-edit' : 'fa-plus' ?>"></i> <?= $is_edit ? 'Edit' : 'Form Input' ?> Pengajuan Price Supplier (Barang Stok)</h3>
		<div class="box-tools pull-right">
			<a href="<?= base_url('price_sup_barang_stok') ?>" class="btn btn-sm btn-default"><i class="fa fa-arrow-left"></i> Kembali</a>
		</div>
	</div>
	
	<form id="form_pengajuan" method="post" autocomplete="off" enctype="multipart/form-data">
		<input type="hidden" name="type" value="<?= $type ?>">
		<input type="hidden" name="no_doc" id="no_doc" value="<?= $no_doc ?>">

		<div class="box-body">
			<!-- Header Card Info -->
			<div class="panel panel-default">
				<div class="panel-heading" style="background:#eaf2f8; font-weight:bold;"><i class="fa fa-info-circle"></i> Informasi Header Dokumen</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<label>No. Dokumen Pengajuan</label>
								<input type="text" class="form-control" value="<?= $no_doc ?>" readonly style="font-weight:bold; background:#f9f9f9;">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Tanggal Dokumen <span class="text-danger">*</span></label>
								<input type="date" class="form-control" name="tanggal_doc" id="tanggal_doc" value="<?= $tanggal_doc ?>" required>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Kurs Saat Ini (USD -> IDR) <span class="text-danger">*</span></label>
								<div class="input-group">
									<input type="text" class="form-control text-right autoNumeric" name="kurs" id="kurs" value="<?= $kurs ?>" required>
									<span class="input-group-btn">
										<button type="button" class="btn btn-info btn-flat" id="btn-refresh-kurs" title="Ambil Kurs Terkini"><i class="fa fa-refresh"></i></button>
									</span>
								</div>
								<small class="text-muted">Kurs digunakan untuk auto kalkulasi USD / IDR</small>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Upload File Evidence (Multiple) <span class="text-danger">*</span></label>
								<input type="file" name="evidence_files[]" id="evidence_files" class="form-control" multiple <?= empty($existing_files) ? 'required' : '' ?>>
								<small class="text-muted">Bisa memilih lebih dari 1 file (PDF, JPG, PNG, dll)</small>
								
								<?php if(!empty($existing_files)): ?>
									<div style="margin-top:5px;">
										<b>File Terupload:</b><br>
										<?php foreach($existing_files as $f): ?>
											<a href="<?= base_url($f->file_path) ?>" target="_blank" class="btn btn-xs btn-default" style="margin-bottom:2px;">
												<i class="fa fa-paperclip"></i> <?= htmlspecialchars($f->file_name) ?>
											</a>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label>Catatan / Keterangan Umum</label>
								<textarea class="form-control" name="note" id="note" rows="2" placeholder="Masukkan keterangan tambahan jika ada..."><?= htmlspecialchars($note ?? '') ?></textarea>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="alert alert-info alert-dismissible" style="padding: 10px 15px; margin-bottom: 15px;">
				<i class="fa fa-info-circle"></i> <b>Petunjuk:</b> Pilih tab kategori di bawah. Masukkan <b>Harga Baru (Lower & Higher Price)</b> pada barang yang ingin diajukan perubahannya. Kolom USD akan terhitung otomatis berdasarkan nilai Kurs. Baris yang tidak diisi harga baru tidak akan diajukan.
			</div>

			<!-- Nav-Tabs Kategori -->
			<div class="nav-tabs-custom">
				<ul class="nav nav-tabs">
					<?php foreach($categories_items as $index => $cat_data): 
						$cat = $cat_data['category'];
						$items = $cat_data['items'];
						$is_active = ($index == 0) ? 'active' : '';
					?>
						<li class="<?= $is_active ?>">
							<a href="#tab_cat_<?= $cat->id ?>" data-toggle="tab">
								<b><?= strtoupper($cat->nm_category) ?></b> 
								<span class="badge bg-aqua" style="margin-left:4px;"><?= count($items) ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>

				<div class="tab-content" style="padding: 15px 0;">
					<?php foreach($categories_items as $index => $cat_data): 
						$cat = $cat_data['category'];
						$items = $cat_data['items'];
						$is_active = ($index == 0) ? 'active' : '';
					?>
						<div class="tab-pane <?= $is_active ?>" id="tab_cat_<?= $cat->id ?>">
							<div class="table-responsive">
								<table class="table table-bordered table-striped table-hover table-custom" width="100%">
									<thead>
										<tr class="bg-blue">
											<th rowspan="2" class="text-center" width="3%">#</th>
											<th rowspan="2" class="text-center" width="8%">Kode Stok</th>
											<th rowspan="2" class="text-center" width="16%">Nama Barang & Spesifikasi</th>
											<th rowspan="2" class="text-center" width="5%">Satuan</th>
											<th colspan="2" class="text-center bg-gray-active" width="16%">Harga Aktif (Before)</th>
											<th colspan="2" class="text-center" style="background:#205081; color:#fff;" width="22%">New Lower Price (After)</th>
											<th colspan="2" class="text-center" style="background:#1b446e; color:#fff;" width="22%">New Higher Price (After)</th>
											<th rowspan="2" class="text-center" width="8%">Expired</th>
										</tr>
										<tr class="bg-blue">
											<!-- Before -->
											<th class="text-center bg-gray">Lower (IDR)</th>
											<th class="text-center bg-gray">Higher (IDR)</th>
											<!-- New Lower -->
											<th class="text-center" style="background:#286090; color:#fff;">IDR</th>
											<th class="text-center" style="background:#286090; color:#fff;">USD</th>
											<!-- New Higher -->
											<th class="text-center" style="background:#204d74; color:#fff;">IDR</th>
											<th class="text-center" style="background:#204d74; color:#fff;">USD</th>
										</tr>
									</thead>
									<tbody>
										<?php if(empty($items)): ?>
											<tr>
												<td colspan="11" class="text-center text-muted">Belum ada barang di kategori ini.</td>
											</tr>
										<?php else: 
											$no = 0;
											foreach($items as $item): 
												$no++;
												$id_item = $item->id;
												$has_exist = isset($existing_details[$id_item]);
												$exist_d = $has_exist ? $existing_details[$id_item] : null;

												$val_price_new = $has_exist ? ($exist_d->price_ref_new > 0 ? number_format($exist_d->price_ref_new, 0) : '') : '';
												$val_price_new_usd = $has_exist ? ($exist_d->price_ref_new_usd > 0 ? number_format($exist_d->price_ref_new_usd, 4) : '') : '';
												$val_price_high_new = $has_exist ? ($exist_d->price_ref_high_new > 0 ? number_format($exist_d->price_ref_high_new, 0) : '') : '';
												$val_price_high_new_usd = $has_exist ? ($exist_d->price_ref_high_new_usd > 0 ? number_format($exist_d->price_ref_high_new_usd, 4) : '') : '';
												$val_exp = $has_exist ? $exist_d->expired : 1;
												$row_hl = $has_exist ? 'item-row-highlight' : '';
										?>
											<tr class="<?= $row_hl ?>" id="row_item_<?= $id_item ?>">
												<td class="text-center">
													<?= $no ?>
													<input type="hidden" name="items[<?= $id_item ?>][id_category]" value="<?= $cat->id ?>">
													<input type="hidden" name="items[<?= $id_item ?>][id_barang]" value="<?= $id_item ?>">
													<input type="hidden" name="items[<?= $id_item ?>][price_ref_before]" value="<?= $item->price_ref ?>">
													<input type="hidden" name="items[<?= $id_item ?>][price_ref_high_before]" value="<?= $item->price_ref_high ?>">
													<input type="hidden" name="items[<?= $id_item ?>][price_ref_usd_before]" value="<?= $item->price_ref_usd ?>">
													<input type="hidden" name="items[<?= $id_item ?>][price_ref_high_usd_before]" value="<?= $item->price_ref_high_usd ?>">
												</td>
												<td><b><?= strtoupper($item->id_stock ?? '-') ?></b></td>
												<td>
													<b><?= strtoupper($item->stock_name) ?></b>
													<?php if(!empty($item->spec)): ?>
														<br><small class="text-muted"><i class="fa fa-tag"></i> <?= $item->spec ?></small>
													<?php endif; ?>
												</td>
												<td class="text-center"><?= $item->nm_satuan ?? '-' ?></td>
												
												<!-- Before IDR -->
												<td class="text-right bg-gray text-bold"><?= number_format($item->price_ref, 0) ?></td>
												<td class="text-right bg-gray text-bold"><?= number_format($item->price_ref_high, 0) ?></td>

												<!-- New Lower -->
												<td>
													<input type="text" class="form-control input-sm text-right autoNumeric input_price_idr" 
														   id="price_new_idr_<?= $id_item ?>" 
														   name="items[<?= $id_item ?>][price_ref_new]" 
														   data-id="<?= $id_item ?>" 
														   data-target-usd="price_new_usd_<?= $id_item ?>"
														   placeholder="0" 
														   value="<?= $val_price_new ?>">
												</td>
												<td>
													<input type="text" class="form-control input-sm text-right autoNumeric4 input_price_usd" 
														   id="price_new_usd_<?= $id_item ?>" 
														   name="items[<?= $id_item ?>][price_ref_new_usd]" 
														   data-id="<?= $id_item ?>" 
														   data-target-idr="price_new_idr_<?= $id_item ?>"
														   placeholder="0.00" 
														   value="<?= $val_price_new_usd ?>">
												</td>

												<!-- New Higher -->
												<td>
													<input type="text" class="form-control input-sm text-right autoNumeric input_price_high_idr" 
														   id="price_high_idr_<?= $id_item ?>" 
														   name="items[<?= $id_item ?>][price_ref_high_new]" 
														   data-id="<?= $id_item ?>" 
														   data-target-usd="price_high_usd_<?= $id_item ?>"
														   placeholder="0" 
														   value="<?= $val_price_high_new ?>">
												</td>
												<td>
													<input type="text" class="form-control input-sm text-right autoNumeric4 input_price_high_usd" 
														   id="price_high_usd_<?= $id_item ?>" 
														   name="items[<?= $id_item ?>][price_ref_high_new_usd]" 
														   data-id="<?= $id_item ?>" 
														   data-target-idr="price_high_idr_<?= $id_item ?>"
														   placeholder="0.00" 
														   value="<?= $val_price_high_new_usd ?>">
												</td>

												<!-- Expired -->
												<td>
													<select class="form-control input-sm" name="items[<?= $id_item ?>][expired]">
														<option value="1" <?= ($val_exp == 1) ? 'selected' : '' ?>>1 Bulan</option>
														<option value="3" <?= ($val_exp == 3) ? 'selected' : '' ?>>3 Bulan</option>
														<option value="6" <?= ($val_exp == 6) ? 'selected' : '' ?>>Semester</option>
														<option value="12" <?= ($val_exp == 12) ? 'selected' : '' ?>>Tahunan</option>
													</select>
												</td>
											</tr>
										<?php endforeach; endif; ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

		<div class="box-footer">
			<button type="submit" class="btn btn-success btn-lg" id="btn-submit"><i class="fa fa-save"></i> Submit Pengajuan Harga</button>
			<a href="<?= base_url('price_sup_barang_stok') ?>" class="btn btn-default btn-lg"><i class="fa fa-times"></i> Batal</a>
		</div>
	</form>
</div>

<script src="<?= base_url('assets/plugins/select2/select2.full.min.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>

<script type="text/javascript">
	$(document).ready(function() {
		init_numeric();
	});

	function init_numeric() {
		$('.autoNumeric').autoNumeric('init', { mDec: '0', aPad: false });
		$('.autoNumeric4').autoNumeric('init', { mDec: '4', aPad: false });
	}

	function get_num(val) {
		if (!val) return 0;
		var clean = (val + '').replace(/,/g, '');
		var n = parseFloat(clean);
		return isNaN(n) ? 0 : n;
	}

	// Auto convert IDR -> USD on typing
	$(document).on('keyup change', '.input_price_idr, .input_price_high_idr', function() {
		var idr_val = get_num($(this).val());
		var kurs = get_num($('#kurs').val());
		var target_usd_id = $(this).data('target-usd');
		var id_item = $(this).data('id');

		if (kurs > 0 && idr_val > 0) {
			var usd_val = idr_val / kurs;
			$('#' + target_usd_id).autoNumeric('set', usd_val.toFixed(4));
			$('#row_item_' + id_item).addClass('item-row-highlight');
		} else if (idr_val === 0) {
			$('#' + target_usd_id).val('');
		}
	});

	// Auto convert USD -> IDR on typing
	$(document).on('keyup change', '.input_price_usd, .input_price_high_usd', function() {
		var usd_val = get_num($(this).val());
		var kurs = get_num($('#kurs').val());
		var target_idr_id = $(this).data('target-idr');
		var id_item = $(this).data('id');

		if (kurs > 0 && usd_val > 0) {
			var idr_val = Math.round(usd_val * kurs);
			$('#' + target_idr_id).autoNumeric('set', idr_val);
			$('#row_item_' + id_item).addClass('item-row-highlight');
		} else if (usd_val === 0) {
			$('#' + target_idr_id).val('');
		}
	});

	// Refresh Kurs from DB
	$(document).on('click', '#btn-refresh-kurs', function() {
		$.ajax({
			url: siteurl + 'price_sup_barang_stok/get_kurs',
			type: 'GET',
			dataType: 'json',
			success: function(res) {
				if (res.status == 1) {
					$('#kurs').autoNumeric('set', res.kurs);
					swal({
						title: "Kurs Diperbarui",
						text: "Kurs aktif saat ini: " + res.kurs,
						type: "success",
						timer: 1500,
						showConfirmButton: false
					});
				}
			}
		});
	});

	// Form Submit Handling
	$('#form_pengajuan').on('submit', function(e) {
		e.preventDefault();

		var formData = new FormData(this);

		swal({
			title: "Konfirmasi Pengajuan",
			text: "Apakah data harga supplier yang dimasukkan sudah benar?",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: "#00a65a",
			confirmButtonText: "Ya, Kirim Pengajuan!",
			cancelButtonText: "Batal",
			closeOnConfirm: false
		}, function(isConfirm) {
			if (isConfirm) {
				$('#btn-submit').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
				
				$.ajax({
					url: siteurl + 'price_sup_barang_stok/save_data',
					type: 'POST',
					data: formData,
					dataType: 'json',
					processData: false,
					contentType: false,
					success: function(res) {
						$('#btn-submit').prop('disabled', false).html('<i class="fa fa-save"></i> Submit Pengajuan Harga');
						if (res.status == 1) {
							swal({
								title: "Berhasil!",
								text: res.pesan,
								type: "success"
							}, function() {
								window.location.href = siteurl + 'price_sup_barang_stok';
							});
						} else {
							swal("Gagal!", res.pesan, "error");
						}
					},
					error: function() {
						$('#btn-submit').prop('disabled', false).html('<i class="fa fa-save"></i> Submit Pengajuan Harga');
						swal("Error!", "Terjadi kesalahan pada server saat memproses pengajuan.", "error");
					}
				});
			}
		});
	});
</script>
