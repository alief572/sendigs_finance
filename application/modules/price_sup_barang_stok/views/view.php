<?php
$header = $header ?? null;
$details_by_cat = $details_by_cat ?? [];
$files = $files ?? [];

$status_badge = '<span class="badge bg-yellow">Waiting Approval</span>';
if (!empty($header)) {
    if ($header->status == '1') {
        $status_badge = '<span class="badge bg-green">Approved</span>';
    } elseif ($header->status == '2') {
        $status_badge = '<span class="badge bg-red">Rejected</span>';
    }
}
?>

<div class="row">
    <div class="col-md-6">
        <table class="table table-condensed table-bordered">
            <tr>
                <th width="35%" class="bg-gray">No. Dokumen</th>
                <td><b><?= $header->no_doc ?? '-' ?></b></td>
            </tr>
            <tr>
                <th class="bg-gray">Tanggal Pengajuan</th>
                <td><?= !empty($header->tanggal_doc) ? date('d-M-Y', strtotime($header->tanggal_doc)) : '-' ?></td>
            </tr>
            <tr>
                <th class="bg-gray">Kurs (USD -> IDR)</th>
                <td>Rp <?= number_format($header->kurs ?? 1, 2) ?></td>
            </tr>
            <tr>
                <th class="bg-gray">Status</th>
                <td><?= $status_badge ?></td>
            </tr>
        </table>
    </div>
    <div class="col-md-6">
        <table class="table table-condensed table-bordered">
            <tr>
                <th width="35%" class="bg-gray">Catatan</th>
                <td><?= nl2br(htmlspecialchars($header->note ?? '-')) ?></td>
            </tr>
            <tr>
                <th class="bg-gray">File Evidence</th>
                <td>
                    <?php if (!empty($files)): ?>
                        <?php foreach ($files as $f): ?>
                            <a href="<?= base_url($f->file_path) ?>" target="_blank" class="btn btn-xs btn-primary" style="margin-bottom:3px;">
                                <i class="fa fa-download"></i> <?= htmlspecialchars($f->file_name) ?>
                            </a><br>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="text-muted">Tidak ada lampiran file.</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php if (!empty($header->rejected_reason)): ?>
                <tr class="danger">
                    <th class="text-danger">Alasan Reject</th>
                    <td class="text-danger"><b><?= nl2br(htmlspecialchars($header->rejected_reason)) ?></b></td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<h4 style="margin-top:20px; font-weight:bold;"><i class="fa fa-list"></i> Rincian Barang yang Diajukan</h4>

<?php if (empty($details_by_cat)): ?>
    <div class="alert alert-warning">Tidak ada rincian barang pada dokumen ini.</div>
<?php else: ?>
    <?php foreach ($details_by_cat as $cat_name => $items): ?>
        <div class="panel panel-info" style="margin-bottom:15px;">
            <div class="panel-heading" style="font-weight:bold; padding: 6px 12px;">
                <i class="fa fa-tags"></i> KATEGORI: <?= strtoupper($cat_name) ?> (<?= count($items) ?> item)
            </div>
            <div class="panel-body" style="padding:0;">
                <table class="table table-bordered table-striped table-condensed" style="margin:0;">
                    <thead>
                        <tr class="bg-gray">
                            <th rowspan="2" class="text-center" width="4%">#</th>
                            <th rowspan="2" class="text-center" width="10%">Kode Stok</th>
                            <th rowspan="2" class="text-center" width="22%">Nama Barang & Spesifikasi</th>
                            <th rowspan="2" class="text-center" width="6%">Satuan</th>
                            <th colspan="2" class="text-center bg-gray-active">Harga Aktif (Before)</th>
                            <th colspan="2" class="text-center" style="background:#d9edf7;">New Lower Price</th>
                            <th colspan="2" class="text-center" style="background:#d9edf7;">New Higher Price</th>
                            <th rowspan="2" class="text-center" width="8%">Expired</th>
                        </tr>
                        <tr class="bg-gray">
                            <th class="text-center">Lower IDR</th>
                            <th class="text-center">Higher IDR</th>
                            <th class="text-center" style="background:#d9edf7;">IDR</th>
                            <th class="text-center" style="background:#d9edf7;">USD</th>
                            <th class="text-center" style="background:#d9edf7;">IDR</th>
                            <th class="text-center" style="background:#d9edf7;">USD</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 0; foreach ($items as $d): $no++; ?>
                            <tr>
                                <td class="text-center"><?= $no ?></td>
                                <td class="text-center"><b><?= strtoupper($d->id_stock ?? '-') ?></b></td>
                                <td>
                                    <b><?= strtoupper($d->stock_name) ?></b>
                                    <?php if (!empty($d->spec)): ?>
                                        <br><small class="text-muted"><?= $d->spec ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?= $d->nm_satuan ?? '-' ?></td>
                                <td class="text-right"><?= number_format($d->price_ref_before, 0) ?></td>
                                <td class="text-right"><?= number_format($d->price_ref_high_before, 0) ?></td>
                                <td class="text-right text-bold text-primary"><?= number_format($d->price_ref_new, 0) ?></td>
                                <td class="text-right text-bold text-primary">$ <?= number_format($d->price_ref_new_usd, 4) ?></td>
                                <td class="text-right text-bold text-primary"><?= number_format($d->price_ref_high_new, 0) ?></td>
                                <td class="text-right text-bold text-primary">$ <?= number_format($d->price_ref_high_new_usd, 4) ?></td>
                                <td class="text-center">
                                    <?php
                                        $exp_text = $d->expired . ' Bulan';
                                        if ($d->expired == 6) $exp_text = 'Semester';
                                        if ($d->expired == 12) $exp_text = 'Tahunan';
                                        echo $exp_text;
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
