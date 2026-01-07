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

        foreach ($list_report as $item) :
            $no++;

            $arr_bulan = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
            $arr_noms = [];
            foreach ($arr_bulan as $item_bulan) :
                $this->db->select('COALESCE(SUM(a.nominal_bulanan), 0) as total_bulanan');
                $this->db->from('view_report_actual_plan_tagih a');
                $this->db->where('a.id_spk_penawaran', $item->id_spk_penawaran);
                $this->db->where('a.bulan', $item_bulan);
                $this->db->where('a.tahun', $tahun);
                $get_nominal_perbulan = $this->db->get()->row();

                $arr_noms[$item_bulan] = $get_nominal_perbulan->total_bulanan;
            endforeach;

            $this->db->select('a.*');
            $this->db->from('kons_tr_actual_plan_tagih a');
            $this->db->where('a.tagih_mundur', '3');
            $this->db->where('a.id_spk_penawaran', $item->id_spk_penawaran);
            $this->db->group_start();
            $this->db->where('DATE_FORMAT(a.tanggal_actual_plan_tagih, "%Y") =', $tahun);
            $this->db->or_where('DATE_FORMAT(a.tgl_plan_tagih, "%Y") =', $tahun);
            $this->db->group_end();

            $get_tagihan_macet = $this->db->get()->result();
            // print_r($this->db->last_query());
            // exit;

            $macet = 0;
            foreach ($get_tagihan_macet as $item_macet) :
                $get_tagihan_tagih = $this->db->get_where('kons_tr_actual_plan_tagih', ['id_detail_plan_tagih' => $item_macet->id_detail_plan_tagih, 'id_top' => $item_macet->id_top, 'tagih_mundur' => '1'])->result();

                if (empty($get_tagihan_tagih)) {
                    $macet += $item_macet->nominal_payment;
                }
            endforeach;
        ?>

            <tr>
                <td align="center"><?= $no; ?></td>
                <td align="left"><?= $item->nm_company ?></td>
                <td><?= $item->id_spk_penawaran ?></td>
                <td><?= $item->nm_customer ?></td>
                <td><?= $item->nm_paket ?></td>
                <td align="right"><?= round($item->nilai_kontrak) ?></td>
                <td align="right"><?= round($item->total_invoice) ?></td>
                <td align="right"><?= round($item->nilai_kontrak - $item->total_invoice) ?></td>
                <td align="right"><?= round($macet) ?></td>
                <?php
                foreach ($arr_bulan as $item_bulan) :
                ?>

                    <td align="right"><?= round($arr_noms[$item_bulan]) ?></td>

                <?php
                endforeach
                ?>
            </tr>

        <?php

            $ttl_nominal_spk += $item->nilai_kontrak;
            $ttl_invoice += $item->total_invoice;
            $ttl_uninvoice += ($item->nilai_kontrak - $item->total_invoice);
            $ttl_macet += $macet;

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
            <?php
            for ($i = 1; $i <= 12; $i++) {
                $this->db->select('COALESCE(SUM(a.nominal_bulanan), 0) as total_bulanan');
                $this->db->from('view_report_actual_plan_tagih a');
                $this->db->where('a.bulan', $i);
                $this->db->where('a.tahun', $tahun);
                $get_nominal_perbulan = $this->db->get()->row();

                $ttl_bulanan = (!empty($get_nominal_perbulan->total_bulanan)) ? $get_nominal_perbulan->total_bulanan : 0;

                echo '<td style="font-weight: bold;" align="right">' . round($ttl_bulanan) . '</td>';
            }
            ?>
        </tr>
    </tfoot>
</table>