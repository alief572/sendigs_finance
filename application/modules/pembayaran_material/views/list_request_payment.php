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
	#rp-transfer-picked { font-size:12.5px; font-weight:600; color:#2b3440; margin-right:6px }
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<?php if (!empty($rp_transfer_count)): ?>
	<?php $rp_tutorial_page = 'queue'; include dirname(__DIR__, 2) . '/request_payment/views/transfer_tutorial.php'; ?>
	<form class="box box-primary" id="rp-payment-selection">
		<div class="box-header with-border rp-list-head">
			<h4>Transfer dari Pengajuan (Request Payment)</h4>
			<div class="rp-list-actions">
				<span id="rp-transfer-picked" role="status" aria-live="polite"></span>
				<a href="<?= base_url('pembayaran_material/payment_list') ?>" class="btn btn-sm btn-danger"><i class="fa fa-arrow-left"></i> Back</a>
				<button type="button" class="btn btn-sm btn-warning" id="rp-transfer-clear"><i class="fa fa-refresh"></i> Clear Checked Payment</button>
				<button type="submit" class="btn btn-sm btn-success"><i class="fa fa-check"></i> Proses</button>
			</div>
		</div>
		<div class="box-body">
			<p class="rp-list-note">Untuk Request Payment, yang dipilih <b>per transfer</b> (1 baris = 1 kali transfer sesuai Excel pengajuan). 1 payment = 1 transfer. Transfer dari <b>PGJ berbeda tidak bisa digabung</b> jadi 1 payment; kalau dokumen ingin ditransfer sekaligus, ajukan di PGJ yang sama dan gabungkan di Atur Pembayaran.</p>
			<div class="table-responsive"><table class="table table-bordered rp-list" id="table_rp_transfer" style="width:100%">
				<thead><tr><th class="text-center">No</th><th class="text-center">No. Pengajuan</th><th class="text-center">Tgl Approve</th><th class="text-center">Company</th><th class="text-center">Penerima / Rekening Tujuan</th>
					<th class="text-center">No. Dokumen</th><th class="text-center">Currency</th><th class="text-center">Admin</th><th class="text-center">Jumlah Transfer</th><th class="text-center">Option</th></tr></thead>
				<tbody></tbody>
			</table></div>
		</div>
	</form>
<?php endif; ?>
<div class="box">
    <div class="box-header with-border rp-list-head">
        <?php if (!empty($rp_transfer_count)): ?><h4>Dokumen Lainnya (jalur lama &amp; Hutang Petty Cash)</h4><?php endif; ?>
        <div class="rp-list-actions">
            <a href="<?= base_url('pembayaran_material/payment_list') ?>" class="btn btn-sm btn-danger"><i class="fa fa-arrow-left"></i> Back</a>
            <button type="button" class="btn btn-sm btn-warning clear_choosed_payment"><i class="fa fa-refresh"></i> Clear Checked Payment</button>
            <button type="button" class="btn btn-sm btn-success proses_payment"><i class="fa fa-check"></i> Proses</button>
        </div>
    </div>
    <div class="box-body">
        <div class="table-responsive"><table class="table table-bordered rp-list" id="table_list_req_payment" style="width:100%" data-jenis-payment="<?= htmlspecialchars((string) $jenis_payment, ENT_QUOTES, 'UTF-8') ?>">
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
            <tbody>

            </tbody>
        </table></div>
    </div>
</div>

<script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>

<script>
	$.extend(true, $.fn.dataTable.defaults, {language: {
		search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ baris', processing: 'Memuat daftar pembayaran...',
		info: 'Menampilkan _START_ - _END_ dari _TOTAL_', infoEmpty: 'Tidak ada data', infoFiltered: '(disaring dari _MAX_)',
		emptyTable: 'Belum ada pembayaran yang menunggu diproses.',
		zeroRecords: 'Tidak ada yang cocok dengan pencarian. Ubah kata kunci atau kosongkan kotak Cari.'
	}});
</script>
<script src="<?= base_url('assets/js/pembayaran_material/payment_selection.js') ?>"></script>
<?php if (!empty($rp_transfer_count)): ?>
<script>
	(function () {
		var form = document.getElementById('rp-payment-selection');
		// Pilihan disimpan di luar tabel supaya tetap ada saat pindah halaman atau mencari.
		var picked = null;
		var summary = document.getElementById('rp-transfer-picked');
		function select(id, label) {
			picked = id;
			summary.textContent = label ? 'Dipilih: ' + label : '';
		}
		var table = $('#table_rp_transfer').DataTable({
			serverSide: true,
			processing: true,
			order: [],
			ajax: {type: 'POST', url: <?= json_encode(site_url('pembayaran_material/get_list_rp_transfer')) ?>},
			columns: [
				{data: 'no', orderable: false}, {data: 'no_pengajuan'}, {data: 'tgl'}, {data: 'company'}, {data: 'penerima'},
				{data: 'dokumen', orderable: false}, {data: 'currency', orderable: false}, {data: 'admin', orderable: false},
				{data: 'jumlah', className: 'text-right'}, {data: 'option', orderable: false, className: 'text-center'}
			],
			drawCallback: function () {
				$(form).find('input[name="transfer"]').each(function () { this.checked = this.value === picked; });
			}
		});
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
		document.getElementById('rp-transfer-clear').addEventListener('click', function () {
			if (!confirm('Seluruh pilihan pembayaran Anda akan dikosongkan.')) return;
			select(null);
			$(form).find('input[name="transfer"]').prop('checked', false);
		});
		form.addEventListener('submit', function (event) {
			event.preventDefault();
			if (picked === null) { alert('Pilih minimal satu transaksi pembayaran.'); return; }
			window.location.href = <?= json_encode(site_url('pembayaran_material/transfer_payment')) ?> + '/' + picked;
		});
	})();
</script>
<?php endif; ?>
