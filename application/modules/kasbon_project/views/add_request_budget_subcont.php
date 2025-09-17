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
    <input type="hidden" name="id_spk_budgeting" value="<?= $list_budgeting->id_spk_budgeting ?>">
    <input type="hidden" name="id_spk_penawaran" value="<?= $list_budgeting->id_spk_penawaran ?>">
    <input type="hidden" name="id_penawaran" value="<?= $list_budgeting->id_penawaran ?>">


    <div class="box">
        <div class="box-header">
            <h4 style="font-weight: 800;">Request Over Budget Akomodasi</h4>
        </div>

        <div class="box-body">
            <button type="button" class="btn btn-sm btn-success" onclick="add_custom_subcont()">
                <i class="fa fa-plus"></i> Add Over Budget Item
            </button>
            <br><br>
            <table class="table custom-table">
                <thead>
                    <tr>
                        <th rowspan="2" class="text-center valign-middle">No</th>
                        <th rowspan="2" class="text-center valign-middle">Item</th>
                        <th colspan="3" class="text-center valign-middle">Estimasi</th>
                        <th colspan="3" class="text-center valign-middle">Pengajuan</th>
                        <th rowspan="2" class="text-center valign-middle">Reason</th>
                        <th rowspan="2" class="text-center valign-middle"></th>
                    </tr>
                    <tr>
                        <th class="text-center valign-middle">Qty</th>
                        <th class="text-center valign-middle">Price/Unit</th>
                        <th class="text-center valign-middle">Total Budget</th>
                        <th class="text-center valign-middle">Qty Budget Tambahan</th>
                        <th class="text-center valign-middle">Budget Tambahan</th>
                        <th class="text-center valign-middle">Pengajuan New Budget</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 0;

                    $ttl_total_budget_estimasi = 0;
                    foreach ($list_kasbon_subcont as $item) {
                        $no++;
                        echo '<tr>';

                        echo '<td class="text-center">';
                        echo $no;
                        echo '<input type="hidden" name="req_subcont[' . $no . '][id_detail]" value="' . $item->id . '">';
                        echo '<input type="hidden" name="req_subcont[' . $no . '][id_aktifitas]" value="' . $item->id_aktifitas . '">';
                        echo '<input type="hidden" name="req_subcont[' . $no . '][nm_aktifitas]" value="' . $item->nm_aktifitas . '">';
                        echo '</td>';
                        echo '<td class="text-left">' . $item->nm_aktifitas . '</td>';

                        echo '<td class="text-center">';
                        echo number_format($item->mandays_subcont_final);
                        echo '<input type="hidden" name="req_subcont[' . $no . '][qty_estimasi]" value="' . $item->mandays_subcont_final . '">';
                        echo '</td>';

                        echo '<td class="text-center">';
                        echo number_format($item->mandays_rate_subcont_final, 2);
                        echo '<input type="hidden" name="req_subcont[' . $no . '][price_unit_estimasi]" value="' . $item->mandays_rate_subcont_final . '">';
                        echo '</td>';

                        echo '<td class="text-center">';
                        echo number_format($item->total_aktifitas_final, 2);
                        echo '<input type="hidden" class="form-control form-control-sm" name="req_subcont[' . $no . '][total_budget]" value="' . $item->total_aktifitas_final . '">';
                        echo '</td>';

                        echo '<td>';
                        echo '<input type="number" class="form-control form-control-sm text-right" name="req_subcont[' . $no . '][qty_budget_tambahan]" min="0" value="0" onchange="hitung_all()">';
                        echo '</td>';

                        echo '<td>';
                        echo '<input type="text" class="form-control form-control-sm text-right auto_num" name="req_subcont[' . $no . '][budget_tambahan]" value="' . $item->mandays_rate_subcont_final . '" readonly>';
                        echo '</td>';

                        echo '<td>';
                        echo '<input type="text" class="form-control form-control-sm text-right auto_num" name="req_subcont[' . $no . '][pengajuan_new_budget]" value="' . $item->total_aktifitas_final . '" readonly>';
                        echo '</td>';

                        echo '<td>';
                        echo '<input type="text" class="form-control form-control-sm" name="req_subcont[' . $no . '][reason]">';
                        echo '</td>';

                        echo '<td></td>';

                        echo '</tr>';

                        $ttl_total_budget_estimasi += $item->total_aktifitas_final;
                    }
                    ?>
                </tbody>
                <tbody class="list_custom_subcont">

                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4"></td>
                        <td class="text-center ttl_estimasi">
                            <?= number_format($ttl_total_budget_estimasi, 2) ?>
                        </td>
                        <td colspan="2"></td>
                        <td class="text-center ttl_new_budget">
                            <?= number_format($ttl_total_budget_estimasi, 2) ?>
                        </td>
                        <td>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>

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
    var no = "<?= $no ?>";

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

    function hitung_all() {
        var no = "<?= $no ?>";

        var ttl_pengajuan_new_budget = 0;

        for (i = 1; i <= no; i++) {
            var total_budget = get_num($('input[name="req_subcont[' + i + '][total_budget]"]').val());
            var qty_budget_tambahan = get_num($('input[name="req_subcont[' + i + '][qty_budget_tambahan]"]').val());
            var budget_tambahan = get_num($('input[name="req_subcont[' + i + '][budget_tambahan]"]').val());

            var pengajuan_budget_new = parseFloat(total_budget + (budget_tambahan * qty_budget_tambahan));

            $('input[name="req_subcont[' + i + '][pengajuan_new_budget]"]').val(number_format(pengajuan_budget_new, 2));

            ttl_pengajuan_new_budget += pengajuan_budget_new;
        }

        $('.ttl_new_budget').html(number_format(ttl_pengajuan_new_budget, 2));
    }

    function add_custom_subcont() {

        no++;
        var html = '<tr class="custom_subcont_' + no + '">';

        html += '<td class="text-center">' + no + '</td>';

        html += '<td class="text-left">';
        html += '<input type="hidden" name="custom_subcont[' + no + '][id_spk_budgeting]" value="<?= $list_budgeting->id_spk_budgeting ?>">';
        html += '<input type="hidden" name="custom_subcont[' + no + '][id_spk_penawaran]" value="<?= $list_budgeting->id_spk_penawaran ?>">';
        html += '<input type="hidden" name="custom_subcont[' + no + '][id_penawaran]" value="<?= $list_budgeting->id_penawaran ?>">';
        html += '<input type="text" class="form-control form-control-sm" name="custom_subcont[' + no + '][nm_item]">';
        html += '</td>';

        html += '<td class="text-left">';
        html += '<input type="number" class="form-control form-control-sm text-right" name="custom_subcont[' + no + '][estimasi_qty]" min="0" value="0" onchange="hitung_custom_subcont_est(' + no + ')">';
        html += '</td>';

        html += '<td class="text-left">';
        html += '<input type="text" class="form-control form-control-sm text-right auto_num" name="custom_subcont[' + no + '][estimasi_harga]" onchange="hitung_custom_subcont_est(' + no + ')">';
        html += '</td>';

        html += '<td class="text-left">';
        html += '<input type="text" class="form-control form-control-sm text-right" name="custom_subcont[' + no + '][estimasi_total]" readonly>';
        html += '</td>';

        html += '<td class="text-left">';
        html += '<input type="number" class="form-control form-control-sm text-right" name="custom_subcont[' + no + '][qty_budget_tambahan]" min="0" value="0" onchange="hitung_custom_subcont_budget(' + no + ');">';
        html += '</td>';

        html += '<td class="text-left">';
        html += '<input type="text" class="form-control form-control-sm text-right auto_num" name="custom_subcont[' + no + '][price_budget_tambahan]" onchange="hitung_custom_subcont_budget(' + no + ');">';
        html += '</td>';

        html += '<td class="text-left">';
        html += '<input type="text" class="form-control form-control-sm text-right auto_num" name="custom_subcont[' + no + '][total_budget_tambahan]" readonly>';
        html += '</td>';

        html += '<td class="text-left">';
        html += '<textarea class="form-control form-control-sm" name="custom_subcont[' + no + '][reason]"></textarea>';
        html += '</td>';

        html += '<td class="text-center">';
        html += '<button type="button" class="btn btn-sm btn-danger" onclick="del_custom_subcont('+no+')"><i class="fa fa-trash"></i></button>';
        html += '</td>';

        html += '</tr>';

        $('.list_custom_subcont').append(html);
        $('.auto_num').autoNumeric();
    }

    function hitung_custom_subcont_est(no) {
        var estimasi_qty = get_num($('input[name="custom_subcont[' + no + '][estimasi_qty]"]').val());
        var estimasi_harga = get_num($('input[name="custom_subcont[' + no + '][estimasi_harga]"]').val());

        var estimasi_total = parseFloat(estimasi_qty * estimasi_harga);

        $('input[name="custom_subcont[' + no + '][estimasi_total]"]').val(number_format(estimasi_total, 2));

        hitung_est_total();
    }

    function hitung_est_total() {
        var total_estimasi = 0;
        for(i = 1; i <= no; i++){
            var estimasi_total = get_num($('input[name="custom_subcont[' + i + '][estimasi_total]"]').val());
            total_estimasi += estimasi_total;
        }
        $('.ttl_estimasi').html(number_format(total_estimasi, 2));
    }

    function hitung_custom_subcont_budget(no) {
        var qty_budget_tambahan = get_num($('input[name="custom_subcont[' + no + '][qty_budget_tambahan]"]').val());
        var price_budget_tambahan = get_num($('input[name="custom_subcont[' + no + '][price_budget_tambahan]"]').val());

        var total_budget_tambahan = parseFloat(qty_budget_tambahan * price_budget_tambahan);

        $('input[name="custom_subcont[' + no + '][total_budget_tambahan]"]').val(number_format(total_budget_tambahan, 2));

        hitung_total_budget();
    }

    function hitung_total_budget() {
        var total_budget = 0;
        for(i = 1; i <= no; i++){
            var total_budget_tambahan = get_num($('input[name="custom_subcont[' + i + '][total_budget_tambahan]"]').val());
            total_budget += total_budget_tambahan;
        }
        $('.ttl_new_budget').html(number_format(total_budget, 2)); 
    }

    function del_custom_subcont(no) {
        $('.custom_subcont_' + no).remove();
        hitung_est_total();
        hitung_total_budget();
    }

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

                var id_spk_budgeting = $('input[name="id_spk_budgeting"]').val();

                $.ajax({
                    type: 'post',
                    url: siteurl + active_controller + 'save_request_budget_subcont',
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
                                window.location.href = siteurl + active_controller + "add_kasbon_subcont/<?= urlencode(str_replace('/', '|', $list_budgeting->id_spk_budgeting)) ?>";
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

    })
</script>