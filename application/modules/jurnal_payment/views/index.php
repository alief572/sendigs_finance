<?php
$ENABLE_ADD     = has_permission('Jurnal.Add');
$ENABLE_MANAGE  = has_permission('Jurnal.Manage');
$ENABLE_VIEW    = has_permission('Jurnal.View');
$ENABLE_DELETE  = has_permission('Jurnal.Delete');
?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.7/css/dataTables.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    /* Card & Box Styling */
    .box-filter {
        border-radius: 8px;
        border-top: 3px solid #3c8dbc;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        background: #fff;
        margin-bottom: 20px;
    }

    .box-table {
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        background: #fff;
    }

    .filter-title {
        font-size: 15px;
        font-weight: 600;
        color: #333;
        margin-bottom: 15px;
        padding-bottom: 8px;
        border-bottom: 1px solid #f0f0f0;
    }

    .form-group label {
        font-size: 13px;
        font-weight: 600;
        color: #555;
        margin-bottom: 6px;
    }

    .form-control {
        border-radius: 6px !important;
        border: 1px solid #d2d6de;
        height: 36px;
        font-size: 13px;
    }

    .form-control:focus {
        border-color: #3c8dbc;
        box-shadow: 0 0 5px rgba(60, 141, 188, 0.3);
    }

    /* Select2 Alignment */
    .select2-container--default .select2-selection--single {
        height: 36px !important;
        border-radius: 6px !important;
        border: 1px solid #d2d6de !important;
        padding: 4px 8px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 26px !important;
        font-size: 13px !important;
        color: #444 !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 34px !important;
        right: 6px !important;
    }

    .select2-dropdown {
        border-radius: 6px !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
        border: 1px solid #d2d6de !important;
        z-index: 9999;
    }

    /* Buttons */
    .btn {
        border-radius: 6px !important;
        font-size: 13px;
        font-weight: 600;
        padding: 7px 14px;
        transition: all 0.2s ease-in-out;
    }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(0,0,0,0.15);
    }

    .filter-btn-group {
        display: flex;
        gap: 8px;
        align-items: flex-end;
        padding-top: 24px;
    }

    .save_btn_modal:disabled {
        cursor: not-allowed;
        opacity: 0.65;
    }

    /* Table Styling */
    #table_penawaran {
        border-collapse: separate !important;
        border-spacing: 0;
        width: 100% !important;
        margin-top: 10px !important;
    }

    #table_penawaran thead th {
        background-color: #3c8dbc !important;
        color: #ffffff !important;
        vertical-align: middle !important;
        text-align: center !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        padding: 12px 10px !important;
        border: none !important;
    }

    #table_penawaran thead th:first-child {
        border-top-left-radius: 6px;
    }

    #table_penawaran thead th:last-child {
        border-top-right-radius: 6px;
    }

    #table_penawaran tbody td {
        vertical-align: middle !important;
        padding: 10px 12px !important;
        font-size: 13px;
        border-color: #f0f2f5 !important;
    }

    #table_penawaran tbody tr:hover {
        background-color: #f4f8fb !important;
    }

    /* DataTables Elements */
    .dataTables_wrapper .dataTables_length select {
        border-radius: 6px;
        border: 1px solid #d2d6de;
        padding: 4px 8px;
        height: 32px;
    }

    .dataTables_wrapper .dataTables_filter input {
        border-radius: 6px;
        border: 1px solid #d2d6de;
        padding: 4px 10px;
        height: 32px;
        outline: none;
    }

    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #3c8dbc;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #3c8dbc !important;
        color: white !important;
        border: 1px solid #3c8dbc !important;
        border-radius: 6px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 6px !important;
    }
</style>

<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>

<!-- Filter Box -->
<div class="box box-filter">
    <div class="box-body" style="padding: 20px;">
        <div class="filter-title">
            <i class="fa fa-sliders text-primary" style="margin-right: 6px;"></i> Filter Data Jurnal
        </div>
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="tgl_jurnal"><i class="fa fa-calendar text-muted" style="margin-right: 4px;"></i> Rentang Tanggal Jurnal</label>
                    <input type="text" class="form-control" id="tgl_jurnal" name="tgl_jurnal" placeholder="Pilih rentang tanggal">
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="no_transaksi"><i class="fa fa-file-text-o text-muted" style="margin-right: 4px;"></i> No. Transaksi</label>
                    <select name="no_transaksi" id="no_transaksi" class="form-control select2">
                        <option value="">- Semua No. Transaksi -</option>
                        <?php
                        if (!empty($list_no_transaksi)) {
                            foreach ($list_no_transaksi as $row) {
                        ?>
                                <option value="<?= htmlspecialchars($row['no_transaksi'], ENT_QUOTES) ?>"><?= htmlspecialchars($row['no_transaksi']) ?></option>
                        <?php
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="company"><i class="fa fa-building-o text-muted" style="margin-right: 4px;"></i> Company</label>
                    <select name="company" id="company" class="form-control select2">
                        <option value="">- Semua Company -</option>
                        <?php
                        if (!empty($list_company)) {
                            foreach ($list_company as $row) {
                        ?>
                                <option value="<?= htmlspecialchars($row['id_company'], ENT_QUOTES) ?>"><?= htmlspecialchars($row['nm_company']) ?></option>
                        <?php
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="filter-btn-group">
                    <button type="button" class="btn btn-primary" onclick="search_jurnal()"><i class="fa fa-search"></i> Search</button>
                    <button type="button" class="btn btn-danger" onclick="reset_search_jurnal()"><i class="fa fa-refresh"></i> Reset</button>
                    <button type="button" class="btn btn-success" onclick="export_jurnal()"><i class="fa fa-download"></i> Export</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Table Box -->
<div class="box box-table">
    <div class="box-body" style="padding: 20px;">
        <div class="table-responsive">
            <table id="table_penawaran" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th width="4%" class="text-center">No.</th>
                        <th width="16%" class="text-center">No. Transaksi</th>
                        <th width="18%" class="text-center">No. Pengajuan</th>
                        <th width="14%" class="text-center">Kategori Transaksi</th>
                        <th width="16%" class="text-center">Tanggal Jurnal</th>
                        <th width="16%" class="text-center">Company</th>
                        <th width="10%" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Posting Jurnal -->
<div class="modal fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="width: 95%; max-width: 1200px;">
        <div class="modal-content" style="border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); border: none;">
            <div class="modal-header" style="background: linear-gradient(135deg, #0073b7 0%, #00a65a 100%); color: white; border-top-left-radius: 8px; border-top-right-radius: 8px; padding: 15px 20px;">
                <button type="button" class="close" data-dismiss="modal" style="color: white; opacity: 0.9; font-size: 24px;"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel" style="font-weight: 600; letter-spacing: 0.5px;"><span class="fa fa-book" style="margin-right: 8px;"></span> Posting Jurnal</h4>
            </div>
            <form action="" method="post" id="frm-data">
                <div class="modal-body" id="ModalView" style="padding: 20px; background-color: #f9fafc;"></div>
                <div class="modal-footer" style="background-color: #f1f4f9; border-top: 1px solid #e1e6ef; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px; padding: 15px 20px;">
                    <button type="submit" class="btn btn-primary save_btn_modal"><i class="fa fa-save"></i> Save</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">
                        <i class="fa fa-times"></i> Close
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="form-data"></div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/2.1.7/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Page Script -->
<script type="text/javascript">
    $(document).ready(function() {
        DataTables();
        select2();

        $('#tgl_jurnal').flatpickr({
            dateFormat: 'Y-m-d',
            allowInput: true,
            mode: 'range',
            placeholder: 'Pilih rentang tanggal'
        });

        $('#tgl_jurnal').attr('placeholder', 'Pilih rentang tanggal');
    });

    $(document).on('submit', '#frm-data', function(e) {
        e.preventDefault();

        var ttl_debit = parseFloat($('input[name="ttl_debit"]').val().replace(/,/g, ''));
        var ttl_kredit = parseFloat($('input[name="ttl_kredit"]').val().replace(/,/g, ''));

        if (isNaN(ttl_debit) || isNaN(ttl_kredit) || ttl_debit !== ttl_kredit) {
            Swal.fire({
                icon: 'warning',
                title: 'Warning !',
                text: 'Maaf, Data jurnal tidak balance untuk di posting !',
                showConfirmButton: true,
                allowOutsideClick: false
            });
            return false;
        }

        Swal.fire({
            icon: 'warning',
            title: 'Are you sure ?',
            text: 'This data will be posted to Tras!',
            showCancelButton: true,
            confirmButtonText: 'Yes, post it!',
            allowOutsideClick: false
        }).then(function(result) {
            if (result.isConfirmed) {
                var data = $('#frm-data').serialize();

                var $saveBtn = $('.save_btn_modal');
                var originalBtnText = $saveBtn.html();
                $saveBtn.html('<i class="fa fa-spinner fa-spin"></i> Processing...').prop('disabled', true);

                $.ajax({
                    type: 'post',
                    url: siteurl + active_controller + 'save_posting_jurnal',
                    data: data,
                    cache: false,
                    dataType: 'json',
                    success: function(result) {
                        $saveBtn.html(originalBtnText).prop('disabled', false);

                        if (result.save == '1') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success !',
                                text: result.msg,
                                timer: 3000,
                                allowOutsideClick: false
                            }).then(function() {
                                $('#dialog-popup').modal('hide');
                                DataTables();
                            });
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Failed !',
                                text: result.msg || 'Please try again later !',
                                timer: 3000,
                                allowOutsideClick: false
                            });
                        }
                    },
                    error: function(result) {
                        $saveBtn.html(originalBtnText).prop('disabled', false);

                        Swal.fire({
                            icon: 'error',
                            title: 'Error !',
                            text: 'Please try again later !',
                            timer: 3000,
                            allowOutsideClick: false
                        });
                    }
                });
            }
        });
    });

    function add_jurnal(id) {
        $.ajax({
            type: 'post',
            url: siteurl + active_controller + 'add_jurnal',
            data: {
                'id': id
            },
            cache: false,
            success: function(result) {
                $('#ModalView').html(result);
                $('#dialog-popup').modal('show');
            },
            error: function(result) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error !',
                    text: 'Please try again later !',
                    timer: 3000,
                    allowOutsideClick: false
                });
            }
        });
    }

    function DataTables(tgl_jurnal = null, no_transaksi = null, company = null) {
        $('#table_penawaran').DataTable({
            ajax: {
                url: siteurl + active_controller + 'get_data_jurnal',
                type: "POST",
                dataType: "JSON",
                data: function(d) {
                    d.tgl_jurnal   = tgl_jurnal;
                    d.no_transaksi = no_transaksi;
                    d.company      = company;
                }
            },
            columns: [
                { data: 'no' },
                { data: 'no_transaksi' },
                { data: 'no_pengajuan' },
                { data: 'kategori_payment' },
                { data: 'tanggal_jurnal' },
                { data: 'company' },
                { data: 'action' }
            ],
            columnDefs: [
                {
                    targets: [0, 1, 2, 3, 4, 5, 6],
                    className: 'text-center'
                },
                {
                    targets: [0, 6],
                    orderable: false,
                    searchable: false
                }
            ],
            order: [[4, 'desc']],
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
            pageLength: 10
        });
    }

    function search_jurnal() {
        var tgl_jurnal   = $('#tgl_jurnal').val();
        var no_transaksi = $('#no_transaksi').val();
        var company      = $('#company').val();

        DataTables(tgl_jurnal, no_transaksi, company);
    }

    function reset_search_jurnal() {
        $('#tgl_jurnal').val('');
        var fp = document.querySelector('#tgl_jurnal')._flatpickr;
        if (fp) fp.clear();

        $('#no_transaksi').val('').trigger('change');
        $('#company').val('').trigger('change');

        DataTables();
    }

    function export_jurnal() {
        var tgl_jurnal   = $('#tgl_jurnal').val();
        var no_transaksi = $('#no_transaksi').val();
        var company      = $('#company').val();

        window.open(siteurl + active_controller + 'export_jurnal?tgl_jurnal=' + encodeURIComponent(tgl_jurnal) + '&no_transaksi=' + encodeURIComponent(no_transaksi) + '&company=' + encodeURIComponent(company), '_blank');
    }

    function select2() {
        $('.select2').select2({
            width: '100%'
        });
    }
</script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>