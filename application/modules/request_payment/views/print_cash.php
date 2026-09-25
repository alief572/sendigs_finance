<?php
function formatDate($date)
{
    if (empty($date) || $date == '0000-00-00') return '-';
    return date('d-M-y', strtotime($date));
}
?>
<html>

<head>
    <title><?= $title ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/AdminLTE/bootstrap/css/bootstrap.min.css') ?>">
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
            padding: 15px;
            margin: 0;
        }

        .document-header {
            text-align: center;
            padding: 8px 0;
            border-top: 2px solid #333;
            border-bottom: 1px solid #333;
            margin-bottom: 10px;
        }

        .document-header h4 {
            margin: 0 0 3px 0;
            font-size: 13px;
            font-weight: bold;
        }

        .document-header p {
            margin: 0;
            font-size: 11px;
        }

        table.info-table {
            width: 100%;
            border-collapse: collapse;
        }

        table.info-table td {
            padding: 2px 5px;
            vertical-align: top;
            font-size: 11px;
        }

        table.detail-table {
            border-collapse: collapse;
            width: 100%;
            font-size: 11px;
        }

        table.detail-table th,
        table.detail-table td {
            border: 1px solid #333;
            padding: 4px 6px;
        }

        table.detail-table th {
            text-align: center;
            font-weight: bold;
            font-style: italic;
        }

        .section-title {
            font-weight: bold;
            margin: 10px 0 3px 0;
            font-size: 11px;
        }

        .signature-table td {
            text-align: center;
            padding: 5px 20px;
            vertical-align: top;
            font-size: 11px;
        }

        .bank-signature-wrapper {
            display: table;
            width: 100%;
            margin-top: 8px;
        }

        .bank-section {
            display: table-cell;
            vertical-align: top;
            width: 35%;
        }

        .signature-section {
            display: table-cell;
            vertical-align: top;
            width: 65%;
            text-align: center;
        }

        .attachment-separator {
            border-top: 3px solid #4CAF50;
            margin: 20px 0 15px 0;
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
</head>

<body>

    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button type="button" onclick="window.print()" class="btn-print-action">
            <i class="glyphicon glyphicon-print"></i> Cetak Dokumen
        </button>
    </div>

    <div class="document-header">
        <h4>Pengajuan Direct Payment</h4>
        <p><?= $pr_header->no_pr ?? '' ?> - <?= $data_pr->no_non_po ?? '' ?></p>
    </div>

    <!-- Informasi Pengajuan -->
    <div class="section-title">Informasi Pengajuan</div>
    <table class="info-table">
        <tr>
            <td width="100">Department</td>
            <td width="5">:</td>
            <td width="150"><?= $dept_name !== '' ? $dept_name : '-' ?></td>
            <td width="40"></td>
            <td width="110">Project Name</td>
            <td width="5">:</td>
            <td><?= !empty($pr_header->project_name) ? $pr_header->project_name : '-' ?></td>
        </tr>
        <tr>
            <td>Request By</td>
            <td>:</td>
            <td><?= $request_by !== '' ? $request_by : '-' ?></td>
            <td></td>
            <td>COA</td>
            <td>:</td>
            <td><?= $coa_display !== '' ? $coa_display : '-' ?></td>
        </tr>
        <tr>
            <td>Tanggal PR</td>
            <td>:</td>
            <td><?= formatDate($pr_header->created_date ?? '') ?></td>
            <td></td>
            <td>Approval PR</td>
            <td>:</td>
            <td><?= formatDate($pr_header->app_3_date ?? '') ?></td>
        </tr>
        <tr>
            <td>Tanggal Direct Payment</td>
            <td>:</td>
            <td><?= formatDate($data_pr->created_date ?? '') ?></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <!-- Detail Pengajuan -->
    <div class="section-title">Detail Pengajuan</div>
    <table class="detail-table">
        <thead>
            <tr>
                <th width="30">No</th>
                <th>Nama Barang / Jasa</th>
                <th>Spec / Requirement</th>
                <th width="40">Qty</th>
                <th width="100">Harga</th>
                <th width="110">Tanggal Dibutuhkan</th>
                <th width="100">Total Harga</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $grand_total = 0;
            $no = 1;
            foreach ($pr_details as $detail) :
                $qty = isset($detail['qty']) && $detail['qty'] !== null ? $detail['qty'] : 0;
                $harga = isset($detail['harga']) && $detail['harga'] !== null ? $detail['harga'] : 0;
                $total_harga = $qty * $harga;
                $grand_total += $total_harga;
            ?>
                <tr>
                    <td style="text-align: center;"><?= $no++ ?></td>
                    <td><?= $detail['nm_barang'] ?></td>
                    <td><?= $detail['spec'] ?></td>
                    <td style="text-align: center;"><?= $detail['qty'] ?></td>
                    <td style="text-align: right;"><?= 'Rp ' . number_format($harga, 0, ',', '.') ?></td>
                    <td style="text-align: center;"><?= formatDate($detail['tanggal']) ?></td>
                    <td style="text-align: right;"><?= 'Rp ' . number_format($total_harga, 0, ',', '.') ?></td>
                    <td><?= !empty($detail['keterangan']) ? $detail['keterangan'] : '' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="border: none; border-top: 1px solid #333;"></td>
                <td style="text-align: center; border-top: 1px solid #333; border-left: none; border-bottom: none; border-right: none;">Total</td>
                <td style="border: none; border-top: 1px solid #333;"></td>
                <td style="text-align: right; border: 1px solid #333;"><?= 'Rp ' . number_format($grand_total, 0, ',', '.') ?></td>
                <td style="border: none; border-top: 1px solid #333;"></td>
            </tr>
        </tfoot>
    </table>

    <!-- Informasi Bank + Signature -->
    <div class="bank-signature-wrapper">
        <div class="bank-section">
            <div class="section-title">Informasi Bank</div>
            <table class="info-table">
                <tr>
                    <td width="90">Bank</td>
                    <td width="5">:</td>
                    <td><?= !empty($bank_name) ? $bank_name : '-' ?></td>
                </tr>
                <tr>
                    <td>No Rekening</td>
                    <td>:</td>
                    <td><?= !empty($bank_account_no) ? $bank_account_no : '-' ?></td>
                </tr>
                <tr>
                    <td>Nama Rekening</td>
                    <td>:</td>
                    <td><?= !empty($bank_account_name) ? $bank_account_name : '-' ?></td>
                </tr>
            </table>
        </div>
        <div class="signature-section">
            <table class="signature-table" style="margin: 0 auto;">
                <tr>
                    <td style="width: 150px;"><strong>Finance</strong></td>
                    <td style="width: 150px;"><strong>Management</strong></td>
                </tr>
                <tr>
                    <td style="height: 50px;"></td>
                    <td style="height: 50px;"></td>
                </tr>
                <tr>
                    <td><u>Fikri</u><br><small><?= formatDate($pr_header->app_2_date ?? '') ?></small></td>
                    <td><u>Imanuel Iman</u><br><small><?= formatDate($pr_header->app_3_date ?? '') ?></small></td>
                </tr>
            </table>
        </div>
    </div>

    <?php if (!empty($pr_header->document)) : ?>
        <?php
        $doc_files = [];
        $decoded = json_decode($pr_header->document, true);
        if (is_array($decoded)) {
            $doc_files = $decoded;
        } else {
            $doc_files = [$pr_header->document];
        }
        foreach ($doc_files as $doc_item) :
            $ext = strtolower(pathinfo($doc_item, PATHINFO_EXTENSION));
            if ($ext == 'pdf') : ?>
                <div class="pdf-attachment-wrapper" data-pdf-url="<?= base_url('assets/pr/' . $doc_item) ?>">
                    <div class="pdf-loading-notice no-print" style="text-align: center; padding: 15px; background: #f8f9fa; border: 1px dashed #bbb; margin: 20px 0; font-size: 12px; color: #555;">
                        <i class="glyphicon glyphicon-refresh"></i> Memproses lampiran PDF (<?= htmlspecialchars($doc_item) ?>)... Mohon tunggu sebentar.
                    </div>
                </div>
            <?php elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) : ?>
                <div class="pagebreak">
                    <img src="<?= base_url('assets/pr/' . $doc_item) ?>" class="attachment-img">
                </div>
            <?php endif;
        endforeach;
        ?>
    <?php endif; ?>

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