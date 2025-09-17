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
            <h4 style="font-weight: 800;">Request Over Budget Lab</h4>
        </div>

        <div class="box-body">
            <table class="table custom-table">
                <thead>
                    <tr>
                        <th rowspan="2" class="text-center valign-middle">No</th>
                        <th rowspan="2" class="text-center valign-middle">Item</th>
                        <th colspan="3" class="text-center valign-middle">Estimasi</th>
                        <th colspan="4" class="text-center valign-middle">Pengajuan</th>
                    </tr>
                    <tr>
                        <th class="text-center valign-middle">Qty</th>
                        <th class="text-center valign-middle">Price/Unit</th>
                        <th class="text-center valign-middle">Total Budget</th>
                        <th class="text-center valign-middle">Qty Budget Tambahan</th>
                        <th class="text-center valign-middle">Budget Tambahan</th>
                        <th class="text-center valign-middle">Pengajuan New Budget</th>
                        <th class="text-center valign-middle">Reason</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;

                    $ttl_total_budget_estimasi = 0;
                    foreach ($list_kasbon_akomodasi as $item) {
                        echo '<tr>';

                        echo '<td class="text-center">';
                        echo $no;
                        echo '<input type="hidden" name="req_lab[' . $no . '][id_detail]" value="' . $item->id . '">';
                        echo '<input type="hidden" name="req_lab[' . $no . '][id_item]" value="' . $item->id_item . '">';
                        echo '<input type="hidden" name="req_lab[' . $no . '][nm_item]" value="' . $item->nm_item . '">';
                        echo '</td>';
                        echo '<td class="text-left">' . $item->nm_item . '</td>';

                        echo '<td class="text-center">';
                        echo number_format($item->qty_final);
                        echo '<input type="hidden" name="req_lab[' . $no . '][qty_estimasi]" value="' . $item->qty_final . '">';
                        echo '</td>';

                        echo '<td class="text-center">';
                        echo number_format($item->price_unit_final, 2);
                        echo '<input type="hidden" name="req_lab[' . $no . '][price_unit_estimasi]" value="' . $item->price_unit_final . '">';
                        echo '</td>';

                        echo '<td class="text-center">';
                        echo number_format($item->total_final, 2);
                        echo '<input type="hidden" class="form-control form-control-sm" name="req_lab[' . $no . '][total_budget]" value="' . $item->total_final . '">';
                        echo '</td>';

                        echo '<td>';
                        echo '<input type="number" class="form-control form-control-sm text-right" name="req_lab[' . $no . '][qty_budget_tambahan]" min="0" value="0" onchange="hitung_all()">';
                        echo '</td>';

                        echo '<td>';
                        echo '<input type="text" class="form-control form-control-sm text-right auto_num" name="req_lab[' . $no . '][budget_tambahan]" value="'.$item->price_unit_final.'" readonly>';
                        echo '</td>';

                        echo '<td>';
                        echo '<input type="text" class="form-control form-control-sm text-right auto_num" name="req_lab[' . $no . '][pengajuan_new_budget]" value="' . $item->total_final . '" readonly>';
                        echo '</td>';

                        echo '<td>';
                        echo '<input type="text" class="form-control form-control-sm" name="req_lab[' . $no . '][reason]">';
                        echo '</td>';

                        echo '</tr>';

                        $ttl_total_budget_estimasi += $item->total_final;

                        $no++;
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4"></td>
                        <td class="text-center">
                            <?= number_format($ttl_total_budget_estimasi, 2) ?>
                        </td>
                        <td colspan="2"></td>
                        <td class="text-center ttl_new_budget">
                            <?= number_format($ttl_total_budget_estimasi, 2) ?>
                        </td>
                        <td>

                        </td>
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
            var total_budget = get_num($('input[name="req_lab[' + i + '][total_budget]"]').val());
            var qty_budget_tambahan = get_num($('input[name="req_lab[' + i + '][qty_budget_tambahan]"]').val());
            var budget_tambahan = get_num($('input[name="req_lab[' + i + '][budget_tambahan]"]').val());

            var pengajuan_budget_new = parseFloat(total_budget + (budget_tambahan * qty_budget_tambahan));

            $('input[name="req_lab[' + i + '][pengajuan_new_budget]"]').val(number_format(pengajuan_budget_new, 2));

            ttl_pengajuan_new_budget += pengajuan_budget_new;
        }

        $('.ttl_new_budget').html(number_format(ttl_pengajuan_new_budget, 2));
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
                    url: siteurl + active_controller + 'save_request_budget_lab',
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
                                window.location.href = siteurl + active_controller + "add_kasbon_lab/<?= urlencode(str_replace('/', '|', $list_budgeting->id_spk_budgeting)) ?>";
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