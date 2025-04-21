<?php

if (!empty($id)) {
  $getC		= $this->db->get_where('master_product_category',array('id_category'=>$id))->row();
}

?>
<form id="form-category" action="" method="post">
<div class="box box-success">
	<div class="box-body" style="">
		<table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%">
			<tbody>
				<tr style='background-color: #175477; font-size: 15px;'>
          <th class="text-center">
            Name:
          </th>
					<th class="text-center">
            <input type="hidden" name="type" value="<?=empty($getC)?'add':'edit'?>">
            <input type="hidden" name="id_category" value="<?=empty($getC)?'':$getC->id_category?>">
            <input type="text" name="name_category" value="<?=empty($getC)?'':$getC->name_category?>" class="form-control input input-sm">
          </th>
          <tr style='background-color: #175477; font-size: 15px;'>
            <th class="text-center">
              Shipment:
            </th>
  					<th class="text-center">
              <select class="form-control" name="supplier_shipping" id="supplier_shipping">
                <?php
                  if (!empty($getC) && strtolower($getC->supplier_shipping) == 'import'):
                    $import = 'selected';
                    $local  = '';
                  else:
                    $local  = 'selected';
                    $import = '';
                  endif;
                ?>
                <option value="Import" <?=$import?> >IMPORT</option>
                <option value="Local" <?=$local?> >LOCAL</option>
              </select>
            </th>
				</tr>

		</table>
		<br>
    <a id="addProductCategorySave" class="btn btn-sm btn-success">Save</a>

	</div>
</div>
</form>

<style>
	.inSp{
		text-align: center;
		display: inline-block;
		width: 100px;
	}
	.inSp2{
		text-align: center;
		display: inline-block;
		width: 45%;
	}
	.inSpL{
		text-align: left;
	}
	.vMid{
		vertical-align: middle !important;
	}

</style>

<script type="text/javascript">

	$(document).ready(function(){
    $(document).on('click', '#addProductCategorySave', function(e){
      var formdata = $("#form-category").serialize();
      $.ajax({
        url: siteurl+active_controller+"saveProductCategory",
        dataType : "json",
        type: 'POST',
        data: formdata,
        success: function(result){
          //console.log(result['msg']);
          if(result.status=='1'){
            swal({
              title: "Sukses!",
              text: result['msg'],
              type: "success",
              timer: 1500,
              showConfirmButton: false
            });
            setTimeout(function(){
              //$("#ModalView2").modal('hide');
              DataTables('set');
              $('body').removeClass('modal-open');
              if (($("#ModalView3").data('bs.modal') || {}).isShown) {
                $("#ModalView3").modal('hide');
              }else if (($("#ModalView2").data('bs.modal') || {}).isShown) {
                $("#ModalView2").modal('hide');
              }else {
                $("#ModalView").modal('hide');
              }

            },1600);
          } else {
            swal({
              title: "Gagal!",
              text: result['msg'],
              type: "error",
              timer: 1500,
              showConfirmButton: false
            });
          };
        },
        error: function (request, error) {
          console.log(arguments);
          alert(" Can't do because: " + error);
        }
      });
      //$("#ModalView").modal('hide');
    });

	});

</script>
