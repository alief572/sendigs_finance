
<div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post" autocomplete="off"><br>
		<div class="form-group row">
        	<div class="col-md-12">
				<table width='50%'>
					<tr>
						<td width='20%'>Sales Order </td>
						<td width='1%'>:</td>
						<td><?=$getData[0]['no_so'];?></td>
					</tr>
					<tr>
						<td>No Penawaran</td>
						<td>:</td>
						<td><?=strtoupper($getData[0]['no_penawaran']);?></td>
					</tr>
					<tr>
						<td>Customer</td>
						<td>:</td>
						<td><?=strtoupper($getData[0]['nm_customer']);?></td>
					</tr>
					<tr>
						<td>Project</td>
						<td>:</td>
						<td><?=strtoupper($getData[0]['project']);?></td>
					</tr>
                    <tr>
						<td>Tanggal Kirim</td>
						<td>:</td>
						<td><input type="text" name='delivery_date' id='delivery_date' class='form-control input-md text-center datepicker' style='width:150px;' value='<?=date('d-M-Y',strtotime($getData[0]['delivery_date']));?>' readonly></td>
					</tr>
                    <tr>
						<td>Alamat Pengiriman</td>
						<td>:</td>
						<td><textarea name="delivery_address" id="delivery_address" class='form-control' cols="100" rows="3"><?=$getData[0]['invoice_address'];?></textarea></td>
					</tr>
				</table>
				<input type="hidden" id='no_so' name='no_so' value='<?=$getData[0]['no_so'];?>'>
			</div>
        </div>
		<div class="form-group row">
        	<div class="col-md-12">
				<table class="table table-bordered table-striped" width='100%'>
					<thead>
                        <tr>
                            <th width='5%' class='text-center'>#</th>
                            <th>PRODUCT</th>
                            <th width='12%' class='text-center'>QTY ORDER</th>
                            <th width='12%' class='text-center'>QTY FG BOOKING</th>
                            <th width='12%' class='text-center'>QTY BELUM KIRIM</th>
                            <th width='12%' class='text-center'>QTY DELIVERY</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($getDetail as $key => $value) { $key++;

                            $qty_belum_kirim = $value['qty_so'] - $value['qty_delivery'];

                            echo "<tr>";
                                echo "<td class='text-center'>".$key."</td>";
                                echo "<td>".$value['nama_produk']."</td>";
                                echo "<td class='text-center' class='qtySO'>".number_format($value['qty_so'],2)."</td>";
                                echo "<td class='text-center'>".number_format($value['stok_tersedia'],2)."</td>";
                                echo "<td class='text-center qtyBelumKirim'>".number_format($qty_belum_kirim,2)."</td>";
                                echo "<td class='text-center'>
                                        <input type='hidden' name='detail[".$key."][code_lv4]' value='".$value['id_category3']."'>
                                        <input type='hidden' name='detail[".$key."][id_so_det]' value='".$value['id_so_detail']."'>
                                        <input type='hidden' name='detail[".$key."][qty_so]' value='".$value['qty_so']."'>
                                        <input type='hidden' name='detail[".$key."][qty_booking]' value='".$value['stok_tersedia']."'>
                                        <input type='text' name='detail[".$key."][qty_delivery]' class='form-control input-sm text-center autoNumeric0 changeDelivery'>
                                        </td>";
                            echo "</tr>";
                        }
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

    	//back
		$(document).on('click', '#back', function(){
		    window.location.href = base_url + active_controller
		});

        $(document).on('keyup', '.changeDelivery', function(){
		    let qty_delivery    = getNum($(this).val().split(",").join(""))
            let HTML = $(this).parent().parent()
            // console.log(HTML.html())
		    let ots_delivery    = getNum(HTML.find('.qtyBelumKirim').text().split(",").join(""))
            console.log(`${qty_delivery} - ${ots_delivery}`)
            if(qty_delivery > ots_delivery){
                $(this).val(ots_delivery)
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
