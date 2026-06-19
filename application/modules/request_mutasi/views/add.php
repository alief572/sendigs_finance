<?php if (empty($result)) {
} else {
    $numb = 0;
    foreach ($result as $record) {
        $numb++;
        $kd_mutasi  = $record->kd_mutasi;
        $keterangan = $record->keterangan;
        $dari       = $record->bank_asal;
        $darinama   = $record->nama_bank_asal;
        $ke         = $record->bank_tujuan;
        $kenama     = $record->nama_bank_tujuan;
        $nilai      = $record->nilai_request;
        $tanggal    = $record->tgl_request;
        $target_accounting = isset($record->target_accounting) ? $record->target_accounting : '';
        $numb++;
    }
}

$target_labels = [
    'accounting_stm'     => 'STM',
    'accounting_vuca'    => 'VUCA',
    'accounting_sustain' => 'SUSTAIN',
];
$target_display = isset($target_accounting, $target_labels[$target_accounting])
    ? $target_labels[$target_accounting]
    : '-';
?>
<input type="hidden" id="id" name="id" value="<?php echo (isset($data->id) ? $data->id : ''); ?>">
<div class="box box-primary">
    <form method="post" id="frm_data" class="form-horizontal">
        <div class="box-body">
            <div class="row col-sm-12">
                <div class="col-sm-6">
                    <div class="form-group ">
                        <label for="tgl_bayar" class="col-sm-4 control-label">No Request</label>
                        <div class="col-sm-8">
                            <input type="text" name="no_request" id="no_request" value="<?= $kd_mutasi ?>" placeholder="Automatic" class="form-control input-sm" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="target_accounting_display" class="col-sm-4 control-label">Target Accounting</label>
                        <div class="col-sm-8">
                            <input type="text" id="target_accounting_display" value="<?= $target_display ?>" class="form-control input-sm" readonly>
                            <input type="hidden" name="target_accounting" id="target_accounting" value="<?= $target_accounting ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="dari" class="col-sm-4 control-label">Dari</label>
                        <div class="col-sm-8">
                            <?php
                            echo form_dropdown('dari_matauang', $matauang, '', array('id' => 'dari_matauang', 'required' => 'required', 'class' => 'form-control select2'));
                            ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="ke" class="col-sm-4 control-label">Ke</label>
                        <div class="col-sm-8">
                            <?php
                            echo form_dropdown('ke_matauang', $matauang, '', array('id' => 'ke_matauang', 'required' => 'required', 'class' => 'form-control select2'));
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="ket_bayar" class="col-sm-4 control-label">Keterangan</label>
                        <div class="col-sm-8">
                            <textarea name="keterangan" class="form-control input-sm" id="keterangan"><?= $keterangan ?></textarea>
                        </div>
                    </div>
                    <div class="form-group ">
                        <label for="tgl_bayar" class="col-sm-4 control-label">Kurs</label>
                        <div class="col-sm-8">
                            <input type="text" name="kurs" class="form-control input-sm divide " id="kurs" value="1" onblur="total()">
                        </div>
                    </div>
                </div>
            </div>
            <div class="row col-sm-12">
                <table class="table table-bordered table-striped" width='100%'>

                    <thead class="bg-blue" align="center">
                        <tr>
                            <td>Tanggal</td>
                            <td>Dari</td>
                            <td>Ke</td>
                            <td>Nilai Request</td>
                            <td>Aktual</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <input type="hidden" name="tgl_request" class="form-control input-sm" id="tgl_request" value="<?= $tanggal ?>" readonly>
                                <input type="date" name="tgl" class="form-control input-sm" id="tgl" value="<?= $tanggal ?>">
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
                            <td>
                                <input type="text" name="rupiah" class="form-control input-sm" id="rupiah" value="<?= number_format($nilai) ?>">
                            </td>
                        </tr>
                    </tbody>

                </table>
            </div>
        </div>
        <div class="box-footer">
            <div class="form-group">
                <div class="text-center">
                    <button type="submit" name="save" class="btn btn-success btn-sm" id="submit"><i class="fa fa-save">&nbsp;</i>Simpan</button>
                    <a class="btn btn-warning btn-sm" onclick="history.back(); return false;"><i class="fa fa-reply">&nbsp;</i>Batal</a>
                </div>
            </div>
        </div>
    </form>
</div>
<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/select2/select2.full.min.js') ?>"></script>
<script type="text/javascript">
    var url_save = siteurl + 'request_mutasi/save_mutasi/';
    $('.select2').select2();
    $('.divide').divide();

    function total() {
        var nilai = parseFloat($('#nilai').val().replace(/,/g, '')) || 0;
        var kurs = parseFloat($('#kurs').val().replace(/,/g, '')) || 1;
        var hasil = nilai * kurs;
        $('#rupiah').val(hasil.toLocaleString('id-ID'));
    }

    $('#frm_data').on('submit', function(e) {
        e.preventDefault();

        swal({
            title: "Konfirmasi",
            text: "Pastikan data sudah benar sebelum disimpan.",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#00a65a",
            confirmButtonText: "Ya, simpan!",
            cancelButtonText: "Batal",
            closeOnConfirm: false,
            closeOnCancel: true
        }, function(isConfirm) {
            if (!isConfirm) return;

            $.ajax({
                url: url_save,
                type: 'POST',
                data: $('#frm_data').serialize(),
                dataType: 'json',
                success: function(res) {
                    if (res.status == 1) {
                        swal({
                            title: "Berhasil!",
                            text: res.pesan + ' No: ' + res.nomor,
                            type: "success",
                            timer: 3000,
                            showConfirmButton: false
                        });
                        setTimeout(function() {
                            window.location.href = base_url + 'request_mutasi/mutasi';
                        }, 3000);
                    } else {
                        swal({
                            title: "Gagal!",
                            text: res.pesan,
                            type: "error",
                            timer: 5000,
                            showConfirmButton: false
                        });
                    }
                },
                error: function() {
                    swal({
                        title: "Error!",
                        text: "Terjadi kesalahan, silakan coba lagi.",
                        type: "error",
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
            });
        });
    });
</script>