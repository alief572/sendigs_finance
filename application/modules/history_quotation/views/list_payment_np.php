<?php
    $ENABLE_ADD     = has_permission('Penerimaan.Add');
    $ENABLE_MANAGE  = has_permission('Penerimaan.Manage');
    $ENABLE_VIEW    = has_permission('Penerimaan.View');
    $ENABLE_DELETE  = has_permission('Penerimaan.Delete');
?>
<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data"> 
	<div class="box box-primary">
		<div class="box-header">
			<h3 class="box-title"><?php echo $title;?></h3>
				<button class="btn btn-success" type="button" onclick="add_inv()">
					<i class="fa fa-plus"></i>Buat Penerimaan Non Produk
				</button>
				<!--<button class="btn btn-success" type="button" onclick="add_unlocated()">
					<i class="fa fa-plus"></i>Penerimaan Unlocated
				</button>
				<button class="btn btn-success" type="button" id="incomplete">
					<i class="fa fa-eye"></i>List Unlocated
				</button>-->
		</div>
		<div class="box-body">
			<table class="table table-bordered table-striped" id="example1" width='100%'> 
				<thead>
					<tr class='bg-blue'>
						<th class="text-center" width='4%'>No</th>
						<th width='7%'>Tgl Penerimaan</th>
						<th width='7%'>Kode Penerimaan</th>
						<th width='7%'>Nama Customer</th>
						<th width='18%'>Keterangan</th>
						<th width='18%'>Bank</th>
						<th class="text-right">PPH</th>
						<th class="text-right">Biaya Admin</th>
						<th class="text-right" width='7%'>Total Penerimaan</th>
						<th class="text-center" width='7%'>Option</th>
					</tr>
				</thead>
				<tbody>
		<?php if(empty($results)){
		}else{
			
			$numb=0; foreach($results AS $record){ $numb++; 

				$tanggal = $record->tgl_pembayaran;
				$coa = $record->kd_bank;
				
				$nmbank =$this->db->query("SELECT nama FROM gl_waterco.coa_master WHERE no_perkiraan='$coa'")->row()
			?>
		<tr>
		    <td><?= $numb; ?></td>
			<td><?=  date('d-F-Y', strtotime($record->tgl_pembayaran)) ?></td>
			<td><?= $record->kd_pembayaran ?></td>			
			<td><?= $record->nm_customer ?></td>
			<td><?= $record->keterangan ?></td>
			<td><?= $nmbank->nama ?></td>
			<td align="right"><?= number_format($record->biaya_pph_idr) ?></td>
			<td align="right"><?= number_format($record->biaya_admin_idr) ?></td>
			<td align="right"><?= number_format($record->jumlah_pembayaran_idr) ?></td>
         	<td style="padding-left:20px">
			<?php if($ENABLE_MANAGE) : ?>
				<button class='btn btn-sm btn-warning detail' title='View' data-id_bq='<?=$record->kd_pembayaran?>'><i class='fa fa-eye'></i></button>
				
			<?php endif; ?>
			
			</td>

		</tr>
		<?php } }  ?>
		</tbody>
			</table>
		</div>
		<!-- /.box-body -->
	</div>
	<!-- /.box -->	
		<!-- modal -->
		<div class="modal fade" id="ModalView">
			<div class="modal-dialog"  style='width:80%; '>
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="head_title"></h4>
						</div>
						<div class="modal-body" id="view">
						</div>
						<div class="modal-footer">
						<!--<button type="button" class="btn btn-primary">Save</button>-->
						<button type="button" class="btn btn-default " data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
		<!-- modal -->
		
	<div class="modal modal-primary" id="dialog-data-incomplete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><span class="fa fa-file-pdf-o"></span>&nbsp;Data Unlocated</h4>
      </div>
      <div class="modal-body" id="MyModalBodyUnlocated" style="background: #FFF !important;color:#000 !important;">
          <table class="table table-bordered" width="100%" id="list_item_unlocated">
              <thead>
                  <tr>
                     <th class="text-center">Tanggal</th>
					 <th class="text-center">Keterangan</th>
					 <th class="text-center">Total Penerimaan</th>
					 <th class="text-center">Bank</th>
                  </tr>
              </thead>
              <tbody>
                  <?php	
				  $cust = $inv->nm_customer;
                  $invoice = $this->db->query("SELECT * FROM tr_unlocated_bank WHERE saldo !=0 ")->result();                  				  
				  if($invoice){
					foreach($invoice as $ks=>$vs){
                  ?>
						  <tr>
							  <td><?php echo $vs->tgl ?></td>
							  <td><center><?php echo $vs->keterangan ?></center></td>
							  <td><center><?php echo number_format($vs->totalpenerimaan) ?></center></td>
							  <td><center><?php echo $vs->bank ?></center></td>							 
						  </tr>
                  <?php 
						}
					  }				  
				  ?>
              </tbody>
          </table>
       </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">
        <span class="glyphicon glyphicon-remove"></span>  Tutup</button>
        </div>
    </div>
  </div>
</div>
</form>
<script>
	$(document).ready(function(){
		$('#spinnerx').hide();
		DataTables();
	});
	$(document).on('click', '.buktip', function(e){
		e.preventDefault();
		$("#head_title").html("<b>Penerimaan Bukti Potong</b>");
		$.ajax({
			type:'POST',
			url: base_url + active_controller+'penerimaan_buktipotong/'+$(this).data('kd_pembayaran'),
			success:function(data){
				$("#ModalView").modal();
				$("#view").html(data);
			},
			error: function() {
				swal({
				  title				: "Error Message !",
				  text				: 'Connection Timed Out ...',
				  type				: "warning",
				  timer				: 5000,
				  showCancelButton	: false,
				  showConfirmButton	: false,
				  allowOutsideClick	: false
				});
			}
		});
	});	
	$(document).on('click', '.detail', function(e){
		e.preventDefault();
		$("#head_title").html("<b>VIEW PAYMENT ["+$(this).data('id_bq')+"]</b>");
		$.ajax({
			type:'POST',
			url: base_url + active_controller+'view_penerimaan/'+$(this).data('id_bq'),
			success:function(data){
				$("#ModalView").modal();
				$("#view").html(data);
			},
			error: function() {
				swal({
				  title				: "Error Message !",
				  text				: 'Connection Timed Out ...',
				  type				: "warning",
				  timer				: 5000,
				  showCancelButton	: false,
				  showConfirmButton	: false,
				  allowOutsideClick	: false
				});
			}
		});
	});
	// function DataTables(){
	// 	var dataTable = $('#my-grid').DataTable({
	// 		"serverSide": true,
	// 		"stateSave" : true,
	// 		"bAutoWidth": true,
	// 		"destroy": true,
	// 		"processing": true,
	// 		"responsive": true,
	// 		"fixedHeader": {
	// 			"header": true,
	// 			"footer": true
	// 		},
	// 		"oLanguage": {
	// 			"sSearch": "<b>Search : </b>",
	// 			"sLengthMenu": "_MENU_ &nbsp;&nbsp;<b>Records Per Page</b>&nbsp;&nbsp;",
	// 			"sInfo": "Showing _START_ to _END_ of _TOTAL_ entries",
	// 			"sInfoFiltered": "(filtered from _MAX_ total entries)",
	// 			"sZeroRecords": "No matching records found",
	// 			"sEmptyTable": "No data available in table",
	// 			"sLoadingRecords": "Please wait - loading...",
	// 			"oPaginate": {
	// 				"sPrevious": "Prev",
	// 				"sNext": "Next"
	// 			}
	// 		},
	// 		"aaSorting": [[ 1, "asc" ]],
	// 		"columnDefs": [ {
	// 			"targets": 'no-sort',
	// 			"orderable": false,
	// 		}],
	// 		"sPaginationType": "simple_numbers",
	// 		"iDisplayLength": 10,
	// 		"aLengthMenu": [[10, 20, 50, 100, 150], [10, 20, 50, 100, 150]],
	// 		"ajax":{
	// 			url: siteurl + active_controller + 'server_side_payment',
	// 			//url : base_url + active_controller+'/server_side_inv', 
	// 			type: "post",
	// 			data: function(d){
	// 				// d.kode_partner = $('#kode_partner').val()
	// 			},
	// 			cache: false,
	// 			error: function(){
	// 				$(".my-grid-error").html("");
	// 				$("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
	// 				$("#my-grid_processing").css("display","none");
	// 			}
	// 		}
	// 	});
	// }
	
	// function add_inv(){ 
        // window.location.href = base_url + active_controller +'create_new'; modal_detail_invoice
    // }
	
	function add_inv(){ 
        window.location.href = base_url + active_controller +'modal_detail_invoice_np'; 
    }
	
	function add_unlocated(){ 
        window.location.href = base_url + active_controller +'unlocated'; 
    }

	$(document).on('click', '.print', function(e){
		e.preventDefault();
		var invoice = $(this).data('inv');
		// alert(invoice); return false;
		swal({
		   title: "Yakin Akan Diproses?",
		  text: "Data tidak bisa dirubah lagi !!",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonClass: "btn-danger",
		  confirmButtonText: "Ya, Proses!",
		  cancelButtonText: "Tidak, Batalkan!",
		  closeOnConfirm: true,
		  closeOnCancel: false
		},
		function(isConfirm) {
			if (isConfirm) {
				$('#spinnerx').show();
				window.open(base_url + active_controller+'print_invoice_fix/'+invoice);
				
			} 
			else {
			swal("Cancelled", "Data can be process again :)", "error");
			return false;
			}		
		});		
	});	
	$(document).on('click', '.print1', function(e){
		e.preventDefault();
		var invoice = $(this).data('inv');
		// alert(invoice); return false;
		swal({
		   title: "Yakin Akan Diproses?",
		  text: "Data tidak bisa dirubah lagi !!",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonClass: "btn-danger",
		  confirmButtonText: "Ya, Proses!",
		  cancelButtonText: "Tidak, Batalkan!",
		  closeOnConfirm: true,
		  closeOnCancel: false
		},
		function(isConfirm) {
			if (isConfirm) {
				$('#spinnerx').show();
				window.open(base_url + active_controller+'print_invoice_np_fix/'+invoice);
			} 
			else {
			swal("Cancelled", "Data can be process again :)", "error");
			return false;
			}		
		});		
	});	 
	$(document).on('click', '.print2', function(e){
		e.preventDefault();
		var invoice = $(this).data('inv');
		// alert(invoice); return false;
		swal({
		   title: "Yakin Akan Diproses?",
		  text: "Data tidak bisa dirubah lagi !!",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonClass: "btn-danger",
		  confirmButtonText: "Ya, Proses!",
		  cancelButtonText: "Tidak, Batalkan!",
		  closeOnConfirm: true,
		  closeOnCancel: false
		},
		function(isConfirm) {
			if (isConfirm) {
				$('#spinnerx').show();
				window.open(base_url + active_controller+'print_invoice_np_fix/'+invoice);
			} 
			else {
			swal("Cancelled", "Data can be process again :)", "error");
			return false;
			}		
		});	
	});
	$(document).on('click', '.jurnal', function(e){
		e.preventDefault();
		var invoice = $(this).data('inv');
		// alert(invoice); return false;
		swal({
		   title: "Yakin Akan Diproses?",
		  text: "Data tidak bisa dirubah lagi !!",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonClass: "btn-danger",
		  confirmButtonText: "Ya, Proses!",
		  cancelButtonText: "Tidak, Batalkan!",
		  closeOnConfirm: true,
		  closeOnCancel: false
		},
		function(isConfirm) {
			if (isConfirm) {
				$('#spinnerx').show();
				window.open(base_url + active_controller+'appr_jurnal/'+invoice);
				location.reload();				
			} else {
			swal("Cancelled", "Data can be process again :)", "error");
			return false;
			}
		});
	});
	$(document).on('click', '.edit', function(e){
		e.preventDefault();
		$("#head_title").html("<b>EDIT INVOICE ["+$(this).data('inv')+"]</b>"); 
		$.ajax({
			type:'POST',
			url: base_url +'invoicing/edit_invoice/'+$(this).data('inv'),
			success:function(data){
				$("#ModalView").modal();
				$("#view").html(data);
			},
			error: function() {
				swal({
				  title				: "Error Message !",
				  text				: 'Connection Timed Out ...',
				  type				: "warning",
				  timer				: 5000,
				  showCancelButton	: false,
				  showConfirmButton	: false,
				  allowOutsideClick	: false
				});
			}
		});
	});

	$(document).on('click', '.terima', function(e){
		e.preventDefault();	
		window.location.href = base_url +'penerimaan/modal_detail_invoice/'+$(this).data('inv');
	});
	
	$("#incomplete").click(function(){
		$('#dialog-data-incomplete').modal('show');
//        $("#list_item_stok").DataTable({lengthMenu:[10,15,25,30]}).draw();		
	});

</script>
