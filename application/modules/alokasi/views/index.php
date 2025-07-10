<link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css">
<div class="box">
    <div class="box-header">
        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#dialog-popup">Upload Rekening Koran</button>
    </div>
    <div class="box-body">
        <table class="table table-bordered" id="table_list">
            <thead>
                <tr>
                    <th class="text-center">No.</th>
                    <th class="text-center">Tanggal Transaksi Bank</th>
                    <th class="text-center">Bank</th>
                    <th class="text-center">Total Debit</th>
                    <th class="text-center">Total Credit</th>
                    <th class="text-center">Saldo Akhir</th>
                    <th class="text-center">Status Alokasi</th>
                    <th class="text-center">Action</th>
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
                            <option value="1">BCA</option>
                            <option value="2">OCBC</option>
                        </select>
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
                                    <th class="text-center" width="100">Tanggal Transaksi</th>
                                    <th class="text-center" width="100">Reference No</th>
                                    <th class="text-center" width="90">Description</th>
                                    <th class="text-center" width="120">Credit</th>
                                    <th class="text-center" width="120">Debit</th>
                                    <th class="text-center" width="120">Balance</th>
                                    <th class="text-center" width="200">Action</th>
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
<script>
    $(document).ready(function() {
        DataTables();
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

    function DataTables() {
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

                }
            },
            columns: [{
                    data: 'no'
                },
                {
                    data: 'tanggal_transaksi_bank'
                },
                {
                    data: 'bank'
                },
                {
                    data: 'total_debit'
                },
                {
                    data: 'total_kredit'
                },
                {
                    data: 'saldo_akhir'
                },
                {
                    data: 'status_alokasi'
                },
                {
                    data: 'action'
                }
            ]
        });
    }
</script>