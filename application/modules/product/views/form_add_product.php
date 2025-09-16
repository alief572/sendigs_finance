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
                            <input type="text" class="form-control" required name="nm_product" placeholder="Product Name" value="">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-3">
                            <label for="">Surface Condition</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="surface" placeholder="Surface Condition" value="">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-3">
                            <label for="">Category</label>
                        </div>
                        <div class="col-md-6">
                            <select id="category" name="category" class="form-control select2">
                            </select>
                        </div>
                    </div>
					<div class="form-group row">
                        <div class="col-md-3">
                            <label for="">Sub Category</label>
                        </div>
                        <div class="col-md-6" id="dt_subCat">
                            <select id="sub_category" name="sub_category" class="form-control select2" disabled>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="country" class="col-sm-3 control-label">Form</label>
                        <div class="col-md-4">
                            <select id="form" name="form" class="form-control select2" required >
                                <option value="">-- Form --</option>
                                <option value="Coil">Coil</option>
                                <option value="Sheet">Sheet</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="currency" class="col-sm-3 control-label">Qty</label>
                        <div class="col-md-4">
                            <input type="text" class="form-control text-right" autocomplete="off" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" name="uCoil" placeholder="0" value="">
                        </div>
                    </div>
					<div class="form-group row">
                        <div class="col-md-3">
                            <label for="">Status</label>
                        </div>
                        <div class="col-md-4">
                            <label>
                                <input type="radio" class="radio-control" name="status" value="aktif" required> Aktif
                            </label>
                            &nbsp &nbsp &nbsp
                            <label>
                                <input type="radio" class="radio-control" name="status" value="nonaktif" required> Non Aktif
                            </label>
                        </div>
                    </div>
                    
                </div>
				
				<!-- ==============================================================-->
                <div class="col-md-6">
                    <div class="form-group row">
                        <div class="col-md-3">
                            <label for="">Hardnes</label>
                        </div>
                        <div class="col-md-5">
                            <div class="input-group">
                                <select id="hardnes" name="hardnes" class="form-control select2" required>
                                    <option value="">...</option>
                                    <option value="1/4">1/4</option>
                                    <option value="1/3">1/3</option>
                                    <option value="1/2">1/2</option>
                                    <option value="1/2">1</option>
                                    <option value="1/2">2</option>
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
                            <input type="text" class="form-control text-right" id="thicknes" name="thicknes" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/\B(?=(?:\d{3})+(?!\d))/g,',');" placeholder="0.0" value="">
                        </div>

                    </div>

                    <div class="form-group row">
                        <div class="col-md-3">
                            <label>Width</label>
                        </div>
                        <div class="col-md-5">
                            <div class="input-group">
                                <input type="text" class="form-control text-right" id="width" name="width" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/\B(?=(?:\d{3})+(?!\d))/g,',');" required placeholder="0" value="">
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
                                <input type="text" class="form-control text-right" id="weight" name="weight" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/\B(?=(?:\d{3})+(?!\d))/g,',');" required placeholder="0" value="">
                                <div class="input-group-addon">Kg</div>
                            </div>
                        </div>
                    </div>
					<div class="form-group row">
                        <div class="col-md-3">
                            <label>Density</label>
                        </div>
                        <div class="col-md-5">
                            <div class="input-group">
                                <input type="text" class="form-control text-right" id="density" name="density" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/\B(?=(?:\d{3})+(?!\d))/g,',');" placeholder="0" value="">
                                <div class="input-group-addon">g/m<sup>3</sup></div>
                            </div>
                        </div>
                    </div>
					<div class="form-group row">
                        <div class="col-md-3">
                            <label>Length</label>
                        </div>
                        <div class="col-md-5">
                            <div class="input-group">
                                <input type="text" class="form-control text-right" readonly id="length" name="length" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/\B(?=(?:\d{3})+(?!\d))/g,',');" placeholder="0" value="">
                                <div class="input-group-addon">mm</div>
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
                                <input type="text" class="form-control text-right" name="price" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/\B(?=(?:\d{3})+(?!\d))/g,',');" placeholder="0" value="">
                            </div>
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
		getCat();
		
		$(document).on('change keyup paste','#density',function(){
			var density = $(this).val().replace(/,/g,'');
				width = $('#width').val().replace(/,/g,'');
				thic  = $('#thicknes').val().replace(/,/g,'');
				weight= $('#weight').val().replace(/,/g,'');
				
			length = (width * density * thic) / weight ;
			$('#length').val(length.toFixed(2));
		})
		
		
		$(document).on('change','#category',function(){
			var id_cat = $(this).val();
			// alert(id_cat);
			getSubcat(id_cat);
			$('#sub_category').prop('disabled', false);
			
		})

        $('#form, #category, #sub_category').select2({
			placeholder: "Choose An Option",
			allowClear: true,
			dropdownParent: $("#data_form")
		
		});

        $(document).on('change', '#formm', function() {
            var change = ($(this).val());
            if (change == 'Sheet') {
                $('#length').attr({
                    'readonly': false,
                    'required': true
                });
            } else {
                $('#length').attr({
                    'readonly': true,
                    'required': false
                });
            }
        })

        //SAVE BARANG
        $(document).on('submit', '#data_form', function(e) {
            e.preventDefault();
            // $('#harga').val($('#harga').val().replace(/[^\d]/g,""));
            var data = $("#data_form").serialize();
            // alert(data);
            $.ajax({
                url: siteurl + "product/saveProduct",
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
		
		
		function getSubcat(id_cat) {
			// if ('<?=($getC->id_type)?>' != null) {
			  // var id_selected = '<?=$getC->id_type?>';
			// }else if ($('#id_type').val() != null || $('#id_type').val() != '') {
			  // var id_selected = $('#id_type').val();
			// }else {
			  // var id_selected = '';
			// }
			
			var column = 'id_kategori';
			var column_fill = id_cat;
			var column_name = 'nm_sub_kategori';
			var table_name = 'sub_kategori';
			var key = 'id_sub_kategori';
			var act = '';
			$.ajax({
			  url: siteurl+active_controller+"getOpt",
			  dataType : "json",
			  type: 'POST',
			  data: {
				// id_selected:id_selected,
				column:column,
				column_fill:column_fill,
				column_name:column_name,
				table_name:table_name,
				key:key,
				act:act
			  },
			  success: function(result){
				$('#sub_category').html(result.html);
			  },
			  error: function (request, error) {
				console.log(arguments);
				alert(" Can't do because: " + error);
			  }
			});
		  }
		  
		  function getCat() {
			// if ('<?=($getC->id_type)?>' != null) {
			  // var id_selected = '<?=$getC->id_type?>';
			// }else if ($('#id_type').val() != null || $('#id_type').val() != '') {
			  // var id_selected = $('#id_type').val();
			// }else {
			  // var id_selected = '';
			// }
			
			var column = 'id_kategori';
			var column_fill = '';
			var column_name = 'nm_kategori';
			var table_name = 'kategori';
			var key = 'id_kategori';
			var act = 'free';
			$.ajax({
			  url: siteurl+active_controller+"getOpt",
			  dataType : "json",
			  type: 'POST',
			  data: {
				// id_selected:id_selected,
				column:column,
				column_fill:column_fill,
				column_name:column_name,
				table_name:table_name,
				key:key,
				act:act
			  },
			  success: function(result){
				$('#category').html(result.html);
				console.log(result);
			  },
			  error: function (request, error) {
				console.log(arguments);
				alert(" Can't do because: " + error);
			  }
			});
		  }
    });
</script>