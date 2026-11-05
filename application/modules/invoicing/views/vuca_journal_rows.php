<?php $escapeVuca=function($value){return htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');}; ?>
<?php foreach ($journals as $index=>$line): $n=$index+1; $revenue=!in_array($line['coa'],Invoice_vuca::fixed_codes(),true); ?>
<tr <?= $revenue?'data-vuca-revenue':'' ?>>
    <td><?= $escapeVuca(date('d-F-Y',strtotime($line['tgl_jurnal']))) ?><input type="hidden" name="tgl_jurnal_<?= $n ?>" value="<?= $escapeVuca($line['tgl_jurnal']) ?>"></td>
    <td><span <?= $revenue?'data-vuca-sales-code':'' ?>><?= $escapeVuca($line['coa']) ?></span><input type="hidden" name="coa_jurnal_<?= $n ?>" <?= $revenue?'data-vuca-code-input':'' ?> value="<?= $escapeVuca($line['coa']) ?>"></td>
    <td><?= $escapeVuca($line['nm_company']) ?><input type="hidden" name="id_company_<?= $n ?>" value="<?= $escapeVuca($line['id_company']) ?>"><input type="hidden" name="nm_company_<?= $n ?>" value="<?= $escapeVuca($line['nm_company']) ?>"></td>
    <td><span <?= $revenue?'data-vuca-sales-name':'' ?>><?= $escapeVuca($line['nm_coa']) ?></span><input type="hidden" name="nm_coa_<?= $n ?>" <?= $revenue?'data-vuca-name-input':'' ?> value="<?= $escapeVuca($line['nm_coa']) ?>"></td>
    <td class="text-right"><?= number_format((float)$line['debit'],2) ?><input type="hidden" name="debit_<?= $n ?>" value="<?= $escapeVuca($line['debit']) ?>"></td>
    <td class="text-right"><?= number_format((float)$line['kredit'],2) ?><input type="hidden" name="kredit_<?= $n ?>" value="<?= $escapeVuca($line['kredit']) ?>"></td>
</tr>
<?php endforeach; ?>
