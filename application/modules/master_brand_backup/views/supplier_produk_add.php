

<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">

<div class="box">
    <div class="box-header">
        <a href="<?= base_url('supplier/produk/'.$results->id_supplier); ?>" class="btn btn-success">Kembali</a>
    
    </div>
    <!-- /.box-header -->
    <form action='<?= base_url('supplier/addproduk_save/'.$results->id_supplier); ?>' method='post'>
    <div class="box-body">
        <table id="example1" class="table table-bordered table-striped">
        <thead>
        <tr>
            <th width="50">#</th>
            <th>Name Produk</th>
            <th width="25">Action</th>
        </tr>
        </thead>

        <tbody>
           <?php if($data !== 0) {
                $num = 1;
                foreach ($data as $row) : ?>
                <tr>
                    <td><?= $num;  ?></td>
                    <td><?= $row->nm_barang;  ?></td>
                    <td style="text-align: center">
                       <input type='checkbox' name='id_barang[]' value='<?= $row->id_barang;  ?>'>
                    </td>
                </tr>
            <?php $num++; endforeach;
                 } ?>
        </tbody>

        </table>
    </div>
    <!-- /.box-body -->
</div>
<div class="text-right">
  <div class="box active">
    <div class="box-body">
      <input class="btn btn-warning" type='reset' value='Reset'>
      <button  class="btn btn-primary" name='submit' type="submit"> Simpan</button>
    </div>
  </div>
</div>


<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>

<script type="text/javascript">

    $(function() {
        $("#example1").DataTable();
        $("#form-area").hide();
    });
</script>