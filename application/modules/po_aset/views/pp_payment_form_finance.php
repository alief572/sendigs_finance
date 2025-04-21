<?php
$readonly='readonly';
$combocoa='';
if($data->status==1) {
	$readonly=' ';
	$combocoa=' select2';
}
if($data->status=='10') {
	$readonlypph=' ';
}else{
	if($data->status=='1') {
		$readonlypph=' readonly ';
	}else{
		$readonlypph=' readonly ';
	}
}
?>
<div class="nav-tabs-area">
    <div class="tab-content">
        <div class="tab-pane active" id="area">
            <div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
            <div class="box box-primary">
            <?= form_open(base_url().'po_aset/save_payment_pp',array('id'=>'frm_data','name'=>'frm_data','role'=>'form','class'=>'form-horizontal')) ?>
				<?php  if(isset($data->id)){$type='edit';}?>
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
								<input type="text" class="form-control tgl" id="tgl_bayar" name="tgl_bayar" value="<?php echo $data->tgl_bayar; ?>" <?=$readonly?>>
							</div>
						</div>
					</div>
					<div class="form-group ">
					   <label for="dpp" class="col-sm-2 control-label">DPP</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="dpp" name="dpp" value="<?php echo $data->dpp; ?>" onchange="cektotal()" readonly>
							</div>
						</div>
					</div>
					<div class="form-group ">
					    <label for="ppn" class="col-sm-2 control-label">PPN</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<?php
							echo form_dropdown('ppn',$datppn, (isset($data->ppn) ? $data->ppn: 0), array('id'=>'ppn','class'=>'form-control '.$readonly,$readonly=>$readonly,'required'=>'required','onchange'=>'cektotal()'));
							?>
							</div>
						</div>
					    <label for="nilai_ppn" class="col-sm-2 control-label">Nilai PPN</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="nilai_ppn" name="nilai_ppn" value="<?php echo $data->nilai_ppn; ?>" readonly tabindex="-1">
							</div>
						</div>
					</div>
					<div class="form-group ">
						<label for="t_biaya_lain" class="col-sm-2 control-label">Biaya Lain</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="t_biaya_lain" name="t_biaya_lain" value="<?php echo $data->t_biaya_lain; ?>" onchange="cektotal()" <?=$readonly?>>
							</div>
							<div class="input-group">
								<?php
								$datcoa[0]	= 'Select An Option';
								echo form_dropdown('coa_lain',$datcoa, $data->coa_lain, array('id'=>'coa_lain','class'=>'form-control '.($readonly==' '?' select2':'').' '.$readonly,$readonly=>$readonly));
								?>
							</div>
						</div>
					   <label for="biaya_lain_note" class="col-sm-2 control-label">Keterangan Biaya Lain</label>
						<div class="col-sm-4">
							<textarea class="form-control" id="biaya_lain_note" name="biaya_lain_note" <?=$readonly?>><?=$data->biaya_lain_note?></textarea>
						</div>
					</div>
					<div class="form-group ">
						<label for="potongan" class="col-sm-2 control-label">Nilai Potongan</label>
						<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-list"></i></span>
								<input type="text" class="form-control divide" id="potongan" name="potongan" value="<?=$data->potongan?>" onchange="cektotal()"  <?=$readonly?>>
							</div>
							<div class="input-group">
								<?php
								echo form_dropdown('coa_potongan',$datcoa, $data->coa_potongan, array('id'=>'coa_potongan','class'=>'form-control '.($readonly==' '?' select2':'').' '.$readonly,$readonly=>$readonly));
								?>
							</div>
						</div>
						<label for="potongan_note" class="col-sm-2 control-label">Note Potongan</label>
						<div class="col-sm-4">
							<textarea class="form-control" id="potongan_note" name="potongan_note" <?=$readonly?> ><?=$data->potongan_note?></textarea>
						</div>
					</div>
					<div class="form-group ">
					   <label for="jenis_pph" class="col-sm-2 control-label">Jenis PPH</label>
						<div class="col-sm-3">
							<div class="input-group">
								<?php
								$pphpembelian[0]	= 'Select An Option';
								echo form_dropdown('jenis_pph',$pphpembelian, $data->jenis_pph, array('id'=>'jenis_pph','class'=>'form-control '.$readonlypph,$readonlypph=>$readonlypph));
								?>
							</div>
						</div>
						<label for="pph" class="col-sm-2 control-label">PPH</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="pph" name="pph" value="<?=$data->pph?>" onblur="cektotal()"  <?=$readonlypph?>>
							</div>
						</div>
					</div>

					<div class="form-group ">
					    <label for="nilai_bayar" class="col-sm-2 control-label">Nilai Pembayaran</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="nilai_bayar" name="nilai_bayar" value="<?=$data->nilai_bayar?>" readonly>
							</div>
						</div>
						<label for="bank" class="col-sm-2 control-label">Bank</label>
						<div class="col-sm-3">
							<?php
							echo form_dropdown('bank',$datbank, $data->bank, array('id'=>'bank','class'=>'form-control '.$readonly,$readonly=>$readonly));
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
							echo form_dropdown('jenis_pembayaran',$datbayar, $data->jenis_pembayaran, array('id'=>'jenis_pembayaran','class'=>'form-control '.$readonly,$readonly=>$readonly,'required'=>'required', 'onchange'=>'cekjenis()'));
							?>
                        </div>
                    </div>
				   <label for="notes" class="col-sm-2 control-label">Note</label>
                    <div class="col-sm-4">
                        <textarea class="form-control" id="notes" name="notes" <?=$readonly?>><?=$data->notes?></textarea>
                    </div>
                </div>
<div id="transfers" class="hidden">
			    <div class="form-group ">
				   <label for="bank_id" class="col-sm-2 control-label">Bank</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-list"></i></span>
                        <input type="text" class="form-control" id="bank_id" name="bank_id" value="<?=$data->bank_id?>" <?=$readonly?>>
                        </div>
                    </div>
					<label for="nama_rekening" class="col-sm-2 control-label">Nama Pemilik Rekening</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-list"></i></span>
                        <input type="text" class="form-control" id="nama_rekening" name="nama_rekening" value="<?=$data->nama_rekening?>"  <?=$readonly?>>
                        </div>
                    </div>
                </div>
			    <div class="form-group ">
				   <label for="nomor_rekening" class="col-sm-2 control-label">No Rekening</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-list"></i></span>
                        <input type="text" class="form-control" id="nomor_rekening" name="nomor_rekening" value="<?=$data->nomor_rekening?>" <?=$readonly?>>
                        </div>
                    </div>
                </div>
</div>
			    <div class="form-group hidden" id="cashgiro">
					<label for="giro_atas_nama" class="col-sm-2 control-label">Atas Nama</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-list"></i></span>
                        <input type="text" class="form-control" id="giro_atas_nama" name="giro_atas_nama" value="<?=$data->giro_atas_nama?>" <?=$readonly?> >
                        </div>
                    </div>
                </div>
			    <div class="form-group ">
				   <label for="no_voucher" class="col-sm-2 control-label">No Voucher</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-list"></i></span>
                        <input type="text" class="form-control" id="no_voucher" name="no_voucher" value="<?=$data->no_voucher?>" readonly>
                        </div>
                    </div>
					<label for="tgl_voucher" class="col-sm-2 control-label">Tgl Voucher</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-list"></i></span>
                        <input type="text" class="form-control <?php echo ($readonly==' '?'tanggal':'');?>" id="tgl_voucher" name="tgl_voucher" value="<?=$data->tgl_voucher?>" required <?=$readonly?>>
                        </div>
                    </div>
                </div>

<?php if($data->status=='10') { ?>

					<div class="row">
						<div class="form-group ">
							<label class="control-label col-sm-2">Alasan Penolakan</label>
							<div class="col-sm-3">
								<textarea class="form-control" readonly id="rejectreason" name="rejectreason"><?php echo isset($data->reject_reason) ? $data->reject_reason: ''; ?></textarea>
							</div>
						</div>
					</div>
<?php } ?>
<hr />
					<div class="form-group ">
						<label for="no_pr" class="col-sm-2 control-label">No PR</label>
						<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-list"></i></span>
								<input type="text" class="form-control" id="no_pr" name="no_pr" value="<?php echo $datarq->no_pr; ?>" readonly tabindex="-1">
							</div>
						</div>
						<label for="vendor_id" class="col-sm-2 control-label">Supplier</label>
						<div class="col-sm-4">
							<input type="text" class="form-control" id="vendor_id" name="vendor_id" value="<?php echo isset($datarq->vendor_id) ? $datarq->vendor_id: ''; ?>" readonly>
						</div>
					</div>

					<div class="form-group ">
					    <label for="nilai_request" class="col-sm-2 control-label">Nilai Pengajuan</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="nilai_request" name="nilai_request" value="<?=$datarq->request_payment?>" readonly>
							</div>
						</div>
					    <label for="notes" class="col-sm-2 control-label">Note</label>
						<div class="col-sm-4">
							<textarea class="form-control" id="notes" name="notes" readonly><?=$datarq->notes?></textarea>
						</div>
					</div>
					<div class="form-group ">
						<label for="quality_inspect" class="col-sm-2 control-label">Kualitas Produk</label>
						<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-list"></i></span>
								<input type="text" class="form-control" id="quality_inspect" name="quality_inspect" value="<?php echo set_value('quality_inspect', isset($datarq->quality_inspect) ? $datarq->quality_inspect: ''); ?>" readonly tabindex="-1">
							</div>
						</div>
						<label for="qty_inspect" class="col-sm-2 control-label">Kesesuaian Qty</label>
						<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-list"></i></span>
								<input type="text" class="form-control" id="qty_inspect" name="qty_inspect" value="<?php echo set_value('qty_inspect', isset($datarq->qty_inspect) ? $datarq->qty_inspect: ''); ?>" readonly tabindex="-1">
							</div>
						</div>
					</div>
					<div class="form-group ">
						<label for="note_release" class="col-sm-2 control-label">Note Release</label>
						<div class="col-sm-4">
								<textarea class="form-control" id="note_release" name="note_release" readonly tabindex="-1"><?php echo set_value('note_release', isset($datarq->note_release) ? $datarq->note_release: ''); ?></textarea>
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
								<input type="hidden" id="status" name="status" value="<?=$data->status?>">
								<input type="hidden" id="id" name="id" value="<?=$data->id; ?>">
<?php if($data->status>='2') { ?>
	<?php if($data->status=='10') { ?>
								<input type="hidden" id="type" name="type" value="edit">
								<button type="submit" name="save" class="btn btn-success" id="submit"><i class="fa fa-save">&nbsp;</i>Save</button>
	<?php }else{ ?>
								<input type="hidden" id="type" name="type" value="approve">
								<button type="submit" name="save" class="btn btn-success" id="submit"><i class="fa fa-save">&nbsp;</i>Approve</button>
								<button type="button" name="save" class="btn btn-warning" id="reject" value="reject" data-toggle="modal" data-target="#frmreject"><i class="fa fa-times">&nbsp;</i>Reject</button>
	<?php } ?>
<?php }else{ ?>
								<input type="hidden" id="type" name="type" value="approve">
								<button type="submit" name="save" class="btn btn-success" id="submit"><i class="fa fa-save">&nbsp;</i>Save</button>
<?php } ?>
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

<div class="modal fade" id="frmreject" tabindex="-1" role="dialog" aria-labelledby="frmreject">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
	<form id="newfrmpp" class="form-horizontal">
		<div class="modal-body">
			<label for="new_note_pp">Alasan</label>
			<textarea class="form-control" id="reject_reason" name="reject_reason"></textarea>
		</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="savereject()">Save</button>
      </div>
	</form>
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
	function savereject(){
		var nopr=$("#no_pp").val();
		var nid=$("#id").val();
		var nreject_reason=$("#reject_reason").val();
					$.ajax({
						url: siteurl+"po_aset/reject_approval_pp_payment_finance",
						dataType : "json",
						type: 'POST',
						data: {no_pr: nopr,reject_reason:nreject_reason,id:nid},
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

	function cektotal(){
		var maxpay=<?php echo $data->nilai_bayar; ?>;
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
		<?php if ($data->status>=2) { ?>
			$('.readonly').css('pointer-events','none');
		<?php } ?>
		$(".divide").divide();
		$(".select2").select2();
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
						url: siteurl+"po_aset/save_payment_pp_finance",
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