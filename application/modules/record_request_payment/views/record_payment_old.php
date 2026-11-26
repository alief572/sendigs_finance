<?php // Tab Payment Lama: data form payment lama, tanpa pembanding pengajuan. ?>
<div class="table-responsive"><table class="table table-bordered table-striped">
	<thead><tr><th>NO. PAYMENT</th><th>NO. PENGAJUAN</th><th>COMPANY</th><th>REQUEST BY</th><th>DOKUMEN</th><th>BANK PEMBAYAR</th><th>DIBAYAR</th><th>BANK CHARGE</th><th>KETERANGAN PEMBAYARAN</th><th>ACTION</th></tr></thead>
	<tbody>
		<?php if (!$old_rows): ?><tr><td colspan="10" class="text-center text-muted">Tidak ada pembayaran yang cocok.</td></tr><?php endif; ?>
		<?php foreach ($old_rows as $row): ?>
			<tr>
				<td style="white-space:nowrap"><b><?= html_escape($row['payment']) ?></b><br><small class="text-muted"><?= $row['tgl'] ? date('d-M-Y', strtotime($row['tgl'])) : '-' ?></small></td>
				<td><?= html_escape($row['pengajuan'] ?: '-') ?></td>
				<td><?= html_escape($row['company'] ?: '-') ?></td>
				<td><?= html_escape($row['request_by'] ?: '-') ?></td>
				<td style="white-space:nowrap"><?= implode('<br>', array_map('html_escape', $row['docs'])) ?></td>
				<td><?= html_escape($row['bank'] ?: '-') ?></td>
				<td class="text-right" style="white-space:nowrap">Rp <?= number_format($row['paid'], fmod($row['paid'], 1) ? 2 : 0, ',', '.') ?></td>
				<td class="text-right" style="white-space:nowrap">Rp <?= number_format($row['charge'], fmod($row['charge'], 1) ? 2 : 0, ',', '.') ?></td>
				<td><?= html_escape($row['note'] ?: '-') ?></td>
				<td class="text-center" style="white-space:nowrap">
					<a class="mini-btn view" href="<?= site_url('pembayaran_material/view_payment_new/' . rawurlencode($row['payment'])) ?>" target="_blank" rel="noopener">View</a>
					<?php foreach ($row['proofs'] as $file): ?>
						<a class="mini-btn" href="<?= html_escape($file['url']) ?>" target="_blank" rel="noopener" title="Bukti bayar <?= html_escape($file['name']) ?>" aria-label="Bukti bayar <?= html_escape($file['name']) ?>"><i class="fa fa-download"></i></a>
					<?php endforeach; ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table></div>
<?php if ($old_pages > 1): ?>
	<nav aria-label="Halaman Payment Lama" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
		<div class="text-muted">Halaman <?= (int) $old_page ?> dari <?= (int) $old_pages ?> &middot; <?= (int) $old_count ?> payment</div>
		<ul class="pagination pagination-sm" style="margin:0">
			<?php foreach (['&laquo;' => 1, '&lsaquo;' => $old_page - 1] as $label => $target): ?>
				<li class="<?= $old_page <= 1 ? 'disabled' : '' ?>"><a href="<?= html_escape($url . '?' . http_build_query($params + ['page' => max(1, $target)])) ?>"><?= $label ?></a></li>
			<?php endforeach; ?>
			<?php for ($i = max(1, $old_page - 2); $i <= min($old_pages, $old_page + 2); $i++): ?>
				<li class="<?= $i === $old_page ? 'active' : '' ?>"><a href="<?= html_escape($url . '?' . http_build_query($params + ['page' => $i])) ?>"><?= $i ?></a></li>
			<?php endfor; ?>
			<?php foreach (['&rsaquo;' => $old_page + 1, '&raquo;' => $old_pages] as $label => $target): ?>
				<li class="<?= $old_page >= $old_pages ? 'disabled' : '' ?>"><a href="<?= html_escape($url . '?' . http_build_query($params + ['page' => min($old_pages, $target)])) ?>"><?= $label ?></a></li>
			<?php endforeach; ?>
		</ul>
	</nav>
<?php endif; ?>
