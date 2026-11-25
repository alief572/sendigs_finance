<?php
$rp_tours = [
	'request' => [['#table_rp', 'Pilih dokumen', 'Centang dokumen dari satu company dan atur PPN atau PPh untuk tiap dokumen.'],
		['#rp_bulk_bar', 'Atur Pembayaran & Ajukan', 'Buka pengaturan rekening dan pengelompokan transfer.']],
	'arrange' => [['#rp-atur-count', 'Ringkasan', 'Periksa jumlah dokumen, transfer, dan total yang keluar dari bank.'],
		['#rp-atur-merge', 'Gabungkan transfer', 'Centang dokumen yang dibayar sekaligus, lalu isi rekening tujuan baru.'],
		['#rp-atur-list', 'Rekening, admin, dan lampiran', 'Periksa rekening, pilih penanggung admin, dan tambahkan lampiran bila perlu.'],
		['#rp-atur-form [type=submit]', 'Ajukan untuk Approval', 'Simpan satu PGJ untuk company ini.']],
	'approval' => [['#appr_search', 'Menunggu Approval', 'Cari PGJ yang perlu diputuskan, lalu buka detailnya.']],
	'decision' => [['[data-transfer]', 'Rincian Transfer', 'Periksa rekening tujuan. Approver bisa mengoreksinya sebelum simpan.'],
		['#table_approve', 'Setujui atau tolak per dokumen', 'Isi alasan jika menolak. Total transfer mengikuti dokumen yang disetujui.']],
	'record' => [['#table_record', 'Record Request Payment', 'Lihat status pembayaran dan cetak Excel per PGJ.'],
		['#rp-export-open', 'Export Excel Beberapa PGJ', 'Filter tanggal diputuskan, kategori, dan status pembayaran.']],
	'payment' => [['.choose_payment', 'Payment', 'Pilih Pembayaran Non PR untuk memproses satu transfer yang disetujui.'],
		['a[href*="/record_payment"]', 'Record Payment', 'Bandingkan nilai rencana dan aktual yang tercatat.']],
	'queue' => [['#rp-payment-selection', 'List Request Payment', 'Centang satu transfer dari PGJ approved, lalu klik Proses.']],
	'form' => [['#rp-pay-bank', 'Bank pembayar', 'Pilih rekening bank untuk company ini dan cocokkan bukti bayar.'],
		['#rp-pay-charge', 'Bank Charge', 'Payment Bank = nominal transfer. Bank Charge = biaya yang dipotong bank, default dari admin pengajuan, bisa diedit sesuai bukti bayar. Administrasi Bank dihitung otomatis; isi keterangan bila ada selisih.'],
		['#rp-pay-proof', 'Bukti bayar', 'Unggah bukti sebelum Submit.'],
		['#rp-pay-journal', 'Jurnal', 'Periksa satu nomor BK dan debit kredit yang seimbang. Posting melalui Jurnal All.']],
	'history' => [['.nav-tabs', 'Record Payment', 'Gunakan tab untuk melihat selisih antara pengajuan dan bukti bayar.']],
];
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/driver/driver.css') ?>">
<script src="<?= base_url('assets/plugins/driver/driver.js.iife.js') ?>"></script>
<div class="dropdown text-right" style="margin-bottom:8px" id="rp-transfer-tutorial">
	<button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		<i class="fa fa-question-circle"></i> Tutorial <span class="caret"></span>
	</button>
	<ul class="dropdown-menu dropdown-menu-right">
		<li><button class="btn btn-link" type="button" data-rp-tour="page">Tutorial halaman ini</button></li>
		<li><button class="btn btn-link" type="button" data-rp-tour="approval">Alur Request Payment sampai Record</button></li>
		<li><button class="btn btn-link" type="button" data-rp-tour="payment">Alur Payment sampai Record Payment</button></li>
	</ul>
</div>
<script>
(() => {
	const page = <?= json_encode($rp_tutorial_page) ?>;
	const guides = <?= json_encode($rp_tours, JSON_HEX_TAG) ?>;
	const flows = {
		approval: [
			['Pilih dokumen', 'Centang dokumen satu company dan atur PPN atau PPh.'],
			['Atur Pembayaran', 'Gabung atau pisah transfer. Isi rekening dan penanggung admin.'],
			['Approval Request Payment', 'Periksa rekening tujuan, setujui atau tolak tiap dokumen, lalu simpan keputusan.'],
			['Record Request Payment', 'Cetak Excel Plan Cash Flow sebagai acuan transfer.']
		],
		payment: [
			['Pilih Jenis Payment', 'Buka Payment dan pilih Pembayaran Non PR.'],
			['List Request Payment', 'Pilih satu transfer dari PGJ yang disetujui.'],
			['Form Payment', 'Pilih bank, unggah bukti bayar, isi aktual dan periksa jurnal sebelum Submit.'],
			['Record Payment', 'Periksa selisih pengajuan dan realisasi. Posting jurnal melalui Jurnal All.']
		]
	};
	document.getElementById('rp-transfer-tutorial').addEventListener('click', event => {
		const button = event.target.closest('[data-rp-tour]');
		if (!button) return;
		if (!window.driver || !window.driver.js) {
			alert('Tutorial belum termuat.');
			return;
		}
		const kind = button.dataset.rpTour;
		const arrange = document.getElementById('rp-atur');
		const current = arrange && !arrange.hidden ? 'arrange' : page;
		const steps = kind === 'page' ? guides[current].map(([selector, title, description]) => {
			const element = document.querySelector(selector);
			return { element: element && element.getClientRects().length ? element : undefined, popover: { title, description } };
		}) : flows[kind].map(([title, description]) => ({ popover: { title, description } }));
		window.driver.js.driver({ showProgress: true, progressText: '{{current}} dari {{total}}',
			nextBtnText: 'Lanjut', prevBtnText: 'Kembali', doneBtnText: 'Selesai', steps }).drive();
	});
})();
</script>
