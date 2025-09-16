<?php
    $ENABLE_ADD     = has_permission('Proses_cost.Add');
    $ENABLE_MANAGE  = has_permission('Proses_cost.Manage');
    $ENABLE_VIEW    = has_permission('Proses_cost.View');
    $ENABLE_DELETE  = has_permission('Proses_cost.Delete');
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
                  <!--<a class="btn btn-sm btn-success" href="<?= base_url('add_supplier') ?>" title="Add" style="float:left;margin-right:8px"><i class="fa fa-plus">&nbsp;</i>New</a>
                 <a class="btn btn-sm btn-danger pdf" id="pdf-report" style="float:right;margin:8px 8px 0 0"><i class="fa fa-file"></i> PDF</a>
                   <a class="btn btn-sm btn-success excel" id="excel-report" style="float:right;margin:8px 8px 0 0"><i class="fa fa-table"></i> Excel</a> -->
				   <a href="<?= base_url('proses_cost/')?>" class="btn btn-warning pull-right"><i class="fa fa-reply"></i> Back</a>
                </div>

              </div>
			  


            <div class="box-body">
			
			<form id="data_form">
				<div class="row">
					<div class="col-md-6">
						<div class="form-group row">
							<div class="col-md-3">
							  <label for="">Item Cost</label>
							</div>
							 <div class="col-md-9">
							  <input type="text" class="form-control" id="" required name="item_cost" placeholder="Item Cost">
							</div>
						</div>
						<div class="form-group row">
							<div class="col-md-3">
							  <label for="">Standard Calculation</label>
							</div>
							<div class="col-md-9">
							  <input type="tel" class="form-control" name="tlp" required placeholder="Telephone Number">
							</div>
						</div>
						<div class="form-group row">
							<div class="col-md-3">
							  <label for="">Standard Sate</label>
							</div>
							<div class="col-md-9">
							  <input type="text" class="form-control" name="fax"  placeholder="Fax Number">
							</div>
						</div>
						<div class="form-group row">
							<div class="col-md-3">
							  <label for="">E-mail</label>
							</div>
							<div class="col-md-9">
							  <input type="email" class="form-control" name="email"  placeholder="E-mail Address">
							</div>
						</div>
						<div class="form-group row">
							  <label for="Address" class="col-sm-3 control-label">Address</label>
							<div class="col-md-9">
							  <textarea type="text" class="form-control" id="Address"  name="address" placeholder="Address"></textarea>
							</div>
						</div>
						<div class="form-group row">
							  <label for="country" class="col-sm-3 control-label">Country Code</label>
							<div class="col-md-5">
							  <select id="country" name="country" class="form-control select2" required >
								<option value="">-- Country --</option>
								<?php foreach ($result['dCountry'] as $ctr){ ?>
								<option value="<?= $ctr->id_country?>"><?= ucfirst(strtolower($ctr->name_country))?></option>
								<?php } ?>
							  </select>
							</div>
						</div>
						<div class="form-group row">
							  <label for="currency" class="col-sm-3 control-label">Currency</label>
							<div class="col-md-5">
							  <select id="currency"  name="currency"  class="form-control select2" required>
								<option value="">-- Currency --</option>
								<?php foreach ($result['dCurrency'] as $crr){ ?>
								<option value="<?= $crr->kode?>"><?= strtoupper($crr->kode)." | ".ucfirst(strtolower($crr->mata_uang))?></option>
								<?php } ?>
							  </select>
							</div>
						</div>
					</div>
						
					<div class="col-md-6">
						<div class="form-group row">
							<div class="col-md-3">
							  <label for="">Product</label>
							</div>
							<div class="col-md-9">
							  <input type="text" class="form-control" name="product"  placeholder="Product">
							</div>
						</div>
						<div class="form-group row">
							<div class="col-md-3">
							  <label for="">Contact Person</label>
							</div>
							<div class="col-md-9">
							  <input type="text" class="form-control" id="" name="persion"  placeholder="Contact Person">
							</div>
						</div>
						<div class="form-group row">
							<div class="col-md-3">
							  <label for="">ID Webchat</label>
							</div>
							<div class="col-md-9">
							  <input type="text" class="form-control" id="" name="webchat"  placeholder="ID Webchat">
							</div>
						</div>
						<div class="form-group row">
							<div class="col-md-3">
							  <label for="">NPWP</label>
							</div>
							<div class="col-md-9"> 
							  <input type="text" class="form-control" id="npwp" name="npwp"  placeholder="NPWP">
							</div>
						</div>
						<div class="form-group row">
							<div class="col-md-3">
							  <label for="">NPWP Address</label>
							</div>
							<div class="col-md-9">
								<textarea type="text" class="form-control"  id="" name="npwpaddress" placeholder="NPWP Address"></textarea>
							</div>
						</div>
						<div class="form-group row">
							<div class="col-md-3">
							  <label for="">Note</label>
							</div>
							<div class="col-md-9">
							  <textarea type="text" class="form-control" id="" name="note"  placeholder="Note"></textarea>
							</div>
						</div>
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
			<hr>
			<button type="submit" class="btn btn-primary" name="save" id="save"><i class="fa fa-save"></i> Save</button>
			<button type="reset" class="btn btn-danger"><i class="fa fa-close"></i> Cancel</button>
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
    //var table = $('#example1').DataTable();

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
			  url:siteurl+'master_supplier/saveSupplier',
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
