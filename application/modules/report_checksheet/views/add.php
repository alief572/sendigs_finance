
<div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post" autocomplete="off"><br>
		<div class="form-group row">
        	<div class="col-md-12">
				<table width='80%'>
					<tr>
						<td width='20%'>Sales Order</td>
						<td width='1%'>:</td>
						<td><?=$getData[0]['so_number'];?></td>
					</tr>
					<tr>
						<td>Product Name</td>
						<td>:</td>
						<td><?=strtoupper($getData[0]['nama_product']);?></td>
					</tr>
					<tr>
						<td>No SPK</td>
						<td>:</td>
						<td><?=strtoupper($getData[0]['no_spk']);?></td>
					</tr>
					<tr>
						<td>Qty Produksi</td>
						<td>:</td>
						<td><?=number_format($getData[0]['qty']);?></td>
					</tr>
					<tr>
						<td>Frequency Checking</td>
						<td>:</td>
						<td><b><?=strtoupper($checksheet_header[0]['frequency_check']);?></b></td>
					</tr>
                </table>
				<input type="hidden" id='id' name='id' value='<?=$id?>'>
				<input type="hidden" id='frequency' name='frequency' value='<?=$checksheet_header[0]['frequency_check']?>'>
				<input type="hidden" id='id_master' name='id_master' value='<?=$checksheet_header[0]['id']?>'>
				<input type="hidden" id='qty_ke' name='qty_ke' value='<?=$qty_ke?>'>
			</div>
        </div>
		<hr>
		<div class="form-group row">
        	<div class="col-md-12">
			<?php
			for ($i=1; $i <= $qty; $i++) {
				$disabledColor = ($qty_ke == $i)?'btn-success':'btn-default';
				echo "<a href='".base_url('report_checksheet/'.$this->uri->segment(2).'/'.$this->uri->segment(3).'/'.$i.'/'.$tanda)."' class='btn ".$disabledColor."' style='margin-right:5px; margin-bottom:5px;'>".$i."</a>";
			}
			?>
			</div>
		</div>
        <?php
        $hourly = ($checksheet_header[0]['frequency_check'] == 'hourly')?':00':'';
        $scroll = ($checksheet_header[0]['frequency_check'] == 'hourly')?'width:2000px; overflow-x:auto;':'';
        ?>
		<h4>List Checksheet</h4>
		<!-- <div class="form-group row">
        	<div class="col-md-12"> -->
		<div class='tableFixHead'>
			<table>
				<tr>
					<th class='text-center'  style='width:50px;'>#</th>
					<th class='text-left'  style='width:250px;'>Items</th>
                        <th class='text-left'  style='width:250px;'>Standard</th>
                        <?php
                        for ($i=1; $i <= $GET_LOOPING[$checksheet_header[0]['frequency_check']]; $i++) { 
							if($checksheet_header[0]['frequency_check'] == 'hourly'){
								if($i>=7 AND $i <=18){
									echo "<th class='text-left' style='width:200px;'>".$GET_LOOPING_LABEL[$checksheet_header[0]['frequency_check']].$i.$hourly."</th>";
								}
							}
							else{
								echo "<th class='text-left' style='width:200px;'>".$GET_LOOPING_LABEL[$checksheet_header[0]['frequency_check']].$i.$hourly."</th>";
							}
                        }
                        ?>
				</tr>
				<?php
				foreach ($checksheet_detail as $key => $value) { $key++;
					echo "<tr>";
						echo "<td class='text-center'>".$key."</td>";
						echo "<td>".$value['items']."</td>";
						echo "<td>".$value['standard']."</td>";
						for ($i=1; $i <= $GET_LOOPING[$checksheet_header[0]['frequency_check']]; $i++) { 
							if($checksheet_header[0]['frequency_check'] == 'hourly'){
								if($i>=7 AND $i <=18){
									$UNIQ = $value['id'].'-'.$i.'-'.$id.'-'.$checksheet_header[0]['frequency_check'].'-'.$qty_ke;
								$id_uniq 	= (!empty($GET_DATA_SHEET[$UNIQ]['id']))?$GET_DATA_SHEET[$UNIQ]['id']:'';
								$ket 		= (!empty($GET_DATA_SHEET[$UNIQ]['ket']))?$GET_DATA_SHEET[$UNIQ]['ket']:'';
								$reason 	= (!empty($GET_DATA_SHEET[$UNIQ]['reason']))?$GET_DATA_SHEET[$UNIQ]['reason']:'';

								$checked_Y = ($ket == 'Y')?'checked':'';
								$checked_N = ($ket == 'N')?'checked':'';

								$checked_D = '';
								if($checked_Y == '' AND $checked_N == ''){
									$checked_D = 'checked';
								}

								echo "<td>";
									echo "<input type='hidden' name='datail[".$key."-".$i."][id_detail]' value='".$value['id']."'>";
									echo "<input type='hidden' name='datail[".$key."-".$i."][id_kolom]' value='".$i."'>";

									echo "<input type='hidden' name='datail[".$key."-".$i."][id]' value='".$id_uniq."'>";
									if($value['tipe'] == '1'){
										echo "<input type='radio' name='datail[".$key."-".$i."][ket]' value='Y' ".$checked_Y.">";
										echo "&nbsp;Yes&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
										echo "<input type='radio' name='datail[".$key."-".$i."][ket]' value='N' ".$checked_N.">";
										echo "&nbsp;No";
										echo "<input type='text' class='form-control input-sm' name='datail[".$key."-".$i."][reason]' value='".$reason."' placeholder='Reason'>";
									}
									else{
										echo "<textarea name='datail[".$key."-".$i."][ket]' class='form-control input-sm' rows='2' placeholder='Keterangan'>".$ket."</textarea>";
									}
								echo "</td>";
								}
							}
							else{
								$UNIQ = $value['id'].'-'.$i.'-'.$id.'-'.$checksheet_header[0]['frequency_check'].'-'.$qty_ke;
								$id_uniq 	= (!empty($GET_DATA_SHEET[$UNIQ]['id']))?$GET_DATA_SHEET[$UNIQ]['id']:'';
								$ket 		= (!empty($GET_DATA_SHEET[$UNIQ]['ket']))?$GET_DATA_SHEET[$UNIQ]['ket']:'';
								$reason 	= (!empty($GET_DATA_SHEET[$UNIQ]['reason']))?$GET_DATA_SHEET[$UNIQ]['reason']:'';

								$checked_Y = ($ket == 'Y')?'checked':'';
								$checked_N = ($ket == 'N')?'checked':'';

								$checked_D = '';
								if($checked_Y == '' AND $checked_N == ''){
									$checked_D = 'checked';
								}

								echo "<td>";
									echo "<input type='hidden' name='datail[".$key."-".$i."][id_detail]' value='".$value['id']."'>";
									echo "<input type='hidden' name='datail[".$key."-".$i."][id_kolom]' value='".$i."'>";

									echo "<input type='hidden' name='datail[".$key."-".$i."][id]' value='".$id_uniq."'>";
									if($value['tipe'] == '1'){
										echo "<input type='radio' name='datail[".$key."-".$i."][ket]' value='Y' ".$checked_Y.">";
										echo "&nbsp;Yes&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
										echo "<input type='radio' name='datail[".$key."-".$i."][ket]' value='N' ".$checked_N.">";
										echo "&nbsp;No";
										echo "<input type='text' class='form-control input-sm' name='datail[".$key."-".$i."][reason]' value='".$reason."' placeholder='Reason'>";
									}
									else{
										echo "<textarea name='datail[".$key."-".$i."][ket]' class='form-control input-sm' rows='2' placeholder='Keterangan'>".$ket."</textarea>";
									}
								echo "</td>";
							}
						}
					echo "</tr>";
				}
				?>
			</table>
		</div>
		<!-- </div>
		</div> -->
		<div class="form-group row">
			<div class="col-md-6">
				<?php if(empty($tanda)){ ?>
					<button type="button" class="btn btn-primary" name="save" id="save">Save</button>
				<?php
				}
				?>
				<button type="button" class="btn btn-danger" style='margin-left:5px;' name="back" id="back">Back</button>
			</div>
        </div>
		</form>
	</div>
</div>


<script src="<?= base_url('assets/js/jquery.maskMoney.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>
<style>
    .datepicker{
        cursor: pointer;
    }
	h1 {
		color: Green;
	}

	.table { border-collapse: collapse; width: 100%; }
  .td { background: #fff; padding: 8px 16px; }

  .tableFixHead {
    overflow: auto;
    height: 300px;
    position: sticky;
    top: 0;
  }

  .thead .th {
    position: sticky;
    top: 0;
    z-index: 9999;
  	background: #0073b7;
	vertical-align: top;
  }

	/* div.scroll {
		margin: 4px, 4px;
		padding: 4px;
		width: 300px;
		overflow-x: auto;
		overflow-y: hidden;
		white-space: nowrap;
	} */
</style>

<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';

	$(document).ready(function(){
		$('.chosen-select').select2();
    	$('.autoNumeric4').autoNumeric('init', {mDec: '4', aPad: false})
    	$('.autoNumeric0').autoNumeric('init', {mDec: '0', aPad: false})
        $('.datepicker').datepicker({ dateFormat: 'dd-M-yy' });

    	//back
		$(document).on('click', '#back', function(){
		    window.location.href = base_url + active_controller
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
						var baseurl=siteurl+active_controller+'/process_input_checksheet';
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
									
								}
								else{
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



</script>
