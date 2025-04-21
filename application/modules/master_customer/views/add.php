<?php
$id_customer = (!empty($restHeader[0]['id_customer'])) ? $restHeader[0]['id_customer'] : '';
$nm_customer = (!empty($restHeader[0]['nm_customer'])) ? $restHeader[0]['nm_customer'] : '';
$bidang_usaha = (!empty($restHeader[0]['bidang_usaha'])) ? $restHeader[0]['bidang_usaha'] : '';
$produk_jual = (!empty($restHeader[0]['produk_jual'])) ? $restHeader[0]['produk_jual'] : '';
$kredibilitas = (!empty($restHeader[0]['kredibilitas'])) ? $restHeader[0]['kredibilitas'] : '';
$country_code = (!empty($restHeader[0]['country_code'])) ? $restHeader[0]['country_code'] : 'IDN';
$provinsi = (!empty($restHeader[0]['provinsi'])) ? $restHeader[0]['provinsi'] : '';
$kota = (!empty($restHeader[0]['kota'])) ? $restHeader[0]['kota'] : '';
$kode_pos = (!empty($restHeader[0]['kode_pos'])) ? $restHeader[0]['kode_pos'] : '';
$alamat = (!empty($restHeader[0]['alamat'])) ? $restHeader[0]['alamat'] : '';
$telpon = (!empty($restHeader[0]['telpon'])) ? $restHeader[0]['telpon'] : '';
$fax = (!empty($restHeader[0]['fax'])) ? $restHeader[0]['fax'] : '';
$npwp = (!empty($restHeader[0]['npwp'])) ? $restHeader[0]['npwp'] : '';
$alamat_npwp = (!empty($restHeader[0]['alamat_npwp'])) ? $restHeader[0]['alamat_npwp'] : '';
$id_marketing = (!empty($restHeader[0]['id_marketing'])) ? $restHeader[0]['id_marketing'] : '';
$website = (!empty($restHeader[0]['website'])) ? $restHeader[0]['website'] : '';
$sts_aktif = (!empty($restHeader[0]['sts_aktif'])) ? $restHeader[0]['sts_aktif'] : '';
$diskon_toko = (!empty($restHeader[0]['diskon_toko'])) ? $restHeader[0]['diskon_toko'] : '';
$reference_by = (!empty($restReff[0]['reference_by'])) ? $restReff[0]['reference_by'] : '';
$reference_name = (!empty($restReff[0]['reference_name'])) ? $restReff[0]['reference_name'] : '';
$reference_phone = (!empty($restReff[0]['reference_phone'])) ? $restReff[0]['reference_phone'] : '';
$id_pic = (!empty($restHeader[0]['id_pic'])) ? $restHeader[0]['id_pic'] : '';
$nm_pic = (!empty($restPIC[0]['nm_pic'])) ? $restPIC[0]['nm_pic'] : '';
$divisi = (!empty($restPIC[0]['divisi'])) ? $restPIC[0]['divisi'] : '';
$hp = (!empty($restPIC[0]['hp'])) ? $restPIC[0]['hp'] : '';
$email_pic = (!empty($restPIC[0]['email_pic'])) ? $restPIC[0]['email_pic'] : '';

?>
<form action="#" method="POST" id="form_proses_bro">
    <input type="hidden" name='id_customer' value='<?= $id_customer; ?>'>
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#data">Customer</a></li>
            <li><a data-toggle="tab" href="#data_pic">PIC</a></li>
        </ul>
        <div class="tab-content">
            <div id="data" class="tab-pane fade in active">
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="form-group row">
                            <label class='label-control col-sm-2'><b>Customer Name <span class='text-red'>*</span></b></label>
                            <div class='col-sm-4'>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-check"></i></span>
                                    <?php
                                    echo form_input(array('id' => 'nm_customer', 'name' => 'nm_customer', 'class' => 'form-control input-md', 'autocomplete' => 'off', 'placeholder' => 'Customer Name'), $nm_customer);
                                    ?>
                                </div>
                            </div>
                            <label class='label-control col-sm-2'><b>Business Fields</b></label>
                            <div class='col-sm-4'>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-building"></i></span>
                                    <select name='bidang_usaha' id='bidang_usaha' class='form-control input-md select2'>
                                        <option value='0'>Select An Business Fields</option>
                                        <?php
                                        foreach ($rows_bidang as $val => $valx) {
                                            $selected = ($bidang_usaha == $valx['id_bidang_usaha']) ? 'selected' : '';
                                            echo "<option value='" . $valx['id_bidang_usaha'] . "' " . $selected . ">" . ucwords(strtolower($valx['bidang_usaha'])) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class='label-control col-sm-2'><b>Selling Product</b></label>
                            <div class='col-sm-4'>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-folder"></i></span>
                                    <?php
                                    echo form_input(array('id' => 'produk_jual', 'name' => 'produk_jual', 'class' => 'form-control input-md', 'autocomplete' => 'off', 'placeholder' => 'Selling Product'), $produk_jual);
                                    ?>
                                </div>
                            </div>

                            <label class='label-control col-sm-2'><b>Credibility</b></label>
                            <div class='col-sm-4'>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-check"></i></span>
                                    <?php
                                    $Arr_Credibility    = array(
                                        ''        => 'Select An Credibility',
                                        'A'        => 'A',
                                        'B'        => 'B',
                                        'C'        => 'C',
                                        'D'        => 'D'
                                    );
                                    echo form_dropdown('kredibilitas', $Arr_Credibility, $kredibilitas, array('id' => 'kredibilitas', 'class' => 'form-control input-sm select2'));
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class='label-control col-sm-2'><b>Country<span class='text-red'>*</span></b></label>
                            <div class='col-sm-4'>
                                <select name='country_code' id='country_code' class='form-control input-md select2'>
                                    <?php
                                    foreach ($CountryName as $val => $valx) {
                                        $selx = ($valx['country_code'] == $country_code) ? 'selected' : '';
                                        echo "<option value='" . $valx['country_code'] . "' " . $selx . ">" . $valx['country_name'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <label class='label-control col-sm-2'><b>Province</b></label>
                            <div class='col-sm-4'>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-home"></i></span>
                                    <select name='provinsi' id='provinsi' class='form-control input-md select2'>
                                        <option value='0'>Select An Province</option>
                                        <?php
                                        foreach ($rows_province as $val => $valx) {
                                            $selx = ($valx['id_prov'] == $provinsi) ? 'selected' : '';
                                            echo "<option value='" . $valx['id_prov'] . "' " . $selx . ">" . $valx['nama'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class='label-control col-sm-2'><b>District / City</b></label>
                            <div class='col-sm-4'>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-home"></i></span>
                                    <select name='kota' id='kota' class='form-control input-md select2'>
                                        <option value='0'>List Empty</option>
                                    </select>
                                </div>
                            </div>

                            <label class='label-control col-sm-2'><b>Post Code</b></label>
                            <div class='col-sm-4'>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                                    <?php
                                    echo form_input(array('id' => 'kode_pos', 'name' => 'kode_pos', 'class' => 'form-control input-md', 'placeholder' => 'Post Code'), $kode_pos);
                                    ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label class='label-control col-sm-2'><b>Address<span class='text-red'>*</span></b></label>
                            <div class='col-sm-10'>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
                                    <?php
                                    echo form_textarea(array('id' => 'alamat', 'name' => 'alamat', 'class' => 'form-control input-md', 'cols' => '75', 'rows' => '2', 'autocomplete' => 'off', 'placeholder' => 'Address'), $alamat);
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class='label-control col-sm-2'><b>Phone</b></label>
                            <div class='col-sm-4'>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                                    <?php
                                    echo form_input(array('id' => 'telpon', 'name' => 'telpon', 'class' => 'form-control input-md', 'placeholder' => 'Phone'), $telpon);
                                    ?>
                                </div>
                            </div>

                            <label class='label-control col-sm-2'><b>Fax</b></label>
                            <div class='col-sm-4'>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-fax"></i></span>
                                    <?php
                                    echo form_input(array('id' => 'fax', 'name' => 'fax', 'class' => 'form-control input-md', 'placeholder' => 'Fax'), $fax);
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row" >
                            <label class='label-control col-sm-2'><b>NPWP</b></label>
                            <div class='col-sm-4'>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                                    <?php
                                    echo form_input(array('id' => 'npwp', 'name' => 'npwp', 'class' => 'form-control input-md', 'placeholder' => 'NPWP'), $npwp);
                                    ?>
                                </div>
                            </div>

                            <label class='label-control col-sm-2'><b>NPWP Address</b></label>
                            <div class='col-sm-4'>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
                                    <?php
                                    echo form_textarea(array('id' => 'alamat_npwp', 'name' => 'alamat_npwp', 'class' => 'form-control input-md', 'cols' => '75', 'rows' => '2', 'autocomplete' => 'off', 'placeholder' => 'NPWP Address'), $alamat_npwp);
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">


                            <label class='label-control col-sm-2'><b>Marketing</b></label>
                            <div class='col-sm-4'>
                                <select name='id_marketing' id='id_marketing' class='form-control input-md select2'>
                                    <option>Select An Marketing</option>
                                    <?php
                                    foreach ($rows_marketing as $val => $valx) {
                                        $selx = ($valx['id'] == $id_marketing) ? 'selected' : '';
                                        echo "<option value='" . $valx['id'] . "' " . $selx . ">" . ucwords(strtolower($valx['nm_karyawan'])) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class='label-control col-sm-2'><b>Website</b></label>
                            <div class='col-sm-4'>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-globe"></i></span>
                                    <?php
                                    echo form_input(array('id' => 'website', 'name' => 'website', 'class' => 'form-control input-md', 'placeholder' => 'Website'), $website);
                                    ?>
                                </div>
                            </div>

                            <label class='label-control col-sm-2'><b>Status </b></label>
                            <div class='col-sm-4'>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                    <?php
                                    $rows_active    = array(
                                        'Y'        => 'Active',
                                        'N'    => 'Inactive'
                                    );
                                    echo form_dropdown('sts_aktif', $rows_active, $sts_aktif, array('id' => 'sts_aktif', 'class' => 'form-control input-md select2'));
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class='label-control col-sm-2'><b>Discount Customer (%)</b></label>
                            <div class='col-sm-4'>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-dollar"></i></span>
                                    <?php
                                    echo form_input(array('id' => 'diskon_toko', 'name' => 'diskon_toko', 'class' => 'form-control input-md', 'placeholder' => 'Discount Customer'), $diskon_toko);
                                    ?>
                                </div>
                            </div>
                            <label class='label-control col-sm-2'><b>Reference By</b></label>
                            <div class='col-sm-4'>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-building"></i></span>
                                    <?php
                                    $rows_reference        = array(
                                        ''            => 'Select An Option',
                                        'Event'        => 'Event',
                                        'Call'        => 'Call',
                                        'Sales'        => 'Sales',
                                        'Socmed'    => 'Social Media',
                                        'Website'    => 'Website',
                                        'Agent'        => 'Agent',
                                        'Adword'    => 'Google Adword'
                                    );
                                    echo form_dropdown('reference_by', $rows_reference, $reference_by, array('id' => 'reference_by', 'class' => 'form-control input-md select2'));
                                    ?>
                                </div>
                            </div>

                        </div>
                        <div class="form-group row" id="detail_reff">
                            <label class='label-control col-sm-2'><b>Reference Name </b></label>
                            <div class='col-sm-4'>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                    <?php
                                    echo form_input(array('id' => 'reference_name', 'name' => 'reference_name', 'class' => 'form-control input-md', 'placeholder' => 'Reference Name'), $reference_name);
                                    ?>
                                </div>
                            </div>
                            <label class='label-control col-sm-2'><b>Reference Phone</b></label>
                            <div class='col-sm-4'>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                                    <?php
                                    echo form_input(array('id' => 'reference_phone', 'name' => 'reference_phone', 'class' => 'form-control input-md', 'placeholder' => 'Reference Phone'), $reference_phone);
                                    ?>
                                </div>
                            </div>
                        </div>
                        <?php
                        foreach ($restAddress as $key => $value) { ?>
                            <div class="form-group row">
                                <label class='label-control col-sm-2'><b>Invoice Address </b></label>
                                <div class='col-sm-4'>
                                    <input type="hidden" name='invoice_address[<?= $key; ?>][id]' value='<?= $value['id'] ?>'>
                                    <?php
                                    echo form_textarea(array('name' => 'invoice_address[' . $key . '][address]', 'class' => 'form-control input-md', 'cols' => '75', 'rows' => '2', 'autocomplete' => 'off', 'placeholder' => 'Invoice Address'), $value['invoice_address']);
                                    ?>
                                    <span class='delAddress text-danger'>Hapus</span>
                                </div>
                            </div>
                        <?php
                        }
                        ?>

                        <div class='invAddress'></div>
                        <div class="form-group row">
                            <div class='col-sm-2'></div>
                            <div class='col-sm-4'>
                                <button type='button' class='btn btn-sm btn-primary' id='invAddress'>Add Invoice Address</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="data_pic" class="tab-pane fade">
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="form-group row">
                            <label for="nm_pic" class="col-sm-2 control-label">PIC Name <font size="4" color="red"><B>*</B></font></label>
                            <div class="col-sm-4">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-building"></i></span>
                                    <input type="text" class="form-control input-md" id="nm_pic" name="nm_pic" maxlength="45" placeholder="PIC Name" autocomplete='off' required value='<?= $nm_pic; ?>'>
                                    <input type="hidden" class="form-control input-md" id="id_pic" name="id_pic" maxlength="45" placeholder="PIC ID" autocomplete='off' required value='<?= $id_pic; ?>'>
                                </div>
                            </div>
                            <label for="divisi" class="col-sm-2 control-label">Division <font size="4" color="red"><B>*</B></font></label>
                            <div class="col-sm-4">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-building"></i></span>
                                    <input type="text" class="form-control" id="divisi" name="divisi" maxlength="45" placeholder="Division" required autocomplete='off' value='<?= $divisi; ?>'>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="hp" class="col-sm-2 control-label">Contact Number <font size="4" color="red"><B>*</B></font></label>
                            <div class="col-sm-4">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-building"></i></span>
                                    <input type="text" class="form-control" id="hp" name="hp" maxlength="15" placeholder="Contact Number" autocomplete='off' required value='<?= $hp; ?>'>
                                </div>
                            </div>
                            <label for="email_pic" class="col-sm-2 control-label">Email Address</label>
                            <div class="col-sm-4">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                                    <input type="text" class="form-control" id="email_pic" name="email_pic" maxlength="45" placeholder="Email Address" autocomplete='off' value='<?= $email_pic; ?>'>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <?php if (empty($tanda)) { ?>
                                        <button type="submit" name="saved" class="btn btn-success" id="saved">Save</button>
                                    <?php } ?>
                                    <button type="button" class="btn btn-danger" style='margin-left:5px;' name="back" id="back">Back</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="list_pic"></div>
                </div>
            </div>
        </div>
    </div>
</form>

<style>
    .delAddress {
        cursor: pointer;
    }
</style>

<script>
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%'
        });
    })

    $(document).on('change', '#provinsi', function(e) {
        e.preventDefault();
        $.ajax({
            url: base_url + active_controller + '/getDistrict',
            cache: false,
            type: "POST",
            data: "id_prov=" + this.value,
            dataType: "json",
            success: function(data) {
                $("#kota").html(data.option).trigger("chosen:updated");
            }
        });
    });

    $(document).on('click', '#back', function() {
        window.location.href = base_url + active_controller
    });

    $(document).on('click', '.delAddress', function() {
        $(this).parent().parent().remove();
    });

    $(document).on('click', '#invAddress', function() {
        let Address = "<div class='form-group row'><label class='label-control col-sm-2'><b>Invoice Address</b></label><div class='col-sm-4'><textarea name='address_new[]' class='form-control input-md' cols='75' row='2'></textarea><span class='delAddress text-danger'>Hapus</span></div></div>";
        $('.invAddress').append(Address)
    });

    $('#saved').click(function(e) {
        e.preventDefault();
        //Customer
        var nm_customer = $('#nm_customer').val();
        var bidang_usaha = $('#bidang_usaha').val();
        var produk_jual = $('#produk_jual').val();
        var kredibilitas = $('#kredibilitas').val();
        var alamat = $('#alamat').val();
        var provinsi = $('#provinsi').val();
        var kota = $('#kota').val();
        var kode_pos = $('#kode_pos').val();
        var telpon = $('#telpon').val();
        var fax = $('#fax').val();
        var npwp = $('#npwp').val();
        var alamat_npwp = $('#alamat_npwp').val();
        var kdcab = $('#kdcab').val();
        var id_marketing = $('#id_marketing').val();
        var website = $('#website').val();
        var sts_aktif = $('#sts_aktif').val();
        var diskon_toko = $('#diskon_toko').val();
        var reference_by = $('#reference_by').val();
        var reference_name = $('#reference_name').val();
        var reference_phone = $('#reference_phone').val();

        //PIC Customer
        var nm_pic = $('#nm_pic').val();
        var divisi = $('#divisi').val();
        var hp = $('#hp').val();
        var email_pic = $('#email_pic').val();

        if (nm_customer == '' || nm_customer == null || nm_customer == '-' || nm_customer == '0') {
            swal({
                title: "Error Message!",
                text: 'Customer Name in master customer tab is empty, please input first ...',
                type: "warning"
            });
            return false;
        }
        // if(bidang_usaha=='' || bidang_usaha==null || bidang_usaha=='-' || bidang_usaha=='0'){
        //     swal({
        //         title	: "Error Message!",
        //         text	: 'Business Field in master customer tab is empty, please input first ...',
        //         type	: "warning"
        //     });
        //     return false;
        // }
        // if(produk_jual=='' || produk_jual==null || produk_jual=='-' || produk_jual=='0'){
        //     swal({
        //         title	: "Error Message!",
        //         text	: 'Selling Producte in master customer tab is empty, please input first ...',
        //         type	: "warning"
        //     });
        //     return false;
        // }
        // if(kredibilitas=='' || kredibilitas==null || kredibilitas=='-' || kredibilitas=='0'){
        //     swal({
        //         title	: "Error Message!",
        //         text	: 'Credibility in master customer tab is empty, please input first ...',
        //         type	: "warning"
        //     });
        //     return false;
        // }
        if (alamat == '' || alamat == null || alamat == '-' || alamat == '0') {
            swal({
                title: "Error Message!",
                text: 'Address in master customer tab is empty, please input first ...',
                type: "warning"
            });
            return false;
        }
        // if(npwp=='' || npwp==null || npwp=='-' || npwp=='0'){
        //     swal({
        //         title	: "Error Message!",
        //         text	: 'Tax ID in master customer tab is empty, please input first ...',
        //         type	: "warning"
        //     });
        //     return false;
        // }
        // if(alamat_npwp=='' || alamat_npwp==null || alamat_npwp=='-' || alamat_npwp=='0'){
        //     swal({
        //         title	: "Error Message!",
        //         text	: 'Tax ID Address in master customer tab is empty, please input first ...',
        //         type	: "warning"
        //     });
        //     return false;
        // }

        if (nm_pic == '' || nm_pic == null || nm_pic == '-' || nm_pic == '0') {
            swal({
                title: "Error Message!",
                text: 'PIC name in PIC customer tab is empty, please input first ...',
                type: "warning"
            });
            return false;
        }
        if (divisi == '' || divisi == null || divisi == '-' || divisi == '0') {
            swal({
                title: "Error Message!",
                text: 'Division in PIC customer tab is empty, please input first ...',
                type: "warning"
            });
            return false;
        }
        if (hp == '' || hp == null || hp == '-' || hp == '0') {
            swal({
                title: "Error Message!",
                text: 'PIC phone in PIC customer tab is empty, please input first ...',
                type: "warning"
            });
            return false;
        }
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
                    var formData = new FormData($('#form_proses_bro')[0]);
                    var baseurl = base_url + active_controller + '/add';
                    $.ajax({
                        url: baseurl,
                        type: "POST",
                        data: formData,
                        cache: false,
                        dataType: 'json',
                        processData: false,
                        contentType: false,
                        beforeSend: function() {
                            $(this).prop('disabled', true);
                        },
                        success: function(data) {
                            if (data.status == 1) {
                                swal({
                                    title: "Save Success!",
                                    text: data.pesan,
                                    type: "success",
                                    timer: 7000
                                });
                                window.location.href = base_url + active_controller;
                            } else {
                                swal({
                                    title: "Save Failed!",
                                    text: data.pesan,
                                    type: "warning",
                                    timer: 7000
                                });
                            }
                            $('#saved').prop('disabled', false);
                        },
                        error: function() {

                            swal({
                                title: "Error Message !",
                                text: 'An Error Occured During Process. Please try again..',
                                type: "warning",
                                timer: 7000
                            });
                            $('#saved').prop('disabled', false);
                        }
                    });
                } else {
                    swal("Cancelled", "Data can be process again :)", "error");
                    $('#saved').prop('disabled', false);
                    return false;
                }
            });
    });
</script>