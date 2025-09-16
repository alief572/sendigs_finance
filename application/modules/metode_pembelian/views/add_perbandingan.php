<?php
$this->load->view('include/side_menu');
?>
<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data" autocomplete='off'> 
<div class="box box-primary" style='margin-right: 17px;'>
	<div class="box-header">
		<h3 class="box-title"><?php echo $title;?></h3>
		<div class="box-tool pull-right">
			<button type='button' class='btn btn-md btn-success' id='add_currency'>Add Currency</button>
		</div>
	</div>
	<div class="box-body">
		<input type='hidden' name='no_rfq' class='form-control input-sm' value='<?=$this->uri->segment(3);?>'>
		<?php
		$no = 0;
		foreach($result AS $val => $valx){ $no++;
			$flag 		= get_name('supplier', 'id_negara', 'id_supplier', $valx['id_supplier']);
			$sel_local 	= ($flag == 'IDN')?'selected':'';
			$sel_import = ($flag <> 'IDN')?'selected':'';
			
			if(!empty($valx['lokasi'])){
				$sel_local 	= ($valx['lokasi'] == 'local')?'selected':'';
				$sel_import = ($valx['lokasi'] == 'import')?'selected':'';
			}

			$sel_usd = ($valx['currency'] == 'USD')?'selected':'';
			$sel_idr = ($valx['currency'] == 'IDR')?'selected':'';
			
			$alamatSUP = (!empty($valx['alamat_supplier']))?$valx['alamat_supplier']:get_name('supplier', 'alamat', 'id_supplier', $valx['id_supplier']);
			$keterangan = $valx['keterangan'];
			$query 	= "	SELECT 
							a.*,
							c.tgl_dibutuhkan
						FROM 
							tran_rfq_detail a 
							LEFT JOIN tran_pr_detail c ON a.no_rfq = c.no_rfq 
						WHERE 
							a.hub_rfq='".$valx['hub_rfq']."'
							AND a.id_barang = c.id_barang
						GROUP BY a.id_barang
							";
			$res 	= $this->db->query($query)->result_array();
			?>
			<div class='form-group row'>		 	 
				<label class='label-control col-sm-6'><b style='font-size: 16px;'><?=$no.'. '.$valx['nm_supplier'];?></b></label>
			</div>
			<div class='form-group row'>		 	 
				<div class='col-sm-2'>
					<select id='lokasi_<?=$no;?>' name='Header[<?=$no;?>][lokasi]' class='form-control input-sm chosen-select'>
						<option value='local' <?=$sel_local;?>>LOCAL</option>
						<option value='import' <?=$sel_import;?>>IMPORT</option>
					</select>
				</div>
				<div class='col-sm-4'></div>
				<div class='col-sm-1'><label>Currency</label></div>
				<div class='col-sm-2'>
					<select id='currency_<?=$no;?>' name='Header[<?=$no;?>][currency]' class='form-control input-md chosen-select changeCurrency' data-no='<?=$no;?>'>
						<?php
						foreach ($currency as $key => $value) {
							$selected = ($valx['currency'] == $value['kode'])?'selected':'';
							echo "<option value='".$value['kode']."' ".$selected.">".$value['kode']."</option>";
						}
						?>
					</select>
				</div>
			</div>
			<div class='form-group row'>		 	 
				<div class='col-sm-6'>
					<textarea id='alamat_<?=$no;?>' class='form-control input-md' name='Header[<?=$no;?>][alamat]' rows='3' placeholder='Supplier Address'><?=strtoupper($alamatSUP);?></textarea>
					<input type='hidden' name='Header[<?=$no;?>][id]' class='form-control input-sm' value='<?=$valx['id'];?>'>
				</div>
				<div class='col-sm-1' hidden><label>Kurs</label></div>
				<div class='col-sm-2' hidden>
					<input type='text' id='kurs_<?=$no;?>' name='Header[<?=$no;?>][kurs]' class='form-control input-md autoNumeric2 changeKurs' data-no='<?=$no;?>' value='1'>
				</div>
			</div>
			<div class='form-group row'>		 	 
				<div class='col-sm-12'>
					<table class="table table-bordered table-striped" width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class="text-center mid">Material Name</th>
								<th class="text-center mid" width='8%'>Price From Supplier</th>
								<!-- <th class="text-center mid" width='10%'>Harga (IDR)</th> -->
								<th class="text-center mid" width='7%'>Qty PR</th>
								<th class="text-center mid" width='7%'>MOQ (Kg)</th>
								<th class="text-center mid" width='7%'>Lead Time (Day)</th>
								<th class="text-center mid" width='10%'>Tanggal Dibutuhkan</th>
								<th class="text-center mid" width='12%'>Total Harga</th>
							</tr>
						</thead>
					<tbody>
						<?php
						$no2 = 0;
						$SUM_HARGA = 0;
						foreach($res AS $val2 => $valx2){ $no2++;
							echo "<tr>";
								echo "<td>".strtoupper($valx2['nm_barang'])."</td>";
								echo "<td class='mid' align='right'><input type='text' name='Detail[".$no."][detail][".$no2."][price_ref_sup]' class='form-control text-right input-md autoNumeric2 price_sub_".$no." changeKurs' value='".$valx2['price_ref_sup']."' data-no='".$no."'></td>";
								echo "<td class='mid' align='center'><input type='text' name='Detail[".$no."][detail][".$no2."][qty]' class='form-control text-right input-md autoNumeric2 qty_pr changeKurs' value='".$valx2['qty']."'  data-no='".$no."'></td>";
								// echo "<td class='mid qty_pr' align='center'>".number_format($valx2['qty'],2)."</td>";
								echo "<td class='mid' align='center'><input type='text' name='Detail[".$no."][detail][".$no2."][moq]' class='form-control text-center input-md maskM' value='".number_format($valx2['moq'])."' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''></td>";
								echo "<td class='mid' align='center'>
										<input type='text' name='Detail[".$no."][detail][".$no2."][lead_time]' class='form-control text-center input-md maskM' value='".number_format($valx2['lead_time'])."' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>
										<input type='hidden' name='Detail[".$no."][detail][".$no2."][id]' class='form-control input-sm' value='".$valx2['id']."'>
										<input type='hidden' name='Detail[".$no."][detail][".$no2."][price_ref]' class='form-control input-sm' value='".number_format($valx2['price_ref'],2)."'>
										<input type='hidden' name='Detail[".$no."][detail][".$no2."][tgl_dibutuhkan]' class='form-control input-sm' value='".$valx2['tgl_dibutuhkan']."'>
										<input type='hidden' name='Detail[".$no."][detail][".$no2."][top]' class='form-control text-left input-sm' value='".strtoupper($valx2['top'])."'>
										<input type='hidden' name='Detail[".$no."][detail][".$no2."][keterangan]' class='form-control text-left input-sm' value='".strtoupper($valx2['keterangan'])."'>
										<input type='hidden' name='Detail[".$no."][detail][".$no2."][harga_idr]' class='form-control text-right input-md autoNumeric2 harga_idr' readonly value='".$valx2['harga_idr']."'>
										</td>";
								echo "<td class='mid' align='center'>".date('d-M-Y', strtotime($valx2['tgl_dibutuhkan']))."</td>";
								echo "<td class='mid' align='right'><input type='text' name='Detail[".$no."][detail][".$no2."][total_harga]' class='form-control text-right input-md autoNumeric2 tot_harga_idr' value='".$valx2['total_harga']."'readonly></td>";
							echo "</tr>";
							$SUM_HARGA += $valx2['total_harga'];
						}
						echo "<tr>";
							echo "<td colspan='5'><textarea id='keterangan_".$no."' class='form-control input-md' name='Header[".$no."][keterangan]' rows='2' placeholder='Deskripsi singkat tentang product/jasa dari supplier.'>".strtoupper($keterangan)."</textarea></td>";
							echo "<td class='text-right text-bold mid'>TOTAL PRICE</td>";
							echo "<td class='mid' align='right'><input type='text' class='form-control text-right input-md autoNumeric2 sum_harga_idr_".$no."' value='".$SUM_HARGA."' readonly></td>";
						echo "</tr>";
						?>
					</tbody>
				</table>
				</div>
			</div>
		<?php } ?>
		<?php
			echo form_button(array('type'=>'button','class'=>'btn btn-sm btn-danger','style'=>'min-width:100px; float:right; margin: 5px 0px 5px 0px;','content'=>'Back','id'=>'back')).' ';
			echo form_button(array('type'=>'button','class'=>'btn btn-sm btn-success','style'=>'min-width:100px; float:right; margin: 5px 5px 5px 0px;','value'=>'Create','content'=>'Save','id'=>'save')).' ';
		?>
	</div>
 </div>
  <!-- /.box -->

  <!-- modal -->
  <div class="modal fade" id="ModalView2">
		<div class="modal-dialog"  style='width:50%; '>
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="head_title2"></h4>
					</div>
					<div class="modal-body" id="view2">
					</div>
					<div class="modal-footer">
					<!--<button type="button" class="btn btn-primary">Save</button>-->
					<button type="button" class="btn btn-default " data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	<!-- modal -->

</form>
<?php $this->load->view('include/footer'); ?>
<style>
	.mid{
		vertical-align: middle !important;
	}
</style>
<script>
	$(document).ready(function(){
		$('.maskM').maskMoney();
		$(".autoNumeric2").autoNumeric('init', {mDec: '2', aPad: false});
	});

	$(document).on('click', '#add_currency', function(e){
		e.preventDefault();
		loading_spinner();
		$("#head_title2").html("<b>ADD CURRENCY</b>");
		$.ajax({
			type:'POST',
			url: base_url +'purchase/modal_add_currency',
			success:function(data){
				$("#ModalView2").modal();
				$("#view2").html(data);
				swal.close()
			},
			error: function() {
				swal({
				  title	: "Error Message !",
				  text	: 'Connection Timed Out ...',
				  type	: "warning",
				  timer	: 5000
				});
			}
		});
	});

	$(document).on('click', '#back', function(e){
		window.location.href = base_url + active_controller+'/perbandingan';
	});

	$(document).on('click', '#save', function(e){
		e.preventDefault();

		swal({ 
			title: "Are you sure?",
			text: "You will save be able to process again this data!",
			type: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-danger",
			confirmButtonText: "Yes, Process it!",
			cancelButtonText: "No, cancel process!",
			closeOnConfirm: false,
			closeOnCancel: false
		},
		function(isConfirm) {
			if (isConfirm) {
				loading_spinner();
				var formData  	= new FormData($('#form_proses_bro')[0]);
				$.ajax({
					url			: base_url + active_controller+'/add_perbandingan',
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
									timer	: 7000,
									showCancelButton	: false,
									showConfirmButton	: false,
									allowOutsideClick	: false
								});
							window.location.href = base_url + active_controller+'/perbandingan';
						}
						else if(data.status == 0){
							swal({
								title	: "Save Failed!",
								text	: data.pesan,
								type	: "warning",
								timer	: 7000,
								showCancelButton	: false,
								showConfirmButton	: false,
								allowOutsideClick	: false
							});
						}
					},
					error: function() {
						swal({
							title				: "Error Message !",
							text				: 'An Error Occured During Process. Please try again..',						
							type				: "warning",								  
							timer				: 7000,
							showCancelButton	: false,
							showConfirmButton	: false,
							allowOutsideClick	: false
						});
					}
				});
			} else {
			swal("Cancelled", "Data can be process again :)", "error");
			return false;
			}
		});
	});

	$(document).on('change', '.changeCurrency', function(){
		var no_ke = $(this).data('no');
		changeKurs(no_ke);
	});

	$(document).on('keyup', '.changeKurs', function(){
		var no_ke = $(this).data('no');
		changeKurs(no_ke);
	});

	let changeKurs = (no_ke) => {
		let currency 	= $('#currency_'+no_ke).val();
		let kurs 		= getNum($('#kurs_'+no_ke).val().split(",").join(""));

		let price_sub
		let harga
		let tot_harga
		let qty_pr
		let sum_total = 0
		$(".price_sub_"+no_ke).each(function() {
        	price_sub = getNum($(this).val().split(",").join(""));
			qty_pr = getNum($(this).parent().parent().find('.qty_pr').val().split(",").join(""));

			harga = price_sub
			if(currency == 'USD'){
				harga = price_sub * kurs
			}
			
			tot_harga = harga * qty_pr
			$(this).parent().parent().find('.harga_idr').val(number_format(harga,2))
			$(this).parent().parent().find('.tot_harga_idr').val(number_format(tot_harga,2))
			
			sum_total += Number(tot_harga);
 		});
		$('.sum_harga_idr_'+no_ke).val(number_format(sum_total,2))
	}

	$(document).on('click', '#save_currency', function(e){
		e.preventDefault();

		swal({ 
			title: "Are you sure?",
			text: "You will save be able to process again this data!",
			type: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-danger",
			confirmButtonText: "Yes, Process it!",
			cancelButtonText: "No, cancel process!",
			closeOnConfirm: false,
			closeOnCancel: false
		},
		function(isConfirm) {
			if (isConfirm) {
				loading_spinner();
				var formData  	= new FormData($('#form_proses_bro')[0]);
				$.ajax({
					url			: base_url +'purchase/modal_add_currency',
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
							window.location.href = base_url + active_controller+'/perbandingan';
						}
						else if(data.status == 0){
							swal({
								title	: "Save Failed!",
								text	: data.pesan,
								type	: "warning",
								timer	: 7000,
							});
						}
					},
					error: function() {
						swal({
							title	: "Error Message !",
							text	: 'An Error Occured During Process. Please try again..',						
							type	: "warning",								  
							timer	: 7000,
						});
					}
				});
			} else {
			swal("Cancelled", "Data can be process again :)", "error");
			return false;
			}
		});
	});
</script>
