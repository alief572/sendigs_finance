<?php
$id_so = $results['no_so'];
$id_billing = $results['id_billing'];
$tipe_billing = $results['tipe_billing'];
$persen_jaminan = $results['billing_plan']->persen_billing_plan;
$persen_ppn = $results['data_penawaran']->ppn;

$total_tagihan = 0;
// foreach ($results['list_spk_delivery'] as $item_spk) {
    
    echo '<table class="table table-bordered">';
    echo '<thead class="bg-primary">';
    echo '<tr>';
    echo '<th class="text-center">No.</th>';
    echo '<th class="text-center">Nama Produk</th>';
    echo '<th class="text-center">Qty</th>';
    echo '<th class="text-center">Qty Delivery</th>';
    echo '<th class="text-center">Price/Unit</th>';
    echo '<th class="text-center">Disc</th>';
    echo '<th class="text-center">Total</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    
    $get_spk_delivery_detail = $this->db
        ->select('b.qty, a.qty_delivery, b.nama_produk, a.qty_delivery, b.harga_satuan, b.diskon_persen, b.diskon_nilai, c.currency')
        ->from('spk_delivery_detail a')
        ->join('tr_sales_order_detail b', 'b.no_so = a.no_so AND b.id_category3 = a.code_lv4', 'left')
        ->join('tr_penawaran c', 'c.no_penawaran = b.no_penawaran', 'left')
        ->where('a.no_so', $id_so)
        ->group_by('a.id')
        ->get()
        ->result();
        // print_r($this->db->last_query());
        // exit;

    $total_all = 0;

    $no = 1;
    foreach ($get_spk_delivery_detail as $item_spk_detail) {

        $nilai_disc = ($item_spk_detail->harga_satuan * $item_spk_detail->diskon_persen / 100);
        $total = (($item_spk_detail->harga_satuan - $nilai_disc) * $item_spk_detail->qty_delivery);

        echo '<tr>';
        echo '<td class="text-center">' . $no . '</td>';
        echo '<td class="text-left">' . $item_spk_detail->nama_produk . '</td>';
        echo '<td class="text-center">' . number_format($item_spk_detail->qty) . '</td>';
        echo '<td class="text-center">' . number_format($item_spk_detail->qty_delivery) . '</td>';
        echo '<td class="text-right">(' . $item_spk_detail->currency . ') ' . number_format($item_spk_detail->harga_satuan, 2) . '</td>';
        echo '<td class="text-right">(' . $item_spk_detail->currency . ') ' . number_format($nilai_disc, 2) . '</td>';
        echo '<td class="text-right">(' . $item_spk_detail->currency . ') ' . number_format($total, 2) . '</td>';
        echo '</tr>';

        $total_all += $total;
        $no++;
    }

    $nilai_dp = ($total_all * $results['data_dp']->persen_invoice / 100);
    $jaminan_prorportion = ($total_all * $persen_jaminan / 100);
    $dpp = (float) ($total_all - $nilai_dp);
    $nilai_ppn = (float) ($dpp * $persen_ppn / 100);

    $grand_total = (float) ($dpp + $nilai_ppn - $jaminan_prorportion);

    echo '</tbody>';
    echo '<tbody class="text-bold">';
    echo '<tr>';
    echo '<td class="text-right" colspan="6">Total</td>';
    echo '<td class="text-right">'.number_format($total_all, 2).'</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td class="text-right" colspan="6">DP ('.number_format($results['data_dp']->persen_invoice).'%)</td>';
    echo '<td class="text-right">-('.number_format($nilai_dp, 2).')</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td class="text-right" colspan="6">DPP</td>';
    echo '<td class="text-right">'.number_format($dpp, 2).'</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td class="text-right" colspan="6">PPn ('.$persen_ppn.')</td>';
    echo '<td class="text-right">'.number_format($nilai_ppn, 2).'</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td class="text-right" colspan="6">Jaminan ('.number_format($persen_jaminan).'%)</td>';
    echo '<td class="text-right">-('.number_format($jaminan_prorportion, 2).')</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td class="text-right" colspan="6">Grand Total</td>';
    echo '<td class="text-right">'.number_format($grand_total, 2).'</td>';
    echo '</tr>';
    echo '</tbody>';
    echo '</table>';

    $total_tagihan += $grand_total;
// }    
?>
<input type="hidden" class="tipe_billing" name="tipe_billing" value="<?= $tipe_billing ?>">
<input type="hidden" class="no_so" name="no_so" value="<?= $id_so ?>">
<input type="hidden" class="id_billing" name="id_billing" value="<?= $id_billing ?>">
<input type="hidden" class="tipe_so" name="tipe_so" value="<?= $results['data_so']->tipe_so ?>">
<input type="hidden" class="id_penawaran" name="id_penawaran" value="<?= $results['data_penawaran']->no_penawaran ?>">
<input type="hidden" class="nilai_asli" name="nilai_asli" value="<?= $total_all ?>">
<input type="hidden" class="nilai_dpp" name="nilai_dpp" value="<?= $dpp ?>">
<input type="hidden" class="nilai_invoice" name="nilai_invoice" value="<?= $total_tagihan + $nilai_ppn ?>">
<input type="hidden" class="ppn" name="ppn" value="0">
<input type="hidden" class="nilai_ppn" name="nilai_ppn" value="0">
<input type="hidden" class="grand_total" name="grand_total" value="<?= ($total_tagihan + $nilai_ppn) ?>">