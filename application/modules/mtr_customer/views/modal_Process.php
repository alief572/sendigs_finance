<?php

if (!empty($this->uri->segment(3))) {
  $getC		= $this->db->get_where('master_supplier',array('id_supplier'=>$this->uri->segment(4)))->row();
  $getSP		= $this->db->get_where('child_supplier_pic',array('id_supplier'=>$this->uri->segment(4)))->result();
  $getSB		= $this->db->get_where('child_supplier_bank',array('id_supplier'=>$this->uri->segment(4)))->result();
}

?>
<form class="" id="form-suppplier" action="" method="post">
  <div class="box box-success">
	<div class="box-body" style="">
    <div class="row">
      <div class="col-sm-12 col-md-6 col-lg-6">
        <table id="my-grid3" class="table table-striped table-bordered table-hover table-condensed" width="100%">
          <tbody>
            <tr style='background-color: #175477 !important; color: white; font-size: 15px !important;'>
              <th class="text-center" colspan='3'>DETAIL SUPPLIER IDENTITY</th>
            </tr>
            <tr id="my-grid-tr-id_supplier">
              <td class="text-left vMid">Code <span class='text-red'>*</span></b></td>
              <td class="text-left">
                <input type="hidden" name="type" value="<?=empty($getC->id_supplier)?'add':'edit'?>">
                <input type="text" class="form-control input input-sm w50" name="id_supplier" id="id_supplier" value="<?=empty($getC->id_supplier)?'':$getC->id_supplier?>" readonly>
                <label class="label label-danger id_supplier hideIt">Code Can't be empty!</label>
              </td>
            </tr>

            <tr id="my-grid-tr-nm_supplier">
              <td class="text-left vMid">Supplier Name</td>
              <td class="text-left">
                <?php
                echo form_input(array('type'=>'text','id'=>'nm_supplier','name'=>'nm_supplier','class'=>'form-control input-sm w70', 'placeholder'=>'Supplier Name','autocomplete'=>'off','value'=>empty($getC->nm_supplier)?'':$getC->nm_supplier))
                ?>
                <a id="generate_id" class="btn btn-sm btn-primary" style="display:inline-block">Generate ID</a>
                <label class="label label-danger nm_supplier hideIt">Supplier Name Can't be empty!</label>
              </td>
            </tr>

            <tr id="my-grid-tr-id_country">
              <td class="text-left vMid">Country <span class='text-red'>*</span></b></td>
              <td class="text-left">
                <select class="form-control input-sm select2 w50" name="id_country" id="id_country">
                </select>
                <label class="label label-danger id_country hideIt">Country Can't be empty!</label>
              </td>
            </tr>

            <tr id="my-grid-tr-city">
              <td class="text-left vMid">City <span class='text-red'>*</span></b></td>
              <td class="text-left">
                <?php
                  echo form_input(array('type'=>'text','id'=>'city','name'=>'city','class'=>'form-control input-sm w50', 'placeholder'=>'City','autocomplete'=>'off','value'=>empty($getC->city)?'':$getC->city))
                ?>
                <label class="label label-danger city hideIt">City Can't be empty!</label>
              </td>
            </tr>

            <tr id="my-grid-tr-address_office">
              <td class="text-left vMid">Address (Office)</td>
              <td class="text-left">
                <?php
                echo form_input(array('type'=>'text','id'=>'address_office','name'=>'address_office','class'=>'form-control input-sm', 'placeholder'=>'Address (Office)','autocomplete'=>'off','value'=>empty($getC->address_office)?'':$getC->address_office))
                ?>
                <label class="label label-danger address_office hideIt">Address (Office) Can't be empty!</label>
              </td>
            </tr>

            <tr id="my-grid-tr-address_factory">
              <td class="text-left vMid">Address (Factory)</td>
              <td class="text-left">
                <?php
                echo form_input(array('type'=>'text','id'=>'address_factory','name'=>'address_factory','class'=>'form-control input-sm', 'placeholder'=>'Address (Factory)','autocomplete'=>'off','value'=>empty($getC->address_factory)?'':$getC->address_factory))
                ?>
                <label class="label label-danger address_factory hideIt">Address (Factory) Can't be empty!</label>
              </td>
            </tr>

            <tr id="my-grid-tr-zip_code">
              <td class="text-left vMid">ZIP Code <span class='text-red'>*</span></b></td>
              <td class="text-left">
                <?php
                  echo form_input(array('type'=>'text','id'=>'zip_code','name'=>'zip_code','class'=>'form-control input-sm w30', 'placeholder'=>'ZIP Code','autocomplete'=>'off','value'=>empty($getC->zip_code)?'':$getC->zip_code))
                ?>
                <label class="label label-danger zip_code hideIt">ZIP Code Can't be empty!</label>
              </td>
            </tr>

            <tr id="my-grid-tr-telephone">
              <td class="text-left vMid">Telephone (Office) <span class='text-red'>*</span></b></td>
              <td class="text-left">
                <?php
                  echo form_input(array('type'=>'text','id'=>'telephone','name'=>'telephone','class'=>'form-control input-sm w30', 'placeholder'=>'Telephone','autocomplete'=>'off','value'=>empty($getC->telephone)?'':$getC->telephone))
                ?>
                <label class="label label-danger telephone hideIt">Telephone (Office) Can't be empty!</label>
              </td>
            </tr>

            <tr id="my-grid-tr-fax">
              <td class="text-left vMid">Fax (Office) <span class='text-red'>*</span></b></td>
              <td class="text-left">
                <?php
                  echo form_input(array('type'=>'text','id'=>'fax','name'=>'fax','class'=>'form-control input-sm w30', 'placeholder'=>'Fax','autocomplete'=>'off','value'=>empty($getC->fax)?'':$getC->fax))
                ?>
                <label class="label label-danger fax hideIt">Fax (Office) Can't be empty!</label>
              </td>
            </tr>

            <tr id="my-grid-tr-npwp">
              <td class="text-left vMid">NPWP/PKP Number</td>
              <td class="text-left">
                <?php
                echo form_input(array('type'=>'text','id'=>'npwp','name'=>'npwp','class'=>'form-control input-sm w50', 'placeholder'=>'NPWP/PKP Number','autocomplete'=>'off','value'=>empty($getC->npwp)?'':$getC->npwp))
                ?>
                <label class="label label-danger npwp hideIt">NPWP/PKP Number Can't be empty!</label>
              </td>
            </tr>

            <tr id="my-grid-tr-npwp_address">
              <td class="text-left vMid">NPWP/PKP Address</td>
              <td class="text-left">
                <?php
                echo form_input(array('type'=>'text','id'=>'npwp_address','name'=>'npwp_address','class'=>'form-control input-sm w50', 'placeholder'=>'NPWP/PKP Address','autocomplete'=>'off','value'=>empty($getC->npwp_address)?'':$getC->npwp_address))
                ?>
                <label class="label label-danger npwp_address hideIt">NPWP/PKP Address Can't be empty!</label>
              </td>
            </tr>

            <tr id="my-grid-tr-owner">
              <td class="text-left vMid">Owner</td>
              <td class="text-left">
                <?php
                echo form_input(array('type'=>'text','id'=>'owner','name'=>'owner','class'=>'form-control input-sm w50', 'placeholder'=>'Owner','autocomplete'=>'off','value'=>empty($getC->owner)?'':$getC->owner))
                ?>
                <label class="label label-danger owner hideIt">Owner Can't be empty!</label>
              </td>
            </tr>

            <tr id="my-grid-tr-remarks">
              <td class="text-left vMid">Remarks</td>
              <td class="text-left">
                <?php
                echo form_input(array('type'=>'text','id'=>'remarks','name'=>'remarks','class'=>'form-control input-sm w50', 'placeholder'=>'Remarks','autocomplete'=>'off','value'=>empty($getC->remarks)?'':$getC->remarks))
                ?>
                <label class="label label-danger remarks hideIt">Remarks Can't be empty!</label>
              </td>
            </tr>

            <tr id="my-grid-tr-agent_name">
              <td class="text-left vMid">Agent Name</td>
              <td class="text-left">
                <?php
                echo form_input(array('type'=>'text','id'=>'agent_name','name'=>'agent_name','class'=>'form-control input-sm w50', 'placeholder'=>'Agent Name','autocomplete'=>'off','value'=>empty($getC->agent_name)?'':$getC->agent_name))
                ?>
                <label class="label label-danger agent_name hideIt">Agent Name Can't be empty!</label>
              </td>
            </tr>

            <tr id="my-grid-tr-vat_name">
              <td class="text-left vMid">VAT Name</td>
              <td class="text-left">
                <?php
                echo form_input(array('type'=>'text','id'=>'vat_name','name'=>'vat_name','class'=>'form-control input-sm w50', 'placeholder'=>'VAT Name','autocomplete'=>'off','value'=>empty($getC->vat_name)?'':$getC->vat_name))
                ?>
                <label class="label label-danger vat_name hideIt">VAT Name Can't be empty!</label>
              </td>
            </tr>

            <tr id="my-grid-tr-email">
              <td class="text-left vMid">E-Mail</td>
              <td class="text-left">
                <?php
                echo form_input(array('type'=>'text','id'=>'email','name'=>'email','class'=>'form-control input-sm w50', 'placeholder'=>'Company E-Mail','autocomplete'=>'off','value'=>empty($getC->email)?'':$getC->email))
                ?>
                <label class="label label-danger email hideIt">E-Mail Can't be empty!</label>
              </td>
            </tr>

            <tr id="my-grid-tr-note">
              <td class="text-left vMid">Note</td>
              <td class="text-left">
                <textarea name="note" id="note" rows="6" cols="30" class="form-control textarea"></textarea>
                <label class="label label-danger note hideIt">Note Can't be empty!</label>
              </td>
            </tr>

            <tr id="my-grid-tr-activation">
              <td class="text-left vMid">Status</td>
              <td class="text-left">
                <select class="form-control select2" name="activation" id="activation" style="width:40%">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
                <label class="label label-danger activation hideIt">Status Can't be empty!</label>
              </td>
            </tr>

          </tbody>
          <tr style='background-color: #175477; color: white; font-size: 15px;'>
            <th class="text-center" colspan='3'>PIC</th>
          </tr>
          <tfoot id="tfoot-pic">
            <?php $no=0; foreach ($getSP as $key => $vp): $no++;?>
              <tr>
                <td>
                  <a class="text-red hapus_item_js" href="javascript:void(0)" title="Delete List"><i class="fa fa-times"></i></a> || <span class="numbering"><?=$no?></span>
                  <input type="text" name="pic[]"  class="form-control input-sm " required="" placeholder="Input PIC Name" style="width:75%;text-align:center;display:inline-block">
                </td>
                <td>
                  <input type="text" name="pic_phone[]"  class="form-control input-sm inSp2 " required="" placeholder="Input PIC Number">
                  <input type="text" name="pic_email[]"  class="form-control input-sm inSp2 " required="" placeholder="Input PIC Email">
                </td>
              </tr>
            <?php endforeach; ?>
          </tfoot>
        </table>
        <br>
        <a class="btn btn-sm btn-success" id="addPIC">Add PIC</a>
        <br>
        <br>
      </div>
      <div class="col-sm-12 col-md-6 col-lg-6">
        <table id="my-grid2" class="table table-striped table-bordered table-hover table-condensed" width="100%">
          <thead>
            <tr style='background-color: #175477; color: white; font-size: 15px;'>
              <th class="text-center" colspan='3'>DETAIL PRODUCTIVITY</th>
            </tr>
          </thead>

          <tbody>
            <tr>
              <td class="text-left vMid">Supplier Shipping</td>
              <td class="text-left">
                <label class="checkbox-inline"><input type="radio" name="supplier_shipping" value="Import" class="radioShipping"> Import</label>
                <label class="checkbox-inline"><input type="radio" name="supplier_shipping" value="Local" class="radioShipping"> Local</label>
              </td>
            </tr>

            <tr>
              <td class="text-left vMid">Supplier Type</td>
              <td class="text-left">
                <select class="form-control input-sm select2 w50" name="id_type" id="id_type">

                </select>
              </td>
            </tr>

            <tr>
              <td class="text-left vMid">Product Category</td>
              <td class="text-left">
                <select class="form-control input-sm select2 w50" name="id_category" id="id_category">

                </select>
              </td>
            </tr>

            <tr id="my-grid-tr-website">
              <td class="text-left vMid">Website</td>
              <td class="text-left">
                <?php
                echo form_input(array('type'=>'text','id'=>'website','name'=>'website','class'=>'form-control input-sm w50', 'placeholder'=>'Web Address','autocomplete'=>'off','value'=>empty($getC->website)?'':$getC->website))
                ?>
                <label class="label label-danger website hideIt">Website Can't be empty!</label>
              </td>
            </tr>

            <tr id="my-grid-tr-input_date">
              <td class="text-left vMid">Input Date</td>
              <td class="text-left">
                <?php
                echo form_input(array('type'=>'text','id'=>'input_date','name'=>'input_date','class'=>'form-control input-sm datepicker w50', 'placeholder'=>'Input Date','autocomplete'=>'off','value'=>empty($getC->input_date)?date("Y-m-d"):$getC->input_date))
                ?>
                <label class="label label-danger input_date hideIt">Input Date Can't be empty!</label>
              </td>
            </tr>

            <tr>
              <td class="text-left vMid">Capacity</td>
              <td class="text-left">
                <select class="form-control input-sm select2 w50" name="id_capacity" id="id_capacity">

                </select>
                <label class="label label-danger id_capacity hideIt">Capacity Can't be empty!</label>
              </td>
            </tr>

            <tr>
              <td class="text-left vMid">Business Category</td>
              <td class="text-left">
                <select class="form-control input-sm select2 w50" name="id_business" id="id_business">

                </select>
                <label class="label label-danger id_business hideIt">Business Category Can't be empty!</label>
              </td>
            </tr>

            <tr id="my-grid-tr-purchase_limit">
              <td class="text-left vMid">Amount purchase limit</td>
              <td class="text-left">
                <?php
                echo form_input(array('type'=>'text','id'=>'purchase_limit','name'=>'purchase_limit','class'=>'form-control input-sm numberOnly w50', 'placeholder'=>'Web Address','autocomplete'=>'off','value'=>empty($getC->purchase_limit)?'':$getC->purchase_limit))
                ?>
                <label class="label label-danger purchase_limit hideIt">Amount purchase limit Can't be empty!</label>
              </td>
            </tr>

            <tr id="my-grid-tr-payment_remark">
              <td class="text-left vMid">Payment remark</td>
              <td class="text-left">
                <?php
                echo form_input(array('type'=>'text','id'=>'payment_remark','name'=>'payment_remark','class'=>'form-control input-sm numberOnly w50', 'placeholder'=>'Web Address','autocomplete'=>'off','value'=>empty($getC->payment_remark)?'':$getC->payment_remark))
                ?>
                <label class="label label-danger payment_remark hideIt">Payment remark Can't be empty!</label>
              </td>
            </tr>

            <tr>
              <td class="text-left vMid">Brand</td>
              <td class="text-left">
                <a id="selBrand" <?=empty($getC->id_brand)?'data-id_supplier=""':'data-id_supplier="'.$getC->id_supplier.'"'?> class="btn btn-sm btn-success"><i class="fa fa-plus"></i> Select Brand</a>
                <input type="hidden" id="id_brand" name="id_brand" value="<?=empty($getC->id_brand)?'':$getC->id_brand?>">
              </td>
            </tr>

            <tr>
              <td class="text-left vMid">Term of price</td>
              <td class="text-left">
                <select class="form-control input-sm select2 w50" name="term_of_price" id="term_of_price">

                </select>
                <label class="label label-danger term_of_price hideIt">Term of price Can't be empty!</label>
              </td>
            </tr>

            <tr>
              <td class="text-left vMid">Level</td>
              <td class="text-left">
                <label class="checkbox-inline" data-toggle="tooltip" data-placement="right" data-custom-class="tooltip-primary" title="Standard Waste : 10%"><input type="radio" name="level" value="1" class="radioLevel"> <i class="fa fa-star"></i></label><br>
                <label class="checkbox-inline" data-toggle="tooltip" data-placement="right" data-custom-class="tooltip-primary" title="Standard Waste : 7.5%"><input type="radio" name="level" value="2" class="radioLevel"> <i class="fa fa-star"></i><i class="fa fa-star"></i></label><br>
                <label class="checkbox-inline" data-toggle="tooltip" data-placement="right" data-custom-class="tooltip-primary" title="Standard Waste : 7.5%"><input type="radio" name="level" value="3" class="radioLevel"> <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></label><br>
                <label class="checkbox-inline" data-toggle="tooltip" data-placement="right" data-custom-class="tooltip-primary" title="Standard Waste : 5%"><input type="radio" name="level" value="3" class="radioLevel"> <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></label><br>
                <label class="checkbox-inline" data-toggle="tooltip" data-placement="right" data-custom-class="tooltip-primary" title="Standard Waste : 5%"><input type="radio" name="level" value="3" class="radioLevel"> <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></label><br>
              </td>
            </tr>

            <tr id="my-grid-tr-activation">
              <td class="text-left vMid">Payment Option</td>
              <td class="text-left">
                <select class="form-control select2 w50" name="payment_option" id="payment_option">
                  <option value="cash">Cash</option>
                  <option value="credit">Credit</option>
                  <option value="transfer">Transfer</option>
                </select>
                <label class="label label-danger payment_option hideIt">Payment Option Can't be empty!</label>
                <br>
                <div class="hideIt" id="credit_term">
                  <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-12">
                      Term of Payment for First Order
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12">
                      <?php
                      echo form_input(array('type'=>'text','id'=>'fo_order','name'=>'fo_order','class'=>'form-control input-sm numberOnly', 'placeholder'=>'Term of Payment for First Order','autocomplete'=>'off','value'=>empty($getC->fo_order)?'':$getC->fo_order))
                      ?>
                    </div>
                  </div>
                  <div class="row">
                  <div class="col-lg-6 col-md-6 col-sm-12">
                    Term of Payment for Repeat Order
                  </div>
                  <div class="col-lg-6 col-md-6 col-sm-12">
                    <?php
                    echo form_input(array('type'=>'text','id'=>'ro_order','name'=>'ro_order','class'=>'form-control input-sm numberOnly', 'placeholder'=>'Term of Payment for Repeat Order','autocomplete'=>'off','value'=>empty($getC->ro_order)?'':$getC->ro_order))
                    ?>
                  </div>
                </div>
                </div>
              </td>
            </tr>

            <tr>
              <td class="text-left vMid">Shipping Term</td>
              <td class="text-left">
                <textarea name="shipping_term" rows="3" cols="50" class="form-control textarea"></textarea>
              </td>
            </tr>

            <tr>
              <td class="text-left vMid">Note</td>
              <td class="text-left">
                <textarea name="note" rows="3" cols="50" class="form-control textarea"></textarea>
              </td>
            </tr>

            <tr>
              <td class="text-left vMid">Status</td>
              <td class="text-left">
                <select class="form-control select2" name="activation" style="width:40%">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </td>
            </tr>

          </tbody>
        </table>
        <br>
        <div class="row">
          <div class="col-sm-12" style='background-color: #175477; color: white; font-size: 15px; text-align:center; padding:1%; width:95%; margin-left:2.5%'>
            <strong>
              Bank Data
            </strong>
          </div>
        </div>
        <div class="row" style='padding:1%; width:95%; margin-left:2.5%'>
          <div class="col-lg-3 col-md-3">
            Bank Name
          </div>
          <div class="col-lg-3 col-md-3">
            A/C No.
          </div>
          <div class="col-lg-3 col-md-3">
            Bank Code
          </div>
          <div class="col-lg-3 col-md-3">
            A/C Name
          </div>
        </div>
        <?php $no=0; foreach ($getSB as $key => $vb): $no++;?>
          <div class="row">
            <div class="col-lg-3 col-md-3">
              Bank Name
            </div>
            <div class="col-lg-3 col-md-3">
              A/C No.
            </div>
            <div class="col-lg-3 col-md-3">
              Bank Code
            </div>
            <div class="col-lg-3 col-md-3">
              A/C Name
            </div>
          </div>
        <?php endforeach; ?>
        <!--<table>
          <thead id="thead-bank1">
            <?php $no=0; foreach ($getSB as $key => $vb): $no++;?>
              <tr>
                <td></td>
                <a class="text-red hapus_item_js" href="javascript:void(0)" title="Delete List"><i class="fa fa-times"></i></a> || <span class="numbering"><?=$no?></span>
                <td colspan="2">
                  <input type="text" name="bank[]"  class="form-control input-sm " required="" placeholder="Input Bank Name" style="width:75%;text-align:center;display:inline-block" value="<?=$vb->bank_name?>">
                  <input type="text" name="bank_code[]"  class="form-control input-sm inSp2 " required="" placeholder="Input PIC Number" value="<?=$vb->bank_code?>">
                  <input type="text" name="bank_number[]"  class="form-control input-sm inSp2 " required="" placeholder="Input PIC Email" value="<?=$vb->bank_number?>">
                </td>
              </tr>
            <?php endforeach; ?>
          </thead>
        </table>-->

        <br>
        <a class="btn btn-sm btn-success" id="addBankList">Add Bank List</a>
        <br>
        <br>
      </div>
    </div>

		<?php
			echo form_button(array('type'=>'button','class'=>'btn btn-md btn-success','style'=>'min-width:100px; float:right;','value'=>'save','content'=>'Save','id'=>'addSupplierSave')).' ';
		?>
	</div>
</div>
</form>

<style>
	.inSp{
		text-align: center;
		display: inline-block;
		width: 100px;
	}
	.inSp2{
		text-align: center;
		display: inline-block;
		width: 45%;
	}
	.inSpL{
		text-align: left;
	}
	.vMid{
		vertical-align: middle !important;
	}
  .w10{
		display: inline-block;
		width: 10%;
	}
  .w20{
		display: inline-block;
		width: 20%;
	}
  .w30{
		display: inline-block;
		width: 30%;
	}
  .w40{
		display: inline-block;
		width: 40%;
	}
  .w50{
		display: inline-block;
		width: 50%;
	}
  .w60{
		display: inline-block;
		width: 60%;
	}
  .w70{
		display: inline-block;
		width: 70%;
	}
  .w80{
		display: inline-block;
		width: 80%;
	}
  .w90{
		display: inline-block;
		width: 90%;
	}
  .hideIt{
    display: none;
  }
  .showIt{
    display: block;
  }

</style>

<script type="text/javascript">

	$(document).ready(function(){

    getCountry();
    getSupplierType();

    $('#addPIC').click(function(e){
      var x = parseInt(document.getElementById("tfoot-pic").rows.length)+1;
      console.log(x);
      var row = '<tr class="addjs">'+
            '<td style="background-color:#E0E0E0">'+
            '<a class="text-red hapus_item_js" href="javascript:void(0)" title="Delete List"><i class="fa fa-times"></i></a> || <span class="numbering">'+
            x+'</span> '+
            '<input type="text" name="pic[]"  class="form-control input-sm " required="" placeholder="Input PIC Name" style="width:75%;text-align:center;display:inline-block">'+
						/*'<select name="step[]" id="step[]" class="chosen_select form-control inline-block select_step">'+
							'<option value="0">Select Step</option>'+
						'<?php foreach($getStep AS $val => $valx){ ?>'+
								'<option value="<?=$valx["step_name"]?>"><?=strtoupper($valx["step_name"])?></option>'+
						'<?php } ?>'+
						'</select>'+*/
            '</td>'+
            '<td style="background-color:#E0E0E0;text-align:center">'+
            '<input type="text" name="pic_phone[]"  class="form-control input-sm inSp2 " required="" placeholder="Input PIC Number">'+
            '<input type="text" name="pic_email[]"  class="form-control input-sm inSp2 " required="" placeholder="Input PIC Email">'+
            '</td>'+
        '</tr>'

      $('#tfoot-pic').append(row);

    });
    $('#addBankList').click(function(e){
      var x = parseInt(document.getElementById("tfoot-pic").rows.length)+1;
      console.log(x);
      var row = '<tr class="addjs">'+
            '<td style="background-color:#E0E0E0">'+
            '<a class="text-red hapus_item_js" href="javascript:void(0)" title="Delete List"><i class="fa fa-times"></i></a> || <span class="numbering">'+
            x+'</span> '+
            '<input type="text" name="pic[]"  class="form-control input-sm " required="" placeholder="Input PIC Name" style="width:75%;text-align:center;display:inline-block">'+
						/*'<select name="step[]" id="step[]" class="chosen_select form-control inline-block select_step">'+
							'<option value="0">Select Step</option>'+
						'<?php foreach($getStep AS $val => $valx){ ?>'+
								'<option value="<?=$valx["step_name"]?>"><?=strtoupper($valx["step_name"])?></option>'+
						'<?php } ?>'+
						'</select>'+*/
            '</td>'+
            '<td style="background-color:#E0E0E0;text-align:center">'+
            '<input type="text" name="pic_phone[]"  class="form-control input-sm inSp2 " required="" placeholder="Input PIC Number">'+
            '<input type="text" name="pic_email[]"  class="form-control input-sm inSp2 " required="" placeholder="Input PIC Email">'+
            '</td>'+
        '</tr>'

      $('#tfoot-pic').append(row);

    });

    //REMOVE LIST BUTTON
    $('#tfoot-pic').on( 'click', 'a.hapus_item_js', function () {
			console.log('a');
      $(this).parents('tr').remove();
      if (parseInt(document.getElementById("tfoot-pic").rows.length) == 0) {
        var x=1;
      }else {
        var x = parseInt(document.getElementById("tfoot-pic").rows.length)+1;
      }
      for (var i = 0; i < x; i++) {
        $('.numbering').eq(i-1).text(i);
      }
    } );


    $(".datepicker").datepicker({
        format : "yyyy-mm-dd",
        showInputs: true,
        autoclose:true
    });

    $(".select2").select2({
      placeholder: "Choose One Option",
      allowClear: true
    });

    $(document).on('click', '#addSupplierSave', function(e){
      var formdata = $("#form-supplier").serialize();
      $.ajax({
        url: siteurl+active_controller+"saveSupplier",
        dataType : "json",
        type: 'POST',
        data: formdata,
        success: function(result){
          //console.log(result['msg']);
          if(result.status=='1'){
            swal({
              title: "Sukses!",
              text: result['pesan'],
              type: "success",
              timer: 1500,
              showConfirmButton: false
            });
            setTimeout(function(){
              DataTables('set');
              if (($("#ModalView3").data('bs.modal') || {}).isShown) {
                $("#ModalView3").modal('hide');
              }else {
                $("#ModalView").modal('hide');
              }

            },1600);
          } else {
            swal({
              title: "Gagal!",
              text: result['pesan'],
              type: "error",
              timer: 1500,
              showConfirmButton: false
            });
          };
        },
        error: function (request, error) {
          console.log(arguments);
          alert(" Can't do because: " + error);
        }
      });
      //$("#ModalView").modal('hide');
    });

    $(document).on('click', '#saveSelBrand', function(e){
      var formdata = $("#form-selBrand").serialize();
      var selected = '';
      $('#my-grid input:checked').each(function() {
          //selected.push($(this).val());
          selected = selected+$(this).val()+';';
      });
      //console.log(selected);
      $('#id_brand').val(selected);
      $("#ModalView2").modal('hide');
    });
    $(document).on('click', '#generate_id', function(e){
      var name = $('#nm_supplier').val();
      if (name != '') {
        $.ajax({
          url: siteurl+active_controller+"getID",
          dataType : "json",
          type: 'POST',
          data: {nm:name},
          success: function(result){
            $('#id_supplier').val(result.id);
          },
          error: function (request, error) {
            console.log(arguments);
            alert(" Can't do because: " + error);
          }
        });
      }else {
        swal({
          title: "Warning!",
          text: "Complete Supplier name first, please!",
          type: "warning",
          timer: 3500,
          showConfirmButton: false
        });
      }

    });
    $(document).on('click', '.radioShipping', function(e){
      var id_category = $('#id_category').val();
      var val = $(this).val();
      if (val != '') {
        $.ajax({
          url: siteurl+active_controller+"getProductCatOpt",
          dataType : "json",
          type: 'POST',
          data: {id_category:id_category,supplier_shipping:val},
          success: function(result){
            $('#id_category').html(result.html);
          },
          error: function (request, error) {
            console.log(arguments);
            alert(" Can't do because: " + error);
          }
        });
      }else {
        swal({
          title: "Warning!",
          text: "Complete Supplier name first, please!",
          type: "warning",
          timer: 3500,
          showConfirmButton: false
        });
      }

    });
    $(document).on('change', '#payment_option', function(e){
      var val = $(this).val();
      if (val == 'credit') {
        $('#credit_term').css({"display": "block"}).fadeIn(1000);
      }else {
        $('#credit_term').fadeOut(1000).css({"display": "none"});
      }

    });

	});

	$(".numberOnly").on("keypress keyup blur",function (event) {
		if ((event.which < 48 || event.which > 57 ) && (event.which < 46 || event.which > 46 )) {
			event.preventDefault();
		}
	});

  function getCountry() {
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
  function getSupplierType() {
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
</script>
