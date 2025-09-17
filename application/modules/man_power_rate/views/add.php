<?php
// print_r($header);
$total_direct   	= (!empty($header))?$header[0]->total_direct:'';
$total_bpjs   		= (!empty($header))?$header[0]->total_bpjs:'';
$total_biaya_lain   = (!empty($header))?$header[0]->total_biaya_lain:'';

$rate_dollar   = (!empty($header))?$header[0]->rate_dollar:'';
$upah_per_bulan_dollar   = (!empty($header))?$header[0]->upah_per_bulan_dollar:'';
$upah_per_jam_dollar   = (!empty($header))?$header[0]->upah_per_jam_dollar:'';
$upah_per_bulan   = (!empty($header))?$header[0]->upah_per_bulan:'';
$upah_per_jam   = (!empty($header))?$header[0]->upah_per_jam:'';

// print_r($header);
?>

 <div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post"><br>
			<input type="hidden" name="total_direct" id="total_direct" value="<?=$total_direct;?>">
			<input type="hidden" name="total_bpjs" id="total_bpjs" value="<?=$total_bpjs;?>">
			<input type="hidden" name="total_biaya_lain" id="total_biaya_lain" value="<?=$total_biaya_lain;?>">
			<div class='box box-info'>
				<div class='box-header'>
					<h3 class='box-title'>A. Salary Direct Man Power</h3>
				</div>
				<div class='box-body hide_header'>
					<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class='text-center' style='width: 4%;'>#</th>
								<th class='text-center' style='width: 30%;'>Salary Direct Man Power</th>
								<th class='text-center' style='width: 15%;'>Nilai</th>
								<th class='text-center'>Keterangan</th>
								<th class='text-center' style='width: 4%;'>#</th>
							</tr>
						</thead>
						<tbody>
							<?php
								$val = 0;
								if(!empty($detail_direct)){
									foreach($detail_direct AS $val => $valx){ $val++;
										echo "<tr class='header_".$val."'>";
											echo "<td align='center'>".$val."</td>";
											echo "<td align='left'>";
											echo "<input type='text' name='Detail[".$val."][nama]' class='form-control input-md' placeholder='Name' value='".$valx['nama']."'>";
											echo "</td>";
											echo "<td align='left'>";
											echo "<input type='text' name='Detail[".$val."][nilai]' class='form-control text-right input-md autoNumeric2 nilaiDirect summaryCal' placeholder='Nilai' value='".$valx['nilai']."'>";
											echo "</td>";
											echo "<td align='left'>";
											echo "<input type='text' name='Detail[".$val."][keterangan]' class='form-control input-md' placeholder='Keterangan' value='".$valx['keterangan']."'>";
											echo "</td>";
											echo "<td align='left'>";
											echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
											echo "</td>";
										echo "</tr>";
									}
								}
							?>
							<tr id='add_<?=$val?>'>
								<td align='center'></td>
								<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-warning addPart' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add</button></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class='box box-info'>
				<div class='box-header'>
					<h3 class='box-title'>B. BPJS</h3>
				</div>
				<div class='box-body hide_header'>
					<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class='text-center' style='width: 4%;'>#</th>
								<th class='text-center' style='width: 30%;'>BPJS</th>
								<th class='text-center' style='width: 15%;'>Nilai</th>
								<th class='text-center'>Keterangan</th>
								<th class='text-center' style='width: 4%;'>#</th>
							</tr>
						</thead>
						<tbody>
							<?php
								$val = 0;
								if(!empty($detail_bpjs)){
									foreach($detail_bpjs AS $val => $valx){ $val++;
										echo "<tr class='header2_".$val."'>";
											echo "<td align='center'>".$val."</td>";
											echo "<td align='left'>";
											echo "<input type='text' name='Detail2[".$val."][nama]' class='form-control input-md' placeholder='Name' value='".$valx['nama']."'>";
											echo "</td>";
											echo "<td align='left'>";
											echo "<input type='text' name='Detail2[".$val."][nilai]' class='form-control text-right input-md autoNumeric2 nilaiBPJS summaryCal' placeholder='Nilai' value='".$valx['nilai']."'>";
											echo "</td>";
											echo "<td align='left'>";
											echo "<input type='text' name='Detail2[".$val."][keterangan]' class='form-control input-md' placeholder='Keterangan' value='".$valx['keterangan']."'>";
											echo "</td>";
											echo "<td align='left'>";
											echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
											echo "</td>";
										echo "</tr>";
									}
								}
							?>
							<tr id='add2_<?=$val?>'>
								<td align='center'></td>
								<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-warning addPart2' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add</button></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class='box box-info'>
				<div class='box-header'>
					<h3 class='box-title'>C. Biaya Lain-Lain</h3>
				</div>
				<div class='box-body hide_header'>
					<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class='text-center' style='width: 4%;'>#</th>
								<th class='text-center' style='width: 30%;'>Biaya Lain-Lain</th>
								<th class='text-center' style='width: 15%;'>Nilai</th>
								<th class='text-center'>Keterangan</th>
								<th class='text-center' style='width: 15%;'>Harga /Pcs</th>
								<th class='text-center' style='width: 4%;'>#</th>
							</tr>
						</thead>
						<tbody>
							<?php
								$val = 0;
								if(!empty($detail_lain)){
									foreach($detail_lain AS $val => $valx){ $val++;
										echo "<tr class='header3_".$val."'>";
											echo "<td align='center'>".$val."</td>";
											echo "<td align='left'>";
											echo "<input type='text' name='Detail3[".$val."][nama]' class='form-control input-md' placeholder='Name' value='".$valx['nama']."'>";
											echo "</td>";
											echo "<td align='left'>";
											echo "<input type='text' name='Detail3[".$val."][nilai]' class='form-control text-right input-md autoNumeric2 nilaiLain summaryCal' placeholder='Nilai' value='".$valx['nilai']."'>";
											echo "</td>";
											echo "<td align='left'>";
											echo "<input type='text' name='Detail3[".$val."][keterangan]' class='form-control input-md' placeholder='Keterangan' value='".$valx['keterangan']."'>";
											echo "</td>";
											echo "<td align='left'>";
											echo "<input type='text' name='Detail3[".$val."][harga_per_pcs]' class='form-control text-right input-md autoNumeric2' placeholder='Nilai' value='".$valx['harga_per_pcs']."'>";
											echo "</td>";
											echo "<td align='left'>";
											echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
											echo "</td>";
										echo "</tr>";
									}
								}
							?>
							<tr id='add3_<?=$val?>'>
								<td align='center'></td>
								<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-warning addPart3' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add</button></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<br>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="customer">Kurs</label>
				</div>
				<div class="col-md-2">
					<input type="text" name="rate_dollar" id="rate_dollar" class='form-control input-md text-right autoNumeric2 summaryCal' value="<?=$rate_dollar;?>">
				</div>
				<div class="col-md-1">
					<button type='button' class='btn btn-sm btn-success' id='update-kurs'>Update Kurs</button>
				</div>
				<div class="col-md-7">
					<p>
						<span class='text-bold'>Kurs date:</span> <?= date('d-M-Y',strtotime($header[0]->kurs_tanggal));?><br>
						<span class='text-bold text-primary'>Last update kurs in rate man power:</span> <?= date('d-M-Y H:i:s',strtotime($header[0]->kurs_date));?>
					</p>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="customer">Upah /Bulan $</label>
				</div>
				<div class="col-md-2">
					<input type="text" name="upah_per_bulan_dollar" id="upah_per_bulan_dollar" class='form-control input-md text-right autoNumeric2' readonly value="<?=$upah_per_bulan_dollar;?>">
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="customer">Rate MP $</label>
				</div>
				<div class="col-md-2">
					<input type="text" name="upah_per_jam_dollar" id="upah_per_jam_dollar" class='form-control input-md text-right autoNumeric2' readonly value="<?=$upah_per_jam_dollar;?>">
				</div>
			</div>
			<hr>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="customer">Upah /Bulan (Rp)</label>
				</div>
				<div class="col-md-2">
					<input type="text" name="upah_per_bulan" id="upah_per_bulan" class='form-control input-md text-right autoNumeric2' readonly value="<?=$upah_per_bulan;?>">
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="customer">Rate MP (Rp)</label>
				</div>
				<div class="col-md-2">
					<input type="text" name="upah_per_jam" id="upah_per_jam" class='form-control input-md text-right autoNumeric2' readonly value="<?=$upah_per_jam;?>">
				</div>
			</div>

			<button type="button" class="btn btn-danger" style='float:right; margin-left:5px;' name="back" id="back"><i class="fa fa-reply"></i> Back</button>
			<button type="submit" class="btn btn-primary" style='float:right;' name="save" id="save"><i class="fa fa-save"></i> Save</button>
		</form>
	</div>
</div>

<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>
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
		$('.chosen-select').select2();
		$( ".datepicker" ).datepicker();
		$( ".autoNumeric2" ).autoNumeric('init', {mDec: '2', aPad: false});

		//add part
		$(document).on('click', '.addPart', function(){
			var get_id 		= $(this).parent().parent().attr('id');
			var split_id	= get_id.split('_');
			var id 		= parseInt(split_id[1])+1;
			var id_bef 	= split_id[1];

			$.ajax({
				url: base_url+active_controller+'/get_add/'+id,
				cache: false,
				type: "POST",
				dataType: "json",
				success: function(data){
					$("#add_"+id_bef).before(data.header);
					$("#add_"+id_bef).remove();
					$('.chosen_select').select2({width: '100%'});
					$('.autoNumeric2').autoNumeric('init', {mDec: '2', aPad: false});
					swal.close();
				},
				error: function() {
					swal({
						title				: "Error Message !",
						text				: 'Connection Time Out. Please try again..',
						type				: "warning",
						timer				: 3000
					});
				}
			});
		});

		$(document).on('click', '.addPart2', function(){
			var get_id 		= $(this).parent().parent().attr('id');
			var split_id	= get_id.split('_');
			var id 		= parseInt(split_id[1])+1;
			var id_bef 	= split_id[1];

			$.ajax({
				url: base_url+active_controller+'/get_add2/'+id,
				cache: false,
				type: "POST",
				dataType: "json",
				success: function(data){
					$("#add2_"+id_bef).before(data.header);
					$("#add2_"+id_bef).remove();
					$('.chosen_select').select2({width: '100%'});
					$('.autoNumeric2').autoNumeric('init', {mDec: '2', aPad: false});
					swal.close();
				},
				error: function() {
					swal({
						title				: "Error Message !",
						text				: 'Connection Time Out. Please try again..',
						type				: "warning",
						timer				: 3000
					});
				}
			});
		});

		$(document).on('click', '.addPart3', function(){
			var get_id 		= $(this).parent().parent().attr('id');
			var split_id	= get_id.split('_');
			var id 		= parseInt(split_id[1])+1;
			var id_bef 	= split_id[1];

			$.ajax({
				url: base_url+active_controller+'/get_add3/'+id,
				cache: false,
				type: "POST",
				dataType: "json",
				success: function(data){
					$("#add3_"+id_bef).before(data.header);
					$("#add3_"+id_bef).remove();
					$('.chosen_select').select2({width: '100%'});
					$('.autoNumeric2').autoNumeric('init', {mDec: '2', aPad: false});
					swal.close();
				},
				error: function() {
					swal({
						title				: "Error Message !",
						text				: 'Connection Time Out. Please try again..',
						type				: "warning",
						timer				: 3000
					});
				}
			});
		});

	   //delete part
		$(document).on('click', '.delPart', function(){
			var get_id 		= $(this).parent().parent().attr('class');
			$("."+get_id).remove();
		});

    	//back
		$(document).on('click', '#back', function(){
		    window.location.href = base_url + active_controller;
		});

		$(document).on('keyup','.summaryCal',function(){
			get_summary();
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
						var baseurl = base_url+active_controller+'/add'
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
										  timer	: 3000,
										  showCancelButton	: false,
										  showConfirmButton	: false,
										  allowOutsideClick	: false
										});
									window.location.href = base_url + active_controller;
								}else{

									if(data.status == 2){
										swal({
										  title	: "Save Failed!",
										  text	: data.pesan,
										  type	: "warning",
										  timer	: 3000,
										});
									}else{
										swal({
										  title	: "Save Failed!",
										  text	: data.pesan,
										  type	: "warning",
										  timer	: 3000,
										});
									}

								}
							},
							error: function() {

								swal({
								  title				: "Error Message !",
								  text				: 'An Error Occured During Process. Please try again..',
								  type				: "warning",
								  timer				: 3000,
								});
							}
						});
				  } else {
					swal("Cancelled", "Data can be process again :)", "error");
					return false;
				  }
			});
		});

		$('#update-kurs').click(function(e){
			e.preventDefault();
			
			swal({
				  title: "Are you sure?",
				  text: "Update KURS!",
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
						var baseurl = base_url+active_controller+'/update_kurs'
						$.ajax({
							url			: baseurl,
							type		: "POST",
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
										  timer	: 3000
										});
									window.location.href = base_url + active_controller + '/add';
								}else{

									if(data.status == 2){
										swal({
										  title	: "Save Failed!",
										  text	: data.pesan,
										  type	: "warning",
										  timer	: 3000,
										});
									}else{
										swal({
										  title	: "Save Failed!",
										  text	: data.pesan,
										  type	: "warning",
										  timer	: 3000,
										});
									}

								}
							},
							error: function() {

								swal({
								  title				: "Error Message !",
								  text				: 'An Error Occured During Process. Please try again..',
								  type				: "warning",
								  timer				: 3000,
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

function get_summary(){
	var rate_dollar   		= getNum($('#rate_dollar').val().split(",").join(""));

	let summary_direct = 0
	let summary_bpjs = 0
	let summary_lainnya = 0

	$(".nilaiDirect" ).each(function() {
		summary_direct += getNum($(this).val().split(",").join(""));
 	});
	 $(".nilaiBPJS" ).each(function() {
		summary_bpjs += getNum($(this).val().split(",").join(""));
 	});
	 $(".nilaiLain" ).each(function() {
		summary_lainnya += getNum($(this).val().split(",").join(""));
 	});

	let sum_semua 		= summary_direct + summary_bpjs + summary_lainnya
	var upah_per_jam 	= sum_semua / 173

	let sum_semua_usd = 0
	let upah_per_jam_usd = 0
	if(rate_dollar > 0){
		sum_semua_usd = sum_semua / rate_dollar
		upah_per_jam_usd = sum_semua_usd / 173
	}

	$('#upah_per_bulan_dollar').val(number_format(sum_semua_usd,2));
	$('#upah_per_jam_dollar').val(number_format(upah_per_jam_usd,2));

	$('#upah_per_bulan').val(number_format(sum_semua));
	$('#upah_per_jam').val(number_format(upah_per_jam));

	$('#total_direct').val(number_format(summary_direct))
	$('#total_bpjs').val(number_format(summary_bpjs))
	$('#total_biaya_lain').val(number_format(summary_lainnya))
}

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
