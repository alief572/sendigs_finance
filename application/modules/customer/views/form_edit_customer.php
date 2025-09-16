<?php
    $ENABLE_ADD     = has_permission('Customer.Add');
    $ENABLE_MANAGE  = has_permission('Customer.Manage');
    $ENABLE_VIEW    = has_permission('Customer.View');
    $ENABLE_DELETE  = has_permission('Customer.Delete');

foreach ($results['cust'] as $cust){
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
	<div class="box-header">
		<a class="btn btn-warning pull-right" href="<?= base_url('customer') ?>" title="Add"> <i class="fa fa-reply">&nbsp;</i>Back</a>
			
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<form id="data_form">
		<input type="hidden" name="id_customer" id="id_cust" value='<?= $cust->id_customer ?>'>
		<div class="row">
			<div class="col-md-12"><legend>Data Customer</legend></div>
			<div class="col-md-6">
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">Customer Name</label>
					</div>
					 <div class="col-md-9">
					  <input type="text" class="form-control" id="" required name="nm_customer" placeholder="Customer Name" value="<?= $cust->nm_customer ?>">
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">Telephone</label>
					</div>
					<div class="col-md-9">
					  <input type="tel" class="form-control" name="tlp" required placeholder="Telephone Number" value="<?= $cust->telepon ?>">
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">Fax.</label>
					</div>
					<div class="col-md-9">
					  <input type="text" class="form-control" name="fax"  placeholder="Fax Number" value="<?= $cust->fax ?>">
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">E-mail</label>
					</div>
					<div class="col-md-9">
					  <input type="email" class="form-control" name="email"  placeholder="email@mail.com" value="<?= $cust->email ?>">
					</div>
				</div>
				<div class="form-group row">
					  <label for="Address" class="col-sm-3 control-label">Address</label>
					<div class="col-md-9">
					  <textarea type="text" class="form-control" id="Address"  name="address" placeholder="Address"><?= $cust->alamat ?></textarea>
					</div>
				</div>
				<div class="form-group row">
					  <label for="country" class="col-sm-3 control-label">Provinsi</label>
					<div class="col-md-9">
					  <select id="country" name="provinsi" class="form-control select2" required>
						<option value="">-- Provinsi --</option>
						<?php foreach ($results['prov'] as $prov){ 
						$select = $cust->provinsi == $prov->id_prov ? 'selected' : '';
						
						?>
						<option value="<?= $prov->id_prov?>" <?= $select ?>><?= ucfirst(strtolower($prov->nama))?></option>
						<?php } ?>
					  </select>
					</div>
				</div>
				<div class="form-group row">
					  <label for="currency" class="col-sm-3 control-label">City</label>
					<div class="col-md-9">
					  <select id="currency"  name="city"  class="form-control select2" required>
						<option value="">-- City --</option>
						<?php foreach ($results['kota'] as $kt){
						$select = $cust->kota == $kt->id_kota ? 'selected' : '';
						?>
						<option value="<?= $kt->id_kota?>" <?= $select ?>><?= $kt->nama_kota?></option>
						<?php } ?>
					  </select>
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">Zip Code</label>
					</div>
					<div class="col-md-9">
					  <input type="tel" class="form-control" name="zipcode" required placeholder="Zip Code" value="<?= $cust->kode_pos ?>">
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">Note</label>
					</div>
					<div class="col-md-9">
					  <textarea type="text" class="form-control" id="" name="note"  placeholder="Note"><?= $cust->note ?></textarea>
					</div>
				</div>
			</div>
				
			<div class="col-md-6">
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">Business Fields</label>
					</div>
					<div class="col-md-9">
					  <input type="tel" class="form-control" name="business" required placeholder="Business Fields" value="<?= $cust->bidang_usaha ?>">
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">Product</label>
					</div>
					<div class="col-md-9">
					  <input type="text" class="form-control" name="product"  placeholder="Product" value="<?= $cust->produk ?>">
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">Marketing</label>
					</div>
					<div class="col-md-9">
					  <input type="text" class="form-control" id="" name="marketing"  placeholder="Marketing" value="<?= $cust->marketing ?>">
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">ID Number</label>
					</div>
					<div class="col-md-9">
					  <input type="text" class="form-control" id="" name="idnumber"  placeholder="ID Number" value="<?= $cust->no_ktp ?>">
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">Name from ID Card</label>
					</div>
					<div class="col-md-9">
					  <input type="text" class="form-control" id="" name="alamtaktp"  placeholder="Address on ID Card" value="<?= $cust->alamat_ktp ?>">
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">Website</label>
					</div>
					<div class="col-md-9">
					  <input type="text" class="form-control" id="" name="website"  placeholder="Website" value="<?= $cust->website ?>">
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">NPWP</label>
					</div>
					<div class="col-md-9"> 
					  <input type="text" class="form-control" id="npwp" name="npwp"  placeholder="NPWP" value="<?= $cust->npwp ?>">
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">NPWP Address</label>
					</div>
					<div class="col-md-9">
						<textarea type="text" class="form-control"  id="" name="npwpaddress" placeholder="NPWP Address"><?= $cust->alamat_npwp ?></textarea>
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">Status</label>
					</div>
					<div class="col-md-4">
					<?php if ($cust->aktif == 'aktif'){?>
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
			<div class="col-md-12"><legend>Data PIC</legend></div>
			<div class="col-md-6">
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">PIC Name</label>
					</div>
					<div class="col-md-9">
					  <input type="tel" class="form-control" name="pic_nm" required placeholder="PIC Name" value="<?= $cust->pic_name ?>">
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">Handphone</label>
					</div>
					<div class="col-md-9">
					  <input type="tel" class="form-control" id="" name="pic_hp"  placeholder="Handphone" value="<?= $cust->pic_hp ?>">
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">E-mail</label>
					</div>
					<div class="col-md-9">
					  <input type="email" class="form-control" id="" name="pic_email"  placeholder="E-mail" value="<?= $cust->pic_email ?>">
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">Division</label>
					</div>
					<div class="col-md-9">
					  <input type="text" class="form-control" name="pic_divisi"  placeholder="Division" value="<?= $cust->pic_divisi ?>">
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-3">
					  <label for="">Position</label>
					</div>
					<div class="col-md-9">
					  <input type="text" class="form-control" id="" name="pic_posisi"  placeholder="Position" value="<?= $cust->pic_jabatan ?>">
					</div>
				</div>
			</div>
			
		</div>
	<hr>
	<button type="submit" class="btn btn-primary" name="save" id="save"><i class="fa fa-save"></i> Save</button>
	<button type="reset" class="btn btn-danger"><i class="fa fa-close"></i> Cancel</button>
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
		var id = $('#id_cust').val();
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
			  url:siteurl+'customer/saveEditCustomer',
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
