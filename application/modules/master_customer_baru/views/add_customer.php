
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
                        <input type="hidden" name="type">
                        <input type="text" class="form-control input input-sm required w30 " name="id_customer" id="id_customer" readonly>
                      </label>
                    </td>
                  </tr>

                  <tr id="my-grid-tr-name_customer">
                    <td class="text-left vMid">Customer Name <span class='text-red'>*</span></b></td>
                    <td class="text-left">
                      <label class="label_input">
					  <input type="text" id="name_customer"  name="name_customer" class="form-control w80 required" Placeholder = "Customer Name" Required>
					  </label>
					</td>
                  </tr>

                  <tr id="my-grid-tr-telephone">
                    <td class="text-left vMid">Telephone 1 <span class='text-red'>*</span></b></td>
                    <td class="text-left">
                      <label class="label_input">
					   <input type="text" id="telephone"  name="telephone[]" class="form-control required w15" Placeholder = "Code" maxlength="4" Required>
					   -
					   <input type="text" id="telephone"  name="telephone[]" class="form-control required numberOnly w40" Placeholder = "Number" maxlength="9" Required>
                      </label>
                    </td>
                  </tr>

                  <tr id="my-grid-tr-telephone_2">
                    <td class="text-left vMid"></td>
                    <td class="text-left">
                      <label class="label_view">
                      </label>
                      <label class="label_input">
					   <input type="text" id="telephone_2_1"  name="telephone_2[]" class="form-control required w15" Placeholder = "Code"maxlength="4">
					   -
					   <input type="text" id="telephone_2_2"  name="telephone_2[]" class="form-control required numberOnly w40" Placeholder = "Number" maxlength="9">
                      </label>
                    </td>
                  </tr>

                  <tr id="my-grid-tr-fax">
                    <td class="text-left vMid">Fax (Office)</td>
                    <td class="text-left">
                      <label class="label_view">
                      </label>
                      <label class="label_input">
					  <input type="text" id="fax  name="fax" class="form-control numberOnly w60" Placeholder = "FAX" maxlength="9" required>
                      </label>
                    </td>
                  </tr>

                  <tr id="my-grid-tr-email">
                    <td class="text-left vMid">E-Mail <span class='text-red'>*</span></td>
                    <td class="text-left">
                      <label class="label_input">
                        <input type="email" id="email" name="email" class="required form-control w60" placeholder="Company E-Mail" required >
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
                    </label>
                    <label class="label_input">
                      <select class="form-control input-sm required select2 w50" name="id_country" id="id_country">
							<option value="">-- Country --</option>
						<?php foreach ($results['country'] as $country){ 
						?>
						<option value="<?= $country->id_negara?>"><?= ucfirst(strtolower($country->nm_negara))?></option>
						<?php } ?>
                      </select>
                    </label>
                  </td>
                </tr>

                <tr id="my-grid-tr-id_prov">
                  <td class="text-left vMid">Province <span class='text-red'>*</span></td>
                  <td class="text-left">
                    <label class="label_input">
                      <select class="form-control input-sm required select2 w50" name="id_prov" id="id_prov">
                      </select>
                    </label>
                  </td>
                </tr>
                <tr id="my-grid-tr-city">
                  <td class="text-left vMid">City <span class='text-red'>*</span></b></td>
                  <td class="text-left">
                    <label class="label_view">
                    </label>
                    <label class="label_input">
                      <select class="form-control input-sm required select2 w50" name="city" id="city">
                      </select>
                    </label>
                  </td>
                </tr>

                <tr id="my-grid-tr-address_office">
                  <td class="text-left vMid">Address (Office) <span class='text-red'>*</span></b></td>
                  <td class="text-left">
                    <label class="label_input">
                      <textarea type="text" name="address_office" id="address_office" class="form-control input-sm required w70" placeholder="Address Office"></textarea>
                    </label>
                  </td>
                </tr>

                <tr id="my-grid-tr-zip_code">
                  <td class="text-left vMid">ZIP Code <span class='text-red'>*</span></td>
                  <td class="text-left">
                    <label class="label_input">
                      <input type="text" class="form-control required w50" id="zip_code" name="zip_code" placeholder="ZIP Code">
                    </label>
                  </td>
                </tr>

                <tr id="my-grid-tr-longitude">
                  <td class="text-left vMid">Longitude</td>
                  <td class="text-left">
                    <label class="label_input">
					<input type="text" class="form-control required w50" id="longitude" name="longitude" placeholder="Longitude">
                    </label>
                  </td>
                </tr>

                <tr id="my-grid-tr-latitude">
                  <td class="text-left vMid">Latitude</td>
                  <td class="text-left">
                    <label class="label_input">
					<input type="text" class="form-control required w50" id="latitude" name="latitude" placeholder="Latitude">
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
                      <label class="label_input">
                      <select class="form-control input-sm required select2 w50" name="id_category_customer" id="id_category_customer">
							<option value="">-- Category --</option>
						<?php foreach ($results['kategori'] as $kategori){ 
						?>
						<option value="<?= $kategori->id_category?>"><?= ucfirst(strtolower($kategori->name_catego))?></option>
						<?php } ?>
                      </select>
                      </label>
                    </td>
                  </tr>

                  <tr id="my-grid-tr-start_date">
                    <td class="text-left vMid">Customer Start Date <span class='text-red'>*</span></b></td>
                    <td class="text-left">
					<input type="text" id="start_date" name = "start_date" class = "form-control required datepicker w30" placeholder = "Customer Start Date" required>
                      <label class="label_input">
                      </label>
                    </td>
                  </tr>

                  <tr id="my-grid-tr-id_karyawan">
                    <td class="text-left vMid">Marketing <span class='text-red'>*</span></b></td>
                    <td class="text-left">
                      <label class="label_input">
                      <select class="form-control input-sm required select2 w50" name="id_karyawan" id="id_karyawan">
							<option value="">-- Category --</option>
						<?php foreach ($results['karyawan'] as $karyawan){ 
						?>
						<option value="<?= $karyawan->id_karyawan?>"><?= ucfirst(strtolower($karyawan->nama_karyawan))?></option>
						<?php } ?>
                      </select>
                      </label>
                    </td>
                  </tr>
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
					  <input type ="text" id="credit_limit" name="credit_limit" class = "form-control numberOnly nominal" placeholder = "Credit limit" required >
                      <span class="input-group-addon" id="basic-addon2">.00</span>
                    </div>
                  </label>
                </td>
              </tr>

              <tr id="my-grid-tr-remarks">
                <td class="text-left vMid">Remarks</td>
                <td class="text-left">
                  <label class="label_input">
                    <textarea name="remarks" id="remarks" class="form-control w80" placeholder="Remarks"></textarea>

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
					<input type="text" id="npwp" name="npwp" class ="numberOnly form-control w70 required" placeholder="NPWP/PKP Number" Required>
                    </label>
                  </td>
                </tr>

                <tr id="my-grid-tr-vat_name">
                  <td class="text-left vMid">NPWP Name <span class="text-red">*</span></td>
                  <td class="text-left">
                    <label class="label_input">
					<input type="text" id="vat_name" name="vat_name" class ="form-control w70 required" placeholder="NPWP Name" Required>
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
                  <tr>
                    <td>
                      <div class="input-group">
                        <span class="input-group-btn">
                          <button class="btn btn-danger hapus_item_js" title="Delete List" type="button"><i class="fa fa-times"></i><span class="numbering-pic"></span></button>
                        </span>
                        <input type="text" name="pic[]" class="form-control" placeholder="PIC Name" >
                      </div>
                    </td>
                    <td>
                      <div class="label_input">
                        <input type="text" name="pic_phone[]" class="form-control" placeholder="Phone Number" >
                      </div>
                    </td>
                    <td>
                      <div class="label_input">
                        <input type="text" name="pic_email[]" class="form-control" placeholder="Email" >
                      </div>
                    </td>
                    <td>
                      <div class="label_input">
                        <input type="text" name="pic_position[]" class="form-control" placeholder="Position" >
                      </div>
                    </td>
                    <td width="20%">
                      <div class="label_input">
                      <select class="form-control select2-100" name="pic_religion[]" id="pic_religion">
							<option value="">-- Country --</option>
						<?php foreach ($results['religion'] as $religion){ 
						?>
						<option value="<?= $religion->id?>"><?= ucfirst(strtolower($religion->name_religi))?></option>
						<?php } ?>
                      </select>
                      </div>
                    </td>
                  </tr>
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
                              <select class="form-control select2-100 country_branch" id="country_branch<?= $no ?>" data-id="<?= $no ?>" name="country[]">
										<option value="">-- Country --</option>
										<?php foreach ($results['country'] as $country){ 
										?>
										<option value="<?= $country->id_negara?>"><?= ucfirst(strtolower($country->nm_negara))?></option>
										<?php } ?>
							  </select>
							  </div>
                          </div>
                          <div class="form-group">
                            <label for="" class="col-sm-3 control-label">Province</label>
                            <div class="col-md-8">
                              <select class="form-control select2-100 prov_branch" id="prov_branch<?= $no ?>" data-id="<?= $no ?>" name="id_prov_branch[]">
							  </select>
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
    $('#addPIC').click(function(e) {
      var x = parseInt(document.getElementById("tfoot-pic").rows.length) + 1;

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
        '<select name="pic_religion[]" class="form-control select2-100" data-id="' + x + '" id="religion_' + x + '" placeholder="Religion">'+
		'<option value="">-- Category --</option>'+
		'<?php foreach ($results['religion'] as $religion){?>'+
		'<option value="<?= $religion->id?>"><?= ucfirst(strtolower($religion->name_religi))?></option>'+
		'<?php } ?>'+
		'</select>' +
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
        '             <select class="form-control select2-100 country_branch" id="country_branch' + x + '" data-id="' + x + '" name="country[]">'+
		'<?php foreach ($results['country'] as $country){?>'+
		'<option value="<?= $country->id_negara?>"><?= ucfirst(strtolower($country->nm_negara))?></option>'+
		'<?php } ?>'+
		'             </select>' +
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

$("#id_country").change(function(){

            // variabel dari nilai combo box kendaraan
            var id_negara = $("#id_country").val();

            // Menggunakan ajax untuk mengirim dan dan menerima data dari server
            $.ajax({
                url:siteurl+'master_customer/get_prov',
                method : "POST",
                data : {id_negara:id_negara},
                async : false,
                dataType : 'json',
                success: function(data){
                    var html = '';
                    var i;

                    for(i=0; i<data.length; i++){
                        html += '<option value='+data[i].id_prov+'>'+data[i].nama+'</option>';
                    }
                    $('#id_prov').html(html);

                }
            });
        });
$("#id_prov").change(function(){

            // variabel dari nilai combo box kendaraan
            var id_prov = $("#id_prov").val();

            // Menggunakan ajax untuk mengirim dan dan menerima data dari server
            $.ajax({
                url:siteurl+'master_customer/get_city',
                method : "POST",
                data : {id_prov:id_prov},
                async : false,
                dataType : 'json',
                success: function(data){
                    var html = '';
                    var i;

                    for(i=0; i<data.length; i++){
                        html += '<option value='+data[i].id_kota+'>'+data[i].nama_kota+'</option>';
                    }
                    $('#city').html(html);

                }
            });
        });
  



  
  
  
  
  
  
  
  


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
	$
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
    var column = '';
    var column_fill = '';
    var column_name = 'nama_karyawan';
    var table_name = 'karyawan';
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