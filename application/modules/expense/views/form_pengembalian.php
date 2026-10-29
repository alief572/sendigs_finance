<?php
$no_doc_val   = isset($data->no_doc) ? $data->no_doc : '';
$no_kasbon    = isset($data->id_kasbon) ? $data->id_kasbon : '-';
$tgl_approval = !empty($data->tgl_approval_direktur) ? $data->tgl_approval_direktur : (!empty($data->created_date) ? $data->created_date : '-');
$keperluan    = !empty($kasbon->keperluan) ? $kasbon->keperluan : (!empty($data->informasi) ? $data->informasi : 'Perjalanan Dinas / Operasional');
$tot_realisasi = isset($data->jumlah) ? floatval($data->jumlah) : 0;
$harus_kembali = isset($data->lebih_bayar) ? floatval($data->lebih_bayar) : 0;
$bank_options = isset($bank_options) && is_array($bank_options) ? $bank_options : [];
$default_tanggal = date('Y-m-d');
$default_tanggal_display = date('d/m/Y');
$is_readonly = isset($is_readonly) ? (bool)$is_readonly : in_array((string)$data->status, ['2', '4']);
$existing_files = !empty($data->bukti_pengembalian) ? explode(';', $data->bukti_pengembalian) : [];
?>

<style>
	.form-pengembalian-wrapper {
		margin-bottom: 30px;
		font-family: "Source Sans Pro", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
	}

	.form-pengembalian-header {
		background: #ffffff;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		padding: 14px 20px;
		margin-bottom: 20px;
		display: flex;
		align-items: center;
		justify-content: space-between;
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
	}

	.form-pengembalian-title {
		font-size: 18px;
		font-weight: 700;
		color: #2d3748;
		margin: 0;
		display: flex;
		align-items: center;
		gap: 12px;
	}

	.btn-back-header {
		background: #f1f5f9;
		color: #475569;
		border: 1px solid #cbd5e1;
		padding: 6px 12px;
		border-radius: 6px;
		cursor: pointer;
		font-size: 13px;
		font-weight: 600;
		transition: all 0.15s ease;
		text-decoration: none;
		display: inline-flex;
		align-items: center;
		gap: 6px;
	}
	.btn-back-header:hover {
		background: #e2e8f0;
		color: #1e293b;
		border-color: #94a3b8;
	}

	.card-sendigs {
		background: #ffffff;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		margin-bottom: 20px;
		overflow: hidden;
		box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
	}

	.card-sendigs-header {
		padding: 13px 20px;
		background: #f8fafc;
		border-bottom: 1px solid #edf2f7;
		font-size: 14px;
		font-weight: 700;
		color: #2d3748;
		display: flex;
		align-items: center;
		justify-content: space-between;
	}

	.card-sendigs-header i {
		margin-right: 8px;
		color: #3182ce;
	}

	.card-sendigs-body {
		padding: 20px;
	}

	.field-row {
		display: flex;
		align-items: center;
		margin-bottom: 12px;
	}
	.field-row:last-child {
		margin-bottom: 0;
	}

	.field-label {
		width: 135px;
		font-size: 13px;
		font-weight: 600;
		color: #64748b;
		flex-shrink: 0;
	}

	.field-value {
		flex: 1;
		font-size: 13px;
		color: #2d3748;
	}

	.sendigs-input-static {
		background: #f8fafc;
		border: 1px solid #e2e8f0;
		border-radius: 6px;
		padding: 7px 12px;
		color: #1e293b;
		font-size: 13px;
		display: inline-block;
		width: 100%;
	}

	.sendigs-form-control {
		width: 100%;
		background: #ffffff;
		border: 1px solid #cbd5e1;
		border-radius: 6px;
		padding: 8px 12px;
		color: #1e293b;
		font-size: 13px;
		outline: none;
		transition: border-color 0.15s ease, box-shadow 0.15s ease;
	}
	.sendigs-form-control:focus {
		border-color: #3182ce;
		box-shadow: 0 0 0 2px rgba(49, 130, 206, 0.15);
	}

	.badge-ticket-kasbon {
		background: #3182ce;
		color: #ffffff;
		padding: 4px 8px;
		border-radius: 4px;
		font-size: 11px;
		font-weight: 600;
		display: inline-flex;
		align-items: center;
		gap: 4px;
	}

	.badge-status-pengembalian {
		background: #fffbeb;
		color: #b45309;
		border: 1px solid #fde68a;
		font-weight: 700;
		padding: 5px 12px;
		border-radius: 12px;
		font-size: 11px;
		display: inline-block;
	}

	.badge-draft-jurnal {
		background: #e2e8f0;
		color: #475569;
		font-size: 11px;
		padding: 4px 10px;
		border-radius: 12px;
		font-weight: 600;
	}

	.btn-upload-picker {
		background: #3182ce;
		color: #ffffff;
		border: 1px solid #2b6cb0;
		border-radius: 6px;
		padding: 8px 16px;
		font-size: 13px;
		font-weight: 600;
		cursor: pointer;
		display: inline-flex;
		align-items: center;
		gap: 8px;
		transition: all 0.15s ease;
	}
	.btn-upload-picker:hover {
		background: #2b6cb0;
		color: #ffffff;
	}

	/* File Chip list for multi-file accumulator */
	.file-chip-container {
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
		margin-top: 10px;
	}
	.file-chip {
		background: #f1f5f9;
		border: 1px solid #cbd5e1;
		color: #1e293b;
		padding: 5px 12px;
		border-radius: 6px;
		font-size: 12px;
		display: inline-flex;
		align-items: center;
		gap: 8px;
	}
	.file-chip i.file-icon {
		color: #3182ce;
	}
	.file-chip .file-size {
		color: #64748b;
		font-size: 11px;
	}
	.file-chip .btn-remove-chip {
		color: #ef4444;
		cursor: pointer;
		font-weight: bold;
		padding: 0 4px;
		transition: color 0.15s ease;
	}
	.file-chip .btn-remove-chip:hover {
		color: #b91c1c;
	}

	/* Table Preview Jurnal */
	.table-sendigs-preview {
		width: 100%;
		border-collapse: collapse;
		margin-bottom: 0;
	}
	.table-sendigs-preview thead th {
		background-color: #2b6cb0;
		color: #ffffff;
		border: 1px solid #2b6cb0;
		padding: 10px 14px;
		font-size: 12px;
		font-weight: 600;
		text-align: left;
		vertical-align: middle;
	}
	.table-sendigs-preview tbody td {
		padding: 10px 14px;
		border: 1px solid #e2e8f0;
		color: #2d3748;
		font-size: 13px;
		vertical-align: middle;
		background: #ffffff;
	}
	.table-sendigs-preview tfoot td {
		padding: 10px 14px;
		background: #f8fafc;
		border-top: 2px solid #cbd5e1;
		border-bottom: 1px solid #e2e8f0;
		color: #1e293b;
		font-size: 13px;
		font-weight: 700;
	}

	.mockup-note-box {
		background: #fffdf0;
		border: 1px solid #fef08a;
		border-left: 4px solid #f59e0b;
		border-radius: 6px;
		padding: 12px 16px;
		font-size: 12px;
		color: #854d0e;
		line-height: 1.5;
		margin-top: 14px;
	}
	.mockup-note-box b {
		color: #b45309;
	}

	.action-bar-footer {
		display: flex;
		justify-content: flex-end;
		align-items: center;
		gap: 12px;
		margin-top: 24px;
	}

	.btn-submit-kirim {
		background: #3182ce;
		border: 1px solid #2b6cb0;
		color: #ffffff;
		padding: 9px 22px;
		border-radius: 6px;
		font-size: 13px;
		font-weight: 600;
		cursor: pointer;
		display: inline-flex;
		align-items: center;
		gap: 8px;
		transition: all 0.15s ease;
	}
	.btn-submit-kirim:hover {
		background: #2b6cb0;
		color: #ffffff;
	}

	.btn-cancel-form {
		background: #ffffff;
		border: 1px solid #cbd5e1;
		color: #475569;
		padding: 9px 18px;
		border-radius: 6px;
		font-size: 13px;
		font-weight: 600;
		cursor: pointer;
		display: inline-flex;
		align-items: center;
		gap: 6px;
		transition: all 0.15s ease;
	}
	.btn-cancel-form:hover {
		background: #f1f5f9;
		color: #1e293b;
		border-color: #94a3b8;
	}
</style>

<div class="form-pengembalian-wrapper">
	<!-- Top Navigation Header -->
	<div class="form-pengembalian-header">
		<h3 class="form-pengembalian-title">
			<a href="javascript:void(0);" onclick="close_form_pengembalian()" class="btn-back-header" title="Kembali ke Daftar Pengembalian">
				<i class="fa fa-arrow-left"></i>
			</a>
			<span>Pengembalian Kasbon <?= htmlspecialchars($no_doc_val) ?></span>
		</h3>
		<div>
			<button type="button" class="btn btn-default btn-sm" onclick="close_form_pengembalian()" style="border-radius:6px;">
				<i class="fa fa-reply"></i> Kembali ke Daftar
			</button>
		</div>
	</div>

	<!-- Form Pengembalian -->
	<form id="formKirimPengembalian" enctype="multipart/form-data">
		<input type="hidden" name="id_expense" value="<?= htmlspecialchars($data->id) ?>">
		<input type="hidden" name="transfer_coa_bank" id="transfer_coa_bank_val" value="">

		<!-- PANEL 1: Informasi Laporan -->
		<div class="card-sendigs">
			<div class="card-sendigs-header">
				<span><i class="fa fa-info-circle"></i> Informasi Laporan</span>
			</div>
			<div class="card-sendigs-body">
				<div class="row">
					<div class="col-md-6">
						<div class="field-row">
							<div class="field-label">No. Dokumen</div>
							<div class="field-value">
								<span class="sendigs-input-static"><?= htmlspecialchars($no_doc_val) ?></span>
							</div>
						</div>
						<div class="field-row">
							<div class="field-label">No. Kasbon</div>
							<div class="field-value" style="display:flex; align-items:center; gap:8px;">
								<span class="badge-ticket-kasbon">
									<i class="fa fa-ticket"></i> <?= htmlspecialchars($no_kasbon) ?>
								</span>
								<span style="color:#475569; font-size:12px;">Keperluan: <?= htmlspecialchars($keperluan) ?></span>
							</div>
						</div>
						<div class="field-row">
							<div class="field-label">Tgl Approval</div>
							<div class="field-value">
								<span class="sendigs-input-static"><?= htmlspecialchars($tgl_approval) ?></span>
							</div>
						</div>
					</div>

					<div class="col-md-6">
						<div class="field-row">
							<div class="field-label">Total Realisasi</div>
							<div class="field-value text-right">
								<span class="sendigs-input-static text-right" style="font-weight:600;">
									<?= number_format($tot_realisasi) ?>
								</span>
							</div>
						</div>
						<div class="field-row">
							<div class="field-label">Harus Dikembalikan</div>
							<div class="field-value text-right">
								<span class="sendigs-input-static text-right" style="font-weight:700; color:#16a34a; font-size:14px; background:#f0fdf4; border-color:#bbf7d0;">
									<?= number_format($harus_kembali) ?>
								</span>
							</div>
						</div>
						<div class="field-row">
							<div class="field-label">Status</div>
							<div class="field-value text-right">
								<span class="badge-status-pengembalian">
									Menunggu Pengembalian
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- PANEL 2: Form Pengembalian Kelebihan Kasbon -->
		<div class="card-sendigs">
			<div class="card-sendigs-header">
				<span><i class="fa fa-reply"></i> Pengembalian Kelebihan Kasbon (Transfer ke Rekening Perusahaan)</span>
			</div>
			<div class="card-sendigs-body">
				<div class="form-group" style="margin-bottom:16px;">
					<label style="color:#2d3748; font-size:13px; font-weight:600; margin-bottom:6px; display:block;">
						Bank Pengembalian (Tujuan Transfer) <span style="color:#e53e3e;">*</span>
					</label>
					<select name="id_bank_pengembalian" id="id_bank_pengembalian" class="sendigs-form-control" <?= ($is_readonly ? 'disabled' : 'required') ?>>
						<option value="">- Pilih Bank -</option>
						<?php foreach ($bank_options as $bk): ?>
							<?php
								$bank_title = !empty($bk->nama_bank) ? $bk->nama_bank : 'Bank';
								$bank_company = 'STM';
								if (stripos($bk->nama, 'VUCA') !== false) {
									$bank_company = 'VUCA';
								} else if (stripos($bk->nama, 'SUSTAIN') !== false) {
									$bank_company = 'SUSTAIN';
								}
								$bank_label = $bank_title . ' - ' . $bk->rekening . ' (a.n ' . $bk->nama . ')';
								$is_selected = false;
								if (!empty($data->id_bank_pengembalian) && $data->id_bank_pengembalian == $bk->id) {
									$is_selected = true;
								} else if (!empty($data->transfer_coa_bank) && $data->transfer_coa_bank == $bk->coa_bank) {
									$is_selected = true;
								}
							?>
							<option value="<?= htmlspecialchars($bk->id) ?>" 
									data-coa="<?= htmlspecialchars($bk->coa_bank) ?>" 
									data-bank-name="<?= htmlspecialchars($bank_title) ?>"
									data-company="<?= htmlspecialchars($bank_company) ?>"
									data-rekening="<?= htmlspecialchars($bk->rekening) ?>"
									<?= ($is_selected ? 'selected' : '') ?>>
								<?= htmlspecialchars($bank_label) ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="form-group" style="margin-bottom:16px;">
					<label style="color:#2d3748; font-size:13px; font-weight:600; margin-bottom:6px; display:block;">
						Tanggal Transfer <span style="color:#e53e3e;">*</span>
					</label>
					<div class="input-group" style="width:100%;">
						<input type="text" name="transfer_tanggal" id="transfer_tanggal_input" class="sendigs-form-control" value="<?= (!empty($data->transfer_tanggal) ? htmlspecialchars($data->transfer_tanggal) : date('Y-m-d')) ?>" <?= ($is_readonly ? 'readonly' : 'required') ?> autocomplete="off">
					</div>
				</div>

				<div class="form-group" style="margin-bottom:18px;">
					<label style="color:#2d3748; font-size:13px; font-weight:600; margin-bottom:6px; display:block;">
						Nominal Transfer
					</label>
					<input type="text" name="transfer_jumlah" id="transfer_jumlah_input" class="sendigs-form-control text-right" value="<?= number_format(!empty($data->transfer_jumlah) ? floatval($data->transfer_jumlah) : $harus_kembali) ?>" <?= ($is_readonly ? 'readonly' : 'required') ?> style="font-weight:700; color:#2b6cb0;">
					<small style="display:block; color:#64748b; font-size:12px; margin-top:4px;">
						<i class="fa fa-info-circle"></i> Harus sama dengan jumlah yang harus dikembalikan.
					</small>
				</div>

				<div class="form-group" style="margin-bottom:6px;">
					<label style="color:#2d3748; font-size:13px; font-weight:600; margin-bottom:6px; display:block;">
						Upload Bukti Transfer Balik <span style="color:#e53e3e;">*</span>
					</label>
					<?php if (!$is_readonly): ?>
						<input type="file" id="multi_file_input" style="display:none;" accept=".jpg,.jpeg,.png,.pdf" multiple>
						<button type="button" class="btn-upload-picker" onclick="$('#multi_file_input').trigger('click')">
							<i class="fa fa-folder-open"></i> Pilih Bukti Transfer
						</button>
						<small style="display:block; color:#64748b; font-size:12px; margin-top:6px;">
							<i class="fa fa-info-circle"></i> Format: JPG, PNG, PDF. Bisa klik <b>Pilih Bukti Transfer</b> berkali-kali untuk memilih file dari berbagai folder satu per satu.
						</small>
					<?php endif; ?>

					<!-- File list chip container -->
					<div id="file_chips_wrapper" class="file-chip-container">
						<?php if (!empty($existing_files)): ?>
							<?php foreach ($existing_files as $ef): ?>
								<?php if (trim($ef) !== ''): ?>
									<div class="file-chip">
										<i class="fa fa-file-text-o file-icon"></i>
										<a href="<?= base_url('assets/expense_bukti_pengembalian/' . trim($ef)) ?>" target="_blank" style="color:#1e293b; text-decoration:underline;">
											<?= htmlspecialchars(trim($ef)) ?>
										</a>
									</div>
								<?php endif; ?>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

		<!-- PANEL 3: Daftar Jurnal Pengembalian Kasbon (Draft Preview) -->
		<div class="card-sendigs">
			<div class="card-sendigs-header">
				<span><i class="fa fa-table"></i> Daftar Jurnal Pengembalian Kasbon</span>
				<?php if ((string)$data->status === '2'): ?>
					<span class="badge" style="background:#00a65a; color:#fff; font-size:11px; padding:3px 8px;">Posted: Terverifikasi oleh Finance</span>
				<?php elseif ((string)$data->status === '4'): ?>
					<span class="badge-draft-jurnal">Draft: Menunggu Verifikasi Finance</span>
				<?php else: ?>
					<span class="badge-draft-jurnal">Draft: jurnal diposting setelah verifikasi Finance</span>
				<?php endif; ?>
			</div>
			<div class="card-sendigs-body" style="padding:0;">
				<div class="table-responsive">
					<table class="table-sendigs-preview">
						<thead>
							<tr>
								<th width="140">Tanggal Jurnal</th>
								<th width="110">COA</th>
								<th width="130">Nama Company</th>
								<th width="160">Nama Account</th>
								<th>Deskripsi / Keterangan</th>
								<th width="130" class="text-right">Debit (Rp)</th>
								<th width="130" class="text-right">Kredit (Rp)</th>
							</tr>
						</thead>
						<tbody>
							<!-- Baris 1: Sisi Debit Bank -->
							<tr style="background: #f0fdf4;">
								<td id="pv_tgl_1" style="color:#64748b;"><?= date('d F Y') ?></td>
								<td id="pv_coa_1" style="font-weight:600; color:#2b6cb0;">—</td>
								<td id="pv_comp_1">STM</td>
								<td id="pv_acc_1" style="color:#64748b; font-style:italic;">(pilih bank)</td>
								<td style="color:#15803d; font-weight:500;">
									<i class="fa fa-reply" style="font-size:11px;"></i> 
									Pengembalian Kelebihan Kasbon <?= htmlspecialchars($no_kasbon) ?> (Transfer ke Rekening Perusahaan)
								</td>
								<td id="pv_debit_1" class="text-right" style="font-weight:700; color:#15803d;"><?= number_format($harus_kembali) ?></td>
								<td class="text-right" style="color:#94a3b8;">0</td>
							</tr>
							<!-- Baris 2: Sisi Kredit Piutang Kelebihan Kasbon -->
							<tr>
								<td id="pv_tgl_2" style="color:#64748b;"><?= date('d F Y') ?></td>
								<td style="font-weight:600; color:#2b6cb0;">1103-01-15</td>
								<td id="pv_comp_2">STM</td>
								<td style="font-weight:500;">Piutang Kelebihan Kasbon</td>
								<td style="color:#475569;">
									Pelunasan Piutang Kelebihan Kasbon (<?= htmlspecialchars($no_kasbon) ?>)
								</td>
								<td class="text-right" style="color:#94a3b8;">0</td>
								<td id="pv_kredit_2" class="text-right" style="font-weight:700; color:#1e293b;"><?= number_format($harus_kembali) ?></td>
							</tr>
						</tbody>
						<tfoot>
							<tr>
								<td colspan="5" class="text-right" style="letter-spacing:0.5px;">TOTAL BALANCING</td>
								<td id="pv_tot_debit" class="text-right" style="color:#2b6cb0; font-size:14px;"><?= number_format($harus_kembali) ?></td>
								<td id="pv_tot_kredit" class="text-right" style="color:#2b6cb0; font-size:14px;"><?= number_format($harus_kembali) ?></td>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>

		<!-- Mockup Note Box -->
		<div class="mockup-note-box">
			<b>Catatan mockup:</b> Jurnal pengembalian terbentuk saat bukti dikirim (draft) dan baru diposting setelah Finance memverifikasi. Dengan begitu saldo Bank tidak bertambah di pembukuan sebelum uangnya benar-benar dicocokkan dengan mutasi rekening.
		</div>

		<!-- Action Footer -->
		<div class="action-bar-footer">
			<button type="button" class="btn-cancel-form" onclick="close_form_pengembalian()">
				<i class="fa fa-reply"></i> Kembali / Batal
			</button>
			<?php if (!$is_readonly): ?>
				<button type="submit" class="btn-submit-kirim" id="btn_submit_kirim_pengembalian">
					<i class="fa fa-send"></i> Kirim Bukti Transfer
				</button>
			<?php endif; ?>
		</div>
	</form>
</div>

<script>
	$(document).ready(function() {
		update_journal_preview();
	});
	// DataTransfer Accumulator untuk multi-file picker
	var accumulatedDataTransfer = new DataTransfer();

	// Inisialisasi datepicker jika tersedia
	if ($.fn.datepicker) {
		$('#transfer_tanggal_input').datepicker({
			format: 'yyyy-mm-dd',
			autoclose: true,
			todayHighlight: true
		}).on('changeDate change', function() {
			update_journal_preview();
		});
	} else {
		$('#transfer_tanggal_input').on('change input', function() {
			update_journal_preview();
		});
	}

	// Update live preview saat bank berubah
	$('#id_bank_pengembalian').on('change', function() {
		var opt = $(this).find(':selected');
		var coa = opt.data('coa') || '';
		var bankName = opt.data('bank-name') || '';
		var company = opt.data('company') || 'STM';

		$('#transfer_coa_bank_val').val(coa);
		update_journal_preview();
	});

	// Update live preview saat nominal transfer berubah
	$('#transfer_jumlah_input').on('input change', function() {
		update_journal_preview();
	});

	function update_journal_preview() {
		var opt = $('#id_bank_pengembalian').find(':selected');
		var coa = opt.data('coa') || '—';
		var bankName = opt.data('bank-name') || '(pilih bank)';
		var company = opt.data('company') || 'STM';
		var tglVal = $('#transfer_tanggal_input').val();

		// Format tanggal display
		var formattedDate = '<?= date('d F Y') ?>';
		if (tglVal) {
			var d = new Date(tglVal);
			if (!isNaN(d.getTime())) {
				var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
				formattedDate = d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
			}
		}

		// Format nominal
		var nominalRaw = $('#transfer_jumlah_input').val().replace(/[^0-9]/g, '');
		var nominal = parseFloat(nominalRaw) || 0;
		var nominalFmt = Number(nominal).toLocaleString('en-US');

		// Update elemen DOM tabel jurnal
		$('#pv_tgl_1').text(formattedDate);
		$('#pv_tgl_2').text(formattedDate);
		$('#pv_coa_1').text(coa);
		$('#pv_comp_1').text(company);
		$('#pv_comp_2').text(company);

		if (bankName !== '(pilih bank)') {
			$('#pv_acc_1').text(bankName).css({'color': '#1e293b', 'font-style': 'normal', 'font-weight': '600'});
		} else {
			$('#pv_acc_1').text('(pilih bank)').css({'color': '#64748b', 'font-style': 'italic', 'font-weight': 'normal'});
		}

		$('#pv_debit_1').text(nominalFmt);
		$('#pv_kredit_2').text(nominalFmt);
		$('#pv_tot_debit').text(nominalFmt);
		$('#pv_tot_kredit').text(nominalFmt);
	}

	// Multi-file selection accumulator handler
	$('#multi_file_input').on('change', function() {
		var newFiles = this.files;
		if (newFiles && newFiles.length > 0) {
			for (var i = 0; i < newFiles.length; i++) {
				accumulatedDataTransfer.items.add(newFiles[i]);
			}
			render_file_chips();
		}
		// Reset file input agar user bisa memilih file yang sama jika diperlukan
		$(this).val('');
	});

	function render_file_chips() {
		var container = $('#file_chips_wrapper');
		container.empty();

		var files = accumulatedDataTransfer.files;
		if (files.length === 0) return;

		for (var i = 0; i < files.length; i++) {
			var f = files[i];
			var sizeKB = (f.size / 1024).toFixed(1);
			var sizeStr = sizeKB > 1024 ? (sizeKB / 1024).toFixed(2) + ' MB' : sizeKB + ' KB';

			var chip = $('<div class="file-chip">' +
				'<i class="fa fa-file-text-o file-icon"></i>' +
				'<span>' + escapeHtml(f.name) + '</span>' +
				'<span class="file-size">(' + sizeStr + ')</span>' +
				'<span class="btn-remove-chip" data-index="' + i + '" title="Hapus file ini">&times;</span>' +
				'</div>');
			container.append(chip);
		}
	}

	$(document).on('click', '.btn-remove-chip', function() {
		var idx = parseInt($(this).data('index'));
		var newDataTransfer = new DataTransfer();
		var files = accumulatedDataTransfer.files;

		for (var i = 0; i < files.length; i++) {
			if (i !== idx) {
				newDataTransfer.items.add(files[i]);
			}
		}
		accumulatedDataTransfer = newDataTransfer;
		render_file_chips();
	});

	function escapeHtml(text) {
		return $('<div>').text(text).html();
	}

	function close_form_pengembalian() {
		$('#form-data').hide().empty();
		$('.box, .nav-tabs-custom').show();
	}

	// Submit handler pengiriman bukti pengembalian
	$('#formKirimPengembalian').on('submit', function(e) {
		e.preventDefault();

		var bank = $('#id_bank_pengembalian').val();
		if (!bank) {
			Swal.fire({
				title: "Perhatian!",
				text: "Bank Tujuan Transfer Perusahaan wajib dipilih!",
				icon: "warning"
			});
			return false;
		}

		if (accumulatedDataTransfer.files.length === 0) {
			Swal.fire({
				title: "Perhatian!",
				text: "Bukti transfer pengembalian dana wajib dilampirkan (minimal 1 file gambar / PDF)!",
				icon: "warning"
			});
			return false;
		}

		var nominalRaw = $('#transfer_jumlah_input').val().replace(/[^0-9]/g, '');
		var nominal = parseFloat(nominalRaw) || 0;
		if (nominal <= 0) {
			Swal.fire({
				title: "Perhatian!",
				text: "Nominal transfer pengembalian harus lebih dari 0!",
				icon: "warning"
			});
			return false;
		}

		// Bangun FormData dengan file dari akumulator
		var formData = new FormData(this);
		// Hapus field file default jika ada
		formData.delete('bukti_transfer[]');
		// Masukkan seluruh file dari akumulator
		for (var i = 0; i < accumulatedDataTransfer.files.length; i++) {
			formData.append('bukti_transfer[]', accumulatedDataTransfer.files[i]);
		}

		$('#btn_submit_kirim_pengembalian').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Mengirim...');

		$.ajax({
			url: siteurl + 'expense/save_pengembalian_dana',
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			dataType: 'json',
			success: function(res) {
				$('#btn_submit_kirim_pengembalian').prop('disabled', false).html('<i class="fa fa-send"></i> Kirim Bukti Transfer');
				if (res && res.save) {
					Swal.fire({
						title: "Berhasil!",
						text: res.message,
						icon: "success"
					}).then(function() {
						close_form_pengembalian();
						if (typeof load_table_pengembalian === 'function') {
							load_table_pengembalian();
						} else {
							window.location.reload();
						}
					});
				} else {
					Swal.fire({
						title: "Gagal!",
						text: (res && res.message) ? res.message : "Gagal mengunggah bukti pengembalian.",
						icon: "error"
					});
				}
			},
			error: function() {
				$('#btn_submit_kirim_pengembalian').prop('disabled', false).html('<i class="fa fa-send"></i> Kirim Bukti Transfer');
				Swal.fire("Gagal!", "Terjadi kesalahan pada server saat mengirim data.", "error");
			}
		});
	});
</script>
