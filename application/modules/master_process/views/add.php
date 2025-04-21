<?php
    $ENABLE_ADD     = has_permission('Inventory_3.Add');
    $ENABLE_MANAGE  = has_permission('Inventory_3.Manage');
    $ENABLE_VIEW    = has_permission('Inventory_3.View');
    $ENABLE_DELETE  = has_permission('Inventory_3.Delete');
	
	$id				= (!empty($header[0]->id))?$header[0]->id:'';
	$nm_process		= (!empty($header[0]->nm_process))?$header[0]->nm_process:'';
	$keterangan		= (!empty($header[0]->keterangan))?$header[0]->keterangan:'';
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
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<link rel="stylesheet" href="<?= base_url('assets/plugins/sweetalert/dist/sweetalert.css')?>">

          <div class="box box-primary">
            <div class="box-body">
              <br>
			<form id="data_form" autocomplete="off">
        


        <div class="form-group row">
          <div class="col-md-3">
            <label>Process Name</label>
          </div>
          <div class="col-md-9">
            <input type="text" class="form-control" id="nm_process" required name="nm_process" placeholder="Process Name" value='<?=$nm_process;?>'>
			<input type="hidden" class="form-control" id="id" required name="id" placeholder="Process Name" value='<?=$id;?>'>
          </div>
        </div>
		<div class="form-group row">
          <div class="col-md-3">
            <label>Information</label>
          </div>
          <div class="col-md-9">
            <textarea type="text" class="form-control" id="keterangan" required name="keterangan" placeholder="Information"><?=$keterangan;?></textarea>
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

<script type="text/javascript">

  $(document).ready(function() {

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
			  url:siteurl+'master_process/save_data',
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
            url:siteurl+'inventory_3/get_inven2',
            data:"inventory_1="+inventory_1,
            success:function(html){
               $("#inventory_2").html(html);
            }
        });
    }



</script>
