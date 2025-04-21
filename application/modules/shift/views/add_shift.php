 <div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post">
					  <div class="col-sm-8">
						   <div class="input_fields_wrap2">
										
										
									<div class="col-sm-12">
									<div class='box-tool pull-right'>
									<?php
										echo form_button(array('type'=>'button','class'=>'btn btn-md btn-success','value'=>'back','content'=>'Tambah','id'=>'add-payment')); 
									?>
									</div>
									<table class='table table-bordered table-striped'>
										<thead>
											<tr class='bg-blue'>
												<td align='center'><b>Type Shift</b></td>	 
												<td align='center'><b>Day</b></td>							
												<td align='center'><b>Start Work</b></td>
												<td align='center'><b>Start Break 1</b></td>
												<td align='center'><b>Finish Break 1</b></td>
												<td align='center'><b>Start Break 2</b></td>
												<td align='center'><b>Finish Break 2</b></td>
												<td align='center'><b>Start Break 3</b></td>
												<td align='center'><b>Finish Break 3</b></td>
												<td align='center'><b>Finish Work</b></td>
											</tr>
											
										</thead>
										<tbody id='list_payment'>
											
										</tbody>
									</table>
									</div>
						  </div>
						</div>
						<div class="col-sm-3">
						</div>
					  </div>
				  </div> 
				  
				  
				 	<hr>
					<center>
					<!--<button type="submit" class="btn btn-primary btn-sm add_field_button2" name="save"><i class="fa fa-plus"></i>Add Main Produk</button>
					--><button type="submit" class="btn btn-success btn-sm" name="save" id="simpan-com"><i class="fa fa-save"></i>Simpan</button>
					</center>
					
				  </form>
				  
				  
				  
	</div>
</div>	
	
				  
				  
				  
<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';
	
	$(document).ready(function(){
		 var data_pay	        = <?php echo json_encode($results['supplier']);?>;	
		 
	
$('#add-payment').click(function(){
			var jumlah	=$('#list_payment').find('tr').length;
			if(jumlah==0 || jumlah==null){
				var ada		= 0;
				var loop	= 1;
			}else{
				var nilai		= $('#list_payment tr:last').attr('id');
				var jum1		= nilai.split('_');
				var loop		= parseInt(jum1[1])+1; 
			} 
			
			Template	='<tr id="tr_'+loop+'">';
				Template	+='<td align="center">';
					Template	+='<select id="type_shift" name="type_shift" class="form-control select" required>';
						Template	+='<option value="">-- Shift --</option>';
						Template	+='<?php foreach ($results['type_shift'] as $type_shift){ ?>';
						Template	+='<option value="<?= $type_shift->id_type_shift?>"><?= ucfirst(strtolower($type_shift->name_type_shift))?></option>';
						Template	+='<?php } ?>';
					  Template	+='</select>';
				Template	+='</td>';	
				Template	+='<td align="center">';
						Template	+='<select id="id_day" name="id_day" class="form-control select" required>';
						Template	+='<option value="">--Hari --</option>';
						Template	+='<?php foreach ($results['hari'] as $hari){ ?>';
						Template	+='<option value="<?= $hari->id_hari?>"><?= ucfirst(strtolower($hari->day_en))?></option>';
						Template	+='<?php } ?>';
					  Template	+='</select>';
				Template	+='</td>';
			Template	+='<td align="center">';
					Template	+='<input type="time" class="form-control" id="start_work" name="start_work">';
			Template	+='</td>';
			Template	+='<td align="center">';
				Template	+='<input type="time" class="form-control" id="start_break1" name="start_break1">';
			Template	+='</td>';
			Template	+='<td align="center">';
				Template	+='<input type="time" class="form-control" id="done_break1" name="done_break1">';
			Template	+='</td>';
			Template	+='<td align="center">';
				Template	+='<input type="time" class="form-control" id="start_break2" name="start_break2">';
			Template	+='</td>';
			Template	+='<td align="center">';
				Template	+='<input type="time" class="form-control" id="done_break2" name="done_break2">';
			Template	+='</td>';
			Template	+='<td align="center">';
				Template	+='<input type="time" class="form-control" id="start_break3" name="start_break3">';
			Template	+='</td>';
			Template	+='<td align="center">';
				Template	+='<input type="time" class="form-control" id="done_break3" name="done_break3">';
			Template	+='</td>';
						Template	+='<td align="center">';
				Template	+='<input type="time" class="form-control" id="done_work" name="done_work">'; 
			Template	+='</td>';
			Template	+='<td align="center"><button type="button" class="btn btn-sm btn-danger" title="Hapus Data" data-role="qtip" onClick="return DelItem('+loop+');"><i class="fa fa-trash-o"></i></button></td>';
			Template	+='</tr>';
			$('#list_payment').append(Template);
			$('input[data-role="tglbayar"]').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true			
			});
			});
			
			
			
	$('#simpan-com').click(function(e){
			e.preventDefault();
			var deskripsi	= $('#deskripsi').val();
			var image	= $('#image').val();
			var idtype	= $('#inventory_1').val();
			
			var data, xhr;

    
				
			
			
			swal({
				  title: "Are you sure?",
				  text: "You will not be able to process again this data!",
				  type: "warning",
				  showCancelButton: true,
				  confirmButtonClass: "btn-danger",
				  confirmButtonText: "Yes, ",
				  cancelButtonText: "No !",
				  closeOnConfirm: true,
				  closeOnCancel: false
				},
				function(isConfirm) {
				  if (isConfirm) {
						var formData 	=new FormData($('#data-form')[0]);
						var baseurl=siteurl+'shift/saveNewShift';
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

function DelItem(id){
		$('#list_payment #tr_'+id).remove();
		
	}
</script>