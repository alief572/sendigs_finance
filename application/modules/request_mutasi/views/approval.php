<?php if (!empty($data)) {
    $numb = 0;
    foreach ($data as $record) {
        $numb++;
        $kd_mutasi = $record->kd_mutasi;
        $keterangan = $record->keterangan;
        $dari = $record->bank_asal;
        $darinama = $record->nama_bank_asal;
        $ke = $record->bank_tujuan;
        $kenama = $record->nama_bank_tujuan;
        $nilai = $record->nilai_request;
        $tanggal = $record->tgl_request;
        $matauang = $record->mata_uang;

        $numb++;
    }
}
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<div class="box box-primary">
    <form method="post" id="frm_data" class="form-horizontal">
        <div class="box-body">
            <div class="col-sm-6">
                <div class="row">
                    <div class="form-group ">
                        <label for="tgl_bayar" class="col-sm-4 control-label">No Request</label>
                        <div class="col-sm-6">
                            <input type="text" name="no_request" id="no_request" value="<?= $kd_mutasi ?>" placeholder="Automatic" class="form-control input-sm" readonly>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group">
                        <label for="ket_bayar" class="col-sm-4 control-label">Keterangan</label>
                        <div class="col-sm-6">
                            <textarea name="keterangan" class="form-control input-sm" id="keterangan" readonly><?= $keterangan ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group ">
                        <label for="tgl_bayar" class="col-sm-4 control-label">Mata Uang</label>
                        <div class="col-sm-6">
                            <input type="text" name="kurs" class="form-control input-sm" id="kurs" value="<?= $matauang ?>" onblur="total()" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <table class="table table-bordered table-striped" width='100%'>
                    <thead class="bg-blue" align="center">
                        <tr>
                            <td>Tanggal</td>
                            <td>Dari</td>
                            <td>Ke</td>
                            <td>Nilai Request</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <input type="hidden" name="tgl_request" class="form-control input-sm" id="tgl_request" value="<?= $tanggal ?>" readonly>
                                <input type="date" name="tgl" class="form-control input-sm" id="tgl" value="<?= $tanggal ?>" readonly>
                            </td>
                            <td>
                                <input type="hidden" name="dari" class="form-control input-sm" id="dari" value="<?= $dari ?>" readonly>
                                <input type="text" name="darinama" class="form-control input-sm" id="darinama" value="<?= $darinama ?>" readonly>
                            </td>
                            <td>
                                <input type="hidden" name="ke" class="form-control input-sm" id="ke" value="<?= $ke ?>" readonly>
                                <input type="text" name="kenama" class="form-control input-sm" id="kenama" value="<?= $kenama ?>" readonly>
                            </td>
                            <td>
                                <input type="text" name="nilai" class="form-control input-sm" id="nilai" value="<?= number_format($nilai) ?>" onblur="total()" readonly>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="col-md-12 text-right">
                <a class="btn btn-warning" onclick="history.back(); return false;"><i class="fa fa-reply"></i><b> Batal</b></a>
                <button id="reject" class="btn btn-danger reject" type="button">
                    <i class="fa fa-save"></i><b> Reject Request</b>
                </button>
                <button class="btn btn-primary approve" type="button">
                    <i class="fa fa-save"></i><b> Approve Request</b>
                </button>
            </div>
        </div>
    </form>
</div>

<div class="modal modal-default fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="width: 50%;">
        <div class="modal-content">
            <form method="post" id="frm-reject" class="form-horizontal">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel"><i class="fa fa-ban"></i>&nbsp;Alasan Reject</h4>
                </div>
                <div class="modal-body" id="ModalView">
                    <div class="box-primary">
                        <div class="box-body">
                            <div class="row">
                                <div class="form-group">
                                    <label for="tgl_bayar" class="col-sm-4 control-label">No Request</label>
                                    <div class="col-sm-6">
                                        <input type="text" name="no_request" id="no_request" value="<?= $kd_mutasi ?>" placeholder="Automatic" class="form-control input-sm" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group">
                                    <label for="ket_bayar" class="col-sm-4 control-label">Alasan Reject</label>
                                    <div class="col-sm-6">
                                        <textarea name="alasan" class="form-control input-sm" id="alasan"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-warning reject_request" type="button">
                        <i class="fa fa-ban"></i> Reject</b>
                    </button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">
                        <span class="glyphicon glyphicon-remove"></span> Tutup</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>
<script>
    $(document).ready(function() {
        $(document).on('click', '.reject_request', function() {
            if ($('#alasan').val() == "") {
                swal({
                    title: "Alasan Tidak Boleh Kosong!",
                    text: "Isi Alasan Terlebih Dahulu!",
                    type: "warning",
                    timer: 3000,
                    showCancelButton: false,
                    showConfirmButton: false,
                    allowOutsideClick: false
                });
            } else {
                swal({
                        title: "Peringatan !",
                        text: "Pastikan data sudah lengkap dan benar",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "Ya, simpan!",
                        cancelButtonText: "Batal!",
                        closeOnConfirm: false,
                        closeOnCancel: true
                    },
                    function(isConfirm) {
                        if (isConfirm) {
                            var formdata = $("#frm-reject").serialize();
                            $.ajax({
                                url: base_url + "request_mutasi/reject_mutasi",
                                dataType: "json",
                                type: 'POST',
                                data: formdata,
                                success: function(data) {
                                    if (data.status == 1) {
                                        swal({
                                            title: "Reject Success!",
                                            text: data.pesan,
                                            type: "success",
                                            timer: 15000,
                                            showCancelButton: false,
                                            showConfirmButton: false,
                                            allowOutsideClick: false
                                        });
                                        window.location.href = base_url + active_controller + 'approval_mutasi';
                                    } else {

                                        if (data.status == 2) {
                                            swal({
                                                title: "Reject Failed!",
                                                text: data.pesan,
                                                type: "warning",
                                                timer: 10000,
                                                showCancelButton: false,
                                                showConfirmButton: false,
                                                allowOutsideClick: false
                                            });
                                        } else {
                                            swal({
                                                title: "Reject Failed!",
                                                text: data.pesan,
                                                type: "warning",
                                                timer: 10000,
                                                showCancelButton: false,
                                                showConfirmButton: false,
                                                allowOutsideClick: false
                                            });
                                        }

                                    }
                                },
                                error: function() {
                                    swal({
                                        title: "Gagal!",
                                        text: "Batal Proses, Data bisa diproses nanti",
                                        type: "error",
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                }
                            });
                        }
                    });
            }
        })

        $(document).on('click', '.approve', function() {
            swal({
                    title: "Peringatan !",
                    text: "Pastikan data sudah lengkap dan benar",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#00a65a",
                    confirmButtonText: "Ya, simpan!",
                    cancelButtonText: "Batal!",
                    closeOnConfirm: false,
                    closeOnCancel: true
                },
                function(isConfirm) {
                    if (isConfirm) {
                        var formdata = $("#frm_data").serialize();
                        $.ajax({
                            url: base_url + "request_mutasi/save_approval",
                            dataType: "json",
                            type: 'POST',
                            data: formdata,
                            success: function(data) {
                                if (data.status == 1) {
                                    swal({
                                        title: "Approve Success!",
                                        text: data.pesan,
                                        type: "success",
                                        timer: 15000,
                                        showCancelButton: false,
                                        showConfirmButton: false,
                                        allowOutsideClick: false
                                    });
                                    window.location.href = base_url + active_controller + 'approval_mutasi';
                                } else {

                                    if (data.status == 2) {
                                        swal({
                                            title: "Approve Failed!",
                                            text: data.pesan,
                                            type: "warning",
                                            timer: 10000,
                                            showCancelButton: false,
                                            showConfirmButton: false,
                                            allowOutsideClick: false
                                        });
                                    } else {
                                        swal({
                                            title: "Approve Failed!",
                                            text: data.pesan,
                                            type: "warning",
                                            timer: 10000,
                                            showCancelButton: false,
                                            showConfirmButton: false,
                                            allowOutsideClick: false
                                        });
                                    }

                                }
                            },
                            error: function() {
                                swal({
                                    title: "Gagal!",
                                    text: "Batal Proses, Data bisa diproses nanti",
                                    type: "error",
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            }
                        });
                    }
                });
        })
    })

    $(document).on('click', '.reject', function() {
        $("#dialog-popup").modal();
        $("#ModalView").show();
    });
</script>