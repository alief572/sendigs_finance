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
        <div class="col-md-2">
            <div class="form-inline">
                <div class="form-group">
                    <label for="startDate2">Start:</label>
                    <input type="date" class="form-control form-control-sm" id="startDate2" name="startDate2" style="width: auto !important;">
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-inline">
                <div class="form-group">
                    <label for="endDate2">End:</label>
                    <input type="date" class="form-control form-control-sm" id="endDate2" name="endDate2" style="width: auto !important;">
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="">Bank</label>
                <select class="form-control form-control-sm search_bank">
                    <option value="">- Bank -</option>
                    <?php foreach ($data_bank as $bank) : ?>
                        <option value="<?= $bank['id'] ?>"><?= $bank['nama_bank'] . ' - ' . $bank['rekening'] . ' - ' . $bank['nama'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="col-md-4 text-center">
            <button type="button" class="btn btn-sm btn-primary search_data"><i class="fa fa-search"></i> Search</button>
            <button type="button" class="btn btn-sm btn-danger clear_data"><i class="fa fa-refresh"></i> Reset</button>
        </div>
    </div>
    <div class="box-body">
        <table class="table table-bordered" id="table_list">
            <thead>
                <tr>
                    <th class="text-center" width="100">No</th>
                    <th class="text-center" width="100">Tanggal Transaksi</th>
                    <th class="text-center" width="100">Reference No</th>
                    <th class="text-center" width="90">Bank</th>
                    <th class="text-center" width="90">Keterangan</th>
                    <th class="text-center" width="120">Nominal</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>
<div class="modal" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"><span class="fa fa-money"></span>&nbsp;Upload Rekening Koran</h4>
            </div>
            <form action="" id="frm_data" enctype="multipart/form-data">
                <div class="modal-body" id="MyModalBody">
                    <div class="form-group">
                        <label for="">Bank</label>
                        <select name="bank" id="" class="form-control form-control-sm bank" required>
                            <option value="">- Bank -</option>
                            <?php foreach ($data_bank as $bank) : ?>
                                <option value="<?= $bank['id'] ?>"><?= $bank['nama_bank'] . ' - ' . $bank['rekening'] . ' - ' . $bank['nama'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="jenis_bank">
                    </div>
                    <div class="form-group">
                        <label for="">Upload File CSV</label>
                        <input type="file" name="upload_csv" id="" class="form-control form-control-sm" accept=".csv" required>
                    </div>
                    <br><br>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center" width="16.7%">Tanggal Transaksi</th>
                                <th class="text-center" width="16.7%">Reference No</th>
                                <th class="text-center" width="16.7%">Description</th>
                                <th class="text-center" width="16.7%">Credit</th>
                                <th class="text-center" width="16.7%">Debit</th>
                                <th class="text-center" width="16.7%">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="list_alokasi_bank">

                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Proses</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">
                        <span class="glyphicon glyphicon-remove"></span> Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal" id="dialog-popup-alokasi" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"><span class="fa fa-money"></span>&nbsp;Alokasi</h4>
            </div>
            <form action="" id="frm_data_alokasi" enctype="multipart/form-data">
                <input type="hidden" name="id">
                <div class="modal-body" id="MyModalBody">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%">
                            <thead>
                                <tr>
                                    <th class="text-center" width="100">No</th>
                                    <th class="text-center" width="100">Tanggal Transaksi</th>
                                    <th class="text-center" width="100">Reference No</th>
                                    <th class="text-center" width="90">Bank</th>
                                    <th class="text-center" width="90">Keterangan</th>
                                    <th class="text-center" width="120">Nominal</th>
                                </tr>
                            </thead>
                            <tbody class="list_balance_alokasi">

                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Proses</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">
                        <span class="glyphicon glyphicon-remove"></span> Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
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

    $(document).on('submit', '#frm_data', function(e) {
        e.preventDefault();
        swal({
            type: 'warning',
            title: 'Warning !',
            text: 'File rekening akan di upload !',
            showCancelButton: true
        }, function(next) {
            if (next) {
                var formdata = new FormData($('#frm_data')[0]);

                $.ajax({
                    type: 'post',
                    url: siteurl + active_controller + 'upload_rekening_koran',
                    data: formdata,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    cache: false,
                    success: function(result) {
                        if (result.status == '1') {
                            swal({
                                type: 'success',
                                title: 'Success !',
                                text: result.msg,
                                timer: 3000
                            }, function(lanjut) {
                                location.reload();
                            });
                        } else {
                            swal({
                                type: 'warning',
                                title: 'Warning !',
                                text: result.msg
                            });
                        }
                    },
                    error: function(result) {
                        swal({
                            type: 'error',
                            title: 'Error !',
                            text: 'Please try again later !'
                        });
                    }
                })
            }
        });
    });

    $(document).on('submit', '#frm_data_alokasi', function(e) {
        e.preventDefault();

        swal({
            type: 'warning',
            title: 'Warning !',
            text: 'This data will be saved !',
            showCancelButton: true
        }, function(next) {
            if (next) {
                var formdata = $('#frm_data_alokasi').serialize();

                $.ajax({
                    type: 'post',
                    url: siteurl + active_controller + 'save_alokasi',
                    data: formdata,
                    dataType: 'json',
                    cache: false,
                    success: function(result) {
                        if (result.status == '1') {
                            swal({
                                type: 'success',
                                title: 'Success !',
                                text: result.msg,
                                allowOutsideClick: false,
                                confirmButtonShow: false
                            }, function(lanjut) {
                                location.reload();
                            });
                        } else {
                            swal({
                                type: 'warning',
                                title: 'Warning !',
                                text: result.msg
                            });
                        }
                    },
                    error: function(result) {
                        swal({
                            type: 'error',
                            title: 'Error !',
                            text: 'Please try again later !'
                        });
                    }
                });
            }
        });
    });

    $(document).on('change', '.bank', function() {
        var bank = $(this).val();

        $.ajax({
            type: 'post',
            url: siteurl + active_controller + 'get_bank_sesuai_alokasi',
            data: {
                'bank': bank
            },
            cache: false,
            success: function(result) {
                $('.list_alokasi_bank').html(result);
            },
            error: function(result) {
                swal({
                    type: 'error',
                    title: 'Error !',
                    text: 'Please try again later !'
                });
            }
        });

        $.ajax({
            type: 'post',
            url: siteurl + active_controller + 'get_jenis_bank',
            data: {
                'bank': bank
            },
            dataType: 'json',
            cache: false,
            success: function(result) {
                $('input[name="jenis_bank"]').val(result.jenis_bank);
            }
        });
    });

    $(document).on('click', '.btn_alokasi', function(e) {
        e.preventDefault();

        var id = $(this).data('id');

        $.ajax({
            type: 'post',
            url: siteurl + active_controller + 'get_alokasi_detail',
            data: {
                'id': id
            },
            dataType: 'json',
            cache: false,
            success: function(result) {
                var vit = '';
                result.data.forEach(function(item) {
                    vit += '<tr>';
                    vit += '<td class="text-center" style="font-size: 12px;">' + item.tanggal_transaksi + '</td>';
                    vit += '<td class="text-center" style="font-size: 12px;">' + item.reference_no + '</td>';
                    vit += '<td class="text-center" style="font-size: 12px;">' + item.description + '</td>';
                    vit += '<td class="text-right" style="font-size: 12px;">Rp. ' + item.credit + '</td>';
                    vit += '<td class="text-right" style="font-size: 12px;">Rp. ' + item.debit + '</td>';
                    vit += '<td class="text-right" style="font-size: 12px;">Rp. ' + item.balance + '</td>';
                    vit += '<td class="text-left" style="font-size: 12px;">' + item.action + '</td>';
                    vit += '</tr>';
                });
                $('input[name="id"]').val(result.id);
                $('.list_balance_alokasi').html(vit);

                $('#dialog-popup-alokasi').modal('show');
            },
            error: function(result) {
                swal({
                    type: 'error',
                    title: 'Error !',
                    text: 'Please try again later !'
                });
            }
        });
    });

    $(document).on('click', '.search_data', function() {
        var startDate = $('#startDate2').val();
        var endDate = $('#endDate2').val();
        var bank = $('.search_bank').val();

        DataTables(startDate, endDate, bank);
    });

    $(document).on('click', '.clear_data', function() {
        $('#startDate2').val('');
        $('#endDate2').val('');
        $('.search_bank').val('');

        $('.search_bank').trigger('chosen:updated');

        DataTables();
    });

    $(document).on('click', '.btn_print', function() {
        var start_date = $('#startDate2').val();
        var end_date = $('#endDate2').val();
        var bank = $('.search_bank').val();

        window.open(siteurl + active_controller + 'printAlokasi?start=' + start_date + '&end' + end_date + '&bank=' + bank, '_blank');
    });

    function DataTables(startDate = null, endDate = null, bank = null) {
        var DataTables = $('#table_list').dataTable({
            serverSide: true,
            process: true,
            stateSave: true,
            destroy: true,
            paging: true,
            ajax: {
                type: 'post',
                url: siteurl + active_controller + 'get_alokasi',
                dataType: 'json',
                data: function(d) {
                    d.startDate = startDate;
                    d.endDate = endDate;
                    d.bank = bank;
                }
            },
            columns: [{
                    data: 'no'
                },
                {
                    data: 'tanggal_transaksi'
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
                }
            ]
        });
    }
</script>