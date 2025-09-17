<?php
    $ENABLE_ADD     = has_permission('kendaraan.Add');
    $ENABLE_MANAGE  = has_permission('kendaraan.Manage');
    $ENABLE_VIEW    = has_permission('kendaraan.View');
    $ENABLE_DELETE  = has_permission('kendaraan.Delete');
?>


<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">

<div class="box">
	<div class="box-header">
			<?php if ($ENABLE_ADD) : ?>
			<a class="btn btn-success" href="javascript:void(0)" title="Add" onclick="add_data()"><i class="fa fa-plus">&nbsp;</i>New</a>
			<?php endif; ?>
		<span class="pull-right">
				<?php //echo anchor(site_url('customer/downloadExcel'), ' <i class="fa fa-download"></i> Excel ', 'class="btn btn-primary btn-sm"'); ?>
		</span>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
		<thead>
		<tr>
			<th width="1%">#</th>
			<th>Model Kendaraan</th>
			<th>Nomor Kendaraan</th>
			<th>Nomor Rangka</th>
			<th>STNK Expired</th>
			<th>KEUR Expired</th>
			<th width="8%">Aksi</th>
		</tr>
		</thead>

		<tbody>
		<?php
		if(@$results){
			$n=1;
			foreach(@$results as $kd=>$vd){
				$no=$n++;
		?>
			<tr>
				<td><center><?php echo $no?></center></td>
				<td><?php echo $vd->model?></td>
				<td><center><?php echo $vd->nm_kendaraan?></center></td>
				<td><center><?php echo $vd->no_rangka?></center></td>
				<td><center><?php echo date('d-M-Y',strtotime($vd->stnk_expired))?></center></td>
				<td><center><?php echo date('d-M-Y',strtotime($vd->keur_expired))?></center></td>
				<td>
					<a class="text-green" href="javascript:void(0)" title="Edit" onclick="edit_data('<?php echo $vd->id_kendaraan?>')"><i class="fa fa-pencil"></i> 
                    </a>
                    <a class="text-red" href="javascript:void(0)" title="Delete" onclick="delete_data('<?php echo $vd->id_kendaraan?>')"><i class="fa fa-trash"></i> 
                    </a>
				</td>
			</tr>
		<?php } ?>
		<?php } ?>
		</tbody>

		<tfoot>
		<tr>
			<th width="1%">#</th>
			<th>Nama Kendaraan</th>
			<th>Nomor Rangka</th>
			<th>STNK Expired</th>
			<th>KEUR Expired</th>
			<th>Aksi</th>
		</tr>
		</tfoot>
		</table>
	</div>
	<!-- /.box-body -->
</div>


<!-- awal untuk modal dialog -->
<!-- Modal -->
<div class="modal modal-primary" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><span class="fa fa-file-pdf-o"></span>&nbsp;Data Kendaraan</h4>
      </div>
      <div class="modal-body" id="MyModalBody">
		...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">
        <span class="glyphicon glyphicon-remove"></span>  Tutup</button>
        </div>
    </div>
  </div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>

<!-- page script -->
<script type="text/javascript">

  	$(function() {
    	$("#example1").DataTable();
  	});
  	function add_data(){
        window.location.href = siteurl+"kendaraan/create";
    }
    function edit_data(id){
		window.location.href = siteurl+"kendaraan/edit/"+id;
	}
	function PreviewPdf(id)
	{
		param=id;
		tujuan = 'customer/print_request/'+param;

	   	$(".modal-body").html('<iframe src="'+tujuan+'" frameborder="no" width="570" height="400"></iframe>');
	}

</script>
