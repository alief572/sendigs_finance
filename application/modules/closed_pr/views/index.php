<?php
$ENABLE_ADD     = has_permission('Closed_PR.Add');
$ENABLE_MANAGE  = has_permission('Closed_PR.Manage');
$ENABLE_VIEW    = has_permission('Closed_PR.View');
$ENABLE_DELETE  = has_permission('Closed_PR.Delete');

?>
<style type="text/css">
	thead input {
		width: 100%;
	}
</style>

<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box">
	
	<!-- /.box-header -->
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
			<thead>
				<tr>
					<th class="text-center">#</th>
					<th class="text-center">No. PR</th>
					<th class="text-center">Kategori PR</th>
					<th class="text-center">Request By</th>
					<th class="text-center">Request Date</th>
					<?php 
						if($ENABLE_VIEW) {
							echo '<th class="text-center">Action</th>';
						}
					?>
				</tr>
			</thead>
			<tbody>
				<?php 
					$no = 1;
					foreach($result as $item) {

						echo '<tr>';
						echo '<td class="text-center">'.$no.'</td>';
						echo '<td class="text-center">'.$item->no_pr.'</td>';
						echo '<td class="text-center">'.$item->kategori_pr.'</td>';
						echo '<td class="text-center">'.$item->request_by.'</td>';
						echo '<td class="text-center">'.$item->request_date.'</td>';
						if($ENABLE_VIEW) {
							if($item->kategori_pr == 'PR Department') {
								$view_detail = '<a href="'.base_url("/non_rutin/add/".$item->id_pr."/view").'" class="btn btn-sm btn-info" target="_blank" title="View Detail PR"><i class="fa fa-eye"></i></a>';
							}else{
								if($item->kategori_pr == 'PR Material') {
									$view_detail = '<a href="'.base_url("request_pr_material/detail_planning/" . $item->id_pr).'" class="btn btn-sm btn-info" target="_blank" title="View Detail PR"><i class="fa fa-eye"></i></a>';
								}else{
									$view_detail = '<a href="'.base_url("request_pr_stok/detail_planning/" . $item->id_pr).'" class="btn btn-sm btn-info" target="_blank" title="View Detail PR"><i class="fa fa-eye"></i></a>';
								}
							}
							$view_barang = '<button type="button" class="btn btn-sm btn-success view_barang_pr" title="View Barang PR" data-id_pr="'.$item->id_pr.'" data-no_pr="'.$item->no_pr.'" data-kategori_pr="'.$item->kategori_pr.'"><i class="fa fa-list"></i></button>';

							echo '<td class="text-center">'.$view_detail.' '.$view_barang.'</td>';
						}
						echo '</tr>';

						$no++;
					}
				?>
			</tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>

<!-- awal untuk modal dialog -->
<!-- Modal -->
<div class="modal modal-primary" id="dialog-rekap" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel"><span class="fa fa-file-pdf-o"></span>&nbsp;Rekap Data Customer</h4>
			</div>
			<div class="modal-body" id="MyModalBody">
				...
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">
					<span class="glyphicon glyphicon-remove"></span> Close</button>
			</div>
		</div>
	</div>
</div>

<div class="modal modal-default fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel">List Material PR</h4>
			</div>
			<div class="modal-body" id="ModalView">
				...
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" data-dismiss="modal">
					<span class="glyphicon glyphicon-remove"></span> Close</button>
			</div>
		</div>
	</div>
</div>

<div class="modal modal-default fade" id="dialog-popupCP" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel">Closing PO</h4>
			</div>
			<form action="" method="post" id="CP-frm-data">
				<div class="modal-body" id="ModalViewCP">
					...
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">
						<span class="glyphicon glyphicon-remove"></span> Cancel
					</button>
					<button type="submit" class="btn btn-sm btn-danger">Close It!</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/js/jquery.maskMoney.js') ?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<!-- page script -->
<script type="text/javascript">
	$(document).ready(function() {
		$('#example1').dataTable();
	});

	$(document).on('click', '.view_barang_pr', function() {
		var id_pr = $(this).data('id_pr');
		var kategori_pr = $(this).data('kategori_pr');
		var no_pr = $(this).data('no_pr');

		$.ajax({
			type: 'post',
			url: siteurl + active_controller + 'view_barang_pr',
			data: {
				'id_pr': id_pr,
				'kategori_pr': kategori_pr,
				'no_pr': no_pr
			},
			cache: false,
			success: function(result) {
				$('#ModalView').html(result);
				$('#dialog-popup').modal('show');
			},
			error: function(result) {
				swal({
					title: 'Error !',
					text: 'Please try again !',
					type: 'error'
				});
			}
		});
	})
</script>