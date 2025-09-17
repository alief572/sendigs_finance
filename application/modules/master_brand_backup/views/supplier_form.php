<div class="nav-tabs-supplier">
    <!-- /.tab-content -->
    <div class="tab-content">
        <div class="tab-pane active" id="supplier">
        <!-- Biodata Mitra -->
            <div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;">
            </div>
            <!-- form start-->
            <div class="box box-primary">
            <?= form_open($this->uri->uri_string(), array('id' => 'frm_supplier', 'name' => 'frm_supplier', 'role' => 'form', 'class' => 'form-horizontal')); ?>
                <div class="box-body">

                <div class="form-group ">
                    <input type="hidden" id="id_supplier" name="id_supplier" value="<?php echo set_value('id_supplier', isset($data->id_supplier) ? $data->id_supplier : ''); ?>">
                        <?php  if (isset($data->id_supplier)) {
    $type = 'edit';
}?>
                    <input type="hidden" id="type" name="type" value="<?= isset($type) ? $type : 'add'; ?>">

                    <label for="nm_supplier" class="col-sm-2 control-label">Nama Supplier <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-user"></i></span>
                        <input type="text" class="form-control" id="nm_supplier" name="nm_supplier" value="<?php echo set_value('nm_supplier', isset($data->nm_supplier) ? $data->nm_supplier : ''); ?>" placeholder="Nama Supplier" required style="text-transform:uppercase">
                        </div>
                    </div>

                    <label for="alamat" class="col-sm-2 control-label">Alamat Lengkap <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-street-view"></i></span>
                        <textarea class="form-control" id="alamat" name="alamat" maxlength="255" placeholder="Alamat Lengkap Supplier" required style="text-transform:uppercase" autofocus="" style="margin: 0px; height: 49px; width: 216px;"><?php echo set_value('alamat', isset($data->alamat) ? $data->alamat : ''); ?></textarea>
                        </div>
                    </div>                    
                </div>

                <div class="form-group ">
                    <label for="nm_supplier" class="col-sm-2 control-label">Group Produk <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-user"></i></span>
                        <input type="text" class="form-control" id="group_produk" name="group_produk"  value="<?php echo set_value('group_produk', isset($data->group_produk) ? $data->group_produk : ''); ?>" placeholder="Group produk" required style="text-transform:uppercase">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="id_negara" class="col-sm-2 control-label">Negara</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                            <div class="input-group-btn">
                            <a class="btn btn-info" title="Negara">
                            <i class="fa fa-flag">&nbsp;</i>
                            </a>
                            </div>
                            <select id="id_negara" name="id_negara" class="form-control pil_negara">
                                <option value=""></option>
                                <?php foreach ($datnegara as $key => $st) : ?>
                                <option value="<?= $st->id_negara; ?>" <?= set_select('id_negara', $st->id_negara, isset($data->id_negara) && $data->id_negara == $st->id_negara); ?>>
                                <?= strtoupper($st->nm_negara); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>                            
                        </div>
                    </div>

                    <label for="mata_uang" class="col-sm-2 control-label">Mata Uang <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                            <div class="input-group-btn">
                            <!--<a class="btn btn-info" href="#add_matauang" data-toggle="modal" title="Add Mata Uang">-->
                            <a class="btn btn-info" title="Mata Uang">
                            <i class="fa fa-money">&nbsp;</i>
                            </a>
                            </div>
                            <select id="mata_uang" name="mata_uang[]" class="form-control pil_matu" style="width: 100%;" tabindex="-1" required multiple="multiple">
                                <option value=""></option>
                                <?php 
                                if (!isset($data->mata_uang)) {
                                    ?>
                                    <?php
                                foreach ($datmatu as $key => $st) : ?>
                                    <option value="<?= $st->id; ?>" <?= set_select('mata_uang', $st->id, isset($data->mata_uang) && $data->mata_uang == $st->id); ?>>
                                    <?= $st->mata_uang.' - '.$st->negara; ?>
                                    </option>
                                    <?php endforeach; ?>                                
                                <?php
                                } else {
                                    ?>
                                <?php
                                foreach ($datmatu as $key => $st) : ?>
                                    <option value="<?= $st->id; ?>" <?= set_select('mata_uang', $st->id, in_array($st->id, unserialize($data->mata_uang))); ?>>
                                    <?= $st->mata_uang.' - '.$st->negara; ?>
                                    </option>
                                    <?php endforeach; ?>
                                <?php
                                }
                                ?>
                                
                            </select>      
                        </div>
                    </div>                    
                </div>

                <div class="form-group" id="row_prokab">
                    <label for="id_prov" class="col-sm-2 control-label">Provinsi</label>                    
                    <div class="col-sm-3">
                        <div class="input-group">
                            <div class="input-group-btn">
                            <a class="btn btn-info" title="Provinsi">
                            <i class="fa fa-flag">&nbsp;</i>
                            </a>
                            </div>
                            <select id="id_prov" name="id_prov" class="form-control pil_provinsi" style="width: 100%;" tabindex="-1" onchange="get_kota()">
                                <option value=""></option>
                                <?php foreach ($datprov as $key => $st) : ?>
                                <option value="<?= $st->id_prov; ?>" <?= set_select('id_prov', $st->id_prov, isset($data->id_prov) && $data->id_prov == $st->id_prov); ?>>
                                <?= strtoupper($st->nama); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <label for="id_kab" class="col-sm-2 control-label">Kota / Kab</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                            <div class="input-group-btn">
                            <a class="btn btn-info" title="Kota">
                            <i class="fa fa-flag">&nbsp;</i>
                            </a>
                            </div>
                            <select id="id_kab" name="id_kab" class="form-control pil_kota" style="width: 100%;" tabindex="-1">
                                <option value=""></option>
                                <?php foreach ($datkota as $key => $st) : ?>
                                <option value="<?= $st->id_kab; ?>" <?= set_select('id_kab', $st->id_kab, isset($data->id_kab) && $data->id_kab == $st->id_kab); ?>>
                                <?= $st->nama; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>                    
                </div>

                <div class="form-group ">
                    <label for="telpon" class="col-sm-2 control-label">No Telpon</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                        <input type="text" class="form-control" id="telpon" name="telpon" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" maxlength="25" value="<?php echo set_value('telpon', isset($data->telpon) ? $data->telpon : ''); ?>" placeholder="No Telpon">
                        </div>
                    </div>

                    <label for="fax" class="col-sm-2 control-label">No Fax</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-fax"></i></span>
                        <input type="text" class="form-control" id="fax" name="fax" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" maxlength="25" value="<?php echo set_value('fax', isset($data->fax) ? $data->fax : ''); ?>" placeholder="No Fax">
                        </div>
                    </div>
                </div>

                <div class="form-group ">
                    <label for="npwp" class="col-sm-2 control-label">NPWP</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                        <input type="text" class="form-control" id="npwp" name="npwp" maxlength="15" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" placeholder="Nomor Pokok Wajib Pajak" value="<?php echo set_value('npwp', isset($data->npwp) ? $data->npwp : ''); ?>">
                        </div>
                    </div>

                    <label for="alamat_npwp" class="col-sm-2 control-label">Alamat NPWP</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-street-view"></i></span>
                        <textarea class="form-control" id="alamat_npwp" name="alamat_npwp" maxlength="255" placeholder="Alamat NPWP" autofocus style="text-transform:uppercase" style="margin: 0px; height: 49px; width: 216px;"><?php echo set_value('alamat_npwp', isset($data->alamat_npwp) ? $data->alamat_npwp : ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-group ">
                    <label for="cp" class="col-sm-2 control-label">Kontak Person  <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                        <input type="text" class="form-control" id="cp" name="cp" maxlength="15" placeholder="Kontak Person" style="text-transform:uppercase" value="<?php echo set_value('cp', isset($data->cp) ? $data->cp : ''); ?>" required>
                        </div>
                    </div>

                    <label for="hp_cp" class="col-sm-2 control-label">Hp Kontak Person</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-fax"></i></span>
                        <input type="text" class="form-control" id="hp_cp" name="hp_cp" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" maxlength="12" value="<?php echo set_value('hp_cp', isset($data->hp_cp) ? $data->hp_cp : ''); ?>" placeholder="No Hp Kontak Person">
                        </div>
                    </div>
                </div>

                <div class="form-group ">
                    <label for="email" class="col-sm-2 control-label">Email</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                        <input type="text" style="text-transform:uppercase" class="form-control" id="email" name="email" maxlength="100" placeholder="Email" value="<?php echo set_value('email', isset($data->email) ? $data->email : ''); ?>">
                        </div>
                    </div> 

                    <label for="id_webchat" class="col-sm-2 control-label">WeChat ID</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                        <input type="text" style="text-transform:uppercase" class="form-control" id="id_webchat" name="id_webchat" maxlength="100" placeholder="WeChat ID" value="<?php echo set_value('id_webchat', isset($data->id_webchat) ? $data->id_webchat : ''); ?>">
                        </div>
                    </div>                    
                </div>

                <div class="form-group ">
                    <label for="keterangan" class="col-sm-2 control-label">Keterangan Lain</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-street-view"></i></span>
                        <textarea class="form-control" id="keterangan" name="keterangan" maxlength="255" placeholder="Keterangan Lain"  style="text-transform:uppercase" style="margin: 0px; height: 49px; width: 216px;"><?php echo set_value('keterangan', isset($data->keterangan) ? $data->keterangan : ''); ?></textarea>
                        </div>
                    </div> 

                    <label for="sts_aktif" class="col-sm-2 control-label">Status</label>
                    <div class="col-sm-2">
                        <select id="sts_aktif" name="sts_aktif" class="form-control">
                            <option value="aktif" <?= set_select('sts_aktif', 'aktif', isset($data->sts_aktif) && $data->sts_aktif == 'aktif'); ?>>Active
                            </option>
                            <option value="nonaktif" <?= set_select('sts_aktif', 'nonaktif', isset($data->sts_aktif) && $data->sts_aktif == 'nonaktif'); ?>>Inactive
                            </option>
                        </select>
                    </div>
                </div>
               <div class="form-group ">
                   <label for="keterangan" class="col-sm-2 control-label">Masa produksi</label>
                   <div class="col-sm-3">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                            <input style="width: 30%" id="produksi_awal" name="produksi_awal" value="<?php echo set_value('produksi_awal', isset($data->produksi_awal) ? $data->produksi_awal : ''); ?>" class="form-control"  /> 
                            <span style="float: left"> Sampai </span>
                            <input style="width: 30%" id="produksi_awal" name="produksi_akhir" value="<?php echo set_value('produksi_akhir', isset($data->produksi_akhir) ? $data->produksi_akhir : ''); ?>" class="form-control"  />
                            &nbsp; Hari
                        </div>
                    </div> 
                    
                    <label for="keterangan" class="col-sm-2 control-label">Masa pengaplan container</label>
                   <div class="col-sm-3">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                            <input style="width: 30%" id="pengapalan_awal" name="pengapalan_awal" value="<?php echo set_value('pengapalan_awal', isset($data->pengapalan_awal) ? $data->pengapalan_awal : ''); ?>" class="form-control"  /> 
                            <span style="float: left"> Sampai </span>
                            <input style="width: 30%" id="pengapalan_akhir" name="pengapalan_akhir" value="<?php echo set_value('pengapalan_akhir', isset($data->pengapalan_akhir) ? $data->pengapalan_akhir : ''); ?>" class="form-control"  />
                            &nbsp; Hari
                        </div>
                    </div> 
               </div>
               
               <div class="form-group ">
                   <label for="keterangan" class="col-sm-2 control-label">Masa pengiriman</label>
                   <div class="col-sm-3">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                            <input style="width: 30%" id="pengiriman_awal" name="pengiriman_awal" value="<?php echo set_value('pengiriman_awal', isset($data->pengiriman_awal) ? $data->pengiriman_awal : ''); ?>" class="form-control"  /> 
                            <span style="float: left"> Sampai </span>
                            <input style="width: 30%"  id="pengiriman_akhir" name="pengiriman_akhir" value="<?php echo set_value('pengiriman_akhir', isset($data->pengiriman_akhir) ? $data->pengiriman_akhir : ''); ?>" class="form-control"  />
                            &nbsp; Hari
                        </div>
                    </div> 
                    
                    <label for="keterangan" class="col-sm-2 control-label">Masa proses cukai</label>
                   <div class="col-sm-3">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                            <input style="width: 30%" id="cukai_awal" name="cukai_awal" value="<?php echo set_value('cukai_awal', isset($data->cukai_awal) ? $data->cukai_awal : ''); ?>" class="form-control"  /> 
                            <span style="float: left"> Sampai </span>
                            <input style="width: 30%" id="cukai_akhir" name="cukai_akhir" value="<?php echo set_value('cukai_akhir', isset($data->cukai_akhir) ? $data->cukai_akhir : ''); ?>" class="form-control"  />
                            &nbsp; Hari
                        </div>
                    </div> 
               </div>
                
                <?php
                $queryc = $this->db->query('SELECT * FROM `cbm` ');

                foreach ($queryc->result() as $rowc) {
                    if (empty($data->id_supplier)) {
                        $id_cbmn = '';
                        $cbm_v = '';
                        $kgs_v = '';
                    } else {
                        $querysd = $this->db->query("SELECT * FROM `supplier_cbm` where id_supplier='$data->id_supplier' and id_cbm='$rowc->id_cbm'");
                        $rowsc = $querysd->row();
                        $cbm_v = $rowsc->cbm;
                        $kgs_v = $rowsc->kgs;
                        $id_cbmn = $rowsc->id_supplier_cbm;
                    } ?>
                     <div class="form-group ">
                        <input type="hidden" name="id_supplier_cbm[]" value="<?= $id_cbmn; ?>" />
                        <input type="hidden" name="id_cbm[]" value="<?= $rowc->id_cbm; ?>" />
                        <label for="id_webchat" class="col-sm-2 control-label"><?= $rowc->name_cbm; ?></label>
                        <div class="col-sm-3">
                            <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                            <input type="text" style="text-transform:uppercase" class="form-control" id="cbm" name="cbm[]" maxlength="4" placeholder="CBM" value="<?php echo $cbm_v; ?>">
                            </div>
                        </div>  
                        
                        <label for="id_webchat" class="col-sm-2 control-label"><?= $rowc->name_cbm; ?> KGS</label>
                        <div class="col-sm-3">
                            <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                            <input type="text" style="text-transform:uppercase" class="form-control" id="cbm" name="cbm_kgs[]" maxlength="4" placeholder="CBM KGS" value="<?php echo $kgs_v; ?>">
                            </div>
                        </div>                    
                    </div>
                    <?php
                }
                ?>

                <div class="box-footer">
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">

                    <button type="submit" name="save" class="btn btn-success" id="submit"><i class="fa fa-save">&nbsp;</i>Save</button>
                    <a class="btn btn-danger" data-toggle="modal" onclick="cancel()"><i class="fa fa-minus-circle">&nbsp;</i>Cancel</a>
                    </div>
                </div>
                </div>

                </div>
            <?= form_close(); ?>
            </div>
        <!-- Biodata Mitra -->
        </div>                                    

    </div>
    <!-- /.tab-content -->
</div>

<!-- Modal Mata Uang-->
<div class="modal modal-info" id="add_matauang" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title" id="myModalLabel"><span class="fa fa-file-pdf-o"></span>&nbsp;Tambah Data Mata Uang</h4>
            </div>
            <div class="modal-body" id="MyModalBody">
            <form action="#" id="form_matu">
            <div class="form-group">
                <label for="kode">Kode <font size="4" color="red"><B>*</B></font></label>
                <input type="text" class="form-control" id="kodex" name="kodex" placeholder="Kode" required>
            </div>
            <div class="form-group">
                <label for="mata_uang">Mata Uang <font size="4" color="red"><B>*</B></font></label>
                <input type="text" class="form-control" id="mata_uangx" name="mata_uangx" placeholder="Mata Uang" required>
            </div>
            <div class="form-group">
                <label for="negara">Negara <font size="4" color="red"><B>*</B></font></label>
                <input type="text" class="form-control" id="negara" name="negara" placeholder="negara" required>
            </div>            
            </form>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="javascript:save_matu();">Save</button>
            <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- End Modal Reff-->
<script type="text/javascript">

    $(function () {
        $('#row_prokab').hide();
        $('#id_negara').change(function () {
             if($('#id_negara').val() == 'ID') {
                $('#row_prokab').show();
             }else{
                $('#row_prokab').hide();
             }
         });
    });

    $(document).ready(function() {               

        var negara = $('#id_negara').val();
        if(negara=='ID'){
            $('#row_prokab').show();
        }else{
            $('#row_prokab').hide();
        }

        $(".pil_matu").select2({
            placeholder: "Pilih Mata Uang",
            allowClear: true
        });

        $(".pil_negara").select2({
            placeholder: "Pilih Negara",
            allowClear: true
        });

        $(".pil_provinsi").select2({
            placeholder: "Pilih Provinsi",
            allowClear: true
        });

        $(".pil_kota").select2({
            placeholder: "Pilih Kota",
            allowClear: true
        });

    });

    function get_kota(){
        var provinsi=$("#id_prov").val();
        $.ajax({
            type:"GET",
            url:siteurl+"customer/get_kota",
            data:"provinsi="+provinsi,
            success:function(html){
               $("#id_kab").html(html);
            }
        });
    }

    //Reff
    function save_matu(){

    var kode=$("#kodex").val();
    var mata_uang=$("#mata_uangx").val();
    var negara=$("#negara").val();
    var keterangan=$("#keterangan").val();

    if(kode=='' || mata_uang=='' || negara==''){
        swal({
        title: "Peringatan!",
        text: "Isi Data yang Lengkap!",
        type: "warning",
        confirmButtonText: "Ok"
        });
        //die;
    }else{
        $.ajax({
            type:"POST",
            url:siteurl+"supplier/add_matu",
            data:"kode="+kode+"&mata_uang="+mata_uang+"&negara="+negara+"&keterangan="+keterangan,
            success:function(html){
                $('#add_matauang').modal('hide');
                $('#form_matu')[0].reset(); // reset form on modals
                get_matu();
            }
        });
    }

    }

    function get_matu(){
        $.ajax({
            type:"GET",
            url:siteurl+"supplier/get_matu",
            success:function(html){
                $("#mata_uang").html(html);
            }
        });
    }

    //Biodata
    $('#frm_supplier').on('submit', function(e){
        e.preventDefault();
        var formdata = $("#frm_supplier").serialize();
        $.ajax({
            url: siteurl+"supplier/save_data_supplier",
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
