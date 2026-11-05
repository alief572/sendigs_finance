<?php
/* Budget Pembayaran Periodik: daftar per departemen (mList di specs/input/Periodik.html), DataTables server-side.
   Baris datang dari Budget_periodik::get_data. Variabel: $dd_items (JSON), $can_add, $can_manage. */
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/periodik_ui.css') ?>">
<div class="pjx">
<div class="panel hl">
	<div class="bar">
		<?php if ($can_add) : ?><span class="dd"><button type="button" class="btn" id="pjNew" aria-haspopup="true" aria-expanded="false">+ New</button></span><?php endif; ?>
		<?php if ($can_manage) : ?><button type="button" class="btn g" id="pjProses" title="Hitung ulang anggaran Pembayaran Periodik dari master budget yang berlaku">Proses Budget</button><?php endif; ?>
	</div>
	<div class="scroll">
		<table id="pjTable" class="table table-bordered table-striped" style="width:100%">
			<thead><tr><th>#</th><th>Departemen</th><th class="num">Item aktif</th><th class="num">Budget / bulan</th><th class="num">Budget / tahun</th><th>Status budget</th><th>Action</th></tr></thead>
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
	var root = document.querySelector('.pjx');
	var flash = '';
	try {
		var msg = sessionStorage.getItem('pjToast'); if (msg) { sessionStorage.removeItem('pjToast'); PJ.toast(msg); }
		flash = sessionStorage.getItem('pjFlash') || ''; sessionStorage.removeItem('pjFlash');
	} catch (x) {}

	$('#pjTable').DataTable({
		serverSide: true,
		processing: true,
		order: [[1, 'asc']],
		ajax: {type: 'post', url: siteurl + 'budget_periodik/get_data', cache: false, dataType: 'json'},
		columns: [
			{data: 'no', orderable: false},
			{data: 'department'},
			{data: 'aktif', className: 'num'},
			{data: 'bulan', className: 'num mono'},
			{data: 'tahun', className: 'num mono'},
			{data: 'status'},
			{data: 'action', orderable: false}
		],
		drawCallback: function () {
			if (!flash) return;
			var row = root.querySelector('tr[data-dept="' + flash.replace(/"/g, '') + '"]');
			if (row) { row.classList.add('flash'); flash = ''; }
		}
	});

	root.addEventListener('click', function (e) {
		var t = e.target.closest('button'); if (!t) return;
		if (t.dataset.view) {
			$.get(siteurl + 'budget_periodik/view/' + encodeURIComponent(t.dataset.view)).done(function (html) { PJ.openModal(html); })
				.fail(function () { PJ.toast('Terjadi kesalahan. Silakan coba lagi.'); });
		} else if (t.hasAttribute('data-close')) { PJ.closeModal(); }
		else if (t.id === 'pjProses') {
			PJ.ask({title: 'Proses budget?', body: '<p class="hint">Anggaran Pembayaran Periodik tahun ini dihitung ulang dari master budget yang berlaku sekarang.</p>', ok: 'Proses',
				onOk: function () {
					$.post(siteurl + 'budget_periodik/proses_budget_periodik', {}, null, 'json').done(function (r) { PJ.toast(r && r.save ? 'Budget berhasil diproses.' : 'Proses budget gagal. Silakan coba lagi.'); })
						.fail(function () { PJ.toast('Terjadi kesalahan. Silakan coba lagi.'); });
				}});
		}
	});
	var btn = document.getElementById('pjNew');
	if (btn) PJ.dropdown(btn, <?= $dd_items ?>, function (id) { location.href = siteurl + 'budget_periodik/create/' + encodeURIComponent(id); });
})();
</script>
