<?php
$tanggal = date('Y-m-d');
?>

<div class="box box-primary">
	<div class="box-body">
		<form id="data-form" method="post">
			<div class="col-sm-12">
				<div class="input_fields_wrap2">
					<div class="row">
						<center><label for="customer">
								<h3>Incoming</h3>
							</label></center>
						<div class="col-sm-12">
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="customer">NO.Dokumen</label>
									</div>
									<div class="col-md-8">
										<input type="text" class="form-control" id="id_incoming" required name="id_incoming" readonly placeholder="No.Dokumen">
									</div>
								</div>
							</div>
							<!-- <div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="id_customer">Supplier</label>
									</div>
									<div class="col-md-8">
										<select id="id_suplier" name="id_suplier" class="form-control input-md chosen-select" required>
											<option value="">--Pilih--</option>
											<?php foreach ($results['suplier'] as $suplier) { ?>
												<option value="<?= $suplier->id_suplier ?>"><?= $suplier->name_suplier ?></option>
											<?php } ?>
										</select>
									</div>
								</div>
							</div> -->
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="id_customer">Kurs Incoming</label>
									</div>
									<div class="col-md-8">
										<input type="text" class="form-control" id="kurs" required name="kurs">
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-12">
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="customer">Tanggal Kedatangan</label>
									</div>
									<div class="col-md-8">
										<input type="date" class="form-control" value="<?= $tanggal ?>" id="tanggal" required name="tanggal">
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="id_customer">Keterangan</label>
									</div>
									<div class="col-md-8">
										<input type="text" class="form-control" id="ket" required name="ket">
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-12">
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="customer">PIC</label>
									</div>
									<div class="col-md-8">
										<input type="text" class="form-control" id="pic" required name="pic" required>
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label id="lbl_inv">Packing List</label>
									</div>
									<div class="col-md-8">
										<input type="text" class="form-control" id="no_invoice" required name="no_invoice">
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-12">
							<div class="col-sm-6">
								<div class="form-group row" id='pib_label'>
									<div class="col-md-4">
										<label for="customer">PIB</label>
									</div>
									<div class="col-md-8">
										<input type="text" class="form-control" id="pib" required name="pib" required>
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="id_customer">Mata Uang</label>
									</div>
									<div class="col-md-8">
										<select id="matauang" name="matauang" class='form-control input-md chosen-select' required>
											<option value="" selected='selected'>--PILIH--</option>
											<?php foreach ($results['matauang'] as $supplier) { ?>
												<option value="<?= $supplier->kode ?>"><?= strtoupper(strtolower($supplier->kode)) ?></option>
											<?php } ?>
										</select>
									</div>
								</div>
							</div>
						</div>

						<div class="col-sm-12">
							<div class="col-sm-6">
								<div class="form-group row" id='supplier_label'>
									<div class="col-md-4">
										<label for="supplier">Supplier</label>
									</div>
									<div class="col-md-8">
										<select name="supplier" id="supplier" class="form-control chosen-select" required>
											<option value="">- Supplier -</option>
											<?php
											foreach ($results['list_supplier'] as $supplier) {
												echo '<option value="' . $supplier->kode_supplier . '">' . $supplier->nama . '</option>';
											}
											?>
										</select>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="form-group row" id="fortombol">
						<button type='button' class='btn btn-sm btn-success' title='Ambil' id='tbh_ata' onClick="addPO('1');"><i class='fa fa-plus'></i>Add</button>
					</div>
					<div class="form-group row" id="Form_Po">

					</div>
				</div>
				<center>
					<button type="submit" class="btn btn-success btn-sm" name="save" id="simpan-com"><i class="fa fa-save"></i>Simpan</button>
				</center>
			</div>
		</form>
	</div>
</div>



<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js'); ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js'); ?>"></script>
<script src="<?= base_url('assets/js/jquery.maskMoney.js') ?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script>
	$(function() {
		$('.chosen-select').select2({
			width: '100%'
		});

		// $('#tanggal').datepicker({
		// format : 'yyyy-mm-dd'
		// minDate: 0
		// });
	});
</script>
<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url = '<?php echo base_url(); ?>';
	var active_controller = '<?php echo ($this->uri->segment(1)); ?>';
	$(document).ready(function() {
		var max_fields2 = 10; //maximum input boxes allowed
		var wrapper2 = $(".input_fields_wrap2"); //Fields wrapper
		var add_button2 = $(".add_field_button2"); //Add button ID

		$('#simpan-com').click(function(e) {
			e.preventDefault();
			var deskripsi = $('#deskripsi').val();
			var id_gudang = $('#id_gudang').val();
			var pic = $('#pic').val();
			var kurs = $('#kurs').val();
			var matauang = $('#matauang').val();
			var data, xhr;
			if (pic == '' || pic == null) {
				swal("Warning", "PIC Tidak Boleh Kosong :)", "error");
				return false;
			} else if (kurs == '' || kurs == null) {
				swal("Warning", "Kurs Tidak Boleh Kosong :)", "error");
				return false;
			} else if (matauang == '' || matauang == null) {
				swal("Warning", "Mata Uang Tidak Boleh Kosong :)", "error");
				return false;
			} else {
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

							var formData = new FormData($('#data-form')[0]);
							var baseurl = siteurl + 'incoming/SaveNew';
							$.ajax({
								url: baseurl,
								type: "POST",
								data: formData,
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
											timer: 7000,
											showCancelButton: false,
											showConfirmButton: false,
											allowOutsideClick: false
										});
										window.location.href = base_url + active_controller;
									} else {

										if (data.status == 2) {
											swal({
												title: "Save Failed!",
												text: data.pesan,
												type: "warning",
												timer: 7000,
												showCancelButton: false,
												showConfirmButton: false,
												allowOutsideClick: false
											});
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

									}
								},
								error: function() {

									swal({
										title: "Error Message !",
										text: 'An Error Occured During Process. Please try again..',
										type: "warning",
										timer: 7000,
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
			}
		});
		//OPPA ARWANT JSON
		$(document).on('click', '.delete_header', function() {
			var nomor = $(this).data('nomor');

			$('#po_' + nomor).remove();
		});

		$(document).on('click', '.cancelSubPart', function() {
			var no1 = $(this).data('no1');
			var no2 = $(this).data('no2');

			$('#trmaterial_' + no1 + '_' + no2).remove();
		});

		$(document).on('change', '#id_suplier', function() {
			let id_suplier = $(this).val();
			$.ajax({
				url: siteurl + 'incoming/checkSupplier',
				type: "POST",
				data: {
					'supplier': id_suplier
				},
				cache: false,
				dataType: 'json',
				success: function(data) {
					let lokasi = data.lokasi;
					if (lokasi == 'local') {
						$('#lbl_inv').html('DO');
						$('#pib_label').hide();
					} else {
						$('#lbl_inv').html('Packing List');
						$('#pib_label').show();
					}
				}
			});
		});
		//END OPPA ARWANT JSON


	});

	function get_material() {
		var jumlah = $('#data_request').find('tr').length;
		var no_po = $("#no_po").val();
		$.ajax({
			type: "GET",
			url: siteurl + 'incoming/GetMaterial',
			data: "jumlah=" + jumlah + "&no_po=" + no_po,
			success: function(html) {
				$("#data_request").html(html);
			}
		});
	}

	function addPO(id) {
		var id_suplier = $("#id_suplier").val();
		$.ajax({
			type: "GET",
			url: siteurl + 'incoming/FormPo',
			data: "id_suplier=" + id_suplier + "&id=" + id,
			success: function(html) {
				$("#Form_Po").append(html);
				$(".bilangan-desimal").maskMoney();
				$(".chosen-select").select2({
					width: '100%'
				});
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'incoming/GantiTombol',
			data: "&id=" + id,
			success: function(html) {
				$("#fortombol").html(html);
			}
		});
	}

	function HapusItem(id) {

		$('#Form_Po #po_' + id).remove();

	}

	function TambahMaterial(id) {
		var nomor = Number(id);
		var nopo = $("#dt_nopo_" + nomor).val();
		var no = $("#pancingan_" + nomor).val();
		// console.log(nomor)
		$.ajax({
			type: "GET",
			url: siteurl + 'incoming/TambahData',
			data: "nopo=" + nopo + "&id=" + nomor + "&no=" + no,
			success: function(html) {
				$("#data_request_" + nomor).append(html);
				$(".bilangan-desimal").maskMoney();
				$(".chosen-select").select2({
					width: '100%'
				});
				$('.autoNumeric').autoNumeric();
			}
		});
	}

	$(document).on('click', '.repeatSubPart', function() {
		var nopo = $(this).data('id');
		var no = $(this).data('no2');
		var id = $(this).data('no1');
		var idroll = $(this).data('lot');
		$.ajax({
			type: "GET",
			url: siteurl + 'incoming/TambahDataRepeat',
			data: "nopo=" + nopo + "&id=" + id + "&no=" + no + "&idroll=" + idroll,
			success: function(html) {
				$("#trmaterial_" + id + "_" + no).after(html);
				$(".bilangan-desimal").maskMoney();
				$(".chosen-select").select2({
					width: '100%'
				});
				$('.autoNumeric').autoNumeric();
				$("#tombol" + no).hide();
			}
		});
	});

	function HitungHarga(id) {
		var dt_qty = $("#dt_qty_" + id).val();
		var dt_width = $("#dt_width_" + id).val();
		var dt_hargasatuan = $("#dt_hargasatuan_" + id).val();
		$.ajax({
			type: "GET",
			url: siteurl + 'incoming/HitungHarga',
			data: "dt_hargasatuan=" + dt_hargasatuan + "&dt_qty=" + dt_qty + "&id=" + id,
			success: function(html) {
				$("#jumlahharga_" + id).html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/TotalWeight',
			data: "dt_width=" + dt_width + "&dt_qty=" + dt_qty + "&id=" + id,
			success: function(html) {
				$("#totalwidth_" + id).html(html);
			}
		});
	}

	function get_kurs() {
		var loi = $("#loi").val();
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/CariKurs',
			data: "loi=" + loi,
			success: function(html) {
				$("#kurs_place").html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/FormInputKurs',
			data: "loi=" + loi,
			success: function(html) {
				$("#input_kurs").html(html);
			}
		});
	}

	function CariProperties(id) {
		var idpr = $("#dt_idpr_" + id).val();
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/CariIdMaterial',
			data: "idpr=" + idpr + "&id=" + id,
			success: function(html) {
				$("#idmaterial_" + id).html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/CariNamaMaterial',
			data: "idpr=" + idpr + "&id=" + id,
			success: function(html) {
				$("#namaterial_" + id).html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/CariPanjangMaterial',
			data: "idpr=" + idpr + "&id=" + id,
			success: function(html) {
				$("#panjang_" + id).html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/CariLebarMaterial',
			data: "idpr=" + idpr + "&id=" + id,
			success: function(html) {
				$("#lebar_" + id).html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/CariDescripitionMaterial',
			data: "idpr=" + idpr + "&id=" + id,
			success: function(html) {
				$("#description_" + id).html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/CariQtyMaterial',
			data: "idpr=" + idpr + "&id=" + id,
			success: function(html) {
				$("#qty_" + id).html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/CariweightMaterial',
			data: "idpr=" + idpr + "&id=" + id,
			success: function(html) {
				$("#width_" + id).html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/CariTweightMaterial',
			data: "idpr=" + idpr + "&id=" + id,
			success: function(html) {
				$("#totalwidth_" + id).html(html);
			}
		});
	}

	function LockMaterial(id) {
		var idpr = $("#dt_idpr_" + id).val();
		var idmaterial = $("#dt_idmaterial_" + id).val();
		var namaterial = $("#dt_namamaterial_" + id).val();
		var description = $("#dt_description_" + id).val();
		var qty = $("#dt_qty_" + id).val();
		var width = $("#dt_width_" + id).val();
		var totalwidth = $("#dt_totalwidth_" + id).val();
		var hargasatuan = $("#dt_hargasatuan_" + id).val();
		var diskon = $("#dt_diskon_" + id).val();
		var pajak = $("#dt_pajak_" + id).val();
		var panjang = $("#dt_panjang_" + id).val();
		var lebar = $("#dt_lebar_" + id).val();
		var jumlahharga = $("#dt_jumlahharga_" + id).val();
		var note = $("#dt_note_" + id).val();
		var subtotal = $("#subtotal").val();
		var hargatotal = $("#hargatotal").val();
		var diskontotal = $("#diskontotal").val();
		var taxtotal = $("#taxtotal").val();
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/LockMatrial',
			data: "idpr=" + idpr + "&id=" + id + "&idmaterial=" + idmaterial + "&width=" + width + "&panjang=" + panjang + "&lebar=" + lebar + "&totalwidth=" + totalwidth + "&namaterial=" + namaterial + "&description=" + description + "&qty=" + qty + "&hargasatuan=" + hargasatuan + "&diskon=" + diskon + "&pajak=" + pajak + "&jumlahharga=" + jumlahharga + "&note=" + note,
			success: function(html) {
				$("#trmaterial_" + id).html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/CariTHarga',
			data: "idpr=" + idpr + "&id=" + id + "&hargatotal=" + hargatotal + "&idmaterial=" + idmaterial + "&namaterial=" + namaterial + "&description=" + description + "&qty=" + qty + "&hargasatuan=" + hargasatuan + "&diskon=" + diskon + "&pajak=" + pajak + "&jumlahharga=" + jumlahharga + "&note=" + note,
			success: function(html) {
				$("#ForHarga").html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/CariTDiskon',
			data: "idpr=" + idpr + "&id=" + id + "&diskontotal=" + diskontotal + "&idmaterial=" + idmaterial + "&namaterial=" + namaterial + "&description=" + description + "&qty=" + qty + "&hargasatuan=" + hargasatuan + "&diskon=" + diskon + "&pajak=" + pajak + "&jumlahharga=" + jumlahharga + "&note=" + note,
			success: function(html) {
				$("#ForDiskon").html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/CariTPajak',
			data: "idpr=" + idpr + "&id=" + id + "&taxtotal=" + taxtotal + "&idmaterial=" + idmaterial + "&namaterial=" + namaterial + "&description=" + description + "&qty=" + qty + "&hargasatuan=" + hargasatuan + "&diskon=" + diskon + "&pajak=" + pajak + "&jumlahharga=" + jumlahharga + "&note=" + note,
			success: function(html) {
				$("#ForTax").html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/CariTSum',
			data: "idpr=" + idpr + "&id=" + id + "&hargatotal=" + hargatotal + "&diskontotal=" + diskontotal + "&taxtotal=" + taxtotal + "&idmaterial=" + idmaterial + "&namaterial=" + namaterial + "&description=" + description + "&qty=" + qty + "&hargasatuan=" + hargasatuan + "&diskon=" + diskon + "&pajak=" + pajak + "&jumlahharga=" + jumlahharga + "&note=" + note,
			success: function(html) {
				$("#ForSum").html(html);
			}
		});
	}

	function CancelItem(id) {
		var idpr = $("#dt_idpr_" + id).val();
		var idmaterial = $("#dt_idmaterial_" + id).val();
		var namaterial = $("#dt_namamaterial_" + id).val();
		var description = $("#dt_description_" + id).val();
		var qty = $("#dt_qty_" + id).val();
		var hargasatuan = $("#dt_hargasatuan_" + id).val();
		var diskon = $("#dt_diskon_" + id).val();
		var pajak = $("#dt_pajak_" + id).val();
		var jumlahharga = $("#dt_jumlahharga_" + id).val();
		var note = $("#dt_note_" + id).val();
		var subtotal = $("#subtotal").val();
		var hargatotal = $("#hargatotal").val();
		var diskontotal = $("#diskontotal").val();
		var taxtotal = $("#taxtotal").val();
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/CariMinHarga',
			data: "idpr=" + idpr + "&id=" + id + "&hargatotal=" + hargatotal + "&idmaterial=" + idmaterial + "&namaterial=" + namaterial + "&description=" + description + "&qty=" + qty + "&hargasatuan=" + hargasatuan + "&diskon=" + diskon + "&pajak=" + pajak + "&jumlahharga=" + jumlahharga + "&note=" + note,
			success: function(html) {
				$("#ForHarga").html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/CariMinDiskon',
			data: "idpr=" + idpr + "&id=" + id + "&diskontotal=" + diskontotal + "&idmaterial=" + idmaterial + "&namaterial=" + namaterial + "&description=" + description + "&qty=" + qty + "&hargasatuan=" + hargasatuan + "&diskon=" + diskon + "&pajak=" + pajak + "&jumlahharga=" + jumlahharga + "&note=" + note,
			success: function(html) {
				$("#ForDiskon").html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/CariMinPajak',
			data: "idpr=" + idpr + "&id=" + id + "&taxtotal=" + taxtotal + "&idmaterial=" + idmaterial + "&namaterial=" + namaterial + "&description=" + description + "&qty=" + qty + "&hargasatuan=" + hargasatuan + "&diskon=" + diskon + "&pajak=" + pajak + "&jumlahharga=" + jumlahharga + "&note=" + note,
			success: function(html) {
				$("#ForTax").html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/CariMinSum',
			data: "idpr=" + idpr + "&id=" + id + "&hargatotal=" + hargatotal + "&diskontotal=" + diskontotal + "&taxtotal=" + taxtotal + "&idmaterial=" + idmaterial + "&namaterial=" + namaterial + "&description=" + description + "&qty=" + qty + "&hargasatuan=" + hargasatuan + "&diskon=" + diskon + "&pajak=" + pajak + "&jumlahharga=" + jumlahharga + "&note=" + note,
			success: function(html) {
				$("#ForSum").html(html);
			}
		});
		$('#data_request #trmaterial_' + id).remove();
	}

	function HapusItem(id) {
		$('#data_request #trmaterial_' + id).remove();

	}




	function cariPanjang(id, no) {

		var beratpackinglist = getNum($("#dt_widthrecive" + id + "_" + no).val().split(",").join(""));
		var thickness = getNum($("#dt_thickness_" + id + "_" + no).val());
		var width = getNum($("#dt_weight_" + id + "_" + no).val());
		var density = getNum($("#dt_density_" + id + "_" + no).val());


		console.log(beratpackinglist);
		console.log(thickness);
		console.log(width);
		console.log(density);


		var panjang = beratpackinglist / (thickness * width * density);



		$("#dt_panjang2_" + id + "_" + no).val(number_format(panjang * 1000));


	}

	function cariSelisih(id, no) {

		var beratpackinglist = getNum($("#dt_widthrecive" + id + "_" + no).val().split(",").join(""));
		var berataktual = getNum($("#dt_aktual_" + id + "_" + no).val().split(",").join(""));






		var selisih = beratpackinglist - berataktual



		$("#dt_selisih_" + id + "_" + no).val(number_format(selisih * -1));


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

	function getNum(val) {
		if (isNaN(val) || val == '') {
			return 0;
		}
		return parseFloat(val);
	}
</script>