<?php

if (!empty($id)) {
  $getC		= $this->db->get_where('child_supplier_type',array('id_type'=>$id))->row();
}

?>
<form id="form-type" action="" method="post">
<div class="box box-success">
	<div class="box-body" style="">
		<table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%">
			<tbody>
				<tr style='background-color: #175477; font-size: 15px;'>
          <th class="text-center">
            Supplier Type Name:
          </th>
					<th class="text-center">
            <input type="hidden" name="type" value="<?=empty($getC)?'add':'edit'?>">
            <input type="hidden" name="id_type" value="<?=empty($getC)?'':$getC->id_type?>">
            <input type="text" name="name_type" value="<?=empty($getC)?'':$getC->name_type?>" class="form-control input input-sm">
          </th>
				</tr>

		</table>
		<br>
    <a id="addSupplierTypeSave" class="btn btn-sm btn-success">Save</a>

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
    

	});

</script>
