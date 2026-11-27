<?php

/**
 * Detail Pelaporan Petty Cash (mockup specs/docs/petty-cash, mode view).
 * Ringkasan, Kontrol Saldo (tersimpan), alasan reject, Detail Pencatatan (item + evidence + jurnal).
 *
 * PHP vars:
 *   $pelaporan, $budget_info, $pencatatan_details, $coa_names, $journal_preview, $actual_journals
 */
require_once __DIR__ . '/../partials/pc_review.php';

$header = isset($pelaporan->header) ? $pelaporan->header : $pelaporan;
$pencatatan_list = isset($pelaporan->pencatatan_list) ? $pelaporan->pencatatan_list : [];

$books_by_id = [];
foreach ((isset($journal_preview) ? $journal_preview : []) as $jp) {
    $books_by_id[$jp['pencatatan']->id] = $jp['books'];
}

pc_style();
pc_evidence_assets();
pc_summary($header, count($pencatatan_list), isset($budget_info->sisa_budget) ? $budget_info->sisa_budget : 0);

if (isset($header->saldo_petty_cash) && $header->saldo_petty_cash !== null) {
    pc_kontrol($budget_info, $header->grand_total, (int) $header->saldo_seharusnya, ['saldo' => (int) $header->saldo_petty_cash]);
} elseif ($header->status !== 'draft') {
    echo '<div class="alert alert-info">Pelaporan lama, dibuat sebelum ada field Saldo Petty Cash.</div>';
}
?>

<?php if (!empty($header->alasan_reject)) : ?>
    <div class="alert alert-danger"><b>Alasan penolakan</b><br><?= pc_e($header->alasan_reject) ?></div>
<?php endif; ?>

<!-- Detail Per Pencatatan -->
<div class="box box-solid">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-list-alt"></i>&nbsp;Detail Pencatatan</h3>
    </div>
    <div class="box-body">
        <?php foreach (array_values($pencatatan_list) as $i => $item) : ?>
            <?php
            $detail_data = isset($pencatatan_details[$item->id]) ? $pencatatan_details[$item->id] : null;
            pc_panel($i + 1, $item, ($detail_data && isset($detail_data->details)) ? $detail_data->details : [], $coa_names, [
                'evidences' => ($detail_data && isset($detail_data->evidences)) ? $detail_data->evidences : [],
                'jurnal'    => isset($actual_journals[$item->id])
                    ? $actual_journals[$item->id]
                    : ['books' => isset($books_by_id[$item->id]) ? $books_by_id[$item->id] : false],
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

<div class="text-center" style="margin:10px 0 20px">
    <a href="<?= site_url('expense_petty_cash/pelaporan') ?>" class="btn btn-default btn-cf">
        <i class="fa fa-arrow-left"></i>&nbsp;Kembali
    </a>
</div>
