<?php

 header("Content-type: application/vnd-ms-excel");
 header("Content-Disposition: attachment; filename=report_produksi.xls");
 header("Pragma: no-cache");
 header("Expires: 0");
 ?>
<style type="text/css">
thead input {
	width: 100%;
}
</style>

<table border="1" class="table table-bordered">
  <thead>
    <tr>
      <th>#</th>
      <th>Production Date</th>
      <th>Costcenter</th>
      <th>Project Name</th>
      <th>Product Name</th>
      <th>Qty Order</th>
      <th>Qty Plan</th>
      <th>Qty Oke</th>
      <th>Qty Failed</th>
    </tr>
  </thead>
  <tbody>
    <?php
    if(!empty($results)){
      $numb=0;
      foreach($results AS $record){
        $numb++; ?>
        <tr>
          <td><?= $numb; ?></td>
          <td><?= date('d-F-Y', strtotime($record->tanggal_produksi)) ?></td>
          <td><?= strtoupper(get_name('ms_costcenter', 'nama_costcenter', 'id_costcenter', $record->id_costcenter)); ?></td>
          <td><?= strtoupper(get_name('ms_inventory_category1', 'nama', 'id_category1', $record->id_category1)); ?></td>
          <td><?= strtoupper(get_name('ms_inventory_category2', 'nama', 'id_category2', $record->id_product)); ?></td>
          <td align='center'><?= get_qty_order($record->id_product);?></td>
          <td align='center'><?= get_sum_planning($record->id_product);?></td>
          <td align='center'><?= get_qty_oke($record->tanggal_produksi, $record->id_product, $record->id_costcenter);?></td>
          <td align='center'><?= get_qty_rusak($record->tanggal_produksi, $record->id_product, $record->id_costcenter);?></td>
        </tr>
      <?php
      }
    }  ?>
  </tbody>
</table>
