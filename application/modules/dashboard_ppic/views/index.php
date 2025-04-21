<?php
$ENABLE_ADD     = has_permission('Monitoring_PPIC.Add');
$ENABLE_MANAGE  = has_permission('Monitoring_PPIC.Manage');
$ENABLE_VIEW    = has_permission('Monitoring_PPIC.View');
$ENABLE_DELETE  = has_permission('Monitoring_PPIC.Delete');
?>
<style type="text/css">
    thead input {
        width: 100%;
    }

    .text_ok {
        color: #6EC207;
    }

    .text_bad {
        color: #D91656;
    }
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box">

    <!-- /.box-header -->
    <div class="box-body">
        <table class="table table-bordered table_list">
            <thead class="bg-primary">
                <tr>
                    <th class="text-center">#</th>
                    <th class="text-center">Item</th>
                    <th class="text-center">Stock</th>
                    <th class="text-center">Expired Date</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                foreach ($result as $item) {
                    $date_now = new DateTime(date('Y-m-d'));
                    $date_expired = new DateTime($item->expired_date);

                    $day_until_expired_date = $date_now->diff($date_expired);

                    if ($date_now > $date_expired) {
                        $status = 'Expired ' . $day_until_expired_date->days . ' Hari';
                        $text_class = 'text_bad';
                    } else {
                        $status = 'Expired dalam ' . $day_until_expired_date->days . ' Hari';
                        $text_class = 'text_ok';
                    }

                    echo '<tr class="' . $text_class . '">';
                    echo '<td class="text-center">' . $no . '</td>';
                    echo '<td class="text-left">' . $item->nm_material . '</td>';
                    echo '<td class="text-left">' . number_format($item->qty_oke) . '</td>';
                    echo '<td class="text-center">' . date('d F Y', strtotime($item->expired_date)) . '</td>';
                    echo '<td class="text-left">' . $status . '</td>';
                    echo '</tr>';

                    $no++;
                }
                ?>
            </tbody>
        </table>
    </div>
    <!-- /.box-body -->
</div>

<!-- awal untuk modal dialog -->

<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="head_title">Default</h4>
            </div>
            <div class="modal-body" id="ModalView">
                ...
            </div>
        </div>
    </div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<!-- page script -->
<script type="text/javascript">
    $(document).ready(function() {
        $('.table_list').dataTable();
    });
</script>