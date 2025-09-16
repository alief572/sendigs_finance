
<div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post" autocomplete="off"><br>
		<div class="form-group row">
        	<div class="col-md-12">
				<table width='80%'>
					<tr>
						<td width='15%'>Sales Order </td>
						<td width='1%'>:</td>
						<td><?=$getDataCut[0]['so_number'];?></td>
					</tr>
					<tr>
						<td>No SPK</td>
						<td>:</td>
						<td><?=strtoupper($getData[0]['no_spk']);?></td>
					</tr>
					<tr>
						<td>Customer</td>
						<td>:</td>
						<td><?=strtoupper(get_name('customer','nm_customer','id_customer',$getDataCut[0]['id_customer']));?></td>
					</tr>
					<tr>
						<td>Nama Product</td>
						<td>:</td>
						<td><?=get_name_product_by_bom($getDataSPK[0]['layer'])[$getDataSPK[0]['layer']];?></td>
					</tr>
                    <tr>
						<td>Tanggal Out</td>
						<td>:</td>
						<td><?=date('d-M-Y');?></td>
					</tr>
					<tr>
						<td>Qty Request</td>
						<td>:</td>
						<td><?=number_format($getData[0]['qty']);?></td>
					</tr>
					<tr>
						<td>Ukuran Potong</td>
						<td>:</td>
						<td><?=number_format($getDataSPK[0]['length']);?> x <?=number_format($getDataSPK[0]['width']);?></td>
					</tr>
					<tr>
						<td class='text-bold'>Qty Sisa</td>
						<td class='text-bold'>:</td>
						<td class='text-bold'><?=number_format($getData[0]['qty']-$getData[0]['qty_out']);?></td>
					</tr>
					<tr hidden>
						<td>No Surat Jalan</td>
						<td>:</td>
						<td><input type="text" name='no_surat_jalan' id='no_surat_jalan' class='form-control input-sm text-left' style='width:300px;' value='<?=$getData[0]['no_surat_jalan'];?>'></td>
					</tr>
				</table>
				<input type="hidden" id='id' name='id' value='<?=$getData[0]['id'];?>'>
				<input type="hidden" id='so_number' name='so_number' value='<?=$getDataCut[0]['so_number'];?>'>
				<input type="hidden" id='no_delivery' name='no_delivery' value='<?=$getData[0]['kode'];?>'>
				<input type="hidden" id='qty_sisa' name='qty_sisa' value='<?=$getData[0]['qty']-$getData[0]['qty_out'];?>'>
			</div>
        </div>
		<!-- <div class="box box-primary">
			<div class="box-header with-border">
				<h3 class="box-title">SCAN QRCODE</h3>
			</div>
			<div class="box-body">
				<div class="form-group row">
					<div class="col-md-4">
						<div class="input-group">
							<span class="input-group-addon" style="padding: 4px 10px 0px 10px;">
								<i class="fa fa-qrcode fa-3x"></i>
							</span>
							<input type="text" name="qr_code" id="qr_code" class="form-control input-lg" placeholder="QR Code">
						</div>
					</div>
					<div class="col-md-8">
						<span id="help-text" class="text-success text-bold text-lg"></span>
						<div class="notif">
						</div>
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-4">
					<input type="checkbox" id="check_waste" name="check_waste" value="1">
					<label for="vehicle1"> Pakai Waste Product</label><br>
					</div>
				</div>
			</div>
		</div> -->
		<div class="box box-primary">
			<div class="box-header with-border">
				<h4 class="box-title">Qty Outgoing Product Cutting</h4>
			</div>
			<div class="box-body">
				<div class="form-group row">
					<div class="col-md-2">
						<input type="text" name="qty_outgoing" id="qty_outgoing" class="form-control input-md autoNumeric0" placeholder="Qty Outgoing">
					</div>
					<!-- <div class="col-md-8">
						<span id="help-text" class="text-success text-bold text-lg"></span>
						<div class="notif">
						</div>
					</div> -->
				</div>
				<!-- <div class="form-group row">
					<div class="col-md-4">
					<input type="checkbox" id="check_waste" name="check_waste" value="1">
					<label for="vehicle1"> Pakai Waste Product</label><br>
					</div>
				</div> -->
			</div>
		</div>
		<h4>List Product Outgoing</h4>
		<div class="form-group row">
        	<div class="col-md-12">
				<table class="table table-bordered table-striped" width='100%'>
					<thead>
                        <tr>
                            <th width='4%' class='text-center'>#</th>
                            <th>PRODUCT</th>
                            <!-- <th width='10%' class='text-center'>QTY SPK</th> -->
                            <th width='10%' class='text-center'>QTY OUTGOING</th>
							<th width='15%' class='text-center'>OUT DATE</th>
                        </tr>
                    </thead>
                    <tbody id='load-data'>
                        <?php
						if(!empty($getDetail)){
							foreach ($getDetail as $key => $value) { $key++;
								// $nama_product = (!empty($GET_DET_Lv4[$value['code_lv4']]['nama']))?$GET_DET_Lv4[$value['code_lv4']]['nama']:'';
								$nm_product = $value['nm_product'];
								echo "<tr class='tr_".$key."'>";
									echo "<td class='text-center'>".$key."</td>";
									echo "<td>".$nm_product."</td>";
									// echo "<td class='text-center'>".number_format($value['qty_spk'],2)."</td>";
									echo "<td class='text-center'>".number_format($value['qty_outgoing'],2)."</td>";
									echo "<td class='text-center'>".date('d-M-Y H:i',strtotime($value['created_date']))."</td>";
								echo "</tr>";
							}
						}
						$key = 1;
						$nm_product = get_name_product_by_bom($getDataSPK[0]['layer'])[$getDataSPK[0]['layer']];
						echo "<tr class='tr_".$key."' hidden>";
							echo "<td class='text-center'>".$key."</td>";
							echo "<td>".$nm_product."</td>";
							echo "<td class='text-center qtyBelumKirim'>".number_format($getData[0]['qty'],2)."</td>";
							echo "<td class='text-center' hidden>
									<input type='hidden' name='detail[".$key."][id_spk]' value='".$getData[0]['id']."'>
									<input type='hidden' name='detail[".$key."][code_lv4]' value='".$getDataSPK[0]['id_material']."'>
									<input type='hidden' name='detail[".$key."][no_bom]' value='".$getDataSPK[0]['layer']."'>
									<input type='hidden' name='detail[".$key."][nm_product]' value='".$nm_product."'>
									<input type='hidden' name='detail[".$key."][qty_spk]' value='".$getData[0]['qty']."'>
									<input type='text' name='detail[".$key."][qty_outgoing]' data-id_spk='".$getData[0]['id']."' class='form-control input-sm text-center autoNumeric0 changeDelivery'>
									</td>";
							echo "<td class='text-center' hidden><button type='button' class='btn btn-sm btn-danger delPart' data-id='".$getData[0]['id']."' title='Delete' data-role='qtip'><i class='fa fa-trash'></i></button></td>";
						echo "</tr>";
                        ?>
                    </tbody>
				</table>
			</div>
        </div>
		<div class="form-group row">
			<div class="col-md-6">
				<button type="button" class="btn btn-primary" name="save" id="save">Save</button>
				<button type="button" class="btn btn-danger" style='margin-left:5px;' name="back" id="back">Back</button>
			</div>
        </div>

      	
		</form>
	</div>
</div>


<script src="<?= base_url('assets/js/jquery.maskMoney.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>
<style>
    .datepicker, .datepicker2{
        cursor: pointer;
    }

    .mid-valign{
        vertical-align: middle !important;
    }
</style>

<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';

	$(document).ready(function(){
		$('.chosen-select').select2();
    	$('.autoNumeric0').autoNumeric('init', {mDec: '0', aPad: false})
        $('.datepicker').datepicker({ dateFormat: 'dd-M-yy' });

		setTimeout(() => {
			$("#qr_code").focus();
			$('#help-text').html('<i>Ready to Scan QR...!!</i>')
		}, 500)

		$(document).on('focus', '#qr_code', function() {
			$('#help-text').html('<i>Ready to Scan QR...!!</i>')
		})
		$(document).on('blur', '#qr_code', function() {
			$('#help-text').html('')
		})

		$(document).on('keyup', '#qty_outgoing', function(){
			let qtySisa 	= getNum($('#qty_sisa').val());
			let qtyRequest 	= getNum($(this).val().split(',').join(''))

			$('.changeDelivery').val(qtyRequest)
			if(qtyRequest > qtySisa){
				$(this).val(qtySisa)
				$('.changeDelivery').val(qtySisa)
			}
		});


    	//back
		$(document).on('click', '#back', function(){
		    window.location.href = base_url + active_controller
		});

		$(document).on('keypress', '#qr_code', function(e) {
			const input = $(this)
			if (e.keyCode == '13') {
				var formData = new FormData($('#data-form')[0]);
				$.ajax({
					url: base_url + active_controller + '/save_detail_delivery',
					type: "POST",
					data: formData,
					cache: false,
					dataType: 'json',
					processData: false,
					contentType: false,
					success: function(data) {
						if (data.status == 1) {
							swal({
								title: "Success!",
								text: data.pesan,
								type: "success",
								timer: 3000
							});

							console.log(data);
							$('#load-data').load(base_url + active_controller + '/loadDataSS/' + data.no_delivery)
							$('.autoNumeric0').autoNumeric('init', {mDec: '0', aPad: false})
							$('.notif').fadeIn('slow').html(`
								<div class="alert alert-info" role="alert">
								<h4 class="alert-heading">Scan Berhasil!</h4>
								<p>` + input.val() + `</p>
								</div>
								`)

							input.val('').focus();
							setTimeout(function() {
								$('.notif').fadeToggle('slow')
							}, 7000)

						} else {
							swal({
								title: "Failed!",
								text: data.pesan,
								type: "warning",
								timer: 3000
							});

							$('.notif').fadeIn('slow').html(`
								<div class="alert alert-warning" role="alert">
								<h4 class="alert-heading">Scan Gagal!</h4>
								<p>` + data.pesan + `</p>
								</div>
								`)

							input.val('').focus();
							setTimeout(function() {
								$('.notif').fadeToggle('slow')
							}, 7000)
						}
					},
					error: function() {
						swal({
							title: "Error Message !",
							text: 'An Error Occured During Process. Please try again..',
							type: "error",
							timer: 3000
						});
					}
				});
			}
		})

		$(document).on('keyup', '.changeDelivery', function(){
			let qty_delivery = $(this).val()
			let id_spk = $(this).data('id_spk')

			let qty_spk = getNum($(this).parent().parent().find('.qtyBelumKirim').text().split(',').join(''))
			if(qty_delivery > qty_spk){
				$(this).val(qty_spk)

				qty_delivery = qty_spk
			}

		    $.ajax({
				url: base_url + active_controller + '/changeDeliveryTemp',
				type: "POST",
				data: {
					'id_spk' : id_spk,
					'qty_delivery' : qty_delivery,
				},
				cache: false,
				dataType: 'json',
				success: function(data) {
					if(data.status == '1'){
						console.log('Success !!!')
					}
					else{
						console.log('Failed !!!')
					}
				},
				error: function() {
					console.log('Error !!!')
				}
			});
		});

		$(document).on('click', '.delPart', function(){
			var get_id 		= $(this).parent().parent().attr('class');
			$("."+get_id).remove();


			let id_spk = $(this).data('id')

			$.ajax({
				url: base_url + active_controller + '/deleteDeliveryTemp',
				type: "POST",
				data: {
					'id_spk' : id_spk
				},
				cache: false,
				dataType: 'json',
				success: function(data) {
					if(data.status == '1'){
						console.log('Success !!!')
					}
					else{
						console.log('Failed !!!')
					}
				},
				error: function() {
					console.log('Error !!!')
				}
			});
		});

		$(document).on('click', '#save', function(e){
			e.preventDefault();
			let no_surat_jalan = $('#no_surat_jalan').val()
			// console.log(no_surat_jalan)
			// if(no_surat_jalan == ''){
			// 	swal({
			// 		title	: "Error Message!",
			// 		text	: 'No Surat Jalan Kosong !',
			// 		type	: "warning"
			// 	});
			// 	return false;
			// }
			
			swal({
				  title: "Are you sure ?",
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
										window.location.href = base_url + active_controller
								}else{
									swal({
										  title	: "Save Failed!",
										  text	: data.pesan,
										  type	: "warning",
										  timer	: 7000
										});
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
