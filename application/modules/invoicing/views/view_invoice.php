<?php
/* Detail invoice konsultasi lama (bukan alur konsultasi v3), dalam tata letak yang sama dengan halaman Buat Invoice (consulting_invoice.php),
   hanya baca. Variabel: $data_invoice, $data_actual_plan_tagih (baris plan tagih atau baris pengganti), $hasil_jurnal (baris <tr>), $total_debit, $total_kredit. */
$escape = function ($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); };
$money = function ($value) { return number_format((float)$value, 2); };
$total_nominal = (float)$data_invoice->total_nominal;
$dpp_lain_lain = (float)$data_invoice->dpp_nilai_lain;
$ppn = (float)$data_invoice->ppn_jurnal;
$pph = (float)$data_invoice->pph_jurnal;
$ppn_persen = isset($data_invoice->ppn_persen) ? $data_invoice->ppn_persen : 12;
$plan = $data_actual_plan_tagih;
$tanggal = strtotime((string)$data_invoice->tanggal_invoice);
?>
<link rel="stylesheet" href="<?= base_url('assets/css/invoicing.css?v='.filemtime(FCPATH.'assets/css/invoicing.css')) ?>">
<div class="box invoice-workspace consulting-invoice">
    <div class="invoice-heading">
        <div><h2>Detail invoice konsultasi</h2><p><?= $escape($data_invoice->no_invoice) ?> · SPK <?= $escape($data_invoice->id_spk_penawaran ?? '') ?></p></div>
        <div class="invoice-heading-actions"><a class="btn btn-default" href="<?= base_url('invoicing') ?>">Kembali ke daftar</a></div>
    </div>
    <div class="box-body">
        <section class="invoice-section" aria-labelledby="invoice-document-title">
            <div class="invoice-section-heading"><h3 id="invoice-document-title">Customer &amp; dokumen</h3><p>Identitas customer dan dokumen sesuai data invoice yang tersimpan.</p></div>
            <div class="invoice-document-grid">
                <div class="invoice-customer"><dl>
                    <dt>Kepada</dt><dd class="invoice-customer-name"><?= $escape($data_invoice->nm_customer) ?></dd>
                    <dt>Alamat</dt><dd><?= nl2br($escape($data_invoice->address)) ?></dd>
                    <dt>Ditujukan</dt><dd>Finance Dept.</dd>
                    <dt>No. SPK</dt><dd><?= $escape($data_invoice->id_spk_penawaran ?? '') ?></dd>
                </dl></div>
                <div class="invoice-fields">
                    <div class="form-group"><label for="view-date">Tanggal invoice</label><input id="view-date" class="form-control" value="<?= $tanggal ? $escape(date('d M Y', $tanggal)) : '' ?>" readonly></div>
                    <div class="form-group"><label for="view-number">Nomor invoice</label><input id="view-number" class="form-control" value="<?= $escape($data_invoice->no_invoice) ?>" readonly></div>
                    <div class="form-group"><label for="view-po">Nomor PO</label><input id="view-po" class="form-control" value="<?= $escape($data_invoice->no_po) ?>" readonly></div>
                    <div class="form-group"><label for="view-faktur">Nomor faktur</label><input id="view-faktur" class="form-control" value="<?= $escape($data_invoice->no_faktur ?? '') ?>" readonly></div>
                    <div class="form-group invoice-field-wide"><label for="view-ppn">PPN</label><input id="view-ppn" class="form-control" value="<?= $escape($ppn_persen) ?>%" readonly></div>
                </div>
            </div>
        </section>

        <div class="invoice-financial-grid">
            <div class="invoice-billing-main">
                <section class="invoice-section" aria-labelledby="invoice-top-title">
                    <div class="invoice-section-heading"><h3 id="invoice-top-title">Rincian TOP</h3><p>Term of payment dan nominal penagihan invoice ini.</p></div>
                    <div class="invoice-table-scroll" role="region" aria-label="Rincian term of payment" tabindex="0"><table class="table table-bordered invoice-top-table">
                        <thead><tr><th scope="col" class="text-center">No.</th><th scope="col">Term of Payment</th><th scope="col" class="text-center">Persentase</th><th scope="col" class="text-right">Nominal (Rp)</th><th scope="col">Description</th></tr></thead>
                        <tbody><tr>
                            <td class="text-center">1</td>
                            <td class="text-left"><?= $escape($plan->term_payment) ?></td>
                            <td class="text-center"><?= $plan->persen_payment === null ? '-' : $escape($money($plan->persen_payment)) . '%' ?></td>
                            <td class="text-right"><?= $money($plan->nominal_payment) ?></td>
                            <td class="text-left"><?= $escape($plan->desc_payment) ?></td>
                        </tr></tbody>
                    </table></div>
                </section>
            </div>
            <section class="invoice-summary" aria-labelledby="invoice-summary-title">
                <h3 id="invoice-summary-title">Ringkasan tagihan <span class="invoice-help">(Rp)</span></h3>
                <table><tbody>
                    <tr><th scope="row">DPP<small>Nominal TOP</small></th><td class="text-right" data-total="sales"><?= $money($total_nominal) ?></td></tr>
                    <tr><th scope="row">DPP Lain-lain<small>Penjualan × 11/12</small></th><td class="text-right" data-total="other"><?= $money($dpp_lain_lain) ?></td></tr>
                    <tr><th scope="row">PPN <?= $escape($ppn_persen) ?>%<small>dari DPP Lain</small></th><td class="text-right" data-total="ppn"><?= $money($ppn) ?></td></tr>
                    <tr class="invoice-grand-total"><th scope="row">Total tagihan + PPN<small>Penjualan + PPN</small></th><td class="text-right" data-total="gross"><?= $money($total_nominal + $ppn) ?></td></tr>
                    <tr><th scope="row">PPh 23<small>Dipotong dari tagihan</small></th><td class="text-right" data-total="pph"><span style="color:#ad3333">- <?= $money($pph) ?></span></td></tr>
                </tbody></table>
                <div class="invoice-receivable"><span>Total akhir</span><strong data-total="net"><?= $money($total_nominal + $ppn - $pph) ?></strong></div>
            </section>
        </div>

        <section class="invoice-section" aria-labelledby="invoice-journal-title">
            <div class="invoice-section-heading"><h3 id="invoice-journal-title">Jurnal invoice</h3><p>Rincian jurnal invoice sesuai data yang tersimpan.</p></div>
            <div class="invoice-table-scroll" role="region" aria-label="Jurnal invoice, geser untuk melihat seluruh kolom" tabindex="0"><table class="table table-striped invoice-journal-table">
                <thead><tr><th scope="col">Tanggal jurnal</th><th scope="col">COA</th><th scope="col">Company</th><th scope="col">Nama COA</th><th scope="col" class="text-right">Debit (Rp)</th><th scope="col" class="text-right">Kredit (Rp)</th></tr></thead>
                <tbody><?php if (trim((string)$hasil_jurnal) === ''): ?><tr><td colspan="6" class="invoice-empty">Rincian jurnal belum tersedia.</td></tr><?php else: ?><?= $hasil_jurnal ?><?php endif; ?></tbody>
                <tfoot><tr><th colspan="4" scope="row">Balancing (total debit / kredit)</th><th class="text-right th_total_debit"><?= number_format((float)$total_debit) ?></th><th class="text-right th_total_kredit"><?= number_format((float)$total_kredit) ?></th></tr></tfoot>
            </table></div>
        </section>

        <div class="invoice-actions"><a class="btn btn-default" href="<?= base_url('invoicing') ?>">Kembali ke daftar</a></div>
    </div>
</div>
