<?=form_open($this->uri->uri_string(),array('id'=>'frm_data','name'=>'frm_data','role'=>'form','class'=>'form-horizontal'));?>
<div class="box">
	<div class="box-body">
	  <div class="table-responsive">
		<table id="mytabledata" class="table table-bordered table-striped">
		<thead>
		<tr>
			<th width="5">#</th>
			<th>Nama</th>
			<th>Spesifikasi</th>
			<th>Brand</th>
			<th>Max Stok</th>
			<th>Qty Stok</th>
			<th>Satuan</th>
			<th>Suggest Qty pembelian</th>
			<th>Request</th>
		</tr>
		</thead>
		<tbody>
		<?php if(empty($results)){
		}else{
			$numb=0; foreach($results AS $record){ $numb++; $stock=0; ?>
		<tr>
		    <td><input type="checkbox" name="" id="" checked>
			<input type="hidden" name="" id="">
			</td>
			<td><?= $record->nama ?></td>
			<td><?= $record->spec1 ?></td>
			<td><?= $record->spec2 ?></td>
			<td><?= $record->spec3 ?></td>
			<td><?= number_format($stock) ?></td>
			<td><?= $record->satuan ?></td>
			<td><?php
			$qty_suggest=($record->spec13-$record->stock);
			echo number_format($qty_suggest) ?></td>
			<td><input type="text" class="form-control divide" value="<?=$qty_suggest;?>"</td>			
		</tr>
		<?php } 
		}  ?>
		</tbody>
		</table>
		<button type="submit" class="tn btn-primary">Pilih</button>
		</div>
	</div>
</div>
<?= form_close() ?>
