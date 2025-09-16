<div class="nav-tabs-custom">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#biodata" data-toggle="tab" aria-expanded="true" id="data">Master Customer</a></li>
        <li class=""><a href="#toko" data-toggle="tab" aria-expanded="false" id="data_toko">Toko</a></li>
        <li class=""><a href="#penagihan" data-toggle="tab" aria-expanded="false" id="data_penag">Sistem Penagihan</a></li>
        <li class=""><a href="#pembayaran" data-toggle="tab" aria-expanded="false" id="data_pemba">Sistem Pembayaran</a></li>
        <li class=""><a href="#pic" data-toggle="tab" aria-expanded="false" id="data_pic">PIC</a></li>
        <li class=""><a href="#foto" data-toggle="tab" aria-expanded="false" id="data_foto">Foto</a></li>
    </ul>
    <!-- /.tab-content -->
    <div class="tab-content">
        <div class="tab-pane active" id="biodata">
        <!-- Biodata Mitra -->
            <div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;">
            </div>
            <!-- form start-->
            <div class="box box-primary">
            <?= form_open($this->uri->uri_string(),array('id'=>'frm_biodata','name'=>'frm_biodata','role'=>'form','class'=>'form-horizontal')) ?>
                <div class="box-body">

                <div class="form-group ">
                    <input type="hidden" id="id_customer" name="id_customer" value="<?php echo set_value('id_customer', isset($data->id_customer) ? $data->id_customer : gen_idcustomer('C')); ?>">
                        <?php  if(isset($data->id_customer)){$type='edit';}?>
                    <input type="hidden" id="type" name="type" value="<?= isset($type) ? $type : 'add' ?>">

                    <label for="nm_customer" class="col-sm-2 control-label">Nama Customer <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-user"></i></span>
                        <input type="text" class="form-control" id="nm_customer" name="nm_customer" maxlength="45" value="<?php echo set_value('nm_customer', isset($data->nm_customer) ? $data->nm_customer : ''); ?>" placeholder="Nama Customer" required="">
                        </div>
                    </div>

                    <label for="bidang_usaha" class="col-sm-2 control-label">Bidang Usaha <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                            <select id="bidang_usaha" name="bidang_usaha" class="form-control pil_bidus" style="width: 100%;" tabindex="-1" required>
                                <option value=""></option>
                                <?php foreach ($datbidus as $key => $st) : ?>
                                <option value="<?= $st->bidang_usaha; ?>" <?= set_select('bidang_usaha', $st->bidang_usaha, isset($data->bidang_usaha) && $data->bidang_usaha == $st->bidang_usaha) ?>>
                                <?= $st->bidang_usaha ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="input-group-btn">
                            <a class="btn btn-info" href="#add_bidangusaha" data-toggle="modal" title="Add Bidang Usaha" onclick="add_bidus()">
                            <i class="fa fa-plus">&nbsp;</i>
                            </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group ">
                    <label for="produk_jual" class="col-sm-2 control-label">Produk Penjualan <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-certificate"></i></span>
                        <input type="text" class="form-control" id="produk_jual" name="produk_jual" maxlength="45" value="<?php echo set_value('produk_jual', isset($data->produk_jual) ? $data->produk_jual : ''); ?>" placeholder="Produk Yang Dijual" required="">
                        </div>
                    </div>

                    <label for="kredibilitas" class="col-sm-2 control-label" >Kredibilitas <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <select id="kredibilitas" name="kredibilitas" class="form-control" required>
                            <option value="" <?= set_select('kredibilitas', '', isset($data->kredibilitas) && $data->kredibilitas == ''); ?>>Pilih
                            </option>
                            <option value="A" <?= set_select('kredibilitas', 'A', isset($data->kredibilitas) && $data->kredibilitas == 'A'); ?>>A
                            </option>
                            <option value="B" <?= set_select('kredibilitas', 'B', isset($data->kredibilitas) && $data->kredibilitas == 'B'); ?>>B
                            </option>
                            <option value="C" <?= set_select('kredibilitas', 'C', isset($data->kredibilitas) && $data->kredibilitas == 'C'); ?>>C
                            </option>
                            <option value="D" <?= set_select('kredibilitas', 'D', isset($data->kredibilitas) && $data->kredibilitas == 'D'); ?>>D
                            </option>
                        </select>
                    </div>
                </div>

                <div class="form-group ">
                    <label for="alamat" class="col-sm-2 control-label">Alamat Lengkap <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-street-view"></i></span>
                        <textarea class="form-control" id="alamat" name="alamat" maxlength="255" placeholder="Alamat Lengkap Customer" required="" autofocus="" style="margin: 0px; height: 49px; width: 216px;"><?php echo set_value('alamat', isset($data->alamat) ? $data->alamat : ''); ?></textarea>
                        </div>
                    </div>


                    <label for="provinsi" class="col-sm-2 control-label">Provinsi <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <select id="provinsi" name="provinsi" class="form-control pil_provinsi" style="width: 100%;" tabindex="-1" required onchange="get_kota()">
                            <option value=""></option>
                            <?php foreach ($datprov as $key => $st) : ?>
                            <option value="<?= $st->id_prov; ?>" <?= set_select('provinsi', $st->id_prov, isset($data->provinsi) && $data->provinsi == $st->id_prov) ?>>
                            <?= strtoupper($st->nama); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group ">
                    <label for="kota" class="col-sm-2 control-label">Kota / Kabupaten <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <select id="kota" name="kota" class="form-control pil_kota" style="width: 100%;" tabindex="-1" required>
                            <option value=""></option>
                            <?php foreach ($datkota as $key => $st) : ?>
                            <option value="<?= $st->id_kab; ?>" <?= set_select('kota', $st->id_kab, isset($data->kota) && $data->kota == $st->id_kab) ?>>
                            <?= $st->nama ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <label for="kode_pos" class="col-sm-2 control-label">Kode Pos</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                        <input type="text" class="form-control" id="kode_pos" name="kode_pos" maxlength="15" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" placeholder="Kode Pos" value="<?php echo set_value('kode_pos', isset($data->kode_pos) ? $data->kode_pos : ''); ?>" >
                        </div>
                    </div>
                </div>

                <div class="form-group ">
                    <label for="telpon" class="col-sm-2 control-label">No Telpon</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                        <input type="text" class="form-control" id="telpon" name="telpon" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" maxlength="12" value="<?php echo set_value('telpon', isset($data->telpon) ? $data->telpon : ''); ?>" placeholder="No Telpon">
                        </div>
                    </div>

                    <label for="fax" class="col-sm-2 control-label">No Fax</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-fax"></i></span>
                        <input type="text" class="form-control" id="fax" name="fax" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" maxlength="12" value="<?php echo set_value('fax', isset($data->fax) ? $data->fax : ''); ?>" placeholder="No Fax">
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
                        <textarea class="form-control" id="alamat_npwp" name="alamat_npwp" maxlength="255" placeholder="Alamat NPWP" style="margin: 0px; height: 49px; width: 216px;"><?php echo set_value('alamat_npwp', isset($data->alamat_npwp) ? $data->alamat_npwp : ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-group ">
                    <label for="id_marketing" class="col-sm-2 control-label">Marketing <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <select id="id_marketing" name="id_marketing" class="form-control pil_marketing" style="width: 100%;" tabindex="-1" required>
                            <option value=""></option>
                            <?php foreach ($datmark as $key => $st) : ?>
                            <option value="<?= $st->id_karyawan; ?>" <?= set_select('id_marketing', $st->id_karyawan, isset($data->id_marketing) && $data->id_marketing == $st->id_karyawan) ?>>
                            <?= $st->nama_karyawan ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <label for="referensi" class="col-sm-2 control-label">Referensi <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                            <select id="referensi" name="referensi" class="form-control pil_reff" style="width: 100%;" tabindex="-1" required>
                                <option value=""></option>
                                <?php foreach ($datreff as $key => $st) : ?>
                                <option value="<?= $st->referensi; ?>" <?= set_select('referensi', $st->referensi, isset($data->referensi) && $data->referensi == $st->referensi) ?>>
                                <?= $st->referensi ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="input-group-btn">
                            <a class="btn btn-info" href="#add_referensi" data-toggle="modal" title="Add Referensi" onclick="add_reff()">
                            <i class="fa fa-plus">&nbsp;</i>
                            </a>
                            </div>
                            <!-- /btn-group
                            <input type="text" class="form-control">-->
                        </div>
                    </div>
                </div>

                <div class="form-group ">
                    <label for="website" class="col-sm-2 control-label">Website</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                        <input type="text" class="form-control" id="website" name="website" maxlength="15" placeholder="Website Perusahaan" value="<?php echo set_value('website', isset($data->website) ? $data->website : ''); ?>">
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

        <div class="tab-pane" id="toko">
        <!-- Toko Kerja -->
            <div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;">
            </div>
            <!-- form start-->
            <div class="box box-primary">
            <?= form_open($this->uri->uri_string(),array('id'=>'frm_toko','name'=>'frm_toko','role'=>'form','class'=>'form-horizontal')) ?>
                <div class="box-body">
                <input type="hidden" id="customer" name="customer" value="<?php echo set_value('customer', isset($data->id_customer) ? $data->id_customer : ''); ?>">
                <input type="hidden" id="id_toko" name="id_toko" value="<?php echo set_value('id_toko', isset($data->id_toko) ? $data->id_toko : gen_id_toko($data->id_customer)); ?>">

                <?php  if(isset($data->nm_toko)){$type1='edit';}?>
                <input type="hidden" id="type1" name="type1" value="<?= isset($type1) ? $type1 : 'add' ?>">
                <div class="form-group ">
                    <label for="nm_toko" class="col-sm-2 control-label">Nama Toko</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-building"></i></span>
                        <input type="text" class="form-control" id="nm_toko" name="nm_toko" maxlength="45" value="<?php echo set_value('nm_toko', isset($data->nm_toko) ? $data->nm_toko : ''); ?>" placeholder="Nama Toko" required="">
                        </div>
                    </div>

                    <label for="status_milik" class="col-sm-2 control-label">Status Kepemilikan</label>
                    <div class="col-sm-3">
                        <select id="status_milik" name="status_milik" class="form-control">
                            <option value="MILIK" <?= set_select('status_milik', 'MILIK', isset($data->status_milik) && $data->status_milik == 'MILIK'); ?>>MILIK SENDIRI
                            </option>
                            <option value="SEWA" <?= set_select('status_milik', 'SEWA', isset($data->status_milik) && $data->status_milik == 'SEWA'); ?>>SEWA
                            </option>
                        </select>
                    </div>
                </div>

                <div class="form-group ">
                    <label for="luas" class="col-sm-2 control-label">Luas</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        <input type="text" class="form-control pull-right" id="luas" name="luas" maxlength="5" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" value="<?php echo set_value('luas', isset($data->luas) ? $data->luas : ''); ?>" placeholder="Luas Toko" required>
                        </div>
                    </div>

                    <label for="thn_berdiri" class="col-sm-2 control-label">Tahun Berdiri</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        <input type="text" class="form-control pull-right" id="thn_berdiri" name="thn_berdiri" maxlength="5" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" placeholder="Tahun Berdiri" value="<?php echo set_value('luas', isset($data->luas) ? $data->luas : ''); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="form-group ">
                    <label for="area" class="col-sm-2 control-label">Area</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-group"></i></span>
                        <input type="text" class="form-control" id="area" name="area" maxlength="45" value="<?php echo set_value('area', isset($data->area) ? $data->area : ''); ?>" placeholder="Area" required>
                        </div>
                    </div>

                    <label for="alamat_toko" class="col-sm-2 control-label">Alamat Toko</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-street-view"></i></span>
                        <textarea class="form-control" id="alamat_toko" name="alamat_toko" maxlength="255" placeholder="Alamat Toko" required="" style="margin: 0px; height: 30px; width: 216px;"><?php echo set_value('alamat_toko', isset($data->alamat_toko) ? $data->alamat_toko : ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-group ">
                    <label for="telpon_toko" class="col-sm-2 control-label">Telpon Toko</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-commenting"></i></span>
                        <input type="text" class="form-control" id="telpon_toko" name="telpon_toko" maxlength="15" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" value="<?php echo set_value('telpon_toko', isset($data->telpon_toko) ? $data->telpon_toko : ''); ?>" placeholder="No Telpon Toko" required>
                        </div>
                    </div>

                    <label for="fax_toko" class="col-sm-2 control-label">Fax</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-commenting"></i></span>
                        <input type="text" class="form-control" id="fax_toko" name="fax_toko" maxlength="15" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" value="<?php echo set_value('fax', isset($data->fax_toko) ? $data->fax_toko : ''); ?>" placeholder="No Fax Toko" required>
                        </div>
                    </div>
                </div>

                <div class="box-footer">
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">

                        <button type="submit" name="btntoko" class="btn btn-success" id="btntoko"><i class="fa fa-save">&nbsp;</i>Save</button>

                        <!--<a class="btn btn-success" id='btntoko' href="javascript:void(0)" title="Add" onclick="add_toko()"><i class="fa fa-save">&nbsp;</i>Save</a>-->

                        <a class="btn btn-danger" href="javascript:void(0)" title="Cancel" onclick="cancel()"><i class="fa fa-minus-circle">&nbsp;</i>Cancel</a>

                    </div>
                </div>
                </div>

                </div>
            <?= form_close() ?>
            <div id="list_toko"></div>
            </div>
        <!-- Toko Kerja -->
        </div>

        <div class="tab-pane" id="penagihan">
        <!-- Penagihan Kerja -->
            <div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;">
            </div>
            <!-- form start-->
            <div class="box box-primary">
            <?= form_open($this->uri->uri_string(),array('id'=>'frm_setup_tagihan','name'=>'frm_setup_tagihan','role'=>'form','class'=>'form-horizontal')) ?>
                <div class="box-body">
                <input type="hidden" id="customer" name="customer" value="<?php echo set_value('customer', isset($data->id_customer) ? $data->id_customer : ''); ?>">
                <input type="hidden" id="id_setup_penagihan" name="id_setup_penagihan" value="<?php echo set_value('id_setup_penagihan', isset($data->id_setup_penagihan) ? $data->id_setup_penagihan : get_id_pnghn($data->id_customer)); ?>">

                <?php  if(isset($data->hari_penagihan)){$type1='edit';}?>
                <input type="hidden" id="type1" name="type1" value="<?= isset($type1) ? $type1 : 'add' ?>">                
                <div class="form-group ">                    
                    <label for="jam_penagihan" class="col-sm-2 control-label">Jam Penagihan</label>
                    <div class="col-sm-3">
                    <div class="bootstrap-timepicker">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
                        <input type="text" class="form-control timepicker" id="jam_penagihan" name="jam_penagihan" maxlength="45" value="<?php echo set_value('jam_penagihan', isset($data->jam_penagihan) ? $data->jam_penagihan : ''); ?>" placeholder="Jam Penagihan" required>
                        </div>
                    </div>
                    </div>

                    <label for="alamat_penagihan" class="col-sm-2 control-label">Alamat Penagihan</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-street-view"></i></span>
                        <textarea class="form-control" id="alamat_penagihan" name="alamat_penagihan" maxlength="255" placeholder="Alamat Penagihan" required="" style="margin: 0px; height: 49px; width: 216px;"><?php echo set_value('alamat_penagihan', isset($data->alamat_penagihan) ? $data->alamat_penagihan : ''); ?></textarea>
                        </div>
                    </div>                    
                </div>

                <div class="form-group ">

                    <label for="hari_penagihan" class="col-sm-2 control-label">Hari Penagihan</label>
                    <div class="col-md-3">
                    <div class="box box-info collapsed-box box-solid">
                        <div class="box-header with-border">
                          <h3 class="box-title">Hari</h3>

                          <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                            </button>
                          </div>
                        </div>
                        <div class="box-body">                        
                          <label>
                              <input type="checkbox" class="minimal-red" id="senin" name="senin" value="OK">
                              Senin
                            </label>

                            <label>
                              <input type="checkbox" class="minimal-red" id="selasa" name="selasa" value="OK">
                              Selasa
                            </label>
                                                       
                            <label>
                              <input type="checkbox" class="minimal-red" id="rabu" name="rabu" value="OK">
                              Rabu            
                            </label>
                                                      
                            <label>
                              <input type="checkbox" class="minimal-red" id="kamis" name="kamis" value="OK">
                              Kamis
                            </label>

                            <label>
                              <input type="checkbox" class="minimal-red" id="jumat" name="jumat" value="OK">
                              Jumat
                            </label>
                        </div>
                    </div>                    
                    </div>

                    <label for="alamat_toko" class="col-sm-2 control-label">Persyaratan Penagihan</label>
                    <div class="col-md-3">  
                    <div class="box box-info collapsed-box box-solid">
                        <div class="box-header with-border">
                          <h3 class="box-title">Dokumen</h3>

                          <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                            </button>
                          </div>
                        </div>
                        <div class="box-body">
                          <label>
                              <input type="checkbox" class="minimal-red" id="sj" name="sj" value="OK">
                              Surat Jalan Asli
                            </label>

                            <label>
                              <input type="checkbox" class="minimal-red" id="bp" name="bp" value="OK">
                              Tanda Bukti Pembayaran
                            </label>
                                                       
                            <label>
                              <input type="checkbox" class="minimal-red" id="fp" name="fp" value="OK">
                              Faktur Pajak              
                            </label>
                                                      
                            <label>
                              <input type="checkbox" class="minimal-red" id="ba" name="ba" value="OK">
                              Berita Acara
                            </label>
                        </div>
                    </div>
                    </div>
                </div>

                <div class="box-footer">
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">

                        <button type="submit" name="btnpngh" class="btn btn-success" id="btnpngh"><i class="fa fa-save">&nbsp;</i>Save</button>

                        <!--<a class="btn btn-success" id='btntoko' href="javascript:void(0)" title="Add" onclick="add_toko()"><i class="fa fa-save">&nbsp;</i>Save</a>-->

                        <a class="btn btn-danger" href="javascript:void(0)" title="Cancel" onclick="cancel()"><i class="fa fa-minus-circle">&nbsp;</i>Cancel</a>

                    </div>
                </div>
                </div>

                </div>
            <?= form_close() ?>
            <div id="list_setpenag"></div>
            </div>
        <!-- Penagihan Kerja -->
        </div>

        <div class="tab-pane" id="pembayaran">
        <!-- Pembayaran Kerja -->
            <div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;">
            </div>
            <!-- form start-->
            <div class="box box-primary">
            <?= form_open($this->uri->uri_string(),array('id'=>'frm_setup_pembayaran','name'=>'frm_setup_pembayaran','role'=>'form','class'=>'form-horizontal')) ?>
                <div class="box-body">
                <input type="hidden" id="customer" name="customer" value="<?php echo set_value('customer', isset($data->id_customer) ? $data->id_customer : ''); ?>">
                <input type="hidden" id="id_setup_pembayaran" name="id_setup_pembayaran" value="<?php echo set_value('id_setup_pembayaran', isset($data->id_setup_pembayaran) ? $data->id_setup_pembayaran : get_id_pmbyr($data->id_customer)); ?>">

                <?php  if(isset($data->metode_bayar)){$type1='edit';}?>
                <input type="hidden" id="type1" name="type1" value="<?= isset($type1) ? $type1 : 'add' ?>">
                <div class="form-group ">
                    <label for="metode_bayar" class="col-sm-2 control-label">Metode Pembayaran</label>
                    <div class="col-sm-3">
                     <select id="metode_bayar" name="metode_bayar" class="form-control">
                         <option value="TRANSFER" <?= set_select('metode_bayar', 'TRANSFER', isset($data->metode_bayar) && $data->metode_bayar == 'TRANSFER'); ?>>TRANSFER
                         </option>
                         <option value="CASH" <?= set_select('metode_bayar', 'CASH', isset($data->metode_bayar) && $data->metode_bayar == 'CASH'); ?>>CASH
                         </option>
                         <option value="GIRO" <?= set_select('metode_bayar', 'GIRO', isset($data->metode_bayar) && $data->metode_bayar == 'GIRO'); ?>>GIRO
                         </option>
                     </select>
                    </div>

                    <label for="sistem_bayar" class="col-sm-2 control-label">Sistem Pembayaran</label>
                    <div class="col-sm-3">
                     <select id="sistem_bayar" name="sistem_bayar" class="form-control">
                         <option value="Pembayaran Sebelum Pengiriman" <?= set_select('sistem_bayar', 'Pembayaran Sebelum Pengiriman', isset($data->sistem_bayar) && $data->sistem_bayar == 'Pembayaran Sebelum Pengiriman'); ?>>Pembayaran Sebelum Pengiriman
                         </option>
                         <option value="Progress" <?= set_select('sistem_bayar', 'Progress', isset($data->sistem_bayar) && $data->sistem_bayar == 'Progress'); ?>>Progress
                         </option>
                         <option value="Kredit" <?= set_select('sistem_bayar', 'Kredit', isset($data->sistem_bayar) && $data->sistem_bayar == 'Kredit'); ?>>Kredit
                         </option>
                         <option value="Setelah Project Selesai" <?= set_select('sistem_bayar', 'Setelah Project Selesai', isset($data->sistem_bayar) && $data->sistem_bayar == 'Setelah Project Selesai'); ?>>Setelah Project Selesai
                         </option>
                     </select>
                    </div>
                </div>

                <div class="form-group ">
                    <label for="keterangan" class="col-sm-2 control-label">Keterangan</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-street-view"></i></span>
                        <textarea class="form-control" id="keterangan" name="keterangan" maxlength="255" placeholder="Catatan Lain" required="" style="margin: 0px; height: 30px; width: 216px;"><?php echo set_value('keterangan', isset($data->keterangan) ? $data->keterangan : ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="box-footer">
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">

                        <button type="submit" name="btnpmbyr" class="btn btn-success" id="btnpmbyr"><i class="fa fa-save">&nbsp;</i>Save</button>

                        <!--<a class="btn btn-success" id='btntoko' href="javascript:void(0)" title="Add" onclick="add_toko()"><i class="fa fa-save">&nbsp;</i>Save</a>-->

                        <a class="btn btn-danger" href="javascript:void(0)" title="Cancel" onclick="cancel()"><i class="fa fa-minus-circle">&nbsp;</i>Cancel</a>

                    </div>
                </div>
                </div>

                </div>
            <?= form_close() ?>
            <div id="list_setpembay"></div>
            </div>
        <!-- Pembayaran Kerja -->
        </div>

        <div class="tab-pane" id="pic">
        <!-- PIC Kerja -->
            <div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;">
            </div>
            <!-- form start-->
            <div class="box box-primary">
            <?= form_open($this->uri->uri_string(),array('id'=>'frm_pic','name'=>'frm_pic','role'=>'form','class'=>'form-horizontal')) ?>
                <div class="box-body">
                <input type="hidden" id="customer" name="customer" value="<?php echo set_value('customer', isset($data->id_customer) ? $data->id_customer : ''); ?>">
                <input type="hidden" id="id_pic" name="id_pic" value="<?php echo set_value('id_pic', isset($data->id_pic) ? $data->id_pic : get_id_pic($data->id_customer)); ?>">

                <?php  if(isset($data->hari_penagihan)){$type1='edit';}?>
                <input type="hidden" id="type1" name="type1" value="<?= isset($type1) ? $type1 : 'add' ?>">
                <div class="form-group ">
                    <label for="nm_pic" class="col-sm-2 control-label">Nama PIC</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-building"></i></span>
                        <input type="text" class="form-control" id="nm_pic" name="nm_pic" maxlength="45" value="<?php echo set_value('nm_pic', isset($data->nm_pic) ? $data->nm_pic : ''); ?>" placeholder="Nama InCharge" required="">
                        </div>
                    </div>

                    <label for="divisi" class="col-sm-2 control-label">Divisi</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-building"></i></span>
                        <input type="text" class="form-control" id="divisi" name="divisi" maxlength="45" value="<?php echo set_value('divisi', isset($data->divisi) ? $data->divisi : ''); ?>" placeholder="Divisi" required="">
                        </div>
                    </div>
                </div>

                <div class="form-group ">
                    <label for="jabatan" class="col-sm-2 control-label">Jabatan</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-building"></i></span>
                        <input type="text" class="form-control" id="jabatan" name="jabatan" maxlength="45" value="<?php echo set_value('jabatan', isset($data->jabatan) ? $data->jabatan : ''); ?>" placeholder="Jabatan" required="">
                        </div>
                    </div>

                    <label for="hp" class="col-sm-2 control-label">HP</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-building"></i></span>
                        <input type="text" class="form-control" id="hp" name="hp" maxlength="15" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" placeholder="Nomor hp" value="<?php echo set_value('hp', isset($data->hp) ? $data->hp : ''); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="box-footer">
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">

                        <button type="submit" name="btnpic" class="btn btn-success" id="btnpic"><i class="fa fa-save">&nbsp;</i>Save</button>

                        <!--<a class="btn btn-success" id='btntoko' href="javascript:void(0)" title="Add" onclick="add_toko()"><i class="fa fa-save">&nbsp;</i>Save</a>-->

                        <a class="btn btn-danger" href="javascript:void(0)" title="Cancel" onclick="cancel()"><i class="fa fa-minus-circle">&nbsp;</i>Cancel</a>

                    </div>
                </div>
                </div>

                </div>
            <?= form_close() ?>
            <div id="list_pic"></div>
            </div>
        <!-- Pembayaran Kerja -->
        </div>

        <div class="tab-pane" id="foto">
        <!-- Data foto -->
        <div class="box box-primary"> 
            <form role="form" name="frm_foto" id="frm_foto" 
                  action="javascript:add_foto();" method="post" enctype="multipart/form-data">

            <div class="box-body">

                <div class="form-group ">
                    <input type="hidden" id="id_customer" name="id_customer" value="<?php echo set_value('id_customer', isset($data->id_customer) ? $data->id_customer : ''); ?>"> 
                    <!-- file gambar kita buat pada field hidden -->
                    <input type="hidden" name="filelama" id="filelama" class="form-control" value="<?php echo set_value('filelama', isset($data->foto) ? $data->foto : ''); ?>"> 

                    <label for="foto" class="col-sm-2 control-label">Foto</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <input id="foto" name="foto" type="file">
                        <p class="help-block">Max Image 2 MB</p>
                        </div>
                    </div>            

                    <div class="col-sm-offset-2 col-sm-10">

                        <button type="submit" onclick="javascript:add_foto();" class="btn btn-primary" id="btnfoto">
                        <span class="glyphicon glyphicon-plus"></span>&nbsp;Upload
                        </button>

                        <a class="btn btn-danger" href="javascript:void(0)" title="Cancel" onclick="cancel()"><i class="fa fa-minus-circle">&nbsp;</i>Cancel</a>                            
                    </div>   
                    <p>
                    <div class="col-sm-offset-2 col-sm-10">
                         <div id='list_foto'></div>   
                    </div>                                          
                </div> 
            </div>
            </form>           
        </div>

        </div>

    </div>
    <!-- /.tab-content -->
</div>

<!-- awal untuk modal dialog -->
<!-- Modal Bidus-->
<div class="modal modal-info" id="add_bidangusaha" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title" id="myModalLabel"><span class="fa fa-file-pdf-o"></span>&nbsp;Tambah Bidang Usaha</h4>
            </div>
            <div class="modal-body" id="MyModalBody">
            <form action="#" id="form_bidus">
            <div class="form-group">
                <label for="bidus">Bidang Usaha <font size="4" color="red"><B>*</B></font></label>
                <input type="text" class="form-control" id="bidus" name="bidus" placeholder="Bidang Usaha" required>
            </div>
            <div class="form-group">
                <label for="exampleInputPassword1">Keterangan <font size="4" color="red"><B>*</B></font></label>
                <textarea class="form-control" id="keterangan" name="keterangan" maxlength="255" placeholder="Keterangan" required="" autofocus="" style="margin: 0px; height: 49px; width: 216px;"></textarea>
            </div>
            </form>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="javascript:save_bidus();">Save</button>
            <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- End Modal Bidus-->
<!-- Modal Reff-->
<div class="modal modal-info" id="add_referensi" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title" id="myModalLabel"><span class="fa fa-file-pdf-o"></span>&nbsp;Tambah Referensi</h4>
            </div>
            <div class="modal-body" id="MyModalBody">
            <form action="#" id="form_reff">
            <div class="form-group">
                <label for="reff">Referensi <font size="4" color="red"><B>*</B></font></label>
                <input type="text" class="form-control" id="reff" name="reff" placeholder="Referensi" required>
            </div>
            <div class="form-group">
                <label for="exampleInputPassword1">Keterangan></label>
                <textarea class="form-control" id="keterangan" name="keterangan" maxlength="255" placeholder="Keterangan" autofocus="" style="margin: 0px; height: 49px; width: 216px;"></textarea>
            </div>
            </form>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="javascript:save_reff();">Save</button>
            <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- End Modal Reff-->
<script type="text/javascript">

    $(document).ready(function() {
        var type = $('#type').val();
        if(type=='edit'){
            ShowOtherButton();
        }else{
            HideOtherButton();
        }
        
        $("#kota2").select2({
                    placeholder: "Please Select"
        });

        $(".multiple2").select2({
            placeholder: "Pilih Hari",
            allowClear: true
        });

        $(".pil_bidus").select2({
            placeholder: "Pilih Bidang Usaha",
            allowClear: true
        });

        $(".pil_reff").select2({
            placeholder: "Pilih Referensi",
            allowClear: true
        });

        $(".pil_provinsi").select2({
            placeholder: "Pilih",
            allowClear: true
        });

        $(".pil_kota").select2({
            placeholder: "Pilih",
            allowClear: true
        });

        $(".pil_marketing").select2({
            placeholder: "Pilih",
            allowClear: true
        });

        $('#data_toko').click(function(){
            var id = $('#id_customer').val();
            if(id==''){
                $("#list_toko").hide();
            }else{
                load_toko_temp(id);
            }
        });

        $('#data_penag').click(function(){
            var id = $('#id_customer').val();
            if(id==''){
                $("#list_setpenag").hide();
            }else{
                load_setpenag(id);
            }
        });

        $('#data_pemba').click(function(){
            var id = $('#id_customer').val();
            if(id==''){
                $("#list_setpembay").hide();
            }else{
                load_setpembay(id);
            }
        });

        $('#data_pic').click(function(){
            var id = $('#id_customer').val();
            if(id==''){
                $("#list_pic").hide();
            }else{
                load_pic(id);
            }
        });

        $('#data_foto').click(function(){
            var id = $('#id_customer').val();
            if(id==''){
                $("#list_foto").hide();
            }else{
                load_foto(id);
            }
        });                

        //Red color scheme for iCheck
        $('input[type="checkbox"].minimal-red, input[type="radio"].minimal-red').iCheck({
          checkboxClass: 'icheckbox_minimal-red',
          radioClass: 'iradio_minimal-red'
        });

        //Timepicker
        $(".timepicker").timepicker({
          showInputs: true
        });
    });

    //Bidang Usaha
    function save_bidus(){

    var bidang_usaha=$("#bidus").val();
    var keterangan=$("#keterangan").val();

    if(bidang_usaha=='' || keterangan==''){
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
            url:siteurl+"customer/add_bidangusaha",
            data:"bidang_usaha="+bidang_usaha+"&keterangan="+keterangan,
            success:function(html){
                $('#add_bidangusaha').modal('hide');
                $('#form_bidus')[0].reset(); // reset form on modals
                get_bidus();
            }
        });
    }

    }

    function get_bidus(){
        $.ajax({
            type:"GET",
            url:siteurl+"customer/get_bidus",
            success:function(html){
               $("#bidang_usaha").html(html);
            }
        });
    }

    //Reff
    function save_reff(){

    var referensi=$("#reff").val();
    var keterangan=$("#keterangan").val();

    if(referensi==''){
        swal({
        title: "Peringatan!",
        text: "Isi Data Referensi!",
        type: "warning",
        confirmButtonText: "Ok"
        });
        //die;
    }else{
        $.ajax({
            type:"POST",
            url:siteurl+"customer/add_referensi",
            data:"referensi="+referensi+"&keterangan="+keterangan,
            success:function(html){
                $('#add_referensi').modal('hide');
                $('#form_reff')[0].reset(); // reset form on modals
                get_reff();
            }
        });
    }

    }

    function get_reff(){
        $.ajax({
            type:"GET",
            url:siteurl+"customer/get_reff",
            success:function(html){
                $("#referensi").html(html);
            }
        });
    }

    function get_kota(){
        var provinsi=$("#provinsi").val();
        $.ajax({
            type:"GET",
            url:siteurl+"customer/get_kota",
            data:"provinsi="+provinsi,
            success:function(html){
               $("#kota").html(html);
            }
        });
    }

    //Biodata
    $('#frm_biodata').on('submit', function(e){
        e.preventDefault();
        var formdata = $("#frm_biodata").serialize();
        $.ajax({
            url: siteurl+"customer/save_customer_ajax",
            dataType : "json",
            type: 'POST',
            data: formdata,
            //alert(msg);
            success: function(msg){
                if(msg['save']=='1'){
                    var customer =msg['customer'];
                    swal({
                      title: "Sukses!",
                      text: "Data Berhasil Disimpan, Lanjutkan pengisian data Toko",
                      type: "success",
                      showCancelButton: true,
                      confirmButtonColor: "Blue",
                      confirmButtonText: "Ya, Lanjutkan",
                      cancelButtonText: "Tidak, Lain waktu saja",
                      closeOnConfirm: true,
                      closeOnCancel: true
                    },
                    function(isConfirm){
                      if (isConfirm) {
                        $('[href="#toko"]').tab('show');
                        $('#customer').val(customer);
                        get_idtoko(customer);
                        load_toko_temp(customer);
                        ShowOtherButton();
                      } else {
                        window.location.reload();
                        cancel();
                      }
                    });
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

    //Toko
    function get_idtoko(customer){
     $.ajax({
         dataType : "json",
         type: 'POST',
         url:siteurl+"customer/get_idtoko",
         data:"customer="+customer,
         success:function(msg){
             var id_toko =msg['id_toko'];
             $("#id_toko").val(id_toko);
         }
     })
    }    

    $('#frm_toko').on('submit', function(e){
        e.preventDefault();
        var formdata = $("#frm_toko").serialize();
        $.ajax({
            url: siteurl+"customer/save_toko_ajax",
            dataType : "json",
            type: 'POST',
            data: formdata,
            success: function(msg){
                if(msg['save']=='1'){
                    var customer =msg['customer'];
                    swal({
                      title: "Sukses!",
                      text: "Data Berhasil Disimpan, Lanjutkan pengisian data Sistem Penagihan",
                      type: "success",
                      showCancelButton: true,
                      confirmButtonColor: "Blue",
                      confirmButtonText: "Ya, Lanjutkan",
                      cancelButtonText: "Tidak, Lain waktu saja",
                      closeOnConfirm: true,
                      closeOnCancel: true
                    },
                    function(isConfirm){
                      if (isConfirm) {
                        $('[href="#penagihan"]').tab('show');
                        $('#id_customer').val(customer);
                        get_idsetpen(customer);
                        load_setpenag(customer);
                        ShowOtherButton();
                      } else {
                        load_toko_temp(customer);  
                        $('#id_customer').val(customer);
                        get_idtoko(customer);                      
                      }
                    });
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

    function get_idsetpen(customer){
     $.ajax({
         dataType : "json",
         type: 'POST',
         url:siteurl+"customer/get_idsetpen",
         data:"customer="+customer,
         success:function(msg){
             var id_setup_penagihan =msg['id_setup_penagihan'];
             $("#id_setup_penagihan").val(id_setup_penagihan);
         }
     })
    }

    //setup Tagihan
    $('#frm_setup_tagihan').on('submit', function(e){
        e.preventDefault();
        var formdata = $("#frm_setup_tagihan").serialize();
        $.ajax({
            url: siteurl+"customer/save_setup_tagihan",
            dataType : "json",
            type: 'POST',
            data: formdata,
            success: function(msg){
                if(msg['save']=='1'){
                    var customer =msg['customer'];
                    swal({
                      title: "Sukses!",
                      text: "Data Berhasil Disimpan, Lanjutkan pengisian data Set Up Pembayaran",
                      type: "success",
                      showCancelButton: true,
                      confirmButtonColor: "Blue",
                      confirmButtonText: "Ya, Lanjutkan",
                      cancelButtonText: "Tidak, Lain waktu saja",
                      closeOnConfirm: true,
                      closeOnCancel: true
                    },
                    function(isConfirm){
                      if (isConfirm) {
                        $('[href="#pembayaran"]').tab('show');
                        $('#id_customer').val(customer);
                        get_idsetpem(customer);
                        load_setpembay(customer);
                        ShowOtherButton();
                      } else {
                        load_setpenag(customer);
                        $('#id_customer').val(customer);
                        get_idsetpen(customer);
                      }
                    });
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

    function get_idsetpem(customer){
     $.ajax({
         dataType : "json",
         type: 'POST',
         url:siteurl+"customer/get_idsetpem",
         data:"customer="+customer,
         success:function(msg){
             var id_setup_pembayaran =msg['id_setup_pembayaran'];
             $("#id_setup_pembayaran").val(id_setup_pembayaran);
         }
     })
    }

    //PIC
    $('#frm_pic').on('submit', function(e){
        e.preventDefault();
        var formdata = $("#frm_pic").serialize();
        $.ajax({
            url: siteurl+"customer/save_pic",
            dataType : "json",
            type: 'POST',
            data: formdata,
            success: function(msg){
                if(msg['save']=='1'){
                    var customer =msg['customer'];
                    swal({
                      title: "Sukses!",
                      text: "Data Berhasil Disimpan, Lanjutkan pengisian data PIC",
                      type: "success",
                      showCancelButton: true,
                      confirmButtonColor: "Blue",
                      confirmButtonText: "Ya, Lanjutkan",
                      cancelButtonText: "Tidak, Lain waktu saja",
                      closeOnConfirm: true,
                      closeOnCancel: true
                    },
                    function(isConfirm){
                      if (isConfirm) {
                        $('[href="#foto"]').tab('show');
                        $('#id_customer').val(customer);
                        load_foto(customer);
                        ShowOtherButton();
                      } else {
                        load_pic(customer);
                        $('#id_customer').val(customer);
                        get_idpic(customer);
                      }
                    });
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
    
    //FOTO
    function add_foto(){
    var foto        =   $('#foto').val();
    var id_customer =   $('#id_customer').val();
    
    $('#frm_foto').ajaxForm({
     url:siteurl+"customer/add_datafoto",
     dataType : "json",
     type: "post",
     data:{"foto":foto,"id_customer":id_customer},
     success:function(msg){
        var pesan = msg['save'];
        var gambar = msg['gambar'];
        if(msg['save']=='1'){    
            swal({
                title: "SUKSES!",
                text: "Sukses Upload Foto Kendaraan",
                type: "success",
                timer: 1500,
                showConfirmButton: false
                });

            load_foto(id_customer);
            $('#filelama').val(gambar);
                //nextDataUrgen();
        }else{
            swal({
                title: "Gagal!",
                text: pesan,
                type: "error",
                //timer: 1500,
                showConfirmButton: true
            });
        };//alert(msg); 
    },
        error: function(){            
            swal({
                title: "Gagal!",
                text: "Gagal Eksekusi Ajax",
                type: "error",
                timer: 1500,
                showConfirmButton: false
            });
        }
    });     

    }    
    /*
    //FOTO
    $('#frm_foto').on('submit', function(e){
        e.preventDefault();
        var formdata = $("#frm_foto").serialize();
        $.ajax({
            url: siteurl+"customer/add_datafoto",
            dataType : "json",
            type: 'POST',
            data: formdata,
            success: function(msg){
                if(msg['save']=='1'){
                    var customer =msg['customer'];
                    swal({
                    title: "SUKSES!",
                    text: "Sukses Upload Foto",
                    type: "success",
                    timer: 1500,
                    showConfirmButton: false
                    });
                    load_foto(customer);
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
    */

    function get_idpic(customer){
     $.ajax({
         dataType : "json",
         type: 'POST',
         url:siteurl+"customer/get_idpic",
         data:"customer="+customer,
         success:function(msg){
             var id_pic =msg['id_pic'];
             $("#id_pic").val(id_pic);
         }
     })
    }

    function cancel(){
        $(".box").show();
        $("#form-area").hide();
        //window.location.reload();
        reload_table();
    }

    function reload_table(){
        //table.ajax.reload(null,false); //reload datatable ajax
        table.ajax.reload();
    }

    function load_toko_temp(id_customer){
    $.ajax({
        type:"GET",
        url:siteurl+"customer/load_toko",
        data:"id_customer="+id_customer,
        success:function(html){
            $("#list_toko").html(html);
        }
    })
    }

    function load_setpenag(id_customer){
    $.ajax({
        type:"GET",
        url:siteurl+"customer/load_setpenag",
        data:"id_customer="+id_customer,
        success:function(html){
            $("#list_setpenag").html(html);
        }
    })
    }

    function load_setpembay(id_customer){
    $.ajax({
        type:"GET",
        url:siteurl+"customer/load_setpembay",
        data:"id_customer="+id_customer,
        success:function(html){
            $("#list_setpembay").html(html);
        }
    })
    }

    function load_pic(id_customer){
    $.ajax({
        type:"GET",
        url:siteurl+"customer/load_pic",
        data:"id_customer="+id_customer,
        success:function(html){
            $("#list_pic").html(html);
        }
    })
    }
    
    function load_foto(id_customer){
    $.ajax({
        type:"GET",
        url:siteurl+"customer/load_foto",
        data:"id_customer="+id_customer,
        success:function(html){
            $("#list_foto").html(html);
        }
    })
    }

    function ShowOtherButton()
    {
        //after success saving then activate sumbit button on each tab
        $("#btntoko").show();
        $("#btnpngh").show();
        $("#btnpngh").show();
        $("#btnpmbyr").show();
        $("#btnpic").show();
        $("#btnfoto").show();
    }

    function HideOtherButton()
    {
        //after success saving then activate sumbit button on each tab
        $("#btntoko").hide();
        $("#btnpngh").hide();
        $("#btnpngh").hide();
        $("#btnpmbyr").hide();
        $("#btnpic").hide();
        $("#btnfoto").hide();
    }

    //Delete Toko
   	function hapus_toko(id){
   		//alert(id);
   		swal({
   		  title: "Anda Yakin?",
   		  text: "Data Toko Akan Terhapus secara Permanen!",
   		  type: "warning",
   		  showCancelButton: true,
   		  confirmButtonColor: "#DD6B55",
   		  confirmButtonText: "Ya, delete!",
   		  cancelButtonText: "Tidak!",
   		  closeOnConfirm: false,
   		  closeOnCancel: true
   		},
   		function(isConfirm){
   		  if (isConfirm) {
   		  	$.ajax({
   		            url: siteurl+'customer/hapus_toko/'+id,
   		            dataType : "json",
   		            type: 'POST',
   		            success: function(msg){
   		                if(msg['delete']=='1'){
   		                    $("#dataku"+id).hide(2000);
   		                    //swal("Terhapus!", "Data berhasil dihapus.", "success");
   		                    swal({
   		                      title: "Terhapus!",
   		                      text: "Data berhasil dihapus",
   		                      type: "success",
   		                      timer: 1500,
   		                      showConfirmButton: false
   		                    });
   		                } else {
   		                    swal({
   		                      title: "Gagal!",
   		                      text: "Data gagal dihapus",
   		                      type: "error",
   		                      timer: 1500,
   		                      showConfirmButton: false
   		                    });
   		                };
   		            },
   		            error: function(){
   		                swal({
   	                      title: "Gagal!",
   	                      text: "Gagal Eksekusi Ajax",
   	                      type: "error",
   	                      timer: 1500,
   	                      showConfirmButton: false
   	                    });
   		            }
   		        });
   		  } else {
   		    //cancel();
   		  }
   		});
   	}

    function hapus_pengh(id){
   		//alert(id);
   		swal({
   		  title: "Anda Yakin?",
   		  text: "Data Toko Akan Terhapus secara Permanen!",
   		  type: "warning",
   		  showCancelButton: true,
   		  confirmButtonColor: "#DD6B55",
   		  confirmButtonText: "Ya, delete!",
   		  cancelButtonText: "Tidak!",
   		  closeOnConfirm: false,
   		  closeOnCancel: true
   		},
   		function(isConfirm){
   		  if (isConfirm) {
   		  	$.ajax({
   		            url: siteurl+'customer/hapus_pengh/'+id,
   		            dataType : "json",
   		            type: 'POST',
   		            success: function(msg){
   		                if(msg['delete']=='1'){
   		                    $("#dataku"+id).hide(2000);
   		                    //swal("Terhapus!", "Data berhasil dihapus.", "success");
   		                    swal({
   		                      title: "Terhapus!",
   		                      text: "Data berhasil dihapus",
   		                      type: "success",
   		                      timer: 1500,
   		                      showConfirmButton: false
   		                    });
   		                } else {
   		                    swal({
   		                      title: "Gagal!",
   		                      text: "Data gagal dihapus",
   		                      type: "error",
   		                      timer: 1500,
   		                      showConfirmButton: false
   		                    });
   		                };
   		            },
   		            error: function(){
   		                swal({
   	                      title: "Gagal!",
   	                      text: "Gagal Eksekusi Ajax",
   	                      type: "error",
   	                      timer: 1500,
   	                      showConfirmButton: false
   	                    });
   		            }
   		        });
   		  } else {
   		    //cancel();
   		  }
   		});
   	}

    function hapus_pic(id){
   		//alert(id);
   		swal({
   		  title: "Anda Yakin?",
   		  text: "Data Toko Akan Terhapus secara Permanen!",
   		  type: "warning",
   		  showCancelButton: true,
   		  confirmButtonColor: "#DD6B55",
   		  confirmButtonText: "Ya, delete!",
   		  cancelButtonText: "Tidak!",
   		  closeOnConfirm: false,
   		  closeOnCancel: true
   		},
   		function(isConfirm){
   		  if (isConfirm) {
   		  	$.ajax({
   		            url: siteurl+'customer/hapus_pic/'+id,
   		            dataType : "json",
   		            type: 'POST',
   		            success: function(msg){
   		                if(msg['delete']=='1'){
   		                    $("#dataku"+id).hide(2000);
   		                    //swal("Terhapus!", "Data berhasil dihapus.", "success");
   		                    swal({
   		                      title: "Terhapus!",
   		                      text: "Data berhasil dihapus",
   		                      type: "success",
   		                      timer: 1500,
   		                      showConfirmButton: false
   		                    });
   		                } else {
   		                    swal({
   		                      title: "Gagal!",
   		                      text: "Data gagal dihapus",
   		                      type: "error",
   		                      timer: 1500,
   		                      showConfirmButton: false
   		                    });
   		                };
   		            },
   		            error: function(){
   		                swal({
   	                      title: "Gagal!",
   	                      text: "Gagal Eksekusi Ajax",
   	                      type: "error",
   	                      timer: 1500,
   	                      showConfirmButton: false
   	                    });
   		            }
   		        });
   		  } else {
   		    //cancel();
   		  }
   		});
   	}
       
    function hapus_pembay(id){
   		//alert(id);
   		swal({
   		  title: "Anda Yakin?",
   		  text: "Data Toko Akan Terhapus secara Permanen!",
   		  type: "warning",
   		  showCancelButton: true,
   		  confirmButtonColor: "#DD6B55",
   		  confirmButtonText: "Ya, delete!",
   		  cancelButtonText: "Tidak!",
   		  closeOnConfirm: false,
   		  closeOnCancel: true
   		},
   		function(isConfirm){
   		  if (isConfirm) {
   		  	$.ajax({
   		            url: siteurl+'customer/hapus_pembay/'+id,
   		            dataType : "json",
   		            type: 'POST',
   		            success: function(msg){
   		                if(msg['delete']=='1'){
   		                    $("#dataku"+id).hide(2000);
   		                    //swal("Terhapus!", "Data berhasil dihapus.", "success");
   		                    swal({
   		                      title: "Terhapus!",
   		                      text: "Data berhasil dihapus",
   		                      type: "success",
   		                      timer: 1500,
   		                      showConfirmButton: false
   		                    });
   		                } else {
   		                    swal({
   		                      title: "Gagal!",
   		                      text: "Data gagal dihapus",
   		                      type: "error",
   		                      timer: 1500,
   		                      showConfirmButton: false
   		                    });
   		                };
   		            },
   		            error: function(){
   		                swal({
   	                      title: "Gagal!",
   	                      text: "Gagal Eksekusi Ajax",
   	                      type: "error",
   	                      timer: 1500,
   	                      showConfirmButton: false
   	                    });
   		            }
   		        });
   		  } else {
   		    //cancel();
   		  }
   		});
   	}
</script>
