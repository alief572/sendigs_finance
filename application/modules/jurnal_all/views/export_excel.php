<?php
$e=function($value){$value=(string)$value;if(preg_match('/^[\s]*[=+@-]/u',$value))$value="'".$value;return htmlspecialchars($value,ENT_QUOTES,'UTF-8');};
?>
<html><head><meta charset="UTF-8"></head><body><table border="1"><thead><tr>
<th>Status</th><th>Jenis Jurnal</th><th>Tgl Jurnal</th><?php if($posted): ?><th>No. Jurnal Accounting (Transaksi)</th><th>Tgl Posting</th><?php endif; ?>
<th>No. Transaksi</th><th>No. Referensi</th><th>Kategori Transaksi</th><th>Company</th><th>Divisi</th><th>No. COA</th><th>Nama COA</th><th>Keterangan</th><th>Debit</th><th>Kredit</th>
</tr></thead><tbody>
<?php foreach($rows as $item):$h=$item['header'];$l=$item['line']; ?>
<tr><td><?= $e($h['group_status'] ?? ($posted?'Sudah posting':'Belum posting')) ?></td><td><?= $e(Jurnal_all_model::TITLES[$h['tab']]) ?></td><td><?= $e($l['tgl_jurnal']) ?></td>
<?php if($posted): ?><td style='mso-number-format:"\@"'><?= $e($h['source_numbers'] ?? '-') ?></td><td style='mso-number-format:"\@"'><?= $e(!empty($h['posted_at']) ? substr((string)$h['posted_at'], 0, 10) : '-') ?></td><?php endif; ?>
<?php foreach([$h['display_no_transaksi'] ?? $h['no_transaksi'],$h['no_referensi'],$h['category'],$l['nm_company'],$l['nm_divisi'],$l['coa'],$l['nm_coa'],$l['keterangan']] as $value): ?><td style='mso-number-format:"\@"'><?= $e($value) ?></td><?php endforeach; ?>
<td style='mso-number-format:"0.00"'><?= $e($l['debit']) ?></td><td style='mso-number-format:"0.00"'><?= $e($l['kredit']) ?></td></tr>
<?php endforeach; ?></tbody></table></body></html>
