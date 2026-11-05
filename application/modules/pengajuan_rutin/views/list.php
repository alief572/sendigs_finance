<?php
/* Daftar Pengajuan Pembayaran Periodik: tab Menunggu approval / Sudah diputuskan, tampilan Per Batch / Per Item.
   Mengikuti pList() di specs/input/Periodik.html. Data: $batches, $rp, $names, $dd_items, $can_add, $can_manage, $can_delete. */
$e   = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$fmt = function ($n) { return number_format((float) $n, 0, ',', '.'); };
$pill = function ($k, $t) use ($e) { return '<span class="pill ' . $e($k) . '">' . $e($t) . '</span>'; };
$hist = function ($i, $withResub = true) use ($e) {
	return ($i['ref'] !== '' ? '<span class="ref" style="display:block">Revisi dari ' . $e($i['ref']) . '</span>' : '')
		. ($withResub && $i['resub'] !== '' ? '<span class="ref" style="display:block">Diajukan ulang di ' . $e($i['resub']) . '</span>' : '');
};
$total = function ($b) { return array_sum(array_column($b['items'], 'nominal')); };
$dname = function ($b) use ($names) { return isset($names[$b['dept']]) ? $names[$b['dept']] : $b['dept']; };

$wait = $done = array();
foreach ($batches as $b) {
	if (in_array('W', array_column($b['items'], 'status'), true)) { $wait[] = $b; } else { $done[] = $b; }
}
$rpOf = function ($i) use ($rp) { return isset($rp[$i['no_item']]) ? $rp[$i['no_item']] : null; };
$canEdit = $can_add || $can_manage;
?>
<link rel="stylesheet" href="<?= base_url('assets/css/periodik_ui.css') ?>">
<div class="pjx">
<div class="panel hl">
	<div class="bar">
		<?php if ($can_add) : ?>
			<span class="dd"><button type="button" class="btn" id="pjNew" aria-haspopup="true" aria-expanded="false">+ New</button></span>
		<?php endif; ?>
	</div>
	<div class="tabs" role="tablist">
		<button type="button" role="tab" class="on" data-pview="wait">Menunggu approval (<?= count($wait) ?>)</button>
		<button type="button" role="tab" data-pview="rec">Sudah diputuskan (<?= count($done) ?>)</button>
	</div>
	<div class="bar">
		<div class="seg" role="group" aria-label="Tampilan">
			<button type="button" class="on A" data-ptab="batch" aria-pressed="true">Per Batch</button>
			<button type="button" data-ptab="item" aria-pressed="false">Per Item</button>
		</div>
		<span class="sp" style="flex:1"></span>
		<div class="filters" id="pjFilter" hidden>
			<label for="pf" class="sub" style="display:inline" id="pfLabel">Status batch</label>
			<select id="pf"></select>
		</div>
	</div>

	<!-- Menunggu approval, Per Batch -->
	<div class="scroll" data-pane="wait-batch">
		<?php if (!$wait) : ?><p>Tidak ada batch yang menunggu approval. Klik + New untuk membuat pengajuan.</p><?php else : ?>
		<table>
			<thead><tr><th>#</th><th>Departemen</th><th>No. Batch</th><th>Item</th><th class="num">Nominal</th><th>Tanggal</th><th>Status batch</th><th>Action</th></tr></thead>
			<tbody>
			<?php foreach ($wait as $ix => $b) :
				list($k, $t) = Periodik_logic::batch_status(array_column($b['items'], 'status')); ?>
				<tr data-batch="<?= $e($b['no']) ?>">
					<td><?= $ix + 1 ?></td>
					<td><?= $e($dname($b)) ?></td>
					<td class="no"><?= $e($b['no']) ?></td>
					<td><ol class="items"><?php foreach ($b['items'] as $i) : ?><li><?= $e($i['desc']) ?></li><?php endforeach; ?></ol></td>
					<td class="num mono"><?= $fmt($total($b)) ?></td>
					<td style="white-space:nowrap"><?= $e(Periodik_logic::fdate($b['date'])) ?></td>
					<td><?= $pill($k, $t) ?></td>
					<td style="white-space:nowrap">
						<button type="button" class="btn c sm" data-view="<?= (int) $b['id'] ?>">Lihat</button>
						<?php if ($canEdit) : ?><a class="btn o sm" href="<?= base_url('pengajuan_rutin/edit/' . (int) $b['id']) ?>" style="text-decoration:none">Edit</a><?php endif; ?>
						<?php if ($can_delete) : ?><button type="button" class="btn r sm" data-del="<?= $e($b['no']) ?>" data-count="<?= count($b['items']) ?>">Hapus</button><?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>
	</div>

	<!-- Menunggu approval, Per Item -->
	<div class="scroll" data-pane="wait-item" hidden>
		<?php $waitItems = array(); foreach ($wait as $b) { foreach ($b['items'] as $i) { $waitItems[] = array($b, $i); } } ?>
		<?php if (!$waitItems) : ?><p>Tidak ada item yang menunggu approval.</p><?php else : ?>
		<table>
			<thead><tr><th>No. Item</th><th>No. Batch</th><th>Departemen</th><th>Deskripsi</th><th class="num">Nominal</th><th>Keterangan</th><th>Progres</th></tr></thead>
			<tbody>
			<?php foreach ($waitItems as $pair) : list($b, $i) = $pair; ?>
				<tr data-batch="<?= $e($b['no']) ?>">
					<td class="no"><?= $e($i['no_item']) ?><?= $hist($i) ?></td>
					<td style="white-space:nowrap"><?= $e($b['no']) ?></td>
					<td style="white-space:nowrap"><?= $e($dname($b)) ?></td>
					<td><?= $e($i['desc']) ?></td>
					<td class="num mono"><?= $fmt($i['nominal']) ?></td>
					<td><?= $e($i['ket']) ?></td>
					<td><?= Periodik_logic::steps_html($i, $rpOf($i)) ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>
	</div>

	<!-- Sudah diputuskan, Per Batch -->
	<div class="scroll" data-pane="rec-batch" hidden>
		<?php if (!$done) : ?><p>Tidak ada batch dengan status ini. Pilih status lain di filter.</p><?php else : ?>
		<table>
			<thead><tr><th>#</th><th>Departemen</th><th>No. Batch</th><th>Item</th><th class="num">Nominal</th><th>Tanggal</th><th>Status batch</th><th>Action</th></tr></thead>
			<tbody>
			<?php foreach ($done as $ix => $b) :
				$letters = array_column($b['items'], 'status');
				list($k, $t) = Periodik_logic::batch_status($letters);
				$counts = array_count_values($letters); ?>
				<tr data-k="<?= $e($k) ?>">
					<td><?= $ix + 1 ?></td>
					<td><?= $e($dname($b)) ?></td>
					<td class="no"><?= $e($b['no']) ?></td>
					<td><ol class="items"><?php foreach ($b['items'] as $i) : ?><li><?= $e($i['desc']) ?></li><?php endforeach; ?></ol></td>
					<td class="num mono"><?= $fmt($total($b)) ?></td>
					<td style="white-space:nowrap"><?= $e(Periodik_logic::fdate($b['date'])) ?></td>
					<td><?= $pill($k, $t) ?><?php if ($k === 'P' || $k === 'R') : ?><span class="sub"><?= (int) ($counts['A'] ?? 0) ?> approved, <?= (int) ($counts['R'] ?? 0) ?> rejected</span><?php endif; ?></td>
					<td style="white-space:nowrap"><button type="button" class="btn c sm" data-view="<?= (int) $b['id'] ?>">Lihat</button></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<p class="hint" data-empty="rec-batch" hidden>Tidak ada batch dengan status ini. Pilih status lain di filter.</p>
		<?php endif; ?>
	</div>

	<!-- Sudah diputuskan, Per Item -->
	<div class="scroll" data-pane="rec-item" hidden>
		<?php $doneItems = array(); foreach ($done as $b) { foreach ($b['items'] as $i) { $doneItems[] = array($b, $i); } } ?>
		<?php if (!$doneItems) : ?><p>Tidak ada item untuk filter ini.</p><?php else : ?>
		<table>
			<thead><tr><th>No. Item</th><th>No. Batch</th><th>Departemen</th><th>Deskripsi</th><th class="num">Nominal</th><th>Progres</th></tr></thead>
			<tbody>
			<?php foreach ($doneItems as $pair) : list($b, $i) = $pair; ?>
				<tr data-st="<?= $e($i['status']) ?>">
					<td class="no"><?= $e($i['no_item']) ?><?= $hist($i, false) ?></td>
					<td style="white-space:nowrap"><?= $e($b['no']) ?></td>
					<td style="white-space:nowrap"><?= $e($dname($b)) ?></td>
					<td><?= $e($i['desc']) ?></td>
					<td class="num mono"><?= $fmt($i['nominal']) ?></td>
					<td><?= Periodik_logic::steps_html($i, $rpOf($i)) ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<p class="hint" data-empty="rec-item" hidden>Tidak ada item untuk filter ini.</p>
		<?php endif; ?>
	</div>
</div>
</div>

<script src="<?= base_url('assets/js/periodik_ui.js') ?>"></script>
<script>
(function () {
	var ddItems = <?= $dd_items ?>;
	var view = 'wait', tab = 'batch';
	var FILTERS = {batch: [['all', 'Semua'], ['A', 'Approved'], ['P', 'Partially Approved'], ['R', 'Rejected']], item: [['all', 'Semua'], ['A', 'Approved'], ['R', 'Rejected']]};
	var root = document.querySelector('.pjx');

	function render() {
		root.querySelectorAll('[data-pview]').forEach(function (b) { var on = b.dataset.pview === view; b.classList.toggle('on', on); b.setAttribute('aria-selected', on); });
		root.querySelectorAll('[data-ptab]').forEach(function (b) {
			var on = b.dataset.ptab === tab; b.className = on ? 'on A' : ''; b.setAttribute('aria-pressed', on);
		});
		root.querySelectorAll('[data-pane]').forEach(function (p) { p.hidden = p.dataset.pane !== view + '-' + tab; });
		var fl = document.getElementById('pjFilter'), sel = document.getElementById('pf');
		fl.hidden = view !== 'rec';
		if (view === 'rec') {
			document.getElementById('pfLabel').textContent = tab === 'item' ? 'Status item' : 'Status batch';
			sel.innerHTML = FILTERS[tab].map(function (o) { return '<option value="' + o[0] + '">' + o[1] + '</option>'; }).join('');
			applyFilter();
		}
	}
	function applyFilter() {
		var v = document.getElementById('pf').value || 'all', pane = view + '-' + tab, shown = 0;
		root.querySelectorAll('[data-pane="' + pane + '"] tbody tr').forEach(function (tr) {
			var key = tab === 'item' ? tr.dataset.st : tr.dataset.k, ok = v === 'all' || key === v;
			tr.hidden = !ok; shown += ok ? 1 : 0;
		});
		var empty = root.querySelector('[data-empty="' + pane + '"]'); if (empty) empty.hidden = shown > 0;
	}
	root.addEventListener('click', function (e) {
		var t = e.target.closest('button'); if (!t) return;
		if (t.dataset.pview) { view = t.dataset.pview; render(); }
		else if (t.dataset.ptab) { tab = t.dataset.ptab; render(); }
		else if (t.dataset.view) {
			$.get(siteurl + 'pengajuan_rutin/view/' + t.dataset.view).done(function (html) { PJ.openModal(html); }).fail(function () { PJ.toast('Terjadi kesalahan. Silakan coba lagi.'); });
		}
		else if (t.hasAttribute('data-close')) { PJ.closeModal(); }
		else if (t.dataset.del) { confirmDelete(t.dataset.del, t.dataset.count); }
	});
	document.getElementById('pf').addEventListener('change', applyFilter);

	function confirmDelete(no, count) {
		PJ.ask({title: 'Hapus ' + no + '?', body: '<p class="hint">' + PJ.esc(count) + ' item akan dihapus. Item yang diajukan ulang di batch ini kembali bisa diajukan dari form New.</p>',
			ok: 'Hapus batch', okCls: 'r', onOk: function () {
				$.post(siteurl + 'pengajuan_rutin/hapus_data/' + encodeURIComponent(no), {}, null, 'json').done(function (r) {
					if (r && r.delete) { try { sessionStorage.setItem('pjToast', no + ' dihapus.'); } catch (x) {} location.reload(); }
					else { PJ.toast((r && r.msg) || 'Terjadi kesalahan. Silakan coba lagi.'); }
				}).fail(function () { PJ.toast('Terjadi kesalahan. Silakan coba lagi.'); });
			}});
	}

	var btn = document.getElementById('pjNew');
	if (btn) PJ.dropdown(btn, ddItems, function (id) { location.href = siteurl + 'pengajuan_rutin/create/' + encodeURIComponent(id); });

	try {
		var msg = sessionStorage.getItem('pjToast'); if (msg) { sessionStorage.removeItem('pjToast'); PJ.toast(msg); }
		var flash = sessionStorage.getItem('pjFlash');
		if (flash) { sessionStorage.removeItem('pjFlash'); var row = root.querySelector('tr[data-batch="' + flash + '"]'); if (row) row.classList.add('flash'); }
	} catch (x) {}
	render();
})();
</script>
