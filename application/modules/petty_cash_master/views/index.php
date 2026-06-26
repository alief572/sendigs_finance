<?php
$ENABLE_ADD    = isset($addPermission) ? $addPermission : false;
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box">
    <div class="box-header">
        <h3 class="box-title">Master Petty Cash</h3>
        <div class="box-tools pull-right">
            <?php if ($ENABLE_ADD) : ?>
                <a class="btn btn-success btn-sm" href="javascript:void(0)" title="Tambah" onclick="data_add()"><i class="fa fa-plus"></i>&nbsp;Tambah</a>
            <?php endif; ?>
        </div>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <div class="table-responsive">
            <table id="mytabledata" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="40">No</th>
                        <th>Nama</th>
                        <th>Keterangan</th>
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

<div id="form-data"></div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- page script -->
<script type="text/javascript">
    $(document).ready(function() {
        DataTables();
    });

    /**
     * Initialize server-side DataTable
     */
    function DataTables() {
        $('#mytabledata').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            stateSave: true,
            ajax: {
                url: siteurl + 'petty_cash_master/get_data',
                type: 'POST'
            },
            columns: [{
                    data: 'no',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'nama'
                },
                {
                    data: 'keterangan'
                },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false
                }
            ]
        });
    }

    /**
     * Load create form via AJAX into #form-data
     */
    function data_add() {
        $.ajax({
            type: 'GET',
            url: siteurl + 'petty_cash_master/create',
            beforeSend: function() {
                $('#form-data').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
            },
            success: function(response) {
                $('#form-data').html(response);
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Gagal menghubungi server',
                    timer: 3000
                });
            }
        });
    }

    /**
     * Load edit form via AJAX into #form-data
     * @param {int} id - Master petty cash ID
     */
    function data_edit(id) {
        $.ajax({
            type: 'GET',
            url: siteurl + 'petty_cash_master/edit/' + id,
            beforeSend: function() {
                $('#form-data').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
            },
            success: function(response) {
                $('#form-data').html(response);
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Gagal menghubungi server',
                    timer: 3000
                });
            }
        });
    }

    /**
     * Load view (read-only) form via AJAX into #form-data
     * @param {int} id - Master petty cash ID
     */
    function data_view(id) {
        $.ajax({
            type: 'GET',
            url: siteurl + 'petty_cash_master/view/' + id,
            beforeSend: function() {
                $('#form-data').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
            },
            success: function(response) {
                $('#form-data').html(response);
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Gagal menghubungi server',
                    timer: 3000
                });
            }
        });
    }

    /**
     * Delete master petty cash with SweetAlert2 confirmation
     * @param {int} id - Master petty cash ID
     */
    function data_delete(id) {
        Swal.fire({
            title: 'Apakah anda yakin?',
            text: 'Data yang sudah dihapus tidak dapat dikembalikan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!'
        }).then(function(result) {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'POST',
                    url: siteurl + 'petty_cash_master/delete/' + id,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status == '1') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.msg,
                                timer: 3000,
                                showConfirmButton: false
                            }).then(function() {
                                DataTables();
                            });
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Gagal!',
                                text: response.msg,
                                timer: 3000
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Gagal menghubungi server',
                            timer: 3000
                        });
                    }
                });
            }
        });
    }
</script>