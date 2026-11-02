<?php $h = $results['header']; $e = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }; ?>
<?= mk_ui_assets() ?>
<div class="mk">
<h1>Kwitansi Deposit</h1>
<div class="crumb"><a href="<?= site_url('kwitansi_deposit') ?>">Kwitansi Deposit</a> / Detail</div>

<div class="box primary">
    <div class="kv">
        <div><span>No Kwitansi</span><?= $e($h['no_kwitansi']) ?></div>
        <div><span>Tanggal</span><?= $e(mk_fdate($h['tanggal_kwitansi'])) ?></div>
        <div><span>Customer</span><?= $e($h['nm_customer']) ?></div>
        <div><span>No SPK</span><?= $e($h['no_spk'] ?? $h['id_spk_penawaran'] ?? '') ?></div>
        <div><span>Nilai Kwitansi</span><?= mk_en($h['nilai_kwitansi']) ?></div>
        <div><span>Status</span><?= $h['status'] === 'MENUNGGU_PEMBAYARAN' ? '<span class="lbl draft">Menunggu Pembayaran</span>' : '<span class="lbl lunas">Lunas</span>' ?></div>
        <div><span>No Penerimaan</span><?= !empty($h['no_penerimaan']) ? '<a href="' . site_url('penerimaan_uang/view_surat?no=' . urlencode($h['no_penerimaan'])) . '">' . $e($h['no_penerimaan']) . '</a>' : '-' ?></div>
        <div><span>Keterangan</span><?= $e($h['keterangan'] ?? '') ?></div>
    </div>

    <div class="section-t" style="margin-top: 12px;">Jurnal Kwitansi Deposit</div>
    <div class="scroll">
        <table>
            <thead><tr><th>Tanggal Jurnal</th><th>COA</th><th>Nama Company</th><th>Nama Account</th><th>Deskripsi</th><th class="r">Debit</th><th class="r">Credit</th></tr></thead>
            <tbody>
                <?php $dr = 0; $cr = 0; foreach ($results['journals'] as $j) : $dr += (float)$j['debit']; $cr += (float)$j['kredit']; ?>
                    <tr>
                        <td class="nowrap"><?= $e(mk_fdate($j['tgl_jurnal'])) ?></td>
                        <td><?= $e($j['coa']) ?></td>
                        <td><?= $e($j['nm_company'] ?? '') ?></td>
                        <td><?= $e($j['nm_coa']) ?></td>
                        <td><?= $e($j['keterangan']) ?></td>
                        <td class="r"><?= mk_en($j['debit']) ?></td>
                        <td class="r"><?= mk_en($j['kredit']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="sum"><td colspan="5" class="c"><b>Balancing</b></td><td class="r"><b><?= mk_en($dr) ?></b></td><td class="r"><b><?= mk_en($cr) ?></b></td></tr>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 12px;"><a class="btn btn-danger" href="<?= site_url('kwitansi_deposit') ?>">Back</a></div>
</div>
</div>
