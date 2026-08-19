<?php
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Report_Jurnal_Payment_" . date('YmdHis') . ".xls");
?>
<h2>Report Jurnal Payment</h2>
<table width="100%" border="1" style="border-collapse: collapse;">
    <thead>
        <tr style="background-color: #0073b7; color: #ffffff; font-weight: bold; text-align: center;">
            <th style="background-color: #0073b7; color: #ffffff; padding: 8px; text-align: center;">No.</th>
            <th style="background-color: #0073b7; color: #ffffff; padding: 8px; text-align: center;">No. Transaksi</th>
            <th style="background-color: #0073b7; color: #ffffff; padding: 8px; text-align: center;">No. Pengajuan</th>
            <th style="background-color: #0073b7; color: #ffffff; padding: 8px; text-align: center;">Kategori Transaksi</th>
            <th style="background-color: #0073b7; color: #ffffff; padding: 8px; text-align: center;">Tanggal Jurnal</th>
            <th style="background-color: #0073b7; color: #ffffff; padding: 8px; text-align: center;">Company</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 0;
        if (!empty($list_jurnal)) {
            foreach ($list_jurnal as $item) {
                $no++;
        ?>
                <tr>
                    <td style="text-align: center; vertical-align: middle;"><?= $no ?></td>
                    <td style="text-align: center; vertical-align: middle;"><?= htmlspecialchars($item['no_transaksi']) ?></td>
                    <td style="text-align: center; vertical-align: middle;"><?= htmlspecialchars($item['no_pengajuan']) ?></td>
                    <td style="text-align: center; vertical-align: middle;"><?= htmlspecialchars($item['kategori_payment']) ?></td>
                    <td style="text-align: center; vertical-align: middle;"><?= date('d F Y', strtotime($item['tgl_jurnal'])) ?></td>
                    <td style="text-align: center; vertical-align: middle;"><?= htmlspecialchars($item['nm_company']) ?></td>
                </tr>
        <?php
            }
        } else {
        ?>
            <tr>
                <td colspan="6" style="text-align: center;">Tidak ada data jurnal payment yang ditemukan.</td>
            </tr>
        <?php
        }
        ?>
    </tbody>
</table>
