<?php
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Jurnal Payment.xls");
?>
<table width="100%" border="1">
    <thead>
        <tr>
            <th class="text-center">No.</th>
            <th class="text-center">No. Transaksi</th>
            <th class="text-center">Jenis Transaksi</th>
            <th class="text-center">Tanggal Jurnal</th>
            <th class="text-center">Company</th>
        </tr>
    </thead>
    <tbody>
        <?php $no = 0;
        foreach ($list_jurnal as $item_jurnal) : $no++;
            $nilai = 0;
            if ($item_jurnal['debit'] > 0) {
                $nilai = $item_jurnal['debit'];
            }
            if ($item_jurnal['kredit'] > 0) {
                $nilai = $item_jurnal['kredit'];
            }

            $status = "Open";
            if ($item_jurnal['sts'] == '1') {
                $status = "Posted";
            }
            if ($item_jurnal['sts'] == '9') {
                $status = "Revisi";
            }
        ?>
            <tr>
                <td style="text-align: center;"><?= $no ?></td>
                <td style="text-align: center;"><?= $item_jurnal['no_transaksi'] ?></td>
                <td style="text-align: center;"><?= $item_jurnal['jenis_transaksi'] ?></td>
                <td style="text-align: center;"><?= date('d F Y', strtotime($item_jurnal['tgl_jurnal'])) ?></td>
                <td style="text-align: center;"><?= $item_jurnal['nm_company'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>