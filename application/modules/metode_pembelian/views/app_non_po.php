<?php
$this->load->view('include/side_menu');
$pic 			= (!empty($header))?$header[0]->pic:'';
$keterangan 	= (!empty($header))?$header[0]->keterangan:'';

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
				echo form_input(array('id'=>'pic','name'=>'pic','class'=>'form-control input-md','placeholder'=>'PIC'),strtoupper($pic));
				?>
			</div>
		</div>
		<div class='form-group row'>		 	 
			<label class='label-control col-sm-2'><b>Keterangan</b></label>
			<div class='col-sm-4'>              
				<?php
				echo form_textarea(array('id'=>'keterangan','name'=>'keterangan','class'=>'form-control input-md','rows'=>'2','cols'=>'75','placeholder'=>'Keterangan'),strtoupper($keterangan));
				?>
			</div>
		</div>
		<?php
			if($approve == 'approve'){
			?>
				<div class='form-group row'>
					<label class='label-control col-sm-2'><b>Approve <span class='text-red'>*</span></b></label>
					<div class='col-sm-2'>
						<select name='sts_app' id='sts_app' class='form-control input-md'>
							<option value='0'>Select Approve</option>
							<option value='Y'>Approve</option>
							<option value='D'>Reject</option>
						</select>
					</div>
					<div class='col-sm-2'>
						
					</div>
					<label class='label-control col-sm-2 tnd_reason'><b>Reason  <span class='text-red'>*</span></b></label>
					<div class='col-sm-4 tnd_reason'>
						<?php
							echo form_textarea(array('id'=>'reason','name'=>'reason','class'=>'form-control input-md','rows'=>'2','cols'=>'75','placeholder'=>'Reason'));
						?>
					</div>
				</div>
			<?php
			}
        ?>
        <table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
            <thead>
                <tr class='bg-blue'>
                    <th class='text-center' style='width: 4%;'>#</th>
                    <th class='text-center' >Nama Barang/Jasa</th>
                    <th class='text-center' style='width: 8%;'>Qty</th>
                    <th class='text-center' style='width: 8%;'>Satuan</th>
                    <th class='text-center' style='width: 10%;'>Est Harga</th>
					<th class='text-center' style='width: 10%;'>Est Total Harga</th>
					<th class='text-center' style='width: 10%;'>Tanggal Dibutuhkan</th>
					<th class='text-center' style='width: 15%;'>Keterangan</th>
					<?php
					if(empty($approve)){
					?>
					<!--
                    <th class='text-center' style='width: 4%;'>#</th>
					-->
					<?php } ?>
                </tr>
            </thead>
            <tbody>
				<?php
				$nomor = 0;
				if(!empty($detail)){
					foreach($detail AS $val => $valx){ $nomor++;
						$qty_rev = (!empty($valx['qty_rev']))?$valx['qty_rev']:$valx['qty'];
						// $nil_rev = (!empty($valx['price_unit_rev']))?$valx['price_unit_rev']:$valx['price_unit'];
						$nil_rev = $valx['price_unit'];
						
						echo "<tr class='header_".$nomor."'>";
							echo "<td align='center'>".$nomor."<input type='hidden' name='detail[".$nomor."][id]' value='".$valx['id']."'></td>";
							echo "<td align='left'><input type='text' ".$disabled3." name='detail[".$nomor."][nm_barang]' class='form-control input-md' value='".strtoupper($valx['nm_barang'])."'></td>";
							echo "<td align='left'><input type='text' ".$disabled2." id='qty_".$nomor."' name='detail[".$nomor."][qty]' class='form-control input-md text-center maskM sum_tot' value='".number_format($qty_rev)."' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''></td>";
							echo "<td align='left'>
									<select name='detail[".$nomor."][satuan]' class='form-control' ".$disabled2.">";
									echo "<option value='0'>Pilih</option>";
									foreach ($satuan as $key => $value) {
										$selected = ($value['id_satuan'] == $valx['satuan'])?'selected':'';
										echo "<option value='".$value['id_satuan']."' ".$selected.">".$value['kode_satuan']."</option>";
									}
							echo "</select></td>";
							echo "<td align='left'><input type='text' ".$disabled2." id='harga_".$nomor."' name='detail[".$nomor."][price_unit]' class='form-control input-md text-right maskM sum_tot' readonly value='".number_format($nil_rev)."' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''></td>";
							echo "<td align='left'><input type='text' ".$disabled2." id='total_harga_".$nomor."' name='detail[".$nomor."][total_harga]' class='form-control input-md text-right maskM jumlah_all' value='".number_format($qty_rev * $nil_rev)."' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero='' readonly></td>";
							echo "<td align='left'><input type='text' ".$disabled3." name='detail[".$nomor."][tanggal]' class='form-control text-center input-md datepicker' readonly value='".strtoupper($valx['tgl_dibutuhkan'])."'></td>";
							echo "<td align='left'><input type='text' ".$disabled3." name='detail[".$nomor."][keterangan]' class='form-control input-md' value='".strtoupper($valx['keterangan'])."'></td>";
							// if(empty($approve)){
							// echo "<td align='center'><button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button></td>";
							// }
						echo "</tr>";
					}
				}
				if(empty($approve)){
				?>
				<!--
                <tr id='add_<?=$nomor;?>'>
                    <td align='center'></td>
                    <td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-warning addPart' title='Add Barang'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Barang</button></td>
                    <td align='center' colspan='6'></td>
                </tr>
				-->
				<?php } ?>
            </tbody>
        </table>
        <div class='box-footer'>
        <?php
            echo form_button(array('type'=>'button','class'=>'btn btn-md btn-danger','style'=>'float:right; margin-left:5px;','value'=>'back','content'=>'Back','id'=>'back'));
            if($approve <> 'view'){
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
		$('.chosen_select').chosen();
		$('.datepicker').datepicker({
			dateFormat : 'yy-mm-dd',
			minDate: 0
		});
		$('.tnd_reason').hide();
	});
	
	$(document).on('change', '#sts_app', function(e){
		var sts = $(this).val();
		if(sts == 'D'){
			$('.tnd_reason').show();
		}
		else{
			$('.tnd_reason').hide();
		}
	});
	
	$(document).on('click', '#back', function(e){
		var app = $("#approve").val();
		var tanda = "";
		if(app == 'approve'){
			var tanda = '/approval';
		}
		if(app == 'non_po'){
			var tanda = '/non_po';
		}
		window.location.href = base_url + active_controller +'/approval_non_po'+tanda;
	});
	
	$(document).on('click', '.addPart', function(){
		loading_spinner();
		var get_id 		= $(this).parent().parent().attr('id');
		// console.log(get_id);
		var split_id	= get_id.split('_');
		var id 		= parseInt(split_id[1])+1;
		var id_bef 	= split_id[1];

		$.ajax({
			url: base_url + active_controller+'/get_add_non_po/'+id,
			cache: false,
			type: "POST",
			dataType: "json",
			success: function(data){
				$("#add_"+id_bef).before(data.header);
				$("#add_"+id_bef).remove();
				$('.chosen_select').chosen({width: '100%'});
				$('.maskM').maskMoney();
				$('.datepicker').datepicker({
					dateFormat : 'yy-mm-dd',
					minDate: 0
				});
				swal.close();
			},
			error: function() {
				swal({
					title				: "Error Message !",
					text				: 'Connection Time Out. Please try again..',
					type				: "warning",
					timer				: 3000,
					showCancelButton	: false,
					showConfirmButton	: false,
					allowOutsideClick	: false
				});
			}
		});
	});
	
	//delete part
	$(document).on('click', '.delPart', function(){
		var get_id 		= $(this).parent().parent().attr('class');
		$("."+get_id).remove();
	});
	
	$(document).on('keyup', '.sum_tot', function(){
		var id 		= $(this).attr('id');
		var det_id	= id.split('_');
		var a		= det_id[1];
		sum_total(a);
	});


	//SAVE
	$(document).on('click', '#save', function(e){
		e.preventDefault();
		$('#save').prop('disabled',true);
		
		var id_dept	= $('#id_dept').val();
		var coa		= $('#coa').val();
		var sts_app	= $('#sts_app').val();
		// alert('Tahan'); return false;
		if(id_dept == '0'){
			swal({
				title	: "Error Message!",
				text	: 'Department name empty, select first ...',
				type	: "warning"
			});

			$('#save').prop('disabled',false);
			return false;
		}
		if(coa == '0'){
			swal({
				title	: "Error Message!",
				text	: 'COA name empty, select first ...',
				type	: "warning"
			});

			$('#save').prop('disabled',false);
			return false;
		}
		
		var app = $("#approve").val();
		var tanda = "";
		if(app == 'approve'){
			if(sts_app == '0'){
				swal({
					title	: "Error Message!",
					text	: 'Status Approve empty, select first ...',
					type	: "warning"
				});

				$('#save').prop('disabled',false);
				return false;
			}
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
				var baseurl		= base_url + active_controller +'/app_non_po';
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
								  timer	: 7000,
								  showCancelButton	: false,
								  showConfirmButton	: false,
								  allowOutsideClick	: false
								});
							window.location.href = base_url + active_controller+'/approval_non_po/'+tanda;;
						}
						else if(data.status == 0){
							swal({
							  title	: "Save Failed!",
							  text	: data.pesan,
							  type	: "warning",
							  timer	: 3000,
							  showCancelButton	: false,
							  showConfirmButton	: false,
							  allowOutsideClick	: false
							});
							$('#save').prop('disabled',false);
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
		var harga 	= getNum($('#harga_'+a).val().split(",").join(""));
		
		var total	= qty * harga;
		// console.log(total);
		$('#total_harga_'+a).val(number_format(total));
		
		var SUM = 0;
		$(".jumlah_all" ).each(function() {
			SUM += Number(getNum($(this).val().split(",").join("")));
		});
		
		$('#budget').val(number_format(SUM));
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
