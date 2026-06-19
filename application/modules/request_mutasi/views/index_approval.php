<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<div class="box box-primary">
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="mytabledata" width='100%'>
                <thead>
                    <tr class='bg-blue'>
                        <th class="text-center" width='3%'>No</th>
                        <th width='8%'>Tgl Request</th>
                        <th width='9%'>Kode Mutasi</th>
                        <th width='14%'>Keterangan</th>
                        <th width='10%'>Bank Asal</th>
                        <th width='10%'>Bank Tujuan</th>
                        <th width='5%'>Mata Uang</th>
                        <th width='6%'>Accounting</th>
                        <th class="text-right" width='8%'>Nilai</th>
                        <th width='8%'>Dibuat Oleh</th>
                        <th width='8%'>Tgl Dibuat</th>
                        <th class="text-center" width='7%'>Option</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data)) {
                        $numb = 0;
                        foreach ($data as $record) {
                            $numb++;
                    ?>
                            <tr>
                                <td class="text-center"><?= $numb; ?></td>
                                <td><?= date('d-F-Y', strtotime($record->tgl_request)) ?></td>
                                <td><?= $record->kd_mutasi ?></td>
                                <td><?= $record->keterangan ?></td>
                                <td><?= $record->nama_bank_asal ?></td>
                                <td><?= $record->nama_bank_tujuan ?></td>
                                <td><?= !empty($record->mata_uang) ? $record->mata_uang : '-' ?></td>
                                <td>
                                    <?php
                                    $target_labels = [
                                        'accounting_stm'     => 'STM',
                                        'accounting_vuca'    => 'VUCA',
                                        'accounting_sustain' => 'SUSTAIN',
                                    ];
                                    if (!empty($record->target_accounting) && isset($target_labels[$record->target_accounting])) {
                                        echo '<span class="label label-info">' . $target_labels[$record->target_accounting] . '</span>';
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td align="right"><?= number_format($record->nilai_request) ?></td>
                                <td><?= isset($record->created_by) ? $record->created_by : '-' ?></td>
                                <td><?= isset($record->created_on) ? date('d-M-Y H:i', strtotime($record->created_on)) : '-' ?></td>
                                <td class="text-center">
                                    <a href="<?= base_url('request_mutasi/approval/' . $record->kd_mutasi) ?>" class='btn btn-sm btn-success' title='Approval'><i class='fa fa-check'></i></a>
                                    <a href="<?= base_url('request_mutasi/printout/' . $record->kd_mutasi) ?>" class='btn btn-sm btn-primary' title='Print' target="_blank"><i class='fa fa-print'></i></a>
                                </td>
                            </tr>
                    <?php }
                    }  ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>