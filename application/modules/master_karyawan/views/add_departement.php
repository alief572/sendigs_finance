<?php
    $ENABLE_ADD     = has_permission('Inventory_1.Add');
    $ENABLE_MANAGE  = has_permission('Inventory_1.Manage');
    $ENABLE_VIEW    = has_permission('Inventory_1.View');
    $ENABLE_DELETE  = has_permission('Inventory_1.Delete');
?>
<style type="text/css">
thead input {
	width: 100%;
}
</style>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<link rel="stylesheet" href="<?= base_url('assets/plugins/sweetalert/dist/sweetalert.css')?>">

          <div class="box box-primary">
            <div class="box-body">
			
			<form id="data_form">
				<div class="row">
					<div class="col-md-12">
			<div class="form-group row">
							<div class="col-md-2">
							  <label for="">Id Number</label>
							</div>
							 <div class="col-md-6">
							  <input type="text" class="form-control" id="nik" required name="nik" maxlength="16"  placeholder="ID Number">
							</div>
			</div>
			<div class="form-group row">
								<div class="col-md-2">
							  <label for="">Employee Name</label>
							</div>
							 <div class="col-md-6">
							  <input type="text" class="form-control" id="nama_karyawan" required name="nama_karyawan" placeholder="Employee Name">
							</div>
			</div>
			<div class="form-group row">
							<div class="col-md-2">
							  <label for="">Birth Place And Date</label>
							</div>
							 <div class="col-md-6">
							  <input type="text" class="form-control" id="tempat_lahir_karyawan" required name="tempat_lahir_karyawan" placeholder="Birth Place">
							  <input type="date" class="form-control" id="tanggal_lahir_karyawan" required name="tanggal_lahir_karyawan" placeholder="Employee Name">
							</div>
			</div>
			<div class="form-group row">
							<div class="col-md-2">
							  <label for="">Division</label>
							</div>
							 <div class="col-md-6">
						<select id="divisi" name="divisi"  class="form-control input-sm" required>
						<option value="">-- Divisi --</option>
						<?php foreach ($results['divisi'] as $divisi){ 
						?>
						<option value="<?= $divisi->id_divisi?>"><?= ucfirst(strtolower($divisi->nm_divisi))?></option>
						<?php } ?>
					  </select>
							</div>
			</div>
			<div class="form-group row">
							<div class="col-md-2">
							  <label for="">Gender</label>
							</div>
							 <div class="col-md-6">
						<select id="gender" name="gender"  class="form-control input-sm" required>
						<option value="">-- Pilih Jenis Kelamin --</option>
						<option value="L">Laki-Laki</option>
						<option value="P">Perempuan</option>
					  </select>
							</div>
			</div>
			<div class="form-group row">
							<div class="col-md-2">
							  <label for="">Agama</label>
							</div>
							 <div class="col-md-6">
						<select id="agama" name="agama"  class="form-control input-sm" required>
						<option value="">-- Pilih Agama --</option>
						<?php foreach ($results['religion'] as $religion){ 
						?>
						<option value="<?= $religion->id?>"><?= ucfirst(strtolower($religion->name_religion))?></option>
						<?php } ?>
					  </select>
							</div>
			</div>
			<div class="form-group row">
							<div class="col-md-2">
							  <label for="">Education</label>
							</div>
							 <div class="col-md-6">
						<select id="levelpendidikan" name="levelpendidikan"  class="form-control input-sm" required>
						<option value="">-- Pilih Type --</option>
						<option value="SD">SD</option>
						<option value="SMP">SMP</option>
						<option value="SMA">SMA</option>
						<option value="DIPLOMA">DIPLOMA</option>
						<option value="SARJANA">SARJANA</option>
						<option value="MASTER">MASTER</option>
						<option value="DOKTORAL">DOKTORAL</option>
						<option value="PROFESOR">PROVESOR</option>
						<option value="LAIN-LAIN">LAIN-LAIN</option>
					  </select>
							</div>
			</div>
			<div class="form-group row">
								<div class="col-md-2">
							  <label for="">Address</label>
							</div>
							 <div class="col-md-6">
							  <textarea type="text" class="form-control" id="alamataktif"  name="alamataktif" placeholder="Alamat"></textarea>
							</div>
			</div>
						<div class="form-group row">
								<div class="col-md-2">
							  <label for="">No. Hp</label>
							</div>
							 <div class="col-md-6">
							   <input type="text" class="form-control" id="nohp" required name="nohp" placeholder="No Hp">
							</div>
			</div>
						<div class="form-group row">
								<div class="col-md-2">
							  <label for="">Email</label>
							</div>
							 <div class="col-md-6">
								<input type="email" class="form-control" id="email" required name="email" placeholder="email@domain">
							</div>
			</div>
						<div class="form-group row">
								<div class="col-md-2">
							  <label for="">NPWP</label>
							</div>
							 <div class="col-md-6">
								 <input type="text" class="form-control" id="npwp" required name="npwp" placeholder="No NPWP">
							</div>
			</div>
			<div class="form-group row">
								<div class="col-md-2">
							  <label for="">Join Date</label>
							</div>
							 <div class="col-md-6">
								 <input type="date" class="form-control" id="tgl_join" required name="tgl_join" placeholder="No NPWP">
							</div>
			</div>
			<div class="form-group row">
								<div class="col-md-2">
							  <label for=""></label>
							</div>
							 <div class="col-md-6">
								 <input type="date" class="form-control" id="tgl_end" required name="tgl_end" placeholder="No NPWP">
							</div>
			</div>
			<div class="form-group row">
			<div class="col-md-2">
							  <label for="">Employee Status</label>
							</div>
							 <div class="col-md-6">
						<select id="sts_karyawan" name="sts_karyawan"  class="form-control input-sm" required>
						<option value="">-- Pilih Type --</option>
						<option value="Kontrak">Kontrak</option>
						<option value="Tetap">Tetap</option>
					  </select>
							</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
							  <label for="">Rekening</label>
							</div>
							 <div class="col-md-6">
								 <input type="text" class="form-control" id="norekening" required name="norekening" placeholder="No Rekening">
							</div>
			</div>
			<div class="form-group row">
			<div class="col-md-3">
			<button type="submit" class="btn btn-primary" name="save" id="save"><i class="fa fa-save"></i> Save</button>
			</div>
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
			  url:siteurl+'master_karyawan/saveNewkaryawan',
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
