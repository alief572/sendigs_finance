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

    <?php 
    // Group rows by nm_company
    $grouped_rows = [];
    foreach ($rows as $row) {
        $comp = !empty($row->nm_company) ? $row->nm_company : 'Lainnya';
        if (!isset($grouped_rows[$comp])) {
            $grouped_rows[$comp] = [];
        }
        $grouped_rows[$comp][] = $row;
    }
    ?>

    <?php foreach ($grouped_rows as $comp => $comp_rows): 
        // Calculate total for this group
        $g_total_debit = 0;
        $g_total_kredit = 0;
        foreach ($comp_rows as $r) {
            $g_total_debit += (float) $r->debit;
            $g_total_kredit += (float) $r->kredit;
        }
        // Use epsilon for floating point comparison
        $g_is_balance = (abs($g_total_debit - $g_total_kredit) < 0.01);

        // Determine label style
        $label_class = 'label-primary';
        if (strtoupper($comp) === 'VUCA' || strtoupper($comp) === 'SUSTAIN') {
            $label_class = 'label-warning';
        } elseif (strtoupper($comp) === 'STM') {
            $label_class = 'label-info';
        }
    ?>
        <label class="label <?= $label_class ?>" style="font-size: 12px; margin-bottom: 8px; margin-top: 10px; display: inline-block;">
            <i class="fa fa-building"></i> Sisi <?= htmlspecialchars($comp) ?>
        </label>
        <div class="table-responsive">
            <table class="table table-bordered table-condensed" style="font-size: 12px;">
                <thead style="background: #f5f5f5;">
                    <tr>
                        <th class="text-center" width="100">Tanggal</th>
                        <th class="text-center">Tipe</th>
                        <th class="text-center" width="100">COA</th>
                        <th class="text-center">Nama Account</th>
                        <th class="text-center">Keterangan</th>
                        <th class="text-center" width="120">No Reff</th>
                        <th class="text-right" width="120">Debit</th>
                        <th class="text-right" width="120">Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comp_rows as $row): ?>
                        <tr>
                            <td class="text-center"><?= date('d/m/Y', strtotime($row->tgl_jurnal)) ?></td>
                            <td class="text-center"><?= isset($row->jenis_transaksi) ? htmlspecialchars($row->jenis_transaksi) : 'Petty Cash' ?></td>
                            <td class="text-center"><?= htmlspecialchars($row->coa) ?></td>
                            <td class="text-left"><?= htmlspecialchars($row->nm_coa) ?></td>
                            <td class="text-left"><?= htmlspecialchars($row->keterangan) ?></td>
                            <td class="text-center"><?= htmlspecialchars($row->no_transaksi) ?></td>
                            <td class="text-right"><?= number_format($row->debit, 0, ',', '.') ?></td>
                            <td class="text-right"><?= number_format($row->kredit, 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot style="background: #f9f9f9; font-weight: bold;">
                    <tr>
                        <td colspan="6" class="text-right">Balancing</td>
                        <td class="text-right" style="color: <?= $g_is_balance ? 'green' : 'red' ?>;">
                            Rp <?= number_format($g_total_debit, 0, ',', '.') ?>
                        </td>
                        <td class="text-right" style="color: <?= $g_is_balance ? 'green' : 'red' ?>;">
                            Rp <?= number_format($g_total_kredit, 0, ',', '.') ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php endforeach; ?>
<?php endif; ?>