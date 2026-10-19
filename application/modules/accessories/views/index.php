<?php
$ENABLE_ADD     = has_permission('Master_Indirect.Add');
$ENABLE_MANAGE  = has_permission('Master_Indirect.Manage');
$ENABLE_VIEW    = has_permission('Master_Indirect.View');
$ENABLE_DELETE  = has_permission('Master_Indirect.Delete');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/accessories-style.css?v=' . (defined('FCPATH') && file_exists(FCPATH . 'assets/css/accessories-style.css') ? filemtime(FCPATH . 'assets/css/accessories-style.css') : '1.0.0')) ?>">

<main class="accessories-page" id="accessories-page">
    <header class="accessories-page-header">
        <div>
            <h1>Barang Stok Aksesoris</h1>
            <p>Kelola katalog dan inventaris barang stok aksesoris, spesifikasi material, dan konversi satuan.</p>
        </div>
        <div class="accessories-actions-group">
            <?php if ($ENABLE_ADD) : ?>
                <a class="accessories-button accessories-button-primary" href="<?= base_url('accessories/add') ?>" title="Tambah Barang Stok">
                    <i class="fa fa-plus" aria-hidden="true"></i>
                    <span>Tambah barang stok</span>
                </a>
            <?php endif; ?>
            <a class="accessories-button accessories-button-secondary" href="<?= base_url('accessories/download_excel'); ?>" target="_blank" title="Unduh Format Excel">
                <i class="fa fa-file-excel-o" aria-hidden="true"></i>
                <span>Unduh Excel</span>
            </a>
        </div>
    </header>

    <div id='alert_edit' class="accessories-notice" role="status" aria-live="polite" hidden></div>

    <section class="accessories-panel" aria-labelledby="accessories-list-title">
        <div class="accessories-panel-header">
            <div class="accessories-panel-heading">
                <span class="accessories-panel-icon" aria-hidden="true"><i class="fa fa-cubes"></i></span>
                <div>
                    <h2 id="accessories-list-title">Daftar Barang Stok Aksesoris</h2>
                    <p>Data barang stok terdaftar beserta kategori, brand, dan status operasional.</p>
                </div>
            </div>
            <div class="accessories-toolbar">
                <div class="accessories-filter-wrapper">
                    <label class="accessories-filter-label" for="id_category">
                        <i class="fa fa-filter" aria-hidden="true"></i> Filter Kategori:
                    </label>
                    <select name="id_category" id="id_category" class="form-control select2">
                        <option value="0">Semua Kategori</option>
                        <?php
                        foreach ($category as $key => $value) {
                            echo "<option value='" . $value['id'] . "'>" . strtoupper($value['nm_category']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="accessories-table-wrap">
            <table id="example1" class="table accessories-table" aria-labelledby="accessories-list-title">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">No.</th>
                        <th style="width: 110px;">Item Code</th>
                        <th>Stock Name</th>
                        <th>Category</th>
                        <th>Trade Name</th>
                        <th>Brand</th>
                        <th>Spec</th>
                        <th style="width: 85px; text-align: center;">Status</th>
                        <th style="width: 90px;">Last By</th>
                        <th style="width: 120px; text-align: center;">Last Date</th>
                        <th style="width: 100px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </section>

    <p class="accessories-footnote">
        <i class="fa fa-info-circle" aria-hidden="true"></i>
        Barang stok aksesoris digunakan untuk alokasi pengadaan, permintaan barang (PR), dan pencatatan gudang.
    </p>
</main>

<div class="modal fade accessories-modal" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="head_title" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
                <span class="accessories-modal-icon" aria-hidden="true"><i class="fa fa-info-circle"></i></span>
                <h2 class="modal-title" id="head_title">Detail Barang Stok Aksesoris</h2>
                <p>Informasi detail spesifikasi dan konversi satuan barang stok.</p>
            </div>
            <div class="modal-body" id="ModalView">
                <div style="text-align: center; color: #7c8981; padding: 36px 0;">
                    <i class="fa fa-spinner fa-spin fa-2x" aria-hidden="true"></i>
                    <p style="margin-top: 10px; font-size: 13px;">Memuat data detail…</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>

<script type="text/javascript">
    $(document).on('click', '.detail', function() {
        var id = $(this).data('id');
        $("#head_title").text("Detail Barang Stok Aksesoris");
        $("#ModalView").html('<div style="text-align: center; color: #7c8981; padding: 36px 0;"><i class="fa fa-spinner fa-spin fa-2x" aria-hidden="true"></i><p style="margin-top: 10px; font-size: 13px;">Memuat data detail…</p></div>');
        $("#dialog-popup").modal('show');

        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + '/detail/' + id,
            data: {
                'id': id
            },
            success: function(data) {
                $("#ModalView").html(data);
            },
            error: function() {
                $("#ModalView").html('<div class="accessories-notice accessories-notice-error"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Gagal memuat data detail.</div>');
            }
        });
    });

    // DELETE DATA
    $(document).on('click', '.delete', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        swal({
                title: "Konfirmasi Hapus",
                text: "Apakah Anda yakin ingin menghapus data barang stok ini?",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Ya, Hapus!",
                cancelButtonText: "Batal",
                closeOnConfirm: false
            },
            function() {
                $.ajax({
                    type: 'POST',
                    url: siteurl + active_controller + '/hapus',
                    dataType: "json",
                    data: {
                        'id': id
                    },
                    success: function(result) {
                        if (result.status == '1' || result.status === 1) {
                            swal({
                                    title: "Sukses!",
                                    text: result.pesan || "Data barang berhasil dihapus.",
                                    type: "success",
                                    timer: 1500,
                                    showConfirmButton: false
                                },
                                function() {
                                    window.location.reload(true);
                                });
                        } else {
                            swal({
                                title: "Gagal!",
                                text: result.pesan || "Data barang gagal dihapus.",
                                type: "error"
                            });
                        }
                    },
                    error: function() {
                        swal({
                            title: "Error",
                            text: "Terjadi kesalahan proses pada server.",
                            type: "error"
                        });
                    }
                });
            });
    });

    $(function() {
        var id_category = $('#id_category').val();
        DataTables(id_category);
        $('.select2').select2({
            width: '100%'
        });

        // GSAP page entrance animation
        if (window.gsap && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            window.gsap.fromTo('.accessories-page-header, .accessories-panel, .accessories-footnote', 
                { y: 14, opacity: 0.5 }, 
                { y: 0, opacity: 1, duration: 0.45, stagger: 0.08, ease: 'power2.out', clearProps: 'all' }
            );
        }
    });

    $(document).on('change', '#id_category', function() {
        var id_category = $('#id_category').val();
        DataTables(id_category);
    });

    function DataTables(id_category) {
        id_category = (typeof id_category !== 'undefined') ? id_category : null;
        $('#example1').DataTable({
            "processing": true,
            "serverSide": true,
            "stateSave": true,
            "bAutoWidth": false,
            "destroy": true,
            "responsive": true,
            "aaSorting": [
                [1, "asc"]
            ],
            "columnDefs": [
                {
                    "targets": [0, 7, 9, 10],
                    "className": "text-center"
                },
                {
                    "targets": [10],
                    "orderable": false
                }
            ],
            "sPaginationType": "simple_numbers",
            "iDisplayLength": 10,
            "aLengthMenu": [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ],
            "language": {
                "emptyTable": "Belum ada data barang stok aksesoris.",
                "zeroRecords": "Tidak ada data barang yang sesuai pencarian.",
                "lengthMenu": "Tampilkan _MENU_ baris",
                "info": "_START_–_END_ dari _TOTAL_ data barang",
                "infoEmpty": "0 data barang",
                "infoFiltered": "(disaring dari _MAX_ total)",
                "paginate": {
                    "first": "Awal",
                    "last": "Akhir",
                    "next": "Berikutnya",
                    "previous": "Sebelumnya"
                }
            },
            "ajax": {
                url: siteurl + active_controller + '/data_side_accessories',
                type: "post",
                data: function(d) {
                    d.id_category = id_category;
                },
                cache: false,
                error: function() {
                    $(".my-grid-error").html("");
                    $("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="11">Data tidak ditemukan di server</th></tr></tbody>');
                    $("#my-grid_processing").css("display", "none");
                }
            }
        });
    }
</script>