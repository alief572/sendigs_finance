<?php
    $ENABLE_ADD     = has_permission('Inventory_5.Add');
    $ENABLE_MANAGE  = has_permission('Inventory_5.Manage');
    $ENABLE_VIEW    = has_permission('Inventory_5.View');
    $ENABLE_DELETE  = has_permission('Inventory_5.Delete');

foreach ($results['inven'] as $inven){
}	

?>
<style type="text/css">
thead input {
	width: 100%;
}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<div class="box box-primary">
	<!-- /.box-header -->
	<div class="box-body">
		<form id="data_form">
		<div class="row">
				<form id="data_form">
		<input type="hidden" name="id_material" id="id_material" value='<?= $inven->id_material ?>'>
		<div class="row">
		<div id="input1">
			<div class="col-md-12">			    
                <div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="inventory_1">Inventory Type</label>
					</div>
					 <div class="col-md-8">
					  <select id="inventory_1" name="inventory_1"  class="form-control input-sm" readonly required>
					   <option value="">-- Pilih Type --</option>
						<?php foreach ($results['lvl1'] as $lvl1){
						$select = $inven->id_type == $lvl1->id_type ? 'selected' : '';
						?>
						<option value="<?= $lvl1->id_type?>" <?= $select ?>><?= $lvl1->nama?></option>
						<?php } ?>
					  </select>
					 </div>
				</div>
				</div>
			<!--	<div class="col-md-5">
			    <div class="form-group row">
					<div class="col-md-4">
					  <label for="">Nama Category I</label>
					</div>
					<div class="col-md-8">
					  <select id="inventory_2" name="inventory_2" class="form-control input-sm" required>
						<option value="">-- Pilih Kategori --</option>
						<?php foreach ($results['lvl2'] as $lvl2){
						$select = $inven->id_category1 == $lvl2->id_category1 ? 'selected' : '';
						?>
						<option value="<?= $lvl2->id_category1?>" <?= $select ?>><?= $lvl2->nama?></option>
						<?php } ?>
					  </select>
					  </div>
				</div>
				</div>				
				<div class="col-md-5">
			    <div class="form-group row">
					<div class="col-md-4">
					  <label for="">Nama Category II</label>
					</div>
					<div class="col-md-8">
					  <select id="inventory_3" name="inventory_3" class="form-control input-sm" required>
						<option value="">-- Pilih Kategori --</option>
						<?php foreach ($results['lvl3'] as $lvl3){
						$select = $inven->id_category2 == $lvl3->id_category2 ? 'selected' : '';
						?>
						
						<option value="<?= $lvl3->id_category2?>" <?= $select ?>><?= $lvl3->nama?></option>
						<?php } ?>
					  </select>
					  </div>
				</div>
				</div>	
				<div class="col-md-5">
			    <div class="form-group row">
					<div class="col-md-4">
					  <label for="">Nama Category III</label>
					</div>
					<div class="col-md-8">
					  <select id="inventory_4" name="inventory_4" class="form-control input-sm" required>
						<option value="">-- Pilih Kategori --</option>
						<?php foreach ($results['lvl4'] as $lvl4){
						$select = $inven->id_category3 == $lvl4->id_category3 ? 'selected' : '';
						?>
						<option value="<?= $lvl4->id_category3?>" <?= $select ?>><?= $lvl4->nama?></option>
						<?php } ?>
					  </select>
					  </div>
				</div>
				</div>-->	
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Nama Material</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="nama"  name="nama" placeholder="Nama Material" value="<?= $inven->nama ?>" required>
					</div>
				</div>
				</div>
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Nama Umum</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec1"  name="spec1" placeholder="Spesifikasi 1" value="<?= $inven->spec1 ?>" required>
					</div>
				</div>
				</div>				
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Brand</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec2"  name="spec2" placeholder="Spesifikasi 2" value="<?= $inven->spec2 ?>" required>
					</div>
				</div>
				</div>				
				<!--<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Material Sejenis</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec3"  name="spec3" placeholder="Spesifikasi 3" value="<?= $inven->spec3 ?>" required>
					</div>
				</div>
				</div>
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Hardness</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec4"  name="spec4" placeholder="Spesifikasi 4" value="<?= $inven->spec4 ?>" required>
					</div>
				</div>
				</div>
			    <div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Thickness</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec5"  name="spec5" placeholder="Spesifikasi 5" value="<?= $inven->spec5 ?>" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Length</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec6"  name="spec6" placeholder="Spesifikasi 6" value="<?= $inven->spec6 ?>" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Width</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec7"  name="spec7" placeholder="Spesifikasi 7" value="<?= $inven->spec7 ?>" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Density</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec8"  name="spec8" placeholder="Spesifikasi 8" value="<?= $inven->spec8 ?>" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Satuan</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec9"  name="spec9" placeholder="Spesifikasi 9" value="<?= $inven->spec9 ?>" required>
					</div>
				</div>
				</div>  -->
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Min Order Qty</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec10"  name="spec10" placeholder="Spesifikasi 10" value="<?= $inven->spec10 ?>" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Safety Stock</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec11"  name="spec11" placeholder="Spesifikasi 11" value="<?= $inven->spec11 ?>" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Konsumsi Per Bulan</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec12"  name="spec12" placeholder="Spesifikasi 12" value="<?= $inven->spec12 ?>" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Maximum Stock</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec13"  name="spec13" placeholder="Spesifikasi 13" value="<?= $inven->spec13 ?>" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Leadtime Pembelian</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec14"  name="spec14" placeholder="Spesifikasi 14" value="<?= $inven->spec14 ?>" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Keterangan</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec15"  name="spec15" placeholder="Spesifikasi 15" value="<?= $inven->spec15 ?>" required>
					</div>
				</div>
				</div> 
                <div class="col-md-5">
				<div class="form-group row">  
					<div class="col-md-4">
					  <label for="">Foto</label> 
					</div>
					 <div class="col-md-6">           
						<?php
							echo form_input(array('type'=>'file','id'=>'image','name'=>'image','class'=>'form-control input-sm','autocomplete'=>'off','placeholder'=>'Image'));											
						?>
					</div>
					 
				</div>
				</div>  				
			</div>
				
			
		</div>
		</div>
		
		
		<div class="col-sm-8">
					<b>Satuan dan Konversi</b>
					<div class='box-tool pull-right'>
					<?php
						echo form_button(array('type'=>'button','class'=>'btn btn-md btn-success','value'=>'back','content'=>'Add','id'=>'add-payment'));
					?>
					</div>
					<table class='table table-bordered table-striped'>
						<thead>
							<tr class='bg-blue'>
								<td align='center'><b>Satuan Material</b></td>	
								<td align='center'><b>Nilai Konversi </b></td>							
								<td align='center'><b>Satuan Terkecil</b></td>
								<td align='center'><b>Action</b></td>
							</tr>
							
						</thead>
						<tbody id='list_payment'>
						<?php
								if(empty($results['konversi'])){
								}else{
								$loop = 0;
								foreach($results['konversi'] AS $pay){ 
								$loop++;
								
								
								
								echo"<tr id='tr_".$loop."'>";
									        echo"<td class='text-center'>";	
                                            echo" <select name ='data1[$loop][tipe_payment]' id =data1$loop_tipe_payment class='form-control input-sm' required>
												  ";
													foreach ($results['satuan'] as $sat){
													$select = ($pay->nama_satuan == $sat->nama)? 'selected':'';
													echo" <option value=$sat->nama $select >$sat->nama</option>";
											        }
										    echo "</select>";											
											echo"</td>";
											echo"<td>";
												echo form_input(array('name'=>'data1['.$loop.'][pembayaran]','id'=>'data1'.$loop.'_pembayaran','class'=>'form-control input-sm','autocomplete'=>'off'),$pay->konversi);
											echo"</td>";
											 echo"<td class='text-center'>";
											 
											echo" <select id='data1$loop_satuan_kecil' name='data1[$loop][satuan_kecil]'  class='form-control input-sm' required>
												  ";
													foreach ($results['satuan'] as $sat){
													$select = ($pay->satuan_konversi == $sat->nama)? 'selected':'';
													echo" <option value=$sat->nama $select >$sat->nama</option>";
											        }
										    echo "</select>";
											echo"</td>";
											echo"<td class='text-center'>";
												echo"<button type='button' class='btn btn-sm btn-danger' title='Hapus Data' data-role='qtip' onClick='return DelItem(".$loop.");'><i class='fa fa-trash-o'></i></button>";
											echo"</td>";
									echo"</tr>";
								}
							}
							
						?>	
						</tbody>
					</table>
					</div>
					
					<div class="col-sm-8">
					<b>Material Sejenis</b>
					<div class='box-tool pull-right'>
					<?php
						echo form_button(array('type'=>'button','class'=>'btn btn-md btn-success','value'=>'back','content'=>'Add','id'=>'add-material'));
					?>
					</div>
					<table class='table table-bordered table-striped'>
						<thead>
							<tr class='bg-blue'>
								<td align='center'><b>Nama Material</b></td>	
								<td align='center'><b>Action</b></td>
							</tr>
							
						</thead>
						<tbody id='list_material'>
						<?php
								if(empty($results['material'])){
								}else{
								$loop = 0;
								foreach($results['material'] AS $mat){ 
								$loop++;
								
								
								
								echo"<tr id='tr_".$loop."'>";
									        echo"<td>";
												echo form_input(array('name'=>'data2['.$loop.'][material]','id'=>'data2'.$loop.'_material','class'=>'form-control input-sm','autocomplete'=>'off'),$mat->nama_material_sejenis);
											echo"</td>";
											echo"<td class='text-center'>";
												echo"<button type='button' class='btn btn-sm btn-danger' title='Hapus Data' data-role='qtip' onClick='return DelItem(".$loop.");'><i class='fa fa-trash-o'></i></button>";
											echo"</td>";
									echo"</tr>";
								}
							}
							
						?>	
						</tbody>
					</table>
					</div>
					
					<div class="col-sm-8">
					<b>Alternatif Supplier</b>
					<div class='box-tool pull-right'>
					<?php
						echo form_button(array('type'=>'button','class'=>'btn btn-md btn-success','value'=>'back','content'=>'Add','id'=>'add-supplier'));
					?>
					</div>
					<table class='table table-bordered table-striped'>
						<thead>
							<tr class='bg-blue'>
								<td align='center'><b>Nama Supplier</b></td>	
								<td align='center'><b>Action</b></td>
							</tr>
							
						</thead>
						<tbody id='list_supplier'>
					    <?php
								if(empty($results['supplier1'])){
								}else{
								$loop = 0;
								foreach($results['supplier1'] AS $sup){ 
								$loop++;
								
								
								
								echo"<tr id='tr_".$loop."'>";
									       echo"<td class='text-center'>";	
                                            echo" <select name ='data3[$loop][suplier]' id =data3$loop_suplier class='form-control input-sm' required>
												  ";
													foreach ($results['mssupplier'] as $mssup){
													$select = ($sup->id_supplier == $mssup->id_supplier)? 'selected':'';
													echo" <option value=$mssup->id_supplier $select >$mssup->name_supplier</option>";
											        }
										    echo "</select>";											
											echo"</td>";
											echo"<td class='text-center'>";
												echo"<button type='button' class='btn btn-sm btn-danger' title='Hapus Data' data-role='qtip' onClick='return DelItem(".$loop.");'><i class='fa fa-trash-o'></i></button>";
											echo"</td>";
									echo"</tr>";
								}
							}
							
						?>	
						</tbody>
					</table>
		</div>
		
					
					
	   </div>
	  </div>			
	   
	<hr>
	<center>
	<button type="submit" class="btn btn-success btn-sm" name="save" id="save"><i class="fa fa-save"></i>Simpan</button>
	<button type="reset" class="btn btn-danger btn-sm"><i class="fa fa-close"></i> Reset</button>
	<a class="btn btn-warning btn-sm" href="<?= base_url('inventory_5') ?>" title="Back"> <i class="fa fa-reply">&nbsp;</i>Back</a>
	</center>
	</form>
	</div>
	<!-- /.box-body -->
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>

<!-- page script -->
<script type="text/javascript">

    var data_pay	            = <?php echo json_encode($results['satuan_type']);?>;
    var data_supplier	        = <?php echo json_encode($results['Supplier']);?>;		

  	$(function() {
		$('.select2').select2();
  	});
	$("#inventory_1").change(function(){

            // variabel dari nilai combo box kendaraan
            var inventory_1 = $("#inventory_1").val();

            // Menggunakan ajax untuk mengirim dan dan menerima data dari server
            $.ajax({
                url:siteurl+'inventory_3/get_inven2',
                method : "POST",
                data : {inventory_1:inventory_1},
                async : false,
                dataType : 'json',
                success: function(data){
                    var html = '';
                    var i;

                    for(i=0; i<data.length; i++){
                        html += '<option value='+data[i].id_inventory2+'>'+data[i].nm_inventory2+'</option>';
                    }
                    $('#inventory_2').html(html);

                }
            });
        });
	
	// ADD CUSTOMER 
	
	$(document).on('submit', '#data_form', function(e){
		e.preventDefault()
		var data = $('#data_form').serialize();
		var id = $('#id_inventory').val();
		// alert(id);
		swal({
		  title: "Anda Yakin?",
		  text: "Data Customer akan di simpan.",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonClass: "btn-info",
		  confirmButtonText: "Ya, Simpan!",
		  cancelButtonText: "Batal",
		  closeOnConfirm: false
		},
		function(){
		  $.ajax({
			  type:'POST',
			  url:siteurl+'inventory_5/saveEditInventory',
			  dataType : "json",
			  data:data,
			  success:function(result){
				  if(result.status == '1'){
					 swal({
						  title: "Sukses",
						  text : "Data Customer berhasil disimpan.",
						  type : "success"
						},
						function (){
							window.location.href=base_url+'inventory_5';
						})
				  } else {
					swal({
					  title : "Error",
					  text  : "Data error. Gagal insert data",
					  type  : "error"
					})
					
				  }
			  },
			  error : function(){
				swal({
					  title : "Error",
					  text  : "Data error. Gagal request Ajax",
					  type  : "error"
					})
			  }
		  })
		});
		
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
				Template	+='<td align="center">';
				Template	+='<select name="data1['+loop+'][tipe_payment]" id="data1_'+loop+'_tipe_payment" class="form-control input-sm chosen-select">';
					Template	+='<option value="">Select An Option</option>';
					if(!$.isEmptyObject(data_pay)){
						$.each(data_pay,function(key,value){
							Template	+='<option value="'+value+'">'+value+'</option>';
						});
					}
					
				Template	+='</select>';
			Template	+='</td>';	
				Template	+='<td align="left">';
					Template	+='<input type="text" class="form-control input-sm" name="data1['+loop+'][pembayaran]" id="data1_'+loop+'_pembayaran" label="FALSE" div="FALSE">';
				Template	+='</td>';
				Template	+='<td align="center">';
				Template	+='<select name="data1['+loop+'][satuan_kecil]" id="data1_'+loop+'_satuan_kecil" class="form-control input-sm chosen-select">';
					Template	+='<option value="">Select An Option</option>';
					if(!$.isEmptyObject(data_pay)){
						$.each(data_pay,function(key,value){
							Template	+='<option value="'+value+'">'+value+'</option>';
						});
					}
					
				Template	+='</select>';
			Template	+='</td>';	
				
			
			Template	+='<td align="center"><button type="button" class="btn btn-sm btn-danger" title="Hapus Data" data-role="qtip" onClick="return DelItem('+loop+');"><i class="fa fa-trash-o"></i></button></td>';
			Template	+='</tr>';
			$('#list_payment').append(Template);
			$('input[data-role="tglbayar"]').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true			
			});
			});
			
	$('#add-material').click(function(){
			var jumlah	=$('#list_material').find('tr').length;
			if(jumlah==0 || jumlah==null){
				var ada		= 0;
				var loop	= 1;
			}else{
				var nilai		= $('#list_material tr:last').attr('id');
				var jum1		= nilai.split('_');
				var loop		= parseInt(jum1[1])+1;
			}
			
			Template	='<tr id="tr_'+loop+'">';
				Template	+='<td align="left">';
					Template	+='<input type="text" class="form-control input-sm" name="data2['+loop+'][material]" id="data2_'+loop+'_material" label="FALSE" div="FALSE">';
				Template	+='</td>';
				
				
			
			Template	+='<td align="center"><button type="button" class="btn btn-sm btn-danger" title="Hapus Data" data-role="qtip" onClick="return DelItem1('+loop+');"><i class="fa fa-trash-o"></i></button></td>';
			Template	+='</tr>';
			$('#list_material').append(Template);
			$('input[data-role="tglbayar"]').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true			
			});
			});
			
			
			$('#add-supplier').click(function(){
			var jumlah	=$('#list_supplier').find('tr').length;
			if(jumlah==0 || jumlah==null){
				var ada		= 0;
				var loop	= 1;
			}else{
				var nilai		= $('#list_supplier tr:last').attr('id');
				var jum1		= nilai.split('_');
				var loop		= parseInt(jum1[1])+1;
			}
			
			Template	='<tr id="tr_'+loop+'">';
				Template	+='<td align="center">';
				Template	+='<select name="data3['+loop+'][suplier]" id="data3_'+loop+'_suplier" class="form-control input-sm chosen-select">';
					Template	+='<option value="">Select An Option</option>';
					if(!$.isEmptyObject(data_supplier)){
						$.each(data_supplier,function(key,value){
							Template	+='<option value="'+value+'">'+value+'</option>';
						});
					}					
				Template	+='</select>';
			Template	+='</td>';			
			Template	+='<td align="center"><button type="button" class="btn btn-sm btn-danger" title="Hapus Data" data-role="qtip" onClick="return DelItem2('+loop+');"><i class="fa fa-trash-o"></i></button></td>';
			Template	+='</tr>';
			$('#list_supplier').append(Template);
			$('input[data-role="tglbayar"]').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true			
			});
			});

	function PreviewPdf(id)
	{
		param=id;
		tujuan = 'customer/print_request/'+param;

	   	$(".modal-body").html('<iframe src="'+tujuan+'" frameborder="no" width="570" height="400"></iframe>');
	}

	function PreviewRekap()
	{
		tujuan = 'customer/rekap_pdf';
	   	$(".modal-body").html('<iframe src="'+tujuan+'" frameborder="no" width="100%" height="400"></iframe>');
	}
	function DelItem(id){
		$('#list_payment #tr_'+id).remove();
		
	}
</script>
