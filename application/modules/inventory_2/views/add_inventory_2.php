<?php
    $ENABLE_ADD     = has_permission('Inventory_2.Add');
    $ENABLE_MANAGE  = has_permission('Inventory_2.Manage');
    $ENABLE_VIEW    = has_permission('Inventory_2.View');
    $ENABLE_DELETE  = has_permission('Inventory_2.Delete');
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


<div class="box box-primary">
  <div class="box-body">
    <br>
    <form id="data_form" autocomplete="off">
      <div class="form-group row">
        <div class="col-md-3">
          <label>Inventory Type</label>
        </div>
        <div class="col-md-9">
          <select id="inventory_1" name="inventory_1" class="form-control input-md chosen-select" required>
            <option value="">Select Inventory</option>
            <?php foreach ($results['inventory_1'] as $inventory_1){
            ?>
            <option value="<?= $inventory_1->id_type?>"><?= strtoupper(strtolower($inventory_1->nama))?></option>
            <?php } ?>
          </select>
        </div>
      </div>
      <div class="form-group row">
        <div class="col-md-3">
          <label>Project Name</label>
        </div>
        <div class="col-md-9">
          <input type="text" class="form-control input-md" id="nm_inventory"  name="nm_inventory" placeholder="Project Name" required>
        </div>
      </div>
      <div class="form-group row">
        <div class="col-md-3"></div>
        <div class="col-md-3">
          <button type="submit" class="btn btn-primary" name="save" id="save"><i class="fa fa-save"></i> Save</button>
        </div>
      </div>
    </form>
  </div>
</div>
<style>
	.chosen-container{
		width: 100% !important;
		text-align : left !important;
	}

</style>
<script type="text/javascript">

  $(document).ready(function() {
    $('.chosen-select').select2();
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
			  url:siteurl+'inventory_2/saveNewinventory',
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
