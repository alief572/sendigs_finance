<?php
$ENABLE_MANAGE = has_permission('Approval_Request_Payment.Manage');
$transfers = $transfers ?? [];
if ($transfers) {
	$rp_tutorial_page = 'decision';
	include dirname(__DIR__, 2) . '/request_payment/views/transfer_tutorial.php';
}

// build items JSON for JS
$items_js = [];
foreach ($items as $it) {
	$items_js[] = [
		'id'         => $it->id,
		'no_dokumen' => $it->no_dokumen,
		'kategori'   => $it->kategori,
		'request_by' => $it->request_by,
		'keperluan'  => $it->keperluan,
		'keterangan_finance' => $it->keterangan_finance ?? '',
		'id_transfer' => $it->id_transfer ?? null,
		'dpp'        => (float) $it->dpp,
		'nilai_ppn'  => (float) $it->nilai_ppn,
		'nilai_pph'  => (float) $it->nilai_pph,
		'flag_ppn'   => (int) $it->flag_ppn,
		'flag_pph23' => (int) $it->flag_pph23,
		'flag_pph21' => (int) $it->flag_pph21,
		'admin'      => (int) $it->admin,
		'dibayarkan' => (float) $it->dibayarkan,
		'print_url'  => isset($it->print_url) ? $it->print_url : '',
	];
}
?>

<style>
	.detail-head {
		display: flex;
		justify-content: space-between;
		align-items: flex-start;
		margin-bottom: 16px;
		padding-bottom: 16px;
		border-bottom: 1px solid #E1E5EA;
		flex-wrap: wrap;
		gap: 12px;
	}

	.detail-head .summary {
		display: flex;
		gap: 26px;
	}

	.detail-head .summary .lbl {
		font-size: 11px;
		color: #7C8A99;
	}

	.detail-head .summary .val {
		font-weight: 700;
		font-size: 16px;
	}

	.subtable-label {
		display: flex;
		align-items: center;
		gap: 8px;
		margin: 20px 0 8px;
		font-size: 13px;
		font-weight: 700;
	}

	.subtable-label .count {
		background: #DCE3EA;
		color: #5A6979;
		font-size: 11px;
		padding: 2px 8px;
		border-radius: 10px;
	}

	.subtable-label.reject-label {
		color: #C4453B;
	}

	.subtable-label.reject-label .count {
		background: #C4453B;
		color: #fff;
	}

	.rp-appr-table thead th {
		background: #3c8dbc;
		color: #fff;
		font-size: 11.5px;
		white-space: nowrap;
		text-align: center;
	}

	.rp-appr-table thead th.text-right-imp {
		text-align: right;
	}

	.rp-appr-table.rp-trf-table thead th {
		background: #605ca8;
	}

	.rp-appr-table tbody td {
		font-size: 12.5px;
		vertical-align: middle;
	}

	.rp-kategori-badge {
		display: inline-block;
		background: #3A4656;
		color: #fff;
		font-size: 10.5px;
		font-weight: 600;
		padding: 3px 9px;
		border-radius: 10px;
	}

	.mini-btn {
		border: 1px solid #ccc;
		background: #fff;
		padding: 4px 10px;
		border-radius: 4px;
		font-size: 11.5px;
		font-weight: 600;
		cursor: pointer;
		color: #333;
		display: inline-block;
		vertical-align: middle;
	}

	.mini-btn.reject {
		color: #C4453B;
		border-color: #F2C4C0;
	}

	.mini-btn.reject.active {
		background: #C4453B;
		color: #fff;
	}

	.reason-box textarea {
		width: 100%;
		min-height: 44px;
		border: 1px solid #F0C6C1;
		border-radius: 5px;
		padding: 6px 9px;
		font-size: 12px;
	}

	.text-right-imp {
		text-align: right;
	}
	.rp-transfer-tag { display:inline-block; border-radius:10px; background:#605ca8; color:#fff; font-size:10.5px; font-weight:700; padding:2px 8px; white-space:nowrap }
</style>

<div class="box">
	<div class="box-body">

		<a href="<?= site_url('approval_request_payment'); ?>" class="btn btn-link" style="padding-left:0;">
			&larr; Kembali ke daftar pengajuan
		</a>

		<div class="detail-head">
			<div>
				<h3 style="margin:0 0 6px;">Pengajuan <?= html_escape($header->no_pengajuan); ?></h3>
				<div class="text-muted">Company: <?= html_escape($header->company_nama ? $header->company_nama : '-'); ?> &mdash; diajukan <?= !empty($header->submitted_at) ? date('d-M-Y H:i', strtotime($header->submitted_at)) : '-'; ?></div>
			</div>
			<div class="summary">
				<div class="text-right">
					<div class="lbl">JUMLAH ITEM</div>
					<div class="val" id="d_count"><?= count($items); ?></div>
				</div>
				<?php if ($transfers): ?>
				<div class="text-right">
					<div class="lbl">JUMLAH TRANSFER</div>
					<div class="val" id="d_transfers"><?= count($transfers); ?></div>
				</div>
				<?php endif; ?>
				<div class="text-right">
					<div class="lbl">TOTAL DIBAYARKAN</div>
					<div class="val" id="d_total">Rp 0</div>
				</div>
			</div>
		</div>

		<?php if ($transfers): ?>
			<div class="subtable-label">Rincian Transfer <span class="count"><?= count($transfers) ?></span>
				<span class="text-muted" style="font-weight:400;">&mdash; 1 baris = 1 kali transfer, mengikuti keputusan di bawah. Approver hanya bisa mengubah <b>rekening tujuan</b>.</span></div>
			<div class="table-responsive"><table class="table table-bordered rp-appr-table rp-trf-table">
				<thead><tr><th>TRANSFER</th><th>PENERIMA / REKENING TUJUAN</th><th>DOKUMEN</th><th class="text-right-imp">TOTAL DIBAYARKAN</th><th class="text-center">ADMIN</th><th class="text-right-imp">JUMLAH TRANSFER</th><th class="text-right-imp">TOTAL KELUAR BANK</th></tr></thead>
				<tbody>
				<?php foreach ($transfers as $transfer): ?>
					<tr data-transfer="<?= (int) $transfer['id'] ?>">
						<td><span class="rp-transfer-tag">Transfer <?= (int) $transfer['urutan'] ?></span><div data-transfer-cancelled class="text-danger" hidden><small>batal, semua dokumen ditolak</small></div></td>
						<td><b data-show="nama"><?= html_escape($transfer['nama']) ?></b><br><small><span data-show="bank"><?= html_escape($transfer['bank']) ?></span> <span data-show="no_rek"><?= html_escape($transfer['no_rek']) ?></span> a/n <span data-show="nama"><?= html_escape($transfer['nama']) ?></span></small>
							<?php foreach (['bank', 'no_rek', 'nama'] as $key): ?>
								<input type="hidden" data-account="<?= $key ?>" data-original="<?= html_escape($transfer[$key]) ?>" value="<?= html_escape($transfer[$key]) ?>">
							<?php endforeach; ?>
							<div data-account-changed class="text-warning" hidden><small></small></div>
							<?php if ($ENABLE_MANAGE): ?>
								<div style="margin-top:6px"><button type="button" class="mini-btn" data-edit-account><i class="fa fa-pencil"></i> Edit rekening</button></div>
							<?php endif; ?></td>
						<td data-transfer-docs></td>
						<td class="text-right-imp" data-transfer-total></td>
						<td class="text-center"><?= (float) $transfer['admin'] ? number_format($transfer['admin'], 0, ',', '.') . '<br><small>' . ($transfer['bearer'] === 'recipient' ? 'Ditanggung penerima' : 'Ditanggung perusahaan') . '</small>' : '-' ?></td>
						<td class="text-right-imp"><b class="text-success" data-transfer-net></b></td><td class="text-right-imp" data-transfer-bank></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table></div>
			<?php if ($ENABLE_MANAGE): ?>
			<div class="modal fade" id="rp-edit-account" tabindex="-1" role="dialog" aria-labelledby="rp-edit-account-title">
				<div class="modal-dialog" role="document"><div class="modal-content">
					<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="rp-edit-account-title">Edit Rekening Tujuan</h4></div>
					<div class="modal-body">
						<p class="text-muted" style="font-size:12px;margin-top:0">Hanya rekening tujuan yang bisa diubah di approval. Dokumen, admin dan penanggung admin tetap. Berlaku saat Simpan Keputusan.</p>
						<div class="form-group"><label for="rp-ea-bank">Bank Tujuan *</label><input class="form-control input-sm" id="rp-ea-bank" list="rp-ea-banks" maxlength="100">
							<datalist id="rp-ea-banks"><?php foreach (['BCA', 'Mandiri', 'BNI', 'BRI', 'BSI', 'CIMB Niaga', 'Permata', 'Danamon', 'BTN'] as $bankName): ?><option value="<?= $bankName ?>"><?php endforeach; ?></datalist></div>
						<div class="form-group"><label for="rp-ea-no">No. Rekening *</label><input class="form-control input-sm" id="rp-ea-no" maxlength="80"></div>
						<div class="form-group" style="margin:0"><label for="rp-ea-name">Nama Penerima (a/n rekening) *</label><input class="form-control input-sm" id="rp-ea-name" maxlength="160"></div>
						<div class="text-danger" id="rp-ea-error" role="alert" style="margin-top:8px"></div>
					</div>
					<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
						<button type="button" class="btn btn-primary" id="rp-ea-save"><i class="fa fa-save"></i> Simpan Rekening</button></div>
				</div></div>
			</div>
			<?php endif; ?>
		<?php endif; ?>

		<!-- Akan Disetujui -->
		<div class="subtable-label">
			Akan Disetujui <span class="count" id="cnt_approve">0</span>
			<span class="text-muted" style="font-weight:400;">&mdash; item di tabel ini akan di-approve saat disimpan</span>
		</div>
		<div style="overflow-x:auto;">
			<table class="table table-bordered rp-appr-table" id="table_approve" style="width:100%;">
				<thead>
					<tr>
						<th>NO. DOKUMEN / KATEGORI</th>
						<?php if ($transfers): ?><th class="text-center">TRANSFER</th><?php endif; ?>
						<th>REQUEST BY</th>
						<th>KEPERLUAN</th>
						<th class="text-right-imp">DPP</th>
						<th class="text-center">PPN</th>
						<th class="text-center">PPH23</th>
						<th class="text-center">PPH21</th>
						<?php if (!$transfers): ?><th class="text-center">ADMIN</th><?php endif; ?>
						<th class="text-right-imp">DIBAYARKAN</th>
						<th style="width:130px;">AKSI</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>

		<!-- Akan Ditolak -->
		<div class="subtable-label reject-label" id="reject_label" style="display:none;">
			Akan Ditolak <span class="count" id="cnt_reject">0</span>
			<span class="text-muted" style="font-weight:400;color:#C4453B;">&mdash; item di tabel ini akan di-reject, kembali ke Request Payment</span>
		</div>
		<div style="overflow-x:auto;display:none;" id="reject_section">
			<table class="table table-bordered rp-appr-table" id="table_reject" style="width:100%;">
				<thead>
					<tr>
						<th>NO. DOKUMEN / KATEGORI</th>
						<?php if ($transfers): ?><th class="text-center">TRANSFER</th><?php endif; ?>
						<th>REQUEST BY</th>
						<th>KEPERLUAN</th>
						<th class="text-right-imp">DPP</th>
						<th class="text-center">PPN</th>
						<th class="text-center">PPH23</th>
						<th class="text-center">PPH21</th>
						<?php if (!$transfers): ?><th class="text-center">ADMIN</th><?php endif; ?>
						<th class="text-right-imp">DIBAYARKAN</th>
						<th style="width:130px;">AKSI</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>

		<div style="display:flex;justify-content:space-between;align-items:center;margin-top:18px;padding-top:16px;border-top:1px solid #E1E5EA;">
			<div class="text-muted">Item yang di-reject akan kembali ke Request Payment dengan alasan reject.<?= $transfers ? ' Admin tetap 1x per transfer untuk item yang tersisa.' : '' ?></div>
			<?php if ($ENABLE_MANAGE): ?>
				<button type="button" class="btn btn-primary" onclick="apprSaveDecision();">
					<i class="fa fa-save"></i> Simpan Keputusan
				</button>
			<?php endif; ?>
		</div>

	</div>
</div>

<script>
	var apprItems = <?= json_encode($items_js, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
	var apprTransfers = <?= json_encode(array_column(array_map(static function ($row) {
		return array_intersect_key($row, array_flip(['id', 'urutan', 'admin', 'bearer']));
	}, $transfers), null, 'id'), JSON_HEX_TAG); ?>;
	var idPengajuan = <?= (int) $header->id; ?>;
	var transferMode = Object.keys(apprTransfers).length > 0;

	// decision state: no_dokumen -> { decision: 'approved'|'rejected', alasan: '' }
	var decState = {};
	apprItems.forEach(function(it) {
		decState[it.no_dokumen] = {
			decision: 'approved',
			alasan: ''
		};
	});

	function apprFmt(n) {
		n = Math.round(Number(n) || 0);
		return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
	}

	function esc(s) {
		return $('<div>').text(s == null ? '' : s).html();
	}

	function rowHtml(it, rejected) {
		var html = '<tr>' +
			'<td><span class="rp-kategori-badge">' + esc(it.kategori) + '</span><br><b>' + esc(it.no_dokumen) + '</b></td>' +
			(transferMode ? '<td class="text-center">' + (apprTransfers[it.id_transfer] ? '<span class="rp-transfer-tag">Transfer ' + apprTransfers[it.id_transfer].urutan + '</span>' : '-') + '</td>' : '') +
			'<td>' + esc(it.request_by) + '</td>' +
			'<td>' + esc(it.keterangan_finance || it.keperluan) + (it.keterangan_finance ? '<div class="text-muted">Keterangan asli: ' + esc(it.keperluan) + '</div>' : '') + '</td>' +
			'<td class="text-right-imp">' + apprFmt(it.dpp) + '</td>' +
			'<td class="text-center">' + (it.flag_ppn ? apprFmt(it.nilai_ppn) : '-') + '</td>' +
			'<td class="text-center">' + (it.flag_pph23 ? apprFmt(it.nilai_pph) : '-') + '</td>' +
			'<td class="text-center">' + (it.flag_pph21 ? apprFmt(it.nilai_pph) : '-') + '</td>' +
			(transferMode ? '' : '<td class="text-center">' + (it.admin ? apprFmt(it.admin) : '-') + '</td>') +
			'<td class="text-right-imp">' + apprFmt(it.dibayarkan) + '</td>' +
			'<td style="white-space:nowrap">';
		if (it.print_url && it.print_url !== '') {
			html += '<a href="' + it.print_url + '" target="_blank" class="mini-btn" title="Print Dokumen" style="text-decoration:none;margin-right:6px;"><i class="fa fa-print"></i></a>';
		}
		if (rejected) {
			html += '<button type="button" class="mini-btn reject active" onclick="apprToggleReject(\'' + it.no_dokumen + '\')">Batal Reject</button>';
		} else {
			html += '<button type="button" class="mini-btn reject" onclick="apprToggleReject(\'' + it.no_dokumen + '\')">Reject</button>';
		}
		html += '</td></tr>';

		if (rejected) {
			html += '<tr class="reason-box"><td colspan="10" style="background:#FDF8F7;">' +
				'<textarea placeholder="Masukkan alasan reject untuk ' + esc(it.no_dokumen) + '..." ' +
				'oninput="decState[\'' + it.no_dokumen + '\'].alasan=this.value">' + esc(decState[it.no_dokumen].alasan) + '</textarea>' +
				'</td></tr>';
		}
		return html;
	}

	function apprRender() {
		var approveHtml = '',
			rejectHtml = '';
		var totalApprove = 0,
			cntA = 0,
			cntR = 0;

		apprItems.forEach(function(it) {
			if (decState[it.no_dokumen].decision === 'rejected') {
				rejectHtml += rowHtml(it, true);
				cntR++;
			} else {
				approveHtml += rowHtml(it, false);
				totalApprove += Number(it.dibayarkan) || 0;
				cntA++;
			}
		});

		$('#table_approve tbody').html(approveHtml);
		$('#table_reject tbody').html(rejectHtml);
		$('#cnt_approve').text(cntA);
		$('#cnt_reject').text(cntR);
		$('#d_count').text(apprItems.length);
		$('#d_total').text('Rp ' + apprFmt(totalApprove));

		$('#reject_label').toggle(cntR > 0);
		$('#reject_section').toggle(cntR > 0);
		$('[data-transfer]').each(function () {
			var row = $(this), transfer = apprTransfers[row.data('transfer')];
			var active = apprItems.filter(function (it) { return String(it.id_transfer) === String(transfer.id) && decState[it.no_dokumen].decision !== 'rejected'; });
			var net = active.reduce(function (sum, it) { return sum + Number(it.dibayarkan); }, 0);
			var admin = active.length ? Number(transfer.admin) : 0;
			row.find('[data-transfer-cancelled]').prop('hidden', active.length > 0);
			row.toggleClass('text-muted', !active.length);
			row.find('[data-edit-account]').toggle(active.length > 0);
			row.find('[data-transfer-docs]').html(apprItems.filter(function (it) { return String(it.id_transfer) === String(transfer.id); }).map(function (it) {
				return decState[it.no_dokumen].decision === 'rejected' ? '<s>' + esc(it.no_dokumen) + '</s>' : esc(it.no_dokumen);
			}).join('<br>'));
			row.find('[data-transfer-total]').text(active.length ? apprFmt(net) : '-');
			row.find('[data-transfer-net]').text(active.length ? apprFmt(net - (transfer.bearer === 'recipient' ? admin : 0)) : '-');
			row.find('[data-transfer-bank]').text(active.length ? apprFmt(net + (transfer.bearer === 'company' ? admin : 0)) : '-');
		});
	}

	var editRow = null;
	$(document).on('click', '[data-edit-account]', function () {
		editRow = $(this).closest('[data-transfer]');
		$('#rp-ea-bank').val(editRow.find('[data-account="bank"]').val());
		$('#rp-ea-no').val(editRow.find('[data-account="no_rek"]').val());
		$('#rp-ea-name').val(editRow.find('[data-account="nama"]').val());
		$('#rp-ea-error').text('');
		$('#rp-edit-account-title').text('Edit Rekening Tujuan \u00b7 Transfer ' + apprTransfers[editRow.data('transfer')].urutan);
		$('#rp-edit-account').modal('show');
	});
	$('#rp-ea-save').on('click', function () {
		var next = {bank: $('#rp-ea-bank').val().trim(), no_rek: $('#rp-ea-no').val().trim(), nama: $('#rp-ea-name').val().trim()};
		if (!next.bank || !next.nama || !/^[0-9][0-9.\- ]{4,}$/.test(next.no_rek)) {
			$('#rp-ea-error').text('Isi Bank, No. Rekening (angka) dan Nama Penerima.');
			return;
		}
		var changed = false, original = {};
		$.each(next, function (key, value) {
			var input = editRow.find('[data-account="' + key + '"]');
			original[key] = input.data('original') == null ? '' : String(input.data('original'));
			input.val(value);
			editRow.find('[data-show="' + key + '"]').text(value);
			changed = changed || value !== original[key];
		});
		editRow.find('[data-account-changed]').prop('hidden', !changed)
			.find('small').text(changed ? 'diubah approver, dari: ' + original.bank + ' ' + original.no_rek + ' a/n ' + original.nama : '');
		$('#rp-edit-account').modal('hide');
	});

	window.apprToggleReject = function(no) {
		decState[no].decision = (decState[no].decision === 'rejected') ? 'approved' : 'rejected';
		if (decState[no].decision !== 'rejected') decState[no].alasan = '';
		apprRender();
	};

	window.apprSaveDecision = function() {
		// validasi alasan reject non-kosong (client, server tetap validasi)
		var missing = [];
		apprItems.forEach(function(it) {
			var d = decState[it.no_dokumen];
			if (d.decision === 'rejected' && (!d.alasan || d.alasan.trim() === '')) {
				missing.push(it.no_dokumen);
			}
		});
		if (missing.length > 0) {
			alert('Isi alasan reject untuk: ' + missing.join(', '));
			return;
		}

		if (!confirm('Simpan keputusan untuk pengajuan ini?')) return;

		var decisions = apprItems.map(function(it) {
			return {
				no_dokumen: it.no_dokumen,
				decision: decState[it.no_dokumen].decision,
				alasan: decState[it.no_dokumen].alasan || ''
			};
		});

		var accounts = {};
		$('[data-transfer] [data-account]').each(function () {
			var id = $(this).closest('[data-transfer]').data('transfer');
			accounts[id] = accounts[id] || {};
			accounts[id][this.dataset.account] = this.value;
		});
		$.ajax({
			url: '<?= site_url("approval_request_payment/save_decision"); ?>',
			type: 'POST',
			data: {
				id_pengajuan: idPengajuan,
				decisions: decisions,
				transfer_accounts: accounts
			},
			dataType: 'json',
			success: function(res) {
				if (res.status == 1) {
					alert(res.msg || 'Keputusan tersimpan.');
					window.location = <?= json_encode(site_url('approval_request_payment')); ?>;
				} else {
					alert(res.msg || 'Gagal menyimpan.');
				}
			},
			error: function() {
				alert('Terjadi error. Silakan coba lagi.');
			}
		});
	};

	$(document).ready(function() {
		apprRender();
	});
</script>
