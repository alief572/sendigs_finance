<style>
    .table-jurnal th {
        background-color: #0073b7 !important;
        color: #fff !important;
        vertical-align: middle !important;
        padding: 12px 8px !important;
        border-bottom: 2px solid #0056b3 !important;
    }
    .table-jurnal td {
        vertical-align: middle !important;
        padding: 8px !important;
    }
    .input-readonly {
        background-color: #f4f6f9 !important;
        border: 1px solid #ddd !important;
        color: #333 !important;
        font-weight: 600;
    }
    .textarea-keterangan {
        resize: vertical;
        min-height: 45px;
        font-size: 13px;
        line-height: 1.4;
        background-color: #fafafa !important;
        border: 1px dashed #ccc !important;
    }
    .tfoot-jurnal th {
        background-color: #f1f4f9;
        font-size: 14px;
        font-weight: bold;
        color: #333;
        padding: 10px 8px !important;
        border-top: 2px solid #0073b7 !important;
    }
</style>

<div class="table-responsive" style="border-radius: 6px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 10px;">
    <table class="table table-bordered table-striped table-hover w-100 table-jurnal" style="margin-bottom: 0;">
        <thead>
            <tr>
                <th class="text-center" width="10%">Tanggal</th>
                <th class="text-center" width="8%">Tipe</th>
                <th class="text-center" width="12%">No. COA</th>
                <th class="text-center" width="18%">Nama COA</th>
                <th class="text-center" width="22%">Keterangan</th>
                <th class="text-center" width="12%">No. Reff</th>
                <th class="text-center" width="9%">Debit</th>
                <th class="text-center" width="9%">Kredit</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 0;
            $ttl_debit = 0;
            $ttl_kredit = 0;

            if (!empty($data_jurnal)) {
                foreach ($data_jurnal as $item) {
                    if ($item->debit <= 0 && $item->kredit <= 0) {
                        continue;
                    }

                    $no++;

                    $no_reff = $item->no_transaksi;
                    $keterangan = $item->keterangan;
                    if (strpos($keterangan, '{REF:') !== false) {
                        preg_match('/\{REF:(.*?)\}/', $keterangan, $matches);
                        if (isset($matches[1])) {
                            $no_reff = $matches[1];
                            $keterangan = trim(str_replace($matches[0], '', $keterangan));
                        }
                    }

                    echo '<tr>';
                    echo '<td class="text-center">' . date('d M Y', strtotime($item->tgl_jurnal)) . '</td>';
                    echo '<td class="text-center"><span class="badge bg-navy">' . htmlspecialchars($item->jenis_transaksi) . '</span></td>';
                    echo '<td class="text-center"><strong>' . htmlspecialchars($item->coa) . '</strong></td>';
                    echo '<td class="text-left">' . htmlspecialchars($item->nm_coa) . '</td>';
                    echo '<td class="text-left"><textarea class="form-control form-control-sm textarea-keterangan" readonly>' . htmlspecialchars($keterangan) . '</textarea></td>';
                    echo '<td class="text-center"><span class="label label-info" style="font-size:11px;">' . htmlspecialchars($no_reff) . '</span></td>';
                    echo '<td class="text-right"><input type="text" class="form-control form-control-sm text-right input-readonly" value="' . number_format($item->debit) . '" readonly></td>';
                    echo '<td class="text-right"><input type="text" class="form-control form-control-sm text-right input-readonly" value="' . number_format($item->kredit) . '" readonly></td>';
                    echo '</tr>';

                    $ttl_debit += $item->debit;
                    $ttl_kredit += $item->kredit;
                }
            } else {
                echo '<tr><td colspan="8" class="text-center text-muted">Tidak ada rincian ayat jurnal.</td></tr>';
            }
            ?>
        </tbody>
        <tfoot class="tfoot-jurnal">
            <tr>
                <th colspan="6" class="text-right" style="vertical-align: middle;">GRAND TOTAL</th>
                <th class="text-right" style="color: #00a65a; font-size: 15px;">
                    <?= number_format($ttl_debit) ?>
                </th>
                <th class="text-right" style="color: #00a65a; font-size: 15px;">
                    <?= number_format($ttl_kredit) ?>
                </th>
            </tr>
            <?php if ($ttl_debit != $ttl_kredit): ?>
            <tr>
                <th colspan="8" class="text-center" style="background-color: #f2dede; color: #a94442;">
                    <i class="fa fa-warning"></i> Perhatian: Total Debit dan Kredit tidak balance!
                </th>
            </tr>
            <?php endif; ?>
        </tfoot>
    </table>
</div>
