
<div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post" autocomplete="off"><br>
		<input type="hidden" name='so_number' value='<?=$header[0]['so_number'];?>'>
      	<div class="form-group row">
		  	<div class="col-md-12">
				<table class='table' width='70%'>
					<tr>
						<td width='20%'>No. SO</td>
						<td width='1%'>:</td>
						<td width='29%'><?=$header[0]['so_number'];?></td>
						<td width='20%'>Due Date SO</td>
						<td width='1%'>:</td>
						<td width='29%'><?=date('d F Y',strtotime($header[0]['due_date']));?></td>
					</tr>
					<tr>
						<td>Customer</td>
						<td>:</td>
						<td><?=$header[0]['nm_customer'];?></td>
						<td></td>
						<td></td>
						<td></td>
					</tr>
					<?php
					$tgl_dibutuhkan = (!empty($header[0]['tgl_dibutuhkan']))?date('d-M-Y',strtotime($header[0]['tgl_dibutuhkan'])):'';
					?>
					<tr>
						<td>Tgl Dibutuhkan <span class='text-red'>*</span></td>
						<td>:</td>
						<td><input type="text" name='tgl_dibutuhkan' id='tgl_dibutuhkan' class='form-control input-sm datepicker' value='<?=$tgl_dibutuhkan;?>' readonly  style='width: 200px;'></td>
						<td></td>
						<td></td>
						<td></td>
					</tr>
				</table>
			</div>
        	<div class="col-md-12">
				<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
					<thead class='thead'>
						<tr class='bg-blue'>
							<th class='text-center th'>#</th>
							<th class='text-center th'>Material Name</th>
							<th class='text-center th'>Estimasi (Kg)</th>
							<th class='text-center th'>Stock Free (Kg)</th>
							<th class='text-center th'>Use Stock (Kg)</th>
							<th class='text-center th'>Sisa Stock Free (Kg)</th>
							<th class='text-center th'>Min Stock</th>
							<th class='text-center th'>Max Stock</th>
							<th class='text-center th'>PR On Progress</th>
							<th class='text-center th'>Propose Purchase</th>
							<th class='text-center th'>Keterangan</th>
						</tr>
					</thead>
					<tbody>
						<?php
						 $GET_OUTANDING_PR = get_pr_on_progress();
						foreach ($detail as $key => $value) { $key++;
							$nm_material 	= (!empty($GET_LEVEL4[$value['id_material']]['nama']))?$GET_LEVEL4[$value['id_material']]['nama']:'';
							$stock_free 	= (!empty($GET_STOK_PUSAT[$value['id_material']]['stok']))?$GET_STOK_PUSAT[$value['id_material']]['stok']:'';
							$use_stock 		= (!empty($value['use_stock']))?$value['use_stock']:$value['qty_order'];
							if($stock_free < $use_stock){
								$use_stock 		= $stock_free;
							}

							$use_stock_new = 0;
							if($use_stock > 0){
								$use_stock_new = $use_stock;
							}

							$sisa_free 		= $stock_free - $use_stock;

							$propose		= 0;
							if(empty($value['propose_purchase'])){
								if($stock_free < $value['min_stok']){
									$propose = ($value['min_stok'] - $sisa_free) + ($value['max_stok'] - $value['min_stok']);
								}
							}
							else{
								$propose = $value['propose_purchase'];
							}

							$outanding_pr   = (!empty($GET_OUTANDING_PR[$value['id_material']]) and $GET_OUTANDING_PR[$value['id_material']] > 0) ? $GET_OUTANDING_PR[$value['id_material']] : 0;

							echo "<tr>";
								echo "<td class='text-center'>".$key."</td>";
								echo "	<td class='text-left'>".$nm_material."
										<input type='hidden' name='detail[".$key."][id]' value='".$value['id']."'>
										<input type='hidden' name='detail[".$key."][code_material]' value='".$value['id_material']."'>
										<input type='hidden' name='detail[".$key."][stock_free]' value='".$stock_free."'>
										<input type='hidden' name='detail[".$key."][min_stok]' value='".$value['min_stok']."'>
										<input type='hidden' name='detail[".$key."][max_stok]' value='".$value['max_stok']."'>
										</td>";
								echo "<td class='text-right qty_order'>".number_format($value['qty_order'],5)."</td>";
								echo "<td class='text-right stock_free'>".number_format($stock_free,5)."</td>";
								echo "<td align='center'><input type='text' class='form-control input-sm text-right autoNumeric5 use_stock' style='width: 100px;' name='detail[".$key."][use_stock]' value='".$use_stock_new."'></td>";
								echo "<td class='text-right sisa_free'>".number_format($sisa_free,5)."</td>";
								echo "<td class='text-right min_stok'>".number_format($value['min_stok'],2)."</td>";
								echo "<td class='text-right max_stok'>".number_format($value['max_stok'],2)."</td>";
								echo "<td class='text-right max_stok'>".number_format($outanding_pr,2)."</td>";
								echo "<td align='center'><input type='text' class='form-control input-sm text-right autoNumeric5 propose' style='width: 120px;' name='detail[".$key."][propose]' value='".$propose."'></td>";
								echo "<td align='center'><input type='text' class='form-control input-sm text-left' name='detail[".$key."][note]' value='".$value['note']."'></td>";
							echo "</tr>";
						}
						?>
					</tbody>
				</table>
        	</div>
        </div>
	
		<div class="form-group row">
        	<div class="col-md-12">
				<button type="button" class="btn btn-primary" name="save" id="save">Process</button>
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
    .datepicker{
        cursor: pointer;
    }
</style>

<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';

	$(document).ready(function(){
		$('.datepicker').datepicker({ dateFormat: 'dd-M-yy' });
    	$('.autoNumeric5').autoNumeric('init', {mDec: '5', aPad: false})
    	$('.chosen-select').select2()

    	//back
		$(document).on('click', '#back', function(){
		    window.location.href = base_url + active_controller
		});

		$(document).on('keyup', '.use_stock', function(){
		    let getHTML = $(this).parent().parent()

		    let qty_order	= getNum(getHTML.find('.qty_order').text().split(",").join(""))
		    let stock_free	= getNum(getHTML.find('.stock_free').text().split(",").join(""))
			let use_stock	= getNum($(this).val().split(",").join(""))

			if(use_stock > qty_order){
				use_stock = qty_order
				$(this).val(use_stock)
			}

			if(use_stock > stock_free){
				use_stock = stock_free
				$(this).val(use_stock)
			}

			let sisa_free	= stock_free - use_stock
		    let min_stok	= getNum(getHTML.find('.min_stok').text().split(",").join(""))
		    let max_stok	= getNum(getHTML.find('.max_stok').text().split(",").join(""))

			getHTML.find('.sisa_free').text(number_format(sisa_free,5))

			let propose = 0
			if(stock_free < min_stok){
				propose = (min_stok - sisa_free) + (max_stok - min_stok);
			}

			getHTML.find('.propose').val(number_format(propose,2))
		});

		$('#save').click(function(e){
			e.preventDefault();
			var tgl_dibutuhkan = $("#tgl_dibutuhkan").val();

			if(tgl_dibutuhkan == ''){
				swal({title	: "Error Message!",text	: 'Tanggal Dibutuhkan masih kosong ...',type	: "warning"
				});
				$('#save').prop('disabled',false); return false;
			}

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
						var baseurl=siteurl+active_controller+'/material_planning';
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
