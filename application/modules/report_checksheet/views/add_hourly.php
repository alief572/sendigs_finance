
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
		<h4>List Checksheet</h4>
		<div class="form-group row">
        	<div class="col-md-12">
				<table class="table table-bordered">
					<tr>
						<th class='text-center'  style='width:50px;'>#</th>
						<th class='text-left'  style='width:250px;'>Items</th>
						<th class='text-left'  style='width:250px;'>Standard</th>
					</tr>
					<?php
					foreach ($checksheet_detail as $key => $value) { $key++;
						$uniqRow = 'uniq'.$key;
						echo "<tr>";
							echo "<td class='text-center'>".$key."</td>";
							echo "<td class='text-bold text-primary'>".$value['items']."</td>";
							echo "<td class='text-bold text-primary'>".$value['standard']."</td>";
						echo "</tr>";
						echo "<tr>";
							echo "<td class='text-center'></td>";
							echo "<td colspan='2'>";
							echo "<table width='100%' class='table'>";
								echo "<tr>";
									echo "<th width='25%'>Jam</th>";
									echo "<th width='35%'>Keterangan</th>";
									echo "<th width='35%'>Reason</th>";
									echo "<th width='5%'></th>";
								echo "</tr>";
								$val = 0;

								$getDataDetail = $this->db->get_where('so_internal_checksheet',array('id_spk'=>$id,'id_detail'=>$value['id'],'qty_ke'=>$qty_ke))->result_array();
								foreach ($getDataDetail as $key2 => $value2) { $val++;
									$UNIQ 		= $value['id'].'-'.$value2['id_kolom'].'-'.$id.'-'.$checksheet_header[0]['frequency_check'].'-'.$qty_ke;
									$id_uniq 	= (!empty($GET_DATA_SHEET[$UNIQ]['id']))?$GET_DATA_SHEET[$UNIQ]['id']:'';
									$ket 		= (!empty($GET_DATA_SHEET[$UNIQ]['ket']))?$GET_DATA_SHEET[$UNIQ]['ket']:'';
									$reason 	= (!empty($GET_DATA_SHEET[$UNIQ]['reason']))?$GET_DATA_SHEET[$UNIQ]['reason']:'';
									$text_kolom = (!empty($GET_DATA_SHEET[$UNIQ]['text_kolom']))?$GET_DATA_SHEET[$UNIQ]['text_kolom']:'';

									$checked_Y = ($ket == 'Y')?'checked':'';
									$checked_N = ($ket == 'N')?'checked':'';

									$checked_D = '';
									if($checked_Y == '' AND $checked_N == ''){
										$checked_D = 'checked';
									}

									echo "<tr class='header".$uniqRow."_".$val."'>";
										echo "<td>";
											echo "<input type='text' class='form-control input-sm' name='datail[".$key."-".$val."][text_kolom]' placeholder='Jam' value='".$text_kolom."'>";
											echo "<input type='hidden' name='datail[".$key."-".$val."][id_detail]' value='".$value['id']."'>";
											echo "<input type='hidden' name='datail[".$key."-".$val."][id_kolom]' value='".$val."'>";
											echo "<input type='hidden' name='datail[".$key."-".$val."][id]' value='".$id_uniq."'>";
										echo "</td>";
										if($value['tipe'] == '1'){
										echo "<td>";
											echo "<input type='radio' name='datail[".$key."-".$val."][ket]' value='Y' ".$checked_Y.">";
											echo "&nbsp;Yes&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
											echo "<input type='radio' name='datail[".$key."-".$val."][ket]' value='N' ".$checked_N.">";
											echo "&nbsp;No";
										echo "</td>";
										}
										else{
										echo "<td>";
											echo "<input type='text' name='datail[".$key."-".$val."][ket]' class='form-control input-sm' placeholder='Keterangan' value='".$ket."'>";
										echo "</td>";
										
										}
										echo "<td>";
											echo "<input type='text' class='form-control input-sm' name='datail[".$key."-".$val."][reason]' placeholder='Reason' value='".$reason."'>";
										echo "</td>";
										if(empty($tanda)){
										echo "<td align='left'>";
											echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
										echo "</td>";
										}
									echo "</tr>";
								}
								if(empty($tanda)){
									echo "<tr id='add".$uniqRow."_".$val."'>";
										echo "<th><button type='button' class='btn btn-sm btn-warning addPart' data-key='".$key."' data-uniq='".$uniqRow."' data-tipe='".$value['tipe']."' data-id_detail='".$value['id']."' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add</button></th>";
										echo "<th></th>";
										echo "<th></th>";
										echo "<th></th>";
									echo "</tr>";
								}
							echo "</table>";
							echo "</td>";
						echo "</tr>";
					}
					?>
				</table>
			</div>
		</div>
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

		//add part
		$(document).on('click', '.addPart', function(){
			// loading_spinner();
			var get_id 		= $(this).parent().parent().attr('id');
			var tipe 		= $(this).data('tipe');
			var id_detail 	= $(this).data('id_detail');
			var uniq 		= $(this).data('uniq');
			var key 		= $(this).data('key');
			// console.log(get_id);
			var split_id	= get_id.split('_');
			var id 			= parseInt(split_id[1])+1;
			var id_bef0 	= split_id[0];
			var id_bef 		= split_id[1];

			$.ajax({
				url: base_url+active_controller+'/get_add/'+id+'/'+tipe+'/'+id_detail+'/'+uniq+'/'+key,
				cache: false,
				type: "POST",
				dataType: "json",
				success: function(data){
					$("#"+id_bef0+"_"+id_bef).before(data.header);
					$("#"+id_bef0+"_"+id_bef).remove();
					$('.chosen_select').select2({width: '100%'});
					$('.autoNumeric4').autoNumeric('init', {mDec: '4', aPad: false});
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
