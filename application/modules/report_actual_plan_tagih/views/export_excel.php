<?php
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Report Actual Plan Tagih (" . $tahun . ") - " . $nm_client . " - " . $nm_company . ".xls");

// Mapping kunci kolom bulan agar loop lebih ringkas
$list_bulan_map = [
    1 => 'jan',
    2 => 'feb',
    3 => 'mar',
    4 => 'apr',
    5 => 'may',
    6 => 'jun',
    7 => 'jul',
    8 => 'aug',
    9 => 'sep',
    10 => 'oct',
    11 => 'nov',
    12 => 'dec'
];
?>

<h2>Report Actual Plan Tagih (<?= $tahun ?>)</h2>
<?php
if (!empty($nm_client)) echo '<h3>Client : ' . $nm_client . '</h3>';
if (!empty($nm_company)) echo '<h3>Company : ' . $nm_company . '</h3>';
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
            <?php foreach ($list_bulan_map as $num => $key) : ?>
                <th class="text-center"><?= date('M', strtotime("2026-$num-01")) ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 0;
        $ttl_nominal_spk = 0;
        $ttl_invoice     = 0;
        $ttl_uninvoice   = 0;
        $ttl_macet       = 0;

        // Inisialisasi array untuk total per bulan (1-12)
        $total_per_bulan = array_fill(1, 12, 0);

        foreach ($list_report as $item) :
            $current_invoice   = $item->nominal_invoice ?? 0;
            $current_uninvoice = $item->nominal_uninvoice ?? 0;
            $current_macet     = $item->macet ?? 0;

            // KUNCI LOGIKA: Cek apakah data ini sesuai tahun filter
            $is_same_year = ($item->tahun_data == $tahun);

            $no++;
        ?>
            <tr>
                <td align="center"><?= $no; ?></td>
                <td align="left"><?= $item->nm_company ?></td>
                <td><?= $item->id_spk_penawaran ?></td>
                <td><?= $item->nm_customer ?></td>
                <td><?= $item->nm_paket ?></td>
                <td align="right"><?= $item->nilai_kontrak ?></td>
                <td align="right"><?= $current_invoice ?></td>
                <td align="right"><?= $current_uninvoice ?></td>
                <td align="right"><?= $current_macet ?></td>

                <?php
                foreach ($list_bulan_map as $num => $key) :
                    // Jika tahun tidak cocok, nominal dipaksa 0 (hanya tampilkan macet)
                    $val_bulan = ($is_same_year) ? ($item->$key ?? 0) : 0;
                    $total_per_bulan[$num] += $val_bulan;
                ?>
                    <td align="right"><?= $val_bulan ?></td>
                <?php endforeach; ?>
            </tr>
        <?php
            // Update Grand Totals
            $ttl_nominal_spk += $item->nilai_kontrak;
            $ttl_invoice     += $current_invoice;
            $ttl_uninvoice   += $current_uninvoice;
            $ttl_macet       += $current_macet;
        endforeach;
        ?>

        <tr style="font-weight: bold; background-color: #f2f2f2;">
            <td colspan="5" align="right">Grand Total</td>
            <td align="right"><?= $ttl_nominal_spk ?></td>
            <td align="right"><?= $ttl_invoice ?></td>
            <td align="right"><?= $ttl_uninvoice ?></td>
            <td align="right"><?= $ttl_macet ?></td>
            <?php foreach ($total_per_bulan as $total_bln) : ?>
                <td align="right"><?= $total_bln ?></td>
            <?php endforeach; ?>
        </tr>
    </tbody>
</table>