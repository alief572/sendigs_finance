<?php

if (!empty($id)) {
  $getC		= $this->db->get_where('child_customer_category',array('id_category_customer'=>$id))->row();
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
            <input type="hidden" name="id_category_customer" value="<?=empty($getC)?'':$getC->id_category_customer?>">
            <input type="text" name="name_category_customer" value="<?=empty($getC)?'':$getC->name_category_customer?>" class="form-control input input-sm">
          </th>
          <tr style='background-color: #175477; font-size: 15px;'>
            <th class="text-center">
              Customer Code
            </th>
  					<th class="text-center">
              <input type="text" name="customer_code" class="form-control input input-sm" value="<?=empty($getC)?'':$getC->customer_code?>">
            </th>
				</tr>

		</table>
		<br>
    <a id="addCustomerCategorySave" class="btn btn-sm btn-success">Save</a>

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
