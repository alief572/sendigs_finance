<?php
$ENABLE_MANAGE = has_permission('Request_Payment.Manage');

// build items JSON for JS
$items_js = [];
foreach ($items as $it) {
	$items_js[] = [
		'id'         => $it->id,
		'no_dokumen' => $it->no_dokumen,
		'kategori'   => $it->kategori,
		'request_by' => $it->request_by,
		'keperluan'  => $it->keperluan,
		'dpp'        => (float) $it->dpp,
		'nilai_ppn'  => (float) $it->nilai_ppn,
		'nilai_pph'  => (float) $it->nilai_pph,
		'flag_ppn'   => (int) $it->flag_ppn,
		'flag_pph23' => (int) $it->flag_pph23,
		'flag_pph21' => (int) $it->flag_pph21,
		'admin'      => (int) $it->admin,
		'dibayarkan' => (float) $it->dibayarkan,
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
				<div class="text-right">
					<div class="lbl">TOTAL DIBAYARKAN</div>
					<div class="val" id="d_total">Rp 0</div>
				</div>
			</div>
		</div>

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
						<th>REQUEST BY</th>
						<th>KEPERLUAN</th>
						<th class="text-right-imp">DPP</th>
						<th class="text-center">PPN</th>
						<th class="text-center">PPH23</th>
						<th class="text-center">PPH21</th>
						<th class="text-center">ADMIN</th>
						<th class="text-right-imp">DIBAYARKAN</th>
						<th style="width:90px;">AKSI</th>
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
						<th>REQUEST BY</th>
						<th>KEPERLUAN</th>
						<th class="text-right-imp">DPP</th>
						<th class="text-center">PPN</th>
						<th class="text-center">PPH23</th>
						<th class="text-center">PPH21</th>
						<th class="text-center">ADMIN</th>
						<th class="text-right-imp">DIBAYARKAN</th>
						<th style="width:90px;">AKSI</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>

		<div style="display:flex;justify-content:space-between;align-items:center;margin-top:18px;padding-top:16px;border-top:1px solid #E1E5EA;">
			<div class="text-muted">Item yang di-reject akan kembali ke Request Payment dengan alasan reject.</div>
			<?php if ($ENABLE_MANAGE): ?>
				<button type="button" class="btn btn-primary" onclick="apprSaveDecision();">
					<i class="fa fa-save"></i> Simpan Keputusan
				</button>
			<?php endif; ?>
		</div>

	</div>
</div>

<script>
	var apprItems = <?= json_encode($items_js); ?>;
	var idPengajuan = <?= (int) $header->id; ?>;

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
			'<td>' + esc(it.request_by) + '</td>' +
			'<td>' + esc(it.keperluan) + '</td>' +
			'<td class="text-right-imp">' + apprFmt(it.dpp) + '</td>' +
			'<td class="text-center">' + (it.flag_ppn ? apprFmt(it.nilai_ppn) : '-') + '</td>' +
			'<td class="text-center">' + (it.flag_pph23 ? apprFmt(it.nilai_pph) : '-') + '</td>' +
			'<td class="text-center">' + (it.flag_pph21 ? apprFmt(it.nilai_pph) : '-') + '</td>' +
			'<td class="text-center">' + (it.admin ? apprFmt(it.admin) : '-') + '</td>' +
			'<td class="text-right-imp">' + apprFmt(it.dibayarkan) + '</td>' +
			'<td>';
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
	}

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

		$.ajax({
			url: '<?= site_url("approval_request_payment/save_decision"); ?>',
			type: 'POST',
			data: {
				id_pengajuan: idPengajuan,
				decisions: decisions
			},
			dataType: 'json',
			success: function(res) {
				if (res.status == 1) {
					alert(res.msg || 'Keputusan tersimpan.');
					window.location = '<?= site_url("approval_request_payment"); ?>';
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
