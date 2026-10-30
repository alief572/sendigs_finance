<?php
$isLedger = !empty($is_customer_ledger);
$filename = $isLedger
    ? "Deposit_" . preg_replace('/[^a-zA-Z0-9_-]/', '_', $id_customer ?? 'customer') . "_" . date('Ymd_His') . ".xls"
    : "Index_Deposit_" . date('Ymd_His') . ".xls";

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=" . $filename);
header("Pragma: no-cache");
header("Expires: 0");

$safe = function($val) {
    $str = (string)$val;
    if (preg_match('/^[\s]*[=+@-]/u', $str)) {
        $str = "'" . $str;
    }
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
};
$n = function ($v) { return number_format((float)$v, 2, ',', '.'); };
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= $isLedger ? 'Mutasi Deposit - ' . $safe($nm_customer ?? $id_customer) : 'Monitoring Deposit' ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; }
        table { border-collapse: collapse; width: 100%; margin-top: 15px; margin-bottom: 25px; }
        th, td { border: 1px solid #000; padding: 6px 8px; }
        th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
<?php if ($isLedger) : ?>
    <h2>Mutasi Deposit - <?= $safe($nm_customer) ?></h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>No Penerimaan Piutang</th>
                <th>Sumber</th>
                <th>Keterangan</th>
                <th>Masuk</th>
                <th>Keluar</th>
                <th>Saldo</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; $running = 0; foreach (($mutasi ?? array()) as $m) :
                $nom = (float)$m['nominal'];
                $isMasuk = ($m['jenis_mutasi'] === 'IN');
                $running += ($isMasuk ? $nom : -$nom);
            ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td class="text-center"><?= date('d-M-Y', strtotime($m['tanggal'])) ?></td>
                    <td><?= $safe($m['no_transaksi']) ?></td>
                    <td><?= $safe(Deposit_customer_model::source_label($m)) ?></td>
                    <td><?= $safe($m['keterangan'] ?? '') ?></td>
                    <td class="text-right"><?= $isMasuk ? $n($nom) : $n(0) ?></td>
                    <td class="text-right"><?= !$isMasuk ? $n($nom) : $n(0) ?></td>
                    <td class="text-right"><?= $n($running) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else : ?>
    <h2>Monitoring Deposit</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Customer</th>
                <th>Jumlah Deposit</th>
                <th>Dari Kwitansi Deposit</th>
                <th>Dari Lebih Bayar</th>
                <th>Total Nilai Deposit</th>
                <th>Total Terpakai</th>
                <th>Total Sisa Saldo</th>
                <th>Deposit Terakhir</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach (($customers ?? array()) as $c) : ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td><?= $safe($c['nm_customer']) ?></td>
                    <td class="text-center"><?= (int)$c['jumlah_deposit'] ?></td>
                    <td class="text-right"><?= $n($c['total_dari_kwitansi']) ?></td>
                    <td class="text-right"><?= $n($c['total_dari_lebih_bayar']) ?></td>
                    <td class="text-right"><?= $n($c['total_nominal_awal']) ?></td>
                    <td class="text-right"><?= $n($c['total_nominal_terpakai']) ?></td>
                    <td class="text-right"><?= $n($c['sisa_saldo']) ?></td>
                    <td class="text-center"><?= !empty($c['last_deposit_date']) ? date('d-M-Y', strtotime($c['last_deposit_date'])) : '-' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
</body>
</html>
