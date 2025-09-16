<?php

$no_so          = (!empty($header))?$header[0]->no_so:'';
$code_cust      = (!empty($header))?$header[0]->code_cust:'';
$delivery_date  = (!empty($header))?date('d-m-Y',strtotime($header[0]->delivery_date)):'';
$shippingx      = (!empty($header))?$header[0]->shipping:'';
$no_so_manual   = (!empty($header))?$header[0]->no_so_manual:'';
$shipment       = (!empty($header))?$header[0]->shipment:'';
$no_po          = (!empty($header))?$header[0]->no_po:'';

// print_r($header);
?>

 <div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post" autocomplete="off">
			<div class="form-group row">
				<div class="col-md-2">
					<label for="customer">Customer Name</label>
				</div>
				<div class="col-md-4">
					<select id="code_cust" name="code_cust" class="form-control input-md chosen-select" required>
						<option value="0">Select An Option</option>
						<?php foreach ($results['customer'] as $customer){
              $sel = ($customer->id_customer == $code_cust)?'selected':'';
						?>
						<option value="<?= $customer->id_customer;?>" <?=$sel;?>><?= strtoupper(strtolower($customer->name_customer))?></option>
						<?php } ?>
					</select>
				</div>
        </div>
        <div class="form-group row">
  				<div class="col-md-2">
  					<label for="customer">Delivery Date</label>
  				</div>
  				<div class="col-md-4">
            <input type="text" id="delivery_date" name="delivery_date" class="form-control input-md datepicker" placeholder="Delivery Date" readonly value='<?=$delivery_date;?>'>
            <input type="hidden" id="no_so" name="no_so" class="form-control input-md" placeholder="No SO" readonly  value='<?=$no_so;?>'>
  				</div>
        </div>
        <div class="form-group row">
    				<div class="col-md-2">
    					<label for="customer">Delivery Methode</label>
    				</div>
    				<div class="col-md-4">
    					<select id="shipping" name="shipping" class="form-control input-md chosen-select" required>
    						<option value="0">Select An Option</option>
    						<?php foreach ($results['shipping'] as $shipping){
                  $sel2 = ($shipping->value == $shippingx)?'selected':'';
    						?>
    						<option value="<?= $shipping->value;?>" <?=$sel2;?>><?= strtoupper(strtolower($shipping->view))?></option>
    						<?php } ?>
    					</select>
    				</div>
            <div class="col-md-2">
     					<label for="customer">No Shipment</label>
     				</div>
     				<div class="col-md-4">
               <input type="text" id="shipment" name="shipment" class="form-control input-md" placeholder="No Shipment" value='<?=$shipment;?>'>
            </div>
			   </div>
         <div class="form-group row">
   				<div class="col-md-2">
   					<label for="customer">No SO Manual</label>
   				</div>
   				<div class="col-md-4">
             <input type="text" id="no_so_manual" name="no_so_manual" class="form-control input-md" placeholder="No SO Manual" value='<?=$no_so_manual;?>'>
          </div>
          <div class="col-md-2">
   					<label for="customer">No PO</label>
   				</div>
   				<div class="col-md-4">
             <input type="text" id="no_po" name="no_po" class="form-control input-md" placeholder="No PO" value='<?=$no_po;?>'>
          </div>
         </div>

			<br>
			<div class='box box-info'>
				<div class='box-header'>
					<h3 class='box-title'>Detail Product</h3>
					<div class='box-tool pull-right'>
						<!--<button type='button' data-id='frp_".$a."' class='btn btn-md btn-info panelSH'>SHOW</button>-->
					</div>
				</div>
				<div class='box-body hide_header'>
					<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class='text-center' style='width: 4%;'>#</th>
								<th class='text-center' style='width: 40%;'>Product Name</th>
								<th class='text-center'>Qty Propose</th>
                <th class='text-center'>Qty Order</th>
                <th class='text-center'>Qty Balance</th>
								<th class='text-center' style='width: 4%;'>#</th>
							</tr>
						</thead>
						<tbody>
              <?php
              $val = 0;
              if(!empty($detail)){
    						foreach($detail AS $val => $valx){ $val++;
                  echo "<tr class='header_".$val."'>";
                    echo "<td align='center'>".$val."</td>";
                    echo "<td align='left'>";
                    echo "<select name='Detail[".$val."][product]' data-no='".$val."' class='chosen-select form-control input-sm inline-blockd product'>";
                    echo "<option value='0'>Select Product Name</option>";
                    foreach($product AS $valx4){
                      $sel2 = ($valx4->id_category2 == $valx['product'])?'selected':'';
                      echo "<option value='".$valx4->id_category2."' ".$sel2.">".strtoupper($valx4->nama)."</option>";
                    }
                    echo 		"</select>";
                    echo "</td>";
                    echo "<td align='left'>";
                    echo "<input type='text' name='Detail[".$val."][qty_order]' class='form-control input-md maskM qty' placeholder='Qty Propose' value='".$valx['qty_order']."' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
                    echo "</td>";
                    echo "<td align='left'>";
                    echo "<input type='text' name='Detail[".$val."][qty_propose]' class='form-control input-md maskM qty' placeholder='Qty Order' value='".$valx['qty_propose']."' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
                    echo "</td>";
                    echo "<td align='left'>";
                    echo "<input type='text' name='Detail[".$val."][qty_balance]' class='form-control input-md text-center maskM qty' readonly placeholder='Qty Balance' value='".get_balance($valx['product'], $code_cust)."' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
                    echo "</td>";
                    echo "<td align='left'>";
                    echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
                    echo "</td>";
                  echo "</tr>";
                }
              }
              else{

                foreach (get_product() as $key => $value) { $val++;
                  echo "<tr class='header_".$val."'>";
            				echo "<td align='center'>".$val."</td>";
            				echo "<td align='left'>";
                    echo "<input type='text' name='Detail[".$val."][product_name]' class='form-control input-md' value='".strtoupper($value['nama'])."' readonly>";
            				echo "</td>";
                    echo "<td align='left'>";
                    echo "<input type='hidden' name='Detail[".$val."][product]' class='form-control input-md' value='".$value['id_category2']."'>";
            				echo "<input type='text' name='Detail[".$val."][qty_order]' class='form-control input-md maskM qty' placeholder='Qty Propose' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
            				echo "</td>";
                    echo "<td align='left'>";
                    echo "<input type='text' name='Detail[".$val."][qty_propose]' class='form-control input-md maskM qty' placeholder='Qty Order' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
            				echo "</td>";
                    echo "<td align='left'>";
                    echo "<input type='text' name='Detail[".$val."][qty_balance]' id='balance_".$val."' class='form-control text-center input-md' value='".get_balance($value['id_category2'], 'CR0003')."' placeholder='Qty Balance' readonly data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
                    echo "</td>";
                    echo "<td align='center'>";
            				echo "<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part' disabled><i class='fa fa-close'></i></button>";
            				echo "</td>";
            			echo "</tr>";
                }
              }
              ?>
							<tr id='add_<?=$val?>'>
								<td align='center'></td>
								<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-warning addPart' title='Add Product'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Product</button></td>
								<td align='center'></td>
                <td align='center'></td>
								<td align='center'></td>
							</tr>
						</tbody>
					</table>
					<br>
          <button type="button" class="btn btn-danger" style='float:right; margin-left:5px;' name="back" id="back"><i class="fa fa-reply"></i> Back</button>
					<button type="submit" class="btn btn-primary" style='float:right;' name="save" id="save"><i class="fa fa-save"></i> Save</button>

				</div>
			</div>
		</form>
	</div>
</div>



<script src="<?= base_url('assets/js/jquery.maskMoney.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>
<!-- <script src="https://code.jquery.com/jquery-1.12.4.js"></script> -->
<!-- <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script> -->

<style media="screen">
  .datepicker{
    cursor: pointer;
    padding-left: 12px;
  }
</style>
<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';

	$(document).ready(function(){
    $('.maskM').maskMoney();
		$('.chosen-select').select2();
    $( ".datepicker" ).datepicker({
      dateFormat: "dd-mm-yy",
      changeMonth: true,
      changeYear: true,
      minDate: 0
    });

		//add part
		$(document).on('click', '.addPart', function(){
			// loading_spinner();
			var get_id 		= $(this).parent().parent().attr('id');
			// console.log(get_id);
			var split_id	= get_id.split('_');
			var id 		= parseInt(split_id[1])+1;
			var id_bef 	= split_id[1];

			$.ajax({
				url: base_url+'index.php/'+active_controller+'/get_add/'+id,
				cache: false,
				type: "POST",
				dataType: "json",
				success: function(data){
					$("#add_"+id_bef).before(data.header);
					$("#add_"+id_bef).remove();
					$('.chosen_select').select2({width: '100%'});
					$('.maskM').maskMoney();
					swal.close();
				},
				error: function() {
					swal({
						title				: "Error Message !",
						text				: 'Connection Time Out. Please try again..',
						type				: "warning",
						timer				: 3000,
						showCancelButton	: false,
						showConfirmButton	: false,
						allowOutsideClick	: false
					});
				}
			});
		});

    $(document).on('change', '.product', function(){
			// loading_spinner();
			var nomor 		 = $(this).data('no');
			var product	   = $(this).val();
      var code_cust	 = $('#code_cust').val();

			if(code_cust == '0' ){
				swal({
					title	: "Error Message!",
					text	: 'Customer name empty, select first ...',
					type	: "warning"
				});
				return false;
			}

			$.ajax({
				url: base_url + active_controller+'/get_balance/'+product+'/'+code_cust,
				cache: false,
				type: "POST",
				dataType: "json",
				success: function(data){
					$("#balance_"+nomor).val(data.balance);
					swal.close();
				},
				error: function() {
					swal({
						title				: "Error Message !",
						text				: 'Connection Time Out. Please try again..',
						type				: "warning",
						timer				: 3000,
						showCancelButton	: false,
						showConfirmButton	: false,
						allowOutsideClick	: false
					});
				}
			});
		});

	   //delete part
		$(document).on('click', '.delPart', function(){
			var get_id 		= $(this).parent().parent().attr('class');
			$("."+get_id).remove();
		});

    //add part
		$(document).on('click', '#back', function(){
		    window.location.href = base_url + active_controller +'/so';
		});

		$('#save').click(function(e){
			e.preventDefault();
			var code_cust		  = $('#code_cust').val();
      var delivery_date	= $('#delivery_date').val();
      var shipping		  = $('#shipping').val();
			var product	      = $('.product').val();
			var qty		        = $('.qty').val();

			if(code_cust == '0' ){
				swal({
					title	: "Error Message!",
					text	: 'Customer name empty, select first ...',
					type	: "warning"
				});

				$('#save').prop('disabled',false);
				return false;
			}
      if(delivery_date == '' ){
				swal({
					title	: "Error Message!",
					text	: 'Delivery date empty, select first ...',
					type	: "warning"
				});

				$('#save').prop('disabled',false);
				return false;
			}
      if(shipping == '0' ){
				swal({
					title	: "Error Message!",
					text	: 'Delivery Methode empty, select first ...',
					type	: "warning"
				});

				$('#save').prop('disabled',false);
				return false;
			}
			if(product == '0' ){
				swal({
					title	: "Error Message!",
					text	: 'Product name empty, select first ...',
					type	: "warning"
				});

				$('#save').prop('disabled',false);
				return false;
			}
      // if(qty == '' ){
			// 	swal({
			// 		title	: "Error Message!",
			// 		text	: 'Qty order empty, select first ...',
			// 		type	: "warning"
			// 	});
      //
			// 	$('#save').prop('disabled',false);
			// 	return false;
			// }

			swal({
				  title: "Are you sure?",
				  text: "You will not be able to process again this data!",
				  type: "warning",
				  showCancelButton: true,
				  confirmButtonClass: "btn-danger",
				  confirmButtonText: "Yes, Process it!",
				  cancelButtonText: "No, cancel process!",
				  closeOnConfirm: true,
				  closeOnCancel: false
				},
				function(isConfirm) {
				  if (isConfirm) {
						var formData 	=new FormData($('#data-form')[0]);
						var baseurl=siteurl+'sales/save_so';
						$.ajax({
							url			: baseurl,
							type		: "POST",
							data		: formData,
							cache		: false,
							dataType	: 'json',
							processData	: false,
							contentType	: false,
							success		: function(data){
								if(data.status == 1){
									swal({
										  title	: "Save Success!",
										  text	: data.pesan,
										  type	: "success",
										  timer	: 7000,
										  showCancelButton	: false,
										  showConfirmButton	: false,
										  allowOutsideClick	: false
										});
									window.location.href = base_url + active_controller+'/so';
								}else{

									if(data.status == 2){
										swal({
										  title	: "Save Failed!",
										  text	: data.pesan,
										  type	: "warning",
										  timer	: 7000,
										  showCancelButton	: false,
										  showConfirmButton	: false,
										  allowOutsideClick	: false
										});
									}else{
										swal({
										  title	: "Save Failed!",
										  text	: data.pesan,
										  type	: "warning",
										  timer	: 7000,
										  showCancelButton	: false,
										  showConfirmButton	: false,
										  allowOutsideClick	: false
										});
									}

								}
							},
							error: function() {

								swal({
								  title				: "Error Message !",
								  text				: 'An Error Occured During Process. Please try again..',
								  type				: "warning",
								  timer				: 7000,
								  showCancelButton	: false,
								  showConfirmButton	: false,
								  allowOutsideClick	: false
								});
							}
						});
				  } else {
					swal("Cancelled", "Data can be process again :)", "error");
					return false;
				  }
			});
		});

});

</script>
