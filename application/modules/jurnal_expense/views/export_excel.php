<?php
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Jurnal_Expense_" . date('YmdHis') . ".xls");
?>
<table width="100%" border="1">
    <thead>
        <tr>
            <th class="text-center" style="background-color: #f2f2f2; font-weight: bold; text-align: center;">No.</th>
            <th class="text-center" style="background-color: #f2f2f2; font-weight: bold; text-align: center;">No. Transaksi</th>
            <th class="text-center" style="background-color: #f2f2f2; font-weight: bold; text-align: center;">No. Pengajuan</th>
            <th class="text-center" style="background-color: #f2f2f2; font-weight: bold; text-align: center;">Kategori Transaksi</th>
            <th class="text-center" style="background-color: #f2f2f2; font-weight: bold; text-align: center;">Tanggal Jurnal</th>
            <th class="text-center" style="background-color: #f2f2f2; font-weight: bold; text-align: center;">Company</th>
        </tr>
    </thead>
    <tbody>
        <?php $no = 0;
        foreach ($list_jurnal as $item_jurnal) : $no++; ?>
            <tr>
                <td style="text-align: center;"><?= $no ?></td>
                <td style="text-align: center;"><?= htmlspecialchars($item_jurnal['no_transaksi'], ENT_QUOTES, 'UTF-8') ?></td>
                <td style="text-align: center;"><?= htmlspecialchars($item_jurnal['no_pengajuan'], ENT_QUOTES, 'UTF-8') ?></td>
                <td style="text-align: center;"><?= htmlspecialchars($item_jurnal['kategori_payment'], ENT_QUOTES, 'UTF-8') ?></td>
                <td style="text-align: center;"><?= date('d F Y', strtotime($item_jurnal['tgl_jurnal'])) ?></td>
                <td style="text-align: center;"><?= htmlspecialchars($item_jurnal['nm_company'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>