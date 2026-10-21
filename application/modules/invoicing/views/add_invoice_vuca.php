<?php
$escape=function($value){return htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');};
if (!empty($error)) { ?>
    <div class="alert alert-danger" role="alert"><?= $escape($error) ?></div>
    <a href="<?= base_url('invoicing') ?>" class="btn btn-default">Kembali ke daftar</a>
<?php return; }
$total_nominal=(float)$data_actual->nominal_payment;
$dpp_nilai_lain=$total_nominal*11/12;
$pajak=$dpp_nilai_lain*12/100;
$total_akhir=$total_nominal+$pajak;
$pph=$total_nominal*0.5/100;
$total_akhir_jurnal=$total_nominal-$pph;
$legacyFields=array('id'=>$data_actual->id,'total_nominal'=>$total_nominal,'dpp_nilai_lain'=>$dpp_nilai_lain,
    'pajak'=>$pajak,'total_akhir'=>$total_akhir,'total_nominal_jurnal'=>$total_nominal,'dpp_lain_lain'=>$dpp_nilai_lain,
    'pph_jurnal'=>$pph,'total_akhir_jurnal'=>$total_akhir_jurnal);
?>
<link rel="stylesheet" href="<?= base_url('assets/css/invoicing.css?v='.filemtime(FCPATH.'assets/css/invoicing.css')) ?>">
<div class="box invoice-workspace consulting-invoice vuca-invoice">
    <div class="invoice-heading">
        <div><h2>Buat invoice Vuca</h2><p><?= $escape($preview_no_invoice) ?> · SPK <?= $escape($data_actual->id_spk_penawaran) ?></p></div>
        <div class="invoice-heading-actions"><a class="btn btn-default" href="<?= base_url('invoicing') ?>">Kembali ke daftar</a></div>
    </div>
    <div class="box-body">
        <form id="vuca-form" action="<?= base_url('invoicing/save_invoice_vuca') ?>" method="post">
            <?php if (isset($this->security)): ?><input type="hidden" name="<?= $escape($this->security->get_csrf_token_name()) ?>" value="<?= $escape($this->security->get_csrf_hash()) ?>"><?php endif; ?>
            <?php foreach($legacyFields as $name=>$value): ?><input type="hidden" name="<?= $name ?>" value="<?= $escape($value) ?>"><?php endforeach; ?>
            <section class="invoice-section" aria-labelledby="vuca-document-title">
                <div class="invoice-section-heading"><h3 id="vuca-document-title">Customer &amp; dokumen</h3><p>Identitas customer mengikuti SPK. Lengkapi dokumen untuk invoice Vuca ini.</p></div>
                <div class="invoice-document-grid">
                    <div class="invoice-customer"><dl>
                        <dt>Kepada</dt><dd class="invoice-customer-name"><?= $escape($data_actual->nm_customer) ?></dd>
                        <dt>Alamat</dt><dd><?= nl2br($escape($data_actual->address)) ?></dd>
                        <dt>Ditujukan</dt><dd>Finance Dept.</dd>
                        <dt>No. SPK</dt><dd><?= $escape($data_actual->id_spk_penawaran) ?></dd>
                    </dl></div>
                    <div class="invoice-fields">
                        <div class="form-group"><label for="vuca-date">Tanggal invoice</label><input id="vuca-date" type="date" class="form-control" name="tanggal_invoice" value="<?= date('Y-m-d') ?>" readonly required><p class="invoice-help">Tanggal jurnal mengikuti tanggal invoice.</p></div>
                        <div class="form-group"><label for="vuca-number">Nomor invoice</label><input id="vuca-number" class="form-control" name="nomor_invoice" value="<?= $escape($preview_no_invoice) ?>" readonly><p class="invoice-help">Nomor ditetapkan saat invoice disimpan.</p></div>
                        <?php foreach(array('po'=>'PO','faktur'=>'faktur') as $key=>$label): ?>
                        <div class="form-group"><label for="vuca-<?= $key ?>">Nomor <?= $label ?> <span class="invoice-required" aria-hidden="true">*</span></label><input id="vuca-<?= $key ?>" class="form-control" name="nomor_<?= $key ?>" maxlength="100" placeholder="Masukkan nomor <?= $label ?>" required></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
            <div class="invoice-financial-grid">
                <div class="vuca-billing-main">
            <section class="invoice-section" aria-labelledby="vuca-top-title">
                <div class="invoice-section-heading"><h3 id="vuca-top-title">Rincian TOP</h3><p>Satu TOP untuk satu invoice. Nominal mengikuti plan tagih dan tidak dapat diubah di sini.</p></div>
                <div class="invoice-table-scroll" role="region" aria-label="Rincian term of payment" tabindex="0"><table class="table table-bordered invoice-top-table">
                    <thead><tr><th scope="col">No.</th><th scope="col">Term of Payment</th><th scope="col">Persentase</th><th scope="col" class="text-right">Nominal (Rp)</th><th scope="col">Deskripsi</th></tr></thead>
                    <tbody><tr><td>1</td><td><?= $escape($data_actual->term_payment) ?></td><td><?= number_format((float)$data_actual->persen_payment,2) ?>%</td><td class="text-right"><?= number_format($total_nominal,2) ?></td><td><?= $escape($data_actual->desc_payment) ?></td></tr></tbody>
                </table></div>
            </section>
                <section aria-labelledby="vuca-revenue-title">
                    <div class="invoice-section-heading"><h3 id="vuca-revenue-title">Akun penjualan</h3><p>Kategori penjualan menentukan akun pendapatan pada jurnal invoice.</p></div>
                    <div class="form-group"><label for="vuca-sales">COA penjualan <span class="invoice-required" aria-hidden="true">*</span></label>
                        <select class="form-control" id="vuca-sales" name="sales_role" aria-describedby="vuca-sales-help" required>
                            <option value="">Pilih COA Penjualan</option>
                            <?php foreach($accounts as $role=>$account): ?><option value="<?= $escape($role) ?>"><?= $escape($account['code'].' - '.$account['label']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <p id="vuca-sales-help" class="invoice-help">Invoice Vuca tanpa PPN. PPh Final 0,5% mengurangi piutang awal invoice.</p>
                </section>
                </div>
                <section class="invoice-summary" aria-labelledby="vuca-summary-title">
                    <h3 id="vuca-summary-title">Ringkasan tagihan <span class="invoice-help">(Rp)</span></h3>
                    <table><tbody>
                        <tr><th scope="row">DPP<small>Nominal TOP</small></th><td class="text-right" data-total="sales"><?= number_format($total_nominal,2) ?></td></tr>
                        <tr><th scope="row">DPP lain-lain<small>DPP × 11/12</small></th><td class="text-right" data-total="other"><?= number_format($dpp_nilai_lain,2) ?></td></tr>
                        <tr><th scope="row">PPh Final Disetor<small>DPP × 0,5%</small></th><td class="text-right" data-total="pph">- <?= number_format($pph,2) ?></td></tr>
                        <tr class="invoice-grand-total"><th scope="row">Total akhir<small>DPP dikurangi PPh Final</small></th><td class="text-right" data-total="net"><?= number_format($total_akhir_jurnal,2) ?></td></tr>
                    </tbody></table>
                    <div class="invoice-receivable"><span>Piutang awal</span><strong><?= number_format($total_akhir_jurnal,2) ?></strong></div>
                </section>
            </div>
            <section class="invoice-section" aria-labelledby="vuca-journal-title">
                <div class="invoice-section-heading"><h3 id="vuca-journal-title">Jurnal invoice</h3><p>Periksa akun dan keseimbangan debit/kredit. Jurnal disimpan sebagai draf untuk diposting melalui modul Jurnal.</p></div>
                <div class="invoice-table-scroll" role="region" aria-label="Jurnal invoice, geser untuk melihat seluruh kolom" tabindex="0"><table class="table table-striped invoice-journal-table">
                    <thead><tr><th scope="col">Tanggal jurnal</th><th scope="col">COA</th><th scope="col">Company</th><th scope="col">Nama COA</th><th scope="col" class="text-right">Debit (Rp)</th><th scope="col" class="text-right">Kredit (Rp)</th></tr></thead>
                    <tbody id="vuca-journal-empty"><tr><td colspan="6" class="invoice-empty">Pilih COA penjualan untuk menampilkan jurnal.</td></tr></tbody>
                    <tbody id="vuca-journals" hidden><?php include __DIR__.'/vuca_journal_rows.php'; ?></tbody>
                    <tfoot><tr><th colspan="4" scope="row">Total debit / kredit</th><th id="vuca-debit" class="text-right">0.00</th><th id="vuca-credit" class="text-right">0.00</th></tr></tfoot>
                </table></div>
            </section>
            <p id="vuca-message" class="invoice-feedback" role="status" aria-live="polite"></p>
            <noscript><p class="alert alert-warning">Aktifkan JavaScript untuk menampilkan jurnal dan menyimpan invoice.</p></noscript>
            <div class="invoice-actions consulting-actions"><button type="submit" class="btn btn-primary" id="vuca-save" disabled>Simpan invoice</button><a class="btn btn-default" href="<?= base_url('invoicing') ?>">Kembali ke daftar</a></div>
        </form>
    </div>
</div>
<link rel="stylesheet" href="<?= base_url('assets/dist/sweetalert2.min.css') ?>">
<script src="<?= base_url('assets/dist/sweetalert2.min.js') ?>"></script>
<script src="<?= base_url('assets/js/invoicing/save-feedback.js?v='.filemtime(FCPATH.'assets/js/invoicing/save-feedback.js')) ?>"></script>
<script src="<?= base_url('assets/js/invoicing/vuca-form.js?v='.filemtime(FCPATH.'assets/js/invoicing/vuca-form.js')) ?>"></script>
<script>
VucaInvoiceForm.mount(document.getElementById('vuca-form'), <?= json_encode(array('accounts'=>$accounts,'total'=>number_format($total_nominal,2),'redirect'=>base_url('invoicing')),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>);
</script>
