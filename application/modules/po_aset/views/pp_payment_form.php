<div class="nav-tabs-area">
    <div class="tab-content">
        <div class="tab-pane active" id="area">
            <div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
            <div class="box box-primary">
            <?= form_open(base_url().'po_aset/save_payment_pp',array('id'=>'frm_data','name'=>'frm_data','role'=>'form','class'=>'form-horizontal')) ?>
				<?php  if(isset($data->id)){$type='edit';}?>
				<input type="hidden" id="type" name="type" value="<?= isset($type) ? $type : 'add' ?>">
				<input type="hidden" id="id" name="id" value="<?php echo set_value('id', isset($data->id) ? $data->id : ''); ?>">
                <div class="box-body">
					<div class="form-group ">
						<label for="no_pp" class="col-sm-2 control-label">No Permintaan Pembayaran</label>
						<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-list"></i></span>
								<input type="text" class="form-control" id="no_pp" name="no_pp" value="<?php echo $data->no_pp; ?>" readonly tabindex="-1">
							</div>
						</div>
						<label for="tgl_bayar" class="col-sm-2 control-label">Tgl Pembayaran</label>
						<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
								<input type="text" class="form-control tgl" id="tgl_bayar" name="tgl_bayar" value="" style="background:white" readonly>
							</div>
						</div>
					</div>
					<div class="form-group ">
					   <label for="dpp" class="col-sm-2 control-label">DPP</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="dpp" name="dpp" value="<?=$data->request_payment?>" readonly onchange="cektotal()">
							</div>
						</div>
					    <label for="sisa_hutang" class="col-sm-2 control-label">Sisa Hutang</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="sisa_hutang" name="sisa_hutang" value="<?=(isset($data)?$data->terbayar:0)?>" readonly>
							<input type="hidden" id="terbayar" name="terbayar" value="<?=(isset($data)?$data->terbayar:0)?>">
							</div>
						</div>
					</div>
					<div class="form-group ">
					    <label for="ppn" class="col-sm-2 control-label">PPN</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<?php
							echo form_dropdown('ppn',$datppn, (isset($data->ppn) ? $data->ppn: 0), array('id'=>'ppn','class'=>'form-control','readonly'=>'readonly','onchange'=>'cektotal()'));
							?>
							</div>
						</div>
					    <label for="nilai_ppn" class="col-sm-2 control-label">Nilai PPN</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="nilai_ppn" name="nilai_ppn" value="0" readonly tabindex="-1">
							</div>
						</div>
					</div>
					<div class="form-group ">
						<label for="t_biaya_lain" class="col-sm-2 control-label">Biaya Lain</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="t_biaya_lain" name="t_biaya_lain" value="0" readonly onchange="cektotal()">
							</div>
							<div class="input-group">
								<?php
								$datcoa[0]	= 'Select An Option';
								echo form_dropdown('coa_lain',$datcoa, 0, array('id'=>'coa_lain','class'=>'form-control readonly','readonly'=>'readonly'));
								?>
							</div>
						</div>
					   <label for="biaya_lain_note" class="col-sm-2 control-label">Keterangan Biaya Lain</label>
						<div class="col-sm-4">
							<textarea class="form-control" id="biaya_lain_note" name="biaya_lain_note" readonly></textarea>
						</div>
					</div>
					<div class="form-group ">
						<label for="potongan" class="col-sm-2 control-label">Nilai Potongan</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="potongan" name="potongan" value="0" onchange="cektotal()" readonly>
							</div>
							<div class="input-group">
								<?php
								echo form_dropdown('coa_potongan',$datcoa, 0, array('id'=>'coa_potongan','class'=>'form-control readonly','readonly'=>'readonly'));
								?>
							</div>
						</div>
						<label for="potongan_note" class="col-sm-2 control-label">Note Potongan</label>
						<div class="col-sm-4">
							<textarea class="form-control" id="potongan_note" name="potongan_note" readonly></textarea>
						</div>
					</div>
					<div class="form-group ">
					   <label for="jenis_pph" class="col-sm-2 control-label">Jenis PPH</label>
						<div class="col-sm-3">
							<div class="input-group">
								<?php
								$pphpembelian[0]	= 'Select An Option';
								echo form_dropdown('jenis_pph',$pphpembelian, '0', array('id'=>'jenis_pph','class'=>'form-control'));
								?>
							</div>
						</div>
						<label for="pph" class="col-sm-2 control-label">PPH</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="pph" name="pph" value="0" onchange="cektotal()">
							</div>
						</div>
					</div>

					<div class="form-group ">
					    <label for="nilai_bayar" class="col-sm-2 control-label">Nilai Pembayaran</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="nilai_bayar" name="nilai_bayar" value="0" readonly>
							</div>
						</div>
					</div>
					<div class="form-group ">
						<label for="bank" class="col-sm-2 control-label">Bank</label>
						<div class="col-sm-3">
							<?php
							echo form_dropdown('bank',$datbank, $data->bank, array('id'=>'bank','class'=>'form-control readonly','readonly'=>'readonly'));
							?>
						</div>
					</div>

			    <div class="form-group ">
					<label for="jenis_pembayaran" class="col-sm-2 control-label">Jenis Pembayaran</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-user"></i></span>
                            <?php
							$datbayar[0]	= 'Select An Option';
							echo form_dropdown('jenis_pembayaran',$datbayar, '0', array('id'=>'jenis_pembayaran','class'=>'form-control','readonly'=>'readonly', 'onchange'=>'cekjenis()'));
							?>
                        </div>
                    </div>
				   <label for="notes" class="col-sm-2 control-label">Note</label>
                    <div class="col-sm-4">
                        <textarea class="form-control" id="notes" name="notes" readonly></textarea>
                    </div>
                </div>
<div id="transfers" class="hidden">
			    <div class="form-group ">
				   <label for="bank_id" class="col-sm-2 control-label">Bank</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-list"></i></span>
                        <input type="text" class="form-control" id="bank_id" name="bank_id" value="" readonly>
                        </div>
                    </div>
					<label for="nama_rekening" class="col-sm-2 control-label">Nama Pemilik Rekening</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-list"></i></span>
                        <input type="text" class="form-control" id="nama_rekening" name="nama_rekening" value="" readonly>
                        </div>
                    </div>
                </div>
			    <div class="form-group ">
				   <label for="nomor_rekening" class="col-sm-2 control-label">No Rekening</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-list"></i></span>
                        <input type="text" class="form-control" id="nomor_rekening" name="nomor_rekening" value="" readonly>
                        </div>
                    </div>
                </div>
</div>
			    <div class="form-group hidden" id="cashgiro">
					<label for="giro_atas_nama" class="col-sm-2 control-label">Atas Nama</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-list"></i></span>
                        <input type="text" class="form-control" id="giro_atas_nama" name="giro_atas_nama" value="" readonly>
                        </div>
                    </div>
                </div>
			    <div class="form-group ">
				   <label for="no_voucher" class="col-sm-2 control-label">No Voucher</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-list"></i></span>
                        <input type="text" class="form-control" id="no_voucher" name="no_voucher" value="Automatic" readonly>
                        </div>
                    </div>
					<label for="tgl_voucher" class="col-sm-2 control-label">Tgl Voucher</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-list"></i></span>
                        <input type="text" class="form-control" readonly id="tgl_voucher" name="tgl_voucher" value="" >
                        </div>
                    </div>
                </div>

<hr />
					<div class="row">
						<div class="col-sm-3">
							<label>Invoice/Faktur/Nota
							<?php echo form_dropdown('c_inv',$datcombodata,  $data->c_inv, array('id'=>'c_inv','class'=>'form-control readonly','readonly'=>'readonly')); ?>
							</label>
							<br />
							No Invoice  <input type="text" name="t_inv" class="form-control" value="<?=$data->t_inv?>" readonly /><br />
							Tgl Invoice  <input type="text" name="t_inv_tgl" class="form-control" value="<?=$data->t_inv_tgl?>" readonly />
						</div>
						<div class="col-sm-3">
							<label>Faktur Pajak
							<?php echo form_dropdown('c_faktur',$datcombodata, $data->c_faktur, array('id'=>'c_faktur','class'=>'form-control readonly ','readonly'=>'readonly')); ?>
							</label><br />
							No Faktur<input type="text" id="t_faktur" name="t_faktur" class="form-control t_faktur" placeholder="xxx.xxx-xx-xxxxxxxx" value="<?=$data->t_faktur?>" readonly /><br />
							Tgl Faktur<input type="text" name="t_faktur_tgl" class="form-control" value="<?=$data->t_faktur_tgl?>" readonly />
						</div>
						<div class="col-sm-3">
							<label>Surat Jalan
							<?php echo form_dropdown('c_surat_jalan',$datcombodata, $data->c_surat_jalan, array('id'=>'c_surat_jalan','class'=>'form-control readonly ','readonly'=>'readonly')); ?>
							</label><br />
							No Surat Jalan  <input type="text" name="t_surat_jalan" class="form-control" value="<?=$data->t_surat_jalan?>" readonly />
						</div>
						<div class="col-sm-3">
							<label>Kontrak Kerjasama
							<?php echo form_dropdown('c_kontrak',$datcombodata, $data->c_kontrak, array('id'=>'c_kontrak','class'=>'form-control readonly','readonly'=>'readonly')); ?>
							</label><br />
							No Kontrak  <input type="text" name="t_kontrak" class="form-control" value="<?=$data->t_kontrak?>" readonly />
						</div>
					</div>
				</div>

				<hr />
					<div class="form-group ">
						<label for="no_pr" class="col-sm-2 control-label">No PR</label>
						<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-list"></i></span>
								<input type="text" class="form-control" id="no_pr" name="no_pr" value="<?php echo $data->no_pr; ?>" readonly tabindex="-1">
							</div>
						</div>
						<label for="vendor_id" class="col-sm-2 control-label">Supplier</label>
						<div class="col-sm-4">
							<?php
							echo form_dropdown('vendor_id',$datvendor, $data->vendor_id, array('id'=>'vendor_id','class'=>'form-control readonly', 'readonly'=>'readonly'));
							?>
							<div id="info_vendor"></div>
						</div>
					</div>

					<div class="form-group ">
					    <label for="nilai_request" class="col-sm-2 control-label">Nilai Pengajuan</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="nilai_request" name="nilai_request" value="<?=$data->request_payment?>" readonly>
							</div>
						</div>
					    <label for="notes" class="col-sm-2 control-label">Note</label>
						<div class="col-sm-4">
							<textarea class="form-control" id="notes" name="notes" readonly><?=$data->notes?></textarea>
						</div>
					</div>
					<div class="form-group ">
						<label for="quality_inspect" class="col-sm-2 control-label">Kualitas Produk</label>
						<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-list"></i></span>
								<input type="text" class="form-control" id="quality_inspect" name="quality_inspect" value="<?php echo set_value('quality_inspect', isset($data->quality_inspect) ? $data->quality_inspect: ''); ?>" readonly tabindex="-1">
							</div>
						</div>
						<label for="qty_inspect" class="col-sm-2 control-label">Kesesuaian Qty</label>
						<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-list"></i></span>
								<input type="text" class="form-control" id="qty_inspect" name="qty_inspect" value="<?php echo set_value('qty_inspect', isset($data->qty_inspect) ? $data->qty_inspect: ''); ?>" readonly tabindex="-1">
							</div>
						</div>
					</div>
					<div class="form-group ">
						<label for="note_release" class="col-sm-2 control-label">Note Release</label>
						<div class="col-sm-4">
								<textarea class="form-control" id="note_release" name="note_release" readonly tabindex="-1"><?php echo set_value('note_release', isset($data->note_release) ? $data->note_release: ''); ?></textarea>
						</div>
					</div>
					<hr>
					<h4>Info PR</h4>
					<div class="form-group ">
						<label for="tgl_pr" class="col-sm-2 control-label">Tgl PR</label>
						<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
								<input type="text" class="form-control tgl" id="tgl_pr" name="tgl_pr" value="<?php echo set_value('tgl_pr', isset($datapr->tgl_pr) ? $datapr->tgl_pr: date("Y-m-d")); ?>" placeholder="Automatic" readonly>
							</div>
						</div>
						<label  class="col-sm-2 control-label">Dibuat oleh</label>
						<div class="col-sm-3"><p class="form-control-static"><?php echo (isset($datapr->username) ? $datapr->username : ''); ?></p></div>
					</div>
					<div class="form-group ">
						<label for="id_aset" class="col-sm-2 control-label">Aset</label>
						<div class="col-sm-3">
								<?php
								$dataaset[0]	= 'Select An Option';
								echo form_dropdown('id_aset',$dataaset, set_value('id_aset', isset($datapr->id_aset) ? $datapr->id_aset: '0'), array('id'=>'id_aset','class'=>'form-control','readonly'=>'readonly'));
								?>
						</div>
						<label for="description" class="col-sm-2 control-label">Deskripsi</label>
						<div class="col-sm-3">
							<textarea class="form-control" id="description" name="description" readonly><?php echo isset($datapr->description) ? $datapr->description: ''; ?></textarea>
						</div>
					</div>
					<div class="form-group ">
						<label class="col-sm-2 control-label">Nilai Budget</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="budget" name="budget" value="<?php echo set_value('budget', isset($datapr->budget) ? $datapr->budget: 0); ?>" placeholder="0" readonly tabindex="-1">
							</div>
						</div>
						<label class="col-sm-2 control-label">Sisa Budget</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="budget_sisa" name="budget_sisa" value="<?php echo set_value('budget_sisa', isset($datapr->budget_sisa) ? $datapr->budget_sisa: 0); ?>" placeholder="0" readonly tabindex="-1">
							</div>
						</div>
					</div>

					<div class="box-footer">
						<div class="form-group">
							<div class="col-sm-offset-2 col-sm-10">
								<input type="hidden" id="type" name="type" value="edit">
								<button type="submit" name="save" class="btn btn-success" id="submit"><i class="fa fa-save">&nbsp;</i>Save</button>
							<a class="btn btn-danger" data-toggle="modal" onclick="cancel()"><i class="fa fa-minus-circle">&nbsp;</i>Cancel</a>
							</div>
						</div>
					</div>
				</div>
            <?= form_close() ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
	function cekjenis(){
		var jenis=$("#jenis_pembayaran").val();
		switch (jenis){
			case 'CASH':
			case 'GIRO':
				$("#transfers").addClass('hidden');
				$("#cashgiro").removeClass('hidden');
			break;
			case 'TRANSFER':
				$("#cashgiro").addClass('hidden');
				$("#transfers").removeClass('hidden');
			break;
			default:
				$("#cashgiro").addClass('hidden');
				$("#transfers").addClass('hidden');
		}
	}
	function cektotal(){
		var maxpay=<?php echo $data->request_payment; ?>;
		var dpp=$("#dpp").val();
		if(Number(dpp)>Number(maxpay)){
			dpp=maxpay;
			$("#dpp").val(maxpay);
			alert("DPP maksimal = "+maxpay);
		}
		var pph=$("#pph").val();
		var ppn=$("#ppn").val();
		var t_biaya_lain=$("#t_biaya_lain").val();
		nilai_ppn=Math.ceil(Number(dpp)*Number(ppn)/100);
		$("#nilai_ppn").val(nilai_ppn);
		var potongan = $("#potongan").val();
		nilai_bayar=(Number(dpp)-Number(pph)+Number(nilai_ppn)+Number(t_biaya_lain)-Number(potongan));
		$("#nilai_bayar").val(nilai_bayar);
	}


    $(document).ready(function() {
		$(".divide").divide();
		$('.readonly').css('pointer-events','none');
		var id_vendor=$("#vendor_id").val();
		if(id_vendor!=''){
			$.ajax({
				type	: "POST",
				url		: siteurl+"purchase_order/vendor_info/"+id_vendor,
				success	: function(ret){
					$("#info_vendor").html(ret);
				}
			});
		}
    });
	$(function () {
		// Daterange Picker
		$(".tgl").datepicker({
			todayHighlight: true,
			format : "yyyy-mm-dd",
			showInputs: true,
			autoclose:true
		});
	});
    $('#frm_data').on('submit', function(e){
        e.preventDefault();
        var formdata = $("#frm_data").serialize();
		var d_error='';
		if($("#nilai_bayar").val()<=0) d_error='Nilai pembayaran harus lebih besar dari 0';
		if($("#tgl_bayar").val()=='') d_error='Tanggal pembayaran harus diisi';
		if(d_error!=''){
			alert(d_error);
		}else{
			swal({
				  title: "Simpan data ini?", text: "Data tidak bisa di ubah kembali !", type: "warning", showCancelButton: true, confirmButtonClass: "btn-danger", confirmButtonText: "Ya", cancelButtonText: "Tidak", closeOnConfirm: true, closeOnCancel: true
				},
				function(isConfirm) {
				  if (isConfirm) {
					$.ajax({
						url: siteurl+"po_aset/save_payment_pp",
						dataType : "json",
						type: 'POST',
						data: formdata,
						success: function(msg){
							if(msg['save']=='1'){
								swal({
									title: "Sukses!",
									text: "Data Berhasil Di Simpan",
									type: "success",
									timer: 1500,
									showConfirmButton: false
								});
								cancel();
								window.location.reload();
							} else {
								swal({
									title: "Gagal!",
									text: "Data Gagal Di Simpan",
									type: "error",
									timer: 1500,
									showConfirmButton: false
								});
							};
							console.log(msg);
						},
						error: function(msg){
							swal({
								title: "Gagal!",
								text: "Ajax Data Gagal Di Proses",
								type: "error",
								timer: 1500,
								showConfirmButton: false
							});
							console.log(msg);
						}
					});
				  }
				}
				);
		}
    });

    function cancel(){
        $(".box").show();
        $("#form-data").hide();
    }

</script>