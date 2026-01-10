<?php
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Report Actual Plan Tagih (" . $tahun . ") - " . $nm_client . " - " . $nm_company . ".xls");
?>
<h2>Report Actual Plan Tagih (<?= $tahun ?>)</h2>
<?php
if (!empty($nm_client)) {
    echo '<h3>Client : ' . $nm_client . '</h3>';
}
if (!empty($nm_company)) {
    echo '<h3>Company : ' . $nm_company . '</h3>';
}
?>

<table border="1" width="100%">
    <thead>
        <tr>
            <th>No.</th>
            <th>Company</th>
            <th>No. SPK</th>
            <th>Customer</th>
            <th>Project</th>
            <th>Nominal SPK</th>
            <th>Nominal Invoice</th>
            <th>Nominal Un-Invoiced</th>
            <th>Macet</th>
            <?php
            for ($i = 1; $i <= 12; $i++) {
                $no_bulan = sprintf('%02s', $i);
                echo '<th class="text-center">' . date('M', strtotime('' . date('Y') . '-' . $no_bulan . '-01')) . '</th>';
            }
            ?>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 0;

        $ttl_nominal_spk = 0;
        $ttl_invoice = 0;
        $ttl_uninvoice = 0;
        $ttl_macet = 0;

        $total_jan = 0;
        $total_feb = 0;
        $total_mar = 0;
        $total_apr = 0;
        $total_may = 0;
        $total_jun = 0;
        $total_jul = 0;
        $total_aug = 0;
        $total_sep = 0;
        $total_oct = 0;
        $total_nov = 0;
        $total_dec = 0;

        foreach ($list_report as $item) :
            $no++;

            $this->db->select('COALESCE(SUM(a.total_nominal), 0) as total_invoice');
            $this->db->from('tr_invoicing a');
            $this->db->where('a.id_spk_penawaran', $item->id_spk_penawaran);
            $get_invoicing = $this->db->get()->row();

            $total_invoice = (!empty($get_invoicing->total_invoice)) ? $get_invoicing->total_invoice : 0;

            // $this->db->select('a.nominal_payment');
            // $this->db->from('kons_tr_actual_plan_tagih a');
            // $this->db->where('a.id_spk_penawaran', $item->id_spk_penawaran);
            // $this->db->where('a.tagih_mundur', '1');
            // $this->db->where('a.sts_inv', '0');
            // $this->db->group_by('a.id_detail_plan_tagih');
            // $get_act_no_inv = $this->db->get()->result();

            // foreach ($get_act_no_inv as $item_act) {
            //     $total_invoice += $item_act->nominal_payment;
            // }

            $total_uninvoiced = ($item->nilai_kontrak - $total_invoice);

            $total_macet = 0;

            $this->db->select('COALESCE(a.nominal_payment, 0) as total_macet');
            $this->db->from('kons_tr_actual_plan_tagih a');
            $this->db->where('a.id_spk_penawaran', $item->id_spk_penawaran);
            $this->db->where('a.tagih_mundur', '3');
            $this->db->group_by('a.id_detail_plan_tagih');
            $get_tagihan_macet = $this->db->get()->result();

            foreach ($get_tagihan_macet as $item_macet) {
                $total_macet += $item_macet->total_macet;
            }

            $arr_bulan = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
            $arr_noms = [];
            foreach ($arr_bulan as $item_bulan) :
                $this->db->select('COALESCE(b.nominal_payment, a.nominal_payment) as nilai_perbulan');
                $this->db->from('kons_tr_plan_tagih_detail a');
                $this->db->join('kons_tr_actual_plan_tagih b', 'b.id_detail_plan_tagih = a.id AND b.tagih_mundur <> "1" AND b.sts_invoice = "0"', 'left');
                $this->db->where('a.id_spk_penawaran', $item->id_spk_penawaran);
                $this->db->where('DATE_FORMAT(COALESCE(b.tanggal_actual_plan_tagih, a.tgl_plan_tagih), "%m") =', $item_bulan);
                $this->db->where('DATE_FORMAT(COALESCE(b.tanggal_actual_plan_tagih, a.tgl_plan_tagih), "%Y") =', $tahun);
                $this->db->group_by('a.id');
                $get_nilai_perbulan = $this->db->get()->result();

                $total_perbulan = 0;

                foreach ($get_nilai_perbulan as $item_nilai_perbulan) {
                    $total_perbulan += $item_nilai_perbulan->nilai_perbulan;
                }
                $arr_noms[$item_bulan] = $total_perbulan;
            endforeach;
        ?>

            <tr>
                <td align="center"><?= $no; ?></td>
                <td align="left"><?= $item->nm_company ?></td>
                <td><?= $item->id_spk_penawaran ?></td>
                <td><?= $item->nm_customer ?></td>
                <td><?= $item->nm_paket ?></td>
                <td align="right"><?= round($item->nilai_kontrak) ?></td>
                <td align="right"><?= round($total_invoice) ?></td>
                <td align="right"><?= round($item->nilai_kontrak - $total_invoice) ?></td>
                <td align="right"><?= round($total_macet) ?></td>
                <?php
                foreach ($arr_bulan as $item_bulan) :
                ?>

                    <td align="right"><?= round($arr_noms[$item_bulan]) ?></td>

                <?php
                    if ($item_bulan == '1') {
                        $total_jan += $arr_noms[$item_bulan];
                    }
                    if ($item_bulan == '2') {
                        $total_feb += $arr_noms[$item_bulan];
                    }
                    if ($item_bulan == '3') {
                        $total_mar += $arr_noms[$item_bulan];
                    }
                    if ($item_bulan == '4') {
                        $total_apr += $arr_noms[$item_bulan];
                    }
                    if ($item_bulan == '5') {
                        $total_may += $arr_noms[$item_bulan];
                    }
                    if ($item_bulan == '6') {
                        $total_jun += $arr_noms[$item_bulan];
                    }
                    if ($item_bulan == '7') {
                        $total_jul += $arr_noms[$item_bulan];
                    }
                    if ($item_bulan == '8') {
                        $total_aug += $arr_noms[$item_bulan];
                    }
                    if ($item_bulan == '9') {
                        $total_sep += $arr_noms[$item_bulan];
                    }
                    if ($item_bulan == '10') {
                        $total_oct += $arr_noms[$item_bulan];
                    }
                    if ($item_bulan == '11') {
                        $total_nov += $arr_noms[$item_bulan];
                    }
                    if ($item_bulan == '12') {
                        $total_dec += $arr_noms[$item_bulan];
                    }
                endforeach
                ?>
            </tr>

        <?php

            $ttl_nominal_spk += $item->nilai_kontrak;
            $ttl_invoice += $total_invoice;
            $ttl_uninvoice += ($item->nilai_kontrak - $total_invoice);
            $ttl_macet += $total_macet;

        endforeach;
        ?>
    </tbody>
    <tfoot>
        <tr>
            <td style="font-weight: bold;" colspan="5" align="right">Grand Total</td>
            <td style="font-weight: bold;" align="right"><?= $ttl_nominal_spk ?></td>
            <td style="font-weight: bold;" align="right"><?= $ttl_invoice ?></td>
            <td style="font-weight: bold;" align="right"><?= $ttl_uninvoice ?></td>
            <td style="font-weight: bold;" align="right"><?= $ttl_macet ?></td>
            <td style="font-weight: bold;" align="right"><?= $total_jan ?></td>
            <td style="font-weight: bold;" align="right"><?= $total_feb ?></td>
            <td style="font-weight: bold;" align="right"><?= $total_mar ?></td>
            <td style="font-weight: bold;" align="right"><?= $total_apr ?></td>
            <td style="font-weight: bold;" align="right"><?= $total_may ?></td>
            <td style="font-weight: bold;" align="right"><?= $total_jun ?></td>
            <td style="font-weight: bold;" align="right"><?= $total_jul ?></td>
            <td style="font-weight: bold;" align="right"><?= $total_aug ?></td>
            <td style="font-weight: bold;" align="right"><?= $total_sep ?></td>
            <td style="font-weight: bold;" align="right"><?= $total_oct ?></td>
            <td style="font-weight: bold;" align="right"><?= $total_nov ?></td>
            <td style="font-weight: bold;" align="right"><?= $total_dec ?></td>
        </tr>
    </tfoot>
</table>