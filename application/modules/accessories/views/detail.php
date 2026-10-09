<?php
$id             = (!empty($header)) ? $header[0]->id : '';
$id_stock       = (!empty($header)) ? $header[0]->id_stock : '-';
$id_category    = (!empty($header)) ? $header[0]->id_category : '';
$stock_name     = (!empty($header)) ? $header[0]->stock_name : '-';
$trade_name     = (!empty($header)) ? $header[0]->trade_name : '-';
$brand          = (!empty($header)) ? $header[0]->brand : '-';
$spec           = (!empty($header)) ? $header[0]->spec : '-';
$id_unit_gudang = (!empty($header)) ? $header[0]->id_unit_gudang : '';
$konversi       = (!empty($header)) ? $header[0]->konversi : '1';
$id_unit        = (!empty($header)) ? $header[0]->id_unit : '';
$min_order      = (!empty($header)) ? $header[0]->min_order : 0;
$coa            = (!empty($header)) ? $header[0]->no_coa : '-';
$nm_coa         = (!empty($header)) ? $header[0]->nm_coa : '-';
$status         = (!empty($header) && isset($header[0]->status)) ? $header[0]->status : 1;

// Resolving category name
$nm_category = '-';
if (!empty($category)) {
    foreach ($category as $cat) {
        if ($cat->id == $id_category) {
            $nm_category = ucwords(strtolower($cat->nm_category));
            break;
        }
    }
}

// Resolving satuan
$nm_satuan_packing = '-';
if (!empty($satuan_packing)) {
    foreach ($satuan_packing as $sat) {
        if ($sat->id == $id_unit_gudang) {
            $nm_satuan_packing = strtoupper($sat->code);
            break;
        }
    }
}

$nm_satuan_unit = '-';
if (!empty($satuan)) {
    foreach ($satuan as $sat) {
        if ($sat->id == $id_unit) {
            $nm_satuan_unit = strtoupper($sat->code);
            break;
        }
    }
}
?>

<div class="detail-box" style="font-family: 'Outfit', 'Segoe UI', -apple-system, sans-serif;">
    <div style="background: #f7faf8; border: 1px solid #dbe8e0; border-radius: 10px; padding: 16px 20px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; gap: 14px;">
        <div>
            <h4 style="font-size: 17px; font-weight: 700; color: #176454; margin: 0 0 6px;">
                <?= htmlspecialchars(strtoupper(strtolower($stock_name))); ?>
            </h4>
            <div style="color: #64756c; font-size: 12.5px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <span>Kode: <strong style="font-family: 'Consolas', monospace; color: #176454;"><?= htmlspecialchars($id_stock); ?></strong></span>
                <span>•</span>
                <span>Kategori: <strong><?= htmlspecialchars($nm_category); ?></strong></span>
            </div>
        </div>
        <div>
            <?php if ($status == '1'): ?>
                <span class="status-badge active" style="font-size: 11.5px; padding: 4px 12px;">
                    <i class="fa fa-check-circle" aria-hidden="true"></i> AKTIF
                </span>
            <?php else: ?>
                <span class="status-badge inactive" style="font-size: 11.5px; padding: 4px 12px;">
                    <i class="fa fa-times-circle" aria-hidden="true"></i> NON-AKTIF
                </span>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="accessories-section-header" style="margin-bottom: 10px;">
                <span class="accessories-section-icon"><i class="fa fa-tag"></i></span>
                <span>Spesifikasi Produk</span>
            </div>
            <table class="table" style="font-size: 12.5px; border: 1px solid #e5ece7; border-radius: 8px; margin-bottom: 18px;">
                <tr>
                    <td style="width: 38%; background: #f8faf9; font-weight: 600; color: #475a52; border-top: none;">Trade Name</td>
                    <td style="border-top: none;"><?= !empty($trade_name) ? htmlspecialchars($trade_name) : '-'; ?></td>
                </tr>
                <tr>
                    <td style="background: #f8faf9; font-weight: 600; color: #475a52;">Brand / Merk</td>
                    <td><?= !empty($brand) ? htmlspecialchars(ucwords($brand)) : '-'; ?></td>
                </tr>
                <tr>
                    <td style="background: #f8faf9; font-weight: 600; color: #475a52;">Spesifikasi</td>
                    <td><?= !empty($spec) ? htmlspecialchars($spec) : '-'; ?></td>
                </tr>
            </table>
        </div>

        <div class="col-md-6">
            <div class="accessories-section-header" style="margin-bottom: 10px;">
                <span class="accessories-section-icon"><i class="fa fa-calculator"></i></span>
                <span>Satuan & Konversi</span>
            </div>
            <table class="table" style="font-size: 12.5px; border: 1px solid #e5ece7; border-radius: 8px; margin-bottom: 18px;">
                <tr>
                    <td style="width: 44%; background: #f8faf9; font-weight: 600; color: #475a52; border-top: none;">Satuan Packing</td>
                    <td style="border-top: none;"><span class="item-code-badge"><?= $nm_satuan_packing; ?></span></td>
                </tr>
                <tr>
                    <td style="background: #f8faf9; font-weight: 600; color: #475a52;">Nilai Konversi</td>
                    <td><strong style="color: #176454;"><?= number_format((float)$konversi, 2); ?></strong></td>
                </tr>
                <tr>
                    <td style="background: #f8faf9; font-weight: 600; color: #475a52;">Satuan Penggunaan</td>
                    <td><span class="item-code-badge"><?= $nm_satuan_unit; ?></span></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="accessories-section-header" style="margin-bottom: 10px;">
                <span class="accessories-section-icon"><i class="fa fa-book"></i></span>
                <span>Ketentuan Akuntansi & Pengadaan</span>
            </div>
            <table class="table" style="font-size: 12.5px; border: 1px solid #e5ece7; border-radius: 8px; margin-bottom: 6px;">
                <tr>
                    <td style="width: 25%; background: #f8faf9; font-weight: 600; color: #475a52; border-top: none;">Minimum Order</td>
                    <td style="border-top: none;"><?= number_format((float)$min_order); ?> <?= $nm_satuan_unit; ?></td>
                </tr>
                <tr>
                    <td style="background: #f8faf9; font-weight: 600; color: #475a52;">Akun COA</td>
                    <td>
                        <?php if (!empty($coa) && $coa != '0'): ?>
                            <strong style="color: #176454;"><?= htmlspecialchars($coa); ?></strong> - <?= htmlspecialchars($nm_coa); ?>
                        <?php else: ?>
                            <span style="color: #9cb0a6; font-style: italic;">Tidak diset</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
