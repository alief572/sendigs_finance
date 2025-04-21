<?php
    $ENABLE_ADD     = has_permission('Incoming.Add');
    $ENABLE_MANAGE  = has_permission('Incoming.Manage');
    $ENABLE_VIEW    = has_permission('Incoming.View');
    $ENABLE_DELETE  = has_permission('Incoming.Delete');
?>
<style type="text/css">
thead input {
	width: 100%;
}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">

<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data">
<div class="box box-primary">
	<div class="box-header">
		<div class="box-tool pull-right">
		</div>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<div class="box box-success">
			<div class="box-body">
				<br>
				<input type="hidden" id='tandax' name='tandax' value='IN'>
        <input type="hidden" id='gudang_before' name='gudang_before' value='OPC'>
				<!-- <form>
					<label class="radio-inline">
					<input type="radio" name="adjustment" id='in' value='IN'><b>Purchasing IN</b>
					</label>
					<label class="radio-inline">
					<input type="radio" name="adjustment" id='Out' value='OUT'><b>Adjustment to Produksi</b>
					</label>
					<label class="radio-inline">
					<input type="radio" name="adjustment" id='move' value='MOVE'><b>Material Mutation</b>
					</label>
				</form><br><br><br> -->
				<div class='in_ipp'>
					<div class='form-group row'>
						<label class='label-control col-sm-2'><b>Nomor PO</b></label>
						<div class='col-sm-4'>
							<select id='no_ipp' name='no_ipp' class='form-control input-sm chosen-select' style='min-width:200px;'>
								<option value='0'>Select Purchase Order</option>
                <?php
                    foreach($results['no_po'] AS $val => $valx){
                        echo "<option value='".$valx['no_po']."'>".strtoupper($valx['no_po'])."</option>";
                    }
                ?>
							</select>
						</div>
					</div>
          <div class='form-group row'>
						<label class='label-control col-sm-2'></label>
						<div class='col-sm-4'>
            <?php
              echo form_button(array('type'=>'button','class'=>'btn btn-md btn-primary','style'=>'min-width:100px; margin: 5px 0px 5px 0px;','value'=>'Process','content'=>'Process','id'=>'modalDetail')).' ';
            ?>
						</div>
					</div>
				</div>
				<!-- <div class='form-group row'>
					<label class='label-control col-sm-2'><b><span class='in_id'>Origin </span>Warehouse</b></label>
					<div class='col-sm-4'>
						<select id='gudang_before' name='gudang_before' class='form-control input-sm' style='min-width:200px;'>
							<option value='0'>List Empty</option>
							<?php
  							// foreach($data_gudang AS $val => $valx){
  							// 	echo "<option value='".$valx['kd_gudang']."'>".strtoupper($valx['nm_gudang'])."</option>";
								// }
							?>
						</select>
					</div>
					<div class='in_id'>
						<label class='label-control col-sm-2'><b>Destination Warehouse</b></label>
						<div class='col-sm-4'>
							<select id='gudang_after' name='gudang_after' class='form-control input-sm' style='min-width:200px;'>
								<option value='0'>List Empty</option>
								<?php
									// foreach($data_gudang AS $val => $valx){
									// 	echo "<option value='".$valx['kd_gudang']."'>".strtoupper($valx['nm_gudang'])."</option>";
									// }
								?>
							</select>
						</div>
					</div>
				</div> -->

				<?php
					// if($akses_menu['create']=='1'){
						echo form_button(array('type'=>'button','class'=>'btn btn-md btn-success','style'=>'min-width:100px; float:right; margin: 5px 0px 5px 0px;','value'=>'Process','content'=>'Process','id'=>'processAjust')).' ';
						echo form_button(array('type'=>'button','class'=>'btn btn-md btn-danger','style'=>'min-width:100px; float:right; margin: 5px 0px 5px 0px;','value'=>'Process','content'=>'Process','id'=>'moveGudang')).' ';
					// }
				?>
			</div>
		</div>
		<table class="table table-bordered table-striped" id="example1" width='100%'>
			<thead>
				<tr>
					<th class="text-center">No</th>
					<th class="text-center">No Trans</th>
          <th class="text-center">Qty Packing</th>
					<th class="text-center">Qty Material</th>
					<th class="text-center">Process By</th>
					<th class="text-center">Process Date</th>
					<th class="text-center">Option</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
	<!-- /.box-body -->
 </div>
  <!-- /.box -->
	<!-- modal -->
	<div class="modal fade" id="ModalView2" style='overflow-y: auto;'>
		<div class="modal-dialog"  style='width:90%; '>
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
<script>
	$(document).ready(function(){
		$('.maskM').maskMoney();
      $('.chosen-select').select2();
		DataTables();
		$('.in_id').hide();

		$('#processAjust').hide();
		// $('#modalDetail').hide();
		$('#moveGudang').hide();

		$(".numberOnly").on("keypress keyup blur",function (event) {
			if ((event.which < 48 || event.which > 57 ) && (event.which < 46 || event.which > 46 )) {
				event.preventDefault();
			}
		});

    $(document).on('keyup', '.qty_in', function(e){
			var no       = $(this).data('no');
      var konversi = getNum($(this).data('konversi'));
      var value    = getNum($(this).val());
      var hasil    = konversi * value;
      $("#qty_in_"+no).val(hasil);
		});

    $(document).on('keyup', '.qty_rusak', function(e){
			var no       = $(this).data('no');
      var konversi = getNum($(this).data('konversi'));
      var value    = getNum($(this).val());
      var hasil    = konversi * value;
      $("#qty_rusak_"+no).val(hasil);
		});

	});

  $(document).on('keyup','.qty_in',function(){
    var no  = $(this).data('no');
    var inx = getNum($(this).val().split(",").join(""));
    var before = getNum($(".qty_sebelum_"+no).val().split(",").join(""));
    // console.log(inx);
    // console.log(before);
    if(inx > before){
      $('#saveINMaterial').prop('disabled',true);
    }
    else{
      $('#saveINMaterial').prop('disabled',false);
    }
  });


	$(document).on('click', '.detailAjust', function(e){
		e.preventDefault();
		$("#head_title2").html("<b>DETAIL ADJUSTMENT ["+$(this).data('no_ipp')+"]</b>");
		$("#view2").load(base_url + active_controller+'/detail_adjustment/'+$(this).data('no_ipp')+'/'+$(this).data('users')+'/'+$(this).data('tanggal'));
		$("#ModalView2").modal();
	});

  $(document).on('click', '#modalDetail', function(e){
		e.preventDefault();
		var no_ipp 			= $('#no_ipp').val();

		if( no_ipp == '0'){
			swal({
			  title	: "Error Message!",
			  text	: 'PO Number Not Select, please input first ...',
			  type	: "warning"
			});
			$('#modalDetail').prop('disabled',false);
			return false;
		}

		var no_ipp 			= $('#no_ipp').val();
		var gudang_before 	= $('#gudang_before').val();
		$("#head_title2").html("<b>TOTAL MATERIAL PURCHASE</b>");
		$("#view2").load(base_url + active_controller+'/adjustment/'+no_ipp+'/'+gudang_before);
		$("#ModalView2").modal();
	});

	$(document).on('click', '#moveGudang', function(e){
		e.preventDefault();
		var gudang_before 	= $('#gudang_before').val();
		var gudang_after 	= $('#gudang_after').val();

		if( gudang_before == '0'){
			swal({
			  title	: "Error Message!",
			  text	: 'Origin Warehouse Not Select, please input first ...',
			  type	: "warning"
			});
			$('#moveGudang').prop('disabled',false);
			return false;
		}

		if( gudang_after == '0'){
			swal({
			  title	: "Error Message!",
			  text	: 'Destination Warehouse Not Select, please input first ...',
			  type	: "warning"
			});
			$('#moveGudang').prop('disabled',false);
			return false;
		}

		loading_spinner();
		$("#head_title2").html("<b>MOVE TO SUB WAREHOUSE</b>");
		$("#view2").load(base_url +'index.php/'+ active_controller+'/modalMoveGudang/'+gudang_before+'/'+gudang_after);
		$("#ModalView2").modal();
	});

	$(document).on('click', '#processAjust', function(){
		var gudang_before 	= $('#gudang_before').val();
		var no_ipp 			= $('#no_ipp').val();
		var gudang_after 	= $('#gudang_after').val();

		if( no_ipp == '0'){
			swal({
			  title	: "Error Message!",
			  text	: 'IPP Not Select, please input first ...',
			  type	: "warning"
			});
			$('#processAjust').prop('disabled',false);
			return false;
		}

		if( gudang_before == '0'){
			swal({
			  title	: "Error Message!",
			  text	: 'Origin Warehouse Not Select, please input first ...',
			  type	: "warning"
			});
			$('#processAjust').prop('disabled',false);
			return false;
		}

		if( gudang_after == '0'){
			swal({
			  title	: "Error Message!",
			  text	: 'Destination Warehouse Not Select, please input first ...',
			  type	: "warning"
			});
			$('#processAjust').prop('disabled',false);
			return false;
		}

		swal({
			title: "Are you sure?",
			text: "You will not be able to process again this data!",
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
				loading_spinner();
				var formData  	= new FormData($('#form_proses_bro')[0]);
				$.ajax({
					url			: base_url+'index.php/'+active_controller+'/processAjust',
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
							window.location.href = base_url + active_controller+'/material_adjustment';
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

	$(document).on('click', '#saveINMaterial', function(){
    // var max_no = $("#max_no").val();
    // var a;
    // alert(max_no);
    // for(a=1;a<=max_no; a++ ){
    //   var inx     = $(".qty_in_"+a).val().split(",").join("");
    //   var sebelum = $(".qty_sebelum_"+a).val().split(",").join("");
    //   console.log(inx);
    //   console.log(sebelum);
    //   if(inx > sebelum){
  	// 		swal({
  	// 		  title	: "Error Message!",
  	// 		  text	: 'Tidak boleh lebih dari sisa PO, please input first ...',
  	// 		  type	: "warning"
  	// 		});
  	// 		return false;
  	// 	}
    // }
		swal({
			title: "Are you sure?",
			text: "You will not be able to process again this data!",
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
				var formData  	= new FormData($('#form_adjustment')[0]);
				$.ajax({
					url			: base_url+active_controller+'/in_material',
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
							window.location.href = base_url + active_controller+'/incoming';
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

	$(document).on('click', '#moveMat', function(){

		swal({
			title: "Are you sure?",
			text: "You will not be able to process again this data!",
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
				loading_spinner();
				var formData  	= new FormData($('#form_move')[0]);
				$.ajax({
					url			: base_url+'index.php/'+active_controller+'/moveMaterial',
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
							window.location.href = base_url + active_controller+'/material_adjustment';
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
			"aaSorting": [[ 1, "asc" ]],
			"columnDefs": [ {
				"targets": 'no-sort',
				"orderable": false,
			}],
			"sPaginationType": "simple_numbers",
			"iDisplayLength": 10,
			"aLengthMenu": [[10, 20, 50, 100, 150], [10, 20, 50, 100, 150]],
			"ajax":{
				url : base_url +'index.php/'+active_controller+'/data_side_incoming',
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

	function DataTables2(gudang1 = null){
		var dataTable = $('#my-grid2').DataTable({
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
				"sEmptyTable": "Stock data is incomplete, please complete...",
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
				url : base_url +'index.php/'+active_controller+'/getJSONMoveGudang',
				type: "post",
				data: function(d){
					d.gudang1 = gudang1
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
