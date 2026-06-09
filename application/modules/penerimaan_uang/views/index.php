<link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" integrity="sha512-yVvxUQV0QESBt1SyZbNJMAwyKvFTLMyXSyBHDO4BG5t7k/Lw34tyqlSDlKIrIENIzCl+RVUNjmCPG+V/GMesRw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<style>
    .form-inline {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    body {
        background-color: #f5f5f5;
        padding-top: 20px;
    }

    .main-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 30px;
        margin-bottom: 30px;
    }

    .form-inline .form-group {
        margin-right: 15px;
        margin-bottom: 10px;
    }

    .form-inline .form-group label {
        margin-right: 8px;
        font-weight: 600;
        color: #555;
    }

    .form-inline .form-control {
        border-radius: 4px;
        border: 1px solid #ddd;
        padding: 8px 12px;
        font-size: 14px;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-inline .form-control:focus {
        border-color: #66afe9;
        outline: 0;
        box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075), 0 0 8px rgba(102, 175, 233, .6);
    }

    .btn {
        border-radius: 6px;
        font-weight: 500;
        margin-right: 8px;
        transition: all 0.2s ease-in-out;
    }

    .btn-primary {
        background-color: #337ab7;
        border-color: #2e6da4;
    }

    .btn-primary:hover {
        background-color: #286090;
        border-color: #204d74;
    }

    .btn-success {
        background-color: #5cb85c;
        border-color: #4cae4c;
    }

    .btn-success:hover {
        background-color: #449d44;
        border-color: #398439;
    }

    .form-control-sm {
        height: 34px;
        padding: 6px 12px;
        font-size: 14px;
    }

    .search-section {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .search-section h4 {
        color: #495057;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .date-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .date-input-wrapper {
        display: flex;
        align-items: center;
        margin-right: 15px;
    }

    .date-input-wrapper label {
        margin-right: 8px;
        white-space: nowrap;
    }

    @media (max-width: 768px) {
        .form-inline .form-group {
            display: block;
            margin-bottom: 15px;
        }

        .date-group {
            flex-direction: column;
            align-items: stretch;
        }

        .date-input-wrapper {
            margin-bottom: 10px;
        }
    }
</style>

<div class="box">
    <div class="box-header">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <select name="bank" class="form-control form-control-sm bank">
                        <option value="">- Pilih Bank -</option>
                        <?php
                        foreach ($list_bank as $item_bank) :
                            echo '<option value="' . $item_bank['id'] . '">' . $item_bank['nama_bank'] . ' - ' . $item_bank['rekening'] . ' - ' . $item_bank['nama'] . '</option>';
                        endforeach;
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-primary search">
                    <i class="fa fa-search"></i> Search
                </button>
            </div>
        </div>
    </div>
    <div class="box-body">
        <table class="table table-striped" id="table_list">
            <thead>
                <tr>
                    <th class="text-center">No.</th>
                    <th class="text-center">Tanggal Transaksi</th>
                    <th class="text-center">Reference No.</th>
                    <th class="text-center">Bank</th>
                    <th class="text-center">Keterangan</th>
                    <th class="text-center">Nominal</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>
<div class="modal" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="width: 100% !important;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"><span class="fa fa-money"></span>&nbsp;Penerimaan Piutang</h4>
            </div>
            <form action="" id="frm_data" enctype="multipart/form-data">
                <div class="modal-body" id="MyModalBody">

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success btn_proses"><i class="fa fa-check"></i> Proses</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">
                        <span class="glyphicon glyphicon-remove"></span> Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        DataTables();

        $('.bank').chosen({
            width: '100%'
        });
        $('.search_bank').chosen({
            width: '450px'
        });
    });

    $(document).on('click', '.search', function() {
        var bank = $('.bank').val();

        DataTables(bank);
    });

    $(document).on('click', '.detail', function() {
        var id_alokasi = $(this).data('id');

        $.ajax({
            type: 'post',
            url: siteurl + active_controller + 'detail',
            data: {
                id_alokasi: id_alokasi
            },
            cache: false,
            success: function(result) {
                $('#MyModalBody').html(result);
                $('#dialog-popup').modal('show');

                $('.btn_proses').hide();

                $('.autonum').autoNumeric('init');
            },
            error: function(result) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error !',
                    text: 'Please try again later !',
                    type: 'error',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    showCancelButton: false,
                    timer: 3000
                });
            }
        });
    });

    $(document).on('click', '.rollback', function() {
        var id_penerimaan = $(this).data('id');

        Swal.fire({
            title: 'Apakah Anda Yakin?',
            text: "Data penerimaan piutang ini akan dihapus dan dibatalkan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Rollback!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'post',
                    url: siteurl + active_controller + 'rollback_penerimaan',
                    data: {
                        id_penerimaan: id_penerimaan
                    },
                    dataType: 'json',
                    cache: false,
                    success: function(result) {
                        if (result.status == 1) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: result.msg,
                                icon: 'success',
                                timer: 3000
                            }).then(function() {
                                DataTables();
                            });
                        } else {
                            Swal.fire({
                                title: 'Gagal!',
                                text: result.msg,
                                icon: 'error',
                                timer: 3000
                            });
                        }
                    },
                    error: function(result) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Gagal saat memproses rollback. Silahkan coba lagi.',
                            icon: 'error',
                            timer: 3000
                        });
                    }
                });
            }
        });
    });

    function DataTables(bank = null) {
        var DataTables = $('#table_list').dataTable({
            serverSide: true,
            process: true,
            stateSave: true,
            destroy: true,
            paging: true,
            ajax: {
                type: 'post',
                url: siteurl + active_controller + 'get_alokasi_penerimaan',
                dataType: 'json',
                data: function(d) {
                    d.bank = bank;
                }
            },
            columns: [{
                    data: 'no'
                },
                {
                    data: 'tgl_transaksi_bank'
                },
                {
                    data: 'reference_no'
                },
                {
                    data: 'bank'
                },
                {
                    data: 'keterangan'
                },
                {
                    data: 'nominal'
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