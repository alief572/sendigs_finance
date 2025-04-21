<style>
img {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 5px;
    width: 50px;
}

img:hover {
    box-shadow: 0 0 2px 1px rgba(0, 140, 186, 0.5);
}
</style>
<div class="nav-tabs-custom">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#biodata" data-toggle="tab" aria-expanded="true" id="data">Master Customer</a></li>
        <li class=""><a href="#cust_pic" data-toggle="tab" aria-expanded="false" id="data_pic">PIC Customer</a></li>
        <!--<li class=""><a href="#foto" data-toggle="tab" aria-expanded="false" id="data_foto">Foto Customer</a></li>-->
        <li class=""><a href="#toko" data-toggle="tab" aria-expanded="false" id="data_toko">Toko</a></li>
        <li class=""><a href="#pic_toko" data-toggle="tab" aria-expanded="false" id="data_foto_toko">Foto Toko</a></li>
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
                        <input type="text" class="form-control" id="nm_customer" name="nm_customer" maxlength="45" value="<?php echo set_value('nm_customer', isset($data->nm_customer) ? $data->nm_customer : ''); ?>" placeholder="Nama Customer" required style="text-transform:uppercase">
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
                        <input type="text" class="form-control" id="produk_jual" name="produk_jual" maxlength="45" value="<?php echo set_value('produk_jual', isset($data->produk_jual) ? $data->produk_jual : ''); ?>" placeholder="Produk Yang Dijual" required style="text-transform:uppercase">
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
                        <textarea class="form-control" style="text-transform:uppercase" id="alamat" name="alamat" maxlength="255" placeholder="Alamat Lengkap Customer" required="" autofocus="" style="margin: 0px; height: 49px; width: 216px;"><?php echo set_value('alamat', isset($data->alamat) ? $data->alamat : ''); ?></textarea>
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
                        <input type="text" class="form-control" id="npwp" name="npwp" maxlength="25" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" placeholder="Nomor Pokok Wajib Pajak" value="<?php echo set_value('npwp', isset($data->npwp) ? $data->npwp : ''); ?>">
                        </div>
                    </div>

                    <label for="alamat_npwp" class="col-sm-2 control-label">Alamat NPWP</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-street-view"></i></span>
                        <textarea class="form-control" style="text-transform:uppercase" id="alamat_npwp" name="alamat_npwp" maxlength="255" placeholder="Alamat NPWP" style="margin: 0px; height: 49px; width: 216px;"><?php echo set_value('alamat_npwp', isset($data->alamat_npwp) ? $data->alamat_npwp : ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-group ">
                    <label for="ktp" class="col-sm-2 control-label">KTP</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                        <input type="text" class="form-control" id="ktp" name="ktp" maxlength="25" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" placeholder="Nomor KTP" value="<?php echo set_value('ktp', isset($data->ktp) ? $data->ktp : ''); ?>">
                        </div>
                    </div>

                    <label for="alamat_ktp" class="col-sm-2 control-label">Alamat KTP</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-street-view"></i></span>
                        <textarea class="form-control" style="text-transform:uppercase" id="alamat_ktp" name="alamat_ktp" maxlength="255" placeholder="Alamat KTP" style="margin: 0px; height: 49px; width: 216px;"><?php echo set_value('alamat_ktp', isset($data->alamat_ktp) ? $data->alamat_ktp : ''); ?></textarea>
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
                        <input type="text" style="text-transform:uppercase" class="form-control" id="website" name="website" maxlength="15" placeholder="Website Perusahaan" value="<?php echo set_value('website', isset($data->website) ? $data->website : ''); ?>">
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
                    <label for="diskon_toko" class="col-sm-2 control-label">Diskon Toko</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                        <input type="text" class="form-control" id="diskon_toko" name="diskon_toko" placeholder="Diskon Toko" value="<?php echo set_value('diskon_toko', isset($data->diskon_toko) ? $data->diskon_toko : ''); ?>" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');">
                        </div>
                    </div>
					 <label for="diskon_toko" class="col-sm-2 control-label">Limit Piutang</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-money"></i></span>
                        <?php
							echo form_input(array('readonly'=>'readonly','id'=>'limit_piutang','name'=>'limit_piutang','class'=>'form-control input-sm harga','data-decimal'=>'.','data-thousand'=>'','data-precision'=>'0','data-allow-zero'=>false,'value'=>set_value('nm_customer', isset($data->limit_piutang) ? number_format($data->limit_piutang) :0)));
						?>
                        </div>
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
            <?= form_open_multipart('Customer/save_toko_ajax',array('id'=>'frm_toko','name'=>'frm_toko','role'=>'form','class'=>'form-horizontal')) ?>
                <div class="box-body">
                <input type="hidden" id="customer" name="customer" value="<?php echo set_value('customer', isset($data->id_customer) ? $data->id_customer : ''); ?>">
                <input type="hidden" id="id_toko" name="id_toko" value="<?php echo set_value('id_toko', isset($data->id_toko) ? $data->id_toko : gen_id_toko($data->id_customer)); ?>">

                <?php  if(isset($data->nm_toko)){$type1='edit';}?>
                <input type="hidden" id="type1" name="type1" value="<?= isset($type1) ? $type1 : 'add' ?>">
                <div class="form-group ">
                    <label for="nm_toko" class="col-sm-2 control-label">Nama Toko <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-building"></i></span>
                        <input type="text" class="form-control" id="nm_toko" name="nm_toko" maxlength="45" value="<?php echo set_value('nm_toko', isset($data->nm_toko) ? $data->nm_toko : ''); ?>" placeholder="Nama Toko" required style="text-transform:uppercase">
                        </div>
                    </div>

                    <label for="status_milik" class="col-sm-2 control-label">Status Kepemilikan <font size="4" color="red"><B>*</B></font></label>
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
                    <label for="luas" class="col-sm-2 control-label">Luas <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        <input type="text" class="form-control pull-right" id="luas" name="luas" maxlength="5" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" value="<?php echo set_value('luas', isset($data->luas) ? $data->luas : ''); ?>" placeholder="Luas Toko" required>
                        </div>
                    </div>

                    <label for="thn_berdiri" class="col-sm-2 control-label">Tahun Berdiri <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        <input type="text" class="form-control pull-right" id="thn_berdiri" name="thn_berdiri" maxlength="5" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" placeholder="Tahun Berdiri" value="<?php echo set_value('luas', isset($data->luas) ? $data->luas : ''); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="form-group ">
                    <label for="area" class="col-sm-2 control-label">Area <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-group"></i></span>
                        <input type="text" style="text-transform:uppercase" class="form-control" id="area" name="area" maxlength="45" value="<?php echo set_value('area', isset($data->area) ? $data->area : ''); ?>" placeholder="Area" required>
                        </div>
                    </div>

                    <label for="alamat_toko" class="col-sm-2 control-label">Alamat Toko <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-street-view"></i></span>
                        <textarea class="form-control" style="text-transform:uppercase" id="alamat_toko" name="alamat_toko" maxlength="255" placeholder="Alamat Toko" required="" style="margin: 0px; height: 30px; width: 216px;"><?php echo set_value('alamat_toko', isset($data->alamat_toko) ? $data->alamat_toko : ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-group ">
                    <label for="telpon_toko" class="col-sm-2 control-label">Telpon Toko</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-commenting"></i></span>
                        <input type="text" class="form-control" id="telpon_toko" name="telpon_toko" maxlength="15" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" value="<?php echo set_value('telpon_toko', isset($data->telpon_toko) ? $data->telpon_toko : ''); ?>" placeholder="No Telpon Toko">
                        </div>
                    </div>

                    <label for="fax_toko" class="col-sm-2 control-label">Fax</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-commenting"></i></span>
                        <input type="text" class="form-control" id="fax_toko" name="fax_toko" maxlength="15" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" value="<?php echo set_value('fax', isset($data->fax_toko) ? $data->fax_toko : ''); ?>" placeholder="No Fax Toko">
                        </div>
                    </div>
                </div>

                <div class="box-footer">
                <div class="form-group ">
                    <label for="pic" class="col-sm-2 control-label">Nama PIC <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <select id="pic" name="pic" class="form-control pil_pic" style="width: 100%;" tabindex="-1" required onchange="get_emailpic()">
                            <option value=""></option>
                            <?php foreach ($datpic as $key => $st) : ?>
                            <option value="<?= $st->id_pic; ?>" <?= set_select('pic', $st->id_pic, isset($data->pic) && $data->pic == $st->id_pic) ?>>
                            <?= strtoupper($st->nm_pic); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <label for="hp_pic" class="col-sm-2 control-label">HP <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-building"></i></span>
                        <input type="text" class="form-control" id="hp_pic" name="hp_pic" maxlength="15" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" placeholder="Nomor hp" value="<?php echo set_value('hp_pic', isset($data->hp_pic) ? $data->hp_pic : ''); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="form-group ">
                    <label for="email" class="col-sm-2 control-label">Email</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-building"></i></span>
                        <input type="text" style="text-transform:uppercase" class="form-control" id="email" name="email" maxlength="45" value="<?php echo set_value('email', isset($data->email) ? $data->email : ''); ?>" placeholder="Email">
                        </div>
                    </div>
                </div>
                </div>

                <div class="box-footer">
                <div class="form-group ">
                    <label for="jam_tagih" class="col-sm-2 control-label">Jam Penagihan</label>
                    <div class="col-sm-3">
                    <div class="input-group bootstrap-timepicker timepicker">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-time"></i></span>
                        <input type="text" class="form-control" id="jam_tagih" name="jam_tagih" maxlength="45" value="<?php echo set_value('jam_tagih', isset($data->jam_tagih) ? $data->jam_tagih : ''); ?>" placeholder="Jam Penagihan">
                    </div>
                    </div>

                    <label for="alamat_tagih" class="col-sm-2 control-label">Alamat Penagihan <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-street-view"></i></span>
                        <textarea style="text-transform:uppercase" class="form-control" id="alamat_tagih" name="alamat_tagih" maxlength="255" placeholder="Alamat Penagihan" required="" style="margin: 0px; height: 49px; width: 216px;"><?php echo set_value('alamat_tagih', isset($data->alamat_tagih) ? $data->alamat_tagih : ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="hari_tagih" class="col-sm-2 control-label">Hari Penagihan</label>
                    <div class="col-md-3">
                    <select id="hari_tagih" name="hari_tagih[]" style="width: 100%;" tabindex="-1" multiple="multiple"  aria-hidden="true">
                        <option value="Senin">Senin</option>
                        <option value="Selasa">Selasa</option>
                        <option value="Rabu">Rabu</option>
                        <option value="Kamis">Kamis</option>
                        <option value="Jum'at">Jum'at</option>
                    </select>
                    </div>

                    <label for="syarat_dokumen" class="col-sm-2 control-label">Persyaratan Penagihan <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-md-3">
                        <div class="input-group">
                            <select id="syarat_dokumen" name="syarat_dokumen[]" class="form-control" style="width: 100%;" tabindex="-1" multiple="multiple" required>
                                <option value=""></option>
                                <?php foreach ($datdok as $key => $st) : ?>
                                <option value="<?= $st->nm_syarat; ?>" <?= set_select('syarat_dokumen', $st->nm_syarat, isset($data->syarat_dokumen) && $data->syarat_dokumen == $st->nm_syarat) ?>>
                                <?= $st->nm_syarat ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="input-group-btn">
                            <a class="btn btn-info" href="#add_syaratdok" data-toggle="modal" title="Add Syarat Penagihan" onclick="add_syardok()">
                            <i class="fa fa-plus">&nbsp;</i>
                            </a>
                            </div>
                        </div>
                    <!--
                    <select id="syarat_dokumen" name="syarat_dokumen[]" style="width: 100%;" tabindex="-1" multiple="multiple"  aria-hidden="true" required>
                        <option value="Surat Jalan">Surat Jalan</option>
                        <option value="Faktur Pajak">Faktur Pajak</option>
                        <option value="Berita Acara">Berita Acara</option>
                        <option value="Bukti Pembayaran">Bukti Pembayaran</option>
                    </select>
                    -->
                    </div>
                </div>
                </div>

                <div class="box-footer">
                <div class="form-group ">
                    <label for="metode_bayar" class="col-sm-2 control-label">Metode Pembayaran <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                     <select id="metode_bayar" name="metode_bayar[]" style="width: 100%;" tabindex="-1" multiple="multiple"  aria-hidden="true" required>
                        <option value="TRANSFER">TRANSFER</option>
                        <option value="CASH">CASH</option>
                        <option value="GIRO">GIRO</option>
                    </select>
                    </div>

                    <label for="sistem_bayar" class="col-sm-2 control-label">Sistem Pembayaran <font size="4" color="red"><B>*</B></font></label>
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

                <div class="form-group" id="row_kredit">
                    <label for="kredit_limit" class="col-sm-2 control-label">Kredit Limit</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-commenting"></i></span>
                        <input type="text" class="form-control" id="kredit_limit" name="kredit_limit" maxlength="15" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" value="<?php echo set_value('kredit_limit', isset($data->kredit_limit) ? $data->kredit_limit : ''); ?>" placeholder="Kredit Limit">
                        </div>
                    </div>

                    <label for="termin_bayar" class="col-sm-2 control-label">Termin Pembayaran</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-commenting"></i></span>
                        <input type="text" class="form-control" id="termin_bayar" name="termin_bayar" maxlength="15" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" value="<?php echo set_value('termin_bayar', isset($data->termin_bayar) ? $data->termin_bayar : ''); ?>" placeholder="Termin Pembayaran dalam Hari">
                        <div class="input-group-btn">
                            <a class="btn btn-info">Hari
                            </a>
                        </div>
                        </div>
                    </div>
                </div>
                </div>

                <!--
                <div class="box-footer">
                <label for="foto_toko" class="col-sm-2 control-label">Foto Toko</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <input class="form-control" id="foto_toko" name="foto_toko" type="file">
                        <p class="help-block">Max Image 2 MB</p>
                        </div>
                    </div>
                </div>
                -->
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

        <div class="tab-pane" id="cust_pic">
          <!-- PIC Kerja -->
            <div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;">
            </div>
            <!-- form start-->
            <div class="box box-primary">
            <?= form_open($this->uri->uri_string(),array('id'=>'frm_pic','name'=>'frm_pic','role'=>'form','class'=>'form-horizontal')) ?>
                <div class="box-body">
                <input type="hidden" id="customerx" name="customerx" value="<?php echo set_value('customerx', isset($data->id_customer) ? $data->id_customer : ''); ?>">
                <input type="hidden" id="id_pic" name="id_pic" value="<?php echo set_value('id_pic', isset($data->id_pic) ? $data->id_pic : get_id_pic($data->id_customer)); ?>">

                <?php  if(isset($data->hari_penagihan)){$type1='edit';}?>
                <input type="hidden" id="type1" name="type1" value="<?= isset($type1) ? $type1 : 'add' ?>">
                <div class="form-group ">
                    <label for="nm_pic" class="col-sm-2 control-label">Nama PIC <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-building"></i></span>
                        <input type="text" class="form-control" id="nm_pic" name="nm_pic" maxlength="45" value="<?php echo set_value('nm_pic', isset($data->nm_pic) ? $data->nm_pic : ''); ?>" placeholder="Nama InCharge" required style="text-transform:uppercase">
                        </div>
                    </div>

                    <label for="divisi" class="col-sm-2 control-label">Divisi <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-building"></i></span>
                        <input type="text" class="form-control" id="divisi" name="divisi" maxlength="45" value="<?php echo set_value('divisi', isset($data->divisi) ? $data->divisi : ''); ?>" placeholder="Divisi" required style="text-transform:uppercase">
                        </div>
                    </div>
                </div>

                <div class="form-group ">
                    <label for="hp" class="col-sm-2 control-label">HP <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-building"></i></span>
                        <input type="text" class="form-control" id="hp" name="hp" maxlength="15" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" placeholder="Nomor hp" value="<?php echo set_value('hp', isset($data->hp) ? $data->hp : ''); ?>" required>
                        </div>
                    </div>

                    <label for="email_pic" class="col-sm-2 control-label">Email</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                        <input type="text" class="form-control" id="email_pic" name="email_pic" maxlength="45" value="<?php echo set_value('email_pic', isset($data->email_pic) ? $data->email_pic : ''); ?>" placeholder="Email" style="text-transform:uppercase">
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

        <div class="tab-pane" id="pic_toko">
        <!-- Data foto -->
        <div class="box box-primary">
            <form role="form" name="frm_foto_toko" id="frm_foto_toko"
                  action="javascript:add_foto_toko();" method="post" enctype="multipart/form-data">

            <div class="box-body">

                <div class="form-group ">
                    <input type="hidden" id="customer" name="customer" value="<?php echo set_value('customer', isset($data->id_customer) ? $data->id_customer : ''); ?>">
                    <!-- file gambar kita buat pada field hidden -->
                    <input type="hidden" name="filelama_toko" id="filelama_toko" class="form-control" value="<?php echo set_value('filelama_toko', isset($data->foto_toko) ? $data->foto_toko : ''); ?>">

                    <label for="id_toko" class="col-sm-2 control-label">Toko <font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <select id="id_toko_foto" name="id_toko_foto" class="form-control pil_toko" style="width: 100%;" tabindex="-1" required>
                            <option value=""></option>
                            <?php foreach ($datprov as $key => $st) : ?>
                            <option value="<?= $st->id_toko; ?>" <?= set_select('id_toko', $st->id_toko, isset($data->id_toko) && $data->id_toko == $st->id_toko) ?>>
                            <?= strtoupper($st->nm_toko); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <label for="foto_toko" class="col-sm-2 control-label">Foto Toko</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <input id="foto_toko" name="foto_toko" type="file">
                        <p class="help-block">Max Image 2 MB</p>
                        </div>
                    </div>

                    <div class="col-sm-offset-2 col-sm-10">

                        <button type="submit" onclick="javascript:add_foto_toko();" class="btn btn-primary" id="btnfoto">
                        <span class="glyphicon glyphicon-plus"></span>&nbsp;Upload
                        </button>

                        <a class="btn btn-danger" href="javascript:void(0)" title="Cancel" onclick="cancel()"><i class="fa fa-minus-circle">&nbsp;</i>Cancel</a>
                    </div>
                    <p>
                    <div class="col-sm-offset-2 col-sm-10">
                         <div id='list_foto_toko'></div>
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
                <input type="text" class="form-control" id="bidus" name="bidus" style="text-transform:uppercase" placeholder="Input Bidang Usaha Baru" required>
                <input type="hidden" class="form-control" id="idbidus" name="idbidus">
            </div>
            <div class="form-group">
                <label for="exampleInputPassword1">Keterangan <font size="4" color="red"><B>*</B></font></label>
                <textarea class="form-control" id="keterangan" name="Input keterangan" maxlength="255" placeholder="Keterangan" required="" autofocus="" style="margin: 0px; height: 49px; width: 216px;"></textarea>
            </div>
            <div class="form-group">
                <iframe onload="ListBD()" hidden="true"></iframe>
            </div>
            </form>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="javascript:save_bidus();">Save</button>
            <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">Close</button>
            </div>
            <div id="list_bd"></div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
<!-- End Modal Bidus-->
<!-- Modal Syarat Penagihan-->
<div class="modal modal-info" id="add_syaratdok" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title" id="myModalLabel"><span class="fa fa-file-pdf-o"></span>&nbsp;Tambah Syarat Penagihan</h4>
            </div>
            <div class="modal-body" id="MyModalBody">
            <form action="#" id="form_syardok">
            <div class="form-group">
                <label for="bidus">Syarat Penagihan <font size="4" color="red"><B>*</B></font></label>
                <input type="text" class="form-control" id="add_nama_syarat" name="add_nama_syarat" placeholder="Input Syarat Penagihan Baru" style="text-transform:uppercase" required>
                <input type="hidden" class="form-control" id="add_id_syarat" name="add_id_syarat">
            </div>
            <div class="form-group">
                <iframe onload="ListSD()" hidden="true"></iframe>
            </div>
            </form>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="javascript:save_syardok();">Save</button>
            <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">Close</button>
            </div>
            <div id="list_sd"></div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
<!-- End Modal Syarat Penagihan-->
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
                <input type="text" class="form-control" id="reff" name="reff" placeholder="Referensi" style="text-transform:uppercase" required>
            </div>
            <div class="form-group">
                <label for="exampleInputPassword1">Keterangan></label>
                <textarea class="form-control" id="keterangan" name="keterangan" maxlength="255" placeholder="Keterangan" style="text-transform:uppercase" style="margin: 0px; height: 49px; width: 216px;"></textarea>
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
<script src="<?= base_url('assets/js/jquery.maskMoney.js')?>"></script>
<script type="text/javascript">

    $(function() {
		$('.harga').maskMoney();
        $('#row_kredit').hide();
        $('#sistem_bayar').change(function(){
        if($('#sistem_bayar').val() == 'Kredit') {
            $('#row_kredit').show();
        } else {
            $('#row_kredit').hide();
        }
        });
    });

    //Timepicker
    $('#jam_tagih').timepicker({
        showInputs: true
    });

    $("#hari_tagih").select2({
        placeholder: "Pilih Hari"//,
        //allowClear: true
    });

    $("#syarat_dokumen").select2({
        placeholder: "Pilih Dokumen"//,
        //allowClear: true
    });

    $("#metode_bayar").select2({
        placeholder: "Pilih"//,
        //allowClear: true
    });

    $(document).ready(function() {
        var type = $('#type').val();
        if(type=='edit'){
            ShowOtherButton();
        }else{
            HideOtherButton();
        }

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

        $(".pil_pic").select2({
            placeholder: "Pilih",
            allowClear: true
        });

        $(".pil_toko").select2({
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
                load_pic_toko(id);
            }
        });

        $('#data_pic').click(function(){
            var id = $('#id_customer').val();
            if(id==''){
                $("#list_pic").hide();
            }else{
                load_pic(id);
                get_idcust_pic(id);
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

        $('#data_foto_toko').click(function(){
            var id = $('#id_customer').val();
            if(id==''){
                $("#list_foto_toko").hide();
            }else{
                load_id_toko(id);
                //load_foto_toko(id);
            }
        });


    });

    //Bidang Usaha
    function save_bidus(){

    var idbidus=$("#idbidus").val();
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
            data:"idbidus="+idbidus+"&bidang_usaha="+bidang_usaha+"&keterangan="+keterangan,
            success:function(html){
                $('#add_bidangusaha').modal('hide');
                $('#form_bidus')[0].reset(); // reset form on modals
                get_bidus();
                ListBD();
            }
        });
    }

    }


    //save_syardok
    function save_syardok(){

    var id_syarat=$("#add_id_syarat").val();
    var nm_syarat=$("#add_nama_syarat").val();

    if(nm_syarat==''){
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
            url:siteurl+"customer/add_syaratdok",
            data:"id_syarat="+id_syarat+"&nm_syarat="+nm_syarat,
            success:function(html){
                $('#add_syaratdok').modal('hide');
                $('#form_syardok')[0].reset(); // reset form on modals
                get_syardok();
                ListSD();
            }
        });
    }

    }


    function ListSD(){
    $.ajax({
        type:"GET",
        url:siteurl+"customer/ListSD",
        success:function(html){
            $("#list_sd").html(html);
        }
    })
    }

    function ListBD(){
    $.ajax({
        type:"GET",
        url:siteurl+"customer/ListBD",
        success:function(html){
            $("#list_bd").html(html);
        }
    })
    }

    function edit_bd(id){
        if(id != ""){
        $.ajax({
            type:"POST",
            url:siteurl+"customer/edit_bd",
            data:{"id":id},
            success:function(result){
                var data = JSON.parse(result);
                $('#bidus').val(data.bidang_usaha);
                $('#keterangan').val(data.keterangan);
                $('#idbidus').val(data.id_bidang_usaha);
            }
        })
    }
    }

    function edit_sd(id){
        if(id != ""){
        $.ajax({
            type:"POST",
            url:siteurl+"customer/edit_sd",
            data:{"id":id},
            success:function(result){
                var data = JSON.parse(result);
                $('#add_nama_syarat').val(data.nm_syarat);
                $('#add_id_syarat').val(data.id_syarat);
            }
        })
    }
    }
/*
    function get_LisBidus() {
        var bidang_usaha = $('#bidang_usaha').val();
        $.ajax({
            type:"GET",
            url:siteurl+"customer/get_LisBidus",
            data:"bidang_usaha="+bidang_usaha,
            dataType : "json",
            success:function(msg){
               $("#nm_cp").val(msg['nm_cp']);
               set_nmcolly();
            }
        });
    }
*/
    function get_bidus(){
        $.ajax({
            type:"GET",
            url:siteurl+"customer/get_bidus",
            success:function(html){
               $("#bidang_usaha").html(html);
            }
        });
    }

    function get_syardok(){
        $.ajax({
            type:"GET",
            url:siteurl+"customer/get_syardok",
            success:function(html){
               $("#syarat_dokumen").html(html);
            }
        });
    }

    function load_id_toko(id){
        $.ajax({
            type:"GET",
            url:siteurl+"customer/load_id_toko",
            data:"id="+id,
            success:function(html){
               $("#id_toko_foto").html(html);
            }
        });
    }

    function load_pic_toko(id){
        $.ajax({
            type:"GET",
            url:siteurl+"customer/load_pic_toko",
            data:"id="+id,
            success:function(html){
               $("#pic").html(html);
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
                        $('[href="#cust_pic"]').tab('show');
                        $('#customerx').val(customer);
                        get_idcust_pic(customer);
                        load_pic(customer);
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

    function get_idcust_pic(customer){
         $.ajax({
             dataType : "json",
             type: 'POST',
             url:siteurl+"customer/get_idcust_pic",
             data:"customer="+customer,
             success:function(msg){
                 var id_pic =msg['id_pic'];
                 $("#id_pic").val(id_pic);
             }
         })
    }

    function get_emailpic(){
    var pic = $('#pic').val();
    if(pic != ""){
        $.ajax({
            type:"POST",
            url:siteurl+"customer/get_emailpic",
            data:{"pic":pic},
            success:function(result){
                var data = JSON.parse(result);
                $("#email").val(data.email_pic);
                $('#hp_pic').val(data.hp);
            }
        })
    }
    }

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
                        $('[href="#pic_toko"]').tab('show');
                        load_id_toko(customer);
                        get_idtoko(customer);
                        ShowOtherButton();
                        $("#frm_toko")[0].reset();
                      } else {
                        load_toko_temp(customer);
                        $('#id_customer').val(customer);
                        get_idtoko(customer);
                        $("#frm_toko")[0].reset();
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
                        $('[href="#toko"]').tab('show');
                        $('#customer').val(customer);
                        load_foto(customer);
                        load_pic_toko(customer);
                        ShowOtherButton();
                        load_toko_temp(customer);
                        $("#frm_pic")[0].reset();
                      } else {
                        load_pic(customer);
                        $('#id_customer').val(customer);
                        get_idcust_pic(customer);
                        $("#frm_pic")[0].reset();
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

    //FOTO CUST
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
                text: "Sukses Upload Foto Customer",
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

    //FOTO TOKO
    function add_foto_toko(){
    var foto_toko       =   $('#foto_toko').val();
    var id_toko_foto    =   $('#id_toko_foto').val();
    var filelama_toko   =   $('#filelama_toko').val();

    $('#frm_foto_toko').ajaxForm({
     url:siteurl+"customer/add_datafoto_toko",
     dataType : "json",
     type: "post",
     data:{"foto_toko":foto_toko,"id_toko_foto":id_toko_foto,"filelama_toko":filelama_toko},
     success:function(msg){
        var pesan = msg['save'];
        var gambar = msg['gambar'];
        var id = $('#id_customer').val();
        if(msg['save']=='1'){
            swal({
                title: "SUKSES!",
                text: "Sukses Upload Foto Toko",
                type: "success",
                timer: 1500,
                showConfirmButton: false
                });

            $('[href="#toko"]').tab('show');
            load_toko_temp(id);
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

    function cancel(){
        $(".box").show();
        $("#form-area").hide();
        //window.location.reload();
        reload_table();
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

    function load_foto_toko(id_toko){
    $.ajax({
        type:"GET",
        url:siteurl+"customer/load_foto_toko",
        data:"id_toko="+id_toko,
        success:function(html){
            $("#list_toko").html(html);
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
</script>
