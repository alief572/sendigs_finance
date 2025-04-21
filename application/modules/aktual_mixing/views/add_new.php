
<div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post" autocomplete="off"><br>
		<div class="form-group row">
        	<div class="col-md-12">
				<table width='50%'>
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
						<td>From Warehouse</td>
						<td>:</td>
						<td><?=strtoupper(get_name('warehouse','nm_gudang','id',$getData[0]['id_gudang']));?></td>
					</tr>
					<tr>
						<td>For Costcenter</td>
						<td>:</td>
						<td><?=strtoupper(get_name('ms_costcenter','nama_costcenter','id_costcenter',$getData[0]['id_costcenter']));?></td>
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
					<tr>
						<td>Tipe Mixing</td>
						<td>:</td>
						<td><?=$tipe_mixing_name;?></td>
					</tr>
				</table>
				<input type="hidden" id='id_uniq' name='id_uniq' value='<?=$getData[0]['id_uniq']?>'>
				<input type="hidden" id='qty_produksi' name='qty_produksi' value='<?=$getData[0]['qty']?>'>
				<input type="hidden" id='kode' name='kode' value='<?=$kode?>'>
				<input type="hidden" id='tipe_mixing_set' name='tipe_mixing_set' value='<?=$tipe_mixing_set?>'>
				<input type="hidden" id='id' name='id' value='<?=$id?>'>
				<input type="hidden" id='qty_ke' name='qty_ke'>
			</div>
        </div>
		<hr>
		<div class="form-group row">
        	<div class="col-md-12">
			<?php
			for ($i=1; $i <= $qty; $i++) { 
				$keyNew = $kode.'-'.$i;
				$disabledColor = (!empty($checkInputMixing[$keyNew]))?'btn-success':'btn-default';
				echo "<button type='button' class='btn ".$disabledColor." qty_ke' data-id='".$i."' style='margin-right:5px;'>".$i."</button>";
			}
			?>
			</div>
		</div>
		
		<span id='alertLabel'><p class='text-danger text-bold'>Data sudah disimpan !</p></span>
		<span id='alertLabelClose'><p class='text-success text-bold'>Sudah close Produksi !</p></span>
		<!-- <div class="form-group row">
			<div class="col-md-12 text-danger text-bold">
				<input type="checkbox" name='check_close' id='check_close' value='1'> Selesai input aktual mixing
			</div>
		</div> -->
		<div class="form-group row" id='TandaTipeMixing'>
			<div class="col-md-3">
				<div class="form-group">
					<label>Qty Mixing <span class='text-primary'>(Qty Mixing di hitung dari nomor yang dipilih)</span></label>
					<input type="text" name='qty_mixing' id='qty_mixing' class="form-control autoNumeric0">
					<span class='text-danger' id='keteranganMixing'></span>
				</div>
			</div>
			
		</div>
		<div class="form-group row" id='listInput'>
			
        	<div class="col-md-12">
				<table class="table table-bordered table-striped" width='100%'>
					<tr>
						<th class='text-center'>Nama Material</th>
						<th class='text-right' width='8%'>Stok (kg)</th>
						<th class='text-right' width='8%'>Kebutuhan (kg)</th>
						<th class='text-center' width='8%'>Mix1</th>
						<th class='text-center' width='8%'>Mix2</th>
						<th class='text-center' width='8%'>Mix3</th>
						<th class='text-center' width='8%'>Mix4</th>
						<th class='text-center' width='8%'>Mix5</th>
						<th class='text-center' width='8%'>Mix6</th>
						<th class='text-center' width='8%'>Mix7</th>
						<th class='text-center' width='8%'>Aktual (kg)</th>
					</tr>
					<?php
					foreach ($getMaterialMixing as $key => $value) {
						$id_material 	= $value['code_material'];
						$stock      	= (!empty($GET_STOK[$id_material]['stok']))?$GET_STOK[$id_material]['stok']:0;
						$nm_material    = (!empty($GET_MATERIAL[$id_material]['nama']))?$GET_MATERIAL[$id_material]['nama']:0;
						$berat			= $value['berat'];
						echo "<tr>";
							echo "<td>".$nm_material."</td>";
							echo "<td class='text-right'>".number_format($stock,4)."</td>";
							echo "<td class='text-right'>".number_format($berat,4)."</td>";
                            echo "<td><input type='text' name='detail[".$key."][mix1]' id='mix1_".$value['id']."' class='form-control autoNumeric4 text-center sumRight'></td>";
                            echo "<td><input type='text' name='detail[".$key."][mix2]' id='mix2_".$value['id']."' class='form-control autoNumeric4 text-center sumRight'></td>";
                            echo "<td><input type='text' name='detail[".$key."][mix3]' id='mix3_".$value['id']."' class='form-control autoNumeric4 text-center sumRight'></td>";
                            echo "<td><input type='text' name='detail[".$key."][mix4]' id='mix4_".$value['id']."' class='form-control autoNumeric4 text-center sumRight'></td>";
                            echo "<td><input type='text' name='detail[".$key."][mix5]' id='mix5_".$value['id']."' class='form-control autoNumeric4 text-center sumRight'></td>";
                            echo "<td><input type='text' name='detail[".$key."][mix6]' id='mix6_".$value['id']."' class='form-control autoNumeric4 text-center sumRight'></td>";
                            echo "<td><input type='text' name='detail[".$key."][mix7]' id='mix7_".$value['id']."' class='form-control autoNumeric4 text-center sumRight'></td>";
							echo "<td>
									<input type='hidden' name='detail[".$key."][id]' value='".$value['id']."'>
									<input type='hidden' name='detail[".$key."][code_material]' value='".$value['code_material']."'>
									<input type='hidden' name='detail[".$key."][berat]' id='est_".$value['id']."' value='".$berat."'>
									<input type='hidden' name='detail[".$key."][code_material_aktual]' value='".$value['code_material']."'>
									<input type='text' name='detail[".$key."][berat_aktual]' id='value_".$value['id']."' class='form-control autoNumeric4 text-center totalMixing' readonly value='".$berat."'>
									</td>";
						echo "</tr>";
					}
					?>
				</table>
			</div>
        </div>
		
		<div class="form-group row">
			<div class="col-md-6">
				<button type="button" class="btn btn-primary" name="save" id="save">Save</button>
				<button type="button" class="btn btn-danger" style='margin-left:5px;' name="back" id="back">Back</button>
			</div>
			<div class="col-md-6 text-right" >
				<button type="button" class="btn btn-primary" name="saveClose" id="saveClose">Close Input Produksi</button>
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
		$('#listInput').hide()
		$('#alertLabel').hide()
		$('#alertLabelClose').hide()
		$('#save').hide()
		$('#saveClose').hide()
		$('#TandaTipeMixing').hide()

    	//back
		$(document).on('click', '#back', function(){
		    window.location.href = base_url + active_controller
		});

        $(document).on('keyup', '.sumRight', function(){
		    let HTML = $(this).parent().parent();
            let nilai
            let SUM = 0
            HTML.find('.sumRight').each(function(){
                nilai = getNum($(this).val().split(',').join(''))
                SUM += nilai
            })
            HTML.find('.totalMixing').val(SUM)
            // console.log(SUM)
		});

		$("#check_close").change(function() {
			if(this.checked) {
				$('#saveClose').show()
			}
			else{
				$('#saveClose').hide()
			}
		});

		$(document).on('change','#qty_mixing', function(){
			var qty_produksi 	= getNum($('#qty_produksi').val())
			var qty_ke 			= getNum($('#qty_ke').val())
			var qty_first 		= qty_ke - 1
			var qty_mixing 		= getNum($('#qty_mixing').val().split(',').join(''))
			var maksimal = qty_ke + qty_mixing;

			// console.log(maksimal)
			
			let HTML_Val = '';
			let nomorMax = 0;
			for (let i = qty_ke; i < maksimal; i++) {
				if(i <= qty_produksi){
					HTML_Val += i + ", ";
					nomorMax++
				}
			}

			// console.log(nomorMax)

			$('#keteranganMixing').text(`Laporan mixing untuk qty: ${HTML_Val}`)
			$(this).val(nomorMax)


		});

		$(document).on('click','.qty_ke', function(){
			let id = $(this).data('id')
			let kode = $('#kode').val()

			var tipe_mixing_set = $('#tipe_mixing_set').val()
			var qty_mixing = (tipe_mixing_set == '1')?'1':'';
			$('#qty_mixing').val(qty_mixing)
			$('#keteranganMixing').text('')
			if(tipe_mixing_set == '1'){
				$('#TandaTipeMixing').hide()
			}
			else{
				$('#TandaTipeMixing').show()
			}
			
			let id2
			$('#listInput').show()
			$('#qty_ke').val(id)

			$('.qty_ke').each(function(){
				id2 = $(this).data('id')
				if(id == id2){
					$(this).css("background-color", "#3c8dbc");
					$(this).css("color", "white");
				}
				else{
					$(this).css("background-color", "#f4f4f4");
					$(this).css("color", "#444");
				}
			})

			var baseurl = siteurl + active_controller + '/getChangeMaterialMixing';
			$.ajax({
				url			: baseurl,
				type		: "POST",
				data		: {
					'id' : id,
					'kode' : kode,
					'tipe_mixing_set' : tipe_mixing_set
				},
				cache		: false,
				dataType	: 'json',
				success		: function(data){
					if(data.arrayData != 0){
						if(tipe_mixing_set == '2'){
							$('#TandaTipeMixing').hide()
						}
						$('#qty_mixing').val(1)
						// $('#save').hide()
						$('#alertLabel').show()
						data.arrayData.map((row,idx)=>{
							$('#value_'+row.id_det_spk).val(row.weight_aktual)
                            $('#mix1_'+row.id_det_spk).val(row.mix1)
							$('#mix2_'+row.id_det_spk).val(row.mix2)
							$('#mix3_'+row.id_det_spk).val(row.mix3)
							$('#mix4_'+row.id_det_spk).val(row.mix4)
							$('#mix5_'+row.id_det_spk).val(row.mix5)
							$('#mix6_'+row.id_det_spk).val(row.mix6)
							$('#mix7_'+row.id_det_spk).val(row.mix7)
							// if(row.close == 'Y'){
							// 	$('#save').hide()
							// 	$('#alertLabel').hide()
							// 	$('#alertLabelClose').show()
							// }
							// else{
							// 	$('#save').show()
							// 	$('#alertLabel').show()
							// 	$('#alertLabelClose').hide()
							// }

							$('#save').hide()
							$('#alertLabel').hide()
						})
					}
					else{
						if(tipe_mixing_set == '2'){
						$('#TandaTipeMixing').show()
						}
						$('#save').show()
						$('#alertLabel').hide()
						$('#alertLabelClose').hide()
						data.ArrayIDData.map((row,idx)=>{
							$('#value_'+row.id_det_spk).val(row.weight)
							$('#mix1_'+row.id_det_spk).val('')
							$('#mix2_'+row.id_det_spk).val('')
							$('#mix3_'+row.id_det_spk).val('')
							$('#mix4_'+row.id_det_spk).val('')
							$('#mix5_'+row.id_det_spk).val('')
							$('#mix6_'+row.id_det_spk).val('')
							$('#mix7_'+row.id_det_spk).val('')
						})
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
		})

		$('#save').click(function(e){
			e.preventDefault();

			var qty_mixing 		= getNum($('#qty_mixing').val().split(',').join(''))

			if(qty_mixing < 1){
				swal({
				  title	: "Error Message!",
				  text	: 'Qty Mixing wajib diisi ...',
				  type	: "warning"
				});
				return false;
				
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
						var baseurl=siteurl+active_controller+'/add_new';
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
										if(data.check_close == '0'){
											window.location.href = base_url + active_controller + '/add_new/' + data.id
										}
										else{
											window.location.href = base_url + active_controller
										}
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

		$('#saveClose').click(function(e){
			e.preventDefault();
			swal({
				  title: "Are you sure?",
				  text: "Close input aktual mixing !",
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
						var baseurl=siteurl+active_controller+'/saveClose';
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



</script>
