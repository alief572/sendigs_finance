<?php
$ENABLE_ADD     = has_permission('Kasbon_Project.Add');
$ENABLE_MANAGE  = has_permission('Kasbon_Project.Manage');
$ENABLE_VIEW    = has_permission('Kasbon_Project.View');
$ENABLE_DELETE  = has_permission('Kasbon_Project.Delete');
?>
<!-- <link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>"> -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.7/css/dataTables.dataTables.min.css">

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

    .valign-middle {
        vertical-align: middle !important;
    }
</style>

<form action="" method="post" id="frm-data" enctype="multipart/form-data">
    <input type="hidden" name="id_header" value="<?= $header->id ?>">
    <input type="hidden" name="id_spk_budgeting" value="<?= $header->id_spk_budgeting ?>">
    <input type="hidden" name="id_spk_penawaran" value="<?= $header->id_spk_penawaran ?>">
    <input type="hidden" name="id_penawaran" value="<?= $header->id_penawaran ?>">

    <div class="box">
        <div class="box-header">

        </div>

        <div class="box-body">
            <table border="0" style="width: 100%;">
                <tr>
                    <th class="pd-5 valign-top" width="150">No. SPK</th>
                    <td class="pd-5 valign-top" width="400"><?= $list_budgeting->id_spk_penawaran ?></td>
                    <th class="pd-5 valign-top" width="150">Project Leader</th>
                    <td class="pd-5 valign-top" width="400"><?= ucfirst($list_budgeting->nm_project_leader) ?></td>
                </tr>
                <tr>
                    <th class="pd-5 valign-top" width="150">Customer</th>
                    <td class="pd-5 valign-top" width="400"><?= $list_budgeting->nm_customer ?></td>
                    <th class="pd-5 valign-top" width="150">Sales</th>
                    <td class="pd-5 valign-top" width="400"><?= ucfirst($list_budgeting->nm_sales) ?></td>
                </tr>
                <tr>
                    <th class="pd-5 valign-top" width="150">Address</th>
                    <td class="pd-5 valign-top" width="400"><?= $list_budgeting->alamat ?></td>
                    <th class="pd-5 valign-top" width="150">Waktu</th>
                    <td class="pd-5 valign-top" width="400">
                        <div class="form-inline">
                            <div class="form-group">
                                <input type="date" name="" id="" class="form-control form-control-sm" value="<?= $list_budgeting->waktu_from ?>" readonly>
                            </div>
                            <div class="form-group text-center" style="width: 50px; padding-top: 8px;">
                                <span>-</span>
                            </div>
                            <div class="form-group">
                                <input type="date" name="" id="" class="form-control form-control-sm" value="<?= $list_budgeting->waktu_to ?>" readonly>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="pd-5 valign-top" width="150">Project</th>
                    <td class="pd-5 valign-top" width="400"><?= $list_budgeting->nm_project ?></td>
                    <th class="pd-5 valign-top" width="150"></th>
                    <td class="pd-5 valign-top" width="400"></td>
                </tr>
                <tr>
                    <th class="pd-5 valign-top" width="150">Tanggal</th>
                    <td class="pd-5 valign-top" width="400">
                        <input type="date" name="tgl" id="" class="form-control form-control-sm" value="<?= $header->tgl ?>" required>
                    </td>
                    <th class="pd-5 valign-top" width="150">Deskripsi</th>
                    <td class="pd-5 valign-top" width="400">
                        <textarea name="deskripsi" id="" class="form-control form-control-sm"><?= $header->deskripsi ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th class="pd-5 valign-top" width="150">Metode Pembayaran</th>
                    <td class="pd-5 valign-top" width="400">
                        <select name="metode_pembayaran" class="form-control form-control-sm">
                            <option value="1" <?= ($header->metode_pembayaran == '1') ? 'selected' : '' ?>>Kasbon</option>
                            <option value="2" <?= ($header->metode_pembayaran == '2') ? 'selected' : '' ?>>Direct Payment</option>
                            <option value="3" <?= ($header->metode_pembayaran == '3') ? 'selected' : '' ?>>PO</option>
                        </select>
                    </td>
                    <th colspan="2"></th>
                </tr>
            </table>
        </div>
    </div>

    <div class="box">
        <div class="box-header">
            <h4 style="font-weight: 800;">Informasi Pengajuan</h4>
        </div>

        <div class="box-body">
            <table class="table custom-table">
                <thead>
                    <tr>
                        <th rowspan="2" class="text-center">No.</th>
                        <th rowspan="2" class="text-center">Item</th>
                        <th colspan="3" class="text-center">Estimasi</th>
                        <th colspan="3" class="text-center">Terpakai</th>
                        <th colspan="3" class="text-center">Overbudget</th>
                    </tr>
                    <tr>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Price / Unit</th>
                        <th class="text-center">Total Budget</th>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Price / Unit</th>
                        <th class="text-center">Total Terpakai</th>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Budget</th>
                        <th class="text-center">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 0;

                    $ttl_qty_estimasi = 0;
                    $ttl_total_estimasi = 0;
                    $ttl_qty_terpakai = 0;
                    $ttl_total_terpakai = 0;
                    $ttl_qty_overbudget = 0;
                    $ttl_total_overbudget = 0;

                    foreach ($list_data_others as $item) {
                        if (isset($list_arr_kasbon[$item->id_others])) {
                            $no++;

                            $qty_pengajuan = (isset($list_arr_kasbon[$item->id_others])) ? $list_arr_kasbon[$item->id_others]['qty_pengajuan'] : 0;
                            $nominal_pengajuan = (isset($list_arr_kasbon[$item->id_others])) ? $list_arr_kasbon[$item->id_others]['nominal_pengajuan'] : 0;
                            $total_pengajuan = (isset($list_arr_kasbon[$item->id_others])) ? $list_arr_kasbon[$item->id_others]['total_pengajuan'] : 0;
                            $aktual_terpakai = (isset($list_arr_kasbon[$item->id_others])) ? $list_arr_kasbon[$item->id_others]['aktual_terpakai'] : 0;
                            if ($aktual_terpakai <= 0) {
                                $aktual_terpakai = $item->qty_final;
                            }
                            $sisa_budget = (isset($list_arr_kasbon[$item->id_others])) ? $list_arr_kasbon[$item->id_others]['sisa_budget'] : 0;
                            if ($sisa_budget <= 0) {
                                $sisa_budget = $item->total_final;
                            }

                            $qty_terpakai = (isset($list_arr_kasbon[$item->id_item]['qty_terpakai'])) ? $list_arr_kasbon[$item->id_item]['qty_terpakai'] : 0;
                            $nominal_terpakai = (isset($list_arr_kasbon[$item->id_item]['nominal_terpakai'])) ? $list_arr_kasbon[$item->id_item]['nominal_terpakai'] : 0;
                            $total_terpakai = (isset($list_arr_kasbon[$item->id_item]['total_terpakai'])) ? $list_arr_kasbon[$item->id_item]['total_terpakai'] : 0;

                            $qty_overbudget = (isset($list_arr_kasbon[$item->id_item]['qty_overbudget'])) ? $list_arr_kasbon[$item->id_item]['qty_overbudget'] : 0;
                            $nominal_overbudget = (isset($list_arr_kasbon[$item->id_item]['nominal_overbudget'])) ? $list_arr_kasbon[$item->id_item]['nominal_overbudget'] : 0;
                            $total_overbudget = (isset($list_arr_kasbon[$item->id_item]['total_overbudget'])) ? $list_arr_kasbon[$item->id_item]['total_overbudget'] : 0;

                            echo '<tr>';

                            echo '<td class="text-center">';
                            echo $no;
                            echo '<input type="hidden" name="detail_others[' . $no . '][id_others]" value="' . $item->id_others . '">';
                            echo '<input type="hidden" name="detail_others[' . $no . '][id_item]" value="' . $item->id_item . '">';
                            echo '<input type="hidden" name="detail_others[' . $no . '][nm_item]" value="' . $item->nm_biaya . '">';
                            echo '</td>';

                            echo '<td>' . $item->nm_biaya . '</td>';

                            echo '<td class="text-center">';
                            echo number_format($item->qty_final);
                            echo '<input type="hidden" name="detail_others[' . $no . '][qty_estimasi]" value="' . $item->qty_final . '">';
                            echo '</td>';

                            echo '<td class="text-right">';
                            echo number_format($item->price_unit_final, 2);
                            echo '<input type="hidden" name="detail_others[' . $no . '][price_unit_estimasi]" value="' . $item->price_unit_final . '">';
                            echo '</td>';

                            echo '<td class="text-right">';
                            echo number_format($item->total_final, 2);
                            echo '<input type="hidden" name="detail_others[' . $no . '][total_budget_estimasi]" value="' . $item->total_final . '">';
                            echo '</td>';

                            echo '<td class="text-center">';
                            echo number_format($qty_terpakai);
                            echo '<input type="hidden" name="detail_others[' . $no . '][qty_terpakai]" value="' . $qty_terpakai . '">';
                            echo '</td>';

                            echo '<td class="text-right">';
                            echo ($qty_terpakai > 0) ? number_format($nominal_terpakai, 2) : '-';
                            echo '<input type="hidden" name="detail_others[' . $no . '][nominal_terpakai]" value="' . $nominal_terpakai . '">';
                            echo '</td>';

                            echo '<td class="text-right">';
                            echo ($qty_terpakai > 0) ? number_format($total_terpakai, 2) : '-';
                            echo '<input type="hidden" name="detail_others[' . $no . '][total_terpakai]" value="' . $total_terpakai . '">';
                            echo '</td>';

                            echo '<td class="text-center">';
                            echo number_format($qty_overbudget);
                            echo '<input type="hidden" name="detail_others[' . $no . '][qty_overbudget]" value="' . $qty_overbudget . '">';
                            echo '</td>';

                            echo '<td class="text-right">';
                            echo ($qty_overbudget > 0) ? number_format($nominal_overbudget, 2) : '-';
                            echo '<input type="hidden" name="detail_others[' . $no . '][nominal_overbudget]" value="' . $nominal_overbudget . '">';
                            echo '</td>';

                            echo '<td class="text-right">';
                            echo ($qty_overbudget > 0) ? number_format($total_overbudget, 2) : '-';
                            echo '<input type="hidden" name="detail_others[' . $no . '][total_overbudget]" value="' . $total_overbudget . '">';
                            echo '<input type="hidden" name="detail_others[' . $no . '][sisa_budget]" value="' . $sisa_budget . '">';
                            echo '<input type="hidden" name="detail_others[' . $no . '][aktual_terpakai]" value="' . $aktual_terpakai . '">';
                            echo '</td>';

                            echo '</tr>';

                            // Sampai sini saja yang aktif

                            $ttl_qty_estimasi += $item->qty_estimasi;
                            $ttl_total_estimasi += $item->total_estimasi;
                            $ttl_qty_terpakai += $qty_terpakai;
                            $ttl_total_terpakai += ($qty_terpakai > 0) ? $total_terpakai : 0;
                            $ttl_qty_overbudget += $qty_overbudget;
                            $ttl_total_overbudget += ($qty_overbudget > 0) ? $total_overbudget : 0;
                        }
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2" class="text-center">Total</th>
                        <th class="text-center"><?= number_format($ttl_qty_estimasi, 2) ?></th>
                        <th></th>
                        <th class="text-right"><?= number_format($ttl_total_estimasi, 2) ?></th>
                        <th class="text-center"><?= number_format($ttl_qty_terpakai, 2) ?></th>
                        <th></th>
                        <th class="text-right"><?= number_format($ttl_total_terpakai, 2) ?></th>
                        <th class="text-center"><?= number_format($ttl_qty_overbudget, 2) ?></th>
                        <th></th>
                        <th class="text-right"><?= number_format($ttl_total_overbudget, 2) ?></th>
                    </tr>
                </tfoot>
            </table>

            <br><br>

            <h4 style="font-weight: 800;">Pengajuan</h4>

            <table class="table custom-table">
                <thead>
                    <tr>
                        <th rowspan="2" class="text-center">No</th>
                        <th rowspan="2" class="text-center">Item</th>
                        <th colspan="3" class="text-center">Pengajuan</th>
                    </tr>
                    <tr>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Price / Unit</th>
                        <th class="text-center">Total Pengajuan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 0;

                    $ttl_qty_pengajuan = 0;
                    $ttl_total_pengajuan = 0;
                    foreach ($list_data_others as $item) {
                        if (isset($list_arr_kasbon[$item->id_others])) {
                            $no++;

                            $qty_tambahan = (isset($data_overbudget_others[$item->id_others])) ? $data_overbudget_others[$item->id_others]['qty_budget_tambahan'] : 0;
                            $nominal_tambahan = (isset($data_overbudget_others[$item->id_others])) ? $data_overbudget_others[$item->id_others]['budget_tambahan'] : 0;
                            $pengajuan_budget = (isset($data_overbudget_others[$item->id_others])) ? $data_overbudget_others[$item->id_others]['pengajuan_budget'] : 0;

                            $aktual_terpakai = (isset($data_kasbon_others[$item->id_others]['ttl_qty_pengajuan'])) ? $data_kasbon_others[$item->id_others]['ttl_qty_pengajuan'] : 0;
                            $sisa_budget = (isset($data_kasbon_others[$item->id_others]['ttl_total_pengajuan'])) ? (($item->price_unit_final * $item->qty_final) - $data_kasbon_others[$item->id_others]['ttl_total_pengajuan']) : ($item->price_unit_final * $item->qty_final);

                            $readonly = '';
                            if (($sisa_budget + ($qty_tambahan * $nominal_tambahan)) <= 0) {
                                $readonly = 'readonly';
                            }

                            $qty_pengajuan = (isset($list_arr_kasbon[$item->id_others]['qty_pengajuan'])) ? $list_arr_kasbon[$item->id_others]['qty_pengajuan'] : '0';
                            $nominal_pengajuan = (isset($list_arr_kasbon[$item->id_others]['nominal_pengajuan'])) ? $list_arr_kasbon[$item->id_others]['nominal_pengajuan'] : '0';
                            $total_pengajuan = (isset($list_arr_kasbon[$item->id_others]['total_pengajuan'])) ? $list_arr_kasbon[$item->id_others]['total_pengajuan'] : '0';

                            echo '<tr>';

                            echo '<td class="text-center">' . $no . '</td>';

                            echo '<td>';
                            echo $item->nm_biaya;
                            echo '<input type="hidden" name="detail_others[' . $no . '][id_others]" value="' . $item->id_others . '">';
                            echo '<input type="hidden" name="detail_others[' . $no . '][id_item]" value="' . $item->id_item . '">';
                            echo '<input type="hidden" name="detail_others[' . $no . '][nm_item]" value="' . $item->nm_item . '">';
                            echo '</td>';

                            echo '<td>';
                            echo '<input type="number" name="detail_others[' . $no . '][qty_pengajuan]" class="form-control form-control-sm text-right" onchange="hitung_all_pengajuan()" step="0.01" value="' . $qty_pengajuan . '" ' . $readonly . '>';
                            echo '</td>';

                            echo '<td>';
                            echo '<input type="text" name="detail_others[' . $no . '][nominal_pengajuan]" class="form-control form-control-sm text-right auto_num hitung_per_price" data-no="' . $no . '" data-budget="' . $item->price_unit_final . '" value="' . $nominal_pengajuan . '" ' . $readonly . '>';
                            echo '</td>';

                            echo '<td>';
                            echo '<input type="text" name="detail_others[' . $no . '][total_pengajuan]" class="form-control form-control-sm text-right auto_num" onchange="hitung_all_pengajuan()" value="' . $total_pengajuan . '" readonly>';
                            echo '</td>';

                            echo '</tr>';

                            $ttl_qty_pengajuan += $qty_pengajuan;
                            $ttl_total_pengajuan += $total_pengajuan;
                        }
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2" class="text-center">Grand Total</th>
                        <th class="text-center ttl_qty_pengajuan"><?= number_format($ttl_qty_pengajuan, 2) ?></th>
                        <th></th>
                        <th class="text-center ttl_pengajuan"><?= number_format($ttl_total_pengajuan, 2) ?></th>
                    </tr>
                </tfoot>
            </table>

            <br><br>

            <div class="col-md-6">
                <table style="width: 100%">
                    <tr>
                        <th style="padding: 5px;">Bank</th>
                        <td style="padding: 5px;">
                            <input type="text" name="kasbon_bank" id="" class="form-control form-control-sm" placeholder="- Bank -" value="<?= $header->bank ?>">
                        </td>
                    </tr>
                    <tr>
                        <th style="padding: 5px;">Bank Number</th>
                        <td style="padding: 5px;">
                            <input type="text" name="kasbon_bank_number" id="" class="form-control form-control-sm" placeholder="- Bank Number -" value="<?= $header->bank_number ?>">
                        </td>
                    </tr>
                    <tr>
                        <th style="padding: 5px;">Account Name</th>
                        <td style="padding: 5px;">
                            <input type="text" name="kasbon_bank_account" id="" class="form-control form-control-sm" placeholder="- Account Name -" value="<?= $header->bank_account ?>">
                        </td>
                    </tr>
                </table>
            </div>

            <div class="col-md-12 mt-5">
                <a href="<?= base_url('kasbon_project/add_kasbon/' . urlencode(str_replace('/', '|', $list_budgeting->id_spk_budgeting))) ?>" class="btn btn-sm btn-danger">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
                <button type="submit" class="btn btn-sm btn-success">
                    <i class="fa fa-save"></i> Save
                </button>
            </div>
        </div>
    </div>
</form>


<script src="<?= base_url('assets/js/autoNumeric.js'); ?>"></script>
<script>
    $(document).ready(function() {
        $('.auto_num').autoNumeric();
    });

    function number_format(number, decimals, dec_point, thousands_sep) {
        // Strip all characters but numerical ones.
        number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
        var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
            dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
            s = '',
            toFixedFix = function(n, prec) {
                var k = Math.pow(10, prec);
                return '' + Math.round(n * k) / k;
            };
        // Fix for IE parseFloat(0.55).toFixed(0) = 0;
        s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || '').length < prec) {
            s[1] = s[1] || '';
            s[1] += new Array(prec - s[1].length + 1).join('0');
        }
        return s.join(dec);
    }

    function get_num(nilai = null) {
        if (nilai !== '' && nilai !== null) {
            nilai = nilai.split(',').join('');
            nilai = parseFloat(nilai);
        } else {
            nilai = 0;
        }

        return nilai;
    }

    function hitung_all_pengajuan() {
        var no = "<?= $no ?>";

        var ttl_qty = 0;
        var ttl_price = 0;
        var ttl_total = 0;

        for (i = 1; i <= no; i++) {
            var qty_pengajuan = $('input[name="detail_others[' + i + '][qty_pengajuan]"]').val();
            if (isNaN(qty_pengajuan) || qty_pengajuan == '') {
                qty_pengajuan = 0;
            } else {
                qty_pengajuan = parseFloat(qty_pengajuan);
            }

            var nominal_pengajuan = get_num($('input[name="detail_others[' + i + '][nominal_pengajuan]"]').val());
            if (qty_pengajuan < 1) {
                var total_pengajuan = get_num($('input[name="detail_others[' + i + '][total_pengajuan]"]').val());
            } else {
                var total_pengajuan = (nominal_pengajuan * qty_pengajuan);
            }

            $('input[name="detail_others[' + i + '][total_pengajuan]"]').autoNumeric('set', total_pengajuan);

            ttl_qty += qty_pengajuan;
            ttl_price += nominal_pengajuan;
            ttl_total += total_pengajuan;
        }

        $('.ttl_pengajuan').html(number_format(ttl_total));
        $('.ttl_qty_pengajuan').html(number_format(ttl_qty, 2));
    }

    $(document).on('change', '.hitung_per_price', function() {
        var no = $(this).data('no');
        var budget = $(this).data('budget');
        var pengajuan = get_num($(this).val());

        var qty = (pengajuan / budget);


        $('input[name="detail_others[' + no + '][qty_pengajuan]"]').val(qty.toFixed(2));
        $('input[name="detail_others[' + no + '][total_pengajuan]"]').autoNumeric('set', pengajuan);

        hitung_all_pengajuan();
    });

    $(document).on('submit', '#frm-data', function(e) {
        e.preventDefault();

        var no = "<?= $no ?>";

        var valid = 1;

        for (i = 1; i <= no; i++) {
            var qty_pengajuan = get_num($('input[name="detail_others[' + i + '][qty_pengajuan]"]').val());
            var nominal_pengajuan = get_num($('input[name="detail_others[' + i + '][nominal_pengajuan]"]').val());
            var sisa_budget = get_num($('input[name="detail_others[' + i + '][sisa_budget]"]').val());

            if (qty_pengajuan > 0 && qty_pengajuan < 1) {
                qty_pengajuan = 1;
            }
            if (valid == '1' && (nominal_pengajuan * qty_pengajuan) > sisa_budget) {
                valid = 0;
            }
        }

        if (valid == '0') {
            swal({
                type: 'warning',
                title: 'Warning !',
                text: 'Nominal pengajuan melebihi Sisa Budget !'
            });
        } else {
            swal({
                type: 'warning',
                title: 'Are you sure ?',
                text: 'This data will be saved !',
                showCancelButton: true
            }, function(next) {
                if (next) {
                    var formData = new FormData($('#frm-data')[0]);

                    $.ajax({
                        type: 'post',
                        url: siteurl + active_controller + 'update_kasbon_others',
                        data: formData,
                        cache: false,
                        processData: false,
                        contentType: false,
                        dataType: 'JSON',
                        success: function(result) {
                            if (result.status == '1') {
                                swal({
                                    type: 'success',
                                    title: 'Success !',
                                    text: result.pesan
                                }, function(lanjut) {
                                    window.location.href = siteurl + active_controller + "add_kasbon/<?= urlencode(str_replace('/', '|', $list_budgeting->id_spk_budgeting)) ?>"
                                });
                            } else {
                                swal({
                                    type: 'warning',
                                    title: 'Failed !',
                                    text: result.pesan
                                });
                            }
                        },
                        error: function(result) {
                            swal({
                                type: 'error',
                                title: 'Error !',
                                text: 'Please try again later !'
                            });
                        }
                    });
                }
            });
        }

    })
</script>