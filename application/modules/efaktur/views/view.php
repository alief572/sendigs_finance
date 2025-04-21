
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<div class="box box-primary box-solid">
	<div class="box-header">
		<h3 class="box-title">View Detail E-Faktur</h3>
	</div>
	<div class="box-body">
		<div class='form-group row'>
			<label class="col-sm-2 control-label"><b>No Export</b></label>
			<div class='col-sm-4'>
				 <div class="input-group">
					<span class="input-group-addon"><i class="fa fa-file"></i></span>              
					<?php
						echo form_input(array('id'=>'id_export','name'=>'idgen','class'=>'form-control input-sm','autocomplete'=>'off','readOnly'=>true),$row_header[0]['id_export']);											
					?>
				</div>
								
			</div>
			<label class='control-label col-sm-2'><b>Tanggal <span class='text-red'>*</span></b></label>
			<div class='col-sm-4'>
				<div class="input-group">
					<span class="input-group-addon"><i class="fa fa-calendar"></i></span>              
					<?php
						echo form_input(array('id'=>'datet','name'=>'datet','class'=>'form-control input-sm','autocomplete'=>'off','readOnly'=>true),date('d F Y',strtotime($row_header[0]['date_export'])));											
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
						echo"<th class='text-center'>No Invoice</th>";
						echo"<th class='text-center'>Tgl Invoice</th>";
						echo"<th class='text-center'>Customer</th>";
						echo"<th class='text-center'>Total</th>";
						echo"<th class='text-center'>No Faktur</th>";
					echo"</tr>";
				echo"</thead>";
				echo"<tbody>";
					$loop		= 0;
					foreach($row_detail as $key=>$vals){
						$loop++;
						$Tgl_Invoice	= date('d M Y',strtotime($vals['tanggal_invoice']));
						
						echo"<tr>";
							echo"<td class='text-center'>".$loop."</td>";
							echo"<td class='text-center'>".$vals['no_invoice']."</td>";
							echo"<td class='text-center'>".$Tgl_Invoice."</td>";
							echo"<td class='text-left'>".$vals['nm_customer']."</td>";
							echo"<td class='text-right'>".number_format($vals['hargajualtotal'])."</td>";
							echo"<td class='text-center'>".$vals['nofakturpajak']."</td>";
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
