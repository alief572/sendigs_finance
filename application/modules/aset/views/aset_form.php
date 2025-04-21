<?php
function namepr($id){
	if($id=='PP') return "Permintaan Pembayaran";
	if($id=='PO') return "Purchase Order";
	if($id=='KASBON') return "Cash Advance";
}
?>
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
				<div class="col-md-16">
                    <?php  if(isset($data->id)){$type='edit';}?>
                    <input type="hidden" id="type" name="type" value="<?= isset($type) ? $type : 'add' ?>">
                    <input type="hidden" id="id" name="id" value="<?php echo set_value('id', isset($data->id) ? $data->id : ''); ?>">
					
					<div class="form-group ">
						<label for="divisi" class="col-sm-2 control-label">Departemen<font size="4" color="red"><B>*</B></font></label>
						<div class="col-sm-6">
							<div class="input-group">
								<?php
								$datdivisi[0]	= 'Select An Option';
								echo form_dropdown('divisi',$datdivisi, set_value('divisi', isset($data->divisi) ? $data->divisi: '0'), array('id'=>'divisi','class'=>'form-control','required'=>'required'));
								?>
							</div>
						</div>
					</div>
					<div class="form-group ">
						<label for="costcenter" class="col-sm-2 control-label">Costcenter<font size="4" color="red"><B>*</B></font></label>
						<div class="col-sm-6">
							<div class="input-group">
								<?php
								$datcostcenter[0]	= 'Select An Option';
								echo form_dropdown('costcenter',$datcostcenter, set_value('costcenter', isset($data->costcenter) ? $data->costcenter: '0'), array('id'=>'costcenter','class'=>'form-control','required'=>'required'));
								?>
							</div>
						</div>
					</div>
					
					<div class="form-group ">
						<label for="coa" class="col-sm-2 control-label">Pos Anggaran<font size="4" color="red"><B>*</B></font></label>
						<div class="col-sm-6">
							<div class="input-group">
								<?php
								$datcoa[0]	= 'Select An Option';
								echo form_dropdown('coa',$datcoa, set_value('coa', isset($data->coa) ? $data->coa: '0'), array('id'=>'coa','class'=>'form-control'));
								?>
							</div>
						</div>
					</div>
					
					<div class="form-group ">
						<label for="penyusutan" class="col-sm-2 control-label">Pos Penyusutan<font size="4" color="red"><B>*</B></font></label>
						<div class="col-sm-6">
							<div class="input-group">
								<?php
								$penyusutan[0]	= 'Select An Option';
								echo form_dropdown('penyusutan',$penyusutan, set_value('penyusutan', isset($data->coa_akum) ? $data->coa_akum: '0'), array('id'=>'penyusutan','class'=>'form-control'));
								?>
							</div> 
						</div>
					</div>
					
					<div class="form-group ">
						<label for="nama_aset" class="col-sm-2 control-label">Nama Aset<font size="4" color="red"><B>*</B></font></label>
						<div class="col-sm-6">
							<div class="input-group">
							<input type="text" class="form-control" id="nama_aset" name="nama_aset" value="<?php echo set_value('nama_aset', isset($data->nama_aset) ? $data->nama_aset: ''); ?>" placeholder="Nama Aset" required>
							</div>
						</div>
					</div>
					
					<div class="form-group ">
						<label for="" class="col-sm-2 control-label">Qty<font size="4" color="red"><B>*</B></font></label>
						<div class="col-sm-6">
							<div class="input-group">
							<input type="text" class="form-control" id="qty" name="qty" value="<?php echo isset($data->qty) ? $data->qty: '0' ?>" placeholder="Qty" required>
							</div>
						</div>
					</div>
					<div class="form-group ">
						<label for="tahun" class="col-sm-2 control-label">Tahun<font size="4" color="red"><B>*</B></font></label>
						<div class="col-sm-6">
							<div class="input-group">
							<select id="tahun" name="tahun" required class="form-control">
							<?php
							$selected='';
							$tahunawal=(date("Y")+1);
							for($i=$tahunawal;$i>=2019;$i--){
								$selected='';
								if(isset($data->tahun)){
									if($data->tahun==$i) $selected=' selected';
								}else{
									if(date("Y")==$i) $selected=' selected';
								}
								echo "<option value='".$i."'".$selected.">".$i."</option>";
							}
							?>
							</select>
							</div>
						</div>
					</div>
					<div class="form-group ">
						<label for="bulan" class="col-sm-2 control-label">Bulan<font size="4" color="red"><B>*</B></font></label>
						<div class="col-sm-6">
							<div class="input-group">
							<select id="bulan" name="bulan" required class="form-control">
							<?php
							$selected='';
							for($i=1;$i<=12;$i++){
								$selected='';
								if(isset($data->bulan)){
									if($data->bulan==$i) $selected=' selected';
								}else{
									if(date("m")==$i) $selected=' selected';
								}
								echo "<option value='".$i."'".$selected.">".date("F", mktime(0, 0, 0, $i, 10))."</option>";
							}
							?>
							</select>
							</div>
						</div>
					</div>
					<!--
					<div class="form-group ">
						<label for="" class="col-sm-2 control-label">Tgl Buat</label>
						<div class="col-sm-6">
							<div class="input-group">
							<input type="text" class="form-control" id="tgl_create" name="tgl_create" value="<?php echo isset($data->created_on) ? $data->created_on: date("Y-m-d"); ?>" readonly tabindex="-1">
							</div>
						</div>
					</div>
					<div class="form-group ">
						<label for="" class="col-sm-2 control-label">Tgl Ubah</label>
						<div class="col-sm-6">
							<div class="input-group">
							<input type="text" class="form-control" id="tgl_modifed" name="tgl_modifed" value="<?php echo isset($data->modified_on) ? $data->modified_on:""; ?>" readonly tabindex="-1">
							</div>
						</div>
					</div>
					-->
					<div class="form-group ">
						<label for="budget" class="col-sm-2 control-label">Budget<font size="4" color="red"><B>*</B></font></label>
						<div class="col-sm-6">
							<div class="input-group">
							<input type="text" class="form-control divide" onkeyup="sisabudget()" id="budget" name="budget" value="<?php echo set_value('budget', isset($data->budget) ? $data->budget: '0'); ?>" placeholder="budget" required>
							</div>
						</div>
					</div>
					 <div class="form-group ">
						<label for="description" class="col-sm-2 control-label">Deskripsi</label>
						<div class="col-sm-6">
							<textarea class="form-control" id="description" name="description"><?php echo isset($data->deskripsi) ? $data->deskripsi: ''; ?></textarea>
						</div>
					</div>
                    <div class="form-group ">
						<div class="col-sm-6">
							<div class="input-group">
							<input type="hidden" class="form-control divide" id="budgetpr" name="budgetpr" value="<?php echo set_value('budget', isset($data->budget) ? $data->budget: '0'); ?>" placeholder="budget" required>
							</div>
						</div>
					</div>	
                    <div class="form-group ">
						<div class="col-sm-6">
							<div class="input-group">
							<input type="hidden" class="form-control divide" id="budgetpo" name="budgetpo" value="<?php echo set_value('budget', isset($data->budget) ? $data->budget: '0'); ?>" placeholder="budget" required>
							</div>
						</div>
					</div>
                   					
				</div>
				</div>
					<div class="box-footer">
						<div class="form-group">
							<div class="col-sm-offset-2 col-sm-6">

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

    $(document).ready(function() {
        $("#coa").select2({});
		$(".divide").divide();
    });

	function cektotal(){
		var sum = 0;
		$('.bulan').each(function() {
			sum += Number($(this).val());
		});
		$("#total").val(sum);
	}
	
	function sisabudget(){
		var budget = $('#budget').val();
		$("#budgetpr").val(budget);
		$("#budgetpo").val(budget);
	}

    $('#frm_data').on('submit', function(e){
        e.preventDefault();
        var formdata = $("#frm_data").serialize();
        $.ajax({
            url: siteurl+"aset/save_data",
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