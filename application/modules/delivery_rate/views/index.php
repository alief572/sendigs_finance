<?php
    $ENABLE_ADD     = has_permission('Machine_Rate.Add');
    $ENABLE_MANAGE  = has_permission('Machine_Rate.Manage');
    $ENABLE_VIEW    = has_permission('Machine_Rate.View');
    $ENABLE_DELETE  = has_permission('Machine_Rate.Delete');
?>
<style type="text/css">
thead input {
	width: 100%;
}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">

<div class="box">
	<div class="box-header">
			<?php if($ENABLE_ADD) : ?>
				<a class="btn btn-success btn-sm add" href="javascript:void(0)" title="Add"><i class="fa fa-plus">&nbsp;</i>Add</a>
			<?php endif; ?>

		<span class="pull-right">
		</span>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
		<thead>
		<tr>
			<th>#</th>
			<th>Type</th>
			<th>Country</th>
			<th>Category/Method</th>
			<th>Type</th>
			<th>Area</th>
			<th>Price IDR</th>
			<th>Kurs</th>
			<th>Price USD</th>
			<th>Action</th>
		</tr>
		</thead>

		<tbody>
		<?php if(empty($result)){
		}else{
			$numb=0; foreach($result AS $record){ $numb++;
				$Category = ($record->type == 'local')?$record->category:$record->shipping_method;
				$nama_negara = (!empty($country[$record->id_country]['nama']))?$country[$record->id_country]['nama']:'';
				$Country = ($record->type == 'export')?$nama_negara:'indonesia';

				$area_tujuan = ($record->type == 'export')?$nama_negara:$record->area_tujuan;
			?>
		<tr>
		    <td><?= $numb; ?></td>
			<td><?= strtoupper($record->type) ?></td>
			<td><?= strtoupper($Country) ?></td>
			<td><?= strtoupper($Category) ?></td>
			<td><?= strtoupper($record->transport_type) ?></td>
			<td><?= strtoupper($area_tujuan) ?></td>
			<td class='text-right'><?= number_format($record->price) ?></td>
			<td class='text-right'><?= number_format($record->kurs) ?></td>
			<td class='text-right'><?= number_format($record->price_usd,2) ?></td>

			<td>

			<?php if($ENABLE_MANAGE) : ?>
				<a class="btn btn-primary btn-sm edit" href="javascript:void(0)" title="Edit" data-id="<?=$record->id?>"><i class="fa fa-edit"></i>
				</a>
			<?php endif; ?>

			<?php if($ENABLE_DELETE) : ?>
				<a class="btn btn-danger btn-sm delete" href="javascript:void(0)" title="Delete" data-id="<?=$record->id?>"><i class="fa fa-trash"></i>
				</a>
			<?php endif; ?>

			<?php if($ENABLE_MANAGE) : ?>
				<a class="btn btn-warning btn-sm updatekurs" href="javascript:void(0)" title="Update Kurs" data-id="<?=$record->id?>">Update Kurs</i>
				</a>
			<?php endif; ?>
			</td>

		</tr>
		<?php } }  ?>
		</tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>

<!-- awal untuk modal dialog -->

<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="head_title">Default</h4>
      </div>
      <div class="modal-body" id="ModalView">
		...
      </div>
  </div>
</div>

<div class="modal modal-default fade" id="dialog-country" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="head_title-country">Add Country</h4>
      </div>
      <div class="modal-body" id="ModalView-country">
		...
      </div>
  </div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>

<!-- page script -->
<script type="text/javascript">

	$(document).on('click', '.edit', function(e){
		var id = $(this).data('id');
		$("#head_title").html("<b>Delivery Rate</b>");
		$.ajax({
			type:'POST',
			url:siteurl+active_controller+'/add/'+id,
			success:function(data){
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});

	$(document).on('click', '.add', function(){
		$("#head_title").html("<b>Delivery Rate</b>");
		$.ajax({
			type:'POST',
			url:siteurl+active_controller+'/add/',
			success:function(data){
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});

	$(document).on('click', '#addCountry', function(){
		$.ajax({
			type:'POST',
			url:siteurl+active_controller+'/add_country',
			success:function(data){
				$("#dialog-country").modal();
				$("#ModalView-country").html(data);

			}
		})
	});

	$(document).on('submit', '#data_form', function(e){
		e.preventDefault()
		
		var type = $('#type').val();

		if(type == 'local' ){
			
			var category = $('#category').val();

			if(category == '0' ){
				swal({title	: "Error Message!",text	: 'Delivery Category not selected...',type	: "warning"
				});
				return false;
			}

		}
		else{
			var id_country = $('#id_country').val();
			var shipping_method = $('#shipping_method').val();

			if(id_country == '0' ){
				swal({title	: "Error Message!",text	: 'Country not selected...',type	: "warning"
				});
				return false;
			}
			if(shipping_method == '0' ){
				swal({title	: "Error Message!",text	: 'Shipping Method not selected...',type	: "warning"
				});
				return false;
			}
			
		}

		var price = $('#price').val();

		if(price == '' ){
			swal({title	: "Error Message!",text	: 'Price is empty...',type	: "warning"
			});
			return false;
		}
		// alert(data);

		swal({
		  title: "Anda Yakin?",
		  text: "Data akan diproses!",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonClass: "btn-info",
		  confirmButtonText: "Yes",
		  cancelButtonText: "No",
		  closeOnConfirm: false
		},
		function(){
			// var form_data = $('#data_form').serialize();
			var form_data = new FormData($('#data_form')[0]);
		  $.ajax({
			  type:'POST',
			  url:siteurl + active_controller+'add',
			  dataType : "json",
			  data:form_data,
			  processData: false,
        		contentType: false,
			  success:function(data){
				  if(data.status == '1'){
					 swal({
						  title: "Sukses",
						  text : data.pesan,
						  type : "success"
						},
						function (){
							window.location.reload(true);
						})
				  } else {
					swal({
					  title : "Error",
					  text  : data.pesan,
					  type  : "error"
					})

				  }
			  },
			  error : function(){
				swal({
					  title : "Error",
					  text  : "Error proccess !",
					  type  : "error"
					})
			  }
		  })
		});

	})

	// DELETE DATA
	$(document).on('click', '.delete', function(e){
		e.preventDefault()
		var id = $(this).data('id');
		// alert(id);
		swal({
		  title: "Anda Yakin?",
		  text: "Data akan di hapus!",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonClass: "btn-info",
		  confirmButtonText: "Yes",
		  cancelButtonText: "No",
		  closeOnConfirm: false
		},
		function(){
		  $.ajax({
			  type:'POST',
			  url:siteurl+active_controller+'/delete',
			  dataType : "json",
			  data:{'id':id},
			  success:function(data){
				  if(data.status == '1'){
					 swal({
						  title: "Sukses",
						  text : data.pesan,
						  type : "success"
						},
						function (){
							window.location.reload(true);
						})
				  } else {
					swal({
					  title : "Error",
					  text  : data.pesan,
					  type  : "error"
					})

				  }
			  },
			  error : function(){
				swal({
					  title : "Error",
					  text  : "Error proccess !",
					  type  : "error"
					})
			  }
		  })
		});

	})

	$(document).on('click', '#save_country', function(e){
		e.preventDefault()
		var country_code = $('#country_code').val();

		if(country_code == '0' ){
			swal({title	: "Error Message!",text	: 'Country not selected...',type	: "warning"
			});
			return false;
		}
		// alert(id);
		swal({
		  title: "Anda Yakin?",
		  text: "Tambahkan Negara!",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonClass: "btn-info",
		  confirmButtonText: "Yes",
		  cancelButtonText: "No",
		  closeOnConfirm: false
		},
		function(){
		  $.ajax({
			  type:'POST',
			  url:siteurl+active_controller+'/add_country',
			  dataType : "json",
			  data:{'country_code':country_code},
			  success:function(data){
				  if(data.status == '1'){
					 swal({
						  title: "Sukses",
						  text : data.pesan,
						  type : "success"
						},
						function (){
							window.location.reload(true);
						})
				  } else {
					swal({
					  title : "Error",
					  text  : data.pesan,
					  type  : "error"
					})

				  }
			  },
			  error : function(){
				swal({
					  title : "Error",
					  text  : "Error proccess !",
					  type  : "error"
					})
			  }
		  })
		});

	})

	$(document).on('click', '.updatekurs', function(e){
		e.preventDefault();
		var id = $(this).data('id')
		swal({
				title: "Are you sure?",
				text: "Update KURS!",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-danger",
				confirmButtonText: "Yes, Process it!",
				cancelButtonText: "No, cancel process!",
				closeOnConfirm: true,
				closeOnCancel: false
			},
			function(isConfirm) {
				if (isConfirm) {
					var baseurl = base_url+active_controller+'/update_kurs'
					$.ajax({
						url			: baseurl,
						type		: "POST",
						data 		: {
							'id' : id
						},
						cache		: false,
						dataType	: 'json',
						success		: function(data){
							if(data.status == 1){
								swal({
										title	: "Save Success!",
										text	: data.pesan,
										type	: "success",
										timer	: 3000
								});
								window.location.href = base_url + active_controller
							}else{

								if(data.status == 2){
									swal({
										title	: "Save Failed!",
										text	: data.pesan,
										type	: "warning",
										timer	: 3000,
									});
								}else{
									swal({
										title	: "Save Failed!",
										text	: data.pesan,
										type	: "warning",
										timer	: 3000,
									});
								}

							}
						},
						error: function() {

							swal({
								title				: "Error Message !",
								text				: 'An Error Occured During Process. Please try again..',
								type				: "warning",
								timer				: 3000,
							});
						}
					});
				} else {
				swal("Cancelled", "Data can be process again :)", "error");
				return false;
				}
		});
	});

  	$(function() {
	    var table = $('#example1').DataTable( {
	        orderCellsTop: true,
	        fixedHeader: true
	    } );
    	$("#form-area").hide();
  	});
</script>
