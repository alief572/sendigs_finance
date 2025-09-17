<?php

if (!empty($id)) {
  $getC		= $this->db->get_where('child_supplier_pattern',array('id_pattern'=>$id))->row();
  $name_supplier = $this->db->get_where('master_supplier',array('id_supplier'=>$getC->id_supplier))->row();
}
$getS		= $this->db->get_where('master_supplier',array('activation'=>'active'))->result();

?>
<form id="form-PatternName" action="" method="post" class="form-active">
<div class="box box-success">
	<div class="box-body" style="">
		<table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%">
			<tbody>
				<tr style='background-color: #175477; font-size: 15px;'>
          <th class="" style="width:20%">
            Pattern Name :
          </th>
					<th class="">
            <div class="label_view">
              <?=empty($getC)?'':$getC->name_pattern?>
            </div>
            <div class="label_input">
              <input type="hidden" name="type" value="<?=empty($getC)?'add':'edit'?>">
              <input type="hidden" name="id_pattern" value="<?=empty($getC)?'':$getC->id_pattern?>">
              <input type="text" name="name_pattern" id="name_pattern" value="<?=empty($getC)?'':$getC->name_pattern?>" class="form-control input input-sm required w70">
              <label class="label label-danger hideIt name_pattern">Pattern Name Can't be empty!</label>
            </div>

          </th>
				</tr>
				<tr style='background-color: #175477; font-size: 15px;'>
          <th class="">
            Item Name :
          </th>
					<th class="">
            <div class="label_view">
              <?=empty($getC)?'':$getC->item_name?>
            </div>
            <div class="label_input">
              <input type="text" name="item_name" id="item_name" value="<?=empty($getC)?'':$getC->item_name?>" class="form-control input input-sm required w70">
              <label class="label label-danger hideIt item_name">Item Name Can't be empty!</label>
            </div>

          </th>
				</tr>
          <tr style='background-color: #175477; font-size: 15px;'>
            <th class="">
              Supplier :
            </th>
            <th class=" select2-bootstrap-append">
              <?php if (empty($id)): ?>
                <div class="label_input">
                  <select class="form-control select2 select2_edit required" name="id_supplier" id="id_supplier">
                    <?php if ($getS): ?>
                      <option value=""></option>
                    <?php endif; ?>
                  </select>
                  <label class="label label-danger hideIt id_supplier">Supplier Can't be empty!</label>
                </div>
                <div class="label_view">
                  <?=!empty($getC)?$name_supplier->nm_supplier:''?>
                </div>
              <?php else: ?>
                <?=!empty($getC)?$getC->name_supplier:'-'?>
              <?php endif; ?>
            </th>
          </tr>

		</table>

		<br>
    <a id="addPatternNameSave" class="btn btn-sm btn-success">Save</a>

	</div>
</div>
</form>

<script type="text/javascript">

$(document).ready(function(){
  if ('<?= $this->uri->segment(4) ?>' == 'view') {
    view_selector();
  }else {
    input_selector();
  }

  $(".datepicker").datepicker({
      format : "yyyy-mm-dd",
      showInputs: true,
      autoclose:true
  });
  if ('<?=empty($getC->id_pattern)?>') {
    $(".select2_edit").select2({
      placeholder: "Choose An Option",
      allowClear: true,
      width: '70%'
      //templateResult: formatState,
      //dropdownParent: $("#form-PatternName")
    });
    var row = '<option value=""></option>'+
    <?php foreach ($getS as $key => $vs): ?>
    '<option value="<?=$vs->id_supplier?>"><?=$vs->nm_supplier?></option>'+
    <?php endforeach; ?>
    '';
    $('.select2_edit').html(row);
  }
  $('.select2-selection').on('click', function (e) {
    $('li.select2-results__option').each(function(e) {
      val = $(this).parent('option').html();
      //$(this).text('XXX');
      console.log(val);
      //$(this).append(' <a data-id="'+val+'" class="btn btn-sm btn-primary edit-select2" onclick="getsss()" style="text-align:right">Edit</a> ');
    });
  });
  $(document).on('click', 'a.edit-select2', function() {
    var id = $(this).find().parent('li').html();
    console.log('AHAHAHA');
  });

});
function getsss() {
  console.log('AHAHAHA');
}
function formatState (state) {
  if (!state.id) {
    return state.text;
  }
  var baseUrl = "/user/pages/images/flags";
  var $state = $(
    '<span>'+state.text+' <a data-id="'+state.element.value+'" class="btn btn-sm btn-primary edit-select2" onclick="alert("AAA")" style="text-align:right">Edit</a></span>'
    //'<span><img src="' + baseUrl + '/' + state.element.value.toLowerCase() + '.png" class="img-flag" /> ' + state.text + '</span>'
  );
  return $state;
};

</script>
