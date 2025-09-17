<?php
    $ENABLE_ADD     = has_permission('Loading_Vs_Capacity.Add');
    $ENABLE_MANAGE  = has_permission('Loading_Vs_Capacity.Manage');
    $ENABLE_VIEW    = has_permission('Loading_Vs_Capacity.View');
    $ENABLE_DELETE  = has_permission('Loading_Vs_Capacity.Delete');
?>
<style type="text/css">
thead input {
	width: 100%;
}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">

<div class="box">
	<div class="box-header"><br>
    <div class="form-group row">
      <div class="col-md-2">
        <label for="customer">No Sales Order</label>
      </div>
      <div class="col-md-3">
        <select id='no_so' name='no_so' class='form-control input-sm chosen-select'>
          <option value="0">Select Sales Order</option>
          <?php foreach (get_sales_order() as $key => $value): ?>
            <option value="<?=$value['no_so'];?>"><?=$value['no_so'].' - '.strtoupper($value['name_customer']);?></option>
          <?php endforeach; ?>
        </select>
     </div>
    </div>

    <div class="form-group row">
      <div class="col-md-2">
        <label for="customer">Capacity Day</label>
      </div>
      <div class="col-md-3">
        <input type="text" name="date_range" id="date_range" class="form-control input-md datepicker" style='margin-bottom:5px;' readonly="readonly" placeholder="Select Date">
     </div>
    </div>
	</div><br>
  <div id="tmp_plan">

  </div>


</div>

<!-- page script -->
<style media="screen">
.datepicker{
  cursor: pointer;
}
</style>
<script type="text/javascript">

$(document).ready(function(){
  $('.chosen-select').select2();
  $('.datepicker').daterangepicker({
    showDropdowns: true,
    autoUpdateInput: false,
    locale: {
      cancelLabel: 'Clear'
    }
  });

  $(document).on('change','#no_so', function(){
    get_data();
  });

  $('.datepicker').on('apply.daterangepicker', function(ev, picker) {
    $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
    get_data();
  });

  $('.datepicker').on('cancel.daterangepicker', function(ev, picker) {
    $(this).val('');
    $('#tmp_plan').html('');
  });
});

function get_data(){
  var no_so		    = $('#no_so').val();
  var date_range  = $('#date_range').val();

  if(no_so != '0' && date_range != ''){
    var tgl_awal 	= '0';
  	var tgl_akhir 	= '0';
  	if(date_range != ''){
  		var sPLT 		= date_range.split(' - ');
  		var tgl_awal 	= sPLT[0];
  		var tgl_akhir 	= sPLT[1];
  	}

    $.ajax({
      url: base_url + active_controller+'get_capacity/'+no_so+'/'+tgl_awal+'/'+tgl_akhir,
      cache: false,
      type: "POST",
      dataType: "json",
      success: function(data){
        $("#tmp_plan").html(data.header);
        // $("#kolom").val(data.total);
        swal.close();
      },
      error: function() {
        swal({
          title				: "Error Message !",
          text				: 'Connection Time Out. Please try again..',
          type				: "warning",
          timer				: 3000,
          showCancelButton	: false,
          showConfirmButton	: false,
          allowOutsideClick	: false
        });
      }
    });
  }
}

</script>
