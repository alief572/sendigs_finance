<?php
/* Approval Perubahan Budget: tab Menunggu approval / Record approval (bApprList di specs/input/Periodik.html),
   dua tabel DataTables server-side yang datang dari Approval_perubahan_budget::get_data. */
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/periodik_ui.css') ?>">
<div class="pjx">
<div class="panel hl">
	<div class="tabs" role="tablist">
		<button type="button" role="tab" class="on" data-btab="wait" id="pjTabWait">Menunggu approval</button>
		<button type="button" role="tab" data-btab="rec">Record approval</button>
	</div>
	<div class="scroll" data-bpane="wait">
		<table id="pjWait" class="table table-bordered table-striped" style="width:100%">
			<thead><tr><th>#</th><th>Departement</th><th>No. Perubahan</th><th>Perubahan</th><th>Diajukan</th><th>Action</th></tr></thead>
			<tbody></tbody>
		</table>
	</div>
	<div class="scroll" data-bpane="rec" hidden>
		<table id="pjRec" class="table table-bordered table-striped" style="width:100%">
			<thead><tr><th>Diputuskan</th><th>No. Perubahan</th><th>Departemen</th><th>Item</th><th>Perubahan</th><th>Keputusan</th><th>Keterangan reject</th></tr></thead>
			<tbody></tbody>
		</table>
	</div>
</div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/js/periodik_ui.js') ?>"></script>
<script>
(function () {
	var root = document.querySelector('.pjx'), recTable = null;
	function source(tab) {
		return {type: 'post', url: siteurl + 'approval_perubahan_budget/get_data', cache: false, dataType: 'json', data: function (d) { d.tab = tab; }};
	}
	var waitTable = $('#pjWait').DataTable({
		serverSide: true, processing: true, order: [[2, 'desc']], ajax: source('wait'),
		columns: [{data: 'no', orderable: false}, {data: 'department'}, {data: 'nomor', className: 'no'}, {data: 'perubahan'}, {data: 'diajukan'}, {data: 'action', orderable: false}]
	});
	// judul tab membawa jumlah BG menunggu approval (tanpa filter pencarian)
	$('#pjWait').on('xhr.dt', function (e, settings, json) { if (json) document.getElementById('pjTabWait').textContent = 'Menunggu approval (' + json.pending_total + ')'; });

	function initRec() {
		if (recTable) return;
		recTable = $('#pjRec').DataTable({
			serverSide: true, processing: true, order: [[0, 'desc']], ajax: source('rec'),
			columns: [{data: 'diputuskan'}, {data: 'nomor', className: 'no'}, {data: 'department'}, {data: 'item'}, {data: 'perubahan', orderable: false}, {data: 'keputusan'}, {data: 'note', orderable: false}]
		});
	}
	root.addEventListener('click', function (e) {
		var t = e.target.closest('button'); if (!t) return;
		if (t.dataset.btab) {
			root.querySelectorAll('[data-btab]').forEach(function (b) { b.classList.toggle('on', b === t); });
			root.querySelectorAll('[data-bpane]').forEach(function (p) { p.hidden = p.dataset.bpane !== t.dataset.btab; });
			if (t.dataset.btab === 'rec') initRec();
		} else if (t.dataset.view) {
			$.get(siteurl + 'approval_perubahan_budget/view/' + encodeURIComponent(t.dataset.view)).done(function (html) { PJ.openModal(html); })
				.fail(function () { PJ.toast('Terjadi kesalahan. Silakan coba lagi.'); });
		} else if (t.hasAttribute('data-close')) { PJ.closeModal(); }
	});
	try { var msg = sessionStorage.getItem('pjToast'); if (msg) { sessionStorage.removeItem('pjToast'); PJ.toast(msg); } } catch (x) {}
})();
</script>
