<?php
$id_so = $results['no_so'];
$id_billing = $results['id_billing'];
$tipe_billing = $results['tipe_billing'];
?>
<h5>Other Cost</h5>
<table class="table table-bordered">
    <thead>
        <tr>
            <th class="text-center bg-primary">No</th>
            <th class="text-center bg-primary">Item</th>
            <th class="text-center bg-primary">Total</th>
            <th class="text-center bg-primary">Checklist</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;
        $ttl_other_cost = 0;
        foreach ($results['list_other_cost'] as $item_other_cost) {

            $check_used_other_cost = $this->db->get_where('tr_used_invoice_sales_other_cost', ['id_other_cost' => $item_other_cost->id])->num_rows();

            if (!isset($results['view'])) {
                if ($check_used_other_cost < 1) {
                    echo '<tr>';
                    echo '<td class="text-center">' . $no . '</td>';
                    echo '<td class="text-center">' . $item_other_cost->keterangan . '</td>';
                    echo '<td class="text-right">(' . $results['currency'] . ') ' . number_format($item_other_cost->total_nilai, 2) . '</td>';
                    echo '<td class="text-center">
                        <input type="checkbox" name="" class="check_other_cost other_cost_' . $item_other_cost->id . '" value="' . $item_other_cost->total_nilai . '" onclick="check_other_cost()" data-id="' . $item_other_cost->id . '">
                    </td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr>';
                echo '<td class="text-center">' . $no . '</td>';
                echo '<td class="text-center">' . $item_other_cost->keterangan . '</td>';
                echo '<td class="text-right">(' . $results['currency'] . ') ' . number_format($item_other_cost->total_nilai, 2) . '</td>';
                echo '<td class="text-center">
                </td>';
                echo '</tr>';

                $ttl_other_cost += $item_other_cost->total_nilai;
            }
            $no++;
        }
        ?>
    </tbody>
</table>

<h5>Detail Invoice</h5>
<table class="table table-bordered">
    <thead>
        <tr>
            <th class="text-center bg-primary">No.</th>
            <th class="text-center bg-primary">Nama Produk</th>
            <th class="text-center bg-primary">Qty</th>
            <th class="text-center bg-primary">Qty Delivery</th>
            <th class="text-center bg-primary">Price/Unit</th>
            <th class="text-center bg-primary">Disc</th>
            <th class="text-center bg-primary">Total</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;
        $subtotal = 0;
        foreach ($results['list_so_detail'] as $item_detail) {
            $nilai_disc = (float) $item_detail->diskon_nilai;
            $total_harga = (($item_detail->harga_satuan - $nilai_disc) * $item_detail->qty_delivery);

            echo '<tr>';
            echo '<td class="text-center">' . $no . '</td>';
            echo '<td class="text-left">' . $item_detail->nama_produk . '</td>';
            echo '<td class="text-center">' . number_format($item_detail->qty) . '</td>';
            echo '<td class="text-center">' . number_format($item_detail->qty_delivery) . '</td>';
            echo '<td class="text-right">(' . $results['currency'] . ') ' . number_format($item_detail->harga_satuan, 2) . '</td>';
            echo '<td class="text-right">(' . $results['currency'] . ') ' . number_format($nilai_disc, 2) . '</td>';
            echo '<td class="text-right">(' . $results['currency'] . ') ' . number_format($total_harga, 2) . '</td>';
            echo '</tr>';

            $subtotal += $total_harga;
            $no++;
        }
        ?>
    </tbody>
    <tbody class="text-bold grand_total_info">
        <?php

        $dp_proporsional = ($subtotal * $results['persen_dp'] / 100);
        $retensi_proporsional = ($subtotal * $results['persen_retensi'] / 100);
        $jaminan_proporsional = ($subtotal * $results['persen_jaminan'] / 100);

        $dpp = ($subtotal - $dp_proporsional - $retensi_proporsional);
        $nilai_ppn = (($dpp + $ttl_other_cost) * $results['persen_ppn'] / 100);
        $total_all = (($dpp + $ttl_other_cost) + $nilai_ppn);
        $total_tagihan = ($total_all + $jaminan_proporsional);

        $nilai_invoice = ($dpp + $ttl_other_cost);
        ?>
        <tr>
            <td class="text-right" colspan="6">Total</td>
            <td class="text-right">(<?= $results['currency'] ?>) <?= number_format($subtotal, 2) ?></td>
        </tr>
        <tr>
            <td class="text-right" colspan="6">DP Proporsional (<?= number_format($results['persen_dp'], 2) ?>%)</td>
            <td class="text-right">(<?= $results['currency'] ?>) <?= number_format($dp_proporsional, 2) ?></td>
        </tr>
        <tr>
            <td class="text-right" colspan="6">Retensi Proporsional (<?= number_format($results['persen_retensi'], 2) ?>%)</td>
            <td class="text-right">(<?= $results['currency'] ?>) <?= number_format($retensi_proporsional, 2) ?></td>
        </tr>
        <tr>
            <td class="text-right" colspan="6">DPP</td>
            <td class="text-right">(<?= $results['currency'] ?>) <?= number_format($dpp, 2) ?></td>
        </tr>
        <tr>
            <td class="text-right" colspan="6">Other Cost</td>
            <td class="text-right">(<?= $results['currency'] ?>) <?= number_format($ttl_other_cost, 2) ?></td>
        </tr>
        <tr>
            <td class="text-right" colspan="6">PPn (<?= number_format($results['persen_ppn'], 2) ?>)</td>
            <td class="text-right">(<?= $results['currency'] ?>) <?= number_format($nilai_ppn, 2) ?></td>
        </tr>
        <tr>
            <td class="text-right" colspan="6">Total</td>
            <td class="text-right">(<?= $results['currency'] ?>) <?= number_format($total_all, 2) ?></td>
        </tr>
        <tr>
            <td class="text-right" colspan="6">Jaminan</td>
            <td class="text-right">(<?= $results['currency'] ?>) <?= number_format($jaminan_proporsional, 2) ?></td>
        </tr>
        <tr>
            <td class="text-right" colspan="6">Total Tagihan</td>
            <td class="text-right">(<?= $results['currency'] ?>) <?= number_format($total_tagihan, 2) ?></td>
        </tr>
    </tbody>
</table>
<input type="hidden" class="tipe_billing" name="tipe_billing" value="<?= $tipe_billing ?>">
<input type="hidden" class="no_so" name="no_so" value="<?= $id_so ?>">
<input type="hidden" class="id_billing" name="id_billing" value="<?= $id_billing ?>">
<input type="hidden" class="tipe_so" name="tipe_so" value="<?= $results['data_so']->tipe_so ?>">
<input type="hidden" class="id_penawaran" name="id_penawaran" value="<?= $results['data_penawaran']->no_penawaran ?>">
<input type="hidden" class="nilai_asli" name="nilai_asli" value="<?= $total_tagihan ?>">
<input type="hidden" class="nilai_dpp" name="nilai_dpp" value="<?= $total_tagihan ?>">
<input type="hidden" class="nilai_invoice" name="nilai_invoice" value="<?= $total_tagihan ?>">
<!-- <input type="hidden" class="persen_invoice" name="persen_invoice" value="<?= $persen_invoice ?>"> -->
<input type="hidden" class="ppn" name="ppn" value="<?= $results['persen_ppn'] ?>">
<input type="hidden" class="nilai_ppn" name="nilai_ppn" value="<?= $nilai_ppn ?>">
<input type="hidden" class="grand_total" name="grand_total" value="<?= ($total_tagihan) ?>">

<input type="hidden" name="persen_retensi" class="persen_retensi" value="<?= $results['persen_retensi'] ?>">
<input type="hidden" name="nilai_retensi" class="nilai_retensi" value="<?= $retensi_proporsional ?>">
<input type="hidden" name="persen_jaminan" class="persen_jaminan" value="<?= $results['persen_jaminan'] ?>">
<input type="hidden" name="nilai_jaminan" class="nilai_jaminan" value="<?= $jaminan_proporsional ?>">