<?php

$id             = (!empty($header))?$header[0]->id:'';
$id_stock       = (!empty($header))?$header[0]->id_stock:'';
$id_category    = (!empty($header))?$header[0]->id_category:'';
$stock_name     = (!empty($header))?$header[0]->stock_name:'';
$trade_name     = (!empty($header))?$header[0]->trade_name:'';
$brand          = (!empty($header))?$header[0]->brand:'';
$spec           = (!empty($header))?$header[0]->spec:'';
$id_unit_gudang = (!empty($header))?$header[0]->id_unit_gudang:'';
$konversi       = (!empty($header))?$header[0]->konversi:'';
$id_unit        = (!empty($header))?$header[0]->id_unit:'';
$min_stok        = (!empty($header))?$header[0]->min_stok:'';
$max_stok        = (!empty($header))?$header[0]->max_stok:'';
if(!empty($id)){
$status1         = ($header[0]->status == '1')?'checked':'';
$status2         = ($header[0]->status == '0')?'checked':'';
}

// print_r($header);
?>

 <div class="box box-primary">
    <div class="box-body"><br>
		<form id="data-form" method="post" autocomplete="off">
		<div class="form-group row">
			<div class="col-md-2">
					<label>Category Stok <span class="text-red">*</span></label>
			</div>
			<div class="col-md-10">
				<select id="id_category" name="id_category" class="form-control input-md chosen-select" required>
					<option value="0">Select An Option</option>
					<?php foreach ($results['category'] as $kel){
						$sel = ($kel->id == $id_category)?'selected':'';
					?>
					<option value="<?= $kel->id;?>" <?=$sel;?>><?= strtoupper(strtolower($kel->nm_category))?></option>
					<?php } ?>
				</select>
			</div>
		</div>
      	<div class="form-group row">
			<div class="col-md-2">
				<label>Stok Name <span class="text-red">*</span></label>
			</div>
			<div class="col-md-10">
				<input type="hidden" id="id" name="id" class="form-control input-md" readonly  value='<?=$id;?>'>
				<input type="text" id="stock_name" name="stock_name" class="form-control input-md" required placeholder="Stok Name" value='<?=$stock_name;?>'>
			</div>
		</div>
		<hr>
		<div class="form-group row">
			<div class="col-md-2">
					<label>Item Code</label>
				</div>
				<div class="col-md-4">
          <input type="text" id="id_stock" name="id_stock" class="form-control input-md" placeholder="Item Code" value='<?=$id_stock;?>'>
				</div>
        
        <div class="col-md-2">
          <label>Trade Name</label>
        </div>
        <div class="col-md-4">
          <input type="text" id="trade_name" name="trade_name" class="form-control input-md" placeholder="Trade Name" value='<?=$trade_name;?>'>
        </div>
      </div>
      <div class="form-group row">
        <div class="col-md-2">
          <label>Brand</label>
        </div>
        <div class="col-md-4">
          <input type="text" id="brand" name="brand" class="form-control input-md" placeholder="Brand" value='<?=$brand;?>'>
        </div>
        <div class="col-md-2">
          <label>Spesification</label>
        </div>
        <div class="col-md-4">
          <input type="text" id="spec" name="spec" class="form-control input-md" placeholder="Spesification" value='<?=$spec;?>'>
        </div>
      </div>
      <div class="form-group row">
        <div class="col-md-2">
					<label>Packing Unit/ Konversi</label>
				</div>
				<div class="col-md-2">
					<select id="id_unit_gudang" name="id_unit_gudang" class="form-control input-md chosen-select">
						<option value="0">Select An Option</option>
            <?php foreach ($results['satuan_packing'] as $satuan){
              $sel = ($satuan->id == $id_unit_gudang)?'selected':'';
            ?>
            <option value="<?= $satuan->id;?>" <?=$sel;?>><?= strtoupper(strtolower($satuan->code))?></option>
						<?php } ?>
					</select>
				</div>
				<div class="col-md-2">
          <input type="text" id="konversi" name="konversi" class="form-control input-md autoNumeric" placeholder="Konversi" value='<?=$konversi;?>'>
        </div>
        <div class="col-md-2">
          <label>Unit Measurement</label>
        </div>
        
        <div class="col-md-2">
          <select id="id_unit" name="id_unit" class="form-control input-md chosen-select">
            <option value="0">Select An Option</option>
            <?php foreach ($results['satuan'] as $satuan){
              $sel = ($satuan->id == $id_unit)?'selected':'';
            ?>
            <option value="<?= $satuan->id;?>" <?=$sel;?>><?= strtoupper(strtolower($satuan->code))?></option>
            <?php } ?>
          </select>
        </div>
      </div>
	  <div class="form-group row">
        <div class="col-md-2">
          <label>Maksimum Stok</label>
        </div>
        <div class="col-md-4">
          <input type="text" id="max_stok" name="max_stok" class="form-control input-md autoNumeric" placeholder="Maksimum Stok" value='<?=$max_stok;?>'>
        </div>
        <div class="col-md-2">
          <label>Minimum Stok</label>
        </div>
        <div class="col-md-4">
          <input type="text" id="min_stok" name="min_stok" class="form-control input-md autoNumeric" placeholder="Minimum Stok" value='<?=$min_stok;?>'>
        </div>
      </div>
      <?php if(!empty($id)){ ?>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="">Status</label>
				</div>
				<div class="col-md-4">
					<label>
					<input type="radio" class="radio-control" name="status" value="1" <?=$status1;?>> Aktif
					</label>
					&nbsp &nbsp &nbsp
					<label>
					<input type="radio" class="radio-control" name="status" value="0" <?=$status2;?>> Non-Aktif
					</label>
				</div>
			</div>
			<?php } ?>
      <button type="button" class="btn btn-danger" style='float:right; margin-left:5px;' name="back" id="back"><i class="fa fa-reply"></i> Back</button>
        <?php if(empty($results['tanda'])){ ?>
        <button type="submit" class="btn btn-primary" style='float:right;' name="save" id="save"><i class="fa fa-save"></i> Save</button>
        <?php } ?>
		</form>
	</div>
</div>

<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>
<style media="screen">
  .datepicker{
    cursor: pointer;
    padding-left: 12px;
  }
</style>
<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';

	$(document).ready(function(){
		$('.chosen-select').select2();
    $('.autoNumeric').autoNumeric();

    $(document).on('click', '#back', function(){
		    window.location.href = base_url + active_controller;
		});

		$(document).on('click','#save', function(e){
			e.preventDefault();

			var id_category		  = $('#id_category').val();
      var stock_name	    = $('#stock_name').val();

			if(id_category == '0' ){
				swal({title	: "Error Message!",text	: 'Category stok empty, select first ...',type	: "warning"
				});
				$('#save').prop('disabled',false); return false;
			}
      if(stock_name == '' ){
				swal({title	: "Error Message!",text	: 'Stok name empty, select first ...',type	: "warning"
				});
				$('#save').prop('disabled',false); return false;
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
						var formData 	=new FormData($('#data-form')[0]);
						var baseurl=siteurl+active_controller+'/add';
						$.ajax({
							url			: baseurl,
							type		: "POST",
							data		: formData,
							cache		: false,
							dataType	: 'json',
							processData	: false,
							contentType	: false,
							success		: function(data){
								if(data.status == 1){
									swal({
										  title	: "Save Success!",
										  text	: data.pesan,
										  type	: "success",
										  timer	: 3000
										});
									window.location.href = base_url + active_controller;
								}else{

									if(data.status == 2){
										swal({
										  title	: "Save Failed!",
										  text	: data.pesan,
										  type	: "warning",
										  timer	: 3000
										});
									}else{
										swal({
										  title	: "Save Failed!",
										  text	: data.pesan,
										  type	: "warning",
										  timer	: 3000
										});
									}

								}
							},
							error: function() {

								swal({
								  title				: "Error Message !",
								  text				: 'An Error Occured During Process. Please try again..',
								  type				: "warning",
								  timer				: 3000
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
