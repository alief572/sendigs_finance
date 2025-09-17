<?php
    $ENABLE_ADD     = has_permission('Inventory_2.Add');
    $ENABLE_MANAGE  = has_permission('Inventory_2.Manage');
    $ENABLE_VIEW    = has_permission('Inventory_2.View');
    $ENABLE_DELETE  = has_permission('Inventory_2.Delete');

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
						<?php foreach ($results['lvl1'] as $lvl1){
						$select = $inven->id_type == $lvl1->id_type ? 'selected' : '';
						?>
						<option value="<?= $lvl1->id_type?>" <?= $select ?>><?= $lvl1->nama?></option>
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
				</div>	
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
					  <label for="">Spesifikasi 1</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec1"  name="spec1" placeholder="Spesifikasi 1" value="<?= $inven->spec1 ?>" required>
					</div>
				</div>
				</div>				
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Spesifikasi 2</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec2"  name="spec2" placeholder="Spesifikasi 2" value="<?= $inven->spec2 ?>" required>
					</div>
				</div>
				</div>				
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Spesifikasi 3</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec3"  name="spec3" placeholder="Spesifikasi 3" value="<?= $inven->spec3 ?>" required>
					</div>
				</div>
				</div>
				
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Spesifikasi 4</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec4"  name="spec4" placeholder="Spesifikasi 4" value="<?= $inven->spec4 ?>" required>
					</div>
				</div>
				</div>
			    <div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Spesifikasi 5</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec5"  name="spec5" placeholder="Spesifikasi 5" value="<?= $inven->spec5 ?>" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Spesifikasi 6</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec6"  name="spec6" placeholder="Spesifikasi 6" value="<?= $inven->spec6 ?>" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Spesifikasi 7</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec7"  name="spec7" placeholder="Spesifikasi 7" value="<?= $inven->spec7 ?>" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Spesifikasi 8</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec8"  name="spec8" placeholder="Spesifikasi 8" value="<?= $inven->spec8 ?>" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Spesifikasi 9</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec9"  name="spec9" placeholder="Spesifikasi 9" value="<?= $inven->spec9 ?>" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Spesifikasi 10</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec10"  name="spec10" placeholder="Spesifikasi 10" value="<?= $inven->spec10 ?>" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Spesifikasi 11</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec11"  name="spec11" placeholder="Spesifikasi 11" value="<?= $inven->spec11 ?>" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Spesifikasi 12</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec12"  name="spec12" placeholder="Spesifikasi 12" value="<?= $inven->spec12 ?>" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Spesifikasi 13</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec13"  name="spec13" placeholder="Spesifikasi 13" value="<?= $inven->spec13 ?>" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Spesifikasi 14</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec14"  name="spec14" placeholder="Spesifikasi 14" value="<?= $inven->spec14 ?>" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Spesifikasi 15</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec15"  name="spec15" placeholder="Spesifikasi 15" value="<?= $inven->spec15 ?>" required>
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
	<!-- /.box-body -->
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>

<!-- page script -->
<script type="text/javascript">

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
			  url:siteurl+'inventory_3/saveEditInventory',
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
</script>
