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
        <li class="active"><a href="#produk" data-toggle="tab" aria-expanded="true" id="data">Master Produk</a></li>
        <li class=""><a href="#koli" data-toggle="tab" aria-expanded="false" id="data_koli">Colly</a></li>
        <li class=""><a href="#komponen" data-toggle="tab" aria-expanded="false" id="data_komponen">Komponen</a></li>
    </ul>
    <!-- /.tab-content -->
    <div class="tab-content">
        <div class="tab-pane active" id="produk">
          <!-- Data Produk -->
            <div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;">
            </div>
            <!-- form start-->
            <div class="box box-primary">
              <?= form_open_multipart($this->uri->uri_string(), array('id' => 'frm_barang', 'name' => 'frm_barang', 'role' => 'form', 'class' => 'form-horizontal')); ?>
                <div class="box-body">

                  <input type="hidden" id="id_barang" name="id_barang" value="<?php echo set_value('id_barang', isset($data->id_barang) ? $data->id_barang : ''); ?>">
                  <?php  if (isset($data->id_barang)) {
                      $type = 'edit';
                  }?>
                  <input type="hidden" id="type" name="type" value="<?= isset($type) ? $type : 'add'; ?>">
                  <input type="hidden" id="nm_group" name="nm_group">

                  <div class="form-group ">
                      <label for="id_jenis" class="col-sm-2 control-label">Jenis Produk <font size="4" color="red"><B>*</B></font></label>
                      <div class="col-sm-3">
                          <div class="input-group">
                              <select id="id_jenis" name="id_jenis" class="form-control pil_jb" style="width: 100%;" tabindex="-1" required>
                                  <option value=""></option>
                                  <?php foreach ($jenis_barang as $key => $st) : ?>
                                  <option value="<?= $st->id_jenis; ?>" <?= set_select('id_jenis', $st->id_jenis, isset($data->jenis) && $data->jenis == $st->id_jenis); ?>>
                                  <?= $st->nm_jenis; ?>
                                  </option>
                                  <?php endforeach; ?>
                              </select>
                              <div class="input-group-btn">
                              <a class="btn btn-info" href="#add_jb" data-toggle="modal" title="Add Jenis Produk">
                              <i class="fa fa-plus">&nbsp;</i>
                              </a>
                              </div>
                          </div>
                      </div>

                      <label for="id_group" class="col-sm-2 control-label">Group Produk <font size="4" color="red"><B>*</B></font></label>
                      <div class="col-sm-3">
                          <div class="input-group">
                              <select id="id_group" name="id_group" class="form-control pil_gb" style="width: 100%;" tabindex="-1" required onchange="get_nmgroup()">
                                  <option value=""></option>
                                  <?php foreach ($group_barang as $key => $st) : ?>
                                  <option value="<?= $st->id_group; ?>" <?= set_select('id_group', $st->id_group, isset($data->id_group) && $data->id_group == $st->id_group); ?>>
                                  <?= $st->nm_group; ?>
                                  </option>
                                  <?php endforeach; ?>
                              </select>
                              <div class="input-group-btn">
                              <a class="btn btn-info" href="#add_gb" data-toggle="modal" title="Add Group Produk">
                              <i class="fa fa-plus">&nbsp;</i>
                              </a>
                              </div>
                          </div>
                      </div>
                  </div>

                  <div class="form-group ">
                      <label for="nm_barang" class="col-sm-2 control-label">Nama Set <font size="4" color="red"><B>*</B></font></label>
                      <div class="col-sm-3">
                          <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-user"></i></span>
                          <input type="text" class="form-control" id="nm_barang" name="nm_barang" maxlength="45" value="<?php echo set_value('nm_barang', isset($data->nm_barang) ? $data->nm_barang : ''); ?>" placeholder="Nama Set" required=""  style="text-transform:uppercase">
                          </div>
                      </div>

                      <label for="brand" class="col-sm-2 control-label">Brand <font size="4" color="red"><B>*</B></font></label>
                      <div class="col-sm-3">
                          <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                          <input type="text" class="form-control" id="brand" name="brand" placeholder="Brand Produk" value="<?php echo set_value('brand', isset($data->brand) ? $data->brand : ''); ?>" style="text-transform:uppercase" required>
                          </div>
                      </div>
                  </div>

                  <div class="form-group ">
                      <label for="series" class="col-sm-2 control-label">Series <font size="4" color="red"><B>*</B></font></label>
                      <div class="col-sm-3">
                          <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                          <input type="text" class="form-control" id="series" name="series" placeholder="Series Produk" value="<?php echo set_value('series', isset($data->series) ? $data->series : ''); ?>" required style="text-transform:uppercase">
                          </div>
                          <!--onkeyup="setNapro()"-->
                      </div>

                      <label for="varian" class="col-sm-2 control-label">Varian / Warna<font size="4" color="red"><B>*</B></font></label>
                      <div class="col-sm-3">
                          <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                          <input type="text" class="form-control" id="varian" name="varian" placeholder="Varian / Warna" value="<?php echo set_value('varian', isset($data->varian) ? $data->varian : ''); ?>" required style="text-transform:uppercase">
                          </div>
                          <!--onkeyup="setNapro()"-->
                      </div>
                  </div>
                  <div class="form-group">
                    <!--
                      <label for="id_supplier" class="col-sm-2 control-label">Supplier <font size="4" color="red"><B>*</B></font></label>
                      <div class="col-sm-3">
                          <div class="input-group">
                              <div class="input-group-btn">
                              <a class="btn btn-info" data-toggle="modal">
                              <i class="fa fa-truck">&nbsp;</i>
                              </a>
                              </div>
                              <select id="id_supplier" name="id_supplier" class="form-control pil_sup" style="width: 100%;" tabindex="-1" required>
                                  <option value=""></option>
                                  <?php foreach ($suppl_barang as $key => $st) : ?>
                                  <option value="<?= $st->id_supplier; ?>" <?= set_select('id_supplier', $st->id_supplier, isset($data->id_supplier) && $data->id_supplier == $st->id_supplier); ?>>
                                  <?= $st->nm_supplier; ?>
                                  </option>
                                  <?php endforeach; ?>
                              </select>
                          </div>
                      </div>
                      -->
                      <label for="qty" class="col-sm-2 control-label">Qty / Satuan <font size="4" color="red"><B>*</B></font></label>
                      <div class="col-sm-2">
                          <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-opencart"></i></span>
                          <input type="text" class="form-control" id="qty" name="qty" maxlength="45" value="1" readonly>
                          </div>
                      </div>
                      <div class="col-sm-2">
                      <select id="satuan" name="satuan" class="form-control">
                              <option value="SET" <?= set_select('satuan', 'SET', isset($data->satuan) && $data->satuan == 'SET'); ?>>SET
                              </option>
                              <option value="PCS" <?= set_select('satuan', 'PCS', isset($data->satuan) && $data->satuan == 'PCS'); ?>>PCS
                              </option>
                          </select>
                      </div>
                  </div>

                  <div class="form-group ">
                      <label for="netto_weight" class="col-sm-2 control-label">Netto Weight<font size="4" color="red"><B>*</B></font></label>
                      <div class="col-sm-3">
                          <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                          <input type="text" class="form-control" onkeyup="this.value=this.value.replace(/[^0-9\.]/g,'');" id="netto_weight" name="netto_weight" placeholder="Input netto_weight" value="<?php echo set_value('netto_weight', isset($data->netto_weight) ? $data->netto_weight : ''); ?>" required>
                          </div>
                      </div>

                      <label for="cbm_each" class="col-sm-2 control-label">CBM Each<font size="4" color="red"><B>*</B></font></label>
                      <div class="col-sm-3">
                          <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                          <input type="text" class="form-control" onkeyup="this.value=this.value.replace(/[^0-9\.]/g,'');" id="cbm_each" name="cbm_each" placeholder="Input CBM Each" value="<?php echo set_value('cbm_each', isset($data->cbm_each) ? $data->cbm_each : ''); ?>" required>
                          </div>
                      </div>
                  </div>

                  <div class="form-group ">
                      <label for="gross_weight" class="col-sm-2 control-label">Gross Weight<font size="4" color="red"><B>*</B></font></label>
                      <div class="col-sm-3">
                          <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                          <input type="text" class="form-control" onkeyup="this.value=this.value.replace(/[^0-9\.]/g,'');" id="gross_weight" name="gross_weight" placeholder="Input gross_weight" value="<?php echo set_value('gross_weight', isset($data->gross_weight) ? $data->gross_weight : ''); ?>" required>
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
                      <label for="foto_barang" class="col-sm-2 control-label">Foto Set</label>
                      <div class="col-sm-1" id="list_foto_barang" class="zoom">
                      </div>
                      <div class="col-sm-2">
                          <div class="input-group">
                          <input id="foto_barang" name="foto_barang" type="file">
                          <input type="hidden" id="foto_barang_lama" name="foto_barang_lama" value="<?php echo set_value('foto_barang_lama', isset($data->foto_barang) ? $data->foto_barang : ''); ?>">
                          <p class="help-block">Max Image 2 MB</p>
                          </div>
                      </div>

                      <label for="spesifikasi" class="col-sm-2 control-label">Spesifikasi Produk</label>
                      <div class="col-sm-3">
                          <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-street-view"></i></span>
                          <textarea class="form-control" id="spesifikasi" name="spesifikasi" style="text-transform:uppercase" placeholder="Spesifikasi Produk" style="margin: 0px; height: 49px; width: 216px;"><?php echo set_value('spesifikasi', isset($data->spesifikasi) ? $data->spesifikasi : ''); ?></textarea>
                          </div>
                      </div>
                  </div>

                  <div class="form-group ">
                      <label for="leadtime_produksi" class="col-sm-2 control-label">Leadtime Produksi<font size="4" color="red"><B>*</B></font></label>
                      <div class="col-sm-3">
                          <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                          <input type="text" class="form-control" onkeyup="this.value=this.value.replace(/[^0-9\.]/g,'');" id="leadtime_produksi" name="leadtime_produksi" placeholder="Input Leadtime Produksi" value="<?php echo set_value('leadtime_produksi', isset($data->leadtime_produksi) ? $data->leadtime_produksi : ''); ?>" required>
                          </div>
                      </div>

                      <label for="leadtime_pengiriman" class="col-sm-2 control-label">Leadtime Pengiriman<font size="4" color="red"><B>*</B></font></label>
                      <div class="col-sm-3">
                          <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                          <input type="text" class="form-control" onkeyup="this.value=this.value.replace(/[^0-9\.]/g,'');" id="leadtime_pengiriman" name="leadtime_pengiriman" placeholder="Input Leadtime Pengiriman" value="<?php echo set_value('leadtime_pengiriman', isset($data->leadtime_pengiriman) ? $data->leadtime_pengiriman : ''); ?>" required>
                          </div>
                      </div>
                  </div>

                  <div class="form-group">
                  	<label for="qty" class="col-sm-2 control-label">Pricelist <font size="4" color="red"><B>*</B></font></label>
                      <div class="col-sm-3">
                          <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-money"></i></span>
                          <input type="text" class="form-control" id="harga" name="harga" onkeyup="filterAngka1(this.id);document.getElementById(this.id).value = formatCurrency(this.value);" value="<?php echo set_value('harga', isset($data->harga) ? $data->harga : ''); ?>" required>
                          </div>
                      </div>

                      <label for="qty" class="col-sm-2 control-label">Diskon Standart (%) <font size="4" color="red"><B>*</B></font></label>
                      <div class="col-sm-3">
                          <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                          <input type="text" class="form-control" id="diskon_standart" name="diskon_standart" onkeyup="this.value=this.value.replace(/[^0-9\.]/g,'');" value="<?php echo set_value('diskon_standar_persen', isset($data->diskon_standar_persen) ? $data->diskon_standar_persen : ''); ?>">
                          </div>
                      </div>
                  </div>

                  <div class="form-group">
                  	<label for="qty" class="col-sm-2 control-label">Diskon Promo (Rp) <font size="4" color="red"><B>*</B></font></label>
                      <div class="col-sm-3">
                          <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-money"></i></span>
                          <input type="text" class="form-control" id="diskon_promo_rp" name="diskon_promo_rp" onkeyup="this.value=this.value.replace(/[^0-9\.]/g,'');" value="<?php echo set_value('diskon_promo_rp', isset($data->diskon_promo_rp) ? $data->diskon_promo_rp : ''); ?>">
                          </div>
                      </div>

                      <label for="qty" class="col-sm-2 control-label">Diskon Promo (%) <font size="4" color="red"><B>*</B></font></label>
                      <div class="col-sm-3">
                          <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                          <input type="text" class="form-control" id="diskon_promo_persen" name="diskon_promo_persen" onkeyup="this.value=this.value.replace(/[^0-9\.]/g,'');" value="<?php echo set_value('diskon_promo_persen', isset($data->diskon_promo_persen) ? $data->diskon_promo_persen : ''); ?>">
                          </div>
                      </div>
                  </div>

                  <div class="form-group">
                  	<label for="qty" class="col-sm-2 control-label">Diskon Jika Qty <font size="4" color="red"><B>*</B></font></label>
                      <div class="col-sm-3">
                          <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                          <input type="text" class="form-control" id="diskon_jika_qty" name="diskon_jika_qty" onkeyup="this.value=this.value.replace(/[^0-9\.]/g,'');" value="<?php echo set_value('diskon_jika_qty', isset($data->diskon_jika_qty) ? $data->diskon_jika_qty : ''); ?>">
                          </div>
                      </div>

                      <label for="qty" class="col-sm-2 control-label">Diskon Qty Gratis <font size="4" color="red"><B>*</B></font></label>
                      <div class="col-sm-3">
                          <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                          <input type="text" class="form-control" id="diskon_qty_gratis" name="diskon_qty_gratis" onkeyup="this.value=this.value.replace(/[^0-9\.]/g,'');" value="<?php echo set_value('diskon_qty_gratis', isset($data->diskon_qty_gratis) ? $data->diskon_qty_gratis : ''); ?>">
                          </div>
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
              <?= form_close(); ?>
            </div>
          <!-- Data Produk -->
        </div>

        <div class="tab-pane" id="koli">
          <!-- Data Koli -->
            <div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;">
            </div>
            <!-- form start-->
            <div class="box box-primary">
              <?= form_open($this->uri->uri_string(), array('id' => 'frm_koli', 'name' => 'frm_koli', 'role' => 'form', 'class' => 'form-horizontal')); ?>
                <div class="box-body">

                  <input type="hidden" id="barang" name="barang" value="<?php echo set_value('barang', isset($data->id_barang) ? $data->id_barang : ''); ?>">
                  <input type="hidden" id="series_nm" name="series_nm" value="<?php echo set_value('series_nm', isset($data->series) ? $data->series : ''); ?>">
                  <?php  if (isset($data->nm_koli)) {
                      $type1 = 'edit';
                  }?>
                  <input type="hidden" id="type1" name="type1" value="<?= isset($type1) ? $type1 : 'add'; ?>">
                  <input type="hidden" id="id_koli" name="id_koli" value="">
                  <input type="hidden" id="nm_cp" name="nm_cp" value="">
                  <input type="hidden" id="barang_nm" name="barang_nm" value="<?php echo set_value('barang_nm', isset($data->nm_barang) ? $data->nm_barang : ''); ?>">

                  <div class="form-group ">
                      <label for="nm_koli" class="col-sm-2 control-label">Nama Colly Produk <font size="4" color="red"><B>*</B></font></label>
                      <div class="col-sm-3">
                          <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-user"></i></span>
                          <input type="text" class="form-control" id="nm_koli" name="nm_koli" maxlength="45" value="<?php echo set_value('nm_koli', isset($data->nm_koli) ? $data->nm_koli : ''); ?>" placeholder="Nama Colly" required="" readonly style="text-transform:uppercase">
                          </div>
                      </div>

                      <label for="nm_koli" class="col-sm-2 control-label">Nama Model <font size="4" color="red"><B>*</B></font></label>
                      <div class="col-sm-3">
                          <div class="input-group">
                            <select id="id_koli_model" name="id_koli_model" class="form-control pil_barang" style="width: 100%;" tabindex="-1" required>
                                <option value=""></option>
                              <?php foreach ($model as $key => $st) : ?>
                                <option value="<?= $st->id_koli_model; ?>" <?= set_select('id_koli_model', $st->id_koli_model, isset($data->id_koli_model) && $data->id_koli_model == $st->id_koli_model) ?>>
                                  <?= $st->koli_model ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                              <div class="input-group-btn">
                              <a class="btn btn-info" href="#add_cp" data-toggle="modal" title="Add Colly Produk">
                              <i class="fa fa-plus">&nbsp;</i>
                              </a>
                              </div>
                          </div>
                      </div>
                  </div>

                  <div class="form-group ">
                      <label for="id_koli_warna" class="col-sm-2 control-label">Nama Warna <font size="4" color="red"><B>*</B></font></label>
                      <div class="col-sm-3">
                          <div class="input-group">
                            <div class="input-group-btn">
                              <a class="btn btn-info" data-toggle="modal" title="Tambah Warna">
                                <i class="fa fa-plus">&nbsp;W</i>
                              </a>
                            </div>

                            <select id="id_koli_warna" name="id_koli_warna" class="form-control pil_barang" style="width: 100%;" tabindex="-1">
                                <option value=""></option>
                              <?php foreach ($warna as $key => $st) : ?>
                                <option value="<?= $st->id_koli_warna; ?>" <?= set_select('id_koli_warna', $st->id_koli_warna, isset($data->id_koli_warna) && $data->id_koli_warna == $st->id_koli_warna) ?>>
                                  <?= $st->koli_warna ?>
                                </option>
                              <?php endforeach; ?>
                            </select>

                            <input type="hidden" class="form-control" id="koli_warna" name="koli_warna" maxlength="45" value="<?php echo set_value('koli_warna', isset($data->koli_warna) ? $data->koli_warna : ''); ?>">
                          </div>
                      </div>

                      <label for="id_koli_varian" class="col-sm-2 control-label">Nama Varian <font size="4" color="red"><B>*</B></font></label>
                      <div class="col-sm-3">
                          <div class="input-group">
                            <div class="input-group-btn">
                              <a class="btn btn-info" data-toggle="modal" title="Tambah Varian">
                                <i class="fa fa-plus">&nbsp;V</i>
                              </a>
                            </div>

                            <select id="id_koli_varian" name="id_koli_varian" class="form-control pil_barang" style="width: 100%;" tabindex="-1">
                                <option value=""></option>
                              <?php foreach ($varian as $key => $st) : ?>
                                <option value="<?= $st->id_koli_varian; ?>" <?= set_select('id_koli_varian', $st->id_koli_varian, isset($data->id_koli_varian) && $data->id_koli_varian == $st->id_koli_varian) ?>>
                                  <?= $st->koli_varian ?>
                                </option>
                              <?php endforeach; ?>
                            </select>

                            <input type="hidden" class="form-control" id="koli_varian" name="koli_varian" maxlength="45" value="<?php echo set_value('koli_varian', isset($data->koli_varian) ? $data->koli_varian : ''); ?>">
                              <div class="input-group-btn">

                              </div>
                          </div>
                      </div>
                  </div>

                  <div class="form-group ">
                      <label for="qty_koli" class="col-sm-2 control-label">Qty <font size="4" color="red"><B>*</B></font></label>
                      <div class="col-sm-3">
                          <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-user"></i></span>
                          <input type="text" class="form-control" id="qty_koli" name="qty_koli" onkeyup="this.value=this.value.replace(/[^0-9.]/g,'');" maxlength="5" placeholder="Qty Colly" required>
                          </div>
                      </div>

                      <label for="c_cbm_each" class="col-sm-2 control-label">CBM Each</label>
                      <div class="col-sm-3">
                          <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                          <input type="text" onkeyup="this.value=this.value.replace(/[^0-9\.]/g,'');" class="form-control" id="c_cbm_each" name="c_cbm_each"  value="" placeholder="cbm_each">
                          </div>
                      </div>
                  </div>

                  <div class="form-group ">
                      <label for="c_netto_weight" class="col-sm-2 control-label">Netto Weight</label>
                      <div class="col-sm-3">
                          <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                          <input type="text" onkeyup="this.value=this.value.replace(/[^0-9\.]/g,'');" class="form-control" id="c_netto_weight" name="c_netto_weight" value="" placeholder="netto_weight">
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
                      <label for="c_gross_weight" class="col-sm-2 control-label">Gross Weight</label>
                      <div class="col-sm-3">
                          <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                          <input type="text" onkeyup="this.value=this.value.replace(/[^0-9\.]/g,'');" class="form-control" id="c_gross_weight" name="c_gross_weight" value="" placeholder="gross_weight">
                          </div>
                      </div>

                      <label for="keterangan_kol" class="col-sm-2 control-label">Keterangan</label>
                      <div class="col-sm-3">
                          <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-street-view"></i></span>
                          <textarea class="form-control" id="keterangan_kol" name="keterangan_kol" placeholder="keterangan Colly" style="margin: 0px; height: 49px; width: 216px;"><?php echo set_value('keterangan_kol', isset($data->keterangan) ? $data->keterangan : ''); ?></textarea>
                          </div>
                      </div>
                  </div>



                  <div class="box-footer">
                  <div class="form-group">
                      <div class="col-sm-offset-2 col-sm-10">

                          <button type="submit" name="btnkoli" class="btn btn-primary" id="btnkoli">Save</button>
                          <input type="button" name="btn_cancel" class="btn btn-danger" onclick="cancel()" value="Cancel">

                      </div>
                  </div>
                  </div>

                  </div>
              <?= form_close(); ?>
            </div>
            <div id="list_koli"></div>
            <!-- Data Koli -->
        </div>

        <div class="tab-pane" id="komponen">
          <!-- Data komponen -->
              <div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;">
              </div>
              <!-- form start-->
              <div class="box box-primary">
              <?= form_open_multipart($this->uri->uri_string(), array('id' => 'frm_komponen', 'name' => 'frm_komponen', 'role' => 'form', 'class' => 'form-horizontal')); ?>
                  <div class="box-body">

                  <?php  if (isset($data->id_komponen)) {
                      $type2 = 'edit';
                  }?>
                  <input type="hidden" id="type2" name="type2" value="<?= isset($type2) ? $type2 : 'add'; ?>">
                  <input type="hidden" id="barangc" name="barangc" value="<?php echo set_value('barangc', isset($data->id_barang) ? $data->id_barang : ''); ?>">
                  <input type="hidden" id="id_komponen" name="id_komponen" value="<?php echo set_value('id_komponen', isset($data->id_komponen) ? $data->id_komponen : ''); ?>">

                  <div class="form-group ">
                      <label for="id_koli_c" class="col-sm-2 control-label">Nama Colly <font size="4" color="red"><B>*</B></font></label>
                      <div class="col-sm-3">
                          <div class="input-group">
                              <div class="input-group-btn">
                              <a class="btn btn-info" data-toggle="modal">
                              <i class="fa fa-truck">&nbsp;</i>
                              </a>
                              </div>
                              <select id="id_koli_c" name="id_koli_c" class="form-control pil_koli" style="width: 100%;" tabindex="-1" required>
                                  <option value=""></option>
                                  <?php foreach ($koli as $key => $st) : ?>
                                  <option value="<?= $st->id_koli; ?>" <?= set_select('id_koli_c', $st->id_koli, isset($data->id_koli) && $data->id_koli == $st->id_koli); ?>>
                                  <?= $st->nm_koli; ?>
                                  </option>
                                  <?php endforeach; ?>
                              </select>
                          </div>
                      </div>

                      <label for="nm_komponen" class="col-sm-2 control-label">Nama Komponen <font size="4" color="red"><B>*</B></font></label>
                      <div class="col-sm-3">
                          <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-user"></i></span>
                          <input type="text" class="form-control" id="nm_komponen" name="nm_komponen" maxlength="45" value="<?php echo set_value('nm_komponen', isset($data->nm_komponen) ? $data->nm_komponen : ''); ?>" placeholder="Nama Komponen" required="" style="text-transform:uppercase">
                          </div>
                      </div>
                  </div>

                  <div class="form-group ">
                      <label for="qty_komponen" class="col-sm-2 control-label">Qty <font size="4" color="red"><B>*</B></font></label>
                      <div class="col-sm-3">
                          <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-user"></i></span>
                          <input type="text" class="form-control" id="qty_komponen" name="qty_komponen" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" maxlength="5" value="<?php echo set_value('qty_komponen', isset($data->qty) ? $data->qty : ''); ?>" placeholder="Qty Komponen" required="">
                          </div>
                      </div>

                      <label for="keterangan_kom" class="col-sm-2 control-label">Keterangan</label>
                      <div class="col-sm-3">
                          <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-street-view"></i></span>
                          <textarea class="form-control" id="keterangan_kom" name="keterangan_kom" placeholder="Keterangan" style="margin: 0px; height: 49px; width: 216px;"><?php echo set_value('keterangan_kom', isset($data->keterangan) ? $data->keterangan : ''); ?></textarea>
                          </div>
                      </div>
                  </div>

                  <div class="form-group">
                      <label for="foto_komponen" class="col-sm-2 control-label">Foto Komponen</label>
                      <div class="col-sm-1" id="list_foto_komponen" class="zoom">
                      </div>
                      <div class="col-sm-2">
                          <div class="input-group">
                          <input id="foto_komponen" name="foto_komponen" type="file">
                          <input type="hidden" id="foto_komponen_lama" name="foto_komponen_lama" value="<?php echo set_value('foto_komponen_lama', isset($data->foto_komponen) ? $data->foto_komponen : ''); ?>">
                          <p class="help-block">Max Image 2 MB</p>
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

                          <button type="submit" name="btnkomponen" class="btn btn-primary" id="btnkomponen">Save</button>
                          <input type="button" name="btn_cancel" class="btn btn-danger" onclick="cancel()" value="Cancel">

                      </div>
                  </div>
                  </div>

                  </div>
              <?= form_close(); ?>
              </div>
              <div id="list_komponen"></div>
              </div>
          <!-- Data Komponen -->
        </div>

    </div>
    <!-- /.tab-content -->
</div>
<!-- Modal Bidus-->
<div class="modal modal-info" id="add_gb" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title" id="myModalLabel"><span class="fa fa-file-pdf-o"></span>&nbsp;Tambah Data Group Barang</h4>
            </div>
            <div class="modal-body" id="MyModalBody">
            <form action="#" id="form_gb">
            <div class="form-group">
                <label for="gb">ID Group Produk <font size="4" color="red"><B>*</B></font></label>
                <input type="text" class="form-control" id="add_id_group" name="add_id_group" placeholder="ID Produk" maxlength="2" style="text-transform:uppercase">

                <label for="gb">Nama Group Barang <font size="4" color="red"><B>*</B></font></label>
                <input type="text" style="text-transform:uppercase" class="form-control" id="add_nm_group" name="add_nm_group" placeholder="Nama Group BArang" required>
            </div>
            </form>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="javascript:save_gb();">Save</button>
            <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal modal-info" id="add_jb" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title" id="myModalLabel"><span class="fa fa-file-pdf-o"></span>&nbsp;Tambah Data Jenis Produk</h4>
            </div>
            <div class="modal-body" id="MyModalBody">
            <form action="#" id="form_jb">
            <div class="form-group">
                <label for="id_jenis">Kode <font size="4" color="red"><B>*</B></font></label>
                <input type="text" class="form-control" id="add_id_jenis" name="add_id_jenis" placeholder="ID" required maxlength="2" style="text-transform:uppercase">

                <label for="nm_jenis">Jenis Produk <font size="4" color="red"><B>*</B></font></label>
                <input type="text" style="text-transform:uppercase" class="form-control" id="nm_jenis" name="nm_jenis" placeholder="Jenis Produk" required>
            </div>
            </form>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="javascript:save_jb();">Save</button>
            <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal modal-info" id="add_cp" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title" id="myModalLabel"><span class="fa fa-file-pdf-o"></span>&nbsp;Tambah Data Colly Produk</h4>
            </div>
            <div class="modal-body" id="MyModalBody">
            <form action="#" id="form_cp">
            <div class="form-group">
                <label for="colly_produk">Colly Produk <font size="4" color="red"><B>*</B></font></label>
                <input type="text" class="form-control" id="colly_produk" name="colly_produk" placeholder="Input Name Colly Produk" required   style="text-transform:uppercase">
                <input type="hidden" id="id_colly" name="id_colly" class="form-control">
            </div>
            <div class="form-group">
                <iframe onload="ListCP()" hidden="true"></iframe>
                <button type="button" class="btn btn-outline" onclick="javascript:save_cp();">Save</button>

                <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">Close</button>
            </div>
            </form>
            </div>
            <div id="list_cp">
            </div>
            <div class="modal-footer">

            </div>
        </div>
    </div>
</div>

<!-- End Modal Bidus-->
<script type="text/javascript">
    $(document).ready(function() {
    //  $("#harga").val(formatCurrency($("#harga").val()));
    });

    function filterAngka1(a){
        document.getElementById(a).value = document.getElementById(a).value.replace(/[^\d]/g,"");
    }

    function formatCurrency(c){
        n = c.replace(/,/g, "");
      var s=n.split('.')[1];
      (s) ? s="."+s : s="";
      n=n.split('.')[0]
      while(n.length>3){
          s="."+n.substr(n.length-3,3)+s;
          n=n.substr(0,n.length-3)
      }
      return n+s

      }
      function formatnomor($angka)
      {
       if($angka){
           $jadi = number_format($angka,0,',','.');
           return $jadi;
          }
          else {
            return 0;
          }
      }
      function load_foto_barang(id){
      $.ajax({
          type:"GET",
          url:siteurl+"barang/load_foto_barang",
          data:"id="+id,
          success:function(html){

              $("#list_foto_barang").html(html);
          }
      })
    }

    function get_nmgroup() {
        var id_group = $('#id_group').val();
        $.ajax({
            type:"GET",
            url:siteurl+"barang/get_nmgroup",
            data:"id_group="+id_group,
            dataType : "json",
            success:function(msg){
               $("#nm_group").val(msg['nm_group']);
               //setNapro();
            }
        });
    }

    function get_nmcp() {
        var id_colly_produk = $('#id_colly_produk').val();
        $.ajax({
            type:"GET",
            url:siteurl+"barang/get_nmcp",
            data:"id_colly_produk="+id_colly_produk,
            dataType : "json",
            success:function(msg){
               $("#nm_cp").val(msg['nm_cp']);
               set_nmcolly();
            }
        });
    }

    function set_nmcolly(){
        var nm_cp = $('#nm_cp').val();
        var varian = $('#variank').val();

        var nakol = nm_cp+' '+varian;
        $('#nm_koli').val(nakol);
    }

    function setNapro(){
        var series = $('#series').val();
        var nm_group = $('#nm_group').val();
        var varian = $('#varian').val();
        var nmbar = series+' '+varian+' '+nm_group;
        //alert(series);
        $('#nm_barang').val(nmbar);
    }

    function get_cp(){
        $.ajax({
            type:"GET",
            url:siteurl+"barang/get_cp",
            success:function(html){
               $("#id_colly_produk").html(html);
            }
        });
    }

    function get_gb(){
        $.ajax({
            type:"GET",
            url:siteurl+"barang/get_gb",
            success:function(html){
               $("#id_group").html(html);
            }
        });
    }

    function get_jb(){
        $.ajax({
            type:"GET",
            url:siteurl+"barang/get_jb",
            success:function(html){
               $("#id_jenis").html(html);
            }
        });
    }

    function get_koli(id_barang){
        $.ajax({
            type:"GET",
            url:siteurl+"barang/get_koli",
            data:"id_barang="+id_barang,
            success:function(html){
               $("#id_koli_c").html(html);
            }
        });
    }

    //save_cp
    function save_cp(){

      var colly_produk=$("#colly_produk").val();
      var id_colly=$("#id_colly").val();
      if(colly_produk==''){
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
              url:siteurl+"barang/add_cp",
              data :{"id_colly":id_colly,"colly_produk":colly_produk},
              dataType : "json",
              success:function(msg){
                  $('#add_cp').modal('hide');
                  $('#form_cp')[0].reset(); // reset form on modals
                  get_cp();
                  ListCP();
              }
          });
      }

    }
    //save_gb
    function save_gb(){

      var id_group=$("#add_id_group").val();
      var nm_group=$("#add_nm_group").val();

      if(id_group=='' || nm_group==''){
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
              url:siteurl+"barang/add_gb",
              data :{"id_group":id_group,"nm_group":nm_group},
              dataType : "json",
              success:function(msg){
                  $('#add_gb').modal('hide');
                  $('#form_gb')[0].reset(); // reset form on modals
                  get_gb();
              }
          });
      }

    }

    //save_jb
    function save_jb(){

      var id_jenis=$("#add_id_jenis").val();
      var nm_jenis=$("#nm_jenis").val();

      if(id_jenis=='' || nm_jenis==''){
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
              url:siteurl+"barang/add_jb",
              data :{"id_jenis":id_jenis,"nm_jenis":nm_jenis},
              dataType : "json",
              success:function(msg){
                  $('#add_jb').modal('hide');
                  $('#form_jb')[0].reset(); // reset form on modals
                  get_jb();
              }
          });
      }

    }

    $(document).ready(function() {

        var type = $('#type').val();
        var foto = $('#id_barang').val();
            if(type=='edit'){
                ShowOtherButton();
                load_foto_barang(foto);
                //$("#id_jenis").prop("disabled", true);
                //$("#id_group").prop("disabled", true);
                //$("#series").prop("disabled", true);
                //$("#varian").prop("disabled", true);
            }else{
                HideOtherButton();
            }

        /*$("input#series").on({
          keydown: function(e) {
            if (e.which === 32)
              return false;
          },
          change: function() {
            this.value = this.value.replace(/\s/g, "");
          }
        });*/

        $(".pil_sup").select2({
            placeholder: "Pilih Supplier Produk",
            allowClear: true
        });

        $(".pil_jb").select2({
            placeholder: "Pilih Jenis Produk",
            allowClear: true
        });

        $(".pil_gb").select2({
            placeholder: "Pilih Group Produk",
            allowClear: true
        });

        $(".pil_cp").select2({
            placeholder: "Pilih Colly Produk",
            allowClear: true
        });

        $(".pil_koli").select2({
            placeholder: "Pilih Data Colly",
            allowClear: true
        });

        $("#id_koli_model,#id_koli_warna,#id_koli_varian").select2({
            placeholder: "Pilih Data Colly",
            allowClear: true
        });

        //Date picker
        $('#tanggallahir').datepicker({
          format: 'dd-mm-yyyy',
          todayHighlight: true,
          //startDate: new Date(),
          autoclose: true
        });

        $('#data').click(function(){
            var id = $('#id_barang').val();
            if(id==''){
                $("#list_foto_barang").hide();
            }else{
                load_foto_barang(id);
            }
        });

        $('#data_koli').click(function(){
            var id = $('#id_barang').val();
            if(id==''){
                $("#list_koli").hide();
            }else{
                load_koli(id);
            }
        });

        $('#data_komponen').click(function(){
            var id = $('#id_barang').val();
            if(id==''){
                $("#list_komponen").hide();
            }else{
                load_komponen(id);
                get_koli(id);
            }
        });

    });

    $('#id_koli_model,#id_koli_warna,#id_koli_varian').on('change', function(){

      var model = $("#id_koli_model option:selected").text().split(' ').join('');
      var warna = $("#id_koli_warna option:selected").text().split(' ').join('');
      var varian = $("#id_koli_varian option:selected").text().split(' ').join('');
      console.log(model);
      $("#nm_koli").val(model+' '+warna+' '+varian);
    });

    //Data Koli
    function load_koli(id_barang){
      $.ajax({
          type:"GET",
          url:siteurl+"barang/load_koli",
          data:"id_barang="+id_barang,
          success:function(html){
              $("#list_koli").html(html);
          }
      })
    }

    //Data komponen
    function load_komponen(id_barang){
      $.ajax({
          type:"GET",
          url:siteurl+"barang/load_komponen",
          data:"id_barang="+id_barang,
          success:function(html){
              $("#list_komponen").html(html);
          }
      })
    }

    function ListCP(){
      $.ajax({
          type:"GET",
          url:siteurl+"barang/ListCP",
          success:function(html){
              $("#list_cp").html(html);
          }
      })
    }

    function cancel(){
        $(".box").show();
        $("#form_barang").hide();
        window.location.reload();
        //reload_table();
    }

    //Barang
    $('#frm_barang').on('submit', function(e){
        e.preventDefault();
        $('#harga').val($('#harga').val().replace(/[^\d]/g,""));
        var formdata = $("#frm_barang").serialize();
        $.ajax({
          /*
          url: siteurl+"barang/save_data_ajax",
          dataType : "json",
          type: 'POST',
          processData:false,
          contentType:false,
          cache:false,
          async:false,
          data: new FormData(this),
          */
          url: siteurl+"barang/save_data_ajax",
          dataType : "json",
          type: 'POST',
          data: formdata,
            //alert(msg);
            success: function(msg){
                if(msg['save']=='1'){
                    var barang =msg['barang'];
                    swal({
                      title: "Sukses!",
                      text: "Data Berhasil Disimpan, Lanjutkan pengisian data Koli",
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
                        $('[href="#koli"]').tab('show');
                        $('#barang').val(barang);
                        load_koli(barang);
                        ShowOtherButton();
                      } else {
                        load_foto_barang(barang);
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

    //Koli
    $('#frm_koli').on('submit', function(e){
        e.preventDefault();
        var formdata = $("#frm_koli").serialize();
        $.ajax({
            url: siteurl+"barang/save_data_koli",
            dataType : "json",
            type: 'POST',
            data: formdata,
            //alert(msg);
            success: function(msg){
                if(msg['save']=='1'){
                    var barang =msg['barang'];
                    swal({
                      title: "Sukses!",
                      text: "Data Berhasil Disimpan, Lanjutkan pengisian data Komponen",
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
                        $('[href="#komponen"]').tab('show');
                        $('#barangc').val(barang);
                        get_koli(barang);
                        load_komponen(barang);
                        ShowOtherButton();
                      } else {
                        load_koli(barang);
                        //window.location.reload();
                        //cancel();
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

    //Komponen
    $('#frm_komponen').on('submit', function(e){
        e.preventDefault();
        var formdata = $("#frm_komponen").serialize();
        $.ajax({
            url: siteurl+"barang/save_data_komponen",
            dataType : "json",
            type: 'POST',
            processData:false,
            contentType:false,
            cache:false,
            async:false,
            data: new FormData(this),
            //alert(msg);
            success: function(msg){
                var barang = msg['barang'];
                if(msg['save']=='1'){
                    swal({
                        title: "Sukses!",
                        text: "Data Berhasil Di Simpan",
                        type: "success",
                        timer: 1500,
                        showConfirmButton: false
                    });
                    load_komponen(barang);
                    //cancel();
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

    function ShowOtherButton()
    {
        //after success saving then activate sumbit button on each tab
        $("#btnkoli").show();
        $("#btnkomponen").show();
    }

    function HideOtherButton()
    {
        //after success saving then activate sumbit button on each tab
        $("#btnkoli").hide();
        $("#btnkomponen").hide();
    }


    //Edit Koli
    function edit_koli(id_koli){
      if(id_koli != ""){
          $.ajax({
              type:"POST",
              url:siteurl+"barang/edit_koli",
              data:{"id_koli":id_koli},
              success:function(result){
                  var data = JSON.parse(result);
                  $('#type1').val('edit');
                  $('#id_koli').val(data.id_koli);
                  $('#nm_koli').val(data.nm_koli);
                  $('#qty_koli').val(data.qty);
                  $('#id_koli_model').val(data.id_koli_model).change();
                  $('#id_koli_warna').val(data.id_koli_warna).change();
                  $('#id_koli_varian').val(data.id_koli_varian).change();
                  $('#sts_aktif').val(data.sts_aktif);
                  $('#keterangan_kol').val(data.keterangan);
                  $('#c_netto_weight').val(data.netto_weight);
                  $('#c_cbm_each').val(data.cbm_each);
                  $('#c_gross_weight').val(data.gross_weight);
              }
          })
      }
    }

    function edit_komponen(id_komponen){
      if(id_komponen != ""){
          $.ajax({
              type:"POST",
              url:siteurl+"barang/edit_komponen",
              data:{"id_komponen":id_komponen},
              success:function(result){
                  var data = JSON.parse(result);
                  $('#type2').val('edit');
                  $('#id_koli_c').val(data.id_koli).change();
                  $('#id_komponen').val(data.id_komponen);
                  $('#nm_komponen').val(data.nm_komponen);
                  $('#qty_komponen').val(data.qty);
                  $('#sts_aktif').val(data.sts_aktif);
                  $('#keterangan_kom').val(data.keterangan);
              }
          })
      }
    }


    function edit_cp(id){
        if(id != ""){
        $.ajax({
            type:"POST",
            url:siteurl+"barang/edit_cp",
            data:{"id":id},
            success:function(result){
                var data = JSON.parse(result);
                $('#id_colly').val(data.id_colly_produk);
                $('#colly_produk').val(data.colly_produk);
            }
        })
    }
    }

    //Delete Koli
    function hapus_koli(id){
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
                    url: siteurl+'barang/hapus_koli/'+id,
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

    //Delete Komponen
    function hapus_komponen(id){
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
                    url: siteurl+'barang/hapus_komponen/'+id,
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
