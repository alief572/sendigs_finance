<?php
define('BASEPATH',__DIR__); define('FCPATH',dirname(__DIR__,4).'/');
require dirname(__DIR__).'/libraries/Invoice_vuca.php';
function base_url($path=''){return '/'.$path;}
set_error_handler(function($severity,$message,$file,$line){throw new ErrorException($message,0,$severity,$file,$line);});
class VucaRender {
    function render($view,$data){extract($data);ob_start();include dirname(__DIR__).'/views/'.$view.'.php';return ob_get_clean();}
}
$checks=0;
function renderCheck($ok,$message){global $checks;$checks++;if(!$ok)throw new RuntimeException($message);}
$accounts=array();$i=1;foreach(Invoice_consulting::revenues() as $role=>$label)$accounts[$role]=array('code'=>'4101-01-0'.$i++,'label'=>$label,'name'=>'<script>alert(1)</script>');
$actual=(object)array('id'=>'A1','nominal_payment'=>'1000000.00','nm_customer'=>'<script>alert(1)</script>','address'=>"A & B\nAlamat",'id_spk_penawaran'=>'SPK"1','term_payment'=>'TOP','persen_payment'=>'100','desc_payment'=>'Selesai <img src=x onerror=alert(1)>');
$journals=array();foreach(array('1102-01-01','2104-01-07','1106-01-05','') as $code)$journals[]=array('coa'=>$code,'nm_coa'=>'<script>alert(1)</script>','id_company'=>'4','nm_company'=>'Vuca','tgl_jurnal'=>'2026-10-21','debit'=>$code==='1102-01-01'?995000:($code==='1106-01-05'?5000:0),'kredit'=>$code===''?1000000:0);
$renderer=new VucaRender;
$html=$renderer->render('add_invoice_vuca',array('data_actual'=>$actual,'accounts'=>$accounts,'journals'=>$journals,'preview_no_invoice'=>'<test>','error'=>null));
libxml_use_internal_errors(true);
$dom=new DOMDocument;$dom->loadHTML('<?xml encoding="UTF-8">'.$html);$xpath=new DOMXPath($dom);
renderCheck(strpos($html,'<script>alert(1)</script>')===false && strpos($html,'<img src=x')===false,'All customer, TOP and COA content escaped');
renderCheck($xpath->query('//*[@id="vuca-form" and @action="/invoicing/save_invoice_vuca"]')->length===1,'Existing save endpoint');
renderCheck($xpath->query('//select[@name="sales_role" and @required]/option')->length===5,'Four categories and empty placeholder');
renderCheck($xpath->query('//select[@name="tax_treatment" or @name="ppn_persen"]')->length===0,'No PPN selection');
foreach(array('nomor_po','nomor_faktur') as $name)renderCheck($xpath->query('//input[@name="'.$name.'" and @required]')->length===1,'Required document '.$name);
foreach(array('tanggal_invoice','nomor_invoice') as $name)renderCheck($xpath->query('//input[@name="'.$name.'" and @readonly]')->length===1,'Readonly '.$name);
foreach(array('sales'=>'1,000,000.00','pph'=>'- 5,000.00','net'=>'995,000.00') as $key=>$expected)renderCheck($xpath->query('//*[@data-total="'.$key.'"]')->item(0)->textContent===$expected,'Unchanged Vuca '.$key);
foreach(array('pajak'=>110000,'total_akhir'=>1110000,'pph_jurnal'=>5000,'total_akhir_jurnal'=>995000,'dpp_nilai_lain'=>1000000*11/12) as $name=>$expected)renderCheck(abs((float)$xpath->query('//input[@name="'.$name.'"]')->item(0)->getAttribute('value')-$expected)<0.00001,'Legacy hidden field '.$name);
renderCheck($xpath->query('//*[@id="vuca-journals" and @hidden]')->length===1,'Journal hidden until a category is selected');
renderCheck($xpath->query('//*[@role="region" and @tabindex="0"]')->length===2,'Keyboard accessible table scroll regions');
foreach($xpath->query('//label[@for]') as $label)renderCheck($xpath->query('//*[@id="'.$label->getAttribute('for').'"]')->length===1,'Labels target existing controls');
renderCheck(strpos($html,'https://cdn')===false && strpos($html,'save-feedback.js')!==false,'Local assets and shared feedback');
$error=$renderer->render('add_invoice_vuca',array('error'=>'Mapping <bad> tidak valid'));
renderCheck(strpos($error,'Mapping &lt;bad&gt; tidak valid')!==false && strpos($error,'<form')===false,'Configuration error stops unusable form');
// view_invoicing_vuca: tata letak sama seperti halaman Buat Invoice Vuca, hanya baca
require_once dirname(__DIR__).'/libraries/Invoice_calculation.php';
$stored=(object)array('id'=>'V1','no_invoice'=>'003/VSB/2026','id_spk_penawaran'=>'SPK-7','tanggal_invoice'=>'2026-02-09','nm_customer'=>'PT <b>Contoh</b>','address'=>"Jl. A\nJakarta",
    'no_po'=>'PO-9','no_faktur'=>'FP-9','total_nominal'=>'1000000.00','dpp_nilai_lain'=>'916666.67','pph_jurnal'=>'5000.00');
$planRow=(object)array('term_payment'=>'TOP 1','persen_payment'=>'50','nominal_payment'=>'1000000.00','desc_payment'=>'Termin <i>1</i>');
$viewJournals=array(
    array('coa'=>'1102-01-01','nm_coa'=>'Piutang','id_company'=>'4','nm_company'=>'Vuca','tgl_jurnal'=>'2026-02-09','debit'=>995000,'kredit'=>0),
    array('coa'=>'1106-01-05','nm_coa'=>'PPh Final','id_company'=>'4','nm_company'=>'Vuca','tgl_jurnal'=>'2026-02-09','debit'=>5000,'kredit'=>0),
    array('coa'=>'4101-01-02','nm_coa'=>'Penjualan Konsultasi','id_company'=>'4','nm_company'=>'Vuca','tgl_jurnal'=>'2026-02-09','debit'=>0,'kredit'=>1000000));
$viewData=array('data_invoice'=>$stored,'data_plan_tagih'=>$planRow,'hasil_jurnal'=>$renderer->render('vuca_journal_rows',array('journals'=>$viewJournals)),
    'total_debit'=>1000000,'total_kredit'=>1000000,'journals'=>$viewJournals);
$html=$renderer->render('view_invoice_vuca',$viewData);
$dom=new DOMDocument;@$dom->loadHTML('<?xml encoding="UTF-8">'.$html);$xpath=new DOMXPath($dom);
renderCheck($xpath->query('//div[contains(@class,"invoice-workspace") and contains(@class,"vuca-invoice")]')->length===1 && strpos($html,'assets/css/invoicing.css')!==false,'View uses the Vuca workspace layout and stylesheet of the add page');
foreach(array('Detail invoice Vuca','Customer &amp; dokumen','Rincian TOP','Akun penjualan','Ringkasan tagihan','Jurnal invoice','Kembali ke daftar') as $label)renderCheck(strpos($html,$label)!==false,'View section: '.$label);
renderCheck($xpath->query('//div[contains(@class,"invoice-document-grid")]')->length===1 && $xpath->query('//div[contains(@class,"invoice-financial-grid")]')->length===1 && $xpath->query('//section[contains(@class,"invoice-summary")]')->length===1 && $xpath->query('//table[contains(@class,"invoice-top-table")]')->length===1 && $xpath->query('//table[contains(@class,"invoice-journal-table")]')->length===1,'Same section structure as the add page');
renderCheck(strpos($html,'<b>Contoh</b>')===false && strpos($html,'PT &lt;b&gt;Contoh')!==false && strpos($html,'Termin <i>1</i>')===false,'Customer and TOP content escaped');
foreach(array('sales'=>'1,000,000.00','other'=>'916,666.67','pph'=>'- 5,000.00','net'=>'995,000.00') as $key=>$expected)renderCheck($xpath->query('//*[@data-total="'.$key.'"]')->item(0)->textContent===$expected,'View summary keeps the existing calculation: '.$key);
renderCheck(strpos($html,'value="PO-9"')!==false && strpos($html,'value="FP-9"')!==false && strpos($html,'value="003/VSB/2026"')!==false && strpos($html,'value="09 Feb 2026"')!==false,'Document fields shown read-only');
renderCheck(strpos($html,'4101-01-02 - Penjualan Konsultasi')!==false,'Sales account taken from the stored journal');
renderCheck($xpath->query('//th[contains(@class,"th_total_debit")]')->item(0)->textContent==='1,000,000.00' && strpos($html,'Piutang')!==false,'Journal rows and totals shown');
renderCheck(strpos($html,'<form')===false && strpos($html,'type="submit"')===false && strpos($html,'<script')===false,'View has no form, submit button or script');
foreach($xpath->query('//label[@for]') as $label)renderCheck($xpath->query('//*[@id="'.$label->getAttribute('for').'"]')->length===1,'View labels target existing controls');
$noJournal=$viewData;$noJournal['hasil_jurnal']='';$noJournal['journals']=array();$noJournal['total_debit']=0;$noJournal['total_kredit']=0;
$html=$renderer->render('view_invoice_vuca',$noJournal);
renderCheck(strpos($html,'Rincian jurnal belum tersedia.')!==false && strpos($html,'<div class="invoice-customer">-</div>')!==false,'Empty journal shows the empty-state row and a dash for the sales account');
// invoice lama: baris plan tagih tidak ada dan customer kosong di tr_invoicing
$bare=(object)array_merge((array)$stored,array('nm_customer'=>null,'address'=>null));
$filled=Invoice_calculation::fill_customer(clone $bare,(object)array('nm_customer'=>'PT Waterco Indonesia','address'=>'Ruko Plaza'));
$noPlan=$viewData;$noPlan['data_invoice']=$filled;$noPlan['data_plan_tagih']=Invoice_calculation::plan_or_invoice(null,$filled);
$html=$renderer->render('view_invoice_vuca',$noPlan);
renderCheck(strpos($html,'PT Waterco Indonesia')!==false && strpos($html,'Ruko Plaza')!==false && strpos($html,'1,000,000.00')!==false,'Old Vuca invoice: customer from the SPK and nominal from the invoice, no PHP notice');
$html=$renderer->render('view_invoice_vuca',array_merge($noPlan,array('data_invoice'=>$bare,'data_plan_tagih'=>Invoice_calculation::plan_or_invoice(null,$bare))));
renderCheck(strpos($html,'<dd class="invoice-customer-name"></dd>')!==false,'Control: without the SPK fallback the customer name is blank');

echo "PASS: $checks Vuca render checks\n";
