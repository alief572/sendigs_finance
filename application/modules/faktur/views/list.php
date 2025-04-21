<?php
    $ENABLE_ADD     = has_permission('Faktur.Add');
    $ENABLE_MANAGE  = has_permission('Faktur.Manage');
    $ENABLE_VIEW    = has_permission('Faktur.View');
    $ENABLE_DELETE  = has_permission('Faktur.Delete');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">

	<div class="box">
		 <div class="box-header">
			<?php if ($ENABLE_ADD) : ?>
				<button type="button" class='btn btn-sm btn-success' id='btn-add'><i class="fa fa-plus"></i> Generate Faktur</button>
								
			<?php endif; ?>

			
		</div>
		<div class="box-body">
			<table id="example1" class="table table-bordered table-striped">
				<thead>
					<tr class="bg-blue">
					  <th width="2%" class="text-center">#</th>
					  <th width="30%" class="text-center">ID Generate</th>
					  <th class="text-center">Tanggal</th>
					  <th class="text-center">Tahun</th>
					  <th class="text-center">No Awal</th>
					  <th class="text-center">No Akhir</th>
					  <th class="text-center">Status</th>
					  <th class="text-center">Aksi</th>
					</tr>
				</thead>
				<tbody>
				<?php
				$n=0;
				if(@$results){
					foreach(@$results as $kr=>$vr){
					$n++;
				?>
					<tr>		
					  <td class='text-center'><?php echo $n?></td>
					  <td class='text-left'><?php echo $vr->idgen?></td>
					  <td class='text-center'><?php echo date('d M Y',strtotime($vr->tanggal));?></td>
					  <td class='text-center'><?php echo $vr->tahun?></td>
					  <td class='text-center'><?php echo $vr->noawal?></td>
					  <td class='text-center'><?php echo $vr->noakhir?></td>
					  <td class='text-center'>
						<?php
							if($vr->status=='1'){
								echo"<span class='badge bg-green'>AKTIF</span>";
							}else{
								echo"<span class='badge bg-red'>NON AKTIF</span>";
							}
						?>
					  </td>
					  <td class='text-center'>
						<?php
						echo"<a href='#' onClick='view_data(\"".$vr->kode_req."\")' class='btn btn-sm btn-default' title='View Data' data-role='qtip'><i class='fa fa-search'></i></a>";
						if ($ENABLE_MANAGE) : 
							echo"&nbsp;<a href='#' onClick='edit_data(\"".$n."\")' class='btn btn-sm btn-info' title='Set Aktif' data-role='qtip'><i class='fa fa-check'></i></a>";
						endif;
						echo"<input type='hidden' id='kode_hide_".$n."' value='".$vr->idgen."'>";
						?>
					  
					  </td>
					  
					</tr>
				<?php 
					}
				} 
				?>
				</tbody>
			
		   </table>
		</div>
	</div>
  
<div class="modal fade" id="myAktifModal">
	<div class="modal-dialog" style="width:50%">
		<div class="modal-content">				
			<div class="modal-body" id="detail_all_approve">
				<form action="<?= site_url(strtolower($this->uri->segment(1).'/set_sktif'))?>" method="POST" id='form_proses_aktif'>
					<div class="box box-primary box-solid">
						<div class="box-header">
							<h3 class="box-title">SET AKTIF FAKTUR</h3>
						</div>
						<div class="box-body">
							<div class='form-group row'>
								<label class='label-control col-sm-3'><b>ID Generate</b></label>
								<div class='col-sm-8'>
									 <div class="input-group">
										<span class="input-group-addon"><i class="fa fa-file"></i></span>              
										<?php
											echo form_input(array('id'=>'idgen_det','name'=>'idgen_det','class'=>'form-control input-sm','autocomplete'=>'off','readOnly'=>true));											
										?>
									</div>
								</div>
							</div>
							<div class='form-group row'>			
								<label class='label-control col-sm-3'><b>AKTIF ?</b></label>
								<div class='col-sm-8' id='btnAprv_all'>
								<?php
									echo "<input type='radio' name='approval_all' id='approval_all1' value='Y' checked><b><font color='#009900'>&nbsp; YES</font></b>";
									echo "&nbsp;&nbsp;";
									echo "<input type='radio' name='approval_all' id='approval_all2'  value='N'><b><font color='#cc0000'>&nbsp; NO</font></b>";
								?>
					
								</div>
								
							</div>
							
						</div>
						<div class="box-footer">
							<?php
							echo form_button(array('type'=>'button','class'=>'btn btn-md btn-success','value'=>'tambah','content'=>'SUMBIT','id'=>'simpan_bro_aktif')).' ';
							?>
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						</div>
					</div>
				</form>
			</div>
			
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div>


 
<div class="modal fade" id="myAddModal" >
	<div class="modal-dialog" style="width:80%">
		<div class="modal-content">				
			<div class="modal-body" id="detail_all_approve">
				<form action="<?= site_url(strtolower($this->uri->segment(1).'/add'))?>" method="POST" id='form_proses_add'> 
					<div class="box box-info box-solid">
						<div class="box-header">
							<h3 class="box-title">GENERATE FAKTUR</h3>
						</div>
						<div class="box-body">
							
							<div class='form-group row'>
								<label class="col-sm-2 control-label"><b>ID Generate <span class='text-red'>*</span></b></label>
								<div class='col-sm-4'>
									 <div class="input-group">
										<span class="input-group-addon"><i class="fa fa-file"></i></span>              
										<?php
											echo form_input(array('id'=>'idgen','name'=>'idgen','class'=>'form-control input-sm','autocomplete'=>'off','placeholder'=>'ID Generate Faktur'));											
										?>
									</div>
													
								</div>
								<label class='control-label col-sm-2'><b>Kode Faktur <span class='text-red'>*</span></b></label>
								<div class='col-sm-4'>
									 <div class="input-group">
										<span class="input-group-addon"><i class="fa fa-power-off"></i></span>              
										<?php
											echo form_input(array('id'=>'kode_faktur','name'=>'kode_faktur','class'=>'form-control input-sm','autocomplete'=>'off','placeholder'=>'Kode Faktur'));											
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
											echo form_input(array('id'=>'datet','name'=>'datet','class'=>'form-control input-sm','autocomplete'=>'off','placeholder'=>'Tanggal','readOnly'=>true));											
										?>
									</div>
												
								</div>
								<label class='control-label col-sm-2'><b>Tahun Faktur <span class='text-red'>*</span></b></label>
								<div class='col-sm-4'>
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-calendar"></i></span>              
										<?php
											echo form_input(array('id'=>'tahun_faktur','name'=>'tahun_faktur','class'=>'form-control input-sm','autocomplete'=>'off','placeholder'=>'Masa Faktur'));											
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
											echo form_input(array('id'=>'no_awal','name'=>'no_awal','class'=>'form-control input-sm','autocomplete'=>'off','placeholder'=>'No Awal'));											
										?>
									</div>
												
								</div>
								<label class='control-label col-sm-2'><b>No Akhir <span class='text-red'>*</span></b></label>
								<div class='col-sm-4'>
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-calculator"></i></span>              
										<?php
											echo form_input(array('id'=>'no_akhir','name'=>'no_akhir','class'=>'form-control input-sm','autocomplete'=>'off','placeholder'=>'No Awal'));											
										?>
									</div>
													
								</div>
							</div>
						</div>
						<div class="box-footer">
							<?php
							echo form_button(array('type'=>'button','class'=>'btn btn-md btn-success','value'=>'approve','content'=>'SUMBIT','id'=>'simpan_bro_add')).' ';
							?>
							<button type="button" class="btn btn-md btn-danger" data-dismiss="modal">Close</button>
						</div>
					</div>
				</form>
			</div>
			
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div>

<!-- Modal -->
<div class="modal fade" id="myViewModal" >
	<div class="modal-dialog" style="width:80%">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				
			</div>
			<div class="modal-body" id="det_view">
			
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>

<link type="text/css" rel="stylesheet" href="<?= base_url('assets/plugins/sweetalert/dist/sweetalert.css')?>">
<script src="<?= base_url('assets/plugins/sweetalert/dist/sweetalert.min.js')?>"></script>
<script src="<?= base_url('assets/dist/jquery.maskedinput.min.js')?>"></script>

<!-- page script -->
<script type="text/javascript">
	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';
    $(function() {
      var dataTable = $("#example1").DataTable();
	  $('#no_akhir, #no_awal').mask('?99999999');
	  $('#kode_faktur').mask('?999');
	  $('#tahun_faktur').mask('?9999');
	  $('#datet').datepicker({
          format: 'dd-mm-yyyy',
          todayHighlight: true,         
          autoclose: true
      });
	  /*
	  $("#datet").datepicker({
			changeMonth		: true,
			changeYear		: true,
			showButtonPanel	: false,
			dateFormat		: 'yy-mm-dd',
			maxDate			:'+0d'
		});
	  */
	  $('#btn-add').click(function(){
		  $('#form_proses_add')[0].reset();
		  $('#myAddModal').modal('show');
	  });
	  
	  $('#simpan_bro_add').click(function(e){
		  e.preventDefault();		  
		  var kode_gen		= $('#idgen').val();
		  var kode_faktur	= $('#kode_faktur').val();
		  var tanggal		= $('#datet').val();
		  var tahun_faktur	= $('#tahun_faktur').val();
		  var no_awal		= $('#no_awal').val();
		  var no_akhir		= $('#no_akhir').val();
		  if(kode_gen=='' || kode_gen==null || kode_gen=='-'){
				swal({
				  title: "Error Message!",
				  text: 'ID Generate Belum Diinput, Mohon Input ID Generate Terlebih Dahulu.....',
				  type: "warning",								  
				  timer: 5000
				});
				
				return false;
		  }
		  
		  if(kode_faktur=='' || kode_faktur==null || kode_faktur=='-'){
				swal({
				  title: "Error Message!",
				  text: 'Kode Faktur Belum Diinput, Mohon Input Kode Faktur Terlebih Dahulu.....',
				  type: "warning",								  
				  timer: 5000
				});
				
				return false;
		  }
		  if(tanggal=='' || tanggal==null || tanggal=='-'){
				swal({
				  title: "Error Message!",
				  text: 'Tanggal Faktur Belum Diinput, Mohon Input Tanggal Faktur Terlebih Dahulu.....',
				  type: "warning",								  
				  timer: 5000
				});
				
				return false;
		  }
		  
		  if(tahun_faktur=='' || tahun_faktur==null || tahun_faktur=='-'){
				swal({
				  title: "Error Message!",
				  text: 'Masa Faktur Belum Diinput, Mohon Input Masa Faktur Terlebih Dahulu.....',
				  type: "warning",								  
				  timer: 5000
				});
				
				return false;
		  }
		  
		  if(no_awal=='' || no_awal==null || no_awal=='-'){
				swal({
				  title: "Error Message!",
				  text: 'No Awal Faktur Belum Diinput, Mohon Input No Awal Faktur Terlebih Dahulu.....',
				  type: "warning",								  
				  timer: 5000
				});
				
				return false;
		  }
		  
		  if(no_akhir=='' || no_akhir==null || no_akhir=='-'){
				swal({
				  title: "Error Message!",
				  text: 'No Akhir Faktur Belum Diinput, Mohon Input No Akhir Faktur Terlebih Dahulu.....',
				  type: "warning",								  
				  timer: 5000
				});
				
				return false;
		  }
		  
		  swal({
				  title: "Are you sure?",
				  text: "You will not be able to process again this data!",
				  type: "warning",
				  showCancelButton: true,
				  confirmButtonClass: "btn-danger",
				  confirmButtonText: "Yes, Process it!",
				  cancelButtonText: "No, cancel process!",
				  closeOnConfirm: false,
				  closeOnCancel: false,
				  showLoaderOnConfirm: true
				},
				function(isConfirm) {
				  if (isConfirm) {
						
						var formData 	=new FormData($('#form_proses_add')[0]);
						var baseurl=base_url + active_controller +'/add';
						$.ajax({
							url			: baseurl,
							type		: "POST",
							data		: formData,
							cache		: false,
							dataType	: 'json',
							processData	: false, 
							contentType	: false,				
							success		: function(data){
								
								var kode_bast	= data.kode;
								if(data.status == 1){											
									swal({
										  title	: "Save Success!",
										  text	: data.pesan,
										  type	: "success",
										  timer	: 15000,
										  showCancelButton	: false,
										  showConfirmButton	: false,
										  allowOutsideClick	: false
										});
									window.location.href = base_url + active_controller;
								}else{
									
									if(data.status == 2){
										swal({
										  title	: "Save Failed!",
										  text	: data.pesan,
										  type	: "danger",
										  timer	: 10000,
										  showCancelButton	: false,
										  showConfirmButton	: false,
										  allowOutsideClick	: false
										});
									}else{
										swal({
										  title	: "Save Failed!",
										  text	: data.pesan,
										  type	: "warning",
										  timer	: 10000,
										  showCancelButton	: false,
										  showConfirmButton	: false,
										  allowOutsideClick	: false
										});
									}
									
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
	  
	  $('#simpan_bro_aktif').click(function(e){
		  e.preventDefault();
		  swal({
				  title: "Are you sure?",
				  text: "You will not be able to process again this data!",
				  type: "warning",
				  showCancelButton: true,
				  confirmButtonClass: "btn-danger",
				  confirmButtonText: "Yes, Process it!",
				  cancelButtonText: "No, cancel process!",
				  closeOnConfirm: false,
				  closeOnCancel: false,
				  showLoaderOnConfirm: true
				},
				function(isConfirm) {
				  if (isConfirm) {
						//$('#spinner').modal('show');
						var formData 	=new FormData($('#form_proses_aktif')[0]);
						var baseurl=base_url + active_controller +'/edit';
						$.ajax({
							url			: baseurl,
							type		: "POST",
							data		: formData,
							cache		: false,
							dataType	: 'json',
							processData	: false, 
							contentType	: false,				
							success		: function(data){
								//$('#spinner').modal('hide');
								var kode_bast	= data.kode;
								if(data.status == 1){											
									swal({
										  title	: "Save Success!",
										  text	: data.pesan,
										  type	: "success",
										  timer	: 15000,
										  showCancelButton	: false,
										  showConfirmButton	: false,
										  allowOutsideClick	: false
										});
									window.location.href = base_url + active_controller;
								}else{
									//$('#spinner').modal('hide');
									if(data.status == 2){
										swal({
										  title	: "Save Failed!",
										  text	: data.pesan,
										  type	: "danger",
										  timer	: 10000,
										  showCancelButton	: false,
										  showConfirmButton	: false,
										  allowOutsideClick	: false
										});
									}else{
										swal({
										  title	: "Save Failed!",
										  text	: data.pesan,
										  type	: "warning",
										  timer	: 10000,
										  showCancelButton	: false,
										  showConfirmButton	: false,
										  allowOutsideClick	: false
										});
									}
									
								}
							},
							error: function() {
								//$('#spinner').modal('hide');
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
    });
    function view_data(kode){		
		var baseurl=base_url + active_controller+'/view/'+kode; 
		$.ajax({
			url			: baseurl,
			type		: "get",			
			success		: function(data){
				$('#det_view').empty();
				$('#det_view').html(data);
				$("#detail_view").DataTable();
				$('#myViewModal').modal('show');
			}
		});	
		
		
    }
    function edit_data(kode){
		var kode_gen	= $('#kode_hide_'+kode).val();
		$('#idgen_det').val(kode_gen);
		$('#myAktifModal').modal('show');
    }
    
</script>