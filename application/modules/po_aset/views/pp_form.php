<?php
$readonly='';
if($data->status==1 || $data->status==2 || $data->status==5) $readonly=' readonly';
?>
<script src="<?= base_url('assets/js/cleave.min.js')?>"></script>
<div class="nav-tabs-area">
    <div class="tab-content">
        <div class="tab-pane active" id="area">
            <div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
            <div class="box box-primary">
            <?= form_open(base_url().'po_aset/save_data_pp',array('id'=>'frm_data','name'=>'frm_data','role'=>'form','class'=>'form-horizontal')) ?>
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
						<label for="tgl_pp" class="col-sm-2 control-label">Tgl Permintaan Pembayaran</label>
						<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
								<input type="text" class="form-control tgl" id="tgl_pp" name="tgl_pp" value="<?php echo set_value('tgl_pp', isset($data->tgl_pp) ? $data->tgl_pp: date("Y-m-d")); ?>" <?=$readonly;?>>
							</div>
						</div>
					</div>
					<div class="form-group ">
						<label for="no_pr" class="col-sm-2 control-label">No PR</label>
						<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-list"></i></span>
								<input type="text" class="form-control" id="no_pr" name="no_pr" value="<?php echo $data->no_pr; ?>" readonly tabindex="-1">
							</div>
						</div>
						<label for="vendor_id" class="col-sm-2 control-label">Supplier</label>
						<div class="col-sm-3">
							<?php
							$datvendor[0]	= 'Select An Option';
							echo form_dropdown('vendor_id',$datvendor, $data->vendor_id, array('id'=>'vendor_id','class'=>'form-control '.$readonly, $readonly=>$readonly));
							?>
							<div id="info_vendor"></div>
						</div>
					</div>

					<div class="form-group ">
					    <label for="request_payment" class="col-sm-2 control-label">Nilai Pengajuan</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="request_payment" name="request_payment" value="<?=$data->request_payment?>" <?=$readonly;?> onblur="cekppn()">
							</div>
						</div>
					    <label for="notes" class="col-sm-2 control-label">Note</label>
						<div class="col-sm-3">
							<textarea class="form-control" id="notes" name="notes" <?=$readonly;?>><?=$data->notes?></textarea>
						</div>
					</div>

					<div class="form-group ">
					    <label for="ppn" class="col-sm-2 control-label">PPN</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<?php
							echo form_dropdown('ppn',$datppn, (isset($data->ppn) ? $data->ppn: 0), array('id'=>'ppn','class'=>'form-control readonly','required'=>'required','readonly'=>'readonly','onchange'=>'cekppn()'));
							?>
							</div>
						</div>
					    <label for="nilai_ppn" class="col-sm-2 control-label">Nilai PPN</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="nilai_ppn" name="nilai_ppn" value="<?=(isset($data->nilai_ppn) ? $data->nilai_ppn: 0);?>" readonly tabindex="-1">
							</div>
						</div>
					</div>

					<div class="form-group ">
						<label for="divisi" class="col-sm-2 control-label">Tipe Pembayaran</label>
						<div class="col-sm-3">
							<div class="input-group">
								<?php
								echo form_dropdown('tipe_bayar',$tipe_bayar,(isset($data->tipe_bayar) ? $data->tipe_bayar: ''), array('id'=>'tipe_bayar','class'=>'form-control readonly','readonly'=>'readonly'));
								?>
							</div>
						</div>
					</div>
<?php if($data->quality_inspect!='' || $data->qty_inspect!='' || $data->note_release!='') { ?>
					<div class="form-group ">
						<label for="quality_inspect" class="col-sm-2 control-label">Kualitas Produk</label>
						<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-list"></i></span>
								<input type="text" class="form-control" id="quality_inspect" name="quality_inspect" value="<?php echo set_value('quality_inspect', isset($data->quality_inspect) ? $data->quality_inspect: ''); ?>" <?=$readonly;?> >
							</div>
						</div>
						<label for="qty_inspect" class="col-sm-2 control-label">Kesesuaian Qty</label>
						<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-list"></i></span>
								<input type="text" class="form-control" id="qty_inspect" name="qty_inspect" value="<?php echo set_value('qty_inspect', isset($data->qty_inspect) ? $data->qty_inspect: ''); ?>" <?=$readonly;?> >
							</div>
						</div>
					</div>
					<div class="form-group ">
						<label for="note_release" class="col-sm-2 control-label">Note Release</label>
						<div class="col-sm-4">
								<textarea class="form-control" id="note_release" name="note_release" <?=$readonly;?>><?php echo set_value('note_release', isset($data->note_release) ? $data->note_release: ''); ?></textarea>
						</div>
					</div>
<?php } ?>
					<?php if($data->status==10) { ?>
						<div class="form-group ">
							<label class="control-label col-sm-2">Alasan Penolakan</label>
							<div class="col-sm-4">
								<textarea class="form-control" readonly id="rejectreason" name="rejectreason"><?php echo isset($data->reject_reason) ? $data->reject_reason: ''; ?></textarea>
							</div>
						</div>
					<?php } ?>


					<?php if(isset($data)){
						if($data->status=='2') { ?>
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
							<a class="btn btn-danger" data-toggle="modal" onclick="cancel()"><i class="fa fa-minus-circle">&nbsp;</i>Cancel</a>
							<?php if($data->status==1) { ?>
								<input type="hidden" id="type" name="type" value="approve">
								<button type="submit" name="save" class="btn btn-success" id="approve" value="approve"><i class="fa fa-save">&nbsp;</i>Approve</button>
								<button type="button" name="save" class="btn btn-warning" id="reject" value="reject" data-toggle="modal" data-target="#frmreject"><i class="fa fa-times">&nbsp;</i>Reject</button>
								<!-- <label><input type="checkbox" name="edit_t" id="edit_t" value="1" onclick="opencek()" /> Edit</label> -->
							<?php }else{ ?>
								<?php if($data->status==0 || $data->status==10) { ?>
									<input type="hidden" id="type" name="type" value="edit">
									<button type="submit" name="save" class="btn btn-success" id="submit"><i class="fa fa-save">&nbsp;</i>Save</button>
								<?php } ?>
								<?php if($data->status=='2' && $data->ap_cek=='0') { ?>
									<input type="hidden" id="type" name="type" value="approve_ap">
									<button type="submit" name="save" class="btn btn-success" id="approve" value="approve"><i class="fa fa-save">&nbsp;</i>Approve</button>
									<button type="button" name="save" class="btn btn-warning" id="reject" value="reject" data-toggle="modal" data-target="#frmreject"><i class="fa fa-times">&nbsp;</i>Reject</button>
								<?php } ?>
							<?php } ?>
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
			<label>Alasan</label>
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
<?php if(isset($data)){
	if($data->status=='2') { ?>

 var cleave = new Cleave('.t_faktur', {
     delimiters: ['.', '-', '-','.'],
     blocks: [3, 3, 2, 8],
     numericOnly: true
 });

	<?php }
} ?>
	function savereject(){
		var nopr=$("#no_pp").val();
		var nid=$("#id").val();
		var nreject_reason=$("#reject_reason").val();
		$.ajax({
			url: siteurl+"po_aset/reject_approval_pp",
			dataType : "json",
			type: 'POST',
			data: {no_pp: nopr,reject_reason:nreject_reason,id:nid},
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

	function cekppn(){
		var ppn=$("#ppn").val();
		var request_payment=$("#request_payment").val();
		nilai_ppn=Math.ceil(Number(request_payment)*Number(ppn)/100);
		$("#nilai_ppn").val(nilai_ppn);
	}
	function cektotalpo(){
	}
	function opencek(){
            if($("#edit_t").prop("checked") == true){
				$("#notes").attr('readonly', false);
				$("#request_payment").attr('readonly', false);
            }else{
				$("#notes").attr('readonly', true);
				$("#request_payment").attr('readonly', true);
				$('#frm_data')[0].reset();
			}
	}

    $(document).ready(function() {
		$(".divide").divide();
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
        $.ajax({
            url: siteurl+"po_aset/save_data_pp",
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

    $(document).ready(function() {
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

    function cancel(){
        $(".box").show();
        $("#form-data").hide();
    }

</script>