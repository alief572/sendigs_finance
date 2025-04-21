<div class="nav-tabs-supplier">
    <!-- /.tab-content -->
    <div class="tab-content">
        <div class="tab-pane active" id="supplier">
        <!-- Biodata Mitra -->
            <div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;">
            </div>
            <!-- form start-->
            <div class="box box-primary">
            <?= form_open($this->uri->uri_string(),array('id'=>'frm_biodata','name'=>'frm_biodata','role'=>'form','class'=>'form-horizontal')) ?>
                <div class="box-body">

                <input type="hidden" id="id_karyawan" name="id_karyawan" value="<?php echo set_value('id_karyawan', isset($data->id_karyawan) ? $data->id_karyawan : ''); ?>">
                <?php  if(isset($data->id_karyawan)){$type='edit';}?>
                <input type="hidden" id="type" name="type" value="<?= isset($type) ? $type : 'add' ?>">

                <div class="form-group ">
                    <label for="nama_karyawan" class="col-sm-2 control-label">Nama Lengkap <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-user"></i></span>
                        <input type="text" class="form-control" id="nama_karyawan" name="nama_karyawan" maxlength="45" value="<?php echo set_value('nama_karyawan', isset($data->nama_karyawan) ? $data->nama_karyawan : ''); ?>" placeholder="Nama Lengkap Karyawan" required style="text-transform:uppercase">
                        </div>
                    </div>

                    <label for="nik" class="col-sm-2 control-label">NIK</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                        <input type="text" class="form-control" id="nik" name="nik" maxlength="16" value="<?php echo set_value('nik', isset($data->nik) ? $data->nik : ''); ?>" placeholder="Nomor Induk Karyawan" style="text-transform:uppercase">
                        </div>
                    </div>
                </div>

                <div class="form-group ">
                    <label for="kelamin" class="col-sm-2 control-label">Jenis Kelamin <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <select id="kelamin" name="kelamin" class="form-control">
                            <option value="L" <?= set_select('kelamin', 'L', isset($data->jeniskelamin) && $data->jeniskelamin == 'L'); ?>>Laki-laki
                            </option>
                            <option value="P" <?= set_select('kelamin', 'P', isset($data->jeniskelamin) && $data->jeniskelamin == 'P'); ?>>Perempuan</option>
                        </select>
                    </div>

                    <label for="tempatlahir" class="col-sm-2 control-label">Tempat / Tangal Lahir <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-2">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-map-pin"></i></span>
                        <input type="text" class="form-control" id="tempatlahir" name="tempatlahir" maxlength="15" value="<?php echo set_value('tempatlahir', isset($data->tempatlahir) ? $data->tempatlahir : ''); ?>" placeholder="Tempat Lahir" required style="text-transform:uppercase" autofocus="">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="input-group">
                            <?php $tanggallahir = $data->tanggallahir;?>
                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        <input type="text" class="form-control pull-right" id="tanggallahir" name="tanggallahir" value="<?php echo set_value('tanggallahir', isset($tanggallahir) ? date('d-m-Y', strtotime($tanggallahir)) :''); ?>" placeholder="Tanggal Lahir" required>
                        </div>
                    </div>
                </div>

                <div class="form-group ">
                    <label for="tgl_join" class="col-sm-2 control-label">Tanggal Bergabung<font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                            <?php $tgl_join = $data->tgl_join;?>
                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        <input type="text" class="form-control" id="tgl_join" name="tgl_join" value="<?php echo set_value('tgl_join', isset($data->tgl_join) ? date('d-m-Y', strtotime($tgl_join)) :''); ?>" placeholder="Tanggal Masuk" required="">
                        </div>
                    </div>

                    <label for="tgl_end" class="col-sm-2 control-label">Tanggal Berakhir Kontrak<font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                            <?php $tgl_end = $data->tgl_end;?>
                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        <input type="text" class="form-control" id="tgl_end" name="tgl_end" value="<?php echo set_value('tgl_end', isset($data->tgl_end) ? date('d-m-Y', strtotime($tgl_end)) :''); ?>" placeholder="Tgl Akhir Kontrak" required>
                        </div>
                    </div>
                </div>

                <div class="form-group ">
                    <label for="sts_karyawan" class="col-sm-2 control-label">Status Karyawan <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <select id="sts_karyawan" name="sts_karyawan" class="form-control">
                            <option value="Tetap" <?= set_select('sts_karyawan', 'Tetap', isset($data->sts_karyawan) && $data->sts_karyawan == 'Tetap'); ?>>Tetap
                            </option>
                            <option value="Kontrak" <?= set_select('sts_karyawan', 'Kontrak', isset($data->sts_karyawan) && $data->sts_karyawan == 'Kontrak'); ?>>Kontrak
                            </option>
                        </select>
                    </div>

                    <label for="norekening" class="col-sm-2 control-label">No Rekening</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                        <input type="text" class="form-control" id="norekening" name="norekening" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" maxlength="20" value="<?php echo set_value('norekening', isset($data->norekening) ? $data->norekening : ''); ?>" placeholder="Nomor Rekening">
                        </div>
                    </div>
                </div>
                <div class="form-group ">
                   <label for="agama" class="col-sm-2 control-label">Agama <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <select id="agama" name="agama" class="form-control">
                            <option value="Islam" <?= set_select('agama', 'Islam', isset($data->agama) && $data->agama == 'Islam'); ?>>Islam
                            </option>
                            <option value="Protestan" <?= set_select('agama', 'Protestan', isset($data->agama) && $data->agama == 'Protestan'); ?>>Protestan
                            </option>
                            <option value="Katolik" <?= set_select('agama', 'Katolik', isset($data->agama) && $data->agama == 'Katolik'); ?>>Katolik
                            </option>
                            <option value="Hindu" <?= set_select('agama', 'Hindu', isset($data->agama) && $data->agama == 'Hindu'); ?>>Hindu
                            </option>
                            <option value="Budha" <?= set_select('agama', 'Budha', isset($data->agama) && $data->agama == 'Budha'); ?>>Budha
                            </option>
                            <option value="Konghucu" <?= set_select('agama', 'Konghucu', isset($data->agama) && $data->agama == 'Konghucu'); ?>>Konghucu
                            </option>
                            <option value="Lainnya" <?= set_select('agama', 'Lainnya', isset($data->agama) && $data->agama == 'Lainnya'); ?>>Lainnya
                            </option>
                        </select>
                    </div>

                    <label for="divisi" class="col-sm-2 control-label">Divisi <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                            <select id="id_divisi" name="id_divisi" class="form-control pil_divisi" style="width: 100%;" tabindex="-1" required >
                                <option value=""></option>
                                <?php foreach ($datdiv as $key => $st) : ?>
                                <option value="<?= $st->id_divisi; ?>" <?= set_select('id_divisi', $st->id_divisi, isset($data->divisi) && $data->divisi == $st->id_divisi) ?>>
                                <?= $st->nm_divisi ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="input-group-btn">
                            <a class="btn btn-info" href="#add_divisi" data-toggle="modal" title="Add Divisi" onclick="add_divisi()">
                            <i class="fa fa-plus">&nbsp;</i>
                            </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group ">
                    <label for="noktp" class="col-sm-2 control-label">No KTP <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                        <input type="text" class="form-control" id="noktp" name="noktp" maxlength="20" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" placeholder="Nomor KTP" value="<?php echo set_value('noktp', isset($data->noktp) ? $data->noktp : ''); ?>" required>
                        </div>
                    </div>

                    <label for="npwp" class="col-sm-2 control-label">NPWP</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                        <input type="text" class="form-control" id="npwp" name="npwp" maxlength="15" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" placeholder="Nomor Pokok Wajib Pajak" value="<?php echo set_value('npwp', isset($data->npwp) ? $data->npwp : ''); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group ">
                    <label for="pendidikan" class="col-sm-2 control-label">Level Pendidikan</label>
                    <div class="col-sm-3">
                        <select id="pendidikan" name="pendidikan" class="form-control">
                            <option value="SD" <?= set_select('pendidikan', 'SD', isset($data->levelpendidikan) && $data->levelpendidikan == 'SD'); ?>>SD
                            </option>
                            <option value="SMP" <?= set_select('pendidikan', 'SMP', isset($data->levelpendidikan) && $data->levelpendidikan == 'SMP'); ?>>SMP
                            </option>
                            <option value="SMA" <?= set_select('pendidikan', 'SMA', isset($data->levelpendidikan) && $data->levelpendidikan == 'SMA'); ?>>SMA
                            </option>
                            <option value="DIPLOMA" <?= set_select('pendidikan', 'Diploma', isset($data->levelpendidikan) && $data->levelpendidikan == 'Diploma'); ?>>Diploma
                            </option>
                            <option value="SARJANA" <?= set_select('pendidikan', 'SARJANA', isset($data->levelpendidikan) && $data->levelpendidikan == 'SARJANA'); ?>>Sarjana
                            </option>
                            <option value="MASTER" <?= set_select('pendidikan', 'MASTER', isset($data->levelpendidikan) && $data->levelpendidikan == 'MASTER'); ?>>Master
                            <option value="DOKTORAL" <?= set_select('pendidikan', 'DOKTORAL', isset($data->levelpendidikan) && $data->levelpendidikan == 'DOKTORAL'); ?>>Doktoral
                            <option value="PROFESOR" <?= set_select('pendidikan', 'PROFESOR', isset($data->levelpendidikan) && $data->levelpendidikan == 'PROFESOR'); ?>>Profesor
                            <option value="LAIN-LAIN" <?= set_select('pendidikan', 'LAIN-LAIN', isset($data->levelpendidikan) && $data->levelpendidikan == 'LAIN-LAIN'); ?>>Lain-lain
                            </option>
                        </select>
                    </div>

                    <label for="nohp" class="col-sm-2 control-label">No Handphone <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-mobile-phone"></i></span>
                        <input type="text" class="form-control" id="nohp" name="nohp" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" maxlength="12" value="<?php echo set_value('nohp', isset($data->nohp) ? $data->nohp : ''); ?>" placeholder="No Handphone" required>
                        </div>
                    </div>
                </div>

                <div class="form-group ">
                    <label for="email" class="col-sm-2 control-label">Email</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" maxlength="45" value="<?php echo set_value('email', isset($data->email) ? $data->email : ''); ?>" placeholder="alamat email" style="text-transform:uppercase">
                        </div>
                    </div>

                    <label for="alamat" class="col-sm-2 control-label">Alamat Lengkap <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-street-view"></i></span>
                        <textarea style="text-transform:uppercase" class="form-control" id="alamat" name="alamat" maxlength="255" placeholder="Alamat Lengkap Karyawan" required autofocus="" style="margin: 0px; height: 49px; width: 216px;"><?php echo set_value('alamat', isset($data->alamataktif) ? $data->alamataktif : ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-group ">
                    <label for="sts_aktif" class="col-sm-2 control-label">Status</label>
                    <div class="col-sm-2">
                        <select id="sts_aktif" name="sts_aktif" class="form-control">
                            <option value="aktif" <?= set_select('sts_aktif', 'aktif', isset($data->status_aktif) && $data->status_aktif == 'aktif'); ?>>Active
                            </option>
                            <option value="nonaktif" <?= set_select('sts_aktif', 'nonaktif', isset($data->status_aktif) && $data->status_aktif == 'nonaktif'); ?>>Inactive
                            </option>
                        </select>
                    </div>
                </div>

                <div class="box-footer">
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">

                        <button type="submit" name="save" class="btn btn-primary" id="submit">Save</button>
                        <input type="button" name="btn_cancel" class="btn btn-danger" onclick="cancel()" value="Cancel">

                    </div>
                </div>
                </div>

                </div>
            <?= form_close() ?>
            </div>
        <!-- Biodata Mitra -->
        </div>

    </div>
    <!-- /.tab-content -->
</div>
<!-- Modal Bidus-->
<div class="modal modal-info" id="add_divisi" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title" id="myModalLabel"><span class="fa fa-file-pdf-o"></span>&nbsp;Tambah Data Divisi</h4>
            </div>
            <div class="modal-body" id="MyModalBody">
            <form action="#" id="form_bidus">
            <div class="form-group">
                <label for="bidus">Nama Divisi <font size="4" color="red"><B>*</B></font></label>
                <input type="text" class="form-control" id="nm_divisi" name="nm_divisi" placeholder="Nama Divisi" required style="text-transform:uppercase">
                <input type="hidden" class="form-control" id="id_divisix" name="id_divisix">
            </div>
            <div class="form-group">
                <iframe onload="ListDV()" hidden="true"></iframe>
            </div>
            </form>
            </div>
            <div id="list_dv">
            </div>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="javascript:save_divisi();">Save</button>
            <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- End Modal Bidus-->
<script type="text/javascript">

    //save_divisi
    function save_divisi(){

    var nm_divisi=$("#nm_divisi").val();
    var id_divisix=$("#id_divisix").val();

    if(nm_divisi==''){
        swal({
          title: "Peringatan!",
          text: "Isi Data Dengan Lengkap!",
          type: "warning",
          confirmButtonText: "Ok"
        });
        //die;
    }else{
        $.ajax({
            type:"POST",
            url:siteurl+"karyawan/add_divisi",
            data:"id_divisix="+id_divisix+"&nm_divisi="+nm_divisi,
            success:function(html){
                $('#add_divisi').modal('hide');
                //$('#add_divisi')[0].reset(); // reset form on modals
                get_divisi();
                ListDV();
            }
        });
    }

    }

    function get_div() {
        var id_divisi = $('#id_divisi').val();
        $.ajax({
            type:"GET",
            url:siteurl+"karyawan/get_div",
            data:"id_divisi="+id_divisi,
            dataType : "json",
            success:function(msg){
               $("#nm_cp").val(msg['nm_cp']);
               set_nmcolly();
            }
        });
    }

    function get_divisi(){
        $.ajax({
            type:"GET",
            url:siteurl+"karyawan/get_divisi",
            success:function(html){
               $("#id_divisi").html(html);
            }
        });
    }

    function ListDV(){
    $.ajax({
        type:"GET",
        url:siteurl+"karyawan/ListDV",
        success:function(html){
            $("#list_dv").html(html);
        }
    })
    }

    function edit_dv(id){
        if(id != ""){
        $.ajax({
            type:"POST",
            url:siteurl+"karyawan/edit_dv",
            data:{"id":id},
            success:function(result){
                var data = JSON.parse(result);
                $('#id_divisix').val(data.id_divisi);
                $('#nm_divisi').val(data.nm_divisi);
            }
        })
    }
    }

    $(document).ready(function() {
        $(".pil_divisi").select2({
            placeholder: "Pilih Divisi",
            allowClear: true
        });
    });

    //Date picker
    $('#tanggallahir').datepicker({
      format: 'dd-mm-yyyy',
      todayHighlight: true,
      //startDate: new Date(),
      autoclose: true
    });

    $('#tgl_join').datepicker({
      format: 'dd-mm-yyyy',
      todayHighlight: true,
      //startDate: new Date(),
      autoclose: true
    });

    $('#tgl_end').datepicker({
      format: 'dd-mm-yyyy',
      todayHighlight: true,
      //startDate: new Date(),
      autoclose: true
    });

    function cancel(){
        $(".box").show();
        $("#form-area").hide();
        window.location.reload();
        //reload_table();
    }

    //Biodata
    $('#frm_biodata').on('submit', function(e){
        e.preventDefault();
        var formdata = $("#frm_biodata").serialize();
        $.ajax({
            url: siteurl+"karyawan/save_biodata_ajax",
            dataType : "json",
            type: 'POST',
            data: formdata,
            //alert(msg);
            success: function(msg){
                if(msg['save']=='1'){
                    swal({
                        title: "Sukses!",
                        text: "Data Berhasil Di Simpan",
                        type: "success",
                        timer: 1500,
                        showConfirmButton: false
                    });
                    cancel();
                    //document.getElementById("frm_biodata").reset();
                } else {
                    swal({
                        title: "Gagal!",
                        text: "Data Gagal Di Simpan",
                        type: "error",
                        timer: 1500,
                        showConfirmButton: false
                    });
                };//alert(msg);
            },
            error: function(){
                swal({
                    title: "Gagal!",
                    text: "Ajax Data Gagal Di Proses",
                    type: "error",
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        });
    });
</script>
