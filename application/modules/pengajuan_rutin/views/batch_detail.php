<?php
/* Isi modal "Lihat" satu batch (pDetail di specs/input/Periodik.html). Variabel: $batch, $dept_name, $rp, $chain, $from, $can_decide. */
$e   = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$fmt = function ($n) { return number_format((float) $n, 0, ',', '.'); };
if (!$batch) { echo '<p class="hint">Data tidak ditemukan.</p>'; return; }
$letters = array_column($batch['items'], 'status');
list($k, $t) = Periodik_logic::batch_status($letters);
$label = array('W' => 'Waiting Approval', 'A' => 'Approved', 'R' => 'Rejected');
?>
<div class="mhead">
	<h2><?= $e($batch['no']) ?></h2>
	<div class="bar" style="margin:0">
		<?php if (!empty($can_decide) && in_array('W', $letters, true)) : ?>
			<a class="btn sm" href="<?= base_url('pengajuan_rutin/proses/' . (int) $batch['id']) ?>" style="text-decoration:none">Proses approval</a>
		<?php endif; ?>
		<button type="button" class="btn g sm" data-close>Tutup</button>
	</div>
</div>
<div class="grid2">
	<div><span>Departemen</span><?= $e($dept_name) ?></div>
	<div><span>Tanggal</span><?= $e(Periodik_logic::fdate($batch['date'])) ?></div>
	<div><span>Total</span><b class="mono"><?= $fmt(array_sum(array_column($batch['items'], 'nominal'))) ?></b></div>
	<div><span>Status batch</span><span class="pill <?= $e($k) ?>"><?= $e($t) ?></span></div>
</div>
<div class="scroll">
	<table>
		<thead><tr><th>No. Item</th><th>Deskripsi</th><th class="num">Perkiraan biaya</th><th>Keterangan</th><th>Dokumen</th><th>Progres</th></tr></thead>
		<tbody>
		<?php foreach ($batch['items'] as $i) : $ch = isset($chain[$i['no_item']]) ? $chain[$i['no_item']] : array(); ?>
			<tr>
				<td class="no"><?= $e($i['no_item']) ?>
					<?php if ($i['ref'] !== '') : ?><span class="ref" style="display:block">Revisi dari <?= $e($i['ref']) ?></span><?php endif; ?>
					<?php if ($i['resub'] !== '') : ?><span class="ref" style="display:block">Diajukan ulang di <?= $e($i['resub']) ?></span><?php endif; ?>
				</td>
				<td><?= $e($i['desc']) ?>
					<?php if ($ch) : ?><span class="sub">Riwayat revisi: <?php
						echo implode('; ', array_map(function ($c) use ($e) { return $e($c['no']) . ' (' . ($c['note'] !== '' ? $e($c['note']) : 'Rejected') . ')'; }, $ch));
					?></span><?php endif; ?>
				</td>
				<td class="num mono"><?= $fmt($i['nominal']) ?></td>
				<td><?= $e($i['ket']) ?></td>
				<td><?php if ($i['doc_file'] !== '') : ?>
					<a class="doclink" target="_blank" rel="noopener" href="<?= $e(periodik_doc_url($i['doc_file'])) ?>"><?= $e($i['doc_file']) ?></a>
				<?php else : ?><span class="sub">Tidak ada file</span><?php endif; ?></td>
				<td><?= Periodik_logic::steps_html($i, isset($rp[$i['no_item']]) ? $rp[$i['no_item']] : null) ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>
