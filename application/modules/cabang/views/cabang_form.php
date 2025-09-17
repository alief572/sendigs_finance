<div class="nav-tabs-cabang">
    <!-- /.tab-content -->
    <div class="tab-content">
        <div class="tab-pane active" id="cabang">
        <!-- Biodata Mitra -->
            <div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;">
            </div>
            <!-- form start-->
            <div class="box box-primary">
            <?= form_open($this->uri->uri_string(),array('id'=>'frm_cabang','name'=>'frm_cabang','role'=>'form','class'=>'form-horizontal')) ?>
                <div class="box-body">

                <div class="form-group ">
                    <?php  if(isset($data->id)){$type='edit';}?>
                    <input type="hidden" id="type" name="type" value="<?= isset($type) ? $type : 'add' ?>">
                    <input type="hidden" id="id" name="id" value="<?php echo set_value('id', isset($data->id) ? $data->id : ''); ?>">

                    <label for="kdcab" class="col-sm-2 control-label">Kode Cabang<font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-user"></i></span>
                        <input type="text" class="form-control" id="kdcab" name="kdcab" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" maxlength="4" value="<?php echo set_value('kdcab', isset($data->kdcab) ? $data->kdcab : ''); ?>" placeholder="Kode Cabang" required>
                        </div>
                    </div>

                    <label for="namacabang" class="col-sm-2 control-label">Nama Cabang <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-user"></i></span>
                        <input type="text" class="form-control" id="namacabang" name="namacabang" maxlength="45" value="<?php echo set_value('namacabang', isset($data->namacabang) ? $data->namacabang : ''); ?>" placeholder="Nama Cabang" required>
                        </div>
                    </div>
                </div>

                <div class="form-group ">
                    <label for="kepalacabang" class="col-sm-2 control-label">Kepala Cabang<font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-user"></i></span>
                        <input type="text" class="form-control" id="kepalacabang" name="kepalacabang" value="<?php echo set_value('kepalacabang', isset($data->kepalacabang) ? $data->kepalacabang : ''); ?>" placeholder="Kepala Cabang" required>
                        </div>
                    </div>

                    <label for="kabagjualan" class="col-sm-2 control-label">Kabag Penjualan<font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-user"></i></span>
                        <input type="text" class="form-control" id="kabagjualan" name="kabagjualan" value="<?php echo set_value('kabagjualan', isset($data->kabagjualan) ? $data->kabagjualan : ''); ?>" placeholder="Kabag Penjualan" required>
                        </div>
                    </div>
                </div>

                <div class="form-group ">
                    <label for="admcabang" class="col-sm-2 control-label">Adm Cabang<font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-user"></i></span>
                        <input type="text" class="form-control" id="admcabang" name="admcabang" value="<?php echo set_value('admcabang', isset($data->admcabang) ? $data->admcabang : ''); ?>" placeholder="Adm Cabang" required>
                        </div>
                    </div>

                    <label for="gudang" class="col-sm-2 control-label">Adm Gudang<font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-user"></i></span>
                        <input type="text" class="form-control" id="gudang" name="gudang" value="<?php echo set_value('gudang', isset($data->gudang) ? $data->gudang : ''); ?>" placeholder="Adm Gudang" required>
                        </div>
                    </div>
                </div>

                <div class="form-group ">
                    <label for="kota" class="col-sm-2 control-label">Kota <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                            <div class="input-group-btn">
                            <a class="btn btn-info">
                            <i class="fa fa-street-view">&nbsp;</i>
                            </a>
                            </div>
                            <select id="kota" name="kota" class="form-control pil_kota" style="width: 100%;" tabindex="-1" required>
                                <option value=""></option>
                                <?php foreach ($datkota as $key => $st) : ?>
                                <option value="<?= $st->nama; ?>" <?= set_select('kota', $st->nama, isset($data->kota) && $data->kota == $st->nama) ?>>
                                <?= $st->nama ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <label for="alamat" class="col-sm-2 control-label">Alamat</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-street-view"></i></span>
                        <textarea class="form-control" id="alamat" name="alamat" maxlength="255" placeholder="Alamat Kantor"  style="margin: 0px; height: 49px; width: 216px;"><?php echo set_value('alamat', isset($data->alamat) ? $data->alamat : ''); ?></textarea>
                        </div>
                    </div>
                </div>
				
				<input type="hidden" class="form-control" id="no_so" name="no_so" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" value="<?php echo set_value('no_so', isset($data->no_so) ? $data->no_so : '0'); ?>" placeholder="No SO" required>
				<input type="hidden" class="form-control" id="no_suratjalan" name="no_suratjalan" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" value="<?php echo set_value('no_suratjalan', isset($data->no_suratjalan) ? $data->no_suratjalan : '0'); ?>" placeholder="No DO" required>
				<input type="hidden" class="form-control" id="no_picking_list" name="no_picking_list" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" value="<?php echo set_value('no_picking_list', isset($data->no_picking_list) ? $data->no_picking_list : '0'); ?>" placeholder="No Picking List" required>
				<input type="hidden" class="form-control" id="no_invoice" name="no_invoice" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" value="<?php echo set_value('no_invoice', isset($data->no_invoice) ? $data->no_invoice : '0'); ?>" placeholder="No Invoice" required>
				<input type="hidden" class="form-control" id="no_pr" name="no_pr" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" value="<?php echo set_value('no_pr', isset($data->no_pr) ? $data->no_pr : '0'); ?>" placeholder="No Purchase Request" required>
				<input type="hidden" class="form-control" id="no_po" name="no_po" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" value="<?php echo set_value('no_po', isset($data->no_po) ? $data->no_po : '0'); ?>" placeholder="No PUrchase Order" required>
				<input type="hidden" class="form-control" id="no_receive" name="no_receive" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" value="<?php echo set_value('no_receive', isset($data->no_receive) ? $data->no_receive : '0'); ?>" placeholder="No Receive" required>
				<input type="hidden" class="form-control" id="th_picking_list" name="th_picking_list" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" value="<?php echo set_value('th_picking_list', isset($data->th_picking_list) ? $data->th_picking_list : '0'); ?>" placeholder="Tahun Picking List" required>
				<input type="hidden" class="form-control" id="sts_aktif" name="sts_aktif" value="aktif">
				<input type="hidden" class="form-control" id="biaya_logistik_lokal" name="biaya_logistik_lokal" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" value="<?php echo set_value('biaya_logistik_lokal', isset($data->biaya_logistik_lokal) ? $data->biaya_logistik_lokal : '0'); ?>" placeholder="Biaya Logistik Lokal" required>
				
				<!--
                <div class="form-group ">
                    <label for="no_so" class="col-sm-2 control-label">Number Sales Order<font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-user"></i></span>
                        <input type="text" class="form-control" id="no_so" name="no_so" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" value="<?php echo set_value('no_so', isset($data->no_so) ? $data->no_so : '0'); ?>" placeholder="No SO" required>
                        </div>
                    </div>

                    <label for="no_suratjalan" class="col-sm-2 control-label">Number Delivery Order<font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-user"></i></span>
                        <input type="text" class="form-control" id="no_suratjalan" name="no_suratjalan" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" value="<?php echo set_value('no_suratjalan', isset($data->no_suratjalan) ? $data->no_suratjalan : '0'); ?>" placeholder="No DO" required>
                        </div>
                    </div>


                </div>

                <div class="form-group ">
                    <label for="no_picking_list" class="col-sm-2 control-label">Number Picking List<font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-user"></i></span>
                        <input type="text" class="form-control" id="no_picking_list" name="no_picking_list" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" value="<?php echo set_value('no_picking_list', isset($data->no_picking_list) ? $data->no_picking_list : '0'); ?>" placeholder="No Picking List" required>
                        </div>
                    </div>

                    <label for="no_invoice" class="col-sm-2 control-label">Number Invoice<font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-user"></i></span>
                        <input type="text" class="form-control" id="no_invoice" name="no_invoice" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" value="<?php echo set_value('no_invoice', isset($data->no_invoice) ? $data->no_invoice : '0'); ?>" placeholder="No Invoice" required>
                        </div>
                    </div>


                </div>

                <div class="form-group ">
                    <label for="no_pr" class="col-sm-2 control-label">Number PR<font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-user"></i></span>
                        <input type="text" class="form-control" id="no_pr" name="no_pr" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" value="<?php echo set_value('no_pr', isset($data->no_pr) ? $data->no_pr : '0'); ?>" placeholder="No Purchase Request" required>
                        </div>
                    </div>

                    <label for="no_po" class="col-sm-2 control-label">Number PO<font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-user"></i></span>
                        <input type="text" class="form-control" id="no_po" name="no_po" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" value="<?php echo set_value('no_po', isset($data->no_po) ? $data->no_po : '0'); ?>" placeholder="No PUrchase Order" required>
                        </div>
                    </div>

                </div>		
						
                <div class="form-group ">
                    <label for="no_receive" class="col-sm-2 control-label">Number Receive<font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-user"></i></span>
                        <input type="text" class="form-control" id="no_receive" name="no_receive" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" value="<?php echo set_value('no_receive', isset($data->no_receive) ? $data->no_receive : '0'); ?>" placeholder="No Receive" required>
                        </div>
                    </div>

                    <label for="th_picking_list" class="col-sm-2 control-label">Tahun PL<font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-user"></i></span>
                        <input type="text" class="form-control" id="th_picking_list" name="th_picking_list" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" value="<?php echo set_value('th_picking_list', isset($data->th_picking_list) ? $data->th_picking_list : '0'); ?>" placeholder="Tahun Picking List" required>
                        </div>
                    </div>

                </div>

                <div class="form-group ">
                    <label for="sts_aktif" class="col-sm-2 control-label">Status</label>
                    <div class="col-sm-3">
                        <select id="sts_aktif" name="sts_aktif" class="form-control">
                            <option value="aktif" <?= set_select('sts_aktif', 'aktif', isset($data->sts_aktif) && $data->sts_aktif == 'aktif'); ?>>Active
                            </option>
                            <option value="nonaktif" <?= set_select('sts_aktif', 'nonaktif', isset($data->sts_aktif) && $data->sts_aktif == 'nonaktif'); ?>>Inactive
                            </option>
                        </select>
                    </div>

                    <label for="biaya_logistik_lokal" class="col-sm-2 control-label">Biaya Logistik Lokal<font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-user"></i></span>
                        <input type="text" class="form-control" id="biaya_logistik_lokal" name="biaya_logistik_lokal" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" value="<?php echo set_value('biaya_logistik_lokal', isset($data->biaya_logistik_lokal) ? $data->biaya_logistik_lokal : '0'); ?>" placeholder="Biaya Logistik Lokal" required>
                        </div>
                    </div>
                </div>

                <div class="form-group ">

                </div>
				-->

                <div class="box-footer">
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">

                    <button type="submit" name="save" class="btn btn-success" id="submit"><i class="fa fa-save">&nbsp;</i>Save</button>
                    <a class="btn btn-danger" data-toggle="modal" onclick="cancel()"><i class="fa fa-minus-circle">&nbsp;</i>Cancel</a>
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

<script type="text/javascript">

    $(document).ready(function() {

        $(".pil_kota").select2({
            placeholder: "Pilih Kota",
            allowClear: true
        });

    });

    //Biodata
    $('#frm_cabang').on('submit', function(e){
        e.preventDefault();
        var formdata = $("#frm_cabang").serialize();
        $.ajax({
            url: siteurl+"cabang/save_data_cabang",
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
                    window.location.reload();
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

    function cancel(){
        $(".box").show();
        $("#form-area").hide();
        //window.location.reload();
        //reload_table();
    }

    //function reload_table(){
        //table.ajax.reload(null,false); //reload datatable ajax
     //   table.ajax.reload();
   // }

</script>
