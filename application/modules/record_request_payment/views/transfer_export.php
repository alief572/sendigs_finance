<?php
$batches = [];
foreach ($groups as $group) {
	$id = $group['id_pengajuan'];
	$batches[$id] = $batches[$id] ?? ['no' => $group['no_pengajuan'], 'company' => $group['company_nama'], 'count' => 0, 'amount' => 0];
	$batches[$id]['count']++;
	$batches[$id]['amount'] += Rp_transfer_payment::planned($group)['bank'];
}
?>
<div class="box box-primary">
	<div class="box-header"><h3 class="box-title">Export Excel Beberapa PGJ</h3></div>
	<div class="box-body">
		<form class="form-inline" method="get" action="<?= site_url('record_request_payment/export_transfer_batches') ?>">
			<label class="form-group">Tanggal diputuskan dari <input type="date" name="from" class="form-control input-sm" required value="<?= html_escape($filter['from']) ?>"></label>
			<label class="form-group">s/d <input type="date" name="to" class="form-control input-sm" required value="<?= html_escape($filter['to']) ?>"></label>
			<label class="form-group">Status Pembayaran <select name="status" class="form-control input-sm">
				<?php foreach (['unpaid' => 'Belum dibayar', 'paid' => 'Sudah dibayar', '' => 'Semua'] as $key => $name): ?>
					<option value="<?= $key ?>"<?= $key === $filter['status'] ? ' selected' : '' ?>><?= $name ?></option>
				<?php endforeach; ?>
			</select></label>
			<label class="form-group">Kategori <select name="category" class="form-control input-sm">
				<option value="">Semua kategori</option>
				<?php foreach (Rp_transfer_record::CATEGORIES as $category): ?>
					<option value="<?= html_escape($category) ?>"<?= $category === $filter['category'] ? ' selected' : '' ?>><?= html_escape($category) ?></option>
				<?php endforeach; ?>
			</select></label>
			<button class="btn btn-primary btn-sm" type="submit">Tampilkan</button>
		</form>
		<hr>
		<form method="post" action="<?= html_escape(site_url('record_request_payment/export_transfer_batches') . '?' . http_build_query($filter)) ?>" id="rp-export-pick">
			<div class="table-responsive"><table class="table table-bordered table-striped">
				<thead><tr class="bg-blue"><th><input type="checkbox" aria-label="Pilih semua PGJ" id="rp-export-all" checked></th><th>NO. PENGAJUAN</th><th>COMPANY</th><th>JUMLAH TRANSFER</th><th>TOTAL KELUAR BANK</th></tr></thead>
				<tbody>
					<?php if (!$batches): ?><tr><td colspan="5" class="text-center text-muted">Tidak ada pengajuan yang cocok.</td></tr><?php endif; ?>
					<?php foreach ($batches as $id => $batch): ?>
						<tr>
							<td><input type="checkbox" name="batches[]" checked aria-label="Pilih <?= html_escape($batch['no']) ?>" value="<?= (int) $id ?>" data-count="<?= $batch['count'] ?>" data-total="<?= $batch['amount'] ?>"></td>
							<td><?= html_escape($batch['no']) ?></td><td><?= html_escape($batch['company']) ?></td>
							<td><?= $batch['count'] ?></td><td>Rp <?= number_format($batch['amount'] / 100, 2, ',', '.') ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table></div>
			<div class="well well-sm" id="rp-export-summary" role="status"></div>
			<a class="btn btn-default" href="<?= site_url('record_request_payment') ?>">Kembali</a>
			<button class="btn btn-success pull-right" type="submit"><i class="fa fa-file-excel-o"></i> Export Excel</button>
		</form>
	</div>
</div>
<script>
(() => {
	const form = document.getElementById('rp-export-pick'), all = document.getElementById('rp-export-all');
	const picks = Array.from(form.querySelectorAll('[name="batches[]"]'));
	const update = () => {
		const checked = picks.filter(el => el.checked);
		const count = checked.reduce((sum, el) => sum + Number(el.dataset.count), 0);
		const total = checked.reduce((sum, el) => sum + Number(el.dataset.total), 0);
		document.getElementById('rp-export-summary').textContent = checked.length + ' PGJ · ' + count + ' transfer · Rp ' + (total / 100).toLocaleString('id-ID');
		form.querySelector('[type=submit]').disabled = !checked.length;
		all.checked = picks.length > 0 && checked.length === picks.length;
		all.indeterminate = checked.length > 0 && !all.checked;
	};
	all.addEventListener('change', () => { picks.forEach(el => { el.checked = all.checked; }); });
	form.addEventListener('change', update);
	update();
})();
</script>
