<link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css">
<?= mk_tax_badge_css() ?>
<style>
    /* Tab & label mengikuti mockup (.tabs, .cnt, .lbl) */
    .pu-tabs { display: flex; gap: 2px; border-bottom: 1px solid #d2d6de; margin: 0 0 14px; padding: 0; list-style: none; }
    .pu-tabs > li > a { display: block; padding: 9px 16px; color: #777; border: 1px solid transparent; border-bottom: 0; border-radius: 3px 3px 0 0; margin-bottom: -1px; font-weight: 600; text-decoration: none; }
    .pu-tabs > li.active > a { background: #fff; color: #333; border-color: #d2d6de; border-top: 3px solid #3c8dbc; }
    .pu-tabs .cnt { display: inline-block; min-width: 20px; padding: 0 6px; border-radius: 10px; background: #d2d6de; color: #333; font-size: 11px; text-align: center; margin-left: 4px; }
    .pu-lbl { display: inline-block; padding: 2px 8px; border-radius: 10px; color: #fff; font-size: 11px; font-weight: 700; white-space: nowrap; background: #605ca8; }
    .pu-lbl-draft { background: #f39c12; }
    .pu-hint { font-size: 12px; color: #777; }
    .pu-filters { display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap; }
    .pu-filters label { display: block; font-size: 12px; font-weight: 700; margin-bottom: 2px; }
    #table_report td { vertical-align: top; }
    #table_report th { white-space: nowrap; }
    #table_report .r { text-align: right; }
    #table_report .c { text-align: center; }
    #table_report tr.sum td { border-top: 1px solid #d2d6de; }
    #table_report .empty { text-align: center; color: #777; padding: 16px; }
    #table_list_wrapper .dt-search, #table_kwitansi_wrapper .dt-search { display: block; }
</style>

<?php $tabCounts = $tab_counts ?? array('invoice' => 0, 'deposit' => 0, 'report' => 0); ?>
<h1 style="font-size: 24px; margin: 0 0 14px;">Penerimaan Piutang</h1>

<ul class="pu-tabs" id="penerimaan_tabs" role="tablist">
    <li class="active"><a href="#tab_penerimaan" data-toggle="tab" role="tab">Penerimaan Piutang Invoice <span class="cnt"><?= (int)$tabCounts['invoice'] ?></span></a></li>
    <li><a href="#tab_kwitansi" data-toggle="tab" role="tab">Penerimaan Piutang Kwitansi Deposit <span class="cnt"><?= (int)$tabCounts['deposit'] ?></span></a></li>
    <li><a href="#tab_report" data-toggle="tab" role="tab">Report Penerimaan <span class="cnt"><?= (int)$tabCounts['report'] ?></span></a></li>
</ul>

<div class="box box-primary">
    <div class="box-body">
        <div class="tab-content">
            <!-- TAB 1: PENERIMAAN INVOICE (DRAFT) -->
            <div class="tab-pane active" id="tab_penerimaan">
                <div class="pu-filters" style="margin-bottom: 12px;">
                    <div style="min-width: 260px; flex: 0 1 420px;">
                        <select name="bank" id="bank_filter" class="form-control input-sm" aria-label="Bank">
                            <option value="">- Pilih Bank -</option>
                            <?php foreach ($list_bank as $item_bank) : ?>
                                <option value="<?= $item_bank['id'] ?>"><?= htmlspecialchars($item_bank['nama_bank'] . ' - ' . $item_bank['rekening'] . ' - ' . $item_bank['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="button" class="btn btn-primary" id="btn_search_draft">Search</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="table_list" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Tanggal Transaksi</th>
                                <th>Bank</th>
                                <th>Keterangan</th>
                                <th class="text-right">Nominal</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 2: PENERIMAAN KWITANSI DEPOSIT (DRAFT) -->
            <div class="tab-pane" id="tab_kwitansi">
                <div class="pu-filters" style="margin-bottom: 12px;">
                    <div style="min-width: 260px; flex: 0 1 420px;">
                        <select name="bank" id="bank_filter_kwitansi" class="form-control input-sm" aria-label="Bank">
                            <option value="">- Pilih Bank -</option>
                            <?php foreach ($list_bank as $item_bank) : ?>
                                <option value="<?= $item_bank['id'] ?>"><?= htmlspecialchars($item_bank['nama_bank'] . ' - ' . $item_bank['rekening'] . ' - ' . $item_bank['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="button" class="btn btn-primary" id="btn_search_kwitansi">Search</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="table_kwitansi" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Tanggal Transaksi</th>
                                <th>Bank</th>
                                <th>Keterangan</th>
                                <th class="text-right">Nominal</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 3: REPORT PENERIMAAN (USED) -->
            <div class="tab-pane" id="tab_report">
                <div class="pu-filters">
                    <div>
                        <label for="rep_year">Tahun</label>
                        <select id="rep_year" class="form-control input-sm">
                            <option value="">Semua</option>
                            <?php foreach ($list_years as $yr) : ?>
                                <option value="<?= $yr ?>"><?= $yr ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="rep_month">Bulan</label>
                        <select id="rep_month" class="form-control input-sm">
                            <option value="">Semua</option>
                            <?php
                            $months = array(1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April', 5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus', 9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember');
                            foreach ($months as $mNum => $mName) :
                            ?>
                                <option value="<?= $mNum ?>"><?= $mName ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="rep_company">Company</label>
                        <select id="rep_company" class="form-control input-sm">
                            <option value="">Semua</option>
                            <?php foreach ($list_companies as $cmp) : ?>
                                <option value="<?= $cmp ?>"><?= $cmp ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="rep_kind">Jenis</label>
                        <select id="rep_kind" class="form-control input-sm">
                            <option value="">Semua</option>
                            <option value="invoice">Penerimaan Piutang Invoice</option>
                            <option value="deposit">Penerimaan Piutang Kwitansi Deposit</option>
                        </select>
                    </div>
                    <div>
                        <label for="rep_search">Search</label>
                        <input type="search" id="rep_search" class="form-control input-sm" placeholder="Cari apa saja: teks, angka, tanggal">
                    </div>
                    <div style="margin-left: auto;">
                        <a href="javascript:void(0)" class="btn btn-success" id="btn_export_excel">Export to Excel</a>
                    </div>
                </div>

                <div class="table-responsive" style="margin-top: 12px;">
                    <table class="table" id="table_report" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Tanggal Penerimaan</th>
                                <th>Bank</th>
                                <th>Detail Penerimaan Bank</th>
                                <th>Customer</th>
                                <th>Nomor Invoice / Kwitansi</th>
                                <th>Company</th>
                                <th>TAX Code &amp; PPh 23</th>
                                <th class="r">Penerimaan Bank</th>
                                <th class="c">Action</th>
                            </tr>
                        </thead>
                        <tbody id="report_body"></tbody>
                    </table>
                </div>
                <div class="pu-hint" style="margin-top: 8px;">Tanggal penerimaan mengikuti tanggal rekening koran yang diupload di Alokasi. Satu transfer yang membayar beberapa invoice tampil sebagai satu grup.</div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        // DataTables draft list: urut tanggal naik (diurutkan server), tanpa sort kolom; teks sesuai mockup.
        function draftTable(selector, bankSel, extra) {
            return $(selector).DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                ajax: {
                    url: siteurl + active_controller + 'get_alokasi_penerimaan',
                    type: 'POST',
                    data: function(d) {
                        d.bank = $(bankSel).val();
                        d.tab = 'draft';
                        if (extra) d.receipt_kind = extra;
                    }
                },
                columns: [
                    { data: 'no' },
                    { data: 'tgl_transaksi_bank', className: 'nowrap' },
                    { data: 'bank' },
                    { data: 'keterangan' },
                    { data: 'nominal', className: 'text-right' },
                    { data: 'status' },
                    { data: 'action', className: 'text-center' }
                ],
                pageLength: 25,
                lengthMenu: [10, 25, 50, 100],
                language: {
                    emptyTable: 'Tidak ada penerimaan yang menunggu proses. Yang sudah diproses ada di tab Report Penerimaan.',
                    zeroRecords: 'Tidak ada penerimaan yang menunggu proses. Yang sudah diproses ada di tab Report Penerimaan.',
                    lengthMenu: '_MENU_ entries per page',
                    search: 'Search:',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    infoEmpty: 'Showing 0 to 0 of 0 entries',
                    infoFiltered: ''
                }
            });
        }
        var table = draftTable('#table_list', '#bank_filter', null);
        var table_kwitansi = draftTable('#table_kwitansi', '#bank_filter_kwitansi', 'deposit');

        $('#btn_search_draft').on('click', function() { table.draw(); });
        $('#btn_search_kwitansi').on('click', function() { table_kwitansi.draw(); });

        // Report: dimuat sebagai HTML (satu baris per invoice, kolom penerimaan di-rowspan) seperti mockup.
        var reportReq = null;
        function loadReport() {
            if (reportReq) reportReq.abort();
            reportReq = $.ajax({
                url: siteurl + active_controller + 'get_report_penerimaan',
                type: 'POST',
                dataType: 'json',
                data: {
                    year: $('#rep_year').val(),
                    month: $('#rep_month').val(),
                    company: $('#rep_company').val(),
                    receipt_kind: $('#rep_kind').val(),
                    id_customer: '',
                    custom_search: $('#rep_search').val()
                }
            }).done(function(res) {
                var body = res.html || '';
                if (!body) {
                    body = '<tr><td colspan="10" class="empty">Tidak ada penerimaan yang sudah diproses untuk filter ini.</td></tr>';
                } else {
                    body += '<tr class="sum"><td colspan="8" style="text-align:right"><b>Total (' + res.count_receipts + ' penerimaan, ' + res.count_invoices + ' invoice/kwitansi)</b></td><td class="r"><b>' + res.total_nominal + '</b></td><td></td></tr>';
                }
                $('#report_body').html(body);
                updateExportLink(!res.html);
            });
        }

        function updateExportLink(disabled) {
            var expUrl = siteurl + active_controller + 'export_excel?year=' + encodeURIComponent($('#rep_year').val()) +
                '&month=' + encodeURIComponent($('#rep_month').val()) +
                '&company=' + encodeURIComponent($('#rep_company').val()) +
                '&search=' + encodeURIComponent($('#rep_search').val()) + '&receipt_kind=' + encodeURIComponent($('#rep_kind').val()) + '&id_customer=';
            var btn = $('#btn_export_excel');
            if (disabled) {
                btn.removeAttr('href').addClass('disabled').attr('disabled', 'disabled');
            } else {
                btn.attr('href', expUrl).removeClass('disabled').removeAttr('disabled');
            }
        }

        // Filter report berjalan langsung (seperti mockup): select saat berubah, pencarian bebas dengan jeda singkat.
        var searchTimer = null;
        $('#rep_search').on('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(loadReport, 300);
        });
        $('#rep_year, #rep_month, #rep_company, #rep_kind').on('change', loadReport);

        // Tab switch handler
        $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
            var target = $(e.target).attr('href');
            if (target === '#tab_report') {
                loadReport();
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

        // Rollback Click (fitur tambahan aplikasi, dipertahankan)
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
                                table_kwitansi.draw();
                                loadReport();
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

        loadReport();
    });
</script>
