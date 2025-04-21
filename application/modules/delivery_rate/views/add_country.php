
<div class="box box-primary">
	<div class="box-body">
		<form id="data_form" method="post"  autocomplete="off" enctype='multiple/form-data'>
			<div class="form-group row export">
				<div class="col-md-2">
				<label for="">Country <span class='text-danger'>*</span></label>
				</div>
				<div class="col-md-10">
                    <select name="country_code" id="country_code" class='chosen-select'>
                        <option value="0">Select Country</option>
                        <?php
                        foreach ($country as $key => $value) {
                            echo "<option value='".$value['iso3']."'>".strtoupper($value['name'])."</option>";
                        }
                        ?>
                    </select>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2"></div>
				<div class="col-md-10">
				<button type="button" class="btn btn-primary" name="save" id="save_country"><i class="fa fa-save"></i> Save</button>
				</div>
			</div>
		</form>
	</div>
</div>

<script>
	$(document).ready(function(){
    	$('.chosen-select').select2({width: '100%'});
  	});
</script>
