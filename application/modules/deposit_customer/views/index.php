<?php
defined('BASEPATH') or exit('No direct script access allowed');
$kpi = $results['kpi'] ?? array();
$customers = $results['customers'] ?? array();
?>

<link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css">
<style>
    .kpi-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 16px 20px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .kpi-title {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        margin-bottom: 6px;
    }
    .kpi-value {
        font-size: 22px;
        font-weight: 700;
        color: #1e293b;
        font-variant-numeric: tabular-nums;
    }
    .kpi-subtext {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 4px;
    }
    .table-monitoring th {
        background-color: #f8fafc;
        color: #334155;
        font-weight: 700;
        font-size: 12px;
        vertical-align: middle !important;
        border-bottom: 2px solid #cbd5e1 !important;
    }
    .table-monitoring td {
        vertical-align: middle !important;
        font-size: 13px;
    }
    .num {
        font-variant-numeric: tabular-nums;
    }
</style>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title" style="font-weight: 700; color: #1e293b;">
            <i class="fa fa-folder-open-o"></i> Monitoring Deposit
        </h3>
        <div class="box-tools pull-right">
            <a href="<?= base_url('deposit_customer/export_excel') ?>" class="btn btn-sm btn-success">
                <i class="fa fa-file-excel-o"></i> Export to Excel
            </a>
        </div>
    </div>

    <div class="box-body">
        <!-- 4 KPI Cards Row -->
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="kpi-card" style="border-left: 4px solid #0284c7;">
                    <div class="kpi-title"><i class="fa fa-money"></i> Total Sisa Saldo Deposit</div>
                    <div class="kpi-value text-primary">
                        Rp <?= number_format($kpi['total_saldo'] ?? 0, 2, ',', '.') ?>
                    </div>
                    <div class="kpi-subtext">Total masuk dikurangi terpakai, seluruh customer</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="kpi-card" style="border-left: 4px solid #10b981;">
                    <div class="kpi-title"><i class="fa fa-users"></i> Customer Punya Saldo</div>
                    <div class="kpi-value text-success">
                        <?= number_format($kpi['total_customer_aktif'] ?? 0) ?>
                    </div>
                    <div class="kpi-subtext">Customer dengan saldo deposit > Rp 0</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="kpi-card" style="border-left: 4px solid #f59e0b;">
                    <div class="kpi-title"><i class="fa fa-arrow-circle-down"></i> Jumlah Deposit Masuk</div>
                    <div class="kpi-value text-warning">
                        <?= number_format($kpi['cnt_masuk'] ?? 0) ?>
                    </div>
                    <div class="kpi-subtext">Penerimaan Kwitansi Deposit dan Lebih Bayar</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="kpi-card" style="border-left: 4px solid #64748b;">
                    <div class="kpi-title"><i class="fa fa-arrow-circle-up"></i> Total Terpakai</div>
                    <div class="kpi-value" style="color: #475569;">
                        Rp <?= number_format($kpi['total_keluar'] ?? 0, 2, ',', '.') ?>
                    </div>
                    <div class="kpi-subtext">Sudah dialokasikan ke penerimaan piutang</div>
                </div>
            </div>
        </div>

        <div class="alert alert-info" style="margin-bottom: 15px; padding: 10px 15px; font-size: 13px;">
            Halaman ini <b>hanya untuk memantau</b>, tidak ada input. Saldo dicatat per customer dan terbentuk otomatis dari <b>Penerimaan Piutang Kwitansi Deposit</b> (masuk), serta dari <b>Penerimaan Piutang Invoice</b>: <b>Lebih Bayar</b> (masuk) dan <b>Deposit Dipakai</b> (keluar). Kwitansi yang belum dibayar belum menambah saldo.
        </div>

        <!-- Customer Summary Table -->
        <div class="table-responsive" style="margin-top: 10px;">
            <table class="table table-bordered table-hover table-monitoring" id="table_deposit_summary">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 40px;">No</th>
                        <th>Customer</th>
                        <th class="text-center" style="width: 110px;">Jumlah Deposit</th>
                        <th class="text-right" style="width: 135px;">Dari Kwitansi Deposit</th>
                        <th class="text-right" style="width: 135px;">Dari Lebih Bayar</th>
                        <th class="text-right" style="width: 140px;">Total Nilai Deposit</th>
                        <th class="text-right" style="width: 135px;">Total Terpakai</th>
                        <th class="text-right" style="width: 145px;">Total Sisa Saldo</th>
                        <th class="text-center" style="width: 110px;">Deposit Terakhir</th>
                        <th class="text-center" style="width: 100px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $totJumlah = 0; $totKwDep = 0; $totLebihDep = 0; $totNilaiDep = 0; $totTerpakaiDep = 0; $totSisaDep = 0;
                    $no = 1;
                    foreach ($customers as $c) :
                        $sisa = (float)$c['sisa_saldo'];
                        $awal = (float)$c['total_nominal_awal'];
                        $kw = (float)($c['total_dari_kwitansi'] ?? 0);
                        $lebih = (float)($c['total_dari_lebih_bayar'] ?? 0);
                        $terpakai = (float)$c['total_nominal_terpakai'];
                        $totJumlah += (int)$c['jumlah_deposit'];
                        $totSisaDep += $sisa; $totNilaiDep += $awal; $totKwDep += $kw; $totLebihDep += $lebih; $totTerpakaiDep += $terpakai;
                    ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td><div class="font-bold" style="color: #1e293b;"><?= htmlspecialchars($c['nm_customer'], ENT_QUOTES, 'UTF-8') ?></div></td>
                            <td class="text-center"><?= (int)$c['jumlah_deposit'] ?></td>
                            <td class="text-right num">Rp <?= number_format($kw, 2, ',', '.') ?></td>
                            <td class="text-right num">Rp <?= number_format($lebih, 2, ',', '.') ?></td>
                            <td class="text-right num font-bold">Rp <?= number_format($awal, 2, ',', '.') ?></td>
                            <td class="text-right num text-muted">Rp <?= number_format($terpakai, 2, ',', '.') ?></td>
                            <td class="text-right num font-bold" style="font-size: 14px;">
                                <?php if ($sisa > 0.009) : ?>
                                    <span class="text-success">Rp <?= number_format($sisa, 2, ',', '.') ?></span>
                                <?php else : ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= !empty($c['last_deposit_date']) ? date('d-M-Y', strtotime($c['last_deposit_date'])) : '-' ?></td>
                            <td class="text-center nowrap">
                                <a href="<?= base_url('deposit_customer/detail?id_customer=' . urlencode($c['id_customer'])) ?>" class="btn btn-xs btn-primary" title="Lihat mutasi deposit">
                                    <i class="fa fa-list"></i> Detail
                                </a>
                                <a href="<?= base_url('deposit_customer/export_excel?id_customer=' . urlencode($c['id_customer'])) ?>" class="btn btn-xs btn-success" title="Download Excel mutasi customer">
                                    <i class="fa fa-file-excel-o"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background: #f1f5f9; font-weight: 700;">
                        <td colspan="2" class="text-right">Total</td>
                        <td class="text-center" id="tot_foot_jumlah"><?= (int)$totJumlah ?></td>
                        <td class="text-right num" id="tot_foot_kw">Rp <?= number_format($totKwDep, 2, ',', '.') ?></td>
                        <td class="text-right num" id="tot_foot_lebih">Rp <?= number_format($totLebihDep, 2, ',', '.') ?></td>
                        <td class="text-right num" id="tot_foot_nilai">Rp <?= number_format($totNilaiDep, 2, ',', '.') ?></td>
                        <td class="text-right num" id="tot_foot_terpakai">Rp <?= number_format($totTerpakaiDep, 2, ',', '.') ?></td>
                        <td class="text-right num text-success" id="tot_foot_sisa" style="font-size: 14px;">Rp <?= number_format($totSisaDep, 2, ',', '.') ?></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
<script>
$(document).ready(function() {
    $('#table_deposit_summary').DataTable({
        pageLength: 25,
        ordering: true,
        order: [[7, 'desc']],
        language: {
            emptyTable: "Belum ada data deposit customer.",
            search: "Cari nama customer:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ customer",
            zeroRecords: "Customer tidak ditemukan"
        }
    });
});
</script>
