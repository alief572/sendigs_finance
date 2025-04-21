<?php
	$ENABLE_ADD     = has_permission('Master_Department.Add');
    $ENABLE_MANAGE  = has_permission('Master_Department.Manage');
    $ENABLE_VIEW    = has_permission('Master_Department.View');
    $ENABLE_DELETE  = has_permission('Master_Department.Delete');
?>
 <div class="box box-primary">
 	<div class="box-header">
 		<h3 class="box-title"><?= $title; ?></h3>
 		<div class="box-tool pull-right">
 		</div>
 	</div>
 	<!-- /.box-header -->
 	<div class="box-body">
 		<table id="example1" class="table table-bordered table-striped">
 			<thead>
 				<tr class='bg-blue'>
 					<th class="text-center">No</th>
 					<th class="text-center">Code</th>
 					<th class="text-center">Name</th>
 					<th class="text-center">Division</th>
 					<th class="text-center">Company</th>
 					<th class="text-center">Option</th>
 				</tr>
 			</thead>
 			<tbody>
 				<?php
					if ($row) {
						$int	= 0;
						foreach ($row as $datas) {
							$int++;


							echo "<tr>";
							echo "<td align='center'>$int</td>";
							echo "<td align='left'>" . $datas->id . "</td>";
							echo "<td align='left'>" . $datas->name . "</td>";
							echo "<td align='left'>" . $datas->division_name . "</td>";
							echo "<td align='left'>" . $datas->company_name . "</td>";
							echo "<td align='center'>";
							if ($ENABLE_MANAGE) {
								echo "<a href='" . site_url('departements/edit/' . $datas->id) . "' class='btn btn-sm btn-primary' title='Edit Data' data-role='qtip'><i class='fa fa-eye'></i></a>";
							}
							
							echo "</td>";
							echo "</tr>";
						}
					}
					?>
 			</tbody>
 		</table>
 	</div>
 	<!-- /.box-body -->
 </div>
 <!-- /.box -->

 <script>
 	$(document).ready(function() {
 		$('#btn-add').click(function() {
 			loading_spinner();
 		});
 	});

 	function deleteData(id) {
 		swal({
 				title: "Are you sure?",
 				text: "You will not be able to process again this data!",
 				type: "warning",
 				showCancelButton: true,
 				confirmButtonClass: "btn-danger",
 				confirmButtonText: "Yes, Process it!",
 				cancelButtonText: "No, cancel process!",
 				closeOnConfirm: true,
 				closeOnCancel: false
 			},
 			function(isConfirm) {
 				if (isConfirm) {
 					loading_spinner();
 					window.location.href = base_url + 'index.php/' + active_controller + '/delete/' + id;

 				} else {
 					swal("Cancelled", "Data can be process again :)", "error");
 					return false;
 				}
 			});

 	}

 	function klik() {
 		alert("Ini adalah contoh event klik");
 	}
 </script>