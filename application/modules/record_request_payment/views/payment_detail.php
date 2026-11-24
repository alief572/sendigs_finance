<?php $rp = static function ($cents) { return 'Rp ' . number_format($cents / 100, 2, ',', '.'); }; ?>
<div class="box box-primary">
	<div class="box-header"><h3 class="box-title">Payment <?= html_escape($group['id_payment']) ?></h3></div>
	<div class="box-body">
		<p><?= html_escape($group['no_pengajuan']) ?> / Transfer <?= (int) $group['urutan'] ?> &middot; <?= html_escape($group['company_nama']) ?></p>
		<p><b>Transfer ke</b> <?= html_escape($group['bank'] . ' ' . $group['no_rek'] . ' a/n ' . $group['nama']) ?></p>
		<p><b>Dokumen</b> <?= html_escape(implode(', ', array_column($group['docs'], 'no_dokumen'))) ?></p>
		<table class="table table-bordered">
			<thead><tr class="bg-blue"><th>Komponen</th><th>Request Payment</th><th>Payment</th><th>Selisih</th></tr></thead>
			<tbody>
				<tr><th>Penanggung admin</th><td><?= html_escape($plannedBearer) ?></td><td><?= html_escape($actualBearer) ?></td><td><?= $plannedBearer === $actualBearer ? '-' : 'Berbeda' ?></td></tr>
				<?php foreach (['Admin / Bank Charge' => [$admin, $charge], 'Transfer diterima penerima' => [$planned['transfer'], $received], 'Total keluar bank' => [$planned['bank'], $actualBank]] as $label => $values): ?>
					<tr><th><?= html_escape($label) ?></th><td><?= $rp($values[0]) ?></td><td><?= $rp($values[1]) ?></td><td><?= $rp($values[1] - $values[0]) ?></td></tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<p><b>Keterangan Pembayaran</b> <?= html_escape($group['keterangan_pembayaran'] ?: '-') ?></p>
		<h4>Bukti Bayar</h4>
		<ul><?php foreach ($proof as $file): ?><li><a href="<?= site_url('record_request_payment/transfer_file/' . (int) $file['id']) ?>" target="_blank" rel="noopener"><?= html_escape($file['nama']) ?></a></li><?php endforeach; ?></ul>
		<a class="btn btn-default" href="<?= site_url('record_request_payment/record_payment') ?>">Kembali</a>
	</div>
</div>
