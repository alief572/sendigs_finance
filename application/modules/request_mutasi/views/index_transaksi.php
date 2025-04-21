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
            <a class="btn btn-success btn-sm" href="<?= base_url('request_mutasi/create_transaksi') ?>" title="Tambah"><i class="fa fa-plus">&nbsp;</i>Tambah</a>
        <?php endif; ?>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table id="mytabledata" class="table table-bordered table-striped" width='100%'>
                <thead>
                    <tr class='bg-blue'>
                        <th class="text-center" width='4%'>No</th>
                        <th width='7%'>Tgl Transaksi</th>
                        <th width='7%'>Kode Transaksi</th>
                        <th width='18%'>Keterangan</th>
                        <th width='7%'>COA Asal</th>
                        <th width='7%'>COA Tujuan</th>
                        <th class="text-right" width='7%'>Nilai</th>
                        <th class="text-right" width='7%'>Transaksi</th>
                        <th class="text-center" width='7%'>Option</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data)) {
                        $numb = 0;
                        foreach ($data as $record) {
                            $numb++;
                            $tanggal = $record->tgl_request;
                    ?>
                            <tr>
                                <td><?= $numb; ?></td>
                                <td><?= date('d-F-Y', strtotime($record->tgl_request)) ?></td>
                                <td><?= $record->kd_mutasi ?></td>
                                <td><?= $record->keterangan ?></td>
                                <td><?= $record->nama_bank_asal ?></td>
                                <td><?= $record->nama_bank_tujuan ?></td>
                                <td align="right"><?= number_format($record->nilai) ?></td>
                                <td align="right"><?= number_format($record->transaksi) ?></td>
                                <td style="padding-left:20px">
                                    <a href="<?= base_url('request_mutasi/printout_transaksi/' . $record->kd_mutasi) ?>" class='btn btn-sm btn-primary' title='Print' target="_blank"><i class='fa fa-print'></i></a>
                                </td>
                            </tr>
                    <?php
                        }
                    }  ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>