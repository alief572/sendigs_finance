<?php

 header("Content-type: application/vnd-ms-excel");

 header("Content-Disposition: attachment; filename=x.xls");

 header("Pragma: no-cache");

 header("Expires: 0");

 ?>

<style type="text/css">
thead input {
	width: 100%;
}
</style>
<div class="box">
	<!-- /.box-header -->
	<div class="box-body">
		<table border="1" class="table table-bordered">
  		<thead>
    		<tr>
    			<th>#</th>
    			<th>Kode Produk</th>
    			<th>Jenis Produk</th>
    			<th>Group Produk</th>
    			<th>Nama Set</th>
    			<th>Satuan</th>
    			<th>Qty</th>
    			<th>Status</th>
          <th>ID Koli</th>
    			<th>Nama Koli</th>
    			<th>Qty. Koli</th>
    			<th>Satuan</th>
    		</tr>
  		</thead>

		<tbody>
		<?php if ($brg_data) {

    $numb = 0;
    foreach ($brg_data as $record) {
      $ambil_koli = $this->db->get_where('barang_koli',array('id_barang'=>$record->id_barang))->result();
      $rs = count($ambil_koli);
        ++$numb; ?>
		<tr>
			<?php
                if ($record->satuan == '') {
                    $satuan = $record->setpcs;
                } else {
                    $satuan = $record->satuan;
                } ?>
		    <td rowspan="<?=$rs?>"><?= $numb; ?></td>
	        <td rowspan="<?=$rs?>"><?= $record->id_barang; ?></td>

			<td rowspan="<?=$rs?>"><?= strtoupper($record->nm_jenis); ?></td>
			<td rowspan="<?=$rs?>"><?= strtoupper($record->nm_group); ?></td>
			<td rowspan="<?=$rs?>"><?= $record->nm_barang; ?></td>
			<td rowspan="<?=$rs?>"><?= $satuan; ?></td>
			<td rowspan="<?=$rs?>"><?= $record->qty; ?></td>
			<td rowspan="<?=$rs?>">
				<?php if ($record->sts_aktif == 'aktif') {
					echo '<strong>Aktif</strong>';
              } else {
					echo 'Non Aktif';
				      } ?>
			</td>
      <?php foreach ($ambil_koli as $key => $value) {?>
        <td><?=$value->id_koli?></td>
        <td><?=$value->nm_koli?></td>
        <td><?=$value->qty?></td>
        <td><?=$value->satuan?></td>
      <?php if ($rs > 1) {
        echo '</tr>';
      } } ?>
		</tr>
		<?php
    }
}  ?>
		</tbody>

  		<tfoot>
    		<tr>
    			<th>#</th>
    			<th>Kode Produk</th>
    			<th>Jenis Produk</th>
    			<th>Group Produk</th>
    			<th>Nama Set</th>
    			<th>Satuan</th>
    			<th>Qty</th>
    			<th>Status</th>
    		</tr>
  		</tfoot>
		</table>
	</div>
	<!-- /.box-body -->
</div>
