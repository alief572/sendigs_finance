<?php
$escape=function($value){return htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');};
if(!empty($error)){echo '<p>'.$escape($error).'</p>';return;}
$top=$snapshot['top'];
?>
<!doctype html>
<html lang="id"><head><meta charset="utf-8"><title>Invoice <?= $escape($invoice['no_invoice']) ?></title>
<style>
body{font:14px Arial,sans-serif;color:#111;max-width:900px;margin:32px auto;padding:0 24px;line-height:1.5}
header{display:flex;justify-content:space-between;align-items:baseline;border-bottom:2px solid #222;margin-bottom:24px}
h1{font-size:28px}h2{font-size:18px}.metadata{width:100%;margin-bottom:24px}.metadata th{text-align:left;width:130px;vertical-align:top}
table.items{width:100%;border-collapse:collapse}.items th,.items td{border:1px solid #aaa;padding:9px;text-align:left;vertical-align:top}
.money{text-align:right!important;white-space:nowrap}.totals{width:50%;margin:20px 0 40px auto;border-collapse:collapse}.totals th{text-align:left}.totals td,.totals th{padding:6px}.total{border-top:2px solid #222;font-weight:bold}
@media print{body{margin:0;max-width:none;padding:0}thead{display:table-header-group}tr{break-inside:avoid}}
</style></head><body>
<header><h2><?= $escape($snapshot['owner']['nm_company']) ?></h2><h1>INVOICE</h1></header>
<table class="metadata"><tr><th>Kepada</th><td><?= $escape($invoice['nm_customer']) ?></td><th>Nomor Invoice</th><td><?= $escape($invoice['no_invoice']) ?></td></tr>
<tr><th>Alamat</th><td><?= nl2br($escape($invoice['address'])) ?></td><th>Tanggal Invoice</th><td><?= $escape($invoice['tanggal_invoice']) ?></td></tr>
<tr><th>Ditujukan</th><td>Finance Dept.</td><th>Nomor PO</th><td><?= $escape($invoice['no_po']) ?></td></tr>
<tr><th>No. SPK</th><td><?= $escape($invoice['id_spk_penawaran']) ?></td><th>Nomor Faktur</th><td><?= $escape($invoice['no_faktur']) ?></td></tr>
<tr><th>Perlakuan PPN</th><td><?= $escape($invoice['tax_treatment']==='KB'?'Kawasan Berikat':$invoice['tax_treatment']) ?></td><th></th><td></td></tr></table>
<table class="items"><thead><tr><th>No.</th><th>Term of Payment</th><th>Persentase</th><th>Description</th><th>Nominal</th></tr></thead><tbody><tr>
<td>1</td><td><?= $escape($top['term_payment']) ?></td><td><?= number_format((float)$top['persen_payment'],2) ?>%</td><td><?= $escape($top['desc_payment']) ?></td><td class="money"><?= number_format((float)$invoice['total_nominal'],2) ?></td>
</tr></tbody></table>
<table class="totals"><tbody>
<?php foreach(array('total_nominal'=>'Penjualan','dpp_nilai_lain'=>'DPP','ppn_jurnal'=>'PPN','tagihan_ppn_jurnal'=>'Total Tagihan')as $field=>$label): ?>
<tr <?= $field==='tagihan_ppn_jurnal'?'class="total"':'' ?>><th><?= $label ?></th><td class="money">Rp <?= number_format((float)$invoice[$field],2) ?></td></tr>
<?php endforeach; ?></tbody></table>
<p>Hormat kami,</p><p><?= $escape($snapshot['owner']['nm_company']) ?></p>
</body></html>
