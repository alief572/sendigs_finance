<?php
/* Approval Pengajuan Periodik: tab Menunggu approval / Record approval (pApprList di specs/input/Periodik.html).
   Tabel diisi DataTables serverside (Pengajuan_rutin::app_data). Variabel: $wait_count, $can_decide. */
?>
<link rel="stylesheet" href="<?= base_url('assets/css/periodik_ui.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<div class="pjx">
<div class="panel hl">
	<div class="tabs" role="tablist">
		<button type="button" role="tab" class="on" data-atab="wait">Menunggu approval (<?= (int) $wait_count ?>)</button>
		<button type="button" role="tab" data-atab="rec">Record approval</button>
	</div>
	<div class="scroll" data-apane="wait">
		<table data-dt="wait"><thead><tr><th>#</th><th>Departement</th><th>Nomor</th><th class="num">Jumlah item</th><th class="num">Nominal</th><th>Tanggal</th><th>Action</th></tr></thead></table>
	</div>
	<div class="scroll" data-apane="rec" hidden>
		<table data-dt="rec"><thead><tr><th>Diputuskan</th><th>No. Item</th><th>No. Batch</th><th>Deskripsi</th><th class="num">Nominal</th><th>Keputusan</th><th>Keterangan reject</th></tr></thead></table>
	</div>
</div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/js/periodik_ui.js') ?>"></script>
<script>
(function () {
	var root = document.querySelector('.pjx');
	var COLS = {wait: ['', '', 'no', 'num', 'num mono', '', ''], rec: ['', 'no', '', '', 'num mono', '', '']};
	var EMPTY = {
		wait: 'Tidak ada pengajuan yang menunggu approval. Pengajuan baru muncul di sini setelah disimpan dari menu Pengajuan Pembayaran Periodik.',
		rec: 'Belum ada keputusan approval.'
	};
	var tables = {};
	// tabel tab yang tersembunyi baru dibuat saat tab dibuka supaya lebar kolomnya terhitung benar
	function load(view) {
		if (tables[view]) { tables[view].columns.adjust(); return; }
		tables[view] = $(root.querySelector('table[data-dt="' + view + '"]')).DataTable({
			serverSide: true, processing: true, ordering: false, autoWidth: false,
			ajax: {url: siteurl + 'pengajuan_rutin/app_data', type: 'POST', data: function (d) { d.view = view; }},
			columns: COLS[view].map(function (c) { return {className: c}; }),
			language: {emptyTable: EMPTY[view], zeroRecords: view === 'wait' ? 'Tidak ada batch yang cocok dengan pencarian.' : 'Tidak ada keputusan yang cocok dengan pencarian.', processing: 'Memuat...'}
		});
	}
	root.addEventListener('click', function (e) {
		var t = e.target.closest('button'); if (!t) return;
		if (t.dataset.atab) {
			root.querySelectorAll('[data-atab]').forEach(function (b) { b.classList.toggle('on', b === t); });
			root.querySelectorAll('[data-apane]').forEach(function (p) { p.hidden = p.dataset.apane !== t.dataset.atab; });
			load(t.dataset.atab);
		} else if (t.dataset.view) {
			$.get(siteurl + 'pengajuan_rutin/view/' + t.dataset.view + '/app').done(function (html) { PJ.openModal(html); })
				.fail(function () { PJ.toast('Terjadi kesalahan. Silakan coba lagi.'); });
		} else if (t.hasAttribute('data-close')) { PJ.closeModal(); }
	});
	try { var msg = sessionStorage.getItem('pjToast'); if (msg) { sessionStorage.removeItem('pjToast'); PJ.toast(msg); } } catch (x) {}
	load('wait');
})();
</script>
