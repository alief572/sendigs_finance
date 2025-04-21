<!-- <div class="row">
	<div class="col-md-12">
	    <div class="box box-primary">
				<div class="box-body">

					<div class="form-group row">
		        <div class="col-md-12">
			        <div class="box-body table-responsive">
		            <table class="table table-hover table-bordered table-responsive">
		              <thead style='background-color: #e3e3e3;'>
		                <tr>
											<th width='40%' class='text-left mid'>NO SO</th>
		                  <th width='10%' class='text-left mid'>SHIPMENT</th>
		                  <th width='10%' class='text-right mid bold'>ORDER</th>
		                  <th width='10%' class='text-right mid bold'>PROPOSE</th>
		                  <th width='10%' class='text-right mid bold'>FINISH GOOD</th>
		                  <th width='10%' class='text-right mid bold'>BALANCE FG</th>
		                  <th width='10%' class='text-right mid bold'>PROGRESS</th>
		                </tr>
		              </thead>
		              <tbody>
		                <tr>
		                  <td>
												<select id="sales_order" name="sales_order" class="form-control input-md chosen-select">
													<?php
													foreach (get_sales_order() as $val => $valx) {
														$selx = ($no_so == $valx['no_so']) ? 'selected' : '';
														echo "<option value='" . $valx['no_so'] . "' " . $selx . ">" . $valx['no_so'] . "  [" . strtoupper(date('d-M-Y', strtotime($valx['delivery_date']))) . "] / " . strtoupper($valx['name_customer']) . "</option>";
													}
													?>
												</select>
											</td>
											<td>EXISTING</td>
		                  <td class='text-right mid'><?= number_format($qty_order1); ?></td>
		                  <td class='text-right mid'><?= number_format($qty_propose1); ?></td>
		                  <td class='text-right mid'><?= number_format($qtyfg1); ?></td>
		                  <td class='text-right mid'><?= number_format($qtybal1); ?></td>
		                  <td class='text-right mid'><?= number_format($progres1, 2); ?> %</td>
		                </tr>
		                <tr>
		                  <td>
												<select id="sales_order2" name="sales_order2" class="form-control input-md chosen-select">
													<?php
													foreach (get_sales_order() as $val => $valx) {
														$selx = ($no_so2 == $valx['no_so']) ? 'selected' : '';
														echo "<option value='" . $valx['no_so'] . "' " . $selx . ">" . $valx['no_so'] . "  [" . strtoupper(date('d-M-Y', strtotime($valx['delivery_date']))) . "] / " . strtoupper($valx['name_customer']) . "</option>";
													}
													?>
												</select>
											</d>
											<td>DOHA</td>
		                  <td class='text-right mid'><?= number_format($qty_order2); ?></td>
		                  <td class='text-right mid'><?= number_format($qty_propose2); ?></td>
		                  <td class='text-right mid'><?= number_format($qtyfg2); ?></td>
		                  <td class='text-right mid'><?= number_format($qtybal2); ?></td>
		                  <td class='text-right mid'><?= number_format($progres2, 2); ?> %</td>
		                </tr>
		                <tr>
		                  <td></td>
											<td class='text-left mid'>TOTAL</td>
		                  <td class='text-right mid'><?= number_format($qty_order1 + $qty_order2); ?></td>
		                  <td class='text-right mid'><?= number_format($qty_propose1 + $qty_propose2); ?></td>
		                  <td class='text-right mid'><?= number_format($qtyfg1 + $qtyfg2); ?></td>
		                  <td class='text-right mid'><?= number_format($qtybal1 + $qtybal2); ?></td>
											<?php
											$progres = 0;
											if (($qtyfg1 + $qtyfg2) > 0 and ($qty_propose1 + $qty_propose2) > 0) {
												$progres = (($qtyfg1 + $qtyfg2) / ($qty_propose1 + $qty_propose2)) * 100;
											}
											?>
		                  <td class='text-right mid'><?= number_format($progres, 2); ?> %</td>
		                </tr>
		              </tbody>
		            </table>
			        </div>
		  	    </div>
					</div>
				</div>
      </div>
	</div>
</div> -->
<!-- Dashboard2 -->
<div class="row">
	<div class="col-md-12">
		<div class="box box-primary">
			<div class="box-body">

				<div class="form-group row">
					<div class="col-md-12">
						<div class="box-body table-responsive">
							<table class="table table-hover table-bordered table-responsive">
								<thead style='background-color: #e3e3e3;'>
									<tr>
										<th width='40%' class='text-left mid'>NO SO</th>
										<th width='10%' class='text-left mid'>SHIPMENT</th>
										<th width='10%' class='text-right mid bold'>ORDER</th>
										<th width='10%' class='text-right mid bold'>PROPOSE</th>
										<th width='10%' class='text-right mid bold'>FINISH GOOD</th>
										<th width='10%' class='text-right mid bold'>BALANCE FG</th>
										<th width='10%' class='text-right mid bold'>PROGRESS</th>
									</tr>
								</thead>
								<tbody>
									<tr id='exis_1' class='header_1'>
										<td>
											<select id="noso_1" class="form-control input-md chosen-select salesorder" data-type='exis'>
												<?php
												foreach (get_sales_order() as $val => $valx) {
													$selx = ($no_so == $valx['no_so']) ? 'selected' : '';
													echo "<option value='" . $valx['no_so'] . "' " . $selx . ">" . $valx['no_so'] . "  [" . strtoupper(date('d-M-Y', strtotime($valx['delivery_date']))) . "] / " . strtoupper($valx['name_customer']) . "</option>";
												}
												?>
											</select>
										</td>
										<td>EXISTING<input type='hidden' id='exis_over_1' value='<?= $over1; ?>'></td>
										<td class='text-right mid'>
											<div class='order' id='exis_order_1'><?= number_format($qty_order1); ?></div>
										</td>
										<td class='text-right mid'>
											<div class='propose' id='exis_propose_1'><?= number_format($qty_propose1); ?></div>
										</td>
										<td class='text-right mid'>
											<div class='fg' id='exis_fg_1'><?= number_format($qtyfg1); ?></div>
										</td>
										<td class='text-right mid'>
											<div class='bal' id='exis_balance_1'><?= number_format($qtybal1); ?></div>
										</td>
										<td class='text-right mid'>
											<div id='exis_progress_1'><?= number_format($progres1, 2); ?> %</div>
										</td>
									</tr>
									<tr id='exis_2'>
										<td colspan='7'><button type='button' class='btn btn-sm btn-primary addPart' title='ADD EXISTING' style='min-width:150px;'><i class='fa fa-plus'></i>&nbsp;&nbsp;ADD EXISTING</button></td>
									</tr>
									<tr id='doha_1' class='header2_1'>
										<td>
											<select id="noso2_1" class="form-control input-md chosen-select salesorder" data-type='doha'>
												<?php
												foreach (get_sales_order() as $val => $valx) {
													$selx = ($no_so2 == $valx['no_so']) ? 'selected' : '';
													echo "<option value='" . $valx['no_so'] . "' " . $selx . ">" . $valx['no_so'] . "  [" . strtoupper(date('d-M-Y', strtotime($valx['delivery_date']))) . "] / " . strtoupper($valx['name_customer']) . "</option>";
												}
												?>
											</select>
											</d>
										<td>DOHA<input type='hidden' id='doha_over_1' value='<?= $over2; ?>'></td>
										<td class='text-right mid'>
											<div class='order' id='doha_order_1'><?= number_format($qty_order2); ?></div>
										</td>
										<td class='text-right mid'>
											<div class='propose' id='doha_propose_1'><?= number_format($qty_propose2); ?></div>
										</td>
										<td class='text-right mid'>
											<div class='fg' id='doha_fg_1'><?= number_format($qtyfg2); ?></div>
										</td>
										<td class='text-right mid'>
											<div class='bal' id='doha_balance_1'><?= number_format($qtybal2); ?></div>
										</td>
										<td class='text-right mid'>
											<div id='doha_progress_1'><?= number_format($progres2, 2); ?> %</div>
										</td>
									</tr>
									<tr id='doha_2'>
										<td colspan='7'><button type='button' class='btn btn-sm btn-success addPart2' title='ADD DOHA' style='min-width:150px;'><i class='fa fa-plus'></i>&nbsp;&nbsp;ADD DOHA</button></td>
									</tr>
									<tr>
										<td></td>
										<td class='text-left mid'>TOTAL</td>
										<td class='text-right mid'>
											<div class='tot_order'><?= number_format($qty_order1 + $qty_order2); ?></div>
										</td>
										<td class='text-right mid'>
											<div class='tot_propose'><?= number_format($qty_propose1 + $qty_propose2); ?></div>
										</td>
										<td class='text-right mid'>
											<div class='tot_fg'><?= number_format($qtyfg1 + $qtyfg2); ?></div>
										</td>
										<td class='text-right mid'>
											<div class='tot_bal'><?= number_format($qtybal1 + $qtybal2); ?></div>
										</td>
										<?php
										$progres = 0;
										if (($qtyfg1 + $qtyfg2) > 0 and ($qty_propose1 + $qty_propose2) > 0) {
											$progres = (($qtyfg1 + $qtyfg2) / ($qty_propose1 + $qty_propose2)) * 100;
										}
										?>
										<td class='text-right mid'>
											<div class='tot_progress'><?= number_format($progres, 2); ?> %</div>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
	.mid {
		vertical-align: middle !important;
	}

	.chosen-select {
		min-width: 200px !important;
		max-width: 100% !important;
	}

	.bold {
		font-weight: bold !important;
		padding-right: 20px !important;
	}
</style>
<script type="text/javascript">
	$(document).on('change', '#sales_order, #sales_order2', function() {
		var noso = $('#sales_order').val();
		var noso2 = $('#sales_order2').val();
		window.location.href = base_url + 'dashboard/index/' + noso + '/' + noso2;
	});

	$(document).on('click', '.addPart', function() {
		var get_id = $(this).parent().parent().attr('id');
		var split_id = get_id.split('_');
		var id = parseInt(split_id[1]) + 1;
		var id_bef = split_id[1];

		$.ajax({
			url: base_url + 'dashboard/get_add/' + id,
			cache: false,
			type: "POST",
			dataType: "json",
			success: function(data) {
				$("#exis_" + id_bef).before(data.header);
				$("#exis_" + id_bef).remove();
				$('.chosen_select').select2();
			},
			error: function() {
				swal({
					title: "Error Message !",
					text: 'Connection Time Out. Please try again..',
					type: "warning",
					timer: 3000,
					showCancelButton: false,
					showConfirmButton: false,
					allowOutsideClick: false
				});
			}
		});
	});

	$(document).on('click', '.addPart2', function() {
		var get_id = $(this).parent().parent().attr('id');
		var split_id = get_id.split('_');
		var id = parseInt(split_id[1]) + 1;
		var id_bef = split_id[1];

		$.ajax({
			url: base_url + 'dashboard/get_add2/' + id,
			cache: false,
			type: "POST",
			dataType: "json",
			success: function(data) {
				$("#doha_" + id_bef).before(data.header);
				$("#doha_" + id_bef).remove();
				$('.chosen_select').select2();
			},
			error: function() {
				swal({
					title: "Error Message !",
					text: 'Connection Time Out. Please try again..',
					type: "warning",
					timer: 3000,
					showCancelButton: false,
					showConfirmButton: false,
					allowOutsideClick: false
				});
			}
		});
	});

	$(document).on('click', '.delPart', function() {
		var get_id = $(this).parent().parent().attr('class');
		$("." + get_id).remove();
	});

	$(document).on('change', '.salesorder', function() {
		var get_id = $(this).parent().parent().attr('class');
		var split_id = get_id.split('_');
		var id_bef = split_id[1];
		// console.log(id_bef);
		var type = $(this).data('type');
		var value = $(this).val();

		var over1 = $('#exis_over_1').val();
		var over2 = $('#doha_over_1').val();

		var SUM_ORDER = 0;
		var SUM_PROPOSE = 0;
		var SUM_FG = 0;
		var SUM_BAL = 0;
		var progres = 0;

		$.ajax({
			url: base_url + 'dashboard/get_result/' + type + '/' + value,
			cache: false,
			type: "POST",
			data: {
				'over1': over1,
				'over2': over2,
				'nomor': id_bef
			},
			dataType: "json",
			success: function(data) {
				$("#" + data.id + "_order_" + id_bef).html(data.order);
				$("#" + data.id + "_propose_" + id_bef).html(data.propose);
				$("#" + data.id + "_fg_" + id_bef).html(data.fg);
				$("#" + data.id + "_balance_" + id_bef).html(data.bal);
				$("#" + data.id + "_progress_" + id_bef).html(data.progres);
				if (data.nomor == '1') {
					$("#" + data.id + "_over_1").val(data.over);
				}
				//order
				$(".order").each(function() {
					SUM_ORDER += Number($(this).html().split(",").join(""));
				});
				$(".tot_order").html(SUM_ORDER);
				//propose
				$(".propose").each(function() {
					SUM_PROPOSE += Number($(this).html().split(",").join(""));
				});
				$(".tot_propose").html(SUM_PROPOSE);
				//FG
				$(".fg").each(function() {
					SUM_FG += Number($(this).html().split(",").join(""));
				});
				$(".tot_fg").html(SUM_FG);
				//BAL
				$(".bal").each(function() {
					SUM_BAL += Number($(this).html().split(",").join(""));
				});
				$(".tot_bal").html(SUM_BAL);
				//PROCESS
				if (SUM_FG > 0 && SUM_PROPOSE > 0) {
					progres = (SUM_FG / SUM_PROPOSE) * 100;
				}
				$(".tot_progress").html(number_format(progres, 2) + ' %');

			},
			error: function() {
				swal({
					title: "Error Message !",
					text: 'Connection Time Out. Please try again..',
					type: "warning",
					timer: 3000,
					showCancelButton: false,
					showConfirmButton: false,
					allowOutsideClick: false
				});
			}
		});
	});

	function getNum(val) {
		if (isNaN(val) || val == '') {
			return 0;
		}
		return parseFloat(val);
	}

	function number_format(number, decimals, dec_point, thousands_sep) {
		// Strip all characters but numerical ones.
		number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
		var n = !isFinite(+number) ? 0 : +number,
			prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
			sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
			dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
			s = '',
			toFixedFix = function(n, prec) {
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