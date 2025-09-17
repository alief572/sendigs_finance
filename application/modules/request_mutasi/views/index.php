<?php
$ENABLE_ADD     = has_permission('Request_Mutasi.Add');
$ENABLE_MANAGE  = has_permission('Request_Mutasi.Manage');
$ENABLE_DELETE  = has_permission('Request_Mutasi.Delete');
$ENABLE_VIEW    = has_permission('Request_Mutasi.View');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<div class="box box-primary">
    <div class="box-header">
        <?php if ($ENABLE_ADD) : ?>
            <a class="btn btn-success btn-sm" href="<?= base_url('request_mutasi/create') ?>" title="Tambah"><i class="fa fa-plus">&nbsp;</i>Tambah</a>
        <?php endif; ?>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <div class="table-responsive">
            <table id="mytabledata" class="table table-bordered table-striped" width='100%'>
                <thead>
                    <tr class='bg-blue'>
                        <th class="text-center" width='4%'>No</th>
                        <th width='7%'>Tgl Request</th>
                        <th width='7%'>Kode Mutasi</th>
                        <th width='18%'>Keterangan</th>
                        <th width='7%'>Bank Asal</th>
                        <th width='7%'>Bank Tujuan</th>
                        <th width='7%'>Mata Uang</th>
                        <th class="text-right" width='7%'>Nilai</th>
                        <th class="text-center" width='7%'>Option</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($data)) {
                        $numb = 0;
                        foreach ($data as $record) {
                            $numb++;
                    ?>
                            <tr>
                                <td><?= $numb; ?></td>
                                <td><?= date('d-F-Y', strtotime($record->tgl_request)) ?></td>
                                <td><?= $record->kd_mutasi ?></td>
                                <td><?= $record->keterangan ?></td>
                                <td><?= $record->nama_bank_asal ?></td>
                                <td><?= $record->nama_bank_tujuan ?></td>
                                <td><?= $record->mata_uang ?></td>
                                <td align="right"><?= number_format($record->nilai_request) ?></td>
                                <td style="padding-left:20px">
                                    <?php if ($record->status_approve == '0') { ?>
                                        <span class="badge badge-pill bg-blue">Menunggu Approval</span>
                                    <?php } elseif ($record->status_approve == '2') { ?>
                                        <a class="badge badge-pill bg-red" title="<?= $record->alasan ?>">Ditolak</a>
                                    <?php } else { ?>
                                        <a href=" <?= base_url('request_mutasi/add_mutasi/' . $record->kd_mutasi) ?>" class='btn btn-sm btn-warning' title='Add Mutasi'><i class='fa fa-plus'></i></a>
                                        <a href="<?= base_url('request_mutasi/printout/' . $record->kd_mutasi) ?>" class='btn btn-sm btn-primary' title='Print' target="_blank"><i class='fa fa-print'></i></a>
                                    <?php } ?>
                                </td>

                            </tr>
                    <?php }
                    } ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- /.box-body -->
</div>
<div id="form-data"></div>
<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>
<!-- page script -->
<script type="text/javascript">
    var url_add = siteurl + 'Request_mutasi/create/';
    var url_edit = siteurl + 'Request_mutasi/edit/';
    var url_delete = siteurl + 'Request_mutasi/delete/';
</script>