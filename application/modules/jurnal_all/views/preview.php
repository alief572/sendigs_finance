<?php
$e=function($value){return htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');};
$h=$detail['header'];
$debt=!empty($h['debt_group']);
$perCompany=$debt||($h['source']==='PCS'&&($h['jenis_transaksi'] ?? '')==='Petty Cash');
$format=function($value){$parts=explode('.',(string)$value);return preg_replace('/\B(?=(\d{3})+(?!\d))/',',',$parts[0]).'.'.str_pad($parts[1] ?? '',2,'0');};
?>
<?php if (!$posted && !empty($h['payment_reference_error'])): ?>
<p class="alert alert-warning"><?= $e($h['payment_reference_error']) ?></p>
<?php endif; ?>
<?php if (!$posted && !empty($h['company_error'])): ?>
<p class="alert alert-warning"><?= $e($h['company_error']) ?></p>
<?php endif; ?>
<dl class="ja-meta">
    <div><dt>No. Transaksi</dt><dd><?= $e(($h['display_no_transaksi'] ?? $h['no_transaksi']) ?: '—') ?></dd></div>
    <div><dt>No. Referensi</dt><dd><?= $e($h['no_referensi']) ?></dd></div>
    <div><dt>Company</dt><dd><?= $e($h['nm_company']) ?></dd></div>
    <?php if (!$posted && !empty($h['accounting_target'])): ?><div><dt>Tujuan TRAS</dt><dd><?= $e(['accounting_vuca'=>'VUCA','accounting_sustain'=>'Sustain','accounting_stm'=>'STM'][$h['accounting_target']] ?? '-') ?></dd></div><?php endif; ?>
    <div><dt>Divisi</dt><dd><?= $e($h['nm_divisi']) ?></dd></div>
    <div><dt>Tgl Jurnal</dt><dd><?= $e($h['tgl_jurnal']) ?></dd></div>
    <div><dt>Status</dt><dd><?= $e($h['group_status'] ?? ($posted?'Sudah posting':'Belum posting')) ?></dd></div>
    <?php if($debt): ?><div><dt><?= empty($h['relation_warning'])?'Nominal pembayaran':'Nominal sumber (belum terverifikasi)' ?></dt><dd><?= $e($format($h['debit'])) ?></dd></div><?php endif; ?>
    <?php if($posted): ?>
    <div><dt>No. Jurnal Accounting</dt><dd><?= $e($h['source_numbers'] ?: '-') ?></dd></div>
    <div><dt>Tgl Posting</dt><dd><?= $e($h['posted_at'] ?? '-') ?><?= !empty($h['posted_at'])?' WIB':'' ?></dd></div>
    <div><dt>Diposting Oleh</dt><dd><?= $e($h['posted_by'] ?? '-') ?></dd></div>
    <?php endif; ?>
</dl>
<?php $sections=$perCompany ? $detail['company_sections'] : [['rows'=>$detail['rows'],'debit'=>$h['debit'],'kredit'=>$h['kredit']]];
foreach($sections as $index=>$section): ?>
<?php if($perCompany): ?><section class="ja-company-section" aria-labelledby="ja-company-<?= $index ?>">
    <h3 id="ja-company-<?= $index ?>">Sisi <?= $e($section['nm_company']) ?></h3>
    <?php if($debt): ?><p><?= $e($section['status']) ?> | Accounting: <?= $e($section['accounting_status'] ?? '-') ?> | No. Accounting: <?= $e($section['accounting_number'] ?? '-') ?></p><?php endif; ?>
<?php endif; ?>
<div class="table-responsive"><table class="table table-bordered ja-lines"><thead><tr>
    <th>Tanggal</th><th>Tipe</th><?php if($h['source']==='PCS'): ?><th>Company</th><?php endif; ?><th>No. COA</th><th>Nama COA</th><th>Keterangan</th><th>No. Reff</th><th>Debit</th><th>Kredit</th>
</tr></thead><tbody>
<?php foreach($section['rows'] as $line):
    if((float)$line['debit']==0&&(float)$line['kredit']==0) continue;
    $reference=$line['no_transaksi']; $description=$line['keterangan'];
    if($debt)$reference=$section['no_referensi'];
    if(!$posted&&$h['jenis_transaksi']==='Payment'){
        $reference=$h['payment_voucher'] ?? '-';
        $description=trim(preg_replace('/\{REF:.*?\}/','',(string)$description));
    } elseif(in_array($h['source'],['PAY','EXP'],true)&&preg_match('/\{REF:(.*?)\}/',(string)$description,$matches)){$reference=$matches[1];$description=trim(str_replace($matches[0],'',$description));}
    if(!$posted&&$h['source']==='INV'){$reference=$h['invoice_number'];$description=explode(' - ',(string)$description)[0].' - '.$reference;}
?>
<tr><td><?= $e($line['tgl_jurnal']) ?></td><td><?= $e($line['jenis_transaksi']) ?></td><?php if($h['source']==='PCS'): ?><td><?= $e($line['nm_company']) ?></td><?php endif; ?><td><?= $e($line['coa']) ?></td><td><?= $e($line['nm_coa']) ?></td>
<td><?php if(!$posted&&$h['source']==='PAY'): ?><textarea class="form-control" name="jurnal[<?= (int)$line['id'] ?>][keterangan]" rows="2"><?= $e($description) ?></textarea><?php else: ?><?= nl2br($e($description)) ?><?php endif; ?></td>
<td><?= $e($reference) ?></td><td class="ja-money"><?= $e($format($line['debit'])) ?></td><td class="ja-money"><?= $e($format($line['kredit'])) ?></td></tr>
<?php endforeach; ?>
</tbody><tfoot><tr><th colspan="<?= $h['source']==='PCS'?7:6 ?>"><?= $perCompany?'TOTAL COMPANY':'GRAND TOTAL' ?></th><th class="ja-money"><?= $e($format($section['debit'])) ?></th><th class="ja-money"><?= $e($format($section['kredit'])) ?></th></tr></tfoot></table></div>
<?php if($perCompany): ?>
<p class="<?= $section['is_balance']?'text-success':'text-danger' ?>"><?= $section['is_balance']?'Debit dan kredit company ini seimbang.':'Debit dan kredit company ini tidak seimbang atau nominal tidak valid.' ?></p>
</section><?php endif; ?>
<?php endforeach; ?>
<?php if($debt): ?>
<p>Total seluruh jurnal accounting: Debit <?= $e($format($h['accounting_debit'])) ?> / Kredit <?= $e($format($h['accounting_kredit'])) ?></p>
<?php foreach($detail['blockers'] as $reason): ?><p class="text-danger"><?= $e($reason) ?></p><?php endforeach; ?>
<?php if(!$posted&&empty($detail['posting_ready'])): ?><p class="text-danger">Posting gabungan belum diaktifkan. Hubungi administrator.</p><?php endif; ?>
<?php endif; ?>
<p class="<?= $detail['is_balance']?'text-success':'text-danger' ?>"><?= $detail['is_balance']?($perCompany?'Total debit dan kredit seluruh company seimbang.':'Debit dan kredit seimbang.'):'Debit dan kredit tidak seimbang atau nominal tidak valid. Posting tidak tersedia.' ?></p>
