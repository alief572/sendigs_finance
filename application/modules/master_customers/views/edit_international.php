<?php
    $ENABLE_ADD     = has_permission('master_suplier.Add');
    $ENABLE_MANAGE  = has_permission('master_suplier.Manage');
    $ENABLE_VIEW    = has_permission('master_suplier.View');
    $ENABLE_DELETE  = has_permission('master_suplier.Delete');
	foreach ($results['sup'] as $sup){
}	
?>
 <div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post">
			<div class="col-sm-12">
				<div class="input_fields_wrap2">
					<div class="row">
						
										<center><label for="customer" ><h3>Detail Identitas Siplier</h3></label></center>
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-6">
									    <label for="id_supplier">Id Suplier</label>
									</div>
									<div class="col-md-6">
											<input type="text" class="form-control" id="id_suplier" value='<?= $sup->id_suplier ?>' required name="id_suplier" readonly placeholder="Id Suplier">
									</div>
								</div>
								<div class="form-group row">
									<div class="col-md-6">
										<label for="id_category_supplier">Category Suplier</label>
									</div>
									<div class="col-md-6">
											  <select id="id_category_supplier" name="id_category_supplier" class="form-control select" required>
												<option value="">--pilih--</option>
												<?php foreach ($results['category'] as $category){
												$select = $sup->id_category_supplier == $category->id_category_supplier ? 'selected' : '';
												?>
												<option value="<?= $category->id_category_supplier?>" <?= $select ?>><?= $category->name_category_supplier?></option>
												<?php } ?>
											  </select>
									</div>
								</div>
								<div class="form-group row">
									<div class="col-md-6">
									    <label for="customer">Nama Suplier</label>
									    </div>
									    <div class="col-md-6">
											<input type="text" class="form-control" id="name_suplier" value='<?= $sup->name_suplier ?>' required name="name_suplier" placeholder="Nama Suplier">
									</div>
								</div>
										
										<div class="form-group row">
										<div class="col-md-6">
									    <label for="customer">Telephone</label>
									    </div>
									    <div class="col-md-6">
											<input type="text" class="form-control" id="telephone" value='<?= $sup->telephone ?>' required name="telephone" placeholder="Nomor Telephone">
									    </div>
										</div>
										<div class="form-group row">
										<div class="col-md-6">
									    <label for="customer"></label>
									    </div>
									    <div class="col-md-6">
											<input type="text" class="form-control" id="telephone_2" value='<?= $sup->telephone_2 ?>' name="telephone_2" placeholder="Nomor Telephone">
									    </div>
										</div>
										<div class="form-group row">
										<div class="col-md-6">
									    <label for="customer">Fax</label>
									    </div>
									    <div class="col-md-6">
											<input type="text" class="form-control" id="fax" value='<?= $sup->fax ?>' required name="fax" placeholder="Nomor Fax">
									    </div>
										</div>
										<div class="form-group row">
										<div class="col-md-6">
									    <label for="customer">Email</label>
									    </div>
									    <div class="col-md-6">
											<input type="text" class="form-control" id="email" required name="email" value='<?= $sup->email ?>' placeholder="email@domain.adress">
									    </div>
										</div>
										<div class="form-group row">
										<div class="col-md-6">
									    <label for="customer">Tanggal Mulai</label>
									    </div>
									    <div class="col-md-6">
											<input type="date" class="form-control" id="start_date" required value='<?= $sup->start_date ?>' name="start_date" placeholder="Tanggal Mulai">
									    </div>
										</div>
						</div>
						<div class="col-sm-6">
										<div class="form-group row">
											<div class="col-md-6">
											 <label for="id_category_supplier">Negara</label>
											</div>
											<div class="col-md-6">
											  <select id="id_negara" name="id_negara" class="form-control select" onchange="get_kota()" required>
												<option value="">--Pilih--</option>
												<?php foreach ($results['negara'] as $negara){
												$select = $sup->id_negara == $negara->id_negara ? 'selected' : '';
												?>
												<option value="<?= $negara->id_negara?>" <?= $select ?>><?= $negara->nm_negara?></option>
												<?php } ?>
											  </select>
											</div>
										</div>
										
										<div class="form-group row">
											<div class="col-md-6">
											 <label for="id_category_supplier">Profinsi</label>
											</div>
											<div class="col-md-6">
											  <input type="text" class="form-control" id="international_prov" value="<?= $sup->international_prov?>" required name="international_prov" placeholder="Profinsi">
											</div>
										</div>
										
										<div class="form-group row">
											<div class="col-md-6">
											 <label for="id_category_supplier">Kota</label>
											</div>
											<div class="col-md-6">
											   <input type="text" class="form-control" id="international_kota" value="<?= $sup->international_kota?>" required name="international_kota" placeholder="Kota">
											</div>
										</div>
										
										<div class="form-group row">
										<div class="col-md-6">
									    <label for="customer">Alamat</label>
									    </div>
									    <div class="col-md-6">
										<textarea type="text" name="address_office" id="address_office" class="form-control input-sm required w70" placeholder="Alamat"><?= $sup->address_office ?></textarea>
									    </div>
										</div>
										
										<div class="form-group row">
										<div class="col-md-6">
									    <label for="customer">Kode Pos</label>
									    </div>
									    <div class="col-md-6">
											<input type="text" class="form-control" id="zip_code" required name="zip_code" value='<?= $sup->zip_code ?>' placeholder="Kode Pos">
									    </div>
										</div>
										<div class="form-group row">
										<div class="col-md-6">
									    <label for="customer">Lontitude</label>
									    </div>
									    <div class="col-md-6">
											<input type="text" class="form-control" id="longitude" required name="longitude" value='<?= $sup->longitude ?>' placeholder="Longtitude">
									    </div>
										</div>
										<div class="form-group row">
										<div class="col-md-6">
									    <label for="customer">Latitude</label>
									    </div>
									    <div class="col-md-6">
											<input type="text" class="form-control" id="latitude" required name="latitude" value='<?= $sup->latitude ?>' placeholder="Latitude">
									    </div>
										</div>
							<div class="form-group row">
										<div class="col-md-6">
									    <label for="customer">Status</label>
									    </div>
									    <div class="col-md-6">
										<?php if($sup->activation == 'aktif'){ ?>
										  <label>
											<input type="radio" class="radio-control" id="activation" name="activation" value="aktif" checked required> Aktif
										  </label>
										  <label>
											<input type="radio" class="radio-control" id="activation" name="activation" value="inaktif" required> Inaktif
										  </label>
										<?php }else{ ?>
										  <label>
											<input type="radio" class="radio-control" id="activation" name="activation" value="aktif" required> Aktif
										  </label>
										  <label>
											<input type="radio" class="radio-control" id="activation" name="activation" value="inaktif" checked required> Inaktif
										  </label>
										<?php } ?>
									    </div>
							</div>
						</div>
						<br>
						<div class="col-sm-12">
								<div class="col-md-12">
						<center><label for="customer" ><h3>PIC</h3></label></center>
								</div>
						</div>
							<div class="col-sm-12">
							<?php
								echo form_button(array('type'=>'button','class'=>'btn btn-md btn-success','value'=>'back','content'=>'Add','id'=>'add-payment'));
							?>
							</div>
							<div class="form-group row">
							<div class="col-md-12">
							
								<table class='table table-bordered table-striped'>
										<thead>
											<tr class='bg-blue'>
												<td align='center'><b>Nama PIC</b></td>	
												<td align='center'><b>Nomor Telp</b></td>	
												<td align='center'><b>Email</b></td>	
												<td align='center'><b>Jabatan</b></td>
												<td align='center'><b>Aksi</b></td>
											</tr>
											
										</thead>
										<tbody id='list_payment'>
												<?php
											$loop = 0;
											foreach ($results['pic'] as $pic){
											$loop++;
												echo"<tr id='tr_".$loop."'>";
			echo"<td align='left'><input type='text' class='form-control input-sm' name='data1[".$loop."][name_pic]' value='$pic->name_pic' id='data1_".$loop."_name_pic' label=/FALSE' div='FALSE'></td>";
			echo"<td align='left'><input type='text' class='form-control input-sm' name='data1[".$loop."][phone_pic]' value='$pic->phone_pic'  id='data1_".$loop."_phone_pic' label='FALSE' div='FALSE'></td>";
			echo"<td align='left'><input type='text' class='form-control input-sm' name='data1[".$loop."][email_pic]' value='$pic->email_pic'  id='data1_".$loop."_email_pic' label='FALSE' div='FALSE'></td>";
			echo"<td align='left'><input type='text' class='form-control input-sm' name='data1[".$loop."][position_pic]' value='$pic->position_pic' d='data1_".$loop."_position_pic' label='FALSE' div='FALSE'></td>";
			echo"<td align='center'><button type='button' class='btn btn-sm btn-danger' title='Hapus Data' data-role='qtip' onClick='return DelItem(".$loop.");'><i class='fa fa-trash-o'></i></button></td></tr>";
											
											}?>
										</tbody>
								</table>
							</div>
							</div>
						
						
						<center><label for="customer" ><h3>INFORMASI PEMBAYARAN</h3></label></center>
						
						<div class="col-sm-12">
									<div class="col-md-12">
									    <label for="id_supplier"><h4>Informasi Bank</h4></label>
									</div>
									<div class="form-group row">
										<div class="col-md-3">
									    <label for="id_supplier">Nama Bank</label>
									    </div>
									    <div class="col-md-9">
											<input type="text" class="form-control" value='<?= $sup->name_bank ?>' id="name_bank" required name="name_bank" placeholder="Nama Bank">
									    </div>
									</div>
										
										<div class="form-group row">
											<div class="col-md-3">
											 <label for="id_category_supplier">Nomor Akun</label>
											</div>
											<div class="col-md-9">
												<input type="text" class="form-control" id="no_rekening" value='<?= $sup->no_rekening ?>' required name="no_rekening" placeholder="No Rekening">
											</div>
										</div>
										
										<div class="form-group row">
										<div class="col-md-3">
									    <label for="customer">Nama Akun</label>
									    </div>
									    <div class="col-md-9">
											<input type="text" class="form-control" id="nama_rekening" required value='<?= $sup->nama_rekening ?>' name="nama_rekening" placeholder="Nama Rekening">
									    </div>
										</div>
										
										<div class="form-group row">
										<div class="col-md-3">
									    <label for="customer">Alamat Bank</label>
									    </div>
									    <div class="col-md-9">
										<textarea type="text" name="alamat_bank" id="alamat_bank" class="form-control input-sm required w70"  placeholder="Alamat_Bank"><?= $sup->alamat_bank ?></textarea>
									    </div>
										</div>
										
										<div class="form-group row">
										<div class="col-md-3">
									    <label for="customer">Swift Code</label>
									    </div>
									    <div class="col-md-9">
											<input type="text" class="form-control" id="swift_code" required name="swift_code" value='<?= $sup->swift_code ?>' placeholder="Swift Code">
									    </div>
										</div>
							</div>
							<div class="col-sm-12">
								<div class="col-md-3">
									<label for="customer">Term Of Payment</label>
								</div>
								<div class="col-md-9">
								<?php if($sup->payment_term == 'LC'){ ?>
									  <label>
										<input type="radio" class="radio-control" id="payment_term" name="payment_term" checked value="LC" required> LC
									  </label>
										&nbsp &nbsp &nbsp
									  <label>
										<input type="radio" class="radio-control" id="payment_term" name="payment_term" value="T/T 30 Day After BL Date" required> T/T 30 Day After BL Date
									  </label>
									  &nbsp &nbsp &nbsp
									  <label>
										<input type="radio" class="radio-control" id="payment_term" name="payment_term" value="T/T 60 Day After BL Date" required> T/T 60 Day After BL Date
									  </label>
								<?php }elseif($sup->payment_term == 'T/T 30 Day After BL Date'){ ?>
								 <label>
										<input type="radio" class="radio-control" id="payment_term" name="payment_term" value="LC" required> LC
									  </label>
										&nbsp &nbsp &nbsp
									  <label>
										<input type="radio" class="radio-control" id="payment_term" name="payment_term" checked value="T/T 30 Day After BL Date" required> T/T 30 Day After BL Date
									  </label>
									  &nbsp &nbsp &nbsp
									  <label>
										<input type="radio" class="radio-control" id="payment_term" name="payment_term" value="T/T 60 Day After BL Date" required> T/T 60 Day After BL Date
									  </label>
								<?php }elseif($sup->payment_term == 'T/T 60 Day After BL Date'){ ?>
								 <label>
										<input type="radio" class="radio-control" id="payment_term" name="payment_term" value="LC" required> LC
									  </label>
										&nbsp &nbsp &nbsp
									  <label>
										<input type="radio" class="radio-control" id="payment_term" name="payment_term" value="T/T 30 Day After BL Date" required> T/T 30 Day After BL Date
									  </label>
									  &nbsp &nbsp &nbsp
									  <label>
										<input type="radio" class="radio-control" id="payment_term" name="payment_term" checked value="T/T 60 Day After BL Date" required> T/T 60 Day After BL Date
									  </label>
								<?php }else{ ?>
								 <label>
										<input type="radio" class="radio-control" id="payment_term" name="payment_term" value="LC" required> LC
									  </label>
										&nbsp &nbsp &nbsp
									  <label>
										<input type="radio" class="radio-control" id="payment_term" name="payment_term" value="T/T 30 Day After BL Date" required> T/T 30 Day After BL Date
									  </label>
									  &nbsp &nbsp &nbsp
									  <label>
										<input type="radio" class="radio-control" id="payment_term" name="payment_term" value="T/T 60 Day After BL Date" required> T/T 60 Day After BL Date
									  </label>
								<?php } ?>
									</div>
							</div>
						
						<div class="col-sm-12">
							<center>
								<button type="submit" class="btn btn-success btn-sm" name="save" id="simpan-com"><i class="fa fa-save"></i>Simpan</button>
								</center>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
 </div>

				  
				  
				  
<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';
	
	$(document).ready(function(){
		 var data_pay	        = <?php echo json_encode($results['supplier']);?>;	
		 
				  ///INPUT PERKIRAAN KIRIM
			
			
			var max_fields2      = 10; //maximum input boxes allowed
			var wrapper2         = $(".input_fields_wrap2"); //Fields wrapper
			var add_button2      = $(".add_field_button2"); //Add button ID

			//console.log(persen);

			var x2 = 1; //initlal text box count
			$(add_button2).click(function(e){ //on add input button click
			  e.preventDefault();
			  if(x2 < max_fields2){ //max input box allowed
				x2++; //text box increment
				
				$(wrapper2).append('<div class="row">'+
				'<div class="col-xs-1">'+x2+'</div>'+
				'<div class="col-xs-3">'+
				'<div class="input-group">'+
				'<input type="text" name="hd'+x2+'[produk]"  class="form-control input-sm" value="">'+
				'</div>'+
				'<div class="input-group">'+
				'<input type="text" name="hd'+x2+'[costcenter]"  class="form-control input-sm" value="">'+
				'</div>'+
				'<div class="input-group">'+
				'<input type="text" name="hd'+x2+'[mesin]"  class="form-control input-sm" value="">'+
				'</div>'+
				'<div class="input-group">'+
				'<input type="text" name="hd'+x2+'[mold_tools]"  class="form-control input-sm" value="">'+
				'</div>'+
				'</div>'+
				'<a href="#" class="remove_field2">Remove</a>'+
				'</div>'); //add input box
				$('#datepickerxxr'+x2).datepicker({
				  format: 'dd-mm-yyyy',
				  autoclose: true
				});
			  }
			});

			$(wrapper2).on("click",".remove_field2", function(e){ //user click on remove text
			  e.preventDefault(); $(this).parent('div').remove(); x2--;
			})
			


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
			Template	+='<td align="left">';
					Template	+='<input type="text" class="form-control input-sm" name="data1['+loop+'][name_pic]"  id="data1_'+loop+'_name_pic" label="FALSE" div="FALSE">';
			Template	+='</td>';
			Template	+='<td align="left">';
					Template	+='<input type="text" class="form-control input-sm" name="data1['+loop+'][phone_pic]" id="data1_'+loop+'_phone_pic" label="FALSE" div="FALSE">';
			Template	+='</td>';
			Template	+='<td align="left">';
					Template	+='<input type="text" class="form-control input-sm" name="data1['+loop+'][email_pic]" id="data1_'+loop+'_email_pic" label="FALSE" div="FALSE">';
			Template	+='</td>';
			Template	+='<td align="left">';
					Template	+='<input type="text" class="form-control input-sm" name="data1['+loop+'][position_pic]" id="data1_'+loop+'_position_pic" label="FALSE" div="FALSE">';
			Template	+='</td>';
			Template	+='<td align="center"><button type="button" class="btn btn-sm btn-danger" title="Hapus Data" data-role="qtip" onClick="return DelItem('+loop+');"><i class="fa fa-trash-o"></i></button></td>';
			Template	+='</tr>';
			$('#list_payment').append(Template);
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
				  confirmButtonText: "Yes, Process it!",
				  cancelButtonText: "No, cancel process!",
				  closeOnConfirm: true,
				  closeOnCancel: false
				},
				function(isConfirm) {
				  if (isConfirm) {
						var formData 	=new FormData($('#data-form')[0]);
						var baseurl=siteurl+'master_suplier/saveEditInternational';
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
  function get_kota(){
        var id_prov=$("#id_prov").val();
		 $.ajax({
            type:"GET",
            url:siteurl+'master_suplier/getkota',
            data:"id_prov="+id_prov,
            success:function(html){
               $("#id_kota").html(html);
            }
        });
    }
function DelItem(id){
		$('#list_payment #tr_'+id).remove();
		
	}
</script>