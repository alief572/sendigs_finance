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

        @media print {
            .pagebreak {
                page-break-before: always;
            }

            body {
                padding: 10px;
            }

            .attachment-separator {
                page-break-before: always;
                border-top: 3px solid #4CAF50;
            }
        }
    </style>
</head>

<body>

    <!-- Document Header -->
    <div class="document-header">
        <h4>Pengajuan Kasbon</h4>
        <p><?= $pr_header->no_pr ?? '' ?> - <?= $kasbon->no_doc ?></p>
    </div>

    <!-- Informasi Pengajuan -->
    <div class="section-title">Informasi Pengajuan</div>
    <table class="info-table">
        <tr>
            <td width="100">Department</td>
            <td width="5">:</td>
            <td width="150"><?= $dept_name ?></td>
            <td width="40"></td>
            <td width="110">Project Name</td>
            <td width="5">:</td>
            <td><?= $pr_header->project_name ?? '-' ?></td>
        </tr>
        <tr>
            <td>Request By</td>
            <td>:</td>
            <td><?= $request_by ?? '' ?></td>
            <td></td>
            <td>COA</td>
            <td>:</td>
            <td><?= $kasbon->no_coa . ' ' . $kasbon->nm_coa ?></td>
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
            <td>Tanggal Kasbon</td>
            <td>:</td>
            <td><?= formatDate($kasbon->tgl_doc) ?></td>
            <td></td>
            <td>Approval Kasbon</td>
            <td>:</td>
            <td><?= formatDate($kasbon->approved_on) ?></td>
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
                $total_harga = $detail->qty * $detail->harga;
                $grand_total += $total_harga;
            ?>
                <tr>
                    <td style="text-align: center;"><?= $no++ ?></td>
                    <td><?= $detail->nm_barang ?></td>
                    <td><?= $detail->spec ?></td>
                    <td style="text-align: center;"><?= $detail->qty ?></td>
                    <td style="text-align: right;">Rp &nbsp;&nbsp; <?= number_format($detail->harga, 0, ',', '.') ?></td>
                    <td style="text-align: center;"><?= formatDate($detail->tanggal) ?></td>
                    <td style="text-align: right;">Rp &nbsp;&nbsp; <?= number_format($total_harga, 0, ',', '.') ?></td>
                    <td><?= $detail->keterangan ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="border: none; border-top: 1px solid #333;"></td>
                <td style="text-align: center; border-top: 1px solid #333; border-left: none; border-bottom: none; border-right: none;">Total</td>
                <td style="border: none; border-top: 1px solid #333;"></td>
                <td style="text-align: right; border: 1px solid #333;">Rp &nbsp;&nbsp; <?= number_format($grand_total, 0, ',', '.') ?></td>
                <td style="border: none; border-top: 1px solid #333;"></td>
            </tr>
        </tfoot>
    </table>

    <!-- Informasi Bank + Signature side by side -->
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
                    <td><u>Fikri</u><br><small>tgl approval terakhir</small></td>
                    <td><u>Imanuel Iman</u><br><small>tgl approval terakhir</small></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Attachments -->
    <?php if (!empty($kasbon->doc_file) || !empty($kasbon->doc_file_2)) : ?>
        <div class="attachment-separator"></div>

        <?php if (!empty($kasbon->doc_file)) : ?>
            <?php if (strtolower(pathinfo($kasbon->doc_file, PATHINFO_EXTENSION)) == 'pdf') : ?>
                <iframe src="<?= base_url('assets/expense/' . $kasbon->doc_file) ?>" width="100%" height="600px" style="border: none;"></iframe>
            <?php else : ?>
                <img src="<?= base_url('assets/expense/' . $kasbon->doc_file) ?>" class="attachment-img">
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!empty($kasbon->doc_file_2)) : ?>
            <?php if (strtolower(pathinfo($kasbon->doc_file_2, PATHINFO_EXTENSION)) == 'pdf') : ?>
                <iframe src="<?= base_url('assets/expense/' . $kasbon->doc_file_2) ?>" width="100%" height="600px" style="border: none;"></iframe>
            <?php else : ?>
                <img src="<?= base_url('assets/expense/' . $kasbon->doc_file_2) ?>" class="attachment-img">
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>

    <script>
        window.print();
    </script>
</body>

</html>