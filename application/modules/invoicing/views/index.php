<?php
$ENABLE_ADD     = has_permission('Invoicing.Add');
$ENABLE_MANAGE  = has_permission('Invoicing.Manage');
$ENABLE_VIEW    = has_permission('Invoicing.View');
$ENABLE_DELETE  = has_permission('Invoicing.Delete');
?>
<!-- <link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>"> -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.7/css/dataTables.dataTables.min.css">

<link rel="stylesheet" href="<?= base_url('assets/css/invoicing.css?v='.filemtime(FCPATH.'assets/css/invoicing.css')) ?>">
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<div class="box invoice-workspace" id="invoicing-workspace">
    <div class="invoice-heading">
        <div><h2>Daftar invoice</h2><p>Kelola tagihan konsultasi per TOP dan invoice non konsultasi.</p></div>
        <div class="invoice-heading-actions"><?php if ($this->auth->is_admin() && has_permission('Invoicing.Configure')): ?><a href="<?= base_url('invoicing/coa_settings') ?>" class="btn btn-default"><i class="fa fa-sliders" aria-hidden="true"></i> Pengaturan COA</a><?php endif; ?></div>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <ul class="invoice-tabs" role="tablist" aria-label="Jenis invoice">
            <li role="presentation" class="tab_pin tab_1 active" data-no="1"><button type="button" id="invoice-tab-consulting" role="tab" aria-selected="true" aria-controls="invoice-panel-consulting">Konsultasi <span class="invoice-tab-count" id="count-consulting"></span></button></li>
            <li role="presentation" class="tab_pin tab_2" data-no="2"><button type="button" id="invoice-tab-other" role="tab" aria-selected="false" aria-controls="invoice-panel-other" tabindex="-1">Non Konsultasi</button></li>
            <li role="presentation" class="tab_pin tab_3" data-no="3"><button type="button" id="invoice-tab-invoiced" role="tab" aria-selected="false" aria-controls="invoice-panel-invoiced" tabindex="-1">Invoiced <span class="invoice-tab-count" id="count-invoiced"></span></button></li>
        </ul>
        <div class="col_1" id="invoice-panel-consulting" role="tabpanel" aria-labelledby="invoice-tab-consulting">
            <div class="invoice-toolbar">
                <div class="invoice-filter">
                    <label for="filter_tahun">Tahun</label>
                    <select name="filter_tahun" id="filter_tahun" class="form-control form-control-sm">
                        <?php
                        $tahun_sekarang = date('Y');
                        for ($y = $tahun_sekarang; $y >= 2020; $y--) :
                        ?>
                            <option value="<?= $y ?>" <?= ($y == $tahun_sekarang) ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="invoice-toolbar-actions">
                    <button type="button" class="btn btn-primary btn_filter">Terapkan filter</button>
                </div>
                <div class="invoice-toolbar-actions invoice-toolbar-end"><a href="<?= base_url('invoicing/download_excel') ?>" class="btn btn-default btn_download_excel"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Ekspor Excel</a></div>
            </div>
            <p class="invoice-table-status" id="invoice-list-status" role="status" aria-live="polite"></p>
            <div class="invoice-table-scroll" role="region" aria-label="Daftar tagihan konsultasi, geser untuk melihat seluruh kolom" tabindex="0">
            <table id="table_penawaran" class="table table-striped invoice-list-table invoice-consulting-table">
                <thead>
                    <tr>
                        <th scope="col" width="4%">No.</th>
                        <th scope="col" width="21%">Dokumen</th>
                        <th scope="col" width="32%">Customer &amp; Project</th>
                        <th scope="col" width="10%">Company</th>
                        <th scope="col" width="17%">Tim</th>
                        <th scope="col" width="9%">Status</th>
                        <th scope="col" width="7%" class="text-center">Aksi</th>
                    </tr>
                </thead>
            </table>
            </div>
        </div>
        <div class="col_2" id="invoice-panel-other" role="tabpanel" aria-labelledby="invoice-tab-other" style="display: none;">
            <div class="invoice-toolbar">
                <div class="invoice-filter">
                    <label for="filter_tahun_non_kons">Tahun</label>
                    <select name="filter_tahun_non_kons" id="filter_tahun_non_kons" class="form-control form-control-sm">
                        <?php
                        for ($y = $tahun_sekarang; $y >= 2020; $y--) :
                        ?>
                            <option value="<?= $y ?>" <?= ($y == $tahun_sekarang) ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="invoice-toolbar-actions">
                    <button type="button" class="btn btn-primary btn_filter_non_kons">Terapkan filter</button>
                </div>
                <div class="invoice-toolbar-actions invoice-toolbar-end"><?php if ($ENABLE_ADD): ?><a href="<?= base_url('invoicing/list_penawaran_non_kons') ?>" class="btn btn-primary"><i class="fa fa-plus" aria-hidden="true"></i> Buat invoice</a><?php endif; ?></div>
            </div>
            <p class="invoice-table-status" id="invoice-other-status" role="status" aria-live="polite"></p>
            <div class="invoice-table-scroll" role="region" aria-label="Daftar invoice non konsultasi, geser untuk melihat seluruh kolom" tabindex="0">
            <table id="table_penawaran_non_konsultasi" class="table table-striped invoice-list-table">
                <thead>
                    <tr>
                        <th class="text-center">No.</th>
                        <th class="text-center">No. Invoice</th>
                        <th class="text-center">Kepada</th>
                        <th class="text-center">Company</th>
                        <th class="text-center">No. Penawaran</th>
                        <th class="text-center">Penjualan</th>
                        <th class="text-center">PIC</th>
                        <th class="text-center">Revisi</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            </div>
        </div>
        <div class="col_3" id="invoice-panel-invoiced" role="tabpanel" aria-labelledby="invoice-tab-invoiced" style="display: none;">
            <div class="invoice-summary-cards" id="invoiced-summary" role="group" aria-label="Ringkasan invoice sesuai filter" aria-live="polite">
                <div class="invoice-summary-card invoice-summary-main"><span>Total Invoiced</span><strong data-sum="gross">-</strong><small data-sum="count">-</small><small data-sum="filter"></small></div>
                <div class="invoice-summary-card"><span>Penjualan</span><strong data-sum="sales">-</strong></div>
                <div class="invoice-summary-card"><span>PPN</span><strong data-sum="ppn">-</strong></div>
                <div class="invoice-summary-card"><span>Saldo piutang</span><strong data-sum="saldo">-</strong></div>
            </div>
            <div class="invoice-toolbar">
                <div class="invoice-filter">
                    <label for="filter_tahun_inv">Tahun</label>
                    <select name="filter_tahun_inv" id="filter_tahun_inv" class="form-control form-control-sm">
                        <option value="all">Semua tahun</option>
                        <?php for ($y = $tahun_sekarang; $y >= 2020; $y--) : ?>
                            <option value="<?= $y ?>" <?= ($y == $tahun_sekarang) ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="invoice-filter">
                    <label for="filter_bulan_inv">Bulan invoice</label>
                    <select name="filter_bulan_inv" id="filter_bulan_inv" class="form-control form-control-sm">
                        <option value="">Semua bulan</option>
                        <?php foreach (array('Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember') as $i => $namaBulan) : ?>
                            <option value="<?= $i + 1 ?>"><?= $namaBulan ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="invoice-filter">
                    <label for="filter_company_inv">Company</label>
                    <select name="filter_company_inv" id="filter_company_inv" class="form-control form-control-sm">
                        <option value="">Semua company</option>
                        <?php foreach (array('STM', 'VUCA', 'SUSTAIN') as $group) : ?>
                            <option value="<?= $group ?>"><?= $group ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="invoice-filter">
                    <label for="filter_ppn_inv">Perlakuan PPN</label>
                    <select name="filter_ppn_inv" id="filter_ppn_inv" class="form-control form-control-sm">
                        <option value="">Semua perlakuan</option>
                        <?php foreach (Invoice_consulting::tax_treatments() as $key => $label) : ?>
                            <option value="<?= $key ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="invoice-toolbar-actions">
                    <button type="button" class="btn btn-primary btn_filter_invoiced">Terapkan filter</button>
                </div>
                <div class="invoice-toolbar-actions invoice-toolbar-end"><a href="<?= base_url('invoicing/download_excel') ?>" class="btn btn-default btn_download_excel_invoiced"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Ekspor Excel</a></div>
            </div>
            <p class="invoice-table-status" id="invoice-invoiced-status" role="status" aria-live="polite"></p>
            <div class="invoice-table-scroll" role="region" aria-label="Daftar invoice konsultasi yang sudah terbit, geser untuk melihat seluruh kolom" tabindex="0">
            <table id="table_invoiced" class="table table-striped invoice-list-table invoice-consulting-table">
                <thead>
                    <tr>
                        <th scope="col" width="3%">No.</th>
                        <th scope="col" width="13%">Dokumen</th>
                        <th scope="col" width="8%">Tanggal Invoice</th>
                        <th scope="col" width="17%">Customer &amp; Project</th>
                        <th scope="col" width="7%">Company</th>
                        <th scope="col" width="11%">Tim</th>
                        <th scope="col" width="8%">Perlakuan PPN</th>
                        <th scope="col" width="17%" class="text-right">Nilai (Rp)</th>
                        <th scope="col" width="8%">Status</th>
                        <th scope="col" width="8%" class="text-center">Aksi</th>
                    </tr>
                </thead>
            </table>
            </div>
        </div>
    </div>
    <!-- /.box-body -->
</div>
<div class="modal" id="modal_print" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"><span class="fa fa-print"></span> Print Invoice</h4>
            </div>
            <div class="modal-body" id="MyModalBody">
                <div class="form-group">
                    <input type="hidden" class="id_inv">
                    <label for="">Company</label>
                    <select name="company" id="" class="form-control form-control-sm company">
                        <option value="">- Company -</option>
                        <?php
                        foreach ($data_company as $item) :
                            echo '<option value="' . $item->id . '">' . $item->nm_company . '</option>';
                        endforeach;
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="keterangan_print">Keterangan Print</label>
                    <textarea name="keterangan_print" id="keterangan_print" class="form-control form-control-sm"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success confirm_jenis_header"><i class="fa fa-check"></i> Proses</button>
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <span class="glyphicon glyphicon-remove"></span> Batal
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="modal_print_non_kons" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"><span class="fa fa-print"></span> Print Invoice Non Konsultasi</h4>
            </div>
            <div class="modal-body" id="MyModalBody">
                <div class="form-group">
                    <input type="hidden" class="id_inv_non_kons">
                    <label for="">Company</label>
                    <select name="company_non_kons" id="" class="form-control form-control-sm company_non_kons">
                        <option value="">- Company -</option>
                        <?php
                        foreach ($data_company as $item) :
                            echo '<option value="' . $item->id . '">' . $item->nm_company . '</option>';
                        endforeach;
                        ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success confirm_jenis_header_non_kons"><i class="fa fa-check"></i> Proses</button>
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <span class="glyphicon glyphicon-remove"></span> Batal
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="modal_print_vuca" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"><span class="fa fa-print"></span> Print Invoice Vuca</h4>
            </div>
            <div class="modal-body" id="MyModalBody">
                <input type="hidden" class="id_inv_vuca">
                <div class="form-group">
                    <label for="keterangan_print_vuca">Keterangan Print</label>
                    <textarea name="keterangan_print_vuca" id="keterangan_print_vuca" class="form-control form-control-sm"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success confirm_jenis_header_vuca"><i class="fa fa-check"></i> Proses</button>
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <span class="glyphicon glyphicon-remove"></span> Batal
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="modal_print_kwitansi" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"><span class="fa fa-file-pdf-o"></span> Print Kwitansi</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <input type="hidden" class="id_inv_kwitansi">
                    <label for="">Company</label>
                    <select name="company_kwitansi" id="" class="form-control form-control-sm company_kwitansi">
                        <option value="">- Company -</option>
                        <?php
                        foreach ($data_company as $item) :
                            echo '<option value="' . $item->id . '">' . $item->nm_company . '</option>';
                        endforeach;
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="keterangan_print_kwitansi">Keterangan Print</label>
                    <textarea name="keterangan_print_kwitansi" id="keterangan_print_kwitansi" class="form-control form-control-sm"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success confirm_kwitansi"><i class="fa fa-check"></i> Proses</button>
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <span class="glyphicon glyphicon-remove"></span> Batal
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="modal_print_kwitansi_vuca" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"><span class="fa fa-file-pdf-o"></span> Print Kwitansi Vuca</h4>
            </div>
            <div class="modal-body" id="MyModalBody">
                <input type="hidden" class="id_inv_kwitansi_vuca">
                <div class="form-group">
                    <label for="keterangan_print_kwitansi_vuca">Keterangan Print</label>
                    <textarea name="keterangan_print_kwitansi_vuca" id="keterangan_print_kwitansi_vuca" class="form-control form-control-sm"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success confirm_kwitansi_vuca"><i class="fa fa-check"></i> Proses</button>
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <span class="glyphicon glyphicon-remove"></span> Batal
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="modal_list_non_kons" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"><span class="fa fa-print"></span> Closing Invoice</h4>
            </div>
            <form action="" method="post" id="form_close_invoice">
                <div class="modal-body" id="MyModalBody">
                    <input type="hidden" name="id_invoicing">
                    <div class="form-group">
                        <label for="">Alasan Closing Invoice</label>
                        <textarea name="alasan_closing" id="" class="form-control form-control-sm"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-danger" name="simpan_closing"><i class="fa fa-close"></i> Close Invoice !</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <span class="glyphicon glyphicon-remove"></span> Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div id="form-data"></div>
<!-- DataTables -->
<!-- <script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script> -->

<script src="https://cdn.datatables.net/2.1.7/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= base_url('assets/js/invoicing/action-menu.js?v='.filemtime(FCPATH.'assets/js/invoicing/action-menu.js')) ?>"></script>
<!-- page script -->
<script type="text/javascript">
    $(document).ready(function() {
        [['#table_penawaran', '#invoice-list-status'], ['#table_penawaran_non_konsultasi', '#invoice-other-status'], ['#table_invoiced', '#invoice-invoiced-status']].forEach(function(pair) {
            $(pair[0]).on('preXhr.dt', function() {
                $(pair[1]).attr('data-state', 'loading').text('Memuat daftar invoice…');
                $(this).attr('aria-busy', 'true');
            }).on('xhr.dt', function(event, settings, json) {
                $(this).attr('aria-busy', 'false');
                if (json === null) {
                    $(pair[1]).attr('data-state', 'error').text('Daftar invoice gagal dimuat. Tekan Terapkan filter untuk mencoba lagi.');
                    return true;
                }
                $(pair[1]).removeAttr('data-state').text('');
                // Badge tab Konsultasi = jumlah TOP belum ditagihkan pada tahun terpilih; tidak berubah saat mencari.
                if (pair[0] === '#table_penawaran' && !consultingSearched) $('#count-consulting').text(json.recordsTotal);
                // Pencarian di kotak tabel Invoiced juga menggeser card; paging dengan filter sama tidak memuat ulang.
                if (pair[0] === '#table_invoiced' && JSON.stringify(invoicedFilters(true)) !== invoicedSummaryKey) loadInvoicedSummary();
            });
        });
        $('.invoice-tabs [role="tab"]').on('keydown', function(event) {
            var tabs = $('.invoice-tabs [role="tab"]'), index = tabs.index(this), next;
            if (event.key === 'ArrowRight') next = (index + 1) % tabs.length;
            else if (event.key === 'ArrowLeft') next = (index + tabs.length - 1) % tabs.length;
            else if (event.key === 'Home') next = 0;
            else if (event.key === 'End') next = tabs.length - 1;
            else return;
            event.preventDefault(); tabs.eq(next).trigger('click').trigger('focus');
        });
        DataTables();
        loadInvoicedCount();
    });

    // Badge tab Invoiced = seluruh invoice (semua tahun, tanpa filter), memakai endpoint card.
    function loadInvoicedCount() {
        $.ajax({url: siteurl + active_controller + 'invoiced_summary', type: 'POST', dataType: 'JSON', data: {filter_tahun: 'all'}})
            .done(function(t) { $('#count-invoiced').text(t.count); });
    }

    // $(document).on('click', '.tab_pin', function() {
    //     var no = $(this).data('no');
    //     if (no == 1) {
    //         DataTables();
    //         $('.col_1').show();
    //         $('.col_2').hide();

    //         $('.tab_1').addClass('active');
    //         $('.tab_2').removeClass('active');
    //     } else {
    //         DataTablesNonKonsultasi();
    //         $('.col_1').hide();
    //         $('.col_2').show();

    //         $('.tab_1').removeClass('active');
    //         $('.tab_2').addClass('active');
    //     }
    // });

    $(document).on('click', '.pilih_print_inv', function() {
        var id_inv = $(this).data('id_inv');

        $('.id_inv').val(id_inv);
    });

    $(document).on('click', '.pilih_print_inv_vuca', function() {
        var id_inv = $(this).data('id_inv');

        $('.id_inv_vuca').val(id_inv);
    });

    $(document).on('click', '.pilih_print_inv_non_kons', function() {
        var id_inv = $(this).data('id_inv');

        $('.id_inv_non_kons').val(id_inv);
    });

    $(document).on('click', '.confirm_jenis_header', function() {
        var company = $('.company').val();
        var id_inv = $('.id_inv').val();
        var keterangan_print = $('#keterangan_print').val();

        if (company == '') {
            Swal.fire({
                icon: 'warning',
                title: 'Warning !',
                text: 'Company cannot be empty !'
            });

            return false;
        } else {
            $.ajax({
                type: 'post',
                url: siteurl + active_controller + 'save_keterangan_print',
                data: {
                    'id': id_inv,
                    'keterangan_print': keterangan_print,
                    'company': company
                },
                cache: false,
                dataType: 'json',
                success: function(result) {
                    window.open(siteurl + active_controller + 'print_invoicing/' + id_inv + '/' + company, '_blank')
                },
                error: function(result) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error !',
                        text: 'Please try again later !',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            });


        }
    })

    $(document).on('click', '.confirm_jenis_header_vuca', function() {
        var id_inv = $('.id_inv_vuca').val();
        var keterangan_print = $('#keterangan_print_vuca').val();

        $.ajax({
            type: 'post',
            url: siteurl + active_controller + 'save_keterangan_print_vuca',
            data: {
                'id': id_inv,
                'keterangan_print': keterangan_print
            },
            cache: false,
            dataType: 'json',
            success: function(result) {
                window.open(siteurl + active_controller + 'print_invoicing_vuca/' + id_inv + '/4', '_blank')
            },
            error: function(result) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error !',
                    text: 'Please try again later !',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        });
    })

    $(document).on('click', '.confirm_jenis_header_non_kons', function() {
        var company = $('.company_non_kons').val();
        var id_inv = $('.id_inv_non_kons').val();

        if (company == '') {
            Swal.fire({
                icon: 'warning',
                title: 'Warning !',
                text: 'Company cannot be empty !'
            });

            return false;
        } else {
            window.open(siteurl + active_controller + 'print_invoice_non_kons/' + id_inv + '/' + company, '_blank')


        }
    })

    $(document).on('click', '.pilih_print_kwitansi', function() {
        var id_inv = $(this).data('id_inv');

        $('.id_inv_kwitansi').val(id_inv);
    });

    $(document).on('click', '.pilih_print_kwitansi_vuca', function() {
        var id_inv = $(this).data('id_inv');

        $('.id_inv_kwitansi_vuca').val(id_inv);
    });

    $(document).on('click', '.confirm_kwitansi', function() {
        var company = $('.company_kwitansi').val();
        var id_inv = $('.id_inv_kwitansi').val();
        var keterangan_print = $('#keterangan_print_kwitansi').val();

        if (company == '') {
            Swal.fire({
                icon: 'warning',
                title: 'Warning !',
                text: 'Company cannot be empty !'
            });

            return false;
        } else {
            $.ajax({
                type: 'post',
                url: siteurl + active_controller + 'save_keterangan_print_kwitansi',
                data: {
                    'id': id_inv,
                    'keterangan_print': keterangan_print,
                    'company': company
                },
                cache: false,
                dataType: 'json',
                success: function(result) {
                    window.open(siteurl + active_controller + 'print_kwitansi/' + id_inv + '/' + company, '_blank')
                },
                error: function(result) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error !',
                        text: 'Please try again later !',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            });
        }
    })

    $(document).on('click', '.confirm_kwitansi_vuca', function() {
        var id_inv = $('.id_inv_kwitansi_vuca').val();
        var keterangan_print = $('#keterangan_print_kwitansi_vuca').val();

        $.ajax({
            type: 'post',
            url: siteurl + active_controller + 'save_keterangan_print_kwitansi_vuca',
            data: {
                'id': id_inv,
                'keterangan_print': keterangan_print
            },
            cache: false,
            dataType: 'json',
            success: function(result) {
                window.open(siteurl + active_controller + 'print_kwitansi_vuca/' + id_inv + '/4', '_blank')
            },
            error: function(result) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error !',
                    text: 'Please try again later !',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        });
    })

    $(document).on('click', '.btn_filter', function() {
        DataTables();
    });

    $(document).on('click', '.btn_filter_non_kons', function() {
        DataTablesNonKonsultasi();
    });

    $(document).on('click', '.btn_filter_invoiced', function() {
        DataTablesInvoiced();
    });

    // Tab Konsultasi hanya berisi tagihan yang belum di-invoice (INV-FR-01).
    $(document).on('click', '.btn_download_excel', function(e) {
        e.preventDefault();
        var filter_tahun = $('#filter_tahun').val();
        var url = siteurl + active_controller + 'download_excel?filter_status=uninvoiced&filter_tahun=' + filter_tahun;
        window.open(url, '_blank');
    });

    // Ekspor tab Invoiced mengikuti ketiga filter (INV-FR-02).
    $(document).on('click', '.btn_download_excel_invoiced', function(e) {
        e.preventDefault();
        window.open(siteurl + active_controller + 'download_invoiced?' + $.param(invoicedFilters(true)), '_blank');
    });

    function invoiceShowTab(active) {
        var tabs = {1: '#invoice-tab-consulting', 2: '#invoice-tab-other', 3: '#invoice-tab-invoiced'};
        [1, 2, 3].forEach(function(no) {
            var on = no === active;
            if (on) { $('.col_' + no).show(); $('.tab_' + no).addClass('active'); }
            else { $('.col_' + no).hide(); $('.tab_' + no).removeClass('active'); }
            $(tabs[no]).attr({'aria-selected': on ? 'true' : 'false', 'tabindex': on ? '0' : '-1'});
        });
    }

    $(document).on('click', '.tab_1', function() {
        invoiceShowTab(1);
        DataTables();
    });

    $(document).on('click', '.tab_2', function() {
        invoiceShowTab(2);
        DataTablesNonKonsultasi();
    });

    $(document).on('click', '.tab_3', function() {
        invoiceShowTab(3);
        DataTablesInvoiced();
    });

    $(document).on('click', '.close_invoice_non_kons', function() {
        var id = $(this).data('id');

        $('input[name="id_invoicing"]').val(id);

        $('#modal_list_non_kons').modal('show');
    })

    $(document).on('submit', '#form_close_invoice', function(e) {
        e.preventDefault();

        // 1. Tambahkan .trim() untuk membuang spasi di awal/akhir
        var alasan_closing = $('textarea[name="alasan_closing"]').val();

        if (!alasan_closing || alasan_closing.trim() === "") {
            Swal.fire({
                title: 'Perhatian!',
                text: 'Mohon isi alasan penutupan invoice terlebih dahulu.',
                icon: 'warning', // Pakai warning biasanya lebih pas untuk validasi input
                confirmButtonText: 'Oke, Saya Isi'
            });
            return false;
        }

        Swal.fire({
            title: 'Konfirmasi Penutupan',
            text: 'Invoice ini akan ditutup secara permanen dan tidak dapat diubah kembali. Lanjutkan?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Tutup Invoice!',
            cancelButtonText: 'Batal',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan loading agar tidak double-submit
                Swal.fire({
                    title: 'Sedang memproses...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Gunakan $(this) untuk mengambil form yang sedang di-submit
                var formData = $(this).serialize();

                $.ajax({
                    type: 'POST',
                    url: siteurl + active_controller + 'save_close_invoice',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: response.msg,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let errorMsg = 'Terjadi kesalahan sistem.';
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMsg = response.msg;
                        } catch (e) {
                            errorMsg = 'Gagal memproses permintaan (Error: ' + xhr.status + ')';
                        }

                        Swal.fire({
                            title: 'Oops!',
                            text: errorMsg,
                            icon: 'error',
                            confirmButtonText: 'Tutup'
                        });
                    }
                });
            }
        });
    });

    function invoiceTableLanguage() {
        return {
            search: 'Cari:', searchPlaceholder: 'Nomor, customer, atau project',
            lengthMenu: 'Tampilkan _MENU_ baris',
            info: '_START_–_END_ dari _TOTAL_ baris', infoEmpty: 'Belum ada baris ditampilkan',
            infoFiltered: '(disaring dari _MAX_ baris)',
            emptyTable: 'Belum ada tagihan pada tahun ini. Pilih tahun lain atau periksa plan tagih.',
            zeroRecords: 'Tidak ada invoice yang sesuai. Ubah kata pencarian atau filter.',
            processing: 'Memuat daftar invoice…', loadingRecords: 'Memuat daftar invoice…',
            paginate: {first: 'Pertama', previous: 'Sebelumnya', next: 'Berikutnya', last: 'Terakhir'}
        };
    }

    function invoiceActionLabels(cell) {
        $(cell).find('a[title],button[title]').each(function() {
            var title = $(this).attr('title');
            $(this).attr('aria-label', title).find('i').attr('aria-hidden', 'true');
        });
        if (window.InvoiceActionMenu) window.InvoiceActionMenu.enhance(cell);
    }

    function invoiceCellText(value) {
        if (value === null || value === undefined || String(value).trim() === '') return '-';
        return String(value).replace(/[&<>"']/g, function(character) {
            return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[character];
        });
    }

    function invoiceDocumentCell(data, type, row) {
        if (type !== 'display') return [row.no_invoice || '', row.no_spk || ''].join(' ');
        var number = row.no_invoice ? invoiceCellText(row.no_invoice) : 'Belum ada invoice';
        return '<div class="invoice-cell-primary">' + number + '</div><div class="invoice-cell-secondary"><span class="invoice-cell-label">SPK:</span> ' + invoiceCellText(row.no_spk) + '</div>';
    }

    function invoiceCustomerCell(data, type, row) {
        if (type !== 'display') return [row.customer || '', row.project || ''].join(' ');
        return '<div class="invoice-cell-primary">' + invoiceCellText(row.customer) + '</div><div class="invoice-cell-secondary"><span class="invoice-cell-label">Project:</span> ' + invoiceCellText(row.project) + '</div>';
    }

    function invoiceTeamCell(data, type, row) {
        if (type !== 'display') return [row.project_leader || '', row.sales || ''].join(' ');
        return '<dl class="invoice-cell-team"><dt>Project Leader</dt><dd>' + invoiceCellText(row.project_leader) + '</dd><dt>Sales</dt><dd>' + invoiceCellText(row.sales) + '</dd></dl>';
    }

    $(document).on('preDraw.dt', '#table_penawaran, #table_penawaran_non_konsultasi, #table_invoiced', function() {
        if (window.InvoiceActionMenu) window.InvoiceActionMenu.close();
    });

    var consultingSearched = false;
    function DataTables() {
        // var dataTables = $('#table_penawaran').dataTable();
        // dataTables.destroy();

        var dataTables = $('#table_penawaran').dataTable({
            ajax: {
                url: siteurl + active_controller + 'get_data_spk',
                type: "POST",
                dataType: "JSON",
                data: function(d) {
                    d.filter_status = 'uninvoiced';
                    d.filter_tahun = $('#filter_tahun').val();
                    consultingSearched = !!(d.search && d.search.value);
                }
            },
            columns: [{
                    data: 'no',
                },
                {
                    data: null, render: invoiceDocumentCell
                },
                {
                    data: null, render: invoiceCustomerCell
                },
                {
                    data: 'company', render: function(value, type) { return type === 'display' ? invoiceCellText(value) : value; }
                },
                {
                    data: null, render: invoiceTeamCell
                },
                {
                    data: 'status'
                },
                {
                    data: 'option', createdCell: invoiceActionLabels
                }
            ],
            language: invoiceTableLanguage(),
            ordering: false,
            autoWidth: false,
            stateLoadParams: function(settings, state) { if (!state.columns || state.columns.length !== 7) return false; },
            responsive: false,
            processing: true,
            serverSide: true,
            stateSave: true,
            destroy: true,
            paging: true
        });
    }

    function invoiceDateCell(data, type) {
        return type === 'display' ? invoiceCellText(data) : data;
    }

    function invoicedFilters(withSearch) {
        var f = {filter_tahun: $('#filter_tahun_inv').val(), filter_bulan: $('#filter_bulan_inv').val(),
            filter_company: $('#filter_company_inv').val(), filter_ppn: $('#filter_ppn_inv').val()};
        if (withSearch) f.filter_search = ($('#table_invoiced_filter input').val() || '');
        return f;
    }

    function invoiceMoney(cents) {
        return (cents / 100).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    // Card Total Invoiced mengikuti filter dan pencarian yang sama dengan tabel dan ekspor (UMKM-FR-07).
    var invoicedSummaryKey = null;
    function loadInvoicedSummary() {
        var box = $('#invoiced-summary');
        if (!box.length) return;
        invoicedSummaryKey = JSON.stringify(invoicedFilters(true));
        $.ajax({url: siteurl + active_controller + 'invoiced_summary', type: 'POST', dataType: 'JSON', data: invoicedFilters(true)}).done(function(t) {
            ['gross', 'sales', 'ppn', 'saldo'].forEach(function(k) { box.find('[data-sum="' + k + '"]').text(invoiceMoney(t[k])); });
            box.find('[data-sum="count"]').text(t.count + ' invoice');
            var parts = [$('#filter_tahun_inv option:selected').text(), $('#filter_bulan_inv').val() ? $('#filter_bulan_inv option:selected').text() : 'Semua bulan',
                $('#filter_company_inv').val() || 'Semua company', $('#filter_ppn_inv').val() ? $('#filter_ppn_inv option:selected').text() : 'Semua perlakuan'];
            box.find('[data-sum="filter"]').text(parts.join(' · '));
        }).fail(function() { box.find('strong').text('-'); box.find('[data-sum="count"]').text('Ringkasan tidak dapat dimuat.'); });
    }

    function invoiceValueCell(data, type, row) {
        var n = row.nilai;
        if (type !== 'display') return n ? String(n.gross) : '';
        if (!n) return '<span class="muted">-</span>';
        return '<dl class="invoice-cell-money"><dt>Penjualan</dt><dd>' + invoiceMoney(n.sales) + '</dd><dt>PPN</dt><dd>' + invoiceMoney(n.ppn) + '</dd>'
            + '<dt>Total tagihan</dt><dd>' + invoiceMoney(n.gross) + '</dd><dt>Saldo piutang</dt><dd>' + invoiceMoney(n.saldo) + '</dd></dl>';
    }

    function DataTablesInvoiced() {
        loadInvoicedSummary();
        $('#table_invoiced').dataTable({
            ajax: {
                url: siteurl + active_controller + 'get_data_spk',
                type: "POST",
                dataType: "JSON",
                data: function(d) {
                    d.filter_status = 'invoiced';
                    d.filter_tahun = $('#filter_tahun_inv').val();
                    d.filter_bulan = $('#filter_bulan_inv').val();
                    d.filter_company = $('#filter_company_inv').val();
                    d.filter_ppn = $('#filter_ppn_inv').val();
                }
            },
            columns: [
                {data: 'no'},
                {data: null, render: invoiceDocumentCell},
                {data: 'tanggal_invoice', render: invoiceDateCell},
                {data: null, render: invoiceCustomerCell},
                {data: 'company', render: function(value, type) { return type === 'display' ? invoiceCellText(value) : value; }},
                {data: null, render: invoiceTeamCell},
                {data: 'tax_label', render: function(value, type) { return type === 'display' ? invoiceCellText(value) : value; }},
                {data: null, render: invoiceValueCell, className: 'text-right'},
                {data: 'status'},
                {data: 'option', createdCell: invoiceActionLabels}
            ],
            language: $.extend({}, invoiceTableLanguage(), {emptyTable: 'Belum ada invoice pada periode ini. Ubah tahun, bulan, company, atau perlakuan PPN.'}),
            ordering: false,
            autoWidth: false,
            stateLoadParams: function(settings, state) { if (!state.columns || state.columns.length !== 10) return false; },
            responsive: false,
            processing: true,
            serverSide: true,
            stateSave: true,
            destroy: true,
            paging: true
        });
    }

    function DataTablesNonKonsultasi() {
        var dataTables = $('#table_penawaran_non_konsultasi').dataTable({
            ajax: {
                url: siteurl + active_controller + 'get_data_quotation_non_konsultasi',
                type: "POST",
                dataType: "JSON",
                data: function(d) {
                    d.filter_tahun = $('#filter_tahun_non_kons').val();
                }
            },
            columns: [{
                    data: 'no'
                },
                {
                    data: 'no_invoice'
                },
                {
                    data: 'kepada'
                },
                {
                    data: 'company'
                },
                {
                    data: 'no_penawaran'
                },
                {
                    data: 'penjualan'
                },
                {
                    data: 'pic'
                },
                {
                    data: 'revisi'
                },
                {
                    data: 'status'
                },
                {
                    data: 'action', createdCell: invoiceActionLabels
                }
            ],
            language: $.extend({}, invoiceTableLanguage(), {emptyTable: 'Belum ada invoice non konsultasi pada tahun ini. Pilih tahun lain atau buat invoice dari penawaran.'}),
            responsive: false,
            processing: true,
            serverSide: true,
            stateSave: true,
            destroy: true,
            paging: true
        });
    }

    function DataTableListNonKons() {
        $('#table_list_non_kons').dataTable({
            serverSide: true,
            processing: true,
            destroy: true,
            paging: true,
            stateSave: true,
            ajax: {
                type: 'get',
                url: siteurl + active_controller + 'datatable_list_non_kons',
                dataType: 'json',
                data: function(d) {

                }
            },
            columns: [{
                    data: 'no'
                },
                {
                    data: 'company'
                },
                {
                    data: 'no_penawaran'
                },
                {
                    data: 'penjualan'
                },
                {
                    data: 'pic'
                },
                {
                    data: 'status'
                },
                {
                    data: 'action'
                }
            ]
        });
    }
</script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>
