<?php
    $ENABLE_ADD     = has_permission('Shift.Add');
    $ENABLE_MANAGE  = has_permission('Shift.Manage');
    $ENABLE_VIEW    = has_permission('Shift.View');
    $ENABLE_DELETE  = has_permission('Shift.Delete');
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
				<div class="form-group row">
					<div class="col-md-2">
					 <label for="type_shift" class="col-sm-3 control-label">Shift</label>
					</div>
					<div class="col-md-6">
					  <select id="type_shift" name="type_shift" class="form-control select" required>
						<option value="">-- Shift --</option>
						<?php foreach ($results['type_shift'] as $type_shift){ 
						?>
						<option value="<?= $type_shift->id_type_shift?>"><?= ucfirst(strtolower($type_shift->name_type_shift))?></option>
						<?php } ?>
					  </select>
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-2">
					 <label for="id_day" class="col-sm-3 control-label">Day</label>
					</div>
					<div class="col-md-6">
					  <select id="id_day" name="id_day" class="form-control select" required>
						<option value="">--Hari --</option>
						<?php foreach ($results['hari'] as $hari){ 
						?>
						<option value="<?= $hari->id_hari?>"><?= ucfirst(strtolower($hari->day_en))?></option>
						<?php } ?>
					  </select>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<div class="form-group row">
							<div class="col-md-2">
							  <label for="">Start Work</label>
							</div>
							 <div class="col-md-2">
							  <input type="time" class="form-control" id="start_work" name="start_work">
							</div>
							<div class="col-md-2">
							  <label for="">Finish Work</label>
							</div>
							 <div class="col-md-2">
							  <input type="time" class="form-control" id="done_work" name="done_work">
							</div>
						</div>
						<div class="form-group row">
							<div class="col-md-2">
							  <label for="">Start Break 1</label>
							</div>
							 <div class="col-md-2">
							  <input type="time" class="form-control" id="start_break1" name="start_break1">
							</div>
							<div class="col-md-2">
							  <label for="">Finish Break 1</label>
							</div>
							 <div class="col-md-2">
							  <input type="time" class="form-control" id="done_break1" name="done_break1">
							</div>
						</div>
						<div class="form-group row">
							<div class="col-md-2">
							  <label for="">Start Break 2</label>
							</div>
							 <div class="col-md-2">
							  <input type="time" class="form-control" id="start_break2" name="start_break2">
							</div>
							<div class="col-md-2">
							  <label for="">Finish Break 2</label>
							</div>
							 <div class="col-md-2">
							  <input type="time" class="form-control" id="done_break2" name="done_break2">
							</div>
						</div>
						<div class="form-group row">
							<div class="col-md-2">
							  <label for="">Start Break 3</label>
							</div>
							 <div class="col-md-2">
							  <input type="time" class="form-control" id="start_break3" name="start_break3">
							</div>
							<div class="col-md-2">
							  <label for="">Finish Break 3</label>
							</div>
							 <div class="col-md-2">
							  <input type="time" class="form-control" id="done_break3" name="done_break3">
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
	$("#id_divisi").change(function(){

            // variabel dari nilai combo box kendaraan
            var id_divisi = $("#id_divisi").val();

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
			  url:siteurl+'shift/saveNewshift',
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
