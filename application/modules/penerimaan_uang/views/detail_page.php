<?php
defined('BASEPATH') or exit('No direct script access allowed');
// Halaman detail penerimaan (mockup "viewPenerimaanResult"): header ringkas, tabel, jurnal, kartu deposit. Fragmen AJAX lama tetap di detail.php.
$e = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
$idn = function ($v) { return number_format((float)$v, 2, ',', '.'); };
$en = function ($v) { return number_format((float)$v, 2); };
$fdate = function ($d) { return $d ? date('d-F-Y', strtotime($d)) : '-'; };

$customer = !empty($header['nm_customer']) ? $header['nm_customer'] : (!empty($detail[0]['nm_customer']) ? $detail[0]['nm_customer'] : '-');
$bankName = !empty($alokasi['nm_bank']) ? $alokasi['nm_bank'] : (!empty($alokasi['nama_bank_acc']) ? $alokasi['nama_bank_acc'] : 'Bank');
$tglKoran = !empty($alokasi['tanggal_transaksi']) ? $alokasi['tanggal_transaksi'] : ($header['created_date'] ?? '');
$firstSnap = json_decode($detail[0]['consulting_snapshot'] ?? '', true);
if ((int)($firstSnap['calculation_version'] ?? 0) === 4 && !empty($firstSnap['date'])) $tglKoran = $firstSnap['date'];
$uangMasuk = (float)($header['nominal_penerimaan_bank'] ?? 0);
$pphDipotong = (($header['pph23_dipotong'] ?? '') === 'Y');

$isDeposit = (($header['jenis_penerimaan'] ?? '') === 'deposit');
foreach ($detail as $d) {
    $tt = $d['tax_treatment'] ?? '';
    if ($tt === '' && !empty($d['consulting_snapshot'])) { $s = json_decode($d['consulting_snapshot'], true); $tt = $s['tax_treatment'] ?? $s['type'] ?? ''; }
    if (strtoupper($tt) === 'DEP') { $isDeposit = true; break; }
}

// Satu baris per invoice. Piutang Dagang = saldo saat penerimaan: snapshot (baru) atau kredit piutang di jurnal (lama), bukan total invoice,
// supaya cicilan tidak tampil seolah-olah belum dibayar.
$rows = array(); $tot = array('piutang'=>0, 'pph'=>0, 'alok'=>0, 'dep'=>0, 'admin'=>0, 'lebih'=>0, 'sisa'=>0);
foreach ($detail as $d) {
    $snap = json_decode($d['consulting_snapshot'] ?? '', true);
    $isV4 = (int)($snap['calculation_version'] ?? 0) === 4;
    $noInv = (string)($d['no_invoice'] ?? '-');
    $piutang = (float)($d['total_akhir_jurnal'] ?? 0);
    if (!$isV4) {
        foreach ((array)($list_jurnal ?? array()) as $j) {
            if (($j['coa'] ?? '') === '1102-01-01' && $noInv !== '' && strpos((string)($j['keterangan'] ?? ''), ' - ' . $noInv) !== false) {
                $piutang = (float)$j['kredit'] + (float)$d['sisa_piutang'];
                break;
            }
        }
    }
    $r = array('no'=>$noInv, 'spk'=>(string)($d['id_spk_penawaran'] ?? ''), 'tax'=>Penerimaan_calculation::stored_tax_code($snap, $d['tax_treatment'] ?? ''),
        'pph'=>(float)($d['pph23'] ?? 0), 'piutang'=>$piutang, 'alok'=>(float)($d['penerimaan'] ?? 0), 'dep'=>(float)($d['dep'] ?? 0),
        'admin'=>(float)($d['biaya_admin'] ?? 0), 'lebih'=>(float)($d['lebih'] ?? 0), 'sisa'=>(float)($d['sisa_piutang'] ?? 0), 'v4'=>$isV4);
    foreach (array('piutang','pph','alok','dep','admin','lebih','sisa') as $k) $tot[$k] += $r[$k];
    $rows[] = $r;
}
// Kontrol: baris baru memakai rumus snapshot; invoice lama = uang kas + lebih bayar - uang masuk (sama dengan Kontrol saat modal proses),
// karena Piutang/PPh invoice lama tidak bisa dipakai langsung (neto PPh, cicilan, penyesuaian PPh).
$allV4 = !empty($rows) && count(array_filter($rows, function ($r) { return $r['v4']; })) === count($rows);
$kontrol = $allV4
    ? (($tot['piutang'] - $tot['pph'] - $tot['admin'] - $tot['sisa']) - $uangMasuk - $tot['dep']) + $tot['lebih']
    : ($tot['alok'] + $tot['lebih'] - $uangMasuk);
if ($isDeposit) $kontrol = ($tot['piutang'] - $tot['admin']) - $uangMasuk;

// Jurnal: baris bernilai nol disembunyikan (mockup).
$jurnal = array(); $jDebit = 0.0; $jKredit = 0.0;
foreach ((array)($list_jurnal ?? array()) as $j) {
    $dr = (float)($j['debit'] ?? 0); $cr = (float)($j['kredit'] ?? 0);
    $jDebit += $dr; $jKredit += $cr;
    if ($dr != 0 || $cr != 0) $jurnal[] = $j + array('dr'=>$dr, 'cr'=>$cr);
}

$showKartu = $isDeposit || $tot['dep'] > 0 || $tot['lebih'] > 0;
$kartu = array(); $running = 0.0;
if ($showKartu) {
    foreach ((array)($kartu_ledger ?? array()) as $m) {
        $in = $m['jenis_mutasi'] === 'IN' ? (float)$m['nominal'] : 0.0; $out = $m['jenis_mutasi'] === 'OUT' ? (float)$m['nominal'] : 0.0;
        $running += $in - $out;
        $kartu[] = array('tanggal'=>$m['tanggal'], 'ref'=>$m['no_transaksi'] ?? '-', 'ket'=>$m['keterangan'] ?? '-', 'masuk'=>$in, 'keluar'=>$out, 'saldo'=>$running);
    }
}
?>
<style>
    .rcp-kv { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px 16px; font-size: 13px; margin-bottom: 8px; }
    .rcp-kv > div > span:first-child { display: block; color: #777; font-size: 12px; }
    .rcp-state-ok { color: #00a65a; font-weight: 700; }
    .rcp-state-bad { color: #dd4b39; font-weight: 700; }
    .rcp-section { font-weight: 700; margin: 18px 0 8px; }
    .tax-badge { display: inline-block; padding: 1px 7px; border-radius: 3px; font-size: 11px; font-weight: 700; border: 1px solid; white-space: nowrap; }
    .tax-PKP { color: #2e6da4; border-color: #2e6da4; } .tax-WAPU { color: #8a5a00; border-color: #c28a00; } .tax-KB { color: #5b4a9e; border-color: #7d6cc0; } .tax-DEP { color: #0369a1; border-color: #0369a1; }
    @media (max-width: 900px) { .rcp-kv { grid-template-columns: 1fr 1fr; } }
</style>
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title" style="font-weight: 700;">Penerimaan Piutang</h3>
    </div>
    <div class="box-body">
        <div class="text-muted" style="font-size: 12px; margin-bottom: 14px;"><a href="<?= site_url('penerimaan_uang') ?>#tab_report">Report Penerimaan</a> / Detail</div>

        <div class="rcp-kv">
            <div><span>Customer</span><?= $e($customer) ?></div>
            <?php if ($isDeposit) : ?><div><span>Nomor Penerimaan</span><?= $e($header['no_surat'] ?? '-') ?></div><?php endif; ?>
            <div><span>Tanggal rekening koran</span><?= $e($fdate($tglKoran)) ?></div>
            <div><span>Bank</span><?= $e($bankName) ?></div>
            <div><span>Status</span><span class="label label-success">Used</span></div>
            <?php if ($isDeposit) : ?>
                <div><span>Jenis</span><span class="label label-info">Kwitansi Deposit</span></div>
                <div><span>Uang masuk</span>Rp <?= $idn($uangMasuk) ?></div>
                <div><span>Masuk ke deposit customer</span>Rp <?= $idn($tot['piutang']) ?></div>
            <?php else : ?>
                <div><span>PPh 23</span><?= $pphDipotong ? 'Dipotong' : 'Tidak dipotong' ?></div>
                <div><span>Uang masuk</span>Rp <?= $idn($uangMasuk) ?></div>
                <div><span>Total PPh 23</span>Rp <?= $idn($tot['pph']) ?></div>
                <div><span>Deposit dipakai</span>Rp <?= $idn($tot['dep']) ?></div>
                <div><span>Lebih bayar</span>Rp <?= $idn($tot['lebih']) ?></div>
            <?php endif; ?>
            <div><span>Kontrol</span><span class="<?= abs($kontrol) < 0.01 ? 'rcp-state-ok' : 'rcp-state-bad' ?>"><?= $idn($kontrol) ?></span></div>
        </div>

        <div class="table-responsive" style="margin-top: 8px;">
            <?php if ($isDeposit) : ?>
                <table class="table table-bordered table-striped table-hover" style="font-size: 13px;">
                    <thead><tr><th>Nomor Kwitansi</th><th>No SPK</th><th class="text-right">Nilai Kwitansi</th><th class="text-right">Alokasi Penerimaan</th><th class="text-right">Biaya Admin</th><th>Status Kwitansi</th></tr></thead>
                    <tbody>
                        <?php foreach ($rows as $r) : ?>
                            <tr><td><?= $e($r['no']) ?></td><td><?= $e($r['spk'] !== '' ? $r['spk'] : '-') ?></td><td class="text-right"><?= $en($r['piutang']) ?></td><td class="text-right"><?= $en($r['alok']) ?></td><td class="text-right"><?= $en($r['admin']) ?></td><td><span class="label label-success">Lunas</span></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <table class="table table-bordered table-striped table-hover" style="font-size: 13px;">
                    <thead><tr><th>Nomor Invoice</th><th>TAX Code</th><th class="text-right">PPH 23</th><th class="text-right">Piutang Dagang</th><th class="text-right">Alokasi Penerimaan</th><th class="text-right">Deposit Dipakai</th><th class="text-right">Biaya Admin</th><th class="text-right">Lebih Bayar</th><th class="text-right">Sisa Piutang</th><th>Status Invoice</th></tr></thead>
                    <tbody>
                        <?php foreach ($rows as $r) : ?>
                            <tr>
                                <td><?= $e($r['no']) ?></td>
                                <td><?= $r['tax'] !== '' ? '<span class="tax-badge tax-' . $e($r['tax']) . '">' . $e($r['tax']) . '</span>' : '<span class="text-muted">-</span>' ?></td>
                                <td class="text-right"><?= $en($r['pph']) ?></td>
                                <td class="text-right"><?= $en($r['piutang']) ?></td>
                                <td class="text-right"><?= $en($r['alok']) ?></td>
                                <td class="text-right"><?= $en($r['dep']) ?></td>
                                <td class="text-right"><?= $en($r['admin']) ?></td>
                                <td class="text-right"><?= $en($r['lebih']) ?></td>
                                <td class="text-right"><?= $en($r['sisa']) ?></td>
                                <td><?= $r['sisa'] > 0 ? '<span class="label label-warning">Sisa piutang</span>' : '<span class="label label-success">Lunas</span>' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="rcp-section"><?= $isDeposit ? 'Jurnal Penerimaan Piutang Kwitansi Deposit' : 'Jurnal Penerimaan Piutang' ?></div>
        <?php if (empty($jurnal)) : ?>
            <div class="text-muted">Belum ada baris jurnal untuk penerimaan ini.</div>
        <?php else : ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" style="font-size: 13px;">
                    <thead><tr><th>Tanggal Jurnal</th><th>COA</th><th>Nama Company</th><th>Nama Account</th><th>Deskripsi</th><th class="text-right">Debit</th><th class="text-right">Credit</th></tr></thead>
                    <tbody>
                        <?php foreach ($jurnal as $j) : ?>
                            <tr>
                                <td class="text-nowrap"><?= $e($fdate($j['tgl_jurnal'] ?? '')) ?></td>
                                <td><?= $e($j['coa'] ?? '') ?></td>
                                <td><?= $e($j['nm_company'] ?? '') ?></td>
                                <td><?= $e($j['nm_coa'] ?? '') ?></td>
                                <td><?= $e($j['keterangan'] ?? '') ?></td>
                                <td class="text-right"><?= $en($j['dr']) ?></td>
                                <td class="text-right"><?= $en($j['cr']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr><td colspan="5" class="text-center"><strong>Balancing</strong></td><td class="text-right"><strong><?= $en($jDebit) ?></strong></td><td class="text-right"><strong><?= $en($jKredit) ?></strong></td></tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php if ($showKartu) : ?>
            <div class="rcp-section">Kartu Deposit Customer (setelah penerimaan)</div>
            <?php if (empty($kartu)) : ?>
                <div class="text-muted">Belum ada mutasi deposit untuk customer ini.</div>
            <?php else : ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" style="font-size: 13px;">
                        <thead><tr><th>Tanggal</th><th>Referensi</th><th>Keterangan</th><th class="text-right">Masuk</th><th class="text-right">Keluar</th><th class="text-right">Saldo</th></tr></thead>
                        <tbody>
                            <?php foreach ($kartu as $k) : ?>
                                <tr>
                                    <td class="text-nowrap"><?= $e($fdate($k['tanggal'])) ?></td>
                                    <td class="text-nowrap"><a href="<?= site_url('penerimaan_uang/view_surat?no=' . urlencode((string)$k['ref'])) ?>"><?= $e($k['ref']) ?></a></td>
                                    <td><?= $e($k['ket']) ?></td>
                                    <td class="text-right"><?= $en($k['masuk']) ?></td>
                                    <td class="text-right"><?= $en($k['keluar']) ?></td>
                                    <td class="text-right"><?= $en($k['saldo']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div style="margin-top: 12px;"><a class="btn btn-danger" href="<?= site_url('penerimaan_uang') ?>#tab_report">Back</a></div>
    </div>
</div>
