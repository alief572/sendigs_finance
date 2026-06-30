<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<style>
    .filter-actions {
        margin-top: 25px;
    }

    #table-vuca-sustain th.text-right,
    #table-vuca-sustain td.text-right {
        text-align: right;
    }
</style>

<div class="box box-primary">
    <div class="box-header">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="filter-company">Company</label>
                    <select id="filter-company" class="form-control input-sm">
                        <option value="">Semua</option>
                        <option value="VUCA">VUCA</option>
                        <option value="SUSTAIN">SUSTAIN</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="filter-status">Status</label>
                    <select id="filter-status" class="form-control input-sm">
                        <option value="">Semua</option>
                        <option value="draft">Draft</option>
                        <option value="waiting payment">Waiting Payment</option>
                        <option value="done payment">Done Payment</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group filter-actions">
                    <button type="button" class="btn btn-sm btn-primary" id="btn-filter">
                        <i class="fa fa-search"></i> Filter
                    </button>
                    <button type="button" class="btn btn-sm btn-default" id="btn-reset-filter">
                        <i class="fa fa-refresh"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <div class="table-responsive">
            <table id="table-vuca-sustain" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="30">No</th>
                        <th>No Pelaporan</th>
                        <th>No Payment Hutang</th>
                        <th>Periode</th>
                        <th>Company</th>
                        <th class="text-right">Jumlah Pencatatan</th>
                        <th class="text-right">Grand Total Periode</th>
                        <th>Status</th>
                        <th width="120">Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
    <!-- /.box-body -->
</div>

<script>
    var PERMISSIONS = {
        has_manage: <?= json_encode($has_manage) ?>
    };
    var BASE_URL = '<?= site_url('petty_cash_vuca_sustain/') ?>';
</script>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {

        // =========================================================================
        // DataTables Initialization
        // =========================================================================

        var table = $('#table-vuca-sustain').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            responsive: true,
            oLanguage: {
                sSearch: '<b>Search : </b>',
                sLengthMenu: '_MENU_ &nbsp;&nbsp;<b>Records Per Page</b>&nbsp;&nbsp;',
                sInfo: 'Showing _START_ to _END_ of _TOTAL_ entries',
                sInfoFiltered: '(filtered from _MAX_ total entries)',
                sZeroRecords: 'No matching records found',
                sEmptyTable: 'No data available in table',
                sLoadingRecords: 'Please wait - loading...',
                oPaginate: {
                    sPrevious: 'Prev',
                    sNext: 'Next'
                }
            },
            aaSorting: [
                [1, 'desc']
            ],
            columnDefs: [{
                    targets: [0, 8],
                    orderable: false,
                    searchable: false
                },
                {
                    targets: [5, 6],
                    className: 'text-right'
                }
            ],
            iDisplayLength: 10,
            aLengthMenu: [
                [10, 20, 50, 100],
                [10, 20, 50, 100]
            ],
            ajax: {
                url: BASE_URL + 'get_data',
                type: 'POST',
                cache: false,
                data: function(d) {
                    d.company = $('#filter-company').val();
                    d.status = $('#filter-status').val();
                },
                error: function() {
                    $('#table-vuca-sustain tbody').html(
                        '<tr><th colspan="9" class="text-center">No data found in the server</th></tr>'
                    );
                }
            },
            columns: [{
                    // Column 0: Row number
                    data: 'no'
                },
                {
                    // Column 1: No Pelaporan
                    data: 'no_pelaporan'
                },
                {
                    // Column 2: No Payment Hutang
                    data: 'no_payment_hutang',
                    render: function(data) {
                        return data ? data : '-';
                    }
                },
                {
                    // Column 3: Periode
                    data: 'periode'
                },
                {
                    // Column 4: Company
                    data: 'company'
                },
                {
                    // Column 5: Jumlah Pencatatan
                    data: 'jumlah_pencatatan'
                },
                {
                    // Column 6: Grand Total Periode
                    data: 'grand_total'
                },
                {
                    // Column 7: Status (colored badge)
                    data: 'status',
                    render: function(data) {
                        var labelClass = 'label-default';
                        var labelText = data;

                        switch (data) {
                            case 'draft':
                                labelClass = 'label-warning';
                                labelText = 'Draft';
                                break;
                            case 'waiting payment':
                                labelClass = 'label-info';
                                labelText = 'Waiting Payment';
                                break;
                            case 'done payment':
                                labelClass = 'label-success';
                                labelText = 'Done Payment';
                                break;
                        }

                        return '<span class="label ' + labelClass + '">' + labelText + '</span>';
                    }
                },
                {
                    // Column 8: Action buttons
                    data: null,
                    render: function(data, type, row) {
                        var html = '';

                        // Payment Hutang button — only if draft + has manage permission
                        if (PERMISSIONS.has_manage && row.status === 'draft') {
                            html += '<button type="button" class="btn btn-xs btn-success btn-payment-hutang" ' +
                                'data-id="' + row.id + '" ' +
                                'data-no="' + row.no_payment_hutang + '" ' +
                                'data-company="' + row.company + '" ' +
                                'data-total="' + row.grand_total_raw + '" ' +
                                'title="Payment Hutang">' +
                                '<i class="fa fa-send"></i></button> ';
                        }

                        // View button — always shown
                        html += '<a href="' + BASE_URL + 'view/' + row.id + '" ' +
                            'class="btn btn-xs btn-info" title="View">' +
                            '<i class="fa fa-eye"></i></a> ';

                        // Print button — always shown, opens in new tab
                        html += '<a href="' + BASE_URL + 'print_pdf/' + row.id + '" ' +
                            'target="_blank" class="btn btn-xs btn-default" title="Print">' +
                            '<i class="fa fa-print"></i></a> ';

                        return html;
                    }
                }
            ]
        });

        // =========================================================================
        // Filter Handling
        // =========================================================================

        // Filter button click — reload table with filter values
        $('#btn-filter').on('click', function() {
            table.ajax.reload(null, false);
        });

        // Reset filter button — clear dropdowns and reload
        $('#btn-reset-filter').on('click', function() {
            $('#filter-company').val('');
            $('#filter-status').val('');
            table.ajax.reload(null, false);
        });

        // Filter on dropdown change — immediate reload without page refresh
        $('#filter-company, #filter-status').on('change', function() {
            table.ajax.reload(null, false);
        });

        // =========================================================================
        // Payment Hutang Confirmation
        // =========================================================================

        $(document).on('click', '.btn-payment-hutang', function() {
            var id = $(this).data('id');
            var no = $(this).data('no');
            var company = $(this).data('company');
            var total = $(this).data('total');

            Swal.fire({
                title: 'Konfirmasi Payment Hutang',
                html: '<table class="table table-bordered">' +
                    '<tr><td><b>No Payment Hutang</b></td><td>' + no + '</td></tr>' +
                    '<tr><td><b>Company</b></td><td>' + company + '</td></tr>' +
                    '<tr><td><b>Grand Total</b></td><td>Rp ' + Number(total).toLocaleString('id-ID') + '</td></tr>' +
                    '</table>' +
                    '<p class="text-warning">Apakah Anda yakin ingin memproses Payment Hutang ini?</p>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#00a65a',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Proses!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: BASE_URL + 'payment_hutang/' + id,
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            if (response.status) {
                                Swal.fire('Berhasil!', response.message, 'success').then(function() {
                                    table.ajax.reload(null, false);
                                });
                            } else {
                                Swal.fire('Gagal!', response.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error!', 'Terjadi kesalahan saat memproses request.', 'error');
                        }
                    });
                }
            });
        });

    });
</script>