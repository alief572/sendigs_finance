<?php
    $ENABLE_ADD     = has_permission('Inventory_3.Add');
    $ENABLE_MANAGE  = has_permission('Inventory_3.Manage');
    $ENABLE_VIEW    = has_permission('Inventory_3.View');
    $ENABLE_DELETE  = has_permission('Inventory_3.Delete');
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
            <label>Inventory Type</label>
          </div>
          <div class="col-md-9">
            <select id="inventory_1" name="inventory_1" class="form-control chosen-select" onchange="get_inv2()" required>
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
            <select id="inventory_2" name="inventory_2" class="form-control chosen-select" required>
						<option value="">List Empty</option>
					  </select>
          </div>
        </div>

        <div class="form-group row">
          <div class="col-md-3">
            <label>Product Name</label>
          </div>
          <div class="col-md-9">
            <input type="text" class="form-control" id="" required name="nm_inventory" placeholder="Product Name">
          </div>
        </div>

        <div class="form-group row">
          <div class="col-md-3">
            <label>Dimensi (L,W,H)</label>
          </div>
          <div class="col-md-3">
            <input type="text" class="form-control maskM getCub" required id="length" name="length" placeholder="Length" data-decimal="." data-thousand="" data-precision="0" data-allow-zero="">
          </div>
          <div class="col-md-3">
            <input type="text" class="form-control maskM getCub" required id="wide" name="wide" placeholder="Wide" data-decimal="." data-thousand="" data-precision="0" data-allow-zero="">
          </div>
          <div class="col-md-3">
            <input type="text" class="form-control maskM getCub" required id="high" name="high" placeholder="High" data-decimal="." data-thousand="" data-precision="0" data-allow-zero="">
          </div>
        </div>

        <div class="form-group row">
          <div class="col-md-3">
            <label>CUB</label>
          </div>
          <div class="col-md-9">
            <input type="text" class="form-control" id="cub" name="cub" placeholder="CUB" readonly>
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
  </div>
<script src="<?= base_url('assets/js/jquery.maskMoney.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>
<script type="text/javascript">

  $(document).ready(function() {
	$('.chosen-select').select2();
  $('.maskM').maskMoney();
	$("#inventory_1_old").change(function(){

        // variabel dari nilai combo box kendaraan
        var inventory_1 = $("#inventory_1").val();

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
                    html += '<option value='+data[i].id_category1+'>'+data[i].nama+'</option>';
                }
                $('#inventory_2').html(html);

            }
        });
    });

    $(document).on('keyup','.getCub',function(){
      get_cub();
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
			  url:siteurl+'inventory_3/saveNewinventory',
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

    function get_cub(){
      var l   = getNum($('#length').val().split(",").join(""));
      var w   = getNum($('#wide').val().split(",").join(""));
      var h   = getNum($('#high').val().split(",").join(""));
      var cub = (l * w * h) / 1000000000;

      $('#cub').val(cub.toFixed(7));
    }



</script>
