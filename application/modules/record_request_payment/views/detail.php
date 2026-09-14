<?php
$total_dibayarkan = 0;
foreach ($records as $r) {
	$total_dibayarkan += (float) $r->dibayarkan;
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

	.rec-table thead th {
		background: #3c8dbc;
		color: #fff;
		font-size: 11.5px;
		white-space: nowrap;
	}

	.rec-table tbody td {
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

	.text-right-imp {
		text-align: right;
	}
</style>

<div class="box">
	<div class="box-body">

		<a href="<?= site_url('record_request_payment'); ?>" class="btn btn-link" style="padding-left:0;">
			&larr; Kembali ke daftar record
		</a>

		<div class="detail-head">
			<div>
				<h3 style="margin:0 0 6px;">Pengajuan <?= html_escape($header->no_pengajuan); ?></h3>
				<div class="text-muted">
					Company: <?= html_escape($header->company_nama ? $header->company_nama : '-'); ?>
					&mdash; diajukan <?= !empty($header->submitted_at) ? date('d-M-Y H:i', strtotime($header->submitted_at)) : '-'; ?>,
					diputuskan <?= !empty($header->decided_at) ? date('d-M-Y H:i', strtotime($header->decided_at)) : '-'; ?>
				</div>
			</div>
			<div class="summary">
				<div class="text-right">
					<div class="lbl">JUMLAH ITEM</div>
					<div class="val"><?= count($records); ?></div>
				</div>
				<div class="text-right">
					<div class="lbl">TOTAL DIBAYARKAN</div>
					<div class="val">Rp <?= number_format($total_dibayarkan, 0, ',', '.'); ?></div>
				</div>
			</div>
		</div>

		<div style="overflow-x:auto;">
			<table class="table table-bordered rec-table" style="width:100%;">
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
						<th class="text-center" style="width:60px;">AKSI</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($records as $r): ?>
						<tr>
							<td>
								<span class="rp-kategori-badge"><?= html_escape($r->kategori); ?></span><br>
								<b><?= html_escape($r->no_dokumen); ?></b>
							</td>
							<td><?= html_escape($r->request_by); ?><?= $r->company_nama ? ' - ' . html_escape($r->company_nama) : ''; ?></td>
							<td><?= html_escape($r->keperluan); ?></td>
							<td class="text-right-imp"><?= number_format($r->dpp, 0, ',', '.'); ?></td>
							<td class="text-center"><?= ((int) $r->flag_ppn) ? number_format($r->nilai_ppn, 0, ',', '.') : '-'; ?></td>
							<td class="text-center"><?= ((int) $r->flag_pph23) ? number_format($r->nilai_pph, 0, ',', '.') : '-'; ?></td>
							<td class="text-center"><?= ((int) $r->flag_pph21) ? number_format($r->nilai_pph, 0, ',', '.') : '-'; ?></td>
							<td class="text-center"><?= ((int) $r->admin) ? number_format($r->admin, 0, ',', '.') : '-'; ?></td>
							<td class="text-right-imp"><?= number_format($r->dibayarkan, 0, ',', '.'); ?></td>
							<td class="text-center">
								<?php if (!empty($r->print_url)): ?>
									<a href="<?= $r->print_url; ?>" target="_blank" class="btn btn-sm btn-info" title="Print Dokumen"><i class="fa fa-print"></i></a>
								<?php else: ?>
									<span class="text-muted" title="Tidak ada dokumen print untuk tipe ini">-</span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

		<div style="display:flex;justify-content:space-between;align-items:center;margin-top:18px;padding-top:16px;border-top:1px solid #E1E5EA;">
			<div class="text-muted">Data ini adalah catatan final (snapshot), tidak bisa diubah lagi.</div>
			<a href="<?= site_url('record_request_payment/export_excel/' . $header->id); ?>" class="btn btn-primary">
				<i class="fa fa-file-excel-o"></i> Cetak ke Excel
			</a>
		</div>

	</div>
</div>
