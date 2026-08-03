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
            font-size: 10px;
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
            <td width="100">Request By</td>
            <td width="5">:</td>
            <td width="150"><?= !empty($request_by) ? $request_by : '-' ?></td>
            <td width="40"></td>
            <td width="110">No PR</td>
            <td width="5">:</td>
            <td><?= !empty($pr_header->no_pr) ? $pr_header->no_pr : '-' ?></td>
        </tr>
        <tr>
            <td>Tanggal Kasbon</td>
            <td>:</td>
            <td><?= formatDate($kasbon->tgl_doc) ?></td>
            <td></td>
            <td>Kategori</td>
            <td>:</td>
            <td><?= !empty($pr_header->category) ? $pr_header->category : '-' ?></td>
        </tr>
        <tr>
            <td>Approval Kasbon</td>
            <td>:</td>
            <td><?= formatDate($kasbon->approved_on ?? '') ?></td>
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
                <th width="25">#</th>
                <th>Material Name</th>
                <th width="65">Min Stock</th>
                <th width="65">Max Stock</th>
                <th width="65">Min Order</th>
                <th width="70">Qty PR (Pack)</th>
                <th width="60">Unit Pack</th>
                <th width="45">Qty</th>
                <th width="75">Unit Measurement</th>
                <th width="70">Price Ref</th>
                <th width="80">Total Price</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $grand_total = 0;
            $no = 1;
            foreach ($pr_details as $detail) :
                $konversi = (!empty($detail->konversi) && $detail->konversi > 0) ? $detail->konversi : 1;
                $qty_pack = $detail->propose_purchase;
                $qty = $detail->propose_purchase * $konversi;
                $price_ref = !empty($detail->price_ref) ? $detail->price_ref : 0;
                $total_price = $qty * $price_ref;
                $grand_total += $total_price;
            ?>
                <tr>
                    <td style="text-align: center;"><?= $no++ ?></td>
                    <td><?= $detail->material_name ?></td>
                    <td style="text-align: right;"><?= number_format($detail->min_stok, 2) ?></td>
                    <td style="text-align: right;"><?= number_format($detail->max_stok, 2) ?></td>
                    <td style="text-align: right;"><?= number_format(0, 2) ?></td>
                    <td style="text-align: right;"><?= number_format($qty_pack, 2) ?></td>
                    <td style="text-align: center;"><?= strtoupper($detail->unit_packing ?? '') ?></td>
                    <td style="text-align: right;"><?= number_format($qty, 2) ?></td>
                    <td style="text-align: center;"><?= strtoupper($detail->unit_measurement ?? '') ?></td>
                    <td style="text-align: right;"><?= number_format($price_ref, 2) ?></td>
                    <td style="text-align: right;"><?= number_format($total_price, 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="10" style="text-align: right; font-weight: bold; border: none; border-top: 1px solid #333;">Total</td>
                <td style="text-align: right; font-weight: bold; border: 1px solid #333;"><?= number_format($grand_total, 2) ?></td>
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
        <?php if ($ext == 'pdf') : ?>
            <div class="attachment-separator"></div>
            <iframe src="<?= base_url('assets/expense/' . $kasbon->doc_file) ?>" width="100%" height="600px" style="border: none;"></iframe>
        <?php elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) : ?>
            <div class="attachment-separator"></div>
            <img src="<?= base_url('assets/expense/' . $kasbon->doc_file) ?>" class="attachment-img">
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!empty($kasbon->doc_file_2)) : ?>
        <?php $ext2 = strtolower(pathinfo($kasbon->doc_file_2, PATHINFO_EXTENSION)); ?>
        <?php if ($ext2 == 'pdf') : ?>
            <div class="attachment-separator"></div>
            <iframe src="<?= base_url('assets/expense/' . $kasbon->doc_file_2) ?>" width="100%" height="600px" style="border: none;"></iframe>
        <?php elseif (in_array($ext2, ['jpg', 'jpeg', 'png', 'gif'])) : ?>
            <div class="attachment-separator"></div>
            <img src="<?= base_url('assets/expense/' . $kasbon->doc_file_2) ?>" class="attachment-img">
        <?php endif; ?>
    <?php endif; ?>

</body>

</html>