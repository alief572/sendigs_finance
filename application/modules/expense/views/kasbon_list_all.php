<?php
$ENABLE_ADD     = has_permission('Kasbon_List.Add');
$ENABLE_MANAGE  = has_permission('Kasbon_List.Manage');
$ENABLE_VIEW    = has_permission('Kasbon_List.View');
$ENABLE_DELETE  = has_permission('Kasbon_List.Delete');
?>
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<div class="box">
	<!-- /.box-header -->
	<div class="box-body">
		<div class="table-responsive">
			<table id="mytabledata2" class="table table-bordered table-striped">
				<thead>
					<tr>
						<th width="5">#</th>
						<th>No Kasbon</th>
						<th>Tanggal</th>
						<th>Nama</th>
						<th>Approval Date</th>
						<th>Status</th>
						<th width="120">Action</th>
					</tr>
				</thead>
				<tbody>
					<?php
					if (!empty($results)) {
						$numb = 0;
						foreach ($results as $record) {
							$numb++; ?>
							<tr>
								<td><?= $numb; ?></td>
								<td><?= $record->no_doc ?></td>
								<td><?= $record->tgl_doc ?></td>
								<td><?= $record->nmuser ?></td>
								<td><?= $record->approved_on ?></td>
								<td>
									<?php
									if ($record->status == '0') {
										echo '<div class="badge bg-yellow text-light">New</div>';
									}
									if ($record->status == '1' || $record->status == '2') {
										echo '<div class="badge bg-dark-blue text-light">Approved</div>';
									}
									if ($record->status == '3') {
										$check_expense_report = $this->db->get_where('tr_expense_detail', ['id_kasbon' => $record->no_doc, 'status' => 2])->row();
										if (!empty($check_expense_report)) {
											echo '<div class="badge text-light" style="backgound-color: #990000;">Close</div>';
										} else {
											echo '<div class="badge bg-green text-light">Paid</div>';
										}
									}
									if ($record->status == '9') {
										echo '<div class="badge bg-red text-light">Reject</div>';
									}
									if ($record->status == '4') {
										echo '<div class="badge bg-blue text-dark">Kurang</div>';
									}
									?>
								</td>
								<td>
									<?php if ($ENABLE_VIEW) : ?>
										<?php
										if ($record->approved_by !== null) {
										?>

											<a class="btn btn-default btn-sm print" href="<?= base_url('expense/kasbon_print/' . $record->id) ?>" target="_blank" title="Print"><i class="fa fa-print"></i></a>

										<?php
										}
										?>
										<a class="btn btn-warning btn-sm view" href="javascript:void(0)" title="View" onclick="data_view('<?= $record->id ?>')"><i class="fa fa-eye"></i></a>
									<?php endif; ?>
								</td>
							</tr>
					<?php
						}
					}  ?>
				</tbody>
			</table>
		</div>
	</div>
	<!-- /.box-body -->
</div>
<div id="form-data"></div>
<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<!-- page script -->
<script type="text/javascript">
	var url_view = siteurl + 'expense/kasbon_view/';
	$("#mytabledata2").DataTable({
		dom: "<'row'<'col-sm-2'B><'col-sm-4'l><'col-sm-6'f>>rtip",
		buttons: [
			'excel'
		]
	});
</script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>