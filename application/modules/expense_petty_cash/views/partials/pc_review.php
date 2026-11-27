<?php

/**
 * Bagian tampilan bersama Konfirmasi, Approval, dan Detail Pelaporan (mockup specs/docs/petty-cash).
 * Hanya mencetak HTML; tidak membaca atau mengubah data.
 */

if (!function_exists('pc_rp')) {

    function pc_e($v)
    {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }

    function pc_rp($n)
    {
        return number_format((float) $n, 0, ',', '.');
    }

    function pc_tgl($d)
    {
        return date('d/m/Y', strtotime($d));
    }

    function pc_style()
    {
        ?>
        <style>
            .sum-card { background: linear-gradient(135deg, #3c8dbc, #00c0ef); color: #fff; border-radius: 6px; padding: 14px 16px; margin-bottom: 14px; }
            .sum-card .card-row { display: flex; flex-wrap: wrap; gap: 10px 26px; }
            .sum-card .label-text { font-size: 11px; text-transform: uppercase; opacity: .85; letter-spacing: .3px; }
            .sum-card .value-text { font-size: 18px; font-weight: 700; }
            .sum-card .value-text.text-sm { font-size: 14px; }
            .kontrol { background: #fff; border-top: 3px solid #f39c12; border-radius: 3px; box-shadow: 0 1px 1px rgba(0,0,0,.1); padding: 12px 14px; margin-bottom: 14px; }
            .kontrol h4 { margin: 0 0 8px; font-size: 15px; }
            .kontrol table { margin: 0; max-width: 640px; }
            .kontrol .table > tbody > tr > td { padding: 5px 8px; font-size: 13.5px; }
            .kontrol .table > tbody > tr.tot > td { font-weight: 700; border-top: 2px solid #ccc; }
            .money { text-align: right; white-space: nowrap; }
            .sel-ok { color: #00a65a; font-weight: 700; }
            .sel-no { color: #dd4b39; font-weight: 700; }
            .sel-warn { background: #fcf8e3; border: 2px solid #f39c12; border-left-width: 6px; border-radius: 4px; padding: 10px 14px; margin: 10px 0 0; font-size: 13.5px; color: #8a6d3b; }
            .pc-panel { border: 1px solid #e3e8ee; border-radius: 4px; margin-bottom: 10px; background: #fff; }
            .pc-panel .ph { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 8px 12px; background: #f7f9fb; border-bottom: 1px solid #e3e8ee; }
            .pc-panel .ph b { font-size: 14px; white-space: nowrap; }
            .pc-panel .pm { font-size: 12px; color: #777; }
            .pc-panel .pb { padding: 8px 12px; }
            .pc-panel .detail-item-table { font-size: 12.5px; margin-bottom: 8px; }
            .jurnal-section h5 { font-size: 12px; font-weight: 600; color: #555; margin: 10px 0 6px; }
            .ja-meta { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px 16px; margin: 0 0 10px; padding: 12px; border: 1px solid #d8e0eb; border-radius: 6px; background: #f8fafc; }
            .ja-meta dt { margin-bottom: 3px; font-size: 12px; font-weight: 500; color: #526176; }
            .ja-meta dd { margin: 0; font-weight: 600; overflow-wrap: anywhere; }
            @media (max-width: 900px) { .ja-meta { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
            .jlab { display: inline-block; font-size: 12px; font-weight: 700; color: #fff; padding: 3px 8px; border-radius: 3px; margin: 4px 0 6px; }
            .jl-stm { background: #3c8dbc; }
            .jl-co { background: #f39c12; }
            .jl-ic { background: #00c0ef; }
            .ja-lines { width: 100%; font-size: 12.5px; border-collapse: collapse; margin-bottom: 4px; }
            .ja-lines thead th, .ja-lines tfoot th { padding: 7px 9px; background: #1a78b1; color: #fff; font-weight: 600; }
            .ja-lines td { padding: 7px 9px; border: 1px solid #d8e0eb; vertical-align: top; }
            .ja-lines .ja-money { text-align: right; white-space: nowrap; }
            .ja-lines td:nth-child(1), .ja-lines td:nth-child(4), .ja-lines td:nth-child(7) { white-space: nowrap; }
            .ja-lines td:nth-child(6) { min-width: 200px; }
            .btn-cf { padding: 10px 25px; }
        </style>
        <?php
    }

    function pc_summary($header, $jumlah_item, $sisa_budget)
    {
        ?>
        <div class="sum-card">
            <div class="card-row">
                <div><div class="label-text">No Pelaporan</div><div class="value-text text-sm"><?= pc_e($header->no_pelaporan) ?></div></div>
                <div><div class="label-text">Periode</div><div class="value-text text-sm"><?= pc_tgl($header->periode_start) ?> — <?= pc_tgl($header->periode_end) ?></div></div>
                <div><div class="label-text">Company</div><div class="value-text text-sm"><?= pc_e($header->company) ?></div></div>
                <div><div class="label-text">Grand Total</div><div class="value-text">Rp <?= pc_rp($header->grand_total) ?></div></div>
                <div><div class="label-text">Pencatatan</div><div class="value-text text-sm"><?= (int) $jumlah_item ?> item</div></div>
                <div><div class="label-text">Sisa Budget</div><div class="value-text text-sm">Rp <?= pc_rp($sisa_budget) ?></div></div>
            </div>
        </div>
        <?php
    }

    /**
     * Rincian Kontrol Saldo.
     *
     * @param object|false $budget_info  get_budget_info()
     * @param float        $grand_total  grand total pelaporan
     * @param int|null     $seharusnya   saldo seharusnya
     * @param array        $opt          'editable' => bool, 'saldo' => int|null, 'approval' => bool
     */
    function pc_kontrol($budget_info, $grand_total, $seharusnya, array $opt = [])
    {
        $editable = !empty($opt['editable']);
        $saldo = isset($opt['saldo']) ? $opt['saldo'] : null;
        $budget = isset($budget_info->budget) ? $budget_info->budget : 0;
        $nama = !empty($budget_info->nama) ? $budget_info->nama : 'Petty Cash';
        $selisih = ($editable || $saldo === null || $seharusnya === null) ? null : (int) $saldo - (int) $seharusnya;
        ?>
        <div class="kontrol" id="box-kontrol-saldo">
            <h4><i class="fa fa-balance-scale"></i> Kontrol Saldo Petty Cash</h4>
            <table class="table">
                <tbody>
                    <tr>
                        <td>Total Budget (plafon) <?= pc_e($nama) ?> <small class="text-muted">(1 kas untuk semua company)</small></td>
                        <td class="money"><?= pc_rp($budget) ?></td>
                    </tr>
                    <tr>
                        <td>&minus; Grand Total pelaporan ini</td>
                        <td class="money"><?= pc_rp($grand_total) ?></td>
                    </tr>
                    <tr>
                        <td>&minus; Pengeluaran lain yang belum di-refill <small class="text-muted">(pencatatan lain / pelaporan belum dibayar, semua company)</small></td>
                        <td class="money"><?= $seharusnya === null ? '-' : pc_rp($budget - $grand_total - $seharusnya) ?></td>
                    </tr>
                    <tr class="tot">
                        <td>= Saldo seharusnya <small class="text-muted">(Sisa Budget)</small></td>
                        <td class="money"><?= $seharusnya === null ? '-' : pc_rp($seharusnya) ?></td>
                    </tr>
                    <tr>
                        <td><label <?= $editable ? 'for="saldo_petty_cash"' : '' ?> style="margin:0">Saldo Petty Cash</label> <small class="text-muted">(hitung fisik uang di kas<?= $editable ? ', wajib' : '' ?>)</small></td>
                        <td class="money">
                            <?php if ($editable) : ?>
                                <input type="text" class="form-control input-sm text-right" id="saldo_petty_cash" inputmode="numeric" autocomplete="off" placeholder="0" style="width:160px;display:inline-block">
                            <?php else : ?>
                                <?= $saldo === null ? '-' : pc_rp($saldo) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr class="tot">
                        <td>Selisih (Saldo Petty Cash &minus; Saldo seharusnya)</td>
                        <td class="money" id="selisih-saldo-text">
                            <?php if ($selisih === null) : ?>
                                -
                            <?php elseif ($selisih === 0) : ?>
                                <span class="sel-ok"><i class="fa fa-check"></i> 0 (sesuai)</span>
                            <?php else : ?>
                                <span class="sel-no"><i class="fa fa-exclamation-triangle"></i> <?= pc_rp($selisih) ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php if ($editable) : ?>
                <div class="sel-warn" id="warning-selisih" style="display:none;" role="alert"></div>
            <?php elseif ($selisih !== null && $selisih !== 0) : ?>
                <div class="sel-warn" role="alert">
                    <i class="fa fa-exclamation-triangle"></i> <b>Ada selisih saldo Rp <?= pc_rp($selisih) ?></b> (<?= $selisih < 0 ? 'uang fisik kurang' : 'uang fisik lebih' ?> dari saldo seharusnya).
                    <?php if (!empty($opt['approval'])) : ?>
                        <b>Dept Head Finance wajib mengecek selisih ini</b> (nota belum dicatat, salah input, atau uang kurang/lebih) sebelum Approve. Approve tetap bisa.
                    <?php else : ?>
                        Pelaporan tetap bisa diajukan, tetapi selisih ini akan ditandai untuk dicek Dept Head Finance saat approval.
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <p class="text-muted" style="font-size:12px;margin:6px 0 0">Selisih &ne; 0 berarti ada nota yang belum dicatat, salah input, atau uang kurang/lebih. Bila ada selisih: <b>peringatan</b> ditampilkan, Ajukan dan Approve tetap bisa, Dept Head Finance mengecek saat approval.</p>
        </div>
        <?php
    }

    /**
     * Jurnal format Jurnal All (PC-R-10) untuk satu pencatatan.
     *
     * @param array  $books     [['label','company','rows'=>[[tanggal,coa,nm_coa,company,debit,kredit,keterangan]]]]
     * @param string $trx       no pencatatan
     * @param string $tanggal   tanggal pencatatan (Y-m-d)
     * @param string $status    teks status jurnal
     * @param string $no_jurnal nomor jurnal bila sudah terbentuk
     */
    function pc_jurnal(array $books, $trx, $tanggal, $status = 'Preview · terbentuk saat approve Pelaporan', $no_jurnal = '')
    {
        $multi = count($books) > 1;
        $meta = [
            'No. Transaksi'      => $trx,
            'No. Referensi'      => '-',
            'Company'            => implode(' + ', array_column($books, 'company')),
            'Divisi'             => '-',
            'Tgl Jurnal'         => pc_tgl($tanggal),
            'Status'             => $status,
            'Kategori Transaksi' => 'Petty Cash',
        ];
        if ($no_jurnal !== '') {
            $meta['No. Jurnal'] = $no_jurnal;
        }
        $semua_seimbang = true;
        ?>
        <dl class="ja-meta">
            <?php foreach ($meta as $label => $value) : ?>
                <div><dt><?= pc_e($label) ?></dt><dd><?= pc_e($value) ?></dd></div>
            <?php endforeach; ?>
        </dl>
        <?php foreach (array_values($books) as $b => $book) : ?>
            <?php
            $debit = (int) array_sum(array_column($book['rows'], 'debit'));
            $kredit = (int) array_sum(array_column($book['rows'], 'kredit'));
            $seimbang = $debit === $kredit;
            $semua_seimbang = $semua_seimbang && $seimbang;
            ?>
            <span class="jlab <?= !$multi ? 'jl-stm' : ($b === 0 ? 'jl-co' : 'jl-ic') ?>"><i class="fa fa-building"></i> <?= pc_e($book['label']) ?></span>
            <div class="table-responsive">
                <table class="ja-lines">
                    <thead>
                        <tr>
                            <th>Tanggal</th><th>Tipe</th><th>Company</th><th>No. COA</th><th>Nama COA</th><th>Keterangan</th><th>No. Reff</th>
                            <th class="ja-money">Debit</th><th class="ja-money">Kredit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($book['rows'] as $r) : ?>
                            <tr>
                                <td><?= pc_tgl($r['tanggal']) ?></td>
                                <td>Petty Cash</td>
                                <td><?= pc_e($r['company']) ?></td>
                                <td><?= pc_e($r['coa']) ?></td>
                                <td><?= pc_e($r['nm_coa']) ?></td>
                                <td><?= pc_e(isset($r['keterangan']) ? $r['keterangan'] : '') ?></td>
                                <td><?= pc_e($trx) ?></td>
                                <td class="ja-money"><?= pc_rp($r['debit']) ?></td>
                                <td class="ja-money"><?= pc_rp($r['kredit']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="7"><?= $multi ? 'TOTAL COMPANY' : 'GRAND TOTAL' ?></th>
                            <th class="ja-money"><?= pc_rp($debit) ?></th>
                            <th class="ja-money"><?= pc_rp($kredit) ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <?php if ($multi) : ?>
                <p class="<?= $seimbang ? 'text-success' : 'text-danger' ?>" style="font-size:12px;margin:4px 0 8px">Debit dan kredit company ini <?= $seimbang ? '' : 'tidak ' ?>seimbang.</p>
            <?php endif; ?>
        <?php endforeach; ?>
        <p class="<?= $semua_seimbang ? 'text-success' : 'text-danger' ?>" style="margin:6px 0 4px">
            <?= $semua_seimbang ? ($multi ? 'Total debit dan kredit seluruh company seimbang.' : 'Debit dan kredit seimbang.') : 'Debit dan kredit tidak seimbang.' ?>
        </p>
        <?php
    }

    /**
     * Thumbnail evidence satu item; klik membuka popup preview (evidence_viewer.js).
     *
     * @param array $files objek dengan original_name dan encrypted_name
     */
    function pc_evidence(array $files)
    {
        if (empty($files)) {
            echo '-';
            return;
        }
        ?>
        <div class="ev-list">
            <?php foreach ($files as $ev) : ?>
                <?php
                $ext = strtolower(pathinfo($ev->encrypted_name, PATHINFO_EXTENSION));
                $type = in_array($ext, ['png', 'jpg', 'jpeg'], true) ? 'image' : ($ext === 'pdf' ? 'pdf' : 'excel');
                $url = base_url('assets/expense_petty_cash/' . rawurlencode($ev->encrypted_name));
                ?>
                <a class="ev-thumb" href="<?= pc_e($url) ?>" target="_blank" rel="noopener" data-type="<?= $type ?>" data-name="<?= pc_e($ev->original_name) ?>" title="<?= pc_e($ev->original_name) ?>" aria-label="Lihat evidence <?= pc_e($ev->original_name) ?>">
                    <?php if ($type === 'image') : ?>
                        <img src="<?= pc_e($url) ?>" alt="" loading="lazy">
                    <?php else : ?>
                        <i class="fa <?= $type === 'pdf' ? 'fa-file-pdf-o' : 'fa-file-excel-o' ?>" aria-hidden="true"></i>
                        <small><?= pc_e(strtoupper($ext)) ?></small>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /** CSS dan skrip popup evidence, cukup sekali per halaman. */
    function pc_evidence_assets()
    {
        ?>
        <link rel="stylesheet" href="<?= base_url('assets/css/petty-cash-evidence.css') ?>">
        <script src="<?= base_url('assets/js/modules/expense_petty_cash/evidence_viewer.js') ?>"></script>
        <?php
    }

    /**
     * Panel satu pencatatan: item, evidence (opsional), dan jurnal (opsional).
     *
     * @param int    $no
     * @param object $item       baris pencatatan_list
     * @param array  $details    detail item
     * @param array  $coa_names  coa_code => nama
     * @param array  $opt        'evidences' => [detail_id => [evidence]] (kolom tampil bila diberikan),
     *                           'jurnal' => null (tanpa bagian jurnal) | ['books'=>array|false, 'actual'=>bool, 'posted'=>bool, 'no_jurnal'=>string]
     */
    function pc_panel($no, $item, array $details, array $coa_names, array $opt = [])
    {
        $evidences = isset($opt['evidences']) ? $opt['evidences'] : null;
        $jurnal = isset($opt['jurnal']) ? $opt['jurnal'] : null;
        $cols = $evidences === null ? 7 : 8;
        ?>
        <div class="pc-panel">
            <div class="ph">
                <div>
                    <div><strong><i class="fa fa-file-text-o"></i>&nbsp;<?= (int) $no ?>. <?= pc_e($item->no_pencatatan) ?></strong></div>
                    <div class="pm">
                        <i class="fa fa-calendar-o"></i> <?= pc_tgl($item->tanggal) ?> &bull; <i class="fa fa-user"></i> <?= pc_e($item->request_by) ?>
                        <?php if (!empty($item->keterangan)) : ?>
                            &bull; <i class="fa fa-comment-o"></i> <?= pc_e($item->keterangan) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <b>Rp <?= pc_rp($item->grand_total) ?></b>
            </div>
            <div class="pb">
                <div class="table-responsive">
                    <table class="table table-bordered detail-item-table">
                        <thead>
                            <tr>
                                <th width="30" class="text-center">#</th>
                                <th>COA</th>
                                <th>Pengeluaran</th>
                                <th>Spesifikasi</th>
                                <th class="text-center" width="55">Qty</th>
                                <th class="text-right" width="100">Nominal</th>
                                <th class="text-right" width="100">Total</th>
                                <?php if ($evidences !== null) : ?><th>Evidence</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($details)) : ?>
                                <?php foreach (array_values($details) as $k => $d) : ?>
                                    <?php $nama = isset($coa_names[$d->coa_code]) && $coa_names[$d->coa_code] !== $d->coa_code ? ' - ' . $coa_names[$d->coa_code] : ''; ?>
                                    <tr>
                                        <td class="text-center"><?= $k + 1 ?></td>
                                        <td><?= pc_e($d->coa_code . $nama) ?></td>
                                        <td><?= pc_e($d->pengeluaran) ?></td>
                                        <td><?= pc_e($d->spesifikasi) ?></td>
                                        <td class="text-center"><?= pc_e($d->jumlah) ?></td>
                                        <td class="text-right"><?= pc_rp($d->nominal) ?></td>
                                        <td class="text-right"><?= pc_rp($d->total) ?></td>
                                        <?php if ($evidences !== null) : ?>
                                            <td class="ev">
                                                <?php pc_evidence(isset($evidences[$d->id]) ? $evidences[$d->id] : []); ?>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr><td colspan="<?= $cols ?>" class="text-center text-muted"><em>Detail item tidak tersedia</em></td></tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="6" class="text-right">Total</th>
                                <th class="text-right"><?= pc_rp($item->grand_total) ?></th>
                                <?php if ($evidences !== null) : ?><th></th><?php endif; ?>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <?php if ($jurnal !== null) : ?>
                    <?php
                    $actual = !empty($jurnal['actual']);
                    $status = !$actual ? 'Preview · terbentuk saat approve Pelaporan' : (!empty($jurnal['posted']) ? 'Sudah posting' : 'Belum posting');
                    ?>
                    <div class="jurnal-section">
                        <h5><i class="fa fa-book"></i>&nbsp;<?= $actual ? 'Jurnal' : 'Preview Jurnal' ?></h5>
                        <?php if (empty($jurnal['books'])) : ?>
                            <div class="alert alert-danger">Jurnal tidak dapat dibentuk untuk pencatatan ini (company atau nominal tidak valid). Approve akan dibatalkan sampai diperbaiki.</div>
                        <?php else : ?>
                            <?php pc_jurnal($jurnal['books'], $item->no_pencatatan, $item->tanggal, $status, isset($jurnal['no_jurnal']) ? $jurnal['no_jurnal'] : ''); ?>
                            <p class="text-muted" style="font-size:12px;margin:0 0 6px">
                                <?= $actual
                                    ? 'Jurnal di Jurnal All (status ' . strtolower($status) . ').'
                                    : 'Jurnal dibentuk saat Pelaporan di-approve, tanggal ' . pc_tgl($item->tanggal) . ' (tanggal pencatatan).' ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
