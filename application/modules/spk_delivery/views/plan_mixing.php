
<div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post" autocomplete="off"><br>
		<div class="form-group row">
        	<div class="col-md-12">
				<table width='50%'>
					<tr>
						<td width='20%'>Sales Order </td>
						<td width='1%'>:</td>
						<td><?=$getData[0]['no_so'];?></td>
					</tr>
					<tr>
						<td>No Penawaran</td>
						<td>:</td>
						<td><?=strtoupper($getData[0]['no_penawaran']);?></td>
					</tr>
					<tr>
						<td>Customer</td>
						<td>:</td>
						<td><?=strtoupper($getData[0]['nm_customer']);?></td>
					</tr>
					<tr>
						<td>Project</td>
						<td>:</td>
						<td><?=strtoupper($getData[0]['project']);?></td>
					</tr>
				</table>
				<input type="hidden" id='no_so' name='no_so' value='<?=$getData[0]['no_so'];?>'>
			</div>
        </div>
		<h4>Plan Mixing</h4>
		<div class="form-group row">
        	<div class="col-md-12">
				<table class="table table-bordered table-striped" width='100%'>
					<tr>
						<th width='3%' class='text-center mid-valign' rowspan='2'>#</th>
						<th class='text-center mid-valign' rowspan='2'>Material Name</th>
						<th width='8%' class='text-center mid-valign' rowspan='2'>Weight (kg)</th>
						<th width='8%' class='text-center mid-valign' rowspan='2'>% Komposisi</th>
						<th class='text-center' colspan='7'>Mixing Option (kg)</th>
					</tr>
                    <tr>
                        <th width='8%'><input type="text" name='mix1' id='mix1' data-id='mix1' class='form-control input-sm text-right autoNumeric4 changeOption'></th>
                        <th width='8%'><input type="text" name='mix2' id='mix2' data-id='mix2' class='form-control input-sm text-right autoNumeric4 changeOption'></th>
                        <th width='8%'><input type="text" name='mix3' id='mix3' data-id='mix3' class='form-control input-sm text-right autoNumeric4 changeOption'></th>
                        <th width='8%'><input type="text" name='mix4' id='mix4' data-id='mix4' class='form-control input-sm text-right autoNumeric4 changeOption'></th>
                        <th width='8%'><input type="text" name='mix5' id='mix5' data-id='mix5' class='form-control input-sm text-right autoNumeric4 changeOption'></th>
                        <th width='8%'><input type="text" name='mix6' id='mix6' data-id='mix6' class='form-control input-sm text-right autoNumeric4 changeOption'></th>
                        <th width='8%'><input type="text" name='mix7' id='mix7' data-id='mix7' class='form-control input-sm text-right autoNumeric4 changeOption'></th>
                    </tr>
                    <?php
                        $SUM_FIRST = 0;
                        foreach ($getMaterialMixing as $key => $value) {
                            $SUM_FIRST += $value['berat'];
                        }

                        $SUM = 0;
                        $SUM_PERSEN = 0;
                        foreach ($getMaterialMixing as $key => $value) { $key++;
                            $nama_material = (!empty($GET_DET_Lv4[$value['code_material']]['nama']))?$GET_DET_Lv4[$value['code_material']]['nama']:'';
                            $code_material = (!empty($GET_DET_Lv4[$value['code_material']]['code']))?$GET_DET_Lv4[$value['code_material']]['code']:'';
                            
                            $berat = $value['berat'];
                            $persen = $berat / $SUM_FIRST * 100;

                            $SUM += $berat;
                            $SUM_PERSEN += $persen;

                            echo "<tr>";
                                echo "<td>".$key."
                                        <input type='hidden' name='detail[$key][id]' value='".$value['id']."' >
                                        <input type='hidden' name='detail[$key][mix1]' class='valmix1_".$key."'>
                                        <input type='hidden' name='detail[$key][mix2]' class='valmix2_".$key."'>
                                        <input type='hidden' name='detail[$key][mix3]' class='valmix3_".$key."'>
                                        <input type='hidden' name='detail[$key][mix4]' class='valmix4_".$key."'>
                                        <input type='hidden' name='detail[$key][mix5]' class='valmix5_".$key."'>
                                        <input type='hidden' name='detail[$key][mix6]' class='valmix6_".$key."'>
                                        <input type='hidden' name='detail[$key][mix7]' class='valmix7_".$key."'>
                                        </td>";
                                echo "<td>".$nama_material."</td>";
                                echo "<td align='right'>".number_format($berat,4)."</td>";
                                echo "<td align='right' class='persen' data-id='".$key."'>".number_format($persen,2)." %</td>";
                                echo "<td align='right' class='mix1_".$key."'></td>";
                                echo "<td align='right' class='mix2_".$key."'></td>";
                                echo "<td align='right' class='mix3_".$key."'></td>";
                                echo "<td align='right' class='mix4_".$key."'></td>";
                                echo "<td align='right' class='mix5_".$key."'></td>";
                                echo "<td align='right' class='mix6_".$key."'></td>";
                                echo "<td align='right' class='mix7_".$key."'></td>";
                            echo "</tr>";
                        }
                        echo "<tr>";
                            echo "<td></td>";
                            echo "<td><b>TOTAL MATERIAL</b</td>";
                            echo "<td align='right'><b>".number_format($SUM,4)."</b></td>";
                            echo "<td align='right'><b>".number_format($SUM_PERSEN,2)." %</b></td>";
                            echo "<td align='right' id='mix1_sum' class='text-bold'></td>";
                            echo "<td align='right' id='mix2_sum' class='text-bold'></td>";
                            echo "<td align='right' id='mix3_sum' class='text-bold'></td>";
                            echo "<td align='right' id='mix4_sum' class='text-bold'></td>";
                            echo "<td align='right' id='mix5_sum' class='text-bold'></td>";
                            echo "<td align='right' id='mix6_sum' class='text-bold'></td>";
                            echo "<td align='right' id='mix7_sum' class='text-bold'></td>";
                        echo "</tr>";
                    ?>
				</table>
			</div>
        </div>
		<div class="form-group row">
			<div class="col-md-6">
				<button type="button" class="btn btn-primary" name="save" id="save">Save</button>
				<button type="button" class="btn btn-danger" style='margin-left:5px;' name="back" id="back">Back</button>
			</div>
        </div>

      	
		</form>
	</div>
</div>


<script src="<?= base_url('assets/js/jquery.maskMoney.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>
<style>
    .datepicker, .datepicker2{
        cursor: pointer;
    }

    .mid-valign{
        vertical-align: middle !important;
    }
</style>

<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';

	$(document).ready(function(){
		$('.chosen-select').select2();
    	$('.autoNumeric4').autoNumeric('init', {mDec: '4', aPad: false})

    	//back
		$(document).on('click', '#back', function(){
		    window.location.href = base_url + active_controller
		});

        $(document).on('keyup', '.changeOption', function(){
		    let numOption = getNum($(this).val().split(",").join(""))
		    let id = $(this).data('id')
            let id2
            let persen
            let nilai
            let HTML
            let SUM = 0
            $(".persen" ).each(function() {
                id2 = $(this).data('id')
                persen = Number($(this).text().split(" %").join(""));
                nilai = numOption * persen / 100
                SUM += nilai
                // console.log(nilai)
                HTML = $(this).parent()
                // console.log(HTML.html())

                HTML.find('.'+id+'_'+id2).text(number_format(nilai,4))
                HTML.find('.val'+id+'_'+id2).val(nilai)
            });
            // console.log(SUM)
            $('#'+id+'_sum').text(number_format(SUM,4))

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
						var baseurl=siteurl+active_controller+'/request_to_subgudang';
						$.ajax({
							url			: baseurl,
							type		: "POST",
							data		: formData,
							cache		: false,
							dataType	: 'json',
							processData	: false,
							contentType	: false,
							success		: function(data){
								window.open(base_url + active_controller+'/print_spk/'+data.kode_det,'_blank');
							    window.location.href = base_url + active_controller
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
