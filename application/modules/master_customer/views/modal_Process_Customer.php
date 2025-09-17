<?php

if (!empty($this->uri->segment(3))) {
  $getC             = $this->db->get_where('master_customer', array('id_customer' => $id))->row();
  $getCP            = $this->db->get_where('child_customer_pic', array('id_customer' => $id))->result();
  $getCB            = $this->db->get_where('child_customer_branch', array('id_customer' => $id))->result();

  $id_prov = (!empty($getC->id_prov))?$getC->id_prov:'';
  $id_country = (!empty($getC->id_country))?$getC->id_country:'';
  $id_category_customer = (!empty($getC->id_category_customer))?$getC->id_category_customer:'';
  $id_karyawan = (!empty($getC->id_karyawan))?$getC->id_karyawan:'';
  $id_reference = (!empty($getC->id_reference))?$getC->id_reference:'';

  $name_prov        = $this->db->get_where('provinsi', array('id_prov' => $id_prov))->row();
  $name_country     = $this->db->get_where('negara', array('id' => $id_country))->row();
  $name_category    = $this->db->get_where('child_customer_category', array('id_category_customer' => $id_category_customer))->row();
  $name_sales       = $this->db->get_where('ms_karyawan', array('id_karyawan' => $id_karyawan))->row();
  $religion         = $this->db->get_where('child_customer_pic', array('id_customer' => $id))->row();
  $name_reference   = $this->db->get_where('child_customer_reference', array('id_reference' => $id_reference))->row();
}
$getI   = $this->db->query("SELECT MAX(id_customer) AS max_id FROM master_customer ORDER BY id_customer DESC LIMIT 1")->row();
//echo "$first_letter";
//exit;
$num = substr($getI->max_id, 1, 5) + 1;
$id_customer = 'C' . str_pad($num, 5, "0", STR_PAD_LEFT);


?>
<form class="" id="form-supplier" action="" method="post">
  <div class="box box-success">
    <div class="box-body" style="">
      <div class="row">
        <div class="col-md-12">
          <table id="my-grid3" class="table-condensed" width="100%">
            <thead>
              <tr style='background-color: #175477 !important; color: white; font-size: 15px !important;'>
                <th class="text-center" colspan='3'>DETAIL CUSTOMER IDENTITY</th>
              </tr>
            </thead>
          </table>
          <div class="col-md-6">
            <section id="DETAIL_CUSTOMER_IDENTITY">
              <table id="my-grid3" class="table-condensed" width="100%">
                <tbody>
                  <tr id="my-grid-tr-id_customer">
                    <td class="text-left vMid" width="30%">Code <span class='text-red'>*</span></b></td>
                    <td class="text-left">
                      <label class="label_input">
                        <input type="hidden" name="type" value="<?= empty($getC->id_customer) ? 'add' : 'edit' ?>">
                        <input type="text" class="form-control input input-sm required w30 " name="id_customer" id="id_customer" value="<?= empty($getC->id_customer) ? '' : $getC->id_customer ?>" readonly>
                        <label class="label label-danger id_customer hideIt">Code Can't be empty!</label>
                      </label>
                    </td>
                  </tr>

                  <tr id="my-grid-tr-name_customer">
                    <td class="text-left vMid">Customer Name <span class='text-red'>*</span></b></td>
                    <td class="text-left">
                      <label class="label_input">
                        <?php
                        echo form_input(array('type' => 'text', 'id' => 'name_customer', 'name' => 'name_customer', 'class' => 'form-control w80 required', 'placeholder' => 'Customer Name', 'autocomplete' => 'off', 'value' => empty($getC->name_customer) ? '' : $getC->name_customer))
                        ?>
                        <!--<a id="generate_id" class="btn btn-sm btn-primary" style="display:inline-block">Generate ID</a>-->
                        <label class="label label-danger name_customer hideIt">Customer Name Can't be empty!</label>
                      </label>
                    </td>
                  </tr>

                  <tr id="my-grid-tr-telephone">
                    <td class="text-left vMid">Telephone 1 <span class='text-red'>*</span></b></td>
                    <td class="text-left">
                      <label class="label_input">
                        <?php
                        $tlp = '';
                        if ($getC->telephone) {
                          $tlp = explode("-", $getC->telephone);
                        }
                        echo form_input(array('type' => 'text', 'id' => 'telephone', 'name' => 'telephone[]', 'class' => 'form-control required w15', 'placeholder' => 'Code', 'autocomplete' => 'off', 'maxlength' => '4', 'value' => empty($tlp[0]) ? '' : $tlp[0]));
                        echo " - ";
                        echo form_input(array('type' => 'text', 'id' => 'telephone', 'name' => 'telephone[]', 'class' => 'form-control required numberOnly w40', 'placeholder' => 'Number', 'autocomplete' => 'off', 'maxlength' => '9', 'value' => empty($tlp[1]) ? '' : $tlp[1]));
                        ?>
                        <label class="label label-danger telephone hideIt">Telephone 1 Can't be empty!</label>
                      </label>
                    </td>
                  </tr>

                  <tr id="my-grid-tr-telephone_2">
                    <td class="text-left vMid"></td>
                    <td class="text-left">
                      <label class="label_view">
                        <?= $getC->telephone_2 ?>
                      </label>
                      <label class="label_input">
                        <?php
                        $tlp = '';
                        if ($getC->telephone_2) {
                          $tlp = explode("-", $getC->telephone_2);
                        }
                        echo form_input(array('type' => 'text', 'id' => 'telephone_2_1', 'name' => 'telephone_2[]', 'class' => 'form-control w15', 'placeholder' => 'Code', 'autocomplete' => 'off', 'maxlength' => '4', 'value' => empty($tlp[0]) ? '' : $tlp[0]));
                        echo " - ";
                        echo form_input(array('type' => 'text', 'id' => 'telephone_2_2', 'name' => 'telephone_2[]', 'class' => 'form-control numberOnly w40', 'placeholder' => 'Number', 'autocomplete' => 'off', 'maxlength' => '9', 'value' => empty($tlp[1]) ? '' : $tlp[1]));
                        ?>
                      </label>
                    </td>
                  </tr>

                  <tr id="my-grid-tr-fax">
                    <td class="text-left vMid">Fax (Office)</td>
                    <td class="text-left">
                      <label class="label_view">
                        <?= $getC->fax ?>
                      </label>
                      <label class="label_input">
                        <?php
                        echo form_input(array('type' => 'text', 'id' => 'fax', 'name' => 'fax', 'class' => 'form-control numberOnly w60', 'placeholder' => 'Fax', 'autocomplete' => 'off', 'value' => empty($getC->fax) ? '' : $getC->fax))
                        ?>
                      </label>
                    </td>
                  </tr>

                  <tr id="my-grid-tr-email">
                    <td class="text-left vMid">E-Mail <span class='text-red'>*</span></td>
                    <td class="text-left">
                      <label class="label_input">
                        <input type="email" id="email" name="email" class="required form-control w60" placeholder="Company E-Mail" autocomplete="off" value="<?= empty($getC->email) ? '' : $getC->email ?>">
                        <label class="label label-danger email hideIt">E-Mail Can't be empty!</label>
                      </label>
                    </td>
                  </tr>

                  <tr id="my-grid-tr-activation">
                    <td class="text-left vMid">Status <span class='text-red'>*</span></td>
                    <td class="text-left">
                      <label class="label_input">
                        <select class="form-control required select2" name="activation" id="activation" style="width:40%">
                          <option value="active">Active</option>
                          <option value="inactive">Inactive</option>
                        </select>
                        <label class="label label-danger activation hideIt">Status Can't be empty!</label>
                      </label>
                    </td>
                  </tr>

                </tbody>
              </table>
            </section>
          </div>

          <div class="col-md-6">
            <table class="table-condensed" width="100%">
              <tbody>

                <tr id="my-grid-tr-id_country">
                  <td class="text-left vMid" width="30%">Country <span class='text-red'>*</span></td>
                  <td class="text-left">
                    <label class="label_view">
                      <?= $name_country->nm_negara ?>
                    </label>
                    <label class="label_input">
                      <select class="form-control input-sm required select2 w50" name="id_country" id="id_country">
                      </select>
                      <label class="label label-danger id_country hideIt">Country Can't be empty!</label>
                    </label>
                  </td>
                </tr>

                <tr id="my-grid-tr-id_prov">
                  <td class="text-left vMid">Province <span class='text-red'>*</span></td>
                  <td class="text-left">
                    <label class="label_view">
                      <?= $name_prov->nama ?>
                    </label>
                    <label class="label_input">
                      <select class="form-control input-sm required select2 w50" name="id_prov" id="id_prov">

                      </select>
                      <label class="label label-danger id_prov hideIt">Province Can't be empty!</label>
                    </label>
                  </td>
                </tr>

                <tr id="my-grid-tr-city">
                  <td class="text-left vMid">City <span class='text-red'>*</span></b></td>
                  <td class="text-left">
                    <label class="label_view">
                      <?= $getC->city ?>
                    </label>
                    <label class="label_input">
                      <select class="form-control input-sm required select2 w50" name="city" id="city">
                      </select>
                      <label class="label label-danger city hideIt">City Can't be empty!</label>
                    </label>
                  </td>
                </tr>

                <tr id="my-grid-tr-address_office">
                  <td class="text-left vMid">Address (Office) <span class='text-red'>*</span></b></td>
                  <td class="text-left">
                    <label class="label_view">
                      <?= $getC->address_office ?>
                    </label>
                    <label class="label_input">
                      <textarea type="text" name="address_office" id="address_office" class="form-control input-sm required w70" placeholder="Address Office"><?= empty($getC->address_office) ? '' : $getC->address_office ?></textarea>
                      <label class="label label-danger address_office hideIt">Address (Office) Can't be empty!</label>
                    </label>
                  </td>
                </tr>

                <tr id="my-grid-tr-zip_code">
                  <td class="text-left vMid">ZIP Code <span class='text-red'>*</span></td>
                  <td class="text-left">
                    <label class="label_view">
                      <?= $getC->zip_code ?>
                    </label>
                    <label class="label_input">
                      <input type="text" class="form-control required w50" id="zip_code" name="zip_code" placeholder="ZIP Code" value="<?= empty($getC->zip_code) ? '' : $getC->zip_code ?>">
                      <label class="label label-danger zip_code hideIt">ZIP Code Can't be empty!</label>
                    </label>
                  </td>
                </tr>

                <tr id="my-grid-tr-longitude">
                  <td class="text-left vMid">Longitude</td>
                  <td class="text-left">
                    <label class="label_view">
                      <?= $getC->longitude ?>
                    </label>
                    <label class="label_input">
                      <?php
                      echo form_input(array('type' => 'text', 'id' => 'longitude', 'name' => 'longitude', 'class' => 'form-control input-sm w50', 'placeholder' => 'Longitude', 'autocomplete' => 'off', 'value' => empty($getC->longitude) ? '' : $getC->longitude))
                      ?>
                      <label class="label label-danger longitude hideIt">Longitude Can't be empty!</label>
                    </label>
                  </td>
                </tr>

                <tr id="my-grid-tr-latitude">
                  <td class="text-left vMid">Latitude</td>
                  <td class="text-left">
                    <label class="label_view">
                      <?= $getC->latitude ?>
                    </label>
                    <label class="label_input">
                      <?php
                      echo form_input(array('type' => 'text', 'id' => 'latitude', 'name' => 'latitude', 'class' => 'form-control input-sm w50', 'placeholder' => 'Latitude', 'autocomplete' => 'off', 'value' => empty($getC->latitude) ? '' : $getC->latitude))
                      ?>
                      <label class="label label-danger latitude hideIt">Latitude Can't be empty!</label>
                    </label>
                  </td>
                </tr>

              </tbody>
            </table>
            <br>
          </div>
        </div>

        <div class="col-md-12">
          <table class="table-condensed" width="100%">
            <thead>
              <tr style='background-color: #175477; color: white; font-size: 15px;'>
                <th class="text-center" colspan='3'>DETAIL PRODUCTIVITY</th>
              </tr>
            </thead>
          </table>
          <div class="col-md-6">
            <section id="DETAIL_PRODUCTIVITY">
              <table id="my-grid2" class="table-condensed" width="100%">
                <tbody>

                  <tr id="my-grid-tr-id_category_customer">
                    <td class="text-left vMid" width="30%">Category <span class='text-red'>*</span></b></td>
                    <td class="text-left">
                      <label class="label_view">
                        <?= $name_category->name_category_customer ?>
                      </label>
                      <label class="label_input">
                        <select class="form-control required select2 w50" name="id_category_customer" id="id_category_customer">
                        </select>
                        <label class="label label-danger id_category_customer hideIt">Category Can't be empty!</label>
                      </label>
                    </td>
                  </tr>

                  <tr id="my-grid-tr-start_date">
                    <td class="text-left vMid">Customer Start Date <span class='text-red'>*</span></b></td>
                    <td class="text-left">
                      <?php
                      echo form_input(array('type' => 'text', 'id' => 'start_date', 'name' => 'start_date', 'class' => 'form-control required datepicker w30', 'placeholder' => 'Customer Start Date', 'autocomplete' => 'off', 'value' => empty($getC->start_date) ? date('Y-m-d') : $getC->start_date))
                      ?>
                      <label class="label label-danger start_date hideIt">Customer Start Date Can't be empty!</label>
                      <label class="label_input">
                      </label>
                    </td>
                  </tr>

                  <tr id="my-grid-tr-id_karyawan">
                    <td class="text-left vMid">Marketing <span class='text-red'>*</span></b></td>
                    <td class="text-left">
                      <label class="label_input">
                        <select class="form-control input-sm required select2 w50" name="id_karyawan" id="id_karyawan"></select>
                        <label class="label label-danger id_karyawan hideIt">Marketing Can't be empty!</label>
                      </label>
                    </td>
                  </tr>

                  <!-- <tr id="my-grid-tr-id_reference">
                    <td class="text-left vMid">Reference <span class='text-red'>*</span></b></td>
                    <td class="text-left">
                      <label class="label_view">
                        <?php //$name_reference->name_reference
                        ?>
                      </label>
                      <label class="label_input">
                        <select class="form-control input-sm required select2 w50" name="id_reference" id="id_reference">

                        </select>
                        <label class="label label-danger required id_reference hideIt">Reference Can't be empty!</label>
                      </label>
                    </td>
                  </tr> -->

                </tbody>
              </table>
            </section>
          </div>
          <div class="col-md-6">
            <table class="table-condensed" width="100%">
              <tr id="my-grid-tr-credit_limit">
                <td class="text-left vMid" width="30%">Credit limit</td>
                <td class="text-left">
                  <label class="label_input w70">
                    <div class="input-group ">
                      <span class="input-group-addon" id="basic-addon1">Rp</span>
                      <?php
                      echo form_input(array('type' => 'text', 'id' => 'credit_limit', 'name' => 'credit_limit', 'class' => 'form-control numberOnly nominal', 'placeholder' => 'Credit limit', 'autocomplete' => 'off', 'value' => empty($getC->credit_limit) ? '' : $getC->credit_limit))
                      ?>
                      <span class="input-group-addon" id="basic-addon2">.00</span>
                    </div>
                    <label class="label label-danger credit_limit hideIt">Credit limit Can't be empty!</label>
                  </label>
                </td>
              </tr>

              <tr id="my-grid-tr-remarks">
                <td class="text-left vMid">Remarks</td>
                <td class="text-left">
                  <label class="label_input">
                    <textarea name="remarks" id="remarks" class="form-control w80" placeholder="Remarks"><?= empty($getC->remarks) ? '' : $getC->remarks ?></textarea>
                    <label class="label label-danger remarks hideIt">Remarks Can't be empty!</label>
                  </label>
                </td>
              </tr>
            </table>
          </div>
        </div>

        <div class="col-md-12">
          <table class="table-condensed" width="100%">
            <thead>
              <tr style='background-color: #175477; color: white; font-size: 15px;'>
                <th class="text-center" colspan='3'>Invoice Bill Information</th>
              </tr>
            </thead>
          </table>
          <div class="col-md-6">
            <table id="my-grid2" class="table-condensed" width="100%">
              <tbody>
                <tr id="my-grid-tr-npwp">
                  <td class="text-left vMid" width="30%">NPWP/PKP Number <span class="text-red">*</span></td>
                  <td class="text-left">
                    <label class="label_input">
                      <?php
                      echo form_input(array('type' => 'text', 'id' => 'npwp', 'name' => 'npwp', 'class' => 'numberOnly form-control w70 required', 'placeholder' => 'NPWP/PKP Number', 'autocomplete' => 'off', 'value' => empty($getC->npwp) ? '' : $getC->npwp))
                      ?>
                    </label>
                    <label class="label label-danger npwp hideIt">NPWP Can't be empty!</label>
                  </td>
                </tr>

                <tr id="my-grid-tr-vat_name">
                  <td class="text-left vMid">NPWP Name <span class="text-red">*</span></td>
                  <td class="text-left">
                    <label class="label_input">
                      <?php
                      echo form_input(array('type' => 'text', 'id' => 'vat_name', 'name' => 'vat_name', 'class' => 'form-control w70 required', 'placeholder' => 'NPWP Name', 'autocomplete' => 'off', 'value' => empty($getC->vat_name) ? '' : $getC->vat_name))
                      ?>
                      <label class="label label-danger vat_name hideIt">NPWP Name Can't be empty!</label>
                    </label>
                  </td>
                </tr>

                <tr id="my-grid-tr-npwp_address">
                  <td class="text-left vMid">NPWP/PKP Address <span class="text-red">*</span></td>
                  <td class="text-left">
                    <label class="label_input">
                      <?php
                      echo form_input(array('type' => 'text', 'id' => 'npwp_address', 'name' => 'npwp_address', 'class' => 'form-control w70 required', 'placeholder' => 'NPWP/PKP Address', 'autocomplete' => 'off', 'value' => empty($getC->npwp_address) ? '' : $getC->npwp_address))
                      ?>
                      <label class="label label-danger npwp_address hideIt">NPWP Address Can't be empty!</label>
                    </label>
                  </td>
                </tr>

                <tr id="my-grid-tr-pic_finance">
                  <td class="text-left vMid">PIC Finance <span class="text-red">*</span></td>
                  <td class="text-left">
                    <label class="label_input">
                      <?php
                      echo form_input(array('type' => 'text', 'id' => 'pic_finance', 'name' => 'pic_finance', 'class' => 'form-control w70 required', 'placeholder' => 'PIC Finance', 'autocomplete' => 'off', 'value' => empty($getC->pic_finance) ? '' : $getC->pic_finance))
                      ?>
                      <label class="label label-danger pic_finance hideIt">PIC Finance Can't be empty!</label>
                    </label>
                  </td>
                </tr>

                <tr id="my-grid-tr-day_invoice_receive">
                  <td class="text-left vMid">Day invoice receive <span class="text-red">*</span></td>
                  <td class="text-left">
                    <label class="label_view">
                      <?= implode("<br>", explode(";", $getC->day_invoice_receive)) ?>
                    </label>
                    <div class="input-group ">
                      <span class="input-group-addon" id="basic-addon2">
                        <input type="checkbox" name="sel_all_day_invoice_receive" value="all" class="checkbox sel_all" style="display:inline-block" id="sel_all_day_invoice_receive">
                      </span>
                      <select class="form-control select2 days required" name="day_invoice_receive[]" id="day_invoice_receive" multiple="multiple">
                      </select>
                      <!-- <label for="sel_all_day_invoice_receive" style="display:inline-block">Check all day!</label> -->
                    </div>
                    <label class="label label-danger day_invoice_receive hideIt">Day invoice receive Can't be empty!</label>
                    <label class="label_input w90">
                    </label>
                  </td>
                </tr>

                <div class="form-group">
                  <tr id="my-grid-tr-receipt_time">
                    <td class="text-left vMid">Receipt Time <span class='text-red'>*</span></b></td>
                    <td class="text-left">
                      <label class="label_input w70">
                        <div class="input-group">
                          <span class="input-group-addon" id="basic-addon1">start</span>
                          <input type="time" name="receipt_time_1" id="receipt_time_1" class="w30 form-control required" value="<?= empty($getC->receipt_time_1) ? '' : $getC->receipt_time_1; ?>">
                          <span class="input-group-addon" id="basic-addon1">end</span>
                          <input type="time" name="receipt_time_2" id="receipt_time_2" class="w30 form-control required" value="<?= empty($getC->receipt_time_2) ? '' : $getC->receipt_time_2; ?>">
                        </div>
                      </label>
                      <label class="label label-danger receipt_time_1 receipt_time_2 hideIt">Receipt Time Can't be empty!</label>
                    </td>
                  </tr>

                  <tr id="my-grid-tr-invoice_address">
                    <td class="text-left vMid">Invoice Address <span class='text-red'>*</span></b></td>
                    <td class="text-left">
                      <label class="label_input">
                        <textarea name="invoice_address" id="invoice_address" class="form-control required w90" placeholder="Invoice Address"><?= empty($getC->invoice_address) ? '' : $getC->invoice_address ?></textarea>
                        <label class="label label-danger invoice_address hideIt">Invoice Address Can't be empty!</label>
                      </label>
                    </td>
                  </tr>

                  <tr id="my-grid-tr-va_number">
                    <td class="text-left vMid">Virtual Acc. Number</td>
                    <td class="text-left">
                      <label class="label_input">
                        <?php
                        echo form_input(array('type' => 'text', 'id' => 'va_number', 'name' => 'va_number', 'class' => 'form-control w70', 'placeholder' => 'Virtual Acc. Number', 'autocomplete' => 'off', 'value' => empty($getC->va_number) ? '' : $getC->va_number))
                        ?>
                        <label class="label label-danger va_numberX hideIt">Virtual Acc. Number Can't be empty!</label>
                      </label>
                    </td>
                  </tr>
              </tbody>
            </table>
          </div>

          <div class="col-md-6">
            <table id="my-grid2" class="table-condensed" width="100%">
              <tbody>

                <!-- <tr id="my-grid-tr-payment_category">
                  <td class="text-left vMid">Payment Term.</td>
                  <td class="text-left">
                    <label class="label_view">
                      <?= $getC->payment_category ?>
                    </label>
                    <label class="label_input">
                      <select class="form-control input-sm select2 w50" name="payment_category" id="payment_category">
                        <option value="">None</option>
                        <option value="Cash in Advance">Cash in Advance</option>
                        <option value="DP">DP</option>
                        <option value="Temporary(After Delivery)">Temporary(After Delivery)</option>
                        <option value="2 week ">2 week </option>
                        <option value="3 week">3 week</option>
                        <option value="1 Month">1 Month</option>
                        <option value="2 Month">2 Month</option>
                        <option value="3 Month">3 Month</option>
                      </select>
                      <label class="label label-danger payment_categoryX hideIt">Payment Category Can't be empty!</label>
                    </label>
                  </td>
                </tr> -->

                <!-- <tr id="my-grid-tr-payment_day">
                  <td class="text-left vMid">Payment Day</td>
                  <td class="text-left">
                    <label class="label_view">
                      <?php // implode("<br>", explode(";", $getC->payment_day))
                      ?>
                    </label>
                    <label class="label_input">
                      <select class="form-control input-sm select2 days w50" name="payment_day[]" id="payment_day" multiple="multiple">
                      </select>
                      <label class="label label-danger payment_dayX hideIt">Payment Day Can't be empty!</label>

                      <input type="checkbox" name="sel_all_payment_day" value="all" class="checkbox sel_all" style="display:inline-block" id="sel_all_payment_day"> <label for="sel_all_payment_day" style="display:inline-block">Check all day!</label>
                    </label>
                  </td>
                </tr> -->

                <tr id="my-grid-tr-payment_requirements">
                  <td class="text-left vMid">Payment requirements</td>
                  <td class="text-left">
                    <label class="label_view">
                      <?= implode("<br>", explode(";", $getC->payment_requirements)) ?>
                    </label>
                    <label class="label_input">
                      <?php
                      if ($getC) {
                        if (in_array('ba', explode(';', $getC->payment_req))) {
                          $checked_pr = 'checked';
                        } else {
                          $checked_pr = '';
                        }
                      }
                      echo form_input(array('type' => 'checkbox', 'id' => 'ba', 'name' => 'payment_req[]', 'class' => 'checkbox inline-block checkbox-label', 'placeholder' => 'BA', 'autocomplete' => 'off', 'value' => 'ba', $checked_pr => $checked_pr))
                      ?>
                      <label for="ba" class="checkbox-label">
                        BA
                      </label>
                      <label class="label label-danger ba hideIt">BA Can't be empty!</label>
                      <br>

                      <?php
                      if ($getC) {
                        if (in_array('real_po', explode(';', $getC->payment_req))) {
                          $checked_pr = 'checked';
                        } else {
                          $checked_pr = '';
                        }
                      }
                      echo form_input(array('type' => 'checkbox', 'id' => 'real_po', 'name' => 'payment_req[]', 'class' => 'checkbox inline-block checkbox-label', 'placeholder' => 'Real PO', 'autocomplete' => 'off', 'value' => 'real_po', $checked_pr => $checked_pr))
                      ?>
                      <label for="real_po" class="checkbox-label">
                        Real PO
                      </label>
                      <label class="label label-danger real_po hideIt">Real PO Can't be empty!</label>
                      <br>

                      <?php
                      if ($getC) {
                        if (in_array('photo', explode(';', $getC->payment_req))) {
                          $checked_pr = 'checked';
                        } else {
                          $checked_pr = '';
                        }
                      }
                      echo form_input(array('type' => 'checkbox', 'id' => 'photo', 'name' => 'payment_req[]', 'class' => 'checkbox inline-block checkbox-label', 'placeholder' => 'Photo', 'autocomplete' => 'off', 'value' => 'photo', $checked_pr => $checked_pr))
                      ?>
                      <label for="photo" class="checkbox-label">
                        Photos
                      </label>
                      <label class="label label-danger photo hideIt">Photo Can't be empty!</label>
                      <br>

                      <?php
                      if ($getC) {
                        if (in_array('do', explode(';', $getC->payment_req))) {
                          $checked_pr = 'checked';
                        } else {
                          $checked_pr = '';
                        }
                      }
                      echo form_input(array('type' => 'checkbox', 'id' => 'do', 'name' => 'payment_req[]', 'class' => 'checkbox inline-block checkbox-label', 'placeholder' => 'Photo', 'autocomplete' => 'off', 'value' => 'do', $checked_pr => $checked_pr))
                      ?>
                      <label for="do" class="checkbox-label">
                        DO
                      </label>
                      <label class="label label-danger do hideIt">DO Can't be empty!</label>
                      <br>

                      <?php
                      if ($getC) {
                        if (in_array('tax_invoice', explode(';', $getC->payment_req))) {
                          $checked_pr = 'checked';
                        } else {
                          $checked_pr = '';
                        }
                      }
                      echo form_input(array('type' => 'checkbox', 'id' => 'tax_invoice', 'name' => 'payment_req[]', 'class' => 'checkbox inline-block checkbox-label', 'placeholder' => 'Photo', 'autocomplete' => 'off', 'value' => 'tax_invoice', $checked_pr => $checked_pr))
                      ?>
                      <label for="tax_invoice" class="checkbox-label">
                        Tax Invoice(Faktur)
                      </label>
                      <label class="label label-danger tax_invoice hideIt">Tax Invoice(Faktur) Can't be empty!</label>
                      <br>

                      <?php
                      if ($getC) {
                        if (in_array('ttd_specimen', explode(';', $getC->payment_req))) {
                          $checked_pr = 'checked';
                        } else {
                          $checked_pr = '';
                        }
                      }
                      echo form_input(array('type' => 'checkbox', 'id' => 'ttd_specimen', 'name' => 'payment_req[]', 'class' => 'checkbox inline-block checkbox-label', 'placeholder' => 'Photo', 'autocomplete' => 'off', 'value' => 'ttd_specimen', $checked_pr => $checked_pr))
                      ?>
                      <label for="ttd_specimen" class="checkbox-label">
                        TTD Specimen / Tax Invoice Serial Number
                      </label>
                      <label class="label label-danger ttd_specimen hideIt">TTD Specimen / Tax Invoice Serial Number Can't be empty!</label>
                      <br>

                      <?php
                      if ($getC) {
                        if (in_array('siup', explode(';', $getC->payment_req))) {
                          $checked_pr = 'checked';
                        } else {
                          $checked_pr = '';
                        }
                      }
                      echo form_input(array('type' => 'checkbox', 'id' => 'siup', 'name' => 'payment_req[]', 'class' => 'checkbox inline-block checkbox-label', 'placeholder' => 'Photo', 'autocomplete' => 'off', 'value' => 'siup', $checked_pr => $checked_pr))
                      ?>
                      <label for="siup" class="checkbox-label">
                        SIUP
                      </label>
                      <label class="label label-danger siup hideIt">SIUP Can't be empty!</label>
                      <br>

                      <?php
                      if ($getC) {
                        if (in_array('npwp_acc', explode(';', $getC->payment_req))) {
                          $checked_pr = 'checked';
                        } else {
                          $checked_pr = '';
                        }
                      }
                      echo form_input(array('type' => 'checkbox', 'id' => 'npwp_acc', 'name' => 'payment_req[]', 'class' => 'checkbox inline-block checkbox-label', 'placeholder' => 'Photo', 'autocomplete' => 'off', 'value' => 'npwp_acc', $checked_pr => $checked_pr))
                      ?>
                      <label for="npwp_acc" class="checkbox-label">
                        NPWP
                      </label>
                      <label class="label label-danger npwp_acc hideIt">NPWP Can't be empty!</label>
                      <br>

                      <?php
                      if ($getC) {
                        if (in_array('tdp', explode(';', $getC->payment_req))) {
                          $checked_pr = 'checked';
                        } else {
                          $checked_pr = '';
                        }
                      }
                      echo form_input(array('type' => 'checkbox', 'id' => 'tdp', 'name' => 'payment_req[]', 'class' => 'checkbox inline-block checkbox-label', 'placeholder' => 'Photo', 'autocomplete' => 'off', 'value' => 'tdp', $checked_pr => $checked_pr))
                      ?>
                      <label for="tdp" class="checkbox-label">
                        TDP
                      </label>
                      <label class="label label-danger tdp hideIt">TDP Can't be empty!</label>
                      <br>

                      <?php
                      if ($getC) {
                        if (in_array('payment_certificate', explode(';', $getC->payment_req))) {
                          $checked_pr = 'checked';
                        } else {
                          $checked_pr = '';
                        }
                      }
                      echo form_input(array('type' => 'checkbox', 'id' => 'payment_certificate', 'name' => 'payment_req[]', 'class' => 'checkbox inline-block checkbox-label', 'placeholder' => 'Photo', 'autocomplete' => 'off', 'value' => 'payment_certificate', $checked_pr => $checked_pr))
                      ?>
                      <label for="payment_certificate" class="checkbox-label">
                        Payment Certificate
                      </label>
                      <label class="label label-danger payment_certificate hideIt">Payment Certificate Can't be empty!</label>
                      <br>

                      <?php
                      if ($getC) {
                        if (in_array('spk', explode(';', $getC->payment_req))) {
                          $checked_pr = 'checked';
                        } else {
                          $checked_pr = '';
                        }
                      }
                      echo form_input(array('type' => 'checkbox', 'id' => 'spk', 'name' => 'payment_req[]', 'class' => 'checkbox inline-block checkbox-label', 'placeholder' => 'Photo', 'autocomplete' => 'off', 'value' => 'spk', $checked_pr => $checked_pr))
                      ?>
                      <label for="spk" class="checkbox-label">
                        SPK(Work order letter)
                      </label>
                      <label class="label label-danger spk hideIt">SPK(Work order letter) Can't be empty!</label>
                      <br>


                    </label>
                  </td>
                </tr>

              </tbody>
            </table>
          </div>

        </div>

        <!-- PIC -->
        <!-- ============================================== -->
        <div class="col-md-12">
          <section id="PIC">
            <table class="table table-striped table-bordered table-hover table-condensed" width="100%">
              <tr style='background-color: #175477; color: white; font-size: 15px;'>
                <th class="text-center" colspan='6'>PIC <span class='text-red'>*</span></b></th>
              </tr>
              <tfoot id="tfoot-pic">
                <?php $no = 0;
                foreach ($getCP as $key => $vp) : $no++; ?>
                  <tr>
                    <td>
                      <div class="input-group">
                        <span class="input-group-btn">
                          <button class="btn btn-danger hapus_item_js" title="Delete List" type="button"><i class="fa fa-times"></i><span class="numbering-pic"></span></button>
                        </span>
                        <input type="text" name="pic[]" class="form-control" placeholder="PIC Name" value="<?= $vp->name_pic ?>">
                      </div>
                    </td>
                    <td>
                      <div class="label_input">
                        <input type="text" name="pic_phone[]" class="form-control" placeholder="Phone Number" value="<?= $vp->phone_pic ?>">
                      </div>
                    </td>
                    <td>
                      <div class="label_input">
                        <input type="text" name="pic_email[]" class="form-control" placeholder="Email" value="<?= $vp->email_pic ?>">
                      </div>
                    </td>
                    <td>
                      <div class="label_input">
                        <input type="text" name="pic_position[]" class="form-control" placeholder="Position" value="<?= $vp->position_pic ?>">
                      </div>
                    </td>
                    <td width="20%">
                      <div class="label_input">
                        <select name="pic_religion[]" class="form-control select2-100" data-id="<?= $no ?>" id="religion_<?= $no ?>" placeholder="Religion"></select>
                        <input type="hidden" id="religion_h_<?= $no ?>" value="<?= $vp->religion_pic ?>">
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tfoot>
            </table>
            <a class="btn btn-sm btn-success" id="addPIC">Add PIC</a>
          </section>

          <section id="BRANCH_DATA">
            <br>
            <table class="table-condensed" width="100%">
              <thead>
                <tr>
                  <th class="text-center">Branch <span class='text-red'>*</span></b></th>
                </tr>
              </thead>
            </table>
            <div id="branchList">
              <?php if ($getCB) : ?>
                <?php $no = 0;
                foreach ($getCB as $key => $vb) : $no++; ?>
                  <div class="branch">
                    <div class="col-sm-12" style="padding:2%">
                      <legend class="legend"> <a class="text-red hapus_item_js" href="javascript:void(0)" title="Delete List"><i class="fa fa-times"></i></a> Branch No. <span class="numbering-branch"><?= $no ?></span></legend>
                      <div class="row form-horizontal">

                        <div class="col-md-4">

                          <div class="form-group">
                            <label for="" class="col-sm-3 control-label"> Name Branch </label>
                            <div class="col-sm-8">
                              <div class="label_input">
                                <input type="text" name="name_branch[]" class="form-control" placeholder="Name Branch" value="<?= $vb->name_branch ?>">
                              </div>
                              <div class="label_view">
                                <?= $vb->name_branch ?>
                              </div>
                            </div>
                          </div>

                          <div class="form-group">
                            <label for="" class="col-sm-3 control-label">PIC</label>
                            <div class="col-md-8">
                              <input type="text" name="pic_branch[]" class="form-control" placeholder="PIC Branch" value="<?= $vb->pic_branch ?>">
                            </div>
                          </div>
                          <div class="form-group">
                            <label for="" class="col-sm-3 control-label">Telephone</label>
                            <div class="col-md-8">
                              <input type="text" name="telephone_1_branch[]" class="form-control" placeholder="Telephone" value="<?= $vb->telephone_1_branch ?>">
                            </div>
                          </div>
                          <div class="form-group">
                            <label for="" class="col-sm-3 control-label"></label>
                            <div class="col-md-8">
                              <input type="text" name="telephone_2_branch[]" class="form-control" placeholder="Other Telephone" value="<?= $vb->telephone_2_branch ?>">
                            </div>
                          </div>
                          <div class="form-group">
                            <label for="" class="col-sm-3 control-label">Handphone</label>
                            <div class="col-md-8">
                              <input type="text" name="handphone_branch[]" class="form-control" placeholder="Handphone" value="<?= $vb->handphone_branch ?>">
                            </div>
                          </div>
                          <div class="form-group">
                            <label for="" class="col-sm-3 control-label">Email</label>
                            <div class="col-md-8">
                              <input type="text" name="email_branch[]" class="form-control" placeholder="E-mail" value="<?= $vb->email_branch ?>">
                            </div>
                          </div>
                          <div class="form-group">
                            <label for="" class="col-sm-3 control-label">Fax</label>
                            <div class="col-md-8">
                              <input type="text" name="fax_branch[]" class="form-control" placeholder="Fax" value="<?= $vb->fax_branch ?>">
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="" class="col-sm-3 control-label">Country</label>
                            <div class="col-md-8">
                              <select class="form-control select2-100 country_branch" id="country_branch<?= $no ?>" data-id="<?= $no ?>" name="country[]"></select>
                              <input type="hidden" id="cb_h<?= $no ?>" data-id="<?= $no ?>" value="<?= $vb->id_country_branch ?>">
                            </div>
                          </div>
                          <div class="form-group">
                            <label for="" class="col-sm-3 control-label">Province</label>
                            <div class="col-md-8">
                              <select class="form-control select2-100 prov_branch" id="prov_branch<?= $no ?>" data-id="<?= $no ?>" name="id_prov_branch[]"></select>
                              <input type="hidden" id="pb_h<?= $no ?>" data-id="<?= $no ?>" value="<?= $vb->id_prov_branch ?>">
                            </div>
                          </div>
                          <div class="form-group">
                            <label for="" class="col-sm-3 control-label">City</label>
                            <div class="col-md-8">
                              <select class="form-control select2-100 city_branch" id="city_branch<?= $no ?>" data-id="<?= $no ?>" name="city_branch[]"></select>
                              <input type="hidden" id="cib_h<?= $no ?>" data-id="<?= $no ?>" value="<?= $vb->city_branch ?>">
                            </div>
                          </div>
                          <div class="form-group">
                            <label for="" class="col-sm-3 control-label">Address</label>
                            <div class="col-md-8">
                              <textarea type="text" name="address_branch[]" class="form-control" placeholder="Address"><?= $vb->address_branch ?></textarea>
                            </div>
                          </div>
                          <div class="form-group">
                            <label for="" class="col-sm-3 control-label">ZIP Code</label>
                            <div class="col-md-4">
                              <input type="text" name="zip_code_branch[]" class="form-control" placeholder="ZIP Code" value="<?= $vb->zip_code_branch ?>">
                            </div>
                          </div>

                        </div>

                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
            <br>
            <label class="label_input">
              <a class="btn btn-sm btn-success" id="addBranchList">Add Branch List</a>
            </label>
            <br>
          </section>

        </div>


      </div>
      <label class="label_input">
        <?php
        echo form_button(array('type' => 'button', 'class' => 'btn btn-md btn-success', 'style' => 'min-width:100px; float:right;', 'value' => 'save', 'content' => 'Save', 'id' => 'addCustomerSave')) . ' ';
        ?>
      </label>

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

  .w100 {
    display: inline-block;
    width: 100%;
  }

  .inline-block {
    display: inline-block;
  }

  .checkbox-label:hover {
    cursor: pointer;
  }

  .hideIt {
    display: none;
  }

  .showIt {
    display: block;
  }
</style>

<script type="text/javascript">
  $(document).ready(function() {
    getCustomerCat();
    // getProv();
    // getSales();
    // getReference();
    //console.log($(".branch").length);
    $(".datepicker").datepicker({
      format: "yyyy-mm-dd",
      showInputs: true,
      autoclose: true
    });
    $(".select2").select2({
      placeholder: "Choose An Option",
      allowClear: true,
      width: '70%',
      dropdownParent: $("#form-supplier")
    });
    $(".select2-100").select2({
      placeholder: "Choose An Option",
      allowClear: true,
      width: '100%',
      dropdownParent: $("#form-supplier")
    });
    //ADD LIST BUTTON
    $('#addPIC').click(function(e) {
      var x = parseInt(document.getElementById("tfoot-pic").rows.length) + 1;
      getReligion(x);
      //console.log(x);
      var row = '<tr class="addjs">' +
        '<td>' +
        '<div class="input-group">' +
        '<span class="input-group-btn">' +
        '<button class="btn btn-danger hapus_item_js" title="Delete List" type="button"><i class="fa fa-times"></i></button>' +
        '</span>' +
        '<input type="text" name="pic[]" class="form-control" placeholder="PIC Name">' +
        '</div>' +
        '</td>' +
        '<td>' +
        '<input type="text" name="pic_phone[]" class="form-control" placeholder="Phone Number">' +
        '</td>' +
        '<td>' +
        '<input type="text" name="pic_email[]" class="form-control" placeholder="Email">' +
        '</td>' +
        '<td>' +
        '<input type="text" name="pic_position[]" class="form-control" placeholder="Position">' +
        '</td>' +
        '<td>' +
        '<select name="pic_religion[]" class="form-control select2-100" data-id="' + x + '" id="religion_' + x + '" placeholder="Religion"></select>' +
        '</td>' +
        '</tr>'
      $('#tfoot-pic').append(row);
      $(".select2-100").select2({
        placeholder: "Choose An Option",
        allowClear: true,
        width: '100%',
        dropdownParent: $("#form-supplier")
      });

    });
    $('#addBranchList').click(function(e) {
      var x = parseInt($(".branch").length) + 1; //parseInt(document.getElementById("tfoot-pic").rows.length)+1;
      getCountryBranch(x);
      //console.log(x);
      var row =
        '<div class="branch">' +
        '  <div class="col-sm-12" style="padding:2%">' +
        '  <legend class="legend"> <a class="text-red hapus_item_js" href="javascript:void(0)" title="Delete List"><i class="fa fa-times"></i></a> Branch No. <span class="numbering">' + x + '</span></legend> ' +
        '    <div class="row form-horizontal" style="">' +
        '      <div class="col-md-4">' +

        '       <div class="form-group">' +
        '         <label for="" class="col-sm-3 control-label"> Name Branch </label>' +
        '           <div class="col-sm-8">' +
        '             <input type="text" name="name_branch[]" class="form-control" placeholder="Name Branch">' +
        '           </div>' +
        '       </div>' +

        '       <div class="form-group">' +
        '         <label for="" class="col-sm-3 control-label">PIC</label>' +
        '           <div class="col-md-8">' +
        '             <input type="text" name="pic_branch[]" class="form-control" placeholder="PIC Branch">' +
        '           </div>' +
        '       </div>' +
        '       <div class="form-group">' +
        '         <label for="" class="col-sm-3 control-label">Telephone</label>' +
        '           <div class="col-md-8">' +
        '             <input type="text" name="telephone_1_branch[]" class="form-control" placeholder="Telephone">' +
        '           </div>' +
        '       </div>' +
        '       <div class="form-group">' +
        '         <label for="" class="col-sm-3 control-label"></label>' +
        '           <div class="col-md-8">' +
        '             <input type="text" name="telephone_2_branch[]" class="form-control" placeholder="Other Telephone">' +
        '           </div>' +
        '       </div>' +
        '       <div class="form-group">' +
        '         <label for="" class="col-sm-3 control-label">Handphone</label>' +
        '           <div class="col-md-8">' +
        '             <input type="text" name="handphone_branch[]" class="form-control" placeholder="Handphone">' +
        '           </div>' +
        '       </div>' +
        '       <div class="form-group">' +
        '         <label for="" class="col-sm-3 control-label">Email</label>' +
        '           <div class="col-md-8">' +
        '             <input type="text" name="email_branch[]" class="form-control" placeholder="E-mail">' +
        '           </div>' +
        '       </div>' +
        '       <div class="form-group">' +
        '         <label for="" class="col-sm-3 control-label">Fax</label>' +
        '           <div class="col-md-8">' +
        '             <input type="text" name="fax_branch[]" class="form-control" placeholder="Fax">' +
        '           </div>' +
        '       </div>' +
        '     </div>' +


        '     <div class="col-md-6">' +
        '       <div class="form-group">' +
        '         <label for="" class="col-sm-3 control-label">Country</label>' +
        '           <div class="col-md-8">' +
        '             <select class="form-control select2-100 country_branch" id="country_branch' + x + '" data-id="' + x + '" name="country[]"></select>' +
        '           </div>' +
        '       </div>' +
        '       <div class="form-group">' +
        '         <label for="" class="col-sm-3 control-label">Province</label>' +
        '           <div class="col-md-8">' +
        '             <select class="form-control select2-100 prov_branch" id="prov_branch' + x + '" data-id="' + x + '" name="id_prov_branch[]"></select>' +
        '           </div>' +
        '       </div>' +
        '       <div class="form-group">' +
        '           <label for="" class="col-sm-3 control-label">City</label>' +
        '           <div class="col-md-8">' +
        '             <select class="form-control select2-100 city_branch" id="city_branch' + x + '" data-id="' + x + '" name="city_branch[]"></select>' +
        '           </div>' +
        '       </div>' +
        '       <div class="form-group">' +
        '         <label for="" class="col-sm-3 control-label">Address</label>' +
        '           <div class="col-md-8">' +
        '             <textarea type="text" name="address_branch[]" class="form-control" placeholder="Address"></textarea>' +
        '           </div>' +
        '       </div>' +
        '       <div class="form-group">' +
        '         <label for="" class="col-sm-3 control-label">ZIP Code</label>' +
        '           <div class="col-md-4">' +
        '             <input type="text" name="zip_code_branch[]" class="form-control" placeholder="ZIP Code">' +
        '           </div>' +
        '       </div>' +

        '      </div>' +
        '    </div>' +
        '    </div>' +
        '  </div>' +
        '</div>';

      $('#branchList').append(row);
      $(".select2-100").select2({
        placeholder: "Choose An Option",
        allowClear: true,
        width: '100%',
        dropdownParent: $("#form-supplier")
      });

    });

    //REMOVE LIST BUTTON
    $('#tfoot-pic').on('click', '.hapus_item_js', function() {
      //console.log('a');
      $(this).parents('tr').remove();
      if (parseInt(document.getElementById("tfoot-pic").rows.length) == 0) {
        var x = 1;
      } else {
        var x = parseInt(document.getElementById("tfoot-pic").rows.length) + 1;
      }
      for (var i = 0; i < x; i++) {
        $('.numbering-pic').eq(i - 1).text(i);
      }
    });

    $('#branchList').on('click', 'a.hapus_item_js', function() {
      console.log('a');
      $(this).parents('.branch').remove();
      if (parseInt($(".branch").length) == 0) {
        var x = 1;
      } else {
        var x = parseInt($(".branch").length) + 1;
      }
      for (var i = 0; i < x; i++) {
        $('.numbering-branch').eq(i - 1).text(i);
      }
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

    $(document).on('click', '#generate_id', function(e) {
      var name = $('#name_customer').val();
      if (name != '') {
        $.ajax({
          url: siteurl + active_controller + "getID",
          dataType: "json",
          type: 'POST',
          data: {
            nm: name
          },
          success: function(result) {
            $('#id_customer').val(result.id);
          },
          error: function(request, error) {
            console.log(arguments);
            alert(" Can't do because: " + error);
          }
        });
      } else {
        swal({
          title: "Warning!",
          text: "Complete Customer name first, please!",
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

    $(document).on('change', '#id_category_customer', function(e) {
      var id_selected = $('#id_category_customer').val();
      getCode(id_selected);
      console.log(id_selected);
    });
    $(document).on('click change keyup paste blur', '#form-supplier .required', function(e) {
      //console.log('AHAHAHAHA');
      var val = $(this).val();
      //console.log('a='+val);
      var id = $(this).attr('id');
      if (val == '') {
        //$('.'+id).addClass('hideIt');
        $('.' + id).css('display', 'inline-block');
      } else {
        $('.' + id).css('display', 'none');
      }
    });
    $('.sel_all').click(function() {
      var day_html =
        '<option value="Monday">Monday</option>' +
        '<option value="Tuesday">Tuesday</option>' +
        '<option value="Wednesday">Wednesday</option>' +
        '<option value="Thursday">Thursday</option>' +
        '<option value="Friday">Friday</option>' +
        '<option value="Saturday">Saturday</option>' +
        '<option value="Sunday">Sunday</option>';
      var val = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
      if ($(this).is(":checked")) {
        if (this.id == 'sel_all_payment_day') {
          //$("#payment_day").select2("readonly", true);
          $("#payment_day").html(day_html)
          $("#payment_day").val(val);
        } else {
          //$("#day_invoice_receive").select2("readonly", true);
          $("#day_invoice_receive").html(day_html)
          $("#day_invoice_receive").val(val);
        }
      } else {
        if (this.id == 'sel_all_payment_day') {
          $("#payment_day").html(day_html)
          $("#payment_day").val('');
        } else {
          $("#day_invoice_receive").html(day_html)
          $("#day_invoice_receive").val('');
        }
      }
    });
    $(document).on('change', '.days', function(e) {
      console.log($(this).not(":selected").length);
      if ($(this).not(":selected").length == 0) {
        alert('a')
        if (this.id == 'payment_day') {
          $("#sel_all_payment_day").attr('checked', true);
        } else {
          $("#sel_all_day_invoice_receive").attr('checked', true);
        }
      } else {
        if (this.id == 'payment_day') {
          $("#sel_all_payment_day").attr('checked', false);
        } else {
          $("#sel_all_day_invoice_receive").attr('checked', false);
        }
      }
    });
    if ('<?php $getC ?>' != null) {
      var day_invoice_receive = <?php echo json_encode(explode(";", $getC->day_invoice_receive)); ?>;
      var payment_day = <?php echo json_encode(explode(";", $getC->payment_day)); ?>;
      var day_html =
        '<option value="Monday">Monday</option>' +
        '<option value="Tuesday">Tuesday</option>' +
        '<option value="Wednesday">Wednesday</option>' +
        '<option value="Thursday">Thursday</option>' +
        '<option value="Friday">Friday</option>' +
        '<option value="Saturday">Saturday</option>' +
        '<option value="Sunday">Sunday</option>';
      $("#day_invoice_receive").html(day_html)
      $("#day_invoice_receive").val(day_invoice_receive);
      $("#payment_day").html(day_html)
      $("#payment_day").val(payment_day);
    }
    if ('<?= $this->uri->segment(4) ?>' == 'view') {
      $('.label_view').css("display", "block");
      $('.label_input').css("display", "none");
    } else {
      $('.label_view').css("display", "none");
      $('.label_input').css("display", "block");
    }
    $(document).on('change', '#day_invoice_receive', function(e) {
      console.log($(this).val());
    });
  });

  getCountry();
  $(document).on('change', '#id_country', function() {
    var id_count = $(this).val();
    getProv(id_count);
  })

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

  function getProv(id_count) {
    if ('<?= ($getC->id_prov) ?>' == null) {
      var id_selected = '';
    } else {
      var id_selected = '<?= $getC->id_prov ?>';
      getCity('<?= ($getC->id_prov) ?>');
    }
    //console.log(id_selected);
    var column = 'id_negara';
    var column_fill = id_count;
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

  $(document).on('change', '#id_prov', function() {
    var id_prov = $(this).val();
    getCity(id_prov);
  })

  function getCity(id_prov) {
    if ('<?= ($getC->id_city) ?>' == null) {
      var id_selected = '';
    } else {
      var id_selected = '<?= $getC->id_city ?>';
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
        $('#city').html(result.html);
      },
      error: function(request, error) {
        console.log(arguments);
        alert(" Can't do because: " + error);
      }
    });
  }


  function getCustomerCat() {
    if ('<?= ($getC->id_category_customer) ?>' != null) {
      var id_selected = '<?= $getC->id_category_customer ?>';
    } else if ($('#id_category_customer').val() != null || $('#id_category_customer').val() != '') {
      var id_selected = $('#id_category_customer').val();
    } else {
      var id_selected = '';
    }
    //getCode(id_selected);
    var column = '';
    var column_fill = '';
    var column_name = 'name_category_customer';
    var table_name = 'child_customer_category';
    var key = 'id_category_customer';
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
        $('#id_category_customer').html(result.html);
      },
      error: function(request, error) {
        console.log(arguments);
        alert(" Can't do because: " + error);
      }
    });
  }

  // RELIGIONS

  var x = parseInt(document.getElementById("tfoot-pic").rows.length);
  if (x > 0) {
    for (let i = 0; i < x; i++) {
      e = parseInt(i) + 1;
      // alert(e);
      getReligion(e);
    }
  }

  function getReligion(e) {
    // alert(e);
    if ($('#religion_h_' + e).val() == '') {
      var id_selected = '';
    } else {
      var id_selected = $('#religion_h_' + e).val();
    }
    //console.log(id_selected);
    var column = '';
    var column_fill = '';
    var column_name = 'name_religion';
    var table_name = 'religion';
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
        $('#religion_' + e).html(result.html);
      },
      error: function(request, error) {
        console.log(arguments);
        alert(" Can't do because: " + error);
      }
    });
  }


  // BRANCH DATA COUNTRY, PROV, CITY
  $(document).on('change', '.country_branch', function() {
    dataId = $(this).data('id');
    var id_country = $(this).val();
    getProvBranch(id_country, dataId);
  })

  var x = parseInt($(".branch").length); //parseInt(document.getElementById("tfoot-pic").rows.length)+1;
  if (x > 0) {
    for (let i = 0; i < x; i++) {
      element = parseInt(i) + 1;
      getCountryBranch(element);
    }
  }

  function getCountryBranch(x) {

    if ($('#cb_h' + x).val() == '') {
      var id_selected = '';
    } else {
      var id_selected = $('#cb_h' + x).val();
      getProvBranch(id_selected, x);
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
        $('#country_branch' + x).html(result.html);
      },
      error: function(request, error) {
        console.log(arguments);
        alert(" Can't do because: " + error);
      }
    });
  }

  function getProvBranch(id_country, dataId) {

    if ($('#pb_h' + dataId).val() == '') {
      var id_selected = '';
    } else {
      var id_selected = $('#pb_h' + dataId).val();
      getCityBranch(id_selected, dataId);
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
        $('#prov_branch' + dataId).html(result.html);
      },
      error: function(request, error) {
        console.log(arguments);
        alert(" Can't do because: " + error);
      }
    });
  }

  $(document).on('change', '.prov_branch', function() {
    dataId = $(this).data('id');
    var id_prov = $(this).val();
    getCityBranch(id_prov, dataId);
  })

  function getCityBranch(id_prov, dataId) {
    if ($('#cib_h' + dataId).val() == '') {
      var id_selected = '';
    } else {
      var id_selected = $('#cib_h' + dataId).val();
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
        $('#city_branch' + dataId).html(result.html);
      },
      error: function(request, error) {
        console.log(arguments);
        alert(" Can't do because: " + error);
      }
    });
  }

  getKaryawan();

  function getKaryawan() {

    if ('<?= $getC->id_karyawan ?>' == '') {
      var id_selected = '';
    } else {
      var id_selected = '<?= $getC->id_karyawan ?>';
    }
    //console.log(id_selected);
    var column = 'divisi';
    var column_fill = '3';
    var column_name = 'nama_karyawan';
    var table_name = 'ms_karyawan';
    var key = 'id_karyawan';
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
        $('#id_karyawan').html(result.html);
      },
      error: function(request, error) {
        console.log(arguments);
        alert(" Can't do because: " + error);
      }
    });
  }

  function getCode(id_cat) {
    // alert(id_cat);
    if (id_cat != '') {
      $.ajax({
        url: siteurl + active_controller + "getCodeByCat",
        dataType: "json",
        type: 'POST',
        data: {
          id_category_customer: id_cat
        },
        success: function(result) {
          $('#id_customer').val(result.id);
        },
        error: function(request, error) {
          // console.log(arguments);
          alert(" Can't do because: " + error);
        }
      });
    } else {
      alert(" Can't do because: ");
    }
  }

  function getValidation() {
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

      } else if ((node == 'INPUT' && type == 'text') || (node == 'SELECT') || (node == 'TEXTAREA')) {
        if ($(this).val() == null || $(this).val() == '') {
          var name = $(this).attr('id');

          name.replace('[]', '');
          $('.' + name).removeClass('hideIt');
          $('.' + name).css('display', 'inline-block');

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
</script>
