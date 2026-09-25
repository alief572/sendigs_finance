<?php
$nmuser = (!empty($data_pr->nm_pic)) ? $data_pr->nm_pic : '';
?>
<html>

<head>
    <title> PEMBELIAN CASH </title>
</head>

<body>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
            padding: 15px;
            margin: 0;
            background: #fff;
        }

        table.garis {
            border-collapse: collapse;
            font-size: 0.9em;
            font-family: sans-serif;
        }

        .pdf-page-container {
            margin: 20px auto;
            text-align: center;
            background: #fff;
        }

        .pdf-page-image {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .attachment-img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 15px auto;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .btn-print-action {
            background-color: #3c8dbc;
            border: 1px solid #367fa9;
            color: #fff;
            padding: 6px 16px;
            font-size: 13px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-print-action:hover {
            background-color: #357ca5;
        }

        @media print {
            @page {
                size: auto;
                margin: 8mm 10mm;
            }

            body {
                padding: 0;
                margin: 0;
                background: #fff;
            }

            .no-print {
                display: none !important;
            }

            .pagebreak {
                page-break-before: always !important;
                break-before: page !important;
            }

            .pdf-page-container {
                page-break-before: always !important;
                break-before: page !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                margin: 0 !important;
                padding: 0 !important;
                text-align: center !important;
            }

            .pdf-page-image {
                max-width: 100% !important;
                max-height: 275mm !important;
                width: auto !important;
                height: auto !important;
                display: block !important;
                margin: 0 auto !important;
                box-shadow: none !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .attachment-img {
                max-width: 100% !important;
                max-height: 275mm !important;
                width: auto !important;
                height: auto !important;
                display: block !important;
                margin: 0 auto !important;
                box-shadow: none !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }
    </style>
    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button type="button" onclick="window.print()" class="btn-print-action">
            Cetak Dokumen
        </button>
    </div>
    <table cellpadding=2 cellspacing=0 border=0 width=650>
        <tr>
            <th colspan=6>PEMBELIAN CASH<br /><br /><br /></th>
        </tr>
        <tr>
            <td nowrap colspan=2>No Dokumen : <?= $data_pr->no_non_po ?></td>
            <td nowrap colspan=2>Total : <?= number_format($data_pr->total_pr) ?></td>
            <td nowrap colspan=2>Tanggal : <?= date('d F Y', strtotime($v_req_payment->tanggal)) ?></td>
        </tr>
        <tr>
            <th colspan=6><br /></th>
        </tr>
        <tr>
            <td valign=top width=100>Keperluan</td>
            <td valign=top colspan=5>: <?= $v_req_payment->keperluan ?></td>
        </tr>
        <tr>
            <td height=60 colspan=6></td>
        </tr>
        <tr>
            <td colspan=2 align=center>Mengajukan</td>
            <td colspan=2 rowspan=3></td>
            <td colspan=2 align=center>Mengetahui</td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <?php
        $mengajukan = $this->db->query("SELECT a.nm_lengkap as name FROM users a WHERE a.username='" . $data_pr->created_by . "'")->row();
        $mengetahui = new stdClass();
        $mengetahui->name = "FINANCE";
        ?>
        <tr height=120>
            <td colspan=2 align=center nowrap valign="bottom">
                <u>&nbsp; &nbsp; <?= (($nmuser) ? $nmuser : ' &nbsp; &nbsp;  &nbsp; &nbsp;  &nbsp; &nbsp; ') ?> &nbsp; &nbsp; </u><br><?= date('d F Y'); ?>
            </td>
            <td colspan=2 align=center nowrap valign="bottom">
                <u>&nbsp; &nbsp; <?= (($mengetahui) ? $mengetahui->name : ' &nbsp; &nbsp;  &nbsp; &nbsp;  &nbsp; &nbsp; ') ?> &nbsp; &nbsp; </u><br><?= date('d F Y'); ?>
            </td>
        </tr>
    </table><br /><br />
    <?php
    if (isset($doc_pr) && !empty($doc_pr->doc_file)) {
        $ext = strtolower(pathinfo($doc_pr->doc_file, PATHINFO_EXTENSION));
        if ($ext == 'pdf' || strpos($doc_pr->doc_file, '.pdf') !== false) {
            echo '<div class="pdf-attachment-wrapper" data-pdf-url="' . base_url($doc_pr->doc_file) . '">
                <div class="pdf-loading-notice no-print" style="text-align: center; padding: 15px; background: #f8f9fa; border: 1px dashed #bbb; margin: 20px 0; font-size: 12px; color: #555;">
                    Memproses lampiran PDF (' . htmlspecialchars(basename($doc_pr->doc_file)) . ')... Mohon tunggu sebentar.
                </div>
            </div>';
        } else {
            echo '<div class="pagebreak">
                <div class="col-md-12"><img src="' . base_url($doc_pr->doc_file) . '" class="attachment-img"><br /><div style="text-align:center;">' . htmlspecialchars($doc_pr->no_doc) . '</div></div>
            </div>';
        }
    }
    ?>
    <script src="<?= base_url('assets/js/pdfjs/pdf.min.js') ?>"></script>
    <script>
        if (typeof pdfjsLib === 'undefined') {
            document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"><\/script>');
        }
    </script>
    <script>
        if (typeof pdfjsLib !== 'undefined') {
            pdfjsLib.GlobalWorkerOptions.workerSrc = '<?= base_url("assets/js/pdfjs/pdf.worker.min.js") ?>';
        }

        async function renderPdfWrapper(wrapper) {
            var url = wrapper.getAttribute('data-pdf-url');
            if (!url) return;

            try {
                var loadingTask = pdfjsLib.getDocument({
                    url: url,
                    cMapUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/cmaps/',
                    cMapPacked: true
                });
                var pdf = await loadingTask.promise;
                var totalPages = pdf.numPages;

                for (var pageNum = 1; pageNum <= totalPages; pageNum++) {
                    var page = await pdf.getPage(pageNum);
                    var scale = 2.0; // High resolution rendering for clear, sharp printout
                    var viewport = page.getViewport({ scale: scale });

                    var canvas = document.createElement('canvas');
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;
                    var ctx = canvas.getContext('2d');

                    await page.render({
                        canvasContext: ctx,
                        viewport: viewport
                    }).promise;

                    var pageDiv = document.createElement('div');
                    pageDiv.className = 'pdf-page-container pagebreak';

                    var img = document.createElement('img');
                    img.src = canvas.toDataURL('image/png');
                    img.className = 'pdf-page-image';
                    img.alt = 'Halaman ' + pageNum;

                    pageDiv.appendChild(img);
                    wrapper.appendChild(pageDiv);
                }

                var notice = wrapper.querySelector('.pdf-loading-notice');
                if (notice) notice.remove();
            } catch (err) {
                console.error('Error saat merender PDF:', err);
                var notice = wrapper.querySelector('.pdf-loading-notice');
                if (notice) {
                    notice.innerHTML = '<span style="color:#d9534f;">Lampiran PDF tidak dapat dimuat otomatis: <a href="' + url + '" target="_blank">' + url.split("/").pop() + '</a></span>';
                    notice.classList.remove('no-print');
                }
            }
        }

        async function initPrint() {
            var pdfWrappers = document.querySelectorAll('.pdf-attachment-wrapper');
            if (pdfWrappers.length > 0 && typeof pdfjsLib !== 'undefined') {
                for (var i = 0; i < pdfWrappers.length; i++) {
                    await renderPdfWrapper(pdfWrappers[i]);
                }
            }

            // Beri jeda sedikit agar browser render gambar sebelum dialog print muncul
            setTimeout(function() {
                window.print();
            }, 600);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initPrint);
        } else {
            initPrint();
        }
    </script>
</body>

</html>