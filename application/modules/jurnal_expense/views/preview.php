<?php
$escape = function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
$debit = $credit = 0;
?>
<input type="hidden" name="id" value="<?= (int) $id ?>">
<table class="table table-striped">
    <thead><tr>
        <?php foreach (['Tanggal','Tipe','No. COA','Nama COA','Keterangan','No. Reff','Debit','Kredit'] as $heading): ?>
        <td class="text-center"><?= $heading ?></td>
        <?php endforeach; ?>
    </tr></thead>
    <tbody>
    <?php foreach ($rows as $row):
        $debit += (int) round((float) $row->debit * 100); $credit += (int) round((float) $row->kredit * 100);
        if ($row->debit <= 0 && $row->kredit <= 0) { continue; }
        $reference = $row->no_transaksi; $description = $row->keterangan;
        if (preg_match('/\{REF:(.*?)\}/', $description, $matches)) { $reference = $matches[1]; $description = trim(str_replace($matches[0], '', $description)); }
    ?>
        <tr>
            <td class="text-center"><?= date('d F Y', strtotime($row->tgl_jurnal)) ?></td>
            <td class="text-center"><?= $escape($row->jenis_transaksi) ?></td>
            <td class="text-center"><?= $escape($row->coa) ?></td>
            <td class="text-center"><?= $escape($row->nm_coa) ?></td>
            <td class="text-center"><textarea class="form-control form-control-sm" readonly><?= $escape($description) ?></textarea></td>
            <td class="text-center"><?= $escape($reference) ?></td>
            <td class="text-center"><input class="form-control form-control-sm text-right" value="<?= number_format($row->debit, 2) ?>" readonly></td>
            <td class="text-center"><input class="form-control form-control-sm text-right" value="<?= number_format($row->kredit, 2) ?>" readonly></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot><tr>
        <th class="text-right" colspan="6">Total</th>
        <td class="text-right"><input class="form-control form-control-sm text-right" name="ttl_debit" value="<?= number_format($debit / 100, 2) ?>" readonly></td>
        <td class="text-right"><input class="form-control form-control-sm text-right" name="ttl_kredit" value="<?= number_format($credit / 100, 2) ?>" readonly></td>
    </tr></tfoot>
</table>
