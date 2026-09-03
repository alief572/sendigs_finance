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

                        $readonly_qty = '';
                        $readonly_nominal = '';

                        if ($item['qty_kasbon'] <= 0) {
                            $readonly_qty = 'readonly';
                        }
                        if ($item['nominal_kasbon'] <= 0) {
                            $readonly_nominal = 'readonly';
                        }

                        echo '<tr>';

                        echo '<td class="text-center">';
                        echo $item['no'];
                        echo '<input type="hidden" name="detail_subcont[' . $item['no'] . '][id_detail_kasbon]" value="' . $item['id_detail_kasbon'] . '">';
                        echo '</td>';

                        echo '<td width="200">' . $item['nm_item'] . '</td>';

                        echo '<td class="text-center" width="120">';
                        echo number_format($item['qty_kasbon'], 2);
                        echo '<input type="hidden" name="detail_subcont[' . $item['no'] . '][qty_kasbon]" value="' . $item['qty_kasbon'] . '">';
                        echo '</td>';

                        echo '<td class="text-center" width="120">';
                        echo number_format($item['nominal_kasbon'], 2);
                        echo '<input type="hidden" name="detail_subcont[' . $item['no'] . '][nominal_kasbon]" value="' . $item['nominal_kasbon'] . '">';
                        echo '</td>';

                        echo '<td class="text-center" width="120">';
                        echo number_format($item['nominal_kasbon'] * $item['qty_kasbon'], 2);
                        echo '<input type="hidden" name="detail_subcont[' . $item['no'] . '][total_kasbon]" value="' . ($item['qty_kasbon'] * $item['nominal_kasbon']) . '">';
                        echo '</td>';

                        echo '<td>';
                        echo '<input type="text" name="detail_subcont[' . $item['no'] . '][qty_expense]" class="form-control form-control-sm auto_num text-right qty_expense" value="' . $item['qty_kasbon'] . '" data-no="' . $item['no'] . '" onchange="hitung_total(' . $item['no'] . ')" ' . $readonly_qty . '>';
                        echo '</td>';

                        echo '<td>';
                        echo '<input type="text" name="detail_subcont[' . $item['no'] . '][nominal_expense]" class="form-control form-control-sm auto_num text-right nominal_expense" value="' . $item['nominal_kasbon'] . '" data-no="' . $item['no'] . '" onchange="hitung_total(' . $item['no'] . ')" ' . $readonly_nominal . '>';
                        echo '</td>';

                        echo '<td>';
                        echo '<input type="text" name="detail_subcont[' . $item['no'] . '][total_expense]" class="form-control form-control-sm auto_num text-right nominal_expense" value="' . ($item['qty_kasbon'] * $item['nominal_kasbon']) . '" data-no="' . $item['no'] . '" onchange="hitung_total(' . $item['no'] . ')" readonly>';
                        echo '</td>';

                        echo '<td width="50" colspan="2">';
                        echo '<textarea class="form-control form-control-sm" name="detail_subcont[' . $item['no'] . '][keterangan]"  ' . $readonly_nominal . ' rows="5"></textarea>';
                        echo '</td>';

                        echo '</tr>';

                        $ttl_kasbon += ($item['qty_kasbon'] * $item['nominal_kasbon']);
                        $ttl_expense_report += ($item['qty_kasbon'] * $item['nominal_kasbon']);

                        $count_no++;
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="7" class="text-right">Total Kasbon</td>
                        <td class="text-right col_ttl_kasbon"><?= number_format($ttl_kasbon, 2) ?></td>
                        <td>Kelebihan Kasbon</td>
                        <td>
                            <input type="text" name="kelebihan_kasbon" class="form-control form-control-sm text-right kelebihan_kasbon" value="0" readonly>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="7" class="text-right">Total Expense Report</td>
                        <td class="text-right col_ttl_expense_report"><?= number_format($ttl_expense_report, 2) ?></td>
                        <td>Kelebihan Expense</td>
                        <td>
                            <input type="text" name="kelebihan_expense" class="form-control form-control-sm text-right kelebihan_expense" value="0" readonly>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="7" class="text-right">Selisih</td>
                        <td class="text-right col_selisih">0.00</td>
                        <td>Kontrol</td>
                        <td>
                            <input type="text" name="kontrol" class="form-control form-control-sm text-right kontrol" value="0" readonly>
                        </td>
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
                            </td>
                        </tr>
                        <tr>
                            <th style="padding: 5px;">Bukti Pengembalian</th>
                            <td style="padding: 5px;">
                                <input type="file" name="bukti_pengembalian[]" id="" class="form-control form-control-sm" multiple>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                Keterangan Kurang Bayar
                            </th>
                            <td>
                                <textarea class="form-control form-control-sm" name="keterangan_kurang_bayar"></textarea>
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
                                        echo '<option value="' . $item->id . '">' . $item->nama_bank . ' - ' . $item->rekening . ' - ' . $item->nama . '</option>';
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
                                <th class="text-center">Kredit</th>
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

                <input type="hidden" name="ttl_debit">
                <input type="hidden" name="ttl_kredit">
            </div>

            <div class="col-md-12">
                <a href="<?= base_url('expense_report_project/add/' . urlencode(str_replace('/', '|', $id_spk_budgeting))) ?>" class="btn btn-sm btn-danger">
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
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('.auto_num').autoNumeric('init', {
            allowNegative: true,
            minimumValue: '-999999999', // Atur sesuai kebutuhan
            maximumValue: '999999999'
        });
        $('.select2').select2({
            width: '100%'
        });

        set_jurnal();
        hitung_total(1);
    });

    $(document).on('submit', '#frm-data', function(e) {
        e.preventDefault();

        var tipe = "<?= $tipe; ?>";

        var url_dest = 'save_expense_report';

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
                    url: siteurl + active_controller + url_dest,
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
</script>