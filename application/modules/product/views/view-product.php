<div class="row">
    <!-- <div class="col-md-6">

    </div> -->

    <div class="col-md-12">
        <h3><?= $result['product_name'] ?></h3>
        <table class="table table-condensed">
            <tbody>
                <tr>
                    <td width="25%"><strong> Product ID</strong></td>
                    <td width="4">:</td>
                    <td><?= $result['id_product'] ?></td>
                </tr>
                <tr>
                    <td><strong> Hardnes ID</strong></td>
                    <td>:</td>
                    <td><?= $result['hardnes'] ?></td>
                </tr>
                <tr>
                    <td><strong> Surface Condition</strong></td>
                    <td>:</td>
                    <td><?= $result['surface'] ?></td>
                </tr>
                <tr>
                    <td><strong> Form</strong></td>
                    <td>:</td>
                    <td><?= $result['form'] ?></td>
                </tr>
            </tbody>
        </table>
        <table class="table-condensed table-bordered table-default" width="100%">
            <thead>
                <tr class="alert-success">
                    <th>Thicknes</th>
                    <th>Width</th>
                    <th>Weight</th>
                    <th>Length</th>
                    <th>Unit Coil</th>
                </tr>
            </thead>
            <tbody>
                <tr class="text-right">
                    <td scope="row"><?= $result['thicknes'] ?></td>
                    <td><?= $result['width'] ?></td>
                    <td><?= $result['weight'] ?></td>
                    <td><?= $result['length'] ?></td>
                    <td><?= $result['unit_coil'] ?></td>
                </tr>
            </tbody>
        </table>
        <br>
        <div class=""><strong> Price($)</strong></div>
        <h2 style="line-height:10px"><b> $ <?= number_format($result['price']) ?></b></h2>
        <div class="">
            <h2></h2>
        </div>
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