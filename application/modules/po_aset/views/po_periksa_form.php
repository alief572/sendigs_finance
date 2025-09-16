<?php
$readonly='';
if(isset($datarpo)){
	if($datarpo->status==1) $readonly=' readonly';
	if($datarpo->status==2) $readonly=' readonly';
}
?>
<script src="<?= base_url('assets/js/cleave.min.js')?>"></script>
<div class="nav-tabs-area">
    <div class="tab-content">
        <div class="tab-pane active" id="area">
            <div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
            <div class="box box-primary">
            <?= form_open(base_url().'po_aset/save_release_po',array('id'=>'frm_data','name'=>'frm_data','role'=>'form','class'=>'form-horizontal')) ?>
				<?php  if(isset($data->id)){$type='edit';}?>
				<input type="hidden" id="type" name="type" value="<?= isset($type) ? $type : 'add' ?>">
                <div class="box-body">
					<div class="form-group ">
						<label for="no_po" class="col-sm-2 control-label">No PO</label>
						<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-list"></i></span>
								<input type="text" class="form-control" id="no_po" name="no_po" value="<?php echo $data->no_po; ?>" readonly tabindex="-1">
							</div>
						</div>
						<label for="tgl_periksa" class="col-sm-2 control-label">Tgl Pemeriksaan</label>
						<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
								<input type="text" class="form-control tgl" id="tgl_periksa" name="tgl_periksa" value="<?php echo set_value('tgl_periksa', isset($datarpo->tgl_periksa) ? $datarpo->tgl_periksa: date("Y-m-d")); ?>" <?=$readonly;?>>
							</div>
						</div>
					</div>
					<div class="form-group ">
						<label for="request_payment" class="col-sm-2 control-label">Nilai Pembayaran</label>
						<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-list"></i></span>
								<input type="text" class="form-control divide" id="request_payment" name="request_payment" value="<?php echo set_value('request_payment', isset($datarpo->request_payment) ? $datarpo->request_payment: 0); ?>" <?=$readonly;?> onblur="cektotal()" >
							</div>
						</div>
						<label for="request_note" class="col-sm-2 control-label">Note Pembayaran</label>
						<div class="col-sm-3">
							<textarea class="form-control" id="request_note" name="request_note" <?=$readonly;?>><?php echo set_value('request_note', isset($datarpo->request_note) ? $datarpo->request_note: ''); ?></textarea>
						</div>
					</div>
					<div class="form-group ">
					    <label for="ppn" class="col-sm-2 control-label">PPN</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<?php
							echo form_dropdown('ppn',$datppn, (isset($datarpo) ? $datarpo->ppn: $data->ppn), array('id'=>'ppn','class'=>'form-control '.$readonly,'required'=>'required',$readonly=>$readonly,'onblur'=>'cektotal()'));
							?>
							</div>
						</div>
					    <label for="nilai_ppn" class="col-sm-2 control-label">Nilai PPN</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="nilai_ppn" name="nilai_ppn" value="<?=(isset($datarpo)?$datarpo->nilai_ppn:'0')?>" readonly tabindex="-1">
							</div>
						</div>
					</div>
					<div class="form-group ">
							<label for="divisi" class="col-sm-2 control-label">Tipe Pembayaran</label>
							<div class="col-sm-3">
								<div class="input-group">
									<?php
									echo form_dropdown('tipe_bayar',$tipe_bayar, (isset($datarpo->tipe_bayar) ? $datarpo->tipe_bayar: '0'), array('id'=>'tipe_bayar','class'=>'form-control '.$readonly,$readonly=>$readonly));
									?>
								</div>
							</div>
					</div>

					<?php if(isset($datarpo)){
						if($datarpo->status=='10') echo '<div class="form-group "><label class="col-sm-2 control-label">Alasan penolakan</label><div class="col-sm-3"><p class="form-control-static">'.$datarpo->reject_reason.'</p></div></div>';
					}?>
<hr>
<h4>Penerimaan Barang</h4>
<?php
$show_terima_barang='';
if(isset($data->terima_barang)){
	if($data->terima_barang=="1") $show_terima_barang = '<div class="form-group"><label class="col-sm-2 control-label">Barang sudah diterima.<input type="hidden" name="quality_inspect" /><input type="hidden" name="qty_inspect" /></label></div>';
	echo $show_terima_barang;
}
if($show_terima_barang==''){ ?>
					<div class="form-group ">
						<label for="quality_inspect" class="col-sm-2 control-label">Kualitas Produk</label>
						<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-list"></i></span>
								<input type="text" class="form-control" id="quality_inspect" name="quality_inspect" value="<?php echo set_value('quality_inspect', isset($datarpo->quality_inspect) ? $datarpo->quality_inspect: ''); ?>" <?=$readonly?>>
							</div>
						</div>
						<label for="qty_inspect" class="col-sm-2 control-label">Kesesuaian Qty</label>
						<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-list"></i></span>
								<input type="text" class="form-control" id="qty_inspect" name="qty_inspect" value="<?php echo set_value('qty_inspect', isset($datarpo->qty_inspect) ? $datarpo->qty_inspect: ''); ?>" <?=$readonly?>>
							</div>
						</div>
					</div>
<?php } ?>
					<div class="form-group ">
						<label for="note_release" class="col-sm-2 control-label">Note Release</label>
						<div class="col-sm-3">
								<textarea class="form-control" id="note_release" name="note_release" <?=$readonly;?>><?php echo set_value('note_release', isset($datarpo->note_release) ? $datarpo->note_release: ''); ?></textarea>
						</div>
						<label for="terbayar" class="col-sm-2 control-label">Terbayar</label>
						<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-list"></i></span>
								<?php
									$terbayar=0;
									if(isset($datarpo)){
										$terbayar=$datarpo->terbayar;
									}else{
										if (isset($data->terbayar)) $terbayar=$data->terbayar;
									}
								?>
								<input type="text" class="form-control divide" id="terbayar" name="terbayar" value="<?php echo $terbayar ?>" readonly tabindex="-1">
							</div>
						</div>
					</div>

					<?php if(isset($datarpo)){
						if($datarpo->status=='10') echo '<div class="form-group "><label class="col-sm-2 control-label">Alasan penolakan</label><div class="col-sm-3"><p class="form-control-static">'.$datarpo->reject_reason.'</p></div></div>';
					}?>
					<?php if(isset($datarpo)){
						if($datarpo->status=='2') { ?> 
						<input type="hidden" id="approve_ap" name="approve_ap" value="approve_ap">
					<div class="row">
						<div class="col-sm-3">
							<label>Invoice/Faktur/Nota
							<?php echo form_dropdown('c_inv',$datcombodata, 'No', array('id'=>'c_inv','class'=>'form-control select2')); ?>
							</label>
							<br />
							No Invoice  <input type="text" name="t_inv" class="form-control" /><br />
							Tgl Invoice  <input type="text" name="t_inv_tgl" class="form-control tgl" />
						</div>
						<div class="col-sm-3">
							<label>Faktur Pajak
							<?php echo form_dropdown('c_faktur',$datcombodata, 'No', array('id'=>'c_faktur','class'=>'form-control select2')); ?>
							</label><br />
							No Faktur<input type="text" id="t_faktur" name="t_faktur" class="form-control t_faktur" placeholder="xxx.xxx-xx-xxxxxxxx" /><br />
							Tgl Faktur<input type="text" name="t_faktur_tgl" class="form-control tgl" />
						</div>
						<div class="col-sm-3">
							<label>Surat Jalan
							<?php echo form_dropdown('c_surat_jalan',$datcombodata, 'No', array('id'=>'c_surat_jalan','class'=>'form-control select2')); ?>
							</label><br />
							No Surat Jalan  <input type="text" name="t_surat_jalan" class="form-control" />
						</div>
						<div class="col-sm-3">
							<label>Kontrak Kerjasama
							<?php echo form_dropdown('c_kontrak',$datcombodata, 'No', array('id'=>'c_kontrak','class'=>'form-control select2')); ?>
							</label><br />
							No Kontrak  <input type="text" name="t_kontrak" class="form-control" />
						</div>
					</div>

						<?php }
					} ?>


					<hr >
					<h4>Info PO</h4>
					<div class="form-group ">
						<label for="vendor_id" class="col-sm-2 control-label">Supplier</label>
						<div class="col-sm-3">
							<?php
							echo form_dropdown('vendor_id',$datvendor, ($data->vendor_id!=''?$data->vendor_id:0), array('id'=>'vendor_id','class'=>'form-control readonly','readonly'=>'readonly'));
							?>
							<div id="info_vendor"></div>
						</div>
						<label for="vendor_reason" class="col-sm-2 control-label">Alasan Pilih Supplier</label>
						<div class="col-sm-3">
							<textarea class="form-control" id="vendor_reason" name="vendor_reason" readonly><?php echo isset($data->vendor_reason) ? $data->vendor_reason: ''; ?></textarea>
						</div>
					</div>
					<div class="form-group ">
						<label for="info_desc" class="col-sm-2 control-label">Deskripsi</label>
						<div class="col-sm-3">
							<textarea class="form-control" id="info_desc" name="info_desc" readonly tabindex="-1"><?php echo isset($data->info_desc) ? $data->info_desc: ''; ?></textarea>
						</div>
					    <label for="notes" class="col-sm-2 control-label">Note</label>
						<div class="col-sm-3">
							<textarea class="form-control" id="notes" name="notes" readonly tabindex="-1"><?=$data->notes?></textarea>
						</div>
					</div>
					<div class="form-group ">
						<label for="qty" class="col-sm-2 control-label">Qty</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="qty" name="qty" value="<?php echo isset($data->qty) ? $data->qty: 0; ?>"  readonly tabindex="-1">
							</div>
						</div>
						<label for="harga_satuan" class="col-sm-2 control-label">Harga Satuan</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="harga_satuan" name="harga_satuan" value="<?php echo isset($data->harga_satuan) ? $data->harga_satuan: 0; ?>"  readonly tabindex="-1" >
							</div>
						</div>
					</div>
					<div class="form-group ">
					    <label class="col-sm-2 control-label">Nilai PPN</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="po_nilai_ppn" name="po_nilai_ppn" value="<?=$data->nilai_ppn?>" readonly tabindex="-1">
							</div>
						</div>
					</div>
					<div class="form-group ">
						<input type="hidden" id="harga_total" name="harga_total" value="<?= $data->harga_total; ?>">
					   <label for="total_nilai_po" class="col-sm-2 control-label">Total PO</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="total_nilai_po" name="total_nilai_po" value="<?=$data->total_nilai_po?>" readonly tabindex="-1">
							</div>
						</div>
						<label  class="col-sm-2 control-label">Dibuat oleh</label>
						<div class="col-sm-3"><p class="form-control-static"><?php echo (isset($data->username) ? $data->username : ''); ?></p></div>
					</div>
					<hr>
					<h4>Info PR</h4>
					<div class="form-group ">
						<label for="no_pr" class="col-sm-2 control-label">No PR</label>
						<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-list"></i></span>
								<input type="text" class="form-control" id="no_pr" name="no_pr" value="<?php echo set_value('no_pr', isset($datapr->no_pr) ? $datapr->no_pr: ""); ?>" placeholder="Automatic" readonly tabindex="-1">
							</div>
						</div>
						<label for="tgl_pr" class="col-sm-2 control-label">Tgl PR</label>
						<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
								<input type="text" class="form-control" id="tgl_pr" name="tgl_pr" value="<?php echo set_value('tgl_pr', isset($datapr->tgl_pr) ? $datapr->tgl_pr: date("Y-m-d")); ?>" readonly>
							</div>
						</div>
					</div>
					<div class="form-group ">
						<label for="id_aset" class="col-sm-2 control-label">Aset</label>
						<div class="col-sm-3">
							<?php
							echo form_dropdown('id_aset',$dataaset, $datapr->id_aset, array('id'=>'id_aset','class'=>'form-control readonly','readonly'=>'readonly'));
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
					<div class="form-group ">
						<label for="nilai_pr" class="col-sm-2 control-label">Nilai PR</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="nilai_pr" name="nilai_pr" value="<?php echo isset($datapr->nilai_pr) ? $datapr->nilai_pr: 0; ?>" readonly>
							</div>
						</div>
						<label for="no_coa" class="col-sm-2 control-label">Tipe PR</label>
						<div class="col-sm-3">
							<div class="input-group">
							<?php if(isset($datapr->nilai_pr)){?>
								<input type="text" class="form-control" id="tipe_pr" name="tipe_pr" value="<?php echo $datapr->tipe_pr ?>" readonly>
							<?php } ?>
							</div>
						</div>
					</div>
					<div class="form-group ">
						<label  class="col-sm-2 control-label">Dibuat oleh</label>
						<div class="col-sm-3"><p class="form-control-static"><?php echo (isset($datapr->username) ? $datapr->username : ''); ?></p></div>
					</div>

					<div class="box-footer">
						<div class="form-group">
							<div class="col-sm-offset-2 col-sm-10">
							<a class="btn btn-danger" data-toggle="modal" onclick="cancel()"><i class="fa fa-minus-circle">&nbsp;</i>Cancel</a>
							<?php if($data->status==2 && !isset($datarpo->id)){ ?>
								<input type="hidden" id="status" name="status" value="2">
								<input type="hidden" id="id" name="id" value="<?php echo set_value('id', isset($data->id) ? $data->id : ''); ?>">
								<button type="submit" name="save" class="btn btn-success" id="submit"><i class="fa fa-save">&nbsp;</i>Save</button>
							<?php }else{
								if($datarpo->status=='10'){ ?>
								<input type="hidden" id="status" name="status" value="10">
								<input type="hidden" id="id" name="id" value="<?php echo set_value('id', isset($datarpo->id) ? $datarpo->id : ''); ?>">
								<button type="submit" name="save" class="btn btn-success" id="submit"><i class="fa fa-save">&nbsp;</i>Save</button>
							<?php }else{?>
								<input type="hidden" id="status" name="status" value="1">
								<input type="hidden" id="id" name="id" value="<?php echo set_value('id', isset($datarpo->id) ? $datarpo->id : ''); ?>">
								<input type="hidden" id="no_request" name="no_request" value="<?php echo set_value('no_request', isset($datarpo->no_request) ? $datarpo->no_request : ''); ?>">
								<button type="submit" name="save" class="btn btn-success" id="submit"><i class="fa fa-save">&nbsp;</i>Approve</button>
								<button type="button" name="save" class="btn btn-warning" id="reject" value="reject" data-toggle="modal" data-target="#frmreject"><i class="fa fa-times">&nbsp;</i>Reject</button>
								<!-- <label><input type="checkbox" name="edit_t" id="edit_t" value="1" onclick="opencek()" /> Edit</label> -->
							<?php }
							} ?>
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
        <button type="button" class="btn btn-primary"onclick="savereject()">Save</button>
      </div>
	</form>
    </div>
  </div>
</div>

<script type="text/javascript">
	function savereject(){
		var nopr=$("#no_po").val();
		var nid=$("#id").val();
		var nreject_reason=$("#reject_reason").val();
			$.ajax({
				url: siteurl+"po_aset/reject_approval_po_payment",
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

	function opencek(){
            if($("#edit_t").prop("checked") == true){
				$("#request_note").attr('readonly', false);
				$("#request_payment").attr('readonly', false);
				$("#quality_inspect").attr('readonly', false);
				$("#qty_inspect").attr('readonly', false);
				$("#note_release").attr('readonly', false);
            }else{
				$("#request_note").attr('readonly', true);
				$("#request_payment").attr('readonly', true);
				$("#quality_inspect").attr('readonly', true);
				$("#qty_inspect").attr('readonly', true);
				$("#note_release").attr('readonly', true);
				$('#frm_data')[0].reset();
			}
	}
	function cektotal(){
		var request_payment=$("#request_payment").val();
		var ppn=$("#ppn").val();
		var nilai_ppn=Math.ceil(Number(request_payment)*Number(ppn)/100);
		var nilai_total=(Number(request_payment)+Number(nilai_ppn));
		$("#nilai_ppn").val(nilai_ppn);
	}

	function cektotalpo(){
		var qty=$("#qty").val();
		var harga_satuan=$("#harga_satuan").val();
		var total=(Number(qty)*Number(harga_satuan));
		var ppn=$("#ppn").val();
		var nilai_ppn=Math.ceil(Number(total)*Number(ppn)/100);
		var nilai_total=(Number(total)+Number(nilai_ppn));
		$("#nilai_ppn").val(nilai_ppn);
		$("#total_nilai_po").val(nilai_total);
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
		var request_payment=$("#request_payment").val();
		var terbayar=$("#terbayar").val();
		var total_nilai_po=$("#total_nilai_po").val();
		if((Number(request_payment)+Number(terbayar))>Number(total_nilai_po)){
			alert("Nilai pembayaran lebih besar dari nilai PO");
			return false
		}
        var formdata = $("#frm_data").serialize();
        $.ajax({
            url: siteurl+"po_aset/save_release_po",
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
    });

    function cancel(){
        $(".box").show();
        $("#form-data").hide();
    }
<?php if(isset($datarpo)){
	if($datarpo->status=='2') { ?>

 var cleave = new Cleave('.t_faktur', {
     delimiters: ['.', '-', '-','.'],
     blocks: [3, 3, 2, 8],
     numericOnly: true
 });

	<?php }
} ?>

</script>
