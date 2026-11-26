<?php // Modal Cetak Dokumen (mockup specs/docs/request-payment, fungsi printDoc). Dipakai tab Per Transaksi dan detail Record. ?>
<style>
	#rec-doc-modal .rec-doc-head { font-weight:600; border-bottom:1px solid #ddd; padding-bottom:4px }
	#rec-doc-modal .rec-doc-group { margin-top:10px }
	#rec-doc-modal .rec-doc-file { border:1px dashed #bbb; border-radius:4px; padding:10px; margin-top:6px; background:#fafafa; overflow-wrap:anywhere }
</style>
<div class="modal fade" id="rec-doc-modal" tabindex="-1" role="dialog" aria-labelledby="rec-doc-title">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Tutup">&times;</button>
				<h4 class="modal-title" id="rec-doc-title">Cetak Dokumen</h4>
			</div>
			<div class="modal-body" id="rec-doc-body" aria-live="polite"></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
				<a class="btn btn-primary" id="rec-doc-print" target="_blank" rel="noopener"><i class="fa fa-print"></i> Print</a>
			</div>
		</div>
	</div>
</div>
<script>
	$(function() {
		var sequence = 0;
		function esc(value) { return $('<div>').text(value == null ? '' : String(value)).html().replace(/"/g, '&quot;'); }
		function safe(url) { return typeof url === 'string' && /^(https?:\/\/|\/)/i.test(url); }
		function group(title, files) {
			return '<div class="rec-doc-group"><b>' + title + '</b>' + (files.length ? files.map(function(file) {
				var icon = /\.pdf$/i.test(file.name) ? 'fa-file-pdf-o' : /\.(jpe?g|png|gif|webp)$/i.test(file.name) ? 'fa-file-image-o' : 'fa-file-o';
				return '<div class="rec-doc-file"><i class="fa ' + icon + '"></i> ' + (safe(file.url)
					? '<a href="' + esc(file.url) + '" target="_blank" rel="noopener">' + esc(file.name) + '</a>'
					: esc(file.name) + ' <span class="text-muted">(berkas tidak tersedia)</span>') + '</div>';
			}).join('') : '<div class="text-muted" style="font-size:12px;margin-top:4px">Tidak ada.</div>') + '</div>';
		}
		$(document).on('click', '[data-pdoc]', function() {
			var number = $(this).attr('data-pdoc'), token = ++sequence;
			$('#rec-doc-title').text('Cetak Dokumen ' + number);
			$('#rec-doc-body').html('<p class="text-muted"><i class="fa fa-spinner fa-spin"></i> Memuat...</p>');
			$('#rec-doc-print').removeAttr('href').addClass('disabled');
			$('#rec-doc-modal').modal('show');
			$.getJSON(<?= json_encode(site_url('record_request_payment/document_info')) ?>, {id_pengajuan: $(this).attr('data-pengajuan'), no_dokumen: number})
				.done(function(res) {
					if (token !== sequence) return;
					if (!res || res.status !== 1) { $('#rec-doc-body').html('<p class="text-danger">' + esc(res && res.msg || 'Gagal memuat dokumen.') + '</p>'); return; }
					var d = res.document;
					$('#rec-doc-body').html('<table class="table table-bordered" style="font-size:12.5px;margin-bottom:6px"><tbody>' +
						'<tr><td style="width:160px">No. Dokumen</td><td><b>' + esc(d.no_dokumen) + '</b></td></tr>' +
						'<tr><td>Kategori</td><td>' + esc(d.kategori) + '</td></tr>' +
						'<tr><td>Request By</td><td>' + esc(d.request_by) + (d.company ? ' - ' + esc(d.company) : '') + '</td></tr>' +
						'<tr><td>Keperluan</td><td>' + esc(d.keperluan) + '</td></tr>' +
						'<tr><td>Dibayarkan</td><td>Rp ' + Number(d.dibayarkan || 0).toLocaleString('id-ID') + '</td></tr></tbody></table>' +
						'<div class="rec-doc-head">Lampiran</div>' +
						group('1. Lampiran awal (dari dokumen)', res.initial || []) +
						group('2. Lampiran tambahan dari Request Payment (Atur Pembayaran)', res.additional || []));
					if (safe(res.print_url)) { $('#rec-doc-print').attr('href', res.print_url).removeClass('disabled'); }
				})
				.fail(function() { if (token === sequence) $('#rec-doc-body').html('<p class="text-danger">Gagal memuat dokumen.</p>'); });
		});
	});
</script>
