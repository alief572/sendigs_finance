<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post" autocomplete="off"><br>
		<div class="form-group row">
        	<div class="col-md-12">
				<table width='50%' cellpadding='2'>
					<tr>
						<th width='30%'>Request Ke <span class='text-danger'>*</span></th>
						<td>
                            <select name='id_gudang' id='id_gudang' class='form-control input-sm chosen-select'>
                                <?php
								echo "<option value='0'>Select Gudang</option>";
                                foreach ($listGudang as $key => $value) {
                                    echo "<option value='".$value['id']."'>".$value['nm_gudang']."</option>";
                                }
                                ?>
                            </select>
                        </td>
					</tr>
					<tr>
						<th>Ke Costcenter <span class='text-danger'>*</span></th>
						<td>
                            <select name='id_costcenter' id='id_costcenter' class='form-control input-sm chosen-select'>
                                <option value='0'>Select Costcenter</option>
                                <?php
                                foreach ($listCostcenter as $key => $value) {
                                    echo "<option value='".$value['id_costcenter']."'>".strtoupper($value['nama_costcenter'])."</option>";
                                }
                                ?>
                            </select>
                        </td>
					</tr>
					<tr>
						<th>Keterangan</th>
						<td>
                            <textarea name="keterangan" id="keterangan" class='form-control input-sm' rows="3"></textarea>
                        </td>
					</tr>
				</table>
				<!-- <input type="hidden" id='id_uniq' name='id_uniq' value='<?=$getData[0]['id_uniq']?>'> -->
			</div>
        </div>
		<hr>
		<h4>Daftar Permintaan</h4>
		<div class="form-group row">
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
		<h4>Daftar Material</h4>
		<div class="form-group row">
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

			var id_gudang = $('#id_gudang').val();
			var id_costcenter = $('#id_costcenter').val();

			if( id_gudang == '0'){
				swal({
				title	: "Error Message!",
				text	: 'Gudang belum dipilih ...',
				type	: "warning"
				});
				return false;
			}

			if( id_costcenter == '0'){
				swal({
				title	: "Error Message!",
				text	: 'Costcenter belum dipilih ...',
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
