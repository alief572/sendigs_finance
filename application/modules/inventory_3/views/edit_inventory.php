<?php
    $ENABLE_ADD     = has_permission('Inventory_3.Add');
    $ENABLE_MANAGE  = has_permission('Inventory_3.Manage');
    $ENABLE_VIEW    = has_permission('Inventory_3.View');
    $ENABLE_DELETE  = has_permission('Inventory_3.Delete');

foreach ($results['inven'] as $inven){
}

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
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<div class="box box-primary">
	<!-- /.box-header -->
	<div class="box-body">
    <br>
    <form id="data_form" autocomplete="off">
      <input type="hidden" name="id_inventory" id="id_inventory" value='<?= $inven->id_category2 ?>'>
    <div class="form-group row">
    <div class="col-md-3">
      <label>Inventory Type</label>
    </div>
    <div class="col-md-9">
      <select id="inventory_1"  name="inventory_1"  class="form-control chosen-select" required>
        <?php foreach ($results['lvl1'] as $lvl1){
        $select = $inven->id_type == $lvl1->id_type ? 'selected' : '';
        ?>
        <option value="<?= $lvl1->id_type?>" <?= $select ?>><?= strtoupper($lvl1->nama)?></option>
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
        <?php foreach ($results['lvl2'] as $lvl2){
        $select = $inven->id_category1 == $lvl2->id_category1 ? 'selected' : '';
        ?>
        <option value="<?= $lvl2->id_category1?>" <?= $select ?>><?= strtoupper($lvl2->nama)?></option>
        <?php } ?>
      </select>
    </div>
    </div>

    <div class="form-group row">
      <div class="col-md-3">
        <label>Product Name</label>
      </div>
      <div class="col-md-9">
        <input type="text" class="form-control" id="nm_inventory" required name="nm_inventory" placeholder="Product Name" value="<?= $inven->nama ?>">
      </div>
    </div>

    <div class="form-group row">
      <div class="col-md-3">
        <label>Dimensi (L,W,H)</label>
      </div>
      <div class="col-md-3">
        <input type="text" class="form-control maskM getCub" required id="length" name="length" placeholder="Length" value="<?= number_format($inven->length); ?>" data-decimal="." data-thousand="" data-precision="0" data-allow-zero="">
      </div>
      <div class="col-md-3">
        <input type="text" class="form-control maskM getCub" required id="wide" name="wide" placeholder="Wide" value="<?= number_format($inven->wide); ?>" data-decimal="." data-thousand="" data-precision="0" data-allow-zero="">
      </div>
      <div class="col-md-3">
        <input type="text" class="form-control maskM getCub" required id="high" name="high" placeholder="High" value="<?= number_format($inven->high); ?>" data-decimal="." data-thousand="" data-precision="0" data-allow-zero="">
      </div>
    </div>

    <div class="form-group row">
      <div class="col-md-3">
        <label>CUB</label>
      </div>
      <div class="col-md-9">
        <input type="text" class="form-control" id="cub" name="cub" placeholder="CUB" readonly value="<?= $inven->cub ?>">
      </div>
    </div>

    <div class="form-group row">
      <div class="col-md-3">
        <label>Status</label>
      </div>
      <div class="col-md-9">
        <?php if ($inven->aktif == 'aktif'){?>
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
    <div class="form-group row">
    <div class="col-md-3"></div>
    <div class="col-md-9">
      <button type="submit" class="btn btn-primary" name="save" id="save"><i class="fa fa-save"></i> Save</button>
    </div>
    </div>
  </form>
	</div>
	<!-- /.box-body -->
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/js/jquery.maskMoney.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>

<!-- page script -->
<script type="text/javascript">

  	$(function() {
		$('.chosen-select').select2();
  	});
	$("#inventory_1").change(function(){

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
                        html += '<option value='+data[i].id_inventory2+'>'+data[i].nm_inventory2+'</option>';
                    }
                    $('#inventory_2').html(html);

                }
            });
        });

	// ADD CUSTOMER
  $(document).on('keyup','.getCub',function(){
    get_cub();
  });

	$(document).on('submit', '#data_form', function(e){
		e.preventDefault()
		var data = $('#data_form').serialize();
		var id = $('#id_inventory').val();
		// alert(id);
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
			  url:siteurl+'inventory_3/saveEditInventory',
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

  function get_cub(){
    var l   = getNum($('#length').val().split(",").join(""));
    var w   = getNum($('#wide').val().split(",").join(""));
    var h   = getNum($('#high').val().split(",").join(""));
    var cub = (l * w * h) / 1000000000;

    $('#cub').val(cub.toFixed(7));
  }


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
