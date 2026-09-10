<?php
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Request_Payment_" . date('YmdHis') . ".xls");

// susun label periode untuk baris info
$periode = '';
if (!empty($date_from) || !empty($date_to)) {
    $periode = 'Periode: ' . (!empty($date_from) ? date('d-M-Y', strtotime($date_from)) : '...')
        . ' s/d ' . (!empty($date_to) ? date('d-M-Y', strtotime($date_to)) : '...');
}
$kat = (!empty($kategori)) ? $kategori : 'Semua';

$total_dpp = 0;
foreach ($rows as $r) {
    $total_dpp += (float) $r['dpp'];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Request Payment</title>
</head>

<body>
    <table border="0" width="100%">
        <tr>
            <td colspan="6" style="font-weight:bold;font-size:16px;">DAFTAR REQUEST PAYMENT (Menunggu Diajukan Approval)</td>
        </tr>
        <tr>
            <td colspan="6">Entitas: <?= htmlspecialchars($company_label); ?></td>
        </tr>
        <?php if ($periode !== '') : ?>
            <tr>
                <td colspan="6"><?= htmlspecialchars($periode); ?></td>
            </tr>
        <?php endif; ?>
        <tr>
            <td colspan="6">Kategori: <?= htmlspecialchars($kat); ?></td>
        </tr>
        <tr>
            <td colspan="6">Dicetak: <?= date('d-M-Y H:i'); ?></td>
        </tr>
    </table>

    <br>

    <table border="1" width="100%" cellspacing="0" cellpadding="4">
        <thead>
            <tr style="background-color:#3c8dbc;color:#ffffff;font-weight:bold;text-align:center;">
                <th style="width:40px;">NO</th>
                <th>NO. DOKUMEN / KATEGORI</th>
                <th>REQUEST BY</th>
                <th>TANGGAL</th>
                <th>KEPERLUAN</th>
                <th>DPP (RP)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rows)) : ?>
                <tr>
                    <td colspan="6" style="text-align:center;">Tidak ada data.</td>
                </tr>
            <?php else : ?>
                <?php $no = 1;
                foreach ($rows as $r) :
                    $kategori_row = !empty($r['kategori']) ? $r['kategori'] : '-';
                    $req_by = $r['request_by'];
                    if (!empty($r['company_nama'])) {
                        $req_by .= ' - ' . $r['company_nama'];
                    }
                    $tgl = !empty($r['tanggal_raw']) ? date('d-M-Y', strtotime($r['tanggal_raw'])) : '';
                ?>
                    <tr>
                        <td style="text-align:center;"><?= $no; ?></td>
                        <td>[<?= htmlspecialchars($kategori_row); ?>] <?= htmlspecialchars($r['no_dokumen']); ?></td>
                        <td><?= htmlspecialchars($req_by); ?></td>
                        <td style="text-align:center;"><?= $tgl; ?></td>
                        <td><?= htmlspecialchars($r['keperluan']); ?></td>
                        <td style="text-align:right;"><?= number_format((float) $r['dpp'], 0, ',', '.'); ?></td>
                    </tr>
                <?php $no++;
                endforeach; ?>
                <tr style="font-weight:bold;background-color:#f2f2f2;">
                    <td colspan="5" style="text-align:right;">TOTAL DPP</td>
                    <td style="text-align:right;"><?= number_format($total_dpp, 0, ',', '.'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>
