<?php
$ENABLE_ADD     = has_permission('Expense_Report_Project.Add');
$ENABLE_MANAGE  = has_permission('Expense_Report_Project.Manage');
$ENABLE_VIEW    = has_permission('Expense_Report_Project.View');
$ENABLE_DELETE  = has_permission('Expense_Report_Project.Delete');

$title_header = 'Subcont';
if ($tipe == '2') {
    $title_header = 'Akomodasi';
}
if ($tipe == '3') {
    $title_header = 'Others';
}
if ($tipe == '4') {
    $title_header = 'Lab';
}
if ($tipe == '5') {
    $title_header = 'Subcont Tenaga Ahli';
}
if ($tipe == '6') {
    $title_header = 'Subcont Perusahaan';
}

$enb_reject_reason = 'd-none';
if ($header->reject_reason !== '' && $header->reject_reason !== null) {
    $enb_reject_reason = '';
}

$hide_jurnal_pph21 = 'd-none';
if (!empty($list_jurnal_pph21) && $list_jurnal_pph21['nominal_pph'] > 0) {
    $hide_jurnal_pph21 = '';
}
?>

<link rel="stylesheet" href="https://cdn.datatables.net/2.1.7/css/dataTables.dataTables.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

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
</style>

<form id="frm-data" enctype="multipart/form-data">
    <input type="hidden" name="id_expense" value="<?= $header->id ?>">
    <input type="hidden" name="id_header" value="<?= $id_header ?>">
    <input type="hidden" name="id_spk_budgeting" value="<?= $id_spk_budgeting ?>">
    <input type="hidden" name="id_spk_penawaran" value="<?= $id_spk_penawaran ?>">
    <input type="hidden" name="id_penawaran" value="<?= $id_penawaran ?>">
    <input type="hidden" name="tipe" value="<?= $tipe ?>">
    <div class="box">
        <div class="box-header">
            <h3>List Item <?= $title_header ?></h3>
        </div>

        <div class="box-body" style="z-index: 1 !important;">
            <div class="col-md-6 <?= $enb_reject_reason ?>">
                <div class="alert alert-dismissable alert-danger">
                    <span>
                        <i class="fa fa-close"></i> Reject Reason
                    </span>

                    <br><br>

                    <span class="text-bold"><?= $header->reject_reason ?></span>
                </div>
            </div>
            <table class="table custom-table mt-5">
                <thead>
                    <tr>
                        <th class="text-center" rowspan="2">No.</th>
                        <th class="text-center" rowspan="2">Item</th>
                        <th class="text-center" colspan="3">Kasbon</th>
                        <th class="text-center" colspan="3">Expense Report</th>
                        <th class="text-center" rowspan="2" colspan="2">Keterangan</th>
                    </tr>
                    <tr>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Nominal</th>
                        <th class="text-center">Total Kasbon</th>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Nominal</th>
                        <th class="text-center">Total Expense</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $ttl_expense_report = 0;
                    $ttl_kasbon = 0;

                    $count_no = 0;
                    foreach ($datalist_item as $item) {

                        $readonly_qty = 'readonly';
                        $readonly_nominal = 'readonly';

                        $qty_expense = (isset($datalist_item_expense[$item['id_detail_kasbon']])) ? $datalist_item_expense[$item['id_detail_kasbon']]['qty_expense'] : 0;
                        $nominal_expense = (isset($datalist_item_expense[$item['id_detail_kasbon']])) ? $datalist_item_expense[$item['id_detail_kasbon']]['nominal_expense'] : 0;
                        $total_expense = (isset($datalist_item_expense[$item['id_detail_kasbon']])) ? $datalist_item_expense[$item['id_detail_kasbon']]['total_expense'] : 0;
                        $keterangan = (isset($datalist_item_expense[$item['id_detail_kasbon']])) ? $datalist_item_expense[$item['id_detail_kasbon']]['keterangan'] : '';

                        if ($qty_expense > 0) {
                            $readonly_qty = '';
                            $readonly_nominal = '';
                        }

                        echo '<tr>';

                        echo '<td class="text-center">';
                        echo $item['no'];
                        echo '<input type="hidden" name="detail_subcont[' . $item['no'] . '][id_detail_kasbon]" value="' . $item['id_detail_kasbon'] . '">';
                        echo '</td>';

                        echo '<td width="300">' . $item['nm_item'] . '</td>';

                        echo '<td class="text-center" width="200">';
                        echo number_format($item['qty_kasbon'], 2);
                        echo '<input type="hidden" name="detail_subcont[' . $item['no'] . '][qty_kasbon]" value="' . $item['qty_kasbon'] . '">';
                        echo '</td>';

                        echo '<td class="text-center" width="200">';
                        echo number_format($item['nominal_kasbon'], 2);
                        echo '<input type="hidden" name="detail_subcont[' . $item['no'] . '][nominal_kasbon]" value="' . $item['nominal_kasbon'] . '">';
                        echo '</td>';

                        echo '<td width="200">';
                        echo '<input type="text" name="detail_subcont[' . $item['no'] . '][total_kasbon]" class="form-control form-control-sm auto_num text-right " value="' . ($item['qty_kasbon'] * $item['nominal_kasbon']) . '" data-no="' . $item['no'] . '" onchange="hitung_total(' . $item['no'] . ')" readonly>';
                        echo '</td>';

                        echo '<td width="200">';
                        echo '<input type="text" name="detail_subcont[' . $item['no'] . '][qty_expense]" class="form-control form-control-sm auto_num text-right qty_expense" value="' . $qty_expense . '" data-no="' . $item['no'] . '" onchange="hitung_total(' . $item['no'] . ')" ' . $readonly_qty . '>';
                        echo '</td>';

                        echo '<td width="200">';
                        echo '<input type="text" name="detail_subcont[' . $item['no'] . '][nominal_expense]" class="form-control form-control-sm auto_num text-right nominal_expense" value="' . $nominal_expense . '" data-no="' . $item['no'] . '" onchange="hitung_total(' . $item['no'] . ')" ' . $readonly_nominal . '>';
                        echo '</td>';

                        echo '<td width="200">';
                        echo '<input type="text" name="detail_subcont[' . $item['no'] . '][total_expense]" class="form-control form-control-sm auto_num text-right nominal_expense" value="' . ($nominal_expense * $qty_expense) . '" data-no="' . $item['no'] . '" onchange="hitung_total(' . $item['no'] . ')" ' . $readonly_nominal . '>';
                        echo '</td>';

                        echo '<td width="400" colspan="2">';
                        echo '<textarea class="form-control form-control-sm" readonly>' . $keterangan . '</textarea>';
                        echo '</td>';

                        echo '</tr>';

                        $ttl_kasbon += ($item['qty_kasbon'] * $item['nominal_kasbon']);
                        $ttl_expense_report += ($total_expense);

                        $count_no++;
                    }

                    $kelebihan_kasbon = ($ttl_kasbon > $ttl_expense_report) ? ($ttl_kasbon - $ttl_expense_report) : 0;
                    $kelebihan_expense = ($ttl_expense_report > $ttl_kasbon) ? ($ttl_expense_report - $ttl_kasbon) : 0;
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-right">Total Kasbon</td>
                        <td class="text-right col_ttl_kasbon"><?= number_format($ttl_kasbon, 2) ?></td>
                        <td>Kelebihan Kasbon</td>
                        <td>
                            <input type="text" name="kelebihan_kasbon" class="form-control form-control-sm text-right kelebihan_kasbon" value="<?= number_format($kelebihan_kasbon, 2) ?>" readonly>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-right">Total Expense Report</td>
                        <td class="text-right col_ttl_expense_report"><?= number_format($ttl_expense_report, 2) ?></td>
                        <td>Kelebihan Expense</td>
                        <td>
                            <input type="text" name="kelebihan_expense" class="form-control form-control-sm text-right kelebihan_expense" value="<?= number_format($kelebihan_expense, 2) ?>" readonly>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-right">Selisih</td>
                        <td class="text-right col_selisih"><?= number_format($header->selisih, 2) ?></td>
                        <td>Kontrol</td>
                        <td>
                            <input type="text" name="kontrol" class="form-control form-control-sm text-right kontrol" value="<?= number_format($header->selisih, 2) ?>" readonly>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>

            <br><br>

            <div class="row">
                <div class="col-md-6">
                    <table style="width: 100%">
                        <tr>
                            <th style="padding: 5px;">Bukti Penggunaan</th>
                            <td style="padding: 5px;">
                                <input type="file" name="bukti_penggunaan[]" id="" class="form-control form-control-sm" multiple>
                                <?php
                                if (count($list_bukti_penggunaan) > 0) {
                                    echo '<button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#dialog-popup2">';
                                    echo '<i class="fa fa-list"></i> List Bukti Penggunaan';
                                    echo '</button>';
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="padding: 5px;">Bukti Pengembalian</th>
                            <td style="padding: 5px;">
                                <input type="file" name="bukti_pengembalian[]" id="" class="form-control form-control-sm" multiple>
                                <?php
                                if (count($list_bukti_pengembalian) > 0) {
                                    echo '<button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#dialog-popup">';
                                    echo '<i class="fa fa-list"></i> List Bukti Pengemblian';
                                    echo '</button>';
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                Keterangan Kurang Bayar
                            </th>
                            <td>
                                <textarea class="form-control form-control-sm" name="keterangan_kurang_bayar"><?= $header->keterangan_kurang_bayar ?></textarea>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table style="width: 100%">
                        <tr>
                            <th style="padding: 5px;">Bank</th>
                            <td style="padding: 5px;">
                                <select name="bank" class="form-control form-control-sm select2" onchange="set_jurnal()">
                                    <option value="">- Pilih Bank -</option>
                                    <?php
                                    foreach ($list_bank  as $item) :
                                        $selected = ($item->id == $header->id_bank) ? 'selected' : '';
                                        echo '<option value="' . $item->id . '" ' . $selected . '>' . $item->nama_bank . ' - ' . $item->rekening . ' - ' . $item->nama . '</option>';
                                    endforeach;
                                    ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-12">
                    <br><br>

                    <table class="table custom-table">
                        <thead>
                            <tr>
                                <th class="text-center">Tanggal Jurnal</th>
                                <th class="text-center">COA</th>
                                <th class="text-center">Nama Company</th>
                                <th class="text-center">Nama Account</th>
                                <th class="text-center">Deskripsi</th>
                                <th class="text-center">Debit</th>
                                <th class="text-center">Credit</th>
                            </tr>
                        </thead>
                        <tbody class="tbody_jurnal">
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-center">Balancing</th>
                                <th class="text-right ttl_debit">0.00</th>
                                <th class="text-right ttl_kredit">0.00</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="col-md-12 <?= $hide_jurnal_pph21 ?>">
                    <h4>Jurnal PPh 21</h4>
                    <table class="table custom-table">
                        <thead>
                            <tr>
                                <th class="text-center">Tanggal Jurnal</th>
                                <th class="text-center">COA</th>
                                <th class="text-center">Nama Company</th>
                                <th class="text-center">Nama Account</th>
                                <th class="text-center">Deskripsi</th>
                                <th class="text-center">Debit</th>
                                <th class="text-center">Credit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?= $list_jurnal_pph21['hasil'] ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-center">
                                    Balancing
                                </th>
                                <th class="text-right jurnal_pph_debit"><?= number_format(0) ?></th>
                                <th class="text-right jurnal_pph_kredit"><?= number_format($list_jurnal_pph21['nominal_pph']) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <a href="<?= base_url('expense_report_project/add/' . urlencode(str_replace('/', '|', $id_spk_budgeting))) ?>" class="btn btn-sm btn-danger">
                <i class="fa fa-arrow-left"></i> Back
            </a>
            <button type="submit" class="btn btn-sm btn-success">
                <i class="fa fa-save"></i> Save
            </button>
        </div>
    </div>

    <input type="hidden" name="ttl_debit">
    <input type="hidden" name="ttl_kredit">
</form>

<div class="modal modal-default fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" style='width:70%; '>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;List Bukti Pengembalian</h4>
            </div>
            <div class="modal-body" id="ModalView">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Document Link</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        foreach ($list_bukti_pengembalian as $item) {
                            echo '<tr>';
                            echo '<td class="text-center">' . $no . '</td>';
                            echo '<td><a href="' . base_url($item->document_link) . '" target="_blank">' . $item->document_link . '</a></td>';
                            echo '<td>';
                            echo '<button type="button" class="btn btn-sm btn-danger del_bukti_pengembalian" data-id="' . $item->id . '" title="Delete Bukti Pengembalian"><i class="fa fa-trash"></i></button>';
                            echo '</td>';
                            echo '</tr>';

                            $no++;
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>



<div class="modal modal-default fade" id="dialog-popup2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" style='width:70%; '>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;List Bukti Peggunaan</h4>
            </div>
            <div class="modal-body" id="ModalView">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Document Link</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        foreach ($list_bukti_penggunaan as $item) {
                            echo '<tr>';
                            echo '<td class="text-center">' . $no . '</td>';
                            echo '<td><a href="' . base_url($item->upload_file) . '" target="_blank">' . $item->upload_file . '</a></td>';
                            echo '<td>';
                            echo '<button type="button" class="btn btn-sm btn-danger del_bukti_penggunaan" title="Delete" data-id="' . $item->id . '"><i class="fa fa-trash"></i></button>';
                            echo '</td>';
                            echo '</tr>';

                            $no++;
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/js/autoNumeric.js'); ?>"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('.auto_num').autoNumeric();

        $('.select2').select2({
            width: '100%'
        });

        hitung_total(1);
        set_jurnal();
    });

    $(document).on('click', '.del_bukti_penggunaan', function() {
        var id = $(this).data('id');

        swal({
            type: 'warning',
            title: 'Are you sure ?',
            text: 'This data will be deleted !',
            showCancelButton: true
        }, function(next) {
            if (next) {
                $.ajax({
                    type: 'post',
                    url: siteurl + active_controller + 'del_bukti_penggunaan',
                    data: {
                        'id': id
                    },
                    cache: false,
                    dataType: 'json',
                    success: function(result) {
                        if (result.status == '1') {
                            swal({
                                type: 'success',
                                title: 'Success !',
                                text: 'Data has been deleted !'
                            }, function() {
                                location.reload();
                            });
                        } else {
                            swal({
                                type: 'warning',
                                title: 'Failed !',
                                text: 'Please try again later !'
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
    });

    $(document).on('click', '.del_bukti_pengembalian', function() {
        var id = $(this).data('id');

        swal({
            type: 'warning',
            title: 'Are you sure ?',
            text: 'This data will be deleted !',
            showCancelButton: true
        }, function(next) {
            if (next) {
                $.ajax({
                    type: 'post',
                    url: siteurl + active_controller + 'del_bukti_pengembalian',
                    data: {
                        'id': id
                    },
                    cache: false,
                    dataType: 'json',
                    success: function(result) {
                        if (result.status == '1') {
                            swal({
                                type: 'success',
                                title: 'Success !',
                                text: 'Data has been deleted !'
                            }, function() {
                                location.reload();
                            });
                        } else {
                            swal({
                                type: 'warning',
                                title: 'Failed !',
                                text: 'Please try again later !'
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
    });

    $(document).on('submit', '#frm-data', function(e) {
        e.preventDefault();

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
                    url: siteurl + active_controller + 'update_expense_report_subcont',
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
                                window.location.href = siteurl + active_controller + 'add/' + '<?= urlencode(str_replace('/', '|', $id_spk_budgeting)) ?>';
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
    });

    function get_num(nilai = null) {
        if (nilai !== '' && nilai !== null) {
            nilai = nilai.split(',').join('');
            nilai = parseFloat(nilai);
        } else {
            nilai = 0;
        }

        return nilai;
    }

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

    function hitung_total(no) {
        var count_no = "<?= $count_no ?>";

        var ttl_expense_report = 0;
        var ttl_kasbon = 0;
        var selisih = 0;

        for (i = 1; i <= count_no; i++) {
            var qty_kasbon = get_num($('input[name="detail_subcont[' + i + '][qty_kasbon]"]').val());
            var nominal_kasbon = get_num($('input[name="detail_subcont[' + i + '][nominal_kasbon]"]').val());

            var qty_expense = get_num($('input[name="detail_subcont[' + i + '][qty_expense]"]').val());
            if (qty_expense <= 0) {
                qty_expense = 0;
            }
            var nominal_expense = get_num($('input[name="detail_subcont[' + i + '][nominal_expense]"]').val());

            var total_expense = parseFloat(qty_expense * nominal_expense);

            $('input[name="detail_subcont[' + i + '][total_expense]"]').val(number_format(total_expense, 2));

            ttl_expense_report += (qty_expense * nominal_expense);
            ttl_kasbon += (qty_kasbon * nominal_kasbon);

            selisih += ((qty_kasbon * nominal_kasbon) - (qty_expense * nominal_expense));
        }

        $('.col_ttl_expense_report').html(number_format(ttl_expense_report, 2));
        $('.col_ttl_kasbon').html(number_format(ttl_kasbon, 2));
        $('.col_selisih').html(number_format(selisih, 2));
        $('input[name="kontrol"]').val(number_format(selisih, 2));

        hitung_kelebihan_dan_control();
        set_jurnal();
    }

    function set_jurnal() {
        var count_no = parseInt(<?= $count_no ?>)
        var id_header = $('input[name="id_header"]').val();

        var kelebihan_kasbon = get_num($('input[name="kelebihan_kasbon"]').val());
        var kelebihan_expense = get_num($('input[name="kelebihan_expense"]').val());
        var kontrol = get_num($('input[name="kontrol"]').val());

        var total_kasbon = get_num($('.col_ttl_kasbon').html());
        var total_expense = get_num($('.col_ttl_expense_report').html());

        var id_bank = $('select[name="bank"]').val();

        var id_penawaran = "<?= $id_penawaran ?>";

        var arr_total_expense = {};
        for (i = 1; i <= count_no; i++) {
            var id_detail_kasbon = $('input[name="detail_subcont[' + i + '][id_detail_kasbon]"]').val();
            var total_expense = get_num($('input[name="detail_subcont[' + i + '][total_expense]"]').val());

            var arr = [];

            arr_total_expense[id_detail_kasbon] = total_expense;
        }

        $.ajax({
            type: 'post',
            url: siteurl + active_controller + 'set_jurnal_expense',
            data: {
                'kelebihan_kasbon': kelebihan_kasbon,
                'kelebihan_expense': kelebihan_expense,
                'kontrol': kontrol,
                'total_kasbon': total_kasbon,
                'total_expense': total_expense,
                'id_penawaran': id_penawaran,
                'id_bank': id_bank,
                'id_header': id_header,
                'id_expense': $('input[name="id_expense"]').val(),
                'arr_total_expense': arr_total_expense
            },
            cache: false,
            dataType: 'json',
            success: function(result) {
                $('.tbody_jurnal').html(result.hasil);
                $('.ttl_debit').html(number_format(result.ttl_debit));
                $('.ttl_kredit').html(number_format(result.ttl_kredit));
            }
        });
    }

    function hitung_kelebihan_dan_control() {
        var total_kasbon = get_num($('.col_ttl_kasbon').html());
        var total_expense = get_num($('.col_ttl_expense_report').html());

        var kelebihan_kasbon = 0;
        var kelebihan_expense = 0;

        if (total_kasbon > total_expense) {
            kelebihan_kasbon = (total_kasbon - total_expense);
        }
        if (total_expense > total_kasbon) {
            kelebihan_expense = (total_expense - total_kasbon);
        }

        // alert(kelebihan_kasbon + ' - ' + kelebihan_expense);

        $('input[name="kelebihan_kasbon"]').val(number_format(kelebihan_kasbon, 2));
        $('input[name="kelebihan_expense"]').val(number_format(kelebihan_expense, 2));

        set_jurnal();
    }
</script>