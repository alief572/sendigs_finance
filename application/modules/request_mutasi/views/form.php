<input type="hidden" id="id" name="id" value="<?php echo (isset($data->id) ? $data->id : ''); ?>">
<div class="box box-primary">
    <form method="post" id="frm_data" class="form-horizontal">
        <div class="box-body">
            <div class="row col-sm-12">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="tgl_bayar" class="col-sm-4 control-label">No Request </label>
                        <div class="col-sm-8">
                            <input type="text" name="no_request" id="no_request" placeholder="Automatic" class="form-control input-sm" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <?php
                        $tglinv = date('Y-m-d');
                        ?>
                        <label for="tgl_bayar" class="col-sm-4 control-label">Tgl</label>
                        <div class="col-sm-8">
                            <input type="date" name="tgl_request" id="tgl_request" class="form-control input-sm" value="<?php echo date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="dari" class="col-sm-4 control-label">Dari</label>
                        <div class="col-sm-8">
                            <?php
                            echo form_dropdown('dari', $datbank, '', array('id' => 'dari', 'required' => 'required', 'class' => 'form-control select2'));
                            ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="ke" class="col-sm-4 control-label">Ke</label>
                        <div class="col-sm-8">
                            <?php
                            echo form_dropdown('ke', $datbank, '', array('id' => 'ke', 'required' => 'required', 'class' => 'form-control select2'));
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="ket_bayar" class="col-sm-4 control-label">Keterangan</label>
                        <div class="col-sm-8">
                            <textarea name="keterangan" class="form-control input-sm" id="keterangan"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="matauang" class="col-sm-4 control-label">Mata Uang</label>
                        <div class="col-sm-8">
                            <?php
                            echo form_dropdown('matauang', $matauang, '', array('id' => 'matauang', 'required' => 'required', 'class' => 'form-control select2'));
                            ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="tgl_bayar" class="col-sm-4 control-label">Nilai</label>
                        <div class="col-sm-8">
                            <input type="text" name="nilai" class="form-control input-sm divide " id="nilai" onblur="fn_terbilang()">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-8">
                <div class="form-group">
                    <input type="text" name="terbilang" id="terbilang" placeholder="TERBILANG" class="form-control input-sm" readonly>
                </div>
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
    var url_save = siteurl + 'request_mutasi/save/';
    $('.select2').select2();
    $('.divide').divide();

    $('#frm_data').on('submit', function(e) {
        e.preventDefault();

        let errors = {
            '#tgl_request': 'Tanggal Tidak Boleh Kosong!',
            '#matauang': 'Mata uang tidak boleh kosong!',
            '#dari': 'Bank Asal tidak boleh kosong!',
            '#ke': 'Bank Tujuan tidak boleh kosong!',
            '#nilai': 'Nilai tidak boleh kosong!',
            '#keterangan': 'Keterangan tidak boleh kosong!',
        };

        for (let field in errors) {
            if ($(field).val() === "" || $(field).val() === "0") {
                swal({
                    title: errors[field],
                    text: "Silakan isi dengan benar!",
                    type: "warning",
                    timer: 3000,
                    showConfirmButton: false,
                    allowOutsideClick: false
                });
                return;
            }
        }

        swal({
            title: "Peringatan!",
            text: "Pastikan data sudah lengkap dan benar",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya, simpan!",
            cancelButtonText: "Batal!",
            confirmButtonColor: "#DD6B55",
            closeOnConfirm: false,
            closeOnCancel: true
        }, function(isConfirm) {
            if (isConfirm) {
                $.ajax({
                    url: base_url + "request_mutasi/save",
                    dataType: "json",
                    type: "POST",
                    data: $("#frm_data").serialize(),
                    success: function(data) {
                        let status = data.status == 1 ? "success" : "warning";
                        swal({
                            title: data.status == 1 ? "Save Success!" : "Save Failed!",
                            text: data.pesan,
                            type: status,
                            timer: 10000,
                            showConfirmButton: false,
                            allowOutsideClick: false
                        });

                        if (data.status == 1) {
                            window.location.href = base_url + active_controller + 'index';
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
    });

    //fungsi untuk diview
    function fn_terbilang() {
        var nilai = $("#nilai").val();
        var matauang = $("#matauang").val();

        if ($('#matauang').val() == "0") {
            swal({
                title: "Mata uang tidak boleh kosong!",
                text: "Silahkan Pilih Mata Uang Dahulu!",
                type: "warning",
                timer: 3000,
                showCancelButton: false,
                showConfirmButton: false,
                allowOutsideClick: false
            });
        } else {
            $.ajax({
                type: "GET",
                url: base_url + "request_mutasi/terbilang",
                data: "nilai=" + nilai + "&matauang=" + matauang,
                success: function(html) {
                    $("#terbilang").val(html);
                }
            });
        }

    }
</script>