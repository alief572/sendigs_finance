<?php
ob_start();require __DIR__.'/legacy_vuca_preview_test.php';ob_end_clean();
foreach(array('invoice','deposit')as $kind){
 foreach(array(array('2025-09-17',null,'17-Sep-2025'),array('2025-09-17','2024-01-01','17-Sep-2025'),array(null,'2024-01-01','-'),array('0000-00-00','2024-01-01','-'))as $case){
  $db->invoice['tanggal_invoice']=$case[0];$db->invoice['created_date']=$case[1];$db->invoice['consulting_version']=null;$db->invoice['tax_treatment']=$kind==='deposit'?'DEP':'PKP';
  $c->input->values=array('nm_customer'=>'Customer','receipt_kind'=>$kind,'pph23_dipotong'=>'N');
  $before=$db->invoice;ob_start();$c->get_inv_by_cust();$html=ob_get_clean();
  preg_match('/<tr>\s*<td[^>]*>(.*?)<\/td>/s',$html,$match);
  expectLegacy(($match[1]??null)===$case[2],'List date must come from tanggal_invoice: '.$kind.' '.json_encode($case));
  expectLegacy($db->invoice===$before,'Date display must not mutate invoice');
 }
}
// Invoice lama tanpa perlakuan PPN tersimpan: label "-" (tidak menebak), tetapi Total Tagihan tetap penjualan + PPN.
$db->invoice['tanggal_invoice']='2025-09-17';$db->invoice['consulting_version']=null;$db->invoice['tax_treatment']=null;$db->invoice['consulting_snapshot']=null;
$db->invoice['total_nominal_jurnal']='15000000.00';$db->invoice['ppn_jurnal']='1650000.00';
$c->input->values=array('nm_customer'=>'Customer','receipt_kind'=>'invoice','pph23_dipotong'=>'N');
ob_start();$c->get_inv_by_cust();$legacyHtml=ob_get_clean();
expectLegacy(strpos($legacyHtml,'Perlakuan PPN belum tercatat')!==false && strpos($legacyHtml,'tax-badge tax-PKP')===false && strpos($legacyHtml,'tax-badge tax-WAPU')===false,'Legacy invoice without stored tax treatment shows "-" instead of a guessed badge');
expectLegacy(strpos($legacyHtml,'16,650,000')!==false,'Legacy PKP-like invoice keeps Total Tagihan = penjualan + PPN');
$db->invoice['tax_treatment']='PKP';
ob_start();$c->get_inv_by_cust();$storedHtml=ob_get_clean();
expectLegacy(strpos($storedHtml,'tax-badge tax-PKP')!==false && strpos($storedHtml,'Perlakuan PPN belum tercatat')===false,'Stored tax treatment still renders its badge');
echo "PASS: invoice/kwitansi list date uses tanggal_invoice, independent of created_date, with missing/zero-date handling, and no guessed tax label\n";
