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

$d_none = '';
$d_none2 = 'style="display: none;"';
if(empty($tgl_approve_direktur)) {
    $d_none = 'style="display: none;"';
    $d_none2 = '';
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
        <h2>Print Expense - <?= $id ?></h2>
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

    <div class="box <?= $box_expense ?>">
        <div class="box-header">
            <h4 style="font-weight: 800;"><?= $title_expense ?></h4>
        </div>

        <div class="box-body">
            <table border="1" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="text-center" valign="top" rowspan="2">No.</th>
                        <th class="text-center" valign="top" rowspan="2">Item</th>
                        <th class="text-center" valign="top" colspan="2">Kasbon</th>
                        <th class="text-center" valign="top" colspan="2">Expense Report</th>
                    </tr>
                    <tr>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Nominal</th>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 0;
                    $ttl_kasbon_exp = 0;
                    $ttl_exp = 0;
                    if (isset($list_expense_detail)) {

                        foreach ($list_expense_detail as $item) : $no++;
                            $qty_kasbon = (!empty($list_detail_expense_detail[$item->id])) ? $list_detail_expense_detail[$item->id]['qty_kasbon'] : 0;
                            $nominal_kasbon = (!empty($list_detail_expense_detail[$item->id])) ? $list_detail_expense_detail[$item->id]['nominal_kasbon'] : 0;
                            $qty_expense = (!empty($list_detail_expense_detail[$item->id])) ? $list_detail_expense_detail[$item->id]['qty_expense'] : 0;
                            $nominal_expense = (!empty($list_detail_expense_detail[$item->id])) ? $list_detail_expense_detail[$item->id]['nominal_expense'] : 0;

                    ?>

                            <tr>
                                <td class="text-center"><?= $no ?></td>
                                <td class="text-left"><?= (!empty($list_detail_expense_detail[$item->id])) ? $list_detail_expense_detail[$item->id]['nama_expense'] : '' ?></td>
                                <td class="text-center"><?= number_format($qty_kasbon, 2) ?></td>
                                <td class="text-center"><?= number_format($nominal_kasbon, 2) ?></td>
                                <td class="text-center"><?= number_format($qty_expense, 2) ?></td>
                                <td class="text-center"><?= number_format($nominal_expense, 2) ?></td>
                            </tr>

                    <?php
                            if ($qty_kasbon > 0 && $qty_kasbon < 1) {
                                $ttl_kasbon_exp += $nominal_kasbon;
                            } else {
                                if ($qty_kasbon > 0) {
                                    $ttl_kasbon_exp += ($nominal_kasbon * $qty_kasbon);
                                }
                            }
                            if ($qty_expense > 0 && $qty_expense < 1) {
                                $ttl_exp += $nominal_expense;
                            } else {
                                if ($qty_expense > 0) {
                                    $ttl_exp += ($nominal_expense * $qty_expense);
                                }
                            }
                        endforeach;
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-right">Total Kasbon</td>
                        <td class="text-right col_ttl_kasbon"><?= number_format($ttl_kasbon_exp, 2) ?></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-right">Total Expense Report</td>
                        <td class="text-right col_ttl_expense_report"><?= number_format($ttl_exp, 2) ?></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-right">Selisih</td>
                        <td class="text-right col_selisih"><?= number_format($ttl_kasbon_exp - $ttl_exp, 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="box" <?= $d_none ?>>
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
    
    <div class="box" style="padding-top: 3vh;">
        <div class="box-body">
            <div style="width: 50% !important;">
                <?php 
                    foreach($list_bukti_penggunaan as $item) :
                        echo '<img src="'.base_url($item->upload_file).'" img="500">';
                    endforeach;
                ?>
            </div>
        </div>
    </div>
</body>

</html>