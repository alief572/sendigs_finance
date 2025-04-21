
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
						<td>Tgl. Selesai Produksi</td>
						<td>:</td>
						<td><?=tgl_indo($getData[0]['tanggal_close']);?></td>
					</tr>
					<?php
					$USERNAME = (!empty($GET_USER[$getData[0]['close_by']]['nama']))?$GET_USER[$getData[0]['close_by']]['nama']:'';
					?>
					<tr>
						<td>Aktual Produksi By</td>
						<td>:</td>
						<td><?=$USERNAME;?></td>
					</tr>
					<tr>
						<td>Aktual Produksi Date</td>
						<td>:</td>
						<td><?=date('d-M-Y',strtotime($getData[0]['tanggal_close']));?></td>
					</tr>
					<tr>
						<td>Close Reason</td>
						<td>:</td>
						<td class='text-danger text-bold'><?=$getData[0]['reason_close'];?></td>
					</tr>
				</table>
				<input type="hidden" id='id_uniq' name='id_uniq' value='<?=$getData[0]['id_uniq']?>'>
				<input type="hidden" id='kode' name='kode' value='<?=$kode?>'>
			</div>
        </div>
        <hr>
		<div class="form-group row">
        	<div class="col-md-12">
			<?php
			for ($i=1; $i <= $qty; $i++) { 
				echo "<button type='button' class='btn btn-default qty_ke' data-id='".$i."' style='margin-right:5px;'>".$i."</button>";
			}
			?>
			</div>
		</div>
		
		<div class="form-group row" id='listInput2'>
            <div class="col-md-12">
				<table class="table table-bordered" width='100%'>
                    <tr class='bg-blue'>
                        <th colspan='6'>Material Mixing</th>
                    </tr>
					<tr>
                        <th class='text-center' width='3%'>#</th>
						<th class='text-center'  width='17%'>Code</th>
						<th class='text-center'>Nama Material</th>
						<th class='text-right' width='12%'>Kebutuhan (kg)</th>
						<th class='text-right' width='12%'>Aktual (kg)</th>
						<th class='text-center' width='12%'>Status</th>
					</tr>
					<?php
					foreach ($getMaterialMixing as $key => $value) { $key++;
						$id_material 	= $value['code_material'];
						$stock      	= (!empty($GET_STOK[$id_material]['stok']))?$GET_STOK[$id_material]['stok']:0;
						$nm_material    = (!empty($GET_MATERIAL[$id_material]['nama']))?$GET_MATERIAL[$id_material]['nama']:0;
						$code_material  = (!empty($GET_MATERIAL[$id_material]['code']))?$GET_MATERIAL[$id_material]['code']:0;
						$berat			= $value['berat'];
						echo "<tr>";
							echo "<td class='text-center'>".$key."</td>";
							echo "<td>".$code_material."</td>";
							echo "<td>".$nm_material."</td>";
							echo "<td class='text-right'>".number_format($berat,4)."</td>";
							echo "<td class='text-right' id='value_".$value['id']."'></td>";
							echo "<td class='text-center' id='valuests_".$value['id']."'></td>";
						echo "</tr>";
					}
					?>
				</table>
			</div>
        </div>
		<div class="form-group row" id='listInput'>
            <div class="col-md-12">
				<table class="table table-bordered" width='100%'>
                    <tr class='bg-green'>
                        <th colspan='6'>Material Non-Mixing</th>
                    </tr>
					<tr>
                        <th class='text-center' width='3%'>#</th>
						<th class='text-center'  width='17%'>Code</th>
						<th class='text-center'>Nama Material</th>
						<th class='text-right' width='12%'>Kebutuhan (kg)</th>
						<th class='text-right' width='12%'>Aktual (kg)</th>
						<th class='text-center' width='12%'>Status</th>
					</tr>
					<?php
					foreach ($getMaterialNonMixing as $key => $value) { $key++;
						$id_material 	= $value['code_material'];
						$stock      	= (!empty($GET_STOK[$id_material]['stok']))?$GET_STOK[$id_material]['stok']:0;
						$nm_material    = (!empty($GET_MATERIAL[$id_material]['nama']))?$GET_MATERIAL[$id_material]['nama']:0;
						$code_material  = (!empty($GET_MATERIAL[$id_material]['code']))?$GET_MATERIAL[$id_material]['code']:0;
						$berat			= $value['berat'];
						echo "<tr>";
							echo "<td class='text-center'>".$key."</td>";
							echo "<td>".$code_material."</td>";
							echo "<td>".$nm_material."</td>";
							echo "<td class='text-right'>".number_format($berat,4)."</td>";
							echo "<td class='text-right' id='value_".$value['id']."'></td>";
							echo "<td class='text-center' id='valuests_".$value['id']."'></td>";
						echo "</tr>";
					}
					?>
				</table>
			</div>
        </div>
		<div class="form-group row">
			<div class="col-md-6">
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
		$('#listInput').hide()
		$('#listInput2').hide()
    	//back
		$(document).on('click', '#back', function(){
		    window.location.href = base_url + active_controller
		});

	});

	$(document).on('click','.qty_ke', function(){
		let id = $(this).data('id')
		let kode = $('#kode').val()
		let id2
		$('#listInput').show()
		$('#listInput2').show()
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

		var baseurl = siteurl + active_controller + '/getChangeMaterialHistory';
		$.ajax({
			url			: baseurl,
			type		: "POST",
			data		: {
				'id' : id,
				'kode' : kode
			},
			cache		: false,
			dataType	: 'json',
			success		: function(data){
				if(data.arrayData != 0){
					// $('#save').hide()
					$('#alertLabel').show()
					data.arrayData.map((row,idx)=>{
						$('#value_'+row.id_det_spk).text(row.weight_aktual)
						$('#valuests_'+row.id_det_spk).html(row.status)
					})
				}
				else{
					data.ArrayIDData.map((row,idx)=>{
						$('#value_'+row.id_det_spk).text('')
						$('#valuests_'+row.id_det_spk).text('')
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



</script>
