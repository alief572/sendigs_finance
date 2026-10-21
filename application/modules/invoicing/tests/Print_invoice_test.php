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
echo 'Print invoice: '.($GLOBALS['checks']-$before)." assertions passed\n";
