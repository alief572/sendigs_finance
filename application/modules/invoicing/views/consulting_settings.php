<?php
$escape=function($value){return htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');};
if (!empty($error)) { echo '<div class="alert alert-danger" role="alert">'.$escape($error).'</div>'; return; }
$codes=json_decode($mapping['accounts']??'{}',true);
$companyNames=array_column($companies,'nm_company','id');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/select2/select2.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/invoicing.css?v='.filemtime(FCPATH.'assets/css/invoicing.css')) ?>">
<div class="box invoice-workspace" id="consulting-coa-settings">
    <div class="invoice-heading">
        <div><h2>Pengaturan COA invoicing</h2><p>Tentukan company pemilik jurnal dan akun yang dipakai untuk invoice konsultasi baru.</p></div>
        <div class="invoice-heading-actions"><a class="btn btn-default" href="<?= base_url('invoicing') ?>">Kembali ke daftar</a></div>
    </div>
    <div class="box-body">
        <form method="get" action="<?= base_url('invoicing/coa_settings') ?>">
            <div class="invoice-toolbar">
                <?php foreach(array('source_company_id'=>'Company SPK','owner_company_id'=>'Company pemilik jurnal') as $field=>$label): ?>
                <div class="invoice-filter" style="flex-grow:1"><label for="<?= $field ?>"><?= $label ?></label><select class="form-control" id="<?= $field ?>" name="<?= $field ?>" required><option value="">Pilih company</option><?php foreach(($field==='source_company_id'?$companies:$journal_owners) as $company): ?><option value="<?= $escape($company['id']) ?>" <?= (string)$company['id']===(string)($field==='source_company_id'?$source_id:$owner_id)?'selected':'' ?>><?= $escape($company['nm_company']) ?></option><?php endforeach; ?></select></div>
                <?php endforeach; ?>
                <div class="invoice-toolbar-actions"><button class="btn btn-primary" type="submit">Muat COA</button></div>
            </div>
        </form>
        <p class="invoice-help">Company SPK adalah company pada SPK/invoice. Pemilik jurnal hanya STM, Sustain, atau Vuca dan menentukan sumber COA serta tujuan posting. Untuk company gabungan, gunakan company depan pada SPK.</p>
        <?php if (!empty($owner_warning)): ?><div class="invoice-feedback" data-state="error" role="alert"><?= $escape($owner_warning) ?></div><?php endif; ?>
        <?php if ($source_id && $owner_id): ?>
        <div class="invoice-section-heading" style="margin-top:28px"><h3>Mapping <?= $escape($companyNames[$source_id]??$source_id) ?></h3><p>Tujuan jurnal: <?= $escape($companyNames[$owner_id]??$owner_id) ?>. Perubahan ini berlaku untuk invoice baru; invoice tersimpan menggunakan akun saat diterbitkan.</p></div>
        <?php if (!$master): ?><div class="invoice-feedback" data-state="error" role="alert">Master COA company tujuan belum tersedia. Periksa company pemilik jurnal lalu tekan Muat COA kembali.</div><?php endif; ?>
        <form method="post" id="consulting-settings" action="<?= base_url('invoicing/save_coa_mapping') ?>">
            <?php if(isset($this->security)): ?><input type="hidden" name="<?= $escape($this->security->get_csrf_token_name()) ?>" value="<?= $escape($this->security->get_csrf_hash()) ?>"><?php endif; ?>
            <input type="hidden" name="source_company_id" value="<?= $escape($source_id) ?>"><input type="hidden" name="owner_company_id" value="<?= $escape($owner_id) ?>"><input type="hidden" name="mapping_fingerprint" value="<?= $escape($mapping_fingerprint) ?>">
            <div class="invoice-mapping-groups">
                <?php foreach(array('Piutang & pajak'=>array('receivable','vat_domestic','vat_wapu_prepaid','vat_wapu_payable','pph'),'Kategori penjualan'=>array('sales_consulting','sales_inhouse','sales_public','sales_hse')) as $group=>$groupRoles): ?>
                <fieldset><legend><?= $escape($group) ?></legend>
                    <?php foreach($groupRoles as $role): ?><div class="form-group"><label for="coa-<?= $role ?>"><?= $escape($roles[$role]) ?> <span class="invoice-required" aria-hidden="true">*</span></label><select class="form-control" id="coa-<?= $role ?>" name="accounts[<?= $role ?>]" required><option value="">Pilih COA</option><?php foreach($master as $account): ?><option value="<?= $escape($account['no_perkiraan']) ?>" <?= ($codes[$role]??'')===$account['no_perkiraan']?'selected':'' ?>><?= $escape($account['no_perkiraan'].' - '.$account['nama']) ?></option><?php endforeach; ?></select></div><?php endforeach; ?>
                </fieldset>
                <?php endforeach; ?>
            </div>
            <p class="invoice-help">Seluruh akun wajib diisi. Ketik kode atau nama akun di dropdown untuk mempercepat pencarian.</p>
            <p id="mapping-status" class="invoice-feedback" role="status" aria-live="polite"></p>
            <div class="invoice-actions"><button class="btn btn-primary" type="submit" <?= !$master?'disabled':'' ?>>Simpan mapping</button><a class="btn btn-default" href="<?= base_url('invoicing') ?>">Kembali ke daftar</a></div>
        </form>
        <?php else: ?>
        <div class="invoice-feedback" role="status">Pilih Company SPK dan company pemilik jurnal, lalu tekan Muat COA untuk mengatur akun invoicing.</div>
        <?php endif; ?>
    </div>
</div>
<script src="<?= base_url('assets/plugins/select2/select2.min.js') ?>"></script>
<link rel="stylesheet" href="<?= base_url('assets/dist/sweetalert2.min.css') ?>">
<script src="<?= base_url('assets/dist/sweetalert2.min.js') ?>"></script>
<script src="<?= base_url('assets/js/invoicing/save-feedback.js?v='.filemtime(FCPATH.'assets/js/invoicing/save-feedback.js')) ?>"></script>
<script>
$(function () {
    $('#consulting-coa-settings select').select2({
        width: '100%',
        minimumResultsForSearch: 0,
        language: { noResults: function () { return 'Tidak ada hasil yang cocok'; } }
    });
    $('#consulting-settings').on('submit', function (event) {
        event.preventDefault();
        var form = this;
        InvoiceSaveFeedback.submit({form: form, button: form.querySelector('button[type=submit]'),
            message: document.getElementById('mapping-status'),
            request: function () { return $.ajax({url: form.action, type: 'POST', data: $(form).serialize(), dataType: 'json'}); },
            successText: 'Mapping COA berhasil disimpan.',
            unconfirmedText: 'Penyimpanan tidak dapat dikonfirmasi. Muat ulang pengaturan sebelum mencoba kembali.',
            onSuccess: function () { window.location.reload(); }
        });
    });
});
</script>
