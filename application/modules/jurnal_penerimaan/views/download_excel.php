<?php
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Jurnal Penerimaan.xls");
?>
<table width="100%" border="1">
    <thead>
        <tr>
            <th>No.</th>
            <th>Tgl</th>
            <th>Klien</th>
            <th>No. Invoice</th>
            <th>Keterangan Tagihan</th>
            <th>Company</th>
            <th>Nama Divisi</th>
            <th>Uraian</th>
            <th>Original</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php $no = 0;
        foreach ($list_jurnal as $item_jurnal) : $no++;

            $status = "Open";
            if ($item_jurnal->sts == '1') {
                $status = "Posted";
            }
            if ($item_jurnal->sts == '9') {
                $status = "Revisi";
            }
        ?>
            <tr>
                <td style="text-align: center;"><?= $no ?></td>
                <td style="text-align: center"><?= date('d F Y', strtotime($item_jurnal->tgl_jurnal)) ?></td>
                <td><?= $item_jurnal->nm_customer ?></td>
                <td><?= $item_jurnal->no_invoice ?></td>
                <td><?= $item_jurnal->nm_project ?></td>
                <td><?= $item_jurnal->nm_company ?></td>
                <td><?= $item_jurnal->nm_divisi ?></td>
                <td><?= $item_jurnal->no_invoice ?></td>
                <td style="text-align: cemter;"><?= number_format($item_jurnal->total_debit) ?></td>
                <td><?= $status ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>