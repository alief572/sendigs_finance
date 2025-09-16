<?php
$readonly='';
$readonlycombo='';
if(isset($data->id)){
	if($data->status==1) {
		$readonly=' readonly';
		$readonlycombo='disabled';
	}
}
	?>
<div class="nav-tabs-area">
    <div class="tab-content">
        <div class="tab-pane active" id="area">
            <div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
            <div class="box box-primary">
            <?= form_open(base_url().'po_nonstock/save_data_po',array('id'=>'frm_data','name'=>'frm_data','role'=>'form','class'=>'form-horizontal')) ?>
				<?php  if(isset($data->id)){$type='edit';}?>
				<input type="hidden" id="type" name="type" value="<?= isset($type) ? $type : 'add' ?>">
				<input type="hidden" id="id" name="id" value="<?php echo set_value('id', isset($data->id) ? $data->id : ''); ?>">
                <div class="box-body">
					<div class="form-group ">
						<label for="no_po" class="col-sm-2 control-label">No PO</label>
						<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-list"></i></span>
								<input type="text" class="form-control" id="no_po" name="no_po" value="<?php echo $data->no_po; ?>" readonly tabindex="-1">
							</div>
						</div>
						<label for="tgl_po" class="col-sm-2 control-label">Tgl PO</label>
						<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
								<input type="text" class="form-control tgl" id="tgl_po" name="tgl_po" value="<?php echo set_value('tgl_po', isset($data->tgl_po) ? $data->tgl_po: date("Y-m-d")); ?>" <?=$readonly;?>>
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
						<label for="vendor1_id" class="col-sm-2 control-label">Alternatif Supplier I</label>
						<div class="col-sm-3">
								<?php
								$datvendor[0]	= 'Select An Option';
								echo form_dropdown('vendor1_id',$datvendor, $data->vendor1_id, array('id'=>'vendor1_id','class'=>'form-control select2',$readonlycombo=>$readonlycombo));
								?>
						</div>
					</div>
					<div class="form-group ">
						<label for="vendor2_id" class="col-sm-2 control-label">Alternatif Supplier II</label>
						<div class="col-sm-3">
								<?php
								echo form_dropdown('vendor2_id',$datvendor, $data->vendor2_id, array('id'=>'vendor2_id','class'=>'form-control select2',$readonlycombo=>$readonlycombo));
								?>
						</div>
						<label for="vendortext_id" class="col-sm-2 control-label">Alternatif Supplier III</label>
						<div class="col-sm-3">
							<input type="text" class="form-control" id="vendortext_id" name="vendortext_id" value="<?php echo isset($data->vendortext_id) ? $data->vendortext_id: ''; ?>"  <?=$readonly;?>>
						</div>
					</div>
					<hr>
					<div class="form-group ">
						<label for="vendor_id" class="col-sm-2 control-label">Supplier Terpilih</label>
						<div class="col-sm-3">
								<?php
								$datvendor[0]	= 'Select An Option';
								echo form_dropdown('vendor_id',$datvendor, ($data->vendor_id!=''?$data->vendor_id:0), array('id'=>'vendor_id','class'=>'form-control select2',$readonlycombo=>$readonlycombo));
								?>
						</div>
						<label for="vendor_reason" class="col-sm-2 control-label">Alasan Pilih Supplier</label>
						<div class="col-sm-3">
							<textarea class="form-control" id="vendor_reason" name="vendor_reason" <?=$readonly;?>><?php echo isset($data->vendor_reason) ? $data->vendor_reason: ''; ?></textarea>
						</div>
					</div>
					<div class="form-group ">
						<label for="info_desc" class="col-sm-2 control-label">Deskripsi</label>
						<div class="col-sm-3">
							<textarea class="form-control" id="info_desc" name="info_desc" <?=$readonly;?>><?php echo isset($data->info_desc) ? $data->info_desc: ''; ?></textarea>
						</div>
					    <label for="notes" class="col-sm-2 control-label">Note</label>
						<div class="col-sm-3">
							<textarea class="form-control" id="notes" name="notes" <?=$readonly;?>><?=$data->notes?></textarea>
						</div>
					</div>
					<div class="form-group ">
						<label for="qty" class="col-sm-2 control-label">Qty</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="qty" name="qty" value="<?php echo isset($data->qty) ? $data->qty: 0; ?>" onchange="cektotalpo()" <?=$readonly;?> >
							</div>
						</div>
						<label for="harga_satuan" class="col-sm-2 control-label">Harga Satuan</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="harga_satuan" name="harga_satuan" value="<?php echo isset($data->harga_satuan) ? $data->harga_satuan: 0; ?>" onchange="cektotalpo()" <?=$readonly;?> >
							</div>
						</div>
					</div>
					<div class="form-group ">
					    <label for="ppn" class="col-sm-2 control-label">PPN</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<?php
							$readonlystat='';
							if($readonly!=''){
								$readonlystat='readonly';
							}
							echo form_dropdown('ppn',$datppn, $data->ppn, array('id'=>'ppn','class'=>'form-control','required'=>'required','onchange'=>'cektotalpo()',$readonly=>$readonly));
							?>
							</div>
						</div>
					    <label for="nilai_ppn" class="col-sm-2 control-label">Nilai PPN</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="nilai_ppn" name="nilai_ppn" value="<?=$data->nilai_ppn?>" readonly tabindex="-1">
							</div>
						</div>
					</div>
					<div class="form-group ">
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
								$dataaset[0]	= 'Select An Option';
								echo form_dropdown('id_aset',$dataaset, set_value('id_aset', isset($datapr->id_aset) ? $datapr->id_aset: '0'), array('id'=>'id_aset','class'=>'form-control','required'=>'required', 'readonly'=>'readonly'));
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
							<input type="text" class="form-control divide" id="budget" name="budget" value="<?php echo set_value('budget', isset($datapr->budget) ? $datapr->budget: 0); ?>" placeholder="0" required readonly tabindex="-1">
							</div>
						</div>
						<label class="col-sm-2 control-label">Sisa Budget</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="budget_sisa" name="budget_sisa" value="<?php echo set_value('budget_sisa', isset($datapr->budget_sisa) ? $datapr->budget_sisa: 0); ?>" placeholder="0" required readonly tabindex="-1">
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
						<div class="col-sm-3"><p class="form-control-static"><?php echo set_value('id', isset($datapr->username) ? $datapr->username : ''); ?></p></div>
					</div>

					<div class="box-footer">
						<div class="form-group">
							<div class="col-sm-offset-2 col-sm-10">
							<input type="hidden" id="status" name="status" value="<?= $data->status; ?>">
							<input type="hidden" id="edit_status" name="edit_status" value="<?= $data->edit_status; ?>">
							<a class="btn btn-danger" data-toggle="modal" onclick="cancel()"><i class="fa fa-minus-circle">&nbsp;</i>Cancel</a>
							<?php if($data->status==0){ ?>
								<button type="submit" name="save" class="btn btn-success" id="submit"><i class="fa fa-save">&nbsp;</i>Save</button>
							<?php }else{ ?>
								<button type="submit" name="save" class="btn btn-success" id="submit"><i class="fa fa-save">&nbsp;</i>Approve</button>
								<label><input type="checkbox" name="edit_t" id="edit_t" value="1" onclick="opencek()" /> Edit</label>
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

<script type="text/javascript">
	function opencek(){
            if($("#edit_t").prop("checked") == true){
				$("#notes").attr('readonly', false);
				$("#qty").attr('readonly', false);
				$("#harga_satuan").attr('readonly', false);
				$("#ppn").attr('readonly', false);
				$("#vendor_reason").attr('readonly', false);
				$("#info_desc").attr('readonly', false);
				$("#vendor_id").attr('disabled', false);
            }else{
				$("#notes").attr('readonly', true);
				$("#qty").attr('readonly', true);
				$("#harga_satuan").attr('readonly', true);
				$("#ppn").attr('readonly', true);
				$("#vendor_reason").attr('readonly', true);
				$("#info_desc").attr('readonly', true);
				$("#vendor_id").attr('disabled', true);
				$('#frm_data')[0].reset();
			}
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
        $(".select2").select2();
//		$("#vendor_id").select2();
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
		$("#vendor_id").attr('disabled', false);
		$("#vendor1_id").attr('disabled', false);
		$("#vendor2_id").attr('disabled', false);
        var formdata = $("#frm_data").serialize();
        $.ajax({
            url: siteurl+"po_aset/save_data_po",
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

</script>