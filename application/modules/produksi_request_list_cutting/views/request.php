<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post" autocomplete="off"><br>
			<div class="form-group row">
				<div class="col-md-2 text-bold">No. SO <span class='text-danger'>*</span></div>
				<div class="col-md-4">
					<select name='id_so' id='id_so' class='form-control input-md chosen-select'>
						<?php
						echo "<option value='0'>Select SO Number</option>";
						foreach ($listSO as $key => $value) {
							$so_sales = (!empty($value['so_customer']))?' - '.$value['so_customer'].' (SO Sales)':'';
							echo "<option value='".$value['id']."'  data-so_number='".$value['so_number']."' data-nama='".strtoupper($value['nama_product'])."'>".$value['so_number']." (SO Produksi)".$so_sales."</option>";
						}
						?>
					</select>
				</div>
				<div class="col-md-2 text-bold"></div>
				<div class="col-md-4"></div>
			</div>
			<div class="form-group row">
				<div class="col-md-2 text-bold">No. SPK <span class='text-danger'>*</span></div>
				<div class="col-md-4">
					<select name='id_spk' id='id_spk' class='form-control input-md chosen-select'>
						<?php
						echo "<option value='0'>List Empty</option>";
						// foreach ($listGudang as $key => $value) {
						// 	echo "<option value='".$value['id']."'>".$value['nm_gudang']."</option>";
						// }
						?>
					</select>
				</div>
				<div class="col-md-2 text-bold"></div>
				<div class="col-md-4"></div>
			</div>
			<div class="form-group row">
				<div class="col-md-2 text-bold">Request Ke <span class='text-danger'>*</span></div>
				<div class="col-md-4">
					<select name='id_gudang' id='id_gudang' class='form-control input-md chosen-select'>
						<?php
						echo "<option value='0'>Pilih Request Ke Gudang</option>";
						foreach ($listGudang as $key => $value) {
							echo "<option value='".$value['id']."'>".$value['nm_gudang']."</option>";
						}
						?>
					</select>
				</div>
				<div class="col-md-2 text-bold">Product Name</div>
				<div class="col-md-4">
					<input type="text" name='product_name' id='product_name' class='form-control input-md' readonly value=''>
				</div>
			</div>
		  	<div class="form-group row">
				<div class="col-md-2 text-bold">Ke Costcenter <span class='text-danger'>*</span></div>
				<div class="col-md-4">
					<select name='id_costcenter' id='id_costcenter' class='form-control input-md chosen-select'>
						<?php
						echo "<option value='0'>Pilih Costcenter</option>";
						foreach ($listCostcenter as $key => $value) {
							echo "<option value='".$value['id_costcenter']."'>".strtoupper($value['nama_costcenter'])."</option>";
						}
						?>
					</select>
				</div>
				<div class="col-md-2 text-bold">Tanggal Planning</div>
				<div class="col-md-2">
					<input type="text" name='tgl_planning' id='tgl_planning' class='form-control input-md' readonly value=''>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2 text-bold">Keterangan</div>
				<div class="col-md-4">
					<textarea name="keterangan" id="keterangan" class='form-control input-md' rows="3"></textarea>
				</div>
				<div class="col-md-2 text-bold">Qty</div>
				<div class="col-md-2">
					<input type="text" name='qty' id='qty' class='form-control input-md' readonly value=''>
				</div>
			</div>
		<hr>
		<h4>Material Non-Mixing</h4>
		<div class="form-group row">
        	<div class="col-md-12">
				<table class="table table-striped table-bordered table-hover table-condensed" width="100%">
					<thead>
						<tr class='bg-blue'>
							<th class="text-center" width='3%'>#</th>
							<th class="text-center">Code</th>
							<th class="text-center">Material Name</th>
							<th class="text-center" width='10%'>Estimasi</th>
							<th class="text-center" width='10%'>Sisa Request</th>
							<th class="text-center" width='10%'>Total Request</th>
							<th class="text-center" width='10%'>Request</th>
							<th class="text-center" width='15%'>Keterangan</th>
						</tr>
					</thead>
					<tbody id='body_req_ftackle'>
						<tr>
							<td colspan='9'>Empty request material.</td>
						</tr>
					</tbody>
					<?php $nomor =200;?>
					<tbody id='body_table'>
						<tr id='add_<?=$nomor?>'>
							<td align='center' colspan='2'></td>
							<td align='left'><button type='button' class='btn btn-sm btn-warning addPart' data-mixing='non' title='Add Material'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Material</button></td>
							<td align='center' colspan='5'></td>
						</tr>
					</tbody>
				</table>
			</div>
        </div>

		<h4>Material Mixing</h4>
		<div class="form-group row">
        	<div class="col-md-12">
				<table class="table table-striped table-bordered table-hover table-condensed" width="100%">
					<thead>
						<tr class='bg-blue'>
							<th class="text-center" width='3%'>#</th>
							<th class="text-center">Code</th>
							<th class="text-center">Material Name</th>
							<th class="text-center" width='10%'>Estimasi</th>
							<th class="text-center" width='10%'>Sisa Request</th>
							<th class="text-center" width='10%'>Total Request</th>
							<th class="text-center" width='10%'>Request</th>
							<th class="text-center" width='15%'>Keterangan</th>
						</tr>
					</thead>
					<tbody id='body_req_ftackle_mixing'>
						<tr>
							<td colspan='9'>Empty request material.</td>
						</tr>
					</tbody>
					<?php $nomor = 300;?>
					<tbody id='body_table2'>
						<tr id='add2_<?=$nomor?>'>
							<td align='center' colspan='2'></td>
							<td align='left'><button type='button' class='btn btn-sm btn-warning addPart' data-mixing='mix' title='Add Material'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Material</button></td>
							<td align='center' colspan='5'></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<div class="form-group row" hidden>
        	<div class="col-md-12">
				<table class="table table-striped table-bordered table-hover table-condensed" width="100%">
					<thead>
						<tr class='bg-blue'>
							<th class="text-center" width='3%'>#</th>
							<th class="text-center">Code</th>
							<th class="text-center">Material Name</th>
							<th class="text-center" width='5%'>Packing</th>
							<th class="text-center" width='10%'>Request</th>
							<th class="text-center" width='6%'>Konversi</th>
							<th class="text-center" width='10%'>Total</th>
							<th class="text-center" width='5%'>Unit</th>
							<th class="text-center" width='15%'>Keterangan</th>
							<th class="text-center" width='4%'>#</th>
						</tr>
					</thead>
					<tbody id='body_req'>
						<tr>
							<td colspan='9'>Empty request material.</td>
						</tr>
					</tbody>
				</table>
			</div>
        </div>
		<!-- <h4>Daftar Material</h4> -->
		<div class="form-group row" hidden>
        	<div class="col-md-12">
				<table id="my-grid2" class="table table-striped table-bordered table-hover table-condensed" width="100%">
					<thead>
						<tr class='bg-blue'>
							<th class="text-center" width='5%'>#</th>
							<th class="text-center">Code</th>
							<th class="text-center">Material Name</th>
							<th class="text-center no-sort" width='7%'>Packing</th>
							<th class="text-center no-sort" width='10%'>Request</th>
							<th class="text-center no-sort" width='20%'>Keterangan</th>
							<th class="text-center no-sort" width='7%'>Option</th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
        </div>
		<div class="form-group row">
			<div class="col-md-6">
				<button type="button" class="btn btn-primary" name="save" id="save">Request</button>
				<button type="button" class="btn btn-danger" style='margin-left:5px;' name="back" id="back">Back</button>
			</div>
        </div>

      	
		</form>
	</div>
</div>


<script src="<?= base_url('assets/js/jquery.maskMoney.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>
<style>
    .datepicker{
        cursor: pointer;
    }
    th, td {
        padding: 5px;
    }
</style>

<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';

	$(document).ready(function(){
		$('.chosen-select').select2();
    	$('.autoNumeric4').autoNumeric('init', {mDec: '4', aPad: false})

		DataTables2();

		let arrayRequest = [];
		let arrayDataCheck = [];

		arrayDataCheck.splice(0,arrayDataCheck.length)
		$(document).on('click','.pindahkan', function(){
			let id = $(this).parent().parent().parent().find('.id').val();
			let code_material = $(this).parent().parent().parent().find('.code_material').val();
			let nm_material = $(this).parent().parent().parent().find('.nm_material').val();
			let packing = $(this).parent().parent().parent().find('.packing').val();
			let satuan = $(this).parent().parent().parent().find('.satuan').val();
			let konversi = $(this).parent().parent().parent().find('.konversi').val();
			let sudah_request = $(this).parent().parent().parent().find('.sudah_request').val().split(",").join("");
			let ket_request = $(this).parent().parent().parent().find('.ket_request').val();

			let check = arrayDataCheck.includes(id);
			// console.log(arrayDataCheck);
			// console.log(check);
			if(check === false){
				let dataArr = {
					'id' : id,
					'code_material' : code_material,
					'nm_material' : nm_material,
					'packing' : packing,
					'satuan' : satuan,
					'konversi' : konversi,
					'qty_unit' : Number(konversi) * Number(sudah_request),
					'sudah_request' : sudah_request,
					'ket_request' : ket_request
				}
				// console.log(dataArr);
				arrayRequest.push(dataArr);
				arrayDataCheck.push(id);
				// console.log(arrayDataCheck);
				viewRequest();
			}
			// else{
			// 	alert('Material sudah ada dalam daftar !!!')
			// }
		});

		$(document).on('click', '.hapus_req', function(){
			let id = $(this).data('id');
			delete arrayRequest[id]
			delete arrayDataCheck[id]
			viewRequest();
		});

		$(document).on('keyup', '.qtyRequest', function(){
			let qtyRequest = getNum($(this).val().split(',').join(''))
			let konversiRequest = getNum($(this).parent().parent().find('.konversiRequest').text().split(',').join(''))
			let unitRequest = qtyRequest * konversiRequest
			$(this).parent().parent().find('.unitRequest').text(unitRequest)
		});

		const viewRequest = () => {
			let DataAppend = "";
			let nomor = 0;
			// console.log(arrayRequest)
			arrayRequest.map((row,idx)=>{
					nomor++

					DataAppend += "<tr>"
						DataAppend += "<td class='text-center'>"+nomor+"</td>"
						DataAppend += "<td>"+row.code_material+"</td>"
						DataAppend += "<td>"+row.nm_material+"</td>"
						DataAppend += "<td class='text-center'>"+row.packing+"</td>"
						DataAppend += "<td>"
							DataAppend += "<input type='hidden' name='detail["+idx+"][id]' value='"+row.id+"'>"
							DataAppend += "<input type='text' name='detail["+idx+"][sudah_request]' class='form-control input-sm text-center autoNumeric qtyRequest' value='"+row.sudah_request+"'>"
						DataAppend += "</td>"
						DataAppend += "<td class='text-center konversiRequest'>"+row.konversi+"</td>"
						DataAppend += "<td class='text-center unitRequest'>"+row.qty_unit+"</td>"
						DataAppend += "<td class='text-center'>"+row.satuan+"</td>"
						DataAppend += "<td>"
							DataAppend += "<input type='text' name='detail["+idx+"][ket_request]' class='form-control input-sm' value='"+row.ket_request+"'>"
						DataAppend += "</td>"
						DataAppend += "<td class='text-center'><button type='button' class='btn btn-danger btn-sm hapus_req' data-id='"+idx+"' title='Delete'><i class='fa fa-trash'></i></button></td>"
					DataAppend += "</tr>"
			})

			$('#body_req').html(DataAppend)
			$('.autoNumeric').autoNumeric('init', {mDec: '4', aPad: false});
		}

    	//back
		$(document).on('click', '#back', function(){
		    window.location.href = base_url + active_controller
		});

		$('#save').click(function(e){
			e.preventDefault();

			var id_so = $('#id_so').val();
			var id_spk = $('#id_spk').val();
			var id_gudang = $('#id_gudang').val();
			var id_costcenter = $('#id_costcenter').val();

			if( id_so == '0'){
				swal({
				title	: "Error Message!",
				text	: 'No SO belum dipilih ...',
				type	: "warning"
				});
				return false;
			}

			if(id_spk == '0'){
				swal({
				title	: "Error Message!",
				text	: 'No SPK belum dipilih ...',
				type	: "warning"
				});
				return false;
			}

			if(id_gudang == '0'){
				swal({
				title	: "Error Message!",
				text	: 'Gudang request belum dipilih ...',
				type	: "warning"
				});
				return false;
			}

			if(id_costcenter == '0'){
				swal({
				title	: "Error Message!",
				text	: 'Tujuan costcenter dipilih ...',
				type	: "warning"
				});
				return false;
			}

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
						var formData 	=new FormData($('#data-form')[0]);
						var baseurl=siteurl+active_controller+'/request';
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
										  title	: "Save Success!",
										  text	: data.pesan,
										  type	: "success",
										  timer	: 7000
										});
										window.location.href = base_url + active_controller
								}else{

									if(data.status == 2){
										swal({
										  title	: "Save Failed!",
										  text	: data.pesan,
										  type	: "warning",
										  timer	: 7000
										});
									}else{
										swal({
										  title	: "Save Failed!",
										  text	: data.pesan,
										  type	: "warning",
										  timer	: 7000
										});
									}

								}
							},
							error: function() {

								swal({
								  title				: "Error Message !",
								  text				: 'An Error Occured During Process. Please try again..',
								  type				: "warning",
								  timer				: 7000
								});
							}
						});
				  } else {
					swal("Cancelled", "Data can be process again :)", "error");
					return false;
				  }
			});
		});

		$(document).on('change', '#id_so', function(){
		    var id_so = $("#id_so").val();
			let nm_product = $(this).find(':selected').data('nama');
			$('#product_name').val(nm_product)

			$.ajax({
				url:siteurl+active_controller+'/get_list_spk',
				method : "POST",
				data : {id_so:id_so},
				dataType : 'json',
				success: function(data){
					$('#id_spk').html(data.option);
				}
			});
		});

		$(document).on('change', '#id_spk', function(){
		    var id_spk 			= $("#id_spk").val();
			let tgl_planning 	= $(this).find(':selected').data('tanggal');
			let qty 			= $(this).find(':selected').data('qty');
			let kode_det 		= $(this).find(':selected').data('kode_det');

			$('#tgl_planning').val(tgl_planning)
			$('#qty').val(qty)

			$.ajax({
				url:siteurl+active_controller+'/get_estimasi_material',
				method : "POST",
				data : {
					kode_det:kode_det,
					qty:qty
				},
				dataType : 'json',
				success: function(data){
					$('#body_req_ftackle').html(data.option);
					$('#body_req_ftackle_mixing').html(data.option2);
					$('.autoNumeric4').autoNumeric('init', {mDec: '4', aPad: false})
				}
			});
		});

		//add part
		$(document).on('click', '.addPart', function(){
			// loading_spinner();
			var get_id 		= $(this).parent().parent().attr('id');
			var mixing 		= $(this).data('mixing');
			var split_id	= get_id.split('_');
			var id 		= parseInt(split_id[1])+1;
			var id_bef 	= split_id[1];
			// console.log(get_id)
			$.ajax({
				url: base_url+active_controller+'/get_add/'+id+'/'+mixing,
				cache: false,
				type: "POST",
				dataType: "json",
				success: function(data){
					if(mixing == 'non'){
						$("#add_"+id_bef).before(data.header);
						$("#add_"+id_bef).remove();
					}
					if(mixing == 'mix'){
						$("#add2_"+id_bef).before(data.header);
						$("#add2_"+id_bef).remove();
					}
					$('.chosen_select').select2({width: '100%'});
					$('.autoNumeric4').autoNumeric('init', {mDec: '4', aPad: false});
					swal.close();
				},
				error: function() {
					swal({
						title				: "Error Message !",
						text				: 'Connection Time Out. Please try again..',
						type				: "warning",
						timer				: 3000,
						showCancelButton	: false,
						showConfirmButton	: false,
						allowOutsideClick	: false
					});
				}
			});
		});

	   //delete part
		$(document).on('click', '.delPart', function(){
			var get_id 		= $(this).parent().parent().attr('class');
			$("."+get_id).remove();
			sumMaterial()
		});

	});

	function DataTables2(){
		var dataTable = $('#my-grid2').DataTable({
			"processing": true,
			"serverSide": true,
			"stateSave" : true,
			"autoWidth": false,
			"destroy": true,
			"responsive": true,
			"aaSorting": [[ 1, "asc" ]],
			"columnDefs": [ {
				"targets": 'no-sort',
				"orderable": false,
			}], 
			"sPaginationType": "simple_numbers",
			"iDisplayLength": 10,
			"aLengthMenu": [[10, 20, 50, 100, 150], [10, 20, 50, 100, 150]],
			"ajax":{
				url : base_url + active_controller+'/server_side_request_produksi',
				type: "post",
				// data: function(d){
				// 	d.no_ipp = no_ipp,
				// 	d.pusat = pusat
				// },
				cache: false,
				error: function(){
					$(".my-grid-error").html("");
					$("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
					$("#my-grid_processing").css("display","none");
				}
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
