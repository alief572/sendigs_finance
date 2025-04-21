
<div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post" autocomplete="off"><br>
		<div class="form-group row">
        	<div class="col-md-12">
				<table width='100%'>
					<tr>
						<td width='15%'>Sales Order</td>
						<td width='1%'>:</td>
						<td><?=$getData[0]['so_customer'];?></td>
					</tr>
					<tr>
						<td>Nm Customer</td>
						<td>:</td>
						<td><?=strtoupper($getData[0]['nm_customer']);?></td>
					</tr>
				</table>
				<input type="hidden" id='id' name='id' value='<?=$id?>'>
			</div>
        </div>
		<hr>
		<div class="form-group row" id='listInput'>
			<div class="col-md-12">
				<table class="table table-bordered" width='100%'>
					<?php
						//input aktual material
						echo "<tr>";
							echo "<th class='text-center' width='3%'></th>";
							echo "<th class='text-left'  width='17%'>Code</th>";
							echo "<th class='text-left'>Nama Material</th>";
							echo "<th class='text-right'  colspan='2' width='24%'><span class='text-green text-bold'>Aktual</span> / <span class='text-blue text-bold'>Est</span> / <span class='text-red text-bold'>Sisa</span> (kg)</th>";
							echo "<th class='text-center' width='12%'>Aktual (kg)</th>";
						echo "</tr>";
						if(!empty($listMaterialJoint)){
							echo "<tr>";
								echo "<th></th>";
								echo "<th class='bg-green' colspan='5'>A. Material Joint & Finishing</th>";
							echo "</tr>";
							foreach ($listMaterialJoint as $key2 => $value) { $key2++;
								$id_material 	= $value['id_material'];
								$nm_material    = (!empty($GET_MATERIAL[$id_material]['nama']))?$GET_MATERIAL[$id_material]['nama']:0;
								$code_material  = (!empty($GET_MATERIAL[$id_material]['code']))?$GET_MATERIAL[$id_material]['code']:0;
								$berat			= $value['qty'];
								$qtyAktual 		= 0;

								$sisaLabel = ($berat - $qtyAktual > 0)?$berat - $qtyAktual:0;
								$sisa = '';
								$addMat = '';

								echo "<tr>";
									echo "<td class='text-center'></td>";
									echo "<td>".$code_material."</td>";
									echo "<td>".$nm_material.$addMat."</td>";
									echo "<td class='text-right'  colspan='2'><span class='text-green text-bold'>".number_format($qtyAktual,4)."</span> / <span class='text-blue text-bold'>".number_format($berat,4)."</span> / <span class='text-red text-bold'>".number_format($sisaLabel,4)."</span></td>";
									echo "<td>
											<input type='hidden' name='detailJoint[".$key2."][id]' value='".$value['id']."'>
											<input type='hidden' name='detailJoint[".$key2."][code_material]' value='".$id_material."'>
											<input type='hidden' name='detailJoint[".$key2."][berat]' id='est_".$value['id']."' value='".$berat."'>
											<input type='hidden' name='detailJoint[".$key2."][code_material_aktual]' value='".$id_material."'>
											<input type='text' name='detailJoint[".$key2."][berat_aktual]' id='value_".$value['id']."' class='form-control autoNumeric4 text-center input-sm' value='".$sisa."'>
											</td>";
								echo "</tr>";
							}
						}

						if(!empty($listMaterialFlat)){
							echo "<tr>";
								echo "<th></th>";
								echo "<th class='bg-green' colspan='5'>B. Flat Sheet</th>";
							echo "</tr>";
							foreach ($listMaterialFlat as $key2 => $value) { $key2++;
								$id_material 	= $value['id_material'];
								$nm_material    = (!empty($GET_MATERIAL[$id_material]['nama']))?$GET_MATERIAL[$id_material]['nama']:0;
								$code_material  = (!empty($GET_MATERIAL[$id_material]['code']))?$GET_MATERIAL[$id_material]['code']:0;
								$berat			= $value['qty'];
								$qtyAktual 		= 0;

								$sisaLabel = ($berat - $qtyAktual > 0)?$berat - $qtyAktual:0;
								$sisa = '';
								$addMat = '';

								echo "<tr>";
									echo "<td class='text-center'></td>";
									echo "<td>".$code_material."</td>";
									echo "<td>".$nm_material.$addMat."</td>";
									echo "<td class='text-right'  colspan='2'><span class='text-green text-bold'>".number_format($qtyAktual,4)."</span> / <span class='text-blue text-bold'>".number_format($berat,4)."</span> / <span class='text-red text-bold'>".number_format($sisaLabel,4)."</span></td>";
									echo "<td>
											<input type='hidden' name='detailFlat[".$key2."][id]' value='".$value['id']."'>
											<input type='hidden' name='detailFlat[".$key2."][code_material]' value='".$id_material."'>
											<input type='hidden' name='detailFlat[".$key2."][berat]' id='est_".$value['id']."' value='".$berat."'>
											<input type='hidden' name='detailFlat[".$key2."][code_material_aktual]' value='".$id_material."'>
											<input type='text' name='detailFlat[".$key2."][berat_aktual]' id='value_".$value['id']."' class='form-control autoNumeric4 text-center input-sm' value='".$sisa."'>
											</td>";
								echo "</tr>";
							}
						}

						if(!empty($listMaterialEnd)){
							echo "<tr>";
								echo "<th></th>";
								echo "<th class='bg-green' colspan='5'>C. End Plate / Kick Plate</th>";
							echo "</tr>";
							foreach ($listMaterialEnd as $key2 => $value) { $key2++;
								$id_material 	= $value['id_material'];
								$nm_material    = (!empty($GET_MATERIAL[$id_material]['nama']))?$GET_MATERIAL[$id_material]['nama']:0;
								$code_material  = (!empty($GET_MATERIAL[$id_material]['code']))?$GET_MATERIAL[$id_material]['code']:0;
								$berat			= $value['qty'];
								$qtyAktual 		= 0;

								$sisaLabel = ($berat - $qtyAktual > 0)?$berat - $qtyAktual:0;
								$sisa = '';
								$addMat = '';

								echo "<tr>";
									echo "<td class='text-center'></td>";
									echo "<td>".$code_material."</td>";
									echo "<td>".$nm_material.$addMat."</td>";
									echo "<td class='text-right'  colspan='2'><span class='text-green text-bold'>".number_format($qtyAktual,4)."</span> / <span class='text-blue text-bold'>".number_format($berat,4)."</span> / <span class='text-red text-bold'>".number_format($sisaLabel,4)."</span></td>";
									echo "<td>
											<input type='hidden' name='detailEnd[".$key2."][id]' value='".$value['id']."'>
											<input type='hidden' name='detailEnd[".$key2."][code_material]' value='".$id_material."'>
											<input type='hidden' name='detailEnd[".$key2."][berat]' id='est_".$value['id']."' value='".$berat."'>
											<input type='hidden' name='detailEnd[".$key2."][code_material_aktual]' value='".$id_material."'>
											<input type='text' name='detailEnd[".$key2."][berat_aktual]' id='value_".$value['id']."' class='form-control autoNumeric4 text-center input-sm' value='".$sisa."'>
											</td>";
								echo "</tr>";
							}
						}

						if(!empty($listMaterialChequered)){
							echo "<tr>";
								echo "<th></th>";
								echo "<th class='bg-green' colspan='5'>D. Chequered Plate</th>";
							echo "</tr>";
							foreach ($listMaterialChequered as $key2 => $value) { $key2++;
								$id_material 	= $value['id_material'];
								$nm_material    = (!empty($GET_MATERIAL[$id_material]['nama']))?$GET_MATERIAL[$id_material]['nama']:0;
								$code_material  = (!empty($GET_MATERIAL[$id_material]['code']))?$GET_MATERIAL[$id_material]['code']:0;
								$berat			= $value['qty'];
								$qtyAktual 		= 0;

								$sisaLabel = ($berat - $qtyAktual > 0)?$berat - $qtyAktual:0;
								$sisa = '';
								$addMat = '';

								echo "<tr>";
									echo "<td class='text-center'></td>";
									echo "<td>".$code_material."</td>";
									echo "<td>".$nm_material.$addMat."</td>";
									echo "<td class='text-right'  colspan='2'><span class='text-green text-bold'>".number_format($qtyAktual,4)."</span> / <span class='text-blue text-bold'>".number_format($berat,4)."</span> / <span class='text-red text-bold'>".number_format($sisaLabel,4)."</span></td>";
									echo "<td>
											<input type='hidden' name='detailCheq[".$key2."][id]' value='".$value['id']."'>
											<input type='hidden' name='detailCheq[".$key2."][code_material]' value='".$id_material."'>
											<input type='hidden' name='detailCheq[".$key2."][berat]' id='est_".$value['id']."' value='".$berat."'>
											<input type='hidden' name='detailCheq[".$key2."][code_material_aktual]' value='".$id_material."'>
											<input type='text' name='detailCheq[".$key2."][berat_aktual]' id='value_".$value['id']."' class='form-control autoNumeric4 text-center input-sm' value='".$sisa."'>
											</td>";
								echo "</tr>";
							}
						}

						if(!empty($listMaterialOthers)){
							echo "<tr>";
								echo "<th></th>";
								echo "<th class='bg-green' colspan='5'>E. Others</th>";
							echo "</tr>";
							foreach ($listMaterialOthers as $key2 => $value) { $key2++;
								$id_material 	= $value['id_material'];
								$nm_material    = (!empty($GET_MATERIAL[$id_material]['nama']))?$GET_MATERIAL[$id_material]['nama']:0;
								$code_material  = (!empty($GET_MATERIAL[$id_material]['code']))?$GET_MATERIAL[$id_material]['code']:0;
								$berat			= $value['qty'];
								$qtyAktual 		= 0;

								$sisaLabel = ($berat - $qtyAktual > 0)?$berat - $qtyAktual:0;
								$sisa = '';
								$addMat = '';

								echo "<tr>";
									echo "<td class='text-center'></td>";
									echo "<td>".$code_material."</td>";
									echo "<td>".$nm_material.$addMat."</td>";
									echo "<td class='text-right'  colspan='2'><span class='text-green text-bold'>".number_format($qtyAktual,4)."</span> / <span class='text-blue text-bold'>".number_format($berat,4)."</span> / <span class='text-red text-bold'>".number_format($sisaLabel,4)."</span></td>";
									echo "<td>
											<input type='hidden' name='detailOthers[".$key2."][id]' value='".$value['id']."'>
											<input type='hidden' name='detailOthers[".$key2."][code_material]' value='".$id_material."'>
											<input type='hidden' name='detailOthers[".$key2."][berat]' id='est_".$value['id']."' value='".$berat."'>
											<input type='hidden' name='detailOthers[".$key2."][code_material_aktual]' value='".$id_material."'>
											<input type='text' name='detailOthers[".$key2."][berat_aktual]' id='value_".$value['id']."' class='form-control autoNumeric4 text-center input-sm' value='".$sisa."'>
											</td>";
								echo "</tr>";
							}
						}
					?>
				</table>
			</div>
            <div class="col-md-12">
				<?php
					
					?>
				</table>
			</div>
        </div>
		<div class="form-group row">
			<div class="col-md-6">
				<button type="button" class="btn btn-primary" name="save" id="save">Close</button>
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

		$(document).on('keyup', '.changeQty', function(){
		    let processName  = $(this).data('id')
			let qty 		= getNum($('#qty'+processName).val().split(',').join(''));
			let qtybelum 	= getNum($('#qtybelum'+processName).val().split(',').join(''));
			// console.log(qty)
			// console.log(qtybelum)
			if(qty > qtybelum){
				$(this).val(qtybelum)
			}
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
						var baseurl=siteurl+active_controller+'/process_input_produksi_assembly';
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
