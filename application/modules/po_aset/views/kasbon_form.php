<?php
$readonly='';
if($data->status==1) $readonly=' readonly';
?>
<div class="nav-tabs-area">
    <div class="tab-content">
        <div class="tab-pane active" id="area">
            <div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
            <div class="box box-primary">
            <?= form_open(base_url().'po_aset/save_data_kasbon',array('id'=>'frm_data','name'=>'frm_data','role'=>'form','class'=>'form-horizontal')) ?>
				<input type="hidden" id="id" name="id" value="<?php echo set_value('id', isset($data->id) ? $data->id : ''); ?>">
                <div class="box-body">
					<div class="form-group ">
						<label for="no_kasbon" class="col-sm-2 control-label">No Cash Advance</label>
						<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-list"></i></span>
								<input type="text" class="form-control" id="no_kasbon" name="no_kasbon" value="<?php echo $data->no_kasbon; ?>" readonly tabindex="-1">
							</div>
						</div>
						<label for="tgl_kasbon" class="col-sm-2 control-label">Tgl Cash Advance</label>
						<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
								<input type="text" class="form-control tgl" id="tgl_kasbon" name="tgl_kasbon" value="<?php echo set_value('tgl_kasbon', isset($data->tgl_kasbon) ? $data->tgl_kasbon: date("Y-m-d")); ?>" style="background:white" readonly>
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
					    <label for="nilai_kasbon" class="col-sm-2 control-label">Total Cash Advance</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="nilai_kasbon" name="nilai_kasbon" value="<?=$data->nilai_kasbon?>" <?=$readonly;?>>
							</div>
						</div>
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
							<div class="input-group">
								<?php
								$dataaset[0]	= 'Select An Option';
								echo form_dropdown('id_aset',$dataaset, set_value('id_aset', isset($datapr->id_aset) ? $datapr->id_aset: '0'), array('id'=>'id_aset','class'=>'form-control','required'=>'required', 'readonly'=>'readonly'));
								?>
							</div>
						</div>
						<label for="description" class="col-sm-2 control-label">Deskripsi</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control" id="description" name="description" value="<?php echo isset($datapr->description) ? $datapr->description: ''; ?>" readonly>
							</div>
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
					
					<div class="box-footer">
						<div class="form-group">
							<div class="col-sm-offset-2 col-sm-10">
							<a class="btn btn-danger" data-toggle="modal" onclick="cancel()"><i class="fa fa-minus-circle">&nbsp;</i>Cancel</a>
							<?php if($data->status==1) { ?>
								<input type="hidden" id="type" name="type" value="approve">
								<button type="submit" name="save" class="btn btn-success" id="submit"><i class="fa fa-save">&nbsp;</i>Approve</button>
								<label><input type="checkbox" name="edit_t" id="edit_t" value="1" onclick="opencek()" /> Edit</label>
							<?php }else{ ?>
								<input type="hidden" id="type" name="type" value="edit">
								<button type="submit" name="save" class="btn btn-success" id="submit"><i class="fa fa-save">&nbsp;</i>Save</button>
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
				$("#nilai_kasbon").attr('readonly', false);
            }else{
				$("#nilai_kasbon").attr('readonly', true);
				$('#frm_data')[0].reset();
			}
	}
	function cektotalpo(){
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
            url: siteurl+"po_aset/save_data_kasbon",
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