<?php
$id             = (!empty($header)) ? $header[0]->id : '';
$id_stock       = (!empty($header)) ? $header[0]->id_stock : '';
$id_category    = (!empty($header)) ? $header[0]->id_category : '';
$stock_name     = (!empty($header)) ? $header[0]->stock_name : '';
$trade_name     = (!empty($header)) ? $header[0]->trade_name : '';
$brand          = (!empty($header)) ? $header[0]->brand : '';
$spec           = (!empty($header)) ? $header[0]->spec : '';
$id_unit_gudang = (!empty($header)) ? $header[0]->id_unit_gudang : '';
$konversi       = (!empty($header)) ? $header[0]->konversi : '';
$id_unit        = (!empty($header)) ? $header[0]->id_unit : '';
$min_stok       = (!empty($header)) ? $header[0]->min_stok : '';
$max_stok       = (!empty($header)) ? $header[0]->max_stok : '';
$min_order      = (!empty($header)) ? $header[0]->min_order : 0;
$coa            = (!empty($header)) ? $header[0]->no_coa : 0;
$is_view        = (!empty($results['tanda']) && $results['tanda'] == 'view');

$status_val = 1;
if (!empty($id)) {
	$status_val = isset($header[0]->status) ? $header[0]->status : 1;
}
?>

<style type="text/css">
	.form-card {
		background: #ffffff;
		border-radius: 8px;
		border: 1px solid #e2e8f0;
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
		margin-bottom: 25px;
		overflow: hidden;
	}
	.form-card-header {
		background: #f8fafc;
		padding: 16px 24px;
		border-bottom: 1px solid #edf2f7;
		display: flex;
		align-items: center;
		justify-content: space-between;
	}
	.form-card-header h3 {
		margin: 0;
		font-size: 16px;
		font-weight: 600;
		color: #1e293b;
		display: flex;
		align-items: center;
		gap: 8px;
	}
	.form-card-body {
		padding: 28px 30px;
	}
	
	/* Form Section Headers */
	.section-title {
		font-size: 14px;
		font-weight: 700;
		color: #334155;
		text-transform: uppercase;
		letter-spacing: 0.5px;
		margin-top: 0;
		margin-bottom: 18px;
		padding-bottom: 8px;
		border-bottom: 2px solid #f1f5f9;
		display: flex;
		align-items: center;
		gap: 8px;
	}
	.section-title i {
		color: #3b82f6;
		font-size: 15px;
	}
	.section-divider {
		margin: 28px 0 22px 0;
	}

	/* Form Field Controls */
	.form-group label {
		font-size: 13px;
		font-weight: 600;
		color: #475569;
		margin-bottom: 6px;
	}
	.form-control {
		border-radius: 6px;
		border: 1px solid #cbd5e1;
		box-shadow: none;
		height: 38px;
		font-size: 13px;
		color: #1e293b;
		transition: all 0.2s ease;
	}
	.form-control:focus {
		border-color: #3b82f6;
		box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
	}
	.select2-container .select2-choice {
		height: 38px;
		line-height: 36px;
		border-radius: 6px;
		border: 1px solid #cbd5e1;
	}
	.req-star {
		color: #ef4444;
		font-weight: bold;
	}

	/* Status Toggle Pill Options */
	.status-pills {
		display: inline-flex;
		background: #f1f5f9;
		padding: 4px;
		border-radius: 8px;
		gap: 4px;
	}
	.status-pill-label {
		cursor: pointer;
		margin-bottom: 0;
		font-weight: 500;
		font-size: 13px;
		padding: 6px 16px;
		border-radius: 6px;
		transition: all 0.2s ease;
		color: #64748b;
		display: inline-flex;
		align-items: center;
		gap: 6px;
	}
	.status-pill-label input[type="radio"] {
		display: none;
	}
	.status-pill-label.active-pill {
		background: #22c55e;
		color: #ffffff;
		box-shadow: 0 2px 4px rgba(34, 197, 94, 0.2);
	}
	.status-pill-label.inactive-pill {
		background: #ef4444;
		color: #ffffff;
		box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2);
	}

	/* Form Footer */
	.form-card-footer {
		background: #f8fafc;
		padding: 16px 28px;
		border-top: 1px solid #edf2f7;
		display: flex;
		align-items: center;
		justify-content: flex-end;
		gap: 10px;
	}
	.btn-form {
		padding: 8px 18px;
		border-radius: 6px;
		font-weight: 500;
		font-size: 13px;
		display: inline-flex;
		align-items: center;
		gap: 6px;
		transition: all 0.2s ease;
	}
	.btn-form:hover {
		transform: translateY(-1px);
	}
</style>

<div class="form-card">
	<div class="form-card-header">
		<h3>
			<i class="fa <?= (!empty($id) ? ($is_view ? 'fa-eye text-warning' : 'fa-pencil-square-o text-primary') : 'fa-plus-circle text-success') ?>"></i>
			<?= (!empty($id) ? ($is_view ? 'Detail Barang Stok' : 'Edit Barang Stok') : 'Tambah Barang Stok Baru') ?>
		</h3>
		<div>
			<?php if (!empty($id)): ?>
				<span class="badge bg-<?= ($status_val == '1' ? 'green' : 'red') ?>">
					<?= ($status_val == '1' ? 'STATUS: AKTIF' : 'STATUS: NON-AKTIF') ?>
				</span>
			<?php endif; ?>
		</div>
	</div>

	<form id="data-form" method="post" autocomplete="off">
		<input type="hidden" id="id" name="id" value='<?= $id; ?>'>

		<div class="form-card-body">
			<!-- Section 1: Informasi Utama -->
			<div class="section-title">
				<i class="fa fa-info-circle"></i> 1. Informasi Utama
			</div>
			
			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label for="id_category">Kategori Stok <span class="req-star">*</span></label>
						<select id="id_category" name="id_category" class="form-control chosen-select" required <?= ($is_view ? 'disabled' : '') ?>>
							<option value="0">- Pilih Kategori -</option>
							<?php foreach ($results['category'] as $kel) {
								$sel = ($kel->id == $id_category) ? 'selected' : '';
							?>
								<option value="<?= $kel->id; ?>" <?= $sel; ?>><?= strtoupper(strtolower($kel->nm_category)) ?></option>
							<?php } ?>
						</select>
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label for="stock_name">Nama Stok (Stock Name) <span class="req-star">*</span></label>
						<input type="text" id="stock_name" name="stock_name" class="form-control" required placeholder="Contoh: Lakban Bening 2 Inch" value='<?= htmlspecialchars($stock_name); ?>' <?= ($is_view ? 'readonly' : '') ?>>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label for="id_stock">Item Code / Kode Barang</label>
						<input type="text" id="id_stock" name="id_stock" class="form-control" placeholder="Contoh: ACC-001" value='<?= htmlspecialchars($id_stock); ?>' <?= ($is_view ? 'readonly' : '') ?>>
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label for="trade_name">Trade Name / Nama Dagang</label>
						<input type="text" id="trade_name" name="trade_name" class="form-control" placeholder="Nama dagang atau komersial" value='<?= htmlspecialchars($trade_name); ?>' <?= ($is_view ? 'readonly' : '') ?>>
					</div>
				</div>
			</div>

			<!-- Section 2: Spesifikasi & Brand -->
			<div class="section-divider"></div>
			<div class="section-title">
				<i class="fa fa-tags"></i> 2. Spesifikasi & Brand
			</div>

			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label for="brand">Brand / Merk</label>
						<input type="text" id="brand" name="brand" class="form-control" placeholder="Contoh: 3M, Daimaru, dll" value='<?= htmlspecialchars($brand); ?>' <?= ($is_view ? 'readonly' : '') ?>>
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label for="spec">Spesifikasi Detail</label>
						<input type="text" id="spec" name="spec" class="form-control" placeholder="Dimensi, ketebalan, material, dll" value='<?= htmlspecialchars($spec); ?>' <?= ($is_view ? 'readonly' : '') ?>>
					</div>
				</div>
			</div>

			<!-- Section 3: Satuan & Konversi -->
			<div class="section-divider"></div>
			<div class="section-title">
				<i class="fa fa-cubes"></i> 3. Satuan & Konversi
			</div>

			<div class="row">
				<div class="col-md-4">
					<div class="form-group">
						<label for="id_unit_gudang">Satuan Packing (Gudang)</label>
						<select id="id_unit_gudang" name="id_unit_gudang" class="form-control chosen-select" <?= ($is_view ? 'disabled' : '') ?>>
							<option value="0">- Pilih Satuan Packing -</option>
							<?php foreach ($results['satuan_packing'] as $satuan) {
								$sel = ($satuan->id == $id_unit_gudang) ? 'selected' : '';
							?>
								<option value="<?= $satuan->id; ?>" <?= $sel; ?>><?= strtoupper(strtolower($satuan->code)) ?></option>
							<?php } ?>
						</select>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
						<label for="konversi">Nilai Konversi</label>
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-calculator"></i></span>
							<input type="text" id="konversi" name="konversi" class="form-control autoNumeric text-right" placeholder="1" value='<?= $konversi; ?>' <?= ($is_view ? 'readonly' : '') ?>>
						</div>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
						<label for="id_unit">Unit Measurement (Satuan Terkecil)</label>
						<select id="id_unit" name="id_unit" class="form-control chosen-select" <?= ($is_view ? 'disabled' : '') ?>>
							<option value="0">- Pilih Satuan Penggunaan -</option>
							<?php foreach ($results['satuan'] as $satuan) {
								$sel = ($satuan->id == $id_unit) ? 'selected' : '';
							?>
								<option value="<?= $satuan->id; ?>" <?= $sel; ?>><?= strtoupper(strtolower($satuan->code)) ?></option>
							<?php } ?>
						</select>
					</div>
				</div>
			</div>

			<!-- Section 4: Pengaturan Akuntansi & Inventory -->
			<div class="section-divider"></div>
			<div class="section-title">
				<i class="fa fa-book"></i> 4. Akuntansi & Pengadaan
			</div>

			<div class="row">
				<div class="col-md-4">
					<div class="form-group">
						<label for="min_order">Minimum Order Qty</label>
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-shopping-cart"></i></span>
							<input type="text" name="min_order" id="min_order" class="form-control autoNumeric text-right" value="<?= $min_order; ?>" <?= ($is_view ? 'readonly' : '') ?>>
						</div>
					</div>
				</div>
				<div class="col-md-8">
					<div class="form-group">
						<label for="coa">Chart of Account (COA)</label>
						<select class="form-control chosen-select" name="coa" id="coa" <?= ($is_view ? 'disabled' : '') ?>>
							<option value="">- Pilih Akun COA -</option>
							<?php
							foreach ($list_coa as $item) {
								$selected = ($item->no_perkiraan == $coa) ? 'selected' : '';
								echo '<option value="' . $item->no_perkiraan . '" ' . $selected . '>' . $item->no_perkiraan . ' - ' . $item->nama . '</option>';
							}
							?>
						</select>
					</div>
				</div>
			</div>

			<?php if (!empty($id)) : ?>
				<div class="section-divider"></div>
				<div class="section-title">
					<i class="fa fa-toggle-on"></i> 5. Status Barang
				</div>
				<div class="form-group">
					<label style="display: block; margin-bottom: 8px;">Status Aktif Master:</label>
					<div class="status-pills">
						<label class="status-pill-label <?= ($status_val == '1' ? 'active-pill' : '') ?>">
							<input type="radio" name="status" value="1" <?= ($status_val == '1' ? 'checked' : '') ?> <?= ($is_view ? 'disabled' : '') ?>>
							<i class="fa fa-check-circle"></i> Aktif
						</label>
						<label class="status-pill-label <?= ($status_val == '0' ? 'inactive-pill' : '') ?>">
							<input type="radio" name="status" value="0" <?= ($status_val == '0' ? 'checked' : '') ?> <?= ($is_view ? 'disabled' : '') ?>>
							<i class="fa fa-times-circle"></i> Non-Aktif
						</label>
					</div>
				</div>
			<?php endif; ?>
		</div>

		<!-- Footer Actions -->
		<div class="form-card-footer">
			<button type="button" class="btn btn-default btn-form" name="back" id="back">
				<i class="fa fa-arrow-left"></i> Kembali
			</button>
			<?php if (!$is_view): ?>
				<button type="submit" class="btn btn-primary btn-form" name="save" id="save">
					<i class="fa fa-save"></i> Simpan Data
				</button>
			<?php endif; ?>
		</div>
	</form>
</div>

<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script type="text/javascript">
	var base_url = '<?php echo base_url(); ?>';
	var active_controller = '<?php echo ($this->uri->segment(1)); ?>';

	$(document).ready(function() {
		$('.chosen-select').select2({ width: '100%' });
		$('.autoNumeric').autoNumeric();

		// Interactive Status Pill Toggle
		$('input[name="status"]').on('change', function() {
			$('.status-pill-label').removeClass('active-pill inactive-pill');
			if ($(this).val() == '1') {
				$(this).closest('.status-pill-label').addClass('active-pill');
			} else {
				$(this).closest('.status-pill-label').addClass('inactive-pill');
			}
		});

		$(document).on('click', '#back', function() {
			window.location.href = base_url + active_controller;
		});

		$(document).on('click', '#save', function(e) {
			e.preventDefault();

			var id_category = $('#id_category').val();
			var stock_name = $.trim($('#stock_name').val());

			if (id_category == '0' || id_category == '') {
				swal({
					title: "Perhatian",
					text: "Silakan pilih Kategori Stok terlebih dahulu!",
					type: "warning"
				});
				return false;
			}
			if (stock_name == '') {
				swal({
					title: "Perhatian",
					text: "Nama Stok wajib diisi!",
					type: "warning"
				});
				$('#stock_name').focus();
				return false;
			}

			swal({
					title: "Konfirmasi Simpan",
					text: "Apakah data barang stok yang diinput sudah sesuai?",
					type: "warning",
					showCancelButton: true,
					confirmButtonClass: "btn-primary",
					confirmButtonText: "Ya, Simpan!",
					cancelButtonText: "Batal",
					closeOnConfirm: false
				},
				function(isConfirm) {
					if (isConfirm) {
						var formData = $('#data-form').serialize();
						var baseurl = siteurl + active_controller + '/add';
						$.ajax({
							url: baseurl,
							type: "POST",
							data: formData,
							cache: false,
							dataType: 'json',
							success: function(data) {
								if (data.status == 1) {
									swal({
										title: "Sukses!",
										text: data.pesan,
										type: "success",
										timer: 2000
									});
									setTimeout(function() {
										window.location.href = base_url + active_controller;
									}, 1500);
								} else {
									swal({
										title: "Gagal Simpan",
										text: data.pesan,
										type: "warning"
									});
								}
							},
							error: function() {
								swal({
									title: "Error!",
									text: "Terjadi kesalahan saat memproses data ke server.",
									type: "error"
								});
							}
						});
					}
				}
			);
		});
	});
</script>