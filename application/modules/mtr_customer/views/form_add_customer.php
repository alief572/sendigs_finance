<?php
    $ENABLE_ADD     = has_permission('Master_customer.Add');
    $ENABLE_MANAGE  = has_permission('Master_customer.Manage');
    $ENABLE_VIEW    = has_permission('Master_customer.View');
    $ENABLE_DELETE  = has_permission('Master_customer.Delete');
?>
<style type="text/css">
thead input {
	width: 100%;
}
</style>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<link rel="stylesheet" href="<?= base_url('assets/plugins/sweetalert/dist/sweetalert.css')?>">

          <div class="box box-primary">
              <div class="box-header">
                <div style="display:inline-block;width:100%;">
                  <!--<a class="btn btn-sm btn-success" href="<?= base_url('add_customer') ?>" title="Add" style="float:left;margin-right:8px"><i class="fa fa-plus">&nbsp;</i>New</a>
                 <a class="btn btn-sm btn-danger pdf" id="pdf-report" style="float:right;margin:8px 8px 0 0"><i class="fa fa-file"></i> PDF</a>
                   <a class="btn btn-sm btn-success excel" id="excel-report" style="float:right;margin:8px 8px 0 0"><i class="fa fa-table"></i> Excel</a> -->
				   <a href="<?= base_url('master_customer/')?>" class="btn btn-warning pull-right"><i class="fa fa-reply"></i> Back</a>
                </div>

              </div>>
			<div name="customer_box">
			<div class="box-header"><center><h3>Data Customer</h3><center></div>
            <div class="box-body">
			
			<form id="data_form">
				<div class="row">
					<div class="col-md-6">
						<div class="form-group row">
							<div class="col-md-3">
							  <label for="">Nama Customer</label>
							</div>
							 <div class="col-md-9">
							  <input type="text" class="form-control" id="" required name="nm_customer" placeholder="Nama Customer">
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group row">
							<div class="col-md-3">
							  <label for="">Bidang Usaha</label>
							</div>
							 <div class="col-md-9">
							  <input type="text" class="form-control" id="" required name="business" placeholder="Bidang Usaha">
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group row">
							<div class="col-md-3">
							  <label for="">Nomor Telepon 1</label>
							</div>
							 <div class="col-md-9">
							  <input type="text" class="form-control" id="" name="tlp" placeholder="Nomor Telepon">
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group row">
							<div class="col-md-3">
							  <label for="">Alamat Perusahaan</label>
							</div>
							<div class="col-md-9">
							  <textarea type="text" class="form-control" id="sddress"  name="address" placeholder="Address"></textarea>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group row">
							<div class="col-md-3">
							  <label for="">Nomor Telepon 2</label>
							</div>
							 <div class="col-md-9">
							  <input type="text" class="form-control" id="" name="tlp2" placeholder="Nomor Telepon">
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group row">
							  <label for="provinsi" class="col-sm-3 control-label">Negara</label>
							<div class="col-md-6">
							  <select id="negara" name="negara" class="form-control select2" required>
								<option value="">-- Negara --</option>
								<?php foreach ($results['negara'] as $negara){ 
								?>
								<option value="<?= $negara->id_negara?>"><?= ucfirst(strtolower($negara->nm_negara))?></option>
								<?php } ?>
							  </select>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group row">
							<div class="col-md-3">
							  <label for="">Nomor Telepon 3</label>
							</div>
							 <div class="col-md-9">
							  <input type="text" class="form-control" id="" name="tlp3" placeholder="Nomor Telepon">
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group row">
							  <label for="provinsi" class="col-sm-3 control-label">Provinsi</label>
							<div class="col-md-6">
							  <select id="provinsi" name="provinsi" class="form-control select2" required>
								<option value="">-- Provinsi --</option>
								<?php foreach ($results['provinsi'] as $prov){ 
								?>
								<option value="<?= $prov->id_prov?>"><?= ucfirst(strtolower($prov->nama))?></option>
								<?php } ?>
							  </select>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group row">
							<div class="col-md-3">
							  <label for="">Email Perusahaan</label>
							</div>
							 <div class="col-md-9">
							  <input type="email" class="form-control" id="" name="email" placeholder="email@mail.com">
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group row">
							  <label for="provinsi" class="col-sm-3 control-label">Kota</label>
							<div class="col-md-6">
							  	<select name='kota' id='kota' class='form-control'>
									<option value=''>-- Kota --</option>
								</select>	
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group row">
							<div class="col-md-3">
							  <label for="">Fax</label>
							</div>
							 <div class="col-md-9">
							  <input type="text" class="form-control" id="" required name="fax" placeholder="Nomor Fax">
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group row">
							<div class="col-md-3">
							  <label for="">Kode Pos</label>
							</div>
							 <div class="col-md-9">
							  <input type="text" class="form-control" id="" required name="zipcode" placeholder="Kode Pos">
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group row">
							<div class="col-md-3">
							  <label for="">NPWP</label>
							</div>
							 <div class="col-md-9">
							  <input type="text" class="form-control" id="" required name="npwp" placeholder="Nomor NPWP">
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group row">
							<div class="col-md-3">
							  <label for="">Website</label>
							</div>
							 <div class="col-md-9">
							  <input type="text" class="form-control" id="" required name="website" placeholder="www.domain.ext">
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group row">
							<div class="col-md-3">
							  <label for="">Alamat NPWP</label>
							</div>
							 <div class="col-md-9">
							  <input type="text" class="form-control" id="" required name="npwpaddress" placeholder="Alamat NPWP">
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group row">
							<div class="col-md-3">
							  <label for="">Status</label>
							</div>
							<div class="col-md-4">
							  <label>
								<input type="radio" class="radio-control" id="" name="status" value="aktif" required> Aktif
							  </label>
								&nbsp &nbsp &nbsp
							  <label>
								<input type="radio" class="radio-control" id="" name="status" value="nonaktif" required> Non Aktif
							  </label>
								
							</div>
						</div>
					</div>
				</div>
				</br>
			<div id="marketing">
				<div class="box-header"><center><h3>Data Marketing</h3><center></div>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group row">
							<div class="col-md-3">
							  <label for="">Nama Marketing</label>
							</div>
							<div class="col-md-2">
							<select id="gelar_mkt" class="form-control" name="gelar_mkt">
								<option value="Mr">Mr</option>
								<option value="Mrs">Mrs</option>
								<option value="Ms">Ms</option>
							</select>
							</div>
							 <div class="col-md-6">
							
							  <input type="text" class="form-control" id="" name="marketing" placeholder="Nama Marketing">
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group row">
							<div class="col-md-3">
							  <label for="">Id Marketing</label>
							</div>
							<div class="col-md-9">
							  <input type="text" class="form-control" id="idnumber" name="idnumber" placeholder="Nomor Id">
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group row">
							<div class="col-md-3">
							  <label for="">Alamat KTP</label>
							</div>
							 <div class="col-md-9">
							  <input type="text" class="form-control" id="" name="idaddress" placeholder="Alamat KTP">
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group row">
							<div class="col-md-3">
							  <label for="">Email Marketing</label>
							</div>
							<div class="col-md-9">
							  <input type="email" class="form-control" id="emailmkt" name="idaddress" placeholder="email@domailn.co">
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group row">
							<div class="col-md-3">
							  <label for="">Telepon Marketing</label>
							</div>
							 <div class="col-md-9">
							  <input type="text" class="form-control" id="" name="telpmkt" placeholder="Nomor Telepon Marketing">
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="pic_box">
				<div class="box-header"><center><h3>PIC</h3><center></div>
								<div class="row">
					<div class="col-md-6">
						<div class="form-group row">
							<div class="col-md-3">
							  <label for="">Nama PIC</label>
							</div>
							<div class="col-md-2">
							<select id="gelar_mkt" class="form-control" name="gelar_pic">
								<option value="Mr">Mr</option>
								<option value="Mrs">Mrs</option>
								<option value="Ms">Ms</option>
							</select>
							</div>
							 <div class="col-md-6">		
							  <input type="text" class="form-control" id="" name="pic" placeholder="Nama PIC">
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group row">
							<div class="col-md-3">
							  <label for="">Email PIC</label>
							</div>
							<div class="col-md-9">
							  <textarea type="text" class="form-control" id="sddress"  name="emailpic" placeholder="EMail Marketing"></textarea>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group row">
							<div class="col-md-3">
							  <label for="">Telepon PIC</label>
							</div>
							 <div class="col-md-9">
							  <input type="text" class="form-control" id="" name="telppic" placeholder="Nomor Telepon Marketing">
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group row">
							<div class="col-md-3">
							  <label for="">Jabatan PIC</label>
							</div>
							 <div class="col-md-9">
							  <input type="text" class="form-control" id="" name="jabatan_pic" placeholder="Nomor Telepon Marketing">
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="list_pid">
			<div class="col-md-12">
			<table id="example1" class="table table-bordered table-striped">
				<thead>
				<tr>
					<th width="5">#</th>
					<th>ID PIC</th>
					<th>Nama PIC</th>
					<th>Jabatan</th>
					<th>Email</th>
					<th>Aksi</th>
				</tr>
				</thead>
			</div>
			</div>
			<div id= "final_button">
				<div class="row">
			<div class="col-md-12">
			<center>
			<input type="button" class="btn btn-primary" name="showpic"  value="Tambah PIC" id="showpic">
			<button type="submit" class="btn btn-primary" name="save" id="save"><i class="fa fa-save"></i> Save</button>
			<button type="reset" class="btn btn-danger"><i class="fa fa-close"></i> Cancel</button>
			</center>
			</div>
				</div>
			</div>
			</form>
        </div>

<!-- Modal Bidus-->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/sweetalert/dist/sweetalert.js')?>"></script>
<!-- End Modal Bidus-->

<script type="text/javascript">

  $(document).ready(function() {
	$('.select2').select2();
	$("#customer_box").show();
	$("#marketing").show();
	$("#marketing_button").show();
	$("#pic_box").hide();
	$("#eksternal_marketing").hide();
	$("#final_button").hide();
    //var table = $('#example1').DataTable();

		$(document).on('change', '#provinsi', function(e){
			e.preventDefault();
			$.ajax({
				url: base_url +'index.php/'+ active_controller+'/getkota',
				cache: false,
				type: "POST",
				data: "id_prov="+this.value,
				dataType: "json",
				success: function(data){
					$("#kota").html(data.option).trigger("chosen:updated");
				}
			});
		});
		$(document).on('click', '#showinternal', function(e){
			$("#customer_box").show();
			$("#marketing_button").fadeOut(500);
			$("#pic_box").hide();
			$("#internal_marketing").fadeIn(500);
			$("#eksternal_marketing").hide();
			$("#final_button").hide();
		});	
		$(document).on('click', '#showeksternal', function(e){
			$("#customer_box").show();
			$("#marketing_button").fadeOut(500);
			$("#pic_box").hide();
			$("#internal_marketing").hide();
			$("#eksternal_marketing").fadeIn(500);
			$("#final_button").hide();
		});	

    $(document).on('submit', '#data_form', function(e){
		e.preventDefault()
		var data = $('#data_form').serialize();
		// alert(data);

		swal({
		  title: "Anda Yakin?",
		  text: "Data Supplier akan di simpan.",
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
			  url:siteurl+'master_customer/saveCustomer',
			  dataType : "json",
			  data:data,
			  success:function(result){
				  if(result.status == '1'){
					 swal({
						  title: "Sukses",
						  text : "Data Supllier berhasil disimpan.",
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
