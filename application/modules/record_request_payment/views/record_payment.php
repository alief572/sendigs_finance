<?php
$rp_tutorial_page = 'history';
include dirname(__DIR__, 2) . '/request_payment/views/transfer_tutorial.php';
$params = array_filter(['company' => $company, 'q' => $search, 'tab' => $tab], static function ($v) { return $v !== null && $v !== ''; });
$url = site_url('record_request_payment/record_payment');
$num = static function ($cents) { return number_format($cents / 100, $cents % 100 ? 2 : 0, ',', '.'); };
$rp = static function ($cents) use ($num) { return 'Rp ' . $num($cents); };
?>
<style>
	#rec-pay .breadcrumb { background:none; padding:0; margin-bottom:5px; font-size:12px }
	#rec-pay .nav-tabs > li.active > a { border-top:3px solid #3c8dbc; font-weight:600 }
	#rec-pay .rp-count { display:inline-block; background:#777; color:#fff; font-size:11px; font-weight:700; padding:1px 7px; border-radius:10px }
	#rec-pay .rp-count.diff { background:#b9690a }
	#rec-pay .rp-toolbar { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin:14px 0 12px }
	#rec-pay .rp-toolbar form { display:flex; flex-wrap:wrap; gap:8px; margin:0 }
	#rec-pay .rp-toolbar input[type=text] { width:300px; max-width:100% }
	#rec-pay thead th { background:#3c8dbc; color:#fff; font-size:12px; text-align:center; vertical-align:middle; white-space:nowrap }
	#rec-pay tbody td { font-size:12.5px; vertical-align:top }
	#rec-pay .rp-trf-tag { display:inline-block; background:#605ca8; color:#fff; font-size:10.5px; font-weight:700; padding:2px 8px; border-radius:10px; white-space:nowrap }
	#rec-pay .rp-same { display:inline-block; background:#e1f3ea; color:#1f7a4d; font-size:11.5px; font-weight:700; padding:4px 10px; border-radius:10px }
	#rec-pay .rp-diff { margin-bottom:3px }
	#rec-pay .rp-delta { color:#a35f00 }
	/* Garis oranye = payment berbeda dari pengajuan. */
	#rec-pay tbody tr.rp-has-diff > td:first-child { box-shadow:inset 3px 0 0 #e08e0b }
	#rec-pay .mini-btn { display:inline-block; margin:2px; padding:4px 10px; border:1px solid #c9d3dc; border-radius:4px; background:#fff; color:#3a4656; font-size:11.5px; font-weight:600 }
	#rec-pay .mini-btn.view { color:#3d6690; border-color:#bfd6ea }
</style>
<div class="box" id="rec-pay">
	<div class="box-header with-border">
		<ol class="breadcrumb">
			<li><a href="javascript:void(0);">Finance</a></li>
			<li class="active">Record Payment</li>
		</ol>
		<h3 class="box-title" style="font-size:22px;font-weight:600">Record Payment</h3>
	</div>
	<div class="box-body">
		<ul class="nav nav-tabs">
			<?php foreach (['' => ['Semua', $counts['all']], 'diff' => ['Ada perbedaan', $counts['diff']], 'same' => ['Sesuai pengajuan', $counts['all'] - $counts['diff']]] as $key => $label): ?>
				<li class="<?= $key === ($tab ?? '') ? 'active' : '' ?>"><a href="<?= html_escape($url . '?' . http_build_query(['tab' => $key] + $params)) ?>"><?= html_escape($label[0]) ?> <span class="rp-count<?= $key === 'diff' ? ' diff' : '' ?>"><?= $label[1] ?></span></a></li>
			<?php endforeach; ?>
		</ul>
		<div class="rp-toolbar">
			<div class="text-muted">Payment dari Request Payment beserta perbedaannya dengan pengajuan.</div>
			<form action="<?= html_escape($url) ?>" method="get">
				<input type="hidden" name="tab" value="<?= html_escape($tab ?? '') ?>">
				<select class="form-control input-sm" name="company" aria-label="Company" onchange="this.form.submit()" style="width:auto">
					<option value="">Semua Company</option>
					<?php foreach (['7' => 'STM', '3' => 'Sustain', '4' => 'Vuca'] as $id => $name): ?>
						<option value="<?= $id ?>"<?= (string) $id === $company ? ' selected' : '' ?>><?= $name ?></option>
					<?php endforeach; ?>
				</select>
				<input type="text" class="form-control input-sm" name="q" aria-label="Cari" value="<?= html_escape($search ?? '') ?>" placeholder="Cari no. payment, pengajuan, dokumen, penerima...">
				<a class="btn btn-success btn-sm" href="<?= html_escape($url . '?' . http_build_query($params + ['export' => '1'])) ?>"><i class="fa fa-file-excel-o"></i> Export Excel</a>
			</form>
		</div>
		<div class="table-responsive"><table class="table table-bordered table-striped">
			<thead><tr><th>NO. PAYMENT</th><th>NO. PENGAJUAN</th><th>PENERIMA</th><th>DOKUMEN</th><th style="width:180px">REQUEST PAYMENT</th><th style="width:180px">PAYMENT</th><th style="width:210px">PERBEDAAN</th><th>KETERANGAN PEMBAYARAN</th><th>ACTION</th></tr></thead>
			<tbody>
				<?php if (!$entries): ?><tr><td colspan="9" class="text-center text-muted">Tidak ada pembayaran yang cocok.</td></tr><?php endif; ?>
				<?php foreach ($entries as $row): ?>
					<tr<?= $row['differences'] ? ' class="rp-has-diff"' : '' ?>>
						<td style="white-space:nowrap"><b><?= html_escape($row['id_payment']) ?></b><br><small class="text-muted"><?= $row['tgl_bayar'] ? date('d-M-Y', strtotime($row['tgl_bayar'])) : '-' ?></small></td>
						<td><?= html_escape($row['no_pengajuan']) ?><br><span class="rp-trf-tag">Transfer <?= (int) $row['urutan'] ?></span> <small class="text-muted"><?= html_escape($row['company_nama']) ?></small></td>
						<td><b><?= html_escape($row['nama']) ?></b><br><small><?= html_escape($row['bank'] . ' ' . $row['no_rek'] . ' a/n ' . $row['nama']) ?></small></td>
						<td style="white-space:nowrap"><?= implode('<br>', array_map('html_escape', array_column($row['docs'], 'no_dokumen'))) ?></td>
						<td>Admin: <?= $row['admin'] ? $rp($row['admin']) . '<br><span class="text-muted">' . html_escape($row['plannedBearer']) . '</span>' : 'tanpa admin' ?><br>Transfer: <?= $rp($row['planned']['transfer']) ?><br>Keluar bank: <b><?= $rp($row['planned']['bank']) ?></b></td>
						<td>Admin: <?= $row['plannedBearer'] !== $row['actualBearer'] ? '<span class="text-warning">' . html_escape($row['actualBearer']) . '</span>' : ($row['admin'] ? 'sesuai pengajuan' : 'tanpa admin') ?><br>Bank charge: <?= $rp($row['charge']) ?><br>Transfer: <?= $rp($row['received']) ?><br>Keluar bank: <b><?= $rp($row['actualBank']) ?></b></td>
						<td><?php if (!$row['differences']): ?><span class="rp-same">Sesuai pengajuan</span><?php endif; ?>
							<?php foreach ($row['diffRows'] as [$label, $from, $to]): ?>
								<div class="rp-diff"><b><?= html_escape($label) ?></b>: <?= is_int($from) ? $num($from) . ' &rarr; ' . $num($to) . ' <span class="rp-delta">(' . ($to > $from ? '+' : '') . $num($to - $from) . ')</span>' : html_escape($from) . ' &rarr; ' . html_escape($to) ?></div>
							<?php endforeach; ?></td>
						<td><?= html_escape($row['keterangan_pembayaran'] ?: '-') ?></td>
						<td class="text-center" style="white-space:nowrap"><a class="mini-btn view" data-payment="<?= html_escape($row['id_payment']) ?>" href="<?= site_url('record_request_payment/payment_detail/' . (int) $row['id']) ?>">View</a><br>
							<a class="mini-btn" href="<?= site_url('record_request_payment/print_voucher/' . (int) $row['id']) ?>" target="_blank" rel="noopener" title="Print Bukti Bank Keluar (voucher) ke PDF"><i class="fa fa-print"></i> Voucher</a></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table></div>
	</div>
</div>
<style>
	/* Header dan tombol Tutup tetap terlihat; hanya isi modal yang digulir. */
	#rp-view-body { max-height:calc(100vh - 200px); overflow-y:auto }
	@media (min-width:992px) { #rp-view-modal .modal-dialog { width:960px } }
</style>
<div class="modal fade" id="rp-view-modal" tabindex="-1" role="dialog" aria-labelledby="rp-view-title">
	<div class="modal-dialog modal-lg" role="document"><div class="modal-content">
		<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title" id="rp-view-title">Record Payment</h4></div>
		<div class="modal-body" id="rp-view-body" aria-live="polite"></div>
		<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button></div>
	</div></div>
</div>
<script>
	(function () {
		var request = null;
		// Tautan View tetap punya href: klik biasa membuka modal, buka di tab baru tetap halaman penuh.
		$('#rec-pay').on('click', 'a.view', function (event) {
			if (event.ctrlKey || event.metaKey || event.shiftKey) return;
			event.preventDefault();
			var body = $('#rp-view-body');
			$('#rp-view-title').text('Record Payment ' + $(this).attr('data-payment'));
			body.html('<p class="text-muted"><i class="fa fa-spinner fa-spin"></i> Memuat detail payment...</p>');
			$('#rp-view-modal').modal('show');
			if (request) request.abort();
			var fail = function () { body.html('<p class="text-danger">Detail payment gagal dimuat. Muat ulang halaman lalu coba lagi.</p>'); };
			request = $.get($(this).attr('href'), {modal: 1}).done(function (html) {
				// Sesi habis mengembalikan halaman login, bukan potongan detail.
				if (String(html).indexOf('id="rp-detail"') === -1) return fail();
				body.html(html);
			}).fail(function (xhr, status) { if (status !== 'abort') fail(); });
		});
	})();
</script>
