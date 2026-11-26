<link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css">
<style>
	/* Dua tabel antrean memakai satu gaya; header biru modul, digelapkan agar teks putih terbaca (5:1). */
	.rp-list-head { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px }
	.rp-list-head h4 { margin:0; font-size:15px; font-weight:700; color:#2b3440 }
	.rp-list-actions { display:flex; flex-wrap:wrap; gap:6px; align-items:center; margin-left:auto }
	table.table.rp-list { border-collapse:collapse; margin:0 }
	table.table.rp-list > thead > tr > th { background:#31739a; color:#fff; font-size:11.5px; text-transform:uppercase; border:1px solid #2a6485; vertical-align:middle; white-space:nowrap; text-align:center }
	table.table.rp-list > tbody > tr > td { padding:10px; border-color:#e4e9ee; vertical-align:top; font-size:13px }
	table.table.rp-list > tbody > tr:nth-child(even) > td { background:#f8fafb }
	table.table.rp-list > tbody > tr:hover > td { background:#eef5fa }
	table.table.rp-list > tbody > tr:has(input[type=checkbox]:checked) > td { background:#fdf3d8 }
	table.rp-list input[type=checkbox] { width:18px; height:18px; cursor:pointer }
	table.rp-list .text-muted { color:#5f6b76 }
	table.rp-list .rp-amount { font-weight:700; font-size:14px; white-space:nowrap; font-variant-numeric:tabular-nums }
	#table_rp_transfer tbody tr { cursor:pointer }
	.rp-list-note { font-size:12.5px; color:#5f6b76; margin:0 0 12px; max-width:110ch }
	#rp-transfer-picked { display:block; font-size:12.5px; font-weight:600; color:#2b3440; margin-bottom:8px }
	.rp-tabbar { display:flex; flex-wrap:wrap; align-items:center; gap:10px; border-bottom:1px solid #f4f4f4 }
	.rp-tabbar > .nav-tabs { border-bottom:0 }
	.rp-tabbar > .rp-list-actions { padding:6px 10px }
	.rp-tabbar .badge { margin-left:6px; background:#31739a }
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<?php ob_start(); ?>
<div class="table-responsive"><table class="table table-bordered rp-list" id="table_list_req_payment" style="width:100%" data-periode="<?= !empty($rp_transfer_show) ? 'lama' : '' ?>" data-jenis-payment="<?= htmlspecialchars((string) $jenis_payment, ENT_QUOTES, 'UTF-8') ?>">
    <thead>
        <tr>
            <th class="text-center">No</th>
            <th class="text-center">No. Dokumen</th>
            <th class="text-center">Tgl</th>
            <th class="text-center">Company</th>
            <th class="text-center">Keperluan</th>
            <th class="text-center">Currency</th>
            <th class="text-center">Total Invoice</th>
            <th class="text-center">Requestor</th>
            <th class="text-center">Option</th>
        </tr>
    </thead>
    <tbody></tbody>
</table></div>
<?php $legacyTable = ob_get_clean(); ob_start(); ?>
<div class="rp-list-actions">
    <a href="<?= base_url('pembayaran_material/payment_list') ?>" class="btn btn-sm btn-danger"><i class="fa fa-arrow-left"></i> Back</a>
    <button type="button" class="btn btn-sm btn-warning clear_choosed_payment"><i class="fa fa-refresh"></i> Clear Checked Payment</button>
    <button type="button" class="btn btn-sm btn-success proses_payment"><i class="fa fa-check"></i> Proses<span id="rp-selected-count" aria-live="polite"></span></button>
</div>
<?php $actions = ob_get_clean(); ?>
<?php if (!empty($rp_transfer_show)): ?>
	<?php $rp_tutorial_page = 'queue'; include dirname(__DIR__, 2) . '/request_payment/views/transfer_tutorial.php'; ?>
	<div class="nav-tabs-custom">
		<div class="rp-tabbar">
			<ul class="nav nav-tabs" id="rp-tabs">
				<li class="active"><a href="#rp-tab-baru" data-toggle="tab">Pengajuan Baru<span class="badge" data-count="baru"></span></a></li>
				<li><a href="#rp-tab-lama" data-toggle="tab">Dokumen Lama<span class="badge" data-count="lama"></span></a></li>
			</ul>
			<?= $actions ?>
		</div>
		<div class="tab-content">
			<div class="tab-pane active" id="rp-tab-baru">
				<form id="rp-payment-selection">
					<p class="rp-list-note">Dokumen mulai 1 November 2026 dan transfer dari pengajuan. <b>Transfer</b> dipilih satu per satu (1 payment = 1 transfer). <b>Dokumen</b> bisa dicentang lebih dari satu bila jenisnya sama dan <b>No. Pengajuannya sama</b>; Hutang Petty Cash dibayar lewat form lama.</p>
					<span id="rp-transfer-picked" role="status" aria-live="polite"></span>
					<div class="table-responsive"><table class="table table-bordered rp-list" id="table_rp_transfer" style="width:100%">
						<thead><tr><th class="text-center">No</th><th class="text-center">No. Pengajuan</th><th class="text-center">Tgl Approve</th><th class="text-center">Company</th><th class="text-center">Penerima / Rekening Tujuan</th>
							<th class="text-center">No. Dokumen</th><th class="text-center">Currency</th><th class="text-center">Admin</th><th class="text-center">Jumlah Transfer</th><th class="text-center">Option</th></tr></thead>
						<tbody></tbody>
					</table></div>
				</form>
			</div>
			<div class="tab-pane" id="rp-tab-lama"><?= $legacyTable ?></div>
		</div>
	</div>
<?php else: ?>
	<div class="box">
		<div class="box-header with-border rp-list-head"><?= $actions ?></div>
		<div class="box-body"><?= $legacyTable ?></div>
	</div>
<?php endif; ?>

<script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>

<script>
	$.extend(true, $.fn.dataTable.defaults, {language: {
		search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ baris', processing: 'Memuat daftar pembayaran...',
		info: 'Menampilkan _START_ - _END_ dari _TOTAL_', infoEmpty: 'Tidak ada data', infoFiltered: '(disaring dari _MAX_)',
		emptyTable: 'Belum ada pembayaran yang menunggu diproses.',
		zeroRecords: 'Tidak ada yang cocok dengan pencarian. Ubah kata kunci atau kosongkan kotak Cari.'
	}});
</script>
<script src="<?= base_url('assets/js/pembayaran_material/payment_selection.js') ?>?v=<?= (int) @filemtime(dirname(__DIR__, 4) . '/assets/js/pembayaran_material/payment_selection.js') ?>"></script>
<?php if (!empty($rp_transfer_show)): ?>
<script>
	(function () {
		var form = document.getElementById('rp-payment-selection');
		// Pilihan disimpan di luar tabel supaya tetap ada saat pindah halaman atau mencari.
		var picked = null;
		var summary = document.getElementById('rp-transfer-picked');
		var documents = 0;
		function showCount() {
			var total = documents + (picked === null ? 0 : 1);
			// Pilihan berlaku untuk kedua tab, jadi jumlahnya selalu terlihat di tombol Proses.
			$('#rp-selected-count').text(total ? ' (' + total + ' dipilih)' : '');
		}
		function select(id, label) {
			picked = id;
			summary.textContent = label ? 'Dipilih: ' + label : '';
			showCount();
		}
		window.rpSelectedCount = function (count) { documents = count; showCount(); };
		var table = $('#table_rp_transfer').DataTable({
			serverSide: true,
			processing: true,
			order: [],
			ajax: {type: 'POST', url: <?= json_encode(site_url('pembayaran_material/get_list_rp_transfer')) ?>, dataSrc: function (json) {
				if (json.selection_error) { alert(json.selection_error); }
				if (json.selected_count !== undefined) { window.rpSelectedCount(json.selected_count); }
				$('[data-count="baru"]').text(json.recordsTotal);
				return json.data;
			}},
			columns: [
				{data: 'no', orderable: false}, {data: 'no_pengajuan'}, {data: 'tgl'}, {data: 'company'}, {data: 'penerima'},
				{data: 'dokumen', orderable: false}, {data: 'currency', orderable: false}, {data: 'admin', orderable: false},
				{data: 'jumlah', className: 'text-right'}, {data: 'option', orderable: false, className: 'text-center'}
			],
			drawCallback: function () {
				$(form).find('input[name="transfer"]').each(function () { this.checked = this.value === picked; });
				$(form).find('.check_payment').each(function () { this.disabled = this.getAttribute('data-selection-disabled') === '1'; });
			}
		});
		// Dipanggil payment_selection.js setelah pilihan dokumen lama berubah.
		window.rpUpperTable = table;
		$(form).on('change', 'input[name="transfer"]', function () {
			if (!this.checked) { if (picked === this.value) select(null); return; }
			if (picked !== null && picked !== this.value) {
				this.checked = false;
				alert('1 payment = 1 transfer. Transfer lain (termasuk dari PGJ berbeda) tidak bisa digabung ke payment ini. Kosongkan pilihan dan pilih ulang.');
				return;
			}
			select(this.value, this.getAttribute('data-label'));
		});
		$('#table_rp_transfer tbody').on('click', 'tr', function (event) {
			if ($(event.target).is('input, a, button')) return;
			$(this).find('input[name="transfer"]').trigger('click');
		});
		$('.clear_choosed_payment').on('click', function () {
			select(null);
			$(form).find('input[name="transfer"]').prop('checked', false);
		});
		// Enter di kotak Cari tidak boleh menjalankan Proses.
		form.addEventListener('keydown', function (event) { if (event.key === 'Enter' && event.target.type === 'search') event.preventDefault(); });
		form.addEventListener('submit', function (event) { event.preventDefault(); });
		// Dipanggil tombol Proses; false = tidak ada transfer terpilih, lanjutkan proses dokumen.
		window.rpProcessTransfer = function () {
			if (picked === null) { return false; }
			$.post(<?= json_encode(site_url('pembayaran_material/proses_payment')) ?>, null, null, 'json').done(function (data) {
				if (!data || data.status !== 1 || data.count_choosed_payment > 0) {
					alert('Transfer tidak bisa digabung dengan dokumen lain. Kosongkan pilihan dan pilih ulang.');
					return;
				}
				window.location.href = <?= json_encode(site_url('pembayaran_material/transfer_payment')) ?> + '/' + picked;
			}).fail(function () { alert('Proses gagal. Silakan coba lagi.'); });
			return true;
		};
		// Angka tab lama = seluruh data, bukan hasil pencarian.
		$('#table_list_req_payment').on('xhr.dt', function (event, settings, json) {
			if (json && !$('#rp-tab-lama input[type=search]').val()) { $('[data-count="lama"]').text(json.recordsTotal); }
		});
		$('#rp-tabs a').on('shown.bs.tab', function () {
			var target = $(this).attr('href');
			$(target).find('table.dataTable').DataTable().columns.adjust();
			try { localStorage.setItem('rp_payment_tab', target); } catch (e) {}
		});
		try {
			var saved = localStorage.getItem('rp_payment_tab');
			if (saved === '#rp-tab-lama') { $('#rp-tabs a[href="#rp-tab-lama"]').tab('show'); }
		} catch (e) {}
	})();
</script>
<?php endif; ?>
