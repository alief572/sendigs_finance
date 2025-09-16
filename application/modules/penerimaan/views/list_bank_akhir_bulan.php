<?php
$this->load->view('include/side_menu');	
?>

	<div class="box box-primary">
		<div class="myDiv">
			<form method="post" action="<?= base_url() ?>index.php/penerimaan/update_bank" autocomplete="off">
				<div class="row">
					<div class="col-sm-10">
						<div class="col-sm-2">
							<div class="form-group">
								<br>
								<label>Tanggal</label>
								 <input type="date" name="tgl_update" id="tgl_update" class="form-control input-sm" value="<?php echo date('Y-m-d') ?>" >
							</div>
						</div>
						<div class="col-sm-2">
							<div class="form-group">
								<br>
								<label>Kurs</label>
								 <input type="text" name="kurs" id="kurs" class="form-control input-sm divide">
							</div>
						</div>
						<div class="col-sm-5">
							<div class="form-group">
								<br>
								<label> &nbsp;</label><br>
								<input type="submit" name="tampilkan" value="Update Kurs Akhir Bulan" onclick="return check()" class="btn warnaTombol pull-center"> &nbsp;
								</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
	

	<div class="box box-primary">
		<div class="box-header">
			
		</div>
		<div class="box-body">
			<table class="table table-bordered table-striped" id="example1" width='100%'> 
				<thead>
					<tr class='bg-blue'>
						<th class="text-center" width='4%'>#</th>
						<th class="text-center" width='8%'>Tgl Terakhir</th>
						<th class="text-center" width='8%'>COA Bank</th>
						<th class="text-center" width='12%'>Kurs Lama</th>
						<th class="text-center" width='12%'>Saldo Awal (USD)</th>
						<th class="text-center" width='12%'>Saldo Awal (IDR)</th>
						<th class="text-center" width='12%'>Kurs Transaksi</th>
						<th class="text-center" width='12%'>Transaksi(USD)</th>
						<th class="text-center" width='12%'>Transaksi(IDR)</th>
						<th class="text-center" width='12%'>Saldo Akhir(USD)</th>
						<th class="text-center" width='12%'>Saldo Akhir(IDR)</th>						
					</tr>
				</thead>
				<tbody>
		<?php if(empty($results)){
		}else{
			
			$numb=0; foreach($results AS $record){ $numb++; 

							
				
			?>
		<tr>
		    <td><?= $numb; ?></td>
			<td><?=  date('d-F-Y', strtotime($record->tgl_transaksi)) ?></td>
			<td><?= $record->kd_bank ?></td>			
			<td align="right"><?= number_format($record->kurs_lama) ?></td>
			<td align="right"><?= number_format($record->saldo_lama) ?></td>
			<td align="right"><?= number_format($record->saldo_lama_idr) ?></td>
			<td align="right"><?= number_format($record->kurs_baru) ?></td>
			<td align="right"><?= number_format($record->saldo_baru) ?></td>
			<td align="right"><?= number_format($record->saldo_baru_idr) ?></td>
			<td align="right"><?= number_format($record->saldo_akhir) ?></td>
			<td align="right"><?= number_format($record->saldo_akhir_idr) ?></td>   	
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


<!-- DataTables -->
<?php $this->load->view('include/footer'); ?>

<script>

	$(document).ready(function(){
		$('#spinnerx').hide();
		$("#example1").DataTable();
		$(".divide").divide();
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
			url: base_url + active_controller+'/view_penerimaan/'+$(this).data('id_bq'),
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
			    window.location.href = base_url + active_controller +'create_jurnal_akhir_bulan'; 
				
			} 
			else {
			swal("Cancelled", "Data can be process again :)", "error");
			return false;
			}		
		});
       
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
