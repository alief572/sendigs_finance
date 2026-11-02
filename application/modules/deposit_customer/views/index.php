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
<?= mk_ui_assets() ?>
<div class="mk">
<h1>Monitoring Deposit</h1>

<div class="gate">
    <div><span>Total Sisa Saldo Deposit</span><b>Rp <?= mk_idn($kpi['total_saldo'] ?? 0) ?></b></div>
    <div><span>Customer Punya Saldo</span><b><?= (int)($kpi['total_customer_aktif'] ?? 0) ?></b></div>
    <div><span>Jumlah Deposit Masuk</span><b><?= (int)($kpi['cnt_masuk'] ?? 0) ?></b></div>
    <div><span>Total Terpakai</span><b>Rp <?= mk_idn($kpi['total_keluar'] ?? 0) ?></b></div>
</div>

<div class="box primary">
    <div class="filters">
        <div class="f">
            <label for="mq">Search:</label>
            <input type="search" id="mq" placeholder="Cari nama customer">
        </div>
        <div class="f" style="margin-left: auto;">
            <a href="<?= base_url('deposit_customer/export_excel') ?>" class="btn btn-success">Export to Excel</a>
        </div>
    </div>

    <div class="scroll" style="margin-top: 12px;">
        <table id="table_deposit_summary">
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
                    <tr class="md-row" data-name="<?= $e($c['nm_customer']) ?>">
                        <td class="md-no"></td>
                        <td style="min-width: 180px;"><?= $e($c['nm_customer']) ?></td>
                        <td class="r"><?= (int)$c['jumlah_deposit'] ?></td>
                        <td class="r"><?= mk_en($c['total_dari_kwitansi']) ?></td>
                        <td class="r"><?= mk_en($c['total_dari_lebih_bayar']) ?></td>
                        <td class="r"><?= mk_en($c['total_nominal_awal']) ?></td>
                        <td class="r"><?= mk_en($c['total_nominal_terpakai']) ?></td>
                        <td class="r"><?= $sisa > 0.009 ? '<span class="state-ok">' . mk_en($sisa) . '</span>' : '<span class="muted">-</span>' ?></td>
                        <td class="nowrap"><?= !empty($c['last_deposit_date']) ? $e(mk_fdate($c['last_deposit_date'])) : '-' ?></td>
                        <td class="c">
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
// Pencarian langsung (M:1177, matchQ pada nama). Teks pencarian dipertahankan di sessionStorage selama tab terbuka (variabel MQ mockup).
(function () {
    var input = document.getElementById('mq');
    try { input.value = sessionStorage.getItem('md_q') || ''; } catch (err) {}
    var rows = Array.prototype.slice.call(document.querySelectorAll('#table_deposit_summary tr.md-row'));
    function render() {
        var shown = 0;
        rows.forEach(function (row) {
            var ok = MockupUI.matchQ(input.value, MockupUI.hayOf([row.getAttribute('data-name')]));
            row.style.display = ok ? '' : 'none';
            if (ok) { shown++; row.querySelector('.md-no').textContent = shown; }
        });
        document.getElementById('md_total_row').style.display = shown ? '' : 'none';
        document.getElementById('md_empty_row').style.display = shown ? 'none' : '';
    }
    input.addEventListener('input', function () { try { sessionStorage.setItem('md_q', input.value); } catch (err) {} render(); });
    render();
})();
</script>
