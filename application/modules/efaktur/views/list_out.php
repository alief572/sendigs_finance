<?php
    $ENABLE_ADD     = has_permission('Efaktur.Add');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<form action="<?= site_url(strtolower($this->uri->segment(1).'/create'))?>" method="POST" id='form_proses'>
<div class="box">
	<div class="box-body">
        <table id="example1" class="table table-bordered table-striped">
        <thead>
	        <tr class='bg-blue'>
	          <th class='text-center' width="2%">#</th>
              <th class='text-center'>No Invoice</th>
	          <th class='text-center'>Nama Customer</th>
              <th class='text-center'>Tanggal</th>
              <th class='text-center'>Total</th>
              <th class='text-center'>No Faktur</th>
	          <th class='text-center'>Aksi</th>
	        </tr>
        </thead>
        <tbody id='list_detail'>
          <?php if(@$results){ ?>
            <?php 
            $n = 1;
			
            foreach(@$results as $kso=>$vso){ 
                $no = $n++;
            ?>
            <tr>
              <td><center><?php echo $no?></center></td>
              <td><?php echo $vso['no_invoice']?></td>
              <td><?php echo $vso['nm_customer']?></td>
              <td class="text-center"><?php echo date('d/m/Y',strtotime($vso['tanggal_invoice']))?></td>
              <td class="text-right"><?php echo number_format($vso['hargajualtotal'])?></td>
              <td><?php echo $vso['nofakturpajak']?></td>              
              <td class="text-center">
                <input type="checkbox" name="set_choose_invoice[<?php echo $no;?>]" id="set_choose_invoice" value="<?php echo $vso['no_invoice']?>">
              </td>
            </tr>
            <?php } ?>
          <?php } ?>
        </tbody>
        
        </table>
    </div>
	<div class="box-footer">
		<button class="btn btn-primary" id="btn-proses-do" type="button"> Proses E-Faktur</button>&nbsp;&nbsp;<button class="btn btn-danger" id="btn-proses-back" type="button"> Kembali</button>
	</div>
</div>

</form>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>
<link type="text/css" rel="stylesheet" href="<?= base_url('assets/plugins/sweetalert/dist/sweetalert.css')?>">
<script src="<?= base_url('assets/plugins/sweetalert/dist/sweetalert.min.js')?>"></script>
<!-- page script -->
<script type="text/javascript">
	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';
    $(document).ready(function(){
      
      $("#example1").DataTable();
	 
	  $('#btn-proses-do').click(function(e){
			e.preventDefault();
			
			var ints		=0;
			$('#list_detail').find('input[type="checkbox"]').each(function(){
				if($(this).is(':checked')){
					ints++;
				}
			});
			
			if(ints==0){
				swal({
				  title: "Error Message!",
				  text: 'No Record Was  Selected. Please Choose At Least One Record....',
				  type: "warning",								  
				  timer: 5000
				});
				
				return false;
			}
			var links	= base_url+active_controller+'/proses';
			$('#form_proses').attr('action',links);
			$('#form_proses').submit();
		
	  });
	  $('#btn-proses-back').click(function(){
		 window.location =  base_url+active_controller;
	  });
    });
    
</script>