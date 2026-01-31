<?php
$ENABLE_ADD     = has_permission('Approval Expense Management.Add');
$ENABLE_MANAGE  = has_permission('Approval Expense Management.Manage');
$ENABLE_VIEW    = has_permission('Approval Expense Management.View');
$ENABLE_DELETE  = has_permission('Approval Expense Management.Delete');
?>
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<div class="box">
    <!-- /.box-header -->
    <div class="box-body">
        <div class="table-responsive col-md-12">
            <table id="mytabledata" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="5">#</th>
                        <th>No Dokumen</th>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>Keterangan</th>
                        <th>Nominal</th>
                        <th>Status</th>
                        <th width="120">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($data)) {
                        $numb = 0;
                        foreach ($data as $record) {
                            $numb++; ?>
                            <tr>
                                <td><?= $numb; ?></td>
                                <td><?= $record->no_doc ?></td>
                                <td><?= $record->tgl_doc ?></td>
                                <td><?= $record->nmuser ?></td>
                                <td><?= $record->informasi ?></td>
                                <td class="text-right"><?= number_format($record->nominal) ?></td>
                                <td><?= $status[$record->status] ?></td>
                                <td>
                                    <?php if ($ENABLE_VIEW) : ?>
                                        <a class="btn btn-warning btn-sm view" href="javascript:void(0)" title="View" onclick="data_view('<?= $record->id ?>')"><i class="fa fa-eye"></i></a>
                                        <?php endif;
                                    if ($ENABLE_MANAGE) :
                                        if ($record->status == 0) { ?>
                                            <a class="btn btn-success btn-sm approve" href="javascript:void(0)" title="Approve" onclick="data_approve('<?= $record->id ?>')"><i class="fa fa-check-square-o"></i></a>
                                    <?php }
                                    endif;
                                    ?>
                                </td>
                            </tr>
                    <?php
                        }
                    }  ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- /.box-body -->
</div>
<div id="form-data"></div>
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript">
    var url_edit = siteurl + 'expense/edit/';
    var url_view = siteurl + 'expense/view/';
    var url_approval = siteurl + 'expense/approval/';
    //Edit
    function data_approve(id) {
        if (id != "") {
            $(".box").hide();
            $("#form-data").show();
            $("#form-data").load(url_approval + id);
        }
    }

    function datatables() {
        var datatables = $('#mytabledata').dataTable({
            serverSide: true,
            processing: true,
            destroy: true,
            paging: true,
            stateSave: true,
            ajax: {
                type: 'post',
                url: siteurl + active_controller + 'get_dat_approval_expense_manage',
                cache: false,
                dataType: 'json',
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error !',
                        text: error,
                        showConfirmButton: false,
                        showCancelButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        timer: 3000
                    });
                },
                columns: [{
                        data: 'no'
                    },
                    {
                        data: 'no_doc'
                    },
                    {
                        data: 'tgl_doc'
                    },
                    {
                        data: 'nama'
                    },
                    {
                        data: 'keterangan'
                    },
                    {
                        data: 'nominal'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'action'
                    }
                ]
            }
        });
    }
</script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>