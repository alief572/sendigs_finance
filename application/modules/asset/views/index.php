<?php
$ENABLE_ADD = has_permission('Assets.Add');
$ENABLE_MANAGE = has_permission('Assets.Manage');
$ENABLE_VIEW = has_permission('Assets.View');
$ENABLE_DELETE = has_permission('Assets.Delete');
?>
<style type="text/css">
	thead input {
		width: 100%;
	}
</style>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css'); ?>">
<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data">
	<div class="box">
		<div class="box-header">
			<div class="box-tool pull-right">

				<?php if ($ENABLE_ADD) : ?>
					<button type='button' id='add' class="btn btn-success" title="Tambah Asset"><i class="fa fa-plus">&nbsp;</i>Tambah Asset</button>
				<?php endif; ?>
				<!--<button type='button' id='jurnal' class="btn btn-primary" title="Buat Jurnal"><i class="fa fa-plus">&nbsp;</i>Buat Jurnal</button>-->

			</div>
			<div class="box-tool pull-left">

				<label>Pencarian : </label>
				<!--<select id='kdcab' name='kdcab' class='form-control input-sm chosen-container' style='min-width:150px; float:left; margin-bottom: 5px;'>
					<option value='0'>Semua Cabang</option>
					<?php
					foreach ($cabang as $val => $valx) {
						echo "<option value='" . $valx['kdcab'] . "'>" . strtoupper($valx['namacabang']) . "</option>";
					}
					?>
				</select>-->
				<select id='kategory' name='kategory' class='form-control input-sm chosen-select' style='min-width:150px; float:left; margin-bottom: 5px;'>
					<option value='0'>Semua Kategori</option>
					<?php
					foreach ($kategori as $val => $valx) {
						echo "<option value='" . $valx['id'] . "'>" . strtoupper($valx['nm_category']) . "</option>";
					}
					?>
				</select>
				<?php
				echo form_input(array('type' => 'hidden', 'id' => 'tanggalx', 'name' => 'tanggalx', 'class' => 'form-control input-sm', 'autocomplete' => 'off', 'placeholder' => 'Tanggal', 'readonly' => 'readonly'));
				?>

			</div>
		</div>
		<!-- /.box-header -->
		<div class="box-body">
			<table id="example1" class="table table-bordered table-striped" width='100%'>
				<thead>
					<tr class='bg-blue'>
						<th class="text-center">No</th>
						<th class="text-center" width='140px'>Kode Asset</th>
						<th class="text-center">Nama Asset</th>
						<th class="text-center">Category</th>
						<th class="text-center">Depresiasi</th>
						<th class="text-center">Nilai&nbsp;Perolehan</th>
						<th class="text-center">Penyusutan</th>
						<th class="text-center">Akumulasi</th>
						<th class="text-center">Nilai Buku</th>
						<th class="text-center" class='no-sort'>#</th>
					</tr>
				</thead>
				<tbody></tbody>
				<tfoot>
					<tr>
						<th colspan="5" style="text-align:center">TOTAL KESELURUHAN</th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
					</tr>
				</tfoot>
			</table>
		</div>
		<!-- /.box-body -->
	</div>

	<!-- modal -->
	<div class="modal fade" id="ModalView">
		<div class="modal-dialog" style='width:80%; '>
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="head_title"></h4>
				</div>
				<div class="modal-body" id="view">
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

<style>
	.chosen-container {
		width: 100% !important;
		text-align: left !important;
	}
</style>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js'); ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js'); ?>"></script>
<script src="<?= base_url('assets/js/jquery.maskMoney.js') ?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>

<!-- page script -->
<script type="text/javascript">
	$(document).ready(function() {
		$('.chosen-select').select2();
		var kdcab = $('#kdcab').val();
		var tgl = $('#tanggalx').val();
		var kategori = $('#kategory').val();
		DataTables(kdcab, tgl, kategori);
	});

	$(document).on('change', '#kdcab', function(e) {
		e.preventDefault();
		var kdcab = $('#kdcab').val();
		var tgl = $('#tanggalx').val();
		var kategori = $('#kategory').val();
		DataTables(kdcab, tgl, kategori);
	});

	$(document).on('change', '#tanggalx', function(e) {
		e.preventDefault();
		var kdcab = $('#kdcab').val();
		var tgl = $('#tanggalx').val();
		var kategori = $('#kategory').val();
		DataTables(kdcab, tgl, kategori);
		// alert($(this).val());
	});

	$(document).on('change', '#kategory', function(e) {
		e.preventDefault();
		var kdcab = $('#kdcab').val();
		var tgl = $('#tanggalx').val();
		var kategori = $('#kategory').val();
		DataTables(kdcab, tgl, kategori);
		// alert($(this).val());
	});

	$("#tanggalx").datepicker({
		format: 'mm-yyyy',
		// dateFormat: 'dd, mm, yy',
		viewMode: "months",
		minViewMode: "months",
		autoClose: true
		// defaultDate: new Date()
	});

	$(document).on('click', '#add', function(e) {
		e.preventDefault();
		$("#head_title").html("<b>TAMBAHKAN ASET BARU</b>");
		$("#view").load(siteurl + 'asset/modal');
		$("#ModalView").modal();
	});

	$(document).on('click', '#jurnal', function(e) {
		e.preventDefault();
		$("#head_title").html("<b>TAMBAHKAN JURNAL BARU</b>");
		$("#view").load(siteurl + 'asset/modal_jurnal');
		$("#ModalView").modal();
	});

	$(document).on('click', '#edit', function(e) {
		e.preventDefault();
		$("#head_title").html("<b>EDIT ASET</b>");
		$("#view").load(siteurl + 'asset/modal_edit/' + $(this).data('id') + '/' + $(this).data('group_akses'));
		$("#ModalView").modal();
	});

	$(document).on('click', '#detail', function(e) {
		e.preventDefault();
		$("#head_title").html("<b>DETAIL ASET</b>");
		$("#view").load(siteurl + 'asset/modal_view/' + $(this).data('id'));
		$("#ModalView").modal();
	});

	function DataTables(kdcab = null, tgl = null, kategori = null) {
		let total_aset = 0;
		let total_susut = 0;
		let total_sisa = 0;
		var dataTable = $('#example1').DataTable({
			"serverSide": true,
			"stateSave": true,
			"bAutoWidth": true,
			"destroy": true,
			"responsive": true,
			"aaSorting": [
				[1, "asc"]
			],
			"columnDefs": [{
					"targets": 'no-sort',
					"orderable": false,
				},
				{
					className: 'text-right',
					targets: [5, 6, 7]
				}
			],
			"sPaginationType": "simple_numbers",
			"iDisplayLength": 10,
			"aLengthMenu": [
				[10, 20, 50, 100, 150],
				[10, 20, 50, 100, 150]
			],
			"ajax": {
				url: siteurl + active_controller + 'data_side',
				type: "post",
				data: function(d) {
					d.kdcab = kdcab,
						d.tgl = tgl,
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
					total_susut = data.recordsSusut;
					total_sisa = data.recordsSisa;
					return data.data;
				}
			},
			drawCallback: function(settings) {
				var api = this.api();

				$(api.column(5).footer()).html("<div align='right'>" + number_format(total_aset) + "</div>");
				$(api.column(6).footer()).html("<div align='right'>" + number_format(total_susut) + "</div>");
				$(api.column(7).footer()).html("<div align='right'>" + number_format(total_sisa) + "</div>");
			}


		});
	}

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