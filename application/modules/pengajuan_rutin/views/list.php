<?php
/* Daftar Pengajuan Pembayaran Periodik: tab Menunggu approval / Sudah diputuskan, tampilan Per Batch / Per Item.
   Mengikuti pList() di specs/input/Periodik.html. Tabel diisi DataTables serverside (Pengajuan_rutin::data_list).
   Data: $counts (wait, rec), $dd_items, $can_add. */
?>
<link rel="stylesheet" href="<?= base_url('assets/css/periodik_ui.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<div class="pjx">
<div class="panel hl">
	<div class="bar">
		<?php if ($can_add) : ?>
			<span class="dd"><button type="button" class="btn" id="pjNew" aria-haspopup="true" aria-expanded="false">+ New</button></span>
		<?php endif; ?>
	</div>
	<div class="tabs" role="tablist">
		<button type="button" role="tab" class="on" data-pview="wait">Menunggu approval (<?= (int) $counts['wait'] ?>)</button>
		<button type="button" role="tab" data-pview="rec">Sudah diputuskan (<?= (int) $counts['rec'] ?>)</button>
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

	<div class="scroll" data-pane="wait-batch">
		<table data-dt="wait-batch"><thead><tr><th>#</th><th>Departemen</th><th>No. Batch</th><th>Item</th><th class="num">Nominal</th><th>Tanggal</th><th>Status batch</th><th>Action</th></tr></thead></table>
	</div>
	<div class="scroll" data-pane="wait-item" hidden>
		<table data-dt="wait-item"><thead><tr><th>No. Item</th><th>No. Batch</th><th>Departemen</th><th>Deskripsi</th><th class="num">Nominal</th><th>Keterangan</th><th>Progres</th></tr></thead></table>
	</div>
	<div class="scroll" data-pane="rec-batch" hidden>
		<table data-dt="rec-batch"><thead><tr><th>#</th><th>Departemen</th><th>No. Batch</th><th>Item</th><th class="num">Nominal</th><th>Tanggal</th><th>Status batch</th><th>Action</th></tr></thead></table>
	</div>
	<div class="scroll" data-pane="rec-item" hidden>
		<table data-dt="rec-item"><thead><tr><th>No. Item</th><th>No. Batch</th><th>Departemen</th><th>Deskripsi</th><th class="num">Nominal</th><th>Progres</th></tr></thead></table>
	</div>
</div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
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
		}
		load();
	}
	var COLS = {
		'wait-batch': ['', '', 'no', '', 'num mono', '', '', ''],
		'wait-item': ['no', '', '', '', 'num mono', '', ''],
		'rec-batch': ['', '', 'no', '', 'num mono', '', '', ''],
		'rec-item': ['no', '', '', '', 'num mono', '']
	};
	var EMPTY = {
		'wait-batch': 'Tidak ada batch yang menunggu approval. Klik + New untuk membuat pengajuan.',
		'wait-item': 'Tidak ada item yang menunggu approval.',
		'rec-batch': 'Tidak ada batch dengan status ini. Pilih status lain di filter.',
		'rec-item': 'Tidak ada item untuk filter ini.'
	};
	var tables = {}, flash = null;
	function load() {
		var key = view + '-' + tab;
		if (tables[key]) { tables[key].ajax.reload(null, false); return; }
		tables[key] = $(root.querySelector('table[data-dt="' + key + '"]')).DataTable({
			serverSide: true, processing: true, ordering: false, autoWidth: false,
			ajax: {url: siteurl + 'pengajuan_rutin/data_list', type: 'POST', data: function (d) {
				d.view = view; d.mode = tab; d.filter = view === 'rec' ? (document.getElementById('pf').value || 'all') : 'all';
			}},
			columns: COLS[key].map(function (c) { return {className: c}; }),
			language: {emptyTable: EMPTY[key], zeroRecords: EMPTY[key], processing: 'Memuat...'},
			drawCallback: function () {
				if (!flash) return;
				var row = root.querySelector('tr[data-batch="' + flash + '"]');
				if (row) { row.classList.add('flash'); flash = null; }
			}
		});
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
	document.getElementById('pf').addEventListener('change', load);

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
		flash = sessionStorage.getItem('pjFlash');
		if (flash) { sessionStorage.removeItem('pjFlash'); }
	} catch (x) {}
	render();
})();
</script>
