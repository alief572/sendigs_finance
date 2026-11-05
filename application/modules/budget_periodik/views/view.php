<?php
/* Isi modal "Lihat" budget satu departemen (mView di specs/input/Periodik.html).
   Variabel: $dept, $dept_name, $masters, $sum, $bgs, $can_edit. */
$e   = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$fmt = function ($n) { return number_format((float) $n, 0, ',', '.'); };
$pending = null;
foreach ($bgs as $bg) { if ($bg['status'] === 'pending') { $pending = $bg; break; } }
?>
<div class="mhead">
	<h2>Budget <?= $e($dept_name) ?></h2>
	<div class="bar" style="margin:0">
		<?php if ($can_edit) : ?><a class="btn o sm" href="<?= base_url('budget_periodik/edit/' . rawurlencode($dept)) ?>" style="text-decoration:none">Edit</a><?php endif; ?>
		<button type="button" class="btn g sm" data-close>Tutup</button>
	</div>
</div>
<div class="grid2">
	<div><span>Departemen</span><?= $e($dept_name) ?></div>
	<div><span>Item aktif</span><?= (int) $sum['act'] ?> dari <?= (int) $sum['n'] ?></div>
	<div><span>Budget / bulan</span><b class="mono"><?= $fmt($sum['bln']) ?></b></div>
	<div><span>Status</span><?= $pending ? '<span class="pill W">Menunggu approval ' . $e($pending['no']) . '</span>' : '<span class="pill A">Aktif</span>' ?></div>
</div>
<div class="scroll">
	<table>
		<thead><tr><th>#</th><th>Post Pengeluaran</th><th>Nama Barang / Jasa</th><th>Bulanan / Tahunan</th><th>Jadwal</th><th class="num">Perkiraan Biaya</th><th>Masa berlaku</th><th>Status</th></tr></thead>
		<tbody>
		<?php if (!$masters) : ?><tr><td colspan="8">Belum ada item yang berlaku. Item baru menunggu approval.</td></tr><?php endif; ?>
		<?php foreach ($masters as $ix => $m) :
			$n = Budget_logic::normalize($m);
			$used = Budget_logic::used($m, $m['in_sys']);
			$done = $n['masa_berlaku'] > 0 && $used >= $n['masa_berlaku']; ?>
			<tr class="<?= $n['aktif'] ? '' : 'lockrow' ?>">
				<td><?= $ix + 1 ?></td>
				<td><?= $e($n['coa']) ?></td>
				<td><?= $e($n['nama']) ?></td>
				<td><?= $n['tipe'] === 'tahun' ? 'Tahunan' : 'Bulanan' ?></td>
				<td style="white-space:nowrap"><?= $e(periodik_schedule_text($n['tipe'], $n['tanggal'])) ?></td>
				<td class="num mono"><?= $fmt($n['nilai']) ?></td>
				<td><?= $n['masa_berlaku'] > 0 ? $used . ' / ' . $n['masa_berlaku'] . ' kali' : 'Selalu' ?></td>
				<td><?= !$n['aktif'] ? '<span class="pill R">Nonaktif</span>' : ($done ? '<span class="pill P">Selesai</span>' : '<span class="pill A">Aktif</span>') ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>
<h2 style="font-size:15px;margin:14px 0 6px">Riwayat perubahan</h2>
<?php if (!$bgs) : ?><p class="sub">Belum ada perubahan yang diajukan.</p><?php else : ?>
<div class="scroll">
	<table>
		<thead><tr><th>No. Perubahan</th><th>Diajukan</th><th>Perubahan</th><th>Status</th></tr></thead>
		<tbody>
		<?php foreach ($bgs as $bg) : ?>
			<tr>
				<td class="no"><?= $e($bg['no']) ?></td>
				<td style="white-space:nowrap"><?= $e(substr((string) $bg['at'], 0, 16)) ?><span class="sub"><?= $e($bg['by']) ?></span></td>
				<td><?php foreach ($bg['lines'] as $l) : $k = Budget_logic::KINDS[$l['kind']]; $name = $l['kind'] === 'new' ? $l['after']['nama'] : $l['before']['nama']; ?>
					<div><span class="pill <?= $e($k[0]) ?>"><?= $e($k[1]) ?></span> <?= $e($name) ?><?= $l['decision'] ? ' · ' . ($l['decision'] === 'A' ? 'Approved' : 'Rejected') : '' ?></div>
				<?php endforeach; ?></td>
				<td><?= $bg['status'] === 'pending' ? '<span class="pill W">Menunggu approval</span>' : '<span class="pill A">Selesai</span>' ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>
<?php endif; ?>
