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

        .attachment-img {
            max-width: 100%;
            margin: 10px 0;
        }

        .pdf-page-canvas {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 10px auto;
            border: 1px solid #ddd;
        }

        @media print {
            .pagebreak {
                page-break-before: always;
            }

            body {
                padding: 10px;
            }

            .pdf-page-canvas {
                max-width: 100% !important;
                height: auto !important;
                page-break-inside: avoid;
                border: none;
                margin: 10px auto;
            }
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
</head>

<body>

    <!-- Document Header -->
    <div class="document-header">
        <h4>Pengajuan Kasbon</h4>
        <p><?= $pr_asset->no_pr ?? '' ?> - <?= $kasbon->no_doc ?></p>
    </div>

    <!-- Informasi Pengajuan -->
    <div class="section-title">Informasi Pengajuan</div>
    <table class="info-table">
        <tr>
            <td width="100">Department</td>
            <td width="5">:</td>
            <td width="150"><?= !empty($dept_name) ? $dept_name : '-' ?></td>
            <td width="40"></td>
            <td width="110">No PR</td>
            <td width="5">:</td>
            <td><?= !empty($pr_asset->no_pr) ? $pr_asset->no_pr : '-' ?></td>
        </tr>
        <tr>
            <td>Request By</td>
            <td>:</td>
            <td><?= !empty($request_by) ? $request_by : '-' ?></td>
            <td></td>
            <td>COA</td>
            <td>:</td>
            <td><?= !empty($pr_asset->no_coa) ? $pr_asset->no_coa . ' ' . $pr_asset->nm_coa : '-' ?></td>
        </tr>
        <tr>
            <td>Tanggal Kasbon</td>
            <td>:</td>
            <td><?= formatDate($kasbon->tgl_doc) ?></td>
            <td></td>
            <td>Approval Kasbon</td>
            <td>:</td>
            <td><?= formatDate($kasbon->approved_on ?? '') ?></td>
        </tr>
    </table>

    <!-- Detail Pengajuan -->
    <div class="section-title">Detail Pengajuan</div>
    <table class="detail-table">
        <thead>
            <tr>
                <th width="30">No</th>
                <th>Nama Asset</th>
                <th width="50">Qty</th>
                <th width="120">Budget (Harga)</th>
                <th width="120">Total</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $qty = !empty($pr_asset->qty) ? $pr_asset->qty : 0;
            $budget = !empty($pr_asset->budget) ? $pr_asset->budget : 0;
            $total = $qty * $budget;
            ?>
            <tr>
                <td style="text-align: center;">1</td>
                <td><?= !empty($pr_asset->nama_asset) ? $pr_asset->nama_asset : '-' ?></td>
                <td style="text-align: center;"><?= $qty ?></td>
                <td style="text-align: right;"><?= 'Rp ' . number_format($budget, 0, ',', '.') ?></td>
                <td style="text-align: right;"><?= 'Rp ' . number_format($total, 0, ',', '.') ?></td>
                <td><?= !empty($pr_asset->keterangan) ? $pr_asset->keterangan : '' ?></td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="border: none; border-top: 1px solid #333;"></td>
                <td style="text-align: center; border-top: 1px solid #333; border-left: none; border-bottom: none; border-right: none;">Total</td>
                <td style="text-align: right; border: 1px solid #333;"><?= 'Rp ' . number_format($total, 0, ',', '.') ?></td>
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
                    <td><?= !empty($kasbon->bank_id) ? $kasbon->bank_id : '-' ?></td>
                </tr>
                <tr>
                    <td>No Rekening</td>
                    <td>:</td>
                    <td><?= !empty($kasbon->accnumber) ? $kasbon->accnumber : '-' ?></td>
                </tr>
                <tr>
                    <td>Nama Rekening</td>
                    <td>:</td>
                    <td><?= !empty($kasbon->accname) ? $kasbon->accname : '-' ?></td>
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
                    <td><u>Fikri</u><br><small><?= formatDate($kasbon->approved_on ?? '') ?></small></td>
                    <td><u>Imanuel Iman</u><br><small><?= formatDate($kasbon->approved_on ?? '') ?></small></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Attachments -->
    <?php if (!empty($kasbon->doc_file)) : ?>
        <?php $ext = strtolower(pathinfo($kasbon->doc_file, PATHINFO_EXTENSION)); ?>
        <div class="attachment-separator"></div>
        <?php if ($ext == 'pdf') : ?>
            <?php $pdf_url = base_url('assets/expense/' . $kasbon->doc_file); ?>
            <div class="pdf-render-container" data-pdf-url="<?= $pdf_url ?>">
                <iframe src="<?= $pdf_url ?>#toolbar=0&navpanes=0" title="PDF" style="width:100%; height:600px;" frameborder="0">
                    <a href="<?= $pdf_url ?>">Download PDF</a>
                </iframe>
            </div>
        <?php elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) : ?>
            <img src="<?= base_url('assets/expense/' . $kasbon->doc_file) ?>" class="attachment-img">
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!empty($kasbon->doc_file_2)) : ?>
        <?php $ext2 = strtolower(pathinfo($kasbon->doc_file_2, PATHINFO_EXTENSION)); ?>
        <div class="attachment-separator"></div>
        <?php if ($ext2 == 'pdf') : ?>
            <?php $pdf_url2 = base_url('assets/expense/' . $kasbon->doc_file_2); ?>
            <div class="pdf-render-container" data-pdf-url="<?= $pdf_url2 ?>">
                <iframe src="<?= $pdf_url2 ?>#toolbar=0&navpanes=0" title="PDF" style="width:100%; height:600px;" frameborder="0">
                    <a href="<?= $pdf_url2 ?>">Download PDF</a>
                </iframe>
            </div>
        <?php elseif (in_array($ext2, ['jpg', 'jpeg', 'png', 'gif'])) : ?>
            <img src="<?= base_url('assets/expense/' . $kasbon->doc_file_2) ?>" class="attachment-img">
        <?php endif; ?>
    <?php endif; ?>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var containers = document.querySelectorAll('.pdf-render-container');
            if (typeof pdfjsLib !== 'undefined' && containers.length > 0) {
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

                var totalContainers = containers.length;
                var renderedContainers = 0;

                containers.forEach(function(container) {
                    var url = container.getAttribute('data-pdf-url');
                    if (!url) {
                        renderedContainers++;
                        if (renderedContainers === totalContainers) window.print();
                        return;
                    }

                    pdfjsLib.getDocument(url).promise.then(function(pdf) {
                        container.innerHTML = ''; // Hapus fallback iframe
                        
                        var renderPage = function(num) {
                            pdf.getPage(num).then(function(page) {
                                var scale = 1.5;
                                var viewport = page.getViewport({ scale: scale });
                                var canvas = document.createElement('canvas');
                                canvas.className = 'pdf-page-canvas';
                                var context = canvas.getContext('2d');
                                canvas.height = viewport.height;
                                canvas.width = viewport.width;

                                container.appendChild(canvas);

                                var renderContext = {
                                    canvasContext: context,
                                    viewport: viewport
                                };
                                page.render(renderContext).promise.then(function() {
                                    if (num < pdf.numPages) {
                                        renderPage(num + 1);
                                    } else {
                                        renderedContainers++;
                                        if (renderedContainers === totalContainers) {
                                            setTimeout(function() {
                                                window.print();
                                            }, 500);
                                        }
                                    }
                                });
                            });
                        };
                        renderPage(1);
                    }).catch(function(err) {
                        console.error("PDF.js render error:", err);
                        renderedContainers++;
                        if (renderedContainers === totalContainers) {
                            window.print();
                        }
                    });
                });
            } else {
                window.print();
            }
        });
    </script>
</body>

</html>