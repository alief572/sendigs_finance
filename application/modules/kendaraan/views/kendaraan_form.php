<div class="box box-primary">
	<div class="box-body">
		<form id="form-kendaraan" method="post" class="form-horizontal" role="form">
		<div class="form-group ">
			<label for="nm_customer" class="col-sm-2 control-label">Nomor Kendaraan <font size="4" color="red"><B>*</B></font></label>
            <div class="col-sm-3">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                     <input type="hidden" class="form-control" id="id_kendaraan" name="id_kendaraan" value="<?php echo @$detail->id_kendaraan?>">
                     <input type="text" class="form-control" id="no_kendaraan" name="no_kendaraan" maxlength="45" placeholder="Nomor Kendaraan" required="" value="<?php echo @$detail->nm_kendaraan?>">
                </div>
            </div>
            <label for="nm_customer" class="col-sm-2 control-label">STNK Expired <font size="4" color="red"><B>*</B></font></label>
            <div class="col-sm-3">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                     <input value="<?php echo @$detail->stnk_expired?>" type="text" class="form-control datepicker" id="stnk_expired" name="stnk_expired" maxlength="45" placeholder="STNK Expired" required="">
                </div>
            </div>
		</div>
		<div class="form-group ">
			<label for="nm_customer" class="col-sm-2 control-label">Nomor Rangka <font size="4" color="red"><B>*</B></font></label>
            <div class="col-sm-3">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                     <input  value="<?php echo @$detail->no_rangka?>" type="text" class="form-control" id="no_rangka" name="no_rangka" maxlength="45" placeholder="Nomor Rangka" required="">
                </div>
            </div>
            <label for="nm_customer" class="col-sm-2 control-label">KEUR Expired <font size="4" color="red"><B>*</B></font></label>
            <div class="col-sm-3">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                     <input  value="<?php echo @$detail->keur_expired?>" type="text" class="form-control datepicker" id="keur_expired" name="keur_expired" maxlength="45" placeholder="KEUR Expired" required="">
                </div>
            </div>
		</div>
        <div class="form-group ">
            <label for="nm_customer" class="col-sm-2 control-label">Model <font size="4" color="red"><B>*</B></font></label>
            <div class="col-sm-3">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                     <input  value="<?php echo @$detail->model?>" type="text" class="form-control" id="model" name="model" maxlength="45" placeholder="Model" required="">
                </div>
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
		</form>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function(){
		$(".datepicker").datepicker({
            format : "yyyy-mm-dd",
            showInputs: true,
            autoclose:true
        });
	});
	$('#form-kendaraan').on('submit', function(e){
        e.preventDefault();
        var formdata = $("#form-kendaraan").serialize();
        $.ajax({
            url: siteurl+"kendaraan/savekendaraan",
            dataType : "json",
            type: 'POST',
            data: formdata,
            success: function(msg){
                if(msg['save']=='1'){
                   swal({
                        title: "Sukses!",
                        text: "Data Sukses Di Simpan",
                        type: "success",
                        timer: 1500,
                        showConfirmButton: false
                    });
                    window.location.href = siteurl+"kendaraan";
                } else {
                    swal({
                        title: "Gagal!",
                        text: "Data Gagal Di Simpan",
                        type: "error",
                        timer: 1500,
                        showConfirmButton: false
                    });
                };
            },
            error: function(){
                swal({
                    title: "Gagal!",
                    text: "Ajax Data Gagal Di Proses",
                    type: "error",
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        });
    });
</script>