<?php
$ENABLE_ADD     = has_permission('Master_customers.Add');
$ENABLE_MANAGE  = has_permission('Master_customers.Manage');
$ENABLE_VIEW    = has_permission('Master_customers.View');
$ENABLE_DELETE  = has_permission('Master_customers.Delete');
foreach ($results['cus'] as $cus) {
}
?>
<div class="box box-primary">
    <div class="box-body">
        <form id="data-form" method="post">
            <div class="col-sm-12">
                <div class="input_fields_wrap2">
                    <div class="row">
                        <div class="col-sm-12">
                            <center>
                                <h3>DETAIL IDENTITAS CUSTOMER</h3>
                            </center>
                            <div class="col-sm-6">
                                <div class="form-group row">
                                    <div class="col-md-6">
                                        <label for="id_supplier">Id Customer</label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="id_customer" value='<?= $cus->id_customer ?>' required name="id_customer" readonly placeholder="Id Suplier">
                                    </div>
                                </div>

                                <div class="form-group row" hidden>
                                    <div class="col-md-6">
                                        <label for="id_category_customer">Category Customer</label>
                                    </div>
                                    <div class="col-md-6">
                                        <select id="id_category_customer" name="id_category_customer" class="form-control select" required>
                                            <option value="">--Pilih--</option>
                                            <?php foreach ($results['category'] as $category) {
                                                $select = $cus->id_category_customer == $category->id_category_customer ? 'selected' : '';
                                            ?>
                                                <option value="<?= $category->id_category_customer ?>" <?= $select ?>><?= $category->name_category_customer ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-6">
                                        <label for="customer">Nama Customer</label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="name_customer" value='<?= $cus->name_customer ?>' required name="name_customer" placeholder="Nama Customer">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-md-6">
                                        <label for="customer">Telephone</label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="telephone" value='<?= $cus->telephone ?>' required name="telephone" placeholder="Nomor Telephone">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-6">
                                        <label for="customer"></label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="telephone_2" value='<?= $cus->telephone_2 ?>' name="telephone_2" placeholder="Nomor Telephone">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-6">
                                        <label for="customer">Fax</label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="fax" required name="fax" value='<?= $cus->fax ?>' placeholder="Nomor Fax">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-6">
                                        <label for="customer">Email</label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="email" required name="email" value='<?= $cus->name_customer ?>' placeholder="email@domain.adress">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-6">
                                        <label for="customer">Tanggal Mulai</label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="date" class="form-control" id="start_date" required name="start_date" value='<?= $cus->start_date ?>' placeholder="Tanggal Mulai">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-6">
                                        <label for="id_category_customer">Marketing</label>
                                    </div>
                                    <div class="col-md-6">
                                        <select id="id_karyawan" name="id_karyawan" class="form-control select" required>
                                            <option value="">--pilih--</option>
                                            <?php foreach ($results['karyawan'] as $karyawan) {
                                                $select = $cus->id_karyawan == $karyawan->id ? 'selected' : '';
                                            ?>
                                                <option value="<?= $karyawan->id ?>" <?= $select ?>><?= ucfirst(strtolower($karyawan->nm_karyawan)) ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group row">
                                    <div class="col-md-6">
                                        <label for="id_category_supplier">Provinsi</label>
                                    </div>
                                    <div class="col-md-6">
                                        <select id="id_prov" name="id_prov" class="form-control select" onchange="get_kota()" required>
                                            <option value="">--Pilih--</option>
                                            <?php foreach ($results['prof'] as $prof) {
                                                $select = $cus->id_prov == $prof->id_prov ? 'selected' : '';
                                            ?>
                                                <option value="<?= $prof->id_prov ?>" <?= $select ?>><?= ucfirst(strtolower($prof->nama)) ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-md-6">
                                        <label for="id_category_supplier">Kota</label>
                                    </div>
                                    <div class="col-md-6">
                                        <select id="id_kota" name="id_kota" class="form-control select" required>
                                            <option value="">--Pilih--</option>
                                            <?php foreach ($results['kota'] as $kota) {
                                                $select = $cus->id_kota == $kota->id_kota ? 'selected' : '';
                                            ?>
                                                <option value="<?= $kota->id_kota ?>" <?= $select ?>><?= ucfirst(strtolower($kota->nama_kota)) ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-md-6">
                                        <label for="customer">Alamat</label>
                                    </div>
                                    <div class="col-md-6">
                                        <textarea type="text" name="address_office" id="address_office" class="form-control input-sm required w70" placeholder="Alamat" readonly><?= $cus->address_office ?></textarea>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-md-6">
                                        <label for="customer">Kode Pos</label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="zip_code" value='<?= $cus->zip_code ?>' required name="zip_code" placeholder="Kode Pos" readonly>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-6">
                                        <label for="customer">Longtitude</label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="longitude" value='<?= $cus->longitude ?>' required name="longitude" placeholder="Longtitude" readonly>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-6">
                                        <label for="customer">Latitude</label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="latitude" value='<?= $cus->latitude ?>' required name="latitude" placeholder="Latitude" readonly>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-6">
                                        <label for="customer">Status</label>
                                    </div>
                                    <div class="col-md-6">
                                        <label>
                                            <input type="radio" class="radio-control" id="activation" name="activation" value="aktif" <?= (($cus->activation == 'aktif') ? 'checked' : '') ?> required> Aktif
                                        </label>
                                        <label>
                                            <input type="radio" class="radio-control" id="activation" name="activation" value="inaktif" <?= (($cus->activition == 'inaktif') ? 'checked' : '') ?> required> Non aktif
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-6">
                                        <label for="customer">Facility</label>
                                    </div>
                                    <div class="col-md-6">
                                        <label>
                                            <input type="radio" class="radio-control" id="facility" name="facility" value="DPIL" <?= (($cus->facility == 'DPIL') ? 'checked' : '') ?> required> DPIL
                                        </label>
                                        <label>
                                            <input type="radio" class="radio-control" id="facility" name="facility" value="Kawasan Berikat" <?= (($cus->facility == 'Kawasan Berikat') ? 'checked' : '') ?> required> Kawasan Berikat
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <center>
                                <h3>CATEGORY CUSTOMER</h3>
                            </center>
                            <div class="form-group row">
                                <div class="col-md-12">

                                    <table class='table table-bordered table-striped'>
                                        <thead>
                                            <tr class='bg-blue'>
                                                <td align='center'><b>Category Customer</b></td>
                                                <td style="width: 50px;" align='center'>
                                                    <?php
                                                    echo form_button(array('type' => 'button', 'class' => 'btn btn-sm btn-success', 'value' => 'back', 'content' => 'Add', 'id' => 'add-category'));
                                                    ?>
                                                </td>
                                            </tr>

                                        </thead>
                                        <tbody id='list_category'>
                                            <?php
                                            $loop = 0;
                                            foreach ($results['cate'] as $cate) {
                                                $loop++;
                                                echo "<tr id='tr_" . $loop . "'>";
                                                echo "<td align='left'>";
                                                echo "<select id='data2_" . $loop . "_id_category_customer' name='data2[" . $loop . "][id_category_customer]' class='form-control select' required>";
                                                echo "<option value='$cate->name_category_customer'>$cate->name_category_customer</option>";
                                                foreach ($results['category'] as $category) {
                                                    echo "<option value='$category->name_category_customer'>$category->name_category_customer</option>";
                                                }
                                                echo "</select>";
                                                echo "</td>";
                                                echo "<td align='center'><button type='button' class='btn btn-sm btn-danger' title='Hapus Data' data-role='qtip' onClick='return DelItem2(" . $loop . ");'><i class='fa fa-trash-o'></i></button></td></tr>";
                                                echo "</tr>";
                                            } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <center>
                                <h3>PIC</h3>
                            </center>
                            <div class="form-group row">
                                <div class="col-md-12">
                                    <table class='table table-bordered table-striped'>
                                        <thead>
                                            <tr class='bg-blue'>
                                                <td align='center'><b>Nama PIC</b></td>
                                                <td align='center'><b>Nomor Telp</b></td>
                                                <td align='center'><b>Email</b></td>
                                                <td align='center'><b>Jabatan</b></td>
                                                <td style="width: 50px;" align='center'>
                                                    <?php
                                                    echo form_button(array('type' => 'button', 'class' => 'btn btn-sm btn-success', 'value' => 'back', 'content' => 'Add', 'id' => 'add-payment'));
                                                    ?>
                                                </td>
                                            </tr>

                                        </thead>
                                        <tbody id='list_payment'>
                                            <?php
                                            $defaultRows = [
                                                ['name' => 'PIC'],
                                                ['name' => 'Owner'],
                                                ['name' => 'KA Toko']
                                            ];

                                            // Cek apakah ada data yang akan diedit
                                            $loop = 0;
                                            if (!empty($results['pic'])) {
                                                // Mode Edit (menggunakan data dari database)
                                                foreach ($results['pic'] as $pic) {
                                                    $loop++;
                                                    echo "<tr id='tr_" . $loop . "'>";
                                                    foreach (['name_pic', 'phone_pic', 'email_pic', 'position_pic'] as $field) {
                                                        $value = $pic->$field;
                                                        $readonly = ($field == 'position_pic') ? 'readonly' : '';
                                                        echo "<td align='left'>";
                                                        echo "<input type='text' class='form-control input-sm' name='data1[" . $loop . "][$field]' value='$value' id='data1_" . $loop . "_$field' $readonly required>";
                                                        echo "</td>";
                                                    }
                                                    echo "<td align='center'><button type='button' class='btn btn-sm btn-danger' title='Hapus Data' data-role='qtip' onClick='return DelItem(" . $loop . ");'><i class='fa fa-trash-o'></i></button></td>";
                                                    echo "</tr>";
                                                }
                                            } else {
                                                // Mode Tambah (menggunakan data default)
                                                foreach ($defaultRows as $index => $row) {
                                                    $loop = $index + 1;
                                                    echo "<tr id='tr_$loop'>";
                                                    foreach (['name_pic', 'phone_pic', 'email_pic', 'position_pic'] as $field) {
                                                        $value = ($field == 'position_pic') ? $row['name'] : '';
                                                        $readonly = ($field == 'position_pic') ? 'readonly' : '';
                                                        echo "<td align='left'>";
                                                        echo "<input type='text' class='form-control input-sm' name='data1[$loop][$field]' id='data1_{$loop}_{$field}' value='$value' $readonly required>";
                                                        echo "</td>";
                                                    }
                                                    echo "<td align='center'><button type='button' class='btn btn-sm btn-danger' title='Hapus Data' data-role='qtip' onClick='return DelItem(" . $loop . ");'><i class='fa fa-trash-o'></i></button></td>";
                                                    echo "</tr>";
                                                }
                                            }
                                            ?>
                                        </tbody>

                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <center>
                                <h3>INFORMASI PEMBAYARAN</h3>
                            </center>
                            <div class="col-sm-6">
                                <div class="col-md-12">
                                    <label for="id_supplier">
                                        <h4>Informasi Bank</h4>
                                    </label>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-6">
                                        <label for="id_supplier">Nama Bank</label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="name_bank" value="<?= $cus->name_bank ?>" required name="name_bank" placeholder="Nama Bank">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-md-6">
                                        <label for="id_category_supplier">Nomor Akun</label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="no_rekening" value="<?= $cus->no_rekening ?>" required name="no_rekening" placeholder="No Rekening">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-md-6">
                                        <label for="customer">Nama Akun</label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="nama_rekening" value="<?= $cus->nama_rekening ?>" required name="nama_rekening" placeholder="Nama Rekening">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-md-6">
                                        <label for="customer">Alamat Bank</label>
                                    </div>
                                    <div class="col-md-6">
                                        <textarea type="text" name="alamat_bank" id="alamat_bank" class="form-control input-sm required w70" placeholder="Alamat_Bank"><?= $cus->alamat_bank ?></textarea>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-md-6">
                                        <label for="customer">Swift Code</label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="swift_code" value="<?= $cus->swift_code ?>" required name="swift_code" placeholder="Swift Code">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="col-md-12">
                                    <label for="id_supplier">
                                        <h4>Informasi Pajak</h4>
                                    </label>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-6">
                                        <label for="customer">Nomor NPWP/PKP</label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="npwp" value="<?= $cus->npwp ?>" required name="npwp" placeholder="Nomor NPWP">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-6">
                                        <label for="customer">Nama NPWP/PKP</label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="npwp_name" value='<?= $cus->npwp_name ?>' required name="npwp_name" placeholder="Nama NPWP">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-6">
                                        <label for="customer">Alamat NPWP</label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="npwp_address" value='<?= $cus->npwp_address ?>' required name="npwp_address" placeholder="Alamat NPWP">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-6">
                                        <label for="id_category_customer">Term Of Payment</label>
                                    </div>
                                    <div class="col-md-6">
                                        <select id="payment_term" name="payment_term" class="form-control select" required>
                                            <option value="<?= $cus->payment_term ?>"><?= $cus->payment_term ?></option>
                                            <option value="Cash Before Delivery">Cash Before Delivery</option>
                                            <option value="Cash on Delivery">Cash on Delivery</option>
                                            <option value="30 Day">30 Day-</option>
                                            <option value="45 Day">45 Day</option>
                                            <option value="60 Day">60 Day</option>
                                            <option value="DP">DP</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-6">
                                        <label for="customer">Nominal DP</label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="nominal_dp" value='<?= $cus->nominal_dp ?>' required name="nominal_dp" placeholder="Alamat NPWP">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-6">
                                        <label for="id_category_customer">Sisa Pembayaran</label>
                                    </div>
                                    <div class="col-md-6">
                                        <select id="sisa_pembayaran" name="sisa_pembayaran" class="form-control select" required>
                                            <option value="<?= $cus->sisa_pembayaran ?>"><?= $cus->sisa_pembayaran ?></option>
                                            <option value="15 After Delifery">15 After Delifery</option>
                                            <option value="30 After Delifery">30 After Delifery</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <center>
                                <h3>INFORMASI INVOICE</h3>
                            </center>
                            <div class="col-sm-12">
                                <div class="form-group-row">
                                    <div class="col-md-3">
                                        <label for="customer">Hari Terima</label>
                                    </div>
                                    <div class="col-md-9">
                                        <label>
                                            <input type="checkbox" class="radio-control" id="senin" name="senin" <?= (($cus->senin == 'Y') ? 'checked' : '') ?> value="Y" required> Senin
                                        </label>
                                        &nbsp
                                        <label>
                                            <input type="checkbox" class="radio-control" id="selasa" name="selasa" <?= (($cus->selasa == 'Y') ? 'checked' : '') ?> value="Y" required> Selasa
                                        </label>
                                        &nbsp
                                        <label>
                                            <input type="checkbox" class="radio-control" id="rabu" name="rabu" <?= (($cus->rabu == 'Y') ? 'checked' : '') ?> value="Y" required> Rabu
                                        </label>
                                        &nbsp
                                        <label>
                                            <input type="checkbox" class="radio-control" id="kamis" name="kamis" <?= (($cus->kamis == 'Y') ? 'checked' : '') ?> value="Y" required> Kamis
                                        </label>
                                        &nbsp
                                        <label>
                                            <input type="checkbox" class="radio-control" id="jumat" name="jumat" <?= (($cus->jumat == 'Y') ? 'checked' : '') ?> value="Y" required> Jumat
                                        </label>
                                        &nbsp
                                        <label>
                                            <input type="checkbox" class="radio-control" id="sabtu" name="sabtu" <?= (($cus->sabtu == 'Y') ? 'checked' : '') ?> value="Y" required> Sabtu
                                        </label>
                                        &nbsp
                                        <label>
                                            <input type="checkbox" class="radio-control" id="minggu" name="minggu" <?= (($cus->minggu == 'Y') ? 'checked' : '') ?> value="Y" required> Minggu
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="col-sm-6">
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <label for="customer">Waktu Penerimaan Invoice</label>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <label for="customer">Start</label>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="time" class="form-control" id="start_recive" value='<?= $cus->start_recive ?>' required name="start_recive" placeholder="Latitude">
                                        </div>
                                        <div class="col-md-2">
                                            <label for="customer">END</label>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="time" class="form-control" id="end_recive" value='<?= $cus->end_recive ?>' required name="end_recive" placeholder="Latitude">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group row">
                                        <div class="col-md-6">
                                            <label for="customer">Alamat Invoice</label>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <textarea type="text" name="address_invoice" id="address_invoice" class="form-control input-sm required w70" placeholder="Alamat"><?= $cus->adress_invoice ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <center>
                                <h3>PERSYARATAN PEMBAYARAN</h3>
                            </center>
                            <div class="col-sm-12">
                                <div class="col-sm-4">
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <label>
                                                <input type="checkbox" class="radio-control" id="berita_acara" name="berita_acara" <?= (($cus->berita_acara == 'Y') ? 'checked' : '') ?> value="Y" required> Berita Acara
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <label>
                                                <input type="checkbox" class="radio-control" id="faktur" name="faktur" <?= (($cus->faktur == 'Y') ? 'checked' : '') ?> value="Y" required> Faktur Pajak
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <label>
                                                <input type="checkbox" class="radio-control" id="tdp" name="tdp" <?= (($cus->tdp == 'Y') ? 'checked' : '') ?> value="Y" required> TDP
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <label>
                                                <input type="checkbox" class="radio-control" id="real_po" name="real_po" <?= (($cus->real_po == 'Y') ? 'checked' : '') ?> value="Y" required> Real PO
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <label>
                                                <input type="checkbox" class="radio-control" id="ttd_specimen" name="ttd_specimen" <?= (($cus->ttd_specimen == 'Y') ? 'checked' : '') ?> value="Y" required> TTD Specimen / Tax Invoice Serial Number
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <label>
                                                <input type="checkbox" class="radio-control" id="payement_certificate" name="payement_certificate" <?= (($cus->payement_certificate == 'Y') ? 'checked' : '') ?> value="Y" required> Payment Certificate
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <label>
                                                <input type="checkbox" class="radio-control" id="photo" name="photo" <?= (($cus->photo == 'Y') ? 'checked' : '') ?> value="Y" required> Photo
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <label>
                                                <input type="checkbox" class="radio-control" id="siup" name="siup" <?= (($cus->siup == 'Y') ? 'checked' : '') ?> value="Y" required> SIUP
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <label>
                                                <input type="checkbox" class="radio-control" id="spk" name="spk" <?= (($cus->spk == 'Y') ? 'checked' : '') ?> value="Y" required> SPK
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <label>
                                                <input type="checkbox" class="radio-control" id="delivery_order" name="delivery_order" <?= (($cus->delivery_order == 'Y') ? 'checked' : '') ?> value="Y" required> Delivery Order
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <label>
                                                <input type="checkbox" class="radio-control" id="need_npwp" name="need_npwp" <?= (($cus->need_npwp == 'Y') ? 'checked' : '') ?> value="Y" required> NPWP
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
    var base_url = '<?php echo base_url(); ?>';
    var active_controller = '<?php echo ($this->uri->segment(1)); ?>';

    $(document).ready(function() {
        // Ambil form berdasarkan ID
        var form = document.getElementById("data-form");

        // Ambil semua elemen input, select, textarea, dan button dalam form
        var elements = form.querySelectorAll("input, select, textarea, button");

        // Loop untuk menonaktifkan semua elemen
        elements.forEach(function(element) {
            element.disabled = true;
        });

        var data_pay = <?php echo json_encode($results['supplier']); ?>;
        $('.select').select2({
            width: '100%'
        });
        ///INPUT PERKIRAAN KIRIM


        var max_fields2 = 10; //maximum input boxes allowed
        var wrapper2 = $(".input_fields_wrap2"); //Fields wrapper
        var add_button2 = $(".add_field_button2"); //Add button ID

        //console.log(persen);

        var x2 = 1; //initlal text box count
        $(add_button2).click(function(e) { //on add input button click
            e.preventDefault();
            if (x2 < max_fields2) { //max input box allowed
                x2++; //text box increment

                $(wrapper2).append('<div class="row">' +
                    '<div class="col-xs-1">' + x2 + '</div>' +
                    '<div class="col-xs-3">' +
                    '<div class="input-group">' +
                    '<input type="text" name="hd' + x2 + '[produk]"  class="form-control input-sm" value="">' +
                    '</div>' +
                    '<div class="input-group">' +
                    '<input type="text" name="hd' + x2 + '[costcenter]"  class="form-control input-sm" value="">' +
                    '</div>' +
                    '<div class="input-group">' +
                    '<input type="text" name="hd' + x2 + '[mesin]"  class="form-control input-sm" value="">' +
                    '</div>' +
                    '<div class="input-group">' +
                    '<input type="text" name="hd' + x2 + '[mold_tools]"  class="form-control input-sm" value="">' +
                    '</div>' +
                    '</div>' +
                    '<a href="#" class="remove_field2">Remove</a>' +
                    '</div>'); //add input box
                $('#datepickerxxr' + x2).datepicker({
                    format: 'dd-mm-yyyy',
                    autoclose: true
                });
            }
        });

        $(wrapper2).on("click", ".remove_field2", function(e) { //user click on remove text
            e.preventDefault();
            $(this).parent('div').remove();
            x2--;
        })


        $('#add-category').click(function() {
            var jumlah = $('#list_category').find('tr').length;
            if (jumlah == 0 || jumlah == null) {
                var ada = 0;
                var loop = 1;
            } else {
                var nilai = $('#list_category tr:last').attr('id');
                var jum1 = nilai.split('_');
                var loop = parseInt(jum1[1]) + 1;
            }
            Template = '<tr id="tr_' + loop + '">';
            Template += '<td align="left">';
            Template += '<select id="data2_' + loop + '_id_category_customer" name="data2[' + loop + '][id_category_customer]" class="form-control select" required>';
            Template += '<option value="">--pilih--</option>';
            Template += '<?php foreach ($results['category'] as $category) { ?>';
            Template += '<option value="<?= $category->name_category_customer ?>"><?= ucfirst(strtolower($category->name_category_customer)) ?></option>';
            Template += '<?php } ?>';
            Template += '</select>';
            Template += '</td>';
            Template += '</td>';
            Template += '<td align="center"><button type="button" class="btn btn-sm btn-danger" title="Hapus Data" data-role="qtip" onClick="return DelItem2(' + loop + ');"><i class="fa fa-trash-o"></i></button></td>';
            Template += '</tr>';
            $('#list_category').append(Template);
        });
        $('#add-payment').click(function() {
            var jumlah = $('#list_payment').find('tr').length;
            if (jumlah == 0 || jumlah == null) {
                var ada = 0;
                var loop = 1;
            } else {
                var nilai = $('#list_payment tr:last').attr('id');
                var jum1 = nilai.split('_');
                var loop = parseInt(jum1[1]) + 1;
            }
            Template = '<tr id="tr_' + loop + '">';
            Template += '<td align="left">';
            Template += '<input type="text" class="form-control input-sm" name="data1[' + loop + '][name_pic]" id="data1_' + loop + '_name_pic" label="FALSE" div="FALSE">';
            Template += '</td>';
            Template += '<td align="left">';
            Template += '<input type="text" class="form-control input-sm" name="data1[' + loop + '][phone_pic]" id="data1_' + loop + '_phone_pic" label="FALSE" div="FALSE">';
            Template += '</td>';
            Template += '<td align="left">';
            Template += '<input type="text" class="form-control input-sm" name="data1[' + loop + '][email_pic]" id="data1_' + loop + '_email_pic" label="FALSE" div="FALSE">';
            Template += '</td>';
            Template += '<td align="left">';
            Template += '<input type="text" class="form-control input-sm" name="data1[' + loop + '][position_pic]" id="data1_' + loop + '_position_pic" label="FALSE" div="FALSE">';
            Template += '</td>';
            Template += '<td align="center"><button type="button" class="btn btn-sm btn-danger" title="Hapus Data" data-role="qtip" onClick="return DelItem(' + loop + ');"><i class="fa fa-trash-o"></i></button></td>';
            Template += '</tr>';
            $('#list_payment').append(Template);
        });



        $('#simpan-com').click(function(e) {
            e.preventDefault();
            var deskripsi = $('#deskripsi').val();
            var image = $('#image').val();
            var idtype = $('#inventory_1').val();

            var data, xhr;
            swal({
                    title: "Are you sure?",
                    text: "You will not be able to process again this data!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-danger",
                    confirmButtonText: "Yes, Process it!",
                    cancelButtonText: "No, cancel process!",
                    closeOnConfirm: true,
                    closeOnCancel: false
                },
                function(isConfirm) {
                    if (isConfirm) {
                        var formData = new FormData($('#data-form')[0]);
                        var baseurl = siteurl + 'master_customers/saveEditcustomer';
                        $.ajax({
                            url: baseurl,
                            type: "POST",
                            data: formData,
                            cache: false,
                            dataType: 'json',
                            processData: false,
                            contentType: false,
                            success: function(data) {
                                if (data.status == 1) {
                                    swal({
                                        title: "Save Success!",
                                        text: data.pesan,
                                        type: "success",
                                        timer: 7000,
                                        showCancelButton: false,
                                        showConfirmButton: false,
                                        allowOutsideClick: false
                                    });
                                    window.location.href = base_url + active_controller;
                                } else {

                                    if (data.status == 2) {
                                        swal({
                                            title: "Save Failed!",
                                            text: data.pesan,
                                            type: "warning",
                                            timer: 7000,
                                            showCancelButton: false,
                                            showConfirmButton: false,
                                            allowOutsideClick: false
                                        });
                                    } else {
                                        swal({
                                            title: "Save Failed!",
                                            text: data.pesan,
                                            type: "warning",
                                            timer: 7000,
                                            showCancelButton: false,
                                            showConfirmButton: false,
                                            allowOutsideClick: false
                                        });
                                    }

                                }
                            },
                            error: function() {

                                swal({
                                    title: "Error Message !",
                                    text: 'An Error Occured During Process. Please try again..',
                                    type: "warning",
                                    timer: 7000,
                                    showCancelButton: false,
                                    showConfirmButton: false,
                                    allowOutsideClick: false
                                });
                            }
                        });
                    } else {
                        swal("Cancelled", "Data can be process again :)", "error");
                        return false;
                    }
                });
        });

    });

    function get_kota() {
        var id_prov = $("#id_prov").val();
        $.ajax({
            type: "GET",
            url: siteurl + 'master_customers/getkota',
            data: "id_prov=" + id_prov,
            success: function(html) {
                $("#id_kota").html(html);
            }
        });
    }

    function DelItem(id) {
        $('#list_payment #tr_' + id).remove();

    }

    function DelItem2(id) {
        $('#list_category #tr_' + id).remove();

    }
</script>