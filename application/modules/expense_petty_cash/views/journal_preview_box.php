<?php

/**
 * Kotak "Preview Jurnal (dibentuk saat Approve)" di Approval Pelaporan (mockup specs/docs/petty-cash).
 *
 * Jurnal pencatatan TIDAK dibuat saat simpan; dibentuk saat pelaporan di-approve, satu jurnal per
 * pencatatan, bertanggal sesuai tanggal pencatatan (PC-R-02, PC-R-04). Baris dibentuk oleh
 * Expense_petty_cash_model::build_journal_preview (dijaga tes paritas terhadap jurnal nyata).
 *
 * Vars: $journal_preview [['pencatatan' => obj, 'books' => array|false], ...], $header (pelaporan), $coa_kas, $master_nama
 */
require_once __DIR__ . '/partials/pc_review.php';

$jp_list  = isset($journal_preview) ? $journal_preview : [];
$jp_inter = in_array(strtoupper($header->company), ['VUCA', 'SUSTAIN'], true);
$jp_count = 0;
$jp_debit = 0;
foreach ($jp_list as $item) {
    foreach ($item['books'] ?: [] as $book) {
        $jp_count++;
        $jp_debit += array_sum(array_column($book['rows'], 'debit'));
    }
}
?>
<div class="box box-solid" id="box-preview-jurnal" style="border-top:3px solid #00a65a">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-book"></i>&nbsp;Preview Jurnal <small>(dibentuk saat Approve)</small></h3>
    </div>
    <div class="box-body">
        <p style="font-size:13px;margin-top:0"><?= count($jp_list) ?> pencatatan &rarr; <b><?= $jp_count ?> jurnal</b>, masing-masing bertanggal pencatatannya, masuk Jurnal All (belum posting). COA Petty Cash dari master <b><?= pc_e($master_nama) ?></b>: <b><?= pc_e($coa_kas['kode'] . ' ' . $coa_kas['nama']) ?></b>.</p>

        <?php foreach ($jp_list as $item) : ?>
            <?php $p = $item['pencatatan']; ?>
            <div style="font-weight:600;font-size:13px;margin:10px 0 6px"><?= pc_e($p->no_pencatatan) ?> · <?= pc_tgl($p->tanggal) ?><?= !empty($p->keterangan) ? ' · ' . pc_e($p->keterangan) : '' ?></div>
            <?php if (empty($item['books'])) : ?>
                <div class="alert alert-danger">Jurnal tidak dapat dibentuk untuk pencatatan ini (company atau nominal tidak valid). Approve akan dibatalkan sampai diperbaiki.</div>
            <?php else : ?>
                <?php pc_jurnal($item['books'], $p->no_pencatatan, $p->tanggal); ?>
            <?php endif; ?>
        <?php endforeach; ?>

        <table class="table" style="max-width:520px;font-size:13px;margin:8px 0 0">
            <tbody>
                <tr>
                    <td>Total debit seluruh jurnal</td>
                    <td class="money"><b><?= pc_rp($jp_debit) ?></b></td>
                </tr>
            </tbody>
        </table>
        <div class="alert" style="background:#f4f8fb;border:1px solid #d6e4ef;color:#31708f;font-size:12.5px;margin:10px 0 0">
            Saat Approve juga dibuat: Request Payment refill <b>Rp <?= pc_rp($header->grand_total) ?></b> (refill_pettycash, company STM, masuk menu Request Payment)<?= $jp_inter ? ' dan Payment Hutang ' . pc_e($header->company) . ' (draft)' : '' ?>. Jurnal refill dibentuk saat Payment: Dr <?= pc_e($coa_kas['kode'] . ' ' . $coa_kas['nama']) ?> / Cr Bank yang dipilih (buku STM).
        </div>
    </div>
</div>
