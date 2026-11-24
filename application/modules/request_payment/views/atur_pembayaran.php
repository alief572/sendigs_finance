<style>
	#rp-atur .detail-head, #rp-atur .detail-foot, #rp-atur .summary { display:flex; justify-content:space-between; align-items:center; gap:20px; flex-wrap:wrap }
	#rp-atur .detail-head { border-bottom:1px solid #e1e5ea; margin-bottom:14px; padding-bottom:14px }
	#rp-atur .detail-foot { border-top:1px solid #e1e5ea; padding-top:14px }
	#rp-atur h3 { margin:0 0 4px; font-size:20px }
	#rp-atur .summary small, #rp-atur .trf-label { color:#657586; font-size:11px; font-weight:600; display:block }
	#rp-atur .summary strong { font-size:16px }
	#rp-atur .trf-panel { border:1px solid #d6dee7; border-radius:4px; margin:12px 0 }
	#rp-atur .trf-head { background:#f3f7fb; padding:10px 12px; display:flex; gap:10px; flex-wrap:wrap; align-items:center }
	.rp-transfer-tag { display:inline-block; border-radius:3px; background:#d9e9f5; color:#23547a; font-weight:600; padding:3px 7px; white-space:nowrap }
	#rp-atur .trf-body { padding:12px }
	#rp-atur thead th { background:#3c8dbc; color:white; white-space:nowrap }
	#rp-atur .trf-rek, #rp-atur .trf-fields, #rp-atur .trf-sum { display:grid; gap:12px; grid-template-columns:repeat(auto-fit,minmax(195px,1fr)); padding:10px }
	#rp-atur .trf-rek { background:#fff8e8; border:1px solid #f0dfb2; margin-bottom:8px }
	#rp-atur .trf-sum { background:#f2f6fa; font-weight:600 }
</style>
<section class="box" id="rp-atur" hidden aria-labelledby="rp-atur-title">
	<div class="box-body">
		<button type="button" id="rp-atur-back" class="btn btn-link">&larr; Kembali ke Request Payment</button>
		<div class="detail-head">
			<div><h3 id="rp-atur-title">Atur Pembayaran</h3><div class="text-muted">Company <b id="rp-atur-company"></b> &mdash; pengajuan baru, belum diajukan</div></div>
			<div class="summary text-right">
				<div><small>JUMLAH DOKUMEN</small><strong id="rp-atur-docs"></strong></div>
				<div><small>JUMLAH TRANSFER</small><strong id="rp-atur-count"></strong></div>
				<div><small>TOTAL KELUAR BANK</small><strong id="rp-atur-bank"></strong></div>
			</div>
		</div>
		<p class="text-muted">Default <b>1 dokumen = 1 transfer</b>. Centang dokumen yang mau dibayar sekaligus, gabungkan, lalu isi rekening tujuannya.</p>
		<button type="button" class="btn btn-default btn-sm" id="rp-atur-merge"><i class="fa fa-link"></i> Gabungkan jadi 1 transfer</button>
		<span class="text-muted" id="rp-atur-picked"></span>
		<form id="rp-atur-form">
			<div id="rp-atur-list"></div>
			<div class="alert alert-danger" id="rp-atur-error" role="alert" hidden></div>
			<div class="detail-foot">
				<div class="text-muted">Setelah diajukan, pengajuan masuk ke Approval Request Payment sebagai 1 batch.</div>
				<div>
					<button type="button" class="btn btn-default" id="rp-atur-cancel">Batal</button>
					<button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> Ajukan untuk Approval</button>
				</div>
			</div>
		</form>
	</div>
</section>
<script src="<?= base_url('assets/js/request-payment-transfer.js') ?>"></script>
