<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box box-primary">
    <div class="box-body table-responsive">
        <table class="table table-bordered table-striped" id="mytabledata" width='100%'>
            <thead>
                <tr class='bg-blue'>
                    <th class="text-center" width='4%'>No</th>
                    <th width='7%'>Tgl Mutasi</th>
                    <th width='7%'>Kode Mutasi</th>
                    <th width='18%'>Keterangan</th>
                    <th width='7%'>Bank Asal</th>
                    <th width='7%'>Bank Tujuan</th>
                    <th class="text-right" width='7%'>Nilai</th>
                    <th class="text-center" width='7%'>Option</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data)) {
                } else {

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
                            <td align="right"><?= number_format($record->nilai_request) ?></td>
                            <td style="padding-left:20px">
                                <a href="<?= base_url('request_mutasi/printout_mutasi/' . $record->kd_mutasi) ?>" class='btn btn-sm btn-primary' title='Print' target="_blank"><i class='fa fa-print'></i></a>
                            </td>

                        </tr>
                <?php }
                }  ?>
            </tbody>
        </table>
    </div>
</div>
<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>