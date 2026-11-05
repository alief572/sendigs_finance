<?php
/* Detail invoice Vuca, dalam tata letak yang sama dengan halaman Buat Invoice Vuca (add_invoice_vuca.php), hanya baca.
   Variabel: $data_invoice, $data_plan_tagih (baris plan tagih atau baris pengganti), $hasil_jurnal (baris <tr>), $total_debit, $total_kredit,
   $journals (baris jurnal tersimpan, untuk menampilkan akun penjualan). */
$escape = function ($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); };
$money = function ($value) { return number_format((float)$value, 2); };
$total_nominal = (float)$data_invoice->total_nominal;
$dpp_lain_lain = (float)$data_invoice->dpp_nilai_lain;
$pph = (float)$data_invoice->pph_jurnal;
$plan = $data_plan_tagih;
$tanggal = strtotime((string)$data_invoice->tanggal_invoice);
// akun penjualan = baris jurnal selain akun tetap Vuca (piutang, PPN, PPh)
$salesAccount = '-';
if (!empty($journals) && class_exists('Invoice_vuca')) {
    foreach ($journals as $line) {
        if (!in_array($line['coa'], Invoice_vuca::fixed_codes(), true) && trim((string)$line['coa']) !== '') { $salesAccount = trim($line['coa'] . ' - ' . $line['nm_coa'], ' -'); break; }
    }
}
?>
<link rel="stylesheet" href="<?= base_url('assets/css/invoicing.css?v='.filemtime(FCPATH.'assets/css/invoicing.css')) ?>">
<div class="box invoice-workspace consulting-invoice vuca-invoice">
    <div class="invoice-heading">
        <div><h2>Detail invoice Vuca</h2><p><?= $escape($data_invoice->no_invoice) ?> · SPK <?= $escape($data_invoice->id_spk_penawaran ?? '') ?></p></div>
        <div class="invoice-heading-actions"><a class="btn btn-default" href="<?= base_url('invoicing') ?>">Kembali ke daftar</a></div>
    </div>
    <div class="box-body">
        <section class="invoice-section" aria-labelledby="vuca-document-title">
            <div class="invoice-section-heading"><h3 id="vuca-document-title">Customer &amp; dokumen</h3><p>Identitas customer dan dokumen sesuai data invoice Vuca yang tersimpan.</p></div>
            <div class="invoice-document-grid">
                <div class="invoice-customer"><dl>
                    <dt>Kepada</dt><dd class="invoice-customer-name"><?= $escape($data_invoice->nm_customer) ?></dd>
                    <dt>Alamat</dt><dd><?= nl2br($escape($data_invoice->address)) ?></dd>
                    <dt>Ditujukan</dt><dd>Finance Dept.</dd>
                    <dt>No. SPK</dt><dd><?= $escape($data_invoice->id_spk_penawaran ?? '') ?></dd>
                </dl></div>
                <div class="invoice-fields">
                    <div class="form-group"><label for="vuca-date">Tanggal invoice</label><input id="vuca-date" class="form-control" value="<?= $tanggal ? $escape(date('d M Y', $tanggal)) : '' ?>" readonly></div>
                    <div class="form-group"><label for="vuca-number">Nomor invoice</label><input id="vuca-number" class="form-control" value="<?= $escape($data_invoice->no_invoice) ?>" readonly></div>
                    <div class="form-group"><label for="vuca-po">Nomor PO</label><input id="vuca-po" class="form-control" value="<?= $escape($data_invoice->no_po) ?>" readonly></div>
                    <div class="form-group"><label for="vuca-faktur">Nomor faktur</label><input id="vuca-faktur" class="form-control" value="<?= $escape($data_invoice->no_faktur ?? '') ?>" readonly></div>
                </div>
            </div>
        </section>

        <div class="invoice-financial-grid">
            <div class="vuca-billing-main">
                <section class="invoice-section" aria-labelledby="vuca-top-title">
                    <div class="invoice-section-heading"><h3 id="vuca-top-title">Rincian TOP</h3><p>Term of payment dan nominal penagihan invoice ini.</p></div>
                    <div class="invoice-table-scroll" role="region" aria-label="Rincian term of payment" tabindex="0"><table class="table table-bordered invoice-top-table">
                        <thead><tr><th scope="col">No.</th><th scope="col">Term of Payment</th><th scope="col">Persentase</th><th scope="col" class="text-right">Nominal (Rp)</th><th scope="col">Deskripsi</th></tr></thead>
                        <tbody><tr>
                            <td>1</td>
                            <td><?= $escape($plan->term_payment) ?></td>
                            <td><?= $plan->persen_payment === null ? '-' : $escape($money($plan->persen_payment)) . '%' ?></td>
                            <td class="text-right"><?= $money($plan->nominal_payment) ?></td>
                            <td><?= $escape($plan->desc_payment) ?></td>
                        </tr></tbody>
                    </table></div>
                </section>
                <section aria-labelledby="vuca-revenue-title">
                    <div class="invoice-section-heading"><h3 id="vuca-revenue-title">Akun penjualan</h3><p>Akun pendapatan yang dipakai pada jurnal invoice.</p></div>
                    <div class="invoice-customer"><?= $escape($salesAccount) ?></div>
                    <p class="invoice-help">Invoice Vuca tanpa PPN. PPh Final 0,5% mengurangi piutang awal invoice.</p>
                </section>
            </div>
            <section class="invoice-summary" aria-labelledby="vuca-summary-title">
                <h3 id="vuca-summary-title">Ringkasan tagihan <span class="invoice-help">(Rp)</span></h3>
                <table><tbody>
                    <tr><th scope="row">DPP<small>Nominal TOP</small></th><td class="text-right" data-total="sales"><?= $money($total_nominal) ?></td></tr>
                    <tr><th scope="row">DPP lain-lain<small>DPP × 11/12</small></th><td class="text-right" data-total="other"><?= $money($dpp_lain_lain) ?></td></tr>
                    <tr><th scope="row">PPh Final Disetor<small>DPP × 0,5%</small></th><td class="text-right" data-total="pph">- <?= $money($pph) ?></td></tr>
                    <tr class="invoice-grand-total"><th scope="row">Total akhir<small>DPP dikurangi PPh Final</small></th><td class="text-right" data-total="net"><?= $money($total_nominal - $pph) ?></td></tr>
                </tbody></table>
                <div class="invoice-receivable"><span>Piutang awal</span><strong><?= $money($total_nominal - $pph) ?></strong></div>
            </section>
        </div>

        <section class="invoice-section" aria-labelledby="vuca-journal-title">
            <div class="invoice-section-heading"><h3 id="vuca-journal-title">Jurnal invoice</h3><p>Rincian jurnal invoice sesuai data yang tersimpan.</p></div>
            <div class="invoice-table-scroll" role="region" aria-label="Jurnal invoice, geser untuk melihat seluruh kolom" tabindex="0"><table class="table table-striped invoice-journal-table">
                <thead><tr><th scope="col">Tanggal jurnal</th><th scope="col">COA</th><th scope="col">Company</th><th scope="col">Nama COA</th><th scope="col" class="text-right">Debit (Rp)</th><th scope="col" class="text-right">Kredit (Rp)</th></tr></thead>
                <tbody><?php if (trim((string)$hasil_jurnal) === ''): ?><tr><td colspan="6" class="invoice-empty">Rincian jurnal belum tersedia.</td></tr><?php else: ?><?= $hasil_jurnal ?><?php endif; ?></tbody>
                <tfoot><tr><th colspan="4" scope="row">Total debit / kredit</th><th class="text-right th_total_debit"><?= $money($total_debit) ?></th><th class="text-right th_total_kredit"><?= $money($total_kredit) ?></th></tr></tfoot>
            </table></div>
        </section>

        <div class="invoice-actions"><a class="btn btn-default" href="<?= base_url('invoicing') ?>">Kembali ke daftar</a></div>
    </div>
</div>
