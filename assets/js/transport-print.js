(function () {
    'use strict';

    var workerUrl = document.currentScript.getAttribute('data-worker-url');

    function waitForImage(image) {
        return new Promise(function (resolve, reject) {
            function loaded() {
                if (image.naturalWidth > 0) resolve();
                else reject(new Error('Lampiran gambar gagal dimuat'));
            }
            if (image.complete) {
                loaded();
            } else {
                image.addEventListener('load', loaded, { once: true });
                image.addEventListener('error', reject, { once: true });
            }
        });
    }

    async function renderPdf(wrapper) {
        var url = wrapper.getAttribute('data-pdf-url');
        var notice = wrapper.querySelector('.transport-pdf-notice');
        var pdf;
        try {
            if (typeof pdfjsLib === 'undefined') throw new Error('PDF.js tidak tersedia');
            pdf = await pdfjsLib.getDocument(url).promise;
            for (var number = 1; number <= pdf.numPages; number++) {
                var page = await pdf.getPage(number);
                var original = page.getViewport({ scale: 1 });
                // Leave 1mm of vertical space for print rounding within A4's 190 x 277mm area.
                var fit = Math.min(190 / original.width, 276 / original.height);
                var viewport = page.getViewport({ scale: fit * 150 / 25.4 });
                var canvas = document.createElement('canvas');
                canvas.width = Math.ceil(viewport.width);
                canvas.height = Math.ceil(viewport.height);
                canvas.style.width = original.width * fit + 'mm';
                canvas.style.height = original.height * fit + 'mm';
                canvas.setAttribute('role', 'img');
                canvas.setAttribute('aria-label', 'Lampiran PDF halaman ' + number);
                await page.render({ canvasContext: canvas.getContext('2d'), viewport: viewport }).promise;

                var container = document.createElement('div');
                container.className = 'transport-pdf-page';
                container.appendChild(canvas);
                wrapper.appendChild(container);
                page.cleanup();
            }
            notice.remove();
            return true;
        } catch (error) {
            wrapper.querySelectorAll('.transport-pdf-page').forEach(function (page) { page.remove(); });
            notice.textContent = 'Lampiran PDF gagal dimuat. Periksa file asli: ';
            var link = document.createElement('a');
            link.href = url;
            link.target = '_blank';
            link.rel = 'noopener';
            link.textContent = 'Buka lampiran PDF';
            notice.appendChild(link);
            return false;
        } finally {
            if (pdf) {
                try { await pdf.destroy(); } catch (error) { /* Rendered canvases remain usable. */ }
            }
        }
    }

    async function initPrint() {
        var button = document.getElementById('transport-print-button');
        var status = document.getElementById('transport-print-status');
        button.addEventListener('click', function () { window.print(); });
        if (typeof pdfjsLib !== 'undefined') pdfjsLib.GlobalWorkerOptions.workerSrc = workerUrl;

        var images = Array.from(document.querySelectorAll('.attachment-img'));
        // Attach image error handlers before awaiting any PDF downloads.
        var imagesReady = Promise.all(images.map(waitForImage)).then(function () { return true; }, function () { return false; });
        var wrappers = document.querySelectorAll('.transport-pdf-attachment');
        var ready = true;
        for (var index = 0; index < wrappers.length; index++) {
            if (!await renderPdf(wrappers[index])) ready = false;
        }
        if (!await imagesReady) ready = false;
        if (!ready) {
            status.textContent = 'Lampiran gagal dimuat. Cetak otomatis dibatalkan. Periksa file lampiran lalu muat ulang halaman.';
            return;
        }

        status.textContent = 'Dokumen dan seluruh lampiran siap dicetak.';
        button.disabled = false;
        requestAnimationFrame(function () {
            requestAnimationFrame(function () { window.print(); });
        });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initPrint, { once: true });
    else initPrint();
})();
