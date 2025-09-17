<?php
    $ENABLE_ADD     = has_permission('Inventory_1.Add');
    $ENABLE_MANAGE  = has_permission('Inventory_1.Manage');
    $ENABLE_VIEW    = has_permission('Inventory_1.View');
    $ENABLE_DELETE  = has_permission('Inventory_1.Delete');

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
		<input type="hidden" name="id_inventory" id="id_inventory" value='<?= $inven->id_inventory1 ?>'>
		<div class="row">
			<div class="col-md-12"><legend>Data Type</legend></div>
			<div class="col-md-6">
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">Nama Type</label>
					</div>
					 <div class="col-md-9">
					  <input type="text" class="form-control" id="nm_inventory" required name="nm_inventory" placeholder="Nama Type" value="<?= $inven->nama ?>">
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">Status</label>
					</div>
					<div class="col-md-4">
					<?php if ($inven->aktif == 'aktif'){?>
					  <label>
						<input type="radio" class="radio-control" id="" name="status" value="aktif" checked required> Aktif
					  </label>
						&nbsp &nbsp &nbsp
					  <label>
						<input type="radio" class="radio-control" id="" name="status" value="nonaktif" required> Non Aktif
					  </label>
					<?php } else { ?>
					  <label>
						<input type="radio" class="radio-control" id="" name="status" value="aktif" required> Aktif
					  </label>
						&nbsp &nbsp &nbsp
					  <label>
						<input type="radio" class="radio-control" id="" name="status" value="nonaktif" checked required> Non Aktif
					  </label>
					<?php } ?>
					</div>
				</div>
			</div>
		</div>
	<hr>
	<button type="submit" class="btn btn-primary" name="save" id="save"><i class="fa fa-save"></i> Save</button>
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
			  url:siteurl+'inventory_1/saveEditInventory',
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
