<?php
$rp = static function ($cents) { return 'Rp ' . number_format($cents / 100, $cents % 100 ? 2 : 0, ',', '.'); };
$paidAmount = static function ($value) { return number_format((float) $value, round((float) $value * 100) % 100 ? 2 : 0, ',', '.'); };
$num = static function ($value) { return (float) $value ? number_format((float) $value, 2, '.', ',') : '-'; };
$compare = [
	['Penanggung admin', $plannedBearer, $actualBearer, null],
	['Bank charge (dipotong bank)', $rp($admin), $rp($charge), $charge - $admin],
	['Transfer diterima penerima', $rp($planned['transfer']), $rp($received), $received - $planned['transfer']],
	['Total keluar bank', $rp($planned['bank']), $rp($actualBank), $actualBank - $planned['bank']],
];
$debit = array_sum(array_map('floatval', array_column($journal, 'debit')));
$credit = array_sum(array_map('floatval', array_column($journal, 'kredit')));
?>
<?php $modal = !empty($modal); $paidAt = $group['tgl_bayar'] ? date('d-M-Y', strtotime($group['tgl_bayar'])) : '-'; ?>
<?php if (!$modal): ?>
<div class="box box-primary">
	<div class="box-header"><h3 class="box-title">Record Payment <?= html_escape($group['id_payment']) ?></h3></div>
	<div class="box-body">
<?php endif; ?>
	<style>
		#rp-detail table > thead > tr > th { text-align:center; vertical-align:top }
		#rp-detail table > thead > tr > th.text-right { text-align:right }
		#rp-detail table > tbody > tr > td { vertical-align:top }
		#rp-detail .rp-journal-head th { background:#3c8dbc; color:#fff; border-color:#5aa0c8 }
	</style>
	<div id="rp-detail" style="font-size:12.5px">
		<table class="table table-bordered" style="margin-bottom:8px">
			<tbody>
				<tr><td style="width:170px">No. Pengajuan</td><td><?= html_escape($group['no_pengajuan']) ?> / Transfer <?= (int) $group['urutan'] ?> &middot; <?= html_escape($group['company_nama']) ?></td></tr>
				<tr><td>Tgl Bayar</td><td><?= html_escape($paidAt) ?><?= $group['nm_coa_bank'] ? ' &middot; dari ' . html_escape($group['nm_coa_bank']) : '' ?></td></tr>
				<tr><td>Transfer ke</td><td><?= html_escape($group['bank'] . ' ' . $group['no_rek'] . ' a/n ' . $group['nama']) ?></td></tr>
				<tr><td>Keterangan Pembayaran</td><td><?= html_escape($group['keterangan_pembayaran'] ?: '-') ?></td></tr>
				<tr><td>Bukti bayar</td><td><?php if (!$proof): ?>-<?php endif; ?>
					<?php foreach ($proof as $i => $file): ?><?= $i ? ', ' : '' ?><a href="<?= site_url('record_request_payment/transfer_file/' . (int) $file['id']) ?>" target="_blank" rel="noopener"><?= html_escape($file['nama']) ?></a><?php endforeach; ?></td></tr>
			</tbody>
		</table>
		<table class="table table-bordered" style="margin-bottom:8px">
			<thead><tr><th></th><th>Request Payment</th><th>Payment (aktual)</th><th class="text-right">Selisih</th></tr></thead>
			<tbody>
				<?php foreach ($compare as [$label, $plan, $paid, $diff]): ?>
					<tr<?= $plan !== $paid ? ' class="warning"' : '' ?>><td><?= html_escape($label) ?></td><td><?= html_escape($plan) ?></td><td><?= html_escape($paid) ?></td>
						<td class="text-right"><?= $diff === null || $plan === $paid ? '-' : '<span class="text-warning">' . ($diff > 0 ? '+' : '') . html_escape($rp($diff)) . '</span>' ?></td></tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<table class="table table-bordered" style="margin:0">
			<thead><tr><th>Dokumen</th><th>Kategori</th><th>Request By</th><th class="text-right">Dibayarkan</th></tr></thead>
			<tbody>
				<?php foreach ($group['docs'] as $doc): ?>
					<tr><td><?= html_escape($doc['no_dokumen']) ?></td><td><?= html_escape(Rp_transfer_record::category($doc['kategori'])) ?></td>
						<td><?= html_escape($doc['request_by']) ?></td><td class="text-right"><?= $paidAmount($doc['dibayarkan']) ?></td></tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<p class="text-muted" style="margin:8px 0 0"><small><?= $differences ? 'Baris kuning = berbeda dari Request Payment.' : 'Payment sesuai dengan Request Payment.' ?></small></p>
		<p style="margin:10px 0 4px"><b>Preview Jurnal</b> <small class="text-muted">No Transaksi <?= html_escape($group['id_payment']) ?> &middot; <?= html_escape($paidAt) ?></small></p>
		<div class="table-responsive"><table class="table table-bordered" style="font-size:12px;margin:0">
			<thead><tr class="rp-journal-head"><th>Company</th><th>COA</th><th>Nama Account</th><th>Keterangan</th><th class="text-right">Debit</th><th class="text-right">Kredit</th></tr></thead>
			<tbody>
				<?php if (!$journal): ?><tr><td colspan="6" class="text-center text-muted">Jurnal payment belum tersedia.</td></tr><?php endif; ?>
				<?php foreach ($journal as $row): ?>
					<tr<?= $row['coa'] === $group['coa_bank'] ? ' style="background:#f4f8fb"' : '' ?>><td><?= html_escape($row['nm_company']) ?></td><td><?= html_escape($row['coa']) ?></td>
						<td><?= html_escape($row['nm_coa']) ?></td><td><?= html_escape($row['keterangan']) ?></td>
						<td class="text-right"><?= $num($row['debit']) ?></td><td class="text-right"><?= $num($row['kredit']) ?></td></tr>
				<?php endforeach; ?>
			</tbody>
			<tfoot><tr><th colspan="4" class="text-right">Balancing</th><th class="text-right"><?= number_format($debit, 2, '.', ',') ?></th><th class="text-right"><?= number_format($credit, 2, '.', ',') ?></th></tr></tfoot>
		</table></div>
	</div>
<?php if (!$modal): ?>
	</div>
	<div class="box-footer">
		<a class="btn btn-default" href="<?= site_url('record_request_payment/record_payment') ?>">Kembali</a>
		<?php if ($journal): ?><a class="btn btn-primary pull-right" href="<?= site_url('record_request_payment/print_voucher/' . (int) $group['id']) ?>" target="_blank" rel="noopener" title="Print Bukti Bank Keluar (voucher) ke PDF"><i class="fa fa-print"></i> Voucher</a><?php endif; ?>
	</div>
</div>
<?php endif; ?>
