<?php
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Kwitansi_Deposit_Customer_" . date('Ymd_His') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");
$kwitansiList = $kwitansiList ?? array();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kwitansi Deposit</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; }
        table { border-collapse: collapse; width: 100%; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 6px 8px; }
        th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>
    <h2>Kwitansi Deposit</h2>

    <table>
        <thead>
            <tr>
                <th width="40">No</th>
                <th>Tanggal</th>
                <th>No Kwitansi</th>
                <th>Customer</th>
                <th>No SPK</th>
                <th>Company</th>
                <th>Nilai Kwitansi</th>
                <th>Status</th>
                <th>No Penerimaan</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($kwitansiList as $item): ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td class="text-center"><?= date('d-M-Y', strtotime($item['tanggal_kwitansi'])) ?></td>
                    <td class="text-center font-bold"><?= htmlspecialchars($item['no_kwitansi']) ?></td>
                    <td><?= htmlspecialchars($item['nm_customer']) ?></td>
                    <td><?= htmlspecialchars($item['no_spk'] ?? $item['id_spk_penawaran'] ?? $item['po_customer'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($item['nm_company'] ?? '-') ?></td>
                    <td class="text-right"><?= number_format((float)$item['nilai_kwitansi'], 2) ?></td>
                    <td class="text-center"><?= $item['status'] === 'LUNAS' ? 'Lunas' : 'Menunggu Pembayaran' ?></td>
                    <td class="text-center"><?= htmlspecialchars($item['no_penerimaan'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
