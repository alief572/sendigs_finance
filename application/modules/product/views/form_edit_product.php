<div class="box box-primary">
    <div class="box-header">
        <a href="<?= base_url('product') ?>" class="btn btn-warning pull-right"><i class="fa fa-reply"></i> Back</a>
    </div>
    <div class="box-body">
        <form id="data_form">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group row">
                        <div class="col-md-3">
                            <label for="">Product Name</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" id="" required name="nm_product" placeholder="Product Name" value="<?= $result['product']['product_name'] ?>">
                            <input type="hidden" class="form-control" id="id_product" name="id_product" placeholder="Product Name" value="<?= $result['product']['id_product'] ?>">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-3">
                            <label for="">Surface Condition</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="surface" placeholder="Surface Condition" value="<?= $result['product']['surface'] ?>">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-3">
                            <label for="">Categori</label>
                        </div>
                        <div class="col-md-6">
                            <select id="category" name="category" class="form-control select2">
                                <option value="">...</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="country" class="col-sm-3 control-label">Form</label>
                        <div class="col-md-4">
                            <select id="form" name="form" class="form-control select2" required>
                                <?php if ($result['product']['form'] == 'Coil') { ?>
                                    <option value="">-- Form --</option>
                                    <option value="Coil" selected>Coil</option>
                                    <option value="Sheet">Sheet</option>
                                <?php } else if ($result['product']['form'] == 'Sheet') { ?>
                                    <option value="">-- Form --</option>
                                    <option value="Coil">Coil</option>
                                    <option value="Sheet" selected>Sheet</option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="currency" class="col-sm-3 control-label">Unit Coil</label>
                        <div class="col-md-4">
                            <input type="text" class="form-control text-right" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" name="uCoil" placeholder="0" value="<?= number_format($result['product']['unit_coil'],2,".",",") ?>">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-3">
                            <label>Length</label>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" class="form-control" id="length" name="length" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" placeholder="0" value="<?= number_format($result['product']['length'],2,".",",") ?>">
                                <div class="input-group-addon">mm</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group row">
                        <div class="col-md-3">
                            <label for="">Hardnes</label>
                        </div>
                        <div class="col-md-5">
                            <div class="input-group">
                                <select id="hardnes" name="hardnes" class="form-control select2" required>
                                    <option value="">...</option>
                                    <?php foreach ($result['hardnes'] as $h) :
                                        $select = $h['name'] == $result['product']['hardnes'] ? "selected" : "";
                                    ?>
                                        <option value="<?= $h['name'] ?>" <?= $select ?>><?= $h['name'] ?></option>
                                    <?php endforeach ?>
                                </select>
                                <div class="input-group-addon">/H</div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-3">
                            <label for="">Thicknes</label>
                        </div>
                        <div class="col-md-5">
                            <input type="text" class="form-control text-right" name="thicknes" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" placeholder="0.0" value="<?= $result['product']['thicknes'] ?>">
                        </div>

                    </div>

                    <div class="form-group row">
                        <div class="col-md-3">
                            <label>Width</label>
                        </div>
                        <div class="col-md-5">
                            <div class="input-group">
                                <input type="text" class="form-control text-right" name="width" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" required placeholder="0" value="<?= number_format($result['product']['width'],2,".",",") ?>">
                                <div class="input-group-addon">mm</div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-3">
                            <label for="">Weight</label>
                        </div>
                        <div class="col-md-5">
                            <div class="input-group">
                                <input type="text" class="form-control text-right" name="weight" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" required placeholder="0" value="<?= number_format($result['product']['weight'],2,".",",") ?>">
                                <div class="input-group-addon">Kg</div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-3">
                            <label for="">Price($)</label>
                        </div>
                        <div class="col-md-5">
                            <div class="input-group">
                                <div class="input-group-addon">$</div>
                                <input type="text" class="form-control text-right" id="" name="price" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" placeholder="0" value="<?= number_format($result['product']['price']) ?>">
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-3">
                            <label for="">Status</label>
                        </div>
                        <div class="col-md-4">
                            <?php if ($result['product']['status'] == 'aktif') : ?>
                                <label>
                                    <input type="radio" class="radio-control" id="" name="status" checked value="aktif" required> Aktif
                                </label>
                                &nbsp &nbsp &nbsp
                                <label>
                                    <input type="radio" class="radio-control" id="" name="status" value="nonaktif" required> Non Aktif
                                </label>
                            <?php else : ?>
                                <label>
                                    <input type="radio" class="radio-control" id="" name="status" value="aktif" required> Aktif
                                </label>
                                &nbsp &nbsp &nbsp
                                <label>
                                    <input type="radio" class="radio-control" id="" name="status" checked value="nonaktif" required> Non Aktif
                                </label>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <button type="submit" class="btn btn-primary" id="save"><i class="fa fa-save"></i> Save</button>
            <button type="reset" class="btn btn-danger" id="cencel"><i class="fa fa-close"></i> Cencel</button>
        </form>
    </div>
</div>


<script type="text/javascript">
    $(document).ready(function() {
        _change();
        $('#form, #category').select2();

        function _change() {
            var change = ($('#form').val());
            if (change == 'Sheet') {
                $('#length').attr({
                    'readonly': false,
                    'required': true

                });
            } else {
                $('#length').attr({
                    'readonly': true,
                    'required': false,
                    'value': 0
                });
            }

        }

        $(document).on('change', '#form', function() {
            _change();
        })

        //SAVE BARANG
        $(document).on('submit', '#data_form', function(e) {
            e.preventDefault();
            var id = $('#id_product').val();
            var data = $("#data_form").serialize();
            // alert(data);
            $.ajax({
                url: siteurl + "product/saveEditProduct/" + id,
                dataType: "json",
                type: 'POST',
                data: data,
                //alert(msg);
                success: function(result) {
                    if (result.status == '1') {
                        swal({
                                title: "Sukses!",
                                text: "Data Produk berhasil disimpan.",
                                type: "success",
                                timer: 1500,
                                showConfirmButton: false
                            },
                            function() {
                                window.location.reload(true);
                            })
                    } else {
                        swal({
                            title: "Error",
                            text: "Data error. Gagal insert data",
                            type: "error",
                            timer: 1500,
                            showConfirmButton: false
                        })
                    }
                },
                error: function() {
                    swal({
                        title: "Gagal!",
                        text: "Data Ajax Gagal Di Proses",
                        type: "error",
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        });
    });
</script>