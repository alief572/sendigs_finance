<?php
//ipp
$id              = (!empty($header[0]->id)) ? $header[0]->id : '';
$no_ipp         = (!empty($header[0]->no_ipp)) ? $header[0]->no_ipp : '';
$id_customer      = (!empty($header[0]->id_customer)) ? $header[0]->id_customer : '';
$project           = (!empty($header[0]->project)) ? $header[0]->project : '';
$referensi       = (!empty($header[0]->referensi)) ? $header[0]->referensi : '';
$id_top           = (!empty($header[0]->id_top)) ? $header[0]->id_top : '';
$keterangan       = (!empty($header[0]->keterangan)) ? $header[0]->keterangan : '';
//delivery
$delivery_type       = (!empty($header[0]->delivery_type)) ? $header[0]->delivery_type : '';
$id_country           = (!empty($header[0]->id_country)) ? $header[0]->id_country : 'IDN';
$delivery_category  = (!empty($header[0]->delivery_category)) ? $header[0]->delivery_category : '';
$area_destinasi       = (!empty($header[0]->area_destinasi)) ? $header[0]->area_destinasi : '';
$delivery_address   = (!empty($header[0]->delivery_address)) ? $header[0]->delivery_address : '';
$shipping_method       = (!empty($header[0]->shipping_method)) ? $header[0]->shipping_method : '';
$packing               = (!empty($header[0]->packing)) ? $header[0]->packing : '';
$guarantee           = (!empty($header[0]->guarantee)) ? $header[0]->guarantee : '';
$delivery_date               = (!empty($header[0]->delivery_date)) ? $header[0]->delivery_date : '';
$instalasi_option    = (!empty($header[0]->instalasi_option)) ? $header[0]->instalasi_option : '';

$delivery_type1    = (!empty($header[0]->delivery_type) and $header[0]->delivery_type == 'local') ? 'selected' : '';
$delivery_type2 = (!empty($header[0]->delivery_type) and $header[0]->delivery_type == 'export') ? 'selected' : '';

$instalasi1    = (!empty($header[0]->instalasi_option) and $header[0]->instalasi_option == 'N') ? 'selected' : '';
$instalasi2 = (!empty($header[0]->instalasi_option) and $header[0]->instalasi_option == 'Y') ? 'selected' : '';
// print_r($header);
?>

<div class="box box-primary">
    <div class="box-body">
        <form id="data-form" method="post"><br>
            <div class="form-group row">
                <div class="col-12">
                    <button type="button" class="btn btn-sm btn-primary">Molded Grating</button>
                    <button type="button" class="btn btn-sm btn-secondary">Pultrution</button>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-2">
                    <label for="customer">Customer Name <span class='text-red'>*</span></label>
                </div>
                <div class="col-md-4">
                    <select id="id_customer" name="id_customer" class="form-control input-md chosen-select">
                        <option value="0">Select An Customer</option>
                        <?php foreach ($customer as $val => $value) {
                            $sel = ($value['id_customer'] == $id_customer) ? 'selected' : '';
                        ?>
                            <option value="<?= $value['id_customer']; ?>" <?= $sel; ?>><?= strtoupper($value['nm_customer']) ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-2">
                    <label for="customer">Project Name <span class='text-red'>*</span></label>
                </div>
                <div class="col-md-4">
                    <input type="hidden" name="id" id="id" value="<?= $id; ?>">
                    <input type="hidden" name="no_ipp" id="no_ipp" value="<?= $no_ipp; ?>">
                    <input type="text" name="project" id="project" class='form-control input-md' required placeholder='Project Name' value="<?= $project; ?>">
                </div>
            </div>



            <div>
                <?php
                $val = 0;
                if (!empty($detail)) {
                    foreach ($detail as $val => $valx) {
                        $val++;

                        $platform           = (!empty($valx['platform']) and $valx['platform'] == 'Y') ? 'checked' : '';
                        $cover_drainage       = (!empty($valx['cover_drainage']) and $valx['cover_drainage'] == 'Y') ? 'checked' : '';
                        $facade               = (!empty($valx['facade']) and $valx['facade'] == 'Y') ? 'checked' : '';
                        $ceilling           = (!empty($valx['ceilling']) and $valx['ceilling'] == 'Y') ? 'checked' : '';
                        $partition           = (!empty($valx['partition']) and $valx['partition'] == 'Y') ? 'checked' : '';
                        $fence               = (!empty($valx['fence']) and $valx['fence'] == 'Y') ? 'checked' : '';
                        $app_indoor           = (!empty($valx['app_indoor']) and $valx['app_indoor'] == 'Y') ? 'checked' : '';
                        $app_outdoor           = (!empty($valx['app_outdoor']) and $valx['app_outdoor'] == 'Y') ? 'checked' : '';
                        $max_load           = (!empty($valx['max_load'])) ? $valx['max_load'] : '';
                        $min_load           = (!empty($valx['min_load'])) ? $valx['min_load'] : '';
                        $type_product       = (!empty($valx['type_product'])) ? $valx['type_product'] : '';

                        $file_pendukung_1   = (!empty($valx['file_pendukung_1'])) ? $valx['file_pendukung_1'] : '';
                        $file_pendukung_2   = (!empty($valx['file_pendukung_2'])) ? $valx['file_pendukung_2'] : '';
                        $color               = (!empty($valx['color'])) ? $valx['color'] : '';
                        $other_test           = (!empty($valx['other_test'])) ? $valx['other_test'] : '';

                        $food_grade           = (!empty($valx['food_grade']) and $valx['food_grade'] == 'Y') ? 'checked' : '';
                        $uv                   = (!empty($valx['uv']) and $valx['uv'] == 'Y') ? 'checked' : '';
                        $fire_reterdant_1   = (!empty($valx['fire_reterdant_1']) and $valx['fire_reterdant_1'] == 'Y') ? 'checked' : '';
                        $fire_reterdant_2   = (!empty($valx['fire_reterdant_2']) and $valx['fire_reterdant_2'] == 'Y') ? 'checked' : '';
                        $fire_reterdant_3   = (!empty($valx['fire_reterdant_3']) and $valx['fire_reterdant_3'] == 'Y') ? 'checked' : '';
                        $standard_astm       = (!empty($valx['standard_astm']) and $valx['standard_astm'] == 'Y') ? 'checked' : '';
                        $standard_bs           = (!empty($valx['standard_bs']) and $valx['standard_bs'] == 'Y') ? 'checked' : '';
                        $standard_dnv       = (!empty($valx['standard_dnv']) and $valx['standard_dnv'] == 'Y') ? 'checked' : '';

                        $surface_concave       = (!empty($valx['surface_concave']) and $valx['surface_concave'] == 'Y') ? 'checked' : '';
                        $surface_flat       = (!empty($valx['surface_flat']) and $valx['surface_flat'] == 'Y') ? 'checked' : '';
                        $id_bom_topping           = (!empty($valx['id_bom_topping'])) ? $valx['id_bom_topping'] : '';
                        $file_dokumen           = (!empty($valx['file_dokumen'])) ? $valx['file_dokumen'] : '';

                        echo "<div id='header_" . $val . "'>";
                        echo "<h4 class='text-bold text-primary'>Permintaan " . $val . "&nbsp;&nbsp;<span class='text-red text-bold delPart' data-id='" . $val . "' style='cursor:pointer;' title='Delete Part'>Delete</span></h4>";
                        echo "<div class='form-group row'>";
                        echo "<div class='col-md-2'>";
                        echo "<label>Aplikasi Kebutuhan</label>";
                        echo "</div>";
                        echo "<div class='col-md-2'>";
                        echo "<div class='form-group'>";
                        echo "<div class='checkbox'><label><input type='checkbox' name='Detail[" . $val . "][platform]' value='Y' " . $platform . ">Platform</label></div>";
                        echo "<div class='checkbox'><label><input type='checkbox' name='Detail[" . $val . "][cover_drainage]' value='Y' " . $cover_drainage . ">Cover Drainage</label></div>";
                        echo "<div class='checkbox'><label><input type='checkbox' name='Detail[" . $val . "][facade]' value='Y' " . $facade . ">Facade</label></div>";
                        echo "</div>";
                        echo "</div>";
                        echo "<div class='col-md-2'>";
                        echo "<div class='form-group'>";
                        echo "<div class='checkbox'><label><input type='checkbox' name='Detail[" . $val . "][ceilling]' value='Y' " . $ceilling . ">Ceilling</label></div>";
                        echo "<div class='checkbox'><label><input type='checkbox' name='Detail[" . $val . "][partition]' value='Y' " . $partition . ">Partition</label></div>";
                        echo "<div class='checkbox'><label><input type='checkbox' name='Detail[" . $val . "][fence]' value='Y' " . $fence . ">Fence</label></div>";
                        echo "</div>";
                        echo "</div>";
                        echo "<div class='col-md-2'>";
                        echo "<div class='form-group'><label>Aplikasi Pemasangan</label>";
                        echo "<div class='checkbox'><label><input type='checkbox' name='Detail[" . $val . "][app_indoor]' value='Y' " . $app_indoor . ">Indoor</label></div>";
                        echo "<div class='checkbox'><label><input type='checkbox' name='Detail[" . $val . "][app_outdoor]' value='Y' " . $app_outdoor . ">Outdoor</label></div>";
                        echo "</div>";
                        echo "</div>";
                        echo "	<div class='col-md-2'>";
                        echo "		<div class='form-group'><label>Max Load</label>";
                        echo "	<input type='text' name='Detail[" . $val . "][max_load]' class='form-control input-md autoNumeric0' placeholder='Max Load' value='" . $max_load . "'>";
                        echo "</div>";
                        echo "</div>";
                        echo "<div class='col-md-2'>";
                        echo "	<div class='form-group'><label>Min Load</label>";
                        echo "		<input type='text' name='Detail[" . $val . "][min_load]' class='form-control input-md autoNumeric0' placeholder='Min Load' value='" . $min_load . "'>";
                        echo "	</div>";
                        echo "</div>";
                        echo "</div>";

                        echo "<hr>";
                        echo "<div class='form-group row'>";
                        echo "	<div class='col-md-2'>";
                        echo "		<label>Type Product</label>";
                        echo "	</div>";
                        echo "	<div class='col-md-4'>";
                        echo "	<select name='Detail[" . $val . "][type_product]' id='type_product_" . $val . "' class='form-control chosen-select'>";
                        echo "		<option value='0'>All Type Product</option>";
                        foreach ($product_lv1 as $valz => $valxz) {
                            $selected = ($type_product == $valxz['code_lv1']) ? 'selected' : '';
                            echo "<option value='" . $valxz['code_lv1'] . "' " . $selected . ">" . strtoupper($valxz['nama']) . "</option>";
                        }
                        echo     "</select>";
                        echo "	</div>";
                        echo "</div>";
                        echo "<div class='form-group row'>";
                        echo "	<div class='col-md-2'>";
                        echo "		<label>List Produk</label>";
                        echo "	</div>";
                        echo "	<div class='col-md-8'>";
                        echo "	<table class='table table-striped table-bordered table-hover table-condensed'>";
                        echo "		<tr class='bg-blue'>";
                        echo "			<th class='text-center'>Product Master</th>";
                        echo "			<th class='text-center' width='15%'>Qty Order</th>";
                        echo "			<th class='text-center' width='10%'>#</th>";
                        echo "		</tr>";

                        $getdetailProduct4 = $this->db->get_where('ipp_detail_lainnya', array('category' => 'product', 'no_ipp' => $valx['no_ipp'], 'no_ipp_code' => $valx['no_ipp_code']))->result_array();
                        $new_number = 0;
                        foreach ($getdetailProduct4 as $key => $value) {
                            $new_number++;
                            $where = array('deleted_date' => NULL, 'category' => 'product');
                            if ($type_product != '0') {
                                $where = array('deleted_date' => NULL, 'category' => 'product', 'code_lv1' => $type_product);
                            }

                            $product4Detail    = $this->db->get_where('new_inventory_4', $where)->result();

                            echo "<tr id='header_" . $val . "_" . $new_number . "'>";
                            echo "<td align='left'>";
                            echo "<select name='Detail[" . $val . "][product_master][" . $new_number . "][code_lv4]' class='chosen-select form-control input-sm'>";
                            echo "<option value='0'>Select Material Name</option>";
                            foreach ($product4Detail as $productlv4) {
                                $selected = ($value['code_lv4'] == $productlv4->code_lv4) ? 'selected' : '';
                                echo "<option value='" . $productlv4->code_lv4 . "' " . $selected . ">" . strtoupper($productlv4->nama) . "</option>";
                            }
                            echo "</select>";
                            echo "</td>";
                            echo "<td align='left'>";
                            echo "<input type='text' name='Detail[" . $val . "][product_master][" . $new_number . "][order]' class='form-control input-md text-center autoNumeric0 qty' placeholder='Order' value='" . $value['order'] . "'>";
                            echo "</td>";
                            echo "<td align='center'>";
                            echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delPartProduct4' title='Delete'><i class='fa fa-close'></i></button>";
                            echo "</td>";
                            echo "</tr>";
                        }


                        echo "		<tr id='addproduct4_" . $val . "_" . $new_number . "'>";
                        echo "			<td><button type='button' class='btn btn-sm btn-warning addPartProduct4' title='Add Product'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Product</button></td>";
                        echo "			<td></td>";
                        echo "			<td></td>";
                        echo "		</tr>";
                        echo "	</table>";
                        echo "	</div>";
                        echo "</div>";
                        echo "<div class='form-group row'>";
                        echo "	<div class='col-md-2'>";
                        echo "		<label>Additional Spesification</label>";
                        echo "	</div>";
                        echo "	<div class='col-md-2'>";
                        echo "		<div class='form-group'><label>Additional</label>";
                        echo "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $val . "][food_grade]' value='Y' " . $food_grade . ">Food Grade</label></div>";
                        echo "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $val . "][uv]' value='Y' " . $uv . ">UV</label></div>";
                        echo "		</div>";
                        echo "	</div>";
                        echo "	<div class='col-md-2'>";
                        echo "		<div class='form-group'><label>Fire Retardant</label>";
                        echo "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $val . "][fire_reterdant_1]' value='Y' " . $fire_reterdant_1 . ">Level 1</label></div>";
                        echo "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $val . "][fire_reterdant_2]' value='Y' " . $fire_reterdant_2 . ">Level 2</label></div>";
                        echo "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $val . "][fire_reterdant_3]' value='Y' " . $fire_reterdant_3 . ">Level 3</label></div>";
                        echo "		</div>";
                        echo "	</div>";
                        echo "	<div class='col-md-2'>";
                        echo "		<div class='form-group'><label>Standard Spec</label>";
                        echo "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $val . "][standard_astm]' value='Y' " . $standard_astm . ">ASTM</label></div>";
                        echo "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $val . "][standard_bs]' value='Y' " . $standard_bs . ">BS</label></div>";
                        echo "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $val . "][standard_dnv]' value='Y' " . $standard_dnv . ">GNV-GL</label></div>";
                        echo "		</div>";
                        echo "	</div>";
                        echo "	<div class='col-md-4'>";
                        echo "		<div class='form-group'><label>Dokumen Pendukung</label>";
                        echo "		<input type='text' class='form-control' name='Detail[" . $val . "][file_pendukung_1]' placeholder='Dokumen Pendukung 1' style='margin-bottom:5px;' value='" . $file_pendukung_1 . "'>";
                        echo "		<input type='text' class='form-control' name='Detail[" . $val . "][file_pendukung_2]' placeholder='Dokumen Pendukung 2' style='margin-bottom:5px;' value='" . $file_pendukung_2 . "'>";
                        echo "		</div>";
                        echo "	</div>";
                        echo "</div>";
                        echo "<div class='form-group row'>";
                        echo "	<div class='col-md-2'>";
                        echo "		<label></label>";
                        echo "	</div>";
                        echo "	<div class='col-md-2'>";
                        echo "		<div class='form-group'><label>Color</label>";
                        echo "		<input type='text' class='form-control' name='Detail[" . $val . "][color]' placeholder='Color' value='" . $color . "'>";
                        echo "		</div>";
                        echo "	</div>";
                        echo "	<div class='col-md-4'>";
                        echo "		<div class='form-group'><label>Other Testing Requirement</label>";
                        echo "		<textarea class='form-control' name='Detail[" . $val . "][other_test]' rows='2' placeholder='Other Testing Requirement'>" . $other_test . "</textarea>";
                        echo "		</div>";
                        echo "	</div>";
                        echo "</div>";
                        echo "<div class='form-group row'>";
                        echo "	<div class='col-md-2'>";
                        echo "		<label>Surface</label>";
                        echo "	</div>";
                        echo "	<div class='col-md-2'>";
                        echo "		<div class='form-group'>";
                        echo "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $val . "][surface_concave]' value='Y' " . $surface_concave . ">Concave</label></div>";
                        echo "		<div class='checkbox'><label><input type='checkbox' name='Detail[" . $val . "][surface_flat]' value='Y' " . $surface_flat . ">Flat</label></div>";
                        echo "		</div>";
                        echo "	</div>";
                        echo "</div>";
                        echo "<div class='form-group row'>";
                        echo "	<div class='col-md-2'>";
                        echo "		<label>Topping</label>";
                        echo "	</div>";
                        echo "	<div class='col-md-4'>";
                        echo "	<input type='text' name='Detail[" . $val . "][id_bom_topping]' class='form-control'>";

                        echo "	</div>";
                        echo "</div>";

                        echo "<div class='form-group row'>";
                        echo "	<div class='col-md-2'>";
                        echo "		<label>Ukuran Jadi</label>";
                        echo "	</div>";
                        echo "	<div class='col-md-5'>";
                        echo "	<table class='table table-striped table-bordered table-hover table-condensed'>";
                        echo "		<tr class='bg-blue'>";
                        echo "			<th class='text-center' width='30%'>Length</th>";
                        echo "			<th class='text-center' width='30%'>Width</th>";
                        echo "			<th class='text-center' width='30%'>Qty</th>";
                        echo "			<th class='text-center' width='10%'>#</th>";
                        echo "		</tr>";

                        $getdetailProduct4 = $this->db->get_where('ipp_detail_lainnya', array('category' => 'ukuran jadi', 'no_ipp' => $valx['no_ipp'], 'no_ipp_code' => $valx['no_ipp_code']))->result_array();
                        $new_number = 0;
                        foreach ($getdetailProduct4 as $key => $value) {
                            $new_number++;

                            echo "<tr id='headerjadi_" . $val . "_" . $new_number . "'>";
                            echo "<td align='left'>";
                            echo "<input type='text' name='Detail[" . $val . "][ukuran_jadi][" . $new_number . "][length]' class='form-control input-md text-center autoNumeric4' value='" . $value['length'] . "'>";
                            echo "</td>";
                            echo "<td align='left'>";
                            echo "<input type='text' name='Detail[" . $val . "][ukuran_jadi][" . $new_number . "][width]' class='form-control input-md text-center autoNumeric4' value='" . $value['width'] . "'>";
                            echo "</td>";
                            echo "<td align='left'>";
                            echo "<input type='text' name='Detail[" . $val . "][ukuran_jadi][" . $new_number . "][order]' class='form-control input-md text-center autoNumeric0' value='" . $value['order'] . "'>";
                            echo "</td>";
                            echo "<td align='center'>";
                            echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delPartUkj' title='Delete'><i class='fa fa-close'></i></button>";
                            echo "</td>";
                            echo "</tr>";
                        }
                        echo "		<tr id='addjadi_" . $val . "_" . $new_number . "'>";
                        echo "			<td><button type='button' class='btn btn-sm btn-warning addPartUkj' title='Add Ukuran Jadi'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Ukuran Jadi</button></td>";
                        echo "			<td></td>";
                        echo "			<td></td>";
                        echo "		</tr>";
                        echo "	</table>";
                        echo "	</div>";
                        echo "</div>";



                        echo "<div class='form-group row'>";
                        echo "	<div class='col-md-2'>";
                        echo "		<label>Drawing Customer</label>";
                        echo "	</div>";
                        echo "	<div class='col-md-5'><input type='file' name='photo_" . $val . "' id='photo_" . $val . "' >";
                        if (!empty($file_dokumen)) {
                            echo "<a href='" . base_url() . $file_dokumen . "' target='_blank' class='help-block' title='Download'>Download File</a>";
                        }
                        echo "	</div>";
                        echo "</div>";




                        //penutup div delete
                        echo "</div>";
                    }
                }
                ?>
                <div id='add_<?= $val ?>'><button type='button' class='btn btn-sm btn-primary addPart' title='Add Permintaan'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Permintaan</button></td>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style='float:right;' name="save" id="save"><i class="fa fa-save"></i> Save</button>

        </form>
    </div>
</div>

<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<style media="screen">
    .datepicker {
        cursor: pointer;
        padding-left: 12px;
    }
</style>
<script type="text/javascript">
    //$('#input-kendaraan').hide();
    var base_url = '<?php echo base_url(); ?>';
    var active_controller = '<?php echo ($this->uri->segment(1)); ?>';

    $(document).ready(function() {
        $('.chosen-select').select2({
            width: '100%'
        });
        $(".datepicker").datepicker();
        $(".autoNumeric4").autoNumeric('init', {
            mDec: '4',
            aPad: false
        });
        $(".autoNumeric0").autoNumeric('init', {
            mDec: '0',
            aPad: false
        });

        //add part
        $(document).on('click', '.addPart', function() {
            // loading_spinner();
            var get_id = $(this).parent().attr('id');
            // console.log(get_id);
            var split_id = get_id.split('_');
            var id = parseInt(split_id[1]) + 1;
            var id_bef = split_id[1];

            $.ajax({
                url: base_url + active_controller + '/get_add/' + id,
                cache: false,
                type: "POST",
                dataType: "json",
                success: function(data) {
                    $("#add_" + id_bef).before(data.header);
                    $("#add_" + id_bef).remove();
                    $('.chosen-select').select2({
                        width: '100%'
                    });
                    $('.autoNumeric4').autoNumeric('init', {
                        mDec: '4',
                        aPad: false
                    });
                    $(".autoNumeric0").autoNumeric('init', {
                        mDec: '0',
                        aPad: false
                    });
                    swal.close();
                },
                error: function() {
                    swal({
                        title: "Error Message !",
                        text: 'Connection Time Out. Please try again..',
                        type: "warning",
                        timer: 3000
                    });
                }
            });
        });



        $(document).on('click', '.delPart', function() {
            var get_id = $(this).data('id');
            $("#header_" + get_id).remove();
        });

        //add product level 4
        $(document).on('click', '.addPartProduct4', function() {
            // loading_spinner();
            var get_id = $(this).parent().parent().attr('id');
            // console.log(get_id);
            var split_id = get_id.split('_');
            var id_head = split_id[1];

            var id = parseInt(split_id[2]) + 1;
            var id_bef = split_id[2];

            var type_product = $('#type_product_' + id_head).val()

            $.ajax({
                url: base_url + active_controller + '/get_add_product_lv4/' + id_head + '/' + id,
                cache: false,
                type: "POST",
                data: {
                    'type_product': type_product
                },
                dataType: "json",
                success: function(data) {
                    $("#addproduct4_" + id_head + "_" + id_bef).before(data.header);
                    $("#addproduct4_" + id_head + "_" + id_bef).remove();
                    $('.chosen-select').select2({
                        width: '100%'
                    });
                    $('.autoNumeric4').autoNumeric('init', {
                        mDec: '4',
                        aPad: false
                    });
                    $(".autoNumeric0").autoNumeric('init', {
                        mDec: '0',
                        aPad: false
                    });
                    swal.close();
                },
                error: function() {
                    swal({
                        title: "Error Message !",
                        text: 'Connection Time Out. Please try again..',
                        type: "warning",
                        timer: 3000
                    });
                }
            });
        });

        $(document).on('click', '.delPartProduct4', function() {
            var get_id = $(this).parent().parent().attr('id');
            // console.log(get_id);
            var split_id = get_id.split('_');
            var id_head = split_id[1];
            var id_child = split_id[2];
            $("#header_" + id_head + "_" + id_child).remove();
        });

        //add accessories
        $(document).on('click', '.addPartAcc', function() {
            // loading_spinner();
            var get_id = $(this).parent().parent().attr('id');
            // console.log(get_id);
            var split_id = get_id.split('_');
            var id_head = split_id[1];

            var id = parseInt(split_id[2]) + 1;
            var id_bef = split_id[2];

            $.ajax({
                url: base_url + active_controller + '/get_add_accessories/' + id_head + '/' + id,
                cache: false,
                type: "POST",
                data: {
                    'type_product': '5'
                },
                dataType: "json",
                success: function(data) {
                    $("#addacc_" + id_head + "_" + id_bef).before(data.header);
                    $("#addacc_" + id_head + "_" + id_bef).remove();
                    $('.chosen-select').select2({
                        width: '100%'
                    });
                    $('.autoNumeric4').autoNumeric('init', {
                        mDec: '4',
                        aPad: false
                    });
                    $(".autoNumeric0").autoNumeric('init', {
                        mDec: '0',
                        aPad: false
                    });
                    swal.close();
                },
                error: function() {
                    swal({
                        title: "Error Message !",
                        text: 'Connection Time Out. Please try again..',
                        type: "warning",
                        timer: 3000
                    });
                }
            });
        });

        $(document).on('click', '.delPartAcc', function() {
            var get_id = $(this).parent().parent().attr('id');
            // console.log(get_id);
            var split_id = get_id.split('_');
            var id_head = split_id[1];
            var id_child = split_id[2];
            $("#headeracc_" + id_head + "_" + id_child).remove();
        });

        //ukuran jadi
        $(document).on('click', '.addPartUkj', function() {
            // loading_spinner();
            var get_id = $(this).parent().parent().attr('id');
            // console.log(get_id);
            var split_id = get_id.split('_');
            var id_head = split_id[1];

            var id = parseInt(split_id[2]) + 1;
            var id_bef = split_id[2];

            $.ajax({
                url: base_url + active_controller + '/get_add_ukuran/' + id_head + '/' + id,
                cache: false,
                type: "POST",
                data: {
                    'NameSave': 'ukuran_jadi',
                    'LabelAdd': 'Ukuran Jadi',
                    'LabelClass': 'Ukj',
                    'idClass': 'jadi',
                },
                dataType: "json",
                success: function(data) {
                    $("#addjadi_" + id_head + "_" + id_bef).before(data.header);
                    $("#addjadi_" + id_head + "_" + id_bef).remove();
                    $('.chosen-select').select2({
                        width: '100%'
                    });
                    $('.autoNumeric4').autoNumeric('init', {
                        mDec: '4',
                        aPad: false
                    });
                    $(".autoNumeric0").autoNumeric('init', {
                        mDec: '0',
                        aPad: false
                    });
                    swal.close();
                },
                error: function() {
                    swal({
                        title: "Error Message !",
                        text: 'Connection Time Out. Please try again..',
                        type: "warning",
                        timer: 3000
                    });
                }
            });
        });

        $(document).on('click', '.delPartUkj', function() {
            var get_id = $(this).parent().parent().attr('id');
            // console.log(get_id);
            var split_id = get_id.split('_');
            var id_head = split_id[1];
            var id_child = split_id[2];
            $("#headerjadi_" + id_head + "_" + id_child).remove();
        });

        //ukuran jadi
        $(document).on('click', '.addPartSheet', function() {
            // loading_spinner();
            var get_id = $(this).parent().parent().attr('id');
            // console.log(get_id);
            var split_id = get_id.split('_');
            var id_head = split_id[1];

            var id = parseInt(split_id[2]) + 1;
            var id_bef = split_id[2];

            $.ajax({
                url: base_url + active_controller + '/get_add_ukuran/' + id_head + '/' + id,
                cache: false,
                type: "POST",
                data: {
                    'NameSave': 'flat_sheet',
                    'LabelAdd': 'Flat Sheet',
                    'LabelClass': 'Sheet',
                    'idClass': 'sheet',
                },
                dataType: "json",
                success: function(data) {
                    $("#addsheet_" + id_head + "_" + id_bef).before(data.header);
                    $("#addsheet_" + id_head + "_" + id_bef).remove();
                    $('.chosen-select').select2({
                        width: '100%'
                    });
                    $('.autoNumeric4').autoNumeric('init', {
                        mDec: '4',
                        aPad: false
                    });
                    $(".autoNumeric0").autoNumeric('init', {
                        mDec: '0',
                        aPad: false
                    });
                    swal.close();
                },
                error: function() {
                    swal({
                        title: "Error Message !",
                        text: 'Connection Time Out. Please try again..',
                        type: "warning",
                        timer: 3000
                    });
                }
            });
        });

        $(document).on('click', '.delPartSheet', function() {
            var get_id = $(this).parent().parent().attr('id');
            // console.log(get_id);
            var split_id = get_id.split('_');
            var id_head = split_id[1];
            var id_child = split_id[2];
            $("#headersheet_" + id_head + "_" + id_child).remove();
        });

        //ukuran jadi
        $(document).on('click', '.addPartEnd', function() {
            // loading_spinner();
            var get_id = $(this).parent().parent().attr('id');
            // console.log(get_id);
            var split_id = get_id.split('_');
            var id_head = split_id[1];

            var id = parseInt(split_id[2]) + 1;
            var id_bef = split_id[2];

            $.ajax({
                url: base_url + active_controller + '/get_add_ukuran/' + id_head + '/' + id,
                cache: false,
                type: "POST",
                data: {
                    'NameSave': 'end_plate',
                    'LabelAdd': 'End/Kick Plate',
                    'LabelClass': 'End',
                    'idClass': 'end',
                },
                dataType: "json",
                success: function(data) {
                    $("#addend_" + id_head + "_" + id_bef).before(data.header);
                    $("#addend_" + id_head + "_" + id_bef).remove();
                    $('.chosen-select').select2({
                        width: '100%'
                    });
                    $('.autoNumeric4').autoNumeric('init', {
                        mDec: '4',
                        aPad: false
                    });
                    $(".autoNumeric0").autoNumeric('init', {
                        mDec: '0',
                        aPad: false
                    });
                    swal.close();
                },
                error: function() {
                    swal({
                        title: "Error Message !",
                        text: 'Connection Time Out. Please try again..',
                        type: "warning",
                        timer: 3000
                    });
                }
            });
        });

        $(document).on('click', '.delPartEnd', function() {
            var get_id = $(this).parent().parent().attr('id');
            // console.log(get_id);
            var split_id = get_id.split('_');
            var id_head = split_id[1];
            var id_child = split_id[2];
            $("#headerend_" + id_head + "_" + id_child).remove();
        });


        //add part
        $(document).on('click', '#back', function() {
            window.location.href = base_url + active_controller;
        });

        $('#save').click(function(e) {
            e.preventDefault();
            var id_customer = $('#id_customer').val();
            var project = $('#project').val();

            if (id_customer == '0') {
                swal({
                    title: "Error Message!",
                    text: 'Customer name empty, select first ...',
                    type: "warning"
                });
                return false;
            }
            if (project == '') {
                swal({
                    title: "Error Message!",
                    text: 'Project name empty, select first ...',
                    type: "warning"
                });
                return false;
            }

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
                        var baseurl = base_url + active_controller + '/add'
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
                                        timer: 3000,
                                        showCancelButton: false,
                                        showConfirmButton: false,
                                        allowOutsideClick: false
                                    });
                                    $('#sales_product_price_list_modal').modal('hide');
                                } else {

                                    if (data.status == 2) {
                                        swal({
                                            title: "Save Failed!",
                                            text: data.pesan,
                                            type: "warning",
                                            timer: 3000,
                                            showCancelButton: false,
                                            showConfirmButton: false,
                                            allowOutsideClick: false
                                        });
                                        $('#sales_product_price_list_modal').modal('hide');
                                    } else {
                                        swal({
                                            title: "Save Failed!",
                                            text: data.pesan,
                                            type: "warning",
                                            timer: 3000,
                                            showCancelButton: false,
                                            showConfirmButton: false,
                                            allowOutsideClick: false
                                        });
                                        $('#sales_product_price_list_modal').modal('hide');
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
</script>