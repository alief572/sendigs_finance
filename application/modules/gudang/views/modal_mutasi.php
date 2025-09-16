
<form action="#" method="POST" id="form_adjustment" enctype="multipart/form-data" autocomplete='off'>
<div class="box-body">
	<br>
  <input type="hidden" name='gudang_before' id='gudang_before' value='<?= $gudang_before;?>'>
	<input type="hidden" name='gudang_after' id='gudang_after' value='<?= $gudang_after;?>'>

	<table id="example2" class="table table-striped table-bordered table-hover" width="100%">
		<thead>
			<tr>
				<th class="text-center" style='vertical-align:middle;' width='5%'>No</th>
				<th class="text-left" style='vertical-align:middle;' width='36%'>Material Name</th>
				<th class="text-right no-sort" style='vertical-align:middle;' width='8%'>Stock Pack</th>
				<th class="text-left no-sort" style='vertical-align:middle;' width='6%'>Unit Pack</th>
				<th class="text-right no-sort" style='vertical-align:middle;' width='8%'>Stock</th>
				<th class="text-left no-sort" style='vertical-align:middle;' width='6%'>Unit Stock</th>
        <th class="text-center no-sort" style='vertical-align:middle;' width='8%'>Req Pack</th>
				<th class="text-center no-sort" style='vertical-align:middle;' width='8%'>Req Stock</th>
				<th class="text-center no-sort" style='vertical-align:middle;' width='15%'>Keterangan</th>
			</tr>
		</thead>
		<tbody></tbody>
	</table>
		<span id='notive' style='color:red;font-weight:bold;float:right;font-size:18px;'></span><br><br>
    <?php
		if(!empty($result)){
			echo form_button(array('type'=>'button','class'=>'btn btn-md btn-success','style'=>'min-width:100px; float:right; margin: 5px 0px 5px 0px;','value'=>'Save','content'=>'Save','id'=>'process_mutasi'));
		}
	?>
</div>
</form>
<style>
	.tanggal{
		cursor: pointer;
	}
</style>
<script>
	$(document).ready(function(){
    swal.close();
		var gudang_before 	= $('#gudang_before').val();
		DataTables2(gudang_before);

		$('.maskM').maskMoney();
  });
</script>
