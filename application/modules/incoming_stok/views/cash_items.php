<?php
$number = 0;
$escape = function ($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); };
foreach ($items as $item):
    $remaining = max(0, $item['propose_purchase'] - $item['qty_in']);
    if ($remaining <= 0) continue;
    $index = ++$number;
?>
<tr>
    <td><?= $index ?></td>
    <td><?= $escape($item['detail_id']) ?>
        <input type="hidden" name="Detail[<?= $index ?>][id]" value="<?= $escape($item['detail_id']) ?>">
        <input type="hidden" name="Detail[<?= $index ?>][id_cash]" value="<?= $escape($item['no_non_po']) ?>">
    </td>
    <td><?= $escape($item['id_stock']) ?></td>
    <td><?= $escape($item['stock_name']) ?><br><small><?= $escape($item['no_non_po'].' / '.$item['no_pr']) ?></small></td>
    <td><?= number_format($item['propose_purchase'], 5) ?></td>
    <td><?= $escape($item['unit']) ?></td>
    <td><?= number_format($item['qty_in'], 5) ?></td>
    <td><?= number_format($remaining, 5) ?></td>
    <td><input type="text" name="Detail[<?= $index ?>][qty_in]" class="form-control input-sm autoNumeric4" value="0"></td>
    <td><input type="text" name="Detail[<?= $index ?>][ket]" class="form-control input-sm"></td>
</tr>
<?php endforeach; ?>
<?php if (!$number): ?><tr><td colspan="10">Tidak ada outstanding Pembelian Cash.</td></tr><?php endif; ?>
