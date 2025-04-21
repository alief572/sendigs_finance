<?php

$id          	= (!empty($header[0]->id)) ? $header[0]->id : '';
$kode_supplier  = (!empty($header[0]->kode_supplier)) ? $header[0]->kode_supplier : '';
$nama   		= (!empty($header[0]->nama)) ? $header[0]->nama : '';
$id_country   	= (!empty($header[0]->id_country)) ? $header[0]->id_country : 'IDN';
$id_provinsi   	= (!empty($header[0]->id_provinsi)) ? $header[0]->id_provinsi : '';
$id_currency   	= (!empty($header[0]->id_currency)) ? $header[0]->id_currency : 'IDR';
$telp   		= (!empty($header[0]->telp)) ? $header[0]->telp : '';
$telp2   		= (!empty($header[0]->telp2)) ? $header[0]->telp2 : '';
$fax   			= (!empty($header[0]->fax)) ? $header[0]->fax : '';
$email   		= (!empty($header[0]->email)) ? $header[0]->email : '';
$email2   		= (!empty($header[0]->email2)) ? $header[0]->email2 : '';
$email3   		= (!empty($header[0]->email3)) ? $header[0]->email3 : '';
$contact   		= (!empty($header[0]->contact)) ? $header[0]->contact : '';
$contact_person	= (!empty($header[0]->contact_person)) ? $header[0]->contact_person : '';
$tax_number   	= (!empty($header[0]->tax_number)) ? $header[0]->tax_number : '';
$address   		= (!empty($header[0]->address)) ? $header[0]->address : '';
$tax_address   	= (!empty($header[0]->tax_address)) ? $header[0]->tax_address : '';
$note   		= (!empty($header[0]->note)) ? $header[0]->note : '';
$bank_account   = (!empty($header[0]->bank_account)) ? $header[0]->bank_account : '';

// print_r($header);
?>

<form id="data-form" method="post">
	<div class="row">
		<div class="col-sm-12">
			<div class="col-sm-6">
				<div class="form-group row">
					<div class="col-md-4">
						<label for="customer">Supplier Name <span class='text-red'>*</span></label>
					</div>
					<div class="col-md-8">
						<input type="hidden" name="id" id="id" value="<?= $id; ?>">
						<input type="hidden" name="kode_supplier" id="kode_supplier" value="<?= $kode_supplier; ?>">
						<input type="text" name="nama" id="nama" class='form-control input-md' placeholder='Supplier Name' value="<?= $nama; ?>">
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-4">
						<label for="customer">Country</label>
					</div>
					<div class="col-md-8">
						<select id="id_country" name="id_country" class="form-control select">
							<?php foreach ($country as $val => $value) {
								$sel = ($value['iso3'] == $id_country) ? 'selected' : '';
							?>
								<option value="<?= $value['iso3']; ?>" <?= $sel; ?>><?= strtoupper($value['name']) ?></option>
							<?php } ?>
						</select>
					</div>
				</div>
			</div>
			<div class="col-sm-6">
				<div class="form-group row">
					<div class="col-md-4">
						<label for="customer">Provinsi</label>
					</div>
					<div class="col-md-8">
						<select id="id_provinsi" name="id_provinsi" class="form-control select">
							<option value="0">Select Provinsi</option>
							<?php foreach ($provinsi as $val => $value) {
								$sel = ($value['id_prov'] == $id_provinsi) ? 'selected' : '';
							?>
								<option value="<?= $value['id_prov']; ?>" <?= $sel; ?>><?= strtoupper($value['nama']) ?></option>
							<?php } ?>
						</select>
					</div>
				</div>
			</div>
		</div>

		<div class="col-sm-12">
			<div class="col-sm-6">
				<div class="form-group row">
					<div class="col-md-4">
						<label for="customer">Telephone</label>
					</div>
					<div class="col-md-8">
						<input type="text" name="telp" id="telp" class='form-control input-md' placeholder='Telephone' value="<?= $telp; ?>">
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-4">
						<label for="customer">Telephone 2</label>
					</div>
					<div class="col-md-8">
						<input type="text" name="telp2" id="telp2" class='form-control input-md' placeholder='Telephone 2' value="<?= $telp2; ?>">
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-4">
						<label for="customer">Fax</label>
					</div>
					<div class="col-md-8">
						<input type="text" name="fax" id="fax" class='form-control input-md' placeholder='Fax' value="<?= $fax; ?>">
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-4">
						<label for="customer">Address</label>
					</div>
					<div class="col-md-8">
						<textarea name='address' id='address' class='form-control input-md' placeholder='Address' rows='2'><?= $address; ?></textarea>
					</div>
				</div>
			</div>
			<div class="col-sm-6">
				<div class="form-group row">
					<div class="col-md-4">
						<label for="customer">Email</label>
					</div>
					<div class="col-md-8">
						<input type="text" name="email" id="email" class='form-control input-md' placeholder='Email' value="<?= $email; ?>">
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-4">
						<label for="customer">Email 2</label>
					</div>
					<div class="col-md-8">
						<input type="text" name="email2" id="email2" class='form-control input-md' placeholder='Email 2' value="<?= $email2; ?>">
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-4">
						<label for="customer">Email 3</label>
					</div>
					<div class="col-md-8">
						<input type="text" name="email3" id="email3" class='form-control input-md' placeholder='Email 3' value="<?= $email3; ?>">
					</div>
				</div>
			</div>
		</div>

		<div class="col-sm-12">
			<div class="col-sm-6">
				<div class="form-group row">
					<div class="col-md-4">
						<label for="customer">Contact</label>
					</div>
					<div class="col-md-8">
						<input type="text" name="contact" id="contact" class='form-control input-md' placeholder='Contact' value="<?= $contact; ?>">
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-4">
						<label for="customer">Tax Number</label>
					</div>
					<div class="col-md-8">
						<input type="text" name="tax_number" id="tax_number" class='form-control input-md' placeholder='Tax Number' value="<?= $tax_number; ?>">
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-4">
						<label for="customer">Currency</label>
					</div>
					<div class="col-md-8">
						<select id="id_currency" name="id_currency" class="form-control input-md chosen-select">
							<!-- <option value="0">Select Currency</option> -->
							<?php foreach ($currency as $val => $value) {
								$sel = ($value['kode'] == $id_currency) ? 'selected' : '';
							?>
								<option value="<?= $value['kode']; ?>" <?= $sel; ?>><?= strtoupper($value['kode'] . ' - ' . $value['mata_uang']) ?></option>
							<?php } ?>
						</select>
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-4">
						<label for="customer">Note</label>
					</div>
					<div class="col-md-8">
						<textarea name='note' id='note' class='form-control input-md' placeholder='Note' rows='2'><?= $note; ?></textarea>
					</div>
				</div>
			</div>
			<div class="col-sm-6">
				<div class="form-group row">
					<div class="col-md-4">
						<label for="customer">Contact Person</label>
					</div>
					<div class="col-md-8">
						<input type="text" name="contact_person" id="contact_person" class='form-control input-md' placeholder='Contact Person' value="<?= $contact_person; ?>">
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-4">
						<label for="customer">Tax Address</label>
					</div>
					<div class="col-md-8">
						<textarea name='tax_address' id='tax_address' class='form-control input-md' placeholder='Tax Address' rows='2'><?= $tax_address; ?></textarea>
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-4">
						<label for="customer">Bank Account</label>
					</div>
					<div class="col-md-8">
						<input type="text" name="bank_account" id="bank_account" class='form-control input-md' placeholder='Bank Account' value="<?= $bank_account; ?>">
					</div>
				</div>
			</div>
		</div>

		<center>
			<button type="submit" class="btn btn-success" ame="save" id="save"><i class="fa fa-save"></i> Save</button>
		</center>
	</div>
</form>


<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<style media="screen">
	.datepicker {
		cursor: pointer;
		padding-left: 12px;
	}
</style>
<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url = '<?php echo base_url(); ?>';
	var active_controller = '<?php echo ($this->uri->segment(1)); ?>';

	$(document).ready(function() {
		$('.chosen-select').select2();
		$(".datepicker").datepicker();
		$(".autoNumeric4").autoNumeric('init', {
			mDec: '4',
			aPad: false
		});

		//add part
		$(document).on('click', '.addPart', function() {
			// loading_spinner();
			var get_id = $(this).parent().parent().attr('id');
			// console.log(get_id);
			var split_id = get_id.split('_');
			var id = parseInt(split_id[1]) + 1;
			var id_bef = split_id[1];

			$.ajax({
				url: base_url + active_controller + '/get_add/' + id,
				cache: false,
				type: "POST",
				dataType: "json",
				success: function(data) {
					$("#add_" + id_bef).before(data.header);
					$("#add_" + id_bef).remove();
					$('.chosen_select').select2({
						width: '100%'
					});
					$('.autoNumeric4').autoNumeric('init', {
						mDec: '4',
						aPad: false
					});
					swal.close();
				},
				error: function() {
					swal({
						title: "Error Message !",
						text: 'Connection Time Out. Please try again..',
						type: "warning",
						timer: 3000,
						showCancelButton: false,
						showConfirmButton: false,
						allowOutsideClick: false
					});
				}
			});
		});

		//delete part
		$(document).on('click', '.delPart', function() {
			var get_id = $(this).parent().parent().attr('class');
			$("." + get_id).remove();
		});

		//add part
		$(document).on('click', '#back', function() {
			window.location.href = base_url + active_controller;
		});

		$('#save').click(function(e) {
			e.preventDefault();
			var nama = $('#nama').val();

			if (nama == '') {
				swal({
					title: "Error Message!",
					text: 'Supplier name empty, select first ...',
					type: "warning"
				});

				$('#save').prop('disabled', false);
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
						var formData = new FormData($('#data-form')[0]);
						var baseurl = base_url + active_controller + '/add'
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
										timer: 3000,
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
											timer: 3000,
											showCancelButton: false,
											showConfirmButton: false,
											allowOutsideClick: false
										});
									} else {
										swal({
											title: "Save Failed!",
											text: data.pesan,
											type: "warning",
											timer: 3000,
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
		});

	});
</script>