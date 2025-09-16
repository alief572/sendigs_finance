<?php
$box_kasbon_subcont = 'd-none';
$box_kasbon_akomodasi = 'd-none';
$box_kasbon_others = 'd-none';
$box_expense = 'd-none';

$tipe2 = $tipe;
if ($tipe !== 'Expense') {
    $tipe2 = 'Kasbon';
}

$title_expense = (isset($title_expense)) ? $title_expense : '';

if ($tipe == 'Kasbon Subcont') {
    $box_kasbon_subcont = '';
}
if ($tipe == 'Kasbon Akomodasi') {
    $box_kasbon_akomodasi = '';
}
if ($tipe == 'Kasbon Others') {
    $box_kasbon_others = '';
}
if ($tipe == 'Expense') {
    $box_expense = '';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print PDF Kasbon</title>
</head>
<style>
    .btn {
        border-radius: 10px;
    }

    .dropdown-menu {
        top: 100%;
        position: absolute;
        overflow: auto;
    }

    .pd-5 {
        padding: 5px;
    }

    .form-inline .form-control {
        width: auto;
        /* Let elements adjust automatically */
        max-width: 100%;
        /* Prevent overflow */
    }

    .form-inline {
        display: flex;
        /* Use flexbox for better alignment */
        justify-content: flex-start;
        /* Align items to the left */
        flex-wrap: nowrap;
        /* Prevent wrapping to the next line */
    }

    .top-total-project {
        width: 280px;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 15px;
    }

    .pd-5 {
        padding: 5px;
    }

    .valign-top {
        vertical-align: top;
    }

    .mt-5 {
        margin-top: 5px;
    }

    .dropdown-menu {

        position: absolute;
        top: 100%;
        /* Position below the button */
        right: 0;
        /* Align with left edge */
    }

    .d-none {
        display: none;
    }

    .text-center {
        text-align: center;
    }

    .text-right {
        text-align: right;
    }

    .text-left {
        text-align: left;
    }
</style>

<body>
    <div class="text-center">
        <h2>Print Kasbon - <?= $id ?></h2>
    </div>
    <div class="box-body" style="z-index: 1 !important;">
        <table border="0" style="width: 100%; z-index: 1 !important;">
            <tr>
                <th class="pd-5 valign-top" width="150">No. SPK</th>
                <td class="pd-5 valign-top" width="400"><?= $data_spk_penawaran->id_spk_penawaran ?></td>
                <th class="pd-5 valign-top" width="150">Project Leader</th>
                <td class="pd-5 valign-top" width="400"><?= ucfirst($data_spk_penawaran->nm_project_leader) ?></td>
            </tr>
            <tr>
                <th class="pd-5 valign-top" width="150">Customer</th>
                <td class="pd-5 valign-top" width="400"><?= $data_spk_penawaran->nm_customer ?></td>
                <th class="pd-5 valign-top" width="150">Sales</th>
                <td class="pd-5 valign-top" width="400"><?= ucfirst($data_spk_penawaran->nm_sales) ?></td>
            </tr>
            <tr>
                <th class="pd-5 valign-top" width="150">Address</th>
                <td class="pd-5 valign-top" width="400"><?= $data_spk_penawaran->alamat ?></td>
                <th class="pd-5 valign-top" width="150">Waktu</th>
                <td class="pd-5 valign-top" width="400">
                    (<?= date('d F Y', strtotime($data_spk_penawaran->waktu_from)) ?>)
                    <span>-</span>
                    (<?= date('d F Y', strtotime($data_spk_penawaran->waktu_to)) ?>)
                </td>
            </tr>
            <tr>
                <th class="pd-5 valign-top" width="150">Project</th>
                <td class="pd-5 valign-top" width="400"><?= $data_spk_penawaran->nm_paket ?></td>
                <th class="pd-5 valign-top" width="150">Keperluan</th>
                <td class="pd-5 valign-top" width="400">
                    <?= $data_spk_penawaran->nm_customer . ', ' . $data_spk_penawaran->id_spk_penawaran . ', ' . $tipe ?>
                </td>
            </tr>
            <tr>
                <th class="pd-5 valign-top" width="150">Keterangan</th>
                <td class="pd-5 valign-top" width="400"><?= $data_kasbon_header->deskripsi ?></td>
                <th class="pd-5 valign-top" width="150"></th>
                <td class="pd-5 valign-top" width="400">
                </td>
            </tr>
        </table>
    </div>

    <div class="box <?= $box_kasbon_subcont ?>">
        <div class="box-header">
            <h4 style="font-weight: 800;">List Item Subcont</h4>
        </div>

        <div class="box-body">
            <table style="width: 100%;" border="1">
                <thead>
                    <tr>
                        <th rowspan="2" class="text-center" valign="middle">No.</th>
                        <th rowspan="2" class="text-center" valign="middle" width="170">Item</th>
                        <th colspan="2" class="text-center">Estimasi</th>
                        <th rowspan="2" class="text-center" valign="middle">Total Budget</th>
                        <th colspan="3" class="text-center">Pengajuan</th>
                        <th rowspan="2" class="text-center" valign="middle">Sisa Qty</th>
                        <th rowspan="2" class="text-center" valign="middle">Sisa Budget</th>
                    </tr>
                    <tr>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Price / Unit</th>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Price / Unit</th>
                        <th class="text-center">Total Pengajuan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no_subcont = 0;

                    $ttl_qty_pengajuan = 0;
                    $ttl_pengajuan = 0;
                    foreach ($data_kasbon_subcont as $item) {
                        $no_subcont++;

                        $sisa_qty = ($item->aktual_terpakai - $item->qty_pengajuan);
                        $sisa_budget = ($item->sisa_budget - $item->total_pengajuan);

                        echo '<tr>';
                        echo '<td class="text-center">' . $no_subcont . '</td>';
                        echo '<td class="text-left">' . $item->nm_aktifitas . '</td>';
                        echo '<td class="text-center">' . number_format($item->qty_estimasi) . '</td>';
                        echo '<td class="text-right">' . number_format($item->price_unit_estimasi, 2) . '</td>';
                        echo '<td class="text-right">' . number_format($item->total_budget_estimasi, 2) . '</td>';
                        echo '<td class="text-center">' . number_format($item->qty_pengajuan, 2) . '</td>';
                        echo '<td class="text-right">' . number_format($item->nominal_pengajuan, 2) . '</td>';
                        echo '<td class="text-right">' . number_format($item->total_pengajuan, 2) . '</td>';
                        echo '<td class="text-center">' . number_format($sisa_qty, 2) . '</td>';
                        echo '<td class="text-right">' . number_format($sisa_budget, 2) . '</td>';
                        echo '</tr>';

                        $ttl_qty_pengajuan += $item->qty_pengajuan;
                        $ttl_pengajuan += $item->total_pengajuan;
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="text-right">Total</td>
                        <td class="text-center"></td>
                        <td class="text-center"></td>
                        <td class="text-center"></td>
                        <td class="text-center ttl_qty_pengajuan"><?= number_format($ttl_qty_pengajuan, 2) ?></td>
                        <td class="text-center"></td>
                        <td class="text-right ttl_pengajuan"><?= number_format($ttl_pengajuan, 2) ?></td>
                        <td class="text-center"></td>
                        <td class="text-center"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="box <?= $box_kasbon_akomodasi ?>">
        <div class="box-header">
            <h4 style="font-weight: 800;">List Item Others</h4>
        </div>

        <div class="box-body">
            <table style="width: 100%;" border="1">
                <thead>
                    <tr>
                        <th rowspan="2" class="text-center" valign="middle">No.</th>
                        <th rowspan="2" class="text-center" valign="middle" width="170">Item</th>
                        <th colspan="2" class="text-center">Estimasi</th>
                        <th rowspan="2" class="text-center" valign="middle">Total Budget</th>
                        <th colspan="3" class="text-center">Pengajuan</th>
                        <th rowspan="2" class="text-center" valign="middle">Qty Budget Tambahan</th>
                        <th rowspan="2" class="text-center" valign="middle">Budget Tambahan</th>
                        <th rowspan="2" class="text-center" valign="middle">Sisa Qty</th>
                        <th rowspan="2" class="text-center" valign="middle">Sisa Budget</th>
                    </tr>
                    <tr>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Price / Unit</th>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Price / Unit</th>
                        <th class="text-center">Total Pengajuan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no_subcont = 0;

                    $ttl_qty_pengajuan = 0;
                    $ttl_pengajuan = 0;
                    foreach ($data_kasbon_akomodasi as $item) {
                        $no_subcont++;

                        $sisa_qty = ($item->aktual_terpakai + $item->qty_budget_tambahan - $item->qty_pengajuan);
                        $sisa_budget = ($item->sisa_budget + $item->budget_tambahan - $item->total_pengajuan);

                        echo '<tr>';
                        echo '<td class="text-center">' . $no_subcont . '</td>';
                        echo '<td class="text-left">' . $item->nm_item . '</td>';
                        echo '<td class="text-center">' . number_format($item->qty_estimasi) . '</td>';
                        echo '<td class="text-right">' . number_format($item->price_unit_estimasi, 2) . '</td>';
                        echo '<td class="text-right">' . number_format($item->total_budget_estimasi, 2) . '</td>';
                        echo '<td class="text-center">' . number_format($item->qty_pengajuan, 2) . '</td>';
                        echo '<td class="text-right">' . number_format($item->nominal_pengajuan, 2) . '</td>';
                        echo '<td class="text-right">' . number_format($item->total_pengajuan, 2) . '</td>';
                        echo '<td class="text-center">' . number_format($item->qty_budget_tambahan) . '</td>';
                        echo '<td class="text-right">' . number_format($item->budget_tambahan, 2) . '</td>';
                        echo '<td class="text-center">' . number_format($sisa_qty, 2) . '</td>';
                        echo '<td class="text-right">' . number_format($sisa_budget, 2) . '</td>';
                        echo '</tr>';

                        $ttl_qty_pengajuan += $item->qty_pengajuan;
                        $ttl_pengajuan += $item->total_pengajuan;
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="text-right">Total</td>
                        <td class="text-center"></td>
                        <td class="text-center"></td>
                        <td class="text-center"></td>
                        <td class="text-center ttl_qty_pengajuan"><?= number_format($ttl_qty_pengajuan, 2) ?></td>
                        <td class="text-center"></td>
                        <td class="text-right ttl_pengajuan"><?= number_format($ttl_pengajuan, 2) ?></td>
                        <td class="text-center"></td>
                        <td class="text-center"></td>
                        <td class="text-center"></td>
                        <td class="text-center"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="box <?= $box_kasbon_others ?>">
        <div class="box-header">
            <h4 style="font-weight: 800;">List Item Others</h4>
        </div>

        <div class="box-body">
            <table style="width: 100%;" border="1">
                <thead>
                    <tr>
                        <th rowspan="2" class="text-center" valign="middle">No.</th>
                        <th rowspan="2" class="text-center" valign="middle" width="170">Item</th>
                        <th colspan="2" class="text-center">Estimasi</th>
                        <th rowspan="2" class="text-center" valign="middle">Total Budget</th>
                        <th colspan="3" class="text-center">Pengajuan</th>
                        <th rowspan="2" class="text-center" valign="middle">Sisa Qty</th>
                        <th rowspan="2" class="text-center" valign="middle">Sisa Budget</th>
                    </tr>
                    <tr>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Price / Unit</th>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Price / Unit</th>
                        <th class="text-center">Total Pengajuan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no_subcont = 0;

                    $ttl_qty_pengajuan = 0;
                    $ttl_pengajuan = 0;
                    foreach ($data_kasbon_others as $item) {
                        $no_subcont++;

                        $sisa_qty = ($item->aktual_terpakai - $item->qty_pengajuan);
                        $sisa_budget = ($item->sisa_budget - $item->total_pengajuan);

                        echo '<tr>';
                        echo '<td class="text-center">' . $no_subcont . '</td>';
                        echo '<td class="text-left">' . $item->nm_item . '</td>';
                        echo '<td class="text-center">' . number_format($item->qty_estimasi) . '</td>';
                        echo '<td class="text-right">' . number_format($item->price_unit_estimasi, 2) . '</td>';
                        echo '<td class="text-right">' . number_format($item->total_budget_estimasi, 2) . '</td>';
                        echo '<td class="text-center">' . number_format($item->qty_pengajuan, 2) . '</td>';
                        echo '<td class="text-right">' . number_format($item->nominal_pengajuan, 2) . '</td>';
                        echo '<td class="text-right">' . number_format($item->total_pengajuan, 2) . '</td>';
                        echo '<td class="text-center">' . number_format($sisa_qty, 2) . '</td>';
                        echo '<td class="text-right">' . number_format($sisa_budget, 2) . '</td>';
                        echo '</tr>';

                        $ttl_qty_pengajuan += $item->qty_pengajuan;
                        $ttl_pengajuan += $item->total_pengajuan;
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="text-right">Total</td>
                        <td class="text-center"></td>
                        <td class="text-center"></td>
                        <td class="text-center"></td>
                        <td class="text-center ttl_qty_pengajuan"><?= number_format($ttl_qty_pengajuan, 2) ?></td>
                        <td class="text-center"></td>
                        <td class="text-right ttl_pengajuan"><?= number_format($ttl_pengajuan, 2) ?></td>
                        <td class="text-center"></td>
                        <td class="text-center"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="box">
        <div class="box-body">
            <div style="width: 50% !important;">
                <table style="width: 100%;">
                    <tr>
                        <th>Tgl Approve <?= $tipe2 ?> oleh Direktur</th>
                        <th>:</th>
                        <th>
                            <?= date('d F Y H:i:s', strtotime($tgl_approve_direktur)) ?>
                        </th>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>

</html>