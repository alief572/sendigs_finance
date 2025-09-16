<?php

if (!empty($this->uri->segment(3))) {
  $getC             = $this->db->get_where('master_supplier', array('id_supplier' => $id))->row();
  $getCur           = $this->db->get('mata_uang')->result();
  $PIC_Office       = $this->db->get_where('child_supplier_pic_office', array('id_supplier' => $id))->result();
  $PIC_Factory      = $this->db->get_where('child_supplier_pic_factory', array('id_supplier' => $id))->result();
  $PIC_Excompany    = $this->db->get_where('child_supplier_pic_excompany', array('id_supplier' => $id))->result();
  $name_type        = $this->db->get_where('child_supplier_type', array('id_type' => $getC->id_type))->row();
  $name_brand       = $this->db->where_in('id_brand', explode(";", $getC->id_brand))->get('master_product_brand')->result();
  $name_procat      = $this->db->get_where('master_product_category', array('id_category' => $getC->id_category))->row();
  $name_buscat      = $this->db->get_where('child_supplier_business_category', array('id_business' => $getC->id_business))->row();
  $name_supcap      = $this->db->get_where('child_supplier_capacity', array('id_capacity' => $getC->id_capacity))->row();
  $name_toq         = $this->db->get_where('child_supplier_toq', array('id_toq' => $getC->id_toq))->row();
  $getSP            = $this->db->get_where('child_supplier_pic', array('id_supplier' => $id))->result();
  $getSB            = $this->db->get_where('child_supplier_bank', array('id_supplier' => $id))->result();
  //$getB     = $this->db->get_where('master_product_brand',array('id_supplier'=>$id))->result();

  // echo "<pre>";
  // print_r($getC);
  // echo "</pre>";
}
if ($this->uri->segment(4) == 'view') {
  $view = 'style="display:block"';
} else {
  $mode = 'input';
}

?>
<form class="form-active" id="form-supplier" action="" method="post" enctype="multipart/form-data">
  <div class="box box-success">
    <div class="box-body">
      <div class="row">
        <div class="col-md-12">
          <table class="" width="100%">
            <tr style='background-color: #175477 !important; color: white; font-size: 15px !important;'>
              <th class="text-center" colspan='3'>DETAIL SUPPLIER OFFICE</th>
            </tr>
          </table>
          <table id="my-grid1" class="table table-striped table-bordered table-hover table-condensed" width="100%">
            <tbody>
              <tr id="my-grid-tr-input_date">
                <td class="text-left vMid" width="25%">Input Date <span class='text-red'>*</span></td>
                <td class="text-left">
                  <input type="date" name="input_date" id="input_date" class="form-control input-sm required w20" readonly value="<?= empty($getC->input_date) ? date("Y-m-d") : $getC->input_date ?>" title="Input Date" placeholder="Input Data" autocomplete="off">
                  <small class="text-red input_date hideIt">Input Date Can't be empty!</small>
                  <label class="label_input">
                  </label>
                </td>
              </tr>
              <tr id="my-grid-tr-supplier_shipping">
                <td class="text-left vMid">Supplier Shipping <span class='text-red'>*</span></td>
                <td class="text-left">
                  <?php
                  if ($getC) :
                    $Import = '';
                    $Local = '';
                    $getC->supplier_shipping == "Import" ? $Import = 'checked' : $Local = 'checked';
                  endif; ?>
                  <label class="checkbox-inline"><input type="radio" name="supplier_shipping" value="Import" class="radioShipping required" <?= $Import ?>> Import</label>
                  <label class="checkbox-inline"><input type="radio" name="supplier_shipping" value="Local" class="radioShipping required" <?= $Local ?>> Local</label>
                  <small class="text-red supplier_shipping hideIt">Supplier Shipping Can't be empty!</small>
                  <small class="label_input">
                    </label>
                </td>
              </tr>

              <tr id="my-grid-tr-id_supplier">
                <td class="text-left vMid">Supplier ID <span class='text-red'>*</span></td>
                <td class="text-left">
                  <input type="hidden" name="type" value="<?= empty($getC->id_supplier) ? 'add' : 'edit' ?>">
                  <input type="text" class="form-control input input-sm required w20" name="id_supplier" id="id_supplier" value="<?= empty($getC->id_supplier) ? '' : $getC->id_supplier ?>" readonly>
                  <small class="text-red id_supplier hideIt">Supplier ID Can't be empty!</small>
                  <label class="label_input">

                  </label>
                </td>
              </tr>

              <tr id="my-grid-tr-nm_supplier_office">
                <td class="text-left vMid">Supplier Office Name <span class='text-red'>*</span></td>
                <td class="text-left">
                  <input type="text" name="nm_supplier_office" id="nm_supplier_office" class="form-control w60 required ucfirst" title="Name Supplier Office" placeholder="Name Supplier Office" value="<?= empty($getC->nm_supplier_office) ? '' : $getC->nm_supplier_office ?>">
                  <small class="text-red nm_supplier_office hideIt">Supplier Office Name Can't be empty!</small>
                  <label class="label_input">
                  </label>
                </td>
              </tr>

              <tr id="my-grid-tr-id_country">
                <td class="text-left vMid">Country <span class='text-red'>*</span></td>
                <td class="text-left">
                  <select class="form-control required select2 " name="id_country" id="id_country"></select>
                  <small class="text-red id_country hideIt">Country Can't be empty!</small>
                  <label class="label_input">
                  </label>
                </td>
              </tr>
              <tr id="my-grid-tr-id_prov">
                <td class="text-left vMid">Province <span class='text-red'>*</span></td>
                <td class="text-left">
                  <select class="form-control required select2 w50" name="id_prov" id="id_prov">
                  </select>
                  <small class="text-red id_prov hideIt">Country Can't be empty!</small>
                  <label class="label_input">
                  </label>
                </td>
              </tr>
              <tr id="my-grid-tr-city_office">
                <td class="text-left vMid">City <span class='text-red'>*</span></td>
                <td class="text-left">
                  <select class="form-control required select2 w50" name="city_office" id="city_office"></select>
                  <small class="text-red city_office hideIt">City Can't be empty!</small>
                  <label class="label_input">

                  </label>
                </td>
              </tr>

              <tr id="my-grid-tr-address_office">
                <td class="text-left vMid">Address <span class='text-red'>*</span></td>
                <td class="text-left">
                  <textarea type="text" id="address_office" name="address_office" class="form-control required w60 ucfirst" placeholder="Address" autocomplete="off"><?= empty($getC->address_office) ? '' : $getC->address_office ?></textarea>
                  <small class="text-red address_office hideIt">Address Can't be empty!</small>
                  <label class="label_input">

                  </label>
                </td>
              </tr>

              <tr id="my-grid-tr-zip_code_office">
                <td class="text-left vMid">ZIP Code <span class='text-red'>*</span></td>
                <td class="text-left">
                  <input type="text" name="zip_code_office" id="zip_code_office" class="form-control required w30" placeholder="ZIP Code" autocomplete="off" value="<?= empty($getC->zip_code_office) ? '' : $getC->zip_code_office ?>">
                  <small class="text-red zip_code_office hideIt">ZIP Code Can't be empty!</label>
                    <label class="label_input">
                    </label>
                </td>
              </tr>

              <tr id="my-grid-tr-telephone_office_1">
                <td class="text-left vMid">Telephone <span class='text-red'>*</span></td>
                <td class="text-left">
                  <div>
                    <?php
                    $tlp = '';
                    if ($getC->telephone_office_1) {
                      $tlp = explode("-", $getC->telephone_office_1);
                    }
                    ?>
                    <input type="text" id="telephone_office_1_1" name="telephone_office_1[]" class="form-control required w15 numberOnly" placeholder="Code" autocomplete="off" maxlength="4" value="<?= empty($tlp[0]) ? '' : $tlp[0] ?>">
                    -
                    <input type="text" id="telephone_office_1_2" name="telephone_office_1[]" class="form-control required w30 numberOnly" placeholder="Number" autocomplete="off" maxlength="9" value="<?= empty($tlp[1]) ? '' : $tlp[1] ?>">
                  </div>
                  <small class="text-red telephone_office_1 telephone_office_1_2 hideIt">Telephone (Office) Can't be empty!</small>
                </td>
              </tr>

              <tr id="my-grid-tr-telephone_office_2">
                <td class="text-left vMid"></td>
                <td class="text-left">
                  <?php
                  $tlp = '';
                  if ($getC->telephone_office_2) {
                    $tlp = explode("-", $getC->telephone_office_2);
                  }
                  ?>
                  <input type="text" id="telephone_office_2_1" name="telephone_office_2[]" class="form-control w15 numberOnly" placeholder="Code" autocomplete="off" maxlength="4" value="<?= empty($tlp[0]) ? '' : $tlp[0] ?>">
                  -
                  <input type="text" id="telephone_office_2_2" name="telephone_office_2[]" class="form-control w30 numberOnly" placeholder="Number" autocomplete="off" maxlength="9" value="<?= empty($tlp[1]) ? '' : $tlp[1] ?>">
                  <small class="text-red telephone_office_2X hideIt">Telephone (Office) Can't be empty!</small>
                  <label class="label_input">
                  </label>
                </td>
              </tr>

              <tr id="my-grid-tr-fax_office">
                <td class="text-left vMid">Fax</td>
                <td class="text-left">
                  <input type="text" name="fax_office" id="fax_office" class="form-control w30 numberOnly" value="<?= empty($getC->fax_office) ? '' : $getC->fax_office ?>" placeholder="Fax" autocomplete="off">
                </td>
              </tr>

              <tr id="my-grid-tr-owner">
                <td class="text-left vMid">Owner</td>
                <td class="text-left">
                  <input type="text" name="owner" id="owner" class="form-control w50 ucfirst" value="<?= empty($getC->owner) ? '' : $getC->owner ?>" placeholder="Owner" autocomplete="off">
                </td>
              </tr>

            </tbody>
          </table>
          <table class="table table-striped table-bordered table-hover table-condensed" width="100%">
            <tr style='background-color: #175477; color: white; font-size: 15px;'>
              <th class="text-center" colspan='4'>PIC Office <span class='text-red'>*</span></th>
            </tr>
          </table>
          <div class="picoffice_class">
            <?php if (!empty($PIC_Office)) : ?>
              <?php $no = 0;
              foreach ($PIC_Office as $key => $vb) : $no++ ?>
                <div class="picoffice_list" style="padding-bottom:1.5em">
                  <div class="pull-right">
                    <a type="button" class="text-lg text-red deletePicOffice" href="javascript:void(0)" title="Delete PIC Office">
                      <i class="fa fa-trash-o"></i> Delete PIC Office
                    </a>
                  </div>
                  <legend class="legend">PIC Office <span class="numbering_picoffice"><?= $no ?></span></legend>
                  <div class="row">
                    <div class="col-md-6">
                      <table class="table-condensed" width="100%">
                        <tr>
                          <td width="30%">Name PIC <span class='text-red'>*</span></td>
                          <td>
                            <input type="text" name="pic_name[]" id="pic_office_name<?= $no ?>" class="form-control required" value="<?= $vb->pic_name ?>" placeholder="PIC Name">
                            <small class="text-red pic_office_name<?= $no ?> hideIt">PIC Name Can't be empty!</small>
                          </td>
                        </tr>
                        <tr>
                          <td>Position <span class='text-red'>*</span></td>
                          <td>
                            <input type="text" name="pic_position[]" id="pic_position<?= $no ?>" class="form-control required" value="<?= $vb->pic_position ?>" placeholder="PIC Position">
                            <small class="text-red pic_position<?= $no ?> hideIt">PIC Position Can't be empty!</small>
                          </td>
                        </tr>
                        <tr>
                          <td>Mobile Phone <span class='text-red'>*</span></td>
                          <td>
                            <input type="text" name="pic_phone[]" id="pic_phone<?= $no ?>" class="form-control required" value="<?= $vb->pic_phone ?>" placeholder="PIC Mobile Phone">
                            <small class="text-red pic_phone<?= $no ?> hideIt">PIC Mobile Phone Can't be empty!</small>
                          </td>
                        </tr>
                        <tr>
                          <td>WhatsApp Number <span class='text-red'>*</span></td>
                          <td>
                            <input type="text" name="pic_wa[]" id="pic_wa<?= $no ?>" class="form-control required" value="<?= $vb->pic_wa ?>" placeholder="PIC WhatsApp Number">
                            <small class="text-red pic_wa<?= $no ?> hideIt">PIC WhatsApp Number Can't be empty!</small>
                          </td>
                        </tr>
                        <tr>
                          <td>E-mail <span class='text-red'>*</span></td>
                          <td>
                            <input type="text" name="pic_email[]" id="pic_email<?= $no ?>" class="form-control required" value="<?= $vb->pic_email ?>" placeholder="PIC E-mail">
                            <small class="text-red pic_email<?= $no ?> hideIt">PIC E-mail Can't be empty!</small>
                          </td>
                        </tr>
                        <tr>
                          <td>WeChat Id <span class='text-red'>*</span></td>
                          <td>
                            <input type="text" name="pic_wechat[]" id="pic_wechat<?= $no ?>" class="form-control" value="<?= $vb->pic_wechat ?>" placeholder="PIC WeChat Id">
                            <small class="text-red pic_wechat<?= $no ?> hideIt">PIC WeChat Id Can't be empty!</small>
                          </td>
                        </tr>
                        <tr>
                          <td>Website <span class='text-red'>*</span></td>
                          <td>
                            <input type="text" name="pic_web[]" id="pic_web<?= $no ?>" class="form-control" value="<?= $vb->pic_web ?>" placeholder="PIC Website">
                            <small class="text-red pic_web<?= $no ?> hideIt">PIC Website Can't be empty!</small>
                          </td>
                        </tr>
                      </table>
                    </div>
                    <div class="col-md-6">
                      <button type="button" onclick="$('#image_<?= $no ?>').click()" class="btn btn-md btn-primary"><i class="fa fa-upload"></i> Upload PIC Card</button>
                      <input type="file" name="pic_card[]" id="image_<?= $no ?>" class="hidden pic_card_office" onchange="tampilkanPreview(this,'preview_<?= $no ?>')">
                      <img id="preview_<?= $no ?>" class="img-responsive" style="height: 250px;margin: 26px auto;width: auto;" src="<?= empty($PIC_Office) ? '' : base_url('assets/img/master_supplier/PIC_Office/' . $vb->pic_card) ?>" alt="<?= $vb->pic_card ?>">
                      <input type="hidden" name="filelama[]" id="filelama_<?= $no ?>" class="form-control" value="<?= ($PIC_Office) ?  $vb->pic_card : '' ?>">
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          <div>
            <a class="btn btn-sm btn-success" id="addPICOffice"><i class="fa fa-plus"></i> Add PIC Office</a>
          </div>
          <br>

          <table id="my-grid2" class="table table-striped table-bordered table-hover table-condensed" width="100%">
            <tbody>
              <tr style='background-color: #175477 !important; color: white; font-size: 15px !important;'>
                <th class="text-center" colspan='3'>DETAIL SUPPLIER FACTORY</th>
              </tr>
              <tr id="my-grid-tr-nm_supplier_factory">
                <td class="text-left vMid" width="25%">Supplier Factory Name <span class='text-red'>*</span></td>
                <td class="text-left">
                  <input type="text" name="nm_supplier_factory" id="nm_supplier_factory" placeholder="Factory Name" autocomplete="off" class="form-control required w60 ucfirst" value="<?= empty($getC->nm_supplier_factory) ? '' : $getC->nm_supplier_factory ?>">
                  <small class="text-red nm_supplier_factory hideIt">Factory Name Can't be empty!</small>
                </td>
              </tr>

              <tr id="my-grid-tr-id_country_factory">
                <td class="text-left vMid">Country <span class='text-red'>*</span></td>
                <td class="text-left">
                  <select class="form-control required select2 " name="id_country_factory" id="id_country_factory"></select>
                  <small class="text-red id_country_factory hideIt">Country Can't be empty!</small>
                </td>
              </tr>
              <tr id="my-grid-tr-id_prov_factory">
                <td class="text-left vMid">Province <span class='text-red'>*</span></td>
                <td class="text-left">
                  <select class="form-control required select2 w50" name="id_prov_factory" id="id_prov_factory">
                  </select>
                  <small class="text-red id_prov_factory hideIt">Country Can't be empty!</small>
                </td>
              </tr>

              <tr id="my-grid-tr-city_factory">
                <td class="text-left vMid">City <span class='text-red'>*</span></td>
                <td class="text-left">
                  <select class="form-control required select2 w50" name="city_factory" id="city_factory"></select>
                  <small class="text-red city_factory hideIt">City Can't be empty!</small>
                </td>
              </tr>

              <tr id="my-grid-tr-address_factory">
                <td class="text-left vMid">Address <span class='text-red'>*</span></td>
                <td class="text-left">
                  <label class="label_view">
                    <?= ($getC) ? $getC->address_factory : '-' ?>
                  </label>
                  <textarea name="address_factory" id="address_factory" class="form-control  required w60" placeholder="Address"><?= empty($getC->address_factory) ? '' : $getC->address_factory ?></textarea>
                  <small class="text-red address_factory hideIt">Address Can't be empty!</small>
                </td>
              </tr>

              <tr id="my-grid-tr-zip_code_factory">
                <td class="text-left vMid">ZIP Code <span class='text-red'>*</span></td>
                <td class="text-left">
                  <?php
                  echo form_input(array('type' => 'text', 'id' => 'zip_code_factory', 'name' => 'zip_code_factory', 'class' => 'form-control required w30', 'placeholder' => 'ZIP Code', 'autocomplete' => 'off', 'value' => empty($getC->zip_code_factory) ? '' : $getC->zip_code_factory))
                  ?>
                  <small class="text-red zip_code_factory hideIt">ZIP Code Can't be empty!</small>
                </td>
              </tr>

              <tr id="my-grid-tr-telephone_factory_1">
                <td class="text-left vMid">Telephone (Factory) <span class='text-red'>*</span></td>
                <td class="text-left">
                  <?php
                  $tlp = '';
                  if ($getC->telephone_factory_1) {
                    $tlp = explode("-", $getC->telephone_factory_1);
                  }
                  echo form_input(array('type' => 'text', 'id' => 'telephone_factory_1_1', 'name' => 'telephone_factory_1[]', 'class' => 'form-control required w15 numberOnly', 'placeholder' => 'Code', 'autocomplete' => 'off', 'maxlength' => '4', 'value' => empty($tlp[0]) ? '' : $tlp[0]));
                  echo " - ";
                  echo form_input(array('type' => 'text', 'id' => 'telephone_factory_1_2', 'name' => 'telephone_factory_1[]', 'class' => 'form-control required w30 numberOnly', 'placeholder' => 'Number', 'autocomplete' => 'off', 'maxlength' => '9', 'value' => empty($tlp[1]) ? '' : $tlp[1]));
                  ?>
                  <small class="text-red telephone_factory_1_1 telephone_factory_1_2 hideIt">Telephone (Office) Can't be empty!</small>
                </td>
              </tr>

              <tr id="my-grid-tr-telephone_factory_2">
                <td class="text-left vMid"></td>
                <td class="text-left">
                  <?php
                  $tlp = '';
                  if ($getC->telephone_factory_2) {
                    $tlp = explode("-", $getC->telephone_factory_2);
                  }
                  echo form_input(array('type' => 'text', 'id' => 'telephone_factory_2_1', 'name' => 'telephone_factory_2[]', 'class' => 'form-control w15 numberOnly', 'placeholder' => 'Code', 'autocomplete' => 'off', 'maxlength' => '4', 'value' => empty($tlp[0]) ? '' : $tlp[0]));
                  echo " - ";
                  echo form_input(array('type' => 'text', 'id' => 'telephone_factory_2_2', 'name' => 'telephone_factory_2[]', 'class' => 'form-control w30 numberOnly', 'placeholder' => 'Number', 'autocomplete' => 'off', 'maxlength' => '9', 'value' => empty($tlp[1]) ? '' : $tlp[1]));
                  ?>
                  <small class="text-red telephone_factory_2 hideIt">Telephone (Office) Can't be empty!</small>
                </td>
              </tr>

              <tr id="my-grid-tr-fax_factory">
                <td class="text-left vMid">Fax (Office)</td>
                <td class="text-left">
                  <?php
                  echo form_input(array('type' => 'text', 'id' => 'fax_factory', 'name' => 'fax_factory', 'class' => 'form-control w30 numberOnly', 'placeholder' => 'Fax', 'autocomplete' => 'off', 'value' => empty($getC->fax_factory) ? '' : $getC->fax_factory))
                  ?>
                </td>
              </tr>

              <tr id="my-grid-tr-owner_factory">
                <td class="text-left vMid">Owner</td>
                <td class="text-left">
                  <?php
                  echo form_input(array('type' => 'text', 'id' => 'owner_factory', 'name' => 'owner_factory', 'class' => 'form-control w50 ucfirst', 'placeholder' => 'Owner', 'autocomplete' => 'off', 'value' => empty($getC->owner_factory) ? '' : $getC->owner_factory))
                  ?>
                </td>
              </tr>

            </tbody>
          </table>
          <table class="table table-striped table-bordered table-hover table-condensed" width="100%">
            <tr style='background-color: #175477; color: white; font-size: 15px;'>
              <th class="text-center" colspan='4'>PIC Factory<span class='text-red'>*</span></th>
            </tr>
          </table>
          <div class="picfactory_class">
            <?php if (!empty($PIC_Factory)) : ?>
              <?php $no = 0;
              foreach ($PIC_Factory as $key => $vb) : $no++ ?>
                <div class="picfactory_list" style="padding-bottom:1.5em">
                  <div class="pull-right">
                    <a type="button" class="text-lg text-red deletePicFactory" href="javascript:void(0)" title="Delete PIC Factory">
                      <i class="fa fa-trash-o"></i> Delete PIC Factory
                    </a>
                  </div>
                  <legend class="legend">PIC Factory <span class="numbering_picfactory"><?= $no ?></span></legend>
                  <div class="row">
                    <div class="col-md-6">
                      <table class="table-condensed" width="100%">
                        <tr>
                          <td width="30%">Name PIC <span class="text-red">*</span></td>
                          <td>
                            <input type="text" name="pic_name_factory[]" id="pic_name_factory<?= $no ?>" class="form-control required" placeholder="PIC Name" value="<?= $vb->pic_name ?>">
                            <small class="text-red pic_name_factory<?= $no ?> hideIt">PIC Name Can't be empty!</small>
                          </td>
                        </tr>
                        <tr>
                          <td>Position <span class="text-red">*</span></td>
                          <td>
                            <input type="text" name="pic_position_factory[]" id="pic_position_factory<?= $no ?>" class="form-control required" value="<?= $vb->pic_position ?>" placeholder="PIC Position">
                            <small class="text-red pic_position_factory<?= $no ?> hideIt">PIC Position Can't be empty!</small>
                          </td>
                        </tr>
                        <tr>
                          <td>Mobile Phone <span class="text-red">*</span></td>
                          <td>
                            <input type="text" name="pic_phone_factory[]" id="pic_phone_factory<?= $no ?>" class="form-control required" value="<?= $vb->pic_phone ?>" placeholder="PIC Mobile Phone">
                            <small class="text-red pic_phone_factory<?= $no ?> hideIt">PIC Mobile Phone Can't be empty!</small>
                          </td>
                        </tr>
                        <tr>
                          <td>WhatsApp Number <span class="text-red">*</span></td>
                          <td>
                            <input type="text" name="pic_wa_factory[]" id="pic_wa_factory<?= $no ?>" class="form-control required" value="<?= $vb->pic_wa ?>" placeholder="PIC WhatsApp Number">
                            <small class="text-red pic_wa_factory<?= $no ?> hideIt">PIC WhatsApp Number Can't be empty!</small>
                          </td>
                        </tr>
                        <tr>
                          <td>E-mail <span class="text-red">*</span></td>
                          <td>
                            <input type="text" name="pic_email_factory[]" id="pic_email_factory<?= $no ?>" class="form-control required" value="<?= $vb->pic_email ?>" placeholder="PIC E-mail">
                            <small class="text-red pic_email_factory<?= $no ?> hideIt">PIC E-mail Can't be empty!</small>
                          </td>
                        </tr>
                        <tr>
                          <td>WeChat Id <span class="text-red">*</span></td>
                          <td>
                            <input type="text" name="pic_wechat_factory[]" id="pic_wechat_factory<?= $no ?>" class="form-control" value="<?= $vb->pic_wechat ?>" placeholder="PIC WeChat Id">
                            <small class="text-red pic_wechat_factory<?= $no ?> hideIt">PIC WeChat Id Can't be empty!</small>
                          </td>
                        </tr>
                        <tr>
                          <td>Website <span class="text-red">*</span></td>
                          <td>
                            <input type="text" name="pic_web_factory[]" id="pic_web_factory<?= $no ?>" class="form-control" value="<?= $vb->pic_web ?>" placeholder=" PIC Website">
                            <small class="text-red pic_web_factory<?= $no ?> hideIt">PIC Website Can't be empty!</small>
                          </td>
                        </tr>
                      </table>
                    </div>
                    <div class="col-md-6">
                      <button type="button" onclick="$('#image_factory<?= $no ?>').click()" class="btn btn-md btn-primary"><i class="fa fa-upload"></i> Upload PIC Card</button>
                      <input type="file" name="pic_card_factory[]" id="image_factory<?= $no ?>" class="hidden pic_card_office" onchange="tampilkanPreview(this,'preview_factory<?= $no ?>')">
                      <img id="preview_factory<?= $no ?>" class="img-responsive" style="height: 250px;margin: 26px auto;width: auto;" src="<?= empty($PIC_Factory) ? '' : base_url('assets/img/master_supplier/PIC_Factory/' . $vb->pic_card) ?>" alt="<?= $vb->pic_card ?>">
                      <input type="hidden" name="filelama_factory[]" id="filelama_factory<?= $no ?>" class="form-control" value="<?= ($PIC_Factory) ?  $vb->pic_card : '' ?>">
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          <label class=" label_input">
            <a class="btn btn-sm btn-success" id="addPICFactory">Add PIC Factory</a>
          </label>
          <br>

          <table id="my-grid3" class="table table-striped table-bordered table-hover table-condensed" width="100%">
            <tbody>
              <tr style='background-color: #175477 !important; color: white; font-size: 15px !important;'>
                <th class="text-center" colspan='3'>DETAIL SUPPLIER EXPORT COMPANY</th>
              </tr>
              <tr id="my-grid-tr-nm_supplier_excompany">
                <td class="text-left vMid" width="25%">Export Company Name <span class='text-red'>*</span></td>
                <td class="text-left">
                  <input type="text" name="nm_supplier_excompany" id="nm_supplier_excompany" placeholder="Export Company Name" autocomplete="off" class="form-control required w60 ucfirst" value="<?= empty($getC->nm_supplier_excompany) ? '' : $getC->nm_supplier_excompany ?>">
                  <small class="text-red nm_supplier_excompany hideIt">Export Company Can't be empty!</small>
                </td>
              </tr>

              <tr id="my-grid-tr-id_country_excompany">
                <td class="text-left vMid">Country <span class='text-red'>*</span></td>
                <td class="text-left">
                  <select class="form-control required select2 " name="id_country_excompany" id="id_country_excompany"></select>
                  <small class="text-red id_country_excompany hideIt">Country Can't be empty!</small>
                </td>
              </tr>

              <tr id="my-grid-tr-id_prov_excompany">
                <td class="text-left vMid">Province <span class='text-red'>*</span></td>
                <td class="text-left">
                  <select class="form-control required select2 w50" name="id_prov_excompany" id="id_prov_excompany">
                  </select>
                  <small class="text-red id_prov_excompany hideIt">Country Can't be empty!</small>
                </td>
              </tr>

              <tr id="my-grid-tr-id_city_excompany">
                <td class="text-left vMid">City <span class='text-red'>*</span></td>
                <td class="text-left">
                  <select class="form-control required select2 w50" name="id_city_excompany" id="id_city_excompany"></select>
                  <small class="text-red id_city_excompany hideIt">City Can't be empty!</small>
                </td>
              </tr>

              <tr id="my-grid-tr-address_excompany">
                <td class="text-left vMid">Address <span class='text-red'>*</span></td>
                <td class="text-left">
                  <textarea name="address_excompany" id="address_excompany" class="form-control  required w60" placeholder="Address"><?= empty($getC->address_excompany) ? '' : $getC->address_excompany ?></textarea>
                  <small class="text-red address_excompany hideIt">Address Can't be empty!</small>
                </td>
              </tr>

              <tr id="my-grid-tr-zip_code_excompany">
                <td class="text-left vMid">ZIP Code <span class='text-red'>*</span></td>
                <td class="text-left">
                  <?php
                  echo form_input(array('type' => 'text', 'id' => 'zip_code_excompany', 'name' => 'zip_code_excompany', 'class' => 'form-control required w30', 'placeholder' => 'ZIP Code', 'autocomplete' => 'off', 'value' => empty($getC->zip_code_excompany) ? '' : $getC->zip_code_excompany))
                  ?>
                  <small class="text-red zip_code_excompany hideIt">ZIP Code Can't be empty!</small>
                </td>
              </tr>

              <tr id="my-grid-tr-telephone_excompany_1">
                <td class="text-left vMid">Telephone (Factory) <span class='text-red'>*</span></td>
                <td class="text-left">
                  <?php
                  $tlp = '';
                  if ($getC->telephone_excompany_1) {
                    $tlp = explode("-", $getC->telephone_excompany_1);
                  }
                  echo form_input(array('type' => 'text', 'id' => 'telephone_excompany_1_1', 'name' => 'telephone_excompany_1[]', 'class' => 'form-control required w15 numberOnly', 'placeholder' => 'Code', 'autocomplete' => 'off', 'maxlength' => '4', 'value' => empty($tlp[0]) ? '' : $tlp[0]));
                  echo " - ";
                  echo form_input(array('type' => 'text', 'id' => 'telephone_excompany_1_2', 'name' => 'telephone_excompany_1[]', 'class' => 'form-control required w30 numberOnly', 'placeholder' => 'Number', 'autocomplete' => 'off', 'maxlength' => '9', 'value' => empty($tlp[1]) ? '' : $tlp[1]));
                  ?>
                  <small class="text-red telephone_excompany_1_1 hideIt">Telephone (Office) Can't be empty!</small>
                </td>
              </tr>

              <tr id="my-grid-tr-telephone_excompany_2">
                <td class="text-left vMid"></td>
                <td class="text-left">
                  <?php
                  $tlp = '';
                  if ($getC->telephone_excompany_2) {
                    $tlp = explode("-", $getC->telephone_excompany_2);
                  }
                  echo form_input(array('type' => 'text', 'id' => 'telephone_excompany_2_1', 'name' => 'telephone_excompany_2[]', 'class' => 'form-control w15 numberOnly', 'placeholder' => 'Code', 'autocomplete' => 'off', 'maxlength' => '4', 'value' => empty($tlp[0]) ? '' : $tlp[0]));
                  echo " - ";
                  echo form_input(array('type' => 'text', 'id' => 'telephone_excompany_2_2', 'name' => 'telephone_excompany_2[]', 'class' => 'form-control w30 numberOnly', 'placeholder' => 'Number', 'autocomplete' => 'off', 'maxlength' => '9', 'value' => empty($tlp[1]) ? '' : $tlp[1]));
                  ?>
                  <small class="text-red telephone_excompany_2_1 telephone_excompany_2_2 hideIt">Telephone Can't be empty!</small>
                </td>
              </tr>

              <tr id="my-grid-tr-fax_excompany">
                <td class="text-left vMid">Fax (Office)</td>
                <td class="text-left">
                  <?php
                  echo form_input(array('type' => 'text', 'id' => 'fax_excompany', 'name' => 'fax_excompany', 'class' => 'form-control w30 numberOnly', 'placeholder' => 'Fax', 'autocomplete' => 'off', 'value' => empty($getC->fax_excompany) ? '' : $getC->fax_excompany))
                  ?>
                </td>
              </tr>

              <tr id="my-grid-tr-owner_excompany">
                <td class="text-left vMid">Owner</td>
                <td class="text-left">
                  <?php
                  echo form_input(array('type' => 'text', 'id' => 'owner_excompany', 'name' => 'owner_excompany', 'class' => 'form-control w50 ucfirst', 'placeholder' => 'Owner', 'autocomplete' => 'off', 'value' => empty($getC->owner_factory) ? '' : $getC->owner_factory))
                  ?>
                </td>
              </tr>

            </tbody>
          </table>

          <table class="table table-striped table-bordered table-hover table-condensed" width="100%">
            <tr style='background-color: #175477; color: white; font-size: 15px;'>
              <th class="text-center" colspan='4'>PIC Export Company<span class='text-red'>*</span></th>
            </tr>
            <tbody id="tfoot-pic-excompany">
            </tbody>
          </table>
          <div class="picexcompany_class">
            <?php if (!empty($PIC_Excompany)) : ?>
              <?php $no = 0;
              foreach ($PIC_Excompany as $key => $vb) : $no++ ?>
                <div class="picexcompany_list" style="padding-bottom:1.5em">
                  <div class="pull-right">
                    <a type="button" class="text-lg text-red deletePicExcompany" href="javascript:void(0)" title="Delete PIC Excompany">
                      <i class="fa fa-trash-o"></i> Delete PIC Excompany
                    </a>
                  </div>
                  <legend class="legend">PIC Excompany <span class="numbering_picexcompany"><?= $no ?></span></legend>
                  <div class="row">
                    <div class="col-md-6">
                      <table class="table-condensed" width="100%">
                        <tr>
                          <td width="30%">Name PIC <span class="text-red">*</span></td>
                          <td>
                            <input type="text" name="pic_name_excompany[]" id="pic_name_excompany<?= $no ?>" class="form-control required" placeholder="PIC Name" value="<?= $vb->pic_name ?>">
                            <small class="text-red pic_name_excompany<?= $no ?> hideIt">PIC Name Can't be empty!</small>
                          </td>
                        </tr>
                        <tr>
                          <td>Position <span class="text-red">*</span></td>
                          <td>
                            <input type="text" name="pic_position_excompany[]" id="pic_position_excompany<?= $no ?>" class="form-control required" value="<?= $vb->pic_position ?>" placeholder="PIC Position">
                            <small class="text-red pic_position_excompany<?= $no ?> hideIt">PIC Position Can't be empty!</small>
                          </td>
                        </tr>
                        <tr>
                          <td>Mobile Phone <span class="text-red">*</span></td>
                          <td>
                            <input type="text" name="pic_phone_excompany[]" id="pic_phone_excompany<?= $no ?>" class="form-control required" value="<?= $vb->pic_phone ?>" placeholder="PIC Mobile Phone">
                            <small class="text-red pic_phone_excompany<?= $no ?> hideIt">PIC Mobile Phone Can't be empty!</small>
                          </td>
                        </tr>
                        <tr>
                          <td>WhatsApp Number <span class="text-red">*</span></td>
                          <td>
                            <input type="text" name="pic_wa_excompany[]" id="pic_wa_excompany<?= $no ?>" class="form-control required" value="<?= $vb->pic_wa ?>" placeholder="PIC WhatsApp Number">
                            <small class="text-red pic_wa_excompany<?= $no ?> hideIt">PIC WhatsApp Number Can't be empty!</small>
                          </td>
                        </tr>
                        <tr>
                          <td>E-mail <span class="text-red">*</span></td>
                          <td>
                            <input type="text" name="pic_email_excompany[]" id="pic_email_excompany<?= $no ?>" class="form-control required" value="<?= $vb->pic_email ?>" placeholder="PIC E-mail">
                            <small class="text-red pic_email_excompany<?= $no ?> hideIt">PIC E-mail Can't be empty!</small>
                          </td>
                        </tr>
                        <tr>
                          <td>WeChat Id <span class="text-red">*</span></td>
                          <td>
                            <input type="text" name="pic_wechat_excompany[]" id="pic_wechat_excompany<?= $no ?>" class="form-control" value="<?= $vb->pic_wechat ?>" placeholder="PIC WeChat Id">
                            <small class="text-red pic_wechat_excompany<?= $no ?> hideIt">PIC WeChat Id Can't be empty!</small>
                          </td>
                        </tr>
                        <tr>
                          <td>Website <span class="text-red">*</span></td>
                          <td>
                            <input type="text" name="pic_web_excompany[]" id="pic_web_excompany<?= $no ?>" class="form-control" value="<?= $vb->pic_web ?>" placeholder=" PIC Website">
                            <small class="text-red pic_web_excompany<?= $no ?> hideIt">PIC Website Can't be empty!</small>
                          </td>
                        </tr>
                      </table>
                    </div>
                    <div class="col-md-6">
                      <button type="button" onclick="$('#image_excompany<?= $no ?>').click()" class="btn btn-md btn-primary"><i class="fa fa-upload"></i> Upload PIC Card</button>
                      <input type="file" name="pic_card_excompany[]" id="image_excompany<?= $no ?>" class="hidden pic_card_office" onchange="tampilkanPreview(this,'preview_excompany<?= $no ?>')">
                      <img id="preview_excompany<?= $no ?>" class="img-responsive" style="height: 250px;margin: 26px auto;width: auto;" src="<?= empty($PIC_Excompany) ? '' : base_url('assets/img/master_supplier/PIC_Excompany/' . $vb->pic_card) ?>" alt="<?= $vb->pic_card ?>">
                      <input type="hidden" name="filelama_excompany[]" id="filelama_excompany<?= $no ?>" class="form-control" value="<?= ($PIC_Excompany) ?  $vb->pic_card : '' ?>">
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          <label class="label_input">
            <a class="btn btn-sm btn-success" id="addPICExcompany">Add PIC Export Company</a>
          </label>
          <br>

        </div>
        <div class="col-md-12">
          <table id="my-grid2" class="table table-striped table-bordered table-hover table-condensed" width="100%">
            <thead>
              <tr style='background-color: #175477; color: white; font-size: 15px;'>
                <th class="text-center" colspan='3'>DETAIL PRODUCTIVITY</th>
              </tr>
            </thead>

            <tbody>
              <tr id="my-grid-tr-website">
                <td class="text-left vMid" width="25%">Website <span class='text-red'>*</span></td>
                <td class="text-left">
                  <input type="text" class="form-control required w50" name="website" id="website" placeholder="Web Address" autocomplete="off" value="<?= empty($getC->website) ? '' : $getC->website ?>">
                  <small class="text-red website hideIt">Website Can't be empty!</small>
                </td>
              </tr>

              <tr id="my-grid-tr-npwp">
                <td class="text-left vMid">NPWP/PKP Number <span class='text-red'>*</span></td>
                <td class="text-left">
                  <input type="text" class="form-control required numberOnly w50" name="npwp" id="npwp" placeholder="NPWP/PKP Number" autocomplete="off" value="<?= empty($getC->npwp) ? '' : $getC->npwp ?>">
                  <small class="text-red npwp hideIt">Website Can't be empty!</small>
                </td>
              </tr>

              <tr id="my-grid-tr-npwp_name">
                <td class="text-left vMid">NPWP/PKP Name <span class='text-red'>*</span></td>
                <td class="text-left">
                  <input type="text" class="form-control required w50 ucfirst" name="npwp_name" id="npwp_name" placeholder="NPWP Name" autocomplete="off" value="<?= empty($getC->npwp_name) ? '' : $getC->npwp_name ?>">
                  <small class="text-red npwp_name hideIt">NPWP/PKP Name Can't be empty!</small>
                </td>
              </tr>

              <tr id="my-grid-tr-npwp_address">
                <td class="text-left vMid">NPWP/PKP Address <span class='text-red'>*</span></td>
                <td class="text-left">
                  <textarea name="npwp_address" id="npwp_address" class="form-control required w60" placeholder="NPWP/PKP Address"><?= empty($getC->npwp_address) ? '' : $getC->npwp_address ?></textarea>
                  <small class="text-red npwp_address hideIt">NPWP/PKP Address Can't be empty!</small>
                </td>
              </tr>

              <tr id="my-grid-tr-id_type">
                <td class="text-left vMid">Supplier Type <span class='text-red'>*</span></td>
                <td class="text-left">
                  <div class="input-group w90">
                    <select class="form-control select2 required" name="id_type" id="id_type"></select>
                    <button type="button" id="add_SupType" class="btn btn-primary "><i class="fa fa-plus"></i></button>
                    <small class="text-red id_type hideIt">NPWP/PKP Address Can't be empty!</small>
                  </div>

                </td>
              </tr>

              <tr id="my-grid-tr-id_business">
                <td class="text-left vMid">Business Category</td>
                <td class="text-left">
                  <select class="form-control required select2 w50" name="id_business" id="id_business"></select>
                  <small class="text-red id_business hideIt">Business Category Can't be empty!</small>
                </td>
              </tr>

              <tr id="my-grid-tr-id_capacity">
                <td class="text-left vMid">Capacity</td>
                <td class="text-left">
                  <select class="form-control select2 select2-hidden-accessible" aria-hidden="true" name="id_capacity[]" id="id_capacity" multiple></select>
                </td>
              </tr>

              <tr id="my-grid-tr-id_category">
                <td class="text-left vMid">Product Category <span class='text-red'>*</span></td>
                <td class="text-left">
                  <div class="input-group w90">
                    <select class="form-control required select2" name="id_category" id="id_category"></select>
                    <button type="button" id="add_ProCat" class="btn btn-primary"><i class="fa fa-plus"></i></button>
                  </div>
                  <small class="text-red id_category hideIt">Product Category Can't be empty!</small>
                </td>
              </tr>

              <tr id="my-grid-tr-activation_factory">
                <td class="text-left vMid">Status Factory <span class="text-red">*</span></td>
                <td class="text-left">
                  <select class="form-control required select2" name="activation_factory" id="activation_factory" style="width:40%">
                    <option value=""></option>
                    <option value="active" <?= $getC->activation_factory == 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?= $getC->activation_factory == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                  </select>
                  <small class="text-red activation_factory hideIt">Status Can't be empty!</small>
                </td>
              </tr>

              <tr id="my-grid-tr-activation">
                <td class="text-left vMid">Status <span class="text-red">*</span></td>
                <td class="text-left">
                  <select class="form-control required select2" name="activation" id="activation">
                    <option value=""></option>
                    <option value="active" <?= $getC->activation == 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?= $getC->activation == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                  </select>
                  <small class="text-red activation hideIt">Status Can't be empty!</small>
                </td>
              </tr>

              <tr id="my-grid-tr-agent_name">
                <td class="text-left vMid">Agent Name</td>
                <td class="text-left">
                  <input name="agent_name" id="agent_name" class="form-control required w60 ucfirst" placeholder="Agent Name" value="<?= empty($getC->agent_name) ? '' : $getC->agent_name ?>"></input>
                </td>
              </tr>

              <tr id="my-grid-tr-remarks">
                <td class="text-left vMid">Remarks</td>
                <td class="text-left">
                  <textarea name="remarks" id="remarks" class="form-control required w60 ucfirst" placeholder="Remarks"><?= empty($getC->remarks) ? '' : $getC->remarks ?></textarea>
                </td>
              </tr>


              <!-- <tr id="my-grid-tr-id_brand">
                <td class="text-left vMid">Brand</td>
                <td class="text-left">
                  <label class="label_view">
                    <?php
                    if ($getC) {
                      foreach ($name_brand as $key => $v) {
                        echo ($key + 1) . ". " . $v->name_brand . "<br>";
                      }
                    }
                    ?>
                  </label>
                  <label class="label_input">
                    <select class="form-control select2 id_brand w70" name="id_brand[]" id="id_brand" multiple="multiple" style="margin-right:-18px">

                    </select>
                    <a id="addBrand" class="btn btn-sm btn-primary" style="display:inline-block"><i class="fa fa-plus">&nbsp;</i></a>

                  </label>
                </td>
              </tr> -->

            </tbody>
          </table>
          <br>
          <div class="bank_class">
            <table id="my-grid2" class="table-condensed" width="100%">
              <thead>
                <tr style='background-color: #175477; color: white; font-size: 15px;'>
                  <th class="text-center" colspan='3'>Bank Data</th>
                </tr>
              </thead>
            </table>
            <br>
            <?php $no = 0;
            foreach ($getSB as $key => $vb) : $no++; ?>
              <div class="bank_list" style="margin-bottom:2em">
                <span class="pull-right"><a class="text-red deleteBank" href="javacript:void(0)"><i class="fa fa-trash-o"></i> Delete Bank</a></span>
                <legend class="legend">Supplier Bank <span class="numbering_bank"><?= $no ?></span></legend>
                <h4 class="legend"><span style="width:100%; padding:2px;color:#7e7e7e"><strong>Beneficiary Bank Detail</strong></h4>
                <div class="row">
                  <div class="col-md-6" style="margin-top:1em">
                    <table class="table-condensed" width="100%">
                      <tbody>
                        <tr>
                          <td width="30%">Bank Name <span class="text-red">*</span></td>
                          <td>
                            <input type="text" name="bank_name[]" id="bank_name_<?= $no ?>" class="form-control required" value="<?= $vb->bank_name ?>" placeholder="Bank Name">
                            <small class="hideIt text-red bank_name_<?= $no ?>">Bank Name Can't be empty!</small>
                          </td>
                        </tr>
                        <tr>
                          <td>Bank Address <span class="text-red">*</span></td>
                          <td>
                            <textarea type="text" name="bank_address[]" id="bank_address<?= $no ?>" class="form-control required" placeholder="Bank Address"><?= $vb->bank_address ?></textarea>
                            <small class="hideIt text-red bank_address<?= $no ?>">Bank Address Can't be empty!</small>
                          </td>
                        </tr>
                        <tr>
                          <td>Beneficiary A/C Name <span class="text-red">*</span></td>
                          <td>
                            <input type="text" name="bank_beneficiary_name[]" id="bank_beneficiary_name<?= $no ?>" class="form-control required" value="<?= $vb->bank_beneficiary_name ?>" placeholder="Bank Benificiary Name">
                            <small class="hideIt text-red bank_beneficiary_name<?= $no ?>">Bank Benificiary Name Can't be empty!</small>
                          </td>
                        </tr>
                        <tr>
                          <td>Beneficiary A/C Number <span class="text-red">*</span></td>
                          <td>
                            <input type="text" name="bank_beneficiary_no[]" id="bank_beneficiary_no<?= $no ?>" class="form-control required" value="<?= $vb->bank_beneficiary_no ?>" placeholder="Bank Benificiary Number">
                            <small class="hideIt text-red bank_beneficiary_no<?= $no ?>">Bank Benificiary Number Can't be empty!</small>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <div class="col-md-6">
                    <table class="table-condensed" width="100%">
                      <tbody>
                        <tr>
                          <td width="30%">Swift Code</td>
                          <td>
                            <input type="text" name="swift_code[]" id="swift_code<?= $no ?>" class="form-control" value="<?= $vb->swift_code ?>" placeholder="Swift Code">
                            <small class="hideIt text-red swift_code<?= $no ?>">Swift Code Can't be empty!</small>
                          </td>
                        </tr>
                        <tr>
                          <td>IBAN</td>
                          <td>
                            <input type="text" name="iban[]" id="iban<?= $no ?>" class="form-control" value="<?= $vb->iban ?>" placeholder="IBAN">
                            <small class="hideIt text-red iban<?= $no ?>">IBAN Can't be empty!</small>
                          </td>
                        </tr>
                        <tr>
                          <td>BIC</td>
                          <td>
                            <input type="text" name="bic[]" id="bic<?= $no ?>" class="form-control" value="<?= $vb->bic ?>" placeholder="BIC">
                            <small class="hideIt text-red bic<?= $no ?>">BIC Can't be empty!</small>
                          </td>
                        </tr>
                        <tr>
                          <td>Currency</td>
                          <td>
                            <select type="text" name="currency[]" id="currency<?= $no ?>" class="form-control curs">
                              <option value="<?= $vb->currency ?>"><?= $vb->currency ?></option>
                            </select>
                            <small class="hideIt text-red currency<?= $no ?>">Currency Can't be empty!</small>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
                <h4 class="legend" style="padding-top:1em"><span style="width:100%; padding:2px;color:#7e7e7e;"><strong>Intermediary Bank Detail</strong></h4>
                <div class="row">
                  <div class="col-md-6">
                    <table width="100%" class="table-condensed">
                      <tbody>
                        <tr>
                          <td width="30%">Bank Name</td>
                          <td>
                            <input type="text" name="bank_name_intermediary[]" id="bank_name_intermediary<?= $no ?>" class="form-control" value="<?= $vb->bank_name_intermediary ?>" placeholder="Bank Name">
                            <small class="hideIt text-red bank_name_intermediary<?= $no ?>">Bank Name Can't be empty!</small>
                          </td>
                        </tr>
                        <tr>
                          <td>Bank Address</td>
                          <td>
                            <textarea type="text" name="bank_address_intermediary[]" id="bank_address_intermediary<?= $no ?>" class="form-control" placeholder="Bank Address"><?= $vb->bank_address_intermediary ?></textarea>
                            <small class="hideIt text-red bank_address_intermediary<?= $no ?>">Bank Name Can't be empty!</small>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <div class="col-md-6">
                    <table class="table-condensed" width="100%">
                      <tbody>
                        <tr>
                          <td>Swift Code</td>
                          <td>
                            <input type="text" name="swift_code_intermediary[]" id="swift_code_intermediary<?= $no ?>" class="form-control" value="<?= $vb->swift_code_intermediary ?>" placeholder="Swift Code">
                            <small class="hideIt text-red swift_code<?= $no ?>">Swift Code Can't be empty!</small>
                          </td>
                        </tr>
                        <tr>
                          <td>IBAN</td>
                          <td>
                            <input type="text" name="iban_intermediary[]" id="iban_intermediary<?= $no ?>" class="form-control" value="<?= $vb->iban_intermediary ?>" placeholder="IBAN">
                            <small class="hideIt text-red iban<?= $no ?>">IBAN Can't be empty!</small>
                          </td>
                        </tr>
                        <tr>
                          <td>BIC</td>
                          <td>
                            <input type="text" name="bic_intermediary[]" id="bic_intermediary<?= $no ?>" class="form-control" value="<?= $vb->bic_intermediary ?>" placeholder="BIC">
                            <small class="hideIt text-red bic<?= $no ?>">BIC Can't be empty!</small>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <br>
          <label class="label_input">
            <a class="btn btn-sm btn-success" id="addBankList">Add Bank List</a>
          </label>
          <br>
        </div>
      </div>

      <?php
      echo form_button(array('type' => 'button', 'class' => 'btn btn-md btn-success label_input', 'style' => 'min-width:100px; float:right;', 'value' => 'save', 'content' => 'Save', 'id' => 'addSupplierSave')) . ' ';
      ?>
    </div>
  </div>
</form>

<style>
  .inSp {
    text-align: center;
    display: inline-block;
    width: 100px;
  }

  .inSp2 {
    text-align: center;
    display: inline-block;
    width: 45%;
  }

  .inSpL {
    text-align: left;
  }

  .vMid {
    vertical-align: middle !important;
  }

  .w10 {
    display: inline-block;
    width: 10%;
  }

  .w15 {
    display: inline-block;
    width: 15%;
  }

  .w20 {
    display: inline-block;
    width: 20%;
  }

  .w30 {
    display: inline-block;
    width: 30%;
  }

  .w40 {
    display: inline-block;
    width: 40%;
  }

  .w50 {
    display: inline-block;
    width: 50%;
  }

  .w60 {
    display: inline-block;
    width: 60%;
  }

  .w70 {
    display: inline-block;
    width: 70%;
  }

  .w80 {
    display: inline-block;
    width: 80%;
  }

  .w90 {
    display: inline-block;
    width: 90%;
  }

  .hideIt {
    display: none;
  }

  .showIt {
    display: block;
  }
</style>

<script type="text/javascript">
  // PREVIEW PICTURE
  function tampilkanPreview(gambar, idpreview) {
    var gb = gambar.files;
    for (var i = 0; i < gb.length; i++) {
      var gbPreview = gb[i];
      var imageType = /image.*/;
      var preview = document.getElementById(idpreview);
      var reader = new FileReader();

      if (gbPreview.type.match(imageType)) {
        preview.file = gbPreview;
        reader.onload = (function(element) {
          return function(e) {
            element.src = e.target.result;
          };
        })(preview);
        reader.readAsDataURL(gbPreview);
      } else {
        reader.onload = (function(element) {
          return function(e) {
            // element.src = "../../../../images/avatar.png"; 
          };
        })(preview);
        reader.readAsDataURL(gbPreview);
        const Toast = Swal.mixin({
          toast: true,
          position: 'top',
          showConfirmButton: false,
          timer: 5000,
          onOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
          }
        })

        Toast.fire({
          icon: 'error',
          title: 'Type file tidak sesuai. Khusus file gambar.'
        })
        $('#gambar').val('');
        return false;
      }

    }
  }
  $(document).ready(function() {

    getCountry();
    getSupplierType();
    getCountryFactory();
    getCountryExcompany();
    getToq();
    $(".datepicker").datepicker({
      format: "yyyy-mm-dd",
      showInputs: true,
      autoclose: true
    });
    $(".select2").select2({
      placeholder: "Choose An Option",
      allowClear: true,
      width: '60%',
      dropdownParent: $("#form-supplier")
    });

    $(".select2-100").select2({
      placeholder: "Choose An Option",
      allowClear: true
    });
    $('.select2-search--inline').css('margin-right', '5%');
    $('.select2-search--inline').css('width', '100%');
    $('.select2-search__field').css('margin-right', '5%');
    $('.select2-search__field').css('width', '90% !important');
    $('.select2-search__field').css('padding-right', '5%');
    //ADD LIST BUTTON
    $('#addPICOffice').click(function(e) {
      var x = parseInt($(".picoffice_list").length) + 1; //var x = parseInt(document.getElementById("tfoot-pic").rows.length)+1;
      //console.log(x);
      var row = '' +

        '<div class="picoffice_list" style="padding-bottom:1.5em">' +
        '          <div class="pull-right">' +
        '            <a type="button" class="text-lg text-red deletePicOffice" href="javascript:void(0)" title="Delete PIC Office">' +
        '              <i class="fa fa-trash-o"></i> Delete PIC Office' +
        '            </a>' +
        '          </div>' +
        '            <legend class="legend">PIC Office <span class="numbering_picoffice">' + x + '</span></legend>' +
        '          <div class="row">' +
        '            <div class="col-md-6">' +
        '              <table class="table-condensed" width="100%">' +
        '                <tr>' +
        '                  <td width="30%">Name PIC <span class="text-red">*</span></td>' +
        '                  <td>' +
        '                    <input type="text" name="pic_name[]" id="pic_office_name' + x + '" class="form-control required" placeholder="PIC Name">' +
        '                    <small class="text-red pic_office_name' + x + ' hideIt">PIC Name Can\'t be empty!</small>' +
        '                  </td>' +
        '                </tr>' +
        '                <tr>' +
        '                  <td>Position <span class="text-red">*</span></td>' +
        '                  <td>' +
        '                    <input type="text" name="pic_position[]" id="pic_position' + x + '" class="form-control required" placeholder="PIC Position">' +
        '                    <small class="text-red pic_position' + x + ' hideIt">PIC Position Can\'t be empty!</small>' +
        '                  </td>' +
        '                </tr>' +
        '                <tr>' +
        '                  <td>Mobile Phone <span class="text-red">*</span></td>' +
        '                  <td>' +
        '                    <input type="text" name="pic_phone[]" id="pic_phone' + x + '" class="form-control required" placeholder="PIC Mobile Phone">' +
        '                    <small class="text-red pic_phone' + x + ' hideIt">PIC Mobile Phone Can\'t be empty!</small>' +
        '                  </td>' +
        '                </tr>' +
        '                <tr>' +
        '                  <td>WhatsApp Number <span class="text-red">*</span></td>' +
        '                  <td>' +
        '                    <input type="text" name="pic_wa[]" id="pic_wa' + x + '" class="form-control required" placeholder="PIC WhatsApp Number">' +
        '                    <small class="text-red pic_wa' + x + ' hideIt">PIC WhatsApp Number Can\'t be empty!</small>' +
        '                  </td>' +
        '                </tr>' +
        '                <tr>' +
        '                  <td>E-mail <span class="text-red">*</span></td>' +
        '                  <td>' +
        '                    <input type="text" name="pic_email[]" id="pic_email' + x + '" class="form-control required" placeholder="PIC E-mail">' +
        '                    <small class="text-red pic_email' + x + ' hideIt">PIC E-mail Can\'t be empty!</small>' +
        '                  </td>' +
        '                </tr>' +
        '                <tr>' +
        '                  <td>WeChat Id</td>' +
        '                  <td>' +
        '                    <input type="text" name="pic_wechat[]" id="pic_wechat' + x + '" class="form-control" placeholder="PIC WeChat Id">' +
        '                    <small class="text-red pic_wechat' + x + ' hideIt">PIC WeChat Id Can\'t be empty!</small>' +
        '                  </td>' +
        '                </tr>' +
        '                <tr>' +
        '                  <td>Website</td>' +
        '                  <td>' +
        '                    <input type="text" name="pic_web[]" id="pic_web' + x + '" class="form-control" placeholder="PIC Website">' +
        '                    <small class="text-red pic_web' + x + ' hideIt">PIC Website Can\'t be empty!</small>' +
        '                  </td>' +
        '                </tr>' +
        '              </table>' +
        '            </div>' +
        '            <div class="col-md-6">' +
        '              <button type="button" onclick=$("#image_' + x + '").click() class="btn btn-md btn-primary"><i class="fa fa-upload"></i> Upload PIC Card</button>' +
        '              <br>' +
        '              <input type="file" name="pic_card[]" id="image_' + x + '" class="hidden pic_card_office" onchange="tampilkanPreview(this,\'preview_' + x + '\')">' +
        '              <img id="preview_' + x + '" class="img-responsive" style="height: 250px;margin: 26px auto;width: auto;">' +
        '              <input type="hidden" name="filelama[]" id="filelama_' + x + '" class="form-control">' +
        '            </div>' +
        '          </div>' +
        '        </div>';

      $('.picoffice_class').append(row);

    });
    $('#addPICFactory').click(function(e) {
      var x = parseInt($(".picfactory_list").length) + 1; //var x = parseInt(document.getElementById("tfoot-pic").rows.length)+1;
      //console.log(x);
      var row = '' +
        '<div class="picfactory_list" style="padding-bottom:1.5em">' +
        '  <div class="pull-right">' +
        '    <a type="button" class="text-lg text-red deletePicFactory" href="javascript:void(0)" title="Delete PIC Factory">' +
        '      <i class="fa fa-trash-o"></i> Delete PIC Factory' +
        '    </a>' +
        '  </div>' +
        '  <legend class="legend">PIC Factory <span class="numbering_picfactory">' + x + '</span></legend>' +
        '  <div class="row">' +
        '    <div class="col-md-6">' +
        '      <table class="table-condensed" width="100%">' +
        '        <tr>' +
        '          <td width="30%">Name PIC <span class="text-red">*</span></td>' +
        '          <td>' +
        '            <input type="text" name="pic_name_factory[]" id="pic_name_factory' + x + '" class="form-control required" placeholder="PIC Name">' +
        '            <small class="text-red pic_name_factory' + x + ' hideIt">PIC Name Can\t be empty!</small>' +
        '          </td>' +
        '        </tr>' +
        '        <tr>' +
        '          <td>Position <span class="text-red" >*</span></td>' +
        '          <td>' +
        '            <input type="text" name="pic_position_factory[]" id="pic_position_factory' + x + '" class="form-control required" placeholder="PIC Position">' +
        '            <small class="text-red pic_position_factory' + x + ' hideIt">PIC Position Can\'t be empty!</small>' +
        '          </td>' +
        '        </tr>' +
        '        <tr>' +
        '          <td>Mobile Phone <span class="text-red" >*</span></td>' +
        '          <td>' +
        '            <input type="text" name="pic_phone_factory[]" id="pic_phone_factory' + x + '" class="form-control required" placeholder="PIC Mobile Phone">' +
        '            <small class="text-red pic_phone_factory' + x + ' hideIt">PIC Mobile Phone Can\'t be empty!</small>' +
        '          </td>' +
        '        </tr>' +
        '        <tr>' +
        '          <td>WhatsApp Number <span class="text-red" >*</span></td>' +
        '          <td>' +
        '            <input type="text" name="pic_wa_factory[]" id="pic_wa_factory' + x + '" class="form-control required" placeholder="PIC WhatsApp Number">' +
        '            <small class="text-red pic_wa_factory' + x + ' hideIt">PIC WhatsApp Number Can\'t be empty!</small>' +
        '          </td>' +
        '        </tr>' +
        '        <tr>' +
        '          <td>E-mail <span class="text-red" >*</span></td>' +
        '          <td>' +
        '            <input type="text" name="pic_email_factory[]" id="pic_email_factory' + x + '" class="form-control required" placeholder="PIC E-mail">' +
        '            <small class="text-red pic_email_factory' + x + ' hideIt">PIC E-mail Can\'t be empty!</small>' +
        '          </td>' +
        '        </tr>' +
        '        <tr>' +
        '          <td>WeChat Id</td>' +
        '          <td>' +
        '            <input type="text" name="pic_wechat_factory[]" id="pic_wechat_factory' + x + '" class="form-control" placeholder="PIC WeChat Id">' +
        '            <small class="text-red pic_wechat_factory' + x + ' hideIt">PIC WeChat Id Can\'t be empty!</small>' +
        '          </td>' +
        '        </tr>' +
        '        <tr>' +
        '          <td>Website</td>' +
        '          <td>' +
        '            <input type="text" name="pic_web_factory[]" id="pic_web_factory' + x + '" class="form-control" placeholder="PIC Website">' +
        '            <small class="text-red pic_web_factory' + x + ' hideIt">PIC Website Can\'t be empty!</small>' +
        '          </td>' +
        '        </tr>' +
        '      </table>' +
        '    </div>' +
        '    <div class="col-md-6">' +
        '      <button type="button" onclick=$("#image_factory' + x + '").click() class="btn btn-md btn-primary"><i class="fa fa-upload"></i> Upload PIC Card</button>' +
        '      <br>' +
        '      <input type="file" name="pic_card_factory[]" id="image_factory' + x + '" class="hidden pic_card_office" onchange="tampilkanPreview(this,\'preview_factory' + x + '\')">' +
        '      <img id="preview_factory' + x + '" class="img-responsive" style="height: 250px;margin: 26px auto;width: auto;">' +
        '      <input type="hidden" name="filelama_factory[]" id="filelama_factory' + x + '" class="form-control">' +
        '    </div>' +
        '  </div>' +
        '</div>';
      $('.picfactory_class').append(row);

    });
    $('#addPICExcompany').click(function(e) {
      var x = parseInt($(".picexcompany_list").length) + 1; //var x = parseInt(document.getElementById("tfoot-pic").rows.length)+1;
      //console.log(x);picexcompany_list
      var row = '' +
        '<div class="picexcompany_list" style="padding-bottom:1.5em">' +
        '  <div class="pull-right">' +
        '    <a type="button" class="text-lg text-red deletePicExcompany" href="javascript:void(0)" title="Delete PIC Excompany">' +
        '      <i class="fa fa-trash-o"></i> Delete PIC Excompany' +
        '    </a>' +
        '  </div>' +
        '  <legend class="legend">PIC Excompany <span class="numbering_picexcompany">' + x + '</span></legend>' +
        '  <div class="row">' +
        '    <div class="col-md-6">' +
        '      <table class="table-condensed" width="100%">' +
        '        <tr>' +
        '          <td width="30%">Name PIC <span class="text-red">*</span></td>' +
        '          <td>' +
        '            <input type="text" name="pic_name_excompany[]" id="pic_name_excompany' + x + '" class="form-control required" placeholder="PIC Name">' +
        '            <small class="text-red pic_name_excompany' + x + ' hideIt">PIC Name Can\t be empty!</small>' +
        '          </td>' +
        '        </tr>' +
        '        <tr>' +
        '          <td>Position <span class="text-red" >*</span></td>' +
        '          <td>' +
        '            <input type="text" name="pic_position_excompany[]" id="pic_position_excompany' + x + '" class="form-control required" placeholder="PIC Position">' +
        '            <small class="text-red pic_position_excompany' + x + ' hideIt">PIC Position Can\'t be empty!</small>' +
        '          </td>' +
        '        </tr>' +
        '        <tr>' +
        '          <td>Mobile Phone <span class="text-red" >*</span></td>' +
        '          <td>' +
        '            <input type="text" name="pic_phone_excompany[]" id="pic_phone_excompany' + x + '" class="form-control required" placeholder="PIC Mobile Phone">' +
        '            <small class="text-red pic_phone_excompany' + x + ' hideIt">PIC Mobile Phone Can\'t be empty!</small>' +
        '          </td>' +
        '        </tr>' +
        '        <tr>' +
        '          <td>WhatsApp Number <span class="text-red" >*</span></td>' +
        '          <td>' +
        '            <input type="text" name="pic_wa_excompany[]" id="pic_wa_excompany' + x + '" class="form-control required" placeholder="PIC WhatsApp Number">' +
        '            <small class="text-red pic_wa_excompany' + x + ' hideIt">PIC WhatsApp Number Can\'t be empty!</small>' +
        '          </td>' +
        '        </tr>' +
        '        <tr>' +
        '          <td>E-mail <span class="text-red" >*</span></td>' +
        '          <td>' +
        '            <input type="text" name="pic_email_excompany[]" id="pic_email_excompany' + x + '" class="form-control required" placeholder="PIC E-mail">' +
        '            <small class="text-red pic_email_excompany' + x + ' hideIt">PIC E-mail Can\'t be empty!</small>' +
        '          </td>' +
        '        </tr>' +
        '        <tr>' +
        '          <td>WeChat Id</td>' +
        '          <td>' +
        '            <input type="text" name="pic_wechat_excompany[]" id="pic_wechat_excompany' + x + '" class="form-control" placeholder="PIC WeChat Id">' +
        '            <small class="text-red pic_wechat_excompany' + x + ' hideIt">PIC WeChat Id Can\'t be empty!</small>' +
        '          </td>' +
        '        </tr>' +
        '        <tr>' +
        '          <td>Website</td>' +
        '          <td>' +
        '            <input type="text" name="pic_web_excompany[]" id="pic_web_excompany' + x + '" class="form-control" placeholder="PIC Website">' +
        '            <small class="text-red pic_web_excompany' + x + ' hideIt">PIC Website Can\'t be empty!</small>' +
        '          </td>' +
        '        </tr>' +
        '      </table>' +
        '    </div>' +
        '    <div class="col-md-6">' +
        '      <button type="button" onclick=$("#image_excompany' + x + '").click() class="btn btn-md btn-primary"><i class="fa fa-upload"></i> Upload PIC Card</button>' +
        '      <br>' +
        '      <input type="file" name="pic_card_excompany[]" id="image_excompany' + x + '" class="hidden pic_card_excompany" onchange="tampilkanPreview(this,\'preview_excompany' + x + '\')">' +
        '      <img id="preview_excompany' + x + '" class="img-responsive" style="height: 250px;margin: 26px auto;width: auto;">' +
        '      <input type="hidden" name="filelama_excompany[]" id="filelama_excompany' + x + '" class="form-control">' +
        '    </div>' +
        '  </div>' +
        '</div>';

      $('.picexcompany_class').append(row);

    });

    $('#addBankList').click(function(e) {
      var x = parseInt($(".bank_list").length) + 1;
      // console.log(x);
      var row = '' +
        ' <div class="bank_list" style="margin-bottom:2em">' +
        '  <span class="pull-right"><a class="text-red deleteBank" href="javacript:void(0)"><i class="fa fa-trash-o"></i> Delete Bank</a></span>' +
        '  <legend class="legend">Supplier Bank <span class="numbering_bank">' + x + '</span></legend>' +
        '  <h4 class="legend"><span style="width:100%; padding:2px;color:#7e7e7e"><strong>Beneficiary Bank Detail</strong></h4>' +
        '  <div class="row">' +
        '    <div class="col-md-6" style="margin-top:1em">' +
        '      <table class="table-condensed" width="100%">' +
        '        <tbody>' +
        '          <tr>' +
        '            <td width="30%">Bank Name <span class="text-red">*</span></td>' +
        '            <td>' +
        '              <input type="text" name="bank_name[]" id="bank_name_' + x + '" class="form-control required" placeholder="Bank Name">' +
        '              <small class="hideIt text-red bank_name_' + x + '">Bank Name Can\'t be empty!</small>' +
        '            </td>' +
        '          </tr>' +
        '          <tr>' +
        '            <td>Bank Address <span class="text-red">*</span></td>' +
        '            <td>' +
        '              <textarea type="text" name="bank_address[]" id="bank_address' + x + '" class="form-control required" placeholder="Bank Address"></textarea>' +
        '              <small class="hideIt text-red bank_address' + x + '">Bank Address Can\'t be empty!</small>' +
        '            </td>' +
        '          </tr>' +
        '          <tr>' +
        '            <td>Beneficiary A/C Name <span class="text-red">*</span></td>' +
        '            <td>' +
        '              <input type="text" name="bank_beneficiary_name[]" id="bank_beneficiary_name' + x + '" class="form-control required" placeholder="Bank Benificiary Name">' +
        '              <small class="hideIt text-red bank_beneficiary_name' + x + '">Bank Benificiary Name Can\'t be empty! < /small>' +
        '            </td>' +
        '          </tr>' +
        '          <tr>' +
        '            <td>Beneficiary A/C Number <span class="text-red">*</span></td>' +
        '            <td>' +
        '              <input type="text" name="bank_beneficiary_no[]" id="bank_beneficiary_no' + x + '" class="form-control required" placeholder="Bank Benificiary Number">' +
        '              <small class="hideIt text-red bank_beneficiary_no' + x + '">Bank Benificiary Number Can\'t be empty! < /small>' +
        '            </td>' +
        '          </tr>' +
        '        </tbody>' +
        '      </table>' +
        '    </div>' +
        '    <div class="col-md-6">' +
        '      <table class="table-condensed" width="100%">' +
        '        <tbody>' +
        '          <tr>' +
        '            <td width="30%">Swift Code</td>' +
        '            <td>' +
        '              <input type="text" name="swift_code[]" id="swift_code' + x + '" class="form-control" placeholder="Swift Code">' +
        '              <small class="hideIt text-red swift_code' + x + '">Swift Code Can\'t be empty! < /small>' +
        '            </td>' +
        '          </tr>' +
        '          <tr>' +
        '            <td>IBAN</td>' +
        '            <td>' +
        '              <input type="text" name="iban[]" id="iban' + x + '" class="form-control" placeholder="IBAN">' +
        '              <small class="hideIt text-red iban' + x + '">IBAN Can\'t be empty! < /small>' +
        '            </td>' +
        '          </tr>' +
        '          <tr>' +
        '            <td>BIC</td>' +
        '            <td>' +
        '              <input type="text" name="bic[]" id="bic' + x + '" class="form-control" placeholder="BIC">' +
        '              <small class="hideIt text-red bic' + x + '">BIC Can\'t be empty! < /small>' +
        '            </td>' +
        '          </tr>' +
        '          <tr>' +
        '            <td>Currency</td>' +
        '            <td>' +
        '              <select name="currency[]" id="currency' + x + '" class="form-control curs"></select>' +
        '              <small class="hideIt text-red currency' + x + '">Currency Can\'t be empty! < /small>' +
        '            </td>' +
        '          </tr>' +
        '        </tbody>' +
        '      </table>' +
        '    </div>' +
        '  </div>' +
        '  <h4 class="legend" style="padding-top:1em"><span style="width:100%; padding:2px;color:#7e7e7e;"><strong>Intermediary Bank Detail</strong></h4>' +
        '  <div class="row">' +
        '    <div class="col-md-6">' +
        '      <table width="100%" class="table-condensed">' +
        '        <tbody>' +
        '          <tr>' +
        '            <td width="30%">Bank Name</td>' +
        '            <td>' +
        '              <input type="text" name="bank_name_intermediary[]" id="bank_name_intermediary' + x + '" class="form-control" placeholder="Bank Name">' +
        '              <small class="hideIt text-red bank_name_intermediary' + x + '">Bank Name Can\'t be empty! < /small>' +
        '            </td>' +
        '          </tr>' +
        '          <tr>' +
        '            <td>Bank Address</td>' +
        '            <td>' +
        '              <textarea type="text" name="bank_address_intermediary[]" id="bank_address_intermediary' + x + '" class="form-control" placeholder="Bank Address"></textarea>' +
        '              <small class="hideIt text-red bank_name_intermediary' + x + '">Bank Name Can\'t be empty! < /small>' +
        '            </td>' +
        '          </tr>' +
        '        </tbody>' +
        '      </table>' +
        '    </div>' +
        '    <div class="col-md-6">' +
        '      <table class="table-condensed" width="100%">' +
        '        <tbody>' +
        '          <tr>' +
        '            <td>Swift Code</td>' +
        '            <td>' +
        '              <input type="text" name="swift_code_intermediary[]" id="swift_code_intermediary' + x + '" class="form-control" placeholder="Swift Code">' +
        '              <small class="hideIt text-red swift_code' + x + '">Swift Code Can\'t be empty! < /small>' +
        '            </td>' +
        '          </tr>' +
        '          <tr>' +
        '            <td>IBAN</td>' +
        '            <td>' +
        '              <input type="text" name="iban_intermediary[]" id="iban_intermediary' + x + '" class="form-control" placeholder="IBAN">' +
        '              <small class="hideIt text-red iban' + x + '">IBAN Can\'t be empty! < /small>' +
        '            </td>' +
        '          </tr>' +
        '          <tr>' +
        '            <td>BIC</td>' +
        '            <td>' +
        '              <input type="text" name="bic_intermediary[]" id="bic_intermediary' + x + '" class="form-control" placeholder="BIC">' +
        '              <small class="hideIt text-red bic' + x + '">BIC Can\'t be empty! < /small>' +
        '            </td>' +
        '          </tr>' +
        '        </tbody>' +
        '      </table>' +
        '    </div>' +
        '  </div>' +
        '</div>';

      $('.bank_class').append(row);
      getCurs_js(x);
      $(".curs").select2({
        placeholder: "Choose An Option",
        allowClear: true,
        width: '100%',
        dropdownParent: $("#form-supplier")
      });

    });

    //REMOVE LIST BUTTON
    $('#tfoot-pic').on('click', 'a.hapus_item_js', function() {
      //console.log('a');
      $(this).parents('tr').remove();
      if (parseInt(document.getElementById("tfoot-pic").rows.length) == 0) {
        var x = 1;
      } else {
        var x = parseInt(document.getElementById("tfoot-pic").rows.length) + 1;
      }
      for (var i = 0; i < x; i++) {
        $('.numbering').eq(i - 1).text(i);
      }
    });
    $('.picoffice_class').on('click', '.deletePicOffice', function() {
      //console.log('a');
      $(this).parents('.picoffice_list').remove();
      if (parseInt($(".picoffice_list").length) == 0) {
        var x = 1;
      } else {
        var x = parseInt($(".picoffice_list").length) + 1;
      }
      for (var i = 0; i < x; i++) {
        $('.numbering_picoffice').eq(i - 1).text(i);
      }
      /*if (parseInt(document.getElementById("tfoot-pic").rows.length) == 0) {
        var x=1;
      }else {
        var x = parseInt(document.getElementById("tfoot-pic").rows.length)+1;
      }
      for (var i = 0; i < x; i++) {
        $('.numbering').eq(i-1).text(i);
      }*/
    });
    $('.picfactory_class').on('click', '.deletePicFactory', function() {
      //console.log('a');
      $(this).parents('.picfactory_list').remove();
      if (parseInt($(".picfactory_list").length) == 0) {
        var x = 1;
      } else {
        var x = parseInt($(".picfactory_list").length) + 1;
      }
      for (var i = 0; i < x; i++) {
        $('.numbering_picfactory').eq(i - 1).text(i);
      }
    });
    $('.picexcompany_class').on('click', '.deletePicExcompany', function() {
      //console.log('a');
      $(this).parents('.picexcompany_list').remove();
      if (parseInt($(".picexcompany_list").length) == 0) {
        var x = 1;
      } else {
        var x = parseInt($(".picexcompany_list").length) + 1;
      }
      for (var i = 0; i < x; i++) {
        $('.numbering_picexcompany').eq(i - 1).text(i);
      }
    });
    $('.bank_class').on('click', '.deleteBank', function() {
      //console.log('a');
      $(this).parents('.bank_list').remove();
      if (parseInt($(".bank_list").length) == 0) {
        var x = 1;
      } else {
        var x = parseInt($(".bank_list").length) + 1;
      }
      for (var i = 0; i < x; i++) {
        $('.numbering_bank').eq(i - 1).text(i);
      }
    });


    jQuery(document).on('keyup', '.bank_num', function() {
      var foo = $(this).val().split("-").join(""); // remove hyphens
      if (foo.length > 0) {
        foo = foo.match(new RegExp('.{1,4}', 'g')).join("-");
      }
      $(this).val(foo);
    });
    jQuery(document).on('keyup keypress blur paste', '.ucfirst', function() {
      var string = $(this).val();
      $(this).val(string.charAt(0).toUpperCase() + string.slice(1));
    });


    $(document).on('click', '#saveSelBrand', function(e) {
      var formdata = $("#form-selBrand").serialize();
      var selected = '';
      $('#my-grid input:checked').each(function() {
        //selected.push($(this).val());
        selected = selected + $(this).val() + ';';
      });
      //console.log(selected);
      $('#id_brand').val(selected);
      $("#ModalView2").modal('hide');
    });
    $(document).on('blur', '#nm_supplier_office', function(e) {
      var name = $('#nm_supplier_office').val();
      if (name != '') {
        $.ajax({
          url: siteurl + active_controller + "getID",
          dataType: "json",
          type: 'POST',
          data: {
            nm: name
          },
          success: function(result) {
            $('#id_supplier').val(result.id);
          },
          error: function(request, error) {
            console.log(arguments);
            alert(" Can't do because: " + error);
          }
        });
      } else {
        swal({
          title: "Warning!",
          text: "Complete Supplier name first, please!",
          type: "warning",
          timer: 3500,
          showConfirmButton: false
        });
      }

    });

    $(document).on('change', '#payment_option', function(e) {
      var val = $(this).val();
      if (val == 'credit') {
        $('#credit_term').css({
          "display": "block"
        }).fadeIn(1000);
      } else {
        $('#credit_term').fadeOut(1000).css({
          "display": "none"
        });
      }

    });
    $(document).on('click', '.radioShipping', function(e) {
      getProCat();
    });
    $(document).on('change', '#id_type', function(e) {
      getBusCat();
    });
    $(document).on('change', '#id_business', function(e) {
      getSupCap();
    });
    $(document).on('change', '#id_capacity', function(e) {
      //  console.log($(this).val());
    });
    $(document).on('change', '#id_country', function(e) {
      var id_country = $(this).val();
      getProv(id_country);
      getCodeCountry();
    });
    $(document).on('change', '#id_prov', function(e) {
      var id_prov = $(this).val();
      getCity(id_prov);
    });
    $(document).on('change', '#id_country_factory', function(e) {
      var id_country = $(this).val();
      getProvFactory(id_country);
    });
    $(document).on('change', '#id_prov_factory', function(e) {
      var id_prov = $(this).val();
      getCityFactory(id_prov);
    });
    $(document).on('change', '#id_country_excompany', function(e) {
      var id_country = $(this).val();
      getProvExcompany(id_country);
    });
    $(document).on('change', '#id_prov_excompany', function(e) {
      var id_prov = $(this).val();
      getCityExcompany(id_prov);
    });




    $(document).on('click change keyup paste blur', '#form-supplierX .form-control', function(e) {
      //console.log('AHAHAHAHA');
      var val = $(this).val();
      var id = $(this).attr('id');
      if (val == '') {
        //$('.'+id).addClass('hideIt');
        $('.' + id).css('display', 'inline-block');
      } else {
        $('.' + id).css('display', 'none');
      }
    });
    if ('<?php $getC ?>' != '' || '<?php $getC ?>' != null) {
      getProCat();
      getBusCat();
      getSupCap();
    }
    if ('<?= $this->uri->segment(4) ?>' == 'view') {
      $('.label_view').css("display", "block");
      $('.label_input').css("display", "none");
    } else {
      $('.label_view').css("display", "none");
      $('.label_input').css("display", "block");
    }
    console.log('<?= $this->uri->segment(4) ?>');
  });

  function getCountry() {
    if ('<?= ($getC->id_country) ?>' == null || '<?= ($getC->id_country) ?>' == '') {
      var id_selected = '';
    } else {
      var id_selected = '<?= $getC->id_country ?>';
      getProv(id_selected);
    }
    //console.log(id_selected);
    var column = '';
    var column_fill = '';
    var column_name = 'nm_negara';
    var table_name = 'negara';
    var key = 'id';
    var act = 'free';
    $.ajax({
      url: siteurl + active_controller + "getOpt",
      dataType: "json",
      type: 'POST',
      data: {
        id_selected: id_selected,
        column: column,
        column_fill: column_fill,
        column_name: column_name,
        table_name: table_name,
        key: key,
        act: act
      },
      success: function(result) {
        $('#id_country').html(result.html);
      },
      error: function(request, error) {
        console.log(arguments);
        alert(" Can't do because: " + error);
      }
    });
  }

  function getProv(id_country = '') {
    if ('<?= ($getC->id_prov) ?>' == null || '<?= ($getC->id_prov) ?>' == '') {
      var id_selected = '';
    } else {
      var id_selected = '<?= $getC->id_prov ?>';
      getCity(id_selected);
    }
    //console.log(id_selected);
    var column = 'id_negara';
    var column_fill = id_country;
    var column_name = 'nama';
    var table_name = 'provinsi';
    var key = 'id_prov';
    var act = 'free';
    $.ajax({
      url: siteurl + active_controller + "getOpt",
      dataType: "json",
      type: 'POST',
      data: {
        id_selected: id_selected,
        column: column,
        column_fill: column_fill,
        column_name: column_name,
        table_name: table_name,
        key: key,
        act: act
      },
      success: function(result) {
        $('#id_prov').html(result.html);
      },
      error: function(request, error) {
        console.log(arguments);
        alert(" Can't do because: " + error);
      }
    });
  }

  function getCity(id_prov = '') {
    if ('<?= ($getC->city_office) ?>' == null || '<?= ($getC->city_office) ?>' == '') {
      var id_selected = '';
    } else {
      var id_selected = '<?= $getC->city_office ?>';
    }
    //console.log(id_selected);
    var column = 'id_prov';
    var column_fill = id_prov;
    var column_name = 'nama';
    var table_name = 'kabupaten';
    var key = 'id_kab';
    var act = 'free';
    $.ajax({
      url: siteurl + active_controller + "getOpt",
      dataType: "json",
      type: 'POST',
      data: {
        id_selected: id_selected,
        column: column,
        column_fill: column_fill,
        column_name: column_name,
        table_name: table_name,
        key: key,
        act: act
      },
      success: function(result) {
        $('#city_office').html(result.html);
      },
      error: function(request, error) {
        console.log(arguments);
        alert(" Can't do because: " + error);
      }
    });
  }


  function getCodeCountry() {

    var id_selected = $('#id_country').val();
    //console.log(id_selected);
    var column = 'code';
    var column_fill = '';
    var column_name = 'name_country';
    var table_name = 'master_country';
    var key = 'id_country';
    var act = 'free';
    $.ajax({
      url: siteurl + active_controller + "getVal",
      dataType: "json",
      type: 'POST',
      data: {
        id_selected: id_selected,
        column: column,
        column_fill: column_fill,
        column_name: column_name,
        table_name: table_name,
        key: key,
        act: act
      },
      success: function(result) {
        $('.telephone_code').val(result.html);
      },
      error: function(request, error) {
        console.log(arguments);
        alert(" Can't do because: " + error);
      }
    });

  }

  function getCurs_js(x) {
    var id_selected = '';
    var column = '';
    var column_fill = '';
    var column_name = 'kode';
    var table_name = 'mata_uang';
    var key = 'id_kurs';
    var act = 'free';
    $.ajax({
      url: siteurl + active_controller + "getOpt",
      dataType: "json",
      type: 'POST',
      data: {
        id_selected: id_selected,
        column: column,
        column_fill: column_fill,
        column_name: column_name,
        table_name: table_name,
        key: key,
        act: act
      },
      success: function(result) {
        $('#currency' + x).html(result.html);
      },
      error: function(request, error) {
        console.log(arguments);
        alert(" Can't do because: " + error);
      }
    });
  }

  function getSupplierType() {
    if ('<?= ($getC->id_type) ?>' != null) {
      var id_selected = '<?= $getC->id_type ?>';
    } else if ($('#id_type').val() != null || $('#id_type').val() != '') {
      var id_selected = $('#id_type').val();
    } else {
      var id_selected = '';
    }
    var column = '';
    var column_fill = '';
    var column_name = 'name_type';
    var table_name = 'child_supplier_type';
    var key = 'id_type';
    var act = '';
    $.ajax({
      url: siteurl + active_controller + "getOpt",
      dataType: "json",
      type: 'POST',
      data: {
        id_selected: id_selected,
        column: column,
        column_fill: column_fill,
        column_name: column_name,
        table_name: table_name,
        key: key,
        act: act
      },
      success: function(result) {
        $('#id_type').html(result.html);
      },
      error: function(request, error) {
        console.log(arguments);
        alert(" Can't do because: " + error);
      }
    });
  }

  function getProCat() {
    if ('<?= ($getC->id_product_category) ?>' == '') {
      var id_selected = '';
    } else {
      var id_selected = '<?= $getC->id_product_category ?>';
    }
    var column = 'shipping';
    var column_fill = $("input[name='supplier_shipping']:checked").val();
    var column_name = 'name_category';
    var table_name = 'master_product_category';
    var key = 'id_category';
    var act = 'free';
    $.ajax({
      url: siteurl + active_controller + "getOpt",
      dataType: "json",
      type: 'POST',
      data: {
        id_selected: id_selected,
        column: column,
        column_fill: column_fill,
        column_name: column_name,
        table_name: table_name,
        key: key,
        act: act
      },
      success: function(result) {
        $('#id_category').html(result.html);
      },
      error: function(request, error) {
        console.log(arguments);
        alert(" Can't do because: " + error);
      }
    });
  }

  function getBusCat() {
    if ('<?= ($getC->id_business) ?>' == '') {
      var id_selected = '';
    } else {
      var id_selected = '<?= $getC->id_business ?>';
    }
    var column = 'id_type';
    var column_fill = $("#id_type").val();
    var column_name = 'name_business';
    var table_name = 'child_supplier_business_category';
    var key = 'id_business';
    var act = 'free';
    $.ajax({
      url: siteurl + active_controller + "getOpt",
      dataType: "json",
      type: 'POST',
      data: {
        id_selected: id_selected,
        column: column,
        column_fill: column_fill,
        column_name: column_name,
        table_name: table_name,
        key: key,
        act: act
      },
      success: function(result) {
        $('#id_business').html(result.html);
      },
      error: function(request, error) {
        console.log(arguments);
        alert(" Can't do because: " + error);
      }
    });
  }

  function getSupCap() {
    var id_capacity = <?php echo json_encode(explode(";", $getC->id_capacity)); ?>;
    var id_selected = 'multiple';
    //console.log(id_selected);
    var column = 'id_business';
    var column_fill = $('#id_business').val();
    var column_name = 'name_capacity';
    var table_name = 'child_supplier_capacity';
    var key = 'id_capacity';
    var act = 'free';
    $.ajax({
      url: siteurl + active_controller + "getOpt",
      dataType: "json",
      type: 'POST',
      data: {
        id_selected: id_selected,
        column: column,
        column_fill: column_fill,
        column_name: column_name,
        table_name: table_name,
        key: key,
        act: act
      },
      success: function(result) {
        $('#id_capacity').html(result.html);
        $('#id_capacity').val(id_capacity);
      },
      error: function(request, error) {
        console.log(arguments);
        alert(" Can't do because: " + error);
      }
    });
  }

  function getToq() {
    if ('<?= ($getC->id_toq) ?>' != null) {
      var id_selected = '<?= $getC->id_toq ?>';
    } else if ($('#id_toq').val() != null || $('#id_toq').val() != '') {
      var id_selected = $('#id_toq').val();
    } else {
      var id_selected = '';
    }
    //console.log(id_selected);
    var column = '';
    var column_fill = '';
    var column_name = 'name_toq';
    var table_name = 'child_supplier_toq';
    var key = 'id_toq';
    var act = '';
    $.ajax({
      url: siteurl + active_controller + "getOpt",
      dataType: "json",
      type: 'POST',
      data: {
        id_selected: id_selected,
        column: column,
        column_fill: column_fill,
        column_name: column_name,
        table_name: table_name,
        key: key,
        act: act
      },
      success: function(result) {
        $('#id_toq').html(result.html);
      },
      error: function(request, error) {
        console.log(arguments);
        alert(" Can't do because: " + error);
      }
    });

  }

  function getValidationX() {
    var count = 0;
    var success = true;
    $(".required").each(function() {
      var node = $(this).prop('nodeName');
      var type = $(this).attr('type');
      //console.log(type);
      var success = true;
      if (node == 'INPUT' && type == 'radio') {
        //$("input[name='"+$(this).attr('id')+"']:checked").val();
        var c = 0;
        //console.log($(this).attr('name'));
        //console.log($("."+$(this).attr('name')).parents('td').html());
        $("input[name='" + $(this).attr('name') + "']").each(function() {
          if ($(this).prop('checked') == true) {
            c++;
          }
        });
        if (c == 0) {
          //console.log('berhasil');

          var name = $(this).attr('name');
          $('.' + name).removeClass('hideIt');
          $('.' + name).css('display', 'inline-block');
          $('html, body, .modal').animate({
            scrollTop: $("#form-supplier").offset().top
          }, 2000);
          count = count + 1;
        }

      } else if ((node == 'INPUT' && type == 'text') || (node == 'SELECT')) {
        if ($(this).val() == null || $(this).val() == '') {
          var name = $(this).attr('id');

          name.replace('[]', '');
          $('.' + name).removeClass('hideIt');
          $('.' + name).css('display', 'inline-block');
          $('html, body, .modal').animate({
            scrollTop: $("#form-supplier").offset().top
          }, 2000);
          //console.log(name);
          count = count + 1;
        }
      }

    });
    console.log(count);
    if (count == 0) {
      //console.log(success);
      return success;
    } else {
      return false;
    }
  }


  function getCountryFactory() {
    if ('<?= ($getC->id_country_factory) ?>' == null || '<?= ($getC->id_country_factory) ?>' == '') {
      var id_selected = '';
    } else {
      var id_selected = '<?= $getC->id_country_factory ?>';
      getProvFactory(id_selected);
    }
    //console.log(id_selected);
    var column = '';
    var column_fill = '';
    var column_name = 'nm_negara';
    var table_name = 'negara';
    var key = 'id';
    var act = 'free';
    $.ajax({
      url: siteurl + active_controller + "getOpt",
      dataType: "json",
      type: 'POST',
      data: {
        id_selected: id_selected,
        column: column,
        column_fill: column_fill,
        column_name: column_name,
        table_name: table_name,
        key: key,
        act: act
      },
      success: function(result) {
        $('#id_country_factory').html(result.html);
      },
      error: function(request, error) {
        console.log(arguments);
        alert(" Can't do because: " + error);
      }
    });
  }

  function getCountryExcompany() {
    if ('<?= ($getC->id_country_excompany) ?>' == '') {
      var id_selected = '';
    } else {
      var id_selected = '<?= $getC->id_country_excompany ?>';
      getProvExcompany(id_selected);
    }
    //console.log(id_selected);
    var column = '';
    var column_fill = '';
    var column_name = 'nm_negara';
    var table_name = 'negara';
    var key = 'id';
    var act = 'free';
    $.ajax({
      url: siteurl + active_controller + "getOpt",
      dataType: "json",
      type: 'POST',
      data: {
        id_selected: id_selected,
        column: column,
        column_fill: column_fill,
        column_name: column_name,
        table_name: table_name,
        key: key,
        act: act
      },
      success: function(result) {
        $('#id_country_excompany').html(result.html);
      },
      error: function(request, error) {
        console.log(arguments);
        alert(" Can't do because: " + error);
      }
    });
  }

  function getProvFactory(id_country = '') {
    if ('<?= ($getC->id_prov_factory) ?>' == null || '<?= ($getC->id_prov_factory) ?>' == '') {
      var id_selected = '';
    } else {
      var id_selected = '<?= $getC->id_prov_factory ?>';
      getCityFactory(id_selected);
    }
    //console.log(id_selected);
    var column = 'id_negara';
    var column_fill = id_country;
    var column_name = 'nama';
    var table_name = 'provinsi';
    var key = 'id_prov';
    var act = 'free';
    $.ajax({
      url: siteurl + active_controller + "getOpt",
      dataType: "json",
      type: 'POST',
      data: {
        id_selected: id_selected,
        column: column,
        column_fill: column_fill,
        column_name: column_name,
        table_name: table_name,
        key: key,
        act: act
      },
      success: function(result) {
        $('#id_prov_factory').html(result.html);
      },
      error: function(request, error) {
        console.log(arguments);
        alert(" Can't do because: " + error);
      }
    });
  }


  function getProvExcompany(id_country = "") {
    if ('<?= ($getC->id_prov_excompany) ?>' == '') {
      var id_selected = '';
    } else {
      var id_selected = '<?= $getC->id_prov_excompany ?>';
      getCityExcompany(id_selected);
    }
    //console.log(id_selected);
    var column = 'id_negara';
    var column_fill = id_country;
    var column_name = 'nama';
    var table_name = 'provinsi';
    var key = 'id_prov';
    var act = 'free';
    $.ajax({
      url: siteurl + active_controller + "getOpt",
      dataType: "json",
      type: 'POST',
      data: {
        id_selected: id_selected,
        column: column,
        column_fill: column_fill,
        column_name: column_name,
        table_name: table_name,
        key: key,
        act: act
      },
      success: function(result) {
        $('#id_prov_excompany').html(result.html);
      },
      error: function(request, error) {
        console.log(arguments);
        alert(" Can't do because: " + error);
      }
    });
  }

  function getCityFactory(id_prov = "") {
    if ('<?= ($getC->city_factory) ?>' == null || '<?= ($getC->city_factory) ?>' == '') {
      var id_selected = '';
    } else {
      var id_selected = '<?= $getC->city_factory ?>';
    }
    //console.log(id_selected);
    var column = 'id_prov';
    var column_fill = id_prov;
    var column_name = 'nama';
    var table_name = 'kabupaten';
    var key = 'id_kab';
    var act = 'free';
    $.ajax({
      url: siteurl + active_controller + "getOpt",
      dataType: "json",
      type: 'POST',
      data: {
        id_selected: id_selected,
        column: column,
        column_fill: column_fill,
        column_name: column_name,
        table_name: table_name,
        key: key,
        act: act
      },
      success: function(result) {
        $('#city_factory').html(result.html);
      },
      error: function(request, error) {
        console.log(arguments);
        alert(" Can't do because: " + error);
      }
    });
  }

  function getCityExcompany(id_prov = "") {
    if ('<?= ($getC->city_excompany) ?>' == '') {
      var id_selected = '';
    } else {
      var id_selected = '<?= $getC->city_excompany ?>';
    }
    //console.log(id_selected);
    var column = 'id_prov';
    var column_fill = id_prov;
    var column_name = 'nama';
    var table_name = 'kabupaten';
    var key = 'id_kab';
    var act = 'free';
    $.ajax({
      url: siteurl + active_controller + "getOpt",
      dataType: "json",
      type: 'POST',
      data: {
        id_selected: id_selected,
        column: column,
        column_fill: column_fill,
        column_name: column_name,
        table_name: table_name,
        key: key,
        act: act
      },
      success: function(result) {
        $('#id_city_excompany').html(result.html);
      },
      error: function(request, error) {
        console.log(arguments);
        alert(" Can't do because: " + error);
      }
    });
  }

  /*
  function getSupplierTypeX() {
    var id_type = $('#id_type').val();
    var supplier_shipping = $('#supplier_shipping').val();
    $.ajax({
      url: siteurl+active_controller+"getSupplierTypeOpt",
      dataType : "json",
      type: 'POST',
      data: {id_type:id_type},
      success: function(result){
        $('#id_type').html(result.html);
      },
      error: function (request, error) {
        console.log(arguments);
        alert(" Can't do because: " + error);
      }
    });
  }
  function getCountryX() {
    var id_country = $('#id_country').val();
    $.ajax({
      url: siteurl+active_controller+"getCountryOpt",
      dataType : "json",
      type: 'POST',
      data: {id_country:id_country},
      success: function(result){
        $('#id_country').html(result.html);
      },
      error: function (request, error) {
        console.log(arguments);
        alert(" Can't do because: " + error);
      }
    });
  }
  function getBusinessCatX(id_business=null) {
    var id_type = $('#id_type').val();
    //var supplier_shipping = $('#supplier_shipping').val();
    $.ajax({
      url: siteurl+active_controller+"getBusinessCatOpt",
      dataType : "json",
      type: 'POST',
      data: {id_type:id_type,id_business:id_business},
      success: function(result){
        $('#id_business').html(result.html);
      },
      error: function (request, error) {
        console.log(arguments);
        alert(" Can't do because: " + error);
      }
    });
  }
  function getSupplierCapX(id_capacity=null) {
    var id_business = $('#id_business').val();
    $.ajax({
      url: siteurl+active_controller+"getSupplierCapOpt",
      dataType : "json",
      type: 'POST',
      data: {id_capacity:id_capacity,id_business:id_business},
      success: function(result){
        $('#id_capacity').html(result.html);
      },
      error: function (request, error) {
        console.log(arguments);
        alert(" Can't do because: " + error);
      }
    });
  }
  function getRefreshBrand() {
    var id_brand = $('#id_brand').val();
    if ('<?= ($getC->id_brand) ?>' != null) {
      var id_brand = '<?= $getC->id_brand ?>';
    }else if ($('#id_brand').val() != null || $('#id_brand').val() != '') {
      var id_brand = $('#id_brand').val();
    }else {
      var id_brand = '';
    }
    console.log(id_brand);
    $.ajax({
      url: siteurl+active_controller+"getRefreshBrand",
      dataType : "json",
      type: 'POST',
      data: {id: '<?= $id ?>',idb:id_brand},
      success: function(result){
        $('#tableselBrand_tbody').empty();
        $('#tableselBrand_tbody').append(result.html);
      },
      error: function (request, error) {
        console.log(arguments);
        alert(" Can't do because: " + error);
      }
    });
  }
  function getRefreshProCat() {
    //var id_category = $('#id_category').val();
    var val = $("input[name='supplier_shipping']:checked").val();
    $.ajax({
      url: siteurl+active_controller+"getProductCatOpt",
      dataType : "json",
      type: 'POST',
      data: {id_category:'',supplier_shipping:val},
      success: function(result){
        $('#id_category').html(result.html);
      },
      error: function (request, error) {
        console.log(arguments);
        alert(" Can't do because: " + error);
      }
    });
    //getSupplierType();
    //getBusinessCat('<?= empty($getC->id_business) ? "" : $getC->id_business ?>');
    //getSupplierCap('<?= empty($getC->id_capacity) ? "" : $getC->id_capacity ?>');
  }
  */
</script>