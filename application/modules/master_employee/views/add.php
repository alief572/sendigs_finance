
<?php
$id					= (!empty($header[0]->id))?$header[0]->id:'';
$nik				= (!empty($header[0]->id))?$header[0]->nik:'';
$nm_karyawan		= (!empty($header[0]->id))?$header[0]->nm_karyawan:'';
$no_ktp				= (!empty($header[0]->id))?$header[0]->no_ktp:'';
$tmp_lahir			= (!empty($header[0]->id))?$header[0]->tmp_lahir:'';
$tgl_lahir			= (!empty($header[0]->id))?$header[0]->tgl_lahir:'';
$gender				= (!empty($header[0]->id))?$header[0]->gender:'';
$agama				= (!empty($header[0]->id))?$header[0]->agama:'';
$department			= (!empty($header[0]->id))?$header[0]->department:'';
$cost_center		= (!empty($header[0]->id))?$header[0]->cost_center:'';
$no_ponsel			= (!empty($header[0]->id))?$header[0]->no_ponsel:'';
$email				= (!empty($header[0]->id))?$header[0]->email:'';
$pendidikan			= (!empty($header[0]->id))?$header[0]->pendidikan:'';
$position			= (!empty($header[0]->id))?$header[0]->position:'';
$ktp_provinsi		= (!empty($header[0]->id))?$header[0]->ktp_provinsi:'';
$domisili_provinsi	= (!empty($header[0]->id))?$header[0]->domisili_provinsi:'';
$ktp_kota			= (!empty($header[0]->id))?$header[0]->ktp_kota:'';
$domisili_kota		= (!empty($header[0]->id))?$header[0]->domisili_kota:'';
$ktp_kecamatan		= (!empty($header[0]->id))?$header[0]->ktp_kecamatan:'';
$domisili_kecamatan	= (!empty($header[0]->id))?$header[0]->domisili_kecamatan:'';
$ktp_kelurahan		= (!empty($header[0]->id))?$header[0]->ktp_kelurahan:'';
$domisili_kelurahan	= (!empty($header[0]->id))?$header[0]->domisili_kelurahan:'';
$ktp_kode_pos		= (!empty($header[0]->id))?$header[0]->ktp_kode_pos:'';
$domisili_kode_pos	= (!empty($header[0]->id))?$header[0]->domisili_kode_pos:'';
$ktp_alamat			= (!empty($header[0]->id))?$header[0]->ktp_alamat:'';
$domisili_alamat	= (!empty($header[0]->id))?$header[0]->domisili_alamat:'';
$npwp				= (!empty($header[0]->id))?$header[0]->npwp:'';
$bpjs				= (!empty($header[0]->id))?$header[0]->bpjs:'';
$tgl_join			= (!empty($header[0]->id))?$header[0]->tgl_join:'';
$tgl_end			= (!empty($header[0]->id))?$header[0]->tgl_end:'';
$rek_number			= (!empty($header[0]->id))?$header[0]->rek_number:'';
$bank_account		= (!empty($header[0]->id))?$header[0]->bank_account:'';
$sts_karyawan		= (!empty($header[0]->id))?$header[0]->sts_karyawan:'';
$status				= (!empty($header[0]->id))?$header[0]->status:'';
$tanda_tangan		= (!empty($header[0]->id))?$header[0]->tanda_tangan:'';

?>
<form action="#" method="POST" id="form_employee" autocomplete='off'>   
	<div class="box box-primary">
		<!-- /.box-header -->
		<div class="box-body">
            <div class="box box-warning">
                <div class="box-header">
                    <h3 class="box-title">Personal</h3>		
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                <div class='form-group row'>		 	 
                    <label class='label-control col-sm-2'><b>Employee Name  <span class='text-red'>*</span></b></label>
                    <div class='col-sm-4'>              
                        <?php
							echo form_input(array('type'=>'hidden','id'=>'id','name'=>'id','class'=>'form-control input-md'),$id);
							echo form_input(array('type'=>'hidden','id'=>'nik','name'=>'nik','class'=>'form-control input-md'),$nik);			
                            echo form_input(array('id'=>'nm_karyawan','name'=>'nm_karyawan','class'=>'form-control input-md'),$nm_karyawan);											
                        ?>	
                    </div>
                    <label class='label-control col-sm-2'><b>ID Number</b></label>
                    <div class='col-sm-4'>              
                        <?php
                            echo form_input(array('id'=>'no_ktp','name'=>'no_ktp','class'=>'form-control input-md','placeholder'=>'ID Number'),$no_ktp);											
                        ?>	
                    </div>
                </div>
                <div class='form-group row'>		 	 
                    <label class='label-control col-sm-2'><b>Place of birth</b></label>
                    <div class='col-sm-4'>              
                        <?php
                            echo form_input(array('id'=>'tmp_lahir','name'=>'tmp_lahir','class'=>'form-control input-md','placeholder'=>'Place of birth'),$tmp_lahir);											
                        ?>	
                    </div>
                    <label class='label-control col-sm-2'><b>Date of birth</b></label>
                    <div class='col-sm-4'>              
                        <?php
                            echo form_input(array('id'=>'tgl_lahir','name'=>'tgl_lahir','class'=>'form-control input-md datepicker','readonly'=>'readonly','placeholder'=>'Date of birth'),$tgl_lahir);											
                        ?>		
                    </div>
                </div>
                <div class='form-group row'>		 	 
                    <label class='label-control col-sm-2'><b>Religion</b></label>
                    <div class='col-sm-4'>
                        <select name='agama' id='agama' class='form-control input-md'>
                            <option value='0'>Select An Religion</option>
                            <?php
							foreach($agamax AS $val => $valx){
								$selected = ($valx['name'] == $agama)?'selected':'';
								echo "<option value='".$valx['name']."' ".$selected.">".strtoupper($valx['data1'])."</option>";
							}
							?>
                        </select>	
                    </div>
                    <label class='label-control col-sm-2'><b>Gender</b></label>
                    <div class='col-sm-4'>
                        <select name='gender' id='gender' class='form-control input-md'>
                            <option value='0'>Select An Gender</option>
                            <?php
							foreach($genderx AS $val => $valx){
								$selected = ($valx['name'] == $gender)?'selected':'';
								echo "<option value='".$valx['name']."' ".$selected.">".strtoupper($valx['data1'])."</option>";
							}
							?>
                        </select>	
                    </div>
                </div>	
                <div class='form-group row'>		 	 
                    <label class='label-control col-sm-2'><b>Department</b></label>
                    <div class='col-sm-4'>
                        <select name='department' id='department' class='form-control input-md'>
                            <option value='0'>Select An Department</option>
							<?php
							foreach($departmentx AS $val => $valx){
								$selected = ($valx['id'] == $department)?'selected':'';
								echo "<option value='".$valx['id']."' ".$selected.">".strtoupper($valx['nama'])."</option>";
							}
							?>
                        </select>
                    </div>
                    <label class='label-control col-sm-2'><b>Contact Number</b></label>
                    <div class='col-sm-4'>             
                        <?php
                            echo form_input(array('id'=>'no_ponsel','name'=>'no_ponsel','class'=>'form-control input-md numberOnly','placeholder'=>'Contact Number'),$no_ponsel);											
                        ?>
                    </div>
                </div>
                <div class='form-group row'>		 	 
                    <label class='label-control col-sm-2'><b>Last Education</b></label>
                    <div class='col-sm-4'>
                        <select name='pendidikan' id='pendidikan' class='form-control input-md'>
                            <option value='0'>Select An Last Education</option>
							<?php
							foreach($pendidikanx AS $val => $valx){
								$selected = ($valx['name'] == $pendidikan)?'selected':'';
								echo "<option value='".$valx['name']."' ".$selected.">".$valx['data1']."</option>";
							}
							?>
                        </select>
                    </div>
                    <label class='label-control col-sm-2'><b>Email</b></label>
                    <div class='col-sm-4'>          
                        <?php
                            echo form_input(array('id'=>'email','name'=>'email','class'=>'form-control input-md','placeholder'=>'Email'),$email);											
                        ?>	
                    </div>
                </div>
            </div>

            <div class="box box-info">
                <div class="box-header">
                    <h3 class="box-title">Address</h3>		
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <div class='form-group row'>		 	 
                        <label class='label-control col-sm-2'><b>ID Card Address</b></label>
                        <div class='col-sm-4'>            
                            <?php
                                echo form_textarea(array('id'=>'ktp_alamat','name'=>'ktp_alamat','class'=>'form-control input-md','rows'=>'2','cols'=>'75','placeholder'=>'ID Card Address'),$ktp_alamat);											
                            ?>
                        </div>
                        <label class='label-control col-sm-2'><b>Domicile Address</b></label>
                        <div class='col-sm-4'>             
                            <?php
                                echo form_textarea(array('id'=>'domisili_alamat','name'=>'domisili_alamat','class'=>'form-control input-md','rows'=>'2','cols'=>'75','placeholder'=>'Domicile Address'),$domisili_alamat);											
                            ?>
                        </div>
                    </div>
                    <div class='form-group row'>		 	 
                        <label class='label-control col-sm-2'><b>ID Card Postcode</b></label>
                        <div class='col-sm-4'>            
                            <?php
                                echo form_input(array('id'=>'ktp_kode_pos','name'=>'ktp_kode_pos','class'=>'form-control input-md numberOnly','maxlength'=>'5','placeholder'=>'ID Card Postcode'),$ktp_kode_pos);											
                            ?>
                        </div>
                        <label class='label-control col-sm-2'><b>Domicile Postcode</b></label>
                        <div class='col-sm-4'>             
                            <?php
                                echo form_input(array('id'=>'domisili_kode_pos','name'=>'domisili_kode_pos','class'=>'form-control input-md numberOnly','maxlength'=>'5','placeholder'=>'Domicile Postcode'),$domisili_kode_pos);											
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="box box-danger">
                <div class="box-header">
                    <h3 class="box-title">Etc</h3>		
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <div class='form-group row'>		 	 
                        <label class='label-control col-sm-2'><b>TAX / NPWP Number</b></label>
                        <div class='col-sm-4'>          
                            <?php
                                echo form_input(array('id'=>'npwp','name'=>'npwp','class'=>'form-control input-md numberOnly ','placeholder'=>'TAX / NPWP Number'),$npwp);											
                            ?>	
                        </div>
                        <label class='label-control col-sm-2'><b>BPJS Number</b></label>
                        <div class='col-sm-4'>             
                            <?php
                                echo form_input(array('id'=>'bpjs','name'=>'bpjs','class'=>'form-control input-md','placeholder'=>'BPJS Number'),$bpjs);											
                            ?>
                        </div>
                    </div>
                    <div class='form-group row'>		 	 
                        <label class='label-control col-sm-2'><b>Join Date</b></label>
                        <div class='col-sm-4'>          
                            <?php
                                echo form_input(array('id'=>'tgl_join','name'=>'tgl_join','class'=>'form-control input-md datepicker','readonly'=>'readonly','placeholder'=>'Join Date'),$tgl_join);											
                            ?>	
                        </div>
                        <label class='label-control col-sm-2'><b>End Date</b></label>
                        <div class='col-sm-4'>             
                            <?php
                                echo form_input(array('id'=>'tgl_end','name'=>'tgl_end','class'=>'form-control input-md datepicker','readonly'=>'readonly','placeholder'=>'End Date'),$tgl_end);											
                            ?>
                        </div>
                    </div>
                    <div class='form-group row'>		 	 
                        <label class='label-control col-sm-2'><b>Account Number</b></label>
                        <div class='col-sm-4'>          
                            <?php
                                echo form_input(array('id'=>'rek_number','name'=>'rek_number','class'=>'form-control input-md','placeholder'=>'Account Number'),$rek_number);											
                            ?>	
                        </div>
                        <label class='label-control col-sm-2'><b>Account Bank</b></label>
                        <div class='col-sm-4'>
                            <select name='bank_account' id='bank_account' class='form-control input-md'>
                                <option value='0'>Select An Account Bank</option>
								<?php
								foreach($bankx AS $val => $valx){
									$selected = ($valx['code'] == $bank_account)?'selected':'';
									echo "<option value='".$valx['code']."' ".$selected.">".strtoupper($valx['name'])."</option>";
								}
								?>
                            </select>
                        </div>
                    </div>
                    <div class='form-group row'>		 	 
                        <label class='label-control col-sm-2'><b>Employee Status</b></label>
                        <div class='col-sm-4'>
                            <select name='sts_karyawan' id='sts_karyawan' class='form-control input-md'>
                                <option value='0'>Select An Employee Status</option>
								<?php
								foreach($sts_karyawanx AS $val => $valx){
									$selected = ($valx['name'] == $sts_karyawan)?'selected':'';
									echo "<option value='".$valx['name']."' ".$selected.">".strtoupper($valx['data1'])."</option>";
								}
								?>
                            </select>
                        </div>
                        <label class='label-control col-sm-2'><b>Status</b></label>
                        <div class='col-sm-4'>
                            <select name='status' id='status' class='form-control input-md'>
                                <option value='0'>Select An Status</option>
								<?php
								foreach($statusx AS $val => $valx){
									$selected = ($valx['name'] == $status)?'selected':'';
									echo "<option value='".$valx['name']."' ".$selected.">".strtoupper($valx['data1'])."</option>";
								}
								?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="label-control col-sm-2">Tanda Tangan (.jpeg/.jpg/.png)</label>
                        <div class='col-sm-3'> 
                            <div class="custom-file">
                                <input type="file" name='tanda_tangan' id="tanda_tangan" class='custom-file-input' accept='image/jpeg,image/jpg' >	
                                <!-- <label class="custom-file-label" for="customFile">Choose file</label> -->
                            </div>
                            <?php if(empty($tanda_tangan)){ ?>
                            <small style='color:red;'>Belum diupload !!!</small>
                            <?php } ?>
                        </div>
                        <?php if(!empty($tanda_tangan)){ ?>
                            <div class='col-sm-1'>
                                <a href='<?=get_root3LinkLive().$tanda_tangan;?>' target='_blank' class="btn btn-success btn-sm mr-2" title='Download'>Download</a>
                            </div>
                            <div class='col-sm-3'>
                                <img src='<?=get_root3LinkLive().$tanda_tangan;?>' alt='Ttd' style='width:160px;height:100px;'>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
		</div>
		<div class='box-footer'>
			<?php
            if(empty($tanda)){
                echo form_button(array('type'=>'button','class'=>'btn btn-md btn-primary','value'=>'save','content'=>'Save','id'=>'saved'));
            }
			echo form_button(array('type'=>'button','class'=>'btn btn-md btn-danger','value'=>'back','content'=>'Back','id'=>'back'));
			?>
		</div>
		<!-- /.box-body -->
	 </div>
  <!-- /.box -->
</form>

<script>
    $(document).ready(function(){
        $('select').select2({width: '100%'});
        $('.datepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth:true,
            changeYear:true,
            maxDate:'+0d'
        })
    })

    $(document).on('click', '#back', function(){
        window.location.href = base_url + active_controller
    });

    $('#saved').click(function(e){
        e.preventDefault();
        //Customer
        var nm_karyawan = $("#nm_karyawan").val();
        var tmp_lahir = $("#tmp_lahir").val();
        var tgl_lahir = $("#tgl_lahir").val();
        var department = $("#department").val();
        var gender = $("#gender").val();
        var agama = $("#agama").val();
        var pendidikan = $("#pendidikan").val();
        var ktp_kode_pos = $("#ktp_kode_pos").val();
        var ktp_alamat = $("#ktp_alamat").val();
        var domisili_kode_pos = $("#domisili_kode_pos").val();
        var domisili_alamat = $("#domisili_alamat").val();
        var no_ponsel = $("#no_ponsel").val();
        var email = $("#email").val();
        var npwp = $("#npwp").val();
        var bpjs = $("#bpjs").val();
        var no_ktp = $("#no_ktp").val();
        var tgl_join = $("#tgl_join").val();
        var tgl_end = $("#tgl_end").val();
        var sts_karyawan = $("#sts_karyawan").val();
        var rek_number = $("#rek_number").val();
        var bank_account = $("#bank_account").val();
        var status = $("#status").val();
        
        if(nm_karyawan == ''){
            swal({
                title	: "Error Message!",text	: 'Employee name is empty, please input first ...',type	: "warning"
            });
            $('#simpan-bro').prop('disabled',false);
            return false;
        }
        
        // if(nm_customer=='' || nm_customer==null || nm_customer=='-' || nm_customer=='0'){
        //     swal({
        //         title	: "Error Message!",
        //         text	: 'Customer Name in master customer tab is empty, please input first ...',
        //         type	: "warning"
        //     });
        //     return false;
        // }
        // $('#saved').prop('disabled',false);
        
        swal({
                title: "Are you sure?",
                text: "You will not be able to process again this data!",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Yes, Process it!",
                cancelButtonText: "No, cancel process!",
                closeOnConfirm: false,
                closeOnCancel: false
            },
            function(isConfirm) {
                if (isConfirm) {
                    // loading_spinner();
                    var formData 	= new FormData($('#form_employee')[0]);
                    var baseurl		= base_url + active_controller +'/add';
                    $.ajax({
                        url			: baseurl,
                        type		: "POST",
                        data		: formData,
                        cache		: false,
                        dataType	: 'json',
                        processData	: false, 
                        contentType	: false,
                        beforeSend : function(){
                            $(this).prop('disabled',true);
                        },					
                        success		: function(data){								
                            if(data.status == 1){
                                swal({
                                        title	: "Save Success!",
                                        text	: data.pesan,
                                        type	: "success",
                                        timer	: 7000
                                    });
                                window.location.href = base_url + active_controller;
                            }
                           else{
                                swal({
                                    title	: "Save Failed!",
                                    text	: data.pesan,
                                    type	: "warning",
                                    timer	: 7000
                                });
                            }
                            $('#saved').prop('disabled',false);
                        },
                        error: function() {
                            
                            swal({
                                title   : "Error Message !",
                                text    : 'An Error Occured During Process. Please try again..',						
                                type    : "warning",								  
                                timer   : 7000
                            });
                            $('#saved').prop('disabled',false);
                        }
                    });
                } else {
                swal("Cancelled", "Data can be process again :)", "error");
                $('#saved').prop('disabled',false);
                return false;
                }
        });
    });
</script>