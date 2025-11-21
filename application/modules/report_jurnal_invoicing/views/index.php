<?php
$ENABLE_ADD     = has_permission('Report_Jurnal_Invoicing.Add');
$ENABLE_MANAGE  = has_permission('Report_Jurnal_Invoicing.Manage');
$ENABLE_VIEW    = has_permission('Report_Jurnal_Invoicing.View');
$ENABLE_DELETE  = has_permission('Report_Jurnal_Invoicing.Delete');
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
</style>
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<div class="box">
    <div class="box-header">
        <div class="row">
            <div class="col-md-2">
                <input type="date" name="tgl_from" class="form-control form-control-sm" placeholder="- Dari Tgl -">
            </div>
            <div class="col-md-2">
                <input type="date" name="tgl_to" id="" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <select class="form-control form-control-sm select2" name="klien">
                    <option value="">- Select Client -</option>
                    <?php
                    foreach ($list_customer as $item) {
                        echo '<option value="' . $item->id_customer . '">' . $item->nm_customer . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-control form-control-sm select2" name="company">
                    <option value="">- Select Company -</option>
                    <?php
                    foreach ($list_company as $item) {
                        echo '<option value="' . $item->id_company . '">' . $item->nm_company . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-control form-control-sm select2" name="divisi">
                    <option value="">- Select Divisi -</option>
                    <?php
                    foreach ($list_divisi as $item) {
                        echo '<option value="' . $item->id_divisi . '">' . $item->nm_divisi . '</option>';
                    }
                    ?>
                </select>
            </div>
            <br><br>
            <div class="col-md-12 text-left">
                <button type="button" class="btn btn-sm btn-primary search_jurnal"><i class="fa fa-search"></i> Search</button>
                <button type="button" class="btn btn-sm btn-danger reset_search"><i class="fa fa-exclamation"></i> Reset</button>
                <button type="button" class="btn btn-sm btn-success" onclick="download_excel();"><i class="fa fa-download"></i> Download Excel</button>
            </div>
            <!-- <div class="col-md-2">
            </div> -->
        </div>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <!-- <button type="button" class="btn btn-sm btn-primary" onclick="fix_company()">Fix Company</button> -->
        <table id="table_penawaran" class="table table-bordered table-striped">
            <thead>
                <tr class="bg-blue">
                    <th class="text-center">No.</th>
                    <th class="text-center">Tgl</th>
                    <th class="text-center">Klien</th>
                    <th class="text-center">No. Invoice</th>
                    <th class="text-center">Keterangan Tagihan</th>
                    <th class="text-center">Company</th>
                    <th class="text-center">Nama Divisi</th>
                    <th class="text-center">Uraian</th>
                    <th class="text-center">Original</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>

        </table>
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
        DataTables();
        autoNum();
        select2();
    });

    $(document).on('click', '.view_jurnal', function() {
        var no_transaksi = $(this).data('no_transaksi');
        var jenis_transaksi = $(this).data('jenis_transaksi');

        $.ajax({
            type: 'post',
            url: siteurl + active_controller + 'view_jurnal',
            data: {
                'no_transaksi': no_transaksi,
                'jenis_transaksi': jenis_transaksi
            },
            cache: false,
            success: function(result) {
                $('#ModalView').html(result);
                $('#myModalLabel').html('View Jurnal');

                $('#dialog-popup').modal('show');
            },
            error: function(result) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error !',
                    text: 'Please try again later !',
                    showConfirmButton: false,
                    showCancelButton: false,
                    allowOutsideClick: false,
                    timer: 3000
                });
            }
        });
    });

    $(document).on('click', '.search_jurnal', function() {
        var tgl_from = $('input[name="tgl_from"]').val();
        var tgl_to = $('input[name="tgl_to"]').val();
        var client = $('select[name="klien"]').val();
        var company = $('select[name="company"]').val();
        var divisi = $('select[name="divisi"]').val();

        DataTables(tgl_from, tgl_to, client, company, divisi);
    });

    $(document).on('click', '.reset_search', function() {
        $('input[name="tgl_from"]').val('');
        $('input[name="tgl_to"]').val('');
        $('select[name="klien"]').val('').trigger('change');
        $('select[name="company"]').val('').trigger('change');
        $('select[name="divisi"]').val('').trigger('change');

        DataTables();
    });

    function select2() {
        $('.select2').select2({
            width: '100%'
        });
    }

    function autoNum() {
        $('.autonum').autoNumeric('init');
    }

    function download_excel() {
        var tgl_from = $('input[name="tgl_from"]').val();
        var tgl_to = $('input[name="tgl_to"]').val();
        var client = $('select[name="klien"]').val();
        var company = $('select[name="company"]').val();
        var divisi = $('select[name="divisi"]').val();

        window.open(siteurl + active_controller + 'export_excel/?tgl_from=' + tgl_from + '&tgl_to=' + tgl_to + '&client=' + client + '&company=' + company + '&divisi=' + divisi);
    }

    function DataTables(tgl_from = null, tgl_to = null, client = null, company = null, divisi = null) {
        // var dataTables = $('#table_penawaran').dataTable();
        // dataTables.destroy();

        var dataTables = $('#table_penawaran').dataTable({
            ajax: {
                url: siteurl + active_controller + 'get_data_jurnal_invoicing',
                type: "POST",
                dataType: "JSON",
                data: function(d) {
                    d.tgl_from = tgl_from;
                    d.tgl_to = tgl_to;
                    d.client = client;
                    d.company = company;
                    d.divisi = divisi;
                }
            },
            columns: [{
                    data: 'no'
                },
                {
                    data: 'tgl'
                },
                {
                    data: 'klien'
                },
                {
                    data: 'no_invoice'
                },
                {
                    data: 'keterangan_tagihan'
                },
                {
                    data: 'company'
                },
                {
                    data: 'nm_divisi'
                },
                {
                    data: 'uraian'
                },
                {
                    data: 'original'
                },
                {
                    data: 'action'
                }
            ],
            responsive: true,
            processing: true,
            serverSide: true,
            stateSave: true,
            destroy: true,
            paging: true
        });
    }
</script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>