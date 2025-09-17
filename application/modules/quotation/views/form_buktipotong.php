<?= form_open($this->uri->uri_string(),array('id'=>'frm_data','name'=>'frm_data','role'=>'form','class'=>'form-horizontal')) ?>
<input type="hidden" id="kd_pembayaran" name="kd_pembayaran" value="<?php echo $kodebayar; ?>">
<div class="tab-content">
	<div class="tab-pane active">
		<div class="box box-primary">
			<div class="box-body">
				<div class="form-group ">
					<label class="col-sm-2 control-label">Nomor Bukti Potong<b class="text-red">*</b></label>
					<div class="col-sm-4">
						<input type="text" class="form-control" id="no_bukti_potong" name="no_bukti_potong" value="" required>
					</div>
					<label class="col-sm-2 control-label">Tanggal Terima<b class="text-red">*</b></label>
					<div class="col-sm-4">
						<input type="text" class="form-control tanggal" id="tgl_terima" name="tgl_terima" value="" required>
					</div>
				</div>
				<div class="form-group ">
					<label class="col-sm-2 control-label">No Invoice<b class="text-red">*</b></label>
					<div class="col-sm-4">
						<select name="no_invoice" id="no_invoice" class="form-control" required>
							<option value="">Select Invoice</option>
							<?php
							if($noinvoice){
								foreach($noinvoice as $keys){
									echo '<option value="'.$keys->no_invoice.'">'.$keys->no_invoice.'</option>';
								}
							}
							?>
						</select>
					</div>
				</div>
				<div class="box-footer">
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10">
							<button type="submit" name="save" class="btn btn-success btn-sm" id="submit"><i class="fa fa-save">&nbsp;</i>Simpan</button>
						</div>
					</div>
				</div>
			</div>
			<div class="box-footer">
			<table class="table"><tr><th>No Invoice</th><th>No Bukti potong</th><th>Tanggal terima</th></tr>
			<?php 
			if($buktipotong){
				foreach($buktipotong as $keys){
					echo '<tr><td>'.$keys->no_invoice.'</td><td>'.$keys->no_bukti_potong.'</td><td>'.$keys->tgl_terima.'</td></tr>';
				}
			}
			?>
			</table>
			</div>
		</div>
	</div>
</div>
<?= form_close() ?>
<script type="text/javascript">
	$(function () {
		$(".tanggal").datepicker({
			todayHighlight: true,
			format : "yyyy-mm-dd",
			showInputs: true,
			autoclose:true,
			endDate: '0',
		});
	});
	var url_save = base_url + active_controller+'penerimaan/save_buktipotong/';
    $('#frm_data').on('submit', function(e){
        e.preventDefault();
		var errors="";
		if($("#no_bukti_potong").val()=="") errors="Nomor bukti potong tidak boleh kosong";
		if($("#tgl_terima").val()=="") errors="Tanggal terima tidak boleh kosong";
		if($("#no_invoice").val()=="") errors="Nomor invoice tidak boleh kosong";
		if(errors==""){

        swal({
          title: "Peringatan !",
          text: "Pastikan data sudah lengkap dan benar",
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: "#DD6B55",
          confirmButtonText: "Ya, simpan!",
          cancelButtonText: "Batal!",
          closeOnConfirm: false,
          closeOnCancel: true
        },
        function(isConfirm){
			if(isConfirm) {
				var formdata = $("#frm_data").serialize();
				$.ajax({
					url: siteurl+"penerimaan/save_buktipotong",
					dataType : "json",
					type: 'POST',
					data: formdata,
					success: function(data){
						if(data.status == 1){
						swal({
						  title	: "Save Success!",
						  text	: data.pesan,
						  type	: "success",
						  timer	: 15000,
						  showCancelButton	: false,
						  showConfirmButton	: false,
						  allowOutsideClick	: false
						});
						window.location.href = base_url + active_controller;
					  }else{
						if(data.status == 2){
						  swal({
							title	: "Save Failed!",
							text	: data.pesan,
							type	: "warning",
							timer	: 10000,
							showCancelButton	: false,
							showConfirmButton	: false,
							allowOutsideClick	: false
						  });
						}else{
						  swal({
							title	: "Save Failed!",
							text	: data.pesan,
							type	: "warning",
							timer	: 10000,
							showCancelButton	: false,
							showConfirmButton	: false,
							allowOutsideClick	: false
						  });
						}

					  }
					},
					error: function(){
						swal({
							title: "Gagal!",
							text: "Batal Proses, Data bisa diproses nanti",
							type: "error",
							timer: 1500,
							showConfirmButton: false
						});
					}
				});
			}
        });


		}else{
			swal(errors);
			return false;
		}
    });
</script>
