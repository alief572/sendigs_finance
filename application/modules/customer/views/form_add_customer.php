<?php
    $ENABLE_ADD     = has_permission('Customer.Add');
    $ENABLE_MANAGE  = has_permission('Customer.Manage');
    $ENABLE_VIEW    = has_permission('Customer.View');
    $ENABLE_DELETE  = has_permission('Customer.Delete');
?>
<style type="text/css">
thead input {
	width: 100%;
}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">

<div class="box box-primary">
	<div class="box-header">
		<a class="btn btn-warning pull-right" href="<?= base_url('customer') ?>" title="Add"> <i class="fa fa-reply">&nbsp;</i>Back</a>
			
	</div>
	<!-- /.box-header -->
	<div class="box-body">
	<div id="data_custommer" >
		<form id="data_form">
		<div class="row">
		<div id="input1">
			<div class="col-md-9"><legend>Data Customer</legend></div>
			<div class="col-md-6">
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">Nama Custommer</label>
					</div>
					 <div class="col-md-6">
					  <input type="text" class="form-control" id="" required name="nm_customer" placeholder="Nama Custommer" value="">
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">No. Telepon 1</label>
					</div>
					<div class="col-md-6">
					  <input type="tel" class="form-control" name="tlp1" required placeholder="Nomor Telepon 1" value="">
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">No. Telepon 2</label>
					</div>
					<div class="col-md-6">
					  <input type="tel" class="form-control" name="tlp2" required placeholder="Nomor Telepon 2" value="">
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">No. Telepon 3</label>
					</div>
					<div class="col-md-6">
					  <input type="tel" class="form-control" name="tlp3" required placeholder="Nomor Telepon 3" value="">
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">Fax.</label>
					</div>
					<div class="col-md-6">
					  <input type="text" class="form-control" name="fax"  placeholder="Nomor Fax" value="">
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">E-mail</label>
					</div>
					<div class="col-md-6">
					  <input type="email" class="form-control" name="email"  placeholder="Email" value="">
					</div>
				</div>
				<div class="form-group row">
					  <label for="Address" class="col-sm-3 control-label">Alamat Perusahaan</label>
					<div class="col-md-6">
					  <textarea type="text" class="form-control" id="Address"  name="address" placeholder="Alamat"></textarea>
					</div>
				</div>
				<div class="form-group row">
					  <label for="provinsi" class="col-sm-3 control-label">Provinsi</label>
					<div class="col-md-6">
					  <select id="provinsi" name="provinsi" class="form-control select2" required>
						<option value="">-- Provinsi --</option>
						<?php foreach ($results['prov'] as $prov){ 
						?>
						<option value="<?= $prov->id_prov?>"><?= ucfirst(strtolower($prov->nama))?></option>
						<?php } ?>
					  </select>
					</div>
				</div>
				<div class="form-group row">
					  <label for="provinsi" class="col-sm-3 control-label">Kota</label>
					<div class="col-md-6">
					  <select id="city"  name="city"  class="form-control select2" required>
						<option value="">-- City --</option>
						<?php foreach ($results['kota'] as $kt){
						?>
						<option value="<?= $kt->id_kota?>"><?= ucfirst($kt->nama_kota)?></option>
						<?php } ?>
					  </select>
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">Kode Pos</label>
					</div>
					<div class="col-md-6">
					  <input type="tel" class="form-control" name="zipcode" required placeholder="Zip Code" value="">
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">Note</label>
					</div>
					<div class="col-md-6">
					  <textarea type="text" class="form-control" id="" name="note"  placeholder="Note"></textarea>
					</div>
				</div>
			</div>
				
			<div class="col-md-6">
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">Bidang Usaha</label>
					</div>
					<div class="col-md-6">
					  <input type="tel" class="form-control" name="business" required placeholder="Bidang Usaha" value="">
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">Produk</label>
					</div>
					<div class="col-md-6">
					  <input type="text" class="form-control" name="product"  placeholder="Produk" value="">
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">Website</label>
					</div>
					<div class="col-md-6">
					  <input type="text" class="form-control" id="" name="website"  placeholder="Website" value="">
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">NPWP</label>
					</div>
					<div class="col-md-6"> 
					  <input type="text" class="form-control" id="npwp" name="npwp"  placeholder="NPWP" value="">
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">Alamat NPWP</label>
					</div>
					<div class="col-md-6">
						<textarea type="text" class="form-control"  id="" name="npwpaddress" placeholder="NPWP Address"></textarea>
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">Status</label>
					</div>
					<div class="col-md-6">
					<?php if ($result['supplier']['activation'] == 'aktif'){?>
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
		</div>
				<div class="row">
		<div id="input1">
			<div class="col-md-9"><legend>Marketing</legend></div>
			<div class="col-md-6">
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">Nama Marketing</label>
					</div>
					<div class="col-md-2">
					<select class="form-control" id="gelar_marketing" name="gelar_marketing">
						<option value="Mr">Mr</option>
						<option value="Mrs">Mrs</option>
						<option value="Ms">Ms</option>
					  </select>
					 </div>
					 <div class="col-md-4">
					  <input type="text" class="form-control" id="" required name="nm_marketing" placeholder="Nama Marketing" value="">
					</div>
				</div>

				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">E-mail</label>
					</div>
					<div class="col-md-6">
					  <input type="email" class="form-control" name="emailmkt"  placeholder="email@mail.com" value="">
					</div>
				</div>

				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">Note</label>
					</div>
					<div class="col-md-6">
					  <textarea type="text" class="form-control" id="" name="note"  placeholder="Note"></textarea>
					</div>
				</div>
			</div>
				
			<div class="col-md-6">
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">No. Id</label>
					</div>
					<div class="col-md-6">
					  <input type="tel" class="form-control" name="no_id" required placeholder="Nomor Id" value="">
					</div>
				</div>
			</div>
			<div class="form-group row">
					<div class="col-md-3">
					  <label for="">No. Telepon</label>
					</div>
					<div class="col-md-6">
					  <input type="tel" class="form-control" name="tlpmkt" required placeholder="No Telp. Marketing" value="">
					</div>
			</div>
		</div>
		</div>
	<hr>
	<button type="submit" class="btn btn-primary" name="save" id="save"><i class="fa fa-save"></i>Tambah PIC</button>
	<button type="reset" class="btn btn-danger"><i class="fa fa-close"></i> Cancel</button>
	</form>
	</div>
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
		// alert(data);
// exit();
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
			  url:siteurl+'customer/saveCustomer',
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
