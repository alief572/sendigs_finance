<?php

/**
 * Modal Detail Jurnal Petty Cash (Partial View)
 *
 * Loaded via AJAX into modal body.
 * Receives:
 *   - $rows (array of objects: tgl_jurnal, coa, nm_coa, keterangan, no_transaksi, debit, kredit)
 *   - $is_balance (bool)
 *   - $total_debit (float)
 *   - $total_kredit (float)
 *   - $no_transaksi (string)
 *   - $jenis_transaksi (string)
 */
?>

<?php if (empty($rows)): ?>
    <p class="text-center text-muted">Tidak ada data jurnal</p>
<?php else: ?>
    <input type="hidden" name="no_transaksi" value="<?= $no_transaksi ?>">
    <input type="hidden" name="jenis_transaksi" value="<?= isset($jenis_transaksi) ? $jenis_transaksi : 'Petty Cash' ?>">

    <table class="table table-bordered">
        <thead>
            <tr>
                <th class="text-center">Tanggal</th>
                <th class="text-center">Tipe</th>
                <th class="text-center">No COA</th>
                <th class="text-center">Nama COA</th>
                <th class="text-center">Keterangan</th>
                <th class="text-center">No Reff</th>
                <th class="text-center">Debit</th>
                <th class="text-center">Kredit</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td class="text-center"><?= date('d F Y', strtotime($row->tgl_jurnal)) ?></td>
                    <td class="text-center"><?= isset($row->jenis_transaksi) ? $row->jenis_transaksi : 'Petty Cash' ?></td>
                    <td class="text-center"><?= $row->coa ?></td>
                    <td class="text-left"><?= $row->nm_coa ?></td>
                    <td class="text-left"><?= $row->keterangan ?></td>
                    <td class="text-center"><?= $row->no_transaksi ?></td>
                    <td class="text-right"><?= number_format($row->debit, 0, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($row->kredit, 0, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="6" class="text-right">Grand Total</th>
                <th class="text-right" style="color: <?= $is_balance ? 'green' : 'red' ?>;">
                    <?= number_format($total_debit, 0, ',', '.') ?>
                </th>
                <th class="text-right" style="color: <?= $is_balance ? 'green' : 'red' ?>;">
                    <?= number_format($total_kredit, 0, ',', '.') ?>
                </th>
            </tr>
        </tfoot>
    </table>
<?php endif; ?>