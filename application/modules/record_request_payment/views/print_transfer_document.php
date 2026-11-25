<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<title><?= html_escape($doc->no_dokumen) ?></title>
	<style>
		body{font-family:Arial,sans-serif;margin:20px} iframe{width:100%;min-height:500px;border:1px solid #ddd}
		img,canvas{display:block;max-width:100%;height:auto;max-height:91vh;object-fit:contain} .attachment{margin:14px 0}
		@media print{.attachment, .print-pdf canvas+canvas{break-before:page} iframe{border:0}}
	</style>
</head>
<body>
	<?php if ($original): ?>
		<div class="print-pdf" data-document="<?= html_escape($original) ?>" data-original></div>
	<?php endif; ?>
	<?php foreach ($files as $file): ?>
		<?php $url = site_url('record_request_payment/transfer_file/' . $file['id']); ?>
		<div class="attachment">
			<?php if (strpos($file['mime'], 'image/') === 0): ?>
				<img alt="<?= html_escape($file['nama']) ?>" src="<?= html_escape($url) ?>">
			<?php elseif ($file['mime'] === 'application/pdf'): ?>
				<div class="print-pdf" data-document="<?= html_escape($url) ?>"></div>
			<?php else: ?>
				<?php // docx/xlsx tidak bisa ditampilkan browser: sediakan tautan unduh. ?>
				<p>Lampiran tidak bisa ditampilkan: <a href="<?= html_escape($url) ?>" target="_blank" rel="noopener"><?= html_escape($file['nama']) ?></a></p>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
<script src="<?= base_url('assets/js/pdfjs/pdf.min.js') ?>"></script>
<script>
(() => {
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
	// Cetak otomatis setelah semua dokumen dan gambar siap; kalau ada yang gagal, jangan cetak setengah jadi.
	const documents = Array.from(document.querySelectorAll('[data-document]'), wrapper => render(wrapper).then(() => true, () => {
		const link = document.createElement('a');
		link.href = wrapper.dataset.document;
		link.target = '_blank';
		link.rel = 'noopener';
		link.textContent = 'Pratinjau gagal dimuat. Buka dokumennya di sini.';
		wrapper.replaceChildren(link);
		return false;
	}));
	const images = Array.from(document.images, image => image.decode().then(() => true, () => false));
	Promise.all(documents.concat(images)).then(results => {
		if (results.every(Boolean)) window.print();
	});
})();
</script>
</body>
</html>
