<link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" integrity="sha512-yVvxUQV0QESBt1SyZbNJMAwyKvFTLMyXSyBHDO4BG5t7k/Lw34tyqlSDlKIrIENIzCl+RVUNjmCPG+V/GMesRw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<style>
    .tax-badge {
        display: inline-block;
        padding: 2px 7px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    .tax-PKP { background: #e8f4fd; color: #1976d2; border: 1px solid #bbdefb; }
    .tax-WAPU { background: #fef8e7; color: #b78103; border: 1px solid #ffe082; }
    .tax-KB { background: #f3e5f5; color: #7b1fa2; border: 1px solid #e1bee7; }

    .nav-tabs-custom > .nav-tabs > li.active {
        border-top-color: #3c8dbc;
    }
    .nav-tabs-custom > .nav-tabs > li > a {
        font-weight: 600;
        font-size: 14px;
    }

    .filter-panel {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 14px 18px;
        margin-bottom: 18px;
    }
    .filter-panel label {
        font-size: 12px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 4px;
    }
    .form-control-sm {
        height: 34px;
        padding: 6px 10px;
        font-size: 13px;
        border-radius: 4px;
    }

    .table-report th {
        background-color: #f1f5f9;
        color: #334155;
        font-weight: 700;
        font-size: 12px;
        vertical-align: middle !important;
        white-space: nowrap;
    }
    .table-report td {
        vertical-align: middle !important;
        font-size: 13px;
    }
    .table-report tbody tr:hover {
        background-color: #f8fafc;
    }
    .nowrap {
        white-space: nowrap;
    }
    .font-bold {
        font-weight: 700;
    }
    #table_report_filter {
        display: none;
    }
</style>

<div class="nav-tabs-custom" style="box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 4px;">
    <ul class="nav nav-tabs" id="penerimaan_tabs" role="tablist">
        <li class="active">
            <a href="#tab_penerimaan" data-toggle="tab" role="tab">
                <i class="fa fa-hourglass-half text-yellow"></i> Penerimaan Piutang Invoice <span class="badge bg-yellow" id="badge_draft_invoice" style="margin-left: 4px;">0</span>
            </a>
        </li>
        <li>
            <a href="#tab_kwitansi" data-toggle="tab" role="tab">
                <i class="fa fa-ticket text-aqua"></i> Penerimaan Piutang Kwitansi Deposit <span class="badge bg-aqua" id="badge_draft_deposit" style="margin-left: 4px;">0</span>
            </a>
        </li>
        <li>
            <a href="#tab_report" data-toggle="tab" role="tab">
                <i class="fa fa-check-square-o text-green"></i> Report Penerimaan <span class="badge bg-green" id="badge_report" style="margin-left: 4px;">0</span>
            </a>
        </li>
    </ul>

    <div class="tab-content" style="padding: 18px;">
        <!-- TAB 1: PENERIMAAN (DRAFT) -->
        <div class="tab-pane active" id="tab_penerimaan">
            <div class="filter-panel">
                <div class="row">
                    <div class="col-md-4">
                        <label for="bank_filter">Filter Bank Penerima</label>
                        <select name="bank" id="bank_filter" class="form-control form-control-sm bank">
                            <option value="">- Semua Bank -</option>
                            <?php foreach ($list_bank as $item_bank) : ?>
                                <option value="<?= $item_bank['id'] ?>"><?= htmlspecialchars($item_bank['nama_bank'] . ' - ' . $item_bank['rekening'] . ' - ' . $item_bank['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2" style="margin-top: 22px;">
                        <button type="button" class="btn btn-sm btn-primary search" id="btn_search_draft">
                            <i class="fa fa-search"></i> Search
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="table_list" style="width: 100%;">
                    <thead>
                        <tr style="background-color: #f8fafc;">
                            <th class="text-center" style="width: 50px;">No.</th>
                            <th class="text-center" style="width: 120px;">Tanggal Transaksi</th>
                            <th class="text-center" style="width: 140px;">Reference No.</th>
                            <th style="min-width: 180px;">Bank</th>
                            <th style="min-width: 220px;">Keterangan</th>
                            <th class="text-right" style="width: 130px;">Nominal (Rp)</th>
                            <th class="text-center" style="width: 90px;">Status</th>
                            <th class="text-center" style="width: 80px;">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane" id="tab_kwitansi">
            <div class="filter-panel">
                <div class="row">
                    <div class="col-md-4">
                        <label for="bank_filter_kwitansi">Filter Bank Penerima</label>
                        <select name="bank" id="bank_filter_kwitansi" class="form-control form-control-sm bank">
                            <option value="">- Semua Bank -</option>
                            <?php foreach ($list_bank as $item_bank) : ?>
                                <option value="<?= $item_bank['id'] ?>"><?= htmlspecialchars($item_bank['nama_bank'] . ' - ' . $item_bank['rekening'] . ' - ' . $item_bank['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2" style="margin-top: 22px;">
                        <button type="button" class="btn btn-sm btn-primary search" id="btn_search_kwitansi">
                            <i class="fa fa-search"></i> Search
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="table_kwitansi" style="width: 100%;">
                    <thead>
                        <tr style="background-color: #f8fafc;">
                            <th class="text-center" style="width: 50px;">No.</th>
                            <th class="text-center" style="width: 120px;">Tanggal Transaksi</th>
                            <th class="text-center" style="width: 140px;">Reference No.</th>
                            <th style="min-width: 180px;">Bank</th>
                            <th style="min-width: 220px;">Keterangan</th>
                            <th class="text-right" style="width: 130px;">Nominal (Rp)</th>
                            <th class="text-center" style="width: 90px;">Status</th>
                            <th class="text-center" style="width: 80px;">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <!-- TAB 2: REPORT PENERIMAAN (USED) -->
        <div class="tab-pane" id="tab_report">
            <div class="row" style="margin-bottom:12px">
                <div class="col-md-4"><label for="rep_kind">Jenis Penerimaan</label><select id="rep_kind" class="form-control"><option value="">Semua</option><option value="invoice">Invoice</option><option value="deposit">Kwitansi Deposit</option></select></div>
                <div class="col-md-4"><label for="rep_customer">ID Customer</label><input id="rep_customer" class="form-control" placeholder="Semua customer"></div>
            </div>
            <div class="filter-panel">
                <div class="row">
                    <div class="col-md-2">
                        <label for="rep_year">Tahun</label>
                        <select id="rep_year" class="form-control form-control-sm">
                            <option value="">Semua</option>
                            <?php foreach ($list_years as $yr) : ?>
                                <option value="<?= $yr ?>" <?= ($yr == date('Y')) ? 'selected' : '' ?>><?= $yr ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="rep_month">Bulan</label>
                        <select id="rep_month" class="form-control form-control-sm">
                            <option value="">Semua</option>
                            <?php
                            $months = array(1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April', 5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus', 9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember');
                            foreach ($months as $mNum => $mName) :
                            ?>
                                <option value="<?= $mNum ?>"><?= $mName ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="rep_company">Company</label>
                        <select id="rep_company" class="form-control form-control-sm">
                            <option value="">Semua</option>
                            <?php foreach ($list_companies as $cmp) : ?>
                                <option value="<?= $cmp ?>"><?= $cmp ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="rep_search">Search Bebas</label>
                        <input type="text" id="rep_search" class="form-control form-control-sm" placeholder="Cari invoice, customer, bank, keterangan...">
                    </div>
                    <div class="col-md-3 text-right" style="margin-top: 22px;">
                        <button type="button" class="btn btn-sm btn-primary" id="btn_filter_report">
                            <i class="fa fa-filter"></i> Filter
                        </button>
                        <button type="button" class="btn btn-sm btn-default" id="btn_reset_report">
                            <i class="fa fa-refresh"></i> Reset
                        </button>
                        <a href="javascript:void(0)" class="btn btn-sm btn-success" id="btn_export_excel" title="Download Excel">
                            <i class="fa fa-file-excel-o"></i> Export to Excel
                        </a>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-report" id="table_report" style="width: 100%;">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 45px;">No.</th>
                            <th class="text-center" style="width: 115px;">Tanggal Penerimaan</th>
                            <th style="min-width: 140px;">Bank</th>
                            <th style="min-width: 180px;">Detail Penerimaan Bank</th>
                            <th style="min-width: 160px;">Customer</th>
                            <th class="text-center" style="width: 140px;">Nomor Invoice / Kwitansi</th>
                            <th class="text-center" style="width: 90px;">Company</th>
                            <th class="text-center" style="width: 130px;">TAX Code & PPh 23</th>
                            <th class="text-right" style="width: 140px;">Penerimaan Bank</th>
                            <th class="text-center" style="width: 130px;">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr class="bg-gray font-bold">
                            <td colspan="8" class="text-right" id="foot_summary_report">Total Penerimaan:</td>
                            <td class="text-right" id="foot_tot_nominal_report">Rp. 0,00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="text-muted" style="margin-top: 8px; font-size: 12px;">
                <i class="fa fa-info-circle"></i> Tanggal penerimaan mengikuti tanggal mutasi bank yang dialokasikan. Satu mutasi bank yang membayar beberapa invoice ditampilkan sebagai satu grup transfer.
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="width: 95% !important; max-width: 1400px;">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #3c8dbc; color: #fff;">
                <button type="button" class="close" data-dismiss="modal" style="color: #fff;"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"><i class="fa fa-file-text-o"></i> Detail Penerimaan Piutang & Jurnal</h4>
            </div>
            <div class="modal-body" id="MyModalBody" style="max-height: 80vh; overflow-y: auto; padding: 18px;">
            </div>
            <div class="modal-footer" style="background-color: #f8fafc;">
                <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    $(document).ready(function() {
        // Init DataTables for Tab 1 (Draft)
        var table = $('#table_list').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: siteurl + active_controller + 'get_alokasi_penerimaan',
                type: 'POST',
                data: function(d) {
                    d.bank = $('#bank_filter').val();
                    d.tab = 'draft';
                }
            },
            columns: [
                { data: 'no', className: 'text-center' },
                { data: 'tgl_transaksi_bank', className: 'text-center nowrap' },
                { data: 'reference_no', className: 'text-center' },
                { data: 'bank' },
                { data: 'keterangan' },
                { data: 'nominal', className: 'text-right font-bold' },
                { data: 'status', className: 'text-center' },
                { data: 'action', className: 'text-center nowrap' }
            ],
            order: [[1, 'desc']],
            pageLength: 25,
            drawCallback: function(settings) {
                var cnt = (settings.json && settings.json.recordsFiltered !== undefined) ? settings.json.recordsFiltered : 0;
                $('#badge_draft_invoice').text(cnt);
            }
        });

        $('#btn_search_draft').on('click', function() {
            table.draw();
        });

        var table_kwitansi = $('#table_kwitansi').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: siteurl + active_controller + 'get_alokasi_penerimaan',
                type: 'POST',
                data: function(d) {
                    d.bank = $('#bank_filter_kwitansi').val();
                    d.tab = 'draft';
                    d.receipt_kind = 'deposit';
                }
            },
            columns: [
                { data: 'no', className: 'text-center' },
                { data: 'tgl_transaksi_bank', className: 'text-center nowrap' },
                { data: 'reference_no', className: 'text-center' },
                { data: 'bank' },
                { data: 'keterangan' },
                { data: 'nominal', className: 'text-right font-bold' },
                { data: 'status', className: 'text-center' },
                { data: 'action', className: 'text-center nowrap' }
            ],
            order: [[1, 'desc']],
            pageLength: 25,
            drawCallback: function(settings) {
                var cnt = (settings.json && settings.json.recordsFiltered !== undefined) ? settings.json.recordsFiltered : 0;
                $('#badge_draft_deposit').text(cnt);
            }
        });

        $('#btn_search_kwitansi').on('click', function() {
            table_kwitansi.draw();
        });

        // Init DataTables for Tab 2 (Report Penerimaan Server-Side)
        var table_report = $('#table_report').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: siteurl + active_controller + 'get_report_penerimaan',
                type: 'POST',
                data: function(d) {
                    d.year = $('#rep_year').val();
                    d.month = $('#rep_month').val();
                    d.company = $('#rep_company').val();
                    d.receipt_kind = $('#rep_kind').val();
                    d.id_customer = $('#rep_customer').val();
                    d.custom_search = $('#rep_search').val();
                }
            },
            columns: [
                { data: 'no', className: 'text-center', orderable: false },
                { data: 'tanggal', className: 'text-center nowrap' },
                { data: 'bank' },
                { data: 'detail_bank' },
                { data: 'customer' },
                { data: 'no_invoice', className: 'text-center font-bold nowrap', orderable: false },
                { data: 'company', className: 'text-center', orderable: false },
                { data: 'tax_pph', className: 'text-center', orderable: false },
                { data: 'penerimaan_bank', className: 'text-right font-bold nowrap' },
                { data: 'action', className: 'text-center nowrap', orderable: false }
            ],
            order: [[1, 'desc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            drawCallback: function(settings) {
                var json = settings.json;
                if (json) {
                    var countRec = json.recordsFiltered !== undefined ? json.recordsFiltered : 0;
                    var countInv = json.count_invoices !== undefined ? json.count_invoices : 0;
                    $('#badge_report').text(countRec);
                    $('#foot_summary_report').html('<b>Total (' + countRec + ' penerimaan, ' + countInv + ' invoice):</b>');
                    $('#foot_tot_nominal_report').html('<b>' + (json.total_nominal || 'Rp. 0,00') + '</b>');
                }
                updateExportLink();
            }
        });

        function updateExportLink() {
            var year = $('#rep_year').val();
            var month = $('#rep_month').val();
            var company = $('#rep_company').val();
            var search = $('#rep_search').val();

            var expUrl = siteurl + active_controller + 'export_excel?year=' + encodeURIComponent(year) +
                '&month=' + encodeURIComponent(month) +
                '&company=' + encodeURIComponent(company) +
                '&search=' + encodeURIComponent(search) + '&receipt_kind=' + encodeURIComponent($('#rep_kind').val()) + '&id_customer=' + encodeURIComponent($('#rep_customer').val());
            $('#btn_export_excel').attr('href', expUrl);
        }

        $('#btn_filter_report').on('click', function() {
            table_report.draw();
        });

        $('#rep_search').on('keyup', function(e) {
            if (e.keyCode === 13) {
                table_report.draw();
            }
        });

        $('#rep_year, #rep_month, #rep_company, #rep_kind, #rep_customer').on('change', function() {
            table_report.draw();
        });

        $('#btn_reset_report').on('click', function() {
            $('#rep_year').val('<?= date('Y') ?>');
            $('#rep_month').val('');
            $('#rep_company').val('');
            $('#rep_search').val('');
            $('#rep_kind, #rep_customer').val('');
            table_report.draw();
        });

        // Tab switch handler
        $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
            var target = $(e.target).attr('href');
            if (target === '#tab_report') {
                table_report.columns.adjust().draw();
            } else if (target === '#tab_penerimaan') {
                table.columns.adjust().draw();
            } else if (target === '#tab_kwitansi') {
                table_kwitansi.columns.adjust().draw();
            }
        });

        // Buka tab dari alamat, mis. penerimaan_uang#tab_kwitansi dari daftar Kwitansi Deposit
        if (['#tab_penerimaan', '#tab_kwitansi', '#tab_report'].indexOf(window.location.hash) !== -1) {
            $('#penerimaan_tabs a[href="' + window.location.hash + '"]').tab('show');
        }

        // Detail Modal Click
        $(document).on('click', '.detail', function() {
            var id = $(this).data('id');
            $('#MyModalBody').html('<div class="text-center" style="padding: 40px;"><i class="fa fa-spinner fa-spin fa-2x text-primary"></i><p style="margin-top: 10px;">Memuat detail penerimaan...</p></div>');
            $('#dialog-popup').modal('show');

            $.ajax({
                type: 'POST',
                url: siteurl + active_controller + 'detail',
                data: { id_alokasi: id },
                success: function(result) {
                    $('#MyModalBody').html(result);
                    // Input total/kontrol di modal detail memakai class autonum; tanpa init nominalnya tampil tanpa pemisah ribuan.
                    $('#MyModalBody .autonum').autoNumeric('init', {
                        vMin: '-9999999999999.99',
                        vMax: '9999999999999.99',
                        aSep: ',',
                        aDec: '.'
                    });
                },
                error: function() {
                    $('#MyModalBody').html('<div class="alert alert-danger">Gagal memuat rincian data penerimaan.</div>');
                }
            });
        });

        // Rollback Click
        $(document).on('click', '.rollback', function() {
            var id = $(this).data('id');

            swal({
                type: 'warning',
                title: 'Konfirmasi Rollback',
                text: 'Apakah Anda yakin ingin membatalkan (rollback) penerimaan ini? Saldo piutang dan sisa alokasi bank akan dipulihkan.',
                showCancelButton: true,
                confirmButtonText: 'Ya, Rollback',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dd4b39'
            }, function(isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        type: 'POST',
                        url: siteurl + active_controller + 'rollback_penerimaan',
                        data: { id_penerimaan: id },
                        dataType: 'json',
                        success: function(res) {
                            if (res.status == '1') {
                                swal({
                                    type: 'success',
                                    title: 'Berhasil !',
                                    text: res.msg || 'Penerimaan berhasil di-rollback.',
                                    timer: 2500
                                });
                                table.draw();
                                table_report.draw();
                            } else {
                                swal({
                                    type: 'warning',
                                    title: 'Gagal Rollback',
                                    text: res.msg || res.message || 'Tidak dapat melakukan rollback.',
                                    timer: 3500
                                });
                            }
                        },
                        error: function() {
                            swal({
                                type: 'error',
                                title: 'Error !',
                                text: 'Gagal menghubungi server.',
                                timer: 3000
                            });
                        }
                    });
                }
            });
        });

        // Trigger initial export link setup
        updateExportLink();
    });
</script>
