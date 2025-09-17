<?php
    $ENABLE_ADD     = has_permission('Customer.Add');
    $ENABLE_MANAGE  = has_permission('Customer.Manage');
    $ENABLE_VIEW    = has_permission('Customer.View');
    $ENABLE_DELETE  = has_permission('Customer.Delete');
?>
<div class="box box-primary">
	<div class="box-header">
        <?php if ($ENABLE_ADD) : ?>
            <a class="btn btn-success" href="javascript:void(0)" title="Add" onclick="add_data()"><i class="fa fa-plus">&nbsp;</i>New</a>
        <?php endif; ?>

        <span class="pull-right">
                <?php //echo anchor(site_url('customer/downloadExcel'), ' <i class="fa fa-download"></i> Excel ', 'class="btn btn-primary btn-sm"'); ?>
        </span>
    </div>
    <div class="box-body">
        <table id="example1" class="table table-bordered table-striped">
        <thead>
        <tr>
            <th width="50">#</th>
            <th>Customer ID</th>
            <th>Nama Customer</th>
            <th>Bidang Usaha</th>
            <th>Produk</th>
            <th>Kredibilitas</th>
            <th>Alamat</th>
            <th>Status</th>
            <?php if($ENABLE_MANAGE) : ?>
            <th width="25">Action</th>
            <?php endif; ?>
        </tr>
        </thead>
        </table>
    </div>
</div>