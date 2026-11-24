<?php
$rp_tutorial_page = 'history';
include dirname(__DIR__, 2) . '/request_payment/views/transfer_tutorial.php';
$params = array_filter(['company' => $company, 'q' => $search, 'tab' => $tab], static function ($v) { return $v !== null && $v !== ''; });
$url = site_url('record_request_payment/record_payment');
$rp = static function ($cents) { return 'Rp ' . number_format($cents / 100, 2, ',', '.'); };
?>
<div class="box box-primary">
	<div class="box-header">
		<h3 class="box-title">Record Payment</h3>
		<a class="btn btn-success btn-sm pull-right" href="<?= html_escape($url . '?' . http_build_query($params + ['export' => '1'])) ?>"><i class="fa fa-file-excel-o"></i> Export Excel</a>
	</div>
	<div class="box-body">
		<ul class="nav nav-tabs">
			<?php foreach (['' => ['Semua', $counts['all']], 'diff' => ['Ada perbedaan', $counts['diff']], 'same' => ['Sesuai pengajuan', $counts['all'] - $counts['diff']]] as $key => $label): ?>
				<li class="<?= $key === ($tab ?? '') ? 'active' : '' ?>"><a href="<?= html_escape($url . '?' . http_build_query(['tab' => $key] + $params)) ?>"><?= html_escape($label[0]) ?> <span class="badge"><?= $label[1] ?></span></a></li>
			<?php endforeach; ?>
		</ul>
		<form class="form-inline" style="margin:12px 0" action="<?= html_escape($url) ?>" method="get">
			<input type="hidden" name="tab" value="<?= html_escape($tab ?? '') ?>">
			<label>Company <select class="form-control input-sm" name="company">
				<option value="">Semua Company</option>
				<?php foreach (['7' => 'STM', '3' => 'Sustain', '4' => 'Vuca'] as $id => $name): ?>
					<option value="<?= $id ?>"<?= (string) $id === $company ? ' selected' : '' ?>><?= $name ?></option>
				<?php endforeach; ?>
			</select></label>
			<label>Cari <input class="form-control input-sm" name="q" value="<?= html_escape($search ?? '') ?>" placeholder="BK, PGJ, dokumen, keterangan"></label>
			<button class="btn btn-primary btn-sm" type="submit">Filter</button>
		</form>
		<div class="table-responsive"><table class="table table-bordered table-striped">
			<thead><tr class="bg-blue"><th>NO. PAYMENT</th><th>NO. PENGAJUAN / TRANSFER</th><th>TRANSFER KE</th><th>DOKUMEN</th><th>REQUEST PAYMENT</th><th>PAYMENT</th><th>PERBEDAAN</th><th>KETERANGAN PEMBAYARAN</th><th>AKSI</th></tr></thead>
			<tbody>
				<?php if (!$entries): ?><tr><td colspan="9" class="text-center text-muted">Tidak ada pembayaran yang cocok.</td></tr><?php endif; ?>
				<?php foreach ($entries as $row): ?>
					<tr>
						<td><b><?= html_escape($row['id_payment']) ?></b><br><small><?= html_escape($row['tgl_bayar']) ?></small></td>
						<td><?= html_escape($row['no_pengajuan']) ?><br>Transfer <?= (int) $row['urutan'] ?> &middot; <?= html_escape($row['company_nama']) ?></td>
						<td><b><?= html_escape($row['nama']) ?></b><br><?= html_escape($row['bank'] . ' - ' . $row['no_rek']) ?></td>
						<td><?= implode('<br>', array_map('html_escape', array_column($row['docs'], 'no_dokumen'))) ?></td>
						<td><small>Admin <?= $rp($row['admin']) ?> (<?= html_escape($row['plannedBearer']) ?>)<br>Transfer <?= $rp($row['planned']['transfer']) ?><br>Keluar bank <b><?= $rp($row['planned']['bank']) ?></b></small></td>
						<td><small>Admin <?= html_escape($row['actualBearer']) ?><br>Bank Charge <?= $rp($row['charge']) ?><br>Transfer <?= $rp($row['received']) ?><br>Keluar bank <b><?= $rp($row['actualBank']) ?></b></small></td>
						<td><?= $row['differences'] ? implode('<br>', array_map('html_escape', $row['differences'])) : '<span class="text-success">Sesuai pengajuan</span>' ?></td>
						<td><?= html_escape($row['keterangan_pembayaran'] ?: '-') ?></td>
						<td><a class="btn btn-default btn-xs" href="<?= site_url('record_request_payment/payment_detail/' . (int) $row['id']) ?>">Lihat</a></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table></div>
	</div>
</div>
