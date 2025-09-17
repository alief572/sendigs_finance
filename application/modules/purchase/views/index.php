
<style type="text/css">
thead input {
	width: 100%;
}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">

<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data">
<div class="box">

	<div class="box-header">
    <!-- <?php
      echo form_button(array('type'=>'button','class'=>'btn btn-sm btn-success','style'=>'min-width:100px; float:right; margin: 5px 0px 5px 0px;','value'=>'Add PO','content'=>'Add PO','id'=>'addPO')).' ';
    ?> -->
		<span class="pull-right">
		</span>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
		<thead>
		<tr>
      <th class="text-center">No</th>
			<th class="text-center">No PO</th>
			<th class="text-center">Supplier</th>
			<th class="text-center">Create By</th>
			<th class="text-center">Created Date</th>
			<th class="text-center">Status</th>
			<th class="text-center">Option</th>
		</tr>
		</thead>

		<tbody></tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>


<!-- modal -->
	<div class="modal fade" id="ModalView2" style='overflow-y: auto;'>
		<div class="modal-dialog"  style='width:80%; '>
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="head_title2"></h4>
					</div>
					<div class="modal-body" id="view2">
					</div>
					<div class="modal-footer">
					<!--<button type="button" class="btn btn-primary">Save</button>-->
					<button type="button" class="btn btn-default " data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	<!-- modal -->
</form>
<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>
<script src="<?= base_url('assets/js/jquery.maskMoney.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>

<!-- page script -->
<script type="text/javascript">

$(document).ready(function(){
  $('.maskM').maskMoney();
  DataTables();
});

$(".numberOnly").on("keypress keyup blur",function (event) {
		if ((event.which < 48 || event.which > 57 ) && (event.which < 46 || event.which > 46 )) {
				event.preventDefault();
		}
});

$(document).on('click', '#addPO', function(e){
  e.preventDefault();
  // loading_spinner();
  $("#head_title2").html("<b>ADD PURCHASE ORDER</b>");
  $("#view2").load(base_url +'purchase/add_po');
  $("#ModalView2").modal();
});

  $(document).on('click', '.detailMat', function(e){
  e.preventDefault();
  // loading_spinner();
  $("#head_title2").html("<b>TOTAL MATERIAL PURCHASE ["+$(this).data('no_po')+"]</b>");
  $("#view2").load(base_url +'/purchase/detail_purchase/'+$(this).data('no_po')+'/'+$(this).data('status'));
  $("#ModalView2").modal();
});

$(document).on('click', '.editMat', function(e){
  e.preventDefault();
  // loading_spinner();
  $("#head_title2").html("<b>EDIT MATERIAL PURCHASE ["+$(this).data('no_po')+"]</b>");
  $("#view2").load(base_url +'purchase/edit_purchase/'+$(this).data('no_po'));
  $("#ModalView2").modal();
});

$(document).on('click', '#savePO', function(){
  var id_supplier = $('#id_supplier').val();
  if( id_supplier == '0'){
    swal({
      title	: "Error Message!",
      text	: 'Supplier Not Select, please input first ...',
      type	: "warning"
    });
    $('#savePO').prop('disabled',false);
    return false;
  }

  if($('input[type=checkbox]:checked').length == 0){
    swal({
      title	: "Error Message!",
      text	: 'Checklist Minimal One Component',
      type	: "warning"
    });
    $('#savePO').prop('disabled',false);
    return false;
  }

  swal({
    title: "Are you sure?",
    text: "You will booking material planning be able to process again this data!",
    type: "warning",
    showCancelButton: true,
    confirmButtonClass: "btn-danger",
    confirmButtonText: "Yes, Process it!",
    cancelButtonText: "No, cancel process!",
    closeOnConfirm: false,
    closeOnCancel: false
  },
  function(isConfirm) {
    if (isConfirm) {
      // loading_spinner();
      var formData  	= new FormData($('#form_proses_bro')[0]);
      $.ajax({
        url			: base_url+'purchase/createPO',
        type		: "POST",
        data		: formData,
        cache		: false,
        dataType	: 'json',
        processData	: false,
        contentType	: false,
        success		: function(data){
          if(data.status == 1){
            swal({
                title	: "Save Success!",
                text	: data.pesan,
                type	: "success",
                timer	: 7000,
                showCancelButton	: false,
                showConfirmButton	: false,
                allowOutsideClick	: false
              });
            window.location.href = base_url + active_controller;
          }
          else if(data.status == 0){
            swal({
              title	: "Save Failed!",
              text	: data.pesan,
              type	: "warning",
              timer	: 7000,
              showCancelButton	: false,
              showConfirmButton	: false,
              allowOutsideClick	: false
            });
          }
        },
        error: function() {
          swal({
            title				: "Error Message !",
            text				: 'An Error Occured During Process. Please try again..',
            type				: "warning",
            timer				: 7000,
            showCancelButton	: false,
            showConfirmButton	: false,
            allowOutsideClick	: false
          });
        }
      });
    } else {
    swal("Cancelled", "Data can be process again :)", "error");
    return false;
    }
  });
});

//EditPurchase
$(document).on('click', '#updatePur', function(){

  swal({
    title: "Are you sure?",
    text: "You will booking material planning be able to process again this data!",
    type: "warning",
    showCancelButton: true,
    confirmButtonClass: "btn-danger",
    confirmButtonText: "Yes, Process it!",
    cancelButtonText: "No, cancel process!",
    closeOnConfirm: false,
    closeOnCancel: false
  },
  function(isConfirm) {
    if (isConfirm) {
      // loading_spinner();
      var formData  	= new FormData($('#form_proses_bro')[0]);
      $.ajax({
        url			: base_url+'purchase/updatePur',
        type		: "POST",
        data		: formData,
        cache		: false,
        dataType	: 'json',
        processData	: false,
        contentType	: false,
        success		: function(data){
          if(data.status == 1){
            swal({
                title	: "Save Success!",
                text	: data.pesan,
                type	: "success",
                timer	: 7000,
                showCancelButton	: false,
                showConfirmButton	: false,
                allowOutsideClick	: false
              });
            window.location.href = base_url + active_controller;
          }
          else if(data.status == 0){
            swal({
              title	: "Save Failed!",
              text	: data.pesan,
              type	: "warning",
              timer	: 7000,
              showCancelButton	: false,
              showConfirmButton	: false,
              allowOutsideClick	: false
            });
          }
        },
        error: function() {
          swal({
            title				: "Error Message !",
            text				: 'An Error Occured During Process. Please try again..',
            type				: "warning",
            timer				: 7000,
            showCancelButton	: false,
            showConfirmButton	: false,
            allowOutsideClick	: false
          });
        }
      });
    } else {
    swal("Cancelled", "Data can be process again :)", "error");
    return false;
    }
  });
});

$(document).on('click', '.cancelPO', function(){
  var no_po = $(this).data('no_po');
  // alert(no_po);
  // return false;

  swal({
    title: "Are you sure?",
    text: "You will cancel material planning be able to process again this data!",
    type: "warning",
    showCancelButton: true,
    confirmButtonClass: "btn-danger",
    confirmButtonText: "Yes, Process it!",
    cancelButtonText: "No, cancel process!",
    closeOnConfirm: false,
    closeOnCancel: false
  },
  function(isConfirm) {
    if (isConfirm) {
      // loading_spinner();
      var formData  	= new FormData($('#form_proses_bro')[0]);
      $.ajax({
        url			: base_url+'purchase/cancelPO/'+no_po,
        type		: "POST",
        data		: formData,
        cache		: false,
        dataType	: 'json',
        processData	: false,
        contentType	: false,
        success		: function(data){
          if(data.status == 1){
            swal({
                title	: "Save Success!",
                text	: data.pesan,
                type	: "success",
                timer	: 7000,
                showCancelButton	: false,
                showConfirmButton	: false,
                allowOutsideClick	: false
              });
            window.location.href = base_url + active_controller;
          }
          else if(data.status == 0){
            swal({
              title	: "Save Failed!",
              text	: data.pesan,
              type	: "warning",
              timer	: 7000,
              showCancelButton	: false,
              showConfirmButton	: false,
              allowOutsideClick	: false
            });
          }
        },
        error: function() {
          swal({
            title				: "Error Message !",
            text				: 'An Error Occured During Process. Please try again..',
            type				: "warning",
            timer				: 7000,
            showCancelButton	: false,
            showConfirmButton	: false,
            allowOutsideClick	: false
          });
        }
      });
    } else {
    swal("Cancelled", "Data can be process again :)", "error");
    return false;
    }
  });
});

$(document).on('click', '.delMat', function(){
  var id = $(this).data('id');
  var no_po = $(this).data('no_po');
  var id_material = $(this).data('id_material');
  // alert(no_po);
  // return false;

  swal({
    title: "Are you sure?",
    text: "You will delete material planning be able to process again this data!",
    type: "warning",
    showCancelButton: true,
    confirmButtonClass: "btn-danger",
    confirmButtonText: "Yes, Process it!",
    cancelButtonText: "No, cancel process!",
    closeOnConfirm: false,
    closeOnCancel: false
  },
  function(isConfirm) {
    if (isConfirm) {
      // loading_spinner();
      var formData  	= new FormData($('#form_proses_bro')[0]);
      $.ajax({
        url			: base_url+'purchase/cancel_mat_sebagian/'+id+'/'+no_po+'/'+id_material,
        type		: "POST",
        data		: formData,
        cache		: false,
        dataType	: 'json',
        processData	: false,
        contentType	: false,
        success		: function(data){
          if(data.status == 1){
            swal({
                title	: "Save Success!",
                text	: data.pesan,
                type	: "success",
                timer	: 7000,
                showCancelButton	: false,
                showConfirmButton	: false,
                allowOutsideClick	: false
              });
            // window.location.href = base_url + active_controller+'/material_purchase';
            $("#head_title2").html("<b>EDIT MATERIAL PURCHASE ["+data.no_po+"]</b>");
            $("#view2").load(base_url +'purchase/edit_purchase/'+data.no_po);
            $("#ModalView2").modal();
          }
          else if(data.status == 0){
            swal({
              title	: "Save Failed!",
              text	: data.pesan,
              type	: "warning",
              timer	: 7000,
              showCancelButton	: false,
              showConfirmButton	: false,
              allowOutsideClick	: false
            });
          }
        },
        error: function() {
          swal({
            title				: "Error Message !",
            text				: 'An Error Occured During Process. Please try again..',
            type				: "warning",
            timer				: 7000,
            showCancelButton	: false,
            showConfirmButton	: false,
            allowOutsideClick	: false
          });
        }
      });
    } else {
    swal("Cancelled", "Data can be process again :)", "error");
    return false;
    }
  });
});
    function DataTables(){
  		var dataTable = $('#example1').DataTable({
  			// "scrollX": true,
  			"scrollY": "500",
  			"scrollCollapse" : true,
  			"processing" : true,
  			"serverSide": true,
  			"stateSave" : true,
  			"bAutoWidth": true,
  			"destroy": true,
  			"responsive": true,
  			"oLanguage": {
  				"sSearch": "<b>Live Search : </b>",
  				"sLengthMenu": "_MENU_ &nbsp;&nbsp;<b>Records Per Page</b>&nbsp;&nbsp;",
  				"sInfo": "Showing _START_ to _END_ of _TOTAL_ entries",
  				"sInfoFiltered": "(filtered from _MAX_ total entries)",
  				"sZeroRecords": "No matching records found",
  				"sEmptyTable": "No data available in table",
  				"sLoadingRecords": "Please wait - loading...",
  				"oPaginate": {
  					"sPrevious": "Prev",
  					"sNext": "Next"
  				}
  			},
  			"aaSorting": [[ 1, "asc" ]],
  			"columnDefs": [ {
  				"targets": 'no-sort',
  				"orderable": false,
  			}],
  			"sPaginationType": "simple_numbers",
  			"iDisplayLength": 10,
  			"aLengthMenu": [[10, 20, 50, 100, 150], [10, 20, 50, 100, 150]],
  			"ajax":{
  				url : siteurl+'purchase/data_side_purchase',
  				type: "post",
  				data: function(d){
  					// d.kode_partner = $('#kode_partner').val()
  				},
  				cache: false,
  				error: function(){
  					$(".my-grid-error").html("");
  					$("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
  					$("#my-grid_processing").css("display","none");
  				}
  			}
  		});
  	}

    function DataTables3(){
		var dataTable = $('#my-grid3').DataTable({
			"processing": true,
			"serverSide": true,
			"stateSave" : true,
			"bAutoWidth": true,
			"destroy": true,
			"responsive": true,
			"oLanguage": {
				"sSearch": "<b>Live Search : </b>",
				"sLengthMenu": "_MENU_ &nbsp;&nbsp;<b>Records Per Page</b>&nbsp;&nbsp;",
				"sInfo": "Showing _START_ to _END_ of _TOTAL_ entries",
				"sInfoFiltered": "(filtered from _MAX_ total entries)",
				"sZeroRecords": "No matching records found",
				"sEmptyTable": "No data available in table",
				"sLoadingRecords": "Please wait - loading...",
				"oPaginate": {
					"sPrevious": "Prev",
					"sNext": "Next"
				}
			},
			"aaSorting": [[ 2, "asc" ]],
			"columnDefs": [ {
				"targets": 'no-sort',
				"orderable": false,
			}],
			"sPaginationType": "simple_numbers",
			"iDisplayLength": 10,
			"aLengthMenu": [[10, 20, 50, 100, 150], [10, 20, 50, 100, 150]],
			"ajax":{
				url : base_url +'index.php/'+active_controller+'/data_side_request',
				type: "post",
				data: function(d){
					// d.kode_partner = $('#kode_partner').val()
				},
				cache: false,
				error: function(){
					$(".my-grid-error").html("");
					$("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
					$("#my-grid_processing").css("display","none");
				}
			}
		});
	}

	function getNum(val) {
		if (isNaN(val) || val == '') {
				return 0;
		}
		return parseFloat(val);
	}
</script>
