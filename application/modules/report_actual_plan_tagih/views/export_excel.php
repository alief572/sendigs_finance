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

            $total_uninvoiced = ($item->nilai_kontrak - $total_invoice);

            $total_macet = 0;

            $this->db->select('a.*,b.nominal_payment');
            $this->db->from('kons_tr_actual_plan_tagih a');
            $this->db->join('kons_tr_plan_tagih_detail b', 'b.id = a.id_detail_plan_tagih');
            $this->db->where('a.id_spk_penawaran', $item->id_spk_penawaran);
            // $this->db->where('YEAR(a.tanggal_actual_plan_tagih)', $tahun);
            // $this->db->where('MONTH(a.tanggal_actual_plan_tagih)', $item_bulan);
            $this->db->group_start();
            $this->db->where('a.tagih_mundur', '3');
            $this->db->or_where('a.macet', '1');
            $this->db->group_end();
            $this->db->group_by('a.id_detail_plan_tagih');
            $get_nilai_macet = $this->db->get()->result();

            foreach ($get_nilai_macet as $item_nilai_macet) {
                $this->db->select('a.id');
                $this->db->from('kons_tr_actual_plan_tagih a');
                $this->db->where('a.id_detail_plan_tagih', $item_nilai_macet->id_detail_plan_tagih);
                $this->db->where('a.id_spk_penawaran', $item_nilai_macet->id_spk_penawaran);
                // $this->db->group_start();
                $this->db->where_in('a.tagih_mundur', ['1', '2']);
                $this->db->where('a.created_date >', $item_nilai_macet->created_date);
                $this->db->group_start();
                $this->db->where('a.macet IS NULL');
                $this->db->or_where('a.macet', '');
                $this->db->group_end();
                // $this->db->group_end();
                $get_check_tagih_mundur_balik = $this->db->get()->num_rows();

                if ($get_check_tagih_mundur_balik < 1) {
                    $total_macet += $item_nilai_macet->nominal_payment;
                }
            }

            // $this->db->select('COALESCE(a.nominal_payment, 0) as total_macet');
            // $this->db->from('kons_tr_actual_plan_tagih a');
            // $this->db->where('a.id_spk_penawaran', $item->id_spk_penawaran);
            // $this->db->group_start();
            // $this->db->where('a.tagih_mundur', '3');
            // $this->db->or_where('a.macet', '1');
            // $this->db->group_end();
            // $this->db->group_by('a.id_detail_plan_tagih');
            // $get_tagihan_macet = $this->db->get()->result();

            // foreach ($get_tagihan_macet as $item_macet) {

            //     $this->db->select('a.id');
            //     $this->db->from('kons_tr_actual_plan_tagih a');
            //     $this->db->where('a.id_detail_plan_tagih', $item_macet->id_detail_plan_tagih);
            //     $this->db->where('a.id_spk_penawaran', $item->id_spk_penawaran);
            //     $this->db->where('a.tagih_mundur', '1');
            //     $this->db->where('a.created_date >', $item->created_date);
            //     $get_check_tagih_mundur_balik = $this->db->get()->num_rows();

            //     if ($get_check_tagih_mundur_balik < 1) {
            //         $total_macet += $item_macet->total_macet;
            //     }
            // }

            $arr_bulan = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
            $arr_noms = [];
            foreach ($arr_bulan as $item_bulan) :

                $this->db->select('b.nominal_payment');
                $this->db->from('kons_tr_actual_plan_tagih a');
                $this->db->join('kons_tr_plan_tagih_detail b', 'b.id = a.id_detail_plan_tagih');
                $this->db->where('a.id_spk_penawaran', $item->id_spk_penawaran);
                $this->db->where('YEAR(a.tanggal_actual_plan_tagih)', $tahun);
                $this->db->where('MONTH(a.tanggal_actual_plan_tagih)', $item_bulan);
                $this->db->where('a.tagih_mundur', '1');
                $this->db->group_by('a.id_detail_plan_tagih');
                $get_nilai_per_bulan = $this->db->get()->result();

                $total_perbulan = 0;
                foreach ($get_nilai_per_bulan as $item_nilai_perbulan) {
                    $total_perbulan += $item_nilai_perbulan->nominal_payment;
                }



                // $this->db->select('
                //     COALESCE(b.nominal_payment, a.nominal_payment) as nilai_perbulan, 
                //     b.tagih_mundur, 
                //     b.id_detail_plan_tagih, 
                //     b.tanggal_actual_plan_tagih
                // ', FALSE); // FALSE supaya CI tidak otomatis nambahin backtick (`) yang bikin error di subquery

                // $this->db->from('kons_tr_plan_tagih_detail a');

                // // Join dengan subquery yang lebih bersih
                // $join_subquery = '(SELECT dd.id_detail_plan_tagih, dd.nominal_payment, dd.tagih_mundur, dd.tanggal_actual_plan_tagih 
                //    FROM kons_tr_actual_plan_tagih dd 
                //    WHERE dd.tanggal_actual_plan_tagih IS NOT NULL 
                //    ORDER BY dd.created_date DESC LIMIT 1) b';

                // $this->db->join($join_subquery, 'b.id_detail_plan_tagih = a.id', 'left');

                // $this->db->where('a.id_spk_penawaran', $item->id_spk_penawaran);

                // // Gunakan pembanding tanggal yang lebih efisien (Sargable)
                // $target_date = $tahun . '-' . str_pad($item_bulan, 2, '0', STR_PAD_LEFT);
                // // $this->db->where("DATE_FORMAT(COALESCE(b.tanggal_actual_plan_tagih, a.tgl_plan_tagih), '%Y-%m') =", $target_date);
                // $this->db->where("DATE_FORMAT(b.tanggal_actual_plan_tagih, '%Y-%m') =", $target_date);

                // $this->db->group_by('a.id');
                // $get_nilai_perbulan = $this->db->get()->result();

                // $total_perbulan = 0;

                // foreach ($get_nilai_perbulan as $item_nilai_perbulan) {
                //     if (!empty($item_nilai_perbulan->tagih_mundur)) {
                //         if ($item_nilai_perbulan->tagih_mundur == '2') {
                //             $this->db->select('a.id');
                //             $this->db->from('kons_tr_actual_plan_tagih a');
                //             $this->db->where('a.id_spk_penawaran', $item->id_spk_penawaran);
                //             $this->db->where('a.id_detail_plan_tagih', $item_nilai_perbulan->id_detail_plan_tagih);
                //             $this->db->where('a.tanggal_actual_plan_tagih >', $item_nilai_perbulan->tanggal_actual_plan_tagih);
                //             $this->db->where('a.tanggal_actual_plan_tagih IS NOT NULL');
                //             if (!empty($item_nilai_perbulan->tanggal_actual_plan_tagih)) {
                //             }
                //             $get_check_lastest = $this->db->get()->num_rows();

                //             // print_r($this->db->last_query());
                //             // exit;

                //             if ($get_check_lastest < 1) {
                //                 $total_perbulan += $item_nilai_perbulan->nilai_perbulan;
                //             }
                //         } else {
                //             $total_perbulan += $item_nilai_perbulan->nilai_perbulan;
                //         }
                //     } else {
                //         $total_perbulan += $item_nilai_perbulan->nilai_perbulan;
                //     }
                // }
                $arr_noms[$item_bulan] = $total_perbulan;
            endforeach;
        ?>

            <tr>
                <td align="center"><?= $no; ?></td>
                <td align="left"><?= $item->nm_company ?></td>
                <td><?= $item->id_spk_penawaran ?></td>
                <td><?= $item->nm_customer ?></td>
                <td><?= $item->nm_paket ?></td>
                <td align="right"><?= number_format($item->nilai_kontrak, 2) ?></td>
                <td align="right"><?= number_format($total_invoice, 2) ?></td>
                <td align="right"><?= number_format($item->nilai_kontrak - $total_invoice, 2) ?></td>
                <td align="right"><?= number_format($total_macet, 2) ?></td>
                <?php
                foreach ($arr_bulan as $item_bulan) :
                ?>

                    <td align="right"><?= number_format($arr_noms[$item_bulan], 2) ?></td>

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


        <tr>
            <td style="font-weight: bold;" colspan="5" align="right">Grand Total</td>
            <td style="font-weight: bold;" align="right"><?= number_format($ttl_nominal_spk, 2) ?></td>
            <td style="font-weight: bold;" align="right"><?= number_format($ttl_invoice, 2) ?></td>
            <td style="font-weight: bold;" align="right"><?= number_format($ttl_uninvoice, 2) ?></td>
            <td style="font-weight: bold;" align="right"><?= number_format($ttl_macet, 2) ?></td>
            <td style="font-weight: bold;" align="right"><?= number_format($total_jan, 2) ?></td>
            <td style="font-weight: bold;" align="right"><?= number_format($total_feb, 2) ?></td>
            <td style="font-weight: bold;" align="right"><?= number_format($total_mar, 2) ?></td>
            <td style="font-weight: bold;" align="right"><?= number_format($total_apr, 2) ?></td>
            <td style="font-weight: bold;" align="right"><?= number_format($total_may, 2) ?></td>
            <td style="font-weight: bold;" align="right"><?= number_format($total_jun, 2) ?></td>
            <td style="font-weight: bold;" align="right"><?= number_format($total_jul, 2) ?></td>
            <td style="font-weight: bold;" align="right"><?= number_format($total_aug, 2) ?></td>
            <td style="font-weight: bold;" align="right"><?= number_format($total_sep, 2) ?></td>
            <td style="font-weight: bold;" align="right"><?= number_format($total_oct, 2) ?></td>
            <td style="font-weight: bold;" align="right"><?= number_format($total_nov, 2) ?></td>
            <td style="font-weight: bold;" align="right"><?= number_format($total_dec, 2) ?></td>
        </tr>
    </tbody>
</table>