
<div class="box box-info">
	<div class="box-body">
    <div class="callout callout-success">
       <p>
         <h4><i class="icon fa fa-info"></i> Info !!!</h4>
         <span><b>(NEW) Checklist product yang akan tampil di input produksi. </b></span><br>
     </p>
    </div>
    <br>
		<table id="example1" class="table table-bordered table-striped">
		<thead>
		<tr>
			<th width="5%">#</th>
			<th>Project Name</th>
			<th>Product Name</th>
			<th>Status</th>
		</tr>
		</thead>
		<tbody>
  		<?php
    		$numb=0; foreach($check_p AS $val => $valx){ $numb++;
          $checked = '';
          if($valx['ck_produksi'] == 'Y'){
            $checked = 'checked';
          }
        ?>
    		<tr>
    		  <td><?= $numb; ?></td>
    			<td><?= strtoupper(get_name('ms_inventory_category1','nama','id_category1',$valx['id_category1'])) ?></td>
    			<td><?= strtoupper($valx['nama']) ?></td>
    			<td><label class="checkbox-inline"><input type="checkbox" class='checked_save' value="<?=$valx['id_category2'];?>" <?=$checked;?>> Use production input</label></td>
    		</tr>
  		<?php
      }
      ?>
		</tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>
<!-- page script -->
<script type="text/javascript">
  $(document).ready(function(){

    $('.checked_save').change(function() {
        if(this.checked) {
            console.log('checked');
            $.ajax({
        				url			: base_url + active_controller+'/upd_checked_product',
        				type		: "POST",
        				data		: {
        					"id" : $(this).val(),
        				},
        				cache		: false
        		});
        }
        else{
          console.log('un checked');
          $.ajax({
              url			: base_url + active_controller+'/upd_unchecked_product',
              type		: "POST",
              data		: {
                "id" : $(this).val(),
              },
              cache		: false
          });
        }
    });

    var table = $('#example1').DataTable( {
        orderCellsTop: true,
        fixedHeader: true
    } );
  });
</script>
