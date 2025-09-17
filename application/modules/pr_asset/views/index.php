<?php
$this->load->view('include/side_menu');
?>
<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data">
	<div class="box box-primary">
		<div class="box-header">
			<h3 class="box-title"><?php echo $title; ?></h3><br><br>
			<div class="box-tool pull-right">
				<?php
				if ($akses_menu['create'] == '1') {
				?>
					<a href="<?php echo site_url('asset/add') ?>" class="btn btn-md btn-success" id='btn-add'>
						<i class="fa fa-plus"></i> Add Asset
					</a>
					<a href="<?php echo site_url('asset/download_excel_all_default/0') ?>" target='_blank' class="btn btn-md btn-info" '>
					<i class="fa fa-file-excel-o"></i> Download ALL
				</a>
			<?php } ?>
			</div>
			<div class="box-tool pull-left">
				<select id=' kategory' name='kategory' class='form-control input-sm chosen-select' style='min-width:150px; float:left; margin-bottom: 5px;'>
						<option value='0'>All Category</option>
						<?php
						foreach ($kategori as $val => $valx) {
							echo "<option value='" . $valx['id'] . "'>" . strtoupper($valx['nm_category']) . "</option>";
						}
						?>
						</select>
			</div>
		</div>
		<!-- /.box-header -->
		<div class="box-body">
			<div class="table-responsive">
				<table id="example1" class="table table-bordered table-striped" width='100%'>
					<thead>
						<tr class='bg-blue'>
							<th class="text-center" width='5%'>#</th>
							<th class="text-center">Kode Asset</th>
							<th class="text-center">Asset Name</th>
							<th class="text-center" width='8%'>Tgl Perolehan</th>
							<th class="text-center" width='10%'>Category</th>
							<th class="text-center" width='20%'>Kelompok Penyusutan</th>
							<th class="text-center" width='10%'>Costcenter</th>
							<th class="text-center" width='8%'>Depreciation</th>
							<th class="text-center" width='10%'>Acquisition</th>
							<th class="text-center no-sort" width='10%'>#</th>
						</tr>
					</thead>
					<tbody></tbody>
					<tfoot>
						<tr>
							<th colspan="7" style="text-align:center">SUM</th>
							<th></th>
							<th></th>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
		<!-- /.box-body -->
	</div>

	<!-- modal -->
	<div class="modal fade" id="ModalView">
		<div class="modal-dialog" style='width:90%; '>
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="head_title"></h4>
				</div>
				<div class="modal-body" id="view">
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default " data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	<!-- modal -->

	<!-- modal alert -->
	<div class="modal fade" id="myModal" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content" style='margin-top: 150px;'>
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title"><b>Pemberitahuan</b></h4>
				</div>
				<div class="modal-body">
					<p id="error"></p>
				</div>
			</div>
		</div>
	</div>
	<!-- modal alert -->

</form>
<!-- DataTables -->
<?php $this->load->view('include/footer'); ?>
<!-- page script -->
<script type="text/javascript">
	$(document).ready(function() {
		var kategori = $('#kategory').val();
		DataTables(kategori);
	});

	$(document).on('click', '#download_asset', function(e) {
		// loading_spinner();
		var kategori = $('#kategory').val();
		var bulan = $('#bulan').val();
		var tahun = $('#tahun').val();


		var Link = base_url + active_controller + '/download_excel/' + kategori + '/' + bulan + '/' + tahun;
		window.open(Link);
	});

	$(document).on('change', '#kategory', function(e) {
		e.preventDefault();
		var kategori = $('#kategory').val();
		DataTables(kategori);
	});

	$(document).on('click', '.detail', function(e) {
		e.preventDefault();
		loading_spinner();
		$("#head_title").html("<b>DETAIL ASSET</b>");
		$("#view").load(base_url + active_controller + '/modal_view/' + $(this).data('id'));
		$("#ModalView").modal();
	});

	$(document).on('click', '#jurnal', function(e) {
		e.preventDefault();
		$("#head_title").html("<b>TAMBAHKAN JURNAL BARU</b>");
		$("#view").load(base_url + active_controller + '/modal_jurnal');
		$("#ModalView").modal();
	});

	function DataTables(kategori = null) {
		let total_aset = 0;
		let total_susut = 0;
		let total_sisa = 0;
		var dataTable = $('#example1').DataTable({
			"serverSide": true,
			"stateSave": true,
			"autoWidth": false,
			"destroy": true,
			"responsive": true,
			"processing": true,
			"fixedHeader": {
				"header": true,
				"footer": true
			},
			"aaSorting": [
				[1, "asc"]
			],
			"columnDefs": [{
					"targets": 'no-sort',
					"orderable": false,
				},
				{
					className: 'text-right',
					targets: [7]
				}
			],
			"sPaginationType": "simple_numbers",
			"iDisplayLength": 10,
			"aLengthMenu": [
				[10, 20, 50, 100, 150],
				[10, 20, 50, 100, 150]
			],
			"ajax": {
				url: base_url + active_controller + '/data_side',
				type: "post",
				data: function(d) {
					d.kategori = kategori
				},
				cache: false,
				error: function() {
					$(".my-grid-error").html("");
					$("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
					$("#my-grid_processing").css("display", "none");
				},
				dataSrc: function(data) {
					total_aset = data.recordsAset;
					return data.data;
				}
			},
			drawCallback: function(settings) {
				var api = this.api();
				$(api.column(7).footer()).html("<div align='right'>" + number_format(total_aset) + "</div>");
			}


		});
	}

	$(document).on('click', '.delete_asset', function(e) {
		e.preventDefault();
		var id = $(this).data('id');

		swal({
				title: "Are you sure?",
				text: "You will not be able to process again this data!",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-danger",
				confirmButtonText: "Yes, Process it!",
				cancelButtonText: "No, cancel process!",
				closeOnConfirm: false,
				closeOnCancel: false
			},
			function(isConfirm) {
				if (isConfirm) {
					var baseurl = base_url + active_controller + '/delete_asset/' + id;
					$.ajax({
						url: baseurl,
						type: "POST",
						cache: false,
						dataType: 'json',
						processData: false,
						contentType: false,
						success: function(data) {
							if (data.status == 1) {
								swal({
									title: "Save Success!",
									text: data.pesan,
									type: "success",
									timer: 7000
								});
								window.location.href = base_url + active_controller;
							} else {
								swal({
									title: "Save Failed!",
									text: data.pesan,
									type: "warning",
									timer: 7000,
									showCancelButton: false,
									showConfirmButton: false,
									allowOutsideClick: false
								});
							}
						},
						error: function() {
							swal({
								title: "Error Message !",
								text: 'An Error Occured During Process. Please try again..',
								type: "warning",
								timer: 3000,
								showCancelButton: false,
								showConfirmButton: false,
								allowOutsideClick: false
							});
						}
					});
				} else {
					swal("Cancelled", "Data can be process again :)", "error");
					return false;
				}
			});

	});


	function number_format(number, decimals, dec_point, thousands_sep) {
		// Strip all characters but numerical ones.
		number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
		var n = !isFinite(+number) ? 0 : +number,
			prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
			sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
			dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
			s = '',
			toFixedFix = function(n, prec) {
				var k = Math.pow(10, prec);
				return '' + Math.round(n * k) / k;
			};
		// Fix for IE parseFloat(0.55).toFixed(0) = 0;
		s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
		if (s[0].length > 3) {
			s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
		}
		if ((s[1] || '').length < prec) {
			s[1] = s[1] || '';
			s[1] += new Array(prec - s[1].length + 1).join('0');
		}
		return s.join(dec);
	}
</script>