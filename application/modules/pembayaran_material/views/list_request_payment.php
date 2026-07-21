<link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css">
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<div class="box">
    <div class="box-header">
        <a href="<?= base_url('pembayaran_material/payment_list') ?>" class="btn btn-sm btn-danger"><i class="fa fa-arrow-left"></i> Back</a>
        <button type="button" class="btn btn-sm btn-warning clear_choosed_payment"><i class="fa fa-refresh"></i> Clear Checked Payment</button>
        <button type="button" class="btn btn-sm btn-success proses_payment"><i class="fa fa-check"></i> Proses</button>
    </div>
    <div class="box-body">
        <table class="table table-bordered" id="table_list_req_payment">
            <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">No. Dokumen</th>
                    <th class="text-center">Tgl</th>
                    <th class="text-center">Keperluan</th>
                    <th class="text-center">Currency</th>
                    <th class="text-center">Total Invoice</th>
                    <th class="text-center">Requestor</th>
                    <th class="text-center">Option</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>

<style>
    .modal-admin-charge .modal-content {
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }
    .modal-admin-charge .modal-header {
        border-bottom: 1px solid #eee;
        background-color: #f8f9fa;
        border-radius: 10px 10px 0 0;
        padding: 15px 20px;
    }
    .modal-admin-charge .modal-header .close {
        color: #333;
        opacity: 0.7;
    }
    .modal-admin-charge .modal-title {
        font-weight: 600;
        color: #333;
        margin: 0;
    }
    .modal-admin-charge .modal-title i {
        margin-right: 8px;
    }
    .modal-admin-charge .modal-body {
        padding: 20px;
    }
    .modal-admin-charge .validation-msg {
        border-radius: 6px;
        padding: 10px;
        margin-bottom: 15px;
        font-size: 13px;
    }
    .modal-admin-charge .form-group {
        margin-bottom: 0;
    }
    .modal-admin-charge label {
        font-weight: 500;
        color: #555;
        margin-bottom: 8px;
    }
    .modal-admin-charge select.form-control {
        border-radius: 6px;
        border: 1px solid #ccc;
        padding: 8px 12px;
        height: auto;
        font-size: 14px;
        box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
    }
    .modal-admin-charge .modal-footer {
        border-top: 1px solid #eee;
        padding: 15px 20px;
        background-color: #fcfcfc;
        border-radius: 0 0 10px 10px;
    }
    .modal-admin-charge .btn {
        border-radius: 6px;
        padding: 6px 16px;
        font-weight: 500;
    }
    .modal-admin-charge .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
    }
</style>

<!-- Modal Admin Charge -->
<div class="modal fade modal-admin-charge" id="modalAdminCharge" tabindex="-1" role="dialog" aria-labelledby="modalAdminChargeLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modalAdminChargeLabel">
                    <i class="fa fa-info-circle text-primary"></i> Pilih Penanggung Admin Charge
                </h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger validation-msg" style="display:none;">
                    <i class="fa fa-exclamation-triangle"></i> Silakan pilih salah satu opsi penanggung admin charge.
                </div>
                <div class="form-group">
                    <label for="admin_charge_bearer">Penanggung Admin Charge</label>
                    <select name="admin_charge_bearer" id="admin_charge_bearer" class="form-control">
                        <option value="">- Pilih Penanggung -</option>
                        <option value="company">Admin charge ditanggung perusahaan</option>
                        <option value="recipient">Admin charge ditanggung penerima</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary btn-confirm-bearer">
                    <i class="fa fa-check"></i> Konfirmasi
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>

<!-- page script -->
<script>
    DataTables();

    var arr_choosed_payment = '';

    function check_choosed_payment() {
        return $.ajax({
            type: "POST",
            url: siteurl + active_controller + 'proses_payment',
            cache: false,
            dataType: 'json'
        });
    }

    $(document).on('click', '.check_payment', function() {
        var val = $(this).val();

        var checked = 0;
        if ($(this).is(':checked')) {
            checked = 1;
        }

        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'check_payment',
            data: {
                'id': val,
                'checked': checked
            },
            cache: false,
            success: function(result) {

            },
            error: function(result) {
                swal({
                    title: 'Error !',
                    text: 'Please try again later !',
                    type: 'error'
                });
            }
        });
    });

    $(document).on('click', '.clear_choosed_payment', function() {
        swal({
                title: "Are you sure?",
                text: "Your choosed payment data will be cleared!",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Yes, Clear it!",
                cancelButtonText: "No, cancel process!",
                closeOnConfirm: true,
                closeOnCancel: false
            },
            function(isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        url: siteurl + active_controller + 'clear_choosed_payment',
                        type: "POST",
                        cache: false,
                        dataType: 'json',
                        success: function(data) {
                            swal.close();
                            DataTables();
                        },
                        error: function() {
                            swal({
                                title: 'Error !',
                                text: 'Please try again later !',
                                type: 'error'
                            });
                        }
                    });
                } else {
                    swal("Cancelled", "Data can be process again :)", "error");
                    return false;
                }
            });
    });

    $(document).on('click', '.proses_payment', function() {

        check_choosed_payment().done(function(data) {
            var choosed_payment = data.count_choosed_payment;

            if (choosed_payment >= 1) {
                // Store payment IDs and show modal instead of direct redirect
                arr_choosed_payment = data.arr_choosed_payment;

                // Reset modal state: clear select and hide validation message
                $('#admin_charge_bearer').val('');
                $('.validation-msg').hide();

                $('#modalAdminCharge').modal('show');
            } else {
                swal({
                    title: 'Warning !',
                    text: 'Please check at least 1 payment data !',
                    type: 'warning'
                });
            }
        }).fail(function(data) {
            swal({
                title: 'Error !',
                text: 'Proses gagal. Silakan coba lagi nanti !',
                type: 'error'
            });
        });
    });

    // Confirm bearer selection and redirect
    $(document).on('click', '.btn-confirm-bearer', function() {
        var selected = $('#admin_charge_bearer').val();

        if (!selected) {
            $('.validation-msg').show();
            return;
        }

        $('.validation-msg').hide();
        window.location.href = siteurl + active_controller + 'form_payment_new/?id_payment=' + arr_choosed_payment + '&admin_charge_bearer=' + selected;
    });

    function DataTables() {
        var DataTables = $('#table_list_req_payment').dataTable({
            serverSide: true,
            processing: true,
            destroy: true,
            paging: true,
            stateSave: true,
            ajax: {
                type: 'post',
                url: siteurl + active_controller + 'get_list_req_payment',
                dataType: 'json',
                data: function(d) {
                    d.jenis_payment = '<?= $jenis_payment ?>';
                }
            },
            columns: [{
                    data: 'no'
                },
                {
                    data: 'no_dokumen'
                },
                {
                    data: 'tgl'
                },
                {
                    data: 'keperluan'
                },
                {
                    data: 'currency'
                },
                {
                    data: 'total_invoice'
                },
                {
                    data: 'requestor'
                },
                {
                    data: 'option'
                }
            ]
        });
    }
</script>