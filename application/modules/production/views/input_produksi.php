
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
						<td><?=strtoupper($NamaProduct);?></td>
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
			$nomor_max=0;
			$HTML_NUMBER = array();
			for ($i=1; $i <= $qty; $i++) { 
				$keyNew = $kode.'-'.$i;
				$disabled = (!empty($checkInputMixing[$keyNew]))?'':'disabled';
				$disabledTitle = (!empty($checkInputMixing[$keyNew]))?'':'Belum input mixing!';
				$disabledColor = (!empty($checkInputMixing[$keyNew]))?'btn-default':'btn-danger';
				echo "<button type='button' class='btn ".$disabledColor." qty_ke' data-id='".$i."' title='".$disabledTitle."' style='margin-right:5px;' ".$disabled.">".$i."</button>";
				if(!empty($checkInputMixing[$keyNew])){
					$nomor_max++;
				}

				$getMaterialMixing  = $this->db->select('*')->where('kode_det', $kode)->get_where('so_internal_spk_material',array('type_name <>'=>'mixing'))->result_array();
				$ArrayID = [];
				foreach ($getMaterialMixing as $key => $value) {
					$ArrayID[] = $value['id'];
				}

				$getdata = $this->db->where_in('id_det_spk',$ArrayID)->get_where('so_internal_spk_material_pengeluaran',array('qty_ke'=>$i))->result_array();
				foreach ($getdata as $key => $value) {
					$sts_close = (!empty($value['close_date']))?'Y':'N';
					if($sts_close == 'N'){
						$HTML_NUMBER[] = $i;
					}
				}

				if(empty($getdata) AND !empty($checkInputMixing[$keyNew])){
					$HTML_NUMBER[] = $i;
				}
			}
			?>
			<input type="hidden" id='qty_max_mixing' name='qty_max_mixing' value='<?=$nomor_max;?>'>
			</div>
		</div>
		<span id='alertLabel'><p class='text-danger text-bold'>Data sudah disimpan !</p></span>
		<span id='alertLabelClose'><p class='text-success text-bold'>Sudah close Produksi !</p></span>
		<div class="form-group row" id='TandaTipeMixing'>
			<div class="col-md-3">
				<div class="form-group">
					<label>Qty Produksi <span class='text-primary'>(Qty Produksi di hitung dari nomor yang dipilih)</span></label>
					<input type="text" name='qty_mixing' id='qty_mixing' class="form-control autoNumeric0">
					<span class='text-danger' id='keteranganMixing'></span>
				</div>
			</div>
			
		</div>
        <div class="form-group row">
            <div class="col-md-2">
                <label for="customer">Tanggal Selesai Produksi <span class='text-red'>*</span></label>
            </div>
            <div class="col-md-2">
                <input type="text" name='close_date' id='close_date' class='form-control text-center datepicker' readonly>
            </div>
			<div class="col-md-1">
                <label for="customer">Shift <span class='text-red'>*</span></label>
            </div>
            <div class="col-md-1">
                <select name="id_shift" id="id_shift" class='chosen-select'>
					<option value="0">Pilih Shift</option>
					<?php
					foreach ($getShift as $key => $value) {
						echo "<option value='".$value['id']."'>".$value['nama']."</option>";
					}
					?>
				</select>
            </div>
			<div class="col-md-1">
                <label for="customer">Mesin <span class='text-red'>*</span></label>
            </div>
            <div class="col-md-5">
				<select name="id_mesin" id="id_mesin" class='chosen-select'>
					<option value="0">Pilih Mesin</option>
					<?php
					foreach ($getMachine as $key => $value) {
						echo "<option value='".$value['id']."'>".$value['nama']."</option>";
					}
					?>
				</select>
            </div>
        </div>
		<!-- <div class="form-group row">
			<div class="col-md-12 text-danger text-bold">
				<input type="checkbox" name='check_close_all' id='check_close_all' value='1'> Close All Input Aktual Produksi <i><u>dengan checklist status produksi menjadi close</u></i>
			</div>
		</div> -->
		<hr>
		<div class="form-group row" id='listInput'>
            <div class="col-md-12">
				<table class="table table-bordered" width='100%'>
                    <tr class='bg-green'>
                        <th colspan='7'>Material Non-Mixing</th>
                    </tr>
					<tr>
                        <th class='text-center' width='3%'>#</th>
						<th class='text-center'  width='17%'>Code</th>
						<th class='text-center'>Nama Material</th>
						<th class='text-right' width='12%' hidden>Stok (kg)</th>
						<th class='text-right' width='10%'>Kebutuhan (kg)</th>
						<th class='text-right' width='10%'>Aktual Before (kg)</th>
						<th class='text-right' width='10%'>Balance (kg)</th>
						<th class='text-center' width='10%'>Aktual (kg)</th>
					</tr>
					<?php
					foreach ($getMaterialNonMixing as $key => $value) { $key++;
						$id_material 	= $value['code_material'];
						$stock      	= (!empty($GET_STOK[$id_material]['stok']))?$GET_STOK[$id_material]['stok']:0;
						$nm_material    = (!empty($GET_MATERIAL[$id_material]['nama']))?$GET_MATERIAL[$id_material]['nama']:0;
						$code_material  = (!empty($GET_MATERIAL[$id_material]['code']))?$GET_MATERIAL[$id_material]['code']:0;
						$berat			= $value['berat'];
						$berat_aktual	= $value['weight_aktual'];
						echo "<tr>";
							echo "<td class='text-center'>".$key."</td>";
							echo "<td>".$code_material."</td>";
							echo "<td>".$nm_material."</td>";
							echo "<td class='text-right' hidden>".number_format($stock,4)."</td>";
							echo "<td class='text-right'>".number_format($berat,4)."</td>";
							echo "<td class='text-right'><span id='valuelabel_".$value['id']."'>".number_format($berat_aktual,4)."</span>
									<input type='hidden' name='detail2[".$key."][id]' value='".$value['id']."'>
									<input type='hidden' name='detail2[".$key."][code_material]' value='".$value['code_material']."'>
									<input type='hidden' name='detail2[".$key."][berat]' id='est_".$value['id']."' value='".$berat."'>
									<input type='hidden' name='detail2[".$key."][code_material_aktual]' value='".$value['code_material']."'>
									<input type='hidden' name='detail2[".$key."][berat_aktual]' id='value_".$value['id']."' class='form-control autoNumeric4 text-center' value='".$berat_aktual."'>
									</td>";
							echo "<td class='text-right'><span id='balance_".$value['id']."'>".number_format($berat,4)."</span></td>";
							echo "<td class='text-right'><input type='text' name='detail2[".$key."][berat_aktual_plus]' id='valueplus_".$value['id']."' data-id='".$value['id']."' class='form-control autoNumeric4 text-center aktualPlus'></td>";
						echo "</tr>";
					}
					?>
				</table>
			</div>
        </div>
		<div class="form-group row">
			<div class="col-md-12 text-primary text-bold">
				<input type="checkbox" name='check_close' value='1'> Close Input Produksi <i><u>(Product ini akan ke WIP)</u></i>
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
		<?php
			$disabled = (!empty($HTML_NUMBER))?'disabled':'';
			$textDisabled = (!empty($HTML_NUMBER))?'Qty ke '.implode(array_unique($HTML_NUMBER),', ').' masih belum close !':'';
		?>
		<div class="form-group row">
			<div class="col-md-4">
				<textarea name="reason_close" id="reason_close" class='form-control' rows='2' placeholder='Reason Close Produksi' <?=$disabled;?>></textarea>
			</div>
		</div>
		<div class="form-group row">
			<div class="col-md-4">
			<button type="button" class="btn btn-primary" id="saveCloseManual" <?=$disabled;?>>Close Produksi</button><br>
			<span class='text-danger text-bold'><?=$textDisabled;?></span>
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
		$('.chosen-select').select2({width: '100%'});
    	$('.autoNumeric4').autoNumeric('init', {mDec: '4', aPad: false})
		$('.autoNumeric0').autoNumeric('init', {mDec: '0', aPad: false})
        $('.datepicker').datepicker({ dateFormat: 'dd-M-yy' });
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

		$("#check_close_all").change(function() {
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
			var qty_max_mixing = getNum($('#qty_max_mixing').val())

			// console.log(maksimal)
			
			let HTML_Val = '';
			let nomorMax = 0;
			for (let i = qty_ke; i < maksimal; i++) {
				if(i <= qty_produksi && i <= qty_max_mixing){
					HTML_Val += i + ", ";
					nomorMax++
				}
			}

			// console.log(nomorMax)

			$('#keteranganMixing').text(`Laporan produksi untuk qty: ${HTML_Val}`)
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
							$('#valuelabel_'+row.id_det_spk).text(row.weight_aktual)
							$('#balance_'+row.id_det_spk).text(row.balance)
							$('#close_date').val(row.close_produksi)
							$('#id_shift').val(row.id_shift).change()
							$('#id_mesin').val(row.id_mesin).change()
							if(row.close == 'Y'){
								$('#save').hide()
								$('#alertLabel').hide()
								$('#alertLabelClose').show()
							}
							else{
								$('#save').show()
								$('#alertLabel').show()
								$('#alertLabelClose').hide()
							}
						})
					}
					else{
						if(tipe_mixing_set == '2'){
							$('#TandaTipeMixing').show()
						}
						$('#save').show()
						$('#alertLabel').hide()
						$('#alertLabelClose').hide()
						$('#close_date').val('')
						data.ArrayIDData.map((row,idx)=>{
							$('#value_'+row.id_det_spk).val(0)
							$('#valuelabel_'+row.id_det_spk).text(0)
							$('#balance_'+row.id_det_spk).text(0)
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

            var close_date = $("#close_date").val();
            var id_shift = $("#id_shift").val();
            var id_mesin = $("#id_mesin").val();

      		if(close_date == '' ){
				swal({title	: "Error Message!",text	: 'Date close empty, select first ...',type	: "warning"
				});
				$('#save').prop('disabled',false); return false;
			}

			if(id_shift == '0' ){
				swal({title	: "Error Message!",text	: 'Shift empty, select first ...',type	: "warning"
				});
				$('#save').prop('disabled',false); return false;
			}

			if(id_mesin == '0' ){
				swal({title	: "Error Message!",text	: 'Machine empty, select first ...',type	: "warning"
				});
				$('#save').prop('disabled',false); return false;
			}

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
						var baseurl=siteurl+active_controller+'/input_produksi';
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
										if(data.close == 0){
											window.location.href = base_url + active_controller + '/input_produksi/' + data.id
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
				  text: "Close input aktual produksi !",
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

		$('#saveCloseManual').click(function(e){
			e.preventDefault();

			var reason_close 		= $('#reason_close').val()

			if(reason_close == ''){
				swal({
				  title	: "Error Message!",
				  text	: 'Alasan close wajib diisi ...',
				  type	: "warning"
				});
				return false;
				
			}

			swal({
				  title: "Are you sure?",
				  text: "Close input aktual produksi !",
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
						var baseurl=siteurl+active_controller+'/saveCloseManual';
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
