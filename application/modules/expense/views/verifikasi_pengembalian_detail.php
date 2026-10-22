<?php
$no_doc_val = isset($data->no_doc) ? $data->no_doc : '';
$no_kasbon = isset($data->id_kasbon) ? $data->id_kasbon : '-';
$tgl_doc = isset($data->tgl_doc) ? $data->tgl_doc : '-';
$nama_pelapor = isset($data->nama) ? $data->nama : '-';
$informasi = isset($data->informasi) ? $data->informasi : '-';
$departement = isset($data->departement) ? $data->departement : '-';

$tot_kasbon = isset($data->total_kasbon) ? floatval($data->total_kasbon) : 0;
$tot_expense = isset($data->jumlah) ? floatval($data->jumlah) : 0;
$lebih_kasbon = isset($data->lebih_bayar) ? floatval($data->lebih_bayar) : ($tot_kasbon - $tot_expense);

$transfer_bank_coa = isset($data->transfer_coa_bank) ? $data->transfer_coa_bank : '';
$transfer_bank_name = isset($bank_name) ? $bank_name : $transfer_bank_coa;
$transfer_tanggal = isset($data->transfer_tanggal) ? $data->transfer_tanggal : '-';
$transfer_jumlah = isset($data->transfer_jumlah) ? floatval($data->transfer_jumlah) : $lebih_kasbon;

$bukti_files = [];
if (!empty($data->bukti_pengembalian)) {
	$raw_files = explode(';', $data->bukti_pengembalian);
	foreach ($raw_files as $f) {
		$f = trim($f);
		if (!empty($f)) $bukti_files[] = $f;
	}
}
?>

<style>
	.verif-card {
		background: #fff;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		box-shadow: 0 2px 8px rgba(0,0,0,0.04);
		margin-bottom: 20px;
	}
	.verif-header {
		padding: 14px 20px;
		border-bottom: 1px solid #edf2f7;
		display: flex;
		justify-content: space-between;
		align-items: center;
		background: #f8fafc;
		border-radius: 8px 8px 0 0;
	}
	.checklist-box {
		background: #fffdf5;
		border: 1px solid #f9e2ae;
		border-radius: 6px;
		padding: 15px 20px;
		margin-bottom: 20px;
	}
	.checklist-item {
		font-size: 13px;
		color: #2d3748;
		margin-bottom: 10px;
		cursor: pointer;
		display: flex;
		align-items: center;
		gap: 10px;
	}
	.checklist-item input[type="checkbox"] {
		width: 18px;
		height: 18px;
		cursor: pointer;
	}
	.table-jurnal2 thead th {
		background: #2b6cb0;
		color: #fff;
		font-size: 12px;
		text-align: center;
		vertical-align: middle;
	}
	.table-jurnal2 tbody td {
		font-size: 12px;
		vertical-align: middle;
	}
</style>

<div class="verif-card">
	<div class="verif-header">
		<h4 style="margin:0; font-size:16px; font-weight:700; color:#2d3748;">
			<i class="fa fa-check-square-o text-primary"></i> Verifikasi Pengembalian Kelebihan Kasbon
		</h4>
		<button type="button" class="btn btn-default btn-sm" onclick="window.location.reload();">
			<i class="fa fa-reply"></i> Kembali ke Antrian
		</button>
	</div>

	<div class="box-body" style="padding: 20px;">
		<!-- RINGKASAN EXPENSE & KASBON -->
		<div class="row">
			<div class="col-md-6">
				<table class="table table-condensed table-striped" style="font-size:13px; margin-bottom:0;">
					<tr>
						<th width="150">No. Dokumen Expense</th>
						<td><b><?= htmlspecialchars($no_doc_val) ?></b></td>
					</tr>
					<tr>
						<th>No. Kasbon Asal</th>
						<td><span class="badge bg-blue"><i class="fa fa-ticket"></i> <?= htmlspecialchars($no_kasbon) ?></span></td>
					</tr>
					<tr>
						<th>Tanggal Dokumen</th>
						<td><?= htmlspecialchars($tgl_doc) ?></td>
					</tr>
					<tr>
						<th>Pelapor / Pemohon</th>
						<td><?= htmlspecialchars($nama_pelapor) ?></td>
					</tr>
					<tr>
						<th>Departemen</th>
						<td><?= htmlspecialchars($departement) ?></td>
					</tr>
					<tr>
						<th>Keterangan Laporan</th>
						<td><?= htmlspecialchars($informasi) ?></td>
					</tr>
				</table>
			</div>
			<div class="col-md-6">
				<div class="well well-sm" style="background:#f8fafc; border-radius:6px; margin-bottom:0;">
					<h5 style="font-weight:700; color:#2d3748; margin-top:0; border-bottom:1px solid #e2e8f0; padding-bottom:6px;">
						<i class="fa fa-calculator text-primary"></i> Rekapitulasi Nominal
					</h5>
					<div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px;">
						<span>Total Kasbon Awal:</span>
						<b style="color:#2b6cb0;">Rp <?= number_format($tot_kasbon) ?></b>
					</div>
					<div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px;">
						<span>Total Realisasi Pengeluaran:</span>
						<b style="color:#28a745;">Rp <?= number_format($tot_expense) ?></b>
					</div>
					<div style="display:flex; justify-content:space-between; font-size:14px; padding-top:6px; border-top:1px dashed #cbd5e0; color:#c53030;">
						<b>Sisa Kelebihan Kasbon (Wajib Dikembalikan):</b>
						<b style="font-size:16px;">Rp <?= number_format($lebih_kasbon) ?></b>
					</div>
				</div>
			</div>
		</div>

		<hr style="margin: 20px 0; border-color: #edf2f7;">

		<!-- DATA PENGEMBALIAN DANA DARI PELAPOR -->
		<div class="row">
			<div class="col-md-6">
				<div class="panel panel-default" style="border-radius:6px;">
					<div class="panel-heading" style="background:#f8fafc; font-weight:700; font-size:13px; color:#2d3748;">
						<i class="fa fa-university text-primary"></i> Data Transfer Pengembalian Dana
					</div>
					<div class="panel-body" style="font-size:13px;">
						<div style="margin-bottom:10px;">
							<label style="color:#718096; font-size:12px; margin-bottom:2px; display:block;">Bank Tujuan Perusahaan:</label>
							<b style="font-size:14px; color:#2d3748;"><?= htmlspecialchars($transfer_bank_name) ?></b>
							<small class="text-muted" style="display:block;">(COA: <?= htmlspecialchars($transfer_bank_coa) ?>)</small>
						</div>
						<div style="margin-bottom:10px;">
							<label style="color:#718096; font-size:12px; margin-bottom:2px; display:block;">Tanggal Transfer:</label>
							<b><?= htmlspecialchars($transfer_tanggal) ?></b>
						</div>
						<div>
							<label style="color:#718096; font-size:12px; margin-bottom:2px; display:block;">Nominal Ditransfer:</label>
							<b style="font-size:16px; color:#2b6cb0;">Rp <?= number_format($transfer_jumlah) ?></b>
							<?php if ($transfer_jumlah == $lebih_kasbon): ?>
								<span class="badge bg-green" style="margin-left:6px;"><i class="fa fa-check"></i> Sesuai Nilai Kelebihan</span>
							<?php else: ?>
								<span class="badge bg-red" style="margin-left:6px;"><i class="fa fa-warning"></i> Selisih Rp <?= number_format(abs($transfer_jumlah - $lebih_kasbon)) ?></span>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>

			<div class="col-md-6">
				<div class="panel panel-default" style="border-radius:6px;">
					<div class="panel-heading" style="background:#f8fafc; font-weight:700; font-size:13px; color:#2d3748;">
						<i class="fa fa-paperclip text-primary"></i> Lampiran Bukti Transfer Pengembalian
					</div>
					<div class="panel-body">
						<?php if (!empty($bukti_files)): ?>
							<div style="display:flex; flex-direction:column; gap:8px;">
								<?php foreach ($bukti_files as $idx => $bf): 
									$file_url = base_url($bf);
									$is_pdf = (stripos($bf, '.pdf') !== false);
								?>
									<div style="display:flex; align-items:center; justify-content:space-between; padding:8px 12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px;">
										<span style="font-size:13px; color:#2d3748;">
											<i class="fa <?= $is_pdf ? 'fa-file-pdf-o text-danger' : 'fa-file-image-o text-primary' ?>"></i>
											<?= htmlspecialchars(basename($bf)) ?>
										</span>
										<a href="<?= $file_url ?>" target="_blank" class="btn btn-primary btn-xs btn-flat-custom">
											<i class="fa fa-external-link"></i> Buka File
										</a>
									</div>
								<?php endforeach; ?>
							</div>
						<?php else: ?>
							<div class="alert alert-warning" style="margin-bottom:0; font-size:12px;">
								<i class="fa fa-warning"></i> Belum ada file bukti transfer pengembalian yang terunggah.
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

		<!-- PREVIEW JURNAL 2 (POSTING OTOMATIS) -->
		<div class="panel panel-default" style="border-radius:6px; margin-top:10px;">
			<div class="panel-heading" style="background:#edf2f7; font-weight:700; font-size:13px; color:#2d3748;">
				<i class="fa fa-book text-primary"></i> Preview JURNAL 2 (Penerimaan Pengembalian Kasbon &amp; Pengurangan Piutang)
			</div>
			<div class="panel-body" style="padding:10px;">
				<div class="table-responsive">
					<table class="table table-bordered table-striped table-jurnal2" width="100%" style="margin-bottom:0;">
						<thead>
							<tr>
								<th width="110">Tanggal</th>
								<th width="120">No. Akun (COA)</th>
								<th>Nama Akun</th>
								<th>Keterangan Jurnal</th>
								<th width="140" class="text-right">Debit (Rp)</th>
								<th width="140" class="text-right">Kredit (Rp)</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td class="text-center"><?= htmlspecialchars($transfer_tanggal) ?></td>
								<td class="text-center"><b><?= htmlspecialchars($transfer_bank_coa) ?></b></td>
								<td><b><?= htmlspecialchars($transfer_bank_name) ?></b></td>
								<td>Penerimaan Pengembalian Kelebihan Kasbon - <?= htmlspecialchars($no_doc_val) ?> (<?= htmlspecialchars($no_kasbon) ?>)</td>
								<td class="text-right" style="font-weight:bold; color:#00a65a;"><?= number_format($transfer_jumlah) ?></td>
								<td class="text-right">0</td>
							</tr>
							<tr style="background:#e8fadf;">
								<td class="text-center"><?= htmlspecialchars($transfer_tanggal) ?></td>
								<td class="text-center"><b>1103-01-15</b></td>
								<td><b>Piutang Kelebihan Kasbon</b></td>
								<td>Penghapusan Piutang Kelebihan Kasbon (Pengembalian Selesai)</td>
								<td class="text-right">0</td>
								<td class="text-right" style="font-weight:bold; color:#2e59d9;"><?= number_format($transfer_jumlah) ?></td>
							</tr>
						</tbody>
						<tfoot>
							<tr style="font-weight:bold; background:#edf2f7;">
								<td colspan="4" class="text-center">TOTAL BALANCING</td>
								<td class="text-right" style="color:#00a65a;">Rp <?= number_format($transfer_jumlah) ?></td>
								<td class="text-right" style="color:#2e59d9;">Rp <?= number_format($transfer_jumlah) ?></td>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>

		<!-- 3 CHECKLIST VERIFIKASI WAJIB -->
		<div class="checklist-box">
			<h5 style="margin-top:0; font-weight:700; color:#8a4b12; margin-bottom:12px;">
				<i class="fa fa-shield"></i> Checklist Verifikasi Keuangan Wajib (FR-F13, FR-F14):
			</h5>
			<p style="font-size:12px; color:#744210; margin-bottom:12px;">
				Finance wajib mencentang seluruh 3 butir di bawah setelah melakukan konfirmasi fisik dan mutasi rekening:
			</p>

			<label class="checklist-item">
				<input type="checkbox" id="chk_bukti" onchange="check_verif_readiness()">
				<span><b>1. Bukti Transfer Valid &amp; Jelas:</b> Bukti transfer yang diunggah asli, sah, dan dapat terbaca jelas.</span>
			</label>

			<label class="checklist-item">
				<input type="checkbox" id="chk_mutasi" onchange="check_verif_readiness()">
				<span><b>2. Mutasi Rekening Bank Cocok:</b> Dana telah dicek dan terkonfirmasi masuk ke rekening bank perusahaan (rekening koran/e-banking).</span>
			</label>

			<label class="checklist-item">
				<input type="checkbox" id="chk_nominal" onchange="check_verif_readiness()">
				<span><b>3. Nominal Sesuai:</b> Jumlah uang yang masuk (Rp <?= number_format($transfer_jumlah) ?>) sesuai dengan sisa kelebihan kasbon yang tercatat (Rp <?= number_format($lebih_kasbon) ?>).</span>
			</label>
		</div>

		<!-- FOOTER CONTROLS -->
		<div style="border-top:1px solid #edf2f7; padding-top:15px; display:flex; justify-content:space-between; align-items:center;">
			<div>
				<button type="button" class="btn btn-default btn-sm" onclick="window.location.reload();">
					<i class="fa fa-reply"></i> Batal / Kembali
				</button>
			</div>
			<div style="display:flex; gap:10px;">
				<button type="button" class="btn btn-danger btn-sm" onclick="reject_verifikasi(<?= $data->id ?>)">
					<i class="fa fa-ban"></i> Tolak Pengembalian
				</button>
				<button type="button" class="btn btn-success btn-sm" id="btn_approve_verif" disabled onclick="approve_verifikasi(<?= $data->id ?>)">
					<i class="fa fa-check-circle"></i> Verifikasi Selesai &amp; Posting Jurnal 2
				</button>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	function check_verif_readiness() {
		var c1 = $('#chk_bukti').is(':checked');
		var c2 = $('#chk_mutasi').is(':checked');
		var c3 = $('#chk_nominal').is(':checked');

		if (c1 && c2 && c3) {
			$('#btn_approve_verif').prop('disabled', false).removeClass('btn-default').addClass('btn-success');
		} else {
			$('#btn_approve_verif').prop('disabled', true);
		}
	}

	function approve_verifikasi(id) {
		Swal.fire({
			title: "Selesaikan Verifikasi?",
			text: "Jurnal 2 akan langsung dibukukan ke buku besar dan status laporan menjadi Selesai!",
			icon: "question",
			showCancelButton: true,
			confirmButtonText: "Ya, Verifikasi & Posting Jurnal 2",
			cancelButtonText: "Batal",
			confirmButtonColor: "#28a745"
		}).then((res) => {
			if (res.isConfirmed) {
				$.post(siteurl + 'expense/verifikasi_pengembalian_approve', {
					id: id,
					chk_bukti: $('#chk_bukti').is(':checked') ? 1 : 0,
					chk_mutasi: $('#chk_mutasi').is(':checked') ? 1 : 0,
					chk_nominal: $('#chk_nominal').is(':checked') ? 1 : 0
				}, function(resp) {
					if (resp && resp.save) {
						Swal.fire({
							title: "Sukses!",
							text: resp.message || "Verifikasi pengembalian kasbon selesai.",
							icon: "success"
						}).then(() => {
							window.location.reload();
						});
					} else {
						Swal.fire({
							title: "Gagal!",
							text: (resp && resp.message) ? resp.message : "Gagal memproses verifikasi.",
							icon: "error"
						});
					}
				}, "json").fail(function(xhr) {
					var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Terjadi kesalahan pada server.";
					Swal.fire("Gagal!", msg, "error");
				});
			}
		});
	}

	function reject_verifikasi(id) {
		Swal.fire({
			title: "Tolak Pengembalian Kasbon",
			input: "textarea",
			inputLabel: "Alasan Penolakan Verifikasi (Wajib Diisi)",
			inputPlaceholder: "Tuliskan alasan penolakan secara spesifik (misal: mutasi belum masuk, bukti buram)...",
			showCancelButton: true,
			confirmButtonText: "Tolak & Minta Kirim Ulang",
			cancelButtonText: "Batal",
			confirmButtonColor: "#d33",
			inputValidator: (value) => {
				if (!value || $.trim(value) === "") {
					return "Alasan penolakan wajib diisi!";
				}
			}
		}).then((res) => {
			if (res.isConfirmed) {
				$.post(siteurl + 'expense/verifikasi_pengembalian_reject', {
					id: id,
					reason: res.value
				}, function(resp) {
					if (resp && resp.save) {
						Swal.fire({
							title: "Ditolak!",
							text: "Status dikembalikan ke antrian pelapor untuk transfer / kirim ulang bukti.",
							icon: "info"
						}).then(() => {
							window.location.reload();
						});
					} else {
						Swal.fire("Gagal!", (resp && resp.message) ? resp.message : "Gagal menolak.", "error");
					}
				}, "json").fail(function(xhr) {
					var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Terjadi kesalahan pada server.";
					Swal.fire("Gagal!", msg, "error");
				});
			}
		});
	}
</script>
