<?php
$id_so = $results['no_so'];
$id_billing = $results['id_billing'];
$tipe_billing = $results['tipe_billing'];

?>
<h5>Detail Invoice</h5>
<table class="table table-bordered">
    <thead>
        <tr>
            <th class="text-center bg-primary">No</th>
            <th class="text-center bg-primary">Nama Produk</th>
            <th class="text-center bg-primary">Qty</th>
            <th class="text-center bg-primary">UOM</th>
            <th class="text-center bg-primary">Price/Unit</th>
            <th class="text-center bg-primary">Disc</th>
            <th class="text-center bg-primary">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;
        $grand_total = 0;
        foreach ($results['detail'] as $item) {

            $diskon_nilai = (float) $item->diskon_nilai;
            echo '<tr>';
            echo '<td style="font-size: 11px;" class="text-center">' . $no . '</td>';
            echo '<td style="font-size: 11px;">' . $item->nama_produk . '</td>';
            echo '<td style="font-size: 11px;" class="text-center">' . number_format($item->qty) . '</td>';
            echo '<td style="font-size: 11px;" class="text-center">' . ucfirst($item->uom) . '</td>';
            echo '<td style="font-size: 11px;" class="text-right">' . number_format($item->harga_satuan, 2) . '</td>';
            echo '<td style="font-size: 11px;" class="text-right">' . number_format($diskon_nilai, 2) . '</td>';
            echo '<td style="font-size: 11px;" class="text-right">' . number_format($item->total_harga, 2) . '</td>';
            echo '</tr>';

            $grand_total += $item->total_harga;
            $no++;
        }
        ?>
    </tbody>
    <tbody>
        <?php
        $persen_invoice = (float) $results['billing_details']->persen_billing_plan;
        $value_invoice = (float) ($grand_total * $persen_invoice / 100);
        $persen_ppn = (float) $results['data_penawaran']->ppn;
        $nilai_ppn = (float) ($value_invoice * $persen_ppn / 100);

        echo '<tr>';
        echo '<td style="font-size: 11px;" class="text-right text-bold" colspan="6">DPP</td>';
        echo '<td class="text-right text-bold" style="font-size: 11px;">' . number_format($grand_total, 2) . '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<td style="font-size: 11px;" class="text-right text-bold" colspan="5">'.strtoupper($results['tipe_billing']).'</td>';
        echo '<td style="font-size: 11px;" class="text-center text-bold">' . number_format($persen_invoice, 2) . '%</td>';
        echo '<td class="text-right text-bold" style="font-size: 11px;">' . number_format($value_invoice, 2) . '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<td style="font-size: 11px;" class="text-right text-bold" colspan="6">PPN ' . number_format($persen_ppn) . '%</td>';
        echo '<td class="text-right text-bold" style="font-size: 11px;">' . number_format($nilai_ppn, 2) . '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<td style="font-size: 11px;" class="text-right text-bold" colspan="6"> Grand Total</td>';
        echo '<td class="text-right text-bold" style="font-size: 11px;">' . number_format(($value_invoice + $nilai_ppn), 2) . '</td>';
        echo '</tr>';

        ?>
    </tbody>
</table>
<input type="hidden" class="tipe_billing" name="tipe_billing" value="<?= $tipe_billing ?>">
<input type="hidden" class="no_so" name="no_so" value="<?= $id_so ?>">
<input type="hidden" class="id_billing" name="id_billing" value="<?= $id_billing ?>">
<input type="hidden" class="tipe_so" name="tipe_so" value="<?= $results['data_so']->tipe_so ?>">
<input type="hidden" class="id_penawaran" name="id_penawaran" value="<?= $results['data_penawaran']->no_penawaran ?>">
<input type="hidden" class="nilai_asli" name="nilai_asli" value="<?= $grand_total ?>">
<input type="hidden" class="nilai_dpp" name="nilai_dpp" value="<?= $grand_total ?>">
<input type="hidden" class="nilai_invoice" name="nilai_invoice" value="<?= ($value_invoice + $nilai_ppn) ?>">
<input type="hidden" class="persen_invoice" name="persen_invoice" value="<?= $persen_invoice ?>">
<input type="hidden" class="ppn" name="ppn" value="<?= $persen_ppn ?>">
<input type="hidden" class="nilai_ppn" name="nilai_ppn" value="<?= $nilai_ppn ?>">
<input type="hidden" class="grand_total" name="grand_total" value="<?= ($value_invoice + $nilai_ppn) ?>">