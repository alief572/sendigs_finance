<div class="box box-primary">
	<div class="box-header">
		<h3 class="box-title">Request Customer</h3>
	</div>
	<div class="box-body">
	    <?php if(empty($results['inquiry'])){
		}else{
	    foreach($results['inquiry'] AS $record){ ?>
		<table border='0' width='100%'  class="table table-striped table-hover">
		    <tr>
				<td width='15%'><b>No Inquiry</b></td>
				<td width='5%'>:</td>
				<td width='30%'><?= $record->no_inquiry;?></td>
				<td width='15%'></td>
				<td width='5%'></td>
				<td width='30%'></td>
			</tr>
			<tr>
				<td width='15%'><b>Tgl Request</b></td>
				<td width='5%'>:</td>
				<td width='30%'><?= tgl_indo($record->tgl_inquiry);?></td>
				<td width='15%'></td>
				<td width='5%'></td>
				<td width='30%'></td>
			</tr>
			<tr>
				<td width='15%'><b>Nama Customer</b></td>
				<td width='5%'>:</td>
				<td width='30%'><?= $record->name_customer;?></td>
				<td width='15%'></td>
				<td width='5%'></td>
				<td width='30%'></td>
			</tr>
			<tr>
				<td width='15%'><b>Project</b></td>
				<td width='5%'>:</td>
				<td width='30%'><?= $record->project;?></td>
				<td width='15%'></td>
				<td width='5%'></td>
				<td width='30%'></td>
			</tr>
			<tr>
				<td width='15%'><b>Keterangan</b></td>
				<td width='5%'>:</td>
				<td width='30%' colspan='4'><?= $record->ket_project; ?></td>
			</tr>
			<tr>
				<td width='15%'><b>Sales</b></td>
				<td width='5%'>:</td>
				<td width='30%' colspan='4'><?= $record->nama_karyawan; ?></td>
			</tr>
			
			
			<?php } }  ?>
		</table>
		<br><br>
		<div class="box box-primary">
			<div class="box-header">
				<h3 class="box-title">Specification</h3>
			</div>
			<div class="box-body">
				<table border='0' width='100%' class="table table-striped table-bordered table-hover table-condensed ">
			   <?php if(empty($results['form'])){
		       }else{
			    $numb=0; foreach($results['form'] AS $d){ $numb++; 
				
				 
				  if ($d->form==0) {
				  $form = 'SHEET';
				  }
				  elseif ($d->form==1) {
				  $form = 'COIL';
				  }
				 
				
				  if ($d->joint==1) {
				  $joint = 'Joint';
				  }
				  elseif ($d->joint==2) {
				  $joint = 'Marking';
				  }
				  elseif ($d->joint==3) {
				  $joint = 'Tidak Joint';
				  }
				  elseif ($d->joint==4) {
				  $joint = 'Tidak Marking';
				  }
				  elseif ($d->joint==5) {
				  $joint = 'Tidak Joint Dan Marking';
				  }
				  
				
			    ?>
				
			 	<tr id='tr_<?= $numb;?>'  >
				<td width='4%' class='text'><b>No.
				<?php echo $numb ;?>
				</b>
				</td>
				 <td width='10%' class='text'><b>Id Material</b><br>
				 <input type='text' class='form-control input-sm' id='id_material_$numb'  name='data[$numb][id_material]' value='<?php echo $d->id_material; ?>' placeholder='ID MATERIAL'  readonly>
               	 </td>
				 <td width='10%' class='text'><b>Nama Material</b><br>
				 <input type='text' class='form-control input-sm' id='nama_material_$numb'  name='data[$numb][nama_material]' value='<?php echo $d->nama_material; ?>' placeholder='NAMA MATERIAL' readonly >
               	 </td>
				  <td width='10%' class='text'><b>Length</b><br>
				 <input type='text' class='form-control input-sm' id='length_$numb'  name='data[$numb][length]' value='<?php echo $d->length; ?>' placeholder='HARDNESS'  readonly>
               	 </td>
				 <td width='10%' class='text'><b>Width</b><br>
				 <input type='text' class='form-control input-sm' id='width1_$numb'  name='data[$numb][width1]' value='<?php echo $d->width; ?>' placeholder='WIDTH' readonly >
               	 </td>
				  <td width='10%' class='text'><b>Thickness</b><br>
				 <input type='text' class='form-control input-sm' id='thickness_$numb'  name='data[$numb][thickness]' value='<?php echo $d->thickness; ?>' placeholder='THICKNESS' readonly >
               	 </td>
				 <td width='10%' class='text'><b>Density</b><br>
				 <input type='text' class='form-control input-sm' id='density_$numb'  name='data[$numb][density]' value='<?php echo $d->density; ?>' placeholder='THICKNESS' readonly >
               	 </td>
					               				
				</tr>
				<tr id='trx_$numb' >
				<td></td>
				<td width='10%' class='text'><b>Form</b><br>
				  <input type='text' class='form-control input-sm' id='pilih_form_$numb'  name='data[$numb][pilih_form]' value='<?php echo $form; ?>' placeholder='FORM' readonly >
               	 </td>
				 <td width='10%' class='text'><b>Length</b><br>
				 <input type='text' class='form-control input-sm' id='length_$numb'  name='data[$numb][length]' value='<?php echo $d->length_f; ?>' placeholder='Length' readonly >
               	 </td>	
				 <td width='10%' class='text'><b>Width</b><br>
				 <input type='text' class='form-control input-sm' id='width_$numb'  name='data[$numb][width]' value='<?php echo $d->width_f; ?>' placeholder='Width' readonly >
               	 </td>
				 <td width='10%' class='text'><b>Inner Diameter</b><br>
				 <input type='text' class='form-control input-sm' id='inner_$numb'  name='data[$numb][inner]' value='<?php echo $d->inner_d; ?>' placeholder='Inner Diameter' readonly >
               	 </td>	
				 <td width='10%' class='text'><b>Kg/Sheet</b><br>
				 <input type='text' class='form-control input-sm' id='kg_sheet_$numb'  name='data[$numb][kg_sheet]' value='<?php echo $d->kg_sheet; ?>' placeholder='KG/SHEET' readonly >
               	 </td>
				 <td width='10%' class='text'><b>Kg/Roll</b><br>
				 <input type='text' class='form-control input-sm' id='kg_roll_$numb'  name='data[$numb][kg_roll]' value='<?php echo $d->kg_roll; ?>' placeholder='KG/ROLL' readonly >
               	 </td>
				</tr>
				<tr id='trxx_$numb'  >
				<td></td>
				<td width='10%' class='text'><b>Qty Order (pcs)</b><br>
				<input type='text' class='form-control input-sm' id='qty_pcs_$numb'  name='data[$numb][qty_pcs]' value='<?php echo $d->qty_pcs; ?>' placeholder='QTY ORDER (PCS)' readonly >
				</td>
				<td width='10%' class='text'><b>Qty Order (Roll) </b><br>
				<input type='text' class='form-control input-sm' id='qty_roll_$numb'  name='data[$numb][qty_roll]' value='<?php echo $d->qty_roll; ?>' placeholder='QTY ORDER (ROLL)' readonly >
				</td>				
				<td width='10%' class='text'><b>Total Kg </b><br>
				<input type='text' class='form-control input-sm' id='total_kg_$numb'  name='data[$numb][total_kg]' value='<?php echo separator($d->total_kg); ?>' placeholder='TOTAL KG' readonly >
				</td>
				<td width='10%' class='text'><b>Customer Budget </b><br>
				<input type='text' class='form-control input-sm' id='total_kg_$numb'  name='data[$numb][total_kg]' value='<?php echo separator($d->cust_budget); ?>' placeholder='TOTAL KG' readonly >
				</td>
				 <td width='10%' class='text'><b></b><br>
				 </td>
				 <td width='10%' class='text'><b></b><br>
				 </td>
						
				</tr>
		<?php	 
            
			   } }
        ?>
   		
				</table><br>
				<div class="col-md-5">
				<h5 class="box-title"><b>Perkiraan Bayar</b></h5>
				<table border='0' width='100%' class="table table-striped table-bordered table-hover table-condensed">
					<thead id='head_table'>
						<tr class='bg-blue'>
							<th style='text-align:center; vertical-align: middle;' width='5%'>No</th>
							<th style='text-align:center; vertical-align: middle;' width='15%'>Tgl</th>
							<th style='text-align:center; vertical-align: middle;' width='15%'>Nominal</th>
							<th style='text-align:center; vertical-align: middle;' width='20%'>Nilai </th>
						</tr>
					</thead>
					<tbody>
						<?php
						$nopay=0;
						foreach($results['payment'] AS $pay){
						$nopay++;
							?>
							<tr>
								<td ><?= $nopay;?></td>
								<td ><?= tgl_indo($pay->perkiraan_bayar);?></td>
								<td ><?= $pay->tipe_payment;?></td>
								<td ><?= $pay->nominal;?></td>
							</tr>
							
							<?php
						}
						?>
					</tbody>
				</table>
				</div>
				<div class="col-md-5">
				<h5 class="box-title"><b>Perkiraan Kirim</b></h5>
				<table border='0' width='100%' class="table table-striped table-bordered table-hover table-condensed">
					<thead id='head_table'>
						<tr class='bg-blue'>
							<th style='text-align:center; vertical-align: middle;' width='5%'>No</th>
							<th style='text-align:center; vertical-align: middle;' width='15%'>Tgl</th>
							<th style='text-align:center; vertical-align: middle;' width='15%'>Qty Kirim</th>
							
						</tr>
					</thead>
					<tbody>
						<?php
						$nodel=0;
						foreach($results['delivery'] AS $del){
						$nodel++;
							?>
							<tr>
								<td ><?= $nodel;?></td>
								<td ><?= tgl_indo($del->perkiraan_kirim);?></td>
								<td ><?= $del->qty_kirim;?></td>
								
							</tr>
							
							<?php
						}
						?>
					</tbody>
				</table>
				</div>
				
				
			</div>
		</div>
	</div>
</div>
<div class="box box-primary">
	<div class="box-header">
		<h3 class="box-title">Keterangan</h3>
	</div>
	<div class="box-body">
		<table border='0' width='100%' class="table table-striped table-hover">
			<tr>
			    <?php 
				  if ($record->sample==0) {
				  $master = 'Tidak';
				  }
				  elseif ($record->sample==1) {
				   $master = 'Ya';
				  }
				  
				  if ($record->pay_instrument==1) {
				  $instrument = 'Cash';
				  }
				  elseif ($record->pay_instrument==2) {
				   $instrument = 'Giro';
				  }
				  elseif ($record->pay_instrument==3) {
				  $instrument = 'Transfer Bank';
				  }
				  
				  if ($record->pay_term==1) {
				  $pay_term = 'COD';
				  }
				  elseif ($record->pay_term==7) {
				  $pay_term = '7 Hari';
				  }
				  elseif ($record->pay_term==14) {
				  $pay_term = '14 Hari';
				  }
				  elseif ($record->pay_term==30) {
				  $pay_term = '30 Hari';
				  }
				  elseif ($record->pay_term==45) {
				  $pay_term = '45 Hari';
				  }
				  elseif ($record->pay_term==60) {
				  $pay_term = '60 Hari';
				  }
				  
     			?>
				<td width='15%'><b>Master Sample</b></td>
				<td width='5%'>:</td>
				<td width='30%'><?= "$master" ?></td>
				<td width='15%'></td>
				<td width='5%'></td>
				<td width='30%'></td>
			</tr>
			<tr>
				<td width='15%'><b>Payment Instrument</b></td>
				<td width='5%'>:</td>
				<td width='30%'><?= "$instrument" ?></td>
				<td width='15%'></td>
				<td width='5%'></td>
				<td width='30%'></td>
			</tr>
			<tr>
				<td width='15%'><b>Payment Term</b></td>
				<td width='5%'>:</td>
				<td width='30%'><?= "$pay_term" ?></td>
				<td width='15%'></td>
				<td width='5%'></td>
				<td width='30%'></td>
			</tr>
			
		</table>
		
	</div>
</div>


<script>
	$(document).ready(function(){
		var standard_spec 	= $('#standard_spec').val();
		var document 		= $('#document').val();
		var color 			= $('#color').val();
		var test 			= $('#test').val();
		var sertifikat 		= $('#sertifikat').val();
		var abrasi 			= $('#abrasi').val();
		var konduksi 		= $('#konduksi').val();
		var tahan_api 		= $('#tahan_api').val();
		
		if(standard_spec != 'S-NON-01'){
			$('#StandardHide').hide();
		}
		if(document == 'N'){
			$('#DocumentHide').hide();
		}
		if(color == 'N'){
			$('#ColorHide').hide();
		}
		if(test == 'N'){
			$('#TestingHide').hide();
		}
		if(sertifikat == 'N'){
			$('#SertifikatHide').hide();
		}
		if(abrasi == 'N'){
			$('#AbrasiHide').hide();
		}
		if(konduksi == 'N'){
			$('#KonduksiHide').hide();
		}
		if(tahan_api == 'N'){
			$('#FireHide').hide();
		}
	});
</script>