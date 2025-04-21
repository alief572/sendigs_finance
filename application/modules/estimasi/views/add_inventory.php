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
		<form id="data_form">
		<div class="row">
		<div id="input1">
			<div class="col-md-12">			    
                <div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="inventory_1">Inventory Type</label>
					</div>
					 <div class="col-md-8">
					  <select id="inventory_1" name="inventory_1"  class="form-control input-sm" required>
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
					  <select id="inventory_2" name="inventory_2" class="form-control input-sm" required>
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
					  <select id="inventory_3" name="inventory_3" class="form-control input-sm" required>
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
					  <label for="">Spesifikasi 1</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec1"  name="spec1" placeholder="Spesifikasi 1" value="" required>
					</div>
				</div>
				</div>				
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Spesifikasi 2</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec2"  name="spec2" placeholder="Spesifikasi 2" value="" required>
					</div>
				</div>
				</div>				
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Spesifikasi 3</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec3"  name="spec3" placeholder="Spesifikasi 3" value="" required>
					</div>
				</div>
				</div>
				
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Spesifikasi 4</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec4"  name="spec4" placeholder="Spesifikasi 4" value="" required>
					</div>
				</div>
				</div>
			    <div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Spesifikasi 5</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec5"  name="spec5" placeholder="Spesifikasi 5" value="" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Spesifikasi 6</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec6"  name="spec6" placeholder="Spesifikasi 6" value="" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Spesifikasi 7</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec7"  name="spec7" placeholder="Spesifikasi 7" value="" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Spesifikasi 8</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec8"  name="spec8" placeholder="Spesifikasi 8" value="" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Spesifikasi 9</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec9"  name="spec9" placeholder="Spesifikasi 9" value="" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Spesifikasi 10</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec10"  name="spec10" placeholder="Spesifikasi 10" value="" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Spesifikasi 11</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec11"  name="spec11" placeholder="Spesifikasi 11" value="" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Spesifikasi 12</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec12"  name="spec12" placeholder="Spesifikasi 12" value="" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Spesifikasi 13</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec13"  name="spec13" placeholder="Spesifikasi 13" value="" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Spesifikasi 14</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec14"  name="spec14" placeholder="Spesifikasi 14" value="" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Spesifikasi 15</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec15"  name="spec15" placeholder="Spesifikasi 15" value="" required>
					</div>
				</div>
				</div>  
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
	</div>
	<!-- /.box-body -->
</div>


<!-- Modal Bidus-->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/sweetalert/dist/sweetalert.js')?>"></script>
<!-- End Modal Bidus-->

<script type="text/javascript">

  $(document).ready(function() {
	$('.select2').select2();
	$("#inventory_1").change(function(){

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
	
	$("#inventory_2").change(function(){

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
	
	$("#inventory_3").change(function(){

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
    $(document).on('submit', '#data_form', function(e){
		e.preventDefault()
		var data = $('#data_form').serialize();
		// alert(data);

		swal({
		  title: "Anda Yakin?",
		  text: "Data Material akan di simpan.",
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
			  url:siteurl+'inventory_5/saveNewinventory',
			  dataType : "json",
			  data:data,
			  success:function(result){
				  if(result.status == '1'){
					 swal({
						  title: "Sukses",
						  text : "Data Inventory berhasil disimpan.",
						  type : "success"
						},
						function (){
							window.location.reload(true);
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
   
  });

  

</script>
