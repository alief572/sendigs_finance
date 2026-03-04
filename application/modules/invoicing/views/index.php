<?php
$ENABLE_ADD     = has_permission('Invoicing.Add');
$ENABLE_MANAGE  = has_permission('Invoicing.Manage');
$ENABLE_VIEW    = has_permission('Invoicing.View');
$ENABLE_DELETE  = has_permission('Invoicing.Delete');
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
</style>
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<div class="box">
    <div class="box-header">

    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="tab_pin tab_1 active" data-no="1"><a href="javascript:void(0);">Konsultasi</a></li>
            <li role="presentation" class="tab_pin tab_2" data-no="2"><a href="javascript:void(0);">Non Konsultasi</a></li>
        </ul>
        <div class="col_1">
            <table id="table_penawaran" class="table table-bordered table-striped">
                <thead class="bg-primary">
                    <tr>
                        <th class="text-center" width="5%">No.</th>
                        <th class="text-center" width="15%">No. Invoice</th>
                        <th class="text-center" width="15%">Company</th>
                        <th class="text-center" width="15%">No. SPK</th>
                        <th class="text-center" width="20%">Customer</th>
                        <th class="text-center" width="15%">Project</th>
                        <th class="text-center" width="15%">Project Leader</th>
                        <th class="text-center" width="15%">Sales</th>
                        <th class="text-center" width="10%">Status</th>
                        <th class="text-center" width="15%">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
        <div class="col_2" style="display: none;">
            <br>
            <a href="<?= base_url('invoicing/list_penawaran_non_kons') ?>" class="btn btn-sm btn-success" title="Add Invoice"><i class="fa fa-plus"></i> Add Invoice</a>
            <br><br>
            <table id="table_penawaran_non_konsultasi" class="table table-bordered table-striped">
                <thead class="bg-primary">
                    <tr>
                        <th class="text-center">No.</th>
                        <th class="text-center">No. Invoice</th>
                        <th class="text-center">Company</th>
                        <th class="text-center">No. Penawaran</th>
                        <th class="text-center">Penjualan</th>
                        <th class="text-center">PIC</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
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
                <div class="form-group">
                    <label for="keterangan_print_non_kons">Keterangan Print</label>
                    <textarea name="keterangan_print_non_kons" id="keterangan_print_non_kons" class="form-control form-control-sm"></textarea>
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
<!-- page script -->
<script type="text/javascript">
    $(document).ready(function() {
        DataTables();
    });

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
        var keterangan_print = $('#keterangan_print_non_kons').val();

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
                url: siteurl + active_controller + 'save_keterangan_print_non_kons',
                data: {
                    'id': id_inv,
                    'keterangan_print': keterangan_print,
                    'company': company
                },
                cache: false,
                dataType: 'json',
                success: function(result) {
                    window.open(siteurl + active_controller + 'print_invoice_non_kons/' + id_inv + '/' + company, '_blank')
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

    $(document).on('click', '.tab_1', function() {
        $('.col_1').show();
        $('.col_2').hide();

        $('.tab_1').addClass('active');
        $('.tab_2').removeClass('active');
        DataTables();
    });

    $(document).on('click', '.tab_2', function() {
        $('.col_1').hide();
        $('.col_2').show();

        $('.tab_1').removeClass('active');
        $('.tab_2').addClass('active');
        DataTablesNonKonsultasi();
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

    function DataTables() {
        // var dataTables = $('#table_penawaran').dataTable();
        // dataTables.destroy();

        var dataTables = $('#table_penawaran').dataTable({
            ajax: {
                url: siteurl + active_controller + 'get_data_spk',
                type: "POST",
                dataType: "JSON",
                data: function(d) {

                }
            },
            columns: [{
                    data: 'no',
                },
                {
                    data: 'no_invoice'
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
                    data: 'project_leader'
                },
                {
                    data: 'sales'
                },
                {
                    data: 'status'
                },
                {
                    data: 'option'
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

    function DataTablesNonKonsultasi() {
        var dataTables = $('#table_penawaran_non_konsultasi').dataTable({
            ajax: {
                url: siteurl + active_controller + 'get_data_quotation_non_konsultasi',
                type: "POST",
                dataType: "JSON",
                data: function(d) {

                }
            },
            columns: [{
                    data: 'no'
                },
                {
                    data: 'no_invoice'
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
            ],
            responsive: true,
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