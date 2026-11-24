<section class="invoice-section" aria-labelledby="invoice-items-title">
    <div class="invoice-section-heading"><h3 id="invoice-items-title">Rincian item</h3><p>Biaya kirim mengikuti penawaran. Total penjualan tidak boleh melebihi outstanding.</p></div>
    <?php if ($editable): ?><button type="button" class="btn btn-default" data-add-item>Tambah item</button><?php endif; ?>
    <div class="invoice-table-scroll" role="region" aria-label="Rincian item invoice" tabindex="0">
        <table class="table table-bordered invoice-top-table"><thead><tr><th scope="col">Item</th><th scope="col">Qty</th><th scope="col">Harga (Rp)</th><th scope="col">Total (Rp)</th><?php if ($editable): ?><th scope="col">Aksi</th><?php endif; ?></tr></thead>
        <tbody data-invoice-items>
        <?php foreach ($items as $index=>$item): ?><tr data-item>
            <td><?php if ($editable): ?><textarea class="form-control" name="item[<?= $index ?>][nama]" maxlength="1000" required aria-label="Deskripsi item <?= $index+1 ?>"><?= $escape($item['nm_item']) ?></textarea><?php else: ?><?= nl2br($escape($item['nm_item'])) ?><?php endif; ?></td>
            <td><?php if ($editable): ?><input class="form-control" type="number" min="1" max="999999" step="1" name="item[<?= $index ?>][qty]" value="<?= $escape(fmod((float)$item['qty'],1)==0.0?(string)(int)$item['qty']:$item['qty']) ?>" required aria-label="Qty item <?= $index+1 ?>"><?php else: ?><?= $escape($item['qty']) ?><?php endif; ?></td>
            <td><?php if ($editable): ?><input class="form-control text-right" type="text" inputmode="decimal" data-money autocomplete="off" name="item[<?= $index ?>][harga]" value="<?= $escape(number_format((float)$item['harga'],2,'.',',')) ?>" required aria-label="Harga item <?= $index+1 ?>"><?php else: ?><?= number_format((float)$item['harga'],2) ?><?php endif; ?></td>
            <td class="text-right" data-item-total><?= number_format((float)$item['total'],2) ?></td>
            <?php if ($editable): ?><td><button type="button" class="btn btn-default" data-remove-item aria-label="Hapus item <?= $index+1 ?>">Hapus</button></td><?php endif; ?>
        </tr><?php endforeach; ?>
        </tbody></table>
    </div>
    <p>Biaya kirim: <strong><?= number_format((float)$invoice['biaya_kirim'],2) ?></strong></p>
    <?php if ($editable): ?><input type="hidden" name="biaya_kirim" value="<?= $escape($invoice['biaya_kirim']) ?>"><?php endif; ?>
    <?php if ($outstanding!==null): ?><p>Outstanding penawaran: <strong><?= number_format((float)$outstanding,2) ?></strong></p><?php endif; ?>
</section>
