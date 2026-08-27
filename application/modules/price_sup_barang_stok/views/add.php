<?php
	$is_edit = ($type == 'edit');
	$nm_cat  = !empty($category) ? strtoupper($category->nm_category) : 'Kategori';
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/select2/select2.min.css')?>">
<style>
	.table-custom th, .table-custom td {
		vertical-align: middle !important;
		padding: 6px 8px !important;
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
		<h3 class="box-title"><i class="fa <?= $is_edit ? 'fa-edit' : 'fa-plus' ?>"></i> <?= $is_edit ? 'Edit' : 'Form Input' ?> Pengajuan Price Supplier &mdash; <b><?= $nm_cat ?></b></h3>
		<div class="box-tools pull-right">
			<a href="<?= base_url('price_sup_barang_stok') ?>" class="btn btn-sm btn-default"><i class="fa fa-arrow-left"></i> Kembali</a>
		</div>
	</div>
	
	<form id="form_pengajuan" method="post" autocomplete="off" enctype="multipart/form-data">
		<input type="hidden" name="type" value="<?= $type ?>">
		<input type="hidden" name="no_doc" id="no_doc" value="<?= $no_doc ?>">
		<input type="hidden" name="id_category" id="id_category" value="<?= $id_category ?>">
		<input type="hidden" name="kurs" id="kurs" value="1">

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
								<label>Kategori Barang Stok</label>
								<input type="text" class="form-control" value="<?= $nm_cat ?>" readonly style="font-weight:bold; background:#f9f9f9; color:#0073b7;">
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
								<label>Evidence Files <?= empty($existing_files) ? '<span class="text-danger">*</span>' : '' ?></label>
								<div style="display:flex; align-items:center; gap:8px;">
									<label class="btn btn-sm btn-primary btn-flat" style="margin-bottom:0; cursor:pointer; font-weight:600;">
										<i class="fa fa-folder-open"></i> Pilih File Evidence
										<input type="file" id="temp_evidence_picker" style="display:none;" accept=".jpg,.jpeg,.png,.pdf,.xls,.xlsx,.doc,.docx" multiple>
									</label>
									<span id="selected_evidence_count" class="text-muted" style="font-size:11px;">Belum ada file dipilih</span>
								</div>

								<!-- Hidden actual input holding accumulated files -->
								<input type="file" name="evidence_files[]" id="evidence_files" multiple style="display:none;" <?= empty($existing_files) ? 'required' : '' ?>>

								<!-- List of newly selected files -->
								<div id="new_evidence_list" style="display:flex; flex-wrap:wrap; gap:5px; margin-top:6px;"></div>
								<small class="text-muted" style="font-size:11px; display:block; margin-top:3px;">Format: JPG, PNG, PDF, XLS/XLSX, DOC/DOCX. Bisa klik pilih file berkali-kali dari folder berbeda.</small>

								<?php if(!empty($existing_files)): ?>
									<div style="margin-top:6px;">
										<button type="button" class="btn btn-xs btn-info btn-view-evidence" data-no_doc="<?= $no_doc ?>" title="Lihat Evidence Files">
											<i class="fa fa-paperclip"></i> <b><?= count($existing_files) ?> File Terlampir Sebelumnya</b> (Lihat)
										</button>
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
				<i class="fa fa-info-circle"></i> <b>Petunjuk:</b> Masukkan <b>Harga Baru (Lower Price & Higher Price dalam IDR)</b> pada barang di bawah yang ingin diajukan perubahannya. Baris yang tidak diisi harga baru tidak akan diajukan.
			</div>

			<!-- Table List Barang Khusus Kategori Terpilih -->
			<div class="panel panel-primary">
				<div class="panel-heading" style="font-weight:bold;">
					<i class="fa fa-tags"></i> DAFTAR BARANG STOK: <?= $nm_cat ?> (<?= count($items) ?> item)
				</div>
				<div class="panel-body" style="padding: 0;">
					<div class="table-responsive">
						<table class="table table-bordered table-striped table-hover table-custom" width="100%" style="margin:0;">
							<thead>
								<tr class="bg-blue">
									<th rowspan="2" class="text-center" width="3%">#</th>
									<th rowspan="2" class="text-center" width="10%">Kode Stok</th>
									<th rowspan="2" class="text-center" width="23%">Nama Barang & Spesifikasi</th>
									<th rowspan="2" class="text-center" width="6%">Satuan</th>
									<th colspan="2" class="text-center bg-gray-active" width="22%">Harga Aktif (Before)</th>
									<th colspan="2" class="text-center" style="background:#205081; color:#fff;" width="26%">Harga Baru Diajukan (After)</th>
									<th rowspan="2" class="text-center" width="10%">Expired</th>
								</tr>
								<tr class="bg-blue">
									<!-- Before -->
									<th class="text-center bg-gray" width="11%">Lower (IDR)</th>
									<th class="text-center bg-gray" width="11%">Higher (IDR)</th>
									<!-- New After -->
									<th class="text-center" style="background:#286090; color:#fff;" width="13%">New Lower (IDR)</th>
									<th class="text-center" style="background:#204d74; color:#fff;" width="13%">New Higher (IDR)</th>
								</tr>
							</thead>
							<tbody>
								<?php if(empty($items)): ?>
									<tr>
										<td colspan="9" class="text-center text-muted">Belum ada master barang pada kategori ini.</td>
									</tr>
								<?php else: 
									$no = 0;
									foreach($items as $item): 
										$no++;
										$id_item = $item->id;
										$has_exist = isset($existing_details[$id_item]);
										$exist_d = $has_exist ? $existing_details[$id_item] : null;

										$val_price_new = $has_exist ? ($exist_d->price_ref_new > 0 ? number_format($exist_d->price_ref_new, 0) : '') : '';
										$val_price_high_new = $has_exist ? ($exist_d->price_ref_high_new > 0 ? number_format($exist_d->price_ref_high_new, 0) : '') : '';
										$val_exp = $has_exist ? $exist_d->expired : 1;
										$row_hl = $has_exist ? 'item-row-highlight' : '';
								?>
									<tr class="<?= $row_hl ?>" id="row_item_<?= $id_item ?>">
										<td class="text-center">
											<?= $no ?>
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

										<!-- New Lower IDR -->
										<td>
											<input type="text" class="form-control input-sm text-right autoNumeric input_price_idr" 
												   id="price_new_idr_<?= $id_item ?>" 
												   name="items[<?= $id_item ?>][price_ref_new]" 
												   data-id="<?= $id_item ?>" 
												   placeholder="0" 
												   value="<?= $val_price_new ?>">
										</td>

										<!-- New Higher IDR -->
										<td>
											<input type="text" class="form-control input-sm text-right autoNumeric input_price_high_idr" 
												   id="price_high_idr_<?= $id_item ?>" 
												   name="items[<?= $id_item ?>][price_ref_high_new]" 
												   data-id="<?= $id_item ?>" 
												   placeholder="0" 
												   value="<?= $val_price_high_new ?>">
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
	}

	function get_num(val) {
		if (!val) return 0;
		var clean = (val + '').replace(/,/g, '');
		var n = parseFloat(clean);
		return isNaN(n) ? 0 : n;
	}

	// Highlight row on typing
	$(document).on('keyup change', '.input_price_idr, .input_price_high_idr', function() {
		var idr_val = get_num($(this).val());
		var id_item = $(this).data('id');

		if (idr_val > 0) {
			$('#row_item_' + id_item).addClass('item-row-highlight');
		}
	});

	// Evidence File Accumulator
	var dtEvidence = new DataTransfer();

	$(document).on('change', '#temp_evidence_picker', function() {
		var newFiles = this.files;
		if (newFiles.length > 0) {
			for (var i = 0; i < newFiles.length; i++) {
				var file = newFiles[i];
				var exists = false;
				for (var j = 0; j < dtEvidence.items.length; j++) {
					var existingFile = dtEvidence.items[j].getAsFile();
					if (existingFile && existingFile.name === file.name && existingFile.size === file.size) {
						exists = true;
						break;
					}
				}
				if (!exists) {
					dtEvidence.items.add(file);
				}
			}
			var hiddenInput = document.getElementById('evidence_files');
			if (hiddenInput) {
				hiddenInput.files = dtEvidence.files;
			}
			$(this).val('');
			render_new_evidence_list();
		}
	});

	function render_new_evidence_list() {
		var container = $('#new_evidence_list');
		container.empty();
		var count = dtEvidence.files.length;
		if (count > 0) {
			$('#selected_evidence_count').text(count + ' file baru dipilih:');
			for (var i = 0; i < count; i++) {
				var file = dtEvidence.files[i];
				var sizeKb = Math.round(file.size / 1024);
				var badgeHtml = '<span class="badge" style="background:#00a65a; padding:5px 8px; font-size:11px; font-weight:normal; border-radius:3px; display:inline-flex; align-items:center; gap:5px;">' +
					'<i class="fa fa-file-o"></i> ' + file.name + ' (' + sizeKb + ' KB) ' +
					'<button type="button" class="btn btn-xs btn-danger remove-new-evidence" data-index="' + i + '" style="padding:0 4px; line-height:1; font-size:10px; border-radius:2px; margin-left:3px;" title="Hapus file ini">' +
					'<i class="fa fa-times"></i>' +
					'</button>' +
					'</span>';
				container.append(badgeHtml);
			}
		} else {
			$('#selected_evidence_count').text('Belum ada file dipilih');
		}
	}

	$(document).on('click', '.remove-new-evidence', function(e) {
		e.preventDefault();
		var idx = parseInt($(this).data('index'));
		var newDt = new DataTransfer();
		for (var i = 0; i < dtEvidence.files.length; i++) {
			if (i !== idx) {
				newDt.items.add(dtEvidence.files[i]);
			}
		}
		dtEvidence = newDt;
		var hiddenInput = document.getElementById('evidence_files');
		if (hiddenInput) {
			hiddenInput.files = dtEvidence.files;
		}
		render_new_evidence_list();
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

<!-- Modal Evidence Files -->
<div class="modal fade" id="modal-evidence" role="dialog" aria-labelledby="modalEvidenceLabel" aria-hidden="true" style="z-index: 1060;">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header bg-blue">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title"><i class="fa fa-paperclip"></i> Daftar File Evidence Terlampir</h4>
      </div>
      <div class="modal-body" id="modal-evidence-body">
		<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Memuat file...</div>
      </div>
	  <div class="modal-footer">
		<button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-close"></i> Tutup</button>
	  </div>
    </div>
  </div>
</div>

<script type="text/javascript">
	$(document).on('click', '.btn-view-evidence', function() {
		var no_doc = $(this).data('no_doc');
		$('#modal-evidence-body').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Memuat data file...</div>');
		$('#modal-evidence').modal('show');
		$.ajax({
			url: siteurl + 'price_sup_barang_stok/get_evidence_modal/' + encodeURIComponent(no_doc),
			type: 'GET',
			success: function(html) {
				$('#modal-evidence-body').html(html);
			},
			error: function() {
				$('#modal-evidence-body').html('<div class="alert alert-danger">Gagal memuat daftar file.</div>');
			}
		});
	});
</script>



