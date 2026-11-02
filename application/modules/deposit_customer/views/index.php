<?php
defined('BASEPATH') or exit('No direct script access allowed');
$kpi = $results['kpi'] ?? array();
$customers = $results['customers'] ?? array();
$e = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };

$totJumlah = 0; $totKw = 0; $totLebih = 0; $totNilai = 0; $totTerpakai = 0;
foreach ($customers as $c) {
    $totJumlah += (int)$c['jumlah_deposit'];
    $totKw += (float)$c['total_dari_kwitansi'];
    $totLebih += (float)$c['total_dari_lebih_bayar'];
    $totNilai += (float)$c['total_nominal_awal'];
    $totTerpakai += (float)$c['total_nominal_terpakai'];
}
?>

<style>
    .md-gate { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; margin-bottom: 10px; }
    .md-gate > div { border: 1px solid #d2d6de; border-radius: 3px; padding: 8px 10px; background: #fff; }
    .md-gate span { display: block; font-size: 12px; color: #777; }
    .md-gate b { font-variant-numeric: tabular-nums; }
    .md-filters { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .md-filters label { margin: 0 6px 0 0; font-weight: 600; }
    .md-filters input[type=search] { min-width: 260px; }
    .md-table th { white-space: nowrap; }
    .md-table td.r, .md-table th.r { text-align: right; }
    .md-table td.c, .md-table th.c { text-align: center; }
    .md-table .state-ok { color: #00a65a; font-weight: 700; }
    .md-table .muted { color: #777; }
    .md-table tr.sum td { background: transparent !important; border-top: 1px solid #d2d6de; }
    .md-table tr.sum .k { text-align: right; color: #777; }
    .md-table .empty { text-align: center; color: #777; padding: 16px; }
</style>

<h1 style="font-size: 24px; margin: 0 0 12px;">Monitoring Deposit</h1>

<div class="md-gate">
    <div><span>Total Sisa Saldo Deposit</span><b>Rp <?= mk_idn($kpi['total_saldo'] ?? 0) ?></b></div>
    <div><span>Customer Punya Saldo</span><b><?= (int)($kpi['total_customer_aktif'] ?? 0) ?></b></div>
    <div><span>Jumlah Deposit Masuk</span><b><?= (int)($kpi['cnt_masuk'] ?? 0) ?></b></div>
    <div><span>Total Terpakai</span><b>Rp <?= mk_idn($kpi['total_keluar'] ?? 0) ?></b></div>
</div>

<div class="box box-primary">
    <div class="box-body">
        <div class="md-filters">
            <div>
                <label for="mq">Search:</label>
                <input type="search" id="mq" class="form-control input-sm" style="display: inline-block; width: auto;" placeholder="Cari nama customer">
            </div>
            <div style="margin-left: auto;">
                <a href="<?= base_url('deposit_customer/export_excel') ?>" class="btn btn-success">Export to Excel</a>
            </div>
        </div>

        <div class="table-responsive" style="margin-top: 12px;">
            <table class="table table-bordered md-table" id="table_deposit_summary">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Customer</th>
                        <th class="r">Jumlah Deposit</th>
                        <th class="r">Dari Kwitansi Deposit</th>
                        <th class="r">Dari Lebih Bayar</th>
                        <th class="r">Total Nilai Deposit</th>
                        <th class="r">Total Terpakai</th>
                        <th class="r">Total Sisa Saldo</th>
                        <th>Deposit Terakhir</th>
                        <th class="c">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $c) :
                        $sisa = (float)$c['sisa_saldo'];
                    ?>
                        <tr class="md-row" data-name="<?= $e(mb_strtolower((string)$c['nm_customer'])) ?>">
                            <td class="md-no"></td>
                            <td style="min-width: 180px;"><?= $e($c['nm_customer']) ?></td>
                            <td class="r"><?= (int)$c['jumlah_deposit'] ?></td>
                            <td class="r"><?= mk_en($c['total_dari_kwitansi']) ?></td>
                            <td class="r"><?= mk_en($c['total_dari_lebih_bayar']) ?></td>
                            <td class="r"><?= mk_en($c['total_nominal_awal']) ?></td>
                            <td class="r"><?= mk_en($c['total_nominal_terpakai']) ?></td>
                            <td class="r"><?= $sisa > 0.009 ? '<span class="state-ok">' . mk_en($sisa) . '</span>' : '<span class="muted">-</span>' ?></td>
                            <td style="white-space: nowrap;"><?= !empty($c['last_deposit_date']) ? $e(mk_fdate($c['last_deposit_date'])) : '-' ?></td>
                            <td class="c" style="white-space: nowrap;">
                                <a href="<?= base_url('deposit_customer/detail?id_customer=' . urlencode($c['id_customer'])) ?>" class="btn btn-primary btn-xs">Detail</a>
                                <a href="<?= base_url('deposit_customer/export_excel?id_customer=' . urlencode($c['id_customer'])) ?>" class="btn btn-info btn-xs">Excel</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="sum" id="md_total_row">
                        <td colspan="2" class="k"><b>Total</b></td>
                        <td class="r"><b><?= (int)$totJumlah ?></b></td>
                        <td class="r"><b><?= mk_en($totKw) ?></b></td>
                        <td class="r"><b><?= mk_en($totLebih) ?></b></td>
                        <td class="r"><b><?= mk_en($totNilai) ?></b></td>
                        <td class="r"><b><?= mk_en($totTerpakai) ?></b></td>
                        <td class="r"><b><?= mk_en($totNilai - $totTerpakai) ?></b></td>
                        <td colspan="2"></td>
                    </tr>
                    <tr id="md_empty_row" style="display: none;"><td colspan="10" class="empty">Customer tidak ditemukan.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Seperti mockup: pencarian langsung (nama customer saja); No. dihitung ulang, baris Total (seluruh customer) disembunyikan bila hasil kosong.
(function () {
    var input = document.getElementById('mq');
    var rows = Array.prototype.slice.call(document.querySelectorAll('#table_deposit_summary tr.md-row'));
    function render() {
        var words = input.value.toLowerCase().split(/\s+/).filter(Boolean);
        var shown = 0;
        rows.forEach(function (row) {
            var name = row.getAttribute('data-name') || '';
            var ok = words.every(function (w) { return name.indexOf(w) !== -1; });
            row.style.display = ok ? '' : 'none';
            if (ok) { shown++; row.querySelector('.md-no').textContent = shown; }
        });
        document.getElementById('md_total_row').style.display = shown ? '' : 'none';
        document.getElementById('md_empty_row').style.display = shown ? 'none' : '';
    }
    input.addEventListener('input', render);
    render();
})();
</script>
