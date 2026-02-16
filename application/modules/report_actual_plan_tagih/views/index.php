<?php
$ENABLE_ADD     = has_permission('Report_Jurnal_Penerimaan.Add');
$ENABLE_MANAGE  = has_permission('Report_Jurnal_Penerimaan.Manage');
$ENABLE_VIEW    = has_permission('Report_Jurnal_Penerimaan.View');
$ENABLE_DELETE  = has_permission('Report_Jurnal_Penerimaan.Delete');
?>
<!-- <link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>"> -->
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

    .form-control {
        border-radius: 10px;
    }

    .btn {
        font-weight: bold;
    }
</style>
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<div class="box">
    <div class="box-header">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <select class="form-control form-control-sm select2" name="client">
                        <option value="">- Select Client -</option>
                        <?php
                        foreach ($list_customer as $item_customer) :
                        ?>

                            <option value="<?= $item_customer->id_customer ?>"><?= strtoupper($item_customer->nm_customer) ?></option>

                        <?php
                        endforeach;
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <select class="form-control form-control-sm select2" name="company">
                        <option value="">- Select Company -</option>
                        <?php
                        foreach ($list_company as $item_company) :
                        ?>

                            <option value="<?= $item_company->id_company ?>"><?= strtoupper($item_company->nm_company) ?></option>

                        <?php
                        endforeach;
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <select class="form-control form-control-sm select2" name="tahun">
                        <?php
                        for ($i = (date('Y') - 5); $i <= date('Y'); $i++) {
                            $selected = ($i == date('Y')) ? 'selected' : '';
                            echo '<option value="' . $i . '" ' . $selected . '>' . $i . '</option>';
                        }
                        ?>

                        <?php
                        for ($i = (date('Y') + 1); $i <= ((date('Y') + 1) + 5); $i++) {
                            $selected = ($i == date('Y')) ? 'selected' : '';
                            echo '<option value="' . $i . '" ' . $selected . '>' . $i . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <button type="button" class="btn btn-sm btn-primary" onclick="search_data();"><i class="fa fa-search"></i> Search</button>
                    <button type="button" class="btn btn-sm btn-success" onclick="download_excel();"><i class="fa fa-download"></i> Download Excel</button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="reset();"><i class="fa fa-refresh"></i> Reset</button>
                </div>
            </div>
        </div>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <!-- <button type="button" class="btn btn-sm btn-primary" onclick="fix_company()">Fix Company</button> -->
        <div class="table-responsive">
            <table id="table_penawaran" class="table table-bordered">
                <thead>
                    <tr class="bg-blue">
                        <th class="text-center" width="50px">No.</th>
                        <th class="text-center" width="300px">Company</th>
                        <th class="text-center" width="300px">No. SPK</th>
                        <th class="text-center" width="300px">Customer</th>
                        <th class="text-center" width="300px">Project</th>
                        <th class="text-center" width="300px">Nominal SPK</th>
                        <th class="text-center" width="300px">Nominal Invoice</th>
                        <th class="text-center" width="300px">Nominal Un-Invoiced</th>
                        <th class="text-center" width="300px">Macet</th>
                        <?php
                        for ($i = 1; $i <= 12; $i++) {
                            $no_bulan = sprintf('%02s', $i);
                            echo '<th class="text-center" width="300px">' . date('M', strtotime('' . date('Y') . '-' . $no_bulan . '-01')) . '</th>';
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>

                </tbody>
                <tfoot>
                    <tr>
                        <th>Grand Total</th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th class="ttl_nominal_spk">0</th>
                        <th class="ttl_nominal_invoice">0</th>
                        <th class="ttl_nominal_uninvoice">0</th>
                        <th class="ttl_macet">0</th>
                        <th class="ttl_jan">0</th>
                        <th class="ttl_feb">0</th>
                        <th class="ttl_mar">0</th>
                        <th class="ttl_apr">0</th>
                        <th class="ttl_may">0</th>
                        <th class="ttl_jun">0</th>
                        <th class="ttl_jul">0</th>
                        <th class="ttl_aug">0</th>
                        <th class="ttl_sep">0</th>
                        <th class="ttl_oct">0</th>
                        <th class="ttl_nov">0</th>
                        <th class="ttl_dec">0</th>
                    </tr>
                </tfoot>

            </table>
        </div>
    </div>
    <!-- /.box-body -->
</div>

<div class="modal modal-default fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="width: 1200px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span> Posting Jurnal</h4>
            </div>
            <form action="" method="post" id="frm-data">
                <div class="modal-body" id="ModalView">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">
                        <span class="glyphicon glyphicon-remove"></span> Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="form-data"></div>
<!-- DataTables -->
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script src="https://cdn.datatables.net/2.1.7/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- page script -->
<script type="text/javascript">
    $(document).ready(function() {
        var tahun = $('select[name="tahun"]').val();

        autoNum();
        select2();
        datatables('', '', tahun);
    });

    function select2() {
        $('.select2').select2({
            width: '100%'
        });
    }

    function autoNum() {
        $('.autonum').autoNumeric('init');
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

    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR'
        }).format(angka);
    }

    function reset() {
        $('select[name="client"]').val(null).trigger('change');
        $('select[name="company"]').val(null).trigger('change');
        // $('select[name="tahun"]').val();

        var client = $('select[name="client"]').val();
        var company = $('select[name="company"]').val();
        var tahun = $('select[name="tahun"]').val();

        datatables(client, company, tahun);
    }

    function search_data() {
        var client = $('select[name="client"]').val();
        var company = $('select[name="company"]').val();
        var tahun = $('select[name="tahun"]').val();

        datatables(client, company, tahun);
    }

    function download_excel() {
        const client = $('select[name="client"]').val();
        const company = $('select[name="company"]').val();
        const tahun = $('select[name="tahun"]').val();

        if (tahun.length <= 1) {
            Swal.fire({
                icon: 'warning',
                title: 'Warning !',
                text: 'Tahun harus diisi !',
                showConfirmButton: false,
                showCancelButton: false,
                allowEscapeKey: false,
                allowOutsideClick: false,
                timer: 3000
            });

            return false;
        }

        window.open(siteurl + active_controller + 'download_excel?client=' + client + '&company=' + company + '&tahun=' + tahun);
    }

    function datatables(client = null, company = null, tahun = null) {

        $('#table_penawaran').dataTable({
            serverSide: true,
            processing: true,
            destroy: true,
            paging: true,
            lengthMenu: [500, 1000, 2500, 5000, 10000],
            ajax: {
                type: 'get',
                url: siteurl + active_controller + 'get_data_report_apt',
                cache: false,
                dataType: 'json',
                data: function(d) {
                    d.client = client;
                    d.company = company;
                    d.tahun = tahun;
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error !',
                        text: error,
                        showConfirmButton: false,
                        showCancelButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                    });
                }
            },
            autoWidth: true,
            columns: [{
                    data: 'no'
                },
                {
                    data: 'company'
                },
                {
                    data: 'no_spk'
                },
                {
                    data: 'customer'
                },
                {
                    data: 'project'
                },
                {
                    data: 'nominal_spk'
                },
                {
                    data: 'nominal_invoice'
                },
                {
                    data: 'nominal_uninvoice'
                },
                {
                    data: 'macet'
                },
                {
                    data: 'jan'
                },
                {
                    data: 'feb'
                },
                {
                    data: 'mar'
                },
                {
                    data: 'apr'
                },
                {
                    data: 'may'
                },
                {
                    data: 'jun'
                },
                {
                    data: 'jul'
                },
                {
                    data: 'aug'
                },
                {
                    data: 'sep'
                },
                {
                    data: 'oct'
                },
                {
                    data: 'nov'
                },
                {
                    data: 'dec'
                },
            ],
            // --- TAMBAHKAN INI UNTUK COLSPAN ---
            footerCallback: function(row, data, start, end, display) {
                var api = this.api();

                // Cek apakah kolom masih lengkap (mencegah double manipulasi)
                if ($('th', row).length > 17) {
                    // Gabungkan index 0 sampai 4 (5 kolom pertama)
                    $('th', row).eq(0).attr('colspan', 5).html('Grand Total').addClass('text-center font-weight-bold');

                    // Sembunyikan th index 1, 2, 3, dan 4 supaya colspan-nya gak berantakan
                    $('th', row).eq(1).hide();
                    $('th', row).eq(2).hide();
                    $('th', row).eq(3).hide();
                    $('th', row).eq(4).hide();
                }
            },
            // --- ISI DATA TETAP DI DRAW CALLBACK ---
            drawCallback: function(settings) {
                var response = settings.json;
                if (response) {
                    // Pakai .text() atau .html() ke class masing-masing
                    $('.ttl_nominal_spk').html(number_format(response.total_nominal_spk, 2));
                    $('.ttl_nominal_invoice').html(number_format(response.total_invoice, 2));
                    $('.ttl_nominal_uninvoice').html(number_format(response.total_uninvoice, 2));
                    $('.ttl_macet').html(number_format(response.total_macet, 2));

                    // Looping bulan biar gak capek ngetik bro
                    const bulan = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
                    bulan.forEach(function(m) {
                        $('.ttl_' + m).html(number_format(response['total_' + m], 2));
                    });
                }
            }
        });
    }
</script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>