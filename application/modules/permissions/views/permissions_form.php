<div class="nav-tabs-permissions">
    <!-- /.tab-content -->
    <div class="tab-content">
        <div class="tab-pane active" id="permissions">
        <!-- Biodata Mitra -->
            <div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;">
            </div>
            <!-- form start-->
            <div class="box box-primary">
            <?= form_open($this->uri->uri_string(),array('id'=>'frm_permissions','name'=>'frm_permissions','role'=>'form','class'=>'form-horizontal')) ?>
                <div class="box-body">

                <div class="form-group ">                    
                    <?php  if(isset($data->id_permission)){$type='edit';}?>
                    <input type="hidden" id="type" name="type" value="<?= isset($type) ? $type : 'add' ?>">
                    <input type="hidden" id="id" name="id" value="<?php echo set_value('id', isset($data->id) ? $data->id : ''); ?>">

                    <label for="id_permission" class="col-sm-2 control-label">ID Menu<font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-user"></i></span>
                        <input type="text" class="form-control" id="id_permission" name="id_permission" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" maxlength="4" value="<?php echo set_value('id_permission', isset($data->id_permission) ? $data->id_permission : ''); ?>" placeholder="ID Permission" readonly>
                        </div>
                    </div>               
                </div>
				<div class="form-group ">
                    <label for="link" class="col-sm-2 control-label">Permission Menu<font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                            <div class="input-group-btn">
                            <a class="btn btn-info">
                            <i class="fa fa-street-view">&nbsp;</i>
                            </a>
                            </div>
                            <?php
							$datmenu[0]	= 'Select An Option';						
							echo form_dropdown('nm_permission',$datmenu, set_value('nm_permission', isset($data->nm_menu) ? $data->nm_menu : 'selected'), array('id'=>'nm_permission','class'=>'form-control nm_permission'));											
							?>                         
                        </div>
                    </div>  
				</div>
                
              	<div class="form-group ">
                    <label for="ket" class="col-sm-2 control-label">Jenis Permission<font size="4" color="red"><B>*</B></font></label>
                    <div class="col-sm-2">
					     <div class="input-group">
						    <div class="input-group-btn">
                            <a class="btn btn-info">
                            <i class="fa fa-user">&nbsp;</i>
                            </a>
                            </div>
                        <select id="ket" name="ket" class="form-control">
                            <option value="View" <?= set_select('ket', 'View', isset($data->ket) && $data->ket == 'View'); ?>>View
                            </option>
                            <option value="Add" <?= set_select('ket', 'Add', isset($data->ket) && $data->ket == 'Add'); ?>>Add
                            </option>
							<option value="Manage" <?= set_select('ket', 'Manage', isset($data->ket) && $data->ket == 'Manage'); ?>>Manage
                            </option>
                            <option value="Delete" <?= set_select('ket', 'Delete', isset($data->ket) && $data->ket == 'Delete'); ?>>Delete
                            </option>
							<option value="Marketing" <?= set_select('ket', 'Marketing', isset($data->ket) && $data->ket == 'Marketing'); ?>>Marketing
                            </option>
                        </select>
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

                </div>
            <?= form_close() ?>
            </div>
        <!-- Biodata Mitra -->
        </div>                                    

    </div>
    <!-- /.tab-content -->
</div>

<script type="text/javascript">

    $(document).ready(function() {               

        $(".nm_permission").select2({
           
        });
		
	 });

    //Biodata
    $('#frm_permissions').on('submit', function(e){
        e.preventDefault();
        var formdata = $("#frm_permissions").serialize();
        $.ajax({
            url: siteurl+"permissions/save_data_permissions",
            dataType : "json",
            type: 'POST',
            data: formdata,
            //alert(msg);
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
                };//alert(msg);
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

    function cancel(){
        $(".box").show();
        $("#form-area").hide();
        //window.location.reload();
        //reload_table();
    }

    //function reload_table(){
        //table.ajax.reload(null,false); //reload datatable ajax
     //   table.ajax.reload();
   // }

</script>
