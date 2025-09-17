<?php
    $ENABLE_ADD     = has_permission('Costcenter.Add');
    $ENABLE_MANAGE  = has_permission('Costcenter.Manage');
    $ENABLE_VIEW    = has_permission('Costcenter.View');
    $ENABLE_DELETE  = has_permission('Costcenter.Delete');

?>
<style type="text/css">
thead input {
	width: 100%;
}
.select2-container {
    box-sizing: border-box;
    display: inline-block;
    margin: 0;
    position: relative;
    vertical-align: middle;
    width: 100% !important;
}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<div class="box box-primary">
	<!-- /.box-header -->
	<div class="box-body">
    <br>
		<form id="data_form" autocomplete="off">
		<input type="hidden" name="id" id="id" value='<?= $data[0]->id; ?>'>
			<div class="form-group row">
				<div class="col-md-3">
				  <label for="">Costcenter Name</label>
				</div>
				 <div class="col-md-9">
				  <input type="text" class="form-control" id="nama_costcenter" required name="nama_costcenter"  placeholder="Costcenter Name" value="<?= strtolower($data[0]->nama_costcenter) ?>">
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-3">
				  <label for="">Qty MP Shift 1</label>
				</div>
				 <div class="col-md-9">
				  <input type="text" class="form-control qty" id="mp_1" name="mp_1"  placeholder="Costcenter Name" value="<?= strtolower($data[0]->mp_1) ?>" data-decimal='.' data-thousand='' data-precision='0' data-allow-zero='false'>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-3">
				  <label for="">Qty MP Shift 2</label>
				</div>
				 <div class="col-md-9">
				  <input type="text" class="form-control qty" id="mp_2" name="mp_2"  placeholder="Costcenter Name" value="<?= strtolower($data[0]->mp_2) ?>" data-decimal='.' data-thousand='' data-precision='0' data-allow-zero='false'>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-3">
				  <label for="">Qty MP Shift 3</label>
				</div>
				 <div class="col-md-9">
				  <input type="text" class="form-control qty" id="mp_3" name="mp_3"  placeholder="Costcenter Name" value="<?= strtolower($data[0]->mp_3) ?>" data-decimal='.' data-thousand='' data-precision='0' data-allow-zero='false'>
				</div>
			</div>

			<div class="form-group row">
				<div class="col-md-3"></div>
				 <div class="col-md-9">
				  <button type="submit" class="btn btn-primary" name="save" id="save"><i class="fa fa-save"></i> Save</button>
				</div>
			</div>
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
		    $('.chosen-select').select2();
			$('.qty').maskMoney();
  	});


	// ADD CUSTOMER

	$(document).on('submit', '#data_form', function(e){
		// alert('Failed'); return false;
		e.preventDefault()
		var data = $('#data_form').serialize();
		var id = $('#id_inventory').val();
		// alert(id);
		swal({
		  title: "Anda Yakin?",
		  text: "Data Inventory akan di simpan.",
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
			  url:siteurl+'costcenter/edit_save',
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
