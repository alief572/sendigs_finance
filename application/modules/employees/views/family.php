<?php
$this->load->view('include/side_menu'); 
//echo"<pre>";print_r($data_companies);
?> 
<form action="#" method="POST" id="form_proses_bro">   
	<div class="box box-primary">
		<div class="box-header">
			<h3 class="box-title"><?=$title;?></h3>		
		</div>
		<!-- /.box-header -->
		<div class="box-body">
		<div class='form-group row'>			
				<label class='label-control col-sm-2'><b>Employee ID <span class='text-red'>*</span></b></label> 
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-file"></i></span>              
						<?php
							echo form_input(array('readonly'=>'readonly','id'=>'employee_id','name'=>'employee_id','class'=>'form-control input-sm','autocomplete'=>'off','placeholder'=>'Employee Id'),$row[0]->id);											
						?>
					</div>
							
				</div>
			</div>
		<div class='form-group row'>			
				<label class='label-control col-sm-2'><b>Employee NIK <span class='text-red'>*</span></b></label> 
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-file"></i></span>              
						<?php
							echo form_input(array('readonly'=>'readonly','id'=>'employee_nik','name'=>'employee_nik','class'=>'form-control input-sm','autocomplete'=>'off','placeholder'=>'Employee NIK'),$row[0]->nik);											
						?>
					</div>
							
				</div>
		</div>
		<div class='form-group row'>
			    <label class='label-control col-sm-2'><b>Family <span class='text-red'>*</span></b></label>
					<div class='col-sm-4'>
						<div class='input-group'>
							<span class='input-group-addon'><i class='fa fa-user'></i></span> 
								<select id='family' name='family' class='form-control'>
									<option value='' selected>Select An Option</option>
									<option value='anak'>Anak</option>
									<option value='istri'>Istri</option>
									<option value='suami'>Suami</option>
								</select>
						</div>
					</div>
		</div>		
		<div class='form-group row'>			
				<label class='label-control col-sm-2'><b>Family Name <span class='text-red'>*</span></b></label> 
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-file"></i></span>              
						<?php
							echo form_input(array('id'=>'family_name','name'=>'family_name','class'=>'form-control input-sm','autocomplete'=>'off','placeholder'=>'Family Name'));											
						?>
					</div>
							
				</div>
		</div>
								
		</div>
		
		<div class='box-footer'>
			<?php
			echo form_button(array('type'=>'button','class'=>'btn btn-md btn-primary','value'=>'save','content'=>'Save','id'=>'simpan-bro')).' ';
			echo form_button(array('type'=>'button','class'=>'btn btn-md btn-danger','value'=>'back','content'=>'Back','onClick'=>'javascript:back()'));
			?>
		</div>
		<!-- /.box-body -->
	 </div>
  <!-- /.box -->
</form>

<?php $this->load->view('include/footer'); ?>
<script>
	$(document).ready(function(){
		$('#simpan-bro').click(function(e){
			e.preventDefault();
			var nama	= $('#family').val();
			var family	= $('#family_name').val();
			
			if(nama=='' || nama==null){
				swal({
				  title	: "Error Message!",
				  text	: 'Empty Family, please input Family first.....',
				  type	: "warning"
				});
				
				return false;
				
			}
			
			if(family=='' || family==null){
				swal({
				  title	: "Error Message!",
				  text	: 'Empty Family Name, please input Family name first.....',
				  type	: "warning"
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
				  closeOnConfirm: true,
				  closeOnCancel: false
				},
				function(isConfirm) {
				  if (isConfirm) {
						loading_spinner();
						var formData 	=new FormData($('#form_proses_bro')[0]);
						var baseurl=base_url + active_controller +'/addHisfamily';
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
									window.location.href = base_url + active_controller;
								}else{
									
									if(data.status == 2){
										swal({
										  title	: "Save Failed!",
										  text	: data.pesan,
										  type	: "warning",
										  timer	: 7000,
										  showCancelButton	: false,
										  showConfirmButton	: false,
										  allowOutsideClick	: false
										});
									}else{
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
	});
</script>
