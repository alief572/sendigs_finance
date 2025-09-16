
<div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post" autocomplete="off"><br>
		<div class="form-group row">
        	<div class="col-md-12">
				<table width='50%'>
					<tr>
						<td width='20%'>Sales Order </td>
						<td width='1%'>:</td>
						<td><?=$getData[0]['so_number'];?></td>
					</tr>
					<tr>
						<td>Project</td>
						<td>:</td>
						<td><?=strtoupper($getData[0]['project']);?></td>
					</tr>
                    <tr>
						<td>Product Custom</td>
						<td>:</td>
						<td><?=strtoupper($getDataReq[0]['nama_product']);?></td>
					</tr>
					<tr>
						<td>Due Date</td>
						<td>:</td>
						<td><?=date('d-M-Y',strtotime($getData[0]['due_date']));?></td>
					</tr>
				</table>
				<input type="hidden" id='id' name='id' value='<?=$getData[0]['id']?>'>
			</div>
        </div>
		<h4>Request Single Product</h4>
		<div class="form-group row">
        	<div class="col-md-12">
				<table class="table table-bordered table-striped" width='100%'>
					<tr>
						<th class='text-center' width='5%'>#</th>
						<th class='text-center'>Nama Product</th>
						<th class='text-center' width='10%'>Qty Kebutuhan</th>
						<th class='text-center' width='10%'>Sudah Request</th>
						<th class='text-center' width='10%'>Qty Request</th>
						<!-- <th class='text-center' width='10%'>Option</th> -->
					</tr>
					<?php
					$nomor = 0;
					foreach ($reqSingleProduct as $key => $value) { $nomor++;
						$ID 		= $value['id'];
						$qty_sisa 	= $value['qty'] - $value['qty_req'];
						echo "<tr>";
							echo "<td class='text-center'>".$nomor."
								<input type='hidden' name='single[".$nomor."][id]' value='".$ID."'>
								<input type='hidden' name='single[".$nomor."][sisa]' id='sisa".$ID."' value='".$qty_sisa."'>
								</td>";
							echo "<td>".get_name_product_by_bom($value['layer'])[$value['layer']]."</td>";
							echo "<td class='text-center'>".number_format($value['qty'])."</td>";
							echo "<td class='text-center'>".number_format($value['qty_req'])."</td>";
							echo "<td class='text-center'><input type='text' class='form-control text-center autoNumeric0 qtyReq' data-id='".$ID."' name='single[".$nomor."][qty_req]'></td>";
							// echo "<td class='text-center'><button type='button' class='btn btn-sm btn-primary'>Request</button></td>";
						echo "</tr>";
					}
					?>
				</table>
			</div>
        </div>
		<h4>Request Cutting Product</h4>
		<div class="form-group row">
        	<div class="col-md-12">
				<table class="table table-bordered table-striped" width='100%'>
					<tr>
						<th class='text-center' width='5%'>#</th>
						<th class='text-center'>Nama Product</th>
						<th class='text-center' width='15%'>Spec</th>
						<th class='text-center' width='10%'>Qty Kebutuhan</th>
						<th class='text-center' width='10%'>Sudah Request</th>
						<th class='text-center' width='10%'>Qty Request</th>
						<!-- <th class='text-center' width='10%'>Option</th> -->
					</tr>
					<?php
					$nomor = 0;
					foreach ($reqCuttingProduct as $key => $value) { $nomor++;
						$ID 		= $value['id'];
						$qty_sisa 	= $value['qty'] - $value['qty_req'];
						echo "<tr>";
							echo "<td class='text-center'>".$nomor."
							<input type='hidden' name='cutting[".$nomor."][id]' value='".$ID."'>
							<input type='hidden' name='cutting[".$nomor."][sisa]' id='sisa".$ID."' value='".$qty_sisa."'>
							</td>";
							echo "<td>".get_name_product_by_bom($value['layer'])[$value['layer']]."</td>";
							echo "<td class='text-center'>".number_format($value['length'])." x ".number_format($value['width'])."</td>";
							echo "<td class='text-center'>".number_format($value['qty'])."</td>";
							echo "<td class='text-center'>".number_format($value['qty_req'])."</td>";
							echo "<td class='text-center'><input type='text' class='form-control text-center autoNumeric0 qtyReq' data-id='".$ID."' name='cutting[".$nomor."][qty_req]'></td>";
							// echo "<td class='text-center'><button type='button' class='btn btn-sm btn-primary'>Request</button></td>";
						echo "</tr>";
					}
					?>
				</table>
			</div>
        </div>
		<div class="form-group row">
			<div class="col-md-6">
				<button type="button" class="btn btn-primary" name="save" id="save">Request</button>
				<button type="button" class="btn btn-danger" style='margin-left:5px;' name="back" id="back">Back</button>
			</div>
        </div>

      	
		</form>
	</div>
</div>

<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" style='width:70%;'>
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;Detail Data</h4>
      </div>
      <div class="modal-body" id="ModalView">
		...
      </div>
  </div>
</div>


<script src="<?= base_url('assets/js/jquery.maskMoney.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>
<style>
    .datepicker, .datepicker2{
        cursor: pointer;
    }
</style>

<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';

	$(document).ready(function(){
		$('.chosen-select').select2();
    	$('.autoNumeric0').autoNumeric('init', {mDec: '0', aPad: false})

    	//back
		$(document).on('click', '#back', function(){
		    window.location.href = base_url + active_controller
		});

		$(document).on('keyup', '.qtyReq', function(){
			let noData 		= $(this).data('id');
			let qtySisa 	= getNum($('#sisa'+noData).val());
			let qtyRequest 	= getNum($(this).val().split(',').join(''))

			if(qtyRequest > qtySisa){
				$(this).val(qtySisa)
			}
		});

		$('#save').click(function(e){
			e.preventDefault();
			
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
						var baseurl=siteurl+active_controller+'/add';
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
										  timer	: 7000
										});
										// window.open(base_url + active_controller+'/print_spk/'+data.kode,'_blank');
										window.location.href = base_url + active_controller
								}else{

									if(data.status == 2){
										swal({
										  title	: "Save Failed!",
										  text	: data.pesan,
										  type	: "warning",
										  timer	: 7000
										});
									}else{
										swal({
										  title	: "Save Failed!",
										  text	: data.pesan,
										  type	: "warning",
										  timer	: 7000
										});
									}

								}
							},
							error: function() {

								swal({
								  title				: "Error Message !",
								  text				: 'An Error Occured During Process. Please try again..',
								  type				: "warning",
								  timer				: 7000
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

	function number_format (number, decimals, dec_point, thousands_sep) {
		// Strip all characters but numerical ones.
		number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
		var n = !isFinite(+number) ? 0 : +number,
			prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
			sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
			dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
			s = '',
			toFixedFix = function (n, prec) {
				var k = Math.pow(10, prec);
				return '' + Math.round(n * k) / k;
			};
		// Fix for IE parseFloat(0.55).toFixed(0) = 0;
		s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
		if (s[0].length > 3) {
			s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
		}
		if ((s[1] || '').length < prec) {
			s[1] = s[1] || '';
			s[1] += new Array(prec - s[1].length + 1).join('0');
		}
		return s.join(dec);
	}


</script>
