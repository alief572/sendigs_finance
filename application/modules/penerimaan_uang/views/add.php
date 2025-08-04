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
    </div>
    <div class="box-body">
        <div class="row">
            <form action="" method="post" id="form_data">
                <input type="hidden" name="id_alokasi" value="<?= $results['id_alokasi'] ?>">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="customer">Nama Customer <span class="text-red">*</span></label>
                        <select class="form-control form-control-sm" name="customer" id="customer">
                            <option value="">- Pilih Customer -</option>
                            <?php
                            foreach ($results['list_customer'] as $item) {
                                echo '<option value="' . $item['id_customer'] . '">' . $item['nm_customer'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="pph23_dipotong">PPH23 Dipotong <span class="text-red">*</span></label>
                        <select class="form-control form-control-sm" name="pph23_dipotong" id="pph23_dipotong">
                            <option value="1">Ya</option>
                            <option value="2">Tidak</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="form-group">
                            <label for="customer">PPN Dipotong <span class="text-red">*</span></label>
                            <select class="form-control form-control-sm" name="ppn_dipotong" id="ppn_dipotong">
                                <option value="1">Ya</option>
                                <option value="2">Tidak</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="nominal_penerimaan_bank">Nominal Penerimaan Bank <span class="text-red">*</span></label>
                        <input type="text" name="nominal_penerimaan_bank" id="nominal_penerimaan_bank" class="form-control form-control-sm text-right" value="<?= $results['nominal_penerimaan_bank'] ?>" readonly>
                    </div>
                </div>

                <br><br>

                <div class="col-md-12">
                    <table class="table table-striped table_list">
                        <thead>
                            <tr>
                                <th class="text-center">Tgl Inv</th>
                                <th class="text-center">No Invoice</th>
                                <th class="text-center">Customer</th>
                                <th class="text-center">DPP</th>
                                <th class="text-center">PPN</th>
                                <th class="text-center">PPH 23</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Saldo Piutang</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="tbody_list">
                            <tr>
                                <td colspan="9" class="text-center">No Data Found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <br><br>

                <div class="col-md-12">
                    <a href="<?= base_url('penerimaan_uang') ?>" class="btn btn-sm btn-danger">
                        <i class="fa fa-arrow-left"></i> Back
                    </a>
                    <button type="submit" class="btn btn-sm btn-success btn_submit_process">
                        <i class="fa fa-refresh"></i> Process
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"><span class="fa fa-money"></span>&nbsp;Penerimaan Piutang</h4>
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

<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    $(document).ready(function() {
        $('#customer').chosen({
            width: '100%'
        });
        $('#ppn_dipotong').chosen({
            width: '100%'
        });
        $('#pph23_dipotong').chosen({
            width: '100%'
        });

        $('#nominal_penerimaan_bank').autoNumeric('init');
    });

    $(document).on('change', '#customer', function() {
        var id_customer = $(this).val();

        if (id_customer !== '') {
            $.ajax({
                type: 'post',
                url: siteurl + active_controller + 'get_inv_by_cust',
                data: {
                    'id': id_customer
                },
                cache: false,
                success: function(result) {
                    $('.tbody_list').html(result);
                },
                error: function(result) {
                    swal({
                        type: 'error',
                        title: 'Error !',
                        text: 'Please try again later !',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        timer: 3000
                    });
                }
            });
        } else {
            var hasil = '<tr>';
            hasil += '<td colspan="9" class="text-center">No Data Found</td>';
            hasil += '</tr>';
        }
    });

    $(document).on('submit', '#form_data', function(e) {

        e.preventDefault();

        var customer = $('#customer').val();
        var pph23_dipotong = $('#pph23_dipotong').val();
        var ppn_dipotong = $('#ppn_dipotong').val();
        var nominal_penerimaan_bank = $('#nominal_penerimaan_bank').val();

        var check_data = $('input[name="choose_inv\\[\\]"]:checked').length;
        if (check_data < 1) {
            swal({
                type: 'warning',
                title: 'Warning !',
                text: 'Pilih data yang akan diproses !',
                showConfirmButton: false,
                allowOutsideClick: false,
                timer: 3000
            });

            return false;
        }

        if (customer == '') {
            swal({
                type: 'warning',
                title: 'Warning !',
                text: 'Nama Customer masih kosong !',
                showConfirmButton: false,
                allowOutsideClick: false,
                timer: 3000
            });

            return false;
        }
        if (pph23_dipotong == '') {
            swal({
                type: 'warning',
                title: 'Warning !',
                text: 'Pilihan PPH23 Dipotong masih kosong !',
                showConfirmButton: false,
                allowOutsideClick: false,
                timer: 3000
            });

            return false;
        }
        if (ppn_dipotong == '') {
            swal({
                type: 'warning',
                title: 'Warning !',
                text: 'Pilihan PPN Dipotong masih kosong !',
                showConfirmButton: false,
                allowOutsideClick: false,
                timer: 3000
            });

            return false;
        }

        var formData = $(this).serialize();

        $.ajax({
            type: 'post',
            url: siteurl + active_controller + 'process_alokasi',
            data: formData,
            cache: false,
            success: function(result) {
                $('#MyModalBody').html(result);
                $('#dialog-popup').modal('show');

                $('.autonum').autoNumeric('init');
            },
            error: function(result) {
                swal({
                    type: 'error',
                    title: 'Error !',
                    text: 'Please try again later !',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    timer: 3000
                });
            }
        });
    });

    $(document).on('submit', '#frm_data', function(e) {
        e.preventDefault();

        var kontrol = get_num($('input[name="kontrol"]').val());

        if (kontrol > 0) {
            swal({
                type: 'warning',
                title: 'Warning !',
                text: 'Kontrol tidak boleh lebih dari 0 !',
                showConfirmButton: false,
                allowOutsideClick: false,
                timer: 3000
            });

            return false;
        }

        swal({
            type: 'warning',
            title: 'Are you sure ?',
            text: 'Data yang sudah diproses tidak dapat diubah !',
            showConfirmButton: true,
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
            confirmButtonColor: '#3085d6',
        }, function(next) {
            if (next) {
                var data = $('#frm_data').serialize();
                $.ajax({
                    type: 'post',
                    url: siteurl + active_controller + 'save_penerimaan_piutang',
                    data: data,
                    cache: false,
                    dataType: 'json',
                    success: function(result) {
                        if (result.status == '1') {
                            swal({
                                type: 'success',
                                title: 'Success !',
                                text: result.message,
                                showConfirmButton: false,
                                allowOutsideClick: false,
                                timer: 3000
                            }, function(lanjut) {
                                window.location.href = siteurl + active_controller;
                            });
                        } else {
                            swal({
                                type: 'warning',
                                title: 'Warning !',
                                text: result.message,
                                showConfirmButton: false,
                                allowOutsideClick: false,
                                timer: 3000
                            });
                        }
                    },
                    error: function(result) {
                        swal({
                            type: 'error',
                            title: 'Error !',
                            text: 'Please try again later !',
                            showConfirmButton: false,
                            allowOutsideClick: false,
                            timer: 3000
                        });
                    }
                });
            }
        });
    });

    function get_num(nilai = null) {
        if (nilai !== '' && nilai !== null) {
            nilai = nilai.split(',').join('');
            nilai = parseFloat(nilai);
        } else {
            nilai = 0;
        }

        return nilai;
    }
</script>