<?php
require __DIR__.'/Ui_render_test.php';

$before=$GLOBALS['checks'];
$printInvoice=$invoice+[
    'dpp_nilai_lain'=>'917.17','ppn_jurnal'=>'110.06','tagihan_ppn_jurnal'=>'1110.61',
    'consulting_version'=>3,'print_keterangan'=>''
];
foreach (['7'=>'logo_sentral.png','3'=>'sentral_sustain.png','4'=>'logo_vuca.png'] as $owner=>$logo) {
    foreach (['PKP','WAPU','KB','UMKM'] as $tax) {
        $data=$base;
        $data['invoice']=$printInvoice;
        $data['invoice']['tax_treatment']=$tax;
        if ($tax==='KB' || $tax==='UMKM') {
            $data['invoice']['ppn_jurnal']='0.00';
            $data['invoice']['tagihan_ppn_jurnal']='1000.55';
        }
        $data['snapshot']['owner']['id']=$owner;
        $html=$renderer->render('consulting_print',$data);
        $xpath=htmlQuery($html);
        verifyUi(strpos($html,$logo)!==false,'Original letterhead restored '.$owner);
        verifyUi($xpath->query('//table[contains(@class,"table_list_barang")]')->length===1,'Original invoice table');
        verifyUi(strpos($html,'Pembayaran di Transfer ke')!==false,'Original bank instructions');
        verifyUi(strpos($html,'Direktur')!==false && strpos($html,'window.print()')!==false,'Original signature and print dialog');
        verifyUi(strpos($html,'Deskripsi uji &lt;script&gt;')!==false && strpos($html,'<script>alert(1)</script>')===false,'Snapshot description and customer escaped');
        $total=$xpath->query('//tr[td[normalize-space(.)="TOTAL"]]/td[last()]//td[last()]')->item(0);
        $vat=$xpath->query('//tr[td[normalize-space(.)="Pajak 12%"]]/td[last()]//td[last()]')->item(0);
        verifyUi($total && trim($total->textContent)===number_format((float)$data['invoice']['tagihan_ppn_jurnal'],2),'Saved gross retained '.$tax);
        verifyUi($vat && trim($vat->textContent)===number_format((float)$data['invoice']['ppn_jurnal'],2),'Saved VAT retained '.$tax);
    }
}
$data=$base;$data['invoice']=$printInvoice;$data['invoice']['print_keterangan']='Keterangan cetak tersimpan';
$html=$renderer->render('consulting_print',$data);
verifyUi(strpos($html,'Keterangan cetak tersimpan')!==false,'Existing print description retained');
$html=$renderer->render('consulting_print',['error'=>'Gagal <script>alert(1)</script>']);
verifyUi(strpos($html,'&lt;script&gt;')!==false && strpos($html,'window.print()')===false,'Error remains escaped without invoice');
$legacy=(object)$printInvoice;$legacy->consulting_version=0;
$html=$renderer->render('print_invoice',['data_invoice'=>$legacy,'data_actual_plan_tagih'=>(object)['nominal_payment'=>2000],'id_company'=>'7']);
verifyUi(strpos($html,number_format(2000+917.17*.12))!==false,'Legacy total calculation retained');
$data=$base;$data['invoice']=$printInvoice;
$data['invoice']['total_nominal']='291000000.00';
$data['invoice']['dpp_nilai_lain']='266750000.00';
$data['invoice']['ppn_jurnal']='32010000.00';
$data['invoice']['tagihan_ppn_jurnal']='323010000.00';
$html=$renderer->render('consulting_print',$data);
foreach (['291,000,000.00','266,750,000.00','32,010,000.00','323,010,000.00'] as $amount) {
    verifyUi(strpos($html,$amount)!==false,'Screenshot invoice amount '.$amount);
}
// invoice lama hasil impor ('v'): tanpa baris plan tagih, dpp/pajak/total_akhir NULL (kasus 00328-INV-XII-25)
$imported=(object)['id'=>'00328-INV-XII-25','no_invoice'=>'028/VSB/2025','tanggal_invoice'=>'2025-10-10','nm_customer'=>'PT Contoh','address'=>'Jakarta','no_po'=>'PO-1','print_keterangan'=>'Jasa',
    'total_nominal'=>'3000000.00','dpp_nilai_lain'=>null,'pajak'=>null,'total_akhir'=>null,'ppn_persen'=>'0.00','id_actual_plan_tagih'=>'v','tipe_invoice'=>'0'];
$realPlan=(object)['nominal_payment'=>'2000'];
verifyUi(Invoice_calculation::plan_or_invoice($realPlan,$imported)===$realPlan,'Existing plan row is returned untouched');
foreach([null,false] as $missing) {
    $plan=Invoice_calculation::plan_or_invoice($missing,$imported);
    verifyUi($plan instanceof stdClass && $plan->nominal_payment==='3000000.00','Missing plan row falls back to the invoice total_nominal');
}
foreach(['print_invoice','print_invoice_vuca'] as $view) {
    $html=$renderer->render($view,['data_invoice'=>$imported,'data_actual_plan_tagih'=>Invoice_calculation::plan_or_invoice(null,$imported),'id_company'=>'4']);
    verifyUi(strpos($html,'3,000,000')!==false,$view.' shows the imported invoice nominal');
    verifyUi(strpos($html,'Notice')===false && strpos($html,'non-object')===false,$view.' renders without PHP notices');
}
$html=$renderer->render('print_invoice',['data_invoice'=>$imported,'data_actual_plan_tagih'=>Invoice_calculation::plan_or_invoice(null,$imported),'id_company'=>'4']);
verifyUi(substr_count($html,'3,000,000')>=3,'Imported invoice nominal appears on rows, subtotal and total');
// regresi: tanpa fallback (pra-perbaikan) nominal tercetak 0, dengan fallback tidak
$noFallback=$renderer->render('print_invoice',['data_invoice'=>$imported,'data_actual_plan_tagih'=>(object)['nominal_payment'=>null],'id_company'=>'4']);
verifyUi(strpos($noFallback,'3,000,000')===false,'Control: without the fallback the nominal would not be printed');
// halaman View invoice lama (view_invoicing): baris plan tagih pengganti, tanpa notice
$importedView=(object)array_merge((array)$imported,['no_faktur'=>'','total_nominal_jurnal'=>null,'ppn_jurnal'=>null,'pph_jurnal'=>null,'total_akhir_jurnal'=>null]);
$viewData=['data_invoice'=>$importedView,'data_actual_plan_tagih'=>Invoice_calculation::plan_or_invoice(null,$importedView),'hasil_jurnal'=>'','total_debit'=>0,'total_kredit'=>0];
$html=$renderer->render('view_invoice',$viewData);
verifyUi(strpos($html,'Notice')===false && strpos($html,'non-object')===false,'View of an imported invoice renders without PHP notices');
verifyUi(strpos($html,'<td class="text-right">3,000,000.00</td>')!==false && strpos($html,'data-total="sales">3,000,000.00')!==false,'View shows the imported invoice nominal in the TOP table and the summary');
verifyUi(strpos($html,'<td class="text-center">-</td>')!==false && strpos($html,'<td class="text-center">0.00%</td>')===false,'View shows a dash, not 0.00%, for the unknown payment percent');
$plan=Invoice_calculation::plan_or_invoice(null,$importedView);
verifyUi($plan->nm_company==='-' && $plan->id_company==='' && $plan->term_payment==='-' && $plan->desc_payment==='-' && $plan->persen_payment===null,'Fallback row carries neutral markers for unknown fields');
$realRow=(object)['nominal_payment'=>'2000000','persen_payment'=>'30','term_payment'=>'TOP 1','desc_payment'=>'Termin pertama','nm_company'=>'PT A','id_company'=>'7'];
$viewData['data_actual_plan_tagih']=$realRow;
$html=$renderer->render('view_invoice',$viewData);
verifyUi(strpos($html,'30.00%')!==false && strpos($html,'TOP 1')!==false && strpos($html,'<td class="text-right">2,000,000.00</td>')!==false && strpos($html,'Termin pertama')!==false,'View of an invoice with a real plan row shows its term, percent, nominal and description');
// customer invoice lama kosong di tr_invoicing: diisi dari SPK, nilai yang sudah ada tidak ditimpa
$spkRow=(object)['nm_customer'=>'PT Waterco Indonesia','address'=>'Ruko Plaza Kelapa Gading'];
$blank=(object)['id'=>'00328-INV-XII-25','nm_customer'=>null,'address'=>null];
$filled=Invoice_calculation::fill_customer($blank,$spkRow);
verifyUi($filled->nm_customer==='PT Waterco Indonesia' && $filled->address==='Ruko Plaza Kelapa Gading','Empty customer name and address are filled from the SPK');
$keep=Invoice_calculation::fill_customer((object)['nm_customer'=>'PT Tersimpan','address'=>''],$spkRow);
verifyUi($keep->nm_customer==='PT Tersimpan' && $keep->address==='Ruko Plaza Kelapa Gading','A stored name is never overwritten; only the empty field is filled');
verifyUi(Invoice_calculation::fill_customer((object)['nm_customer'=>'  ','address'=>null],$spkRow)->nm_customer==='PT Waterco Indonesia','Whitespace-only counts as empty');
verifyUi(Invoice_calculation::fill_customer($blank,null)===$blank && Invoice_calculation::fill_customer(null,$spkRow)===null,'Missing SPK or invoice is returned unchanged');
$spkEmpty=Invoice_calculation::fill_customer((object)['nm_customer'=>null,'address'=>null],(object)['nm_customer'=>'','address'=>null]);
verifyUi($spkEmpty->nm_customer===null && $spkEmpty->address===null,'An empty SPK value does not replace anything');
$filledImported=(object)array_merge((array)$importedView,['nm_customer'=>$filled->nm_customer,'address'=>$filled->address]);
foreach(['view_invoice'=>['data_invoice'=>$filledImported,'data_actual_plan_tagih'=>Invoice_calculation::plan_or_invoice(null,$filledImported),'hasil_jurnal'=>'','total_debit'=>0,'total_kredit'=>0],
         'print_invoice'=>['data_invoice'=>$filledImported,'data_actual_plan_tagih'=>Invoice_calculation::plan_or_invoice(null,$filledImported),'id_company'=>'4'],
         'print_invoice_vuca'=>['data_invoice'=>$filledImported,'data_actual_plan_tagih'=>Invoice_calculation::plan_or_invoice(null,$filledImported),'id_company'=>'4']] as $view=>$vars) {
    $html=$renderer->render($view,$vars);
    verifyUi(strpos($html,'PT Waterco Indonesia')!==false && strpos($html,'Ruko Plaza Kelapa Gading')!==false,$view.' shows the customer name and address from the SPK');
}
$noCustomer=(object)array_merge((array)$importedView,['nm_customer'=>null,'address'=>null]);
$html=$renderer->render('view_invoice',['data_invoice'=>$noCustomer,'data_actual_plan_tagih'=>Invoice_calculation::plan_or_invoice(null,$noCustomer),'hasil_jurnal'=>'','total_debit'=>0,'total_kredit'=>0]);
verifyUi(strpos($html,'invoice-customer-name"></dd>')!==false,'Control: without the fallback the customer name is blank');
// tata letak sama seperti halaman Buat Invoice (consulting_invoice.php), hanya baca
$full=(object)array_merge((array)$importedView,['nm_customer'=>'PT Contoh <script>alert(1)</script>','address'=>"Jl. A
Jakarta",'no_po'=>'PO-77','no_faktur'=>'FP-88','total_nominal'=>'1000.00',
    'dpp_nilai_lain'=>'917.17','ppn_jurnal'=>'110.06','pph_jurnal'=>'20.00','ppn_persen'=>'12.00']);
$rows='<tr><td class="text-center">10 Okt 2025</td><td class="text-center">1102-01-01</td><td class="text-center">PT Vuca</td><td class="text-center">Piutang</td><td class="text-right">1,090</td><td class="text-right">0</td></tr>';
$html=$renderer->render('view_invoice',['data_invoice'=>$full,'data_actual_plan_tagih'=>$realRow,'hasil_jurnal'=>$rows,'total_debit'=>1090,'total_kredit'=>1090]);
$xp=htmlQuery($html);
verifyUi($xp->query('//div[contains(@class,"invoice-workspace") and contains(@class,"consulting-invoice")]')->length===1 && strpos($html,'assets/css/invoicing.css')!==false,'View uses the invoice workspace layout and stylesheet of the add page');
foreach(['Detail invoice konsultasi','Customer &amp; dokumen','Rincian TOP','Ringkasan tagihan','Jurnal invoice','Kembali ke daftar'] as $label) { verifyUi(strpos($html,$label)!==false,'View section: '.$label); }
verifyUi($xp->query('//section[contains(@class,"invoice-summary")]')->length===1 && $xp->query('//div[contains(@class,"invoice-document-grid")]')->length===1 && $xp->query('//table[contains(@class,"invoice-journal-table")]')->length===1 && $xp->query('//table[contains(@class,"invoice-top-table")]')->length===1,'View has the same section structure as the add page');
verifyUi(strpos($html,'<script>alert(1)</script>')===false && strpos($html,'PT Contoh &lt;script&gt;')!==false,'View escapes customer data');
verifyUi(strpos($html,'Jl. A<br />')!==false || strpos($html,"Jl. A<br />
Jakarta")!==false || strpos($html,'Jl. A<br')!==false,'Address keeps its line breaks');
$cell=function($key) use ($xp){ $n=$xp->query('//*[@data-total="'.$key.'"]')->item(0); return $n?trim($n->textContent):null; };
verifyUi($cell('sales')==='1,000.00' && $cell('other')==='917.17' && $cell('ppn')==='110.06' && $cell('gross')==='1,110.06' && $cell('pph')==='- 20.00' && $cell('net')==='1,090.06','Summary numbers keep the existing calculation (total + PPN - PPh)');
verifyUi(strpos($html,'PPN 12.00%')!==false && strpos($html,'value="PO-77"')!==false && strpos($html,'value="FP-88"')!==false && strpos($html,'value="12.00%"')!==false,'Document fields shown read-only');
verifyUi(strpos($html,'PT Vuca')!==false && strpos($html,'1,090')!==false && $xp->query('//th[contains(@class,"th_total_debit")]')->item(0)->textContent==='1,090','Journal rows and balancing totals are shown');
verifyUi(strpos($html,'invoice-empty')===false,'No empty-state row when journal rows exist');
$html=$renderer->render('view_invoice',['data_invoice'=>$full,'data_actual_plan_tagih'=>$realRow,'hasil_jurnal'=>'','total_debit'=>0,'total_kredit'=>0]);
verifyUi(strpos($html,'Rincian jurnal belum tersedia.')!==false,'Empty journal shows the empty-state row');
verifyUi(strpos($html,'<form')===false && strpos($html,'type="submit"')===false,'View has no form or submit button');
// pemilihan layout cetak di daftar: Vuca (print_invoicing_vuca) atau konsultasi (print_invoicing)
$tipeVuca=['tipe_invoice'=>'1','consulting_version'=>null];
$legacyVuca=['tipe_invoice'=>'0','consulting_version'=>null,'non_kons'=>'0','id_actual_plan_tagih'=>'v'];
$newFlow=['tipe_invoice'=>'0','consulting_version'=>3,'non_kons'=>'0'];
verifyUi(Invoice_consulting::print_as_vuca($tipeVuca,false)===true && Invoice_consulting::print_as_vuca($tipeVuca,true)===true,'Vuca-type invoice always prints with the Vuca layout');
verifyUi(Invoice_consulting::print_as_vuca($legacyVuca,true)===true,'Old invoice of a Vuca company prints with the Vuca layout (00328-style imported invoice)');
verifyUi(Invoice_consulting::print_as_vuca((object)$legacyVuca,true)===true,'Row objects work like arrays');
verifyUi(Invoice_consulting::print_as_vuca($legacyVuca,false)===false,'Old invoice of another company keeps the consulting layout');
verifyUi(Invoice_consulting::print_as_vuca(['id'=>'X'],false)===false && Invoice_consulting::print_as_vuca(['id'=>'X'],true)===true,'Missing columns count as an old invoice');
verifyUi(Invoice_consulting::print_as_vuca($newFlow,true)===false,'New consulting-flow invoice owned by Vuca keeps the consulting layout and owner letterhead');
verifyUi(Invoice_consulting::print_as_vuca($newFlow,false)===false,'New consulting-flow invoice of another company unchanged');
verifyUi(Invoice_consulting::print_as_vuca(array_merge($newFlow,['tipe_invoice'=>'1']),false)===true,'tipe_invoice 1 wins over the consulting flag');
echo 'Print invoice: '.($GLOBALS['checks']-$before)." assertions passed\n";
