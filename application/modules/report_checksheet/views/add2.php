
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
						<td><?=strtoupper($nama_product);?></td>
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
						<td>Machine</td>
						<td>:</td>
						<td><?=strtoupper($nm_machine);?></td>
					</tr>
                    <tr>
						<td>Plan Produksi</td>
						<td>:</td>
						<td><?=date('d-M-Y',strtotime($getData[0]['tanggal']));?></td>
					</tr>
                    <tr>
						<td>Est. Finish</td>
						<td>:</td>
						<td><?=date('d-M-Y',strtotime($getData[0]['tanggal_est_finish']));?></td>
					</tr>
                </table>
				<input type="hidden" id='id' name='id' value='<?=$id?>'>
				<input type="hidden" id='id_mesin' name='id_mesin' value='<?=$checksheet_header[0]['id_mesin']?>'>
				<input type="hidden" id='id_master' name='id_master' value='<?=$checksheet_header[0]['id']?>'>
				<input type="hidden" id='qty_ke' name='qty_ke' value='<?=$qty_ke?>'>
				<input type="hidden" id='code_lv4' name='code_lv4' value='<?=$getData[0]['code_lv4']?>'>
			</div>
        </div>
		<hr>
		<div class="form-group row">
        	<div class="col-md-12">
			<?php
			for ($i=1; $i <= $qty; $i++) {
				$disabledColor = ($qty_ke == $i)?'btn-success':'btn-default';
				echo "<a href='".base_url('report_checksheet/'.$this->uri->segment(2).'/'.$this->uri->segment(3).'/'.$i.'/'.$tanda)."' class='btn ".$disabledColor."' style='margin-right:5px; margin-bottom:5px;'>".$i."</a>";
			}
			?>
			</div>
		</div>
        <?php
        $hourly = ($checksheet_header[0]['frequency_check'] == 'hourly')?':00':'';
        $scroll = ($checksheet_header[0]['frequency_check'] == 'hourly')?'width:2000px; overflow-x:auto;':'';
        ?>
		<h4>List Checksheet</h4>
		<div class="form-group row">
            <div class="col-md-3">
                <h4>Surfacing Veil</h4>
                <table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
                    <thead>
                        <tr class='bg-blue'>
                            <th class='text-left' width='50%'>#</th>
                            <th class='text-left'>Atas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $val = 0;
                        if(!empty($listSurface)){
                            foreach($listSurface AS $val => $valx){ 
                                $val++;
                                $idValue = (!empty($GET_VALUE[$valx['id']]['id']))?$GET_VALUE[$valx['id']]['id']:'';
                                echo "<tr>";
                                    echo "<td align='left'>".$valx['nama']."
                                            <input type='hidden' name='DetailSurface[".$val."][id]' value='".$idValue."'>
                                            <input type='hidden' name='DetailSurface[".$val."][id_checksheet]' value='".$valx['id']."'>
                                            </td>";
                                    if($valx['id'] == '1'){
                                        $atasValue = (!empty($GET_VALUE_MST[$valx['id']]['surface']))?$GET_VALUE_MST[$valx['id']]['surface']:'';

                                        echo "<td align='left' class='text-bold'>".$atasValue."<input type='hidden' class='form-control input-sm' name='DetailSurface[".$val."][atas]' value='".$atasValue."'></td>";
                                    }
                                    else{
                                        $atasValue = (!empty($GET_VALUE[$valx['id']]['surface']))?$GET_VALUE[$valx['id']]['surface']:'';
                                        if($valx['id'] == '2'){
                                            $atasValue = (!empty($GET_VALUE[$valx['id']]['surface']))?$GET_VALUE[$valx['id']]['surface']:$GET_VALUE_MST[1]['surface'];
                                        }
                                        echo "<td align='left'><input type='text' class='form-control input-sm' name='DetailSurface[".$val."][atas]' value='".$atasValue."'></td>";
                                    }
                                echo "</tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="col-md-6">
                <h4>Matt</h4>
                <table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
                    <thead>
                        <tr class='bg-blue'>
                            <th class='text-left' width='25%'>#</th>
                            <th class='text-left'>Atas</th>
                            <th class='text-left'>Bawah</th>
                            <th class='text-left'>Kiri</th>
                            <th class='text-left'>Kanan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $val = 0;
                        if(!empty($listMatt)){
                            foreach($listMatt AS $val => $valx){ 
                                $val++;

                                $idValue = (!empty($GET_VALUE[$valx['id']]['id']))?$GET_VALUE[$valx['id']]['id']:'';
                                

                                echo "<tr>";
                                    echo "<td align='left'>".$valx['nama'];
                                        echo "<input type='hidden' name='DetailMatt[".$val."][id]' value='".$idValue."'>";
                                        echo "<input type='hidden' name='DetailMatt[".$val."][id_checksheet]' value='".$valx['id']."'>";
                                    echo "</td>";
                                    if($valx['id'] == 6 OR $valx['id'] == 7){
                                        $matt_atas = (!empty($GET_VALUE_MST[$valx['id']]['matt_atas']))?$GET_VALUE_MST[$valx['id']]['matt_atas']:'';
                                        $matt_bawah = (!empty($GET_VALUE_MST[$valx['id']]['matt_bawah']))?$GET_VALUE_MST[$valx['id']]['matt_bawah']:'';
                                        $matt_kiri = (!empty($GET_VALUE_MST[$valx['id']]['matt_kiri']))?$GET_VALUE_MST[$valx['id']]['matt_kiri']:'';
                                        $matt_kanan = (!empty($GET_VALUE_MST[$valx['id']]['matt_kanan']))?$GET_VALUE_MST[$valx['id']]['matt_kanan']:'';
                                        echo "<td align='left' class='text-bold'>".$matt_atas."<input type='hidden' class='form-control input-sm' name='DetailMatt[".$val."][atas]' value='".$matt_atas."'></td>";
                                        echo "<td align='left' class='text-bold'>".$matt_bawah."<input type='hidden' class='form-control input-sm' name='DetailMatt[".$val."][bawah]' value='".$matt_bawah."'></td>";
                                        echo "<td align='left' class='text-bold'>".$matt_kiri."<input type='hidden' class='form-control input-sm' name='DetailMatt[".$val."][kiri]' value='".$matt_kiri."'></td>";
                                        echo "<td align='left' class='text-bold'>".$matt_kanan."<input type='hidden' class='form-control input-sm' name='DetailMatt[".$val."][kanan]' value='".$matt_kanan."'></td>";
                                    }
                                    else{
                                        $matt_atas = (!empty($GET_VALUE[$valx['id']]['matt_atas']))?$GET_VALUE[$valx['id']]['matt_atas']:'';
                                        $matt_bawah = (!empty($GET_VALUE[$valx['id']]['matt_bawah']))?$GET_VALUE[$valx['id']]['matt_bawah']:'';
                                        $matt_kiri = (!empty($GET_VALUE[$valx['id']]['matt_kiri']))?$GET_VALUE[$valx['id']]['matt_kiri']:'';
                                        $matt_kanan = (!empty($GET_VALUE[$valx['id']]['matt_kanan']))?$GET_VALUE[$valx['id']]['matt_kanan']:'';
                                        if($valx['id'] == 8){
                                            $matt_atas = (!empty($GET_VALUE[$valx['id']]['matt_atas']))?$GET_VALUE[$valx['id']]['matt_atas']:$GET_VALUE_MST[7]['matt_atas'];
                                            $matt_bawah = (!empty($GET_VALUE[$valx['id']]['matt_bawah']))?$GET_VALUE[$valx['id']]['matt_bawah']:$GET_VALUE_MST[7]['matt_bawah'];
                                            $matt_kiri = (!empty($GET_VALUE[$valx['id']]['matt_kiri']))?$GET_VALUE[$valx['id']]['matt_kiri']:$GET_VALUE_MST[7]['matt_kiri'];
                                            $matt_kanan = (!empty($GET_VALUE[$valx['id']]['matt_kanan']))?$GET_VALUE[$valx['id']]['matt_kanan']:$GET_VALUE_MST[7]['matt_kanan'];
                                        }
                                        echo "<td align='left'><input type='text' class='form-control input-sm' name='DetailMatt[".$val."][atas]' value='".$matt_atas."'></td>";
                                        echo "<td align='left'><input type='text' class='form-control input-sm' name='DetailMatt[".$val."][bawah]' value='".$matt_bawah."'></td>";
                                        echo "<td align='left'><input type='text' class='form-control input-sm' name='DetailMatt[".$val."][kiri]' value='".$matt_kiri."'></td>";
                                        echo "<td align='left'><input type='text' class='form-control input-sm' name='DetailMatt[".$val."][kanan]' value='".$matt_kanan."'></td>";
                                    }
                                echo "</tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="col-md-3">
                <h4>Rooving</h4>
                <table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
                    <thead>
                        <tr class='bg-blue'>
                            <th class='text-left' width='50%'>#</th>
                            <th class='text-left'>Pemakaia Aktual</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $val = 0;
                        if(!empty($listRooving)){
                            foreach($listRooving AS $val => $valx){ 
                                $val++;

                                $idValue = (!empty($GET_VALUE[$valx['id']]['id']))?$GET_VALUE[$valx['id']]['id']:'';

                                echo "<tr>";
                                    echo "<td align='left'>".$valx['nama']."
                                            <input type='hidden' name='DetailRooving[".$val."][id]' value='".$idValue."'>
                                            <input type='hidden' name='DetailRooving[".$val."][id_checksheet]' value='".$valx['id']."'>
                                            </td>";
                                    if($valx['id'] == '12'){
                                        $pemakaianValue = (!empty($GET_VALUE_MST[$valx['id']]['rooving']))?$GET_VALUE_MST[$valx['id']]['rooving']:'';

                                        echo "<td align='left' class='text-bold'>".$pemakaianValue."<input type='hidden' class='form-control input-sm' name='DetailRooving[".$val."][pemakaian]' value='".$pemakaianValue."'></td>";
                                    }
                                    else{
                                        $pemakaianValue = (!empty($GET_VALUE[$valx['id']]['rooving']))?$GET_VALUE[$valx['id']]['rooving']:'';
                                        if($valx['id'] == '13'){
                                            $pemakaianValue = (!empty($GET_VALUE[$valx['id']]['rooving']))?$GET_VALUE[$valx['id']]['rooving']:$GET_VALUE_MST[12]['rooving'];
                                        }
                                        echo "<td align='left'><input type='text' class='form-control input-sm' name='DetailRooving[".$val."][pemakaian]' value='".$pemakaianValue."'></td>";
                                    }
                                echo "</tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-12">
                <h4>Checksheet Suhu dan Speed</h4>
                <table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
                    <thead>
                        <tr class='bg-blue'>
                            <th class='text-left' width='15%'></th>
                            <th class='text-left' colspan='3'>Display Temperature (^Celsius)</th>
                            <th class='text-left' colspan='3'>Dies Temperature (^Celsius)</th>
                            <th class='text-left'>Speed Hidrolik (cm/menit)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $val = 0;
                        if(!empty($listSuhuSpeed)){
                            foreach($listSuhuSpeed AS $val => $valx){ 
                                $val++;

                                $idValue = (!empty($GET_VALUE[$valx['id']]['id']))?$GET_VALUE[$valx['id']]['id']:'';
                                
                                echo "<tr>";
                                    echo "<td align='left'>".$valx['nama']."
                                            <input type='hidden' name='DetailSuhuSpeed[".$val."][id]' value='".$idValue."'>
                                            <input type='hidden' name='DetailSuhuSpeed[".$val."][id_checksheet]' value='".$valx['id']."'>
                                            </td>";
                                    if($valx['id'] == '17'){
                                        $display1 = (!empty($GET_VALUE_MST[$valx['id']]['display1']))?$GET_VALUE_MST[$valx['id']]['display1']:'';
                                        $display2 = (!empty($GET_VALUE_MST[$valx['id']]['display2']))?$GET_VALUE_MST[$valx['id']]['display2']:'';
                                        $display3 = (!empty($GET_VALUE_MST[$valx['id']]['display3']))?$GET_VALUE_MST[$valx['id']]['display3']:'';
                                        $dies1 = (!empty($GET_VALUE_MST[$valx['id']]['dies1']))?$GET_VALUE_MST[$valx['id']]['dies1']:'';
                                        $dies2 = (!empty($GET_VALUE_MST[$valx['id']]['dies2']))?$GET_VALUE_MST[$valx['id']]['dies2']:'';
                                        $dies3 = (!empty($GET_VALUE_MST[$valx['id']]['dies3']))?$GET_VALUE_MST[$valx['id']]['dies3']:'';
                                        $speed = (!empty($GET_VALUE_MST[$valx['id']]['speed']))?$GET_VALUE_MST[$valx['id']]['speed']:'';

                                        echo "<td align='left' class='text-bold'>".$display1."<input type='hidden' class='form-control input-sm' name='DetailSuhuSpeed[".$val."][display1]' value='".$display1."'></td>";
                                        echo "<td align='left' class='text-bold'>".$display2."<input type='hidden' class='form-control input-sm' name='DetailSuhuSpeed[".$val."][display2]' value='".$display2."'></td>";
                                        echo "<td align='left' class='text-bold'>".$display3."<input type='hidden' class='form-control input-sm' name='DetailSuhuSpeed[".$val."][display3]' value='".$display3."'></td>";
                                        echo "<td align='left' class='text-bold'>".$dies1."<input type='hidden' class='form-control input-sm' name='DetailSuhuSpeed[".$val."][dies1]' value='".$dies1."'></td>";
                                        echo "<td align='left' class='text-bold'>".$dies2."<input type='hidden' class='form-control input-sm' name='DetailSuhuSpeed[".$val."][dies2]' value='".$dies2."'></td>";
                                        echo "<td align='left' class='text-bold'>".$dies3."<input type='hidden' class='form-control input-sm' name='DetailSuhuSpeed[".$val."][dies3]' value='".$dies3."'></td>";
                                        echo "<td align='left' class='text-bold'>".$speed."<input type='hidden' class='form-control input-sm' name='DetailSuhuSpeed[".$val."][speed]' value='".$speed."'></td>";
                                    }
                                    else{
                                        $display1 = (!empty($GET_VALUE[$valx['id']]['display1']))?$GET_VALUE[$valx['id']]['display1']:'';
                                        $display2 = (!empty($GET_VALUE[$valx['id']]['display2']))?$GET_VALUE[$valx['id']]['display2']:'';
                                        $display3 = (!empty($GET_VALUE[$valx['id']]['display3']))?$GET_VALUE[$valx['id']]['display3']:'';
                                        $dies1 = (!empty($GET_VALUE[$valx['id']]['dies1']))?$GET_VALUE[$valx['id']]['dies1']:'';
                                        $dies2 = (!empty($GET_VALUE[$valx['id']]['dies2']))?$GET_VALUE[$valx['id']]['dies2']:'';
                                        $dies3 = (!empty($GET_VALUE[$valx['id']]['dies3']))?$GET_VALUE[$valx['id']]['dies3']:'';
                                        $speed = (!empty($GET_VALUE[$valx['id']]['speed']))?$GET_VALUE[$valx['id']]['speed']:'';

                                        echo "<td align='left'><input type='text' class='form-control input-sm' name='DetailSuhuSpeed[".$val."][display1]' value='".$display1."'></td>";
                                        echo "<td align='left'><input type='text' class='form-control input-sm' name='DetailSuhuSpeed[".$val."][display2]' value='".$display2."'></td>";
                                        echo "<td align='left'><input type='text' class='form-control input-sm' name='DetailSuhuSpeed[".$val."][display3]' value='".$display3."'></td>";
                                        echo "<td align='left'><input type='text' class='form-control input-sm' name='DetailSuhuSpeed[".$val."][dies1]' value='".$dies1."'></td>";
                                        echo "<td align='left'><input type='text' class='form-control input-sm' name='DetailSuhuSpeed[".$val."][dies2]' value='".$dies2."'></td>";
                                        echo "<td align='left'><input type='text' class='form-control input-sm' name='DetailSuhuSpeed[".$val."][dies3]' value='".$dies3."'></td>";
                                        echo "<td align='left'><input type='text' class='form-control input-sm' name='DetailSuhuSpeed[".$val."][speed]' value='".$speed."'></td>";
                                    }
                                echo "</tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
		<div class="form-group row">
			<div class="col-md-6">
				<?php if(empty($tanda)){ ?>
					<button type="button" class="btn btn-primary" name="save" id="save">Save</button>
				<?php
				}
				?>
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
	h1 {
		color: Green;
	}

	.table { border-collapse: collapse; width: 100%; }
  .td { background: #fff; padding: 8px 16px; }

  .tableFixHead {
    overflow: auto;
    height: 300px;
    position: sticky;
    top: 0;
  }

  .thead .th {
    position: sticky;
    top: 0;
    z-index: 9999;
  	background: #0073b7;
	vertical-align: top;
  }

	/* div.scroll {
		margin: 4px, 4px;
		padding: 4px;
		width: 300px;
		overflow-x: auto;
		overflow-y: hidden;
		white-space: nowrap;
	} */
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
						var baseurl=siteurl+active_controller+'/process_input_checksheet_new';
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
									window.location.href = base_url + active_controller + '/add2/' + data.id + '/' + data.qty_ke
									
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
