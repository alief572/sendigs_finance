<?php
$this->load->view('include/side_menu');
?>
<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data">
	<div class="box box-primary">
		<div class="box-header">
		<h3 class="box-title"><?php echo $title;?></h3><br><br>
			<div class="box-tool pull-right">
			<?php
				if($akses_menu['create']=='1'){
			?>
				<!-- <a href="<?php echo site_url('asset/download_excel_all/0') ?>" target='_blank' class="btn btn-md btn-info" '>
					<i class="fa fa-file-excel-o"></i> Download ALL
				</a> -->
				<button type='button' id='jurnal' class="btn btn-md btn-primary" title="Next Depresiasi"></i>Next Depresiasi</button>
			<?php } ?>
			</div>
			<div class="box-tool pull-left">
				<select id='kategory' name='kategory' class='form-control input-sm chosen-select' style='min-width:150px; float:left; margin-bottom: 5px;'>
					<option value='0'>Select Category</option>
					<?php
						foreach($kategori AS $val => $valx){
							echo "<option value='".$valx['id']."'>".strtoupper($valx['nm_category'])."</option>";
						}
					?>
				</select>
				<?php
					echo form_input(array('type'=>'hidden','id'=>'tanggalx','name'=>'tanggalx','class'=>'form-control input-sm','autocomplete'=>'off','placeholder'=>'Tanggal', 'readonly'=>'readonly'));
				?> 
				<select id='bulan' name='bulan' class='form-control input-sm' style='width:120px;'>
					<option value='0'>Select Month</option>
					<option value='01'>January</option>
					<option value='02'>February</option>
					<option value='03'>March</option>
					<option value='04'>April</option>
					<option value='05'>May</option>
					<option value='06'>June</option>
					<option value='07'>July</option>
					<option value='08'>August</option>
					<option value='09'>September</option>
					<option value='10'>October</option>
					<option value='11'>November</option>
					<option value='12'>December</option>
				</select>
				<select id='tahun' name='tahun' class='form-control input-sm' style='width:100px;'>
					<option value='0'>Select Year</option>
					<?php
					$date = date('Y');
					for($a=2022; $a <= $date; $a++){
						echo "<option value='".$a."'>".$a."</option>";
					}
					?>
				</select>
				<button type='button' id='search_filter' class="btn btn-md btn-warning" title="Search"><i class="fa fa-search">&nbsp;</i>Search</button>
				<button type='button' id='download_asset' class="btn btn-md btn-success" title="Download"><i class="fa fa-file-excel-o">&nbsp;</i>Download</button>
				<!-- <br><p class='text-bold text-red'>Last Depreciation : <?=$bulan_;?> <?=$date;?></p> -->
			</div>
		</div>
		<!-- /.box-header -->
		<div class="box-body">
			<div class="table-responsive">
				<table id="example1" class="table table-bordered table-striped" width='100%'>
					<thead>
						<tr class='bg-blue' >
							<th class="text-center">#</th>
							<th class="text-center">Kode Asset</th>
							<th class="text-center">Asset Name</th>
							<th class="text-center">Tgl Perolehan</th>
							<th class="text-center">Category</th>
							<th class="text-center">Kelompok Penyusutan</th>
							<th class="text-center">Costcenter</th>
							<th class="text-center">Depreciation</th>
							<th class="text-center">Acquisition</th>
							<th class="text-center">Depreciation</th>
							<th class="text-center">Akumulasi Depresiasi</th>
							<th class="text-center">Asset&nbsp;Val</th>
							<th class="text-center no-sort">#</th>
						</tr>
					</thead>
					<tbody></tbody>
					<tfoot>
						<tr>
							<th colspan="6" style="text-align:center">SUM</th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
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
		<div class="modal-dialog"  style='width:90%; '>
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
	$(document).ready(function(){
		$('#bulan').val('<?=date('m')?>').trigger("chosen:updated");;
		$('#tahun').val('<?=date('Y')?>').trigger("chosen:updated");;
		var kdcab 		= $('#kdcab').val();
		var tgl 		= $('#tanggalx').val();
		var kategori 	= $('#kategory').val();
		var bulan 		= $('#bulan').val();
		var tahun 		= $('#tahun').val();
		DataTables(kdcab, tgl, kategori, bulan, tahun);
	});

	$(document).on('click', '#download_asset', function(e){
		// loading_spinner();
		var kategori 	= $('#kategory').val();
		var bulan 		= $('#bulan').val();
		var tahun 		= $('#tahun').val();
		
		if(bulan == 0){
			swal({
				title	: "Error Message!",
				text	: 'Filter bulan asset empty ...',
				type	: "warning"
			});
			return false;
		}
        if(tahun == 0){
			swal({
				title	: "Error Message!",
				text	: 'Filter tahun asset empty ...',
				type	: "warning"
			});
			return false;
		}

		var Link	= base_url + active_controller +'/download_excel/'+kategori+'/'+bulan+'/'+tahun;
//		alert(Link);
		window.open(Link);
	});

    
    $(document).on('click','#search_filter', function(e){
        e.preventDefault();
        var kdcab         = $('#kdcab').val();
        var tgl         = $('#tanggalx').val();
        var kategori     = $('#kategory').val();
        var bulan         = $('#bulan').val();
        var tahun         = $('#tahun').val();
/*
        if(kategori == 0){
			swal({
				title	: "Error Message!",
				text	: 'Filter category asset empty ...',
				type	: "warning"
			});
			return false;
		}
*/
        if(bulan == 0){
			swal({
				title	: "Error Message!",
				text	: 'Filter bulan asset empty ...',
				type	: "warning"
			});
			return false;
		}
        if(tahun == 0){
			swal({
				title	: "Error Message!",
				text	: 'Filter tahun asset empty ...',
				type	: "warning"
			});
			return false;
		}
        DataTables(kdcab, tgl, kategori, bulan, tahun);
    });

	$(document).on('change','#kdcab,#kategory', function(e){
		e.preventDefault();
		var kdcab 	= $('#kdcab').val();
		var tgl 	= $('#tanggalx').val();
		var kategori 	= $('#kategory').val();
		var bulan 		= $('#bulan').val();
		var tahun 		= $('#tahun').val();
		DataTables(kdcab, tgl, kategori, bulan, tahun);
	});

	$(document).on('click', '.detail', function(e){
		e.preventDefault();
		loading_spinner();
		$("#head_title").html("<b>DETAIL ASSET</b>");
		$("#view").load(base_url + active_controller +'/modal_view/'+$(this).data('id'));
		$("#ModalView").modal();
	});

	$(document).on('click', '#jurnal', function(e){
		e.preventDefault();
		$("#head_title").html("<b>NEXT DEPRESIASI</b>");
		$("#view").load(base_url + active_controller +'/modal_jurnal');
		$("#ModalView").modal();
	});

	$(document).on('click', '#simpanjurnal', function(e){
		e.preventDefault();
		swal({
			  title: "Apakah anda yakin ?",
			  text: "Data akan diproses ke jurnal erp!",
			  type: "warning",
			  showCancelButton: true,
			  confirmButtonClass: "btn-danger",
			  confirmButtonText: "Ya, Lanjutkan!",
			  cancelButtonText: "Tidak, Batalkan!",
			  closeOnConfirm: false,
			  closeOnCancel: false
			},
			function(isConfirm) {
			  if (isConfirm) {
					// loading_spinner();
					var formData  	= new FormData($('#form_proses_bro')[0]);
					var baseurl		= base_url + active_controller +'/saved_jurnal_erp';
					$.ajax({
						url			: baseurl,
						type		: "POST",
						data		: formData,
						cache		: false,
						dataType	: 'json',
						processData	: false, 
						contentType	: false,				
						success		: function(data){								
							if(data.status == 1){											
								swal({
									  title	: "Berhasil Tersimpan !",
									  text	: data.pesan,
									  type	: "success",
									  timer	: 7000
									});
								window.location.href = base_url + active_controller + '/depreciation';
							}
							else{ 
								if(data.status == 0){
									swal({
									  title	: "Gagal Tersimpan !",
									  text	: data.pesan,
									  type	: "warning",
									  timer	: 7000
									});
								}
								else{
									swal({
									  title	: "Terjadi kesalahan saat proses simpan data!",
									  text	: data.pesan,
									  type	: "warning",
									  timer	: 7000
									});
								}
								$('#simpanjurnal').prop('disabled',false);
							}
						},
						error: function() {
							swal({
							  title				: "Error Message !",
							  text				: 'Terjadi kesalahan saat proses simpan data!',						
							  type				: "warning",								  
							  timer				: 7000
							});
							$('#simpanjurnal').prop('disabled',false);
						}
					});
			  } else {
				swal("Dibatalkan", "Data dapat diproses kembali", "error");
				$('#simpanjurnal').prop('disabled',false);
				return false;
			  }
		});
	});

	function DataTables(kdcab=null, tgl=null, kategori=null, bulan=null, tahun=null){
		let total_aset	= 0;
		let total_susut	= 0;
		let total_sisa	= 0;
		var dataTable = $('#example1').DataTable({
			"serverSide": true,
			"stateSave" : true,
			"autoWidth": false,
			"destroy"	: true,
			"responsive": true,
			"processing": true,
/*
			"fixedHeader": {
				"header": true,
				"footer": true
			},
*/
			"aaSorting"		: [[ 1, "asc" ]],
			"columnDefs"	: [ {
				"targets"	: 'no-sort',
				"orderable"	: false,
				},
				{ className: 'text-right', targets: [7, 8, 9, 10] }
			],
			"sPaginationType": "simple_numbers",
			"iDisplayLength": 10,
			"aLengthMenu": [[10, 20, 50, 100, 150], [10, 20, 50, 100, 150]],
			"ajax":{
				url : base_url + active_controller+'/data_side_depreciation',
				type: "post",
				data: function(d){
					d.kdcab = kdcab,
					d.tgl = tgl,
					d.kategori = kategori,
					d.bulan = bulan,
					d.tahun = tahun
				},
				cache: false,
				error: function(){
					$(".my-grid-error").html("");
					$("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="13">No data found in the server</th></tr></tbody>');
					$("#my-grid_processing").css("display","none");
				},
				 dataSrc: function ( data ) {
				   total_aset = data.recordsAset;
				   total_susut = data.recordsSusut;
				   total_susut_ak = data.recordsSusutAk;
				   total_sisa = data.recordsSisa;
				   return data.data;
				 }
			},
			/*
			"footerCallback": function ( row, data, start, end, display ) {
				var api = this.api(), data;

				var intVal = function ( i ) {

					return typeof i === 'string' ?
						i.replace(/[\$,]/g, '')*1 :
						typeof i === 'number' ?
							i : 0;
				};
				console.log(data);
				var perolehan = api
					.column(5)
					// .cells( null, this.index())
					.data()
					.reduce( function (a, b) { return intVal(a) + intVal(b); }, 0 );

				var susut = api
					.column(6)
					.data()
					.reduce( function (a, b) { return intVal(a) + intVal(b); }, 0 );

				var sisa = api
					.column(7)
					.data()
					.reduce( function (a, b) { return intVal(a) + intVal(b); }, 0 );

				$( api.column( 5 ).footer() ).html("<div align='right'>"+ number_format(perolehan) +"</div>");
				$( api.column( 6 ).footer() ).html("<div align='right'>"+ number_format(susut) +"</div>");
				$( api.column( 7 ).footer() ).html("<div align='right'>"+ number_format(sisa) +"</div>");
			}
			*/
			drawCallback: function( settings ) {
				var api = this.api();

				$( api.column( 8 ).footer() ).html("<div align='right'>"+ number_format(total_aset) +"</div>");
				$( api.column( 9 ).footer() ).html("<div align='right'>"+ number_format(total_susut) +"</div>");
				// $( api.column( 8 ).footer() ).html("<div align='right'></div>");
				$( api.column( 10 ).footer() ).html("<div align='right'>"+ number_format(total_susut_ak) +"</div>");
				$( api.column( 11 ).footer() ).html("<div align='right'>"+ number_format(total_sisa) +"</div>");
			}


		});
	}
	

	function number_format (number, decimals, dec_point, thousands_sep) {
		// Strip all characters but numerical ones.
		number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
		var n = !isFinite(+number) ? 0 : +number,
			prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
			sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
			dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
			s = '',
			toFixedFix = function (n, prec) {
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
