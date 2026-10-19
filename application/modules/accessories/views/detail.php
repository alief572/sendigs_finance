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
<<<<<<< HEAD
	foreach ($category as $cat) {
		if ($cat->id == $id_category) {
			$nm_category = strtoupper($cat->nm_category);
			break;
		}
	}
=======
    foreach ($category as $cat) {
        if ($cat->id == $id_category) {
            $nm_category = ucwords(strtolower($cat->nm_category));
            break;
        }
    }
>>>>>>> b127c8e19d1f7bed84ac03533b27c503f3837a47
}

// Resolving satuan
$nm_satuan_packing = '-';
if (!empty($satuan_packing)) {
<<<<<<< HEAD
	foreach ($satuan_packing as $sat) {
		if ($sat->id == $id_unit_gudang) {
			$nm_satuan_packing = strtoupper($sat->code);
			break;
		}
	}
=======
    foreach ($satuan_packing as $sat) {
        if ($sat->id == $id_unit_gudang) {
            $nm_satuan_packing = strtoupper($sat->code);
            break;
        }
    }
>>>>>>> b127c8e19d1f7bed84ac03533b27c503f3837a47
}

$nm_satuan_unit = '-';
if (!empty($satuan)) {
<<<<<<< HEAD
	foreach ($satuan as $sat) {
		if ($sat->id == $id_unit) {
			$nm_satuan_unit = strtoupper($sat->code);
			break;
		}
	}
}
?>

<style>
	.detail-box {
		font-size: 13px;
	}
	.detail-header-card {
		background: #f8fafc;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		padding: 16px 20px;
		margin-bottom: 20px;
		display: flex;
		justify-content: space-between;
		align-items: center;
	}
	.detail-header-title {
		font-size: 16px;
		font-weight: 700;
		color: #1e293b;
		margin: 0;
	}
	.detail-header-sub {
		color: #64748b;
		font-size: 12px;
		margin-top: 4px;
	}
	.spec-table {
		width: 100%;
		border-collapse: separate;
		border-spacing: 0;
		margin-bottom: 18px;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		overflow: hidden;
	}
	.spec-table th {
		background: #f1f5f9;
		color: #475569;
		padding: 10px 14px;
		font-weight: 600;
		font-size: 12px;
		text-transform: uppercase;
		letter-spacing: 0.4px;
		border-bottom: 1px solid #e2e8f0;
		width: 32%;
	}
	.spec-table td {
		background: #ffffff;
		color: #1e293b;
		padding: 10px 14px;
		border-bottom: 1px solid #f1f5f9;
		border-left: 1px solid #e2e8f0;
		font-weight: 500;
	}
	.spec-table tr:last-child th,
	.spec-table tr:last-child td {
		border-bottom: none;
	}
	.detail-section-heading {
		font-size: 13px;
		font-weight: 700;
		color: #3b82f6;
		text-transform: uppercase;
		letter-spacing: 0.5px;
		margin-bottom: 10px;
		display: flex;
		align-items: center;
		gap: 6px;
	}
</style>

<div class="detail-box">
	<div class="detail-header-card">
		<div>
			<h4 class="detail-header-title"><?= htmlspecialchars($stock_name); ?></h4>
			<div class="detail-header-sub">
				<i class="fa fa-barcode"></i> Item Code: <strong><?= htmlspecialchars($id_stock); ?></strong> &nbsp;|&nbsp; 
				<i class="fa fa-folder"></i> Kategori: <strong><?= htmlspecialchars($nm_category); ?></strong>
			</div>
		</div>
		<div>
			<?php if ($status == '1'): ?>
				<span class="status-badge active" style="font-size: 12px; padding: 6px 14px;"><i class="fa fa-check-circle"></i> AKTIF</span>
			<?php else: ?>
				<span class="status-badge inactive" style="font-size: 12px; padding: 6px 14px;"><i class="fa fa-times-circle"></i> NON-AKTIF</span>
			<?php endif; ?>
		</div>
	</div>

	<div class="row">
		<div class="col-md-6">
			<div class="detail-section-heading">
				<i class="fa fa-info-circle"></i> Spesifikasi Produk
			</div>
			<table class="spec-table">
				<tr>
					<th>Trade Name</th>
					<td><?= !empty($trade_name) ? htmlspecialchars($trade_name) : '-'; ?></td>
				</tr>
				<tr>
					<th>Brand / Merk</th>
					<td><?= !empty($brand) ? htmlspecialchars(strtoupper($brand)) : '-'; ?></td>
				</tr>
				<tr>
					<th>Spesifikasi</th>
					<td><?= !empty($spec) ? htmlspecialchars($spec) : '-'; ?></td>
				</tr>
			</table>
		</div>

		<div class="col-md-6">
			<div class="detail-section-heading">
				<i class="fa fa-cubes"></i> Satuan & Konversi
			</div>
			<table class="spec-table">
				<tr>
					<th>Satuan Packing</th>
					<td><span class="label label-default" style="font-size: 11px;"><?= $nm_satuan_packing; ?></span></td>
				</tr>
				<tr>
					<th>Nilai Konversi</th>
					<td><strong><?= number_format((float)$konversi, 2); ?></strong></td>
				</tr>
				<tr>
					<th>Unit Penggunaan</th>
					<td><span class="label label-info" style="font-size: 11px;"><?= $nm_satuan_unit; ?></span></td>
				</tr>
			</table>
		</div>
	</div>

	<div class="row">
		<div class="col-md-12">
			<div class="detail-section-heading">
				<i class="fa fa-book"></i> Akuntansi & Pengadaan
			</div>
			<table class="spec-table">
				<tr>
					<th>Minimum Order</th>
					<td><?= number_format((float)$min_order); ?> <?= $nm_satuan_unit; ?></td>
				</tr>
				<tr>
					<th>Akun COA</th>
					<td>
						<?php if (!empty($coa) && $coa != '0'): ?>
							<strong><?= htmlspecialchars($coa); ?></strong> - <?= htmlspecialchars($nm_coa); ?>
						<?php else: ?>
							<span class="text-muted">Tidak diset</span>
						<?php endif; ?>
					</td>
				</tr>
			</table>
		</div>
	</div>
=======
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
>>>>>>> b127c8e19d1f7bed84ac03533b27c503f3837a47
</div>
