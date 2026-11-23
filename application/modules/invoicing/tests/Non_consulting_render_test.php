<?php
require __DIR__.'/Ui_render_test.php';
$ncInvoice=$invoice+array('id_penawaran'=>'NC-UI','biaya_kirim'=>'0.55','dpp_nilai_lain'=>'917.17','ppn_jurnal'=>'110.06','tagihan_ppn_jurnal'=>'1110.61','total_akhir_jurnal'=>'1110.61','print_keterangan'=>'<script>alert(1)</script>');
$ncItems=array(array('nm_item'=>'Item <script>alert(1)</script>','qty'=>'1.25','harga'=>'800.00','total'=>'1000.00'));
foreach(array('add','edit','view') as $ncMode) foreach(array('PKP','WAPU','KB','UMKM') as $ncTax){
    $data=$base;$data['invoice']=$ncInvoice;$data['invoice']['tax_treatment']=$ncTax;$data['mode']=$ncMode;$data['non_consulting']=true;$data['items']=$ncItems;$data['outstanding']='2000.00';
    $html=$renderer->render('consulting_invoice',$data);$xpath=htmlQuery($html);
    verifyUi(strpos($html,'<script>alert(1)</script>')===false,'Non consulting item escaping');
    verifyUi(strpos($html,'No. Penawaran')!==false && strpos($html,'Rincian TOP')===false,'Quote replaces TOP');
    verifyUi($xpath->query('//*[@data-item]')->length===1,'Item table on all modern modes');
    verifyUi($xpath->query('//*[@name="id_penawaran"]')->item(0)->getAttribute('value')==='NC-UI','Quote identity field');
    verifyUi($xpath->query('//*[@name="item[0][qty]"]')->length===($ncMode==='view'?0:1),'Qty editability');
    if($ncMode!=='view') {
        verifyUi($xpath->query('//*[@name="item[0][qty]"]')->item(0)->getAttribute('value')==='1.25','No integer qty rounding');
        verifyUi($xpath->query('//*[@name="biaya_kirim"]')->item(0)->getAttribute('type')==='hidden','Shipping is fixed');
        verifyUi(strpos($html,'non-consulting-form.js')!==false,'Item client loaded');
        verifyUi($xpath->query('//*[@id="consulting-form"]')->item(0)->getAttribute('action')===($ncMode==='edit'?'/invoicing/update_invoice_non_konsultasi':'/invoicing/save_invoice_non_konsultasi'),'Modern non consulting endpoint');
    }
}
foreach(array('7'=>'logo_sentral.png','3'=>'sentral_sustain.png','4'=>'logo_vuca.png') as $ncOwner=>$logo){
    $data=array('invoice'=>$ncInvoice,'items'=>$ncItems,'snapshot'=>array('owner'=>array('id'=>$ncOwner,'nm_company'=>'Owner')),'error'=>null);
    $html=$renderer->render('non_consulting_print',$data);
    verifyUi(strpos($html,$logo)!==false,'Print uses saved owner '.$ncOwner);
    verifyUi(strpos($html,'<script>alert(1)</script>')===false,'Print escapes customer, items and note');
    verifyUi(strpos($html,'1.25')!==false && strpos($html,'1,110.61')!==false,'Print keeps qty decimals and saved gross including Vuca');
}
$html=$renderer->render('non_consulting_print',array('error'=>'Missing <script>'));
verifyUi(strpos($html,'Missing &lt;script&gt;')!==false,'Print safe unavailable snapshot');
echo 'PASS: modern non consulting render/print; '.$GLOBALS['checks']." total UI checks\n";
