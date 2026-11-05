<?php
/* Approval Pengajuan Periodik: tab Menunggu approval / Record approval (pApprList di specs/input/Periodik.html).
   Variabel: $batches, $names, $can_decide. */
$e   = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$fmt = function ($n) { return number_format((float) $n, 0, ',', '.'); };
$IL  = array('W' => 'Waiting Approval', 'A' => 'Approved', 'R' => 'Rejected');
$dname = function ($b) use ($names) { return isset($names[$b['dept']]) ? $names[$b['dept']] : $b['dept']; };

$pend = $rec = array();
foreach ($batches as $b) {
	if (in_array('W', array_column($b['items'], 'status'), true)) { $pend[] = $b; }
	foreach ($b['items'] as $i) {
		if ($i['status'] !== 'W') { $rec[] = array($b, $i); }
	}
}
usort($rec, function ($x, $y) { return strcmp((string) $y[1]['at'], (string) $x[1]['at']); });
?>
<link rel="stylesheet" href="<?= base_url('assets/css/periodik_ui.css') ?>">
<div class="pjx">
<div class="panel hl">
	<div class="tabs" role="tablist">
		<button type="button" role="tab" class="on" data-atab="wait">Menunggu approval (<?= count($pend) ?>)</button>
		<button type="button" role="tab" data-atab="rec">Record approval</button>
	</div>
	<div class="scroll" data-apane="wait">
		<?php if (!$pend) : ?><p>Tidak ada pengajuan yang menunggu approval. Pengajuan baru muncul di sini setelah disimpan dari menu Pengajuan Pembayaran Periodik.</p><?php else : ?>
		<table>
			<thead><tr><th>#</th><th>Departement</th><th>Nomor</th><th class="num">Jumlah item</th><th class="num">Nominal</th><th>Tanggal</th><th>Action</th></tr></thead>
			<tbody>
			<?php foreach ($pend as $ix => $b) : ?>
				<tr>
					<td><?= $ix + 1 ?></td>
					<td><?= $e($dname($b)) ?></td>
					<td class="no"><?= $e($b['no']) ?></td>
					<td class="num"><?= count($b['items']) ?></td>
					<td class="num mono"><?= $fmt(array_sum(array_column($b['items'], 'nominal'))) ?></td>
					<td style="white-space:nowrap"><?= $e(substr((string) $b['date'], 0, 10)) ?></td>
					<td style="white-space:nowrap">
						<button type="button" class="btn c sm" data-view="<?= (int) $b['id'] ?>">Lihat</button>
						<?php if ($can_decide) : ?><a class="btn sm" href="<?= base_url('pengajuan_rutin/proses/' . (int) $b['id']) ?>" style="text-decoration:none">Proses approval</a><?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>
	</div>
	<div class="scroll" data-apane="rec" hidden>
		<?php if (!$rec) : ?><p>Belum ada keputusan approval.</p><?php else : ?>
		<table>
			<thead><tr><th>Diputuskan</th><th>No. Item</th><th>No. Batch</th><th>Deskripsi</th><th class="num">Nominal</th><th>Keputusan</th><th>Keterangan reject</th></tr></thead>
			<tbody>
			<?php foreach ($rec as $pair) : list($b, $i) = $pair; ?>
				<tr>
					<td style="white-space:nowrap"><?= $e(substr((string) $i['at'], 0, 16)) ?><span class="sub"><?= $e($i['by']) ?></span></td>
					<td class="no"><?= $e($i['no_item']) ?></td>
					<td style="white-space:nowrap"><?= $e($b['no']) ?></td>
					<td><?= $e($i['desc']) ?></td>
					<td class="num mono"><?= $fmt($i['nominal']) ?></td>
					<td><span class="pill <?= $e($i['status']) ?>"><?= $e($IL[$i['status']]) ?></span></td>
					<td><?= $i['note'] !== '' ? '<span class="note">' . $e($i['note']) . '</span>' : '' ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>
	</div>
</div>
</div>

<script src="<?= base_url('assets/js/periodik_ui.js') ?>"></script>
<script>
(function () {
	var root = document.querySelector('.pjx');
	root.addEventListener('click', function (e) {
		var t = e.target.closest('button'); if (!t) return;
		if (t.dataset.atab) {
			root.querySelectorAll('[data-atab]').forEach(function (b) { b.classList.toggle('on', b === t); });
			root.querySelectorAll('[data-apane]').forEach(function (p) { p.hidden = p.dataset.apane !== t.dataset.atab; });
		} else if (t.dataset.view) {
			$.get(siteurl + 'pengajuan_rutin/view/' + t.dataset.view + '/app').done(function (html) { PJ.openModal(html); })
				.fail(function () { PJ.toast('Terjadi kesalahan. Silakan coba lagi.'); });
		} else if (t.hasAttribute('data-close')) { PJ.closeModal(); }
	});
	try { var msg = sessionStorage.getItem('pjToast'); if (msg) { sessionStorage.removeItem('pjToast'); PJ.toast(msg); } } catch (x) {}
})();
</script>
