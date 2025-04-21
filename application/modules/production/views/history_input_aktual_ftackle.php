
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
						$GET_ACTUAL     = getActualFtackle($id);
						$GET_HISTORY    = getActualFtackleHistory($id);
						$nomor = -2;
						foreach ($cycletime as $key => $value) { 
							$key++;
							$UNIQ_NEXT = '';
							$next_process = '';
							$UNIQ = $id.'-'.$value['nm_process'];
							$QTY_INPUT = (!empty($GET_ACTUAL[$UNIQ]['qty']))?$GET_ACTUAL[$UNIQ]['qty']:0;
							$QTY_BELUM = $getData[0]['qty'] - $QTY_INPUT;

							// if($nomor != 0){
								$nomor++;
								$next_process = (!empty($cycletime[$nomor]['nm_process']))?$cycletime[$nomor]['nm_process']:0;
								if($next_process != '0'){
									$UNIQ_NEXT = $id.'-'.$cycletime[$nomor]['nm_process'];
									$QTY_INPUT_NEXT = (!empty($GET_ACTUAL[$UNIQ_NEXT]['qty']))?$GET_ACTUAL[$UNIQ_NEXT]['qty']:0;
									$QTY_BELUM = $QTY_INPUT_NEXT - $QTY_INPUT;
								}
							// }

							$labelClose = ($QTY_INPUT == $getData[0]['qty'])?"<span class='text-bold bg-green'>(CLOSE)</span>":"";

							$NmProcess = str_replace([' ','-','&','+','(',')'],'',$value['nm_process']);
							echo "<tr>";
								echo "<td class='text-bold bg-primary' align='center' width='3%'>".$key."</td>";
								echo "<td class='text-bold bg-primary' align='left' colspan='4'>".$value['nm_process']." ".$labelClose."</td>";
								echo "<td class='text-bold bg-primary' align='center'  width='12%'><button type='button' class='btn btn-sm btn-default containerProcessBtn text-bold'  data-process='".$NmProcess."'>SHOW</button></td>";
							echo "</tr>";
							echo "<tr class='containerProcess ".$NmProcess."' data-process='".$NmProcess."'>";
								echo "<td colspan='6'>";
                                echo "<table class='table' width='100%'>";
                                echo "<tr>";
                                    echo "<th width='5%'>No</th>";
                                    echo "<th width='10%'>Tgl. Selesai</th>";
                                    echo "<th width='10%'>Qty. Selesai</th>";
                                    echo "<th>Material</th>";
                                    echo "<th width='8%' class='text-right'>Berat</th>";
                                    echo "<th width='10%'>By</th>";
                                    echo "<th width='10%'>Dated</th>";
                                echo "</tr>";

                                $UniqHist = $id.'-'.$value['nm_process'];
                                $restHistory = (!empty($GET_HISTORY[$UniqHist]))?$GET_HISTORY[$UniqHist]:[];
                                $urut = 0 ;
                                foreach ($restHistory as $key2 => $value2) { 
                                   
                                    $nomor = '';
                                    $tglClose = '';
                                    if($value2['qty'] > 0){
                                        $urut++;
                                        $nomor = $urut;
                                        $tglClose = (!empty($value2['tanggal']))?date('d-M-Y',strtotime($value2['tanggal'])):'';
                                    }

                                    $boldMat = ($value2['berat'] > 0)?'text-bold text-primary':'';
                                    echo "<tr>";
                                        echo "<td>".$nomor."</td>";
                                        echo "<td>".$tglClose."</td>";
                                        echo "<td>".$value2['qty']."</td>";
                                        echo "<td class='".$boldMat."'>".$value2['nm_material']."</td>";
                                        echo "<td class='".$boldMat."' align='right'>".$value2['berat']."</td>";
                                        echo "<td>".$value2['nm_lengkap']."</td>";
                                        echo "<td>".date('d-M-Y H:i:s',strtotime($value2['created_date']))."</td>";
                                    echo "</tr>";
                                }
                                echo "</table>";
                                echo "</td>";
							echo "</tr>";
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

		$('.containerProcess').hide();

		$(document).on('click', '.containerProcessBtn', function(){
		    let labelName  = $(this).text()
		    let processName  = $(this).data('process')
			$('.'+processName).toggle();

			if(labelName == 'SHOW'){
				$(this).text('HIDE')
			}
			else{
				$(this).text('SHOW')
			}
		});

		$(document).on('keyup', '.changeQty', function(){
		    let processName  = $(this).data('process')
			let qty 		= $('#qty'+processName).val();
			let qtybelum 	= $('#qtybelum'+processName).val();

			if(qty > qtybelum){
				$(this).val(qtybelum)
			}
		});

    	//back
		$(document).on('click', '#back', function(){
		    window.location.href = base_url + active_controller
		});

		$('.saveProcess').click(function(e){
			e.preventDefault();
			let processName  = $(this).data('process')

            var tanggal = $("#tanggal"+processName).val();
            var qty 	= $("#qty"+processName).val();

      		if(tanggal == '' ){
				swal({title	: "Error Message!",text	: 'Date selesai produksi empty, select first ...',type	: "warning"
				});
				$('#save').prop('disabled',false); return false;
			}

			if(qty == '' || qty == '0'){
				swal({title	: "Error Message!",text	: 'Qty empty, select first ...',type	: "warning"
				});
				$('#save').prop('disabled',false); return false;
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
						var baseurl=siteurl+active_controller+'/process_input_produksi_ftackle/'+processName;
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
									if(data.close == 1){
										window.location.href = base_url + active_controller
									}
									else{
										window.location.href = base_url + active_controller + '/input_produksi_ftackle/'+data.id
									}
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
						var baseurl=siteurl+active_controller+'/process_input_produksi_ftackle_close';
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
