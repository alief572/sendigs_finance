<?php
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Report_Petty_Cash_Buku_Kas_Kecil.xls");
?>
<table border="1" width="100%">
    <thead>
        <tr>
            <th colspan="11" style="text-align: center; font-size: 14pt; font-weight: bold;">Report Petty Cash - Buku Kas Kecil</th>
        </tr>
        <?php if (!empty($start_date) || !empty($end_date)): ?>
        <tr>
            <th colspan="11" style="text-align: center;">Periode: <?= $start_date ?> s/d <?= $end_date ?></th>
        </tr>
        <?php endif; ?>
        <tr>
            <th>No</th>
            <th>No Transaksi</th>
            <th>Tanggal</th>
            <th>COA</th>
            <th>Company</th>
            <th>Pengeluaran</th>
            <th>Jenis Jurnal</th>
            <th>Debit</th>
            <th>Kredit</th>
            <th>Saldo</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td> </td>
            <td></td>
            <td></td>
            <td></td>
            <td>saldo awal &gt;&gt;</td>
            <td></td>
            <td><?= $saldo_awal ?></td>
            <td>Saldo Awal Petty Cash</td>
        </tr>
        <?php 
        $no = 1;
        $running_balance = $saldo_awal;
        foreach ($records as $row): 
            $running_balance = $running_balance + $row->debit - $row->kredit;
        ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= $row->no_transaksi ?></td>
            <td><?= date('d/m/Y', strtotime($row->tanggal)) ?></td>
            <td> <?= $row->coa ?></td>
            <td><?= $row->company ?></td>
            <td><?= $row->pengeluaran ?></td>
            <td><?= $row->jenis_jurnal ?></td>
            <td><?= $row->debit > 0 ? $row->debit : '' ?></td>
            <td><?= $row->kredit > 0 ? $row->kredit : '' ?></td>
            <td><?= $running_balance ?></td>
            <td><?= $row->keterangan ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
