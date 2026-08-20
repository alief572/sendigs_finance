<?php
$ENABLE_ADD     = has_permission('Payment_List.Add');
$ENABLE_MANAGE  = has_permission('Payment_List.Manage');
$ENABLE_DELETE  = has_permission('Payment_List.Delete');
$ENABLE_VIEW    = has_permission('Payment_List.View');
?>

<link rel="stylesheet" href="https://cdn.datatables.net/2.1.7/css/dataTables.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    .btn {
        border-radius: 8px;
    }

    .filter-card {
        background: #fdfdfd;
        border: 1px solid #e1e6ef;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .filter-actions {
        margin-top: 25px;
    }

    #mytabledata th {
        background-color: #f4f6f9;
        font-weight: 600;
        vertical-align: middle;
        text-align: center;
    }

    #mytabledata td {
        vertical-align: middle;
    }
</style>

<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-list-alt"></i> Payment List</h3>
    </div>
    <div class="box-body">
        <!-- Filter Card -->
        <div class="filter-card">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label><i class="fa fa-calendar"></i> Tanggal (Dokumen / Pengajuan / Bayar)</label>
                        <input type="text" class="form-control form-control-sm" id="filter_tgl" placeholder="Pilih rentang tanggal">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label><i class="fa fa-tags"></i> Tipe Pengajuan</label>
                        <select id="filter_tipe" class="form-control form-control-sm select2">
                            <option value="">- Semua Tipe -</option>
                            <option value="kasbon">Kasbon</option>
                            <option value="expense">Expense</option>
                            <option value="transportasi">Transportasi</option>
                            <option value="periodik">Periodik / Rutin</option>
                            <option value="direct_payment">Direct Payment</option>
                            <option value="nonpo">Non PO</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group filter-actions">
                        <button type="button" class="btn btn-sm btn-primary" onclick="search_data()"><i class="fa fa-search"></i> Search</button>
                        <button type="button" class="btn btn-sm btn-danger" onclick="reset_filter()"><i class="fa fa-refresh"></i> Reset</button>
                        <button type="button" class="btn btn-sm btn-success" onclick="export_excel()"><i class="fa fa-file-excel-o"></i> Excel</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table id="mytabledata" class="table table-bordered table-striped table-hover" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="text-center" width="30">#</th>
                        <th class="text-center">No Dokumen</th>
                        <th class="text-center">No Transaksi Payment</th>
                        <th class="text-center">Request By</th>
                        <th class="text-center">Tanggal Dokumen</th>
                        <th class="text-center">Keperluan</th>
                        <th class="text-center">Tipe</th>
                        <th class="text-center">Nilai Pengajuan</th>
                        <th class="text-center">Diajukan Oleh</th>
                        <th class="text-center">Tanggal Pengajuan</th>
                        <th class="text-center">Dibayar Oleh</th>
                        <th class="text-center">Tanggal Pembayaran</th>
                        <th class="text-center" width="60">Status</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    <!-- /.box-body -->
</div>

<script src="https://cdn.datatables.net/2.1.7/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script type="text/javascript">
    var table_payment;

    $(document).ready(function() {
        $('.select2').select2({
            width: '100%'
        });

        $('#filter_tgl').flatpickr({
            dateFormat: 'Y-m-d',
            allowInput: true,
            mode: 'range',
            placeholder: 'Pilih rentang tanggal'
        });

        load_data();
    });

    function load_data() {
        var filter_tgl = $('#filter_tgl').val();
        var tgl_from = '';
        var tgl_to = '';

        if (filter_tgl) {
            var exp = filter_tgl.split(' to ');
            tgl_from = exp[0] ? exp[0].trim() : '';
            tgl_to = exp[1] ? exp[1].trim() : exp[0].trim();
        }

        var tipe = $('#filter_tipe').val();

        table_payment = $('#mytabledata').DataTable({
            ajax: {
                url: siteurl + active_controller + 'get_data_payment_list',
                type: "POST",
                dataType: "JSON",
                data: function(d) {
                    d.tgl_from = tgl_from;
                    d.tgl_to   = tgl_to;
                    d.tipe     = tipe;
                }
            },
            columns: [
                { data: 'no', className: 'text-center' },
                { data: 'no_doc', className: 'text-center' },
                { data: 'no_payment', className: 'text-center' },
                { data: 'nama', className: 'text-left' },
                { data: 'tgl_doc', className: 'text-center' },
                { data: 'keperluan', className: 'text-left' },
                { data: 'tipe', className: 'text-center' },
                { data: 'nilai_pengajuan', className: 'text-right' },
                { data: 'diajukan_oleh', className: 'text-center' },
                { data: 'tgl_pengajuan', className: 'text-center' },
                { data: 'dibayar_oleh', className: 'text-center' },
                { data: 'tgl_pembayaran', className: 'text-center' },
                { data: 'status', className: 'text-center' }
            ],
            columnDefs: [
                {
                    targets: [0, 12],
                    orderable: false,
                    searchable: false
                }
            ],
            responsive: true,
            processing: true,
            serverSide: true,
            stateSave: true,
            paging: true,
            destroy: true,
            searchDelay: 500,
            lengthMenu: [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ],
            pageLength: 10,
            order: [[4, 'desc']]
        });
    }

    function search_data() {
        load_data();
    }

    function reset_filter() {
        $('#filter_tgl').val('');
        var fp = document.querySelector('#filter_tgl')._flatpickr;
        if (fp) fp.clear();

        $('#filter_tipe').val('').trigger('change');

        load_data();
    }

    function export_excel() {
        var filter_tgl = $('#filter_tgl').val();
        var tgl_from = '';
        var tgl_to = '';

        if (filter_tgl) {
            var exp = filter_tgl.split(' to ');
            tgl_from = exp[0] ? exp[0].trim() : '';
            tgl_to = exp[1] ? exp[1].trim() : exp[0].trim();
        }

        var tipe = $('#filter_tipe').val();

        window.open(siteurl + active_controller + 'excel_payment_list?tgl_from=' + encodeURIComponent(tgl_from) + '&tgl_to=' + encodeURIComponent(tgl_to) + '&tipe=' + encodeURIComponent(tipe), '_blank');
    }
</script>