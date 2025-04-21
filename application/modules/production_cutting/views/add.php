<?php
$costcenter = ucwords(get_name('ms_costcenter','nama_costcenter','id_costcenter',$getData[0]['id_costcenter']));
?>
<div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post" autocomplete="off"><br>
		<div class="form-group row">
        	<div class="col-md-12">
				<table width='100%'>
					<tr>
						<td width='15%'>Sales Order</td>
						<td width='1%'>:</td>
						<td><?=$getData[0]['no_so'];?></td>
					</tr>
					<tr>
						<td>Product Name</td>
						<td>:</td>
						<td><?=strtoupper($NamaProduct);?></td>
					</tr>
					<tr>
						<td>No SPK</td>
						<td>:</td>
						<td><?=strtoupper($getData[0]['no_spk']);?></td>
					</tr>
					<tr>
						<td>Qty Laporan</td>
						<td>:</td>
						<td><?=number_format($getData[0]['qty_outgoing']);?> dari jumlah produksi <?=number_format($getData[0]['qty']);?></td>
					</tr>
					<!-- <tr>
						<td>From Warehouse</td>
						<td>:</td>
						<td><?=strtoupper(get_name('warehouse','nm_gudang','id',$getData[0]['id_gudang']));?></td>
					</tr> -->
					<tr>
						<td>Costcenter</td>
						<td>:</td>
						<td><?=$costcenter;?></td>
					</tr>
                    <tr>
						<td>Plan Produksi</td>
						<td>:</td>
						<td><?=tgl_indo($getData[0]['tanggal']);?></td>
					</tr>
					<tr>
						<td>Est. Finish</td>
						<td>:</td>
						<td><?=tgl_indo($getData[0]['tanggal_est_finish']);?></td>
					</tr>
				</table>
				<input type="hidden" id='qty_produksi' name='qty_produksi' value='<?=$getData[0]['qty']?>'>
				<input type="hidden" id='kode' name='kode' value='<?=$kode?>'>
				<input type="hidden" id='id' name='id' value='<?=$id?>'>
				<input type="hidden" id='qty_ke' name='qty_ke'>
			</div>
        </div>
		<hr>
		<div class="form-group row" id='listInput'>
			<div class="col-md-12">
				<table class="table table-bordered" width='100%'>
					<?php
						$GET_ACTUAL = getActualFtackle($id);
						$GET_ACTUAL_MAT = getActualFtackleMaterial($id);
						$nomor = -2;
						$QTY_BELUM = 0;
						foreach ($cycletime as $key => $value) { 
							$key++;
							$UkuranCutting = number_format($value['length'],2).' x '.number_format($value['width'],2);
							$QtyPotong = number_format($value['qty']);

							$checkQtySelesai = $this->db->get_where('so_spk_cutting_product',array('id_key_spk'=>$value['id'],'kode_det'=>$id))->result_array();
							$qty_selesai = COUNT($checkQtySelesai);
							$qty_sisa = $value['qty'] - $qty_selesai;
							$QTY_BELUM += $qty_sisa;
							echo "<tr>";
								echo "<td class='text-bold bg-primary' align='center'>".$key."</td>";
								echo "<td class='text-bold bg-primary' align='left' colspan='2'>Ukuran : ".$UkuranCutting."</td>";
								echo "<td class='text-bold bg-primary' align='left' colspan='2'>Qty Potong : ".$QtyPotong."</td>";
								echo "<td class='text-bold bg-primary' align='center'></td>";
							echo "</tr>";
							echo "<tr>";
								echo "<td align='center'></td>";
								echo "<td align='left' colspan='2'></td>";
								echo "<td align='center'  width='12%'>
										<div class='form-group'>
										<label>Tgl. Selesai</label>
										<input type='hidden' name='detailCutting[".$key."][id]' value='".$value['id']."'>
										<input type='hidden' name='detailCutting[".$key."][length]' value='".$value['length']."'>
										<input type='hidden' name='detailCutting[".$key."][width]' value='".$value['width']."'>
										<input type='hidden' name='detailCutting[".$key."][lari]' value='".$value['lari']."'>
										<input type='hidden' name='detailCutting[".$key."][no_bom]' value='".$getData[0]['no_bom']."'>
										<input type='text' name='detailCutting[".$key."][tanggal]' class='form-control text-center input-sm datepicker' readonly>
										</div>
									</td>";
								echo "<td align='center'>
										<div class='form-group'>
										<label>Qty Selesai</label>
										<input type='text' name='detailCutting[".$key."][qty]' data-id='".$key."' id='qty".$key."' class='form-control text-center input-sm autoNumeric0 changeQty' >
										</div>
									</td>";
								echo "<td align='center'>
										<div class='form-group'>
										<label>Qty Belum Selesai</label>
										<input type='text' name='detailCutting[".$key."][qty_belum]' id='qtybelum".$key."' class='form-control text-center input-sm autoNumeric0' readonly value='".$qty_sisa."'>
										</div>
									</td>";
							echo "</tr>";
						}

						//input aktual material
						echo "<tr>";
							echo "<th class='text-center' width='3%'></th>";
							echo "<th class='text-left'  width='17%'>Code</th>";
							echo "<th class='text-left'>Nama Material</th>";
							// echo "<th class='text-right' width='12%'>Stok SPK (kg)</th>";
							echo "<th class='text-right'  colspan='2' width='24%'><span class='text-green text-bold'>Aktual</span> / <span class='text-blue text-bold'>Est</span> / <span class='text-red text-bold'>Sisa</span> (kg)</th>";
							echo "<th class='text-center' width='12%'>Aktual (kg)</th>";
						echo "</tr>";
						$key2 = 0;
						if(!empty($getMaterialNonMixing)){
							echo "<tr>";
								echo "<th></th>";
								echo "<th class='bg-green' colspan='5'>Non Mixing</th>";
							echo "</tr>";
							foreach ($getMaterialNonMixing as $key2 => $value) { $key2++;
								$id_material 	= $value['code_material'];
								// $stock      	= (!empty($GET_STOK[$id_material]['stok']))?$GET_STOK[$id_material]['stok']:0;
								// $stock      	= (!empty($ArrStokSPK[$id_material]))?$ArrStokSPK[$id_material]:0;
								$nm_material    = (!empty($GET_MATERIAL[$id_material]['nama']))?$GET_MATERIAL[$id_material]['nama']:0;
								$code_material  = (!empty($GET_MATERIAL[$id_material]['code']))?$GET_MATERIAL[$id_material]['code']:0;
								$berat			= $value['berat'];
								$UNIQ = $id.'-'.$value['id'];
								$qtyAktual = (!empty($GET_ACTUAL_MAT[$UNIQ]['aktual']))?$GET_ACTUAL_MAT[$UNIQ]['aktual']:0;

								$sisaLabel = ($berat - $qtyAktual > 0)?$berat - $qtyAktual:0;
								$sisa = '';

								// $addMat = ($value['add_material'] == 'add')?' <sup><span class="text-danger">Material Tambahan</span></sup>':'';
								$addMat = '';

								echo "<tr>";
									echo "<td class='text-center'></td>";
									echo "<td>".$code_material."</td>";
									echo "<td>".$nm_material.$addMat."</td>";
									// $berat_sisa = ($stock-$qtyAktual > 0)?$stock-$qtyAktual:0;
									// echo "<td class='text-right'>".number_format($berat_sisa,4)."</td>";
									echo "<td class='text-right'  colspan='2'><span class='text-green text-bold'>".number_format($qtyAktual,4)."</span> / <span class='text-blue text-bold'>".number_format($berat,4)."</span> / <span class='text-red text-bold'>".number_format($sisaLabel,4)."</span></td>";
									echo "<td>
											<input type='hidden' name='detailCuttingMaterial[".$key."][detail][".$key2."][id]' value='".$value['id']."'>
											<input type='hidden' name='detailCuttingMaterial[".$key."][detail][".$key2."][code_material]' value='".$value['code_material']."'>
											<input type='hidden' name='detailCuttingMaterial[".$key."][detail][".$key2."][berat]' id='est_".$value['id']."' value='".$berat."'>
											<input type='hidden' name='detailCuttingMaterial[".$key."][detail][".$key2."][code_material_aktual]' value='".$value['code_material']."'>
											<input type='text' name='detailCuttingMaterial[".$key."][detail][".$key2."][berat_aktual]' id='value_".$value['id']."' class='form-control autoNumeric4 text-center input-sm' value='".$sisa."'>
											</td>";
								echo "</tr>";
							}
						}

						if(!empty($getMaterialMixing)){
							echo "<tr>";
								echo "<th></th>";
								echo "<th class='bg-purple' colspan='5'>Mixing</th>";
							echo "</tr>";
							$nextNumber = $key2;
							foreach ($getMaterialMixing as $key2 => $value) { $nextNumber++;
								$id_material 	= $value['code_material'];
								// $stock      	= (!empty($GET_STOK[$id_material]['stok']))?$GET_STOK[$id_material]['stok']:0;
								// $stock      	= (!empty($ArrStokSPK[$id_material]))?$ArrStokSPK[$id_material]:0;
								$nm_material    = (!empty($GET_MATERIAL[$id_material]['nama']))?$GET_MATERIAL[$id_material]['nama']:0;
								$code_material  = (!empty($GET_MATERIAL[$id_material]['code']))?$GET_MATERIAL[$id_material]['code']:0;
								$berat			= $value['berat'];
								$UNIQ = $id.'-'.$value['id'];
								$qtyAktual = (!empty($GET_ACTUAL_MAT[$UNIQ]['aktual']))?$GET_ACTUAL_MAT[$UNIQ]['aktual']:0;

								$sisaLabel = ($berat - $qtyAktual > 0)?$berat - $qtyAktual:0;
								$sisa = '';

								// $addMat = ($value['add_material'] == 'add')?' <sup><span class="text-danger">Material Tambahan</span></sup>':'';
								$addMat = '';

								echo "<tr>";
									echo "<td class='text-center'></td>";
									echo "<td>".$code_material."</td>";
									echo "<td>".$nm_material.$addMat."</td>";
									// $berat_sisa = ($stock-$qtyAktual > 0)?$stock-$qtyAktual:0;
									// echo "<td class='text-right'>".number_format($berat_sisa,4)."</td>";
									echo "<td class='text-right' colspan='2'><span class='text-green text-bold'>".number_format($qtyAktual,4)."</span> / <span class='text-blue text-bold'>".number_format($berat,4)."</span> / <span class='text-red text-bold'>".number_format($sisaLabel,4)."</span></td>";
									echo "<td>
											<input type='hidden' name='detailCuttingMaterial[".$key."][detail][".$nextNumber."][id]' value='".$value['id']."'>
											<input type='hidden' name='detailCuttingMaterial[".$key."][detail][".$nextNumber."][code_material]' value='".$value['code_material']."'>
											<input type='hidden' name='detailCuttingMaterial[".$key."][detail][".$nextNumber."][berat]' id='est_".$value['id']."' value='".$berat."'>
											<input type='hidden' name='detailCuttingMaterial[".$key."][detail][".$nextNumber."][code_material_aktual]' value='".$value['code_material']."'>
											<input type='text' name='detailCuttingMaterial[".$key."][detail][".$nextNumber."][berat_aktual]' id='value_".$value['id']."' class='form-control autoNumeric4 text-center input-sm' value='".$sisa."'>
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
				<?php if($QTY_BELUM > 0){ ?>
				<button type="button" class="btn btn-primary" name="save" id="save">Close</button>
				<?php } ?>
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
						var baseurl=siteurl+active_controller+'/process_input_produksi_cutting';
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
