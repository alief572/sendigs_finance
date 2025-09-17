<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<form method="POST" id="form_proses">
<div class="nav-tabs-salesorder">
    <div class="tab-content">
        <div class="tab-pane active" id="salesorder">
            <div class="box box-primary">
                <div class="box-body">
					<div class="form-group">
						<label for="idcustomer_do" class="col-sm-4 control-label">No Export </font></label>
						<div class="col-sm-8" style="padding-top: 8px;">
							<?php echo ": AUTOMATIC";?>
							
						</div>
					</div>
					<div class="form-group">
						<label for="idcustomer" class="col-sm-4 control-label">Tgl  Export </font></label>
						<div class="col-sm-8" style="padding-top: 8px;">
							<?php 
							echo ": ".date('d F Y')?>
						</div>
					</div>
                </div>
            </div>
			
        </div>
    </div>
</div>
<div class="box box-default ">
    <div class="box-body">
        
        <table id="deliveryorderitem" class="table table-bordered table-striped" width="100%">
            <thead>
                 <tr class='bg-blue'>
                      <th class='text-center' width="2%">#</th>
					  <th class='text-center'>No Invoice</th>
					  <th class='text-center'>Nama Customer</th>
					  <th class='text-center'>Tanggal</th>
					  <th class='text-center'>Total</th>
					  <th class='text-center'>No Faktur</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $n = $i =0;
				if($records){
					foreach($records as $keys=>$vals){
						$i++;
						
						echo"<tr>";
							echo form_input(array('id'=>'det_do_'.$i,'name'=>'det_do['.$i.']','class'=>'form-control input-sm','type'=>'hidden'),$vals['no_invoice']);
							echo"<td class='text-center'>".$i."</td>";
							echo"<td class='text-center'>".$vals['no_invoice']."</td>";
							echo"<td class='text-left'>".$vals['nm_customer']."</td>";
							echo"<td class='text-center'>".date('d M Y',strtotime($vals['tanggal_invoice']))."</td>";
							echo"<td class='text-right'>".number_format($vals['hargajualtotal'])."</td>";
							echo"<td class='text-left'>".$vals['nofakturpajak']."</td>";
							
						echo"</tr>";
						
					}
				}
                ?>
            </tbody>
            
        </table>
       
    </div>
</div>
<div class="text-right">
  <div class="box active">
    <div class="box-body">
        <button class="btn btn-danger" id="kembali_inv" type="button">
           <i class="fa fa-refresh"></i><b> Kembali</b>
        </button>
        <button class="btn btn-primary" type="button" id="proses_inv">
            <i class="fa fa-save"></i><b> Simpan Data E-Faktur</b>
        </button>
    </div>
  </div>
</div>
 </form>
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/sweetalert/dist/sweetalert.min.js')?>"></script>
<script src="<?= base_url('assets/dist/jquery.maskedinput.min.js')?>"></script>

<script type="text/javascript">
	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';
    $(document).ready(function(){
        
        var dataTableItem = $('#deliveryorderitem').DataTable({
            "sDom": 'Bfrtip',
            "responsive": true,
            "bInfo":false,
            "bPaginate":false,
            "bFilter":false,
            "processing": false,
        });
		$('#kembali_inv').click(function(){
			 window.location.href = base_url + active_controller+'/list_outstanding';
		});
		$('#proses_inv').click(function(e){
			  e.preventDefault();
			  swal({
					  title: "Are you sure?",
					  text: "You will not be able to process again this data!",
					  type: "warning",
					  showCancelButton: true,
					  confirmButtonClass: "btn-danger",
					  confirmButtonText: "Yes, Process it!",
					  cancelButtonText: "No, cancel process!",
					  closeOnConfirm: false,
					  closeOnCancel: false,
					  showLoaderOnConfirm: true
					},
					function(isConfirm) {
					  if (isConfirm) {
							
							var formData 	=new FormData($('#form_proses')[0]);
							var baseurl=base_url + active_controller +'/add';
							$.ajax({
								url			: baseurl,
								type		: "POST",
								data		: formData,
								cache		: false,
								dataType	: 'json',
								processData	: false, 
								contentType	: false,				
								success		: function(data){
									
									var kode_bast	= data.kode;
									if(data.status == 1){											
										swal({
											  title	: "Save Success!",
											  text	: data.pesan,
											  type	: "success",
											  timer	: 15000,
											  showCancelButton	: false,
											  showConfirmButton	: false,
											  allowOutsideClick	: false
											});
										window.location.href = base_url + active_controller;
									}else{
										
										if(data.status == 2){
											swal({
											  title	: "Save Failed!",
											  text	: data.pesan,
											  type	: "danger",
											  timer	: 10000,
											  showCancelButton	: false,
											  showConfirmButton	: false,
											  allowOutsideClick	: false
											});
										}else{
											swal({
											  title	: "Save Failed!",
											  text	: data.pesan,
											  type	: "warning",
											  timer	: 10000,
											  showCancelButton	: false,
											  showConfirmButton	: false,
											  allowOutsideClick	: false
											});
										}
										
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
    });
   
    
</script>
