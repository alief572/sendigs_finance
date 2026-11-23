<?php
$escape=function($value){return htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');};
if (!empty($error)) { echo '<p>'.$escape($error).'</p>'; return; }
$owner=(string)$snapshot['owner']['id'];
$logo=$owner==='3'?'sentral_sustain.png':($owner==='4'?'logo_vuca.png':'logo_sentral.png');
$company=$owner==='3'?'PT. SENTRAL SUSTAINABILITY CONSULTING':($owner==='4'?'PT. VUCA STRATEGI BISNIS':'PT. SENTRAL TEHNOLOGI MANAGEMEN');
$bank=$owner==='3'?'BCA Tebet Barat Acc. No 436.400.0300':($owner==='4'?'OCBC NISP Acc. No 7788.0000.0417':'BCA Tebet Barat Acc. No 436.300.5287');
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><title>Invoice <?= $escape($invoice['no_invoice']) ?></title>
<style>body{font:14px Georgia,serif;max-width:1000px;margin:24px auto;color:#111}table{width:100%;border-collapse:collapse;margin:20px 0}th,td{padding:9px;border:1px solid #555;text-align:left;vertical-align:top}.num{text-align:right}.logo{max-width:350px;max-height:110px}dl{display:grid;grid-template-columns:160px 1fr;gap:8px}dd{margin:0}.summary{max-width:500px;margin-left:auto}@media print{body{margin:0}}</style></head><body>
<img class="logo" src="<?= base_url('assets/images/'.$logo) ?>" alt="<?= $escape($snapshot['owner']['nm_company']) ?>">
<h2><?= $company ?></h2>
<?php if ($owner==='7'): ?><p>Jl. MT. Haryono Kav. 10, Jakarta Timur 13330<br>Telp. (021) 29067201, 29067202 Fax. (021) 29067204<br>Email: sspm@sentralsistem.com · Website: www.sentralsistem.com</p><?php endif; ?>
<h1>INVOICE</h1>
<dl><?php foreach (array('Nomor invoice'=>$invoice['no_invoice'],'Tanggal'=>$invoice['tanggal_invoice'],'Kepada'=>$invoice['nm_customer'],'Alamat'=>$invoice['address'],
    'Nomor penawaran'=>$invoice['id_penawaran'],'Nomor PO'=>$invoice['no_po'],'Nomor faktur'=>$invoice['no_faktur'],'Perlakuan PPN'=>Invoice_consulting::tax_treatments()[$invoice['tax_treatment']]) as $label=>$value): ?>
    <dt><?= $label ?></dt><dd><?= nl2br($escape($value)) ?></dd><?php endforeach; ?></dl>
<table><thead><tr><th scope="col">Item</th><th scope="col">Qty</th><th scope="col">Harga (Rp)</th><th scope="col">Total (Rp)</th></tr></thead><tbody>
<?php foreach ($items as $item): ?><tr><td><?= nl2br($escape($item['nm_item'])) ?></td><td class="num"><?= $escape($item['qty']) ?></td><td class="num"><?= number_format((float)$item['harga'],2) ?></td><td class="num"><?= number_format((float)$item['total'],2) ?></td></tr><?php endforeach; ?>
</tbody></table>
<table class="summary"><tbody><?php foreach (array('Biaya kirim'=>'biaya_kirim','Penjualan termasuk biaya kirim'=>'total_nominal','DPP'=>'dpp_nilai_lain','PPN'=>'ppn_jurnal','Total tagihan'=>'tagihan_ppn_jurnal','Piutang awal'=>'total_akhir_jurnal') as $label=>$field): ?>
<tr><th scope="row"><?= $label ?></th><td class="num"><?= number_format((float)$invoice[$field],2) ?></td></tr><?php endforeach; ?></tbody></table>
<?php if (!empty($invoice['print_keterangan'])): ?><p><?= nl2br($escape($invoice['print_keterangan'])) ?></p><?php endif; ?>
<p>Pembayaran harus dilakukan paling lambat 14 hari setelah invoice diterima.</p>
<p>Pembayaran ditransfer ke:<br><strong><?= $company ?><br><?= $bank ?></strong><br>Bukti pembayaran mohon di email ke: Finance@sentralsistem.com</p>
<p style="text-align:right;margin-top:60px"><?= $owner==='3'?'Cahyadi':'Imanuel Iman' ?><br>Direktur</p>
<script>window.print();</script>
</body></html>
