<?php
$ENABLE_ADD     = has_permission('Kasbon.Add');
$ENABLE_MANAGE  = has_permission('Kasbon.Manage');
$ENABLE_VIEW    = has_permission('Kasbon.View');
$ENABLE_DELETE  = has_permission('Kasbon.Delete');
?>
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" integrity="sha512-yVvxUQV0QESBt1SyZbNJMAwyKvFTLMyXSyBHDO4BG5t7k/Lw34tyqlSDlKIrIENIzCl+RVUNjmCPG+V/GMesRw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<div class="box">
	<div class="box-header">
		<?php if ($ENABLE_ADD) : ?>
			<div class="dropdown">
				<button class="btn btn-success btn-sm" type="button" onclick="data_add()">
					<i class="fa fa-plus">&nbsp;</i> Tambah
				</button>
			</div>
		<?php endif; ?>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<div class="table-responsive">
			<table id="mytabledata" class="table table-bordered table-striped">
				<thead>
					<tr>
						<th width="5">#</th>
						<th>No Kasbon</th>
						<th>Tanggal</th>
						<th>Nama</th>
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
											echo '<div class="badge bg-dark text-light">Close</div>';
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
									<?php if ($ENABLE_VIEW && $record->approved_by !== null) : ?>
										<a class="btn btn-default btn-sm print" href="<?= base_url('expense/kasbon_print/' . $record->id) ?>" target="_blank" title="Print"><i class="fa fa-print"></i></a>
										<a class="btn btn-warning btn-sm view" href="javascript:void(0)" title="View" onclick="data_view('<?= $record->id ?>')"><i class="fa fa-eye"></i></a>
										<?php endif;
									if ($ENABLE_MANAGE) :
										if ($record->status == 0 || $record->status == 9) { ?>
											<a class="btn btn-success btn-sm edit" href="javascript:void(0)" title="Edit" onclick="data_edit('<?= $record->id ?>')"><i class="fa fa-edit"></i></a>
										<?php }
									endif;
									if ($ENABLE_DELETE) :
										if ($record->status == 0 || $record->status == 9) { ?>
											<a class="btn btn-danger btn-sm delete" href="javascript:void(0)" title="Hapus" onclick="data_delete('<?= $record->id ?>')"><i class="fa fa-trash"></i></a>
									<?php }
									endif; ?>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<!-- page script -->
<script type="text/javascript">
	var url_add = siteurl + 'expense/kasbon_create/';
	var url_edit = siteurl + 'expense/kasbon_edit/';
	var url_delete = siteurl + 'expense/kasbon_delete/';
	var url_view = siteurl + 'expense/kasbon_view/';

	$('.chosen_select').chosen({
		width: '100%'
	});
</script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>