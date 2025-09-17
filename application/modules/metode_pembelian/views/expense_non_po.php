<?php
$this->load->view('include/side_menu');
$pic 			= (!empty($header))?$header[0]->pic:'';
$keterangan 	= (!empty($header))?$header[0]->keterangan:'';
$coa_bank_ 		= (!empty($header))?$header[0]->coa_bank:'';
$coa_lain_ 		= (!empty($header))?$header[0]->coa_lain:'';
$biaya_lain 		= (!empty($header))?$header[0]->biaya_lain:'';
$nilai_selisih 		= (!empty($header))?$header[0]->nilai_selisih:'';
$keterangan_lain 	= (!empty($header))?$header[0]->keterangan_lain:'';

$tanda 			= (!empty($code))?'Update':'Insert';
$disabled		= (!empty($approve))?'disabled':'';
$disabled2		= ($approve == 'view')?'disabled':'';
$disabled3		= ($approve == 'view')?'readonly':'';
?>
<form action="#" method="POST" id="form_ct" enctype="multipart/form-data" autocomplete='off'> 
	<input type="hidden" name="id" value="<?=$id;?>">
    <input type="hidden" name="tanda" value="<?=$tanda;?>">
	<input type="hidden" id="approve" name="approve" value="<?=$approve;?>">
	<input type="hidden" id="non_po" name="non_po" value="<?=$non_po;?>">
<div class="box box-primary">
	<div class="box-header">
		<h3 class="box-title"><?php echo $title;?></h3>
		<div class="box-tool pull-right">
            
		</div>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
        <div class='form-group row'>		 	 
			<label class='label-control col-sm-2'><b>PIC <span class='text-red'>*</span></b></label>
			<div class='col-sm-4'>              
				<?php
				echo form_input(array('id'=>'pic','name'=>'pic','class'=>'form-control input-md','placeholder'=>'PIC','readonly'=>true),strtoupper($pic));
				?>
			</div>
		</div>
		<div class='form-group row'>		 	 
			<label class='label-control col-sm-2'><b>Keterangan</b></label>
			<div class='col-sm-4'>              
				<?php
				echo form_textarea(array('id'=>'keterangan','name'=>'keterangan','class'=>'form-control input-md','rows'=>'2','cols'=>'75','placeholder'=>'Keterangan','readonly'=>true),strtoupper($keterangan));
				?>
			</div>
		</div>
        <table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
            <thead>
                <tr class='bg-blue'>
                    <th class='text-center' style='width: 3%;'>#</th>
                    <th class='text-center' >Nama Barang/Jasa</th>
                    <th class='text-center' style='width: 7%;'>Qty</th>
                    <th class='text-center' style='width: 7%;'>Satuan</th>
                    <th class='text-center' style='width: 10%;'>Est Harga</th>
					<th class='text-center' style='width: 10%;'>Est Total Harga</th>
					<th class='text-center' style='width: 10%;'>Tanggal Dibutuhkan</th>
					<th class='text-center' style='width: 10%;'>Harga Beli</th>
					<th class='text-center' style='width: 10%;'>Total Harga</th>
					<th class='text-center' style='width: 12%;'>Keterangan</th>
                </tr>
            </thead>
            <tbody>
				<?php
				$nomor = 0;
				$SUM = 0;
				if(!empty($detail)){
					foreach($detail AS $val => $valx){ $nomor++;
						$qty_rev = (!empty($valx['qty_rev']))?$valx['qty_rev']:$valx['qty'];
						// $nil_rev = (!empty($valx['price_unit_rev']))?$valx['price_unit_rev']:$valx['price_unit'];
						$nil_rev = $valx['price_unit'];
						$SUM += $qty_rev * $nil_rev;
						echo "<tr class='header_".$nomor."'>";
							echo "<td align='center'>".$nomor."<input type='hidden' name='detail[".$nomor."][id]' value='".$valx['id']."'></td>";
							echo "<td align='left'><input type='text' ".$disabled3." disabled name='detail[".$nomor."][nm_barang]' class='form-control input-md' value='".strtoupper($valx['nm_barang'])."'></td>";
							echo "<td align='left'><input type='text' ".$disabled2." readonly id='qty_".$nomor."' name='detail[".$nomor."][qty]' class='form-control input-md text-center maskM sum_tot' value='".number_format($qty_rev)."' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''></td>";
							echo "<td align='left'>
									<select name='detail[".$nomor."][satuan]' class='form-control' ".$disabled2." disabled>";
									echo "<option value='0'>Pilih</option>";
									foreach ($satuan as $key => $value) {
										$selected = ($value['id_satuan'] == $valx['satuan'])?'selected':'';
										echo "<option value='".$value['id_satuan']."' ".$selected.">".$value['kode_satuan']."</option>";
									}
							echo "</select></td>";
							echo "<td align='left'><input type='text' ".$disabled2." disabled id='harga_".$nomor."' name='detail[".$nomor."][price_unit]' class='form-control input-md text-right maskM sum_tot' readonly value='".number_format($nil_rev)."' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''></td>";
							echo "<td align='left'><input type='text' ".$disabled2." disabled id='total_harga_".$nomor."' name='detail[".$nomor."][total_harga]' class='form-control input-md text-right maskM jumlah_all' value='".number_format($qty_rev * $nil_rev)."' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero='' readonly></td>";
							echo "<td align='left'><input type='text' ".$disabled3." disabled name='detail[".$nomor."][tanggal]' class='form-control text-center input-md datepicker' readonly value='".strtoupper($valx['tgl_dibutuhkan'])."'></td>";
							echo "<td align='left'><input type='text' id='hargareal_".$nomor."' name='detail[".$nomor."][price_unit_real]' class='form-control input-md text-right maskM sum_tot_real' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero='' value='".number_format($valx['price_pay_unit'])."'></td>";
							echo "<td align='left'><input type='text' id='total_harga_real_".$nomor."' name='detail[".$nomor."][total_harga_real]' class='form-control input-md text-right maskM jumlah_all_real' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero='' readonly value='".number_format($valx['price_pay_total'])."'></td>";
							echo "<td align='left'><input type='text' name='detail[".$nomor."][keterangan_real]' class='form-control input-md' value='".strtoupper($valx['keterangan_real'])."'></td>";
							// if(empty($approve)){
							// echo "<td align='center'><button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button></td>";
							// }
						echo "</tr>";
					}
				}
				echo "<tr>";
					echo "<td></td>";
					echo "<td colspan='4'><b>TOTAL</b></td>";
					echo "<td><input type='text' id='total_est' class='form-control input-md text-right maskM' value='".number_format($SUM)."' readonly data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''></td>";
					echo "<td colspan='2'></td>";
					echo "<td><input type='text' id='total_real' name='total_real' class='form-control input-md text-right maskM' readonly data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''></td>";
					echo "<td></td>";
				echo "</tr>";
				echo "<tr>";
					echo "<td colspan='2'></td>";
					echo "<td><b>Biaya Lain</b></td>";
					echo "<td colspan='3'>";
					echo form_dropdown('coa_lain',$datcoa, $coa_lain_, array('id'=>'coa_lain','class'=>'form-control chosen_select','required'=>'required'));
					echo "</td>";
					echo "<td></td>";
					echo "<td>Biaya Lain</td>";
					echo "<td><input type='text' id='biaya_lain' name='biaya_lain' value='".number_format($biaya_lain)."' class='form-control input-md text-right maskM' placeholder='Biaya Lain' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''></td>";
					echo "<td><input type='text' id='keterangan_lain' value='".$keterangan_lain."' name='keterangan_lain' class='form-control input-md text-left' placeholder='Keterangan Biaya Lain'></td>";
				echo "</tr>";
				echo "<tr>";
					echo "<td colspan='2'></td>";
					echo "<td><b>Cash/Bank</b></td>";
					echo "<td colspan='3'>";
					echo form_dropdown('coa_bank',$datcoa, $coa_bank_, array('id'=>'coa_bank','class'=>'form-control chosen_select','required'=>'required'));
					echo "</td>";
					echo "<td></td>";
					echo "<td>Selisih</td>";
					echo "<td><input type='text' id='selisih' name='selisih' value='".number_format($nilai_selisih)."' class='form-control input-md text-right maskM' readonly data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''></td>";
					echo "<td></td>";
				echo "</tr>";
				?>
            </tbody>
        </table>
        <div class='box-footer'>
        <?php
            echo form_button(array('type'=>'button','class'=>'btn btn-md btn-danger','style'=>'float:right; margin-left:5px;','value'=>'back','content'=>'Back','id'=>'back'));
            if(empty($header[0]->expense_date)){
				echo form_button(array('type'=>'button','class'=>'btn btn-md btn-primary','style'=>'float:right;','value'=>'save','content'=>'Save','id'=>'save')).' ';
            }
        ?>
        </div>
	</div>
	<!-- /.box-body -->
 </div>
  <!-- /.box -->

</form>
<?php $this->load->view('include/footer'); ?>
<style type="text/css">
	.datepicker{
		cursor: pointer;
	}
</style>
<script>
	$(document).ready(function(){
		$('.maskM').maskMoney();
		$('.chosen_select').chosen({
			'width' : '100%'
		});
		$('.datepicker').datepicker({
			dateFormat : 'yy-mm-dd',
			minDate: 0
		});
		$('.tnd_reason').hide();
	});
	
	$(document).on('click', '#back', function(e){
		var tanda = '/non_po';
		window.location.href = base_url + active_controller +'/approval_non_po'+tanda;
	});
	
	$(document).on('keyup', '.sum_tot_real', function(){
		var id 		= $(this).attr('id');
		var det_id	= id.split('_');
		var a		= det_id[1];
		sum_total(a);
	});

	$(document).on('keyup', '#biaya_lain', function(){
		sum_total2();
	});


	//SAVE
	$(document).on('click', '#save', function(e){
		e.preventDefault();
		$('#save').prop('disabled',true);
		
		var coa_bank	= $('#coa_bank').val();
		// alert('Tahan'); return false;
		if(coa_bank == '0'){
			swal({
				title	: "Error Message!",
				text	: 'COA BANK empty, select first ...',
				type	: "warning"
			});

			$('#save').prop('disabled',false);
			return false;
		}
		
		$('#save').prop('disabled',true);
		
		swal({
		  title: "Are you sure?",
		  text: "Save this data ?",
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
				loading_spinner();
				var formData 	= new FormData($('#form_ct')[0]);
				var baseurl		= base_url + active_controller +'/expense_non_po';
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
								  timer	: 3000
								});
							window.location.href = base_url + active_controller+'/approval_non_po/non_po';
						}
						else if(data.status == 0){
							swal({
							  title	: "Save Failed!",
							  text	: data.pesan,
							  type	: "warning",
							  timer	: 3000
							});
							$('#save').prop('disabled',false);
						}
					},
					error: function() {
						swal({
						  title		: "Error Message !",
						  text		: 'An Error Occured During Process. Please try again..',
						  type		: "warning",
						  timer		: 3000
						});
						$('#save').prop('disabled',false);
					}
				});
			}
			else {
				swal("Cancelled", "Data can be process again :)", "error");
				$('#save').prop('disabled',false);
				return false;
			}
		});
	});
	
	function sum_total(a){
		var qty 	= getNum($('#qty_'+a).val().split(",").join(""));
		var harga 	= getNum($('#hargareal_'+a).val().split(",").join(""));
		var total_est 	= getNum($('#total_est').val().split(",").join(""));
		var biaya_lain 	= getNum($('#biaya_lain').val().split(",").join(""));
		let selisih
		var total	= qty * harga;
		// console.log(total);
		$('#total_harga_real_'+a).val(number_format(total));
		
		var SUM = 0;
		$(".jumlah_all_real" ).each(function() {
			SUM += Number(getNum($(this).val().split(",").join("")));
		});
		selisih = total_est - (SUM + biaya_lain)
		$('#total_real').val(number_format(SUM));
		$('#selisih').val(number_format(selisih));
	}

	function sum_total2(){
		var total_est 	= getNum($('#total_est').val().split(",").join(""));
		var biaya_lain 	= getNum($('#biaya_lain').val().split(",").join(""));
		let selisih
		var SUM = 0;
		$(".jumlah_all_real" ).each(function() {
			SUM += Number(getNum($(this).val().split(",").join("")));
		});
		selisih = total_est - (SUM + biaya_lain)
		$('#total_real').val(number_format(SUM));
		$('#selisih').val(number_format(selisih));
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
