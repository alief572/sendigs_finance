<div class="nav-tabs-area">
    <!-- /.tab-content -->
    <div class="tab-content">
        <div class="tab-pane active" id="area">
        <!-- Biodata Mitra -->
            <div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;">
            </div>
            <!-- form start-->
            <div class="box box-primary">
            <?= form_open($this->uri->uri_string(),array('id'=>'frm_data','name'=>'frm_data','role'=>'form','class'=>'form-horizontal')) ?>
                <div class="box-body">
				<div class="row">
				<div class="col-md-4">
                    <?php  if(isset($data->id)){$type='edit';}?>
                    <input type="hidden" id="type" name="type" value="<?= isset($type) ? $type : 'add' ?>">
                    <input type="hidden" id="id" name="id" value="<?php echo set_value('id', isset($data->id) ? $data->id : ''); ?>">
					<div class="form-group ">
						<label for="coa" class="col-sm-2 control-label">Tipe<font size="4" color="red"><B>*</B></font></label>
						<div class="col-sm-10">
							<div class="input-group">
								<?php
								$datcoa[0]	= 'Select An Option';
								echo form_dropdown('coa',$datcoa, set_value('coa', isset($data->coa) ? $data->coa: '0'), array('id'=>'coa','class'=>'form-control','style'=>'width:400px;','required'=>'required'));
								?>
							</div>
						</div>
					</div>
					<div class="form-group ">
						<label for="tahun" class="col-sm-2 control-label">Tahun<font size="4" color="red"><B>*</B></font></label>
						<div class="col-sm-10">
							<div class="input-group">
							<input type="text" class="form-control" id="tahun" name="tahun" value="<?php echo set_value('tahun', isset($data->tahun) ? $data->tahun: date("Y")); ?>" placeholder="tahun" required>
							</div>
						</div>
					</div>
					<div class="form-group ">
						<label for="divisi" class="col-sm-2 control-label">Departemen<font size="4" color="red"><B>*</B></font></label>
						<div class="col-sm-10">
							<div class="input-group">
								<?php
								$datdivisi[0]	= 'Select An Option';
								echo form_dropdown('divisi',$datdivisi, set_value('divisi', isset($data->divisi) ? $data->divisi: '0'), array('id'=>'divisi','class'=>'form-control'));
								?>
							</div>
						</div>
					</div>
					<div class="form-group ">
						<label for="total" class="col-sm-2 control-label">Total</label>
						<div class="col-sm-10">
							<div class="input-group">
							<input type="text" class="form-control divide" id="total" name="total" value="<?php echo isset($data->total) ? $data->total: 0; ?>" readonly tabindex="-1">
							</div>
						</div>
					</div>
					<div class="form-group ">
						<label for="terpakai" class="col-sm-2 control-label">Terpakai</label>
						<div class="col-sm-10">
							<div class="input-group">
							<input type="text" class="form-control divide" id="terpakai" name="terpakai" value="<?php echo isset($data->terpakai) ? $data->terpakai: 0; ?>" readonly tabindex="-1">
							</div>
						</div>
					</div>
					<div class="form-group ">
						<label for="sisa" class="col-sm-2 control-label">Sisa</label>
						<div class="col-sm-10">
							<div class="input-group">
							<input type="text" class="form-control divide" id="sisa" name="sisa" value="<?php echo isset($data->sisa) ? $data->sisa: 0; ?>" readonly tabindex="-1">
							</div>
						</div>
					</div>
					<div class="form-group ">
						<label for="" class="col-sm-2 control-label">Tgl Buat</label>
						<div class="col-sm-10">
							<div class="input-group">
							<input type="text" class="form-control" id="tgl_create" name="tgl_create" value="<?php echo isset($data->created_on) ? $data->created_on: date("Y-m-d"); ?>" readonly tabindex="-1">
							</div>
						</div>
					</div>
					<div class="form-group ">
						<label for="" class="col-sm-2 control-label">Tgl Ubah</label>
						<div class="col-sm-10">
							<div class="input-group">
							<input type="text" class="form-control" id="tgl_modifed" name="tgl_modifed" value="<?php echo isset($data->modified_on) ? $data->modified_on:""; ?>" readonly tabindex="-1">
							</div>
						</div>
					</div>
					<div class="form-group ">
						<label for="info" class="col-sm-2 control-label"></label>
						<div class="col-sm-10">
							<div class="input-group">
							<?php								
								echo '<label><input type="checkbox" name="info" value="Non Budget"'.(isset($data->info) ? ($data->info!=''?' checked':''):'').' /> Non Budget</label><br />';
							?>
							</div>
						</div>
					</div>

				</div>
				<div class="col-md-4"><h3 style="text-align:center">Budget</h3>
					<?php
					for($i=1;$i<=12;$i++){?>
						<div class="form-group ">
							<label for="bulan_<?=$i?>" class="col-sm-4 control-label">Bulan <?=$i?><font size="4" color="red"><B>*</B></font></label>
							<div class="col-sm-8">
								<div class="input-group">
								<input type="text" class="form-control divide bulan" id="bulan_<?=$i?>" name="bulan_<?=$i?>" value="<?php echo isset($data->tahun) ? $data->{"bulan_".$i}: '0'; ?>" required onchange="cektotal()">
								</div>
							</div>
						</div>
					<?php } ?>
				</div>
				<div class="col-md-4"><h3 style="text-align:center">Terpakai</h3>
					<?php
					for($i=1;$i<=12;$i++){?>
						<div class="form-group ">
							<label for="bulan_<?=$i?>" class="col-sm-4 control-label">Bulan <?=$i?></label>
							<div class="col-sm-8">
								<p class="form-control-static divide"><?php echo isset($data->tahun) ? $data->{"terpakai_bulan_".$i}: '0'; ?></p>
							</div>
						</div>
					<?php } ?>
				</div>
				</div>
					<div class="box-footer">
						<div class="form-group">
							<div class="col-sm-offset-2 col-sm-10">

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
<script src="<?= base_url('/assets/js/number-divider.min.js')?>"></script>
<script type="text/javascript">

    $(document).ready(function() {
        $("#coa").select2({});
		$(".divide").divide();
		<?php if(isset($data->coa)) echo '$("#coa").prop("disabled", true);';?>
    });

	function cektotal(){
		var sum = 0;
		$('.bulan').each(function() {
			sum += Number($(this).val());
		});
		$("#total").val(sum);
	}

    $('#frm_data').on('submit', function(e){
        e.preventDefault();
        var formdata = $("#frm_data").serialize();
        $.ajax({
            url: siteurl+"budget/save_data",
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
