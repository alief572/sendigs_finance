<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .header h2 {
            margin: 0;
            padding: 0;
            font-size: 14pt;
        }

        .header p {
            margin: 2px 0;
            font-size: 9pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }

        table th,
        table td {
            border: 1px solid #000;
            padding: 3px 5px;
            vertical-align: top;
        }

        table th {
            background-color: #d9d9d9;
            font-weight: bold;
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .footer-row td {
            font-weight: bold;
            font-size: 9pt;
        }
    </style>
</head>

<body>
    <!-- Header Section -->
    <div class="header">
        <h2>REPORT PIUTANG</h2>
        <p>Periode: <?php echo !empty($tgl_dari) ? str_replace('/', '-', $tgl_dari) : '-'; ?> s/d <?php echo !empty($tgl_sampai) ? str_replace('/', '-', $tgl_sampai) : '-'; ?></p>
        <p>Tanggal Cetak: <?php echo $tgl_cetak; ?></p>
    </div>

    <!-- Data Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 10%;">No SPK</th>
                <th style="width: 12%;">Customer</th>
                <th style="width: 10%;">Nilai Kontrak</th>
                <th style="width: 10%;">No Invoice</th>
                <th style="width: 8%;">Tgl Invoice</th>
                <th style="width: 10%;">Nilai Invoice</th>
                <th style="width: 10%;">No Penerimaan</th>
                <th style="width: 8%;">Tgl Penerimaan</th>
                <th style="width: 10%;">Nilai Penerimaan</th>
                <th style="width: 9%;">Saldo Piutang</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            if (!empty($report_data)):
                foreach ($report_data as $spk):
                    // Calculate total rows for this SPK (for rowspan)
                    $spk_total_rows = 0;
                    if (!empty($spk['invoices'])) {
                        foreach ($spk['invoices'] as $invoice) {
                            $payment_count = count($invoice['payments']);
                            $spk_total_rows += ($payment_count > 0) ? $payment_count : 1;
                        }
                    } else {
                        $spk_total_rows = 1;
                    }

                    $first_spk_row = true;

                    if (empty($spk['invoices'])):
            ?>
                        <tr>
                            <td class="text-center" rowspan="1"><?php echo $no++; ?></td>
                            <td rowspan="1"><?php echo $spk['no_spk']; ?></td>
                            <td rowspan="1"><?php echo $spk['nm_customer']; ?></td>
                            <td class="text-right" rowspan="1"><?php echo number_format($spk['nilai_kontrak'], 0, ',', '.'); ?></td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                        </tr>
                        <?php
                    else:
                        foreach ($spk['invoices'] as $inv_idx => $invoice):
                            $payment_count = count($invoice['payments']);
                            $invoice_rows = ($payment_count > 0) ? $payment_count : 1;
                            $first_inv_row = true;

                            if (empty($invoice['payments'])):
                        ?>
                                <tr>
                                    <?php if ($first_spk_row): ?>
                                        <td class="text-center" rowspan="<?php echo $spk_total_rows; ?>"><?php echo $no++; ?></td>
                                        <td rowspan="<?php echo $spk_total_rows; ?>"><?php echo $spk['no_spk']; ?></td>
                                        <td rowspan="<?php echo $spk_total_rows; ?>"><?php echo $spk['nm_customer']; ?></td>
                                        <td class="text-right" rowspan="<?php echo $spk_total_rows; ?>"><?php echo number_format($spk['nilai_kontrak'], 0, ',', '.'); ?></td>
                                    <?php $first_spk_row = false;
                                    endif; ?>
                                    <td rowspan="<?php echo $invoice_rows; ?>"><?php echo $invoice['no_invoice']; ?></td>
                                    <td class="text-center" rowspan="<?php echo $invoice_rows; ?>"><?php echo date('d-m-Y', strtotime($invoice['tanggal_invoice'])); ?></td>
                                    <td class="text-right" rowspan="<?php echo $invoice_rows; ?>"><?php echo number_format($invoice['nilai_invoice'], 0, ',', '.'); ?></td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td class="text-right" rowspan="<?php echo $invoice_rows; ?>"><?php echo number_format($invoice['saldo_piutang'], 0, ',', '.'); ?></td>
                                </tr>
                                <?php
                            else:
                                foreach ($invoice['payments'] as $pay_idx => $payment):
                                ?>
                                    <tr>
                                        <?php if ($first_spk_row): ?>
                                            <td class="text-center" rowspan="<?php echo $spk_total_rows; ?>"><?php echo $no++; ?></td>
                                            <td rowspan="<?php echo $spk_total_rows; ?>"><?php echo $spk['no_spk']; ?></td>
                                            <td rowspan="<?php echo $spk_total_rows; ?>"><?php echo $spk['nm_customer']; ?></td>
                                            <td class="text-right" rowspan="<?php echo $spk_total_rows; ?>"><?php echo number_format($spk['nilai_kontrak'], 0, ',', '.'); ?></td>
                                        <?php $first_spk_row = false;
                                        endif; ?>
                                        <?php if ($first_inv_row): ?>
                                            <td rowspan="<?php echo $invoice_rows; ?>"><?php echo $invoice['no_invoice']; ?></td>
                                            <td class="text-center" rowspan="<?php echo $invoice_rows; ?>"><?php echo date('d-m-Y', strtotime($invoice['tanggal_invoice'])); ?></td>
                                            <td class="text-right" rowspan="<?php echo $invoice_rows; ?>"><?php echo number_format($invoice['nilai_invoice'], 0, ',', '.'); ?></td>
                                        <?php $first_inv_row = false;
                                        endif; ?>
                                        <td><?php echo $payment['no_penerimaan']; ?></td>
                                        <td class="text-center"><?php echo date('d-m-Y', strtotime($payment['tanggal_penerimaan'])); ?></td>
                                        <td class="text-right"><?php echo number_format($payment['nilai_penerimaan'], 0, ',', '.'); ?></td>
                                        <?php if ($pay_idx === 0): ?>
                                            <td class="text-right" rowspan="<?php echo $invoice_rows; ?>"><?php echo number_format($invoice['saldo_piutang'], 0, ',', '.'); ?></td>
                                        <?php endif; ?>
                                    </tr>
            <?php
                                endforeach;
                            endif;
                        endforeach;
                    endif;
                endforeach;
            endif;
            ?>
        </tbody>
        <tfoot>
            <tr class="footer-row">
                <td colspan="10" style="text-align: right; font-weight: bold;">Total Piutang</td>
                <td class="text-right" style="font-weight: bold;"><?php echo number_format($total_piutang, 0, ',', '.'); ?></td>
            </tr>
        </tfoot>
    </table>
</body>

</html>