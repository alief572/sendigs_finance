<?php
$ENABLE_ADD     = has_permission('Kasbon_Approval.Add');
$ENABLE_MANAGE  = has_permission('Kasbon_Approval.Manage');
$ENABLE_VIEW    = has_permission('Kasbon_Approval.View');
$ENABLE_DELETE  = has_permission('Kasbon_Approval.Delete');
?>
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<div class="box">
	<div class="box-header">
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
							$nmuser = $record->nmuser;
							$check_detail = $this->db->get_where('tr_pr_detail_kasbon', ['id_kasbon' => $record->no_doc])->result();
							if (count($check_detail)) {
								if ($record->tipe_pr == 'pr departemen') {
									$this->db->select('b.nm_lengkap');
									$this->db->from('rutin_non_planning_header a');
									$this->db->join('users b', 'b.id_user = a.created_by');
									$this->db->where('a.no_pr', $record->id_pr);
									$get_single_detail = $this->db->get()->row();

									$nmuser = $get_single_detail->nm_lengkap;
								}

								if ($record->tipe_pr == 'pr stok') {
									$this->db->select('b.nm_lengkap');
									$this->db->from('material_planning_base_on_produksi a');
									$this->db->join('users b', 'b.id_user = a.created_by');
									$this->db->where('a.no_pr', $record->id_pr);
									$get_single_detail = $this->db->get()->row();

									$nmuser = $get_single_detail->nm_lengkap;
								}

								if ($record->tipe_pr == 'pr asset') {
									$this->db->select('b.nm_lengkap');
									$this->db->from('tran_pr_header a');
									$this->db->join('users b', 'b.id_user = a.created_by');
									$this->db->where('a.no_pr', $record->id_pr);
									$get_single_detail = $this->db->get()->row();

									$nmuser = $get_single_detail->nm_lengkap;
								}
							}
							$numb++; ?>
							<tr>
								<td><?= $numb; ?></td>
								<td><?= $record->no_doc ?></td>
								<td><?= $record->tgl_doc ?></td>
								<td><?= $nmuser ?></td>
								<td><?= $status[$record->status] ?></td>
								<td>
									<?php if ($ENABLE_VIEW) : ?>
										<a class="btn btn-default btn-sm print" href="<?= base_url('expense/kasbon_print/' . $record->id) ?>" target="_blank" title="Print"><i class="fa fa-print"></i></a>
										<a class="btn btn-warning btn-sm view" href="<?= base_url('expense/kasbon_view/' . $record->id . '/_fin') ?>" title="View"><i class="fa fa-eye"></i> </a>
										<?php endif;
									if ($ENABLE_MANAGE) :
										if ($record->status == 0) { ?>
											<a class="btn btn-success btn-sm approve" href="<?= base_url('expense/kasbon_edit/' . $record->id . '/_fin') ?>" title="Approve"><i class="fa fa-check-square-o"></i></a>
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