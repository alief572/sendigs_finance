<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<title><?= html_escape($doc->no_dokumen) ?></title>
	<style>
		body{font-family:Arial,sans-serif;margin:20px} iframe{width:100%;min-height:500px;border:1px solid #ddd}
		img,canvas{display:block;max-width:100%;height:auto;max-height:91vh;object-fit:contain} .attachment{margin:14px 0}
		@media print{.print-controls{display:none} .attachment, .print-pdf canvas+canvas{break-before:page} iframe{border:0}}
	</style>
</head>
<body>
	<div class="print-controls"><button type="button" id="rp-print" disabled>Print</button></div>
	<h1><?= html_escape($doc->no_dokumen) ?></h1>
	<h2>Dokumen dan lampiran awal</h2>
	<?php if ($original): ?>
		<p class="print-controls"><a href="<?= html_escape($original) ?>" target="_blank" rel="noopener">Buka dokumen asli</a></p>
		<div class="print-pdf" data-document="<?= html_escape($original) ?>" data-original></div>
	<?php endif; ?>
	<h2>Lampiran tambahan dari Request Payment</h2>
	<?php foreach ($files as $file): ?>
		<?php $url = site_url('record_request_payment/transfer_file/' . $file['id']); ?>
		<div class="attachment">
			<h3><a href="<?= html_escape($url) ?>" target="_blank" rel="noopener"><?= html_escape($file['nama']) ?></a></h3>
			<?php if (strpos($file['mime'], 'image/') === 0): ?>
				<img alt="<?= html_escape($file['nama']) ?>" src="<?= html_escape($url) ?>">
			<?php elseif ($file['mime'] === 'application/pdf'): ?>
				<div class="print-pdf" data-document="<?= html_escape($url) ?>"></div>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
<script src="<?= base_url('assets/js/pdfjs/pdf.min.js') ?>"></script>
<script>
(() => {
	const button = document.getElementById('rp-print');
	button.onclick = () => window.print();
	if (window.pdfjsLib) {
		pdfjsLib.GlobalWorkerOptions.workerSrc = <?= json_encode(base_url('assets/js/pdfjs/pdf.worker.min.js')) ?>;
	}
	function embed(wrapper, url, html) {
		const frame = document.createElement('iframe');
		frame.title = 'Dokumen dan lampiran awal';
		if (html === undefined) {
			url.searchParams.set('rp_preview', '1');
			frame.src = url.href;
		} else {
			frame.setAttribute('sandbox', 'allow-same-origin');
			frame.srcdoc = html;
		}
		return new Promise(resolve => {
			frame.addEventListener('load', () => {
				const body = frame.contentDocument && frame.contentDocument.body;
				if (body) {
					const resize = () => { frame.style.height = Math.max(500, body.scrollHeight + 20) + 'px'; };
					new ResizeObserver(resize).observe(body);
					resize();
				}
				resolve();
			}, { once: true });
			wrapper.appendChild(frame);
		});
	}
	async function render(wrapper) {
		const url = new URL(wrapper.dataset.document, window.location.href);
		if (wrapper.hasAttribute('data-original') && url.pathname.includes('/request_payment/print_cash/')) {
			return embed(wrapper, url);
		}
		const response = await fetch(url.href, { credentials: 'same-origin' });
		if (!response.ok) throw new Error('Dokumen tidak tersedia.');
		const type = response.headers.get('content-type') || '';
		if (type.includes('application/pdf')) {
			if (!window.pdfjsLib) throw new Error('Pembaca PDF tidak tersedia.');
			const pdf = await pdfjsLib.getDocument({ data: await response.arrayBuffer() }).promise;
			for (let index = 1; index <= pdf.numPages; index++) {
				const page = await pdf.getPage(index);
				const viewport = page.getViewport({ scale: 1.5 });
				const canvas = document.createElement('canvas');
				canvas.width = viewport.width;
				canvas.height = viewport.height;
				await page.render({ canvasContext: canvas.getContext('2d'), viewport }).promise;
				wrapper.appendChild(canvas);
			}
			await pdf.destroy();
			return;
		}
		if (!wrapper.hasAttribute('data-original') || !type.includes('text/html')) throw new Error('Lampiran perlu dibuka dari tautan.');
		return embed(wrapper, url, await response.text());
	}
	Promise.all(Array.from(document.querySelectorAll('[data-document]'), wrapper => render(wrapper).catch(() => {
		wrapper.textContent = 'Pratinjau gagal dimuat. Buka dokumen dari tautan di atas.';
	}))).finally(() => { button.disabled = false; });
})();
</script>
</body>
</html>
