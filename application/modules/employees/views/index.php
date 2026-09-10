<?php
$ENABLE_VIEW = has_permission('Master_Employee.View');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css'); ?>">

<style>
	.table-employees thead th {
		vertical-align: middle !important;
		font-size: 13px;
		font-weight: 600;
		letter-spacing: 0.3px;
	}
	.table-employees tbody td {
		vertical-align: middle !important;
		font-size: 12px;
	}
	.box-employee-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 12px 15px;
	}
	.box-employee-title {
		font-size: 17px;
		font-weight: 700;
		margin: 0;
		color: #333;
	}
	.box-employee-desc {
		font-size: 12px;
		color: #777;
		margin-top: 2px;
	}
</style>

<div class="box box-primary box-solid" style="border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.08);">
	<div class="box-header with-border" style="background-color: #3c8dbc; color: #fff; border-top-left-radius: 4px; border-top-right-radius: 4px;">
		<div class="row">
			<div class="col-xs-12 col-sm-8">
				<h3 class="box-title" style="font-size: 18px; font-weight: 600;">
					<i class="fa fa-users" style="margin-right: 8px;"></i> <?= !empty($title) ? $title : 'Data Karyawan (Employees)'; ?>
				</h3>
				<span class="label label-default" style="margin-left: 10px; font-weight: normal; background-color: rgba(255,255,255,0.25); color: #fff;">
					<i class="fa fa-eye"></i> Mode View Only
				</span>
			</div>
			<div class="col-xs-12 col-sm-4 text-right hidden-xs">
				<small style="color: #e0f2fe;"><i class="fa fa-info-circle"></i> Menampilkan seluruh data karyawan aktif</small>
			</div>
		</div>
	</div>

	<div class="box-body" style="padding: 15px;">
		<div class="table-responsive">
			<table id="table-employees" class="table table-bordered table-striped table-hover table-employees" style="width: 100%;">
				<thead>
					<tr style="background-color: #2c3b41; color: #fff;">
						<th class="text-center" style="width: 40px;">No</th>
						<th class="text-center" style="width: 80px;">ID Emp</th>
						<th class="text-center" style="width: 100px;">NIK</th>
						<th class="text-left" style="min-width: 160px;">Nama Karyawan</th>
						<th class="text-left" style="min-width: 110px;">Kota Lahir</th>
						<th class="text-center" style="width: 95px;">Tgl Lahir</th>
						<th class="text-center" style="width: 95px;">Gender</th>
						<th class="text-center" style="width: 85px;">Agama</th>
						<th class="text-center" style="width: 60px;">Warga</th>
						<th class="text-center" style="width: 100px;">Status Kerja</th>
						<th class="text-center" style="width: 80px;">Status</th>
						<th class="text-center" style="width: 70px;">Aksi</th>
					</tr>
				</thead>
				<tbody>
					<!-- Loaded via DataTables Server-Side AJAX -->
				</tbody>
			</table>
		</div>
	</div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js'); ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js'); ?>"></script>
<script>
	$(document).ready(function() {
		initEmployeeTable();
	});

	function initEmployeeTable() {
		var table = $('#table-employees').DataTable({
			ajax: {
				url: siteurl + active_controller + 'get_data_employees',
				type: "POST",
				dataType: "JSON",
				data: function(d) {
					// Extra parameters if needed in the future
				},
				error: function(xhr, error, thrown) {
					console.error("Error loading employee data: ", thrown);
				}
			},
			columns: [
				{ data: 'no', orderable: false, searchable: false },
				{ data: 'id' },
				{ data: 'nik' },
				{ data: 'name' },
				{ data: 'hometown' },
				{ data: 'birthday' },
				{ data: 'gender' },
				{ data: 'religion' },
				{ data: 'nationality' },
				{ data: 'employee_status' },
				{ data: 'status_aktif' },
				{ data: 'option', orderable: false, searchable: false }
			],
			responsive: true,
			processing: true,
			serverSide: true,
			stateSave: true,
			destroy: true,
			paging: true,
			pageLength: 10,
			lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
			order: [[3, 'asc']], // Order by Name ascending
			drawCallback: function() {
				$('[data-toggle="tooltip"]').tooltip({ container: 'body' });
			},
			language: {
				processing: '<i class="fa fa-spinner fa-spin fa-2x fa-fw text-primary"></i><span class="sr-only">Memuat...</span>',
				search: "_INPUT_",
				searchPlaceholder: "Cari NIK, Nama, Kota...",
				lengthMenu: "Tampilkan _MENU_ data",
				info: "Menampilkan _START_ sampai _END_ dari total _TOTAL_ karyawan",
				infoEmpty: "Menampilkan 0 data",
				infoFiltered: "(disaring dari _MAX_ total data)",
				zeroRecords: "Tidak ada data karyawan yang cocok",
				emptyTable: "Data karyawan belum tersedia",
				paginate: {
					first: '<i class="fa fa-angle-double-left"></i>',
					previous: '<i class="fa fa-angle-left"></i>',
					next: '<i class="fa fa-angle-right"></i>',
					last: '<i class="fa fa-angle-double-right"></i>'
				}
			}
		});
	}
</script>