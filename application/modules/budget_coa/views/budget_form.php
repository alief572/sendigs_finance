<?php
?>
<style>
.tableFixHead          { overflow: auto; height: 500px; }
.tableFixHead thead th { position: sticky; top: 0; z-index: 1; background-color:#dadada; }
</style>
<div class="nav-tabs-area">
    <!-- /.tab-content -->
    <div class="tab-content">
        <div class="tab-pane active" id="area">
            <div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
            <!-- form start-->
            <div class="box box-primary">
            <?= form_open($this->uri->uri_string(),array('id'=>'frm_data','name'=>'frm_data','role'=>'form','class'=>'form-horizontal')) ?>
                <div class="box-body">
				<div class="row">
					<div class="col-md-4">
						<input type="hidden" id="type" name="type" value="<?=$type?>">
						<div class="form-group ">
							<label class="col-sm-2 control-label">Tahun<font size="4" color="red"><B>*</B></font></label>
							<div class="col-sm-10">
								<div class="input-group">
									<input type="text" class="form-control" id="tahunform" name="tahun" value="" placeholder="tahun" required maxlength=4>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row table-responsive">
					<div class="col-md-12 tableFixHead">
					<table class="table table-bordered table-condensed">
					<thead>
					<tr>
						<th>COA</th>
						<th>Penanggung Jawab</th>
						<th>Kategori</th>
						<th>Definisi</th>
						<th>Formulasi Budget</th>
						<th>Referensi Budget/Tahun</th>
					</tr>
					</thead>
					<tbody>
					<?php $i=0; $tahun=date("Y");
					foreach($data as $record) {
						$i++;
						if (isset($record->tahun)) $tahun=$record->tahun;?>
						<tr>
							<td>
								<input type="hidden" name="id[]" value="<?=(isset($record->id)?$record->id:''); ?>">
								<input type="hidden" name="coa[]" value="<?=$record->no_perkiraan; ?>">
								<?=$record->no_perkiraan.'<br />'.$record->nama_perkiraan; ?>
							</td>
							<td>
								<?php
								$datadept[0]='';
								echo form_dropdown('divisi[]',$datadept, set_value('divisi', isset($record->divisi) ? $record->divisi: '0'), array('id'=>'divisi'.$i,'class'=>'form-control'));
								?>
							</td>
							<td>
								<?php
								echo form_dropdown('kategori[]',$datakategori, set_value('kategori', isset($record->kategori) ? $record->kategori: '0'), array('id'=>'kategori'.$i,'class'=>'form-control'));
								?>
							</td>
							<td>
								<input type="text" class="form-control" id="definisi_<?=$i?>" name="definisi[]" value="<?=(isset($record->definisi)?$record->definisi:''); ?>">
							</td>
							<td>
								<input type="text" class="form-control" id="info_<?=$i?>" name="info[]" value="<?=(isset($record->info)?$record->info:''); ?>">
							</td>
							<td>
								<input type="hidden" id="finance_bulan<?=$i?>" name="finance_bulan[]" value="<?=(isset($record->finance_bulan)?$record->finance_bulan:0); ?>">
								<input type="text" class="form-control divide" id="finance_tahun<?=$i?>" name="finance_tahun[]" value="<?=(isset($record->finance_tahun)?$record->finance_tahun:0); ?>" size="5">
							</td>
					<?php } ?>
					</tbody>
					</table>
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
            <?= form_close() ?>
            </div>
        </div>
    </div>
</div>
<script src="<?= base_url('assets/js/number-divider.min.js')?>"></script>
<script type="text/javascript">
    $(document).ready(function() {
		$(".divide").divide();
		$("#tahunform").val('<?=$tahun?>');
    });
	function settahun(id){
		var perbulan=$("#finance_bulan"+id).val();
		$("#finance_tahun"+id).val(parseFloat(perbulan)*12);
	}
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
            url: siteurl+"budget_coa/save_data",
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
//                    cancel();
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
