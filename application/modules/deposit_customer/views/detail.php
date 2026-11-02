<?php
defined('BASEPATH') or exit('No direct script access allowed');
$e = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
?>
<?= mk_ui_assets() ?>
<div class="mk">
<h1>Monitoring Deposit</h1>

<?php if (!empty($results['not_found'])) : ?>
    <div class="box">Customer tidak ditemukan. <a href="<?= base_url('deposit_customer') ?>">Kembali</a></div>
<?php else :
    $id_customer = $results['id_customer'] ?? '';
    $nm_customer = $results['nm_customer'] ?? '';
    $summary = $results['summary'] ?? array();
    $mutasi = $results['mutasi'] ?? array();
    $canExport = function_exists('has_permission') ? has_permission('Deposit_Customer.Export') : true;
?>
    <div class="crumb"><a href="<?= base_url('deposit_customer') ?>">Monitoring Deposit</a> / Detail</div>

    <div class="box primary">
        <div class="kv">
            <div><span>Customer</span><?= $e($nm_customer) ?></div>
            <div><span>Total Nilai Deposit</span>Rp <?= mk_idn($summary['total_nilai'] ?? 0) ?></div>
            <div><span>Dari Kwitansi Deposit</span>Rp <?= mk_idn($summary['dari_kwitansi'] ?? 0) ?></div>
            <div><span>Dari Lebih Bayar</span>Rp <?= mk_idn($summary['dari_lebih_bayar'] ?? 0) ?></div>
            <div><span>Total Terpakai</span>Rp <?= mk_idn($summary['total_terpakai'] ?? 0) ?></div>
            <div><span>Sisa Saldo Deposit</span><span class="state-ok">Rp <?= mk_idn($summary['sisa_saldo'] ?? 0) ?></span></div>
        </div>

        <div class="tbl-bar">
            <div class="section-t">Mutasi Deposit</div>
            <?php if (!empty($mutasi) && $canExport) : ?>
                <a class="btn btn-success btn-xs" href="<?= base_url('deposit_customer/export_excel?id_customer=' . urlencode($id_customer)) ?>">Export Excel</a>
            <?php else : ?>
                <button class="btn btn-success btn-xs" disabled>Export Excel</button>
            <?php endif; ?>
        </div>

        <?php if (!empty($mutasi)) : ?>
            <div class="scroll">
                <table>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Tanggal</th>
                            <th>No Penerimaan</th>
                            <th>Sumber</th>
                            <th>Keterangan</th>
                            <th class="r">Masuk</th>
                            <th class="r">Keluar</th>
                            <th class="r">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $running = 0;
                        foreach ($mutasi as $m) :
                            $nom = (float)$m['nominal'];
                            $isMasuk = ($m['jenis_mutasi'] === 'IN');
                            $running += ($isMasuk ? $nom : -$nom);
                        ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td class="nowrap"><?= $e(mk_fdate($m['tanggal'])) ?></td>
                                <td class="nowrap"><?= !empty($m['penerimaan_id']) ? '<a href="' . base_url('penerimaan_uang/view/' . (int)$m['penerimaan_id']) . '">' . $e($m['no_transaksi']) . '</a>' : $e($m['no_transaksi']) ?></td>
                                <td><?= $e(Deposit_customer_model::source_label($m)) ?></td>
                                <td><?= $e($m['keterangan'] ?? '') ?></td>
                                <td class="r"><?= $isMasuk ? '<span class="state-ok">' . mk_en($nom) . '</span>' : '<span class="muted">-</span>' ?></td>
                                <td class="r"><?= !$isMasuk ? '<span class="state-bad">' . mk_en($nom) . '</span>' : '<span class="muted">-</span>' ?></td>
                                <td class="r"><?= mk_en($running) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <div class="muted">Belum ada mutasi deposit untuk customer ini.</div>
        <?php endif; ?>

        <div style="margin-top: 12px;"><a class="btn btn-danger" href="<?= base_url('deposit_customer') ?>">Back</a></div>
    </div>
<?php endif; ?>
</div>
