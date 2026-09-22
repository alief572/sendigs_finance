<?php
/**
 * Komponen Card Danger Informatif untuk Dokumen Expense yang Ditolak (Rejected)
 * Menampilkan: Identitas penolak, Waktu penolakan, dan Alasan penolakan.
 */
$is_rejected = false;
if (isset($data) && !empty($data)) {
	$data_obj = is_array($data) ? (object)$data : $data;
	// Card reject HANYA muncul jika status dokumen saat ini masih Rejected (status == 9)
	if (isset($data_obj->status) && (string)$data_obj->status === '9') {
		$is_rejected = true;
	}
}

if ($is_rejected):
	// 1. Alasan Penolakan
	$alasan_reject = '';
	if (!empty($data_obj->reject_reason)) {
		$alasan_reject = $data_obj->reject_reason;
	} elseif (!empty($data_obj->reject_reason_finance)) {
		$alasan_reject = $data_obj->reject_reason_finance;
	} elseif (!empty($data_obj->st_reject)) {
		$alasan_reject = $data_obj->st_reject;
	} else {
		$alasan_reject = 'Tidak ada catatan alasan yang dilampirkan.';
	}

	// 2. Siapa yang menolak
	$penolak_username = !empty($data_obj->rejected_by) ? $data_obj->rejected_by : '';
	if (empty($penolak_username) && !empty($data_obj->modified_by)) {
		$creator = !empty($data_obj->created_by) ? $data_obj->created_by : (!empty($data_obj->nama) ? $data_obj->nama : '');
		if ($data_obj->modified_by !== $creator) {
			$penolak_username = $data_obj->modified_by;
		}
	}

	$penolak_nama = $penolak_username;
	$penolak_full = '';
	if (!empty($penolak_username) && function_exists('get_instance')) {
		$CI =& get_instance();
		$get_user = $CI->db->select('nm_lengkap, username')->get_where('users', ['username' => $penolak_username])->row();
		if (!empty($get_user) && !empty($get_user->nm_lengkap)) {
			$penolak_nama = $get_user->nm_lengkap;
			$penolak_full = $get_user->username;
		}
	}
	if (empty($penolak_nama)) {
		$penolak_nama = '-';
	}

	// 3. Kapan ditolak
	$waktu_raw = !empty($data_obj->rejected_on) ? $data_obj->rejected_on : '';
	if (empty($waktu_raw) && !empty($penolak_username) && !empty($data_obj->modified_on)) {
		$waktu_raw = $data_obj->modified_on;
	}

	$waktu_reject = '-';
	if (!empty($waktu_raw) && $waktu_raw != '0000-00-00 00:00:00') {
		$bulan_indo = [
			1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
			5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
			9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
		];
		$ts = strtotime($waktu_raw);
		$d = date('j', $ts);
		$m = (int)date('n', $ts);
		$y = date('Y', $ts);
		$h = date('H:i', $ts);
		$waktu_reject = $d . ' ' . $bulan_indo[$m] . ' ' . $y . ' &bull; ' . $h . ' WIB';
	}
?>
<style>
	.reject-card-wrapper {
		background: #ffffff;
		border: 1px solid #fecaca;
		border-left: 5px solid #dc2626;
		border-radius: 8px;
		box-shadow: 0 4px 14px rgba(220, 38, 38, 0.08);
		margin-bottom: 22px;
		overflow: hidden;
	}
	.reject-card-header {
		background: linear-gradient(to right, #fef2f2, #fff5f5);
		padding: 13px 18px;
		border-bottom: 1px solid #fee2e2;
		display: flex;
		align-items: center;
		justify-content: space-between;
		flex-wrap: wrap;
		gap: 10px;
	}
	.reject-card-title-group {
		display: flex;
		align-items: center;
		gap: 12px;
	}
	.reject-icon-badge {
		width: 38px;
		height: 38px;
		border-radius: 50%;
		background: #fee2e2;
		color: #dc2626;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		font-size: 18px;
		box-shadow: inset 0 0 0 1px rgba(220, 38, 38, 0.15);
		flex-shrink: 0;
	}
	.reject-card-title {
		margin: 0;
		color: #991b1b;
		font-size: 15px;
		font-weight: 700;
		letter-spacing: 0.2px;
	}
	.reject-card-subtitle {
		margin: 2px 0 0 0;
		font-size: 11.5px;
		color: #b91c1c;
		opacity: 0.9;
	}
	.reject-badge-status {
		background-color: #dc2626;
		color: #ffffff;
		font-size: 11px;
		font-weight: 700;
		padding: 5px 12px;
		border-radius: 20px;
		letter-spacing: 0.6px;
		text-transform: uppercase;
		display: inline-flex;
		align-items: center;
		gap: 5px;
		box-shadow: 0 2px 4px rgba(220, 38, 38, 0.25);
	}
	.reject-card-body {
		padding: 16px 18px;
		background: #ffffff;
	}
	.reject-info-grid {
		display: flex;
		flex-wrap: wrap;
		gap: 12px;
		margin-bottom: 14px;
	}
	.reject-info-tile {
		flex: 1 1 250px;
		background: #fafafa;
		border: 1px solid #f1f1f1;
		border-radius: 6px;
		padding: 10px 14px;
		display: flex;
		align-items: center;
		gap: 12px;
	}
	.reject-tile-icon {
		width: 34px;
		height: 34px;
		border-radius: 6px;
		background: #ffffff;
		border: 1px solid #e5e7eb;
		display: flex;
		align-items: center;
		justify-content: center;
		color: #ef4444;
		font-size: 15px;
		flex-shrink: 0;
	}
	.reject-tile-label {
		font-size: 11px;
		text-transform: uppercase;
		color: #6b7280;
		font-weight: 600;
		letter-spacing: 0.4px;
		margin-bottom: 2px;
	}
	.reject-tile-value {
		font-size: 13.5px;
		font-weight: 700;
		color: #1f2937;
		word-break: break-word;
	}
	.reject-tile-sub {
		font-size: 11.5px;
		font-weight: 500;
		color: #9ca3af;
		margin-left: 4px;
	}
	.reject-reason-box {
		background: #fffdfd;
		border: 1px solid #fecaca;
		border-radius: 6px;
		padding: 12px 16px;
	}
	.reject-reason-header {
		font-size: 12px;
		font-weight: 700;
		color: #b91c1c;
		margin-bottom: 6px;
		display: flex;
		align-items: center;
		gap: 6px;
	}
	.reject-reason-content {
		font-size: 13.5px;
		color: #7f1d1d;
		line-height: 1.6;
		font-weight: 500;
		white-space: pre-line;
		margin: 0;
	}
</style>

<div class="reject-card-wrapper">
	<div class="reject-card-header">
		<div class="reject-card-title-group">
			<div class="reject-icon-badge">
				<i class="fa fa-ban"></i>
			</div>
			<div>
				<h4 class="reject-card-title">Pengajuan Dokumen Expense Ditolak</h4>
				<p class="reject-card-subtitle">Silakan periksa catatan alasan penolakan di bawah ini sebelum melakukan revisi pengajuan.</p>
			</div>
		</div>
		<div style="display: flex; align-items: center; gap: 10px;">
			<span class="reject-badge-status">
				<i class="fa fa-times-circle"></i> REJECTED
			</span>
			<button type="button" class="close" onclick="$(this).closest('.reject-card-wrapper').slideUp(250);" aria-label="Close" style="opacity: 0.5; font-size: 20px; line-height: 1; margin-top: -2px; color: #991b1b;" title="Tutup pemberitahuan">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
	</div>

	<div class="reject-card-body">
		<div class="reject-info-grid">
			<!-- TILE: PENOLAK -->
			<div class="reject-info-tile">
				<div class="reject-tile-icon">
					<i class="fa fa-user"></i>
				</div>
				<div>
					<div class="reject-tile-label">Ditolak Oleh</div>
					<div class="reject-tile-value">
						<span class="text-danger"><?= htmlspecialchars($penolak_nama) ?></span>
						<?php if (!empty($penolak_full) && strtolower($penolak_full) !== strtolower($penolak_nama)): ?>
							<span class="reject-tile-sub">(@<?= htmlspecialchars($penolak_full) ?>)</span>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<!-- TILE: WAKTU PENOLAKAN -->
			<div class="reject-info-tile">
				<div class="reject-tile-icon">
					<i class="fa fa-calendar-times-o"></i>
				</div>
				<div>
					<div class="reject-tile-label">Waktu Penolakan</div>
					<div class="reject-tile-value">
						<span><?= $waktu_reject ?></span>
					</div>
				</div>
			</div>
		</div>

		<!-- BOX: ALASAN PENOLAKAN -->
		<div class="reject-reason-box">
			<div class="reject-reason-header">
				<i class="fa fa-commenting"></i> Catatan & Alasan Penolakan:
			</div>
			<p class="reject-reason-content"><?= nl2br(htmlspecialchars($alasan_reject)) ?></p>
		</div>
	</div>
</div>
<?php endif; ?>

