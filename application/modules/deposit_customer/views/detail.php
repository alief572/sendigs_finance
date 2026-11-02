<?php
defined('BASEPATH') or exit('No direct script access allowed');
$e = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
?>

<style>
    .md-kv { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px 16px; font-size: 13px; margin-bottom: 6px; }
    .md-kv > div > span:first-child { display: block; color: #777; font-size: 12px; }
    .md-detail .r { text-align: right; }
    .md-detail .state-ok { color: #00a65a; font-weight: 700; }
    .md-detail .state-bad { color: #dd4b39; font-weight: 700; }
    .md-detail .muted { color: #777; }
    .md-bar { display: flex; justify-content: space-between; align-items: center; margin: 4px 0 8px; }
    .md-bar .section-t { font-weight: 700; }
    @media (max-width: 900px) { .md-kv { grid-template-columns: 1fr 1fr; } }
</style>

<h1 style="font-size: 24px; margin: 0 0 12px;">Monitoring Deposit</h1>

<?php if (!empty($results['not_found'])) : ?>
    <div class="box box-primary"><div class="box-body">Customer tidak ditemukan. <a href="<?= base_url('deposit_customer') ?>">Kembali</a></div></div>
<?php else :
    $id_customer = $results['id_customer'] ?? '';
    $nm_customer = $results['nm_customer'] ?? '';
    $summary = $results['summary'] ?? array();
    $mutasi = $results['mutasi'] ?? array();
    $canExport = function_exists('has_permission') ? has_permission('Deposit_Customer.Export') : true;
?>
    <ol class="breadcrumb" style="background: transparent; padding: 0; margin: 0 0 10px;">
        <li><a href="<?= base_url('deposit_customer') ?>">Monitoring Deposit</a> / Detail</li>
    </ol>

    <div class="box box-primary md-detail">
        <div class="box-body">
            <div class="md-kv">
                <div><span>Customer</span><?= $e($nm_customer) ?></div>
                <div><span>Total Nilai Deposit</span>Rp <?= mk_idn($summary['total_nilai'] ?? 0) ?></div>
                <div><span>Dari Kwitansi Deposit</span>Rp <?= mk_idn($summary['dari_kwitansi'] ?? 0) ?></div>
                <div><span>Dari Lebih Bayar</span>Rp <?= mk_idn($summary['dari_lebih_bayar'] ?? 0) ?></div>
                <div><span>Total Terpakai</span>Rp <?= mk_idn($summary['total_terpakai'] ?? 0) ?></div>
                <div><span>Sisa Saldo Deposit</span><span class="state-ok">Rp <?= mk_idn($summary['sisa_saldo'] ?? 0) ?></span></div>
            </div>

            <div class="md-bar">
                <div class="section-t">Mutasi Deposit</div>
                <?php if ($canExport) : ?>
                    <?php if (!empty($mutasi)) : ?>
                        <a class="btn btn-success btn-xs" href="<?= base_url('deposit_customer/export_excel?id_customer=' . urlencode($id_customer)) ?>">Export Excel</a>
                    <?php else : ?>
                        <button class="btn btn-success btn-xs" disabled>Export Excel</button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <?php if (!empty($mutasi)) : ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
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
                                    <td style="white-space: nowrap;"><?= $e(mk_fdate($m['tanggal'])) ?></td>
                                    <td style="white-space: nowrap;"><a href="<?= base_url('penerimaan_uang/view_surat?no=' . urlencode((string)$m['no_transaksi'])) ?>"><?= $e($m['no_transaksi']) ?></a></td>
                                    <td><?= $e(Deposit_customer_model::source_label($m)) ?></td>
                                    <td><?= $e($m['keterangan'] ?? '-') ?></td>
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
    </div>
<?php endif; ?>
