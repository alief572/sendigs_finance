<?php
$escape = function ($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); };
if (!empty($error)) { echo '<div class="alert alert-danger" role="alert">'.$escape($error).'</div>'; return; }
$editable = in_array($mode,array('add','edit'),true);
$top = $snapshot['top'];
$role = $snapshot['sales_role'] ?? '';
$type = $invoice['tax_treatment'] ?? '';
$totals = $type ? Invoice_consulting::totals($invoice['total_nominal'],$type) : null;
$number = $invoice['no_invoice'];
?>
<link rel="stylesheet" href="<?= base_url('assets/css/invoicing.css?v='.filemtime(FCPATH.'assets/css/invoicing.css')) ?>">
<div class="box invoice-workspace consulting-invoice<?= $mode==='add'?' invoice-compact':'' ?>">
    <div class="invoice-heading">
        <div><h2><?= $mode==='add'?'Buat invoice konsultasi':($mode==='edit'?'Edit invoice konsultasi':'Detail invoice konsultasi') ?></h2><p><?= $escape($number) ?> · SPK <?= $escape($invoice['id_spk_penawaran']) ?></p></div>
        <div class="invoice-heading-actions"><a class="btn btn-default" href="<?= base_url('invoicing') ?>">Kembali ke daftar</a></div>
    </div>
    <div class="box-body">
        <form id="consulting-form" action="<?= base_url('invoicing/'.($mode==='edit'?'update_invoice':'save_invoice')) ?>" method="post">
            <?php if (isset($this->security)): ?><input type="hidden" name="<?= $escape($this->security->get_csrf_token_name()) ?>" value="<?= $escape($this->security->get_csrf_hash()) ?>"><?php endif; ?>
            <input type="hidden" name="id_detail_plan_tagih" value="<?= $escape($invoice['id_detail_plan_tagih']) ?>">
            <input type="hidden" name="id_invoicing" value="<?= $escape($invoice['id']??'') ?>">
            <input type="hidden" name="source_fingerprint" value="<?= $escape($source_fingerprint) ?>">
            <input type="hidden" name="invoice_fingerprint" value="<?= $escape($invoice_fingerprint) ?>">
            <section class="invoice-section" aria-labelledby="invoice-document-title">
                <div class="invoice-section-heading"><h3 id="invoice-document-title">Customer &amp; dokumen</h3><p><?= $editable?'Identitas customer mengikuti SPK. Lengkapi dokumen dan perlakuan PPN untuk invoice ini.':'Identitas customer dan dokumen invoice mengikuti data yang tersimpan.' ?></p></div>
                <div class="invoice-document-grid">
                    <div class="invoice-customer"><dl>
                        <dt>Kepada</dt><dd class="invoice-customer-name"><?= $escape($invoice['nm_customer']) ?></dd>
                        <dt>Alamat</dt><dd><?= nl2br($escape($invoice['address'])) ?></dd>
                        <dt>Ditujukan</dt><dd>Finance Dept.</dd>
                        <dt>No. SPK</dt><dd><?= $escape($invoice['id_spk_penawaran']) ?></dd>
                    </dl></div>
                    <div class="invoice-fields">
                        <div class="form-group"><label for="consulting-date">Tanggal invoice <span class="invoice-required" aria-hidden="true">*</span></label><input id="consulting-date" type="date" class="form-control" name="tanggal_invoice" value="<?= $escape($invoice['tanggal_invoice']) ?>" required <?= $editable?'':'readonly' ?>><p class="invoice-help">Tanggal jurnal mengikuti tanggal invoice.</p></div>
                        <div class="form-group"><label for="consulting-number">Nomor invoice</label><input id="consulting-number" class="form-control" value="<?= $escape($number) ?>" readonly><p class="invoice-help"><?= $mode==='add'?'Nomor ditetapkan saat invoice disimpan.':'Nomor invoice mengikuti dokumen tersimpan.' ?></p></div>
                        <div class="form-group"><label for="consulting-po">Nomor PO <span class="invoice-required" aria-hidden="true">*</span></label><input id="consulting-po" class="form-control" name="nomor_po" maxlength="100" value="<?= $escape($invoice['no_po']) ?>" placeholder="Masukkan nomor PO" required <?= $editable?'':'readonly' ?>></div>
                        <div class="form-group"><label for="consulting-faktur">Nomor faktur <span class="invoice-required" aria-hidden="true">*</span></label><input id="consulting-faktur" class="form-control" name="nomor_faktur" maxlength="100" value="<?= $escape($invoice['no_faktur']) ?>" placeholder="Masukkan nomor faktur" required <?= $editable?'':'readonly' ?>></div>
                        <div class="form-group invoice-field-wide"><label for="consulting-tax">Perlakuan PPN <span class="invoice-required" aria-hidden="true">*</span></label>
                            <select id="consulting-tax" name="tax_treatment" class="form-control" aria-describedby="consulting-tax-help" required <?= $editable?'':'disabled' ?>>
                                <option value="">Pilih perlakuan PPN</option>
                                <?php foreach(array('PKP'=>'PKP','WAPU'=>'WAPU','KB'=>'Kawasan Berikat') as $key=>$label): ?><option value="<?= $key ?>" <?= $type===$key?'selected':'' ?>><?= $label ?></option><?php endforeach; ?>
                            </select>
                            <p class="invoice-help" id="consulting-tax-help">Piutang PKP mencakup PPN. Piutang WAPU dan Kawasan Berikat sebesar penjualan.</p>
                        </div>
                    </div>
                </div>
            </section>
            <?php if ($mode==='add'): ?><div class="invoice-financial-grid"><div class="invoice-billing-main"><?php endif; ?>
            <section class="invoice-section" aria-labelledby="invoice-top-title">
                <div class="invoice-section-heading"><h3 id="invoice-top-title">Rincian TOP</h3><p>Satu TOP untuk satu invoice. Nominal mengikuti plan tagih dan tidak dapat diubah di sini.</p></div>
                <div class="invoice-table-scroll" role="region" aria-label="Rincian term of payment" tabindex="0"><table class="table table-bordered invoice-top-table"><thead><tr><th scope="col">No.</th><th scope="col">Term of Payment</th><th scope="col">Persentase</th><th scope="col" class="text-right">Nominal (Rp)</th><th scope="col">Deskripsi</th></tr></thead><tbody><tr>
                    <td>1</td><td><?= $escape($top['term_payment']) ?></td><td><?= $escape(number_format((float)$top['persen_payment'],2)) ?>%</td><td class="text-right"><?= number_format((float)$invoice['total_nominal'],2) ?></td><td><?= $escape($top['desc_payment']) ?></td>
                </tr></tbody></table></div>
            </section>
            <?php if ($mode!=='add'): ?><div class="invoice-financial-grid"><?php endif; ?>
                <section aria-labelledby="invoice-revenue-title">
                    <div class="invoice-section-heading"><h3 id="invoice-revenue-title">Akun penjualan</h3><p>Kategori penjualan menentukan akun pendapatan pada jurnal invoice.</p></div>
                    <?php if ($editable): ?><div class="form-group"><label for="consulting-sales">COA penjualan <span class="invoice-required" aria-hidden="true">*</span></label><select class="form-control" id="consulting-sales" name="sales_role" required><option value="">Pilih COA Penjualan</option>
                        <?php foreach(Invoice_consulting::revenues() as $key=>$label): ?><option value="<?= $key ?>" <?= $role===$key?'selected':'' ?>><?= $escape($snapshot['accounts'][$key]['code'].' - '.$label) ?></option><?php endforeach; ?>
                    </select></div><?php else: ?><div class="invoice-customer"><?= $escape(($snapshot['accounts'][$role]['code']??'').' - '.($snapshot['accounts'][$role]['name']??'')) ?></div><?php endif; ?>
                    <p class="invoice-help">PPh dicatat saat penerimaan sesuai bukti yang diinput. Nilainya tidak mengurangi piutang awal invoice ini.</p>
                </section>
                <?php if ($mode==='add'): ?></div><?php endif; ?>
                <section class="invoice-summary" aria-labelledby="invoice-summary-title">
                    <h3 id="invoice-summary-title">Ringkasan tagihan <span class="invoice-help">(Rp)</span></h3>
                    <table><tbody>
                        <?php foreach(array('sales'=>array('Penjualan','Nominal TOP'),'other'=>array('DPP','Penjualan × 11/12'),'ppn'=>array('PPN','DPP × 12%'),'gross'=>array('Total tagihan','Penjualan + PPN')) as $key=>$label): ?><tr class="<?= $key==='gross'?'invoice-grand-total':'' ?>"><th scope="row"><?= $label[0] ?><small><?= $label[1] ?></small></th><td class="text-right" data-total="<?= $key ?>"><?= $totals?number_format($totals[$key]/100,2):($key==='sales'?number_format((float)$invoice['total_nominal'],2):'-') ?></td></tr><?php endforeach; ?>
                    </tbody></table>
                    <div class="invoice-receivable"><span>Piutang awal</span><strong data-total="receivable"><?= $totals?number_format($totals['receivable']/100,2):'-' ?></strong></div>
                </section>
            </div>
            <section class="invoice-section" aria-labelledby="invoice-journal-title">
                <div class="invoice-section-heading"><h3 id="invoice-journal-title">Jurnal invoice</h3><p><?= $editable?'Periksa akun dan keseimbangan debit/kredit. Jurnal disimpan sebagai draf untuk diposting melalui modul Jurnal.':'Rincian jurnal invoice sesuai data yang tersimpan.' ?></p></div>
                <div class="invoice-table-scroll" role="region" aria-label="Jurnal invoice, geser untuk melihat seluruh kolom" tabindex="0"><table class="table table-striped invoice-journal-table"><thead><tr><th scope="col">Tanggal jurnal</th><th scope="col">Company</th><th scope="col">COA</th><th scope="col">Nama COA</th><th scope="col">Deskripsi</th><th scope="col" class="text-right">Debit (Rp)</th><th scope="col" class="text-right">Kredit (Rp)</th></tr></thead>
                    <tbody id="consulting-journals"><?php if (!$journals): ?><tr><td colspan="7" class="invoice-empty"><?= $editable?'Pilih perlakuan PPN dan COA penjualan untuk menampilkan jurnal.':'Rincian jurnal belum tersedia. Kembali ke daftar dan periksa invoice melalui modul Jurnal.' ?></td></tr><?php endif; ?><?php foreach($journals as $line): ?><tr><td><?= $escape($line['tgl_jurnal']) ?></td><td><?= $escape($line['nm_company']) ?></td><td><?= $escape($line['coa']) ?></td><td><?= $escape($line['nm_coa']) ?></td><td><?= $escape($line['keterangan']) ?></td><td class="text-right"><?= number_format((float)$line['debit'],2) ?></td><td class="text-right"><?= number_format((float)$line['kredit'],2) ?></td></tr><?php endforeach; ?></tbody>
                    <tfoot><tr><th colspan="5" scope="row">Total debit / kredit</th><th id="consulting-debit" class="text-right"><?= number_format(array_sum(array_column($journals,'debit')),2) ?></th><th id="consulting-credit" class="text-right"><?= number_format(array_sum(array_column($journals,'kredit')),2) ?></th></tr></tfoot>
                </table></div>
            </section>
            <p id="consulting-message" class="invoice-feedback" role="status" aria-live="polite"></p>
            <div class="invoice-actions consulting-actions"><?php if ($editable): ?><button type="submit" class="btn btn-primary" id="consulting-save">Simpan invoice</button><?php endif; ?><a class="btn btn-default" href="<?= base_url('invoicing') ?>">Kembali ke daftar</a></div>
        </form>
    </div>
</div>
<?php if ($editable): ?>
<link rel="stylesheet" href="<?= base_url('assets/dist/sweetalert2.min.css') ?>">
<script src="<?= base_url('assets/dist/sweetalert2.min.js') ?>"></script>
<script src="<?= base_url('assets/js/invoicing/save-feedback.js?v='.filemtime(FCPATH.'assets/js/invoicing/save-feedback.js')) ?>"></script>
<script src="<?= base_url('assets/js/invoicing/consulting.js?v='.filemtime(FCPATH.'assets/js/invoicing/consulting.js')) ?>"></script>
<script src="<?= base_url('assets/js/invoicing/consulting-form.js?v='.filemtime(FCPATH.'assets/js/invoicing/consulting-form.js')) ?>"></script>
<script>
ConsultingInvoiceForm.mount(document.getElementById('consulting-form'), <?= json_encode(array('amount'=>$invoice['total_nominal'],'accounts'=>$snapshot['accounts'],'company'=>$snapshot['owner']['nm_company'],'number'=>$number,'spk'=>$invoice['id_spk_penawaran'],'redirect'=>base_url('invoicing')),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>);
</script>
<?php endif; ?>
