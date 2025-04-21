<?php

if (!empty($this->uri->segment(3))) {
  $getC             = $this->db->get_where('master_customer', array('id_customer' => $id))->row();
  $getCP            = $this->db->get_where('child_customer_pic', array('id_customer' => $id))->result();
  $getCB            = $this->db->get_where('child_customer_branch', array('id_customer' => $id))->result();
  $name_prov        = $this->db->get_where('provinsi', array('id_prov' => $getC->id_prov))->row();
  $name_country     = $this->db->get_where('negara', array('id' => $getC->id_country))->row();
  $name_city        = $this->db->get_where('kabupaten', array('id_kab' => $getC->id_city))->row();
  $name_category    = $this->db->get_where('child_customer_category', array('id_category_customer' => $getC->id_category_customer))->row();
  $name_sales       = $this->db->get_where('ms_karyawan', array('id_karyawan' => $getC->id_karyawan))->row();
  $name_reference   = $this->db->get_where('child_customer_reference', array('id_reference' => $getC->id_reference))->row();
}
$getI   = $this->db->query("SELECT MAX(id_customer) AS max_id FROM master_customer ORDER BY id_customer DESC LIMIT 1")->row();
//echo "$first_letter";
//exit;
$num = substr($getI->max_id, 1, 5) + 1;
$id_customer = 'C' . str_pad($num, 5, "0", STR_PAD_LEFT);
?>
<section class="content">
  <legend class="legend" style="font-size:26px"><strong><?= strtoupper($getC->name_customer) ?></strong></legend>
  <div class="row">
    <div class="col-md-6">
      <table width="100%" class="table-condensed">
        <tbody>
          <tr>
            <td width="30%"><strong>ID Customer</strong></td>
            <td>: <?= strtoupper($getC->id_customer) ?></td>
          </tr>
          <tr>
            <td><strong>Telephone</strong></td>
            <td>: <?= strtoupper($getC->telephone) ?></td>
          </tr>
          <tr>
            <td><strong></strong></td>
            <td>&nbsp&nbsp<?= strtoupper($getC->telephone_2) ?> </td>
          </tr>
          <tr>
            <td><strong>Fax</strong></td>
            <td>: <?= strtoupper($getC->fax) ?> </td>
          </tr>
          <tr>
            <td><strong>E-mail</strong></td>
            <td>: <?= ($getC->email) ?> </td>
          </tr>
          <tr>
            <td><strong>Status</strong></td>
            <td>:
              <?php
              if ($getC->activation == 'active') {
                $bg = 'bg-blue';
              } else {
                $bg = 'bg-red';
              } ?>
              <span class="badge <?= $bg ?>"><?= ucfirst($getC->activation) ?></span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="col-md-6">
      <table width="100%" class="table-condensed">
        <tbody>
          <tr>
            <td width="30%"><strong>Country</strong></td>
            <td>: <?= strtoupper($name_country->nm_negara) ?></td>
          </tr>
          <tr>
            <td><strong>Province</strong></td>
            <td>: <?= strtoupper($name_prov->nama) ?> </td>
          </tr>
          <tr>
            <td><strong>City</strong></td>
            <td>: <?= strtoupper($name_city->nama) ?> </td>
          </tr>
          <tr>
            <td><strong>Address</strong></td>
            <td>: <?= strtoupper($getC->address_office) ?> </td>
          </tr>
          <tr>
            <td width="30%"><strong>Zip Code</strong></td>
            <td>: <?= strtoupper($getC->zip_code) ?> </td>
          </tr>
          <tr>
            <td><strong>Longitude</strong></td>
            <td>: <?= strtoupper($getC->longitude) ?> </td>
          </tr>
          <tr>
            <td><strong>Latitude</strong></td>
            <td>: <?= strtoupper($getC->latitude) ?> </td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>
  <!-- <span for="input-id">ID Customer</span>
  <div class="row">
    <div class="col-md-4">
      <div>
        <div class="">
          <label for="input-id" class="f-16"><?= strtoupper($getC->id_customer) ?></label>
        </div>
      </div>
    </div>
  </div> -->

  <hr>


  <div class="nav-tabs-custom">
    <ul class="nav nav-tabs nav-justified">
      <li class="active"><a href="#productivity" data-toggle="tab"><strong>DETAIL PRODUCTIVITY</a></strong></li>
      <li><a href="#invoice_bill" data-toggle="tab"><strong>INVOICE BILL INFORMATION</a></strong></li>
      <li><a href="#pic" data-toggle="tab"><strong>PIC</a></strong></li>
      <li><a href="#branch" data-toggle="tab"><strong>BRANCH</a></strong></li>
    </ul>
    <div class="tab-content">
      <div class="tab-pane active" id="productivity">
        <div class="row" style="padding-left:10px">
          <div class="col-md-4">
            <div class="mt-2">
              <span>Category Customer</span>
              <div class="">
                <label for="input-id" class="f-16"><?= $name_category->name_category_customer ?></label>
              </div>
            </div>

            <div class="mt-2">
              <span>Customer Start Date</span>
              <div class="">
                <label for="input-id" class="f-16"><?= $getC->start_date ?></label>
              </div>
            </div>

            <div class="mt-2">
              <span>Marketing</span>
              <div class="">
                <label for="input-id" class="f-16"><?= $name_sales->nama_karyawan ?></label>
              </div>
            </div>

          </div>
          <div class="col-md-4">
            <div class="mt-2">
              <span>Credit Limit</span>
              <div class="">
                <label for="input-id" class="f-16"><?= $getC->credit_limit ?></label>
              </div>
            </div>
            <div class="mt-2">
              <span>Remarks</span>
              <div class="">
                <label for="input-id" class="f-16"><?= $getC->remarks ?></label>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- /.tab-pane -->
      <div class="tab-pane" id="invoice_bill">
        <div class="row mt-2" style="padding-left:10px">
          <div class="col-md-8">
            <table class="table-condensed" width="100%">
              <tbody>
                <tr>
                  <td width="30%">NPWP/PKP Number</td>
                  <td>: <label><?= $getC->npwp ?></label></td>
                </tr>
                <tr>
                  <td>NPWP/PKP Address</td>
                  <td>: <label><?= $getC->npwp_address ?></label></td>
                </tr>
                <tr>
                  <td>VAT Name</td>
                  <td>: <label><?= $getC->vat_name ?></label></td>
                </tr>
                <tr>
                  <td>PIC Finance</td>
                  <td>: <label><?= $getC->pic_finance ?></label></td>
                </tr>
                <tr>
                  <td>Invoice Address</td>
                  <td>: <label> <?= $getC->invoice_address ?></label></td>
                </tr>
                <tr>
                  <td>Virtual Acc. Number</td>
                  <td>: <label><?= $getC->va_number ?></label></td>
                </tr>
                <tr>
                  <td>Day invoice receive</td>
                  <td>: <strong>
                      <?= implode(", ", explode(";", $getC->day_invoice_receive)) ?>
                    </strong>
                  </td>
                </tr>
                <tr>
                  <td>Receipt Time</td>
                  <td class="">
                    From : <label for="input-id" class="f-16"><?= $getC->receipt_time_1 ?></label> to
                    <label for="input-id" class="f-16"><?= $getC->receipt_time_2 ?></label>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="col-md-4 ">
            <span>Payment Requirement</span>
            <div class="mt-2">
              <label for=""><i class='fa fa-check-square-o'></i>
                <?= implode("<br> <i class='fa fa-check-square-o'></i> ", explode(";", $getC->payment_req)) ?>
              </label>
            </div>
          </div>
        </div>
      </div>
      <!-- /.tab-pane -->
      <div class="tab-pane" id="pic">
        <div class="mt-2">

          <table class="table-condensed table-bordered table">
            <thead class="bg-success">
              <tr style="font-weight:700;color:#333">
                <td>Name PIC</td>
                <td>Phone Number</td>
                <td>Email Address</td>
                <td>Position</td>
                <td>Religion</td>
              </tr>
            </thead>
            <tbody>
              <?php $no = 0;
              foreach ($getCP as $key => $vp) : $no++;
                $religion = $this->db->get_where('religion', array('id' => $vp->religion_pic))->row();
              ?>
                <tr>
                  <td><?= $vp->name_pic ?></td>
                  <td> <?= $vp->phone_pic ?></td>
                  <td><?= $vp->email_pic ?></td>
                  <td><?= $vp->position_pic ?></td>
                  <td><?= $religion->name_religion ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="tab-pane" id="branch">
        <?php if ($getCB) : ?>
          <?php $no = 0;
          foreach ($getCB as $key => $vb) : $no++;
            $name_country_b   = $this->db->get_where('negara', array('id' => $vb->id_country_branch))->row();
            $name_prov_b        = $this->db->get_where('provinsi', array('id_prov' => $vb->id_prov_branch))->row();
            $name_city_b      = $this->db->get_where('kabupaten', array('id_kab' => $vb->city_branch))->row();
          ?>
            <legend class="mt-2"><strong><?= $no ?>. <?= $vb->name_branch ?></strong></legend>
            <div class="row" style="padding-left:10px">
              <div class="col-md-5">
                <table class="table-condensed" width="100%">
                  <tbody>
                    <tr>
                      <td width="30%"><strong>PIC</strong></td>
                      <td>: <?= $vb->pic_branch ?></td>
                    </tr>
                    <tr>
                      <td><strong>Telephone</strong></td>
                      <td>: <?= $vb->telephone_1_branch ?></td>
                    </tr>
                    <tr>
                      <td></td>
                      <td>&nbsp <?= $vb->telephone_2_branch ?></td>
                    </tr>
                    <tr>
                      <td><strong>Handphone</strong></td>
                      <td>: <?= $vb->handphone_branch ?></td>
                    </tr>
                    <tr>
                      <td><strong>Email</strong></td>
                      <td>: <?= $vb->email_branch ?></td>
                    </tr>
                    <tr>
                      <td><strong>Fax</strong></td>
                      <td>: <?= $vb->fax_branch ?></td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div class="col-md-7">
                <table class="table-condensed" width="100%">
                  <tbody>
                    <tr>
                      <td width="25%"><strong>Country</strong></td>
                      <td>: <?= $name_country_b->nm_negara ?></td>
                    </tr>
                    <tr>
                      <td><strong>Province</strong></td>
                      <td>: <?= $name_prov_b->nama ?></td>
                    </tr>
                    <tr>
                      <td><strong>City</strong></td>
                      <td>: <?= $name_city_b->nama ?></td>
                    </tr>
                    <tr>
                      <td><strong>Address</strong></td>
                      <td>: <?= $vb->address_branch ?></td>
                    </tr>
                    <tr>
                      <td><strong>ZIP Code</strong></td>
                      <td>: <?= $vb->zip_code_branch ?></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

          <?php endforeach; ?>
        <?php endif; ?>

      </div>
      <!-- /.tab-pane -->
    </div>
    <!-- /.tab-content -->
  </div>


</section>

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

  .mt-2 {
    padding-top: 1.5em;
  }

  label.f-16 {
    font-size: 16px;
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
      //console.log(x);
      var row = '<tr class="addjs">' +
        '<td style="background-color:#E0E0E0">' +
        '<a class="text-red hapus_item_js" href="javascript:void(0)" title="Delete List"><i class="fa fa-times"></i></a> || <span class="numbering">' +
        x + '</span> ' +
        '<input type="text" name="pic[]"  class="form-control input-sm text-left" required="" placeholder="Input PIC Name" style="width:80%;display:inline-block">' +
        '</td>' +
        '<td style="background-color:#E0E0E0">' +
        '<input type="text" name="pic_phone[]"  class="form-control input-sm inSp2 text-left" required="" placeholder="Input PIC Number" style="width:100%;display:inline-block">' +
        '</td>' +
        '<td style="background-color:#E0E0E0">' +
        '<input type="text" name="pic_email[]"  class="form-control input-sm inSp2 text-left" required="" placeholder="Input PIC Email" style="width:100%;display:inline-block">' +
        '</td>' +
        '<td style="background-color:#E0E0E0">' +
        '<input type="text" name="pic_position[]"  class="form-control input-sm inSp2 text-left" required="" placeholder="Input PIC Position" style="width:100%;display:inline-block">' +
        '</td>' +
        '<td style="background-color:#E0E0E0">' +
        '<input type="text" name="pic_religion[]"  class="form-control input-sm inSp2 text-left" required="" placeholder="Input PIC Religion" style="width:100%;display:inline-block">' +
        '</td>' +
        '</tr>'

      $('#tfoot-pic').append(row);

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
    $('#tfoot-pic').on('click', 'a.hapus_item_js', function() {
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
  /*
  function getBrandX() {
    var id_customer = '<?= $id ?>';
    var id_brand    = <?php echo json_encode(explode(";", $getC->id_brand)); ?>;
    $.ajax({
      url: siteurl+active_controller+"getBrandOpt",
      dataType : "json",
      type: 'POST',
      data: {id_customer:id_customer},
      success: function(result){
        $('#id_brand').html(result.html);
        $('#id_brand').val(id_brand);
      },
      error: function (request, error) {
        console.log(arguments);
        alert(" Can't do because: " + error);
      }
    });
  }
  function getCustomerTypeX() {
    var id_type = $('#id_type').val();
    var supplier_shipping = $('#supplier_shipping').val();
    $.ajax({
      url: siteurl+active_controller+"getCustomerTypeOpt",
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
  function getCustomerCapX(id_capacity=null) {
    var id_business = $('#id_business').val();
    $.ajax({
      url: siteurl+active_controller+"getCustomerCapOpt",
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
    //getCustomerType();
    //getBusinessCat('<?= empty($getC->id_business) ? "" : $getC->id_business ?>');
    //getCustomerCap('<?= empty($getC->id_capacity) ? "" : $getC->id_capacity ?>');
  }
  */
</script>