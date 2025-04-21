<?php
$this->load->view('include/side_menu');	
?>
<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data"> 
	<div class="box box-primary">
		<div class="box-header">
			<h3 class="box-title"><?php echo $title;?></h3>
		</div>
		<div class="box-body table-responsive">
			<table class="table table-bordered table-striped" id="example1" width='100%'> 
				<thead>
					<tr class='bg-blue'>
						<th class="text-center" width='4%'>No</th>
						<th width='7%'>Tgl Penerimaan</th>
						<th width='7%'>Kode Penerimaan</th>
						<th width='7%'>Nama Customer</th>
						<th width='18%'>Keterangan</th>
						<th width='7%'>No Invoice</th>
						<th class="text-right" width='7%'>Total Invoice</th>
						<th class="text-right">PPH</th>
						<th class="text-right">Biaya Admin</th>
						<th class="text-right" width='7%'>Total Penerimaan <br> (IDR)</th>
						<th class="text-right" width='7%'>Total Penerimaan <br> (USD)</th>
						<th class="text-center" width='7%'>Option</th>
					</tr>
				</thead>
				<tbody>
		<?php if(empty($results)){
		}else{
			
			$numb=0; foreach($results AS $record){ $numb++; 

				$tanggal = $record->tgl_pembayaran;
				
				$kurs = $record->kurs_bayar;
				if($kurs > 0){
					$idr = $record->jumlah_pembayaran;
				}else{
					$idr = 0;
				}
					
				
				
			?>
		<tr>
		    <td><?= $numb; ?></td>
			<td><?=  date('d-F-Y', strtotime($record->tgl_pembayaran)) ?></td>
			<td><?= $record->kd_pembayaran ?></td>			
			<td><?= $record->nm_customer ?></td>
			<td><?= $record->keterangan ?></td>
			<td><?= $record->invoiced ?></td>
			<td align="right"><?= number_format($record->totalinvoiced) ?></td>
			<td align="right"><?= number_format($record->biaya_pph_idr) ?></td>
			<td align="right"><?= number_format($record->biaya_admin_idr) ?></td>
			<td align="right"><?= number_format($record->jumlah_pembayaran_idr) ?></td>
			<td align="right"><?= number_format($idr,2) ?></td>
         	<td style="padding-left:20px">
			<button class='btn btn-sm btn-warning detail' title='View' data-id_bq='<?=$record->kd_pembayaran?>'><i class='fa fa-eye'></i></button>
			<a href="<?=base_url('penerimaan/modal_detail_invoice_draf/'.$record->kd_pembayaran)?>" class='btn btn-sm btn-default' title='Create Penerimaan'target="_blank"><i class='fa fa-edit'></i></a>
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
				  // $cust = $inv->nm_customer;
                  // $invoice = $this->db->query("SELECT * FROM tr_unlocated_bank WHERE saldo !=0 ")->result();                  				  
				  // if($invoice){
					// foreach($invoice as $ks=>$vs){
                  ?>
						  <!-- <tr>
							  // <td><?php echo $vs->tgl ?></td>
							  // <td><center><?php echo $vs->keterangan ?></center></td>
							  // <td><center><?php echo number_format($vs->totalpenerimaan) ?></center></td>
							  // <td><center><?php echo $vs->bank ?></center></td>							 
						  // </tr>-->
                  <?php 
						// }
					  // }				  
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

<!-- DataTables -->
<?php $this->load->view('include/footer'); ?>

<script>
	$(document).ready(function(){
		$('#spinnerx').hide();
		$("#example1").DataTable();
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
			url: base_url + active_controller+'/view_penerimaan_draf/'+$(this).data('id_bq'),
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
	
	
	function add_inv(){ 
        window.location.href = base_url + active_controller +'/modal_detail_invoice'; 
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
