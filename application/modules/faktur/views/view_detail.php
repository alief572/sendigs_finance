
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<div class="box box-primary box-solid">
	<div class="box-header">
		<h3 class="box-title">View Detail Faktur</h3>
	</div>
	<div class="box-body">
		<div class='form-group row'>
			<label class="col-sm-2 control-label"><b>ID Generate</b></label>
			<div class='col-sm-4'>
				 <div class="input-group">
					<span class="input-group-addon"><i class="fa fa-file"></i></span>              
					<?php
						echo form_input(array('id'=>'idgen','name'=>'idgen','class'=>'form-control input-sm','autocomplete'=>'off','readOnly'=>true),$row_header[0]['idgen']);											
					?>
				</div>
								
			</div>
			<label class='control-label col-sm-2'><b>Kode Faktur</b></label>
			<div class='col-sm-4'>
				 <div class="input-group">
					<span class="input-group-addon"><i class="fa fa-power-off"></i></span>              
					<?php
						echo form_input(array('id'=>'kode_faktur','name'=>'kode_faktur','class'=>'form-control input-sm','autocomplete'=>'off','readOnly'=>true),$row_header[0]['kode']);											
					?>
				</div>
								
			</div>
		</div>
		<div class='form-group row'>							
			<label class='control-label col-sm-2'><b>Tanggal <span class='text-red'>*</span></b></label>
			<div class='col-sm-4'>
				<div class="input-group">
					<span class="input-group-addon"><i class="fa fa-calendar"></i></span>              
					<?php
						echo form_input(array('id'=>'datet','name'=>'datet','class'=>'form-control input-sm','autocomplete'=>'off','readOnly'=>true),date('d F Y',strtotime($row_header[0]['tanggal'])));											
					?>
				</div>
							
			</div>
			<label class='control-label col-sm-2'><b>Tahun Faktur <span class='text-red'>*</span></b></label>
			<div class='col-sm-4'>
				<div class="input-group">
					<span class="input-group-addon"><i class="fa fa-calendar"></i></span>              
					<?php
						echo form_input(array('id'=>'tahun_faktur','name'=>'tahun_faktur','class'=>'form-control input-sm','autocomplete'=>'off','readOnly'=>true),$row_header[0]['tahun']);											
					?>
				</div>
								
			</div>
		</div>
		<div class='form-group row'>							
			<label class='control-label col-sm-2'><b>No Awal <span class='text-red'>*</span></b></label>
			<div class='col-sm-4'>
				<div class="input-group">
					<span class="input-group-addon"><i class="fa fa-calculator"></i></span>              
					<?php
						echo form_input(array('id'=>'no_awal','name'=>'no_awal','class'=>'form-control input-sm','autocomplete'=>'off','readOnly'=>true),$row_header[0]['noawal']);											
					?>
				</div>
							
			</div>
			<label class='control-label col-sm-2'><b>No Akhir <span class='text-red'>*</span></b></label>
			<div class='col-sm-4'>
				<div class="input-group">
					<span class="input-group-addon"><i class="fa fa-calculator"></i></span>              
					<?php
						echo form_input(array('id'=>'no_akhir','name'=>'no_akhir','class'=>'form-control input-sm','autocomplete'=>'off','readOnly'=>true),$row_header[0]['noakhir']);											
					?>
				</div>
								
			</div>
		</div>
	</div>
	<div class="box-body">
	<?php
	
		if(isset($row_detail) && $row_detail){
			echo"<table id='detail_view' class='table table-bordered table-striped'>";
				echo"<thead>";
					echo"<tr class='bg-blue'>";
						echo"<th class='text-center'>No</th>";
						echo"<th class='text-center'>Faktur ID</th>";
						echo"<th class='text-center'>No Invoice</th>";
						echo"<th class='text-center'>Tgl Invoice</th>";
						echo"<th class='text-center'>No Faktur</th>";
						echo"<th class='text-center'>Status</th>";
					echo"</tr>";
				echo"</thead>";
				echo"<tbody>";
					$loop		= 0;
					foreach($row_detail as $key=>$vals){
						$loop++;
						$Status			= $vals['sts'];
						$Invoice		= $vals['noinvoice'];
						$Ket			= "<span class='badge bg-green'>OPEN</span>";
						if($Status=='1'){
							if($Invoice=='' || $Invoice=='-'){
								$Ket			= "<span class='badge bg-red'>BLOCKED</span>";
							}else{
								$Ket			= "<span class='badge bg-maroon'>USED</span>";
							}
						}
						$Tgl_Invoice	= '-';
						if($vals['tglinvoice']!='' && $vals['tglinvoice']!='-'){
							$Tgl_Invoice	= date('d M Y',strtotime($vals['tglinvoice']));
						}
						echo"<tr>";
							echo"<td class='text-center'>".$loop."</td>";
							echo"<td class='text-center'>".$vals['fakturid']."</td>";
							echo"<td class='text-center'>".$Invoice."</td>";
							echo"<td class='text-center'>".$Tgl_Invoice."</td>";
							echo"<td class='text-center'>".$vals['nofaktur']."</td>";
							echo"<td class='text-center'>".$Ket."</td>";
						echo"</tr>";
					}
				echo"</tbody>";
			echo"</table>";
		}
		
	?>
	</div>
</div>
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>

<script type="text/javascript">
    $(document).ready(function(){       
		$("#detail_view").DataTable();
    });
    
</script>
