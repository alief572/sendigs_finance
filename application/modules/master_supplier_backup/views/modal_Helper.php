<?php

if (!empty($id)) {
  $getS   = $this->db->get_where('master_supplier',array('id_supplier'=>$id))->row();
  $arrB   = explode(";",$getS->id_brand);
}
$getB		= $this->db->get('master_product_brand')->result();
echo "$id";
?>
<?php selbrand: ?>

<form class="" action="" method="post" id="form-selBrand">
  <div class="box box-success">
    <div class="box-body" style="">
      <table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%">
        <thead>
          <tr>
            <th>Code</th>
            <th>Brand Name</th>
          </tr>
        </thead>
        <tbody id="tableselBrand_tbody">

        </tbody>
      </table>
      <br>
      <a class="btn btn-sm btn-success" id="addBrand">Add Brand</a>
      <br>
      <br>
      <?php
      echo form_button(array('type'=>'button','class'=>'btn btn-md btn-success','style'=>'min-width:100px; float:right;','value'=>'save','content'=>'Save','id'=>'saveSelBrand')).' ';
      ?>
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
    //console.log('<?=$id?>');
    //DataTables();
    getRefreshBrand();
	});



	$(".numberOnly").on("keypress keyup blur",function (event) {
		if ((event.which < 48 || event.which > 57 ) && (event.which < 46 || event.which > 46 )) {
			event.preventDefault();
		}
	});

	function getNum(val) {
	   if (isNaN(val) || val == '') {
		 return 0;
	   }
	   return parseFloat(val);
	}

  function numfor(nmbr, n, x, s, c) {
    var re = '\\d(?=(\\d{' + (x || 3) + '})+' + (n > 0 ? '\\D' : '$') + ')',
      num = nmbr.toFixed(Math.max(0, ~~n));

    return (c ? num.replace('.', c) : num).replace(new RegExp(re, 'g'), '$&' + (s || ','));
  };

</script>
