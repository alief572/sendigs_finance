<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<div class="row">

        <!-- NEW WIDGET START -->
        <article class="col-sm-12">

            <?php if($this->session->flashdata('success')): ?>
                <div class="alert alert-success fade in">
                    <button class="close" data-dismiss="alert">
                        ×
                    </button>
                    <i class="fa-fw fa fa-check"></i>
                    <strong>Success</strong> <?php echo $this->session->flashdata('success'); ?>
                </div>
            <?php endif ?>
            <?php if($this->session->flashdata('error')): ?>
                <div class="alert alert-danger fade in">
                    <button class="close" data-dismiss="alert">
                        ×
                    </button>
                    <i class="fa-fw fa fa-times"></i>
                    <strong>Error!</strong> <?php echo $this->session->flashdata('error'); ?>
                </div>
            <?php endif ?>

        </article>
        <!-- WIDGET END -->

    </div>
<div class="box">
    <div class="box-header">
        <a href="<?= base_url('supplier/addproduk/'.$results->id_supplier); ?>" class="btn btn-success"  title="Add" ><i class="fa fa-plus">&nbsp;</i>New Produk</a>
    
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <table id="example1" class="table table-bordered table-striped">
        <thead>
        <tr>
            <th width="50">#</th>
            <th>Name CBM</th>
            <th width="25">Action</th>
        </tr>
        </thead>

        <tbody>
           <?php if($data->num_rows() !== 0) {
                $num = 1;
                foreach ($data->result() as $row) : ?>
                <tr>
                    <td><?= $num;  ?></td>
                    <td><?= $row->nm_barang;  ?></td>
                    <td style="text-align: center">
                       <a href="<?= base_url('supplier/delete_produk/'.$row->id_supplier_barang.'/'.$results->id_supplier); ?>" rel="tooltip" data-placement="top" data-original-title="Hapus" class="btn btn-xs btn-default txt-color-red" onclick="return confirm('Are you sure you want to delete this data?')"><i class="fa fa-trash"></i></a>
                    </td>
                </tr>
            <?php $num++; endforeach;
                 } ?>
        </tbody>

        </table>
    </div>
    <!-- /.box-body -->
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