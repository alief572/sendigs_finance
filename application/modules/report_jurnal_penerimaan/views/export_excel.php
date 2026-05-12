<?php
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Report Jurnal Penerimaan.xls");
?>
<h2>Report Jurnal Penerimaan</h2>

<table width="100%" border="1">
    <thead>
        <tr>
            <th style="text-align: center;">No.</th>
            <th style="text-align: center;">Tgl</th>
            <th style="text-align: center;">Klien</th>
            <th style="text-align: center;">No. Invoice</th>
            <th style="text-align: center;">Keterangan</th>
            <th style="text-align: center;">COA</th>
            <th style="text-align: center;">Nama COA</th>
            <th style="text-align: center;">Company</th>
            <th style="text-align: center;">Nama Divisi</th>
            <th style="text-align: center;">Uraian</th>
            <th style="text-align: center;">Original</th>
            <th style="text-align: center;">Debit</th>
            <th style="text-align: center;">Kredit</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 0;

        $ttl_debit = 0;
        $ttl_kredit = 0;
        foreach ($data_jurnal as $item) {
            $no++;

            echo '<tr>
                
                <td style="text-align: center;">' . $no . '</td>
                <td>' . date('d F Y', strtotime($item->tgl_jurnal)) . '</td>
                <td>' . $item->nm_customer . '</td>
                <td>' . $item->no_invoice . '</td>
                <td>' . $item->nm_project . ' - <span style="font-weight: bold;">' . $item->id_spk_penawaran . '</span></td>
                <td> ' . "'" . $item->coa . '</td>
                <td>' . $item->nm_coa . '</td>
                <td>' . $item->nm_company . '</td>
                <td>' . $item->nm_divisi . '</td>
                <td>' . $item->no_invoice . '</td>
                <td style="text-align: right;">' . number_format($item->total_debit) . '</td>
                <td style="text-align: right;">' . number_format($item->debit) . '</td>
                <td style="text-align: right;">' . number_format($item->kredit) . '</td>
                
                </tr>';

            $ttl_debit += $item->debit;
            $ttl_kredit += $item->kredit;
        }
        ?>
    </tbody>
    <tfoot>
        <th colspan="10" style="text-align: center;">Grand Total</th>
        <th style="text-align: right"></th>
        <th style="text-align: right"><?= number_format($ttl_debit) ?></th>
        <th style="text-align: right"><?= number_format($ttl_kredit) ?></th>
    </tfoot>
</table>