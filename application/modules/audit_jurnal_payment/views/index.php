<link rel="stylesheet" href="https://cdn.datatables.net/2.1.7/css/dataTables.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    /* Metric Widgets */
    .metric-card {
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        padding: 16px;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        border-left: 4px solid #3c8dbc;
        transition: transform 0.2s;
    }
    .metric-card:hover {
        transform: translateY(-2px);
    }
    .metric-card.danger { border-left-color: #dd4b39; }
    .metric-card.warning { border-left-color: #f39c12; }
    .metric-card.info { border-left-color: #00c0ef; }
    .metric-card.success { border-left-color: #00a65a; }

    .metric-icon {
        font-size: 28px;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f4f6f9;
        margin-right: 14px;
        color: #3c8dbc;
    }
    .metric-card.danger .metric-icon { color: #dd4b39; background: #fdeeed; }
    .metric-card.warning .metric-icon { color: #f39c12; background: #fef5e7; }
    .metric-card.info .metric-icon { color: #00c0ef; background: #e6f9fd; }
    .metric-card.success .metric-icon { color: #00a65a; background: #eaf6ee; }

    .metric-data h3 {
        margin: 0 0 2px 0;
        font-size: 22px;
        font-weight: 700;
        color: #333;
    }
    .metric-data span {
        font-size: 12px;
        font-weight: 600;
        color: #777;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

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

    /* Table Styling */
    #table_audit {
        border-collapse: separate !important;
        border-spacing: 0;
        width: 100% !important;
        margin-top: 10px !important;
    }

    #table_audit thead th {
        background-color: #3c8dbc !important;
        color: #ffffff !important;
        vertical-align: middle !important;
        text-align: center !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        padding: 12px 8px !important;
        border: none !important;
    }

    #table_audit thead th:first-child {
        border-top-left-radius: 6px;
    }

    #table_audit thead th:last-child {
        border-top-right-radius: 6px;
    }

    #table_audit tbody td {
        vertical-align: middle !important;
        padding: 10px 10px !important;
        font-size: 13px;
        border-color: #f0f2f5 !important;
    }

    #table_audit tbody tr:hover {
        background-color: #f4f8fb !important;
    }

    .bulk-toolbar {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 8px;
        padding: 12px 18px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }
</style>

<!-- Summary Metric Widgets -->
<div class="row">
    <div class="col-md-3 col-sm-6">
        <div class="metric-card info">
            <div class="metric-icon"><i class="fa fa-list-alt"></i></div>
            <div class="metric-data">
                <h3 id="cnt_audited">0</h3>
                <span>Total Payment Diaudit</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="metric-card danger">
            <div class="metric-icon"><i class="fa fa-exclamation-triangle"></i></div>
            <div class="metric-data">
                <h3 id="cnt_issues">0</h3>
                <span>Perlu Penyesuaian</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="metric-card danger">
            <div class="metric-icon"><i class="fa fa-balance-scale"></i></div>
            <div class="metric-data">
                <h3 id="cnt_unbalanced">0</h3>
                <span>Tidak Balance</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="metric-card warning">
            <div class="metric-icon"><i class="fa fa-tag"></i></div>
            <div class="metric-data">
                <h3 id="cnt_suffix">0</h3>
                <span>Suffix / Data Kurang</span>
            </div>
        </div>
    </div>
</div>

<!-- Filter Panel -->
<div class="box box-filter">
    <div class="box-body" style="padding: 20px;">
        <div class="filter-title">
            <i class="fa fa-sliders text-primary" style="margin-right: 6px;"></i> Filter Parameter Audit
        </div>
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="tgl_jurnal"><i class="fa fa-calendar text-muted"></i> Rentang Tanggal Bayar</label>
                    <input type="text" class="form-control" id="tgl_jurnal" name="tgl_jurnal" placeholder="Pilih rentang tanggal">
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="tipe"><i class="fa fa-folder-open-o text-muted"></i> Kategori / Tipe Payment</label>
                    <select name="tipe" id="tipe" class="form-control select2">
                        <option value="">- Semua Kategori -</option>
                        <option value="kasbon">Kasbon</option>
                        <option value="transport">Transport / Transportasi</option>
                        <option value="expense">Expense</option>
                        <option value="direct_payment">Direct Payment</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="status_issue"><i class="fa fa-filter text-muted"></i> Status Hasil Audit</label>
                    <select name="status_issue" id="status_issue" class="form-control select2">
                        <option value="">- Semua Status -</option>
                        <option value="issue_only" selected>Hanya Yang Bermasalah (Perlu Fix)</option>
                        <option value="unbalanced">Hanya Yang Tidak Balance</option>
                        <option value="no_journal">Hanya Yang Belum Ada Jurnal</option>
                        <option value="missing_suffix">Hanya Yang Suffix Hilang</option>
                        <option value="ok_only">Hanya Yang Sesuai (OK)</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div style="padding-top: 24px; display:flex; gap:8px;">
                    <button type="button" class="btn btn-primary" onclick="load_audit_data()"><i class="fa fa-search"></i> Jalankan Audit</button>
                    <button type="button" class="btn btn-danger" onclick="reset_filter()"><i class="fa fa-refresh"></i> Reset</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Table Panel -->
<div class="box box-table">
    <div class="box-body" style="padding: 20px;">
        
        <!-- Bulk Action Toolbar -->
        <div class="bulk-toolbar">
            <div>
                <strong style="color:#2c3e50;"><i class="fa fa-check-square-o text-primary"></i> Aksi Massal:</strong>
                <span id="selected_counter" class="label label-primary" style="margin-left:6px; font-size:12px;">0 data terpilih</span>
            </div>
            <div style="display:flex; gap:8px;">
                <button type="button" class="btn btn-sm btn-success" id="btn_fix_selected" onclick="fix_selected()" disabled>
                    <i class="fa fa-wrench"></i> Perbaiki Data Yang Dipilih
                </button>
                <button type="button" class="btn btn-sm btn-danger" id="btn_fix_all" onclick="fix_all_issues()">
                    <i class="fa fa-magic"></i> Perbaiki Semua Yang Bermasalah
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table id="table_audit" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th width="3%" class="text-center"><input type="checkbox" id="check_all"></th>
                        <th width="4%" class="text-center">No.</th>
                        <th width="12%" class="text-center">No. Transaksi</th>
                        <th width="14%" class="text-center">No. Dokumen</th>
                        <th width="10%" class="text-center">Tipe</th>
                        <th width="11%" class="text-center">Tgl Bayar</th>
                        <th width="11%" class="text-center">Jumlah Bayar</th>
                        <th width="15%" class="text-center">Status Audit</th>
                        <th width="12%" class="text-center">Perbandingan Nominal</th>
                        <th width="8%" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Comparison -->
<div class="modal fade" id="modal-compare" tabindex="-1" role="dialog" aria-labelledby="compareLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="width: 95%; max-width: 1400px;">
        <div class="modal-content" style="border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); border: none;">
            <div class="modal-header" style="background: linear-gradient(135deg, #0073b7 0%, #3c8dbc 100%); color: white; border-top-left-radius: 8px; border-top-right-radius: 8px; padding: 15px 20px;">
                <button type="button" class="close" data-dismiss="modal" style="color: white; opacity: 0.9; font-size: 24px;"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="compareLabel" style="font-weight: 600; letter-spacing: 0.5px;"><span class="fa fa-columns" style="margin-right: 8px;"></span> Komparasi Rincian Ayat Jurnal</h4>
            </div>
            <div class="modal-body" id="modalCompareBody" style="padding: 20px; background-color: #f9fafc;"></div>
            <div class="modal-footer" style="background-color: #f1f4f9; border-top: 1px solid #e1e6ef; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px; padding: 15px 20px;">
                <button type="button" class="btn btn-danger pull-right" data-dismiss="modal" style="min-width: 100px;">
                    <i class="fa fa-times"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/2.1.7/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script type="text/javascript">
    var dataTableObj = null;

    $(document).ready(function() {
        select2();

        $('#tgl_jurnal').flatpickr({
            dateFormat: 'Y-m-d',
            allowInput: true,
            mode: 'range',
            placeholder: 'Pilih rentang tanggal'
        });

        load_audit_data();

        // Check all handler
        $(document).on('change', '#check_all', function() {
            var isChecked = $(this).is(':checked');
            $('.check_audit:not(:disabled)').prop('checked', isChecked);
            update_selected_counter();
        });

        $(document).on('change', '.check_audit', function() {
            update_selected_counter();
        });
    });

    function select2() {
        $('.select2').select2({
            width: '100%'
        });
    }

    function update_selected_counter() {
        var count = $('.check_audit:checked').length;
        $('#selected_counter').text(count + ' data terpilih');
        if (count > 0) {
            $('#btn_fix_selected').prop('disabled', false);
        } else {
            $('#btn_fix_selected').prop('disabled', true);
        }
    }

    function load_audit_data() {
        var tgl_jurnal   = $('#tgl_jurnal').val();
        var tipe         = $('#tipe').val();
        var status_issue = $('#status_issue').val();

        $('#check_all').prop('checked', false);
        update_selected_counter();

        if (dataTableObj) {
            dataTableObj.destroy();
        }

        dataTableObj = $('#table_audit').DataTable({
            ajax: {
                url: siteurl + active_controller + 'get_data_audit',
                type: "POST",
                dataType: "JSON",
                data: function(d) {
                    d.tgl_jurnal   = tgl_jurnal;
                    d.tipe         = tipe;
                    d.status_issue = status_issue;
                },
                dataSrc: function(json) {
                    if (json.summary) {
                        $('#cnt_audited').text(json.summary.total_audited);
                        $('#cnt_issues').text(json.summary.total_issues);
                        $('#cnt_unbalanced').text(json.summary.total_unbalanced);
                        $('#cnt_suffix').text(json.summary.total_suffix_issue);
                    }
                    return json.data;
                }
            },
            columns: [
                { data: 'checkbox' },
                { data: 'no' },
                { data: 'no_transaksi' },
                { data: 'no_doc' },
                { data: 'tipe' },
                { data: 'tgl_bayar' },
                { data: 'jumlah' },
                { data: 'status_issue' },
                { data: 'balance_info' },
                { data: 'action' }
            ],
            columnDefs: [
                {
                    targets: [0, 1, 4, 5, 6, 7, 8, 9],
                    className: 'text-center'
                },
                {
                    targets: [0, 9],
                    orderable: false,
                    searchable: false
                }
            ],
            responsive: true,
            processing: true,
            serverSide: true,
            paging: true,
            destroy: true,
            searchDelay: 500,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]]
        });
    }

    function reset_filter() {
        $('#tgl_jurnal').val('');
        var fp = document.querySelector('#tgl_jurnal')._flatpickr;
        if (fp) fp.clear();

        $('#tipe').val('').trigger('change');
        $('#status_issue').val('issue_only').trigger('change');

        load_audit_data();
    }

    // Modal Compare
    $(document).on('click', '.btn_compare', function() {
        var id = $(this).data('id');

        $('#modalCompareBody').html('<div class="text-center" style="padding:40px;"><i class="fa fa-spinner fa-spin fa-3x text-primary"></i><br><br>Memuat perbandingan jurnal...</div>');
        $('#modal-compare').modal('show');

        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'modal_compare',
            data: { id: id },
            cache: false,
            success: function(html) {
                $('#modalCompareBody').html(html);
            },
            error: function() {
                $('#modalCompareBody').html('<div class="alert alert-danger">Gagal memuat data perbandingan. Silakan coba lagi!</div>');
            }
        });
    });

    // Fix Single Jurnal
    $(document).on('click', '.btn_fix_single', function() {
        var id = $(this).data('id');
        var doc = $(this).data('doc');

        Swal.fire({
            title: 'Perbaiki Jurnal Transaksi?',
            html: 'Sistem akan menghapus ayat jurnal lama yang salah/unbalanced untuk dokumen <b>' + doc + '</b> dan menyusun ulang ayat jurnal baru yang benar dan seimbang.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '<i class="fa fa-wrench"></i> Ya, Perbaiki Sekarang!',
            cancelButtonText: 'Batal',
            allowOutsideClick: false
        }).then(function(result) {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses Perbaikan...',
                    text: 'Mohon tunggu beberapa saat',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    type: 'POST',
                    url: siteurl + active_controller + 'fix_single_jurnal',
                    data: { id: id },
                    dataType: 'JSON',
                    success: function(res) {
                        if (res.status == 1) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: res.msg,
                                timer: 2500,
                                allowOutsideClick: false
                            }).then(() => {
                                $('#modal-compare').modal('hide');
                                load_audit_data();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: res.msg,
                                allowOutsideClick: false
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Terjadi kesalahan pada server saat memproses perbaikan.',
                            allowOutsideClick: false
                        });
                    }
                });
            }
        });
    });

    // Fix Selected Bulk
    function fix_selected() {
        var selected = [];
        $('.check_audit:checked').each(function() {
            selected.push($(this).val());
        });

        if (selected.length === 0) {
            Swal.fire('Perhatian', 'Pilih setidaknya satu data transaksi untuk diperbaiki.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Perbaiki ' + selected.length + ' Transaksi Terpilih?',
            text: 'Seluruh ayat jurnal lama yang bermasalah akan disinkronkan ulang dengan susunan jurnal yang benar.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '<i class="fa fa-wrench"></i> Ya, Perbaiki ' + selected.length + ' Data!',
            cancelButtonText: 'Batal',
            allowOutsideClick: false
        }).then(function(result) {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses Perbaikan Massal...',
                    text: 'Sedang memproses ' + selected.length + ' data transaksi',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    type: 'POST',
                    url: siteurl + active_controller + 'fix_bulk_jurnal',
                    data: { ids: selected },
                    dataType: 'JSON',
                    success: function(res) {
                        if (res.status == 1) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Selesai!',
                                text: res.msg,
                                allowOutsideClick: false
                            }).then(() => {
                                load_audit_data();
                            });
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Sebagian atau Semua Gagal!',
                                text: res.msg,
                                allowOutsideClick: false
                            });
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Gagal memproses perbaikan massal pada server.', 'error');
                    }
                });
            }
        });
    }

    // Fix All Issues
    function fix_all_issues() {
        var all_issue_ids = [];
        $('.check_audit:not(:disabled)').each(function() {
            all_issue_ids.push($(this).val());
        });

        if (all_issue_ids.length === 0) {
            Swal.fire('Info', 'Tidak ada data bermasalah pada tabel saat ini yang perlu diperbaiki.', 'info');
            return;
        }

        Swal.fire({
            title: 'Perbaiki SEMUA Data Bermasalah?',
            html: 'Sistem akan memperbaiki total <b>' + all_issue_ids.length + ' data</b> transaksi yang tampil pada tabel saat ini.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '<i class="fa fa-magic"></i> Ya, Perbaiki Semua!',
            cancelButtonText: 'Batal',
            allowOutsideClick: false
        }).then(function(result) {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses Semua Data...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    type: 'POST',
                    url: siteurl + active_controller + 'fix_bulk_jurnal',
                    data: { ids: all_issue_ids },
                    dataType: 'JSON',
                    success: function(res) {
                        Swal.fire({
                            icon: res.status == 1 ? 'success' : 'warning',
                            title: res.status == 1 ? 'Selesai!' : 'Perhatian!',
                            text: res.msg,
                            allowOutsideClick: false
                        }).then(() => {
                            load_audit_data();
                        });
                    },
                    error: function() {
                        Swal.fire('Error', 'Gagal memproses perbaikan massal.', 'error');
                    }
                });
            }
        });
    }
</script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>
