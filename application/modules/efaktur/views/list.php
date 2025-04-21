<?php
    $ENABLE_ADD     = has_permission('Efaktur.Add');
    $ENABLE_MANAGE  = has_permission('Efaktur.Manage');
    $ENABLE_VIEW    = has_permission('Efaktur.View');
    $ENABLE_DELETE  = has_permission('Efaktur.Delete');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<form action="<?= site_url(strtolower($this->uri->segment(1).'/index'))?>" method="POST" id='form_proses'>
	<div class="box">
		 <div class="box-header">
			<?php if ($ENABLE_ADD) : ?>
				<button type="button" class='btn btn-md btn-success' id='btn-add'><i class="fa fa-plus"></i> Export E-Faktur</button>
								
			<?php endif; ?>

			
		</div>
		<div class="box-body">
			<div class="form-group row">
				<label class="control-label col-sm-2">Periode Awal</label>
				<div class="col-sm-3">
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-calendar"></i></span>              
						<?php
							echo form_input(array('id'=>'tgl_awal','name'=>'tgl_awal','class'=>'form-control input-sm','autocomplete'=>'off','placeholder'=>'Tanggal','readOnly'=>true),$tgl_awal);											
						?>
					</div>
				</div>
				<label class="control-label col-sm-2">Periode Akhir</label>
				<div class="col-sm-3">
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-calendar"></i></span>              
						<?php
							echo form_input(array('id'=>'tgl_akhir','name'=>'tgl_akhir','class'=>'form-control input-sm','autocomplete'=>'off','placeholder'=>'Tanggal','readOnly'=>true),$tgl_akhir);											
						?>
					</div>
				</div>
				<div class="col-sm-2">					           
					<?php
						echo form_button(array('type'=>'submit','class'=>'btn btn-md btn-primary','value'=>'approve','content'=>'PREVIEW','id'=>'simpan_bro_add')).' ';										
					?>
					
				</div>
			</div>
			<table id="example1" class="table table-bordered table-striped">
				<thead>
					<tr class="bg-blue">
					  <th width="2%" class="text-center">#</th>
					  <th width="30%" class="text-center">No Export</th>
					  <th class="text-center">Tanggal Export</th>
					  <th class="text-center">Jam Export</th>
					  <th class="text-center">Aksi</th>
					</tr>
				</thead>
				<tbody>
				<?php
				$n=0;
				if(@$results){
					foreach(@$results as $kr=>$vr){
					$n++;
				?>
					<tr>		
					  <td class='text-center'><?php echo $n?></td>
					  <td class='text-left'><?php echo $vr['id_export']?></td>
					  <td class='text-center'><?php echo date('d M Y',strtotime($vr['date_export']));?></td>
					  <td class='text-center'><?php echo substr($vr['time_export'],0,5)?></td>
					 
					  <td class='text-center'>
						<?php
						echo"<a href='#' onClick='view_data(\"".$vr['id_export']."\")' class='btn btn-sm btn-default' title='View Detail' data-role='qtip'><i class='fa fa-search'></i></a>";
						
						echo"&nbsp;&nbsp;<a href='".site_url(strtolower($this->uri->segment(1).'/export_csv/'.$vr['id_export']))."' target='_blank' class='btn btn-sm btn-success' title='Download CSV' data-role='qtip'><i class='fa fa-file'></i></a>";
						
						?>
					  
					  </td>
					  
					</tr>
				<?php 
					}
				} 
				?>
				</tbody>
			
		   </table>
		</div>
	</div>
 
<!-- Modal -->
<div class="modal fade" id="myViewModal" >
	<div class="modal-dialog" style="width:80%">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				
			</div>
			<div class="modal-body" id="det_view">
			
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>

<link type="text/css" rel="stylesheet" href="<?= base_url('assets/plugins/sweetalert/dist/sweetalert.css')?>">
<script src="<?= base_url('assets/plugins/sweetalert/dist/sweetalert.min.js')?>"></script>
<script src="<?= base_url('assets/dist/jquery.maskedinput.min.js')?>"></script>

<!-- page script -->
<script type="text/javascript">
	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';
    $(function() {
      var dataTable = $("#example1").DataTable();
	 
	  $('#tgl_awal, #tgl_akhir').datepicker({
          format: 'yyyy-mm-dd',
          todayHighlight: true,         
          autoclose: true
      });
	 
	  $('#btn-add').click(function(){
		  window.location.href = base_url + active_controller+'/list_outstanding';
	  });
	  
	 
    });
    function view_data(kode){		
		var baseurl=base_url + active_controller+'/view/'+kode; 
		$.ajax({
			url			: baseurl,
			type		: "get",			
			success		: function(data){
				$('#det_view').empty();
				$('#det_view').html(data);
				$("#detail_view").DataTable();
				$('#myViewModal').modal('show');
			}
		});	
		
		
    }
   
    
</script>