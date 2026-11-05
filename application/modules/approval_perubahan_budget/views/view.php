<?php
/* Isi modal "Lihat" satu perubahan budget (bView di specs/input/Periodik.html). Variabel: $bg, $dept_name, $can_decide. */
$e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
if (!$bg) { echo '<p class="hint">Data tidak ditemukan.</p>'; return; }
require_once __DIR__ . '/_line_helpers.php';
?>
<div class="mhead">
	<h2><?= $e($bg['no']) ?></h2>
	<div class="bar" style="margin:0">
		<?php if (!empty($can_decide)) : ?><a class="btn sm" href="<?= base_url('approval_perubahan_budget/proses/' . rawurlencode($bg['no'])) ?>" style="text-decoration:none">Proses approval</a><?php endif; ?>
		<button type="button" class="btn g sm" data-close>Tutup</button>
	</div>
</div>
<div class="grid2">
	<div><span>Departemen</span><?= $e($dept_name) ?></div>
	<div><span>Diajukan</span><?= $e(substr((string) $bg['at'], 0, 16)) ?> · <?= $e($bg['by']) ?></div>
	<div><span>Jumlah perubahan</span><b><?= count($bg['lines']) ?></b></div>
	<div><span>Status</span><?= $bg['status'] === 'pending' ? '<span class="pill W">Menunggu approval</span>' : '<span class="pill A">Selesai</span>' ?></div>
</div>
<div class="scroll">
	<table>
		<thead><tr><th>#</th><th>Item</th><th>Jenis perubahan</th><th>Sebelum</th><th>Sesudah</th></tr></thead>
		<tbody>
		<?php foreach ($bg['lines'] as $ix => $l) : $k = Budget_logic::KINDS[$l['kind']]; ?>
			<tr>
				<td><?= $ix + 1 ?></td>
				<td><?= $e($l['kind'] === 'new' ? $l['after']['nama'] : $l['before']['nama']) ?><span class="sub"><?= $e($l['after']['coa']) ?></span></td>
				<td><span class="pill <?= $e($k[0]) ?>"><?= $e($k[1]) ?></span></td>
				<td><?= apb_line_html($l, 'before') ?></td>
				<td><?= apb_line_html($l, 'after') ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>
