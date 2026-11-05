<?php
require __DIR__.'/Ui_render_test.php';

$before=$GLOBALS['checks'];
$printInvoice=$invoice+[
    'dpp_nilai_lain'=>'917.17','ppn_jurnal'=>'110.06','tagihan_ppn_jurnal'=>'1110.61',
    'consulting_version'=>3,'print_keterangan'=>''
];
foreach (['7'=>'logo_sentral.png','3'=>'sentral_sustain.png','4'=>'logo_vuca.png'] as $owner=>$logo) {
    foreach (['PKP','WAPU','KB'] as $tax) {
        $data=$base;
        $data['invoice']=$printInvoice;
        $data['invoice']['tax_treatment']=$tax;
        if ($tax==='KB') {
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
