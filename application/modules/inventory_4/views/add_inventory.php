<?php
    $ENABLE_ADD     = has_permission('Inventory_4.Add');
    $ENABLE_MANAGE  = has_permission('Inventory_4.Manage');
    $ENABLE_VIEW    = has_permission('Inventory_4.View');
    $ENABLE_DELETE  = has_permission('Inventory_4.Delete');
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
					<div class="col-md-3">
					 <label for="inventory_1" class="col-sm-3 control-label">Inventory Type</label>
					</div>
					<div class="col-md-6">
					  <select id="inventory_1" name="inventory_1" class="form-control select" onchange="get_inv2()" required>
						<option value="">-- Pilih Type --</option>
						<?php foreach ($results['inventory_1'] as $inventory_1){ 
						?>
						<option value="<?= $inventory_1->id_type?>"><?= ucfirst(strtolower($inventory_1->nama))?></option>
						<?php } ?>
					  </select>
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-3">
					 <label for="inventory_2" class="col-sm-3 control-label">Nama Category I</label>
					</div>
					<div class="col-md-6">
					  <select id="inventory_2" name="inventory_2" class="form-control select" onchange="get_inv3()" required>
						<option value="">-- Pilih Category I --</option>
					  </select>
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-3">
					 <label for="inventory_3" class="col-sm-3 control-label">Nama Category II </label>
					</div>
					<div class="col-md-6">
					  <select id="inventory_3" name="inventory_3" class="form-control select" required>
						<option value="">-- Pilih Category II --</option>
					  </select>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<div class="form-group row">
							<div class="col-md-3">
							  <label for="">Nama Category III</label>
							</div>
							 <div class="col-md-6">
							  <input type="text" class="form-control" id="" required name="nm_inventory" placeholder="Nama Inventory">
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
	$("#inventory_1_old").change(function(){

            // variabel dari nilai combo box kendaraan
            var inventory_1 = $("#inventory_1").val();

            // Menggunakan ajax untuk mengirim dan dan menerima data dari server
            $.ajax({
                url:siteurl+'inventory_4/get_inven2',
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

	$("#inventory_2_old").change(function(){

            // variabel dari nilai combo box kendaraan
            var inventory_2 = $("#inventory_2").val();

            // Menggunakan ajax untuk mengirim dan dan menerima data dari server
            $.ajax({
                url:siteurl+'inventory_4/get_inven3',
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
	
    $(document).on('submit', '#data_form', function(e){
		e.preventDefault()
		var data = $('#data_form').serialize();
		// alert(data);

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
			  url:siteurl+'inventory_4/saveNewinventory',
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

  	function get_inv2(){
        var inventory_1=$("#inventory_1").val();
		 $.ajax({
            type:"GET",
            url:siteurl+'inventory_4/get_inven2',
            data:"inventory_1="+inventory_1,
            success:function(html){
               $("#inventory_2").html(html);
            }
        });
    }
		function get_inv3(){
        var inventory_2=$("#inventory_2").val();
		 $.ajax({
            type:"GET",
            url:siteurl+'inventory_4/get_inven3',
            data:"inventory_2="+inventory_2,
            success:function(html){
               $("#inventory_3").html(html);
            }
        });
    }

</script>
