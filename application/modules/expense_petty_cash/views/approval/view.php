<?php

/**
 * Approval Pelaporan Petty Cash (mockup specs/docs/petty-cash, mode approval).
 * Ringkasan, Kontrol Saldo, Detail Pencatatan (item + evidence), Preview Jurnal gabungan, tombol Approve/Reject.
 *
 * PHP vars:
 *   $pelaporan, $pencatatan_details (pencatatan_id => detail item + evidences), $budget_info,
 *   $coa_names, $coa_kas, $journal_preview, $actual_journals
 */
require_once __DIR__ . '/../partials/pc_review.php';

$header = isset($pelaporan->header) ? $pelaporan->header : $pelaporan;
$pencatatan_list = isset($pelaporan->pencatatan_list) ? $pelaporan->pencatatan_list : [];

// Kontrol saldo diisi saat Ajukan; pelaporan lama (sebelum fitur ini) tidak punya nilainya.
$ada_kontrol_saldo = isset($header->saldo_petty_cash) && $header->saldo_petty_cash !== null;
$selisih_saldo     = $ada_kontrol_saldo && isset($header->selisih_saldo) ? (int) $header->selisih_saldo : null;

pc_style();
pc_evidence_assets();
pc_summary($header, count($pencatatan_list), isset($budget_info->sisa_budget) ? $budget_info->sisa_budget : 0);
if ($ada_kontrol_saldo) {
    pc_kontrol($budget_info, $header->grand_total, (int) $header->saldo_seharusnya, ['saldo' => (int) $header->saldo_petty_cash, 'approval' => true]);
} else {
    echo '<div class="alert alert-info">Pelaporan lama, dibuat sebelum ada field Saldo Petty Cash.</div>';
}
?>

<!-- Detail Per Pencatatan -->
<div class="box box-solid">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-list-alt"></i>&nbsp;Detail Pencatatan</h3>
    </div>
    <div class="box-body">
        <?php foreach (array_values($pencatatan_list) as $i => $item) : ?>
            <?php
            $details = isset($pencatatan_details[$item->id]) ? $pencatatan_details[$item->id] : [];
            $evidences = [];
            foreach ($details as $d) {
                $evidences[$d->id] = isset($d->evidences) ? $d->evidences : [];
            }
            pc_panel($i + 1, $item, $details, $coa_names, [
                'evidences' => $evidences,
                // Jurnal per panel hanya bila sudah terbentuk; preview-nya ada di kotak gabungan di bawah
                'jurnal'    => isset($actual_journals[$item->id]) ? $actual_journals[$item->id] : null,
            ]);
            ?>
        <?php endforeach; ?>
        <?php if (empty($pencatatan_list)) : ?>
            <div class="callout callout-warning">
                <h4><i class="fa fa-info-circle"></i> Tidak Ada Data</h4>
                <p>Tidak ada pencatatan dalam pelaporan ini.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $this->load->view('journal_preview_box', ['journal_preview' => isset($journal_preview) ? $journal_preview : [], 'header' => $header, 'coa_kas' => $coa_kas, 'master_nama' => !empty($budget_info->nama) ? $budget_info->nama : 'Petty Cash']); ?>

<div class="text-center" style="margin:10px 0 20px">
    <button type="button" class="btn btn-success btn-cf btn-approve" data-id="<?= $header->id ?>">
        <i class="fa fa-check"></i>&nbsp;Approve
    </button>
    &nbsp;&nbsp;
    <button type="button" class="btn btn-danger btn-cf btn-reject" data-id="<?= $header->id ?>">
        <i class="fa fa-times"></i>&nbsp;Reject
    </button>
    &nbsp;&nbsp;
    <a href="<?= site_url('expense_petty_cash/approval') ?>" class="btn btn-default btn-cf">
        <i class="fa fa-arrow-left"></i>&nbsp;Kembali
    </a>
</div>

<!-- Reject Form (hidden by default) -->
<div class="box box-danger" id="reject-form-box" style="display: none; margin-top: 15px;">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-times-circle"></i>&nbsp;Form Alasan Reject</h3>
    </div>
    <div class="box-body">
        <div class="form-group">
            <label for="alasan-reject">Alasan Reject <span class="text-red">*</span></label>
            <textarea id="alasan-reject" class="form-control" rows="4" placeholder="Masukkan alasan reject (minimal 10 karakter)" maxlength="500"></textarea>
            <span class="help-block text-red" id="alasan-reject-error" style="display: none;">Alasan reject wajib diisi minimal 10 karakter.</span>
            <span class="help-block text-muted"><span id="char-count">0</span>/500 karakter</span>
        </div>
    </div>
    <div class="box-footer">
        <button type="button" class="btn btn-default" id="btn-cancel-reject">
            <i class="fa fa-ban"></i>&nbsp;Batal
        </button>
        <button type="button" class="btn btn-danger pull-right" id="btn-submit-reject" data-id="<?= $header->id ?>">
            <i class="fa fa-send"></i>&nbsp;Submit Reject
        </button>
    </div>
</div>

<script>
    var BASE_URL = '<?= site_url('expense_petty_cash/') ?>';
    var SELISIH_SALDO = <?= json_encode($selisih_saldo) ?>;
</script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= base_url('assets/js/modules/expense_petty_cash/approval.js') ?>"></script>
