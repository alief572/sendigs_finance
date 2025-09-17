<?php
    $ENABLE_ADD     = has_permission('Inventory_5.Add');
    $ENABLE_MANAGE  = has_permission('Inventory_5.Manage');
    $ENABLE_VIEW    = has_permission('Inventory_5.View');
    $ENABLE_DELETE  = has_permission('Inventory_5.Delete');
?>
</style>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<link rel="stylesheet" href="<?= base_url('assets/plugins/sweetalert/dist/sweetalert.css')?>">
		
<div class="box box-primary">
	<!-- /.box-header -->
	<div class="box-body">
	<div id="data_material" >
		<form id="data-form" >
		<div class="row">
		<div id="input1">
			<div class="col-md-12">			    
                <div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="inventory_1">Inventory Type</label>
					</div>
					 <div class="col-md-8">
					  <select id="inventory_1" name="inventory_1" class="form-control select" onchange="get_inv2()" required>
						<option value="">-- Pilih Type --</option>
						<?php foreach ($results['inventory_1'] as $inventory_1){ 
						?>
						<option value="<?= $inventory_1->id_type?>"><?= ucfirst(strtolower($inventory_1->nama))?></option>
						<?php } ?>
					  </select>
					 </div>
				</div>
				</div>
				<div class="col-md-5">
			    <div class="form-group row">
					<div class="col-md-4">
					  <label for="">Nama Category I</label>
					</div>
					<div class="col-md-8">
					  <select id="inventory_2" name="inventory_2" class="form-control select" onchange="get_inv3()" required>
						<option value="">-- Pilih Category I --</option>
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
					  <select id="inventory_3" name="inventory_3" class="form-control select" onchange="get_inv4()" required>
						<option value="">-- Pilih Category II --</option>
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
						<option value="">-- Pilih Category III --</option>
					  </select>
					  </div>
				</div>
				</div>	
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Nama Material</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="nama"  name="nama" placeholder="Nama Material" value="" required>
					</div>
				</div>
				</div>
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Nama Umum</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec1"  name="spec1" placeholder="Spesifikasi 1" value="" required>
					</div>
					 <!--<div class="col-md-2" ><select id="sat" name="sat"  class="form-control input-sm" required>
						<option value="m">M</option>						
						<option value="kg">Kg</option>
						
					  </select></div>-->
				</div>
				</div>				
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Brand</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec2"  name="spec2" placeholder="Spesifikasi 2" value="" required>
					</div>
				</div>
				</div>				
				<!--<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Material Sejenis</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec3"  name="spec3" placeholder="Spesifikasi 3" value="" required>
					</div>
				</div>
				</div>-->
				
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Hardness</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec4"  name="spec4" placeholder="Spesifikasi 4" value="" required>
					</div>
				</div>
				</div>
			    <div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Thickness</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec5"  name="spec5" placeholder="Spesifikasi 5" value="" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Length</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec6"  name="spec6" placeholder="Spesifikasi 6" value="" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Width</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec7"  name="spec7" placeholder="Spesifikasi 7" value="" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Density</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec8"  name="spec8" placeholder="Spesifikasi 8" value="" required>
					</div>
				</div>
				</div>  
				<!--<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Satuan</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec9"  name="spec9" placeholder="Spesifikasi 9" value="" required>
					</div>
				</div>
				</div>  -->
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Min Order Qty</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec10"  name="spec10" placeholder="Spesifikasi 10" value="" required>
					</div>
				</div>
				</div>
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Safety Stock</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec11"  name="spec11" placeholder="Spesifikasi 11" value="" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Order Point</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec12"  name="spec12" placeholder="Spesifikasi 12" value="" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Maximum Stock</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec13"  name="spec13" placeholder="Spesifikasi 13" value="" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Leadtime</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec14"  name="spec14" placeholder="Spesifikasi 14" value="" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Keterangan</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec15"  name="spec15" placeholder="Spesifikasi 15" value="" required>
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
							
						</tbody>
					</table>
		</div>
					
					
		</div>
		</div>				
	<hr>
	<center>
	<button type="submit" class="btn btn-success btn-sm" name="save" id="simpan-com"><i class="fa fa-save"></i>Simpan</button>
	<button type="reset" class="btn btn-danger btn-sm"><i class="fa fa-close"></i> Reset</button>
	<a class="btn btn-warning btn-sm" href="<?= base_url('inventory_5') ?>" title="Back"> <i class="fa fa-reply">&nbsp;</i>Back</a>
	</center>
	</form>
	
	<!-- /.box-body -->
</div>


<!-- Modal Bidus-->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/sweetalert/dist/sweetalert.js')?>"></script>
<!-- End Modal Bidus-->

<script type="text/javascript">

  $(document).ready(function() {
	
     var data_pay	        = <?php echo json_encode($results['satuan_type']);?>;	
	 var data_supplier	        = <?php echo json_encode($results['Supplier']);?>;	
	  
	$('.select2').select2();
	$("#inventory_1_old").change(function(){

            // variabel dari nilai combo box kendaraan
            var inventory_1 = $("#inventory_1").val();

            // Menggunakan ajax untuk mengirim dan dan menerima data dari server
            $.ajax({
                url:siteurl+'inventory_5/get_inven2',
                method : "POST",
                data : {inventory_1:inventory_1},
                async : false,
                dataType : 'json',
                success: function(data){
                    var html = '';
                    var i;

                    for(i=0; i<data.length; i++){
                        html += '<option value='+data[i].id_category1+'>'+data[i].nama+'</option>';
                    }
                    $('#inventory_2').html(html);

                }
            });
        });
	
	$("#inventory_2_old").change(function(){

            // variabel dari nilai combo box kendaraan
            var inventory_2 = $("#inventory_2").val();

            // Menggunakan ajax untuk mengirim dan dan menerima data dari server
            $.ajax({
                url:siteurl+'inventory_5/get_inven3',
                method : "POST",
                data : {inventory_2:inventory_2},
                async : false,
                dataType : 'json',
                success: function(data){
                    var html = '';
                    var i;

                    for(i=0; i<data.length; i++){
                        html += '<option value='+data[i].id_category2+'>'+data[i].nama+'</option>';
                    }
                    $('#inventory_3').html(html);

                }
            });
        });
	
	$("#inventory_3_old").change(function(){

            // variabel dari nilai combo box kendaraan
            var inventory_3 = $("#inventory_3").val();

            // Menggunakan ajax untuk mengirim dan dan menerima data dari server
            $.ajax({
                url:siteurl+'inventory_5/get_inven4',
                method : "POST",
                data : {inventory_3:inventory_3},
                async : false,
                dataType : 'json',
                success: function(data){
                    var html = '';
                    var i;

                    for(i=0; i<data.length; i++){
                        html += '<option value='+data[i].id_category3+'>'+data[i].nama+'</option>';
                    }
                    $('#inventory_4').html(html);

                }
            });
        });
    // $(document).on('submit', '#data_form', function(e){
		// e.preventDefault()
		// var formData 	=new FormData($('#data_form')[0]);
		// alert(data);

		// swal({
		  // title: "Anda Yakin?",
		  // text: "Data Material akan di simpan.",
		  // type: "warning",
		  // showCancelButton: true,
		  // confirmButtonClass: "btn-info",
		  // confirmButtonText: "Ya, Simpan!",
		  // cancelButtonText: "Batal",
		  // closeOnConfirm: false
		// },
		// function(){
		  // $.ajax({
			  // type:'POST',
			  // url:siteurl+'inventory_5/saveNewinventory',
			  // dataType : "json",
			  // data:formData,
			  // success:function(result){
				  // if(result.status == '1'){
					 // swal({
						  // title: "Sukses",
						  // text : "Data Inventory berhasil disimpan.",
						  // type : "success"
						// },
						// function (){
							// window.location.href = base_url+'inventory_5';
						// })
				  // } else {
					// swal({
					  // title : "Error",
					  // text  : "Data error. Gagal insert data",
					  // type  : "error"
					// })
					
				  // }
			  // },
			  // error : function(){
				// swal({
					  // title : "Error",
					  // text  : "Data error. Gagal request Ajax",
					  // type  : "error"
					// })
			  // }
		  // })
		// });
		
	// })
	
	
	$('#simpan-com').click(function(e){
			e.preventDefault();
			var deskripsi	= $('#deskripsi').val();
			var image	= $('#image').val();
			
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
						var formData 	=new FormData($('#data-form')[0],$('#data2-form')[0]);
						var baseurl=siteurl+'inventory_5/saveNewinventory';
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
   
   });
  
  
  

   	function get_inv2(){
        var inventory_1=$("#inventory_1").val();
		 $.ajax({
            type:"GET",
            url:siteurl+'inventory_5/get_inven2',
            data:"inventory_1="+inventory_1,
            success:function(html){
               $("#inventory_2").html(html);
            }
        });
    }
		function get_inv3(){
        var inventory_2=$("#inventory_2").val();
		 $.ajax({
            type:"GET",
            url:siteurl+'inventory_5/get_inven3',
            data:"inventory_2="+inventory_2,
            success:function(html){
               $("#inventory_3").html(html);
            }
        });
    } 
		function get_inv4(){
        var inventory_3=$("#inventory_3").val();
		 $.ajax({
            type:"GET",
            url:siteurl+'inventory_5/get_inven4',
            data:"inventory_3="+inventory_3,
            success:function(html){
               $("#inventory_4").html(html);
            }
        });
    } 	
		
	function DelItem(id){
		$('#list_payment #tr_'+id).remove();
		
	}
</script>
