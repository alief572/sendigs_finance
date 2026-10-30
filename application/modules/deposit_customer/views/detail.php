<?php
defined('BASEPATH') or exit('No direct script access allowed');
$id_customer = $results['id_customer'] ?? '';
$nm_customer = $results['nm_customer'] ?? '';
$summary = $results['summary'] ?? array();
$mutasi = $results['mutasi'] ?? array();
$rp = function ($v) { return 'Rp ' . number_format((float)$v, 2, ',', '.'); };
?>

<style>
    .table-detail th {
        background-color: #f8fafc;
        color: #334155;
        font-weight: 700;
        font-size: 12px;
        vertical-align: middle !important;
    }
    .table-detail td {
        vertical-align: middle !important;
        font-size: 13px;
    }
    .num {
        font-variant-numeric: tabular-nums;
    }
    .profile-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 16px 20px;
        margin-bottom: 20px;
    }
    .profile-card .k { display: block; color: #64748b; font-size: 12px; }
    .profile-card .v { font-weight: 700; color: #1e293b; font-variant-numeric: tabular-nums; }
</style>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title" style="font-weight: 700; color: #1e293b;">
            <i class="fa fa-book"></i> Monitoring Deposit &middot; Detail
        </h3>
        <div class="box-tools pull-right">
            <?php if (function_exists('has_permission') ? has_permission('Deposit_Customer.Export') : true) : ?>
                <a href="<?= base_url('deposit_customer/export_excel?id_customer=' . urlencode($id_customer)) ?>" class="btn btn-sm btn-success">
                    <i class="fa fa-file-excel-o"></i> Export Excel
                </a>
            <?php endif; ?>
            <a href="<?= base_url('deposit_customer') ?>" class="btn btn-sm btn-default">
                <i class="fa fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="box-body">
        <div class="profile-card">
            <div class="row">
                <div class="col-md-4 col-sm-6"><span class="k">Customer</span><span class="v"><?= htmlspecialchars($nm_customer, ENT_QUOTES, 'UTF-8') ?></span></div>
                <div class="col-md-4 col-sm-6"><span class="k">Total Nilai Deposit</span><span class="v"><?= $rp($summary['total_nilai'] ?? 0) ?></span></div>
                <div class="col-md-4 col-sm-6"><span class="k">Dari Kwitansi Deposit</span><span class="v"><?= $rp($summary['dari_kwitansi'] ?? 0) ?></span></div>
                <div class="col-md-4 col-sm-6"><span class="k">Dari Lebih Bayar</span><span class="v"><?= $rp($summary['dari_lebih_bayar'] ?? 0) ?></span></div>
                <div class="col-md-4 col-sm-6"><span class="k">Total Terpakai</span><span class="v"><?= $rp($summary['total_terpakai'] ?? 0) ?></span></div>
                <div class="col-md-4 col-sm-6"><span class="k">Sisa Saldo Deposit</span><span class="v text-success"><?= $rp($summary['sisa_saldo'] ?? 0) ?></span></div>
            </div>
        </div>

        <h4 style="font-weight: 700; color: #334155; margin-bottom: 12px;">
            <i class="fa fa-history"></i> Mutasi Deposit
        </h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-detail">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 40px;">No</th>
                        <th class="text-center" style="width: 100px;">Tanggal</th>
                        <th style="width: 150px;">No Penerimaan</th>
                        <th style="width: 220px;">Sumber</th>
                        <th>Keterangan</th>
                        <th class="text-right" style="width: 140px;">Masuk</th>
                        <th class="text-right" style="width: 140px;">Keluar</th>
                        <th class="text-right" style="width: 150px;">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($mutasi)) : ?>
                        <?php
                        $no = 1;
                        $running = 0;
                        foreach ($mutasi as $m) :
                            $nom = (float)$m['nominal'];
                            $isMasuk = ($m['jenis_mutasi'] === 'IN');
                            $running += ($isMasuk ? $nom : -$nom);
                        ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td class="text-center"><?= date('d-M-Y', strtotime($m['tanggal'])) ?></td>
                                <td class="font-bold"><?= htmlspecialchars($m['no_transaksi'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars(Deposit_customer_model::source_label($m), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($m['keterangan'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-right num <?= $isMasuk ? 'font-bold text-success' : 'text-muted' ?>"><?= $isMasuk ? $rp($nom) : '-' ?></td>
                                <td class="text-right num <?= !$isMasuk ? 'font-bold text-danger' : 'text-muted' ?>"><?= !$isMasuk ? $rp($nom) : '-' ?></td>
                                <td class="text-right num font-bold" style="color: #1e293b; background-color: #f8fafc;"><?= $rp($running) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
